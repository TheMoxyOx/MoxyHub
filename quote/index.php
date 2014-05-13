<?php 
include("../includes/header.php"); 
include ('classes/mysql.php');
include_once('../quote/jcart/jcart/jcart.php');

$mysql = New Mysql();

?>
<script>
function loadXMLDoc()
{
var name = document.getElementById("prodName").value;
var paper = document.getElementById("paper").value;
var length = document.getElementById("length").value;
var width = document.getElementById("width").value;
if (document.getElementById("bleed").checked) {
	var bleed = document.getElementById("bleed").value;
}

var quantity1 = document.getElementById("quantity1").value;
var quantity2 = document.getElementById("quantity2").value;
var quantity3 = document.getElementById("quantity3").value;
var color = document.getElementById("color").value;

if (document.getElementById("fold").checked) {
	var fold = document.getElementById("fold").value;
}

if (document.getElementById("book").checked) {
	var book = document.getElementById("book").value;
}

if (document.getElementById("notepad").checked) {
	var notepad = document.getElementById("notepad").value;
}

var cover = document.getElementById("cover").value;
var pages = document.getElementById("pages").value;
if (document.getElementById("spiral").checked) {
	var spiral = document.getElementById("spiral").value;
}

if (document.getElementById("saddle").checked) {
	var saddle = document.getElementById("saddle").value;
}

if (document.getElementById("round").checked) {
	var round = document.getElementById("round").value;
}

if (document.getElementById("uvSpot").checked) {
	var uvSpot = document.getElementById("uvSpot").value;
}

if (document.getElementById("uvFlood").checked) {
	var uvFlood = document.getElementById("uvFlood").value;
}

if (document.getElementById("drill").checked) {
	var drill = document.getElementById("drill").value;
}

if (document.getElementById("lam").checked) {
	var lam = document.getElementById("lam").value;
}

if (document.getElementById("lamcover").checked) {
	var lamcover = document.getElementById("lamcover").value;
}

if (document.getElementById("score").checked) {
	var score = document.getElementById("score").value;
}

if (document.getElementById("white").checked) {
	var white = document.getElementById("white").value;
}

var margin1 = document.getElementById("margin1").value;
var margin2 = document.getElementById("margin2").value;
var margin3 = document.getElementById("margin3").value;




var xmlhttp;
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
    document.getElementById("myDiv").innerHTML=xmlhttp.responseText;
    }
  }
xmlhttp.open("POST", "quote.php" ,true);
xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
xmlhttp.send("name=" + name + "&paper=" + paper + "&length=" + length + "&width=" + width + "&bleed=" + bleed + "&quantity1=" + quantity1 + "&color=" + color + "&fold=" + fold + "&book=" + book + "&notepad=" + notepad + "&cover=" + cover + "&pages=" + pages + "&spiral=" + spiral + "&saddle=" + saddle + "&round=" + round +  "&uvSpot=" + uvSpot +  "&uvFlood=" + uvFlood + "&drill=" + drill +  "&lam=" + lam + "&lamcover=" + lamcover + "&score=" + score +  "&white=" + white + "&margin1=" + margin1 + "&margin2=" + margin2 + "&margin3=" + margin3 + "&quantity2=" + quantity2 + "&quantity3=" + quantity3);
}


function loadXMLDoc1()
{

var xmlhttp;
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
    document.getElementById("myDiv").innerHTML=xmlhttp.responseText;
    }
  }
xmlhttp.open("GET", "http://hub.moxyox.com/quote/quote.php?clear=clear" ,true);
xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
xmlhttp.send();
}


</script>
<div class="col-lg-6">


<form action="quote.php" method="post">
<label for="prodName">Enter Product Name</label>
<input id="prodName" type="text" name="prodName"> <br>
Select Paper Type
	<select id="paper" name="paper">
		<?php $mysql->list_papers(); ?>
	</select><br>
	<label for="length">Enter Product Length in inches (or spread size if booklet): </label>
	<input id="length" type="text" name="length"><br>

	<label for="width">Enter Product Width in inches (or spread size if booklet): </label>
	<input id="width" type="text" name="width"><br>

<input id="bleed" type="checkbox" name="bleed" value="bleed">Has Bleed<br>
Enter Desired Quantity #1: <input id="quantity1" type="text" name="quantity1"><br>
Enter Desired Quantity #2: <input id="quantity2" type="text" name="quantity2"><br>
Enter Desired Quantity #3: <input id="quantity3" type="text" name="quantity3"><br>
Select Ink Colors: 
	<select id="color" name="color">
		<option value="4/4">4/4</option>
		<option value="4/1">4/1</option>
		<option value="4/0">4/0</option>
		<option value="1/1">1/1</option>
		<option value="1/0">1/0</option>
	</select><br>
<input id="fold" type="checkbox" name="fold" value="fold">Folded<br>
<input id="book" type="checkbox" name="book" value="book">Booklet<br>
<input id="notepad" type="checkbox" name="notepad" value="notepad">Notepad<br>
Booklet Cover	<select id="cover" name="cover">
		<?php $mysql->list_papers(); ?>
	</select><br>
Number of Pages: <input id="pages" type="text" name="pages"><br>
<input id="spiral" type="checkbox" name="spiral" value="spiral">Spiral Bound<br>
<input id="saddle" type="checkbox" name="saddle" value="saddle">Saddle Stitched<br>
<input id="round" type="checkbox" name="round" value="round">Rounded Corners<br>
<input id="uvSpot" type="checkbox" name="uvSpot" value="uvSpot">UV coating (spot)<br>
<input id="uvFlood" type="checkbox" name="uvFlood" value="uvFlood">UV coating (flood)<br>
<input id="drill" type="checkbox" name="drill" value="drill">Drilled<br>
<input id="lam" type="checkbox" name="lam" value="lam">Laminated<br>
<input id="lamcover" type="checkbox" name="lamcover" value="lamcover">Cover Laminated<br>
<input id="score" type="checkbox" name="score" value="score">Score/Perf<br>
<input id="white" type="checkbox" name="white" value="white">White Ink<br>
Desired Margin #1(enter only a number, no %)<input id="margin1" type="text" name="margin1"><br>
Desired Margin #2(enter only a number, no %)<input id="margin2" type="text" name="margin2"><br>
Desired Margin #3(enter only a number, no %)<input id="margin3" type="text" name="margin3"><br>

</form>

<button type="button" onclick="loadXMLDoc()">Calculate</button>
<button type="button" onclick="loadXMLDoc1()">Clear</button>
</div>

<div id="jcart"><?php// $jcart->display_cart();?></div>

<div class="col-lg-6" id="myDiv"></div>


</body>
<html>