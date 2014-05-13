<?php
/**
 * Handle bootstrap
 * $Id$
 */

/* set up the defines for the enum of request types we allow */

class Bootstrap
{
	// deals with system wide setup. Gives everything a chance to do any init before we actually start running.
	// Note that no db connections happen here, as it's also used for setup and upgrades
	public static function go()
	{
		// first, register the copper handler.
		spl_autoload_register(array('Bootstrap', 'copper_autoload'));
		// then register the default handler as well
		spl_autoload_register();
		
		error_reporting(E_ALL);

		// Include older stuff that hasn't been oop'd yet.
		require_once(CU_SYSTEM_PATH . 'sql_' . DB_DRIVER . CU_SCRIPT_EXT);
		require_once(CU_SYSTEM_PATH . 'copper' . CU_SCRIPT_EXT);

		self::read_config();
	
		// init any classes that need it.
		Request::init();
		DB::init(DB_USERNAME, DB_PASSWORD, DB_NAME, DB_SERVER);
		
		Settings::init();

		// upgrader requries settings
		Upgrader::run_upgrades();
	}
	
	public static function read_config()
	{
		/* first see if we have a proper config file */
		$config = realpath(dirname(__FILE__) . '/../../config_local.php');
		$hosted_config = Utils::get_hosted_path() . '/config_local.php';

		if (is_readable($config)) 
		{
			require($config);
			$is_hosted = FALSE;
			
		// maybe we are hosted
		} elseif (file_exists($hosted_config) )
		{
			require($hosted_config);
			require_once(Utils::get_hosted_path() . '/htdocs/system/system.php');
			$is_hosted = TRUE;

		// lets try the normal config location
		} elseif (is_readable('config.php')) 
		{
			require('config.php');
			$is_hosted = FALSE;

		// redirect
		} else 
		{
			header('Location: install/index.php');
			$is_hosted = FALSE;
		}

		define('IS_HOSTED', $is_hosted);
	}

	public static function doit() 
	{
		// load up the language stuff
		// Note this makes a db call too
		$language = new Language();
		$language->main();

		Response::init();

		// If we aren't doing a download, then add the gz handler.
		if (Request::get('action') != 'filedown') {
		    ob_start('ob_gzhandler', 1);
		}

		$module = self::select_module();
		$module_class  = 'mod_' . $module;
		new $module_class();
	}
	
	/*
	 * Find out which module we are destined for.
	 * @return string containing the module we are destined for.
	*/ 
	public static function select_module()
	{
		$module = Request::any('module');
		$auth   = Request::cookie('authorised'); // gets set from within AUTH module
		
		// hackalert!
		// so because the structure of this code is retarded, file uploads don't sent the auth cookie. We shouldn't be doing that anyway, 
		// but until we move auth to the right place, for now we have to bypass this check if wwe are doing a flash file upload
		// see the function to see that we do another check there
		$hack_auth = (($module == 'files') && (Request::any('action') == 'swffilesave'));
		
		if (($auth != 1) && (!$hack_auth))
		{
			$module = CU_AUTH_MODULE;
		}

		if (strlen($module) == 0) {
			$module = CU_DEFAULT_MODULE;
		}

		if (($module != CU_AUTH_MODULE) && IS_HOSTED && (Settings::get('trial_status') == 'expired'))
		{
			Response::redirect('index.php?module=authorisation&action=expired');
		}

		// do a little sanity checking, as we're going to do fs ops on this thing.
		if (preg_match('/[a-z]*/', $module) !== 1)
		{
			Response::redirect('index.php?module=' . CU_ERROR_MODULE . '&error=501');
		}
		
		if (class_exists('mod_' . $module))
		{
			return $module;
		} else {
			Response::redirect('index.php?module=' . CU_ERROR_MODULE . '&error=500');
			die();
		}
	}
	
	public static function copper_autoload($class)
	{
		// first try for base classes. 
		if (file_exists(CU_CLASS_PATH . $class . CU_SCRIPT_EXT))
		{
			require_once(CU_CLASS_PATH . $class . CU_SCRIPT_EXT);
			return;
		}
		
		if (file_exists(CU_CLASS_PATH . strtolower($class) . CU_SCRIPT_EXT))
		{
			require_once(CU_CLASS_PATH . strtolower($class) . CU_SCRIPT_EXT);
			return;
		}
		
		// secondly, lets try for module classes. in this case, the class name is mod_{modulename}.
		// {modulename} is the name of the folder, but the file is always called index.php. So see if we can find an
		// index.php in the appropraite folder, ie SYSTEM/modules/{modulename}/index.php

		if (substr($class, 0, 4) == 'mod_')
		{
			$module_name = substr($class, 4);
			$file = sprintf(CU_MODULE_PATH . CU_MODULE_ENGINE, $module_name);
			if (file_exists($file))
			{
				require_once($file);
				if (class_exists($class, FALSE)) // don't call autoload on the internal test
				{
					return;
				} else {
					Response::redirect('index.php?module=' . CU_ERROR_MODULE . '&error=501');
					die();
				}
			}
		}
		
		// okay, now try for library classes, in folders according to the 3rd partyness(ie sets of things). 
		$folder_contents = scandir(CU_CLASS_LIB_PATH);
		foreach($folder_contents as $file)
		{
			if (is_dir(CU_CLASS_LIB_PATH . $file) && (substr($file, 0, 1) != '.'))
			{
				if (file_exists(CU_CLASS_LIB_PATH . $file . '/' . $class . CU_SCRIPT_EXT))
				{
					require_once(CU_CLASS_LIB_PATH . $file . '/' . $class . CU_SCRIPT_EXT);
					return;
				}
			}
		}

		// uh oh, we couldn't find anything. bye!
		Response::redirect('index.php?module=' . CU_ERROR_MODULE . '&error=502&info=' . $class);
		die();
	}
}

