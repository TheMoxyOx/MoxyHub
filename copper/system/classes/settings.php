<?php
/**
 * Settings class
 * $Id$
 */

class Settings extends Items
{
	protected $tableName 	= 'sysAdminSettings';
	protected $className	= 'Setting';
	
	// keep data in a container
	private static $default_data = array(
		'RecordsPerPage' 						=> 15,
		'WeekStart'									=> CU_WEEK_START,
		'DefaultColour'							=> '#00CCFF',  // Used for initial project colour
		'HeaderBackgroundColour'		=> '#00CCFF', // Used for header background colour

		// Default to US conventions.
		'CurrencySymbol'						=> '$',
		'DecimalPlaces'							=> 2,
		'DecimalPoint'							=> '.',
		'ThousandsSeparator'				=> ',',
		'MoneyDecimalPlaces'				=> 2,
		'MoneyDecimalPoint'					=> '.',
		'MoneyThousandsSeparator'		=> ',',
		'TaxRate'										=> 0
	);
	
	private static $setting_items = null;

	public static function init()
	{
		self::$setting_items = new Settings();
		// sum clean up
		if (strlen(Settings::get('HeaderBackgroundColour')))
		{
			Settings::set('HeaderBackgroundColour', self::$default_data['HeaderBackgroundColour']);
		}
	}
	
	
	public static function get($key)
	{
		if (self::$setting_items == null)
		{
			return null;
		}
		
		foreach(self::$setting_items as $s)
		{
			if ($s->Setting == $key)
			{
				return $s->Value;
			}
		}

		// check in the default set.
		foreach(self::$default_data as $default_key => $default_val)
		{
			if ($key == $default_key)
			{
				return $default_val;
			}
		}
		
		return null;
	}
	
	public static function set($key, $value)
	{
		$setting = null;
		foreach(self::$setting_items as $s)
		{
			if ($s->Setting == $key)
			{
				$setting = $s;
				break;
			}
		}
		
		// check if it's a new setting
		if ($setting == null)
		{
			$setting = new Setting(array('Setting' => $key));
		}
		
		$setting->Value = $value;
		$setting->commit();
	}
}
 
