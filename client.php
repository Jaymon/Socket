<?php

// client: http://www.php.net/manual/en/sockets.examples.php
include('Socket/Server.php');
include('Socket/Client.php');

error_reporting(-1);
ini_set('display_errors','on');

$address = 'localhost';
$port = 9080;
$input = array_slice($argv, 1);
if(empty($input)){
  echo "please pass in some arguments to send to the server", PHP_EOL;
  echo "example: php ".basename(__FILE__).' "this is some input"', PHP_EOL;
  exit(1);

}//if

$s = new Socket\Client($address,$port);

echo 'Writing to ',$address,':',$port,PHP_EOL;

foreach($input as $in){
  echo 'writing: ',$in,PHP_EOL;

  $s->write($in);

  echo 'Getting response!',PHP_EOL;

  $out = $s->read();

  echo 'server returned: ',$out,PHP_EOL;

}//foreach
