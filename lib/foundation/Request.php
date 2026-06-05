<?php
/**
 * 请求类
 */
namespace YXLib\foundation;

use YXLib\exceptions\ErrorMethodException;
class Request {

    public static $self = null;

    /**
     * 获取单实例
     *
     * @return Request
     */
    public static function getInstance()
    {
        if(is_null(self::$self)){
            self::$self = new self();
        }

        return self::$self;
    }

    /**
	 * REQUEST值
	 *
	 * @param mixed $name 支持字符串或者数组，如果是数据，需要递归检查，默认什么都不过滤
	 * @param string $type 1) int integer 整形；2) float 浮点型；3) json传过来的数据为json数据；4) /.../需要符合正则；
	 * @param string $default 如果取不到，默认值
	 * @param string $low 默认去除xss攻击-是否简单
	 * @return string
	 */
	public function R($name, $type = '', $default = '', $low = false){
		return $this->getValue($name, $_REQUEST, $type, $default, $low);
	}

	/**
	 * GET值
	 *
	 * @param mixed $name 支持字符串或者数组，如果是数据，需要递归检查，默认什么都不过滤
	 * @param string $type 1) int integer 整形；2) float 浮点型；3) json传过来的数据为json数据；4) /.../需要符合正则；
	 * @param string $default 如果取不到，默认值
	 * @param string $low 默认去除xss攻击-是否简单
	 * @return string
	 */
	public function get($name, $type = '', $default = '', $low = false){
		return $this->getValue($name, $_GET, $type, $default, $low);
	}

	/**
	 * POST值
	 *
	 * @param mixed $name 支持字符串或者数组，如果是数据，需要递归检查，默认什么都不过滤
	 * @param string $type 1) int integer 整形；2) float 浮点型；3) json传过来的数据为json数据；4) /.../需要符合正则；
	 * @param string $default 如果取不到，默认值
	 * @param string $low 默认去除xss攻击-是否简单
	 * @return string
	 */
	public function post($name, $type = '', $default = '', $low = false){
		return $this->getValue($name, $_POST, $type, $default, $low);
	}

	/*
	 * 获得数据
	 */
	/**
	 * @param $name
	 * @param $data
	 * @param $type
	 * @param $default
	 * @param $low
	 * @return array|mixed|string
	 */
	private function getValue($name, $data, $type, $default, $low){
		if(isset($data[$name])){
			return $this->_ff($data[$name], $type, $default, $low);
		}else{
			return $default;
		}
	}

	/**
	 * 获取所有GET数据
	 *
	 * @param $low
	 * @return void
	 */
	public function getAll($low = false)
	{
		return array_map(function($value) use ($low){
			$this->_xss($value,$low);
			return $value;
		},$_GET);
	}

	/**
	 * 获取所有POST数据
	 *
	 * @param $low
	 * @return array
	 */
	public function postAll($low = false)
	{
		return array_map(function($value) use ($low){
			$this->_xss($value,$low);
			return $value;
		},$_POST);
	}

	/**
	 * 获取post过来的原始数据
	 *
	 * @return string
	 */
	public function postRaw()
	{
        $raw = $GLOBALS['HTTP_RAW_POST_DATA'];
        if (!$raw) {
            $raw = file_get_contents("php://input");
        }
		return $raw;
	}

	/*
	 * 过滤函数
	 */
	public function _ff($value, $type = '', $default = '', $low = false){
		//系统自动转义的情况下需要将转义的字符串纠正回来
		if(get_magic_quotes_gpc()){
			$value = stripslashes($value);
		}

		$isReg = preg_match("/^\/.*\/[a-z]*$/", $type) ? true : false;

		if(in_array($type, array('int', 'integer', 'float','string'))){
			settype($value, $type);
			if(!$value){
				$value = $default;
			}
		}elseif($type == 'json'){
			$value = json_decode($value, true);
			if(!$value){
				$value = array();
			}
			$value = json_encode($value);
		}elseif($isReg) {
			if (!preg_match($type, $value)) {
				$value = $default;
			}
		}
		$this->_xss($value, $low);
		return $value;
	}

	/**
	 * 去除xss攻击
	 *
	 * @param [type] $string
	 * @param boolean $low
	 * @return void
	 */
	public function _xss(&$string, $low = false)
	{
		if (! is_array ( $string ))
        {
            $string = trim ( $string );
            $string = strip_tags ( $string );
            if ($low)
            {
                return true;
            }
			$string = htmlspecialchars ( $string );
            $string = str_replace ( array ('"', "\\", "'", "/", "..", "../", "./", "//" ), '', $string );
            $no = '/%0[0-8bcef]/';
            $string = preg_replace ( $no, '', $string );
            $no = '/%1[0-9a-f]/';
            $string = preg_replace ( $no, '', $string );
            $no = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';
            $string = preg_replace ( $no, '', $string );
            return true;
        }
        $keys = array_keys ( $string );
        foreach ( $keys as $key )
        {
            $this->_xss( $string[$key], $low);
        }
	}
}