<?php
// LOGIC
$mainconfigfile = __DIR__ .'/now.json';
$mainconfigjson = file_get_contents($mainconfigfile);
$mainconfigobj = json_decode($mainconfigjson, true);
// TODO: cache in session?

$config = $_GET["c"];
if ($config == "") {
    $config = $mainconfigobj['default']; // default station from file
}
$notification = "";
if (isset($mainconfigobj['notification'])) {
    $notification = $mainconfigobj['notification']; // notification URL for HA
    // only called from JS when change is detected
    // will use notif.php proxy to avoid CORS issues
    // also not needed from HA now that the custom sensor setup 'works'
}
$force_ext_path = "";
if (isset($mainconfigobj['force_ext_path'])) {
    $force_ext_path = $mainconfigobj['force_ext_path']; // if set, will be used to build cover.php URL
}

$configfile = __DIR__ .'/'.$config.'.json';
$configjson = file_get_contents($configfile);
$configobj = json_decode($configjson, true);
// TODO: cache in session?

?>