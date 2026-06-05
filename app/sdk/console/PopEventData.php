<?php

namespace App\sdk\console;

use App\common\App;
use App\common\Table;
use YXLib\foundation\ModelFactory;
use YXLib\foundation\queue\RedisQueue;
use App\common\Queue;

class PopEventData
{
    public $queueName = 'log_event';

    public function handle()
    {
        $job = new RedisQueue('queue');
        $model = ModelFactory::getInstance('main');
        
        try {
            while (1) {
                $json = $job->pop($this->queueName);
                if ($json) {
                    $gpc = json_decode($json, true);//[]
                    
                    if (empty($wm_gpc['refer'])) {
                        $arr_refer = App::parseSdkRefer($gpc['refer'], $gpc['appid']);
                        //修改cid
                        $gpc['cid'] = $arr_refer['cid'];
                    }

                    $row = Table::fieldsFilter($gpc, 'log_event');
                    // $model->insert($row, false, Table::$log_active . date('Ymd'));
                    $model->insert($row, false, 'log_event');
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