<?php
namespace App\admin\model;

use App\common\Table;
use YXLib\foundation\Model;
use YXLib\support\Util;

class ModAuth extends Model{

    public function __construct(){
        $this->conn = 'admin';
    }

    /**
     * 获取登录信息
     * @param $user
     * @return array|bool|resource|string
     */
    public function getLoginInfo($user){
        $sql = "select * from ".Table::$admin_user." where `user`=:user";
        return $this->getOne($sql,array('user'=>$user));
    }

    /**
     * 更新登录信息
     * @param $user
     * @return bool
     */
    public function updateLoginInfo($user){
        $this->update(array('last_ip'=>Util::getIp(),'last_lt'=>time()),array('user'=>$user),Table::$admin_user);
        return true;
    }


}