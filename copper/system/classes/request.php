<?php

/**
 * Handle request related data
 * $Id$
 */

/* set up the defines for the enum of request types we allow */

class Request
{
	const R_STRING	= 1;
	const R_INT			= 2;
	const R_ARRAY		= 3;

	public static $GET;
	public static $POST;
	public static $COOKIE;
	public static $FILES;
	public static $SERVER;
	
	public static function init()
	{
		self::$GET		= new RequestGlobal($_GET);
		self::$POST		= new RequestGlobal($_POST);
		self::$COOKIE	= new RequestGlobal($_COOKIE);
		self::$FILES	= new RequestGlobal($_FILES);
		self::$SERVER	= new RequestGlobal($_SERVER);
	}
	
	
	/* hokay so we can't put these oaver all the things at this stage, becuase the non-pdo queries in copper
	** __REQUIRE__ (yes require) magic quotes to work . they are relying on the security of magic quotes to keep their shit safe.
	** WTF. One day, it will all be okay.
	*/
	public static function clean_magic_quotes($data)
	{
		// from http://www.php.net/manual/en/security.magicquotes.disabling.php
		if (get_magic_quotes_gpc()) 
		{
			return Request::strip_quotes($data);
		} else {
			return $data;
		}
	}

	public static function strip_quotes($var) 
	{
		if (is_array($var)) 
		{
			$newvar = array();
			foreach ($var as $k => $val)
			{
				$newvar[stripslashes($k)] = Request::strip_quotes($val);
			}
			return $newvar;
		} else if ($var === null) {
			return null;
		} else {
			return stripslashes($var);
		}
	}
	
	/*
	 * include query string is obvious
	 * include basedir is whether we should include the bits of the path above the app install.
	 * ie, if app is installed at /path/at/app, and the full request is /path/at/app/checkout, 
	 * then $include_basedir == TRUE returns /path/at/app/checkout, FALSE returns checkout
	 */
	public static function get_request_uri($include_query_string = FALSE, $include_basedir = FALSE)
	{
		// we can't use parse_url here, as we want things like page:2 to be the page, not the port, which is how parse_url decides it is
		$path = Request::server('REQUEST_URI');

		if (strstr($path, '?'))
		{
			list($path, $qs) = explode('?', $path, 2);
		} else {
			$qs = null;
		}

		// as it is if the app is installed in root
		if ( $include_basedir || (dirname(Request::server('PHP_SELF')) == '/' ) )
		{
			$url = rtrim( $path, '/\\' );
		} else {
			$url = rtrim( str_replace( dirname(Request::server('PHP_SELF') ), '', $path), '/\\' );
		}

		if ($include_query_string) {
			$url .= '?' . $qs;
		}
		
		return $url;
	}
	
	// given http://localhost/squareweave/app/trunk/htdocs/checkout/success this returns
	// array(checkout, success)
	public static function get_path_parts()
	{
		return explode('/', ltrim(Request::get_request_uri(), '/'));
	}

	public static function get_path_part($n)
	{
		$arr = self::get_path_parts();
		if (array_key_exists($n, $arr))
		{
			return $arr[$n];
		} else {
			return null;
		}
	}
	
	public static function get($field, $type = self::R_STRING)
	{
		// var_dump($field, $type, self::$GET[$field]);
		return self::retrieve_data(self::$GET, $field, $type);
	}
	
	public static function post($field, $type = self::R_STRING)
	{
		return self::retrieve_data(self::$POST, $field, $type);
	}

	public static function server($field, $type = self::R_STRING)
	{
		return self::retrieve_data(self::$SERVER, $field, $type);
	}

	public static function files($field, $type = self::R_STRING)
	{
		return self::retrieve_data(self::$FILES, $field, $type);
	}

	public static function cookie($field, $type = self::R_STRING)
	{
		return self::retrieve_data(self::$COOKIE, $field, $type);
	}

	public static function any($field, $type = self::R_STRING)
	{
		$val = self::retrieve_data(self::$GET, $field, $type);
		if ($val != null) return $val;

		$val = self::retrieve_data(self::$POST, $field, $type);
		if ($val != null) return $val;
	}

	private static function retrieve_data(&$a, $f, $type)
	{
		// we need to be _really_ forgiving here, as some of the other code is pretty bad.
		if (($f != null) && (isset($a[$f])))
		{
			switch($type)
			{
				case self::R_STRING:
					return $a[$f];
				case self::R_INT:
					return (is_numeric($a[$f]) && is_int((int)$a[$f])) ? (int)$a[$f] : null;
				case self::R_ARRAY:
					return is_array($a[$f]) ? $a[$f] : null;
				default: 
					return null;
			}
		} else {
			return null;
		}
	}
}

class RequestGlobal extends ArrayIterator {
	
	public function __construct($array)
	{
		parent::__construct($array);
	}
	
	/**
	 * @param string request field to use 
	 * @param $orderOptions array of key value pairs. if the field in the request field matches a key, that key-pair is returned
	 * @param $default if nothing is found, return this.
	 */
	public function filterRequest($key, $options, $default = null) {
		if ($this->offsetExists($key) && isset($options[$this->offsetGet($key)]))
		{
			return array($this->offsetGet($key), $options[$this->offsetGet($key)]);
		} else {
			return $default;
		}
	}

	/**
	 * Special instance of above function, for brevity
	 */
	public function filterOrderDirection()
	{
		return self::filterRequest('direction', array('down' => 'DESC', 'up' => 'ASC'), array('up' => 'ASC'));
	}	
}
