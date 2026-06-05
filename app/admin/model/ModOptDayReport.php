<?php

namespace App\admin\model;

use App\admin\service\SrvAuth;
use App\admin\service\SrvAuthBusiness;
use App\common\Table;
use YXLib\foundation\Debug;
use YXLib\foundation\Model;

class ModOptDayReport extends Model{

    public $conn = 'admin';

    public function getData($request = [])
    {
        $groupbyInfo = $this->makeSqlGroupBy($request);
        $group_fields = implode(',',$groupbyInfo);
        //查基础数据
        $sql_base = "select $group_fields,sum(a.`active`) `active`,sum(a.`reg`) `reg`,sum(a.`reg_device`) `reg_device`,sum(a.`role_created`) `role_created`,sum(a.`login_user`) `login_user` 
                from `".Table::$data_overview_day."` a
                left join `".Table::$pf_game."` m on a.gid = m.gid
                where 1 ";
        //查消耗
        $sql_consume = "select $group_fields,sum(`consume`) `consume`
                from `".Table::$data_consume."` a 
                left join ".Table::$pf_game." m on a.gid = m.gid
                where 1 ";

        //查付费
        $sum_pay_money = $this->makeSqlDivideMoneyFileds('money',$request['divide']);
        $sql_pay = "select $group_fields,sum(a.`pay`) `pay`,$sum_pay_money 
                from `".Table::$data_pay_link_day."` a
                left join `".Table::$pf_game."` m on a.gid = m.gid
                where 1 ";

        //查新增付费
        $sum_new_pay_money = $this->makeSqlDivideMoneyFileds('new_pay_money',$request['divide']);
        $sql_new_pay = "select $group_fields,sum(a.`new_pay`) `new_pay`,$sum_new_pay_money 
                from `".Table::$data_new_pay_link_day."` a
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
       
        $sql_base = $sql_base. $and . " group by " . $group_fields .' order by null';
        $sql_consume = $sql_consume. $and . " group by " . $group_fields .' order by null';
        $sql_pay = $sql_pay. $and . " group by " . $group_fields .' order by null';
        $sql_new_pay = $sql_new_pay. $and . " group by " . $group_fields .' order by null';

        $dataBase = $this->query($sql_base,$param);
        $dataConsume = $this->query($sql_consume,$param);
        $dataPay = $this->query($sql_pay,$param);
        $dataNewPay = $this->query($sql_new_pay,$param);

        $base = $this->arrayMerge($dataBase,$dataConsume,$request['groupby']);
        $base = $this->arrayMerge($base,$dataPay,$request['groupby']);
        $base = $this->arrayMerge($base,$dataNewPay,$request['groupby']);
        
        $result = array(
            'list' => $base,
        );

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
            case 'pid':
            case 'gid':
                return 'a';
                break;
            default:
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
}