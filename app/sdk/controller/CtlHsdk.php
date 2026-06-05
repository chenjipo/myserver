<?php
namespace App\sdk\controller;

use App\common\Table;
use App\sdk\service\SrvH5Act;

class CtlHsdk
{
    public $srv;

    public $commomParams = [
        'appid', 'duid', 'mac', 'version', 'resolution', 'model', 'sysver', 'brand', 'pkgid', 'ntype', 'nname'
    ];

    public function __construct()
    {
        $this->srv = new SrvH5Act();
    }

    /**
     * 检验签名
     *
     * @var array
     */
    public $middleware = [
        \App\sdk\middleware\VerifySign::class,
        \App\sdk\middleware\AllowOrigin::class,  
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
        foreach ($params as $param) {
            $data[$param] = post($param);
        }
        
        if (in_array($data['mac'], ['020000000000'])) $data['mac'] = '';

        return $this->srv->active($data);
    }

    /**
     * 登录
     */
    public function login()
    {
        $params = array_merge($this->commomParams, [
            'duid',
            'checktoken'
        ]);
        $data = array();
        foreach ($params as $param) {
            $data[$param] = post($param);
        }
        
        return $this->srv->login($data);
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
}