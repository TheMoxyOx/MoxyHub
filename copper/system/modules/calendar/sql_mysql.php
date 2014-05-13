<?php
// $Id$
define('SQL_UPDATE_AVAILABILITY',
       'UPDATE tblResourceDay SET HoursAvailable = \'%3$s\' 
        WHERE ResourceID = %1$d AND DayID = %2$d');

define('SQL_INSERT_AVAILABILITY',
       'INSERT INTO tblResourceDay (ResourceID, DayID, HoursAvailable, HoursCommittedCache) VALUES (%d, %d, \'%s\', 0)');

define('SQL_GET_PROJECTS_STARTING_MONTH',
       'SELECT p.ID AS ProjectID, p.Name, p.StartDate, p.EndDate, p.Description, p.Colour, tblDay.ID AS DayID
        FROM tblProjects p
        LEFT JOIN tblDay ON CONCAT( tblDay.Year,  \'-\', tblDay.Month,  \'-\', tblDay.Day ) = DATE_FORMAT(p.StartDate, \'%%Y-%%c-%%e\')
        WHERE YEAR(p.StartDate) = \'%1$s\' AND MONTH(p.StartDate) = \'%2$s\' AND p.ID IN (%3$s)');

define('SQL_GET_PROJECTS_ENDING_MONTH',
       'SELECT p.ID AS ProjectID, p.Name, p.StartDate, p.EndDate, p.Description, p.Colour, tblDay.ID AS DayID
        FROM tblProjects p
        LEFT JOIN tblDay ON CONCAT( tblDay.Year,  \'-\', tblDay.Month,  \'-\', tblDay.Day ) = DATE_FORMAT(p.EndDate, \'%%Y-%%c-%%e\')
        WHERE YEAR(p.EndDate) = \'%1$s\' AND MONTH(p.EndDate) = \'%2$s\' AND p.ID IN (%3$s)');

define('SQL_GET_TASKS_STARTING_MONTH',
       'SELECT t.ID AS TaskID, t.Name AS TaskName, t.StartDate, t.EndDate, t.Description, t.ProjectID, p.Name AS ProjectName, p.Colour,
        t.StartDate, tblDay.ID AS DayID
        FROM tblTasks t
        LEFT JOIN tblProjects p ON t.ProjectID = p.ID
        LEFT JOIN tblDay ON CONCAT( tblDay.Year,  \'-\', tblDay.Month,  \'-\', tblDay.Day ) = DATE_FORMAT(t.StartDate, \'%%Y-%%c-%%e\')
        WHERE YEAR(t.StartDate) = \'%1$s\' AND MONTH(t.StartDate) = \'%2$s\' AND t.ProjectID IN (%3$s)');

define('SQL_GET_TASKS_ENDING_MONTH',
       'SELECT t.ID AS TaskID, t.Name AS TaskName, t.StartDate, t.EndDate, t.Description, t.ProjectID, p.Name AS ProjectName, p.Colour,
        t.StartDate, tblDay.ID AS DayID
        FROM tblTasks t
        LEFT JOIN tblProjects p ON t.ProjectID = p.ID
        LEFT JOIN tblDay ON CONCAT( tblDay.Year,  \'-\', tblDay.Month,  \'-\', tblDay.Day ) = DATE_FORMAT(t.EndDate, \'%%Y-%%c-%%e\')
        WHERE YEAR(t.EndDate) = \'%1$s\' AND MONTH(t.EndDate) = \'%2$s\' AND t.ProjectID IN (%3$s)');

define('SQL_GET_CALENDAR_NOTES_MONTH',
       'SELECT tblCalendar.*, tblDay.ID AS DayID
        FROM tblCalendar 
        LEFT JOIN tblDay ON CONCAT( tblDay.Year,  \'-\', tblDay.Month,  \'-\', tblDay.Day ) = DATE_FORMAT(tblCalendar.Date, \'%%Y-%%c-%%e\')
        WHERE YEAR(tblCalendar.Date) = \'%1$s\' AND MONTH(tblCalendar.Date) = \'%2$s\'');

define('SQL_PROJECT_GET_DATE_DIFF',
       'SELECT DATEDIFF(\'%s\', %s) FROM tblProjects WHERE ID = %d');

define('SQL_PROJECT_MOVE_DATES',
       'UPDATE tblProjects SET StartDate = StartDate + INTERVAL %1$s DAY, EndDate = EndDate + INTERVAL %1$s DAY WHERE ID = %2$d');

define('SQL_PROJECT_TASKS_MOVE_START_DATE',
       'UPDATE tblTasks SET StartDate = StartDate + INTERVAL %1$s DAY WHERE ProjectID = %d');

define('SQL_PROJECT_TASKS_MOVE_END_DATE',
       'UPDATE tblTasks SET EndDate = EndDate + INTERVAL %1$s DAY WHERE ProjectID = %d AND EndDate <> \'0000-00-00\'');

define('SQL_PROJECT_MOVE_DATE',
       'UPDATE tblProjects SET %s = \'%s\' WHERE ID = %d');

define('SQL_PROJECT_GET_TASK_DATES',
       'SELECT ID, StartDate, EndDate FROM tblTasks WHERE ProjectID = %d');

define('SQL_GET_DAY_ID',
       'SELECT ID FROM tblDay WHERE Year = %d AND Month = %d AND Day = %d');

define('SQL_DELETE_TASK_RESOURCE_DAY',
       'DELETE FROM tblTaskResourceDay
        WHERE TaskID = %1$s
        AND (DayID < %2$s OR DayID > %3$s)
        AND HoursCompleted = 0');

define('SQL_UPDATE_TASK_RESOURCE_DAY_COMMITTED',
       'UPDATE tblTaskResourceDay SET HoursCommitted = 0
        WHERE TaskID = %1$s
        AND (DayID < %2$s OR DayID > %3$s)');

define('SQL_TASK_MOVE_DATE',
       'UPDATE tblTasks SET %s = \'%s\' WHERE ID = %d');

define('SQL_TASK_GET_DATES',
       'SELECT ID, StartDate, EndDate FROM tblTasks WHERE ID = %d');

define('SQL_TASK_GET_PROJECT_ID',
       'SELECT ProjectID FROM tblTasks WHERE ID = %d');

define('SQL_CALENDAR_MOVE_DATE',
       'UPDATE tblCalendar SET Date = \'%s\' WHERE ID = %d');

define('SQL_LAST_INSERT', 'SELECT LAST_INSERT_ID()');

define('SQL_EVENT_CLEAR_ASSIGNED', 'DELETE FROM tblEvents_Users WHERE EventID = \'%s\'');
define('SQL_EVENT_CLEAR_REMOVED', 'DELETE FROM tblEvents_Users WHERE EventID = \'%s\' AND UserID NOT IN (%s)');
define('SQL_EVENT_CHECKASSIGNMENT', 'SELECT UserID FROM tblEvents_Users WHERE UserID = \'%s\' AND EventID = \'%s\'');
define('SQL_EVENT_ASSIGN', 'INSERT INTO tblEvents_Users (EventID,UserID) VALUES (\'%s\', \'%s\')');

define('SQL_EVENT_GET_USERS',
       'SELECT u.ID, u.FirstName, u.LastName
       FROM tblUsers u
       INNER JOIN tblEvents_Users e ON e.UserID = u.ID AND e.EventID = \'%s\'');

define('SQL_GET_USERS',
       'SELECT ID, FirstName, LastName FROM tblUsers WHERE Active = \'1\' ORDER BY LastName, FirstName');

define('SQL_GET_USERS_MINUS',
       'SELECT ID, FirstName, LastName
        FROM tblUsers
        WHERE Active = \'1\' AND ID NOT IN (%s)
        ORDER BY LastName, FirstName');

define('SQL_GET_CALENDAR_NOTE',
       'SELECT *
        FROM tblCalendar
        WHERE ID = %s');

define('SQL_DELETE_CALENDAR_NOTE',
       'DELETE FROM tblCalendar
        WHERE ID = %s');

define('SQL_GET_PROJECT_IDS',
        'SELECT ID FROM tblProjects');

define('SQL_GET_PROJECT_IDS_FOR_CLIENT',
        'SELECT ID as ProjectID, Name as ProjectName FROM tblProjects
         WHERE ClientID = \'%1$s\' AND ID IN (%2$s)
         ORDER BY Name');

define('SQL_GET_CLIENT_IDS',
        'SELECT ID FROM tblClients');

define('SQL_GET_CLIENTS_IN',
        'SELECT c.ID as ClientID, c.Name as ClientName
        FROM tblClients AS c
        WHERE c.ID IN (%1$s)
        ORDER BY Name');

define('SQL_GET_PROJECTS',
        'SELECT p.ID as ProjectID, p.Name as ProjectName
        FROM tblProjects AS p
        ORDER BY Name');

define('SQL_GET_PROJECTS_IN',
        'SELECT p.ID as ProjectID, p.Name as ProjectName
        FROM tblProjects AS p
        WHERE p.ID IN (%1$s)
        ORDER BY Name');

define('SQL_GET_PROJECTS_STARTING',
        'SELECT p.ID as ProjectID, p.Name as ProjectName, p.StartDate, p.EndDate, p.Colour,
        CONCAT(uP.FirstName, \' \', uP.LastName) AS ProjectOwner, c.Name AS ClientName
        FROM tblProjects AS p
        LEFT JOIN tblUsers AS uP ON uP.ID = p.Owner
        LEFT JOIN tblClients c ON c.ID = p.ClientID
        WHERE p.StartDate = \'%1$s\' AND p.ID IN (%2$s)');

define('SQL_GET_PROJECTS_FINISHING',
        'SELECT p.ID as ProjectID, p.Name as ProjectName, p.StartDate, p.EndDate, p.Colour,
        CONCAT(uP.FirstName, \' \', uP.LastName) AS ProjectOwner, c.Name AS ClientName
        FROM tblProjects AS p
        LEFT JOIN tblUsers AS uP ON uP.ID = p.Owner
        LEFT JOIN tblClients c ON c.ID = p.ClientID
        WHERE p.EndDate = \'%1$s\' AND p.ID IN (%2$s)');

define( 'SQL_GET_PROJECTS_STARTING_AND_FINISHING',
        sprintf( "%s UNION DISTINCT %s", SQL_GET_PROJECTS_STARTING, SQL_GET_PROJECTS_FINISHING ) ); 

define('SQL_GET_TASKS_STARTING',
            'SELECT DISTINCT t.ID as TaskID, p.ID as ProjectID, p.Name as ProjectName, p.Colour,
            t.Name AS TaskName, t.StartDate AS TaskStartDate, t.EndDate AS TaskEndDate,
            CONCAT(u.FirstName, \' \', u.LastName) AS TaskOwner, c.Name AS ClientName
            FROM tblTasks AS t
            INNER JOIN tblProjects p ON p.ID = t.ProjectID
            LEFT JOIN tblClients c ON c.ID = p.ClientID
            LEFT JOIN tblUsers AS u ON u.ID = t.Owner
            WHERE t.StartDate = \'%1$s\' AND p.ID IN (%3$s)');

define('SQL_GET_TASKS_DUE',
            'SELECT DISTINCT t.ID AS TaskID, p.ID as ProjectID, p.Name as ProjectName, p.Colour,
            t.Name AS TaskName, t.StartDate AS TaskStartDate, t.EndDate AS TaskEndDate,
            CONCAT(u.FirstName, \' \', u.LastName) AS TaskOwner, c.Name AS ClientName
            FROM tblTasks AS t
            INNER JOIN tblProjects p ON p.ID = t.ProjectID
            LEFT JOIN tblUsers AS u ON u.ID = t.Owner
            LEFT JOIN tblClients c ON c.ID = p.ClientID
            WHERE t.EndDate = \'%1$s\' AND p.ID IN (%3$s)');

define( 'SQL_GET_TASKS_STARTING_AND_DUE',
        sprintf( "%s UNION DISTINCT %s", SQL_GET_TASKS_STARTING, SQL_GET_TASKS_DUE ) ); 

define('SQL_GET_CALENDAR_NOTES',
       'SELECT * FROM tblCalendar WHERE DATE_FORMAT( Date, "%%Y-%%m-%%d" ) = \'%1$s\'');

define('SQL_CALENDAR_NOTE_CREATE',
       'INSERT INTO tblCalendar (Name, Date, StartTime, EndTime, Colour, Description, Holiday)
       VALUES (\'%1$s\', \'%2$s\', \'%3$s\', \'%4$s\', \'%5$s\', \'%6$s\', \'%7$s\')');

define('SQL_CALENDAR_NOTE_UPDATE',
       'UPDATE tblCalendar SET Name = \'%1$s\', Date = \'%2$s\', StartTime = \'%3$s\', EndTime = \'%4$s\', Colour = \'%5$s\', Description = \'%6$s\', Holiday = \'%7$s\' WHERE ID = \'%8$s\'');

define('SQL_GET_USER_LIST',
       'SELECT u.ID, u.Username, u.FirstName, u.LastName, u.EmailAddress, u.Active
        FROM tblUsers AS u
        LEFT JOIN tblResource AS r ON u.ID = r.UserID
        WHERE u.Active = 1 AND u.ID = r.UserID
        ORDER BY FirstName, LastName');

define('SQL_GET_TASKID',
       'SELECT TaskID
        FROM tblTasks_Delegation
        WHERE UserID = %s
        AND TaskID = %s');


// calandar
//define('GET_HOURS_DAY_FOR_RESOURCE',
//            'SELECT tblDay.ID, tblResourceDay.HoursAvailable, tblDay.Epoch,
//             tblDay.Day, tblDay.Month, tblDay.Year, tblDay.Weekday
//             FROM tblDay
//             LEFT JOIN tblResourceDay ON tblDay.ID = tblResourceDay.DayID
//             WHERE tblResourceDay.ResourceID = \'%1$s\' AND %2$s
//             ORDER BY tblDay.ID');


define('GET_HOURS_DAY_FOR_RESOURCE',
    'SELECT td.ID, trd.HoursAvailable, td.Epoch, td.Day, td.Month, td.Year, td.Weekday
    FROM tblDay td
    LEFT JOIN tblResourceDay trd ON td.ID = trd.DayID
    WHERE trd.ResourceID=%1$s AND Month= %2$s AND td.Year=%3$s'
);

define('SQL_USER_RESOURCE',
            'SELECT tblResource.ID, tblUsers.FirstName, tblUsers.LastName
             FROM tblUsers
             LEFT JOIN tblResource ON tblUsers.ID = tblResource.UserID
             WHERE tblUsers.ID = \'%1$s\'');

define('SQL_HOURS_COMMITTED_TASK_DAYS_MONTH_PROJECT',
            'SELECT tblTaskResourceDay.TaskID, tblTaskResourceDay.DayID, tblTaskResourceDay.HoursCommitted,
             tblTasks.Name, tblTasks.ProjectID,
             (SELECT tblTaskResource.ResourceID FROM tblTaskResource WHERE tblTaskResource.TaskID = tblTaskResourceDay.TaskID AND tblTaskResource.ResourceID = tblTaskResourceDay.ResourceID) AS Assigned
             FROM tblTaskResourceDay
             LEFT JOIN tblTasks ON tblTaskResourceDay.TaskID = tblTasks.ID
             WHERE tblTaskResourceDay.ResourceID = %1$s
             AND %2$s
             AND tblTasks.ProjectID %3$s
             HAVING Assigned > 0
             ORDER BY tblTaskResourceDay.TaskID');

define('SQL_HOURS_COMMITTED_TASK_DAYS_MONTH_CLIENT',
            'SELECT tblTaskResourceDay.TaskID, tblTaskResourceDay.DayID, tblTaskResourceDay.HoursCommitted,
             tblTasks.Name, tblTasks.ProjectID,
             (SELECT tblTaskResource.ResourceID FROM tblTaskResource WHERE tblTaskResource.TaskID = tblTaskResourceDay.TaskID AND tblTaskResource.ResourceID = tblTaskResourceDay.ResourceID) AS Assigned
             FROM tblTaskResourceDay
             LEFT JOIN tblTasks ON tblTaskResourceDay.TaskID = tblTasks.ID
             LEFT JOIN tblProjects ON tblProjects.ID = tblTasks.ProjectID
             WHERE tblTaskResourceDay.ResourceID = %1$s
             AND %2$s
             AND tblProjects.ClientID = %3$s
             HAVING Assigned > 0
             ORDER BY tblTaskResourceDay.TaskID');

define('SQL_HOURS_COMMITTED_TASK_DAYS_MONTH',
            'SELECT tblTaskResourceDay.TaskID, tblTaskResourceDay.DayID, tblTaskResourceDay.HoursCommitted,
             tblTasks.Name, tblTasks.ProjectID, 
             (SELECT tblTaskResource.ResourceID FROM tblTaskResource WHERE tblTaskResource.TaskID = tblTaskResourceDay.TaskID AND tblTaskResource.ResourceID = tblTaskResourceDay.ResourceID) AS Assigned
             FROM tblTaskResourceDay
              LEFT JOIN tblTasks ON tblTaskResourceDay.TaskID = tblTasks.ID
             WHERE tblTaskResourceDay.ResourceID = %1$s
             AND %2$s
             HAVING Assigned > 0
             ORDER BY tblTaskResourceDay.TaskID');

// calandar form submit
define('SQL_GET_IDS_FOR_MONTH_AND_HOURS_COMMITTED_OF_TASKS',
            'SELECT tblDay.ID, tblDay.Day, COALESCE(tblResourceDay.HoursCommittedCache, 0.00) AS HoursCommitted, tblResourceDay.HoursCommittedCache AS NullCheck
             FROM tblDay
             LEFT JOIN tblResourceDay ON tblDay.ID = tblResourceDay.DayID
             AND tblResourceDay.ResourceID = \'%1$s\'
             WHERE Month = \'%2$s\' AND Year = \'%3$s\'
             ORDER BY tblDay.ID');

define('SQL_GET_IDS_FOR_MONTH',
            'SELECT ID
             FROM tblDay
             WHERE Month = \'%1$s\' AND Year = \'%2$s\'
             ORDER BY ID ASC');

define('SQL_GET_OVER_COMMITTED',
            'SELECT tblTaskResourceDay.TaskID, tblTasks.ProjectID, tblTaskResourceDay.DayID, tblDay.Epoch, tblTasks.Name
                FROM tblTaskResourceDay
                LEFT JOIN tblTasks ON tblTaskResourceDay.TaskID = tblTasks.ID
                LEFT JOIN tblDay ON tblTaskResourceDay.DayID = tblDay.ID
                WHERE tblTaskResourceDay.ResourceID = \'%1$s\'
                AND tblTaskResourceDay.DayID IN (%2$s)
                ORDER BY tblTaskResourceDay.TaskID');

// resourcing data collector
// must be in the same order as GET_HOURS_AVAILABLE_ALL_RESOURCES and GET_HOURS_COMMITTED_ALL_RESOURCES
define('SQL_RESOURCE_USERS',
       'SELECT tblResource.ID, tblResource.UserID, tblUsers.FirstName, tblUsers.LastName
       FROM tblResource
       LEFT JOIN tblUsers ON tblResource.UserID = tblUsers.ID
       WHERE Active = \'1\'
       ORDER BY tblResource.ID');

define('SQL_RESOURCE_USERS_RESTRICT',
       'SELECT tblResource.ID, tblResource.UserID, tblUsers.FirstName, tblUsers.LastName
       FROM tblResource
       LEFT JOIN tblUsers ON tblResource.UserID = tblUsers.ID
       WHERE tblUsers.ID = %s
       ORDER BY tblResource.ID');

define('SQL_GET_DAYID_EPOCH',
       'SELECT ID AS DayID, Epoch, Day, Month
        FROM tblDay
        WHERE Epoch >= \'%1$s\' AND Epoch <= \'%2$s\'
        ORDER BY ID');

define('GET_HOURS_AVAILABLE_ALL_RESOURCES',
       'SELECT tblResourceDay.DayID, tblResourceDay.ResourceID,
        tblResourceDay.HoursAvailable
        FROM tblResourceDay
        WHERE %1$s
        ORDER BY ResourceID, DayID');

define('GET_HOURS_COMMITTED_ALL_RESOURCES',
       'SELECT tblTaskResourceDay.TaskID, tblTaskResourceDay.ResourceID, tblTaskResourceDay.DayID, tblTaskResourceDay.HoursCommitted
       FROM tblTaskResourceDay
       LEFT JOIN tblTasks ON tblTaskResourceDay.TaskID = tblTasks.ID
       WHERE %1$s AND tblTaskResourceDay.ResourceID IN (SELECT ID FROM tblResource)
       ORDER BY ResourceID, DayID');

define('GET_HOURS_AVAILABLE_SINGLE_RESOURCE',
       'SELECT tblResourceDay.DayID, tblResourceDay.ResourceID,
        tblResourceDay.HoursAvailable
        FROM tblResourceDay
        WHERE ResourceID = %2$s AND %1$s
        ORDER BY ResourceID, DayID');

define('GET_HOURS_COMMITTED_SINGLE_RESOURCE',
       'SELECT tblTaskResourceDay.TaskID, tblTaskResourceDay.ResourceID, tblTaskResourceDay.DayID, tblTaskResourceDay.HoursCommitted
       FROM tblTaskResourceDay
       LEFT JOIN tblTasks ON tblTaskResourceDay.TaskID = tblTasks.ID
       WHERE ResourceID = %2$s AND %1$s AND tblTaskResourceDay.ResourceID IN (SELECT ID FROM tblResource)
       ORDER BY DayID');

define('SQL_GET_TASKNAMES',
       'SELECT tblTasks.ID AS TaskID, tblTasks.ProjectID, tblTasks.Name AS TaskName,
       tblTasks.StartDate, tblTasks.EndDate,
       tblProjects.Name AS ProjectName, tblProjects.Colour
       FROM tblTasks
       LEFT JOIN tblProjects ON tblTasks.ProjectID = tblProjects.ID
       WHERE tblTasks.ID %1$s
       ORDER BY tblTasks.ProjectID');

define('SQL_USER_RESOURCE_FROM_RESOURCEID',
        'SELECT tblResource.ID, tblUsers.FirstName, tblUsers.LastName
         FROM tblResource
         LEFT JOIN tblUsers ON tblResource.UserID = tblUsers.ID
         WHERE tblResource.ID = \'%1$s\'');

define('SQL_RESOURCE_FROM_USER_ID',
       'SELECT r.ID
        FROM tblResource r
        WHERE r.UserID = %d');

define('SQL_GET_ID_EPOCH_WEEKDAY_FROM_DAY',
            'SELECT ID, Epoch, Weekday
             FROM tblDay
             WHERE Epoch BETWEEN \'%1$s\' AND \'%2$s\'');

define('SQL_GET_ID_EPOCH_WEEKDAY_FROM_DAY_USERSAVE',
            'SELECT Epoch
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

 
