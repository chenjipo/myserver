<?php
namespace YXLib;

use YXLib\foundation\Pipeline;
use YXLib\foundation\Debug;

/**
 * 定义目录绝对路径
 */
if (!defined('ROOT')) {
	define('ROOT', dirname(__DIR__));
}
/**
 * 核心类
 */
class Cmd{

	/**
	 * 启动
	 *
	 * @return void
	 */
	public static function run()
	{
		/**
		 * 初始化
		 */
		YX::init();
		
		/**
		 * 运行系统路由
		 */
        self::route();
    }

	protected static function route()
	{
        $cmd = $_SERVER['argv'][1] ?? '';
		$args = $_SERVER['argv'][2] ?? '';
        parse_str($args, $params);
		$exp = explode(':', $cmd);
		if (count($exp) > 1) {
			$command = "App\\" . $exp[0] . "\\console\\" . ucfirst($exp[1]);
		}else{
			$command = "App\\console\\" . ucfirst($cmd);
		}
		if (!class_exists($command)) {
			$command = "YXLib\\command\\" . ucfirst($cmd);
			if(!class_exists($command)){
				Debug::log("命令{$command}无法自动加载", 'error');
				return;
			}
		}
		$command = new $command;
		//中间件
		$pipes = [];
		if (property_exists($command, 'middleware')) {
			$pipes = is_array($command->middleware) ? $command->middleware : [];
		}
		if (!empty($pipes)) {//执行中间件
			$response = (new Pipeline())
			->through($pipes)
			->then(function() use ($command,$params){
				return $command->handle($params);
			});
		} else {
			$response = $command->handle($params);
		}
		if($response instanceof \YXLib\contracts\ResponseInterface)
			$response->send();
		else echo $response;
	}

}