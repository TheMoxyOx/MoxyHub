<?php
class mod_cron extends Module
{

	// you can run this module from the command line, so you can kick off scheduled tasks from cron
	// The action=noaction parameter is required to because MailProjectReports() includes the reports module,
	// and without overriding the action parameter it will print out the full Copper Reports screen and General tab.
	//   wget -q -O- 'http://localhost/copper/index.php?module=cron&action=noaction&run=1'

	function mod_cron() {
		$this->ModuleName	 = 'cron';
		$this->ModuleID	  = '26D3FABDA482E8017DF1FFE0874636AC';
		$this->RequireLogin = 0;
		$this->Public		 = 0;
		parent::Module();
	}

	function main() {
		if (Request::get('run') != '') {
			$this->Run();
		}
	}

	function Run() {

		// update those alerts plox
		Alerts::update_from_master();

		// are you serious. i can't deal with this shit right now.
		$firstlogin = Settings::get('FirstLogin');
		$midnight = date("Ymd")."000001";
		if (Request::get('force') != '' || $firstlogin < $midnight) {  

			$timestamp = date("YmdHis");
			$sql = sprintf(SQL_UPDATETIMESTAMP,$timestamp);
			$this->DB->Execute($sql);
			$this->MailUsers(Settings::get('DaysBeforeTaskDue'), 'is', 1);
			$this->MailUsers(Settings::get('DaysAfterTaskDue'), 'was', -1);
			
			$this->MailProjectReports();
			$this->MailWorkReports();

		}

	}

	function MailUsers($setting, $message, $int) {
		if ($setting <= 0) {
			return;
		}

		// Select all tasks that were due after_days days ago
		$sql = sprintf(SQL_GET_TASKS, date("Y-m-d"), ($setting * $int));
		$result = $this->DB->Query($sql);
		//  for each task
		if ($result) {
			foreach ($result as $value) {
				$SQL = sprintf(SQL_GET_EMAIL_SUBJECT_DETAILS, $value['ID']);
				$subjectdetails = $this->DB->QuerySingle($SQL);
				// get assigned users
				$users = NULL;
				$user_result = $this->DB->Query(sprintf(SQL_GET_TASK_ASSIGNED, $value['ID']));
				if ($user_result) {
					$count = 0;
					foreach ($user_result as $user_value) {
						$users[$count]['Email'] = $user_value['EmailAddress'];
						$users[$count]['Name'] = $user_value['FirstName'];
						$users[$count]['FullName'] = $user_value['FullName'];
						$count++;
					}
				}

				// send mail and cc if necessary
				//foreach user mail
				$url = url::build_url('projects', 'taskview', "taskid={$value['ID']}&projectid={$value['ProjectID']}");
				$date = Format::date($value['EndDate']);
				if ($users) {
					foreach ($users as $user) {
						$sent_array[] = $user['Email'];
						$mailer = new SMTPMail();
						$mailer->FromName = SYS_FROMNAME;
						$mailer->FromAddress = SYS_FROMADDR;
						$mailer->Subject = sprintf(MSG_TASK_EMAIL_REMIND_CC_SUBJECT, "- {$subjectdetails['ClientName']}: {$subjectdetails['ProjectName']}: {$subjectdetails['TaskName']}");

						$mailer->ToName = $user['FullName'];
						$mailer->ToAddress = $user['Email'];
						$mailer->Body = sprintf(MSG_TASK_EMAIL_REMIND_CC_BODY, $user['Name'], $value['Name'], $message, $date, $url);
						$mailer->Execute();
						unset($mailer);
					}
				}

				//if cc, mail owner
				if (Settings::get('CCTaskOwner') > 0) {
					$sql = sprintf(SQL_GET_TASK_OWNER, $value['ID']);
					$owner_result = $this->DB->QuerySingle($sql);
					if ($owner_result && !(in_array($owner_result['EmailAddress'], $sent_array))) {
						$mailer = new SMTPMail();
						$mailer->FromName = SYS_FROMNAME;
						$mailer->FromAddress = SYS_FROMADDR;
						$mailer->Subject = sprintf(MSG_TASK_EMAIL_REMIND_OWNER_SUBJECT, "- {$subjectdetails['ClientName']}: {$subjectdetails['ProjectName']}: {$subjectdetails['TaskName']}");

						$mailer->Priority = 1;
						$mailer->ToName = $owner['FullName'];
						$mailer->ToAddress = $owner['EmailAddress'];
						$mailer->Body = sprintf(MSG_TASK_EMAIL_REMIND_OWNER_BODY, $owner['FirstName'], $value['Name'], $message, $date, $url);
						$mailer->Execute();
						unset($mailer);
					}
				}

			} // end foreach
		} // end if
	}

	function MailProjectReports() {
		$url = url::build_url('reports');

		// Loop on all users that have reports in the tblProjectsReports table.
		// Send each user an email containing CSV attachments of their reports.
		$users = $this->DB->Query( sprintf( SQL_SELECT_PROJECT_REPORTS_USERS ) );
		if ( is_array( $users ) )
		{
			foreach ( $users as $user ) 
			{
				$mailer = new SMTPMail();
				$mailer->FromName = SYS_FROMNAME;
				$mailer->FromAddress = SYS_FROMADDR;
				$mailer->Subject = MSG_PROJECT_ANALYSIS_REPORT;
				$mailer->Priority = 1;
				$mailer->ToName = $user['FullName'];
				$mailer->ToAddress = $user['EmailAddress'];
				$mailer->Body = sprintf( MSG_PROJECT_REPORT_EMAIL_BODY, $user['FirstName'], $url );
				// Loop on all the project reports for this user, adding each CSV report as an attachment to the email.
				$reports = $this->DB->Query( sprintf( SQL_SELECT_PROJECT_REPORTS_FOR_USER, $user['UserID'] ) );
				if ( is_array( $reports ) && count( $reports ) > 0 )
				{
					foreach ( $reports as $report )
					{
						// Adjust start and end date periods.
						$startTime = strtotime( $report['StartDate'] . ' 00:00:00' );
						$adjustedStartTime = strtotime( "+{$report['DayShift']} day", $startTime );
						$adjustedStartDate = date( 'Y-m-d', $adjustedStartTime );

						$endTime = strtotime( $report['EndDate'] . ' 00:00:00' );
						$adjustedEndTime = strtotime( "+{$report['DayShift']} day", $endTime );
						$adjustedEndDate = date( 'Y-m-d', $adjustedEndTime );

						// Assumption: The report name does not contain characters that result 
						// in invalid filenames eg. a directory separator or carriage return.
						$filename = str_replace( ' ', '_', strtolower( trim( $report['Name'] ) ) ) . '.csv';
						$csv  = $this->ReportCSV( 'project', $report['ID'], $adjustedStartDate, $adjustedEndDate );

						$i = count( $mailer->Attachments );
						$mailer->Attachments[$i]['Filename'] = $filename; 
						$mailer->Attachments[$i]['Content'] = $csv;
						$mailer->Attachments[$i]['Content-Type'] = 'text/plain';
					}

					$mailer->Execute();
				}

				unset( $mailer );
			}
		}
	}

	function MailWorkReports() {
		$url = url::build_url('reports');

		// Loop on all users that have reports in the tblProjectsReports table.
		// Send each user an email containing CSV attachments of their reports.
		$users = $this->DB->Query( sprintf( SQL_SELECT_WORK_REPORTS_USERS ) );
		if ( is_array( $users ) )
		{
			foreach ( $users as $user ) 
			{
				$mailer = new SMTPMail();
				$mailer->FromName = SYS_FROMNAME;
				$mailer->FromAddress = SYS_FROMADDR;
				$mailer->Subject = MSG_WORK_ANALYSIS_REPORT;
				$mailer->Priority = 1;
				$mailer->ToName = $user['FullName'];
				$mailer->ToAddress = $user['EmailAddress'];
				$mailer->Body = sprintf( MSG_WORK_REPORT_EMAIL_BODY, $user['FirstName'], $url );

				// Loop on all the project reports for this user, adding each CSV report as an attachment to the email.
				$reports = $this->DB->Query( sprintf( SQL_SELECT_WORK_REPORTS_FOR_USER, $user['UserID'] ) );
				if ( is_array( $reports ) && count( $reports ) > 0 )
				{
					foreach ( $reports as $report )
					{
						// Adjust start and end date periods.
						$startTime = strtotime( $report['StartDate'] . ' 00:00:00' );
						$adjustedStartTime = strtotime( "+{$report['DayShift']} day", $startTime );
						$adjustedStartDate = date( 'Y-m-d', $adjustedStartTime );

						$endTime = strtotime( $report['EndDate'] . ' 00:00:00' );
						$adjustedEndTime = strtotime( "+{$report['DayShift']} day", $endTime );
						$adjustedEndDate = date( 'Y-m-d', $adjustedEndTime );

						// Assumption: The report name does not contain characters that result 
						// in invalid filenames eg. a directory separator or carriage return.
						$filename = str_replace( ' ', '_', strtolower( trim( $report['Name'] ) ) ) . '.csv';
						$csv = $this->ReportCSV( 'work', $report['ID'], $adjustedStartDate, $adjustedEndDate );

						$i = count( $mailer->Attachments );
						$mailer->Attachments[$i]['Filename'] = $filename; 
						$mailer->Attachments[$i]['Content'] = $csv;
						$mailer->Attachments[$i]['Content-Type'] = 'text/plain';
					}

					$mailer->Execute();
				}
			}
		}
	}

	function ReportCSV( $type = NULL, $reportID = NULL, $startDate = NULL, $endDate = NULL ) {
		$type = ( $type ) ? $type : Request::get( 'type' ); 
		$reportID = ( $reportID ) ? $reportID : Request::get( 'report' );

		include_once('system/classes/workreport.php');
		include_once('system/classes/projectreport.php');

		switch ( $type )
		{
			case 'project': $report = new ProjectReport($this->DB); break;
			case 'work':	$report = new WorkReport($this->DB); break;
		}

		$report->load( $reportID );
		if ( !empty( $startDate ) ) 
			$report->startDate = $startDate;
		if ( !empty( $endDate ) ) 
			$report->endDate = $endDate;
		$report->run();

		$filename = 'data.csv';  // Default filename
		if ( !empty( $report->name ) )
			$filename = str_replace( ' ', '_', strtolower( $report->name ) ) . '.csv';

		return $report->exportCSV();
	}

}

 
