<?php
namespace App\admin\service;

use App\admin\model\ModMkDayReport;
use YXLib\foundation\Cache;
use YXLib\foundation\Debug;

class SrvMkDayReport{

    private $mod;
    public function __construct(){
        $this->mod = new ModMkDayReport();
    }

    /**
     * 翻译字段
     *
     * @var array
     */
    public $changeForNameArr = array('date_format','mgid_name','gid_name','cid_name','pitid_name','accid_name');


    /**
     * 设置客户自定义列
     *
     * @param [type] $cols
     * @return void
     */
    public function setCustomCols($cols){
        $cols = is_array($cols)?$cols:explode(',',$cols);
        $key = $this->getMcColsKey();
        Cache::getInstance('default')->set($key,json_encode($cols));
        return success([],'success');
    }

    /**
     * 获取哦客户自定义列
     *
     * @return array
     */
    public function getCustomCols()
    {
        $customCols = [];
        $key = $this->getMcColsKey();
        $ret = Cache::getInstance('default')->get($key);
        if($ret){
            $customCols = json_decode($ret,true); 
        }else{
            $customCols = $this->defaultCustomCols;
        }
        $list = $this->makeFields([],'',true);
        foreach ($list as $key => $index) {
            foreach ($index as $k => $v) {
                if(in_array($v['field'],$customCols)){
                    $list[$key][$k]['checked'] = true;
                }else{
                    $list[$key][$k]['checked'] = '';
                }
            }
        }

        return success($list);
    }

    /**
     * 默认的自定义字段
     *
     * @var array
     */
    public $defaultCustomCols = array(
        'active_rate','reg_rate','reg_device_rate','role_create_rate','new_pay_rate','click','active','reg','reg_device','new_pay','new_pay_money','reg_unit_price','new_pay_unit_price','high_quality_price','consume','profit_loss','new_reg_arpu','new_pay_arppu','old_user','arpu','arppu','pay','pay_rate','money','max_money','max_roi',
        'roi1','roi2','roi3','roi4','roi5','roi6','roi7',
        'ltv1','ltv2','ltv3','ltv4','ltv5','ltv6','ltv7',
        'retain2','retain3','retain4','retain5','retain6','retain7',
        'pay_retain2','pay_retain3','pay_retain4','pay_retain5','pay_retain6','pay_retain7',
    );

    public function getData($request)
    {
        $cols = [];
        $rows = [];
        
        if(empty($request['groupby'])){
            $request['groupby'] = array('date','mgid');
        }else{
            $request['groupby'] = explode(',',$request['groupby']);
        }
        if($request['has_cost'] == 'undefined'){
            $request['has_cost'] = 0;
        }
        if($request['index'] == ''){
            $request['index'] = 'roi';
        }
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
        $sumDataRow = $this->makeRow($sumDataOri,$request,$changeFor);

        $sumDataRow['date_format'] = '汇总';

        /**
         * 带综合 样式
         */
        $diff =(strtotime($request['edate']) - strtotime($request['sdate']))/86400;
        if($diff >= 1 && 2<=count($groupby) && in_array('date',$groupby)){
            /**
             * 去除日期维度
             * 用维度作为key，分开数组
             */
            $otherGroupCols = array_diff($groupby,array('date'));
            $listOnGroup = [];
            foreach($info['list'] as $v){
                $otherGroupColKey = [];
                foreach ($otherGroupCols as $groupCol) {
                    $otherGroupColKey[] = $v[$groupCol];
                }
                $otherGroupColKey = implode('|',$otherGroupColKey);
                $listOnGroup[$otherGroupColKey][] = $v;
            }

            /**
             * 先制作合计行，在制作合计行下面的每一行
             */
            $sumRowOnGroups = [];
            $rowsOnGroups = [];
            foreach($listOnGroup as $otherGroupColKey => $lists){
                $sumDataOri = $this->sumData($lists,$groupby);
                //补回维度字段值
                $fix = [];
                $otherGroupColKeys = explode('|',$otherGroupColKey);
                $i = 0;
                foreach ($otherGroupCols as $groupCol) {
                    $fix[$groupCol] = $otherGroupColKeys[$i];
                    $i++;
                }
                $sumDataOri = array_merge($sumDataOri,$fix);//覆盖
                $otherGroupColKey = '__'.$otherGroupColKey;//坑，必须字符串，防止排序后丢失数组key
                //制作合计行
                $sumRowOnGroups[$otherGroupColKey] = $this->makeRow($sumDataOri,$request,$changeFor);
                foreach($lists as $row){
                    $rowsOnGroups[$otherGroupColKey][] = $this->makeRow($row,$request,$changeFor);
                }
            }
            //先把汇总行排序按成本排序
            $sortCost= array_column($sumRowOnGroups,'consume');
            array_multisort($sortCost, SORT_DESC, $sumRowOnGroups);
            //把每个主游戏下的子行排序 按日期排序 默认就是

            //综合行合并子行
            foreach($sumRowOnGroups as $otherGroupColKey => $sumRow){
                $sumRow['综合'] = true;//标记此行为综合行
                if(count($rowsOnGroups[$otherGroupColKey])>1){
                    $rows[] = $sumRow;
                }
                $rows = array_merge($rows,$rowsOnGroups[$otherGroupColKey]);
            }

        }else{
            /**
             * 每一行处理
             */
            foreach($info['list'] as $v){
                $rows[] = $this->makeRow($v,$request,$changeFor);
            }

            /**
             * 按成本排序
             */
            $rows = $this->sortRows($rows,$request);
        }

        
        /**
         * 表头字段
         */
        $cols = $this->makeFields($groupby,$request['index']);

        /**
         * 只显示客户自定义字段
         */
        $customCols = $customRows = [];
        $key = $this->getMcColsKey();
        $customColsMc = Cache::getInstance('default')->get($key);
        if(!$customColsMc) {
            $customColsMc = $this->defaultCustomCols;
        }else{
            $customColsMc = json_decode($customColsMc,true);
        }

        foreach ($cols as $v) {
            if(in_array($v['field'],$customColsMc) || in_array($v['field'],$groupby) || in_array($v['field'],$this->changeForNameArr)){
                $customCols[] = $v;
            }
        }
        foreach ($rows as $i=>$row) {
            $cusRow = [];
            /**
             * 补综合
             */
            if($row['综合']){
                $row['date_format'] = '综合';
            }
            foreach ($row as $c => $v) {
                if(in_array($c,$customColsMc) || in_array($c,$groupby) || in_array($c,$this->changeForNameArr)){
                    $cusRow[$c] = $v;
                }
            }
            $customRows[] = $cusRow;
        }

        /**
         * 合并汇总行
         */
        array_unshift($customRows,$sumDataRow);
        
        return success(['cols'=>[$customCols],'rows'=>$customRows]);
    }

     /**
     * 获取自定义列缓存key
     *
     * @return void
     */
    public function getMcColsKey()
    {
        return md5(SrvAuth::$adminId.'MDDAYREPORTCUSTOMCOLS');
    }

    /**
     * 排序最后的结果
     *
     * 以下四种情况按注册排序 1、维度没选日期、2、区间选按日：日期跨度只有一天、3、区间选按周：时间跨度只有一周，4、区间选按月：时间跨度在一个月内
     * @param array $rows
     * @param array $request
     * @return array
     */
    public function sortRows($rows,$request)
    {
        $groupby = $request['groupby'];
        $sortCost = array_column($rows, 'consume');
        $sortDate = array_column($rows,'date');
        $sortMainGame = array_column($rows,'mgid');
        $sortGame = array_column($rows,'gid');

        if(2 == count($groupby) && in_array('date',$groupby) && $request['edate'] == $request['sdate']){
            array_multisort( $sortCost, SORT_DESC, $rows);
        }elseif(1 == count($groupby) && in_array('date',$groupby)){
            array_multisort( $sortDate, SORT_ASC, $rows);
        }else{
            if(in_array('mgid',$groupby)){
                array_multisort($sortMainGame, SORT_DESC,  $sortCost, SORT_DESC, $rows);
            }elseif(in_array('gid',$groupby)){
                array_multisort($sortGame, SORT_DESC,  $sortCost, SORT_DESC, $rows);
            }
            else{
                array_multisort($sortCost, SORT_DESC, $rows);
            }
        }

        return $rows;
    }

    /**
     * 自作列字段
     *
     * @param array $groupby
     * @param string $index
     * @param boolean $all
     * @return array
     */
    private function makeFields($groupby ,$index = '', $all = false)
    {

        $allFields = [];
        $fields = [
            //激活率	注册率	新付率	点击数	激活数	注册数	新付数	注册单价	新付单价	成本	盈亏	注册AP 1	新付AP 1	首充数%	首充额%	最高者%	至今ROI
            ['field'=>'consume','title'=>'成本','width'=>'80','style'=>'color:#0882ca'],
            ['field'=>'reg_device','title'=>'注册设备'],
            ['field'=>'reg_device_rate','title'=>'设备注册率','width'=>'80'],
            ['field'=>'reg','title'=>'注册数'],
            ['field'=>'reg_rate','title'=>'注册率'],
            ['field'=>'role_create_rate','title'=>'创角率'],
            ['field'=>'click','title'=>'点击数'],
            ['field'=>'active','title'=>'激活数'],
            ['field'=>'active_rate','title'=>'激活率'],
            ['field'=>'reg_unit_price','title'=>'注册价','style'=>'color:#0fa555'],

            ['field'=>'new_pay','title'=>'新付数'],
            ['field'=>'new_pay_money','title'=>'新付额'],
            ['field'=>'new_pay_rate','title'=>'新付率'],//new_pay/reg
            ['field'=>'new_pay_unit_price','title'=>'新付价'],

            ['field'=>'old_user','title'=>'老用户'],//登陆用户数-注册用户数

            ['field'=>'profit_loss','title'=>'盈亏'],
            ['field'=>'new_reg_arpu','title'=>'新增ARPU','width'=>'80'],//首日新用户付费总额 / 首日新增注册数			
            ['field'=>'new_pay_arppu','title'=>'新增ARPPU','width'=>'80'],//首日新用户付费总额 / 首日新付费人数			
            ['field'=>'arpu','title'=>'ARPU'],//即ARPU，期内新用户付费总额 / 期内新注册用户数
            ['field'=>'arppu','title'=>'ARPPU'],//即ARPPU，期内新用户付费总额 / 期内新付费用户数
            ['field'=>'pay','title'=>'总付数'],//付费数
            ['field'=>'pay_rate','title'=>'总付率'],// 付费数/登陆数
            ['field'=>'money','title'=>'总付额',],//
            ['field'=>'max_money','title'=>'至今付额',],//
            ['field'=>'max_roi','title'=>'至今ROI',],//
        ];

        $allFields['base'] = $fields;
        if(in_array($index,['roi','beilv','increase']) || $all){
            $level = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45];
            foreach($level as $l){
                $col = ['field'=>'roi'.$l, 'title'=> 'roi'.$l];
                $fields[] = $col;
                if($all){
                    $allFields['roi'][] = $col;
                }
            }
        }
        if('ltv' == $index || $all){
            $level = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45];
            foreach($level as $l){
                $col = ['field'=>'roi'.$l, 'title'=> 'ltv'.$l];
                $fields[] = $col;
                if($all){
                    $allFields['ltv'][] = $col;
                }
            }
        }
        if('retain' == $index || $all){
            $level = [2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45];
            foreach($level as $l){
                $col = ['field'=>'retain'.$l, 'title'=> $l.'留'];
                $fields[] = $col;
                if($all){
                    $allFields['retain'][] = $col;
                }
            }
        }
        if('pay_retain' == $index || $all){
            $level = [2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45];
            foreach($level as $l){
                $col = ['field'=>'pay_retain'.$l, 'title'=> $l.'留'];
                $fields[] = $col;
                if($all){
                    $allFields['pay_retain'][] = $col;
                }
            }
        }

        if($all){
            return $allFields;
        }

        $groupField['date'] = ['field'=>'date_format','title'=>'日期','width'=>'100','fixed'=>true];
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

        if('roi' == $request['index']){
            $level = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45];
            foreach($level as $l){
                if(isset($v['day'.$l])){
                    $money = $this->mybcdiv($v['day'.$l],100,false);
                    $row['roi'.$l] = $this->mybcdiv($money,$v['consume']) ;
                }
            }
        }
        if('beilv' == $request['index']){
            $money1 = $this->mybcdiv($v['day1'],100,false);
            $row['day1'] = $this->mybcdiv($money1,$v['consume']);
            $level = [2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45];
            foreach($level as $l){
                if(isset($v['day'.$l])){
                    $row['roi'.$l] = $this->mybcdiv($v['day'.$l],$row['day1'],false);
                }
            }
        }
        //roi变化值
        if('increase' == $request['index']){
            $level = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45];
            $increase = ['day0'=>0];
            foreach($level as $l){
                if(isset($v['day'.$l])){
                    $money = $this->mybcdiv($v['day'.$l],100,false);
                    $increase['day'.$l] = str_replace('%','',$this->mybcdiv($money,$v['consume']) );
                    $row['roi'.$l] = ($increase['day'.$l] - $increase['day'.($l-1)]).'%';
                }
            }
        }
        
        if('ltv' == $request['index']){
            $level = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45];
            foreach($level as $l){
                if(isset($v['day'.$l])){
                    $money = $this->mybcdiv($v['day'.$l],100,false);
                    $row['roi'.$l] = $this->mybcdiv($money,$v['reg'],false);
                }
            }
        }
        if('retain' == $request['index']){
            $level = [2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45];
            foreach($level as $l){
                if(isset($v['day'.$l])){
                    $row['retain'.$l] = $this->mybcdiv($v['day'.$l],$v['reg']);
                }
            }
        }
        if('pay_retain' == $request['index']){
            $level = [2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45];
            foreach($level as $l){
                if(isset($v['day'.$l])){
                    $row['pay_retain'.$l] = $this->mybcdiv($v['day'.$l],$v['new_pay']);
                }
            }
        }
        return $row;
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