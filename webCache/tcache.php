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
// 08oct2012  GJ  ORIGINAL VERSION
//
echo "tchache.php";

?>