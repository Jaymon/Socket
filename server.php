<?php

// code: http://devzone.zend.com/article/1086
include('out_class.php');
include('Socket/Server.php');

error_reporting(-1);

// Set time limit to indefinite execution
set_time_limit(0);

// Set the ip and port we will listen on
$address = '127.0.0.1';
$port = 9080;

echo 'Listening on ',$address,':',$port,PHP_EOL;

$s = new Socket\Server($address,$port);

$s->listen(function($input){

  echo "received: ".$input, PHP_EOL;
  $ret_str = preg_replace('#\s#','',$input);
  echo "returning: ".$ret_str, PHP_EOL;
  return $ret_str;

});
