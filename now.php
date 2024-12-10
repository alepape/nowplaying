<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
error_reporting(E_ERROR);

// this page is just for compatibility purposes...
// use index.php for web
// use pict.php for pict
// use data.php for JSON

$mode = $_GET["p"];
if ($mode == "true") {
	$mode = "pict";
} else if ($mode == "json") {
    $mode = "json";
} else {
    $mode = "page";
}

$config = $_GET["c"];
$param = "";
if ($config != "") {
	$param = "?c=".$config;
}

// BOOTSTRAP
include 'bootstrap.php';

if ($mode == "pict") { // pict mode

	header("Location: pict.php".$param);
	die();

} else if ($mode == "json") { // JSON mode

	header("Location: data.php".$param);
	die();
	
} else if ($mode == "page") {

	header("Location: index.php".$param);
	die();

}
?>