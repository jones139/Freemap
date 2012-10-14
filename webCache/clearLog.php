<?php
include('config.php');
include('logger.php');

unlink(DEFAULT_LOG);

write_log("New Log Created",DEFAULT_LOG);

echo "<h1>Done!</h1>";
?>