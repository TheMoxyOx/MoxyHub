<?php
// $Id$
class mod_administration extends Module {
	var $AccessList = array(MSG_DENY, MSG_READ, MSG_WRITE);
	var $dateSelectorFormat = array('1' => 'yyyy-mm-dd', '2' => 'yyyy-dd-mm', '3' => 'dd-mm-yyyy', '4' => 'mm-dd-yyyy');
	var $dateFormat = array('1' => 'Y-m-d', '2' => 'Y-d-m', '3' => 'd-m-Y', '4' => 'm-d-Y');
	var $imTypes = array(MSG_MSN, MSG_ICQ, MSG_YAHOO, MSG_AIM, MSG_JABBER);
	var $availabilityOptions = array(MSG_ALWAYS, MSG_NUMBER_OF_HOURS_PER_DAY);

	private $appearance_images = array('logo', 'header', 'home');
	private $appearance_pdfs = array( 'invoice', 'invoice_notax', 'quote', 'quote_notax');

	const AJAX_EDIT_EDIT = 1;
	const AJAX_EDIT_COPY = 2;
	const AJAX_EDIT_NEW  = 3;

	const USERS_AT_LIMIT		= 1;
	const USERS_BELOW_LIMIT = 2;
	const USERS_ABOVE_LIMIT = 3;
	

	function mod_administration() {
		$this->ModuleName   = 'administration';
		$this->RequireLogin = 1;
		$this->Public	   = 0;
		parent::Module();
	}

	function main() {
		if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_READ)) {
			switch (Request::any('action')) {
				case 'users'		: $this->UserList();break;
				case 'savesettings' : $this->SaveSettings();break;
				case 'groups'	   : $this->Groups();break;
				case 'groupnew'	 : $this->GroupNew();break;
				case 'groupview'	: $this->Groups();break;
				case 'groupsave'	: $this->GroupSave();break;
				case 'groupdel'	 : $this->GroupDel();break;
				case 'groupmodadd'  : $this->GroupModulesAdd();break;
				case 'groupmoddel'  : $this->GroupModulesDel();break;
				case 'grouppermdel' : $this->GroupPermissionDel(); break;
				case 'groupperm'	: $this->GroupPermissions(Request::any('id'));break;
				case 'addproj'	  : $this->GroupPermissionAdd(); break;
				case 'addclient'	: $this->GroupPermissionAdd(); break; // this alos gets called when updating permissions.
				case 'useraddproj'  : $this->UserPermissionAdd(); break;
				case 'useraddclient': $this->UserPermissionAdd(); break;
				case 'usermodadd'   : $this->UserModulesAdd();break;
				case 'usermoddel'   : $this->UserModulesDel();break;
				case 'userpermdel'  : $this->UserPermissionDel(); break;
				case 'usernew'	  : $this->UserNew(); break;
				case 'useredit'	 : $this->UserList(); break;
				case 'userview'	 : $this->UserList(); break;
				case 'usercopy'	 : $this->UserEdit(); break;
				case 'usersave'	 : $this->UserSave(); break;
				case 'userdel'	  : $this->UserDel(); break;
				case 'language'	 : $this->Language(); break;
				case 'language_save': $this->LanguageSave(); break;
				case 'import'	   : $this->Import(); break;
				case 'importdb'	 : $this->ImportDB(); break;
				case 'import_from_basecamp'	 : $this->ImportFromBasecamp(); break;
				case 'export'	   : $this->Export(); break;
				case 'tools'		: $this->Tools(); break;
				case 'download'	 : $this->Download(); break;
				case 'appearance'   : $this->Appearance(); break;
				case 'saveappearance':  $this->SaveAppearance(); break;
//				case 'setavailability': $this->ResourceSet();break;
//				case 'resourcesetsave': $this->ResourceSet();break;
				case 'ajaxuserview':  $this->AjaxUserView(); break;
				case 'ajaxuseredit':  $this->AjaxUserEdit(self::AJAX_EDIT_EDIT); break;
				case 'ajaxusercopy':  $this->AjaxUserEdit(self::AJAX_EDIT_COPY); break;
				case 'ajaxusernew':   $this->AjaxUserEdit(self::AJAX_EDIT_NEW); break;
				case 'ajaxgroupview': $this->AjaxGroupView(); break;
				case 'ajaxgroupedit': $this->AjaxGroupEdit(self::AJAX_EDIT_EDIT); break;
				case 'ajaxgroupcopy': $this->AjaxGroupEdit(self::AJAX_EDIT_COPY); break;
				case 'ajaxgroupnew':  $this->AjaxGroupEdit(self::AJAX_EDIT_NEW); break;
				case 'ajaxgroupmember':  $this->AjaxGroupMember(); break;
				default			 : $this->Settings();
			}
		}
		else {
			$this->ThrowError(2001);
		}
	}

	function AjaxGroupView() {
		header('Content-Type: text/html; charset='.CHARSET);
		$groupID = Request::get('id', Request::R_INT);
		$this->DisplayGroupForm($groupID);
	}

	function AjaxGroupEdit($type = self::AJAX_EDIT_EDIT) {
		header('Content-Type: text/html; charset='.CHARSET);
		if (($type == self::AJAX_EDIT_EDIT) || ($type == self::AJAX_EDIT_COPY))
		{
			$groupID = Request::get('id', Request::R_INT);
		} else {
			// really, this should be incrementing and unique, as multiple 'create' forms might be made. for now, we need to fix.
			$groupID = 0;
		}
		$this->DisplayGroupForm($groupID);
	}

	function AjaxUserEdit($type = self::AJAX_EDIT_EDIT) {
		header('Content-Type: text/html; charset='.CHARSET);
		$userID = Request::get('id', Request::R_INT);

		if (!$this->User->HasModuleItemAccess($this->ModuleName, $userID, CU_ACCESS_WRITE))
		{
			$this->ThrowError(2001);
			return;
		}
		
		// require users available returns false if there wasn't users available.
		if (($type != self::AJAX_EDIT_EDIT) && (!$this->requireUsersAvailable()))
		{
			return;
		}

		$this->DisplayUserForm($userID);
	}

	function AjaxUserView() {
		header('Content-Type: text/html; charset='.CHARSET);
		$userID = Request::get('id', Request::R_INT);

		if (!$this->User->HasModuleItemAccess($this->ModuleName, $userID, CU_ACCESS_READ))
		{
			$this->ThrowError(2001);
			return;
		}

		$this->DisplayUserForm($userID);
	}

	function Appearance() {
		$this->CreateTabs('appearance');

		$data = array();
		foreach($this->appearance_images as $file)
		{
			$data[$file] = Settings::get($file);
		}
		
		foreach($this->appearance_pdfs as $file)
		{
			$data[$file] = Settings::get($file);
		}
		
		$this->includeTemplate('appearance', $data, TRUE);

		$this->setHeader(MSG_ADMINISTRATION);
		$this->setModule(MSG_APPEARANCE, $modAction);
		$this->Render();
	}

	function SaveAppearance() 
	{
		$this->upload_appearance_fileset($this->appearance_images, 'AppearanceImageUpload');
		$this->upload_appearance_fileset($this->appearance_pdfs, 'AppearancePDFUpload');
		
		
		Response::redirect('index.php?module=administration&action=appearance');
	}

	private function upload_appearance_fileset($set, $class)
	{
		foreach($set as $file)
		{
			// see if the user is trying to upload this file
			$file_info = Request::files($file, Request::R_ARRAY);
			if ($file_info['error'] === UPLOAD_ERR_OK)
			{
				$au = new $class();
				$file_data = $au->create_from_upload($file);

				if (is_array($file_data))
				{
					Settings::set($file, $file_data['filename']);
				} else {
					// we should show some error here.
					Debug::dump(Upload::get_upload_error($file_data));
				}
				
			}
		}
	}


	function Download() {
		$file = getcwd() . '/system/modules/projects/templates/InvoiceQuoteTemplate.psd';
		if ( is_readable( $file ) )
		{
			// Content-length is invalid when ob_gzip is enabled.
			//header( 'Content-length: ' . filesize( $file ) );
			header( 'Content-type: application/octet-stream' );
			header( 'Content-disposition: attachment; filename="' . basename($file ) . '"' );
			readfile( $file );
			die();
		}
		else
			header( 'Location: index.php?module=administration' );
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

		/*
		if ($overCommitted) {
			$overCommitted = substr($overCommitted,0,strlen($overCommitted) - 1);

			// get the task id, month and year for the task that they are overCommitted to
			$daysOverCommittedSQL = sprintf(SQL_GET_OVER_COMMITTED,$resourceID, $overCommitted);
			$RS =& new DBRecordset();
			$RS->Open($daysOverCommittedSQL, $this->DB);
			$daysOverCommitted = $RS->_Result;
			$RS->Close();
			unset($RS);

			$errorMessage = '';
			$errorMessage .= '<tr align="left" valign="middle">';
			$errorMessage .= '<td colspan="2">';
			$errorMessage .= '<p class="errors"><b>Error:</b> Unable to set the availability for this resource because the resource would become over committed for the following:<br>';
			for ($i = 0; $i < count($daysOverCommitted); $i++) {
				if ($i > 0 && $daysOverCommitted[$i]['TaskID'] != $daysOverCommitted[($i - 1)]['TaskID']) {
					$errorMessage = substr($errorMessage,0,strlen($errorMessage) - 2);
					$errorMessage .= '<br>Task: <a href="">' . $daysOverCommitted[$i]['Name'] . '</a><br>';
				}
				else if ($i == 0) $errorMessage .= 'Task: <a href="">' . $daysOverCommitted[$i]['Name'] . '</a><br>';
				$errorMessage .=  date('d/m/Y',$daysOverCommitted[$i]['Epoch']) . ', ';
			}
			$errorMessage = substr($errorMessage,0,strlen($errorMessage) - 2);
			$errorMessage .= '</p></td></tr>';
		  echo $errorMessage;
		}
		else {
		*/

		// do it
			$tmpl['OK'] = MSG_OK;

			// delete the resource day enterys we are about to insert
			$resourceDayWhereInRange = 'DayID >= ' . $dayIDs[0]['ID'] . ' AND DayID <= ' . $dayIDs[count($dayIDs) - 1]['ID'];
			$deleteResourceDaySQL = 'DELETE FROM tblResourceDay WHERE ResourceID = ' . $resourceID . ' AND ' . $resourceDayWhereInRange;
			$this->DB->Execute($deleteResourceDaySQL);

			// create the insert statement
			$insertResourceDaysSQL = 'INSERT INTO tblResourceDay (ResourceID, DayID, HoursAvailable, HoursCommittedCache) Values ';

			for ($i = 0; $i < count($dayIDs); $i++) {
				$insertResourceDaysSQL .= '(' . $resourceID . ', ' . $dayIDs[$i]['ID'] . ', ' . $dayIDs[$i]['HoursAvailable'];
				for ($j = 0; $j < count($durationOfTasks); $j++) if ($durationOfTasks[$j]['ID'] == $dayIDs[$i]['ID']) break;
				if ($j < count($durationOfTasks)) $insertResourceDaysSQL .= ', ' . $durationOfTasks[$j]['HoursCommittedCache'];
				else $insertResourceDaysSQL .= ', 0';
				$insertResourceDaysSQL .= ')';
				if (($i + 1) < count($dayIDs)) $insertResourceDaysSQL .= ',';
			}
			$this->DB->Execute($insertResourceDaysSQL);
		//	}

	}

//change_log 4.
	function Import() {
		$modTitle = MSG_ADMINISTRATION;
		$modHeader = MSG_IMPORT_DB_FILE;

		$this->setTemplate('file_upload', $tmpl);

		$this->setHeader($modTitle);
		$this->setModule($modHeader);
		$this->Render();
	}

//change_log 4.
	function ImportDB() {
		set_time_limit(600);
		ignore_user_abort(1);

		$file = Request::files('file', Request::R_ARRAY);
		$file_tmp  = $file['tmp_name'];
		$file_name = $file['name'];
		$file_type = $file['type'];
		$file_size = $file['size'];
		if ($file_size > 0) {
			$sql_statements = file_get_contents($file_tmp);
			$arr_sql =  preg_split('/;[\n\r]+/',$sql_statements);
			reset($arr_sql);
			$arr_success=array();
			$arr_failure=array();
			$this->DB->Query('DROP DATABASE '.DB_NAME);
			$this->DB->Query('CREATE DATABASE '.DB_NAME);
			$this->DB		   =& new DBConnection(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
			while (list($k,$v)=each($arr_sql))
			{
			   $this->DB->Query($v);
				if(0 < strlen($this->DB->LastErrorMessage))
					echo '<p>'.$this->DB->LastErrorMessage;
			}
			Response::redirect('index.php?module=authorisation&action=logout');
		}
		else {
		   $this->ThrowError(3000);
		}
	}

	function ImportFromBasecamp()
	{
		// we parse with simplexml.
		$file = Request::files('basecamp_xml', Request::R_ARRAY);
		$bc = new Basecamp();
		if ($file && $file['tmp_name'])
		{
			$bc->import($file['tmp_name']);
		}
		
		$this->CreateTabs('tools');
		$this->includeTemplate('basecamp/review_users', array('stats' => $bc->import_stats), TRUE);
		$this->setHeader(MSG_ADMINISTRATION);
		$this->setModule(MSG_BACKUP);
		$this->Render();
	}

//change_log 4.
	function Export() {
		// if we would allow exec, we could use something like this:
		// $fpath = '/tmp/' . 'sqlexp';
		// exec('mysqldump '.DB_NAME.' -u'.DB_USERNAME.' -p'.DB_PASSWORD.' > '. $fpath .'.sql');
		
		// ...but we're not. So stop output buffering!
		ob_end_flush();

		// and set headers
		header( "Content-type: text-plain");
		header( "content-disposition: attachment; filename=\"".DB_NAME.".sql\"");

		$db = DB_PREFIX . DB_NAME;
		$sql = sprintf(SQL_SHOW_TABLES, $db);
		$result = $this->DB->Query($sql);

		// and write directly to the output. no buffers, pls!
		$fp = fopen('php://output', 'w');

		$firstrun = true;
		foreach ($result as $key => $value)
		{
			$table = $value[0];
			$create_result = $this->DB->QuerySingle(sprintf(SQL_SHOW_CREATE_TABLE, $table));
			$insert_sql = "";
			$create_result[1] .= ";";
			
			if (!$firstrun)
			{
				fputs($fp, ";");
				$firstrun = false;		
			}
			// output create syntax.
			fputs($fp, str_replace("\n", '', $create_result[1]));

			// now get all the data
			$qry = "SELECT * FROM $table";
			$this->DB->QueryPrepare($qry);
			$query = @mysql_query($qry, $this->DB->ID);
			if (!$query)
			{
				continue;
			}
			$firstrun = true;

			while ($table_result = mysql_fetch_assoc($query))
			{
				if (!$firstrun)
				{
					fputs($fp, ',');
				} else
				{
					fputs($fp, "\nINSERT INTO $table VALUES ");
					$firstrun = false;
				}

				// and now output the values of a row
				fputs($fp, "(");
				$n = count($table_result);
				foreach ($table_result as $value)
				{
					if ($n-- > 1)
					{
						fputs($fp, "'".mysql_real_escape_string($value)."', ");
					} else
					{
						fputs($fp, "'".mysql_real_escape_string($value)."')");
					}
				}
				continue;
			}
			if ($query)
			{
				mysql_free_result($query);
			}
		}
	}

//change_log 4.
	function Tools() {
		$title = MSG_ADMINISTRATION;
		$breadcrumbs = MSG_BACKUP;

		$this->CreateTabs('tools');

		$this->setTemplate('tools');

		$this->setHeader($title);
		$this->setModule($breadcrumbs);

		$this->Render();
	}

	function LanguageSave() {
			$filter = Request::any('filter');
		if (!($filter == 'MOD' || $filter == 'ALL' || ($filter >= 'A' && $filter <= 'Z'))) {
			$this->ThrowError(3000);
		}

		// do we want to delete all the language token overrides, or just some?
		// ie, were we showing all the overwritten tokens at once, or just a subset?
		if ($filter == 'ALL' || $filter == 'MOD') {
			$SQL = sprintf(SQL_DELETE_LANGUAGE, Settings::get('DefaultLanguage'));
		}
		else {
			$SQL = sprintf(SQL_DELETE_LANGUAGE_FILTER, Settings::get('DefaultLanguage'), "$filter%");
		}

		// Ka-Pow!

		$this->DB->Execute($SQL);
		$form = Request::$POST;
		foreach ($form as $key=>$value) {
			if ((substr($key, 0, 4) == 'MSG_') && ($value != '')) {
				$SQL = sprintf(SQL_INSERT_LANGUAGE, $key, Settings::get('DefaultLanguage'), $value);
				$this->DB->Execute($SQL);
			}
		}
		Response::redirect('index.php?module=administration&action=language');
		}

	function Language() {
	
		// get the filter, if any
		$filter = Request::get('filter');
		// if the filter isn't a capital letter in [A-Z], and isn't ALL then force it to be MOD
		// this prevents stupid or missing filter values
		if (!($filter >= 'A' && $filter <='Z') && ($filter != 'ALL')) {
			$filter = 'A';
		}

		// build the filter string
		for ($i = 65; $i < 91; $i++) {
			$chr = chr($i);
			$filterHeader .= '<a href="index.php?module=administration&action=language&filter=' . $chr . '">' . $chr . '</a>';
			$filterHeader .= ' | ';
		}
		$filterHeader .= '<a href="index.php?module=administration&action=language&filter=ALL">' . strtoupper(MSG_ALL) . '</a> | ';
		$filterHeader .= '<a href="index.php?module=administration&action=language&filter=MOD">' . strtoupper(MSG_MODIFIED) . '</a>';

		$modAction[] = '<a href="javascript:document.language.submit();">'.MSG_SAVE.'</a>';
		$this->CreateTabs('language');
		$tmpl['FILTER'] = $filter;
		$tmpl['LETTERS'] = $filterHeader;

		// load the override array with values from the database
		$RS =& new DBRecordset();
		// we use the same SQL query as the initial language loader, called on init
		$SQL = sprintf(CU_SQL_LANGUAGE_OVERRIDE, Settings::get('DefaultLanguage'));
		$RS->Open($SQL, $this->DB);
		$override = array();
		while (!$RS->EOF()) {
			$override[$RS->Field('Token')] = $RS->Field('Value');
			$RS->MoveNext();
		}

		ksort($override);
		$constants = get_defined_constants();
		foreach ($constants as $key=>$value) {
			if (substr($key, 0, 9) == 'ORIGINAL_') {
				$key = str_replace('ORIGINAL_', '', $key);
				$new_constants[$key] = $value;
			}
		}
		if (is_array($new_constants)) {
			$constants = array_merge($constants, $new_constants);
			ksort($constants);
		}

		// What modified tokens do we display at the top? All of them, or only a range?
		if (count($override) == 0 && $filter == 'MOD') {
//			$this->setTemplate('language_message', array('MESSAGE'=>'borg'));
		}
		else if ($filter == 'MOD' || $filter == 'ALL') {
					$this->setTemplate('language_header', $tmpl);
			$old_first_char = '';
			$count = 0;
			foreach($override as $key=>$value) {
				$new_first_char = strtoupper(substr($key, 4, 1));
				if ($new_first_char != $old_first_char) {
					$tmpl = array();
					$old_first_char = $new_first_char;
					$tmpl['LETTER'] = $old_first_char;
					$this->setTemplate('language_item_alpha', $tmpl);
				}
				$tmpl = array();
				$tmpl['TOKEN'] = $key;
				$tmpl['DEFAULT_VALUE'] = $constants[$key];
				$new_value = htmlspecialchars($override[$key]);
				if (strlen($value) > 40) {
					$tmpl['NEW_VALUE'] = "<textarea class=\"edit\" name=\"$key\">$new_value</textarea>";
				}
				else {
					$tmpl['NEW_VALUE'] = "<input class=\"edit\" name=\"$key\" value=\"$new_value\">";
				}
				$this->setTemplate('language_item', $tmpl);
				$count++;
			}
				}
		else {
					$this->setTemplate('language_header', $tmpl);
			$old_first_char = '';
			$count = 0;
			foreach($override as $key=>$value) {
				if (substr($key, 4, 1) != $filter) {
					continue;
				}
				$new_first_char = strtoupper(substr($key, 4, 1));
				if ($new_first_char != $old_first_char) {
					$tmpl = array();
					$old_first_char = $new_first_char;
					$tmpl['LETTER'] = $old_first_char;
					$this->setTemplate('language_item_alpha', $tmpl);
				}
				$tmpl = array();
				$tmpl['TOKEN'] = $key;
				$tmpl['DEFAULT_VALUE'] = $constants[$key];
				$new_value = htmlspecialchars($override[$key]);
				if (strlen($value) > 40) {
					$tmpl['NEW_VALUE'] = "<textarea class=\"edit\" name=\"$key\">$new_value</textarea>";
				}
				else {
					$tmpl['NEW_VALUE'] = "<input class=\"edit\" name=\"$key\" value=\"$new_value\">";
				}
				$this->setTemplate('language_item', $tmpl);
				$count++;
			}
				}

		// Display the list of un-altered tokens below
		if ($filter != 'MOD' && $filter != 'ALL') {
					$old_first_char = '';
			$count = 0;
			foreach($constants as $key=>$value) {
				if (substr($key, 0, 5) == 'MSG_'. $filter && (!array_key_exists($key, $override))) {
					$new_first_char = strtoupper(substr($key, 4, 1));
					if ($new_first_char != $old_first_char) {
						$tmpl = array();
						$old_first_char = $new_first_char;
						$tmpl['LETTER'] = $old_first_char;
						$this->setTemplate('language_item_alpha', $tmpl);
					}
					$tmpl = array();
					$tmpl['TOKEN'] = $key;
					$tmpl['DEFAULT_VALUE'] = htmlspecialchars($value);
					$new_value = $override[$key];
					if (strlen($value) > 40) {
						$tmpl['NEW_VALUE'] = "<textarea class=\"edit\" name=\"$key\">$new_value</textarea>";
					}
					else {
						$tmpl['NEW_VALUE'] = "<input class=\"edit\" name=\"$key\" value=\"$new_value\">";
					}
					$this->setTemplate('language_item', $tmpl);
					$count++;
				}
			}
				}
		if ($filter == 'ALL') {
					$old_first_char = '';
			$count = 0;
			foreach($constants as $key=>$value) {
				if (substr($key, 0, 4) == 'MSG_' && (!array_key_exists($key, $override))) {
					$new_first_char = strtoupper(substr($key, 4, 1));
					if ($new_first_char != $old_first_char) {
						$tmpl = array();
						$old_first_char = $new_first_char;
						$tmpl['LETTER'] = $old_first_char;
						$this->setTemplate('language_item_alpha', $tmpl);
					}
					$tmpl = array();
					$tmpl['TOKEN'] = $key;
					$tmpl['DEFAULT_VALUE'] = htmlspecialchars($value);
					$new_value = $override[$key];
					if (strlen($value) > 40) {
						$tmpl['NEW_VALUE'] = "<textarea class=\"edit\" name=\"$key\">$new_value</textarea>";
					}
					else {
						$tmpl['NEW_VALUE'] = "<input class=\"edit\" name=\"$key\" value=\"$new_value\">";
					}
					$this->setTemplate('language_item', $tmpl);
					$count++;
				}
			}
				}

		$tmpl['LETTERS'] = '[ ' . $filterHeader . ' ]';
		if (!(count($override) == 0 && $filter == 'MOD')) {
			$this->setTemplate('language_footer', $tmpl);
		}

		$this->setHeader(MSG_ADMINISTRATION);
		$this->setModule(MSG_LABEL_EDITOR, $modAction);
		$this->Render();
		}

	function Settings() {
			switch (Request::get('message')) {
			case "1" : $tmpl['message'] = '<font color="red">'.MSG_SETTINGS_SAVED.'</font>';break;
			case "2" : $tmpl['message'] = '<font color="red">'.MSG_PROJECT_ID_MUST_BE_NUMERIC.'</font>';break;
			default : $tmpl['message'] = '';
		}

		$modHeader = MSG_SETTINGS;
		$modAction[] = '<a href="javascript:document.settings.submit();">'.MSG_SAVE.'</a>';

		$this->CreateTabs('settings');

		$tmpl['records_per_page'] = MSG_RECORDS_PER_PAGE;
		$tmpl['currency_symbol'] = MSG_CURRENCY_SYMBOL;
		$tmpl['date_format'] = MSG_FORM_DATE_FORMAT;
		$tmpl['pretty_date_format'] = MSG_VIEW_DATE_FORMAT;
		$tmpl['auto_id'] = MSG_INCREMENT_PROJECT_ID;
		$tmpl['task_log_edit'] = MSG_ALLOW_USERS_EDIT_TASK_LOG;
		$tmpl['users_in_contacts'] = MSG_USERS_IN_CONTACTS;
		$tmpl['disable_nice_dates'] = MSG_DISABLE_NICE_DATES;
		$tmpl['txt_records_per_page'] = Settings::get('RecordsPerPage');
		$tmpl['txt_currency_symbol'] = Settings::get('CurrencySymbol');

		$option_array = array('1' => MSG_DATEFORMAT_YYYY_MM_DD, '2' => MSG_DATEFORMAT_YYYY_DD_MM, '3' => MSG_DATEFORMAT_DD_MM_YYYY, '4' => MSG_DATEFORMAT_MM_DD_YYYY);
		foreach($option_array as $key => $value) {
			$tmpl['txt_date_format'] .= sprintf('<option value="%s" %s>%s</option>', $key, (Settings::get('DateFormat') == $key) ? 'selected' : '', $value);
		}

		$option_array = array('1' => MSG_DATEFORMAT_DD_Mon_YY, '2' => MSG_DATEFORMAT_DD_Mon_YYYY, '3' => MSG_DATEFORMAT_DD_MM_YYYY, '4' => MSG_DATEFORMAT_MM_DD_YYYY, '5' => MSG_DATEFORMAT_YYYY_MM_DD, '6' => MSG_DATEFORMAT_YYYY_DD_MM);
		foreach($option_array as $key => $value) {
			$tmpl['txt_pretty_date_format'] .= sprintf('<option value="%s" %s>%s</option>', $key, (Settings::get('PrettyDateFormat') == $key) ? 'selected' : '', $value);
		}

		$tmpl['txt_auto_id'] = (Settings::get('AutoID') == '1') ? 'checked' : '';
		$tmpl['txt_task_log_edit'] = (Settings::get('TaskLogEdit') == '1') ? 'checked' : '';
		$tmpl['txt_users_in_contacts'] = (Settings::get('UsersInContacts') == '1') ? 'checked' : '';
		$tmpl['txt_disable_nice_dates'] = (Settings::get('DisableNiceDates') == '1') ? 'checked' : '';
		$tmpl['txt_show_dependent_tasks'] = (Settings::get('ShowDependentTasks') == '1') ? 'checked' : '';
		$tmpl['txt_convert_to_days'] = (Settings::get('ConvertToDays') == '1') ? 'checked' : '';
		$tmpl['txt_id_value'] = Settings::get('IDStartValue');
		$before_task = Settings::get('DaysBeforeTaskDue');
		$after_task = Settings::get('DaysAfterTaskDue');

		$filter_array = array('1','2','3','4','5','6','7');

		for ($i = 0, $filteroptions = null, $filtercount = count($filter_array); $i < $filtercount; $i++) {
			$filteroptions .= sprintf('<option value="%s" %s>%s</option>', $filter_array[$i], ($before_task == $filter_array[$i]) ? 'SELECTED' : '', $filter_array[$i]);
		}

		$tmpl['before_task'] = $filteroptions;

		for ($i = 0, $filteroptions = null, $filtercount = count($filter_array); $i < $filtercount; $i++) {
			$filteroptions .= sprintf('<option value="%s" %s>%s</option>', $filter_array[$i], ($after_task == $filter_array[$i]) ? 'SELECTED' : '', $filter_array[$i]);
		}

		$tmpl['after_task'] = $filteroptions;
		$tmpl['email_updates'] = (Settings::get('EmailOnUpdate') == '1') ? 'checked' : '';
		$tmpl['cc_alerts'] = (Settings::get('CCTaskOwner') == '1') ? 'checked' : '';

		$tmpl['terms'] = Settings::get('Terms');
		$tmpl['txt_hourly_rate'] = Settings::get('HourlyRate');
		$tmpl['txt_tax_rate'] = Settings::get('TaxRate');
		// Default language settings

		$languages = array(
			'en' => MSG_ENGLISH, 
			'de' => MSG_GERMAN, 
			'fr' => MSG_FRENCH, 
			'nl' => MSG_DUTCH, 
			'es' => MSG_SPANISH, 
			'it' => MSG_ITALIAN,
			'pt' => MSG_PORTUGUESE,
		); 
		asort($languages);
		$select = '';
		foreach ($languages as $code => $language)
		{
			$selected = ($code == Settings::get('DefaultLanguage')) ? ' selected' : '';
			$select .= "<option value=\"$code\"$selected>$language</option>";
		}
		$tmpl['DEFAULT_LANGUAGE_SELECT'] = $select;

		$tmpl['startofweek'] = MSG_START_OF_WEEK;
		if (Settings::get('WeekStart') == 'Sunday')
		{
			$tmpl['weekStartOptions'] = '<option value="Sunday" selected>'.MSG_SUNDAY.'</option>';
			$tmpl['weekStartOptions'] .= '<option value="Monday">'.MSG_MONDAY.'</option>';
		}
		else
		{
			$tmpl['weekStartOptions'] = '<option value="Sunday">'.MSG_SUNDAY.'</option>';
			$tmpl['weekStartOptions'] .= '<option value="Monday" selected>'.MSG_MONDAY.'</option>';

		}

		//Time zones
		$zones = $this->DB->Query(SQL_SELECT_TIME_ZONES);

		if ($zones) 
		{
			foreach ($zones as $key => $value) 
			{
				$tmpl['txt_time_zone'] .= sprintf('<option value="%s" %s>%s</option>', 
																					$value['ID'], 
																					(Settings::get('TimeZone') == $value['ID']) ? 'SELECTED' : '', $value['Zone']
																	);
			}
		}

		$resourceManagementArray = array('Simple','Advanced');
		for ($i = 0; $i < 2; $i++) 
		{
			$tmpl['resourceManagementOptions'] .= sprintf('<option value="%s" %s>%s</option>', 
																										$i,
																										($i == Settings::get('ResourceManagement')) ? 'selected' : '', $resourceManagementArray[$i]
																						);
		}
		

		$tmpl['colour'] = Settings::get('HeaderBackgroundColour');

		$this->setTemplate('settings', $tmpl);

		$this->setHeader(MSG_ADMINISTRATION);
		$this->setModule($modHeader, $modAction);

		$this->Render();
	}

	function SaveSettings() {
		$message = 1;
		$settings_array['RecordsPerPage'] = $this->DB->Prepare(Request::post('records_per_page'));
		$settings_array['CurrencySymbol'] = $this->DB->Prepare(Request::post('currency_symbol'));
		$settings_array['DateFormat'] = $this->DB->Prepare(Request::post('date_format'));
		$settings_array['PrettyDateFormat'] = $this->DB->Prepare(Request::post('pretty_date_format'));
		$settings_array['TaskLogEdit'] = (strlen(Request::post('task_log_edit')) > 0) ? 1 : 0;
		$settings_array['CCTaskOwner'] = (strlen(Request::post('cc_alerts')) > 0) ? 1 : 0;
		$settings_array['EmailOnUpdate'] = (strlen(Request::post('email_updates')) > 0) ? 1 : 0;
		$settings_array['ShowDependentTasks'] = (strlen(Request::post('show_dependent_tasks')) > 0) ? 1 : 0;
		$settings_array['UsersInContacts'] = (strlen(Request::post('users_in_contacts')) > 0) ? 1 : 0;
		$settings_array['DisableNiceDates'] = (strlen(Request::post('disable_nice_dates')) > 0) ? 1 : 0;
		$settings_array['ConvertToDays'] = (strlen(Request::post('convert_to_days')) > 0) ? 1 : 0;
		$settings_array['DaysBeforeTaskDue'] = Request::post('days_before', Request::R_INT);
		$settings_array['DaysAfterTaskDue'] = Request::post('days_after', Request::R_INT);
		$settings_array['AutoID'] = 0;
		$settings_array['DefaultLanguage'] = $this->DB->Prepare(Request::post('default_language'));
		$settings_array['Terms'] = $this->DB->Prepare(Request::post('terms'));
		$settings_array['HourlyRate'] = $this->DB->Prepare(Request::post('hourly_rate'));
		$settings_array['TaxRate'] = $this->DB->Prepare(Request::post('tax_rate'));
		$settings_array['TimeZone'] = $this->DB->Prepare(Request::post('time_zone'));
		$settings_array['WeekStart'] = $this->DB->Prepare(Request::post('weekstart'));
		$settings_array['ResourceManagement'] = Request::post('resource_management');

		if (strlen(Request::post('auto_id')) > 0) {
			$settings_array['AutoID'] = 1;

			if (is_numeric(Request::post('id_value'))) {
				$settings_array['IDStartValue'] = $this->DB->Prepare(Request::post('id_value'));
			}
			else {
				$message = 2;
			}
		}

		foreach ($settings_array as $key => $value) {
			Settings::set($key, $value);
		}
		
		Response::redirect('index.php?module=administration&action=settings&message='.$message);
	}

	function GroupModulesDel() {
		$id	 = Request::get('id', Request::R_INT);
		$sid = Request::get('sid', Request::R_INT);
		if ( (is_numeric($id)) && (is_numeric($sid))) {
				$SQL = sprintf(SQL_GROUP_PERMISSION_DELETE, $id, $sid);
				if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE)) {
					$this->DB->Execute($SQL);
				}
		}
		Response::redirect('index.php?module=administration&action=groupview&id='.$id);
		}

	function UserModulesDel() {
		$id	 = Request::get('id', Request::R_INT);
		$sid = Request::get('sid', Request::R_INT);
		if ( (is_numeric($id)) && (is_numeric($sid)) ) {
				$SQL = sprintf(SQL_USER_PERMISSION_DELETE, $id, $sid);
				if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE)) {
					$this->DB->Execute($SQL);
				}
		}
		Response::redirect('index.php?module=administration&action=useredit&id='.$id);
		}

	function GroupModulesAdd() {
		$id	 = Request::post('id', Request::R_INT);
		$sid	= Request::post('sid', Request::R_INT);
		$access = Request::post('accessid', Request::R_INT);
		$object = Request::post('objectid');
		if ((is_numeric($id)) && ($object != NULL)) {
			if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE)) {
				// Delete the perm if its already there.
				if ($sid > 0) 
					$this->DB->Execute(sprintf(SQL_GROUP_PERMISSION_DELETE, $id, $sid));

				$SQL = sprintf(SQL_GROUP_PERMISSION_ADD, $id, $object,'-1' , $access);
				$this->DB->Execute($SQL);
			}
		}
		Response::redirect('index.php?module=administration&action=groupview&id='.$id);
	}

	function GroupPermissionDel() {
		$id	 = Request::get('id', Request::R_INT);
		$sid	= Request::get('sid', Request::R_INT);
		$object = Request::get('object');
		if ((is_numeric($id)) &&
			(is_numeric($sid)) &&
			($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE))) {
			if ($object == 'clients') {
				$sql = sprintf(SQL_GET_CLIENT_ID_GROUP, $sid);
				$clientID = $this->DB->ExecuteScalar($sql);
				$sql = sprintf(SQL_GET_PROJECT_IDS_GROUP, 'projects', $id, $clientID);
				$projectIDs = $this->DB->Query($sql);
				//for each project id,
				if (is_array($projectIDs)) {
					foreach($projectIDs as $key => $value) {
						$sql = sprintf(SQL_GROUP_PERMISSION_INDIVIDUAL_DELETE, $id, $value[0], 'projects');
						$this->DB->Execute($sql);
					}
				}
			}
			$SQL = sprintf(SQL_GROUP_PERMISSION_DELETE, $id, $sid);
			$this->DB->Execute($SQL);
		}
		Response::redirect('index.php?module=administration&action=groupview&id='.$id);
		}

	// okay this is a very poorly named bastard. It's not just add, but edit as well. 
	// and it's for both clients _and_ projects, but not at the same time.
	function GroupPermissionAdd() 
	{
		// first, lets have some useful variable names.
		$group_id = Request::post('id');
		$access_ids = Request::post('accessid', Request::R_ARRAY);
		$new_items = Request::post('itemid', Request::R_ARRAY);
		// now we do all the updates as well. 
		
		if (array_key_exists('clients', $access_ids))
		{
			foreach($access_ids['clients'] as $client_id => $permission)
			{
				if ($permission == '')
				{
					continue; // this should be handled by the javascript hijacking the select input.
				}

				if ($client_id == 'new') {
					//override witih the proper client id, as passed in by itemid, which actually represents the new select box.
					$client_id = $new_items['clients'];
				}

				// first, add the appropriate permissions to the client, for the group
				$client = new Client($client_id);
				$client->set_group_permissions($group_id, $permission);

				// now set the same permissions on all the projects owned by that client.
				// do this next.
				foreach($client->projects as $project)
				{
					$project->set_group_permissions($group_id, $permission);
				}
			}
		}

		if (array_key_exists('projects', $access_ids))
		{
			foreach($access_ids['projects'] as $project_id => $permission)
			{
				if ($permission == '')
				{
					continue; // this should be handled by the javascript hijacking the select input.
				}
			
				// skip as it's handled above.
				if ($project_id == 'new') {
					//override witih the proper client id, as passed in by itemid, which actually represents the new select box.
					$project_id = $new_items['projects'];
				}
			
				// first, add the appropriate permissions to the project, for the group
				$project = new Project($project_id);
				$project->set_group_permissions($group_id, $permission);
			}
		}

		Response::redirect('index.php?module=administration&action=groupview&id='.$group_id);
	}

	function UserModulesAdd() {
		$id	 = Request::post('id');
		$access = Request::post('accessid');
		$object = Request::post('objectid');
		if ((is_numeric($id)) && ($object != NULL)) {
			$sql = sprintf(SQL_USER_PERMISSION_EXISTS, $id, $object, '-1');
			$rows = $this->DB->Query($sql);
			if ( is_array( $rows ) && count( $rows ) == 1 )
				$SQL = sprintf(SQL_USER_PERMISSION_UPDATE, $id, $object, '-1', $access);
			else
				$SQL = sprintf(SQL_USER_PERMISSION_ADD, $id, $object,'-1' , $access);
			if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE)) {
				$this->DB->Execute($SQL);
			}
		}
		Response::redirect('index.php?module=administration&action=useredit&id='.$id);
		}

	function UserPermissionAdd() {
		$id	 = Request::post('id');
		$itemid = Request::post('itemid');
		$access = Request::post('accessid');
		$object = Request::post('objectid');

		if (($object != NULL) && (is_numeric($id)) && (is_numeric($itemid)) &&
			($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE))) {

			if ($object == 'clients') {
				$sql = sprintf(SQL_GET_CLIENT_PROJECTS,$itemid);
				$projectIDs = $this->DB->Query($sql);
				//for each project id,
				if (is_array($projectIDs)) {
					foreach($projectIDs as $key => $value) {
						//if entry in permissions,
						$sql = sprintf(SQL_USER_PERMISSION_EXISTS, $id, 'projects', $value[0]);
						if ($this->DB->Query($sql)){
						//set access = 0
							$SQL = sprintf(SQL_USER_PERMISSION_UPDATE, $id, 'projects', $value[0], $access);
							$this->DB->Execute($SQL);
						}
						else {
						// add entry with access = 0
							$SQL = sprintf(SQL_USER_PERMISSION_ADD, $id, 'projects', $value[0], $access);
							$this->DB->Execute($SQL);
						}
					}
				}
			}

			$sql = sprintf(SQL_USER_PERMISSION_EXISTS, $id, $object, $itemid);
			$rows = $this->DB->Query($sql);
			if ( is_array( $rows ) && count( $rows ) == 1 )
				$SQL = sprintf(SQL_USER_PERMISSION_UPDATE, $id, $object, $itemid, $access);
			else
				$SQL = sprintf(SQL_USER_PERMISSION_ADD, $id, $object, $itemid, $access);

			$this->DB->Execute($SQL);
		}
		Response::redirect('index.php?module=administration&action=useredit&id='.$id);
		}

	function UserPermissionDel($id = NULL, $sid = NULL, $object = NULL) {
			if ( $id == NULL )
			$id	= Request::get('id', Request::R_INT);
		if ( $sid == NULL )
			$sid   = Request::get('sid', Request::R_INT);
		if ( $object == NULL )
		   $object = Request::get('object');

		if ((is_numeric($id)) && ($id != 1) && (is_numeric($sid)) &&
			($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE))) {
			if ($object == 'clients') {
				$sql = sprintf(SQL_GET_CLIENT_ID, $sid);
				$clientID = $this->DB->ExecuteScalar($sql);
				$sql = sprintf(SQL_GET_PROJECT_IDS, 'projects', $id, $clientID);
				$projectIDs = $this->DB->Query($sql);
				//for each project id,
				if (is_array($projectIDs)) {
					foreach($projectIDs as $key => $value) {
						$sql = sprintf(SQL_USER_PERMISSION_INDIVIDUAL_DELETE, $id, $value[0], 'projects');
						$this->DB->Execute($sql);
					}
				}
			}
			$SQL = sprintf(SQL_USER_PERMISSION_DELETE, $id, $sid);
			$this->DB->Execute($SQL);
		}
		Response::redirect('index.php?module=administration&action=useredit&id='.$id);
		}

	function GroupNew() {
			if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE)) {
			$this->DisplayGroupForm();
		}
		else {
			$this->ThrowError(2001);
		}
		}

	function GroupEdit() {
		$id = Request::get('id', Request::R_INT);
		if (is_numeric($id)) {
			if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE)) {
				$this->DisplayGroupForm($id);
			}
			else {
				$this->ThrowError(2001);
			}
		}
		else {
			Response::redirect('index.php?module=administration&action=groups');
		}
		}

	function DisplayGroupForm($id = 0) 
	{
		$isEditMode = (Request::get('action') == 'ajaxgroupview') ? FALSE : TRUE;
		$hasAdminWrite = $this->User->HasModuleItemAccess('administration', CU_ACCESS_ALL, CU_ACCESS_WRITE);
		$this->CreateTabs('groups');
		$this->setTemplate('groups_main_header');
		$tmpl['txtID'] = $id; // this is the group id.
		$tmpl['lblGroupDetail'] = MSG_GROUP_DETAILS;
		$tmpl['lblGroupName'] = MSG_GROUP_NAME;
		$modTitle = MSG_ADMINISTRATION;
		$modAction[] = '<a href="javascript:SubmitForm();">'.MSG_SAVE.'</a>';
		if ($id == 0) {
			$modHeader = MSG_NEW_GROUP;
			$tmpl['txtGroupName'] = '';
		}
		else {
			$SQL = sprintf(SQL_GET_GROUP, $id);
			$RS =& new DBRecordset();
			$RS->Open($SQL, $this->DB);
			if (!$RS->EOF()) {
				$tmpl['txtGroupName'] = htmlspecialchars($RS->Field('Name'));
				$modHeader = $tmpl['txtGroupName'] . ' ' .MSG_EDIT;
			}
			$RS->Close();
			unset($RS);
		}

		if ($hasAdminWrite)
		{
			if ($isEditMode)
				$actions[] = array('url' => '#', 'name' => MSG_SAVE, 'attrs' => "onclick=\"saveGroup($id); return false;\"");
			else
				$actions[] = array('url' => '#', 'name' => MSG_EDIT, 'attrs' => "onclick=\"editGroup($id); return false;\"");

			//$actions[] = array('url' => '#', 'name' => MSG_COPY, 'attrs' => "onclick=\"copyGroup($id); return false;\"");
			if (Request::get('action') != 'ajaxgroupnew') {
				$actions[] = array('url' => url::build_url('administration', 'groupdel', "id=$id"), 'name' => MSG_DELETE,
					'confirm' => 1, 'title' => MSG_CONFIRM_GROUP_DELETE_TITLE, 'body' => MSG_CONFIRM_GROUP_DELETE_BODY);
			}
		}

		$minuslist = array(0);
		$tmpl['userlist'] = '';
		$template = ($isEditMode) ? 'group_member_item_edit' : 'group_member_item_view';
		$assigned = null;
		$assigned_sql = sprintf(SQL_LIST_USERS_GROUP, $id);
		$assigned_list = $this->DB->Query($assigned_sql);
		if ( is_array($assigned_list) ) {
			$assigned_count = count($assigned_list);
			for ($i = 0; $i < $assigned_count; $i++) {
				$assigned_list[$i]['GroupID'] = $id;
				$tmpl['userlist'] .= $this->getTemplate($template, $assigned_list[$i]);
				$minuslist[] = $assigned_list[$i]['ID'];
			}
		}

		$users = '';
		$users_sql = sprintf(SQL_LIST_USERS_MINUS, '1,'.implode(',', $minuslist), 'FullName', 'ASC');
		$users_list = $this->DB->Query($users_sql);
		if ( is_array($users_list) ) {
			$users_count = count($users_list);
			for ($i = 0; $i < $users_count; $i++) {
				$users .= sprintf('<option value="%s"%s>%s</option>', $users_list[$i]['ID'], ($users_list[$i]['ID'] == $this->User->ID ? ' selected' : ''), $users_list[$i]['FullName']);
			}
		}

		$tmpl['selectUsers'] = $users;
		$tmpl['selectAssigned'] = $assigned;

		$tmpl['perms'] = NULL;
		$tmpl['modules'] = NULL;
		$tmpl['clients'] = NULL;
		$tmpl['projects'] = NULL;
		if ($id > 0) {

				$RS =& new DBRecordset();
				$SQL1 = sprintf(SQL_GET_GROUP_PERMISSIONS_CLIENT, $id);
				$SQL2 = sprintf(SQL_GET_GROUP_PERMISSIONS_PROJECT, $id);
				$SQL_MODULES = sprintf(SQL_GET_GROUP_MODULES_LIST, $id);
				$sql = sprintf(SQL_GET_GROUP_ACCESS, $id, 'clients');
				$client_access_list = $this->DB->Query($sql);
				$sql = sprintf(SQL_GET_GROUP_ACCESS, $id, 'projects');
				$project_access_list = $this->DB->Query($sql);

				if($project_access_list) {
					foreach($project_access_list as $key => $value) {
						$return[] = $value[0];
					}
					$project_list = join(',', $return);
				}
				else {
					$project_list = '-1';
				}
				unset($return);

				if ($client_access_list) {
					foreach($client_access_list as $key => $value) {
						$return[] = $value[0];
					}
					$client_list = join(',', $return);
				}
				else {
					$client_list = '-1';
				}
				$SQL3 = sprintf(SQL_GET_CLIENTS_LIST,$id);
				$SQL4 = sprintf(SQL_GET_PROJECTS_LIST,$id);
				$SQL_MODULE_LIST = sprintf(SQL_GET_GROUP_MODULES,$id);
				$moduleList = $this->DB->Query($SQL_MODULE_LIST);
				$clientItemList = $this->DB->Query($SQL3);
				$projectItemList = $this->DB->Query($SQL4);

				// Generic access list drop down at the top of menu items/clients/projects sections.
				$tmpl_accesslist = '<option value="">'.MSG_SELECT.'...</option>';
				$tmpl_accesslist_rw = '<option value="">'.MSG_SELECT.'...</option>';
				foreach ($this->AccessList as $k => $v ) { 
					$tmpl_accesslist .= '<option value="' . $k . '">' . $v . '</option>';
					$tmpl_accesslist_rw .= ( $k == 0 ) ? '' : '<option value="' . $k . '">' . $v . '</option>';
				}

				$RS->Open($SQL_MODULE_LIST, $this->DB);
				if (!$RS->EOF()) {
					while (!$RS->EOF()) {
						$item_tmpl['CATEGORY'] = MSG_AREAS;
						$item_tmpl['ITEM'] = constant(MSG_.strtoupper(str_replace(' ', '_', $RS->Field('Name'))));  // Translate the module name.
						$item_tmpl['OBJECTID'] = $RS->Field('Class');
						$item_tmpl['GROUPID'] = $id;
						$item_tmpl['SID'] = $RS->Field('ID');
						$item_tmpl['ACCESS'] = $this->AccessList[(int)$RS->Field('AccessID')];
						$sel = ( $RS->Field('AccessID') == '' ) ? ' selected' : '';
						$item_tmpl['ACCESSLIST'] = '<option value=""'.$sel.'>'.MSG_SELECT.'...</option>';
						foreach ($this->AccessList as $k => $v ) {
							$sel = ( $sel == '' && (int)$RS->Field('AccessID') == $k ) ? ' selected' : '';
							$item_tmpl['ACCESSLIST'] .= '<option value="'.$k.'"'.$sel.'>'.$v.'</option>';
						}
						$sel == '';
						$template = ($isEditMode) ? 'group_mod_item_edit' : 'group_mod_item_view';
						$tmpl['modules'] .= $this->getTemplate($template, $item_tmpl);
						unset($item_tmpl);
						$RS->MoveNext();
					}
					$RS->Close();
				}
				else {
					$item_tmpl['CATEGORY'] = MSG_AREAS;
					$item_tmpl['message'] = MSG_NONE;
					$tmpl['modules'] .= $this->getTemplate('group_perm_empty', $item_tmpl);
					unset($item_tmpl);
				}
				$counter = 1;
				$RS->Open($SQL1, $this->DB);
				if (!$RS->EOF()) {
					while (!$RS->EOF()) {
						$item_tmpl['CATEGORY'] = MSG_CLIENTS;
						$item_tmpl['ITEM'] = $RS->Field('Name');
						$item_tmpl['ITEMID'] = $RS->Field('ItemID');
						$item_tmpl['GROUPID'] = $id;
						$item_tmpl['SID'] = $RS->Field('ID');
						$item_tmpl['OBJECT'] = 'clients';
						$item_tmpl['ACCESS'] = $this->AccessList[(int)$RS->Field('AccessID')];
						$item_tmpl['ACCESSID'] = $RS->Field('AccessID');
						$item_tmpl['FUNC'] = 'changeClientPerm';
						
						// hack alert. so. the js here is all completely fucked. So we only allow to change to deny, and they can re-add to the other permission
						// changing to the other permission directly just fux things. 
						// however, we do want to preserve the currently selected item.

						// old code
						// $item_tmpl['ACCESSLIST'] = '<option value="">'.MSG_SELECT.'...</option>';
						// foreach ($this->AccessList as $k => $v ) {
						// 	$sel = ( $RS->Field('AccessID') == $k ) ? ' selected' : '';
						// 	$item_tmpl['ACCESSLIST'] .= '<option value="'.$k.'"'.$sel.'>'.$v.'</option>';
						// }

						// we have to preserve the 'select' option, as the js requires it. GAY
						$item_tmpl['ACCESSLIST'] = '<option> ' .MSG_SELECT . '...</option>';
						$item_tmpl['ACCESSLIST'] .= '<option value="0"> ' .MSG_DENY . '</option>';
						$item_tmpl['ACCESSLIST'] .= '<option selected value="' . $RS->Field('AccessID') . '">' . $this->AccessList[$RS->Field('AccessID')]. '</option>';


						$template = ($isEditMode) ? 'group_perm_item_edit' : 'group_perm_item_view';
						$tmpl['clients'] .= $this->getTemplate($template, $item_tmpl);
						unset($item_tmpl);
						++$counter;
						$RS->MoveNext();
					}
					$RS->Close();
				}
				else {
					$item_tmpl['CATEGORY'] = MSG_CLIENTS;
					$item_tmpl['message'] = MSG_NONE;
					$tmpl['clients'] .= $this->getTemplate('group_perm_empty', $item_tmpl);
					unset($item_tmpl);
				}
				$counter = 1;
				$RS->Open($SQL2, $this->DB);
				if (!$RS->EOF()) {
					while (!$RS->EOF()) {
						if ( $counter > 1 ) $items .= $this->getTemplate('group_perm_spacer');
						$item_tmpl['CATEGORY'] = MSG_PROJECTS;
						$item_tmpl['ITEM'] = $RS->Field('Name');
						$item_tmpl['ITEMID'] = $RS->Field('ItemID');
						$item_tmpl['GROUPID'] = $id;
						$item_tmpl['SID'] = $RS->Field('ID');
						$item_tmpl['OBJECT'] = 'projects';
						$item_tmpl['ACCESS'] = $this->AccessList[(int)$RS->Field('AccessID')];
						$item_tmpl['FUNC'] = 'changeProjectPerm';
						
						// hack alert. so. the js here is all completely fucked. So we only allow to change to deny, and they can re-add to the other permission
						// changing to the other permission directly just fux things. 
						// however, we do want to preserve the currently selected item.

						// old code
						// $item_tmpl['ACCESSLIST'] = '<option value="">'.MSG_SELECT.'...</option>';
						// foreach ($this->AccessList as $k => $v ) {
						// 	$sel = ( $RS->Field('AccessID') == $k ) ? ' selected' : '';
						// 	$item_tmpl['ACCESSLIST'] .= '<option value="'.$k.'"'.$sel.'>'.$v.'</option>';
						// }

						// we have to preserve the 'select' option, as the js requires it. GAY
						$item_tmpl['ACCESSLIST'] = '<option> ' .MSG_SELECT . '...</option>';
						$item_tmpl['ACCESSLIST'] .= '<option value="0"> ' .MSG_DENY . '</option>';
						$item_tmpl['ACCESSLIST'] .= '<option selected value="' . $RS->Field('AccessID') . '">' . $this->AccessList[$RS->Field('AccessID')]. '</option>';


						$template = ($isEditMode) ? 'group_perm_item_edit' : 'group_perm_item_view';
						$tmpl['projects'] .= $this->getTemplate($template, $item_tmpl);
						unset($item_tmpl);
						++$counter;
						$RS->MoveNext();
					}
					$RS->Close();
				}
				else {
					$item_tmpl['CATEGORY'] = MSG_PROJECTS;
					$item_tmpl['message'] = MSG_NONE;
					$tmpl['projects'] .= $this->getTemplate('group_perm_empty', $item_tmpl);
					unset($item_tmpl);
				}
				// create list of modules. If Permissions/Module is set. Create a list of Items for the module
				$tmpl_accesslist = null;
				$tmpl_itemlist = '';
				for ($i = 0, $count = count($clientItemList); $i < $count; $i++) {
					if ($clientItemList[$i][2] != $clientItemList[$i][0]) {
						$tmpl_itemlist .= sprintf('<option value="%1$s">%2$s</option>',
							$clientItemList[$i][0], $clientItemList[$i][1]);
					}
				}
				for ($i = 1, $count = count($this->AccessList); $i < $count; $i++) {
					$selected = '';
					$tmpl_accesslist .= '<option value="' . $i . '" ' . $selected .'>' . $this->AccessList[$i] . '</option>';
				}
				$tmpl['CLIENTITEMLIST'] = $tmpl_itemlist;
				$tmpl['CLIENTACTION'] = 'addclient';
				$tmpl['PROJECTACTION'] = 'addproj';
				$tmpl['MODULEACTION'] = 'groupmodadd';
				$tmpl['ACCESSLIST'] = $tmpl_accesslist;

				$tmpl_itemlist = '';
				for ($i = 0, $count = count($projectItemList); $i < $count; $i++) {
					$extra = '';
					if ( isset($projectItemList[$i][3]) && ($projectItemList[$i][0] != $projectItemList[$i][1])) {
						$extra = $projectItemList[$i][3] . ' / ';
						$tmpl_itemlist .= sprintf('<option value="%1$s">%3$s%2$s</option>',
							$projectItemList[$i][0], $projectItemList[$i][2], $extra);
					}
				}

				$tmpl['PROJECTITEMLIST'] = $tmpl_itemlist;
				$tmpl_accesslist = null;
				$tmpl_itemlist = '';
				for ($i = 0, $count = count($moduleList); $i < $count; $i++) {
					if (!(isset($moduleList[$i][2]))) {
						$tmpl_itemlist .= sprintf('<option value="%1$s">%2$s</option>', $moduleList[$i][0], $moduleList[$i][1]);
					}
				}

				for ($i = 0, $count = count($this->AccessList); $i < $count; $i++) {
					$selected = '';
					if ( $i == 1 ) {
						$selected = 'SELECTED';
					}
					$tmpl_accesslist .= '<option value="' . $i . '" ' . $selected .'>' . $this->AccessList[$i] . '</option>';
				}

				$tmpl['MODULELIST'] = $tmpl_itemlist;
				$tmpl['MODULEACCESSLIST'] = $tmpl_accesslist;
				$tmpl['ITEMS'] = $items;
				$tmpl['perms'] = $this->getTemplate('groups_perms',$perms_tmpl);
			}
		$tmpl['actions'] = $this->ActionMenu($actions);

		switch (Request::get('action'))
		{
			case 'ajaxgroupview': $template = 'group_view'; break;
			case 'ajaxgroupedit': $template = 'group_edit'; break;
			case 'ajaxgroupnew': $template = 'group_new'; break;
			default: $template = 'group_view';
		}
		$this->setHeader($modTitle);
		$this->setModule($modHeader, $modAction);
		$html = $this->getTemplate($template, $tmpl);
		if (Request::get('action') != 'ajaxgroupnew')
			$html .= $this->getTemplate('groups_main_footer');
		echo $html;
		}

	function GroupSave() {
	
		$id		= Request::post('id');
		$groupname  = $this->DB->Prepare(Request::post('groupname'));
		$superuser  = $this->DB->Prepare(Request::post('superuser'));

		$assign = 0;
		if ( strlen(Request::post('hassign')) > 0 ) {
			$assigned = explode(',', Request::post('hassign'));
			$assign = 1;
		}
		if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE)) {
			if ($id == 0) {
				$redirect = 1;
				// INSERT record
				$SQL = sprintf(SQL_CREATE_GROUP, $groupname);
				$this->DB->Execute($SQL);
				$id = $this->DB->ExecuteScalar(SQL_LAST_INSERT);
				$SQL = sprintf(SQL_CREATE_GROUP_PERMISSIONS, $id, 'administration', '0');
				$this->DB->Execute($SQL);
				$SQL = sprintf(SQL_CREATE_GROUP_PERMISSIONS, $id, 'budget', '2');
				$this->DB->Execute($SQL);
				$SQL = sprintf(SQL_CREATE_GROUP_PERMISSIONS, $id, 'clients', '2');
				$this->DB->Execute($SQL);
				$SQL = sprintf(SQL_CREATE_GROUP_PERMISSIONS, $id, 'springboard', '2');
				$this->DB->Execute($SQL);
				$SQL = sprintf(SQL_CREATE_GROUP_PERMISSIONS, $id, 'projects', '2');
				$this->DB->Execute($SQL);
				$SQL = sprintf(SQL_CREATE_GROUP_PERMISSIONS, $id, 'files', '2');
				$this->DB->Execute($SQL);
				$SQL = sprintf(SQL_CREATE_GROUP_PERMISSIONS, $id, 'contacts', '2');
				$this->DB->Execute($SQL);
				$SQL = sprintf(SQL_CREATE_GROUP_PERMISSIONS, $id, 'calendar', '2');
				$this->DB->Execute($SQL);
				$SQL = sprintf(SQL_CREATE_GROUP_PERMISSIONS, $id, 'updates', '0');
				$this->DB->Execute($SQL);
			}
			else {	
				// UPDATE Record
				$SQL = sprintf(SQL_UPDATE_GROUP, $groupname, $id);
				$this->DB->Execute($SQL);
				$redirect = 1;
			}

			if ($assign) {
				$SQL = sprintf(SQL_USERS_CLEAR_REMOVED, $id, join(",", $assigned));
				$this->DB->Execute($SQL);
				$recs = count($assigned);
				for ($i = 0; $i < $recs; $i++) {
					$SQL = sprintf(SQL_USERS_CHECK, $assigned[$i], $id);
					if (!$this->DB->Exists($SQL))  {
						// user isn't still in the assigned list. add them, with a notified flag of 0
						$SQL = sprintf(SQL_USERS_ASSIGN, $id, $assigned[$i]);
						$this->DB->Execute($SQL);
					}
				}
			}
			else {
				// clear all the assigned users - Not needed for Copper 4
				//$SQL = sprintf(SQL_USERS_CLEAR_ASSIGNED, $id);
				//$this->DB->Execute($SQL);
			}
		} // end if
		else {
			// the user doesn't have access to eitehr insert new, or update existing.
			$this->ThrowError(2001);
		}

		if ($redirect == 1)
			Response::redirect('index.php?module=administration&action=groupview&id='.$id);
		else
			Response::redirect('index.php?module=administration&action=groups');
		}

	function AjaxGroupMember() {
			$groupID = Request::get('groupid', Request::R_INT);
		$userID = Request::get('userid', Request::R_INT);
		$direction = (Request::get('direction') == 'remove') ? 'remove' : 'add';

		if ($this->User->IsAdmin)
		{
			$userRow = $this->DB->QuerySingle(sprintf(SQL_GET_USER, $userID));
			$userGroupRow = $this->DB->QuerySingle(sprintf(SQL_USER_GROUP_CHECK, $userID, $groupID));
			if (is_array($userRow))
			{
				if ($direction == 'add') // Add a user to the group
				{
					// If user is already in the group, do nothing.
					if (is_array($userGroupRow) && intval($userGroupRow['ID']) > 0) 
						echo '';
					else
					{
						$this->DB->Execute(sprintf(SQL_USERS_ASSIGN, $groupID, $userID));
						$userRow['GroupID'] = $groupID;
						$html = $this->getTemplate('group_member_item_edit', $userRow);
						echo $html;
					}
				}
				if ($direction == 'remove') // Remove a user from the group
				{
					// If user is in the group, remove them.
					if (is_array($userGroupRow) && intval($userGroupRow['ID']) > 0) 
					{
						$this->DB->Execute(sprintf(SQL_USERS_REMOVE, $groupID, $userID));
						echo '{success:1,name:"'.$userRow['FirstName'].' '.$userRow['LastName'].'"}';
					}
					else
						echo '{success:0}';
				}
			}

			//Response::redirect('index.php?module=administration&action=groupview&id='.$id);
		}
		
		}

	function GroupDel() {
			$id = Request::get('id', Request::R_INT);
		$tmpl['MESSAGE'] = MSG_USER_NOT_FOUND;
		$template = 'message';
		$title = MSG_ADMINISTRATION;
		$breadcrumbs = MSG_DELETE;

		if (is_numeric($id)) {
			if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL , CU_ACCESS_WRITE)) {
				$confirm = Request::get('confirm');
				if ($confirm == 1) {
					$SQL1 = sprintf(SQL_DELETE_GROUP, $id);
					$SQL2 = sprintf(SQL_DELETE_GROUP_PERMISSIONS, $id);
					$SQL3 = sprintf(SQL_USERS_CLEAR_ASSIGNED, $id);
					$this->DB->Execute($SQL1);
					$this->DB->Execute($SQL2);
					$this->DB->Execute($SQL3);
					Response::redirect('index.php?module=administration&action=groups');
				}
				else {
					$SQL = sprintf(SQL_GET_GROUP, $id);
					$rs  = $this->DB->QuerySingle($SQL);
					if (is_array($rs)) {
						$tmpl['ID']	  = $id;
						$tmpl['MESSAGE'] = sprintf(MSG_DELETE_GROUP_WARNING, $rs['Name']);
						$tmpl['YES']	 = MSG_YES;
						$tmpl['NO']	  = MSG_NO;
						$template		= 'delete_group';
					}
				}
			}
			else {
				$this->ThrowError(2001);
			}
		}

		$this->setHeader($title);
		$this->setModule($breadcrumbs);
		$this->setTemplate($template, $tmpl);

		$this->Render();

		}

	function Groups() {
			$modHeader = MSG_GROUPS . ' ' . MSG_LIST;
		if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE)) 
			$modAction[] = '<a href="#" onclick="newGroup(); return false;">' . MSG_NEW_GROUP . '</a>';

		$this->CreateTabs('groups');

		// paging code
		$limit = Settings::get('RecordsPerPage');
		$offset = Request::get('start');
		$SQL = sprintf(SQL_LIST_GROUPS, 'Name', $orderdir);
		if ($offset == 'all') {
			$RS =& new DBRecordset();
			$RS->Open($SQL, $this->DB);
		}
		else {
			if (!is_numeric($offset)) { $offset = 0; }
			$RS =& new DBPagedRecordset();
			$RS->Open($SQL, $this->DB, $limit, $offset);
		}
		//~ paging code

		if (!$RS->EOF()) {
			$tmpl['HEADERGROUPNAME'] = MSG_GROUP;
			$this->setTemplate('groups_header', $tmpl);
			unset($tmpl);
			while (!$RS->EOF()) {
				$tmpl['GROUPID']   = $RS->Field('ID');
				$tmpl['GROUPNAME'] = $RS->Field('Name');
				$this->setTemplate('groups_item', $tmpl);
				unset($tmpl);
				$RS->MoveNext();
			}


			if ($RS->TotalRecords > $limit) {
				$url = 'index.php?module=administration&action=groups&amp;order='.$order.'&amp;direction='.$direction;
				cuPaginate($RS->TotalRecords, $limit, $url, $offset, $tmpl);
				$this->setTemplate('paging', $tmpl);
				unset($tmpl);
			}

			// Emulate the group view screen.
			$tmpl['script'] = ''; 
			if (Request::get('action') == 'groupview' || Request::get('action') == 'groupedit')
			{
				$tempTmpl['txtGroupID'] = Request::get('id', Request::R_INT); 
				if ($tempTmpl['txtGroupID'] > 0)
					$tmpl['script'] = $this->getTemplate('group_view_script', $tempTmpl);
			}

			$this->setTemplate('groups_footer', $tmpl);
		}
		else {
			$tmpl['script'] = NULL;
			$tmpl['txtMessage'] = MSG_NO_GROUPS_AVAILABLE;
			$this->setTemplate('nogroup', $tmpl);
		}

		$RS->Close();
		unset($RS);
		$this->setHeader(MSG_ADMINISTRATION);
		$this->setModule($modHeader, $modAction);
		$this->Render();
		}

	function UserList() {
			$modHeader = MSG_USERS. ' ' . MSG_LIST;
		if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE))
			$modAction[] = '<a href="#" onclick="newUser(); return false;">'.MSG_NEW_USER.'</a>';
		$this->CreateTabs('users');
		//ordering
		switch (Request::get('order')) {
			case 'fullname': $order = 'fullname'; $orderby = 'FullName'; break;
			case 'email'   : $order = 'email'; $orderby = 'EmailAddress'; break;
			case 'active'  : $order = 'active'; $orderby = 'Active'; break;
//change_log 2.
			case 'groups'  : $order = 'groups'; $orderby = 'Groups'; break;
			default		: $order = 'username'; $orderby = 'Username';
		}

		switch (Request::get('direction')) {
			case 'down': $direction = 'down'; $orderdir = 'DESC'; break;
			default	: $direction = 'up'; $orderdir = 'ASC';
		}
		// end ordering

		// paging code
		$limit = Settings::get('RecordsPerPage');
		$offset = Request::get('start');
		$SQL = sprintf(SQL_LIST_USERS, $orderby, $orderdir);
		if ($offset == 'all') {
			$RS =& new DBRecordset();
			$RS->Open($SQL, $this->DB);
		}
		else {
			if (!is_numeric($offset)) { $offset = 0; }
			$RS =& new DBPagedRecordset();
			$RS->Open($SQL, $this->DB, $limit, $offset);
		}
		//~ paging code

		if (!$RS->EOF()) {
			$tmpl['HEADERUSERNAME'] = MSG_USERNAME;
			$tmpl['HEADERFULLNAME'] = MSG_FULL_NAME;
			$tmpl['HEADEREMAIL']	= MSG_EMAIL_ADDRESS;
			$tmpl['HEADERACTIVE']   = MSG_ACTIVE;
			$tmpl['HEADERACTION']   = MSG_ACTION;
//change_log 2.
			$tmpl['HEADERGROUPS']   = MSG_GROUPS;
			$tmpl['SORTASC']		= MSG_ASCENDING;
			$tmpl['SORTDESC']	   = MSG_DESCENDING;
			$this->setTemplate('list_header', $tmpl);
			unset($tmpl);

			$counter = 1;
			while (!$RS->EOF()) {
				$tmpl['lblEdit']  = MSG_EDIT;
//change_log 3.
				$tmpl['lblCopy']  = MSG_COPY;
			/*  if ($RS->Field('ID') == 1) {
					$tmpl['lblAccess'] = '';
				}
				else {
					$tmpl['lblAccess'] =  '| <a class="linkon" href="index.php?module=administration&action=userperm&id={USERID}">'.MSG_PERMISSIONS.'</a>';
				}
			*/
				$tmpl['lblDelete']  = MSG_DELETE;
				$tmpl['USERID']   = $RS->Field('ID');
				$tmpl['USERNAME'] = $RS->Field('Username');
				$tmpl['FULLNAME'] = $RS->Field('FullName');
				$tmpl['EMAIL']	= $RS->Field('EmailAddress');
				$tmpl['TITLE']	= $RS->Field('Title');
//change_log 2.
				$tmpl['GROUPS'] = NULL;
				$groups = $this->DB->Query(sprintf(SQL_USER_GROUPS, $RS->Field('ID')));
				if ((is_array($groups)) && (count($groups) > 0)) {
					foreach ($groups as $key => $value) {
						$tmpl['GROUPS'] .= $value['Name'].', ';
					}
					$tmpl['GROUPS'] = substr($tmpl['GROUPS'],0,-2);
				}
				$tmpl['ACTIVE']   = ($RS->Field('Active') == 1) ? MSG_YES : MSG_NO ;
				//only the Administrator can edit the Administrator
				if($RS->Field('ID')==1){
					if($this->User->ID==1){
						$this->setTemplate('list_item', $tmpl);
					} 
				} else {
					$this->setTemplate('list_item', $tmpl);
				}
				unset($tmpl);

				++$counter;
				$RS->MoveNext();
			}

			if ($RS->TotalRecords > $limit) {
				$url = 'index.php?module=administration&action=users&amp;order='.$order.'&amp;direction='.$direction;
				cuPaginate($RS->TotalRecords, $limit, $url, $offset, $tmpl);
				$this->setTemplate('paging', $tmpl);
				unset($tmpl);
			}
			else
				$this->setTemplate('nopaging');

			$SQL = sprintf(SQL_LIST_USERS, 'UserName', 'ASC');
			$result = $this->DB->Query($SQL);

			$count = 0;
			foreach ($result as $key => $value) {
				$userlist .= '"'.$value['Username'].'": '.$count.',';
				$count++;
			}

			$userlist = substr($userlist, 0, -1);
			$tmpl['Userlist'] = $userlist;
			$this->setTemplate('list_footer', $tmpl);
		}
		else {
			$this->setTemplate('message', array('MESSAGE' => MSG_EOF_ALL));
		}

		// Emulate the task view screen.
		$action = Request::get('action');
		if ($action == 'userview' || $action == 'useredit')
		{
			$tempTmpl['txtUserID'] = Request::get('id', Request::R_INT);
			$tempTmpl['txtAction'] = "ajax$action";
			if ($tempTmpl['txtUserID'] > 0)
				$this->setTemplate('user_view_script', $tempTmpl);
		}
		

		$RS->Close();
		unset($RS);
		$this->setHeader(MSG_ADMINISTRATION);
		$this->setModule($modHeader, $modAction);
		$this->Render();
		}

	function checkUserLimit()
	{
		$maxUsers = $this->GetVal(explode('_', $this->get_license_token()));
		$numUsers = $this->DB->ExecuteScalar(SQL_COUNT_ACTIVE_USERS);
		if ($numUsers == $maxUsers)
		{
			return self::USERS_AT_LIMIT;
		} else if ($numUsers < $maxUsers) {
			return self::USERS_BELOW_LIMIT;
		} else {
			return self::USERS_ABOVE_LIMIT;
		}
		
	}
	
	// require that there are still users available in the account.
	// return false if no more users are available.
	function requireUsersAvailable($ajax = true)
	{
		$limit = $this->checkUserLimit();
		if ($limit != self::USERS_BELOW_LIMIT)
		{
			$tmpl['MESSAGE'] = sprintf(MSG_USER_LIMIT_REACHED, $this->GetVal(explode('_', $this->get_license_token())));
			$tmpl['RETURN'] = MSG_RETURN_TO_USER_LIST;

			if ($ajax) {
				echo $this->getTemplate('user_limit_reached', $tmpl);

			} else {
				$this->setHeader(MSG_NEW_USER);
				$this->setModule(MSG_ADMINISTRATION);
				$this->setTemplate('user_limit_reached', $tmpl);
				$this->Render();

			}

			return false;
		} else {
			return true;
		}
		
	}

	function UserNew() {
		if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE)) {
			if ($this->requireUsersAvailable())
			{
				$this->DisplayUserForm();
			}
		} else {
			$this->ThrowError(2001);
		}
	}

	function UserEdit() {
			$id = Request::get('id', Request::R_INT);
		if (is_numeric($id)) {
			if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE)) {
				$this->DisplayUserForm($id);
			}
			else {
				$this->ThrowError(2001);
			}
		}
		else {
			Response::redirect('index.php?module=administration&action=users');
		}
		}

	function DisplayUserForm($id = 0) {
			$this->CreateTabs('users');
		$SQL = sprintf(SQL_LIST_USERS_MINUS, $id, 'UserName', 'ASC');
		$result = $this->DB->Query($SQL);

		$count = 0;
		foreach ($result as $key => $value) {
			$userlist .= '"'.$value['Username'].'": '.$count.',';
			$count++;
		}

		$userlist = substr($userlist, 0, -1);
		$tmpl['Userlist'] = $userlist;

		$tmpl['txtID'] = $id;
		$tmpl['lblDetails'] = MSG_PERSONAL_DETAILS;
		$tmpl['lblUsername'] = MSG_USERNAME;

		//change_log 1.
		$tmpl['lblTitle']   = MSG_TITLE;
		$tmpl['lblCostRate']   = MSG_COST_RATE;
		$tmpl['lblCostRateUnits']   = MSG_COST_RATE_UNITS;
		$tmpl['lblChargeRate']   = MSG_CHARGE_RATE;
		$tmpl['lblChargeRateUnits']   = MSG_CHARGE_RATE_UNITS;

		$tmpl['lblFirstName'] = MSG_FIRSTNAME;
		$tmpl['lblLastName'] = MSG_LAST_NAME;
		$tmpl['lblPassword'] = MSG_PASSWORD;
		$tmpl['lblPassConf'] = MSG_PASSWORD_CONFIRM;
		$tmpl['lblAddress'] = MSG_ADDRESS_DETAILS;
		$tmpl['lblAddress1'] = MSG_ADDRESS_1;
		$tmpl['lblAddress2'] = MSG_ADDRESS_2;
		$tmpl['lblCity'] = MSG_CITY;
		$tmpl['lblState'] = MSG_STATE;
		$tmpl['lblCountry'] = MSG_COUNTRY;
		$tmpl['lblPostcode'] = MSG_POSTCODE;
		$tmpl['lblContact'] = MSG_CONTACT_DETAILS;
		$tmpl['lblPhone1'] = MSG_PHONE_1;
		$tmpl['lblPhone2'] = MSG_PHONE_2;
		$tmpl['lblPhone3'] = MSG_PHONE_3;
		$tmpl['lblIsResource'] = MSG_IS_RESOURCE;
		$tmpl['lblEmail'] = MSG_EMAIL_ADDRESS;
		$tmpl['lblUserMessage'] = '';
		$tmpl['lblIMType'] = MSG_IM_TYPE;
		$tmpl['lblIMAccount'] = MSG_IM_ACCOUNT;
		$tmpl['txtUserError'] = MSG_ERR_USERNAME;
		$tmpl['txtFNameError'] = MSG_ENTER_FIRST_NAME;
		$tmpl['txtLNameError'] = MSG_ENTER_LAST_NAME;
		$tmpl['txtPassError'] = MSG_PASSWORDS_NOT_MATCH;
		$tmpl['txtCostRateError'] = MSG_ERR_COST_RATE;
		$tmpl['txtChargeRateError'] = MSG_ERR_CHARGE_RATE;

		$tmpl['actions'] = '';
		$hasAdminWrite = $this->User->HasModuleItemAccess('administration', CU_ACCESS_ALL, CU_ACCESS_WRITE);
		$isEditMode = (Request::get('action') == 'ajaxuserview') ? FALSE : TRUE;

		//prevent modification of the Administrator user for the moment.
		
		if($id!=1) 
		{
			if ($hasAdminWrite)
			{
				if ($isEditMode) // ie new or copied
				{
				  if (Request::get('action') == 'ajaxusercopy')
				  {
					  $actions[] = array('url' => '#', 'name' => MSG_SAVE, 'attrs' => "onclick=\"saveUser($id, true); return false;\"");
					  $actions[] = array('url' => '#', 'name' => MSG_CANCEL, 'attrs' => "onclick=\"$(this).up('.expanded').fade({duration: 0.5, afterFinish:function() { $(this).up('.expanded').remove(); }.bind(this) }); return false;\"");
				  } else {
					  $actions[] = array('url' => '#', 'name' => MSG_SAVE, 'attrs' => "onclick=\"saveUser($id); return false;\"");
						$onclick = "toggleUser($('user-opener-$id'), $id); return false;";
					  $actions[] = array('url' => '#', 'name' => MSG_CANCEL, 'attrs' => 'onclick="' . $onclick . '"');
				  }

				}
				else {
					$actions[] = array('url' => '#', 'name' => MSG_EDIT, 'attrs' => "onclick=\"editUser($id); return false;\"");
					$actions[] = array('url' => '#', 'name' => MSG_COPY, 'attrs' => "onclick=\"copyUser($id); return false;\"");
					$actions[] = array('url' => url::build_url('administration', 'userdel', "id=$id"), 'name' => MSG_DELETE,
							'confirm' => 1, 'title' => MSG_CONFIRM_USER_DELETE_TITLE, 'body' => MSG_CONFIRM_USER_DELETE_BODY);
				}

			}
		}

		if ($id == 0) {
			$modTitle = MSG_ADMINISTRATION;
			$modHeader = MSG_NEW_USER;

//change_log 3.
			$tmpl['Copy'] = '';
			$tmpl['txtUsername'] = '';
//change_log 1.
			$tmpl['txtTitle'] = '';
			$tmpl['txtCostRate'] = '';
			$tmpl['txtChargeRate'] = '';
			$tmpl['txtFirstName'] = '';
			$tmpl['txtLastName'] = '';
			$tmpl['txtAddress1'] = '';
			$tmpl['txtAddress2'] = '';
			$tmpl['txtCity'] = '';
			$tmpl['txtState'] = '';
			$tmpl['txtCountry'] = '';
			$tmpl['txtPostcode'] = '';
			$tmpl['txtPhone1'] = '';
			$tmpl['txtPhone2'] = '';
			$tmpl['txtPhone3'] = '';
			$tmpl['txtIsResource'] = 'checked';
			$tmpl['txtEmailNotification'] = 'checked';
			$tmpl['txtEmail'] = '';
			$tmpl['txtIMAccount'] = '';
			$tmpl['imOptions'] = '';
			foreach ($this->imTypes as $v)
				$tmpl['imOptions'] .= '<option>'.$v.'</option>';
	  $tmpl['availabilityOptions'] = '';
	  foreach($this->availabilityOptions as $k => $v)
				$tmpl['availabilityOptions'] .= '<option value="'.$k.'">'.$v.'</option>';
		
			$tmpl['txtCurrencySymbol'] = Settings::get('CurrencySymbol');
		for ($i = 0; $i <= MAX_DAY_LENGTH; $i++) {
			$tmpl['mondayOptions'] .= '<option value="' . $i . '"';
			if ($i == 8)  $tmpl['mondayOptions'] .= ' selected';
			$tmpl['mondayOptions'] .= '>' . $i . 'hrs</option>';
		}
		$tmpl['tuesdayOptions'] = $tmpl['wednesdayOptions'] = $tmpl['thursdayOptions'] = $tmpl['fridayOptions'] = $tmpl['saturdayOptions'] = $tmpl['sundayOptions'] = $tmpl['mondayOptions'];
	  
		}
		else {
		// edit user
			$action = Request::get('action');
			$tmpl['Copy'] = ($action == "ajaxusercopy") ? '<input type="hidden" name="copy" value="1">' : '';
			$header = ($action == 'copy') ? MSG_COPY : MSG_EDIT;

			$modTitle = MSG_ADMINISTRATION;
			$modHeader = $header;

			$SQL = sprintf(SQL_GET_USER, $id);
			$RS =& new DBRecordset();
			$RS->Open($SQL, $this->DB);

			if (!$RS->EOF()) {
				$tmpl['txtUsername'] = ($action == "ajaxusercopy") ? '' : htmlspecialchars($RS->Field('Username'));
				$modHeader = $tmpl['txtUsername'] . ' ' .$header;
				//change_log 1.
				$tmpl['txtTitle'] = htmlspecialchars($RS->Field('Title'));
				$tmpl['txtCostRate'] = htmlspecialchars($RS->Field('CostRate'));
				$tmpl['txtChargeRate'] = htmlspecialchars($RS->Field('ChargeRate'));
				$tmpl['txtFirstName'] = htmlspecialchars($RS->Field('FirstName'));
				$tmpl['txtLastName'] = htmlspecialchars($RS->Field('LastName'));
				$tmpl['txtAddress1'] = htmlspecialchars($RS->Field('Address1'));
				$tmpl['txtAddress2'] = htmlspecialchars($RS->Field('Address2'));
				$tmpl['txtCity'] = htmlspecialchars($RS->Field('City'));
				$tmpl['txtState'] = htmlspecialchars($RS->Field('State'));
				$tmpl['txtCountry'] = htmlspecialchars($RS->Field('Country'));
				$tmpl['txtPostcode'] = htmlspecialchars($RS->Field('Postcode'));
				$tmpl['txtPhone1'] = htmlspecialchars($RS->Field('Phone1'));
				$tmpl['txtPhone2'] = htmlspecialchars($RS->Field('Phone2'));
				$tmpl['txtPhone3'] = htmlspecialchars($RS->Field('Phone3'));
				$term = (Request::get('action') == 'ajaxuserview') ? MSG_YES : 'checked';
				$tmpl['txtIsResource'] = $RS->Field('IsResource') == 0 ? "" : "$term";
				$tmpl['txtEmailNotification'] = $RS->Field('EmailNotify') == 0 ? "" : "$term";
				$tmpl['txtEmail'] = htmlspecialchars($RS->Field('EmailAddress'));
				$tmpl['Userlist'] = '';

				$tmpl['txtIMAccount'] = '';
				if (strlen($RS->Field('IMAccount')) > 0)
				{
					if ($isEditMode)
						$tmpl['txtIMAccount'] = $RS->Field('IMAccount');
					else
						$tmpl['txtIMAccount'] = $RS->Field('IMType').": ".$RS->Field('IMAccount');
				}
				$tmpl['imOptions'] = '';
				foreach ($this->imTypes as $v)
				{
					$selected = ($v == $RS->Field('IMType')) ? 'selected' : '';
					$tmpl['imOptions'] .= '<option '.$selected.'>'.$v.'</option>'; 
				}

		$tmpl['txtAvailabilityType'] = $this->availabilityOptions[$RS->Field('AvailabilityType')];
		$tmpl['availabilityOptions'] = '';
		foreach($this->availabilityOptions as $k => $v) {
					$selected = ($k == $RS->Field('AvailabilityType')) ? 'selected' : '';
				  $tmpl['availabilityOptions'] .= '<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
		}
		$tmpl['togglescript'] = NULL;
		if ($RS->Field('AvailabilityType') == 1)
		  $tmpl['togglescript'] = "<script>$('days').toggle();</script>";

		$weekdays = @split("\|",$RS->Field('WeekDays'));
		if ($weekdays[0] == '')
		  $weekdays = array(8,8,8,8,8,8,8);
		$tmpl['txtMonday'] = $weekdays[0];
		$tmpl['txtTuesday'] = $weekdays[1];
		$tmpl['txtWednesday'] = $weekdays[2];
		$tmpl['txtThursday'] = $weekdays[3];
		$tmpl['txtFriday'] = $weekdays[4];
		$tmpl['txtSaturday'] = $weekdays[5];
		$tmpl['txtSunday'] = $weekdays[6];
		for ($i = 0; $i <= MAX_DAY_LENGTH; $i++) {
			$tmpl['mondayOptions'] .= '<option value="' . $i . '"';
			if ($i == $weekdays[0])  $tmpl['mondayOptions'] .= ' selected';
			$tmpl['mondayOptions'] .= '>' . $i . 'hrs</option>';
		}
		for ($i = 0; $i <= MAX_DAY_LENGTH; $i++) {
			$tmpl['tuesdayOptions'] .= '<option value="' . $i . '"';
			if ($i == $weekdays[1])  $tmpl['tuesdayOptions'] .= ' selected';
			$tmpl['tuesdayOptions'] .= '>' . $i . 'hrs</option>';
		}
		for ($i = 0; $i <= MAX_DAY_LENGTH; $i++) {
			$tmpl['wednesdayOptions'] .= '<option value="' . $i . '"';
			if ($i == $weekdays[2])  $tmpl['wednesdayOptions'] .= ' selected';
			$tmpl['wednesdayOptions'] .= '>' . $i . 'hrs</option>';
		}
		for ($i = 0; $i <= MAX_DAY_LENGTH; $i++) {
			$tmpl['thursdayOptions'] .= '<option value="' . $i . '"';
			if ($i == $weekdays[3])  $tmpl['thursdayOptions'] .= ' selected';
			$tmpl['thursdayOptions'] .= '>' . $i . 'hrs</option>';
		}
		for ($i = 0; $i <= MAX_DAY_LENGTH; $i++) {
			$tmpl['fridayOptions'] .= '<option value="' . $i . '"';
			if ($i == $weekdays[4])  $tmpl['fridayOptions'] .= ' selected';
			$tmpl['fridayOptions'] .= '>' . $i . 'hrs</option>';
		}
		for ($i = 0; $i <= MAX_DAY_LENGTH; $i++) {
			$tmpl['saturdayOptions'] .= '<option value="' . $i . '"';
			if ($i == $weekdays[5])  $tmpl['saturdayOptions'] .= ' selected';
			$tmpl['saturdayOptions'] .= '>' . $i . 'hrs</option>';
		}
		for ($i = 0; $i <= MAX_DAY_LENGTH; $i++) {
			$tmpl['sundayOptions'] .= '<option value="' . $i . '"';
			if ($i == $weekdays[6])  $tmpl['sundayOptions'] .= ' selected';
			$tmpl['sundayOptions'] .= '>' . $i . 'hrs</option>';
		}
		
				$tmpl['txtCurrencySymbol'] = Settings::get('CurrencySymbol');
			}
			$RS->Close();
			unset($RS);
		}
		$tmpl['perms'] = NULL;
		$tmpl['modules'] = NULL;
		$tmpl['clients'] = NULL;
		$tmpl['projects'] = NULL;
		if ($id > 0) {
				$SQL1 = sprintf(SQL_GET_USER_PERMISSIONS_CLIENT, $id);
				$SQL2 = sprintf(SQL_GET_USER_PERMISSIONS_PROJECT, $id);
				$SQL_MODULES = sprintf(SQL_GET_USER_MODULES_LIST, $id);
				$SQL3 = sprintf(SQL_GET_USER_CLIENTS_LIST,$id);
				$SQL4 = sprintf(SQL_GET_USER_PROJECTS_LIST,$id);
				$SQL_MODULE_LIST = sprintf(SQL_GET_USER_MODULES, $id);
				$moduleList = $this->DB->Query($SQL_MODULE_LIST);
				$clientItemList = $this->DB->Query($SQL3);
				$projectItemList = $this->DB->Query($SQL4);
				$counter = 1;

				// Generic access list drop down at the top of menu items/clients/projects sections.
				$tmpl_accesslist = '<option value="">'.MSG_SELECT.'...</option>';
				$tmpl_accesslist_rw = '<option value="">'.MSG_SELECT.'...</option>';
				foreach ($this->AccessList as $k => $v ) { 
					$tmpl_accesslist .= '<option value="' . $k . '">' . $v . '</option>';
					$tmpl_accesslist_rw .= ( $k == 0 ) ? '' : '<option value="' . $k . '">' . $v . '</option>';
				}

				// Create list of all modules and the access to them that this user has. 
				$RS =& new DBRecordset();
				$RS->Open($SQL_MODULE_LIST, $this->DB);
				if (!$RS->EOF()) {
					while (!$RS->EOF()) {
						$item_tmpl['CATEGORY'] = MSG_AREAS;
						$item_tmpl['ITEM'] = constant(MSG_.strtoupper(str_replace(' ', '_', $RS->Field('Name'))));  // Translate the module name.
						$item_tmpl['OBJECTID'] = $RS->Field('Class');
						$item_tmpl['USERID'] = $id;
						$item_tmpl['SID'] = $RS->Field('ID');
						$item_tmpl['ACCESS'] = $this->AccessList[(int)$RS->Field('AccessID')];
						$sel = ( $RS->Field('AccessID') == '' ) ? ' selected' : '';
						$item_tmpl['ACCESSLIST'] = '<option value=""'.$sel.'>'.MSG_SELECT.'...</option>';
						foreach ($this->AccessList as $k => $v ) {
							$sel = ( $sel == '' && $RS->Field('AccessID') == $k ) ? ' selected' : '';
							$item_tmpl['ACCESSLIST'] .= '<option value="'.$k.'"'.$sel.'>'.$v.'</option>';
						}
						$sel == '';
						$template = (Request::get('action') == 'ajaxuseredit') ? 'user_mod_item' : 'user_mod_item_view';
						$tmpl['modules'] .= $this->getTemplate($template, $item_tmpl);
						unset($item_tmpl);
						++$counter;
						$RS->MoveNext();
					}
					$RS->Close();
				}
				else {
					$item_tmpl['CATEGORY'] = MSG_AREAS;
					$item_tmpl['message'] = MSG_NONE;
					$items .= $this->getTemplate('user_perm_empty', $item_tmpl);
					unset($item_tmpl);
				}
				$items .= $this->getTemplate('keyline');

				// Create list of clients the user has access to. 
								$tmpl_itemlist = '';
				for ($i = 0, $count = count($clientItemList); $i < $count; $i++) {
					if ($clientItemList[$i][2] != $clientItemList[$i][0]) 
						$tmpl_itemlist .= sprintf('<option value="%1$s">%2$s</option>', $clientItemList[$i][0], $clientItemList[$i][1]);
				}

				if (Request::get('action') != 'ajaxuserview')
				{
					$perms_tmpl['USERID'] = $id;
					$perms_tmpl['CLIENTITEMLIST'] = $tmpl_itemlist;
					$perms_tmpl['CLIENTACTION'] = 'useraddclient';
					$perms_tmpl['ACCESSLIST'] = $tmpl_accesslist_rw;
					$tmpl['clients'] .= $this->getTemplate('perm_client_header', $perms_tmpl);
				}

				$counter = 1;
				$RS->Open($SQL1, $this->DB);
				if (!$RS->EOF()) {
					while (!$RS->EOF()) {
						$item_tmpl['CATEGORY'] = MSG_CLIENTS;
						$item_tmpl['ITEM'] = $RS->Field('Name');
						$item_tmpl['ITEMID'] = $RS->Field('ItemID');
						$item_tmpl['USERID'] = $id;
						$item_tmpl['SID'] = $RS->Field('ID');
						$item_tmpl['OBJECT'] = 'clients';
						$item_tmpl['ACCESS'] = $this->AccessList[(int)$RS->Field('AccessID')];
						$item_tmpl['ACCESSID'] = $RS->Field('AccessID');
						$item_tmpl['FUNC'] = 'changeClientPerm';

						// hack alert. so. the js here is all completely fucked. So we only allow to change to deny, and they can re-add to the other permission
						// changing to the other permission directly just fux things. 
						// however, we do want to preserve the currently selected item.

						// old code
						// foreach ($this->AccessList as $k => $v ) {
						// 	$sel = ( $RS->Field('AccessID') == $k ) ? ' selected' : '';
						// 	$item_tmpl['ACCESSLIST'] .= '<option value="'.$k.'"'.$sel.'>'.$v.'</option>';
						// }

						// we have to preserve the 'select' option, as the js requires it. GAY
						$item_tmpl['ACCESSLIST'] = '<option> ' .MSG_SELECT . '...</option>';
						$item_tmpl['ACCESSLIST'] .= '<option value="0"> ' .MSG_DENY . '</option>';
						$item_tmpl['ACCESSLIST'] .= '<option selected value="' . $RS->Field('AccessID') . '">' . $this->AccessList[$RS->Field('AccessID')]. '</option>';

						$template = (Request::get('action') == 'ajaxuseredit') ? 'user_perm_item' : 'user_perm_item_view';
						$tmpl['clients'] .= $this->getTemplate($template, $item_tmpl);
						unset($item_tmpl);
						++$counter;
						$RS->MoveNext();
					}
					$RS->Close();
				}
				else {
					$item_tmpl['CATEGORY'] = MSG_CLIENTS;
					$item_tmpl['message'] = MSG_NONE;
					$tmpl['clients'] .= $this->getTemplate('user_perm_empty', $item_tmpl);
					unset($item_tmpl);
				}
				
				$items .= $this->getTemplate('keyline');
				
				// Create list of projects the user has access to. 
								$tmpl_itemlist = '';
				for ($i = 0, $count = count($projectItemList); $i < $count; $i++) {
					$extra = '';
					if ( isset($projectItemList[$i][3]) && ($projectItemList[$i][0] != $projectItemList[$i][1])) {
						$extra = $projectItemList[$i][3] . ' / ';
						$tmpl_itemlist .= sprintf('<option value="%1$s">%3$s%2$s</option>', $projectItemList[$i][0], $projectItemList[$i][2], $extra);
					}
				}

				if (Request::get('action') != 'ajaxuserview')
				{
					$perms_tmpl['USERID'] = $id;
					$perms_tmpl['PROJECTITEMLIST'] = $tmpl_itemlist;
					$perms_tmpl['PROJECTACTION'] = 'useraddproj';
					$perms_tmpl['ACCESSLIST'] = $tmpl_accesslist_rw;
					$tmpl['projects'] .= $this->getTemplate('perm_project_header', $perms_tmpl);
				}

				$counter = 1;
				$RS->Open($SQL2, $this->DB);
				if (!$RS->EOF()) {
					while (!$RS->EOF()) {
						$item_tmpl['CATEGORY'] = MSG_PROJECTS;
						$item_tmpl['ITEM'] = $RS->Field('Name');
						$item_tmpl['ITEMID'] = $RS->Field('ItemID');
						$item_tmpl['USERID'] = $id;
						$item_tmpl['SID'] = $RS->Field('ID');
						$item_tmpl['OBJECT'] = 'projects';
						$item_tmpl['ACCESS'] = $this->AccessList[$RS->Field('AccessID')];
						$item_tmpl['FUNC'] = 'changeProjectPerm';

						// hack alert. so. the js here is all completely fucked. So we only allow to change to deny, and they can re-add to the other permission
						// changing to the other permission directly just fux things. 
						// however, we do want to preserve the currently selected item.

						// old code
						// $item_tmpl['ACCESSLIST'] = '<option value="">'.MSG_SELECT.'...</option>';
						// foreach ($this->AccessList as $k => $v ) {
						// 	$sel = ( $RS->Field('AccessID') == $k ) ? ' selected' : '';
						// 	$item_tmpl['ACCESSLIST'] .= '<option value="'.$k.'"'.$sel.'>'.$v.'</option>';
						// }

						// we have to preserve the 'select' option, as the js requires it. GAY
						$item_tmpl['ACCESSLIST'] = '<option> ' .MSG_SELECT . '...</option>';
						$item_tmpl['ACCESSLIST'] .= '<option value="0"> ' .MSG_DENY . '</option>';
						$item_tmpl['ACCESSLIST'] .= '<option selected value="' . $RS->Field('AccessID') . '">' . $this->AccessList[$RS->Field('AccessID')]. '</option>';

						$template = (Request::get('action') == 'ajaxuseredit') ? 'user_perm_item' : 'user_perm_item_view';
						$tmpl['projects'] .= $this->getTemplate($template, $item_tmpl);
						unset($item_tmpl);
						++$counter;
						$RS->MoveNext();
					}
					$RS->Close();
				}
				else {
					$item_tmpl['CATEGORY'] = MSG_PROJECTS;
					$item_tmpl['message'] = MSG_NONE;
					$tmpl['projects'] .= $this->getTemplate('user_perm_empty', $item_tmpl);
					unset($item_tmpl);
				}
				
				$perms_tmpl['ITEMS'] = $items;
			$tmpl['perms'] = $this->getTemplate('user_perms', $perms_tmpl);
		}

		$tmpl['actions'] = $this->ActionMenu($actions);

		switch (Request::get('action'))
		{
			case 'ajaxuserview': $template = 'user_view'; break;
			case 'ajaxuseredit': $template = 'user_edit'; break;
			case 'ajaxusernew':  $template = 'user_new'; break;
			case 'ajaxusercopy': $template = 'user_new'; break;
			default: $template = 'user_view';
		}

		$this->setHeader($modTitle);
		$this->setModule($modHeader, $modAction);
		$html = $this->getTemplate($template, $tmpl);
		echo $html;
		}

	function UserSave() {
		$caller		= Request::post("caller");
		$copy	   = Request::post('copy');
		$id		 = Request::post('id');
		$username   = $this->DB->Prepare(Request::post('username'));
		$title	  = $this->DB->Prepare(Request::post('title'));
		$costrate   = $this->DB->Prepare(Request::post('costrate'));
		$chargerate = $this->DB->Prepare(Request::post('chargerate'));
		$firstname  = $this->DB->Prepare(Request::post('firstname'));
		$lastname   = $this->DB->Prepare(Request::post('lastname'));
		$pass1	  = $this->DB->Prepare(Request::post('pass1'));
		$pass2	  = $this->DB->Prepare(Request::post('pass2'));
		$address1   = $this->DB->Prepare(Request::post('address1'));
		$address2   = $this->DB->Prepare(Request::post('address2'));
		$country	= $this->DB->Prepare(Request::post('country'));
		$city	   = $this->DB->Prepare(Request::post('city'));
		$state	  = $this->DB->Prepare(Request::post('state'));
		$postcode   = $this->DB->Prepare(Request::post('postcode'));
		$phone1	 = $this->DB->Prepare(Request::post('phone1'));
		$phone2	 = $this->DB->Prepare(Request::post('phone2'));
		$phone3	 = $this->DB->Prepare(Request::post('phone3'));
		$isResource = $this->DB->Prepare(Request::post('isResource') == "1" ? 1 : 0);
		$email	  = $this->DB->Prepare(Request::post('email'));
		$imType	 = $this->DB->Prepare(Request::post('imtype'));
		$imAccount  = $this->DB->Prepare(Request::post('imaccount'));
		$emailNotification = $this->DB->Prepare(Request::post('emailNotification') == "1" ? 1 : 0);

		// javascript provides a better error message, these are just a fallbacks
		if (($costrate != '') && (!is_numeric($costrate))) {
			$this->ThrowError(3001);
			exit;
		}
		if (($chargerate != '') && (!is_numeric($chargerate))) {
			$this->ThrowError(3001);
			exit;
		}

		if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE)) {
			if (Base::asfoieas()) {
				$tmpl['OK'] = MSG_OK;
				if (($id == 0) || ($copy != null)) {
					// INSERT record
					$password = $this->MD5($pass1);
					$SQL = sprintf(SQL_CREATE_USER, $username, $password, $title, $firstname, $lastname, $email,
						$phone1, $phone2, $phone3, $address1, $address2, $city, $state, $postcode, $country, 
						$costrate, $chargerate, $emailNotification, $imType, $imAccount);
					$this->DB->Execute($SQL);
					$lastID = $this->DB->ExecuteScalar(SQL_LAST_INSERT);
					if ($isResource == 1) {
			$availabilitytype = Request::post('availabilitytype');
			$weekdays = NULL;
			if ($availabilitytype == 1) $weekdays = Request::post('monday').'|'.Request::post('tuesday').'|'.Request::post('wednesday').'|'.Request::post('thursday').'|'.Request::post('friday').'|'.Request::post('saturday').'|'.Request::post('sunday');
						$SQL = sprintf(SQL_USER_SETRESOURCE, $lastID, $availabilitytype, $weekdays);
						$this->DB->Execute($SQL);
						$resourceID = $this->DB->ExecuteScalar(SQL_LAST_INSERT);
						//set availability
			$this->ResourceSet($resourceID, $availabilitytype, $weekdays);
						
					}
					$SQL = sprintf(SQL_CREATE_USER_PERMISSIONS, $lastID, 'administration', '0');
					$this->DB->Execute($SQL);
					$SQL = sprintf(SQL_CREATE_USER_PERMISSIONS, $lastID, 'budget', '2');
					$this->DB->Execute($SQL);
					$SQL = sprintf(SQL_CREATE_USER_PERMISSIONS, $lastID, 'clients', '2');
					$this->DB->Execute($SQL);
					$SQL = sprintf(SQL_CREATE_USER_PERMISSIONS, $lastID, 'springboard', '2');
					$this->DB->Execute($SQL);
					$SQL = sprintf(SQL_CREATE_USER_PERMISSIONS, $lastID, 'projects', '2');
					$this->DB->Execute($SQL);
					$SQL = sprintf(SQL_CREATE_USER_PERMISSIONS, $lastID, 'files', '2');
					$this->DB->Execute($SQL);
					$SQL = sprintf(SQL_CREATE_USER_PERMISSIONS, $lastID, 'contacts', '2');
					$this->DB->Execute($SQL);
					$SQL = sprintf(SQL_CREATE_USER_PERMISSIONS, $lastID, 'calendar', '2');
					$this->DB->Execute($SQL);
					$SQL = sprintf(SQL_CREATE_USER_PERMISSIONS, $lastID, 'updates', '0');
					$this->DB->Execute($SQL);

					// Email new user.
					$mailer = new SMTPMail();
					$mailer->FromName = SYS_FROMNAME;
					$mailer->FromAddress = SYS_FROMADDR;
					$mailer->Subject = sprintf(MSG_USER_CREATION_SUBJECT, CU_PRODUCT_NAME);
					$mailer->ToName = $firstname.' '.$lastname;
					$mailer->ToAddress = $email;
					$mailer->Body = sprintf(MSG_USER_CREATION_BODY, $firstname, CU_PRODUCT_NAME, $username, $pass1, url::build_url());
					$mailer->Execute();
					unset($mailer);
				} else {
					if ( strlen($pass1) > 0 ) 
						$password = '\''.$this->MD5($pass1).'\'';
					else 
						$password = SQL_PASS_FIELD;
					
					// UPDATE Record
					$SQL = sprintf(SQL_UPDATE_USER, $username, $password, $title, $firstname, $lastname, $email,
						$phone1, $phone2, $phone3, $address1, $address2, $city, $state, $postcode, $country, $costrate, 
						$chargerate, $emailNotification, $imType, $imAccount, $id);
					$this->DB->Execute($SQL);

					if ($isResource == 1) {
						$resource = $this->DB->QuerySingle(sprintf(SQL_RESOURCE_ID_FOR_USER, $id));

			$availabilitytype = Request::post('availabilitytype');
			$weekdays = '';
			if ($availabilitytype == 1) $weekdays = Request::post('monday').'|'.Request::post('tuesday').'|'.Request::post('wednesday').'|'.Request::post('thursday').'|'.Request::post('friday').'|'.Request::post('saturday').'|'.Request::post('sunday');

						if (!is_array($resource) || count($resource) == 0) {
							$this->DB->Execute(sprintf(SQL_USER_SETRESOURCE, $id, $availabilitytype, $weekdays));
						}
			else {
							$this->DB->Execute(sprintf(SQL_USER_UPDATERESOURCE, $id, $availabilitytype, $weekdays));
			}
			$this->ResourceSet($resource['ID'], $availabilitytype, $weekdays);
					} else {
						// Clear out the HoursAvailable entries since the user is no longer a resource.
						// DO NOT clear out the TaskResourceDay entries since they are used in the Reports module.
						$resource = $this->DB->QuerySingle(sprintf(SQL_RESOURCE_ID_FOR_USER, $id));
						$this->DB->Execute(sprintf(SQL_DELETE_RESOURCE_DAYS, $resource['ID']));	

						// Delete the resource entry.
						$this->DB->Execute(sprintf(SQL_USER_UNSETRESOURCE, $id));
					}
				}
			}
			else {
				$this->ThrowError(777);
			}
		}
		else {
			// the user doesn't have access to either insert new, or update existing.
			$this->ThrowError(2001);
		}
		if($caller=="ajax"){
			echo("{success:1}");
		} else {
			if ($id == 0 || $copy != NULL)
				Response::redirect('index.php?module=administration&action=useredit&id='.$lastID);
			else
				Response::redirect('index.php?module=administration&action=userview&id='.$id);
		}
		}

	function UserDel() {
			$id = Request::get('id', Request::R_INT);
		$tmpl['MESSAGE'] = MSG_USER_NOT_FOUND;
		$template = 'message';
		$title = MSG_ADMINISTRATION;
		$breadcrumbs = MSG_DELETE;

		if (is_numeric($id) && ($id != 1)) {
			if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE)) {
				$confirm = Request::get('confirm');
				if ($confirm == 1) {
					if ( $id != 1 )  {
						$SQL = sprintf(SQL_RESOURCE_ID_FOR_USER,$id);
						$resourceID = $this->DB->QuerySingle($SQL);
						$resourceID = $resourceID['ID'];
						$SQL = sprintf(SQL_DELETE_RESOURCE, $resourceID);
						$this->DB->Execute($SQL);
						$SQL = sprintf(SQL_DELETE_TASK_RESOURCE_DAYS, $resourceID);
						$this->DB->Execute($SQL);
						$SQL = sprintf(SQL_DELETE_RESOURCE_DAYS, $resourceID);
						$this->DB->Execute($SQL);

						$this->DB->Execute(sprintf(SQL_DELETE_USER_GROUPS, $id));
						$this->DB->Execute(sprintf(SQL_DELETE_USER_PERMISSIONS, $id));
						$this->DB->Execute(sprintf(SQL_DELETE_USER, $id));
					}
					Response::redirect('index.php?module=administration&action=users');
				}
				else {
					$SQL = sprintf(SQL_GET_USER, $id);
					$rs  = $this->DB->QuerySingle($SQL);
					if (is_array($rs)) {
						$tmpl['ID']	  = $id;
						$tmpl['MESSAGE'] = sprintf(MSG_DELETE_USER_WARNING, $rs['FirstName'] . ' ' . $rs['LastName']);
						$tmpl['YES']	 = MSG_YES;
						$tmpl['NO']	  = MSG_NO;
						$template		= 'delete';
					}
				}
			}
			else {
				$this->ThrowError(2001);
			}
		}
		$this->setHeader($title);
		$this->setModule($breadcrumbs);
		$this->setTemplate($template, $tmpl);
		$this->Render();
		}

	 function CreateTabs($active) {
			$tmpl['lblUsersTab'] = $this->AddTab(MSG_USERS, 'users', $active);
		$tmpl['lblSettingsTab'] = $this->AddTab(MSG_SETTINGS, 'settings', $active);
		$tmpl['lblGroupsTab'] = $this->AddTab(MSG_GROUPS, 'groups', $active);
		$tmpl['lblLanguageTab'] = $this->AddTab(MSG_LANGUAGE, 'language', $active);
		$tmpl['lblToolsTab'] = $this->AddTab(MSG_BACKUP, 'tools', $active);
		$tmpl['lblAppearanceTab'] = $this->AddTab(MSG_APPEARANCE, 'appearance', $active);
		$this->setTemplate('tabs', $tmpl);
		unset($tmpl);
		}

	function AddTab($name, $action, $active) {
			if ($active == strtolower($action)) {
			$tab = 'tab_active';
		}
		else {
			$tab = 'tab_inactive';
		}
		if (strlen($action) > 0) {
			$query = '&amp;action='.$action;
		}
		return $this->getTemplate($tab, array('lblTabName' => $name, 'lblTabQuery' => $query));
		}

	function ActionMenu($actions) {
			$actionMenu = '';
		if (is_array($actions))
		{
			foreach ($actions as $action)
			{
				if ($action['confirm'] == 1)
				{
					$action['attrs'] = 'class="lbOn" rel="confirmLightBox"';
					if (isset($action['title']))
						$action['attrs'] .= ' msgTitle="'.$action['title'].'"';
					if (isset($action['body']))
						$action['attrs'] .= ' msgBody="'.$action['body'].'"';
				}
				if (!isset($action['attrs']))
					$action['attrs'] = '';
		   
				$template = (empty($actionMenu)) ? 'action_item_first' : 'action_item';
				$actionMenu .= $this->getTemplate($template, $action);
			}

			$actionMenu = $this->getTemplate('action', array('ACTION' => $actionMenu));
		}
		return $actionMenu;
		}

}
 
