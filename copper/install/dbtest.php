<?php
$link = @mysql_connect($_POST['hostname'], $_POST['username'], $_POST['password']);
if (!$link)
    die('Error: Could not connect to MySQL server.');
$db = @mysql_select_db($_POST['database']);
if (!$db)
    die('Error: Could not connect to database.');
@mysql_close($link);
die('Ok');
 
