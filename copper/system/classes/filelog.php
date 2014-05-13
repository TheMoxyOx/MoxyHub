<?php
/**
 * Setting class
 * $Id$
 */

/* this class is the direct association with the db object */

class FileLog extends Item
{
	protected $tableName = 'tblFile_Log';
	
	protected $defaultFields = array(
		'ID',
		'FileID',
		'UserID',
		'Time',
		'Activity',
		'Version',
		'FileName',
		'Type',
		'Size',
		'RealName',
	);
	
	protected $default_data = array(
	);
}
