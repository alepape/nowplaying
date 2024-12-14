<?php
// LOGIC
$configfile = __DIR__ .'/now.json';
$configjson = file_get_contents($configfile);
$configobj = json_decode($configjson, true);

$config = $_GET["c"];
if ($config == "") {
    $config = $configobj['default']; // default station from file
}
$notification = $configobj['notification']; // notification URL for HA
// only called from JS when change is detected
// will use notif.php proxy to avoid CORS issues
$force_ext_path = $configobj['force_ext_path']; // if set, will be used to build cover.php URL

$configfile = __DIR__ .'/'.$config.'.json';
$configjson = file_get_contents($configfile);
$configobj = json_decode($configjson, true);
?>