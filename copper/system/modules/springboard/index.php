<?php
// $Id$
class mod_springboard extends Module
{
	var $date_format = array('1' => 'yyyy-mm-dd', '2' => 'yyyy-dd-mm', '3' => 'dd-mm-yyyy', '4' => 'mm-dd-yyyy');

	function mod_springboard()
	{
		$this->ModuleName   = 'springboard';
		$this->RequireLogin = 1;
		$this->Public	   = 1;
		parent::Module();
	}

	function main()
	{
		switch (Request::any('action'))
		{
			case 'comment':  $this->TaskComment();  break;
			case 'comments': $this->TaskComments(); break;
			case 'owed':	 $this->Todo();		 break;
			case 'list':	 $this->TaskList();	 break;
			case 'activity': $this->Activity();	 break;
			case 'upload':   $this->Upload();	   break;
			case 'stopwatchstart':  $this->StopwatchStart();  break;
			case 'stopwatchping':   $this->StopwatchPing();   break;
			case 'stopwatchcancel': $this->StopwatchCancel(); break;
			case 'stopwatchpause':  $this->StopwatchPause();  break;
			case 'stopwatchsave':  $this->StopwatchSave();  break;
			default:		 $this->Todo();
		}
	}

	// wtf. apparently file upload from a task view goes here too. I just don't understand sometimes.
	function Upload() {
		// What permissions checks do we need?

		set_time_limit(600);
		ignore_user_abort(1);

		$taskID = Request::post('taskid');
		$projectID = Request::post('projectid');
		$file = Request::files('document', Request::R_ARRAY);
		$caller = Request::post('caller');

		foreach ($file["error"] as $key => $error)
		{
			if ($error == UPLOAD_ERR_OK)
			{
				$file_tmp  = $file['tmp_name'][$key];
				$file_name = $file['name'][$key];
				$file_type = $file['type'][$key];
				$file_size = $file['size'][$key];
				if ($file_size > 0) 
				{
					$project_dir = str_pad($projectID, 7, '0', STR_PAD_LEFT);
					$task_dir = str_pad($taskID, 7, '0', STR_PAD_LEFT);

					$trailer = substr(SYS_FILEPATH, strlen(SYS_FILEPATH) - 1, 1);
					$filepath = ( ($trailer != '\\') && ($trailer != '/') ) ? SYS_FILEPATH . '/' : SYS_FILEPATH;

					$project_path = $filepath . $project_dir;
					$task_path = $filepath . $project_dir . '/'. $task_dir;
					@mkdir($project_path, 0777);
					@mkdir($task_path, 0777);

					$new_file_name = $this->MD5($projectid.$taskid.$file_name.microtime(true));
					$new_file_path = $task_path . '/' . $new_file_name;

					// File versioning has introduced a different file naming convention.
					// Instead of <project id>/<task id>/<md5 hash> we will have  
					// <project id>/<task id>/md5 hash/<md5 hash>_<version>
					// ie. the hash becomes the folder, and version number is a suffix of the on-disk filename.
					if ( defined( 'FILE_VERSIONING_ENABLED' ) && FILE_VERSIONING_ENABLED == '1' )
					{
						if ( !is_dir( $new_file_path ) )
							mkdir( $new_file_path, 0777 );
						$versionSuffix = 1; // We are always uploading a new file in Springboard, so it gets version=1. 
						$new_file_path .= '/' . $new_file_name . '_' . $versionSuffix;
					}

					if ( move_uploaded_file($file_tmp, $new_file_path) ) 
					{
						$SQL = sprintf(SQL_FILE_CREATE, $file_name, '', $file_type, $this->User->ID, date('Y-m-d H:i:s'), $file_size, 1, $new_file_name, 0, '0', $projectID, $taskID);
						$this->DB->Execute($SQL);

						$fileID = $this->DB->ExecuteScalar( SQL_LAST_INSERT );
						$SQL = sprintf(SQL_FILE_LOG, $fileID, $this->User->ID, date('Y-m-d H:i:s'), MSG_UPLOADED, $version);
						$this->DB->Execute($SQL);
					}
					else 
					{
						$no_redirect = 1;
						$message = "Post Max Size: ".ini_get('post_max_size');
						$message .= ' - Upload Max Filesize: '.ini_get('upload_max_filesize');
						$this->ThrowError(4000, base64_encode($message));
					}
				}
			}
		}

		$module = ($caller == 'springboard') ? 'springboard' : 'projects';
		Response::redirect("index.php?module=$module&action=taskview&projectid=$projectID&taskid=$taskID");
	}


	function StopwatchStart(){
		$task = new Task(Request::get('taskid', Request::R_INT));
		if ($task->exists)
		{
			// copper says we should remove old timers here. meh. 
			$tls = new TimerLogs(array('where' => array('UserID' => CopperUser::current()->ID)));
			foreach($tls as $t)
			{
				$t->delete();
			}

			$tl = new TimerLog(null);
			$tl->Updated = DB::now();
			$tl->UserID = CopperUser::current()->ID;
			$tl->TaskID = $task->ID;
			$tl->Elapsed = Request::get('time');
			$tl->Paused = 0;
			$tl->commit();

			$ar = new AjaxResponse();
			$ar->task_name = $task->Name;
			$ar->task_permalink = $task->permalink;
			$ar->task_pc = $task->PercentComplete;
			$ar->out();
		} else
		{
			$ar = new AjaxResponse(FALSE, 'Task not found');
			$ar->out();
			
		}


		$taskID = Request::get('taskid', Request::R_INT);
		$time = Request::get('time');
		//clear out old timers.
		$sql = sprintf(CU_SQL_REMOVE_TIMER, $this->User->ID);
		$this->DB->Execute($sql);
		//create a new timer.
		$sql = sprintf(SQL_INSERT_TIMER, date('Y-m-d H:i:s'), $this->User->ID, $taskID, $time, 0);
		$this->DB->Execute($sql);
		
		
		
	}

	function StopwatchPing(){
		$taskID = Request::get('taskid', Request::R_INT);
		$time = Request::get('time');
		$timer = $this->DB->QuerySingle(sprintf(SQL_GET_TIMER, $this->User->ID, $taskID));
		//only update an existing timer..don't create a new one.
		if (is_array($timer)){
			$sql = sprintf(SQL_UPDATE_TIMER, $time, $this->User->ID, $taskID);
			$this->DB->Execute($sql);
			$ar = new AjaxResponse();
			$ar->exists = true;
			$ar->out();
		} else {
			$ar = new AjaxResponse();
			$ar->exists = false;
			$ar->out();
		}

	}

	function StopwatchPause(){ //pause / resume
		$taskID = Request::get('taskid', Request::R_INT);
		$paused = Request::get('paused') == 'true' ? 1 : 0;
		
		$timer = $this->DB->QuerySingle(sprintf(SQL_GET_TIMER, $this->User->ID, $taskID));
		//only update an existing timer.
		if (is_array($timer)){
			//if pausing true..update to the latest time.. else unpausing doesn't effect the time.
			if($paused == 1){
				$time = Request::get('time');
				$sql = sprintf(SQL_UPDATE_TIMER, date('Y-m-d H:i:s'), $time, $this->User->ID, $taskID);
				$this->DB->Execute($sql);
			}
			$sql = sprintf(SQL_TOGGLE_PAUSE_TIMER, date('Y-m-d H:i:s'), $paused, $this->User->ID, $taskID);
			$this->DB->Execute($sql);
		}
	}

	function StopwatchCancel(){
		$taskID = Request::get('taskid', Request::R_INT);
		$sql = sprintf(CU_SQL_REMOVE_TIMER, $this->User->ID);
		$this->DB->Execute($sql);
	}
	
	function StopwatchSave(){

		$tc = new TaskComment();
		$tc->UserID = CopperUser::current()->ID;
		$tc->TaskID = Request::get('taskid', Request::R_INT);
		$tc->Body = Request::get('comment');
		$tc->Date = DB::now();
		$tc->HoursWorked = Request::get('hours');
		$tc->commit();
		
		$tc->task->PercentComplete = Request::get('complete');
		$tc->task->Status = (Request::get('complete')=='100') ? 1 : 0;
		$tc->task->commit();
		
		$timer = new TimerLog(array('UserID' => CopperUser::current()->ID, 'TaskID' => Request::get('taskid', Request::R_INT)));
		$timer->delete();

		$ar = new AjaxResponse();
		$ar->out();
	}


	function Activity() {
		// start of code to select other user information
		$query_string = Request::$GET;
		$global_string = $this->buildQuery($query_string);
		//Display User select tool if user has admin permissions
		$globalUser = '';
		if ($this->User->HasModuleItemAccess('administration', CU_ACCESS_ALL, CU_ACCESS_READ)) {
			//  $actualUserID = $this->User->ID;
			$userID = Request::get('userID', Request::R_INT);
			// Create Temp user for creating correct permissions
			if ($userID) {
			$this->Session->Set('springboardID', $userID);
			$this->TempUser		 =& new User();
			$this->TempUser->Initialise($userID, $this->DB);
			} else if ($this->Session->Get('springboardID')) {
			$userID = $this->Session->Get('springboardID');
			$this->TempUser		 =& new User();
			$this->TempUser->Initialise($userID, $this->DB);
			} else {
			$this->TempUser->ID = $this->User->ID;
			$userID = $this->User->ID;
			$this->Session->Set('springboardID', $userID);
			}
			$globalUser = '<label>' . MSG_USER_TO_SHOW . '</label><select id="activityUserToShowFilter">';

			$SQL = sprintf(SQL_GET_USER_LIST);
			$userList = $this->DB->Query($SQL);
			if ($userList) {
			for ($i = 0; $i < count($userList); $i++) {
				$globalUser .= '<option value="' . $userList[$i]['ID'] . '"'
				. ($this->TempUser->ID == $userList[$i]['ID'] ? ' selected' : '') . '>'
				. $userList[$i]['FirstName'].' '.$userList[$i]['LastName'].'</option>';
				
			}
			}

			$globalUser .= '</select>';
			$tmplDash['globalUser'] = $globalUser;

						$tmplDash['displayState'] = ($userID == $this->User->ID) ? "showDashOnLoad" : "dontShowDashOnLoad";

			$tmplDash['showfilter'] = '';

			$URI = split ("index\.php",Request::server(SCRIPT_NAME_VAR));
			// Create validity key
			$this->KeyUser =& new User();
			$this->KeyUser->Initialise($userID, $this->DB);
			$key = substr(md5($this->KeyUser->Fullname.$this->KeyUser->PasswordHash), 2, 8);
			$modAction[] = '<a href="webcal://' . Request::server(SERVER_NAME_VAR) . $URI[0] . 'system/ical_springboard.php?show=' . $query_string['show'] 
				. '&completed=' . $query_string['completed'] 
				. '&action=' . $query_string['action'] 
				. '&key=' . $key 
				. '&userid=' . $userID . '">' . MSG_SYNC_TO_ICAL . '</a>';

			$modAction[] = '<a id="dash-toggler" href="#" onclick="toggleDash(); return false;">'.MSG_SHOW_DASH.'</a>';
						$tmplDash['period'] = '';
			$this->setDash($this->getTemplate("dashBlock", $tmplDash));
		} else {
			$userID = $this->User->ID;
			$this->Session->Set('springboardID', $userID);
		}
		$modAction[] = '<a href="index.php?module=springboard&completed=1">'.MSG_VIEW_COMPLETED.'</a>';
		//end of other user select code.

		$this->CreateTabs('activity');

		// Tell MySQL if we want the week to start on Sunday or Monday.
		$weekMode = (CU_WEEK_START == 'Sunday') ? 0 : 1;
		$day = $this->DB->QuerySingle(sprintf(SQL_GET_ACTIVITY_DAY, $userID));
		$week = $this->DB->QuerySingle(sprintf(SQL_GET_ACTIVITY_WEEK, $weekMode, $userID));
		$month = $this->DB->QuerySingle(sprintf(SQL_GET_ACTIVITY_MONTH, $userID));

		if($userID == $this->User->ID){
			$tmpl['txtUsername'] = $this->User->Name();
		} else {
			$tmpl['txtUsername'] = $this->TempUser->Name();
		}
		$tmpl['txtDayComments'] = $day['Comments'];
		$tmpl['txtDayHours'] = $day['Hours'];
		$tmpl['txtWeekComments'] = $week['Comments'];
		$tmpl['txtWeekHours'] = $week['Hours'];
		$tmpl['txtMonthComments'] = $month['Comments'];
		$tmpl['txtMonthHours'] = $month['Hours'];

		$tmpl['issues'] = '';
		$rows = $this->DB->Query(sprintf(SQL_GET_OPEN_ISSUES, $userID));
		if (is_array($rows))
		{
			foreach ($rows as $r)
			{
				$url = "index.php?module=springboard&action=taskview&projectid={$r['ProjectID']}&taskid={$r['TaskID']}";
				$itemTmpl['name'] = "<a href=\"$url\">{$r['TaskName']}</a>";
				$url = "index.php?module=projects&action=taskview&projectid={$r['ProjectID']}&taskid={$r['TaskID']}";
				$itemTmpl['value'] = "<a href=\"$url\">{$r['ProjectName']}</a>";
				$tmpl['issues'] .= $this->getTemplate('activity_item', $itemTmpl);
			}
		}

		$tmpl['commentary'] = '';
		$rows = $this->DB->Query(sprintf(SQL_GET_RECENT_COMMENTARY, $userID));
		if (is_array($rows))
		{
			foreach ($rows as $r)
			{
				$url = "index.php?module=springboard&action=taskview&projectid={$r['ProjectID']}&taskid={$r['TaskID']}";
				$itemTmpl['name'] = "<a href=\"$url\">{$r['TaskName']}</a>";
				$url = "index.php?module=projects&action=taskview&projectid={$r['ProjectID']}&taskid={$r['TaskID']}";
				$itemTmpl['value'] = "<a href=\"$url\">{$r['ProjectName']}</a>";
				$tmpl['commentary'] .= $this->getTemplate('activity_item', $itemTmpl);
			}
		}

		$tmpl['pages'] = '';
		$rows = $this->DB->Query(sprintf(SQL_GET_PAGES_VISITED, $userID));
		if (is_array($rows))
		{
			foreach ($rows as $r)
			{
				switch ($r['Context'])
				{
					case 'client': $context = MSG_CLIENT; $url = "index.php?module=clients&action=view&id={$r['ContextID']}"; break;
					case 'project': $context = MSG_PROJECT; $url = "index.php?module=projects&action=view&projectid={$r['ContextID']}"; break;
					case 'task': $context = MSG_TASK; $url = "index.php?module=projects&action=taskview&projectid={$r['Comment']}&taskid={$r['ContextID']}"; break;
					case 'file': $context = MSG_FILE; $url = "index.php?module=files&action=view&fileid={$r['ContextID']}"; break;
					case 'contact': $context = MSG_CONTACT; $url = "index.php?module=contacts&action=view&id={$r['ContextID']}"; break;
					case 'projectreport': $context = MSG_REPORT; $url = "index.php?module=reports&action=analysis&report={$r['ContextID']}"; break;
					case 'workreport': $context = MSG_REPORT; $url = "index.php?module=reports&action=timesheets&report={$r['ContextID']}"; break;
					case 'login': $context = MSG_LOGIN; $url = '';
					default: $context = ''; $url = '';
				}

				if ($context != '')
				{
					if ($url == '')
						$itemTmpl['name'] = "$context: {$r['Detail']}";
					else
						$itemTmpl['name'] = "<a href=\"$url\">$context: {$r['Detail']}</a>";
					$itemTmpl['value'] = $r['Timestamp'];
					$tmpl['pages'] .= $this->getTemplate('activity_item', $itemTmpl);
				}
			}
		}

		$tmpl['groupactivity'] = '';
		$rows = $this->DB->Query(sprintf(SQL_GET_TASK_TIMES_FOR_GROUP_MEMBERS, $userID));
		if (is_array($rows))
		{
			foreach ($rows as $r)
			{
				$url = "index.php?module=springboard&action=taskview&projectid={$r['ProjectID']}&taskid={$r['TaskID']}";
				$itemTmpl['task'] = "<a href=\"$url\">{$r['TaskName']}</a>";
				$itemTmpl['user'] = $r['Name'];
				$itemTmpl['elapsed'] = substr($r['Elapsed'], 0, -3);
				$tmpl['groupactivity'] .= $this->getTemplate('group_activity_item', $itemTmpl);
			}
		}

		$this->setTemplate('activity', $tmpl);

		$modHeader = MSG_ACTIVITY;
		$this->setHeader($modHeader, $insert);
		$this->SetModule($modHeader, $modAction);
		$this->Render();
	}

	function Todo() {

		// start of code to select other user information
		$query_string = Request::$GET;
		$global_string = $this->buildQuery($query_string);

		// we only want to do this is we have a valid, single user. 
		$show_ical = FALSE;

		//Display User select tool if user has admin permissions
		$globalUser = '';
		if ($this->User->HasModuleItemAccess('administration', CU_ACCESS_ALL, CU_ACCESS_READ)) {

			//  $actualUserID = $this->User->ID;
			// try to get a imitation user form the request string.
			$userID = Request::get('userID', Request::R_INT);

			// userID can be 'all'
			if (($userID == null) && (Request::get('userID') == 'all'))
			{
				$userID = 'all';
			}

			// else, check if we have a imitation user in the session object.
			if (!$userID) {
			  $userID = $this->Session->Get('springboardID');
			}
			
			// Create Temp user for creating correct permissions
			if ($userID) {
				$this->Session->Set('springboardID', $userID);

				if ($userID == 'all')
				{
				  $this->TempUser =& new User();
				  $this->TempUser->ID = null;
				} else {
					$show_ical = TRUE;
				  $this->TempUser =& new User();
				  $this->TempUser->Initialise($userID, $this->DB);
				}
			} else {
				$this->TempUser->ID = $this->User->ID;
				$userID = $this->User->ID;
				$this->Session->Set('springboardID', $userID);
			}
			
			$globalUser = '<label>' . MSG_USER_TO_SHOW . '</label>'
						.'<select name="UserID" class="TaskUpdate_dd" id="userToShowFilter">';

			$SQL = sprintf(SQL_GET_USER_LIST);
			$userList = $this->DB->Query($SQL);
			if ($userList) {
				// first add in a "All users option"
				$globalUser .= '<option value="all"'
				. (($this->TempUser->ID == null) ? ' selected' : '') . '>'
				. MSG_SHOW_ALL . '</option>';
			  
				for ($i = 0; $i < count($userList); $i++) {
					$globalUser .= '<option value="' . $userList[$i]['ID'] . '"'
					. ($this->TempUser->ID == $userList[$i]['ID'] ? ' selected' : '') . '>'
					. $userList[$i]['FirstName'].' '.$userList[$i]['LastName'].'</option>';
				}
			}

			$globalUser .= '</select>';
			$tmplDash['globalUser'] = $globalUser;

		} else {
			// don't allow non-admins to edit the user they view for.
			$tmplDash['globalUser'] = '';
			$userID = $this->User->ID;
			$show_ical = TRUE;
			$this->Session->Set('springboardID', $userID);
		}

		$filter_array = array('mytasks','owed','all');
		$filter_display = array(MSG_MINE,MSG_OWED,MSG_ALL);
		for ($i = 0, $filteroptions = null, $filtercount = count($filter_array); $i < $filtercount; $i++) 
		{
			$filteroptions .= sprintf('<option value="%s" %s>%s</option>', 
																$filter_array[$i],
																($_GET['action'] == $filter_array[$i]) ? ' SELECTED' : '',
																$filter_display[$i]
															);
		}

		$tmplDash['showfilter'] = '<label>'.MSG_SHOW_THESE_TASKS.'</label>'
			.'<select id="showTheseTasksFilter">'.$filteroptions.'</select>';

		$range_array = array('all' => '{MSG_ALL}', 
												'lastweek' => '{MSG_LAST_WEEK}', 
												'thisweek' => '{MSG_THIS_WEEK}',
												'nextweek' => '{MSG_NEXT_WEEK}',
												'today' => '{MSG_TODAY}',
												'yesterday' => '{MSG_YESTERDAY}',
												'lastmonth' => '{MSG_LAST_MONTH}',
												'thismonth' => '{MSG_THIS_MONTH}', 
												'nextmonth' => '{MSG_NEXT_MONTH}'
												);

		foreach($range_array as $key => $value) 
		{
			$rangeoptions .= sprintf('<option value="%s" %s>%s</option>',
															$key, 
															($key == $query_string['show']) ? 'selected' : '',
															$value
														);
		}

		$tmplDash['period'] = '<label>'.MSG_PERIOD_TO_SHOW.'</label>
							   <select id="periodToShowFilter">'.$rangeoptions.'</select>';

		$URI = split ("index\.php",Request::server(SCRIPT_NAME_VAR));
		// Create validity key
		
		if ($show_ical == TRUE)
		{
		  $this->KeyUser =& new User();
		  $this->KeyUser->Initialise($userID, $this->DB);
		  $key = substr(md5($this->KeyUser->Fullname . $this->KeyUser->PasswordHash), 2, 8);
		  $modAction[] = '<a href="webcal://' . Request::server(SERVER_NAME_VAR) . $URI[0] . 'system/ical_springboard.php?show=' . $query_string['show'] 
			. '&completed=' . $query_string['completed'] 
			. '&action=' . $query_string['action'] 
			. '&key=' . $key 
			. '&userid=' . $userID . '">'.MSG_SYNC_TO_ICAL.'</a>';
		}

		$modAction[] = '<a id="dash-toggler" href="#" onclick="toggleDash(); return false;">'.MSG_SHOW_DASH.'</a>';
		$this->setDash($this->getTemplate("dashBlock", $tmplDash));

		// pick whether to show completed or active
		$completed = (isset($query_string['completed']) && ($query_string['completed'] == 1));
		if ($completed)
		{
			$modAction[] = '<a href="index.php?module=springboard">'.MSG_VIEW_ACTIVE.'</a>';
		} else {
			$modAction[] = '<a href="index.php?module=springboard&completed=1">'.MSG_VIEW_COMPLETED.'</a>';
		}

		$this->CreateTabs('todo');
		$tmpl['tasklist'] = $this->TaskList($query_string['action'], $userID);

		// Emulate the task view screen.
		if (Request::get('taskid', Request::R_INT)) {
			Response::addToJavascript('auto_open_task', TRUE);
			Response::addToJavascript('item_ids', array(
				'project_id' => Request::get('projectid', Request::R_INT),
				'task_id' => Request::get('taskid', Request::R_INT),
				'comment_id' => Request::get('commentid', Request::R_INT),
			));
		}

		// this is here to keep template happy.
		// remove when we are sure that nobody else calls the todo template.
		$tmpl['script'] = ''; 

		$this->setTemplate('todo', $tmpl);

		$this->SetModule(MSG_TODO, $modAction);
		$this->Render();
	}

  function buildQuery($array) {
		foreach($array as $key => $value) {
			$string .= $key.'='.$value.'&';
		}
		$string = substr($string,0,-1);
		return $string;
  }

	function Springboard() {

		$modHeader = MSG_SPRINGBOARD;
/*		$query_string = Request::$GET;

		$global_string = $this->buildQuery($query_string);
		$completed = $query_string['completed'];
				if ($query_string['completed'] > 0) {
			$query_string['completed'] = 0;
			$msg = MSG_OUTSTANDING;
		}
		else {
			$query_string['completed'] = 1;
			$msg = MSG_COMPLETED;
		}
		$new_string = $this->buildQuery($query_string);

		$query_string['completed'] = ($query_string['completed'])? 0: 1;
*/
		//Display User select tool if user has admin permissions
		$globalUser = '';

		if ($this->User->HasModuleItemAccess('administration', CU_ACCESS_ALL, CU_ACCESS_READ)) {
			//  $actualUserID = $this->User->ID;
			$userID = Request::get('userID', Request::R_INT);
			// Create Temp user for creating correct permissions

			 if ($userID) {
				$this->Session->Set('springboardID', $userID);

				$this->TempUser		 =& new User();
				$this->TempUser->Initialise($userID, $this->DB);
			}
			else if ($this->Session->Get('springboardID')) {
				$userID = $this->Session->Get('springboardID');
				$this->TempUser		 =& new User();
				$this->TempUser->Initialise($userID, $this->DB);
			}
			else {
				$this->TempUser->ID = $this->User->ID;
				$userID = $this->User->ID;
				$this->Session->Set('springboardID', $userID);
			}
			$globalUser = '<tr align="left" valign="top">'
									.'<td width="50%" valign="middle" nowrap>' . MSG_USER_TO_SHOW . '</td>'
									.'<td width="50%"><select name="UserID" class="TaskUpdate_dd" id="userToShowFilter">';

			$SQL = sprintf(SQL_GET_USER_LIST);
			$userList = $this->DB->Query($SQL);
			if ($userList) {
				for ($i = 0; $i < count($userList); $i++) {
					$globalUser .= '<option value="' . $userList[$i]['ID'] . '"'
						. ($this->TempUser->ID == $userList[$i]['ID'] ? ' selected' : '') . '>'
						. $userList[$i]['FirstName'].' '.$userList[$i]['LastName'].'</option>';
				}
			}

			$globalUser .= '</select></td>'
								 .'</tr>';
		}
		else {
			$userID = $this->User->ID;
			$this->Session->Set('springboardID', $userID);
		}
		$tmpl['tasklist'] = $this->TaskList($query_string['action'], $userID);
		$URI = split ("index\.php",Request::server(SCRIPT_NAME_VAR));
		//Create validitiy key
		$this->KeyUser		 =& new User();
		$this->KeyUser->Initialise($userID, $this->DB);
		$key = substr(md5($this->KeyUser->Fullname.$this->KeyUser->PasswordHash),2,8);
		$modAction[] = '<a href="webcal://'.Request::server(SERVER_NAME_VAR).$URI[0].'system/ical_springboard.php?show='.$query_string['show'].'&completed='.$completed.'&action='.$query_string['action'].'&key='.$key.'&userid='.$userID.'">'.MSG_SYNC_TO_ICAL.'</a>';
		$modAction[] = '<a href="index.php?'.$new_string.'">' . MSG_VIEW . ' ' . $msg . '</a>';

		$filter_array = array('mytasks','owed','all');
		$filter_display = array(MSG_MINE,MSG_OWED,MSG_ALL);

		$hoursWorkedArray = array();
		for ($i = 0; $i <= 10; $i+=0.25)
		{
			$hoursWorkedArray[] = sprintf( '<option value="%.2f">%.2f</option>', $i, $i );
		}

		 for ($i = 0, $filteroptions = null, $filtercount = count($filter_array); $i < $filtercount; $i++)
		{
			$filteroptions .= sprintf('<option value="%s" %s>%s</option>', $filter_array[$i], ($_GET['action'] == $filter_array[$i]) ? ' SELECTED' : '', $filter_display[$i]);
		}


		$tmpl['showfilter'] = $filteroptions;
		$tmpl['location'] = $this->buildQuery($query_string);
		$tmpl['globalUser'] = $globalUser;

		$range_array = array('all' => '{MSG_ALL}', 'lastweek' => '{MSG_LAST_WEEK}', 'thisweek' => '{MSG_THIS_WEEK}','nextweek' => '{MSG_NEXT_WEEK}','today' => '{MSG_TODAY}','yesterday' => '{MSG_YESTERDAY}','lastmonth' => '{MSG_LAST_MONTH}','thismonth' => '{MSG_THIS_MONTH}', 'nextmonth' => '{MSG_NEXT_MONTH}');

		foreach($range_array as $key => $value) {
			$tmpl['filter'] .= sprintf('<option value="%s" %s>%s</option>',$key, ($key == $query_string['show']) ? 'selected' : '', $value);
		}

		// If a task comment is saved, the redirect back here adds a taskid parameter to the query string.
		// Open the task by using the window.onload event, forcing the AJAX call.
		$taskid = Request::get( 'taskid' );
		$script = '<script type="text/javascript">window.onload=function() { toggleTask(\''.$taskid.'\'); }</script>';
		$tmpl['opentask'] = ( $taskid ) ? $script : '';

		$this->setTemplate('springboard', $tmpl);
		$this->SetModule($modHeader,$modAction);
		$this->Render();
  }

	function TaskList($action, $userID) {

		$query_string = Request::$GET;

		if (!isset($query_string['completed'])) {
		  $query_string['completed'] = 0;
		}

		if ($quickedit == '1')
			$quickedit_id = $query_string['id'];

		//ordering
		switch ($query_string['order'])
		{
			case 'client'   : $query_string['order'] = 'client';   $query_string['orderby'] = 'ClientName'; break;
			case 'project'  : $query_string['order'] = 'project';  $query_string['orderby'] = 'ProjectName'; break;
			case 'urgency'  : $query_string['order'] = 'urgency';  $query_string['orderby'] = 'Priority'; break;
			case 'progress' : $query_string['order'] = 'progress'; $query_string['orderby'] = 'PercentComplete'; break;
			case 'deadline' : $query_string['order'] = 'deadline'; $query_string['orderby'] = 'EndDate'; break;
			case 'start'	: $query_string['order'] = 'start';	$query_string['orderby'] = 'StartDate'; break;
			case 'duration' : $query_string['order'] = 'duration'; $query_string['orderby'] = 'Duration'; break;
			case 'task'	 : $query_string['order'] = 'task';	 $query_string['orderby'] = 'Name'; break;
			case 'active'   : $query_string['order'] = 'active';   $query_string['orderby'] = 'projects_active'; break;
			case 'archived' : $query_string['order'] = 'archived'; $query_string['orderby'] = 'projects_inactive'; break;
			default		 : $query_string['order'] = 'deadline'; $query_string['orderby'] = 'EndDate';
		}

		switch ($query_string['direction'])
		{
			case 'down': $query_string['direction'] = 'down'; $query_string['orderdir'] = 'DESC'; break;
			default	: $query_string['direction'] = 'up';   $query_string['orderdir'] = 'ASC';
		}
		// end ordering

		// paging code
		$limit = Settings::get('RecordsPerPage');

		$today = date('Y-m-d');
		$newDate = getdate(time());
		$weekDay = $newDate['wday'];
		$month = $newDate['mon'];
		$range = $query_string['show'];

		$ids = 0;
		if (Settings::get('ShowDependentTasks') < 1) {
			$tasks_with_dependencies = $this->DB->Query(SQL_GET_TASKS_WITH_DEPENDENCIES);
			if ($tasks_with_dependencies) {
				$ids = NULL;
				foreach($tasks_with_dependencies as $key => $value) {
					$ids .= $value['TaskID'].',';
				}
				$ids = substr($ids,0,-1);
			}
		}

		if ($userID == 'all')
		{
		  // use a different set.
		  switch($action) {
			  case "all": {
				  $sql = SQL_TASKS_ALL_ALLUSERS;
				  $SQL = sprintf(SQL_TASKS_ALL_ALLUSERS, $userID, $query_string['completed'], ' AND t.ID NOT IN ('.$ids.') ',$query_string['orderby'], $query_string['orderdir']);
				  break;
			  }
			  case "owed": {
				  $sql = SQL_TASKS_OWED_ALLUSERS;
				  $SQL = sprintf(SQL_TASKS_OWED_ALLUSERS, $userID, $query_string['completed'], ' AND t.ID NOT IN ('.$ids.')',$query_string['orderby'], $query_string['orderdir']);
				  break;
			  }
			  default:  {
				  $sql = SQL_TASKS_LIST_ALLUSERS;
				  $SQL = sprintf(SQL_TASKS_LIST_ALLUSERS, $userID, $query_string['completed'], ' AND t.ID NOT IN ('.$ids.')',$query_string['orderby'], $query_string['orderdir']);
				  $action = 'mytasks';
			  }
		  }
		  
		} else {

		  switch($action) {
			  case "all": {
				  $sql = SQL_TASKS_ALL;
				  $SQL = sprintf(SQL_TASKS_ALL, $userID, $query_string['completed'], ' AND t.ID NOT IN ('.$ids.') ',$query_string['orderby'], $query_string['orderdir']);
				  break;
			  }
			  case "owed": {
				  $sql = SQL_TASKS_OWED;
				  $SQL = sprintf(SQL_TASKS_OWED, $userID, $query_string['completed'], ' AND t.ID NOT IN ('.$ids.')',$query_string['orderby'], $query_string['orderdir']);
				  break;
			  }
			  default:  {
				  $sql = SQL_TASKS_LIST;
				  $SQL = sprintf(SQL_TASKS_LIST, $userID, $query_string['completed'], ' AND t.ID NOT IN ('.$ids.')',$query_string['orderby'], $query_string['orderdir']);
				  $action = 'mytasks';
			  }
		  }
		}
		
		if ($range) {
			$query_string['start'] = 0;
			switch($range) {
				case "today" : $SQL = sprintf($sql, $userID, $query_string['completed'], ' AND DATE_FORMAT( t.EndDate, \'%Y-%m-%d\' ) = DATE_SUB(\''.$today.'\', INTERVAL \'0\' DAY) AND t.ID NOT IN ('.$ids.')', $query_string['orderby'], $query_string['orderdir']);break;
				case "yesterday" : $SQL = sprintf($sql, $userID, $query_string['completed'], ' AND DATE_FORMAT( t.EndDate, \'%Y-%m-%d\' ) = DATE_SUB(\''.$today.'\', INTERVAL \'1\' DAY) AND t.ID NOT IN ('.$ids.')', $query_string['orderby'], $query_string['orderdir']);break;
				case "lastweek" : $SQL = sprintf($sql, $userID, $query_string['completed'], ' AND DATE_FORMAT(t.EndDate,\'%Y-%m-%d\') >= DATE_SUB(\''.$today.'\', INTERVAL \''.($weekDay+7).'\' DAY) AND t.EndDate < DATE_ADD(DATE_SUB(\''.$today.'\', INTERVAL \''.($weekDay+7).'\' DAY), INTERVAL 7 DAY) AND t.ID NOT IN ('.$ids.')', $query_string['orderby'], $query_string['orderdir']);break;
				case "thisweek" : $SQL = sprintf($sql, $userID, $query_string['completed'], ' AND DATE_FORMAT(t.EndDate,\'%Y-%m-%d\') >= DATE_SUB(\''.$today.'\', INTERVAL \''.$weekDay.'\' DAY) AND t.EndDate < DATE_ADD(DATE_SUB(\''.$today.'\', INTERVAL \''.$weekDay.'\' DAY), INTERVAL 7 DAY) AND t.ID NOT IN ('.$ids.')', $query_string['orderby'], $query_string['orderdir']);break;
				case "nextweek" : $SQL = sprintf($sql, $userID, $query_string['completed'], ' AND DATE_FORMAT(t.EndDate,\'%Y-%m-%d\') >= DATE_ADD(\''.$today.'\', INTERVAL \''.(7-$weekDay).'\' DAY) AND t.EndDate < DATE_ADD(\''.$today.'\', INTERVAL \''.((7-$weekDay)+14).'\' DAY) AND t.ID NOT IN ('.$ids.')', $query_string['orderby'], $query_string['orderdir']);break;
				case "lastmonth" : $SQL = sprintf($sql, $userID, $query_string['completed'], ' AND MONTH(t.EndDate) = MONTH(DATE_SUB(\''.$today.'\', INTERVAL \'1\' MONTH)) AND YEAR(t.EndDate) = YEAR(DATE_SUB(\''.$today.'\', INTERVAL \'1\' MONTH)) AND t.ID NOT IN ('.$ids.')', $query_string['orderby'], $query_string['orderdir']);break;
				case "thismonth" : $SQL = sprintf($sql, $userID, $query_string['completed'], ' AND MONTH(t.EndDate) = MONTH(DATE_SUB(\''.$today.'\', INTERVAL \'0\' MONTH)) AND YEAR(t.EndDate) = YEAR(DATE_SUB(\''.$today.'\', INTERVAL \'0\' MONTH)) AND t.ID NOT IN ('.$ids.')', $query_string['orderby'], $query_string['orderdir']);break;
				case "nextmonth" : $SQL = sprintf($sql, $userID, $query_string['completed'], ' AND MONTH(t.EndDate) = MONTH(DATE_ADD(\''.$today.'\', INTERVAL \'1\' MONTH)) AND YEAR(t.EndDate) = YEAR(DATE_ADD(\''.$today.'\', INTERVAL \'1\' MONTH)) AND t.ID NOT IN ('.$ids.')', $query_string['orderby'], $query_string['orderdir']);break;
			}
		}

		if ($query_string['start'] == "all") {
			$RS =& new DBRecordset();
			$RS->Open($SQL, $this->DB);
		}
		else {
			if (!is_numeric($query_string['start'])) { $query_string['start'] = 0; }
			$RS =& new DBPagedRecordset();
			$RS->Open($SQL, $this->DB, $limit, $query_string['start']);
		}

		//~ paging code

		// get the list of that this user is assigned to.
		//$projects_access_list = $this->User->GetUserItemAccess('projects', CU_ACCESS_READ);

		if (!$RS->EOF())
		{
			$tmpl['lblTask'] = MSG_TASK;
			$tmpl['lblClient'] = MSG_CLIENT;
			$tmpl['lblProject'] = MSG_SUBJECT;
			$tmpl['lblProgress'] = MSG_PROGRESS;
			$tmpl['lblUrgency'] = MSG_URGENCY;
			$tmpl['lblDuration'] = MSG_ESTIMATED;
			$tmpl['lblDue'] = MSG_ENDS;
			$tmpl['lblStarts'] = MSG_STARTS;
			$tmpl['lblLatestActivity'] = MSG_LATESTACTIVITY;
			$tmpl['lblBatch'] = MSG_BATCH;
			$tmpl['lblAsc']	  = MSG_ASCENDING;
			$tmpl['lblDesc']	 = MSG_DESCENDING;
			$tmpl['start']	 = $query_string['start'];
			$tmpl['show']	 = $range;
			$tmpl['action']	 = $action;
			$tmpl['completed']	 = $query_string['completed'];
			$tmpl['AjaxUrl'] = url::build_url();

			$task_tmpl = $this->getTemplate('list_header', $tmpl);
			unset($tmpl);


			$counter = 1;
			while (!$RS->EOF())
			{
				$tmpl['caller'] = $this->ModuleName;
				// don't throw in 'all' in the template. that's just silly. fall back to the admin ID, given
				// that they are the only ones that can do this functionality anyways.
				$tmpl['userid'] = ($userID == 'all') ? $this->User->ID : $userID;
				$tmpl['action'] = $action;
				$quickedit_flag = (($quickedit == 1) && ($quickedit_id == $RS->Field('ID'))) ? '0':'1';
				$tmpl['txtOptions'] = ($status == 1) ? '&amp;action=view' : '&amp;quickedit=' . $quickedit_flag;
				$tmpl['txtOptions'] .= '&amp;start='.$query_string['start'];
				$tmpl['txtStatus'] .= $status;
				$tmpl['txtQuery'] = "&action=taskview&projectid=".$RS->Field('ProjectID')."&taskid=".$RS->Field('ID');
				$tmpl['batch'] = '<input type="checkbox" name="batch" value="'.$RS->Field('ID').'"></td>';
				$tmpl['txtID'] = $RS->Field('ID');
				$tmpl['txtClientName'] = ($this->User->HasUserItemAccess('clients', $RS->Field('ClientID'), CU_ACCESS_READ)) ? '<a href="index.php?module=clients&action=view&id=' . $RS->Field('ClientID') . '">' . $RS->Field('ClientName').'</a>' : $RS->Field('ClientName');
				$tmpl['txtName'] = $RS->Field('Name');
				
				$issues = $this->DB->ExecuteScalar(sprintf(SQL_COUNT_TASK_ISSUES, $RS->Field('ID')));
				if ($issues > 0) {
					$tmpl['txtName'] .= ' <span class="issue">'.MSG_ISSUE.'</span>';
				}
				
				$tmpl['txtClientID'] = $RS->Field('ClientID');

				$sql = sprintf(SQL_GET_LAST_COMMENT,$RS->Field('ID'));
				$result = $this->DB->QuerySingle($sql);
				$div = NULL;
				if ($result) {
					$comment = $result['Body'];
					$updated_by = $result['FullName'].' - '.Format::date($result['Date']);
					$div = "<div class=\"tooltip\"><h1>Last update</h1><p>".$comment."</p><p>".$updated_by."</p></div>";
				}
				$tmpl['div'] = $div;

				$tmpl['txtDuration'] = $this->CalculateDuration($RS->Field('Duration'));
				$tmpl['txtProjectID'] = $RS->Field('ProjectID');
				$tmpl['txtProjectName'] = ($this->User->HasUserItemAccess('projects', $RS->Field('ProjectID'), CU_ACCESS_READ)) ? '<a href="index.php?module=projects&action=view&projectid=' . $RS->Field('ProjectID') . '">' . $RS->Field('ProjectName').'</a>' : $RS->Field('ProjectName');
				$tmpl['txtColour'] = $RS->Field('Colour');
				$tmpl['txtComplete'] = @number_format($RS->Field('PercentComplete'));
				$tmpl['txtRolloverPercent'] = @number_format($RS->Field('PercentComplete')) . "% Complete";
				$tmpl['txtPriority'] = Format::convert_priority($RS->Field('Priority'));
				$tmpl['txtLatestActivity'] = Format::date($RS->Field('LatestActivity'));
				$tmpl['txtStarts'] = Format::date($RS->Field('StartDate'));
				$tmpl['txtDue'] = Format::date($RS->Field('EndDate'));

				$task_tmpl .= $this->getTemplate('list_item', $tmpl);
				unset($tmpl);
				++$counter;
				$RS->MoveNext();
			}

			$task_tmpl .= $this->getTemplate('list_footer', $footer_tmpl);

			if ($RS->TotalRecords > $limit)
			{
				$url = 'index.php?module=springboard&action='.$action.'&completed='.$query_string['completed'].'&order='.$query_string['order'].'&direction='.$query_string['direction'];
				cuPaginate($RS->TotalRecords, $limit, $url, $query_string['start'], $tmpl);
				$task_tmpl .= $this->getTemplate('paging', $tmpl);
				unset($tmpl);
			}
		}
		else
		{
			$tmpl['MESSAGE'] = MSG_NO_TASKS_AVAILABLE;
			$tmpl['lblIcon'] = 'tasks';
			$task_tmpl .= $this->getTemplate('eof', $tmpl);
		}
		$RS->Close();
		unset($RS);

		return $task_tmpl;
	}

	function TaskComment() {
		$taskID = (int) Request::post('taskid');
		$projectID = (int) Request::post('projectid');
		$commentID = (int) Request::post('commentid');
		$comment = $this->DB->Prepare(Request::post('comment'));
		$comment = preg_replace('(http:[\w/.:+\-~#?]+)','<a href=\"$0">$0</a>', $comment);
		$percentage = (int) Request::post('percentage');
		$contact = (int) Request::post('contact');
		$oldhours = (double) Request::post('oldhours');
		$hours = (double) abs(Request::post('hours'));
		$status = ($percentage == 100) ? '1' : '0';
		$issue = (strlen(Request::post('issue')) > 0) ? 1 : 0;
		$outofscope = (strlen(Request::post('outofscope')) > 0) ? 1 : 0;
		$billcode = $this->DB->Prepare(Request::post('billcode'));
		$date = Format::parse_date(Request::post('date'));
		$subject = $this->DB->Prepare(MSG_QUICK_UPDATE);
		$caller = Request::post('caller');

		$hasProjectWrite = $this->User->HasUserItemAccess('projects', $projectID, CU_ACCESS_WRITE);
		if ($hasProjectWrite || $this->UserIsAssigned($taskID)) 
		{
			// Update/insert the comment and hours.
			if ($commentID > 0) 
			{
				$sql = sprintf(SQL_UPDATE_COMMENT, $this->User->ID, $commentID, $comment, $hours, $issue, $contact, 
					$outofscope, $date.' '.date('H:i:s'), $this->User->CostRate, $this->User->ChargeRate);
				$this->DB->Execute($sql);
			}
			else 
			{
				$dateValue = $date.' '.date('H:i:s');
				$sql = sprintf(SQL_INSERT_COMMENT, $this->User->ID, $taskID, $subject, $comment, $hours, $issue, $contact, 
					$outofscope, $dateValue, $this->User->CostRate, $this->User->ChargeRate);
				$this->DB->Execute($sql);

				$sql = sprintf(SQL_GET_COMMENT_ID, $this->User->ID, $dateValue);
				$row = $this->DB->QuerySingle($sql);
				if(is_array($row)){
					$commentID = $row['ID'];
				}
			}
	  // Code no longer sets task to inactive/complete. Reintroduce that here:
	  if ($status == 1)
		$this->DB->Execute(sprintf(SQL_COMPLETE_TASK, $taskID));
		

			// If the user is a resource, update their completed hours records.
			$sql = sprintf(SQL_GET_RESOURCE_ID, $this->User->ID);
			$resourceID = (int)$this->DB->ExecuteScalar($sql);
			if ( $resourceID > 0 )
			{
				list($y, $m, $d) = explode('-', $date);
				$sql = sprintf(SQL_GET_DAY_ID_BY_DATE, $d, $m, $y);
				$dayID = (int)$this->DB->ExecuteScalar($sql);

				// Check that the resource is committed to the task.
				$sql = sprintf(SQL_CHECK_IF_ASSIGNED_TO_TASK_ON_DAY, $taskID, $resourceID, $dayID);
				$isAssigned = $this->DB->QuerySingle($sql);

				// Update/insert completed hours in tblTaskResourceDay.
				if (!$isAssigned) 
				{
					$sql = sprintf(SQL_INSERT_TASK_RESOURCE_DAY_COMMITMENT, $taskID, $resourceID, $dayID, 0.00, $hours);
					$this->DB->Execute($sql);
				}
				else 
				{
					$sql = sprintf(SQL_GET_TASK_USER_DAY_HOURS, $taskID, $this->User->ID, $date);
					$task = $this->DB->QuerySingle($sql);
					$sql = sprintf(SQL_UPDATE_TASK_RESOURCE_DAY, $task['HoursWorked'], $taskID, $resourceID, $dayID);
					$this->DB->Execute($sql);
				}
			}

			// Update the task and project costs.
			// The cost is retrieved from vwTaskComments but saved in tblTasks/tblProjects.
			// Have to do it with a temporary variable because MySQL won't let you 
			// insert into a table if you are also selecting from it in a view.
			$task = $this->DB->QuerySingle(sprintf(SQL_GET_TASK_COST, $taskID));
			$sql = sprintf(SQL_UPDATE_TASK_HOURSWORKED_ACTUAL_BUDGET, $task['HoursWorked'], $task['Cost'], $taskID);
			$this->DB->Execute($sql);

			$project = $this->DB->QuerySingle(sprintf(SQL_GET_PROJECT_COST, $projectID));
			$sql = sprintf(SQL_UPDATE_PROJECT_ACTUAL_BUDGET, $project['Cost'], $projectID);
			$this->DB->Execute($sql);

			// Send an email about this comment.
			$this->mailOnUpdate($taskID, $this->User->ID, $projectID, stripslashes($comment));
		}


		if ($caller == 'stopwatch')
			echo "{success:1, date:".$date."}";
		elseif ($caller == 'taskview'){
			//adding tasks from the ajax taskview we respond with the udpated comment
			//which can be drawn dynamically.
			$tmplComment['txtCommentID'] = $commentID;
			$tmplComment['txtEditMsg'] = MSG_EDIT;
			$tmplComment['txtUsername'] = $this->User->Fullname;
			$tmplComment['txtHours'] = ($hours > 0.00) ? $hours.MSG_HRS : MSG_QUICK_UPDATE;
			$tmplComment['txtContact'] = $contact;
            $tmplComment['txtBillable'] = ($outofscope == '1') ? ', <span class="billability baNonBillable">'.MSG_NOT_BILLABLE.'</span>' : ', <span class="billability baBillable">'.MSG_BILLABLE.'</span>';
			$tmplComment['txtIssue'] = ($issue == '1') ? ',<span class="issue">'.MSG_ISSUE.'</span>' : '';
			$tmplComment['txtBody'] = Format::blocktext($comment);
			$tmplComment['txtBodyValue'] = $comment;
			$tmplComment['txtDayValue'] =  Format::date($date, TRUE, FALSE);
			//$tmplComment['txtDay'] = MSG_TODAY;
			if ($date == date('Y-m-d'))
				$tmplComment['txtDay'] = MSG_TODAY;
			elseif ($date == date('Y-m-d', time()-24*60*60))
				$tmplComment['txtDay'] = MSG_YESTERDAY;
			else
				$tmplComment['txtDay'] = Format::date($date);

			$tmplComment['txtEdit'] = '';
			$tmplComment['txtTime'] = date('H:ia');
			$params = "projectid=$projectID&taskid=$taskID&commentid={$c['ID']}";
			$editUrl = url::build_url('projects', 'taskcommentedit', $params);
			$delUrl = url::build_url('projects', 'deletecomment', $params);
			$hasAdmin = $this->User->HasModuleItemAccess('administration', CU_ACCESS_ALL, CU_ACCESS_READ);
			$tmplComment['txtEdit'] = '<a href="'.$editUrl.'">'.MSG_EDIT.'</a> | <a href="'.$delUrl.'">X</a>';
			$tmplComment['txtProjectID']=$projectID;
			$tmplComment['txtTaskID']=$taskID;

			$commentBlock = $this->getTemplate('tasks_view_comment', $tmplComment);
			echo $commentBlock;
		} elseif ($caller == 'springboard') {
			Response::redirect('index.php?module=springboard&action=taskview&taskid='.$taskID.'&projectid='.$projectID);
				} elseif ($caller == 'stopwatch') {
					echo "{success:1}";
				} else
			Response::redirect('index.php?module=projects&action=taskview&taskid='.$taskID.'&projectid='.$projectID);
	}

	function TaskComments() {
			header('Content-Type: text/html; charset='.CHARSET);
		$userID = $this->Session->Get('springboardID');
		$taskID = Request::get( 'id' );
		$projectID = $this->DB->ExecuteScalar( sprintf( SQL_GET_PROJECTID, $taskID ) );
		$colour = $this->DB->ExecuteScalar( sprintf( SQL_GET_PROJECT_COLOUR_FOR_TASK, $taskID ) );
		$colour = ( strlen( $colour ) > 0 ) ? $colour : '#00CCFF';
		$ownerID = $this->DB->ExecuteScalar( sprintf( SQL_GET_TASK_OWNER_ID, $taskID ) );

		// Task Comments Form + User Comments
		$comments = '';
		$comments_sql = sprintf(SQL_TASKS_GET_COMMENTS, $taskID);

		// Ben requested that users can see all comments if they can see a task at all.
		// If a user is selected and they are not the owner of the task, restrict them to only seeing their own task comments.
		//if (is_numeric($userID) && $userID != $ownerID)  
		//	$comments_sql = sprintf(SQL_TASKS_GET_USERS_COMMENTS, $taskID, $userID);

		$comments_list = $this->DB->Query($comments_sql);
		if (is_array($comments_list) && count($comments_list) > 0) {
			for ($i = 0; $i < count($comments_list); $i++) {
				if ($comments_list[$i]['HoursWorked'] == 0 )
					$hours = MSG_QUICK_UPDATE;
				else 
					$hours = $this->CalculateDuration($comments_list[$i]['HoursWorked']);

				$comment_tmpl['id'] = 'task'.$comments_list[$i]['ID'];
				$comment_tmpl['txtColour'] = $colour;
				$comment_tmpl['txtUsername'] = $comments_list[$i]['FirstName'] . ' ' . $comments_list[$i]['LastName'];
				$comment_tmpl['txtComment'] = Format::blocktext($comments_list[$i]['Body']);
				$comment_tmpl['txtWorked'] = $hours;
				$comment_tmpl['txtWorked'] .= ($comments_list[$i]['OutOfScope'] == 1) ? '*' : '';

				list( $date, $time ) = explode( ' ', $comments_list[$i]['Date'] );
				$time = strtotime( $comments_list[$i]['Date'] );
				$comment_tmpl['txtTime'] = '';
				$comment_tmpl['txtDay'] = date( 'l', $time );
				$comment_tmpl['txtTime'] = Format::date( $date, TRUE, FALSE) . ' ';
				$comment_tmpl['txtTime'] .= date( 'g:ia', $time );

				$editUrl = "index.php?module=projects&action=taskcommentedit&projectid=$projectID&taskid=$taskID&commentid={$comments_list[$i]['ID']}";
				$delUrl  = "index.php?module=projects&action=deletecomment&projectid=$projectID&taskid=$taskID&commentid={$comments_list[$i]['ID']}";
				$comment_tmpl['txtEdit'] = '';
				if ($this->User->HasModuleItemAccess('administration', CU_ACCESS_ALL, CU_ACCESS_READ) || 
					(($comments_list[$i]['UserID'] == $this->User->ID) && (Settings::get('TaskLogEdit') == 1))) 
					$comment_tmpl['txtEdit'] = '(<a href="'.$editUrl.'">edit</a> | <a href="'.$delUrl.'">delete</a>)';

				$comment_tmpl['ISSUE'] = ($comments_list[$i]['Issue'] == 1) ? '&nbsp;'.strtoupper( MSG_ISSUE ).'&nbsp;</span>&nbsp;' : '</span>';
				$comment_tmpl['txtColour'] = ($comments_list[$i]['Issue'] == 1) ? $colour : '';

				$comments .= $this->getTemplate('task_comment', $comment_tmpl);
				unset($comment_tmpl);
			}
		} else {
			$comments .= $this->getTemplate('task_comment_none', array('txtColour' => $colour, 'message' => MSG_NO_COMMENTS_AVAILABLE));
		}

		$tmpl = array();
		$tmpl['lblComment'] = MSG_QUICK_COMMENT;
		$tmpl['COMMENTS'] = $comments;
		$tmpl['taskID'] = $taskID;
		$tmpl['projectID'] = $projectID;
		$tmpl['userID'] = $userID;
		$tmpl['txtCommentDate'] = Format::date( date( 'Y-m-d' ), FALSE, FALSE);
		$tmpl['lblHours'] = MSG_HOURS_WORKED;
		$tmpl['lblPercentage'] = MSG_PERCENTAGE_SYMBOL_COMPLETE;
		$tmpl['MSG_FLAG_AS_ISSUE'] = MSG_FLAG_AS_ISSUE;
		$tmpl['MSG_OUT_OF_SCOPE'] = MSG_OUT_OF_SCOPE;
		$tmpl['date_format'] = $this->date_format[Settings::get('DateFormat')];
		$tmpl['selectPercentage'] = $this->SelectPercentage($this->DB->ExecuteScalar(sprintf(SQL_GET_TASK_PERCENTAGE,$taskID)));

		$tmpl['selectContact'] = '<option></option>';
		$sql = sprintf(SQL_CONTACTS_LIST,'ContactName','ASC', $projectID);
		$list = $this->DB->Query($sql);
		if ( is_array($list) ) {
			$list_count = count($list);
			for ($i = 0; $i < $list_count; $i++) {
				$contacts .= sprintf('<option value="%s" %s>%s</option>', $list[$i]['ID'], ($list[$i]['ID'] == $contact) ? 'SELECTED' : '',$list[$i]['ContactName']);
			}
		}
		$tmpl['selectContact'] .= $contacts;
		echo $this->getTemplate( 'springboard_task', $tmpl ); 
		}

	function mailOnUpdate($taskID, $userID, $projectID, $update) {
		if (Settings::get('EmailOnUpdate') > 0) 
		{
	  $sent_array = array();

			$url = url::build_url('projects', 'taskview', "taskid=$taskID&projectid=$projectID");

			$SQL = sprintf(SQL_GET_EMAIL_SUBJECT_DETAILS, $taskid);
			$subjectdetails = $this->DB->QuerySingle($SQL);
			$SQL = sprintf(SQL_GET_USERNAME, $userid);
			$username = $this->DB->ExecuteScalar($SQL);
			$users_list = $this->DB->Query(sprintf(SQL_TASKS_EMAIL, $taskid));
			if ( is_array($users_list) )
			{
				$users_count = count($users_list);
				for ($i = 0; $i < $users_count; $i++)
				{
					$sent_array[] = $users_list[$i]['EmailAddress'];
					$mailer = new SMTPMail();
					$mailer->FromName = SYS_FROMNAME;
					$mailer->FromAddress = SYS_FROMADDR;
					$mailer->Subject = sprintf(MSG_TASK_EMAIL_UPDATE_CC_SUBJECT, "- {$subjectdetails['ClientName']}: {$subjectdetails['ProjectName']}: {$subjectdetails['TaskName']}");
					$mailer->ToName = $users_list[$i]['FirstName'];
					$mailer->ToAddress = $users_list[$i]['EmailAddress'];
					$mailer->Body = sprintf(MSG_TASK_EMAIL_UPDATE_CC_BODY, $users_list[$i]['FirstName'], $subjectdetails['ProjectName'], $subjectdetails['TaskName'], $update, $username, $url);
					$mailer->Execute();
				}

				if (Settings::get('CCTaskOwner') > 0) 
				{
					$sql = sprintf(SQL_GET_TASK_OWNER, $taskid);
					$owner_result = $this->DB->QuerySingle($sql);
					if ($owner_result && !(in_array($owner_result['EmailAddress'], $sent_array))) 
					{
						$mailer = new SMTPMail();
						$mailer->FromName = SYS_FROMNAME;
						$mailer->FromAddress = SYS_FROMADDR;
						$mailer->Subject = sprintf(MSG_TASK_EMAIL_UPDATE_OWNER_SUBJECT, "- {$subjectdetails['ClientName']}: {$subjectdetails['ProjectName']}: {$subjectdetails['TaskName']}");
						$mailer->ToName = $owner_result['FirstName'];
						$mailer->ToAddress = $owner_result['EmailAddress'];
						$mailer->Body = sprintf(MSG_TASK_EMAIL_UPDATE_OWNER_BODY, $owner_result['FirstName'], $subjectdetails['ProjectName'], $subjectdetails['TaskName'], $update, $username, $url);
						$mailer->Execute();
					}
				}
			}
		}
	}

	function UserIsAssigned($taskid = 0) {
		$userid = $this->User->ID;
		$SQL = sprintf(SQL_ASSIGNED_COUNT, $taskid, $userid);
		$assigned = $this->DB->ExecuteScalar($SQL);

		$owner = $this->DB->ExecuteScalar(sprintf(SQL_GET_TASK_OWNER_ID, $taskid));
		if ($owner == $userid)
			$assigned = $assigned + 1;

		if ( $assigned > 0 )
		{
			return true;
		}
		return false;
	}

	function SelectPercentage($val) {
		$list = null;
		for ($i = 0; $i <= 100; $i += 5)
		{
			$list .= sprintf('<option value="%1$s"%2$s>%1$s</option>', $i, ($val == $i) ? ' SELECTED' : '');
		}
		return $list;
	}

	function CalculateDuration($hours) {
		$duration = round($hours, 2);
		if ($duration > DAY_LENGTH)
		{
			$duration = round($duration / DAY_LENGTH, 1);
			$dt = ($duration != 1) ? MSG_DAYS : MSG_DAY;
		}
		else
		{
			$dt = ($duration != 1) ? MSG_HOURS : MSG_HOUR;
		}
		return $duration . ' ' . $dt;
	}

	function CreateTabs($active) {
		$tmpl['lblTodoTab'] = $this->AddTab(MSG_TODO, 'todo', $active);
		$tmpl['lblActivityTab'] = $this->AddTab(MSG_ACTIVITY, 'activity', $active);
		$this->setTemplate('tabs', $tmpl);
		unset($tmpl);
	}

	function AddTab($name, $action, $active) {
		if ($active == strtolower($action)) $tab = 'tab_active';
		else $tab = 'tab_inactive';
		if (strlen($action) > 0) $query = '&amp;action='.$action;
		return $this->getTemplate($tab, array('lblTabName' => $name, 'lblTabQuery' => $query));
	}
}

 
