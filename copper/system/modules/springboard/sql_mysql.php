<?php
// $Id$
define('SQL_COMPLETE_TASK','UPDATE tblTasks SET Status = 1 WHERE ID = %s');

define('SQL_GET_TASK_TIMES_FOR_GROUP_MEMBERS',
       'SELECT tl.Updated, tl.TaskID, tl.Elapsed, t.Name AS TaskName, t.ProjectID, CONCAT(u.FirstName, \' \', u.LastName) AS Name
        FROM tblTimerLog tl
        LEFT JOIN tblTasks t ON t.ID = tl.TaskID
        LEFT JOIN tblUsers u ON u.ID = tl.UserID
        LEFT JOIN tblTimerLog tl2 ON tl.UserID = tl2.UserID AND tl.Updated < tl2.Updated
        WHERE tl.UserID IN 
        (
          SELECT ug2.UserID
          FROM tblUsers_Groups ug
          LEFT JOIN tblUsers_Groups ug2 ON ug.GroupID = ug2.GroupID
          WHERE ug.UserID = %1$d AND ug2.UserID != %1$d
        )
        AND tl2.Updated IS NULL  
        ORDER BY Updated DESC
        LIMIT 10');

define('SQL_UPDATE_COMMENT','
        UPDATE tblTasks_Comments SET
        UserID = \'%1$s\',
        Body = \'%3$s\',
        HoursWorked = \'%4$s\',
        Date = \'%8$s\',
        Issue = \'%5$s\',
        Contact = \'%6$s\',
        OutOfScope = \'%7$s\',
        CostRate = \'%9$s\',
        ChargeRate = \'%10$s\'
        WHERE ID = %2$s');

define('SQL_FILE_CREATE',
       'INSERT INTO tblFiles (FileName, Description, Type, Owner, Date, Size, Version, RealName, Folder, Linked, ProjectID, TaskID)
       VALUES (\'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', %d, %d)');

define('SQL_FILE_LOG',
        'INSERT INTO tblFile_Log (FileID, UserID, Time, Activity, Version)
         VALUES (\'%s\', \'%s\', \'%s\', \'%s\', \'%s\')');

define('SQL_GET_TIMER',
       'SELECT tl.*, t.Name AS TaskName, p.ID AS ProjectID, p.Name AS ProjectName
        FROM tblTimerLog tl 
        LEFT JOIN tblTasks t ON tl.TaskID = t.ID
        LEFT JOIN tblProjects p ON t.ProjectID = p.ID
        WHERE UserID = %d AND TaskID = %d');

define('SQL_INSERT_TIMER',
       'INSERT INTO tblTimerLog (ID, Updated, UserID, TaskID, Elapsed, Paused)
        VALUES (NULL, \'%s\', %d, %d, \'%s\', %d)');

define('SQL_UPDATE_TIMER',
       'UPDATE tblTimerLog SET Updated = \'%s\', Elapsed = \'%s\'
        WHERE UserID = %d AND TaskID = %d');

define('SQL_TOGGLE_PAUSE_TIMER',
        'UPDATE tblTimerLog SET Updated = \'%s\', Paused = %d
        WHERE UserID = %d AND TaskID = %d');


define('SQL_GET_ACTIVITY_DAY',
       'SELECT COUNT(*) AS Comments, COALESCE(SUM(HoursWorked), 0) AS Hours
        FROM tblTasks_Comments
        WHERE DATE(Date) = CURDATE() AND UserID = %d');

define('SQL_GET_ACTIVITY_WEEK',
       'SELECT COUNT(*) AS Comments, COALESCE(SUM(HoursWorked), 0) AS Hours 
        FROM tblTasks_Comments 
        WHERE YEARWEEK(Date, %1$d) = YEARWEEK(NOW(), %1$d) AND UserID = %2$s');

define('SQL_GET_ACTIVITY_MONTH',
       'SELECT COUNT(*) AS Comments, COALESCE(SUM(HoursWorked), 0) AS Hours 
        FROM tblTasks_Comments 
        WHERE MONTH(Date) = MONTH(NOW()) AND YEAR(Date) = YEAR(NOW()) AND UserID = %d');

define('SQL_GET_OPEN_ISSUES',
       'SELECT TaskID, TaskName, ProjectID, ProjectName 
        FROM vwTaskComments 
        WHERE UserID = %d AND Issue = 1 
        ORDER BY Date DESC LIMIT 5');

define('SQL_GET_RECENT_COMMENTARY',
       'SELECT TaskID, TaskName, ProjectID, ProjectName 
        FROM vwTaskComments 
        WHERE UserID = %d 
        ORDER BY Date DESC LIMIT 5');

define('SQL_GET_PAGES_VISITED',
       'SELECT Timestamp, Context, ContextID, Detail, Comment 
        FROM tblActivityLog
        WHERE UserID = %d
        ORDER BY Timestamp DESC LIMIT 5');

define('SQL_GET_CHARGE_RATE','
        SELECT ChargeRate FROM tblUsers WHERE ID = %s');

define('SQL_GET_COST_RATE','
        SELECT CostRate FROM tblUsers WHERE ID = %s');

define('SQL_GET_TASK_PERCENTAGE','SELECT PercentComplete FROM tblTasks WHERE ID = %s');

define('SQL_CONTACTS_LIST',
       'SELECT c.ID, CONCAT(c.FirstName,\' \',c.LastName) AS ContactName, c.EmailAddress1, c.Phone1, c.KeyContact
        FROM tblContacts AS c
        INNER JOIN tblClients AS cl ON cl.ID = c.ClientID
        INNER JOIN tblProjects AS p ON p.ClientID = cl.ID
        WHERE p.ID = \'%3$s\'
        ORDER BY c.KeyContact DESC, %1$s %2$s');

define('SQL_DEPENDENT_TASKS','SELECT Value FROM sysAdminSettings WHERE Setting = \'ShowDependentTasks\'');

define('SQL_INSERT_COMMENT',
       'INSERT INTO tblTasks_Comments (UserID, TaskID, Subject, Body, Date, HoursWorked, Issue, Contact, OutOfScope, CostRate, ChargeRate)
        VALUES (\'%1$s\', \'%2$s\', \'%3$s\', \'%4$s\', \'%9$s\', \'%5$s\', \'%6$s\', \'%7$s\', \'%8$s\', \'%10$s\', \'%11$s\')');

define('SQL_GET_USER_LIST',
       'SELECT ID, Username, FirstName, LastName, EmailAddress, Active
        FROM tblUsers
        WHERE Active = 1
        ORDER BY FirstName, LastName');

define('SQL_GET_TASKS_WITH_DEPENDENCIES','
        SELECT td.TaskID FROM tblTasks_Dependencies td
        LEFT JOIN tblTasks t ON t.ID = td.TaskDependencyID
        WHERE t.PercentComplete < 100');

define('SQL_GET_TASK_OWNER_ID','
        SELECT Owner FROM tblTasks WHERE ID = %s');

define('SQL_GET_PROJECTID','
    SELECT ProjectID FROM tblTasks WHERE ID = %s');

define('SQL_TASKS_EMAIL',
       'SELECT DISTINCT u.ID, u.FirstName, u.LastName, u.EmailAddress
        FROM tblUsers u
        LEFT JOIN tblResource ON u.ID = tblResource.UserID
        LEFT JOIN tblTaskResource ON tblResource.ID = tblTaskResource.ResourceID
        WHERE tblTaskResource.TaskID = \'%1$s\' AND u.EmailNotify = 1 GROUP BY u.ID');


//change_log 1.
define('SQL_GET_TASK_OWNER',
        'SELECT DISTINCT u.EmailAddress, CONCAT(u.FirstName, \' \', u.LastName) AS FullName, u.FirstName
        FROM tblUsers u
        LEFT JOIN tblTasks t ON t.Owner = u.ID
        WHERE t.ID = \'%1$s\' AND u.EmailNotify = 1');

define('SQL_GET_USERNAME','
        SELECT CONCAT(FirstName, \' \', LastName)
        FROM tblUsers
        WHERE ID = %s');

define('SQL_SELECT_PROJECT_NAME',
        'SELECT Name
        FROM tblProjects
        WHERE ID = \'%s\'');

define('SQL_GET_EMAIL_SUBJECT_DETAILS',
       'SELECT c.Name AS ClientName, p.Name AS ProjectName, t.Name AS TaskName
        FROM tblTasks t
        LEFT JOIN tblProjects p ON p.ID = t.ProjectID
        LEFT JOIN tblClients c ON c.ID = p.ClientID
        WHERE t.ID = \'%s\'');

define('SQL_SELECT_RECENT_ISSUES','
        SELECT tc. ID, t.ProjectID, p.Name as ProjectName, c.Name AS ClientName, tc.TaskID, tc.Body, tc.Date, t.Name, t.ProjectID, CONCAT(u.FirstName, \' \', u.LastName) AS FlaggedBy
        FROM tblTasks_Comments tc
        LEFT JOIN tblTasks t ON t.ID = tc.TaskID
        LEFT JOIN tblProjects p ON t.ProjectID = p.ID
        LEFT JOIN tblClients c ON p.ClientID = c.ID
        LEFT JOIN tblUsers u ON u.ID = tc.UserID
        WHERE tc.TaskID IN (%s) AND tc.Issue = 1
        ORDER BY Date DESC
        LIMIT 5');

//change_log 1.
define('SQL_GET_LAST_COMMENT','
        SELECT tc.Body, tc.Date, CONCAT(u.FirstName, \' \', u.LastName) AS FullName
        FROM tblTasks_Comments tc
        LEFT JOIN tblUsers u ON u.ID = tc.UserID
        WHERE tc.TaskID = %s');

define('SQL_SELECT_RECENT_UPDATES','
        SELECT tc. ID, t.ProjectID, p.Name as ProjectName, c.Name AS ClientName, tc.TaskID, tc.Body, tc.Date, t.Name, t.ProjectID
        FROM tblTasks_Comments tc
        LEFT JOIN tblTasks t ON t.ID = tc.TaskID
    LEFT JOIN tblProjects p ON t.ProjectID = p.ID
    LEFT JOIN tblClients c ON p.ClientID = c.ID
        LEFT JOIN tblUsers u ON u.ID = tc.UserID
        WHERE UserID = %s AND tc.Issue = 0
        ORDER BY Date DESC
        LIMIT 5');

define('SQL_TASKS_LIST',
       'SELECT DISTINCT t.ID, t.Name, t.Priority,
        p.ID as ProjectID, p.Name AS ProjectName, p.Colour ,
        c.ID AS ClientID, c.Name AS ClientName,
        t.Duration, t.EndDate, t.StartDate, t.LatestActivity, t.PercentComplete
        FROM tblTasks t
        LEFT JOIN tblTaskResource ON tblTaskResource.TaskID = t.ID
        LEFT JOIN tblResource ON tblTaskResource.ResourceID = tblResource.ID
        INNER JOIN tblProjects p ON p.ID = t.ProjectID
        INNER JOIN tblClients c ON c.ID = p.ClientID
        WHERE t.Status = \'%2$s\' AND tblResource.UserID = \'%1$s\' AND p.Active = \'1\' %3$s
        ORDER BY %4$s %5$s');

define('SQL_TASKS_OWED',
       'SELECT t.ID, t.Name, t.Priority,
        p.ID as ProjectID, p.Name AS ProjectName, p.Colour ,
        c.ID AS ClientID, c.Name AS ClientName,
        t.Duration, t.EndDate, t.StartDate, t.LatestActivity, t.PercentComplete
        FROM tblTasks t
        INNER JOIN tblProjects p ON p.ID = t.ProjectID
        INNER JOIN tblClients c ON c.ID = p.ClientID
        WHERE t.Status = \'%2$s\' AND t.Owner = \'%1$s\' AND p.Active = \'1\' %3$s
        ORDER BY %4$s %5$s');

define('SQL_TASKS_ALL',
        'SELECT DISTINCT t.ID, t.Name, t.Priority,
        p.ID as ProjectID, p.Name AS ProjectName, p.Colour ,
        c.ID AS ClientID, c.Name AS ClientName,
        t.Duration, t.EndDate, t.StartDate, t.LatestActivity, t.PercentComplete
        FROM tblTasks t
        LEFT JOIN tblTaskResource ON tblTaskResource.TaskID = t.ID
        LEFT JOIN tblResource ON tblTaskResource.ResourceID = tblResource.ID
        INNER JOIN tblProjects p ON p.ID = t.ProjectID
        INNER JOIN tblClients c ON c.ID = p.ClientID
        WHERE t.Status = \'%2$s\' AND (tblResource.UserID = \'%1$s\' OR t.Owner = \'%1$s\') AND p.Active = \'1\' %3$s
        ORDER BY %4$s %5$s');

define('SQL_TASKS_LIST_ALLUSERS',
       'SELECT DISTINCT t.ID, t.Name, t.Priority,
        p.ID as ProjectID, p.Name AS ProjectName, p.Colour ,
        c.ID AS ClientID, c.Name AS ClientName,
        t.Duration, t.EndDate, t.StartDate, t.LatestActivity, t.PercentComplete
        FROM tblTasks t
        LEFT JOIN tblTaskResource ON tblTaskResource.TaskID = t.ID
        LEFT JOIN tblResource ON tblTaskResource.ResourceID = tblResource.ID
        INNER JOIN tblProjects p ON p.ID = t.ProjectID
        INNER JOIN tblClients c ON c.ID = p.ClientID
        WHERE t.Status = \'%2$s\' AND p.Active = \'1\' %3$s
        ORDER BY %4$s %5$s');

define('SQL_TASKS_OWED_ALLUSERS',
       'SELECT t.ID, t.Name, t.Priority,
        p.ID as ProjectID, p.Name AS ProjectName, p.Colour ,
        c.ID AS ClientID, c.Name AS ClientName,
        t.Duration, t.EndDate, t.StartDate, t.LatestActivity, t.PercentComplete
        FROM tblTasks t
        INNER JOIN tblProjects p ON p.ID = t.ProjectID
        INNER JOIN tblClients c ON c.ID = p.ClientID
        WHERE t.Status = \'%2$s\' AND p.Active = \'1\' %3$s
        ORDER BY %4$s %5$s');

define('SQL_TASKS_ALL_ALLUSERS',
        'SELECT DISTINCT t.ID, t.Name, t.Priority,
        p.ID as ProjectID, p.Name AS ProjectName, p.Colour ,
        c.ID AS ClientID, c.Name AS ClientName,
        t.Duration, t.EndDate, t.StartDate, t.LatestActivity, t.PercentComplete
        FROM tblTasks t
        LEFT JOIN tblTaskResource ON tblTaskResource.TaskID = t.ID
        LEFT JOIN tblResource ON tblTaskResource.ResourceID = tblResource.ID
        INNER JOIN tblProjects p ON p.ID = t.ProjectID
        INNER JOIN tblClients c ON c.ID = p.ClientID
        WHERE t.Status = \'%2$s\' AND p.Active = \'1\' %3$s
        ORDER BY %4$s %5$s');

define('SQL_ASSIGNED_COUNT',
            'SELECT COUNT(*)
            FROM tblTaskResource
            LEFT JOIN tblResource ON tblResource.ID = tblTaskResource.ResourceID
            WHERE tblTaskResource.TaskID = %s
            AND tblResource.UserID = %s');

define('SQL_UPDATE_TASK',
       'UPDATE tblTasks SET
       HoursWorked = HoursWorked + \'%2$s\',
       PercentComplete = \'%3$s\',
       Status = \'%4$s\',
       ActualBudget = ActualBudget + \'%5$s\'
       WHERE ID = \'%1$s\'');

// update resources completed time
define('SQL_GET_DAY_ID','SELECT ID FROM tblDay WHERE Epoch = %s');
define('SQL_GET_RESOURCE_ID','SELECT ID FROM tblResource WHERE UserID = %s');

define('SQL_UPDATE_TASK_RESOURCE_DAY_COMMITMENT',
            'UPDATE tblTaskResourceDay SET HoursCommitted = \'%4$s\'
             WHERE TaskID = \'%1$s\'
             AND ResourceID = \'%2$s\'
             AND DayID = \'%3$s\'');


define('SQL_CHECK_IF_ASSIGNED_TO_TASK_ON_DAY',
            'SELECT TaskID
            FROM tblTaskResourceDay
            WHERE TaskID = \'%1$s\'
            AND ResourceID = \'%2$s\'
            AND DayID = \'%3$s\'');

define('SQL_INSERT_TASK_RESOURCE_DAY_COMMITMENT',
            'INSERT INTO tblTaskResourceDay (TaskID,ResourceID,DayID,HoursCommitted,HoursCompleted)
             VALUES(\'%1$s\',\'%2$s\',\'%3$s\',\'%4$s\',\'%5$s\')');

define('SQL_UPDATE_TASK_RESOURCE_DAY',
            'UPDATE tblTaskResourceDay
            SET HoursCompleted = HoursCompleted + %s
            WHERE TaskID = %s
            AND ResourceID = %s
            AND DayID = %s');

define('SQL_TASKS_GET_COMMENTS',
       'SELECT t.ID, t.UserID, t.Subject, t.Body, t.Date,IFNULL(t.HoursWorked, 0) AS HoursWorked, t.Issue, t.OutOfScope, u.FirstName, u.LastName
       FROM tblTasks_Comments t
       INNER JOIN tblUsers u ON u.ID = t.UserID
       WHERE t.TaskID = \'%s\'
       ORDER BY Date DESC');

define('SQL_TASKS_GET_USERS_COMMENTS',
       'SELECT t.ID, t.UserID, t.Subject, t.Body, t.Date,IFNULL(t.HoursWorked, 0) AS HoursWorked, t.Issue, t.OutOfScope, u.FirstName, u.LastName
       FROM tblTasks_Comments t
       INNER JOIN tblUsers u ON u.ID = t.UserID
       WHERE t.TaskID = \'%s\'
        AND u.ID = \'%s\'
       ORDER BY Date DESC');

define('SQL_GET_PROJECT_COLOUR_FOR_TASK',
       'SELECT tblProjects.Colour
       FROM tblTasks
       LEFT JOIN tblProjects ON tblTasks.ProjectID = tblProjects.ID
       WHERE tblTasks.ID = \'%s\'');

define('SQL_GET_COMMENT_ID',
        'SELECT t.ID
        FROM tblTasks_Comments t
        WHERE t.UserID = %s
        AND t.Date = \'%s\'');

define('SQL_COUNT_TASK_ISSUES',
      'SELECT COUNT(*) AS count FROM tblTasks_Comments WHERE Issue = 1 AND TaskID = \'%d\'');


