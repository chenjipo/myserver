<?php
namespace App\admin\controller;

class CtlIndex{

    public function index()
    {
        return view('index/index.html');
    }

    /**
     * 控制台
     */
    public function console(){
        return view('index/console.html');
    }

}