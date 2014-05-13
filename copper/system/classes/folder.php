<?php
/**
 * Setting class
 * $Id$
 */

class Folder extends Item
{
	protected $tableName = 'tblFolders';
	
	protected $defaultFields = array(
		'ID',
		'ProjectID',
		'Folder',
		'ParentID',
	);
	
	protected $default_data = array(
	);
	
	private $files_items = null;
	
	public function __get($var)
	{
		if ($var == 'has_files')
		{
			return (count($this->get_files()) > 0);
		} else {
			return parent::__get($var);
		}
	}
	
	public function get_files()
	{
		if ($this->files_items == null)
		{
			$this->files_items = new Files(array('where' => array('Folder' => $this->ID)));
		}
		
		return $this->files_items;
	}
}

