<?php

return [
    'queueLimitNum'     => 5,  // 队列目标数量（TvUserPool::LIMIT_NUM）
    'replenishLimitNum' => 8,  // 开新号阈值：可入队空闲池+队列（TvUserPool::REPLENISH_LIMIT）
    'userPool'          => 100,
];
