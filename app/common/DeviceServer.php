<?php
namespace App\common;

use YXLib\foundation\redis\ClsRedis;

class DeviceServer extends ClsRedis
{

    public $name = 'store';

    CONST DEV = 'd:%s'; // dev

    static function hashDevKey( $dev )
	{
		return strtolower(sprintf(self::DEV, $dev));
	} 

    public function getDevInfo($dev)
    {
        $hashKey = self::hashDevKey($dev);
        return $this->get($hashKey);
    }

    public function addDevInfo($dev,$info)
    {
        $hashKey = self::hashDevKey($dev);
        return $this->setNx($hashKey,$info);
    }

    public function setDevInfo($dev,$info)
    {
        $hashKey = self::hashDevKey($dev);
        return $this->set($hashKey,$info);
    }

    /**
     * 安卓
     * 设备号唯一标识计算规则
     *
     * @param  $ <type> $imei  设备标识码
     * @param  $ <type> $oaid  安卓联盟应用内唯一码
     * @param  $ <type> $mac  设备MAC地址
     * @param  $ <type> $androidId  安卓ID
     * @param  $ <type> $idfa  广告标示符( IOS )
     * @param  $ <type> $client_id  客户端生成的唯一 安卓iosh5wx
     * @return <type>
     */
    public static function androidCall($imei = '', $oaid = '', $mac = '', $androidId = '', $client_id = '' )
    {
        $key = '($IJGJLLg*-2Hev)';//不能修改 固定
        $device_id = '';
        do {
            
            if ($mac && $mac != '02:00:00:00:00:00' && $mac != '00:00:00:00:00:00') {
                $device_id = $mac;
                break;
            }
        
            if ($client_id) {
                $device_id = $client_id;
            }
        } while (false);
        $dev = strtolower(md5($device_id . $key));
        return $dev;
    }

    

}
