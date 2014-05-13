<?php
/** 
** Config file. If no config, then set some defaults. for the installer
**  @version $Id$
**/

if (basename(__FILE__) != 'config_local.php' && file_exists('config_local.php')) {
    /* read the config, and then lets get going! */
    require('config_local.php');
    require_once('system/system.php');
} 
else {
    header('Location: install/index.php');
}
