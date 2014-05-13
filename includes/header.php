<?php
define('ROOTPATH', "http://hub.moxyox.com");

?>

<!DOCTYPE html>
<html>
  <head>
    <title>Moxy Ox Hub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <link href="<?php echo ROOTPATH; ?>/css/bootstrap.min.css" rel="stylesheet" media="screen">
    <link href="<?php ROOTPATH; ?>/css/style.css" rel="stylesheet">
     <!-- JavaScript plugins (requires jQuery) -->
    <script src="http://code.jquery.com/jquery.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="<?php ROOTPATH; ?>/js/bootstrap.js"></script>
  </head>
<body>
<div class="container">
<img id="logo" src="<?php ROOTPATH; ?>/img/logo.png" alt="logo">
<!-- nav -->
<div id="nav" class="row text-center">
	<a href="<?php echo ROOTPATH; ?>/index.php"><div class="col-lg-2">Home</div></a>
	<a href="<?php echo ROOTPATH; ?>/timeclock/index.php"><div class="col-lg-2">TimeClock</div></a>
	<a href="<?php echo ROOTPATH; ?>/quote/index.php"><div class="col-lg-2">Quote Generator</div></a>
	<a href="<?php echo ROOTPATH; ?>/quote/checkout.php"><div class="col-lg-2">Internal Order Page</div></a>
	<a href="<?php echo ROOTPATH; ?>/copper/index.php"><div class="col-lg-2">Copper</div></a>
	<a href="<?php echo ROOTPATH; ?>/server/index.php"><div class="col-lg-2">Server</div></a>
</div>
 
 
 
 
 



