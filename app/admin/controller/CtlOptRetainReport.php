<?php
namespace App\admin\controller;

use App\admin\service\SrvOptRetainReport;

class CtlOptRetainReport {

    private $srv;
    public function __construct(){
        $this->srv = new SrvOptRetainReport();
    }

    //留存报表
    public function index()
    {
        return view('optdata/retainReport.html');
    }

    //获取留存报表数据
    public function getData()
    {
        return $this->srv->getData(getAll());
    }

}