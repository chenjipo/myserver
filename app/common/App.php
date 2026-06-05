<?php
namespace App\common;


class App
{

    public static function parseSdkRefer($refer = '', $appid = 100001, $cid = 1, $no = 1)
    {
        $arr = array('appid' => $appid, 'cid' => $cid, 'no' => $no);
        @list($arr['gid'], $arr['cid'], $arr['no']) = explode('_', $refer);
        $arr = array_map('intval', $arr);
        if (!$arr['cid']) $arr['cid'] = $cid;
        if (!$arr['no']) $arr['no'] = $no;
        $arr['refer'] = $refer;
        return $arr;
    }
    

}
