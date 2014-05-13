<?php
// $Id$
//change_log 4.
define('SQL_COUNT_ACTIVE_USERS',
       'SELECT COUNT(*) AS count FROM tblUsers WHERE Active = 1');

define('SQL_USER_GROUPS','
        SELECT g.Name FROM tblUsers_Groups u
        LEFT JOIN tblGroups g ON g.ID = u.GroupID
        WHERE u.UserID = %s');

define('SQL_SHOW_TABLES','
        SHOW TABLES FROM `%s`');

define('SQL_SHOW_CREATE_TABLE','
        SHOW CREATE TABLE `%s`');

define('SQL_SELECT_FROM','
        SELECT * FROM %s');

define('SQL_SELECT_TIME_ZONES','
        SELECT * FROM sysTimeZones ORDER BY Zone');

define('SQL_GET_SETTING',
       'SELECT * FROM sysAdminSettings WHERE Setting = \'%s\'');

define('SQL_LAST_INSERT', 'SELECT LAST_INSERT_ID()');

define('SQL_USERS_CLEAR_REMOVED', 'DELETE FROM tblUsers_Groups WHERE GroupID = \'%s\'');

define('SQL_USERS_CLEAR_ASSIGNED', 'DELETE FROM tblUsers_Groups WHERE GroupID = \'%s\'');

define('SQL_USERS_CHECK', 'SELECT ID FROM tblUsers_Groups WHERE ID IN (%s) AND GroupID = \'%s\'');

define('SQL_USER_GROUP_CHECK', 'SELECT ID FROM tblUsers_Groups WHERE UserID = \'%d\' AND GroupID = \'%d\'');

define('SQL_USERS_ASSIGN', 'INSERT INTO tblUsers_Groups (UserID, GroupID) VALUES (\'%2$s\',\'%1$s\')');

define('SQL_USERS_REMOVE', 'DELETE FROM tblUsers_Groups WHERE GroupID = \'%1$d\' AND UserID = \'%2$d\'');

define('SQL_GET_GROUP_PERMISSIONS_PROJECT',
       'SELECT s.ID, s.AccessID, s.ItemID, CONCAT(c.Name, \' / \', p.Name) AS Name
       FROM sysGroupPermissions s
       LEFT JOIN tblProjects p ON p.ID = s.ItemID
       LEFT JOIN tblClients c ON c.ID = p.ClientID
       WHERE s.GroupID = \'%s\' AND s.ObjectID = \'projects\' AND s.ItemID <> \'-1\'
       ORDER BY Name ASC');

define('SQL_GET_GROUP_PERMISSIONS_CLIENT',
       'SELECT s.ID, s.AccessID, s.ItemID, c.Name
       FROM sysGroupPermissions s
       LEFT JOIN tblClients c ON c.ID = s.ItemID
       WHERE s.GroupID = \'%s\' AND s.ObjectID = \'clients\' AND s.ItemID <> \'-1\'
        ORDER BY Name ASC');

define('SQL_GET_USER_PERMISSIONS_PROJECT',
       'SELECT s.ID, s.AccessID, s.ItemID, CONCAT(c.Name, \' / \', p.Name) AS Name
       FROM sysUserPermissions s
       LEFT JOIN tblProjects p ON p.ID = s.ItemID
       LEFT JOIN tblClients c ON c.ID = p.ClientID
       WHERE s.UserID = \'%s\' AND s.ObjectID = \'projects\' AND s.ItemID <> \'-1\'
       ORDER BY Name ASC');

define('SQL_GET_USER_PERMISSIONS_CLIENT',
       'SELECT s.ID, s.AccessID, s.ItemID, c.Name
       FROM sysUserPermissions s
       LEFT JOIN tblClients c ON c.ID = s.ItemID
       WHERE s.UserID = \'%s\' AND s.ObjectID = \'clients\' AND s.ItemID <> \'-1\'
        ORDER BY Name ASC');


define('SQL_GET_PROJECTS_LIST',
       'SELECT p.ID, gp.ItemID, p.Name, c.Name AS ClientName
        FROM tblProjects p
        LEFT  JOIN tblClients c ON c.ID = p.ClientID
        LEFT  JOIN sysGroupPermissions gp ON gp.GroupID = \'%s\' AND p.ID = gp.ItemID AND gp.ObjectID =  \'projects\'
        ORDER  BY ClientName ASC');

define('SQL_GET_CLIENTS_LIST',
       'SELECT c.ID, c.Name, gp.ItemID
        FROM tblClients c
        LEFT JOIN sysGroupPermissions gp ON gp.GroupID = \'%s\' AND gp.ItemID = c.ID AND gp.ObjectID = \'clients\'
        ORDER BY Name ASC');

define('SQL_GET_USER_PROJECTS_LIST',
       'SELECT p.ID, up.ItemID, p.Name, c.Name AS ClientName
        FROM tblProjects p
        LEFT  JOIN tblClients c ON c.ID = p.ClientID
        LEFT  JOIN sysUserPermissions up ON up.UserID = \'%s\' AND p.ID = up.ItemID AND up.ObjectID =  \'projects\'
        ORDER  BY ClientName ASC');

define('SQL_GET_USER_CLIENTS_LIST',
       'SELECT c.ID, c.Name, up.ItemID
        FROM tblClients c
        LEFT JOIN sysUserPermissions up ON up.UserID = \'%s\' AND up.ItemID = c.ID AND up.ObjectID = \'clients\'
        ORDER BY Name ASC');

define('SQL_GROUP_PERMISSION_DELETE',
       'DELETE FROM sysGroupPermissions WHERE GroupID = \'%s\' AND ID = \'%s\'');

define('SQL_USER_PERMISSION_DELETE',
       'DELETE FROM sysUserPermissions WHERE UserID = \'%s\' AND ID = \'%s\'');

define('SQL_USER_PERMISSION_INDIVIDUAL_DELETE',
       'DELETE FROM sysUserPermissions WHERE UserID = \'%s\' AND ItemID = \'%s\' AND ObjectID = \'%s\'');

define('SQL_GROUP_PERMISSION_INDIVIDUAL_DELETE',
       'DELETE FROM sysGroupPermissions WHERE GroupID = \'%s\' AND ItemID = \'%s\' AND ObjectID = \'%s\'');

define('SQL_GROUP_PERMISSION_ADD',
       'INSERT INTO sysGroupPermissions (GroupID, ObjectID, ItemID, AccessID)
       VALUES (\'%s\', \'%s\', \'%s\', \'%s\')');

define('SQL_USER_PERMISSION_ADD',
       'INSERT INTO sysUserPermissions (UserID, ObjectID, ItemID, AccessID)
       VALUES (\'%s\', \'%s\', \'%s\', \'%s\')');

//change_log 1. 2.

define('SQL_LIST_USERS',
        'SELECT u.ID, u.Username, CONCAT(u.FirstName, \' \', u.LastName) AS FullName, u.Title, u.EmailAddress, u.Active
        FROM tblUsers u
        WHERE u.Active = 1
        ORDER BY %1$s %2$s');

define('SQL_LIST_USERS_MINUS',
       'SELECT ID, Username, CONCAT(FirstName, \' \', LastName) AS FullName, EmailAddress, Active
        FROM tblUsers
        WHERE ID NOT IN (%1$s) AND Active = 1
        ORDER BY %2$s %3$s');

define('SQL_LIST_USERS_GROUP',
       'SELECT DISTINCT u.ID, u.FirstName, u.LastName
        FROM tblUsers u, tblUsers_Groups t
        WHERE t.GroupID = \'%s\' AND u.ID = t.UserID
        ORDER BY FirstName ASC');

define('SQL_LIST_GROUPS',
       'SELECT ID, Name
        FROM tblGroups
        ORDER BY %1$s %2$s');

define('SQL_GET_USER',
       'SELECT u.*, COALESCE(r.ID, 0) AS IsResource, r.AvailabilityType, r.WeekDays
        FROM tblUsers u
        LEFT JOIN tblResource r ON r.UserID = u.ID
        WHERE u.ID = \'%s\'');

define('SQL_GET_GROUP',
       'SELECT *
        FROM tblGroups
        WHERE ID = \'%s\'');

define('SQL_GET_GROUP_MODULES',
       'SELECT m.Class, m.Name, gp.ObjectID, gp.AccessID, gp.ID
        FROM sysModules m
        LEFT JOIN sysGroupPermissions gp ON gp.GroupID = \'%s\' AND gp.ItemID = -1 AND m.Class = gp.ObjectID
        WHERE m.MenuItem = 1
        ORDER BY m.Class ASC');

define('SQL_GET_USER_MODULES',
       'SELECT m.Class, m.Name, up.ObjectID, up.AccessID, up.ID
        FROM sysModules m
        LEFT JOIN sysUserPermissions up ON up.UserID = \'%s\' AND up.ItemID = -1 AND m.Class = up.ObjectID
        WHERE m.MenuItem = 1
        ORDER BY m.Class ASC');

define('SQL_GET_GROUP_MODULES_LIST',
       'SELECT p.ID, m.Class, m.Name, p.AccessID
        FROM sysGroupPermissions p
        LEFT JOIN sysModules m ON p.ObjectID = m.Class
        WHERE p.ItemID = -1 AND p.GroupID = \'%s\'
        ORDER BY m.Class ASC');

define('SQL_GET_USER_MODULES_LIST',
       'SELECT p.ID, m.Class, m.Name, p.AccessID
        FROM sysUserPermissions p
        LEFT JOIN sysModules m ON p.ObjectID = m.Class
        WHERE p.ItemID = -1 AND p.UserID = \'%s\'
        ORDER BY m.Class ASC');

define('SQL_GET_PROJECTS',
       'SELECT p.ID, p.Name, c.Name AS ClientName
       FROM tblProjects p LEFT JOIN tblClients c ON c.ID = p.ClientID ORDER BY ClientName, Name ASC');

define('SQL_GET_CLIENTS',
       'SELECT ID, Name FROM tblClients ORDER BY Name ASC');

define('SQL_GET_CLIENT_PROJECTS',
       'SELECT ID
        FROM tblProjects
        WHERE ClientID = \'%s\'');

define('SQL_GET_USER_PROJECTS',
       'SELECT s.ID, s.AccessID, s.ItemID, CONCAT(c.Name, \' / \', p.Name) AS Name
       FROM sysSecurity s
       LEFT JOIN tblProjects p ON p.ID = s.ItemID
       LEFT JOIN tblClients c ON c.ID = p.ClientID
       WHERE s.UserID = \'%s\' AND s.ObjectID = \'projects\'');

define('SQL_GET_USER_CLIENTS',
       'SELECT s.ID, s.AccessID, s.ItemID, c.Name
       FROM sysSecurity s
       LEFT JOIN tblClients c ON c.ID = s.ItemID
       WHERE s.UserID = \'%s\' AND s.ObjectID = \'clients\'');

define('SQL_USER_PERMISSION_EXISTS',
       'SELECT * FROM sysUserPermissions WHERE UserID = \'%1$s\' AND ObjectID = \'%2$s\' AND ItemID = \'%3$s\'');

define('SQL_USER_PERMISSION_UPDATE',
       'UPDATE sysUserPermissions SET AccessID = \'%4$s\' WHERE UserID = \'%1$s\' AND ObjectID = \'%2$s\' AND ItemID = \'%3$s\'');

//change_log 1.
define('SQL_CREATE_USER',
       'INSERT INTO tblUsers (Username, Password, Title, FirstName, LastName, EmailAddress, Phone1, Phone2, Phone3, Address1, Address2, City, State, Postcode, Country, Module, CostRate, ChargeRate, Active, EmailNotify, IMType, IMAccount)
       VALUES (\'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'projects\', \'%s\', \'%s\', \'1\', \'%s\', \'%s\', \'%s\')');

define('SQL_CREATE_USER_PERMISSIONS',
       'INSERT INTO sysUserPermissions
        (UserID, ObjectID, ItemID, AccessID)
        VALUES (\'%s\',\'%s\',\'-1\',\'%s\')');

define('SQL_CREATE_GROUP',
       'INSERT INTO tblGroups (Name)
        VALUES (\'%s\')');

define('SQL_CREATE_GROUP_PERMISSIONS',
       'INSERT INTO sysGroupPermissions
        (GroupID, ObjectID, ItemID, AccessID)
        VALUES (\'%s\',\'%s\',\'-1\',\'%s\')');

//change_log 1.
define('SQL_UPDATE_USER',
       'UPDATE tblUsers SET
        Username = \'%s\',
        Password = %s,
        Title = \'%s\',
        FirstName = \'%s\',
        LastName =  \'%s\',
        EmailAddress = \'%s\',
        Phone1 = \'%s\',
        Phone2 = \'%s\',
        Phone3 = \'%s\',
        Address1 = \'%s\',
        Address2 = \'%s\',
        City = \'%s\',
        State = \'%s\',
        Postcode = \'%s\',
        Country = \'%s\',
        Module = Module,
        CostRate = \'%s\',
        ChargeRate = \'%s\',
        EmailNotify = \'%s\',
        IMType = \'%s\',
        IMAccount = \'%s\'
        WHERE ID = \'%s\'');

define('SQL_USER_SETRESOURCE',
       'INSERT INTO tblResource (UserID, AvailabilityType, WeekDays) VALUES (\'%s\', \'%s\', \'%s\')');

define('SQL_USER_UPDATERESOURCE',
       'UPDATE tblResource SET AvailabilityType = %2$s, WeekDays = \'%3$s\' WHERE UserID = %1$s');

define('SQL_USER_UNSETRESOURCE',
       'DELETE FROM tblResource WHERE UserID = \'%s\'');

define('SQL_UPDATE_GROUP',
       'UPDATE tblGroups SET
        Name = \'%s\'
        WHERE ID = \'%s\'');

define('SQL_PASS_FIELD', 'Password');

define('SQL_DELETE_USER_GROUPS',
        'DELETE FROM tblUsers_Groups WHERE UserID = \'%s\'');

define('SQL_DELETE_USER',
        'UPDATE tblUsers SET Active = 0 WHERE ID = \'%s\'');
define('SQL_DELETE_GROUP',
        'DELETE FROM tblGroups WHERE ID = \'%s\'');

define('SQL_DELETE_GROUP_PERMISSIONS',
        'DELETE FROM sysGroupPermissions WHERE GroupID = \'%s\'');
define('SQL_DELETE_USER_PERMISSIONS',
        'DELETE FROM sysUserPermissions WHERE UserID = \'%s\'');

define('SQL_GET_CLIENT_ID',
       'SELECT ItemID
        FROM sysUserPermissions
        WHERE ID = %s');

define('SQL_GET_CLIENT_ID_GROUP',
       'SELECT ItemID
        FROM sysGroupPermissions
        WHERE ID = %s');

define('SQL_GET_PROJECT_IDS',
       'SELECT ItemID
        FROM sysUserPermissions u, tblProjects p
        WHERE u.ObjectID = \'%s\'
        AND u.UserID = %s
        AND u.ItemID = p.ID
        AND p.ClientID = %s');

define('SQL_GET_PROJECT_IDS_GROUP',
       'SELECT ItemID
        FROM sysGroupPermissions g, tblProjects p
        WHERE g.ObjectID = \'%s\'
        AND g.GroupID = %s
        AND g.ItemID = p.ID
        AND p.ClientID = %s');

define('SQL_GET_FULLNAME',
       'SELECT CONCAT(FirstName, \' \', LastName) AS FullName
        FROM tblUsers
        WHERE ID = %s');

define('SQL_GET_GROUP_ACCESS',
       'SELECT ItemID
        FROM sysPermissions
        WHERE GroupID = %s
        AND ItemID <> -1
        AND ObjectID = \'%s\'');

define('SQL_DELETE_LANGUAGE',
       'DELETE FROM tblLanguageOverride
        WHERE LangCode = \'%s\'');

define('SQL_DELETE_LANGUAGE_FILTER',
       'DELETE FROM tblLanguageOverride
        WHERE LangCode = \'%s\'
        AND Token LIKE \'MSG_%s\'');

define('SQL_INSERT_LANGUAGE',
       'INSERT INTO tblLanguageOverride
        (Token, LangCode, Value) VALUES
        (\'%1$s\', \'%2$s\', \'%3$s\')');

// resources

define('SQL_RESOURCE_ID_FOR_USER',
            'SELECT ID
             FROM tblResource
             WHERE UserID = \'%s\'');

// delete resource
define('SQL_DELETE_RESOURCE','DELETE FROM tblResource WHERE ID = %s');
define('SQL_DELETE_TASK_RESOURCE_DAYS','DELETE FROM tblTaskResourceDay WHERE ResourceID = %s');
define('SQL_DELETE_RESOURCE_DAYS','DELETE FROM tblResourceDay WHERE ResourceID = %s');

// set ava
define('SQL_USER_RESOURCE',
            'SELECT tblResource.ID, tblUsers.FirstName, tblUsers.LastName
             FROM tblUsers
             LEFT JOIN tblResource ON tblUsers.ID = tblResource.UserID
             WHERE tblUsers.ID = \'%1$s\'');

//move to calendar space
//define('SQL_USER_RESOURCE_FROM_RESOURCEID',
//            'SELECT tblResource.ID, tblUsers.FirstName, tblUsers.LastName
//             FROM tblResource
//             LEFT JOIN tblUsers ON tblResource.UserID = tblUsers.ID
//             WHERE tblResource.ID = \'%1$s\'');

define('SQL_GET_ID_EPOCH_WEEKDAY_FROM_DAY',
            'SELECT ID, Epoch, Weekday
             FROM tblDay
       WHERE Epoch BETWEEN \'%1$s\' AND \'%2$s\'');

define('SQL_GET_ID_EPOCH_WEEKDAY_FROM_DAY_USERSAVE',
            'SELECT ID
             FROM tblDay
             WHERE Day = "%s" AND Month = "%s" AND Year = "%s"');

define('SQL_GET_HOURS_COMMITTED_OF_TASKS',
            'SELECT tblDay.ID, tblResourceDay.HoursCommittedCache
                FROM tblDay
                LEFT JOIN tblResourceDay ON tblDay.ID = tblResourceDay.DayID
                WHERE tblResourceDay.ResourceID = \'%1$s\'
                AND tblDay.ID > \'%2$s\'
                AND tblDay.ID < \'%3$s\'
                AND tblResourceDay.HoursCommittedCache > 0');

define('SQL_GET_OVER_COMMITTED',
            'SELECT tblTaskResourceDay.TaskID, tblTasks.ProjectID, tblTaskResourceDay.DayID, tblDay.Epoch, tblTasks.Name
                FROM tblTaskResourceDay
                LEFT JOIN tblTasks ON tblTaskResourceDay.TaskID = tblTasks.ID
                LEFT JOIN tblDay ON tblTaskResourceDay.DayID = tblDay.ID
                WHERE tblTaskResourceDay.ResourceID = \'%1$s\'
                AND tblTaskResourceDay.DayID IN (%2$s)
                ORDER BY tblTaskResourceDay.TaskID');

 
