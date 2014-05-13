<?php
// $Id$
class mod_files extends Module
{

	function mod_files() {
		$this->ModuleName	 = 'files';
		$this->ModuleID	  = '45B963397AA40D4A0063E0D85E4FE7A1';
		$this->RequireLogin = (Request::any('action') == 'swffilesave') ? 0 : 1;
		$this->Public		 = 0;
		parent::Module();
	}

	function main() {
		switch (Request::any('action')) {
			case 'public'			: $this->ViewFileGroups(); break;
			case 'fileview'		  : $this->ViewFileGroups(); break;
			case 'filedown'		  : $this->FileDownload(); break;
			case 'filenew'		   : $this->AjaxFileNew(); break;
			case 'fileedit'		  : $this->FileEdit(); break;
			case 'filedetails'	   : $this->FileDetails(); break;
			case 'swffilesave'	   : $this->swfFileSave(); break;
			case 'filedel'		   : $this->FileDelete(); break;
			case 'showfiles'		 : $this->ShowFiles(); break;
			case 'hidefiles'		 : $this->HideFiles(); break;
			case 'ajaxclientfolders' : $this->AjaxClientFolders(); break;
			case 'ajaxprojectfolders': $this->AjaxProjectFolders(); break;
			case 'ajaxfileview'	  : $this->AjaxFileView(); break;
			case 'ajaxfileedit'	  : $this->AjaxFileEdit(); break;
			case 'projecttasks'	  : $this->ProjectTasks(); break;
			case 'getfileid'		 : $this->AjaxGetFileId(); break;
			case 'savedragdrop'	  : $this->SaveDragDrop(); break;
			case 'renamefolder'	  : $this->RenameFolder(); break;
			case 'deletefolder'	  : $this->DeleteFolder(); break;
			case 'foldernew'		 : $this->NewFolder(); break;
			case 'savenewfolder'	 : $this->SaveNewFolder(); break;
			case 'ajaxfolder'		: $this->AjaxFolder(); break;
			case 'ajaxfileversion'   : $this->AjaxFileVersion(); break;
			case 'ajaxfiledetails'   : $this->AjaxFileDetails(); break;
			default				  : $this->ViewFileGroups();
		}
	}

	function AjaxFileDetails() {
		$fileID = Request::get('fileid', Request::R_INT);
		$sql = sprintf(SQL_FILES_GET_DETAILS_ALL, $fileID);
		$f = $this->DB->QuerySingle($sql);

		$tmplFile['txtFileID'] = $f['ID'];
		$tmplFile['txtFilename'] = $f['FileName'];
		$tmplFile['txtSize'] = Format::file_size($f['Size']);
		$tmplFile['txtDate'] = Format::date_time($f['Date'], Settings::get('PrettyDateFormat'));
		$tmplFile['txtActivity'] = $f['Activity'];
		$tmplFile['txtUser'] = $f['FirstName'].' '.$f['LastName'];
		$tmplFile['txtClass'] = $this->getIconClassForFile($f);

		echo $this->getTemplate('tasks_view_files_item', $tmplFile);
	}

	function AjaxFileVersion() {
		$fileID = Request::get('fileid', Request::R_INT);
		$sql = sprintf(SQL_FILES_GET_DETAILS_ALL, $fileID);
		$file = $this->DB->QuerySingle($sql);

		// Build path to file
		$trailer = substr(SYS_FILEPATH, strlen(SYS_FILEPATH) - 1, 1);
		$filepath = ( ($trailer != '\\') && ($trailer != '/') ) ? SYS_FILEPATH . '/' : SYS_FILEPATH;
		$projectDir = str_pad($file['ProjectID'], 7, '0', STR_PAD_LEFT);
		$taskDir = str_pad($file['TaskID'], 7, '0', STR_PAD_LEFT);
		$filename  = $filepath . $projectDir . '/' . $taskDir . '/' . $file['RealName'];

		$sql = sprintf(SQL_FILES_GET_ACTIVITY, $fileID);
		$logs = $this->DB->Query($sql);
		if (is_array($logs))
		{
			foreach ($logs as $log)
			{
				list($date, $time) = explode(' ', $log['Date']);
				$logTmpl['txtFileName'] = (empty($log['FileName'])) ? $file['FileName'].'&#42;' : $log['FileName'];
				$logTmpl['txtSize'] = (empty($log['Size'])) ? Format::file_size($file['Size']) : Format::file_size($log['Size']);
				$logTmpl['txtDate'] = Format::date_time($log['Time'], Settings::get('PrettyDateFormat'));
				$logTmpl['txtVersion'] = $log['Version'];
				$logTmpl['txtActivity'] = $log['Activity'];
				$logTmpl['txtUser'] = $log['User'];
				$logTmpl['txtClass'] = (empty($log['Type'])) ? $this->getIconClassForFile($file) : $this->getIconClassForFile($log);
				$logTmpl['txtDownload'] = '';

				// Show link to this version of the file if it exists
				if (is_dir($filename))
					$versionFilename = $filename . '/' . $file['RealName'] . '_' . round( $log['Version'] );

				if ( is_file( $versionFilename ) && ($log['Activity'] == MSG_CHECKED_IN || $log['Activity'] == MSG_UPLOADED))
					$logTmpl['txtDownload'] = '<a href="index.php?module=files&fileid='.$fileID.'&action=filedown&version='.round($log['Version']).'">'.strtoupper(MSG_DOWNLOAD).'</a>';
				$html .= $this->getTemplate('file_version', $logTmpl);
			}
		}

		echo $html;
	}

	function AjaxFolder() {
		$tmpl['txtProjectID'] = Request::get('projectid', Request::R_INT);
		$tmpl['txtFolderID'] = Request::get('folderid', Request::R_INT);
		$tmpl['txtSortOrder'] = '';
		$tmpl['txtSortDirection'] = '';
		$tmpl['txtFolderName'] = $this->DB->ExecuteScalar(sprintf(SQL_GET_FOLDER_NAME, $tmpl['txtFolderID']));
		$tmpl['txtSize'] = (int)$this->DB->ExecuteScalar(sprintf(SQL_COUNT_FILES_IN_FOLDER, $tmpl['txtFolderID'])).' '.MSG_FILES;

		$module = Request::get('caller');
		$template = ($module == 'projects') ? 'folder_project_with_margin' : 'folder_project';
		echo $this->getTemplate($template, $tmpl);
	}

	function NewFolder() {
		$projectID = (Request::get('projectid', Request::R_INT) == null) ? 0 : Request::get('projectid', Request::R_INT);
		$tmpl['txtProjectID'] = $projectID;
		echo $this->getTemplate('folder_new', $tmpl);
	}

	function SaveNewFolder() {
		$folderName = Request::get('name');
		$projectID = Request::get('projectid', Request::R_INT);
		$sql = sprintf(SQL_INSERT_FOLDER, $projectID, $folderName);
		$this->DB->Execute($sql);
		$folderID = $this->DB->ExecuteScalar(SQL_LAST_INSERT);
		echo "{\"folderID\":$folderID}";
	}

	function DeleteFolder() {
		$id = Request::get('id', Request::R_INT);
		$confirm = Request::get('confirm');
		if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE) && $confirm == 1 && $id > 0) {
			$folder = $this->DB->QuerySingle(sprintf(SQL_GET_FOLDER_DETAILS, $id));

			// Get the folder tree, and delete all the folder nodes.
			$folderIDs = $this->GetFolderSubfoldersDFS($id, array($id));
			foreach ($folderIDs as $folderID) {
				$this->DB->Execute(sprintf(SQL_DELETE_FOLDER, $folderID));
			}

			// Get the files in the folder tree, and delete them.
			$fileIDs = array();
			$files = $this->DB->Query(sprintf(SQL_GET_FILES_IN_FOLDERS, implode(',', $folderIDs)));
			foreach ($files as $file) {
				cuDeleteFile($file);
				$this->DB->Execute(sprintf(SQL_FILE_DELETE, $file['ID']));
				$this->DB->Execute(sprintf(SQL_FILE_DELETE_HISTORY, $file['ID']));
			}
		}

		if ($folder['ProjectID'] > 0)
			Response::redirect('index.php?module=projects&action=filelist&projectid='.$folder['ProjectID']);
		else
			Response::redirect('index.php?module=files');
	}

	function GetFolderSubfoldersDFS($folderID, $folderIDs) {
		$rows = $this->DB->Query(sprintf(SQL_GET_FOLDER_SUBFOLDERS, $folderID));
		if (count($rows) > 0) {
			foreach ($rows as $r) {
				$folderIDs[] = $r['ID'];
				$folderIDs = $this->GetFolderSubfoldersDFS($r['ID'], $folderIDs);
			}
		}
		return $folderIDs;
	}

	function RenameFolder() 
	{
		$folder = new Folder(Request::get('id', Request::R_INT));
		$folder->Folder = Request::get('name');
		$folder->commit();
		
		if (Request::any('ajax'))
		{
			$ar = new AjaxResponse($folder->exists);
			$ar->folder = array(
				'ID' => $folder->ID,
				'ProjectID' => $folder->ProjectID,
				'Folder' => $folder->Folder,
				'ParentID' => $folder->ParentID
			);

			$ar->out();
		}
		
		// normal doesn't spit out anything.
	}

	function SaveDragDrop() {
		$drag = Request::get('drag');
		$drop = Request::get('drop');
		$pos = Request::get('position');

		list($dragType, $dragID) = explode('_', $drag);
		list($dropType, $dropID) = explode('_', $drop);

		if ($dragType == 'file' && $dropType == 'folder') { // File dropped onto or alongside a folder
			if ($pos == 'top' || $pos == 'bottom') {
				$sql = sprintf(SQL_GET_FOLDER_PARENT_ID, $dropID);
				$folderID = (int)$this->DB->ExecuteScalar($sql);
				$sql = sprintf(SQL_MOVE_FILE, $folderID, $dragID);
				$this->DB->Execute($sql);
			} elseif ($pos == 'insert') {
				$sql = sprintf(SQL_MOVE_FILE, $dropID, $dragID);
				$this->DB->Execute($sql);
			}
		} elseif ($dragType == 'file' && $dropType == 'file') {  // File dropped next to another file, possibly in another folder
			$sql = sprintf(SQL_GET_FILE_FOLDER, $dropID);
			$folderID = (int)$this->DB->ExecuteScalar($sql);
			$sql = sprintf(SQL_MOVE_FILE, $folderID, $dragID);
			$this->DB->Execute($sql);
		} elseif ($dragType == 'file' && $dropType == 'folderlist') { // File dropped onto project name
			$sql = sprintf(SQL_MOVE_FILE, 0, $dragID);
			$this->DB->Execute($sql);
		} elseif ($dragType == 'folder' && $dropType == 'folder') {  // Folder dropped onto a folder
			if ($pos == 'top' || $pos == 'bottom') {
				$sql = sprintf(SQL_GET_FOLDER_PARENT_ID, $dropID);
				$folderID = (int)$this->DB->ExecuteScalar($sql);
				$sql = sprintf(SQL_MOVE_FOLDER, $folderID, $dragID);
				$this->DB->Execute($sql);
			} elseif ($pos == 'insert') {
				$sql = sprintf(SQL_MOVE_FOLDER, $dropID, $dragID);
				$this->DB->Execute($sql);
			}
		} elseif ($dragType == 'folder' && $dropType == 'file') {  // Folder dropped onto a file
				$sql = sprintf(SQL_GET_FILE_FOLDER, $dropID);
				$folderID = (int)$this->DB->ExecuteScalar($sql);
				$sql = sprintf(SQL_MOVE_FOLDER, $folderID, $dragID);
				$this->DB->Execute($sql);
		} elseif ($dragType == 'folder' && $dropType == 'folderlist') { // File dropped onto project name
			$sql = sprintf(SQL_MOVE_FOLDER, 0, $dragID);
			$this->DB->Execute($sql);
		}

	}

	function AjaxGetFileId(){
		$filename = Request::get("fname");
		$oid = $this->User->ID;
		$pid = Request::get("pid");
		$sql = "SELECT ID FROM tblFiles WHERE FileName like '$filename' AND Owner = $oid AND Project = $pid;";
		$fid = $this->DB->ExecuteScalar($sql);
		echo $sql;
		echo('{fid:'.$fid.'}');
	}

	function ProjectTasks() {
		header('Content-Type: text/html; charset='.CHARSET);
		$projectID = Request::get('projectid', Request::R_INT);

		$options = '<option value="0">'.MSG_NA.'</option>';

		if ($this->User->HasUserItemAccess('projects', $projectID, CU_ACCESS_READ))
		{
			$tasks = $this->DB->Query(sprintf(SQL_GET_PROJECT_TASKS, $projectID));
		}
		else  // User has no access to the project, show only the assigned tasks.
		{
			$taskIDs = $this->GetAssignedTasks($this->User->ID);
			$tasks = $this->DB->Query(sprintf(SQL_GET_PROJECT_TASKS_RESTRICTED, $projectID, implode(',', $taskIDs)));
		}

		foreach ($tasks as $t)
			$options .= "<option value=\"{$t['ID']}\">{$t['Name']}</option>";

		echo $options;
}

	function ViewFileGroups() {
		header('Content-Type: text/html; charset='.CHARSET);

		$hasFileRead = ( $this->User->HasModuleItemAccess( $this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_READ ) );
		if ( $hasFileRead ) 
		{
			$hasFileWrite = $this->User->HasModuleItemAccess( 'files', CU_ACCESS_ALL, CU_ACCESS_WRITE );
			if ( $hasFileWrite ) {
				$modAction[] = '<a href="#" onclick="newFile(0);return false;">' . MSG_NEW_FILE . '</a>';
				$modAction[] = '<a href="#" onclick="newFolder(0);return false;">' . MSG_NEW_FOLDER . '</a>';
			}
			$conds = array();

			// Get tasks the user is allocated to in a csv format.
			$taskIDs = CopperUser::current()->get_assigned_tasks();
			if (count($taskIDs) > 0)
				$conds[] = 'f.TaskID IN ('.implode(',', $taskIDs).')';

			$rows = $this->User->GetUserItemAccess('projects', CU_ACCESS_READ);
			if ($rows == '-1') // User is admin
				$conds[] = "f.ProjectID > 0";
			elseif ($rows == '0') // User has access to projects module, but no specific projects
				$conds[] = "f.ProjectID IN (0)";
			elseif (is_array($rows) && count($rows) == 1 && $rows[0] == 0) // User has no access to projects module
				$conds[] = "f.ProjectID IN (0)";
			else
				$conds[] = "f.ProjectID IN ($rows)";
			
			$rows = $this->User->GetUserItemAccess('clients', CU_ACCESS_READ);
			if ($rows == '-1') // User is admin
				$conds[] = "p.ClientID > 0";
			elseif ($rows == '0') // User has access to clients module, but no specific clients
				$conds[] = "p.ClientID IN (0)";
			elseif (is_array($rows) && count($rows) == 1 && $rows[0] == 0) // User has no access to clients module
				$conds[] = "p.ClientID IN (0)";
			else
				$conds[] = "p.ClientID IN ($rows)";

			$where = implode(') OR (', $conds);

			// Determine display order for folders.
			$dir = (Request::get('direction') == 'down') ? 'DESC' : 'ASC';
			switch (Request::get('order'))
			{
				case 'name': $orderBy = "p.Name $dir"; break;
				default:	 $orderBy = "p.Name ASC"; break;
			}

			// Build General Files row.
			$group = array();
			$group['txtProjectName'] = MSG_GENERAL_FILES;
			$group['txtProjectID'] = 0;
			$group['txtClientName'] = MSG_GENERAL_FILES;
			$group['txtClientID'] = 0;
			$group['txtLastModified'] = '';
			$group['txtSize'] = $this->DB->ExecuteScalar( sprintf( SQL_COUNT_PUBLIC_FILES ) ).' '.MSG_FILES;
			$group['txtType'] = '';
			$group['txtSortOrder'] = Request::get('order');
			$group['txtSortDirection'] = Request::get('direction');
			$tmpl['filegroups'] .= $this->getTemplate('file_group_nolink', $group);

			// Build client files rows.
			$sql = sprintf(SQL_GET_PROJECTS_WITH_FILES, "($where)", $orderBy);
			$clients = $this->DB->Query($sql);
			if (is_array($clients))
			{
				foreach ($clients as $c)
				{
					$group = array();
					$group['txtProjectName'] = $c['ProjectName'];
					$group['txtProjectID'] = $c['ProjectID'];
					$group['txtClientName'] = $c['ClientName'];
					$group['txtClientID'] = $c['ClientID'];
					$group['txtLastModified'] = '';
					$group['txtSize'] = $c['Count'].' '.MSG_FILES;
					$group['txtType'] = MSG_PROJECT;
					$group['txtSortOrder'] = Request::get('order');
					$group['txtSortDirection'] = Request::get('direction');
					$tmpl['filegroups'] .= $this->getTemplate('file_group', $group);
				}
			}

			$tmpl['script'] = '';
			if (Request::get('action') == 'fileview')
			{
				$fileID = Request::get('id', Request::R_INT);
				$file = $this->DB->QuerySingle(sprintf(SQL_FILES_GET_DETAILS_ALL, $fileID));
				if (is_array($file))
				{
					$fileTmpl['txtProjectID'] = (int)$file['ProjectID'];
					$fileTmpl['txtFolderID'] = (int)$file['Folder'];
					$fileTmpl['txtFileID'] = $fileID;
					$fileTmpl['txtSortOrder'] = Request::get('order');
					$fileTmpl['txtSortDirection'] = Request::get('direction');
					$tmpl['script'] = $this->getTemplate('file_view_script', $fileTmpl);
				}
			}

			$tmpl['txtModule'] = "files";
			$this->setTemplate('files', $tmpl);
			$this->addScript('lib/sortable_tree.js');
			$this->setHeader( MSG_FILES, '' );
			$this->setModule( MSG_LIST, $modAction );
			$this->Render();
		} 
		else 
		{
			$this->ThrowError( 2001 );
		}
	}

	function AjaxClientFolders() {
		header('Content-Type: text/html; charset='.CHARSET);
		$clientID = Request::get('clientid', Request::R_INT);
		$folderID = Request::get('folderid', Request::R_INT);

		$hasFileRead = ( $this->User->HasModuleItemAccess( $this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_READ ) );
		if (!$hasFileRead)
		{
			$this->ThrowError(2001);
			return;
		}

		// Determine display order for folders.
		$dir = (Request::get('direction') == 'down') ? 'DESC' : 'ASC';
		switch (Request::get('order'))
		{
			case 'name': $orderBy = "fo.Folder $dir"; break;
			default:	 $orderBy = "fo.Folder ASC"; break;
		}

		if ($clientID > 0)
		{
			$hasClientRead = ( $this->User->HasUserItemAccess( 'clients', $clientID, CU_ACCESS_READ ) );
			if ($hasClientRead || $this->User->IsAdmin)
			{
				$where = '';
				$sql = sprintf(SQL_GET_CLIENT_FOLDERS, $clientID, '', $orderBy);
			}
			else
			{
				$conds = array();

				// Get tasks the user is allocated to in a csv format.
				$taskIDs = $this->GetAssignedTasks($this->User->ID);
				if (count($taskIDs) > 0)
					$conds[] = 'f.TaskID IN ('.implode(',', $taskIDs).')';

				$rows = $this->User->GetUserItemAccess('projects', CU_ACCESS_READ);
				if ($rows == '-1') // User is admin
					$conds[] = "f.ProjectID > 0";
				elseif ($rows == '0') // User has access to projects module, but no specific projects
					$conds[] = "f.ProjectID IN (0)";
				elseif (is_array($rows) && count($rows) == 1 && $rows[0] == 0) // User has no access to projects module
					$conds[] = "f.ProjectID IN (0)";
				else
					$conds[] = "f.ProjectID IN ($rows)";

				$where = 'AND ('.implode(') OR (', $conds).')';
				$sql = sprintf(SQL_GET_CLIENT_FOLDERS, $clientID, $where, $orderBy);
			}
		}
		else  // $clientID = 0 for public files
		{
			$sql = sprintf(SQL_GET_FOLDERS_PUBLIC, $orderBy);
		}

		// Currently there are no multi-level folders so the test for folderID == 0 is a bit pointless.
		if ($folderID == 0)
		{
			$rows = $this->DB->Query($sql);
			if (is_array($rows))
			{
				foreach ($rows as $r)
				{
					$tmpl['txtFolderName'] = $r['Folder'];
					$tmpl['txtFolderID'] = $r['ID'];
					$tmpl['txtClientID'] = $clientID;
					$tmpl['txtSize'] = $r['Count'].' '.MSG_FILES;
					$tmpl['txtSortOrder'] = Request::get('order');
					$tmpl['txtSortDirection'] = Request::get('direction');
					$html .= $this->getTemplate('folder', $tmpl);
				}
			}
		}

		// Determine display order for files.
		$dir = (Request::get('direction') == 'down') ? 'DESC' : 'ASC';
		switch (Request::get('order'))
		{
			case 'name': $orderBy = "f.FileName $dir"; break;
			case 'date': $orderBy = "f.Date $dir, f.FileName ASC"; break;
			case 'size': $orderBy = "f.Size $dir, f.FileName ASC"; break;
			case 'type': $orderBy = "f.Type $dir, f.FileName ASC"; break;
			default:	 $orderBy = "f.FileName ASC"; break;
		}

		if ($clientID > 0)
			$sql = sprintf(SQL_GET_CLIENT_FILES, $clientID, $folderID, $where, $orderBy);
		else
			$sql = sprintf(SQL_GET_FILES_IN_FOLDER_PUBLIC, $folderID, $orderBy);

		$rows = $this->DB->Query($sql);
		if (is_array($rows))
		{
			foreach ($rows as $r)
			{
				$pathinfo = pathinfo($r['FileName']);
				$tmpl['txtFileName'] = $r['FileName'];
				$tmpl['txtFileID'] = $r['ID'];
				$tmpl['txtSize'] = round($r['Size'] / 1024).MSG_KB;
				$tmpl['txtType'] = strtoupper($pathinfo['extension']);
				$tmpl['txtLastModified'] = Format::date($r['Date']); 

				switch ($r['Type'])
				{
					case 'application/vnd.ms-excel': $tmpl['txtIcon'] = 'file_icon_spreadsheet.gif'; break;
					case 'application/msword': $tmpl['txtIcon'] = 'file_icon_word.gif'; break;
					case 'application/pdf': $tmpl['txtIcon'] = 'file_icon_pdf.gif'; break;
					default: $tmpl['txtIcon'] = 'file_icon.gif'; 
				}

				$html .= $this->getTemplate('file', $tmpl);
			}
		}

		echo $html;
	}

	function AjaxProjectFolders() {
		header('Content-Type: text/html; charset='.CHARSET);
		$projectID = Request::get('projectid', Request::R_INT);
		$folderID = Request::get('folderid', Request::R_INT);
				
		$hasReadAccess = $this->User->HasUserItemAccess( $this->ModuleName, $projectID, CU_ACCESS_READ );

		// we can still show general files.
		if ((!$hasReadAccess) && ($projectID != 0)) {
			$this->ThrowError(2001);
			return;
		}

		// Determine display order for folders.
		$dir = (Request::get('direction') == 'down') ? 'DESC' : 'ASC';
		switch (Request::get('order')) {
			case 'name': $orderBy = "fo.Folder $dir"; break;
			default:	 $orderBy = "fo.Folder ASC"; break;
		}

		if ($folderID > 0) {
			$tmpl['actions'] = '';
			$tmpl['txtFolderID'] = $folderID;
			$tmpl['txtProjectID'] = $projectID;
			$hasFileWrite = ( $this->User->HasModuleItemAccess( $this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE ) );
			if ($hasFileWrite)
			{
				$actions[] = array('url' => '#', 'name' => MSG_EDIT, 'attrs' => "onclick=\"editFolder($projectID, $folderID); return false;\"");
				$actions[] = array('url' => url::build_url('files', 'deletefolder', "id=$folderID"), 'name' => MSG_DELETE,
					'confirm' => 1, 'title' => MSG_CONFIRM_FOLDER_DELETE_TITLE, 'body' => MSG_CONFIRM_FOLDER_DELETE_BODY);
				$tmpl['actions'] = $this->ActionMenu($actions);
			}
			$html .= $this->getTemplate('folder_nav', $tmpl);
		} else {
			  $tmpl['txtFolderID'] = $folderID;
			  $tmpl['txtProjectID'] = $projectID;
			$html .= $this->getTemplate('fakefile', $tmpl);
		}

		if ($projectID >= 0) {
			$sql = sprintf(SQL_GET_PROJECT_FOLDERS, $projectID, $folderID, $orderBy);
			$rows = $this->DB->Query($sql);
			if (is_array($rows)) {
				foreach ($rows as $r)
				{
					$sql = sprintf(SQL_COUNT_FILES_IN_FOLDER, $r['ID']);
					$count = $this->DB->ExecuteScalar($sql);

					$tmpl['txtFolderName'] = $r['Folder'];
					$tmpl['txtFolderID'] = $r['ID'];
					$tmpl['txtProjectID'] = $projectID;
					$tmpl['txtSize'] = $count.' '.MSG_FILES;
					$tmpl['txtSortOrder'] = Request::get('order');
					$tmpl['txtSortDirection'] = Request::get('direction');
					$html .= $this->getTemplate('folder_project', $tmpl);
				}
			}
		} else {
			$this->ThrowError(2001);
		}

		// Determine display order for files.
		$dir = (Request::get('direction') == 'down') ? 'DESC' : 'ASC';
		switch (Request::get('order'))
		{
			case 'name': $orderBy = "f.FileName $dir"; break;
			case 'date': $orderBy = "f.Date $dir, f.FileName ASC"; break;
			case 'size': $orderBy = "f.Size $dir, f.FileName ASC"; break;
			case 'type': $orderBy = "f.Type $dir, f.FileName ASC"; break;
			default:	 $orderBy = "f.FileName ASC"; break;
		}

		$sql = sprintf(SQL_GET_PROJECT_FILES, $projectID, $folderID, $where, $orderBy);
		$rows = $this->DB->Query($sql);
		if (is_array($rows))
		{
			foreach ($rows as $r)
			{
				$pathinfo = pathinfo($r['FileName']);
				$tmpl['txtFileName'] = $r['FileName'];
				$tmpl['txtFileID'] = $r['ID'];
				$tmpl['txtSize'] = round($r['Size'] / 1024).MSG_KB;
				$tmpl['txtType'] = strtoupper($pathinfo['extension']);
				$tmpl['txtLastModified'] = Format::date_time($r['Date'], Settings::get('PrettyDateFormat'));

				switch ($r['Type'])
				{
					case 'application/vnd.ms-excel': $tmpl['txtIcon'] = 'file_icon_spreadsheet.gif'; break;
					case 'application/msword': $tmpl['txtIcon'] = 'file_icon_word.gif'; break;
					case 'application/pdf': $tmpl['txtIcon'] = 'file_icon_pdf.gif'; break;
					case 'image/gif':
					case 'image/jpeg':
					case 'image/tiff':
					case 'image/png':
					case 'image/photoshop':
					case 'image/x-photoshop':
					case 'application/photoshop':
					case 'application/psd': $tmpl['txtIcon'] = 'file_icon_image.gif'; break;
					case 'application/octet-stream':
						switch (strtolower($pathinfo['extension'])) 
						{
							case 'xls': $tmpl['txtIcon'] = 'file_icon_spreadsheet.gif'; break;
							case 'doc': $tmpl['txtIcon'] = 'file_icon_word.gif'; break;
							case 'docx': $tmpl['txtIcon'] = 'file_icon_word.gif'; break;
							case 'pdf': $tmpl['txtIcon'] = 'file_icon_pdf.gif'; break;
							case 'gif':
							case 'jpeg':
							case 'jpg':
							case 'tiff':
							case 'png':
							case 'ps': $tmpl['txtIcon'] = 'file_icon_image.gif'; break;
							default: $tmpl['txtIcon'] = 'file_icon.gif';
						}
						break;
					default: $tmpl['txtIcon'] = 'file_icon.gif';
				}

				$html .= $this->getTemplate('file', $tmpl);
			}
		}

		// When there are no folders or files in a project, at this point $html will contain the fakefile template only. 
		// If that happens, don't display it as it will cause a blue line we don't want.
		echo ($html == $this->getTemplate('fakefile')) ? '' : $html;
	}

	function AjaxFileView() {
		header('Content-Type: text/html; charset='.CHARSET);
		$fileID = Request::get('fileid', Request::R_INT);
		$editMode = (Request::get('action') == 'ajaxfileedit') ? TRUE : FALSE;

		$hasFileRead = ( $this->User->HasModuleItemAccess( $this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_READ ) );
		if (!$hasFileRead)
		{
			$this->ThrowError(2001);
			return;
		}

		// TODO: File permissions
		$sql = sprintf(SQL_FILES_GET_DETAILS_ALL, $fileID);
		$file = $this->DB->QuerySingle($sql);
		$this->Log('file', $fileID, 'view', $file['FileName']);

		// The Flash file upload widget adds the protocol itself, so strip it here.
		$tmpl['txtTargetPath'] = str_replace(array('http://', 'https://'), '', url::build_url('files')); 

		if (is_array($file))
		{
			$tmpl['txtFileID'] = $fileID;
			$tmpl['txtDescription'] = (empty($file['Description'])) ? '': $file['Description'];
			if (!$editMode)
				$tmpl['txtDescription'] = '<p>'.$tmpl['txtDescription'].'</p>';
			$tmpl['txtProjectName'] = $file['ProjectName'];
			$tmpl['txtProjectID'] = $file['ProjectID'];
			$tmpl['txtTaskName'] = $file['TaskName'];
			$tmpl['txtTaskID'] = $file['TaskID'];
			$tmpl['txtOwner'] = $file['Owner'];
			$tmpl['txtVersion'] = $file['Version'];
			$tmpl['txtFolder'] = $file['Folder'];
			$tmpl['filedetails'] = '';
			$tmpl['fileversions'] = '';
			$tmpl['fileupload'] = '';
			$tmpl['filecheckout'] = '';
			$tmpl['fileedit'] = '';

			$hasFileWrite = ( $this->User->HasModuleItemAccess( $this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE ) );
			if ($hasFileWrite)
			{
				if ($editMode)
					$actions[] = array('url' => '#', 'name' => MSG_SAVE, 'attrs' => "onclick=\"saveEditFile($fileID); return false;\"");
				else
					$actions[] = array('url' => '#', 'name' => MSG_EDIT, 'attrs' => "onclick=\"editFile($fileID); return false;\"");
			}

			$actions[] = array('url' => url::build_url('files', 'filedown', "fileid=$fileID"), 'name' => MSG_DOWNLOAD);

			// Disabled as checkout feature no longer needed.
			// Can only check out files if they aren't already checked out.
			//if ($file['CheckedOut'] == '0')
			//	$actions[] = array('url' => url::build_url('files', 'filedown', "checkout=1&fileid=$fileID"), 'name' => MSG_CHECKOUT_LATEST);

			if ($hasFileWrite)
				$actions[] = array('url' => url::build_url('files', 'filedel', "fileid=$fileID"), 'name' => MSG_DELETE,
					'confirm' => 1, 'title' => MSG_CONFIRM_FILE_DELETE_TITLE, 'body' => MSG_CONFIRM_FILE_DELETE_BODY);

			// Render details subsection if this is not a public file.
			if ($file['ProjectID'] > 0 && $editMode)
			{
				$tmpl['projectoptions'] = '<option value="0">'.MSG_NA.'</option>';
				$tmpl['taskoptions'] = '<option value="0">'.MSG_NA.'</option>';
				if ($this->User->IsAdmin)
				{
					$projects = $this->DB->Query(sprintf(SQL_GET_PROJECTS_ALL));
					foreach ($projects as $p)
					{
						$selected = ($p['ID'] == $file['ProjectID']) ? 'selected' : '';
						$tmpl['projectoptions'] .= "<option value=\"{$p['ID']}\" $selected>{$p['ClientName']} - {$p['Name']}</option>";
					}
				}
				else
				{
					// Get the projects the user has access to.
					$projectIDs = $this->User->GetUserItemAccess('projects', CU_ACCESS_READ);

					// Get the projects that contain tasks the user is assigned to.
					$assignedTasks = $this->GetAssignedTasks($this->User->ID);
					$sql = sprintf(SQL_GET_PROJECT_IDS_FOR_TASKS, implode(',', $assignedTasks));
					$assignedProjects = $this->DB->Query($sql);
					foreach ($assignedProjects as $p)
						$projectIDsArray[] = $p['ProjectID'];

					// Combine the two lists of projects.
					$projectIDs .= ','.implode(',', $projectIDsArray);

					// Get the project names for the list.
					$projects = $this->DB->Query(sprintf(SQL_GET_PROJECTS, $projectIDs));
					foreach ($projects as $p)
					{
						$selected = ($p['ID'] == $file['ProjectID']) ? 'selected' : '';
						$tmpl['projectoptions'] .= "<option value=\"{$p['ID']}\" $selected>{$p['ClientName']} - {$p['Name']}</option>";
					}
				}

				$tasks = $this->DB->Query(sprintf(SQL_GET_PROJECT_TASKS, $file['ProjectID']));
				foreach ($tasks as $t)
				{
					$selected = ($t['ID'] == $file['TaskID']) ? 'selected' : '';
					$tmpl['taskoptions'] .= "<option value=\"{$t['ID']}\" $selected>{$t['Name']}</option>";
				}
				
				$tmpl['filedetails'] = $this->getTemplate('file_details_edit', $tmpl);
			}
			elseif ($file['ProjectID'] > 0 && !$editMode)
				$tmpl['filedetails'] = $this->getTemplate('file_details', $tmpl);

			// Build path to file
			$trailer = substr(SYS_FILEPATH, strlen(SYS_FILEPATH) - 1, 1);
			$filepath = ( ($trailer != '\\') && ($trailer != '/') ) ? SYS_FILEPATH . '/' : SYS_FILEPATH;
			$projectDir = str_pad($file['ProjectID'], 7, '0', STR_PAD_LEFT);
			$taskDir = str_pad($file['TaskID'], 7, '0', STR_PAD_LEFT);
			$filename  = $filepath . $projectDir . '/' . $taskDir . '/' . $file['RealName'];

			$sql = sprintf(SQL_FILES_GET_ACTIVITY, $fileID);
			$logs = $this->DB->Query($sql);
			if (is_array($logs))
			{
								$newest_file = null;
				foreach ($logs as $log)
				{
					list($date, $time) = explode(' ', $log['Date']);
					$logTmpl['txtFileName'] = (empty($log['FileName'])) ? $file['FileName'].'&#42;' : $log['FileName'];
					$logTmpl['txtSize'] = (empty($log['Size'])) ? Format::file_size($file['Size']) : Format::file_size($log['Size']);
					$logTmpl['txtDate'] = Format::date_time($log['Time'], Settings::get('PrettyDateFormat'));
					$logTmpl['txtVersion'] = $log['Version'];
					$logTmpl['txtActivity'] = $log['Activity'];
					$logTmpl['txtUser'] = $log['User'];
					$logTmpl['txtClass'] = (empty($log['Type'])) ? $this->getIconClassForFile($file) : $this->getIconClassForFile($log);
					$logTmpl['txtDownload'] = '';

					// Show link to this version of the file if it exists
					if (is_dir($filename))
						$versionFilename = $filename . '/' . $file['RealName'] . '_' . round( $log['Version'] );

					if ( is_file( $versionFilename ) && ($log['Activity'] == MSG_CHECKED_IN || $log['Activity'] == MSG_UPLOADED)) 
										{
												if ($newest_file == null) {
													$newest_file = $log;
												}
						$logTmpl['txtDownload'] = '<a href="index.php?module=files&fileid='.$fileID.'&action=filedown&version='.round($log['Version']).'">'.strtoupper(MSG_DOWNLOAD).'</a>';
										}
					$tmpl['fileversions'] .= $this->getTemplate('file_version', $logTmpl);
				}
			}

			$tmpl['actions'] = $this->ActionMenu($actions);
			$tmpl['actionsCancelOnly'] = '';
			$tmpl['UPLOAD_TEXT'] = MSG_FILE_UPLOAD_TEXT;

			$tmpl['preview'] = '';

						$newest_versionFilename = $filename . '/' . $file['RealName'] . '_' . round( $newest_file['Version'] );
			if (((empty($newest_file['Type'])) ? $this->getIconClassForFile($file) : $this->getIconClassForFile($newest_file) == 'filetype-image') 
								&& is_file( $newest_versionFilename )
								) 
			{
				$tmpl['preview'] = '<img class="file-preview" src="index.php?module=files&fileid='.$fileID.'&action=filedown&version='.round($newest_version['Version']).'" alt="File preview" width="350">';
			}

			$tmpl['txtUrl'] = url::build_url();
			$tmpl['txtUserID'] = $this->User->ID;
			$tmpl['txtProjectID'] = $file['ProjectID'];

			$template = ($editMode) ? 'file_edit' : 'file_view';
			$html = $this->getTemplate($template, $tmpl);
			echo $html;
		}
		else
		{
			$newFile = (Request::get('action') == 'ajaxfileedit') ? TRUE : FALSE;
		}
	}

	function AjaxFileEdit() {
		$this->AjaxFileView();
	}

	function AjaxFileNew() {
		// The Flash file upload widget adds the protocol itself, so strip it here.
		$tmpl['txtTargetPath'] = str_replace(array('http://', 'https://'), '', url::build_url('files')); 
		$tmpl['txtFileID'] = 0;
		$tmpl['txtFileState'] = 'new' . ((Request::get('taskid', Request::R_INT) == null) ? 0 : Request::get('taskid', Request::R_INT)) . ':';
		$tmpl['txtDescription'] = '';
		$tmpl['txtProjectName'] = '';
		$tmpl['txtProjectID'] = (Request::get('projectid', Request::R_INT) == null) ? 0 : Request::get('projectid', Request::R_INT);
		$tmpl['txtTaskName'] = '';
		$tmpl['txtTaskID'] = (Request::get('taskid', Request::R_INT) == null) ? 0 : Request::get('taskid', Request::R_INT);
		$tmpl['txtUserID'] = $tmpl['txtOwner'] = $this->User->ID;
		$tmpl['txtVersion'] = '1';
		$tmpl['txtFolder'] = '0';
		$tmpl['filedetails'] = '';
		$tmpl['fileversions'] = '';
		$tmpl['fileupload'] = '';
		$tmpl['filecheckout'] = '';
		$tmpl['fileedit'] = '';
		$tmpl['txtClass'] = 'filetype-misc';
		$tmpl['txtUrl'] = url::build_url();
		$actions[] = array('url' => '#', 'name' => MSG_SAVE, 'attrs' => "onclick=\"saveEditFile(0); return false;\"");
		$actions[] = array('url' => '#', 'name' => MSG_CANCEL, 'attrs' => "onclick=\"cancelNewFile(this); return false;\"");
		$actionsCancelOnly[] = array('url' => '#', 'name' => MSG_CANCEL, 'attrs' => "onclick=\"cancelNewFile(this); return false;\"");
		$tmpl['actions'] = '<span id="actions" style="display:none;">'.$this->ActionMenu($actions).'</span>';
		$tmpl['actionsCancelOnly'] = '<span id="actionsCancelOnly">'.$this->ActionMenu($actionsCancelOnly).'</span>';
		$html = $this->getTemplate('file_edit', $tmpl);
		echo '
		<div class="sortableMeta newFileSortableMeta">
			<div class="cell cellFirst cell-name">
				<span id="filename0" class="newFileInstruct">'.MSG_NEW_FILE_INSTRUCTIONS.'</span>
			</div>
			<div class="cell cell-lastup" id="filetime0"></div>
			<div class="cell cell-size" id="filesize0"></div>
			<div class="cell cellLast cell-type" id="filetype0"></div>
		</div>
		<div id="fileholder-new" style="overflow: visible;" class="expandWrap">'.$html."
			<div class='clear'></div>
		</div>";
	}

	function plural($amount) {
		if ($amount == 1)
			$return = $amount .' '. MSG_FILE;
		else
			$return = $amount .' '. MSG_FILES;
		return $return;
	}

	function selectImage($file_type) {
	  echo $file_type;
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

	function HideFiles() {
		header('Content-Type: text/html; charset='.CHARSET);
		$folderID = Request::get( 'folderid' );
		$filter = Request::get( 'filter' );

		if ( $this->User->HasModuleItemAccess( 'files', CU_ACCESS_ALL, CU_ACCESS_READ ) ) 
		{
			if ( $folderID == 0 ) 
			{
				$folderName  = MSG_UNSORTED;
				$projectID   = NULL;
				$projectName = NULL;
			} 
			else 
			{
				$row = $this->DB->QuerySingle( sprintf( SQL_GET_FOLDER_DETAILS, $folderID ) );
				$folderName  = $row['Folder'];
				$projectID   = $row['ProjectID'];
				$projectName = $row['Name'];
			}

			if ( $filter == 'public' ) 
			{
				$template = 'list_public_item'; 
				$fileCount = $this->DB->ExecuteScalar( sprintf( SQL_COUNT_FILES_IN_FOLDER_PUBLIC, $folderID ) );
			} 
			else 
			{
				$template = 'list_item'; 

				if ( $this->User->HasModuleItemAccess( 'administration', CU_ACCESS_ALL, CU_ACCESS_WRITE ) ) 
				{
					$sql = sprintf( SQL_COUNT_FILES_IN_FOLDER_PROJECT, $folderID );
					$fileCount = $this->DB->ExecuteScalar( $sql );
				}
				else 
				{
					$projectsAccessList = $this->User->GetUserItemAccess( 'projects', CU_ACCESS_READ );
					if ( $projectsAccessList == 0 )
						$projectsAccessList = -1;
					$sql = sprintf( SQL_COUNT_FILES_IN_FOLDER_FOR_PROJECTS, $folderID, $projectsAccessList );
					$fileCount = $this->DB->ExecuteScalar( $sql );

					$tasksAccessList = $this->GetAssignedTasks( $this->User->ID );
					if ( count( $tasksAccessList ) == 0 )
						$tasksAccessList[] = -1;
					$sql = sprintf( SQL_COUNT_FILES_IN_FOLDER_FOR_TASKS, $folderID, implode( ',', $tasksAccessList ) );
					$fileCount += $this->DB->ExecuteScalar( $sql );
				}
			}

			// Display folder name and file count
			$item_tmpl = array();
			$item_tmpl['ID']			 = $folderID;
			$item_tmpl['txtFileFolder']  = $folderName;
			$item_tmpl['txtFileName']	= ( $fileCount == 1 ) ? $fileCount.' '.MSG_FILE : $fileCount.' '.MSG_FILES;
			$item_tmpl['txtProjectID']   = $projectID;
			$item_tmpl['txtProjectName'] = $projectName;
			$item_tmpl['txtTaskID']	  = NULL;
			$item_tmpl['txtTaskName']	= NULL;
			$item_tmpl['txtStatus']	  = NULL;
			$item_tmpl['txtVersion']	 = NULL;
			$item_tmpl['txtLastChange']  = NULL;
			$item_tmpl['txtAction']	  = NULL;
			$item_tmpl['icon']		   = 'folder';

			$content = '<table width="100%" border="0" cellpadding="0" cellspacing="0">';
			$content .= $this->getTemplate( $template, $item_tmpl );
			$content .= '</table>';

			echo $content;
		} 
		else
			$this->ThrowError( 2001 );
	}

	function ShowFiles() {
		header('Content-Type: text/html; charset='.CHARSET);
		$folderID = Request::get( 'folderid' );
		$filter = Request::get( 'filter' );

		if ( $this->User->HasModuleItemAccess( $this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_READ ) ) 
		{
			$folderName = ( $folderID == 0 ) ? MSG_UNSORTED : $this->DB->ExecuteScalar( sprintf( SQL_GET_FOLDER_NAME, $folderID ) );
			$content = $this->getTemplate( 'file_spacer' );

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

			if ( $filter == 'public' ) 
			{
				$template = 'list_public_item'; 
				$sql = sprintf(SQL_GET_FILES_IN_FOLDER_PUBLIC, $folderID, "FileName $dir"); 
			} 
			else 
			{
				$template = 'list_item'; 

				if ( $this->User->HasModuleItemAccess( 'administration', CU_ACCESS_ALL, CU_ACCESS_WRITE ) ) 
					$sql = sprintf( SQL_GET_FILES_IN_FOLDER_PROJECT, $folderID, $orderBy );
				else 
				{
					$projectsAccessList = $this->User->GetUserItemAccess( 'projects', CU_ACCESS_READ );
					if ( $projectsAccessList == 0 )
						$projectsAccessList = -1;
					$tasksAccessList = $this->GetAssignedTasks( $this->User->ID );
					if ( count( $tasksAccessList ) == 0 )
						$tasksAccessList[] = -1;
					$sql = sprintf( SQL_GET_FILES_IN_FOLDER_FOR_PROJECTS_OR_TASKS, $folderID, $projectsAccessList, implode( ',', $tasksAccessList ), $orderBy );	
				}
			}

			$result = $this->DB->Query( $sql );
			if ( !is_array( $result ) ) $result = array();
			foreach ( $result as $index => $row ) 
			{
				if ( !isset( $row['ID'] ) )
					$row['ID'] = $row['FileID'];

				$lastCheckedOut = $this->DB->QuerySingle( sprintf( SQL_LAST_CHECKED_OUT, $row['ID'] ) );

				$tmpl = array();
				$tmpl['txtFileID']	  = $row['ID'];
				$tmpl['txtFileFolder']  = $folderName;
				$tmpl['txtProjectID']   = $row['ProjectID'];
				$tmpl['txtProjectName'] = ( isset( $row['Project'] ) ) ? $row['Project'] : NULL;
				$tmpl['txtTaskID']	  = $row['TaskID'];
				$tmpl['txtTaskName']	= ( isset( $row['Task'] ) ) ? $row['Task'] : NULL;
				$tmpl['txtLastChange']  = Format::date( $row['Date'], Settings::get('PrettyDateFormat') );
				$tmpl['txtVersion']	 = $row['Version'];
				$tmpl['icon']		   = $this->selectImage( $row['Type'] );
				$tmpl['txtRolloverDescription'] = "Uploaded By: ".$row['FirstName']." ".$row['LastName']."\n\n".$row['Description'];
				$tmpl['txtLastChange']  = Format::date( $row['Date'], Settings::get('PrettyDateFormat') );

				if ($row['Linked'] == 1) 
					$tmpl['txtFileName'] = '<a href="'.$row['RealName'].'" target="_new">'.$row['FileName'].'</a>';
				else 
					$tmpl['txtFileName'] = '<a href="index.php?module=files&fileid='.$row['ID'].'&action=filedown" target="_new">'.$row['FileName'].'</a>';

				$tmpl['txtStatus'] = ($row['CheckedOut'] == 1) ? MSG_CHECKED_OUT : MSG_AVAILABLE;
				$tmpl['txtAction'] = '<select name="ProjectID" onchange="if (this.options[this.selectedIndex].value != 0) { openWindow(\''.$row['ID'].'\', this.options[this.selectedIndex].value);};return false;" class="TaskUpdate_dd" id="select" style="width:100%">';
				$tmpl['txtAction'] .= '<option value="0">'.MSG_SELECT.'...</option>';

				if ($row['Linked'] == 1) 
				{
						$tmpl['txtStatusRollover'] = '';
						$tmpl['txtAction'] .= '<option value="filedetails">'.MSG_FILE_DETAILS.'</option>';
						$tmpl['txtAction'] .= '<option value="filedel">'.MSG_DELETE.'</option>';
				} 
				else if ($row['CheckedOut'] == 0) 
				{
						$tmpl['txtStatusRollover'] = '';
						$tmpl['txtAction'] .= '<option value="filedetails">'.MSG_FILE_DETAILS.'</option>';
						$tmpl['txtAction'] .= '<option value="filedown&checkout=1">'.MSG_CHECK_OUT.'</option>';
						$tmpl['txtAction'] .= '<option value="filedel">'.MSG_DELETE.'</option>';
				} 
				else if ( ( $row['CheckedOut'] == 1 ) && ( ( $row['CheckedOutUserID'] == $this->User->ID ) || ( $this->User->HasModuleItemAccess( 'administration', CU_ACCESS_ALL, CU_ACCESS_READ ) ) ) ) 
				{
						$tmpl['txtStatusRollover'] = 'By: '.$lastCheckedOut['Name']. ' at '.$lastCheckedOut['Time'];
						$tmpl['txtAction'] .= '<option value="fileedit">'.MSG_CHECK_IN.'</option>';
				} 
				else
					$tmpl['txtStatusRollover'] = 'By: '.$lastCheckedOut['Name']. ' at '.$lastCheckedOut['Time'];

				$tmpl['txtAction'] .= '</select>';

				// If the user lacks write access, don't show the Actions drop down.
				if ( !$this->User->HasUserItemAccess( 'projects', $row['ProjectID'], CU_ACCESS_WRITE ) ) 
					$tmpl['txtAction'] = '';

				$content .= $this->getTemplate( $template, $tmpl );
				if ( $index != ( count( $result ) - 1 ) )
					$content .= $this->getTemplate( 'file_spacer' );
			}

			$content = '<table border="0" cellpadding="0" cellspacing="0">'.$content.'</table>';
			echo $content;
		} 
		else 
			$this->ThrowError( 2001 );
	}

	function ViewProjectFiles() {
		$modHeader = MSG_ALL_PROJECT_FILES;
		$this->CreateTabs( 'project files' );

		if ( $this->User->HasModuleItemAccess( $this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_READ ) && $this->User->HasModuleItemAccess( 'projects', CU_ACCESS_ALL, CU_ACCESS_READ ) ) {
			$item_tmpl = array();
			$item_tmpl['lblProjectName'] = MSG_PROJECT_NAME;
			$item_tmpl['lblTaskName']	= MSG_TASK_NAME;
			$item_tmpl['lblFileName']	= MSG_FILE_NAME;
			$item_tmpl['lblFolder']	  = MSG_FOLDER;
			$item_tmpl['lblType']		= MSG_TYPE;
			$item_tmpl['lblLastChange']  = MSG_LAST_CHANGE;
			$item_tmpl['lblStatus']	  = MSG_STATUS;
			$item_tmpl['lblAction']	  = MSG_ACTION;
			$item_tmpl['lblAsc']		 = MSG_ASCENDING;
			$item_tmpl['lblDesc']		= MSG_DESCENDING;
			$item_tmpl['order']		  = Request::get('order');
			$item_tmpl['direction']	  = Request::get('direction');
			$this->setTemplate( 'list_header', $item_tmpl );
			unset( $item_tmpl );

			// Determine display order for folders.
			$dir = (Request::get('direction') == 'down') ? 'DESC' : 'ASC';
			switch (Request::get('order'))
			{
				case 'folder':	  $orderBy = "fo.Folder $dir, p.Name ASC"; break;
				case 'projectname': $orderBy = "p.Name $dir, fo.Folder ASC"; break;
				default:			$orderBy = "fo.Folder ASC, p.Name ASC"; break;
			}
			
			if ( $this->User->HasModuleItemAccess( 'administration', CU_ACCESS_ALL, CU_ACCESS_WRITE ) ) {
				$sql = sprintf( SQL_GET_FOLDERS_PROJECT, $orderBy );
				$sqlUnsorted = sprintf( SQL_COUNT_FILES_IN_FOLDER_PROJECT, 0 );
			}
			else {
				$projectsAccessList = $this->User->GetUserItemAccess( 'projects', CU_ACCESS_READ );
				if ( $projectsAccessList == 0 )
					$projectsAccessList = -1;
				$tasksAccessList = $this->GetAssignedTasks( $this->User->ID );
				if ( count( $tasksAccessList ) == 0 )
					$tasksAccessList[] = -1;
				$sql = sprintf( SQL_GET_FOLDERS_FOR_PROJECTS_OR_TASKS, $projectsAccessList, implode( ',', $tasksAccessList ), $orderBy );
				$sqlUnsorted = sprintf( SQL_COUNT_FILES_IN_FOLDER_FOR_PROJECTS_OR_TASKS, 0, $projectsAccessList, implode( ',', $tasksAccessList ) );
			}

			$result = $this->DB->Query( $sql );
						if (is_array($result)) {
						foreach ( $result as $index => $row ) {
								$item_tmpl = array();
								$item_tmpl['ID']			 = $row['ID'];
								$item_tmpl['txtFileFolder']  = $row['Folder'];
								$item_tmpl['txtFileName']	= ( $row['count'] == 1 ) ? $row['count'].' '.MSG_FILE : $row['count'].' '.MSG_FILES;
								$item_tmpl['txtProjectID']   = $row['ProjectID'];
								$item_tmpl['txtProjectName'] = $row['Name'];
								$item_tmpl['txtTaskID']	  = NULL;
								$item_tmpl['txtTaskName']	= NULL;
								$item_tmpl['txtLastChange']  = NULL;
								$item_tmpl['txtAction']	  = NULL;
								$item_tmpl['txtStatus']	  = NULL;
								$item_tmpl['icon']		   = 'folder';
								$item_tmpl['txtStatusRollover'] = NULL;
								$item_tmpl['txtRolloverDescription'] = NULL;

								$tmpl = array();
								$tmpl['ID'] = $row['ID'];
								$tmpl['items'] = $this->getTemplate( 'list_item', $item_tmpl );
								$this->setTemplate( 'file_content', $tmpl );

								// Set a spacer if we aren't on the last folder.
								if ( $index != ( count( $result ) - 1 ) )
										$this->setTemplate( 'spacer' );
						}
				}

			// Display folderless files in projects the user has access to.
				$fileCount = $this->DB->ExecuteScalar( $sqlUnsorted );
				if ( $fileCount > 0 ) {
					if ($row['count'] > 0)
								$this->setTemplate( 'spacer' );

							$item_tmpl = array();
							$item_tmpl['ID']			 = 0;
							$item_tmpl['txtFileFolder']  = MSG_UNSORTED;
							$item_tmpl['txtFileName']	= ($fileCount == 1) ? $fileCount.' '.MSG_FILE : $fileCount.' '.MSG_FILES;
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
							$tmpl['items'] = $this->getTemplate( 'list_item', $item_tmpl );
							$this->setTemplate( 'file_content', $tmpl );
					}

					if ( ( count( $result ) + $fileCount ) == 0 ) {
							$tmpl['message'] = MSG_NO_FILES_AVAILABLE;
							$this->setTemplate( 'no_files', $tmpl );
					}

					$this->setTemplate( 'list_footer' );

					$header = '';
					$this->setHeader( MSG_FILES, $header );
					$this->setModule( $modHeader, $modAction );
					$this->Render();
		} else {
					$this->ThrowError( 2001 );
		}
	}

	function ViewPublicFiles() {
		if ( $this->User->HasModuleItemAccess( $this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_READ ) ) {
			$modHeader = MSG_GENERAL_FILES;
			$modAction[0] = ( $this->User->HasModuleItemAccess( 'files', CU_ACCESS_ALL, CU_ACCESS_WRITE ) ) ? '<a href="index.php?module=files&amp;action=filenew">' . MSG_NEW_FILE . '</a>' : '';
			$this->CreateTabs( 'general files' );

			$item_tmpl = array();
			$item_tmpl['lblFileName']   = MSG_FILE_NAME;
			$item_tmpl['lblFolder']	 = MSG_FOLDER;
			$item_tmpl['lblType']	   = MSG_TYPE;
			$item_tmpl['lblLastChange'] = MSG_LAST_CHANGE;
			$item_tmpl['lblStatus']	 = MSG_STATUS;
			$item_tmpl['lblVersion']	= MSG_VERSION;
			$item_tmpl['lblAction']	 = MSG_ACTION;
			$item_tmpl['lblAsc']		= MSG_ASCENDING;
			$item_tmpl['lblDesc']	   = MSG_DESCENDING;
			$item_tmpl['order']		 = Request::get('order');
			$item_tmpl['direction']	 = Request::get('direction');
			$this->setTemplate( 'list_public_header', $item_tmpl );
			unset( $item_tmpl );

			// Determine display order for folders.
			$dir = (Request::get('direction') == 'down') ? 'DESC' : 'ASC';
			$orderBy = "fo.Folder $dir";

			$sql = sprintf( SQL_GET_FOLDERS_PUBLIC, $orderBy );
			$result = $this->DB->Query( $sql );
			foreach ( $result as $index => $row ) {
				$item_tmpl = array();
				$item_tmpl['ID']			= $row['ID'];
				$item_tmpl['txtFileFolder'] = $row['Folder'];
				$item_tmpl['txtFileName']   = ($row['count'] == 1) ? $row['count'].' '.MSG_FILE : $row['count'].' '.MSG_FILES;
				$item_tmpl['txtStatus']	 = NULL;
				$item_tmpl['txtLastChange'] = NULL;
				$item_tmpl['txtVersion']	= NULL;
				$item_tmpl['txtAction']	 = ($row['count'] == 0) ? 'Delete': NULL;
				$item_tmpl['icon']		  = 'folder';
				$item_tmpl['txtStatusRollover'] = NULL;
				$item_tmpl['txtRolloverDescription'] = NULL;

				$tmpl = array();
				$tmpl['ID']	= $row['ID'];
				$tmpl['items'] = $this->getTemplate( 'list_public_item', $item_tmpl );
				$this->setTemplate( 'file_content', $tmpl );

				if ( $index != ( count( $result ) - 1 ) )
					$this->setTemplate('spacer');
			}

			// Display count of folderless files
			$fileCount = $this->DB->ExecuteScalar( sprintf( SQL_COUNT_FILES_IN_FOLDER_PUBLIC, 0 ) );
			if ( $fileCount > 0 ) {
				// Only show spacer if we have folders above.
				if ( $result )
					$this->setTemplate('spacer');

				$item_tmpl = array();
				$item_tmpl['ID']			= 0;
				$item_tmpl['txtFileFolder'] = MSG_UNSORTED;
				$item_tmpl['txtFileName']   = ($fileCount == 1) ? $fileCount.' '.MSG_FILE : $fileCount.' '.MSG_FILES;
				$item_tmpl['txtStatus']	 = NULL;
				$item_tmpl['txtLastChange'] = NULL;
				$item_tmpl['txtVersion']	= NULL;
				$item_tmpl['txtAction']	 = NULL;
				$item_tmpl['icon']		  = 'folder';

				$tmpl = array();
				$tmpl['ID']	= 0;
				$tmpl['items'] = $this->getTemplate('list_public_item', $item_tmpl);
				$this->setTemplate('file_content', $tmpl);
			}

			if ( ( count( $result ) + $fileCount ) == 0 ) {
				$tmpl['message'] = MSG_NO_FILES_AVAILABLE;
				$this->setTemplate( 'no_files', $tmpl );
			}

			$this->setTemplate( 'list_public_footer' );

			$header = '';
			$this->setHeader( MSG_FILES, $header );
			$this->setModule( $modHeader, $modAction );
			$this->Render();
		}
		else {
			$this->ThrowError( 2001 );
		}
	}

	function FileNew() {
		if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE)) {
			$this->FileDisplayForm();
		}
		else {
			$this->ThrowError(2001);
		}
	}

	function FileEdit() {
		$fileid = Request::get('fileid', Request::R_INT);
		$SQL = sprintf(SQL_FILES_GET_DETAILS_ALL, $fileid);
		$file = $this->DB->QuerySingle($SQL);

		if (($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE)) && ($file['CheckedOut'] == 1) && (($file['CheckedOutUserID'] == $this->User->ID) || ($this->User->HasModuleItemAccess('administration', CU_ACCESS_ALL, CU_ACCESS_READ)))){
			$this->FileDisplayForm($fileid, 1);
		}
		else {
			$this->ThrowError(2001);
		}
	}

	function FileDetails() {
		$fileid = Request::get('fileid', Request::R_INT);
		$SQL = sprintf(SQL_FILES_GET_DETAILS_ALL, $fileid);
		$file = $this->DB->QuerySingle($SQL);

			if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE))
			{
				$this->FileDisplayForm($fileid, 0);
			}
			else
			{
				$this->ThrowError(2001);
			}
	}

	function FileDisplayForm($fileid = 0, $numUploads = 5) {
		$tmpl['numUploads']			= $numUploads;
		$tmpl['txtFileID']		  = $fileid;
		$tmpl['txtOwner']		   = $this->User->ID;
		$tmpl['lblFileDescription'] = MSG_FILE_DESCRIPTION;
		$tmpl['lblFileName']		= MSG_FILE_NAME;
		$tmpl['lblFileDetails']	 = MSG_FILE_DETAILS;
		$tmpl['lblUploaded']		= MSG_UPLOADED_BY;
		$tmpl['lblFileType']		= MSG_FILE_TYPE;
		$tmpl['lblFileSize']		= MSG_FILE_SIZE;
		$tmpl['lblFileVersion']	 = MSG_FILE_VERSION;
//change_log 2.
		$tmpl['lblLinkedFile']	  = MSG_LINKED_FILE;

			$modHeader = MSG_GENERAL_FILES. ' '. MSG_EDIT;
			$modAction[] = '<a href="#" onclick="$(\'uploadfile\').submit();return false;">'.MSG_SAVE.'</a>';
		if ($fileid == 0) {
			$modTitle = MSG_FILES;
			$tmpl['lblAction'] = MSG_NEW_FILE;
			$tmpl['txtFileName'] = '';
			$tmpl['txtFolder'] = '';
			$tmpl['txtDescription'] = '';
			$tmpl['txtOwnerName'] = '';
			$tmpl['txtFileType'] = '';
			$tmpl['txtFileSize'] = '';
			$tmpl['txtVersion'] = '1.0';
			$tmpl['txtDelete'] = '';
		}
		else {
			$modTitle = MSG_FILES;
			$SQL = sprintf(SQL_FILES_GET_DETAILS_ALL, $fileid);
			$file = $this->DB->QuerySingle($SQL);
			$tmpl['lblAction'] = MSG_UPDATE;
			$tmpl['txtFileName'] = $file['FileName'];
			$folder				 = $file['Folder'];
			$tmpl['txtDescription'] = $file['Description'];
			$tmpl['txtOwnerName'] = $file['FirstName'] . ' ' . $file['LastName'];
			$tmpl['txtFileType'] = substr($file['Type'], strpos($file['Type'],'/') + 1);
			$tmpl['txtFileSize'] = round($file['Size'] / 1024, 2);
			$tmpl['txtVersion'] = $file['Version'];
			$tmpl['txtDelete'] = '';
			$tmpl['txtColour'] = ( $file['Colour'] == ''  ) ? '#00CCFF' : $file['Colour'];
//change_log 2.
			if ($file['Linked'] == 1)
				$linkedfile = $file['RealName'];
		}

		$tmpl['txtFileDetails'] = 'filedetails';
		$tmpl['txtBrowse'] = '';
		if ( $numUploads > 0 )
		{
			$tmpl['txtFileDetails'] = '';
			$tmpl['txtBrowse'] = $this->getTemplate( 'browse_header' );
			for ( $i = 0; $i < $numUploads; $i++ )
			{
				$tmpl['thisBrowse'] = $i+1;
				$tmpl['nextBrowse'] = $i+2;
				$tmpl['display'] = ( $i == 0 ) ? '' : 'display:none;';
				$tmpl['i'] = $i;
				$tmpl['txtBrowse'] .= $this->getTemplate( 'browse_item', $tmpl ); 
			}
		}
	
		$tmpl['txtLinkedFile'] = '';
		if ($file['Linked'] == 1) {
			$tmpl['txtLinkedFile'] = '<tr align="left" valign="top">
							  <td colspan="2"><strong>'.MSG_LINKED_FILE.'</strong></td>
							</tr>
							<tr align="left" valign="top">
							  <td colspan="2"><input type="text" name="linkedfile" class="TaskEdit_tf" style="width:100%" value="'.$linkedfile.'"></textarea></td>
							</tr>';
		}
		$sql = sprintf(SQL_GET_FOLDERS_PUBLIC, 'fo.Folder ASC');
		$tmpl['selectFolder'] = $this->MakeSelectBoxWithSQL($sql, 'ID', $folder, 'Folder');
//change_log 1.
		$tmpl['max_size'] = Format::convert_to_bytes(ini_get('post_max_size'));

		$tmpl['ActivityLog'] = NULL;
		if ($fileid != 0) {
			// Build path to file
			$trailer = substr(SYS_FILEPATH, strlen(SYS_FILEPATH) - 1, 1);
			$filepath = ( ($trailer != '\\') && ($trailer != '/') ) ? SYS_FILEPATH . '/' : SYS_FILEPATH;
			$projectDir = str_pad($file['ProjectID'], 7, '0', STR_PAD_LEFT);
			$taskDir = str_pad($file['TaskID'], 7, '0', STR_PAD_LEFT);
			$filename  = $filepath . $projectDir . '/' . $taskDir . '/' . $file['RealName'] . '/' . $file['RealName'] . '_';

			$SQL = sprintf(SQL_FILES_GET_ACTIVITY, $fileid);
			$logs = $this->DB->Query($SQL);
			if (is_array($logs)) {
				$tmpl['ActivityLog'] .= '<b>'.MSG_ACTIVITY.'</b></td></tr><tr align="left" valign="top" bgcolor="#CCCCCC"><td height="15" colspan="2"><img src="images/common/keyline_mask.gif" width="100%" height="15"></td></tr><tr><td colspan="2">';
				foreach($logs as $key => $log) {
					$log_tmpl['Activity'] = $log['Activity'];
					$log_tmpl['Date'] = $log['Date'];
					$log_tmpl['User'] = $log['User'];
					$log_tmpl['Download'] = '';
					$log_tmpl['txtColour'] = $tmpl['txtColour'];
					
					// Show link to this version of the file if it exists
					$versionFilename = $filename . round( $log['Version'] );
					if ( is_file( $versionFilename ) && ($log['Activity'] == MSG_CHECKED_IN || $log['Activity'] == MSG_UPLOADED))
						$log_tmpl['Download'] = '<a href="index.php?module=files&fileid='.$fileid.'&action=filedown&version='.round($log['Version']).'">'.MSG_DOWNLOAD.'&nbsp;v'.$log['Version'].'<a>';
					$tmpl['ActivityLog'] .= $this->getTemplate('activity_log',$log_tmpl);
				}
			}
		}
		$this->setTemplate('files_form', $tmpl);
		$this->setHeader($modTitle);
		$this->setModule($modHeader, $modAction);
		$this->Render();
	}

	function swfFileSave() 
	{
		// hackalert. because this thing doesn't do auth properly, we have to double check the auth here agin. Otherwise we might have files going up anytime.
		// bah.
		// okay we are going to re-get the session data from the db, because the file uploader sends the cookie now, but it sends in the post data. and
		// the copper session class is too dumb.
		$cookie = preg_match('/SESSIONID=([^;]{32})/', Request::post('post_cookie'), $matches);
		$session_id = $matches[1];

		$SQL			= sprintf(CU_SQL_SESSIONS_DATA, $session_id);
		$result	 	= $this->DB->QuerySingle($SQL);
		$session_data = @unserialize($result[0]);

		if ((($session_data['authorised'] == 1) && ($session_data['userid'] > 0)))
		{
			set_time_limit(600);
			ignore_user_abort(1);

			$file = Request::files("Filedata", Request::R_ARRAY);
			if (($file['error'] == UPLOAD_ERR_OK)) 
			{
				$projectID = Request::post("projectid");
				$taskID = Request::post("taskid");
				$fileID = Request::post("fileid");
				$owner = $session_data['userid']; 
				$version = $this->DB->Prepare(Request::post('version'));
				if (empty($version)) {
					$version = 1.0;
				}

				$file_tmp	= $file['tmp_name'];
				$file_name = $file['name'];
				$file_type = $file['type'];
				$file_size = $file['size'];
				
				if ($file_size > 0) 
				{
					$fileArray = ( $fileID > 0 ) ? $this->DB->QuerySingle( sprintf( SQL_GET_FILE_DETAILS, $fileID ) ) : NULL;
					$project_dir = str_pad($projectID, 7, '0', STR_PAD_LEFT);
					$task_dir = str_pad($taskID, 7, '0', STR_PAD_LEFT);
					$trailer = substr(SYS_FILEPATH, strlen(SYS_FILEPATH) - 1, 1);
					$filepath = ( ($trailer != '\\') && ($trailer != '/') ) ? SYS_FILEPATH . '/' : SYS_FILEPATH;

					$version = ($fileArray == null) ? 1.0 : $fileArray['Version'];

					$project_path = $filepath . $project_dir;
					$task_path = $filepath . $project_dir . '/'. $task_dir;
					@mkdir($project_path, 0777);
					@mkdir($task_path, 0777);

					$new_file_name = ( $fileID == 0 ) ? $this->MD5($projectID.$taskID.$file_name.microtime(true)) : $fileArray['RealName'];
					$new_file_path = $task_path . '/' . $new_file_name;

					// File versioning has introduced a different file naming convention.
					// Instead of <project id>/<task id>/<md5 hash> we will have
					// <project id>/<task id>/md5 hash/<md5 hash>_<version>
					// ie. the hash becomes the folder, and version number is a suffix of the on-disk filename.
					if ( defined( 'FILE_VERSIONING_ENABLED' ) && FILE_VERSIONING_ENABLED == '1' )
					{
						if ( !is_dir( $new_file_path ) )
								mkdir( $new_file_path, 0777 );
						$versionSuffix = ( $fileID == 0 ) ? round( $version ) : round( $version + 1 );
						$new_file_path .= '/' . $new_file_name . '_' . $versionSuffix;
					}

					if ( move_uploaded_file($file_tmp, $new_file_path) ) 
					{
						$file = new File(($fileID == 0) ? null : $fileID);
						$file->FileName = $file_name;
						$file->Description = $description;
						$file->Type = $file_type;
						$file->Owner = $owner;
						$file->Date = DB::now();
						$file->Size = $file_size;
						$file->RealName = $new_file_name;
						$file->Folder = $folder;
						$file->ProjectID = $projectID;
						$file->TaskID = $taskID;

						if ($file->exists) {
							$file->Version = number_format($version + 1, 1);
						} else {
							$file->Version = $version;
						}
						
						$file->commit();

						$return_data = array(
							'done' => 1,
							'fileid' => $file->ID,
							'now' => Format::date_time(date('Y-m-d H:i:s'), Settings::get('PrettyDateFormat')),
							'version' => $file->Version,
						);

						echo json_encode($return_data);
						
						// okay now we have some sense, we can email
						$mailer = new SMTPMail();
						$mailer->FromName = SYS_FROMNAME;
						$mailer->FromAddress = SYS_FROMADDR;
						$mailer->Subject = MSG_NEW_FILE . " - " . $file->project->Name . ": " . $file->task->Name;

						// mail all the resources
						foreach($file->task->assigned_resources as $user)
						{
							$mailer->ToName = $user->FirstName;
							$mailer->Body = sprintf(MSG_NEW_FILE_EMAIL_BODY, $user->FirstName, $file->FileName, $file->task->permalink);
							$mailer->ToAddress = $user->EmailAddress;
							$mailer->Execute();
						}

						unset($mailer);
						
					} else 
					{
						$no_redirect = 1;
						$message = "Post Max Size: ".ini_get('post_max_size');
						$message .= ' - Upload Max Filesize: '.ini_get('upload_max_filesize');
					}
				}
			} else {
				echo "{done:1}";
			}
		}
	}

	function FileDelete() {
		$fileid = Request::any('fileid');
		$tmpl['MESSAGE'] = MSG_USER_NOT_FOUND;
		$template = 'message';
		$title = MSG_FILES;
		$breadcrumbs = MSG_DELETE;

		$this->setHeader($title);
		$this->setModule($breadcrumbs);
		if (is_numeric($fileid)) {
			if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE)) {
				$confirm = Request::get('confirm');

				if ($confirm == 1) {
					// Ignore any error messages. Just do it. 
					$file = $this->DB->QuerySingle(sprintf(SQL_GET_FILE_DETAILS, $fileid));
					cuDeleteFile($file);
					$this->DB->Execute(sprintf(SQL_FILE_DELETE, $fileid));
					$this->DB->Execute(sprintf(SQL_FILE_DELETE_HISTORY, $fileid));
					$url = (!empty($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : 'index.php?module=files';
					Response::redirect($url);
				}
				else {
					$SQL = sprintf(SQL_GET_FILE_NAME, $fileid);
					$rs  = $this->DB->QuerySingle($SQL);
					if (is_array($rs)) {
						$tmpl['fileid'] = $fileid;
						$tmpl['MESSAGE'] = sprintf(MSG_DELETE_FILE_WARNING, $rs['FileName']);
						$tmpl['YES'] = MSG_YES;
						$tmpl['NO'] = MSG_NO;
						$template = 'delete_file';
					}
				}
			}
			else {
				$this->ThrowError(2001);
			}
		}

		$this->setTemplate($template, $tmpl);
		$this->Render();
	}

	function FileDownload() {
		$fileid	= Request::get('fileid', Request::R_INT);
		$version = Request::get( 'version' );
		if ( $this->User->HasModuleItemAccess('files', CU_ACCESS_ALL, CU_ACCESS_READ) ) 
		{
			$file = new File($fileid, $version);
			if ($file->download()) 
			{
				if (($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_WRITE)) && (Request::get('checkout') == 1)) 
				{
					$SQL = sprintf(SQL_FILE_CHECKOUT, $fileid, $this->User->ID);
					$this->DB->Execute($SQL);

					$SQL = sprintf(SQL_FILE_LOG, 
													$fileid, 
													$this->User->ID, 
													date('Y-m-d H:i:s'), 
													MSG_CHECKED_OUT, 
													$file->Version, 
													$file->FileName, 
													$file->Type, 
													$file->Size, 
													$file->RealName
												);
					$this->DB->Execute($SQL);

				}
				else {
					$SQL = sprintf(SQL_FILE_LOG, 
													$fileid, 
													$this->User->ID, 
													date('Y-m-d H:i:s'), 
													MSG_VIEWED, 
													$version, 
													$file->FileName, 
													$file->Type, 
													$file->Size, 
													$file->RealName
												);
					$this->DB->Execute($SQL);
				}

				return;
			}
		  else {
				$this->ThrowError(4001);
			}
		}
		else {
			$action = Request::get( 'action' );
			Response::redirect('index.php?module=files&action='.$action);
		}
	}

	function CreateTabs($active) {
		$tmpl['lblProjectTab'] = $this->AddTab(MSG_PROJECT_FILES, '', $active);
		if ($this->User->HasModuleItemAccess($this->ModuleName, CU_ACCESS_ALL, CU_ACCESS_READ)) {
			$tmpl['lblPublicTab'] = $this->AddTab(MSG_GENERAL_FILES, 'public', $active);
		}
		$this->setTemplate('tabs', $tmpl);
		unset($tmpl);
	}

	function AddTab($name, $action, $active) {
		if ( $active == strtolower($name) ) {
			$tab = 'tab_active';
		}
		else {
			$tab = 'tab_inactive';
		}
		if (strlen($action) > 0) $query = '&amp;action='.$action;
		return $this->getTemplate($tab, array('lblTabName' => $name, 'lblTabQuery' => $query));
}

	 function MakeSelectBoxWithSQL($sql, $param1, $param2, $param3) {
		$result = $this->DB->Query($sql);
		if ($result) {
			foreach ($result as $key => $value) {
				$return .= sprintf('<option value="%s" %s>%s</option>', $value[$param1], ($value[$param1] == $param2) ? 'selected' : '' ,$value[$param3]);
			}
		}
		return $return;
	}

	function GetAssignedTasks( $userID ) {
			$taskIDs = array();

		$rows = $this->DB->Query( sprintf( SQL_SELECT_TASKS_FOR_USER, $userID ) ); 
		if ( is_array( $rows ) )
		{
			foreach ( $rows as $row )
				$taskIDs[] = $row['ID'];
		}

		return $taskIDs;
		}

	function ActionMenu($actions) {
		$actionMenu = '';
		if (is_array($actions)) 
		{
			foreach ($actions as $action)
			{
				if ($action['confirm'] == 1) 
				{
					$action['attrs'] = 'class="lbOn" rel="confirmLightBox"';
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

	function getIconClassForFile($file) {
		switch ($file['Type']) 
		{
			case "application/msword": $className = 'filetype-word'; break;
			case "application/vnd.ms-excel" : $className = 'filetype-spreadsheet'; break;
			case "application/pdf": $className = 'filetype-pdf'; break;
			case 'image/gif':
			case 'image/jpeg':
			case 'image/tiff':
			case 'image/png':
			case 'image/photoshop':
			case 'image/x-photoshop':
			case 'application/photoshop':
			case 'application/psd': $className = 'filetype-image'; break;
			case 'application/octet-stream':
				$pathinfo = pathinfo($file['FileName']);
				switch (strtolower($pathinfo['extension'])) 
				{
					case 'xls': $className = 'filetype-spreadsheet'; break;
					case 'doc': $className = 'filetype-word'; break;
					case 'docx': $className = 'filetype-word'; break;
					case 'pdf': $className = 'filetype-pdf'; break;
					case 'gif': 
					case 'jpeg': 
					case 'jpg': 
					case 'png': 
					case 'tiff': 
					case 'ps': $className = 'filetype-image'; break;
					default: $className = 'filetype-misc';
				}
				break;
			default: $className = 'filetype-misc';
		}
		return $className;
	}
}
 
