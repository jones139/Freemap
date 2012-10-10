<?php
// expiretiles.php
// Remove expired tiles from the cache.
//
// 09oct2012  GJ  ORIGINAL VERSION
//
include("config.php");

if (isset($_GET['debug'])) $debug=true;
if (isset($_GET['all'])) $expireAll=true;

$expireAll=true;
$debug=true;

// Do we have a valid URL?
if (isset($expireAll)) {
  if ($debug) echo "Removing all tiles....<br>\n";
  clearDir($cacheDir,$debug);
}



// From http://php.net/manual/en/class.recursivedirectoryiterator.php
function clearDir($dirPath,$debug) {
  echo "clearDir - dirPath=".$dirPath."<br>\n";
  $iterator = new RecursiveIteratorIterator(
		     new RecursiveDirectoryIterator($dirPath),
		     RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($iterator as $path) {
      if ($path->isDir()) {
	if ($debug) echo "removing directory ".$path->__toString();
	rmdir($path->__toString());
      } else {
	if ($debug) echo "removing ".$path->__toString();
         unlink($path->__toString());
      }
    }
  return true;
}

?>