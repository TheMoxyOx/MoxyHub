<?php
include("../includes/header.php");
// jCart v1.3
// http://conceptlogic.com/jcart/

// This file demonstrates a basic checkout page

// If your page calls session_start() be sure to include jcart.php first
include_once('jcart/jcart/jcart.php');

session_start();
?>


<p>under construction</p>

<form class="col-lg-8 text-right" id="order" action="mail.php" method="get">
	<h3>Order Form, for internal use only.</h3><br><br>
	<h5>Please Fill out this form with as much detail as possible</h5> <br>

	<label for="firstName">Customer First Name: </label>
	<input id="firstName" type="text" name="firstName"><br>

	<label for="lastName">Customer Last Name: </label>
	<input id=lastName"" type="text" name="lastName"><br>
	
	<label for="company">Company Name: </label>
	<input id="company" type="text" name="company"><br>
	
	<label for="new">New Customer</label>
	<input id="new" type="checkbox" name="new" value="new"><br>
	
	<label for="address1">Address Line 1: </label>
	<input id="address1" type="text" name="address1"><br>
	
	<label for="address1">Address Line 2: </label>
	<input id="address2" type="text" name="address2"><br>
	
	<label for="city">City: </label>
	<input id="city" type="text" name="city"><br>
	
	<label for="state">State: </label>
	<input id="state" type="text" name="state"><br>
	
	<label for="zip">Zip: </label>
	<input id="zip" type="text" name="zip"><br>
	
	<label for="phone">Phone: </label>
	<input id="phone" type="text" name="phone"><br>
	
	<label for="email">Email Address: </label>
	<input id="email" type="email" name="email"><br>
	
	<label for="notes">Notes:</label>
	

<div id="jcart"><?php $jcart->display_cart();?></div>



<input type="submit" form="order" value="Submit Order">
<form>
