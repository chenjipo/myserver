<?php 

namespace YXLib\contracts;
/**
 * 缓存类抽象接口
 */
interface CacheInterface {
    /**
     * 设置缓存
     *
     * @param [type] $name
     * @param [type] $value
     * @param integer $ttl
     * @return void
     */
    public function set($name , $value , $ttl = 0);
    /**
     * 获取缓存
     *
     * @param [type] $name
     * @return void
     */
    public function get($name );
    /**
     * 删除缓存
     *
     * @param [type] $name
     * @return void
     */
    public function delete($name );
}