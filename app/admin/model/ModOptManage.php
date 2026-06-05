<?php

namespace App\admin\model;

use App\admin\service\SrvAuthBusiness;
use App\common\DeviceServer;
use App\common\Table;
use YXLib\foundation\Debug;
use YXLib\foundation\Model;
use YXLib\foundation\ModelFactory;

class ModOptManage extends Model{

    public $conn = 'admin';

    public function getOrderList($data)
    {
        $param = [];
        $sql = "select a.*,m.mgid from `".Table::$t_order."` a left join `".Table::$pf_game."` m on a.gid=m.gid where 1 ";
        $sql_c = "select count(*) as c from `".Table::$t_order."` a left join ".Table::$pf_game." m on a.gid=m.gid where 1 ";
        
        $and = '';
        if($data['mgid']){
            $param['mgid'] = $data['mgid'];
            $and .= "and m.mgid = :mgid ";
        }
        if($data['pid']){
            $param['pid'] = $data['pid'];
            $and .= "and a.pid = :pid ";
        }
        if($data['gid']){
            $param['gid'] = $data['gid'];
            $and .= "and a.gid = :gid ";
        }
        if($data['cid']){
            $param['cid'] = $data['cid'];
            $and .= "and a.cid = :cid ";
        }
        if($data['uid']){
            $param['uid'] = $data['uid'];
            $and .= "and a.uid = :uid ";
        }
        if($data['sdate']){
            $param['sdate'] = strtotime($data['sdate']);
            $and .= " and a.`atime` >= :sdate ";
        }
        if($data['edate']){
            $param['edate'] = strtotime($data['edate'])+86400;
            $and .= " and a.`atime` <= :edate ";
        }
        if($data['dtype']){
            $param['dtype'] = $data['dtype'];
            $and .= " and a.`dtype` = :dtype";
        }
      
        //数据权限
        $authoritySqlAnd = SrvAuthBusiness::makeDataAuthoritySqlAnd('m','a','a');
        $param = array_merge($param,$authoritySqlAnd['param']);
        $and .= $authoritySqlAnd['and'];

        $sql .= " {$and} order by `atime` desc ";
        $sql_c .= " {$and}";
        if($data['page']){
            $pageSize = $data['limit']??20;
            $limit = $this->getLimit($data['page'],$pageSize);
            $sql .= "{$limit} ";
            $count = $this->getOne($sql_c,$param);
            if(!$count['c']) return array();
        }
        return array(
            'list' => $this->query($sql,$param),
            'total' => $count['c'],
        );
    }


    //----------------------------------------
    public function getUserList($data)
    {
        $param = [];
        $sql = "select a.*,m.mgid from `".Table::$user_ext."` a left join `".Table::$pf_game."` m on a.gid=m.gid left join ".Table::$mk_ad_link." c on a.linkid=c.linkid where 1 ";
        $sql_c = "select count(*) as c from `".Table::$user_ext."` a left join ".Table::$pf_game." m on a.gid=m.gid left join ".Table::$mk_ad_link." c on a.linkid=c.linkid where 1 ";
        
        $and = '';
        if($data['mgid']){
            $param['mgid'] = $data['mgid'];
            $and .= "and m.mgid = :mgid ";
        }
        if($data['pid']){
            $param['pid'] = $data['pid'];
            $and .= "and a.pid = :pid ";
        }
        if($data['gid']){
            $param['gid'] = $data['gid'];
            $and .= "and a.gid = :gid ";
        }
        if($data['cid']){
            $param['cid'] = $data['cid'];
            $and .= "and a.cid = :cid ";
        }
        if($data['uid']){
            $param['uid'] = $data['uid'];
            $and .= "and a.uid = :uid ";
        }
        if($data['linkid']){
            $param['linkid'] = $data['linkid'];
            $and .= "and a.linkid = :linkid ";
        }
        if($data['sdate']){
            $param['sdate'] = strtotime($data['sdate']);
            $and .= " and a.`reg_time` >= :sdate ";
        }
        if($data['edate']){
            $param['edate'] = strtotime($data['edate'])+86400;
            $and .= " and a.`reg_time` <= :edate ";
        }
        if($data['dtype']){
            $param['dtype'] = $data['dtype'];
            $and .= " and a.`dtype` = :dtype";
        }

        //投放权限
        $authorityPitids = SrvAuthBusiness::getMkAuthorityCache();
        if($authorityPitids){
            $param['pitid_auth'] = $authorityPitids;
            $and .= " and c.`pitid` in (:pitid_auth)";
        }
      
        //数据权限
        $authoritySqlAnd = SrvAuthBusiness::makeDataAuthoritySqlAnd('m','a','a');
        $param = array_merge($param,$authoritySqlAnd['param']);
        $and .= $authoritySqlAnd['and'];

        $sql .= " {$and} order by `reg_time` desc ";
        $sql_c .= " {$and}";
        if($data['page']){
            $pageSize = $data['limit']??20;
            $limit = $this->getLimit($data['page'],$pageSize);
            $sql .= "{$limit} ";
            $count = $this->getOne($sql_c,$param);
            if(!$count['c']) return array();
        }
        return array(
            'list' => $this->query($sql,$param),
            'total' => $count['c'],
        );
    }

    public function getUserInfo($uid)
    {
        $sql = "select a.*,b.status,b.phone from ".Table::$user_ext." a left join ".Table::$t_user." b on a.uid=b.uid where a.uid =:uid";
        return $this->getOne($sql,['uid'=>$uid]);
    }

    public function getUserDeviceInfo($deviceId)
    {
        $dserver = new DeviceServer();
        $i = $dserver->getDevHashPart($deviceId);
        $deviceTable = Table::$user_device_.$i;
        $sql = "select * from `{$deviceTable}` where `device_id`=:device_id";
        return ModelFactory::getInstance('sdk_user_device')->getOne($sql,['device_id'=>$deviceId]);
    }


    //----------------------------------------
    public function getUserRoleList($data)
    {
        $param = [];
        $sql = "select a.*,m.mgid from `".Table::$user_role."` a left join `".Table::$pf_game."` m on a.gid=m.gid left join ".Table::$mk_ad_link." c on a.linkid=c.linkid where 1 ";
        $sql_c = "select count(*) as c from `".Table::$user_role."` a left join ".Table::$pf_game." m on a.gid=m.gid left join ".Table::$mk_ad_link." c on a.linkid=c.linkid where 1 ";
        
        $and = '';
        if($data['mgid']){
            $param['mgid'] = $data['mgid'];
            $and .= "and m.mgid = :mgid ";
        }
        if($data['pid']){
            $param['pid'] = $data['pid'];
            $and .= "and a.pid = :pid ";
        }
        if($data['gid']){
            $param['gid'] = $data['gid'];
            $and .= "and a.gid = :gid ";
        }
        if($data['cid']){
            $param['cid'] = $data['cid'];
            $and .= "and a.cid = :cid ";
        }
        if($data['uid']){
            $param['uid'] = $data['uid'];
            $and .= "and a.uid = :uid ";
        }
        if($data['rid']){
            $param['rid'] = $data['rid'];
            $and .= "and a.rid = :rid ";
        }
        if($data['linkid']){
            $param['linkid'] = $data['linkid'];
            $and .= "and a.linkid = :linkid ";
        }
        if($data['sdate']){
            $param['sdate'] = strtotime($data['sdate']);
            $and .= " and a.`atime` >= :sdate ";
        }
        if($data['edate']){
            $param['edate'] = strtotime($data['edate'])+86400;
            $and .= " and a.`atime` <= :edate ";
        }
        if($data['dtype']){
            $param['dtype'] = $data['dtype'];
            $and .= " and a.`dtype` = :dtype";
        }

        //投放权限
        $authorityPitids = SrvAuthBusiness::getMkAuthorityCache();
        if($authorityPitids){
            $param['pitid_auth'] = $authorityPitids;
            $and .= " and c.`pitid` in (:pitid_auth)";
        }
      
        //数据权限
        $authoritySqlAnd = SrvAuthBusiness::makeDataAuthoritySqlAnd('m','a','a');
        $param = array_merge($param,$authoritySqlAnd['param']);
        $and .= $authoritySqlAnd['and'];

        $sql .= " {$and} order by `atime` desc ";
        $sql_c .= " {$and}";
        if($data['page']){
            $pageSize = $data['limit']??20;
            $limit = $this->getLimit($data['page'],$pageSize);
            $sql .= "{$limit} ";
            $count = $this->getOne($sql_c,$param);
            if(!$count['c']) return array();
        }
        return array(
            'list' => $this->query($sql,$param),
            'total' => $count['c'],
        );
    }

    //------------
    public function getSdkWhiteNameList($data=[])
    {
        $param = [];
        $sql = "select * from ".Table::$pf_sdk_white." where 1 ";
        $sql_c = "select count(*) as c from ".Table::$pf_sdk_white." where 1 ";
        
        $and = '';
        if($data['name']){
            $param['name'] = $data['name'];
            $and .= "and `name` = :name ";
        }
        if($data['type']){
            $param['type'] = $data['type'];
            $and .= "and `type` = :type ";
        }
       
        $sql .= " {$and} order by `atime` desc ";
        $sql_c .= " {$and}";
        if($data['page']){
            $pageSize = $data['limit']??20;
            $limit = $this->getLimit($data['page'],$pageSize);
            $sql .= "{$limit} ";
            $count = $this->getOne($sql_c,$param);
            if(!$count['c']) return array();
        }
        return array(
            'list' => $this->query($sql,$param),
            'total' => $count['c'],
        );
    }

    public function addSdkWhiteNameAction($data)
    {
        if(!$data){
            return false;
        }
        $this->multiInsert($data,Table::$pf_sdk_white,true);
        return true;
    }

    public function deleteSdkWhiteNameAction($ids)
    {
        if(!$ids) return false;
        $this->delete(['id'=>$ids],0,Table::$pf_sdk_white);
        return true;
    }

    //-----------------
    public function getSdkBlackNameList($data=[])
    {
        $param = [];
        $sql = "select * from ".Table::$pf_sdk_black." where 1 ";
        $sql_c = "select count(*) as c from ".Table::$pf_sdk_black." where 1 ";
        
        $and = '';
        if($data['name']){
            $param['name'] = $data['name'];
            $and .= "and `name` = :name ";
        }
        if($data['type']){
            $param['type'] = $data['type'];
            $and .= "and `type` = :type ";
        }
       
        $sql .= " {$and} order by `atime` desc ";
        $sql_c .= " {$and}";
        if($data['page']){
            $pageSize = $data['limit']??20;
            $limit = $this->getLimit($data['page'],$pageSize);
            $sql .= "{$limit} ";
            $count = $this->getOne($sql_c,$param);
            if(!$count['c']) return array();
        }
        return array(
            'list' => $this->query($sql,$param),
            'total' => $count['c'],
        );
    }

    public function addSdkBlackNameAction($data)
    {
        if(!$data){
            return false;
        }
        $this->multiInsert($data,Table::$pf_sdk_black,true);
        return true;
    }

    public function deleteSdkBlackNameAction($ids)
    {
        if(!$ids) return false;
        $this->delete(['id'=>$ids],0,Table::$pf_sdk_black);
        return true;
    }

    //-----------------
    public function getAnnouncementList($data=[])
    {
        $param = [];
        $sql = "select * from ".Table::$pf_announcement." where 1 ";
        $sql_c = "select count(*) as c from ".Table::$pf_announcement." where 1 ";
        
        $and = '';
        if($data['title']){
            $param['title'] = "%".$data['title']."%";
            $and .= "and `title` like :title ";
        }
    
        $sql .= " {$and} order by `id` desc ";
        $sql_c .= " {$and}";
        if($data['page']){
            $pageSize = $data['limit']??20;
            $limit = $this->getLimit($data['page'],$pageSize);
            $sql .= "{$limit} ";
            $count = $this->getOne($sql_c,$param);
            if(!$count['c']) return array();
        }
        return array(
            'list' => $this->query($sql,$param),
            'total' => $count['c'],
        );
    }

    public function getAnnouncementInfo($id)
    {
        return $this->getById($id,'*','id',Table::$pf_announcement);
    }
 
    public function addAnnouncementAction($data)
    {
        if(!$data){
            return false;
        }
        return $this->insert($data,true,Table::$pf_announcement);
    }

    public function updateAnnouncementAction($data,$id)
    {
        if(!$id){
            return false;
        }
        return $this->update($data,['id'=>$id],Table::$pf_announcement);
    }

    public function getAsyncAnnouncement()
    {
        $time = time();
        $sql = "select `id`,`mapkey` from ".Table::$pf_announcement." where end_time>:end_time order by id desc";
        return $this->query($sql,['end_time'=>$time]);
    }

    
}