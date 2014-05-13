<?php
// $Id$

class mod_search extends Module
{
	const SEARCH_GROUP_LIMIT = 5;


	function mod_search() {
		$this->ModuleName = 'search';
		$this->ModuleID = '26D3FABDA482E8016DF1D2B0874636AC';
		$this->RequireLogin = 1;
		$this->Public = 1;
		parent::Module();
	}

	function main() {
		switch (Request::any('action')) {
			case 'ajaxsearch' : $this->ReturnResultsForAjax(); break;
			default: $this->ReturnResultsForAjax();
		}	
	}

	private function clean_search_term($term)
	{
		// lets do some cleanup on that search term. We're going to put it in a like, so we'll only allow letters, numbers and spaces, and a few misc, 
		$searchTerm = preg_replace('/[^0-9a-zA-Z._\s-]/', '', $term);
		return $searchTerm;
	}

	function ReturnResultsForAjax() 
	{
		header('Content-Type: text/html; charset='.CHARSET);
		$searchTerm = $this->clean_search_term(Request::get('searchTerm'));
		
		$totalReturn = Request::get('returnNumber');
		if ( ! $totalReturn ) 
		{
			$totalReturn = 10;
		};
		
		$resultCount = 1;
		
		$projectsAccessList = $this->User->GetUserItemAccess('projects', CU_ACCESS_READ);
		if ($projectsAccessList == '-1') 
		{
			$project_limit_where = '';
		} else {
			$project_limit_where = " AND p.ID IN ( " . $projectsAccessList . " ) ";
		}
		$clientsAccessList = $this->User->GetUserItemAccess('clients', CU_ACCESS_DENY);
		if (strlen($clientsAccessList)>0)
		{
			$clientsAccessList .= ',0'; //add client 0
		}

		if ($clientsAccessList == '-1') 
		{
			$client_limit_where = '';
			$contact_limit_where = '';
		} else {
			$client_limit_where = " AND ID IN ( " . $clientsAccessList . " ) ";
			$contact_limit_where = " AND ClientID IN ( " . $clientsAccessList . " ) ";
		}

		if ($searchTerm) 
		{
			$sql_search = '%' . $searchTerm . '%';
			
			$start = true;
			$sql = "SELECT p.ID as ProjectID, p.Name as ProjectName, c.ID as ClientID, c.Name as ClientName, p.StartDate, p.EndDate
				FROM tblProjects as p
				LEFT JOIN tblClients AS c ON c.ID = p.ClientID
				WHERE
				(
					p.ProjectID LIKE ? OR
					p.Name LIKE ? OR
					p.URL LIKE ? OR
					p.Description LIKE ?
				)
				$project_limit_where
				LIMIT " . self::SEARCH_GROUP_LIMIT;
			
			// not a typo. Note that the search term is repeated above	
			$data = array($sql_search, $sql_search, $sql_search, $sql_search);
			$statement = DB::q($sql, $data);
			$projectSearchResults = $statement->fetchAll();

			if ($projectSearchResults) {

				for ($i = 0; $i < count($projectSearchResults); $i++) {
					if (!$start) {
						$this->setTemplate('ajax_spacer', array());
					}
					$start = false;
					
					$clientId = $projectSearchResults[$i]['ClientID'];
					$clientName = $projectSearchResults[$i]['ClientName'];
					$projectId = $projectSearchResults[$i]['ProjectID'];
					$projectName = $projectSearchResults[$i]['ProjectName'];
					
					$searchResultType = strtoupper(MSG_PROJECT);
					$searchResultLink = '<a href="index.php?module=clients&action=view&id=' . $clientId . '">' . $clientName . '</a> | <a href="index.php?module=projects&action=view&projectid=' . $projectId . '">' . $projectName . '</a>';
					
					$tmpl['SEARCH_RESULT_TYPE'] = $searchResultType;
					$tmpl['SEARCH_RESULT_LINK'] = $searchResultLink;
					
					if ($resultCount <= $totalReturn) {
						$this->setTemplate('search_result_ajax', $tmpl);
						unset($tmpl);
					}
					$resultCount++;
				}
			}
			if (count($projectSearchResults) == $limit) {
				$this->setTemplate('ajax_spacer', array());
				unset($tmpl);
			}

			$sql = "SELECT t.ID as TaskID, t.Name as TaskName, p.ID as ProjectID, p.Name as ProjectName, c.ID as ClientID, c.Name as ClientName, t.StartDate, t.EndDate
				FROM tblTasks as t
				LEFT JOIN tblProjects AS p ON p.ID = t.ProjectID
				INNER JOIN tblClients AS c ON c.ID = p.ClientID
				WHERE
				(
				t.Name LIKE ? OR
				t.Description LIKE ?
				)
				$project_limit_where
				LIMIT " . self::SEARCH_GROUP_LIMIT;
				
			// not a typo. Note that the search term is repeated above	
			$data = array($sql_search, $sql_search);
			$statement = DB::q($sql, $data);
			$tasksSearchResults = $statement->fetchAll();
				
			if ($tasksSearchResults) {
				for ($i = 0; $i < count($tasksSearchResults); $i++) {
					if (!$start) $this->setTemplate('ajax_spacer', array());
					$start = false;
					
					$taskId = $tasksSearchResults[$i]['TaskID'];
					$taskName = $tasksSearchResults[$i]['TaskName'];
					$clientId = $tasksSearchResults[$i]['ClientID'];
					$clientName = $tasksSearchResults[$i]['ClientName'];
					$projectId = $tasksSearchResults[$i]['ProjectID'];
					$projectName = $tasksSearchResults[$i]['ProjectName'];
					
					$searchResultType = strtoupper(MSG_TASK);
					$searchResultLink = '<a href="index.php?module=projects&action=taskview&projectid=' . $projectId . '&taskid=' . $taskId . '">' . $taskName . '</a>';
					
					$tmpl['SEARCH_RESULT_TYPE'] = $searchResultType;
					$tmpl['SEARCH_RESULT_LINK'] = $searchResultLink;
					
					if ($resultCount <= $totalReturn) {
						$this->setTemplate('search_result_ajax', $tmpl);
						unset($tmpl);
					}
					$resultCount++;

				}
			}
			if (count($tasksSearchResults) == $limit) {
				$this->setTemplate('ajax_spacer', array());
				unset($tmpl);
			}

			// despite bad variable naming below, this is actually taskcomments i think.
			$sql = "SELECT t.ID as TaskID, t.Name as TaskName, p.ID as ProjectID, p.Name as ProjectName, c.ID as ClientID, c.Name as ClientName, t.StartDate, t.EndDate
				FROM tblTasks as t
				LEFT JOIN tblProjects AS p ON p.ID = t.ProjectID
				LEFT JOIN tblTasks_Comments AS tc ON tc.TaskID = t.ID
				INNER JOIN tblClients AS c ON c.ID = p.ClientID
				WHERE
				(
				tc.Subject LIKE ? OR
				tc.Body LIKE ?
				)
				$project_limit_where
				LIMIT " . self::SEARCH_GROUP_LIMIT;;
				
			// not a typo. Note that the search term is repeated above	
			$data = array($sql_search, $sql_search);
			$statement = DB::q($sql, $data);
			$tasksSearchResults = $statement->fetchAll();

			if ($tasksSearchResults) {
				for ($i = 0; $i < count($tasksSearchResults); $i++) {
					if (!$start) $this->setTemplate('ajax_spacer', array());
					$start = false;
					
					$taskId = $tasksSearchResults[$i]['TaskID'];
					$taskName = $tasksSearchResults[$i]['TaskName'];
					$clientId = $tasksSearchResults[$i]['ClientID'];
					$clientName = $tasksSearchResults[$i]['ClientName'];
					$projectId = $tasksSearchResults[$i]['ProjectID'];
					$projectName = $tasksSearchResults[$i]['ProjectName'];

					$searchResultType = strtoupper(MSG_TASK_COMMENT);
					$searchResultLink = '<a href="index.php?module=projects&action=taskview&projectid=' . $projectId . '&taskid=' . $taskId . '">' . $taskName . '</a>';
					
					$tmpl['SEARCH_RESULT_TYPE'] = $searchResultType;
					$tmpl['SEARCH_RESULT_LINK'] = $searchResultLink;
					
					if ($resultCount <= $totalReturn) {
						$this->setTemplate('search_result_ajax', $tmpl);
						unset($tmpl);
					}
					$resultCount++;
				}
			}
			if (count($tasksSearchResults) == $limit) {
				$this->setTemplate('ajax_spacer', array());
				unset($tmpl);
			}

			$sql = "SELECT ID, Name, Phone1 as Telephone, City
				FROM tblClients
				WHERE
				(
					Name LIKE ? OR
					Phone1 LIKE ? OR
					Phone2 LIKE ? OR
					FAX LIKE ? OR
					Address1 LIKE ? OR
					Address2 LIKE ? OR
					City LIKE ? OR
					Postcode LIKE ? OR
					URL LIKE ? OR
					Description LIKE ? OR
					ContactEmail LIKE ?
				)
				$client_limit_where
				LIMIT " . self::SEARCH_GROUP_LIMIT;;
			
			// not a typo. Note that the search term is repeated above
			$data = array_fill(0, 11, $sql_search);
			$statement = DB::q($sql, $data);
			$clientSearchResults = $statement->fetchAll();

			if ($clientSearchResults) {
				for ($i = 0; $i < count($clientSearchResults); $i++) {
					if (!$start) $this->setTemplate('ajax_spacer', array());
					$start = false;

					$clientId = $clientSearchResults[$i]['ID'];
					$clientName = $clientSearchResults[$i]['Name'];
										
					$searchResultType = strtoupper(MSG_CLIENT);
					$searchResultLink = '<a href="index.php?module=clients&action=view&id=' . $clientId . '">' . $clientName . '</a>';
					
					$tmpl['SEARCH_RESULT_TYPE'] = $searchResultType;
					$tmpl['SEARCH_RESULT_LINK'] = $searchResultLink;
					
					if ($resultCount <= $totalReturn) {
						$this->setTemplate('search_result_ajax', $tmpl);
						unset($tmpl);
					}
					$resultCount++;
				}
			}
			if (count($clientSearchResults) == $limit) {
				$this->setTemplate('ajax_spacer', array());
				unset($tmpl);
			}

			$sql = "SELECT ID, FirstName, LastName, Title, EmailAddress1 as Email, Phone1 as Telephone
				FROM tblContacts
				WHERE (
					FirstName LIKE ? OR
					LastName LIKE ? OR
					CONCAT(FirstName, ' ', LastName) LIKE ? OR
					Title LIKE ? OR
					Notes LIKE ? OR
					EmailAddress1 LIKE ? OR
					EmailAddress2 LIKE ? OR
					Phone1 LIKE ? OR
					Phone2 LIKE ? OR
					Phone3 LIKE ?
				)
				$contact_limit_where
				LIMIT " . self::SEARCH_GROUP_LIMIT;;

			// not a typo. Note that the search term is repeated above
			$data = array_fill(0, 10, $sql_search);
			$statement = DB::q($sql, $data);
			$contactSearchResults = $statement->fetchAll();

			if ($contactSearchResults) {
				for ($i = 0; $i < count($contactSearchResults); $i++) {
					if (!$start) $this->setTemplate('ajax_spacer', array());
					$start = false;

					$contactId = $contactSearchResults[$i]['ID'];
					$contactName = $contactSearchResults[$i]['FirstName'] . ' ' . $contactSearchResults[$i]['LastName'];
										
					$searchResultType = strtoupper(MSG_CONTACT);
					$searchResultLink = '<a href="index.php?module=contacts&action=view&id=' . $contactId . '">' . $contactName . '</a>';
					
					$tmpl['SEARCH_RESULT_TYPE'] = $searchResultType;
					$tmpl['SEARCH_RESULT_LINK'] = $searchResultLink;
					
					if ($resultCount <= $totalReturn) {
						$this->setTemplate('search_result_ajax', $tmpl);
						unset($tmpl);
					}
					$resultCount++;
				}
			}
			if (count($contactSearchResults) == $limit) {
				$this->setTemplate('ajax_spacer', array());
				unset($tmpl);
			}

			if ($this->User->HasModuleItemAccess('administration', CU_ACCESS_ALL, CU_ACCESS_WRITE))
			{
				$sql = "SELECT ID, FirstName, LastName, Title, EmailAddress as Email, Phone1 as Telephone
					FROM tblUsers
					WHERE
					(
						FirstName LIKE ? OR
						LastName LIKE ? OR
						CONCAT(FirstName, ' ', LastName) LIKE ? OR
						Title LIKE ? OR
						EmailAddress LIKE ? OR
						Phone1 LIKE ? OR
						Phone2 LIKE ? OR
						Phone3 LIKE ?
					)
					LIMIT " . self::SEARCH_GROUP_LIMIT;;

				$data = array_fill(0, 8, $sql_search);
				$statement = DB::q($sql, $data);
				$userSearchResults = $statement->fetchAll();

				if ($userSearchResults) {
					for ($i = 0; $i < count($userSearchResults); $i++) {
						if (!$start) 
							$this->setTemplate('ajax_spacer', array());
						$start = false;

						$userId = $userSearchResults[$i]['ID'];
						$userName = $userSearchResults[$i]['FirstName'] . ' ' . $userSearchResults[$i]['LastName'];

						$tmpl['SEARCH_RESULT_TYPE'] = strtoupper(MSG_USER); 
						$tmpl['SEARCH_RESULT_LINK'] = '<a href="index.php?module=administration&action=useredit&id='.$userId.'">'.$userName.'</a>';

						if ($resultCount <= $totalReturn) {
							$this->setTemplate('search_result_ajax', $tmpl);
							unset($tmpl);
						}
						$resultCount++;
					}
				}
				if (count($userSearchResults) == $limit) {
					$this->setTemplate('ajax_spacer', array());
					unset($tmpl);
				}
			}

			$sql = "SELECT ID, Name
				FROM tblCalendar
				WHERE
				Name LIKE ? OR
				Description LIKE ?
				LIMIT " . self::SEARCH_GROUP_LIMIT;;
			
			$data = array_fill(0, 2, $sql_search);
			$statement = DB::q($sql, $data);
			$calendarSearchResults = $statement->fetchAll();
			
			if ($calendarSearchResults) {
				for ($i = 0; $i < count($calendarSearchResults); $i++) {
					if (!$start) $this->setTemplate('ajax_spacer', array());
					$start = false;

					$calendarId = $calendarSearchResults[$i]['ID'];
					$calendarText = $calendarSearchResults[$i]['Name'];
										
					$searchResultType = strtoupper(MSG_CALENDAR_NOTE);
					$searchResultLink = '<a href="index.php?module=calendar&action=view&id=' . $calendarId . '">' . $calendarText . '</a>';
					
					$tmpl['SEARCH_RESULT_TYPE'] = $searchResultType;
					$tmpl['SEARCH_RESULT_LINK'] = $searchResultLink;
					
					if ($resultCount <= $totalReturn) {
						$this->setTemplate('search_result_ajax', $tmpl);
						unset($tmpl);
					}
					$resultCount++;
				}
			}
			if (count($calendarSearchResults) == $limit) {
				$this->setTemplate('ajax_spacer', array());
				unset($tmpl);
			}

			// hokay this 'where' is weird. We have to make sure it can combine with the other bit of the query below.
			// just be careful.
			$files_where = ($projectsAccessList == -1) ? '' : " ( p.ID IN ( " . $projectsAccessList . " ) OR f.ProjectID = 0) AND ";
			$sql = "SELECT DISTINCT f.ID, f.ProjectID, f.TaskID, f.FileName
				FROM
				  tblFiles f
					LEFT JOIN tblTasks AS t ON t.ID = f.TaskID
					LEFT JOIN tblProjects AS p ON p.ID = f.ProjectID
					LEFT JOIN tblTaskResourceDay AS d ON d.TaskID = t.ID
				WHERE $files_where ( f.FileName LIKE ? OR f.Description LIKE ? )
				LIMIT " . self::SEARCH_GROUP_LIMIT;;

			$data = array_fill(0, 2, $sql_search);
			$statement = DB::q($sql, $data);
			$fileSearchResults = $statement->fetchAll();
			
			if ($fileSearchResults) {
				for ($i = 0; $i < count($fileSearchResults); $i++) {
					if (!$start) $this->setTemplate('ajax_spacer', array());
					$start = false;

					$fileId = $fileSearchResults[$i]['ID'];
					$fileName = $fileSearchResults[$i]['FileName'];
					$taskId = $fileSearchResults[$i]['TaskID'];
					$projectId = $fileSearchResults[$i]['ProjectId'];
										
					$searchResultType = strtoupper(MSG_FILE);
					$searchResultLink = '<a href="index.php?module=projects&action=filedown&projectid=' . $projectId . '&taskid=' . $taskId . '&fileid=' . $fileId . '&from=filelist">' . $fileName . '</a>';
					
					$tmpl['SEARCH_RESULT_TYPE'] = $searchResultType;
					$tmpl['SEARCH_RESULT_LINK'] = $searchResultLink;
					
					if ($resultCount <= $totalReturn) {
						$this->setTemplate('search_result_ajax', $tmpl);
						unset($tmpl);
					}
					$resultCount++;
				}
			}
			if (count($fileSearchResults) == $limit) {
				$this->setTemplate('ajax_spacer', array());
				unset($tmpl);
			}

			if ($start) {
				$tmpl['MESSAGE'] = 'No results found.';
				$this->setTemplate('ajax_search_result_none', $tmpl);
				unset($tmpl);
			}

		}

		$tmpl['MSG_MORE_RESULTS'] = ($resultCount > 1) ? '' : '';
		$this->setTemplate('ajax_search_result_more', $tmpl);
		$this->setModule($modHeader);
		$this->RenderOnlyContent();
	}
}

 
