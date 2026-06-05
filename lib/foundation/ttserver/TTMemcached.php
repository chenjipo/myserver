<?php

namespace YXLib\foundation\ttserver;

use memcached;
/**
 * 用于连接TTServer的memcached协议
 */
defined( '_TTS_RECON_LOG_' ) or define( '_TTS_RECON_LOG_', ROOT ."/runtime/logs/tts_reconnect.log" );

class TTMemcached extends memcached {
	var $master_host ;
	var $master_port ;

	var $bakup_host;
	var $bakup_port;

	var $cur_host ;
	var $cur_port ;

	/**
	 * 构造函数
	 * 
	 * @return <type>
	 */
	function __construct()
	{
		parent::__construct();
		$this->setOption( Memcached::OPT_COMPRESSION, false );
	} 

	/**
	 * 添加节点
	 * 
	 * @param  $ <type> $host
	 * @param  $ <type> $port
	 * @return <type>
	 */
	function addTTServer( $host, $port )
	{
		$this->master_host = $host;
		$this->master_port = $port;
		$this->cur_host = $host;
		$this->cur_port = $port;
		parent::addServer( $host, $port );
	} 

	/**
	 * 备用服务器节点
	 * 
	 * @param  $ <type> $host
	 * @param  $ <type> $port
	 * @return <type>
	 */
	function addBakServer( $host, $port )
	{
		$this->bakup_host = $host;
		$this->bakup_port = $port;
	} 

	/**
	 * 失败重连
	 * 
	 * @return <type>
	 */
	function _reConnect()
	{
		parent::__construct();
		$this->setOption( Memcached::OPT_COMPRESSION, false );
		if ( !empty( $this->bakup_host ) ) {
			parent::addServer( $this->bakup_host, $this->bakup_port );
			$this->cur_host = $this->bakup_host;
			$this->cur_port = $this->bakup_port;
		} else {
			parent::addServer( $this->master_host, $this->master_port );
		} 

		$this->log( "$this->cur_host:$this->cur_port reconnect", _TTS_RECON_LOG_ );
	} 

	/**
	 * 设置键值
	 * 
	 * @param  $ <type> $key
	 * @param  $ <type> $val
	 * @return <type>
	 */
	function setValue( $key, $val )
	{
		$res = parent::set( $key, $val );
		if ( $res === false ) {
			$this->_reConnect();
			$res = parent::set( $key, $val );
		} 
		return $res;
	} 

	/**
	 * 获取键值
	 *
	 * @param [type] $key
	 * @return void
	 */
	function getValue( $key)
	{
		$res = parent::get( $key );
		$op_res = parent::getResultCode();
		if ( $op_res != Memcached::RES_SUCCESS && $op_res != Memcached::RES_NOTFOUND ) {
			$this->_reConnect();
			$res = parent::get( $key );
		} 
		return $res;
	} 

	/**
	 * 文本日志
	 * 
	 * @param  $ <type> $content 内容
	 * @param  $ <type> $url 文件路径
	 * @return <type>
	 */
	function log( $content, $url )
	{
		if ( !( $fp = @fopen( $url, 'a' ) ) ) {
			return false;
		} 
		$now = date( 'Y-m-d H:i:s' );
		fwrite( $fp, "$now $content\n" );
		fclose( $fp );
		return true;
	} 
} 

?>