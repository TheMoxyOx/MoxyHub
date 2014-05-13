<?php
// $Id$

define('SQL_LAST_INSERT', 'SELECT LAST_INSERT_ID()');

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

//change_log 1.
define('SQL_MATCH_CLIENT','
        SELECT ID FROM tblClients WHERE Name LIKE "%%%s%%"');

define('SQL_SEARCH_CONTACTS',
       'SELECT o. * , c.Name AS ClientName
        FROM tblContacts o
        LEFT JOIN tblClients AS c ON c.ID = o.ClientID
        ORDER BY o.LastName ASC');

define('SQL_SEARCH_USERS',
       'SELECT u.*
        FROM tblUsers u
        WHERE u.Active = 1
        ORDER BY u.LastName ASC');

define('SQL_SEARCH_CONTACTS_IN',
        'SELECT o. *, c.Name AS ClientName
        FROM tblContacts o
        LEFT JOIN tblClients AS c ON c.ID = o.ClientID
        WHERE (o.ClientID IN ( %s ) OR o.ClientID = 0)
        ORDER BY o.LastName ASC');

define('SQL_SEARCH_CONTACTS_FILTER',
       'SELECT o. * , c.Name AS ClientName
        FROM tblContacts o
        LEFT JOIN tblClients AS c ON c.ID = o.ClientID
        WHERE o.LastName LIKE \'%s%%\'
        ORDER BY o.LastName ASC');

define('SQL_SEARCH_USERS_FILTER',
       'SELECT u.*
        FROM tblUsers u
        WHERE u.Active = 1 AND u.LastName LIKE \'%s%%\'
        ORDER BY u.LastName ASC');

define('SQL_SEARCH_CONTACTS_IN_FILTER',
       'SELECT o. * , c.Name AS ClientName
        FROM tblContacts o
        LEFT JOIN tblClients AS c ON c.ID = o.ClientID
        WHERE (
            o.ClientID IN ( %s  )
            OR o.ClientID = 0
        )
        AND o.LastName LIKE \'%s%%\'
        ORDER BY o.LastName ASC');

define('SQL_GET_CONTACT_IN',
       'SELECT o. * , c.Name AS ClientName
        FROM tblContacts o
        LEFT JOIN tblClients AS c ON c.ID = o.ClientID
        WHERE (
            o.ClientID IN ( %s  )
            OR o.ClientID = 0
        )
        AND o.ID = \'%s\'');

define('SQL_GET_CONTACT',
       'SELECT o. * , c.Name AS ClientName
        FROM tblContacts o
        LEFT JOIN tblClients AS c ON c.ID = o.ClientID
        WHERE o.ID = \'%s\'');


define('SQL_UPDATES_LIST',
        'SELECT tc.ID, tc.TaskID, tc.Body, tc.Date, tc.HoursWorked, tc.OutOfScope, tc.Issue, c.ID AS ClientID, c.Name AS ClientName, p.ID AS ProjectID, p.Name AS ProjectName, t.Name AS TaskName, CONCAT(u.FirstName,\' \',u.LastName) AS UserName
        FROM tblTasks_Comments tc
        LEFT JOIN tblTasks t ON t.ID = tc.TaskID
        LEFT JOIN tblProjects p ON p.ID = t.ProjectID
        LEFT JOIN tblClients c ON c.ID = p.ClientID
        LEFT JOIN tblUsers u ON u.ID = tc.UserID
        WHERE tc.Contact = %1$s
        ORDER BY %2$s %3$s');

define('SQL_UPDATES_LIST_IN',
        'SELECT tc.ID, tc.TaskID, tc.Body, tc.Date, tc.HoursWorked, tc.OutOfScope, tc.Issue, c.ID AS ClientID, c.Name AS ClientName, p.ID AS ProjectID, p.Name AS ProjectName, t.Name AS TaskName, CONCAT(u.FirstName,\' \',u.LastName) AS UserName
        FROM tblTasks_Comments tc
        LEFT JOIN tblTasks t ON t.ID = tc.TaskID
        LEFT JOIN tblProjects p ON p.ID = t.ProjectID
        LEFT JOIN tblClients c ON c.ID = p.ClientID
        LEFT JOIN tblUsers u ON u.ID = tc.UserID
        WHERE tc.Contact = %1$s AND p.ID IN (%4$s)
        ORDER BY %2$s %3$s');


define('SQL_CONTACT_CREATE',   
       'INSERT INTO tblContacts (ClientID, KeyContact, FirstName, LastName, Notes, Title, EmailAddress1, EmailAddress2, Phone1, Phone2, Phone3) 
       VALUES (\'%1$s\', \'%2$s\', \'%3$s\', \'%4$s\', \'%5$s\', \'%6$s\', \'%7$s\', \'%8$s\', \'%9$s\', \'%10$s\', \'%11$s\')');

define('SQL_CONTACT_UPDATE',   
       'UPDATE tblContacts SET ClientID = \'%1$s\', KeyContact = \'%2$s\', FirstName = \'%3$s\', LastName = \'%4$s\', Notes = \'%5$s\', Title = \'%6$s\', EmailAddress1 = \'%7$s\', EmailAddress2 = \'%8$s\', Phone1 = \'%9$s\', Phone2 = \'%10$s\', Phone3 = \'%11$s\' WHERE ID = \'%12$s\'');

define('SQL_GET_CLIENTS_ALL',
       'SELECT ID, Name FROM tblClients ORDER BY Name ASC');

define('SQL_GET_CLIENTS',
       'SELECT ID, Name FROM tblClients WHERE ID IN (%s) ORDER BY Name ASC');

define('SQL_GET_CLIENTS_PLUS',
       'SELECT ID, Name FROM tblClients WHERE ID = \'%2$s\' OR ID IN (%1$s) ORDER BY Name ASC');

define('SQL_DELETE_CONTACT',
       'DELETE FROM tblContacts 
        WHERE ID = %s');

define('SQL_GET_FULLNAME',
       'SELECT FirstName, LastName 
        FROM tblContacts 
        WHERE ID = %s');

define('SQL_GET_CONTACTS_FOR_CSV',
       'SELECT o. * , c.Name AS ClientName, c.Address1, c.Address2, c.City, c.State, c.Country, c.Postcode
        FROM tblContacts o
        LEFT JOIN tblClients AS c ON c.ID = o.ClientID
        ORDER BY o.LastName ASC');
 
