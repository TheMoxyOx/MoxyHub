<?php
include ('constants.php');


/*---------------------------------------
determining the optimal number of products that can be imposed per sheet
returns Nup, modifies $vertCuts and $horizCuts by reference
to help with cutting labor later on down the flow
*/
function getNup ($paperSize, $prodSize, &$vertCuts, &$horizCuts) {
	//Determine Nhoriz
	$NhorizL = $paperSize[1] / $prodSize[1];
	$NhorizW = $paperSize[0] / $prodSize[0];
	//Getting int value
	$NhorizL = intval($NhorizL);
	$NhorizW = intval($NhorizW);
	$Nhoriz = $NhorizL * $NhorizW;
	
	//Determine Nvert
	$NvertL = $paperSize[0] / $prodSize[1];
	$NvertW = $paperSize[1] / $prodSize[0];
	//Getting int value
	$NvertL = intval($NvertL);
	$NvertW = intval($NvertW);
	$Nvert = $NvertL * $NvertW;
	
	//Determing highest Nup
	if ($Nhoriz >= $Nvert){
		$Nup = $Nhoriz;
		$vertCuts = $NhorizW;
		$horizCuts = $NhorizL;
		return $Nup;
	}
	else {
		$Nup = $Nvert;
		$vertCuts = $NvertL;
		$horizCuts = $NvertW;
		return $Nup;
	}
	
}
/*---------------------------------------
TODO fill this function with codey goodness
*/
function getCover () {

}

/*---------------------------------------
takes in paper size as a string formatted "13 x 19"
and explodes it into an array.
subtracts margins defined in constants.php
then returns array with [0] being width
and [1] being length
*/
function parseSize ($size) {
	if ($size[0]!="*"){
	$paperSize = explode(" x ", $size);
	$paperSize[0] -= EDGEMARGIN;
	$paperSize[1] -= GRIPMARGIN;
	return $paperSize;
	}
}

/*---------------------------------------
adding bleed to product size
*/
function getBleed ($prodSize) {
	if ($_POST["bleed"] == "bleed") {
		$prodSize[1] += .25;
		$prodSize[0] += .25;
		return $prodSize;
	}	
	else {
		return $prodSize;
	}
}

/*---------------------------------------
getting total number of sheets used in the job
returns:  total sheets
*/
function getTotalSheets ($Nup, $quantity) {
	return ceil($quantity / $Nup);	
}

/*---------------------------------------
calculates the total number of sheets if the notepad option is checked
also adds appropriate labor for binding
*/
function notepad ($Nup, $quantity, &$labor, &$totalSheets, &$pages) {
	if ($_POST["notepad"] == "notepad") {
		$pages = $_POST["pages"];
		$totalSheets = (( $quantity / $Nup) * $pages);
		$labor += ( HOUR * .5);
	}
}

/*---------------------------------------
calculates the total number of sheets if the book option is checked
*/
function book ($Nup, $quantity, &$totalSheets, &$pages) {
	if ($_POST["book"] == "book"){
	$pages = $_POST["pages"];
	$spreads = ceil($pages / 4);
	$totalSheets = ($spreads / $Nup) * $quantity;
	}
	return $spreads;
}


/*---------------------------------------
calculates click costs based on color selection
*/
function getClickCost ($color, $totalSheets) {
	switch ($color){
		case "4/4":
			$clickCost = ($totalSheets * .17);
			$color = 44;
			break;
		
		
		case "4/1":
			$clickCost = ($totalSheets * .09);
			$color = 41;
			break;
		
		
		case "4/0":
			$clickCost = ($totalSheets * .085);
			$color = 40;
			break;
		
		case "1/1":
			$clickCost = ($totalSheets * .01);
			$color = 11;
			break;
		
		case "1/0":
			$clickCost = ($totalSheets * .005);
			$color = 10;
			break;
		
		}
		return $clickCost;
}


/*---------------------------------------
calculates raw mat cost based on paper price, click cost and total sheets
*/
function getRawMatCost ($totalSheets, $price, $clickCost) {
	$rawCost = (($totalSheets * $price) + $clickCost);
	return $rawCost;
}



/*---------------------------------------
calculates cost for saddle stitching
currently: 1200/hour and $.01 per unit
*/
function getSaddleCost (&$labor, &$rawCost, $quantity) {
if ($_POST["saddle"] == "saddle") {
	$saddleLabor = (($quantity/SADDLEPERHOUR) * HOUR);
	$saddleCost = ($quantity * STAPLE);
	}
else {
	$saddleLabor = 0;
	$saddleCost = 0;
	}
$labor += $saddleLabor;
$rawCost += $saddleCost;
}


/*---------------------------------------
calculates cost for spiral binding
currently: 5min per unit
spiral cost in constants.php
*/
function getSpiralCost (&$labor, &$rawCost, $quantity) {
if ($_POST["spiral"] == "spiral") {
		$spiralLabor = (HOUR * .08333) * $quantity;
		$spiralCost = ( $quantity * SPIRALCOST);
	}
else {
	$spiralLabor = 0;
	$spiralCost = 0;
	}
$labor += $spiralLabor;
$rawCost += $spiralCost;
}


/*---------------------------------------
calculates modified sheet thickness
used to help calculate cutting labor
*/
function getSheetThickness ($class, $totalSheets) {
	if ($class == 1){
		$modSheets = $totalSheets / 3;
	}
	else if ($class == 2){
			$modSheets = $totalSheets;
	}
	else if ($_POST["lam"] == "lam") {
	
			$modSheets = $totalSheets * 2;
	}
	else {
		$modSheets = $totalSheets * 2;
	}
	return $modSheets;
}


/*---------------------------------------
calculates cost of the booklet cover
added to $rawCost
*/
function getBookletCoverCost ($Nup, $quantity, $coverPrice, &$rawCost) {
	if ($_POST["book"] == "book") {
		$coverCost = (($quantity / $Nup) * $coverPrice);
	}
$rawCost += $coverCost;
}


/*---------------------------------------
calculates cutting labor
currently: 5min setup, 30sec per cut
*/
function getCuttingCost ($modSheets,  &$labor, &$numStacks, $vertCuts, $horizCuts, $Nup) {

//Determine number of stacks that have to be cut
$numStacks = ceil($modSheets/250);

//setup
$cuttingLabor = HOUR*.0833;

//Determine number of cuts needed
$numCuts = (4 + $vertCuts + $horizCuts);

//labor per stack
$cuttingLabor += ((($numCuts*$numStacks)*.004333)*HOUR);

$labor += $cuttingLabor;

//setting new number of stacks post-cutting
$numStacks = $numStacks*$Nup;

}


/*---------------------------------------
calculates rounding labor
currently: 5min setup, 30 sec per stack
*/
function getRoundingCost (&$labor, $numStacks) {
	if ($_POST["round"] == "round"){
		$roundingLabor = HOUR*.0833;
		$roundingLabor += (($numStacks * .004152)*HOUR);
	
	}
	else {
		$roundingLabor = 0;
	}
$labor += $roundingLabor;
}



/*---------------------------------------
calculates score/perf labor
currently: 10min setup, 5sec per piece
*/
function getScoreCost (&$labor, $quantity) {
	if ($_POST["score"] == "score") {
		$scoreLabor = (HOUR * .16);
		$scoreLabor += (($quantity * .000833) * HOUR);
	}
else {
	$scoreLabor = 0;
	}
$labor += $scoreLabor;
}


/*---------------------------------------
calculates UV spot cost
currently: $35 setup, $0.78 per sheet
*/
function getUVSpotCost (&$rawCost, $totalSheets) {
	if($_POST["uvSpot"] == "uvSpot") {
		$uvSpotCost = UVSPOTSETUP;
		$uvSpotCost += ($totalSheets * UVSPOTCOST);
	}
else {
	$uvSpotCost = 0;
	}
$rawCost += $uvSpotCost;
}


/*---------------------------------------
calculates UV flood cost
currently: $15 setup, $0.08 per sheet
*/
function getUVFloodCost (&$rawCost, $totalSheets) {
	if ($_POST["uvFlood"] == "uvFlood") {
		$itemUVFlood = 1;
		$uvFloodCost = UVFLOODSETUP;
		$uvFloodCost += ($totalSheets * UVFLOODCOST);
	}
else {
	$uvFloodCost = 0;
	}

$rawCost += $uvFloodCost;
}


/*---------------------------------------
calculates Drill cost
currently: 5min setup, 1min per stack
*/
function getDrillCost ($numStacks, &$labor) {
	if ($_POST["drill"] == "drill"){
		$drillingLabor = HOUR * DRILLSETUP;
		$drillingLabor += (($numStacks * DRILLTIME)*HOUR);
	}
else{
	$drillingLabor = 0;
	}
$labor += $drillingLabor;
}



/*---------------------------------------
calculates Folding cost
currently: 15min setup, 1.5sec per piece
*/
function getFoldCost ($quantity, &$labor) {
	if ($_POST["fold"] == "fold"){
		$foldLabor = HOUR * FOLDSETUP;
		$foldLabor += (($quantity * FOLDTIME) * HOUR);
	}
else {
	$foldLabor = 0;
	}
$labor += $foldLabor;
}


/*---------------------------------------
calculates Lam cost
currently: $0.31/sheet, 1.5min per sheet
*/
function getLamCost ($totalSheets, &$rawCost, &$labor, $quantity, $Nup) {
	if ($_POST["lam"] == "lam") {
		$lamCost = 0;
		$lamCost += ($totalSheets * LAMCOST);
	}
	else {
		$lamCost = 0;
		}
$rawCost += $lamCost;


// if booklet + lam
	if ($_POST["lamcover"] == "lamcover") {
		$lamCoverLabor = 0;
		$lamCoverLabor += (( $quantity / $Nup ) * LAMTIME * HOUR );
		$lamCoverCost = 0;
		$lamCoverCost += ($quantity * LAMCOST);
	}
	else {
		$lamCoverLabor = 0;
		$lamCoverCost = 0;
	}
$labor += $lamCoverLabor;
$rawCost += $lamCoverCost;


//Determine Lam Labor
	if ($_POST["lam"] == "lam") {
		$lamLabor = 0;
		$lamLabor += ((($totalSheets/4) * LAMTIME) * HOUR);
	}
	else {
		$lamLabor = 0;
	}
$labor += $lamLabor;
}


/*---------------------------------------
calculates White cost
currently: $0.05/sheet,
*/
function getWhiteCost ($totalSheets, &$rawCost) {
	if ($_POST["white"] == "white"){
		$whiteCost = 0;
		$whiteCost += ($totalSheets * WHITECOST);
	}
else {
	$whiteCost = 0;
	}
$rawCost += $whiteCost;

}



//------*misc math functions*--------
function roundUpToAny($n,$x=5) {
	return round(($n+$x/2)/$x)*$x;
}

/*
rounds labor up to the nearest 1/4 hour
*/
function roundLabor($labor) {
	//converts labor into a time
	$laborHour = ($labor / HOUR);
	//rounds labor up to 1/4 hour
	$laborHour = roundUpToAny($laborHour,$x=.25);
	//converts time value back into dollar value
	$labor = ($laborHour * HOUR);
	
	return $labor;
}


/* 
calculate run time
*/
function calcRunTime($totalSheets, $color, $class) {
	if ($class == 1) {
		switch ($color){
			case "4/4":
				$runTime = ($totalSheets / 1000);
				break;
			
			
			case "4/1":
				$runTime = ($totalSheets / 1600);
				break;
			
			
			case "4/0":
				$runTime = ($totalSheets / 2000);
				break;
			
			case "1/1":
				$runTime = ($totalSheets / 4000);
				break;
			
			case "1/0":
				$runTime = ($totalSheets / 8000);	
				break;
		}
	}
	if ($class == 2) {
		switch ($color){
			case "4/4":
				$runTime = ($totalSheets / 666);
				break;
			
			
			case "4/1":
				$runTime = ($totalSheets / 888);
				break;
			
			
			case "4/0":
				$runTime = ($totalSheets / 1333);
				break;
			
			case "1/1":
				$runTime = ($totalSheets / 1333);
				break;
			
			case "1/0":
				$runTime = ($totalSheets / 2666);	
				break;
		}
	}
	if ($class == 3) {
		switch ($color){
			case "4/4":
				$runTime = ($totalSheets / 666);
				break;
			
			
			case "4/1":
				$runTime = ($totalSheets / 888);
				break;
			
			
			case "4/0":
				$runTime = ($totalSheets / 1333);
				break;
			
			case "1/1":
				$runTime = ($totalSheets / 1333);
				break;
			
			case "1/0":
				$runTime = ($totalSheets / 2666);	
				break;
		}		

}
return $runTime;

}














?>











