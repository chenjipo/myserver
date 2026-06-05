<?php
namespace App\admin\controller;

use App\admin\service\SrvOptDayReport;

class CtlOptDayReport {

    private $srv;
    public function __construct(){
        $this->srv = new SrvOptDayReport();
    }

    //数据总览
    public function index()
    {
        return view('optdata/dayReport.html');
    }

    //获取总览数据
    public function getData()
    {
        return $this->srv->getData(getAll());
    }
}