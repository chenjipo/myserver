<?php
namespace App\common;

use YXLib\foundation\queue\RedisQueue;

class SdkQueue
{

    public static function push($queueName, $data)
    {
        $result = false;
        try {
            $job = new RedisQueue('sdk');
            $result = $job->push($queueName, $data);
        } catch (\Exception $ex) {
            $date = date("Y-m-d H:i:s");
            file_put_contents(ROOT . '/runtime/logs/sdk_push.log', $date.'###'.json_encode($data)."\n",FILE_APPEND);
            file_put_contents(ROOT . '/runtime/logs/sdk_push_error.log',$date.'###'.$ex->getMessage()."\n",FILE_APPEND);
        }
        return $result;
    }

    public static function glength($queueName)
    {
        $result = 0;
        try {
            $job = new RedisQueue('sdk');
            $result = $job->glength($queueName);
        } catch (\Exception $ex) {
            $date = date("Y-m-d H:i:s");
            file_put_contents(ROOT . '/runtime/logs/sdk_getkey_error.log', $date . '###' . "{$queueName}" . '###' .$ex->getMessage()."\n",FILE_APPEND);
        }
        return $result;
    }
}