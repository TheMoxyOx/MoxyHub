<?php
/**
 * client class
 * $Id$
 */

class GroupPermission extends Item
{
	protected $tableName = 'sysGroupPermissions';
	
	protected $defaultFields = array(
		'ID',
		'GroupID',
		'ObjectID',
		'ItemID',
		'AccessID',
	);

	protected $default_data = array(
	);
	
}