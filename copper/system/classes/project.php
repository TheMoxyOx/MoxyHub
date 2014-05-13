<?php
/**
 * Project class
 * $Id$
 */

class Project extends Item
{
	protected $tableName = 'tblProjects';
	
	protected $defaultFields = array(
		'ID',
		'ClientID',
		'ProjectID', // this is not the ID. this is the user set pseudo-id, ie, a string.
		'Name',
		'Owner',
		'URL',
		'DemoURL',
		'StartDate',
		'EndDate',
		'ActualEndDate',
		'Status',
		'Priority',
		'Colour',
		'Description',
		'TargetBudget',
		'ActualBudget',
		'Active',
	);

	protected $default_data = array(
		'Status'		=> 1,
		'Priority' 	=> 1,
		'Colour' 		=> '#66CCCC',
		'Active' 		=> 1,
	);
	
	private $tasks_items = null;
	private $owner = null;
	
	/**
	 * Override so we can get tasks.
	 * Note that can give array() with 0 or more items for when it has tasks
	 * And null when tasks are unobtainable (ie the object has no id)
	 */
	public function __get($var)
	{
		if ($var == 'tasks')
		{
			return $this->get_tasks();
			
		} else if ($var == 'owner')
		{
			if ($this->owner == null)
			{
				$this->owner = new CopperUser($this->Owner);
			}
			
			return $this->owner;
		} else if ($var == 'client')
		{
			return new Client($this->ClientID);
		} else {
			return parent::__get($var);
		}
	}

	// we have to make this a funciton, because referencing variables inside the class doesn't call the setter.
	private function get_tasks()
	{
		if ($this->tasks_items == null)
		{
			$this->tasks_items = new Tasks(array('where' => array('ProjectID' => $this->ID)));
		}

		return $this->tasks_items;
		
	}

	public function get_highest_sequence()
	{
		$highest = 0;
		foreach($this->tasks as $task)
		{
			$highest = max($highest, $task->Sequence);
		}
		
		return $highest;
	}

	public function get_folders()
	{
		return new Folders(array('where' => array('ProjectID' => $this->ID)));
	}
	
	public function get_base_files()
	{
		return new Files(array('where' => array('ProjectID' => $this->ID, 'Folder' => 0)));
	}

	public function set_group_permissions($group_id, $permission)
	{
		$gp = new GroupPermission(array(
			'GroupID' => $group_id,
			'ObjectID' => 'projects',
			'ItemID' => $this->ID
		));
		
		$gp->AccessID = $permission;
		return $gp->commit();
	}
	
	public function get_tasks_with_hours()
	{
		$q = 'SELECT tt.*,
					(SELECT SUM(ttc.HoursWorked) from tblTasks_Comments ttc where ttc.TaskId=tt.ID) as HoursWorked,
					(SELECT SUM(ttc.HoursWorked * ttc.ChargeRate) from tblTasks_Comments ttc where ttc.TaskId=tt.ID) as Billable,
					(SELECT SUM(tti.Amount) from tblInvoices_Items tti where tti.TaskId=tt.ID) as Billed
					FROM tblTasks tt
					LEFT OUTER JOIN tblTasks_Comments tc ON tc.TaskID = tt.ID AND tc.OutOfScope = ?
					LEFT JOIN tblInvoices_Items ti on ti.TaskID = tt.ID
					WHERE tt.ProjectID = ?
					GROUP BY tt.ID';

		// outofscope and project id.
		$data = array('0', $this->ID);
		
		$tasks = new Tasks(array('sql' => $q, 'db_params' => $data, 'orderby' => 'Sequence ASC'));
		return $tasks;
	}
	
	public function get_other_invoiced_items()
	{
		$iio = new InvoiceItemOthers(array('where' => array('ProjectID' => $this->ID)));
		return $iio;
	}
	
	public function get_other_items_cost()
	{
		$q = "SELECT SUM(Cost) AS Cost FROM tblInvoices_Items_Other WHERE ProjectID = ?";
		$retval = DB::single($q, array($this->ID));
		return $retval;
	}
	
	public function get_other_items_charge()
	{
		$q = "SELECT SUM(Charge) AS Charge FROM tblInvoices_Items_Other WHERE ProjectID = ?";
		$retval = DB::single($q, array($this->ID));
		return $retval;
	}
	
	public function get_total_charge()
	{
		// okay so in the old code, they were looking at the view. which is gay. just do some left joins.
		$q = "SELECT SUM(tblTasks_Comments.HoursWorked * tblTasks_Comments.ChargeRate) as ProjectCharge 
					FROM tblProjects
					LEFT JOIN tblTasks ON tblProjects.ID = tblTasks.ProjectID
					LEFT JOIN tblTasks_Comments ON tblTasks.ID = tblTasks_Comments.TaskID
					WHERE tblProjects.ID = ?";

		$retval = DB::single($q, array($this->ID));
		return $retval;
	}
}

