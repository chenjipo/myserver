<?php
namespace App\admin\service;

use App\admin\model\ModMk;
use App\admin\model\ModPlatform;
use App\common\Game;
use App\common\Table;
use YXLib\foundation\Debug;

class SrvMk{

    private $mod;
    public function __construct(){
        $this->mod = new ModMk();
    }

    public function getPitcherTeamList($data){
        $info = $this->mod->getPitcherTeamList($data);
        return success($info);
    }

    public function getPitcherTeamOption()
    {
        $params = [];
        $info = $this->mod->getPitcherTeamList($params);
        $data = [];
        foreach($info['list'] as $v){
            $data[] = [
                'teamid' => $v['teamid'],
                'teamname' => $v['teamname'],
            ];
        }
        return success($data);
    }

    public function getPitcherTeamInfo($id)
    {
        if(!$id) return fail('操作失败');
        $info = $this->mod->getPitcherTeamInfo($id);
        $info['agtids'] = $info['agtids'] ? explode(',',$info['agtids']):[];
        $info['cpnids'] = $info['cpnids'] ? explode(',',$info['cpnids']):[];
        return success($info,'success');
    }

   
    public function addPitcherTeamAction($params)
    {
        if(!$params['teamname']){
            return fail('请输入投放分组名称');
        }
        $agtids = $cpnids = '';
        if($params['agtids']){
            $agtids = array_column($params['agtids'],'value');
            sort($agtids);
            $agtids = implode(',',$agtids);

        }
        if($params['cpnids']){
            $cpnids = array_column($params['cpnids'],'value');
            sort($cpnids);
            $cpnids = implode(',',$cpnids);
        }
        $data = [
            'teamname'=>$params['teamname'],
            'sort'=>$params['sort'],
            'agtids'=>$agtids,
            'cpnids'=>$cpnids,
        ];
        if($params['teamid']){
            $id = $params['teamid'];
            $this->mod->updatePitcherTeamAction($data,$id);
        }else{
            $data['atime'] = time();
            $id = $this->mod->addPitcherTeamAction($data);
        }

        //缓存文件
        $info = $this->mod->getPitcherTeamInfo($id);
        $info['agtids'] = $info['agtids'] ? explode(',',$info['agtids']):[];
        $info['cpnids'] = $info['cpnids'] ? explode(',',$info['cpnids']):[];
        $ret = sy_data_write(Table::$mk_pitcher_team, $id, $info);
        if(!$ret){
            return fail('系统错误，请联系技术');
        }

        return success([],'操作成功');
    }

    public function deletePitcherTeamAction($id)
    {
        if(!$id){
            return fail('删除失败');
        }
        $ret = $this->mod->deletePitcherTeamAction($id);
        if($ret){
            return success([],'删除成功');
        }else{
            return fail('删除失败');
        }
    }


    /**
     * ---------------------------------------------
     */

    public function getPitcherList($data){
        $info = $this->mod->getPitcherList($data);
        return success($info);
    }

    public function getPitcherOption($params = [])
    {
        $info = $this->mod->getPitcherList($params);
        $data = [];
        $authorityPitids = SrvAuthBusiness::getMkAuthorityCache();
        if($params['is_map']){
            foreach($info['list'] as $v){
                if($authorityPitids && !in_array($v['pitid'],$authorityPitids)) continue;
                $data[$v['pitid']] = [
                    'pitid' => $v['pitid'],
                    'pitname' => $v['pitname'],
                ];
            }
        }else{
            foreach($info['list'] as $v){
                if($authorityPitids && !in_array($v['pitid'],$authorityPitids)) continue;
                $data[] = [
                    'pitid' => $v['pitid'],
                    'pitname' => $v['pitname'],
                ];
            }
        }
        
        return success($data);
    }

    public function getEnabledPitcherOption()
    {
        $data = [];
        $ret = $this->mod->getEnabledPitcherOption();
        foreach($ret as $v){
            $data[] = [
                'admin_id'=>$v['admin_id'],
                'nick'=>$v['nick'],
            ];
        }
        return success($data);
    }

    public function getPitcherInfo($id)
    {
        if(!$id) return fail('操作失败');
        $info = $this->mod->getPitcherInfo($id);
        return success($info,'success');
    }

   
    public function addPitcherAction($params)
    {
        if(!$params['teamid']){
            return fail('请选择分组');
        }
        if(!$params['pitname']){
            return fail('请输入投放人员名称');
        }
        $data = [
            'teamid'=>$params['teamid'],
            'pitname'=>$params['pitname'],
            'sort'=>$params['sort'],
        ];
        $ret = false;
        if($params['pitid']){
            $ret = $this->mod->updatePitcherAction($data,$params['pitid']);
        }
        if($params['_pitid']){
            $data['pitid'] = $params['_pitid'];
            $data['atime'] = time();
            $ret = $this->mod->addPitcherAction($data);
        }
        if(!$ret){
            return fail('操作失败~');
        }

        $list = $this->mod->getPitcherList([]);
        if($list['list']){
            //生成缓存文件
            $config = [];
            foreach($list['list'] as $v){
                $config[$v['pitid']] = $v['teamid'];
            }
            sy_data_write(Table::$mk_pitcher,'all',$config);
        }
        return success([],'操作成功');
    }

    public function deletePitcherAction($id)
    {
        if(!$id){
            return fail('删除失败');
        }
        $ret = $this->mod->deletePitcherAction($id);
        if($ret){
            return success([],'删除成功');
        }else{
            return fail('删除失败');
        }
    }


    /**
     * ---------------------------------------------
     */
    public function getCompanyList($data){
        $info = $this->mod->getCompanyList($data);
        return success($info);
    }

    public function getCompanyOption()
    {
        $params = [];
        $info = $this->mod->getCompanyList($params);
        $data = [];
        $authority = SrvAuthBusiness::getPitcherTeamAuthority();
        foreach($info['list'] as $v){
            if($authority['cpnids'] && !in_array($v['cpnid'],$authority['cpnids'])) continue;
            $data[] = [
                'cpnid' => $v['cpnid'],
                'cpnname' => $v['cpnname'],
            ];
        }
        return success($data);
    }

    public function getCompanyOptionNoAuth()
    {
        $params = [];
        $info = $this->mod->getCompanyOptionNoAuth($params);
        $data = [];
        foreach($info as $v){
            $data[] = [
                'cpnid' => $v['cpnid'],
                'cpnname' => $v['cpnname'],
            ];
        }
        return success($data);
    }

    public function getCompanyInfo($id)
    {
        if(!$id) return fail('操作失败');
        $info = $this->mod->getCompanyInfo($id);
        return success($info,'success');
    }

    public function addCompanyAction($params)
    {
        if(!$params['cpnname']){
            return fail('请输入资质主体名称');
        }
        $data = [
            'cpnname'=>$params['cpnname'],
            'jdomain'=>$params['jdomain'],
            'ldomain'=>$params['ldomain'],
            'sort'=>$params['sort'],
        ];
        if($params['cpnid']){
            $id = $params['cpnid'];
            $this->mod->updateCompanyAction($data,$id);
        }else{
            $data['atime'] = time();
            $id = $this->mod->addCompanyAction($data);
        }
        if(!$id){
            return fail('添加失败');
        }
        //缓存文件
        $info = $this->mod->getCompanyInfo($id);
        $ret = sy_data_write(Table::$mk_company, $id, $info);
        if(!$ret){
            return fail('缓存文件失败');
        }
        return success([],'操作成功');
    }

    public function deleteCompanyAction($id)
    {
        if(!$id){
            return fail('删除失败');
        }
        $ret = $this->mod->deleteCompanyAction($id);
        if($ret){
            return success([],'删除成功');
        }else{
            return fail('删除失败');
        }
    }

    /**
     * ---------------------------------------------
     */
    public function getAgentList($data){
        $info = $this->mod->getAgentList($data);
        return success($info);
    }

    public function getAgentOption()
    {
        $params = [];
        $info = $this->mod->getAgentList($params);
        $data = [];
        $authority = SrvAuthBusiness::getPitcherTeamAuthority();
        foreach($info['list'] as $v){
            if($authority['agtids'] &&!in_array($v['agtid'],$authority['agtids'])) continue;
            $data[] = [
                'agtid' => $v['agtid'],
                'agtname' => $v['agtname'],
            ];
        }
        return success($data);
    }

    public function getAgentOptionNoAuth()
    {
        $params = [];
        $info = $this->mod->getAgentOptionNoAuth($params);
        $data = [];
        foreach($info as $v){
            $data[] = [
                'agtid' => $v['agtid'],
                'agtname' => $v['agtname'],
            ];
        }
        return success($data);
    }

    public function getAgentInfo($id)
    {
        if(!$id) return fail('操作失败');
        $info = $this->mod->getAgentInfo($id);
        return success($info,'success');
    }

   
    public function addAgentAction($params)
    {
        if(!$params['agtname']){
            return fail('请输入资质主体名称');
        }
        $data = [
            'agtname'=>$params['agtname'],
            'sort'=>$params['sort'],
        ];
        if($params['agtid']){
            $this->mod->updateAgentAction($data,$params['agtid']);
        }else{
            $data['atime'] = time();
            $this->mod->addAgentAction($data);
        }
        return success([],'操作成功');
    }

    public function deleteAgentAction($id)
    {
        if(!$id){
            return fail('删除失败');
        }
        $ret = $this->mod->deleteAgentAction($id);
        if($ret){
            return success([],'删除成功');
        }else{
            return fail('删除失败');
        }
    }

    /**
     * ---------------------------------------------
     */
    public function getAgentRebateList($data){
        $info = $this->mod->getAgentRebateList($data);
        return success($info);
    }

    public function addAgentRebateAction($params)
    {
        $id = (int)$params['id'];
        if(!$params['cid']||!$params['agtid']){
            return fail('缺少参数');
        }
        $save = [
            'cid'=>(int)$params['cid'],
            'agtid'=>(int)$params['agtid'],
            'rebate'=>trim($params['rebate']),
        ];
        $ret = $this->mod->addAgentRebateAction($save,$id);
        if($ret){
            $list = $this->mod->getAgentRebateList([]);
            if($list['list']){
                //生成缓存文件
                $config = [];
                foreach($list['list'] as $v){
                    $config[$v['agtid'].'_'.$v['cid']] = $v['rebate'];
                }
                sy_data_write(Table::$mk_agent_rebate,'all',$config);
            }
            return success([],'操作成功');
        }else{
            return fail('操作失败');
        }
    }

    public function delAgentRebateAction($id)
    {
        if(!$id){
            return fail('删除失败');
        }
        $ret = $this->mod->delAgentRebateAction($id);
        if($ret){
            return success([],'删除成功');
        }else{
            return fail('删除失败');
        }
    }


    /**
     * ---------------------------------------------
     */
    public function getAdAccountList($data){
        $result = $this->mod->getAdAccountList($data);
        $list = [];
        foreach($result['list'] as $k=>$v){
            $authUrl = '';
            $channelInfo = sy_data_read(Table::$pf_channel,$v['cid'],'config_dev');
            if($channelInfo['config_dev']['auth_url']){
                $authUrl = $channelInfo['config_dev']['auth_url'];
            }
            $list[] = [
                'accid'=>$v['accid'],
                'cid'=>$v['cid'],
                'agtid'=>$v['agtid'],
                'accname'=>$v['accname'],
                'alias'=>$v['alias'],
                'advertiserid'=>$v['advertiserid'],
                'pitid'=>$v['pitid'],
                'cpnid'=>$v['cpnid'],
                'acost'=>$v['acost'],
                'acosttime'=>$v['acosttime'],
                'acostfails'=>$v['acostfails'],
                'balance'=>$v['balance'],
                'balancetime'=>$v['balancetime'],
                'status'=>$v['status'],
                'auth_time'=>$v['auth_time'],
                'atime'=>$v['atime'],
                'mtime'=>$v['mtime'],
                'auth_url'=>$authUrl,
            ];
        }

       $result['list'] = $list;
        return success($result);
    }

    public function getAdAccountOption($params = [])
    {
        $info = $this->mod->getAdAccountList($params);
        $data = [];
        if($params['is_map']){
            foreach($info['list'] as $v){
                $data[$v['accid']] = [
                    'accid' => $v['accid'],
                    'accname' => $v['accname'],
                ];
            }
        }else{
            foreach($info['list'] as $v){
                $data[] = [
                    'accid' => $v['accid'],
                    'accname' => $v['accname'],
                ];
            }
        }
        
        return success($data);
    }

    public function getAdAccountInfo($id)
    {
        if(!$id) return fail('操作失败');
        $info = $this->mod->getAdAccountInfo($id);
        $config = $info['config'];
        $config = json_decode($config,true);
        foreach($config as $k=>$v){
            $info[$k] = $v;
        }
        unset($info['config']);

        return success($info,'success');
    }

    public function getChannelConfigTplInfo($id)
    {
        $tpl = [];
        $mod = new ModPlatform();
        $info = $mod->getChannelInfo($id);
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

    public function addAdAccountAction($params)
    {
        if(!$params['accname']){
            return fail('请输入资质主体名称');
        }
        if(!$params['alias']){
            $params['alias'] = $params['accname'];
        }
        if(!$params['cid'] || !$params['agtid']|| !$params['pitid']|| !$params['cpnid']){
            return fail('请选择渠道、代理、主体、投放人员');
        }
        $data = [
            'accname'=>$params['accname'],
            'cid'=>(int)$params['cid'],
            'agtid'=>(int)$params['agtid'],
            'alias'=>$params['alias'],
            'pitid'=>(int)$params['pitid'],
            'cpnid'=>(int)$params['cpnid'],
            'status'=>(int)$params['status'],
        ];

        //获取渠道账户配置模板
        $config = [];
        $channelInfo = sy_data_read(Table::$pf_channel,$data['cid']);
        $configTpl = $channelInfo['config_tpl'];
        if($configTpl){
            foreach ($configTpl as $key => $name) {
                $config[$key] = $params[$key];
            }
        }
        $data['config'] = json_encode($config);

        if($params['accid']){
            $this->mod->updateAdAccountAction($data,$params['accid']);
        }else{
            $data['atime'] = time();
            $this->mod->addAdAccountAction($data);
        }
        return success([],'操作成功');
    }

    public function deleteAdAccountAction($id)
    {
        if(!$id){
            return fail('删除失败');
        }
        $ret = $this->mod->deleteAdAccountAction($id);
        if($ret){
            return success([],'删除成功');
        }else{
            return fail('删除失败');
        }
    }

    public function changeAdAccountStatus($params)
    {
        if(!$params['id'] || !isset($params['status'])) return fail('缺少参数');
        $id = $params['id'];
        $status = (int)$params['status'];
        $data = [
            'status'=>$status,
        ];
        $info = $this->mod->getAdAccountInfo($id);
        if(!$info){
            return fail('操作失败');
        }
        $affectedRows = $this->mod->updateAdAccountAction($data,$id);
        if(!$affectedRows<0){
            return fail('操作失败');
        }
        return success([],'操作成功');
    }


     /**
     * ---------------------------------------------
     */
    public function getAdLinkList($data){
        $result = $this->mod->getAdLinkList($data);
        $list = [];
        foreach($result['list'] as $k=>$v){
            $channelInfo = sy_data_read(Table::$pf_channel,$v['cid'],['aquery','iquery']);
            $companyInfo = sy_data_read(Table::$mk_company,$v['cpnid'],['jurl']);
            $infoList = [];
            if($v['apptype'] == 1){
                $infoList[] = [
                    'info_key'=>'监测链接',
                    'info_value'=>$companyInfo['jurl'].'/?'.$channelInfo['aquery'],
                ];
            }
            if($v['apptype'] == 2){
                $infoList[] = [
                    'info_key'=>'监测链接',
                    'info_value'=>$companyInfo['jurl'].'/?'.$channelInfo['iquery'],
                ];
            }
            if($v['apptype'] == 3){
                $infoList[] = [
                    'info_key'=>'创意自定义参数',
                    'info_value'=>'?adcode='.$v['linkid'],
                ];
            }
            $list[] = [
                'linkid'=>$v['linkid'],
                'lname'=>$v['lname'],
                'pid'=>$v['pid'],
                'gid'=>$v['gid'],
                'cid'=>$v['cid'],
                'pkg'=>$v['pkg'],
                'accid'=>$v['accid'],
                'pitid'=>$v['pitid'],
                'ldymid'=>$v['ldymid'],
                'ldyid'=>$v['ldyid'],
                'cpnid'=>$v['cpnid'],
                'apptype'=>$v['apptype'],
                'status'=>$v['status'],
                'atime'=>$v['atime'],
                'mtime'=>$v['mtime'],
                'info_list'=>$infoList,
            ];
        }
        $result['list'] = $list;
        return success($result);
    }

    public function getAdLinkOption($params)
    {
        $info = $this->mod->getAdLinkList($params);
        $data = [];
        foreach($info['list'] as $v){
            $data[] = [
                'linkid' => $v['linkid'],
                'lname' => $v['lname'],
            ];
        }
        if($params['page']){
            $tatalPage = ceil($info['total']/10);
            return success(['list'=>$data,'total'=> $tatalPage]);
        }
        return success($data);
    }

    public function getAdLinkInfo($id)
    {
        if(!$id) return fail('操作失败');
        $info = $this->mod->getAdLinkInfo($id);
        return success($info,'success');
    }

   
    public function addAdLinkAction($params)
    {
        if(!$params['cid']) return fail('请选择渠道ID');
        if(!$params['accid']) return fail('请选择投放账户');
        if(!$params['lname']) return fail('请输入推广链名称');
        if(!$params['apptype']) return fail('请选择应用类型');
        if(!$params['gid']) return fail('请选择游戏');

        $adAccountInfo = $this->mod->getAdAccountInfo($params['accid']);
        if(!$adAccountInfo){
            return fail('系统找不到当前投放账户');
        }

        if($params['linkid']){
            $data = [
                'lname'=>trim($params['lname']),
                'accid'=>(int)$params['accid'],
                'pitid'=>$adAccountInfo['pitid'],
                'cpnid'=>$adAccountInfo['cpnid'],
            ];
            $ret = $this->mod->updateAdLinkAction($data,$params['linkid']);
            if($ret)  return success([],'操作成功');
            return fail('操作失败');
        }else{
            $pkgs = [];
            if($params['apptype'] == 1){
                if(!$params['pkg']) return fail('安卓应用类型请选择渠道包');
                $pkgs = explode(',',$params['pkg']);
                $params['number'] = count($pkgs);
            }
            if($params['number']>20 || $params['number']<0) return fail('批量创建数量最多20');
            if(in_array($params['apptype'],[2,3])){
                for ($i=0; $i < $params['number']; $i++) { 
                    $pkgs[] = '1_'.$params['gid'].'_'.$params['cid'].'_1';
                }
            }
    
            $lnames = [];
            if(preg_match("/{(.*)}/",$params['lname'],$m)){
                $nameId = (int)$m[1];
                for($i=0;$i<$params['number'];$i++){
                    $namePad = str_pad($nameId,4,"0",STR_PAD_LEFT);
                    $replacName = preg_replace("/{(.*)}/",$namePad,$params['lname']);;
                    $lnames[] = $replacName;
                    $nameId = $nameId+1;
                }
            }else{
                if($params['number']>1){
                    return fail('推广链名称缺少占位符');
                }
                $lnames[] = $params['lname'];
            }
    
            $time = time();
            $modPlat = new ModPlatform();
            $result = true;
            $i = 0;
            foreach($lnames as $lname){
                $data = [
                    'lname'=>trim($lname),
                    'pid'=>1,
                    'gid'=>(int)$params['gid'],
                    'cid'=>(int)$params['cid'],
                    'pkg'=>trim($pkgs[$i]),
                    'accid'=>(int)$params['accid'],
                    'pitid'=>$adAccountInfo['pitid'],
                    'cpnid'=>$adAccountInfo['cpnid'],
                    'apptype'=>$params['apptype'],
                    'atime'=>$time,
                ];
                $ret = $this->mod->addAdLinkAction($data);
                if(!$ret){
                    $result = false;
                }
                if($params['apptype'] == 1){
                    $modPlat->updatePkgByPkg(['status'=>2],$pkgs[$i]);
                }
                $i++;
            }

            if($result) return success([],'操作成功');
            return fail('操作失败');
        }
    }

    public function addAdLinkPartnerAction($params)
    {
        if(!$params['pid']) return fail('请选择联运商');
        if(!$params['gid']) return fail('请选择游戏');
        if(!$params['cid']) return fail('请选择推广渠道');
        if(!$params['accid']) return fail('请选择投放账户');
        if(!$params['lname']) return fail('请输入推广链名称');

        $adAccountInfo = $this->mod->getAdAccountInfo($params['accid']);
        if(!$adAccountInfo){
            return fail('系统找不到当前投放账户');
        }
        $mod = new ModPlatform();
        $pkgInfo = $mod->getGamePartnerPkgInfoByPG($params['pid'],$params['gid']);
        if(!$pkgInfo){
            return fail('联运包还未接入，请联系运营！');
        }

        if($params['linkid']){
            $data = [
                'lname'=>trim($params['lname']),
                'accid'=>(int)$params['accid'],
                'pitid'=>$adAccountInfo['pitid'],
                'cpnid'=>$adAccountInfo['cpnid'],
            ];
            $ret = $this->mod->updateAdLinkAction($data,$params['linkid']);
            if($ret)  return success([],'操作成功');
            return fail('操作失败');
        }else{
            if($params['number']>20 || $params['number']<0) return fail('批量创建数量最多20');
            $lnames = [];
            if(preg_match("/{(.*)}/",$params['lname'],$m)){
                $nameId = (int)$m[1];
                for($i=0;$i<$params['number'];$i++){
                    $namePad = str_pad($nameId,4,"0",STR_PAD_LEFT);
                    $replacName = preg_replace("/{(.*)}/",$namePad,$params['lname']);;
                    $lnames[] = $replacName;
                    $nameId = $nameId+1;
                }
            }else{
                if($params['number']>1){
                    return fail('推广链名称缺少占位符');
                }
                $lnames[] = $params['lname'];
            }
    
            $time = time();
            $result = true;
            $i = 0;
            foreach($lnames as $lname){
                $data = [
                    'lname'=>trim($lname),
                    'pid'=>(int)$params['pid'],
                    'gid'=>(int)$params['gid'],
                    'cid'=>(int)$params['cid'],
                    'pkg'=> $pkgInfo['pkg'],
                    'accid'=>(int)$params['accid'],
                    'pitid'=>$adAccountInfo['pitid'],
                    'cpnid'=>$adAccountInfo['cpnid'],
                    'apptype'=> 1,
                    'atime'=>$time,
                ];
                $ret = $this->mod->addAdLinkAction($data);
                if(!$ret){
                    $result = false;
                }
               
                $i++;
            }

            if($result) return success([],'操作成功');
            return fail('操作失败');
        }
    }

    public function deleteAdLinkAction($id)
    {
        if(!$id){
            return fail('删除失败');
        }
        $ret = $this->mod->deleteAdLinkAction($id);
        if($ret){
            return success([],'删除成功');
        }else{
            return fail('删除失败');
        }
    }

    public function updateAdLNameAction($data)
    {
        Debug::log($data);
        if(!$data['linkid']){
            return fail('修改失败');
        }
        $update = ['lname'=>$data['lname']];
        $ret = $this->mod->updateAdLinkAction($update,$data['linkid']);
        if($ret){
            return success([],'修改成功');
        }else{
            return fail('修改失败');
        }
    }

    //-----------------
    public function getTtMajordomoList($params)
    {
        $ttCid = Game::defaultCid(Game::$default_toutiao_slug);
        if(!$ttCid){
            return fail('匹配不到头条渠道ID');
        }
        $developerInfo = sy_data_read(Table::$pf_channel,$ttCid,['config_dev']);
        $list = $this->mod->getTtMajordomoList($params);
        if($list['list']){
            $mapAdvNum = $this->mod->countAdAccountNumInMajor();
            foreach($list['list'] as $k=>$v){
                $list['list'][$k]['auth_url'] = $developerInfo['config_dev']['auth_url'];
                $list['list'][$k]['adv_num'] = (int)$mapAdvNum[$v['account']]['cnt'];
            }
        }
        return success($list,'success');
    }

    public function getTtMajordomoInfo($id)
    {
        if(!$id) return fail('操作失败');
        $info = $this->mod->getTtMajordomoInfo($id);
        return success($info,'success');
    }

    public function addTtMajordomoAction($params)
    {
        if(!$params['cpnid']) return fail('请选择主体');
        if(!$params['agtid']) return fail('请选择代理');
        if(!$params['pitid']) return fail('请选择投放人员');
        if(!$params['account']) return fail('请输入管家账号');
        if(!in_array($params['type'],[Game::$default_toutiao_slug,Game::$default_semtt_slug])){
            return fail('渠道类型错误');
        }
        $data = [
            'agtid'=>$params['agtid'],
            'cpnid'=>$params['cpnid'],
            'pitid'=>$params['pitid'],
        ];

        $id = $params['id'];
        if($id){
            $re = $this->mod->updateTtMajordomoAction($data,$id);
        }else{
            $data['account'] = $params['account'];
            $data['type'] = $params['type'];
            $data['atime'] = time();
            $re = $this->mod->addTtMajordomoAction($data);
        }
        if($re){
            return success([],'操作成功');
        }else{
            return fail('操作失败');
        }
    }

    //从头条api拉取管家号下的广告主账户
    public function pullTtAdvertisers($account){
        if(!$account){
            return fail('缺少参数');
        }
        $baseInfo = $this->mod->getTtMajordomoInfoByAccount($account);
        if(!$baseInfo['access_token']){
            return fail('当前管家号尚未授权，请先进行授权操作！');
        }
        $ttCid = Game::defaultCid(Game::$default_toutiao_slug);
        if(!$ttCid){
            return fail('找不到头条的CID，请联系技术！');
        }
        $developerInfo = sy_data_read(Table::$pf_channel,$ttCid,['config_dev']);
        $appid = $developerInfo['config_dev']['appid'];
        $secret = $developerInfo['config_dev']['secret'];
        if(!$appid || !$secret){
            return fail('头条渠道未配置开发者参数！');
        }

        include_once ROOT .'/app/common/library/mkting/toutiao-marketing-sdk/index.php';
        $auth = new \ToutiaoSdk\ToutiaoAuth($appid,$secret);
        //现获取管家ID
        $ret = $auth->getAdvertisers($baseInfo['access_token']);
        $ret = '{"code":0,"message":"OK","request_id":"202204271906560102112071991A5CE4E1","data":{"list":[{"account_role":"CUSTOMER_ADMIN","advertiser_id":1730605544047683,"advertiser_name":"GZZL-LT-F01","advertiser_role":2,"company_list":[],"is_valid":true}]}}';
        $ret = json_decode($ret,true);
        if(0 !== $ret['code']){
            return fail('获取管家列表失败[getAdvertisers]'.$ret['message']);
        }
        $list = $ret['data']['list'];
        $resultAdvertiser = [];
        if($list){
            foreach($list as $v){
                $majordomo_advertiser_id = $v['advertiser_id'];
                if($v['is_valid'] != 1){
                    continue;
                }
                //取管家下的广告主
                $advsResult = $this->getTtAdvertiser($majordomo_advertiser_id,$baseInfo);
                if(!$advsResult['state']){
                    return fail($advsResult[1]);
                }
                $advs = $advsResult['data'];
                if($advs){
                    $mapAdv = $this->mod->getAdAccountByMajor($account);
                    foreach($advs as $adv){
                        $status = 0;
                        $accid = 0;
                        if(isset($mapAdv[$adv['advertiser_id']])){
                            if($mapAdv[$adv['advertiser_id']]['pitid'] != $baseInfo['pitid']){
                                return fail('系统检测账户有冲突，请检查账户是否重复分配多个管家号：'.$adv['advertiser_name']);
                            }
                            $status = $mapAdv[$adv['advertiser_id']]['status'];
                            $accid = $mapAdv[$adv['advertiser_id']]['accid'];
                        }
                        
                        $resultAdvertiser[] = [
                            'advertiser_major'=>$account,
                            'advertiser_name'=>$adv['advertiser_name'],
                            'advertiser_type'=>$adv['advertiser_type'],
                            'advertiser_id'=>$adv['advertiser_id'],
                            'accid'=>(int)$accid,
                            'status'=>(int)$status,
                        ];
                    }
                    
                }
            }
        }
        return success(['list'=>$resultAdvertiser,'total'=>count($resultAdvertiser)],'操作成功');
    }

     /**
     * 获取广告主列表（管家）
     * 更新数据里面的已授权广告主
     * APPID SECRET TOKEN ADVERTISER_ID
     * @return void
     */
    private function getTtAdvertiser($majordomoAdvertiserId = null,$baseInfo)
    {
        if(!$majordomoAdvertiserId){
            return ['state'=>0,'msg'=>'majordomoAdvertiserId必须'];
        }
        $auth = new \ToutiaoSdk\ToutiaoAuth($baseInfo['appid'], $baseInfo['secret']);
        $page = 1;
        $advs = [];
        while(true){
            try {
                $ret = $auth->CustomerCenterAdvertiserList($baseInfo['access_token'],"".$majordomoAdvertiserId,$page);
                $ret = '{"message": "OK", "code": 0, "data": {"page_info": {"total_number": 30, "page": 1, "page_size": 100, "total_page": 1}, "list": [{"advertiser_id": 1730605939500104, "advertiser_type": "NORMAL", "advertiser_name": "GZZL-LT-F30"}, {"advertiser_id": 1730605938624525, "advertiser_type": "NORMAL", "advertiser_name": "GZZL-LT-F25"}, {"advertiser_id": 1730605937508365, "advertiser_type": "NORMAL", "advertiser_name": "GZZL-LT-F29"}, {"advertiser_id": 1730605936674824, "advertiser_type": "NORMAL", "advertiser_name": "GZZL-LT-F24"}, {"advertiser_id": 1730605935817742, "advertiser_type": "NORMAL", "advertiser_name": "GZZL-LT-F28"}, {"advertiser_id": 1730605934896205, "advertiser_type": "NORMAL", "advertiser_name": "GZZL-LT-F23"}, {"advertiser_id": 1730605934096391, "advertiser_type": "NORMAL", "advertiser_name": "GZZL-LT-F27"}, {"advertiser_id": 1730605933225037, "advertiser_type": "NORMAL", "advertiser_name": "GZZL-LT-F22"}, {"advertiser_id": 1730605932265480, "advertiser_type": "NORMAL", "advertiser_name": "GZZL-LT-F26"}, {"advertiser_id": 1730605931425869, "advertiser_type": "NORMAL", "advertiser_name": "GZZL-LT-F21"}, {"advertiser_id": 1730605692385309, "advertiser_type": "NORMAL", "advertiser_name": "GZZL-LT-F20"}, {"advertiser_id": 1730605691393031, "advertiser_type": "NORMAL", "advertiser_name": "GZZL-LT-F15"}, {"advertiser_id": 1730605690428429, "advertiser_type": "NORMAL", "advertiser_name": "GZZL-LT-F19"}, {"advertiser_id": 1730605689646093, "advertiser_type": "NORMAL", "advertiser_name": "GZZL-LT-F14"}, {"advertiser_id": 1730605688787975, "advertiser_type": "NORMAL", "advertiser_name": "GZZL-LT-F18"}, {"advertiser_id": 1730605687932942, "advertiser_type": "NORMAL", "advertiser_name": "GZZL-LT-F13"}, {"advertiser_id": 1730605687139336, "advertiser_type": "NORMAL", "advertiser_name": "GZZL-LT-F17"}, {"advertiser_id": 1730605686199304, "advertiser_type": "NORMAL", "advertiser_name": "GZZL-LT-F12"}, {"advertiser_id": 1730605685341192, "advertiser_type": "NORMAL", "advertiser_name": "GZZL-LT-F16"}, {"advertiser_id": 1730605684462605, "advertiser_type": "NORMAL", "advertiser_name": "GZZL-LT-F11"}, {"advertiser_id": 1730605611356167, "advertiser_type": "NORMAL", "advertiser_name": "GZZL-LT-F06"}, {"advertiser_id": 1730605610517517, "advertiser_type": "NORMAL", "advertiser_name": "GZZL-LT-F10"}, {"advertiser_id": 1730605609603085, "advertiser_type": "NORMAL", "advertiser_name": "GZZL-LT-F07"}, {"advertiser_id": 1730605608798232, "advertiser_type": "NORMAL", "advertiser_name": "GZZL-LT-F09"}, {"advertiser_id": 1730605607880840, "advertiser_type": "NORMAL", "advertiser_name": "GZZL-LT-F04"}, {"advertiser_id": 1730605607095368, "advertiser_type": "NORMAL", "advertiser_name": "GZZL-LT-F08"}, {"advertiser_id": 1730605606216711, "advertiser_type": "NORMAL", "advertiser_name": "GZZL-LT-F03"}, {"advertiser_id": 1730605605396488, "advertiser_type": "NORMAL", "advertiser_name": "GZZL-LT-F05"}, {"advertiser_id": 1730605604410445, "advertiser_type": "NORMAL", "advertiser_name": "GZZL-LT-F02"}, {"advertiser_id": 1730605542521864, "advertiser_type": "NORMAL", "advertiser_name": "GZZL-LT-F01"}]}, "request_id": "202204271906580101511990760B283F5C"}';
                // Debug::log($ret);
                $result = json_decode($ret,true); 
                if($result['code'] !== 0){
                    return ['state'=>0,'msg'=>'获取已授权的广告主失败[CustomerCenterAdvertiserList]'.$result['message']];
                }
                $advs = array_merge($advs,$result['data']['list']);
                if(empty($result['data']['list']) || $result['data']['page_info']['total_page'] == $page){
                    break;
                }
            } catch (\Throwable $th) {
                Debug::log($th->getMessage());
            }
            
        }
        return ['state'=>1,'data'=>$advs];
    }

    //开启账户，不存在则新增，存在则改状态
    public function syncTtAdvertiser($params)
    {
        if(!$params['advertiser_name'] || !$params['advertiser_major'] || !$params['advertiser_id'] || !in_array($params['status'],['true','false'])) {
            return fail('缺少参数');
        }
        $ttCid = Game::defaultCid(Game::$default_toutiao_slug);
        if(!$ttCid){
            return fail('找不到头条的CID，请联系技术！');
        }
        $majorInfo = $this->mod->getTtMajordomoInfoByAccount($params['advertiser_major']);
        if(!$majorInfo){
            return fail('找不到改管家号');
        }
        $userInfo = $this->mod->adAccountIsExist($params['advertiser_major'],$params['advertiser_id']);
        if($userInfo){
            if($params['status'] == 'true'){
                $status = 0;
            }else{
                $status = 1;
            }
            $accid = $userInfo['accid'];
            $updateAdAccountInfo = [
                'accname'=>$params['advertiser_name'],
                'agtid'=>$majorInfo['agtid'],
                'pitid'=>$majorInfo['pitid'],
                'cpnid'=>$majorInfo['cpnid'],
                'status'=>$status,
            ];
            $ret = $this->mod->updateAdAccountAction($updateAdAccountInfo,$accid);
            if(!$ret){
                return fail('同步更新后台投放账户失败！');
            }
            return success([],'开启成功');
        }else{
            if($params['status'] == 'true'){
                $cid = Game::defaultCid($majorInfo['type']);
                $newAdAccountInfo = [
                    'accname'=>$params['advertiser_name'],
                    'agtid'=>$majorInfo['agtid'],
                    'pitid'=>$majorInfo['pitid'],
                    'cpnid'=>$majorInfo['cpnid'],
                    'acost'=>1,
                    'advertiser_id'=>$params['advertiser_id'],
                    'advertiser_major'=>$params['advertiser_major'],
                    'cid'=>$cid,
                    'atime'=>time()
                ];
                $userId = $this->mod->addAdAccountAction($newAdAccountInfo);
                if(!$userId){
                    return fail('同步添加后台投放账户失败！请检查是否被别人添加了同名账户！');
                }
                return success([],'开启成功');
            }
        }
        return fail('系统错误，请联系技术!');
    }

    public function changeTtMajordomoStatus($params)
    {
        if(!$params['id'] || !isset($params['status'])) return fail('缺少参数');
        $id = $params['id'];
        $status = (int)$params['status'];
        $data = [
            'status'=>$status,
        ];
        $major = $this->mod->getTtMajordomoInfo($id);
        if(!$major){
            return fail('操作失败');
        }
        $affectedRows = $this->mod->updateTtMajordomoAction($data,$id);
        if(!$affectedRows<0){
            return fail('操作失败');
        }
        if($status == 1){
            $this->mod->multiUpdateAdAccountStatusByMajor($major['account'],$status);
        }
        return success([],'操作成功');
    }
}