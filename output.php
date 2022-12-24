<?php
  require_once("common.php");

  //header('Content-Type: application/json');
  //header('Content-Type: application/x-mpegURL');
  header('Content-Type: text/plain; charset=utf-8');
  header('Access-Control-Allow-Origin: *');

  // load config file
  $outputfile = __DIR__ .'/output.json';
  $outputjson = file_get_contents($outputfile);

  $outputdata = json_decode($outputjson, true);

  $cleaned_output = "";

  foreach ($outputdata as $stream) {
    $channel = new Channel($stream['group_title']);
    $channel->fromObj($stream);
    $cleaned_output .= $channel->toString();
  }

  //header('Content-Length: ', mb_strlen($cleaned_output, '8bit'));
  
  //echo $outputjson;

  echo $cleaned_output;

?>