<?php
// tcache.php
// A simple cache manager for Freemap data tiles.  Its function is:
// Receive requests of the format:
//  tcache.php?x=253&y=162&z=9&way=all&poi=all&kothic=1&contour=0&coastline=1
// Checks its cache directories to see if the required file exists.
// If it does, it returns the contents of hte file to the client.
// If it does not, it queries the database server to generate the tile,
//    saves it into the cache, then returns it to the server.
//
// Uses some code from https://github.com/cowboy/php-simple-proxy
//
// 08oct2012  GJ  ORIGINAL VERSION
//
$dbsUrl = 'http://maps.webhop.net/Freemap/0.6/ws/tsvr.php';
$cacheDir = '/var/www/Freemap/cacheDir';

if (isset($_GET['x']))
  $x = $_GET['x'];
if (isset($_GET['y']))
  $y = $_GET['y'];
if (isset($_GET['z']))
  $z = $_GET['z'];

if (isset($x) and isset($y) and isset($z)) {
  echo "Valid URL!";
  // http://maps.webhop.net/Freemap/0.6/ws/tsvr.php?x=2025&y=1303&z=12&way=all&poi=all&kothic=1&contour=0&coastline=1  
  $url = $dbsUrl . "?x=".$x."&y=".$y."&z=".$z."&way=all&poi=all&kothic=1&contour=1&coastline=1";
  echo "url=".$url;
  $ch = curl_init( $url );
  curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
  curl_setopt( $ch, CURLOPT_HEADER, true );
  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
  curl_setopt( $ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] );
  list( $header, $contents ) = preg_split( '/([\r\n][\r\n])\\1/', curl_exec( $ch ), 2 );
  $status = curl_getinfo( $ch );
  curl_close( $ch );
  echo $contents;

} else {
  echo "ERROR - Invalid URL - must specify x,y and z for this to work!";


}



?>