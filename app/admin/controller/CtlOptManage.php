<?php
namespace App\admin\controller;

use App\admin\service\SrvOptManage;
use YXLib\foundation\Debug;

/**
 * 运营管理
 */
class CtlOptManage {

    private $srv;
    public function __construct(){
        $this->srv = new SrvOptManage();
    }

    //订单列表
    public function orderPage()
    {
        return view('optmanage/order.html');
    }

    //获取订单列表数据
    public function getOrderList()
    {
        return $this->srv->getOrderList(getAll());
    }

    //获取订单日志
    public function getOrderLog()
    {
        $ptno = get('ptno');
        return $this->srv->getOrderLog($ptno);
    }

    //手动发放游戏币
    public function orderHandSend()
    {
        $ptno = post('ptno');
        return $this->srv->orderHandSend($ptno);
    }

    //-----------------------------------
    //用户列表
    public function userPage()
    {
        return view('optmanage/user.html');
    }

    //用户详情页
    public function userInfoPage()
    {
        return view('optmanage/userinfo.html');
    }
    
    //获取用户列表数据
    public function getUserList()
    {
        return $this->srv->getUserList(getAll());
    }

    //获取用户详情
    public function getUserInfo()
    {
        $uid = get('uid');
        return $this->srv->getUserInfo($uid);
    }

    //角色列表
    public function userRolePage()
    {
        return view('optmanage/userrole.html');
    }
    
    //获取角色列表数据
    public function getUserRoleList()
    {
        return $this->srv->getUserRoleList(getAll());
    }

    //解绑手机号
    public function unBindPhone()
    {
        $params = postAll();
        return $this->srv->unBindPhone($params);
    }

    //封禁用户操作
    public function bandUser()
    {
        $params = postAll();
        return $this->srv->banUser($params);
    }

     
    //sdk白名单页面
    public function sdkWhiteNamePage()
    {
        return view('optmanage/sdkWhiteName.html');
    }
 
    //获取sdk白名单列表数据
    public function getSdkWhiteNameList()
    {
        $params = getAll();
        return $this->srv->getSdkWhiteNameList($params);
    }

    //sdk白名单添加页
    public function addSdkWhiteNamePage()
    {
        return view('optmanage/addSdkWhiteName.html');
    }

    //sdk白名单添加操作
    public function addSdkWhiteNameAction()
    {
        $data = postAll();
        return $this->srv->addSdkWhiteNameAction($data);
    }

    //删除sdk白名单操作
    public function deleteSdkWhiteNameAction()
    {
        $data = postAll();
        return $this->srv->deleteSdkWhiteNameAction($data);
    }

    //--------------
    //sdk黑名单页面
    public function sdkBlackNamePage()
    {
        return view('optmanage/sdkBlackName.html');
    }
 
    //获取sdk黑名单列表数据
    public function getSdkBlackNameList()
    {
        $params = getAll();
        return $this->srv->getSdkBlackNameList($params);
    }

    //sdk黑名单添加页
    public function addSdkBlackNamePage()
    {
        return view('optmanage/addSdkBlackName.html');
    }

    //sdk黑名单添加操作
    public function addSdkBlackNameAction()
    {
        $data = postAll();
        return $this->srv->addSdkBlackNameAction($data);
    }

    //删除sdk黑名单操作
    public function deleteSdkBlackNameAction()
    {
        $data = postAll();
        return $this->srv->deleteSdkBlackNameAction($data);
    }

    //--------------
    //sdk公告
    public function announcementPage()
    {
        return view('optmanage/announcement.html');
    }
 
    //获取sdk公告列表数据
    public function getAnnouncementList()
    {
        $params = getAll();
        return $this->srv->getAnnouncementList($params);
    }

    //获取sdk公告详情
    public function getAnnouncementInfo()
    {
        $id = get('id');
        return $this->srv->getAnnouncementInfo($id);
    }

    //sdk公告添加页
    public function addAnnouncementPage()
    {
        return view('optmanage/addAnnouncement.html');
    }

    //sdk公告添加操作
    public function addAnnouncementAction()
    {
        $data = postAll();
        return $this->srv->addAnnouncementAction($data);
    }

    //一键发布公告
    public function syncAnnouncementAction()
    {
        return $this->srv->syncAnnouncementAction();
    }

}