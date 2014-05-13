<?php
// $Id$

class mod_reports extends Module {

	var $date_format = array('1' => 'yyyy-mm-dd', '2' => 'yyyy-dd-mm', '3' => 'dd-mm-yyyy', '4' => 'mm-dd-yyyy');
	var $StatusList = array(MSG_NA, MSG_PROPOSED, MSG_IN_PLANNING, MSG_IN_PROGRESS, MSG_ON_HOLD, MSG_COMPLETE, MSG_ARCHIVED, MSG_CANCELLED);

	function mod_reports() {
		$this->ModuleName = 'reports';
		$this->ModuleID = '26D3FABDA482E8016DF1D2B0874636AC';
		$this->RequireLogin = ( isset( $_GET['action'] ) && $_GET['action'] == 'noaction' ) ? 0 : 1;
		$this->Public = 0;
		parent::Module();
	}

	function main() {
			switch (Request::any('action')) {
			case 'analysis' :		 $this->Analysis();		  break;
			case 'timesheets' :	   $this->Timesheets();		break;
			case 'saveprojectreport': $this->SaveProjectReport(); break;
			case 'saveworkreport':	$this->SaveWorkReport();	break;
			case 'reportcsv':		 $this->ReportCSV();		 break;
			case 'reportcsvmyob':		 $this->ReportCSV(TRUE);		 break;
			case 'deletereport':	  $this->DeleteReport();	  break;
			case 'savedreports':	  $this->SavedReports();	  break;
			case 'noaction':		  break;
			default:				  $this->SavedReports();
		}
	}

	function SavedReports() {
		$modHeader = MSG_LIST;

		$this->CreateTabs('savedreports');

		// Listing of saved project reports.
		$tmpl = array();
		$tmpl['savedReports'] = '';
		$reports = $this->DB->Query( sprintf( SQL_SELECT_SAVED_REPORTS ) );
		if ( is_array( $reports ) )
		{ 
			$i=0;
			foreach ( $reports as $report )
			{
				$i++;
				list ( $date, $time ) = explode( ' ', $report['Date'] );
				$date = Format::date( $date, Settings::get('PrettyDateFormat') );
				$name = ( $report['Name'] == '' ) ? '(unnamed)' : $report['Name'];
				$type = ( $report['Type'] == 'work' ) ? MSG_WORK : MSG_PROJECT;

				switch ( $report['Frequency'] )
				{
					case 'N': $freq = MSG_NEVER;	   break;
					case 'W': $freq = MSG_WEEKLY;	  break;
					case 'F': $freq = MSG_FORTNIGHTLY; break;
					case 'M': $freq = MSG_MONTHLY;	 break;
				}

				$action = ( $report['Type'] == 'work' ) ? 'timesheets' : 'analysis';
				$url = "index.php?module=reports&action=$action&report={$report['ID']}";
				$csvUrl = "index.php?module=reports&action=reportcsv&type={$report['Type']}&report={$report['ID']}";
				$deleteUrl = "index.php?module=reports&action=deletereport&type={$report['Type']}&report={$report['ID']}";

				$tmpl['savedReports'] .= "<tr><td nowrap style=\"padding-top:5px;padding-bottom:5px;\"><a href=\"$url\">$name</a></td><td></td>";
				$tmpl['savedReports'] .= "<td nowrap>$type</td><td></td>";
				$tmpl['savedReports'] .= "<td nowrap>$date</a></td><td></td>";
				$tmpl['savedReports'] .= "<td nowrap>$freq</a></td><td></td>";
				$tmpl['savedReports'] .= "<td nowrap><a href=\"$csvUrl\">" . MSG_DOWNLOAD_CSV . "</a> | ";

				if ($report['Type'] == 'work') {
					$myobcsvUrl = "index.php?module=reports&action=reportcsvmyob&type={$report['Type']}&report={$report['ID']}";
					$tmpl['savedReports'] .= "<a href=\"$myobcsvUrl\">" . MSG_DOWNLOAD_CSV_MYOB . "</a> | ";
				}


				$tmpl['savedReports'] .= "<a href=\"$deleteUrl\">" . MSG_DELETE . "</td></tr>";
				if ($i < count($reports))
					$tmpl['savedReports'] .= $this->getTemplate( 'spacer' );
			}
			$this->setTemplate( 'saved_reports', $tmpl );
		}
		else
			$this->setTemplate( 'saved_reports_none', $tmpl );


		$this->setHeader( MSG_REPORTS );
		$this->setModule( $modHeader );
		$this->Render();
	}

	function Analysis() {
		$report = new ProjectReport($this->DB);

		$modHeader = MSG_PROJECT_ANALYSIS . ' ' .MSG_EDIT;
		$modAction[] = '<a href="javascript:submitForm();">'.MSG_SAVE.'</a>';

		$this->CreateTabs('analysis');
		$tmpl = array();

		// If there was a form submission, create a report dynamically.
		// Otherwise, load a report if an ID has been supplied.
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$report->id = NULL;
			$report->userID = $this->User->ID; 
			$report->name = NULL;

			$report->startDate = (strlen(Request::post('startdate')) > 5) ? Format::parse_date(Request::post('startdate')) : NULL;
			$report->endDate = (strlen(Request::post('enddate')) > 5) ? Format::parse_date(Request::post('enddate')) : NULL;
			$report->projects = (Request::post('projectids')) ? implode(',', Request::post('projectids')) : NULL;
			$report->clients = (Request::post('clientids')) ? implode(',', Request::post('clientids')) : NULL;
			$report->budget = Request::post('budget', Request::R_INT);
			$report->details = Request::post('details', Request::R_INT);
			$report->frequency = 'N';
			$report->created = date('Y-m-d');
			$report->period = Request::post('periodSelect');
			$report->sanitise(); // Clean up the data.
		} else {
			$reportID = Request::get( 'report' );
			if ($reportID > 0)
				$report->load($reportID);
				$this->Log('projectreport', $reportID, 'view', $report->name);
		}

		// Multiple select for projects.
		$tmpl['optSelectProject'] = '';
		$projectsArray = explode(',', $report->projects);
		$projectRows = $this->DB->Query(SQL_SELECT_PROJECTS);
		if ( is_array( $projectRows ) ) 
		{
			foreach ( $projectRows as $project )
			{
				$selected = ( in_array( $project['ID'], $projectsArray ) ) ? 'selected' : '';
				$tmpl['optSelectProject'] .= sprintf('<option value="%s" %s>%s - %s</option>', $project['ID'], $selected, $project['ClientName'], $project['Name']);
			}
		}

		// Multiple select for clients.
		$tmpl['optSelectClient'] = '';
		$clientsArray = explode(',', $report->clients);
		$clientRows = $this->DB->Query(SQL_SELECT_CLIENTS);
		if ( is_array( $clientRows ) ) 
		{
			foreach ( $clientRows as $client )
			{
				$selected = ( in_array( $client['ID'], $clientsArray ) ) ? 'selected' : '';
				$tmpl['optSelectClient'] .= sprintf('<option value="%s" %s>%s</option>', $client['ID'], $selected, $client['Name']);
			}
		}

		// Select box for project filter - currently not used or detected by the code.
		$filters = array('all' => MSG_ALL, '1' => MSG_ACTIVE, '0' => MSG_ARCHIVED);
		foreach ($filters as $k => $v)
		{
			$selected = ($report->filter == $k) ? 'selected' : '';
			$tmpl['optSelectFilter'] = sprintf('<option value="%s" %s>%s</option>', $k, $selected, $v);
		}

		// Check or disable the budget and details checkboxes as appropriate.
		$tmpl['detailsAttr'] = ( $report->details > 0 ) ? 'checked="checked"' : '';
		if ( $this->User->HasModuleItemAccess( 'budget', CU_ACCESS_ALL, CU_ACCESS_READ ) ) 
			$tmpl['budgetAttr'] = ( $report->budget > 0 ) ? 'checked="checked"' : '';
		else 
			$tmpl['budgetAttr'] = 'disabled="true"';

		// {{{ Populate range drop down.
		$tmpl['periodOptions'] = '<option value="">{MSG_SELECT_PERIOD}...</option>';
		$dow = ( date( 'w' ) == 0 ) ? 7 : date( 'w' );  // Emulate date( 'N' ) for PHP versions < 5.1.0

		$sd = Format::date( date( 'Y-m-d', mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - $dow - 6, date( 'Y' ) ) ), FALSE, FALSE);
		$ed = Format::date( date( 'Y-m-d', mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - $dow, date( 'Y' ) ) ), FALSE, FALSE);
		$selected = ( $reportPeriod == 'lastweek' ) ? 'selected' : '';
		$tmpl['periodOptions'] .= '<option value="lastweek" sd="'.$sd.'" ed="'.$ed.'" '.$selected.'>{MSG_LAST_WEEK}</option>';

		$sd = Format::date( date( 'Y-m-d', mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - $dow + 1, date( 'Y' ) ) ), FALSE, FALSE);
		$ed = Format::date( date( 'Y-m-d', mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - $dow + 7, date( 'Y' ) ) ), FALSE, FALSE);
		$selected = ( $reportPeriod == 'thisweek' ) ? 'selected' : '';
		$tmpl['periodOptions'] .= '<option value="thisweek" sd="'.$sd.'" ed="'.$ed.'" '.$selected.'>{MSG_THIS_WEEK}</option>';

		$sd = Format::date( date( 'Y-m-d' ), FALSE, FALSE);
		$ed = Format::date( date( 'Y-m-d' ), FALSE, FALSE);
		$selected = ( $reportPeriod == 'today' ) ? 'selected' : '';
		$tmpl['periodOptions'] .= '<option value="today" sd="'.$sd.'" ed="'.$ed.'" '.$selected.'>{MSG_TODAY}</option>';

		$sd = Format::date( date( 'Y-m-d', mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - 1, date( 'Y' ) ) ), FALSE, FALSE);
		$ed = Format::date( date( 'Y-m-d', mktime( 0, 0, 0, date( 'm' ), date( 'd' ) - 1, date( 'Y' ) ) ), FALSE, FALSE);
		$selected = ( $reportPeriod == 'yesterday' ) ? 'selected' : '';
		$tmpl['periodOptions'] .= '<option value="yesterday" sd="'.$sd.'" ed="'.$ed.'" '.$selected.'>{MSG_YESTERDAY}</option>';

		$sd = Format::date( date( 'Y-m-01' ), FALSE, FALSE);
		$ed = Format::date( date( 'Y-m-t' ), FALSE, FALSE);
		$selected = ( $reportPeriod == 'thismonth' ) ? 'selected' : '';
		$tmpl['periodOptions'] .= '<option value="thismonth" sd="'.$sd.'" ed="'.$ed.'" '.$selected.'>{MSG_THIS_MONTH}</option>';

		$sd = Format::date( date( 'Y-m-01', mktime( 0, 0, 0, date( 'm' ) - 1, date( 'd' ), date( 'Y' ) ) ), FALSE, FALSE);
		$ed = Format::date( date( 'Y-m-t', mktime( 0, 0, 0, date( 'm' ) - 1, date( 'd' ), date( 'Y' ) ) ), FALSE, FALSE);
		$selected = ( $reportPeriod == 'lastmonth' ) ? 'selected' : '';
		$tmpl['periodOptions'] .= '<option value="lastmonth" sd="'.$sd.'" ed="'.$ed.'" '.$selected.'>{MSG_LAST_MONTH}</option>';

		$sd = Format::date( date( 'Y-01-01' ), FALSE, FALSE);
		$ed = Format::date( date( 'Y-12-31' ), FALSE, FALSE);
		$selected = ( $reportPeriod == 'thisyear' ) ? 'selected' : '';
		$tmpl['periodOptions'] .= '<option value="thisyear" sd="'.$sd.'" ed="'.$ed.'" '.$selected.'>{MSG_THIS_YEAR}</option>';

		$sd = Format::date( date( 'Y-01-01', mktime( 0, 0, 0, 1, 1, date( 'Y' ) - 1 ) ), FALSE, FALSE);
		$ed = Format::date( date( 'Y-12-31', mktime( 0, 0, 0, 1, 1, date( 'Y' ) - 1 ) ), FALSE, FALSE);
		$selected = ( $reportPeriod == 'lastyear' ) ? 'selected' : '';
		$tmpl['periodOptions'] .= '<option value="lastyear" sd="'.$sd.'" ed="'.$ed.'" '.$selected.'>{MSG_LAST_YEAR}</option>';
				
		$tmpl['date_format'] = $this->date_format[Settings::get('DateFormat')];
		$tmpl['startDate'] = Format::date($report->startDate, FALSE, FALSE);
		$tmpl['endDate'] = Format::date($report->endDate, FALSE, FALSE);
		$tmpl['reportHeader'] = '';
		$tmpl['reportData'] = '';

		if ( $report->readyToRun )
		{
			$hasBudgetRead = $this->User->HasModuleItemAccess('budget', CU_ACCESS_ALL, CU_ACCESS_READ);
			$showBudgetCols = ($report->budget > 0 && $hasBudgetRead);

			// Report column titles.
			$template = ($showBudgetCols) ? 'project_analysis/columns_budget' : 'project_analysis/columns';
			$tmpl['reportHeader'] = $this->getTemplate($template);

			$data = $report->run();
			foreach ($data as $row)
			{
				if ($row['type'] == 'project')
				{
					$template = ($showBudgetCols) ? 'project_analysis/item_budget' : 'project_analysis/item';
					$tmpl['reportData'] .= $this->getTemplate($template, $row);
				}
				else
				{
					$template = ($showBudgetCols) ? 'project_analysis/item_detail_budget' : 'project_analysis/item_detail';
					$tmpl['reportData'] .= $this->getTemplate($template, $row);
				}

				$tmpl['reportData'] .= $this->getTemplate('spacer');
			}
		}

		// Populate the visible form save options on the right.
		$reportTmpl = array();
		$reportTmpl['reportName'] = $report->name;
		$options = array( 'N' => MSG_NEVER, 'W' => MSG_WEEKLY, 'F' => MSG_FORTNIGHTLY, 'M' => MSG_MONTHLY );
		$reportTmpl['frequencyOptions'] = '';
		foreach( $options as $k => $v )
		{
			$selected = ( $k == $frequency ) ? 'selected="selected"' : '';
			$reportTmpl['frequencyOptions'] .= "<option value=\"$k\" $selected>$v</option>";
		}

		// Populate the hidden form save options on the right.
		$reportTmpl['period'] = $report->period;
		$reportTmpl['startDate'] = $report->startDate; 
		$reportTmpl['endDate'] = $report->endDate;
		$reportTmpl['clients'] = $report->clients;
		$reportTmpl['projects'] = $report->projects;
		$reportTmpl['budget'] = $report->budget; 
		$reportTmpl['details'] = $report->details; 
		$reportTmpl['reportID'] = $report->id; 
		$tmpl['txtProjectSave'] = $this->getTemplate( 'project_analysis/report_save', $reportTmpl );

		$this->setTemplate('project_analysis/header', $tmpl);

		$this->setModule($modHeader, $modAction);
		$this->Render();
	}

	// view a work report. note that this deals with both form submission, as well as saved reports.
	function Timesheets() 
	{
		$modHeader = MSG_WORK_ANALYSIS . ' ' .MSG_EDIT;
		$modAction[] = '<a href="javascript:submitForm();">'.MSG_SAVE.'</a>';

		$this->CreateTabs( 'timesheets' );
		$tmpl = array();
		$show_report = FALSE;
		// If there was a form submission, create a report dynamically.
		// Otherwise, load a report if an ID has been supplied.
		if ($_SERVER['REQUEST_METHOD'] == 'POST') 
		{
			$report = new WorkReport(null);
			$report->UserID = $this->User->ID; 
			$report->Name = NULL;
			$report->StartDate = (strlen(Request::post('startdate')) > 5) ? Format::parse_date(Request::post('startdate')) : NULL;
			$report->EndDate = (strlen(Request::post('enddate')) > 5) ? Format::parse_date(Request::post('enddate')) : NULL;
			$report->Projects = (Request::post('projects')) ? implode(',', Request::post('projects')) : NULL;
			$report->Clients = (Request::post('clients')) ? implode(',', Request::post('clients')) : NULL;
			$report->Users = (Request::post('users')) ? implode(',', Request::post('users')) : NULL;
			$report->Frequency = 'N';
			$report->Created = date('Y-m-d');
			$report->Period = Request::post('periodSelect');
			$report->WithOtherItems = Request::post('other_items');
			$show_report = TRUE;
			$report->sanitise(); // Clean up the data.
		} else 
		{
			$report = new WorkReport(Request::get( 'report' ));
			$this->Log('workreport', Request::get( 'report' ), 'view', $report->name);
			if ($report->exists)
			{
				$show_report = TRUE;
			}
		}

		// Multiple select for users.
		$tmpl['userOptions'] = '';
		$usersArray = explode(',', $report->Users);

		$userRows = $this->DB->Query( sprintf( SQL_SELECT_USERS ) );
		if ( is_array( $userRows ) ) 
		{
			foreach ( $userRows as $user )
			{
				$name = $user['FirstName'] . ' ' . $user['LastName'];
				$selected = ( in_array( $user['ID'], $usersArray ) ) ? 'selected' : '';
				$tmpl['userOptions'] .= sprintf( '<option value="%s" %s>%s</option>', $user['ID'], $selected, $name );
			}
		}

		// Multiple select for clients.
		$tmpl['clientOptions'] = '';
		$clientsArray = explode(',', $report->Clients);
		$clientRows = $this->DB->Query( sprintf( SQL_SELECT_CLIENTS ) );
		if ( is_array( $clientRows ) ) 
		{
			foreach ( $clientRows as $client )
			{
				$selected = ( in_array( $client['ID'], $clientsArray ) ) ? 'selected' : '';
				$tmpl['clientOptions'] .= sprintf( '<option value="%s" %s>%s</option>', $client['ID'], $selected, $client['Name'] );
			}
		}

		// Multiple select for projects.
		$tmpl['projectOptions'] = '';
		$projectsArray = explode(',', $report->Projects);
		$projectRows = $this->DB->Query( sprintf( SQL_SELECT_PROJECTS ) );
		if ( is_array( $projectRows ) ) 
		{
			foreach ( $projectRows as $project )
			{
				$selected = ( in_array( $project['ID'], $projectsArray ) ) ? 'selected' : '';
				$tmpl['projectOptions'] .= sprintf( '<option value="%s" %s>%s - %s</option>', $project['ID'], $selected, $project['ClientName'], $project['Name'] );
			}
		}

		// Populate range drop down.
		$tmpl['periodOptions'] = '<option value="">{MSG_SELECT_PERIOD}...</option>';
		$dow = ( date( 'w' ) == 0 ) ? 7 : date( 'w' );  // Emulate date( 'N' ) for PHP versions < 5.1.0

		foreach($report->period_options as $option => $language_string)
		{
			$sd = $report->getStartDate($option);
			$ed = $report->getEndDate($option);
			$selected = ( $report->Period == $option ) ? 'selected="selected"' : '';
			$tmpl['periodOptions'] .= '<option value="' . $option . '" sd="'.$sd.'" ed="'.$ed.'" '.$selected . '>' . $language_string . '</option>';
		}

		$tmpl['lblTimesheetStart'] = MSG_SELECT_PERIOD_RANGE;
		$tmpl['dateFormat'] = $this->date_format[Settings::get('DateFormat')];
		$tmpl['startDate'] = Format::date($report->StartDate, FALSE, FALSE);
		$tmpl['endDate'] = Format::date($report->EndDate, FALSE, FALSE);
		$tmpl['today'] = Format::date(date('Y-m-d'), FALSE, FALSE);
		$tmpl['lastweek'] = Format::date(date('Y-m-d', strtotime('1 week ago')), FALSE, FALSE);
		$tmpl['WithOtherItemsChecked'] = $report->WithOtherItems ? 'checked=yes' : ''; 

		// Save report form
		$ftmpl = array();
		$ftmpl['period'] = $report->Period;
		$ftmpl['startDate'] = $report->StartDate;
		$ftmpl['endDate'] = $report->EndDate; 
		$ftmpl['report'] = $report->ID;
		$ftmpl['reportName'] = $report->Name;
		$ftmpl['users'] = $report->Users; 
		$ftmpl['clients'] = $report->Clients; 
		$ftmpl['projects'] = $report->Projects; 
		$ftmpl['WithOtherItems'] = $report->WithOtherItems ? '1' : '0'; 

		$foptions = array( 'N' => MSG_NEVER, 'W' => MSG_WEEKLY, 'F' => MSG_FORTNIGHTLY, 'M' => MSG_MONTHLY );
		$ftmpl['frequencyOptions'] = '';
		foreach( $foptions as $k => $v )
		{
			$selected = ( $k == $report->Frequency ) ? 'selected' : '';
			$ftmpl['frequencyOptions'] .= "<option value=\"$k\" $selected>$v</option>";
		}

		$this->setTemplate( 'work_analysis/header', $tmpl );

		if ( $show_report )
		{
			$tmplHeader['lblHoursWorked'] = str_replace( ' ', '&nbsp;', MSG_HOURS_WORKED );
			$this->setTemplate( 'work_analysis/columns', array_merge($tmplHeader, $ftmpl) );

			$userTotalHours = array();
			$lastUserID = NULL;
			$report->run();
			foreach ( $report->rows as $row )
			{	
				if ( $lastUserID != NULL && $lastUserID != $row['UserID'] )
				{
					$tmpl = array('total' => MSG_SUBTOTAL, 'HoursWorked' => $userTotalHours[$lastUserID]);
					$this->setTemplate( 'work_analysis/total', $tmpl );
					$this->setTemplate( 'spacer' );
				}

				$tmpl = $row;
				// Convert date from short format to pretty format. 
				$tmpl['date'] = Format::date( $row['Date'], Settings::get('PrettyDateFormat') ); 
				$this->setTemplate( 'work_analysis/item', $tmpl );

				$userTotalHours[$row['UserID']] += $row['HoursWorked'];
				$lastUserID = $row['UserID'];
			}

			if ( $lastUserID != NULL )
			{
				$tmpl = array('total' => MSG_SUBTOTAL, 'HoursWorked' => $userTotalHours[$lastUserID]);
				$this->setTemplate( 'work_analysis/total', $tmpl );
				$this->setTemplate( 'spacer' );
			}
			
			$tmpl = array('total' => MSG_TOTAL, 'HoursWorked' => array_sum($userTotalHours));
			$this->setTemplate( 'work_analysis/total', $tmpl );

			$others = $report->get_other_items();
			if ($others > 0)
			{
				$this->includeTemplate('work_analysis/other_header', array(), TRUE);
				$total_charge = 0;
				
				foreach($report->get_other_items() as $invoice_item_other)
				{
					$this->includeTemplate('work_analysis/other_item', array('item' => $invoice_item_other), TRUE);
					$total_charge += $invoice_item_other->Charge;
				}

				$this->includeTemplate('work_analysis/other_footer', array('total' => $total_charge), TRUE);
			}
			
			$this->setTemplate( 'work_analysis/footer');
		}

		$this->setModule( $modHeader, $modAction );
		$this->Render();
	}

	function SaveProjectReport() 
	{
		$period	 = Request::post( 'period' );
		$startDate  = Request::post( 'startdate' );
		$endDate	= Request::post( 'enddate' );
		$clients	= Request::post( 'clients' );
		$projects   = Request::post( 'projects' );
		$budget	 = Request::post( 'budget' );
		$details	= Request::post( 'details' );
		$reportID   = Request::post( 'report' );
		$reportName = trim( Request::post( 'reportname' ) );
		$frequency  = Request::post( 'frequency' );

		$sqlStartDate = Utils::convert_form_to_sql_date(Request::post( 'startdate' ));
		$sqlEndDate = Utils::convert_form_to_sql_date(Request::post( 'enddate' ));

		if ( isset( $reportID ) && is_numeric( $reportID ) ) 
			$sql = sprintf( SQL_UPDATE_PROJECT_REPORT, $this->User->ID, $reportName, $sqlStartDate, $sqlEndDate, $clients, $projects, $budget, $details, $frequency, $period, $reportID );
		else
			$sql = sprintf( SQL_INSERT_PROJECT_REPORT, $this->User->ID, $reportName, $sqlStartDate, $sqlEndDate, $clients, $projects, $budget, $details, $frequency, $period );

		$this->DB->Execute( $sql );

		Response::redirect( 'index.php?module=reports&action=savedreports' );
	}

	function SaveWorkReport() 
	{
		$report = new WorkReport(Request::post( 'report' ));
		$report->update_from_postdata(array(
			'Name' => 'reportname',
			'Users' => 'users',
			'Clients' => 'clients',
			'Projects' => 'projects',
			'Frequency' => 'frequency',
			'Period' => 'period',
			'WithOtherItems' => 'WithOtherItems',
		));
		
		$report->UserID = CopperUser::current()->ID;
		$report->StartDate = Utils::convert_form_to_sql_date(Request::post( 'startdate' ));
		$report->EndDate = Utils::convert_form_to_sql_date(Request::post( 'enddate' ));

		if ( ! $report->exists )
		{
			$report->Created = DB::now();
		}
		
		$report->commit();
		Response::redirect( 'index.php?module=reports&action=savedreports' );
	}

	function ReportCSV($myob = FALSE) {
		$type = Request::get( 'type' ); 
		$reportID = Request::get( 'report' );
		$filename = 'data.csv';  // Default filename

		switch ( $type )
		{
			case 'project': 
				$report = new ProjectReport($this->DB); 
				$report->load( $reportID );
				if ((int)$report->id < 1) 
				{
					die(MSG_INVALID_REPORT);
				}
				
				if ( !empty( $report->name ) ) 
				{
					$filename = Format::filename_safe($report->name ) . '.csv';
				}
				
				break;
			case 'work':
				$report = new WorkReport($reportID); 
				if ((int)$report->ID < 1) 
				{
					die(MSG_INVALID_REPORT);
				}
				
				if ( $report->Name ) 
				{
					$filename = Format::filename_safe($report->Name ) . '.csv';
				}
				break;
			default: die(MSG_INVALID_REPORT);
		}

		$report->run();

		header( "Content-type: text/plain" );
		header( "Content-disposition: attachment; filename=$filename" );
		echo $report->exportCSV($myob);
	}

	function DeleteReport() {
		$type = Request::get( 'type' );
		$reportID = Request::get( 'report' );

		switch ( $type )
		{
			case 'project': $this->DB->Execute( sprintf( SQL_DELETE_PROJECT_REPORT, $reportID ) ); break;
			case 'work': $this->DB->Execute( sprintf( SQL_DELETE_WORK_REPORT, $reportID ) ); break;
		}

		Response::redirect( 'index.php?module=reports' );
		}

	function CreateTabs($active) {
		$tmpl['lblSavedReportsTab'] = $this->AddTab(MSG_SAVED_REPORTS, 'savedreports', $active);
		$tmpl['lblProjectAnalysisTab'] = $this->AddTab(MSG_PROJECT_ANALYSIS, 'analysis', $active);
		$tmpl['lblWorkAnalysisTab'] = $this->AddTab(MSG_WORK_ANALYSIS, 'timesheets', $active);
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
 
