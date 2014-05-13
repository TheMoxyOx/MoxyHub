<?php
/**
 * Base Database item
 * $Id$
 */

class InvoiceItemOther extends Item
{
	protected $tableName 	= 'tblInvoices_Items_Other';
	
	protected $defaultFields = array(
		'ID',
		'ProjectID',
		'TaskName',
		'Amount', // this is the same as billed i think
		'Budget',
		'Quantity',
		'Cost',
		'Charge',
		'Logged',
	);
	
	protected $default_data = array(
	);

	public function __get($var)
	{
		if ($var == 'Billable')
		{
			return $this->Quantity * $this->Charge;
		} else if ($var == 'project') {
			return new Project($this->ProjectID);
		} else {
			return parent::__get($var);
		}
		
	}
	
}