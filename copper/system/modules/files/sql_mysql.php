<?php
// $Id$
define('SQL_INSERT_FOLDER',
       'INSERT INTO tblFolders (ProjectID, Folder, ParentID) VALUES (%d, \'%s\', 0)');

define('SQL_GET_FOLDER_PARENT_ID',
       'SELECT ParentID FROM tblFolders WHERE ID = %d');

define('SQL_GET_FILES_IN_FOLDERS',
       'SELECT ID FROM tblFiles WHERE Folder IN (%s)');

define('SQL_DELETE_FOLDER',
       'DELETE FROM tblFolders WHERE ID = %d');

define('SQL_GET_FOLDER_SUBFOLDERS',
       'SELECT ID FROM tblFolders WHERE ParentID = %d');

define('SQL_GET_FILE_FOLDER',
       'SELECT Folder FROM tblFiles WHERE ID = %d');

define('SQL_MOVE_FILE',
       'UPDATE tblFiles SET Folder = %d WHERE ID = %d');

define('SQL_MOVE_FOLDER',
       'UPDATE tblFolders SET ParentID = %d WHERE ID = %d');

define('SQL_GET_PROJECTS_WITH_FILES',
       'SELECT p.ClientID, c.Name AS ClientName, f.ProjectID, p.Name AS ProjectName, COUNT(f.ID) AS Count 
        FROM tblFiles f 
        LEFT JOIN tblProjects p ON p.ID = f.ProjectID 
        LEFT JOIN tblClients c ON c.ID = p.ClientID 
        WHERE c.Name IS NOT NULL AND p.Status != 6 /* 6 is archived, dont show them k? */
        AND (%s)
        GROUP BY f.ProjectID 
        ORDER BY %s');

define('SQL_GET_PROJECTS_ALL',
       'SELECT p.ID, p.Name, c.Name AS ClientName 
        FROM tblProjects p
        LEFT JOIN tblClients c ON c.ID = p.ClientID
        WHERE p.Active = 1 
        ORDER BY ClientName, Name ASC');

define('SQL_GET_PROJECTS',
       'SELECT p.ID, p.Name, c.Name AS ClientName 
        FROM tblProjects p
        LEFT JOIN tblClients c ON c.ID = p.ClientID
        WHERE p.Active = 1 AND p.ID IN (%s)
        ORDER BY ClientName, Name ASC');

define('SQL_GET_PROJECT_TASKS',
       'SELECT ID, Name FROM tblTasks WHERE ProjectID = %d ORDER BY Name ASC');

define('SQL_GET_PROJECT_TASKS_RESTRICTED',
       'SELECT ID, Name FROM tblTasks WHERE ProjectID = %d AND ID IN (%s) ORDER BY Name ASC');

define('SQL_GET_PROJECT_IDS_FOR_TASKS',
       'SELECT DISTINCT ProjectID FROM tblTasks WHERE ID IN (%s)');

define('SQL_GET_CLIENT_FILES',
       'SELECT DISTINCT p.ClientID, f.* 
        FROM tblFiles f
        LEFT JOIN tblProjects p ON p.ID = f.ProjectID
        WHERE p.ClientID = %d AND f.Folder = %d
        %s 
        ORDER BY %s');

define('SQL_GET_CLIENT_FOLDERS',
       'SELECT fo.*, COUNT(fo.ID) AS Count
        FROM tblFiles f
        LEFT JOIN tblProjects p ON p.ID = f.ProjectID
        LEFT JOIN tblFolders fo ON fo.ID = f.Folder
        WHERE f.Folder > 0 AND p.ClientID = %d 
        %s
        GROUP BY fo.ID
        ORDER BY %s');

define('SQL_GET_PROJECT_FILES',
       'SELECT DISTINCT p.ClientID, f.*
        FROM tblFiles f
        LEFT JOIN tblProjects p ON p.ID = f.ProjectID
        WHERE f.ProjectID = %d AND f.Folder = %d
        %s
        ORDER BY %s');

define('SQL_GET_PROJECT_FOLDERS',
       'SELECT * FROM tblFolders fo WHERE fo.ProjectID = %d AND fo.ParentID = %d ORDER BY %s');



define('SQL_COUNT_FILES_IN_FOLDER',
        'SELECT COUNT(ID) FROM tblFiles WHERE Folder = %d');

define('SQL_COUNT_FILES_IN_FOLDER_PUBLIC',
        'SELECT count(ID) FROM tblFiles WHERE Folder = %s AND ProjectID = 0');

define('SQL_COUNT_PUBLIC_FILES',
        'SELECT count(ID) FROM tblFiles WHERE ProjectID = 0');

define('SQL_COUNT_FILES_IN_FOLDER_PROJECT',
        'SELECT count(ID) FROM tblFiles WHERE Folder = %s AND ProjectID <> 0');

define('SQL_COUNT_FILES_IN_FOLDER_FOR_PROJECTS',
        'SELECT count(ID) FROM tblFiles WHERE Folder = %1$s AND ProjectID IN (%2$s)');

define('SQL_COUNT_FILES_IN_FOLDER_FOR_TASKS',
        'SELECT count(ID) FROM tblFiles WHERE Folder = %1$s AND TaskID IN (%2$s)');

define('SQL_COUNT_FILES_IN_FOLDER_FOR_PROJECTS_OR_TASKS',
        'SELECT count(ID) FROM tblFiles WHERE Folder = %1$s AND (ProjectID IN (%2$s) OR TaskID IN (%3$s))');

define('SQL_GET_FOLDER_NAME','
        SELECT Folder FROM tblFolders WHERE ID = %s');

define('SQL_GET_FOLDER_DETAILS',
        'SELECT f.Folder, f.ProjectID, p.Name 
         FROM tblFolders f 
         LEFT JOIN tblProjects p ON f.ProjectID = p.ID
         WHERE f.ID = %s');

define('SQL_GET_FOLDERS_PUBLIC',
       'SELECT COUNT(f.ID) AS Count, fo.ID, fo.Folder
        FROM tblFiles f 
        LEFT JOIN tblFolders fo ON f.Folder = fo.ID 
        WHERE f.ProjectID = 0 AND f.Folder <> 0 
        GROUP BY f.Folder
        ORDER BY %s');

define('SQL_GET_FOLDERS_PROJECT',
       'SELECT COUNT(f.ID) AS count, fo.ID, fo.Folder, fo.ProjectID, p.Name
        FROM tblFiles f 
        LEFT JOIN tblFolders fo ON f.Folder = fo.ID 
        LEFT JOIN tblProjects p ON f.ProjectID = p.ID 
        LEFT JOIN tblTasks t ON f.TaskID = t.ID 
        WHERE f.ProjectID > 0 AND f.Folder > 0 
        GROUP BY f.Folder
        ORDER BY %s');

define('SQL_GET_FOLDERS_FOR_PROJECTS',
       'SELECT COUNT(f.ID) AS count, fo.ID, fo.Folder, fo.ProjectID, p.Name 
        FROM tblFiles f 
        LEFT JOIN tblFolders fo ON f.Folder = fo.ID 
        LEFT JOIN tblProjects p ON f.ProjectID = p.ID 
        WHERE f.ProjectID IN (%1$s) AND f.Folder > 0 
        GROUP BY f.Folder
        ORDER BY %2$s');

define('SQL_GET_FOLDERS_FOR_PROJECTS_OR_TASKS',
       'SELECT COUNT(f.ID) AS count, fo.ID, fo.Folder, fo.ProjectID, p.Name 
        FROM tblFiles f 
        LEFT JOIN tblFolders fo ON f.Folder = fo.ID 
        LEFT JOIN tblProjects p ON f.ProjectID = p.ID 
        WHERE (f.ProjectID IN (%1$s) OR f.TaskID IN (%2$s)) AND f.Folder > 0 
        GROUP BY f.Folder
        ORDER BY %3$s');

define('SQL_CREATE_FOLDER','
        INSERT INTO tblFolders (ProjectID, Folder)
        VALUES (%s, \'%s\')');

define('SQL_LAST_INSERT', 'SELECT LAST_INSERT_ID()');

define('SQL_GET_FILES_IN_FOLDER_PUBLIC',
       'SELECT f.ID, f.FileName, fo.Folder, f.ProjectID, f.TaskID, f.Description, f.Version, f.Size, f.Date, 
          f.Type, f.CheckedOut, f.CheckedOutUserID, f.RealName, f.Linked, u.FirstName, u.LastName
        FROM tblFiles AS f
        LEFT JOIN tblUsers AS u ON u.ID = f.Owner
        LEFT JOIN tblFolders fo ON fo.ID = f.Folder
        WHERE f.Folder = %1$s AND f.ProjectID = 0
        ORDER BY %2$s');

define('SQL_GET_FILES_IN_FOLDER_PROJECT',
       'SELECT f.ID, f.FileName, fo.Folder, f.ProjectID, f.TaskID, f.Description, 
          f.Version, f.Size, f.Date, f.Type, f.CheckedOut, f.CheckedOutUserID, f.RealName, f.Linked,
          u.FirstName, u.LastName, p.Name AS Project, t.Name AS TaskName
        FROM tblFiles AS f
        LEFT JOIN tblUsers AS u ON u.ID = f.Owner
        LEFT JOIN tblFolders fo ON fo.ID = f.Folder
        LEFT JOIN tblProjects p ON f.ProjectID = p.ID
        LEFT JOIN tblTasks t on f.TaskID = t.ID
        WHERE f.Folder = %1$s AND f.ProjectID <> 0
        ORDER BY %2$s');

define('SQL_GET_FILES_IN_FOLDER_FOR_PROJECTS',
       'SELECT f.ID AS FileID, f.FileName, fo.Folder, f.ProjectID, f.TaskID, f.Description, 
          f.Version, f.Size, f.Date, f.Type, f.CheckedOut, f.CheckedOutUserID, f.RealName, f.Linked,
          CONCAT(u.FirstName, \' \', u.LastName) AS Name, p.Name AS Project, t.Name AS Task
        FROM tblFiles AS f
        LEFT JOIN tblUsers AS u ON u.ID = f.Owner
        LEFT JOIN tblFolders fo ON fo.ID = f.Folder
        LEFT JOIN tblProjects p ON f.ProjectID = p.ID
        LEFT JOIN tblTasks t on f.TaskID = t.ID
        WHERE f.Folder = %1$s AND f.ProjectID IN (%2$s)
        ORDER BY %3$s');

define('SQL_GET_FILES_IN_FOLDER_FOR_PROJECTS_OR_TASKS',
       'SELECT f.ID AS FileID, f.FileName, fo.Folder, f.ProjectID, f.TaskID, f.Description, 
          f.Version, f.Size, f.Date, f.Type, f.CheckedOut, f.CheckedOutUserID, f.RealName, f.Linked,
          CONCAT(u.FirstName, \' \', u.LastName) AS Name, p.Name AS Project, t.Name AS Task
        FROM tblFiles AS f
        LEFT JOIN tblUsers AS u ON u.ID = f.Owner
        LEFT JOIN tblFolders fo ON fo.ID = f.Folder
        LEFT JOIN tblProjects p ON f.ProjectID = p.ID
        LEFT JOIN tblTasks t on f.TaskID = t.ID
        WHERE f.Folder = %1$s AND (f.ProjectID IN (%2$s) OR f.TaskID IN (%3$s))
        ORDER BY %4$s');

define('SQL_LAST_CHECKED_OUT',
        'SELECT MAX(f.Time) AS Time, CONCAT(u.FirstName,\' \',u.LastName) AS Name
        FROM tblFile_Log f
        LEFT JOIN tblUsers u ON u.ID = UserID
        WHERE f.FileID = \'%1$s\' AND f.Activity=\'Checked Out\'
        GROUP BY f.UserID');

define('SQL_FILES_GET_ACTIVITY',
       'SELECT f.Activity, f.Time, f.Time AS Date, f.Version, CONCAT(u.FirstName, \' \', u.LastName) AS User, f.FileName, f.Type, f.Size, f.RealName
        FROM tblFile_Log AS f
        LEFT JOIN tblUsers AS u ON u.ID = f.UserID
        WHERE f.FileID = \'%1$s\'
        ORDER BY f.Time DESC');

define('SQL_PROJECT_FILES_LIST',
            'SELECT DISTINCT f.ID as FileID, fo.Folder, f.FileName, f.Description, f.Version, f.Size, f.Date, f.CheckedOut, f.CheckedOutUserID, f.RealName, f.Linked,
            f.Type, p.ID AS ProjectID, p.Name AS ProjectName,
            t.ID as TaskID, t.Name as TaskName,
            u.FirstName, u.LastName
            FROM tblFiles AS f
            LEFT JOIN tblTasks AS t ON t.ID = f.TaskID
            LEFT JOIN tblProjects AS p ON p.ID = f.ProjectID
            LEFT JOIN tblUsers AS u ON u.ID = f.Owner
            LEFT JOIN tblFolders fo ON fo.ID = f.Folder
            WHERE p.ID IN (%1$s)
            ORDER BY %3$s %4$s');
        /* First WHERE CLAUSE:  They have been given direct access to project. */
        /* Second WHERE CLAUSE:  They are the project owner. */
        /* Third WHERE CLAUSE:  They are the task owner. */
        /* Fourth WHERE CLAUSE:  They are a resource assigned to a task. */

define('SQL_FILE_CREATE',
       'INSERT INTO tblFiles (FileName, Description, Type, Owner, Date, Size, Version, RealName, Folder, Linked, ProjectID, TaskID)
       VALUES (\'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', %d, %d)');

define('SQL_FILE_CHECKOUT',
        'UPDATE tblFiles SET
         CheckedOut = 1,
         CheckedOutUserID = \'%2$s\'
         WHERE ID = \'%1$s\'');

define('SQL_FILE_LOG',
        'INSERT INTO tblFile_Log (FileID, UserID, Time, Activity, Version, FileName, Type, Size, RealName)
         VALUES (\'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%d\', \'%s\')');

define('SQL_FILE_UPDATE',
       'UPDATE tblFiles SET
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
        Linked = \'%s\',
        ProjectID = %d,
        TaskID = %d
        WHERE ID = \'%s\'');

define('SQL_FILE_DELETE',
       'DELETE FROM tblFiles WHERE ID = %d');

define('SQL_FILE_DELETE_HISTORY',
       'DELETE FROM tblFile_Log WHERE FileID = %d');

define('SQL_FILE_UPDATE_NOFILE',
       'UPDATE tblFiles SET
        Description = \'%s\',
        Version = \'%s\',
        CheckedOut = 0,
        Folder = \'%s\',
        ProjectID = %d,
        TaskID = %d
        WHERE ID = \'%s\'');

define('SQL_GET_FILE_NAME',
       'SELECT FileName FROM tblFiles WHERE ID = \'%s\'');

define('SQL_GET_FILE_DETAILS',
       'SELECT * FROM tblFiles WHERE ID = \'%s\'');

define('SQL_FILES_GET_DETAILS_ALL',
       'SELECT f.*, u.FirstName, u.LastName, p.Colour, t.Name AS TaskName, p.Name AS ProjectName, p.ClientID
        FROM tblFiles f 
        LEFT JOIN tblUsers u ON u.ID = f.Owner 
        LEFT JOIN tblProjects p ON p.ID = f.ProjectID 
        LEFT JOIN tblTasks t ON t.ID = f.TaskID
        WHERE f.ID = \'%s\'');

define( 'SQL_SELECT_TASKS_FOR_USER',
        "SELECT t.ID FROM tblTasks t "
        ."LEFT JOIN tblTaskResource r ON r.TaskID = t.ID "
        ."WHERE r.ResourceID = "
        ."(SELECT distinct ID from tblResource where UserID = '%s')" );

 
