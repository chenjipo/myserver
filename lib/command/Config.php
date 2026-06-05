<?php 

namespace YXLib\command;

use YXLib\YX;
use YXLib\foundation\Response;
class Config {

    public function handle($params)
    {
        if(in_array($params['opt'],['cache','clear'])){
            return $this->{$params['opt']}();
        }
    }

    public function cache()
    {
        $whole = [];
        $dir = ROOT .'/config';
        $file_arr = scandir($dir);
        foreach($file_arr as $name){
            if($name!=".." && $name !="."){
                $file = $dir."/".$name;
                if(is_file($dir."/".$name) && substr($name,-4) == '.php'){
                    $whole[substr($name,0,-4)] = include $file;
                }
            }
        }
        $ret = config_write(YX::$configCacheFileName.'.php',$whole);
        $response = new Response;
        if($ret){
            return $response->make("cache config success! ".PHP_EOL);
        }
        return $response->make("cache config fail!".PHP_EOL);
    }

    public function clear()
    {
        $response = new Response;
        $file = ROOT .'/runtime/config/'.YX::$configCacheFileName.'.php';
        if(is_file($file)) {
            $ret = unlink($file);
            if(!$ret) {
                return $response->make("clear config fail! ".PHP_EOL);
            }
        }
        return $response->make("clear config success! ".PHP_EOL);
    }
    
}