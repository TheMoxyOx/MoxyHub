<?php
/**
 * Activity Log class
 * $Id $
 */

class ActivityLog extends Item
{
	protected $tableName = 'tblActivityLog';
	
	protected $default_fields = array(
		'ID',
		'Timestamp',
		'UserID',
		'IP',
		'Url',
		'Context',
		'ContextID',
		'Action',
		'Detail',
		'Comment',
	);

	protected $default_data = array(
	);

	public function __get($var)
	{
		if ($var == 'permalink')
		{
			switch($this->Context)
			{
				case 'client': 
					$data = array('module' => 'clients', 'action' => 'view', 'id' => $this->ContextID);
					break;
				case 'project':
					$data = array('module' => 'projects', 'action' => 'view', 'projectid' => $this->ContextID);
					break;
				case 'task':
					$data = array('module' => 'projects', 'action' => 'taskview', 'projectid' => $this->Comment, 'taskid' => $this->ContextID);
					break;
				case 'file':
					$data = array('module' => 'files', 'action' => 'fileview', 'id' => $this->ContextID);
					break;
				case 'contact':
					$data = array('module' => 'contacts', 'action' => 'view', 'id' => $this->ContextID);
					break;
				case 'projectreport':
					$data = array('module' => 'reports', 'action' => 'analysis', 'report' => $this->ContextID);
					break;
				case 'workreport':
					$data = array('module' => 'reports', 'action' => 'timesheets', 'report' => $this->ContextID);
					break;
				default:
					$data = array();
			}
			
			return 'index.php?' . http_build_query($data);
		} else {
			return parent::__get($var);
		}
	}

	public static function map_context_to_lang($context)
	{
		switch($context)
		{
			case 'client':
			case 'project':
			case 'task':
			case 'file':
			case 'contact':
			case 'login':
				return constant('MSG_' . strtoupper($context));
			case 'projectreport':
			case 'workreport':
				return MSG_REPORT;
			default:
				return '';
		}
	}
}
