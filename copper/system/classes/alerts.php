<?php 

class Alerts extends Items
{
	const API_BASE = 'http://alerts.copperproject.com/api/1/alerts';
	
	protected $tableName 	= 'tblAlerts';
	protected $className 	= 'Alert';
	
	public static function update_from_master()
	{
		$opts = array(
			'http' => array(
				'method' => 'GET',
				'header' => 'User-Agent: Copper/' . CU_PRODUCT_VERSION . ' at ' . Request::server('HTTP_HOST'),
			)
		);

		$context = stream_context_create($opts);
		$since = Settings::get('last_alerts_update');
		Settings::set('last_alerts_update', time());

		if ( ! $since ) 
		{
			$since = 0;
		}

		$url = self::API_BASE . '/get_since/' . $since;
		$handle = @fopen($url, "r", FALSE, $context);
		
		if ( ! $handle ) 
		{
			// oh well. try again next cron.
			return;
		}
		
		$content = stream_get_contents($handle);
		fclose($handle);

		$obj = json_decode($content);
		if ( ($obj != null) && ( is_array($obj) ) )
		{
			self::insert_alerts($obj);
		} 

	}
	
	private static function insert_alerts($alerts)
	{
		foreach ($alerts as $alert)
		{
			$data = get_object_vars($alert);
			$a = new Alert($data['id']);
			if ( ! $a->exists )
			{
				$a = new Alert($data);
				$a->commit();
			}
		}
	}
	
	public static function get_for_current_user()
	{
		$q = 'SELECT tblAlerts.* 
						FROM tblAlerts 
						LEFT JOIN tblAlerts_Users 
							ON tblAlerts_Users.alert_id = tblAlerts.id 
								AND tblAlerts_Users.user_id = ? 
						WHERE user_id IS NULL
					AND (UNIX_TIMESTAMP(startdate) < UNIX_TIMESTAMP() OR startdate = "0000-00-00 00:00:00" OR startdate IS NULL)
					AND (UNIX_TIMESTAMP(enddate) > UNIX_TIMESTAMP() OR enddate = "0000-00-00 00:00:00" OR enddate IS NULL)';
					
		$data = array(CopperUser::current()->ID);
		$as = new Alerts(array('sql' => $q, 'db_params' => $data));

		// now get the trial alert if we are a trial.
		if (Settings::get('trial_status') == 'trial')
		{
			$a = new Alert(null);
			$a->title = "We hope you're enjoying your trial of Copper!";
			$a->body = "Your Copper trial will expire " . Format::timeago(Settings::get('trial_expires')) . ". When you're ready to join the Copper family, you can <a href='http://www.copperproject.com/pricing/'>sign up here</a>.";
			$a->closeable = 0;
			$a->style = 'pester';
			
			$as[] = $a;
		}

		return $as;
	}
}