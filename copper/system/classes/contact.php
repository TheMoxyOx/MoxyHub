<?php
/**
 * contact class
 * $Id $
 */

class Contact extends Item
{
	protected $tableName = 'tblContacts';
	
	protected $defaultFields = array(
		'ID',
		'ClientID',
		'KeyContact',
		'FirstName',
		'LastName',
		'Notes',
		'Title',
		'EmailAddress1',
		'EmailAddress2',
		'Phone1',
		'Phone2',
		'Phone3',
		'OrderBy',
	);

	protected $default_data = array(
	);
	
	
	public function __get($var)
	{
		if ($var == 'full_name')
		{
			return $this->FirstName . ' ' . $this->LastName;
		} else if ($var == 'url') {
			if ($this->exists) {
				return "<a href='index.php?module=contacts&action=view&id=" . $this->ID . "'>" . $this->full_name . "</a>";
			} else {
				return null;
			}
		} else {
			return parent::__get($var);
		}
	}
	
}

