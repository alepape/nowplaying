<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// FUNCTIONS

// LOGIC

// try POST first
$config = $_POST["c"];
if ($config == "") {
    // GET second
    $config = $_GET["c"];
    if ($config == "") {
        $config = "fip"; // default station
    }
}

$mainconfigfile = __DIR__ .'/now.json';
$mainconfigjson = file_get_contents($cmainonfigfile); // reading first
$mainconfigobj = json_decode($mainconfigjson, true);

$mainconfigobj["default"] = $config;

file_put_contents($mainconfigfile, json_encode($mainconfigobj, JSON_PRETTY_PRINT)); 
// TODO: have a permission check for config json...

?>