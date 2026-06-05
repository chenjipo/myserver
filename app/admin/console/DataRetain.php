<?php
namespace App\admin\console;

use App\common\Table;
use YXLib\foundation\ModelFactory;

class DataRetain{

    public $runDay = "";
    public $runTimeAry = [];
    public $isdel = false;

    public function handle($v)
    {
        $this->initParams($v);
        if (!empty($this->runTimeAry)) {
            foreach ($this->runTimeAry as $day) {
                echo $day . "\n";
                $this->runDay = $day;
                $this->_stat($day);
            }
        }
    }

    public function _stat($day){
        // `new_duid` int(11) NOT NULL DEFAULT '0' COMMENT '新设备',
        // `new_pay_duid` int(11) NOT NULL DEFAULT '0' COMMENT '新付费设备',
        // `new_pay_money` int(11) NOT NULL DEFAULT '0' COMMENT '新付费金额',
        // `login_duid` int(11) NOT NULL DEFAULT '0' COMMENT '登录设备',
        // `pay_duid` int(11) NOT NULL DEFAULT '0' COMMENT '付费设备',
        // `pay_money` int(11) NOT NULL DEFAULT '0' COMMENT '付费金额',

        $modelMain = ModelFactory::getInstance('main');

        $table = Table::$log_login.$day;


        $sql = "select pid,appid,cid,refer,os,rdate,ymd as ldate,count(distinct duid) AS retain_num from ".$table." where ymd =:ymd group by pid,appid,cid,refer,os,rdate";
        $duidData = $modelMain->query($sql,['ymd'=>$day]);

        $rsData = array();
        if (!empty($duidData) && is_array($duidData)) {
            foreach ($duidData as $info) {
                $uk = $this->_buildUniqueKey($info);
                if (empty($rsData[$uk])) {
                    $rsData[$uk] = $info;
                }
                $rsData[$uk]['retain_duid_num'] = $info['retain_num'];
            }
        }
      
        if (!empty($rsData)) {
            $this->_insDataByUniqueKey($rsData);
        }
        echo "ok\n";
    }

    private function _buildUniqueKey($data)
    {
        $key = implode("#", array($data['ldate'], $data['rdate'], $data['pid'], $data['appid'], $data['cid'], $data['refer'],$data['os']));
        return $key;
    }

    public function dateDiffDays($date1,$date2)
    {
        $u1 = strtotime($date1);
        $u2 = strtotime($date2);
        
        return abs(($u1-$u2)/(3600*24));
    }

    /**
     * 插入/更新数据
     *
     * @param $outPutData
     * @return void
     */
    private function _insDataByUniqueKey($outPutData)
    {
        $modelAdmin = ModelFactory::getInstance('admin');
        if (!empty($outPutData)) {
            foreach ($outPutData as $info) {
                $insData = array(
                    'pid' => $info['pid'],
                    'appid' => $info['appid'],
                    'cid' => $info['cid'],
                    'refer' => $info['refer'],
                    'os' => $info['os'],
                    'retain_duid_num' => !empty($info['retain_duid_num']) ? $info['retain_duid_num'] : 0,
                    'ymd' => $info['rdate'],
                    'ldate' => $info['ldate'],
                    'retain_day_num' => $this->dateDiffDays($info['rdate'], $info['ldate']) + 1,

                );
                $modelAdmin->insertOrUpdate($insData, $insData, Table::$data_retain);
            }
        }
    }
   
    //初始化需要统计的日期数组
    private function initParams($v){
        $sYmd = date("Ymd");//默认
        $eYmd = date("Ymd");//默认
        if(date("H") == '00'){//如果是0点，则把昨天的数据也统计一次
            $sYmd = date("Ymd", strtotime("-1 days"));            
        }
        if($v['sdate'] && $v['edate']){//自定义时间范围
            $sYmd = $v['sdate'];
            $eYmd = $v['edate'];
        }
        $earDay = '20230216';//投放数据从20230216开始
        if(strtotime($sYmd) < strtotime($earDay)){
            $sYmd = $earDay;
        }
        $ymd = $sYmd;
        while ($ymd <= $eYmd) {
            $this->runTimeAry[] = $ymd;
            $ymd = date("Ymd", strtotime("$ymd +1 days"));
            if(count($this->runTimeAry)>1500){
                die("warning : while times is more 1500,plase check the param\n");
            }
        }
    }
    
}