<?php
/**
 * Base Database item
 * $Id$
 */

// note that even 'other' items get added in here, to actually link them to a project.
class InvoiceItem extends Item
{
	protected $tableName 	= 'tblInvoices_Items';
	
	protected $defaultFields = array(
		'ID',
		'InvoiceID',
		'TaskID',
		'TaskName',
		'TaskDescription',
		'Amount',
		'AdditionalID',
	);
	
	protected $defaultData = array(
	);
	
}