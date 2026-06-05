<?php
namespace App\admin\service;

use App\admin\model\ModPlatform;
use App\common\Game;
use App\common\Table;
use YXLib\foundation\Debug;
use YXLib\foundation\file\LibFile;

class SrvPlatform{

    private $mod;
    public function __construct(){
        $this->mod = new ModPlatform();
    }

    public function getGameDivideList($data)
    {
        $result = [];
        $mglist = $this->mod->getMgameList($data);
        $info = $this->mod->getGameDivideList($data);

        $divide = [];
        foreach ($info as $key => $value) {
            $divide[$value['mgid']] = $value['divide'];
        }
        foreach($mglist['list'] as $v){
            $result[] = [
                'mgid'=>$v['mgid'],
                'mgname'=>$v['mgname'],
                'date'=>$data['date'],
                'divide'=>$divide[$v['mgid']]?$divide[$v['mgid']]:'',
            ];
        }
        return success(['list'=>$result,'date'=>substr($data['date'],0,7)]);
    }

    public function updateGameDivideAction($data)
    {
        if(!$data['mgid'] || !$data['date'] ||!$data['divide']){
            return fail('缺少参数');
        }
        $affectRows = $this->mod->updateGameDivideAction($data);
        if($affectRows<=0) return fail('操作失败');
        return success([],'操作成功');
    }


    //主游戏
    public function getMgameList($data)
    {
        $info = $this->mod->getMgameList($data);
        return success($info);
    }

    public function getMgameOption($params = [])
    {
        $info = $this->mod->getMgameList($params);
        $data = [];
        if($params['is_map']){
            foreach($info['list'] as $v){
                $data[$v['mgid']] = [
                    'mgid' => $v['mgid'],
                    'mgname' => $v['mgname'],
                ];
            }
        }else{
            foreach($info['list'] as $v){
                $data[] = [
                    'mgid' => $v['mgid'],
                    'mgname' => $v['mgname'],
                ];
            }
        }
       
        return success($data);
    }

    //主游戏
    public function getMgameInfo($id)
    {
        if(!$id) return fail('操作失败');
        $info = $this->mod->getMgameInfo($id);
        return success($info,'success');
    }

    //主游戏
    public function addMgameAction($params)
    {
        $data = [
            'mgname'=>$params['mgname'],
            'contacts'=>$params['contacts'],
            'contacts_type'=>$params['contacts_type'],
            'sort'=>(int)$params['sort'],
        ];

        if(!$data['mgname']){
            return fail('请填写主游戏名称');
        }
        if(!$params['mgid']){
            $data['sdkkey'] = md5(mt_rand().time().mt_rand().time().mt_rand());
            $data['serverkey'] = md5(mt_rand().time().mt_rand().time().mt_rand());
            $id = $this->mod->addMgameAction($data);
        }else{
            $id = $params['mgid'];
            $this->mod->updateMgameAction($id,$data);
        }
        //缓存文件
        $info = $this->mod->getMGameInfo($id);
        $ret = sy_data_write(Table::$pf_game_main, $id, $info);
        if(!$ret){
            return fail('缓存文件失败');
        }
        return success([],'操作成功');
    }

    //主游戏
    public function deleteMgameAction($id)
    {
        if(!$id) return fail('缺少参数');
        $ret = $this->mod->deleteMgameAction($id);
        if(!$ret){
            return fail('操作失败');
        }
        return success([],'操作成功');
    }

    //子游戏
    public function getGameList($data)
    {
        $info = $this->mod->getGameList($data);
        if($info['list']){
            foreach($info['list'] as $k=> $v){
                // $info['list'][$k]['csdk_type'] = Game::$csdk_type[$v['csdk']];
            }
        }
        return success($info);
    }

    public function getGameOption($params = [])
    {
        $info = $this->mod->getGameList($params);
        $data = [];
        
        foreach($info['list'] as $v){
            if($v['apptype'] == 1){
                $tips = 'Android';
            }elseif($v['apptype'] == 2){
                $tips = 'iOS';
            }elseif($v['apptype'] == 3){
                $tips = '小程序';
            }
            if($params['is_map']){
                $data[$v['gid']] = [
                    'gid' => $v['gid'],
                    'gname' => $v['gname'],
                    'tips' => $tips,
                    'apptype' => $v['apptype'],
                    'appid'=>$v['appid']
                ];
            }else{
                $data[] = [
                    'gid' => $v['gid'],
                    'gname' => $v['gname'],
                    'tips' => $tips,
                    'apptype' => $v['apptype'],
                    'appid'=>$v['appid']
                ];
            }
           
        }
        return success($data);
    }
    

    public function getGameInfo($gid)
    {
        if(!$gid) return fail('缺少参数');
        $info = $this->mod->getGameInfo($gid);
        return success($info);
    }

    public function addGameAction($data)
    {
        if(!$data['gname'] || !$data['dpgn'] || !$data['screen']) {
            return fail('请填写完整数据！');
        }
        if(!is_numeric($data['sort'])) {
            return fail('排序要填写纯数字');
        }
        if(!$data['icon']) {
            return fail('请选择icon图标');
        }
        if($data['gid']) {
            $update = [
                'gname' => $data['gname'],
                'dpgn' => $data['dpgn'],
                'icon' => $data['icon'],
                'h5link' => $data['h5link'],
                'coinlink' => $data['coinlink'],
                'sort' => $data['sort'],
                'appid' => $data['appid'],
                'status' => (int)$data['status'],
            ];
            $id = $data['gid'];
            $this->mod->updateGameAction($update,$id);
        } else {
            $mgInfo = $this->mod->getMgameInfo($data['mgid']);
            if(!$mgInfo){
                return fail('请选择正确的主游戏');
            }
            $insert = [
                'mgid' => $data['mgid'],
                'apptype' => $data['apptype'],
                'screen' => $data['screen'],
                'gname' => $data['gname'],
                'slug' => $data['slug'],
                'dpgn' => $data['dpgn'],
                'icon' => $data['icon'],
                'h5link' => $data['h5link'],
                'coinlink' => $data['coinlink'],
                'sort' => $data['sort'],
                'sdkkey'=>$mgInfo['sdkkey'],
                'serverkey'=>$mgInfo['serverkey'],
                'version'=>1,
                'atime'=>time(),
            ];
            $id = $this->mod->addGameAction($insert);
            //以下是默认创建
            // if($result){
            //     $gid = $result;
            //     //创建一条推广链 - android (安卓创建推广链时会自动分一个package包)
            //     $androidMonitorInfo = [
            //         'name'=>'母包链Android',
            //         'gid'=>$gid,
            //         'cid' =>Game::$androidCid,
            //         'dtype'=>Game::$dtypes['android'],
            //         'user_id'=>App::$defaultUserId,
            //         'group_id'=>App::$defaultGroupId,
            //         'company_id'=>App::$defaultCompanyId,
            //     ];
            //     $srvAd = new SrvAdManage;
            //     $response = $srvAd->addMonitorAction($androidMonitorInfo);
            //     Debug::log($response->out);
            //     if(!$response->out['state']){
            //         return fail('创建失败！默认android推广链创建失败，请联系技术处理！');
            //     }
            //     //创建一条packge - iOS （ios创建package包会自动创建一条推广链）
            //     $iOSPackgeInfo = [
            //         'gid'=>$gid,
            //         'cid' =>Game::$iOSCid,
            //         'dtype'=>App::$dtypes['ios'],
            //         'atime'=>time(),
            //     ];
            //     $response = $srvAd->addPackageAction($iOSPackgeInfo);
            //     Debug::log($response->out);
            //     if(!$response->out['state']){
            //         return fail('创建失败！默认iOS Package创建失败，请联系技术处理！');
            //     }
               
            // }
        }
        if(!$id){
            return fail('添加失败');
        }
        //缓存文件
        $info = $this->mod->getGameInfo($id);
        $ret = sy_data_write(Table::$pf_game, $id, $info);
        if(!$ret){
            return fail('缓存文件失败');
        }
        return success(true,'提交成功');
    }

    public function addGamePaywayAction($data)
    {
        if(!$data['gid']) return fail('缺少游戏ID');
        $config = json_decode($data['config'],true);
        $conf = [];
        foreach ($config as $value) {
            if($value['rate']>0){
                $conf[$value['paytype']][$value['paid']] = $value['rate'];
            }
        }
        foreach($conf as $paytype => $list){
            $check = array_sum($list);
            if($check != 100) {
                return fail("支付方式【".Game::$paytype[$paytype]."】配置有误，必须所有渠道之和为100");
            }
        }
        $ret = sy_data_write('pf_game_payway', $data['gid'], $conf);
        if(!$ret){
            return fail('缓存文件失败');
        }
        return success(true,'提交成功');
    }

    public function getGamePaywayList($gid){
        if(!$gid) return fail('缺少游戏ID');
        $config = sy_data_read('pf_game_payway',$gid);
        $info = $this->mod->getPaywayList();
        $list = [];
        foreach($info['list'] as $k=>$v){
            $rate = (int)$config[$v['paytype']][$v['paid']];
            $list[] = [
                'paid'=>$v['paid'],
                'paytype'=>$v['paytype'],
                'paytype_txt'=>Game::$paytype[$v['paytype']],
                'pname'=>$v['pname'],
                'rate'=>$rate,
            ];
        }
        return success(['list'=>$list]);
    }
    
    public function getGameSdkVersionList()
    {
        $list = $this->mod->getGameSdkVersionList();
        return success($list);
    }

    public function getGameSdkVersionOption()
    {
        $data = [];
        $list = $this->mod->getGameSdkVersionList();
        foreach($list['list'] as $v){
            $data[] = [
                'id'=>$v['id'],
                'sdk_version'=>$v['sdk_version']
            ];
        }
        return success($data);
    }

    public function addGameSdkVersionAction($data)
    {
        if($data['id']){
            $data = [
                'remark'=>$data['remark'],
            ];
        }else{
            $data = [
                'remark'=>$data['remark'],
                'sdk_version'=>$data['sdk_version'],
                'atime'=>time()
            ];
        }
    }

    public function getGameSdkVersionInfo($id)
    {
        if(!$id) return fail('缺少参数');
        $info = $this->mod->getGameSdkVersionInfo($id);
        return success($info);
    }

    public function uploadApk($gid,$cid,$version,$total,$now){
        ini_set('memory_limit', '512M');
        $modelDir = config('app.model_dir');
        if(!$modelDir) return fail('请配置母包上传目录');
        if(!$gid || !$cid) return fail('缺少GID或CID');
        $apkfile = $modelDir .Game::modelApkName($gid,$cid,$version,true);
        $apkdir = $modelDir .Game::modelApkName($gid,$cid,$version,false);

        if(!$total){
            $file = 'model.apk';
        }else{
            $file = $now . '.tmpapk';
        }
        $result = LibFile::upload('apk',$apkdir,'',20480,array('.apk'),$file);
        if($result['state']){
            if($total > 1 && $total == ($now+1)){
                $stream = '';
                file_put_contents( $apkfile, $stream);
                for($i=0;$i<$total;$i++){
                    $stream = file_get_contents($apkdir . $i.'.tmpapk');
                    file_put_contents($apkfile, $stream, FILE_APPEND);
                    unlink($apkdir.$i.'.tmpapk');
                }
                $parseResult = $this->parseApk($apkdir,$gid,$cid,$version);
                // $re = $this->pushApk($apkdir,$apkfile);
                // if(!$re) return fail('上传失败:推送失败');
                return success([],'上传成功');
            }
            if(!$total){
                $parseResult = $this->parseApk($apkdir,$gid,$cid,$version);
                // $re = $this->pushApk($apkdir,$apkfile);
                // if(!$re) return fail('上传失败:推送失败');
                return success([],'上传成功');
            }
        }
        return fail($result['msg']);
    }

    public function parseApk($apkdir,$gid,$cid,$version)
    {
        $mpkgInfo = $this->mod->getGameChannelMPkgInfoOfMaxVersion($gid,$cid);
        if(!$mpkgInfo) return false;
        $parseData = [];
        exec("cd $apkdir && rm -rf model && /usr/local/bin/apktool d model.apk",$out,$result_code);
        Debug::log($out);
        $result = end($out);
        if($result == 'I: Copying original files...' && is_dir($apkdir . 'model/')){
            $parseData['size'] = filesize($apkdir.'model.apk');
            $xml = file_get_contents($apkdir . 'model/AndroidManifest.xml');
            if(preg_match("/package=\"(.*?)\"/",$xml,$match)){
                $parseData['dpgn'] = $match[1];
            }
            if(preg_match("/android:name=\"SQ_CHANNEL_ID\" android:value=\"(.*?)\"/",$xml,$match)){
                $parseData['info']['SQ_CHANNEL_ID'] = $match[1];
            }
            if(preg_match("/android:name=\"SQ_PACKAGE_NAME\" android:value=\"(.*?)\"/",$xml,$match)){
                $parseData['info']['SQ_PACKAGE_NAME'] = $match[1];
            }
            if(preg_match("/android:name=\"SQ_PACKAGE_VERSION\" android:value=\"(.*?)\"/",$xml,$match)){
                $parseData['info']['SQ_PACKAGE_VERSION'] = $match[1];
            }
            if(preg_match("/android:name=\"SQ_SDK_VERSION\" android:value=\"(.*?)\"/",$xml,$match)){
                $parseData['info']['SQ_SDK_VERSION'] = $match[1];
            }
            if(preg_match("/android:name=\"SQ_GAME_ID\" android:value=\"(.*?)\"/",$xml,$match)){
                $parseData['info']['SQ_GAME_ID'] = $match[1];
            }
            if(preg_match("/android:name=\"SQ_GAME_KEY\" android:value=\"(.*?)\"/",$xml,$match)){
                $parseData['info']['SQ_GAME_KEY'] = $match[1];
            }
            if(preg_match("/android:name=\"SQ_TT_APP_NAME\" android:value=\"(.*?)\"/",$xml,$match)){
                $parseData['info']['SQ_TT_APP_NAME'] = $match[1];
            }
            if(preg_match("/android:name=\"SQ_TT_APP_ID\" android:value=\"(.*?)\"/",$xml,$match)){
                $parseData['info']['SQ_TT_APP_ID'] = $match[1];
            }
            if(preg_match("/android:name=\"SQ_TT_APP_CHANNEL\" android:value=\"(.*?)\"/",$xml,$match)){
                $parseData['info']['SQ_TT_APP_CHANNEL'] = $match[1];
            }
            //解析app名称
            $xml = file_get_contents($apkdir . 'model/res/values/strings.xml');
            if(preg_match("/<string name=\"app_name\">(.*?)<\/string>/",$xml,$match)){
                $parseData['pname'] = $match[1];
            }
            $icon_dir = array(
                'drawable-xxhdpi'
            );

            $icon_name = "ic_launcher.png";
            foreach ($icon_dir as $idr) {
                $iconFile = $apkdir . "model/res/{$idr}/{$icon_name}";
                if(file_exists($iconFile)){
                    $_dm = date("ym");
                    $iconFileName = date('dHis') . mt_rand(1000, 9999) . '_' . substr(md5(mt_rand() . time()),4,10) . '.png';
                    $mpkgIconDir = APP_ROOT . '/public/upload/'.$_dm.'/';
                    if(!is_dir($mpkgIconDir)){
                        mkdir($mpkgIconDir,0755,true);
                    }
                    Debug::log("cd {$apkdir} && \\cp -r -f model/res/{$idr}/{$icon_name} {$mpkgIconDir}/{$iconFileName}");
                    exec("cd {$apkdir} && \\cp -r -f model/res/{$idr}/{$icon_name} {$mpkgIconDir}/{$iconFileName}",$output,$result_code);
                    Debug::log($output);
                    $parseData['icon'] = '/upload/'.$_dm.'/'.$iconFileName;
                    break;
                }
            }
        }

        if(!$parseData){
            $update = [
                'status'=>1,//解析失败
            ];
            $this->mod->updateGameChannelMPkgAction($update,$mpkgInfo['id']);
            return false;
        }

        $update = [
            'status'=>2,//解析失败
            'size'=>$parseData['size'],
            'config'=> json_encode($parseData,JSON_UNESCAPED_UNICODE),
        ];
        
        $this->mod->updateGameChannelMPkgAction($update,$mpkgInfo['id']);

        // exec("cd $apkdir && rm -rf model");
        return $parseData;
    }

    /**
     * 推送apk到打包服务器
     * @param $apk
     */
    public function pushApk($apkdir,$apkFile){
        Debug::log("推送母包OSS目录");
        $modelOssDir = config('app.model_oss_dir');
        Debug::log("\cp -rf {$apkdir} {$modelOssDir}");
        exec("\cp -rf {$apkdir} {$modelOssDir}",$out,$code);
        if($code !== 0){
            return false;
        }
        unlink($apkFile);
        return true;
    }

    public function uploadGameIcon($name,$size = 2048,$allow = []){
        if(!$allow){
            $allow = array(
                '.jpg','.png','.jpeg','.psd','.gif'
            );
        }
        
        $data = [];
        $uploadPath = APP_ROOT."/public/upload/";
        $result = LibFile::upload($name,$uploadPath,'',$size,$allow);
        if($result['state']){
            $data = array(
                'url' => '/upload/'.$result['url'],
                //'path'=> str_replace(CDN_UPLOAD_URL,'',$result['url']),
                'path' => $result['url'],
                'width' => $result['width'],
                'height' => $result['height'],
                'ext' => $result['ext'],
                'size' => $result['size']
            );
            return success($data,'上传成功');
        }

        return fail($result['msg']);
    }

    public function getChannelList($data){
        $info = $this->mod->getChannelList($data);
        return success($info);
    }


    public function getChannelOption($params = [])
    {
        $info = $this->mod->getChannelList($params);
        $data = [];
        if($params['is_map']){
            foreach($info['list'] as $v){
                $data[$v['cid']] = [
                    'cid' => $v['cid'],
                    'cname' => $v['cname'],
                    'type' => $v['type'],
                    'pid' => $v['pid']
                ];
            }
        }else{
            foreach($info['list'] as $v){
                $data[] = [
                    'cid' => $v['cid'],
                    'cname' => $v['cname'],
                    'type' => $v['type'],
                    'pid' => $v['pid']
                ];
            }
        }
        
        return success($data);
    }

    public function getChannelInfo($id)
    {
        if(!$id) return fail('操作失败');
        $info = $this->mod->getChannelInfo($id);
        if($info['config_dev']){
            $info['config_dev'] = json_decode($info['config_dev'],true);
            $config = [];
            foreach($info['config_dev'] as $k=>$v){
                $config[] = "{$k}=>{$v}";
            }
            $info['config_dev'] = implode("\r\n",$config);
        }
        if($info['config_tpl']){
            $info['config_tpl'] = json_decode($info['config_tpl'],true);
            $config = [];
            foreach($info['config_tpl'] as $k=>$v){
                $config[] = "{$k}=>{$v}";
            }
            $info['config_tpl'] = implode("\r\n",$config);
        }

        return success($info,'success');
    }

   
    public function addChannelAction($params)
    {
        if(!$params['cname']) return fail('请输入渠道名称');
        if(!$params['type']) return fail('请选择渠道类型');
        if(!$params['pid']) return fail('请选择联运商');
        $data = [
            'cname'=>$params['cname'],
            'sort'=>$params['sort'],
            'aquery'=>$params['aquery'],
            'iquery'=>$params['iquery'],
        ];
        if($params['config_tpl']){
            if(strpos($params['config_tpl'],"\r\n")!==false){
                $configTpl = explode("\r\n",$params['config_tpl']);
            }else{
                $configTpl = explode("\n",$params['config_tpl']);
            }
            $configTplArr = [];
            foreach ($configTpl as $key => $value) {
                list($k,$v) = explode('=>',$value);
                $configTplArr[$k] = $v;
            }
            $data['config_tpl'] = json_encode($configTplArr,JSON_UNESCAPED_UNICODE);
        }
        if($params['config_dev']){
            if(strpos($params['config_dev'],"\r\n")!==false){
                $configDev = explode("\r\n",$params['config_dev']);
            }else{
                $configDev = explode("\n",$params['config_dev']);
            }
            $configDevArr = [];
            foreach ($configDev as $key => $value) {
                list($k,$v) = explode('=>',$value);
                $configDevArr[$k] = $v;
            }
            $data['config_dev'] = json_encode($configDevArr,JSON_UNESCAPED_UNICODE);
        }
        if($params['cid']){
            $id = $params['cid'];
            $this->mod->updateChannelAction($data,$id);
        }else{
            $data['pid'] = $params['pid']?(int)$params['pid']:1;
            $data['type'] = $params['type']?(int)$params['type']:1;
            $data['slug'] = $params['slug'];
            $data['atime'] = time();
            $id = $this->mod->addChannelAction($data);
        }
        //文件缓存操作
        $info = $this->mod->getChannelInfo($id);
        $info['config_tpl'] = json_decode($info['config_tpl'],true);
        $info['config_dev'] = json_decode($info['config_dev'],true);
        $cache = sy_data_write(Table::$pf_channel,$id,$info);
        if(!$cache){
            return fail('缓存文件失败1');
        }
        
        $list = $this->mod->getChannelList([]);
        $map = [];
        foreach($list['list'] as $v){
            $map[$v['slug']] = $v['cid'];
        }
        $cache = sy_data_write(Table::$pf_channel,'slug',$map);
        if(!$cache){
            return fail('缓存文件失败2');
        }
        
        return success([],'操作成功');
    }

    //--------
    public function getGamePartnerPkgList($data){
        $info = $this->mod->getGamePartnerPkgList($data);
        return success($info);
    }

   
    public function getGamePartnerPkgInfo($id)
    {
        if(!$id) return fail('操作失败');
        $info = $this->mod->getGamePartnerPkgInfo($id);
        $config = $info['config'];
        $config = json_decode($config,true);
        foreach($config as $k=>$v){
            $info[$k] = $v;
        }
        unset($info['config']);
        return success($info,'success');
    }

   
    public function addGamePartnerPkgAction($params)
    {
        if(!$params['pid'] || !$params['gid']){
            return fail('请选择游戏和渠道');
        }
        $data = [
            'pid'=>$params['pid'],
            'gid'=>$params['gid'],
        ];
        //获取联运商应用配置模板
        $config = [];
        $channelInfo = sy_data_read(Table::$pf_partner,$data['pid']);
        $configTpl = $channelInfo['config_tpl'];
        if($configTpl){
            foreach ($configTpl as $key => $name) {
                $config[$key] = $params[$key];
            }
        }
        $data['config'] = json_encode($config);
        if($params['id']){
            $id = $params['id'];
            unset($data['gid'],$data['pid']);
            $this->mod->updateGamePartnerPkgAction($data,$params['id']);
        }else{
            $data['pkg'] = $data['pid'].'_'.$data['gid'].'_1000_1';
            $data['atime'] = time();
            $id = $this->mod->addGamePartnerPkgAction($data);
            if(!$id){
                return fail('添加失败，请检查是否已存在！');
            }
        }
        if($id){
            //文件缓存操作
            $info = $this->mod->getGamePartnerPkgInfo($id);
            $info['config'] = json_decode($info['config'],true);
            $cache = sy_data_write(Table::$pf_game_partner_pkg,$info['pid'].'_'.$info['gid'],$info);
            if(!$cache){
                return fail('缓存文件失败1');
            }
        }

        return success([],'操作成功');
    }

    public function getPartnerConfigTplInfo($id)
    {
        $tpl = [];
        $mod = new ModPlatform();
        $info = $mod->getPartnerInfo($id);
        if($info['config_tpl']){
            $arr = json_decode($info['config_tpl'],true);
            foreach($arr as $k=>$v){
                $tpl[] = [
                    'key'=>$k,
                    'value'=>$v,
                ];
            }
        }
        return success($tpl,'success');
    }

    //--------
    public function getPartnerList($data){
        $info = $this->mod->getPartnerList($data);
        return success($info);
    }


    public function getPartnerOption($params = [])
    {
        $info = $this->mod->getPartnerList($params);
        $data = [];
        foreach($info['list'] as $v){
            if($params['is_map']){
                $data[$v['pid']] = [
                    'pid' => $v['pid'],
                    'pname' => $v['pname'],
                ];
            }else{
                $data[] = [
                    'pid' => $v['pid'],
                    'pname' => $v['pname'],
                ];
            }
            
        }
        return success($data);
    }

    public function getPartnerInfo($id)
    {
        if(!$id) return fail('操作失败');
        $info = $this->mod->getPartnerInfo($id);
        if($info['config_tpl']){
            $info['config_tpl'] = json_decode($info['config_tpl'],true);
            $config = [];
            foreach($info['config_tpl'] as $k=>$v){
                $config[] = "{$k}=>{$v}";
            }
            $info['config_tpl'] = implode("\r\n",$config);
        }
        return success($info,'success');
    }

   
    public function addPartnerAction($params)
    {
        $data = [
            'pname'=>$params['pname'],
            'sort'=>$params['sort'],
        ];
        if($params['config_tpl']){
            if(strpos($params['config_tpl'],"\r\n")!==false){
                $configTpl = explode("\r\n",$params['config_tpl']);
            }else{
                $configTpl = explode("\n",$params['config_tpl']);
            }
            $configTplArr = [];
            foreach ($configTpl as $key => $value) {
                list($k,$v) = explode('=>',$value);
                $configTplArr[$k] = $v;
            }
            $data['config_tpl'] = json_encode($configTplArr,JSON_UNESCAPED_UNICODE);
        }
        if($params['pid']){
            $id = $params['pid'];
            $this->mod->updatePartnerAction($data,$id);
        }else{
            $data['slug'] = $params['slug'];
            $data['atime'] = time();
            $id = $this->mod->addPartnerAction($data);
        }

        if($id){
            //文件缓存操作
            $info = $this->mod->getPartnerInfo($id);
            $info['config_tpl'] = json_decode($info['config_tpl'],true);
            $cache = sy_data_write(Table::$pf_partner,$id,$info);
            if(!$cache){
                return fail('缓存文件失败1');
            }
        }
        
        return success([],'操作成功');
    }
    //-------


    //--------
    public function getPkgList($data){
        $info = $this->mod->getPkgList($data);
        return success($info);
    }

    public function getPkgOption($data)
    {
        $info = $this->mod->getPkgList($data);
        $data = [];
        foreach($info['list'] as $v){
            $data[] = [
                'pkg' => $v['pkg'],
                'pkgname' => $v['pkg'].($v['specname']?"[".$v['specname']."]":''),
            ];
        }
        return success($data);
    }

    public function getCanUsedPkgNumber($data)
    {
        $info = $this->mod->getCanUsedPkgNumber($data);
        return success(['number'=>$info['number'],'list'=>$info['list']]);
    }

    public function getPkgInfo($id)
    {
        if(!$id) return fail('操作失败');
        $info = $this->mod->getPkgInfo($id);
        return success($info,'success');
    }

   
    public function addPkgAction($params)
    {
        if(!$params['gid']||!$params['cid']){
            return fail('请选择游戏和渠道');
        }
        $pid = 1;
        $data = [
            'pid'=>$pid,
            'gid'=>$params['gid'],
            'cid'=>$params['cid'],
            'specname'=>$params['specname'],
            'specicon'=>$params['specicon'],
        ];
        if($params['id']){
            $this->mod->updatePkgAction($data,$params['id']);
        }else{
            $gameInfo = $this->mod->getGameInfo($params['gid']);
            $num = (int)$params['num'];
            if(!$params['cid']){
                return fail('请选择渠道');
            }
            if($gameInfo['apptype'] == 1){//安卓
                $status = 0;//等待分包
                if($num<0 || $num>100){
                    return fail('渠道包批量值为1~100个');
                }
                $mpkgInfo = $this->mod->getAvailableMPkg(['gid'=>$params['gid'],'cid'=>$params['cid']]);
                if(!$mpkgInfo){
                    return fail('请先上传当前游戏母包');
                }
            }else{
                return fail('不支持此应用类型！');
            }

            $atime = time();
            $data = [
                'pid'=>$pid,
                'gid'=>$params['gid'],
                'cid'=>$params['cid'],
                'specname'=>$params['specname'],
                'specicon'=>$params['specicon'],
                'status'=> $status,
                'atime' => $atime,
                'pkgver'=>$mpkgInfo['version']
            ];

            $inserts = [];
            $taskInserts = [];
            $index = $this->mod->getGamePkgIndex($pid,$params['gid'],$params['cid']);
            for($i=0;$i<$num;$i++){
                ++$index;
                $data['pkg'] = $pid.'_'.$params['gid'].'_'.$params['cid'].'_'.$index;
                $inserts[] = $data;
                $taskInserts[] = [
                    'gid'=>$params['gid'],
                    'cid'=>$params['cid'],
                    'specname'=>$params['specname'],
                    'specicon'=>$params['specicon'],
                    'specicon'=>$params['specicon'],
                    'pkg'=>$data['pkg'],
                    'pkgver'=>$mpkgInfo['version'],
                    'atime'=>$atime,
                ];
            }
            $this->mod->addMultiPkgAction($inserts);
            $this->mod->addMultiSplitPkgTaskAction($taskInserts);
        }
        return success([],'操作成功');
    }
    //-------


    public function getGameChannelMPkgList($params)
    {
        if(!$params['gid'] || !$params['cid']){
            return success(['list'=>[]]);
        }
        $list = $this->mod->getGameChannelMPkgList($params);
        foreach($list['list'] as $k=>$v){
            $list['list'][$k]['size'] = bcdiv($v['size']/1024/1024,1,2) .'MB';
        }
        return success($list);
    }

    public function addGameChannelMPkgAction($params)
    {

        if($params['id']){
            //修改
            $info = $this->mod->getGameChannelMPkgInfo($params['id']);
            if(!$info){
                return fail('操作失败');
            }
            if($info['status'] == 2){
                return fail('当前版本已上传母包，请重新添加新版本');
            }
            $update = [
                'pname'=>$params['pname'],
                'dpgn'=>$params['dpgn'],
                'sdk_version'=>$params['sdk_version'],
                'icon'=>$params['icon'],
                'channel_appid'=>$params['channel_appid'],
            ];
            $this->mod->updateGameChannelMPkgAction($update,$params['id']);
        }else{
            //添加
            if(!$params['gid'] || !$params['cid']){
                return fail('请选择游戏和渠道');
            }
            $info = $this->mod->getGameChannelMPkgInfoOfMaxVersion($params['gid'],$params['cid']);
            if($info && $info['status'] != 2){
                return fail("版本号为{$info['version']}的母包还未完成，请先处理！");
            }
            $insert = [
                'gid'=>$params['gid'],
                'cid'=>$params['cid'],
                'version'=>$info['version']+1,
                'pname'=>$params['pname'],
                'dpgn'=>$params['dpgn'],
                'sdk_version'=>$params['sdk_version'],
                'icon'=>$params['icon'],
                'channel_appid'=>$params['channel_appid'],
                'atime'=>time(),
            ];
            $this->mod->addGameChannelMPkgAction($insert);
        }
        
        return success([],'success');
    }

    public function addGameChannelMPkgAction2($params)
    {
        if(!$params['gid'] || !$params['cid']){
            return fail('请选先择游戏和渠道');
        }
        $info = $this->mod->getGameChannelMPkgInfoOfMaxVersion($params['gid'],$params['cid']);
        if($info && $info['status']!=2){
            return fail('请上传版本号为'.$info['version']."母包");
        }
        $this->mod->addGameChannelMPkgAction($params);
        return success([],'success');
    }

    public function getGameChannelMPkgInfo($params)
    {
        if($params['id']){
            $info = $this->mod->getGameChannelMPkgInfo($params['id']);
            return success($info,'success');
        }
        if($params['gid'] && $params['cid']){
            $info = $this->mod->getGameChannelMPkgInfoOfMaxVersion($params['gid'],$params['cid']);
            return success($info,'success');
        }
        return fail('操作失败');
    }

    public function getGameChannelMPkgAnalysisInfo($id)
    {
        if(!$id){
            return fail('缺少参数');
        }

        $info = $this->mod->getGameChannelMPkgInfo($id);
        $json = json_decode($info['config'],true);
        $data = [
            'icon'=>$json['icon'],
            'config'=>$info['config'],
        ];
        return success($data,'success');
    }

    public function getAvailableMPkg($params)
    {
        if(!$params['gid'] ||!$params['cid']){
            return fail('请选择游戏和渠道');
        }
        $info = $this->mod->getAvailableMPkg($params);
        if($info){
            $json = json_decode($info['config'],true);
            $info['icon'] = $json['icon'];
            $info['size'] = bcdiv($info['size']/1024/1024,1,2) .'MB';
            return success($info,'success');
        }else{
            return fail('抱歉，请先上传当前游戏母包！');
        }
    }

    public function getSplitPkgTaskList($data){
        $info = $this->mod->getSplitPkgTaskList($data);
        return success($info);
    }

    public function splitPkgTaskProgress($data)
    {
        $info = $this->mod->splitPkgTaskProgress($data);
        $out = array(
            'ing' => 0,
            'success' => 0,
            'error' => 0,
            'wait' => 0,
            'total' => 0,
        );
        foreach($info as $i){
            switch($i['state']){
                case 1:
                    $key = 'ing';
                    break;
                case 2:
                    $key = 'success';
                    break;
                case 3:
                    $key = 'error';
                    break;
                case 0:
                    $key = 'wait';
            }
            $out[$key] = $i['total'];
            $out['total'] += $i['total'];
        }
        return success($out);
    }

    //-------------------

    public function getPaywayList($data){
        $info = $this->mod->getPaywayList($data);
        foreach($info['list'] as $k=>$v){
            $info['list'][$k]['paytype_txt'] = Game::$paytype[$v['paytype']];
        }
        return success($info);
    }


    public function getPaywayOption()
    {
        $params = [];
        $info = $this->mod->getPaywayList($params);
        $data = [];
        foreach($info['list'] as $v){
            $data[] = [
                'id' => $v['id'],
                'pname' => $v['pname'],
            ];
        }
        return success($data);
    }

    public function getPaywayInfo($id)
    {
        if(!$id) return fail('操作失败');
        $info = $this->mod->getPaywayInfo($id);
        $divideInfo = $this->mod->getPaywayDivideInfo($id);
        $info['divide'] = (int) $divideInfo['divide'];
        return success($info,'success');
    }

   
    public function addPaywayAction($params)
    {
        if(!$params['pname']) return fail('操作失败');
        $data = [
            'pname'=>$params['pname'],
            'company'=>$params['company'],
            'paytype'=>$params['paytype'],
            'appid'=>$params['appid'],
            'paykey'=>$params['paykey'],
            'partner'=>$params['partner'],
            'mypubkey'=>$params['mypubkey'],
            'myprikey'=>$params['myprikey'],
            'pubkey'=>$params['pubkey'],
            'domain'=>$params['domain'],
            'notify_url'=>$params['notify_url'],
            'return_url'=>$params['return_url'],
        ];
        
        if($params['paid']){
            $id = $params['paid'];
            $this->mod->updatePaywayAction($data,$id);
        }else{
            $data['atime'] = time();
            $id = $this->mod->addPaywayAction($data);
        }

        //渠道分成设置
        if($params['divide']){
            $divide = (int)$params['divide'];
            $this->mod->setPaywayDivideAction($id,$divide);
        }

        //文件缓存操作
        $info = $this->mod->getPaywayInfo($id);
        $cache = sy_data_write(Table::$pf_payway,$id,$info);
        if(!$cache){
            return fail('缓存文件失败');
        }
        
        return success([],'操作成功');
    }
}