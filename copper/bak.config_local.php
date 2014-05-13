<?php
// Created on 2013-08-22
define('LICENSE_NAME',    'Spruce Feinstein');
define('LICENSE_COMPANY', 'Moxy Ox');
define('LICENSE_CODE',    '6C98-20E8-B9AB-9D2E');
define('DB_SERVER',       'moxyox.com'); //server
define('DB_NAME',         'copper_db'); //database
define('DB_USERNAME',     'root'); //username
define('DB_PASSWORD',     'MoxyOx4180!'); //password
define('DB_PREFIX',       ''); //Table prefix
define('DAY_LENGTH',      '8'); //hours in a working day
define('MAX_DAY_LENGTH',  '12'); //max availability for a resource in a day
define('PERM_ORDER',      '0'); //0 = Groups overrides users
define('SYS_LANGUAGE',    'en'); //language
define('SYS_FROMNAME',    'SYS_FROMNAME');  // Mail From Name
define('SYS_FROMADDR',    'SYS_FROMADDR');  // Mail From Address
define('SYS_FILEPATH',    '/copper_files');  // path to files, make sure you have a trailing slash!
define('FILE_VERSIONING_ENABLED', '1');  // Store every version of a file on disk instead of only the latest? 1 for yes, 0 for no.
define('SERVER_NAME_VAR', 'SERVER_NAME');  // Subdomains use HTTP_HOST
define('SCRIPT_NAME_VAR', 'SCRIPT_NAME');  // Subdomains use REQUEST_URI
define('CHARSET', 'UTF-8'); // ISO-8859-1 default or UTF-8 for lanuage problems

// LDAP server settings. Comment these settings out to disable LDAP support.
//define('CU_LDAP_SERVER', 'ldap.itd.umich.edu');
//define('CU_LDAP_PORT', '389');
//define('CU_LDAP_BASE_DN', 'OU=People,DC=umich,DC=edu');

// LDAP service account settings. By default, Copper attempts to connect 
// with the service account specified below. Comment these settings out to
// perform an unauthenticated bind before searching for a user.
define('CU_LDAP_SERVICE_DN',       'serviceuser@itd.umich.edu');
define('CU_LDAP_SERVICE_PASSWORD', 'password');

// The logfile for the LDAP login process. This may assist in debugging.
// Comment out this setting to prevent the log being recorded.
// No passwords are recorded in this logfile.
define('CU_LDAP_LOG', '/tmp/ldap.log');

// Search for users with the username field specified as field CU_LDAP_FIELD_SEARCH.
// Value should be like 'cn' or 'uid' or 'SAMAccountName', so the search filter becomes '(cn=Joe Smith)' or '(uid=Joe Smith)'.
// Most LDAP servers use 'cn', so it is quite likely that you can ignore this setting.
define('CU_LDAP_FIELD_SEARCH', 'CN');

// These defines serve to map columns in the Copper database tblUser table to LDAP user profile fields.
// This mapping is used when importing user details from the LDAP server into the Copper database.
// Defining the fields as an empty string eg. '' means they will not be populated.
// If CU_LDAP_FIELD_PHONE2 has the same value as CU_LDAP_FIELD_PHONE3, Copper's Phone3 number will map to the second number in the field specified. 
// For example, in Active Directory, setting both fields to 'othertelephone' will retrieve 
// the two most recently edited numbers in the "Phone Number (Others)" dialog under User Properties.
define('CU_LDAP_FIELD_TITLE',     '');
define('CU_LDAP_FIELD_FIRSTNAME', 'givenname');
define('CU_LDAP_FIELD_LASTNAME',  'sn');
define('CU_LDAP_FIELD_EMAIL',     'mail');
define('CU_LDAP_FIELD_PHONE1',    'telephonenumber');
define('CU_LDAP_FIELD_PHONE2',    'othertelephone');
define('CU_LDAP_FIELD_PHONE3',    'othertelephone');
define('CU_LDAP_FIELD_ADDRESS1',  'streetaddress');
define('CU_LDAP_FIELD_ADDRESS2',  '');
define('CU_LDAP_FIELD_CITY',      'l');
define('CU_LDAP_FIELD_STATE',     'st');
define('CU_LDAP_FIELD_POSTCODE',  'postalcode');
define('CU_LDAP_FIELD_COUNTRY',   'co');

define('CU_IMAP_CONNECTION_STRING',     '');
define('CU_IMAP_CONNECTION_USER',       '');
define('CU_IMAP_CONNECTION_PASSWORD',   '');

//// outgoing mail configuration
// Set this to '1' to use the standard php mail() function to send emails:
define('SYS_USE_SENDMAIL', '1');

// Otherwise, set your SMTP credentials:
define('SYS_SMTP_SERVER', 'SYS_SMTP_SERVER');
define('SYS_SMTP_PORT', 'SYS_SMTP_PORT');
define('SYS_SMTP_USER', 'SYS_SMTP_USER');
define('SYS_SMTP_PASS', 'SYS_SMTP_PASS');

