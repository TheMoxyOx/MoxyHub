<?php
/**
 * iCal class. For making ical subscriptions
 * $Id$
 */

class ical {

	var $DB;

	const ICAL_PROJECT = 1;
	const ICAL_SPRINGBOARD = 2;

	private $mode;

	function subscribe($mode) {
		$this->mode = $mode;

		$this->DB =& new DBConnection(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
		$this->iCalOutput();	
	}

	function iCalHeader($Name) {
		$timezone = $this->DB->ExecuteScalar(CU_SQL_GET_TIME_ZONE);
		$return = "BEGIN:VCALENDAR\n"
			. "CALSCALE:GREGORIAN\n"
			. "PRODID:-//Apple Computer\, Inc//iCal 2.0//EN\n"
			. "X-WR-CALNAME:".$Name."\n"
			. "X-WR-TIMEZONE:".$timezone."\n"
			. "VERSION:2.0\n";
		return $return;
	}

	function iCalFooter() {
		$return = 'END:VCALENDAR';
		return $return;
	}

	function iCalTodo(&$RS) {
		$startdate = $RS->Field('StartDate');	
		$enddate = $RS->Field('EndDate');
		if ($startdate == '0000-00-00')
			$startdate = $enddate;
		else if ($enddate == '0000-00-00')
			$enddate = $startdate;
				
		$startdate = split("-",$startdate);
		$enddate = split("-",$enddate);

		if ($this->mode == self::ICAL_PROJECT)
		{
			$summary = "SUMMARY:".$RS->Field('Name')."\n";
		} else if ($this->mode == self::ICAL_SPRINGBOARD) {
			$summary = "SUMMARY:".$RS->Field('ClientName').' - '.$RS->Field('ProjectName').' - '.$RS->Field('Name')."\n";
		} else {
			$summary = '';
		}
		
		
		$url = url::build_url('projects', 'taskview', 'projectid='.$RS->Field('ProjectID').'&taskid='.$RS->Field('ID'));
		$return = "BEGIN:VTODO\n"
				. $summary
				. "DESCRIPTION:".$RS->Field('Description')."\nComplete this task:\n$url\n"
				. "DUE;VALUE=DATE:".$enddate[0].$enddate[1].$enddate[2]."\n"
				. "DTSTART;VALUE=DATE:".$startdate[0].$startdate[1].$startdate[2]."\n"
				. "UID:".$RS->Field('ID')."T\n"
				. "END:VTODO\n";

		return $return;
	}
	
	function iCalEvent(&$RS) {
		$startdate = $RS->Field('StartDate');
		$enddate = $RS->Field('EndDate');
		if ($startdate == '0000-00-00')
			$startdate = $enddate;
		else if ($enddate == '0000-00-00')
			$enddate = $startdate;
				
		$startdate = split("-",$startdate);
		$enddate = split("-",$enddate);

		if ($this->mode == self::ICAL_PROJECT)
		{
			$summary = "SUMMARY:".$RS->Field('Name')."\n";
		} else if ($this->mode == self::ICAL_SPRINGBOARD) {
			$summary = "SUMMARY:".$RS->Field('ClientName').' - '.$RS->Field('ProjectName').' - '.$RS->Field('Name')."\n";
		} else {
			$file = 'system';
		}
		

		$url = url::build_url('projects', 'taskview', 'projectid='.$RS->Field('ProjectID').'&taskid='.$RS->Field('ID'));
		$return = "BEGIN:VEVENT\n"
				. $summary
				. "DESCRIPTION:".$RS->Field('Description')."\nComplete this task:\n$url\n"
				. "DTEND;VALUE=DATE:".$enddate[0].$enddate[1].$enddate[2]."\n"
				. "DTSTART;VALUE=DATE:".$startdate[0].$startdate[1].$startdate[2]."\n"
				. "UID:".$RS->Field('ID')."E\n"
				. "END:VEVENT\n";

		return $return;
	}

	function iCalOutput() {
		$key = Request::get('key');

		$action = Request::get('action');
		$completed = Request::get('completed');
		$range = Request::get('show');

		$userid = Request::get('userid', Request::R_INT);
		$this->User		 =& new User();
		$this->User->Initialise($userid, $this->DB);

		//check valid
		//Create validity key
		$validkey = substr(md5($this->User->Fullname.$this->User->PasswordHash),2,8);
		if ($key == $validkey) {
			if ($this->mode == self::ICAL_PROJECT)
			{
				return $this->getProjectOutput();
			} else if ($this->mode == self::ICAL_SPRINGBOARD) {
				return $this->getSpringboardOutput();
			} else {
				return FALSE;
			}
		}
		else {
			echo "Invalid key";
		}
	}
	
	private function getProjectOutput() {
		$projectid = Request::get('projectid', Request::R_INT);
		$project = new Project(Request::get('projectid', Request::R_INT));
		$taskcount = $this->DB->ExecuteScalar(sprintf(SQL_TASK_COUNT, $projectid));

		if ($taskcount > 0) 
		{
			$ical = $this->iCalHeader($project->Name);

			$SQL = sprintf(SQL_GET_PROJECT_TASKS, $projectid, 'Sequence', 'ASC');
			$RS =& new DBRecordset();
			$RS->Open($SQL, $this->DB);
			if (!$RS->EOF())
			{
				while(!$RS->EOF()) {
					$ical .= $this->icalEvent($RS);
					$RS->MoveNext();
				}
			}
			$RS->Close();
			unset($RS);
			
			$SQL = sprintf(SQL_GET_PROJECT_TASKS, $projectid, 'Sequence', 'ASC');
			$RS =& new DBRecordset();
			$RS->Open($SQL, $this->DB);
			if (!$RS->EOF())
			{
				while(!$RS->EOF()) {
					$ical .= $this->icalTodo($RS);
					$RS->MoveNext();
				}
			}
			$RS->Close();
			unset($RS);

			$ical .= $this->iCalFooter();
			echo $ical;
		} else 
		{
			// nothing to do? nothing to view!
			// echo "Nothing to view.";
			$ical .= $this->iCalFooter();
			echo $ical;
		}
	}
	
	private function getSpringBoardOutput() {
		$action = Request::get('action');
		$completed = Request::get('completed');
		$range = Request::get('show');

		$userid = Request::get('userid', Request::R_INT);

		$today = date('Y-m-d');
		$newDate = getdate(time());
		$weekDay = $newDate['wday'];
		$month = $newDate['mon'];
		$ids = 0;
		if ($this->DB->ExecuteScalar(SQL_DEPENDENT_TASKS) < 1) {
			$tasks_with_dependencies = $this->DB->Query(SQL_GET_TASKS_WITH_DEPENDENCIES);
			if ($tasks_with_dependencies) {
				$ids = NULL;
				foreach($tasks_with_dependencies as $key => $value) {
					$ids .= $value['TaskID'].',';
				}
				$ids = substr($ids,0,-1);
			}
		}

		switch($action) {
			case "all": $sql = SQL_TASKS_ALL;$SQL = sprintf(SQL_TASKS_ALL, $userid, $completed, ' AND t.ID NOT IN ('.$ids.') ','ClientName', 'ASC');break;
			case "owed":$sql = SQL_TASKS_OWED;$SQL = sprintf(SQL_TASKS_OWED, $userid, $completed, ' AND t.ID NOT IN ('.$ids.')','ClientName', 'ASC');break;
			default:	$sql = SQL_TASKS_LIST;$SQL = sprintf(SQL_TASKS_LIST, $userid, $completed, ' AND t.ID NOT IN ('.$ids.')','ClientName', 'ASC');
		}
		
		if ($range) {
			switch($range) { 
  
			case "today": 
				$SQL = sprintf(
					$sql, 
					$userID, 
					$completed, 
					' AND DATE_FORMAT( t.EndDate, \'%Y-%m-%d\' ) = DATE_SUB(\''.$today.'\', INTERVAL \'0\' DAY) AND t.ID NOT IN ('.$ids.')',
					'ClientName',
					'ASC'
				);
				break;

			case "yesterday": 
				$SQL = sprintf(
					$sql, 
					$userID, 
					$completed, 
					' AND DATE_FORMAT( t.EndDate, \'%Y-%m-%d\' ) = DATE_SUB(\''.$today.'\', INTERVAL \'1\' DAY) AND t.ID NOT IN ('.$ids.')', 
					'ClientName', 
					'ASC'
				);
				break;

			case "lastweek": 
				$SQL = sprintf(
					$sql, 
					$userID, 
					$completed, 
					' AND DATE_FORMAT(t.EndDate,\'%Y-%m-%d\') >= DATE_SUB(\''.$today.'\', INTERVAL \''.($weekDay+7).'\' DAY) AND t.EndDate < DATE_ADD(DATE_SUB(\''.$today.'\', INTERVAL \''.($weekDay+7).'\' DAY), INTERVAL 7 DAY) AND t.ID NOT IN ('.$ids.')', 
					'ClientName', 
					'ASC'
				);
				break;

			case "thisweek": 
				$SQL = sprintf(
					$sql, 
					$userID, 
					$completed, 
					' AND DATE_FORMAT(t.EndDate,\'%Y-%m-%d\') >= DATE_SUB(\''.$today.'\', INTERVAL \''.$weekDay.'\' DAY) AND t.EndDate < DATE_ADD(DATE_SUB(\''.$today.'\', INTERVAL \''.$weekDay.'\' DAY), INTERVAL 7 DAY) AND t.ID NOT IN ('.$ids.')', 
					'ClientName', 
					'ASC'
				);
				break;
				
			case "nextweek": 
				$SQL = sprintf(
					$sql, 
					$userID, 
					$completed, 
					' AND DATE_FORMAT(t.EndDate,\'%Y-%m-%d\') >= DATE_ADD(\''.$today.'\', INTERVAL \''.(7-$weekDay).'\' DAY) AND t.EndDate < DATE_ADD(\''.$today.'\', INTERVAL \''.((7-$weekDay)+14).'\' DAY) AND t.ID NOT IN ('.$ids.')', 
					'ClientName', 
					'ASC'
				);
				break;

			case "lastmonth": 
				$SQL = sprintf(
					$sql, 
					$userID, 
					$completed, 
					' AND MONTH(t.EndDate) = MONTH(DATE_SUB(\''.$today.'\', INTERVAL \'1\' MONTH)) AND YEAR(t.EndDate) = YEAR(DATE_SUB(\''.$today.'\', INTERVAL \'1\' MONTH)) AND t.ID NOT IN ('.$ids.')', 
					'ClientName', 
					'ASC'
				);
				break;

			case "thismonth": 
				$SQL = sprintf(
					$sql, 
					$userID, 
					$completed, 
					' AND MONTH(t.EndDate) = MONTH(DATE_SUB(\''.$today.'\', INTERVAL \'0\' MONTH)) AND YEAR(t.EndDate) = YEAR(DATE_SUB(\''.$today.'\', INTERVAL \'0\' MONTH)) AND t.ID NOT IN ('.$ids.')', 
					'ClientName', 
					'ASC'
				);
				break;

			case "nextmonth": 
				$SQL = sprintf(
					$sql, 
					$userID, 
					$completed, 
					' AND MONTH(t.EndDate) = MONTH(DATE_ADD(\''.$today.'\', INTERVAL \'1\' MONTH)) AND YEAR(t.EndDate) = YEAR(DATE_ADD(\''.$today.'\', INTERVAL \'1\' MONTH)) AND t.ID NOT IN ('.$ids.')', 
					'ClientName', 
					'ASC'
				);
				break;
			} 
		}

		$ical = $this->iCalHeader($this->User->Fullname);

		$RS =& new DBRecordset();
		$RS->Open($SQL, $this->DB);
		if (!$RS->EOF()) 
		{
			while(!$RS->EOF()) 
			{
				$ical .= $this->icalEvent($RS);
				$ical .= $this->icalTodo($RS);
				$RS->MoveNext();
			}
			
			$ical .= $this->iCalFooter();
			echo $ical;

		} else {
			// nothing to do? nothing to view!
			// echo "Nothing to view.";
			$ical .= $this->iCalFooter();
			echo $ical;
		}
		
		$RS->Close();
		unset($RS);
	}
}

