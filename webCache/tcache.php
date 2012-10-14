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
include("config.php");
include("logger.php");

write_log("tcache.php start",DEFAULT_LOG);

if (isset($_GET['x']))
  $x = $_GET['x'];
if (isset($_GET['y']))
  $y = $_GET['y'];
if (isset($_GET['z']))
  $z = $_GET['z'];
if (isset($_GET['debug'])) $debug=true;


// Do we have a valid URL?
if (isset($x) and isset($y) and isset($z)) {
  // URL Valid, so do something....
  // See if the tile is already in the cache or not
  $path=$cacheDir."/".$z."/".$x;
  $fname=$path."/".$y.".js";
  if ($debug) write_log("path=".$path,DEFAULT_LOG);
  if ($debug) write_log("fname=".$fname,DEFAULT_LOG);
  if (is_dir($path)) {
    if (is_file($fname)) {
      $isInCache = true;
    } else {
      $isInCache = false;
    }
  } else {
    mkdir($path,0777,true);
    $isInCache = false;
  }

  if ($isInCache) {
    if ($debug) write_log("File is in chache - retrieving cached version...",DEFAULT_LOG);
    $contents=file_get_contents($fname);
  } else {
    if ($debug) write_log("File not cached - retrieving from server...",DEFAULT_LOG);
    // The file is not in the cache, so we need to retrieve it from the server.
    $url = $dbsUrl . "?x=".$x."&y=".$y."&z=".$z."&way=all&poi=all&kothic=1&contour=1&coastline=1";
    if ($debug) write_log("url=".$url,DEFAULT_LOG);
    $ch = curl_init( $url );
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
    curl_setopt( $ch, CURLOPT_HEADER, true );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] );
    curl_setopt( $ch, CURLOPT_TIMEOUT, 120 );
    list( $header, $contents ) = preg_split( '/([\r\n][\r\n])\\1/', curl_exec( $ch ), 2 );
    $status = curl_getinfo( $ch );
    if ($debug) write_log("curl status = ".$status['http_code'],DEFAULT_LOG);
    curl_close( $ch );
    $retVal = file_put_contents($fname,$contents);
  }
  echo $contents;
} else {
  // URL invalid, so just return an error and give up.
  echo "ERROR - Invalid URL - must specify x,y and z for this to work!<br>\n";
}
if ($debug) write_log("tcache.php end",DEFAULT_LOG);



?>