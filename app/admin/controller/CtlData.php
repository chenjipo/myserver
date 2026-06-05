<?php
namespace App\admin\controller;

use App\admin\service\SrvData;

class CtlData {

    private $srv;
    public function __construct(){
    }

    //买量日报表
    public function index()
    {
        return view('mkdata/data.html');
    }

    //获取买量日表表数据
    public function getData()
    {
        $params = getAll();
        $srv = new SrvData;
        return $srv->getData($params);
    }

    //获取自定义列
    public function getCustomCols()
    {
        $srv = new SrvData;
        return $srv->getCustomCols();
    }

    //设置自定义列
    public function setCustomCols()
    {
        $cols = post('cols');
        $srv = new SrvData;
        return $srv->setCustomCols($cols);
    }
}