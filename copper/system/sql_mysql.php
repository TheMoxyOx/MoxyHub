<?php
// $Id$
// define('CU_SQL_GET_NEWEST_TIMER',
//        'SELECT tl.*, t.Name AS TaskName, p.ID AS ProjectID, p.Name AS ProjectName 
//         FROM tblTimerLog tl 
//         LEFT JOIN tblTasks t ON tl.TaskID = t.ID
//         LEFT JOIN tblProjects p ON t.ProjectID = p.ID
//         WHERE UserID = %d 
//         ORDER BY Updated DESC LIMIT 1');

// define('CU_SQL_GET_TIMER',
//        'SELECT tl.*, t.Name AS TaskName, p.ID AS ProjectID, p.Name AS ProjectName
//         FROM tblTimerLog 
//         LEFT JOIN tblTasks t ON tl.TaskID = t.ID
//         LEFT JOIN tblProjects p ON t.ProjectID = p.ID
//         WHERE UserID = %d AND TaskID = %d');

// define('CU_SQL_INSERT_TIMER',
//        'INSERT INTO tblTimerLog(ID, Updated, UserID, TaskID, Elapsed, Paused)
//         VALUES (NULL, NOW(), %d, %d, \'%s\', %d)');

// define('CU_SQL_UPDATE_TIMER',
//        'UPDATE tblTimerLog SET Updated = NOW(), Elapsed = \'%s\', Paused = %d
//         WHERE UserID = %d AND TaskID = %d');

define('CU_SQL_REMOVE_TIMER',
        'DELETE FROM tblTimerLog WHERE UserID = %d');

define('CU_SQL_GET_ACTIVITY_DAY',
       'SELECT COUNT(*) AS Comments, COALESCE(SUM(HoursWorked), 0) AS Hours
        FROM tblTasks_Comments
        WHERE DATE(Date) = "%s" AND UserID = %d');

define('CU_SQL_GET_ACTIVITY_WEEK',
       'SELECT COUNT(*) AS Comments, COALESCE(SUM(HoursWorked), 0) AS Hours 
        FROM tblTasks_Comments 
        WHERE YEARWEEK(Date, %1$d) = YEARWEEK("%3$s", %1$d) AND UserID = %2$s');

define('CU_SQL_LOG_ACTION',
       'INSERT INTO tblActivityLog (Timestamp, UserID, IP, Url, Context, ContextID, Action, Detail, Comment) 
        VALUES (\'%s\', \'%d\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', %s, %s)');

define('CU_SQL_GET_SETTING', 
       'SELECT Value FROM sysAdminSettings WHERE Setting = \'%s\'');

define('CU_SQL_GET_DEFAULT_RATE',
       'SELECT Value FROM sysAdminSettings WHERE Setting = \'HourlyRate\'');

define('CU_SQL_GET_TIME_ZONE','
        SELECT tz.Zone FROM sysAdminSettings a 
        LEFT JOIN sysTimeZones tz ON tz.ID = a.Value
        WHERE Setting = \'TimeZone\'');

define('CU_SQL_SELECT_ASSIGNED_TASKS','
        SELECT DISTINCT
          t.ID
        FROM
          tblTasks t
          INNER JOIN tblProjects p ON p.ID = t.ProjectID
          INNER JOIN tblTaskResourceDay d ON d.TaskID = t.ID
          INNER JOIN tblResource r ON r.ID = d.ResourceID
        WHERE
          r.UserID = \'%s\'
          AND t.Status = 0
          AND p.Active = 1');

define('CU_SQL_ADMIN_SETTINGS','
        SELECT Setting, Value FROM sysAdminSettings');

define('CU_SQL_SYSTEM_MODULES',
       'SELECT s.ID, s.Class, s.IsPublic, s.Name, s.Order 
       FROM sysModules AS s
       WHERE MenuItem = 1 AND s.Order > 0
       ORDER BY s.Order ASC');

define('CU_SQL_MENU_MODULES',
       'SELECT s.ID, s.Class, s.IsPublic, s.Name, s.Order
       FROM sysModules AS s
       WHERE MenuItem = 1 AND s.Order > 0
       ORDER BY s.Order ASC');

define('CU_SQL_DENIED_PROJECT',
       'SELECT ProjectID FROM sysProjectPermissions WHERE UserID = \'%s\'');

define('CU_SQL_USER_DETAILS',
       'SELECT * 
        FROM tblUsers u WHERE u.ID = \'%s\'');

define('CU_SQL_USER_PERMISSIONS',
      'SELECT u.ObjectID, u.ItemID, MAX( u.AccessID ) AS uAccess
      FROM sysUserPermissions u
      WHERE u.UserID = \'%1$s\'
      GROUP BY u.ObjectID, u.ItemID
      ORDER BY u.ObjectID ASC , u.AccessID DESC');

define('CU_SQL_USER_MODULE_PERMISSIONS',
      'SELECT p.ObjectID, p.ItemID, MAX( p.AccessID ) AS AccessID
      FROM sysUserPermissions p
      WHERE p.UserID = \'%1$s\'
      AND p.ItemID = -1
      GROUP BY p.ObjectID
      ORDER BY p.ObjectID ASC, p.AccessID DESC');

define('CU_SQL_GROUP_MODULE_PERMISSIONS','
        SELECT g.ObjectID, g.ItemID, MAX( g.AccessID ) AS AccessID
        FROM sysGroupPermissions g, tblUsers_Groups t
        WHERE t.GroupID = g.GroupID
        AND %1$s = t.UserID
        AND g.ItemID = -1
        GROUP BY ObjectID
        ORDER BY g.ObjectID, g.AccessID DESC');

define('CU_SQL_USER_OBJECT_PERMISSIONS',
      'SELECT p.ObjectID, p.ItemID, MAX( p.AccessID ) AS AccessID
      FROM sysUserPermissions p
      WHERE p.UserID = \'%1$s\'
      AND p.ObjectID = \'%2$s\'
      AND p.ItemID != -1
      GROUP BY p.ItemID
      ORDER BY p.ItemID DESC');

define('CU_SQL_GROUP_OBJECT_PERMISSIONS','
        SELECT g.ObjectID, g.ItemID, MAX( g.AccessID ) AS AccessID
        FROM sysGroupPermissions g, tblUsers_Groups t
        WHERE t.GroupID = g.GroupID
        AND %1$s = t.UserID
        AND g.ObjectID = \'%2$s\'
        AND g.ItemID != -1
        GROUP BY g.ItemID
        ORDER BY g.ItemID, g.AccessID DESC');

define('SQL_GET_PAGES_VISITED',
       'SELECT Timestamp, Context, ContextID, Detail, Comment
        FROM tblActivityLog
        WHERE UserID = %d
        ORDER BY Timestamp DESC LIMIT 5');

define('CU_SQL_GROUP_PERMISSIONS','
    SELECT DISTINCT g.ObjectID, g.ItemID, g.AccessID AS gAccess, g.GroupID, u.AccessID AS uAccess
FROM sysGroupPermissions g, tblUsers_Groups t 
LEFT JOIN sysUserPermissions u ON u.ObjectID = g.ObjectID
WHERE %1$s = t.UserID 
AND t.GroupID = g.GroupID 
AND u.UserID = %1$s
ORDER BY g.ObjectID, g.AccessID DESC');

define('CU_SQL_SESSIONS_COUNT', 
       'SELECT COUNT(ID) FROM sysSessions');

define('CU_SQL_SESSIONS_GETID', 
       'SELECT ID FROM sysSessions WHERE ID = \'%s\'');

define('CU_SQL_SESSIONS_HEARTBEAT', 
       'UPDATE sysSessions SET Timeout = \'%s\' WHERE ID = \'%s\'');

// note this is used in files atm. 
define('CU_SQL_SESSIONS_DATA',
       'SELECT Data FROM sysSessions WHERE ID = \'%s\'');


define('CU_SQL_SESSIONS_CREATE',
       'INSERT INTO sysSessions (ID, Timeout) VALUES (\'%s\', \'%s\')');

define('CU_SQL_LANGUAGE_OVERRIDE',
       'SELECT * FROM tblLanguageOverride WHERE LangCode = (SELECT Value FROM sysAdminSettings WHERE Setting = "DefaultLanguage")');

define('CU_SQL_GET_PROJECT_TASKS',
       'SELECT ID, Name FROM tblTasks WHERE ProjectID = %d ORDER BY Name ASC');
 
