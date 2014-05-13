<?php

function install()
{
    $db = $GLOBALS['db'];

    // Construct a new database.
    $file = file("Copper4New.sql");
    foreach ($file as $line)
    {
        if ($line[0] != '#' && $line[0] != '-' && strlen(trim($line)) > 0)
        {
            $sql .= rtrim($line);
            if (substr($sql, -1) == ";")
            {
                $result = $db->Execute($sql);
                if ($result == FALSE)
                    die('Error: Database construction failed.<br>'.$sql.'<br>'.mysql_error());
                else
                    $sql = '';
            }
        }
    }

    if (!is_numeric($_POST['cost'][0])) 
        $_POST['cost'] = substr($_POST['cost'][0], 1);

    if (!is_numeric($_POST['charge'][0])) 
        $_POST['charge'] = substr($_POST['charge'][0], 1);

    $hourlyRate = (floatval($_POST['charge']) > 0.00) ? $_POST['charge'] : 0.00; // Default system-wide charge rate.
    $costRate = (floatval($_POST['cost']) > 0.0) ? $_POST['cost'] : 0.00; // Default user cost rate.
    $chargeRate = (floatval($_POST['charge']) > 0.00) ? $_POST['charge'] : 0.00; // Default user charge rate.
    $threshold = 0.01; // Only rates below this number will be updated.

    setAdminCostRates($costRate, $chargeRate, $threshold);

    // Update the accounting columns and data.
    establishHourlyRate($hourlyRate, $threshold);
    establishUserRates($costRate, $chargeRate, $threshold);
    establishTaskCommentRates($threshold);
    recalculateBudgets();
    createNewDayIDs();
    addParentFolderColumn();
    addFileHistoryDetails();
}

function upgrade()
{
    if (!is_numeric($_POST['cost'][0])) 
        $_POST['cost'] = substr($_POST['cost'][0], 1);

    if (!is_numeric($_POST['charge'][0])) 
        $_POST['charge'] = substr($_POST['charge'][0], 1);

    $hourlyRate = (floatval($_POST['charge']) > 0.00) ? $_POST['charge'] : 0.00; // Default system-wide charge rate.
    $costRate = (floatval($_POST['cost']) > 0.0) ? $_POST['cost'] : 0.00; // Default user cost rate.
    $chargeRate = (floatval($_POST['charge']) > 0.00) ? $_POST['charge'] : 0.00; // Default user charge rate.
    $threshold = 0.01; // Only rates below this number will be updated.

    // Make sure the admin user can see everything.
    resetAdminPermissions();

    // Update the accounting columns and data.
    establishHourlyRate($hourlyRate, $threshold);
    establishUserRates($costRate, $chargeRate, $threshold);
    establishTaskCommentRates($threshold);
    recalculateBudgets();

    // Modify misc tables.
    updateUserColumns();
    setDefaultTaskIndent();
    increaseTaskDescFieldSize();
    increaseClientAddressFieldSize();
    dropSysSettingsTable();
    restrainTaskPriorityValues();

    // Add new tables and view.
    addIndexOnSettings();
    addTimerLogTable();
    addInvoiceItemsOtherTable();
    addActivityLogTable();
    addAutoIncrementingDayIDs();
    createNewDayIDs();
    createTaskCommentsView();
    addParentFolderColumn();
    addFileHistoryDetails();
    addResourceInfo();
    
    addActivityColumnOnTasks();
}

function ensureSlashes($string){
    if (!get_magic_quotes_gpc())
    {
        $string = addslashes($string);
    }
    return trim($string);
}


function parseConfig($filename)
{
    $file = file($filename);
    foreach ($file as $line)
    {
        if (substr($line, 0, 6) == 'define')
             eval($line);
    }
}

function checkPHPVersion($version)
{
    return (version_compare(PHP_VERSION, $version, '>')); 
}

function checkMySQLVersion($needVersion)
{
    // Try to get the server version, but fall back to getting the client library version.
    $db = $GLOBALS['db'];
    $haveVersion = ($db->State() == 1) ? mysql_get_server_info() : mysql_get_client_info();

		// mysqlnd is retarded and has text at the front.
		if (strpos($haveVersion, "mysqlnd") === 0) {

			$version_bits = explode(" ", $haveVersion);
			$haveVersion = $version_bits[1];
		}

		
    return (version_compare($haveVersion, $needVersion, '>'));
}

function resetAdminPermissions()
{
    $db = $GLOBALS['db'];
    $sql = "DELETE FROM `sysUserPermissions` WHERE UserID = 1 AND ObjectID = -1";
    $db->Execute($sql);
    $sql = "INSERT INTO `sysUserPermissions` (`ID`, `UserID`, `ObjectID`, `ItemID`, `AccessID`) VALUES (NULL, 1, 'administration', -1, 2)";
    $db->Execute($sql);
    $sql = "INSERT INTO `sysUserPermissions` (`ID`, `UserID`, `ObjectID`, `ItemID`, `AccessID`) VALUES (NULL, 1, 'budget', -1, 2)";
    $db->Execute($sql);
    $sql = "INSERT INTO `sysUserPermissions` (`ID`, `UserID`, `ObjectID`, `ItemID`, `AccessID`) VALUES (NULL, 1, 'calendar', -1, 2)";
    $db->Execute($sql);
    $sql = "INSERT INTO `sysUserPermissions` (`ID`, `UserID`, `ObjectID`, `ItemID`, `AccessID`) VALUES (NULL, 1, 'clients', -1, 2)";
    $db->Execute($sql);
    $sql = "INSERT INTO `sysUserPermissions` (`ID`, `UserID`, `ObjectID`, `ItemID`, `AccessID`) VALUES (NULL, 1, 'contacts', -1, 2)";
    $db->Execute($sql);
    $sql = "INSERT INTO `sysUserPermissions` (`ID`, `UserID`, `ObjectID`, `ItemID`, `AccessID`) VALUES (NULL, 1, 'files', -1, 2)";
    $db->Execute($sql);
    $sql = "INSERT INTO `sysUserPermissions` (`ID`, `UserID`, `ObjectID`, `ItemID`, `AccessID`) VALUES (NULL, 1, 'projects', -1, 2)";
    $db->Execute($sql);
    $sql = "INSERT INTO `sysUserPermissions` (`ID`, `UserID`, `ObjectID`, `ItemID`, `AccessID`) VALUES (NULL, 1, 'reports', -1, 2)";
    $db->Execute($sql);
    $sql = "INSERT INTO `sysUserPermissions` (`ID`, `UserID`, `ObjectID`, `ItemID`, `AccessID`) VALUES (NULL, 1, 'springboard', -1, 2)";
    $db->Execute($sql);
}

function setAdminCostRates($costRate, $chargeRate, $threshold)
{
  $db = $GLOBALS['db'];
  // the admin user is hard coded as ID: 1
  $sql = "UPDATE tblUsers SET CostRate = '$costRate' WHERE CostRate < '$threshold' AND ID = '1'";
  $db->Execute($sql);

  $sql = "UPDATE tblUsers SET ChargeRate = '$chargeRate' WHERE ChargeRate < '$threshold' AND ID = '1'";
  $db->Execute($sql);
  
}

function addTimerLogTable()
{
    $db = $GLOBALS['db'];
    $sql = "CREATE TABLE IF NOT EXISTS `tblTimerLog` (
    `ID` int(11) NOT NULL auto_increment, 
    `Updated` datetime NOT NULL,
    `UserID` int(11) NOT NULL, 
    `TaskID` int(11) NOT NULL, 
    `Elapsed` time NOT NULL,  
    `Paused` tinyint(1) NOT NULL default '0',   
    PRIMARY KEY  (`ID`) 
  ) ENGINE=InnoDB";   // InnoDB specified because this table will get more writes than reads.
    $db->Execute($sql);
}

function addInvoiceItemsOtherTable()
{
    $db = $GLOBALS['db'];
    $sql = "CREATE TABLE IF NOT EXISTS `tblInvoices_Items_Other` (
    `ID` int(11) NOT NULL auto_increment,
    `ProjectID` tinyint(4) default NULL,
    `TaskName` varchar(255) default NULL,
    `Amount` decimal(10,0) default NULL,
    `Budget` decimal(10,0) default NULL,
    `Quantity` float default NULL,     
    `Cost` decimal(10,0) default NULL, 
    `Charge` decimal(10,0) default NULL, 
    `Logged` decimal(10,0) default NULL,
    PRIMARY KEY  (`ID`)                
  )";
    $db->Execute($sql);
}

function addActivityLogTable()
{
    $db = $GLOBALS['db'];
    $sql = "CREATE TABLE IF NOT EXISTS `tblActivityLog` (
    `ID` int(11) NOT NULL auto_increment, 
    `Timestamp` datetime NOT NULL,       
    `UserID` int(11) NOT NULL,          
    `IP` varchar(20) NOT NULL,         
    `Url` varchar(255) NOT NULL,      
    `Context` varchar(50) NOT NULL,  
    `ContextID` int(11) NOT NULL,   
    `Action` varchar(50) NOT NULL, 
    `Detail` varchar(255) default NULL, 
    `Comment` varchar(255) default NULL,
    PRIMARY KEY  (`ID`)                
  ) ENGINE=InnoDB";   // InnoDB specified because this table will get more writes than reads.
    $db->Execute($sql);
}

function addAutoIncrementingDayIDs()
{
    $db = $GLOBALS['db'];
    $db->Execute('ALTER TABLE `tblDay` CHANGE `ID` `ID` INT( 11 ) NOT NULL AUTO_INCREMENT');
}

function addIndexOnSettings()
{
    $db = $GLOBALS['db'];

    // At the time of writing this script, MySQL did not support DROP INDEX IF EXISTS
    // or CREATE INDEX IF NOT EXISTS, despite being filed as a request in 2005. 

    $keys = array();
    $table = $db->Query("SHOW KEYS FROM sysAdminSettings");
    foreach ($table as $key)
        $keys[] = $key['Key_name'];

    if (!in_array('Setting', $keys))
        $db->Execute('CREATE INDEX Setting ON sysAdminSettings (Setting)');
}

function updateUserColumns()
{
    $db = $GLOBALS['db'];

    $columns = array();
    $table = $db->Query("DESCRIBE tblUsers");
    foreach ($table as $field)
        $columns[] = $field['Field'];

    if (in_array('ConvertToDays', $columns))
        $db->Execute('ALTER TABLE `tblUsers` DROP COLUMN ConvertToDays');

    if (!in_array('IMType', $columns))
        $db->Execute('ALTER TABLE `tblUsers` ADD `IMType` VARCHAR( 20 ) NULL, ADD `IMAccount` VARCHAR( 100 ) NULL');
    else
    {
        $db->Execute('ALTER TABLE `tblUsers` CHANGE `IMType` `IMType` VARCHAR( 20 ) NULL');
        $db->Execute('ALTER TABLE `tblUsers` CHANGE `IMAccount` `IMAccount` VARCHAR( 100 ) NULL');
    }
}

function setDefaultTaskIndent()
{
    $db = $GLOBALS['db'];
    $db->Execute("ALTER TABLE `tblTasks` CHANGE `Indent` `Indent` INT( 11 ) NOT NULL DEFAULT '0'");
// Removed at Ben's request, may need to run it for older clients if their indent levels are screwy (and they may be).
//    $db->Execute("UPDATE tblTasks SET Indent = 0");
}

function increaseTaskDescFieldSize()
{
    $db = $GLOBALS['db'];
    $db->Execute("ALTER TABLE `tblInvoices_Items` CHANGE `TaskDescription` `TaskDescription` TEXT NOT NULL DEFAULT ''");
}

function increaseClientAddressFieldSize()
{
    $db = $GLOBALS['db'];
    $db->Execute("ALTER TABLE `tblClients` CHANGE `Address1` `Address1` VARCHAR( 255 ) NULL DEFAULT NULL");
}

function createNewDayIDs()
{
    $db = $GLOBALS['db'];

    $db->Execute('DELETE FROM tblDay WHERE ID > 2557');
    for ($ts=1356912000, $id=2557, $y = 2012; $y<2016; $id++, $ts+=86400)
    {
        if ($id == 2557) continue;  // Leave this here, id 2557 is the last day of 2012.
        $d = gmdate('j', $ts);
        $m = gmdate('n', $ts);
        $y = gmdate('Y', $ts);
        $dow = gmdate('w');
        $db->Execute("INSERT INTO tblDay (ID, Epoch, Day, Month, Year, Weekday) VALUES ($id,$ts,$d,$m,$y,$dow)");
    }
}

function dropSysSettingsTable()
{
    $db = $GLOBALS['db'];
    $db->Execute('DROP TABLE IF EXISTS `sysSettings`');
}

function restrainTaskPriorityValues()
{
    $db = $GLOBALS['db'];
    $db->Execute('UPDATE tblTasks SET Priority = (Priority - 1)');
}

function createTaskCommentsView()
{
    $db = $GLOBALS['db'];

    //$db->Execute('DROP VIEW IF EXISTS vwTaskComments');
    
    $sql = "CREATE OR REPLACE VIEW vwTaskComments AS 
            SELECT u.ID as UserID, CONCAT(u.FirstName, ' ', u.LastName) AS UserName, 
                c.ID AS ClientID, c.Name AS ClientName, p.ID AS ProjectID, 
                p.Name AS ProjectName, t.ID AS TaskID, t.Name AS TaskName, 
                tc.Date AS Date, tc.HoursWorked AS HoursWorked, tc.CostRate AS CostRate, 
                tc.ChargeRate AS ChargeRate, 
                (tc.HoursWorked * tc.CostRate) AS Cost, 
                (tc.HoursWorked * tc.ChargeRate) AS Charge,
                tc.Issue AS Issue, tc.OutOfScope AS OutOfScope
            FROM tblTasks_Comments tc 
            INNER JOIN tblUsers AS u ON u.ID = tc.UserID
            INNER JOIN tblTasks AS t ON t.ID = tc.TaskID 
            INNER JOIN tblProjects AS p ON p.ID = t.ProjectID 
            INNER JOIN tblClients AS c ON c.ID = p.ClientID";
    $db->Execute($sql);
}

function recalculateBudgets()
{
    $db = $GLOBALS['db'];
    
    $sql = "UPDATE tblTasks SET HoursWorked = (SELECT COALESCE(SUM(HoursWorked), 0) FROM tblTasks_Comments WHERE tblTasks_Comments.TaskID = tblTasks.ID)";
    $db->Execute($sql);

    // Forced to create a temporary table here since MySQL won't let you update
    // a table if you have a view on it and you are selecting from that view.
    $sql = "CREATE TEMPORARY TABLE tmpTaskComments SELECT TaskID, HoursWorked, CostRate, ChargeRate, HoursWorked * CostRate AS Cost, HoursWorked * ChargeRate AS Charge FROM tblTasks_Comments";
    $db->Execute($sql);
    $sql = "UPDATE tblTasks SET ActualBudget = (SELECT COALESCE(SUM(Charge), 0) FROM tmpTaskComments WHERE tmpTaskComments.TaskID = tblTasks.ID)";
    $db->Execute($sql);
    $sql = "DROP TABLE tmpTaskComments";
    $db->Execute($sql);

    $sql = "UPDATE tblProjects SET ActualBudget = (SELECT COALESCE(SUM(ActualBudget), 0) FROM tblTasks WHERE tblTasks.ProjectID = tblProjects.ID)";
    $db->Execute($sql);
}

function establishTaskCommentRates($threshold)
{
    $db = $GLOBALS['db'];

    $columns = array();
    $table = $db->Query("DESCRIBE tblTasks_Comments");
    foreach ($table as $field)
        $columns[] = $field['Field'];

    if (!in_array('CostRate', $columns))
    {
        $sql = "ALTER TABLE tblTasks_Comments ADD COLUMN `CostRate` DECIMAL(10,2) NOT NULL DEFAULT '0.00' AFTER `HoursWorked`";
        $db->Execute($sql);
    }
    else
    {
        $sql = "ALTER TABLE `tblTasks_Comments` CHANGE `CostRate` `CostRate` DECIMAL(10, 2) NOT NULL DEFAULT '0.00'";
        $db->Execute($sql);
    }

    if (!in_array('ChargeRate', $columns))
    {
        $sql = "ALTER TABLE tblTasks_Comments ADD COLUMN `ChargeRate` DECIMAL(10,2) NOT NULL DEFAULT '0.00' AFTER `CostRate`";
        $db->Execute($sql);
    }
    else
    {
        $sql = "ALTER TABLE `tblUsers` CHANGE `ChargeRate` `ChargeRate` DECIMAL(10, 2) NOT NULL DEFAULT '0.00'";
        $db->Execute($sql);
    }

    $sql = "SELECT ID, CostRate, ChargeRate FROM tblUsers";
    $results = $db->Query($sql);
    foreach ($results as $r)
    {
        $sql = "UPDATE tblTasks_Comments SET CostRate = '{$r['CostRate']}' WHERE UserID = '{$r['ID']}' AND CostRate < '$threshold'";
        $db->Execute($sql);

        $sql = "UPDATE tblTasks_Comments SET ChargeRate = '{$r['ChargeRate']}' WHERE UserID = '{$r['ID']}' AND ChargeRate < '$threshold'";
        $db->Execute($sql);
    }
}

function establishUserRates($costRate, $chargeRate, $threshold)
{
    $db = $GLOBALS['db'];

    $columns = array();
    $table = $db->Query("DESCRIBE tblUsers");
    foreach ($table as $field)
        $columns[] = $field['Field'];

    if (!in_array('CostRate', $columns))
    {
        $sql = "ALTER TABLE tblUsers ADD COLUMN `CostRate` DECIMAL(10,2) NOT NULL DEFAULT '0.00' AFTER `Module`";
        $db->Execute($sql);
    }
    else
    {
        $sql = "ALTER TABLE `tblUsers` CHANGE `CostRate` `CostRate` DECIMAL(10, 2) NOT NULL DEFAULT '0.00'";
        $db->Execute($sql);
    }

    if (!in_array('ChargeRate', $columns))
    {
        $sql = "ALTER TABLE tblUsers ADD COLUMN `ChargeRate` DECIMAL(10,2) NOT NULL DEFAULT '0.00' AFTER `CostRate`";
        $db->Execute($sql);
    }
    else
    {
        $sql = "ALTER TABLE `tblUsers` CHANGE `ChargeRate` `ChargeRate` DECIMAL(10, 2) NOT NULL DEFAULT '0.00'";
        $db->Execute($sql);
    }

    $sql = "UPDATE tblUsers SET CostRate = '$costRate' WHERE CostRate < '$threshold'";
    $db->Execute($sql);

    $sql = "UPDATE tblUsers SET ChargeRate = '$chargeRate' WHERE ChargeRate < '$threshold'";
    $db->Execute($sql);
}

function establishHourlyRate($hourlyRate, $threshold)
{
    $db = $GLOBALS['db'];

    $sql = "SELECT Value FROM sysAdminSettings WHERE Setting = 'HourlyRate'";
    $result = $db->QuerySingle($sql);

    if ($result == FALSE)
    {
        $sql = "INSERT INTO sysAdminSettings (Setting, Value) VALUES ('HourlyRate', '$hourlyRate')";
        $result = $db->Execute($sql);
    }
    else if ($result['Value'] < $threshold)
    {
        $sql = "UPDATE sysAdminSettings SET Value = '$hourlyRate' WHERE Setting = 'HourlyRate'";
        $result = $db->Execute($sql);
    }
}

function addParentFolderColumn()
{
    $db = $GLOBALS['db'];

    $columns = array();
    $table = $db->Query("DESCRIBE tblFolders");
    foreach ($table as $field)
        $columns[] = $field['Field'];

    if (!in_array('ParentID', $columns))
    {
        $sql = "ALTER TABLE tblFolders ADD COLUMN `ParentID` INT NOT NULL DEFAULT 0";
        $db->Execute($sql);
    }
}

function addFileHistoryDetails()
{
    $db = $GLOBALS['db'];

    $columns = array();
    $table = $db->Query("DESCRIBE tblFile_Log");
    foreach ($table as $field)
        $columns[] = $field['Field'];

    if (!in_array('FileName', $columns))
    {
        $sql = "ALTER TABLE `tblFile_Log` ADD `FileName` VARCHAR( 255 ) NOT NULL,
            ADD `Type` VARCHAR( 100 ) NOT NULL,
            ADD `Size` INT( 11 ) NOT NULL DEFAULT 0,
            ADD `RealName` VARCHAR( 255 ) NOT NULL";
        $db->Execute($sql);
    }
}

function addResourceInfo()
{
    $db = $GLOBALS['db'];

    $columns = array();
    $table = $db->Query("DESCRIBE tblResource");
    foreach ($table as $field)
        $columns[] = $field['Field'];

    if (!in_array('AvailabilityType', $columns))
    {
        $sql = "ALTER TABLE `tblResource` ADD AvailabilityType int(11) NOT NULL default '0',
            ADD WeekDays varchar(20) NOT NULL";
        $db->Execute($sql);
    }
}

function addActivityColumnOnTasks()
{
    $db = $GLOBALS['db'];

    $columns = array();
    $table = $db->Query("DESCRIBE tblTasks");
    foreach ($table as $field)
        $columns[] = $field['Field'];

    if (!in_array('LatestActivity', $columns))
    {
        $sql = 'ALTER TABLE `tblTasks` 
                    ADD `LatestActivity` timestamp NULL 
                    DEFAULT NULL on update CURRENT_TIMESTAMP 
                    AFTER `ActualBudget`';
                    
        $db->Execute($sql);
    }
}
 
