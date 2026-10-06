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
        $result = $aa->getAllUserList(1000);
        $arr = isset($result['list']) ? $result['list'] : [];
        $recordsTotal = isset($result['recordsTotal']) ? (int)$result['recordsTotal'] : 0;
        $pages = isset($result['pages']) ? (int)$result['pages'] : 0;
        echo "GetTvUserTool: recordsTotal={$recordsTotal}, pages={$pages}, fetched=" . count($arr) . "\n";
        if (!empty($arr)) {
            $success = 0;
            $xxNum = $xxNum1 = 0;
            foreach ($arr as $userinfo) {
                $uinfo = $xuser->getUserInfo($userinfo['uid']);
                if (!empty($uinfo)) {
                    $upData = array(
                        'upwd'       => $userinfo['upwd'],
                        'is_online'  => $userinfo['is_online'],
                        'ystatus'    => $userinfo['ystatus'],
                        'parentname' => $userinfo['parentname'],
                    );
                    // 到期日解析失败时不覆盖，避免把续费后本地已延长的 yexpired 写坏
                    $expTs = strtotime($userinfo['yexpired']);
                    if ($expTs !== false && $expTs > 0) {
                        $upData['yexpired'] = $expTs;
                    }
                    $lastOnlineTs = strtotime($userinfo['lastonline']);
                    if ($userinfo['is_online'] == 1) {
                        $upData['lastonline'] = $time;
                    } elseif ($lastOnlineTs !== false && $lastOnlineTs > 0) {
                        $upData['lastonline'] = $lastOnlineTs;
                    }

                    $userinfo['ystatus'] == 1 && $upData['status'] = 1;

                    ###判断是否下线
                    if ($uinfo['is_online'] == 1 && $userinfo['is_online'] != 1) {
                        $xxNum1++;
                    }
                    $xuser->updateUinfo($userinfo['uid'], $upData);

                } else {
                    $expTs = strtotime($userinfo['yexpired']);
                    if ($expTs === false || $expTs <= 0) {
                        $expTs = time();
                    }
                    $lastOnlineTs = strtotime($userinfo['lastonline']);
                    if ($userinfo['is_online'] == 1) {
                        $lastOnlineTs = $time;
                    } elseif ($lastOnlineTs === false || $lastOnlineTs <= 0) {
                        $lastOnlineTs = 0;
                    }
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
                        'yexpired'   => $expTs,
                        'lastonline' => $lastOnlineTs,
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
            var_dump($xxNum, $xxNum1);
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
