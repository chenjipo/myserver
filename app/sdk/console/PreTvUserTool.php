<?php

namespace App\sdk\console;

use App\common\DeviceServer;
use App\common\TvTool;
use App\sdk\model\Xuser;
use YXLib\foundation\ModelFactory;
use App\common\Queue;

class PreTvUserTool
{

    public function handle($params)
    { 
        $model = ModelFactory::getInstance('sdk');
        //检查目前剩余可用的账号数
        $sql = "select count(a.id) as num from x_user a left join x_mac_user_map b on a.id=b.userid where b.macid is null and a.status=1 and a.ystatus=1 and a.is_clear=0";
        // echo $sql;
        // $sql = "select count(1) as allNum,count(if(is_online=0,1,null)) as kcNum from x_user where status=1 and ystatus=1 and is_clear=0";
        $hasInfo = $model->getOne($sql);
        // $allNum = $kcNum = 0;
        // if (!empty($hasInfo)) {
        //     $allNum = (int)$hasInfo['allNum'];
        //     $kcNum  = (int)$hasInfo['kcNum'];
        // }
        $limitNum = 5;
        $kcNum = intval($hasInfo['num']);
        var_dump($kcNum, $limitNum);
        if ($kcNum < $limitNum) {
            ###自动填充数量
            $needNum = $limitNum - $kcNum;
            $obj  = new TvTool();
            $success = 0;
            for ($i = 1; $i <= $needNum; $i++) { 
                $newData = array(
                     'username' => rand_user('x1'),
                     'password' => sprintf('%08d', rand(00000000, 99999999)),
                );
                $res = $obj->addUser($newData);
                if (true === $res) {
                    $success++;
                }
            }

            if ($success > 0) {
                ###执行更新
                exec("/usr/local/php/bin/php /www/cmd sdk:GetTvUserTool > /dev/null 2>&1");
            }

            ###钉钉告警
            $message = "目前空闲账号已不足{$limitNum}个,剩余{$kcNum}个可用,已触发自动补充账号机制,成功补充{$success}个账号";
            $monitorArr = ['title' => 'player空闲账号库存通知', 'message' => $message];
            Queue::push('monitor', $monitorArr); 
        }
    }
}
