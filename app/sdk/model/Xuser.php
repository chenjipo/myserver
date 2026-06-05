<?php

namespace App\sdk\model;

use App\common\Table;
use YXLib\foundation\Model;

class Xuser extends Model
{

    public $conn = 'sdk';
    
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
        return $this->commonGetOne(Table::$x_user, 'uid', $uid);
    }

    public function getUserInfoById($id)
    {
        return $this->commonGetOne(Table::$t_user, 'uid', $id);
    }

    public function reg($data)
    {
        $uid = $this->insert($data, true, Table::$x_user);
        return $uid;
    }

    public function addUser($data)
    {
        $uid = $this->ignoreInsert($data, false, Table::$x_user);
        return $uid;
    }

    public function updateUinfo($uid, $data)
    {
        if (!$uid) return false;
        $this->update($data, ['uid' => $uid], Table::$x_user);
        return $this->affectedRows() > 0;
    }

    public function updateUinfoById($id, $data)
    {
        if (!$id) return false;
        $this->update($data, ['id' => $id], Table::$x_user);
        return $this->affectedRows() > 0;
    }
}
