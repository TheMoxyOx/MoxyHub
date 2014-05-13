<?php
// $Id$
define('SQL_SELECT_RECENT_ISSUES','
        SELECT tc. ID, t.ProjectID, p.Name as ProjectName, c.Name AS ClientName, tc.TaskID, tc.Body, tc.Date, t.Name, t.ProjectID, CONCAT(u.FirstName, \' \', u.LastName) AS FlaggedBy
        FROM tblTasks_Comments tc
        LEFT JOIN tblTasks t ON t.ID = tc.TaskID
    LEFT JOIN tblProjects p ON t.ProjectID = p.ID
        LEFT JOIN tblClients c ON p.ClientID = c.ID
        LEFT JOIN tblUsers u ON u.ID = tc.UserID
        WHERE tc.UserID = %s AND tc.Issue = 1
        ORDER BY Date DESC
        LIMIT 5');

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



define('SQL_PROJECTS_LIST',
    'SELECT p.ID, p.Name, c.Name AS ClientName
    FROM tblProjects AS p, tblClients c
    WHERE c.ID = p.ClientID
    ORDER BY ClientName, p.Name
    ');

define('SQL_UPDATES_LIST',
       'SELECT comment.ID, comment.Body as Comment, comment.Date, client.ID as ClientID, client.Name as ClientName, project.ID as ProjectID, project.Name as ProjectName, task.ID as TaskID, task.Name as TaskName, task.PercentComplete, user.FirstName, user.LastName
       FROM tblTasks_Comments comment
       INNER JOIN tblTasks task ON task.ID = comment.TaskID
       INNER JOIN tblProjects project ON project.ID = task.ProjectID
       INNER JOIN tblClients client ON client.ID = project.ClientID
       INNER JOIN tblUsers user ON user.ID = comment.UserID
       WHERE project.ID IN (%1$s) AND comment.Date > \'%2$s\'
       ORDER BY %3$s %4$s');

define('SQL_GET_USERS',
       'SELECT ID, FirstName, LastName
        FROM tblUsers
        WHERE Active = \'1\'
        ORDER BY LastName, FirstName
        ');

// orca 11/1/2007 reports
define('SQL_CLIENTS_LIST',
	'SELECT
	ID, Name
	FROM tblClients;');

define('SQL_GET_CHARGE_RATE','
        SELECT ChargeRate FROM tblUsers WHERE ID = %s');

define('SQL_TASKS_GET_COMMENTS',
       'SELECT t.UserID, IFNULL(t.HoursWorked, 0) AS HoursWorked
       FROM tblTasks_Comments t
       INNER JOIN tblUsers u ON u.ID = t.UserID
       WHERE t.TaskID = %s');

define( 'SQL_PROJECT_ANALYSIS',
    "SELECT p.ID, p.Name, p.ClientID, c.Name AS ClientName, p.ProjectID, p.Status "
    ."FROM tblProjects p "
    ."LEFT JOIN tblClients c ON p.ClientID = c.ID "
    ."WHERE "
    .'((StartDate BETWEEN \'%1$s\' AND \'%2$s\') OR (EndDate BETWEEN \'%1$s\' AND \'%2$s\')) '
    .'%3$s '
    ."ORDER BY c.Name ASC, p.Name ASC" );

define( 'SQL_SELECT_PROJECT_REPORT',
    "SELECT * FROM tblProjectReports WHERE ID = '%s'" );

define( 'SQL_SELECT_PROJECT_REPORTS',
    'SELECT * FROM tblProjectReports' );

define( 'SQL_INSERT_PROJECT_REPORT', 
    "INSERT INTO tblProjectReports ( UserID, Name, StartDate, EndDate, Clients, Projects, Budget, Details, Frequency, Created, Period ) "
    ."VALUES ( '%s', '%s', '%s', '%s', '%s', '%s', %s, %s, '%s', NOW(), '%s' )" );

define( 'SQL_UPDATE_PROJECT_REPORT',
    "UPDATE tblProjectReports SET UserID = '%s', Name = '%s', StartDate = '%s', EndDate = '%s', "
    ."Clients = '%s', Projects = '%s', Budget = '%s', Details = '%s', Frequency = '%s', Period = '%s' "
    ."WHERE ID = '%s' LIMIT 1" );

define( 'SQL_DELETE_PROJECT_REPORT',
    "DELETE FROM tblProjectReports WHERE ID = '%s' LIMIT 1" );

define( 'SQL_SELECT_PROJECT_IDS_FOR_CLIENTS',
    "SELECT ID FROM tblProjects WHERE ClientID IN (%s)" );

define( 'SQL_SELECT_PROJECT_DURATION',
    "SELECT SUM(Duration) AS Duration FROM tblTasks WHERE ProjectID = '%s'" );

define( 'SQL_SELECT_PROJECT_COMMITTED_COMPLETED',
    "SELECT SUM(HoursCommitted) AS TotalHoursCommitted, SUM(HoursCompleted) AS TotalHoursCompleted "
    ."FROM tblTasks "
    ."LEFT JOIN tblTaskResourceDay ON tblTasks.ID = tblTaskResourceDay.TaskID "
    ."LEFT JOIN tblDay ON tblDay.ID = tblTaskResourceDay.DayID "
    ."WHERE tblTasks.ProjectID = '%s' AND Epoch BETWEEN UNIX_TIMESTAMP('%s') AND UNIX_TIMESTAMP('%s')" );

define( 'SQL_SELECT_PROJECT_TARGET_BUDGET',
    "SELECT COALESCE(SUM(TargetBudget), 0) AS TargetBudget FROM tblTasks WHERE ProjectID = '%s'" );

define( 'SQL_SELECT_PROJECT_HOURS_WORKED',
    "SELECT c.UserID, "
    ."(SELECT u.ChargeRate FROM tblUsers u WHERE u.ID = c.UserID) AS Rate, "
    ."SUM(COALESCE(c.HoursWorked, 0)) AS HoursWorked "
    ."FROM tblTasks_Comments c "
    ."WHERE c.TaskID IN "
    ."(SELECT t.ID FROM tblTasks t WHERE t.ProjectID = '%s') "
    ."AND c.Date BETWEEN '%s' AND '%s' "
    ."GROUP BY c.UserID" );

define( 'SQL_SELECT_PROJECT_TASKS',
    "SELECT * FROM tblTasks WHERE ProjectID = '%s' ORDER BY Name ASC" );

define( 'SQL_SELECT_TASK_COMMITTED_COMPLETED',
    "SELECT SUM(HoursCommitted) AS TotalHoursCommitted, SUM(HoursCompleted) AS TotalHoursCompleted "
    ."FROM tblTaskResourceDay "
    ."LEFT JOIN tblDay ON tblDay.ID = tblTaskResourceDay.DayID "
    ."WHERE TaskID = '%s' AND Epoch BETWEEN UNIX_TIMESTAMP('%s') AND UNIX_TIMESTAMP('%s')" );

define( 'SQL_SELECT_TASK_HOURS_WORKED',
    "SELECT c.UserID, "
    ."(SELECT u.ChargeRate FROM tblUsers u WHERE u.ID = c.UserID) AS Rate, "
    ."SUM(COALESCE(c.HoursWorked, 0)) AS HoursWorked "
    ."FROM tblTasks_Comments c "
    ."WHERE c.TaskID = '%s' "
    ."AND c.Date BETWEEN '%s' AND '%s' "
    ."GROUP BY c.UserID" );

define( 'SQL_SELECT_USERS',
    "SELECT ID, FirstName, LastName, CONCAT(FirstName, ' ', LastName) AS FullName "
    ."FROM tblUsers "
    ."WHERE Active = 1 "
    ."ORDER BY FullName ASC" );

define( 'SQL_SELECT_CLIENTS',
    "SELECT ID, Name "
    ."FROM tblClients "
    ."WHERE Archived = 0 "
    ."ORDER BY Name ASC" );

define( 'SQL_SELECT_PROJECTS',
    "SELECT p.ID, p.Name, p.ClientID, c.Name AS ClientName "
    ."FROM tblProjects p "
    ."LEFT JOIN tblClients c ON c.ID = p.ClientID "
    ."WHERE p.Active = 1 AND c.Archived = 0 "
    ."ORDER BY c.Name ASC, p.Name ASC" );

define( 'SQL_SELECT_USERS_HOURS',
    "SELECT u.ID as ID, CONCAT(u.FirstName, ' ', u.LastName) AS FullName, "
    ."c.ID AS ClientID, c.Name AS ClientName, p.ID AS ProjectID, p.Name AS ProjectName, "
    ."t.ID AS TaskID, t.Name AS TaskName, tc.Date AS Date, tc.HoursWorked AS HoursWorked "
    ."FROM tblTasks_Comments tc "
    ."LEFT JOIN tblUsers AS u ON u.ID = tc.UserID "
    ."LEFT JOIN tblTasks AS t ON tc.TaskID = t.ID "
    ."LEFT JOIN tblProjects AS p ON p.ID = t.ProjectID "
    ."LEFT JOIN tblClients AS c ON c.ID = p.ClientID "
    ."WHERE tc.Date BETWEEN '%s' AND '%s' AND t.ID = tc.TaskID AND tc.HoursWorked > 0 "
    ."%s %s "
    ."ORDER BY FullName ASC, tc.Date ASC, c.Name ASC, p.Name ASC, t.Name ASC" );

define( 'SQL_SELECT_WORK_REPORT',
    "SELECT * FROM tblWorkReports WHERE ID = '%s'" );

define( 'SQL_SELECT_WORK_REPORTS',
    'SELECT * FROM tblWorkReports' );

define( 'SQL_DELETE_WORK_REPORT',
    "DELETE FROM tblWorkReports WHERE ID = '%s' LIMIT 1" );

define( 'SQL_SELECT_SAVED_REPORTS',
    "(SELECT ID, Name, 'project' AS Type, Created AS Date, Frequency FROM tblProjectReports) "
    ."UNION "
    ."(SELECT ID, Name, 'work' AS Type, Created AS Date, Frequency FROM tblWorkReports) "
    ."ORDER BY Type ASC, Name ASC" );

define('SQL_GET_DAY_ID',
       'SELECT ID FROM tblDay WHERE Epoch = %s');


