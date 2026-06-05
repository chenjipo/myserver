<?php
namespace YXLib;

use YXLib\foundation\Debug;
use YXLib\foundation\Pipeline;
/**
 * 定义目录绝对路径
 */
if(!defined('ROOT')){
	define('ROOT', dirname(__DIR__));
}
/**
 * 检查是否是子项目
 */
if(!defined('APP_ROOT')){
	define('APP_ROOT', ROOT.'/app');
	define('APP_NAMESPACE','App\\');
}else{
	define('APP_NAMESPACE','App\\'.basename(APP_ROOT));
}

/**
 * 核心类
 */
class YX{

	/**
	 * @var string 控制器
	 */
	public static $ct = '';

	/**
	 * @var string 控制器方法
	 */
	public static $ac = '';

	/**
	 * 路由地址
	 *
	 * @var string
	 */
	public static $route = '';

	/**
	 * pathinfo
	 *
	 * @var string
	 */
	public static $pathInfo = '';

	/**
	 * 解析 path_info 的数组
	 */
	public static $urlSegments;

	/**
	 * 配置文件生成缓存的文件名
	 *
	 * @var string
	 */
	public static $configCacheFileName = 'framework';

	 /**
     * 运行环境初始化
     */
    public static function runtime()
    {
		ini_set('display_errors','On');
		if(config('app.env') == 'production'){
			error_reporting(0);
		}else{
			error_reporting(E_ALL & ~E_NOTICE);
		}
		header("content-type:text/html; charset=utf-8");
        date_default_timezone_set(config('app.timezone'));
    }

	/**
	 * 初始化入口
	 */
	public static function init(){
		/**
		 * 自定义错误处理
		 */
		set_error_handler(array(new Debug(),'error'));
		register_shutdown_function(array(new Debug(),'shutdown'));
		/**
		 * 加载辅助函数库
		 */
		include ROOT . '/lib/Helper.php';
		/**
		 * 运行环境初始化
		 */
		self::runtime();
	}

	/**
	 * 启动
	 *
	 * @return void
	 */
	public static function run(){
		/**
		 * 初始化
		 */
		self::init();
		
		/**
		 * 运行系统路由
		 */
        self::route();
    }

	/**
	 * 根据url找到准确的控制器
	 */
	protected static function route(){
		/**
		 * 命令行模式
		 */
		if(!empty($_SERVER['argv'][1]) && PHP_SAPI == 'cli'){
			parse_str($_SERVER['argv'][1], $_GET);
		}
		/**
		 * 解析pathinfo
		 */
		if(!empty( $_SERVER['PATH_INFO'] )){
			self::$pathInfo = $_SERVER['PATH_INFO'];
		}else{
			self::$pathInfo = str_replace($_SERVER['SCRIPT_FILENAME'],'',$_SERVER['DOCUMENT_ROOT'].$_SERVER['DOCUMENT_URI']);
		}
		/**
		 * 解析url携带参数
		 */
		self::$urlSegments = !empty( self::$pathInfo ) ? array_filter( preg_split( '/[\/\-\.\?]/', self::$pathInfo ) ) : null;
		/**
		 * 设置控制器
		 */
		self::$ct = !empty( self::$urlSegments[1] ) ? preg_replace( '!\W!', '', self::$urlSegments[1] ) : (empty($_GET['ct']) ? 'index' : $_GET['ct']);
		/**
		 * 设置操作方法
		 */
		self::$ac = !empty( self::$urlSegments[2] ) ? preg_replace( '!\W!', '', self::$urlSegments[2] ) : (empty($_GET['ac']) ? 'index' : $_GET['ac']);
		/**
		 * 设置路由
		 */
		self::$route = strtolower('/'.self::$ct.'/'.self::$ac.'/');

		$controller = APP_NAMESPACE."\\" .config('app.ctl_name')."\\". "Ctl" . ucfirst(self::$ct);
		$action = self::$ac;
		//判断控制器类是否存在
		if(!class_exists($controller)){
			Debug::log("控制器{$controller}无法自动加载", 'error');
			send404();
			return;
		}
		$controllerObj = new $controller;
		//判断控制器的方法是否存在
		if (!method_exists($controllerObj, $action)) {
			Debug::log("控制器{$controller}不存在方法：" . $action, 'error');
			send404();
			return;
		}
		//中间件
		$middleware = config('app.middleware.'.basename(APP_ROOT));
		$pipes = is_array($middleware)?$middleware:[]; 
		//控制器中间件
		if(property_exists($controllerObj, 'middleware')){
			$ctlPipes = is_array($controllerObj->middleware)?$controllerObj->middleware:[];
			$pipes = array_merge($pipes,$ctlPipes);
		}
		if(!empty($pipes)){//执行中间件
			$response = (new Pipeline())
			->through($pipes)
			->then(function() use ($controllerObj,$action){
				return $controllerObj->$action();
			});
		}else{
			$response = $controllerObj->$action();
		}
		if($response instanceof \YXLib\contracts\ResponseInterface)
			$response->send();
		else echo $response;
	}	
}