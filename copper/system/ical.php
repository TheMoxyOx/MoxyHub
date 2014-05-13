<?php
// $Id$

/* lets load up the system defines */
require_once('../system/system.php');

/* now init the bootstrapper */
include(CU_CLASS_PATH . 'bootstrap.php');
Bootstrap::go();

require('modules/projects/sql_mysql.php');

$ical = new ical();
$ical->subscribe(iCal::ICAL_PROJECT);
