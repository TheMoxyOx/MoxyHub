<?php 
/**
 * Debugging class
 * $Id$
 */

class Debug
{
	public static $DEBUG_LEVEL = 0;

	public static function debug_sql($pdo, $sql, $params, $named_params = array())
	{
		error_log("Query: $sql");

		if (count($params) > 0) {
			error_log("Params:");
			self::dump($params);
		}
		if (count($named_params) > 0) {
			error_log("Named Param:");
			self::dump($named_params);
		}

		if ($ei instanceof PDOStatement)
		{
			$ei = $pdo->errorInfo();
			if (isset($ei[2]))
			{
				error_log("PDO says: " . $ei[2]);
			}
			error_log('');
		}
	}
	
	public static function dump()
	{
		// only debug if the debug level is set. This is to stop coder written debugs getting onto production servers
		if (self::$DEBUG_LEVEL > 0)
		{
			$args = func_get_args();
			foreach($args as $arg)
			{
				if (is_array($arg))
				{
					$str = print_r($arg, TRUE);
					error_log($str);
				} else {
					error_log($arg);
				}
			}
		}
	}

	// this is the main way that debugging should be done.
	public static function d($level, $data)
	{
		if (self::chk($level))
		{
			self::dump($data);
		}
	}

	public static function set_debug_level($level)
	{
		self::$DEBUG_LEVEL = $level;
	}

	public static function chk($check_level)
	{
		return (self::$DEBUG_LEVEL & $check_level);
	}

}

