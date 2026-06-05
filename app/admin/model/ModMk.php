<?php

namespace App\admin\model;

use App\admin\service\SrvAuthBusiness;
use App\common\Game;
use App\common\Table;
use YXLib\foundation\Model;

class ModMk extends Model{

    protected $conn = 'admin';

    public function getPitcherTeamList($data)
    {
        $sql = "select * from `".Table::$mk_pitcher_team."` where 1 ";
        $sql_c = "select count(*) as c from `".Table::$mk_pitcher_team."` where 1 ";
        if($data['teamname']){
            $param['teamname'] = '%'.$data['teamname'].'%';
            $sql .= "and teamname like :teamname ";
            $sql_c .= "and teamname like :teamname ";
        }
        if($data['teamid']){
            $param['teamid'] = $data['teamid'];
            $sql .= "and teamid = :teamid ";
            $sql_c .= "and teamid = :teamid ";
        }
        $sql .= " order by `sort` desc ";
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

    public function getPitcherTeamInfo($id)
    {
        return $this->getOne("select * from ".Table::$mk_pitcher_team." where teamid=:id",['id'=>$id]);
    }

    public function addPitcherTeamAction($insert)
    {
        return $this->insert($insert, true, Table::$mk_pitcher_team);
    }

    public function updatePitcherTeamAction($data,$id)
    {
        if(!$id) return false;
        $this->update($data,['teamid'=>$id],Table::$mk_pitcher_team);
        return $this->affectedRows();
    }

    public function deletePitcherTeamAction($id)
    {
        if(!$id){
            return false;
        }
        $this->delete(['teamid'=>$id],1,Table::$mk_pitcher_team);
        return $this->affectedRows();
    }


    /**
     * ----------------------------------------------------
     */
    public function getPitcherList($data)
    {
        $sql = "select * from `".Table::$mk_pitcher."` where 1 ";
        $sql_c = "select count(*) as c from `".Table::$mk_pitcher."` where 1 ";
        if($data['pitname']){
            $param['pitname'] = '%'.$data['pitname'].'%';
            $sql .= "and pitname like :pitname ";
            $sql_c .= "and pitname like :pitname ";
        }
        if($data['pitid']){
            $param['pitid'] = $data['pitid'];
            $sql .= "and pitid = :pitid ";
            $sql_c .= "and pitid = :pitid ";
        }
        if($data['teamid']){
            $param['teamid'] = $data['teamid'];
            $sql .= "and teamid = :teamid ";
            $sql_c .= "and teamid = :teamid ";
        }

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

    public function getEnabledPitcherOption()
    {
        $sql = "select admin_id,nick from ".Table::$admin_user." where admin_id not in (select pitid from ".Table::$mk_pitcher.")";
        return $this->query($sql);
    }

    public function getPitcherInfo($id)
    {
        return $this->getOne("select * from ".Table::$mk_pitcher." where pitid=:id",['id'=>$id]);
    }

    public function addPitcherAction($insert)
    {
        return $this->insert($insert, false, Table::$mk_pitcher);
    }

    public function updatePitcherAction($data,$id)
    {
        if(!$id) return false;
        $this->update($data,['pitid'=>$id],Table::$mk_pitcher);
        return $this->affectedRows();
    }

    public function deletePitcherAction($id)
    {
        if(!$id){
            return false;
        }
        $this->delete(['pitid'=>$id],1,Table::$mk_pitcher);
        return $this->affectedRows();
    }

    /**
     * ----------------------------------------------------
     */

    public function getCompanyList($data)
    {
        $sql = "select * from `".Table::$mk_company."` where 1 ";
        $sql_c = "select count(*) as c from `".Table::$mk_company."` where 1 ";
        if($data['cpnname']){
            $param['cpnname'] = '%'.$data['cpnname'].'%';
            $sql .= "and cpnname like :cpnname ";
            $sql_c .= "and cpnname like :cpnname ";
        }
        if($data['cpnid']){
            $param['cpnid'] = $data['cpnid'];
            $sql .= "and cpnid = :cpnid ";
            $sql_c .= "and cpnid = :cpnid ";
        }
        $sql .= " order by `atime` desc ";
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

    public function getCompanyOptionNoAuth($params = [])
    {
        $sql = "select cpnid,cpnname from `".Table::$mk_company."` where 1 ";
        return $this->query($sql);
    }

    public function getCompanyInfo($id)
    {
        return $this->getOne("select * from ".Table::$mk_company." where cpnid=:id",['id'=>$id]);
    }

    public function addCompanyAction($insert)
    {
        return $this->insert($insert, true, Table::$mk_company);
    }

    public function updateCompanyAction($data,$id)
    {
        if(!$id) return false;
        $this->update($data,['cpnid'=>$id],Table::$mk_company);
        return $this->affectedRows();
    }

    public function deleteCompanyAction($id)
    {
        if(!$id){
            return false;
        }
        $this->delete(['cpnid'=>$id],1,Table::$mk_company);
        return $this->affectedRows();
    }

    /**
     * ----------------------------------------------------
     */

    public function getAgentList($data)
    {
        $sql = "select * from `".Table::$mk_agent."` where 1 ";
        $sql_c = "select count(*) as c from `".Table::$mk_agent."` where 1 ";
        if($data['agtname']){
            $param['agtname'] = '%'.$data['agtname'].'%';
            $sql .= "and agtname like :agtname ";
            $sql_c .= "and agtname like :agtname ";
        }
        if($data['agtid']){
            $param['agtid'] = $data['agtid'];
            $sql .= "and agtid = :agtid ";
            $sql_c .= "and agtid = :agtid ";
        }
        $sql .= " order by `atime` desc ";
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

    public function getAgentOptionNoAuth($params = [])
    {
        $sql = "select * from `".Table::$mk_agent."` where 1 ";
        return $this->query($sql);
    }

    public function getAgentInfo($id)
    {
        return $this->getOne("select * from ".Table::$mk_agent." where agtid=:id",['id'=>$id]);
    }

    public function addAgentAction($insert)
    {
        return $this->insert($insert, true, Table::$mk_agent);
    }

    public function updateAgentAction($data,$id)
    {
        if(!$id) return false;
        $this->update($data,['agtid'=>$id],Table::$mk_agent);
        return $this->affectedRows();
    }

    public function deleteAgentAction($id)
    {
        if(!$id){
            return false;
        }
        $this->delete(['agtid'=>$id],1,Table::$mk_agent);
        return $this->affectedRows();
    }

    /**
     * ----------------------------------------------------
     */
    public function getAgentRebateList($data)
    {
        $sql = "select * from `".Table::$mk_agent_rebate."` where 1 ";
        $sql_c = "select count(*) as c from `".Table::$mk_agent_rebate."` where 1 ";
        if($data['cid']){
            $param['cid'] = $data['cid'];
            $sql .= "and cid = :cid ";
            $sql_c .= "and cid = :cid ";
        }
        if($data['agtid']){
            $param['agtid'] = $data['agtid'];
            $sql .= "and agtid = :agtid ";
            $sql_c .= "and agtid = :agtid ";
        }
        $sql .= " order by `atime` desc ";
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

    public function addAgentRebateAction($save,$id)
    {
        if($id){
            unset($save['cid']);
            unset($save['agtid']);
            $this->update($save,['id'=>$id],Table::$mk_agent_rebate);
            return $this->affectedRows();
        }else{
            $save['atime'] = time();
            return $this->insert($save,true,Table::$mk_agent_rebate);
        }
    }

    public function delAgentRebateAction($id)
    {
        if(!$id){
            return false;
        }
        $this->delete(['id'=>$id],1,Table::$mk_agent_rebate);
        return $this->affectedRows();
    }

    /**
     * ----------------------------------------------------
     */

    public function getAdAccountList($data)
    {
        $sql = "select * from `".Table::$mk_ad_account."` where 1 ";
        $sql_c = "select count(*) as c from `".Table::$mk_ad_account."` where 1 ";
        if($data['accname']){
            $param['accname'] = '%'.$data['accname'].'%';
            $sql .= "and accname like :accname ";
            $sql_c .= "and accname like :accname ";
        }
        if($data['accid']){
            $param['accid'] = $data['accid'];
            $sql .= "and accid = :accid ";
            $sql_c .= "and accid = :accid ";
        }
        if($data['cid']){
            $param['cid'] = $data['cid'];
            $sql .= "and cid = :cid ";
            $sql_c .= "and cid = :cid ";
        }
        if($data['agtid']){
            $param['agtid'] = $data['agtid'];
            $sql .= "and agtid = :agtid ";
            $sql_c .= "and agtid = :agtid ";
        }
        if($data['cpnid']){
            $param['cpnid'] = $data['cpnid'];
            $sql .= "and cpnid = :cpnid ";
            $sql_c .= "and cpnid = :cpnid ";
        }
        if($data['pitid']){
            $param['pitid'] = $data['pitid'];
            $sql .= "and pitid = :pitid ";
            $sql_c .= "and pitid = :pitid ";
        }
        if($data['status']){
            $status = $data['status'] == 1?'0':'1';
            $param['status'] = $status;
            $sql .= "and status = :status ";
            $sql_c .= "and status = :status ";
        }

        //投放权限
        $authorityPitids = SrvAuthBusiness::getMkAuthorityCache();
        if($authorityPitids){
            $param['pitid_auth'] = $authorityPitids;
            $sql .= " and `pitid` in (:pitid_auth)";
            $sql_c .= " and `pitid` in (:pitid_auth)";
        }

        $sql .= " order by `atime` desc,`accid` desc ";
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

    public function getAdAccountInfo($id)
    {
        return $this->getOne("select * from ".Table::$mk_ad_account." where accid=:id",['id'=>$id]);
    }

    public function addAdAccountAction($insert)
    {
        return $this->insert($insert, true, Table::$mk_ad_account);
    }

    public function updateAdAccountAction($data,$id)
    {
        if(!$id) return false;
        $this->update($data,['accid'=>$id],Table::$mk_ad_account);
        return $this->affectedRows();
    }

    public function deleteAdAccountAction($id)
    {
        if(!$id){
            return false;
        }
        $this->delete(['accid'=>$id],1,Table::$mk_ad_account);
        return $this->affectedRows();
    }

    public function getAdAccountByMajor($majorAccount)
    {
        $sql = "select advertiser_id,accid,pitid,status from ".Table::$mk_ad_account." where advertiser_major=:advertiser_major";
        $map = $this->query($sql,['advertiser_major'=>$majorAccount],'advertiser_id');
        return $map;
    }

    public function getAdAccountByName($accname)
    {
        $sql = "select * from ".Table::$mk_ad_account." where `accname` = :accname";
        return $this->getOne($sql,['accname'=>$accname]);
    }

    public function countAdAccountNumInMajor()
    {
        $sql = "select advertiser_major,count(*) as cnt from ".Table::$mk_ad_account." where status=0 group by advertiser_major";
        return $this->query($sql,[], 'advertiser_major');
    }

    public function multiUpdateAdAccountStatusByMajor($major,$status)
    {
        $data = ['status'=>$status];
        $where = ['advertiser_major'=>$major];
        $this->update($data,$where,Table::$mk_ad_account);
        return $this->affectedRows();
    }

    public function multiUpdateAdAccountByMajor($major,$data)
    {
        $where = ['advertiser_major'=>$major];
        $this->update($data,$where,Table::$mk_ad_account);
        return $this->affectedRows();
    }


    /**
     * ----------------------------------------------------
     */

    public function getAdLinkList($data)
    {
        $param = [];
        $sql = "select a.* from `".Table::$mk_ad_link."` a left join `".Table::$pf_game."` m on a.gid=m.gid where 1 ";
        $sql_c = "select count(*) as c from `".Table::$mk_ad_link."` a left join ".Table::$pf_game." m on a.gid=m.gid where 1 ";
        if($data['keyword']){
            $param['lname'] = '%'.$data['keyword'].'%';
            $param['linkid'] = (int)$data['keyword'];
            $sql .= "and (lname like :lname or linkid =:linkid)";
            $sql_c .= "and (lname like :lname or linkid =:linkid)";
        }
        if($data['lname']){
            $param['lname'] = '%'.$data['lname'].'%';
            $sql .= "and lname like :lname ";
            $sql_c .= "and lname like :lname ";
        }
        if($data['linkid']){
            $param['linkid'] = $data['linkid'];
            $sql .= "and linkid = :linkid ";
            $sql_c .= "and linkid = :linkid ";
        }
        if($data['pid']){
            $param['pid'] = $data['pid'];
            $sql .= "and pid = :pid ";
            $sql_c .= "and pid = :pid ";
        }
        if($data['gid']){
            $param['gid'] = $data['gid'];
            $sql .= "and a.gid = :gid ";
            $sql_c .= "and a.gid = :gid ";
        }
        if($data['cid']){
            $param['cid'] = $data['cid'];
            $sql .= "and cid = :cid ";
            $sql_c .= "and cid = :cid ";
        }
        if($data['accid']){
            $param['accid'] = $data['accid'];
            $sql .= "and accid = :accid ";
            $sql_c .= "and accid = :accid ";
        }
        if($data['cpnid']){
            $param['cpnid'] = $data['cpnid'];
            $sql .= "and cpnid = :cpnid ";
            $sql_c .= "and cpnid = :cpnid ";
        }
        if($data['pitid']){
            $param['pitid'] = $data['pitid'];
            $sql .= "and pitid = :pitid ";
            $sql_c .= "and pitid = :pitid ";
        }
        if($data['ldymid']){
            $param['ldymid'] = $data['ldymid'];
            $sql .= "and ldymid = :ldymid ";
            $sql_c .= "and ldymid = :ldymid ";
        }
        if($data['status']){
            $status = $data['status'] == 1?'0':'1';
            $param['status'] = $status;
            $sql .= "and a.status = :status ";
            $sql_c .= "and a.status = :status ";
        }
        //投放权限
        $authorityPitids = SrvAuthBusiness::getMkAuthorityCache();
        if($authorityPitids){
            $param['pitid_auth'] = $authorityPitids;
            $sql .= " and a.`pitid` in (:pitid_auth)";
            $sql_c .= " and a.`pitid` in (:pitid_auth)";
        }
        //数据权限
        $authoritySqlAnd = SrvAuthBusiness::makeDataAuthoritySqlAnd('m','a','a');
        $param = array_merge($param,$authoritySqlAnd['param']);
        $sql .= $authoritySqlAnd['and'];
        $sql_c .= $authoritySqlAnd['and'];

        $sql .= " order by `linkid` desc ";
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

    public function getAdLinkInfo($id)
    {
        return $this->getOne("select * from ".Table::$mk_ad_link." where linkid=:id",['id'=>$id]);
    }

    public function addAdLinkAction($insert)
    {
        return $this->insert($insert, true, Table::$mk_ad_link);
    }

    public function updateAdLinkAction($data,$id)
    {
        if(!$id) return false;
        $this->update($data,['linkid'=>$id],Table::$mk_ad_link);
        return $this->affectedRows();
    }

    public function deleteAdLinkAction($id)
    {
        if(!$id){
            return false;
        }
        $this->update(['status'=>1],['linkid'=>$id],Table::$mk_ad_link);
        return $this->affectedRows();
    }

    

    //-------------
    public function getTtMajordomoInfoByAccount($account)
    {
        $sql = "select * from ".Table::$mk_tt_majordomo." where account=:account";

        return $this->getOne($sql,['account'=>$account]);
    }

    public function getTtMajordomoInfo($id)
    {
        $sql = "select * from ".Table::$mk_tt_majordomo." where id=:id";
        return $this->getOne($sql,['id'=>$id]);
    }

    public function getTtMajordomoList($data)
    {
        $sql = "select * from `".Table::$mk_tt_majordomo."` where 1 ";
        $sql_c = "select count(*) as c from `".Table::$mk_tt_majordomo."` where 1 ";
        if($data['account']){
            $param['account'] = '%'.$data['account'].'%';
            $sql .= "and account like :account ";
            $sql_c .= "and account like :account ";
        }
        if($data['pitid']){
            $param['pitid'] = $data['pitid'];
            $sql .= "and pitid = :pitid ";
            $sql_c .= "and pitid = :pitid ";
        }
        if($data['cpnid']){
            $param['cpnid'] = $data['cpnid'];
            $sql .= "and cpnid = :cpnid ";
            $sql_c .= "and cpnid = :cpnid ";
        }
        if($data['agtid']){
            $param['agtid'] = $data['agtid'];
            $sql .= "and agtid = :agtid ";
            $sql_c .= "and agtid = :agtid ";
        }
        if($data['status']){
            $status = $data['status'] == 1?'0':'1';
            $param['status'] = $status;
            $sql .= "and status = :status ";
            $sql_c .= "and status = :status ";
        }

        //投放权限
        $authorityPitids = SrvAuthBusiness::getMkAuthorityCache();
        if($authorityPitids){
            $param['pitid_auth'] = $authorityPitids;
            $sql .= " and `pitid` in (:pitid_auth)";
            $sql_c .= " and `pitid` in (:pitid_auth)";
        }

        $sql .= "order by `id` desc";
        if($data['page']){
            $limit = $this->getLimit($data['page'],20);
            $sql .= "{$limit} ";
            $count = $this->getOne($sql_c,$param);
            if(!$count['c']) return array();
        }
      
        return array(
            'list' => $this->query($sql,$param),
            'total' => (int)$count['c'],
        );
    }

    public function addTtMajordomoAction($data)
    {
        return $this->insert($data,true,Table::$mk_tt_majordomo);
    }

    public function updateTtMajordomoAction($data,$id)
    {
        $this->update($data,['id'=>$id],Table::$mk_tt_majordomo);
        return $this->affectedRows();
    }

    //检查后台账户是否存在
    public function adAccountIsExist($advertiserMajor,$advertiserId)
    {
        $sql = "select `accid`,`pitid`,`status` from ".Table::$mk_ad_account." where advertiser_id=:advertiser_id and advertiser_major =:advertiser_major";
        return $this->getOne($sql,['advertiser_id'=>$advertiserId,'advertiser_major'=>$advertiserMajor]);
    }

  
}