<?php
require_once("common.php");

$data = json_decode(file_get_contents('php://input'), true);

$mode = $_GET['m'];
if ($mode == '') {
    $mode = "output";
}

$outputpath = __DIR__ .'/'.$mode.'.json';
file_put_contents($outputpath, json_encode($data, JSON_PRETTY_PRINT));

?>
