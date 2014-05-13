<?php
/**
 * User class
 * Note that this should actually be user, but that's already taken by crap code. So we use this now. Once we've gotten rid of the crap code, we rename this.
 * $Id$
 */

class CopperUser extends Item
{
	private static $current_user = null;
	
	protected $tableName = 'tblUsers';
	
	protected $defaultFields = array(
		'ID',
		'Username',
		'Password',
		'Title',
		'FirstName',
		'LastName',
		'EmailAddress',
		'Phone1',
		'Phone2',
		'Phone3',
		'Address1',
		'Address2',
		'City',
		'State',
		'Postcode',
		'Country',
		'Module',
		'CostRate',
		'ChargeRate',
		'Active',
		'EmailNotify',
		'IMType',
		'IMAccount',
		'avatar',
	);
	

	protected $default_data = array(
	);
	
	/**
	 * Override so we can get tasks.
	 * Note that can give array() with 0 or more items for when it has tasks
	 * And null when tasks are unobtainable (ie the object has no id)
	 */
	public function __get($var)
	{
		if ($var == 'full_name')
		{
			return $this->FirstName . ' ' . $this->LastName;
		} else 
		{
			return parent::__get($var);
		}
	}

	public function get_activity($limit)
	{
		return new ActivityLogs(array('where' => array('UserID' => $this->ID), 'limit' => $limit, 'orderby' => 'Timestamp DESC', 'groupby' => 'ContextID'));
	}
	
	public function get_avatar()
	{
		$real_avatar = AvatarUpload::get_url($this->avatar);
		return ($this->avatar == null) ? './assets/images/avatars/generic-avatar.jpg' : $real_avatar;
	}
	
	public static function set_current($id)
	{
		self::$current_user = new CopperUser($id);
	}
	
	public static function current()
	{
		if (self::$current_user == null)
		{
			// this should perform auto detection in the future. 
			// For now, we rely on the fact that the base initialisation tells us who the current user is
		}
		
		return self::$current_user;
	}
	
	public function get_assigned_tasks()
	{
		$q = "SELECT t.ID FROM tblTasks t 
						LEFT JOIN tblTaskResource r ON r.TaskID = t.ID 
						WHERE r.ResourceID = (SELECT distinct ID from tblResource where UserID = ?)";
		$data = array($this->ID);
		return DB::col($q, $data);
	}
}

