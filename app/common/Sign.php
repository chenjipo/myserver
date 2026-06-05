<?php

namespace App\common;

use YXLib\foundation\Debug;

/**
 * 接口签名 生成、验签
 */
class Sign{

    /**
     * 生成key
     *
     * @param [type] $data
     * @param [type] $key
     * @return string
     */
    public static function generate(array $data, $key)
    {
        ksort($data);
        return md5($key . http_build_query($data));
    }

     /**
     * 生成key
     *
     * @param [type] $data
     * @param [type] $key
     * @return string
     */
    public static function generatenew(array $data, $key)
    {
        if(empty($data)){
            return false;
        }
        ksort($data);
        $signStr = [];
        foreach($data as $k=>$p){
            $signStr[] = $k .'='. $p;
        }
        $signStr = implode('&',$signStr);
        $_sign = md5( $key . $signStr);
        return $_sign;
    }

    /**
     * 验证签名
     *
     * @param [type] $data
     * @param [type] $key
     * @param [type] $sign
     * @return bool
     */
    public static function verify(array $data, $key, $sign)
    {
        if(empty($data)){
            return false;
        }
        ksort($data);
        $signStr = [];
        foreach($data as $k=>$p){
            $signStr[] = $k .'='. $p;
        }
        $signStr = implode('&',$signStr);
        $signStr2 = urldecode($signStr);
        // Debug::log($key .$signStr);
        // Debug::log($key .$signStr2);
        $_sign = md5( $key . $signStr);
        $_sign2 = md5( $key . $signStr2);
        if($_sign !== strtolower($sign)){
            if($_sign2 !== strtolower($sign)){
                // Debug::log($_sign2 .'---'.$sign);
                return false;
            }
        }
        return true;
    }
}