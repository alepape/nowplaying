<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
error_reporting(E_ERROR);

// BOOTSTRAP
include 'bootstrap.php';

//echo $picturl;
if ($nowPictURL == "") {
    $image = file_get_contents("notfound.png");
    header('Content-type: image/png');
} else {
    $image = file_get_contents($nowPictURL);
    header('Content-type: image/jpeg');
}

header("Content-Length: " . strlen($image));
echo $image;

?>