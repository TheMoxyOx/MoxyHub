<?php
// $Id$
class mod_error extends Module
{
    function mod_error()
    {
        $this->ModuleName     = 'error';
        $this->ModuleID        = '1B8513DD11A85A9A515EE520CC06CA8A';
        $this->RequireLogin = 0;
        $this->Public         = 1;
        parent::Module();
    }

    function main()
    {
        $code = Request::any('error');
        $from = Request::any('from');
        $inject = base64_decode(Request::any('inject'));

        $vars['TITLE']  = MSG_ERROR;
//change_log 1.
        $vars['OUTPUT'] = $this->getMessage($from, $code, $inject);

        $this->setHeader(MSG_ERROR_SYSTEM);
        $this->setTemplate('error', $vars);
        $this->Render();
    }
//change_log 1.
    function getMessage($module = null, $code = 0, $inject = null)
    {
        if ($code < 5000)
        {
            $newcode = @constant('MSG_ERR_'.$code);

            if ($code == 4000) {
                $code = sprintf($newcode, $inject);
            }
            else
                $code = (isset($newcode)) ? $newcode : MSG_ERR_UNKNOWN;
            return $code;
        }
        else
        {
            //user defined error
            $path = sprintf(CU_MODULE_PATH, $module);
            $defined = $path . sprintf(CU_MODULE_ERRORS, Settings::get('DefaultLanguage'));
            $default = $path . sprintf(CU_MODULE_ERRORS, CU_DEFAULT_LANGUAGE);
            if (file_exists($defined))
            {
                include($defined);
                $code = @constant('ERR_'.$code);
                $code = (isset($code)) ? $code : ERR_DEFAULT;
                return $code;
            }
            elseif (file_exists($default))
            {
                include($default);
                $code = @constant('ERR_'.$code);
                $code = (isset($code)) ? $code : ERR_DEFAULT;
                return $code;
            }
            else
            {
                // go with error module error message.
                $code = @constant('MSG_ERR_'.$code);
                $code = (isset($code)) ? $code : MSG_ERR_UNKNOWN;
                return $code;
            }
        }
    }
}

 
