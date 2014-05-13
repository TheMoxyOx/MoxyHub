<?php
/**
 * Base Database item
 * $Id$
 */

class TaskComment extends Item
{
	protected $tableName 	= 'tblTasks_Comments';
	
	protected $defaultFields = array(
		'ID',
		'UserID',
		'TaskID',
		'Subject',
		'Body',
		'Date',
		'HoursWorked',
		'CostRate',
		'ChargeRate',
		'Issue',
		'Contact',
		'OutOfScope',
	);

	protected $default_data = array(
	);

	private $owner		= null;
	private $contact	= null;
	private $task			= null;
	
	public function __get($var)
	{
		if ($var == 'owner')
		{
			if ($this->owner == null)
			{
				$this->owner = new CopperUser($this->UserID);
			}
			return $this->owner;
		} else if ($var == 'task')
		{
			if ($this->task == null)
			{
				$this->task = new Task($this->TaskID);
			}
			return $this->task;
			
		} else if ($var == 'contact')
		{
			if ($this->contact == null)
			{
				$this->contact = new Contact($this->Contact);
			}

			return $this->contact;
		} else {
			return parent::__get($var);
		}
	}
	
	
}

