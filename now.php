<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
error_reporting(E_ERROR);

// FUNCTIONS
include 'common.php';

// LOGIC
$configfile = __DIR__ .'/now.json';
$configjson = file_get_contents($configfile);
$configobj = json_decode($configjson, true);

$config = $_GET["c"];
if ($config == "") {
    $config = $configobj['default']; // default station from file
}
$notification = $configobj['notification']; // notification URL for HA
// TODO: only notif when change (use cache mechanism)

$configfile = __DIR__ .'/'.$config.'.json';
$configjson = file_get_contents($configfile);
$configobj = json_decode($configjson, true);
$mode = $_GET["p"];
if ($mode == "true") {
	$mode = "pict";
} else if ($mode == "json") {
    $mode = "json";
} else {
    $mode = "page";
}

//header('ALP-config: '.$configjson); // no show??? why???

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => $configobj['nowURL'],
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_USERAGENT => 'Home Assistant Now Playing/0.1 (https://github.com/alepape/nowplaying)'
));

$response = curl_exec($curl);
curl_close($curl);
$obj = json_decode($response, true);
//header('ALP-debug: '.$response);

	
// result in array
if (isset($configobj['resultArray'])) {
	$obj = $obj[$configobj['resultArray']][0];
}

if (isset($configobj['mappings'])) { // normal mapping

	$nowTitle = arrayLocator($obj, $configobj['mappings']['nowTitle']);
	$nowArtist = arrayLocator($obj, $configobj['mappings']['nowArtist']);
	
	
	$nowPictURL = arrayLocator($obj, $configobj['mappings']['nowPictURL']);
	
} elseif (isset($configobj['icemappings'])) { // icecast mapping
	$nowTitle = arrayLocator($obj, $configobj['icemappings']['now'], null, $configobj['icemappings']['key'], $configobj['icemappings']['value']);
	// at this stage, the title has both the title and artist - the clean up will be done in another step

} else { // nothing?
	$nowTitle = "no valid configuration found";
	$nowArtist = "error";
	$nowPictURL = "notfound.png";
}

// overrideCover
$overrideCover = false;
if (isset($configobj['overrideCover'])) {
	if ($configobj['overrideCover']) {
		$overrideCover = true;
	}
}

// fixCase
if (isset($configobj['fixCase'])) {
	if ($configobj['fixCase']) {
		$nowTitle = ucwords(strtolower($nowTitle));
		$nowArtist = ucwords(strtolower($nowArtist));
	}
}

// check for string transforms
if (isset($configobj['transform'])) {
	foreach ($configobj['transform'] as $key => $value) {
		//echo $key." from ".$value['from']." to ".$value['to'];
		${$key} = str_replace($value['from'], $value['to'], ${$key});
	}
}

// check for string splits
if (isset($configobj['split'])) {
	foreach ($configobj['split'] as $key => $value) {
		//echo $key." from ".$value['from']." to ".$value['to'];
		$initial = ${$key};
		$result = explode($value["after"], $initial);
		${$key} = $result[0];
		${$value["target"]} = $result[1];
	}
}

header('ALP-overrideCover: '.$overrideCover);
header('ALP-nowPictURL: '.$nowPictURL);

if ($overrideCover || $nowPictURL == "") { 
	// TODO: include album data from radio when available to find better covers...
	$nowPictURL = "cover.php?t=".urlencode($nowTitle)."&a=".urlencode($nowArtist);
	header('ALP-cover: '.$nowPictURL);
}
// default cover managed by cover.php

if ($mode == "pict") { // pict mode

	header('Content-type: image/jpeg');
	//echo $picturl;
	if ($nowPictURL == "") {
		$image = file_get_contents("notfound.png");
		header('Content-type: image/png');
	} else {
		$image = file_get_contents($nowPictURL);
	}

	header("Content-Length: " . strlen($image));
	echo $image;

} else if ($mode == "json") { // JSON mode

	$jsonObj = [];
	$jsonObj["title"] = $nowTitle;
	$jsonObj["artist"] = $nowArtist;
	$jsonObj["pict"] = $nowPictURL;

	$json = json_encode($jsonObj);

	header('Content-type: application/json');
	header("Content-Length: " . strlen($json));
	echo $json;
	
} else if ($mode == "page") {

	if ($notification != "") {
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $notification,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		));
		$response = curl_exec($curl);
		curl_close($curl);	
	}
		
?>
<header>
<meta http-equiv="refresh" content="30" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="style.css">

</header>
<body>
<div id="container">
  <div id="logo">
    <img src="<?=$configobj['logo']?>" align="center">
  </div>
  <div id="now_playing" style="display: block;">
    <div id="coverdiv">
      <a href="https://open.spotify.com/search/<?=urlencode($nowTitle." ".$nowArtist)?>" target="_blank"><img src="<?=$nowPictURL?>" id="cover"></a>
    </div>
    <div id="infodiv">
		<div id="infobox">
	    	<div id="title"><?=$nowTitle?></div>
    		<div id="artist"><?=$nowArtist?></div>
		</div>
	</div>
  </div>
</div>
</body>

<?php
}
?>