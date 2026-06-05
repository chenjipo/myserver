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
use YXLib\foundation\ModelFactory;
use YXLib\foundation\queue\RedisQueue;
use App\common\SdkQueue;
use YXLib\foundation\cache\RedisStore;
use App\common\Sign;
use App\sdk\model\Vote as ModelVote;

class SrvCommon
{
    public function event($data)
    {
        //参数校验
        $nnf = [
            'uuid', 'event'
        ];

        foreach ($nnf as $n) {
            if(empty($data[$n])) {
                Debug::log($n);
                return fail('必要参数不能为空');
            }
        } 

        //参数补齐
        $duid = DeviceServer::androidCall('', '', $data['uuid'], '');
        $ip = Util::getIp();
        $ipSearch = new IpSearch;
        $location = $ipSearch->getIpAddr($ip);
        if (empty($location)) {
            Debug::log($n);
            return fail('必要参数不能为空');
        }

        $refer = 'stb';
        $ext = [
            'duid'       => $duid,
            'ip'         => $ip,
            'refer'      => $refer,
            'continent'  => $location['continent'],
            'country'    => $location['country'],
            'city'       => $location['city'],
            'province'   => $location['province'],
            'atime'      => time(),
        ];
        $data = array_merge($data, $ext);
        $data['ymd']   = date('Ymd', $data['atime']);
        $data['h']     = date('YmdH', $data['atime']);

        Queue::push('log_event', $data); 
        Debug::log($data);
        return success($result, 'success');
    }

    public function runtv($data = [])
    {
        echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><meta property="og:title" content="123"/><meta property="og:image" content="http://image.llmaitengff.com/data/upload/gwpaylist/20240718/af31e5d3e2e54539afc77dee8bf3aa15.jpg"/><meta name="twitter:image" content="http://image.llmaitengff.com/data/upload/gwpaylist/20240718/af31e5d3e2e54539afc77dee8bf3aa15.jpg" /></head><body><iframe src="https://slingtvbox.com/" width="100%" height="100%" frameborder="0" style="border: none;" sandbox="allow-same-origin allow-forms"></iframe></body>';
        // echo '<iframe src="https://m.youdao.com/" sandbox="allow-same-origin allow-forms" seamless width="280" height="653" frameborder="0" name="youdaoFrame"></iframe>';
        exit;
    }

    public function crash($data)
    {
        //参数校验
        $nnf = [
            'appid', 'crash', 'mac', 'duid'
        ];

        foreach ($nnf as $n) {
            if(empty($data[$n])) {
                Debug::log($n);
                return fail('必要参数不能为空');
            }
        }

        //参数补齐
        $ip = Util::getIp();
        $ipSearch = new IpSearch;
        $location = $ipSearch->getIpAddr($ip);
        if (empty($location)) {
            Debug::log($n);
            return fail('必要参数不能为空');
        }

        $refer = 'stb';
        $ext = [
            'duid'       => $data['duid'],
            'ip'         => $ip,
            'refer'      => $refer,
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
        $data['crash'] = urldecode($data['crash']);
        $data['mac']   = strtoupper(urldecode($data['mac']));
        $data['ymd']   = date('Ymd');
        $data['h']     = date('YmdH');

        Queue::push('log_crash', $data); 
        Debug::log($data);
        return success($result, 'success');
    }

}
