<?php

namespace App\sdk\console;

use App\common\App;
use App\common\Table;
use YXLib\foundation\ModelFactory;
use YXLib\foundation\queue\RedisQueue;
use App\common\Queue;

class PopCrashData
{
    public $queueName = 'log_crash';

    public function handle()
    {
        $job = new RedisQueue('queue');
        $store = new RedisQueue('store');
        $model = ModelFactory::getInstance('main');
        $sdk = ModelFactory::getInstance('sdk');
        
        try {
            while (1) {
                $json = $job->pop($this->queueName);
                if ($json) {
                    $gpc = json_decode($json, true);//[]
                    $wm_gpc = array();
                    
                    if (empty($wm_gpc['refer'])) {
                        $arr_refer = App::parseSdkRefer($gpc['refer'], $gpc['appid']);
                        //修改cid
                        $gpc['cid'] = $arr_refer['cid'];
                    }

                    $row = Table::fieldsFilter($gpc, 'log_crash');
                    $model->insert($row, false, 'log_crash');

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