<?php
/**
 * Whole lot of stuff for dealing with urls.
 * $Id$
 */
class url
{
	/**
	 * Take a string, and see if we can't embed some urls into it.
	 */
	public static function embed_urls($s)
	{
		$s = preg_replace('%http://(([\w/.:+\-~#?=]|&amp;)+)%','<a href="http://$1">http://$1</a>', $s);
		// and cos we are so generous, this one for www., without http. maybe at the start of the comment?....
    $s = preg_replace('%^www\.(([\w/.:+\-~#?=]|&amp;)+)%','<a href="http://www.$1">http://www.$1</a>', $s);
		// maybe with some other crap before it
    $s = preg_replace('%([^/])www\.(([\w/.:+\-~#?=]|&amp;)+)%','$1<a href="http://www.$2">http://www.$2</a>', $s);

		return $s;
	}
	
	/**
	 * Build a copper url from it's components
	 * @param   $module     string  The module we want to go into
	 * @param   $action     string  The action we want to accomplish there
	 * @param   $params     string  Any parameters to pass
	 * @param   $protocol   string  The protocol to use
	 * @return              string  The finished url.
	 */
	public static function build_url($module = NULL, $action = NULL, $params = NULL, $protocol = NULL)
	{
		if (is_null($protocol)) {
			$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
		}

		$host = $_SERVER[SERVER_NAME_VAR];
		$parts = explode('?', $_SERVER[SCRIPT_NAME_VAR]);
		$path = (strlen($parts[0]) > 0) ? $parts[0] : $_SERVER['PHP_SELF'];
		$url = $protocol.$host.$path;

		if (isset($module))
		{
			$url .= "?module=$module";
			if (isset($action))
			{
				$url .= "&action=$action";
				if (isset($params)) {
					$url .= "&$params";
				}
			}
		}
		
		return $url;
	}
}