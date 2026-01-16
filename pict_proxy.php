<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ERROR);

if (isset($_GET["p"])) {
    $url = $_GET["p"];
} else {
    $url = "picts/notfound.png";
}

$image = file_get_contents($url); // TODO: use a cache?, like when I override...
header('Content-type: image/*');
header("Content-Length: " . strlen($image));
echo $image;

?>