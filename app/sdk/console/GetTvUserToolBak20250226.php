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
        // $sdkQueue = new RedisQueue('sdk');
        // do {
        //     $json = $sdkQueue->pop('pre_xuser');
        //     if (!$json) {
        //         break;
        //     }
        //     var_dump($json);
        // } while (true);
        // $sdk = ModelFactory::getInstance('sdk');
        // $gpc['mac'] = 'D078090071E5';
        // $black_num = $sdk->getCount(['status' => 0], 'x_mac');
        // $monitorArr = ['title' => '黑名单设备变动通知', 'message' => "新增黑名单设备MAC:{$gpc['mac']},黑名单设备共{$black_num}台."];
        // Queue::push('monitor', $monitorArr); 

        //         exit;
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $aa  = new TvTool();
        $arr = $aa->getUserList();
        $time = time();
        $killUser = [];
        // exit;
        $xuser = new Xuser();
        if (!empty($arr)) {
            $success = 0;
            foreach ($arr as $userinfo) {
                $uinfo = $xuser->getUserInfo($userinfo['uid']);
                if (!empty($uinfo)) {
                    $upData = array(
                        'upwd'       => $userinfo['upwd'],
                        'is_online'  => $uinfo['is_push'] == 1 ? $userinfo['is_online'] : 0,
                        'ystatus'    => $userinfo['ystatus'],
                        'parentname' => $userinfo['parentname'],
                        'yexpired'   => strtotime($userinfo['yexpired']),
                        'lastonline' => $uinfo['is_push'] == 1 ? ($userinfo['is_online'] == 1 ? $time : strtotime($userinfo['lastonline'])) : 0,
                    );

                    ###判断是否下线
                    if ($uinfo['is_online'] == 1 && $userinfo['is_online'] != 1) {
                        ###下线了，踢掉用户
                        $killUser[$uinfo['id']] = 1;
                    }

                    // if ($uinfo['uname'] != 'demotest' && $uinfo['status'] == 1 && $userinfo['ystatus'] == 1 && $uinfo['is_push'] != 1) {
                    //     $xxx = SdkQueue::push('pre_xuser', [
                    //         'id'         => $uinfo['id'],
                    //         'uid'        => $uinfo['uid'],
                    //         'uname'      => $uinfo['uname'],
                    //         'upwd'       => $uinfo['upwd']
                    //         ]
                    //     );
                    //     if (!empty($xxx)) {
                    //         $success++;
                    //         $upData['is_push'] = 1;
                    //     }
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
                    // if (!empty($insetUid) && $data['ystatus'] == 1) {
                    if (!empty($insetUid)) {
                        $success++;
                        // ###新增补充到队列中
                        // $xxx = SdkQueue::push('pre_xuser', [
                        //     'id'         => $insetUid,
                        //     'uid'        => $userinfo['uid'],
                        //     'uname'      => $userinfo['uname'],
                        //     'upwd'       => $userinfo['upwd'],
                        //     'expired'    => $data['yexpired'],
                        //     ]
                        // );
                        // if (!empty($xxx)) {
                        //     $success++;
                        //     $xuser->updateUinfo($userinfo['uid'], array('is_push' => 1));
                        // }
                    }
                }
             }

             if ($success > 0) {
                $message = "程序已新增{$success}个账号到用户池";
                $monitorArr = ['title' => 'player业务通知', 'message' => $message];
                Queue::push('monitor', $monitorArr); 
            }

            if (!empty($killUser)) {
                $killUser = array_keys($killUser);
                $killUserNum = count($killUser);
                $message = "程序监测到{$killUserNum}个账号已下线,自动回收账号";
                $monitorArr = ['title' => 'player业务通知', 'message' => $message];
                Queue::push('monitor', $monitorArr); 
            }
        }
    }
}