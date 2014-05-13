<?php
/**
 * Base Database item
 * $Id$
 */

class Task extends Item
{
	protected $tableName 	= 'tblTasks';
	
	protected $defaultFields = array(
		'ID',
		'Name',
		'ProjectID',
		'Owner',
		'StartDate',
		'Duration',
		'HoursWorked',
		'EndDate',
		'Status',
		'Priority',
		'PercentComplete',
		'Description',
		'RelatedURL',
		'Sequence',
		'Indent',
		'TargetBudget',
		'ActualBudget',
		'LatestActivity',
	);
	
	protected $default_data = array(
		'Duration'				=> 0.00,
		'HoursCommitted'	=> 0.00,
		'HoursWorked'			=> 0.00,
		'TargetBudget' 		=> 0,
	);
	
	private $comments 		= null;
	private $project			= null;
	private $owner				= null;
	private $dependncies	= null;
	
	public function commit($update_activity = TRUE)
	{
		if ($update_activity)
		{
			$this->LatestActivity = DB::now();
		}
		
		return parent::commit();
	}
	
	/**
	 * Override so we can get comments.
	 * Note that can give array() with 0 or more items for when it has comments
	 * And null when comments are unobtainable (ie the object has no id)
	 */
	public function __get($var)
	{
		if ($var == 'comments')
		{
			if ($this->comments != null)
			{
				return $this->comments;
			} else {
				if ($this->ID != null)
				{
					$this->comments = new TaskComments( array( 'where' => array( 'TaskID' => $this->ID ) ) );
				}
				// return null for un-obtainable comments.
				return $this->comments;
			}
		} else if ($var == 'project')
		{
			if ($this->project == null)
			{
				$this->project = new Project($this->ProjectID);
			}
			
			return $this->project;
		} else if ($var == 'owner')
		{
			if ($this->owner == null)
			{
				$this->owner = new CopperUser($this->Owner);
			}

			return $this->owner;
		} else if ($var == 'dependencies')
		{
			if ($this->dependencies == null)
			{
				$this->dependencies = new Dependencies( array( 'where' => array( 'TaskID' => $this->ID ) ) );
			}

			return $this->dependencies;
		} else if ($var == 'assigned_resources')
		{
			$q = "SELECT * FROM tblUsers 
				WHERE ID IN (
					SELECT UserID FROM tblTaskResource 
					LEFT JOIN tblResource 
						ON tblTaskResource.ResourceID = tblResource.ID 
					WHERE TaskID = ?
				)";
				
			$params = array($this->ID);
			return new CopperUsers(array('sql' => $q, 'db_params' => $params));
		} else if ($var == 'files')
		{
			return new Files(array('TaskID' => $this->ID));
		} else if ($var == 'permalink')
		{
			return URL::build_url('projects', 'taskview', "taskid=" . $this->ID . "&projectid=" . $this->ProjectID);
		} else 
		{
			return parent::__get($var);
		}
	}
	
	public function get_sub_tasks()
	{
		// select from tasks where indent > this indent and sequence 
		// this query selects tasks that are one indent higher that this item, but also not in the next set of potentially indented objects
		// then we recurse in move_to_project()
		
		// first get max sequence.
		$q = 'SELECT Sequence 
			FROM tblTasks 
			WHERE ProjectID = ? -- old project id
				AND Indent = ? -- this tasks indent
				AND Sequence > ? -- this tasks sequence
			ORDER BY Sequence
			LIMIT 1';
		$data = array($this->ProjectID, $this->Indent, $this->Sequence);
		$max = DB::single($q, $data);

		$q = 'WHERE ProjectID = ?
			AND Sequence > ? 
			AND Indent = ? + 1 ';

		$data = array($this->ProjectID, $this->Sequence, $this->Indent);

		if ($max)
		{
			$q .= ' AND Sequence < ? ';
			$data[] = $max;
		} 

		$ts = new Tasks(array('sql_where' => $q, 'sql_where_values' => $data, 'orderby' => 'Sequence ASC'));

		return $ts;
	}
	
	public function __set($var, $val)
	{
		if (($var == 'ProjectID') && ($this->exists) && ($this->ProjectID != $val))
		{
			$this->move_to_project($val);
		} else {
			return parent::__set($var, $val);
		}
	}
	
	public function move_to_project($project_id, &$sequence = null, $indent = 0)
	{
		// note that move is only called if ->exists is true.
		foreach($this->files as $file)
		{
			$file->move_to_task($project_id, $file->TaskID);
		}

		if ($sequence == null)
		{
			$q = 'SELECT MAX(Sequence) FROM tblTasks WHERE ProjectID = ?';
			$sequence = DB::single($q, array($project_id));
			if ( ! $sequence)
			{
				$sequence = 0;
			}
		}

		// get the sub tasks first, as the id's etc will change.
		$sub_tasks = $this->get_sub_tasks();

		// set this before hand so we can increment appropriately
		$sequence++;
		// set these before the recursion, so that we have the right order
		$this->Sequence = $sequence;
		parent::__set('ProjectID', $project_id);
		$this->Indent = $indent;
		$this->commit();

		// recurse.
		foreach($sub_tasks as $task)
		{
			$task->move_to_project($project_id, $sequence, $indent + 1);
		}


	}
	
}

