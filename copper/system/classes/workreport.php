<?php
/**
 * Work Reports
 * $Id$
 */

class WorkReport extends Report
{
	// 'item' required classes
	protected $tableName = 'tblWorkReports';
	
	protected $defaultFields = array(
		'ID',
		'UserID',
		'Name',
		'StartDate',
		'EndDate',
		'Users',
		'Clients',
		'Projects',
		'Frequency',
		'Created',
		'Period',
		'WithOtherItems',
	);
	
	protected $default_data = array(
		'StartDate' => null,
		'EndDate' => null,
		'WithOtherItems' => 0,
	);
	// end item required classes.
	
	
	
	// Used during operation for the aggregate data.
	public $data = array();
	public $rows = array();

	function __construct( $id )
	{
		parent::__construct($id);
		if ($this->exists)
		{
			$this->sanitise();
		}
	}
	
	public function sanitise()
	{
		// do some clean up to make other bad code happy
		if ($this->Projects == '') 
		{
			$this->Projects = '-1'; // Default is to select all projects.	
		}

		if ($this->Clients == '')
		{
			$this->Clients = '-1'; // Default is to select all clients.
		}
		
		if ($this->Users == '')
		{
			$this->Users = '-1'; // Default is to select all users.
		}

		// If all clients is selected, make that the only entry in the field.
		$clientIDsArray = explode(',', $this->Clients);
		if (in_array('-1', $clientIDsArray))
			$this->Clients = '-1';

		// If all projects is selected, make that the only entry in the field.
		$projectIDsArray = explode(',', $this->Projects);
		if (in_array('-1', $projectIDsArray))
			$this->Projects = '-1';

		// If all users is selected, make that the only entry in the field.
		$userIDsArray = explode(',', $this->Users);
		if (in_array('-1', $userIDsArray)) 
			$this->Users = '-1';
			
	}

	public function getData( $useView = FALSE )
	{
		// SQL query to not use view.
		$sql = "SELECT u.ID as UserID, CONCAT(u.FirstName, ' ', u.LastName) AS UserName,
			c.ID AS ClientID, c.Name AS ClientName, p.ID AS ProjectID, 
			p.Name AS ProjectName, t.ID AS TaskID, t.Name AS TaskName,
			tc.Date AS Date, tc.HoursWorked AS HoursWorked, tc.CostRate, 
			tc.ChargeRate, (tc.HoursWorked * tc.CostRate) AS Cost,
			(tc.HoursWorked * tc.ChargeRate) AS Charge,
			tc.Issue AS Issue, tc.OutOfScope AS OutOfScope,
			tc.Body AS Comment, tc.ID AS CommentID,
			p.ProjectID AS CustomProjectID
			FROM tblTasks_Comments tc
			INNER JOIN tblUsers AS u ON u.ID = tc.UserID
			INNER JOIN tblTasks AS t ON t.ID = tc.TaskID
			INNER JOIN tblProjects AS p ON p.ID = t.ProjectID
			INNER JOIN tblClients AS c ON c.ID = p.ClientID
			WHERE tc.Date BETWEEN ? AND ? 
				AND tc.HoursWorked > 0 ";
		
		$sql_data = array($this->getStartDate(null, 1) . " 00:00:00", $this->getEndDate(null, 1) . " 23:59:59");

		list($clause, $pcu_sql_data) = $this->get_project_client_user_clauses_and_data();
		$sql_data = array_merge($sql_data, $pcu_sql_data);

		$sql .= $clause . " ORDER BY UserName ASC, Date ASC, ClientName ASC, ProjectName ASC, TaskName ASC";

		$pdo_s = DB::q($sql, $sql_data);
		// we just want the straight data here.
		$this->rows = $pdo_s->fetchAll(PDO::FETCH_ASSOC);
		return $this->rows;
	}

	private function get_project_client_user_clauses_and_data()
	{
		$cond = '';
		$sql_data = array();
		
		$project_ids = explode(',', $this->Projects);
		$client_ids = explode(',', $this->Clients);
		$user_ids = explode(',', $this->Users);
		
		if ( $this->Projects != '-1' && $this->Clients != '-1' )
		{
			$cond = "AND (p.ID IN (" . implode(',', array_fill(0, count($project_ids), '?')) . ") "
											. "OR ClientID IN (" . implode(',', array_fill(0, count($client_ids), '?')) . "))";
			$sql_data = array_merge($sql_data, $project_ids);
			$sql_data = array_merge($sql_data, $client_ids);
			
		} elseif ( $this->Projects != '-1' && $this->Clients == '-1' )
		{
			$cond = "AND p.ID IN (" . implode(',', array_fill(0, count($project_ids), '?')) . ")";
			$sql_data = array_merge($sql_data, $project_ids);

		} elseif ( $this->Projects == '-1' && $this->Clients != '-1' )
		{
			$cond = "AND ClientID IN (" . implode(',', array_fill(0, count($client_ids), '?')) . ")";
			$sql_data = array_merge($sql_data, $client_ids);
		}

		if ( $this->Users != '-1' )
		{
			$cond .= " AND UserID IN (" . implode(',', array_fill(0, count($user_ids), '?')) . ")";
			$sql_data = array_merge($sql_data, $user_ids);
		}
		
		return array($cond, $sql_data);
	}

	function run()
	{
		$rows = $this->getData();
		
		foreach ( $rows as $row )
		{
			// get the data in a format that the other things like.
			$this->data[] = array(
				'userid'	  => $row['UserID'], 
				'username'	=> $row['UserName'], 
				'clientid'	=> $row['ClientID'],
				'clientname'  => $row['ClientName'],
				'projectid'   => $row['ProjectID'],
				'projectname' => $row['ProjectName'],
				'taskid'	  => $row['TaskID'],
				'taskname'	=> $row['TaskName'],
				'date'		=> Format::date( $row['Date'], FALSE, FALSE),
				'hoursworked' => Format::hours( $row['HoursWorked'] ),
				'chargerate' => $row['ChargeRate'], 
			);
		}

		return $this->data;
	}

	public function get_other_items()
	{
		$project_ids = explode(',', $this->Projects);
		if (count($project_ids) > 0)
		{
			$cond = "WHERE ProjectID IN (" . implode(',', array_fill(0, count($project_ids), '?')) . ")";
			$sql_data = $project_ids;
		} else {
			$cond = '';
			$sql_data = array();
		}
		
		$ois = new InvoiceItemOthers(array('sql_where' => $cond, 'sql_where_values' => $sql_data));
		return $ois;
	}

	// Exports the data in CSV format.
	public function exportCSV($myob = FALSE)
	{
		if ($myob)
		{
			$row = array(
				'Emp. Co./Last Name',
				'Emp. First Name',
				'Slip ID',
				'Date',
				'Activity ID',
				'Cust. Co./Last Name',
				'Cust. First Name',
				'Units',
				'Rate',
				'Job',
				'Notes',
			);
			$csv = '"'.implode('","', $row)."\"\r\n";

			// cache the user objects
			$users = array();
			// iterate over raw data. screw that parsed crap
			foreach ($this->rows as $row)
			{
				if ( ! isset($users[$row['UserID']]))
				{
					$users[$row['UserID']] = new CopperUser($row['UserID']);
				}
				
				$user = $users[$row['UserID']];
				
				$row = array(
					$user->LastName,
					$user->FirstName,
					$row['CommentID'],
					$row['Date'],
					$row['TaskID'],
					$row['ClientName'],
					'', // empty customer first name, as we use the company name in the field above
					$row['HoursWorked'],
					$row['ChargeRate'],
					$row['CustomProjectID'],
					Format::trunc($row['Comment'], 200),
				);
				
				$csv .= '"' . implode('","', $row) . "\"\r\n";
			}

			return $csv;
		} else {
			$columns = array(MSG_NAME, MSG_DATE, MSG_CLIENT, MSG_PROJECT, MSG_TASK, MSG_HOURS_WORKED);
			$csv = '"'.implode('","', $columns)."\"\r\n";

			foreach ($this->data as $v)
			{
				$csv .= "\"{$v['username']}\",\"{$v['date']}\",\"{$v['clientname']}\",\"{$v['projectname']}\",\"{$v['taskname']}\",{$v['hoursworked']}\r\n";
			}

			return $csv;
		}
	}
	
}
 
