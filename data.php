<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR);

// BOOTSTRAP
include 'bootstrap.php';

$jsonObj = [];
$jsonObj["title"] = $nowTitle;
$jsonObj["artist"] = $nowArtist;
$jsonObj["pict"] = $nowPictURL;
$jsonObj["radiologo"] = $configobj['logo'];

$json = json_encode($jsonObj);

header('Content-type: application/json');
header("Content-Length: " . strlen($json));
echo $json;
	
?>