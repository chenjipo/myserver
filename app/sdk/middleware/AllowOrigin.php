<?php
namespace App\sdk\middleware;

use App\common\Sign;
use App\common\Table;
use YXLib\foundation\Debug;

/**
 * 跨域中间件
 */
class AllowOrigin
{
  
    public function handle(\Closure $next)
    {
        header("Content-type:text/html;charset=utf-8");
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET, POST, OPTIONS, DELETE");
        header("Access-Control-Allow-Headers:DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type, Accept-Language, Origin, Accept-Encoding");
        return $next();
    }

}