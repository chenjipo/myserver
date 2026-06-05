<?php
namespace App\admin\model;

use App\admin\service\SrvAuth;
use App\admin\service\SrvAuthBusiness;
use App\common\Table;
use YXLib\foundation\Debug;
use YXLib\foundation\Model;
class ModPlatform extends Model{

    protected $conn = 'admin';

    public function getGameDivideList($params)
    {
        $sql = "select * from ".Table::$pf_game_divide." where `month`=:date";
        if($params['mgid']){
            $sql .= " and mgid =:mgid";
        }
        return $this->query($sql,$params);
    }

    public function updateGameDivideAction($data)
    {
        $insert = ['mgid'=>$data['mgid'],'month'=>$data['date'],'divide'=>$data['divide'],'atime'=>time()];
        $update = ['divide'=>$data['divide']];
        $this->insertOrUpdate($insert,$update,Table::$pf_game_divide);
        return $this->affectedRows();
    }

    public function getMgameList($data)
    {
        $param = [];
        $sql = "select * from `".Table::$pf_game_main."` where 1 and status = 0 ";
        $sql_c = "select count(*) as c from `".Table::$pf_game_main."` where 1 and status=0 ";
        if($data['mgname']){
            $param['name'] = '%'.$data['mgname'].'%';
            $sql .= "and mgname like :mgname ";
            $sql_c .= "and mgname like :mgname ";
        }
        if($data['mgid']){
            $param['mgid'] = $data['mgid'];
            $sql .= "and mgid = :mgid ";
            $sql_c .= "and mgid = :mgid ";
        }
        //数据权限
        $authoritySqlAnd = SrvAuthBusiness::makeDataAuthoritySqlAnd(Table::$pf_game_main);
        $param = array_merge($param,$authoritySqlAnd['param']);
        $sql .= $authoritySqlAnd['and'];
        $sql_c .= $authoritySqlAnd['and'];

        $sql .= " order by `sort` desc,`atime` desc ";
        if($data['page']){
            $limit = $this->getLimit($data['page'],100);
            $sql .= "{$limit} ";
        }
        $count = $this->getOne($sql_c,$param);
        if(!$count['c']) return array();
        return array(
            'list' => $this->query($sql,$param),
            'total' => $count['c'],
        );
    }

    public function getMgameInfo($id)
    {
        return $this->getOne("select * from ".Table::$pf_game_main." where mgid=:mgid",['mgid'=>$id]);
    }


    public function addMgameAction($data)
    {
        $insert = [
            'mgname'=>$data['mgname'],
            'sort'=>$data['sort'],
            'atime'=>time()
        ];
        return $this->insert($insert ,true, Table::$pf_game_main);
    }

    public function updateMgameAction($id,$data)
    {
        if(!$id) return false;
        $this->update($data ,['mgid'=>$id],Table::$pf_game_main);
        return $this->affectedRows();
    }

    public function deleteMgameAction($id)
    {
        if(!$id) return false;
        $this->delete(array('mgid'=>$id),1,Table::$pf_game_main);
        return $this->affectedRows();
    }

    //应用
    public function getGameInfo($gid)
    {
        return $this->getOne("select * from ".Table::$pf_game." where gid=:gid",['gid'=>$gid]);
    }

    public function getGameList($data)
    {
        $param = [];
        $sql = "select * from `".Table::$pf_game."` where 1 and status = 0 ";
        $sql_c = "select count(*) as c from `".Table::$pf_game."` where 1 and status=0 ";
        if($data['gname']){
            $param['gname'] = '%'.$data['gname'].'%';
            $sql .= "and gname like :gname ";
            $sql_c .= "and gname like :gname ";
        }
        if($data['gid']){
            $param['gid'] = $data['gid'];
            $sql .= "and gid = :gid ";
            $sql_c .= "and gid = :gid ";
        }
        if($data['mgid']){
            $param['mgid'] = $data['mgid'];
            $sql .= "and mgid = :mgid ";
            $sql_c .= "and mgid = :mgid ";
        }
        if($data['apptype']){
            $param['apptype'] = $data['apptype'];
            $sql .= "and apptype = :apptype ";
            $sql_c .= "and apptype = :apptype ";
        }

        //数据权限
        $authoritySqlAnd = SrvAuthBusiness::makeDataAuthoritySqlAnd(Table::$pf_game);
        $param = array_merge($param,$authoritySqlAnd['param']);
        $sql .= $authoritySqlAnd['and'];
        $sql_c .= $authoritySqlAnd['and'];

        $sql .= " order by `sort` desc,`atime` desc ";
        if($data['page']){
            $limit = $this->getLimit($data['page'],10);
            $sql .= "{$limit} ";
        }
        $count = $this->getOne($sql_c,$param);
        if(!$count['c']) return array();
        return array(
            'list' => $this->query($sql,$param),
            'total' => $count['c'],
        );
    }

    public function addGameAction($insert)
    {
        return $this->insert($insert, true, Table::$pf_game);
    }

    public function updateGameAction($data,$gid)
    {
        if(!$gid) return false;
        $this->update($data,['gid'=>$gid],Table::$pf_game);
        return $this->affectedRows();
    }

    public function getGameSdkVersionList()
    {
        $sql = "select * from ".Table::$pf_game_sdk_version." where 1 order by id desc";
        $list = $this->query($sql);
        return [
            'list'=>$list,
        ];
    }

    public function getGameSdkVersionInfo($id)
    {
        return $this->getOne("select * from ".Table::$pf_game_sdk_version." where id=:id",['id'=>$id]);
    }

    public function addGameSdkVersionAction($data)
    {
        return $this->insert($data,false,Table::$pf_game_sdk_version);
    }

    public function updateGameSdkVersionAction($data,$id)
    {
        if(!$id) return false;
        $this->update($data,['id'=>$id],Table::$pf_game_sdk_version);
        return $this->affectedRows();
    }


    public function getChannelList($data)
    {
        $param = [];
        $sql = "select * from `".Table::$pf_channel."` where 1 and isdel = 0 ";
        $sql_c = "select count(*) as c from `".Table::$pf_channel."` where 1 and isdel=0 ";
        if($data['cname']){
            $param['cname'] = '%'.$data['cname'].'%';
            $sql .= "and cname like :cname ";
            $sql_c .= "and cname like :cname ";
        }
        if($data['cid']){
            $param['cid'] = $data['cid'];
            $sql .= "and cid = :cid ";
            $sql_c .= "and cid = :cid ";
        }
        if($data['type']){
            $param['type'] = $data['type'];
            $sql .= "and type = :type ";
            $sql_c .= "and type = :type ";
        }
        if($data['pid']){
            if($data['pid']<0){
                $sql .= "and pid != :pid ";
                $sql_c .= "and pid != :pid ";
            }else{
                $sql .= "and pid = :pid ";
                $sql_c .= "and pid = :pid ";
            }
            $param['pid'] = abs($data['pid']);
        }

        //数据权限
        $authoritySqlAnd = SrvAuthBusiness::makeDataAuthoritySqlAnd('','',Table::$pf_channel);
        $param = array_merge($param,$authoritySqlAnd['param']);
        $sql .= $authoritySqlAnd['and'];
        $sql_c .= $authoritySqlAnd['and'];

        $sql .= " order by `sort` desc,`atime` desc ";
        if($data['page']){
            $limit = $this->getLimit($data['page'],20);
            $sql .= "{$limit} ";
            $count = $this->getOne($sql_c,$param);
            if(!$count['c']) return array();
        }
        
        return array(
            'list' => $this->query($sql,$param),
            'total' => $count['c'],
        );
    }

    public function getChannelInfo($cid)
    {
        return $this->getOne("select * from ".Table::$pf_channel." where cid=:cid",['cid'=>$cid]);
    }

    public function addChannelAction($insert)
    {
        return $this->insert($insert, true, Table::$pf_channel);
    }


    public function updateChannelAction($data,$cid)
    {
        if(!$cid) return false;
        $this->update($data,['cid'=>$cid],Table::$pf_channel);
        return $this->affectedRows();
    }

    //---
    public function getGamePartnerPkgList($data)
    {
        $sql = "select * from `".Table::$pf_game_partner_pkg."` where 1 ";
        $sql_c = "select count(*) as c from `".Table::$pf_game_partner_pkg."` where 1 ";
    
        if($data['pkg']){
            $param['pkg'] = $data['pkg'];
            $sql .= "and pkg = :pkg ";
            $sql_c .= "and pkg = :pkg ";
        }
        if($data['pid']){
            $param['pid'] = $data['pid'];
            $sql .= "and pid = :pid ";
            $sql_c .= "and pid = :pid ";
        }
        if($data['gid']){
            $param['gid'] = $data['gid'];
            $sql .= "and gid = :gid ";
            $sql_c .= "and gid = :gid ";
        }
        $sql .= " order by `atime` desc ";
        if($data['page']){
            $limit = $this->getLimit($data['page'],20);
            $sql .= "{$limit} ";
        }
        $count = $this->getOne($sql_c,$param);
        if(!$count['c']) return array();
        return array(
            'list' => $this->query($sql,$param),
            'total' => $count['c'],
        );
    }

    public function getGamePartnerPkgInfo($id)
    {
        return $this->getOne("select * from ".Table::$pf_game_partner_pkg." where id=:id",['id'=>$id]);
    }

    public function getGamePartnerPkgInfoByPG($pid,$gid)
    {
        return $this->getOne("select * from ".Table::$pf_game_partner_pkg." where pid=:pid and gid=:gid",['pid'=>$pid,'gid'=>$gid]);
    }

    public function addGamePartnerPkgAction($insert)
    {
        return $this->insert($insert, true, Table::$pf_game_partner_pkg);
    }


    public function updateGamePartnerPkgAction($data,$id)
    {
        if(!$id) return false;
        $this->update($data,['id'=>$id],Table::$pf_game_partner_pkg);
        return $this->affectedRows();
    }

    //---
    public function getPartnerList($data)
    {
        $param = [];
        $sql = "select * from `".Table::$pf_partner."` where 1 ";
        $sql_c = "select count(*) as c from `".Table::$pf_partner."` where 1 ";
        if($data['pname']){
            $param['pname'] = '%'.$data['pname'].'%';
            $sql .= "and pname like :pname ";
            $sql_c .= "and pname like :pname ";
        }
        if($data['pid']){
            if($data['pid'] == -1){
                $param['pid'] = $data['pid'];
                $sql .= "and pid != 1 ";
                $sql_c .= "and pid != 1 ";
            }else{
                $param['pid'] = $data['pid'];
                $sql .= "and pid = :pid ";
                $sql_c .= "and pid = :pid ";
            }
        }

        //数据权限
        $authoritySqlAnd = SrvAuthBusiness::makeDataAuthoritySqlAnd('',Table::$pf_partner);
        $param = array_merge($param,$authoritySqlAnd['param']);
        $sql .= $authoritySqlAnd['and'];
        $sql_c .= $authoritySqlAnd['and'];

        $sql .= " order by `sort` desc,`atime` desc ";
        if($data['page']){
            $limit = $this->getLimit($data['page'],20);
            $sql .= "{$limit} ";
        }
        $count = $this->getOne($sql_c,$param);
        if(!$count['c']) return array();
        return array(
            'list' => $this->query($sql,$param),
            'total' => $count['c'],
        );
    }

    public function getPartnerInfo($pid)
    {
        return $this->getOne("select * from ".Table::$pf_partner." where pid=:pid",['pid'=>$pid]);
    }

    public function addPartnerAction($insert)
    {
        return $this->insert($insert, true, Table::$pf_partner);
    }


    public function updatePartnerAction($data,$pid)
    {
        if(!$pid) return false;
        $this->update($data,['pid'=>$pid],Table::$pf_partner);
        return $this->affectedRows();
    }

    //---


    //---
    public function getPkgList($data)
    {
        $sql = "select * from `".Table::$pf_game_channel_pkg."` where 1 ";
        $sql_c = "select count(*) as c from `".Table::$pf_game_channel_pkg."` where 1 ";
        if($data['specname']){
            $param['specname'] = '%'.$data['specname'].'%';
            $sql .= "and specname like :specname ";
            $sql_c .= "and specname like :specname ";
        }
        if($data['pid']){
            $param['pid'] = $data['pid'];
            $sql .= "and pid = :pid ";
            $sql_c .= "and pid = :pid ";
        }
        if($data['gid']){
            $param['gid'] = $data['gid'];
            $sql .= "and gid = :gid ";
            $sql_c .= "and gid = :gid ";
        }
        if($data['cid']){
            $param['cid'] = $data['cid'];
            $sql .= "and cid = :cid ";
            $sql_c .= "and cid = :cid ";
        }
        if($data['pkg']){
            $param['pkg'] = $data['pkg'];
            $sql .= "and pkg = :pkg ";
            $sql_c .= "and pkg = :pkg ";
        }
        $sql .= " order by `id` desc ";
        if($data['page']){
            $limit = $this->getLimit($data['page'],20);
            $sql .= "{$limit} ";
        }
        $count = $this->getOne($sql_c,$param);
        if(!$count['c']) return array();
        return array(
            'list' => $this->query($sql,$param),
            'total' => $count['c'],
        );
    }

    public function getCanUsedPkgNumber($data)
    {
        $number = 0;
        if(!$data['gid']){
            return ['number'=>$number];
        }
        if(!$data['cid']){
            return ['number'=>$number];
        }
        $sql_count = "select count(*) as number from ".Table::$pf_game_channel_pkg." where gid=:gid and cid=:cid";
        $sql = "select pkg,specname from ".Table::$pf_game_channel_pkg." where gid=:gid and cid =:cid";
        if($data['status']){
            $sql_count .= " and status=:status";
            $sql .= " and status=:status";
        }
        $info = $this->getOne($sql_count,$data);
        $number = (int)$info['number'];
        if(!$number){
            return ['number'=>$number];
        }
        $list = $this->query($sql,$data);
        return ['number'=>$number,'list'=>$list];
    }

    public function getPkgInfo($id)
    {
        return $this->getOne("select * from ".Table::$pf_game_channel_pkg." where id=:id",['id'=>$id]);
    }

    public function addPkgAction($insert)
    {
        return $this->insert($insert, true, Table::$pf_game_channel_pkg);
    }

    public function addMultiPkgAction($inserts)
    {
        return $this->multiInsert($inserts,Table::$pf_game_channel_pkg);
    }

    //添加分包任务
    public function addMultiSplitPkgTaskAction($inserts)
    {
        return $this->multiInsert($inserts,Table::$pf_game_channel_pkg_task);
    }

    /**
     * 分析渠道包的index值
     *
     * @param [type] $pid
     * @param [type] $gid
     * @param [type] $cid
     * @return int
     */
    public function getGamePkgIndex($pid,$gid,$cid){
        $sql = "SELECT (substring_index(pkg, '_', -1)+0 ) as idx FROM ".Table::$pf_game_channel_pkg." WHERE `gid` =:gid AND `cid` =:cid AND `pid`=:pid order by idx desc limit 1";
        $re = $this->getOne($sql,array('pid'=>$pid, 'gid' => $gid , 'cid' => $cid));
        return (int)$re['idx'];
    }

    public function updatePkgAction($data,$id)
    {
        if(!$id) return false;
        $this->update($data,['id'=>$id],Table::$pf_game_channel_pkg);
        return $this->affectedRows();
    }

    public function updatePkgByPkg($data,$pkg)
    {
        if(!$pkg) return false;
        $this->update($data,['pkg'=>$pkg],Table::$pf_game_channel_pkg);
        return $this->affectedRows();
    }

    //---


    public function getGameChannelMPkgList($data)
    {
        $sql = "select * from `".Table::$pf_game_channel_mpkg."` where 1 and gid=:gid and cid=:cid order by id desc";
        return ['list'=>$this->query($sql,$data)];
    }

    public function addGameChannelMPkgAction($data)
    {
        return $this->insert($data,false,Table::$pf_game_channel_mpkg);

    }

    public function updateGameChannelMPkgAction($data,$id)
    {
        return $this->update($data,['id'=>$id],Table::$pf_game_channel_mpkg);
    }

    public function updateGameChannelMPkgAction2($gid,$version,$data)
    {
        if(!$gid || !$version) return false;
        return $this->update($data,['gid'=>$gid,'version'=>$version],Table::$pf_game_channel_mpkg);
    }

    public function getGameChannelMPkgInfo($id)
    {
        return $this->getOne("select * from ".Table::$pf_game_channel_mpkg." where id=:id",['id'=>$id]);
    }

    public function getGameChannelMPkgInfoOfMaxVersion($gid,$cid)
    {
        return $this->getOne("select * from ".Table::$pf_game_channel_mpkg." where gid=:gid and cid=:cid order by id desc",['gid'=>$gid,'cid'=>$cid]);
    }

    public function getAvailableMPkg($params)
    {
        if(!$params['gid'] || !$params['cid']) return [];
        return $this->getOne("select * from ".Table::$pf_game_channel_mpkg." where gid=:gid and cid=:cid and status=2 order by id desc limit 1",$params);
    }

    public function getSplitPkgTaskList($data)
    {
        $sql = "select * from `".Table::$pf_game_channel_pkg_task."` where 1 ";
        $sql_c = "select count(*) as c from `".Table::$pf_game_channel_pkg_task."` where 1 ";
        if($data['specname']){
            $param['specname'] = '%'.$data['specname'].'%';
            $sql .= "and specname like :specname ";
            $sql_c .= "and specname like :specname ";
        }
        if($data['gid']){
            $param['gid'] = $data['gid'];
            $sql .= "and gid = :gid ";
            $sql_c .= "and gid = :gid ";
        }
        if($data['cid']){
            $param['cid'] = $data['cid'];
            $sql .= "and cid = :cid ";
            $sql_c .= "and cid = :cid ";
        }
        if($data['pkg']){
            $param['pkg'] = $data['pkg'];
            $sql .= "and pkg = :pkg ";
            $sql_c .= "and pkg = :pkg ";
        }
        $sql .= " order by `id` desc ";
        if($data['page']){
            $limit = $this->getLimit($data['page'],20);
            $sql .= "{$limit} ";
        }
        $count = $this->getOne($sql_c,$param);
        if(!$count['c']) return array();
        return array(
            'list' => $this->query($sql,$param),
            'total' => $count['c'],
        );
    }

    public function splitPkgTaskProgress($data)
    {
        $sql = "select `state`,count(*) as `total` from `".Table::$pf_game_channel_pkg_task."` where 1";
        if($data['gid']){
            $sql.=" and gid=:gid";
        }
        $sql .= " group by `state`";
        return $this->query($sql,$data);
    }


    //-------------------------------------
    public function getPaywayList($data = [])
    {
        $sql = "select a.*,b.divide from `".Table::$pf_payway."` a left join ".Table::$pf_payway_divide." b on a.paid =b.paid where 1 ";
        $sql_c = "select count(*) as c from `".Table::$pf_payway."` a left join ".Table::$pf_payway_divide." b on a.paid =b.paid where 1 ";
        if($data['pname']){
            $param['pname'] = '%'.$data['pname'].'%';
            $sql .= "and pname like :pname ";
            $sql_c .= "and pname like :pname ";
        }
        if($data['paid']){
            $param['paid'] = $data['paid'];
            $sql .= "and a.paid = :paid ";
            $sql_c .= "and a.paid = :paid ";
        }
        $sql .= " order by a.`paytype` asc,a.`paid` asc ";
        if($data['page']){
            $limit = $this->getLimit($data['page'],20);
            $sql .= "{$limit} ";
            $count = $this->getOne($sql_c,$param);
            if(!$count['c']) return array();
        }
        
        return array(
            'list' => $this->query($sql,$param),
            'total' => $count['c'],
        );
    }

    public function getPaywayInfo($id)
    {
        return $this->getOne("select * from ".Table::$pf_payway." where paid=:id",['id'=>$id]);
    }

    public function getPaywayDivideInfo($id)
    {
        return $this->getOne("select `divide` from ".Table::$pf_payway_divide." where paid=:id",['id'=>$id]);
    }

    public function addPaywayAction($insert)
    {
        return $this->insert($insert, true, Table::$pf_payway);
    }

    public function setPaywayDivideAction($paid,int $divide)
    {
        return $this->insertOrUpdate(['paid'=>$paid,'divide'=>$divide],['divide'=>$divide],Table::$pf_payway_divide);
    }


    public function updatePaywayAction($data,$id)
    {
        if(!$id) return false;
        $this->update($data,['paid'=>$id],Table::$pf_payway);
        return $this->affectedRows();
    }
    
}