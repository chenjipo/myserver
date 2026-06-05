<?php
namespace YXLib\foundation;


use YXLib\YX;
/**
 * 配置管理类
 * 配置文件存放于
 * 目录/config 只读
 * 目录/runtime/config 读写
 * by fwt 2021-04-13
 */
class Config{

    public static $loaded = [];

    public static $config = [];

    /**
     * 获取配置文件 支持读取子配置
     * Config::read('aa.bb.cc')
     * aa为配置文件名称
     * bb、cc为配置文件数组里的key
     * @param [type] $name 名
     * @param boolean $isCache 是否是runtime里的config
     * @return void
     */
	public static function read($name,$isCache = false)
	{
        if(!$name) return false;
        $split = [];
		if(strpos($name,'.') !== false){
			$split = explode('.',$name);
            $name = array_shift($split);
		}
		if ($isCache) {
			$file = ROOT . '/runtime/config/' . $name . '.php';
		}else {
            $file = ROOT . '/runtime/config/'.YX::$configCacheFileName.'.php';
            if(!file_exists($file)){
                $file = ROOT . '/config/' . $name . '.php';
            }else{
                array_unshift($split,$name);
            }
        }
		//读取
		$value = self::load($file);
        //读取子配置
        if(!empty($split)){
            $value = array_reduce($split,function($config,$key) {
                if(!isset($config[$key])) return [];
                return $config[$key];
            },$value);
        }
		return $value;
	}

    /**
     * 加载配置文件到静态数组
     *
     * @param [type] $file
     * @return void
     */
    private static function load($file)
    {
        $key = md5($file);
        if(isset(self::$config[$key])){
            return self::$config[$key];
        }
        if(!is_file($file)){
            Debug::log('配置文件：' . $file . '不存在，返回false', 'warn');
            return false;
        }
        $value = include_once $file;
        self::$config[$key] = $value;
        return $value;
    }

    /**
     * 生成配置文件 写入
     *
     * @param [type] $name
     * @param array $config
     * @return void
     */
	public static function write($name,array $config = [])
	{
        if(!$name) return false;
        $dir = ROOT . '/runtime/config/';
        if(!is_dir($dir)){
            if(!mkdir($dir, 0777, true)){
                Debug::log('配置文件：' . $dir . '创建目录失败', 'warn');
            }
        }
        $save = var_export($config, true);
        $save = "<?php\r\n/*本文件由程序自动生成（" . date('Y-m-d H:i:s') . "）*/\r\nreturn {$save};";
        $file = $dir . $name;
        $re = file_put_contents($file, $save);
        if (!$re) {
            Debug::log('配置文件：' . $file . '写入失败', 'warn');
        }
        return $re;
	}
}