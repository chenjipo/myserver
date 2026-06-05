<?php

namespace App\sdk\console;

use App\common\DeviceServer;
use App\common\TvTool;
use App\sdk\model\Xuser;
use YXLib\foundation\ModelFactory;
use YXLib\foundation\queue\RedisQueue;
use App\common\SdkQueue;
use App\common\Queue;

class GetTvUserTool
{

    public function handle($params)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $aa  = new TvTool();

        $time = time();
        $killUser = [];
        $xuser = new Xuser();
        $model = ModelFactory::getInstance('sdk');
        for ($i=1; $i < 3; $i++) { 
            var_dump($i);
            $arr = $aa->getUserList($i);
            if (!empty($arr)) {
                $success = 0;
                $xxNum = $xxNum1 = 0;
                foreach ($arr as $userinfo) {
                    $uinfo = $xuser->getUserInfo($userinfo['uid']);
                    if (!empty($uinfo)) {
                        $upData = array(
                            'upwd'       => $userinfo['upwd'],
                            // 'is_online'  => $uinfo['is_push'] == 1 ? $userinfo['is_online'] : 0,
                            'is_online'  => $userinfo['is_online'],
                            'ystatus'    => $userinfo['ystatus'],
                            'parentname' => $userinfo['parentname'],
                            'yexpired'   => strtotime($userinfo['yexpired']),
                            'lastonline' => $userinfo['is_online'] == 1 ? $time : strtotime($userinfo['lastonline']),
                            // 'is_push'    => $userinfo['is_online'] == 1 ? 1 : 0,
                        );

                        $userinfo['ystatus'] == 1 && $upData['status'] = 1;

                        ###判断是否下线
                        if ($uinfo['is_online'] == 1 && $userinfo['is_online'] != 1) {
                            $xxNum1++;
                            // ###下线了，踢掉用户
                            // $killUser[$uinfo['id']] = 1;
                            // clear_user($uinfo['id'], $model);
                        }

                        // if ($userinfo['is_online'] == 1) {
                        //    $xxNum++;
                        //    $upData['is_clear'] = 0; ###不能干掉
                        //    $upData['is_push']  = 1;
                        // }
                        $xuser->updateUinfo($userinfo['uid'], $upData);

                    } else {
                        $data = array(
                            'uid'        => $userinfo['uid'],
                            'uname'      => $userinfo['uname'],
                            'upwd'       => $userinfo['upwd'],
                            'parentname' => $userinfo['parentname'],
                            'ystatus'    => $userinfo['ystatus'],
                            'status'     => $userinfo['ystatus'],
                            'is_push'    => 0,
                            'is_online'  => $userinfo['is_online'],
                            'is_clear'   => 0,
                            'yexpired'   => strtotime($userinfo['yexpired']),
                            'lastonline' => $userinfo['is_online'] == 1 ? $time : strtotime($userinfo['lastonline']),
                            'ymd'        => date('Ymd'),
                            'atime'      => time(),
                        );
                        $insetUid = $xuser->reg($data);
                        if (!empty($insetUid)) {
                            $success++;
                        }
                    }
                 }

                 if ($success > 0) {
                    $message = "程序已新增{$success}个账号到用户池";
                    $monitorArr = ['title' => 'player业务通知', 'message' => $message];
                    Queue::push('monitor', $monitorArr); 
                }

                // if (!empty($killUser)) {
                //     $killUserNum = count($killUser);
                //     $message = "程序监测到{$killUserNum}个账号已下线,已自动回收账号";
                //     $monitorArr = ['title' => 'player业务通知', 'message' => $message];
                //     Queue::push('monitor', $monitorArr); 
                // }
                var_dump($xxNum, $xxNum1);
            }
        }
        
       
        
    }
}


function clear_user($uid, $model)
{
    //1.删除映射关系
    $model->delete(['userid' => $uid], 0, 'x_mac_user_map');
    //2.重新设置is_push=0,lastonline=0,is_online=0,is_clear=0
    $model->update(['is_push' => 0, 'is_online' => 0, 'is_clear' => 0], ['id' => $uid], 'x_user');
    return true;
}