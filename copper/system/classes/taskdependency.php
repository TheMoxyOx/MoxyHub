<?php
/**
 * TaskDependency Database item
 * $Id$
 */

class TaskDependency extends Item
{
	protected $tableName 	= 'tblTasks_Dependencies';
	
	protected $defaultFields = array(
		'ID',
		'TaskID',
		'TaskDependencyID',
		'DependencyType',
	);
	
	protected $default_data = array(
		'DependencyType' => 1,
	);
	
}

