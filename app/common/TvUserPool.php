<?php

namespace App\common;

use YXLib\foundation\ModelFactory;

class TvUserPool
{
    /** 队列目标数量：低于此值时从空闲池入队 */
    public const LIMIT_NUM = 5;

    /** 开新号阈值：空闲池(可入队口径)+队列 低于此值才注册 */
    public const REPLENISH_LIMIT = 8;

    /** 与入队一致：距过期至少剩余秒数 */
    public const QUEUE_MIN_REMAIN_SECONDS = 86400;

    public static function getInventory()
    {
        $model = ModelFactory::getInstance('sdk');
        $time = time();
        $minRemain = self::QUEUE_MIN_REMAIN_SECONDS;
        // 与 CheckTvUserTool::fetchQueueCandidates 同口径，避免过期/临期号挡住开新号
        $sql = "select count(a.id) as num from x_user a left join x_mac_user_map b on a.id=b.userid where b.macid is null and a.is_push=0 and a.is_online=0 and a.status=1 and a.ystatus=1 and a.is_clear=0 and a.yexpired-{$minRemain}>={$time}";
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
        return $inventory['available'] < self::REPLENISH_LIMIT;
    }
}
