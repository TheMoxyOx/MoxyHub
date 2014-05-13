<?php
/**
 * Base Database item
 * $Id$
 */

class TaskComments extends Items
{
	protected $tableName 	= 'tblTasks_Comments';
	protected $className	= 'TaskComment';

	public function __construct($params)
	{
		parent::__construct($params + array( 'orderby' => 'Date DESC'));
	}
}


