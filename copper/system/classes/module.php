<?php
// $Id$
class Module extends Base
{
	// global objects
	var $DB;
	var $User;
	var $CopperUser;	
	var $Session;
	var $Request;
	var $Template;

	//var $Config = array();
	var $ModuleName;
	var $RequireLogin;
	var $Public;

	//var $Timer;

	function Module()
	{
	  // register our deconstructor. Register it early so that we can catch dies.
	  register_shutdown_function(array(&$this, '_Module'));

		ob_start();
		$sql  = sprintf(sprintf(CU_MODULE_PATH, $this->ModuleName).CU_MODULE_SQL, DB_DRIVER);
		if (file_exists($sql)) 
			include_once($sql);

		// create our objects
		$this->DB =& new DBConnection(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

		// todo, this should really be in the bootstrap eventually.

		$this->Session = new Session();
		$this->User =& new User();
		$this->Template =& new Template();
		// execute code
		$this->CheckModule();
		$this->InitialiseUser();
		$this->InitialiseTemplate();
		$this->InitialiseTimeZone();
		$this->StartModule();
	}

	function _Module($called = 0)
	{
		$this->Session->Close();

		// if we close it, for some reason the db stuff doesn't make it in.
		// not really needed anyway.
		// $this->DB->Close();
		unset($this->Template);
		unset($this->User);
		unset($this->Session);
		unset($this->DB);
		if ($called) ob_flush();

		// make sure it's not called again
		exit();
	}

	function InitialiseUser()
	{
		$auth = $this->Session->Get('authorised');
		if ($auth == 1)
		{
			$userid = $this->Session->Get('userid');
			$this->User->Initialise($userid, $this->DB);
			CopperUser::set_current($userid);
		} else {
			CopperUser::set_current(null);
		}
	}

	function InitialiseTemplate()
	{
		$mod  = $this->ModuleName;
		$auth = $this->User->Authorised;
		$name = $this->User->Name();
		$list = $this->User->ModuleList;
		$this->Template->Initialise($mod, $auth, $name, $list, $this->User, $this->DB);
	}

	function InitialiseTimeZone()
	{
		$tz = $this->DB->ExecuteScalar(CU_SQL_GET_TIME_ZONE);
				// > php 5.1.0
				date_default_timezone_set($tz);
				// older is poo
		putenv("TZ=".$tz);
	}

	function MD5($string)
	{
		return strtoupper(md5($string . $this->Salt));
	}

	function StartModule()
	{
		if ($this->RequireLogin == 0)
		{
			$this->start_main();
			return;
		}

		// else
		if ($this->User->Authorised != 1)
		{
			// user is not authenticated...
			$this->ShowAuthorisation();
			return;
		}

		// user authenticated, module public
		if ($this->Public == 1)
		{
			$this->start_main();
			return;
		}

		// user authenticated, module private, has access?
		if ($this->User->HasModuleObjectAccess($this->ModuleName))
		{
			$this->start_main();
			return;
		}

		if (($this->User->AssignedTasks != null) && (in_array(Request::any('taskid'), $this->User->AssignedTasks)))
		{
			$this->start_main();
			return;
		}

		// failing all else
		$this->ThrowError(2000);
	}

	function CheckModule()
	{
		if (!is_object($this))
		{
			$this->ThrowError(666);
			return;
		}

		if (!isset($this->ModuleName))
		{
			$this->ThrowError(1001);
			return;
		}

		if (!isset($this->RequireLogin))
		{
			$this->ThrowError(1002);
			return;
		}

		if (!isset($this->Public))
		{
			$this->ThrowError(1003);
			return;
		}
	}

	function ThrowError($code, $inject = NULL)
	{
		Response::redirect('index.php?module=error&from='.$this->ModuleName.'&error='.$code.'&inject='.$inject);
	}

	function ShowAuthorisation()
	{
		// first, set their expected destinatino so we can redirect them back there when they log in again.
		$this->Session->set('expected_destination', Request::get_request_uri(TRUE, TRUE));
		Response::redirect('index.php?module=' .CU_AUTH_MODULE . '&message=2&redirect=' . urlencode(Request::server('QUERY_STRING')));
	}

	// Start template pass through functions
	// try to pass through what we can to the template object. 
	public function __call($name, $args)
	{
		if (is_callable(array($this->Template, $name)))
		{
			return call_user_func_array(array($this->Template, $name), $args);
		} else {
			// return the error as normal.
			trigger_error(sprintf('Call to undefined function: %s::%s().', get_class($this), $name), E_USER_ERROR);
		}
	}

	// this one hijacks, so keep it
	function setModule($crumbs = null, $action1 = null, $action2 = null)
	{
		$this->Template->setModule($this->ModuleName, $crumbs, $action1, $action2);
	}
	// END template pass through functions


	function Log($context, $contextID, $action, $detail = NULL, $comment = NULL) {
		// Get the details of the current connection.
		if ( getenv( "HTTP_CLIENT_IP" ) ) { $ip = getenv( "HTTP_CLIENT_IP" ); }
		elseif ( getenv( "HTTP_X_FORWARDED_FOR" ) ) { $ip = getenv( "HTTP_X_FORWARDED_FOR" ); }
		elseif ( getenv( "REMOTE_ADDR" ) ) { $ip = getenv( "REMOTE_ADDR" ); }
		else { $ip = "UNKNOWN"; }
		$url = $_SERVER['REQUEST_URI'];
		$timestamp = date( 'Y-m-d H:i:s' );

		$detail  = ( $detail != NULL ) ? "'" . addslashes( $detail ) . "'" : 'NULL';
		$comment = ( $comment != NULL ) ? "'" . addslashes( $comment ) . "'" : 'NULL';

		$sql = sprintf(CU_SQL_LOG_ACTION, $timestamp, $this->User->ID, $ip, $url, $context, $contextID, $action, $detail, $comment);
		$this->DB->Execute($sql);
	}
}

 
