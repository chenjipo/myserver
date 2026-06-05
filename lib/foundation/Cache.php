<?php
namespace YXLib\foundation;

use YXLib\contracts\CacheInterface;

Class Cache {

    private static $ins = [];

    /**
     * 获取缓存驱动实例
     * 通过app.cache配置切换缓存类型
     *
     * @param string $conn
     * @param string $driver
     * @return CacheInterface
     */
    public static function getInstance($conn = 'default', $driver = '')
    {
        if(!$driver){
            $driver = config('app.cache');
        }
        $driver = ucfirst(strtolower($driver));
        if(!is_object(self::$ins[$conn.$driver]) ){
            //驱动 memcache|file|redis
            $store = "YXLib\\foundation\\cache\\{$driver}Store";
            if(!class_exists($store)){
                Debug::log("缓存驱动{$store}无法自动加载", 'error');
                throw new \Exception("缓存驱动{$store}无法自动加载[{$store}类不存在]", 1);
            }
            $obj = new $store($conn);
            if(!$obj instanceof CacheInterface){
                Debug::log("缓存驱动{$store}无法自动加载", 'error');
                throw new \Exception("缓存驱动{$store}无法自动加载[{$store}不是CacheInterface的实例]", 1);
            }
            self::$ins[$conn.$driver] = $obj;
        }
        return self::$ins[$conn.$driver];
    }
}