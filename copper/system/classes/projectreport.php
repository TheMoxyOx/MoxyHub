<?php
/**
 * Project Report
 * $Id$
 */

class ProjectReport extends Report
{
	// 'item' required classes -- TO BE DONE
	protected $tableName = 'NYI';
	
	protected $defaultFields = array('NYI');
	// end item required classes

	public $DB = NULL;
	public $statusList = NULL;

	// Loaded from the database.
	public $id;
	public $userID;
	public $name;
	public $startDate;
	public $endDate;
	public $clients;
	public $projects;
	public $budget;
	public $details;
	public $frequency;
	public $created;
	public $period;

	// Used during operation.
	public $data = array();
	public $readyToRun = FALSE;

	function __construct( $DB )
	{
		$this->DB = $DB;
		$this->statusList = array(MSG_NA, MSG_PROPOSED, MSG_IN_PLANNING, MSG_IN_PROGRESS, MSG_ON_HOLD, MSG_COMPLETE, MSG_ARCHIVED, MSG_CANCELLED);
	}

	function __destruct()
	{
	}

	function load( $id )
	{
		$id = intval( $id );
		$sql = "SELECT * FROM tblProjectReports WHERE id = '$id'";
		$row = $this->DB->QuerySingle( $sql );
		if (is_array($row)) 
		{
			$this->id		= (int)$row['ID'];
			$this->userID	= (int)$row['UserID'];
			$this->name	  = $row['Name'];
			$this->startDate = $row['StartDate'];
			$this->endDate   = $row['EndDate'];
			$this->clients   = $row['Clients'];
			$this->projects  = $row['Projects'];
			$this->budget	= (int)$row['Budget'];
			$this->details   = (int)$row['Details'];
			$this->frequency = $row['Frequency'];
			$this->created   = $row['Created'];
			$this->period	= $row['Period'];

			$this->sanitise();
		}
	}

	// TODO: Adjust week settings to allow for Settings::get('WeekStart').
	// once we migrate project report to be an item class, we can remove this and replace it with the getStartDate / getEndDate as defined in the 
	// base report class.
	function adjustDates() 
	{
		if ( !empty( $this->period ) ) {
			$dow = ( date( 'w' ) == 0 ) ? 7 : date( 'w' );  // Emulate date( 'N' ) for PHP versions < 5.1.0
			switch ( $this->period ) {
				case 'today': 
					$this->startDate = date( 'Y-m-d' );
					$this->endDate = date( 'Y-m-d' );
					break;
				case 'yesterday': 
					$this->startDate = date( 'Y-m-d', mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - 1, date( 'Y' ) ) );
					$this->endDate = date( 'Y-m-d', mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - 1, date( 'Y' ) ) );
					break;
				case 'thisweek': 
					$this->startDate = date( 'Y-m-d', mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - $dow + 1, date( 'Y' ) ) );
					$this->endDate = date( 'Y-m-d', mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - $dow + 7, date( 'Y' ) ) );
					break;
				case 'lastweek': 
					$this->startDate = date( 'Y-m-d', mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - $dow - 6, date( 'Y' ) ) );
					$this->endDate = date( 'Y-m-d', mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - $dow, date( 'Y' ) ) );
					break;
				case 'thismonth': 
					$this->startDate = date( 'Y-m-01' );
					$this->endDate = date( 'Y-m-t' );
					break;
				case 'lastmonth': 
					$this->startDate = date( 'Y-m-01', mktime( 0, 0, 0, date( 'm' ) - 1, 1, date( 'Y' ) ) );
					$this->endDate = date( 'Y-m-t', mktime( 0, 0, 0, date( 'm' ) - 1, 1, date( 'Y' ) ) );
					break;
				case 'nextmonth': 
					$this->startDate = date( 'Y-m-01', mktime( 0, 0, 0, date( 'm' ) + 1, 1, date( 'Y' ) ) );
					$this->endDate = date( 'Y-m-t', mktime( 0, 0, 0, date( 'm' ) + 1, 1, date( 'Y' ) ) );
					break;
				case 'thisyear': 
					$this->startDate = date( 'Y-01-01' );
					$this->endDate = date( 'Y-12-31' );
					break;
				case 'lastyear': 
					$this->startDate = date( 'Y-01-01', mktime( 0, 0, 0, 1, 1, date( 'Y' ) - 1 ) );
					$this->endDate = date( 'Y-12-31', mktime( 0, 0, 0, 1, 1, date ('Y' ) - 1 ) );
					break;
				default: 
					$this->startDate = date( 'Y-m-d', mktime( 0, 0, 0, date( 'm' ) - 1, date( 'd' ), date( 'Y' ) ) );
					$this->endDate = date( 'Y-m-d' );
			}
		}

		// okay to fix this properly, we should just not have a between clause when there's no date parameter. 
		// But hacky hacky fixy fixy makes little children cry, but business people happy.
		// note the years are taken from the tblDay table, which is a horrible horrible hack (not mine)
		if ( strtotime( $this->startDate ) == 0 )
			$this->startDate = '2006-01-01';
		if ( strtotime( $this->endDate ) == 0 )
			$this->endDate = '2016-01-01';
	}


	function getProjectSQL()
	{
		$sql = "SELECT p.ID, p.Name, p.ClientID, c.Name AS ClientName, p.ProjectID, p.Status, p.TargetBudget "
			."FROM tblProjects p "
			."LEFT JOIN tblClients c ON p.ClientID = c.ID ";

		$where = '';
		if ( $this->projects != '-1' && $this->clients != '-1' )
			$where = "WHERE p.ID IN ($this->projects) OR p.ClientID IN ($this->clients)";
		elseif ( $this->projects != '-1' && $this->clients == '-1' )
			$where = "WHERE p.ID IN ($this->projects)";
		elseif ( $this->projects == '-1' && $this->clients != '-1' )
			$where = "WHERE p.ClientID IN ($this->clients)";

		$sql .= "$where AND p.Active = 1 AND c.Archived = 0 ";

		$sql .= "ORDER BY c.Name ASC, p.Name ASC";

		return $sql;
	}

	function run()
	{
		// If run() is called when there is nothing to run with, return something iterable.
		if (!$this->readyToRun)
			return array();

		$this->adjustDates();

		$d = Day::create_from_iso8601($this->startDate);
		$startDayID = $d->ID;
		$d = Day::create_from_iso8601($this->endDate);
		$endDayID = $d->ID;

		$sql = $this->getProjectSQL();

		$projects = $this->DB->Query( $sql );
		if (!is_array($projects)) $projects = array(); // Need iterator.
		foreach ( $projects as $project )
		{
			// About the project.
			$project['ProjectID'] = ( empty( $project['ProjectID'] ) ? "(none)" : $project['ProjectID'] );
			$status = $this->statusList[$project['Status']];
			$targetBudget = Format::money( $project['TargetBudget'] );

			// Estimated project duration.
			$sql = "SELECT SUM(Duration) AS Duration FROM tblTasks WHERE ProjectID = '{$project['ID']}'";
			$row = $this->DB->QuerySingle( $sql );
			$duration = Format::hours( $row['Duration'] );


			// Hours committed to project.
			// We ignore HoursCompleted as it is not being populated, but might be one day.
			$sql = "SELECT SUM(HoursCommitted) AS Committed, SUM(HoursCompleted) AS Completed "
				."FROM tblTasks "
				."LEFT JOIN tblTaskResourceDay ON tblTasks.ID = tblTaskResourceDay.TaskID "
				."WHERE tblTasks.ProjectID = '{$project['ID']}' "
				."AND tblTaskResourceDay.DayID BETWEEN $startDayID AND $endDayID";

			$row = $this->DB->QuerySingle( $sql );
			$hoursCommitted = Format::hours( $row['Committed'] );

			$sql = "SELECT SUM(HoursWorked) AS HoursWorked, SUM(Charge) AS Charge "
				."FROM vwTaskComments "
				."WHERE ProjectID = '{$project['ID']}' AND Date BETWEEN '$this->startDate 00:00:00' AND '$this->endDate 23:59:59'";

			$row = $this->DB->QuerySingle( $sql );
			$actualBudget = Format::money( $row['Charge'] );
			$hoursWorked = Format::hours( $row['HoursWorked'] );

			$this->data[] = array(
				'type' => 'project',
				'clientid' => $project['ClientID'],
				'clientname' => $project['ClientName'],
				'projectid' => $project['ID'], 
				'projectname' => $project['Name'],
				'projectlabel' => $project['ProjectID'],
				'status' => $status, 
				'duration' => $duration, 
				'hourscommitted' => $hoursCommitted, 
				'hoursworked' => $hoursWorked, 
				'targetbudget' => $targetBudget,
				'actualbudget' => $actualBudget,
			);

			if ( $this->details > 0 )
			{
				$sql = "SELECT ID, Name, Duration, Status, TargetBudget FROM tblTasks "
					."WHERE ProjectID = '{$project['ID']}' ORDER BY Name ASC";
				$tasks = $this->DB->Query( $sql );
				if (!is_array($tasks)) $tasks = array(); // Need iterator.
				foreach ( $tasks as $task )
				{
					$duration = Format::hours( $task['Duration'] );
					$status = $this->statusList[$task['Status']];
					$targetBudget = Format::money( $task['TargetBudget'] );

					$sql = "SELECT SUM(HoursCommitted) AS HoursCommitted, SUM(HoursCompleted) AS HoursCompleted "
						."FROM tblTaskResourceDay "
						."WHERE TaskID = '{$task['ID']}' AND DayID BETWEEN $startDayID AND $endDayID";
					$row = $this->DB->QuerySingle( $sql );
					$hoursCommitted = Format::hours( $row['HoursCommitted'] );

					$sql = "SELECT SUM(HoursWorked) AS HoursWorked, SUM(Charge) AS Charge "
						."FROM vwTaskComments "
						."WHERE TaskID = '{$task['ID']}' AND Date BETWEEN '$this->startDate 00:00:00' AND '$this->endDate 23:59:59' ";
					$row = $this->DB->QuerySingle( $sql );
					$actualBudget = Format::money( $row['Charge'] );
					$hoursWorked = Format::hours( $row['HoursWorked'] );

					$this->data[] = array(
						'type' => 'task',
						'clientid' => $project['ClientID'],
						'clientname' => $project['ClientName'],
						'projectid' => $project['ID'], 
						'projectname' => $project['Name'],
						'taskid' => $task['ID'], 
						'taskname' => $task['Name'], 
						'status' => $status, 
						'duration' => $duration, 
						'hourscommitted' => $hoursCommitted, 
						'hoursworked' => $hoursWorked, 
						'targetbudget' => $targetBudget,
						'actualbudget' => $actualBudget,
					);
				}
			}
		}

		return $this->data;
	}

	// Cleans up the data so we don't have any nasty stuff in it.
	function sanitise() 
	{
		if (empty($this->projects)) $this->projects = '-1'; // Default is to select all projects.
		if (empty($this->clients)) $this->clients = '-1'; // Default is to select all clients.

		// If all clients is selected, make that the only entry in the field.
		$clientIDsArray = explode(',', $this->clients);
		if (in_array('-1', $clientIDsArray))
			$this->clients = '-1';

		// If all projects is selected, make that the only entry in the field.
		$projectIDsArray = explode(',', $this->projects);
		if (in_array('-1', $projectIDsArray))
			$this->projects = '-1';

		$this->readyToRun = TRUE;
	}

	// Exports the data in CSV format.
	function exportCSV($myob = FALSE)
	{
		$columns = array(MSG_NAME, MSG_CLIENT, MSG_PROJECT_ID, MSG_STATUS, MSG_ESTIMATED, MSG_COMMITTED, MSG_COMPLETED);
		if ( $this->budget > 0 )
			array_push($columns, MSG_TARGET_BUDGET, MSG_ACTUAL_BUDGET);
		$csv = '"'.implode('","', $columns)."\"\r\n";

		foreach ($this->data as $v)
		{
			// First four columns are type-specific, the others aren't.
			if ($v['type'] == 'project')
				$csv .= "\"{$v['projectname']}\",\"{$v['clientname']}\",\"{$v['projectlabel']}\",\"{$v['status']}\",";
			if ($v['type'] == 'task')
				$csv .= "\"{$v['taskname']}\",,,,";

			$csv .= "\"{$v['duration']}\",\"{$v['hourscommitted']}\",\"{$v['hoursworked']}\"";
			$csv .= ( $this->budget ) ? ",\"{$v['targetbudget']}\",\"{$v['actualbudget']}\"\r\n" : "\r\n";
		}

		return $csv;
	}
}
 
