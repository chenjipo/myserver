<?php

namespace App\admin\service;

use App\admin\model\ModMkHourReport;
use YXLib\foundation\Cache;
use YXLib\foundation\Debug;

class SrvMkHourReport{
     /**
     * 本实例
     * @var null
     */
    private static $srv = null;

    /**
     * 获取实例
     * @return null|SrvMkHourReport
     */
    public static function getInstance(){
        if(self::$srv == null){
            self::$srv = new self();
        }
        return self::$srv;
    }

    /**
     * 翻译字段
     *
     * @var array
     */
    public $changeForNameArr = array('date_format','mgid_name','gid_name','cid_name','pitid_name','accid_name');

   
   
     /**
     * 默认的自定义字段
     *
     * @var array
     */
    public $defaultCustomCols = array(
        'click','active','reg_rate','reg_device_rate','new_pay_rate','reg','reg_device','new_pay','ltv','reg_unit_price','new_pay_unit_price','consume','new_pay_money','roi',
        'time0','time1','time2','time3','time4','time5','time6','time7','time8','time9','time10','time11','time12','time13','time14','time15','time16','time17','time18','time19','time20','time21','time22','time23'
    );

    /**
     * 自作列字段
     *
     * @param array $groupby
     * @param string $index
     * @param boolean $all
     * @return array
     */
    private function makeFields($groupby ,$all = false,$index = [])
    {

        $fields = [
            ['field'=>'consume','title'=>'成本','width'=>'80','style'=>'color:#0882ca'],
            //注册用户
            ['field'=>'click','title'=>'点击数',],
            ['field'=>'reg_device','title'=>'设备注册'],
            ['field'=>'reg_device_rate','title'=>'设备注册率','width'=>'80'],
            ['field'=>'reg','title'=>'注册数','width'=>'70'],
            ['field'=>'role_create_rate','title'=>'创角率'],
            ['field'=>'active','title'=>'激活数',],
            ['field'=>'reg_unit_price','title'=>'注册价','style'=>'color:#0fa555'],
            ['field'=>'reg_rate','title'=>'注册率'],

            //付费用户
            ['field'=>'new_pay','title'=>'新付数',],
            ['field'=>'new_pay_unit_price','title'=>'新付价',],
            ['field'=>'new_pay_rate','title'=>'新付率',],//new_pay/reg
            ['field'=>'new_pay_money','title'=>'新付额',],
            //综合
            ['field'=>'ltv','title'=>'LTV',],
            ['field'=>'roi','title'=>'ROI'],
        ];

        if(count($index) == 4){
            $width = '130';
        }elseif(count($index)== 3){
            $width = '100';
        }elseif(count($index) == 2){
            $width = '70';
        }else{
            $width = '50';
        }

        $level = [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23];
        foreach($level as $l){
            $col = ['field'=>'time'.$l, 'title'=> $l.'时', 'width'=> $width];
            $fields[] = $col;
            
        }
        if($all){
            return $fields;
        }

        $groupField['date'] = ['field'=>'date_format','title'=>'日期','width'=>'100', 'fixed'=>true];
        $groupField['mgid'] = ['field'=>'mgid_name','title'=>'主游戏','width'=>'100', 'fixed'=>true];
        $groupField['gid'] = ['field'=>'gid_name','title'=>'游戏','width'=>'100', 'fixed'=>true];
        $groupField['cid'] = ['field'=>'cid_name','title'=>'渠道','width'=>'100', 'fixed'=>true];
        $groupField['pitid'] = ['field'=>'pitid_name','title'=>'投手','width'=>'80', 'fixed'=>true];
        $groupField['accid'] = ['field'=>'accid_name','title'=>'账户','width'=>'100', 'fixed'=>true];
        $groupField['linkid'] = ['field'=>'linkid','title'=>'推广链', 'fixed'=>true];

        if(in_array('linkid',$groupby)){
            array_unshift($fields,$groupField['linkid']);
        }
        if(in_array('accid',$groupby)){
            array_unshift($fields,$groupField['accid']);
        }
        if(in_array('pitid',$groupby)){
            array_unshift($fields,$groupField['pitid']);
        }
        if(in_array('cid',$groupby)){
            array_unshift($fields,$groupField['cid']);
        }
        if(in_array('gid',$groupby)){
            array_unshift($fields,$groupField['gid']);
        }
        if(in_array('mgid',$groupby)){
            array_unshift($fields,$groupField['mgid']);
        }
        if(in_array('date',$groupby)){
            array_unshift($fields,$groupField['date']);
        }

        return $fields;
    }

    /**
     * 返回结果集
     *
     * @param array $request
     * @return void
     */
    public function getData($request)
    {
        $cols = [];
        $rows = [];

        $request['sdate'] = $request['date'].' 00:00:00';
        $request['edate'] = $request['date'].' 23:59:59';
        
        if(empty($request['groupby'])){
            $request['groupby'] = array('mgid','pitid');
        }else{
            $request['groupby'] = explode(',',$request['groupby']);
        }
        array_unshift($request['groupby'],'date');
        $request['index'] = explode(',',$request['index']);
        if($request['cid'] == 'null'){
            $request['cid'] = '';
        }
        if($request['accid'] == 'null'){
            $request['accid'] = '';
        }
        if($request['pitid'] == 'null'){
            $request['pitid'] = '';
        }
        if($request['gid'] == 'null'){
            $request['gid'] = '';
        }
        if($request['has_cost'] == 'undefined'){
            $request['has_cost'] = 0;
        }

        $groupby = $request['groupby'];
        $index = $request['index'];

        /**
         * 返回行
         */
        $rows = [];
        /**
         * 汇总维度字段 翻译数组
         */
        $changeFor = $this->makeChangeFor($groupby);
       
        /**
         * 获取原始数据
         */
        $info = ModMkHourReport::getInstance()->getData($request);
        /**
         * 去掉没消耗的数据
         */
        $list = [];
        foreach ($info['list'] as $key => $v) {
            if($request['has_cost'] && $v['consume']<=0){
                continue;
            }
            $list[] = $v;
        }
        $info['list'] = $list;
        $base = $info['list'];

        $rows = [];
        foreach($base as $v){
            $rows[] = $this->makeRow($v,$request,$changeFor);
        }

        $sortCost= array_column($rows,'consume');
        array_multisort( $sortCost, SORT_DESC,$rows);

        /**
         * 基础数据汇总行处理
         */
        $sumDataOri = $this->sumData($base,$groupby);
        $sumDataRow = $this->makeRow($sumDataOri,$request);
        $sumDataRow['date_format'] = '汇总';
        

        /**
         * 表头字段
         */
        $cols = $this->makeFields($request['groupby'],false,$index);
        /**
         * 只显示客户自定义字段
         */
        $customCols = $customRows = [];

        $customColsMc = $this->defaultCustomCols;
        foreach ($cols as $v) {
            if(in_array($v['field'],$customColsMc) || in_array($v['field'],$groupby) || in_array($v['field'],$this->changeForNameArr)){
                $customCols[] = $v;
            }
        }
        
        $customRows = $rows;
        /**
         * 合并汇总行
         */
        array_unshift($customRows,$sumDataRow);

        return success(['cols'=>[$customCols],'rows'=>$customRows]);
    }

    /**
     * 维度没有选择日期时 需要除去日期再按剩余维度合并
     *
     * @param [type] $data
     * @param [array] $groupBy
     * @return void
     */
    public function sumWithoutDate($data,$groupBy)
    {
        $list = [];
        foreach($data as $v){
            $key = [];
            foreach($groupBy as $g){
                $key[] = $v[$g];
            }
            $key = implode('|',$key);
            $list[$key][] = $v;
        }

        $ret = [];
        foreach($list as $key => $l){
            $row = $this->sumData($l,$groupBy);
            //补回维度字段
            $fix = [];
            $keys = explode('|',$key);
            $i = 0;
            foreach ($groupBy as $groupCol) {
                $fix[$groupCol] = $keys[$i];
                $i++;
            }
            $row = array_merge($row,$fix);//覆盖
            $ret[] = $row;
        }

        return $ret;
    }

    /**
     * 按指标合并 用|分割显示 改用前端处理
     *
     * @param [type] $hourList
     * @param [type] $index
     * @return void
     */
    public function hourListMerge($hourList,$indexs)
    {
        $list = [];
        foreach ($indexs as $index) {
            foreach ($hourList[$index] as $key => $hourArr) {
                foreach ($hourArr as $indexNum => $indexValue) {
                    $list[$key][$indexNum][] = $indexValue;
                }
            }
        }
        $ret = [];
        foreach ($list as $k=>$h) {
            foreach ($h as $key => $v) {
                $v = implode('|',$v);
                $ret[$k][$key] = $v;
            }
            
        }

        return $ret;
    }

    

    /**
     * 获取汇总维度 翻译数组
     *
     * @param [type] $groupby
     * @return void
     */
    public function makeChangeFor(array $groupby)
    {
        $changeFor = [];
        $srv = new SrvPlatform;
        if(in_array('mgid',$groupby)){
            $result = $srv->getMgameOption(['is_map'=>1])->getContent();
            $changeFor['mgid'] = $result['data'];
        }
        if(in_array('gid',$groupby)){
            $result = $srv->getGameOption(['is_map'=>1])->getContent();
            $changeFor['gid'] = $result['data'];
        }
        if(in_array('cid',$groupby)){
            $result = $srv->getChannelOption(['is_map'=>1])->getContent();
            $changeFor['cid'] = $result['data'];
        }
        $srv = new SrvMk;
        if(in_array('pitid',$groupby)){
            $result = $srv->getPitcherOption(['is_map'=>1])->getContent();
            $changeFor['pitid'] = $result['data'];
        }
        if(in_array('accid',$groupby)){
            $result = $srv->getAdAccountOption(['is_map'=>1])->getContent();
            $changeFor['accid'] = $result['data'];
        }
        return $changeFor;
    }

    /**
     * 制作行数据返回
     *
     * @param [type] $v 每行原始数据
     * @param [type] $groupby 汇总维度
     * @param [type] $index 指标
     * @param [type] $dimensionTime 时度
     * @param [type] $changeFor 汇总维度字段翻译
     * @return void
     */
    public function makeRow($v,$request,$changeFor = [])
    {
        $row = [];
        if(in_array('date',$request['groupby'])){
            $row['date'] = $v['date'];
            $row['sort_date'] = strtotime($v['date']);
            if($v['date']){
                $format_date = $this->getDayOfWeek($v['date'],date('N',strtotime($v['date'])));
                $row['date_format'] = $format_date[0];
            }else{
                $row['date_format'] = '';
            }
        }
        if(in_array('mgid',$request['groupby'])){
            $row['mgid'] = $v['mgid'];
            if($v['mgid']){
                $row['mgid_name'] = $v['mgid'].'-'.$changeFor['mgid'][$v['mgid']]['mgname'];
            }else{
                $row['mgid_name'] = '';
            }
        }
        if(in_array('gid',$request['groupby'])){
            $row['gid'] = $v['gid'];
            if($v['gid']){
                $row['gid_name'] = $v['gid'].'-'.$changeFor['gid'][$v['gid']]['gname'];
            }else{
                $row['gid_name'] = '';
            }
        }
        if(in_array('cid',$request['groupby'])){
            $row['cid'] = $v['cid'];
            if($v['cid']){
                $row['cid_name'] = $v['cid'] .'-'. $changeFor['cid'][$v['cid']]['cname'];
            }else{
                $v['cid_name'] = '';
            }
        }
        if(in_array('pitid',$request['groupby'])){
            $row['pitid'] = $v['pitid'];
            if($v['pitid']){
                $row['pitid_name'] = $v['pitid'].'-'.$changeFor['pitid'][$v['pitid']]['pitname'];
            }else{
                $row['pitid_name'] = '';
            }
        }
        if(in_array('accid',$request['groupby'])){
            $row['accid'] = $v['accid'];
            if($v['accid']){
                $user_name = $changeFor['accid'][$v['accid']]['alias'];
                $row['accid_name'] = $v['accid'].'-'.($user_name?$user_name:$changeFor['accid'][$v['accid']]['accname']);
            }else{
                $row['accid_name'] ='';
            }
        }
        if(in_array('linkid',$request['groupby'])){
            $row['linkid'] = $v['linkid'];
        }

        $v['consume'] = $this->mybcdiv($v['consume'],100,false);
        $v['money'] = $this->mybcdiv($v['money'],100,false);
        $v['new_pay_money'] = $this->mybcdiv($v['new_pay_money'],100,false);

        //成本
        $row['consume'] = $this->floatVal($v['consume']);
        //注册用户
        $row['click'] = $this->intVal($v['click']);
        $row['reg_device'] = $this->intVal($v['reg_device']);
        $row['reg_device_rate'] = $this->mybcdiv($v['reg_device'],$v['active']);
        $row['reg'] = $this->intVal($v['reg']);
        $row['role_create_rate'] = $this->mybcdiv($v['role_created'],$v['reg']);
        $row['active'] = $this->intVal($v['active']);
        $row['reg_unit_price'] = $this->mybcdiv($v['consume'],$v['reg'],false);
        $row['reg_rate'] = $this->mybcdiv($v['reg'],$v['active']);

        $row['new_pay'] = $this->intVal($v['new_pay']);
        $row['new_pay_unit_price'] = $this->mybcdiv($v['consume'],$v['new_pay'],false);
        $row['new_pay_rate'] = $this->mybcdiv($v['new_pay'],$v['reg']);
        $row['new_pay_money'] = $this->floatVal($v['new_pay_money']);

        $row['ltv'] = $this->mybcdiv($v['new_pay_money'],$v['reg'],false);
        $row['roi'] =  $this->mybcdiv($v['new_pay_money'],$v['consume']);
       
        $h = date('H');
        $hasToday = strtotime($request['edate']) > strtotime(date('Y-m-d'));
        foreach (range(0,23) as $hour) {
            $row['reg'.$hour] = $v['reg'.$hour]?$v['reg'.$hour]:'0';
            if($hour>$h && $hasToday){
                $row['reg'.$hour] = '-';
            }
        }
        foreach (range(0,23) as $hour) {
            $row['new_pay'.$hour] = $v['new_pay'.$hour]?$v['new_pay'.$hour]:'0';
            if($hour>$h && $hasToday){
                $row['new_pay'.$hour] = '-';
            }
        }
        foreach (range(0,23) as $hour) {
            $row['new_pay_money'.$hour] = $v['new_pay_money'.$hour]? $this->mybcdiv($v['new_pay_money'.$hour],100,false):'0';
            if($hour>$h && $hasToday){
                $row['new_pay_money'.$hour] = '-';
            }
        }
        
        return $row;
    }

    /**
     * 汇总所有行 加法计算
     *
     * @param [type] $data
     * @param [type] $groupby
     * @return void
     */
    public function sumData($data,array $groupby = array())
    {
        $result = [];
        foreach($data as $row){
            foreach($row as $k=>$v){
                if(!in_array($k,$groupby)){
                    $result[$k] += $v;
                }
            }
        }
        if($groupby){
            $merge = [];
            foreach($groupby as $g){
                $merge[$g]='';
            }
            $result = array_merge($merge,$result);
        }
       
        return $result;
    }

    /**
     * @param $year
     * @param int $week
     * @param string $sdate
     * @param string $edate
     * @return mixed
     */
    public function getWeekDayOfYear($week,$year,$sdate = '',$edate = ''){
        $t = strtotime($year.'-01-01');
        $day = date('w',$t);
        //第一个周日
        $firstEndDate = $t + (7-$day)*86400;

        if($week == 0){
            $sd = date('m/d',$t);
            $ed = date('m/d',$firstEndDate);
        }else{
            $st = $firstEndDate + (86400*7*($week-1)) + 86400;
            //先判断有没跨年
            if(date('Y',$t) != $year){
                //不存在这种情况
            }
            $sd = date('m/d',$st);
            $et = $st+(7*86400) - 86400;
            $ed = date('m/d',$et);
            if(date('Y',$et) != $year){
                $ed = '12/31';
            }
        }
        $symd = $year.'/'.$sd;
        $eymd = $year.'/'.$ed;

        if(strtotime($sdate)>strtotime($year.'/'.$sd)){
            $symd = date('Y/m/d',strtotime($sdate));
            $sd = date('m/d',strtotime($sdate));
        }

        if(strtotime($edate)<strtotime($year.'/'.$ed)){
            $eymd = date('Y/m/d',strtotime($edate));
            $ed = date('m/d',strtotime($edate));
        }
        return array(substr($year,2)."年第".($week+1). "周 " . $sd .'~' .$ed, $symd, $eymd);
    }

    /**
     * month
     *
     * @param [type] $month
     * @param [type] $year
     * @return void
     */
    public function getMonthDayOfYear($month,$year,$sdate = '',$edate = ''){
        $start = $year. '-' . $month . '-01';
        $end = date('m/d',strtotime('+ 1 months',strtotime($start))-86400);
        $start = $month . '/01';

        $symd = $year.'/'.$start;
        $eymd = $year.'/'.$end;

        if(strtotime($sdate)>strtotime($symd)){
            $symd = date('Y/m/d',strtotime($sdate));
            $start = date('m/d',strtotime($sdate));
        }

        if(strtotime($edate)<strtotime($eymd)){
            $eymd = date('Y/m/d',strtotime($edate));
            $end = date('m/d',strtotime($edate));
        }

        return array(substr($year,2).'年'.$month."月 ".$start.'~'.$end, $symd, $eymd);
    }

    /**
     * 获取星期
     * @param $num
     * @return array
     */
    public function getDayOfWeek($date,$num){
        $color = '';
        switch($num){
            case 1 :$format = '(一)';
                break;
            case 2 :$format = '(二)';
                break;
            case 3 :$format = '(三)';
                break;
            case 4 :$format = '(四)';
                break;
            case 5 :$format = '(五)';
                break;
            case 6 :$format = '(六)';$color = '#dd4b39';
                break;
            case 7 :$format = '(日)';$color = '#dd4b39';
                break;
            default:$format = $color = '';

        }
        return array($date . ' ' .$format,$color);
    }


    /**
     * 除法
     *
     * @param [type] $left_operand
     * @param [type] $right_operand
     * @param boolean $isRate 默认返回百分比
     * @param integer $scale 保留几位小数
     * @return void
     */
    private function mybcdiv($left_operand,$right_operand, $isRate = true,$scale = 1){
        if((int)$right_operand == 0){
            if($isRate){
                return '0%';
            }
            return '0';
        }

        if($isRate){
            return (100 * bcdiv($left_operand,$right_operand,$scale + 2)) .'%';
        }
        return bcdiv($left_operand,$right_operand,$scale);
    }

    /**
     * 返回整数
     * @param [type] $val
     * @return void
     */
    private function intVal($val)
    {
        return $val ? $val : '0';
    }

    /**
     * 返回浮点数
     *
     * @param [type] $val
     * @return void
     */
    private function floatVal($val){
        return bcdiv($val,1,1);
    }
}