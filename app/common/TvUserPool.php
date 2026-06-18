<?php

namespace App\common;

use YXLib\foundation\ModelFactory;

class TvUserPool
{
    public const LIMIT_NUM = 5;

    public static function getInventory()
    {
        $model = ModelFactory::getInstance('sdk');
        $sql = "select count(a.id) as num from x_user a left join x_mac_user_map b on a.id=b.userid where b.macid is null and a.is_push=0 and a.status=1 and a.ystatus=1 and a.is_clear=0";
        $hasInfo = $model->getOne($sql);
        $dbPoolNum = intval($hasInfo['num'] ?? 0);
        $queueLength = SdkQueue::glength('pre_xuser');
        return [
            'dbPool'    => $dbPoolNum,
            'queue'     => $queueLength,
            'available' => $dbPoolNum + $queueLength,
        ];
    }

    public static function needsReplenish()
    {
        $inventory = self::getInventory();
        return $inventory['available'] < self::LIMIT_NUM;
    }
}
