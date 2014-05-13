<?php
// $Id$
define('SQL_GET_PROJECT_OTHER_ITEMS_COST',
       'SELECT SUM(Cost) AS Cost FROM `tblInvoices_Items_Other` WHERE `ProjectID` = %d');

define('SQL_GET_PROJECT_OTHER_ITEMS_CHARGE',
       'SELECT SUM(Charge) AS Charge FROM `tblInvoices_Items_Other` WHERE `ProjectID` = %d');

define('SQL_GET_CONTACT_NAME',
        'SELECT concat(c.FirstName," ",c.LastName) AS FullName
        FROM tblContacts c
        WHERE c.ID = %d;');

define('SQL_GET_CLIENTS_BY_TASK',
        'SELECT c.id, concat( c.FirstName," ", c.LastName) AS Name
        FROM tblContacts c
        LEFT JOIN tblProjects p ON p.ClientID = c.ClientID
        LEFT JOIN tblTasks t ON t.ProjectID = p.ID
        WHERE t.ID = %d');

define('SQL_TASKS_GET_BASIC_DETAILS',
       'SELECT t.ID, t.Name, t.Description
        FROM tblTasks t
        WHERE t.ID = \'%s\'');

define('SQL_DELETE_OTHER_ITEM',
        'DELETE FROM tblInvoices_Items_Other WHERE ID = %s
        ');

define('SQL_GET_BUDGET_RESOURCES',
        'SELECT CONCAT(FirstName, \' \', LastName) AS Name, t.CostRate, t.ChargeRate, sum(t.HoursWorked) as HoursWorked
        FROM tblTasks_Comments t
        LEFT JOIN tblUsers u ON u.ID = t.UserID
        WHERE TaskID = \'%s\'
        AND t.OutOfScope = \'0\'
        GROUP BY u.ID');

define('SQL_UPDATE_TASK_HOURSWORKED_ACTUAL_BUDGET',
       'UPDATE tblTasks SET HoursWorked = \'%s\', ActualBudget = \'%s\' WHERE ID = %d');

define('SQL_UPDATE_PROJECT_ACTUAL_BUDGET',
       'UPDATE tblProjects SET ActualBudget = \'%s\' WHERE ID = %d');

define('SQL_GET_TASK_COST',
       'SELECT SUM(HoursWorked) AS HoursWorked, SUM(Cost) AS Cost
        FROM vwTaskComments
        WHERE TaskID = %d');

define('SQL_GET_TASK_CHARGE',
       'SELECT SUM(HoursWorked) AS HoursWorked, SUM(Charge) AS Charge
        FROM vwTaskComments
        WHERE TaskID = %d');

define('SQL_GET_PROJECT_COST',
       'SELECT SUM(Cost) AS Cost
        FROM vwTaskComments
        WHERE ProjectID = %d');

define('SQL_GET_PROJECT_CHARGE',
       'SELECT SUM(Charge) AS Charge
        FROM vwTaskComments
        WHERE ProjectID = %d');

define('SQL_GET_TASK_USER_DAY_HOURS',
       'SELECT SUM(HoursWorked) AS HoursWorked
        FROM vwTaskComments
        WHERE TaskID = %d AND UserID = %d AND DATE(Date) = \'%s\'');

define('SQL_GET_PROJECT_ID',
       'SELECT ProjectID FROM tblProjects WHERE ID = %s LIMIT 1');

define('SQL_COUNT_TASK_ISSUES',
       'SELECT COUNT(*) AS count FROM tblTasks_Comments WHERE Issue = 1 AND TaskID = \'%d\'');

define('SQL_GET_EMAIL_SUBJECT_DETAILS',
       'SELECT c.Name AS ClientName, p.Name AS ProjectName, t.Name AS TaskName, p.ID AS ProjectID, t.Description
        FROM tblTasks t
        LEFT JOIN tblProjects p ON p.ID = t.ProjectID
        LEFT JOIN tblClients c ON c.ID = p.ClientID
        WHERE t.ID = \'%s\'');

define('SQL_PROJECTS_GET_USERS_EMAIL',
       'SELECT u.ID, u.FirstName, u.LastName, u.EmailAddress
        FROM tblUsers u
        LEFT JOIN tblResource ON u.ID = tblResource.UserID
        LEFT JOIN tblTaskResource ON tblResource.ID = tblTaskResource.ResourceID
        WHERE tblTaskResource.TaskID IN (SELECT ID FROM tblTasks WHERE ProjectID = \'%1$d\') AND u.EmailNotify = 1');

define('SQL_GET_FILE_IDS',
        'SELECT ID
         FROM tblFiles
         WHERE ProjectID = \'%s\'');

define('SQL_GET_TASK_IDS',
        'SELECT ID
         FROM tblTasks
         WHERE ProjectID = \'%s\'');

define('SQL_GET_PROJECTID_FROM_BILL',
        'SELECT ProjectID FROM tblInvoices WHERE ID = %s');

define('SQL_GET_CHARGE_RATE',
        'SELECT ChargeRate FROM tblUsers WHERE ID = %s');

define('SQL_GET_COST_RATE',
        'SELECT CostRate FROM tblUsers WHERE ID = %s');


define('SQL_GET_LAST_CONTACT',
        'SELECT tc.Date, t.ID, p.ID
        FROM tblTasks_Comments tc
        LEFT JOIN tblTasks t ON t.ID = tc.TaskID
        LEFT JOIN tblProjects p ON p.ID = t.ProjectID
        WHERE p.ID = %s
        AND tc.Contact = %s
        ORDER BY Date DESC
        LIMIT 1');

//change_log 6.
define('SQL_SELECT_CLIENT_COLOUR', 
        'SELECT Colour FROM tblClients WHERE ID = %s');

define('SQL_GET_BILLING_ADDRESS',
        'SELECT CONCAT(c.Address1, \'\n\', c.Address2, \'\n\', c.City, \'\n\', c.State, \'  \', c.Postcode) As Address
        FROM tblClients c
        LEFT JOIN tblProjects p ON p.ClientID = c.ID
        WHERE p.ID = %s');

define('SQL_CREATE_FOLDER',
        'INSERT INTO tblFolders (ProjectID, Folder)
        VALUES (%s, \'%s\')');

define('SQL_COUNT_FILES_IN_FOLDER_PROJECT',
        'SELECT COUNT(ID) AS count FROM tblFiles WHERE Folder = %1$s AND ProjectID = %2$s');
//change_log 7.
define('SQL_DELETE_INVOICE_ITEMS',
        'DELETE FROM tblInvoices_Items WHERE InvoiceID = %d');

define('SQL_DELETE_INVOICE_ITEM',
        'DELETE FROM tblInvoices_Items WHERE InvoiceID = %d AND ID = %d');

define('SQL_DELETE_INVOICE',
        'DELETE FROM tblInvoices WHERE ID = %s LIMIT 1');

define('SQL_GET_INVOICE_ADDITIONAL_LINE_ITEM',
        'SELECT * FROM tblInvoices_Items WHERE AdditionalID = %s AND InvoiceID = %s');

define('SQL_GET_INVOICE_LINE_ITEMS',
        'SELECT * FROM tblInvoices_Items WHERE InvoiceID = %s');

define('SQL_GET_INVOICE_LINE_ITEM',
        'SELECT * FROM tblInvoices_Items WHERE TaskID = %s AND InvoiceID = %s');

define('SQL_GET_INVOICE_DETAILS',
        'SELECT * FROM tblInvoices WHERE ID = %s');

define('SQL_CREATE_INVOICE',
        'INSERT INTO tblInvoices (Quote, ProjectID, Title, DateCreated, CreatedBy, Status, Due)
        VALUES (\'%s\', \'%s\', \'%s\', NOW(), \'%s\', \'%s\', NOW())');

define('SQL_UPDATE_INVOICE',
        'UPDATE tblInvoices
        SET Quote = \'%s\',
        Title = \'%s\',
        CreatedBy = \'%s\',
        Due = \'%s\'
        WHERE ID = %s');

define('SQL_UPDATE_INVOICE_SIMPLE',
        'UPDATE tblInvoices
        SET Title = \'%s\',
        Status = \'%s\',
        Due = \'%s\'
        WHERE ID = %s');

define('SQL_GET_TASK_BUDGETS',
        'SELECT TargetBudget, ActualBudget FROM tblTasks WHERE ProjectID = %s');

define('SQL_ASSIGNED_COUNT',
         'SELECT COUNT(*)
         FROM tblTaskResource
         LEFT JOIN tblResource ON tblResource.ID = tblTaskResource.ResourceID
         WHERE tblTaskResource.TaskID = %s
        AND tblResource.UserID = %s');

define('SQL_SELECT_PROJECT_IDS',
       'SELECT ID FROM tblProjects WHERE ClientID IN (%s)');

define('SQL_GET_TASK_OWNER',
        'SELECT u.EmailAddress, CONCAT(u.FirstName, \' \', u.LastName) AS FullName, u.FirstName
        FROM tblUsers u
        LEFT JOIN tblTasks t ON t.Owner = u.ID
        WHERE t.ID = \'%1$s\' AND u.EmailNotify = 1');

define('SQL_GET_PROJECT_OWNER',
        'SELECT u.EmailAddress, CONCAT(u.FirstName, \' \', u.LastName) AS FullName, u.FirstName
        FROM tblUsers u
        LEFT JOIN tblProjects p ON p.Owner = u.ID
        WHERE p.ID = %s AND u.EmailNotify = 1');

define('SQL_GET_TASK_OWNER_ID',
        'SELECT Owner FROM tblTasks WHERE ID = %s');

define('SQL_GET_CLIENT_IDS',
        'SELECT ID FROM tblClients');

define('SQL_GET_USERS_WITH_READ_PERMS',
        'SELECT u.ID, CONCAT( u.FirstName, \' \', u.LastName ) AS FullName
        FROM sysUserPermissions p
        LEFT JOIN tblUsers u ON u.ID = p.UserID
        WHERE ObjectID = \'%s\'
        AND ItemID = %s
        AND AccessID = 1
        AND u.Active = 1
        ORDER BY FullName');

define('SQL_GET_USERS_WITH_WRITE_PERMS',
        'SELECT u.ID, CONCAT( u.FirstName, \' \', u.LastName ) AS FullName
        FROM sysUserPermissions p
        LEFT JOIN tblUsers u ON u.ID = p.UserID
        WHERE ObjectID = \'%s\'
        AND ItemID = %s
        AND AccessID = 2
        AND u.Active = 1
        ORDER BY FullName');

define('SQL_GET_GROUPS_WITH_READ_PERMS',
        'SELECT g.ID, g.Name
        FROM sysGroupPermissions p
        LEFT JOIN tblGroups g ON g.ID = p.GroupID
        WHERE p.ObjectID = \'%s\'
        AND p.ItemID = %s
        AND p.AccessID = 1
        ORDER BY g.Name');

define('SQL_GET_GROUPS_WITH_WRITE_PERMS',
        'SELECT g.ID, g.Name
        FROM sysGroupPermissions p
        LEFT JOIN tblGroups g ON g.ID = p.GroupID
        WHERE p.ObjectID = \'%s\'
        AND p.ItemID = %s
        AND p.AccessID = 2
        ORDER BY g.Name');

define('SQL_GET_FILE_DETAILS',
       'SELECT * FROM tblFiles WHERE ID = \'%s\'');

define('SQL_CONTACTS_LIST',
       'SELECT c.ID, CONCAT(c.FirstName,\' \',c.LastName) AS ContactName, c.EmailAddress1, c.Phone1, c.KeyContact
        FROM tblContacts AS c
        INNER JOIN tblClients AS cl ON cl.ID = c.ClientID
        WHERE cl.ID = \'%3$s\'
        ORDER BY c.KeyContact DESC, %1$s %2$s');

define('SQL_FILES_GET_ACTIVITY',
       'SELECT f.Activity, f.Time AS Date, f.Version, CONCAT(u.FirstName, \' \', u.LastName) AS User
        FROM tblFile_Log AS f
        LEFT JOIN tblUsers AS u ON u.ID = f.UserID
        WHERE f.FileID = \'%1$s\'
        ORDER BY f.Time DESC');

define('SQL_GET_PROJECTS',
       'SELECT p.ID, p.Name, c.Name AS ClientName
        FROM tblProjects p
        LEFT JOIN tblClients c ON c.ID = p.ClientID
        ORDER BY ClientName, Name ASC');

define('SQL_GET_PROJECTS_IN',
       'SELECT p.ID, p.Name, c.Name AS ClientName
        FROM tblProjects p
        LEFT JOIN tblClients c ON c.ID = p.ClientID
        WHERE p.ID IN (%s)
        ORDER BY ClientName, Name ASC');

define('SQL_PROJECTS_LIST_ALL',
       'SELECT
           p.ID, p.ProjectID, p.Status, p.Priority, p.TargetBudget, p.ActualBudget, p.ClientID, c.Name as ClientName, p.Name as ProjectName,
           p.StartDate, p.EndDate, p.Colour, AVG(IFNULL(t.PercentComplete, 0)) AS PercentComplete, MAX(t.LatestActivity) as LatestActivity,
           u.FirstName, u.LastName, p.Owner
        FROM tblProjects AS p
        LEFT JOIN tblUsers AS u ON u.ID = p.Owner
        LEFT JOIN tblTasks AS t ON t.ProjectID = p.ID
        INNER JOIN tblClients AS c ON c.ID = p.ClientID
        WHERE p.Active = \'%2$s\' %1$s c.Archived != \'%2$s\'
        GROUP BY p.ID
        ORDER BY %3$s %4$s');

define('SQL_LAST_CHECKED_OUT',
        'SELECT MAX(f.Time) AS Time, CONCAT(u.FirstName,\' \',u.LastName) AS Name
        FROM tblFile_Log f
        LEFT JOIN tblUsers u ON u.ID = UserID
        WHERE f.FileID = \'%1$s\' AND f.Activity=\'Checked Out\'
        GROUP BY f.UserID');

define('SQL_PROJECTS_LIST',
       'SELECT
           p.ID, p.ProjectID, p.Status, p.Priority, p.TargetBudget, p.ActualBudget, p.ClientID, c.Name as ClientName, p.Name as ProjectName,
           p.StartDate, p.EndDate, p.Colour, AVG(IFNULL(t.PercentComplete, 0)) AS PercentComplete, MAX(t.LatestActivity) as LatestActivity,
           u.FirstName, u.LastName, p.Owner
        FROM tblProjects AS p
        LEFT JOIN tblUsers AS u ON u.ID = p.Owner
        LEFT JOIN tblTasks AS t ON t.ProjectID = p.ID
        INNER JOIN tblClients AS c ON c.ID = p.ClientID
        WHERE p.ID IN (%1$s) AND (p.Active = \'%3$s\' %2$s c.Archived != \'%3$s\')
        GROUP BY p.ID
        ORDER BY %4$s %5$s');

define('SQL_PROJECTS_LIST_FOR_CLIENT',
       'SELECT
           p.ID, p.Status, p.Priority, p.TargetBudget, p.ActualBudget, p.ClientID, c.Name as ClientName, p.Name as ProjectName,
           p.StartDate, p.EndDate, p.Colour, AVG(IFNULL(t.PercentComplete, 0)) AS PercentComplete,
           u.FirstName, u.LastName, p.Owner
        FROM tblProjects AS p
        LEFT JOIN tblUsers AS u ON u.ID = p.Owner
        LEFT JOIN tblTasks AS t ON t.ProjectID = p.ID
        INNER JOIN tblClients AS c ON c.ID = p.ClientID
        WHERE p.ID IN (%1$s) AND p.Active = \'%2$s\' and c.ID = \'%5$s\'
        GROUP BY p.ID
        ORDER BY %3$s %4$s');

define('SQL_PROJECT_CREATE',
       'INSERT INTO tblProjects (ClientID, ProjectID, Name, Owner, StartDate, EndDate, ActualEndDate, Status, Priority, Colour, Description, TargetBudget, ActualBudget, Active)
        VALUES (\'%1$s\', \'%2$s\', \'%3$s\', \'%4$s\', %5$s, %6$s, %7$s, \'%8$s\', \'%9$s\', \'%10$s\', \'%11$s\', \'%12$s\', 0, \'%13$s\')');

define('SQL_CREATE_USER_PERMISSIONS',
       'INSERT INTO sysUserPermissions (UserID, ObjectID, ItemID, AccessID)
        VALUES (\'%s\', \'projects\',\'%s\',\'%s\')');

define('SQL_DELETE_USER_PERMISSIONS',
       'DELETE FROM sysUserPermissions WHERE UserID = %1$d AND ObjectID = \'projects\' AND ItemID = %2$d');

define('SQL_CREATE_GROUP_PERMISSIONS',
       'INSERT INTO sysGroupPermissions (GroupID, ObjectID, ItemID, AccessID)
        VALUES (\'%s\', \'projects\',\'%s\',\'%s\')');

define('SQL_DELETE_GROUP_PERMISSIONS',
       'DELETE FROM sysGroupPermissions WHERE GroupID = %1$d AND ObjectID = \'projects\' AND ItemID = %2$d');

define('SQL_CLEAR_USER_PERMISSIONS',
       'DELETE FROM sysUserPermissions WHERE ObjectID = \'projects\' AND ItemID = \'%s\'');

define('SQL_CLEAR_GROUP_PERMISSIONS',
       'DELETE FROM sysGroupPermissions WHERE ObjectID = \'projects\' AND ItemID = \'%s\'');

define('SQL_PROJECT_COPY',
       'INSERT INTO tblProjects (ClientID, ProjectID, Name, Owner, StartDate, EndDate, ActualEndDate, Status, Priority, Colour, Description, TargetBudget, ActualBudget, Active)
        VALUES (\'%1$s\', \'%2$s\', \'%3$s\', \'%4$s\', %5$s, %6$s, %7$s, \'%8$s\', \'%9$s\', \'%10$s\', \'%11$s\', \'%12$s\', 0, \'%13$s\')');

define('SQL_LAST_INSERT', 'SELECT LAST_INSERT_ID()');

define('SQL_PROJECT_UPDATE',
       'UPDATE tblProjects SET
        ClientID = \'%1$s\',
        ProjectID = \'%2$s\',
        Name = \'%3$s\',
        Owner = \'%4$s\',
        StartDate = %5$s,
        EndDate = %6$s,
        ActualEndDate = %7$s,
        Status = \'%8$s\',
        Priority = \'%9$s\',
        Colour = \'%10$s\',
        Description = \'%11$s\',
        TargetBudget = \'%12$s\',
        Active = \'%13$s\'
        WHERE ID = \'%14$s\'');

define('SQL_PROJECT_UPDATE_NO_BUDGET',
        'UPDATE tblProjects SET
        ClientID = \'%1$s\',
        ProjectID = \'%2$s\',
        Name = \'%3$s\',
        Owner = \'%4$s\',
        StartDate = %5$s,
        EndDate = %6$s,
        ActualEndDate = %7$s,
        Status = \'%8$s\',
        Priority = \'%9$s\',
        Colour = \'%10$s\',
        Description = \'%11$s\',
        Active = \'%12$s\'
        WHERE ID = \'%13$s\'');

define('SQL_GET_PROJECT',
       'SELECT p.*, c.Name AS ClientName, u.FirstName, u.LastName, u.EmailAddress,
        AVG(IFNULL(t.PercentComplete, 0)) AS PercentComplete, MAX(t.LatestActivity) AS LatestActivity
        FROM tblProjects p
        INNER JOIN tblClients c ON c.ID = p.ClientID
        LEFT JOIN tblUsers u ON u.ID = p.Owner
        LEFT JOIN tblTasks t ON t.ProjectID = p.ID
        WHERE p.ID = \'%s\'
        GROUP BY p.ID');

//change_log 2.
define('SQL_GET_TASK_ASSIGNED',
       'SELECT DISTINCT u.EmailAddress, CONCAT(u.FirstName, \' \', u.LastName) AS Name, u.FirstName
        FROM tblUsers u
        LEFT JOIN tblResource ON u.ID = tblResource.UserID
        LEFT JOIN tblTaskResource ON tblResource.ID = tblTaskResource.ResourceID
        LEFT JOIN tblTasks t ON t.ID = tblTaskResource.TaskID
        WHERE t.ProjectID = \'%1$s\'');

//change_log 2.
define('SQL_GET_TASK_OWNERS',
       'SELECT DISTINCT u.ID, u.EmailAddress, u.FirstName, u.LastName
        FROM tblUsers u
        LEFT JOIN tblTasks t ON t.Owner = u.ID
        WHERE t.ProjectID = \'%1$s\' AND u.ID = t.Owner AND u.EmailNotify = 1');

define('SQL_GET_PROJECT_NAME',
       'SELECT Name FROM tblProjects WHERE ID = %d');

define('SQL_GET_PROJECT_DETAILS',
       'SELECT c.ID AS ClientID, c.Name AS ClientName, p.ID AS ProjectID, p.Name as ProjectName, p.Colour,
        AVG(IFNULL(t.PercentComplete, 0)) AS PercentComplete
        FROM tblProjects p
        INNER JOIN tblClients c ON c.ID = p.ClientID
        LEFT JOIN tblTasks AS t ON t.ProjectID = p.ID
        WHERE p.ID = \'%s\'
        GROUP BY p.ID');

define('SQL_GET_PROJECT_FILES',
       'SELECT f.ID, f.ProjectID, f.TaskID, f.FileName, f.Description, fo.Folder, f.Version, f.CheckedOut, f.CheckedOutUserID, f.Type,  t.ID AS TaskID, t.Name AS TaskName, f.Size, f.Date, f.Linked, f.RealName, CONCAT(u.FirstName,\' \', u.LastName) AS UploadedBy
        FROM tblFiles f
        LEFT JOIN tblTasks t ON t.ID = f.TaskID
        LEFT JOIN tblUsers u ON u.ID = f.Owner
        LEFT JOIN tblFolders fo ON fo.ID = f.Folder
        WHERE f.ProjectID = \'%s\'
        ORDER BY %2$s %3$s');


//change_log 8.
define('SQL_GET_PROJECT_TASKS',
       'SELECT t.*, u.FirstName, u.LastName, p.Colour, c.Name AS ClientName
        FROM tblTasks t
        LEFT JOIN tblUsers u ON u.ID = t.Owner
        LEFT JOIN tblProjects p ON p.ID = t.ProjectID
        LEFT JOIN tblClients c ON c.ID = p.ClientID
        WHERE t.ProjectID = \'%1$d\'
        ORDER BY %2$s %3$s');

define('SQL_GET_PROJECT_TASKS_WITH_HOURS',
       'SELECT t.ID, t.ProjectID, t.Name, t.Description, t.PercentComplete, t.Duration, t.HoursWorked AS ActualDuration, t.Priority, t.Sequence, t.StartDate, t.EndDate, t.LatestActivity, u.FirstName, u.LastName, u.EmailAddress, t.Owner, t.TargetBudget, t.ActualBudget, t.Name AS ClientName, t.Indent,
        (SELECT SUM(HoursCommitted) FROM tblTaskResourceDay trd WHERE trd.TaskID = t.ID) AS HoursCommitted,
        (SELECT SUM(HoursWorked) FROM tblTasks_Comments tc WHERE tc.TaskID = t.ID) AS HoursWorked
        FROM tblTasks t
        LEFT JOIN tblUsers u ON u.ID = t.Owner
        WHERE t.ProjectID = \'%1$s\'
        ORDER BY %2$s %3$s');

define('SQL_GET_PROJECT_TASKS_WITH_HOURS_SINGLE',
       'SELECT t.ID, t.ProjectID, t.Name, t.Description, t.PercentComplete, t.Duration, t.HoursWorked AS ActualDuration, t.Priority, t.Sequence, t.StartDate, t.EndDate, t.LatestActivity, u.FirstName, u.LastName, u.EmailAddress, t.Owner, t.TargetBudget, t.ActualBudget, t.Name AS ClientName, t.Indent,
        (SELECT SUM(HoursCommitted) FROM tblTaskResourceDay trd WHERE trd.TaskID = t.ID) AS HoursCommitted,
        (SELECT SUM(HoursWorked) FROM tblTasks_Comments tc WHERE tc.TaskID = t.ID) AS HoursWorked
        FROM tblTasks t
        LEFT JOIN tblUsers u ON u.ID = t.Owner
        WHERE t.ID = \'%1$s\'');

define('SQL_GET_TASKLIST',
       'SELECT t.ID, t.Name
        FROM tblTasks t
        WHERE t.ProjectID = \'%s\'');

define('SQL_GET_TASKLIST_GANTT',
       'SELECT t.ID, t.Name, CONCAT(u.FirstName," ",u.LastName) AS Owner,
        t.StartDate AS DateStart, t.EndDate AS DateEnd, t.Duration, t.HoursWorked, t.Sequence, t.Indent
        FROM tblTasks t
        LEFT JOIN tblUsers AS u ON u.ID = t.Owner
        WHERE t.ProjectID = \'%s\' ORDER BY t.Sequence');

define('SQL_GET_PROJECT_DATES',
       'SELECT
                MIN(t.StartDate) AS dateStart,
                MAX(t.EndDate) AS dateEnd
        FROM tblTasks t
        WHERE t.ProjectID = \'%s\'');

define('SQL_GET_PROJECT_COLOUR',
       'SELECT Colour
        FROM tblProjects
        WHERE ID = \'%s\'');

define('SQL_GET_USERNAME',
        'SELECT CONCAT(FirstName, \' \', LastName)
        FROM tblUsers
        WHERE ID = %s');

define('SQL_GET_GROUP_NAME',
       'SELECT Name FROM tblGroups WHERE ID = %d');

define('SQL_SELECT_PROJECT_NAME',
        'SELECT Name
        FROM tblProjects
        WHERE ID = \'%s\'');

define('SQL_GET_PROJECT_TASK_NAME',
       'SELECT Name
        FROM tblTasks
        WHERE ID = \'%s\'');

define('SQL_PROJECT_TASK_SEQUENCE_MAX',
       'SELECT MAX(Sequence)
        FROM tblTasks
        WHERE ProjectID = \'%s\'');

define('SQL_UPDATE_START_VALUE',
       'UPDATE sysAdminSettings
        SET Value = \'%s\'
        WHERE Setting = \'IDStartValue\'');

define('SQL_PROJECT_TASK_SEQUENCE_UPDATE',
       'UPDATE tblTasks
        SET sequence = \'%1$s\'
        WHERE ID = \'%2$s\'');

define('SQL_PROJECT_TASK_GET_SEQUENCE_BY_TASK',
       'SELECT sequence
        FROM tblTasks
        WHERE ID = \'%s\'');

define('SQL_PROJECT_TASK_GET_TASK_BY_SEQUENCE',
       'SELECT ID
        FROM tblTasks
        WHERE sequence = \'%1$s\' AND ProjectID = \'%2$s\'');

define('SQL_PROJECT_TASKS',
       'SELECT ID, Name
        FROM tblTasks
        WHERE ProjectID = %1$s
        AND ID != %2$s');

define('SQL_PROJECT_TASK_DEPENDENCIES',
       'SELECT td.TaskDependencyID, td.DependencyType, t.Name
        FROM tblTasks_Dependencies td, tblTasks t
        WHERE td.TaskID = %s AND t.ID = td.TaskDependencyID');

define('SQL_PROJECT_TASK_DEPENDENCY_ADD',
       'INSERT INTO tblTasks_Dependencies
        (TaskID, TaskDependencyID, DependencyType)
        VALUES
        (%1$s, %2$s, %3$s)
        ');

define('SQL_PROJECT_TASK_DEPENDENCY_REMOVE',
       'DELETE FROM tblTasks_Dependencies
        WHERE
        TaskID = %1$s
        AND TaskDependencyID = %2$s
        AND DependencyType = %3$s
        ');

define('SQL_GET_CLIENTS_ALL',
       'SELECT ID, Name FROM tblClients WHERE Archived = 0 ORDER BY Name ASC');

define('SQL_GET_CLIENTS',
       'SELECT ID, Name FROM tblClients WHERE ID IN (%s) AND Archived = 0 ORDER BY Name ASC');

define('SQL_GET_CLIENTS_PLUS',
       'SELECT ID, Name FROM tblClients WHERE (ID IN (%s) OR ID = \'%s\') AND Archived = 0 ORDER BY Name ASC');

define('SQL_GET_USERS',
       'SELECT ID, FirstName, LastName FROM tblUsers WHERE Active = \'1\' ORDER BY FirstName, LastName');

define('SQL_GET_GROUPS_MINUS',
       'SELECT ID, Name
        FROM tblGroups
        WHERE ID NOT IN (%s)
        ORDER BY Name');

define('SQL_GET_RESOURCES_MINUS',
       'SELECT r.ID, CONCAT(u.FirstName, \' \', u.LastName) AS FullName
        FROM tblResource r
        LEFT JOIN tblUsers u ON u.ID = r.UserID
        WHERE u.Active = \'1\' AND r.ID NOT IN (%s)
        ORDER BY u.FirstName, u.LastName');

define('SQL_GET_USERS_MINUS',
       'SELECT ID, FirstName, LastName
        FROM tblUsers
        WHERE Active = \'1\' AND ID NOT IN (%s)
        ORDER BY FirstName, LastName');

define('SQL_GET_RESOURCE_NAME',
       'SELECT CONCAT(u.FirstName, \' \', u.LastName)
        FROM tblResource r
        LEFT JOIN tblUsers u ON u.ID = r.UserID
        WHERE r.ID = %s');

define('SQL_UPDATE_TASK',
       'UPDATE tblTasks SET
       HoursWorked = HoursWorked + \'%2$s\',
       PercentComplete = \'%3$s\',
       Status = \'%4$s\',
       ActualBudget = ActualBudget + \'%5$s\'
       WHERE ID = \'%1$s\'');

define('SQL_UPDATE_TASK_UPON_DELETE',
       'UPDATE tblTasks SET
       HoursWorked = HoursWorked - \'%2$s\',
       ActualBudget = ActualBudget - \'%3$s\'
       WHERE ID = \'%1$s\'');

define('SQL_INSERT_COMMENT',
       'INSERT INTO tblTasks_Comments (UserID, TaskID, Subject, Body, Date, HoursWorked, Issue, Contact, OutOfScope, CostRate, ChargeRate)
        VALUES (\'%1$s\', \'%2$s\', \'%3$s\', \'%4$s\', \'%9$s\', \'%5$s\', \'%6$s\', \'%7$s\', \'%8$s\', \'%10$s\', \'%11$s\')');

define('SQL_UPDATE_COMMENT',
        'UPDATE tblTasks_Comments SET
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

define('SQL_DELETE_COMMENT','DELETE FROM tblTasks_Comments WHERE ID = %s');
define('SQL_GET_COMMENT','SELECT * FROM tblTasks_Comments WHERE ID = %s AND TaskID = %s');
define('SQL_GET_COMMENT_USER','SELECT UserID FROM tblTasks_Comments WHERE ID = %s');
define('SQL_GET_COMMENT_FOR_DELETE','SELECT UserID, Date, HoursWorked FROM tblTasks_Comments WHERE ID = %s');

define('SQL_TASKS_GET_DETAILS',
       'SELECT t.ID, t.ProjectID, t.Name, t.HoursWorked, t.PercentComplete, t.StartDate, t.EndDate,
       t.Description, t.Duration, u.FirstName, u.LastName, u.EmailAddress, t.Sequence, t.TargetBudget, t.ActualBudget, t.Indent
       FROM tblTasks t
       LEFT JOIN tblUsers u ON u.ID = t.Owner
       WHERE t.ID = \'%s\'');

define('SQL_TASKS_GET_DETAILS_ALL',
       'SELECT t.*, p.Name AS ProjectName, c.ID AS ClientID, c.Name AS ClientName, u.FirstName, u.LastName, u.EmailAddress,
        (SELECT SUM(HoursCommitted) FROM tblTaskResourceDay trd WHERE trd.TaskID = \'%1$s\') AS HoursCommitted
        FROM tblTasks t
        LEFT JOIN tblProjects p ON p.ID = t.ProjectID
        LEFT JOIN tblClients c ON c.ID = p.ClientID
        LEFT JOIN tblUsers u ON u.ID = t.Owner
        WHERE t.ID = \'%1$s\'');

define('SQL_TASKS_GET_FILES',
       'SELECT f.ID, f.FileName, f.Type, f.Size, f.Date, f.CheckedOut, f.CheckedOutUserID, f.Linked, f.RealName, fl.Activity, u.FirstName, u.LastName 
        FROM tblFiles f 
        LEFT JOIN tblFile_Log fl ON f.ID = fl.FileID AND fl.ID = (SELECT MAX(ID) FROM tblFile_Log WHERE tblFile_Log.FileID = f.ID)
        LEFT JOIN tblUsers u ON u.ID = fl.UserID
        WHERE f.TaskID = \'%s\'
        ORDER BY f.FileName ASC');

define('SQL_TASKS_GET_FILE',
       'SELECT * FROM tblFiles WHERE ID = \'%s\' AND TaskID = \'%s\'');

//change_log 2.
define('SQL_TASKS_GET_USERS_EMAIL',
       'SELECT u.ID, u.FirstName, u.LastName, u.EmailAddress, CONCAT(u.FirstName, \' \', u.LastName) AS FullName
        FROM tblUsers u
        LEFT JOIN tblResource ON u.ID = tblResource.UserID
        LEFT JOIN tblTaskResource ON tblResource.ID = tblTaskResource.ResourceID
        WHERE tblTaskResource.TaskID = \'%1$s\' AND u.EmailNotify = 1');

define('SQL_TASKS_GET_COMMENTS',
       'SELECT t.ID, t.UserID, t.Subject, t.Body, t.Date, IFNULL(t.HoursWorked, 0) AS HoursWorked, t.Issue, t.OutOfScope,
        u.FirstName, u.LastName, c.id AS ContactID, concat( c.FirstName," ", c.LastName) AS ContactName, ta.PercentComplete
        FROM tblTasks_Comments t
        INNER JOIN tblUsers u ON u.ID = t.UserID
        LEFT JOIN tblContacts c ON c.ID = t.Contact
        LEFT JOIN tblTasks ta ON ta.ID = t.TaskID
        WHERE t.TaskID = \'%s\'
        ORDER BY Date DESC');

define('SQL_PROJECT_GET_COMMENTS',
       'SELECT t.ID, t.UserID, t.Subject, t.Body, t.Date,IFNULL(t.HoursWorked, 0) AS HoursWorked, t.Issue, t.OutOfScope, u.FirstName, u.LastName
        FROM tblTasks
         LEFT JOIN tblTasks_Comments t ON t.TaskID = tblTasks.ID
        INNER JOIN tblUsers u ON u.ID = t.UserID
        WHERE tblTasks.ProjectID = \'%s\'
        ORDER BY t.Date DESC');

define('SQL_TASK_COPY',
       'INSERT INTO tblTasks (Name, ProjectID, Owner, StartDate, Duration, HoursWorked, EndDate, Status, Priority, PercentComplete, Description, RelatedURL, Sequence, Indent, TargetBudget, ActualBudget)
        VALUES (\'%1$s\', \'%2$s\', \'%3$s\', \'%4$s\', \'%5$s\', \'0\', \'%6$s\', \'0\', \'%7$s\', \'%8$s\', \'%9$s\', \'%10$s\', \'%11$s\', \'%12$s\', \'%13$s\', \'%14$s\')');

define('SQL_TASK_UPDATE',
       'UPDATE tblTasks SET
        Name = \'%1$s\',
        ProjectID = \'%2$s\',
        Owner = \'%3$s\',
        StartDate = %4$s,
        Duration = \'%5$s\',
        EndDate = %6$s,
        Priority = \'%7$s\',
        Status = \'%8$s\',
        Description = \'%9$s\',
        RelatedURL = \'%10$s\',
        TargetBudget = \'%11$s\',
        ActualBudget = \'%12$s\'
        WHERE ID = \'%13$s\'');

define('SQL_UPDATE_TASK_PERCENTAGE_COMPLETE',
       'UPDATE tblTasks SET
        PercentComplete = \'%1$s\',
        Status = \'%2$s\'
        WHERE ID = \'%3$s\'');

define('SQL_DELETE_TASK_RESOURCES', 'DELETE FROM tblTaskResource WHERE TaskID = %s');

define('SQL_TASKS_EMAIL',
'SELECT DISTINCT u.ID, u.FirstName, u.EmailAddress
FROM tblUsers u
LEFT JOIN tblResource ON u.ID = tblResource.UserID
LEFT JOIN tblTaskResource ON tblResource.ID = tblTaskResource.ResourceID
WHERE tblTaskResource.TaskID = \'%1$s\' AND u.EmailNotify = 1');

define('SQL_FILES_GET_DETAILS_ALL',
       'SELECT f.*, fo.Folder AS FolderName, u.FirstName, u.LastName FROM tblFiles f
        LEFT JOIN tblUsers u ON u.ID = f.Owner
        LEFT JOIN tblFolders fo ON fo.ID = f.Folder
        WHERE f.ID = \'%s\'');

define('SQL_FILE_CREATE',
       'INSERT INTO tblFiles (ProjectID, TaskID, FileName, Description, Type, Owner, Date, Size, Version, RealName, Folder, Linked)
       VALUES (\'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\')');

define('SQL_FILE_LOG',
        'INSERT INTO tblFile_Log (FileID, UserID, Time, Activity, Version)
         VALUES (\'%s\', \'%s\', \'%s\', \'%s\', \'%s\')');

define('SQL_FILE_UPDATE',
       'UPDATE tblFiles SET
        ProjectID = \'%s\',
        TaskID = \'%s\',
        FileName = \'%s\',
        Description = \'%s\',
        Type = \'%s\',
        Owner = \'%s\',
        Date = \'%s\',
        Size = \'%s\',
        Version = \'%s\',
        RealName = \'%s\',
        CheckedOut = 0,
        Folder = \'%s\',
        Linked = \'%s\'
        WHERE ID = \'%s\'');

define('SQL_FILE_UPDATE_NOFILE',
       'UPDATE tblFiles SET
        ProjectID = \'%s\',
        TaskID = \'%s\',
        Description = \'%s\',
        Version = \'%s\',
        RealName = \'%s\',
        CheckedOut = 0,
        Folder = \'%s\'
        WHERE ID = \'%s\'');

define('SQL_GET_PRIORITY',
        'SELECT * FROM sysPriority');

define('SQL_FILE_CHECKOUT',
        'UPDATE tblFiles SET
         CheckedOut = 1,
         CheckedOutUserID = \'%2$s\'
         WHERE ID = \'%1$s\'');

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

define('SQL_DELETE_TASK',
       'DELETE FROM tblTasks
        WHERE ID = %s');

define('SQL_DELETE_TASK_FILES',
       'DELETE FROM tblFiles
        WHERE TaskID = %s');

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

define('SQL_GET_HOURS',
       'SELECT HoursWorked
        FROM tblTasks_Comments
        WHERE ID = %s');

define('SQL_TASK_COUNT',
       'SELECT COUNT(ID)
        FROM tblTasks
        WHERE ProjectID = %s');

define('SQL_GANTT_TASK_CREATE',
       'INSERT INTO tblTasks (ProjectID, Owner, Name, StartDate, EndDate, Duration, Sequence, Indent, Description, RelatedURL)
        VALUES (\'%1$s\', \'%2$s\', \'%3$s\', \'%4$s\', \'%5$s\', \'%6$s\', \'%7$s\', \'%8$s\', \'\', \'\')');

define('SQL_GANTT_TASK_NAME_SAVE',
       'UPDATE tblTasks SET Name = \'%2$s\' WHERE ID = \'%1$s\'');

define('SQL_GANTT_TASK_STARTDATE_SAVE',
       'UPDATE tblTasks SET StartDate = \'%2$s\' WHERE ID = \'%1$s\'');

define('SQL_GANTT_TASK_ENDDATE_SAVE',
       'UPDATE tblTasks SET EndDate = \'%2$s\' WHERE ID = \'%1$s\'');

define('SQL_GANTT_TASK_DURATION_SAVE',
       'UPDATE tblTasks SET Duration = \'%2$s\' WHERE ID = \'%1$s\'');

define('SQL_GANTT_TASK_REORDER_DOWN',
       'UPDATE tblTasks SET sequence = sequence - 1 WHERE sequence >= \'%1$s\' AND sequence <= \'%2$s\' AND ProjectID = \'%3$s\'');

define('SQL_GANTT_TASK_REORDER_UP',
       'UPDATE tblTasks SET sequence = sequence + 1 WHERE sequence >= \'%2$s\' AND sequence <= \'%1$s\' AND ProjectID = \'%3$s\'');

define('SQL_GANTT_TASK_NEW',
       'UPDATE tblTasks SET sequence = sequence + 1 WHERE sequence >= \'$1s\' AND ProjectID = \'%2$s\'');

define('SQL_GANTT_TASK_REORDER_SET',
       'UPDATE tblTasks SET sequence = \'%2$s\' WHERE ID = \'%1$s\'');
// end new code

// start new code for task commitment by orca 11/06

define('SQL_GET_TASK_RESOURCES',
            'SELECT DISTINCT tblResource.ID, tblUsers.ID AS UserID, CONCAT(tblUsers.FirstName, \' \', tblUsers.LastName) AS FullName
             FROM tblResource
             LEFT JOIN tblUsers ON tblResource.UserID = tblUsers.ID
             LEFT JOIN tblTaskResource ON tblResource.ID = tblTaskResource.ResourceID
             LEFT JOIN tblTasks ON tblTasks.ID = tblTaskResource.TaskID
             WHERE tblTasks.ID = \'%1$s\'
             ORDER BY tblResource.ID');

define('SQL_UPDATE_TASK_RESOURCE_DAY',
       'UPDATE tblTaskResourceDay
        SET HoursCompleted = \'%s\'
        WHERE TaskID = %d AND ResourceID = %d AND DayID = %d');

define('SQL_GET_DAY_ID',
       'SELECT ID FROM tblDay WHERE Epoch = %s');

define('SQL_GET_DAY_ID_BY_DATE',
        'SELECT ID FROM tblDay WHERE Day = %d AND Month = %d AND Year = %d');

define('SQL_GET_RESOURCE_ID','SELECT ID FROM tblResource WHERE UserID = %s');

// task commitment data collector and gantt data
define('SQL_GET_DAYID_EPOCH',
            'SELECT ID AS DayID, Epoch, Day, Month
             FROM tblDay
             WHERE Epoch >= \'%1$s\' AND Epoch <= \'%2$s\'
             ORDER BY ID');

define('GET_HOURS_AVAILABLE_ALL_COMMITMENT',
            'SELECT tblResourceDay.DayID, tblResourceDay.ResourceID,
             tblResourceDay.HoursAvailable, tblResourceDay.HoursCommittedCache
             FROM tblResourceDay
             WHERE tblResourceDay.ResourceID %1$s
             AND %2$s
             ORDER BY ResourceID, DayID');

define('SQL_GET_HOURS_COMMITTED_ON_TASK',
            'SELECT tblTaskResourceDay.DayID, tblTaskResourceDay.ResourceID,
            tblTaskResourceDay.HoursCommitted, tblTaskResourceDay.HoursCompleted
            FROM tblTaskResourceDay
            WHERE ResourceID %1$s
            AND TaskID =  \'%2$s\'
            AND %3$s
            ORDER BY ResourceID, DayID');

define('SQL_GET_PROJECT_COLOUR_FOR_TASK',
            'SELECT tblProjects.Colour
             FROM tblTasks
             LEFT JOIN tblProjects ON tblTasks.ProjectID = tblProjects.ID
             WHERE tblTasks.ID = \'%s\'');

define('SQL_GET_HOURSCOMPLETED_ALL_TIME','SELECT SUM(HoursCompleted) FROM tblTaskResourceDay WHERE TaskID = \'%1$s\'');

// task save
define('SQL_GET_COMPLETED_HOURS_FOR_DAY',
            'SELECT DayID, HoursCompleted
             FROM tblTaskResourceDay
             WHERE TaskID = \'%1$s\'
             AND ResourceID = \'%2$s\'
             AND DayID = \'%3$s\'');

define('SQL_UPDATE_TASK_RESOURCE_DAY_COMMITMENT',
            'UPDATE tblTaskResourceDay SET HoursCommitted = \'%4$s\'
             WHERE TaskID = \'%1$s\'
             AND ResourceID = \'%2$s\'
             AND DayID = \'%3$s\'');

define('SQL_ASSIGN_RESOURCE','INSERT INTO tblTaskResource (TaskID, ResourceID) VALUES(\'%1$s\',\'%2$s\')');

define('SQL_UNASSIGN_TASK_RESOURCE','DELETE FROM tblTaskResource WHERE TaskID = \'%1$s\' AND ResourceID = \'%2$s\'');

define('SQL_INSERT_TASK_RESOURCE_DAY_COMMITMENT',
            'INSERT INTO tblTaskResourceDay (TaskID, ResourceID, DayID, HoursCommitted, HoursCompleted)
             VALUES (\'%1$s\', \'%2$s\', \'%3$s\', \'%4$s\', \'%5$s\')');

// used by gantt data/ save and TaskSave()

define('SQL_TASK_DISTINCT_SEQUENCES','select count(distinct Sequence) from tblTasks where ProjectID = %s');

define('SQL_TASK_SEQUENCES','select ID, Sequence from tblTasks where ProjectID = %s order by Sequence');

define('UPDATE_TASK_INDENT_LEVEL','update tblTasks set Indent = %1$s where ID = %2$s and ProjectID = %3$s');

define('SQL_GET_MIN_MAX_TASK_DATES',
       'SELECT
                MIN(t.StartDate) AS StartDate,
                MAX(t.EndDate) AS EndDate
        FROM tblTasks t
        WHERE %1$s');

define('SQL_GET_HOURS_COMMITTED_ON_TASK_FOR_TOTAL',
            'SELECT tblTaskResourceDay.DayID, tblTaskResourceDay.HoursCommitted, tblTaskResourceDay.HoursCompleted, tblTaskResourceDay.ResourceID
            FROM tblTaskResourceDay
            WHERE TaskID = \'%1$s\'
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

define('SQL_GET_TASKS_START_END_DATE',
        'SELECT ID, StartDate, EndDate
         FROM tblTasks
         WHERE ID IN (%1$s)');

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

define('SQL_UPDATE_TASK_RESOURCE_DAY_COMMITTED',
            'UPDATE tblTaskResourceDay SET HoursCommitted = 0
             WHERE TaskID = %1$s
             AND (DayID < %2$s OR DayID > %3$s)');

// task view resource user list

define('SQL_RESOURCE_USERS',
       'SELECT tblResource.ID, tblResource.UserID, tblUsers.FirstName, tblUsers.LastName
             FROM tblResource
             LEFT JOIN tblUsers ON tblResource.UserID = tblUsers.ID
             WHERE Active = \'1\'
             ORDER BY tblUsers.LastName, tblUsers.FirstName');

define('SQL_CHECK_IF_ASSIGNED_TO_TASK',
            'SELECT ResourceID
            FROM tblTaskResource
            WHERE ResourceID = %1$s
            AND TaskID = %2$s');

define('SQL_CHECK_IF_ASSIGNED_TO_TASK_ON_DAY',
            'SELECT TaskID
            FROM tblTaskResourceDay
            WHERE TaskID = \'%1$s\'
            AND ResourceID = \'%2$s\'
            AND DayID = \'%3$s\'');

// TaskCommitmentSave
define('SQL_UPDATE_RESOURCE_DAY_COMMITMENT_CACHE',
                'UPDATE tblResourceDay SET HoursCommittedCache = HoursCommittedCache + \'%3$s\'
                 WHERE ResourceID = \'%1$s\'
                 AND DayID = \'%2$s\'');

define('SQL_GET_RESOURCE_EMAIL',
            'SELECT tblUsers.ID, tblUsers.FirstName, tblUsers.LastName, tblUsers.EmailAddress
            FROM tblResource
            LEFT JOIN tblUsers ON tblResource.UserID = tblUsers.ID
            WHERE tblResource.ID = %s AND tblUsers.EmailNotify = 1');

// related projects
define('SQL_RELATED_PROJECTS',
       'SELECT tblRelatedProjects.RelatedProjectID, tblProjects.Name
        FROM tblProjects
        LEFT JOIN tblRelatedProjects ON tblRelatedProjects.RelatedProjectID = tblProjects.ID
        WHERE tblRelatedProjects.ProjectID = %d
        ORDER BY tblProjects.Name');

define('SQL_PROJECTS_LIST_ALL_FOR_RELATED',
       'SELECT p.ID, p.Name
        FROM tblProjects AS p
        WHERE p.Active = 1
        AND p.ID NOT IN (%s)
        ORDER BY p.Name');

define('SQL_PROJECTS_LIST_ALL_FOR_RELATED_IN',
       'SELECT p.ID, p.Name
        FROM tblProjects AS p
        WHERE p.Active = 1
        AND p.ID IN (%s)
        AND p.ID NOT IN (%s)
        ORDER BY p.Name');

define('SQL_INSERT_RELATED_PROJECT',
       'INSERT INTO tblRelatedProjects (ProjectID, RelatedProjectID) VALUES (%1$d, %2$d)');

define('SQL_DELETE_RELATED_PROJECT',
       'DELETE FROM tblRelatedProjects
        WHERE ProjectID = %1$d AND RelatedProjectID = %2$d
        LIMIT 1');

define('SQL_DELETE_RELATED_PROJECTS',
       'DELETE FROM tblRelatedProjects
        WHERE ProjectID = %s');

//end new code by orca

define('SQL_GET_FOLDERS',
       'SELECT COUNT(f.ID) AS count, fo.ID, fo.Folder, p.Name
        FROM tblFiles f
        LEFT JOIN tblFolders fo ON f.Folder = fo.ID
        LEFT JOIN tblProjects p ON f.ProjectID = p.ID
        WHERE f.ProjectID = %1$s AND f.Folder > 0
        GROUP BY f.Folder
        ORDER BY %2$s');

define('SQL_GET_FILES_IN_FOLDER',
       'SELECT f.ID, f.FileName, fo.Folder, f.ProjectID, f.TaskID, f.Description,
          f.Version, f.Size, f.Date, f.Type, f.CheckedOut, f.CheckedOutUserID, f.RealName, f.Linked,
          u.FirstName, u.LastName, p.Name AS Project, t.Name AS TaskName
        FROM tblFiles AS f
        LEFT JOIN tblUsers AS u ON u.ID = f.Owner
        LEFT JOIN tblFolders fo ON fo.ID = f.Folder
        LEFT JOIN tblProjects p ON f.ProjectID = p.ID
        LEFT JOIN tblTasks t on f.TaskID = t.ID
        WHERE f.Folder = %1$s AND f.ProjectID = %2$s
        ORDER BY %3$s');

define('SQL_GET_FOLDER_NAME',
        'SELECT f.Folder FROM tblFolders f WHERE f.ID = %s');

define('SQL_GET_FOLDER_DETAILS',
        'SELECT f.Folder, f.ProjectID, p.Name
         FROM tblFolders f
         LEFT JOIN tblProjects p ON f.ProjectID = p.ID
         WHERE f.ID = %s');

define('SQL_COUNT_FILES_IN_FOLDER',
        'SELECT COUNT(f.ID) AS count
         FROM tblFiles f
         WHERE Folder = %1$s AND f.ProjectID = %2$s');

define('SQL_GET_CLIENT_NAME',
        'SELECT c.Name FROM tblClients c, tblProjects p
            WHERE c.ID = p.ClientID AND p.ID = %s');

define( 'SQL_SELECT_TASKS_FOR_USER',
        "SELECT t.ID FROM tblTasks t "
        ."LEFT JOIN tblTaskResource r ON r.TaskID = t.ID "
        ."WHERE t.ProjectID = '%s' AND r.ResourceID = "
        ."(SELECT distinct ID from tblResource where UserID = '%s')" );

define( 'SQL_COUNT_FILES_IN_FOLDER_FOR_TASKS',
        'SELECT COUNT(f.ID) AS count
         FROM tblFiles f
         WHERE Folder = %1$s AND f.ProjectID = %2$s AND f.TaskID IN (%3$s)');

define( 'SQL_SELECT_PROJECT_HOURS_WORKED',
    "SELECT c.UserID, "
    ."(SELECT u.ChargeRate FROM tblUsers u WHERE u.ID = c.UserID) AS Rate, "
    ."SUM(COALESCE(c.HoursWorked, 0)) AS HoursWorked "
    ."FROM tblTasks_Comments c "
    ."WHERE c.TaskID IN "
    ."(SELECT t.ID FROM tblTasks t WHERE t.ProjectID = '%s') "
    ."GROUP BY c.UserID" );

define( 'SQL_SELECT_TASK_HOURS_WORKED',
    "SELECT c.UserID, "
    ."(SELECT u.ChargeRate FROM tblUsers u WHERE u.ID = c.UserID) AS Rate, "
    ."SUM(COALESCE(c.HoursWorked, 0)) AS HoursWorked "
    ."FROM tblTasks_Comments c "
    ."WHERE c.TaskID = '%s' "
    ."GROUP BY c.UserID" );

define( 'SQL_SELECT_TASK_SEQUENCES',
        "SELECT ID, Sequence FROM tblTasks WHERE ProjectID = '%s' ORDER BY Sequence ASC" );

define( 'SQL_UPDATE_TASK_SEQUENCE',
        "UPDATE tblTasks SET Sequence = '%s' WHERE ID = '%s'" );

define( 'SQL_UPDATE_TASK_SEQUENCE_INDENT',
        "UPDATE tblTasks SET Sequence = %d, Indent = %d WHERE ID = %d" );

define('SQL_CLIENT_IMPORT',
       'INSERT INTO tblClients (ID, Name, Manager, Phone1, Phone2, Phone3, FAX, Address1, Address2, City, State, Country, Postcode, URL, Description, Archived, ContactEmail,Colour)
       VALUES (\'%1$s\', \'%2$s\', \'%3$s\', \'%4$s\', \'%5$s\', \'%6$s\', \'%7$s\', \'%8$s\', \'%9$s\', \'%10$s\', \'%11$s\', \'%12$s\', \'%13$s\', \'%14$s\', \'%15$s\', \'%16$s\',\'%17$s\', \'%18$s\')');

define('SQL_CONTACT_IMPORT',
       'INSERT INTO tblContacts (ID, ClientID, KeyContact, FirstName, LastName, Notes, Title, EmailAddress1, EmailAddress2, Phone1, Phone2, Phone3)
       VALUES (\'%1$s\', \'%2$s\', \'%3$s\', \'%4$s\', \'%5$s\', \'%6$s\', \'%7$s\', \'%8$s\', \'%9$s\', \'%10$s\', \'%11$s\', \'%12$s\')');

define('SQL_PROJECT_IMPORT',
       'INSERT INTO tblProjects (ID, ClientID, ProjectID, Name, Owner, StartDate, EndDate, ActualEndDate, Status, Priority, Colour, Description, TargetBudget, ActualBudget, Active)
        VALUES (\'%1$s\', \'%2$s\', \'%3$s\', \'%4$s\', \'%5$s\', \'%6$s\', \'%7$s\', \'%8$s\', \'%9$s\', \'%10$s\', \'%11$s\', \'%12$s\', \'%13$s\', \'%14$s\', \'%15$s\')');

define('SQL_TASK_IMPORT',
       'INSERT INTO tblTasks (ID, Name, ProjectID, Owner, StartDate, Duration, HoursWorked, EndDate, Status, Priority, PercentComplete, Description, RelatedURL, Sequence, TargetBudget, ActualBudget)
        VALUES (\'%1$s\', \'%2$s\', \'%3$s\', \'%4$s\', \'%5$s\', \'0\', \'%6$s\', \'0\', \'%7$s\', \'%8$s\', \'%9$s\', \'%10$s\', \'%11$s\', \'%12$s\', \'%13$s\', \'%14$s\')');

define('SQL_GET_CLIENT_FOLDERS',
       'SELECT fo.*, COUNT(fo.ID) AS Count
        FROM tblFiles f
        LEFT JOIN tblProjects p ON p.ID = f.ProjectID
        LEFT JOIN tblFolders fo ON fo.ID = f.Folder
        WHERE f.Folder > 0 AND p.ClientID = %d
        %s
        GROUP BY fo.ID
        ORDER BY %s');
        
define('SQL_UPDATE_TASK_TIMESTAMP',
        'UPDATE `tblTasks` SET `LatestActivity`= CURRENT_TIMESTAMP WHERE `ID` = \'%d\'');

define('SQL_GET_USERS_WITH_CLIENT_ACCESS', 
			 'SELECT UserID, AccessID FROM sysUserPermissions WHERE ObjectID = \'Clients\' and ItemID = \'%1$s\'');

define('SQL_GET_GROUPS_WITH_CLIENT_ACCESS', 
			 'SELECT GroupID, AccessID FROM sysGroupPermissions WHERE ObjectID = \'Clients\' and ItemID = \'%1$s\'');
