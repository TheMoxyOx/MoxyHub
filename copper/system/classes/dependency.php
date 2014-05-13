<?php
/**
 * Dependancy item
 * $Id$
 */

class Dependancy extends Item
{
	protected $tableName 	= 'tblDependencies';
	
	protected $defaultFields = array(
		'ID',
		'TaskID',
		'TaskDependencyID',
		'DependencyType',
	);

	protected $default_data = array(
	);
	
	
	/**
	 * Override so we can get comments.
	 * Note that can give array() with 0 or more items for when it has comments
	 * And null when comments are unobtainable (ie the object has no id)
	 */
	public function __get($var)
	{
		if ($var == 'nyi')
		{
			return null;
		} else 
		{
			return parent::__get($var);
		}
	}
}

