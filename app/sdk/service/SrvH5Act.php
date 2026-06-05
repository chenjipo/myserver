<?php
namespace App\sdk\service;

use App\sdk\model\User as ModelUser;
use App\common\Table;
use App\common\DeviceServer;
use App\common\LibLog;
use App\common\Queue;
use YXLib\foundation\Debug;
use YXLib\foundation\ipapi\IpSearch;
use YXLib\support\Util;

class SrvH5Act
{
    public function active($data)
    {
        //参数校验
        $nnf = [ 
            'appid', 'version', 'mac',
        ];

        foreach ($nnf as $n) { 
            if (empty($data[$n])) {
                Debug::log($n);
                return fail('必要参数不能为空');
            }
        }

        //参数补齐
        $duid = DeviceServer::androidCall('', '', $data['mac'], '');
        $ip = Util::getIp();
        $ipSearch = new IpSearch;
        $location = $ipSearch->getIpAddr($ip);
        if (empty($location)) {
            Debug::log($n);
            return fail('必要参数不能为空');
        }

        $ext = [
            'duid'       => $duid,
            'ip'         => $ip,
            'continent'  => $location['continent'],
            'country'    => $location['country'],
            'city'       => $location['city'],
            'province'   => $location['province'],
            'ymd'        => date('Ymd'),
            'h'          => date('YmdH'),
            'atime'      => time(),
        ];
        $data = array_merge($data, $ext);
        
        $data['ntype'] = urldecode($data['ntype']);
        $data['nname'] = urldecode($data['nname']);
        $data['mac']   = urldecode($data['mac']);
        $data['ymd']   = date('Ymd');
        $data['h']     = date('YmdH');
        // Queue::push('log_active',$data);

        //返回初始化结果
        $pageVersion = "1.2";
        $result = [
            'duid'     => $duid,
            'server_time' => time(),
        ];

        Debug::log($data);
        return success($result, 'success');
    }

    public function login($data)
    {
        //参数校验
        $nnf = [ 
            'appid', 'version', 'mac', 'checktoken'
        ];

        foreach ($nnf as $n) { 
            if (empty($data[$n])) {
                Debug::log($n);
                return fail('必要参数不能为空');
            }
        }

        ###验证token

        ##通过这个token获取对应的mac是否正确，仅能使用一次
        
        //参数补齐
        $duid = DeviceServer::androidCall('', '', $data['mac'], '');
        $ip = Util::getIp();
        $ipSearch = new IpSearch;
        $location = $ipSearch->getIpAddr($ip);
        if (empty($location)) {
            Debug::log($n);
            return fail('必要参数不能为空');
        }

        $ext = [
            'duid'       => $duid,
            'ip'         => $ip,
            'continent'  => $location['continent'],
            'country'    => $location['country'],
            'city'       => $location['city'],
            'province'   => $location['province'],
            'ymd'        => date('Ymd'),
            'h'          => date('YmdH'),
            'atime'      => time(),
        ];
        $data = array_merge($data, $ext);
        
        $data['ntype'] = urldecode($data['ntype']);
        $data['nname'] = urldecode($data['nname']);
        $data['mac']   = urldecode($data['mac']);
        $data['ymd']   = date('Ymd');
        $data['h']     = date('YmdH');
        // Queue::push('log_active',$data);

        //返回初始化结果
        $result = [
            'duid'     => $data['duid'],
            'uname'    => 'demotest',
            'password' => '132132132',
            'server_time' => time(),
        ];

        Debug::log($data);
        return success($result, 'success');
    }

    public function applist($data)
    {
        ###返回桌面应用
        $result = [
            ['name' => 'AppStore', 'downurl' => 'http://xxx.apk', 'icon' => 'xxx'],
        ];
        return success($result, 'success');
    }
}
