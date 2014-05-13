<?php

class AlertToUser extends Item 
{
	protected $tableName = 'tblAlerts_Users';
	
	protected $defaultFields = array(
		'id',
		'alert_id',
		'user_id',
	);
	
	protected $default_data = array(
	);

}
