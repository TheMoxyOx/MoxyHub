<?php
// $Id$
class mod_contacts extends Module {

	function mod_contacts() {
		$this->ModuleName   = 'contacts';
		$this->RequireLogin = 1;
		$this->Public		= 1;
		parent::Module();
	}

	function main() {
		switch (Request::any('action')) {
			case 'view':		$this->ListContacts(); break;
			case 'new':		 $this->NewContact(); break;
			case 'edit':		$this->EditContact(); break;
			case 'save':		$this->SaveContact(); break;
			case 'delete':	  $this->DeleteContact(); break;
			case 'import':	  $this->Import(); break;
			case 'importvcard': $this->ImportVCard(); break;
			case 'exportvcard': $this->ExportVCard(); break;
			case 'exportcsv':   $this->ExportCSV(); break;
			case 'ajaxview':	$this->AjaxViewContact(); break;
			case 'ajaxedit':	$this->AjaxEditContact(); break;
			case 'ajaxnew':	 $this->AjaxEditContact(); break;
			default:			$this->ListContacts();
		}
	}

	function ExportCSV() {
		$csv = '"ID","ClientName","KeyContact","FirstName","LastName","Notes","Title","EmailAddress1","EmailAddress2","Phone1","Phone2","Phone3","Address", "Address2", "City", "State", "Country", "Zip"'."\r\n";

		$contacts = $this->DB->Query( SQL_GET_CONTACTS_FOR_CSV );
		foreach ( $contacts as $c )
		{
			$csv .= '"'.$c['ID'].'","'.$c['ClientName'].'","'.$c['KeyContact'].'","'.addcslashes($c['FirstName'], '"').'","'.addcslashes($c['LastName'], '"').'","'.addcslashes($c['Notes'], '"').'","'.addcslashes($c['Title'], '"').'","'.$c['EmailAddress1'].'","'.$c['EmailAddress2'].'","'.$c['Phone1'].'","'.$c['Phone2'].'","'.$c['Phone3'].'","'.$c['Address1'].'","'.$c['Address2'].'","'.$c['City'].'","'.$c['State'].'","'.$c['Country'].'","'.$c['Postcode'].'"'."\r\n";
		}

		header( "Content-type: text/plain" );
		header( "Content-disposition: attachment; filename=contacts.csv" );
		echo $csv;
	}

	function ExportVCard() {
		$id = Request::get('id', Request::R_INT);

		$sql = sprintf(SQL_GET_CONTACT, $id);
		$result = $this->DB->QuerySingle($sql);
		$vcard = new Contact_Vcard_Build();

		$name = $result['FirstName'].' '.$result['LastName'];
		$vcard->setFormattedName($name);
		$vcard->setName($result['LastName'], $result['FirstName'], NULL, $result['Title'], NULL);

		$vcard->addOrganization($result['ClientName']);

		$vcard->addEmail($result['EmailAddress1']);
		$vcard->addParam('TYPE', 'WORK');
		$vcard->addParam('TYPE', 'PREF');

		$vcard->addEmail($result['EmailAddress2']);
		$vcard->addParam('TYPE', 'HOME');

		$vcard->addTelephone($result['Phone1']);
		$vcard->addParam('TYPE', 'WORK');
		$vcard->addTelephone($result['Phone2']);
		$vcard->addParam('TYPE', 'HOME');
		$vcard->addTelephone($result['Phone3']);
		$vcard->addParam('TYPE', 'OTHER');

		$text = $vcard->fetch();
		header( "Content-type: text-plain");
		header( "content-disposition: attachment; filename=\"".$name.".vcf\"");
		echo $text;

	}

	function Import() {
		$modTitle = MSG_CONTACTS;
		$modHeader = MSG_IMPORT_VCARD;

		$this->setTemplate('file_upload', $tmpl);

		$this->setHeader($modTitle);
		$this->setModule($modHeader);
		$this->Render();
	}

	function ImportVCard() {
		set_time_limit(600);
		ignore_user_abort(1);

		$file = Request::files('file', Request::R_ARRAY);
		$file_tmp  = $file['tmp_name'];
		$file_name = $file['name'];
		$file_type = $file['type'];
		$file_size = $file['size'];
		
		if ($file_size > 0) {
			$parse = new Contact_Vcard_Parse();
			$data = $parse->fromFile($file_tmp);
			foreach ($data as $key => $vcard) {
				$fn	 = $vcard['N'][0]['value'][1][0];
				$ln	 = $vcard['N'][0]['value'][0][0];
				$title  = $vcard['N'][0]['value'][3][0];

				if (($fn == NULL) && ($ln == NULL))
					$fn = $vcard['FN'][0]['value'][0][0];

				$client = $vcard['ORG'][0]['value'][0][0];
				if ($client)
					$client_id = $this->DB->ExecuteScalar(sprintf(SQL_MATCH_CLIENT, $client));
				 $email1  = $vcard['EMAIL'][0]['value'][0][0];
				$email2  = $vcard['EMAIL'][1]['value'][0][0];
				$phone1  = $vcard['TEL'][0]['value'][0][0];
				$phone2  = $vcard['TEL'][1]['value'][0][0];
				$phone3  = $vcard['TEL'][2]['value'][0][0];
				//Insert contact
				$SQL = sprintf(SQL_CONTACT_CREATE, $client_id, 0, $fn, $ln, NULL,
				NULL, $email1, $email2, $phone1, $phone2, $phone3);
				$this->DB->Execute($SQL);
			}
			Response::redirect('index.php?module=contacts');
		}
		else {
		   $this->ThrowError(3000);
		}
	}

	function ListContacts() {
		if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_READ)) 
		{
			if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE)) 
			{
				$modAction[0] = '<a href="#" onclick="newContact(); return false;">' . MSG_NEW_CONTACT . '</a>';
				$modAction[1] = '<a href="index.php?module=contacts&amp;action=import">' . MSG_IMPORT_VCARD . '</a>';
				$modAction[2] = '<a href="index.php?module=contacts&amp;action=exportcsv">' . MSG_EXPORT_ALL_TO_CSV . '</a>';
			}

			// paging code
			$limit = Settings::get('RecordsPerPage');
			$offset = (Request::get('start') == null) ? 0 : Request::get('start');
			//~ paging code

			// Build links of letters across the top of the page.
			$links = '';
			for ($i = 65; $i < 91; $i++) 
			{
				$chr = chr($i);
				if ($i > 65) { $links .= '|'; }
				$links .= '<a href="index.php?module=contacts&filter=' . $chr . '">' . $chr . '</a>';
			}

			$modHeader = MSG_LIST;

			$clientsAccessList = $this->User->GetUserItemAccess('clients', CU_ACCESS_DENY);
			$clientIDList = array('0');
			if ($clientsAccessList == '-1') 
			{
				$clientIDs = $this->DB->Query(SQL_GET_CLIENTS_ALL);
				for ($i = 0; $i < count($clientIDs); $i++)
					$clientIDList[] = $clientIDs[$i]['ID'];
				$clientsAccessList = join(',', $clientIDList);
			}

			$filter = substr(Request::get('filter'), 0, 1);
			$uc = strtoupper($filter);

			if (strlen($filter) > 0) 
			{
				if ($this->User->HasModuleItemAccess('administration', CU_ACCESS_ALL, CU_ACCESS_READ))
					$SQL = sprintf(SQL_SEARCH_CONTACTS_FILTER, $uc);
				else
					$SQL = sprintf(SQL_SEARCH_CONTACTS_IN_FILTER, $clientsAccessList.$ext, $uc);
				$users_sql = sprintf(SQL_SEARCH_USERS_FILTER, $uc);
			}
			else 
			{
				if ($this->User->HasModuleItemAccess('administration', CU_ACCESS_ALL, CU_ACCESS_READ))
					$SQL = sprintf(SQL_SEARCH_CONTACTS);
				else
					$SQL = sprintf(SQL_SEARCH_CONTACTS_IN, $clientsAccessList.$ext);
				$users_sql = SQL_SEARCH_USERS;
			}

			$RS =& new DBRecordset();
			$RS->Open($SQL, $this->DB);
			$count = 0;

			if (!$RS->EOF()) 
			{
				while (!$RS->EOF()) 
				{
					$user_array[$count]['txtLName'] = $RS->Field('LastName');
					$user_array[$count]['txtName'] = '<a href="index.php?module=contacts&action=view&id='.$RS->Field('ID').'">'
						.$RS->Field('FirstName').' '. '<strong>'.$RS->Field('LastName').'</strong></a>';
					$user_array[$count]['txtCName'] = $RS->Field('ClientName');
					$user_array[$count]['txtTitle'] = $RS->Field('Title');
					$user_array[$count]['txtEmail'] = $RS->Field('EmailAddress1');
					$user_array[$count]['txtPhone'] = $RS->Field('Phone1');
					$user_array[$count]['txtMobile'] = $RS->Field('Phone3');
					$user_array[$count]['txtClientID'] = $RS->Field('ClientID');
					$user_array[$count]['txtID'] = $RS->Field('ID');
					$count++;
					$RS->MoveNext();
				}
			}

			$RS->Close();
			unset($RS);

			$total_contacts = $count;
			$users_count = 0;

			/* this then checks to see if we should list users in the contacts list, and if so, add it to the user list */
			if (Settings::get('UsersInContacts')) 
			{
				$user_result = $this->DB->Query($users_sql);
				if (is_array($user_result)) 
				{
					foreach ($user_result as $key => $value) 
					{
						$user_array[$count]['txtLName'] = $value['LastName'];
						$user_array[$count]['txtName'] = '<strong>'.$value['FirstName'].' '.$value['LastName']. ' (user)</strong></a>';
						$user_array[$count]['txtCName'] = NULL;
						$user_array[$count]['txtTitle'] = $value['Title'];
						$user_array[$count]['txtEmail'] = $value['EmailAddress'];
						$user_array[$count]['txtPhone'] = $value['Phone1'];
						$user_array[$count]['txtMobile'] = $value['Phone3'];
						$user_array[$count]['txtID'] = 0;
						$count++;
						$users_count++;
					}
				}
			}

			if (is_array($user_array)) 
			{
				foreach ($user_array as $key => $row) 
				{
					switch (Request::get('order')) 
					{
						case "cname": $sorted[$key] = $row['txtCName']; break;
						case "title": $sorted[$key] = $row['txtTitle']; break;
						case "email": $sorted[$key] = $row['txtEmail']; break;
						case "phone": $sorted[$key] = $row['txtPhone']; break;
						case "mobile": $sorted[$key] = $row['txtMobile']; break;
						default:	  $sorted[$key] = $row['txtLName'];
					}
				}

				switch (Request::get('direction')) 
				{
					case 'down': $orderdir = SORT_DESC; break;
					default	: $orderdir = SORT_ASC;
				}

				array_multisort($sorted, $orderdir, SORT_STRING, $user_array);
		
				if (Request::get('id', Request::R_INT) != null)
				{
					// Determine what set of 'pagination' users has the selected user in it and go straight to that page
					$index = 0;
					foreach ($user_array as $i => $user)
					{
						if ($user['txtID'] == Request::get('id', Request::R_INT))
						{
							$index = $i;
							break;
						}
					}
				
					$offset = (int) floor($index / Settings::get('RecordsPerPage')) * Settings::get('RecordsPerPage');
				}
				
				$tmpl['start'] = $offset;
				$this->setTemplate('list_header', $tmpl);

				// The code so far has built an array of all contacts and all users on the system.
				// This means we have to get a chunk of the array according to the page selected by the user.
				// do type checking as well, as $offset might be a number
				if ( $offset !== 'all' ) {
					$user_array = array_slice($user_array, (int)$offset, $limit );
				}

				foreach ($user_array as $key => $value) 
				{
					$tmpl['txtID']		  = $value['txtID'];
					$tmpl['txtName']		= $value['txtName'];
					$tmpl['txtCName']	   = $value['txtCName'];
					$tmpl['txtClientID']	= $value['txtClientID'];
					$tmpl['txtTitle']	   = $value['txtTitle'];
					$tmpl['txtEmail']	   = $value['txtEmail'];
					$tmpl['txtPhone']	   = $value['txtPhone'];
					$tmpl['txtMobile']	  = $value['txtMobile'];
					
					if ($value['txtClientID'] == '') 
						$tmpl['txtLastContact'] = Format::date('');
					else
						$tmpl['txtLastContact'] = Format::date($this->DB->ExecuteScalar(sprintf(SQL_GET_LAST_CONTACT,$value['txtClientID'], $value['txtID'])), Settings::get('PrettyDateFormat'));
					$this->setTemplate('list_item', $tmpl);
				}
				if (($total_contacts + $users_count) > $limit) 
				{
					$order = Request::get('order');
					$direction = Request::get('direction');
					$url = 'index.php?module=contacts&amp;filter='.$filter.'&amp;order='.$order.'&amp;direction='.$direction;
					cuPaginate(($total_contacts + $users_count), $limit, $url, $offset, $tmpl);
					$this->setTemplate('paging', $tmpl);
					unset($tmpl);
				}

				// Emulate the task view screen.
				$tmpl['script'] = ''; 
				if (Request::get('action') == 'view')
				{
					$tempTmpl['txtID'] = Request::get('id', Request::R_INT);
					if ($tempTmpl['txtID'] > 0)
						$tmpl['script'] = $this->getTemplate('contact_view_script', $tempTmpl);
				}

				$this->setTemplate('list_footer', $tmpl);
			}
			else 
			{
				$tmpl['MESSAGE'] = MSG_NO_CONTACTS_AVAILABLE;
				$tmpl['lblIcon'] = 'contacts';
				$tmpl['script'] = ''; 

				// still show the wrapper stuff, even if there are no clients.
				$this->setTemplate('list_header', $tmpl);
				$this->setTemplate('list_footer', $tmpl);
			}

			$this->setHeader(MSG_CONTACTS);
			$this->setModule($modHeader, $modAction);
			$this->Render();
		}
		else 
		{
			$this->ThrowError(2001);
		}
	}

	function NewContact() {
		if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE)) {
			$this->DisplayForm();
		}
		else {
			$this->ThrowError(2001);
		}
	}

	function EditContact() {
		$id = Request::get('id', Request::R_INT);
		if (is_numeric($id)) {
			if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE)) {
				$this->DisplayForm($id);
			}
			else {
				$this->ThrowError(2001);
			}
		}
		else {
			Response::redirect('index.php?module=contacts');
		}
	}

	function DisplayForm($id = 0) {
		$clientid = Request::get('clientid', Request::R_INT);
		$title = MSG_CONTACTS;
		$breadcrumbs = '';

		  $tmpl['ID']			= $id;
		$tmpl['lblClient']	   = MSG_CLIENT;
		$tmpl['lblKeyContact']	   = MSG_KEY_CONTACT;

		// get the list of client items that the user has access to.
		$clientsAccessList = $this->User->GetUserItemAccess('clients', CU_ACCESS_WRITE);
		if ($clientsAccessList == '-1') {
			$clientsAccessList = '';
			$clientIDs = $this->DB->Query(SQL_GET_CLIENTS_ALL);
			for ($i = 0; $i < count($clientIDs); $i++)
				if ($this->User->HasUserItemAccess('clients',$clientIDs[$i]['ID'], CU_ACCESS_WRITE)) {
					$clientsAccessList .= $clientIDs[$i]['ID'].',';
				}
				else {
					$clientsAccessList = '0';
				}
			$clientsAccessList = substr($clientsAccessList, 0, -1);

		   }
		if ( $clientsAccessList == '-1' ) {
			$clientlist = $this->DB->Query(SQL_GET_CLIENTS_ALL);
		}
		else {
			$clientsAccessList = ($clientsAccessList) ? $clientsAccessList : '0';
			$SQL = sprintf(SQL_GET_CLIENTS, $clientsAccessList);
			$clientlist = $this->DB->Query($SQL);

		}
		$action[] = '<a href="javascript:SubmitForm();">'.MSG_SAVE.'</a>';
		if ($id == 0) {
			$breadcrumbs .= MSG_NEW_CONTACT;
			$tmpl['FirstName']	 = '';
			$tmpl['LastName']	 = '';
			$tmpl['KeyContact']	 = '';
			$tmpl['Notes']	 = '';
			$tmpl['Title']		 = '';
			$tmpl['EmailAddress1']	 = '';
			$tmpl['EmailAddress2']	 = '';
			$tmpl['EmailAddress3']	 = '';
			$tmpl['Phone1']	  = '';
			$tmpl['Phone2']	  = '';
			$tmpl['Phone3']	  = '';
		}
		else {
			$ext .= ',0';
			if ($clientsAccessList == '-1') {
				$SQL = sprintf(SQL_GET_CONTACT, $id);
			}
			else {
				$SQL = sprintf(SQL_GET_CONTACT_IN,$clientsAccessList.$ext, $id);
			}

			$RS =& new DBRecordset();
			$RS->Open($SQL, $this->DB);
			if (!$RS->EOF()) {
				$tmpl['FirstName']	= htmlspecialchars($RS->Field('FirstName'));
				$tmpl['LastName']	= htmlspecialchars($RS->Field('LastName'));
				$breadcrumbs .= $tmpl['FirstName'] . ' ' . $tmpl['LastName'] . ' ' . MSG_EDIT;
				$tmpl['KeyContact']	 = ($RS->Field('KeyContact')) ? 'checked' : '';
				$tmpl['Notes']	= htmlspecialchars($RS->Field('Notes'));
				$tmpl['Title']		= htmlspecialchars($RS->Field('Title'));
				$tmpl['EmailAddress1']	= htmlspecialchars($RS->Field('EmailAddress1'));
				$tmpl['EmailAddress2']	= htmlspecialchars($RS->Field('EmailAddress2'));
				$tmpl['Phone1']	 = htmlspecialchars($RS->Field('Phone1'));
				$tmpl['Phone2']	 = htmlspecialchars($RS->Field('Phone2'));
				$tmpl['Phone3']	 = htmlspecialchars($RS->Field('Phone3'));
				$clientid				  = $RS->Field('ClientID');
				$tmpl['ClientID']	= $clientid;
			}
			else {
				$this->ThrowError(2001);
			}
			$RS->Close();
			unset($RS);
		}
		$txtclients = '<option value="0">--No Client</option>';
		// create client list <option>s

		for ($i = 0, $clientcount = count($clientlist); $i < $clientcount; $i++) {
			if ($clientlist[$i][0]) {
				$txtclients .= sprintf('<option value="%s" %s>%s</option>',
					$clientlist[$i][0], ($clientid == $clientlist[$i][0]) ? 'SELECTED' : '', $clientlist[$i][1]);
			}
		}
		$tmpl['txtClientList']	 = $txtclients;

		$this->setHeader($title);
		$this->setModule($breadcrumbs,$action);
		$this->setTemplate('form', $tmpl);

		$this->Render();
	}

	function SaveContact() {
		$clientid   = $this->DB->Prepare(Request::post('clientid'));
		if ($clientid) {
			if (!($this->User->HasUserItemAccess('clients', $clientid, CU_ACCESS_WRITE))) {
				$this->ThrowError(2001);
			}
		}

		$title = MSG_CONTACTS;
		$breadcrumbs	= MSG_SAVE;
		$id			 = Request::post('id');
		$firstname	  = $this->DB->Prepare(Request::post('firstname'));
		$lastname	   = $this->DB->Prepare(Request::post('lastname'));
		$keycontact	 = (Request::post('keycontact') > 0) ? 1 : 0;
		$notes		  = $this->DB->Prepare(Request::post('notes'));
		$title		  = $this->DB->Prepare(Request::post('title'));
		$address1	   = $this->DB->Prepare(Request::post('emailaddress1'));
		$address2	   = $this->DB->Prepare(Request::post('emailaddress2'));
		$phone1		 = $this->DB->Prepare(Request::post('phone1'));
		$phone2		 = $this->DB->Prepare(Request::post('phone2'));
		$phone3		 = $this->DB->Prepare(Request::post('phone3'));

		if ($id == 0) {
		// INSERT record
			$SQL = sprintf(SQL_CONTACT_CREATE, $clientid, $keycontact, $firstname, $lastname, $notes,
				$title, $address1, $address2, $phone1, $phone2, $phone3);
			$this->DB->Execute($SQL);
			$id = $this->DB->ExecuteScalar(SQL_LAST_INSERT);
		}
		else {
			// UPDATE record
			$SQL = sprintf(SQL_CONTACT_UPDATE, $clientid, $keycontact, $firstname, $lastname, $notes,
				$title, $address1, $address2, $phone1, $phone2, $phone3, $id);
			$this->DB->Execute($SQL);
		}

		$this->setHeader($title);
		$this->setModule($breadcrumbs);
		$this->setTemplate('saved', $tmpl);
		$this->Render();
		Response::redirect('index.php?module=contacts&action=view&id='.$id);
	}

	function ViewContact() {
		$id		= Request::get('id', Request::R_INT);
		$start	 = Request::get('start');
		$title	 = MSG_CONTACTS;
		$modHeader = MSG_VIEW;
		
		if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE)) {
			$modAction[0] = '<a href="index.php?module=contacts&action=edit&id=' . $id . '">' . MSG_EDIT . '</a>';
		}
		else {
			$modAction[0] = '';
		}
		$template	= 'message';
		$tmpl['MESSAGE'] = MSG_USER_NOT_FOUND;
		$tmpl['lblIcon'] = 'contacts';
		$tmpl['lblKeyContact'] = MSG_KEY_CONTACT;
		if (is_numeric($id)) {
			$client_access_list = $this->User->GetUserItemAccess('clients', CU_ACCESS_READ);
			$ext .= ',0';
			if ($client_access_list == '-1') {
				$SQL = sprintf(SQL_GET_CONTACT, $id);
			}
			else {
				$SQL = sprintf(SQL_GET_CONTACT_IN,$client_access_list.$ext, $id);
			}
			$RS =& new DBRecordset();
			$RS->Open($SQL, $this->DB);
			if (!$RS->EOF()) {
				$clientID = $RS->Field('ClientID');
				if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE)) {
					$modAction[1] = '<a href="index.php?module=contacts&action=delete&id=' . $id . '&clientid='.$clientID.'">' . MSG_DELETE . '</a>';
//change_log 2.
					$modAction[2] = '<a href="index.php?module=contacts&action=exportvcard&id=' . $id . '">' . MSG_EXPORT_VCARD . '</a>';
				}
				$template	   = 'view';
				$tmpl['ID']	   = $id;
				$tmpl['FirstName'] = $RS->Field('FirstName');
				$tmpl['LastName']  = $RS->Field('LastName');
				$modHeader = $tmpl['FirstName'] . ' ' . $tmpl['LastName'] . ' ' .$modHeader;
				$tmpl['ClientName']  = $RS->Field('ClientName');
				$tmpl['Title']	   = $RS->Field('Title');
				$tmpl['EmailAddress1']	  = $RS->Field('EmailAddress1');
				$tmpl['EmailAddress2']	  = $RS->Field('EmailAddress2');
				$tmpl['Phone1']	= $RS->Field('Phone1');
				$tmpl['Phone2']	= $RS->Field('Phone2');
				$tmpl['Phone3']	= $RS->Field('Phone3');
				$tmpl['Notes']	= nl2br($RS->Field('Notes'));
				$tmpl['KeyContact']	 = ($RS->Field('KeyContact')) ? MSG_YES : MSG_NO;
			}
			$RS->Close();
			unset($RS);
		}
		$header_tmpl['ID'] = $id;
		$header_tmpl['start'] = $start;
		$header_tmpl['lblDate'] = MSG_DATE;
		$header_tmpl['lblClientName'] = MSG_CLIENT;
		$header_tmpl['lblProjectName'] = MSG_SUBJECT;
		$header_tmpl['lblTaskName'] = MSG_TASK;
		$header_tmpl['lblUpdate'] = MSG_UPDATE;
		$header_tmpl['lblUserName'] = MSG_USERNAME;
		$tmpl['updates'] = $this->getTemplate('updates_header',$header_tmpl);

		//ordering
		switch (Request::get('order')) {
			case 'clientname'   : $order = 'clientname';	 $orderby = 'ClientName'; break;
			case 'projectname'	: $order = 'projectname';	$orderby = 'ProjectName'; break;
			case 'taskname'	   : $order = 'taskname';		 $orderby = 'TaskName'; break;
			case 'update'	   : $order = 'update';		 $orderby = 'Update'; break;
			case 'username'	   : $order = 'username';		 $orderby = 'UserName'; break;
			default				: $order = 'date';			 $orderby = 'Date';
		}
		switch (Request::get('direction')) {
			case 'down': $direction = 'down'; $orderdir = 'DESC'; break;
			default	: $direction = 'up'; $orderdir = 'ASC';
		}
		// end ordering

		// paging code
		$limit = Settings::get('RecordsPerPage');
		$offset = Request::get('start');

		$projects_access_list = $this->User->GetUserItemAccess('projects', CU_ACCESS_READ);
		if ($projects_access_list == '-1') {
			$SQL = sprintf(SQL_UPDATES_LIST, $id, $orderby, $orderdir);
		}
		else {
			$SQL = sprintf(SQL_UPDATES_LIST_IN, $id, $orderby, $orderdir, $projects_access_list);
		}

		if ($offset == 'all') {
			$UpdatesRS =& new DBRecordset();
			$UpdatesRS->Open($SQL, $this->DB);
		}
		else {
			if (!is_numeric($offset)) { $offset = 0; }
			$UpdatesRS =& new DBPagedRecordset();
			$UpdatesRS->Open($SQL, $this->DB, $limit, $offset);
		}
		//~ paging code

		$counter = 1;
		while (!$UpdatesRS->EOF()) {
			   if ($counter > 1) $tmpl['updates'] .= $this->getTemplate('updates_spacer');
			   $item_tmpl['ID'] = $UpdatesRS->Field('ID');
			   $item_tmpl['CLIENT_ID'] = $UpdatesRS->Field('ClientID');
			   $item_tmpl['PROJECT_ID'] = $UpdatesRS->Field('ProjectID');
			   $item_tmpl['TASK_ID'] = $UpdatesRS->Field('TaskID');
			   $item_tmpl['DATE'] = Format::date($UpdatesRS->Field('Date'));
			   $item_tmpl['CLIENT_NAME'] = $UpdatesRS->Field('ClientName');
			   $item_tmpl['PROJECT_NAME'] = $UpdatesRS->Field('ProjectName');
			   $item_tmpl['TASK_NAME'] = $UpdatesRS->Field('TaskName');
			   $item_tmpl['UPDATE'] = substr($UpdatesRS->Field('Body'),0,50);
			   $item_tmpl['UPDATE_ROLLOVER'] = $UpdatesRS->Field('Body');
			   $item_tmpl['USER_NAME'] = $UpdatesRS->Field('UserName');
			   $tmpl['updates'] .= $this->getTemplate('updates_item', $item_tmpl);
			unset($item_tmpl);
			++$counter;
			$UpdatesRS->MoveNext();
		}

		if ($UpdatesRS->TotalRecords > $limit) {
			$url = 'index.php?module=contacts&amp;action=view&amp;id='.$id.'&amp;order='.$order.'&amp;direction='.$direction;
			   cuPaginate($UpdatesRS->TotalRecords, $limit, $url, $offset, $paging_tmpl);
			   $tmpl['updates'] .= $this->getTemplate('updates_paging', $paging_tmpl);
		}

		$tmpl['updates'] .= $this->getTemplate('updates_footer');
		$this->setHeader($title);
		$this->setModule($modHeader, $modAction);
		$this->setTemplate($template, $tmpl);
		$this->Render();
	}

	function DeleteContact() {
		$id = Request::get('id', Request::R_INT);
		$clientid = Request::get('clientid', Request::R_INT);
		if ((is_numeric($id)) && (is_numeric($clientid)) && ($this->User->HasUserItemAccess('clients', $clientid, CU_ACCESS_WRITE))) {
			$title = MSG_CONTACTS;
			$breadcrumbs = MSG_DELETE;
			$confirm = Request::get('confirm');
			if ($confirm == 1) {
				$this->DB->Execute(sprintf(SQL_DELETE_CONTACT, $id));
				$tmpl['MESSAGE'] = MSG_CALENDAR_DELETED;
				$tmpl['OK']	 = MSG_OK;
				$template		 = 'deleted';
				Response::redirect('index.php?module=contacts');
			}
			else {
				$SQL = sprintf(sprintf(SQL_GET_FULLNAME, $id));
				$rs  = $this->DB->QuerySingle($SQL);
				if (is_array($rs)) {
					$tmpl['ID']		 = $id;
					$tmpl['clientID'] = $clientid;
					$tmpl['MESSAGE'] = sprintf(MSG_DELETE_CONTACT_WARNING, $rs['FirstName'] . ' ' . $rs['LastName']);
					$tmpl['YES']	 = MSG_YES;
					$tmpl['NO']		 = MSG_NO;
					$template	 = 'delete';
				}
			}
			$this->setHeader($title);
			$this->setModule($breadcrumbs);
			$this->setTemplate($template, $tmpl);
			$this->Render();
		}
		else {
			$this->ThrowError(2001);
		}
	}

	function AjaxViewContact() {
		$id = Request::get('id', Request::R_INT);
		$start = Request::get('start');

		$template	= 'message';
		$tmpl['MESSAGE'] = MSG_USER_NOT_FOUND;
		$tmpl['lblIcon'] = 'contacts';
		$tmpl['lblKeyContact'] = MSG_KEY_CONTACT;

		if (is_numeric($id)) 
		{
			$client_access_list = $this->User->GetUserItemAccess('clients', CU_ACCESS_READ);
			$ext .= ',0';
			if ($client_access_list == '-1') 
				$SQL = sprintf(SQL_GET_CONTACT, $id);
			else 
				$SQL = sprintf(SQL_GET_CONTACT_IN,$client_access_list.$ext, $id);

			$RS =& new DBRecordset();
			$RS->Open($SQL, $this->DB);
			if (!$RS->EOF()) 
			{
				$this->Log('contact', $id, 'view', $RS->Field('FirstName').' '.$RS->Field('LastName'));

				$clientID = $RS->Field('ClientID');
				if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE)) 
				{
					$actions[] = array('url' => '#', 'name' => MSG_EDIT, 'attrs' => "onclick=\"editContact($id); return false;\"");
					$actions[] = array('url' => url::build_url('contacts', 'delete', "id=$id&clientid=".$RS->Field('ClientID')), 'name' => MSG_DELETE,
						'confirm' => 1, 'title' => MSG_CONFIRM_CONTACT_DELETE_TITLE, 'body' => MSG_CONFIRM_CONTACT_DELETE_BODY);
				}

				$actions[] = array('url' => url::build_url('contacts', 'exportvcard', "id=$id"), 'name' => MSG_EXPORT_VCARD);

				$tmpl['actions'] = $this->ActionMenu($actions);

				$template = 'ajaxview';
				$tmpl['ID'] = $id;
				$tmpl['FirstName'] = $RS->Field('FirstName');
				$tmpl['LastName'] = $RS->Field('LastName');
				$modHeader = $tmpl['FirstName'] . ' ' . $tmpl['LastName'] . ' ' .$modHeader;
				$tmpl['ClientName'] = $RS->Field('ClientName');
				$tmpl['Title'] = $RS->Field('Title');
				$tmpl['EmailAddress1'] = $RS->Field('EmailAddress1');
				$tmpl['EmailAddress2'] = $RS->Field('EmailAddress2');
				$tmpl['Phone1'] = $RS->Field('Phone1');
				$tmpl['Phone2'] = $RS->Field('Phone2');
				$tmpl['Phone3'] = $RS->Field('Phone3');
				$tmpl['Notes'] = nl2br($RS->Field('Notes'));
				$tmpl['KeyContact'] = ($RS->Field('KeyContact')) ? MSG_YES : MSG_NO;
			}
			$RS->Close();
			unset($RS);
		}

		// paging code
		$limit = Settings::get('RecordsPerPage');
		$offset = Request::get('start');
		$orderby = 'Date';
		$orderdir = 'DESC';

		$projects_access_list = $this->User->GetUserItemAccess('projects', CU_ACCESS_READ);
		if ($projects_access_list == '-1') 
			$SQL = sprintf(SQL_UPDATES_LIST, $id, $orderby, $orderdir);
		else 
			$SQL = sprintf(SQL_UPDATES_LIST_IN, $id, $orderby, $orderdir, $projects_access_list);

		if ($offset == 'all') {
			$UpdatesRS =& new DBRecordset();
			$UpdatesRS->Open($SQL, $this->DB);
		}
		else {
			if (!is_numeric($offset)) { $offset = 0; }
			$UpdatesRS =& new DBPagedRecordset();
			$UpdatesRS->Open($SQL, $this->DB, $limit, $offset);
		}
		//~ paging code

		$tmpl['updates'] = $this->getTemplate('updates_header');

		while (!$UpdatesRS->EOF()) 
		{
						$item_tmpl['txtContact']=$id;
			$item_tmpl['txtCommentID'] = $UpdatesRS->Field('ID');
			$item_tmpl['txtClientID'] = $UpdatesRS->Field('ClientID');
			$item_tmpl['txtProjectID'] = $UpdatesRS->Field('ProjectID');
			$item_tmpl['txtTaskID'] = $UpdatesRS->Field('TaskID');
			$item_tmpl['txtDay'] = Format::date($UpdatesRS->Field('Date'));
						$item_tmpl['txtDayValue'] =  Format::date($UpdatesRS->Field('Date'), TRUE, FALSE);
			$item_tmpl['txtContactName'] = $UpdatesRS->Field('ClientName');
			$item_tmpl['txtProjectName'] = $UpdatesRS->Field('ProjectName');
			$item_tmpl['txtTaskName'] = $UpdatesRS->Field('TaskName');
//			$item_tmpl['UPDATE'] = substr($UpdatesRS->Field('Body'),0,50);
			$item_tmpl['txtBodyValue'] = $UpdatesRS->Field('Body');
						$item_tmpl['txtBody'] = nl2br($UpdatesRS->Field('Body'));
			$item_tmpl['txtUsername'] = $UpdatesRS->Field('UserName');
			$item_tmpl['txtHours'] = (($UpdatesRS->Field('HoursWorked')) > 0.00) ? ($UpdatesRS->Field('HoursWorked')).MSG_HRS : MSG_QUICK_UPDATE;
			$item_tmpl['contact'] = MSG_CONTACT.': '.$tmpl['FirstName'].' '.$tmpl['LastName'].', ';
      $item_tmpl['txtBillable'] = ($UpdatesRS->Field('OutOfScope') == '1') ? "<span class='billability baNonBillable'>".MSG_NOT_BILLABLE."</span>" : "<span class='billability baBillable'>".MSG_BILLABLE."</span>";
			$item_tmpl['txtIssue'] = ($UpdatesRS->Field('Issue') == '1') ? '<span class="issue">'.MSG_ISSUE.'</span>' : '';
			$tmpl['updates'] .= $this->getTemplate('updates_item', $item_tmpl);
			unset($item_tmpl);
			$UpdatesRS->MoveNext();
		}


		if ($UpdatesRS->TotalRecords > $limit) 
		{
			$url = 'index.php?module=contacts&amp;action=view&amp;id='.$id.'&amp;order='.$order.'&amp;direction='.$direction;
			cuPaginate($UpdatesRS->TotalRecords, $limit, $url, $offset, $paging_tmpl);
			$tmpl['updates'] .= $this->getTemplate('updates_paging', $paging_tmpl);
		}

		$tmpl['updates'] .= $this->getTemplate('updates_footer');

		$html = $this->getTemplate('ajaxview', $tmpl);
		echo $html;
	}

	function AjaxEditContact() {
		$id = Request::get('id', Request::R_INT);
		$clientid = Request::get('clientid', Request::R_INT);
		$title = MSG_CONTACTS;
		$breadcrumbs = '';

		if (!$this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE)) 
		{
			$this->ThrowError(2001);
			return;
		}

		// get the list of client items that the user has access to.
		$clientsAccessList = $this->User->GetUserItemAccess('clients', CU_ACCESS_WRITE);
		if ($clientsAccessList == '-1') 
		{
			$clientsAccessList = '';
			$clientIDs = $this->DB->Query(SQL_GET_CLIENTS_ALL);
			for ($i = 0; $i < count($clientIDs); $i++)
			{
				if ($this->User->HasUserItemAccess('clients',$clientIDs[$i]['ID'], CU_ACCESS_WRITE)) 
					$clientsAccessList .= $clientIDs[$i]['ID'].',';
				else 
					$clientsAccessList = '0';
			}
			$clientsAccessList = substr($clientsAccessList, 0, -1);
		}
		if ( $clientsAccessList == '-1' ) 
		{
			$clientlist = $this->DB->Query(SQL_GET_CLIENTS_ALL);
		}
		else 
		{
			$clientsAccessList = ($clientsAccessList) ? $clientsAccessList : '0';
			$SQL = sprintf(SQL_GET_CLIENTS, $clientsAccessList);
			$clientlist = $this->DB->Query($SQL);
		}

		$ext .= ',0';
		if ($clientsAccessList == '-1') 
			$SQL = sprintf(SQL_GET_CONTACT, $id);
		else 
			$SQL = sprintf(SQL_GET_CONTACT_IN,$clientsAccessList.$ext, $id);

		$tmpl['r'] = rand();

		$RS =& new DBRecordset();
		$RS->Open($SQL, $this->DB);
		if (!$RS->EOF()) 
		{
			$tmpl['FirstName'] = htmlspecialchars($RS->Field('FirstName'));
			$tmpl['LastName'] = htmlspecialchars($RS->Field('LastName'));
			$tmpl['KeyContact'] = ($RS->Field('KeyContact')) ? 'checked' : '';
			$tmpl['Notes'] = htmlspecialchars($RS->Field('Notes'));
			$tmpl['Title'] = htmlspecialchars($RS->Field('Title'));
			$tmpl['EmailAddress1'] = htmlspecialchars($RS->Field('EmailAddress1'));
			$tmpl['EmailAddress2'] = htmlspecialchars($RS->Field('EmailAddress2'));
			$tmpl['Phone1'] = htmlspecialchars($RS->Field('Phone1'));
			$tmpl['Phone2'] = htmlspecialchars($RS->Field('Phone2'));
			$tmpl['Phone3'] = htmlspecialchars($RS->Field('Phone3'));
			$tmpl['ClientID'] = $RS->Field('ClientID');
			$tmpl['ClientName'] = $RS->Field('ClientName');
			$tmpl['ID'] = $id;

			$date = ($RS->Field('ClientID') == '') ? '' : $this->DB->ExecuteScalar(sprintf(SQL_GET_LAST_CONTACT, $clientid, $id));
			$tmpl['LastContact'] = Format::date($date);
		}
		else
		{
			if ($id > 0) // Allow ajaxnew to work.
			{
				header('HTTP/1.0 401 Unauthorized');
				return;
			}
			else
			{
				$keys = array('FirstName', 'LastName', 'LastContact', 'Notes', 'Title', 'EmailAddress1', 
					'EmailAddress2', 'Phone1', 'Phone2', 'Phone3', 'ClientID', 'ClientName', 'ID');
				foreach ($keys as $v)
					$tmpl[$v] = '';
			}
		}

		$RS->Close();
		unset($RS);

		$txtclients = '<option value="0">'.MSG_NA.'</option>';
		// create client list <option>s

		for ($i = 0, $clientcount = count($clientlist); $i < $clientcount; $i++) 
		{
			if ($clientlist[$i][0]) 
			{
				$txtclients .= sprintf('<option value="%s" %s>%s</option>',
					$clientlist[$i][0], ($clientid == $clientlist[$i][0]) ? 'SELECTED' : '', $clientlist[$i][1]);
			}
		}
		$tmpl['ClientList'] = $txtclients;

		header('Content-Type: text/html; charset='.CHARSET);
		$html = $this->getTemplate('ajaxedit', $tmpl);
		echo $html;
	}

	function ActionMenu($actions) {
		$actionMenu = '';
		if (is_array($actions))
		{
			foreach ($actions as $action)
			{
				if ($action['confirm'] == 1)
				{
					$action['attrs'] .= 'class="lbOn" rel="confirmLightBox"';
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


} // end class
