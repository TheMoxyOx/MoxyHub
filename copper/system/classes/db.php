<?php
/**
 * Base Database class. Wrap PDO
 * $Id$
 */

class DB
{
	static $pdo;

	public static function init($user, $pass, $database, $host = 'moxyox.com') 
	{
		// we use persistent connection
		self::$pdo = new PDO(
			'mysql:host=' . $host . ';dbname=' . $database, 
			$user, 
			$pass, 
			array(
				PDO::ATTR_PERSISTENT => false,
			)
		);
	}
	
	public static function q($sql, $data = array(), $named_data = array())
	{
		$s = self::$pdo->prepare($sql);

		if ( Debug::chk(DEBUG_SQL_ALL) )
		{
			Debug::debug_sql($s, $sql, $data);
		}

		// die if we couldn't prepare
		if ($s == FALSE) {

			if ( Debug::chk(DEBUG_SQL_ERROR) && ! Debug::chk(DEBUG_SQL_ALL) )
			{
				Debug::debug_sql($pdo, $sql, $data);
			}

			return FALSE;
		}

		foreach($named_data as $key => $data)
		{
			$s->bindParam($key, $data['val'], $data['type']);
		}
		
		$success = $s->execute($data);

		if ($success === FALSE)
		{
			if ( Debug::chk(DEBUG_SQL_ERROR) )
			{
				Debug::debug_sql($s, $sql, $data);
			}

			return FALSE;
		} else {
			return $s;
		}
	}
	
	public static function update($sql, $data = array(), $named_data = array())
	{
		$s = self::q($sql, $data, $named_data);
		if ($s === TRUE)
		{
			$s->closeCursor();
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	public static function col($sql, $data = array(), $named_data = array())
	{
		$s = self::q($sql, $data, $named_data);
		if ($s && $s->rowCount() > 0)
		{
			return $s->fetchAll(PDO::FETCH_COLUMN);
		} else {
			return array();
		}
	}
	
	public static function scalar($sql, $data = array(), $named_data = array())
	{
		$s = self::q($sql, $data, $named_data);
		$row = $s->fetch();
		return $row[0];
	}
	
	public static function single($sql, $data = array(), $named_data = array())
	{
		$s = self::q($sql, $data, $named_data);
		$ret = $s->fetch();
		return $ret[0];
	}
	
	public static function raw($s) 
	{
		return self::$pdo->query($s);
	}
	
	public static function last_insert_id()
	{
		return self::$pdo->lastInsertId();
	}
	
	public static function begin_transaction()
	{
		return self::$pdo->beginTransaction();
	}

	public static function commit_transaction()
	{
		return self::$pdo->commit();
	}

	public static function date($date)
	{
		return date("Y-m-d H:i:s", $date);
	}
	
	public static function now()
	{
		return self::date(time());
	}	
}

