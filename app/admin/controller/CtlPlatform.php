<?php
namespace App\admin\controller;

use App\admin\service\SrvPlatform;
use App\common\Game;
use YXLib\foundation\Debug;

class CtlPlatform{

    private $srv;
    public function __construct(){
        $this->srv = new SrvPlatform();
    }

    //研发分成配置
    public function gameDividePage()
    {
        return view('platform/gameDivide.html');
    }

    //获取研发分成配置数据
    public function getGameDivideList()
    {
        $params = getAll();
        $params['date'] = get('date','string',date('Y-m'));
        $params['date'] = $params['date'].'-01';
        return $this->srv->getGameDivideList($params);
    }

    public function updateGameDivideAction()
    {
        return $this->srv->updateGameDivideAction(postAll());
    }

    //主游戏页面
    public function mgamePage()
    {
        return view('platform/mgame.html');
    }

    //主游戏数据
    public function getMgameList()
    {
        $data = [
            'page' => R('page'),
            'mgid' => R('mgid'),
            'name' => R('name'),
        ];
        return $this->srv->getMgameList($data);
    }

    //主游戏选项
    public function getMgameOption()
    {
        return $this->srv->getMgameOption();
    }

    //主游戏添加页面
    public function addMgamePage()
    {
        return view('platform/addMgame.html');
    }

    //主游戏添加操作
    public function addMgameAction()
    {
        return $this->srv->addMgameAction(postAll());
    }

    //主游戏删除操作
    public function deleteMgameAction()
    {
        $id = post('mgid');
        return $this->srv->deleteMgameAction($id);
    }

    //主游戏 信息
    public function getMgameInfo()
    {
        $id = get('mgid');
        return $this->srv->getMgameInfo($id);
    }

    //子游戏 页面
    public function gameListPage()
    {
        return view('platform/game.html');
    }

    //子游戏列表数据
    public function getGameList()
    {
        $data = [
            'page' => R('page'),
            'gid' => R('gid'),
            'mgid' => R('mgid'),
            'name' => R('name'),
        ];
        return $this->srv->getGameList($data);
    }

    //子游戏选项
    public function getGameOption()
    {
        $params = getAll();
        return $this->srv->getGameOption($params);
    }

    //子游戏信息
    public function getGameInfo()
    {
        $gid = get('gid','int',0);
        return $this->srv->getGameInfo($gid);
    }

    //子游戏添加页面
    public function addGamePage()
    {
        return view('platform/addGame.html');
    }

    //子游戏添加操作
    public function addGameAction()
    {
        $data = postAll(true);
        return $this->srv->addGameAction($data);
    }

    //配置游戏金流渠道
    public function gamePaywayPage()
    {
        return view('platform/gamePayway.html');
    }

    //配置游戏金流渠道操作
    public function addGamePaywayAction()
    {
        Debug::log(1);
        return $this->srv->addGamePaywayAction(postAll(true));
    }

    //获取游戏金流渠道配置
    public function getGamePaywayList()
    {
        Debug::log(1);
        $gid = get('gid');
        return $this->srv->getGamePaywayList($gid);
    }

    //sdk版本页
    public function gameSdkVersionPage()
    {
        return view('platform/gameSdkVersion.html');
    }

    //sdk版本添加页
    public function addGameSdkVersionPage()
    {
        return view('platform/addGameSdkVersion.html');
    }

    //获取sdk版本信息
    public function getGameSdkVersionInfo()
    {
        $id = get('id');
        return $this->srv->getGameSdkVersionInfo($id);
    }

    //获取sdk版本列表
    public function getGameSdkVersionList()
    {
        return $this->srv->getGameSdkVersionList();
    }

    //获取sdk版本选项
    public function getGameSdkVersionOption()
    {
        return $this->srv->getGameSdkVersionOption();
    }

    //sdk版本添加操作
    public function addGameSdkVersionAction()
    {
        $params = postAll();
        return $this->srv->addGameSdkVersionAction($params);
    }

    //上传游戏icon
    public function uploadGameIcon(){
        $name = R('file_name');
        $maxSize = 1024; //kb
        return $this->srv->uploadGameIcon($name,$maxSize,['.png']);
    }

    //上传游戏母包
    public function uploadApk(){
        $total = R('chunks');
        $now = R('chunk');
        $version = R('version');
        $gid = R('gid');
        $cid = R('cid');
        return $this->srv->uploadApk($gid,$cid,$version,$total,$now);
    }

    //游戏母包页
    public function gameChannelMPkgPage()
    {
        return view('platform/gameChannelMPkg.html');
    }

    //获取游戏母包数据
    public function getGameChannelMPkgList()
    {
        $params = getAll();
        return $this->srv->getGameChannelMPkgList($params);
    }

    //游戏母包配置添加页
    public function addGameChannelMPkgPage()
    {
        return view('platform/addGameChannelMPkg.html');
    }

    //游戏母包配置添加操作
    public function addGameChannelMPkgAction()
    {
        $params = postAll(true);
        return $this->srv->addGameChannelMPkgAction($params);
    }

    //获取游戏母包信息
    public function getGameChannelMPkgInfo()
    {
        $params = getAll();
        return $this->srv->getGameChannelMPkgInfo($params);
    }

    //渠道母包解析结果页面
    public function gameChannelMPkgAnalysisPage()
    {
        return view('platform/gameChannelMPkgAnalysis.html');
    }

    //获取渠道母包解析结果信息
    public function getGameChannelMPkgAnalysisInfo()
    {
        $id = get('id');
        return $this->srv->getGameChannelMPkgAnalysisInfo($id);
    }

    //获取可用的母包信息
    public function getAvailableMPkg()
    {
        $params = getAll();
        return $this->srv->getAvailableMPkg($params);
    }

    //渠道页
    public function channelPage()
    {
        return view('platform/channel.html');
    }

    //获取渠道列表数据
    public function getChannelList()
    {
        $params = [];
        return $this->srv->getChannelList($params);
    }

    //获取渠道选项
    public function getChannelOption()
    {
        $params = getAll();
        return $this->srv->getChannelOption($params);
    }
    
    //渠道添加页
    public function addChannelPage()
    {
        return view('platform/addChannel.html');
    }

    //获取渠道信息
    public function getChannelInfo()
    {
        $id = get('cid');
        return $this->srv->getChannelInfo($id);
    }

    //渠道添加操作
    public function addChannelAction()
    {
        $data = postAll(true);
        return $this->srv->addChannelAction($data);
    }

    //----------
    //联运包
    public function gamePartnerPkgPage()
    {
        return view('platform/gamePartnerPkg.html');
    }

    //获取联运表列表数据
    public function getGamePartnerPkgList()
    {
        $params = getAll();
        return $this->srv->getGamePartnerPkgList($params);
    }

    //联运包添加页
    public function addGamePartnerPkgPage()
    {
        return view('platform/addGamePartnerPkg.html');
    }

    //获取联运包信息
    public function getGamePartnerPkgInfo()
    {
        $id = get('id');
        return $this->srv->getGamePartnerPkgInfo($id);
    }

    //联运包添加操作
    public function addGamePartnerPkgAction()
    {
        $data = postAll();
        return $this->srv->addGamePartnerPkgAction($data);
    }

    //获取联运商应用配置参数模板
    public function getPartnerConfigTplInfo()
    {
        $id = get('pid');
        return $this->srv->getPartnerConfigTplInfo($id);
    }

    //-----------

    //联运渠道
    public function partnerPage()
    {
        return view('platform/partner.html');
    }

    //获取联运商列表数据
    public function getPartnerList()
    {
        $params = getAll();
        return $this->srv->getPartnerList($params);
    }

    //获取联运商选项
    public function getPartnerOption()
    {
        $params = getAll();
        return $this->srv->getPartnerOption($params);
    }
    
    //联运商添加页
    public function addPartnerPage()
    {
        return view('platform/addPartner.html');
    }

    //获取联运商信息
    public function getPartnerInfo()
    {
        $id = get('pid');
        return $this->srv->getPartnerInfo($id);
    }

    //联运商添加操作
    public function addPartnerAction()
    {
        $data = postAll(true);
        return $this->srv->addPartnerAction($data);
    }

    //---------------

    //渠道包管理
    public function pkgPage()
    {
        return view('platform/pkg.html');
    }

    //渠道包添加页
    public function addPkgPage()
    {
        return view('platform/addPkg.html');
    }

    //获取渠道包列表数据
    public function getPkgList()
    {
        $params = getAll();
        return $this->srv->getPkgList($params);
    }

    //获取渠道包列表option
    public function getPkgOption()
    {
        $params = getAll();
        return $this->srv->getPkgOption($params);
    }

    //获取可使用的渠道包数量
    public function getCanUsedPkgNumber()
    {
        return $this->srv->getCanUsedPkgNumber(getAll());
    }

    //获取渠道包信息
    public function getPkgInfo()
    {
        $id = get('id');
        return $this->srv->getPkgInfo($id);
    }

    //渠道包添加操作
    public function addPkgAction()
    {
        $data = postAll();
        return $this->srv->addPkgAction($data);
    }

    //分包任务管理页面
    public function splitPkgTaskPage()
    {
        return view('platform/splitPkgTask.html');
    }

    //获取分包任务列表数据
    public function getSplitPkgTaskList()
    {
        $params = getAll();
        return $this->srv->getSplitPkgTaskList($params);
    }

    //获取分包进度信息
    public function splitPkgTaskProgress()
    {
        $params = getAll();
        return $this->srv->splitPkgTaskProgress($params);
    }


    //---------
    //金流渠道页
    public function paywayPage()
    {
        return view('platform/payway.html');
    }

    //获取金流渠道列表数据
    public function getPaywayList()
    {
        $params = [];
        return $this->srv->getPaywayList($params);
    }

    //获取金流渠道选项
    public function getPaywayOption()
    {
        $params = [];
        return $this->srv->getPaywayOption($params);
    }
    
    //金流渠道添加页
    public function addPaywayPage()
    {
        return view('platform/addPayway.html',['paytype'=>Game::$paytype]);
    }

    //获取金流渠道信息
    public function getPaywayInfo()
    {
        $id = get('id');
        return $this->srv->getPaywayInfo($id);
    }

    //金流渠道添加操作
    public function addPaywayAction()
    {
        $data = postAll(true);
        return $this->srv->addPaywayAction($data);
    }

    
}