<?php
namespace App\admin\service;

use App\admin\common\Tree;
use App\admin\model\ModAdmin;
use YXLib\foundation\Debug;
use YXLib\support\Util;

class SrvAdmin{

    private $mod;
    public function __construct(){
        $this->mod = new ModAdmin();
    }

    public function getAdminList($params)
    {
        $list = $this->mod->getAdminList($params);
        return success($list,'success');
    }

    public function getAdminOption($params)
    {
        $list = $this->mod->getAdminList($params);
        $data = [];
        foreach ($list['list'] as $key => $value) {
            $data[] = [
                'admin_id'=>$value['admin_id'],
                'nick'=>$value['nick'],
            ];
        }
        return success($data,'success');
    }

    /**
     * 获取菜单
     * @return array
     */
    public function getMenu(){
        $menu = config('nav');
        $m = array();
        foreach ($menu as $mu) {
            foreach ($mu as $u) {
                if($u['children']){
                    foreach ($u['children'] as $v) {
                        if($v['children']){
                            foreach ($v['children'] as $c) {
                                $m[$u['title']][$c['href']] = $c['title'];
                            }
                        }else{
                            $m[$u['title']][$v['href']] = $v['title'];
                        }
                    }
                }else{
                    $m[$u['title']][$u['href']] = $u['title'];
                }
            }
        }
        return $m;
    }

    /**
     * 获取管理员信息
     * @param $admin_id
     * @return array|bool|resource|string
     */
    public function getAdminInfo($admin_id){
        if(!$admin_id) return fail('获取失败');
        $info = $this->mod->getAdminInfo($admin_id);
        if($info['phone_login']) $info['phone_login'] = true;
        else $info['phone_login'] = false;
        return success($info,'success');
    }

    public function getAdminOwnInfo($adminId)
    {
        if(!$adminId) return fail('获取失败');
        if($adminId != SrvAuth::$adminId) return fail('获取失败');
        $info = $this->mod->getAdminInfo($adminId);
        $user = [
            'nick'=>$info['nick'],
            'phone'=>$info['phone'],
            'role_id'=>$info['role_id'],
            'admin_id'=>$info['admin_id'],
            'user'=>$info['user']
        ];
        return success($user,'success');
    }

    /**
     * 删除管理员
     * @param $admin_id
     * @return array
     */
    public function deleteAdminAction($id){
        $result = $this->mod->deleteAdminAction($id);
        if($result){
            return success([],'操作成功');
        }
        return fail('操作失败');
    }

    /**
     * 更新用户状态
     *
     * @param [type] $adminId
     * @param [type] $status
     * @return void
     */
    public function updateAdminStatusAction($adminId,$status)
    {
        if(!$adminId||!$status){
            return fail('非法操作');
        }
        $result = $this->mod->updateAdminStatusAction($adminId,$status);
        if($result){
            return success([],'操作成功');
        }
        return fail('操作失败');
    }

    public function addAdminAction($data)
    {
        if(!$data['user']){
            return fail('帐号不能为空');
        }
        if($data['pwd']){
            if($data['pwd'] != $data['pwd2']){
                return fail('两次输入密码不一致');
            }
            if(preg_match('/[a-zA-Z]/',$data['pwd'])==0){
                return fail('密码必须包含字母');
            }
            if(preg_match('/[0-9]/',$data['pwd'])==0){
                return fail('密码必须包含数字');
            }
            if(strlen($data['pwd']) < 6){
                return fail('密码不能低于6位');
            }
        }
        $save = [
            'nick'=>trim($data['nick']),
            'phone'=>trim($data['phone']),
            'phone_login'=>trim($data['phone_login'])?1:0,
            'role_id'=>(int)$data['role_id'],
            'mk_id'=>(int)$data['mk_id'],
            'data_id'=>(int)$data['data_id'],
        ];
        $salt = Util::getSalt(10);
        if($data['admin_id']){
            if($data['pwd']){
                $save['salt'] = $salt;
                $save['pwd'] = SrvAuth::signPwd($data['user'],$data['pwd'],$salt);
            }
            $result = $this->mod->updateAdminAction($data['admin_id'],$save);
            $admin_id = $data['admin_id'];
        }else{
            if(!$data['pwd']){
                return fail('密码不能为空');
            }
            $insert = array(
                'user' => $data['user'],
                'salt' => $salt,
                'pwd' => SrvAuth::signPwd($data['user'],$data['pwd'],$salt),
                'state' => 0,
                'atime' => time(),
            );
            $insert = array_merge($save,$insert);
            $admin_id = $this->mod->addAdminAction($insert);
        }
        if($admin_id){
            SrvAuthBusiness::updateUserAuthority($admin_id);
            return success([],'操作成功');
        }else{
            return fail('操作失败');
        }
    }


    /**
     * 修改密码
     * @param $admin_id
     * @param $data
     * @return array
     */
    public function changePwAction($data){
        if(!$data['admin_id']) return fail('操作失败');
        if($data['admin_id'] != SrvAuth::$adminId) return fail('操作失败');
        if($data['pwd']){
            if($data['pwd'] != $data['pwd2']){
                return fail('两次输入密码不一致');
            }
            if(preg_match('/[a-zA-Z]/',$data['pwd'])==0){
                return fail('密码必须包含字母');
            }
            if(preg_match('/[0-9]/',$data['pwd'])==0){
                return fail('密码必须包含数字');
            }
            if(strlen($data['pwd']) < 6){
                return fail('密码不能低于6位');
            }
        }
        $update = [
            'pwd'=>$data['pwd'],
            'phone'=>$data['phone'],
        ];
        $result = $this->mod->updatePwAction($data['admin_id'],$update);
        if($result){
            return success([],'success');
        }else{
            return fail('操作失败');
        }
    }

    public function getRoleList($params)
    {
        $list = $this->mod->getRoleList($params);
        return success($list,'success');
    }

    public function getRoleInfo($id)
    {
        if(!$id) return fail('操作失败');
        $info = $this->mod->getRoleInfo($id);
        return success($info,'success');
    }
    public function deleteRoleAction($id)
    {
        if(!$id) return fail('缺少参数');
        $ret = $this->mod->deleteRoleAction($id);
        if(!$ret){
            return fail('操作失败');
        }
        return success([],'操作成功');
    }
    public function addRoleAction($params)
    {
        if(!$params['id']){
            //update
            $ret = $this->mod->addRoleAction($params);
        }else{
            $id = $params['id'];
            unset($params['id']);
            $ret = $this->mod->updateRoleAction($id,$params);
        }
        return success([],'操作成功');
    }

    //-----------------------------------

    public function getDataAuthorityList($params)
    {
        $list = $this->mod->getDataAuthorityList($params);
        return success($list,'success');
    }

    public function getDataAuthorityInfo($id)
    {
        if(!$id) return fail('操作失败');
        $info = $this->mod->getDataAuthorityInfo($id);
        return success($info,'success');
    }
    public function deleteDataAuthorityAction($id)
    {
        if(!$id) return fail('缺少参数');
        $ret = $this->mod->deleteDataAuthorityAction($id);
        if(!$ret){
            return fail('操作失败');
        }
        return success([],'操作成功');
    }
    public function addDataAuthorityAction($params)
    {
        $data = [
            'name'=>$params['name'],
        ];
        if(!$params['id']){
            //update
            $ret = $this->mod->addDataAuthorityAction($data);
        }else{
            $id = $params['id'];
            unset($params['id']);
            $ret = $this->mod->updateDataAuthorityAction($id,$data);
        }
        return success([],'操作成功');
    }

    public function getDataAuthorityTree($id)
    {
        if(!$id){
            return fail('操作失败');
        }
        $info = $this->mod->getDataAuthorityInfo($id);
        $mgids = explode(',',$info['mgids']);
        $pids = explode(',',$info['pids']);
        $cids = explode(',',$info['cids']);
        $mgidsData = $this->mod->getDataAuthroityMGame();
        $pidsData = $this->mod->getDataAuthroityPids();
        $cidsData = $this->mod->getDataAuthroityCids();
        return success(
            [
                'mgids'=>['list'=>$mgidsData,'value'=>$mgids],
                'pids'=>['list'=>$pidsData,'value'=>$pids],
                'cids'=>['list'=>$cidsData,'value'=>$cids]
            ]
            ,'success');
    }

    public function dataAuthorityEmpowerAction($data)
    {
        if(!$data['id']) return fail('操作失败');
        $mgids = $pids = $cids = [];
        if($data['mgids']){
            $mgids = array_column($data['mgids'],'value');
            sort($mgids);
        }
        if($data['pids']){
            $pids = array_column($data['pids'],'value');
            sort($pids);
        }
        if($data['cids']){
            $cids = array_column($data['cids'],'value');
            sort($cids);
        }
        $update = [
            'mgids'=>implode(',',$mgids),
            'pids'=>implode(',',$pids),
            'cids'=>implode(',',$cids),
        ];
        $ret = $this->mod->updateDataAuthorityAction($data['id'],$update);
        if($ret){
            $list = $this->mod->getAdminList(['data_id'=>$data['id'],'state'=>0]);
            if($list['list']){
                foreach($list['list'] as $v){
                    SrvAuthBusiness::updateUserAuthority($v['admin_id']);
                }
            }
        }
        return success([],'授权成功');
    }

    //-----------------------------------
    public function getMkAuthorityList($params)
    {
        $list = $this->mod->getMkAuthorityList($params);
        return success($list,'success');
    }

    public function getMkAuthorityInfo($id)
    {
        if(!$id) return fail('操作失败');
        $info = $this->mod->getMkAuthorityInfo($id);
        return success($info,'success');
    }
    public function deleteMkAuthorityAction($id)
    {
        if(!$id) return fail('缺少参数');
        $ret = $this->mod->deleteMkAuthorityAction($id);
        if(!$ret){
            return fail('操作失败');
        }
        return success([],'操作成功');
    }
    public function addMkAuthorityAction($params)
    {
        $data = [
            'name'=>$params['name'],
        ];
        if(!$params['id']){
            //update
            $ret = $this->mod->addMkAuthorityAction($data);
        }else{
            $id = $params['id'];
            unset($params['id']);
            $ret = $this->mod->updateMkAuthorityAction($id,$data);
        }
        return success([],'操作成功');
    }

    public function getMkAuthorityTree($id)
    {
        if(!$id){
            return fail('操作失败');
        }
        $info = $this->mod->getMkAuthorityInfo($id);
        $pitids = explode(',',$info['pitids']);
        $list = $this->mod->getPitcherList();
        $team = $pitcher = [];
        foreach ($list as $v) {
            $team[$v['teamid']] = $v['teamname'];
            $checked = false;
            if($pitids && in_array($v['pitid'],$pitids)){
                $checked = true;
            }
            $pitcher[$v['teamid']][] = [
                'id'=>$v['pitid'],
                'parent_id'=>$v['teamid'],
                'title'=>$v['pitname'],
                'spread'=>true,
                'checked'=>$checked,
            ];
        }

        $tree = [];
        foreach($team as $teamid=>$teamname){
            $tree[] = [
                'id'=>$teamid,
                'parent_id'=>0,
                'title'=>$teamname,
                'spread'=>true,
                'checked'=>false,
                'children'=>$pitcher[$teamid],
            ];
        }

        unset($info['pitids']);
        return success(['list'=>$tree,'info'=>$info],'success');
    }

    public function mkAuthorityEmpowerAction($data)
    {
        if(!$data['id']){
            return fail('操作失败');
        }
        
        $pitids = [];
        if($data['permission']){
            foreach($data['permission'] as $team){
                foreach($team['children'] as $pitcher){
                    $pitids[] = $pitcher['id'];
                }
            }
        }
        sort($pitids);
        $pitids = implode(',',$pitids);
        $ret = $this->mod->updateMkAuthorityAction($data['id'],['pitids'=>$pitids]);
        $list = $this->mod->getAdminList(['mk_id'=>$data['id'],'state'=>0]);
        if($list['list']){
            foreach($list['list'] as $v){
                SrvAuthBusiness::updateUserAuthority($v['admin_id']);
            }
        }
        return success([],'授权成功');
    }

    public function getMenuList($params)
    {
        $list = $this->mod->getMenuList($params);
        return success($list,'success');
    }

    public function getMenuTreeList($params)
    {
        $list = $this->mod->getMenuList($params);
        $list = Tree::unlimitedForLevel($list,'━━');
        return success($list,'success');
    }

    public function getMenuInfo($id)
    {
        if(!$id){
            return fail('fail');
        }
       
        $info = $this->mod->getMenuInfo($id);
        if($info['show_menu']) $info['show_menu'] = true;
        else $info['show_menu'] = false;
        return success($info,'success');
    }

    public function addMenuAction($params)
    {
        if(!$params['display_name']){
            return fail('缺少参数');
        }
        if($params['route']){
            $params['route'] = '/'.trim($params['route'],'/').'/';
        }
        $data = [
            'name'=>$params['name'],
            'display_name'=>$params['display_name'],
            'route'=>$params['route'],
            'icon'=>$params['icon'],
            'parent_id'=>$params['parent_id'],
            'sort'=>$params['sort'],
            'type'=>$params['type'],
            'show_menu'=>$params['show_menu']=='on'?1:0,
        ];
        if(!$params['id']){
            $data['atime'] = time();
            $this->mod->addMenuAction($data);
        }else{
            $id = $params['id'];
            //TODO 检查parent_id，不允许是自己或者children
            $list = $this->mod->getMenuList([]);
            $children = Tree::getClildren($list,$id);
            $childrenIds = array_column($children,'id');
            if($params['parent_id'] == $id || in_array($params['parent_id'],$childrenIds)){
                return  fail('父级不能是自己或者是自己的子级');
            }
            $this->mod->updateMenuAction($id,$data);
        }
        return success([],'操作成功');
    }

    public function deleteMenuAction($id)
    {
        if(!$id) return fail('缺少参数');
        $ret = $this->mod->deleteMenuAction($id);
        if(!$ret){
            return fail('操作失败');
        }
        return success([],'操作成功');
    }

    public function getRolePermisonList($params)
    {
        if(!$params['role_id']){
            return fail('操作失败');
        }
        $roleInfo = $this->mod->getRoleInfo($params['role_id']);
        $permissions = $this->mod->roleHasPermission($params['role_id']);
        return success(['hasauths'=>$permissions,'info'=>$roleInfo],'success');
    }

    public function addRolePermission($params)
    {
        $roleId = $params['role_id'];
        $permission = json_decode($params['permission'],true);
        if(!$roleId || !$permission){
            return fail('操作失败!');
        } 
        sort($permission);
        $this->mod->addRolePermission($roleId,$permission);
        return success([],'操作成功');
    }

    public function getAdminNav()
    {
        $permissions = $this->mod->roleHasPermission(SrvAuth::$roleId);
        $list = $this->mod->getMenuList(['ids'=>$permissions,'show_menu'=>1,'type'=>1]);
        // $list = $this->mod->getMenuList(['show_menu'=>1,'type'=>1]);
        $list = Tree::unLimitedForLayer($list);
        return success(['list'=>$list,'user'=>SrvAuth::$info],'success');
    }

    public function autoGetNavList()
    {
        $menuList = $this->mod->getMenuList();
        $routes = array_column($menuList,'route');
        $routes = array_filter($routes);
        $data = [];
        $classDir = APP_ROOT . "/controller";
        $classNames = scandir($classDir);
        foreach($classNames as $className){
            if($className != ".." && $className != "."){
                if(is_file($classDir."/".$className)){
                    $fileContent = file_get_contents($classDir."/".$className);
                    $class = substr($className,0,-4);
                    $ct = lcfirst(str_replace('Ctl','',$class));
                    $classFullName = "App\\admin\\controller\\".$class;
                    $methods = get_class_methods($classFullName);
                    // var_export($methods);
                    foreach($methods as $ac){
                        $route = '/'.$ct.'/'.$ac.'/';
                        if($ac == '__construct') continue;
                        if($ct == 'login') continue;
                        if($ct == 'index') continue;
                        if(!in_array($route,$routes)){
                            $name = '';
                            if(preg_match("/\/\/(.*?)\n(.*?)public function $ac\(/", $fileContent, $match)){
                                $name = $match[1];
                            }
                            $data[] = [
                                'route'=> $route,
                                'name'=> $name
                            ];
                        }
                    }
                    
                }
            }
        }
        return success(['list'=>$data]);
    }
}