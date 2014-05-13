<?php
// $Id$
class mod_authorisation extends Module {

	function mod_authorisation() {
		$this->ModuleName   = 'authorisation';
		$this->RequireLogin = 0;
		$this->Public	   = 1;
		parent::Module();
		}

	function main() {
		switch (Request::any('action')) {
			case 'timeout':  $this->Timeout(); break;
			case 'login':	$this->Login(); break;
			case 'logout':   $this->Logout(); break;
			case 'password': $this->Password(); break;
			case 'expired': $this->Expired(); break;
			default:		 $this->LoginForm();
		}
		}

	function Password() {
		switch (Request::any('sub')) {
			case 'exec': $this->PasswordExec(); break;
			default:	 $this->PasswordForm();
		}
		}

	function GeneratePassword($length = 6) {
		while ($length--) {
			switch (rand(1, 9)) {
				case 1: case 4: case 7: $min = 97; $max = 122; break; //a - z
				case 2: case 5: case 8: $min = 65; $max = 90; break;  //A - Z
				case 3: case 6: case 9: $min = 48; $max = 57; break;  //0 - 9
			}
			@$newpassword .= chr(rand($min, $max));
		}
		return $newpassword;
		}

	function PasswordExec() {
		$emailaddress = $this->DB->Prepare(Request::post('emailaddress'));

		if (strlen($emailaddress) > 0) {
			$SQL = sprintf(SQL_GETDETAILSEMAIL, $emailaddress);
			$result = $this->DB->QuerySingle($SQL);
			if (is_array($result)) {
				$user_id	= $result['ID'];
				$user_name  = $result['Username'];
				$user_email = $result['EmailAddress'];
				$user_pass  = $this->GeneratePassword();
				$user_md5   = $this->MD5($user_pass);
				$Mail =& new SMTPMail();
				$Mail->ToName = $user_name;
				$Mail->ToAddress = $user_email;
				$Mail->FromName = SYS_FROMNAME;
				$Mail->FromAddress = SYS_FROMADDR;
				$Mail->Subject = MSG_PASSWORD_RESET;
				$Mail->Body = sprintf(MSG_PASSWORD_REQUEST_EMAIL_BODY, $user_name, $user_pass, NewLine);
				$Mail->Priority = 1;
				if ($Mail->Execute()) {
					$SQL = sprintf(SQL_UPDATEPASSWORD, $user_id, $user_md5);
					$this->DB->Execute($SQL);
					$this->LoginForm('pass', MSG_PASS_SUCCESS);
				}
				else {
					// SMTP send failed
					$this->PasswordForm('fail', MSG_PASS_FAILED);
				}
				unset($Mail);
				return;
			}
			else {
				// email address not found
				$this->PasswordForm('fail', MSG_EMAIL_NOT_FOUND);
				return;
			}
		}
		else {
			// no email address entered
			$this->PasswordForm('fail', MSG_PASS_EMPTY);
			return;
		}
		}

	function PasswordForm($class = 'pass', $message = null) {
		$title  = MSG_LOGIN;

		$vars['PASS']	= MSG_PASSWORD_REQUEST;
		$vars['EMAIL']   = MSG_EMAIL_ADDRESS;
		$vars['CLASS']   = $class;
		$vars['MESSAGE'] = $message;

		$this->setHeader($title);
		$this->setTemplate('password', $vars);
		$this->Render();
		}

	function Expired() 
	{
	  $vars['LOGO'] = Template::get_appearance_img('logo');
		$vars['HEADER_BG'] = Template::get_appearance_img('header');
		$vars['HOME_IMAGE'] = Template::get_appearance_img('home');
		$vars['TIMESTAMP'] = @filectime("assets/.htaccess");
		$vars['CHARSET'] = CHARSET;
		$vars['DOMAIN'] = array_shift(explode('.', $_SERVER['HTTP_HOST']));
		
		$this->setHeader(MSG_LOGIN);
		$this->setModule(MSG_LOGIN);
		$this->setTemplate('expired_trial', $vars);
		$this->Render();
	}
		
	function LoginForm($class = 'pass', $message = null) 
	{
		$code = Request::get('message');

		switch ($code) 
		{
			case 1:  $class = 'fail'; $message = MSG_CREDENTIALS_INCORRECT; break;
			case 2:  $class = 'fail'; $message = MSG_MUST_LOGIN; break;
			case 3:  $class = 'pass'; $message = MSG_LOGGED_OUT; break;
			case 4:  $class = 'pass'; $message = ''; break;
			case 5:  $class = 'fail'; $message = MSG_SESSION_TIMED_OUT; break;
			case 6:  $class = 'fail'; $message = MSG_LDAP_CONNECTION_FAILED; break;
			default: $class = 'fail'; $message = ( isset( $_GET['message'] ) ) ? MSG_CREDENTIALS_INCORRECT : ''; break;
		}
		
	  $vars['LOGO'] = Template::get_appearance_img('logo');
		$vars['HEADER_BG'] = Template::get_appearance_img('header');
		$vars['HOME_IMAGE'] = Template::get_appearance_img('home');
		$vars['DESC']	   = MSG_LOGIN_DETAILS;
		$vars['USERNAME']   = MSG_USERNAME;
		$vars['PASSWORD']   = MSG_PASSWORD;
		$vars['CLASS']	  = $class;
		$vars['MESSAGE']	= ($message) ? $message : '';
		$vars['PASS']	= MSG_PASSWORD_REQUEST;
		$vars['EMAIL']   = MSG_EMAIL_ADDRESS;
		$vars['TIMESTAMP'] = @filectime("assets/.htaccess");
		$vars['CHARSET'] = CHARSET;

		// Send 403 as a signal to AJAX requests that the session has timed out,
		// and the user should be redirected to the login screen.
		// If this is not done, the login screen will be inserted into the container.
		if (Request::any('module') && (Request::any('module') != $this->ModuleName))
		{
			$this->Session->set('expected_destination', Request::get_request_uri(TRUE, TRUE));
			header('HTTP/1.1 403 Forbidden');   
		}

		$this->setHeader(MSG_LOGIN);
		$this->setModule(MSG_LOGIN);
		$this->setTemplate('login', $vars);
		$this->Render();
	}

	function Login() {
			if (defined('CU_LDAP_SERVER'))
		{
			$this->LdapLogin();
			return;
		}

		$user = new CopperUser(array(
			'username' => Request::post('username'),
			// remember to use module salt.
			'password' => $this->MD5(Request::post('password')),
		));

		
		if ($user->exists) 
		{
			Response::cookie('authorised', 1);
			$this->Session->Set('authorised', 1);
			$this->Session->Set('userid', $user->ID);
			$this->Log('login', $user->ID, MSG_LOGIN, MSG_SUCCESSFUL);
			$userResourceSQL = sprintf(SQL_USER_RESOURCE, $user->ID);
			$userResource = $this->DB->QuerySingle($userResourceSQL);
			if ($userResource['ID'])
			  $this->ResourceSet($userResource['ID'], $userResource['AvailabilityType'], $userResource['WeekDays']);

			if ( ! ($redirect = $this->Session->get('expected_destination') ) )
			{
				$redirect = 'index.php?module=' . $user->Module;
			}

			// run cronned jobs
			include_once('system/modules/cron/index.php');
			$cron = new mod_cron();
			$cron->Run();
			$cron = null;
		}
		else {
			$redirect = 'index.php?module=' . CU_AUTH_MODULE . '&message=1';
		}

		Response::redirect($redirect);
	}

	function ResourceSet($resourceID, $availabilitytype, $weekdays) {
		// get the user name
		$userResourceSQL = sprintf(SQL_USER_RESOURCE_FROM_RESOURCEID, $resourceID);
		$userResource = $this->DB->QuerySingle($userResourceSQL);

		$fromEpoch = gmmktime(0,0,0,date('m'),date('d'),date('Y'));
		$toEpoch = gmmktime(0,0,0,date('m'),(date('d') + 365),date("Y"));

		$dayIDsSQL = sprintf(SQL_GET_ID_EPOCH_WEEKDAY_FROM_DAY, $fromEpoch, $toEpoch);
		$RS =& new DBRecordset();
		$RS->Open($dayIDsSQL, $this->DB);
		$dayIDs = $RS->_Result;
		$RS->Close();
		unset($RS);
		if ($availabilitytype == 0) {
			for ($i = 0; $i < count($dayIDs); $i++) {
				$dayIDs[$i]['HoursAvailable'] = MAX_DAY_LENGTH;
			}
		}
		else {
			$weekdays = @split("\|",$weekdays);
			for ($i = 0; $i < count($dayIDs); $i++) {
				for ($j = 0; $j <= 6; $j++) {
					if ($dayIDs[$i]['Weekday'] == ($j + 1)) $dayIDs[$i]['HoursAvailable'] = $weekdays[$j];
				}
			}
		}
		// check if they are not going to set there availability to less then the time they are Committed to tasks
		// get the Committed duration for all tasks
		$durationOfTasksSQL = sprintf(SQL_GET_HOURS_COMMITTED_OF_TASKS, $resourceID, $dayIDs[0]['ID'], $dayIDs[count($dayIDs) - 1]['ID']);
		$RS =& new DBRecordset();
		$RS->Open($durationOfTasksSQL, $this->DB);
		$durationOfTasks = $RS->_Result;
		$RS->Close();
		unset($RS);

		// store the day id for any over Committed days in the array
		$overCommitted = '';
		for ($i = 0; $i < count($dayIDs); $i++) {
			for ($j = 0; $j < count($durationOfTasks); $j++) if ($durationOfTasks[$j]['ID'] == $dayIDs[$i]['ID']) break;
			if ($j < count($durationOfTasks)) {
			  if ($dayIDs[$i]['HoursAvailable'] < $durationOfTasks[$j]['HoursCommittedCache']) $overCommitted .= '\'' . $dayIDs[$i]['ID'] . '\',';
			}
		}

		// create the insert statement 

		for ($i = 0; $i < count($dayIDs); $i++) { 
			$insertResourceDaysSQL = 'INSERT INTO tblResourceDay (ResourceID, DayID, HoursAvailable, HoursCommittedCache) Values '; 
			$insertResourceDaysSQL .= '(' . $resourceID . ', ' . $dayIDs[$i]['ID'] . ', ' . $dayIDs[$i]['HoursAvailable']; 
			for ($j = 0; $j < count($durationOfTasks); $j++) if ($durationOfTasks[$j]['ID'] == $dayIDs[$i]['ID']) break; 
			if ($j < count($durationOfTasks)) $insertResourceDaysSQL .= ', ' . $durationOfTasks[$j]['HoursCommittedCache']; 
			else $insertResourceDaysSQL .= ', 0'; 
			$insertResourceDaysSQL .= ')';
			$exists = $this->DB->ExecuteScalar("SELECT ResourceID FROM tblResourceDay WHERE ResourceID = $resourceID AND DayID = {$dayIDs[$i]['ID']}");
			if (!$exists)
			  $this->DB->Execute($insertResourceDaysSQL); 
		} 

	}

	function LdapLogin() {
		$username = Request::post('username');
		$password = Request::post('password');
		$redirect = Request::post('redirect');

		if (strlen($password) < 1)
		{
			$this->LdapDebug("Notice User $username attempted to log in with an empty password.\n");
			$redirect = 'index.php?module=' . CU_AUTH_MODULE . '&message=1';
			Response::redirect($redirect);
			exit(0);
		}

		// Connect to the LDAP server.
		$conn = ldap_connect(CU_LDAP_SERVER, CU_LDAP_PORT);
		if (!$conn)	// Could not connect to the LDAP server.
		{
			$this->LdapDebug("Error! Could not connect to server ".CU_LDAP_SERVER.".\n");
			$redirect = 'index.php?module=' . CU_AUTH_MODULE . '&message=6'; 
			Response::redirect($redirect);
			exit(0);
		}
		$this->LdapDebug("Notice Connected to server ".CU_LDAP_SERVER.".\n");

		ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);

		// If a service account is defined, connect with those details.
		// Otherwise perform an unauthenticated bind.
		if (defined('CU_LDAP_SERVICE_DN'))
			$bind = ldap_bind($conn, CU_LDAP_SERVICE_DN, CU_LDAP_SERVICE_PASSWORD);
		else
			$bind = ldap_bind($conn);

		$bindType = (defined('CU_LDAP_SERVICE_DN')) ? 'service' : 'anonymous';
		if (!$bind)	// Could not bind to the LDAP server anonymously or with service username/password.
		{
			$this->LdapDebug("Error! Could not bind as $bindType user.\n");
			$redirect = 'index.php?module=' . CU_AUTH_MODULE . '&message=6';
			Response::redirect($redirect);
			exit(0);
		}
		$this->LdapDebug("Notice Bound as $bindType user.\n");

		// If the bind attempt was successful, search for the supplied username.
		// If the service user (or anonymous user) has no permission to search, 
		// PHP will print Operation error as a warning so you won't see it, and 
		// $numEntries will be an empty string rather than an integer.
		// If the user exists in LDAP, rebind as that user.
		$filter = '('.CU_LDAP_FIELD_SEARCH.'='.$username.')';
		$attrs = array();
		$results = ldap_search($conn, CU_LDAP_BASE_DN, $filter); 
		$numEntries = ldap_count_entries($conn, $results);

		if ($numEntries == 0)	// Could not find any object with supplied username.
		{
			$this->LdapDebug("Error! Search with filter $filter returned $numEntries results.\n");
			ldap_close($conn);
			$redirect = 'index.php?module=' . CU_AUTH_MODULE . '&message=1';
			Response::redirect($redirect);
			exit(0);
		}

		if ($numEntries > 1)	// Found more than one object with supplied username.
		{
			$this->LdapDebug("Error! Search with filter $filter returned $numEntries results.\n");
			ldap_close($conn);
			$redirect = 'index.php?module=' . CU_AUTH_MODULE . '&message=1';
			Response::redirect($redirect);
			exit(0);
		}

		$this->LdapDebug("Notice Search with filter $filter returned 1 result.\n");
		$entries = ldap_get_entries($conn, $results);
		$userDN = $entries[0]['dn'];

		// The PHP Manual says:
		// ldap_unbind kills the link descriptor. So, if you want to rebind as another user, 
		// just bind again; don't unbind. Otherwise, you'll have to open up a new connection.	   
		$bind = ldap_bind($conn, $userDN, $password);
		if (!$bind)	// Could not bind to the LDAP server with username/password.
		{
			$this->LdapDebug("Error! Could not bind as user $username (DN: $userDN). Is password correct?\n");
			ldap_close($conn);
			$redirect = 'index.php?module=' . CU_AUTH_MODULE . '&message=1';
			Response::redirect($redirect);
			exit(0);
		}
		$this->LdapDebug("Notice Bound as user $username.\n");

		// At this point, the username and password are correct, so the user should be logged in.
		// First, check if the user already exists in the Copper database.
		$username = $this->DB->Prepare($username);
		$sql = sprintf(SQL_GETUSER_BYUSERNAME, $username);
		$result = $this->DB->QuerySingle($sql);

		// If the user does not exist in the database, create one using the data retrieved from the LDAP server.
		if (!is_array($result))
		{
			$password  = $this->DB->Prepare($this->MD5($password));
			$title	 = (CU_LDAP_FIELD_TITLE == '')	 ? '' : $this->DB->Prepare($entries[0][CU_LDAP_FIELD_TITLE][0]);
			$firstName = (CU_LDAP_FIELD_FIRSTNAME == '') ? '' : $this->DB->Prepare($entries[0][CU_LDAP_FIELD_FIRSTNAME][0]);
			$lastName  = (CU_LDAP_FIELD_LASTNAME == '')  ? '' : $this->DB->Prepare($entries[0][CU_LDAP_FIELD_LASTNAME][0]);
			$email	 = (CU_LDAP_FIELD_EMAIL == '')	 ? '' : $this->DB->Prepare($entries[0][CU_LDAP_FIELD_EMAIL][0]);
			$phone1	= (CU_LDAP_FIELD_PHONE1 == '')	? '' : $this->DB->Prepare($entries[0][CU_LDAP_FIELD_PHONE1][0]);
			$phone2	= (CU_LDAP_FIELD_PHONE2 == '')	? '' : $this->DB->Prepare($entries[0][CU_LDAP_FIELD_PHONE2][0]);
			$phone3	= (CU_LDAP_FIELD_PHONE3 == '')	? '' : $this->DB->Prepare($entries[0][CU_LDAP_FIELD_PHONE3][0]);
			$address1  = (CU_LDAP_FIELD_ADDRESS1 == '')  ? '' : $this->DB->Prepare($entries[0][CU_LDAP_FIELD_ADDRESS1][0]);
			$address2  = (CU_LDAP_FIELD_ADDRESS2 == '')  ? '' : $this->DB->Prepare($entries[0][CU_LDAP_FIELD_ADDRESS2][0]);
			$city	  = (CU_LDAP_FIELD_CITY == '')	  ? '' : $this->DB->Prepare($entries[0][CU_LDAP_FIELD_CITY][0]);
			$state	 = (CU_LDAP_FIELD_STATE == '')	 ? '' : $this->DB->Prepare($entries[0][CU_LDAP_FIELD_STATE][0]);
			$postcode  = (CU_LDAP_FIELD_POSTCODE == '')  ? '' : $this->DB->Prepare($entries[0][CU_LDAP_FIELD_POSTCODE][0]);
			$country   = (CU_LDAP_FIELD_COUNTRY == '')   ? '' : $this->DB->Prepare($entries[0][CU_LDAP_FIELD_COUNTRY][0]);

			// Windows 2003 Active Directory returns "Other" phone numbers in the 'othertelephone' array.
			// If CU_LDAP_FIELD_PHONE3 has the same value as CU_LDAP_FIELD_PHONE3, get $phone3 from
			// the second element of the 'othertelephone' array.
			// If CU_LDAP_FIELD_PHONE2 has the same value as CU_LDAP_FIELD_PHONE3, 
			// map Copper's Phone3 number to the second number in the field specified. 
			// For example, in Active Directory, setting both fields to 'othertelephone' will retrieve 
			// the two most recently edited numbers in the "Phone Number (Others)" dialog under User Properties.
			if (CU_LDAP_FIELD_PHONE2 != '' && CU_LDAP_FIELD_PHONE2 == CU_LDAP_FIELD_PHONE3)
				$phone3 = $this->DB->Prepare($entries[0][CU_LDAP_FIELD_PHONE3][1]);

			$sql = sprintf(SQL_CREATE_USER, $username, $password, $title, $firstName, $lastName, $email,
				$phone1, $phone2, $phone3, $address1, $address2, $city, $state, $postcode, $country, '', '', '', 1, 1);
			$this->DB->Execute($sql);
			$userID = $this->DB->ExecuteScalar(SQL_LAST_INSERT);

			$sql = sprintf(SQL_USER_SETRESOURCE, $userID);
			$this->DB->Execute($sql);
			$sql = sprintf(SQL_CREATE_USER_PERMISSIONS, $userID, 'administration', '0');
			$this->DB->Execute($sql);
			$sql = sprintf(SQL_CREATE_USER_PERMISSIONS, $userID, 'budget', '2');
			$this->DB->Execute($sql);
			$sql = sprintf(SQL_CREATE_USER_PERMISSIONS, $userID, 'clients', '2');
			$this->DB->Execute($sql);
			$sql = sprintf(SQL_CREATE_USER_PERMISSIONS, $userID, 'springboard', '2');
			$this->DB->Execute($sql);
			$sql = sprintf(SQL_CREATE_USER_PERMISSIONS, $userID, 'projects', '2');
			$this->DB->Execute($sql);
			$sql = sprintf(SQL_CREATE_USER_PERMISSIONS, $userID, 'files', '2');
			$this->DB->Execute($sql);
			$sql = sprintf(SQL_CREATE_USER_PERMISSIONS, $userID, 'contacts', '2');
			$this->DB->Execute($sql);
			$sql = sprintf(SQL_CREATE_USER_PERMISSIONS, $userID, 'calendar', '2');
			$this->DB->Execute($sql);
			$sql = sprintf(SQL_CREATE_USER_PERMISSIONS, $userID, 'reports', '0');
			$this->DB->Execute($sql);

			// Retrieving the user data we've just set in this way is inefficient, but it keeps 
			// the code below similar to Login(), which you'll thank me for later.
			$sql = sprintf(SQL_GETUSER_BYUSERNAME, $username);
			$result = $this->DB->QuerySingle($sql);

			if (is_array($result) && is_numeric($result['ID']) && $result['Active'] == '1') 
				$this->LdapDebug("Notice Added user $username to Copper database with userID $userID.\n");
			else
				$this->LdapDebug("Error! Failed to add user $username to Copper database.\n");
		}

		// The connection to the LDAP server is no longer needed.
		ldap_close($conn);
		$this->LdapDebug("Notice Connection to server closed.\n");

		// The user exists in the database. Has the user been disabled in the tblUser table?
		if (is_array($result) && is_numeric($result['ID']) && $result['Active'] == '0') 
		{
			$redirect = 'index.php?module=' . CU_AUTH_MODULE . '&message=1';
			Response::redirect($redirect);
			exit(0);
		}

		// The user exists and is active, so log them in.
		if (is_array($result) && is_numeric($result['ID']) && $result['Active'] == '1') 
		{
			Response::cookie('authorised', 1);
			$this->Session->Set('authorised', 1);
			$this->Session->Set('userid', $result['ID']);
			$this->Log('login', $result['ID'], MSG_LOGIN, MSG_SUCCESSFUL, MSG_LDAP_DN.": $userDN");

			// Run cron jobs.
			include_once('system/modules/cron/index.php');
			$cron = new mod_cron();
			$cron->Run();
			$cron = null;

			if (strlen($redirect) > 0) 
				$redirect = 'index.php?' . urldecode($redirect);
			else 
				$redirect = 'index.php?module=' . $result['Module'];
			Response::redirect($redirect);
		}
		else 
		{
			$redirect = 'index.php?module=' . CU_AUTH_MODULE . '&message=1';
			Response::redirect($redirect);
		}
	}

	function Logout() {
		$this->Session->Abandon();
		Response::cookie('authorised', null);
		Response::redirect('index.php?module=' . CU_AUTH_MODULE . '&message=3');
		}

	function Timeout() {
		$this->Session->Abandon();
		Response::cookie('authorised', null);
		Response::redirect('index.php?module=' . CU_AUTH_MODULE . '&message=5&redirect=' 
			. urlencode(Request::get('redirect')));
		}

	function LdapDebug($msg) {
		$msg = $_SERVER['REMOTE_ADDR'].' '.date('Y-m-d h:i:s').' '.$msg;
		if (defined('CU_LDAP_LOG'))
			file_put_contents(CU_LDAP_LOG, $msg, FILE_APPEND);
	}
}
 
