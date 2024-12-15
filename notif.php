<?php
// BOOTSTRAP
include 'config.php';

// can't do in JS because CORS
// also - HA updates could be just managed by the sensor... this reduces the volume
// as long as I can control the refresh rate of the sensor (current try is 30s - TBC)
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