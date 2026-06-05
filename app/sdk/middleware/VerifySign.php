<?php
namespace App\sdk\middleware;

use App\common\Sign;
use App\common\Table;
use YXLib\foundation\Debug;

/**
 * api签名校验中间件
 */
class VerifySign
{
  
    public function handle(\Closure $next)
    {
        $contentType = $_SERVER['CONTENT_TYPE'];
        if ($contentType == 'application/json') {
            $data = postRaw();
            $data = json_decode($data, true);
        } else {
            if (!empty($_POST)) {
                $data = $_POST;
            } else {
                $data = $_GET;
            }
        }
        
        $sign = $data['sign'];
        if (!$data['sign'] && !$data['time']) {
            return fail('sign fail');
        }

        if (!$data['appid']) {
            return fail('Lack of Parameter');
        }

        $app = sy_data_read(Table::$pf_app, $data['appid']);

        if (!$app) {
            return fail('app not exists');
        }
        if (!$app['appkey']) {
            return fail('System fail');
        }

        unset($data['sign']);
        if (!Sign::verify($data, $app['appkey'], $sign)) {
            return fail('Sign Error');
        }
        return $next();
    }

}