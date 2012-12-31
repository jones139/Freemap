<?php
// Pre-seed the server cache with tiles.
// usage:  php ./preSeed.php -l lowerZoom -u upperZoom
// where lowZoom and highZoom define the range of zoom levels to seed.
include('config.php');

$serverUrl = "http://freemap.maps3.org.uk/webCache/tcache.php";
$bbox = array(-6.5,49.5,2.1,59);

$lon_bl = $bbox[0];
$lat_bl = $bbox[1];
$lon_tr = $bbox[2];
$lat_tr = $bbox[3];


$longOpts = "u:l:";

$options = getopt($longOpts);
$lowerZoom = $options['l'];
$upperZoom = $options['u'];

print "pre-seeding between zoom levels ".$lowerZoom." and ".$upperZoom.".\n";
print "Bounding Box is (".$lon_bl.",".$lat_bl.") to (".$lon_tr.",".$lat_tr.")\n";

for ($zoom=$lowerZoom; $zoom<=$upperZoom;$zoom++) {
  print "zoom=".$zoom."\n";

  $xtile_bl = floor((($lon_bl + 180) / 360) * pow(2, $zoom));
  $ytile_bl = floor((1 - log(tan(deg2rad($lat_bl)) + 1 / cos(deg2rad($lat_bl))) / pi()) /2 * pow(2,$zoom));
  $xtile_tr = floor((($lon_tr + 180) / 360) * pow(2, $zoom));
  $ytile_tr = floor((1 - log(tan(deg2rad($lat_tr)) + 1 / cos(deg2rad($lat_tr))) / pi()) /2 * pow(2,$zoom));

  print "bounding box is between (".$xtile_bl.",".$ytile_bl.") and (".$xtile_tr.",".$ytile_tr.")\n";

  for ($ytile=$ytile_bl;$ytile>=$ytile_tr;$ytile--) {
    for ($xtile=$xtile_bl;$xtile<=$xtile_tr;$xtile++) {
      print "(".$xtile.",".$ytile.") ";
      $url = $serverUrl."?z=".$zoom."&x=".$xtile."&y=".$ytile;
      print "URL = ".$url."\n";
      $ch = curl_init( $url );
      curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
      curl_setopt( $ch, CURLOPT_HEADER, true );
      curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
      curl_setopt( $ch, CURLOPT_USERAGENT, "preSeed.php" );
      curl_setopt( $ch, CURLOPT_TIMEOUT, 1200 );
      list( $header, $contents ) = preg_split( '/([\r\n][\r\n])\\1/', curl_exec( $ch ), 2 );
      $status = curl_getinfo( $ch );
      curl_close( $ch );
      if ($status['http_code']==200) {
	print("Retrieve data ok\n");
	$retVal = file_put_contents($fname,$contents);
      } else {
	print ("Problem Retrieving Data - status=".$status['http_code']."\n");
      }

    }
    print "\n";
  }

}



?>