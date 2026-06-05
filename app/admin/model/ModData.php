<?php

namespace App\admin\model;

use App\admin\service\SrvAuth;
use App\admin\service\SrvAuthBusiness;
use App\common\Table;
use YXLib\foundation\Debug;
use YXLib\foundation\Model;

class ModData extends Model{

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
            default:
                return 'a';
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

    public function getData($request)
    {
        $groupbyInfo = $this->makeSqlGroupBy($request);
        $group_fields = implode(',',$groupbyInfo);
        //查基础数据
        $sql_base = "select $group_fields,sum(`cost`) `cost`,sum(`sign_num`) `sign_num`,sum(`unsign_num`) `unsign_num`,sum(`new_duid`) `new_duid`,sum(`new_pay_duid`) `new_pay_duid`,sum(`new_pay_money`) `new_pay_money`,sum(`login_duid`) `login_duid`,sum(`pay_duid`) `pay_duid`,sum(`pay_money`) `pay_money`,sum(`ad_money`) `ad_money` 
                from `".Table::$data_overview_day."` a
                where 1 ";
      
        /*
         * 留存
         */
        $sql_retain = "select $group_fields,`ldate`,sum(`retain_duid_num`) retain 
            from `".Table::$data_retain."` a 
            where 1 ";

    
        $param = array();
        $and = '';
        if($request['pid']){
            $param['pid'] = is_array($request['pid']) ? $request['pid'] : explode(',',$request['pid']);
            $and .= " and a.`pid` in (:pid) ";
        }
        if($request['appid']){
            $param['appid'] = is_array($request['appid']) ? $request['appid'] : explode(',',$request['appid']);
            $and .= " and a.`appid` in (:appid) ";
        }
        if($request['cid']){
            $param['cid'] = is_array($request['cid']) ? $request['cid'] : explode(',',$request['cid']);
            $and .= " and a.`cid` in (:cid) ";
        }
       
        if($request['sdate']){
            $param['sdate'] = date('Ymd',strtotime($request['sdate']));
            $and .= " and a.`ymd` >= :sdate ";
        }
        if($request['edate']){
            $param['edate'] = date('Ymd',strtotime($request['edate']));
            $and .= " and a.`ymd` <= :edate ";
        }
        if($request['os']){
            $param['os'] = $request['os'];
            $and .= " and a.`os` = :os";
        }
       
        $sql_base = $sql_base. $and . " group by " . $group_fields .' order by null';
        $sql_retain = $sql_retain. $and . " group by " . $group_fields .',`ldate` order by null';

        $base = $this->query($sql_base,$param);

        $dataRetain = $this->query($sql_retain,$param);
        $base = $this->mergeLongData($base,$dataRetain,$request['groupby'],'ldate','retain',$request);


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
            if(in_array('ymd',$groupby)){
                $startDay = $v['ymd'];
            }else{
                $startDay = $request['edate'];
            }
            if(isset($list[$k])){
                $map = $list[$k];
                $longData = [];
                foreach(range(1,45) as $num){
                    $nTime = strtotime($startDay)+($num-1)*86400;
                    if($nTime>$todayTime) break;
                    $nDay = date('Ymd',$nTime);
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