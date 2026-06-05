<?php
define('VERSION','1.0.2');
/**
 * 定义APP_ROOT
 */
define('APP_ROOT',dirname(__DIR__));
/**
 * 自动加载
 */
require dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
/**
 * 运行项目
 */
YXLib\YX::run();
