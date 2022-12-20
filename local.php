<?php
header('Content-Type: application/x-mpegURL');
//header('Content-Type: text/plain; charset=utf-8');
header('Access-Control-Allow-Origin: *');

error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 'On');

$file = $_GET['f'];

$filepath = __DIR__ .'/'.$file;
$content = file_get_contents($filepath);

header('Content-Length: ', mb_strlen($content, '8bit'));

echo $content;

?>