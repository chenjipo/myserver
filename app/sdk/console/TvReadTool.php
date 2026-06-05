<?php

namespace App\sdk\console;

use App\common\DeviceServer;
// use App\sdk\model\User;

class TvReadTool
{

    public function handle($params)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $title = 'TV数据更新通知';
        $config = config('dingding');
        $api = $config['admin'];

        $destination = "/tmp/playlist_demotest_plus.m3u";
        $str = file_get_contents($destination);
        preg_match_all("/#EXTINF:(.*?)\.ts/s", $str, $matches);

        ###组装数据
        $newArr = [];
        foreach ($matches[0] as $key => $value) {
            preg_match("/tvg-name=\"(.*?)\"/", $value, $arr1);
            preg_match("/tvg-logo=\"(.*?)\"/", $value, $arr2);
            preg_match("/group-title=\"(.*?)\"/", $value, $arr3);
            preg_match("/http?:\/\/[^\s]*\.ts/s", $value, $arr4);
            if (!empty($arr4[0])) {
                $newArr[] = [
                    'group' => $arr3[1],
                    'logo'  => $arr2[1],
                    'name'  => "",
                    'title' => $arr1[1],
                    'uris' => [
                         str_replace(array("demotest", "132132132"), array("__UNAME__", "__UPWD__"), $arr4[0]),
                    ],
                ];
                $jsonString = json_encode($newArr);
                // file_put_contents("/www/site/json/1/tv_public.json", $jsonString);
                file_put_contents("/www/site/json/1/tv_" . strtotime(date("Ymd")) . ".json", $jsonString);
            } else {
                // preg_match("/\/\/?:\/[^\s]*\.ts/s", $value, $arr4);
                // // var_dump($arr4);
                // // exit;
                // $newArr[] = [
                //     'group' => $arr3[1],
                //     'logo'  => $arr2[1],
                //     'name'  => "",
                //     'title' => $arr1[1],
                //     'uris' => [
                //          str_replace(array("//:", "demotest", "132132132"), array("http://31.43.191.59:80", "__UNAME__", "__UPWD__"), $arr4[0]),
                //     ],
                // ];
                // $jsonString = file_get_contents("/www/site/json/1/tv_public.json");
                // file_put_contents("/www/site/json/1/tv_" . strtotime(date("Ymd")) . ".json", $jsonString);
            }
        }

        if (empty($newArr)) {
            $jsonString = file_get_contents("/www/site/json/1/tv_public.json");
            file_put_contents("/www/site/json/1/tv_" . strtotime(date("Ymd")) . ".json", $jsonString);
            $message = date('Y-m-d') . "日数据更新失败,爬取接口数据格式有变,未匹配到数据";
            $res = dd_nitoce($api, $title, $message);
        } else {

            $fsize = filesize("/www/site/json/1/tv_" . strtotime(date("Ymd")) . ".json");
            if ($fsize > 3093968) {
                $jsonString = file_get_contents("/www/site/json/1/tv_" . strtotime(date("Ymd")) . ".json");
                file_put_contents("/www/site/json/1/tv_public.json", $jsonString);
            } else {
                $jsonString = file_get_contents("/www/site/json/1/tv_public.json");
                file_put_contents("/www/site/json/1/tv_" . strtotime(date("Ymd")) . ".json", $jsonString);
            }
            $message = date('Y-m-d') . "数据已更新";
            $res = dd_nitoce($api, $title, $message);
        }
    }
}