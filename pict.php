<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
error_reporting(E_ERROR);

// BOOTSTRAP
include 'bootstrap.php';
// bootstrap uses cover.php when nothing is found, which outputs "notfound"

if ($nowPictURL == "") {
    $image = file_get_contents("picts/logo.png"); // TODO: check same logic in cover.php? why here too?
    // using logo and not "notfound" to detect case (see above)
    // TODO: use radio logo instead
    // TODO: check if/when this is used at all now that I removed it from the HA script
    header('Content-type: image/png');
} else {
    $image = file_get_contents($nowPictURL);
    header('Content-type: image/jpeg');
}

header("Content-Length: " . strlen($image));
echo $image;

?>