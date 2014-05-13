<?php
// $Id$
class mod_projects extends Module
{
	var $date_format = array('1' => 'yyyy-mm-dd', '2' => 'yyyy-dd-mm', '3' => 'dd-mm-yyyy', '4' => 'mm-dd-yyyy');
	var $StatusList = array(MSG_NA, MSG_PROPOSED, MSG_IN_PLANNING, MSG_IN_PROGRESS, MSG_ON_HOLD, MSG_COMPLETE, MSG_ARCHIVED, MSG_CANCELLED);
	var $PriorityList = array(MSG_PRIORITY_LOW, MSG_PRIORITY_NORMAL, MSG_PRIORITY_HIGH);
	var $TaskPriorityList = array('1' => MSG_PRIORITY_LOW, '2' => MSG_PRIORITY_NORMAL, '3' => MSG_PRIORITY_HIGH);

	const IMPORT_MSPROJECT = 0;
	const IMPORT_COPPER = 1;

	const DEFAULT_PDFS_FOLDER = '/assets/pdfs/';

	function mod_projects() {
		$this->ModuleName	 = 'projects';
		$this->RequireLogin = 1;
		$this->Public		 = 0;
		parent::Module();
	}

	function main() {
		switch (Request::any('action'))
		{
			case 'contactlist':	  $this->ContactList(); break;
			case 'saveinvoice':	  $this->SaveInvoice(); break;
			case 'budgets':		  $this->Budgets(); break;
			case 'ajaxaddotheritem': $this->AjaxAddOtherItem(); break;
			case 'ajaxgetinvoicerowitems':  $this->AjaxGetInvoiceRowItems(); break;
			case 'ajaxgetexistinginvoice': $this->AjaxGetExistingInvoice(); break;

			case 'tasklist':			 $this->TaskList(); break;
			case 'taskmoveup':		   $this->TaskMoveUp(); break;
			case 'taskmovedown':		 $this->TaskMoveDown(); break;
			case 'tasknew':			  $this->TaskNew(); break;
			case 'taskedit':			 $this->TaskEdit(); break;
			case 'taskcopy':			 $this->TaskEdit(); break;
			case 'taskmove':			 $this->TaskMove(); break;
			case 'taskmovesave':		 $this->TaskMoveSave(); break;
			case 'tasksave':			 $this->TaskSave(); break;
			case 'taskdel':			  $this->TaskDelete(); break;
			case 'taskview':			 $this->ProjectView(); break;
			case 'taskcomment':		  $this->TaskComment(); break;
			case 'taskcommentedit':	  $this->ProjectView(); break;
			case 'taskcommitmentdata':   $this->TaskCommitmentData(); break;
			case 'taskcommitmentsave':   $this->TaskCommitmentSave(); break;
			case 'taskdependencyadd':	$this->TaskDependencyAdd(); break;
			case 'taskdependencyremove': $this->TaskDependencyRemove(); break;
			case 'deletecomment':		$this->DeleteComment(); break;

			case 'filelist':	 $this->FileList(); break;

			case 'emaillist':	$this->EmailList(); break;
			case 'ajaxemailview':	$this->AjaxEmailView(); break;
			case 'saveemailaseml':  $this->SaveEmailAsEML(); break;
			case 'deleteemail':  $this->DeleteEmail(); break;
			case 'popup':  $this->ProjectPopup(); break;
			case 'view':   $this->ProjectView(); break;
			case 'new':	$this->ProjectNew(); break;
			case 'edit':   $this->ProjectEdit(); break;
			case 'copy':   $this->ProjectEdit(); break;
			case 'save':   $this->ProjectSave(); break;
			case 'delete': $this->ProjectDelete(); break;

			case 'gantt':		  $this->GanttChart(); break;
			case 'ganttdata':	  $this->GanttData(); break;
			case 'ganttdataprint': $this->GanttDataPrint(); break;
			case 'ganttsave':	  $this->GanttSave(); break;

			case 'import':			$this->Import(); break;
			case 'importxml':		 $this->ImportXML(); break;
			case 'importbasecamp':	$this->ImportBasecamp(); break;
			case 'importbasecampxml': $this->ImportBasecampXML(); break;
			case 'exportxml':		 $this->ExportXML(); break;
			case 'invoicepdf':		$this->InvoicePDF(); break;

			case 'ajaxclientprojectlist':	$this->AjaxClientProjectList(); break;
			case 'ajaxtasklist':			 $this->AjaxTaskList(); break;
			case 'ajaxtasklist_project':	 $this->TaskList(); break;
			case 'ajaxtaskview':			 $this->AjaxTaskView(); break;
			case 'ajaxtaskedit':			 $this->AjaxTaskEdit(); break;
			case 'ajaxtaskunedit':		   $this->AjaxTaskUnedit(); break;
			case 'ajaxtaskcopy':			 $this->AjaxTaskCopy(); break;
			case 'ajaxaddrelatedproject':	$this->AjaxAddRelatedProject(); break;
			case 'ajaxremoverelatedproject': $this->AjaxRemoveRelatedProject(); break;
			case 'ajaxaddaccess':			$this->AjaxAddAccess(); break;
			case 'ajaxremoveaccess':		 $this->AjaxRemoveAccess(); break;
			case 'ajaxsavetaskorder':		$this->AjaxSaveTaskOrder(); break;
			case 'deleteotheritem':		  $this->DeleteOtherItem(); break;
			case 'ajaxnewinvoice':		   $this->AjaxNewInvoice(); break;

			case 'ajaxdeleteinvoice':		$this->AjaxDeleteInvoice(); break;
			case 'ajaxupdateinvoice':		$this->AjaxUpdateInvoice(); break;
			case 'ajaxremoveresource':	   $this->AjaxRemoveResource(); break;
			case 'ajaxaddresource':		  $this->AjaxAddResource(); break;
			
			// new SW methods.
			case 'ajaxupdatetasktree': $this->update_task_tree(); break;
			case 'ajaxupdatefiletree': $this->update_file_tree(); break;
			case 'ajaxcreateinvoice': $this->ajax_create_invoice(); break;

			default: $this->ProjectList();
		}
	}

	public function update_task_tree() {
		$tree = Request::post('treedata', Request::R_ARRAY);
		// the tree is like so:
		// treedata[0] = array('attributes' => attribute array (including id), 'data' => data array, 'children' => children array);
		// we will need up update recursively.

		DB::begin_transaction();
		$counter = 0;

		if (array_key_exists('attributes', $tree))
		{
			// single element at root
			$counter = $this->update_task_node($tree, 0, $counter);
		} else {
			foreach ($tree as $node)
			{
				$counter = $this->update_task_node($node, 0, $counter);
			}
		}

		DB::commit_transaction();
	}
	
	private function update_task_node($node, $depth, $counter)
	{
		// first, we make sure tha the node we are going to update is a task node, not a wrapper node.
		$match_count = preg_match('/task_([0-9]+)/i', $node['attributes']['id'], $matches);
		if ($match_count == 1)
		{
			$id = $matches[1];
			$t = new Task($id);
			$t->Sequence = $counter;
			$t->Indent = $depth;
			$t->commit(FALSE); // don't update activity for reordering.
			$counter++;
		}

		if (isset($node['children']) && is_array($node['children'])) {
			foreach($node['children'] as $child) {
				$counter = $this->update_task_node($child, $depth + 1, $counter);
			}
		}

		return $counter;
	}

	function update_file_tree() {
		$tree = Request::post('treedata', Request::R_ARRAY);
		// the tree is like so:
		// treedata[0] = array('attributes' => attribute array (including id), 'data' => data array, 'children' => children array);
		// we will need up update recursively.
		
		DB::begin_transaction();

		if (array_key_exists('attributes', $tree))
		{
			// single element at root
			$this->update_file_node($tree, null);
		} else {
			foreach ($tree as $node)
			{
				$this->update_file_node($node, null);
			}
		}

		DB::commit_transaction();
	}

	function update_file_node($node, $parent) {
		// first, lets see if it's a folder. 
		$match_count = preg_match('/folder_([0-9]+)/i', $node['attributes']['id'], $matches);
		if ($match_count == 1)
		{
			$id = $matches[1];
			$f = new Folder($id);
			$f->ParentID = ($parent == null) ? 0 : $parent;
			$f->commit(); 
			
			// okay it's a folder, so recurse for child folders / files 
			if (isset($node['children']) && is_array($node['children'])) {
				foreach($node['children'] as $child) {
					$this->update_file_node($child, $f->ID);
				}
			}
		} else {
			// hrmm, maybe it's a file.
			$match_count = preg_match('/file_([0-9]+)/i', $node['attributes']['id'], $matches);
			if ($match_count == 1)
			{
				$id = $matches[1];
				$f = new File($id);
				$f->Folder = ($parent == null) ? 0 : $parent;
				$f->commit(); 
			}	
		}
		
	}

	function AjaxRemoveResource() {
		$id = Request::get('id', Request::R_INT);
		$taskid = Request::get('taskid', Request::R_INT);
		$taskResourceDayInsertSQL = sprintf(SQL_UNASSIGN_TASK_RESOURCE,$taskid,$id); 
		$success = $this->DB->Execute($taskResourceDayInsertSQL); 
			echo("{success:$success}");
	}

	function AjaxAddResource() {
		$id = Request::get('id', Request::R_INT);
		$taskid = Request::get('taskid', Request::R_INT);
		$taskResourceDayInsertSQL = sprintf(SQL_ASSIGN_RESOURCE,$taskid,$id);
		$this->DB->Execute($taskResourceDayInsertSQL);

		// Get resource details
		$tmpl['txtID'] = $id;
		$tmpl['taskID'] = $taskid;
		$tmpl['txtName'] = $this->DB->ExecuteScalar(sprintf(SQL_GET_RESOURCE_NAME,$id));
		$this->setTemplate('resource_item', $tmpl);
		$this->RenderOnlyContent();
	}

	function AjaxUpdateInvoice(){
		if (!$this->User->HasUserItemAccess($this->ModuleName, $projectID, CU_ACCESS_WRITE))
		{
			$this->ThrowError(2001);
			return;
		}
		$invoiceId = Request::post('id');
		$name = Request::post('invoiceName');
		$status = Request::post('invoiceStatus');
		$due = Request::post('invoiceDue');

		$sql = sprintf(SQL_UPDATE_INVOICE_SIMPLE, $name, $status, $due, $invoiceId);
		$state = $this->DB->Execute($sql);
		if($state){
			echo('{success:1}');
		} else {
			echo('{success:0}');
		}

	}

	function AjaxDeleteInvoice(){
		if (!$this->User->HasUserItemAccess($this->ModuleName, $projectID, CU_ACCESS_WRITE))
		{
			$this->ThrowError(2001);
			return;
		}
		$invoiceId = Request::get('id', Request::R_INT);
		$sql = sprintf(SQL_DELETE_INVOICE, $invoiceId);
		$state = $this->DB->Execute($sql);

		$sql =  sprintf(SQL_DELETE_INVOICE_ITEMS, $invoiceId);
		$state = $this->DB->Execute($sql);

		echo("{success:1}");


	}

	function AjaxNewInvoice(){
		$projectId = Request::post('projectid', Request::R_INT);
		$this->User->requireHasUserItemAccess($this->ModuleName, $projectId, CU_ACCESS_WRITE);
		
		$data = Request::post('data');
		$project = new Project($projectId);
		$quote = Request::post('isquote');

		// update the invoice first.
		// seriously. fuck copper's dependance on magic quotes. fuck it in the ear.
		$info = json_decode(stripcslashes(Request::post('invoice_info')));
		$invoice = new Invoice(Request::post('invoice_id', Request::R_INT));

		// don't update if it already exists. This is to deal with crappy javascript.
		if ( ! $invoice->exists)
		{
			$invoice->Quote = $quote;
		}

		$invoice->Due = $info->due;
		$invoice->Title = $info->title;
		$invoice->Status = $info->status;
		$invoice->commit();
		
		if ( isset( $data ) )
		{
			$invoiceId = $invoice->ID;
			$jsonData = json_decode(stripcslashes($data));
			// now have tasks and other items to be included in the invoice. loop and save.
			foreach($jsonData->tasks as $task)
			{
				$actual_task = new Task($task->id);
				$ii = new InvoiceItem();
				$ii->Amount = $task->amount;
				$ii->TaskID = $task->id;
				$ii->InvoiceID = $invoice->ID;
				$ii->TaskName = $actual_task->Name;
				$ii->TaskDescription = $actual_task->Description;
				$ii->commit();
			}

			foreach($jsonData->other as $iio)
			{
				$actual_item = new InvoiceItemOther($iio->id);
				$ii = new InvoiceItem();
				$ii->Amount = $iio->amount;
				$ii->TaskID = null;
				$ii->InvoiceID = $invoice->ID;
				$ii->TaskName = $actual_item->TaskName;
				$ii->AdditionalID = $actual_item->ID;
				$ii->commit();
			}
		} 
		
		// hokay, now we want to return the row line, so we can replace the stuff that was there.
		Response::addToJavascript('date_format', $this->date_format[Settings::get('DateFormat')]);
		$this->includeTemplate('budgets/invoice_row', array('invoice' => $invoice, 'project' => $project), TRUE);
		$this->RenderOnlyContent();
	}

	function DeleteOtherItem(){
		$projectID = Request::get('projectid', Request::R_INT);
		$otherItemId = Request::get('id', Request::R_INT);

		if (!$this->User->HasUserItemAccess($this->ModuleName, $projectID, CU_ACCESS_WRITE))
		{
			$this->ThrowError(2001);
			return;
		}

		$sql = sprintf(SQL_DELETE_OTHER_ITEM, $otherItemId);
		$success = $this->DB->Execute($sql);
		echo("{success:$success}");
	}

	function AjaxAddOtherItem(){
		/* have to define this _Before we use it, stupid */
		$projectID = Request::get('projectid', Request::R_INT);
		$this->User->requireHasUserItemAccess($this->ModuleName, $projectID, CU_ACCESS_WRITE);

		$oi = new InvoiceItemOther(null);
		$oi->TaskName = Request::get('task');
		$oi->ProjectID = $projectID;
		$oi->Budget = Request::get('budget');
		$oi->Quantity = Request::get('est');
		$oi->Logged = Request::get('logged');
		$oi->Cost = Request::get('cost');
		$oi->Charge = Request::get('charge');
		$oi->commit();

		$data = array('item' => $oi);

		if (Request::any('ajax'))
		{
			$ar = new AjaxResponse();
			$ar->html = $this->includeTemplate('budgets/overall_other_item', $data);
			$ar->out();
		} else {
			$this->includeTemplate('budgets/other_item', $data, TRUE);
			$this->RenderOnlyContent();
		}
	}

	private function get_pdf_type($setting_name, $fallback_name)
	{
		return Settings::get($setting_name) 
			? Upload::get_full_storage_path() . '/' .  Settings::get($setting_name)
			: realpath(CU_SYSTEM_PATH . '../' . self::DEFAULT_PDFS_FOLDER) . '/' . $fallback_name;
	}

	function InvoicePDF() {
		$billid = Request::get('billid', Request::R_INT);
		//get projectid from bill
		$projectid = $this->DB->ExecuteScalar(sprintf(SQL_GET_PROJECTID_FROM_BILL, $billid));
		$quote = Request::get('quote');
		if ($this->User->HasUserItemAccess($this->ModuleName, $projectid, CU_ACCESS_WRITE) && $this->User->HasModuleItemAccess('budget', CU_ACCESS_ALL, CU_ACCESS_READ)) {
			$nameWidth = 44;
			$descWidth = 99;
			$amountWidth = 33;

			define('FPDF_FONTPATH','system/fonts/');
			$pdf= new fpdi();

			if ($quote)
			{
				if (Settings::get('TaxRate'))
				{
					$file = $this->get_pdf_type('quote', 'Quote.pdf');
				} else {
					$file = $this->get_pdf_type('quote_notax', 'Quote-notax.pdf');
				}
			} else {
				if (Settings::get('TaxRate'))
				{
					$file = $this->get_pdf_type('invoice', 'Invoice.pdf');
				} else {
					$file = $this->get_pdf_type('invoice_notax', 'Invoice-notax.pdf');
				}
			}

			$pagecount = $pdf->setSourceFile($file);
			$tplidx = $pdf->ImportPage(1);
			$pdf->addPage();
			$pdf->useTemplate($tplidx, 0, 0);
			$pdf->SetLeftMargin(20);
			$pdf->SetRightMargin(20);
			$pdf->SetY(63);
			$pdf->SetFont('Arial','',12);

			$bill_address = $this->DB->ExecuteScalar(sprintf(SQL_GET_BILLING_ADDRESS, $projectid));
			$bill_address = str_replace("\n\n", "\n", $bill_address); // Clear out 2nd address field if blank
			$client_name = $this->DB->ExecuteScalar(sprintf(SQL_GET_CLIENT_NAME, $projectid));

			$invoicedetails = $this->DB->QuerySingle(sprintf(SQL_GET_INVOICE_DETAILS, $billid));
			$invoice_date = Format::date($invoicedetails['DateCreated'], FALSE, FALSE);
			$due_date = Format::date($invoicedetails['Due'], FALSE, FALSE);
			$invoice_number = $invoicedetails['Title'];

			//Add issue date
			$pdf->SetXY(47,51);
			$pdf->Cell(25,6, $invoice_date,0,2,"L",0);
			//Add due date
			$pdf->SetXY(155,51);
			$pdf->Cell(25,6,$due_date,0,2,"L",0);
			//Add bill to
			$pdf->SetXY(47,60);
			$pdf->MultiCell(50,6,$client_name."\n".$bill_address,0,2,"L",0);
			//Add invoice number
			$pdf->SetXY(155,60);
			$pdf->MultiCell(25,6,$invoice_number,0,2,"L",0);

			$pdf->SetFont('Arial','',10);
			$posX = 17;
			$posY = 106;

			//Add line items
			$sql = sprintf(SQL_GET_PROJECT_TASKS, $projectid, 'Name', 'ASC');
			$tasks = $this->DB->Query($sql);
			if (is_array($tasks)) {
				foreach ($tasks as $key => $task) {
					//$line_item = $this->DB->QuerySingle(sprintf(SQL_GET_INVOICE_LINE_ITEM, $task['ID'], $billid));
					if (is_array($line_item)) {
						$name = trim( $line_item['TaskName'] ) . "\n";
						$desc = trim( $line_item['TaskDescription'] ) . "\n";
						$amount = Format::money( $line_item['Amount'], TRUE ) . "\n";
						$total = $total + $line_item['Amount'];

						// Print the 'Item' text in the first column and get the X/Y coords after.
						$pdf->SetXY( $posX, $posY );
						$pdf->MultiCell( $nameWidth, 6, $name, 0, 'L', 0 );
						$itemY = $pdf->GetY();

						// Print the 'Description' text in the second column and get the X/Y coords after.
						$pdf->SetXY( $posX + $nameWidth, $posY );
						$pdf->MultiCell( $descWidth, 6, $desc, 0, 'L', 0 );
						$descY = $pdf->GetY();

						// Print the 'Amount' text in the third column and get the X/Y coords after.
						$pdf->SetXY( $posX + $nameWidth + $descWidth, $posY );
						$pdf->MultiCell( $amountWidth, 6, $amount, 0, 'L', 0 );
						$amountY = $pdf->GetY();

						// Set the Y coord to the bottom position of the longest column.
						$coords = array( $itemY, $descY, $amountY );
						sort( $coords, SORT_NUMERIC );
						$posY = array_pop( $coords );
					}

					// Print comments as line items.
					$comments = $this->DB->Query(sprintf(SQL_TASKS_GET_COMMENTS, $task['ID']));
					if (is_array($comments)) {
						foreach($comments as $key => $value) {
							if (0 < $value['OutOfScope']) {
								$line_item = $this->DB->QuerySingle(sprintf(SQL_GET_INVOICE_ADDITIONAL_LINE_ITEM, $value['ID'], $billid));
								if ($line_item) {
									$name = trim( $line_item['TaskName'] ) . "\n";
									$amount = Format::money( $line_item['Amount'], TRUE ) . "\n";
									$total = $total + $line_item['Amount'];

									// Print the 'Item' text in the first column and get the X/Y coords after.
									$pdf->SetXY( $posX, $posY );
									$pdf->MultiCell( $nameWidth, 6, $name, 0, 'L', 0 );
									$itemY = $pdf->GetY();

									// Print the 'Amount' text in the third column and get the X/Y coords after.
									$pdf->SetXY( $posX + $nameWidth + $descWidth, $posY );
									$pdf->MultiCell( $amountWidth, 6, $amount, 0, 'L', 0 );
									$amountY = $pdf->GetY();

									// Set the Y coord to the bottom position of the longest column.
									$coords = array( $itemY, $amountY );
									sort( $coords, SORT_NUMERIC );
									$posY = array_pop( $coords );
								}
							}
						}
					}
				}

				// Print additional items.
				$line_items = $this->DB->Query(sprintf(SQL_GET_INVOICE_LINE_ITEMS, $billid));
				if (is_array($line_items)) {
					foreach ($line_items as $line_item) {
						$name = trim( $line_item['TaskName'] );
						$desc = trim( $line_item['TaskDescription'] ) . "\n";
						$amount = Format::money( $line_item['Amount'], TRUE );
						$total = $total + $line_item['Amount'];

						// Print the 'Item' text in the first column and get the X/Y coords after.
						$pdf->SetXY( $posX, $posY );
						$pdf->MultiCell( $nameWidth, 6, $name, 0, 'L', 0 );
						$itemY = $pdf->GetY();

						// Print the 'Description' text in the second column and get the X/Y coords after.
						$pdf->SetXY( $posX + $nameWidth, $posY );
						$pdf->MultiCell( $descWidth, 6, $desc, 0, 'L', 0 );
						$descY = $pdf->GetY();

						// Print the 'Amount' text in the third column and get the X/Y coords after.
						$pdf->SetXY( $posX + $nameWidth + $descWidth, $posY );
						$pdf->MultiCell( $amountWidth, 6, $amount, 0, 'L', 0 );
						$amountY = $pdf->GetY();

						// Set the Y coord to the bottom position of the longest column.
						$coords = array( $itemY, $descY, $amountY );
						sort( $coords, SORT_NUMERIC );
						$posY = array_pop( $coords );
					}
				}
			}

			$pdf->SetFont('Arial','',12);

			// first total row.
			$pdf->SetXY(160,235);
			$pdf->MultiCell(33, 6, Format::money($total, TRUE), 0, 'L', 0);
			
			if (Settings::get('TaxRate'))
			{
				// Now tax row
				$pdf->SetXY(160,243);
				$tax_rate = (Settings::get('TaxRate') == null) ? 0 : Settings::get('TaxRate');
				$tax = $total * ($tax_rate / 100);
				$pdf->MultiCell(33, 6, Format::money($tax, TRUE), 0, 'L', 0);

				// Grand total
				$pdf->SetXY(160,251);
				$pdf->MultiCell(33, 6, Format::money($total + $tax, TRUE), 0, 'L', 0);
			}

			$pdf->Output('"' . htmlentities($invoicedetails['Title']) . '.pdf"', "D");
			$pdf->closeParsers();
		}
		else
		{
			$this->ThrowError(2001);
		}
	}

	function ImportBasecamp() {
		if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE))
		{
			$modTitle = MSG_PROJECTS;
			$modHeader = MSG_IMPORT;

			$this->setTemplate('import_basecamp', $tmpl);

			$this->setHeader($modTitle);
			$this->setModule($modHeader);
			$this->Render();
		}
		else
		{
			$this->ThrowError(2001);
		}
	}

	function ImportBasecampXML() {
		set_time_limit(600);
		ignore_user_abort(1);

		$file = Request::files('xml_file', Request::R_ARRAY);
		$file_tmp	= $file['tmp_name'];

		$data = file_get_contents($file_tmp);

		include_once('xml.inc.php');

//die(var_dump($data));
		$xml = xmlParse($data);
		$xml = rebuildTree($xml);
//die(var_dump($xml));
		$clients = $xml['ACCOUNT'][0]['CLIENTS'][0]['CLIENT'];
		if ( is_array( $clients ) )
		{
			foreach ( $clients as $c )
			{
				$sql = sprintf( SQL_CLIENT_IMPORT, $c['ID'], $c['NAME'], '', $c['PHONE-NUMBER-OFFICE'], '', '', $c['PHONE-NUMBER-FAX'], $c['ADDRESS-ONE'], $c['ADDRESS-TWO'], $c['CITY'], $c['STATE'], $c['COUNTRY'], $c['ZIP'], $c['WEB-ADDRESS'], '', 0, '', '', '' );
				$this->DB->Execute( $sql );
				echo $sql;

				$contacts = $c['PEOPLE'][0]['PERSON'];
				if ( is_array( $contacts ) )
				{
					foreach ( $contacts as $co )
					{
						$notes = ( $co['IM-HANDLE'] != '' ) ? $co['IM-SERVICE'] . ': ' . $co['IM-HANDLE'] : '';
						$sql = sprintf( SQL_CONTACT_IMPORT, $co['ID'], $co['CLIENT-ID'], 0, $co['FIRST-NAME'], $co['LAST-NAME'], $notes, $co['TITLE'], $co['EMAIL-ADDRESS'], '', $co['PHONE-NUMBER-OFFICE'], $co['PHONE-NUMBER-MOBILE'], $co['PHONE-NUMBER-HOME'], '' );
						$this->DB->Execute( $sql );
						echo $sql;
					}
				}
			}
		}

		$projects = $xml['ACCOUNT'][0]['PROJECTS'][0]['PROJECT'];
		if ( is_array( $projects ) )
		{
			foreach ( $projects as $p )
			{
				//print_r(array_keys($p));
				$status = ( $p['STATUS'] == 'archived' ) ? array_search( MSG_ARCHIVED, $this->StatusList ) : 0;
				$sql = sprintf( SQL_PROJECT_IMPORT, $p['ID'], 0, '', $p['NAME'], 0, '', '', $p['CREATED-ON'], '', '', $status, 1, '', '', 0, 0, 1 );
				$this->DB->Execute( $sql );
				echo $sql;

				$tasks = $p['TODO-LISTS'][0]['TODO-LIST'];
				if ( is_array( $tasks ) )
				{
					foreach ( $tasks as $t )
					{
						$sql = sprintf( SQL_TASK_IMPORT, $t['ID'], $t['NAME'], $t['PROJECT-ID'], 0, '', 0, 0, '', 0, 1, 0, '', '', $t['POSITION'], 0, 0, 0 );
						$this->DB->Execute( $sql );
						echo $sql;
					}
				}
			}
		}

		//Response::redirect('index.php?module=projects');
	}

	function Import() {
		if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE)) {

		$modTitle = MSG_PROJECTS;
		$modHeader = MSG_IMPORT;

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

		//$client_access_list = $this->User->GetUserItemAccess('clients', CU_ACCESS_READ);
		if ( $clientsAccessList == '-1' ) {
			$clientlist = $this->DB->Query(SQL_GET_CLIENTS_ALL);
		}
		else {
			$clientsAccessList = ($clientsAccessList) ? $clientsAccessList : '0';
			$SQL = sprintf(SQL_GET_CLIENTS, $clientsAccessList);
			$clientlist = $this->DB->Query($SQL);
		}

		for ($i = 0, $clientcount = count($clientlist); $i < $clientcount; $i++) {
			if ($clientlist[$i][0]) {
				$txtclients .= sprintf('<option value="%s">%s</option>',
					$clientlist[$i][0], $clientlist[$i][1]);
			}
		}
		$tmpl['selectClient']	 = $txtclients;

		$this->setTemplate('import_file', $tmpl);

		$this->setHeader($modTitle);
		$this->setModule($modHeader);
		$this->Render();
		}
		else
		{
			$this->ThrowError(2001);
		}
	}

	function ImportXML() {
		// i need more time capt'n!!
		set_time_limit(600);
		ignore_user_abort(1);

		// we parse with simplexml.
		$file = Request::files('xml_file', Request::R_ARRAY);

		// do it in a group. fasttter.
		DB::begin_transaction();

		$xml = simplexml_load_file($file['tmp_name']);
		
		// assume it's copper
		$source_type = self::IMPORT_COPPER;
		if (in_array('http://schemas.microsoft.com/project', $xml->getNamespaces()))
		{
			$source_type = self::IMPORT_MSPROJECT;
		}
		
		$p = new Project();
		$p->ClientID 	= Request::post('client', Request::R_INT);

		$p->Name			= $xml->Name;
		$p->StartDate	= str_replace("T", " ", $xml->StartDate);
		$p->EndDate		= str_replace("T", " ", $xml->FinishDate);
		$p->ClientID	= Request::post('client');
		$p->commit();

		$projectid = $p->ID;

		// Add permission to access this project.
		$sql = sprintf(SQL_CREATE_USER_PERMISSIONS, $this->User->ID, $projectid, CU_ACCESS_WRITE);
		$this->DB->Execute($sql);

		$sequence = 0;
		$indent = null;
		$last_seen_indent = null;
		
		$puid_to_taskid_map = array();
		
		foreach($xml->Tasks->Task as $xml_task)
		{
			if (($xml_task->IsNull == 1) || ($xml_task->Name == ''))
			{
				continue;
			}
			
			$t = new Task();

			// update all the fields
			foreach($t->get_default_fields() as $field)
			{
				$t->$field = $xml_task->$field;
			}

			// if we are importing from MS Project, they have some different names. deal with that here.
			// note that these overwrite direct matches, which is weird, but hey, so is copper.
			$maps = array('Start' => 'StartDate', 'Finish' => 'EndDate');
			foreach($maps as $ms_key => $object_key)
			{
				if ($xml_task->$ms_key != '')
				{
					$t->$object_key = $xml_task->$ms_key;
				}
			}
			
			// set the sequence and indent level.
			$t->Sequence = $sequence++;

			$current_indent = (string) $xml_task->OutlineLevel;
			if ($last_seen_indent == null)
			{
				$indent = 0;
			} else 
			{
				// seriously. xml serialiser is a bit shit sometimes.
				// only ever go up by one.
				if ($current_indent > $last_seen_indent)
				{
					$indent++;
				} else if ($current_indent < $last_seen_indent) {
					// but we can go down by many, at a min of 0.
					// we try to drop down by as many as the import file has dropped down.
					$indent = max(0, $indent - ($last_seen_indent - $xml_task->OutlineLevel));
				} // else stay the same.
			}
			
			$t->Indent = $indent;
			$last_seen_indent = $current_indent;

			// do msproject specific stuff.
			if ($source_type == self::IMPORT_MSPROJECT)
			{
			// override priority, as in mssxml is something silly like 500
				$t->Priority = 1;
			}

			// set the project and owner ids.
			// also set ID to null so it autoincremennbts
			$t->ID					= null;
			$t->ProjectID		= $projectid;
			$t->Owner				= $this->User->ID;

			$t->commit();

			// do msproject specific mapping.
			if ($source_type == self::IMPORT_MSPROJECT)
			{
				$puid_to_taskid_map[(string)$xml_task->UID] = $t->ID;
			}

			if (isset($xml_task->PredecessorLink))
			{
				foreach($xml_task->PredecessorLink as $xml_link)
				{
					if ($source_type == self::IMPORT_MSPROJECT)
					{
						// first check that we know which thing this links to. note that we only do one pass, 
						// so future or circular references will just be lost on import
						if (array_key_exists((string) $xml_link->PredecessorUID, $puid_to_taskid_map))
						{
							$td = new TaskDependency(array(
								'TaskID' => $t->ID,
								'TaskDependencyID' => $puid_to_taskid_map[(string) $xml_link->PredecessorUID],
								'DependencyType' => 1,
							));
							$td->commit();
						}
					}
				}
				
				// else, unsupported dependancy mapping.
			}

			// @TODO Implement dependancies on import. 
			// note that what's exported and what copper uses is different. mapping below.
			// switch ($dependency['TYPE']) {
			// 		case "0": $dependency_type = 3; break; //correct
			// 		case "1": $dependency_type = 1; break; //correct
			// 		case "2": $continue = 0; break;
			// 		case "3": $dependency_type = 2; break; //correct
			// }

			if (isset($xml_task->Comments) && isset($xml_task->Comments->Comment))
			{
				
				foreach($xml_task->Comments->Comment as $xml_comment)
				{
					$c = new TaskComment();
				
					// update all the fields
					foreach($c->get_default_fields() as $field)
					{
						$c->$field = $xml_comment->$field;
					}
				
					// override the ID's.
					$c->ID			= null;
					$c->TaskID	= $t->ID;
					$c->UserID	= $this->User->ID;
					$c->commit();
				}
			}
		}
		
		DB::commit_transaction();
		Response::redirect('index.php?module=projects&action=view&projectid=' . $p->ID);
	}

	function ExportXML() 
	{
		// i think this is a map from copper dependancy types, to msproject dependancy types
		$dependency_array = array(1 => 1,2 => 3,3 => 0);
		$id = Request::get('id', Request::R_INT);
		
		if ($this->User->HasUserItemAccess($this->ModuleName, $id, CU_ACCESS_READ)) {

			$project_details = $this->DB->QuerySingle(sprintf(SQL_GET_PROJECT, $id));
			
			$xml = new SimpleXMLElement('<Project></Project>');
			$xml->addAttribute('xmlns', 'http://schemas.microsoft.com/project');
			$xml->addChild( 'Name', 			$project_details['Name']);
			$xml->addChild( 'Company', 		$project_details['ClientName']);
			$xml->addChild( 'Manager', 		$project_details['FirstName'] . ' ' . $project_details['LastName'] );
			$xml->addChild( 'StartDate', 	$project_details['StartDate'] . 'T00:00:00' );
			$xml->addChild( 'FinishDate', $project_details['EndDate'] . 'T00:00:00' );

			$tasks = new Tasks( array( 'where' => array('ProjectID' => $id), 'orderby' => 'Sequence ASC' ) );
			if (count($tasks) > 0) {
				$xml_tasks = $xml->addChild('Tasks');

				foreach ($tasks as $task) {
					$xml_task = $xml_tasks->addChild('Task');

					foreach($task->get_default_fields() as $field)
					{
						$xml_task->addChild($field, $task->$field);
					}

					$dependencies = $this->DB->Query(sprintf(SQL_PROJECT_TASK_DEPENDENCIES, $task->ID));
					if (is_array($dependencies)) {
						$pl = $xml_task->addChild('PredecessorLink');

						foreach($dependencies as $dependency_key => $dependency) {
							$pl->addChild('PredecessorUID', $dependency['TaskDependencyID'] );
							$pl->addChild('Type', $dependency_array[$dependency['DependencyType']] );
						}
					}
					
					if (count($task->comments) > 0) {
						$cs = $xml_task->addChild('Comments');

						foreach($task->comments as $comment) {
							$c = $cs->addChild('Comment');
							
							foreach($comment->get_default_fields() as $field)
							{
								$c->addChild($field, $comment->$field);
							}
						}
					}
					
				}
			}

			header("Content-Type: text/xml");
			header("Content-disposition: attachment;filename=\"$project_details[Name].xml\"");
			echo $xml->asXML();
		}
	}

	function HasAccessToTask($taskid) {
				$access = 0;
				$sql = sprintf(SQL_SELECT_ASSIGNED_TASKS, $this->User->ID);
				$result = $this->DB->Query($sql);
				if ($result) {
						foreach ($result as $key => $value) {
								if ($value['ID'] == $taskid)
										$access = 1;
						}
				}

				return $access;
	}

	function get_project_budget_rows($project, $for_quoting = FALSE)
	{
		$tasks = $project->get_tasks_with_hours();

		$data['project'] = $project;

		$task_list = '';
		$data['task_totals']['TargetBudget'] = 0;
		$data['task_totals']['Duration'] = 0;
		$data['task_totals']['HoursWorked'] = 0;
		$data['task_totals']['Billable'] = 0;
		$data['task_totals']['Billed'] = 0;
		$data['task_totals']['ToBill'] = 0;
		
		if (count($tasks) > 0) 
		{
			foreach ($tasks as $task) 
			{
				$sugg = $for_quoting ? $task->Duration * Settings::get('HourlyRate') : ($task->Billable - $task->Billed);
				$task_list .= $this->includeTemplate('budgets/task_item', array('task' => $task, 'suggested_to_bill' => $sugg));

				$data['task_totals']['TargetBudget'] += $task->TargetBudget;
				$data['task_totals']['Duration'] += $task->Duration;
				$data['task_totals']['HoursWorked'] += $task->HoursWorked;
				$data['task_totals']['Billable'] += $task->Billable;
				$data['task_totals']['Billed'] += $task->Billed;
				$data['task_totals']['ToBill'] += $task->Billable - $task->Billed;
			}
		}

		// other items
		$data['other_totals']['Budget'] = 0;
		$data['other_totals']['Quantity'] = 0;
		$data['other_totals']['Logged'] = 0;
		$data['other_totals']['Cost'] = 0;
		$data['other_totals']['Charge'] = 0;
		$data['other_totals']['Billable'] = 0;
		$data['other_totals']['Billed'] = 0;
		$data['other_totals']['ToBill'] = 0;
		$other_items_list = '';
		
		if ($project->exists)
		{
			// this returnes an object of class InvoiceItemOthers
			foreach ($project->get_other_invoiced_items() as $item) 
			{
				$other_items_list .= $this->includeTemplate('budgets/other_item', array('item' => $item));

				$data['other_totals']['Budget'] += $item->Budget;
				$data['other_totals']['Est'] += $item->Quantity;
				$data['other_totals']['Logged'] = $item->Logged;
				$data['other_totals']['Cost'] += $item->Cost;
				$data['other_totals']['Charge'] += $item->Charge;
				$data['other_totals']['Billable'] += $item->Billable;
				$data['other_totals']['Billed'] += $item->Billed;
				$data['other_totals']['ToBill'] += $item->Billable - $item->Billed;
			}
		}

		$data['project_totals']['Budget']	= $data['task_totals']['TargetBudget'] + $data['other_totals']['Budget'];
		$data['project_totals']['Est']			= $data['task_totals']['Est'] + $data['other_totals']['Est'];
		$data['project_totals']['Logged']	= $data['task_totals']['Logged'] + $data['other_totals']['Logged'];
		$data['project_totals']['Billable']= $data['task_totals']['Billable'] + $data['other_totals']['Billable'];
		$data['project_totals']['Billed']	= $data['task_totals']['Billed'] + $data['other_totals']['Billed'];
		$data['project_totals']['ToBill']	= $data['task_totals']['ToBill'] + $data['other_totals']['ToBill'];

		// add to the template
		$data['task_list'] = $task_list;
		$data['other_items_list'] = $other_items_list;

		$content = $this->includeTemplate('budgets/billable_items', $data); // append this one to the template contents
		
		return $content;
	}


	function Budgets() {
		$this->User->requireHasUserItemAccess($this->ModuleName, Request::get('projectid', Request::R_INT), CU_ACCESS_READ);

		$project = new Project(Request::get('projectid', Request::R_INT));
		Response::addToJavascript('date_format', $this->date_format[Settings::get('DateFormat')]);
		Response::addToJavascript('currency_symbol', Settings::get('CurrencySymbol'));
		// this tab has som efuuuunnky javascript. Add the id. 

		$this->ProjectTabs($project->ID);
		$invoices = new Invoices(array('where' => array('ProjectID' => $project->ID), 'orderby' => 'Due'));

		$invoice_js_data = array();
		$invoice_total = 0;
		foreach($invoices as $invoice)
		{
			$invoice_js_data[] = array('id' => (int) $invoice->ID, 'date' => $invoice->Due, 'Amount' => (float) $invoice->total);
			$invoice_total += $invoice->total;
		}
		
		$project_js_data = array(
			'ID' => $project->ID,
			'StartDate' => $project->StartDate,
			'budgets' => array(	// the following definitions have been taken from the project view screen.
					// this is the field, target budget
				'target' => (float) $project->TargetBudget,
					// this is 'actual' column + other items cost,
				'cost' => $project->ActualBudget + $project->get_other_items_cost(), 
					// actual is charge (which is the sum of the charges from the comments) + other items charge
				'charge' => $project->get_total_charge() + $project->get_other_items_charge(), 
				'invoiced' => $invoice_total,
				'invoices' => $invoice_js_data,
			),
		);

		Response::addToJavascript('project', $project_js_data);

		$data['project'] = $project;
		$this->includeTemplate('budgets/invoices_header', $data, TRUE);
		foreach($invoices as $invoice)
		{
			$data['invoice'] = $invoice;
			$this->includeTemplate('budgets/invoice_row', $data, TRUE);
		}
		$this->includeTemplate('budgets/invoices_footer', $data, TRUE);

		$other_items = new InvoiceItemOthers(array('where' => array('ProjectID' => $project->ID)));

		$this->includeTemplate('budgets/overall_other_items_header', $data, TRUE);
		foreach($other_items as $other_item)
		{
			$data['item'] = $other_item;
			$this->includeTemplate('budgets/overall_other_item_row', $data, TRUE);
		}
		$this->includeTemplate('budgets/overall_other_items_footer', $data, TRUE);


		$this->setModule($project->Name . ' ' . MSG_BUDGETS);
		$this->Render();
	}

	function ajax_create_invoice() {
		$project = new Project(Request::post('projectid', Request::R_INT));

		// create an empty invoice.
		$invoice = new Invoice();
		$invoice->CreatedBy = 1; // CoppperUser::current()->ID;
		$invoice->Title = Request::post('quote', Request::R_INT) ? MSG_NEW_QUOTE : MSG_NEW_INVOICE;
		$invoice->ProjectID = $project->ID;
		$invoice->Quote = Request::post('quote', Request::R_INT);
		$invoice->Due = DB::date(strtotime("+" . Settings::get('Terms') . ' days'));
		$invoice->DateCreated = DB::now();
		$invoice->commit();
		
		$data = array('project' => $project, 'invoice' => $invoice);

		$this->includeTemplate('budgets/new_invoice', $data, TRUE);
		$content = $this->get_project_budget_rows($project, $invoice->Quote);
		$this->addToContent($content);

		$this->includeTemplate('budgets/new_invoice_footer', $data, TRUE);
		
		$this->RenderOnlyContent();
	}

	function AjaxGetInvoiceRowItems() {

		$projectID = Request::get('projectid', Request::R_INT);
		$taskID = Request::get('taskid', Request::R_INT);
		if (!$this->User->HasUserItemAccess($this->ModuleName, $projectID, CU_ACCESS_READ))
		{
			$this->ThrowError(2001);
			return;
		}

		$tmpl['resourceList'] = NULL;
		$sql = sprintf(SQL_GET_BUDGET_RESOURCES,$taskID);
		$users = $this->DB->Query($sql);
		if (is_array($users)) {
			foreach($users as $key => $user) {
				$resource_tmpl['txtName'] = $user['Name'];
				$resource_tmpl['txtHoursLogged'] = $user['HoursWorked'];
				$resource_tmpl['txtRate'] = Format::money($user['CostRate']);
				$resource_tmpl['txtCharge'] = Format::money($user['ChargeRate']);
				$billable = Format::money((float)(isset($user['HoursWorked'])?$user['HoursWorked']:0)*$user['ChargeRate']);
				$resource_tmpl['txtBillable'] = $billable;

				$tmpl['resourceList'] .= $this->getTemplate('budgets/new_invoice_line_item', $resource_tmpl);
			}
		}

		$this->setTemplate('ajax_budgets_task_resources', $tmpl);
		$this->RenderOnlyContent();

	}

	function AjaxGetExistingInvoice() {
		$projectID = Request::get('projectid', Request::R_INT);
		$invoiceID = Request::get('invoiceid', Request::R_INT);
		if (!$this->User->HasUserItemAccess($this->ModuleName, $projectID, CU_ACCESS_READ))
		{
			$this->ThrowError(2001);
			return;
		}

		$sql = sprintf(SQL_GET_INVOICE_DETAILS, $invoiceID);
		$invoice = $this->DB->QuerySingle($sql);

		// get invoice items
		$sql = sprintf(SQL_GET_INVOICE_LINE_ITEMS, $invoiceID);
		$items = $this->DB->Query($sql);

		$tmpl['txtInvoiceID'] = $invoiceID;
		$tmpl['invoiceTaskList'] = NULL;
		$tmpl['txtQuote'] = $invoice['Quote'];
		if (is_array($items) && (count($items) > 0)) {
			foreach ($items as $key => $item) {
				$invoice_tmpl['txtInvoiceID'] = $invoiceID;
				$invoice_tmpl['txtItemID'] = $item['ID'];
				$invoice_tmpl['txtName'] = $item['TaskName'];
				$invoice_tmpl['txtDescription'] = $item['TaskDescription'];
				$invoice_tmpl['txtAmount'] = Format::money($item['Amount']);
				$invoice_tmpl['txtTax'] = Format::money($item['Amount'] * (Settings::get('TaxRate') / 100));
				$invoice_tmpl['txtTotal'] = Format::money($item['Amount'] * (1 + (Settings::get('TaxRate') / 100)));
				$tmpl['invoiceTaskList'] .= $this->getTemplate('budgets/invoice_task_item', $invoice_tmpl);
			}
		} else {
			$tmpl['invoiceTaskList'] = MSG_NO_TASKS_AVAILABLE;
		}
		$this->setTemplate('budgets/existing_invoice', $tmpl);
		$this->RenderOnlyContent();
	}

	function ContactList() {
				$projectid = Request::get('projectid', Request::R_INT);

				if ($this->User->HasUserItemAccess($this->ModuleName, $projectid, CU_ACCESS_READ))
				{
					$details = $this->GetProjectDetails($projectid);

						$modTitle = MSG_PROJECTS;
						$modHeader = $details['ProjectName'] . ' ' . MSG_CONTACTS . ' ' . MSG_VIEW;

						$this->ProjectTabs($projectid);

				//ordering
				$orderOptions = array(
					'email'				=> 'EmailAddress1'
				);
				
				list($order, $orderby) = Request::$GET->filterRequest('order', $orderOptions, array('name', 'ContactName'));
				list($direction, $orderDir) = Request::$GET->filterOrderDirection();

				// paging code
				$limit = Settings::get('RecordsPerPage');
				$offset = Request::get('start');
				$SQL = sprintf(SQL_CONTACTS_LIST, $orderby, $orderdir, $details[ClientID]);
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

				//echo $this->DB->LastErrorMessage;
				if (!$RS->EOF())
				{
						$tmpl['ProjectID']	= $projectid;
						$tmpl['lblContactName']	= MSG_CONTACT_NAME;
						$tmpl['lblEmailAddress'] = MSG_EMAIL_ADDRESS;
						$tmpl['lblAsc']			= MSG_ASCENDING;
						$tmpl['lblDesc']		 = MSG_DESCENDING;
			$tmpl['start']		= $offset;
						$this->setTemplate('contact_header', $tmpl);
						unset($tmpl);

						$counter = 1;
			while (!$RS->EOF())
						{
								if ($counter > 1) $this->setTemplate('contact_spacer');
								$tmpl['bgcolor'] = ($RS->Field('KeyContact')) ? '#FFFF99' : '#FFFFFF';
								$tmpl['CONTACT_ID'] = $RS->Field('ID');
								$tmpl['CONTACT_NAME'] = $RS->Field('ContactName');
								$tmpl['CONTACT_PHONE'] = $RS->Field('Phone1');
								$tmpl['CONTACT_EMAIL'] = $RS->Field('EmailAddress1');
								$tmpl['CONTACT_LAST_CONTACT'] = Format::date($this->DB->ExecuteScalar(sprintf(SQL_GET_LAST_CONTACT,$projectid, $RS->Field('ID'))), Settings::get('PrettyDateFormat'));

								$this->setTemplate('contact_item', $tmpl);
								unset($tmpl);
								++$counter;
								$RS->MoveNext();
						}

						if ($RS->TotalRecords > $limit)
						{
								$url = 'index.php?module=projects&amp;action=contactlist&amp;projectid='.$projectid.'&amp;order='.$order.'&amp;direction='.$direction;
								cuPaginate($RS->TotalRecords, $limit, $url, $offset, $tmpl);
				$this->setTemplate('contact_paging', $tmpl);
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
		if (!$this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_READ))
		{
			$this->ThrowError(2001);
			return;
		}

		// 1. get clients that the user has access to
		// 2. get *all* project ids for those clients (this has been commented out)
		// 3. get all projects user has access to
		// 4. combine list
		$clientsAccessList = $this->User->GetUserItemAccess('clients', CU_ACCESS_DENY);
		if ($clientsAccessList == '-1')
		{
			$clientsAccessList = array();
			$clientIDs = $this->DB->Query(SQL_GET_CLIENT_IDS);
			for ($i = 0; $i < count($clientIDs); $i++)
			{
				if ($this->User->HasUserItemAccess('clients',$clientIDs[$i]['ID'], CU_ACCESS_READ))
					$clientsAccessList[] = $clientIDs[$i]['ID'];
			}

			$clientsAccessList = implode(',', $clientsAccessList);
		}

		if (($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE)) && ($clientsAccessList))
		{
			$modAction[] = '<a href="index.php?module=projects&action=new">' . MSG_NEW_PROJECT . '</a>';
			$modAction[] = '<a href="index.php?module=projects&action=import">' . MSG_IMPORT . '</a>';
			//$modAction[] = '<a href="index.php?module=projects&action=importbasecamp">' . MSG_IMPORT_BASECAMP . '</a>';
		}

		// Compile full project ID listing (allowable clients, and explicitly set projects)
		$tmp = array();
		$projectIDs = $this->DB->Query(sprintf(SQL_SELECT_PROJECT_IDS, $clientsAccessList));
		if (is_array($projectIDs))
		{
			foreach ($projectIDs as $key => $value) {
			if (!$this->User->HasUserItemAccess($this->ModuleName, $value['ID'], CU_ACCESS_DENY))
				  $tmp[] = $value['ID'];
	  }
		}
		$projectList = implode(',', $tmp);

		$projectAccessList = $this->User->GetUserItemAccess($this->ModuleName, CU_ACCESS_READ);
		if ($projectAccessList) // Can be -1
		{
			if (strlen($projectList) > 0)
				$projectList .= ','.$projectAccessList;
			else
				$projectList = $projectAccessList;
		}

		$limit = Settings::get('RecordsPerPage');
		$offset = Request::get('start');
		$active = (Request::get('archived', Request::R_INT) == 1) ? 0 : 1;
		$active_logic = (Request::get('archived', Request::R_INT) == 1) ? 'OR' : 'AND';
		$archive = (Request::get('archived', Request::R_INT) == 1) ? 1 : 0;
		$msg = (Request::get('archived', Request::R_INT) == 1) ? MSG_VIEW_ACTIVE : MSG_VIEW_ARCHIVED;
		$modAction[] = '<a href="index.php?module=projects&archived='.$active.'">'.$msg.'</a>';

		$orderOptions = array(
			'progress'			=> 'PercentComplete',
			'priority'			=> 'Priority',
			'status'				=> 'Status',
			'budget'				=> 'TargetBudget',
			'actualbudget'	=> 'ActualBudget',
			'owner'					=> 'Owner',
			'starts'				=> 'StartDate',
			'ends'					=> 'EndDate',
			'project'				=> 'ProjectName',
			'client'				=> 'ClientName'
		);
		list($order, $orderby) = Request::$GET->filterRequest('order', $orderOptions, array('', 'ProjectName, ClientName, StartDate'));
		list($direction, $orderdir) = Request::$GET->filterOrderDirection();

		// get our SQL
		if ($this->User->HasModuleItemAccess('administration', CU_ACCESS_ALL, CU_ACCESS_WRITE))
			$SQL = sprintf(SQL_PROJECTS_LIST_ALL, $active_logic, $active, $orderby, $orderdir);
		else if ($projectList)
			$SQL = sprintf(SQL_PROJECTS_LIST, $projectList, $active_logic, $active, $orderby, $orderdir);

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

		if (!$RS->EOF())
		{
			$tmpl['lblActualBudget'] = MSG_ACTUAL;
			$tmpl['lblArchive']	  = 'archived='.$archive.'&';
			$tmpl['lblAsc']		  = MSG_ASCENDING;
			$tmpl['lblClient']	   = MSG_CLIENT;
			$tmpl['lblDesc']		 = MSG_DESCENDING;
			$tmpl['lblEnds']		 = MSG_ENDS;
			$tmpl['lblOwner']		= MSG_OWNER;
			$tmpl['lblPriority']	 = MSG_PRIORITY;
			$tmpl['lblProgress']	 = MSG_EST_PERCENT;
			$tmpl['lblProject']	  = MSG_PROJECT;
			$tmpl['lblStarts']	   = MSG_STARTS;
			$tmpl['lblStatus']	   = MSG_STATUS;
			$tmpl['lblTargetBudget'] = MSG_TARGET_BUDGET;
			$tmpl['lblTasks']		= MSG_TASKS;
			$tmpl['start']		   = $offset;
			$this->setTemplate('project_header', $tmpl);

			$hasBudget = $this->User->HasModuleItemAccess('budget', CU_ACCESS_ALL, CU_ACCESS_READ);
			while (!$RS->EOF())
			{
				$tmpl['txtProjectID'] = $RS->Field('ID');
				$tmpl['txtProjectName'] = $RS->Field('ProjectName');
				if ($RS->Field('ProjectID'))
					$tmpl['txtProjectName'] .= ' ('.$RS->Field('ProjectID').')';

				$tmpl['txtClientID'] = $RS->Field('ClientID');
				$tmpl['txtClientName'] = $RS->Field('ClientName');
				$tmpl['txtOwner'] = MSG_OWNER.': '.$RS->Field('FirstName') . ' ' . $RS->Field('LastName');
				$tmpl['txtPercentComplete'] = (int)$RS->Field('PercentComplete');
				$tmpl['txtColour'] = $RS->Field('Colour');
				$tmpl['txtStatus'] = $this->StatusList[$RS->Field('Status')];
				$tmpl['txtStartDate'] = Format::date($RS->Field('StartDate'));
				$tmpl['txtEndDate'] = Format::date($RS->Field('EndDate'));
				$tmpl['txtPriority'] = Format::convert_priority($RS->Field('Priority'));

				// Add Invoice Other Items to the Actual Budget cost.
				$sql = sprintf(SQL_GET_PROJECT_OTHER_ITEMS_COST, $RS->Field('ID'));
				$otherItemCost = $this->DB->ExecuteScalar($sql);

				$sql = sprintf(SQL_GET_PROJECT_OTHER_ITEMS_CHARGE, $RS->Field('ID'));
				$otherItemCharge = $this->DB->ExecuteScalar($sql);

				$sql = sprintf(SQL_GET_PROJECT_CHARGE, $RS->Field('ID'));
				$charge = $this->DB->QuerySingle($sql);

				$tmpl['txtTargetBudget'] = ($hasBudget) ? Format::money($RS->Field('TargetBudget')) : MSG_NA;
				$tmpl['txtBudgetCharge'] = ($hasBudget) ? Format::money($charge['Charge'] + $otherItemCharge) : MSG_NA;
				$tmpl['txtBudgetCost'] = ($hasBudget) ? Format::money($RS->Field('ActualBudget') + $otherItemCost) : MSG_NA;
				/*if ($tmpl['lblStatus'] == 'Proposed'){*/
				$this->setTemplate('project_item', $tmpl);/*}*/
				$RS->MoveNext();
			}

			if ($RS->TotalRecords > $limit)
			{
				$url = 'index.php?module=projects&archived='.$archive.'&order='.$order.'&direction='.$direction;
				cuPaginate($RS->TotalRecords, $limit, $url, $offset, $tmpl);
				$this->setTemplate('project_paging', $tmpl);
				unset($tmpl);
			}

			$this->setTemplate('project_footer');
		}
		else
		{
			$tmpl['MESSAGE'] = MSG_NO_PROJECTS_AVAILABLE;
			$this->setTemplate('eof', $tmpl);
		}

		$RS->Close();
		unset($RS);

		$this->addStyle('projects-projectlist.css');

		$this->setHeader(MSG_PROJECTS, '');
		$this->setModule(MSG_LIST, $modAction);
		$this->Render();
	}

	function GetProjectDetails($projectID = 0) {
		// get name details
		$sql = sprintf(SQL_GET_PROJECT_DETAILS, $projectID);
		$result = $this->DB->QuerySingle($sql);
		return $result;
	}

	function ProjectStatus($array) {
				$tmpl['txtColour']			= $array['Colour'];
				$tmpl['txtClientID']		= $array['ClientID'];
				$tmpl['txtClientName']	= $array['ClientName'];
				$tmpl['txtProjectID']	 = $array['ProjectID'];
				$tmpl['txtProjectName'] = $array['ProjectName'];
				$tmpl['txtWidth']			 = (intval($array['PercentComplete']) > 0) ? $array['PercentComplete'] : 1;
				$tmpl['txtPercent']		 = @number_format($array['PercentComplete']);
				$this->setTemplate('project_status', $tmpl);
				unset($tmpl);
	}


	/**
	 * Retrieve the tabs for a project. 
	 * Note that if project id is non-numeric or 0, we should just show the breakdown tab 
	 */
	function ProjectTabs($projectid) {
		$active = Request::any('action');
		$project_actions = array("taskview", "edit");
		$active = (in_array($active, $project_actions)) ? "view" : $active;
		
		if (is_numeric($projectid) && ($projectid > 0) )
		{
			$tmpl['lblBreakdownTab'] = $this->AddTab(MSG_BREAKDOWN, 'view', $active, $projectid);
			$tmpl['lblTimelineTab']  = $this->AddTab(MSG_TIMELINE, 'gantt', $active, $projectid);
			$tmpl['lblTasksTab']	 = $this->AddTab(MSG_TASKS, 'tasklist', $active, $projectid);
			$tmpl['lblContactsTab']  = $this->AddTab(MSG_CONTACTS, 'contactlist', $active, $projectid);

			if ($this->User->HasModuleItemAccess('files', CU_ACCESS_ALL, CU_ACCESS_READ))
			{
				$tmpl['lblFilesTab'] = $this->AddTab(MSG_FILES, 'filelist', $active, $projectid);
			} else {
				$tmpl['lblFilesTab'] = NULL;
			}

			if ($this->User->HasModuleItemAccess('budget', CU_ACCESS_ALL, CU_ACCESS_READ))
			{
				$tmpl['lblBudgetsTab'] = $this->AddTab(MSG_BUDGETS, 'budgets', $active, $projectid);
			} else {
				$tmpl['lblBudgetsTab'] = NULL;
			}

			$tmpl['lblEmailsTab']	  = $this->AddTab(MSG_EMAILS, 'emaillist', $active, $projectid);

		} else {
			$tmpl['lblBreakdownTab'] = NULL;
			$tmpl['lblTimelineTab']  = NULL;
			$tmpl['lblTasksTab']	 = NULL;
			$tmpl['lblContactsTab']  = NULL;
			$tmpl['lblFilesTab']	 = NULL;
			$tmpl['lblBudgetsTab']   = NULL;
			$tmpl['lblEmailsTab']	= NULL;
		}
		
		$this->setTemplate('tabs', $tmpl);
		unset($tmpl);
	}

	function AddTab($name, $action, $active, $projectid) {
		//echo "$name, $action, $active, $projectid";
		$tab = ($action == strtolower($active)) ? 'tab_active' : 'tab_inactive';
		$query = '&action='.$action.'&projectid='.$projectid;
		return $this->getTemplate($tab, array('lblTabName' => $name, 'lblTabQuery' => $query));
	}

	function ProjectNew() {
		$clientsAccessList = $this->User->GetUserItemAccess('clients', CU_ACCESS_DENY);
		if ($clientsAccessList == '-1')
		{
			$clientsAccessList = '';
			$clientIDs = $this->DB->Query(SQL_GET_CLIENT_IDS);
			for ($i = 0; $i < count($clientIDs); $i++)
			{
				if ($this->User->HasUserItemAccess('clients',$clientIDs[$i]['ID'], CU_ACCESS_WRITE))
					 $clientsAccessList .= $clientIDs[$i]['ID'].',';
				else
					$clientsAccessList = NULL;
			}
			$clientsAccessList = substr($clientsAccessList, 0, -1);
		}

		if (($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE)) && ($clientsAccessList))
			$this->ProjectDisplayForm();
		else
			$this->ThrowError(2001);
	}

	function ProjectEdit() {
		$id = Request::get('projectid', Request::R_INT);
		if (is_numeric($id))
		{
			if (Request::get('action') == 'copy')
				$this->ProjectDisplayForm($id);
			else if ($this->User->HasUserItemAccess($this->ModuleName, $id, CU_ACCESS_WRITE))
				$this->ProjectDisplayForm($id);
			else
				$this->ThrowError(2001);
		}
		else
			Response::redirect('index.php?module=projects');
	}

	function ProjectDisplayForm($id = 0) {
		$modTitle = MSG_PROJECTS;
		$modHeader = '';
		$clientID = Request::get('clientid', Request::R_INT);
		$action = Request::get('action');

		$tmpl['copy'] = ($action == "copy") ? '<input type="hidden" name="copy" value="1">' : '';
		$tmpl['lblClient'] = MSG_CLIENT;
		$tmpl['lblProjectOwner'] = MSG_PROJECT_OWNER;
		$tmpl['lblProjectColour'] = MSG_PROJECT_COLOUR;
		$tmpl['lblPriority'] = MSG_PRIORITY;
		$tmpl['lblStatus'] = MSG_STATUS;
		$tmpl['lblResources'] = MSG_RESOURCES;
		$tmpl['lblReadAccess'] = MSG_READ_ACCESS;
		$tmpl['lblWriteAccess'] = MSG_WRITE_ACCESS;
		$tmpl['lblStartsEnds'] = MSG_STARTS_ENDS;
		$tmpl['lblTargetBudget'] = MSG_BUDGET;
		$tmpl['lblDetails'] = MSG_DETAILS;
		$tmpl['lblRelatedProjects'] = MSG_RELATED_PROJECTS;
		$tmpl['lblSaveChanges'] = MSG_SAVE_CHANGES;
		$tmpl['lblCancel'] = MSG_CANCEL;
		$tmpl['date_format'] = $this->date_format[Settings::get('DateFormat')];
		$tmpl['msgFormError'] = MSG_ENTER_PROJECT_NAME;
		$tmpl['msgConfirmDelete'] = MSG_DELETE_PROJECT;

		// Get the list of client items that the user has access to.
		$clientsAccessList = $this->User->GetUserItemAccess('clients', CU_ACCESS_WRITE);

		if ( $clientsAccessList == '-1' ) {
			$clientlist = $this->DB->Query(SQL_GET_CLIENTS_ALL);
		} else {
			$clientlist = $this->DB->Query(sprintf(SQL_GET_CLIENTS, $clientsAccessList));
		}

		if ($id == 0)
		{
			$modHeader .= MSG_NEW_PROJECT;

			if ( !$clientID )
				$clientID = $clientlist[0][0];

			// The default project colour is #00CCFF, but is overridden by the client colour if one is set.
			$tmpl['txtColour'] = Settings::get('DefaultColour');
			if ($clientID)
			{
				$colour = $this->DB->ExecuteScalar(sprintf(SQL_SELECT_CLIENT_COLOUR, $clientID));
				if ( $colour )
					$tmpl['txtColour'] = $colour;
			}

			$tmpl['txtID'] = $id;
			$tmpl['txtProjectID'] = '';
			$tmpl['txtAction'] = 'new';
			$tmpl['txtName'] = '';
			$tmpl['txtPercentComplete'] = 0;
			$tmpl['txtDescription'] = '';
			$tmpl['txtStartDate'] = Format::date(date('Y-m-d'), FALSE, FALSE);
			$tmpl['txtEndDate'] = $tmpl['txtStartDate'];
			$tmpl['txtTargetBudget'] = 0;
			$status = 0;
			$priority = 1;
			$ownerID = $this->User->ID;
		}
		else
		{
			$SQL = sprintf(SQL_GET_PROJECT, $id);
			$RS =& new DBRecordset();
			$RS->Open($SQL, $this->DB);
			if (!$RS->EOF())
			{
				$header = ($action == 'copy') ? MSG_COPY : MSG_EDIT;
				$modHeader .= $RS->Field('Name').' '.$header;

				if (!$clientID)
					$clientID = $RS->Field('ClientID');
				$tmpl['txtID'] = $id;
				$tmpl['txtAction'] = ($action == 'copy') ? 'copy&projectid='.$id : 'edit&projectid='.$id ;
				$tmpl['txtName'] = $RS->Field('Name');
				$tmpl['txtName'] .= ($action != 'copy') ? '' : ' '.MSG_COPY;
				$tmpl['txtColour'] = $RS->Field('Colour');
				$tmpl['txtStartDate'] = Format::date($RS->Field('StartDate'), FALSE, FALSE);
				$tmpl['txtEndDate'] = Format::date($RS->Field('EndDate'), FALSE, FALSE);
				$tmpl['txtPercentComplete'] = (int)$RS->Field('PercentComplete');
				$tmpl['txtTargetBudget'] = $RS->Field('TargetBudget');
				$tmpl['txtProjectID'] = ($action == 'copy') ? '' : $RS->Field('ProjectID');

				$desc = preg_replace( '<a href=".*?">', '', $RS->Field('Description') );
				$desc = str_replace( '</a>', '', $desc );
				$desc = str_replace( '<>', '', $desc );
				$tmpl['txtDescription'] = $desc;

				$status = $RS->Field('Status');
				$priority = $RS->Field('Priority');
				$ownerID = $RS->Field('Owner');
			}
			$RS->Close();
			unset($RS);

			// Rebuild client list to include the client for this project.
			if ($clientsAccessList != '-1')
			{
				$clientsAccessList = ($clientsAccessList) ? $clientsAccessList : '0';
				$clientlist = $this->DB->Query(sprintf(SQL_GET_CLIENTS_PLUS, $clientsAccessList, $clientID));
			}
		}

		$tmpl['budget'] = '';
		$hasBudgetWrite = $this->User->HasModuleItemAccess('budget', CU_ACCESS_ALL, CU_ACCESS_WRITE);
		if ($hasBudgetWrite)
		{
			$tmpl['budget'] = $this->getTemplate('project_form_budget', $tmpl);
		}

		// {{{ Read & write permissions options
		$allGroups = array(); // All groups with read or write stored here. Used to exclude them later.
		$allUsers = array();  // All users with read or write stored here. Used to exclude them later.

		$groups = '';
		if (($id > 0) && (!Request::get('clientID', Request::R_INT)))
			$groups_sql = sprintf(SQL_GET_GROUPS_WITH_READ_PERMS, 'projects', $id);
		else
			$groups_sql = sprintf(SQL_GET_GROUPS_WITH_READ_PERMS, 'clients', $clientID);
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
		if (($id > 0) && (!Request::get('clientID', Request::R_INT)))
			$users_sql = sprintf(SQL_GET_USERS_WITH_READ_PERMS, 'projects', $id);
		else
			$users_sql = sprintf(SQL_GET_USERS_WITH_READ_PERMS, 'clients', $clientID);
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
		if (($id > 0) && (!Request::get('clientID', Request::R_INT)))
			$groups_sql = sprintf(SQL_GET_GROUPS_WITH_WRITE_PERMS, 'projects', $id);
		else
			$groups_sql = sprintf(SQL_GET_GROUPS_WITH_WRITE_PERMS, 'clients', $clientID);
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
		if (($id > 0) && (!Request::get('clientID', Request::R_INT)))
			$users_sql = sprintf(SQL_GET_USERS_WITH_WRITE_PERMS, 'projects', $id);
		else
			$users_sql = sprintf(SQL_GET_USERS_WITH_WRITE_PERMS, 'clients', $clientID);
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
		
		// Priority list <option>s
		for ($i = 0, $prioritycount = count($this->PriorityList); $i < $prioritycount; $i++)
		{
			$selected = ($priority == $i) ? 'SELECTED' : '';
			$tmpl['txtPriority'] .= sprintf('<option value="%s" %s>%s</option>', $i, $selected, $this->PriorityList[$i]);
		}

		// Status list <option>s
		for ($i = 0, $statuscount = count($this->StatusList); $i < $statuscount; $i++)
		{
			$selected = ($status == $i) ? 'SELECTED' : '';
			$tmpl['txtStatus'] .= sprintf('<option value="%s" %s>%s</option>', $i, $selected, $this->StatusList[$i]);
		}

		// Owner list <option>s
		$userlist = $this->DB->Query(SQL_GET_USERS);
		for ($i = 0, $usercount = count($userlist); $i < $usercount; $i++)
		{
			$selected = ($ownerID == $userlist[$i][0]) ? 'SELECTED' : '';
			$name = $userlist[$i][1].' '.$userlist[$i][2];
			$tmpl['txtOwner'] .= sprintf('<option value="%s" %s>%s</option>', $userlist[$i][0], $selected, $name);
		}

		// Client list <option>s
		$clientArray = array();
		for ($i = 0, $clientcount = count($clientlist); $i < $clientcount; $i++)
		{
			$clientArray[] = $clientlist[$i][0]; // Used below to save a DB hit.
			$selected = ($clientID == $clientlist[$i][0]) ? 'SELECTED' : '';
			$tmpl['txtClientList'] .= sprintf('<option value="%s" %s>%s</option>', htmlspecialchars($clientlist[$i][0]), $selected, htmlspecialchars($clientlist[$i][1]));
		}

		// {{{ Related projects only shown on the edit page.
		$tmpl['relatedprojects'] = '';
		if ($action == 'edit')
		{
			// get all the related projects
			$tmplRelated['txtRelatedProjectsList'] = '';
			$tmplRelated['lblRelatedProjects'] = MSG_RELATED_PROJECTS;

			$relatedArray = array($id);
			$relatedProjectsSQL = sprintf(SQL_RELATED_PROJECTS, $id);
			$relatedProjects = $this->DB->Query($relatedProjectsSQL);
			if ($relatedProjects != false)
			{
				for ($i = 0; $i < count($relatedProjects); $i++)
				{
					$tmplRelated['txtRelatedProjectsList'] .= $this->getTemplate('related_project_item_edit', $relatedProjects[$i]);
					$relatedArray[] = $relatedProjects[$i]['RelatedProjectID'];
				}
			}

			// Compile full project ID listing (allowable clients, and explicitly set projects)
			$projectsArray = array();
			if (count($clientsArray) > 0)
			{
				$projectIDs = $this->DB->Query(sprintf(SQL_SELECT_PROJECT_IDS, implode(',', $clientsArray)));
				foreach ($projectIDs as $p)
					$projectsArray[] = $p['ID'];
			}

			$projectAccessList = $this->User->GetUserItemAccess($this->ModuleName, CU_ACCESS_READ);
			$projectsArray = array_merge($projectsArray, explode(',', $projectAccessList));

			$whereProjectIDNotIn = implode(',', array_unique($relatedArray));
			$projectIDs = implode(',', array_unique($projectsArray));
			if ($this->User->IsAdmin)
				$unrelatedProjectsSQL = sprintf(SQL_PROJECTS_LIST_ALL_FOR_RELATED, $whereProjectIDNotIn);
			else if ($project_list)
				$unrelatedProjectsSQL = sprintf(SQL_PROJECTS_LIST_ALL_FOR_RELATED_IN, $projectIDs, $whereProjectIDNotIn);
			
			$unrelatedProjects = $this->DB->Query($unrelatedProjectsSQL);

			// build the html select options of unrelated projects
			$related_tmpl['txtAddThisProject'] = '';

			if ($unrelatedProjects != false)
			{
				for ($i = 0; $i < count($unrelatedProjects); $i++)
				{
					$tmplRelated['txtAddThisProject'] .= '<option value="' . $unrelatedProjects[$i]['ID'] . '">' . $unrelatedProjects[$i]['Name'] . '</option>';
				}
			}
			$tmpl['relatedprojects'] = $this->getTemplate('related_projects', $tmplRelated);
		}
		
		if (Request::get('ajax')) {
		  $SelectColours  =array('#00CCFF', '#FFFF66', '#FF9200', '#FF4040',
								 '#FFC756', '#84CD53', '#669966', '#E3D9FF',
								 '#B23232', '#EAF5A2', '#99CC99', '#666699',
								 '#AFA4CD', '#5B7F96', '#CDDD61', '#FF7474',
								 '#F2DA00', '#E52828', '#666666', '#708686',
								 '#66513C', '#F2ACCD', '#EB3F91', '#4C61F9',
								 '#1DA0CF', '#5B340D', '#C0E835', '#6B802A',
								 '#EB1D52', '#8E82AF', '#999999', '#707070');

		  $colourPickerScript = <<<COLORPICKER
			<div onclick='bSelectShow=true' id='ColourSelector' style='z-index: +999; position: absolute; visibility: hidden;'>
			  <table width='100'>
				<tr>
				  <td width='100%'>
					<table width="100" height="100" border="0" cellpadding="0" cellspacing="1" bgcolor="#FFFFFF">
					  <tr align="left" valign="top">
COLORPICKER;

		  for ($i = 1; $i < count($SelectColours); $i++)
		  {
			$colourPickerScript .= '<td width="25" height="25" bgcolor="' . $SelectColours[$i-1] . '"><img src="images/common/s.gif" width="25" height="25" border="0" onClick="setColour(\'' . $SelectColours[$i-1] .'\')"></td>';
			if (($i % 4) == 0)	{
			  $colourPickerScript .= '	</tr>';
			  $colourPickerScript .= '	<tr align="left" valign="top">';
			}
		  }

		  $colourPickerScript .= <<<COLORPICKER
					  </tr>
					</table>
				  </td>
				</tr>
			  </table>
			</div>
COLORPICKER;

		$tmpl['SCRIPTS'] .= $colourPickerScript;

		$template = 'project_form_ajax';
		$this->setTemplate($template, $tmpl);
		$this->RenderOnlyContent();
	
	}
	else {

		/* only show the project tabs on non-new projects */
		if ($id > 0)
		{
		  $this->ProjectTabs($id);
		}
		
		$tmpl['tasklist'] = ($id > 0) ? $this->TaskList() : '';

		$this->addStyle('projects-projectview.css');
		$this->addScript('selectors/colourselector.js');
		$this->setHeader($modTitle, '');
		$this->setModule($modHeader, $modAction);
		$template = ($id > 0) ? 'project_form' : 'project_new';
		$this->setTemplate($template, $tmpl);
		$this->Render();
	}
	}

	function ProjectSave() {
		$id = $this->DB->Prepare(Request::post('projectid'));
		$userprojectid = $this->DB->Prepare(Request::post('userprojectid'));
		$clientid = $this->DB->Prepare(Request::post('clientid'));
		$name = htmlentities($this->DB->Prepare(Request::post('name')), ENT_COMPAT, CHARSET);
		$owner = $this->DB->Prepare(Request::post('owner'));
		$status = $this->DB->Prepare(Request::post('status'));
		$priority = $this->DB->Prepare(Request::post('priority'));
		$colour = $this->DB->Prepare(Request::post('colour'));
		$description = htmlentities($this->DB->Prepare(Request::post('description')), ENT_COMPAT, CHARSET);
		$targetbudget = $this->DB->Prepare(Request::post('targetbudget'));
		$active = ($status == 6) ? 0 : 1;
		$copy = $this->DB->Prepare(Request::post('copy'));

		$sd = Request::post('startdate');
		$startdate  = ($sd == '' || $sd == '--') ? 'NULL' : "'".Format::parse_date($sd)."'";
		$ed = Request::post('enddate');
		$enddate  = ($ed == '' || $ed == '--') ? 'NULL' : "'".Format::parse_date($ed)."'";
		$ea = Request::post('enddateactual');
		$endactual  = ($ea == '' || $ea == '--') ? 'NULL' : "'".Format::parse_date($ea)."'";

		// If NEW or COPY check for auto projectid settings
		if ((($copy != null) || ($id == 0)) && (Settings::get('AutoID') == '1'))
		{
			$userprojectid = Settings::get('IDStartValue');
			$length = strlen(Settings::get('IDStartValue'));
			$sql = sprintf(SQL_UPDATE_START_VALUE, str_pad(($userprojectid+1), $length, '0', STR_PAD_LEFT));
			$this->DB->Execute($sql);
		}

		if (($id == 0) && ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE)))
		{
			// INSERT record
			$SQL = sprintf(SQL_PROJECT_CREATE, $clientid, $userprojectid, $name, $owner, $startdate, $enddate,
				$endactual, $status, $priority, $colour, $description, $targetbudget, $active);
			$this->DB->Execute($SQL);
			$projectID = $this->DB->ExecuteScalar(SQL_LAST_INSERT);
						// so here we grant read/write for the user who created.
			$SQL = sprintf(SQL_CREATE_USER_PERMISSIONS, $this->User->ID, $projectID, 2);
			$this->DB->Execute($SQL);

			// now we need to grant permissions for other users who have access to the parent client. 
			// First get the list of users with permissions on the parent client.
			$SQL = sprintf(SQL_GET_USERS_WITH_CLIENT_ACCESS, $clientid);
			$RS =& new DBRecordset();
			$RS->Open($SQL, $this->DB);

			// then go through each user, and give them the same permissions on the new project as 
			// what they have on the parent client. (ie read for read, write for write.)
			while (!$RS->EOF())
			{
				// we've already granted on ourselves.
				if ($RS->Field('UserID') == $this->User->ID)
				{
					$RS->MoveNext();
					continue;
				}
				
				$SQL = sprintf(SQL_CREATE_USER_PERMISSIONS, $RS->Field('UserID'), $projectID, $RS->Field('AccessID'));
				$this->DB->Execute($SQL);
				$RS->MoveNext();
			}
			
			$SQL = sprintf(SQL_GET_GROUPS_WITH_CLIENT_ACCESS, $clientid);
			$RS =& new DBRecordset();
			$RS->Open($SQL, $this->DB);

			// then go through each group, and give them the same permissions on the new project as 
			// what they have on the parent client. (ie read for read, write for write.)
			while (!$RS->EOF())
			{
				$SQL = sprintf(SQL_CREATE_GROUP_PERMISSIONS, $RS->Field('GroupID'), $projectID, $RS->Field('AccessID'));
				$this->DB->Execute($SQL);
				$RS->MoveNext();
			}
		}
		elseif ($this->User->HasUserItemAccess($this->ModuleName, $id, CU_ACCESS_WRITE))
		{
			if ($copy != null)
			{
				// Copy project, tasks and task delegation
				$SQL = sprintf(SQL_PROJECT_COPY, $clientid, $userprojectid, $name, $owner, $startdate, $enddate,
					$endactual, $status, $priority, $colour, $description, $targetbudget, $active);
				$this->DB->Execute($SQL);
				$newProjectID = $this->DB->ExecuteScalar(SQL_LAST_INSERT);
				$SQL = sprintf(SQL_CREATE_USER_PERMISSIONS, $this->User->ID, $newProjectID, 2);
				$this->DB->Execute($SQL);

				// now we need to grant permissions for other users who have access to the parent client. 
				// First get the list of users with permissions on the parent client.
				$SQL = sprintf(SQL_GET_USERS_WITH_CLIENT_ACCESS, $clientid);
				$RS =& new DBRecordset();
				$RS->Open($SQL, $this->DB);

				// then go through each user, and give them the same permissions on the new project as 
				// what they have on the parent client. (ie read for read, write for write.)
				while (!$RS->EOF())
				{
					// we've already granted on ourselves.
					if ($RS->Field('UserID') == $this->User->ID)
					{
						$RS->MoveNext();
						continue;
					}

					$SQL = sprintf(SQL_CREATE_USER_PERMISSIONS, $RS->Field('UserID'), $projectID, $RS->Field('AccessID'));
					$this->DB->Execute($SQL);
					$RS->MoveNext();
				}

				$SQL = sprintf(SQL_GET_GROUPS_WITH_CLIENT_ACCESS, $clientid);
				$RS =& new DBRecordset();
				$RS->Open($SQL, $this->DB);

				// then go through each group, and give them the same permissions on the new project as 
				// what they have on the parent client. (ie read for read, write for write.)
				while (!$RS->EOF())
				{
					$SQL = sprintf(SQL_CREATE_GROUP_PERMISSIONS, $RS->Field('GroupID'), $projectID, $RS->Field('AccessID'));
					$this->DB->Execute($SQL);
					$RS->MoveNext();
				}

				$SQL = sprintf(SQL_GET_PROJECT_TASKS, $id, 'ID', 'ASC');
				$RS =& new DBRecordset();
				$RS->Open($SQL, $this->DB);

				$x = 0;
				while (!$RS->EOF())
				{
					//$sequence   = $this->DB->ExecuteScalar(sprintf(SQL_PROJECT_TASK_SEQUENCE_MAX,$id)) + 1;
					$taskID = $RS->Field('ID');
					$SQL = sprintf(SQL_TASKS_GET_DETAILS_ALL, $taskID);
					$task = $this->DB->QuerySingle($SQL);
					$SQL = sprintf(SQL_TASK_COPY, addslashes($task['Name']), $newProjectID, $task['Owner'], $task['StartDate'], $task['Duration'], $task['EndDate'], $task['Priority'], '0', addslashes($task['Description']), $task['RelatedURL'], $task['Sequence'], $task['Indent'], $task['TargetBudget'], '');
					$this->DB->Execute($SQL);
					$newTaskID = $this->DB->ExecuteScalar(SQL_LAST_INSERT);

					$array[$x][0] = $taskID;
					$array[$x][1] = $newTaskID;
					$x++;

					$RS->MoveNext();
				}

				// Copy dependencies
				for ($i = 0 ; $i < count($array) ;$i++)
				{
					$dependenciesSQL = sprintf(SQL_PROJECT_TASK_DEPENDENCIES,$array[$i][0]);
					$dependenciesRS =& new DBRecordset();
					$dependenciesRS->Open($dependenciesSQL, $this->DB);

					while (!$dependenciesRS->EOF())
					{
						$found=FALSE;
						$j = 0;
						while (!$found)
						{
							if ($array[$j][0] == $dependenciesRS->Field('TaskDependencyID'))
							{
								$newDependency = $array[$j][1];
								$found = TRUE;
							}
							$j++;
						}
						$SQL = sprintf(SQL_PROJECT_TASK_DEPENDENCY_ADD, $array[$i][1], $newDependency, $dependenciesRS->Field('DependencyType'));
						$this->DB->Execute($SQL);
						$dependenciesRS->MoveNext();
					}
				}
				$projectID = $newProjectID;
			}
			else
			{
				// UPDATE record
				if ($this->User->HasModuleItemAccess('budget', CU_ACCESS_ALL, CU_ACCESS_WRITE))
					$SQL = sprintf(SQL_PROJECT_UPDATE, $clientid, $userprojectid, $name, $owner, $startdate, $enddate,
						$endactual, $status, $priority, $colour, $description, $targetbudget, $active, $id);
				else
					$SQL = sprintf(SQL_PROJECT_UPDATE_NO_BUDGET, $clientid, $userprojectid, $name, $owner, $startdate, $enddate,
						$endactual, $status, $priority, $colour, $description, $active, $id);
				$this->DB->Execute($SQL);
				$projectID = $id;
			}
		}
		else
		{
			// The user doesn't have access to either insert new, or update existing.
			$this->ThrowError(2001);
		}

		// Permissions code removed as permissions are now handled in an Ajax manner.

		Response::redirect('index.php?module=projects&action=view&projectid='.$projectID);
	}

	function ProjectDelete() {
		$id = Request::get('id', Request::R_INT);
		$tmpl['MESSAGE'] = MSG_USER_NOT_FOUND;
		$template = 'message';
		$title = MSG_PROJECTS;

		if (is_numeric($id)) {
			if ($this->User->HasUserItemAccess($this->ModuleName, $id, CU_ACCESS_WRITE)) {
				$confirm = Request::get('confirm');
				if ($confirm == 1) {
					//Select tasks
					$tasks = $this->DB->Query(sprintf(SQL_GET_TASK_IDS, $id));
					if ($tasks) {
						foreach ($tasks as $task_key => $task_value) {
							$taskid = $task_value[ID];
							$rows = $this->DB->Query(sprintf(SQL_GET_HOURS_COMMITTED_ON_TASK_FOR_TOTAL, $taskid));
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
					$files = $this->DB->Query(sprintf(SQL_GET_FILE_IDS, $id));
					if ($files) {
						foreach ($files as $file_key => $file_value) {
							$fileid = $file_value['ID'];
							//Delete all file log
							$this->DB->Execute(sprintf(SQL_DELETE_FILE_LOGS, $fileid));
							//Delete file (DB)
							$this->DB->Execute(sprintf(SQL_DELETE_FILE, $fileid));
						}
					}

					$project_dir = str_pad($id, 7, '0', STR_PAD_LEFT);
					// trailing slash - checks for trailing slash, just incase someone didn't put it in
					$trailer = substr(SYS_FILEPATH, strlen(SYS_FILEPATH) - 1, 1);
					$filepath = ( ($trailer != '\\') && ($trailer != '/') ) ? SYS_FILEPATH . '/' : SYS_FILEPATH;
					//~ trailing slash
					$project_path = $filepath . $project_dir . '/';
					@delete_all_from_dir($project_path);

					//Delete project permissions
					$this->DB->Execute(sprintf(SQL_DELETE_GROUP_PERMS, 'projects', $id));
					$this->DB->Execute(sprintf(SQL_DELETE_USER_PERMS, 'projects', $id));

					//Delete related projects
					$this->DB->Execute(sprintf(SQL_DELETE_RELATED_PROJECTS, $id));
					$this->DB->Execute(sprintf(SQL_DELETE_PROJECT, $id));
					$tmpl['MESSAGE'] = 'The project was deleted';
					$tmpl['OK']	  = MSG_OK;
					$template		  = 'deleted';
					Response::redirect('index.php?module=projects');
				}
				else
				{
					$SQL = sprintf(SQL_GET_PROJECT_NAME, $id);
					$rs = $this->DB->QuerySingle($SQL);
					if (is_array($rs))
					{
						$tmpl['ID'] = $id;
						$tmpl['MESSAGE'] = sprintf(MSG_DELETE_PROJECT_WARNING, $rs['Name']);
						$breadcrumbs = MSG_DELETE;
						$tmpl['YES'] = MSG_YES;
						$tmpl['NO'] = MSG_NO;
						$template = 'delete_project';
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

	function ProjectView() {
		$projectID = Request::get('projectid', Request::R_INT);
		if (!$this->User->HasUserItemAccess($this->ModuleName, $projectID, CU_ACCESS_READ))
		{
			$this->ThrowError(2001);
			return;
		}

		$project = $this->DB->QuerySingle(sprintf(SQL_GET_PROJECT, $projectID));
		$this->Log('project', $projectID, 'view', $project['Name']);

		$modTitle = MSG_PROJECTS;
		$modHeader = $project['Name'].' '.MSG_VIEW;
		//$this->ProjectTabs($projectID, 'breakdown', $project['Name']);

		if ($this->User->HasUserItemAccess($this->ModuleName, $projectID, CU_ACCESS_WRITE))
		{
			$actions[] = array('url' => '#', 'attrs' => "onclick=\"newTask($projectID);\"", 'name' => MSG_NEW_TASK);
			$actions[] = array('url' => "javascript:editProject($projectID);", 'name' => MSG_EDIT);
			$actions[] = array('url' => "index.php?module=projects&action=copy&projectid=$projectID", 'name' => MSG_COPY);
			$actions[] = array('url' => "index.php?module=projects&action=delete&id=$projectID", 'name' => MSG_DELETE,
				'confirm' => 1, 'title' => MSG_CONFIRM_PROJECT_DELETE_TITLE, 'body' => MSG_CONFIRM_PROJECT_DELETE_BODY);
			$actions[] = array('url' => "javascript:popupWindow('projects','$projectID');", 'name' => MSG_PRINT);
		}
		else
			$actions[] = array('url' => "index.php?module=projects&action=copy&projectid=$projectID", 'name' => MSG_COPY);

		// Email Team link
		$ownerEmail = $project['EmailAddress'];
		$subject = $project['ClientName']. " > ".$project['Name']." - ".MSG_TEAM_NOTIFICATION;
		$userlist = $this->GetProjectUsers($projectID, $ownerEmail);
		$mailto = "mailto:$ownerEmail?Subject=".$subject."&bcc=$userlist";
		$actions[] = array('url' => $mailto, 'name' => MSG_EMAIL_TEAM);

		// iCal Sync link
		$taskCount = $this->DB->ExecuteScalar(sprintf(SQL_TASK_COUNT, $projectID));
		if ($taskCount > 0)
		{
			// Create validity key
			$key = substr(md5($this->User->Fullname.$this->User->PasswordHash),2,8);
			$url = split ("index\.php",Request::server(SCRIPT_NAME_VAR));
			$iCal = 'webcal://'.Request::server(SERVER_NAME_VAR).$url[0].'system/ical.php?projectid='.$projectID.'&key='.$key.'&userid='.$this->User->ID;
			$actions[] = array('url' => $iCal, 'name' => MSG_ICAL_SYNC);
		}

		// XML export is here to ensure it is listed on the right in the action menu.
		$actions[] = array('url' => "index.php?module=projects&action=exportxml&id=$projectID", 'name' => MSG_XML);

		// by default, we want it to show.
		if ((!isset($_COOKIE["proj-details"])) || ($_COOKIE["proj-details"] != 0)) {
			$actions[] = array('url' => '#', 
							  'attrs' => 'onclick="javascript:toggleProjectDetails(this, \'' . str_replace("'", "\'", MSG_HIDE_DETAILS) . '\', \'' . str_replace("'", "\'", MSG_SHOW_DETAILS) . '\'); return false;"', 
							  'name' => MSG_HIDE_DETAILS
							);
			$tmpl['DETAILS_DISPLAY'] = 'block'; 
		}
		else {
			$actions[] = array('url' => '#', 
								'attrs' => 'onclick="javascript:toggleProjectDetails(this, \'' . str_replace("'", "\'", MSG_HIDE_DETAILS) . '\', \'' . str_replace("'", "\'", MSG_SHOW_DETAILS) . '\'); return false;"', 
								'name' => MSG_SHOW_DETAILS
								);
			$tmpl['DETAILS_DISPLAY'] = 'none'; 
		}
		
		$tmpl['actions'] = $this->ActionMenu($actions);

		// Field labels.
		$tmpl['lblActive'] = MSG_ISACTIVE;
		$tmpl['lblBudgetActual'] = MSG_CURRENT_BUDGET;
		$tmpl['lblBudgetTarget'] = MSG_ESTIMATED_BUDGET;
		$tmpl['lblClient'] = MSG_CLIENT;
		$tmpl['lblDescription'] = MSG_DESCRIPTION;
		$tmpl['lblDetails'] = MSG_PROJECT_DETAILS;
		$tmpl['lblEdit'] = strtolower(MSG_ACTION_EDIT);
		$tmpl['lblEnd'] = MSG_END;
		$tmpl['lblFor'] = MSG_FOR;
		$tmpl['lblOwner'] = MSG_OWNER;
		$tmpl['lblPriority'] = MSG_PRIORITY;
		$tmpl['lblProjectID'] = MSG_PROJECT_ID;
		$tmpl['lblProjectOwner'] = MSG_PROJECT_OWNER;
		$tmpl['lblReadAccess'] = MSG_READ_ACCESS;
		$tmpl['lblRelatedProjects'] = MSG_RELATED_PROJECTS;
		$tmpl['lblResources'] = MSG_RESOURCES;
		$tmpl['lblSchedule'] = MSG_SCHEDULE_AND_BUDGET;
		$tmpl['lblLatestActivity'] = MSG_LATESTACTIVITY;
		$tmpl['lblStart'] = MSG_START;
		$tmpl['lblStartsEnds'] = MSG_STARTS_ENDS;
		$tmpl['lblStatus'] = MSG_STATUS;
		$tmpl['lblWriteAccess'] = MSG_WRITE_ACCESS;
		// Field values.
		$tmpl['txtActive'] = ($project['Active'] == 1) ? MSG_YES : MSG_NO;
		$tmpl['txtBudgetActual'] = MSG_NA;
		$tmpl['txtBudgetTarget'] = MSG_NA;
		$tmpl['txtClient'] = $project['ClientName'];
		$tmpl['txtClientID'] = $project['ClientID'];
		$tmpl['txtColour'] = $project['Colour'];
		$tmpl['txtDescription'] = Format::blocktext($project['Description']);
		$tmpl['txtID'] = $projectID;
		$tmpl['txtName'] = $project['Name'];
		if ($project['ProjectID'])
			$tmpl['txtName'] .= ' ('.$project['ProjectID'].')';
		$tmpl['txtOwner'] = $project['FirstName'] . ' ' . $project['LastName'];
		$tmpl['txtPercentComplete'] = (int)$project['PercentComplete'];
		$tmpl['txtPriority'] = Format::convert_priority($project['Priority']);
		$tmpl['txtProjectID'] = $project['ProjectID'];
		$tmpl['txtStartsEnds'] = sprintf('%s / %s', 
										  Format::date($project['StartDate'], 
										  Settings::get('PrettyDateFormat')), 
										  Format::date($project['EndDate'])
										);
		$tmpl['txtStatus'] = $this->StatusList[$project['Status']];

		$tmpl['budget'] = '';
		$hasBudgetRead = $this->User->HasModuleItemAccess('budget', CU_ACCESS_ALL, CU_ACCESS_READ);
		if ($hasBudgetRead)
		{
			// Add Invoice Other Items to the Actual Budget cost.
			$sql = sprintf(SQL_GET_PROJECT_OTHER_ITEMS_COST, $projectID);
			$otherItemCost = $this->DB->ExecuteScalar($sql);

			$sql = sprintf(SQL_GET_PROJECT_OTHER_ITEMS_CHARGE, $projectID);
			$otherItemCharge = $this->DB->ExecuteScalar($sql);

			$sql = sprintf(SQL_GET_PROJECT_CHARGE, $projectID);
			$charge = $this->DB->QuerySingle($sql);

			$budgetTmpl['txtBudgetTarget'] = Format::money($project['TargetBudget']);
			$budgetTmpl['txtBudgetCost'] = Format::money($project['ActualBudget'] + $otherItemCost);
			$budgetTmpl['txtBudgetCharge'] = Format::money($charge['Charge'] + $otherItemCharge);

			$tmpl['budget'] = $this->getTemplate('project_view_budget', $budgetTmpl);
		}

		// Get groups and users with read access to the project.
		$groups = $users = '';
		$sql = sprintf(SQL_GET_GROUPS_WITH_READ_PERMS, 'projects', $projectID);
		$list = $this->DB->Query($sql);
		if ( is_array($list) )
		{
			for ($i = 0; $i < count($list); $i++)
				$groups .= 'Group: '.$list[$i]['Name'].'<br>';
		}

		$sql = sprintf(SQL_GET_USERS_WITH_READ_PERMS, 'projects', $projectID);
		$list = $this->DB->Query($sql);
		if ( is_array($list) )
		{
			for ($i = 0; $i < count($list); $i++)
				$users .= $list[$i]['FullName'].'<br>';
		}

		$tmpl['txtReadAccess'] = (strlen($groups.$users) > 0) ? $groups.$users : '--';

		// Get groups and users with write access to the project.
		$groups = $users = '';
		$sql = sprintf(SQL_GET_GROUPS_WITH_WRITE_PERMS, 'projects', $projectID);
		$list = $this->DB->Query($sql);
		if ( is_array($list) )
		{
			for ($i = 0; $i < count($list); $i++)
				$groups .= 'Group: '.$list[$i]['Name'].'<br>';
		}

		$sql = sprintf(SQL_GET_USERS_WITH_WRITE_PERMS, 'projects', $projectID);
		$list = $this->DB->Query($sql);
		if ( is_array($list) )
		{
			for ($i = 0; $i < count($list); $i++)
				$users .= $list[$i]['FullName'].'<br>';
		}

		$tmpl['txtWriteAccess'] = (strlen($groups.$users) > 0) ? $groups.$users : '--';

		// Get all the related projects
		$tmpl['txtRelatedProjectsList'] = '';
		$relatedProjects = $this->DB->Query(sprintf(SQL_RELATED_PROJECTS, $projectID));
		if (is_array($relatedProjects))
		{
			for ($i = 0; $i < count($relatedProjects); $i++)
				$tmpl['txtRelatedProjectsList'] .= $this->getTemplate('related_project_item', $relatedProjects[$i]);
		}

		$tmpl['tasklist'] = $this->TaskList();

		// Emulate the task view screen.
		$tmpl['script'] = '';
		if (Request::get('action') == 'taskview' || Request::get('action') == 'taskcommentedit' || Request::get('action') == 'view')
		{
			$tempTmpl['txtProjectID'] = $projectID;
			$tempTmpl['txtTaskID'] = Request::get('taskid', Request::R_INT);
			$comment_id = (Request::get('commentid', Request::R_INT) == null) ? 0 : Request::get('commentid', Request::R_INT);
			$tempTmpl['txtCommentID'] = $comment_id;
			
			if (Request::get('new', Request::R_INT) == 1)
			{
				$tmpl['script'] = $this->getTemplate('task_new_script', $tempTmpl);
			}
			elseif ($tempTmpl['txtTaskID'] > 0)
			{
				Response::addToJavascript('auto_open_task', TRUE);
				Response::addToJavascript('item_ids', array(
					'project_id' => $projectID,
					'task_id' => Request::get('taskid', Request::R_INT),
					'comment_id' => $comment_id,
				));
			}
		}

		if (Request::get('ajax')) {
			$this->setTemplate('project_view_ajax', $tmpl);
			$this->RenderOnlyContent();
		}
		else {
			$this->ProjectTabs($projectID);
			$this->setTemplate('project_view', $tmpl);

			$this->addStyle('projects-projectview.css');
			$this->setHeader($modTitle);
			$this->setModule($modHeader);
			$this->Render();
		}
	}

	function GetProjectUsersWithName($projectid, $owneremail) {
		$counter = 0;
		$usersSQL = sprintf(SQL_GET_TASK_OWNERS, $projectid);
		$usersRS =& new DBRecordset();
		$usersRS->Open($usersSQL, $this->DB);
		while (!$usersRS->EOF()) {
			if ($owneremail != $usersRS->Field('EmailAddress'))
				$users[$counter]['Name'] = $usersRS->Field('FirstName');
				$users[$counter]['EmailAddress'] = $usersRS->Field('EmailAddress');
			++$counter;
			$usersRS->MoveNext();
		}

		unset($usersRS);

		$usersSQL = sprintf(SQL_PROJECTS_GET_USERS_EMAIL, $projectid);
		$usersRS =& new DBRecordset();
		$usersRS->Open($usersSQL, $this->DB);
		while (!$usersRS->EOF()) {
			if ($owneremail != $usersRS->Field('EmailAddress')) {
				$users[$counter]['Name'] = $usersRS->Field('FirstName');
				$users[$counter]['EmailAddress'] = $usersRS->Field('EmailAddress');
			}
			++$counter;
			$usersRS->MoveNext();
		}

		if ($users)
		{
			asort($users);
			reset($users);
		}

		return $users;
	}

	function GetProjectUsers($projectID, $ownerEmail) {
		$users = array();
		$SQL = sprintf(SQL_GET_TASK_OWNERS, $projectID);
		$RS =& new DBRecordset();
		$RS->Open($SQL, $this->DB);
		while (!$RS->EOF())
		{
			$users[] = $RS->Field('EmailAddress');
			$RS->MoveNext();
		}
		unset($usersRS);

		$SQL = sprintf(SQL_PROJECTS_GET_USERS_EMAIL, $projectID);
		$RS =& new DBRecordset();
		$RS->Open($SQL, $this->DB);
		while (!$RS->EOF())
		{
			$users[] = $RS->Field('EmailAddress');
			$RS->MoveNext();
		}

		$users = array_diff($users, array($ownerEmail)); // Remove owner email.
		$userlist = implode(',', array_unique($users));
		return $userlist;
	}

	function GetTaskUsers($taskID, $ownerEmail) {
		$users = array();
		$SQL = sprintf(SQL_TASKS_GET_USERS_EMAIL, $taskID);
		$RS =& new DBRecordset();
		$RS->Open($SQL, $this->DB);
		while (!$RS->EOF())
		{
			$users[] = $RS->Field('EmailAddress');
			$RS->MoveNext();
		}

		$users = array_diff($users, array($ownerEmail)); // Remove owner email.
		$userlist = implode(',', array_unique($users));
		return $userlist;
	}

	function ProjectPopup() {
		$projectid = Request::get('id', Request::R_INT);
		$details = Request::post('details');
		$budget = Request::post('budget');
		$tasks = Request::post('tasks');
		$comments = Request::post('comments');
		$files = Request::post('files');
		if ($this->User->HasUserItemAccess($this->ModuleName, $projectid, CU_ACCESS_READ)) {
			$tmpl['lblEdit'] = strtolower(MSG_ACTION_EDIT);
			$tmpl['lblSchedule'] = MSG_SCHEDULE_AND_BUDGET;
			$tmpl['txtID'] = $projectid;

			$detailschecked = "";
			$budgetchecked = "";
			$taskschecked = "";
			$commentschecked = "";
			$fileschecked = "";
			if ($details)
				$detailschecked = "checked";
			if ($budget)
				$budgetchecked = "checked";
			if ($tasks)
				$taskschecked = "checked";
			if ($comments)
				$commentschecked = "checked";
			if ($files)
				$fileschecked = "checked";

			$tmpl['txtPrintOptions'] = "<input type=\"checkbox\" name=\"details\" value=\"details\" $detailschecked>Show Project Details<br>";
			$tmpl['txtPrintOptions'] .= "<input type=\"checkbox\" name=\"budget\" value=\"budget\" $budgetchecked>Show Budget<br>";
			$tmpl['txtPrintOptions'] .= "<input type=\"checkbox\" name=\"tasks\" id=\"inputTasks\" value=\"tasks\" $taskschecked onclick=\"if (!this.checked) document.getElementById('inputComments').checked=false;\">Show Task list<br>";
			$tmpl['txtPrintOptions'] .= "<input type=\"checkbox\" name=\"comments\" id=\"inputComments\" value=\"comments\" $commentschecked onclick=\"if (this.checked) document.getElementById('inputTasks').checked=true;\">Show Task Comments<br>";
			$tmpl['txtPrintOptions'] .= "<input type=\"checkbox\" name=\"files\" value=\"files\" $fileschecked>Show File list<br>";

			$this->setTemplate('print_header', $tmpl);
			unset($tmpl);
			//Display options
			$SQL = sprintf(SQL_GET_PROJECT, $projectid);
			$RS = new DBRecordset();
			$RS->Open($SQL, $this->DB);

			if ($details) {
				$progress_details = $this->GetProjectDetails($projectid);
				$tmpl['lblDetails'] = MSG_PROJECT_DETAILS;
				$tmpl['lblProjectID'] = MSG_PROJECT_ID;
				$tmpl['lblName'] = MSG_PROJECT_NAME;
				$tmpl['lblClient'] = MSG_CLIENT;
				$tmpl['lblOwner'] = MSG_PROJECT_OWNER;
				$tmpl['lblActive'] = MSG_ISACTIVE;
				$tmpl['lblStatus'] = MSG_STATUS;
				$tmpl['lblComplete'] = MSG_PERCENT_COMPLETE;
				$tmpl['lblURL'] = MSG_URL;
				$tmpl['lblDemoURL'] = MSG_DEVELOPMENT_URL;
				$tmpl['lblDescription'] = MSG_DESCRIPTION;
				$tmpl['lblLatestActivity'] = MSG_LATEST_ACTIVITY;
				$tmpl['lblStart'] = MSG_STARTS;
				$tmpl['lblEndTarget'] = MSG_ENDS_TARGET;
				$tmpl['lblEndActual'] = MSG_ENDS_ACTUAL;


				$tmpl['txtProjectID'] = $RS->Field('ProjectID');
				$tmpl['txtName'] = $RS->Field('Name');
				$tmpl['txtClient'] = $RS->Field('ClientName');
				$tmpl['txtOwner'] = $RS->Field('FirstName') . ' ' . $RS->Field('LastName');
				$tmpl['txtActive'] = ($RS->Field('Active') == 1) ? MSG_YES : MSG_NO;
				$tmpl['txtStatus'] = $this->StatusList[$RS->Field('Status')];
				$tmpl['txtComplete'] = @number_format($progress_details['PercentComplete']).'%';
				$tmpl['txtURL'] = $RS->Field('URL');
				$tmpl['txtDemoURL'] = $RS->Field('DemoURL');
				$tmpl['txtDescription'] = Format::blocktext($RS->Field('Description'));
				$tmpl['txtStart'] =	Format::date($RS->Field('StartDate'));
				$tmpl['txtEndTarget'] = Format::date($RS->Field('EndDate'));
				$tmpl['txtEndActual'] = Format::date($RS->Field('ActualEndDate'));
				$this->setTemplate('spacer');
				$this->setTemplate('print_details', $tmpl);
				unset($tmpl);
			}
			if ($budget) {
				$tmpl['lblBudgetDetails'] = MSG_BUDGET_DETAILS;
				$tmpl['lblBudgetTarget'] = MSG_BUDGET_TARGET;
				$tmpl['lblBudgetActual'] = MSG_BUDGET_ACTUAL;
				$tmpl['txtBudgetTarget'] = 'N/A';
				$tmpl['txtBudgetActual'] = 'N/A';

				$budgets = $this->DB->Query(sprintf(SQL_GET_TASK_BUDGETS,$RS->Field('ID')));
				if ($budgets) {
					$tbudget = 0;
					//$abudget = 0;
					foreach($budgets as $key => $value) {
						$tbudget = $tbudget + $value['TargetBudget'];
						//$abudget = $abudget + $value['ActualBudget'];
					}
				}

				$comments_sql = sprintf(SQL_PROJECT_GET_COMMENTS, $projectid);
				$comments_list = $this->DB->Query($comments_sql);
				if ( is_array($comments_list) ) {
					for ($i = 0; $i < count($comments_list); $i++) {
						if ( $comments_list[$i]['HoursWorked'] != 0 ) {
							$hours = $comments_list[$i]['HoursWorked'];
							$charge_rate = $this->DB->ExecuteScalar(sprintf(SQL_GET_CHARGE_RATE,$comments_list[$i]['UserID']));
							$charge_rate = ($charge_rate > 0) ? $charge_rate : Settings::get('HourlyRate');
							$abudget += $hours * $charge_rate;
						}
					}
				}
				if ($this->User->HasModuleItemAccess('budget', CU_ACCESS_ALL, CU_ACCESS_READ))
				{
					$tmpl['txtBudgetTarget'] = (intval($tbudget) > 0) ? Settings::get('CurrencySymbol'). @number_format($tbudget) : '--';
					$tmpl['txtBudgetActual'] = (intval($abudget) > 0) ? Settings::get('CurrencySymbol'). @number_format($abudget) : '--';
				}

				$this->setTemplate('spacer');
				$this->setTemplate('print_budget', $tmpl);
				unset($tmpl);
			}

						$RS->Close();
						unset($RS);

			if ($tasks) {
				$this->setTemplate('spacer');
							$project_details = $this->GetProjectDetails($projectid);
				$SQL = sprintf(SQL_GET_PROJECT_TASKS, $projectid, 't.Sequence', $orderdir);
				$RS =& new DBRecordset();
				$RS->Open($SQL, $this->DB);
				if (!$RS->EOF())
				{

					$tmpl['ProjectID']	 = $projectid;
					$tmpl['lblSequence']		 = MSG_ORDER;
					$tmpl['lblTask']		 = MSG_TASK;
					$tmpl['lblOwner']		= MSG_OWNER;
					$tmpl['lblProgress'] = MSG_PROGRESS;
					$tmpl['lblUrgency'] = MSG_URGENCY;
					$tmpl['lblDuration'] = MSG_ESTIMATED;
					$tmpl['lblDue']			= MSG_ENDS;
					$tmpl['lblAsc']				 = MSG_ASCENDING;
					$tmpl['lblDesc']				= MSG_DESCENDING;
					$this->setTemplate('print_tasks_header', $tmpl);
					unset($tmpl);

					$counter = 1;
					while (!$RS->EOF())
					{
						if ($counter > 1) $this->setTemplate('print_tasks_spacer');

						$taskID = $RS->Field('ID');
						$taskSequence = $RS->Field('Sequence');

						if (!$taskSequence) {
							$sequenceMaxSQL = sprintf(SQL_PROJECT_TASK_SEQUENCE_MAX, $projectid);
							$sequenceMax = $this->DB->QuerySingle($sequenceMaxSQL);
							$sequenceMax = $sequenceMax[0] + 1;
							$sequenceUpdateSQL = sprintf(SQL_PROJECT_TASK_SEQUENCE_UPDATE, $sequenceMax, $taskID);
							$this->DB->Execute($sequenceUpdateSQL);
							$taskSequence = $sequenceMax;
						}

						$tmpl['txtProjectID'] = $projectid;
						$tmpl['txtID'] = $taskID;
						$tmpl['txtSequence'] = $taskSequence;
						$tmpl['txtName'] = $RS->Field('Name');
						$tmpl['txtRolloverDescription'] = $RS->Field('Description');
						$tmpl['txtOwner'] = $RS->Field('FirstName') . ' ' . $RS->Field('LastName');
						$tmpl['txtDuration'] = $this->CalculateDuration($RS->Field('Duration'));

						switch($RS->Field('Priority')) {
							case "1": $tmpl['txtUrgency'] = "<img src=\"images/icons/icon_priority_low.gif\" title=\"". MSG_PRIORITY_LOW ."\" border=\"0\">"; $tmpl['txtRolloverUrgency'] = MSG_PRIORITY_LOW; break;
							case "2": $tmpl['txtUrgency'] = "<img src=\"images/icons/icon_priority_med.gif\" title=\"". MSG_PRIORITY_NORMAL ."\" border=\"0\">"; $tmpl['txtRolloverUrgency'] = MSG_PRIORITY_NORMAL; break;
							case "3": $tmpl['txtUrgency'] = "<img src=\"images/icons/icon_priority_high.gif\" title=\"". MSG_PRIORITY_HIGH ."\" border=\"0\">"; $tmpl['txtRolloverUrgency'] = MSG_PRIORITY_HIGH; break;
							default : $tmpl['txtUrgency'] = "<img src=\"images/icons/icon_priority_med.gif\" title=\"". MSG_PRIORITY_NORMAL ."\" border=\"0\">"; $tmpl['txtRolloverUrgency'] = MSG_PRIORITY_NORMAL;
						}

						$tmpl['txtDue'] = Format::date($RS->Field('EndDate'));
						$tmpl['txtColour'] = $project_details['Colour'];
						$tmpl['txtRolloverPercent'] = @number_format($RS->Field('PercentComplete')) . "% Complete";
						$tmpl['txtComplete'] = (@number_format($RS->Field('PercentComplete')) / 2 );
						$tmpl['txtIncomplete'] = 50 - $tmpl['txtComplete'];
						$tmpl['lblAsc']				 = MSG_ASCENDING;
						$tmpl['lblDesc']				= MSG_DESCENDING;

					// Display task comments
					$tmpl['txtComments'] = null;
					if ($comments)
					{
						$comments_sql = sprintf(SQL_TASKS_GET_COMMENTS, $taskID);
						$comments_list = $this->DB->Query($comments_sql);
						if ( is_array($comments_list) )
						{
							for ($i = 0; $i < count($comments_list); $i++)
							{
								if ( $comments_list[$i]['HoursWorked'] == 0 )
								{
									$hours = MSG_QUICK_UPDATE;
								}
								else
								{
									$hours = $this->CalculateDuration($comments_list[$i]['HoursWorked']);
								}
								$comment_tmpl['id'] = 'task'.$comments_list[$i]['ID'];
								$comment_tmpl['txtColour'] = $details['Colour'];
								$comment_tmpl['txtUsername'] = $comments_list[$i]['FirstName'] . ' ' . $comments_list[$i]['LastName'];
								$comment_tmpl['txtComment'] = Format::blocktext($comments_list[$i]['Body']);
								$comment_tmpl['txtDate'] = $comments_list[$i]['Date'];

								$comment_tmpl['txtWorked'] = $hours;
								$comment_tmpl['txtWorked'] .= ($comments_list[$i]['OutOfScope'] == 1) ? '*' : '';
								$comment_tmpl['txtEdit'] = null;
								$tmpl['txtComments'] .= $this->getTemplate('tasks_print_comment', $comment_tmpl);
							}
						}
					}

					$this->setTemplate('print_tasks_item', $tmpl);
					unset($tmpl);
					++$counter;
					$RS->MoveNext();
			}
			$this->setTemplate('print_tasks_footer');

				}
				else
				{
					$tmpl['MESSAGE'] = MSG_NO_TASKS_AVAILABLE;
					$tmpl['lblIcon'] = 'tasks';
					$this->setTemplate('eof', $tmpl);
				}
				$RS->Close();
				unset($RS);
			}

			if ($files) {
				$this->setTemplate('spacer');

				$SQL = sprintf(SQL_GET_PROJECT_FILES, $projectid, 'f.FileName', 'ASC');
				$RS =& new DBRecordset();
							$RS->Open($SQL, $this->DB, $limit, $offset);
							if (!$RS->EOF())
							{
									$tmpl['lblFile']		 = MSG_FILE;
									$tmpl['lblUploadedDate'] = MSG_UPLOADED;
									$tmpl['lblType']		 = MSG_FILE_TYPE;
									$tmpl['lblSize']		 = MSG_SIZE;
									$this->setTemplate('print_files_header', $tmpl);
									unset($tmpl);

									$counter = 1;
									while (!$RS->EOF())
									{
											if ($counter > 1) $this->setTemplate('print_files_spacer');

											$fileid = $RS->Field('ID');
											$tmpl['txtFileName'] = $RS->Field('FileName');
											$tmpl['txtMessage'] = ((Request::get('error', Request::R_INT) == 1) && (Request::get('fileid', Request::R_INT) == $RS->Field('ID'))) ? MSG_FILE_NOT_FOUND : '';
											$tmpl['txtType'] = substr($RS->Field('Type'), strpos($RS->Field('Type'),'/') + 1);
												if ($RS->Field('Linked') == 1)
														$tmpl['txtSize'] = MSG_NA;
												else
														$tmpl['txtSize'] = intval($RS->Field('Size') / 1024) . MSG_KB;
											$tmpl['txtUploaded'] = Format::date($RS->Field('Date'));


											$this->setTemplate('print_files_item', $tmpl);
											unset($tmpl);
											++$counter;
											$RS->MoveNext();
									}
									$this->setTemplate('print_files_footer');

							}
							else
							{
						$tmpl['MESSAGE'] = MSG_NO_FILES_AVAILABLE;
								$tmpl['lblIcon'] = 'files';
								$this->setTemplate('eof', $tmpl);
							}

			}

			$this->RenderPopup();

			//echo $this->DB->LastErrorMessage;
		}
		else
		{
				$this->ThrowError(2001);
		}
	}

	function TaskList() {

		$projectID = Request::get('projectid', Request::R_INT);
		$action = Request::get('action');
		if (!$this->User->HasUserItemAccess($this->ModuleName, $projectID, CU_ACCESS_READ))
		{
			$this->ThrowError(2001);
			return;
		}
		$project = $this->GetProjectDetails($projectID);
		$tmpl['projectID'] = $projectID;
		$html = '';

		$orderOptions = array(
			'task'			=> 'Name',
			'owner'			=> 'Owner',
			'progress'	=> 'PercentComplete',
			'priority'	=> 'Priority',
			'duration'	=> 'Duration',
			'effort'		=> 'HoursWorked',
			'starts'		=> 'StartDate',
			'ends'			=> 'EndDate'
		);
		list($order, $orderby) = Request::$GET->filterRequest('order', $orderOptions, array('sequence', 'sequence'));
		list($direction, $orderDir) = Request::$GET->filterOrderDirection();
		
		// Paging
		$limit = Settings::get('RecordsPerPage');
		$offset = Request::get('start');

		$SQL = sprintf(SQL_GET_PROJECT_TASKS_WITH_HOURS, $projectID, $orderby, $orderDir);

	  if (($offset == 'all') || ($offset == null))
		{
			$RS =& new DBRecordset();
			$RS->Open($SQL, $this->DB);
		}
		else
		{
			$RS =& new DBPagedRecordset();
			$RS->Open($SQL, $this->DB, $limit, $offset);
		}

		$tmpl['lblTask']				= MSG_TASK;
		$tmpl['lblOwner'] 			= MSG_OWNER;
		$tmpl['lblEstPercent']	= MSG_EST_PERCENT;
		$tmpl['lblPriority']		= MSG_PRIORITY;
		$tmpl['lblHours'] 			= MSG_HOURS_UPPERCASE;
		$tmpl['lblEst'] 				= MSG_EST;
		$tmpl['lblCom'] 				= MSG_COM;
		$tmpl['lblAct'] 				= MSG_ACT;
		$tmpl['lblLatestActivity'] = MSG_LATESTACTIVITY;
		$tmpl['lblStart'] 			= MSG_START;
		$tmpl['lblEnd'] 				= MSG_END;

		$tmpl['txtTasks'] = "";

		$ending = $this->getTemplate('project_view_task_item_ending', $tmpl);

		if (!$RS->EOF())
		{
		  $taskCounter = 0;
			$indentLevel = 0;
			$parent = null;
			while (!$RS->EOF())
			{
				$taskID = $RS->Field('ID');
				$tmpl['txtTaskID'] = $taskID;
				$tmpl['txtProjectID'] = $projectID;
				$tmpl['txtColour'] = $project['Colour'];
				$tmpl['txtDuration'] = Format::hours($RS->Field('Duration'));
				$tmpl['txtEndDate'] = Format::date($RS->Field('EndDate'));
				$tmpl['txtHoursCommitted'] = Format::hours($RS->Field('HoursCommitted'));
				$tmpl['txtHoursWorked'] = Format::hours($RS->Field('HoursWorked'));
				$tmpl['txtName'] = $RS->Field('Name');
				$tmpl['txtOwner'] = $RS->Field('FirstName') . ' ' . $RS->Field('LastName');
				$tmpl['txtPercentComplete'] = (int) $RS->Field('PercentComplete');
				$tmpl['txtLatestActivity'] = Format::date($RS->Field('LatestActivity'));
				$tmpl['txtStartDate'] = Format::date($RS->Field('StartDate'));
				$tmpl['txtUsername'] = $this->User->Fullname;
				$tmpl['txtDate'] = Format::date(date('Y-m-d'), TRUE, FALSE);
				$tmpl['txtPriority'] = Format::convert_priority($RS->Field('Priority'));
				$issues = $this->DB->ExecuteScalar(sprintf(SQL_COUNT_TASK_ISSUES, $taskID));
				if ($issues > 0) {
					$tmpl['txtName'] .= ' <span class="issue">'.MSG_ISSUE.'</span>';
				}

				// $task_row represents one task, excluding it's closing </li>
				// this is because we want to put subtasks inside the li.
				$task_row = $this->getTemplate('project_view_task_item', $tmpl);
				// add the row to the template variable

				//handle the indent factor
			  $indent = $RS->Field('Indent');
			  if ($action == 'ajaxtasklist_project') {
					$indent = 0;
				}
				
				if ($parent == null)
				{
					$parent = $taskID;
				}
				
				// first item is always depth 0.
				if (($indent == $indentLevel) || ($taskCounter == 0)) {
					// close off the previous element. 
					if ($taskCounter != 0) {
						$tmpl['txtTasks'] .= $ending;
					}

					$tmpl['txtTasks'] .= $task_row;
				  
				} else if ($indent > $indentLevel) 
				{
					// we only ever go up by one level. Anything else is retarded.
					// (love your comments)
					$indentLevel++;
					$tmpl['txtTasks'] .= '<!-- new sublist --><ul>' . PHP_EOL;

				  $tmpl['txtTasks'] .= $task_row;
				} else if ($indent < $indentLevel)
				{
					// we can go down multiples, but only down to zero.
					$base_indent = ($indent >= 0) ? $indent : 0;

					for($level = $indentLevel; $level > $base_indent; $level--)
					{
						// first close the item
						$tmpl['txtTasks'] .= $ending;
						// then close the ul.
						$tmpl['txtTasks'] .= '</ul><!-- close sublist -->' . PHP_EOL;
					}
				  $tmpl['txtTasks'] .= $task_row;

					$indentLevel = $base_indent;
				}
			  $taskCounter++;
			  $RS->MoveNext();
			}
			
			// now we are out of the loop, make sure we close off all the uls.
			for($level = $indentLevel; $level > 0; $level--)
			{
				// first close the item
				$tmpl['txtTasks'] .= $ending;
				$tmpl['txtTasks'] .= '</ul><!-- final close sublist -->';
			}
			
			// finally, close off the first li.
			$tmpl['txtTasks'] .= $ending;
			

		}
		else
		{
			$tmpl['MESSAGE'] = MSG_NO_TASKS_AVAILABLE;
			$tmpl['lblIcon'] = 'tasks';
			//$html .= $this->getTemplate('tasks_eof', $tmpl);
		}
		if (Request::get('action') == 'ajaxtasklist_project') {
	  $html .= $this->getTemplate('ajax_tasks_header_project', $tmpl);
	  echo $html;
	}
	else {
	  $html .= $this->getTemplate('tasks_header', $tmpl);
	  return $html;
	}
	
	}

	function DeleteComment() {
		$caller = Request::get('caller');
		$commentid = Request::get('commentid', Request::R_INT);
		$projectid = Request::get('projectid', Request::R_INT);
		$taskid = Request::get('taskid', Request::R_INT);
		$comment = $this->DB->QuerySingle(sprintf(SQL_GET_COMMENT_FOR_DELETE, $commentid));

		// Get day ID
		list($date, $time) = explode(' ', $comment['Date']);
		list($y, $m, $d) = explode('-', $date);
		$dayID = $this->DB->ExecuteScalar(sprintf(SQL_GET_DAY_ID_BY_DATE, $d, $m, $y));

		// Get resource ID
		$resourceID = $this->DB->ExecuteScalar(sprintf(SQL_GET_RESOURCE_ID, $comment['UserID']));

		if (($this->User->HasModuleItemAccess('administration', CU_ACCESS_ALL, CU_ACCESS_READ) ||
			(($comment['UserID'] == $this->User->ID) && (Settings::get('TaskLogEdit') == 1))) &&
			(is_numeric($commentid)) && (is_numeric($projectid)) && (is_numeric($taskid)))
		{
			// Delete comment.
			$this->DB->Execute(sprintf(SQL_DELETE_COMMENT, $commentid));

			// Update task budget.
			$chargeRate = $this->DB->ExecuteScalar(sprintf(SQL_GET_CHARGE_RATE, $comment['UserID']));
			$rate = ($chargeRate > 0) ? $chargeRate : Settings::get('HourlyRate');
			$charge = $comment['HoursWorked'] * $rate;
			$SQL = sprintf(SQL_UPDATE_TASK_UPON_DELETE, $taskid, $comment['HoursWorked'], $charge);
			$this->DB->Execute($SQL);

			// subtract hours from the task resource day table record
			$SQL = sprintf(SQL_UPDATE_TASK_RESOURCE_DAY, -$comment['HoursWorked'], $taskid, $resourceID, $dayID);
			$this->DB->Execute($SQL);

			if((isset($caller))&&($caller=="ajax")){
				echo "{success:1}";
			} else {
				Response::redirect('index.php?module=projects&action=taskview&projectid='.$projectid.'&taskid='.$taskid);
			}
		}
		else
		{
			if((isset($caller))&&($caller=="ajax")){
				echo "{successs:0}";
			}else{
				$this->ThrowError(2001);
			}
		}
	}

	function TaskCommitmentData() {
		$maxDayLength = '&maxDayLength=' . MAX_DAY_LENGTH;
		$taskID = Request::get('taskID', Request::R_INT);
		$taskName = '&taskName=';
		$taskDurationEstimate = '&taskDurationEstimate=';
		$taskDayIDs = '&taskDayIDs=';

		// resources
		$writePermission = '&writePermission=';
		$taskResourcesCount = '&taskResourcesCount=';
		$taskResourcesIDs	= '&taskResourcesIDs=';
		$taskResourcesUserIDs  = '&taskResourcesUserIDs=';
		$taskResourcesNames = '&taskResourcesNames=';
		$resourcesAssignNames = '&resourcesAssignNames=';
		$resourcesAssignIDs = '&resourcesAssignIDs=';
		$hoursCompletedAllTime = '&hoursCompletedAllTime=';
		$hoursAvailable = '&hoursAvailable=';
		$hoursCommitted = '&hoursCommitted=';
		$hoursCompleted = '&hoursCompleted=';
		$hoursCommittedTotal = '&hoursCommittedTotal=';
		$hoursWorked = '&hoursWorked=';
		$todayIndex = '&todayIndex=';

		// get the total hours completed on this task anytime
		$hoursCompletedAllTime .= $this->DB->ExecuteScalar(sprintf(SQL_GET_HOURSCOMPLETED_ALL_TIME,$taskID));

		// get the duration of this task
		$taskSQL = sprintf(GET_TASK_DATES,$taskID);
		$task = $this->DB->QuerySingle($taskSQL);

		if ($this->User->HasUserItemAccess($this->ModuleName, $task['ProjectID'], CU_ACCESS_WRITE)) $writePermission .= 1;
		else $writePermission .= 0;

		// store the dates in the y-m-d format
		$taskDateStart = $task['StartDate'];
		$taskDateEnd = $task['EndDate'];

		// convert the start date to epoch
		$task['StartDate'] = strtotime($task['StartDate'] . 'GMT');
		// check for no end date
		if ($task['EndDate'] == '0000-00-00') $task['EndDate'] = $task['StartDate'];
		else $task['EndDate'] = strtotime($task['EndDate'] . 'GMT');

		$datesCount = round(($task['EndDate'] - $task['StartDate']) / 86400) + 1;

		$temptaskName = html_entity_decode($task['Name']);
		if (strlen($temptaskName) > 18) $temptaskName = substr($temptaskName,0,17).'..';
		$taskName .= rawurlencode($temptaskName);

		$taskDurationEstimate .= urlencode($task['DurationEstimate']);

		// find the ids for that date range
		$taskDaysSQL = sprintf(SQL_GET_DAYID_EPOCH,$task['StartDate'],$task['EndDate']);
		$taskDays = $this->DB->Query($taskDaysSQL);

		for ($i = 0; $i < count($taskDays); $i++) {
			$taskDayIDs .= ($i ? ',' : '').$taskDays[$i]['DayID'];
		}

		// get the index of today in the days data arrays
		// comparison of time() in epoch to DB-stored epochs results in matches at GMT, add offset for current locale to make comparisons at current timezone
		$epochOffset = 0 - mktime(0,0,0,1,1,1970);
		$currentEpoch = time() + $epochOffset;
		if ($currentEpoch < $taskDays[0]['Epoch']) $todayIndex .= 0;
		else if ($currentEpoch > $taskDays[count($taskDays) - 1]['Epoch']) $todayIndex .= count($taskDays);
		else {
			for ($i = 0; $i < count($taskDays); $i++) {
				if ($currentEpoch >= $taskDays[$i]['Epoch'] && $currentEpoch < $taskDays[$i + 1]['Epoch']) $todayIndex .= $i;
			}
		}

		// get a list of users that can be assigned to this task
		$resourceUserListSQL = sprintf(SQL_RESOURCE_USERS);
		$resourceUserList = $this->DB->Query($resourceUserListSQL);

		if ($resourceUserList) {
			for ($i = 0; $i < count($resourceUserList); $i++) {
				// do not show users assigned on this task
				$resourceAssignedSQL = sprintf(SQL_CHECK_IF_ASSIGNED_TO_TASK,$resourceUserList[$i]['ID'],$taskID);
				$resourceAssigned = $this->DB->QuerySingle($resourceAssignedSQL);
				if (!$resourceAssigned['ResourceID']) {
					$resourcesAssignNamesList .= ($resourcesAssignNamesList ? ',' : '') . $this->encodeCommas($resourceUserList[$i]['FirstName'].' '.$resourceUserList[$i]['LastName']);
					$resourcesAssignIDsList .= ($resourcesAssignIDsList ? ',' : '') . $resourceUserList[$i]['ID'];
				}
			}
		}
		$resourcesAssignNames .= $resourcesAssignNamesList;
		$resourcesAssignIDs .= $resourcesAssignIDsList;

		// get all the resources for this task
		$resourcesSQL = sprintf(SQL_GET_TASK_RESOURCES,$taskID);
		$resources = $this->DB->Query($resourcesSQL);

		if ($resources[0]['ID']) {
			$taskResourcesCount .= count($resources);

			// create the where clause to filter resources
			$resourceIDInWhere = '';
			for ($i = 0; $i < count($resources); $i++) {
				$resourceIDInWhere .= ($i ? ',' : '') . $resources[$i]['ID'];
			}
			if ($i == 1) $resourceIDInWhere = ' = ' . $resourceIDInWhere;
			else $resourceIDInWhere = ' IN (' . $resourceIDInWhere . ')';

			// get the avalibility for all the resources of this task
			$resourceAvailabilityWhereInRangeTableRD = 'tblResourceDay.DayID >= ' . $taskDays[0]['DayID'] . ' AND tblResourceDay.DayID <= ' . $taskDays[count($taskDays) - 1]['DayID'];
			$resourceAvailabilitySQL = sprintf(GET_HOURS_AVAILABLE_ALL_COMMITMENT,$resourceIDInWhere,$resourceAvailabilityWhereInRangeTableRD);
			$resourceAvailabilityTotalCommitment = $this->DB->Query($resourceAvailabilitySQL);

			// get the commitment and the completed hours of those resouces to this task
			$resourceAvailabilityWhereInRangeTableTRD = 'tblTaskResourceDay.DayID >= ' . $taskDays[0]['DayID'] . ' AND tblTaskResourceDay.DayID	<= ' . $taskDays[count($taskDays) - 1]['DayID'];
			$hoursCommittedSQL = sprintf(SQL_GET_HOURS_COMMITTED_ON_TASK, $resourceIDInWhere, $taskID, $resourceAvailabilityWhereInRangeTableTRD);
			$resourceHoursCommitteds = $this->DB->Query($hoursCommittedSQL);

			// for each resource for each day check for data on availabilty, commitment, completed and commitment to other tasks
			// and build the strings to pass to flash
			$resourceAvailabilityPointer = 0;
			$resourceHoursCommittedsPointer = 0;
		}
		else $taskResourcesCount .= '0';

		for ($i = 0; $i < count($resources); $i++) {
			$taskResourcesIDs .= ($i ? ',' : '').$resources[$i]['ID'];
			$taskResourcesUserIDs .= ($i ? ',' : '').$resources[$i]['UserID'];
			if (strlen($resources[$i]['FullName']) > 18) $resources[$i]['FullName'] = substr($resources[$i]['FullName'],0,17).'..';
			$taskResourcesNames .= ($i ? ',' : '') . $this->encodeCommas($resources[$i]['FullName']);

			// initialize the list strings for each resource
			$hoursAvailableList = '';
			$hoursCommittedList = '';
			$hoursCompletedList = '';
			$hoursCommittedTotalList = '';

			for ($j = 0; $j < count($taskDays); $j++) {
			// send flash the total commitment as it float val it will take off the committed time for the current task
			// must compare the resourceID to be sure
			if ($resourceAvailabilityTotalCommitment[$resourceAvailabilityPointer]['DayID'] == $taskDays[$j]['DayID'] && $resources[$i]['ID'] == $resourceAvailabilityTotalCommitment[$resourceAvailabilityPointer]['ResourceID']) {
				$hoursAvailableTemp = $resourceAvailabilityTotalCommitment[$resourceAvailabilityPointer]['HoursAvailable'];
				$hoursCommittedTotalTemp = $resourceAvailabilityTotalCommitment[$resourceAvailabilityPointer]['HoursCommittedCache'];
					$resourceAvailabilityPointer++;
				}
				if ($resourceHoursCommitteds[$resourceHoursCommittedsPointer]['DayID'] == $taskDays[$j]['DayID'] && $resources[$i]['ID'] == $resourceHoursCommitteds[$resourceHoursCommittedsPointer]['ResourceID']) {
					$hoursCommittedTemp = $resourceHoursCommitteds[$resourceHoursCommittedsPointer]['HoursCommitted'];
					$hoursCompletedTemp = $resourceHoursCommitteds[$resourceHoursCommittedsPointer]['HoursCompleted'];
					$resourceHoursCommittedsPointer++;
				}

				if ($hoursAvailableTemp) $hoursAvailableList .= ($hoursAvailableList || $j ? ',' : '') . $hoursAvailableTemp;
				else $hoursAvailableList .= ($j ? ',' : '');
				$hoursAvailableTemp = '';
				if ($hoursCommittedTemp) $hoursCommittedList .= ($hoursCommittedList || $j ? ',' : '') . $hoursCommittedTemp;
				else $hoursCommittedList .= ($j ? ',' : '');
				$hoursCommittedTemp = '';
				if ($hoursCompletedTemp) $hoursCompletedList .= ($hoursCompletedList || $j ? ',' : '') . $hoursCompletedTemp;
				else $hoursCompletedList .= ($j ? ',' : '');
				$hoursCompletedTemp = '';
			if ($hoursCommittedTotalTemp) $hoursCommittedTotalList .= ($hoursCommittedTotalList || $j ? ',' : '') . $hoursCommittedTotalTemp;
			else $hoursCommittedTotalList .= ($j ? ',' : '');
			$hoursCommittedTotalTemp = '';

			}
			$hoursAvailable .= ($i ? ';' : '') . $hoursAvailableList;
			$hoursCommitted .= ($i ? ';' : '') . $hoursCommittedList;
			$hoursCompleted .= ($i ? ';' : '') . $hoursCompletedList;
			$hoursCommittedTotal .= ($i ? ';' : '') . $hoursCommittedTotalList;
		}

		// $today = '2003-05-01';
		$today = date('Y-m-d',time());
		$SQL = sprintf(SQL_GET_PROJECT_COLOUR_FOR_TASK,$taskID);
		$projectColour = $this->DB->QuerySingle($SQL);
		$colour = $projectColour['Colour'];
		if (substr($colour,0,1) == '#') $colour = substr($colour,1);
		// $colour = '3399ff';
		echo '&datesCount='.$datesCount.'&projectID='.$task['ProjectID'].'&taskID='.$taskID.$writePermission;
		echo $maxDayLength.'&taskDateStart='.$taskDateStart.'&taskDateEnd='.$taskDateEnd.'&today='.$today.'&colour=0x'.$colour.'&dayLength='.DAY_LENGTH;
		echo $taskName.$taskDurationEstimate.$taskDayIDs.$taskResourcesCount.$taskResourcesIDs.$taskResourcesUserIDs.$taskResourcesNames.$todayIndex.$hoursCompletedAllTime.$hoursAvailable.$hoursCommitted.$hoursCompleted.$hoursCommittedTotal;
		echo $resourcesAssignNames . $resourcesAssignIDs;
		echo '&dummy=1';
	}

	function TaskCommitmentSave() {
		$taskID = Request::get('taskID', Request::R_INT);
		$resourceID = Request::get('resourceID', Request::R_INT);
		$resourceToAssignID = Request::get('resourceToAssignID', Request::R_INT);
		$resourceToRemoveID = Request::get('resourceToRemoveID', Request::R_INT);
		$dayID = Request::get('dayID', Request::R_INT);
		$committedHours = Request::get('committedHours');
		$oldCommittedHours = Request::get('oldCommittedHours');

		// get the task name and project id for permissions  and posibily the email
		$SQL = sprintf(SQL_GET_EMAIL_SUBJECT_DETAILS, $taskID);
		$subjectdetails = $this->DB->QuerySingle($SQL);

		if ($this->User->HasUserItemAccess($this->ModuleName, $subjectdetails['ProjectID'], CU_ACCESS_WRITE)) {
			if ($taskID && $resourceToRemoveID) {
				$this->DB->Execute(sprintf(SQL_UNASSIGN_TASK_RESOURCE,$taskID,$resourceToRemoveID));
				//Select all days committed
				$result = $this->DB->Query(sprintf(SQL_GET_HOURS_COMMITTED_ON_TASK, " = $resourceToRemoveID", $taskID, 'DayID > 0'));
				//update cache and remove hours from cache
				foreach($result as $key => $value) {
					$this->DB->Execute(sprintf(SQL_UPDATE_RESOURCE_DAY_COMMITMENT_CACHE, $resourceToRemoveID,$value['DayID'], 0 - $value['HoursCommitted']));
					$this->DB->Execute(sprintf(SQL_UPDATE_TASK_RESOURCE_DAY_COMMITMENT,$taskID,$resourceToRemoveID,$value['DayID'],0));
				}
				echo 'taskAssignSaveStatus=OK';
			}
			else if ($taskID && $resourceToAssignID) {
				// the first time a resource is assigned to this task email them
				$resourceAssignedSQL = sprintf(SQL_CHECK_IF_ASSIGNED_TO_TASK,$resourceToAssignID,$taskID);
				$resourceAssigned = $this->DB->QuerySingle($resourceAssignedSQL);
				if (!$resourceAssigned) {
					// get the users details
					$userSQL = sprintf(SQL_GET_RESOURCE_EMAIL,$resourceToAssignID);
					$user = $this->DB->QuerySingle($userSQL);
					if (strlen($user['EmailAddress']) > 0) {
						$mailer = new SMTPMail();
						$mailer->FromName = SYS_FROMNAME;
						$mailer->FromAddress = SYS_FROMADDR;
						$mailer->Subject = sprintf(MSG_TASK_EMAIL_ASSIGNED_SUBJECT, "- {$subjectdetails['ClientName']}: {$subjectdetails['ProjectName']}: {$subjectdetails['TaskName']}");
						//$mailer->Priority = 1;
						$url = url::build_url('projects', 'taskview', "taskid=$taskID&projectid={$subjectdetails['ProjectID']}");
						$mailer->ToName = $user['FirstName'];
						$mailer->ToAddress = $user['EmailAddress'];
						$mailer->Body = sprintf(MSG_TASK_EMAIL_ASSIGNED_BODY, $user['FirstName'], $subjectdetails['TaskName'], $url);
						$mailer->Execute();
						unset($mailer);
					}
				}
				// insert a record into tblTaskResource
				$taskResourceDayInsertSQL = sprintf(SQL_ASSIGN_RESOURCE,$taskID,$resourceToAssignID);
				$this->DB->Execute($taskResourceDayInsertSQL);

				echo 'taskAssignSaveStatus=OK&resourceToAddTaskID='.$resourceToAssignID;
			}
			else if ($taskID) {
				// update the committed hours cache
				// sum the diffrence of old and new committed hours to the current value
				$resourceDayUpdateSQL = sprintf(SQL_UPDATE_RESOURCE_DAY_COMMITMENT_CACHE,$resourceID,$dayID,$committedHours - $oldCommittedHours);
				$this->DB->Execute($resourceDayUpdateSQL);

				// get the completed hours for the task for that resource on that task day
				$hasCompletedHoursSQL = sprintf(SQL_GET_COMPLETED_HOURS_FOR_DAY,$taskID,$resourceID,$dayID);
				$hasCompletedHours = $this->DB->QuerySingle($hasCompletedHoursSQL);

				//if ($committedHours > 0) {
					// update the task resource table with the committed hours if it's already there
					if ($hasCompletedHours['DayID']) {
						$taskResourceDayUpdateSQL = sprintf(SQL_UPDATE_TASK_RESOURCE_DAY_COMMITMENT,$taskID,$resourceID,$dayID,$committedHours);
						$this->DB->Execute($taskResourceDayUpdateSQL);
					}
					else {
						// insert the new commitment
						$taskResourceDayInsertSQL = sprintf(SQL_INSERT_TASK_RESOURCE_DAY_COMMITMENT,$taskID,$resourceID,$dayID,$committedHours,0.00);
						$this->DB->Execute($taskResourceDayInsertSQL);
					}
				//}
				echo 'taskEditSaveStatus=OK';
			}

		}
		else {
			echo 'taskEditSaveStatus=AccessDenied';
		}
	}

	function TaskMove() {
		$modHeader = MSG_MOVE_TASK;
		$taskid = Request::get('taskid', Request::R_INT);
		$projectid = Request::get('projectid', Request::R_INT);
		if (is_numeric($projectid) && is_numeric($taskid) && $this->User->HasUserItemAccess($this->ModuleName, $projectid, CU_ACCESS_WRITE)) {
			$projects_access_list = $this->User->GetUserItemAccess('projects', CU_ACCESS_READ);
			if ($projects_access_list == '-1') {
				$ItemList = $this->DB->Query(SQL_GET_PROJECTS);
			} else {
				$ItemList = $this->DB->Query(sprintf(SQL_GET_PROJECTS_IN, $projects_access_list));
			}
			for ($i = 0, $count = count($ItemList); $i < $count; $i++) {
				if ($this->User->HasUserItemAccess($this->ModuleName, $ItemList[$i][0], CU_ACCESS_WRITE)) {
					$extra = '';
					if ( isset($ItemList[$i][2]) ) {
						$extra = $ItemList[$i][2] . ' / ';
					}
					if ($ItemList[$i][0] == $projectid) $selected = 'selected';
					$tmpl_itemlist .= sprintf('<option value="%1$s" %3$s>%2$s</option>', $ItemList[$i][0], $extra.$ItemList[$i][1], $selected);
					$selected='';
				}
			}
			$tmpl['selectItems'] = $tmpl_itemlist;
			$tmpl['TASKID'] = $taskid;
			$tmpl['PROJECTID'] = $projectid;
			$this->setTemplate('task_move', $tmpl);

			$this->setHeader($modTitle);
			$this->setModule($modHeader, $modAction);
			$this->Render();
		}
		else
			$this->ThrowError(2001);

	}

	function TaskMoveSave() {

		$projectid = Request::post('projectid');
		$movetoproject = Request::post('movetoproject');
		$taskid = Request::post('taskid');
		if (is_numeric($movetoproject) && is_numeric($taskid) && $this->User->HasUserItemAccess($this->ModuleName, $movetoproject, CU_ACCESS_WRITE)) 
		{
			$task = new Task($taskid);
			$task->ProjectID = $movetoproject;
			
			Response::redirect('index.php?module=projects&action=taskview&projectid='.$movetoproject.'&taskid='.$taskid);
		}
		else
			$this->ThrowError(2001);
	}

	function TaskNew() {
		$projectid = Request::get('projectid', Request::R_INT);
		if (is_numeric($projectid))
		{
			if ($this->User->HasUserItemAccess($this->ModuleName, $projectid, CU_ACCESS_WRITE))
			{
				$this->TaskDisplayForm($projectid);
			}
			else
			{
				$this->ThrowError(2001);
			}
		}
		else
		{
			Response::redirect('index.php?module=projects');
		}
	}

	function TaskEdit() {
		$projectid = Request::get('projectid', Request::R_INT);
		$taskid = Request::get('taskid', Request::R_INT);
		$SQL = sprintf(SQL_TASKS_GET_DETAILS, $taskid);
		$taskdetails = $this->DB->QuerySingle($SQL);
		$projectid = $taskdetails['ProjectID'];  // Override project ID. Permissions system can be worked around otherwise.
		if (is_numeric($projectid)) {
			if ($this->User->HasUserItemAccess($this->ModuleName, $projectid, CU_ACCESS_WRITE)) {
				if (is_numeric($taskid)) {
					$this->TaskDisplayForm($projectid, $taskid);
				} else {
					$this->TaskDisplayForm($projectid);
				}
			} else {
				$this->ThrowError(2001);
			}
		} else {
			Response::redirect('index.php?module=projects');
		}
	}

	function TaskDisplayForm($projectid, $taskid = 0) {

		$details = $this->GetProjectDetails($projectid);
		$modAction[] = '<a href="javascript:SubmitForm();">'.MSG_SAVE.'</a>';
		if ( $taskid == 0 ) {
			$modTitle = MSG_PROJECTS;
			$modHeader = MSG_NEW_TASK;
		} else {
			$modTitle = MSG_PROJECTS;
			$modHeader = MSG_EDIT_TASK;
			//$modAction[] = ($this->User->HasUserItemAccess($this->ModuleName, $projectid, CU_ACCESS_WRITE)) ? '<a href="index.php?module=projects&amp;action=tasknew&amp;projectid=' . $projectid . '">' . MSG_NEW_TASK . '</a>' : '';
		}

		$this->ProjectTabs($projectid);

		$tmpl['txtProjectID'] = $projectid;
		$tmpl['txtTaskID'] = $taskid;
		$tmpl['DAY_LENGTH'] = DAY_LENGTH;
		$tmpl['date_format'] = $this->date_format[Settings::get('DateFormat')];

		$tmpl['lblTargetBudget'] = '';
		$tmpl['lblActualBudget'] = '';

		if ($this->User->HasModuleItemAccess('budget', CU_ACCESS_ALL, CU_ACCESS_READ)) {
			$tmpl['lblTargetBudget'] = MSG_BUDGET_TARGET;
			$tmpl['lblActualBudget'] = MSG_BUDGET_ACTUAL;
		}

		$tmpl['txtTargetBudget'] = '';
		$tmpl['txtActualBudget'] = '';
		$tmpl['lblProject'] = MSG_PROJECT;
		$tmpl['txtProject'] = $details['ProjectName'];

		if ( $taskid == 0 ) {
			$tmpl['Copy'] = '';
			$tmpl['txtHeading'] = MSG_NEW_TASK;
			$tmpl['txtProjectList'] = '';
			$tmpl['txtName'] = '';
			$tmpl['txtDescription'] = '';
			$tmpl['txtLatestActivity'] = Format::date_time(date('Y-m-d h:m:s'), Settings::get('DateFormat'));
			$tmpl['txtStartDate']	  = Format::date(date('Y-m-d'), TRUE, FALSE);
			$tmpl['txtEndDate']		= $tmpl['txtStartDate'];
			$tmpl['txtRelatedURL'] = '';
			$tmpl['lblDelete'] = '';
			$tmpl['taskDependencies'] = MSG_SAVE_TASK_FIRST_DEPENDENCIES;


			$duration = '';
			$ownerid = $this->User->ID;
			$priority = CU_DEFAULT_PRIORITY;
			$complete = 0;

			if ($this->User->HasModuleItemAccess('budget', CU_ACCESS_ALL, CU_ACCESS_READ)) {
				$tmpl['txtTargetBudget'] = '<input type="text" name="targetbudget" class="TaskEdit_tf" style="width:70px" value="">';
				$tmpl['txtActualBudget'] = '<input type="text" name="actualbudget" class="TaskEdit_tf" style="width:70px" value="">';
			}


		} else {
			$action = Request::get('action');
			$tmpl['Copy'] = ($action == "taskcopy") ? '<input type="hidden" name="copy" value="1">' : '';
			$tmpl['txtProjectList'] = '';
			$tmpl['txtHeading'] = ($action == 'taskcopy') ? MSG_COPY_TASK_DETAILS : MSG_EDIT_TASK_DETAILS;
			if ($action == "taskcopy") {
				$modTitle = MSG_PROJECTS;
				$modHeader = MSG_COPY_TASK_DETAILS;

				$projects_access_list = $this->User->GetUserItemAccess('projects', CU_ACCESS_READ);
				if ($projects_access_list == '-1') {
					$ItemList = $this->DB->Query(SQL_GET_PROJECTS);
				} else {
					$ItemList = $this->DB->Query(SQL_GET_PROJECTS_IN, $projects_access_list);
				}

				$tmpl_itemlist .= '<select class="edit" name="copytoproject">';
				for ($i = 0, $count = count($ItemList); $i < $count; $i++) {
					if ($this->User->HasUserItemAccess($this->ModuleName, $ItemList[$i][0], CU_ACCESS_WRITE)) {
						$extra = '';
						if ( isset($ItemList[$i][2]) ) {
							$extra = $ItemList[$i][2] . ' / ';
						}
						if ($ItemList[$i][0] == $projectid) $selected = 'selected';
						$tmpl_itemlist .= sprintf('<option value="%1$s" %3$s>%2$s</option>', $ItemList[$i][0], $extra.$ItemList[$i][1], $selected);
						$selected='';
					}
				}
				$tmpl_itemlist .= '</select>';
				$tmpl['txtProjectList'] = $tmpl_itemlist;
			}
			$SQL = sprintf(SQL_TASKS_GET_DETAILS_ALL, $taskid);
			$task = $this->DB->QuerySingle($SQL);
			$suffix = ($action == 'taskcopy') ? MSG_COPY : MSG_EDIT;
			$modHeader = $task['Name']. ' '. $suffix;


			$task['Description'] = preg_replace( '<a href=".*?">', '', $task['Description'] );
			$task['Description'] = str_replace( '</a>', '', $task['Description'] );
			$task['Description'] = str_replace( '<>', '', $task['Description'] );

			$tmpl['txtName'] = htmlspecialchars($task['Name']);
			$tmpl['txtDescription'] = $task['Description'];
			$tmpl['txtStartDate'] = (intval($task['StartDate']) > 0) ? $task['StartDate'] : '';
			$tmpl['txtEndDate'] = (intval($task['EndDate']) > 0) ? $task['EndDate'] : '';
			$tmpl['txtStartDate'] = Format::date($tmpl['txtStartDate'], TRUE, FALSE);
			$tmpl['txtEndDate'] = Format::date($tmpl['txtEndDate'], TRUE, FALSE);

			$tasksSQL = sprintf(SQL_PROJECT_TASKS, $projectid, $taskid);
			$tasks = $this->DB->Query($tasksSQL);
			$dependenciesSQL = sprintf(SQL_PROJECT_TASK_DEPENDENCIES, $taskid);
			$dependencies = $this->DB->Query($dependenciesSQL);
			$taskDependenciesCurrent = '';
			$taskDependenciesSelect = '';
			for ($i = 0; $i < count($dependencies); $i++) { // each dependency for this task
				for ($j = 0; $j < count($tasks); $j++) {
					if ($tasks[$j]['ID'] == $dependencies[$i]['TaskDependencyID'])
						break;
				}

				$tmplDep = array();
				if ($dependencies[$i]['DependencyType'] == 1)
					$tmplDep['msg'] = sprintf(MSG_TASK_DEPENDENCY_THIS_STARTS_WHEN_TASK_FINISHES, '<strong>'.$dependencies[$i]['Name'].'</strong>');
				if ($dependencies[$i]['DependencyType'] == 2)
					$tmplDep['msg'] = sprintf(MSG_TASK_DEPENDENCY_THIS_STARTS_WHEN_TASK_STARTS, '<strong>'.$dependencies[$i]['Name'].'</strong>');
				if ($dependencies[$i]['DependencyType'] == 3)
					$tmplDep['msg'] = sprintf(MSG_TASK_DEPENDENCY_THIS_FINISHES_WHEN_TASK_FINISHES, '<strong>'.$dependencies[$i]['Name'].'</strong>');

				$tmplDep['projectID'] = $projectid;
				$tmplDep['taskID'] = $taskid;
				$tmplDep['otherTaskID'] = $dependencies[$i]['TaskDependencyID'];
				$tmplDep['dependency'] = $dependencies[$i]['DependencyType'];
				$taskDependenciesCurrent .= $this->getTemplate('tasks_form_dependencies', $tmplDep);
			}

			for ($i = 0; $i < count($tasks); $i++) {	// each task in this project
				for ($j = 0; $j < count($dependencies); $j++) { // each dependency for this task
					if ($dependencies[$j]['TaskDependencyID'] == $tasks[$i]['ID']) {	// current dependency is on current task
						break;  // break before end of dependencies
					}
				}

				if ($j == count($dependencies)) {   // this task was not found in dependencies list
					$taskDependenciesSelect .= '<option value="'.$tasks[$i]['ID'].',1">'.sprintf(MSG_TASK_DEPENDENCY_THIS_STARTS_WHEN_TASK_FINISHES, $tasks[$i]['Name']).'</option>';
					$taskDependenciesSelect .= '<option value="'.$tasks[$i]['ID'].',2">'.sprintf(MSG_TASK_DEPENDENCY_THIS_STARTS_WHEN_TASK_STARTS, $tasks[$i]['Name']).'</option>';
					$taskDependenciesSelect .= '<option value="'.$tasks[$i]['ID'].',3">'.sprintf(MSG_TASK_DEPENDENCY_THIS_FINISHES_WHEN_TASK_FINISHES, $tasks[$i]['Name']).'</option>';
				}
			}

			if (!$taskDependenciesCurrent)
				$taskDependenciesCurrent = '';
			$tmpl['taskDependenciesCurrent'] = $taskDependenciesCurrent;
			$tmpl['taskDependenciesSelect'] = $taskDependenciesSelect;

			$tmpl['txtRelatedURL'] = $task['RelatedURL'];
			$duration = $task['Duration'];
			$ownerid = $task['Owner'];
			$priority = $task['Priority'];
			$complete = $task['PercentComplete'];

			if ($this->User->HasModuleItemAccess('budget', CU_ACCESS_ALL, CU_ACCESS_READ)) {
				$tmpl['txtTargetBudget'] = '<input type="text" name="targetbudget" class="TaskEdit_tf" style="width:70px" value="'.htmlspecialchars($task['TargetBudget']).'">';
				$tmpl['txtActualBudget'] = '<input type="text" name="actualbudget" class="TaskEdit_tf" style="width:70px" value="'.htmlspecialchars($task['ActualBudget']).'">';
			}

		}

		$users = null;
		$users_sql = sprintf(SQL_GET_USERS);
		$users_list = $this->DB->Query($users_sql);
		if ( is_array($users_list) ) {
			$users_count = count($users_list);
			for ($i = 0; $i < $users_count; $i++) {
				$users .= sprintf('<option value="%s"%s>%s</option>',$users_list[$i]['ID'], ($users_list[$i]['ID'] == $ownerid) ? ' SELECTED' : '',$users_list[$i]['FirstName'].' '.$users_list[$i]['LastName']);
			}
		}

		$priorities = null;
		$priority_sql = sprintf(SQL_GET_PRIORITY);
		$priority_list = $this->DB->Query($priority_sql);
		if ( is_array($priority_list) ) {
			$priority_count = count($priority_list);
			for ($i = 0; $i < $priority_count; $i++) {
				$priorities .= sprintf('<option value="%s"%s>%s</option>',$priority_list[$i]['ID'], ($priority_list[$i]['ID'] == $priority) ? ' SELECTED' : '',$priority_list[$i]['Name']);
			}
		}
		$tmpl['selectPriority'] = $priorities;
		$tmpl['selectUsers'] = $users;
		$tmpl['selectPercentage'] = $this->SelectPercentage($complete);

		if ( $duration > DAY_LENGTH && Settings::get('ConvertToDays') > 0 ) {
			$duration = round(($duration / DAY_LENGTH), 1);
			$tmpl['txtHours'] = '';
			$tmpl['txtDays'] = ' SELECTED';
		} else {
			$tmpl['txtHours'] = ' SELECTED';
			$tmpl['txtDays'] = '';
		}
		$tmpl['txtDuration'] = $duration;

		// Create Sequence number drop down.
		$rows = $this->DB->Query( sprintf( SQL_SELECT_TASK_SEQUENCES, $projectid ) );
		if ( !is_array( $rows ) ) $rows = array();
		foreach ( $rows as $row )
		{
			$selected = ( $row['Sequence'] == $task['Sequence'] ) ? ' selected' : '';
			$tmpl['sequenceOptions'] .= '<option value="'.$row['Sequence'].'"'.$selected.'>'.$row['Sequence'].'</option>';
		}

		// Add an extra sequence number if this is a new task.
		$extraSeq = $row['Sequence'] + 1;
		if ( $taskid == 0 )
			$tmpl['sequenceOptions'] .= '<option value="'.$extraSeq.'">'.$extraSeq.'</option>';

		$this->setTemplate('tasks_form', $tmpl);

		$this->setModule($modHeader, $modAction);
		$this->Render();
	}

	function TaskSave() 
	{
		$id = Request::post('taskid', Request::R_INT);
		// do we need to do more checks here?
		$task = new Task($id);

		// check why we whitelist a springboard caller?
		if ( ! ( ($this->User->HasUserItemAccess($this->ModuleName, Request::post('projectid', Request::R_INT), CU_ACCESS_WRITE) || Request::post('caller') == 'springboard') ) )
		{
			$this->ThrowError(2001);
			return;
		}

		$task->update_from_postdata(array(
			'Name' => 'taskname',
			'ProjectID' => 'projectid',
			'Owner' => 'owner',
			'Duration' => 'duration',
			'Priority' => 'priority',
			'Description' => 'description',
			'RelatedURL' => 'relatedurl',
		));

		// if the task doesn't exist, put it on the bottom.
		if ( ! $task->exists )
		{
			$task->Sequence = $task->project->get_highest_sequence() + 1;
		}

		$task->Status = (Request::post('complete') == 100) ? 1 : 0;
		
		$sd = Request::post('startdate');
		$task->StartDate  = ($sd == '' || $sd == '--') ? null : Format::parse_date($sd);
		$ed = Request::post('enddate');
		$task->EndDate  = ($ed == '' || $ed == '--') ? null : Format::parse_date($ed);

		// hokay now we have all the post data loaded up.
		$copy = Request::post('copy');
		if ($copy)
		{
			$task->ProjectID = Request::post('copytoproject');
		}

		if ($this->User->HasModuleItemAccess('budget', CU_ACCESS_ALL, CU_ACCESS_WRITE)) 
		{
			$task->TargetBudget = Request::post('targetbudget');
		}

		$task->commit();
		$this->mailOnTaskSave($task->ProjectID, $task->ID, TRUE);

		// finally, do some output. 
		if (Request::post('caller') == 'springboard')
		{
			$saveAndNewParam = (Request::post('saveandnew', Request::R_INT) == 1) ? '&new=1' : '';
			Response::redirect('index.php?module=springboard&action=taskview&taskid=' . $task->ID . '&projectid=' . $task->ProjectID . $saveAndNewParam);
		} else 
		{
			echo $task->ID;
		}
	}

	function mailOnTaskSave($projectID, $taskID, $newTask) {
		if (Settings::get('EmailOnUpdate') > 0)
		{
	  $sent_array = array();
			$SQL = sprintf(SQL_GET_EMAIL_SUBJECT_DETAILS, $taskID);
			$subjectdetails = $this->DB->QuerySingle($SQL);
			$SQL = sprintf(SQL_GET_USERNAME, $this->User->ID);
			$username = $this->DB->ExecuteScalar($SQL);
			$url = url::build_url('projects', 'taskview', "taskid=$taskID&projectid=$projectID");
			$verbLowercase = ($newTask) ? MSG_CREATED_LOWERCASE : MSG_UPDATED_LOWERCASE;
			$verb = ($newTask) ? MSG_CREATED : MSG_UPDATED;

			$users_list = $this->DB->Query(sprintf(SQL_TASKS_EMAIL, $taskID));
			if ( !is_array( $users_list ) )
				$users_list = array();

			for ($i = 0; $i < count($users_list); $i++)
			{
				$sent_array[] = $users_list[$i]['EmailAddress'];
				$mailer = new SMTPMail();
				$mailer->FromName = SYS_FROMNAME;
				$mailer->FromAddress = SYS_FROMADDR;
				$mailer->Subject = sprintf(MSG_TASK_SAVE_EMAIL_CC_SUBJECT, "- {$subjectdetails['ClientName']}: {$subjectdetails['ProjectName']}: {$subjectdetails['TaskName']}");
				$mailer->ToName = $users_list[$i]['FirstName'];
				$mailer->ToAddress = $users_list[$i]['EmailAddress'];
				$mailer->Body = sprintf(MSG_TASK_SAVE_EMAIL_CC_BODY, $users_list[$i]['FirstName'], $verbLowercase, $subjectdetails['ProjectName'], $subjectdetails['TaskName'], $subjectdetails['Description'], $verb, $username, $url);
				$mailer->Execute();
				unset($mailer);
			}

			if (Settings::get('CCTaskOwner') > 0)
			{
				$sql = sprintf(SQL_GET_TASK_OWNER, $taskID);
				$owner_result = $this->DB->QuerySingle($sql);
				if ($owner_result && !(in_array($owner_result['EmailAddress'], $sent_array)))
				{
					$mailer = new SMTPMail();
					$mailer->FromName = SYS_FROMNAME;
					$mailer->FromAddress = SYS_FROMADDR;
					$mailer->Subject = sprintf(MSG_TASK_SAVE_EMAIL_OWNER_SUBJECT, "- {$subjectdetails['ClientName']}: {$subjectdetails['ProjectName']}: {$subjectdetails['TaskName']}");
					$mailer->ToName = $owner_result['FirstName'];
					$mailer->ToAddress = $owner_result['EmailAddress'];
					$mailer->Body = sprintf(MSG_TASK_SAVE_EMAIL_OWNER_BODY, $owner_result['FirstName'], $verbLowercase, $subjectdetails['ProjectName'], $subjectdetails['TaskName'], $subjectdetails['Description'], $verb, $username, $url);
					$mailer->Execute();
				}
			}
		}
	}

	function TaskDelete() {
		$projectid = Request::get('projectid', Request::R_INT);
		$taskid = Request::get('taskid', Request::R_INT);
		$tmpl['MESSAGE'] = MSG_TASK_NOT_FOUND;
		$tmpl['lblIcon'] = 'tasks';
		$template = 'message';
		$title = MSG_PROJECTS;
		$breadcrumbs = MSG_DELETE;

		if (is_numeric($taskid))
		{
			if ($this->User->HasUserItemAccess($this->ModuleName, $projectid, CU_ACCESS_WRITE))
			{
				$confirm = Request::get('confirm');
				if ($confirm == 1)
				{
					$rows = $this->DB->Query(sprintf(SQL_GET_HOURS_COMMITTED_ON_TASK_FOR_TOTAL, $taskid));
					foreach($rows as $key => $value)
						$this->DB->Execute(sprintf(SQL_UPDATE_RESOURCE_DAY_COMMITMENT_CACHE, $value['ResourceID'], $value['DayID'], 0 - $value['HoursCommitted']));

					$this->DB->Execute(sprintf(SQL_DELETE_TASK_RESOURCES, $taskid));
					$this->DB->Execute(sprintf(SQL_DELETE_DELEGATED_TASKS, $taskid));
					$this->DB->Execute(sprintf(SQL_DELETE_TASK_COMMENTS, $taskid));
					$this->DB->Execute(sprintf(SQL_DELETE_TASK_FILES, $taskid));
					$this->DB->Execute(sprintf(SQL_DELETE_TASK, $taskid));
					Response::redirect('index.php?module=projects&action=view&projectid='.$projectid);
				}
				else
				{
					$SQL = sprintf(SQL_TASKS_GET_DETAILS_ALL, $taskid);
					$rs	= $this->DB->QuerySingle($SQL);
					if (is_array($rs))
					{
						$tmpl['pID']	 = $projectid;
						$tmpl['ID']		 = $taskid;
						$tmpl['MESSAGE'] = sprintf(MSG_DELETE_TASK_WARNING, $rs['Name']);
						$tmpl['YES']	 = MSG_YES;
						$tmpl['NO']		 = MSG_NO;
						$template			 = 'delete_task';
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

	function UserIsAssigned($taskid = 0) {
				$userid = $this->User->ID;
				$SQL = sprintf(SQL_ASSIGNED_COUNT, $taskid, $userid);
				$assigned = $this->DB->ExecuteScalar($SQL);

				//TaskOwner
				$owner = $this->DB->ExecuteScalar(sprintf(SQL_GET_TASK_OWNER_ID, $taskid));
				if ($owner == $userid)
						$assigned = $assigned + 1;

				if ( $assigned > 0 )
				{
						return true;
				}
				return false;
	}

	function GetAssignedTasks( $projectID, $userID ) {
			$taskIDs = array();

		$rows = $this->DB->Query( sprintf( SQL_SELECT_TASKS_FOR_USER, $projectID, $userID ) );
		if ( is_array( $rows ) )
		{
			foreach ( $rows as $row )
				$taskIDs[] = $row['ID'];
		}

		return $taskIDs;
		}

	function TaskComment() {
		$taskID = Request::post('taskid', Request::R_INT);
		$projectID = Request::post('projectid', Request::R_INT);
		$commentID = Request::post('commentid', Request::R_INT);
		$comment = htmlentities($this->DB->Prepare(Request::post('comment')), ENT_COMPAT, CHARSET);
		//$comment = preg_replace('(http:[\w/.:+\-~#?]+)','<a href="$0">$0</a>', $comment);
		$percentage = Request::post('percentage', Request::R_INT);
		$contact = Request::post('contact', Request::R_INT);
		$contactName = Request::post('contactName');
		$oldhours = (double) Request::post('oldhours');
		$hours = Format::hours(Request::post('hours'));
		$status = ($percentage == 100) ? '1' : '0';
		$issue = (strlen(Request::post('issue')) > 0) ? 1 : 0;
		$outofscope = (strlen(Request::post('outofscope')) > 0) ? 1 : 0;
		$billcode = $this->DB->Prepare(Request::post('billcode'));
		$date = (!empty($_POST['gendate'])) ? Request::post('gendate') : Format::parse_date(Request::post('date'));
		$subject = $this->DB->Prepare(MSG_QUICK_UPDATE);
		$caller = Request::post('caller');

		// projects seems to be the same as taskview, so map that here. 
		if ( ($caller == '') || ($caller == 'projects') )
		{
			$caller = 'taskview';
		}

		//$comment = $caller;
		if ($this->Session->Get('springboardID') && $caller == 'springboard') {
			$userID = $this->Session->Get('springboardID');
			$this->TempUser =& new User();
			$this->TempUser->Initialise($userID, $this->DB);
		}

		$hasProjectWrite = $this->User->HasUserItemAccess('projects', $projectID, CU_ACCESS_WRITE);
		if ($hasProjectWrite || $this->UserIsAssigned($taskID))
		{
			// Update/insert the comment and hours.
			if ($commentID != null)
			{
				if (isset($this->TempUser) && $caller == 'springboard')
					$sql = sprintf(SQL_UPDATE_COMMENT, $this->TempUser->ID, $commentID, $comment, $hours, $issue, $contact,
			$outofscope, $date.' '.date('H:i:s'), $this->TempUser->CostRate, $this->TempUser->ChargeRate);
				else
					$sql = sprintf(SQL_UPDATE_COMMENT, $this->User->ID, $commentID, $comment, $hours, $issue, $contact,
						$outofscope, $date.' '.date('H:i:s'), $this->User->CostRate, $this->User->ChargeRate);
				$this->DB->Execute($sql);
			}
			else
			{
				if (isset($this->TempUser) && $caller == 'springboard')
					$sql = sprintf(SQL_INSERT_COMMENT, $this->TempUser->ID, $taskID, $subject, $comment, $hours, $issue, $contact,
						$outofscope, $date.' '.date('H:i:s'), $this->TempUser->CostRate, $this->TempUser->ChargeRate);
				else
					$sql = sprintf(SQL_INSERT_COMMENT, $this->User->ID, $taskID, $subject, $comment, $hours, $issue, $contact,
						$outofscope, $date.' '.date('H:i:s'), $this->User->CostRate, $this->User->ChargeRate);
				$this->DB->Execute($sql);
			  $commentID = $this->DB->ExecuteScalar(SQL_LAST_INSERT);
			}

			// update the task latest edited timestamp
			$sql = sprintf(SQL_UPDATE_TASK_TIMESTAMP, $taskID);
			$this->DB->Execute($sql);

			//update the task's percent complete, but only if it's provided.
			if ($percentage != null)
			{
				$sql = sprintf(SQL_UPDATE_TASK_PERCENTAGE_COMPLETE, $percentage, $status, $taskID);
				$this->DB->Execute($sql);
			}

			// If the user is a resource, update their completed hours records.
			$userID = (isset($this->TempUser) && $caller == 'springboard' ) ? $this->TempUser->ID : $this->User->ID;
			$sql = sprintf(SQL_GET_RESOURCE_ID, $userID);
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
					$sql = sprintf(SQL_GET_TASK_USER_DAY_HOURS, $taskID, $userID, $date);
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
			$this->mailOnUpdate($taskID, $userID, $projectID, stripslashes($comment));
		}

		if ($caller == 'springboard' && $percentage == 100) {
			  echo "{nukepanel:$taskID}";
		} else if ($caller == 'taskview' || $caller == 'springboard') {
			//adding tasks from the ajax taskview we respond with the udpated comment
			//which can be drawn dynamically.
			if ($contact > 0) {
				$sql = sprintf(SQL_GET_CONTACT_NAME, $contact);
				$contactnameResult = $this->DB->QuerySingle($sql);
				$contactname = $contactnameResult['FullName'];
			} else {
				$contactname = "-";
			}

			$tmplComment['txtCommentID'] = $commentID;
			$tmplComment['txtEditMsg'] = MSG_EDIT;
			$tmplComment['txtUsername'] = (isset($this->TempUser)) ? $this->TempUser->Fullname : $this->User->Fullname;
			$tmplComment['txtHours'] = ($hours > 0.00) ? $hours : '';
			$tmplComment['txtHoursLbl'] = ($hours > 0.00) ? MSG_HRS : MSG_QUICK_UPDATE;
			$tmplComment['txtContactName'] = $contact;
			$tmplComment['txtBillable'] = ($outofscope == '1') ? ', <span class="billability baNonBillable">'.MSG_NOT_BILLABLE.'</span>' : ', <span class="billability baBillable">'.MSG_BILLABLE.'</span>';
			$tmplComment['txtBillableVal'] = ($outofscope == '1') ? '1' : '0';
			$tmplComment['txtIssue'] = ($issue == '1') ? ', <span class="issue">'.MSG_ISSUE.'</span>' : '';
			$tmplComment['txtIssueChkd'] = ($issue == '1') ? 'checked' : '';
			$tmplComment['txtIssueVal'] = ($issue == '1') ? '1' : '0';

			// we have to strip slashes, as we are working on the db prepared version
			$tmplComment['txtBody'] = Format::blocktext($comment);

			// Added to solve HTML tag problem
			$link_pattern = "/<a[^>]*>(.*)<\/a>/iU";
			$comment = preg_replace($link_pattern, "$1", $comment);
			// we have to strip slashes, as we are working on the db prepared version
			$tmplComment['txtBodyValue'] = stripslashes($comment);
			$tmplComment['txtDayValue'] =  Format::date($date, FALSE, FALSE);
			$tmplComment['txtContactName'] = $contactname;
			$tmplComment['txtDay'] = Format::date($date);

			$tmplComment['txtTime'] = date('h:ia');

		  $hasAdmin = $this->User->HasModuleItemAccess('administration', CU_ACCESS_ALL, CU_ACCESS_READ);
		  if ($hasAdmin || (($c->UserID == $this->User->ID) && (Settings::get('TaskLogEdit') == 1))){
			  $tmplComment['txtEdit'] = '<li class="txtEdit"><a href="#" onclick="return springboard.editTaskComment('.$commentID.');">'.MSG_EDIT.'</a></li>';
			  $tmplComment['txtDel'] = '<li class="txtDelete"><a href="#" onclick="springboard.removeTaskComment('.$commentID.','.$projectID.','.$taskID.'); return false">X</a></li>';
		  } else {
			  $tmplComment['txtEdit'] = '';
			  $tmplComment['txtDel'] = '';
		}

			$tmplComment['txtProjectID']=$projectID;
			$tmplComment['txtTaskID']=$taskID;
			$tmplComment['txtPercentage'] = $percentage;
				  //Put this here so that the second edit of a comment works
			$tmplComment['txtPercentageMax'] = 100;
			
			// new comments always have the logged in users avatar;
			$tmplComment['txtAvatarImage'] = CopperUser::current()->get_avatar();

			$commentBlock = $this->getTemplate('tasks_view_comment', $tmplComment);
			echo $commentBlock;
		} else if ($caller == 'stopwatch') {
			echo "{success:1}";
		} else
			Response::redirect('index.php?module=projects&action=taskview&taskid='.$taskID.'&projectid='.$projectID);
	}

	function mailOnUpdate($taskID, $userID, $projectID, $update) {
		if (Settings::get('EmailOnUpdate') > 0)
		{
	  $sent_array = array();

			$SQL = sprintf(SQL_GET_EMAIL_SUBJECT_DETAILS, $taskID);
			$subjectdetails = $this->DB->QuerySingle($SQL);
			$SQL = sprintf(SQL_GET_USERNAME, $userID);
			$username = $this->DB->ExecuteScalar($SQL);
			$url = url::build_url('projects', 'taskview', "taskid=$taskID&projectid=$projectID");

			$users_list = $this->DB->Query(sprintf(SQL_TASKS_EMAIL, $taskID));
			if ( !is_array( $users_list ) )
				$users_list = array();

			for ($i = 0; $i < count($users_list); $i++)
			{
				$sent_array[] = $users_list[$i]['EmailAddress'];
				$mailer = new SMTPMail();
				$mailer->FromName = SYS_FROMNAME;
				$mailer->FromAddress = SYS_FROMADDR;
				$mailer->Subject = sprintf(MSG_TASK_EMAIL_UPDATE_CC_SUBJECT, "- {$subjectdetails['ClientName']}: {$subjectdetails['ProjectName']}: {$subjectdetails['TaskName']}");
				$mailer->Priority = 1;
				$mailer->ToName = $users_list[$i]['FirstName'];
				$mailer->ToAddress = $users_list[$i]['EmailAddress'];
				$mailer->Body = sprintf(MSG_TASK_EMAIL_UPDATE_CC_BODY, $users_list[$i]['FirstName'], $subjectdetails['ProjectName'], $subjectdetails['TaskName'], $update, $username, $url);
				$mailer->Execute();
				unset($mailer);
			}

			if (Settings::get('CCTaskOwner') > 0)
			{
				$sql = sprintf(SQL_GET_TASK_OWNER, $taskID);
				$owner_result = $this->DB->QuerySingle($sql);
				if ($owner_result && !(in_array($owner_result['EmailAddress'], $sent_array)))
				{
					$mailer = new SMTPMail();
					$mailer->FromName = SYS_FROMNAME;
					$mailer->FromAddress = SYS_FROMADDR;
					$mailer->Subject = sprintf(MSG_TASK_EMAIL_UPDATE_OWNER_SUBJECT, "- {$subjectdetails['ClientName']}: {$subjectdetails['ProjectName']}: {$subjectdetails['TaskName']}");
					$mailer->Priority = 1;
					$mailer->ToName = $owner_result['FirstName'];
					$mailer->ToAddress = $owner_result['EmailAddress'];
					$mailer->Body = sprintf(MSG_TASK_EMAIL_UPDATE_OWNER_BODY, $owner_result['FirstName'], $subjectdetails['ProjectName'], $subjectdetails['TaskName'], $update, $username, $url);
					$mailer->Execute();
				}
			}
		}
	}

	function TaskMoveUp() {
				$taskid		 = Request::get('taskid', Request::R_INT);
				$projectid	= Request::get('projectid', Request::R_INT);
		if ($this->User->HasUserItemAccess($this->ModuleName, $projectid, CU_ACCESS_WRITE))
		{
				$SQL = sprintf(SQL_PROJECT_TASK_GET_SEQUENCE_BY_TASK, $taskid);	// get sequence of task
				$sequence = $this->DB->QuerySingle($SQL);
				$sequence = intval($sequence[0]);
				$sequenceNew = $sequence - 1;	// decrement sequence (move up)

				if ($sequenceNew > 0) {	// if sequence CAN be decremented
					$SQL = sprintf(SQL_PROJECT_TASK_GET_TASK_BY_SEQUENCE, $sequenceNew, $projectid);	// get task ID that already has this sequence
					$task2 = $this->DB->QuerySingle($SQL);
					$task2 = intval($task2[0]);
					if ($task2) {	// if task exists with this sequence
						$SQL = sprintf(SQL_PROJECT_TASK_SEQUENCE_UPDATE, $sequence, $task2);	// swap with sequence from first task
						$this->DB->Execute($SQL);
					}
					$SQL = sprintf(SQL_PROJECT_TASK_SEQUENCE_UPDATE, $sequenceNew, $taskid);	// set sequence of first task to new sequence
						$this->DB->Execute($SQL);
				}
		else
						$this->ThrowError(2001);
				}
				Response::redirect('index.php?module=projects&action=view&projectid='.$projectid);
	}

	function TaskMoveDown() {
				$taskid		 = Request::get('taskid', Request::R_INT);
				$projectid	= Request::get('projectid', Request::R_INT);
		if ($this->User->HasUserItemAccess($this->ModuleName, $projectid, CU_ACCESS_WRITE))
		{
				$SQL = sprintf(SQL_PROJECT_TASK_GET_SEQUENCE_BY_TASK, $taskid);	// get sequence of task
				$sequence = $this->DB->QuerySingle($SQL);
				$sequence = intval($sequence[0]);
				$sequenceNew = $sequence + 1;	// increment sequence (move down)

				$SQL = sprintf(SQL_PROJECT_TASK_GET_TASK_BY_SEQUENCE, $sequenceNew, $projectid);	// get task ID that already has this sequence
				$task2 = $this->DB->QuerySingle($SQL);
				$task2 = intval($task2[0]);
				if ($task2) {	// if task exists with this new sequence (if it doesn't the task is already at the bottom and will not be updated)
					$SQL = sprintf(SQL_PROJECT_TASK_SEQUENCE_UPDATE, $sequence, $task2);	// swap with sequence from first task
					$this->DB->Execute($SQL);
					$SQL = sprintf(SQL_PROJECT_TASK_SEQUENCE_UPDATE, $sequenceNew, $taskid);	// set sequence of first task to new sequence
						$this->DB->Execute($SQL);
				}
		else
						$this->ThrowError(2001);
				}
				Response::redirect('index.php?module=projects&action=view&projectid='.$projectid);
	}

	function TaskDependencyAdd() {
		$taskID = Request::get('taskid', Request::R_INT);
		$projectID = Request::get('projectid', Request::R_INT);
		$taskDependency = Request::get('taskdependency');
		$taskDependency = explode(',',$taskDependency);
		$taskDependencyID = $taskDependency[0];
		$taskDependencyType = $taskDependency[1];

		$SQL = sprintf(SQL_PROJECT_TASK_DEPENDENCY_ADD, $taskID, $taskDependencyID, $taskDependencyType);
		$this->DB->Execute($SQL);

		$task = $this->DB->QuerySingle(sprintf(SQL_TASKS_GET_BASIC_DETAILS, $taskDependencyID));
		$tmplDep = array();
		if ($taskDependencyType == 1)
			$tmplDep['msg'] = sprintf(MSG_TASK_DEPENDENCY_THIS_STARTS_WHEN_TASK_FINISHES, '<strong>'.$task['Name'].'</strong>');
		if ($taskDependencyType == 2)
			$tmplDep['msg'] = sprintf(MSG_TASK_DEPENDENCY_THIS_STARTS_WHEN_TASK_STARTS, '<strong>'.$task['Name'].'</strong>');
		if ($taskDependencyType == 3)
			$tmplDep['msg'] = sprintf(MSG_TASK_DEPENDENCY_THIS_FINISHES_WHEN_TASK_FINISHES, '<strong>'.$task['Name'].'</strong>');

		$tmplDep['projectID'] = $projectID;
		$tmplDep['taskID'] = $taskID;
		$tmplDep['otherTaskID'] = $taskDependencyID;
		$tmplDep['dependency'] = $taskDependencyType;
		$html = $this->getTemplate('tasks_form_dependencies', $tmplDep);
		echo $html;

		//Response::redirect('index.php?module=projects&action=taskedit&projectid='.$projectID.'&taskid='.$taskID);
	}

	function TaskDependencyRemove() {
		$taskID = Request::get('taskid', Request::R_INT);
		$projectID = Request::get('projectid', Request::R_INT);
		$taskDependency = Request::get('taskdependency');
		$taskDependency = explode(',',$taskDependency);
		$taskDependencyID = $taskDependency[0];
		$taskDependencyType = $taskDependency[1];

		$SQL = sprintf(SQL_PROJECT_TASK_DEPENDENCY_REMOVE, $taskID, $taskDependencyID, $taskDependencyType);
		$this->DB->Execute($SQL);

		$task = $this->DB->QuerySingle(sprintf(SQL_TASKS_GET_BASIC_DETAILS, $taskDependencyID));
		$msg1 = sprintf(MSG_TASK_DEPENDENCY_THIS_STARTS_WHEN_TASK_FINISHES, $task['Name']);
		$msg2 = sprintf(MSG_TASK_DEPENDENCY_THIS_STARTS_WHEN_TASK_STARTS, $task['Name']);
		$msg3 = sprintf(MSG_TASK_DEPENDENCY_THIS_FINISHES_WHEN_TASK_FINISHES, $task['Name']);

		if ($task['Name'] != '')
			echo '{success:1,msg1:"'.$msg1.'",msg2:"'.$msg2.'",msg3:"'.$msg3.'"}';
		else
			echo '{success:0}';

		//Response::redirect('index.php?module=projects&action=taskedit&projectid='.$projectID.'&taskid='.$taskID);
	}

	function FileList() {
		$projectID = Request::get('projectid', Request::R_INT);

		$this->ProjectTabs($projectID);
		$hasFileRead = ( $this->User->HasModuleItemAccess( $this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_READ ) );
		if ( $hasFileRead )
		{
			$hasFileWrite = $this->User->HasModuleItemAccess( 'files', CU_ACCESS_ALL, CU_ACCESS_WRITE );
			if ( $hasFileWrite ) {
				$modAction[] = '<a href="#" onclick="newFile('.$projectID.');return false;">' . MSG_NEW_FILE . '</a>';
				$modAction[] = '<a href="#" onclick="newFolder('.$projectID.');return false;">' . MSG_NEW_FOLDER . '</a>';
			}

			$project = new Project($projectID);

			$tmpl['txtProjectID'] = $projectID;
			$tmpl['txtModule'] = "projects&action=filelist&projectid=$projectID";
			$tmpl['txtOrder'] = Request::get('order');
			$tmpl['txtDirection'] = Request::get('direction');

			$file_tmpl = array('project' => $project);
			foreach($project->get_folders() as $folder)
			{
				// render folder header
				$file_tmpl['folder'] = $folder;
				$file_list .= $this->includeTemplate('files/folder_header', $file_tmpl);
				foreach($folder->get_files() as $file)
				{
					// render file
					$file_tmpl['file'] = $file;
					$file_list .= $this->includeTemplate('files/file', $file_tmpl);
				}
				
				// render folder footer
				$file_list .= $this->includeTemplate('files/folder_footer', $file_tmpl);
			}
			
			foreach ($project->get_base_files() as $file)
			{
				// render file
				$file_tmpl['file'] = $file;
				$file_list .= $this->includeTemplate('files/file', $file_tmpl);
			}

			$tmpl['file_list'] = $file_list;

			$this->setTemplate('files/file_list', $tmpl);

			$this->addStyle('files.css');
			$this->setHeader( MSG_FILES, $header );
			$this->setModule( MSG_LIST, $modAction );
			$this->Render();
		}
		else
		{
			$this->ThrowError( 2001 );
		}
	}


	function EmailList() {
		if (!$this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_READ))
		{
			$this->ThrowError(2001);
			return;
		}
		
		$projectID = Request::get('projectid', Request::R_INT);
		$order = Request::get('order');
		$this->ProjectTabs($projectID);

		$modAction[] = '<a href="index.php?module=projects&archived='.$active.'">'.$msg.'</a>';

		$orderOptions = array(
			'subject'				=> 'SORTSUBJECT',
			'from'				=> 'SORTFROM',
			'to'				=> 'SORTTO'
		);
		list($order, $orderby) = Request::$GET->filterRequest('order', $orderOptions, array('date', 'SORTARRIVAL'));

		$direction = (Request::get('direction') == 'down') ? '1' : '0';

		$userProjectID = $this->DB->ExecuteScalar(sprintf(SQL_GET_PROJECT_ID, $projectID));
		$emails_array = $this->GetProjectEmails($userProjectID, $orderby, $direction);

		if (is_array($emails_array)) {
			$tmpl['start']		   = $offset;
			$tmpl['txtProjectID'] = $projectID;
			$this->setTemplate('emails_header', $tmpl);
			foreach($emails_array as $key => $email) {

				$tmpl['txtSubject'] = (isset($userProjectID)) ? str_replace(($userProjectID.":"), "", $email[1]) : $email[1];
				$date = date("Y-m-d",strtotime($email[2]));
				$tmpl['txtDate'] = Format::date($date);
				$tmpl['txtFrom'] = $email[3];
				$tmpl['txtTo'] = $email[4];
				$tmpl['txtAttachments'] = $email[5];
				$tmpl['txtID'] = $email[0];
				$this->setTemplate('email_item', $tmpl);

				if ($RS->TotalRecords > $limit)
				{
					$url = 'index.php?module=projects&order='.$order.'&direction='.$direction;
					cuPaginate($RS->TotalRecords, $limit, $url, $offset, $tmpl);
					$this->setTemplate('emails_paging', $tmpl);
					unset($tmpl);
				}

			}
			$this->setTemplate('emails_footer');
		}
		else
		{
			$tmpl['MESSAGE'] = MSG_NO_EMAILS_AVAILABLE;
			$this->setTemplate('eof', $tmpl);
		}

		$this->setHeader(MSG_PROJECTS, '');
		$this->setModule(MSG_EMAILS_LIST, $modAction);
		$this->Render();
	}

	function AjaxEmailView() {
		header('Content-Type: text/html; charset='.CHARSET);
		$projectID = Request::get('projectid', Request::R_INT);
		$email = Request::get('email', Request::R_INT);

		if (!$this->User->HasUserItemAccess($this->ModuleName, $projectID, CU_ACCESS_READ))
		{
			$this->ThrowError(2001);
			return;
		}


		$mbox = imap_open(CU_IMAP_CONNECTION_STRING, CU_IMAP_CONNECTION_USER, CU_IMAP_CONNECTION_PASSWORD);

		if ( $mbox === false )
			exit ("Can't connect: " . imap_last_error() ."\n");

		$email_headers = imap_header($mbox,$email);
		$from = $email_headers->from[0]->mailbox.'@'.$email_headers->from[0]->host;
		$to = $email_headers->to[0]->mailbox.'@'.$email_headers->to[0]->host;
		$date = $email_headers->date;
		$body = imap_fetchbody($mbox, $email,1);
		imap_close($mbox);

		//get Email
		$tmpl['txtBody'] = nl2br($body);
		$date = date("Y-m-d",strtotime($date));
		$tmpl['txtDate'] = Format::date($date);
		$tmpl['txtFrom'] = $from;
		$tmpl['txtTo'] = $to;
		$tmpl['txtID'] = $email;
		$tmpl['txtProjectID'] = $projectID;


		$this->setTemplate('ajax_email_item', $tmpl);
		$this->RenderOnlyContent();
	}

	function DeleteEmail() {

		$projectID = Request::get('projectid', Request::R_INT);
		$email = Request::get('email');
		if (!$this->User->HasUserItemAccess($this->ModuleName, $projectID, CU_ACCESS_READ))
		{
			$this->ThrowError(2001);
			return;
		}

		$mbox = imap_open(CU_IMAP_CONNECTION_STRING, CU_IMAP_CONNECTION_USER, CU_IMAP_CONNECTION_PASSWORD);

		if ( $mbox === false )
			exit ("Can't connect: " . imap_last_error() ."\n");

		imap_delete($mbox,$email);
		imap_expunge($mbox);
		imap_close($mbox);
		Response::redirect('index.php?module=projects&action=emaillist&projectid='.$projectID);
	}

	function SaveEmailAsEML() {

		$projectID = Request::get('projectid', Request::R_INT);
		$email = Request::get('email');
		if (!$this->User->HasUserItemAccess($this->ModuleName, $projectID, CU_ACCESS_READ))
		{
			$this->ThrowError(2001);
			return;
		}

		$mbox = imap_open(CU_IMAP_CONNECTION_STRING, CU_IMAP_CONNECTION_USER, CU_IMAP_CONNECTION_PASSWORD);

		if ( $mbox === false )
			exit ("Can't connect: " . imap_last_error() ."\n");

		$eml = imap_fetchheader($mbox, $email);
		$eml .= imap_body($mbox, $email);
		imap_close($mbox);
		header("Content-Type: text/plain");
		header("Content-disposition: attachment;filename=\"$projectID.eml\"");
		echo $eml;
	}

	function GetProjectEmails($projectid, $orderby, $direction) {
		try {
			$mbox = imap_open(CU_IMAP_CONNECTION_STRING, CU_IMAP_CONNECTION_USER, CU_IMAP_CONNECTION_PASSWORD);
		} catch (Exception $e) {
			return null;
		}



		if ( $mbox === false )
			exit ("Can't connect: " . imap_last_error() ."\n");
		$sorted = imap_sort($mbox, $orderby, $direction);

		if (is_array($sorted)) {
			$count = 1;

			foreach ($sorted as $val) {
				$email_headers = imap_header($mbox,$val);
				$subject = $email_headers->subject;
				$from = $email_headers->from[0]->mailbox.'@'.$email_headers->from[0]->host;
				$to = $email_headers->to[0]->mailbox.'@'.$email_headers->to[0]->host;
				$date = $email_headers->date;
				$pos = @strpos($subject, $projectid);
				if ($pos === 0) {
					$emails[$count] =  array ($val,$subject,$date,$from,$to);
					$count++;
				}
			}
		}

		imap_close($mbox);
		return $emails;


	}

	//~ project file functions

	function GanttChart() {
		$projectid = Request::get('projectid', Request::R_INT);
		// get all the related projects
		$relatedProjectsSQL = sprintf(SQL_RELATED_PROJECTS, $projectid);
		$relatedProjects = $this->DB->Query($relatedProjectsSQL);

		$projectIDs = $projectid;
		if ($relatedProjects != false) {
			for ($i = 0; $i < count($relatedProjects); $i++) {
				$projectIDs .= ',' . $relatedProjects[$i]['RelatedProjectID'];
			}
		}

		if ($this->User->HasUserItemAccess($this->ModuleName, $projectid, CU_ACCESS_READ)) {
			$details = $this->GetProjectDetails($projectid);

			$modTitle = MSG_PROJECTS;
			$modHeader = $details['ProjectName'].' '.MSG_GANTT_CHART;

			//$this->ProjectStatus($details);
			$this->ProjectTabs($projectid);

			// $SQL = sprintf(SQL_GET_TASKLIST,$projectid);
			// $tasks = $this->DB->Query($SQL);
			// $tasksCount = 0;
			// if (is_array($tasks)) {
				// $tasksCount = count($tasks);
			// }
			$tmpl['projectIDs'] = $projectIDs;
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
			if ($this->User->HasUserItemAccess('projects',$oldProjectIDs[$i],CU_ACCESS_READ)) {
				$projectIDs[count($projectIDs)] = $oldProjectIDs[$i];
			}
		}
		if (count($projectIDs)) {

			// get the min and max task start/end dates for all of the projects
			$whereProjectIDOR = '';
			for ($i = 0; $i < count($projectIDs); $i++) {
				if ($i) $whereProjectIDOR .= ' OR ';
				$whereProjectIDOR .= ' ProjectID = ' . $projectIDs[$i];
			}

			$SQL = sprintf(SQL_GET_MIN_MAX_TASK_DATES,$whereProjectIDOR);
			$minMaxTaskDates = $this->DB->QuerySingle($SQL);
			if ($minMaxTaskDates['StartDate']) $dateStart = strtotime($minMaxTaskDates['StartDate']);
			else $dateStart = time();
			if ($minMaxTaskDates['EndDate']) {
				if ($minMaxTaskDates['EndDate'] == '0000-00-00') $minMaxTaskDates['EndDate'] = $minMaxTaskDates['StartDate'];
				$dateEnd = strtotime($minMaxTaskDates['EndDate']);
			}
			else $dateEnd = time();

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
			$projectTasksIndent = '&projectTasksIndent=';
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

				$tempProjectName = $this->encodeCommas($project['Name']);
				if (strlen($tempProjectName) > 28) $tempProjectName = substr($tempProjectName,0,27).'..';
				$projectNames .= ($i ? ',' : '') . $tempProjectName;
				$projectDatesStart .= ($i ? ',' : '') . $project['StartDate'];
				$projectDatesEnd .= ($i ? ',' : '') . $project['EndDate'];
				$projectColours .= ($i ? ',0x' : ''). $project['Colour'];

				// clear the lists for the next project
				$tasksIDList = '';
				$tasksNameList = '';
				$tasksOwnerList = '';
				$tasksDateStartList = '';
				$tasksDateEndList = '';
				$tasksDurationList = '';
				$tasksSequenceList = '';
				$tasksIndentList = '';
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

				// the gantt chart now relies on the sequences been in a valid order for shift selection
				$distinctSequences = $this->DB->ExecuteScalar(sprintf(SQL_TASK_DISTINCT_SEQUENCES,$projectIDs[$i]));

				if (rand(1,50) == 42 || $distinctSequences != count($tasks)) {
					// reorder the sequences of the project
					$taskSeq = $this->DB->Query(sprintf(SQL_TASK_SEQUENCES,$projectIDs[$i]));

					for ($j = 0; $j < count($taskSeq); $j++) {
						$sql = 'update tblTasks set Sequence = ' . ($j + 1) . ' where ID = ' . $taskSeq[$j]['ID'];
						// echo $sql . '<br>';
						$this->DB->Execute($sql);
					}
					// get the tasks agane with the new sequences
					$SQL = sprintf(SQL_GET_TASKLIST_GANTT,$projectIDs[$i]);
					$tasks = $this->DB->Query($SQL);
				}

					$tasksCount = count($tasks);
					for ($j = 0; $j < count($tasks); $j++) {
						$tasksIDList .= ($j ? ',' : '') . $tasks[$j]['ID'];
						if ($tasks[$j]['DateEnd'] == '0000-00-00') $tasks[$j]['DateEnd'] = $tasks[$j]['DateStart'];
						// if (strlen($tasks[$j]['Name']) > 28) $tasks[$j]['Name'] = substr($tasks[$j]['Name'],0,27).'..';
						$tasksNameList .= ($j ? ',' : '') . $this->encodeCommasNoUTF8($tasks[$j]['Name'],1);
						$tasksOwnerList .= ($j ? ',' : '') . $this->encodeCommas($tasks[$j]['Owner'],1);
						$tasksDateStartList .= ($j ? ',' : '') . $tasks[$j]['DateStart'];
						$tasksDateEndList .= ($j ? ',' : '') . $tasks[$j]['DateEnd'];
						$tasksDurationList .= ($j ? ',' : '') . $tasks[$j]['Duration'];

						$tasksSequenceList .= ($j ? ',' : '') . $tasks[$j]['Sequence'];
					$tasksIndentList .= ($j ? ',' : '') . $tasks[$j]['Indent'];

						$dependenciesSQL = sprintf(SQL_PROJECT_TASK_DEPENDENCIES,$tasks[$j]['ID']);
						$dependencies = $this->DB->Query($dependenciesSQL);
						$dependencyIDsList = '';
						$dependencyTypesList = '';
					if ($dependencies) {
							for ($k = 0; $k < count($dependencies); $k++) {
							$dependencyIDsList .= ($dependencyIDsList ? ',' : '') . $dependencies[$k]['TaskDependencyID'];
							$dependencyTypesList .= ($dependencyTypesList ? ',' : '') . $dependencies[$k]['DependencyType'];
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
						if ($currentEpoch < $taskDays[0]['Epoch']) $todayIndexValue = 0; // today is befor this task
						else if ($currentEpoch > $taskDays[count($taskDays) - 1]['Epoch']) $todayIndexValue = count($taskDays); //today is after this task
						else {
							for ($k = 0; $k < count($taskDays); $k++) {
								if ($currentEpoch >= $taskDays[$k]['Epoch'] && $currentEpoch < $taskDays[$k + 1]['Epoch']) $todayIndexValue = $k;
							}
						}
						$tasksTodayIndexList .= ($j ? ',' : '') . $todayIndexValue;

						// get the total hours completed Before today, hours comitted Before today and hours completed after today
						$hoursCommittedSQL = sprintf(SQL_GET_HOURS_COMMITTED_ON_TASK_FOR_TOTAL,$tasks[$j]['ID']);
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

						$tasksTotalHoursCommittedBeforeList .= ($j ? ',' : '') .  $totalHoursCommittedBefore;
						$tasksTotalHoursCompletedBeforeList .= ($j ? ',' : '') . $totalHoursCompletedBefore;
						$tasksTotalHoursCommittedAfterList .= ($j ? ',' : '') . $totalHoursCommittedAfter;
						$tasksTotalHoursCompletedAfterList .= ($j ? ',' : '') . $totalHoursCompletedAfter;
				} // end tasks loop

				$projectTasksCount .= ($i ? ',' : '') . $tasksCount;

				$projectTasksID .= ($i ? ';' : '') . $tasksIDList;
				$projectTasksName .= ($i ? ';' : '') . $tasksNameList;
				$projectTasksOwner .= ($i ? ';' : '') . $tasksOwnerList;
				$projectTasksDateStart .= ($i ? ';' : '') . $tasksDateStartList;
				$projectTasksDateEnd .= ($i ? ';' : '') . $tasksDateEndList;
				$projectTasksDuration .= ($i ? ';' : '') . $tasksDurationList;
				$projectTasksSequence .= ($i ? ';' : '') . $tasksSequenceList;
				$projectTasksIndent .= ($i ? ';' : '') . $tasksIndentList;
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
			$chartDatesBegin = '&chartDatesBegin=' . date('Y-m-d',$dateStart);
			$datesCount = '&datesCount=' . round(($dateEnd - $dateStart) / 86400);
			$maxDayLength = '&maxDayLength=' . MAX_DAY_LENGTH;
			$dayLength = '&dayLength=' . DAY_LENGTH;

			echo 'today='.$today.$projectCount.$projectNames.$projectDatesStart.$projectDatesEnd.$projectColours.$dayLength.$maxDayLength.$datesCount.$projectTasksCount;
			echo $chartDatesBegin.$projectTasksID.$projectTasksName.$projectTasksOwner.$projectTasksDateStart.$projectTasksDateEnd.$projectTasksDuration.$projectTasksSequence.$projectTasksIndent.$projectTasksDependencyIDs.$projectTasksDependencyTypes;
			echo $projectTasksTodayIndex . $projectTasksDaysCount . $projectTasksTotalHoursCommittedBefore	. $projectTasksTotalHoursCompletedBefore . $projectTasksTotalHoursCommittedAfter . $projectTasksTotalHoursCompletedAfter . $projectTasksResourcesCount;
		}
		else {
			echo 'error=Access+Denied';
		}
	} // end function
	// begin new code by Niveus 2005-05 for new Gantt chart

	function GanttDataPrint() {
		// print data for 1 project

		$projectid = Request::get('projectid', Request::R_INT);
		if ($this->User->HasUserItemAccess('projects',$projectid,CU_ACCESS_READ)) {

			$SQL = sprintf(SQL_GET_PROJECT_DATES,$projectid);
			$projectDates = $this->DB->QuerySingle($SQL);
			if ($projectDates['dateEnd'] == '0000-00-00') $projectDates['dateEnd'] = $projectDates['dateStart'];

			// begin modified code by Niveus 2005-05-16-16-39
			// "dates" array only sends start date (Flash chart as of 2005-05 builds the dates itself)
			$dateStart = strtotime($projectDates['dateStart']);
			$dateEnd = strtotime($projectDates['dateEnd']);
			$dates = '&dates='.date('Y-m-d',$dateStart);
			$datesCount = round( ($dateEnd - $dateStart) / 86400);
			// end modified code

			$tasksID = '&tasksID=';
			$tasksName = '&tasksName=';
			$tasksOwner = '&tasksOwner=';
			$tasksDateStart = '&tasksDateStart=';
			$tasksDateEnd = '&tasksDateEnd=';
			$tasksDuration = '&tasksDuration=';
			$tasksHoursWorked = '&tasksHoursWorked=';
			$tasksSequence = '&tasksSequence=';
			$tasksDependencyIDs = '&tasksDependencyIDs=';
			$tasksDependencyTypes = '&tasksDependencyTypes=';
			$SQL = sprintf(SQL_GET_TASKLIST_GANTT,$projectid);
			$tasks = $this->DB->Query($SQL);

			if (is_array($tasks)) {
				$tasksCount = count($tasks);
				for ($i = 0; $i < count($tasks); $i++) {
					$tasksID .= ($i ? ',' : '').$tasks[$i]['ID'];
					if ($tasks[$i]['DateEnd'] == '0000-00-00') $tasks[$i]['DateEnd'] = $tasks[$i]['DateStart'];
					if (strlen($tasks[$i]['Name']) > 28) $tasks[$i]['Name'] = substr($tasks[$i]['Name'],0,27).'..';
					$tasksName .= ($i ? ',' : '').urlencode($tasks[$i]['Name']);
					$tasksOwner .= ($i ? ',' : '').urlencode($tasks[$i]['Owner']);
					$tasksDateStart .= ($i ? ',' : '').urlencode($tasks[$i]['DateStart']);
					$tasksDateEnd .= ($i ? ',' : '').urlencode($tasks[$i]['DateEnd']);
					$tasksDuration .= ($i ? ',' : '').urlencode($tasks[$i]['Duration']);
					$commentsSQL = sprintf(SQL_TASKS_GET_COMMENTS,$tasks[$i]['ID']);
					$commentsList = $this->DB->Query($commentsSQL);
					$taskHoursWorked = 0;

					if ( is_array($commentsList) ) {
						for ($j = 0; $j < count($commentsList); $j++) $taskHoursWorked += $commentsList[$j]['HoursWorked'];
					}

					$tasksHoursWorked .= ($i ? ',' : '').urlencode($taskHoursWorked);
					$tasksSequence .= ($i ? ',' : '').urlencode($tasks[$i]['Sequence']);
					$dependenciesSQL = sprintf(SQL_PROJECT_TASK_DEPENDENCIES,$tasks[$i]['ID']);
					$dependencies = $this->DB->Query($dependenciesSQL);
					$tasksDependencyIDsList = '';
					$tasksDependencyTypesList = '';
					if (is_array($dependencies)) {
						for ($j = 0; $j < count($dependencies); $j++) {
							$tasksDependencyIDsList .= ($tasksDependencyIDsList ? ',' : '').$dependencies[$j][0];
							$tasksDependencyTypesList .= ($tasksDependencyTypesList ? ',' : '').$dependencies[$j][1];
						}
					}

					$tasksDependencyIDs .= ($i ? ';' : '').$tasksDependencyIDsList;
					$tasksDependencyTypes .= ($i ? ';' : '').$tasksDependencyTypesList;
				}
			}

			$today = date('Y-m-d');
			$SQL = sprintf(SQL_GET_PROJECT_COLOUR,$projectid);
			$projectColour = $this->DB->QuerySingle($SQL);
			$colour = $projectColour['Colour'];
			if (substr($colour,0,1) == '#') $colour = substr($colour,1);
			echo 'today='.$today.'&colour=0x'.$colour.'&dayLength='.DAY_LENGTH.'&datesCount='.$datesCount.'&tasksCount='.$tasksCount;
			echo $dates.$tasksID.$tasksName.$tasksOwner.$tasksDateStart.$tasksDateEnd.$tasksDuration.$tasksHoursWorked.$tasksSequence.$tasksDependencyIDs.$tasksDependencyTypes;
		}
		else {
			echo 'error=Access+Denied';
		}
	}

	function GanttSave() {
	$projectID = Request::get('projectID', Request::R_INT);

		if ($this->User->HasUserItemAccess('projects',$projectID,CU_ACCESS_WRITE)) {

			if (Request::get('projectDateStart') && Request::get('projectDateEnd')) {
				// move project

				// get the current project start/ end dates
				$projectSQL = sprintf(SQL_GET_PROJECT_NAME_DATES_COLOUR,$projectID);
				$project = $this->DB->QuerySingle($projectSQL);
				$project['StartDate'] = strtotime($project['StartDate'] . 'GMT');
				$project['EndDate'] = strtotime($project['EndDate'] . 'GMT');

				// get the relativie number of days the project has been moved
				$newProjectStartDate = strtotime(Request::get('projectDateStart') . 'GMT');
				$newProjectEndDate = strtotime(Request::get('projectDateEnd') . 'GMT');
				$daysDifference = round(($newProjectStartDate - $project['StartDate']) / 60 / 60 / 24);

				// get a list of all the projects tasks and start/ end dates
				$projectsTaskDatesSQL = sprintf(SQL_GET_PROJECTS_TASKS_START_END_DATE,$projectID);
				$projectsTaskDates = $this->DB->Query($projectsTaskDatesSQL);

				// move all of the tasks
				for ($i = 0; $i < count($projectsTaskDates); $i++) {

					$projectsTaskDates[$i]['StartDate'] = strtotime($projectsTaskDates[$i]['StartDate'] . 'GMT');
					$newTaskStartDate = gmdate('Y-m-d',gmmktime(0,0,0,gmdate('m',$projectsTaskDates[$i]['StartDate']),gmdate('d',$projectsTaskDates[$i]['StartDate']) + $daysDifference,gmdate('Y',$projectsTaskDates[$i]['StartDate'])));

					// milestones dont have a end date set
					if ($projectsTaskDates[$i]['EndDate'] != '0000-00-00') {
						$projectsTaskDates[$i]['EndDate'] = strtotime($projectsTaskDates[$i]['EndDate'] . 'GMT');
						$newTaskEndDate = gmdate('Y-m-d',gmmktime(0,0,0,gmdate('m',$projectsTaskDates[$i]['EndDate']),gmdate('d',$projectsTaskDates[$i]['EndDate']) + $daysDifference,gmdate('Y',$projectsTaskDates[$i]['EndDate'])));
					}
					else $newTaskEndDate = '0000-00-00';

					$updateTaskDatesSQL = sprintf(SQL_UPDATE_TASK_DATES,$newTaskStartDate,$newTaskEndDate,$projectsTaskDates[$i]['ID']);
					$this->DB->Execute($updateTaskDatesSQL);

					if ($newTaskEndDate != '0000-00-00') {

						// get the new duration of this task that was just saved
						$taskSQL = sprintf(GET_TASK_DATES,$projectsTaskDates[$i]['ID']);
						$task = $this->DB->QuerySingle($taskSQL);

						// get the day ids for the start and end epoch dates
						$taskDays = $this->DB->Query(sprintf(SQL_GET_DAYID,strtotime($task['StartDate'] . 'GMT'),strtotime($task['EndDate'] . 'GMT')));

						// delete any task resource days not in the new task day range that dont have any completed hours
						$this->DB->Execute(sprintf(SQL_DELETE_TASK_RESOURCE_DAY,$projectsTaskDates[$i]['ID'],$taskDays[0]['ID'],$taskDays[count($taskDays) - 1]['ID']));

						// set the committed hours to 0 for the task days outside the range with completed hours
						$this->DB->Execute(sprintf(SQL_UPDATE_TASK_RESOURCE_DAY_COMMITTED,$projectsTaskDates[$i]['ID'],$taskDays[0]['ID'],$taskDays[count($taskDays) - 1]['ID']));
					}
				}
				// update the start/ end dates of the project
				$updateTaskDatesSQL = sprintf(SQL_UPDATE_PROJECT_DATES,Request::get('projectDateStart'),Request::get('projectDateEnd'),$projectID);
				$this->DB->Execute($updateTaskDatesSQL);

				// tell flash to reload
				echo 'reloadDataNeeded=YES&';
			}
			else if (Request::get('projectDateEnd')) {
				// change project length
				$SQL = sprintf(SQL_GANTT_PROJECT_ENDDATE_SAVE,$projectID,Request::get('projectDateEnd'));
				$this->DB->Execute($SQL);
			}

			if (Request::get('newTask')) {	// new task
				// increment the sequences of any tasks below where the new task has been added
				$SQL = sprintf(SQL_GANTT_TASK_NEW,Request::get('taskSequence'),$projectID);
				// create the new task ProjectID, Owner Name, StartDate, EndDate, Duration, Sequence, Indent
				$SQL = sprintf(SQL_GANTT_TASK_CREATE,$projectID, $this->User->ID, Request::get('taskName'),Request::get('taskDateStart'),Request::get('taskDateEnd'),Request::get('taskDuration'),Request::get('taskSequence'),Request::get('taskIndentLevel'));
				$this->DB->Execute($SQL);

				// get the task ID
				$taskID = $this->DB->ExecuteScalar(SQL_LAST_INSERT);
				echo 'taskCreateID='.$taskID.'&';
			}
			else {

				if (Request::get('taskID', Request::R_INT) && Request::get('taskName')) {
					$SQL = sprintf(SQL_GANTT_TASK_NAME_SAVE,Request::get('taskID', Request::R_INT),Request::get('taskName'));
					$this->DB->Execute($SQL);
				}

					if (Request::get('taskMoveSelection')) {
						if (Request::get('taskID', Request::R_INT) && Request::get('taskDateStart') && Request::get('taskDateEnd')) {
							$taskMoveSelection = Request::get('taskMoveSelection');
							$taskHighlightTaskIDs = Request::get('taskHighlightTaskIDs');
							// $taskHighlightTaskIDs = explode(',',$taskHighlightTaskIDs);

							// get the current task start/ end dates
							$taskSQL = sprintf(GET_TASK_DATES,Request::get('taskID', Request::R_INT));
							$task = $this->DB->QuerySingle($taskSQL);

							$task['StartDate'] = strtotime($task['StartDate'] . 'GMT');
							$task['EndDate'] = strtotime($task['EndDate'] . 'GMT');

							// get the relative number of days to update the start/ end dates of the highlighted tasks
							$newTaskStartDate = strtotime(Request::get('taskDateStart') . 'GMT');
							$newTaskEndDate = strtotime(Request::get('taskDateEnd') . 'GMT');
							$daysDifference = round(($newTaskStartDate - $task['StartDate']) / 60 / 60 / 24);

							// get a list of the highlighted tasks and start/ end dates
							$highlightedTaskDates = $this->DB->Query(sprintf(SQL_GET_TASKS_START_END_DATE,$taskHighlightTaskIDs));


							// move all of the tasks
							for ($i = 0; $i < count($highlightedTaskDates); $i++) {

								$highlightedTaskDates[$i]['StartDate'] = strtotime($highlightedTaskDates[$i]['StartDate'] . 'GMT');
								$newTaskStartDate = gmdate('Y-m-d',gmmktime(0,0,0,gmdate('m',$highlightedTaskDates[$i]['StartDate']),gmdate('d',$highlightedTaskDates[$i]['StartDate']) + $daysDifference,gmdate('Y',$highlightedTaskDates[$i]['StartDate'])));

								// milestones dont have a end date set
								if ($highlightedTaskDates[$i]['EndDate'] != '0000-00-00') {
									$highlightedTaskDates[$i]['EndDate'] = strtotime($highlightedTaskDates[$i]['EndDate'] . 'GMT');
									$newTaskEndDate = gmdate('Y-m-d',gmmktime(0,0,0,gmdate('m',$highlightedTaskDates[$i]['EndDate']),gmdate('d',$highlightedTaskDates[$i]['EndDate']) + $daysDifference,gmdate('Y',$highlightedTaskDates[$i]['EndDate'])));
								}
								else $newTaskEndDate = '0000-00-00';

								$updateTaskDatesSQL = sprintf(SQL_UPDATE_TASK_DATES,$newTaskStartDate,$newTaskEndDate,$highlightedTaskDates[$i]['ID']);

								$this->DB->Execute($updateTaskDatesSQL);

								if ($newTaskEndDate != '0000-00-00') {

									// get the new duration of this task that was just saved
									$taskSQL = sprintf(GET_TASK_DATES,$highlightedTaskDates[$i]['ID']);
									$task = $this->DB->QuerySingle($taskSQL);

									// get the day ids for the start and end epoch dates
									$taskDays = $this->DB->Query(sprintf(SQL_GET_DAYID,strtotime($task['StartDate'] . 'GMT'),strtotime($task['EndDate'] . 'GMT')));

									// delete any task resource days not in the new task day range that dont have any completed hours
									$this->DB->Execute(sprintf(SQL_DELETE_TASK_RESOURCE_DAY,$highlightedTaskDates[$i]['ID'],$taskDays[0]['ID'],$taskDays[count($taskDays) - 1]['ID']));

									// set the committed hours to 0 for the task days outside the range with completed hours
									$this->DB->Execute(sprintf(SQL_UPDATE_TASK_RESOURCE_DAY_COMMITTED,$highlightedTaskDates[$i]['ID'],$taskDays[0]['ID'],$taskDays[count($taskDays) - 1]['ID']));
								}
							}
						}
					}
					else {

						if (Request::get('taskID', Request::R_INT) && Request::get('taskDateStart')) {
							$SQL = sprintf(SQL_GANTT_TASK_STARTDATE_SAVE,Request::get('taskID', Request::R_INT),Request::get('taskDateStart'));
							$this->DB->Execute($SQL);
						}

						if (Request::get('taskID', Request::R_INT) && Request::get('taskDateEnd')) {
							$SQL = sprintf(SQL_GANTT_TASK_ENDDATE_SAVE,Request::get('taskID', Request::R_INT),Request::get('taskDateEnd'));
							$this->DB->Execute($SQL);
						}

						if (Request::get('taskID', Request::R_INT) && (Request::get('taskDateStart') || Request::get('taskDateEnd'))) {

							$taskID = Request::get('taskID', Request::R_INT);
							// get the new duration of this task that was just saved
							$taskSQL = sprintf(GET_TASK_DATES,$taskID);
							$task = $this->DB->QuerySingle($taskSQL);

							// get the day ids for the start and end epoch dates
							$taskDays = $this->DB->Query(sprintf(SQL_GET_DAYID,strtotime($task['StartDate'] . 'GMT'),strtotime($task['EndDate'] . 'GMT')));

							// delete any task resource days not in the new task day range that dont have any completed hours
							$this->DB->Execute(sprintf(SQL_DELETE_TASK_RESOURCE_DAY,$taskID,$taskDays[0]['ID'],$taskDays[count($taskDays) - 1]['ID']));

							// set the committed hours to 0 for the task days outside the range with completed hours
							$this->DB->Execute(sprintf(SQL_UPDATE_TASK_RESOURCE_DAY_COMMITTED,$taskID,$taskDays[0]['ID'],$taskDays[count($taskDays) - 1]['ID']));
						}
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

				$sourceGroup = explode(',',Request::get('sourceGroup'));
				for ($i = 0; $i < count($sourceGroup) || $i < 1; $i++) {
					if ($i == 0) {
						$sourceTaskID = Request::get('taskID', Request::R_INT);
						$destinationTaskID = Request::get('taskDestinationID', Request::R_INT);
					}
					else {
						$sourceTaskID = $sourceGroup[$i];
						$destinationTaskID = Request::get('taskID', Request::R_INT);
					}
						// get the sequence of the source task
					$SQL = sprintf(SQL_PROJECT_TASK_GET_SEQUENCE_BY_TASK, $sourceTaskID);
					$taskSourceSequence = $this->DB->QuerySingle($SQL);
					$taskSourceSequence = intval($taskSourceSequence['sequence']);

						// get the sequence of the destination task
					if ($destinationTaskID == -1) $taskDestinationSequence = 1;
					else {
						$SQL = sprintf(SQL_PROJECT_TASK_GET_SEQUENCE_BY_TASK, $destinationTaskID);
						$taskDestinationSequence = $this->DB->QuerySingle($SQL);
						$taskDestinationSequence = intval($taskDestinationSequence['sequence']);
					}

					// reorder up or down depending on the source and destination order
					if ($taskSourceSequence > $taskDestinationSequence) {
					if ($i != 0 ) $taskDestinationSequence++;
							$SQL = sprintf(SQL_GANTT_TASK_REORDER_UP,$taskSourceSequence,$taskDestinationSequence,$projectID);
							$this->DB->Execute($SQL);
					}
					else {
							$SQL = sprintf(SQL_GANTT_TASK_REORDER_DOWN,$taskSourceSequence,$taskDestinationSequence,$projectID);
							$this->DB->Execute($SQL);
					}
					// set source task order to destination task order
					$SQL = sprintf(SQL_GANTT_TASK_REORDER_SET,$sourceTaskID,$taskDestinationSequence);
					$this->DB->Execute($SQL);
				}
			}
				// *** end new code added 2006-03
				if ($projectID && Request::get('taskIDsToIndent') && strlen(Request::get('taskNewIndentLevel'))) {
					$taskIDsToIndent = explode(',',Request::get('taskIDsToIndent'));
					$taskNewIndentLevel = explode(',',Request::get('taskNewIndentLevel'));

					for ($i = 0; $i < count($taskIDsToIndent); $i++) {
						$this->DB->Execute(sprintf(UPDATE_TASK_INDENT_LEVEL,$taskNewIndentLevel[$i],$taskIDsToIndent[$i],$projectID));
					}

					echo 'redrawNeeded=YES&';
				}
			}

			echo 'taskEditSaveStatus=OK';
		}
		else {
			echo 'taskEditSaveStatus=ERROR&error=Access+Denied';
		}
	}
	// end new code

	function AjaxClientProjectList() {
			header('Content-Type: text/html; charset='.CHARSET);
		if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_READ))
		{
			$modHeader = '';

			$clients_access_list = $this->User->GetUserItemAccess('clients', CU_ACCESS_DENY);

			if ($clients_access_list == '-1') {
				$clients_access_list = '';
				$clientID = Request::get('clientid', Request::R_INT);
				//for ($i = 0; $i < count($clientIDs); $i++) {
					if ($this->User->HasUserItemAccess('clients',$clientID, CU_ACCESS_READ)) $clients_access_list .= $clientID;
					else $clients_access_list = NULL;
				//}

				//$clients_access_list = substr($clients_access_list, 0, -1);
			}

			$project_ids = $this->DB->Query(sprintf(SQL_SELECT_PROJECT_IDS, $clients_access_list));

			/*
			if (($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE)) && ($clients_access_list)) {
				$modAction[0] = '<a href="index.php?module=projects&amp;action=new">' . MSG_NEW_PROJECT . '</a>';
				$modAction[1] = '<a href="index.php?module=projects&amp;action=import">' . MSG_IMPORT . '</a>';
			}
			*/

			if (Request::get('archived', Request::R_INT) == 1) {
				$active = 0;
				$archive = 1;
				$modHeader .= MSG_ARCHIVES . ' (<a href="index.php?module=projects&amp;archived=0">'. MSG_VIEW . ' ' . MSG_ACTIVE . '</a>)';
			}
			else {
				$active = 1;
				$archive = 0;
				$modHeader .= MSG_ACTIVE . ' (<a href="index.php?module=projects&amp;archived=1">'. MSG_VIEW . ' ' . MSG_ARCHIVES . '</a>)';
			}

			$orderOptions = array('progress'				=> 'PercentComplete',
														'priority'				=> 'Priority',
														'status'					=> 'Status',
														'budget'					=> 'TargetBudget',
														'owner'						=> 'Owner',
														'starts'					=> 'StartDate',
														'ends'						=> 'EndDate',
														'project'					=> 'ProjectName',
														'latestactivity'	=> 'LatestActivity'
													);
			list($order, $orderby) = Request::$GET->filterRequest('order', $orderOptions, array('client', 'ClientName'));
			list($direction, $orderDir) = Request::$GET->filterOrderDirection();

			//Compile full project ID listing (allowable clients, and explicitly set projects)
			$project_access_list = $this->User->GetUserItemAccess($this->ModuleName, CU_ACCESS_READ);

			if($project_ids) {
				foreach($project_ids as $key => $value) {
					$project_list .= $value['ID'].',';
				}
				$project_list = substr($project_list, 0, -1);
			}

			if ($project_access_list) {
				if ($project_list) $project_list .= ','.$project_access_list;
				else $project_list = $project_access_list;
			}

			if ($project_list) {
				$SQL = sprintf(SQL_PROJECTS_LIST_FOR_CLIENT, $project_list, $active, $orderby, $orderdir, $clientID);
			}

			/*
			if ($offset == 'all') {
				$RS =& new DBRecordset();
				$RS->Open($SQL, $this->DB);
			}
			else {
				if (!is_numeric($offset)) $offset = 0;
				$RS =& new DBPagedRecordset();
				$RS->Open($SQL, $this->DB, $limit, $offset);
			}
			//~ paging code
			*/
			$RS =& new DBRecordset();
			$RS->Open($SQL, $this->DB);

			$tmpl['lblClient']	 = MSG_CLIENT;
			$tmpl['lblProject']	= MSG_SUBJECT;
			$tmpl['lblProgress'] = MSG_PROGRESS;
			$tmpl['lblPriority'] = MSG_PRIORITY;
			$tmpl['lblStatus']	 = MSG_STATUS;
			$tmpl['lblOwner']		= MSG_OWNER;
			$tmpl['lblBudget']	 = MSG_BUDGET;
			$tmpl['lblStarts']	  = MSG_STARTS;
			$tmpl['lblLatestActivity']	  = MSG_LATESTACTIVITY;
			$tmpl['lblEnds']	  = MSG_ENDS;
			$tmpl['lblArchive']	= 'archived='.$archive.'&';
			$tmpl['lblAsc']			= MSG_ASCENDING;
			$tmpl['lblDesc']		 = MSG_DESCENDING;
			$tmpl['start']			 = $offset;
			$this->setTemplate('ajax_project_header', $tmpl);
			unset($tmpl);

			if (!$RS->EOF()) {

				$counter = 1;
				while (!$RS->EOF()) {
					if ($counter > 1) $this->setTemplate('project_spacer');

					$tmpl['PROJECT_ID'] = $RS->Field('ID');
					$tmpl['PROJECT_NAME'] = $RS->Field('ProjectName');
					$tmpl['PROJECT_OWNER'] = 'Owner: '.$RS->Field('FirstName') . ' ' . $RS->Field('LastName');
					$tmpl['PROJECT_STARTDATE'] = Format::date($RS->Field('StartDate'));
					$tmpl['PROJECT_ENDDATE'] = Format::date($RS->Field('EndDate'));
					$tmpl['PERCENT_COMPLETE'] = @number_format($RS->Field('PercentComplete'));
					$tmpl['PERCENT_INCOMPLETE'] = 100 - $tmpl['PERCENT_COMPLETE'];
					$tmpl['PROJECT_COLOUR'] = $RS->Field('Colour');
					$tmpl['CLIENT_ID'] = $RS->Field('ClientID');

					//Group by Client Name
					$tmpl['CLIENT_NAME'] = $RS->Field('ClientName');

					/*
					$budgets = $this->DB->Query(sprintf(SQL_GET_TASK_BUDGETS,$RS->Field('ID')));
					$tbudget = 0;
					if ($budgets) {
							foreach($budgets as $key => $value) {
									$tbudget = $tbudget + $value['TargetBudget'];
							}
					}

					if ($this->User->HasModuleItemAccess('budget', CU_ACCESS_ALL, CU_ACCESS_READ)) {
						$tmpl['PROJECT_BUDGET'] = Settings::get('CurrencySymbol').@number_format($tbudget);
					}
					else {
						$tmpl['PROJECT_BUDGET'] = 'N/A';
					}
					*/

					/*
					switch($RS->Field('Priority')) {
							case "0": $tmpl['txtPriority'] = "<img src=\"images/icons/icon_priority_low.gif\" title=\"". MSG_PRIORITY_LOW ."\" border=\"0\">"; $tmpl['txtRolloverPriority'] = MSG_PRIORITY_LOW; break;
							case "1": $tmpl['txtPriority'] = "<img src=\"images/icons/icon_priority_med.gif\" title=\"". MSG_PRIORITY_NORMAL ."\" border=\"0\">"; $tmpl['txtRolloverPriority'] = MSG_PRIORITY_NORMAL; break;
							case "2": $tmpl['txtPriority'] = "<img src=\"images/icons/icon_priority_high.gif\" title=\"". MSG_PRIORITY_HIGH ."\" border=\"0\">"; $tmpl['txtRolloverPriority'] = MSG_PRIORITY_HIGH; break;
							default : $tmpl['txtPriority'] = "<img src=\"images/icons/icon_priority_med.gif\" title=\"". MSG_PRIORITY_NORMAL ."\" border=\"0\">"; $tmpl['txtRolloverPriority'] = MSG_PRIORITY_NORMAL;
					}
					*/

					$tmpl['PROJECT_STATUS'] = $this->StatusList[$RS->Field('Status')];

					$this->setTemplate('ajax_project_item', $tmpl);
					unset($tmpl);
					++$counter;
					$RS->MoveNext();
				}
				/*
				if ($RS->TotalRecords > $limit) {
					$url = 'index.php?module=projects&amp;archived='.$archive.'&amp;order='.$order.'&amp;direction='.$direction;
					cuPaginate($RS->TotalRecords, $limit, $url, $offset, $tmpl);
					$this->setTemplate('project_paging', $tmpl);
					unset($tmpl);
				}
				*/
				$this->setTemplate('ajax_project_footer');
			}
			else {
					$tmpl['MESSAGE'] = MSG_NO_PROJECTS_AVAILABLE;
									$tmpl['lblIcon'] = 'projects';
									$this->setTemplate('ajax_eof', $tmpl);
			}
			$RS->Close();
			unset($RS);

			$this->setHeader(MSG_PROJECTS);
			$this->setModule($modHeader, $modAction);
		}
		else {
			$this->ThrowError(2001);
		}
			$this->RenderOnlyContent();
		}

	function AjaxTaskList() {
		header('Content-Type: text/html; charset='.CHARSET);
		$projectID = Request::get('projectid', Request::R_INT);
		if (!$this->User->HasUserItemAccess($this->ModuleName, $projectID, CU_ACCESS_READ))
		{
			$this->ThrowError(2001);
			return;
		}

		$orderOptions = array(
			'owner'				=> 'Owner',
			'progress'		=> 'PercentComplete',
			'urgency'			=> 'Priority',
			'deadline'		=> 'EndDate',
			'duration'		=> 'Duration',
			'task'				=> 'Name'
		);
		
		list($order, $orderby) = Request::$GET->filterRequest('order', $orderOptions, array('sequence', 'sequence'));
		list($direction, $orderDir) = Request::$GET->filterOrderDirection();

		$tmpl['lblBudgets']			= MSG_BUDGETS;
		$tmpl['lblBreakdown']		= MSG_EDIT;
		$tmpl['lblContacts']		= MSG_CONTACTS;
		$tmpl['lblEmails']			= MSG_EMAILS;
		$tmpl['lblFiles']				= MSG_FILES;
		$tmpl['lblTimeline']		= MSG_TIMELINE;
		$tmpl['txtProjectID'] 	= $projectID;
		$tmpl['tasklist'] = '';
		
		$sql = sprintf(SQL_GET_PROJECT_TASKS, $projectID, $orderby, $orderdir);
		$rows = $this->DB->Query($sql);
		if (count($rows) > 0)
		{
			$hasBudgetRead = $this->User->HasModuleItemAccess('budget', CU_ACCESS_ALL, CU_ACCESS_READ);
			foreach ($rows as $task)
			{
				$tmplTask['indent'] = NULL;
				for ($i = 1; $i <= $task['Indent']; $i++) {
				  $tmplTask['indent'] .= '&nbsp;';
				}

				$tmplTask['txtTaskID'] = $task['ID'];
				$tmplTask['txtProjectID'] = $task['ProjectID'];
				$tmplTask['txtTaskName'] = $task['Name'];
				$tmplTask['txtClientName'] = $task['ClientName'];
				$tmplTask['txtPercentComplete'] = $task['PercentComplete'];
				$tmplTask['txtColour'] = $task['Colour'];
				$tmplTask['txtDescription'] = $task['Description'];
				$tmplTask['txtLatestActivity'] = Format::date($task['LatestActivity']);
				$tmplTask['txtStartDate'] = Format::date($task['StartDate']);
				$tmplTask['txtEndDate'] = Format::date($task['EndDate']);
				$tmplTask['txtPriority'] = Format::convert_priority($task['Priority']);
				$tmplTask['txtTargetBudget'] = ($hasBudgetRead) ? Format::money($task['TargetBudget']) : MSG_NA;
				$tmplTask['txtBudgetCost'] = ($hasBudgetRead) ? Format::money($task['ActualBudget']) : MSG_NA;

				$taskCharge = $this->DB->QuerySingle(sprintf(SQL_GET_TASK_CHARGE, $task['ID']));
				$tmplTask['txtBudgetCharge'] = ($hasBudgetRead) ? Format::money($taskCharge['Charge']) : MSG_NA;

				$issues = $this->DB->ExecuteScalar(sprintf(SQL_COUNT_TASK_ISSUES, $task['ID']));
				$tmplTask['txtIssue'] = ($issues > 0) ? ' <span class="issue" style="cursor:pointer">'.MSG_ISSUE.'</span>' : '';

				$tmpl['tasklist'] .= $this->getTemplate('project_list_task_item', $tmplTask);
			}
		}
		else
		{
			$tmpl['tasklist'] = MSG_NO_TASKS_AVAILABLE;
		}

		$this->setTemplate('project_list_tasks', $tmpl);
		$this->RenderOnlyContent();
	}

	function AjaxTaskView() {
		header('Content-Type: text/html; charset='.CHARSET);
		$projectID			= Request::get('projectid', Request::R_INT);
		$taskID 				= Request::get('taskid', Request::R_INT);
		$userID					= (int)$this->Session->Get('springboardID');
		$caller					= Request::get('caller');
		$callingModule	= ($caller == 'springboard') ? $caller : $this->ModuleName;

		// If the user can't read the project, throw an error unless the XHR call is from Springboard.
		$hasProjectRead = $this->User->HasUserItemAccess($this->ModuleName, $projectID, CU_ACCESS_READ);
		if (!$hasProjectRead && $caller != 'springboard')
		{
			$this->ThrowError(2001);
			return;
		}

		$task = $this->DB->QuerySingle(sprintf(SQL_TASKS_GET_DETAILS_ALL, $taskID));
		$this->Log('task', $taskID, 'view', $task['Name'], $projectID);

		if ($this->User->HasUserItemAccess($this->ModuleName, $projectID, CU_ACCESS_WRITE))
		{
			$actions[] = array('url' => '#', 
								'name' => MSG_EDIT, 
								'attrs' => "onclick=\"editTask($projectID, $taskID, '$callingModule'); return false;\""
							  );
							  
			if ($caller != 'springboard')
			{
				//$actions[] = array('url' => url::build_url('projects', 'taskcopy', "taskid=$taskID&projectid=$projectID"), 'name' => MSG_COPY);
				$actions[] = array('url' => '#', 
									'name' => MSG_COPY, 
									'attrs' => "onclick=\"copyTask($projectID, $taskID, '$callingModule'); return false;\""
								  );

				$actions[] = array('url' => url::build_url('projects', 'taskmove', "taskid=$taskID&projectid=$projectID"), 
									'attrs' => 'rel="linky"',
									'name' => MSG_MOVE
								  );
			}
			
			$actions[] = array('url' => url::build_url('projects', 'taskdel', "taskid=$taskID&projectid=$projectID"), 
								'name' => MSG_DELETE,
								'confirm' => 1, 
								'title' => MSG_CONFIRM_TASK_DELETE_TITLE, 
								'body' => MSG_CONFIRM_TASK_DELETE_BODY);
		}

		// Email Team link
		$ownerEmail = $task['EmailAddress'];
		$subject = $task['ClientName']." > ".$task['ProjectName']." > ".$task['Name']." - ".MSG_TEAM_NOTIFICATION;
		$userlist = $this->GetTaskUsers($taskID, $ownerEmail);
		$mailto = "mailto:$ownerEmail?Subject=".htmlentities($subject, ENT_COMPAT, CHARSET)."&bcc=$userlist";
		$actions[] = array('url' => $mailto, 'name' => MSG_EMAIL_TEAM);

		// Timer link
		$actions[] = array('url' => '#', 
							'name' => MSG_TIMER_NEW, 
							'attrs' => "class='js_timer_start' rel='$taskID'", 
							'title' => MSG_CONFIRM_TIMER_RESTART_TITLE, 
							'body' => MSG_CONFIRM_TIMER_RESTART_BODY
						  );

		$tmpl['actions'] = $this->ActionMenu($actions);

		$tmpl['lblActual']		  = MSG_ACTUAL;
		$tmpl['lblAttachedFiles']   = MSG_FILES;
		$tmpl['lblBudget']		  = MSG_BUDGET;
		$tmpl['lblCommentary']	  = MSG_COMMENTARY;
		$tmpl['lblCommentDate']	 = MSG_NOW;
		$tmpl['lblCommitted']	   = MSG_COMMITTED;
		$tmpl['lblContact']		 = MSG_CONTACT;
		$tmpl['lblDateSelect']	  = MSG_SELECT_DATE;
		$tmpl['lblDetails']		 = MSG_DETAILS;
		$tmpl['lblEstimated']	   = MSG_ESTIMATED;
		$tmpl['lblHours']		   = MSG_HOURS_UPPERCASE;
		$tmpl['lblHrs']			 = MSG_HRS;
		$tmpl['lblIssue']		   = MSG_ISSUE;
		$tmpl['lblNotBillable']	 = MSG_NOT_BILLABLE;
		$tmpl['lblOwner']		   = MSG_OWNER;
		$tmpl['lblPriority']		= MSG_PRIORITY;
		$tmpl['lblResources']	   = MSG_RESOURCES;
		$tmpl['lblSave']			= MSG_SAVE;
		$tmpl['lblStartsEnds']	  = MSG_STARTS_ENDS;

		$tmpl['txtColour']		  = $task['Colour'];
		$tmpl['txtDescription']	 = Format::blocktext($task['Description']);
		$tmpl['txtDuration']		= Format::hours($task['Duration']);
		$tmpl['txtHoursCommitted']  = Format::hours($task['HoursCommitted']);
		$tmpl['txtHoursWorked']	 = Format::hours($task['HoursWorked']);
		$tmpl['txtName']			= $task['Name'];
		$tmpl['txtOwner']		   = $task['FirstName'] . ' ' . $task['LastName'];
		$tmpl['txtPriority']		= Format::convert_priority($task['Priority']);
		$tmpl['txtProjectID']	   = $projectID;
		$tmpl['txtStartEndDate']	= sprintf('%s / %s', Format::date($task['StartDate']), Format::date($task['EndDate']));
		$tmpl['txtLatestActivity']  = Format::date($task['LatestActivity']);
		$tmpl['txtTaskID']		  = $taskID;
		$tmpl['txtUserID']		  = $userID;
		$tmpl['txtUsername']		= $this->User->Fullname;
		$tmpl['date_format']		= $this->date_format[Settings::get('DateFormat')];
		$tmpl['caller']			 = $caller;
		$tmpl['txtFileID']		  = 0;
		$tmpl['txtFileState']	   = 'new' . Request::get('taskid', Request::R_INT) . ':';
		$tmpl['txtClass']		   = 'filetype-misc';
		$tmpl['txtUrl']			 = url::build_url();
		$tmpl['txtAvatarImage'] = CopperUser::current()->get_avatar();
		
		//create percentage options ddl and set default value to the tasks percent complete.
		$percentage_options = '';
		for ($i=0; $i<=10; $i++) {
			$sel = (($i*10) == $task['PercentComplete'])?'selected="selected"':'';
			$percentage_options .= sprintf('<option value="%1$s" %2$s>%1$s</option>', ($i*10), $sel);
		}
		$tmpl['txtPercentageOptions'] = $percentage_options;

		$tmpl['budget'] = '';
		$hasBudgetRead = $this->User->HasModuleItemAccess('budget', CU_ACCESS_ALL, CU_ACCESS_READ);
		if ($hasBudgetRead)
		{
			$sql = sprintf(SQL_GET_TASK_CHARGE, $taskID);
			$charge = $this->DB->QuerySingle($sql);
			$budgetTmpl['txtBudgetCost'] = Format::money($task['ActualBudget']);
			$budgetTmpl['txtBudgetCharge'] = Format::money($charge['Charge']);
			$budgetTmpl['txtBudgetTarget'] = Format::money($task['TargetBudget']);
			$tmpl['budget'] = $this->getTemplate('ajaxtaskview_budget', $budgetTmpl);
		}

		$tmpl['filelist'] = '';
		$tmplFile['lblCheckout'] = MSG_VIEW;
		$tmplFile['lblDetails'] = MSG_DETAILS;
		$tmplFile['txtProjectID'] = $projectID;
		$tmplFile['txtTaskID'] = $taskID;
		$sql = sprintf(SQL_TASKS_GET_FILES, $taskID);
		$files = $this->DB->Query($sql);

		// The Flash file upload widget adds the protocol itself, so strip it here.
		$tmpl['txtTargetPath'] = str_replace(array('http://', 'https://'), '', url::build_url('files'));

		$tmpl['txtVersion'] = 0;
		foreach ($files as $f)
		{
			$tmplFile['txtFileID'] = $f['ID'];
			$tmplFile['txtFilename'] = $f['FileName'];
			$tmplFile['txtSize'] = Format::file_size($f['Size']);
			$tmplFile['txtDate'] = Format::date_time($f['Date'], Settings::get('PrettyDateFormat'));
			$tmplFile['txtActivity'] = $f['Activity'];
			$tmplFile['txtUser'] = $f['FirstName'].' '.$f['LastName'];

			switch ($f['Type']) 
			{
				case "application/msword": $tmplFile['txtClass'] = 'filetype-word'; break;
				case "application/vnd.ms-excel" : $tmplFile['txtClass'] = 'filetype-spreadsheet'; break;
				case "application/pdf": $tmplFile['txtClass'] = 'filetype-pdf'; break;
				case 'image/gif':
				case 'image/jpeg':
				case 'image/tiff':
				case 'image/png':
				case 'image/photoshop':
				case 'image/x-photoshop':
				case 'application/photoshop':
				case 'application/psd': $tmpl['txtClass'] = 'filetype-image'; break;
				case 'application/octet-stream':
					$pathinfo = pathinfo($f['FileName']);
					switch (strtolower($pathinfo['extension'])) 
					{
						case 'xls': $tmplFile['txtClass'] = 'filetype-spreadsheet'; break;
						case 'doc': $tmplFile['txtClass'] = 'filetype-word'; break;
						case 'docx': $tmplFile['txtClass'] = 'filetype-word'; break;
						case 'pdf': $tmplFile['txtClass'] = 'filetype-pdf'; break;
						case 'gif': 
						case 'jpeg': 
						case 'jpg': 
						case 'png': 
						case 'tiff': 
						case 'ps': $tmplFile['txtClass'] = 'filetype-image'; break;
						default: $tmplFile['txtClass'] = 'filetype-misc';
					}
					break;
				default: $tmplFile['txtClass'] = 'filetype-misc';
			}
			$tmpl['filelist'] .= $this->getTemplate('tasks_view_files_item', $tmplFile);
		}

		$tmpl['commentlist'] = '';
		$tmplComment['txtColour'] = $project['Colour'];
		$tmplComment['lblSave'] = MSG_SAVE;
		$tmplComment['lblEdit'] = MSG_EDIT;
		$tmplComment['lblContact'] = MSG_CONTACT;
		$tmplComment['txtProjectID'] = $projectID;
		$tmplComment['txtTaskID'] = $taskID;

		$sql = sprintf(SQL_TASKS_GET_COMMENTS, $taskID);
		// $comments = $this->DB->Query($sql);
		$comments = new TaskComments(array('where' => array('TaskID' => $taskID)));

		$sql = sprintf(SQL_GET_CLIENTS_BY_TASK, $taskID);
		$clients = $this->DB->Query($sql);
		$clientOptions = "<option value='0'></option>";
		foreach($clients as $client){
			$clientOptions .= "<option value='".$client['id']."'>".$client['Name']."</option>";
		}
		$tmplComment["txtClientOptions"]=$clientOptions;
		$tmpl["txtClientOptions"]=$clientOptions;
		$comment_count = 0;
		if (count($comments) > 0) 
		{
			foreach ($comments as $c) 
			{
			  $comment_count++;
			  $tmplComment['txtCommentID'] = $c->ID;
			  $tmplComment['txtPercentageMax'] = $task['PercentComplete'];

			  if ($comment_count == 1)
			  {
					$tmplComment['txtPercentageMax'] = 100;
			  }

			  $tmplComment['txtProjectView'] = '1';
			  $tmplComment['txtUsername'] = $c->owner->full_name;
			  $tmplComment['txtHours'] = ($c->HoursWorked > 0.00) ? $c->HoursWorked : '';
			  $tmplComment['txtHoursLbl'] = ($c->HoursWorked > 0.00) ? MSG_HRS : MSG_QUICK_UPDATE;
			  $tmplComment['txtContact'] = ($c->Contact) ? $c->Contact: "";

			  $tmplComment['txtBillable'] = ($c->OutOfScope == '1') ? ' '.MSG_NOT_BILLABLE : ' ';
			  
			  $tmplComment['txtBillableVal'] = ($c->OutOfScope == '1') ? '1' : '0';

			  $tmplComment['txtIssue'] = ($c->Issue == '1') ? ' <span class="issue">'.MSG_ISSUE.'</span>' : '';
			  $tmplComment['txtIssueChkd'] = ($c->Issue == '1') ? 'checked' : '';
			  $tmplComment['txtIssueVal'] = ($c->Issue == '1') ? '1' : '0';


			  $tmplComment['txtBody'] = Format::blocktext($c->Body);

			  $tmplComment['txtBodyValue'] = Format::for_textarea($c->Body);
			  $tmplComment['txtContactName']= $c->contact->url;
					
			  $tmplComment['txtPercentage'] = $c->task->PercentComplete;
				$tmplComment['txtAvatarImage'] = $c->owner->get_avatar();

			  // Display today/yesterday in date fields
			  $tmplComment['txtDay'] = Format::date($c->Date);
			  $tmplComment['txtTime'] = date('h:ia', strtotime($c->Date));
			  $tmplComment['txtDayValue'] = Format::date($c->Date, FALSE, FALSE);

			  $tmplComment['commentAlt'] = ($comment_count % 2 == 0) ? '' : 'altRow';

			  $hasAdmin = $this->User->HasModuleItemAccess('administration', CU_ACCESS_ALL, CU_ACCESS_READ);
			  if ($hasAdmin || (($c->UserID == $this->User->ID) && (Settings::get('TaskLogEdit') == 1))){
				  $tmplComment['txtEdit'] = '<li class="txtEdit"><a href="#" onclick="return springboard.editTaskComment('.$c->ID.');">'.MSG_EDIT.'</a></li>';
				  $tmplComment['txtDel'] = '<li class="txtDelete"><a href="#" onclick="springboard.removeTaskComment('.$c->ID.','.$projectID.','.$taskID.'); return false">X</a></li>';
			  } else {
				  $tmplComment['txtEdit'] = '';
				  $tmplComment['txtDel'] = '';
				}
			  $tmpl['commentlist'] .= $this->getTemplate('tasks_view_comment', $tmplComment);
			}
		}
		$tmpl['brfix'] = ($comment_count == 0) ? '<br />' : '';
		$tmpl['brfix'] = '<br /><br />';

		// Make taskcommentedit links work.
		$tmpl['txtCommentDate'] = Format::date(date('Y-m-d'), FALSE, FALSE);
		$tmpl['txtCommentID'] = $tmpl['txtComment'] = $tmpl['txtOldHours'] = $tmpl['txtHours'] = '';
		$tmpl['txtIsOutOfScope'] = $tmpl['txtIsIssue'] = '';
		$commentID = Request::get('commentid', Request::R_INT);
		if ($commentID > 0)
		{
			$userID = $this->DB->ExecuteScalar(sprintf(SQL_GET_COMMENT_USER, $commentID));

			$access = (
				(($action=='taskcommentedit') && (($this->User->HasModuleItemAccess('administration', CU_ACCESS_ALL, CU_ACCESS_READ)
				|| (($userid == $this->User->ID) && (Settings::get('TaskLogEdit') == 1)))))
				|| ($action == 'taskview')
			) ? TRUE : FALSE;

			if (($access) || ($this->UserIsAssigned($taskID)))
			{
				$SQL = sprintf(SQL_GET_COMMENT, $commentID, $taskID);
				$result = $this->DB->QuerySingle($SQL);
				if ($result)
				{
					$contact = $result['Contact'];
					$tmpl['txtCommentID'] = $commentID;
					$tmpl['txtOldHours'] = $result['HoursWorked'];
					$tmpl['txtHours'] = $result['HoursWorked'];
					$tmpl['txtIsIssue'] = ($result['Issue'] == 1) ? 'checked="checked"' : '';
					$date = split(" ", $result['Date']);
					$tmpl['txtCommentDate'] = Format::date($date[0], FALSE, FALSE);
					$tmpl['txtIsOutOfScope'] = ($result['OutOfScope'] == 1) ? 'checked="checked"' : '';
					$tmpl['txtComment'] = $result['Body'];
					$tmpl['lblCommentDate'] = $tmpl['txtCommentDate'];
				}
			}
		}

		// Task dependencies.
		$dependenciesSQL = sprintf(SQL_PROJECT_TASK_DEPENDENCIES, $taskID);
		$dependencies = $this->DB->Query($dependenciesSQL);
		$taskDependenciesCurrent = '';
		$taskDependenciesSelect = '';
		for ($i = 0; $i < count($dependencies); $i++) {	 // each dependency for this task
			for ($j = 0; $j < count($tasks); $j++) {
				if ($tasks[$j]['ID'] == $dependencies[$i]['TaskDependencyID'])
					break;
			}
			if ($dependencies[$i]['DependencyType'] == 1)
				$taskDependenciesCurrent .= sprintf(MSG_TASK_DEPENDENCY_THIS_STARTS_WHEN_TASK_FINISHES, '<strong>'.$dependencies[$i]['Name'].'</strong>').'<br />';
			if ($dependencies[$i]['DependencyType'] == 2)
				$taskDependenciesCurrent .= sprintf(MSG_TASK_DEPENDENCY_THIS_STARTS_WHEN_TASK_STARTS, '<strong>'.$dependencies[$i]['Name'].'</strong>').'<br />';
			if ($dependencies[$i]['DependencyType'] == 3)
				$taskDependenciesCurrent .= sprintf(MSG_TASK_DEPENDENCY_THIS_FINISHES_WHEN_TASK_FINISHES, '<strong>'.$dependencies[$i]['Name'].'</strong>').'<br />';
		}
		if (!$taskDependenciesCurrent)
			$taskDependenciesCurrent = '';
		$tmpl['taskDependencies'] = $this->getTemplate('tasks_dependencies', array('taskDependenciesCurrent' => $taskDependenciesCurrent));

		// Resource Widget
		if (Settings::get('ResourceManagement') == 0) {
			$resources = $this->DB->Query(sprintf(SQL_GET_TASK_RESOURCES,$taskID));
			$simple_tmpl['resource_item'] = '<dd>'.MSG_NO_RESOURCES_ASSIGNED.'</dd><dd class="divider">&nbsp;</dd>';
			foreach ($resources as $key => $value) 
				$resource_items .= '<dd>'.$value['FullName'].'</dd><dd class="divider">&nbsp;</dd>';
			if ($resource_items)
				$simple_tmpl['resource_item'] = $resource_items;
			$tmpl['resourceManagement'] = $this->getTemplate('resource_management_simple',$simple_tmpl);
		}
		else
			$tmpl['resourceManagement'] = $this->getTemplate('resource_management_advanced', array('txtTaskID' => $taskID));

		$this->setTemplate('project_view_task_item_details', $tmpl);

		$this->RenderOnlyContent();
	}

	function AjaxAddRelatedProject() {
		$projectID = Request::get('projectid', Request::R_INT);
		$relatedProjectID = Request::get('relatedprojectid', Request::R_INT);

		if (!$this->User->HasUserItemAccess($this->ModuleName, $projectID, CU_ACCESS_WRITE))
		{
			header('HTTP/1.0 401 Unauthorized');
			return;
		}

		header('Content-Type: text/html; charset='.CHARSET);

		$sql = sprintf(SQL_INSERT_RELATED_PROJECT, $projectID, $relatedProjectID);
		$this->DB->Execute($sql);

		// Return the related project item HTML to the AJAX call.
		$tmpl['RelatedProjectID'] = $relatedProjectID;
		$tmpl['Name'] = $this->DB->ExecuteScalar(sprintf(SQL_GET_PROJECT_NAME, $relatedProjectID));
		$html = $this->getTemplate('related_project_item_edit', $tmpl);
		echo $html;
	}

	function AjaxRemoveRelatedProject() {
		$projectID = Request::get('projectid', Request::R_INT);
		$relatedProjectID = Request::get('relatedprojectid', Request::R_INT);

		if (!$this->User->HasUserItemAccess($this->ModuleName, $projectID, CU_ACCESS_WRITE))
		{
			header('HTTP/1.0 401 Unauthorized');
			return;
		}

		header('Content-Type: text/html; charset='.CHARSET);

		$sql = sprintf(SQL_DELETE_RELATED_PROJECT, $projectID, $relatedProjectID);
		$this->DB->Execute($sql);

		// Return the related project name to the AJAX call - it gets put into the <option> list.
		$name = $this->DB->ExecuteScalar(sprintf(SQL_GET_PROJECT_NAME, $relatedProjectID));
		echo $name;
	}

	function AjaxAddAccess() {
		$projectID = Request::get('projectid', Request::R_INT);
		$mode = Request::get('mode');
		// this may not be a number, as if it's groups it's made up of 'g' + id of group, ie g2 for group 2.
		$id = Request::get('id');
		$accessID = ($mode == 'write') ? CU_ACCESS_WRITE : CU_ACCESS_READ;

		if (!$this->User->HasUserItemAccess($this->ModuleName, $projectID, CU_ACCESS_WRITE))
		{
			header('HTTP/1.0 401 Unauthorized');
			return;
		}

		header('Content-Type: text/html; charset='.CHARSET);

		if ($id[0] == 'g')
		{
			$groupID = substr($id, 1);
			$tmpl['txtName'] = MSG_GROUP.': '.$this->DB->ExecuteScalar(sprintf(SQL_GET_GROUP_NAME, (int)$groupID));

			$sql = sprintf(SQL_CREATE_GROUP_PERMISSIONS, $groupID, $projectID, $accessID);
			$this->DB->Execute($sql);
		}
		else
		{
			$tmpl['txtName'] = $this->DB->ExecuteScalar(sprintf(SQL_GET_USERNAME, (int)$id));

			$sql = sprintf(SQL_CREATE_USER_PERMISSIONS, (int)$id, $projectID, $accessID);
			$this->DB->Execute($sql);
		}

		$tmpl['txtID'] = $id;
		$tmpl['txtMode'] = $mode;
		$html = $this->getTemplate('permission_item', $tmpl);
		echo $html;
	}

	function AjaxRemoveAccess() {
		$projectID = Request::get('projectid', Request::R_INT);
		$mode = Request::get('mode');
		// this may not be a number, as if it's groups it's made up of 'g' + id of group, ie g2 for group 2.
		$id = Request::get('id');

		if (!$this->User->HasUserItemAccess($this->ModuleName, $projectID, CU_ACCESS_WRITE))
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

			$sql = sprintf(SQL_DELETE_GROUP_PERMISSIONS, $groupID, $projectID);
			$this->DB->Execute($sql);
		}
		else
		{
			$name = $this->DB->ExecuteScalar(sprintf(SQL_GET_USERNAME, (int)$id));
			$isGroup = 0;
			$sql = sprintf(SQL_DELETE_USER_PERMISSIONS, (int)$id, $projectID, $accessID);
			$this->DB->Execute($sql);
		}

		echo "{name:'".$name."',isGroup:".$isGroup."}";
	}

	function AjaxSaveTaskOrder() {
		parse_str($_POST['data']);
		$data = array();
		$this->SaveTaskOrderDFS($tasks, 0, $data);
		$order = 1;
		foreach ($data as $taskID => $indent)
		{
			$this->DB->Execute(sprintf(SQL_UPDATE_TASK_SEQUENCE_INDENT, $order, $indent - 1, $taskID));
			$order++;
		}
	}

	function SaveTaskOrderDFS($tasks, $depth, &$data) {
		// Converts a nested array of tasks as provided by Sortable.serialize()
		// into an ordered array with the order as the position in the array,
		// the key as the task ID and indent as value ie.
		// array ( 123 => 1, 456 => 2, 789 => 1 ).
		foreach ($tasks as $k => $v)
		{
			if (is_array($tasks[$k]))
				$this->SaveTaskOrderDFS($tasks[$k], $depth+1, $data);
			elseif ($k == 'id')
				$data[$v] = $depth;
		}
	}

	function AjaxTaskCopy() {
		$this->AjaxTaskEdit(true);
	}

	function AjaxTaskEdit($isCopy = null) {
		header('Content-Type: text/html; charset='.CHARSET);
		$projectID = Request::get('projectid', Request::R_INT);
		$taskID = Request::get('taskid', Request::R_INT);
		$caller = Request::get('caller');

		if (!$this->User->HasUserItemAccess($this->ModuleName, $projectID, CU_ACCESS_WRITE))
		{
			$this->ThrowError(2001);
			return;
		}

		$task 		= new Task($taskID);
		$project	= $task->exists ? $task->project : new Project($projectID);

		$tmpl['txtColour']					= $project->Colour; 
		$tmpl['txtDate']						= Format::date(date('Y-m-d'), TRUE, FALSE);
		$tmpl['txtDuration']				= Format::hours($task->Duration);
		$tmpl['txtHoursCommitted']	= Format::hours($task->HoursCommitted);
		$tmpl['txtHoursWorked']			= Format::hours($task->HoursWorked);
		$tmpl['txtName']						= $task->Name;
		$tmpl['txtOwner']						= $task->owner->full_name;
		$tmpl['txtPercentComplete']	= (int)$task->PercentComplete;
		$tmpl['txtPriority']				= Format::convert_priority($task->Priority);
		$tmpl['txtProjectID']				= $projectID;

		// danger will robinson! We can't just use an empty id, as then 
		// a) the javascript fails as it has an empty argument
		// b) it's bad practice
		// c) it generates multiple ids that are the same on the page
		// d) did i mention it's bad practice? Instead, prefix with a dummy text 
		//	(So we don't clash with the taskID set, and give it a suffix, so we can try
		//	 not to clash with ourselves.
		if ((!$isCopy) && ($task->exists)) {
		  $tmpl['txtTaskID'] = $taskID;
		  $tmpl['txtNewTaskFormId'] = 'null';
		} else {
		  $timeNonce = time();
		  $tmpl['txtTaskID'] = "new_" . $timeNonce;
		  $tmpl['txtNewTaskFormId'] = $timeNonce;
		}

		$tmpl['txtUsername']		= $this->User->Fullname;
		$tmpl['date_format']		= $this->date_format[Settings::get('DateFormat')];
		$tmpl['txtSequence']		= ($task->exists) ? $task->Sequence : $project->get_highest_sequence() + 1;
		$tmpl['txtClientID']		= $task->ClientID;
		$tmpl['txtClientName']	= $task->ClientName;
		$tmpl['txtProjectName']	= $task->ProjectName;
		$tmpl['caller']					= $caller;
		$tmpl['r']							= rand();

		$latest = ($task->exists) ? $task->LatestActivity : date('Y-m-d');
		$tmpl['txtLatestActivity']	= Format::date($latest);
		$tmpl['latestActivity'] 		= '';

		// Set the default start and end dates for new tasks to "today".
		$sd = ($task->exists) ? $task->StartDate : date('Y-m-d');
		$tmpl['txtStartDate']	= Format::date($sd);
		$tmpl['startDate']		= Format::date($sd, FALSE, FALSE);

		$ed = ($task->exists) ? $task->EndDate : date('Y-m-d');
		$tmpl['txtEndDate']		= Format::date($ed);
		$tmpl['endDate']			= Format::date($ed, FALSE, FALSE);

		if ($caller == 'springboard')
			$tmpl['cancelUrl'] = url::build_url('springboard', 'taskview', "projectid=$projectID&taskid=$taskID");
		else
			$tmpl['cancelUrl'] = url::build_url('projects', 'taskview', "projectid=$projectID&taskid=$taskID");

		$tmpl['txtDescription'] = Format::for_textarea($task->Description);

		$tmpl['txtBudgetTarget'] = '';
		$hasBudgetWrite = $this->User->HasModuleItemAccess('budget', CU_ACCESS_ALL, CU_ACCESS_WRITE);
		if ($hasBudgetWrite)
		{
			$budgetTmpl['txtBudgetTarget'] = $task->TargetBudget;
			$tmpl['txtBudgetTarget'] = $this->getTemplate('ajaxedit_budget', $budgetTmpl);
		}

		$users = new CopperUsers(array(
			'where' 	=> array('active' => 1),
			'orderby'	=> array('FirstName', 'LastName')
		));
		
		foreach ($users as $user)
		{
			if ($task->exists)
			{
			  $selected = ($user->ID == $task->owner->ID) ? ' selected' : '';
			} else
			{
			  $selected = ($user->ID == $project->owner->ID) ? ' selected' : '';
			}

			$tmpl['ownerOptions'] .= "<option value='" . $user->ID . "'$selected>" . $user->full_name . "</option>";
		}

		foreach ($this->PriorityList as $k => $v)
		{
			if ($task->exists) 
			{
				$selected = ($task->Priority == $k) ? ' selected' : '';
			} else {
				$selected = ($k == '1') ? ' selected' : '';
			}
		  $tmpl['priorityOptions'] .= "<option value=\"$k\"$selected>$v</option>\n";
		}

		$tasks = new Tasks(array(
			'sql_where' => "WHERE ID != ? AND ProjectID = ?", 
			'sql_where_values' => array($task->ID, $project->ID),
			'orderby' => 'sequence',
		));
		
		$dependenciesSQL = sprintf(SQL_PROJECT_TASK_DEPENDENCIES, $taskID);
		$dependencies = $this->DB->Query($dependenciesSQL);
		$taskDependenciesCurrent = '';
		$taskDependenciesSelect = '';
		for ($i = 0; $i < count($dependencies); $i++) { // each dependency for this task
			for ($j = 0; $j < count($tasks); $j++) {
				if ($tasks[$j]->ID == $dependencies[$i]['TaskDependencyID'])
					break;
			}

			$tmplDep = array();
			if ($dependencies[$i]['DependencyType'] == 1)
				$tmplDep['msg'] = sprintf(MSG_TASK_DEPENDENCY_THIS_STARTS_WHEN_TASK_FINISHES, '<strong>'.$dependencies[$i]['Name'].'</strong>');
			if ($dependencies[$i]['DependencyType'] == 2)
				$tmplDep['msg'] = sprintf(MSG_TASK_DEPENDENCY_THIS_STARTS_WHEN_TASK_STARTS, '<strong>'.$dependencies[$i]['Name'].'</strong>');
			if ($dependencies[$i]['DependencyType'] == 3)
				$tmplDep['msg'] = sprintf(MSG_TASK_DEPENDENCY_THIS_FINISHES_WHEN_TASK_FINISHES, '<strong>'.$dependencies[$i]['Name'].'</strong>');

			$tmplDep['projectID'] = $projectID;
			$tmplDep['taskID'] = $taskID;
			$tmplDep['otherTaskID'] = $dependencies[$i]['TaskDependencyID'];
			$tmplDep['dependency'] = $dependencies[$i]['DependencyType'];
			$taskDependenciesCurrent .= $this->getTemplate('tasks_form_dependencies', $tmplDep);
		}

		for ($i = 0; $i < count($tasks); $i++) {	// each task in this project
			for ($j = 0; $j < count($dependencies); $j++) { // each dependency for this task
				if ($dependencies[$j]['TaskDependencyID'] == $tasks[$i]->ID) {	// current dependency is on current task
					break;  // break before end of dependencies
				}
			}
			if ($j == count($dependencies)) {   // this task was not found in dependencies list
				$taskDependenciesSelect .= '<option value="'.$tasks[$i]->ID.',1">'.sprintf(MSG_TASK_DEPENDENCY_THIS_STARTS_WHEN_TASK_FINISHES, $tasks[$i]->Name).'</option>';
				$taskDependenciesSelect .= '<option value="'.$tasks[$i]->ID.',2">'.sprintf(MSG_TASK_DEPENDENCY_THIS_STARTS_WHEN_TASK_STARTS, $tasks[$i]->Name).'</option>';
				$taskDependenciesSelect .= '<option value="'.$tasks[$i]->ID.',3">'.sprintf(MSG_TASK_DEPENDENCY_THIS_FINISHES_WHEN_TASK_FINISHES, $tasks[$i]->Name).'</option>';
			}
		}

		if (!$taskDependenciesCurrent)
			$taskDependenciesCurrent = '';

		$tmpl['taskDependenciesCurrent'] = $taskDependenciesCurrent;
		$tmpl['taskDependenciesSelect'] = $taskDependenciesSelect;

		// Dependencies require a task ID, but new tasks don't have a task ID yet,
		// so use a separate template that doesn't have the dependency drop down.
		$template = ($caller == 'springboard') ? 'springboard_task_form' : (($task->exists) ? 'project_view_task_item_form' : 'project_view_task_item_new_form');
		
		$tmpl['Resources'] = NULL;
			// Resource Area
		if (Settings::get('ResourceManagement') == 0) {
			$resource_array[] = 0;
			// Display resources that are assigned
			$resources = $this->DB->Query(sprintf(SQL_GET_TASK_RESOURCES,$taskID));
			foreach ($resources as $key => $value) {
			  $resource_array[] = $value['ID'];
			  $tmplResource['txtID'] = $value['ID'];
			  $tmplResource['taskID'] = $taskID;
			  $tmplResource['txtName'] = $value['FullName'];
			  $resource_items .= $this->getTemplate('resource_item', $tmplResource);
			}
			$simple_tmpl['Resources'] = $resource_items;
			// build options
			if (is_array($resource_array)) {
			  $available_resources = $this->DB->Query(sprintf(SQL_GET_RESOURCES_MINUS,implode(",",$resource_array)));
			  if (is_array($available_resources)) {
				foreach($available_resources as $key => $value)
				  $simple_tmpl['resourceOptions'] .= sprintf('<option value="%s">%s</value>',$value['ID'],$value['FullName']);
			  }
			}
			$simple_tmpl['taskID'] = $taskID; 
			$tmpl['Resources'] = $this->getTemplate('resource_management_simple_form',$simple_tmpl);
		}
		$this->setTemplate($template, $tmpl);

		$this->RenderOnlyContent();
	}

	function AjaxTaskUnedit() {
		header('Content-Type: text/html; charset='.CHARSET);
		$projectID = Request::get('projectid', Request::R_INT);
		$taskID = Request::get('taskid', Request::R_INT);
		$projectColour = $this->DB->ExecuteScalar(sprintf(SQL_GET_PROJECT_COLOUR_FOR_TASK, $taskID));

		if (!$this->User->HasUserItemAccess($this->ModuleName, $projectID, CU_ACCESS_WRITE))
		{
			$this->ThrowError(2001);
			return;
		}
		$SQL = sprintf(SQL_GET_PROJECT_TASKS_WITH_HOURS_SINGLE, $taskID);
		$RS =& new DBRecordset();
		$RS->Open($SQL, $this->DB);
		$tmpl['lblTask'] = MSG_TASK;
		$tmpl['lblOwner'] = MSG_OWNER;
		$tmpl['lblEstPercent'] = MSG_EST_PERCENT;
		$tmpl['lblPriority'] = MSG_PRIORITY;
		$tmpl['lblHours'] = MSG_HOURS_UPPERCASE;
		$tmpl['lblEst'] = MSG_EST;
		$tmpl['lblCom'] = MSG_COM;
		$tmpl['lblAct'] = MSG_ACT;
		$tmpl['lblStart'] = MSG_START;
		$tmpl['lblEnd'] = MSG_END;

		if (!$RS->EOF())
		{
			while (!$RS->EOF())
			{
				$taskID = $RS->Field('ID');
				$indent = $RS->Field('Indent');

				$tmpl['txtTaskID'] = $taskID;
				$tmpl['txtProjectID'] = $projectID;
				$tmpl['txtColour'] = $projectColour;
				$tmpl['txtDuration'] = Format::hours($RS->Field('Duration'));
				$tmpl['txtEndDate'] = Format::date($RS->Field('EndDate'));
				$tmpl['txtHoursCommitted'] = Format::hours($RS->Field('HoursCommitted'));
				$tmpl['txtHoursWorked'] = Format::hours($RS->Field('HoursWorked'));
				$tmpl['txtName'] = $RS->Field('Name');
				$tmpl['txtOwner'] = $RS->Field('FirstName') . ' ' . $RS->Field('LastName');
				$tmpl['txtPercentComplete'] = (int)$RS->Field('PercentComplete');
				$tmpl['txtStartDate'] = Format::date($RS->Field('StartDate'));
				$tmpl['txtLatestActivity'] = Format::date($RS->Field('LatestActivity'));
				$tmpl['txtUsername'] = $this->User->Fullname;
				$tmpl['txtDate'] = Format::date(date('Y-m-d'), TRUE, FALSE);
				$tmpl['txtPriority'] = Format::convert_priority($RS->Field('Priority'));
				$issues = $this->DB->ExecuteScalar(sprintf(SQL_COUNT_TASK_ISSUES, $taskID));
				if ($issues > 0)
						$tmpl['txtName'] .= ' <span class="issue">'.MSG_ISSUE.'</span>';
				$taskStr = $this->getTemplate('project_view_task_item_single', $tmpl);

				//handle the indent factor

				if(($indent==$indentLevel)&&($taskCounter>0)){
					$taskStr = "</ul></li>".$taskStr;
				}
				if($indent<$indentLevel){
					$levelDiff = ($indentLevel - $indent)+1;
					$taskStrInsert = "";
					for($x=0;$x<$levelDiff;$x++){
						$taskStrInsert .= "</ul></li>";
					}
					$taskStr = $taskStrInsert.$taskStr;
				}
				$tmpl['txtTasks'] .= $taskStr;
				$indentLevel = $indent;
				$taskCounter++;
				$RS->MoveNext();
			}
				echo $tmpl['txtTasks'];
		} else echo "cancel";
			$this->RenderOnlyContent();
	}

	function CalculateDuration($hours) {
		$duration = round($hours, 2);
		if (($duration > DAY_LENGTH) && (Settings::get('ConvertToDays') == 1)){
			$duration = round($duration / DAY_LENGTH, 1);
			$dt = ($duration != 1) ? MSG_DAYS : MSG_DAY;
		}
		else {
			$dt = ($duration != 1) ? MSG_HOURS : MSG_HOUR;
		}
		return $duration . ' '.$dt;
	}

	function SelectPercentage($val) {
		$list = null;
		for ($i = 0; $i <= 100; $i += 5) {
			$list .= sprintf('<option value="%1$s"%2$s>%1$s</option>', $i, ($val == $i) ? ' SELECTED' : '');
		}
		return $list;
	}

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
	}

// used by gantt/ task data to encode data passed to flash into utf-8
	function encodeCommas($s, $andSemicolons = 1) {
		$s = html_entity_decode($s);
		if ($andSemicolons) $s = str_replace(';','#:#',$s);
		return rawurlencode(utf8_encode(str_replace(',','#<#',$s)));
	}
	function encodeCommasNoUTF8($s, $andSemicolons = 1) {
		$s = html_entity_decode($s);
		if ($andSemicolons) $s = str_replace(';','#:#',$s);
		return rawurlencode(str_replace(',','#<#',$s));
	}
}

 
