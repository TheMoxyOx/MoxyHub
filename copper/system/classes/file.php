<?php
/**
 * Setting class
 * $Id$
 */

/* this class is the direct association with the db object */

class File extends Item
{
	// this value is the pad length of the folders that the files live in, ie, the length to which the project id and task id are padded to.
	const DIRNAME_PAD_LENGTH = 7;
	const DIRNAME_PAD_CHAR = '0';

	protected $tableName = 'tblFiles';
	
	protected $defaultFields = array(
		'ID',
		'ProjectID',
		'TaskID',
		'FileName',
		'Description',
		'Type',
		'Owner',
		'Date',
		'Size',
		'Version',
		'RealName',
		'CheckedOut',
		'CheckedOutUserID',
		'Folder',
		'Linked',
	);
	
	protected $default_data = array(
	);
	
	private $project_item = null;
	private $task_item = null;
	private $user_item = null;
	
	public function __construct($params, $version = null)
	{
		$this->requested_version = $version;
		parent::__construct($params);
	}
	
	public function __get($var)
	{
		if ($var == 'filepath')
		{
			// sanitaition first. 
			// trailing slash - checks for trailing slash, just incase someone didn't put it in
			$trailer = substr(SYS_FILEPATH, strlen(SYS_FILEPATH) - 1, 1);
			return ( ($trailer != '\\') && ($trailer != '/') ) ? SYS_FILEPATH . '/' : SYS_FILEPATH;
		} else if ($var == 'task')
		{
			if ($this->task_item == null)
			{
				$this->task_item = new Task($this->TaskID);
			}
			return $this->task_item;

		} else if ($var == 'project')
		{
			if ($this->project_item == null)
			{
				$this->project_item = new Project($this->ProjectID);
			}
			return $this->project_item;
		} else if ($var == 'user')
		{
			if ($this->user_item == null)
			{
				$this->user_item = new CopperUser($this->Owner);
			}
			return $this->user_item;
		} else {
			return parent::__get($var);
		}
		
	}
	
	public function get_pathinfo($field = null)
	{
		$pathinfo = pathinfo($this->FileName);
		
		if ($field == null)
		{
			return $pathinfo;
		} else if (array_key_exists($field, $pathinfo))
		{
			return $pathinfo[$field];
		} else {
			return null;
		}
		
	}
	
	/**
	 * Get the real filename for the file. note that this takes into account file versioning
	 * and according to where it _should_ exist according to the db.
	 */
	private function get_filepath()
	{
		$project_dir = str_pad($this->ProjectID, self::DIRNAME_PAD_LENGTH, self::DIRNAME_PAD_CHAR, STR_PAD_LEFT);
		$task_dir = str_pad($this->TaskID, self::DIRNAME_PAD_LENGTH, self::DIRNAME_PAD_CHAR, STR_PAD_LEFT);
		
		$filename  = $this->filepath . $project_dir . '/' . $task_dir . '/' . $this->RealName;

		// File will actually be a dir if file versioning is enabled, or has been in the past.
		// The check for FILE_VERSIONING_ENABLED is omitted here as a "just in case" measure, 
		// since we should just download the file regardless of how it's stored.
		if ( $this->is_versioned() ) {

			if ( !is_numeric( $this->requested_version ) || $this->requested_version < 1 ) {
				$version = $this->Version;
			} else {
				$version = $this->requested_version;
			}
			
	    $filename .= '/' . $this->RealName . '_' . round( $version );
		}
		
		if (file_exists($filename))
		{
			return $filename;
		} else {
			return null;
		}
	}
	
	private function is_versioned()
	{
		$project_dir = str_pad($this->ProjectID, self::DIRNAME_PAD_LENGTH, self::DIRNAME_PAD_CHAR, STR_PAD_LEFT);
		$task_dir = str_pad($this->TaskID, self::DIRNAME_PAD_LENGTH, self::DIRNAME_PAD_CHAR, STR_PAD_LEFT);
		$filename  = $this->filepath . $project_dir . '/' . $task_dir . '/' . $this->RealName;

		// if it's versioned, the base filename will actually be a directory
		return is_dir($filename);
	}
	
	
	public function move_to_task($project_id, $task_id)
	{
		$filepath = $this->get_filepath();
		if ($filepath != null) 
		{
			$project_dir = str_pad($project_id, self::DIRNAME_PAD_LENGTH, self::DIRNAME_PAD_CHAR, STR_PAD_LEFT);
			$task_dir = str_pad($task_id, self::DIRNAME_PAD_LENGTH, self::DIRNAME_PAD_CHAR, STR_PAD_LEFT);

			@mkdir($this->filepath . $project_dir, 0777);
			@mkdir($this->filepath . $project_dir . '/'. $task_dir, 0777);

			$new_filename = $this->filepath . $project_dir . '/' . $task_dir . '/' . $this->RealName;

			if ( $this->is_versioned() ) 
			{
				if ( !is_numeric( $this->requested_version ) || $this->requested_version < 1 ) {
					$version = $this->Version;
				} else {
					$version = $this->requested_version;
				}

				// have to make the versioned directory
				@mkdir($new_filename, 0777);
				$new_filename .= '/' . $this->RealName . '_' . round( $version );
			}

			if ( rename($filepath, $new_filename) ) {
				$this->ProjectID = $project_dir;
				$this->TaskID = $task_id;
				$this->commit();

				return TRUE;
			} else {
				return FALSE;
			}
		}
		
		return FALSE;
	}
	
	public function commit()
	{
		// when commiting, we should also append to the file log.
		$existing_file = $this->exists;
		
		if ( ! parent::commit() )
		{
			return FALSE;
		}
		
		$fl = new FileLog(null);
		$fl->FileID = $this->ID;
		$fl->UserID = $this->Owner;
		$fl->Time = DB::now();
		$fl->Activity = $existing_file ? MSG_CHECKED_IN : MSG_UPLOADED;
		$fl->Version = $this->Version;
		$fl->FileName = $this->FileName;
		$fl->Type = $this->Type;
		$fl->Size = $this->Size;
		$fl->RealName = $this->RealName;
		
		$fl->commit();
	}
	
	/**
	 * download a file, according to some version (if we are using file versioning)
	 * Was cuDownloadFile
	 */
	public function download()
	{
		$filepath = $this->get_filepath();
		if ($filepath != null) 
		{
			// If output buffering is on, readfile() reads the whole file into the output buffer.
			// If the file size is greater than the PHP memory_limit setting, PHP will die with an error.
			// Solution: turn off output buffering before the call to readfile().
			ob_end_clean();

			// send some nice headers.
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="'.$this->FileName.'"');
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Length: ' . filesize($filepath));
			readfile($filepath);
			return true;
		} else {
		  return false;
		}
	}
	
}
