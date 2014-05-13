<?php
// $Id$
class mod_profile extends Module
{
	function mod_profile()
	{
		$this->ModuleName	 = 'profile';
		$this->ModuleID		= 'AA2222DE8DB17A3441A75C92203FCE5A';
		$this->RequireLogin = 1;
		$this->Public		 = 1;
		parent::Module();
	}

	function main()
	{
		if (Request::any('module') == NULL)
			Response::redirect('index.php?module='.$this->User->Module);

		switch (Request::any('action'))
		{
			case 'edit' : $this->EditProfile(); break;
			case 'save' : $this->SaveProfile(); break;
			default	 : $this->ViewProfile();
		}
	}

	function ViewProfile($message = null)
	{
		$modHeader = MSG_PROFILE;
		$modCrumbs = CopperUser::current()->full_name.' '.MSG_VIEW;
		$modAction[0] =  '<a href="index.php?module=profile&amp;action=edit" target="_self" onfocus="if(this.blur)this.blur()" class="linkon">' . MSG_EDIT . '</a>';

		$tmpl['HEADER_PERSONAL'] = MSG_PERSONAL_DETAILS;
		$tmpl['HEADER_CONTACT']  = MSG_CONTACT_NAME;
		$tmpl['HEADER_SETTINGS'] = MSG_PERSONAL_SETTINGS;
		$tmpl['FORM_USERNAME']   = MSG_USERNAME;
		$tmpl['FORM_FIRSTNAME']  = MSG_FIRSTNAME;
		$tmpl['FORM_LASTNAME']   = MSG_LAST_NAME;
		$tmpl['FORM_PASSWORD']   = MSG_PASSWORD;
		$tmpl['FORM_PHONE1']	 = MSG_PHONE_1;
		$tmpl['FORM_PHONE2']	 = MSG_PHONE_2;
		$tmpl['FORM_PHONE3']	 = MSG_PHONE_3;
		$tmpl['FORM_EMAIL']	  = MSG_EMAIL;
		$tmpl['FORM_MODULE']	 = MSG_DEFAULT_MODULE;
		$tmpl['FORM_NOTIFY']	 = MSG_EMAIL_NOTIFICATION;
		$tmpl['USER_USERNAME']   = CopperUser::current()->Username;
		$tmpl['USER_FIRSTNAME']  = CopperUser::current()->FirstName;
		$tmpl['USER_LASTNAME']   = CopperUser::current()->LastName;
		$tmpl['USER_EMAIL']	  = CopperUser::current()->EmailAddress;
		$tmpl['USER_PHONE1']	 = CopperUser::current()->Phone1;
		$tmpl['USER_PHONE2']	 = CopperUser::current()->Phone2;
		$tmpl['USER_PHONE3']	 = CopperUser::current()->Phone3;
		$tmpl['USER_MODULE']	 = strtoupper(CopperUser::current()->Module);
		$tmpl['USER_NOTIFY']	 = (CopperUser::current()->EmailNotify) ? MSG_YES : MSG_NO;

		$this->setHeader($modHeader, $message);
		$this->setModule($modCrumbs, $modAction);
		$this->setTemplate('view', $tmpl);
		$this->Render();
	}

	function SaveProfile()
	{
		$user = CopperUser::current();
		
		// basics
		$user->FirstName 			= Request::post('first_name');
		$user->LastName				= Request::post('last_name');
		$user->Phone1					= Request::post('phone1');
		$user->Phone2					= Request::post('phone2');
		$user->Phone3					= Request::post('phone3');
		$user->EmailAddress		= Request::post('email');
		$user->Module					= Request::post('default_module');
		$user->EmailNotify		= (Request::post('notify') > 0) ? 1 : 0;
		
		$au = new AvatarUpload();
		$file_data = $au->create_from_upload('avatar');
		if (is_array($file_data))
		{
			$user->avatar = $file_data['filename'];
		} else {
			error_log(AvatarUpload::get_upload_error($file_data));
		}
		
		// now check for password change.
		$password  = Request::post('pass1');
		if ((strlen($password) > 0) && (Request::post('pass1') == Request::post('pass2'))) {
			$user->Password = $this->MD5($password);
		}

		$user->commit();
		Response::redirect('index.php?module=profile&updated=1');
	}

	function EditProfile()
	{
		$modHeader = MSG_PROFILE;
		$modCrumbs = CopperUser::current()->Fullname.' '.MSG_EDIT;
		$modAction[0] = '<a href="javascript:SubmitForm();">'.MSG_SAVE.'</a>';

		// @todo, convert to CopperUser once perms are migrated.
 		$modulelist = $this->User->ModuleList;

		// add in the profile because its not a menu module so it won't be in the users list of modules.
		$modulelist['profile'] = 'profile';
		$user_modules = null;

		foreach ($modulelist as $key => $value)
		{
			$user_modules .= sprintf(
				'<option value="%1$s" %3$s>%2$s</option>', 
				$value, 
				constant('MSG_'.strtoupper($key)), 
				(CopperUser::current()->Module == $value) ? 'SELECTED':''
			);
		}

		$tmpl['HEADER_PERSONAL']	= MSG_PERSONAL_DETAILS;
		$tmpl['HEADER_CONTACT']		= MSG_CONTACT_NAME;
		$tmpl['HEADER_SETTINGS']	= MSG_PERSONAL_SETTINGS;
		$tmpl['FORM_USERNAME']		= MSG_USERNAME;
		$tmpl['FORM_FIRSTNAME']		= MSG_FIRSTNAME;
		$tmpl['FORM_LASTNAME']		= MSG_LAST_NAME;
		$tmpl['FORM_PASSWORD']		= MSG_PASSWORD;
		$tmpl['FORM_PASSCONF']		= MSG_CONFIRM_PASSWORD;
		$tmpl['FORM_PHONE1']			= MSG_PHONE_1;
		$tmpl['FORM_PHONE2']			= MSG_PHONE_2;
		$tmpl['FORM_PHONE3']			= MSG_PHONE_3;
		$tmpl['FORM_EMAIL']				= MSG_EMAIL;
		$tmpl['FORM_MODULE']			= MSG_DEFAULT_MODULE;
		$tmpl['FORM_NOTIFY']			= MSG_EMAIL_NOTIFICATION;
		$tmpl['FORM_AVATAR']			= MSG_AVATAR;
		$tmpl['USER_USERNAME'] 		= CopperUser::current()->Username;
		$tmpl['USER_FIRSTNAME']		= CopperUser::current()->FirstName;
		$tmpl['USER_LASTNAME'] 		= CopperUser::current()->LastName;
		$tmpl['USER_EMAIL']				= CopperUser::current()->EmailAddress;
		$tmpl['USER_PHONE1']			= CopperUser::current()->Phone1;
		$tmpl['USER_PHONE2']			= CopperUser::current()->Phone2;
		$tmpl['USER_PHONE3']			= CopperUser::current()->Phone3;
		$tmpl['USER_MODULES']			= $user_modules;
		$tmpl['USER_NOTIFY']			= (CopperUser::current()->EmailNotify) ? 'checked' : '';
		$tmpl['txtPassError']			= MSG_PASSWORDS_NOT_MATCH;

		$this->setHeader($modHeader);
		$this->setModule($modCrumbs, $modAction);
		$this->setTemplate('edit', $tmpl);
		$this->Render();
	}
}
 
