<?php
namespace App\common;

use YXLib\foundation\Debug;
use YXLib\support\Util;

class LibLog
{

    public static function write($type,$data)
    {
        $dir = config('app.sdk_log_path') . '/' . $type . '/';
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                Debug::log('创建日志目录失败' . $dir);
                return false;
            };
        }
        $file =  date('Ymd') . '.log';
        $content = json_encode($data) . "\n";
        return Util::file_append_contents($dir . $file , $content);
    }

    public static function orderLog($order_num, $type, $data)
    {
        $dir = config('app.sdk_log_path') . '/' . 'order/';
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                Debug::log('创建日志目录失败' . $dir);
                return false;
            };
        }
        $file =  $order_num . '.log';
        $content = '[' . date('Y-m-d H:i:s') . ']' . $type . "\n" . json_encode($data) . "\n";
        return Util::file_append_contents($dir . $file, $content);
    }

}