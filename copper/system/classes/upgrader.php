<?php
/**
 * Installer.
 * $Id$
 */

class Upgrader
{
	// version is major.minor.{rev number from svn}, when a db change was made.
	const DB_VERSION = 959;

	public static function upgrade_db_959()
	{
		$q = "ALTER TABLE `tblInvoices_Items_Other` CHANGE `ProjectID` `ProjectID` INT(11)  NULL  DEFAULT NULL;";

		DB::raw($q);

		return TRUE;
	}

	public static function upgrade_db_895()
	{
		// -- Adds the view as required by the reports module.
		$q = "CREATE VIEW vwTaskComments AS 
			SELECT 
				u.ID as UserID, 
				CONCAT(u.FirstName, ' ', u.LastName) AS UserName, 
				c.ID AS ClientID, 
				c.Name AS ClientName, 
				p.ID AS ProjectID, 
				p.Name AS ProjectName, 
				t.ID AS TaskID, 
				t.Name AS TaskName, 
				tc.Date AS Date, 
				tc.HoursWorked AS HoursWorked, 
				tc.CostRate, 
				tc.ChargeRate, 
				(tc.HoursWorked * tc.CostRate) AS Cost, 
				(tc.HoursWorked * tc.ChargeRate) AS Charge,
				tc.Issue AS Issue, tc.OutOfScope AS OutOfScope
			FROM tblTasks_Comments tc 
			INNER JOIN tblUsers AS u 
				ON u.ID = tc.UserID
			INNER JOIN tblTasks AS t 
				ON t.ID = tc.TaskID 
			INNER JOIN tblProjects AS p 
				ON p.ID = t.ProjectID 
			INNER JOIN tblClients AS c 
				ON c.ID = p.ClientID";

		DB::raw($q);

		return TRUE;
	}

	public static function upgrade_db_818()
	{
		// -- fix dates to allow null, and defualt to null;
		$q = "ALTER TABLE `tblWorkReports` ADD `WithOtherItems` tinyint(1) UNSIGNED NOT NULL DEFAULT '0'  AFTER `Period`";
		DB::raw($q);
		$q = "ALTER TABLE `tblWorkReports` CHANGE `StartDate` `StartDate` date NULL DEFAULT NULL";
		DB::raw($q);
		$q = "ALTER TABLE `tblWorkReports` CHANGE `EndDate` `EndDate` date NULL DEFAULT NULL";
		DB::raw($q);
		
		return TRUE;
	}

	public static function upgrade_db_783()
	{
		$q = "CREATE TABLE `tblAlerts` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `title` varchar(255) NOT NULL,
		  `body` text,
		  `startdate` timestamp NULL default NULL,
		  `enddate` timestamp NULL default NULL,
		  `created` timestamp NOT NULL default CURRENT_TIMESTAMP,
		  `style` enum('feature','info','warning','pester') NOT NULL default 'info',
		  `users` enum('user','admin','both') NOT NULL default 'both',
		  `closeable` tinyint(1) unsigned NOT NULL default '1',
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8";

		DB::raw($q);

		$q = "CREATE TABLE `tblAlerts_Users` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `alert_id` int(11) unsigned NOT NULL,
		  `user_id` int(11) unsigned NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8";

		DB::raw($q);

		return TRUE;
	}

	public static function run_upgrades()
	{
		if (self::requires_upgrade())
		{
			self::run();
		}
	}

	private static function requires_upgrade() 
	{
		if (version_compare(Settings::get('db_version'), Upgrader::DB_VERSION) == -1)
		{
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	private static function run() 
	{
		Debug::d(DEBUG_INFO, "Upgrading db.");
		// run every update numbered greater than current version (ie from db)
		// and up to and including new version. (ie the class constant.)
		$current_version = Settings::get('db_version');
		if ( ! $current_version)
		{
			$current_version = 1;
		}
		
		for($i = $current_version + 1; $i <= Upgrader::DB_VERSION; $i++)
		{
			if (method_exists('Upgrader', 'upgrade_db_' . $i))
			{
				Debug::d(DEBUG_INFO, "Running DB upgrade: $i");
				
				if ( ! call_user_func(array('Upgrader', 'upgrade_db_' . $i)) )
				{
					Settings::set('db_upgrade_incomplete', 1);
					// set to a version below so that it can try to run the upgrade again.
					Settings::set('db_version', $i - 1);
					return;
				}
			}
		}

		Settings::set('db_version', Upgrader::DB_VERSION);
	}
}

