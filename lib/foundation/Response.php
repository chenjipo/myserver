<?php 
/**
 * 响应类
 */
namespace YXLib\foundation;
use YXLib\contracts\ResponseInterface;
use YXLib\YX;
class Response implements ResponseInterface{
	/**
	 * 模板文件
	 *
	 * @var string
	 */
    public $tpl = '';
	/**
	 * 返回格式
	 *
	 * @var string
	 */
	public $outType = 'string';
	/**
	 * 数据
	 *
	 * @var string|array
	 */
	public $out;
	/**
	 * jsonp callabck
	 *
	 * @var string
	 */
    public $callback;
	/**
	 * 重定向类型
	 *
	 * @var string
	 */
	public $redirectType = 'php';
	/**
	 * 实例
	 *
	 * @var Response
	 */
    private static $self = null;
	
	public function __construct($content = '')
	{
		if(!empty($content)){
			$this->out = $content;
		}
	}

    /**
     * 获取单实例
     *
     * @return Response
     */
    public static function getInstance()
    {
        if(is_null(self::$self)){
            self::$self = new self();
        }
        return self::$self;
    }
	/**
	 * string
	 *
	 * @param string $content
	 * @return Response
	 */
    public function make(string $content = '')
    {
		$this->outType = 'string';
        $this->out = $content;
		return $this;
    }
	/**
	 * redirect
	 *
	 * @param string $content
	 * @return Response
	 */
	public function redirect(string $url = '',$type = 'php')
	{
		$this->outType = 'redirect';
		$this->out = $url;
		$this->redirectType = $type;
		return $this;
	}
	/**
	 * json
	 *
	 * @param array $data
	 * @return Response
	 */
    public function json(array $data = [])
    {
		$this->outType = 'json';
        $this->out = $data;
		return $this;
    }
	/**
	 * jsonp
	 *
	 * @param array $data
	 * @param string $callback
	 * @return Response
	 */
    public function jsonp(array $data = [], string $callback = 'jsoncallback')
    {
        $this->out = $data;
        $this->callback = $callback;
        $this->outType = 'jsonp';
		return $this;
    }
	/**
	 * 模板
	 *
	 * @param string $tpl
	 * @param array $data
	 * @return Response
	 */
    public function view(string $tpl, array $data = [])
    {
        $this->out = $data;
        $this->tpl = $tpl;
        $this->outType = 'smarty';
		return $this;
    }
	/**
	 * 向客户端返回数据
	 *
	 * @return void
	 */
    public function send()
    {
        if(empty($this->outType) || $this->outType == 'none' || $this->outType == 'string' || $this->outType == 'layout'){
			$content = $this->out;
		}elseif($this->outType == 'json' || $this->outType == 'jsonp'){
			$content = $this->_json();
		}elseif($this->outType == 'smarty'){
			if(empty($this->tpl)){
				$ct = strtolower(YX::$ct);
				$ac = strtolower(YX::$ac);
				$this->tpl = "{$ct}/{$ac}.tpl";
			}
			return $this->_view($this->tpl);
		}elseif($this->outType == 'redirect'){
			return $this->Go($this->out);
		}
        echo $content;
    }
    /**
	 * 调用smarty
	 *
	 * @param string $tpl 模板xxx.tpl或者xxx.html
	 * @param bool $return 是否将结果作为一个变量返回
	 * @param array $out 变量
	 * @param string $tplDir 切换模板目录
	 * @return bool|string
	 * @throws Exception
	 * @throws SmartyException
	 */
	private function _view($tpl, $return = false, $tplDir = 'template'){

		$smarty = new \Smarty();
		$smarty->setTemplateDir(APP_ROOT .'/'. $tplDir);
		$smarty->setCompileDir(ROOT . '/runtime/cache');
		$smarty->setCacheDir(ROOT . '/runtime/cache');
		$smarty->left_delimiter = '<{';
		$smarty->right_delimiter = '}>';
		$smarty->error_reporting = 0;
		$smarty->caching = false;
		$smarty->compile_check = true;
		$smarty->escape_html = true;
		if(!is_array($this->out)){
			return array();
		}
		if(substr($tpl, -4) !== '.tpl' && substr($tpl, -5) !== '.html'){
			$tpl .= '.tpl';
		}
		foreach($this->out as $name => $out){
			$smarty->assign($name, $out);
		}
		if($return){
			$re = $smarty->fetch($tpl);
		}else{
			$smarty->display($tpl);
			$re = true;
		}
		return $re;
	}
	/**
	 * 输出json或者jsonp字符串
	 * @param mixed $out 变量
	 * @param string $jsonCallback getJSON使用的name
	 */
	private function _json(){
		$this->outType = '';
		$callback = get($this->callback, '/^\w+$/');
        $value = $this->out;
		$value = json_encode($value);
		Debug::log($value);
		if($callback){
			return "{$callback}({$value})";
		}else{
			return $value;
		}
	}
	/*
	 * 跳转
	 */
	public function Go($url, $type = 'php', $isTop = false){
		if(!$type || $type == 'php'){
			header('Location: ' . $url);
		}else{
			$top = $isTop ? 'top.' : '';
			echo '<script type="text/javascript">' . $top . 'location.href="' . $url . '";</script>';
		}
		exit;
	}

	/**
	 * 获取返回数据
	 *
	 * @return void
	 */
	public function getContent()
	{
		return $this->out;
	}
}