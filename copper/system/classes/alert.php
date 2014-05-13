<?php

class Alert extends Item 
{
	protected $tableName = 'tblAlerts';
	
	protected $defaultFields = array(
		'id',
		'title',
		'body',
		'startdate',
		'enddate',
		'created',
		'style',
		'users',
		'closeable',
	);
	
	protected $default_data = array(
		'style' => 'info',
		'users' => 'both',
		'closeable' => 1,
	);

}
