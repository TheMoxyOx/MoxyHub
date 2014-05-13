<?php
// $Id$
define('SQL_UPDATE', 
       'UPDATE tblUsers SET
        Password = %1$s,
        FirstName = \'%2$s\',
        LastName =  \'%3$s\',
        Phone1 = \'%4$s\',
        Phone2 = \'%5$s\',
        Phone3 = \'%6$s\',
        EmailAddress = \'%7$s\',
        Module = \'%8$s\',
        EmailNotify = \'%9$s\'
        WHERE ID = \'%10$s\'');

define('SQL_PASS_FIELD', 'Password');
 
