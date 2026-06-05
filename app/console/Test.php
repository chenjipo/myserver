<?php
namespace App\console;

use App\common\TTSUser;
use YXLib\foundation\ttserver\TTServer;

class Test
{

    public function handle()
    {
        $tts = new TTSUser();
        // $tts = new TTServer();
        $key = 'z3vwyeq6jad04nf78r2kgs1lix9upthc';
        $arr = ['a'=>1,'b'=>'234','new'=>234];
        $ret = $tts->addDevInfo($key,$arr);
        var_dump($ret);
        // $ret = $tts->delete($key);
        // $ret = $tts->set($key,$arr);
        // var_dump($ret);
        $ret = $tts->getDevInfo($key);
        var_dump($ret);
        return '';
    }
}