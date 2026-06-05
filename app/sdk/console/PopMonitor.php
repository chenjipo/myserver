<?php

namespace App\sdk\console;

use App\common\App;
use App\common\Table;
use YXLib\foundation\ModelFactory;
use YXLib\foundation\queue\RedisQueue;

class PopMonitor
{
    public $queueName = 'monitor';

    public function handle()
    {
        $job = new RedisQueue('queue');
              
        try {
            while (1) {
                $json = $job->pop($this->queueName);
                if ($json) {
                    $gpc = json_decode($json, true);//[]
                     
                    ###钉钉告警
                    $title = $gpc['title'];
                    $message = $gpc['message'];
                    $config = config('dingding');
                    $api = $config['admin'];
                    $res = dd_nitoce($api, $title, $message);

                } else {
                    sleep(3);
                }
            }
        } catch (\Exception $ex) {
            $date = date("Y-m-d H:i:s");
            file_put_contents(ROOT . '/runtime/logs/queue_pop_[' . $this->queueName . '].log', $date . '###' . json_encode($gpc) . "\n", FILE_APPEND);
            file_put_contents(ROOT . '/runtime/logs/queue_pop_[' . $this->queueName . ']_error.log', $date . '###' . $ex->getMessage() . "\n", FILE_APPEND);
            exit();
        }    
    }
}