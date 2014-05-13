<?php
// $Id$

// core module, for stuff that doesn't happen in any specific module.
class mod_core extends Module
{

	function __construct() 
	{
		$this->ModuleName			= 'core';
		$this->RequireLogin 	= 1;
		$this->Public					= 1;
		parent::Module();
	}

	public function main() 
	{
		switch (Request::any('action'))
		{
			case 'close_alert': 		return $this->close_alert();
			default:								return null;
		}
	}
	
	private function close_alert()
	{
		if (Request::post('alert_id'))
		{
			$au = new AlertToUser(array(
				'alert_id' => Request::post('alert_id'),
				'user_id' => CopperUser::current()->ID,
			));
			$au->commit();

			if (Request::post('ajax'))
			{
				$ar = new AjaxResponse(TRUE);
				$ar->alert_id = Request::post('alert_id');
				$ar->out();
			} else {
				Response::redirect_back();
			}
			
		} else 
		{
			if (Request::post('ajax'))
			{
				$ar = new AjaxResponse(FALSE, "Insufficient Arguments");
				$ar->out();
			} else {
				Response::redirect_back();
			}
		}
		
	}
}