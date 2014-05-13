<?php
include('functions.php');
include('common.php');

if (($db->State() == 1) && (defined("DB_USERNAME")))
{
    /* are all defined (ie have a config) and connected, we shouldn't be doing no dirty installer */
    header('Location: ../index.php');
    exit();
}

$showConfigFileContents = FALSE;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    /* we've recieved an attempt at db credentials, lets try them out */
    if ($db->State() == 0) {
        $message = "Error: Could not connect to database.";
    } 
    else {
        if (is_readable('config_local.php.template')) {
            $file = file_get_contents('config_local.php.template');
            $_POST['date'] = date('Y-m-d');
            $_POST['servernamevar'] = 'SERVER_NAME';
            $_POST['scriptnamevar'] = 'SCRIPT_NAME';
            foreach ($_POST as $k => $v)
            {
                // make sure things like ' are escaped in the config file //
                $file = str_replace('{' . $k . '}', ensureSlashes($v), $file);
            }
            $result = @file_put_contents('../config_local.php', $file); 
            if ($result) {
                install();
                header('Location: ../index.php');
            }
            else {
                $message = "Error: Could not create config_local.php file.";
                $showConfigFileContents = TRUE;
            }
        }
        else {
            $message = "Error: Could not create config_local.php file (template file unreadable).";
        }
    }
}
else {
    $message = 'I\'m gonna install some sweet sweet Copper now :)';
    
    $costRate = $GLOBALS['costRate'];
    $chargeRate = $GLOBALS['chargeRate'];
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Copper 4 Installation</title>
        <style type="text/css" title="copper" media="screen">
            @import "../assets/styles/global.css";
            @import "../assets/styles/login.css";
        </style>

        <script type="text/javascript" src="../assets/js/lib/prototype.js"></script>
		    <script type="text/javascript" src="../assets/js/scriptaculous/scriptaculous.js?load=effects,dragdrop&amp;v={TIMESTAMP}"></script>        

    </head>
    <body>
				<div class="spinner" id="ajaxSpinner"><span class="" id="ajaxSpinnerInner"></span></div>
        <div id="preload1"></div>
        <div id="wrapper">
            <!-- START HEADER -->
            <div id="header">
                <!-- START LOGO -->
                <h1><img src="../assets/images/copper_logo.png" width="136" height="41" alt="Copper" /></h1>
                <!-- END LOGO -->
            </div>
            <!-- END HEADER -->
            <!-- START LOGIN WRAPPER -->
            <div id="login-wrapper">
                <div id="login-img-top"></div>
                <img src="../assets/images/login/login_img_default.jpg" width="950" height="590" alt="" />
                <div id="login-island" style="top:20px">
                    <div id="login-island-content">
                        <form action="" method="post" name="installform" id="installform" onsubmit="return validate();">
                            <h2 class="handle">Enter your details</h2>
                            <dl>
                                <dt>Database Host</dt>
                                <dd><input class="edit" id="hostname" name="hostname" value="DB_SERVER"/></dd>
                                <dd class="divider"> </dd>
                                <dt>Database Username</dt>
                                <dd><input class="edit" id="username" name="username" value="DB_USERNAME"/></dd>
                                <dd class="divider"> </dd>
                                <dt>Database Password</dt>
                                <dd><input class="edit" type="password" id="password" name="password" value="DB_PASSWORD"/></dd>
                                <dd class="divider"> </dd>
                                <dt>Database Name</dt>
                                <dd><input class="edit" id="database" name="database" value="DB_NAME"/></dd>
                                <dd class="divider"> </dd>
                                <dt>Registered Name</dt>
                                <dd><input class="edit" id="name" name="name" value="LICENSE_NAME"/></dd>
                                <dd class="divider"> </dd>
                                <dt>Registered Company</dt>
                                <dd><input class="edit" id="company" name="company" value="LICENSE_COMPANY"/></dd>
                                <dd class="divider"> </dd>
                                <dt>Registration Code</dt>
                                <dd><input class="edit" id="code" name="code" value="LICENSE_CODE"/></dd>
                                <dd class="divider"> </dd>
                                <dt>Emails: Use PHPs 'mail' function to send mail (standard)</dt>
                                <dd><input class="edit" id="sys_use_mail" name="sys_use_mail" value="1"/></dd>
                                <dd class="divider"> </dd>
                                <dt>Emails: SMTP Server (if not using PHP mail)</dt>
                                <dd><input class="edit" id="smtp_server" name="smtp_server" value="SYS_SMTP_SERVER"/></dd>
                                <dd class="divider"> </dd>
                                <dt>Emails: SMTP Port</dt>
                                <dd><input class="edit" id="smtp_port" name="smtp_port" value="SYS_SMTP_PORT"/></dd>
                                <dd class="divider"> </dd>
                                <dt>Emails: SMTP Username</dt>
                                <dd><input class="edit" id="smtp_user" name="smtp_user" value="SYS_SMTP_USER"/></dd>
                                <dd class="divider"> </dd>
                                <dt>Emails: SMTP Password</dt>
                                <dd><input class="edit" type="password" id="smtp_pass" name="smtp_pass" value="SYS_SMTP_PASS"/></dd>
                                <dd class="divider"> </dd>
                                <dt>Email Notification 'From' Address</dt>
                                <dd><input class="edit" id="fromaddr" name="fromaddr" value="SYS_FROMADDR"/></dd>
                                <dd class="divider"> </dd>
                                <dt>Email Notification 'From' Name</dt>
                                <dd><input class="edit" id="fromname" name="fromname" value="SYS_FROMNAME"/></dd>
                                <dd class="divider"> </dd>
                                <dt>Full path to project files storage</dt>
                                <dd><input class="edit" id="filepath" name="filepath" value="SYS_FILEPATH"/></dd>
                                <dd class="divider"> </dd>
                                <dt>Default user cost rate</dt>
                                <dd><input class="edit" id="cost" name="cost" value="<?php echo $costRate ?>" /></dd>
                                <dd class="divider"> </dd>
                                <dt>Default user charge rate</dt>
                                <dd><input class="edit" id="charge" name="charge" value="<?php echo $chargeRate ?>"/></dd>
                                <dd class="divider"> </dd>
                                <dd><a class="submit" href="#" onclick="validate(); return false">Install</a></dd>
                            </dl>
                            <p class="message" id="message" style="font-weight:bold;"><?php echo $message ?></p>
                            <?php if ($showConfigFileContents): ?>
                                <h4 class="toggler">
                                    <a class="max" onclick="toggleComponent(this,'.configFileContents'); return false;" href="#">Config File Contents</a>
                                </h4>
                                <div id="configFileContents" style="overflow: visible;">
                                    <p>You need to copy and paste the following into a file, and save it under config_local.php 
                                        in the root of your install
                                    </p>
                                    <textarea><?php echo $file ?></textarea>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                    <div id="login-island-btm"></div>
                </div>
                <div id="login-img-btm"></div>
            </div>
            <!-- END LOGIN WRAPPER -->
            <script type="text/javascript" language="javascript">
                // <![CDATA[
var validate = function() {
    form = $('installform');
    for (var i = 0; i < form.length; i++) {
        if (form[i].type == 'text' && form[i].name != 'password') {
            if (form[i].value == '') {
                $('message').update('Please fill in all fields.');
                return false;
            }
        }
    }
    install();
}

var install = function() {
    new Ajax.Request('dbtest.php', { 
        method: 'post', 
        parameters: { "hostname": $F('hostname'), "username": $F('username'), "password": $F('password'), "database": $F('database') },
        onSuccess: function(response) { 
            if (response.responseText == 'Ok') 
                $('installform').submit();
            else 
                $('message').update(response.responseText);
        },
        onFailure: function() { $('message').update('Database connection failed. Installation aborted.'); }
    });
}
                //]]>
            </script>
            <!-- END WRAPPER -->
        </div>
        <!-- END WRAPPER -->
    </body>
</html>

