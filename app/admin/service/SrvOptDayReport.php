<?php
namespace App\admin\service;

use App\admin\model\ModOptDayReport;
use YXLib\foundation\Cache;
use YXLib\foundation\Debug;

class SrvOptDayReport {

    public $mod;

    public function __construct()
    {
        $this->mod = new ModOptDayReport();
    }

    public function getData($request)
    {
        $cols = [];
        $rows = [];
        
        if(empty($request['groupby'])){
            $request['groupby'] = array('date');
        }else{
            $request['groupby'] = explode(',',$request['groupby']);
        }

        $groupby = $request['groupby'];


        /**
         * 返回行
         */
        $rows = [];
        /**
         * 汇总维度字段 翻译数组
         */
        $changeFor = $this->makeChangeFor($groupby);


        $info = $this->mod->getData($request);
        
        /**
         * 汇总行处理
         */
        $sumDataOri = $this->sumData($info['list'],$groupby);
        $sumDataRow = $this->makeRow($sumDataOri,$request,$changeFor);

        $sumDataRow['date_format'] = '汇总';

        /**
         * 每一行处理
         */
        foreach($info['list'] as $v){
            $rows[] = $this->makeRow($v,$request,$changeFor);
        }

        /**
         * 合并汇总行
         */
        array_unshift($rows,$sumDataRow);

        /**
         * 表头字段
         */
        $cols = $this->makeFields($groupby);

        return success(['cols'=>[$cols],'rows'=>$rows]);
    }

    /**
     * 自作列字段
     *
     * @param array $groupby
     * @return array
     */
    private function makeFields($groupby)
    {

        $fields = [
            //激活率	注册率	新付率	点击数	激活数	注册数	新付数	注册单价	新付单价	成本	盈亏	注册AP 1	新付AP 1	首充数%	首充额%	最高者%	至今ROI
            ['field'=>'reg_device','title'=>'注册设备'],
            ['field'=>'reg_device_rate','title'=>'设备注册率','width'=>'100'],
            ['field'=>'reg','title'=>'注册数'],
            ['field'=>'reg_rate','title'=>'注册率'],
            ['field'=>'role_create_rate','title'=>'创角率'],
            ['field'=>'active','title'=>'激活数'],
            ['field'=>'active_rate','title'=>'激活率'],
            ['field'=>'reg_unit_price','title'=>'注册价','style'=>'color:#0fa555'],

            ['field'=>'new_pay','title'=>'新付数'],
            ['field'=>'new_pay_money','title'=>'新付额'],
            ['field'=>'new_pay_rate','title'=>'新付率'],//new_pay/reg
            ['field'=>'new_pay_unit_price','title'=>'新付价'],

            ['field'=>'old_user','title'=>'老用户'],//登陆用户数-注册用户数

            ['field'=>'new_reg_arpu','title'=>'新增ARPU','width'=>'100'],//首日新用户付费总额 / 首日新增注册数			
            ['field'=>'new_pay_arppu','title'=>'新增ARPPU','width'=>'100'],//首日新用户付费总额 / 首日新付费人数			
            ['field'=>'arpu','title'=>'ARPU'],//即ARPU，期内新用户付费总额 / 期内新注册用户数
            ['field'=>'arppu','title'=>'ARPPU'],//即ARPPU，期内新用户付费总额 / 期内新付费用户数
            ['field'=>'pay','title'=>'总付数'],//付费数
            ['field'=>'pay_rate','title'=>'总付率'],// 付费数/登陆数
            ['field'=>'consume','title'=>'成本','style'=>'color:#0882ca'],
            ['field'=>'money','title'=>'总付额',],//
        ];

        $groupField['date'] = ['field'=>'date_format','title'=>'日期','width'=>'120','fixed'=>true];
        $groupField['pid'] = ['field'=>'pid_name','title'=>'联运商','width'=>'100', 'fixed'=>true];
        $groupField['mgid'] = ['field'=>'mgid_name','title'=>'主游戏','width'=>'100', 'fixed'=>true];
        $groupField['gid'] = ['field'=>'gid_name','title'=>'游戏','width'=>'100', 'fixed'=>true];

        if(in_array('gid',$groupby)){
            array_unshift($fields,$groupField['gid']);
        }
        if(in_array('mgid',$groupby)){
            array_unshift($fields,$groupField['mgid']);
        }
        if(in_array('pid',$groupby)){
            array_unshift($fields,$groupField['pid']);
        }
        if(in_array('date',$groupby)){
            array_unshift($fields,$groupField['date']);
        }

        return $fields;
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
        if(in_array('pid',$groupby)){
            $result = $srv->getPartnerOption(['is_map'=>1])->getContent();
            $changeFor['pid'] = $result['data'];
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
        if(in_array('pid',$request['groupby'])){
            $row['pid'] = $v['pid'];
            if($v['pid']){
                $row['pid_name'] = $v['pid'].'-'.$changeFor['pid'][$v['pid']]['pname'];
            }else{
                $row['pid_name'] = '';
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

        $v['consume'] = $this->mybcdiv($v['consume'],100,false);
        $v['money'] = $this->mybcdiv($v['money'],100,false);
        $v['new_pay_money'] = $this->mybcdiv($v['new_pay_money'],100,false);
        $v['max_money'] = $this->mybcdiv($v['max_money'],100,false);

        $row['consume'] = $this->floatVal($v['consume']);
       
        $row['active_rate'] = $this->mybcdiv($v['active'],$v['click']);
        $row['reg_rate'] = $this->mybcdiv($v['reg'],$v['active']);
        $row['reg_device_rate'] = $this->mybcdiv($v['reg_device'],$v['active']);
        $row['role_create_rate'] = $this->mybcdiv($v['role_created'],$v['reg']);
        $row['new_pay_rate'] = $this->mybcdiv($v['new_pay'],$v['reg']);
        $row['click'] = $this->intVal($v['click']);
        $row['active'] = $this->intVal($v['active']);
        $row['reg'] = $this->intVal($v['reg']);
        $row['reg_device'] = $this->intVal($v['reg_device']);
        $row['new_pay'] = $this->intVal($v['new_pay']);
        $row['new_pay_money'] = $this->floatVal($v['new_pay_money']);
        $row['reg_unit_price'] = $this->mybcdiv($v['consume'],$v['reg'],false);
        $row['new_pay_unit_price'] = $this->mybcdiv($v['consume'],$v['new_pay'],false);
        $row['profit_loss'] = $this->floatVal($v['max_money']-$v['consume']);
        $row['new_reg_arpu'] = $this->mybcdiv($v['new_pay_money'],$v['reg'],false);
        $row['new_pay_arppu'] = $this->mybcdiv($v['new_pay_money'],$v['new_pay'],false);
        $row['old_user'] = $v['login_user']-$v['reg'];
        $row['arpu'] = $this->mybcdiv($v['money'],$v['reg'],false);
        $row['arppu'] = $this->mybcdiv($v['money'],$v['pay'],false);
        $row['pay'] = $this->intVal($v['pay']);
        $row['pay_rate'] = $this->mybcdiv($v['pay'],$v['login_user']);
        $row['money'] = $this->floatVal($v['money']);
        $row['max_money'] = $this->floatVal($v['max_money']);
        $row['max_roi'] = $this->mybcdiv($v['max_money'],$v['consume']) ;

        return $row;
    }

    /**
     * 汇总所有行 加法计算
     *
     * @param [type] $data
     * @param [type] $groupby
     * @return void
     */
    public function sumData($data,array $groupby)
    {
        $result = [];
        foreach($data as $row){
            foreach($row as $k=>$v){
                if(!in_array($k,$groupby)){
                    $result[$k] += $v;
                }
            }
        }
        $merge = [];
        foreach($groupby as $g){
            $merge[$g]='';
        }
        $result = array_merge($merge,$result);
        return $result;
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