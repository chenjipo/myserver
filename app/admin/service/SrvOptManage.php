<?php
namespace App\admin\service;

use App\admin\model\ModOptManage;
use App\common\Game;
use App\common\SdkHelper;
use App\common\Table;
use YXLib\foundation\Debug;

class SrvOptManage {

    public $mod;

    public function __construct()
    {
        $this->mod = new ModOptManage();
    }

    //获取订单列表数据
    public function getOrderList($params = [])
    {
        $data = $this->mod->getOrderList($params);
        $data['paytype'] = Game::$paytype;
        $data['paystatus'] = Game::$paystatus;
        return success($data,'success');
    }

    public function getOrderLog($ptno)
    {
        if(!$ptno){
            return fail('缺少参数');
        }

        $sdkHelper = new SdkHelper;
        $response = $sdkHelper->getOrderLog($ptno);

        $data = $response->getContent();
        return success(['content'=>$data['data']['content']],'success');
    }

    public function orderHandSend($ptno)
    {
        if(!$ptno){
            return fail('缺少参数');
        }

        $sdkHelper = new SdkHelper;
        $response = $sdkHelper->orderHandSend($ptno);

        return $response;
    }

    //--------------------------------
    public function getUserList($params = [])
    {
        $data = $this->mod->getUserList($params);
    
        return success($data,'success');
    }

    public function getUserInfo($uid)
    {
        $info = $this->mod->getUserInfo($uid);
        $deviceInfo = $this->mod->getUserDeviceInfo($info['device_id']);

        $key1 = [
            "uid"=> "UID",
            "uname"=> "用户名",
            "pid"=> "联运商",
            "gid"=> "游戏",
            "cid"=> "渠道",
            "linkid"=> "推广链ID",
            "pkg"=> "游戏包",
            "pkgver"=> "包版本",
            "sdk_version"=> "SDK版本",
            "active_time"=> "激活时间",
            "reg_time"=> "注册时间",
            "last_login_time"=> "最后登录时间",
            "reg_ip"=> "注册IP",
            "reg_city"=> "注册城市",
            "reg_province"=> "注册省份",
            "reg_isp"=> "运营商",
            "dtype"=> "设备类型",
            "device_id"=> "设备ID",
            "status"=> "状态",
            "phone"=> "绑定手机",
        ];
        $key2 = [
            'wpi'=>'分辨率宽',
            'hpi'=>'分辨率高',
            'model'=>'手机型号',
            'over'=>'手机系统版本',
            'brand'=>'手机品牌',
            'idfa'=>'idfa',
            'idfv'=>'idfv',
            'imei'=>'imei',
            'oaid'=>'安卓oaid',
            'androidid'=>'androidid',
            'mac'=>'mac',
            'client_id'=>'客户端生成唯一设备id',
            'ssid'=>'激活时wifi名',
            'network'=>'激活时网络类型4G5GWifi',
            'inner_ip'=>'激活时内网ip',
            'ip' =>'激活时ip',
            'province' =>'激活时省份',
            'city'=>'激活时城市',
            'isp'=>'激活时运营商',
        ];

        $list = array();
        $list[] = array('key'=>'【用户信息】：');
        foreach ($key1 as $key=>$v) {
            $value = $info[$key];
            if($key == 'reg_time'){
                $value = date('Y-m-d H:i:s',$value);
            }
            if($key == 'last_login_time'){
                $value = date('Y-m-d H:i:s',$value);
            }
            if($key == 'active_time'){
                $value = date('Y-m-d H:i:s',$value);
            }
            if($key == 'status'){
                $txt = $value==1?'解封':'封禁';
                $value = $value == 1 ? '<span style="color:red">已封禁</span>':'<span style="color:green">正常</span>';
                $value .= '&nbsp;<button class="layui-btn layui-btn-normal layui-btn-xs" lay-event="banuser">'.$txt.'</button>';
            }
            if($key == 'phone'){
                $value = $value ? $value . '&nbsp;<button class="layui-btn layui-btn-normal layui-btn-xs" lay-event="unbind_phone">解绑手机</button>':'-';
            }
            if($key == 'dtype'){
                $dtype = [1=>'安卓',2=>'iOS'];
                $value = $dtype[$value];
            }
           
            $list[] = array('key'=>$v,'value'=>$value,'uid'=>$uid);
        }

        $list[] = array('key'=>'');
        $list[] = array('key'=>'【设备信息】：');

        foreach ($key2 as $key => $v) {
            $value = $deviceInfo[$key];
            $list[] = array('key'=>$v,'value'=>$value,'uid'=>$uid);
        }

        return success($list,'success');
    }


    //----------------------------------------
    public function getUserRoleList($params = [])
    {
        $data = $this->mod->getUserRoleList($params);
    
        return success($data,'success');
    }

    public function unBindPhone($params = [])
    {
        $uid = $params['uid'];
        if(!$uid) {
            return fail('操作失败1');
        }
        $sdkHelper = new SdkHelper;
        $response = $sdkHelper->unBindPhone($uid);
        //此处记录操作日志 或者sdk处记录操作日志
        return $response;
    }

    public function banUser($params = [])
    {
        $uids = $params['uids'];
        if(!$uids) {
            if(!$params['uid']){
                return fail('操作失败1');
            }
            $uids = [$params['uid']];
        }
        $status = $params['status'];
        if(!in_array($status,[0,1])){
            return fail('操作失败2');
        }
        $sdkHelper = new SdkHelper;
        if($status == 1){
            $response = $sdkHelper->banUser($uids);
        }else{
            $response = $sdkHelper->unBanUser($uids);
        }
        //此处记录操作日志 或者sdk处记录操作日志
        return $response;
    }

    //--------------------
    public function getSdkWhiteNameList($params=[])
    {
        $data = $this->mod->getSdkWhiteNameList($params);
        return success($data);
    }

    public function addSdkWhiteNameAction($params)
    {
        if(strpos($params['names'],"\r\n")!==false){
            $names = explode("\r\n",$params['config_tpl']);
        }else{
            $names = explode("\n",$params['config_tpl']);
        }
        $names = array_filter($names);
        if(!$names) return fail('操作失败');
        $type = $params['type'];
        if(!$type) return fail('操作失败');
        $data = [];
        foreach ($names as $name) {
            $data[] = [
                'type'=>$type,
                'name'=>$name,
                'atime'=>time(),
            ];
        }
        
        $this->mod->addSdkWhiteNameAction($data);
        $ret = $this->syncSdkWhiteName();
        if(!$ret){
            return fail('同步配置失败，请联系技术处理！');
        }
        return success();
    }

    public function syncSdkWhiteName()
    {
        $config = [];
        $data = $this->mod->getSdkWhiteNameList();
        if($data['list']){
            foreach($data['list'] as $v){
                $config[$v['type']][] = $v['name'];
            }
        }
        return sy_data_write(Table::$pf_sdk_white,'all',$config);
    }

    public function deleteSdkWhiteNameAction($params)
    {
        $ids = explode(',',$params['ids']);
        if(!$ids) return fail('操作失败');
        $this->mod->deleteSdkWhiteNameAction($ids);
        $ret = $this->syncSdkWhiteName();
        if(!$ret){
            return fail('同步配置失败，请联系技术处理！');
        }
        return success();
    }

    //--------------------
    public function getSdkBlackNameList($params=[])
    {
        $data = $this->mod->getSdkBlackNameList($params);
        return success($data);
    }

    public function addSdkBlackNameAction($params)
    {
        if(strpos($params['names'],"\r\n")!==false){
            $names = explode("\r\n",$params['config_tpl']);
        }else{
            $names = explode("\n",$params['config_tpl']);
        }
        $names = array_filter($names);
        if(!$names) return fail('操作失败');
        $type = $params['type'];
        if(!$type) return fail('操作失败');
        $data = [];
        foreach ($names as $name) {
            $data[] = [
                'type'=>$type,
                'name'=>$name,
                'atime'=>time(),
            ];
        }
        
        $this->mod->addSdkBlackNameAction($data);
        $ret = $this->syncSdkBlackName();
        if(!$ret){
            return fail('同步配置失败，请联系技术处理！');
        }
        return success();
    }

    public function syncSdkBlackName()
    {
        $config = [];
        $data = $this->mod->getSdkBlackNameList();
        if($data['list']){
            foreach($data['list'] as $v){
                $config[$v['type']][] = $v['name'];
            }
        }
        return sy_data_write(Table::$pf_sdk_black,'all',$config);
    }

    public function deleteSdkBlackNameAction($params)
    {
        $ids = explode(',',$params['ids']);
        if(!$ids) return fail('操作失败');
        $this->mod->deleteSdkBlackNameAction($ids);
        $ret = $this->syncSdkBlackName();
        if(!$ret){
            return fail('同步配置失败，请联系技术处理！');
        }
        return success();
    }

    //--------------------
    public function getAnnouncementList($params=[])
    {
        $data = $this->mod->getAnnouncementList($params);
        return success($data);
    }

    public function getAnnouncementInfo($id)
    {
        $info = $this->mod->getAnnouncementInfo($id);
        if($info){
            $mapkey = explode(",",$info['mapkey']);
            $info['mapkey'] =  implode("\r\n",$mapkey);
        }
        return success($info);
    }

    public function addAnnouncementAction($params)
    {
        if(!$params['content']){
            return fail('请输入公告内容');
        }
        if(!$params['title']){
            return fail('请输入公告标题');
        }
        if(!$params['start_time']){
            return fail('请设置公告开启时间');
        }
        if(!$params['end_time']){
            return fail('请设置公告结束时间');
        }
        if(!$params['mapkey']){
            return fail('请设置开启对象');
        }

        if(strpos($params['mapkey'],"\r\n")!==false){
            $configTpl = explode("\r\n",$params['mapkey']);
        }else{
            $configTpl = explode("\n",$params['mapkey']);
        }
        $config = [];
        foreach ($configTpl as $key => $value) {
            //校验格式TODO
            $config[] = $value;
        }
        $mapkey = implode(',',$config);
       
        $data = [
            'title'=>$params['title'],
            'content'=>$params['content'],
            'close_second'=>(int)$params['close_second'],
            'start_time'=>strtotime($params['start_time']),
            'end_time'=>strtotime($params['end_time']),
            'mapkey'=>$mapkey,
        ];
        if($params['id']){
            $id = $params['id'];
            $this->mod->updateAnnouncementAction($data,$id);
        }else{
            $data['atime'] = time();
            $id = $this->mod->addAnnouncementAction($data);
        }

        if(!$id){
            return fail('添加失败');
        }

        $info = $this->mod->getAnnouncementInfo($id);
        if($info){
            $mapkey = explode(",",$info['mapkey']);
            $info['mapkey'] =  $mapkey;
        }
        $ret = sy_data_write(Table::$pf_announcement,$id,$info);
        if(!$ret){
            return fail('生成公告失败，请联系技术！');
        }

        return success();
    }

    public function syncAnnouncementAction()
    {
        $list = $this->mod->getAsyncAnnouncement();
        $config = [];
        if($list){
            foreach ($list as $key => $value) {
                if($value['mapkey']){
                    $mapkeys = explode(",",$value['mapkey']);
                    foreach($mapkeys as $key){
                        //按公告添加顺序倒序
                        if(isset($config[$key])){
                            return fail("发布失败，公告ID:{$config[$key]}与公告ID:{$value['id']}冲突，冲突对象：{$key}，请修改公告！");
                        }
                        $config[$key] = $value['id'];
                    }
                }
            }
        }
        $ret = sy_data_write(Table::$pf_announcement,'config',$config);
        if(!$ret){
            return fail('发布公告失败，请联系技术！');
        }

        return success();
    }

}