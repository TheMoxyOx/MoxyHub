<?php
/**
 * Utils class
 * $Id$
 */

class Utils 
{
	const HOSTED_ROOT = '/Users/Spruce/Library/Containers/com.bitnami.mampstack/Data/app-5_4_9/apache2/htdocs/copper/';

	/* returns something like /var/www/vhosts/copperhub.com/subdomains/{clientname} */
	public static function get_hosted_path()
	{
		$server_host = $_SERVER['HTTP_HOST'];
		$server_exploded = explode('.', $server_host);
		$subdomain = array_shift($server_exploded);
		
		if (preg_match('/^[a-zA-Z0-9]+$/', $subdomain))
		{
			return self::HOSTED_ROOT;
		} else {
			// nup. you're doing weird shit now. Cop demos.
			return self::HOSTED_ROOT;
		}
	}

	public static function convert_form_to_sql_date($user_date)
	{
		$date = explode( '-', $user_date );
		
		if (($user_date == '--') || (count($date) != 3))
		{
			return null;
		}
		
		switch ( Settings::get('DateFormat') ) 
		{
			case "2": // YYYY-DD-MM
				$sql_date = date( "Y-m-d", mktime( 0, 0, 0, $date[2], $date[1], $date[0] ) ); 
				break;
			case "3": // DD-MM-YYYY
				$sql_date = date( "Y-m-d", mktime( 0, 0, 0, $date[1], $date[0], $date[2] ) ); 
				break;
			case "4": // MM-DD-YYYY
				$sql_date = date( "Y-m-d", mktime( 0, 0, 0, $date[0], $date[1], $date[2] ) ); 
				break;
			case "1": // YYYY-MM-DD
			default:
				$sql_date = date( "Y-m-d", mktime( 0, 0, 0, $date[1], $date[2], $date[0] ) ); 
				break;
		}
		
		return $sql_date;
	}

	public static function random_str( $length, $character_set = '1234567890!@#$^*-_.?qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVNBM')
	{
		$str = '';
		$data = str_split( $character_set );
		for($i = 0; $i < $length; $i++)
		{
			$str .= $data[rand(1, strlen($character_set)) - 1];
		}
		return $str;
	}
	
	
}