<?php
return array(
	'queue' => env('redis_queue.host') . ':' . env('redis_queue.port') . ':' . env('redis_queue.auth'),
	'store' => env('redis_store.host') .' :' . env('redis_store.port') . ':' . env('redis_store.auth'),
	'sdk'   => env('redis_sdk.host') . ':' . env('redis_sdk.port') . ':' . env('redis_sdk.auth'),
);