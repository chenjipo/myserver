<?php

namespace App\sdk\console;

use App\common\DeviceServer;
use App\common\TvTool;
use App\sdk\model\Xuser;
use YXLib\foundation\ModelFactory;
use YXLib\foundation\queue\RedisQueue;
use App\common\SdkQueue;
use App\common\Queue;

class ClearTvUserTool
{

    public function handle($params)
    {
        $model = ModelFactory::getInstance('sdk');
        ###清理队列数据
        // $this->_clearQueue($model);
        // $this->_clearUser($model);
        $this->_clearOnline($model);
    }

    private function _clearQueue($model)
    {
        ###清理队列数据
        $sdkQueue = new RedisQueue('sdk');
        do {
            $json = $sdkQueue->pop('pre_xuser');
            if (!$json) {
                break;
            }

            ##处理数据
            $userInfo = json_decode($json, true);//[]
            echo $json . "\n";
            if (!empty($userInfo)) {
                $xuser_id = $userInfo['id'];
                ##取消分配账号
                $model->update(['is_push' => 0], ['id' => $xuser_id], 'x_user');
            }
        } while (true);
    }

    ###清除重新进可存账号处
    private function _clearUser($model)
    {
        $sql = "select a.id,a.uid,a.uname,a.lastonline,b.macid,b.atime,a.yexpired,a.is_online,a.lastonline from x_user a left join x_mac_user_map b on a.id=b.userid where a.is_push=1 and a.status=1 and a.ystatus=1 and b.macid is null and a.is_online=0 and a.is_clear=0";
        ###重新设置is_push=0
        $list = $model->query($sql);
        if (!empty($list)) {
            foreach ($list as $row) {
                $model->update(['is_push' => 0], ['id' => $row['id']], 'x_user');
            }
        }
    }

    private function _clearOnline($model)
    {
        $time = time();
        $date = date("Y-m-d H:i:s");
        $nextTime = strtotime(date('Ymd')) + 86400 * 10;
        //清理账号(系统内部)
        $sql = "select a.id,a.uid,a.uname,a.lastonline,b.macid,b.atime,a.yexpired,a.is_online,a.lastonline from x_user a join x_mac_user_map b on a.id=b.userid where a.is_push=1 and a.status=1 and a.ystatus=1 and a.is_online=0";
        //清除过期的账号
        // $sql = "select a.id,a.uid,a.uname,a.lastonline,b.macid,b.atime,a.yexpired,a.is_online,a.lastonline from x_user a join x_mac_user_map b on a.id=b.userid where a.yexpired<={$nextTime}";
        // echo $sql;
        // exit;
        ###重新设置is_push=0
        $list = $model->query($sql);
        // var_dump($list);
        // exit;
        if (!empty($list)) {
            $clear_success = 0;
            foreach ($list as $row) {
                $is_clear = true;
                $is_clear_field = 0;
                do {

                    // $clear_success++;
                    // $is_clear_field = 1;
                    // break;
                    ###如果上次在线时间是15天前回收
                    // if (($row['lastonline'] + 1296000) < $time) {
                    if (($row['lastonline'] + 864000) < $time) {
                        $clear_success++;
                        // var_dump($row);
                        break;
                    }

                    ##如果续费时间在15天以内回收
                    // if (($row['lastonline'] + 1800) <= $time) {
                    //     echo date("Y-m-d H:i:s", $row['lastonline']) . "\n";
                    //     $clear_success++;
                    //     break;
                    // }

                    // if (($row['lastonline'] + 3600) <= $time) {
                    //     echo date("Y-m-d H:i:s", $row['lastonline']) . "\n";
                    //     $clear_success++;
                    //     break;
                    // }

                    $is_clear = false;

                } while (false);
                
                if (true === $is_clear) {
                    //1.删除映射关系
                    $model->delete(['userid' => $row['id']], 0, 'x_mac_user_map');
                    // 2.重新设置is_push=0,lastonline=0,is_online=0,is_clear=1
                    $model->update(['is_push' => 0, 'is_clear' => $is_clear_field], ['id' => $row['id']], 'x_user');
                    file_put_contents(ROOT . '/runtime/logs/ClearTvUserTool_handle.log', $date . '###' . json_encode($row) . "\n", FILE_APPEND);
                }
            }
            // var_dump($clear_success);
            $message = "\n共有{$clear_success}个已绑定mac设备的账号可回收\n有{$clear_success}个成功";
            echo $message . "\n";
            $monitorArr = ['title' => 'player账号自动回收设备账号通知', 'message' => $message];
            Queue::push('monitor', $monitorArr); 
        }

    }
}