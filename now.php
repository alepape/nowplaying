<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR);

// FUNCTIONS

function arrayLocator($array, $locator, $dictindex = 0, $dictkey = "", $dictvalue = "") {
	$addr = explode('.', $locator);

	foreach($addr as $i){
		if(!isset($tmp)){
	        $tmp = &$array[$i];
	    } else if (isset($tmp[$i])) {
	        $tmp = $tmp[$i];
	    }
	}
	//echo gettype($tmp);
	if (gettype($tmp) == "array") {
		// check if using key or index
		if ($dictkey != "") {
			foreach($tmp as $stream) {
				if ($stream[$dictkey] == $dictvalue) {
					$tmp = $stream[$i];
				}
			}
		} else {
			$tmp = $tmp[$dictindex][$i];
		}
	}
	return $tmp;
}

// LOGIC
$configfile = __DIR__ .'/now.json';
$configjson = file_get_contents($configfile);
$configobj = json_decode($configjson, true);

$config = $_GET["c"];
if ($config == "") {
    $config = $configobj['default']; // default station from file
}
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
	
	// fixCase
	if (isset($configobj['fixCase'])) {
		if ($configobj['fixCase']) {
			$nowTitle = ucwords(strtolower($nowTitle));
			$nowArtist = ucwords(strtolower($nowArtist));
		}
	}
	
	// overrideCover
	$overrideCover = false;
	if (isset($configobj['overrideCover'])) {
		if ($configobj['overrideCover']) {
			$overrideCover = true;
		}
	}
	
	$nowPictURL = arrayLocator($obj, $configobj['mappings']['nowPictURL']);
	if ($overrideCover || $nowPictURL == "") { 
		// TODO: include album data from radio when available to find better covers...
		$nowPictURL = "cover.php?t=".urlencode($nowTitle)."&a=".urlencode($nowArtist);
	}
	// default cover managed by cover.php
	
} elseif (isset($configobj['icemappings'])) { // icecast mapping

	$nowTitle = arrayLocator($obj, $configobj['icemappings']['now'], null, $configobj['icemappings']['key'], $configobj['icemappings']['value']);
	$nowArtist = "";
	$nowPictURL = "notfound.png";

} else { // nothing?
	$nowTitle = "no valid configuration found";
	$nowArtist = "error";
	$nowPictURL = "notfound.png";
}

// check for string transforms
if (isset($configobj['transform'])) {
	foreach ($configobj['transform'] as $key => $value) {
		//echo $key." from ".$value['from']." to ".$value['to'];
		${$key} = str_replace($value['from'], $value['to'], ${$key});
	}
}

if ($mode == "page") {

	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => 'http://rpinet:8123/api/webhook/update-radio-media-jk88hlBW3PeOgtzzDMiCoA-t',
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
		
?>
<header>
<meta http-equiv="refresh" content="30" />
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
	body { 
		font-family: Roboto, Noto, sans-serif;
		font-size: 5vh;
		background: #333;
		color: #ccc;
	}
	#container {
		margin: 10px;
		text-align: center;
		position: relative;
	}

	#now_playing {
		width: 100%;
		margin:auto;
		text-align:center;
		margin-top: 20px;
		cursor: pointer;
		clear: left;
	}
	#title {
		margin-top: 8px;
	}
	#artist {
		font-size: 70%;
	}
	#album {
		font-size: 50%;
	}
	#cover {
		height: 50vh;
		width: auto;
    	max-height: 400px;
	}

	@media (max-height: 340px) {
		#cover {
			display: none;
		}
		#container {
			font-size: 200%;
		}
	}

	#controls {
		margin-top: 4vh;
		margin-bottom: 4vh;
		font-size: 60%;
	}

	#logo img {
		height: auto;
		max-height: 15vh;
	}

	@media screen and (width: 1024px) and (height: 600px) { /* google nest hub */
		body {
			margin: 0px;
		}
		#container {
			margin: 0px;
			text-align: right;
			position: relative;
		}
		#now_playing {
			margin-top: 0px;
		}
		#cover {
			height: 510px;
			max-height: 510px;
		}
		#coverdiv {
			float: left;
		}
		#infodiv {
			float: left;
        	display: flex;
        	height: 510px;
		}
		#infobox {
        	margin: auto;
	        text-align: left;
    	    padding-left: 30px;
        	font-size: 50px;
        	width: 484px;
		}
		#title {
			margin-top: 0px;
			color: white;
		}
		#artist {
			margin-top: 20px;
			font-size: 70%;
		}
		#artist::before {
  			content: 'by: ';
		}
	}

</style>
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
} else if ($mode == "pict") { // pict mode

//echo $picturl;
if ($nowPictURL == "") {
	$image = file_get_contents("notfound.png");
} else {
	$image = file_get_contents($nowPictURL);
}

header('Content-type: image/jpeg');
header("Content-Length: " . strlen($image));
echo $image;

} else if ($mode == "json") { 

	$jsonObj = [];
	$jsonObj["title"] = $nowTitle;
	$jsonObj["artist"] = $nowArtist;
	$jsonObj["pict"] = $nowPictURL;

	$json = json_encode($jsonObj);

	header('Content-type: application/json');
	header("Content-Length: " . strlen($json));
	echo $json;
	
}
?>
