<?php
namespace App\admin\controller;

use App\admin\service\SrvAuth;
use App\admin\service\SrvCaptcha;
use YXLib\foundation\Response;

class CtlLogin
{

    /**
     * 控制器单独设置中间件 例子
     *
     * @var array
     */
    public $middleware = [
    ];

    public function __construct()
    {
    }

    public function index()
    {
        $isNeedCaptcha = SrvAuth::isNeedCaptcha();
        return view('index/login.html',['isNeedCaptcha'=>$isNeedCaptcha]);
    }

    public function loginAction()
    {
        $srvAuth = new SrvAuth();
        return $srvAuth->login(postAll());
    }

    /**
     * 退出
     */
    public function logoutAction(){
        SrvAuth::logout();
        (new Response)->go('/login');
    }

    public function captcha(){
        header('P3P: CP=CAO PSA OUR');
        $this->outType = 'string';
        $array1 = array(1,2,3,4,5,6,7,8,9,0);
        $num1 = mt_rand(1,2);
        $array2 = array('减','减去','加','加上','乘于','乘');
        $count = $array2[mt_rand(0,5)];
        if($count=='乘于' || $count=='乘'){
            $num1 = 1;
        }
        $code = array();
        for($i=0;$i<$num1;$i++){
            if($i==0){
                $code[0] = $array1[mt_rand(0,8)];
            }else{
                $code[0] .= $array1[mt_rand(0,9)];
            }
        }
        $_code2 = mt_rand(1,9);
        $code[1] = SrvCaptcha::changeToChn($_code2);
        if($count=='减'||$count=='减去'){
            $str = $code[0]>$_code2?($code[0].$count.$code[1]):($code[1].$count.$code[0]);
            $result = $code[0]>$_code2?(intval($code[0])-intval($_code2)):(intval($_code2)-intval($code[0]));
        }else{
            if($count=='乘于' || $count=='乘') $result = intval(intval($code[0])*intval($_code2));
            else $result = intval(intval($code[0])+intval($_code2));
            shuffle($code);
            $str = $code[0].$count.$code[1];
        }
        SrvCaptcha::save($result);
        SrvCaptcha::meansCode($str, array(
            'width' => 101,
            'height' => 42,
            'fontSize' => 17,
            'fontAngle' => 15,
            'color' => array('#547933'),
            'background' => array('#E8FAFF'),
            'border' => '#F1F1F1',
            'font' => array('CHYaHei'),
        ));
    }


}