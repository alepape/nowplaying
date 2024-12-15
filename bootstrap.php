<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
error_reporting(E_ERROR);

// FUNCTIONS
include 'common.php';
include 'config.php';

// overrideVerb
$method = "GET";
if (isset($configobj['overrideVerb'])) {
	$method = $configobj['overrideVerb'];
}

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => $configobj['nowURL'], // depends on the radio
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => $method,
  CURLOPT_USERAGENT => 'Home Assistant Now Playing/0.1 (https://github.com/alepape/nowplaying)'
));

// TODO: deal with empty responses (ex: cache last one) - HA sensor tends to refresh too often
// and I get throttled (at least on classic)

$response = curl_exec($curl);
curl_close($curl);
$obj = json_decode($response, true);

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
	$nowTitle = "";
	$nowArtist = ""; // keep empty for next step
	$nowPictURL = "notfound.png";
	header("ALP-error: no valid configuration found");
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

// at this stage - title and artist should be known - let's clean them in case the process failed above
if (($nowTitle == "") || !isset($nowTitle)) {
	$nowTitle = "";
}
if (($nowArtist == "") || !isset($nowArtist)) {
	$nowArtist = "";
}

// cache the latest good response
$cache_file = __DIR__ .'/cache/last.json';
// one validation option - we should have nowArtist now... 
// TODO: find a better one based on curl response - but might need a error detection per radio...
if (($nowArtist != "") && ($nowTitle != "")) {
	// save last payload to file
	$jsonObj = [];
	$jsonObj["title"] = $nowTitle;
	$jsonObj["artist"] = $nowArtist;

	// caching the pict as we most certainly have one here...
	$cache_data = json_encode($jsonObj);
	file_put_contents($cache_file, $cache_data); 

} else {
	// read last good payload
	$cache_data = file_get_contents($cache_file);
	$jsonObj = json_decode($cache_data);
	$nowTitle = $jsonObj["title"];
	$nowArtist = $jsonObj["artist"];
}

// default cover managed by cover.php
// except if artist & track missing - then manual here...
if ($overrideCover || $nowPictURL == "") { 
	// TODO: include album data from radio when available to find better covers...

	// check if config has a external URL override (useful when reverse proxy)
	if ($force_ext_path != "") {
		$urlbase = $force_ext_path;
	} else {
		// ok, nothing, we try & build full URL to include https despite the redirects
		$urlbase = $_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'].":".$_SERVER['SERVER_PORT'];
	}
	$filename = basename($_SERVER['REQUEST_URI']);
	$path = str_replace($filename, '', $_SERVER['REQUEST_URI']);
	$urlbase .= $path;

	// check I have something to search for...
	if (($nowArtist == "") || ($nowTitle == "")) {
		header("ALP-debug: ".$respons); // tell why it was empty
		// $nowPictURL = $urlbase."picts/notfound.png"; // no need to go through the motions w/ cover.php
		// NOTE: this fails CORS for some reason - let have a check in cover.php instead
	}
	$nowPictURL = $urlbase."cover.php?t=".urlencode($nowTitle)."&a=".urlencode($nowArtist); // TODO: add hostname from PHP context

	header('ALP-cover: '.$nowPictURL);
}

?>