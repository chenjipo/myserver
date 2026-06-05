<?php
namespace App\admin\middleware;

use Closure;
use App\admin\service\SrvAuth;
use YXLib\foundation\Debug;
use YXLib\foundation\Response;
use YXLib\support\Util;
use YXLib\YX;
class UserAuth {

    public function handle(Closure $next)
    {
        //判断登陆
        if(YX::$ct == 'login') return $next();
        if(!SrvAuth::checkLogin()){
            if(Util::isAjax()){
                return fail('请先登录！', ['direct'=>'/login/'], 301);
            }
            $respose = new Response;
            $respose->Go('/login/');
        }

        //判断权限
        if(YX::$ct == 'index'){
            return $next();
        }
        if(!SrvAuth::canGO(SrvAuth::$adminId,YX::$route)){
            if(Util::isAjax()){
                return fail('抱歉，你无权限操作！', [], 403);
            }
            exit('抱歉，您无权限操作！！');
        }

        return $next();
    }

}