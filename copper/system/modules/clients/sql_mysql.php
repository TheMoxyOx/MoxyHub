<?php
// $Id$
define('SQL_COUNT_TASK_ISSUES',
       'SELECT COUNT(*) AS count FROM tblTasks_Comments WHERE Issue = 1 AND TaskID = \'%d\'');
       
define('SQL_GET_PROJECT_CHARGE',
       'SELECT SUM(Charge) AS Charge
        FROM vwTaskComments
        WHERE ProjectID = %d');

define('SQL_GET_PROJECT_OTHER_ITEMS_COST',
       'SELECT SUM(Cost) AS Cost FROM `tblInvoices_Items_Other` WHERE `ProjectID` = %d');

define('SQL_GET_PROJECT_OTHER_ITEMS_CHARGE',
       'SELECT SUM(Charge) AS Charge FROM `tblInvoices_Items_Other` WHERE `ProjectID` = %d');

define('SQL_GET_HOURS_COMMITTED_ON_TASK_FOR_TOTAL_PROJECT',
            'SELECT tblTaskResourceDay.DayID, tblTaskResourceDay.HoursCommitted, tblTaskResourceDay.HoursCompleted, tblTaskResourceDay.ResourceID
            FROM tblTaskResourceDay
            WHERE TaskID = \'%1$s\'
            ORDER BY DayID');

define('SQL_UPDATE_RESOURCE_DAY_COMMITMENT_CACHE',
                'UPDATE tblResourceDay SET HoursCommittedCache = HoursCommittedCache + \'%3$s\'
                 WHERE ResourceID = \'%1$s\'
                 AND DayID = \'%2$s\'');

define('SQL_GET_LAST_CONTACT','
        SELECT tc.Date, t.ID, p.ID
        FROM tblTasks_Comments tc
        LEFT JOIN tblTasks t ON t.ID = tc.TaskID
        LEFT JOIN tblProjects p ON p.ID = t.ProjectID
        LEFT JOIN tblClients c ON c.ID = p.ClientID
        WHERE c.ID = %s
        AND tc.Contact = %s
        ORDER BY Date DESC
        LIMIT 1');

define('SQL_GET_TASK_BUDGETS','
        SELECT TargetBudget, ActualBudget FROM tblTasks WHERE ProjectID = %s');

define('SQL_COUNT_CLIENTS','SELECT COUNT(ID) FROM tblClients');

define('SQL_LAST_INSERT', 'SELECT LAST_INSERT_ID()');
define('SQL_CLIENTS_LIST',
       'SELECT c.ID, c.Name, c.Colour, u.FirstName, u.LastName,
       COUNT(DISTINCT p1.ID) AS ActiveProjects,
       COUNT(DISTINCT p2.ID) AS InactiveProjects
       FROM tblClients c
       LEFT JOIN tblProjects p1 ON p1.ClientID = c.ID AND p1.Active = 1
       LEFT JOIN tblProjects p2 ON p2.ClientID = c.ID AND p2.Active = 0
       LEFT JOIN tblUsers u ON u.ID = c.Manager
       WHERE c.ID IN (%1$s) AND c.Archived = \'%2$s\'
       GROUP BY c.ID
       ORDER BY %3$s %4$s');

define('SQL_CLIENTS_LIST_ALL',
       'SELECT c.ID, c.Name, u.FirstName, u.LastName,
       COUNT(DISTINCT p1.ID) AS ActiveProjects,
       COUNT(DISTINCT p2.ID) AS InactiveProjects
       FROM tblClients c
       LEFT JOIN tblProjects p1 ON p1.ClientID = c.ID AND p1.Active = 1
       LEFT JOIN tblProjects p2 ON p2.ClientID = c.ID AND p2.Active = 0
       LEFT JOIN tblUsers u ON u.ID = c.Manager
       WHERE c.Archived = \'%1$s\'
       GROUP BY c.ID
       ORDER BY %2$s %3$s');

define('SQL_CREATE_USER_PERMISSIONS',
       'INSERT INTO sysUserPermissions
        (UserID, ObjectID, ItemID, AccessID)
        VALUES (\'%s\', \'clients\',\'%s\',\'%s\')');

define('SQL_GET_USERS',
       'SELECT ID, FirstName, LastName, CONCAT(FirstName, \' \', LastName) AS FullName FROM tblUsers WHERE Active = \'1\' ORDER BY FirstName, LastName');

define('SQL_GET_TASK_ASSIGNED',
        'SELECT DISTINCT u.EmailAddress
        FROM tblUsers u
        LEFT JOIN tblResource ON u.ID = tblResource.UserID
        LEFT JOIN tblTaskResource ON tblResource.ID = tblTaskResource.ResourceID
        LEFT JOIN tblTaskResourceDay ON tblResource.ID = tblTaskResourceDay.ResourceID
        LEFT JOIN tblTasks t ON t.ID = tblTaskResourceDay.TaskID
        WHERE t.ProjectID = \'%1$s\'');

define('SQL_GET_TASK_OWNERS',
        'SELECT DISTINCT u.EmailAddress
        FROM tblUsers u
        LEFT JOIN tblTasks t ON t.Owner = u.ID
        WHERE t.ProjectID = \'%1$s\' AND u.ID = t.Owner');

define('SQL_GETCLIENT',
       'SELECT u.ID, u.FirstName, u.LastName, u.EmailAddress, c.*
        FROM tblClients c
        LEFT JOIN tblUsers u ON u.ID = c.Manager
        WHERE c.ID = \'%s\'');

define('SQL_GETCLIENTNAME',
       'SELECT Name FROM tblClients WHERE ID = \'%s\'');

define('SQL_CLIENTCREATE',
       'INSERT INTO tblClients (Name, Manager, Phone1, Phone2, Phone3, FAX, Address1, Address2, City, State, Country, Postcode, URL, Description, Archived, ContactEmail,Colour)
       VALUES (\'%1$s\', \'%2$s\', \'%3$s\', \'%4$s\', \'%5$s\', \'%6$s\', \'%7$s\', \'%8$s\', \'%9$s\', \'%10$s\', \'%11$s\', \'%12$s\', \'%13$s\', \'%14$s\', \'%15$s\', \'%16$s\',\'%17$s\')');

define('SQL_CLIENTUPDATE',
       'UPDATE tblClients SET Name = \'%1$s\', Phone1 = \'%2$s\', Phone2 = \'%3$s\', Phone3 = \'%4$s\', FAX = \'%5$s\', Address1 = \'%6$s\', Address2 = \'%7$s\', City = \'%8$s\', State = \'%9$s\', Country = \'%10$s\', Postcode = \'%11$s\', URL = \'%12$s\', Description = \'%13$s\', Archived = \'%14$s\', ContactEmail = \'%16$s\', Manager = \'%17$s\', Colour = \'%18$s\' WHERE ID = \'%15$s\'');


define('SQL_GET_PROJECT_IDS',
        'SELECT ID
         FROM tblProjects
         WHERE ClientID = \'%s\'');

define('SQL_GET_TASK_IDS',
        'SELECT ID
         FROM tblTasks
         WHERE ProjectID = \'%s\'');

define('SQL_GET_FILE_IDS',
        'SELECT ID
         FROM tblFiles
         WHERE ProjectID = \'%s\'');

define('SQL_GET_CLIENT_IDS',
        'SELECT ID FROM tblClients');

define('SQL_PROJECTS_LIST_ALL',
       'SELECT
           p.ID, p.Status, p.TargetBudget, p.ActualBudget, p.Priority, p.ClientID, p.Name as ProjectName,
           p.StartDate, p.EndDate, p.Colour, AVG(IFNULL(t.PercentComplete, 0)) AS PercentComplete, MAX(t.LatestActivity) AS LatestActivity,
           u.FirstName, u.LastName, p.Owner, p.Active
        FROM tblProjects AS p
        LEFT JOIN tblUsers AS u ON u.ID = p.Owner
        LEFT JOIN tblTasks AS t ON t.ProjectID = p.ID
        INNER JOIN tblClients AS c ON c.ID = p.ClientID
        WHERE p.Active = \'%1$s\' AND c.ID = \'%4$s\'
        GROUP BY p.ID
        ORDER BY %2$s %3$s');

define('SQL_PROJECTS_LIST',
       'SELECT
           p.ID, p.Status, p.TargetBudget, p.ActualBudget, p.Priority, p.ClientID, p.Name as ProjectName,
           p.StartDate, p.EndDate, p.Colour, AVG(IFNULL(t.PercentComplete, 0)) AS PercentComplete, MAX(t.LatestActivity) AS LatestActivity,
           u.FirstName, u.LastName, p.Owner
        FROM tblProjects AS p
        LEFT JOIN tblUsers AS u ON u.ID = p.Owner
        LEFT JOIN tblTasks AS t ON t.ProjectID = p.ID
        INNER JOIN tblClients AS c ON c.ID = p.ClientID
        WHERE p.ID IN (%1$s) AND p.Active = \'%2$s\' AND c.ID = \'%5$s\'
        GROUP BY p.ID
        ORDER BY %3$s %4$s');

define('SQL_CONTACTS_LIST',
       'SELECT c.ID, c.EmailAddress1, c.KeyContact, c.FirstName, c.LastName, c.Phone1, CONCAT(c.FirstName, \' \', c.LastName) AS ContactName
        FROM tblContacts AS c
        INNER JOIN tblClients AS cl ON cl.ID = c.ClientID
        WHERE cl.ID = \'%3$s\'
        ORDER BY KeyContact DESC, %1$s %2$s');

define('SQL_KEY_CONTACTS_LIST',
       'SELECT c.ID, c.EmailAddress1, c.KeyContact, c.FirstName, c.LastName, c.Phone1, CONCAT(c.FirstName, \' \', c.LastName) AS ContactName
        FROM tblContacts AS c
        INNER JOIN tblClients AS cl ON cl.ID = c.ClientID
        WHERE cl.ID = \'%3$s\' AND KeyContact = 1
        ORDER BY LastName DESC, %1$s %2$s');

define('SQL_FILES_LIST',
       'SELECT f.ID as FileID, f.Version, f.FileName, p.ID as ProjectID, p.Name as ProjectName, t.ID AS TaskID, t.Name AS TaskName, f.Size, f.Date,f.CheckedOut, f.CheckedOutUserID, f.Linked, f.RealName, CONCAT(u.FirstName, \' \',u.LastName) AS UploadedBy
        FROM tblFiles f
        LEFT JOIN tblTasks t ON t.ID = f.TaskID
        LEFT JOIN tblUsers u ON u.ID = f.Owner
        LEFT JOIN tblProjects p ON p.ID = f.ProjectID
        INNER JOIN tblClients c ON c.ID = p.ClientID
        WHERE c.ID = \'%1$s\' AND p.ID IN (%2$s)
        ORDER BY %3$s %4$s');

define('SQL_FILES_LIST_ALL',
       'SELECT f.ID as FileID, f.Version, f.FileName, p.ID as ProjectID, p.Name as ProjectName, t.ID AS TaskID, t.Name AS TaskName, f.Size, f.Date, f.CheckedOut, f.CheckedOutUserID, f.Linked, f.RealName, CONCAT(u.FirstName, \' \',u.LastName) AS UploadedBy
        FROM tblFiles f
        LEFT JOIN tblTasks t ON t.ID = f.TaskID
        LEFT JOIN tblUsers u ON u.ID = f.Owner
        LEFT JOIN tblProjects p ON p.ID = f.ProjectID
        INNER JOIN tblClients c ON c.ID = p.ClientID
        WHERE c.ID = \'%1$s\'
        ORDER BY %2$s %3$s');

define('SQL_GET_FILE_DETAILS',
       'SELECT * FROM tblFiles WHERE ID = \'%s\'');

define('SQL_FILE_CHECKOUT',
        'UPDATE tblFiles SET
         CheckedOut = 1,
         CheckedOutUserID = \'%2$s\'
         WHERE ID = \'%1$s\'');

define('SQL_FILE_LOG',
        'INSERT INTO tblFile_Log (FileID, UserID, Time, Activity, Version)
         VALUES (\'%s\', \'%s\', \'%s\', \'%s\', \'%s\')');

define('SQL_LAST_CHECKED_OUT',
        'SELECT MAX(f.Time) AS Time, CONCAT(u.FirstName,\' \',u.LastName) AS Name
        FROM tblFile_Log f
        LEFT JOIN tblUsers u ON u.ID = UserID
        WHERE f.FileID = \'%1$s\' AND f.Activity=\'Checked Out\'
        GROUP BY f.UserID');

define('SQL_DELETE_GROUP_PERMS',
       'DELETE FROM sysGroupPermissions
        WHERE (ObjectID = \'%s\'
        AND ItemID = %s)');

define('SQL_DELETE_USER_PERMS',
       'DELETE FROM sysUserPermissions
        WHERE (ObjectID = \'%s\'
        AND ItemID = %s)');

define('SQL_DELETE_PROJECT',
       'DELETE FROM tblProjects
        WHERE ID = %s');

define('SQL_DELETE_CLIENT',
       'DELETE FROM tblClients
        WHERE ID = %s');

define('SQL_DELETE_CONTACTS',
       'DELETE FROM tblContacts
        WHERE ClientID = %s');

define('SQL_DELETE_TASK',
       'DELETE FROM tblTasks
        WHERE ID = %s');

define('SQL_DELETE_FILE',
       'DELETE FROM tblFiles
        WHERE ID = %s');

define('SQL_DELETE_FILE_LOGS',
       'DELETE FROM tblFile_log
        WHERE FileID = %s');

define('SQL_DELETE_TASK_DEPENDENCIES',
       'DELETE FROM tblTasks_Dependencies
        WHERE TaskID = %s');

define('SQL_DELETE_TASK_COMMENTS',
       'DELETE FROM tblTasks_Comments
        WHERE TaskID = %s');

define('SQL_DELETE_DELEGATED_TASKS',
       'DELETE FROM tblTaskResourceDay
        WHERE TaskID = %s');

define('SQL_DELETE_TASK_RESOURCES',
       'DELETE FROM tblTaskResource
        WHERE TaskID = %s');

/////

// begin new code by Niveus 2005-05 for new Gantt chart

define('SQL_GANTT_TASK_CREATE',
       'INSERT INTO tblTasks (ProjectID, Name, StartDate, EndDate, Duration, Sequence, Description, RelatedURL)
        VALUES (\'%1$s\', \'%2$s\', \'%3$s\', \'%4$s\', \'%5$s\', \'%6$s\', \'\', \'\')');

define('SQL_GANTT_TASK_NAME_SAVE',
       'UPDATE tblTasks SET Name = \'%2$s\' WHERE ID = \'%1$s\'');

define('SQL_GANTT_TASK_STARTDATE_SAVE',
       'UPDATE tblTasks SET StartDate = \'%2$s\' WHERE ID = \'%1$s\'');

define('SQL_GANTT_TASK_ENDDATE_SAVE',
       'UPDATE tblTasks SET EndDate = \'%2$s\' WHERE ID = \'%1$s\'');

define('SQL_GANTT_TASK_DURATION_SAVE',
       'UPDATE tblTasks SET Duration = \'%2$s\' WHERE ID = \'%1$s\'');

define('SQL_GANTT_TASK_REORDER_DOWN',
       'UPDATE tblTasks SET sequence = sequence - 1 WHERE sequence >= \'%1$s\' AND sequence <= \'%2$s\'');

define('SQL_GANTT_TASK_REORDER_UP',
       'UPDATE tblTasks SET sequence = sequence + 1 WHERE sequence >= \'%2$s\' AND sequence <= \'%1$s\'');

define('SQL_GANTT_TASK_REORDER_SET',
       'UPDATE tblTasks SET sequence = \'%2$s\' WHERE ID = \'%1$s\'');
// end new code

// start new code for gantt by orca 11/06
// used by gantt data/ save

define('SQL_GET_ACTIVE_PROJECT_IDS',
        'SELECT ID
         FROM tblProjects
         WHERE ClientID = \'%s\'
         AND Active = 1');


define('SQL_GET_TASK_RESOURCES',
            'SELECT DISTINCT tblResource.ID, CONCAT(tblUsers.FirstName, \' \', tblUsers.LastName) AS FullName
             FROM tblResource
             LEFT JOIN tblUsers ON tblResource.UserID = tblUsers.ID
             LEFT JOIN tblTaskResourceDay ON tblResource.ID = tblTaskResourceDay.ResourceID
             LEFT JOIN tblTasks ON tblTasks.ID = tblTaskResourceDay.TaskID
             WHERE tblTasks.ID = \'%1$s\'
             ORDER BY tblResource.ID');

define('SQL_GET_DAYID_EPOCH',
            'SELECT ID AS DayID, Epoch, Day, Month
             FROM tblDay
             WHERE Epoch >= \'%1$s\' AND Epoch <= \'%2$s\'
             ORDER BY ID');

define('SQL_GET_HOURS_COMMITTED_ON_TASK',
            'SELECT tblTaskResourceDay.DayID, tblTaskResourceDay.ResourceID,
            tblTaskResourceDay.HoursCommitted, tblTaskResourceDay.HoursCompleted
            FROM tblTaskResourceDay
            WHERE TaskID =  \'%1$s\'
            AND %2$s
            ORDER BY ResourceID, DayID');

define('SQL_GET_MIN_MAX_TASK_DATES',
       'SELECT
                MIN(t.StartDate) AS StartDate,
                MAX(t.EndDate) AS EndDate
        FROM tblTasks t
        WHERE 1 = 1
                %1$s');

define('SQL_GET_HOURS_COMMITTED_ON_TASK_FOR_TOTAL',
            'SELECT tblTaskResourceDay.DayID, tblTaskResourceDay.HoursCommitted, tblTaskResourceDay.HoursCompleted
            FROM tblTaskResourceDay
            WHERE TaskID =  \'%1$s\'
            AND %2$s
            ORDER BY DayID');

define('SQL_UPDATE_PROJECT_DATES',
            'UPDATE tblProjects SET StartDate = \'%1$s\', EndDate = \'%2$s\' WHERE ID = %3$s');

define('SQL_UPDATE_TASK_DATES',
            'UPDATE tblTasks SET StartDate = \'%1$s\', EndDate = \'%2$s\' WHERE ID = %3$s');

define('SQL_GANTT_PROJECT_ENDDATE_SAVE',
       'UPDATE tblProjects SET EndDate = \'%2$s\' WHERE ID = \'%1$s\'');

define('SQL_GET_PROJECT_NAME_DATES_COLOUR',
            'SELECT Name, StartDate, EndDate, Colour
            FROM tblProjects WHERE ID = %s');

define('SQL_GET_PROJECTS_TASKS_START_END_DATE',
            'SELECT ID, StartDate, EndDate
            FROM tblTasks
            WHERE ProjectID = %s');

define('GET_TASK_DATES',
            'SELECT ID, ProjectID, Name, StartDate, EndDate,
             Duration AS DurationEstimate
             FROM tblTasks
             WHERE ID = \'%1$s\'');

define('SQL_GET_DAYID',
            'SELECT ID
             FROM tblDay
             WHERE Epoch >= \'%1$s\' AND Epoch <= \'%2$s\'
             ORDER BY ID');

define('SQL_DELETE_TASK_RESOURCE_DAY',
            'DELETE FROM tblTaskResourceDay
             WHERE TaskID = %1$s
             AND (DayID < %2$s OR DayID > %3$s)
             AND HoursCompleted = 0');

define('SQL_GET_TASKLIST',
       'SELECT t.ID, t.Name
        FROM tblTasks t
        WHERE t.ProjectID = \'%s\'');

// end code by orca

define('SQL_GET_FOLDER_NAME',
        'SELECT f.Folder FROM tblFolders f WHERE f.ID = %s');

define('SQL_GET_FOLDER_DETAILS',
        'SELECT f.Folder, f.ProjectID, p.Name 
         FROM tblFolders f 
         LEFT JOIN tblProjects p ON f.ProjectID = p.ID
         WHERE f.ID = %s');

define('SQL_GET_FOLDERS',
       'SELECT COUNT(f.ID) AS count, fo.ID, fo.Folder, p.ID AS ProjectID, p.Name
        FROM tblFiles f 
        LEFT JOIN tblFolders fo ON f.Folder = fo.ID 
        LEFT JOIN tblProjects p ON f.ProjectID = p.ID 
        WHERE f.ProjectID > 0 AND f.Folder > 0 AND p.ClientID = %1$s 
        GROUP BY f.Folder
        ORDER BY %2$s');

define('SQL_GET_FOLDERS_FOR_PROJECTS',
       'SELECT COUNT(f.ID) AS count, fo.ID, fo.Folder, fo.ProjectID, p.Name 
        FROM tblFiles f 
        LEFT JOIN tblFolders fo ON f.Folder = fo.ID 
        LEFT JOIN tblProjects p ON f.ProjectID = p.ID 
        WHERE p.ClientID = %1$s AND f.ProjectID IN (%2$s) AND f.Folder > 0 
        GROUP BY f.Folder
        ORDER BY %3$s');

define('SQL_COUNT_FILES_IN_FOLDER',
        'SELECT COUNT(f.ID) AS count 
         FROM tblFiles f
         LEFT JOIN tblProjects p ON f.ProjectID = p.ID
         WHERE Folder = %1$s AND p.ClientID = %2$s');

define('SQL_COUNT_FILES_IN_FOLDER_FOR_PROJECTS',
        'SELECT COUNT(f.ID) AS count 
         FROM tblFiles f
         LEFT JOIN tblProjects p ON f.ProjectID = p.ID
         WHERE Folder = %1$s AND p.ClientID = %2$s AND f.ProjectID IN (%3$s)');

define('SQL_GET_FILES_IN_FOLDER', 
       'SELECT f.ID, f.FileName, fo.Folder, f.ProjectID, f.TaskID, f.Description,  
          f.Version, f.Size, f.Date, f.Type, f.CheckedOut, f.CheckedOutUserID, f.RealName, f.Linked, 
          u.FirstName, u.LastName, p.Name AS Project, t.Name AS TaskName 
        FROM tblFiles AS f 
        LEFT JOIN tblUsers AS u ON u.ID = f.Owner 
        LEFT JOIN tblFolders fo ON fo.ID = f.Folder 
        LEFT JOIN tblProjects p ON f.ProjectID = p.ID 
        LEFT JOIN tblTasks t on f.TaskID = t.ID 
        WHERE f.Folder = %1$s AND p.ClientID = %2$s
        ORDER BY %3$s'); 

define('SQL_GET_FILES_IN_FOLDER_FOR_PROJECTS', 
       'SELECT f.ID, f.FileName, fo.Folder, f.ProjectID, f.TaskID, f.Description,  
          f.Version, f.Size, f.Date, f.Type, f.CheckedOut, f.CheckedOutUserID, f.RealName, f.Linked, 
          u.FirstName, u.LastName, p.Name AS Project, t.Name AS TaskName 
        FROM tblFiles AS f 
        LEFT JOIN tblUsers AS u ON u.ID = f.Owner 
        LEFT JOIN tblFolders fo ON fo.ID = f.Folder 
        LEFT JOIN tblProjects p ON f.ProjectID = p.ID 
        LEFT JOIN tblTasks t on f.TaskID = t.ID 
        WHERE f.Folder = %1$s AND p.ClientID = %2$s AND f.ProjectID IN (%3$s)
        ORDER BY %4$s'); 

define( 'SQL_GET_GROUPS',
        'SELECT ID, Name FROM tblGroups ORDER BY Name ASC' );


define('SQL_GET_USERS_WITH_READ_PERMS','
        SELECT u.ID, CONCAT( u.FirstName, \' \', u.LastName ) AS FullName
        FROM sysUserPermissions p
        LEFT JOIN tblUsers u ON u.ID = p.UserID
        WHERE ObjectID = \'%s\'
        AND ItemID = %s
        AND AccessID = 1
        AND u.Active = 1
        ORDER BY FullName');

define('SQL_GET_USERS_WITH_WRITE_PERMS','
        SELECT u.ID, CONCAT( u.FirstName, \' \', u.LastName ) AS FullName
        FROM sysUserPermissions p
        LEFT JOIN tblUsers u ON u.ID = p.UserID
        WHERE ObjectID = \'%s\'
        AND ItemID = %s
        AND AccessID = 2
        AND u.Active = 1
        ORDER BY FullName');

define('SQL_GET_GROUPS_WITH_READ_PERMS','
        SELECT g.ID, g.Name
        FROM sysGroupPermissions p
        LEFT JOIN tblGroups g ON g.ID = p.GroupID
        WHERE p.ObjectID = \'%s\'
        AND p.ItemID = %s
        AND p.AccessID = 1
        ORDER BY g.Name');

define('SQL_GET_GROUPS_WITH_WRITE_PERMS','
        SELECT g.ID, g.Name
        FROM sysGroupPermissions p
        LEFT JOIN tblGroups g ON g.ID = p.GroupID
        WHERE p.ObjectID = \'%s\'
        AND p.ItemID = %s
        AND p.AccessID = 2
        ORDER BY g.Name');

define('SQL_CLEAR_USER_PERMISSIONS',
       'DELETE FROM sysUserPermissions WHERE ObjectID = \'clients\' AND ItemID = \'%s\'');
define('SQL_CLEAR_GROUP_PERMISSIONS',
       'DELETE FROM sysGroupPermissions WHERE ObjectID = \'clients\' AND ItemID = \'%s\'');

//define('SQL_CREATE_USER_READ_PERMISSIONS',
//       'INSERT INTO sysUserPermissions
//        (UserID, ObjectID, ItemID, AccessID)
//        VALUES (\'%s\', \'clients\',\'%s\', \'1\')');
//
//define('SQL_CREATE_GROUP_READ_PERMISSIONS',
//       'INSERT INTO sysGroupPermissions
//        (GroupID, ObjectID, ItemID, AccessID)
//        VALUES (\'%s\', \'clients\',\'%s\', \'1\')');
//
//define('SQL_CREATE_USER_WRITE_PERMISSIONS',
//       'INSERT INTO sysUserPermissions
//        (UserID, ObjectID, ItemID, AccessID)
//        VALUES (\'%s\', \'clients\',\'%s\', \'2\')');
//
//define('SQL_CREATE_GROUP_WRITE_PERMISSIONS',
//       'INSERT INTO sysGroupPermissions
//        (GroupID, ObjectID, ItemID, AccessID)
//        VALUES (\'%s\', \'clients\',\'%s\', \'2\')');

define('SQL_GET_USERNAME','
        SELECT CONCAT(FirstName, \' \', LastName)
        FROM tblUsers
        WHERE ID = %s');

define('SQL_GET_GROUP_NAME',
       'SELECT Name FROM tblGroups WHERE ID = %d');


define('SQL_CREATE_USER_PERMISSIONS',
       'INSERT INTO sysUserPermissions (UserID, ObjectID, ItemID, AccessID)
        VALUES (\'%s\', \'clients\',\'%s\',\'%s\')');

define('SQL_DELETE_USER_PERMISSIONS',
       'DELETE FROM sysUserPermissions 
        WHERE UserID = %1$d
        AND ObjectID = \'clients\' AND ItemID = %2$d');

define('SQL_CREATE_GROUP_PERMISSIONS',
       'INSERT INTO sysGroupPermissions (GroupID, ObjectID, ItemID, AccessID)
        VALUES (\'%s\', \'clients\',\'%s\',\'%s\')');

define('SQL_DELETE_GROUP_PERMISSIONS',
       'DELETE FROM sysGroupPermissions 
        WHERE GroupID = %1$d
        AND ObjectID = \'clients\' AND ItemID = %2$d');

define( 'SQL_GET_GROUPS_MINUS',
        'SELECT ID, Name FROM tblGroups WHERE ID NOT IN (%s) ORDER BY Name ASC' );

define( 'SQL_GET_USERS_MINUS',
        'SELECT ID, FirstName, LastName FROM tblUsers WHERE Active = \'1\' AND ID NOT IN (%s) ORDER BY FirstName, LastName' );


 
