<?php
// expiretiles.php
// Remove expired tiles from the cache.
//
// 09oct2012  GJ  ORIGINAL VERSION
//
include("config.php");

if (isset($_GET['debug'])) $debug=true;
if (isset($_GET['all'])) $expireAll=true;
if (isset($_GET['x']))
  $x = $_GET['x'];
if (isset($_GET['y']))
  $y = $_GET['y'];
if (isset($_GET['z']))
  $z = $_GET['z'];

$debug=true;


if (isset($expireAll)) {
  if ($debug) echo "Removing all tiles....<br>\n";
  clearDir($cacheDir,$debug);
} elseif (isset($x) and isset($y) and isset($z)) {
  clearTile($x,$y,$z,$cacheDir,$maxZoom=18);
} else {
  echo "Unrecognised Options - doing nothing...<br>\n";
}

function clearTile($x,$y,$z,$cacheDir,$maxZoom) {
  $path=$cacheDir."/".$z."/".$x;
  $fname=$path."/".$y.".js";
  if (is_file($fname)) {
    print "Removing ".$fname."<br>\n";
    unlink($fname);
  } else {
    print "File ".$fname." not in cache<br>\n";
  }
  # Now clear the higher zoom level tiles too.
  if ($z<$maxZoom) {
    clearTile($x*2,$y*2,$z+1,$cacheDir,$maxZoom);
    clearTile($x*2,$y*2+1,$z+1,$cacheDir,$maxZoom);
    clearTile($x*2+1,$y*2,$z+1,$cacheDir,$maxZoom);
    clearTile($x*2+1,$y*2+1,$z+1,$cacheDir,$maxZoom);
  }
    
}


// From http://php.net/manual/en/class.recursivedirectoryiterator.php
function clearDir($dirPath,$debug) {
  echo "clearDir - dirPath=".$dirPath."<br>\n";
  $iterator = new RecursiveIteratorIterator(
		     new RecursiveDirectoryIterator($dirPath),
		     RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($iterator as $path) {
      if ($path->isDir()) {
	if ($debug) echo "removing directory ".$path->__toString()."<br>\n";
	rmdir($path->__toString());
      } else {
	if ($debug) echo "removing ".$path->__toString()."<br>\n";
         unlink($path->__toString());
      }
    }
  return true;
}

?>