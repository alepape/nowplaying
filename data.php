<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
error_reporting(E_ERROR);

// BOOTSTRAP
include 'bootstrap.php';
// TODO: include cache + fail-over logic in here rather than in bootstrap?

// header("ALP-debug: ".json_encode($_SERVER));

$jsonObj = [];
$jsonObj["title"] = $nowTitle;
$jsonObj["artist"] = $nowArtist;
$jsonObj["pict"] = $nowPictURL;
$jsonObj["radiologo"] = $configobj['logo'];
$jsonObj["radioname"] = $configobj['name'];
$jsonObj["radiocode"] = $config;
$jsonObj["error"] = $json_error;

$json = json_encode($jsonObj);

// header("ALP-debug: loaded config = ".$configjson);
header('Content-type: application/json');
header("Content-Length: " . strlen($json));
echo $json;
	
?>