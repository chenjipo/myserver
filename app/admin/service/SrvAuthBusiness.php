<?php
namespace App\admin\service;

use App\admin\model\ModAdmin;
use App\common\Table;
use YXLib\foundation\Debug;

/**
 * 业务相关权限
 */
class SrvAuthBusiness {

    //更新用户的投放权限、数据权限; 登陆时或更新后台用户或更新权限组时调用
    public static function updateUserAuthority($adminId)
    {
        $modAdmin = new ModAdmin();
        $info = $modAdmin->getAdminInfo($adminId);
        //获取投放权限授权配置
        $authority = [];
        if($info['mk_id']){
            $mk = $modAdmin->getMkAuthorityInfo($info['mk_id']);
            if($mk && !$mk['pitids']){
                //仅自己
                $authority = [$adminId];
            }else{
                $authority = explode(',',$mk['pitids']);
                $authority[] = $adminId;
                $authority = array_unique($authority);
            }
        }
        if(!self::updateMkAuthorityCache($adminId,$authority)){
            return false;
        }
        //获取数据权限授权配置
        $authority = ['mgids'=>[],'pids'=>[],'cids'=>[]];
        if($info['data_id']){
            $data = $modAdmin->getDataAuthorityInfo($info['data_id']);
            if($data) {
                $authority = [
                    'mgids'=>$data['mgids'] ? explode(',',$data['mgids']) : [],
                    'pids'=>$data['pids'] ? explode(',',$data['pids']): [],
                    'cids'=>$data['cids'] ? explode(',',$data['cids']): [],
                ];
            }
        }
        if(!self::updateDataAuthorityCache($adminId,$authority)){
            return false;
        }
        return true;
    }

    /**
     * 更新用户数据权限授权文件
     *
     * @param [type] $adminId
     * @param [type] $authority
     * @return void
     */
    public static function updateDataAuthorityCache($adminId,$authority = [])
    {
        $dir = ROOT . '/runtime/authority/data/';
        if(!is_dir($dir)){
            $re = mkdir($dir, 0777, true);
            if(!$re){
                return false;
            }
        }
        return file_put_contents($dir.'user_'.$adminId,"<?php\nreturn ".var_export($authority,true).";");
    }

    /**
     * 获取用户数据权限授权配置
     *
     * @param [type] $adminId
     * @return void
     */
    public static function getDataAuthorityCache($adminId = false)
    {
        if(!$adminId) $adminId = SrvAuth::$adminId;
        $file = ROOT . '/runtime/authority/data/user_'.$adminId;
        if(is_file($file)) {
            return include ($file);
        }
        return [];
    }

    /**
     * 组装数据权限sql
     *
     * @param string $mgidTableName
     * @param string $pidTableName
     * @param string $cidTableName
     * @return array
     */
    public static function makeDataAuthoritySqlAnd($mgidTableName='',$pidTableName='',$cidTableName='')
    {
        $and = '';
        $param = [];
        $authorityData = self::getDataAuthorityCache();
        if(!$authorityData){
            return ['and'=>$and,'param'=>$param];
        }
        if($mgidTableName && $authorityData['mgids']){
            $param['mgids_auth'] = $authorityData['mgids'];
            $and .= " and {$mgidTableName}.`mgid` in (:mgids_auth)";
        }
        if($pidTableName && $authorityData['pids']){
            $param['pids_auth'] = $authorityData['pids'];
            $and .= " and {$pidTableName}.`pid` in (:pids_auth)";
        }
        if($cidTableName && $authorityData['cids']){
            $param['cids_auth'] = $authorityData['cids'];
            $and .= " and {$cidTableName}.`cid` in (:cids_auth)";
        }

        return ['and'=>$and,'param'=>$param];
    }

    /**
     * 更新用户投放权限授权文件
     *
     * @param [type] $adminId
     * @param [type] $authority
     * @return array
     */
    public static function updateMkAuthorityCache($adminId,$authority = [])
    {
        $dir = ROOT . '/runtime/authority/mk/';
        if(!is_dir($dir)){
            $re = mkdir($dir, 0777, true);
            if(!$re){
                return false;
            }
        }
        return file_put_contents($dir.'user_'.$adminId,"<?php\nreturn ".var_export($authority,true).";");
    }

    /**
     * 获取用户投放授权配置
     *
     * @param [type] $adminId
     * @return array
     */
    public static function getMkAuthorityCache($adminId = false)
    {
        if(!$adminId) $adminId = SrvAuth::$adminId;
        $file = ROOT . '/runtime/authority/mk/user_'.$adminId;
        if(is_file($file)) {
            return include ($file);
        }
        return [];
    }

    /**
     * 获取登陆用户的投放组权限 【代理、主体】
     *
     * @return array
     */
    public static function getPitcherTeamAuthority()
    {
        $config = sy_data_read(Table::$mk_pitcher,'all');
        $teamId = $config[SrvAuth::$adminId];
        $info = sy_data_read(Table::$mk_pitcher_team,$teamId);
        return $info;
    }

   
}