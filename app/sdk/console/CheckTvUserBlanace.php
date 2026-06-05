<?php

namespace App\sdk\console;

use App\common\DeviceServer;
use App\common\TvTool;
use App\sdk\model\Xuser;
use YXLib\foundation\ModelFactory;
use App\common\Queue;

class CheckTvUserBlanace
{

    public function handle($params)
    { 
        $time = time();
        $obj  = new TvTool();
        $res = $obj->getUserInfo();
        if ($res !== false) {
            // $message = "\n当前账号余额：\${$res['credits']}\n总用户：{$res['active_accounts']}\n在线用户：{$res['online_users']}";
            // $monitorArr = ['title' => 'player账号信息', 'message' => $message];
            // Queue::push('monitor', $monitorArr); 

            if ($res['credits'] <= 100) {
                $message = "当前账号余额：\${$res['credits']}，已不足\$100，请及时充值!";
                $monitorArr = ['title' => 'player账号余额不足告警', 'message' => $message];
                //Queue::push('monitor', $monitorArr); 
            }

            $model = ModelFactory::getInstance('sdk');
            $model->insert(['balance' => $res['credits'], 'all_user' => $res['active_accounts'], 'online_user' => $res['online_users'], 'atime' => $time, 'ymd' => date('Ymd', $time)], false, 'x_all_data_log');
            $model->update(['balance' => $res['credits'], 'all_user' => $res['active_accounts'], 'online_user' => $res['online_users']], ['id' => 1], 'x_all_data');
        }
    }
}
