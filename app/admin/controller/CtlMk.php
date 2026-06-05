<?php
namespace App\admin\controller;

use App\admin\service\SrvMk;
use YXLib\foundation\Debug;

class CtlMk {

    private $srv;
    public function __construct(){
        $this->srv = new SrvMk();
    }

    //投放分组
    public function pitcherTeamPage()
    {
        return view('mk/pitcherTeam.html');
    }

    //获取投放分组数据
    public function getPitcherTeamList()
    {
        $params = getAll();
        return $this->srv->getPitcherTeamList($params);
    }

    //获取投放分组选项
    public function getPitcherTeamOption()
    {
        $params = [];
        return $this->srv->getPitcherTeamOption($params);
    }
    
    //投放分组添加页
    public function addPitcherTeamPage()
    {
        return view('mk/addPitcherTeam.html');
    }

    //获投放分组信息
    public function getPitcherTeamInfo()
    {
        $id = get('id');
        return $this->srv->getPitcherTeamInfo($id);
    }

    //获投放分组添加操作
    public function addPitcherTeamAction()
    {
        $data = postAll();
        return $this->srv->addPitcherTeamAction($data);
    }

    //删除投放分组
    public function deletePitcherTeamAction()
    {
        $id = post('id');
        return $this->srv->deletePitcherTeamAction($id);
    }

    /**
     * -------------------------------------------------
     */
    //投放人员
    public function pitcherPage()
    {
        return view('mk/pitcher.html');
    }

    //获取投放人员数据
    public function getPitcherList()
    {
        $params = getAll();
        return $this->srv->getPitcherList($params);
    }

    //获取投放人员选项
    public function getPitcherOption()
    {
        $params = [];
        return $this->srv->getPitcherOption($params);
    }

    //获取可供选择为投手的人员option
    public function getEnabledPitcherOption()
    {
        return $this->srv->getEnabledPitcherOption();
    }
    
    //投放人员添加页
    public function addPitcherPage()
    {
        return view('mk/addPitcher.html');
    }

    //获投放人员信息
    public function getPitcherInfo()
    {
        $id = get('id');
        return $this->srv->getPitcherInfo($id);
    }

    //获投放人员添加操作
    public function addPitcherAction()
    {
        $data = postAll();
        return $this->srv->addPitcherAction($data);
    }

    //删除投放分组
    public function deletePitcherAction()
    {
        $id = post('id');
        return $this->srv->deletePitcherAction($id);
    }

    /**
     * -------------------------------------------------
     */

     //投放资质公司
    public function companyPage()
    {
        return view('mk/company.html');
    }

    //获取投放资质公司数据
    public function getCompanyList()
    {
        $params = getAll();
        return $this->srv->getCompanyList($params);
    }

    //获取投放资质公司选项
    public function getCompanyOption()
    {
        $params = [];
        return $this->srv->getCompanyOption($params);
    }

    //获取投放资质公司选项-不受数据投放权限控制
    public function getCompanyOptionNoAuth()
    {
        $params = [];
        return $this->srv->getCompanyOptionNoAuth($params);
    }
    
    //投放资质公司添加页
    public function addCompanyPage()
    {
        return view('mk/addCompany.html');
    }

    //获投放资质公司信息
    public function getCompanyInfo()
    {
        $id = get('id');
        return $this->srv->getCompanyInfo($id);
    }

    //获投放资质公司添加操作
    public function addCompanyAction()
    {
        $data = postAll(true);
        return $this->srv->addCompanyAction($data);
    }

    //删除投放资质公司
    public function deleteCompanyAction()
    {
        $id = post('id');
        return $this->srv->deleteCompanyAction($id);
    }

    /**
     * -------------------------------------------------
     */

    //投放代理
    public function agentPage()
    {
        return view('mk/agent.html');
    }

    //获取代理数据
    public function getAgentList()
    {
        $params = getAll();
        return $this->srv->getAgentList($params);
    }

    //获取代理选项-不受数据投放权限控制
    public function getAgentOptionNoAuth()
    {
        $params = [];
        return $this->srv->getAgentOptionNoAuth($params);
    }

    //获取代理选项
    public function getAgentOption()
    {
        $params = [];
        return $this->srv->getAgentOption($params);
    }
    
    //代理添加页
    public function addAgentPage()
    {
        return view('mk/addAgent.html');
    }

    //获投代理信息
    public function getAgentInfo()
    {
        $id = get('id');
        return $this->srv->getAgentInfo($id);
    }

    //代理添加操作
    public function addAgentAction()
    {
        $data = postAll();
        return $this->srv->addAgentAction($data);
    }

    //删除代理操作
    public function deleteAgentAction()
    {
        $id = post('id');
        return $this->srv->deleteAgentAction($id);
    }

    /**
     * -------------------------------------------------
     */

    //代理返点
    public function agentRebatePage()
    {
        return view('mk/agentRebate.html');
    }

    //代理返点添加页
    public function addAgentRebatePage()
    {
        return view('mk/addAgentRebate.html');
    }

    //获取代理返点数据
    public function getAgentRebateList()
    {
        return $this->srv->getAgentRebateList(getAll());
    }

    //代理返点添加操作
    public function addAgentRebateAction()
    {
        return $this->srv->addAgentRebateAction(postAll());
    }

    //代理返点删除操作
    public function delAgentRebateAction()
    {
        $id = post('id');
        return $this->srv->delAgentRebateAction($id);
    }

    /**
     * -------------------------------------------------
     */

    //投放账户
    public function adAccountPage()
    {
        return view('mk/adAccount.html');
    }

    //获取投放账户数据
    public function getAdAccountList()
    {
        $params = getAll();
        return $this->srv->getAdAccountList($params);
    }

    //获取投放账户选项
    public function getAdAccountOption()
    {
        return $this->srv->getAdAccountOption(getAll());
    }
    
    //投放账户添加页
    public function addAdAccountPage()
    {
        return view('mk/addAdAccount.html');
    }

    //获投放账户信息
    public function getAdAccountInfo()
    {
        $id = get('id');
        return $this->srv->getAdAccountInfo($id);
    }

    //获取渠道账户配置模板
    public function getChannelConfigTplInfo()
    {
        $id = get('cid');
        return $this->srv->getChannelConfigTplInfo($id);
    }

    //获投放账户添加操作
    public function addAdAccountAction()
    {
        $data = postAll();
        return $this->srv->addAdAccountAction($data);
    }

    //删除投放账户
    public function deleteAdAccountAction()
    {
        $id = post('id');
        return $this->srv->deleteAdAccountAction($id);
    }

    //切换投放账户状态
    public function changeAdAccountStatus()
    {
        $params = postAll();
        return $this->srv->changeAdAccountStatus($params);
    }

    /**
     * -------------------------------------------------
     */

    //推广链
    public function adLinkPage()
    {
        return view('mk/adLink.html');
    }

    //获取推广链数据
    public function getAdLinkList()
    {
        $params = getAll();
        $params['status'] = 1;//只显示未关闭的
        return $this->srv->getAdLinkList($params);
    }

    //获取推广链选项
    public function getAdLinkOption()
    {
        $params = getAll();
        return $this->srv->getAdLinkOption($params);
    }
    
    //媒体推广链添加页
    public function addAdLinkPage()
    {
        return view('mk/addAdLink.html');
    }

    //联运推广链添加页面
    public function addAdLinkPartnerPage()
    {
        return view('mk/addAdLinkPartner.html');
    }

    //获投推广链信息
    public function getAdLinkInfo()
    {
        $id = get('id');
        return $this->srv->getAdLinkInfo($id);
    }

    //推广链添加操作
    public function addAdLinkAction()
    {
        $data = postAll();
        return $this->srv->addAdLinkAction($data);
    }

    //联运推广链添加操作
    public function addAdLinkPartnerAction()
    {
        $data = postAll();
        return $this->srv->addAdLinkPartnerAction($data);
    }

    //删除推广链
    public function deleteAdLinkAction()
    {
        $id = post('id');
        return $this->srv->deleteAdLinkAction($id);
    }

    //修改推广链名称
    public function updateAdLNameAction()
    {
        $data = postAll();
        return $this->srv->updateAdLNameAction($data);
    }

    // ----------------------------
    //头条管家号
    public function ttMajordomo()
    {
        return view('mk/ttMajordomo.html');
    }
    //获取头条管家号列表
    public function getTtMajordomoList()
    {
        $params =  getAll();
        return $this->srv->getTtMajordomoList($params);
    }

    //头条管家号添加操作
    public function addTtMajordomoAction()
    {
        $params = $_POST;
        return $this->srv->addTtMajordomoAction($params);
    }

    //获取头条管家下的广告主
    public function pullTtAdvertisers()
    {
        $account = get('account');
        return $this->srv->pullTtAdvertisers($account);
    }

    //获取头条管家号信息
    public function getTtMajordomoInfo()
    {
        $id = get('id');
        return $this->srv->getTtMajordomoInfo($id);
    }

    //同步头条账户
    public function syncTtAdvertiser()
    {
        $params = postAll();
        return $this->srv->syncTtAdvertiser($params);
    }

    //切换管家号状态
    public function changeTtMajordomoStatus()
    {
        $params = postAll();
        return $this->srv->changeTtMajordomoStatus($params);
    }
}