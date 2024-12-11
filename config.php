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
// TODO: only notif when change (use cache mechanism)

$configfile = __DIR__ .'/'.$config.'.json';
$configjson = file_get_contents($configfile);
$configobj = json_decode($configjson, true);
?>