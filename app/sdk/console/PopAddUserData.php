<?php

namespace App\sdk\console;

use App\common\App;
use App\common\Table;
use YXLib\foundation\ModelFactory;
use YXLib\foundation\queue\RedisQueue;
use App\common\TvTool;
use App\common\Queue;

class PopAddUserData
{
    public $queueName = 'add_xuser';

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
                    
                    $newData = array(
                         'username' => rand_user('auto'),
                         'password' => sprintf('%08d', rand(00000000, 99999999)),
                    );
		    $obj = new TvTool();
                    $res = $obj->addUser($newData);
                    $message = "已触发自动补充账号机制,操作失败";
                    if (true === $res) {
                        $message = "已触发自动补充账号机制,操作成功";
                        ###执行
                        exec("/usr/local/php/bin/php /www/cmd sdk:GetTvUserTool > /dev/null 2>&1"); 
                    }

                    ###钉钉告警
                    // $title = 'player空闲账号自动补充通知';
                    // $config = config('dingding');
                    // $api = $config['admin'];
                    // $res = dd_nitoce($api, $title, $message);

                    $monitorArr = ['title' => 'player空闲账号自动补充通知', 'message' => $message];
                    Queue::push('monitor', $monitorArr); 

                } else {
                    sleep(3);
                }
            }
        } catch (\Throwable $ex) {
            $date = date("Y-m-d H:i:s");
            file_put_contents(ROOT . '/runtime/logs/queue_pop_[' . $this->queueName . '].log', $date . '###' . json_encode($gpc) . "\n", FILE_APPEND);
            file_put_contents(ROOT . '/runtime/logs/queue_pop_[' . $this->queueName . ']_error.log', $date . '###' . $ex->getMessage() . "\n", FILE_APPEND);
            exit();
        }    
    }
}
