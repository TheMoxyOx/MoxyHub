<?php
// $Id$
/* Keep all the global defines in one place */


date_default_timezone_set("UTC");
define('SYS_SEP',              ' :: ');                    // separates HTML/Title strings
define('DB_DRIVER',            'mysql');                   // don't change. other db's will be supported later

define('NewLine',               substr(PHP_OS, 0, 3) == "WIN"  ? "\r\n" : "\n");
define('CU_SYSTEM_PATH',        str_replace('system.php', '', __FILE__));
define('CU_CLASS_PATH',         CU_SYSTEM_PATH . 'classes/');
define('CU_CLASS_LIB_PATH',			CU_CLASS_PATH  . 'lib/');
define('CU_LANGUAGE_PATH',      CU_SYSTEM_PATH . 'language/');
define('CU_TEMPLATE_PATH',      CU_SYSTEM_PATH . 'templates/');
define('CU_MODULE_PATH',        CU_SYSTEM_PATH . 'modules/%s/');
define('CU_MODULE_TEMPLATES',   CU_MODULE_PATH .'templates/');
define('CU_SCRIPT_EXT',         '.php');
define('CU_LANGUAGE_EXT',       '.lang');
define('CU_TEMPLATE_EXT',       '.html');
define('CU_DEFAULT_LANGUAGE',   'en');
define('CU_ERROR_MODULE',       'error');
define('CU_AUTH_MODULE',        'authorisation');
define('CU_DEFAULT_MODULE',     'profile');
define('CU_ACCESS_ALL',         -1);
define('CU_ACCESS_DENY',        0);
define('CU_ACCESS_READ',        1);
define('CU_ACCESS_WRITE',       2);
define('CU_ACCESS_GLOBALKEY',   '-1');
define('CU_ACCESS_GLOBALNAME',  'all');
define('CU_FORMAT_DATE',        'd M y');                                   // see docs on date() command
define('CU_FORMAT_TIME',        'h:i:s A');                                 // see docs on date() command
define('CU_MODULE_ENGINE',      'index'.CU_SCRIPT_EXT);
define('CU_MODULE_SQL',         'sql_%s'.CU_SCRIPT_EXT);
define('CU_MODULE_ERRORS',      'lang_err_%s'.CU_SCRIPT_EXT);
define('CU_MODULE_LANGUAGE',    'lang_%s'.CU_SCRIPT_EXT);
define('CU_PRODUCT_NAME',       'Copper');
define('CU_COPYRIGHT',					'<a href="http://www.copperproject.com/" target="_blank">&copy; Element Software 2002-' . date('Y') . '</a>');
define('CU_PRODUCT_VERSION',    '4.6.5');
define('CU_SESSION_TIMEOUT',    60 * 168); // 1 week timeout, in minutes.
define('CU_CHG_ADMIN_NAME',     0);
define('CU_CHG_ADMIN_PASS',     0);
define('CU_DEFAULT_PRIORITY',   2);  /*  Current options are 1-low 2-Normal 3-High */
define('CU_WEEK_START',         'Monday');
define('CU_ERR_DB_1000',        'There was a fatal error connecting to the database.<br/>Error code: %s<br/>Error message: %s');

// debuggging constants
$i = 1;
define('DEBUG_SQL_ERROR', 			$i *= 2);
define('DEBUG_SQL_ALL', 				$i *= 2);
