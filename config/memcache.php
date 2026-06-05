<?php
return array(
	'default' => array(
		env('memcache.host').':'.env('memcache.port')
		//在这里添加多个ip
	),
	'admin' => array(
		env('memcache_admin.host').':'.env('memcache_admin.port')
		//在这里添加多个ip
	),
);