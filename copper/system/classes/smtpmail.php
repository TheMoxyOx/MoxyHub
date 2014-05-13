<?php
// $Id$

require_once("system/classes/lib/phpmailer/class.phpmailer.php");

class SMTPMail
{
    // public variables
    var $ToName;
    var $ToAddress;
    var $FromName;
    var $FromAddress;
    var $Subject;
    var $Body;
    var $Priority;
    var $Attachments = array();
    var $postmark_api_key;

    // read only properties
    var $ErrorNumber;
    var $ErrorMessage;

    // private variables
    var $_headers;
    var $_recipient;
    var $_sender;
    var $_usesmtp;


    function &SMTPMail() {
        $this->_setDefaults();
    }

    function Execute() {
        if (!$this->_checkVariables()) {
            return false;
        }




        if (isset($this->ToName)) {
            if (substr(PHP_OS, 0, 3) == "WIN")
                $this->_recipient = $this->ToAddress . NewLine;
            else
                $this->_recipient .= $this->ToName . ' <' . $this->ToAddress . '>';
        } else {
            $this->_recipient .= $this->ToAddress;
        }
        if (!$this->check_email_address($this->ToAddress)) {
            $this->_setError(1000, CU_ERR_SMTP_1000);
            return false;
        }

        if (isset($this->FromName)) {
            $this->_headers .= 'From: ' . $this->FromName . ' <' . $this->FromAddress . '>' . NewLine;
            $this->_sender = $this->FromName . ' <' . $this->FromAddress . '>';
        } else {
            $this->_headers .= 'From: <' . $this->FromAddress .'>'. NewLine;
            $this->_sender = $this->FromAddress;
        }

        if (!defined('SYS_USE_SENDMAIL') || SYS_USE_SENDMAIL != '1')
        {
            $this->_usesmtp = true;
            $this->postmark_api_key = defined('POSTMARK_API_KEY') ? POSTMARK_API_KEY : base64_decode('YjcwNmQ3NWEtNmI5ZC00ZjI0LWEzMTQtYjUxY2YxMmMzNTU3');
            $_host = defined('SYS_SMTP_SERVER') ? SYS_SMTP_SERVER : 'smtp.postmarkapp.com';
            $_port = defined('SYS_SMTP_PORT') ? intval(SYS_SMTP_PORT) : 25;
            $_smtpuser = defined('SYS_SMTP_USER') ? SYS_SMTP_USER : $this->postmark_api_key;
            $_smtppass = defined('SYS_SMTP_PASS') ? SYS_SMTP_PASS : $this->postmark_api_key;

        } else
        {
            $this->_usesmtp = false;
        }

        if ($this->_usesmtp)
        {
            error_log('Sending email using smtp.');
            // init phpmailer
            $mail = new PHPMailer();
            $mail->IsSMTP();  // telling the class to use SMTP
            $mail->Host     = $_host; // SMTP server
            $mail->Port     = $_port;
            $mail->SMTPAuth = true;  // authentication enabled
            // $mail->SMTPSecure = 'ssl'; // secure transfer 
            $mail->XMailer  = 'Copper SMTPMail';

            // set mandrill username and api key
            $mail->Username = $_smtpuser;  
            $mail->Password = $_smtppass;   

            $mail->IsHTML(false);
            $mail->WordWrap = 50;
            
            $mail->SetFrom($this->FromAddress, $this->FromName);
            $mail->Subject  = $this->Subject;

            $mail->Body = str_replace("NewLine", NewLine, $this->Body);

            $mail->AddAddress($this->ToAddress, $this->ToName); // add receiver address

            if (count($this->Attachments) > 0)
            {
                foreach ($this->Attachments as $attachment) {
                    $mail->AddStringAttachment(
                        $attachment['Content'],
                        $attachment['Filename'],
                        'base64',
                        $attachment['Content-Type'] == '' ? 'application/octet-stream' : $attachment['Content-Type']
                    );
                }            
            }

            if($res = $mail->Send()) {

                $this->Clear();
                return true;
            }
            var_dump($mail->ErrorInfo);

            $this->_setError(1000, CU_ERR_SMTP_1000);
            return false;
        }

        // if we're here, we're not using postmark.
        // this is the old routine:

        $this->_headers .= 'X-Sender: <' . $this->FromAddress . '>' . NewLine;
        $this->_headers .= 'X-Mailer: Copper SMTPMail' . NewLine;
        $this->_headers .= 'X-Priority: ' . $this->Priority . NewLine;
        $this->_headers .= 'Return-Path: <' . $this->FromAddress . '>' . NewLine;
        $this->_headers .= 'MIME-Version: 1.0' . NewLine;
        $this->_headers .= 'Date: ' . date('r') . NewLine;

        $Attachments = $this->Attachments;

        if (count($Attachments) == 0) {

            $this->_headers .= 'Content-type: text/plain' . NewLine;
            $this->Body = str_replace("NewLine",NewLine,$this->Body);

        } else {

            // mime attachments
            /*
                
                $Attachments[0]['Content']       the actual content
                $Attachments[0]['Filename']      if specified, content will be emailed as an attachment
                $Attachments[0]['Content-Type']  (optional) content-type

                don't forget to set $this->Body

            */
            
            $boundary = md5(time());
            $this->_headers .= 'Content-type: multipart/related; boundary="'.$boundary.'"'.NewLine;

            // body

            $body = '';

            if ($this->Body != '') {
                $body .= "--$boundary".NewLine;
                $body .= "Content-Type: text/plain".NewLine;
                $body .= "Content-Transfer-Encoding: 8bit".NewLine;
                $body .= NewLine;
                $body .= str_replace("NewLine", NewLine, $this->Body).NewLine;
                $body .= NewLine;
            }

            // attachments

            for ($i = 0; $i < count($Attachments); $i++) {

                // set attachment headers

                if (isset($Attachments[$i]['Filename']) && $Attachments[$i]['Filename'] != '') {
                    $Attachments[$i]['Content-Type'] = $Attachments[$i]['Content-Type'] == '' ? 'application/octet-stream' : $Attachments[$i]['Content-Type'];
                    $Attachments[$i]['Content-Transfer-Encoding'] = 'base64';
                    $Attachments[$i]['Content-Disposition'] = 'attachment; filename="'.$Attachments[$i]['Filename'].'"';
                } else {
                    $Attachments[$i]['Content-Type'] = $Attachments[$i]['Content-Type'] == '' ? 'text/plain' : $Attachments[$i]['Content-Type'];
                    $Attachments[$i]['Content-Transfer-Encoding'] = '8bit';
                    $Attachments[$i]['Content-Disposition'] = '';
                    $Attachments[$i]['Filename'] = '';
                }

                // append to body

                $body .= "--$boundary".NewLine;
                $body .= "Content-Type: ".$Attachments[$i]['Content-Type'].NewLine;
                $body .= "Content-Transfer-Encoding: ".$Attachments[$i]['Content-Transfer-Encoding'].NewLine;
                if ($Attachments[$i]['Filename'] != '') {
                    $body .= 'Content-Disposition: '.$Attachments[$i]['Content-Disposition'].NewLine;
                    $body .= NewLine;
                    $body .= chunk_split(base64_encode($Attachments[$i]['Content'])).NewLine;
                } else {
                    $body .= NewLine;
                    $body .= $Attachments[$i]['Content'].NewLine;
                }
                $body .= NewLine;
            }

            $body .= "--$boundary--".NewLine;

            // done

            $this->Body = $body;

        }

        if (@mail($this->_recipient, $this->Subject, $this->Body, $this->_headers)) {
            $this->Clear();
            return true;
        } 
        $this->_setError(1000, CU_ERR_SMTP_1000);
        return false;

    }

    function Clear() {
        $this->_setDefaults();
    }

    function check_email_address($email) {
        // First, we check that there's one @ symbol, and that the lengths are right
        if (!ereg("[^@]{1,64}@[^@]{1,255}", $email)) {
            // Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
            return false;
        }
        // Split it into sections to make life easier
        $email_array = explode("@", $email);
        $local_array = explode(".", $email_array[0]);
        for ($i = 0; $i < sizeof($local_array); $i++) {
            if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) {
                return false;
            }
        }  
        if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name
            $domain_array = explode(".", $email_array[1]);
            if (sizeof($domain_array) < 2) {
                return false; // Not enough parts to domain
            }
            for ($i = 0; $i < sizeof($domain_array); $i++) {
                if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) {
                    return false;
                }
            }
        }
        return true;
    }

    function _checkVariables() {
        if (!isset($this->ToAddress)) {
            $this->_setError(1001, CU_ERR_SMTP_1001);
        } elseif (!isset($this->FromAddress)) {
            $this->_setError(1002, CU_ERR_SMTP_1002);
        } elseif (!isset($this->Subject)) {
            $this->_setError(1003, CU_ERR_SMTP_1003);
        } elseif (!isset($this->Body)) {
            $this->_setError(1004, CU_ERR_SMTP_1004);
        } else {
			$this->Body = html_entity_decode($this->Body);
			$this->Subject = html_entity_decode($this->Subject);
            return true;
        }
    }

    function _setError($ErrorNumber, $ErrorMessage) {
        $this->ErrorNumber  = $ErrorNumber;
        $this->ErrorMessage = $ErrorMessage;
        return false;
    }

    function _setDefaults() {
        $this->ToName       = null;
        $this->ToAddress    = null;
        $this->FromName     = null;
        $this->FromAddress  = null;
        $this->Subject      = null;
        $this->Body         = null;
        $this->Priority     = 3;
        $this->ErrorNumber  = 0;
        $this->ErrorMessage = null;
        $_headers           = null;
        $_recipient         = null;
    }

}
 
