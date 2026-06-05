<?php

namespace YXLib\foundation\redis;

use YXLib\foundation\redis\ClsRedis;

/**
 * Redis 功能类
 */
class LibRedis extends ClsRedis
{
    public $name = '';
    
    public function __construct($name = 'default')
    {
        $this->name = $name;
    }
}
