<?php
// $Id$
class mod_clients extends Module
{
	var $StatusList = array(MSG_NA, MSG_PROPOSED, MSG_IN_PLANNING, MSG_IN_PROGRESS, MSG_ON_HOLD, MSG_COMPLETE);

	function mod_clients()
	{
		$this->ModuleName   = 'clients';
		$this->RequireLogin = 1;
		$this->Public	   = 0;
		parent::Module();
	}

	function main()
	{
		switch (Request::any('action'))
		{
			case 'view':		$this->ViewClient();   break;
			case 'projectlist': $this->ProjectList();  break;
			case 'filelist':	$this->FileList();	 break;
			case 'contactlist': $this->ContactList();  break;
			case 'filedown':	$this->FileDownload(); break;
			case 'new':		 $this->NewClient();	break;
			case 'edit':		$this->EditClient();   break;
			case 'save':		$this->SaveClient();   break;
			case 'delete':	  $this->DeleteClient(); break;
			case 'showfiles':   $this->ShowFiles();	break;
			case 'hidefiles':   $this->HideFiles();	break;
			case 'ajaxprojectlist': $this->AjaxProjectList();  break;
			case 'ajaxaddaccess':			$this->AjaxAddAccess(); break;
			case 'ajaxremoveaccess':		 $this->AjaxRemoveAccess(); break;

			// new gantt functions
			case 'timeline':  $this->GanttChart(); break;
			case 'ganttdata': $this->GanttData();  break;
			case 'ganttsave': $this->GanttSave();  break;
			default:		  $this->ListClients();
		}
	}

	function AjaxAddAccess() {
		$clientID = Request::get('clientid', Request::R_INT);
		$mode = Request::get('mode');

		// id is not always an int here. sometimes it's g+number for groups
		$id = Request::get('id');
		$accessID = ($mode == 'write') ? CU_ACCESS_WRITE : CU_ACCESS_READ;

		if (!$this->User->HasUserItemAccess($this->ModuleName, $clientID, CU_ACCESS_WRITE))
		{
			header('HTTP/1.0 401 Unauthorized');
			return;
		}

		header('Content-Type: text/html; charset='.CHARSET);

		if ($id[0] == 'g')
		{
			$groupID = substr($id, 1);
			$tmpl['txtName'] = MSG_GROUP.': '.$this->DB->ExecuteScalar(sprintf(SQL_GET_GROUP_NAME, (int)$groupID));

			$sql = sprintf(SQL_CREATE_GROUP_PERMISSIONS, $groupID, $clientID, $accessID);
			$this->DB->Execute($sql);
		}
		else
		{
			$tmpl['txtName'] = $this->DB->ExecuteScalar(sprintf(SQL_GET_USERNAME, (int)$id));

			$sql = sprintf(SQL_CREATE_USER_PERMISSIONS, (int)$id, $clientID, $accessID);
			$this->DB->Execute($sql);
		}

		$tmpl['txtID'] = $id;
		$tmpl['txtMode'] = $mode;
		$html = $this->getTemplate('permission_item', $tmpl);
		echo $html;
	}

	function AjaxRemoveAccess() {
		$clientID = Request::get('clientid', Request::R_INT);
		$mode = Request::get('mode');

		// id is not always an int here. sometimes it's g+number for groups
		$id = Request::get('id');

		if (!$this->User->HasUserItemAccess($this->ModuleName, $clientID, CU_ACCESS_WRITE))
		{
			header('HTTP/1.0 401 Unauthorized');
			return;
		}
		header('Content-Type: text/html; charset='.CHARSET);
		if ($id[0] == 'g')
		{
			$isGroup = 1;
			$groupID = substr($id, 1);
			$name = $this->DB->ExecuteScalar(sprintf(SQL_GET_GROUP_NAME, (int)$groupID));

			$sql = sprintf(SQL_DELETE_GROUP_PERMISSIONS, $groupID, $clientID);
			$this->DB->Execute($sql);
		}
		else
		{
			$isGroup = 0;
			$name = $this->DB->ExecuteScalar(sprintf(SQL_GET_USERNAME, (int)$id));

			$sql = sprintf(SQL_DELETE_USER_PERMISSIONS, (int)$id, $clientID, $accessID);
			$this->DB->Execute($sql);
		}
		echo "{name:'".$name."',isGroup:".$isGroup."}";
	}

	function AjaxProjectList() {
		header('Content-Type: text/html; charset='.CHARSET);
		$clientID = Request::get('id', Request::R_INT);

		if (!$this->User->HasUserItemAccess($this->ModuleName, $clientID, CU_ACCESS_READ)) 
		{
			$this->ThrowError(2001);
			return;
		}

		$active = (Request::get('archived') == '1') ? 0 : 1;
		$archive = (Request::get('archived') == '1') ? 1 : 0;

		switch (Request::get('order')) 
		{
			default :		 $order = 'Name'; $orderby = 'ProjectName';
		}

		switch (Request::get('direction')) 
		{
			case 'down': $direction = 'down'; $orderdir = 'DESC'; break;
			default:	 $direction = 'up';   $orderdir = 'ASC';
		}

		$tmpl['projects'] = '';
		$sql = sprintf(SQL_PROJECTS_LIST_ALL, $active, $orderby, $orderdir, $clientID);
		$rows = $this->DB->Query($sql);
		if (count($rows) > 0)
		{
			$hasBudgetRead = $this->User->HasModuleItemAccess('budget', CU_ACCESS_ALL, CU_ACCESS_READ);
			foreach ($rows as $p)
			{
				// Add Invoice Other Items to the Actual Budget cost.
				$sql = sprintf(SQL_GET_PROJECT_OTHER_ITEMS_COST, $p['ID']);
				$otherItemCost = $this->DB->ExecuteScalar($sql);

				$sql = sprintf(SQL_GET_PROJECT_OTHER_ITEMS_CHARGE, $p['ID']);
				$otherItemCharge = $this->DB->ExecuteScalar($sql);

				$sql = sprintf(SQL_GET_PROJECT_CHARGE, $p['ID']);
				$charge = $this->DB->QuerySingle($sql);

				$tmplItem = $p;
				$tmplItem['Colour'] = ($p['Colour'] == '') ? Settings::get('DefaultColour') : $p['Colour'];
				$tmplItem['Status'] = $this->StatusList[$p['Status']];
				$tmplItem['LatestActivity'] = Format::date($p['LatestActivity']);
				$tmplItem['StartDate'] = Format::date($p['StartDate']);
				$tmplItem['EndDate'] = Format::date($p['EndDate']);
				$tmplItem['Priority'] = Format::convert_priority($p['Priority']);
				$tmplItem['TargetBudget'] = ($hasBudgetRead) ? Format::money($p['TargetBudget']) : MSG_NA;
				$tmplItem['BudgetCharge'] = ($hasBudgetRead) ? Format::money($charge['Charge'] + $otherItemCharge) : MSG_NA;
				$tmplItem['BudgetCost'] = ($hasBudgetRead) ? Format::money($p['ActualBudget'] + $otherItemCost) : MSG_NA;
				$issues = $this->DB->ExecuteScalar(sprintf(SQL_COUNT_TASK_ISSUES, $p['ID']));
				$tmplItem['Issue'] = ($issues > 0) ? ' <span class="issue">'.MSG_ISSUE.'</span>' : '';
				$tmpl['projects'] .= $this->getTemplate('ajax_project_item', $tmplItem);
			}
		}
		else
		{
			$tmpl['projects'] = MSG_NO_PROJECTS_AVAILABLE;
		}
				
		$this->setTemplate('ajax_project_list', $tmpl);
		$this->RenderOnlyContent();
	}

	function ClientTabs($clientid, $active)
	{
		$tmpl['lblClientTab']	 = $this->AddTab(MSG_BREAKDOWN, 'view', $active, $clientid);
		$tmpl['lblProjectsTab'] = $this->AddTab(MSG_PROJECTS, 'projectlist', $active, $clientid);
		$tmpl['lblTimelineTab'] = $this->AddTab(MSG_TIMELINE, 'timeline', $active, $clientid);
		$tmpl['lblContactsTab'] = $this->AddTab(MSG_CONTACTS, 'contactlist', $active, $clientid);

		if ($this->User->HasModuleItemAccess('files', CU_ACCESS_ALL, CU_ACCESS_READ))
			$tmpl['lblFilesTab'] = $this->AddTab(MSG_FILES, 'filelist', $active, $clientid);
		else
			$tmpl['lblFilesTab'] = NULL;

		$this->setTemplate('tabs', $tmpl);
		unset($tmpl);
	}

	function AddTab($name, $action, $active, $clientid)
	{
		$tab = ($active == strtolower($name)) ? 'tab_active' : 'tab_inactive';
		$query = '&action='.$action.'&id='.$clientid;
		return $this->getTemplate($tab, array('lblTabName' => $name, 'lblTabQuery' => $query));
	}

	function ListClients()
	{
		if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_READ))
		{
			$modHeader = '';

			switch (Request::get('order'))
			{
				case 'manager'  : $order = 'manager';  $orderby = 'Manager'; break;
				case 'active'   : $order = 'active';   $orderby = 'ActiveProjects'; break;
				case 'inactive' : $order = 'inactive'; $orderby = 'InactiveProjects'; break;
				default		 : $order = 'client';   $orderby = 'Name';
			}

			switch (Request::get('direction'))
			{
				case 'down': $direction = 'down'; $orderdir = 'DESC'; break;
				default	: $direction = 'up';   $orderdir = 'ASC';
			}
			// end ordering

			// paging code
			$limit = Settings::get('RecordsPerPage');
			$offset = Request::get('start');

			// get the list of items in this object that the user has access to.
			$clientsAccessList = $this->User->GetUserItemAccess('clients', CU_ACCESS_READ);
			if ($clientsAccessList == '-1') 
			{
				$clientsAccessList = '';
				$clientIDs = $this->DB->Query(SQL_GET_CLIENT_IDS);
				for ($i = 0; $i < count($clientIDs); $i++) 
				{
					$hasClientRead = $this->User->HasUserItemAccess('clients', $clientIDs[$i]['ID'], CU_ACCESS_READ);
					$clientsAccessList .= ($hasClientRead) ? $clientIDs[$i]['ID'].',' : '0';
				}
				$clientsAccessList = substr($clientsAccessList, 0, -1);
			}

			$client_count = $this->DB->ExecuteScalar(SQL_COUNT_CLIENTS);
			$modHeader = MSG_LIST; 
			$hasClientWrite = $this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE);
			if (($hasClientWrite) && (($clientsAccessList) || (0 == $client_count)))
				$modAction[] = '<a href="index.php?module=clients&amp;action=new">' . MSG_NEW_CLIENT . '</a>';

			if (Request::get('archived') == '1')
			{
				$archived = 1;
				$modAction[] = '<a href="index.php?module=clients&amp;archived=0">' . MSG_VIEW . ' ' . MSG_ACTIVE . '</a>';
			}
			else
			{
				$archived = 0;
				$modAction[] = '<a href="index.php?module=clients&amp;archived=1">' . MSG_VIEW . ' ' . MSG_ARCHIVES . '</a>';
			}
			$active = ( $archived == 1 ) ? 0 : 1;

			if ( ($clientsAccessList == '-1') || ($this->User->IsAdmin))
				$SQL = sprintf(SQL_CLIENTS_LIST_ALL, $archived, $orderby, $orderdir);
			else
				$SQL = sprintf(SQL_CLIENTS_LIST, $clientsAccessList, $archived, $orderby, $orderdir);

			if ($offset == 'all') 
			{
				$RS =& new DBRecordset();
				$RS->Open($SQL, $this->DB);
			}
			else 
			{
				if (!is_numeric($offset)) { $offset = 0; }
				$RS =& new DBPagedRecordset();
				$RS->Open($SQL, $this->DB, $limit, $offset);
			}
			//~ paging code

			$this->setTemplate('page_header', $tmpl);

			$tmpl['lblClient'] = MSG_CLIENT;
			$tmpl['lblManager'] = MSG_ACCOUNT_MANAGER;
			$tmpl['lblActive'] = MSG_ACTIVE;
			$tmpl['lblInactive'] = MSG_INACTIVE_PROJECTS;
			$tmpl['lblArchive'] = 'archived='.$archived.'&';
			$tmpl['SORTASC'] = MSG_ASCENDING;
			$tmpl['SORTDESC'] = MSG_DESCENDING;
			$tmpl['start'] = $offset;
			if (!$RS->EOF())
			{
								$this->setTemplate('list_header', $tmpl);
								unset($tmpl);
				while (!$RS->EOF())
				{
					$tmpl['CLIENTID'] = $RS->Field('ID');
					$tmpl['CLIENTNAME'] = $RS->Field('Name');
					$tmpl['CLIENTMANAGER'] = $RS->Field('FirstName').' '.$RS->Field('LastName');
					$tmpl['CLIENT_ACTIVE'] = $RS->Field('ActiveProjects');
					$tmpl['CLIENT_INACTIVE'] = $RS->Field('InactiveProjects');
					$tmpl['Colour'] = ($RS->Field('Colour') == '') ? Settings::get('DefaultColour') : $RS->Field('Colour');
					$this->setTemplate('list_item', $tmpl);
					unset($tmpl);
/*
					// Display client projects under the client.
					$project_access_list = $this->User->GetUserItemAccess('projects', CU_ACCESS_READ);
					$SQL = sprintf(SQL_PROJECTS_LIST, $project_access_list, $active, 'ProjectName', $orderdir, $RS->Field('ID'));
					if ( $project_access_list == '-1')
						$SQL = sprintf(SQL_PROJECTS_LIST_ALL, $active, 'ProjectName', $orderdir, $RS->Field('ID'));
					$rows = $this->DB->Query( $SQL );
					if ( !is_array( $rows ) ) $rows = array();
					foreach ( $rows as $row )
					{
						$row['ClientID'] = $RS->Field( 'ID' );
						$this->setTemplate( 'client_project', $row );
					}
*/
					$RS->MoveNext();
				}

				$this->setTemplate('list_footer');

				if ($RS->TotalRecords > $limit)
				{
					$url = 'index.php?module=clients&amp;archived='.$archived.'&amp;order='.$order.'&amp;direction='.$direction;
					cuPaginate($RS->TotalRecords, $limit, $url, $offset, $tmpl);
					$this->setTemplate('paging', $tmpl);
					unset($tmpl);
				}
			}
			else
			{
				$tmpl['MESSAGE'] = MSG_NO_CLIENTS_AVAILABLE;
				$tmpl['lblIcon'] = 'clients';
				$this->setTemplate('message', $tmpl);
			}
			$RS->Close();
			unset($RS);

			$this->setTemplate('page_footer', $tmpl);

			$header = '';

			$this->setHeader(MSG_CLIENTS, $header);
			$this->setModule($modHeader, $modAction);
		}
		else 
			$this->ThrowError(2001);

		$this->Render();
	}

	function NewClient()
	{
		if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE))
		{
			$this->DisplayForm();
		}
		else
		{
			$this->ThrowError(2001);
		}
	}

	function EditClient()
	{
		$id = Request::get('id', Request::R_INT);
		if (is_numeric($id))
		{
			if ($this->User->HasUserItemAccess($this->ModuleName, $id, CU_ACCESS_WRITE))
			{
				$this->DisplayForm($id);
			}
			else
			{
				$this->ThrowError(2001);
			}
		}
		else
		{
			Response::redirect('index.php?module=clients');
		}
	}

	function DisplayForm($id = 0)
	{
		$popuplib   = '<script language="JavaScript" src="assets/js/selectors/selector.lib.js"></script>'.NewLine;
		$cselector  = '<script language="JavaScript" src="assets/js/selectors/colourselector.js"></script>'.NewLine;
		$modInsert = $popuplib.$cselector;
		$title = MSG_CLIENTS;
		$breadcrumbs = '';

		$tmpl['ID']			 = $id;
		$tmpl['HEADER_ACCESS']	 = MSG_CLIENT_OVERVIEW;
		$tmpl['HEADER_ADDRESS'] = MSG_CLIENT_ADDRESS_DETAILS;
		$tmpl['FORM_NAME']		 = MSG_CLIENT_NAME;
		$tmpl['FORM_MANAGER']	 = MSG_ACCOUNT_MANAGER;
		$tmpl['FORM_COLOUR']	= MSG_COLOUR;
		$tmpl['FORM_DESC']		 = MSG_DESCRIPTION;
		$tmpl['FORM_ARCHIVED']	 = MSG_ARCHIVES;
		$tmpl['FORM_PHONE1']	 = MSG_PHONE_1;
		$tmpl['FORM_PHONE2']	 = MSG_PHONE_2;
		$tmpl['FORM_PHONE3']	 = MSG_PHONE_3;
		$tmpl['FORM_FAX']		 = MSG_FAX_NUMBER;
		$tmpl['FORM_ADDRESS1']	 = MSG_ADDRESS_1;
		$tmpl['FORM_ADDRESS2']	 = MSG_ADDRESS_2;
		$tmpl['FORM_CITY']		 = MSG_CITY;
		$tmpl['FORM_STATE']	 = MSG_STATE;
		$tmpl['FORM_COUNTRY']	 = MSG_COUNTRY;
		$tmpl['FORM_POSTCODE']	 = MSG_POSTCODE;
		$tmpl['FORM_URL']		 = MSG_WEBSITE;
		$tmpl['FORM_EMAIL']	 = MSG_EMAIL;
		$tmpl['FORM_VIEW']		 = MSG_VIEW;
		$tmpl['FORM_SAVEERROR'] = MSG_ENTER_CLIENT_NAME;
		$tmpl['FORM_CONFDEL']	 = MSG_DELETE_USER_CONFIRM;
		$tmpl['lblResources']   = MSG_AVAILABLE_RESOURCES;
		$tmpl['lblReadPerms']   = MSG_CAN_READ;
		$tmpl['lblWritePerms']  = MSG_CAN_WRITE;

		$userlist = $this->DB->Query(SQL_GET_USERS);

		if ($id == 0)
		{
			$template = 'form_new';
			$breadcrumbs .= MSG_NEW_CLIENT;
			$tmpl['CLIENT_NAME']		 = '';
			$tmpl['CLIENT_COLOUR']	  = '#66CCCC';
			$tmpl['CLIENT_DESCRIPTION'] = '';
			$tmpl['CLIENT_ADDRESS1']	 = '';
			$tmpl['CLIENT_ADDRESS2']	 = '';
			$tmpl['CLIENT_CITY']		 = '';
			$tmpl['CLIENT_STATE']		 = '';
			$tmpl['CLIENT_COUNTRY']	 = '';
			$tmpl['CLIENT_POSTCODE']	 = '';
			$tmpl['CLIENT_PHONE1']		 = '';
			$tmpl['CLIENT_PHONE2']		 = '';
			$tmpl['CLIENT_PHONE3']		 = '';
			$tmpl['CLIENT_URL']		 = '';
			$tmpl['CLIENT_EMAIL']	   = '';
			$tmpl['CLIENT_FAX']		 = '';
			$tmpl['txtWriteAccess']	 = '';
			$tmpl['txtReadAccess']	 = '';
			$tmpl['ACTIVE_SELECTED']	 = 'selected';
			$tmpl['INACTIVE_SELECTED']	 = '';
			$managerID = $this->User->ID;

			$tmpl['selectRead'] = '';
			$tmpl['selectWrite'] = '<option value="'.$this->User->ID.'" >'.$this->User->Firstname.' '.$this->User->Lastname.'</option>';

			$groups = '';
			$groups_list = $this->DB->Query( sprintf( SQL_GET_GROUPS ) );
			if ( is_array( $groups_list ) )
			{
				for ($i = 0; $i < count( $groups_list ); $i++)
					$groups .= sprintf('<option value="g%s">Group: %s</option>', $groups_list[$i]['ID'], $groups_list[$i]['Name']);
			}


			$users = '';
			$users_list = $this->DB->Query( sprintf( SQL_GET_USERS ) );
			if ( is_array( $users_list)  )
			{
				for ($i = 0; $i < count( $users_list ); $i++)
				{
					if ( $users_list[$i]['ID'] != $this->User->ID )
						$users .= sprintf('<option value="%s">%s</option>', $users_list[$i]['ID'], $users_list[$i]['FullName']);
				}
			}

			$tmpl['txtNoAccess'] = $groups.$users;
		}
		else
		{
			$template = 'form';

			//$action[] = '<a href="javascript:SubmitForm()">'.MSG_SAVE.'</a>';
			$SQL = sprintf(SQL_GETCLIENT, $id);
			$RS =& new DBRecordset();
			$RS->Open($SQL, $this->DB);
			if (!$RS->EOF())
			{
									
				$tmpl['CLIENT_NAME']		= htmlspecialchars($RS->Field('Name'));
				$breadcrumbs .= $tmpl['CLIENT_NAME'] .' '. MSG_EDIT;
				$tmpl['CLIENT_COLOUR']		= htmlspecialchars($RS->Field('Colour'));
				$tmpl['CLIENT_DESCRIPTION'] = htmlspecialchars($RS->Field('Description'));
				$tmpl['CLIENT_ADDRESS1']	= htmlspecialchars($RS->Field('Address1'));
				$tmpl['CLIENT_ADDRESS2']	= htmlspecialchars($RS->Field('Address2'));
				$tmpl['CLIENT_CITY']		= htmlspecialchars($RS->Field('City'));
				$tmpl['CLIENT_STATE']	   = htmlspecialchars($RS->Field('State'));
				$tmpl['CLIENT_COUNTRY']	 = htmlspecialchars($RS->Field('Country'));
				$tmpl['CLIENT_POSTCODE']	= htmlspecialchars($RS->Field('Postcode'));
				$tmpl['CLIENT_PHONE1']	  = htmlspecialchars($RS->Field('Phone1'));
				$tmpl['CLIENT_PHONE2']	  = htmlspecialchars($RS->Field('Phone2'));
				$tmpl['CLIENT_PHONE3']	  = htmlspecialchars($RS->Field('Phone3'));
				$tmpl['CLIENT_URL']		 = htmlspecialchars($RS->Field('URL'));
				$tmpl['CLIENT_EMAIL']	   = htmlspecialchars($RS->Field('ContactEmail'));
				$tmpl['CLIENT_FAX']		 = htmlspecialchars($RS->Field('FAX'));
				$managerID				  = $RS->Field('Manager');
				$tmpl['CLIENT_ARCHIVED']	= ($RS->Field('Archived') == 1) ? MSG_ARCHIVED : MSG_ACTIVE;

				$tmpl['ACTIVE_SELECTED']	 = $RS->Field('Archived') ? '' : 'selected';
				$tmpl['INACTIVE_SELECTED']	 = $RS->Field('Archived') ? 'selected' : '';

				$allGroups = array(); // All groups with read or write stored here. Used to exclude them later.
				$allUsers = array();  // All users with read or write stored here. Used to exclude them later.

				$groups = '';
				$groups_sql = sprintf(SQL_GET_GROUPS_WITH_READ_PERMS, 'clients', $id);
				$groups_list = $this->DB->Query($groups_sql);
				if ( is_array($groups_list) )
				{
					$groups_count = count($groups_list);
					for ($i = 0; $i < $groups_count; $i++)
					{
						$tmplGroup['txtMode'] = 'read';
						$tmplGroup['txtID'] = 'g'.$groups_list[$i]['ID'];
						$tmplGroup['txtName'] = MSG_GROUP.': '.$groups_list[$i]['Name'];
						$groups .= $this->getTemplate('permission_item', $tmplGroup);
						$allGroups[] = $groups_list[$i]['ID'];
					}
				}

				$users = '';
				$users_sql = sprintf(SQL_GET_USERS_WITH_READ_PERMS, 'clients', $id);
				$users_list = $this->DB->Query($users_sql);
				if ( is_array($users_list) )
				{
					$users_count = count($users_list);
					for ($i = 0; $i < $users_count; $i++)
					{
						$tmplUser['txtMode'] = 'read';
						$tmplUser['txtID'] = $users_list[$i]['ID'];
						$tmplUser['txtName'] = $users_list[$i]['FullName'];
						$users .= $this->getTemplate('permission_item', $tmplUser);
						$allUsers[] = $users_list[$i]['ID'];
					}
				}
				$tmpl['txtReadAccess'] = $groups.$users;

				$groups = '';
				$groups_sql = sprintf(SQL_GET_GROUPS_WITH_WRITE_PERMS, 'clients', $id);
				$groups_list = $this->DB->Query($groups_sql);
				if ( is_array($groups_list) )
				{
					$groups_count = count($groups_list);
					for ($i = 0; $i < $groups_count; $i++)
					{
						$tmplGroup['txtMode'] = 'write';
						$tmplGroup['txtID'] = 'g'.$groups_list[$i]['ID'];
						$tmplGroup['txtName'] = MSG_GROUP.': '.$groups_list[$i]['Name'];
						$groups .= $this->getTemplate('permission_item', $tmplGroup);
						$allGroups[] = $groups_list[$i]['ID'];
					}
				}

				$users = '';
				$users_sql = sprintf(SQL_GET_USERS_WITH_WRITE_PERMS, 'clients', $id);
				$users_list = $this->DB->Query($users_sql);
				if ( is_array($users_list) )
				{
					$users_count = count($users_list);
					for ($i = 0; $i < $users_count; $i++)
					{
						$tmplUser['txtMode'] = 'write';
						$tmplUser['txtID'] = $users_list[$i]['ID'];
						$tmplUser['txtName'] = $users_list[$i]['FullName'];
						$users .= $this->getTemplate('permission_item', $tmplUser);
						$allUsers[] = $users_list[$i]['ID'];
					}
				}
				$tmpl['txtWriteAccess'] = $groups.$users;

				// Defaults so as not to break the query when no one has any permissions.
				if (count($allUsers) == 0)
					$allUsers[] = 0;
				if (count($allGroups) == 0)
					$allGroups[] = 0;

				// List the groups that lack read or write permissions (for the drop down).
				$groups = '';
				$groups_sql = sprintf(SQL_GET_GROUPS_MINUS, implode(',', $allGroups));
				$groups_list = $this->DB->Query($groups_sql);
				if ( is_array($groups_list) )
				{
					$groups_count = count($groups_list);
					for ($i = 0; $i < $groups_count; $i++)
						$groups .= sprintf('<option value="g%s">%s: %s</option>', $groups_list[$i]['ID'], MSG_GROUP, $groups_list[$i]['Name']);
				}

				// List the users that lack read or write permissions (for the drop down).
				$users = '';
				$users_sql = sprintf(SQL_GET_USERS_MINUS, implode(',', $allUsers));
				$users_list = $this->DB->Query($users_sql);
				if ( is_array($users_list) )
				{
					$users_count = count($users_list);
					for ($i = 0; $i < $users_count; $i++)
					{
						$selected = ($users_list[$i]['ID'] == $this->User->ID ? ' selected' : '');
						$name = $users_list[$i]['FirstName'].' '.$users_list[$i]['LastName'];
						$users .= sprintf('<option value="%s"%s>%s</option>', $users_list[$i]['ID'], $selected, $name);
					}
				}

				$tmpl['txtNoAccess'] = $groups.$users;

				//Select Read Permissions
				//Select Write Permissions
			}

			$RS->Close();
			unset($RS);
		}

		// create user list <option>s
		for ($i = 0, $txtusers = null, $usercount = count($userlist); $i < $usercount; $i++)
		{
			$txtusers .= sprintf('<option value="%s" %s>%s</option>', $userlist[$i][0], ($managerID == $userlist[$i][0]) ? 'SELECTED' : '', $userlist[$i][1].' '.$userlist[$i][2]);
		}

		$tmpl['txtManager']		  = $txtusers;


		$tmpl['cancelUrl'] = 'index.php?module=clients';
		if (Request::get('action') == 'edit')
			$tmpl['cancelUrl'] .= '&action=view&id='.$id;

		$this->setHeader($title,$modInsert);
		$this->setModule($breadcrumbs, $action);
		$this->setTemplate($template, $tmpl);
		$this->Render();
	}

	function SaveClient()
	{
		$title = MSG_CLIENTS;
		$breadcrumbs = MSG_SAVE;
		$key_all = CU_ACCESS_ALL;
		$access  = CU_ACCESS_WRITE;

		$id		  = Request::post('id');
		$name		= $this->DB->Prepare(Request::post('name'));
		$manager	 = $this->DB->Prepare(Request::post('manager'));
		$colour	  = $this->DB->Prepare(Request::post('colour'));
		$description = $this->DB->Prepare(Request::post('description'));
		$address1	= $this->DB->Prepare(Request::post('address1'));
		$address2	= $this->DB->Prepare(Request::post('address2'));
		$city		= $this->DB->Prepare(Request::post('city'));
		$state	   = $this->DB->Prepare(Request::post('state'));
		$country	 = $this->DB->Prepare(Request::post('country'));
		$postcode	= $this->DB->Prepare(Request::post('postcode'));
		$email	   = $this->DB->Prepare(Request::post('contactemail'));
		$url		 = $this->DB->Prepare(Request::post('url'));
		$phone1	  = $this->DB->Prepare(Request::post('phone1'));
		$phone2	  = $this->DB->Prepare(Request::post('phone2'));
		$phone3	  = $this->DB->Prepare(Request::post('phone3'));
		$fax		 = $this->DB->Prepare(Request::post('fax'));
		$archived	= (Request::post('archived') == 1) ? 1 : 0;
		$readassign = Request::post( 'readassign' );
		$writeassign = Request::post( 'writeassign' );

		if (($id == 0) && ($this->User->HasModuleItemAccess($this->ModuleName, $key_all, $access)))
		{
			// INSERT record
			$SQL = sprintf(SQL_CLIENTCREATE, $name, $manager, $phone1, $phone2, $phone3, $fax, $address1,
								$address2, $city, $state, $country, $postcode, $url,
								$description, $archived, $email, $colour);
			$this->DB->Execute($SQL);
			$id = $this->DB->ExecuteScalar(SQL_LAST_INSERT);
			
			$sql = sprintf(SQL_CREATE_USER_PERMISSIONS, $this->User->ID, $id, CU_ACCESS_WRITE);
			$this->DB->Execute($sql);

//			// Clear all permissions.
//			$this->DB->Execute( sprintf( SQL_CLEAR_GROUP_PERMISSIONS, $id ) );
//			$this->DB->Execute( sprintf( SQL_CLEAR_USER_PERMISSIONS, $id ) );
//
//			// Add read permissions, which are passed in as a comma-separated string.
//			if ( strlen( $readassign ) > 0 )
//			{
//				$readArray = explode( ',', $readassign );
//				foreach ( $readArray as $r )
//				{
//					// If the first character of string is 'g', it is a group
//					if ( $r{0} == 'g' )
//						$SQL = sprintf( SQL_CREATE_GROUP_READ_PERMISSIONS, substr( $r, 1 ), $id );
//					else
//						$SQL = sprintf( SQL_CREATE_USER_READ_PERMISSIONS, $r, $id );
//					$this->DB->Execute( $SQL );
//				}
//			}
//
//			// Add write permissions, which are passed in as a comma-separated string.
//			if ( strlen( $writeassign ) > 0 )
//			{
//				$writeArray = explode( ',', $writeassign );
//				foreach ( $writeArray as $w )
//				{
//					// If the first character of string is 'g', it is a group
//					if ( $w{0} == 'g' )
//						$SQL = sprintf( SQL_CREATE_GROUP_WRITE_PERMISSIONS, substr( $w, 1 ), $id );
//					else
//						$SQL = sprintf( SQL_CREATE_USER_WRITE_PERMISSIONS, $w, $id );
//					$this->DB->Execute( $SQL );
//				}
//			}

			Response::redirect('index.php?module=clients&action=view&id='.$id);
		}
		elseif ($this->User->HasUserItemAccess($this->ModuleName, $id, $access))
		{
			// UPDATE record
			$SQL = sprintf(SQL_CLIENTUPDATE, $name, $phone1, $phone2, $phone3, $fax, $address1,
							$address2, $city, $state, $country, $postcode, $url,
							$description, $archived, $id, $email, $manager, $colour);
			$this->DB->Execute($SQL);

//			// Clear all permissions.
//			$this->DB->Execute( sprintf( SQL_CLEAR_GROUP_PERMISSIONS, $id ) );
//			$this->DB->Execute( sprintf( SQL_CLEAR_USER_PERMISSIONS, $id ) );
//
//			// Add read permissions, which are passed in as a comma-separated string.
//			if ( strlen( $readassign ) > 0 )
//			{
//				$readArray = explode( ',', $readassign );
//				foreach ( $readArray as $r )
//				{
//					// If the first character of string is 'g', it is a group
//					if ( $r{0} == 'g' )
//						$SQL = sprintf( SQL_CREATE_GROUP_READ_PERMISSIONS, substr( $r, 1 ), $id );
//					else
//						$SQL = sprintf( SQL_CREATE_USER_READ_PERMISSIONS, $r, $id );
//					$this->DB->Execute( $SQL );
//				}
//			}
//
//			// Add write permissions, which are passed in as a comma-separated string.
//			if ( strlen( $writeassign ) > 0 )
//			{
//				$writeArray = explode( ',', $writeassign );
//				foreach ( $writeArray as $w )
//				{
//					// If the first character of string is 'g', it is a group
//					if ( $w{0} == 'g' )
//						$SQL = sprintf( SQL_CREATE_GROUP_WRITE_PERMISSIONS, substr( $w, 1 ), $id );
//					else
//						$SQL = sprintf( SQL_CREATE_USER_WRITE_PERMISSIONS, $w, $id );
//					$this->DB->Execute( $SQL );
//				}
//			}

			Response::redirect('index.php?module=clients&action=view&id='.$id);
		}
		else
		{
			// the user doesn't have access to eitehr insert new, or update existing.
			$this->ThrowError(2001);
		}
	}

	function ViewClient() {
		if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_READ)) {
			$id = Request::get('id', Request::R_INT);

			$name = $this->GetClientName($id);
			$this->Log('client', $id, 'view', $name['Name']);

			$title = MSG_CLIENTS;

			if ($this->User->HasUserItemAccess($this->ModuleName, $id, CU_ACCESS_WRITE)) 
			{
				// Okay, also check that they can even make projects
				if ($this->User->HasModuleItemAccess('projects', CU_ACCESS_ALL, CU_ACCESS_WRITE)) {
				  $actions[] = array('url' => "index.php?module=projects&action=new&clientid=$id", 'name' => MSG_NEW_PROJECT);
				}
				  
				$actions[] = array('url' => "index.php?module=clients&action=edit&id=$id", 'name' => MSG_EDIT);
				$actions[] = array('url' => "index.php?module=clients&action=delete&id=$id", 
								   'name' => MSG_DELETE,
								   'confirm' => 1, 
								   'title' => MSG_CONFIRM_CLIENT_DELETE_TITLE, 
								   'body' => MSG_CONFIRM_CLIENT_DELETE_BODY);
			}

			$template = 'message';
			$tmpl['MESSAGE'] = MSG_USER_NOT_FOUND;
			$this->ClientTabs($id, strtolower(MSG_BREAKDOWN));

			if (is_numeric($id)) {
				$access = CU_ACCESS_READ;
				if ($this->User->HasUserItemAccess($this->ModuleName, $id, $access)) {
					$SQL = sprintf(SQL_GETCLIENT, $id);
					$RS =& new DBRecordset();

					$RS->Open($SQL, $this->DB);

/*
							// File list for this client.
					$filesLimit = 20;
					$files = null;

					$files_access_list = $this->User->GetUserItemAccess('projects', CU_ACCESS_READ);
					// get our SQL
					if ( $files_access_list == '-1' )	$files_sql = sprintf(SQL_FILES_LIST_ALL, $id, 'FileName', "");
					else $files_sql = sprintf(SQL_FILES_LIST, $id, $files_access_list, 'FileName', "");

					$files_list = $this->DB->Query($files_sql);
					if ( is_array($files_list) ) {
						$files_count = count($files_list);

						$files .= $this->getTemplate('project_view_files_header', array('lblFileName' => MSG_FILE_NAME, 'lblFileType' => MSG_FILE_TYPE, 'lblFileSize' => MSG_FILE_SIZE));
						for ($i = 0; $i < $files_count; $i++) {
							if ($files_count < 6) {
								$tmpl['txtFileID'] = $files_list[$i]['ID'];
								$tmpl['txtFileName'] = $files_list[$i]['FileName'];

								$tmpl['txtFileType'] = substr($files_list[$i]['Type'], strpos($files_list[$i]['Type'],'/') + 1);
								$tmpl['txtFileSize'] = round($files_list[$i]['Size'] / 1024, 2) . 'Kb';
								$files .= $this->getTemplate('project_view_files_item', $tmpl);
								unset($tmpl);
							}
						}
						if ($files_count > 5) {
							$moreFilesLink = '<a href="index.php?module=files&clientid='.$clientid.'">'.MSG_MORE.'</a>';
						}
						else {
							$moreFilesLink = '';
						}
						$files .= $this->getTemplate('project_view_files_footer', array('txtMoreFiles' => $moreFilesLink));

					}
					else {
						$files .= MSG_NO_FILES_AVAILABLE;
					}

					// Contact list for this project.
					$contactsLimit = 20;
					$contacts = null;
					$contacts_sql = sprintf(SQL_KEY_CONTACTS_LIST, 'LastName', "", $id);
					$contacts_list = $this->DB->Query($contacts_sql);
					if ( is_array($contacts_list) ) {
						$contacts_count = count($contacts_list);

						$contacts .= $this->getTemplate('project_view_contacts_header', array('lblContactName' => MSG_CONTACT_NAME, 'lblContactEmail' => MSG_EMAIL, 'lblContactPhone' => MSG_PHONE));
						for ($i = 0; $i < $contacts_count; $i++) {
							if ($contacts_count < 6) {
								$tmpl['txtContactName'] = $contacts_list[$i]['LastName'].", ".$contacts_list[$i]['FirstName'];
								$tmpl['txtContactEmail'] = '<a href="mailto:'.$contacts_list[$i]['EmailAddress1'].'">'.$contacts_list[$i]['EmailAddress1'].'</a>';
								$tmpl['txtContactPhone'] = $contacts_list[$i]['Phone1'];
								$contacts .= $this->getTemplate('project_view_contacts_item', $tmpl);
								unset($tmpl);
							}
						}
						if ($contacts_count > 5) {
							$moreContactsLink = '<a href="index.php?module=contacts&projectid='.$projectid.'">'.MSG_MORE.'</a>';
						}
						else {
							$moreContactsLink = '';
						}
						$contacts .= $this->getTemplate('project_view_contacts_footer', array('txtMoreContacts' => $moreContactsLink));

					}
					else {
						$contacts .= MSG_NO_CONTACTS_AVAILABLE;
					}
*/

					// Get groups and users with read permission to this client.
					$tmpl['readaccesslist'] = '';
					$readArray = $this->DB->Query( sprintf( SQL_GET_GROUPS_WITH_READ_PERMS, 'clients', $id ) );
					if ( is_array( $readArray ) )
					{
						foreach ( $readArray as $r )
							$tmpl['readaccesslist'] .= 'Group: '.$r['Name'].'<br />';
					}

					$readArray = $this->DB->Query( sprintf( SQL_GET_USERS_WITH_READ_PERMS, 'clients', $id ) );
					if ( is_array( $readArray ) )
					{
						foreach ( $readArray as $r )
							$tmpl['readaccesslist'] .= $r['FullName'].'<br />';
					}
					if ($tmpl['readaccesslist'] == NULL)
						$tmpl['readaccesslist'] = '--';

					// Get groups and users with write permission to this client.
					$tmpl['writeaccesslist'] = '';
					$writeArray = $this->DB->Query( sprintf( SQL_GET_GROUPS_WITH_WRITE_PERMS, 'clients', $id ) );
					if ( is_array( $writeArray ) )
					{
						foreach ( $writeArray as $w )
							$tmpl['writeaccesslist'] .= 'Group: '.$w['Name'].'<br />';
					}

					$writeArray = $this->DB->Query( sprintf( SQL_GET_USERS_WITH_WRITE_PERMS, 'clients', $id ) );
					if ( is_array( $writeArray ) )
					{
						foreach ( $writeArray as $w )
							$tmpl['writeaccesslist'] .= $w['FullName'].'<br />';
					}
					if ($tmpl['writeaccesslist'] == NULL)
						$tmpl['writeaccesslist'] = '--';

					if (!$RS->EOF()) {
						$template = 'view';
						$managerEmail = $RS->Field('EmailAddress');
						$clientName = $RS->Field('Name');
						$modHeader = $clientName.' '.MSG_VIEW;
						$tmpl['lblFiles'] = MSG_PROJECT_FILES;
						$tmpl['lblContacts'] = MSG_CONTACTS;
						$tmpl['ID']			 = $id;
						$tmpl['FORM_ARCHIVED']	 = MSG_ARCHIVES;
						$tmpl['FORM_VIEW']		 = MSG_VIEW;
						$tmpl['FORM_SAVEERROR'] = MSG_ENTER_CLIENT_NAME;
						$tmpl['FORM_CONFDEL']	 = MSG_DELETE_USER_CONFIRM;

						$tmpl['HEADER_ACCESS']	 = MSG_CLIENT_OVERVIEW;
						$tmpl['HEADER_ADDRESS'] = MSG_CLIENT_ADDRESS_DETAILS;

						$tmpl['FORM_NAME']		 = MSG_CLIENT_NAME;
						$tmpl['FORM_MANAGER']		 = MSG_ACCOUNT_MANAGER;
						$tmpl['FORM_DESC']		 = MSG_DESCRIPTION;

						$tmpl['FORM_ADDRESS1']	 = MSG_ADDRESS_1;
						$tmpl['FORM_ADDRESS2']	 = MSG_ADDRESS_2;
						$tmpl['FORM_CITY']		 = MSG_CITY;
						$tmpl['FORM_STATE']	 = MSG_STATE;
						$tmpl['FORM_COUNTRY']	 = MSG_COUNTRY;
						$tmpl['FORM_POSTCODE']	 = MSG_POSTCODE;
						$tmpl['FORM_PHONE1']	 = MSG_PHONE_1;
						$tmpl['FORM_PHONE2']	 = MSG_PHONE_2;
						$tmpl['FORM_PHONE3']	 = MSG_PHONE_3;
						$tmpl['FORM_FAX']		 = MSG_FAX_NUMBER;
						$tmpl['FORM_URL']		 = MSG_WEBSITE;
						$tmpl['FORM_EMAIL']	 = MSG_EMAIL;

								$tmpl['CLIENT_ARCHIVED']	= ($RS->Field('Archived') == 1) ? MSG_ARCHIVED : MSG_ACTIVE;

						$tmpl['CLIENT_NAME']		= $RS->Field('Name');
						$tmpl['CLIENT_MANAGER']	 = $RS->Field('FirstName').' '.$RS->Field('LastName');
						$tmpl['CLIENT_DESCRIPTION'] = nl2br($RS->Field('Description'));

						$tmpl['CLIENT_ADDRESS1']	= $RS->Field('Address1');
						$tmpl['CLIENT_ADDRESS2']	= $RS->Field('Address2');
						$tmpl['CLIENT_CITY']		= $RS->Field('City');
						$tmpl['CLIENT_STATE']	   = $RS->Field('State');
						$tmpl['CLIENT_COUNTRY']	 = $RS->Field('Country');
						$tmpl['CLIENT_POSTCODE']	= $RS->Field('Postcode');
						$tmpl['CLIENT_PHONE1']	  = $RS->Field('Phone1');
						$tmpl['CLIENT_PHONE2']	  = $RS->Field('Phone2');
						$tmpl['CLIENT_PHONE3']	  = $RS->Field('Phone3');
						$tmpl['CLIENT_EMAIL']	   = $RS->Field('ContactEmail');
						$tmpl['CLIENT_URL']		 = $RS->Field('URL');
						$tmpl['CLIENT_FAX']		 = $RS->Field('FAX');
						$tmpl['CLIENT_COLOUR']	  = ($RS->Field('Colour') == '') ? Settings::get('DefaultColour') : $RS->Field('Colour');
						$tmpl['txtFileList'] = $files;
						$tmpl['txtContactList'] = $contacts;

					}
					$RS->Close();
					unset($RS);
				}
				else
				{
					$this->ThrowError(2001);
				}
			}

			$projectsSQL = sprintf(SQL_GET_PROJECT_IDS, $id);
			$projectsRS =& new DBRecordset();
			$projectsRS->Open($projectsSQL, $this->DB);
			$counter = 0;
			while (!$projectsRS->EOF()) {
				$usersSQL = sprintf(SQL_GET_TASK_OWNERS, $projectsRS->Field('ID'));
				$usersRS =& new DBRecordset();
				$usersRS->Open($usersSQL, $this->DB);
				while (!$usersRS->EOF()) {
					if ($managerEmail != $usersRS->Field('EmailAddress')) $users[$counter] = $usersRS->Field('EmailAddress');
					++$counter;
					$usersRS->MoveNext();
				}

				unset($usersRS);

				$usersSQL = sprintf(SQL_GET_TASK_ASSIGNED, $projectsRS->Field('ID'));
				$usersRS =& new DBRecordset();
				$usersRS->Open($usersSQL, $this->DB);
				while (!$usersRS->EOF()) {
					if ($managerEmail != $usersRS->Field('EmailAddress')) $users[$counter] = $usersRS->Field('EmailAddress');
					++$counter;
					$usersRS->MoveNext();
				}

				unset($usersRS);


				$projectsRS->MoveNext();
			}
			unset($projectsRS);

			if ($users) {
				asort($users);
				reset($users);

				while (list($key, $val) = each($users)) {
					if ($prevaddress != $val) $userlist .= ';'.$val;
					$prevaddress = $val;
				}
				$userlist = substr($userlist, 1);
			}
			else $userlist = '';

			$subject = $clientName. ' - '.MSG_ALL_PROJECTS.' - '.MSG_TEAM_NOTIFICATION;
			$actions[] = array('url' => "mailto:$managerEmail?Subject=$subject&bcc=$userlist", 'name' => MSG_EMAIL_TEAM);
			$tmpl['actions'] = $this->ActionMenu($actions);

			$tmpl['projectlist'] = $this->ProjectList();

			$tmpl['archived'] = ( Request::get( 'archived' ) == 1 ) ? MSG_ACTIVE : MSG_ARCHIVED;
			$tmpl['archivedSetting'] = ( $tmpl['archived'] == MSG_ACTIVE ) ? 0 : 1; 

			$this->setHeader($title);
			$this->setModule($modHeader, $modAction);
			$this->setTemplate($template, $tmpl);
		}
		else
		{
			$this->ThrowError(2001);
		}
		$this->Render();
	}

	function GetClientName($clientid = 0)
	{
		// get name
		$SQL = sprintf(SQL_GETCLIENTNAME, $clientid);
		$result = $this->DB->QuerySingle($SQL);
		return $result;
	}


	function ContactList()
	{
		$id = Request::get('id', Request::R_INT);

		if ($this->User->HasUserItemAccess($this->ModuleName, $id, CU_ACCESS_READ))
		{
			$name = $this->GetClientName($id);
			$modTitle = MSG_CLIENTS;
			$modHeader = $name[0] . ' ' . MSG_CONTACT.' '.MSG_LIST;
			if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE))
			{
				$modAction[0] = '<a href="index.php?module=contacts&amp;action=new&clientid='.$id.'">' . MSG_NEW_CONTACT . '</a>';
			}

			$this->ClientTabs($id, 'contacts');

		//ordering
		switch (Request::get('order'))
		{
			case 'email'   : $order = 'email'; $orderby = 'EmailAddress1'; break;
			case 'phone'   : $order = 'phone'; $orderby = 'Phone1'; break;
			default		 : $order = 'name'; $orderby = 'ContactName';
		}

		switch (Request::get('direction'))
		{
			case 'down': $direction = 'down'; $orderdir = 'DESC'; break;
			default	: $direction = 'up'; $orderdir = 'ASC';
		}
		// end ordering

		// paging code
		$limit = Settings::get('RecordsPerPage');
		$offset = Request::get('start');
		$SQL = sprintf(SQL_CONTACTS_LIST, $orderby, $orderdir, $id);
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

		$RS =& new DBPagedRecordset();
		$RS->Open($SQL, $this->DB, $limit, $offset);
		echo $this->DB->LastErrorMessage;
		if (!$RS->EOF())
		{
			$tmpl['ID']  = $id;
			$tmpl['lblContactName']  = MSG_CONTACT_NAME;
			$tmpl['lblEmailAddress'] = MSG_EMAIL_ADDRESS;
			$tmpl['lblAsc']	  = MSG_ASCENDING;
			$tmpl['lblDesc']	 = MSG_DESCENDING;
			$tmpl['start']	 = $offset;
			$this->setTemplate('contact_header', $tmpl);
			unset($tmpl);

			$counter = 1;
			while (!$RS->EOF())
			{
				if ($counter > 1) $this->setTemplate('contact_spacer');
				$tmpl['bgcolor'] = ($RS->Field('KeyContact')) ? '#ffe1e1' : '#ffffff';

				$tmpl['CONTACT_ID'] = $RS->Field('ID');
				$tmpl['CONTACT_NAME'] = $RS->Field('ContactName');
				$tmpl['CONTACT_EMAIL'] = $RS->Field('EmailAddress1');
				$tmpl['CONTACT_PHONE'] = $RS->Field('Phone1');
				$tmpl['CONTACT_LAST_CONTACT'] = Format::date($this->DB->ExecuteScalar(sprintf(SQL_GET_LAST_CONTACT,$id, $RS->Field('ID'))), Settings::get('PrettyDateFormat'));

				$this->setTemplate('contact_item', $tmpl);
				unset($tmpl);
				++$counter;
				$RS->MoveNext();
			}

			if ($RS->TotalRecords > $limit)
			{
				$url = 'index.php?module=clients&amp;action=contactlist&amp;id='.$id.'&amp;order='.$order.'&amp;direction='.$direction;
				cuPaginate($RS->TotalRecords, $limit, $url, $offset, $tmpl);
				$this->setTemplate('paging', $tmpl);
				unset($tmpl);
			}
			$this->setTemplate('contact_footer');
		}
		else
		{
			 $tmpl['MESSAGE'] = MSG_NO_CONTACTS_AVAILABLE;
				$tmpl['lblIcon'] = 'contacts';
				$this->setTemplate('eof', $tmpl);

		}
		$RS->Close();
		unset($RS);
		}
		else {
			$this->ThrowError(2001);
		}
		$this->setHeader($modTitle);
		$this->setModule($modHeader, $modAction);
		$this->Render();

	}

	function ProjectList() {
		$clientID = Request::get('id', Request::R_INT);

		if (!$this->User->HasUserItemAccess($this->ModuleName, $clientID, CU_ACCESS_READ))
			return '';

		$active = (Request::get('archived') == '1') ? 0 : 1;
		$archive = (Request::get('archived') == '1') ? 1 : 0;

		//ordering
		switch (Request::get('order'))
		{
			case 'progress': $order = 'progress'; $orderby = 'PercentComplete'; break;
			case 'priority': $order = 'priority'; $orderby = 'Priority';		break;
			case 'status':   $order = 'status';   $orderby = 'Status';		  break;
			case 'budget':   $order = 'budget';   $orderby = 'TargetBudget';	break;
			case 'actual':   $order = 'actual';   $orderby = 'ActualBudget';	break;
			case 'owner':	$order = 'owner';	$orderby = 'Owner';		   break;
			case 'latestactivity':	$order = 'latestactivity';	$orderby = 'LatestActivity';	   break;
			case 'start':	$order = 'start';	$orderby = 'StartDate';	   break;
			case 'end':	  $order = 'end';	  $orderby = 'EndDate';		 break;
			default:		 $order = 'project';  $orderby = 'ProjectName';
		}

		switch (Request::get('direction'))
		{
			case 'down': $direction = 'down'; $orderdir = 'DESC'; break;
			default:	 $direction = 'up';   $orderdir = 'ASC';
		}
		// end ordering

		// paging code
		$limit = Settings::get('RecordsPerPage');
		$offset = Request::get('start');

		// get the list of items in this object that the user has access to.
		$project_access_list = $this->User->GetUserItemAccess('projects', CU_ACCESS_READ);

		// get our SQL
		if ( $project_access_list == '-1')
			$SQL = sprintf(SQL_PROJECTS_LIST_ALL, $active, $orderby, $orderdir, $clientID);
		else
			$SQL = sprintf(SQL_PROJECTS_LIST, $project_access_list, $active, $orderby, $orderdir, $clientID);

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

		$html = '';
//		if (!$RS->EOF())
//		{
			$tmpl['ID']  = $clientID;
			$tmpl['Archived']  = Request::get('archived');
			$tmpl['start']	 = $offset;
			$html .= $this->getTemplate('project_header', $tmpl);

			$hasBudgetRead = $this->User->HasModuleItemAccess('budget', CU_ACCESS_ALL, CU_ACCESS_READ);
			while (!$RS->EOF())
			{
				// Add Invoice Other Items to the Actual Budget cost.
				$sql = sprintf(SQL_GET_PROJECT_OTHER_ITEMS_SUM, $RS->Field('ID'));
				$otherItemCost = $this->DB->ExecuteScalar($sql);

				$sql = sprintf(SQL_GET_PROJECT_CHARGE, $RS->Field('ID'));
				$charge = $this->DB->QuerySingle($sql);

				$tmpl['ProjectID'] = $RS->Field('ID');
				$tmpl['ProjectName'] = $RS->Field('ProjectName');
				$tmpl['Owner'] = $RS->Field('FirstName') . ' ' . $RS->Field('LastName');
				$tmpl['LatestActivity'] = Format::date($RS->Field('LatestActivity'));
				$tmpl['StartDate'] = Format::date($RS->Field('StartDate'));
				$tmpl['EndDate'] = Format::date($RS->Field('EndDate'));
				$tmpl['PercentComplete'] = $RS->Field('PercentComplete');
				$tmpl['ProjectColour'] = $RS->Field('Colour');
				$tmpl['Status'] = $this->StatusList[$RS->Field('Status')];
				$tmpl['Priority'] = Format::convert_priority($RS->Field('Priority'));
				$tmpl['TargetBudget'] = ($hasBudgetRead) ? Format::money($RS->Field('TargetBudget')) : MSG_NA;
				$tmpl['ChargeBudget'] = ($hasBudgetRead) ? Format::money($charge['Charge']) : MSG_NA;
				$tmpl['CostBudget'] = ($hasBudgetRead) ? Format::money($RS->Field('ActualBudget') + $otherItemCost) : MSG_NA;

				$html .= $this->getTemplate('project_item', $tmpl);
				unset($tmpl);
				$RS->MoveNext();
			}

			$tmpl['paging'] = '';
			if ($RS->TotalRecords > $limit)
			{
				$url = 'index.php?module=clients&amp;action=view&amp;id='.$clientID.'&amp;archived='.$archive.'&amp;order='.$order.'&amp;direction='.$direction;
				cuPaginate($RS->TotalRecords, $limit, $url, $offset, $tmpl);
				$tmpl['paging'] = $this->getTemplate('project_paging', $tmpl);
			}

			$html .= $this->getTemplate('project_footer', $tmpl);
//		}
//		else
//		{
//			$tmpl['MESSAGE'] = MSG_NO_PROJECTS_AVAILABLE;
//			$tmpl['lblIcon'] = 'projects';
//			$html .= $this->getTemplate('eof_projectlist', $tmpl);
//		}

		$RS->Close();
		unset($RS);

		return $html;
	}

	function FileList() {
		$id = Request::get('id', Request::R_INT);

		if ( $this->User->HasModuleItemAccess( 'files', CU_ACCESS_ALL, CU_ACCESS_READ ) && $this->User->HasUserItemAccess( $this->ModuleName, $clientID, CU_ACCESS_READ ) ) {
			$name = $this->GetClientName( $id );
			$modTitle = MSG_CLIENTS;
			$modHeader = $name[0] .' '. MSG_FILES.' '.MSG_LIST;
			$this->ClientTabs( $id, 'files' );

			$tmpl['lblFolder']	  = MSG_FOLDER;
			$tmpl['lblProjectName'] = MSG_PROJECT_NAME;
			$tmpl['lblTaskName']	= MSG_TASK_NAME;
			$tmpl['lblFileName']	= MSG_FILE_NAME;
			$tmpl['lblLastChange']  = MSG_LAST_CHANGE;
			$tmpl['lblStatus']	  = MSG_STATUS;
			$tmpl['lblAction']	  = MSG_ACTION;
			$tmpl['lblAsc']		 = MSG_ASCENDING;
			$tmpl['lblDesc']		= MSG_DESCENDING;
			$tmpl['id']			 = $id;
			$tmpl['order']		  = Request::get('order');
			$tmpl['direction']	  = Request::get('direction');
			$this->setTemplate( 'files_header', $tmpl );
			unset( $tmpl );

			// Determine display order for folders.
			$dir = (Request::get('direction') == 'down') ? 'DESC' : 'ASC';
			switch (Request::get('order'))
			{
				case 'folder':	  $orderBy = "fo.Folder $dir, p.Name ASC"; break;
				case 'projectname': $orderBy = "p.Name $dir, fo.Folder ASC"; break;
				default:			$orderBy = "fo.Folder ASC, p.Name ASC"; break;
			}

			if ( $this->User->HasModuleItemAccess( 'administration', CU_ACCESS_ALL, CU_ACCESS_READ ) ) {
				$sql = sprintf( SQL_GET_FOLDERS, $id, $orderBy );
				$sqlUnsorted = sprintf( SQL_COUNT_FILES_IN_FOLDER, 0, $id );
			} else {
				$projectsAccessList = $this->User->GetUserItemAccess( 'projects', CU_ACCESS_READ );
				$sql = sprintf( SQL_GET_FOLDERS_FOR_PROJECTS, $id, $projectsAccessList, $orderBy );
				$sqlUnsorted = sprintf( SQL_COUNT_FILES_IN_FOLDER_FOR_PROJECTS, 0, $id, $projectsAccessList );
			}

			$result = $this->DB->Query( $sql );
			foreach ( $result as $index => $row ) {
				$item_tmpl = array();
				$item_tmpl['ID']			 = $row['ID'];
				$item_tmpl['txtFileFolder']  = $row['Folder'];
				$item_tmpl['txtFileName']	= ( $row['count'] == 1 ) ? $row['count'].' '.MSG_FILE : $row['count'].' '.MSG_FILES;
				$item_tmpl['txtProjectID']   = $row['ProjectID'];
				$item_tmpl['txtProjectName'] = $row['Name'];
				$item_tmpl['txtTaskID']	  = NULL;
				$item_tmpl['txtTaskName']	= NULL;
				$item_tmpl['txtStatus']	  = NULL;
				$item_tmpl['txtLastChange']  = NULL;
				$item_tmpl['txtAction']	  = NULL;
				$item_tmpl['icon']		   = 'folder';
				$item_tmpl['txtStatusRollover'] = NULL;
				$item_tmpl['txtRolloverDescription'] = NULL;

				$tmpl = array();
				$tmpl['ID'] = $row['ID'];
				$tmpl['ClientID'] = $id;
				$tmpl['items'] = $this->getTemplate( 'files_item', $item_tmpl );

				$this->setTemplate( 'files_content', $tmpl );

				// Set a spacer if we aren't on the last folder.
				if ( $index != ( count( $result ) - 1 ) )  
					$this->setTemplate( 'files_spacer' );
			}

			// Display folderless files
			$fileCount = $this->DB->ExecuteScalar( $sqlUnsorted );
			if ( $fileCount > 0 ) {
				if ($row['count'] > 0)
					$this->setTemplate( 'files_spacer' );

				$item_tmpl = array();
				$item_tmpl['ID']			 = 0;
				$item_tmpl['txtFileFolder']  = MSG_UNSORTED;
				$item_tmpl['txtFileName']	= ( $fileCount == 1 ) ? $fileCount.' '.MSG_FILE : $fileCount.' '.MSG_FILES;
				$item_tmpl['txtProjectID']   = NULL;
				$item_tmpl['txtProjectName'] = NULL;
				$item_tmpl['txtTaskID']	  = NULL;
				$item_tmpl['txtTaskName']	= NULL;
				$item_tmpl['txtStatus']	  = NULL;
				$item_tmpl['txtLastChange']  = NULL;
				$item_tmpl['txtAction']	  = NULL;
				$item_tmpl['icon']		   = 'folder';
				$item_tmpl['txtStatusRollover'] = NULL;
				$item_tmpl['txtRolloverDescription'] = NULL;

				$tmpl = array();
				$tmpl['ID'] = 0;
				$tmpl['ClientID'] = $id;
				$tmpl['items'] = $this->getTemplate( 'files_item', $item_tmpl );
				$this->setTemplate( 'files_content', $tmpl );
			}

			if ( ( count( $result ) + $fileCount ) == 0 ) {
				$tmpl = array();
				$tmpl['message'] = MSG_NO_FILES_AVAILABLE;
				$this->setTemplate( 'no_files', $tmpl );
			}

			$this->setTemplate( 'files_footer' );

			$header = '';
			$this->setHeader( $modTitle, $header );
			$this->setModule( $modHeader );
			$this->Render();
		} else {
			$this->ThrowError( 2001 );
		}
	}

	function ShowFiles() {
		$id = Request::get( 'id' );
		$folderID = Request::get( 'folderid' );

		// Determine display order for files.
		$dir = (Request::get('direction') == 'down') ? 'DESC' : 'ASC';
		switch (Request::get('order'))
		{
			case 'projectname': $orderBy = "p.Name $dir, f.FileName ASC"; break;
			case 'taskname':	$orderBy = "TaskName $dir, f.FileName ASC"; break;
			case 'file':		$orderBy = "f.FileName $dir, p.Name ASC"; break;
			case 'lastchange':  $orderBy = "f.Date $dir, f.FileName ASC"; break;
			case 'status':	  $orderBy = "f.CheckedOut $dir, f.FileName ASC"; break;
			default:			$orderBy = "f.FileName ASC, p.Name ASC"; break;
		}

		if ( $this->User->HasModuleItemAccess( 'files', CU_ACCESS_ALL, CU_ACCESS_READ ) && $this->User->HasUserItemAccess( $this->ModuleName, $clientID, CU_ACCESS_READ ) ) {
			$folderName = ( $folderID == 0 ) ? MSG_UNSORTED : $this->DB->ExecuteScalar( sprintf( SQL_GET_FOLDER_NAME, $folderID ) );
			$content = $this->getTemplate( 'files_spacer' );

			if ( $this->User->HasModuleItemAccess( 'administration', CU_ACCESS_ALL, CU_ACCESS_READ ) ) {
				$sql = sprintf( SQL_GET_FILES_IN_FOLDER, $folderID, $id, $orderBy );
			} else {
				$projectsAccessList = $this->User->GetUserItemAccess( 'projects', CU_ACCESS_READ );
				$sql = sprintf( SQL_GET_FILES_IN_FOLDER_FOR_PROJECTS, $folderID, $id, $projectsAccessList, $orderBy );
			}

			$result = $this->DB->Query( $sql );
			foreach ( $result as $index => $row ) {
				$lastCheckedOut = $this->DB->QuerySingle( sprintf( SQL_LAST_CHECKED_OUT, $row['ID'] ) );

				$tmpl = array();
				$tmpl['txtFileID']	  = $row['ID'];
				$tmpl['txtFileFolder']  = $folderName;
				$tmpl['txtProjectID']   = $row['ProjectID'];
				$tmpl['txtProjectName'] = $row['Project'];
				$tmpl['txtTaskID']	  = $row['TaskID'];
				$tmpl['txtTaskName']	= $row['TaskName'];
				$tmpl['txtLastChange']  = Format::date( $row['Date'], Settings::get('PrettyDateFormat') );
				$tmpl['icon']		   = $this->selectImage( $row['Type'] );
				$tmpl['txtRolloverDescription'] = "Uploaded By: {$row['FirstName']} {$row['LastName']}\n\n{$row['Description']}";

				if ( $row['Linked'] == 1 ) {
					$tmpl['txtFileName'] = '<a href="'.$row['RealName'].'" target="_new">'.$row['FileName'].'</a>';
					$tmpl['txtSize'] = MSG_NA;
				}
				else {
					$tmpl['txtFileName'] = '<a href="index.php?module=projects&fileid='.$row['ID'].'&projectid='.$row['ProjectID'].'&taskid='.$row['TaskID'].'&action=filedown" target="_new">'.$row['FileName'].'</a>';
					$tmpl['txtSize'] = intval( $value['Size'] / 1024 ) . MSG_KB;
				}

				$tmpl['txtStatusRollover'] = '';
				$tmpl['txtAction'] = '<select name="ProjectID" onchange="if (this.options[this.selectedIndex].value != 0) { openWindow(\''.$row['ID'].'\',\''.$row['ProjectID'].'\',\''.$row['TaskID'].'\', this.options[this.selectedIndex].value);}">';
				$tmpl['txtAction'] .= '<option value="0">Select...</option>';

				if ( $row['Linked'] == 1 ) {
					$tmpl['txtStatus'] = 'Available';
					$tmpl['txtAction'] .= '<option value="filedetails">File Details</option>';
				}
				else if ( $row['CheckedOut'] == 0 ) {
					$tmpl['txtStatus'] = 'Available';
					$tmpl['txtAction'] .= '<option value="filedetails">File Details</option>';
					$tmpl['txtAction'] .= '<option value="filemove">Move File</option>';
					$tmpl['txtAction'] .= '<option value="filedown&checkout=1">Check Out</option>';
					$tmpl['txtAction'] .= '<option value="filedel">Delete</option>';
				}
				else if ( ( $row['CheckedOut'] == 1 ) && ( ( $row['CheckedOutUserID'] == $this->User->ID ) || ( $this->User->HasModuleItemAccess( 'administration', CU_ACCESS_ALL, CU_ACCESS_READ ) ) ) ) {
					$tmpl['txtStatus'] = 'Checked Out';
					$tmpl['txtStatusRollover'] = 'By: '.$lastCheckedOut['Name']. ' at '.$lastCheckedOut['Time'];
					$tmpl['txtAction'] .= '<option value="fileedit">Check In</option>';
				}
				else {
					$tmpl['txtStatus'] = 'Checked Out';
					$tmpl['txtStatusRollover'] = 'By: '.$lastCheckedOut['Name']. ' at '.$lastCheckedOut['Time'];
					$tmpl['txtAction'] = '';
				}

				$tmpl['txtAction'] .= ( $tmpl['txtAction'] != '' ) ? '</select>' : '';

				if ( !$this->User->HasUserItemAccess( 'projects', $row['ProjectID'], CU_ACCESS_WRITE ) ) 
					$tmpl['txtAction'] = '';

				$content .= $this->getTemplate( 'files_item', $tmpl );

				if ( $index != ( count( $result ) - 1 ) )  
					$content .= $this->getTemplate( 'files_spacer' );
			}

			$content = '<table border="0" cellpadding="0" cellspacing="0">'.$content.'</table>';
			echo $content;
		} else {
			$this->ThrowError( 2001 );
		}
	}
 
	function HideFiles() {
		$id = Request::get( 'id' );
		$folderID = Request::get( 'folderid' );

		if ( $this->User->HasModuleItemAccess( 'files', CU_ACCESS_ALL, CU_ACCESS_READ ) && $this->User->HasUserItemAccess( $this->ModuleName, $clientID, CU_ACCESS_READ ) ) {
			if ( $folderID == 0 ) {
				$folderName  = MSG_UNSORTED;
				$projectID   = NULL;
				$projectName = NULL;
			} else {
				$row = $this->DB->QuerySingle( sprintf( SQL_GET_FOLDER_DETAILS, $folderID ) );
				$folderName  = $row['Folder'];
				$projectID   = $row['ProjectID'];
				$projectName = $row['Name'];
			}

			if ( $this->User->HasModuleItemAccess( 'administration', CU_ACCESS_ALL, CU_ACCESS_READ ) ) {
				$sql = sprintf( SQL_COUNT_FILES_IN_FOLDER, $folderID, $id );
			} else {
				$projectsAccessList = $this->User->GetUserItemAccess( 'projects', CU_ACCESS_READ );
				$sql = sprintf( SQL_COUNT_FILES_IN_FOLDER_FOR_PROJECTS, $folderID, $id, $projectsAccessList );
			}

			$fileCount = $this->DB->ExecuteScalar( $sql );

			// Display folder name and file count
			$item_tmpl['ID']			 = $folderID;
			$item_tmpl['txtFileFolder']  = $folderName;
			$item_tmpl['txtFileName']	= ( $fileCount == 1 ) ? $fileCount.' '.MSG_FILE : $fileCount.' '.MSG_FILES;
			$item_tmpl['txtProjectID']   = $projectID;
			$item_tmpl['txtProjectName'] = $projectName;
			$item_tmpl['txtTaskID']	  = NULL;
			$item_tmpl['txtTaskName']	= NULL;
			$item_tmpl['txtStatus']	  = NULL;
			$item_tmpl['txtLastChange']  = NULL;
			$item_tmpl['txtAction']	  = NULL;
			$item_tmpl['icon']		   = 'folder';

			$content = '<table border="0" cellpadding="0" cellspacing="0">';
			$content .= $this->getTemplate( 'files_item', $item_tmpl );
			$content .= '</table>';

			echo $content;
		} else {
			$this->ThrowError( 2001 );
		}
	}

	function FileDownload() {
		$fileid = Request::get('fileid', Request::R_INT);
		$clientid = Request::get('clientid', Request::R_INT);
		$file = $this->DB->QuerySingle( sprintf( SQL_GET_FILE_DETAILS, $fileid ) );

		if ($this->User->HasModuleItemAccess('files', CU_ACCESS_ALL, CU_ACCESS_READ) ) {
			$file = new File($fileid);
			if ($file->download()) {
				if (($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE)) && (Request::get('checkout') == 1)) {
					$SQL = sprintf(SQL_FILE_CHECKOUT, $fileid, $this->User->ID);
					$this->DB->Execute($SQL);
					$SQL = sprintf(SQL_FILE_LOG, $fileid, $this->User->ID, date('Y-m-d H:i:s'), MSG_CHECKED_OUT, $file->Version);
					$this->DB->Execute($SQL);

				}
				else {
					$SQL = sprintf(SQL_FILE_LOG, $fileid, $this->User->ID, date('Y-m-d H:i:s'), MSG_VIEWED, $file->Version);
					$this->DB->Execute($SQL);
				}

				return;
			}
		}
		else {
		   Response::redirect('index.php?module=clients&action=filelist&id='.$clientid);
		}
	}

	function DeleteClient()
	{
		$id = Request::get('id', Request::R_INT);
		$tmpl['MESSAGE'] = MSG_USER_NOT_FOUND;
		$template = 'message';
		$title = MSG_CLIENTS;
		$breadcrumbs = MSG_DELETE;

		if (is_numeric($id))
		{
			if ($this->User->HasUserItemAccess($this->ModuleName, $id, CU_ACCESS_WRITE))
			{
				$confirm = Request::get('confirm');
				if ($confirm == 1)
				{
					$projects = $this->DB->Query(sprintf(SQL_GET_PROJECT_IDS, $id));
					if ($projects) {
						//For each project
						foreach ($projects as $key => $value) {
							$projectid = $value[ID];
							//Select tasks
							$tasks = $this->DB->Query(sprintf(SQL_GET_TASK_IDS, $projectid));
							if ($tasks) {
								foreach ($tasks as $task_key => $task_value) {
									$taskid = $task_value[ID];
									$rows = $this->DB->Query(sprintf(SQL_GET_HOURS_COMMITTED_ON_TASK_FOR_TOTAL_PROJECT, $taskid));
									foreach($rows as $key => $value)
										$this->DB->Execute(sprintf(SQL_UPDATE_RESOURCE_DAY_COMMITMENT_CACHE, $value['ResourceID'], $value['DayID'], 0 - $value['HoursCommitted']));
									//Delete all task_delegations
									$this->DB->Execute(sprintf(SQL_DELETE_DELEGATED_TASKS, $taskid));
									//Delete all task_comments
									$this->DB->Execute(sprintf(SQL_DELETE_TASK_COMMENTS, $taskid));
									//Delete all task_dependencies
									$this->DB->Execute(sprintf(SQL_DELETE_TASK_DEPENDENCIES, $taskid));
									//Delete all task resources
									$this->DB->Execute(sprintf(SQL_DELETE_TASK_RESOURCES, $taskid));
									//Delete task
									$this->DB->Execute(sprintf(SQL_DELETE_TASK, $taskid));
								}
							}
							//Select files

							$files = $this->DB->Query(sprintf(SQL_GET_FILE_IDS, $projectid));
							if ($files) {
								foreach ($files as $file_key => $file_value) {
									$fileid = $file_value[ID];
									//Delete all file log
									$this->DB->Execute(sprintf(SQL_DELETE_FILE_LOGS, $fileid));
									//Delete file (DB)
									$this->DB->Execute(sprintf(SQL_DELETE_FILE, $fileid));
								}
							}

							//Delete project permissions
							$this->DB->Execute(sprintf(SQL_DELETE_GROUP_PERMS, 'projects', $projectid));
							$this->DB->Execute(sprintf(SQL_DELETE_USER_PERMS, 'projects', $projectid));

							//Delete project directory

							$project_dir = str_pad($id, 7, '0', STR_PAD_LEFT);
							// trailing slash - checks for trailing slash, just incase someone didn't put it in
							$trailer = substr(SYS_FILEPATH, strlen(SYS_FILEPATH) - 1, 1);
							if ( ($trailer != '\\') && ($trailer != '/') )
							{
								$filepath = SYS_FILEPATH . '/';
							}
							else
							{
								$filepath = SYS_FILEPATH;
							}
							//~ trailing slash
							$project_path = $filepath . $project_dir . '/';
							@delete_all_from_dir($project_path);

							//Delete project
							$this->DB->Execute(sprintf(SQL_DELETE_PROJECT, $projectid));
						}
					}
					//Delete client permissions
					$this->DB->Execute(sprintf(SQL_DELETE_GROUP_PERMS, 'clients', $id));
					$this->DB->Execute(sprintf(SQL_DELETE_USER_PERMS, 'clients', $id));

					//Delete client contacts
					$this->DB->Execute(sprintf(SQL_DELETE_CONTACTS, $id));

					//Delete client
					$this->DB->Execute(sprintf(SQL_DELETE_CLIENT, $id));

					Response::redirect('index.php?module=clients');
				}
				else
				{
					$SQL = sprintf(SQL_GETCLIENTNAME, $id);
					$rs  = $this->DB->QuerySingle($SQL);
					if (is_array($rs))
					{
						$tmpl['ID']		 = $id;
						$tmpl['MESSAGE'] = sprintf(MSG_DELETE_CLIENT_WARNING, $rs['Name']);
						$tmpl['YES']	 = MSG_YES;
						$tmpl['NO']		 = MSG_NO;
						$template		 = 'delete';
					}
				}
			}
			else
			{
				$this->ThrowError(2001);
			}
		}

		$this->setHeader($title);
		$this->setModule($breadcrumbs);
		$this->setTemplate($template, $tmpl);

		$this->Render();
	}

	function GanttChart() {
		$clientID = $id = Request::get('id', Request::R_INT);

		if ($this->User->HasUserItemAccess($this->ModuleName, $clientID, CU_ACCESS_READ)) {

			// SQL_GET_PROJECT_IDS
			$SQL = sprintf(SQL_GET_ACTIVE_PROJECT_IDS,$clientID);
			$projectIDs = $this->DB->Query($SQL);

			$projectid = '';
			for ($i = 0; $i < count($projectIDs); $i++) $projectid .= ($i ? ',' : '') . $projectIDs[$i]['ID'];

			$this->ClientTabs($id, 'timeline');

			// if ($this->User->HasUserItemAccess($this->ModuleName, $projectIDs[0]['ID'], CU_ACCESS_READ)) {

			$name = $this->GetClientName($clientID);

			$modTitle = MSG_CLIENTS;
			$modHeader = $name[0].' '.MSG_GANTT_CHART;

			//$this->ProjectStatus($details);
			// $this->ProjectTabs($projectid, 'gantt');

			// $SQL = sprintf(SQL_GET_TASKLIST,$projectid);
			// $tasks = $this->DB->Query($SQL);
			// $tasksCount = 0;
			// if (is_array($tasks)) {
				// $tasksCount = count($tasks);
			// }
			$tmpl['ProjectID'] = $projectid;
			$this->setTemplate('gantt', $tmpl);
			unset($tmpl);

			$this->setHeader($modTitle);
			$this->setModule($modHeader, $modAction);
			$this->Render();

			echo $this->DB->LastErrorMessage;
		}
		else
		{
			$this->ThrowError(2001);
		}
	}

	// begin modified code by Orca 2006-11 for new Gantt chart
	function GanttData() {

		$projectids = Request::get('projectids');
		$projectid = Request::get('projectid', Request::R_INT);
		if ($projectid) $projectids = $projectid;

		// $projectIDs = explode(',',$projectids);

		$oldProjectIDs = explode(',',$projectids);
		$projectIDs = array();
		for ($i = 0; $i < count($oldProjectIDs); $i++) {
		// echo 'pid: ' . $oldProjectIDs[$i] . ' acc: ' . $this->User->HasUserItemAccess('projects',$oldProjectIDs[$i],CU_ACCESS_READ) . '<br>';
			if ($this->User->HasUserItemAccess('projects',$oldProjectIDs[$i],CU_ACCESS_READ)) {
				$projectIDs[count($projectIDs)] = $oldProjectIDs[$i];
			}
		}
		if (!count($projectIDs)) die('error=AccessDenied');

		// if ($this->User->HasUserItemAccess('projects',$projectid,CU_ACCESS_READ)) {

			// get the min and max task start/end dates for all of the projects
			$whereProjectIDOR = '';
			for ($i = 0; $i < count($projectIDs); $i++) {
				$whereProjectIDOR .= ' OR ProjectID = ' . $projectIDs[$i];
			}

			$SQL = sprintf(SQL_GET_MIN_MAX_TASK_DATES,$whereProjectIDOR);
			$minMaxTaskDates = $this->DB->QuerySingle($SQL);
			if ($minMaxTaskDates['dateEnd'] == '0000-00-00') $minMaxTaskDates['dateEnd'] = $minMaxTaskDates['dateStart'];
			$dateStart = strtotime($minMaxTaskDates['dateStart']);
			$dateEnd = strtotime($minMaxTaskDates['dateEnd']);

			$projectCount = '&projectCount=' . count($projectIDs);

			$projectNames = '&projectNames=';
			$projectDatesStart = '&projectDatesStart=';
			$projectDatesEnd = '&projectDatesEnd=';
			$projectColours = '&projectColours=0x';
			$projectTasksCount = '&projectTasksCount=';

			$projectTasksID = '&projectTasksID=';
			$projectTasksName = '&projectTasksName=';
			$projectTasksOwner = '&projectTasksOwner=';
			$projectTasksDateStart = '&projectTasksDateStart=';
			$projectTasksDateEnd = '&projectTasksDateEnd=';
			$projectTasksDuration = '&projectTasksDuration=';
			$projectTasksSequence = '&projectTasksSequence=';
			$projectTasksDependencyIDs = '&projectTasksDependencyIDs=';
			$projectTasksDependencyTypes = '&projectTasksDependencyTypes=';

			$projectTasksDaysCount = '&projectTasksDaysCount=';
			$projectTasksTodayIndex = '&projectTasksTodayIndex=';
			$projectTasksTotalHoursCommittedBefore = '&projectTasksTotalHoursCommittedBefore=';
			$projectTasksTotalHoursCompletedBefore = '&projectTasksTotalHoursCompletedBefore=';
			$projectTasksTotalHoursCommittedAfter = '&projectTasksTotalHoursCommittedAfter=';
			$projectTasksTotalHoursCompletedAfter = '&projectTasksTotalHoursCompletedAfter=';

			$projectTasksResourcesCount = '&projectTasksResourcesCount=';

			// begin almighty projects loop
			for ($i = 0; $i < count($projectIDs); $i++) {
				// get the name and start/end of this project
				$projectSQL = sprintf(SQL_GET_PROJECT_NAME_DATES_COLOUR,$projectIDs[$i]);
				$project = $this->DB->QuerySingle($projectSQL);

				$colour = $project['Colour'];
				if (substr($project['Colour'],0,1) == '#') $project['Colour'] = substr($project['Colour'],1);
				// $project['Colour'] = '3399ff';

				// if the project start/end are beyond the task dates use them instead
				if (strtotime($project['StartDate']) < $dateStart) $dateStart = strtotime($project['StartDate']);
				if (strtotime($project['EndDate']) > $dateEnd) $dateEnd = strtotime($project['EndDate']);

				if (strlen($project['Name']) > 28) $project['Name'] = substr($project['Name'],0,27).'..';
				$projectNames .= ($i ? ',' : '') . urlencode($project['Name']);
				$projectDatesStart .= ($i ? ',' : '') . $project['StartDate'];
				$projectDatesEnd .= ($i ? ',' : '') . $project['EndDate'];
				$projectColours .= ($i ? ',0x' : '').$project['Colour'];

				// clear the lists for the next project
				$tasksIDList = '';
				$tasksNameList = '';
				$tasksOwnerList = '';
				$tasksDateStartList = '';
				$tasksDateEndList = '';
				$tasksDurationList = '';
				$tasksSequenceList = '';
				$tasksDependencyIDsList = '';
				$tasksDependencyTypesList = '';
				$tasksTodayIndexList = '';
				$tasksDaysCountList = '';
				$tasksResourcesCountList = '';

				$tasksTotalHoursCommittedBeforeList = '';
				$tasksTotalHoursCompletedBeforeList = '';
				$tasksTotalHoursCommittedAfterList = '';
				$tasksTotalHoursCompletedAfterList = '';

				$SQL = sprintf(SQL_GET_TASKLIST_GANTT,$projectIDs[$i]);
				$tasks = $this->DB->Query($SQL);

				$tasksCount = 0;
				if (is_array($tasks)) {
					$tasksCount = count($tasks);
					for ($j = 0; $j < count($tasks); $j++) {
						$tasksIDList .= ($j ? ',' : '') . $tasks[$j]['ID'];
						if ($tasks[$j]['DateEnd'] == '0000-00-00') $tasks[$j]['DateEnd'] = $tasks[$j]['DateStart'];
						// if (strlen($tasks[$j]['Name']) > 28) $tasks[$j]['Name'] = substr($tasks[$j]['Name'],0,27).'..';
						$tasksNameList .= ($j ? ',' : '') . urlencode($tasks[$j]['Name']);
						$tasksOwnerList .= ($j ? ',' : '') . urlencode($tasks[$j]['Owner']);
						$tasksDateStartList .= ($j ? ',' : '') . urlencode($tasks[$j]['DateStart']);
						$tasksDateEndList .= ($j ? ',' : '') . urlencode($tasks[$j]['DateEnd']);
						$tasksDurationList .= ($j ? ',' : '') . urlencode($tasks[$j]['Duration']);

					// $commentsSQL = sprintf(SQL_TASKS_GET_COMMENTS,$tasks[$j]['ID']);
					// $commentsList = $this->DB->Query($commentsSQL);
					// $taskHoursWorked = 0;

					// if ( is_array($commentsList) ) {
						// for ($k = 0; $k < count($commentsList); $k++) $taskHoursWorked += $commentsList[$k]['HoursWorked'];
					// }

						$tasksSequenceList .= ($j ? ',' : '') . urlencode($tasks[$j]['Sequence']);
						$dependenciesSQL = sprintf(SQL_PROJECT_TASK_DEPENDENCIES,$tasks[$j]['ID']);
						$dependencies = $this->DB->Query($dependenciesSQL);
						$dependencyIDsList = '';
						$dependencyTypesList = '';
						if (is_array($dependencies)) {
							for ($k = 0; $k < count($dependencies); $k++) {
								$dependencyIDsList .= ($dependencyIDsList ? ',' : '') . $dependencies[$k][0];
								$dependencyTypesList .= ($dependencyTypesList ? ',' : '') . $dependencies[$k][1];
							}
						}
						else {
							$dependencyIDsList = '0';
							$dependencyTypesList = '0';
						}

						$tasksDependencyIDsList .= ($j ? ';' : '') . $dependencyIDsList;
						$tasksDependencyTypesList .= ($j ? ';' : '') . $dependencyTypesList;

						// get the start and end dates of this task
						$taskDateSQL = sprintf(GET_TASK_DATES,$tasks[$j]['ID']);
						$taskDate = $this->DB->QuerySingle($taskDateSQL);

						// convert the start date to epoch
						$taskDate['StartDate'] = strtotime($taskDate['StartDate']);
						// check for no end date
						if ($taskDate['EndDate'] == '0000-00-00') $taskDate['EndDate'] = $taskDate['StartDate'];
						else $taskDate['EndDate'] = strtotime($taskDate['EndDate']);

						// find the ids for that date range
						$taskDaysSQL = sprintf(SQL_GET_DAYID_EPOCH,$taskDate['StartDate'],$taskDate['EndDate']);
						$taskDays = $this->DB->Query($taskDaysSQL);
						$tasksDaysCountList .= ($j ? ',' : '') . count($taskDays);

						// get all the resources for this task
						$resourcesSQL = sprintf(SQL_GET_TASK_RESOURCES,$tasks[$j]['ID']);
						$resources = $this->DB->Query($resourcesSQL);
						if ($resources[0]['ID']) $tasksResourcesCountList .= ($j ? ',' : '') . count($resources);
						else $tasksResourcesCountList .= ($j ? ',0' : '0');

						// get the index of today in the days data arrays
						$currentEpoch = time();
						$todayIndexValue = 0;
						if ($currentEpoch < $taskDays[0]['Epoch']) $todayIndexValue = 0;
						else if ($currentEpoch > $taskDays[count($taskDays) - 1]['Epoch']) $todayIndexValue = count($taskDays);
						else {
							for ($k = 0; $k < count($taskDays); $k++) {
								if ($currentEpoch >= $taskDays[$k]['Epoch'] && $currentEpoch < $taskDays[$k + 1]['Epoch']) $todayIndexValue = $k;
							}
						}
						$tasksTodayIndexList .= ($j ? ',' : '') . $todayIndexValue;

						// get the total hours completed Before today, hours comitted Before today and hours completed after today
						$resourceAvailabilityWhereInRangeTableTRD = 'tblTaskResourceDay.DayID >= ' . $taskDays[0]['DayID'] . ' AND tblTaskResourceDay.DayID  <= ' . $taskDays[count($taskDays) - 1]['DayID'];
						$hoursCommittedSQL = sprintf(SQL_GET_HOURS_COMMITTED_ON_TASK_FOR_TOTAL,$tasks[$j]['ID'],$resourceAvailabilityWhereInRangeTableTRD);
						$resourceHoursCommitted = $this->DB->Query($hoursCommittedSQL);


						$totalHoursCommittedBefore = 0;
						$totalHoursCompletedBefore = 0;
						$totalHoursCommittedAfter = 0;
						$totalHoursCompletedAfter = 0;

						for ($k = 0; $k < count($resourceHoursCommitted); $k++) {
							if ($resourceHoursCommitted[$k]['DayID'] < $taskDays[$todayIndexValue]['DayID'] || $todayIndexValue == count($taskDays)) {
								$totalHoursCommittedBefore += $resourceHoursCommitted[$k]['HoursCommitted'];
								$totalHoursCompletedBefore += $resourceHoursCommitted[$k]['HoursCompleted'];
							}
							else {
								$totalHoursCommittedAfter += $resourceHoursCommitted[$k]['HoursCommitted'];
								$totalHoursCompletedAfter += $resourceHoursCommitted[$k]['HoursCompleted'];
							}
						}

						$tasksTotalHoursCommittedBeforeList .= ($j ? ',' : '') . $totalHoursCommittedBefore;
						$tasksTotalHoursCompletedBeforeList .= ($j ? ',' : '') . $totalHoursCompletedBefore;
						$tasksTotalHoursCommittedAfterList .= ($j ? ',' : '') . $totalHoursCommittedAfter;
						$tasksTotalHoursCompletedAfterList .= ($j ? ',' : '') . $totalHoursCompletedAfter;
					}
				} // end tasks loop

				$projectTasksCount .= ($i ? ',' : '') . $tasksCount;

				$projectTasksID .= ($i ? ';' : '') . $tasksIDList;
				$projectTasksName .= ($i ? ';' : '') . $tasksNameList;
				$projectTasksOwner .= ($i ? ';' : '') . $tasksOwnerList;
				$projectTasksDateStart .= ($i ? ';' : '') . $tasksDateStartList;
				$projectTasksDateEnd .= ($i ? ';' : '') . $tasksDateEndList;
				$projectTasksDuration .= ($i ? ';' : '') . $tasksDurationList;
				$projectTasksSequence .= ($i ? ';' : '') . $tasksSequenceList;
				$projectTasksDependencyIDs .= ($i ? '|' : '') . $tasksDependencyIDsList;
				$projectTasksDependencyTypes .= ($i ? '|' : '') . $tasksDependencyTypesList;
				$projectTasksTodayIndex .= ($i ? ';' : '') . $tasksTodayIndexList;
				$projectTasksDaysCount .= ($i ? ';' : '') . $tasksDaysCountList;
				$projectTasksResourcesCount .= ($i ? ';' : '') . $tasksResourcesCountList;

				$projectTasksTotalHoursCommittedBefore .= ($i ? ';' : '') . $tasksTotalHoursCommittedBeforeList;
				$projectTasksTotalHoursCompletedBefore .= ($i ? ';' : '') . $tasksTotalHoursCompletedBeforeList;
				$projectTasksTotalHoursCommittedAfter .= ($i ? ';' : '') . $tasksTotalHoursCommittedAfterList;
				$projectTasksTotalHoursCompletedAfter .= ($i ? ';' : '') . $tasksTotalHoursCompletedAfterList;

			} // end projects loop

			// $today = '2003-05-01';
			$today = date('Y-m-d');
			$chartDatesBegin = '&chartDatesBegin='.date('Y-m-d',$dateStart);
			$datesCount = '&datesCount=' . round( ($dateEnd - $dateStart) / 86400);
			$maxDayLength = '&maxDayLength=' . MAX_DAY_LENGTH;
			$dayLength = '&dayLength='.DAY_LENGTH;

			echo 'today='.$today.$projectCount.$projectNames.$projectDatesStart.$projectDatesEnd.$projectColours.$dayLength.$maxDayLength.$datesCount.$projectTasksCount;
			echo $chartDatesBegin.$projectTasksID.$projectTasksName.$projectTasksOwner.$projectTasksDateStart.$projectTasksDateEnd.$projectTasksDuration.$projectTasksSequence.$projectTasksDependencyIDs.$projectTasksDependencyTypes;
			echo $projectTasksTodayIndex . $projectTasksDaysCount . $projectTasksTotalHoursCommittedBefore	. $projectTasksTotalHoursCompletedBefore . $projectTasksTotalHoursCommittedAfter . $projectTasksTotalHoursCompletedAfter . $projectTasksResourcesCount;

	} // end function
	// begin new code by Niveus 2005-05 for new Gantt chart

	function GanttSave() {
		$projectid = Request::get('projectid', Request::R_INT);

		if ($this->User->HasUserItemAccess('projects',$projectid,CU_ACCESS_WRITE)) {

			if (Request::get('projectDateStart') && Request::get('projectDateEnd')) {
				// move project

				// get the current project start/ end dates
				$projectSQL = sprintf(SQL_GET_PROJECT_NAME_DATES_COLOUR,$projectid);
				$project = $this->DB->QuerySingle($projectSQL);
				$project['StartDate'] = strtotime($project['StartDate']);
				$project['EndDate'] = strtotime($project['EndDate']);

				// get the relativie number of days the project has been moved
				$newProjectStartDate = strtotime(Request::get('projectDateStart'));
				$newProjectEndDate = strtotime(Request::get('projectDateEnd'));
				$daysDifference = round(($newProjectStartDate - $project['StartDate']) / 60 / 60 / 24);

				// get a list of all the projects tasks and start/ end dates
				$projectsTaskDatesSQL = sprintf(SQL_GET_PROJECTS_TASKS_START_END_DATE,$projectid);
				$projectsTaskDates = $this->DB->Query($projectsTaskDatesSQL);

				// move all of the tasks
				for ($i = 0; $i < count($projectsTaskDates); $i++) {

					$projectsTaskDates[$i]['StartDate'] = strtotime($projectsTaskDates[$i]['StartDate']);
					$newTaskStartDate = date('Y-m-d',mktime(0,0,0,date('m',$projectsTaskDates[$i]['StartDate']),date('d',$projectsTaskDates[$i]['StartDate']) + $daysDifference,date('Y',$projectsTaskDates[$i]['StartDate'])));

					// milestones dont have a end date set
					if ($projectsTaskDates[$i]['EndDate'] != '0000-00-00') {
						$projectsTaskDates[$i]['EndDate'] = strtotime($projectsTaskDates[$i]['EndDate']);
						$newTaskEndDate = date('Y-m-d',mktime(0,0,0,date('m',$projectsTaskDates[$i]['EndDate']),date('d',$projectsTaskDates[$i]['EndDate']) + $daysDifference,date('Y',$projectsTaskDates[$i]['EndDate'])));
					}
					else $newTaskEndDate = '0000-00-00';

					$updateTaskDatesSQL = sprintf(SQL_UPDATE_TASK_DATES,$newTaskStartDate,$newTaskEndDate,$projectsTaskDates[$i]['ID']);
					$this->DB->Execute($updateTaskDatesSQL);
				}
				// update the start/ end dates of the project
				$updateTaskDatesSQL = sprintf(SQL_UPDATE_PROJECT_DATES,Request::get('projectDateStart'),Request::get('projectDateEnd'),$projectid);
				$this->DB->Execute($updateTaskDatesSQL);

				// tell flash to reload
				echo 'reloadDataNeeded=YES&';
			}
			else if (Request::get('projectDateEnd')) {
				// change project length
				$SQL = sprintf(SQL_GANTT_PROJECT_ENDDATE_SAVE,$projectid,Request::get('projectDateEnd'));
				$this->DB->Execute($SQL);
			}

			if (Request::get('newTask') == 1) {	// new task
				// create the new task (ProjectID, Name, StartDate, EndDate, Duration, Sequence)
				$SQL = sprintf(SQL_GANTT_TASK_CREATE,$projectid,Request::get('taskName'),Request::get('taskDateStart'),Request::get('taskDateEnd'),Request::get('taskDuration'),Request::get('taskSequence'));
				$this->DB->Execute($SQL);

				// get the task ID
				$taskID = $this->DB->ExecuteScalar(SQL_LAST_INSERT);
				echo 'taskCreateID='.$taskID.'&';
			}

			if (Request::get('taskID', Request::R_INT) && Request::get('taskName')) {
				$SQL = sprintf(SQL_GANTT_TASK_NAME_SAVE,Request::get('taskID', Request::R_INT),Request::get('taskName'));
				$this->DB->Execute($SQL);
			}

			if (Request::get('taskID', Request::R_INT) && Request::get('taskDateStart')) {
				$SQL = sprintf(SQL_GANTT_TASK_STARTDATE_SAVE,Request::get('taskID', Request::R_INT),Request::get('taskDateStart'));
				$this->DB->Execute($SQL);
			}

			if (Request::get('taskID', Request::R_INT) && Request::get('taskDateEnd')) {
				$SQL = sprintf(SQL_GANTT_TASK_ENDDATE_SAVE,Request::get('taskID', Request::R_INT),Request::get('taskDateEnd'));
				$this->DB->Execute($SQL);
			}

			if (Request::get('taskID', Request::R_INT) && (Request::get('taskDateStart') || Request::get('taskDateEnd'))) {
				// new code for resources 11/2006

				// get the new duration of this task that was just saved
				$taskSQL = sprintf(GET_TASK_DATES,Request::get('taskID', Request::R_INT));
				$task = $this->DB->QuerySingle($taskSQL);

				// get the day ids for the start and end epoch dates
				$taskDaysSQL = sprintf(SQL_GET_DAYID,strtotime($task['StartDate']),strtotime($task['EndDate']));
				$taskDays = $this->DB->Query($taskDaysSQL);

				// delete any task resource days not in the new task day range that have no HoursCompleted
				$deleteTaskResourceDaysSQL = sprintf(SQL_DELETE_TASK_RESOURCE_DAY,Request::get('taskID', Request::R_INT),$taskDays[0]['ID'],$taskDays[count($taskDays) - 1]['ID']);
				$this->DB->Execute($deleteTaskResourceDaysSQL);
			}

			if (Request::get('taskID', Request::R_INT) && Request::get('taskDuration')) {
				$SQL = sprintf(SQL_GANTT_TASK_DURATION_SAVE,Request::get('taskID', Request::R_INT),Request::get('taskDuration'));
				$this->DB->Execute($SQL);
			}

			// *** begin new code added 2006-03 for improvements to Gantt chart
			if (Request::get('taskID', Request::R_INT) && Request::get('taskDependencyID', Request::R_INT) && Request::get('taskDependencyType')) {
					$SQL = sprintf(SQL_PROJECT_TASK_DEPENDENCY_ADD,Request::get('taskID', Request::R_INT),Request::get('taskDependencyID', Request::R_INT),Request::get('taskDependencyType'));
					$this->DB->Execute($SQL);
			}

			if (Request::get('taskID', Request::R_INT) && Request::get('taskDestinationID', Request::R_INT)) {
					// get the sequence of the source task
					$SQL = sprintf(SQL_PROJECT_TASK_GET_SEQUENCE_BY_TASK, Request::get('taskID', Request::R_INT));

					$taskSourceSequence = $this->DB->QuerySingle($SQL);
					$taskSourceSequence = intval($taskSourceSequence[0]);

					// get the sequence of the destination task
					if (Request::get('taskDestinationID', Request::R_INT) == -1) $taskDestinationSequence = 1;
					else {
							$SQL = sprintf(SQL_PROJECT_TASK_GET_SEQUENCE_BY_TASK, Request::get('taskDestinationID', Request::R_INT));

							$taskDestinationSequence = $this->DB->QuerySingle($SQL);
							$taskDestinationSequence = intval($taskDestinationSequence[0]);
					}

					// reorder up or down depending on the source and destination order
					if ($taskSourceSequence > $taskDestinationSequence) {
							$SQL = sprintf(SQL_GANTT_TASK_REORDER_UP,$taskSourceSequence,$taskDestinationSequence);
							$this->DB->Execute($SQL);
					}
					else {
							$SQL = sprintf(SQL_GANTT_TASK_REORDER_DOWN,$taskSourceSequence,$taskDestinationSequence);
							$this->DB->Execute($SQL);
					}
					// set source task order to destination task order
					$SQL = sprintf(SQL_GANTT_TASK_REORDER_SET,Request::get('taskID', Request::R_INT),$taskDestinationSequence);
					$this->DB->Execute($SQL);
			}
			// *** end new code added 2006-03

			echo 'taskEditSaveStatus=OK';
		}
		else {
			echo 'taskEditSaveStatus=ERROR&error=Access+Denied';
		}
	}
	// end new code

	function selectImage($file_type) {
		switch($file_type) {
			case "application/msword": $return = 'word';break;
			case "text/css":
			case "text/html":
			case "text/plain":
			case "text/rtf":
			case "application/rtf":
			case "application/pdf":
			case "text/xml": $return = 'text';break;
			case "application/x-shockwave-flash":
			case "image/bmp":
			case "image/gif":
			case "image/jpeg":
			case "image/pict":
			case "image/png":
			case "image/tiff": $return = 'image';break;
			case "application/vnd.ms-excel" : $return = 'numbers';break;
			default: $return = 'generic';break;
		}

		return $return;
// }}} 
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
	

}
 
