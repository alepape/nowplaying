<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// FUNCTIONS

// LOGIC

$config = $_GET["c"];
if ($config == "") {
    $config = "fip"; // default station
}

$configfile = __DIR__ .'/now.json';

$configdata = ["default" => $config];

file_put_contents($configfile, json_encode($configdata, JSON_PRETTY_PRINT)); 
// TODO: have a permission check for config json...

?>