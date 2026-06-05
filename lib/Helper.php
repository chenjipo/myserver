<?php
/**
 * 帮助函数
 */

use YXLib\foundation\Debug;

if (!function_exists('config')) {
	/**
     * 获取配置文件
     *
     * @param [type] $name
     * @param boolean $isCache
     * @return array|string
     */
	function config($name, $isCache = false)
	{
		return \YXLib\foundation\Config::read($name, $isCache);
	}
}

if (!function_exists('config_write')) {
	/**
     * 生成配置文件
     *
     * @param [type] $name
     * @param array $config
     * @return void
     */
	function config_write($name, array $config = [])
	{
		return \YXLib\foundation\Config::write($name, $config);
	}
}

if (!function_exists('send404')){
    /**
     * 404
     *
     * @return void
     */
    function send404()
    {
        header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		exit;
    }
}

if (!function_exists('R')){
    /**
     * $_REQUEST
     *
     * @param [type] $name
     * @param string $type
     * @param string $default
     * @param string $low
     * @return string
     */
    function R($name, $type = '', $default = '',$low = false){
		return \YXLib\foundation\Request::getInstance()->R($name, $type, $default,$low);
	}
}
if (!function_exists('get')){
    /**
     * $_GET
     *
     * @param [type] $name
     * @param string $type
     * @param string $default
     * @param string $low
     * @return string
     */
    function get($name, $type = '', $default = '',$low = false){
		return \YXLib\foundation\Request::getInstance()->get($name, $type, $default, $low);
	}
}
if (!function_exists('J')){
	/**
	 * 获取post过来的json数据
	 *
	 * @return array
	 */
	function J()
	{
		$data = file_get_contents('php://input');
        return json_decode($data,true);
	}
}
if (!function_exists('post')){
    /**
     * $_POST
     *
     * @param [type] $name
     * @param string $type
     * @param string $default
     * @param string $low
     * @return void
     */
    function post($name, $type = '', $default = '',$low = false){
		return \YXLib\foundation\Request::getInstance()->post($name, $type, $default, $low);
	}
}
if (!function_exists('getAll')){
    /**
     * 获取所有GET
     * @param array $low
     */
    function getAll($low = false){
		return \YXLib\foundation\Request::getInstance()->getAll($low);
	}
}
if (!function_exists('postAll')){
    /**
     * 获取所有POST
     * @param array $low
     */
    function postAll($low = false){
		return \YXLib\foundation\Request::getInstance()->postAll($low);
	}
}
if (!function_exists('postRaw')){
    /**
     * 获取POST原始数据流
     * @param array $low
     */
    function postRaw(){
		return \YXLib\foundation\Request::getInstance()->postRaw();
	}
}
if (!function_exists('success')){
    /**
     * send success data
     *
     * @param array $data
     * @param string $msg
     * @return Response
     */
    function success($data = array(),  $msg = 'success', $code = 200){
        $out = array(
            'state' => 1,
            'data'  => $data,
            'msg'  => $msg,
            'code' => $code,
        );
		return \YXLib\foundation\Response::getInstance()->json($out);
	}
}

if (!function_exists('fail')){
    /**
     * send fail response
     *
     * @param string $msg
     * @param array $data
     * @return Response
     */
    function fail( $msg = 'fail' , $data = array() ,$code = 200){
        $out = array(
            'state' => 0,
            'msg'  => $msg,
            'data' => $data,
            'code' => $code,
        );
		return \YXLib\foundation\Response::getInstance()->json($out);
	}
}

if (!function_exists('retData')){
    /**
     * send fail response
     *
     * @param string $msg
     * @param array $data
     * @return Response
     */
    function retData( $state = 0 , $msg = '' , $data = array(), $code = 200 ){
        if($state === 1){
            return success($data,$msg,$code);
        }else{
            return fail($msg,$data,$code);
        }
	}
}

if (!function_exists('view')){
    /**
     * 模板渲染
     *
     * @param string $tpl
     * @param array $data
     * @return void
     */
    function view(string $tpl, array $data = [])
    {
        return \YXLib\foundation\Response::getInstance()->view($tpl,$data);
    }
}

if (!function_exists('env')){
    /**
     * 获取环境配置
     *
     * @param string $name
     * @param string $default
     * @return void
     */
    function env(string $name, $default = '')
    {
        return \MillionMile\GetEnv\Env::get($name, $default);
    }
}

/**
 * 根据keys提取数组
 */
function sq_array_slice_assoc( $array, $keys )
{
    $slice = array();
    foreach ( $keys as $key )
    if ( isset( $array[ $key ] ) )
        $slice[ $key ] = $array[ $key ];
    return $slice;
} 


/**
 * 获取文件缓存数组字段
 * 
 * @param  $ <type> $key 键名
 * @param  $ <type> $index 索引值
 * @param  $ <type> $columns  字段名(可以是单个[string]、多个[array]、所有[*])
 * @return Boolean|Array 
 */
function sy_data_read( $key, $index = 'all', $columns = '*') {
    static $list;
    $_key = md5($key.$index);
    if(!empty($list[$_key])){
        $row = $list[$_key];
    }else{
        $format = config('app.sy_data_path'); //缓存文件路径格式
        $fpath = sprintf( $format, $key, $index ) ;
        if ( !$key || !$index || preg_match( '/\./', $key ) || preg_match( '/\./', $index ) || true !== @is_readable( $fpath ) ) {
            return false;
        } 
        $row = include $fpath;
        $list[$_key] = $row;
    }
	if ( '*' === $columns ) {
		return $row;
	} 
	$columns = !is_array( $columns ) ? ( array ) $columns : $columns ;
	return sq_array_slice_assoc( $row, $columns );
} 

/**
 * 获取文件缓存数组字段 
 * 强制读文件 用于队列中读文件
 * 
 * @param  $ <type> $key 键名
 * @param  $ <type> $index 索引值
 * @param  $ <type> $columns  字段名(可以是单个[string]、多个[array]、所有[*])
 * @return Boolean|Array
 */
function sy_data_force_read( $key, $index = 'all', $columns = '*') {
    $format = config('app.sy_data_path'); //缓存文件路径格式
    $fpath = sprintf( $format, $key, $index ) ;
    if ( !$key || !$index || preg_match( '/\./', $key ) || preg_match( '/\./', $index ) || true !== @is_readable( $fpath ) ) {
        return false;
    } 
    $row = include $fpath;
    $list[$_key] = $row;
	if ( '*' === $columns ) {
		return $row;
	} 
	$columns = !is_array( $columns ) ? ( array ) $columns : $columns ;
	return sq_array_slice_assoc( $row, $columns );
} 

/**
 * 写入文件缓存
 *
 * @param [type] $key
 * @param [type] $index
 * @param array $data
 * @return void
 */
function sy_data_write( $key, $index, $data = [])
{
    $format = config('app.sy_data_path'); //缓存文件路径格式
    $fpath = sprintf( $format, $key, $index ) ;
	if ( !$key || !$index || preg_match( '/\./', $key ) || preg_match( '/\./', $index ) ) {
		return false;
	} 
    $dir = dirname($fpath);
    if(!is_dir($dir)){
        if(!mkdir($dir, 0777, true)){
            Debug::log("缓存文件目录创建失败：".$dir);
            return false;
        }
    }
    $save = var_export($data, true);
    $save = "<?php\r\n/*本文件由程序自动生成（" . date('Y-m-d H:i:s') . "）*/\r\nreturn {$save};";
    $re = file_put_contents($fpath, $save);
    if (!$re) {
        Debug::log("缓存文件生成失败：".$fpath);
        return false;
    }
    return $re;
}


/**
 * 钉钉推送 - 业务报警
 *
 * @param [type] $api
 * @param [type] $title
 * @param [type] $message
 * @param boolean $isAtAll
 * @param array $atMobiles
 * @param array $atUserIds
 * @return void
 * 
 * $title = "数据库崩了";
 * $message = "#### 数据库崩了.\n ##### 可能是sql注入.";
 * $api = $_YX['define']['dd_notice_api']['phpteam'];
 * $rest = dd_nitoce($api,$title,$message);
 */
function dd_nitoce($webhook, $title, $message, $isAtAll = false, $atMobiles = [], $atUserIds = [])
{
    $data = array(
        "msgtype" => "text",
        "text" => [
            "content" => $title . '：' . $message . '.'
        ],
        "at" => [
            "atMobiles" => $atMobiles,
            "atUserIds" => $atUserIds,
            "isAtAll"   => $isAtAll,
        ],
    );
    $data_string = json_encode($data);
    $ch = curl_init();  
    curl_setopt($ch, CURLOPT_URL, $webhook);
    curl_setopt($ch, CURLOPT_POST, 1); 
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, array ('Content-Type: application/json;charset=utf-8'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
    // 线下环境不用开启curl证书验证, 未调通情况可尝试添加该代码
    // curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0); 
    // curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $data = curl_exec($ch);
    curl_close($ch);                
    return $data;  
}

 /**
  * 生成随机用户
  *
  * @return void
  */
function rand_user($pre = '')
{
    $length = random_int(7, 10);
    $random_int = random_int(pow(36, $length - 1), pow(36, $length) - 1);
    $suffix = base_convert($random_int, 10, 36);
    $uname = $pre . $suffix;
    return $uname;
}