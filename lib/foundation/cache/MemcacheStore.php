<?php 

namespace YXLib\foundation\cache;

use YXLib\contracts\CacheInterface;

/***
 * 两种使用方式：
 * 1、$ins = new MemcacheStore('default') 获取当前类实例 推荐
 * 2、$ins = MemcacheStore::connect('default'); 获取原生mc实例
 */
class MemcacheStore implements CacheInterface{
    /**
     * 配置名
     *
     * @var [type]
     */
    private $name;
    /**
     * 实例数组
     *
     * @var array
     */
    private static $instances = [];

    public function __construct($name = 'default')
    {
        $this->name = $name;
    }

    /**
     * 当前实例
     *
     * @var [type]
     */
    private static $instance;

    public static function connect($name = 'default')
    {
        if(empty(static::$instances[$name])){
            $config = config('memcache');
            if(empty($config[$name])){
                throw new \Exception('缺少name配置');
            }
            $_config = $config[$name];
            $_instance = new \Memcached();
            if(is_string($_config)){
                $_config = array($_config);
            }
            foreach($_config as $conf){
                $conf = explode(':', $conf);
                if(empty($conf[1])){
                    $conf[1] = 11211;
                }
    
                $_instance->addServer($conf[0], $conf[1]);
            }
    
            static::$instances[$name] = $_instance;
            static::$instance = $_instance;
        }

        return static::$instance;
    }

    public function set($name , $value , $ttl = 0){
        return self::connect($this->name)->set($name,$value,$ttl);
    }

    public function get($name ){
        return self::connect($this->name)->get($name);
    }

    public function delete($name ){
        return self::connect($this->name)->delete($name);
    }
}