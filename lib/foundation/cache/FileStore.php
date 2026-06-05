<?php
namespace YXLib\foundation\cache;

use YXLib\contracts\CacheInterface;
use YXLib\foundation\Debug;

class FileStore implements CacheInterface{
	/**
	 * 缓存目录
	 * @var string
	 */
	private static $msCachePath  = null;
	/**
	 * 默认缓存失效时间(1小时)
	 * @var int
	 */
	const EXPIRE = 0;
 
 
	/**
	 * 构造
	 * self::$msCachePath 缓存目录为共享目录
	 * @param string $name
	 */
	function __construct($name = 'default')
	{
		$config = config('file');
		self::$msCachePath = $config[$name]['path'];
	}
 
	/**
	 * 读取缓存
	 * 返回: 缓存内容,字符串或数组；缓存为空或过期返回null
	 * @param string $sKey 缓存键值(无需做md5())
	 * @return string | null
	 * @access public
	 */
	public function get($sKey)
	{
		if(empty($sKey))
			return false;
		$sFile  = self::getFileName($sKey);
		if(!file_exists($sFile))
			return false;
		else
		{
			$handle = fopen($sFile,'rb');
			$expire = intval(fgets($handle));
			if ( $expire === 0 || $expire > time())//检查时间戳
			{	//未失效期，取出数据
				$sData = fread($handle, filesize($sFile));
				fclose($handle);
				return unserialize($sData);
			}
			else
			{	//已经失效期
				fclose($handle);
				unlink($sFile);
				return false;
			}
		}
	}
 
	/**
	 * 写入缓存
	 *
	 * @param string $sKey 缓存键值
	 * @param mixed $mVal 需要保存的对象
	 * @param int $iExpire 失效时间
	 * @return bool
	 * @access public
	 */
	public function set($sKey, $mVal, $iExpire=null)
	{
		if(empty($sKey))
			return false;
		$sFile = self::getFileName($sKey);
		if (!file_exists(dirname($sFile)))
			if (!self::is_mkdir(dirname($sFile)))
				return false;
		$aBuf = array();
		if($iExpire){
			$aBuf[] = time() + intval($iExpire);
		}else{
			$aBuf[] = self::EXPIRE;
		}
		$aBuf[] = serialize($mVal);
		$handle = fopen($sFile,'wb');
		fwrite($handle, implode("\n", $aBuf));
		fclose($handle);
		return true;
	}
 
	/**
	 * 删除指定的缓存键值
	 *
	 * @param string $sKey 缓存键值
	 * @return bool
	 */
	public function delete($sKey)
	{
		if(empty($sKey))
			return false;
		else
		{
			$sFile = self::getFileName($sKey);
			if (file_exists($sFile)){
				unlink($sFile);
			}
			return true;
		}
	}
 
	/**
	 * 获取缓存文件全路径
	 * 返回: 缓存文件全路径
	 * $sKey的值会被转换成md5(),并分解为3级目录进行访问
	 * @param string $sKey 缓存键
	 * @return string
	 * @access protected
	 */
	private static function getFileName($sKey)
	{
		if(empty($sKey))
			return false;
		$key_md5 = md5($sKey);
		$aFileName = array();
		$aFileName[]  = rtrim(self::$msCachePath,'/');
		$aFileName[]  = $key_md5{0} . $key_md5{1};
		$aFileName[]  = $key_md5{2} . $key_md5{3};
		$aFileName[]  = $key_md5{4} . $key_md5{5};
		$aFileName[]  = $key_md5;
		return implode('/', $aFileName);
	}
 
	/**
	 * 创建目录
	 *
	 * @param string $sDir
	 * @return bool
	 */
	private static function is_mkdir($sDir='')
	{
		if(empty($sDir))
			return false;
 
		if(!mkdir($sDir, 0755, true))
			return false;
		else
			return true;
	}
}
?>