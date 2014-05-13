<?php

//---------------*includes*-----------------
include ('classes/mysql.php');
include ('calculate.php');
include ('constants.php');
include_once('jcart/jcart/jcart.php');
//------------------------------------------



//----------*session stuff*-----------------
session_start();


if (isset($_GET["clear"])) {
	session_destroy();
}

if (!isset($_SESSION["numProd"])) {

	$_SESSION["numProd"] = 0;
}
//------------------------------------------



/*unfinished, eventually for cart functions
function remove_item($itemNum) {


}*/




//------------------------------------------
$mysql = new mysql();
//------------------------------------------






//----*getting the first few POST vars*-----
$ID = $_POST["paper"];
$quantity1 = $_POST["quantity1"];
$quantity2 = $_POST["quantity2"];
$quantity3 = $_POST["quantity3"];
$preMargin1 = $_POST["margin1"];
$preMargin2 = $_POST["margin2"];
$preMargin3 = $_POST["margin3"];
$prodSize[1] = $_POST["length"];
$prodSize[0] = $_POST["width"];
$color = $_POST['color'];
//------------------------------------------





//--------------*if book, then cover*--------
// TODO functionitize this bit
if (isset($_POST["book"])){
	$itemBook = 1;
	$coverID = $_POST["cover"];
	$coverPrice = $mysql->get_price($coverID);
	$coverSize = $mysql->get_size($coverID);
	$coverClass = $mysql->get_class($coverID);
	if ($coverSize[0]!="*"){
		$coverSize = explode(" x ", $size);
		$coverLength = $size[1];
		$coverWidth = $size[0];
	}
	else {
		$itemBook = 0;
		$itemCover = 0; //TBD (size N up)
	}
}
//------------------------------------------





//--------------*getting paper info*---------
$price = $mysql->get_price($ID);

$size = $mysql->get_size($ID);

$class = $mysql->get_class($ID);
//------------------------------------------






//-------*getting paper size*---------------
 $paperSize = parseSize($size);
//------------------------------------------




//---*adding bleed--------------------------
$prodSize = getBleed($prodSize);
//------------------------------------------



//-----*getting Nup*------------------------
$Nup = getNup($paperSize, $prodSize, $vertCuts, $horizCuts);
//------------------------------------------


// TODO functionitize this bit
if ($size[0]=="*") {
	$size = str_replace("*", '', $size);
	$size = explode("/", $size);
	$Nup = $size[0];
}




//---*getting total sheets used*------------
$totalSheets1 = getTotalSheets($Nup, $quantity1);
$totalSheets2 = getTotalSheets($Nup, $quantity2);
$totalSheets3 = getTotalSheets($Nup, $quantity3);
//------------------------------------------





//---*add labor and change totalsheets if notepad*---
notepad($Nup, $quantity1, $labor1, $totalSheets1, $pages);
notepad($Nup, $quantity2, $labor2, $totalSheets2, $pages);
notepad($Nup, $quantity3, $labor3, $totalSheets3, $pages);
//------------------------------------------



//---* change totalsheets if book*----------
$spreads1 = book($Nup, $quantity1, $totalSheets1, $pages);
$spreads2 = book($Nup, $quantity2, $totalSheets2, $pages);
$spreads3 = book($Nup, $quantity3, $totalSheets3, $pages);
//------------------------------------------




//------------*determine click costs*-------
$clickCost1 = getClickCost($color, $totalSheets1);
$clickCost2 = getClickCost($color, $totalSheets2);
$clickCost3 = getClickCost($color, $totalSheets3);
//------------------------------------------




//------*determine raw material costs*------
$rawCost1 = getRawMatCost($totalSheets1, $price, $clickCost1);
$rawCost2 = getRawMatCost($totalSheets2, $price, $clickCost2);
$rawCost3 = getRawMatCost($totalSheets3, $price, $clickCost3);
//------------------------------------------


//------*determine saddle costs costs*------
getSaddleCost($rawCost1, $labor1, $quantity1);
getSaddleCost($rawCost2, $labor2, $quantity2);
getSaddleCost($rawCost3, $labor3, $quantity3);
//------------------------------------------



//------*determine spiral costs*------------
getSpiralCost($rawCost1, $labor1, $quantity1);
getSpiralCost($rawCost2, $labor2, $quantity2);
getSpiralCost($rawCost3, $labor3, $quantity3);
//------------------------------------------




//---*determine modified sheet thickness*---
$modSheets1 = getSheetThickness($class, $totalSheets1);
$modSheets2 = getSheetThickness($class, $totalSheets2);
$modSheets3 = getSheetThickness($class, $totalSheets3);
//------------------------------------------




//---*determine cost of cover booklet*------
getBookletCoverCost($Nup, $quantity1, $coverPrice, $rawCost1);
getBookletCoverCost($Nup, $quantity2, $coverPrice, $rawCost2);
getBookletCoverCost($Nup, $quantity3, $coverPrice, $rawCost3);
//------------------------------------------



//---*determine cutting cost*---------------
getCuttingCost($modSheets1, $labor1, $numStacks1, $vertCuts, $horizCuts, $Nup);
getCuttingCost($modSheets2, $labor2, $numStacks2, $vertCuts, $horizCuts, $Nup);
getCuttingCost($modSheets3, $labor3, $numStacks3, $vertCuts, $horizCuts, $Nup);
//------------------------------------------

//---*determine rounding cost*--------------
getRoundingCost($labor1, $numStacks1);
getRoundingCost($labor2, $numStacks2);
getRoundingCost($labor3, $numStacks3);
//------------------------------------------


//---*determine score/perf cost*------------
getScoreCost($labor1, $quantity1);
getScoreCost($labor2, $quantity2);
getScoreCost($labor3, $quantity3);
//------------------------------------------



//---*determine UV spot cost*---------------
getUVSpotCost($rawCost1, $totalSheets1);
getUVSpotCost($rawCost2, $totalSheets2);
getUVSpotCost($rawCost3, $totalSheets3);
//------------------------------------------



//---*determine UV flood cost*--------------
getUVFloodCost($rawCost1, $totalSheets1);
getUVFloodCost($rawCost2, $totalSheets2);
getUVFloodCost($rawCost3, $totalSheets3);
//------------------------------------------



//------*determine drill cost*--------------
getDrillCost($numStacks1, $labor1);
getDrillCost($numStacks2, $labor2);
getDrillCost($numStacks3, $labor3);
//------------------------------------------



//-------*determine fold cost*--------------
getFoldCost($quantity1, $labor1);
getFoldCost($quantity2, $labor2);
getFoldCost($quantity3, $labor3);
//------------------------------------------



//--------*determine lam cost*--------------
getLamCost($totalSheets1, $rawCost1, $labor1, $quantity1, $Nup);
getLamCost($totalSheets2, $rawCost2, $labor2, $quantity2, $Nup);
getLamCost($totalSheets3, $rawCost3, $labor3, $quantity3, $Nup);
//------------------------------------------


//--------*determine white cost*------------
getWhiteCost($totalSheets1, $rawCost1);
getWhiteCost($totalSheets2, $rawCost2);
getWhiteCost($totalSheets1, $rawCost3);
//------------------------------------------



//---*rounds labor to nearest 1/4 hour*-----
/*
$labor1 = roundLabor($labor1);
$labor2 = roundLabor($labor2);
$labor3 = roundLabor($labor3);*/
$laborHour1 = $labor1 / HOUR;
$laborHour2 = $labor2 / HOUR;
$laborHour3 = $labor3 / HOUR;
//------------------------------------------


$runTime1 = calcRunTime($totalSheets1, $color, $class);
$runTime2 = calcRunTime($totalSheets2, $color, $class);
$runTime3 = calcRunTime($totalSheets3, $color, $class);


//calc margin1
$margin1 = (100-$preMargin1)/100;
//calc margin2
$margin2 = (100-$preMargin2)/100;
//calc margin3
$margin3 = (100-$preMargin3)/100;


//setting pages (to make sure it's not uninitialized
if ($itemBook == 0 && $itemNotepad == 0) {
	$pages = 0;
}


//generate item code
//$itemCode = $ID."-".$paperSize[1]."-".$paperSize[0]."-".$itemBleed."-".$quantity1."-".$color."-".$itemFold."-".$itemBook."-".$itemNotepad."-".$itemCover."-".$pages."-".$itemSpiral."-".$itemSaddle."-".$itemRound."-".$itemUVSpot."-".$itemUVFlood."-".$itemDrill."-".$itemLam."-".$itemLamCover."-".$itemScore."-".$itemWhite;


?>
<!--
<div class="col-lg-6">
<?php
echo "MSRP: $".ceil(($labor + $rawCost)/$margin);
echo "<br><br><br> debugging <br>";
echo "total labor cost: $".$labor1;
echo "<br> total raw mat cost: $".$rawCost1;
echo "<br> total sheets used:".$totalSheets1;
echo "<br> total click cost: $".$clickCost1;
echo "<br> paper price(per sheet): $".$price1;
echo "<br> cutting labor: $".$cuttingLabor1;
echo "<br> drill labor: $".$drillingLabor1;
echo "<br> rounding laobr: $".$roundingLabor1;
echo "<br> score labor: $".$scoreLabor1;
echo "<br> UV Flood Cost: $".$uvFloodCost1;
echo "<br> UV Spot Cost: $".$uvSpotCost;
echo "<br> lam cost: $".$lamCost;
echo "<br> cover cost: $".$coverCost;
echo "<br> lam labor: $".$lamLabor;
echo "<br> lam Cover Labor: $".$lamCoverLabor;
echo "<br> lam Cover Cost: $".$lamCoverCost;
echo "<br> saddle labor: $".$saddleLabor;
echo "<br> saddle cost: $".$saddleCost;
echo "<br> spiral labor: $".$spiralLabor;
echo "<br> spiral cost: $".$spiralCost;
echo "<br> stack cuts: ".$numStacks;
echo "<br> white ink cost: $".$whiteCost;
echo "<br> Profit: $".(ceil(($labor1 + $rawCost1)/$margin1)-$rawCost1-$labor1);
echo "<br> Nup: ".$Nup;
echo "<br> hours: ".$laborHour;
echo "<br> price per: ".(ceil(($labor + $rawCost)/$margin)/$quantity);
echo "<br> est cutting time: ".(($cuttingLabor/HOUR)*60);
?>
</div>
-->
<div class="col-lg-6">

<?php


$total1 = (($labor1 + $rawCost1)/$margin1);
$total2 = (($labor1 + $rawCost1)/$margin2);
$total3 = (($labor1 + $rawCost1)/$margin3);

$total4 = (($labor2 + $rawCost2)/$margin1);
$total5 = (($labor2 + $rawCost2)/$margin2);
$total6 = (($labor2 + $rawCost2)/$margin3);

$total7 = (($labor3 + $rawCost3)/$margin1);
$total8 = (($labor3 + $rawCost3)/$margin2);
$total9 = (($labor3 + $rawCost3)/$margin3);


$roundedTotal1 = roundUpToAny($total1,$x=5);
$roundedTotal2 = roundUpToAny($total2,$x=5);
$roundedTotal3 = roundUpToAny($total3,$x=5);

$roundedTotal4 = roundUpToAny($total4,$x=5);
$roundedTotal5 = roundUpToAny($total5,$x=5);
$roundedTotal6 = roundUpToAny($total6,$x=5);


$roundedTotal7 = roundUpToAny($total7,$x=5);
$roundedTotal8 = roundUpToAny($total8,$x=5);
$roundedTotal9 = roundUpToAny($total9,$x=5);


$revPerHour1 = ($roundedTotal1/($runTime1+$laborHour1));
$formattedRevPerHour1 = number_format($revPerHour1, 2, '.', '');

$revPerHour2 = ($roundedTotal2/($runTime1+$laborHour1));
$formattedRevPerHour2 = number_format($revPerHour2, 2, '.', '');

$revPerHour3 = ($roundedTotal3/($runTime1+$laborHour1));
$formattedRevPerHour3 = number_format($revPerHour3, 2, '.', '');

$revPerHour4 = ($roundedTotal4/($runTime2+$laborHour2));
$formattedRevPerHour4 = number_format($revPerHour4, 2, '.', '');

$revPerHour5 = ($roundedTotal5/($runTime2+$laborHour2));
$formattedRevPerHour5 = number_format($revPerHour5, 2, '.', '');

$revPerHour6 = ($roundedTotal6/($runTime2+$laborHour2));
$formattedRevPerHour6 = number_format($revPerHour6, 2, '.', '');

$revPerHour7 = ($roundedTotal7/($runTime3+$laborHour3));
$formattedRevPerHour7 = number_format($revPerHour7, 2, '.', '');

$revPerHour8 = ($roundedTotal8/($runTime3+$laborHour3));
$formattedRevPerHour8 = number_format($revPerHour8, 2, '.', '');

$revPerHour9 = ($roundedTotal9/($runTime3+$laborHour3));
$formattedRevPerHour9 = number_format($revPerHour9, 2, '.', '');

?>



    <ul>
      <li><strong><?php echo $_POST['name']; ?></strong></li>
      <li><?php echo $Nup; ?> up </li>
      <li><strong>Quantity:</strong> <?php echo $quantity1; ?></li>
      <li><strong>Runtime: </strong> <?php echo date('z:H:i:s', $runTime1*3600); ?> </li>
      <li><strong>Est Finishing Time: </strong><?php echo date('z:H:i:s', $laborHour1*3600); ?></li>
      <li><strong>Raw Mat. Cost: $<?php echo number_format($rawCost1, 2, '.', ''); ?> </strong></li><br>
      <li><span<?php
      if (1/375 < ($runTime1 + $laborHour1)/$roundedTotal1){
	      echo ' style="color:red"';
      }
      else{
	      echo ' style="color:green"';
      }
      
      ?>>Price <?php echo "(".$preMargin1."% margin) $: ".$roundedTotal1." <br>Rev/Hour: $".$formattedRevPerHour1; ?><br> Price/Piece: $<?php echo $roundedTotal1/$quantity1;?></span></li><br>
      <li><span<?php
      if (1/375 < ($runTime1 + $laborHour1)/$roundedTotal2){
	      echo ' style="color:red"';
      }
      else{
	      echo ' style="color:green"';
      }
      
      ?>>Price <?php echo "(".$preMargin2."% margin) $: ".$roundedTotal2." <br>Rev/Hour: $".$formattedRevPerHour2; ?><br> Price/Piece: $<?php echo $roundedTotal2/$quantity1;?></li><br>
      <li><span<?php
      if (1/375 < ($runTime1 + $laborHour1)/$roundedTotal3){
	      echo ' style="color:red"';
      }
      else{
	      echo ' style="color:green"';
      }
      
      ?>>Price <?php echo "(".$preMargin3."% margin) $: ".$roundedTotal3." <br>Rev/Hour: $".$formattedRevPerHour3; ?><br> Price/Piece: $<?php echo $roundedTotal3/$quantity1;?></li><br>
    </ul>
    <ul>
      <li><strong><?php echo $_POST['name']; ?></strong></li>
      <li><?php echo $Nup; ?> up </li>
      <li> <strong>Quantity: </strong><?php echo $quantity2; ?></li>
      <li><strong>Runtime: </strong> <?php echo date('z:H:i:s', $runTime2*3600); ?> </li>
      <li><strong>Est Finishing Time: </strong><?php echo date('z:H:i:s', $laborHour2*3600); ?></li>
      <li><strong>Raw Mat. Cost: $<?php echo number_format($rawCost2, 2, '.', ''); ?> </strong></li><br>
      <li><span<?php
      if (1/375 < ($runTime2 + $laborHour2)/$roundedTotal4){
	      echo ' style="color:red"';
      }
      else{
	      echo ' style="color:green"';
      }
      
      ?>>Price <?php echo "(".$preMargin1."% margin) $: ".$roundedTotal4." <br>Rev/Hour: $".$formattedRevPerHour4; ?><br> Price/Piece: $<?php echo $roundedTotal4/$quantity2;?></li><br>
      <li><span<?php
      if (1/375 < ($runTime2 + $laborHour2)/$roundedTotal5){
	      echo ' style="color:red"';
      }
      else{
	      echo ' style="color:green"';
      }
      
      ?>>Price <?php echo "(".$preMargin2."% margin) $: ".$roundedTotal5." <br>Rev/Hour: $".$formattedRevPerHour5; ?><br> Price/Piece: $<?php echo $roundedTotal5/$quantity2;?></li><br>
      <li><span<?php
      if (1/375 < ($runTime2 + $laborHour2)/$roundedTotal6){
	      echo ' style="color:red"';
      }
      else{
	      echo ' style="color:green"';
      }
      
      ?>>Price <?php echo "(".$preMargin3."% margin) $: ".$roundedTotal6." <br>Rev/Hour: $".$formattedRevPerHour6; ?><br> Price/Piece: $<?php echo $roundedTotal6/$quantity2;?></li><br>
    </ul>
    <ul>
      <li><strong><?php echo $_POST['name']; ?></strong></li>
      <li><?php echo $Nup; ?> up </li>
      <li> <strong>Quantity: </strong><?php echo $quantity3; ?></li>
      <li><strong>Runtime: </strong> <?php echo date('z:H:i:s', $runTime3*3600) ?> </li>
      <li><strong>Est Finishing Time: </strong><?php echo date('z:H:i:s', $laborHour3*3600); ?></li>
      <li><strong>Raw Mat. Cost: $<?php echo number_format($rawCost3, 2, '.', ''); ?> </strong></li><br>
      <li><span<?php
      if (1/375 < ($runTime3 + $laborHour3)/$roundedTotal7){
	      echo ' style="color:red"';
      }
      else{
	      echo ' style="color:green"';
      }
      
      ?>>Price <?php echo "(".$preMargin1."% margin) $: ".$roundedTotal7." <br>Rev/Hour: $".$formattedRevPerHour7; ?><br> Price/Piece: $<?php echo $roundedTotal7/$quantity3;?></li><br>
      <li><span<?php
      if (1/375 < ($runTime3 + $laborHour3)/$roundedTotal8){
	      echo ' style="color:red"';
      }
      else{
	      echo ' style="color:green"';
      }
      
      ?>>Price <?php echo "(".$preMargin2."% margin) $: ".$roundedTotal8." <br>Rev/Hour: $".$formattedRevPerHour8; ?><br> Price/Piece: $<?php echo $roundedTotal8/$quantity3;?></li><br>
      <li><span<?php
      if (1/375 < ($runTime3 + $laborHour3)/$roundedTotal9){
	      echo ' style="color:red"';
      }
      else{
	      echo ' style="color:green"';
      }
      
      ?>>Price <?php echo "(".$preMargin3."% margin) $: ".$roundedTotal9." <br>Rev/Hour: $".$formattedRevPerHour9; ?><br> Price/Piece: $<?php echo $roundedTotal9/$quantity3;?></li><br>
    </ul>


