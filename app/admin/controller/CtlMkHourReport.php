<?php
namespace App\admin\controller;

use App\admin\service\SrvMkHourReport;

class CtlMkHourReport {

    private $srv;
    public function __construct(){
    }

    //买量时报表
    public function index()
    {
        return view('mkdata/hourReport.html');
    }

    //获取买量时表表数据
    public function getData()
    {
        $params = getAll();
        $srv = new SrvMkHourReport;
        return $srv->getData($params);
    }

}