<?php

namespace App\sdk\model;

use App\common\Table;
use YXLib\foundation\Model;

class User extends Model
{

    public $conn = 'main';
    
    public static $self;

    /**
     * Undocumented function
     *
     * @return void|User
     */
    public static function getInstance()
    {
        if (!self::$self) {
            self::$self = new self();
        }
        return self::$self;
    }

    public function getUserInfo($uid)
    {
        return $this->commonGetOne(Table::$t_user, 'uid', $uid);
    }

    public function getUserInfoById($id)
    {
        return $this->commonGetOne(Table::$t_user, 'uid', $id);
    }

    public function reg($data)
    {
        $uid = $this->insert($data, true, Table::$t_user);
        return $uid;
    }

    public function updateUinfo($uid, $data)
    {
        if(!$uid) return false;
        $this->update($data,['uid'=>$uid],Table::$t_user);
        return $this->affectedRows()>0;
    }
}
