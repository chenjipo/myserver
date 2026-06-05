<?php
namespace App\admin\service;

use App\admin\model\ModAdmin;
use App\admin\model\ModAuth;
use YXLib\foundation\Cache;
use YXLib\foundation\Debug;
use YXLib\support\Util;

define("COOKIE_ADMIN_DOMAIN",$_SERVER['SERVER_NAME']);

/**
 * 后台相关权限
 */
class SrvAuth{

    const ADMIN_ID = 'yx_admin_id';
    const USER = 'yx_user';
    const NICK = 'yx_nick';
    const ROLE_ID = 'yx_role_id';
    const UTIME = 'yx_utime';
    const  AUTH = 'yx_auth';
    const  KEEP = 'yx_keep';

    public static $ADMIN_LOGIN_KEY = 'PIOdSJ-SwedJ1HDB-Q2WWOE-NX442V3PP1';

    public static $adminId = '';
    public static $user = '';
    public static $nick = '';
    public static $roleId = '';
    public static $info = [];

    /**
     * 检查是否登录
     * @return bool
     */
    public static function checkLogin(){
        $adminId = self::get_cookie(self::ADMIN_ID,false);
        $user = self::get_cookie(self::USER,false);
        $nick = self::get_cookie(self::NICK,false);
        $roleId = self::get_cookie(self::ROLE_ID,false);
        $utime = self::get_cookie(self::UTIME,false);
        $auth = self::get_cookie(self::AUTH,false);
        $keep = self::get_cookie(self::KEEP,false);

        $info = [
            'admin_id'=>$adminId,
            'user'=>$user,
            'nick'=>$nick,
            'role_id'=>$roleId,
        ];

        $_auth = self::getCookieSign($info,$utime,$keep);
        if($_auth === $auth){
            if( time()> $utime ){
                self::logout();
                return false;
            }else{
                self::set_cookie($info,$keep);
                self::$adminId = $adminId;
                self::$user = $user;
                self::$nick = $nick;
                self::$roleId = $roleId;
                self::$info = $info;
            }
            return true;
        }else{
            self::logout();
            return false;
        }
    }

    public function login($params){
        if(!$params['username'] || !$params['password']) return fail('请填写完整！');

        if(!$this->isCaptchaRight($params['code'])) {
            return fail('验证码错误！', ['direct'=>'/login/'], 301);
        }

        $modAuth = new ModAuth();
        $info = $modAuth->getLoginInfo($params['username']);
        if(empty($info)){
            self::setNeedCaptcha();
            return fail('用户名或密码错误！');
        }

        if($info['state']==1) return fail('登录失败，账户异常！');
        $_pwd = self::signPwd($params['username'],$params['password'],$info['salt']);
        if($_pwd === strtolower($info['pwd'])){
            self::set_cookie($info,$params['keep']);
            //获取权限
            $modAdmin = new ModAdmin();
            $permissions = $modAdmin->roleHasPermission($info['role_id']);
            if(!$permissions){
                return fail('您未有后台权限，请联系负责人！');
            }
            $menu = $modAdmin->getMenuList(['ids'=>$permissions]);
            if(!self::updatePermissionCache($info['admin_id'],$menu)){
                return fail('登陆失败，请联系技术！');
            }
            if(!SrvAuthBusiness::updateUserAuthority($info['admin_id'])){
                return fail('登陆失败，请联系技术！');
            }
            $modAuth->updateLoginInfo($info['user']);
            return success(['direct'=>'/'],'登陆成功');
        }else{
            self::setNeedCaptcha();
            return fail('用户名或密码错误2！');
        }

    }

    /**
     * 更新用户权限缓存文件
     *
     * @param [type] $adminId
     * @param [type] $menu
     * @return void
     */
    public static function updatePermissionCache($adminId,$menu)
    {
        $dir = ROOT . '/runtime/authority/admin/';
        if(!is_dir($dir)){
            $re = mkdir($dir, 0777, true);
            if(!$re){
                return false;
            }
        }
        $data = [];
        foreach($menu as $m){
            if($m['route']){
                $data[strtolower($m['route'])] = $m['id'];
            }
        }
        return file_put_contents($dir.'user_'.$adminId,"<?php\nreturn ".var_export($data,true).";");
    }

    /**
     * 获取用户权限配置
     *
     * @param [type] $adminId
     * @return void
     */
    public static function getPermissionCache($adminId)
    {
        $file = ROOT . '/runtime/authority/admin/user_'.$adminId;
        if(is_file($file)) {
            return include_once ($file);
        }
        return [];
    }

    /**
     * 权限判断
     *
     * @param [type] $adminId
     * @param [type] $route
     * @return boolean
     */
    public static function canGO($adminId,$route)
    {
        $cache = self::getPermissionCache($adminId);
        if(isset($cache[$route])){
            return true;
        }
        return false;
    }

    public static function signPwd($name,$password,$salt){
        return strtolower(md5($password.$name.$salt.'LGKEKHG'));
    }

    /**
     * 退出登录
     */
    public static function logout(){
        setcookie(self::ADMIN_ID, '', 0, '/',COOKIE_ADMIN_DOMAIN);
        setcookie(self::USER, '', 0, '/',COOKIE_ADMIN_DOMAIN);
        setcookie(self::NICK, '', 0, '/',COOKIE_ADMIN_DOMAIN);
        setcookie(self::ROLE_ID, '', 0, '/',COOKIE_ADMIN_DOMAIN);
        setcookie(self::UTIME, '', 0, '/',COOKIE_ADMIN_DOMAIN);
        setcookie(self::AUTH, '', 0, '/',COOKIE_ADMIN_DOMAIN);
    }

    /**
     * 设置cookie
     * @param $name
     * @param $time
     * @param $type
     * @param $id
     */
    public static function set_cookie($info,$keep = false){
        if($keep) $utime = time()+86400*15;
        else $utime = time()+86400;
        $auth = self::getCookieSign($info,$utime,$keep);
        setcookie(self::ADMIN_ID, $info['admin_id'], 0, '/',COOKIE_ADMIN_DOMAIN);
        setcookie(self::USER, $info['user'], 0, '/',COOKIE_ADMIN_DOMAIN);
        setcookie(self::NICK, $info['nick'], 0, '/',COOKIE_ADMIN_DOMAIN);
        setcookie(self::ROLE_ID, $info['role_id'], 0, '/',COOKIE_ADMIN_DOMAIN);
        setcookie(self::UTIME, $utime, 0, '/',COOKIE_ADMIN_DOMAIN);
        setcookie(self::AUTH, $auth, 0, '/',COOKIE_ADMIN_DOMAIN);
        setcookie(self::KEEP, $keep, 0, '/',COOKIE_ADMIN_DOMAIN);
    }

    public static function getCookieSign($info,$utime,$keep){
        return md5($info['admin_id'].$info['user'].$info['nick'].$info['role_id'].$utime.$keep.self::$ADMIN_LOGIN_KEY);
    }

    /**
     * 获取cookie值
     * @param $cookie_name
     * @param bool|true $safe
     * @return bool|string
     */
    public static function get_cookie($cookie_name,$safe=true){
        $value = $_COOKIE[$cookie_name];
        if($value){
            if($safe){
                Util::clean_xss($value);
            }
            return $value;
        }
        return '';
    }

    /**
     * 设置需要验证码
     */
    public static function setNeedCaptcha(){
        $mem = Cache::getInstance();
        $captchaKey = 'NeedCaptcha'.Util::getIp();
        $mem->set($captchaKey, 1);
    }

    /**
     * 判断是否需要验证码
     * @return array|string
     */
    public static function isNeedCaptcha(){
        $mem = Cache::getInstance();
        $captchaKey = 'NeedCaptcha'.Util::getIp();
        return $mem->get($captchaKey);
    }

    /**
     * 检验验证码
     * @param $captcha
     * @return bool
     */
    public function isCaptchaRight($captcha) {
        $mem = Cache::getInstance();
        $captchaKey = 'NeedCaptcha'.Util::getIp();
        $isNeedCaptcha = $mem->get($captchaKey);
        if(!$isNeedCaptcha) return true;
        $verify = $mem->get('Captcha'.Util::getIp());
        if(strtolower($verify) != strtolower($captcha)){
            $mem->delete('Captcha'.Util::getIp());
            return false;
        }
        $mem->delete($captchaKey);
        $mem->delete('Captcha'.Util::getIp());
        return true;
    }

}