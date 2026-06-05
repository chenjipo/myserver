<?php

namespace YXLib\foundation\queue;

use RedisException;
use YXLib\foundation\redis\ClsRedis;

class RedisQueue extends ClsRedis
{

    public $name = 'default';

    public function __construct($name = '')
    {
        if ($name) {
            $this->name = $name;
        }
    }

    /**
     * 队列长度
     *
     * @param [type] $key
     * @return void
     */
    public function glength($key)
    {
        try {
            return $this->lLen($key);
        } catch (RedisException $ex) {
            //写失败日志
            throw $ex;
        }
    }

    /**
     * 入队列
     *
     * @param [type] $key
     * @param [type] $data
     * @return void
     */
    public function push($key, $data)
    {
        try {
            return $this->lPush($key,$data);
        } catch (RedisException $ex) {
            //写失败日志
            throw $ex;
        }
    }

    /**
     * 出队列
     *
     * @param [type] $key
     * @return void
     */
    public function pop($key)
    {
        try {
            return $this->rPop($key);
        } catch (RedisException $ex) {
            //写失败日志
            throw $ex;
        }
    }

}
