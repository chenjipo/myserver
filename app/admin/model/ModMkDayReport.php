<?php

namespace App\admin\model;

use App\admin\service\SrvAuth;
use App\admin\service\SrvAuthBusiness;
use App\common\Table;
use YXLib\foundation\Debug;
use YXLib\foundation\Model;

class ModMkDayReport extends Model{

    protected $conn = 'admin';

    /**
     * 通过字段找到所在表
     *
     * @param [type] $field
     * @return void
     */
    private static function gTable($field)
    {
        switch ($field) {
            case 'mgid':
                return 'm';
                break;
            case 'date':
            case 'gid':
            case 'cid':
            case 'linkid':
                return 'a';
                break;
            case 'accid':
            case 'pitid':
            default:
                return 'c';
                break;
        }
        return '';
    }

    /**
     * makesql
     *
     * @param [type] $request
     * @return array
     */
    private function makeSqlGroupBy($request){
        $requestGroupBy = is_array($request['groupby'])?$request['groupby']:explode(',',$request['groupby']);
        $groupByFields = [];

        foreach($requestGroupBy as $v){
            $table = self::gTable($v);
            $groupByFields[]  = $table.'.`'.$v.'`';
        }
        return $groupByFields;
    }

    /**
     * makesql
     *
     * @param [type] $field
     * @param [type] $divide
     * @return void
     */
    public function makeSqlDivideMoneyFileds($field,$divide)
    {
        $table = 'a';
        switch($field){
            case 'money':
                if($divide == 1){//扣掉研发分成和渠道分成
                    $sql = "sum($table.`money` - $table.`dmoney` - $table.`cmoney`) `money`";
                }
                elseif($divide == 2){//扣掉研发分成
                    $sql = "sum($table.`money` - $table.`dmoney`) `money`";
                }
                elseif($divide == 3){//扣掉研发分成
                    $sql = "sum($table.`money` - $table.`cmoney`) `money`";
                }
                else{
                    $sql = "sum($table.`money`) `money`";
                }
                return $sql;
                break;
            case 'new_pay_money':
                if($divide == 1){
                    $sql = "sum($table.`new_pay_money` - $table.`new_pay_dmoney` - $table.`new_pay_cmoney`) `new_pay_money`";
                }
                elseif($divide == 2){
                    $sql = "sum($table.`new_pay_money` - $table.`new_pay_dmoney`) `new_pay_money`";
                }
                elseif($divide == 3){
                    $sql = "sum($table.`new_pay_money` - $table.`new_pay_cmoney`) `new_pay_money`";
                }
                else{
                    $sql = "sum($table.`new_pay_money`) `new_pay_money`";
                }
                return $sql;
                break;
            case 'ltv_money':
                if($divide == 1){
                    $sql = "sum($table.`money` - $table.`dmoney` - $table.`cmoney`) `ltv_money`";
                }
                elseif($divide == 2){
                    $sql = "sum($table.`money` - $table.`dmoney`) `ltv_money`";
                }
                elseif($divide == 3){
                    $sql = "sum($table.`money` - $table.`cmoney`) `ltv_money`";
                }
                else{
                    $sql = "sum($table.`money`) `ltv_money`";
                }
                return $sql;
                break;
            case 'max_money':
                if($divide == 1){
                    $sql = "sum($table.`money` - $table.`dmoney` - $table.`cmoney`) `max_money`";
                }
                elseif($divide == 2){
                    $sql = "sum($table.`money` - $table.`dmoney`) `max_money`";
                }
                elseif($divide == 3){
                    $sql = "sum($table.`money` - $table.`cmoney`) `max_money`";
                }
                else{
                    $sql = "sum($table.`money`) `max_money`";
                }
                return $sql;
                break;
        }
        return '';
    }
    
    public function getData($request)
    {
        $groupbyInfo = $this->makeSqlGroupBy($request);
        $group_fields = implode(',',$groupbyInfo);
        //查基础数据
        $sql_base = "select $group_fields,sum(a.`click`) `click`,sum(a.`active`) `active`,sum(a.`reg`) `reg`,sum(a.`reg_device`) `reg_device`,sum(a.`role_created`) `role_created`,sum(a.`login_user`) `login_user` 
                from `".Table::$data_overview_link_day."` a
                left join `".Table::$pf_game."` m on a.gid = m.gid
                left join `".Table::$mk_ad_link."` c on a.linkid = c.linkid
                where 1 ";
        //查消耗
        $sql_consume = "select $group_fields,sum(`consume`) `consume`
                from `".Table::$data_consume."` a 
                left join ".Table::$pf_game." m on a.gid = m.gid
                left join ".Table::$mk_ad_link." c on a.linkid = c.linkid
                where 1 ";

        //查付费
        $sum_pay_money = $this->makeSqlDivideMoneyFileds('money',$request['divide']);
        $sql_pay = "select $group_fields,sum(a.`pay`) `pay`,$sum_pay_money 
                from `".Table::$data_pay_link_day."` a
                left join `".Table::$pf_game."` m on a.gid = m.gid
                left join `".Table::$mk_ad_link."` c on a.linkid = c.linkid
                where 1 ";

        //查新增付费
        $sum_new_pay_money = $this->makeSqlDivideMoneyFileds('new_pay_money',$request['divide']);
        $sql_new_pay = "select $group_fields,sum(a.`new_pay`) `new_pay`,$sum_new_pay_money 
                from `".Table::$data_new_pay_link_day."` a
                left join `".Table::$pf_game."` m on a.gid = m.gid
                left join `".Table::$mk_ad_link."` c on a.linkid = c.linkid
                where 1 ";

        /**
         * LTV,ROI
         */
        $sum_money = $this->makeSqlDivideMoneyFileds('ltv_money',$request['divide']);
        $sql_ltv = "select $group_fields,a.`pay_date`,sum(a.`pay`) `pay`,$sum_money 
            from `".Table::$data_ltv_link_day."` a
            left join `".Table::$pf_game."` m on a.gid = m.gid
            left join `".Table::$mk_ad_link."` c on a.linkid = c.linkid
            where 1 ";

        /**
         * 至今付费
         */
        $sum_money = $this->makeSqlDivideMoneyFileds('max_money',$request['divide']);
        $sql_ltv_tonow = "select $group_fields,sum(a.`pay`) `pay`,$sum_money 
            from `".Table::$data_ltv_link_day_tonow."` a
            left join `".Table::$pf_game."` m on a.gid = m.gid
            left join `".Table::$mk_ad_link."` c on a.linkid = c.linkid
            where 1 ";

        /**
         * 留存
         */
        $sql_retain = "select $group_fields,a.`login_date`,sum(a.`login`) `retain` 
            from `".Table::$data_retain_link_day."` a 
            left join `".Table::$pf_game."` m on a.gid = m.gid 
            left join `".Table::$mk_ad_link."` c on a.linkid = c.linkid
            where 1 ";

        /**
         * 付费留存
         */
        $sql_payReatin = "select $group_fields,a.`login_date`,sum(a.`login`) `pay_retain` 
            from `".Table::$data_retain_pay_link_day."` a 
            left join `".Table::$pf_game."` m on a.gid = m.gid 
            left join `".Table::$mk_ad_link."` c on a.linkid = c.linkid
            where 1 ";
    
        $param = array();
        $and = '';
        if($request['mgid']){
            $param['mgid'] = is_array($request['mgid']) ? $request['mgid'] : explode(',',$request['mgid']);
            $and .= " and a.`mgid` in (:mgid) ";
        }
        if($request['pid']){
            $param['pid'] = is_array($request['pid']) ? $request['pid'] : explode(',',$request['pid']);
            $and .= " and a.`pid` in (:pid) ";
        }
        if($request['gid']){
            $param['gid'] = is_array($request['gid']) ? $request['gid'] : explode(',',$request['gid']);
            $and .= " and a.`gid` in (:gid) ";
        }
        if($request['cid']){
            $param['cid'] = is_array($request['cid']) ? $request['cid'] : explode(',',$request['cid']);
            $and .= " and a.`cid` in (:cid) ";
        }
        if($request['linkid']){
            $param['linkid'] = is_array($request['linkid']) ? $request['linkid'] : explode(',',$request['linkid']);
            $and .= " and a.`linkid` in (:linkid) ";
        }
        if($request['sdate']){
            $param['sdate'] = $request['sdate'];
            $and .= " and a.`date` >= :sdate ";
        }
        if($request['edate']){
            $param['edate'] = $request['edate'];
            $and .= " and a.`date` <= :edate ";
        }
        if($request['dtype']){
            $param['dtype'] = $request['dtype'];
            $and .= " and a.`dtype` = :dtype";
        }
        if($request['pitid']){
            $param['pitid'] = is_array($request['pitid'])?$request['pitid']:explode(',',$request['pitid']);
            $and .= " and c.`pitid` in (:pitid)";
        }
        if($request['accid']){
            $param['accid'] = is_array($request['accid'])?$request['accid']:explode(',',$request['accid']);
            $and .= " and c.`accid` in (:accid)";
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
       
        $sql_base = $sql_base. $and . " group by " . $group_fields .' order by null';
        $sql_consume = $sql_consume. $and . " group by " . $group_fields .' order by null';
        $sql_pay = $sql_pay. $and . " group by " . $group_fields .' order by null';
        $sql_new_pay = $sql_new_pay. $and . " group by " . $group_fields .' order by null';
        $sql_ltv = $sql_ltv. $and . " group by " . $group_fields .',`pay_date` order by null';
        $sql_ltv_tonow = $sql_ltv_tonow. $and . " group by " . $group_fields .' order by null';
        $sql_retain = $sql_retain. $and . " group by " . $group_fields .',`login_date` order by null';
        $sql_payReatin = $sql_payReatin. $and . " group by " . $group_fields .',`login_date` order by null';

        $dataBase = $this->query($sql_base,$param);
        $dataConsume = $this->query($sql_consume,$param);
        $dataPay = $this->query($sql_pay,$param);
        $dataNewPay = $this->query($sql_new_pay,$param);
        $dataLtvTonow = $this->query($sql_ltv_tonow,$param);

        $findBase = [$dataBase,$dataConsume];
        $base = $this->findBase($findBase,$request['groupby']);

        $base = $this->arrayMerge($base,$dataBase,$request['groupby']);
        $base = $this->arrayMerge($base,$dataConsume,$request['groupby']);
        $base = $this->arrayMerge($base,$dataPay,$request['groupby']);
        $base = $this->arrayMerge($base,$dataNewPay,$request['groupby']);
        $base = $this->arrayMerge($base,$dataLtvTonow,$request['groupby']);
        
        if(in_array($request['index'],array('ltv','roi','beilv','increase'))){
            $dataLtv = $this->query($sql_ltv,$param);
            $base = $this->mergeLongData($base,$dataLtv,$request['groupby'],'pay_date','ltv_money',$request);
        }
        if(in_array($request['index'],array('retain'))){
            $dataRetain = $this->query($sql_retain,$param);
            $base = $this->mergeLongData($base,$dataRetain,$request['groupby'],'login_date','retain',$request);
        }
        if(in_array($request['index'],array('pay_retain'))){
            $dataPayRetain = $this->query($sql_payReatin,$param);
            $base = $this->mergeLongData($base,$dataPayRetain,$request['groupby'],'login_date','pay_retain',$request);
        }

        $result = array(
            'list' => $base,
        );

        return $result;
    }

    public function mergeLongData($base,$data,$groupby,$keyField,$valueField,$request)
    {
        $list = [];
        $todayTime = strtotime(date('Y-m-d'));
        foreach ($data as $v) {
            $keyValue = [];
            foreach($groupby as $g){
                $keyValue[] = $v[$g];
            }
            $keyValue = implode('|',$keyValue);
            $list[$keyValue][$v[$keyField]] = $v[$valueField];
        }
        $_base = [];
        foreach($base as $v){
            $keyValue = [];
            foreach($groupby as $g){
                $keyValue[] = $v[$g];
            }
            $keyValue = implode('|',$keyValue);
            $_base[$keyValue] = $v;
        }
        $result = [];
        foreach($_base as $k => $v){
            if(in_array('date',$groupby)){
                $startDay = $v['date'];
            }else{
                $startDay = $request['edate'];
            }
            if(isset($list[$k])){
                $map = $list[$k];
                $longData = [];
                foreach(range(1,45) as $num){
                    $nTime = strtotime($startDay)+($num-1)*86400;
                    if($nTime>$todayTime) break;
                    $nDay = date('Y-m-d',$nTime);
                    $longData['day'.$num] = (int)$map[$nDay];
                }
                $result[] = array_merge($v,$longData);
            }else{
                $longData = [];
                foreach(range(1,45) as $num){
                    $nTime = strtotime($startDay)+($num-1)*86400;
                    if($nTime>$todayTime) break;
                    $longData['day'.$num] = 0;
                }
                $result[] = array_merge($v,$longData);
            }
        }
        return $result;
    }

    /**
     * 取所有结果涉及到所有的维度
     *
     * @param [type] $data
     * @param [type] $groupby
     * @return void
     */
    public function findBase($data,$groupby)
    {
        // $groupby = explode(',',$groupby);

        $base = [];
        foreach($data as $list){
            foreach($list as $v){
                $key = [];
                foreach($groupby as $g){
                    $key[] = $g."=".$v[$g];
                }
                $key = implode('&',$key);
                $base[$key] = 1;
            }
        }
        $result = [];
        foreach($base as $k => $v){
            $row = [];
            parse_str($k,$row);
            $result[] = $row;
        }
        return $result;
    }


    public function arrayMerge($base,$list,$groupby)
    {
        $_base = [];
        foreach($base as $v){
            $keyValue = [];
            foreach($groupby as $g){
                $keyValue[] = $v[$g];
            }
            $keyValue = implode('|',$keyValue);
            $_base[$keyValue] = $v;
        }
        $_list = [];
        foreach($list as $v){
            $keyValue = [];
            foreach($groupby as $g){
                $keyValue[] = $v[$g];
            }
            $keyValue = implode('|',$keyValue);
            $_list[$keyValue] = $v;
        }

        $result = [];
        foreach($_base as $k => $v){
            if(isset($_list[$k])){
                $result[] = array_merge($v,$_list[$k]);
            }else{
                $result[]= $v;
            }
        }
        return $result;
    }
  
}