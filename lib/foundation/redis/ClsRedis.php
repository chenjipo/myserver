<?php

namespace YXLib\foundation\redis;

use redis;
use RedisException;

abstract class ClsRedis
{

    /**
     * 实例
     */
    static $instances;

    /**
     * 连接redis
     *
     * @param string $name
     * @return redis
     */
    public function getRedis($name = 'default')
    {
        if (!extension_loaded('redis')) {
            throw new \Exception('_NOT_SUPPERT_:redis');
        }
        if (!$name) {
            throw new RedisException('缺少name配置');
        }
        if (empty(static::$instances[$name])) {
            $config = config('redis');
            $conf = explode(':', $config[$name]);
            $redis = new Redis();
            $redis->connect($conf[0], $conf[1]);
            if ($conf[2]) {
                $redis->auth($conf[2]);
            }
            static::$instances[$name] = $redis;
        }
        return static::$instances[$name];
    }

    /**
     * 重连
     *
     * @param string $name
     * @return redis
     */
    public function reConnect($name = 'default')
    {
        unset(static::$instances[$name]);
        return $this->getRedis($name);
    }

    /**
     * 判断是否可重新连接redis
     *
     * @param [type] $ex
     * @return boolean
     */
    protected function isReconnect($ex)
    {
        $shouldReconnect = false;
        if (
            strripos($ex->getMessage(), 'Redis server went away') !== false
            || strripos($ex->getMessage(), 'Connection lost') !== false
            || strripos($ex->getMessage(), 'connect failed') !== false
            || strripos($ex->getMessage(), 'read error on connection') !== false
        ) {
            $shouldReconnect = true;
        }
        return $shouldReconnect;
    }

    /**
     * 设置值
     *
     * @param [type] $name
     * @param [type] $value
     * @return void
     */
    public function set($name, $value)
    {
        $ret = false;
        $data = $this->encode($value);
        try {
            $redis = $this->getRedis($this->name);
            $ret = $redis->set($name, $data);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->set($name, $data);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * Redis 2.6.12 以上版本支持set多个操作
     * 如 $redis->set('test',1,array('ex'=>10,'nx'));
     * 见 https://redis.io/commands/set
     */
    public function nset($key, $data, $options)
    {
        $ret = false;
        $data = $this->encode($data);
        try {
            $redis = $this->getRedis($this->name);
            $ret = $redis->set($key, $data, $options);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->set($key, $data, $options);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * Redis Setex 命令为指定的 key 设置值及其过期时间。如果 key 已经存在， SETEX 命令将会替换旧的值。
     *
     * @param [type] $key
     * @param [type] $ttl
     * @param [type] $data
     * @return void
     */
    public function setEx($key, $ttl, $data)
    {
        $ret = false;
        $data = $this->encode($data);
        try {
            $ret = $this->getRedis($this->name)->setEx($key, $ttl, $data);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->setEx($key, $ttl, $data);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * Redis Setnx（SET if Not eXists） 命令在指定的 key 不存在时，为 key 设置指定的值。
     * @param string $key 键
     * @param string|array $data 值
     */
    public function setNx($key, $data)
    {
        $ret = FALSE;
        $data = $this->encode($data);
        try {
            $ret = $this->getRedis($this->name)->setNx($key, $data);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->setNx($key, $data);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * 获取缓存
     *
     * @param [type] $name
     * @return void
     */
    public function get($name)
    {
        $ret = '';
        try {
            $ret = $this->getRedis($this->name)->get($name);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->get($name);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * 获取多个key
     *
     * @param array $array
     * @return void
     */
    public function mget(array $array)
    {
        $ret = '';
        try {
            $ret = $this->getRedis($this->name)->mget($array);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->mget($array);
            } else {
                throw $ex;
            }
        }

        return $ret;
    }

    /**
     * 在队列尾部加入数据
     * @param string $key 键
     * @param string|array $data 值
     * @return boolean INT | FALSE
     */
    public function rPush($key, $data)
    {
        $ret = false;
        $data = $this->encode($data);
        try {
            $ret = $this->getRedis($this->name)->rPush($key, $data);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->rPush($key, $data);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * 在队列头部加入数据
     * @param string $key 键
     * @param string|array $data 值
     * @return boolean TRUE | FALSE
     */
    public function lPush($key, $data)
    {
        $ret = false;
        $data = $this->encode($data);
        try {
            $ret = $this->getRedis($this->name)->lPush($key, $data);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->lPush($key, $data);
            } else {
                throw $ex;
            }
        }

        return $ret;
    }

    /**
     * 从队列头部弹出数据
     * @param string $key 键
     * @param boolean $autoDis 是否需要自动分到到不同的端口上
     * @param redis $conn redis连接
     * @return string
     */
    public function lPop($key, $autoDis = false, $conn = null)
    {
        $ret = false;
        try {
            $ret = $this->getRedis($this->name)->lPop($key);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->lPop($key);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * 从队列尾部弹出数据
     * @param string $key 键
     * @return string
     */
    public function rPop($key)
    {
        $ret = false;
        try {
            $ret = $this->getRedis($this->name)->rPop($key);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->rPop($key);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * 查看指定索引值的队列值
     * @param string $key
     * @param int $index 队列的索引位置
     * @return int
     */
    public function lGet($key, $index)
    {
        $ret = null;
        try {
            $ret = $this->getRedis($this->name)->lGet($key, $index);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->lGet($key, $index);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * 获取当前队列的长度
     * @param string $key 键
     
     * @return mixed If the list didn't exist or is empty, the command returns 0. If the data type identified by Key is not a list, the command return FALSE.
     * @throws RedisException
     */
    public function lLen($key)
    {
        $ret = false;
        try {
            $ret = $this->getRedis($this->name)->lLen($key);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->lLen($key);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * lrange封装
     * @param string $key 键
     * @param string $start list起始位置
     * @param string $end 键 list结束位置
     * @return mixed If the list didn't exist or is empty, the command returns 0. If the data type identified by Key is not a list, the command return FALSE.
     * @throws RedisException
     */
    public function lRange($key, $start, $end)
    {
        $ret = false;
        try {
            $ret = $this->getRedis($this->name)->lrange($key, $start, $end);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->lLen($key, $start, $end);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * lTrim封装 截断留下索引范围内数据
     * @param string $key 键
     * @param string $start list起始位置
     * @param string $end 键 list结束位置
     * @return array    Bool return FALSE if the key identify a non-list value.
     * @throws RedisException
     */
    public function lTrim($key, $start, $end)
    {
        $ret = false;
        try {
            $ret =  $this->getRedis($this->name)->lTrim($key, $start, $end);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->lTrim($key, $start, $end);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * zAdd
     * @param string $key 键
     * @param string $start sortset起始位置
     * @param string $end 键 sortset结束位置
     
     * @return  int     Number of values added
     * @throws RedisException
     */
    public function zAdd($key, $score, $data)
    {
        $ret = FALSE;
        $data = $this->encode($data);
        try {
            $ret = $this->getRedis($this->name)->zAdd($key, $score, $data);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->zAdd($key, $score, $data);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * 判断成员元素是否是集合的成员。
     *
     * @param [type] $key
     * @param [type] $value
     * @return void
     */
    public function sIsMember($key,$value)
    {
        $ret = FALSE;
        try {
            $ret = $this->getRedis($this->name)->sIsMember($key,$value);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->sIsMember($key,$value);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * zRevRange封装 返回指定索引内数据，分数从高到低
     * @param string $key 键
     * @param string $start sortset起始位置
     * @param string $end 键 sortset结束位置
     * @param int $withSore 键 返回分数
     
     
     * @return  array   Array containing the values in specified range.
     * @throws RedisException
     */
    public function zRevRange($key, $start, $end, $withSore = false)
    {
        $ret = false;
        try {
            $ret = $this->getRedis($this->name)->zRevRange($key, $start, $end, $withSore);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->zRevRange($key, $start, $end, $withSore);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * zRangeByScore封装 获取指定分数范围内数据
     * @param string $key 键
     * @param string $min sortset分数起始位置
     * @param string $max 键 sortset分数结束位置
     * @return  array   Array containing the values in specified range.
     * @throws RedisException
     */
    public function zRangeByScore($key, $min, $max)
    {
        $ret = false;
        try {
            $ret = $this->getRedis($this->name)->zRangeByScore($key, $min, $max);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->zRangeByScore($key, $min, $max);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /*
     * 根据key获取field排名
     * @param string $key 键
     * @param string $key 域
     * @throws RedisException
     * @return int    the item's score
     */
    public function zRevRank($key, $field)
    {
        $ret = '';
        try {
            $ret = $this->getRedis($this->name)->zRevRank($key, $field);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->zRevRank($key, $field);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /*
     * 根据key获取field分数
     * @param string $key 键
     * @param string $key 域
     * @return  float
     */
    public function zScore($key, $field)
    {
        $ret = '';
        try {
            $ret = $this->getRedis($this->name)->zScore($key, $field);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->zScore($key, $field);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * 移除有序集中，指定分数（score）区间内的所有成员
     * 返回被移除成员的数量。
     * @param [type] $key
     * @param [type] $min
     * @param [type] $max
     * @return int
     */
    public function zRemRangeByScore($key, $min, $max)
    {
        $ret = 0;
        try {
            $ret = $this->getRedis($this->name)->zRemRangeByScore($key, $min, $max);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->zRemRangeByScore($key, $min, $max);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }


    /**
     * 设置hash表键值
     * @param string $key 键
     * @param string|array $field 域
     * @param string $value 值
     
     
     * @return boolean TRUE | FALSE
     */
    public function hSet($key, $field, $value)
    {

        $ret = FALSE;
        $data = $this->encode($value);
        try {
            $ret = $this->getRedis($this->name)->hSet($key, $field, $data);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->hSet($key, $field, $data);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * 设置hash表多个键值
     * $redis->hMset('user:1', array('name' => 'Joe', 'salary' => 2000));
     * @param string $key 键名
     * @param array $value 键值对
     * @return boolean TRUE | FALSE
     */
    public function hMset($key, $hashKeys)
    {
        $ret = FALSE;
        try {
            $ret = $this->getRedis($this->name)->hMset($key, $hashKeys);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->hMset($key, $hashKeys);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * 获取hash表多个键值
     * $redis->hMGet('h', array('field1', 'field2'));
     */
    public function hMGet($key, $hashKeys)
    {

        $ret = false;
        try {
            $ret = $this->getRedis($this->name)->hMGet($key, $hashKeys);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->hMGet($key, $hashKeys);
            } else {
                throw $ex;
            }
        }

        return $ret;
    }

    /**
     * 判断hset中是否存在field值
     * @param string $key 键
     * @param string $key 域
     
     
     * @return string
     */
    public function hExists($key, $field)
    {
        $ret = false;
        try {
            $ret = $this->getRedis($this->name)->hExists($key, $field);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->hExists($key, $field);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * 删除hset中某个field值
     */
    public function hDel($key, $field)
    {
        $ret = false;
        try {
            $ret = $this->getRedis($this->name)->hDel($key, $field);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->hDel($key, $field);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * 根据key获取hash表值
     * @param string $key 键
     * @param string $key 域
     * @return string
     */
    public function hGet($key, $field)
    {
        $ret = '';
        try {
            $ret = $this->getRedis($this->name)->hGet($key, $field);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->hGet($key, $field);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * 根据key获取hash表键
     * @param string $key 键
     * @return string
     */
    public function hKeys($key)
    {
        $ret = '';
        try {
            $ret = $this->getRedis($this->name)->hKeys($key);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->hKeys($key);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * 根据key获取hash表值
     * @param string $key 键
     * @return string
     */
    public function hVals($key)
    {

        $ret = '';
        try {
            $ret = $this->getRedis($this->name)->hVals($key);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->hVals($key);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * 根据key获取hash表所有的值
     * @param string $key 键
     * @return string
     */
    public function hGetAll($key)
    {
        $ret = false;
        try {
            $ret = $this->getRedis($this->name)->hGetAll($key);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->hGetAll($key);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }


    /**
     * 获取hash表的长度
     * @param string $key 键
     * @return mixed If the list didn't exist or is empty, the command returns 0. If the data type identified by Key is not a list, the command return FALSE.
     * @throws RedisException
     */
    public function hLen($key)
    {
        $ret = false;
        try {
            $ret = $this->getRedis($this->name)->hLen($key);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->hLen($key);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    public function hIncrBy($key, $hashKey, $value, $autoDis = false, $conn = null)
    {
        $ret = false;
        try {
            $ret = $this->getRedis($this->name)->hIncrBy($key, $hashKey, $value);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->hIncrBy($key, $hashKey, $value);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    //---------------------集合操作----------------------//
    /**
     * 集合存在则往集合中添加元素，不存在则创建并且添加元素
     *
     * @param [type] $key
     * @param [type] $data
     * @return void
     */
    public function sAdd($key, $data)
    {

        $ret = FALSE;
        try {
            $ret = $this->getRedis($this->name)->sAdd($key, $data);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->sAdd($key, $data);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * 返回集合中元素的数量
     *
     * @param [type] $key
     * @return void
     */
    public function sCard($key)
    {
        $ret = FALSE;
        try {
            $ret = $this->getRedis($this->name)->sCard($key);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->sCard($key);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * 返回集合元素
     * @author huangkenan
     * @param unknown $key 集合key
     * @param boolean $autoDis
     * @param unknown $conn
     * @throws RedisException
     * @return unknown
     */
    public function sMembers($key)
    {
        $ret = FALSE;
        try {
            $ret = $this->getRedis($this->name)->sMembers($key);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->sMembers($key);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * 删除集合某元素
     */
    public function sRem($key, $member)
    {
        $ret = FALSE;
        try {
            $ret = $this->getRedis($this->name)->sRem($key, $member);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->sRem($key, $member);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * set交集
     * @param $key1
     * @param $key2
     * @param bool $autoDis
     * @param null $conn
     * @return bool
     * @throws RedisException
     */
    public function sInter($key1, $key2)
    {
        $ret = FALSE;
        try {
            $ret = $this->getRedis($this->name)->sInter($key1, $key2);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->sInter($key1, $key2);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * 设置key的过期时间戳,指定时间戳后过期
     * @param string $key
     * @param int $expireVl 过期时间（过期的时间戳）
     * @return boolean TRUE | FALSE
     */
    public function expireAt($key, $expireVl)
    {
        $ret = FALSE;
        try {
            $ret = $this->getRedis($this->name)->expireAt($key, $expireVl);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->expireAt($key, $expireVl);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * 设置key的过期时间,当前时间后多少秒后过期
     * @param string $key
     * @param int $expire 过期时间（过期的时间戳）
     * @return boolean TRUE | FALSE
     */
    public function expire($key, $expire)
    {

        $ret = FALSE;
        try {
            $ret = $this->getRedis($this->name)->expire($key, $expire);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->expire($key, $expire);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * redis incr
     */
    public function incr($key)
    {
        $ret = FALSE;
        try {
            $ret = $this->getRedis($this->name)->incr($key);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->incr($key);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * redis incrBy
     */
    public function incrBy($key, $value)
    {
        $ret = FALSE;
        try {
            $ret = $this->getRedis($this->name)->incrBy($key, $value);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->incrBy($key, $value);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * redis incr
     */
    public function decr($key)
    {
        $ret = FALSE;
        try {
            $ret = $this->getRedis($this->name)->decr($key);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->decr($key);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * redis decrBy
     */
    public function decrBy($key, $value)
    {
        $ret = FALSE;
        try {
            $ret = $this->getRedis($this->name)->decrBy($key, $value);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->decrBy($key, $value);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * 测试与服务器的连接是否仍然生效，或者用于测量延迟值。
     * @param Bollean $reconnect
     * @param Reids $conn redis 连接
     * @return type
     * @throws RuntimeException
     * @return string 
     */
    public function ping($reconnect = false)
    {
        try {
            $pong = $this->getRedis($this->name)->ping();
        } catch (RedisException $ex) {
            if (!$reconnect) {
                throw $ex;
            }
            $pong = $this->reConnect($this->name)->ping();
        }
        return $pong;
    }

    /**
     * 删除key
     *
     * @param [type] $name
     * @return void
     */
    public function delete($name)
    {
        $ret = false;
        try {
            $ret = $this->getRedis($this->name)->delete($name);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->delete($name);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * 获取键值的过期时间
     * @param string $key
     * @param redis $conn
     * @return long int 返回key存活的时间（单位为 s） ， -1 （如果没有设置过期时间）, -2 （键不存在）.
     * @throws RedisException
     */
    public function ttl($key)
    {
        $ret = 0;
        try {
            $ret = $this->getRedis($this->name)->ttl($key);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->ttl($key);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * PIPELINE 执行
     * @param      $key
     * @return bool
     * @throws RedisException
     */
    public function pipe($key)
    {
        $ret = false;
        try {
            $ret =  $this->getRedis($this->name)->multi(Redis::PIPELINE);
        } catch (RedisException $ex) {
            if ($this->isReconnect($ex)) {
                $ret = $this->reConnect($this->name)->multi($key);
            } else {
                throw $ex;
            }
        }
        return $ret;
    }


    /**
     * 编码
     * @param mixed $data
     */
    protected function encode($data)
    {
        return is_array($data) ? json_encode($data) : $data;
    }
}
