<?php
namespace YXLib\foundation\ttserver;

use memcached;
use YXLib\exceptions\ErrorClassException;

/**
 * 用户映射 TTserver 组件
 * 使用方式： 开发者继承TTserver，重新设置成员变量$hosts
 */
class TTServer {

	protected $p;
	protected $tts = array( ); // tts 对象
	protected $isAddtts = array( ); // 连接标记                                
	protected $hosts = array( 
		array( 'ip' => '172.18.6.239', 'port' => 11200, 'bak_ip' => '172.18.6.238', 'bak_port' => 11200 ),
        array( 'ip' => '172.18.6.239', 'port' => 11201, 'bak_ip' => '172.18.6.238', 'bak_port' => 11201 ),
        array( 'ip' => '172.18.6.239', 'port' => 11202, 'bak_ip' => '172.18.6.238', 'bak_port' => 11202 ),
        array( 'ip' => '172.18.6.239', 'port' => 11203, 'bak_ip' => '172.18.6.238', 'bak_port' => 11203 ),
        array( 'ip' => '172.18.6.239', 'port' => 11204, 'bak_ip' => '172.18.6.238', 'bak_port' => 11204 ),
		);

	public function __construct(array $connection = [])
	{
		if($connection){
			$this->hosts = $connection;
		}
		$this->p = count($this->hosts);
	}

	/**
	 * 实例 连接对象
	 * 
	 * @param  $ <type> $i
	 * @return <type>
	 */
	public function addTTserver( $i )
	{
		if(!class_exists('Memcached')){
			throw new ErrorClassException('Memcached类不存在！');
		}
		if ( $this->isAddtts[$i] == true ) {
			return ;
		} 
		$this->tts[$i] = new TTMemcached(); //connect to ttserver
		$this->tts[$i]->setOption( Memcached::OPT_CONNECT_TIMEOUT, 5000 ); // 超时设置 5000 毫秒
		$this->tts[$i]->addTTServer( $this->hosts[$i]['ip'], $this->hosts[$i]['port'] );
		$this->tts[$i]->addBakServer( $this->hosts[$i]['bak_ip'], $this->hosts[$i]['bak_port'] );
		$this->isAddtts[$i] = true;
	} 

	/**
	 * 获取 hash 节点
	 * 
	 * @param  $ <type> $key
	 * @return <type>
	 */
	public function hashPart( $key )
	{
		return sprintf( '%u', crc32( $key ) ) % $this->p ; //分布算法
	} 


	/**
	 * 获取指定键值
	 * 
	 * @param  $ <type> $key
	 * @return <type>
	 */
	public function get( $key )
	{
		$i = $this->hashPart( $key ) ;
		$this->addTTserver( $i );
		$str = $this->tts[$i]->getValue( $key );
		$op_resNew = $this->tts[$i]->getResultCode();
		if ( $op_resNew != Memcached::RES_SUCCESS && $op_resNew != Memcached::RES_NOTFOUND ) {
			$this->log( $this->tts[$i]->getResultMessage() . " " . $this->tts[$i]->cur_host . ":" . $this->tts[$i]->cur_port . " $key" );
			return 0;
		} 
		$arr = unserialize($str);
		if($arr === false){
			return $str;
		}

		return $arr;
	}

    /**
     * 删除指定键值
     *
     * @param  $ <type> $key
     * @return <type>
     */
    public function delete( $key )
    {
        $i = $this->hashPart( $key ) ;
        $this->addTTserver( $i );
        $r = $this->tts[$i]->delete( $key );
        if ( $r !== TRUE ) {
            $this->log($this->tts[$i]->getResultCode() . " | " . $this->tts[$i]->getResultMessage() . " | " . $this->tts[$i]->cur_host . ":" . $this->tts[$i]->cur_port . " $key", FAIL_LOG_PATH );
            $r = false;
        }
        return $r;
    }

	
	/**
	 * 添加数据
	 * 
	 * @param  $ <type> $key
	 * @return <type>
	 */
	public function add( $key, $arr )
	{
		$i = $this->hashPart( $key ) ;
		$this->addTTserver( $i );
		$resNew = $this->tts[$i]->add( $key, $arr );
		if ( $resNew === false ) {
			$this->log( $this->tts[$i]->getResultMessage() . " " . $this->tts[$i]->cur_host . ":" . $this->tts[$i]->cur_port . "  $key
			  " . serialize( $arr ) );
		} 
		return $resNew;
	} 
	
	/**
	 * 设置数据
	 * 
	 * @param  $ <type> $key
	 * @param  $ <type> $arr
	 * @return <type>
	 */
	public function set( $key, $arr )
	{
		$i = $this->hashPart( $key ) ;
		$this->addTTserver( $i );
		$resNew = $this->tts[$i]->setValue( $key, $arr );
		if ( $resNew === false ) {
			$this->log( $this->tts[$i]->getResultMessage() . " " . $this->tts[$i]->cur_host . ":" . $this->tts[$i]->cur_port . "  $key  " . serialize( $arr ) );
		} 
		return $resNew;
	}
	/**
	 * 更新指定键值
	 * 
	 * @param  $ <type> $key
	 * @param  $ <type> $params
	 * @return <type>
	 */
	public function update( $key, $params = array() )
	{
		$arr = $this->get( $key );
		foreach( $params as $key => $value ) {
			$arr[$key] = $value;
		} 
		return $this->set( $key, $arr );
	} 

	/**
	 * 事件日志
	 * 
	 * @param  $ <type> $content
	 * @param  $ <type> $url
	 * @return <type>
	 */
	public function log( $content )
	{
		$path = ROOT . '/runtime/logs/'.date('Ym').'/ttserver_error_'.date('Ymd').'.log';
		if ( !( $fp = @fopen( $path, "a" ) ) ) {
			return false;
		} 
		$now = date( 'Y-m-d H:i:s' );
		fwrite( $fp, "$now $content\n" );
		fclose( $fp );

		return true;
	} 
} 

?>