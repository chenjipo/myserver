<?php

namespace App\sdk\console;

use App\common\DeviceServer;
// use App\sdk\model\User;

class TvTool
{

    public function handle($params)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $url = 'http://kytv.xyz/get.php?username=x1b7u80147v&password=14079055&type=m3u_plus&output=mpegts';
        $destination = "/tmp/playlist_demotest_plus.m3u";
        downCurl($url, $destination);
    }
}

function downCurl($url,$filePath)
{
    $headers = [
        'User-Agent:' . 'Mozilla/5.0 (Linux; U; Android 8.1.0; zh-cn; BLA-AL00 Build/HUAWEIBLA-AL00) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/57.0.2987.132 MQQBrowser/8.9 Mobile Safari/537.36'
    ];
    //初始化
    $curl = curl_init();
    //设置抓取的url
    curl_setopt($curl, CURLOPT_URL, $url);
    //打开文件描述符
    $fp = fopen ($filePath, 'w+');
    curl_setopt($curl, CURLOPT_FILE, $fp);
    //这个选项是意思是跳转，如果你访问的页面跳转到另一个页面，也会模拟访问。
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 60);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    //执行命令
    curl_exec($curl);
    //关闭URL请求
    curl_close($curl);
    //关闭文件描述符
    fclose($fp);
}