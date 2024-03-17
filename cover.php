<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

// FUNCTIONS

// LOGIC

$artist = $_GET["a"];
if ($artist == "") {
    $artist = "catie lausten"; // default artist
    $artist = urlencode($artist);
}
$track = $_GET["t"];
if ($track == "") {
    $track = "man, ur not my man"; // default track
    $track = urlencode($track);
}

// TODO: URL encode / decode check - for now, assumes encoded and pass as is

$curl = curl_init();

// user agent = Application name/<version> ( contact-email )
$url = 'https://musicbrainz.org/ws/2/recording/?query=recording%3A%22'.$track.'%22%20AND%20artist%3A%22'.$artist.'%22%20AND%20status%3Aofficial%20AND%20primarytype%3Aalbum&inc=releases&fmt=json';

echo $url;

curl_setopt_array($curl, array(
  CURLOPT_URL => $url,
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
echo $response;


?>