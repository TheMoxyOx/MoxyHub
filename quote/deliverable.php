<?php

class Deliverable {
	private $name, $type, $width, $length, $paper, $cover, $quantity, $binding, $spiralColor, $rounded, $uv, $drill, $lam, $score, $perf, $white, $nup, $glue, $saddle;

function __construct() {
}

//setfunctions
function set_name($arg) {
	$name = $arg;
}

function set_type($arg) {
	$type = $arg;
}

function set_width($arg) {
	$width = $arg
}

function set_length($arg) {
	$length = $arg
}

function set_paper($arg) {
	$paper = $arg
}

function set_cover($arg) {
	$cover = $arg
}

function set_quantity($arg) {
	$quantity = $arg
}

function set_binding($arg) {
	$binding = $arg
}

function set_spiralColor($arg) {
	$spiralColor = $arg
}

function set_glue($arg) {
	$glue = $arg
}

function set_saddle($arg) {
	$saddle = $arg
}

function set_rounded($arg) {
	$rounded = $arg
}

function set_uv($arg) {
	$uv = $arg
}

function set_drill($arg) {
	$drill = $arg
}

function set_lam($arg) {
	$lam = $arg
}

function set_score($arg) {
	$score = $arg
}

function set_perf($arg) {
	$perf = $arg
}

function set_white($arg) {
	$white = $arg
}

function set_nup($arg) {
	$nup = $arg
}

function print_deliverable() {
	
	/* printing out specs for the individual deliverable */
	
	echo "Deliverable name: ".$name;
	echo "Deliverable type: ".$type;
	echo "size: ".$length." x ".$width;
	echo "Paper: ".$paper;
	if ($binding == 1) {
	echo "Cover paper: ".$cover;
	echo "Binding: ".$saddle;
	echo "Binding: ".$spiralColor;
	echo "Binding: ".$glue;
	}
	echo "Quantity: ".$quantity;
	echo "Finishing Options:";
	if ($rounded) {
	echo "Rounded Corners";
	}
	if ($uv) {
	echo "UV Coating";
	}
	echo "Drilled: ".$drill;
	echo "Laminated: ".$lam;
	echo "Scored: ".$score;
	echo "Perforated: ".$perf;
	echo "White Ink: ".$white;
	echo "Number up: ".$nup;
	
}


}