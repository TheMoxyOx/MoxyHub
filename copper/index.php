<?php
// $Id$

// lets load up the system defines 
// it means we can set debugging level in config_local.
require_once('system/system.php');

/* now init the bootstrapper */
include(CU_CLASS_PATH . 'bootstrap.php');
Bootstrap::go();

/* now either config_local.php or config.php has been read and loaded up
** all our files, so now we can start the fun */
Bootstrap::doit();