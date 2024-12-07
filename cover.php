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

function endsWith( $haystack, $needle ) {
  $length = strlen( $needle );
  if( !$length ) {
      return true;
  }
  return substr( $haystack, -$length ) === $needle;
}

// LOGIC

$artist = $_GET["a"];
if ($artist == "") {
    $artist = "Stevie Wonder"; // default artist
}
$artist = rawurlencode(urldecode($artist));
$track = $_GET["t"];
if ($track == "") {
    $track = "Superstition"; // default track
}
$track = rawurlencode(urldecode($track));
$force = $_GET["f"];
if ($force == "") {
  $force = false; // default
} else {
  $force = true;
}

$sessionID = $artist.$track;

if (($_SESSION['lastrequest'] == $sessionID) && $force == false) {
  // I HAVE CACHE, let's use it
  $picturl = $_SESSION['lastcover'];
  header('ALP-lastrequest-get: '.$sessionID);
  header('ALP-lastcover: '.$picturl);
  header('ALP-status: cache');  
} else {
  // NO CACHE, let's call the APIs...
  $_SESSION['lastrequest'] = $sessionID;
  header('ALP-lastrequest-set: '.$sessionID);

  $curl = curl_init();

  // user agent = Application name/<version> ( contact-email )
  $url = 'https://musicbrainz.org/ws/2/recording/?query=recording%3A%22'.$track.'%22%20AND%20artist%3A%22'.$artist.'%22%20AND%20status%3Aofficial%20AND%20primarytype%3Aalbum&inc=releases&fmt=json';

  header('ALP-musicbrainz-url: '.$url);

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
  //header('ALP-musicbrainz: '.$response);

  $musicbrainz = json_decode($response, true);

  curl_close($curl);
  //echo $response;

  $releasecandidates = [];
  $mb_count = 0;

  foreach ($musicbrainz["recordings"] as $recording) {
    foreach ($recording["releases"] as $release) {
      $mb_count++;
      // if ($release["artist-credit"][0]["name"] == urldecode($artist)) {
      //   $releasecandidates[] = $release;
      //   header('ALP-direct-hit: true');
      //   continue;
      // }
      if ($release["date"] == "") {
        continue;
      }
      if ($release["artist-credit"][0]["name"] == "Various Artists") {
        continue;
      } // is object array - need better filter
      if (is_array($release["release-group"]["secondary-types"])) {
        if (in_array("Compilation", $release["release-group"]["secondary-types"])) {
          continue;
        }
      }
      $releasecandidates[] = $release;
    }
  }
  header('ALP-musicbrainz-count: '.$mb_count);
  header('ALP-candidates: '.count($releasecandidates));

  //echo json_encode($releasecandidates);
  //usort($releasecandidates, "cmp");
  //echo "<br/><br/>------------------------------<br/><br/>";
  //echo json_encode($releasecandidates);

  foreach ($releasecandidates as $candidate) {
    $coverurl = "https://coverartarchive.org/release/".$candidate["id"];
    header('ALP-pict-id: '.$candidate["id"]);

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

  $pict_type = "jpeg";
  if (endsWith($picturl, ".png")) {
    $pict_type = "png";
  }

  // TODO: could we cache the pict itself? worth it?
  if ($picturl != "") {
    $cache_file = __DIR__ .'/cache/cover.'.$pict_type;
    $cache_data = file_get_contents($picturl);
    file_put_contents($cache_file, $cache_data); 
    $_SESSION['lastcover'] = 'cache/cover.'.$pict_type;
  }
  header('ALP-status: live');
}

//echo $picturl;
if ($picturl == "") {
  $image = file_get_contents("notfound.png");
  header('Content-type: image/png');
} else {
  header('Content-type: image/'.$pict_type);
  $image = file_get_contents($picturl);
}

header("Content-Length: " . strlen($image));
echo $image;
?>