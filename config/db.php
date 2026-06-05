<?php

$db['main'] = array(
   'db' => env('database_player_main.database'),
   'server' => array('host' => env('database_player_main.host'), 'user' => env('database_player_main.username'), 'password' => env('database_player_main.password'),'port'=>env('database_player_main.port')),
);

$db['admin'] = array(
   'db' => env('database_player_admin.database'),
   'server' => array('host' => env('database_player_admin.host'), 'user' => env('database_player_admin.username'), 'password' => env('database_player_admin.password'),'port'=>env('database_player_admin.port')),
);

$db['sdk'] = array(
   'db' => env('database_player_sdk.database'),
   'server' => array('host' => env('database_player_sdk.host'), 'user' => env('database_player_sdk.username'), 'password' => env('database_player_sdk.password'),'port'=>env('database_player_sdk.port')),
);


return $db;
