<?php
/** 
 * Response class. Deal with responding to the request
 * This may include redirecting, building up js, or even return cookies
 * $Id$
 */

class Response {
	
	static $js = null;

	const GA_TRACKING_CODE = 'UA-5821577-15';

	public static function init()
	{
		Response::addBasicsToJavascript();
		Response::addTrackingToJavascript();
		
		// set a default header. some other classes will overwrite later.
		header('Content-Type: text/html; charset='.CHARSET);
	}
	
	public static function addToJavascript($key, $data)
	{
		self::$js[$key] = $data;
	}

	public static function addBasicsToJavascript()
	{
		self::addToJavascript('module', Request::get('module'));
		self::addToJavascript('action', Request::get('action'));
		
		self::addToJavascript('base_url', Request::get_request_uri(FALSE, TRUE));
	}
	
	public static function addTrackingToJavascript()
	{
		self::addToJavascript('tracking', array(
			'ga_tracking_code' => self::GA_TRACKING_CODE
		));
	}
	
	public static function getJavascript() {
		return json_encode(self::$js);
	}
	
	/**
	 * @todo. Fix this so that it does path and secure where neccessary
	 */
	public static function cookie($key, $value)
	{
		setcookie($key, $value);
	}

	public static function redirect($location, $code = 302, $die = true)
	{
		header('Location: ' . $location, true, $code);

		if ($die) {
			exit;
		}
	}
	
	public static function redirect_back($code = 302, $die = true)
	{
		$location = Request::server('HTTP_REFERER');
		self::redirect($location, $code, $die);
	}
	

}
