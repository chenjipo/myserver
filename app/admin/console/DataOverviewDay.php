<?php
namespace App\admin\console;

use App\common\Table;
use YXLib\foundation\ModelFactory;

class DataOverviewDay{

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

        $outPutData = array();
        $modelMain = ModelFactory::getInstance('main');

        // $table = Table::$log_login.$day;
        // $sql = "select pid,appid,cid,refer,os, `duid` as `new_duid` from ".$table." where ymd =:ymd and utype=1 group by pid,appid,cid,refer,os";
        // $newDuid = $modelMain->query($sql,['ymd'=>$day]);
        // $this->_buildData($outPutData, $newDuid, ['new_duid']);

        $table = Table::$log_login.$day;
        $sql = "select pid,appid,cid,refer,os, count(distinct `duid`) as `new_duid` from ".$table." where ymd =:ymd and install=1 group by pid,appid,cid,refer,os";
        $newDuid = $modelMain->query($sql,['ymd'=>$day]);
        $this->_buildData($outPutData, $newDuid, ['new_duid']);

        $sql = "select pid,appid,cid,refer,os,count(distinct `duid`) as `new_pay_duid`,sum(`money`) as new_pay_money from ".Table::$t_order." where ispay=1 and rdate =:rdate and ymd=:ymd group by pid,appid,cid,refer,os";
        $newPayDuid = $modelMain->query($sql,['rdate'=>$day,'ymd'=>$day]);
        $this->_buildData($outPutData, $newPayDuid, ['new_pay_duid','new_pay_money']);

        $sql = "select pid,appid,cid,refer,os,count(distinct `duid`) as `pay_duid`,sum(`money`) as pay_money from ".Table::$t_order." where ispay=1 and ymd=:ymd group by pid,appid,cid,refer,os";
        $payDuid = $modelMain->query($sql,['ymd'=>$day]);
        $this->_buildData($outPutData, $payDuid, ['pay_duid','pay_money']);

        $table = Table::$log_login.$day;
        $sql = "select pid,appid,cid,refer,os,count(distinct `duid`) as `login_duid` from ".$table." where ymd=:ymd group by pid,appid,cid,refer,os";
        $loginDuid = $modelMain->query($sql,['ymd'=>$day]);
        $this->_buildData($outPutData, $loginDuid, ['login_duid']);

        $st = strtotime($day);
        $et = $st+86400;
        $sql = "SELECT '2' as pid,'100001' as appid,'2' as cid, '2_100001_2_1' as refer,'1' as os, COUNT(`agreement_no`) as sign_num FROM ".Table::$z_agreement_sign." where sign_time>0 and atime>{$st} and atime<{$et}";
        $signData = $modelMain->query($sql);
        $this->_buildData($outPutData, $signData, ['sign_num']);

        $sql = "SELECT '2' as pid,'100001' as appid,'2' as cid, '2_100001_2_1' as refer,'1' as os,COUNT(`agreement_no`) as unsign_num FROM ".Table::$z_agreement_sign." where unsign_time>0 and atime>{$st} and atime<{$et}";
        $unSignData = $modelMain->query($sql);
        $this->_buildData($outPutData, $unSignData, ['unsign_num']);


        $this->_insDataByUniqueKey($outPutData);
        echo "ok\n";
    }


    /**
     * 组装内容
     *
     * @param $outPutData
     * @param $data
     * @param array $field
     * @return void
     */
    private function _buildData(&$outPutData, $data, $field = array())
    {
        foreach ($data as $info) {
            $key = $this->_buildKey($info);
            foreach ($field as $inputKey) {
                $outPutData[$key][$inputKey] = !empty($info[$inputKey]) ? $info[$inputKey] : 0;
            }
        }
    }

    /**
     * 插入/更新数据
     *
     * @param $outPutData
     * @return void
     */
    private function _insDataByUniqueKey($outPutData)
    {
        if (!empty($outPutData)) {
            $modelAdmin = ModelFactory::getInstance('admin');
            foreach ($outPutData as $key => $info) {
                $keyData = $this->_analyKey($key);
                $data = array_merge($keyData, $info);
                $modelAdmin->insertOrUpdate($data, $data, Table::$data_overview_day);
            }
        }
    }

    /**
     * 组装KEY
     *
     * @param $data
     * @return string
     */
    private function _buildKey($data)
    {
        return $this->runDay . "#" . $data['pid'] . "#" . $data['cid'] . "#" . $data['appid'] . "#" . $data['refer']."#".$data['os'];
    }

    /**
     * 解析key信息
     *
     * @param $key
     * @return array
     */
    private function _analyKey($key)
    {
        $keyData = array();
        list($keyData['ymd'], $keyData['pid'], $keyData['cid'], $keyData['appid'], $keyData['refer'],$keyData['os']) = explode("#", $key);
        return $keyData;
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