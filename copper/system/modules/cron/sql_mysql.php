<?php

define('SQL_GET_EMAIL_SUBJECT_DETAILS',
       'SELECT c.Name AS ClientName, p.Name AS ProjectName, t.Name AS TaskName
        FROM tblTasks t
        LEFT JOIN tblProjects p ON p.ID = t.ProjectID
        LEFT JOIN tblClients c ON c.ID = p.ClientID
        WHERE t.ID = \'%s\'');

define('SQL_LAST_INSERT', 'SELECT LAST_INSERT_ID()');

define('SQL_UPDATETIMESTAMP',
       'UPDATE sysAdminSettings SET Value = %s WHERE Setting = \'FirstLogin\'');

define('SQL_GET_TASKS',
       'SELECT *
        FROM tblTasks
        WHERE PercentComplete < 100
        AND EndDate = date_add( \'%s\', INTERVAL %s DAY)');

define('SQL_GET_TASK_ASSIGNED',
        'SELECT DISTINCT u.EmailAddress, CONCAT(u.FirstName, \' \', u.LastName) AS Name, u.FirstName
        FROM tblUsers u
        LEFT JOIN tblResource ON u.ID = tblResource.UserID
        LEFT JOIN tblTaskResource ON tblResource.ID = tblTaskResource.ResourceID
        WHERE tblTaskResource.TaskID = \'%1$s\' AND u.EmailNotify = 1');

define('SQL_GET_TASK_OWNER',
        'SELECT DISTINCT u.EmailAddress, CONCAT(u.FirstName, \' \', u.LastName) AS FullName, u.FirstName
        FROM tblUsers u
        LEFT JOIN tblTasks t ON t.Owner = u.ID
        WHERE t.ID = \'%1$s\' AND u.EmailNotify = 1');

define('SQL_GET_INVOICE',
       'SELECT * FROM tblInvoices_tfg WHERE ID=%s');


define( 'SQL_SELECT_PROJECT_REPORTS_USERS',
    "SELECT DISTINCT p.UserID, u.FirstName, CONCAT(u.FirstName, ' ', u.LastName) AS FullName, u.EmailAddress "
    ."FROM tblProjectReports p "
    ."LEFT JOIN tblUsers u ON u.ID = p.UserID "
    ."WHERE u.Active = 1 AND u.EmailNotify = 1" );

define( 'SQL_SELECT_PROJECT_REPORTS_FOR_USER',
    "SELECT *, DATEDIFF(NOW(), Created) AS DayShift, "
    ."PERIOD_DIFF(EXTRACT(YEAR_MONTH FROM NOW()), EXTRACT(YEAR_MONTH FROM Created)) AS MonthShift "
    ."FROM tblProjectReports "
    ."WHERE UserID = '%s' AND "
    ."("
    ." (DATEDIFF(NOW(), Created) %% 7 = 0 AND Frequency = 'W' ) OR "
    ." (DATEDIFF(NOW(), Created) %% 14 = 0 AND Frequency = 'F' ) OR "
    ." (DAY(NOW()) = DAY(Created) AND Frequency = 'M' ) "
    .")" );

define( 'SQL_SELECT_WORK_REPORTS_USERS',
    "SELECT DISTINCT p.UserID, u.FirstName, CONCAT(u.FirstName, ' ', u.LastName) AS FullName, u.EmailAddress "
    ."FROM tblWorkReports p "
    ."LEFT JOIN tblUsers u ON u.ID = p.UserID "
    ."WHERE u.Active = 1 AND u.EmailNotify = 1" );

define( 'SQL_SELECT_WORK_REPORTS_FOR_USER',
    "SELECT *, DATEDIFF(NOW(), Created) AS DayShift, "
    ."PERIOD_DIFF(EXTRACT(YEAR_MONTH FROM NOW()), EXTRACT(YEAR_MONTH FROM Created)) AS MonthShift "
    ."FROM tblWorkReports "
    ."WHERE UserID = '%s' AND "
    ."("
    ." (DATEDIFF(NOW(), Created) %% 7 = 0 AND Frequency = 'W' ) OR "
    ." (DATEDIFF(NOW(), Created) %% 14 = 0 AND Frequency = 'F' ) OR "
    ." (DAY(NOW()) = DAY(Created) AND Frequency = 'M' ) "
    .")" );

define( 'SQL_SELECT_PROJECT_REPORT',
    "SELECT * FROM tblProjectReports WHERE ID = '%s'" );

define( 'SQL_SELECT_PROJECT_IDS_FOR_CLIENTS',
    "SELECT ID FROM tblProjects WHERE ClientID IN (%s)" );

define( 'SQL_SELECT_PROJECT_DETAILS',
    "SELECT p.ID, p.Name, p.ProjectID, p.Status, c.Name AS ClientName "
    ."FROM tblProjects p "
    ."LEFT JOIN tblClients c ON c.ID = p.ClientID "
    ."ORDER BY c.Name ASC, p.Name ASC" );

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
    ."WHERE TaskID = '%s'" );

define( 'SQL_SELECT_TASK_HOURS_WORKED',
    "SELECT c.UserID, "
    ."(SELECT u.ChargeRate FROM tblUsers u WHERE u.ID = c.UserID) AS Rate, "
    ."SUM(COALESCE(c.HoursWorked, 0)) AS HoursWorked "
    ."FROM tblTasks_Comments c "
    ."WHERE c.TaskID = '%s' "
    ."AND c.Date BETWEEN '%s' AND '%s' "
    ."GROUP BY c.UserID" );

define( 'SQL_SELECT_WORK_REPORT',
    "SELECT * FROM tblWorkReports WHERE ID = '%s'" );

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

 
