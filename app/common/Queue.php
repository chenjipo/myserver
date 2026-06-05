<?php
namespace App\common;

use YXLib\foundation\queue\RedisQueue;

class Queue {

    /**
     * 激活数据
     *
     * @param [type] $data
     * @return void
     */
    public static function pushActiveDataToMonitor($data)
    {
        $result = false;
        try {
            $queueName = 'queue_ad_active';
            $job = new RedisQueue('queue');
            $result = $job->push($queueName,$data);
        } catch (\Exception $ex) {
            $date = date("Y-m-d H:i:s");
            file_put_contents(ROOT.'/runtime/logs/pushActiveData.log',$date.'###'.json_encode($data)."\n",FILE_APPEND);
            file_put_contents(ROOT.'/runtime/logs/pushActiveData_error.log',$date.'###'.$ex->getMessage()."\n",FILE_APPEND);
        }
        return $result;
    }
    
    public static function push($queueName, $data)
    {
        $result = false;
        try {
            $job = new RedisQueue('queue');
            $result = $job->push($queueName, $data);
        } catch (\Exception $ex) {
            $date = date("Y-m-d H:i:s");
            file_put_contents(ROOT.'/runtime/logs/push.log', $date.'###'.json_encode($data)."\n",FILE_APPEND);
            file_put_contents(ROOT.'/runtime/logs/push_error.log',$date.'###'.$ex->getMessage()."\n",FILE_APPEND);
        }
        return $result;
    }
}