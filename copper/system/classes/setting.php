<?php
/**
 * Setting class
 * $Id$
 */

class Setting extends Item
{
	protected $tableName = 'sysAdminSettings';
	
	protected $defaultFields = array(
		'ID',
		'Setting',
		'Value',
	);
	
	protected $default_data = array(
	);
	
}

