<?php

namespace App\sdk\console;

use App\common\DeviceServer;
use App\sdk\model\User;

class UserTool {

    public function handle($params){
        var_dump($params);
        if($params['opt']){
            return $this->{$params['opt']}($params);
        }
    }


    public function setMember($params){
        $uid = $params['uid'];
        $vipTime = $params['vip_time'];
        $mod = new User();
        $uinfo =$mod->getUserInfoById($uid);
        if(!$uinfo){
            return false;
        }
        $update = [
            'orderid'=>'',
            'vip_etime'=> $vipTime,
        ];
        $ret = User::getInstance()->updateUinfo($uid,$update);
        if(!$ret){
            return false;
        }
        $uinfo = array_merge($uinfo,$update);
        $userDeviceService = new DeviceServer();
        return $userDeviceService->setDevInfo($uinfo['duid'],$uinfo);
    }

    public function unsetMember($params){
        $uid = $params['uid'];
        $mod = new User();
        $uinfo =$mod->getUserInfoById($uid);
        var_dump($uinfo);
        if(!$uinfo){
            return false;
        }
        $update = [
            'orderid'=>'1',
            'vip_etime'=> 0,
        ];
        $ret = User::getInstance()->updateUinfo($uid,$update);
        if(!$ret){
            return false;
        }
        $uinfo = array_merge($uinfo,$update);
        $userDeviceService = new DeviceServer();
        return $userDeviceService->setDevInfo($uinfo['duid'],$uinfo);
    }
}