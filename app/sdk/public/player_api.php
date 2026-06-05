<?php

header("Content-type:text/html;charset=utf-8");
header("Access-Control-Allow-Origin:*");
header("Access-Control-Allow-Methods:GET, POST, OPTIONS, DELETE");
header("Access-Control-Allow-Headers:DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type, Accept-Language, Origin, Accept-Encoding");
$url = 'http://honeytv.xyz:80' . $_SERVER['REQUEST_URI'];
$result = file_get_contents($url);

echo  $result;


?>