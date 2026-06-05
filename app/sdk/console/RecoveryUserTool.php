<?php

namespace App\sdk\console;

use App\common\DeviceServer;
use App\common\TvTool;
use App\sdk\model\Xuser;
use YXLib\foundation\ModelFactory;
use App\common\Queue;

class RecoveryUserTool
{

    public function handle($params)
    { 
        $model = ModelFactory::getInstance('sdk');
        //检查黑名单设备
        $sql = "select b.userid from x_mac a join x_mac_user_map b on a.id=b.macid where a.status=0";
        $list = $model->query($sql);
        if (!empty($list)) {
            $success = 0;
            $xuser = new Xuser();
            foreach ($list as $row) {
                // $res = $xuser->update(array('status' => 0), ['id' => $row['userid'], 'status' => 1], 'x_user');
                $res = $xuser->updateUinfoById($row['userid'], array('status' => 0));
                if ($res) {
                    $success++;
                }
            }

            if ($success > 0) {
                $message = "已设置{$success}个黑名单账号";
                $monitorArr = ['title' => 'player账号黑名单设置通知', 'message' => $message];
                //Queue::push('monitor', $monitorArr); 
            }
            
        }
    }
}
