<?php

namespace App\admin\model;

use App\admin\service\SrvAuthBusiness;
use App\common\Table;
use YXLib\foundation\Debug;
use YXLib\foundation\Model;

class ModOptLtvReport extends Model{

    public $conn = 'admin';

    public function getData($request = [])
    {
        //查基础数据
        $sql_base = "select `date`,sum(a.`reg`) `reg` 
                from `".Table::$data_overview_link_day."` a
                left join `".Table::$pf_game."` m on a.gid = m.gid
                where 1 ";

        //查消耗
        $sql_consume = "select `date`,sum(`consume`) `consume`
                from `".Table::$data_consume."` a 
                left join ".Table::$pf_game." m on a.gid = m.gid
                where 1 ";

        /**
         * LTV,ROI
         */
        $sql_ltv = "select `date`,a.`pay_date`,sum(a.`pay`) `pay`,sum(`money`) as `money` 
            from `".Table::$data_ltv_link_day."` a
            left join `".Table::$pf_game."` m on a.gid = m.gid
            where 1 ";

        /**
         * 至今付费
         */
        $sql_ltv_tonow = "select `date`,sum(a.`pay`) `pay`,sum(`money`) as `max_money` 
            from `".Table::$data_ltv_link_day_tonow."` a
            left join `".Table::$pf_game."` m on a.gid = m.gid
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
        
        //数据权限
        $authoritySqlAnd = SrvAuthBusiness::makeDataAuthoritySqlAnd('m','a');
        $param = array_merge($param,$authoritySqlAnd['param']);
        $and .= $authoritySqlAnd['and'];
       
        $sql_base = $sql_base. $and . " group by `date` order by null";
        $sql_consume = $sql_consume. $and . " group by `date` order by null";
        $sql_ltv = $sql_ltv. $and . " group by `date`,`pay_date` order by null";
        $sql_ltv_tonow = $sql_ltv_tonow. $and . " group by `date` order by null";

        $dataBase = $this->query($sql_base,$param);
        $dataConsume = $this->query($sql_consume,$param);
        $dataLtv = $this->query($sql_ltv,$param);
        $dataLtvTonow = $this->query($sql_ltv_tonow,$param);

        $base = $this->arrayMerge($dataBase,$dataConsume,['date']);
        $base = $this->arrayMerge($base,$dataLtvTonow,['date']);
        $base = $this->mergeLongData($base,$dataLtv,['date'],'pay_date','money',$request);
        
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