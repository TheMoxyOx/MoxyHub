<?php

include('../system/classes/dbconnection.php');

if (is_readable('../config_local.php')) {
    include '../config_local.php';
}

if (($_SERVER['REQUEST_METHOD'] == 'POST') && isset($_POST['hostname']) && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['database'])) {
    $db = new DBConnection($_POST['hostname'], $_POST['username'], $_POST['password'], $_POST['database']);
} 
elseif (isset($_GET['license_name'])) { // Handle automatic install scripts on copperhub.com
    if (is_dir("/home/virtual/copperhub.com"))
    {
       $NodensDBPrefix = "copperhub";
    } else
    {
       $NodensDBPrefix = "copperhq";
    }
    
    $db = new DBConnection('localhost', $_GET['db_username'], $_GET['db_password'], $NodensDBPrefix.'_com_-_'.$_GET['license_name']);
}
elseif (defined('DB_SERVER')) {
    $db = new DBConnection(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
}
else {
    $db = new DBConnection();
}    
    
//$db->FatalDBErrors = 1;
$GLOBALS['db'] = $db;

if ($db->State() == 1)
{
    $sql = "SELECT Value FROM sysAdminSettings WHERE Setting = 'HourlyRate'";
    $hourlyRate = $db->ExecuteScalar($sql);
    $hourlyRate = (!$hourlyRate) ? '0.00' : $hourlyRate;
    $GLOBALS['costRate'] = $hourlyRate;
    $GLOBALS['chargeRate'] = $hourlyRate;
}
else
{
    $GLOBALS['costRate'] = '0.00';
    $GLOBALS['chargeRate'] = '0.00';
}
 
/* set a default to stop php errors */
$message = '';