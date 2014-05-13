<?php
/**
 * Base Database item
 * $Id$
 */

class Invoice extends Item
{
	protected $tableName 	= 'tblInvoices';
	
	protected $defaultFields = array(
		'ID',
		'Quote',
		'ProjectID',
		'Title',
		'DateCreated',
		'CreatedBy',
		'Status',
		'Amount',
		'Due',
		'EmailedTo',
	);
	
	private $subtotal_val = null;
	
	protected $default_data = array(
	);

	public function __get($var)
	{
		if ($var == 'subtotal')
		{
			if ($this->subtotal_val == null)
			{
				$q = 'SELECT SUM(Amount) as subtotal FROM tblInvoices_Items WHERE InvoiceID = ?';
				$params = array($this->ID);
				$this->subtotal_val = DB::scalar($q, $params);
			}
			
			return $this->subtotal_val;
		} else if ($var == 'tax')
		{
			
			return $this->subtotal * Invoice::tax_rate();
		} else if ($var == 'total')
		{
			return $this->subtotal * (1 + Invoice::tax_rate());
		} else {
			return parent::__get($var);
		}
	}
	
	public static function tax_rate()
	{
		$tax_rate = Settings::get('TaxRate');
		if ($tax_rate == null) {
			$tax_rate = 0;
		} else {
			$tax_rate = $tax_rate / 100; // in the db, it's stored as 10 for 10% tax.
		}
		return $tax_rate;
	}
}