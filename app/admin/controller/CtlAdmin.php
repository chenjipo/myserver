<?php
namespace App\admin\controller;

use App\admin\service\SrvAdmin;
use YXLib\foundation\Debug;

class CtlAdmin{

    private $srv;
    public function __construct(){
        $this->srv = new SrvAdmin();
    }

    /**
     * 管理员列表
     */
    public function adminListPage(){
        return view('admin/adminList.html');
    }

    /**
     * 管理员列表
     */
    public function addAdminPage(){
        return view('admin/addAdmin.html');
    }

    public function getAdminList()
    {
        return $this->srv->getAdminList(getAll());
    }

    public function getAdminInfo()
    {
        $id = get('id');
        return $this->srv->getAdminInfo($id);
    }

    public function getAdminOption()
    {
        $params = getAll();
        return $this->srv->getAdminOption($params);
    }

    /**
     * 修改密码
     */
    public function changePwPage(){
        return view('admin/changePw.html');
    }

    public function getAdminOwnInfo()
    {
        $id = get('id');
        return $this->srv->getAdminOwnInfo($id);
    }

    /**
     * 删除管理员
     */
    public function deleteAdminAction(){
        $id = R('id');
        return $this->srv->deleteAdminAction($id);
    }

    /**
     * 修改密码
     */
    public function changePwAction(){
        return $this->srv->changePwAction(postAll());
    }

    public function addAdminAction()
    {
        return $this->srv->addAdminAction(postAll());
    }

    /**
     * 更新用户状态
     *
     * @return void
     */
    public function updateAdminStatusAction()
    {
        $status = R('state','int',0);
        $adminId = R('admin_id','int',0);
        return $this->srv->updateAdminStatusAction($adminId,$status);
    }

    /**
     * 角色列表
     */
    public function roleListPage(){
        return view('admin/roleList.html');
    }
    public function addRolePage()
    {
        return view('admin/addRole.html');
    }

    public function getRoleList()
    {
        $params = getAll();
        return $this->srv->getRoleList($params);
    }

    public function getRoleInfo()
    {
        $roleId = get('id');
        return $this->srv->getRoleInfo($roleId);
    }
    public function deleteRoleAction()
    {
        $roleId = post('id');
        return $this->srv->deleteRoleAction($roleId);
    }
    public function addRoleAction()
    {
        return $this->srv->addRoleAction(postAll());
    }
    //------------------------------------------------
    
    //数据权限
    public function dataAuthorityListPage()
    {
        return view('admin/dataAuthorityList.html');
    }
    //数据权限添加页
    public function addDataAuthorityPage()
    {
        return view('admin/addDataAuthority.html');
    }
    //获取数据权限列表
    public function getDataAuthorityList()
    {
        $params = getAll();
        return $this->srv->getDataAuthorityList($params);
    }
    //获取数据权限详情
    public function getDataAuthorityInfo()
    {
        $roleId = get('id');
        return $this->srv->getDataAuthorityInfo($roleId);
    }
    //删除数据权限操作
    public function deleteDataAuthorityAction()
    {
        $roleId = post('id');
        return $this->srv->deleteDataAuthorityAction($roleId);
    }
    //添加数据权限操作
    public function addDataAuthorityAction()
    {
        return $this->srv->addDataAuthorityAction(postAll());
    }

    //数据权限授权管理
    public function dataAuthorityEmpowerPage()
    {
        return view('admin/dataAuthorityEmpower.html');
    }

    //获取数据权限列表
    public function getDataAuthorityTree()
    {
        $id = get('id');
        return $this->srv->getDataAuthorityTree($id);
    }

    //数据权限授权操作
    public function dataAuthorityEmpowerAction()
    {
        return $this->srv->dataAuthorityEmpowerAction(postAll());
    }
    
    //------------------------------------------------
    //投放权限
    public function mkAuthorityListPage()
    {
        return view('admin/mkAuthorityList.html');
    }
    //投放限添加页
    public function addMkAuthorityPage()
    {
        return view('admin/addMkAuthority.html');
    }
    //获取投放权限列表
    public function getMkAuthorityList()
    {
        $params = getAll();
        return $this->srv->getMkAuthorityList($params);
    }
    //获取投放权限详情
    public function getMkAuthorityInfo()
    {
        $roleId = get('id');
        return $this->srv->getMkAuthorityInfo($roleId);
    }
    //删除投放权限操作
    public function deleteMkAuthorityAction()
    {
        $roleId = post('id');
        return $this->srv->deleteMkAuthorityAction($roleId);
    }
    //添加投放权限操作
    public function addMkAuthorityAction()
    {
        return $this->srv->addMkAuthorityAction(postAll());
    }

    //投放权限授权管理
    public function mkAuthorityEmpowerPage()
    {
        return view('admin/mkAuthorityEmpower.html');
    }

    //获取投放权限树形列表
    public function getMkAuthorityTree()
    {
        $id = get('id');
        return $this->srv->getMkAuthorityTree($id);
    }

    //投放权限授权操作
    public function mkAuthorityEmpowerAction()
    {
        return $this->srv->mkAuthorityEmpowerAction(postAll());
    }

    //------------------------------------------------
    //权限列表
    public function menuListPage()
    {
        return view('admin/menuList.html');
    }

    public function getMenuList()
    {
        $params = J();
        return $this->srv->getMenuList($params);
    }

    public function addMenuPage()
    {
        return view('admin/addMenu.html');
    }
    
    public function getMenuTreeList()
    {
        $params = J();
        return $this->srv->getMenuTreeList($params);
    }

    public function getMenuInfo()
    {
        $id = get('id');
        return $this->srv->getMenuInfo($id);
    }

    public function addMenuAction()
    {
        return $this->srv->addMenuAction(postAll(true));
    }

    public function deleteMenuAction()
    {
        $id = post('id');
        return $this->srv->deleteMenuAction($id);
    }

    public function rolePermissionPage()
    {
        return view('admin/rolePermission.html');
    }

    public function getRolePermisonList(){
        $params = [
            'role_id'=>get('role_id')
        ];
        return $this->srv->getRolePermisonList($params);
    }

    public function addRolePermission()
    {
        return $this->srv->addRolePermission(postAll(true));
    }

    public function getAdminNav()
    {
        return $this->srv->getAdminNav();
    }

    public function autoGetNavPage()
    {
        return view('admin/autoGetNav.html');
    }

    public function autoGetNavList()
    {
        return $this->srv->autoGetNavList();
    }

}