<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
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

$configfile = __DIR__ .'/now.json';
$configjson = file_get_contents($configfile); // reading first
$configobj = json_decode($configjson, true);

$configdata = ["default" => $config];
if ($configobj["notification"] != "") { // notif URL - let's not lose it
    $configdata["notification"] = $configobj["notification"];
}

file_put_contents($configfile, json_encode($configdata, JSON_PRETTY_PRINT)); 
// TODO: have a permission check for config json...

?>