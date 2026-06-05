<?php
namespace App\admin\controller;

use App\admin\service\SrvOptLtvReport;

class CtlOptLtvReport {

    private $srv;
    public function __construct(){
        $this->srv = new SrvOptLtvReport();
    }

    //ltv报表
    public function index()
    {
        return view('optdata/ltvReport.html');
    }

    //获取ltv报表数据
    public function getData()
    {
        return $this->srv->getData(getAll());
    }

}