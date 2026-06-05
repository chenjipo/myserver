<?php
namespace App\admin\service;

use App\admin\model\ModData;
use YXLib\foundation\Cache;
use YXLib\foundation\Debug;

class SrvData{

    private $mod;
    public function __construct(){
        $this->mod = new ModData();
    }
    /**
     * 默认的自定义字段
     *
     * @var array
     */
    public $defaultCustomCols = array(
        'cost','pay_money','new_pay_money','ad_money','new_duid','new_pay_duid','login_duid','pay_duid',/*'sign_num','unsign_num',*/
        'retain2','retain3','retain4','retain5','retain6','retain7',
    );

    public function getData($request)
    {
        $cols = [];
        $rows = [];
        
        if(empty($request['groupby'])){
            $request['groupby'] = array('ymd');
        }else{
            $request['groupby'] = explode(',',$request['groupby']);
        }
        if($request['index'] == ''){
            $request['index'] = 'retain';
        }
        if($request['cid'] == 'null'){
            $request['cid'] = '';
        }
        if($request['appid'] == 'null'){
            $request['appid'] = '';
        }

        $groupby = $request['groupby'];

        /**
         * 返回行
         */
        $rows = [];
       
        /**
         * 获取原始数据
         */
        $info = $this->mod->getData($request);

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
        // exit(json_encode($info['list']));die;
        /**
         * 汇总行处理
         */
        $sumDataOri = $this->sumData($info['list'],$groupby);
        $sumDataOri['ymd'] = '汇总';
        $sumDataRow = $this->makeRow($sumDataOri,$request);
        Debug::log($sumDataRow);


        /**
         * 每一行处理
         */
        foreach($info['list'] as $v){
            $rows[] = $this->makeRow($v,$request);
        }

        /**
         * 表头字段
         */
        $cols = $this->makeFields($groupby,$request['index']);

    

        /**
         * 合并汇总行
         */
        array_unshift($rows,$sumDataRow);
        
        return success(['cols'=>[$cols],'rows'=>$rows]);
    }

    /**
     * 自作列字段
     *
     * @param array $groupby
     * @param string $index
     * @param boolean $all
     * @return array
     */
    private function makeFields($groupby )
    {

        $allFields = [];
        $fields = [
            //激活率	注册率	新付率	点击数	激活数	注册数	新付数	注册单价	新付单价	成本	盈亏	注册AP 1	新付AP 1	首充数%	首充额%	最高者%	至今ROI
            ['field'=>'cost','title'=>'成本','width'=>'80'],
            ['field'=>'new_duid','title'=>'新增设备','width'=>'100'],
            ['field'=>'new_pay_duid','title'=>'新增付费设备','width'=>'100'],
            ['field'=>'new_pay_money','title'=>'新增付费金额','width'=>'100'],
            ['field'=>'login_duid','title'=>'活跃设备','width'=>'100'],
            ['field'=>'pay_duid','title'=>'总付费设备','width'=>'100'],
            ['field'=>'pay_money','title'=>'总付费金额','width'=>'100'],
            ['field'=>'ad_money','title'=>'广告金额','width'=>'100'],
            ['field'=>'sign_num','title'=>'签约次数','width'=>'100'],
            ['field'=>'unsign_num','title'=>'解约次数','width'=>'100'],
        ];

        $allFields['base'] = $fields;
        $level = [2,3,4,5,6,7,8,9,10,11,12,13,14,15];
        foreach($level as $l){
            $col = ['field'=>'retain'.$l, 'title'=> $l.'留'];
            $fields[] = $col;
        }
     
        $groupField['ymd'] = ['field'=>'ymd','title'=>'日期','width'=>'100','fixed'=>true];
        $groupField['appid'] = ['field'=>'appid','title'=>'应用ID','width'=>'100', 'fixed'=>true];
        $groupField['pid'] = ['field'=>'pid','title'=>'媒体','width'=>'100', 'fixed'=>true];
        $groupField['cid'] = ['field'=>'cid','title'=>'渠道','width'=>'100', 'fixed'=>true];
        $groupField['refer'] = ['field'=>'refer','title'=>'编号','width'=>'100', 'fixed'=>true];

       
        if(in_array('refer',$groupby)){
            array_unshift($fields,$groupField['refer']);
        }
        if(in_array('cid',$groupby)){
            array_unshift($fields,$groupField['cid']);
        }
        if(in_array('pid',$groupby)){
            array_unshift($fields,$groupField['pid']);
        }
        if(in_array('appid',$groupby)){
            array_unshift($fields,$groupField['appid']);
        }
        if(in_array('ymd',$groupby)){
            array_unshift($fields,$groupField['ymd']);
        }

        return $fields;
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
    public function makeRow($v,$request)
    {
        $row = [];
        if(in_array('ymd',$request['groupby'])){
            $row['ymd'] = $v['ymd'];
        }
        if(in_array('appid',$request['groupby'])){
            $row['appid'] = $v['appid'];
        }
        if(in_array('pid',$request['groupby'])){
            $row['pid'] = $v['pid'];
        }
        if(in_array('cid',$request['groupby'])){
            $row['cid'] = $v['cid'];
        }
        if(in_array('refer',$request['groupby'])){
            $row['refer'] = $v['refer'];
        }

        $row['cost'] = $this->mybcdiv($v['cost'],1,false);
        $row['pay_money'] = $this->mybcdiv($v['pay_money'],1,false);
        $row['new_pay_money'] = $this->mybcdiv($v['new_pay_money'],1,false);
        $row['ad_money'] = $this->mybcdiv($v['ad_money'],1,false);
        $row['new_duid'] = $this->intVal($v['new_duid']);
        $row['new_pay_duid'] = $this->intVal($v['new_pay_duid']);
        $row['login_duid'] = $this->intVal($v['login_duid']);
        $row['pay_duid'] = $this->intVal($v['pay_duid']);
        $row['sign_num'] = $this->intVal($v['sign_num']);
        $row['unsign_num'] = $this->intVal($v['unsign_num']);


        $todayTime = strtotime(date('Ymd'));

        if($v['ymd'] == '汇总' || !in_array('ymd',$request['groupby'])){
            $startDay = date('Ymd',strtotime($request['edate']));
        }else{
            $startDay = $v['ymd'];
        }

        $level = [2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30];
        foreach($level as $l){
            $nTime = strtotime($startDay)+($l-1)*86400;
            if($nTime>$todayTime) break;
            $row['retain'.$l] = $this->mybcdiv($v['day'.$l],$v['new_duid']);
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