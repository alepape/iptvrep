<?php
require_once("common.php");

$mode = $_GET['m'];
if ($mode == "") {
    $mode = "m3u8";
}

$id = $_GET['id'];
if ($id == "") {
    $id = "Melbourne";
}

header('Access-Control-Allow-Origin: *');

if ($mode == "m3u8") {
    header('Content-Type: application/x-mpegURL');
}
if ($mode == "text") {
    header('Content-Type: text/plain; charset=utf-8');
}
if ($mode == "json") {
    header('Content-Type: application/json; charset=utf-8');
}

// load config file
$configfile = __DIR__ .'/config.json';
$configjson = file_get_contents($configfile);
$configdata = json_decode($configjson, true);

$config = find_config($id, $configdata['sources']);

if ($config != null) {
    if ($config['url'] != null) {
        echo remote_src($config, $mode);
    }
    if ($config['file'] != null) {
        echo local_src($config, $mode);
    }
}

?>