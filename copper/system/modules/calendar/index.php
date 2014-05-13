<?php
// $Id$
class mod_calendar extends Module
{
	var $date_format = array('1' => 'yyyy-mm-dd', '2' => 'yyyy-dd-mm', '3' => 'dd-mm-yyyy', '4' => 'mm-dd-yyyy');

	function mod_calendar()
	{
		$this->ModuleName	 = 'calendar';
		$this->RequireLogin = 1;
		$this->Public		 = 1;
		parent::Module();
	}

	function main()
	{
		switch (Request::any('action'))
		{
			case 'resource':	 $this->Resource();		   break;
			case 'resourceData': $this->ResourceData();	   break;
			case 'week':		 $this->WeekView();		   break;
			case 'view':		 $this->ViewCalendarNote();   break;
			case 'new':		  $this->NewCalendarNote();	break;
			case 'edit':		 $this->EditCalendarNote();   break;
			case 'save':		 $this->SaveCalendarNote();   break;
			case 'delete':	   $this->DeleteCalendarNote(); break;
			case 'changedate':   $this->ChangeDate();		 break;
			case 'resourceupdatesave': $this->ResourceUpdateSave(); break;
			case 'resourceset':  $this->ResourceSet();		 break;
			case 'resourcesetsave': $this->ResourceSet();	 break;
			default:			 $this->MonthView();		  break;
		}
	}

	function ResourceUpdateSave() {
		if (!$this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE)) 
		{
			echo '{"success":0,"msg":'.MSG_PERMISSION_DENIED.'}';
			return;
		}

		$resourceID = Request::post('resourceID', Request::R_INT);
		$newDays = Request::post('days');
		$month = Request::post('month', Request::R_INT);
		$year = Request::post('year', Request::R_INT);
		$clashes = array();

		// Get the dayIDs and hours committed for the month.
		// Convert it to an array indexed by dayIDs for ease of comparison.
		$sql = sprintf(SQL_GET_IDS_FOR_MONTH_AND_HOURS_COMMITTED_OF_TASKS, $resourceID, $month, $year);
		$rows = $this->DB->Query($sql);
		foreach ($rows as $r) {
	  if ($r['NullCheck'] != NULL)
			  $hours[$r['ID']] = array('day' => $r['Day'], 'hoursCommitted' => $r['HoursCommitted']);
		}

		foreach ($newDays as $dayID => $v)
		{
			$hoursAvailable = min((float)$v['hoursAvailable'], (float)MAX_DAY_LENGTH); // Restrict availability to the maximum day length.

			// If there are hours committed for that dayID, check that they are not going to set their availability
			// to less than the hours committed for that day.
			if (isset($hours[$dayID]))
			{
				if ($hoursAvailable < (float)$hours[$dayID]['hoursCommitted'])
					$clashes[$dayID] = array('hoursAvailable' => $hoursAvailable, 'day' => $hours[$dayID]['day'], 
						'hoursCommitted' => $hours[$dayID]['hoursCommitted']);
				else
					$this->DB->Execute(sprintf(SQL_UPDATE_AVAILABILITY, $resourceID, $dayID, $hoursAvailable));
			}
			else  // No hours set for this day.
			{
				$this->DB->Execute(sprintf(SQL_INSERT_AVAILABILITY, $resourceID, $dayID, $hoursAvailable));
			}
		}

		if (count($clashes) == 0)
			echo '{"success":1}';
		else
		{
			foreach ($clashes as $dayID => $v)
			{
				$date = Format::date("$year-$month-{$v['day']}", TRUE, FALSE);
				$available = sprintf("%.2f", $v['hoursAvailable']).MSG_HOUR_SHORT;
				$committed = sprintf("%.2f", $v['hoursCommitted']).MSG_HOUR_SHORT;
				$msgArray[] = '"'.sprintf(MSG_AVAILABILITY_CLASH, $date, $committed, $available).'"';
			}

			$msg = implode(',', $msgArray);
			echo '{"success":1,"msg":['.$msg.']}';
		}
	}

	function MonthView() {
		if (!$this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_READ))
			$this->ThrowError(2001);

		$this->CreateTabs('month');

		$clientID = Request::get('clientid', Request::R_INT);
		$projectID = Request::get('projectid', Request::R_INT);
		$userID = Request::get('userid', Request::R_INT);
		$month = Request::get('month');
		$year = Request::get('year');

		// Validate the data input.
		$month = ($month > 0 && $month < 13) ? $month : (int)date('n'); // Default to current month.
		$year = ($year > 1000 && $year < 3000) ? $year : (int)date('Y'); // Default to current year.

		// Only Admins can override the user.
		if (!$this->User->IsAdmin || $userID < 1)
			$userID = $this->User->ID;

		// Initialise a new user object to get access to the permissions functions.
		$this->TempUser =& new User();
		$this->TempUser->Initialise($userID, $this->DB);

		// Get the list of clients that the user can see in CSV format.
		$clientsAccessList = $this->TempUser->GetUserItemAccess('clients', CU_ACCESS_READ);
		if ($clientsAccessList == '-1') // User is an admin
		{
			$clientsAccessArray = array();
			$clientIDs = $this->DB->Query(SQL_GET_CLIENT_IDS);
			for ($i = 0; $i < count($clientIDs); $i++) 
				$clientsAccessArray[] = $clientIDs[$i]['ID'];
			$clientsAccessList = implode(',', $clientsAccessArray);
		}

		// Get the list of projects that the user can see in CSV format.
		$projectsAccessList = $this->TempUser->GetUserItemAccess('projects', CU_ACCESS_READ);
		if ($projectsAccessList == '-1') // User is an admin
		{
			$projectsAccessArray = array();
			$projectIDs = $this->DB->Query(SQL_GET_PROJECT_IDS);
			for ($i = 0; $i < count($projectIDs); $i++) 
				$projectsAccessArray[] = $projectIDs[$i]['ID'];
			$projectsAccessList = implode(',', $projectsAccessArray);
		}

		// Filter projects by selected client, if there was one.
		if ($clientID > 0 && in_array($clientID, $clientsAccessArray))
		{
			$clientsSQLList = $clientID;

			if ($projectID > 0 && in_array($projectID, $projectsAccessArray))
				$projectsSQLList = $projectID;
			else
			{
				$temp = array();
				$projects = $this->DB->Query(sprintf(SQL_GET_PROJECT_IDS_FOR_CLIENT, $clientID, $projectsAccessList));
				for ($i = 0; $i < count($projects); $i++)
					$temp[] = $projects[$i]['ProjectID'];
				$projectsSQLList = implode(',', $temp);
			}
		}
		else
		{
			$clientsSQLList = $clientsAccessList;
			$projectsSQLList = ($projectID > 0 && in_array($projectID, $projectsAccessArray)) ? $projectID : $projectsAccessList;
		}
		// Get the user's resource ID, if they are one, and their name.
		$row = $this->DB->QuerySingle(sprintf(SQL_USER_RESOURCE, $userID));
		$resourceID = (int)$row['ID'];
		$tmpl['resourceID'] = $resourceID;
		$tmpl['name'] = $row['FirstName'].' '.$row['LastName'];
		
		$tmpl['month'] = $month;
		$tmpl['year'] = $year;

		// Get the name of the selected month. 
		$monthNames = array(MSG_JANUARY, MSG_FEBRUARY, MSG_MARCH, MSG_APRIL, MSG_MAY, MSG_JUNE, MSG_JULY, MSG_AUGUST, MSG_SEPTEMBER, MSG_OCTOBER, MSG_NOVEMBER, MSG_DECEMBER);
		$tmpl['monthName'] = $monthNames[$month-1];

		// Calculate the month and year for the "Next Month" option.
		if (($nextMonth = $month + 1) == 13) { $nextMonth = 1; $nextYear = $year + 1; }
		else $nextYear = $year;
		$nextMonthName = $monthNames[$nextMonth-1];

		// Calculate the month and year for the "Previous Month" option.
		if (($prevMonth = $month  - 1) == 0) { $prevMonth = 12; $prevYear = $year - 1; }
		else $prevYear = $year;
		$prevMonthName = $monthNames[$prevMonth-1];

		$actions[] = array('url' => url::build_url('calendar', 'monthview', "month=$prevMonth&year=$prevYear"), 'name' => $prevMonthName);
		$actions[] = array('url' => url::build_url('calendar', 'monthview', "month=$nextMonth&year=$nextYear"), 'name' => $nextMonthName);
		$actions[] = array('url' => '#', 'attrs' => "onclick=\"calendar.saveForm();return false;\"", 'name' => MSG_SAVE_CHANGES);
		$actions[] = array('url' => url::build_url('calendar', 'new'), 'name' => MSG_NEW_EVENT);
		$actions[] = array('url' => '#', 'attrs' => "onclick=\"calendar.showAvailability('$userID');return false;\"", 'name' => MSG_AVAILABILITY);				

		$tmpl['actions'] = $this->ActionMenu($actions);

		// Build an array of day names to iterate over.
		$dayNames = array(MSG_MONDAY, MSG_TUESDAY, MSG_WEDNESDAY, MSG_THURSDAY, MSG_FRIDAY, MSG_SATURDAY, MSG_SUNDAY);
		if (Settings::get('WeekStart') == 'Sunday')
			 array_unshift($dayNames, array_pop($dayNames));

		// The day names in the column headers.
		foreach ($dayNames as $k => $dayName)
			$tmpl["dayName$k"] = $dayName;

		// Get timestamps for start and end day of the month.
		// Use 6am for the time to avoid daylight savings issues where the day is 23 or 25 hours long.
		$tsStart = mktime(6, 0, 0, $month, 1, $year);
		$daysInMonth = date('t', $tsStart);
		$tsEnd = mktime(6, 0, 0, $month, $daysInMonth, $year);

		// Get day IDs for start and end day of the month.
		$dayIDs = $this->DB->Query(sprintf(SQL_GET_IDS_FOR_MONTH, $month, $year));
		$startDayID = $dayIDs[0]['ID'];
		$last = count($dayIDs) - 1;
		$endDayID = $dayIDs[$last]['ID'];

		// How many days do we go back into the previous month to get the previous Sunday or Monday?
		$firstDayNumber = date('w', $tsStart); // Day of week: 0 = Sunday, 6 = Saturday
		$daysToSubtract = (Settings::get('WeekStart') == 'Sunday') ? $firstDayNumber : $firstDayNumber - 1;
		if ($daysToSubtract < 0)
			$daysToSubtract = 6;

		// How many days do we go forward into the next month to get the next Sunday or Monday?
		$lastDayNumber = date('w', $tsEnd); // Day of week: 0 = Sunday, 6 = Saturday
		$daysToAdd = (Settings::get('WeekStart') == 'Sunday') ? 6 - $lastDayNumber : 6 - ($lastDayNumber - 1);
		if ($daysToAdd > 6)
			$daysToAdd = 0;

		// Get timestamps and day IDs for the period being shown on the calendar grid.
		$tsCalStart = $tsStart - ($daysToSubtract * 24 * 60 * 60);
		$tsCalEnd = $tsEnd + ($daysToAdd * 24 * 60 * 60);
		list($y, $m, $d) = explode('-', date('Y-m-d', $tsCalStart));
		$calStartDayID = $this->DB->ExecuteScalar(sprintf(SQL_GET_DAY_ID, $y, $m, $d));
		//list($y, $m, $d) = explode('-', date('Y-m-d', $tsCalEnd));
		//$calEndDayID = $this->DB->ExecuteScalar(sprintf(SQL_GET_DAY_ID, $y, $m, $d));

		// Build an array of all the days that will become day boxes on the calendar grid.
		// This array has data stored in it that will be converted to HTML later on.
		$lastDay = NULL;
		for ($i = $tsCalStart, $dayID = $calStartDayID; $i <= $tsCalEnd; $i += (24 * 60 * 60), $dayID++)
		{
			list($y, $m, $d) = explode('-', date('Y-m-d', $i));

			$days[$dayID] = array(
				'dayID' => $dayID,
				'date' => "$y-$m-$d",
				'dayNum' => $d,
				'otherMonth' => ($m != $month),
				'currentDay' => (date('Y-m-d') == "$y-$m-$d"),
				'hoursAvailable' => NULL,
				'hoursCommitted' => array(),
				'projectsList' => array(),
				'tasksList' => array(),
				'events' => array(),
			);
		}
		
		// Get the user's availability for this month and store it in the days array.
		
		$sql = sprintf(GET_HOURS_DAY_FOR_RESOURCE, $resourceID, $month, $year);
		$rows = $this->DB->Query($sql);

		if (!is_array($rows)) $rows = array();
		foreach ($rows as $r)
		{
			$days[$r['ID']]['hoursAvailable'] = $r['HoursAvailable'];
		}

		// Get the user's hours committed for this month and store it in the days array.
		// When a client or project is selected only show the commitment for that subset
		$where = "tblTaskResourceDay.DayID >= $startDayID AND tblTaskResourceDay.DayID <= $endDayID";
		if ($projectID > 0) $sql = sprintf(SQL_HOURS_COMMITTED_TASK_DAYS_MONTH_PROJECT, $resourceID, $where, $projectID);
		else if ($clientID > 0) $sql = sprintf(SQL_HOURS_COMMITTED_TASK_DAYS_MONTH_CLIENT, $resourceID, $where, $clientID);
		else $sql = sprintf(SQL_HOURS_COMMITTED_TASK_DAYS_MONTH, $resourceID, $where);
		$rows = $this->DB->Query($sql);
		if (!is_array($rows)) $rows = array();
		foreach ($rows as $r)
		{
			$data = array();
			$data['label'] = $r['HoursCommitted'].MSG_HOUR_SHORT.' '.$r['Name'];
			$data['url'] = url::build_url('projects', 'taskview', "projectid={$r['ProjectID']}&taskid={$r['TaskID']}");
			$data['id'] = $r['DayID'].'_commitment';
			$days[$r['DayID']]['hoursCommitted'][] = $data;
		}

		// Get the projects starting this month and store them in the days array.
		$rows = $this->DB->Query(sprintf(SQL_GET_PROJECTS_STARTING_MONTH, $year, $month, $projectsSQLList));
		if (!is_array($rows)) $rows = array();
		foreach ($rows as $r)
		{
			$data = array();
			$data['label'] = $r['Name'];
			$data['colour'] = $r['Colour'];
			$data['url'] = url::build_url('projects', 'view', "projectid={$r['ProjectID']}");
			$data['id'] = 'project_'.$r['ProjectID'].'_start';
			$data['startDate'] = Format::date($r['StartDate']);
			$data['endDate'] = Format::date($r['EndDate']);
			$data['description'] = nl2br($r['Description']);
			$data['type'] = 'start';
			$days[$r['DayID']]['projectsList'][] = $data;
		}

		// Get the projects ending this month and store them in the days array.
		$rows = $this->DB->Query(sprintf(SQL_GET_PROJECTS_ENDING_MONTH, $year, $month, $projectsSQLList));
		if (!is_array($rows)) $rows = array();
		foreach ($rows as $r)
		{
			$data = array();
			$data['label'] = $r['Name'];
			$data['colour'] = $r['Colour'];
			$data['url'] = url::build_url('projects', 'view', "projectid={$r['ProjectID']}");
			$data['id'] = 'project_'.$r['ProjectID'].'_end';
			$data['startDate'] = Format::date($r['StartDate']);
			$data['endDate'] = Format::date($r['EndDate']);
			$data['description'] = nl2br($r['Description']);
			$data['type'] = 'end';
			$days[$r['DayID']]['projectsList'][] = $data;
		}
		
		// Get the tasks starting this month and store them in the days array.
		$rows = $this->DB->Query(sprintf(SQL_GET_TASKS_STARTING_MONTH, $year, $month, $projectsSQLList));
		if (!is_array($rows)) $rows = array();
		foreach ($rows as $r)
		{
			$data = array();
			$data['label'] = $r['TaskName'];
			$data['colour'] = $r['Colour'];
			$data['url'] = url::build_url('projects', 'taskview', "projectid={$r['ProjectID']}&taskid={$r['TaskID']}"); 
			$data['id'] = 'task_'.$r['TaskID'].'_start';
			$data['startDate'] = Format::date($r['StartDate']);
			$data['endDate'] = Format::date($r['EndDate']);
			$data['description'] = nl2br(htmlentities($r['Description'], ENT_COMPAT, 'UTF-8'));
			$data['type'] = 'start';
			$days[$r['DayID']]['tasksList'][] = $data;
		}

		// Get the tasks ending this month and store them in the days array.
		$rows = $this->DB->Query(sprintf(SQL_GET_TASKS_ENDING_MONTH, $year, $month, $projectsSQLList));
		if (!is_array($rows)) $rows = array();
		foreach ($rows as $r)
		{
			$data = array();
			$data['label'] = $r['TaskName'];
			$data['colour'] = '#fff';
			$data['url'] = url::build_url('projects', 'taskview', "projectid={$r['ProjectID']}&taskid={$r['TaskID']}"); 
			$data['id'] = 'task_'.$r['TaskID'].'_end';
			$data['startDate'] = Format::date($r['StartDate']);
			$data['endDate'] = Format::date($r['EndDate']);
			$data['description'] = nl2br(htmlentities($r['Description'], ENT_COMPAT, 'UTF-8'));
			$data['type'] = 'end';
			$days[$r['DayID']]['tasksList'][] = $data;
		}

		// Get the events occurring this month and store them in the days array.
		$rows = $this->DB->Query(sprintf(SQL_GET_CALENDAR_NOTES_MONTH, $year, $month));
		if (!is_array($rows)) $rows = array();
		foreach ($rows as $r)
		{
			$data = array();
			$data['label'] = $r['Name'];
			$data['colour'] = '#fff';
			$data['url'] = url::build_url('calendar', 'view', "id={$r['ID']}");
			$data['id'] = 'event_'.$r['ID'];
			$data['startDate'] = Format::date($r['Date'].' '.$r['StartTime']);
			$data['endDate'] = Format::date($r['Date'].' '.$r['EndTime']);
			$data['description'] = htmlentities($r['Description'], ENT_COMPAT, 'UTF-8');
			$days[$r['DayID']]['eventsList'][] = $data; 
		}

		// Turn days array into a week view ie. 7 columns, 4 or 5 or 6 rows.
		// It makes looping on the rows easier when producing the HTML.
		$weeks = array();
		for ($i = 0; $i < count($days); $i++)
		{
			$index = floor($i/7);
			$weeks[$index][] = $days[$calStartDayID+$i];
		}

		// Loop on the weeks and days to display the HTML.
		$tmpl['calendar'] = '';
		foreach ($weeks as $week)
		{
			$tmpl['calendar'] .= "<!-- START WEEK -->\n<tr>\n";
			foreach ($week as $day)
			{
				// Set a different style for days not in the selected month, and the current day.
				$day['style'] = ($day['otherMonth']) ? 'other' : (($day['currentDay']) ? 'current' : '');

				// If availability data exists, display an input box to edit it. The box is set visibility:hidden by default.
				$day['availability'] = '';
				if (is_null($day['hoursAvailable'])) 
					$day['hoursAvailable'] = '0.00';
				if (!$day['otherMonth'])
					$day['availability'] = $this->getTemplate('new_month_view_day_availability', $day);

				// If commitment/project/task/event data exists, display a link to the relevant item.
				$day['commitments'] = '';
				if (count($day['hoursCommitted']) > 0)
				{
					foreach ($day['hoursCommitted'] as $hc)
					{
						$hc['class'] = 'commitment';
						$day['commitments'] .= $this->getTemplate('new_month_view_day_commitment', $hc);
					}
				}

				$day['projects'] = '';
				if (count($day['projectsList']) > 0)
				{
					foreach ($day['projectsList'] as $p)
					{
						$p['class'] = 'draggable proj';
						$p['icon'] = ($p['type'] == 'start') ? 'icon_cal_s.gif' : 'icon_cal_f.gif';
						$day['projects'] .= $this->getTemplate('new_month_view_day_item', $p);
					}
				}

				$day['tasks'] = '';
				if (count($day['tasksList']) > 0)
				{
					foreach ($day['tasksList'] as $t)
					{
						$t['class'] = 'draggable task';
						$t['icon'] = ($t['type'] == 'start') ? 'icon_cal_s.gif' : 'icon_cal_f.gif';
						$day['tasks'] .= $this->getTemplate('new_month_view_day_item', $t);
					}
				}

				$day['events'] = '';
				if (count($day['eventsList']) > 0)
				{
					foreach ($day['eventsList'] as $e)
					{
						$e['class'] = 'draggable event';
						$day['events'] .= $this->getTemplate('new_month_view_day_event', $e);
					}
				}

				$tmpl['calendar'] .= $this->getTemplate('new_month_view_day', $day);
			}
			$tmpl['calendar'] .= "</tr>\n<!-- END WEEK -->\n";
		}

		$this->setTemplate('new_month_view', $tmpl);

		$modAction[] = '<a id="dash-toggler" href="#" onclick="toggleDash(); return false;">SHOW DASH</a>';

			// Make Client dropdown, selecting clientID if possible.
		$tmplDash['clients'] = '<option value="">'.MSG_ALL_CLIENTS.'</option>';
		$tmplDash['period'] = "month";

		$sql = sprintf(SQL_GET_CLIENTS_IN, $clientsAccessList);
		$clientList = $this->DB->Query($sql);
		if (is_array($clientList))
		{
			for ($i = 0; $i < count($clientList); $i++)
			{
				$selected = ($clientID == $clientList[$i]['ClientID']) ? ' selected' : '';
				$tmplDash['clients'] .= '<option value="'.$clientList[$i]['ClientID'].'"'.$selected.'>'.$clientList[$i]['ClientName'].'</option>';
			}
		}

		// Make Project dropdown, selecting projectID if possible.
		$tmplDash['projects'] = '<option value="">'.MSG_ALL_PROJECTS.'</option>';
		$SQL = sprintf(SQL_GET_PROJECTS_IN, $projectsSQLList);
		$projectsList = $this->DB->Query($SQL);
		if (is_array($projectsList))
		{
			for ($i = 0; $i < count($projectsList); $i++)
			{
				$selected = ($projectID == $projectsList[$i]['ProjectID']) ? ' selected' : '';
				$tmplDash['projects'] .= '<option value="'.$projectsList[$i]['ProjectID'].'"'.$selected.'>'.$projectsList[$i]['ProjectName'].'</option>';
						}
				}
				
		// Display a user select dropdown if the user is an admin.
		$tmplDash['month'] = $month;
		$tmplDash['year'] = $year;

		Response::addToJavascript('calendar', array('month' => $month, 'year' => $year, 'period' => 'month'));

		$tmplDash['user'] = $userID;
		$tmplDash['txtAdminOnly'] = '';
		if ($this->User->IsAdmin)
		{
			// Setup the User Swap List.
					$SQL = sprintf(SQL_GET_USER_LIST);
					$userList = $this->DB->Query($SQL);
					$tmplAdmin['users'] = "";
			if (is_array($userList))
			{
				for ($i = 0; $i < count($userList); $i++)
				{
					$selected = ($this->TempUser->ID == $userList[$i]['ID']) ? ' selected' : '';
					$tmplAdmin['users'] .= '<option value="'.$userList[$i]['ID'].'"'.$selected.'>'.$userList[$i]['FirstName'].' '.$userList[$i]['LastName'].'</option>';
							}
					}
					$tmplAdmin['period'] = "month";
					$tmplAdmin['month'] = $month;
					$tmplAdmin['year'] = $year;
					$tmplDash['txtAdminOnly'] = $this->getTemplate("dashBlockAdmin", $tmplAdmin);
				}

		$this->setDash($this->getTemplate("dashBlock", $tmplDash));
		$this->setModule(MSG_MONTH_VIEW, $modAction);
		$this->Render();

	}

	function ChangeDate() {
		list($itemType, $itemID, $itemField) = explode('_', Request::get('item'));
		list($day, $date) = explode('_', Request::get('newdate'));

		switch ($itemType)
		{
			case 'task': 
				$projectID = $this->DB->ExecuteScalar(sprintf(SQL_TASK_GET_PROJECT_ID, $itemID));
				if (!($this->User->HasUserItemAccess('projects', $projectID, CU_ACCESS_WRITE)))
				{
					$this->ThrowError(2001);
					return;
				}

				$field = ($itemField == 'start') ? 'StartDate' : 'EndDate';
				$sql = sprintf(SQL_TASK_MOVE_DATE, $field, $date, $itemID);
				$this->DB->Execute($sql);

				$sql = sprintf(SQL_TASK_GET_DATES, $itemID);
				$t = $this->DB->QuerySingle($sql);
				if ($t['EndDate'] != '0000-00-00') // Ignore milestones 
				{
					list($y, $m, $d) = explode('-', $t['StartDate']);
					$startDayID = $this->DB->ExecuteScalar(sprintf(SQL_GET_DAY_ID, $y, $m, $d));
					list($y, $m, $d) = explode('-', $t['EndDate']);
					$endDayID = $this->DB->ExecuteScalar(sprintf(SQL_GET_DAY_ID, $y, $m, $d));

					// Delete any task resource days not in the new task day range that dont have any completed hours
					$sql = sprintf(SQL_DELETE_TASK_RESOURCE_DAY, $t['ID'], $startDayID, $endDayID);
					$this->DB->Execute($sql);

					// Set the committed hours to 0 for the task days outside the range with completed hours
					$sql = sprintf(SQL_UPDATE_TASK_RESOURCE_DAY_COMMITTED, $t['ID'], $startDayID, $endDayID);
					$this->DB->Execute($sql);
				}
				break;	
			case 'project': 
				if (!($this->User->HasUserItemAccess('projects', $itemID, CU_ACCESS_WRITE)))
				{
					$this->ThrowError(2001);
					return;
				}

				if ($itemField == 'start') 
				{
					// Move the project start and end dates by the requested number of days.
					$dateDiff = $this->DB->ExecuteScalar(sprintf(SQL_PROJECT_GET_DATE_DIFF, $date, 'StartDate', $itemID));
					$sql = sprintf(SQL_PROJECT_MOVE_DATES, $dateDiff, $itemID);
					$this->DB->Execute($sql);

					// Milestones are tasks with no end date set.
					// Move the start and end dates separately for tasks to accomodate milestones.
					$sql = sprintf(SQL_PROJECT_TASKS_MOVE_START_DATE, $dateDiff, $itemID);
					$this->DB->Execute($sql);
					$sql = sprintf(SQL_PROJECT_TASKS_MOVE_END_DATE, $dateDiff, $itemID);
					$this->DB->Execute($sql);

					// Loop on the tasks using the new dates.					
					$sql = sprintf(SQL_PROJECT_GET_TASK_DATES, $itemID);
					$tasks = $this->DB->Query($sql);
					foreach ($tasks as $t)
					{
						if ($t['EndDate'] != '0000-00-00') // Ignore milestones 
						{
							list($y, $m, $d) = explode('-', $t['StartDate']);
							$startDayID = $this->DB->ExecuteScalar(sprintf(SQL_GET_DAY_ID, $y, $m, $d));
							list($y, $m, $d) = explode('-', $t['EndDate']);
							$endDayID = $this->DB->ExecuteScalar(sprintf(SQL_GET_DAY_ID, $y, $m, $d));

							// Delete any task resource days not in the new task day range that dont have any completed hours
							$sql = sprintf(SQL_DELETE_TASK_RESOURCE_DAY, $t['ID'], $startDayID, $endDayID);
							$this->DB->Execute($sql);

							// Set the committed hours to 0 for the task days outside the range with completed hours
							$sql = sprintf(SQL_UPDATE_TASK_RESOURCE_DAY_COMMITTED, $t['ID'], $startDayID, $endDayID);
							$this->DB->Execute($sql);
						}
					}
				}
				else
				{
					$sql = sprintf(SQL_PROJECT_MOVE_DATE, 'EndDate', $date, $itemID);
					$this->DB->Execute($sql);
				}
				break;
			case 'cal':
				if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE)) 
				{
					$this->ThrowError(2001);
					return;
				}

				$sql = sprintf(SQL_CALENDAR_MOVE_DATE, $date, $itemID);
				$sqlState = $this->DB->Execute($sql);
				break;
		}
				echo "{actioned:1, success:1}";
		//Response::redirect('index.php?module=calendar');
	}
	
	function ResourceData() {
		if (!($monthToRequest = Request::get('monthToRequest'))) $monthToRequest = date('n',time()); // get the month and year of local time when they are not supplied
		if (!($yearToRequest = Request::get('yearToRequest'))) $yearToRequest = date('Y',time());

		$datesCount = '&datesCount=';
		$datesStart = '&datesStart=';
		$datesEnd = '&datesEnd=';
		$todaysDate = '&todaysDate=';
		$maxDayLength = '&maxDayLength=' . MAX_DAY_LENGTH;
		$dayLength = '&dayLength=' . DAY_LENGTH;

		$monthRequested = '&monthRequested=' . $monthToRequest;
		$yearRequested = '&yearRequested=' . $yearToRequest;

		// resources
		$resourcesIDs = '&resourcesIDs=';
		$resourcesUserIDs = '&resourcesUserIDs=';
		$resourcesNames = '&resourcesNames=';

		$taskIDs = '&taskIDs=';
		$taskData = '&taskData=';
		$projectIDs = '&projectIDs=';
		$projectData = '&projectData=';

		$resourceDayHoursAvailable = '&resourceDayHoursAvailable=';
		$resourceDayHoursCommitted = '&resourceDayHoursCommitted=';
		$resourceDayTaskID = '&resourceDayTaskID=';

		if ($this->User->HasModuleItemAccess('administration', CU_ACCESS_ALL, CU_ACCESS_READ))
			// get a list of all resources
			$resources = $this->DB->Query(SQL_RESOURCE_USERS);
		else {
				$user = $this->DB->QuerySingle(sprintf(SQL_USER_RESOURCE,$this->User->ID));
				$userID = $user[0];
				$resources = $this->DB->Query(sprintf(SQL_RESOURCE_USERS_RESTRICT, $this->User->ID));
		}
		$monthStartEpoch = gmmktime(0,0,0,$monthToRequest,1,$yearToRequest);
		$monthEndEpoch = gmmktime(0,0,0,$monthToRequest + 1,0,$yearToRequest); // The last day of any given month can be expressed as the "0" day of the next month

		// find the ids for that date range
		$daysSQL = sprintf(SQL_GET_DAYID_EPOCH,$monthStartEpoch,$monthEndEpoch);
		$days = $this->DB->Query($daysSQL);
		// send the dates in the Y-m-d format eg: 2007-06-25
		$datesStart .= gmdate('Y-m-d',$monthStartEpoch);
		$datesEnd .= gmdate('Y-m-d',$monthEndEpoch);
		$datesCount .= gmdate('t',$monthStartEpoch);
		$todaysDate .= gmdate('Y-m-d',time());
		// get the avalibility for all the resources
		$resourceAvailabilityWhereInRangeTableRD = 'tblResourceDay.DayID >= ' . $days[0]['DayID'] . ' AND tblResourceDay.DayID <= ' . $days[count($days) - 1]['DayID'];
		if ($this->User->HasModuleItemAccess('administration', CU_ACCESS_ALL, CU_ACCESS_READ))
			$resourceAvailabilitySQL = sprintf(GET_HOURS_AVAILABLE_ALL_RESOURCES,$resourceAvailabilityWhereInRangeTableRD);
		else
			$resourceAvailabilitySQL = sprintf(GET_HOURS_AVAILABLE_SINGLE_RESOURCE,$resourceAvailabilityWhereInRangeTableRD, $userID);
		$resourceAvailability = $this->DB->Query($resourceAvailabilitySQL);
		$resourceAvailabilityPointer = 0;

		// get the committed hours for all the resources for all the tasks
		$resourceCommittedWhereInRangeTableTRD = 'tblTaskResourceDay.HoursCommitted > "0.00" AND tblTaskResourceDay.DayID >= ' . $days[0]['DayID'] . ' AND tblTaskResourceDay.DayID <= ' . $days[count($days) - 1]['DayID'];
		if ($this->User->HasModuleItemAccess('administration', CU_ACCESS_ALL, CU_ACCESS_READ))
			$resourceCommittedSQL = sprintf(GET_HOURS_COMMITTED_ALL_RESOURCES,$resourceCommittedWhereInRangeTableTRD);
		else
			$resourceCommittedSQL = sprintf(GET_HOURS_COMMITTED_SINGLE_RESOURCE,$resourceCommittedWhereInRangeTableTRD, $userID);
		$resourceCommitted = $this->DB->Query($resourceCommittedSQL);
		$resourceCommittedPointer = 0;
		$uniqueTaskIDs = array();

		// for each of the resources
		for ($i = 0; $i < count($resources); $i++) {
			// create the list of res ids
			$resourcesIDs .= ($i ? ',' : '') . $resources[$i]['ID'];
			$resourcesUserIDs .= ($i ? ',' : '') . $resources[$i]['UserID'];

			// create the list of res names
			$resourcesNames .= ($i ? ',' : '') . $this->encodeCommas($resources[$i]['FirstName'].' '.$resources[$i]['LastName']);

			// initialize the list strings for each resource
			$hoursAvailableList = '';
			$hoursCommittedList = '';
			$taskIDList = '';
			$projectIDList = '';
			$taskNameList = '';

			// for each of the days
			for ($j = 0; $j < count($days); $j++) {

				if ($resourceAvailability[$resourceAvailabilityPointer]['DayID'] == $days[$j]['DayID'] && $resources[$i]['ID'] == $resourceAvailability[$resourceAvailabilityPointer]['ResourceID']) {
					$hoursAvailableTemp = $resourceAvailability[$resourceAvailabilityPointer]['HoursAvailable'];
					$resourceAvailabilityPointer++;
				}

				$hoursCommittedSwitch = false;
				while ($resourceCommitted[$resourceCommittedPointer]['DayID'] == $days[$j]['DayID'] && $resources[$i]['ID'] == $resourceCommitted[$resourceCommittedPointer]['ResourceID']) {
					// get the unique task ids
					if (!$uniqueTaskIDs[$resourceCommitted[$resourceCommittedPointer]['TaskID']]) {
						$uniqueTaskIDs[$resourceCommitted[$resourceCommittedPointer]['TaskID']] = true;
					}

					// committed hours and task id for each task
					$hoursCommittedTemp = $resourceCommitted[$resourceCommittedPointer]['HoursCommitted'];
					$taskIDTemp = $resourceCommitted[$resourceCommittedPointer]['TaskID'];

					if ($hoursCommittedTemp) $hoursCommittedListTemp .= ($hoursCommittedSwitch ? ',' : '') . $hoursCommittedTemp;
					else $hoursCommittedListTemp .= ($hoursCommittedSwitch ? ',' : '');
					$hoursCommittedTemp = '';
					if ($taskIDTemp) $taskIDListTemp .= ($hoursCommittedSwitch ? ',' : '') . $taskIDTemp;
					else $taskIDListTemp .= ($hoursCommittedSwitch ? ',' : '');
					$taskIDTemp = '';

					$resourceCommittedPointer++;
					$hoursCommittedSwitch = true;
				}

				if ($hoursAvailableTemp) $hoursAvailableList .= ($hoursAvailableList || $j ? ',' : '') . $hoursAvailableTemp;
				else $hoursAvailableList .= ($j ? ',' : '');
				$hoursAvailableTemp = '';
				// for each day
				if ($hoursCommittedListTemp) $hoursCommittedList .= ($hoursCommittedList || $j ? ';' : '') . $hoursCommittedListTemp;
				else $hoursCommittedList .= ($j ? ';' : '');
				$hoursCommittedListTemp = '';
				if ($taskIDListTemp) $taskIDList .= ($taskIDList || $j ? ';' : '') . $taskIDListTemp;
				else $taskIDList .= ($j ? ';' : '');
				$taskIDListTemp = '';

			} // end days loop
			$resourceDayHoursAvailable .= ($i ? ';' : '') . $hoursAvailableList;

			// for each resource
			$resourceDayHoursCommitted .= ($i ? '|' : '') . $hoursCommittedList;
			$resourceDayTaskID .= ($i ? '|' : '') . $taskIDList;

		} // end almightie resources loop

		// create the where clause to filter resources
		$taskIDInWhere = '';
		for (reset($uniqueTaskIDs); key($uniqueTaskIDs); next($uniqueTaskIDs)) {
			$taskIDInWhere .= ($taskIDInWhere ? ',' : '') . key($uniqueTaskIDs);
		}
		if (count($uniqueTaskIDs) == 1) $taskIDInWhere = ' = ' . $taskIDInWhere;
		else $taskIDInWhere = ' IN (' . $taskIDInWhere . ')';

		$taskProjects = $this->DB->Query(sprintf(SQL_GET_TASKNAMES,$taskIDInWhere));

		for ($i = 0; $i < count($taskProjects); $i++) {
			$taskIDs .= ($i ? ',' : '') . $taskProjects[$i]['TaskID'];
			$taskData .= ($i ? ';' : '') . $this->encodeCommas($taskProjects[$i]['ProjectID']);
			$taskData .= ',' . $this->encodeCommas($taskProjects[$i]['TaskName']);
			$taskData .= ',' . $this->encodeCommas(gmdate('d-m-Y',strtotime($taskProjects[$i]['StartDate'] . 'GMT')));
			$taskData .= ',' . $this->encodeCommas(gmdate('d-m-Y',strtotime($taskProjects[$i]['EndDate'] . 'GMT')));

			if ($taskProjects[$i - 1]['ProjectID'] != $taskProjects[$i]['ProjectID']) {
				$projectIDs .= ($i ? ',' : '') . $taskProjects[$i]['ProjectID'];
				$projectData .= ($i ? ';' : '') . $this->encodeCommas($taskProjects[$i]['ProjectName']);
				$projectData .= ',0x' . substr($taskProjects[$i]['Colour'],1);
			}
		}

		echo $datesCount . $datesStart . $datesEnd . $todaysDate . $maxDayLength . $dayLength;
		echo $monthRequested . $yearRequested;
		echo $resourcesIDs . $resourcesUserIDs . $resourcesNames;
		echo $resourceDayHoursAvailable . $resourceDayHoursCommitted . $resourceDayTaskID;
		echo $taskIDs . $taskData;
		echo $projectIDs . $projectData;
		echo '&theAnswer=42';

	}

	function encodeCommas($s, $andSemicolons = 0, $andPipes = 0) {
		if ($andPipes) $s = str_replace('|','#\#',$s);
		if ($andSemicolons) $s = str_replace(';','#:#',$s);
		return rawurlencode(utf8_encode(str_replace(',','#<#',$s)));
	}

	function Resource() {
		$this->CreateTabs('resource');
			$this->setTemplate('gantt');

			$this->setHeader(MSG_CALENDAR);
			$this->setModule(MSG_RESOURCE);
			$this->Render();


	}

	function WeekView() {
		if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_READ)) {
			$modHeader = MSG_WEEK_VIEW;
			$modAction[0] = '<a href="index.php?module=calendar&amp;action=new">' . MSG_NEW_EVENT . '</a>';
			$this->CreateTabs('week');
			$now = time();

			$limit = 50;
			$offset = Request::get('date');

			if ($offset) {
				 $this->Session->Set('date', $offset);
			}
			else if ($this->Session->Get('date')) {
					$offset = $this->Session->Get('date');
			}

			$url = 'index.php?module=calendar&action=week&date='.$offset;

			$projectID = Request::get('projectID', Request::R_INT);
			$clientID = Request::get('clientID', Request::R_INT);
			$tmpl['projectID'] = $projectID;
			$tmpl['clientID'] = $clientID;

			if ($projectID && !($this->User->HasUserItemAccess('projects', $projectID, CU_ACCESS_READ))) $this->ThrowError(2001);
			if ($clientID && !($this->User->HasUserItemAccess('clients', $clientID, CU_ACCESS_READ))) $this->ThrowError(2001);

			$footer_tmpl['projectID'] = $projectID;
			$footer_tmpl['clientID'] = $clientID;

			//Display User select tool if user has admin permissions
			$tmpl['GlobalCalendar'] = '';
		// Only Admins can override the user.
		if (!$this->User->IsAdmin || $userID < 1)
			$userID = $this->User->ID;


			if ($this->User->HasModuleItemAccess('administration', CU_ACCESS_ALL, CU_ACCESS_READ)) {
				$userID = Request::get('userID', Request::R_INT);
				// Create Temp user for creating correct permissions

				if ($userID) {
					$this->Session->Set('calendarID', $userID);
					$this->TempUser		 =& new User();
					$this->TempUser->Initialise($userID, $this->DB);
				}
				else if	($this->Session->Get('calendarID')) {
					$userID = $this->Session->Get('calendarID');
					$this->TempUser		 =& new User();
					$this->TempUser->Initialise($userID, $this->DB);
				}
				else {
					$this->TempUser		 =& new User();
					$this->TempUser->Initialise($this->User->ID, $this->DB);
		}
		$SQL = sprintf(SQL_GET_USER_LIST);
				$userList = $this->DB->Query($SQL);
				if ($userList) {
					for ($i = 0; $i < count($userList); $i++) {
						$tmpl['GlobalCalendar'] .= '<option value="' . $userList[$i]['ID'] . '"'
						. ($this->TempUser->ID == $userList[$i]['ID'] ? ' selected' : '') . '>'
						. $userList[$i]['FirstName'].' '.$userList[$i]['LastName'].'</option>';
					}
				}
								
			}
			if ($this->TempUser) $clientsAccessList = $this->TempUser->GetUserItemAccess('clients', CU_ACCESS_READ);
			else $clientsAccessList = $this->User->GetUserItemAccess('clients', CU_ACCESS_READ);

			if ($clientsAccessList == '-1') {
				$clientsAccessList = NULL;
				$clientIDs = $this->DB->Query(SQL_GET_CLIENT_IDS);
					for ($i = 0; $i < count($clientIDs); $i++) {
						if ($userID) $clientsAccessList .= ($this->TempUser->HasUserItemAccess('clients',$clientIDs[$i]['ID'], CU_ACCESS_READ) ? $clientIDs[$i]['ID'].',' : '0');
						else $clientsAccessList .= ($this->User->HasUserItemAccess('clients',$clientIDs[$i]['ID'], CU_ACCESS_READ) ? $clientIDs[$i]['ID'].',' : '0');
					}
				$clientsAccessList = substr($clientsAccessList, 0, -1);
			}

			if ($this->TempUser) $projectsAccessList = $this->TempUser->GetUserItemAccess('projects', CU_ACCESS_READ);
			else $projectsAccessList = $this->User->GetUserItemAccess('projects', CU_ACCESS_READ);

			if ($projectsAccessList == '-1') {
				$projectsAccessList = NULL;
				$projectIDs = $this->DB->Query(SQL_GET_PROJECT_IDS);
				for ($i = 0; $i < count($projectIDs); $i++) {
					if ($userID) $projectsAccessList .= ($this->TempUser->HasUserItemAccess('projects',$projectIDs[$i]['ID'], CU_ACCESS_READ) ? $projectIDs[$i]['ID'].',' : '0');
					else $projectsAccessList .= ($this->User->HasUserItemAccess('projects',$projectIDs[$i]['ID'], CU_ACCESS_READ) ? $projectIDs[$i]['ID'].',' : '0');
				}
				$projectsAccessList = substr($projectsAccessList, 0, -1);
			}

			if ($clientID) {
				// If client is selected

				// Show client's projects
				$SQL = sprintf(SQL_GET_PROJECT_IDS_FOR_CLIENT, $clientID, $projectsAccessList);
				$projectsList = $this->DB->Query($SQL);
				for ($i = 0; $i < count($projectsList); $i++) {
					$projects_sql_list .= $projectsList[$i]['ProjectID'].',';
				}
				$projects_sql_list = substr($projects_sql_list, 0, -1);

				// If project is selected
				if ($projectID) $projects_access_list = $projectID;
				else $projects_access_list = $projects_sql_list;
			}
			else {
				// No client
				if ($projectID) {
					// If project is selected
					$projects_access_list = $projectID;
					$projects_sql_list = $projectsAccessList;
				}
				else {
					//Show all projects
					$projects_access_list = $projectsAccessList;
					$projects_sql_list = $projectsAccessList;
				}
			}

			//Make Client dropdown - selecting clientID if possible

			$tmpl['clients'] = '<option value="">'.MSG_ALL_CLIENTS.'</option>';
			$SQL = sprintf(SQL_GET_CLIENTS_IN,$clientsAccessList);
			$clientList = $this->DB->Query($SQL);
			if ($clientList) {
				for ($i = 0; $i < count($clientList); $i++) {
					$tmpl['clients'] .= '<option value="'.$clientList[$i]['ClientID'].'"'.($clientID == $clientList[$i]['ClientID'] ? ' selected' : '').'>'.$clientList[$i]['ClientName'].'</option>';
				}
			}

			//Make Project dropdown - selecting projectID if possible

			$tmpl['projects'] = '<option value="">'.MSG_ALL_PROJECTS.'</option>';
			$SQL = sprintf(SQL_GET_PROJECTS_IN, $projects_sql_list);
			$projectsList = $this->DB->Query($SQL);
			if ($projectsList) {
				for ($i = 0; $i < count($projectsList); $i++) {
					$tmpl['projects'] .= '<option value="'.$projectsList[$i]['ProjectID'].'"'.($projectID == $projectsList[$i]['ProjectID'] ? ' selected' : '').'>'.$projectsList[$i]['ProjectName'].'</option>';
				}
			}


			if (!is_numeric($offset)) $offset = $this->getPreviousStartDay($now);

			$tmpl['lblWeek']			= MSG_WEEK;
			$tmpl['lblProject']		= MSG_SUBJECT;
			$tmpl['lblProjectOwner']	= MSG_OWNER;
			$tmpl['lblResource']		= MSG_RESOURCE;
			$tmpl['lblItem']			= MSG_ITEM;
			$tmpl['WeekNumber']		 = $this->getWeekNumber($offset);

			$prev = MSG_PREV;
			$next = MSG_NEXT;
			$prevoffset = $offset - 7*86400;
			$newoffset = $offset + 7*86400;
			$prev = '<a class="linkon" href="index.php?module=calendar&amp;action=week&amp;date='.$prevoffset.'">' . $prev . '</a>';
			$next = '<a class="linkon" href="index.php?module=calendar&amp;action=week&amp;date='.$newoffset.'">' . $next . '</a>';
			$tmpl['PREV'] = $prev;
			$tmpl['NEXT'] = $next;

			$tmpl['url'] = 'index.php?module=calendar&action=week&date='.$offset;

			$this->setTemplate('week_view_header', $tmpl);
			unset($tmpl);

			$startDate = $offset - 86400;
			$endDate = $offset + (86400 * 7);

			// Get the user resources. First select the resourceID from the userID.
			$userResourceSQL = sprintf(SQL_USER_RESOURCE,($userID ? $userID : $this->User->ID)); // use the current user if it's not set
			$rows = $this->DB->QuerySingle( $userResourceSQL );
			$resourceID = $rows['ID'];

			$gmStartDate = gmmktime(0, 0, 0, date('m', $startDate), date('d', $startDate), date('Y', $startDate));
			$gmEndDate = gmmktime(0, 0, 0, date('m', $endDate), date('d', $endDate), date('Y', $endDate));
			$sql = sprintf( SQL_GET_DAYID_EPOCH, $gmStartDate, $gmEndDate );
			$days = $this->DB->Query( $sql );

//			$where = ' Month = \'' . $month . '\' AND Year = \'' . $year . '\'';
//			$daysSQL = sprintf(GET_HOURS_DAY_FOR_RESOURCE,$resourceID,$where);
//			$days = $this->DB->Query($daysSQL);
//			if (count($days) == 0) {
//					$committedHoursWhereIn = '(1=0)';
//			} else {
				$committedHoursWhereIn = 'tblTaskResourceDay.DayID >= ' . $days[0]['DayID'] . ' AND tblTaskResourceDay.DayID  <= ' . $days[count($days) - 1]['DayID'];
//			}

			// when a client or project is selected only show the commitment for that subset
			if ($projectID) {
				$committedHoursSQL = sprintf(SQL_HOURS_COMMITTED_TASK_DAYS_MONTH_PROJECT,$resourceID,$committedHoursWhereIn,$projectID);
			}
			else if ($clientID) {
				$committedHoursSQL = sprintf(SQL_HOURS_COMMITTED_TASK_DAYS_MONTH_CLIENT,$resourceID,$committedHoursWhereIn,$clientID);
			}
			else {
				$committedHoursSQL = sprintf(SQL_HOURS_COMMITTED_TASK_DAYS_MONTH,$resourceID,$committedHoursWhereIn);
			}
			$committedHours = $this->DB->Query($committedHoursSQL);

			// Build an array of day names to iterate over.
			$dayNames = array(MSG_MONDAY, MSG_TUESDAY, MSG_WEDNESDAY, MSG_THURSDAY, MSG_FRIDAY, MSG_SATURDAY, MSG_SUNDAY);
			if (Settings::get('WeekStart') == 'Sunday')
				 array_unshift($dayNames, array_pop($dayNames));

			for ($day = $startDate; $day < $endDate; $day = $day + 86400) {

				if ($day != $startDate) $this->setTemplate('week_view_spacer');
				unset($tmpl);

				// Calculate the day name and use the language tokens.
				$dowDateN = ( date( 'w', $day ) == 0 ) ? 7 : date( 'w', $day );  // Emulate date( 'N' ) for PHP versions < 5.1.0
				$dayOfWeek = (Settings::get('WeekStart') == 'Sunday') ? date('w', $day) : $dowDateN - 1;
				$dayName = $dayNames[$dayOfWeek];

				$tmpl['Date'] = '<b>'.$dayName.'</b> '.Format::date(date('Y-m-d', $day));
				$tmpl['Item'] = '';

				$gmDay = gmmktime(0, 0, 0, date('m', $day), date('d', $day), date('Y', $day));
				$sql = sprintf( SQL_GET_DAYID_EPOCH, $gmDay, $gmDay );
				$rows = $this->DB->QuerySingle( $sql );

				for ($k = 0; $k < count($committedHours); $k++) {
					if ($rows['DayID'] == $committedHours[$k]['DayID']) {
						$tmpl['Item'] .= '<img src="images/icons/resource16x16.gif" height="10" border="0"><a href="index.php?module=projects&action=taskview&projectid=' . $committedHours[$k]['ProjectID'] . '&taskid=' . $committedHours[$k]['TaskID'] . '">' . $committedHours[$k]['HoursCommitted'] . 'h ' . $committedHours[$k]['Name'] . '</a><br>';
					}
				}

				$SQL = sprintf(SQL_GET_PROJECTS_STARTING_AND_FINISHING, date('Y-m-d',$day), $projects_access_list);
				$projectsStartingList = $this->DB->Query($SQL);
				if ($projectsStartingList) {
					for ($j = 0; $j < count($projectsStartingList); $j++) {
						$imgS = ( $projectsStartingList[$j]['StartDate'] == date('Y-m-d',$day) ) ? '<img src="images/icons/icon_cal_s.gif" border="0">' : ''; 
						$imgF = ( $projectsStartingList[$j]['EndDate'] == date('Y-m-d',$day) ) ? '<img src="images/icons/icon_cal_f.gif" border="0">' : ''; 
						$tmpl['Item'] .= '<img src="images/icons/project16x16.gif" height="10" border="0"><a style="bg-color: '.$projectsStartingList[$j]['Colour'].';" href="index.php?module=projects&action=view&projectid='.$projectsStartingList[$j]['ProjectID'].'" title="'.$projectsStartingList[$j]['ClientName'].' &gt; '.$projectsStartingList[$j]['ProjectName'].' &gt; Starts '.$projectsStartingList[$j]['StartDate'].' &gt; Ends '.$projectsStartingList[$j]['EndDate'].' &gt; Owned by '.$projectsStartingList[$j]['ProjectOwner'].'... Click here for more.">'.$projectsStartingList[$j]['ProjectName']." $imgS $imgF</a><br>";
					}
				}

/* Commented out as this was combined with the query above for efficiency reasons
				$SQL = sprintf(SQL_GET_PROJECTS_FINISHING, date('Y-m-d',$day), $projects_access_list);
				$projectsFinishingList = $this->DB->Query($SQL);
				if ($projectsFinishingList) {
					for ($j = 0; $j < count($projectsFinishingList); $j++) {
						$tmpl['Item'] .= '<img src="images/icons/project16x16.gif" height="10" border="0"><a style="bg-color: '.$projectsFinishingList[$j]['Colour'].';" href="index.php?module=projects&action=view&projectid='.$projectsFinishingList[$j]['ProjectID'].'" title="'.$projectsFinishingList[$j]['ClientName'].' &gt; '.$projectsFinishingList[$j]['ProjectName'].' &gt; Starts '.$projectsFinishingList[$j]['StartDate'].' &gt; Ends '.$projectsFinishingList[$j]['EndDate'].' &gt; Owned by '.$projectsFinishingList[$j]['ProjectOwner'].'... Click here for more.">'.$projectsFinishingList[$j]['ProjectName'].' <img src="images/icons/icon_cal_f.gif" border="0"></a><br>';
					}
				}
*/

				$SQL = sprintf(SQL_GET_TASKS_STARTING_AND_DUE, date('Y-m-d',$day), ($userID ? $userID : $this->User->ID), $projects_access_list);
				$tasksStartingList = $this->DB->Query($SQL);
				if ($tasksStartingList) {
					for ($j = 0; $j < count($tasksStartingList); $j++) {
						$imgS = ( $tasksStartingList[$j]['TaskStartDate'] == date('Y-m-d',$day) ) ? '<img src="images/icons/icon_cal_s.gif" border="0">' : ''; 
						$imgF = ( $tasksStartingList[$j]['TaskEndDate'] == date('Y-m-d',$day) ) ? '<img src="images/icons/icon_cal_f.gif" border="0">' : ''; 
						if ( $this->TempUser ? $this->TempUser->HasUserItemAccess('projects', $tasksStartingList[$k]['ProjectID'], CU_ACCESS_READ) :  $this->User->HasUserItemAccess('projects', $tasksStartingList[$k]['ProjectID'], CU_ACCESS_READ)) {
							$tmpl['Item'] .= '<img src="images/icons/task16x16.gif" height="10" border="0"><span style="bg-color: '.$tasksStartingList[$j]['Colour'].';"><a style="bg-color: '.$tasksStartingList[$j]['Colour'].';" href="index.php?module=projects&action=taskview&projectid='.$tasksStartingList[$j]['ProjectID'].'&taskid='.$tasksStartingList[$j]['TaskID'].'" title="'.$tasksStartingList[$j]['ClientName'].' &gt; '.$tasksStartingList[$j]['ProjectName'].' &gt; '.$tasksStartingList[$j]['TaskName'].' &gt; Starts '.$tasksStartingList[$j]['TaskStartDate'].' &gt; Ends '.$tasksStartingList[$j]['TaskEndDate'].' &gt; Owned by '.$tasksStartingList[$j]['TaskOwner'].'... Click here for more.">'.$this->stringShave($tasksStartingList[$j]['TaskName'],18)."</a> $imgS $imgF</span><br>";
						}
						else if ($this->DB->QuerySingle(sprintf(SQL_GET_TASKID, ($userID ? $userID : $this->User->ID), $tasksStartingList[$j][TaskID]))) {
							$tmpl['Item'] .= '<img src="images/icons/task16x16.gif" height="10" border="0"><span style="bg-color: '.$tasksStartingList[$j]['Colour'].';"><a style="bg-color: '.$tasksStartingList[$j]['Colour'].';" href="index.php?module=springboard&action=view&projectid='.$tasksStartingList[$j]['ProjectID'].'&id='.$tasksStartingList[$j]['TaskID'].'" title="'.$tasksStartingList[$j]['ClientName'].' &gt; '.$tasksStartingList[$j]['ProjectName'].' &gt; '.$tasksStartingList[$j]['TaskName'].' &gt; Starts '.$tasksStartingList[$j]['TaskStartDate'].'&gt; Ends '.$tasksStartingList[$j]['TaskEndDate'].' &gt; Owned by '.$tasksStartingList[$j]['TaskOwner'].'... Click here for more.">'.$this->stringShave($tasksStartingList[$j]['TaskName'],18)."</a> $imgS $imgF</span><br>";
						}
					}
				}

/* Commented out as this was combined with the query above for efficiency reasons
				$SQL = sprintf(SQL_GET_TASKS_DUE, date('Y-m-d',$day), $this->User->ID, $projects_access_list);
				$tasksDueList = $this->DB->Query($SQL);
				if ($tasksDueList) {
					for ($j = 0; $j < count($tasksDueList); $j++) {
						if ($this->User->HasUserItemAccess('projects', $tasksStartingList[$j]['ProjectID'], CU_ACCESS_READ)) {
							$tmpl['Item'] .= '<img src="images/icons/task16x16.gif" height="10" border="0"><span style="bg-color: '.$tasksDueList[$j]['Colour'].';"><a style="bg-color: '.$tasksDueList[$j]['Colour'].';" href="index.php?module=projects&action=taskview&projectid='.$tasksDueList[$j]['ProjectID'].'&taskid='.$tasksDueList[$j]['TaskID'].'" title="'.$tasksDueList[$j]['ClientName'].' &gt; '.$tasksDueList[$j]['ProjectName'].' &gt; '.$tasksDueList[$j]['TaskName'].' &gt; Starts '.$tasksDueList[$j]['TaskStartDate'].' &gt; Ends '.$tasksDueList[$j]['TaskEndDate'].' &gt; Owned by '.$tasksDueList[$j]['TaskOwner'].'... Click here for more.">'.$this->stringShave($tasksDueList[$j]['TaskName'],18).' <img src="images/icons/icon_cal_f.gif" border="0"></a></span><br>';
						}
						else if ($this->DB->QuerySingle(sprintf(SQL_GET_TASKID, $this->User->ID, $tasksDueList[$j]['TaskID']))) {
							$tmpl['Item'] .= '<img src="images/icons/task16x16.gif" height="10" border="0"><span style="bg-color: '.$tasksDueList[$j]['Colour'].';"><a style="bg-color: '.$tasksDueList[$j]['Colour'].';" href="index.php?module=springboard&action=view&projectid='.$tasksDueList[$j]['ProjectID'].'&id='.$tasksDueList[$j]['TaskID'].'" title="'.$tasksDueList[$j]['ClientName'].' &gt; '.$tasksDueList[$j]['ProjectName'].' &gt; '.$tasksDueList[$j]['TaskName'].' &gt; Starts '.$tasksDueList[$j]['TaskStartDate'].' &gt; Ends '.$tasksDueList[$j]['TaskEndDate'].' &gt; Owned by '.$tasksDueList[$j]['TaskOwner'].'... Click here for more.">'.$this->stringShave($tasksDueList[$j]['TaskName'],18).' <img src="images/icons/icon_cal_f.gif" border="0"></a></span><br>';
						}
					}
				}
*/

				$SQL = sprintf(SQL_GET_CALENDAR_NOTES, date('Y-m-d',$day));
				$calendarNotes = $this->DB->Query($SQL);
				if ($calendarNotes) {
					for ($j = 0; $j < count($calendarNotes); $j++) {
						$tmpl['Item'] .= '<img src="images/icons/icon_cal.gif" border="0">&nbsp;<a class="calendar" style="bg-color: '.$calendarNotes[$j]['Colour'].';" href="index.php?module=calendar&action=view&id='.$calendarNotes[$j]['ID'].'" title="'.$calendarNotes[$j]['Name'].' &gt; '.$calendarNotes[$j]['Description'].'">'.$this->stringShave($calendarNotes[$j]['Name'],18).'</a><br>';
					}
				}

				$this->setTemplate('week_view_item',$tmpl);
				unset($tmpl);
			}

			$prev = MSG_PREV;
			$next = MSG_NEXT;

			$prevoffset = $offset - 7*86400;
			$newoffset = $offset + 7*86400;
			$prev = '<a class="linkon" href="index.php?module=calendar&amp;action=week&amp;date='.$prevoffset.'">' . $prev . '</a>';
			$next = '<a class="linkon" href="index.php?module=calendar&amp;action=week&amp;date='.$newoffset.'">' . $next . '</a>';
			$footer_tmpl['PREV'] = $prev;
			$footer_tmpl['NEXT'] = $next;
			$this->setTemplate('week_view_footer', $footer_tmpl);
			unset($tmpl);


					$modAction[] = '<a id="dash-toggler" href="#" onclick="toggleDash(); return false;">SHOW DASH</a>';

					if ($clientID) {
							// If client is selected

							// Show client's projects
							$SQL = sprintf(SQL_GET_PROJECT_IDS_FOR_CLIENT, $clientID, $projectsAccessList);
							$projectsList = $this->DB->Query($SQL);
							for ($i = 0; $i < count($projectsList); $i++) {
									$projects_sql_list .= $projectsList[$i]['ProjectID'].',';
							}
							$projects_sql_list = substr($projects_sql_list, 0, -1);

							// If project is selected
							if ($projectID) $projects_access_list = $projectID;
							else $projects_access_list = $projects_sql_list;
					}
					else {
							// No client

							// If project is selected
							if ($projectID) {
									$projects_access_list = $projectID;
									$projects_sql_list = $projectsAccessList;
							}
							else {
									//Show all projects
									$projects_access_list = $projectsAccessList;
									$projects_sql_list = $projectsAccessList;
							}
					}

					//Make Client dropdown - selecting clientID if possible
					$tmplDash['clients'] = '<option value="">'.MSG_ALL_CLIENTS.'</option>';
					$SQL = sprintf(SQL_GET_CLIENTS_IN, $clientsAccessList);
					$clientList = $this->DB->Query($SQL);
					if ($clientList) {
							for ($i = 0; $i < count($clientList); $i++) {
									$tmplDash['clients'] .= '<option value="'.$clientList[$i]['ClientID'].'"'.($clientID == $clientList[$i]['ClientID'] ? ' selected' : '').'>'.$clientList[$i]['ClientName'].'</option>';
							}
					}

					//Make Project dropdown - selecting projectID if possible
					$tmplDash['projects'] = '<option value="">'.MSG_ALL_PROJECTS.'</option>';
					$SQL = sprintf(SQL_GET_PROJECTS_IN, $projects_sql_list);
					$projectsList = $this->DB->Query($SQL);
					if ($projectsList) {
							for ($i = 0; $i < count($projectsList); $i++) {
									$tmplDash['projects'] .= '<option value="'.$projectsList[$i]['ProjectID'].'"'.($projectID == $projectsList[$i]['ProjectID'] ? ' selected' : '').'>'.$projectsList[$i]['ProjectName'].'</option>';
							}
					}

					$tmplDash['month'] = $month;
					$tmplDash['year'] = $year;
					$tmplDash['user'] = $userID;
					$tmplDash['period'] = "week";
					$tmplDash['txtAdminOnly'] = NULL;
					if ($this->User->IsAdmin){
						//Setup the User Swap List.
						$SQL = sprintf(SQL_GET_USER_LIST);
						$userList = $this->DB->Query($SQL);
						$tmplAdmin['users'] = "";
						if ($userList) {
								for ($i = 0; $i < count($userList); $i++) {
										$tmplAdmin['users'] .= '<option value="' . $userList[$i]['ID'] . '"' . ($this->TempUser->ID == $userList[$i]['ID'] ? ' selected' : '') . '>'
										. $userList[$i]['FirstName'].' '.$userList[$i]['LastName'].'</option>';
								}
						}
						$tmplAdmin['month'] = $month;
						$tmplAdmin['year'] = $year;
						$tmplAdmin['period'] = "week";
						$tmplDash['txtAdminOnly'] = $this->getTemplate("dashBlockAdmin", $tmplAdmin);
						$this->setDash($this->getTemplate("dashBlock", $tmplDash));
					} 
					$this->setDash($this->getTemplate("dashBlock", $tmplDash));
					$this->setHeader(MSG_CALENDAR);
					$this->setModule($modHeader,$modAction);
					$this->Render();
		}
		else
		{
			$this->ThrowError(2001);
		}
	}

	function OldMonthView()
	{
		if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_READ)) {

			$modHeader = MSG_MONTH_VIEW;
			$calendarStyle = '<link rel="stylesheet" type="text/css" href="assets/styles/calendar.css">' . NewLine;
			$modInsert = $calendarStyle;
			$this->CreateTabs('month');

			if (Request::any('action') == 'resourceupdatesave') {

				// receive the data from POST
				$resourceID = Request::post('resourceID');
				$newDays = Request::post('days');
				$month = Request::post('month');
				$year = Request::post('year');

				// SELECT the day ids for that month
				$dayIDsTotalHoursCommittedSQL = sprintf(SQL_GET_IDS_FOR_MONTH_AND_HOURS_COMMITTED_OF_TASKS,$resourceID, $month, $year);
				$RS =& new DBRecordset();
				$RS->Open($dayIDsTotalHoursCommittedSQL, $this->DB);
				$dayIDsTotalHoursCommitted = $RS->_Result;
				$RS->Close();
				unset($RS);

				// create the SQL statements and check that they are not going to set there availability to less then the time they are Committed to tasks

				$overCommitted = '';
				for ($i = 1; $i < count($dayIDsTotalHoursCommitted) + 1;$i++) {

					// new days array is indexed by the day of the month so the $dayIDsTotalHoursCommitted array is off by one
					if ($newDays[$i]['hoursAvailable'] > MAX_DAY_LENGTH) $newDays[$i]['hoursAvailable'] = MAX_DAY_LENGTH;
					if ($newDays[$i]['dayID']) $SQL[count($SQL)] = 'Update tblResourceDay set HoursAvailable = ' . $newDays[$i]['hoursAvailable'] . ' WHERE DayID = ' . $newDays[$i]['dayID'] . ' AND ResourceID = ' . $resourceID;
					else if ($newDays[$i]['hoursAvailable']) $SQL[count($SQL)] = 'INSERT INTO tblResourceDay (ResourceID, DayID, HoursAvailable, HoursCommittedCache) VALUES (' . $resourceID . ',' . $dayIDsTotalHoursCommitted[$i - 1]['ID'] . ',' . $newDays[$i]['hoursAvailable'] . ',0)';

					// store the day id for any over Committed days in the array
					if ($newDays[$i]['hoursAvailable'] < $dayIDsTotalHoursCommitted[$i - 1]['HoursCommittedCache']) {
						$overCommitted .= '\'' . $newDays[$i]['dayID'] . '\',';
					}
				}

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
					$errorMessage .= '<td>';
					$errorMessage .= '<p class="errors"><b>Error:</b> Unable to set the availability for this resource because the resource would become over committed for the following:<br>';
					for ($i = 0; $i < count($daysOverCommitted); $i++) {
						if ($i > 0 && $daysOverCommitted[$i]['TaskID'] != $daysOverCommitted[($i - 1)]['TaskID']) {
							$errorMessage = substr($errorMessage,0,strlen($errorMessage) - 2);
							$errorMessage .= '<br>Task: <a href="?module=projects&action=taskview&projectid='. $daysOverCommitted[$i]['ProjectID'] . '&taskid=' . $daysOverCommitted[$i]['TaskID'] . '">' . $daysOverCommitted[$i]['Name'] . '</a><br>';
						}
						else if ($i == 0) $errorMessage .= 'Task: <a href="?module=projects&action=taskview&projectid='. $daysOverCommitted[$i]['ProjectID'] . '&taskid=' . $daysOverCommitted[$i]['TaskID'] . '">' . $daysOverCommitted[$i]['Name'] . '</a><br>';
						$errorMessage .=  date('d/m/Y',$daysOverCommitted[$i]['Epoch']) . ', ';
					}
					$errorMessage = substr($errorMessage,0,strlen($errorMessage) - 2);
					$errorMessage .= '</p></td></tr>';
				}
				else {
					// do it
					if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE)) {
						$tmpl['OK'] = MSG_OK;
						for ($i = 0; $i < count($SQL);$i++) {
							$this->DB->Execute($SQL[$i]);
							// echo 'SQL[' . $i . ']:' . $SQL[$i] . '<br>';
						}
					}
					else {
						// the user doesn't have access to update records.
						$this->ThrowError(2001);
					}
					// Response::redirect('index.php?module=administration&action=resourceupdate&resourceID=' . $resourceID);
		}
			}


			$projectID = Request::get('projectID', Request::R_INT);
			$clientID = Request::get('clientID', Request::R_INT);

			if ($projectID) {
				if (!($this->User->HasUserItemAccess('projects', $projectID, CU_ACCESS_READ)))
				$this->ThrowError(2001);
			}

			if ($clientID) {
				if (!($this->User->HasUserItemAccess('clients', $clientID, CU_ACCESS_READ)))
				$this->ThrowError(2001);
			}

			$tmpl['projectID'] = $projectID;
			$tmpl['clientID'] = $clientID;

			if (Request::any('month')) {
				$month = Request::any('month');
				$year = Request::any('year');
			}
			else {
				$month = date('n',time());
				$year = date('Y',time());
			}

			if (($nextMonth = $month + 1) == 13) {
				$nextMonth = 1;
				$nextYear = $year + 1;
			}
			else $nextYear = $year;
			if (($previousMonth = $month  - 1) == 0) {
				$previousMonth = 12;
				$previousYear = $year - 1;
			}
			else $previousYear = $year;
			if (($previousPreviousMonth = $previousMonth  - 1) == 0) {
				$previousPreviousMonth = 12;
				$previousPreviousYear = $previousYear - 1;
			}
			else $previousPreviousYear = $previousYear;

			//Display User select tool if user has admin permissions
			$tmpl['users'] = $this->User->Fullname;

			if ($this->User->HasModuleItemAccess('administration', CU_ACCESS_ALL, CU_ACCESS_READ)) {
				// Create Temp user for creating correct permissions
				$userID = Request::get('userID', Request::R_INT);

				if ($userID) {
					$this->Session->Set('calendarID', $userID);
				}
				else if ($this->Session->Get('calendarID')) {
					$userID = $this->Session->Get('calendarID');
				}
				else {
					$userID = $this->User->ID;
				}
								$this->Session->Set('calendarID', $userID);
								$this->TempUser		 =& new User();
								$this->TempUser->Initialise($userID, $this->DB);

				$tmpl['users'] = '<select name="UserID" onchange="location = \'index.php?module=calendar&action=monthview&amp;month=' . $month . '&amp;year=' . $year . '&userID=\''
												.' + this.options[selectedIndex].value;" class="TaskUpdate_dd" id="select" style="width:100%">';
				$SQL = sprintf(SQL_GET_USER_LIST);
				$userList = $this->DB->Query($SQL);

				if ($userList) {
					for ($i = 0; $i < count($userList); $i++) {
						$tmpl['users'] .= '<option value="' . $userList[$i]['ID'] . '"' . ($this->TempUser->ID == $userList[$i]['ID'] ? ' selected' : '') . '>'
						. $userList[$i]['FirstName'].' '.$userList[$i]['LastName'].'</option>';
					}
				}
				$tmpl['users'] .= '</select>';

			}

			if ($userID)	$clientsAccessList = $this->TempUser->GetUserItemAccess('clients', CU_ACCESS_READ);
			else	$clientsAccessList = $this->User->GetUserItemAccess('clients', CU_ACCESS_READ);

			if ($clientsAccessList == '-1') {
				$clientsAccessList = NULL;
				$clientIDs = $this->DB->Query(SQL_GET_CLIENT_IDS);
					for ($i = 0; $i < count($clientIDs); $i++) {
						if ($userID) $clientsAccessList .= ($this->TempUser->HasUserItemAccess('clients',$clientIDs[$i]['ID'], CU_ACCESS_READ) ? $clientIDs[$i]['ID'].',' : '0');
						else $clientsAccessList .= ($this->User->HasUserItemAccess('clients',$clientIDs[$i]['ID'], CU_ACCESS_READ) ? $clientIDs[$i]['ID'].',' : '0');
					}
				$clientsAccessList = substr($clientsAccessList, 0, -1);
			}

			if ($userID) $projectsAccessList = $this->TempUser->GetUserItemAccess('projects', CU_ACCESS_READ);
			else $projectsAccessList = $this->User->GetUserItemAccess('projects', CU_ACCESS_READ);
			if ($projectsAccessList == '-1') {
				$projectsAccessList = NULL;
				$projectIDs = $this->DB->Query(SQL_GET_PROJECT_IDS);
				for ($i = 0; $i < count($projectIDs); $i++) {
					if ($userID) $projectsAccessList .= ($this->TempUser->HasUserItemAccess('projects',$projectIDs[$i]['ID'], CU_ACCESS_READ) ? $projectIDs[$i]['ID'].',' : '0');
					else $projectsAccessList .= ($this->User->HasUserItemAccess('projects',$projectIDs[$i]['ID'], CU_ACCESS_READ) ? $projectIDs[$i]['ID'].',' : '0');
				}
				$projectsAccessList = substr($projectsAccessList, 0, -1);
			}

			if ($clientID) {
				// If client is selected

				// Show client's projects
				$SQL = sprintf(SQL_GET_PROJECT_IDS_FOR_CLIENT, $clientID, $projectsAccessList);
				$projectsList = $this->DB->Query($SQL);
				for ($i = 0; $i < count($projectsList); $i++) {
					$projects_sql_list .= $projectsList[$i]['ProjectID'].',';
				}
				$projects_sql_list = substr($projects_sql_list, 0, -1);

				// If project is selected
				if ($projectID) $projects_access_list = $projectID;
				else $projects_access_list = $projects_sql_list;
			}
			else {
				// No client

				// If project is selected
				if ($projectID) {
					$projects_access_list = $projectID;
					$projects_sql_list = $projectsAccessList;
				}
				else {
					//Show all projects
					$projects_access_list = $projectsAccessList;
					$projects_sql_list = $projectsAccessList;
				}
			}

			//Make Client dropdown - selecting clientID if possible
			$tmpl['url'] = 'index.php?module=calendar&action=monthview&amp;month=' . $month . '&amp;year=' . $year . '&userID=' . $userID;
			$tmpl['clients'] = '<option value="">'.MSG_ALL_CLIENTS.'</option>';
			$SQL = sprintf(SQL_GET_CLIENTS_IN,$clientsAccessList);
			$clientList = $this->DB->Query($SQL);
			if ($clientList) {
				for ($i = 0; $i < count($clientList); $i++) {
					$tmpl['clients'] .= '<option value="'.$clientList[$i]['ClientID'].'"'.($clientID == $clientList[$i]['ClientID'] ? ' selected' : '').'>'.$clientList[$i]['ClientName'].'</option>';
				}
			}

			//Make Project dropdown - selecting projectID if possible

			$tmpl['projects'] = '<option value="">'.MSG_ALL_PROJECTS.'</option>';
			$SQL = sprintf(SQL_GET_PROJECTS_IN, $projects_sql_list);
			$projectsList = $this->DB->Query($SQL);
			if ($projectsList) {
				for ($i = 0; $i < count($projectsList); $i++) {
					$tmpl['projects'] .= '<option value="'.$projectsList[$i]['ProjectID'].'"'.($projectID == $projectsList[$i]['ProjectID'] ? ' selected' : '').'>'.$projectsList[$i]['ProjectName'].'</option>';
				}
			}

// main calendar to update the availability of the resource

			// select the resourceID from the userID
			$userResourceSQL = sprintf(SQL_USER_RESOURCE,($userID ? $userID : $this->User->ID)); // use the current user if it's not set
			$RS =& new DBRecordset();
			$RS->Open($userResourceSQL, $this->DB);
			$userResource = $RS->_Result[0];
			$RS->Close();
			unset($RS);

			$resourceID = $userResource['ID'];
			if (!$firstDayOfWeekCurrentMonth = date('w',mktime(0,0,0,$month,1,$year))) $firstDayOfWeekCurrentMonth = '7';
			$currentDayEpoch = gmmktime(0,0,0,date('n'),date('d'),date('Y'));
			$numberOfDaysInMonth = date('t',mktime(0,0,0,$month,1,$year));
			$previousNumberOfDaysInMonth = date('t',mktime(0,0,0,$previousMonth,1,$previousYear));

			$daysSQL = sprintf(GET_HOURS_DAY_FOR_RESOURCE, $resourceID, $month, $year);
			$days = $this->DB->Query($daysSQL);
			if (count($days) == 0) {
					$committedHoursWhereIn = '(1=0)';
			} else {
				$committedHoursWhereIn = 'tblTaskResourceDay.DayID >= ' . $days[0]['ID'] . ' AND tblTaskResourceDay.DayID  <= ' . $days[count($days) - 1]['ID'];
			}
			// when a client or project is selected only show the commitment for that subset
			if ($projectID) {
				$committedHoursSQL = sprintf(SQL_HOURS_COMMITTED_TASK_DAYS_MONTH_PROJECT,$resourceID,$committedHoursWhereIn,$projectID);
			}
			else if ($clientID) {
				$committedHoursSQL = sprintf(SQL_HOURS_COMMITTED_TASK_DAYS_MONTH_CLIENT,$resourceID,$committedHoursWhereIn,$clientID);
			}
			else {
				$committedHoursSQL = sprintf(SQL_HOURS_COMMITTED_TASK_DAYS_MONTH,$resourceID,$committedHoursWhereIn);
			}
			$committedHours = $this->DB->Query($committedHoursSQL);

			$tmplHeader['txtMonth'] = $month;
			$tmplHeader['txtYear'] =  $year;
			$tmplHeader['txtResourceID'] =  $resourceID;
			$tmplHeader['lblTitle'] = MSG_AVAILABILITY . ' ' . MSG_FOR . ' ' . MSG_RESOURCE . ' ' . $userResource['FirstName'] . ' ' . $userResource['LastName'];

			if ($errorMessage) $tmplHeader['lblErrorMessage'] = $errorMessage;
			else $tmplHeader['lblErrorMessage'] = '';

			$this->setTemplate('month_view_header', $tmplHeader);
			unset($tmplHeader);

			$tmpl['lblMainCalendar'] = '';
			$tmpl['lblMainCalendar'] .= '<table><tr>';
			$tmpl['lblMainCalendar'] .= '<td colspan="7"><span class="header">' . date('F, Y',mktime(0,0,0,$month,1,$year));
			$tmpl['lblMainCalendar'] .= '</span>&nbsp;&nbsp;';
			$tmpl['lblMainCalendar'] .= '<a href="?module=calendar&action=monthview&amp;month=' . $previousMonth . '&amp;year=' . $previousYear . '&userID=' . $userID . '">'.MSG_PREV.'</a>&nbsp;|&nbsp;';
			$tmpl['lblMainCalendar'] .= '<a href="?module=calendar&action=monthview&amp;month=' . $nextMonth . '&amp;year=' . $nextYear . '&userID=' . $userID . '">'.MSG_NEXT.'</a><br>&nbsp;</td>';
			$tmpl['lblMainCalendar'] .= '</tr><tr>' . "\n";
			// headings for each day of the week
			for ($i = 1; $i < 8; $i++) {
				$tmpl['lblMainCalendar'] .= '<th class="heading">' . date('l',mktime(0,0,0,1,$i,2001)) . '</th>';
			}
			$tmpl['lblMainCalendar'] .=	'</tr><tr>' . "\n";
			$dayOfWeekPointer = 1;
			$nextDaysPointer = 1;
			$j = 0;
			// work out how many rows to display
			if ($firstDayOfWeekCurrentMonth == 7 && $numberOfDaysInMonth >= 30 || $firstDayOfWeekCurrentMonth >= 6 && $numberOfDaysInMonth >= 31) $dayboxes = 42;
			else $dayboxes = 35;

// draw each of the days
			for ($i = 0; $i < $dayboxes; $i++) {
				$tmpl['lblMainCalendar'] .= '<td valign="top" ';
				if ($dayOfWeekPointer == $days[$j]['Weekday'] && $days[$j]['Day'] == ($i + 1 - $firstDayOfWeekCurrentMonth + 1)) {
					$dayBoxEpoch = $days[$j]['Epoch'];
					if ($currentDayEpoch == $days[$j]['Epoch']) $tmpl['lblMainCalendar'] .= ' style="background:#C3F1FC;"';
					$tmpl['lblMainCalendar'] .= ' class="day droppable" id="day_'.gmdate('Y-m-d',$dayBoxEpoch).'">' . "\n";

					// day layout
					$tmpl['lblMainCalendar'] .=	'<table><tr>';
					$tmpl['lblMainCalendar'] .=	'<th>' . gmdate('d',$days[$j]['Epoch']) . '</th>' . "\n";
					$tmpl['lblMainCalendar'] .= '<td align="right"><input class="newbox" name="days[' . gmdate('j',$days[$j]['Epoch']) . '][hoursAvailable]" type="text" value="' . floatval($days[$j]['HoursAvailable']) . '"/></td>' . "\n";
					$tmpl['lblMainCalendar'] .= '</tr></table>';

					for ($k = 0; $k < count($committedHours); $k++) {
						if ($days[$j]['ID'] == $committedHours[$k]['DayID']) {
							$tmpl['lblMainCalendar'] .= '<img src="images/icons/resource16x16.gif" height="10" border="0"><a href="index.php?module=projects&action=taskview&projectid=' . $committedHours[$k]['ProjectID'] . '&taskid=' . $committedHours[$k]['TaskID'] . '">' . $committedHours[$k]['HoursCommitted'] . 'h ' . $committedHours[$k]['Name'] . '</a><br>';
						}
					}

					$tmpl['lblMainCalendar'] .= '<input type="hidden" name="days[' . gmdate('j',$days[$j]['Epoch']) . '][dayID]" value="' . $days[$j]['ID'] . '">';
					$j++;
				}
				// days before the start of the current month
				else if ($previousNumberOfDaysInMonth - $firstDayOfWeekCurrentMonth + 1 + $i + 1 <= $previousNumberOfDaysInMonth) {
					$dayBoxEpoch = gmmktime(0,0,0,$previousMonth,($previousNumberOfDaysInMonth - $firstDayOfWeekCurrentMonth + $dayOfWeekPointer + 1),$previousYear);
					$tmpl['lblMainCalendar'] .= 'class="day other droppable" id="day_'.gmdate('Y-m-d',$dayBoxEpoch).'">'  . ($previousNumberOfDaysInMonth - $firstDayOfWeekCurrentMonth + $dayOfWeekPointer + 1) . '<br>';
				}
				// days in the month that they dont have avalibality set
				else if ($firstDayOfWeekCurrentMonth + $numberOfDaysInMonth > $i && ($i - $firstDayOfWeekCurrentMonth + 1 < $numberOfDaysInMonth)) {
					$dayBoxEpoch = gmmktime(0,0,0,$month,$i - $firstDayOfWeekCurrentMonth + 1 + 1,$year);
					$tmpl['lblMainCalendar'] .= 'class="day droppable" id="day_'.gmdate('Y-m-d',$dayBoxEpoch).'"';
					if ($currentDayEpoch == $dayBoxEpoch) $tmpl['lblMainCalendar'] .= ' style="background:#C3F1FC;"';
					$tmpl['lblMainCalendar'] .= '>';

					// day layout
					$tmpl['lblMainCalendar'] .=	'<table><tr>';
					$tmpl['lblMainCalendar'] .=	'<th>';
					if (strlen($i - $firstDayOfWeekCurrentMonth + 1 + 1) == 1) $tmpl['lblMainCalendar'] .= '0';
					$tmpl['lblMainCalendar'] .= ($i - $firstDayOfWeekCurrentMonth + 1 + 1) . '</th>' . "\n";
					$tmpl['lblMainCalendar'] .= '<td align="right"><input class="newbox" name="days[' . ($i - $firstDayOfWeekCurrentMonth + 1 + 1) . '][hoursAvailable]" type="text" value="0"/></td>' . "\n";
					$tmpl['lblMainCalendar'] .= '</tr></table>';

				}
				// days after the end of the month
				else {
					$dayBoxEpoch = gmmktime(0,0,0,$nextMonth,date('d',mktime(0,0,0,1,$nextDaysPointer,2001)),$nextYear);
					$tmpl['lblMainCalendar'] .= 'class="day other droppable" id="day_'.gmdate('Y-m-d',$dayBoxEpoch).'">' . date('d',mktime(0,0,0,1,$nextDaysPointer,2001)) . '<br>';
					$nextDaysPointer++;
				}

				// display events for that day
				$baseUrl = "index.php?module=projects&action=view&projectid=";
				$SQL = sprintf(SQL_GET_PROJECTS_STARTING_AND_FINISHING, gmdate('Y-m-d',$dayBoxEpoch), $projects_access_list);
				$p = $this->DB->Query($SQL);
				if ($p) {
					for ($k = 0; $k < count($p); $k++) {
						$startOnThisDay = ( $p[$k]['StartDate'] == gmdate('Y-m-d',$dayBoxEpoch) );
						$icon = ($startOnThisDay) ? 'icon_cal_s.gif' : 'icon_cal_f.gif'; // This is dumb - what if they start and finish on same day?
						$suffix = ($startOnThisDay) ? 'start' : 'finish';
						$id = 'project_'.$p[$k]['ProjectID'].'_'.$suffix;
						$url = 'javascript:alert(\'following link\');'; //"index.php?module=projects&action=view&projectid=".$p[$k]['ProjectID'];
						$title = $p[$k]['ClientName'].' &gt; '.$p[$k]['ProjectName'].' &gt; Starts '.$p[$k]['StartDate'].' &gt; Ends '.$p[$k]['EndDate'].' &gt; Owned by '.$p[$k]['ProjectOwner'].'... Click here for more.';
						$tmpl['lblMainCalendar'] .= '<span id="'.$id.'" class="draggable"><img src="images/icons/project16x16.gif" height="10" border="0"><a class="calendar" href="'.$url.'" title="'.$title.'">'.$this->stringShave($p[$k]['ProjectName'],24).'&nbsp;<span style="background-color: '.$p[$k]['Colour'].';">'." <img src=\"images/icons/$icon\" border=\"0\"></span></a><br></span>";
					}
				}

/* Commented out as this was combined with the query above for efficiency reasons
				$SQL = sprintf(SQL_GET_PROJECTS_FINISHING, gmdate('Y-m-d',$dayBoxEpoch), $projects_access_list);
				$projectsFinishingList = $this->DB->Query($SQL);
				if ($projectsFinishingList) {
					for ($k = 0; $k < count($projectsFinishingList); $k++) {
						$tmpl['lblMainCalendar'] .= '<img src="images/icons/project16x16.gif" height="10" border="0"><a class="calendar" style="bgcolor: '.$projectsFinishingList[$k]['Colour'].';" href="index.php?module=projects&action=view&projectid='.$projectsFinishingList[$k]['ProjectID'].'" title="'.$projectsFinishingList[$k]['ClientName'].' &gt; '.$projectsFinishingList[$k]['ProjectName'].' &gt; Starts '.$projectsFinishingList[$k]['StartDate'].' &gt; Ends '.$projectsFinishingList[$k]['EndDate'].' &gt; Owned by '.$projectsFinishingList[$k]['ProjectOwner'].'... Click here for more.">'.$this->stringShave($projectsFinishingList[$k]['ProjectName'],24).'&nbsp;<span style="padding-right:0.25em;background-color: '.$projectsFinishingList[$k]['Colour'].';"><img src="images/icons/icon_cal_f.gif" border="0"></span></a><br>';
					}
				}
*/

				$SQL = sprintf(SQL_GET_TASKS_STARTING_AND_DUE, gmdate('Y-m-d',$dayBoxEpoch), ($userID ? $userID : $this->User->ID), $projects_access_list);
				$t = $this->DB->Query($SQL);
				if ($t) {
					for ($k = 0; $k < count($t); $k++) {
						$startOnThisDay = ( $t[$k]['TaskStartDate'] == gmdate('Y-m-d',$dayBoxEpoch) );
						$icon = ($startOnThisDay) ? 'icon_cal_s.gif' : 'icon_cal_f.gif';
						$suffix = ($startOnThisDay) ? 'start' : 'finish';
						$id = 'task_'.$t[$k]['TaskID'].'_'.$suffix;
						$title = $t[$k]['ClientName'].' &gt; '.$t[$k]['ProjectName'].' &gt; '.$t[$k]['TaskName'].' &gt; Starts '.$t[$k]['TaskStartDate'].'&gt; Ends '.$t[$k]['TaskEndDate'].' &gt; Owned by '.$t[$k]['TaskOwner'].'... Click here for more.';
						if ( $userID ? $this->TempUser->HasUserItemAccess('projects', $t[$k]['ProjectID'], CU_ACCESS_READ) : $this->User->HasUserItemAccess('projects', $t[$k]['ProjectID'], CU_ACCESS_READ)) {
							$tmpl['lblMainCalendar'] .= '<span id="'.$id.'" class="draggable"><img src="images/icons/task16x16.gif" height="10" border="0"><a class="calendar" style="bgcolor: '.$t[$k]['Colour'].';" href="index.php?module=projects&action=taskview&projectid='.$t[$k]['ProjectID'].'&taskid='.$t[$k]['TaskID'].'" title="'.$title.'">'.$this->stringShave($t[$k]['TaskName'],18)." <img src=\"images/icons/$icon\" border=\"0\"></a><br></span>";
						}
						else if ($this->DB->QuerySingle(sprintf(SQL_GET_TASKID, ($userID ? $userID : $this->User->ID), $t[$k]['TaskID']))) {
							$tmpl['lblMainCalendar'] .= '<span id="'.$id.'" class="draggable"><img src="images/icons/task16x16.gif" height="10" border="0"><a class="calendar" style="bgcolor: '.$t[$k]['Colour'].';" href="index.php?module=springboard&action=view&projectid='.$t[$k]['ProjectID'].'&id='.$t[$k]['TaskID'].'" title="'.$title.'">'.$this->stringShave($t[$k]['TaskName'],18)." <img src=\"images/icons/$icon\" border=\"0\"></a><br></span>";
						}
					}
				}

/* Commented out as this was combined with the query above for efficiency reasons
				$SQL = sprintf(SQL_GET_TASKS_DUE, gmdate('Y-m-d',$dayBoxEpoch), $this->User->ID, $projects_access_list);
				$tasksDueList = $this->DB->Query($SQL);
				if ($tasksDueList) {
					for ($k = 0; $k < count($tasksDueList); $k++) {
						if ($this->User->HasUserItemAccess('projects', $tasksStartingList[$k]['ProjectID'], CU_ACCESS_READ)) {
							$tmpl['lblMainCalendar'] .= '<img src="images/icons/task16x16.gif" height="10" border="0"><a class="calendar" style="bg-color: '.$tasksDueList[$k]['Colour'].';" href="index.php?module=projects&action=taskview&projectid='.$tasksDueList[$k]['ProjectID'].'&taskid='.$tasksDueList[$k]['TaskID'].'" title="'.$tasksDueList[$k]['ClientName'].' &gt; '.$tasksDueList[$k]['ProjectName'].' &gt; '.$tasksDueList[$k]['TaskName'].' &gt; Starts '.$tasksDueList[$k]['TaskStartDate'].' &gt; Ends '.$tasksDueList[$k]['TaskEndDate'].' &gt; Owned by '.$tasksDueList[$k]['TaskOwner'].'... Click here for more.">'.$this->stringShave($tasksDueList[$k]['TaskName'],18).' <img src="images/icons/icon_cal_f.gif" border="0"></a><br>';
						}
						else if ($this->DB->QuerySingle(sprintf(SQL_GET_TASKID, $this->User->ID, $tasksDueList[$k]['TaskID']))) {
							$tmpl['lblMainCalendar'] .= '<img src="images/icons/task16x16.gif" height="10" border="0"><a class="calendar" style="bg-color: '.$tasksDueList[$k]['Colour'].';" href="index.php?module=springboard&action=view&projectid='.$tasksDueList[$k]['ProjectID'].'&id='.$tasksDueList[$k]['TaskID'].'" title="'.$tasksDueList[$k]['ClientName'].' &gt; '.$tasksDueList[$k]['ProjectName'].' &gt; '.$tasksDueList[$k]['TaskName'].' &gt; Starts '.$tasksDueList[$k]['TaskStartDate'].' &gt; Ends '.$tasksDueList[$k]['TaskEndDate'].' &gt; Owned by '.$tasksDueList[$k]['TaskOwner'].'... Click here for more.">'.$this->stringShave($tasksDueList[$k]['TaskName'],18).' <img src="images/icons/icon_cal_f.gif" border="0"></a><br>';
						}
					}
				}
*/

				$SQL = sprintf(SQL_GET_CALENDAR_NOTES, gmdate('Y-m-d',$dayBoxEpoch));
				$calendarNotes = $this->DB->Query($SQL);
				if ($calendarNotes) {
					for ($k = 0; $k < count($calendarNotes); $k++) {
						$id = 'cal_'.$calendarNotes[$k]['ID'].'_note';
						$tmpl['lblMainCalendar'] .= '<span id=".'.$id.'" class="draggable"><img src="images/icons/icon_cal.gif" border="0">&nbsp;<a class="calendar" style="bg-color: '.$calendarNotes[$k]['Colour'].';" href="index.php?module=calendar&action=view&id='.$calendarNotes[$k]['ID'].'" title="'.$calendarNotes[$k]['Name'].' &gt; '.$calendarNotes[$k]['Description'].'">'.$this->stringShave($calendarNotes[$k]['Name'],18).'</a><br></span>';
					}
				}

				// close day cell
				$tmpl['lblMainCalendar'] .= '</td>' . "\n";

				$dayOfWeekPointer++;
				if ($dayOfWeekPointer == 8) {
					$dayOfWeekPointer = 1;
					$tmpl['lblMainCalendar'] .= "\n" . '</tr>' . "\n";
					if ($i < ($dayboxes - 1)) $tmpl['lblMainCalendar'] .= "\n" . '<tr>' . "\n";
				}
			}
			$tmpl['lblMainCalendar'] .= '</table>' . "\n";

			if ($userID) $tmpl['txtUserID'] = '&userID=' . $userID;
			else $tmpl['txtUserID'] = '';

			$this->setTemplate('month_view_list', $tmpl);
			unset($tmpl);

			$this->setTemplate('month_view_footer', $tmpl);
			unset($tmpl);

		$modAction[0] = '<a href="index.php?module=calendar&amp;action=new">' . MSG_NEW_EVENT . '</a>';
		if ($this->User->HasModuleItemAccess('administration', CU_ACCESS_ALL, CU_ACCESS_READ)) {
			$modAction[1] = '<a href="?module=administration&action=setavailability&id=' . $userID . '">' . MSG_SET_AVAILABILITY_CALENDAR . '</a>';
		}
		$this->setHeader(MSG_CALENDAR, $modInsert);
		$this->setModule($modHeader,$modAction);
		}
		else {
			$this->ThrowError(2001);
		}
		$this->Render();
	}

	function NewCalendarNote() {
		if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE))
		{
			$this->DisplayForm();
		}
		else
		{
			$this->ThrowError(2001);
		}
	}

	function EditCalendarNote() {
		$id = Request::get('id', Request::R_INT);
		if (is_numeric($id) && ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE))) {
			$this->DisplayForm($id);
		}
		else {
			Response::redirect('index.php?module=calendar');
		}
	}

	function DisplayForm($id = 0) {
		$title = MSG_CALENDAR;
		$tmpl['ID'] = $id;
		if ($id == 0) {
			$breadcrumbs = MSG_NEW_EVENT;
			$tmpl['Title'] = MSG_NEW_EVENT;
			$tmpl['Name'] = '';
			$tmpl['Date'] = Format::date(date('Y-m-d'), FALSE, FALSE);
			
			// ben says he wants 12pm - 1pm for defaults. So be it.
			$hour	   = 12;
			$minute	 = 00;
			$end_hour   = 13;
			$end_minute = 00;
			
			$tmpl['Colour'] = '#79D2E7';
			$tmpl['Holiday'] = '';
			$tmpl['Description'] = '';
			$tmpl['FORM_DELETELINK'] = '';
			$tmpl['date_format'] = $this->date_format[Settings::get('DateFormat')];
			$tmpl['DeleteAction'] = '';
		}
		else {
			$breadcrumbs .= MSG_EDIT;
			$tmpl['Title'] = MSG_EDIT;
			$tmpl['DeleteAction'] = '<li>|<a href="index.php?module=calendar&action=delete&id='.$id.'">'.MSG_DELETE.'</a></li>';
			
			$SQL = sprintf(SQL_GET_CALENDAR_NOTE, $id);
			$RS =& new DBRecordset();
			$RS->Open($SQL, $this->DB);
			if (!$RS->EOF()) {

				$date	   = Format::date($RS->Field('Date'), FALSE, FALSE);
				$indate	 = explode(" ", $date);
				$timeArr	= explode(":", $RS->Field('StartTime'));
				$endTimeArr = explode(":", $RS->Field('EndTime'));

				$hour	   = $timeArr[0];
				$minute	 = $timeArr[1];
				$end_hour   = $endTimeArr[0];
				$end_minute = $endTimeArr[1];

				$tmpl['Name']	   = htmlspecialchars($RS->Field('Name'));
				$tmpl['Date']	   = $date;
				$tmpl['Colour']	 = htmlspecialchars($RS->Field('Colour'));
				$tmpl['Holiday']	= ($RS->Field('Holiday')) ? 'checked' : '';
				$tmpl['Description']= htmlspecialchars($RS->Field('Description'));
			}
			$RS->Close();
			unset($RS);
		}

		for ($i = 0;$i < 24;$i++) {
			$selectHour .= sprintf('<option value="%s" %s>%s</option>',$i,($i == $hour) ? 'selected' : '',str_pad($i, 2, '0', STR_PAD_LEFT));
		}

		for ($i = 0;$i < 4; $i++) {
			$selectMinute .= sprintf('<option value="%s" %s>%s</option>',($i*15),((($i*15) == $minute) && (is_numeric($minute))) ? 'selected' : '',str_pad($i*15, 2, '0', STR_PAD_LEFT));
		}

		$tmpl['selectHour'] = $selectHour;
		$tmpl['selectMinute'] = $selectMinute;


		for ($i = 0;$i < 24;$i++) {
			$selectEndHour .= sprintf('<option value="%s" %s>%s</option>',$i,($i == $end_hour) ? 'selected' : '',str_pad($i, 2, '0', STR_PAD_LEFT));
		}

		for ($i = 0;$i < 4; $i++) {
			$selectEndMinute .= sprintf('<option value="%s" %s>%s</option>',($i*15),((($i*15) == $end_minute) && (is_numeric($minute))) ? 'selected' : '',str_pad($i*15, 2, '0', STR_PAD_LEFT));
		}

		$tmpl['selectEndHour'] = $selectEndHour;
		$tmpl['selectEndMinute'] = $selectEndMinute;


		$assigned = null;
		$assigned_sql = sprintf(SQL_EVENT_GET_USERS, $id);
		$assigned_list = $this->DB->Query($assigned_sql);
		if ( is_array($assigned_list) ) {
			$assigned_count = count($assigned_list);
			for ($i = 0; $i < $assigned_count; $i++) {
				$assigned .= sprintf('<option value="%s">%s</option>', $assigned_list[$i]['ID'], $assigned_list[$i]['FirstName'] . ' ' . $assigned_list[$i]['LastName']);
				$minuslist .= $assigned_list[$i]['ID'].",";
			}
		}

		$minuslist = substr($minuslist, 0, -1);
		if (!$minuslist)
			$minuslist = 0;
		$users = '';
		$users_sql = sprintf(SQL_GET_USERS_MINUS, $minuslist);
		$users_list = $this->DB->Query($users_sql);
		if ( is_array($users_list) ) {
			$users_count = count($users_list);
			for ($i = 0; $i < $users_count; $i++) {
				 $users .= sprintf('<option value="%s">%s</option>', $users_list[$i]['ID'], $users_list[$i]['FirstName'].' '.$users_list[$i]['LastName']);
			}
		}

		$tmpl['selectUsers'] = $users;
		$tmpl['selectAssigned'] = $assigned;



		$popuplib   = '<script type="text/javascript" src="assets/js/selectors/selector.lib.js"></script>'.NewLine;
		$cselector  = '<script type="text/javascript" src="assets/js/selectors/colourselector.js"></script>'.NewLine;

		$insert = $popuplib.$cselector;

		$this->setHeader($title, $insert);
		$this->setModule($breadcrumbs, array()); // put delete with the other stuff
		$this->setTemplate('form', $tmpl);
		$this->Render();
	}

	function SaveCalendarNote() {
		if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE))
		{
		$title = MSG_CALENDAR;
		$breadcrumbs = MSG_SAVE;
		$id = Request::post('id');
		$name = $this->DB->Prepare(Request::post('name'));
		$date = $this->DB->Prepare(Request::post('date'));
		$colour = $this->DB->Prepare(Request::post('colour'));
		$holiday	= (Request::post('holiday') > 0) ? 1 : 0;
		$description = $this->DB->Prepare(Request::post('description'));

		// Parse date into yyyy-mm-dd format.
		switch ( Settings::get('DateFormat') )
		{
			case 1:  list($year, $month, $day) = split('-', $date);  break;
			case 2:  list($year, $day, $month) = split('-', $date); break;
			case 3:  list($day, $month, $year) = split('-', $date); break;
			case 4:  list($month, $day, $year) = split('-', $date); break;
			default: list($year, $month, $day) = split('-', $date); break;
		}
		$date = "$year-$month-$day";

		$assign = 0;
		if ( strlen(Request::post('assignedarray')) > 0 )
		{
			$assigned = explode(',', Request::post('assignedarray'));
			$assign = 1;
		}

		$hour =	 Request::post('hour');
		$minute =   Request::post('minute');
		if (!is_numeric($hour) || !is_numeric($minute))
			$time = '00:00:00';
		else
			$time = $hour.':'.$minute;

		$end_hour =	 Request::post('end_hour');
		$end_minute =   Request::post('end_minute');
		if (!is_numeric($end_hour) || !is_numeric($end_minute))
			$end_time = '00:00:00';
		else
			$end_time = $end_hour.':'.$end_minute;

		if ($id == 0) {
			$SQL = sprintf(SQL_CALENDAR_NOTE_CREATE, $name, $date, $time, $end_time, $colour, $description, $holiday);
			$this->DB->Execute($SQL);
			$id = $this->DB->ExecuteScalar(SQL_LAST_INSERT);
		}
		else {
			$SQL = sprintf(SQL_CALENDAR_NOTE_UPDATE, $name, $date, $time, $end_time, $colour, $description, $holiday, $id);
			$this->DB->Execute($SQL);
		}

		if ( $assign )
		{
			$SQL = sprintf(SQL_EVENT_CLEAR_REMOVED, $id, join(",", $assigned));
			$this->DB->Execute($SQL);
			$recs = count($assigned);
			for ($i = 0; $i < $recs; $i++)
			{
				$SQL = sprintf(SQL_EVENT_CHECKASSIGNMENT, $assigned[$i], $id);
				if (!$this->DB->Exists($SQL) )
				{
					// user isn't still in the assigned list. add them, with a notified flag of 0
					$SQL = sprintf(SQL_EVENT_ASSIGN, $id, $assigned[$i]);
					$this->DB->Execute($SQL);
				}
			}
		}
		else
		{
			// clear all the assigned users
			$SQL = sprintf(SQL_EVENT_CLEAR_ASSIGNED, $id);
			$this->DB->Execute($SQL);
		}


		$this->setHeader($title);
		$this->setModule($breadcrumbs);
		$this->setTemplate('saved', $tmpl);
		$this->Render();
		}
		else
		{
			$this->ThrowError(2001);
		}
		Response::redirect('index.php?module=calendar');
	}

	function ViewCalendarNote() 
	{
		if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_READ))
		{
			$id = Request::get('id', Request::R_INT);
			$title = MSG_CALENDAR;

			$breadcrumbs = MSG_VIEW;
			$template = 'message';
			$tmpl['MESSAGE'] = MSG_USER_NOT_FOUND;

			if (is_numeric($id)) {
				$SQL = sprintf(SQL_GET_CALENDAR_NOTE, $id);
				$RS =& new DBRecordset();
				$RS->Open($SQL, $this->DB);
				if (!$RS->EOF()) {
					$template = 'view';
					$tmpl['ID'] = $id;
					$tmpl['Name'] = $RS->Field('Name');
					$tmpl['Date'] = Format::date($RS->Field('Date'));
					$tmpl['StartTime'] = '--';
					$tmpl['EndTime'] = '--';

					if ($RS->Field('StartTime') != '00:00:00') {
						$timeArr	= explode(":", $RS->Field('StartTime'));

						$hour = $timeArr[0];
						$minute = $timeArr[1];

						if ($hour > 12) {
							$hour = $hour - 12;
							$ampm = 'pm';
						}
						else
							$ampm = 'am';

						$tmpl['StartTime'] = ltrim($hour,"0").':'.$minute.' '.$ampm;

						$endTimeArr	= explode(":", $RS->Field('EndTime'));

						$end_hour = $endTimeArr[0];
						$end_minute = $endTimeArr[1];

						if ($end_hour > 12) {
							$end_hour = $end_hour - 12;
							$end_ampm = 'pm';
						}
						else
							$end_ampm = 'am';

						$tmpl['EndTime'] = ltrim($end_hour,"0").':'.$end_minute.' '.$end_ampm;
					}

					$tmpl['Colour'] = $RS->Field('Colour');
					$tmpl['Holiday']	 = ($RS->Field('Holiday')) ? MSG_YES : MSG_NO;
					$tmpl['Description'] = $RS->Field('Description');
					$tmpl['Assigned'] = NULL;
					$tmpl['EditActionUrl'] = "index.php?module=calendar&action=edit&id=" . $id;
					$tmpl['DeleteAction'] = '<li>|<a href="index.php?module=calendar&action=delete&id='.$id.'">'.MSG_DELETE.'</a></li>';

					$users = null;
					$users_sql = sprintf(SQL_EVENT_GET_USERS, $id);
					$users_list = $this->DB->Query($users_sql);
					if ( is_array($users_list) )
					{
						$users_count = count($users_list);
						for ($i = 0; $i < $users_count; $i++)
						{
							$tmpl['Assigned'] .= $users_list[$i]['FirstName'] . ' ' . $users_list[$i]['LastName'].'<br>';
						}
					}
					$RS->Close();
					unset($RS);
				}
				$this->setHeader($title);
				// we don't want any actions in the header for view. they should be down
				// the bottom so they are uniform
				$this->setModule($breadcrumbs,array());
				$this->setTemplate($template, $tmpl);
				$this->Render();
			}
			else
			{
				$this->ThrowError(2001);
			}
		}
	}

	function DeleteCalendarNote() {
		if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE))
		{
			$id = Request::get('id', Request::R_INT);
			$tmpl['MESSAGE'] = MSG_USER_NOT_FOUND;
			$template = 'message';
			$title = MSG_CALENDAR;
			$breadcrumbs = MSG_DELETE;

			if (is_numeric($id)) {
				$this->DB->Execute(sprintf(SQL_DELETE_CALENDAR_NOTE, $id));
				$tmpl['MESSAGE'] = MSG_CALENDAR_DELETED;
				$tmpl['OK'] = MSG_OK;
				$template = 'deleted';
			}

			$this->setHeader($title);
			$this->setModule($breadcrumbs);
			$this->setTemplate($template, $tmpl);

			$this->Render();
		}
		else
		{
			$this->ThrowError(2001);
		}
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

	function CreateTabs($active)
	{
		$tmpl['lblMonthTab'] = $this->AddTab(MSG_MONTH, '', $active);
		$tmpl['lblWeekTab'] = $this->AddTab(MSG_WEEK, 'week', $active);
		if (Settings::get('ResourceManagement') == 1)
			$tmpl['lblResourceTab'] = $this->AddTab(MSG_RESOURCE, 'resource', $active);
		else
			$tmpl['lblResourceTab'] = NULL;
		$this->setTemplate('tabs', $tmpl);
		unset($tmpl);
	}

	function AddTab($name, $action, $active)
	{
		$tab = (strtolower($active) == strtolower($name)) ? 'tab_active' : 'tab_inactive';
		if (strlen($action) > 0) 
			$query = '&action='.$action;
		return $this->getTemplate($tab, array('lblTabName' => $name, 'lblTabQuery' => $query));
	}

	/* return formated date. */
	function getPreviousStartDay($now)
	{
		if (date('l', $now + 86400) != Settings::get('WeekStart'))
		{
			/* Determine the previous week start date from the current date. */
			while (date('l', $now) != Settings::get('WeekStart'))
				$now -= 86400;
		}
		else 
			$now += 86400;
	
		return $now;
	}

	function getFirstDay($now)
	{
		/* Determine the first week start date from the beginning of the year. */
		while (date('l', $now) != Settings::get('WeekStart'))
			  $now += 86400;

		return $now;
	}

	function getWeekNumber($offset)
	{
		/* Determine current day number from $offset. */
		$currentDayNumber = date('z', $offset);
		/* Change date to beginning of the year.  */
		$newOffsetDate = $offset - ($currentDayNumber * 86400);
		/* Now find first week start day of the year. */
		$dayDiff = $currentDayNumber - date('z', $this->getFirstDay($newOffsetDate));
		$weekNumber = intval(($dayDiff + 1)/7);
		/* Check $dayDiff modulo 7 and increment if necessary. */
		if ( $dayDiff%7 >= 0 ) { $weekNumber += 1; }
		return $weekNumber;
	}

	function stringShave($string,$length) {
		if (strlen($string) <= $length) return $string;
		else return substr($string,0,$length).'..';
	}


		function ResourceSet() {
			$userID = Request::get('userID', Request::R_INT);
		//$this->CreateTabs(MSG_RESOURCE_AVAILABILITY);
		$sql = sprintf(SQL_RESOURCE_FROM_USER_ID,$userID);
		$resourceID = $this->DB->QuerySingle($sql);
		if(is_array($resourceID)){
			$userResourceSQL = sprintf(SQL_USER_RESOURCE_FROM_RESOURCEID, $resourceID['ID']);
			$userResource = $this->DB->QuerySingle($userResourceSQL);
		} else {
			$userResource = null;
		}

		if (Request::any('action') == 'resourcesetsave') {
			$availability = Request::post('availability');
			$fromEpoch = mktime(0,0,0,$availability['from']['month'],$availability['from']['day'],$availability['from']['year']);
			$toEpochMax = mktime(0,0,0,$availability['from']['month'],($availability['from']['day'] + 365),$availability['from']['year']);
			$toEpoch = mktime(0,0,0,$availability['to']['month'],$availability['to']['day'],$availability['to']['year']);
			if ($toEpoch > $toEpochMax) $toEpoch = $toEpochMax;

			$dayIDsSQL = sprintf(SQL_GET_ID_EPOCH_WEEKDAY_FROM_DAY, $fromEpoch, $toEpoch);
			$RS =& new DBRecordset();
			$RS->Open($dayIDsSQL, $this->DB);
			$dayIDs = $RS->_Result;
			$RS->Close();
			unset($RS);

			if ($availability['type'] == 'always') {
				for ($i = 0; $i < count($dayIDs); $i++) {
					$dayIDs[$i]['HoursAvailable'] = MAX_DAY_LENGTH;
				}
			}
			else if ($availability['type'] == 'day') {
				for ($i = 0; $i < count($dayIDs); $i++) {
					$dayIDs[$i]['HoursAvailable'] = $availability['hours'];
				}
			}
			else if ($availability['type'] == 'weekday') {
				for ($i = 0; $i < count($dayIDs); $i++) {
					if ($dayIDs[$i]['Weekday'] < 6) $dayIDs[$i]['HoursAvailable'] = $availability['hours'];
					else $dayIDs[$i]['HoursAvailable'] = 0;
				}
			}
			else if ($availability['type'] == 'week') {
				for ($i = 0; $i < count($dayIDs); $i++) {
					for ($j = 0; $j <= 6; $j++) {
						if ($dayIDs[$i]['Weekday'] == ($j + 1)) $dayIDs[$i]['HoursAvailable'] = $availability['weekday'][$j];
					}
				}
			}
			else if ($availability['type'] == 'fortnight') {
				$week = 1;
				// get the id of the starting epoch
				for ($i = 0; $i < count($dayIDs); $i++) {
					if ($dayIDs[$i]['Epoch'] == $availability['starting']) {
						$startingWeekID = $i;
					}
				}
				// go from the starting epoch to the end of time as we know
				for ($i = $startingWeekID; $i < count($dayIDs); $i++) {
					// check if the week has changed
					if ($i != $startingWeekID && date('W',$dayIDs[$i - 1]['Epoch']) != date('W',$dayIDs[$i]['Epoch'])) {
						if ($week == 1) $week = 2;
						else $week = 1;
					}
					for ($j = 0; $j <= 6; $j++) {
						if ($week == 1 && $dayIDs[$i]['Weekday'] == $j + 1) $dayIDs[$i]['HoursAvailable'] = $availability['weekday'][$j];
						else if ($dayIDs[$i]['Weekday'] == $j + 1) $dayIDs[$i]['HoursAvailable'] = $availability['weekday2'][$j];
					}
				}
				$week = 2;
				// go from one day befor starting epoch to the begining of time as we know
				for ($i = ($startingWeekID - 1); $i >= 0; $i--) {
					// check if the week has changed
					if ($i != ($startingWeekID - 1) && date('W',$dayIDs[$i + 1]['Epoch']) != date('W',$dayIDs[$i]['Epoch'])) {
						if ($week == 1) $week = 2;
						else $week = 1;
					}
					for ($j = 0; $j <= 6; $j++) {
						if ($week == 1 && $dayIDs[$i]['Weekday'] == $j + 1) $dayIDs[$i]['HoursAvailable'] = $availability['weekday'][$j];
						else if ($dayIDs[$i]['Weekday'] == $j + 1) $dayIDs[$i]['HoursAvailable'] = $availability['weekday2'][$j];
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
			}
			else {
				// do it
				if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE)) {
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
				}
				else {
					// the user doesn't have access to insert new records.
					$this->ThrowError(2001);
				}
				Response::redirect('index.php?module=calendar&action=resource');
			}
		}

		$modHeader = MSG_SET_AVAILABILITY;
		$modAction[] = '<a href="#" onclick="return SubmitForm()">'.MSG_SAVE.'</a>';
		// interface to set the availability of the resource

		$currentTime = time();
		if (!$currentDayOfWeek = date('w',$currentTime)) $currentDayOfWeek = 7;
		$thisWeekStart = mktime(0,0,0,date('n',$currentTime),(date('j',$currentTime) - $currentDayOfWeek + 1),date('Y',$currentTime));
		$nextWeekStart = mktime(0,0,0,date('n',$currentTime),(date('j',$currentTime) +  (7 - ($currentDayOfWeek - 1))),date('Y',$currentTime));
/*
		if ($userResource != false) {
			$tmpl['lblResource']  = MSG_AVAILABILITY . ' ' . MSG_FOR . ' ' . MSG_RESOURCE . ' ' . $userResource['FirstName'] . ' ' . $userResource['LastName'];
			$tmpl['txtResourceID']  = $resourceID;
			$this->setTemplate('create_resource_header', $tmpl);
			unset($tmpl);


			// $tmpl['lblResourceTitle'] = MSG_RESOURCE;
			// $tmpl['lblResourceName'] = 'availability[resourceID]';
			// $tmpl['lblResourceUserName'] = '';

			// default the from date to today
			if (!$availability['from']['day']) $availability['from']['day'] = date('j',$currentTime);
			if (!$availability['from']['month']) $availability['from']['month'] = date('m',$currentTime);
			if (!$availability['from']['year']) $availability['from']['year'] = date('Y',$currentTime);
			if (!$availability['to']['year']) $availability['to']['year'] = date('Y',$currentTime+(365*86400));

			if ($errorMessage) $tmpl['lblErrorMessage'] = $errorMessage;
			else $tmpl['lblErrorMessage'] = '';

			// from select dates
			$tmpl['lblStartingFromDateSelectDay'] = '<select name="availability[from][day]">';
			// start at 1 for days
			for ($i = 1; $i <= 31; $i++) {
				$tmpl['lblStartingFromDateSelectDay'] .= '<option value="' . $i . '"';
				if ($i == $availability['from']['day']) $tmpl['lblStartingFromDateSelectDay'] .= ' selected';
				$tmpl['lblStartingFromDateSelectDay'] .= '>' . date('jS',mktime(0,0,0,1,$i,2001)) . '</option>';
			}
			$tmpl['lblStartingFromDateSelectDay'] .= '</select>';

			$tmpl['lblStartingFromDateSelectMonth'] .= '<select name="availability[from][month]">';
			for ($i = 1; $i <= 12; $i++) {
				$tmpl['lblStartingFromDateSelectMonth'] .= '<option value="' . $i . '"';
				if ($i == $availability['from']['month']) $tmpl['lblStartingFromDateSelectMonth'] .= ' selected';
				$tmpl['lblStartingFromDateSelectMonth'] .= '>' . date('F',mktime(0,0,0,$i,1,2001)) . '</option>';
			}
			$tmpl['lblStartingFromDateSelectMonth'] .= '</select>';

			$tmpl['lblStartingFromDateSelectYear'] .= '<select name="availability[from][year]">';
			for ($i = 2006; $i <= 2012; $i++) {
				$tmpl['lblStartingFromDateSelectYear'] .= '<option value="' . $i . '"';
				if ($i == $availability['from']['year']) $tmpl['lblStartingFromDateSelectYear'] .= ' selected';
				$tmpl['lblStartingFromDateSelectYear'] .= '>' . $i . '</option>';
			}
			$tmpl['lblStartingFromDateSelectYear'] .= '</select>';

			$tmpl['txtStartingFromDate'] = Format::date(date('Y-m-d'), TRUE, FALSE);//date($this->dateFormat[Settings::get('DateFormat')]);

			// until select dates
			$tmpl['lblUntilDateSelectDay'] = '<select name="availability[to][day]">';
			// start at 1 for days
			for ($i = 1; $i <= 31; $i++) {
				$tmpl['lblUntilDateSelectDay'] .= '<option value="' . $i . '"';
				if ($i == $availability['to']['day']) $tmpl['lblUntilDateSelectDay'] .= ' selected';
				$tmpl['lblUntilDateSelectDay'] .= '>' . date('jS',mktime(0,0,0,1,$i,2001)) . '</option>';
			}
			$tmpl['lblUntilDateSelectDay'] .= '</select>';

			$tmpl['lblUntilDateSelectMonth'] .= '<select name="availability[to][month]">';
			for ($i = 1; $i <= 12; $i++) {
				$tmpl['lblUntilDateSelectMonth'] .= '<option value="' . $i . '"';
				if ($i == $availability['to']['month']) $tmpl['lblUntilDateSelectMonth'] .= ' selected';
				$tmpl['lblUntilDateSelectMonth'] .= '>' . date('F',mktime(0,0,0,$i,1,2001)) . '</option>';
			}
			$tmpl['lblUntilDateSelectMonth'] .= '</select>';

			$tmpl['lblUntilDateSelectYear'] .= '<select name="availability[to][year]">';
			for ($i = 2006; $i <= 2012; $i++) {
				$tmpl['lblUntilDateSelectYear'] .= '<option value="' . $i . '"';
				if ($i == $availability['to']['year']) $tmpl['lblUntilDateSelectYear'] .= ' selected';
				$tmpl['lblUntilDateSelectYear'] .= '>' . $i . '</option>';
			}
			$tmpl['lblUntilDateSelectYear'] .= '</select>';

			//$tmpl['txtUntilDate'] = date($this->dateFormat[Settings::get('DateFormat')], time() + (365 * 86400));
			$d = new DateTime();
			$d->modify("+365 day");
			$tmpl['txtUntilDate'] = Format::date($d->format('Y-m-d'), TRUE, FALSE);

			if ($availability['type'] == 'always') $tmpl['txtAlwaysSelected'] = 'selected';
			else $tmpl['txtAlwaysSelected'] = '';
			if ($availability['type'] == 'day') $tmpl['txtDaySelected'] = 'selected';
			else $tmpl['txtDaySelected'] = '';
			if ($availability['type'] == 'weekday') $tmpl['txtWeekdaySelected'] = 'selected';
			else $tmpl['txtWeekdaySelected'] = '';
			if ($availability['type'] == 'week') $tmpl['txtWeekSelected'] = 'selected';
			else $tmpl['txtWeekSelected'] = '';
			if ($availability['type'] == 'fortnight') $tmpl['txtFortnightSelected'] = 'selected';
			else $tmpl['txtFortnightSelected'] = '';

			$tmpl['lblAlways'] = MSG_ALWAYS;
			$tmpl['lblDay'] = MSG_DAY_BASED;
			$tmpl['lblWeekday'] = MSG_WEEKDAY_BASED;
			$tmpl['lblWeek'] = MSG_WEEK_BASED;
			$tmpl['lblFortnight'] = MSG_FORTNIGHT_BASED;


			$tmpl['lblHoursTitle'] = MSG_HOURS_A_DAY;
			$tmpl['txtHoursName'] = 'availability[hours]';
			$tmpl['lblHoursOptions'] = '';
			$tmpl['lblHoursOptions'] .= '<option value="0"';
			if (0 == $availability['hours']) $tmpl['lblHoursOptions'] .= ' selected';
			$tmpl['lblHoursOptions'] .= '>N/A</option>';

			$tmpl['lblHoursOptions'] .= "\n\t\t\t\t\t\t\t";
			$tmpl['lblHoursOptions'] .= '<option value="1"';
			if (1 == $availability['hours']) $tmpl['lblHoursOptions'] .=	' selected';
			$tmpl['lblHoursOptions'] .= '>1hr</option>';

			for ($i = 2; $i <= MAX_DAY_LENGTH; $i++) {
				if ($i > 2) $tmpl['lblHoursOptions'] .= "\n\t\t\t\t\t\t\t";
				$tmpl['lblHoursOptions'] .= '<option value="' . $i . '"';
				if ($i == $availability['hours']) $tmpl['lblHoursOptions'] .= ' selected';
				$tmpl['lblHoursOptions'] .= '>' . $i . 'hrs</option>';
			}

			$tmpl['lblStartingOptions'] =   '';
			$tmpl['txtStartingName'] = 'availability[starting]';
			$tmpl['lblStartingOptions'] .= '<option value="' . $thisWeekStart . '">' . date('D jS, F',$thisWeekStart) . '</option>';
			$tmpl['lblStartingOptions'] .= '<option value="' . $nextWeekStart . '">' . date('D jS, F',$nextWeekStart) . '</option>';

			$tmpl['lblWeekdayHeading'] = MSG_ON;
			$tmpl['lblWeekday2Heading'] = MSG_AND_EVERY_SECOND_WEEK_ON;
			$tmpl['txtWeekdayName'] = 'availability[weekday][]';
			$tmpl['txtWeekdayName2'] = 'availability[weekday2][]';
			for ($i = 1; $i <= 7; $i++) {
				$tmpl['lblWeekdayOptions' . $i] =   '';
				$tmpl['lblWeekdayTitle' . $i] = date('l',mktime(0,0,0,1,$i,2001));
				for ($j = 0; $j <= MAX_DAY_LENGTH; $j++) {
					if ($j > 0) $tmpl['lblWeekdayOptions' . $i] .= "\n\t\t\t\t\t\t\t";
					$tmpl['lblWeekdayOptions' . $i] .= '<option value="' . $j . '"';
					if ($j == $availability['weekday'][$i - 1]) $tmpl['lblWeekdayOptions' . $i] .=  ' selected';

					if ($j == 0) $tmpl['lblWeekdayOptions' . $i] .= '>NA';
					else if ($j == 1) $tmpl['lblWeekdayOptions' . $i] .= '>' . $j . 'hr';
					else	$tmpl['lblWeekdayOptions' . $i] .= '>' . $j . 'hrs';

					$tmpl['lblWeekdayOptions' . $i] .= '</option>';
				}
			}

			$tmpl['date_format'] = $this->date_format[Settings::get('DateFormat')];
			$this->setTemplate('create_resource_main', $tmpl);
			$this->setTemplate('create_resource_footer');
		}
		else {
			$this->setTemplate('eof', array('txtMessage' => MSG_NO_RESOURCES_AVAILABLE));
		}
*/
		
		$this->setModule($modHeader, $modAction);
		$this->RenderOnlyContent();
//		$this->Render();
			}
}
 
