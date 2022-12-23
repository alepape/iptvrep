<?php
  require_once("common.php");

  // load config file
  $configfile = __DIR__ .'/config.json';
  $configjson = file_get_contents($configfile);
  $configdata = json_decode($configjson, true);

  //header('Content-Type: application/x-mpegURL');
  header('Content-Type: text/plain; charset=utf-8');
  header('Access-Control-Allow-Origin: *');

  $actual_link = $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], "/"));
  $static_url = "http://".$actual_link."/static.mp4\r\n\r\n";

  // configuration
  $configurations = $configdata['sources'];

  $output = parse_config($configurations);

  header('Content-Length: ', mb_strlen($output, '8bit'));

  echo $output;

?>