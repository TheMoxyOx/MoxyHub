<?php
include('functions.php');
include('common.php');
$db = $GLOBALS['db'];
$detected = ($db->State() == 1) ? 'has' : '(if you have one) has not';
$message = "Your existing Copper installation $detected been detected.";

$php = checkPHPVersion('5.0.0');
$mysql = checkMySQLVersion('5.0.1');
if (!$mysql || !$php)
    $message = "Your server software must be upgraded to run Copper 4.";

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

    </head>
    <body>
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
                        <form action="" method="post" name="installform" id="installform" onsubmit="return validate();">
                            <h2 class="handle" style="cursor:default;">Select an option</h2>
                            <dl>
<?php if (!$php) { ?>
                                <dt>PHP Version Required</dt>
                                <dd>5.0 or newer</dd>
                                <dd class="divider"> </dd>
                                <dt>Your PHP Version</dt>
                                <dd><?= PHP_VERSION ?></dd>
                                <dd class="divider"> </dd>
<?php } ?>
<?php if (!$mysql) { ?>
                                <dt>MySQL Version Required</dt>
                                <dd>5.0.1 or newer</dd>
                                <dd class="divider"> </dd>
                                <dt>Your MySQL Version</dt>
                                <dd><?= mysql_get_client_info(); ?></dd>
<?php } ?>
<?php if ($mysql && $php) { ?>
                                <dd class="divider"> </dd>
                                <dd><a class="submit" href="install.php">Install</a></dd>
                                <dd class="divider"> </dd>
                                <dd><a class="submit" href="upgrade.php">Upgrade</a></dd>
<?php } ?>
                            </dl>
                            <p class="message" id="message" style="font-weight:bold;"><?= $message ?></p>
                        </form>
                    </div>
                    <div id="login-island-btm"></div>
                </div>
                <div id="login-img-btm"></div>
            </div>
            <!-- END LOGIN WRAPPER -->
        </div>
        <!-- END WRAPPER -->
    </body>
</html>

