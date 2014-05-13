<?php
// $Id$

class Session
{
	const SESSION_NAME = 'SESSIONID';		// Cookie variable

	// private properties/variables
	var $ID;										// read only!
	var $timeout;								// Timeout in minutes / change variable to whatever in minutes
	var $Data = array();				// Stores our SessionData
	var $Sessions = null;

	public function __construct($timeout = CU_SESSION_TIMEOUT, $session_id = null)
	{
		$this->timeout	= $timeout;
		$this->ID				= ($session_id == null) ? Request::cookie(self::SESSION_NAME) : $session_id;

		$this->ClearOldSessions();
		$this->InitSession();
		
	}

	public function Set($name, $data)
	{
		$this->Data[$name] = $data;
	}

	public function Get($name)
	{	
		return (is_array($this->Data) && array_key_exists($name, $this->Data)) ? $this->Data[$name] : null;
	}

	public function Clear($name)
	{
		unset($this->Data[$name]);
	}

	public function SessionID()
	{
		return $this->ID;
	}

	public function Abandon()
	{
		$this->Data = array();
	}

	public function Close()
	{
		$q = 'UPDATE sysSessions SET Data = ? WHERE ID = ?';
		$data = array(serialize($this->Data), $this->ID);
		DB::q($q, $data);
	}


	private function ClearOldSessions()
	{
		$timeout = time() - ($this->timeout * 60);

		$q = 'DELETE FROM sysSessions WHERE Timeout < ?';
		$data = array($timeout);
		DB::q($q, $data);
	}


	private function InitSession()
	{
		if ($this->SessionExists())
		{
			$this->SendHeartbeat();
			$this->OpenSession();
		}
		else
		{
			$this->CreateNewSession();
		}
	}

	private function SessionExists()
	{
		$q = 'SELECT ID FROM sysSessions WHERE ID = ?';
		$data = array($this->ID);
		$res = DB::q($q, $data);

		return ($res->rowCount() > 0);
	}

	private function SendHeartbeat()
	{
		$q = 'UPDATE sysSessions SET Timeout = ? WHERE ID = ?';
		$data = array(time(), $this->ID);
		DB::q($q, $data);
	}

	private function OpenSession()
	{
		$q = 'SELECT Data FROM sysSessions WHERE ID = ?';
		$data = array($this->ID);
		$data = DB::single($q, $data);

		$data = unserialize($data);

		if (is_array($data))
		{
			$this->Data = $data;
		} else {
			$this->Data = array();
		}
	}

	private function CreateNewSession()
	{
		$q = 'INSERT INTO sysSessions (ID, Timeout) VALUES (?, ?)';

		if ( strlen($this->ID) != 32 ) 
		{
			$this->ID = $this->CreateID();
			Response::cookie(self::SESSION_NAME, $this->ID);
		}

		$data = array($this->ID, time());
		DB::q($q, $data);
		
	}

	private function CreateID()
	{
		$server	= Request::server('SERVER_ADDR');
		$microtime = microtime();
		$rand		= rand(0, getrandmax());
		return strtoupper(md5($microtime.$server.$rand));
	}
}
 
