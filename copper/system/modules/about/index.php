<?php
// $Id$
class mod_about extends Module
{
    function mod_about()
    {
        $this->ModuleName   = 'about';
        $this->RequireLogin = 0;
        $this->Public       = 1;
        parent::Module();
    }

    function main()
    {
        $this->ShowAbout();
    }

    function ShowAbout()
    {
        $tmp['NAME_TITLE']      = MSG_NAME;
        $tmp['COMPANY_TITLE']   = MSG_COMPANY;
        $tmp['VERSION_TITLE']   = MSG_VERSION;
        $tmp['WEBLINK']         = MSG_WEB;
        $tmp['EMAILLINK']       = MSG_EMAIL;
        $tmp['FORUMSLINK']      = MSG_FORUMS;
        $tmp['WARNING_TITLE']   = MSG_WARNING;
        $tmp['WARNING_MESSAGE'] = MSG_COPYRIGHT_NOTICE;
        $tmp['BUILD_NUMBER']    = MSG_BUILD_NUMBER;
        $tmp['LICENSENAME']     = LICENSE_NAME;
        $tmp['LICENSECOMPANY']  = LICENSE_COMPANY;
        $tmp['PRODUCTVERSION']  = CU_PRODUCT_VERSION;
        $tmp['PRODUCTNAME']     = CU_PRODUCT_NAME;
        $tmp['COPYRIGHT']       = CU_COPYRIGHT;

				$rev = exec('svnversion');
				// check it's not something like 'exported' or 'command not found'
				if (preg_match('/[0-9:MS]+/', $rev) == 1) {
					$tmp['PRODUCTVERSION'] .= ":$rev";
				}

        $this->setHeader(MSG_ABOUT_THIS_PRODUCT);
        $this->setTemplate('about', $tmp);
        if ($this->User->Authorised == 1)
        {
            $this->setModule(MSG_ABOUT_THIS_PRODUCT, '<a href="javascript:history.go(-1);">'. MSG_BACK .'</a>');
        }
        else
        {
            $vars['BACKLINK'] = MSG_BACK;
            $this->setTemplate('back', $vars);
        }
        $this->Render();
    }
}

 
