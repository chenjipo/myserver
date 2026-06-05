<?php
namespace App\admin\service;

use App\admin\model\ModOptLtvReport;
use YXLib\foundation\Debug;

class SrvOptLtvReport {

    public $mod;

    public function __construct()
    {
        $this->mod = new ModOptLtvReport();
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

        /**
         * 返回行
         */
        $rows = [];
        /**
         * 汇总维度字段 翻译数组
         */

        $info = $this->mod->getData($request);
        
        /**
         * 汇总行处理
         */
        $sumDataOri = $this->sumData($info['list'],['date']);
        $sumDataRow = $this->makeRow($sumDataOri,$request);

        $sumDataRow['date_format'] = '汇总';

        /**
         * 每一行处理
         */
        foreach($info['list'] as $v){
            $rows[] = $this->makeRow($v,$request);
        }
        /**
         * 排序
         */
        $sorttime = array_column($rows,'sorttime');
        array_multisort( $sorttime, SORT_DESC, $rows);

        /**
         * 合并汇总行
         */
        array_unshift($rows,$sumDataRow);

        /**
         * 表头字段
         */
        $cols = $this->makeFields();

        return success(['cols'=>[$cols],'rows'=>$rows]);
    }

    /**
     * 自作列字段
     *
     * @return array
     */
    private function makeFields()
    {

        $fields = [
            ['field'=>'date_format','title'=>'日期','width'=>120],
            // ['field'=>'reg','title'=>'注册数','width'=>80],
            ['field'=>'reg_unit_price','title'=>'注册价','width'=>80],
            ['field'=>'max_ltv','title'=>'至今LTV','width'=>80],
        ];
        foreach([1,2,3,4,5,6,7,15,30,45] as $l){
            $col = ['field'=>'ltv'.$l, 'title'=> 'ltv'.$l,'minWidth'=>70];
            $fields[] = $col;
        }
        return $fields;
    }
    /**
     * 制作行数据返回
     *
     * @param [type] $v 每行原始数据
     * @return void
     */
    public function makeRow($v,$request = [])
    {
        $row = [];
        $row['date'] = $v['date'];
        $format_date = $this->getDayOfWeek($v['date'],date('N',strtotime($v['date'])));
        $row['date_format'] = $format_date[0];
        
        $v['max_money'] = $this->mybcdiv($v['max_money'],100,false);
        $v['consume'] = $this->mybcdiv($v['consume'],100,false);

        $row['max_ltv'] = $this->mybcdiv($v['max_money'],$v['reg'],false);
        $row['reg'] = $this->intVal($v['reg']);
        $row['reg_unit_price'] = $this->mybcdiv($v['consume'],$v['reg'],false);

        // 处理显示字段
        $nowDate = date("Y-m-d");
        if(!$v['date']){//汇总行情况
            $row['sorttime'] = 0;
            if($request['edate']>$nowDate){
                $endDate = $nowDate;
            }else{
                $endDate = $request['edate'];
            }
        }else{
            $row['sorttime'] = strtotime($v['date']);
            $endDate = $v['date'];
        }
        $differ = (strtotime($nowDate)-strtotime($endDate))/86400+1;//最后的日期到今天的天数
        foreach(range(1,45) as $l){
            if($l>$differ){
                $row['ltv'.$l] = '';
            }else{
                $money = $this->mybcdiv($v['day'.$l],100,false);
                $row['ltv'.$l] = $this->mybcdiv($money,$v['reg'],false);
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