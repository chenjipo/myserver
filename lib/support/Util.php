<?php
namespace YXLib\support;

use YXLib\foundation\Debug;
/**
 * 工具箱
 * User: fwt
 * Date: 2014-11-06
 */
class Util
{

    /**
     * 防xss
     * @param $string
     * @param bool|False $low
     * @return bool
     */
    public static function clean_xss(&$string, $low = False)
    {
        if (! is_array ( $string ))
        {
            $string = trim ( $string );
            $string = strip_tags ( $string );
            $string = htmlspecialchars ( $string );
            if ($low)
            {
                return True;
            }
            $string = str_replace ( array ('"', "\\", "'", "/", "..", "../", "./", "//" ), '', $string );
            $no = '/%0[0-8bcef]/';
            $string = preg_replace ( $no, '', $string );
            $no = '/%1[0-9a-f]/';
            $string = preg_replace ( $no, '', $string );
            $no = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';
            $string = preg_replace ( $no, '', $string );
            return True;
        }
        $keys = array_keys ( $string );
        foreach ( $keys as $key )
        {
            self::clean_xss ( $string [$key], $low);
        }
    }

    public static function request($url, $data = array(), $timeout = 7, $userHeader = array(), $returnInfo = false)
    {
        $url = trim($url);
        if (empty($url)) {
            return array('code' => '0');
        }
        $curl = curl_init();
        $header = array(
            'Accept-Language: zh-cn',
            'Connection: Keep-Alive',
            'Cache-Control: no-cache'
        );

        if ($userHeader) {
            $header = array_merge($header, $userHeader);
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.95 Safari/537.36');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

        if ($timeout > 0) {
            curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
        }

        curl_setopt($curl, CURLINFO_HEADER_OUT, true);//header
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);//自动跳转

        $isPost = false;
        if (!empty($data)) {
            $isPost = true;
        }
      
        if($isPost && is_array($data)){
            curl_setopt($curl, CURLOPT_POST, true);//这玩意一定要写在CURLOPT_POSTFIELDS前面
            if(is_array($data)) $data = http_build_query($data);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        $result['result'] = curl_exec($curl);
        $result['code'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($result['result'] === false) {
            $result['result'] = curl_error($curl);
            $result['code'] = -curl_errno($curl);
        }

        if($returnInfo){
            $get_info = curl_getinfo($curl);
            $result['response_code'] = $get_info['http_code']; //返回http状态
            $result['info'] = $get_info; //返回所有相关信息
            $result['error_no'] = curl_errno($curl); //返回错误状态
            $result['error_content'] = curl_error($curl); //返回错误内容
        }

        curl_close($curl);
        return $result;
    }

    public static function requestJSON($url, $data = array(), $timeout = 7, $userHeader = array())
    {
        $url = trim($url);
        if (empty($url)) {
            return array('code' => '0');
        }
        $curl = curl_init();
        $header = array(
            'Accept-Language: zh-cn',
            'Connection: Keep-Alive',
            'Cache-Control: no-cache',
            'Content-Type: application/json',
        );

        if (!empty($hostIp)) {
            $urlInfo = parse_url($url);
            $url = str_replace($urlInfo['host'], $hostIp, $url);
            $header[] = "Host: {$urlInfo['host']}";
        }
        if ($userHeader) {
            $header = array_merge($header, $userHeader);
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.95 Safari/537.36');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

        if ($timeout > 0) {
            curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
        }

        curl_setopt($curl, CURLINFO_HEADER_OUT, true);//header
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);//自动跳转


        $isPost = false;
        if (!empty($data)) {
            $isPost = true;
        }

        if($isPost){
            curl_setopt($curl, CURLOPT_POST, true);//这玩意一定要写在CURLOPT_POSTFIELDS前面
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        $result['result'] = curl_exec($curl);
        $result['code'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($result['result'] === false) {
            $result['result'] = curl_error($curl);
            $result['code'] = -curl_errno($curl);
        }
        curl_close($curl);
        return $result;
    }

	public static function getIp()
	{
		static $clientip = null;
		if ( $clientip !== null ) {
			return $clientip;
		} 
		$clientip = '0.0.0.0';
		$keys = array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_FROM', 'REMOTE_ADDR' );
		foreach ( $keys as $key ) {
			if ( isset( $_SERVER[$key] ) ) {
				if ( ! preg_match( '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $_SERVER[$key] ) ) {
					continue;
				} 
				$clientip = $_SERVER[$key];
			} 
		} 
		return $clientip;
	}

	//  获得唯一的SessionID
	public static function getSid()
	{
		return base_convert(sprintf('%u',
			crc32(self::getIp() . ' ' . (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''))), 10, 36) .
		'-' . base_convert(microtime(true) * 100, 10, 36) .
		'-' . base_convert(mt_rand(0, 38885), 10, 36);
	}

    /**
     * 字符串hash成数值
     * @param $secret 字符串
     * @param int $num 最大值
     * @return float hash值
     */
    public static function getHash($secret,$num = 100){
        $count = 0;
        $max = strlen($secret) >= 8 ? 8 : strlen($secret);
        $hashSeeds = str_split(substr($secret,0,$max),1);
        if(is_array($hashSeeds)){
            foreach($hashSeeds as $char){
                $count += ord($char);
            }
        }
        return floor($count % $num);
    }

    /**
     * 数值hash成数值
     * @param $secret 原始数值
     * @param int $num 最大值
     * @return float hash值
     */
    public static function getIntHash($int,$num = 100){
        return floor($int % $num);
    }

    public static function checkUA(){
    	$ua = $_SERVER['HTTP_USER_AGENT'];
    	if(stripos($ua, 'Mobile') === false || stripos($ua, 'iPad') !== false){
    		return 'PC';
    	} else {
	    	if(stripos($ua, 'Android') !== false){
	    		return 'Android';
	    	}
	    	if(stripos($ua, 'iPhone') !== false){
	    		return 'IOS';
	    	}
	    	return 'PC';
    	}
    }
    public static function GUID(){
        if (function_exists('com_create_guid') === true)
        {
            return strtolower(trim(com_create_guid(), '{}'));
        }

        return strtolower(sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535)));
    }

    public static function isEmail($data){
        $data = trim($data);
        return preg_match("/^\w+([\.-]\w+)*@\w+([\.-]\w+)*\.\w+([-\.]\w+)*$/", $data);
    }


	/**
	 * 检查字符串是否是UTF8状态
	 * @param $str
	 * @return int
	 */
	public static function isUtf8($str)
	{
		return preg_match('%^(?:
		 [\x09\x0A\x0D\x20-\x7E]            # ASCII
	   | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
	   |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
	   | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
	   |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
	   |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
	   | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
	   |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
        )*$%xs', $str);
	}
	/*
	 * 转成UTF8
	 */
	public static function forceUtf8($org)
	{
		if (!is_array($org)) {
			return self::isUtf8($org) ? $org : iconv('GBK', 'UTF-8', $org);
		} else {
			foreach ($org as $k => $v) {
				$org[$k] = self::forceUtf8($v);
			}
			return $org;
		}
	}

	/*
	 * 追加到文件中
	 */
	public static function file_append_contents($filename, $str) {
		if (strlen($str) <= 8192) {
			return file_put_contents($filename, $str, FILE_APPEND);
		}

		$fp = fopen($filename, 'a');
		if (!empty($fp)) {
			stream_set_chunk_size($fp, 2147483647);
			fwrite($fp, $str);
			fclose($fp);
			return true;
		}else{
			return false;
		}
	}

	public static function myTrim($data){
		if(is_array($data)){
			foreach($data as &$_data){
				$_data = self::myTrim($_data);
			}
		}elseif(is_string($data)){
			$data = trim($data);
		}
		return $data;
	}

    public static function getRandStr($length = 32){
    	$str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $len = strlen($str)-1;
        $randstr = '';
        for ($i=0;$i<$length;$i++) {
            $num=mt_rand(0,$len);
            $randstr .= $str[$num];
        }
        return $randstr;
    }

	public static function getSalt($len){
		if($len > 32){
			$len = 32;
		}
		return substr(md5(microtime(true) . mt_rand(100000, 999999)), 0, $len);
	}

    public static function strSplit($string, $len=1){
        $start = 0;
        $str_len = mb_strlen($string);
        while($str_len){
            $array[] = mb_substr($string,$start,$len,"utf8");
            $string = mb_substr($string, $len, $str_len,"utf8");
            $str_len = mb_strlen($string);
        }
        return $array;
    }

    public static function getRequestUrlNoXss(){
        $script = $_SERVER['SCRIPT_NAME'];
        $param = trim($_SERVER['QUERY_STRING']);
        if($param == ''){
            return $script;
        }
        $params = explode('&',$param);
        foreach($params as &$p){
            self::clean_xss($p);
        }
        $param = join('&',$params);
        return $script.'?'.$param;
    }

    public static function makeRandomString(){
        $time = explode('.',microtime(true));
        $time[1] = str_pad(substr($time[1],0,3),3,0,STR_PAD_RIGHT);
        $order = date('y',$time[0])*12+date('m',$time[0]);
        $order .= date('d',$time[0]);
        $order .= substr($time[0],5,5);
        $order .= $time[1];
        return $order;
    }

    public static function isAjax()
    {
        if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest"){ 
            return true;
        }
        return false;
    }

}
