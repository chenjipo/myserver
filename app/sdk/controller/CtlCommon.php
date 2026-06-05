<?php
namespace App\sdk\controller;

use App\common\Table;
use App\sdk\service\SrvCommon;

class CtlCommon
{
    public $srv;

    public $commomParams = [
        'appid', 'uuid', 'version', 'pkgid', 'event'
    ];

    public function __construct()
    {
        $this->srv = new SrvCommon();
    }

    /**
     * 事件
     */
    public function event()
    {
        $params = array_merge($this->commomParams, [
            'uuid', 'event'
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
        
        return $this->srv->event($data);
    }

    /**
     * 异常
     */
    public function crash()
    {
        $params = array_merge($this->commomParams, [
            'crash', 'mac', 'ntype', 'nname', 'duid', 'uid'
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
        
        return $this->srv->crash($data);
    }

    /**
     * 跳转地址
     */
    public function runtv()
    {
        
        return $this->srv->runtv([]);
    }
}