<?php

namespace App\sdk\model;

use App\common\Table;
use YXLib\foundation\Model;
use YXLib\foundation\cache\RedisStore;

class Vote extends Model
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

    /**
     * @param array $where
     * @return bool
     */
    public function isRelation(array $where)
    {
        $res = $this->commonGetOne(Table::$t_vote, $where);
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    public function relation($linkId, $mac, $way, $category)
    {
        $way = strtolower($way);
        $data = ['MAC' => $mac, 'LINK_ID' => $linkId, 'VOTE_TYPE' => $way, 'TYPE' => $category];
        if ($this->isRelation($data)) {
            return true;
        }

        $sdkCache = new RedisStore('sdk');
        $sdkCache->incr(sprintf("xlplayer:sdk:mac#%s_%s_count_%s", $category, $way, $mac));
        $data['STATE'] = 1;
        $data['ATIME'] = time();
        if (!$this->ignoreInsert($data, false, Table::$t_vote)) {
            return false;
        }
        return true;
    }
}
