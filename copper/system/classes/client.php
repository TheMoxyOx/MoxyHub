<?php
/**
 * client class
 * $Id$
 */

class Client extends Item
{
	protected $tableName = 'tblClients';
	
	protected $defaultFields = array(
		'ID',
		'Name',
		'Manager',
		'Phone1',
		'Phone2',
		'Phone3',
		'FAX',
		'Address1',
		'Address2',
		'City',
		'State',
		'Country',
		'Postcode',
		'URL',
		'Description',
		'Archived',
		'ContactName',
		'ContactEmail',
		'Colour',
	);
	
	private $project_items = null;

	
	public function __get($var)
	{
		if ($var == 'projects')
		{
			return $this->get_projects();
			
		} else {
			return parent::__get($var);
		}
	}

	// we have to make this a funciton, because referencing variables inside the class doesn't call the setter.
	private function get_projects()
	{
		if ($this->project_items == null)
		{
			$this->project_items = new Projects(array('where' => array('ClientID' => $this->ID)));
		}

		return $this->project_items;
		
	}
	
	public function set_group_permissions($group_id, $permission)
	{
		$gp = new GroupPermission(array(
			'GroupID' => $group_id,
			'ObjectID' => 'clients',
			'ItemID' => $this->ID
		));
		
		$gp->AccessID = $permission;
		return $gp->commit();
	}
}

