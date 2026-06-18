<?php

namespace App\sdk\console;

use App\common\TvTool;
use App\common\TvUserPool;
use App\common\Queue;
use YXLib\foundation\queue\RedisQueue;

class PopAddUserData
{
    public $queueName = 'add_xuser';

    public function handle()
    {
        $job = new RedisQueue('queue');
        $gpc = array();
        try {
            while (1) {
                $json = $job->pop($this->queueName);
                if ($json) {
                    $gpc = json_decode($json, true);
                    $inventory = TvUserPool::getInventory();
                    $limitNum = TvUserPool::LIMIT_NUM;
                    echo "PopAddUserData: dbPool={$inventory['dbPool']}, queue={$inventory['queue']}, available={$inventory['available']}, limit={$limitNum}\n";
                    if ($inventory['available'] >= $limitNum) {
                        echo "PopAddUserData: inventory sufficient, skip register\n";
                        continue;
                    }
                    $newData = array(
                        'username' => rand_user('auto'),
                        'password' => sprintf('%08d', rand(00000000, 99999999)),
                    );
                    $obj = new TvTool();
                    $res = $obj->addUser($newData);
                    $message = "激活消耗补号:可用库存{$inventory['available']}个,不足{$limitNum}个,注册失败";
                    if ($res === true) {
                        $message = "激活消耗补号:可用库存{$inventory['available']}个,不足{$limitNum}个,已成功注册1个账号";
                        $getTvUserTool = new GetTvUserTool();
                        $getTvUserTool->handle([]);
                    }
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
