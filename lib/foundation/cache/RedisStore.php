<?php

namespace YXLib\foundation\cache;

use YXLib\contracts\CacheInterface;
use YXLib\foundation\redis\ClsRedis;

class RedisStore extends ClsRedis implements CacheInterface
{
    public $name = '';
    
    public function __construct($name = 'default')
    {
        $this->name = $name;
    }

    public function set($name , $value , $ttl = 0)
    {
        return $this->setEx($name,$ttl,$value);
    }

}
