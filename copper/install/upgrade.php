<?php
include('functions.php');
include('common.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    if ($db->State() == 1)
    {
        upgrade();
        header('Location: ../index.php');
    }
    else
        $message = "Error: Could not connect to database.";
}
else
{
    $costRate = $GLOBALS['costRate'];
    $chargeRate = $GLOBALS['chargeRate'];
    if ($db->State() == 0)
        $message = "Error: Could not connect to database.";
}
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Copper 4 Upgrade</title>
        <style type="text/css" title="copper" media="screen">
            @import "../assets/styles/global.css";
            @import "../assets/styles/login.css";
        </style>

        <script type="text/javascript" src="../assets/js/lib/prototype.js"></script>

    </head>
    <body>
				<div class="spinner" id="ajaxSpinner"><span class="spinIn" id="ajaxSpinnerInner"></span></div>
        <div id="preload1"></div>
        <div id="wrapper">
            <!-- START HEADER -->
            <div id="header" style="background: url('../assets/images/header_bg.gif') left top no-repeat;">
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
                        <form action="" method="post" name="upgradeform" id="upgradeform" onsubmit="return validate();">
                            <h2 class="handle">Enter your details</h2>
                            <?php if ($db->state() == 1): ?>
                                <p>Your existing database connection has been detected. Copper can be upgraded using these credentials</p>

                                <dl>
                                    <dt>Default user cost rate</dt>
                                    <dd><input class="edit" id="cost" name="cost" value="<?php echo $costRate ?>" /></dd>
                                    <dd class="divider"> </dd>
                                    <dt>Default user charge rate</dt>
                                    <dd><input class="edit" id="charge" name="charge" value="<?php echo $chargeRate ?>"/></dd>
                                    <dd class="divider"> </dd>
                                </dl>
                                
                                <p><input type="submit" name='upgrade' value='Upgrade' /></p>
                            <?php else: ?>
                                <dl>
                                    <dt>Database Host</dt>
                                    <dd><input class="edit" id="hostname" name="hostname" value="<?php echo DB_SERVER ?>"/></dd>
                                    <dd class="divider"> </dd>
                                    <dt>Database Username</dt>
                                    <dd><input class="edit" id="username" name="username" value="<?php echo DB_USERNAME ?>"/></dd>
                                    <dd class="divider"> </dd>
                                    <dt>Database Password</dt>
                                    <dd><input class="edit" type="password" id="password" name="password" value="<?php echo DB_PASSWORD ?>"/></dd>
                                    <dd class="divider"> </dd>
                                    <dt>Database Name</dt>
                                    <dd><input class="edit" id="database" name="database" value="<?php echo DB_NAME ?>"/></dd>
                                    <dd class="divider"> </dd>
                                    <dt>Default user cost rate</dt>
                                    <dd><input class="edit" id="cost" name="cost" value="<?php echo $costRate ?>" /></dd>
                                    <dd class="divider"> </dd>
                                    <dt>Default user charge rate</dt>
                                    <dd><input class="edit" id="charge" name="charge" value="<?php echo $chargeRate ?>"/></dd>
                                    <dd class="divider"> </dd>

                                    <dd><a class="submit" href="#" onclick="validate(); return false">Upgrade</a></dd>
                                </dl>
                            <?php endif; ?>
                            <p class="message" id="message" style="font-weight:bold;"><?php echo $message ?></p>
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
    form = $('upgradeform');
    for (var i = 0; i < form.length; i++) {
        if (form[i].type == 'text' && form[i].name != 'password') {
            if (form[i].value == '') {
                $('message').update('Please fill in all fields.');
                return false;
            }
        }
    }
    upgrade();
}

var upgrade = function() {
    new Ajax.Request('dbtest.php', { 
        method: 'post', 
        parameters: { "hostname": $F('hostname'), "username": $F('username'), "password": $F('password'), "database": $F('database') },
        onSuccess: function(response) { 
            if (response.responseText == 'Ok') 
                $('upgradeform').submit();
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

