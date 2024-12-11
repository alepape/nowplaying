<?php
// BOOTSTRAP
include 'config.php';

// TODO: do me in JS instead...
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