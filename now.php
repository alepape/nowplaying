<?php

// FUNCTIONS

function arrayLocator($array, $locator) {
	$addr = explode('.', $locator);

	foreach($addr as $i){
	    if(!isset($tmp)){
	        $tmp = &$array[$i];
	    } else {
	        $tmp = $tmp[$i];
	    }
	}
	return $tmp;
}

// LOGIC

$config = $_GET["c"];
if ($config == "") {
    $config = "fip"; // default station
}
$configfile = __DIR__ .'/'.$config.'.json';
$configjson = file_get_contents($configfile);
$configobj = json_decode($configjson, true);

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
));

$response = curl_exec($curl);

curl_close($curl);

$obj = json_decode($response, true);

// $nowTitle = $obj["now"]["firstLine"];
// $nowArtist = $obj["now"]["secondLine"];
// $nowPictURL = $obj["now"]["cover"];

$nowTitle = arrayLocator($obj, $configobj['mappings']['nowTitle']);
$nowArtist = arrayLocator($obj, $configobj['mappings']['nowArtist']);
$nowPictURL = arrayLocator($obj, $configobj['mappings']['nowPictURL']);

//echo $nowURL;

//header('Location: '.$nowURL);
?>
<header>
<meta http-equiv="refresh" content="30" />
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
body {background: #1c1c1c}
h2 {
  color: white;
  margin-top: 20px;
}
body {
  margin: 0px;
}
.hass-text {
  color: rgb(220, 220, 220);
  font-family: Roboto, Noto, sans-serif;
  font-size: 22px;
  letter-spacing: -0.012em;
  /*line-height: 32px;*/
  padding: 20px;
  display: block;
  margin-block: 0px;
  font-weight: normal;
}

@media screen and (max-width: 500px) {
  img.player {
    width: 200px;
    float: left; 
    margin-right: 20px;
  }
}
@media screen and (min-width: 500px) {
  img.player {
    width: 100%;
    /* margin-bottom: 20px; */
  }
  .hass-text {
    padding-bottom: 0px;
    padding-top: 10px;
    font-size: 28px;
  }
}
</style>
</header>
<body>
<a href="https://www.deezer.com/search/<?=urlencode($nowTitle." ".$nowArtist)?>" target="_blank"><img class="player" src="<?=$nowPictURL?>"/></a>
<div>
  <h2 class="hass-text"><b><?=$nowTitle?></b></h2>
  <h2 class="hass-text"><?=$nowArtist?></h2>
</div>
</body>