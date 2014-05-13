<?php
// $Id$
define('SQL_USER_RESOURCE_FROM_RESOURCEID',
      'SELECT tblResource.ID, tblUsers.FirstName, tblUsers.LastName
       FROM tblResource
       LEFT JOIN tblUsers ON tblResource.UserID = tblUsers.ID
       WHERE tblResource.ID = \'%1$s\'');

define('SQL_GET_ID_EPOCH_WEEKDAY_FROM_DAY',
      'SELECT ID, Epoch, Weekday
       FROM tblDay
       WHERE Epoch BETWEEN \'%1$s\' AND \'%2$s\'');

define('SQL_GET_HOURS_COMMITTED_OF_TASKS',
      'SELECT tblDay.ID, tblResourceDay.HoursCommittedCache
        FROM tblDay
        LEFT JOIN tblResourceDay ON tblDay.ID = tblResourceDay.DayID
        WHERE tblResourceDay.ResourceID = \'%1$s\'
        AND tblDay.ID > \'%2$s\'
        AND tblDay.ID < \'%3$s\'
        AND tblResourceDay.HoursCommittedCache > 0');

define('SQL_USER_RESOURCE',
      'SELECT tblResource.ID, tblUsers.FirstName, tblUsers.LastName, tblResource.AvailabilityType, tblResource.WeekDays
       FROM tblUsers
       LEFT JOIN tblResource ON tblUsers.ID = tblResource.UserID
       WHERE tblUsers.ID = \'%1$s\'');

define('SQL_GETUSER_BYUSERNAME',
       'SELECT * FROM tblUsers WHERE Username = \'%1$s\'');

define('SQL_GETDETAILSEMAIL',
       'SELECT ID, Username, EmailAddress FROM tblUsers WHERE EmailAddress = \'%1$s\'');

define('SQL_UPDATEPASSWORD',
       'UPDATE tblUsers SET Password = \'%2$s\' WHERE ID = \'%1$s\'');

define('SQL_UPDATETIMESTAMP',
       'UPDATE sysAdminSettings SET Value = %s WHERE Setting = \'FirstLogin\'');

define('SQL_GET_TASKS',
       'SELECT *
        FROM tblTasks
        WHERE PercentComplete < 100
        AND EndDate = date_add( \'%s\', INTERVAL %s DAY)');


//change_log 1.
define('SQL_GET_TASK_OWNER',
        'SELECT DISTINCT u.EmailAddress, CONCAT(u.FirstName, \' \', u.LastName) AS FullName, u.FirstName
        FROM tblUsers u
        LEFT JOIN tblTasks t ON t.Owner = u.ID
        WHERE t.ID = \'%1$s\' AND u.EmailNotify = 1');

define('SQL_CREATE_USER',
       'INSERT INTO tblUsers (Username, Password, Title, FirstName, LastName, EmailAddress, Phone1, Phone2, Phone3, Address1, Address2, City, State, Postcode, Country, Module, CostRate, ChargeRate, Active)
       VALUES (\'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'springboard\', \'%s\', \'%s\', \'1\')');

define('SQL_USER_SETRESOURCE', 
       'INSERT INTO tblResource (UserID) VALUES (\'%1$s\')');

define('SQL_CREATE_USER_PERMISSIONS',
        'INSERT INTO sysUserPermissions
        (UserID, ObjectID, ItemID, AccessID)
        VALUES (\'%s\',\'%s\',\'-1\',\'%s\')');

define('SQL_LAST_INSERT', 'SELECT LAST_INSERT_ID()');
 
