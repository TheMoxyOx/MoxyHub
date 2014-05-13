<?php
/**
 * Avatarupload class
 * $Id $
 */

class AppearancePDFUpload extends Upload
{
	public function __construct()
	{
		parent::__construct(array(
			'application/pdf'  => 'pdf',
		));
	}
	
}