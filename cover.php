<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
error_reporting(E_ERROR);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");

// SESSION FOR CACHED COVER
session_start();

// FUNCTIONS
include 'common.php';

// LOGIC

$artist = $_GET["a"];
$artist = rawurlencode(urldecode($artist));
$track = $_GET["t"];
$track = rawurlencode(urldecode($track));
$force = $_GET["f"];
if ($force == "") {
  $force = false; // default
} else {
  $force = true;
}

$sessionID = $artist.'+'.$track;

if (($_SESSION['lastrequest'] == $sessionID) && $force == false) {
  // I HAVE CACHE, let's use it
  $picturl = $_SESSION['lastcover'];
  header('ALP-status: cache');  
  header('ALP-lastrequest-get: '.$sessionID);
  header('ALP-lastcover: '.$picturl);
} else {
  // NO CACHE, let's call the APIs...
  $_SESSION['lastrequest'] = $sessionID;
  header('ALP-status: live');
  header('ALP-lastrequest-set: '.$sessionID);

  $curl = curl_init();

  // user agent = Application name/<version> ( contact-email )
  $url = 'https://musicbrainz.org/ws/2/recording/?query=recording%3A%22'.$track.'%22%20AND%20artist%3A%22'.$artist.'%22%20AND%20status%3Aofficial%20AND%20primarytype%3Aalbum&inc=releases&fmt=json';

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

  $curl_errno = curl_errno($curl);
  if ($curl_errno > 0) {
    $response = "";
    $header['ALP-musicbrainz-error: '.$curl_errno];
    curl_close($curl);  // closing when failure
  } else {
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if ($httpCode == 200) {
      $musicbrainz = json_decode($response, true);
      curl_close($curl);  // closing when success
    }
  }

  $releasecandidates = [];
  $releasecandidates_planb = [];
  $mb_count = 0;

  foreach ($musicbrainz["recordings"] as $recording) {
    foreach ($recording["releases"] as $release) {
      $mb_count++;
      // if ($release["artist-credit"][0]["name"] == urldecode($artist)) {
      //   $releasecandidates[] = $release;
      //   header('ALP-direct-hit: true');
      //   continue;
      // }
      // commented because too many false positive (compilation from main artist)
      if ($release["date"] == "") {
        continue;
        // release w/o dates - can't sort them + means not a lot of data - bad
      }
      if ($release["artist-credit"][0]["name"] == "Various Artists") {
        continue;
        // compilations - we no like...
      } // is object array - need better filter
      if (is_array($release["release-group"]["secondary-types"])) {
        if (in_array("Compilation", $release["release-group"]["secondary-types"])) {
          $releasecandidates_planb[] = $release;
          continue;
          // compilations - we no like... but keeping as plan b
        }
      }
      $releasecandidates[] = $release;
    }
  }
  header('ALP-musicbrainz-count: '.$mb_count);
  header('ALP-candidates: '.count($releasecandidates));
  header('ALP-candidates-b: '.count($releasecandidates_planb));

  if (count($releasecandidates) == 0) {
    $releasecandidates = $releasecandidates_planb;
    // switching to plan B if no proper album found
  }

  usort($releasecandidates, "cmp");
  // trying to find the oldest release... 

  $response = ""; // in case no single hit, that'll help us skip the next steps

  foreach ($releasecandidates as $candidate) {
    $coverurl = "https://coverartarchive.org/release/".$candidate["id"];
    header('ALP-pict-id: '.$candidate["id"]);

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $coverurl,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 4, // 4 sec
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
    ));

    $response = curl_exec($curl);
    $curl_errno = curl_errno($curl);
    if ($curl_errno > 0) {
      $response = "";
      $header['ALP-coverartarchive-error: '.$curl_errno];
    } else {
      $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      if ($httpCode == 200) {
        curl_close($curl);  // closing when success
        break;
      }
    }

    curl_close($curl);  // closing when failure
  }

  if ($response != "") {
    $coverapi = json_decode($response, true);
    if (isset($coverapi["images"][0]["thumbnails"]["large"])) {
      $picturl = $coverapi["images"][0]["thumbnails"]["large"]; // TODO: check if front or back
      $pict_type = "jpeg";
      if (endsWith($picturl, ".png")) { // hopefully enough - could use the file_get_content below to check...
        $pict_type = "png";
      }
      // caching the pict as we most certainly have one here...
      $cache_file = __DIR__ .'/cache/cover.'.$pict_type;
      $cache_data = file_get_contents($picturl);
      file_put_contents($cache_file, $cache_data); 
      $_SESSION['lastcover'] = 'cache/cover.'.$pict_type; // full URL not needed as we are a proxy :)
      // and using hte cached version even the first time ;)
      $picturl = $_SESSION['lastcover'];
    }
  } else {
    header('ALP-status: failed');
    $picturl = "";
  }
}

if ($picturl == "") {
  header('Content-type: image/png');
  $image = file_get_contents("picts/notfound.png");
} else {
  header('Content-type: image/'.$pict_type);
  $image = file_get_contents($picturl);
}

header("Content-Length: " . strlen($image));
echo $image;
?>