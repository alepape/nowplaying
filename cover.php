<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
error_reporting(E_ERROR);

// SESSION FOR CACHED COVER
session_start();

// FUNCTIONS
function cmp($a, $b) {
  $date_a = substr($a["date"], 0, 4);
  $date_b = substr($b["date"], 0, 4);

  return strcmp($date_a, $date_b);
}

// LOGIC

$artist = $_GET["a"];
if ($artist == "") {
    $artist = "Stevie Wonder"; // default artist
}
$artist = urlencode($artist);
$track = $_GET["t"];
if ($track == "") {
    $track = "Superstition"; // default track
}
$track = urlencode($track);

$sessionID = $artist.$track;

if ($_SESSION['lastrequest'] == $sessionID) {
  // I HAVE CACHE, let's use it
  $picturl = $_SESSION['lastcover'];
  header('ALP-status: cache');
} else {
  // NO CACHE, let's call the APIs...
  $_SESSION['lastrequest'] = $sessionID;

  $curl = curl_init();

  // user agent = Application name/<version> ( contact-email )
  $url = 'https://musicbrainz.org/ws/2/recording/?query=recording%3A%22'.$track.'%22%20AND%20artist%3A%22'.$artist.'%22%20AND%20status%3Aofficial%20AND%20primarytype%3Aalbum&inc=releases&fmt=json';

  //echo $url;

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
  $musicbrainz = json_decode($response, true);

  curl_close($curl);
  //echo $response;

  $releasecandidates = [];

  foreach ($musicbrainz["recordings"] as $recording) {
    foreach ($recording["releases"] as $release) {
      if ($release["date"] != "") {
        $releasecandidates[] = $release;
      }
    }
  }
  //echo json_encode($releasecandidates);
  usort($releasecandidates, "cmp");
  //echo "<br/><br/>------------------------------<br/><br/>";
  //echo json_encode($releasecandidates);

  foreach ($releasecandidates as $candidate) {
    $coverurl = "https://coverartarchive.org/release/".$candidate["id"];
    //echo $release["id"]."<br/>";
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $coverurl,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
    ));

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if ($httpCode == 200) {
      break;
    }

    curl_close($curl);  
  }

  $coverapi = json_decode($response, true);
  $picturl = $coverapi["images"][0]["thumbnails"]["large"]; // TODO: check if front or back
  $_SESSION['lastcover'] = $picturl;
  // TODO: could we cache the pict itself? worth it?
  header('ALP-status: live');
}

//echo $picturl;
if ($picturl == "") {
  $image = file_get_contents("notfound.png");
} else {
  $image = file_get_contents($picturl);
}

header('Content-type: image/jpeg');
header("Content-Length: " . strlen($image));
echo $image;
?>