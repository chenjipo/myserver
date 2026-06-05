<?php
namespace App\sdk\controller;

use App\common\Table;
use App\sdk\service\SrvAct;

class Ctlsdk
{
    public $srv;

    public $commomParams = [
        'appid', 'duid', 'mac', 'version', 'resolution', 'model', 'sysver', 'brand', 'pkgid', 'ntype', 'nname'
    ];

    public function __construct()
    {
        $this->srv = new SrvAct();
    }

    /**
     * 检验签名
     *
     * @var array
     */
    public $middleware = [
        \App\sdk\middleware\VerifySign::class,
    ];

    /**
     * 激活
     */
    public function active()
    {
        $params = array_merge($this->commomParams, [
            'install'
        ]);
        $data = array();
        $contentType = $_SERVER['CONTENT_TYPE'];
        if ($contentType == 'application/json') {
            $post_data_arr = postRaw();
            $post_data_arr = json_decode($post_data_arr, true);
        } else {
            $post_data_arr = $_POST;
        }

        foreach ($params as $param) {
            $data[$param] = $post_data_arr[$param];
        }
         
        if (in_array($data['mac'], ['020000000000'])) $data['mac'] = '';

        return $this->srv->active($data);
    }

    /**
     * 获取应用列表
     */
    public function applist()
    {
        $params = array_merge($this->commomParams, [
            'duid'
        ]);
        $data = array();
        foreach ($params as $param) {
            $data[$param] = post($param);
        }
        
        return $this->srv->applist($data);
    }

    /**
     * 获取分享状态
     */
    public function share()
    {
        $params = array_merge($this->commomParams, [
            'duid',
        ]);
        $data = array();
        $contentType = $_SERVER['CONTENT_TYPE'];
        if ($contentType == 'application/json') {
            $post_data_arr = postRaw();
            $post_data_arr = json_decode($post_data_arr, true);
        } else {
            $post_data_arr = $_POST;
        }

        foreach ($params as $param) {
            $data[$param] = $post_data_arr[$param];
        }
         
        if (in_array($data['mac'], ['020000000000'])) $data['mac'] = '';
        return $this->srv->share($data);
    }

     /**
     * 跳转地址
     */
    public function rgo()
    {
        $params = array_merge($this->commomParams, [
            'invite',
            'appid'
        ]);

        $data = array();
        $post_data_arr = $_GET;

        foreach ($params as $param) {
            $data[$param] = $post_data_arr[$param];
        }
         
        return $this->srv->rgo($data);
    }

}