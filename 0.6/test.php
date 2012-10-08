<?php
$lat = (isset($_GET['lat'])) ? $_GET['lat']: "null"; 
$lon = (isset($_GET['lon'])) ? $_GET['lon']: "null";
$zoom = (isset($_GET['zoom'])) ? $_GET['zoom']: "null";
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
<title>Freemap Test</title>
<link rel='stylesheet' type='text/css' href='css/style.css' />
<script type='text/javascript' src='Leaflet/dist/leaflet.js'></script>
<script type='text/javascript' src='kothic/dist/kothic.js'></script>
<script type='text/javascript' src='kothic/dist/kothic-leaflet.js'></script>
<script type='text/javascript' src='style.js'></script>
<link rel='stylesheet' type='text/css' href='Leaflet/dist/leaflet.css' />

<script type='text/javascript'>
var lat=<?php echo $lat; ?>;
var lon=<?php echo $lon; ?>;
var zoom=<?php echo $zoom;?>;
</script>

<script type='text/javascript' src='js/test.js'> </script>
<!--<script type='text/javascript' src='js/FeatureLoader.js'> </script>-->
</head>

<body onload="init()">
<h3>Freemap Test</h3>
<div style="height:400px; width:600px;" id=map>map goes here</div>



</body> </html>
