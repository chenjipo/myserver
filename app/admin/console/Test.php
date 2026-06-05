<?php
namespace App\admin\console;

use App\common\SdkHelper;
use App\common\Sign;
use YXLib\foundation\Response;

class Test{

    

    public function handle()
    {
        // $sdkHelper = new SdkHelper;
        // $result = $sdkHelper->unBanUser(['13','24']);
        // var_dump($result->getContent());


        $data = [
            'amt' => '8',
            'game_ext' => '"100012"',
            'game_order_no' => '220802091314763263353263',
            'goods_name' => '首充',
            'role_id' => '82825542',
            'role_level' => '305',
            'role_name' => '超级战甲',
            'server_id' => '720320',
            'server_name' => '降魔320区',
            'pid' => '2',
            'gid' => '100012',
            'mid' => '10000',
            'p_mid' => '1',
            'sdk_ver' => '1.0.4',
            'client' => 'h5',
            'client_id' => 'wx2022-7-21-16-53-45-2231975',
            'device_id' => '777c10e81137443c67f101f46edc4b44',
            'access_token' => '353f7UcxABqGebUITQJBgjcYE6wqp7olYnpIZCWg6ipSV8qaWVv_a89XQQhjYM6n7cXGwr-0SMV1YnGRqwZ65v8uc6bDBb6n003t6rz54lDN6zR_oq3Dv4WgQwXe776pEfq7B8g63CErEeJtW6L8c1zrIGYIXowHKTNK3B8JHNUd5dnT9AXR4ypX7lC1dyqWHtfYPEu5eYFN_KkPMmtceWbs7Bgy3sSdeLM0Bt_jrfZsLfnVWOYmR6XDp1ZUSP3L8v2iLj_gOJYfF1oC402b5gVZQY9CbFXyGF6XD3v-FNQ',
            'tm' => '1659402795',
            'partner_pay_data' => '{"system":"ios"}'
        ];

        echo $this->h5Sign($data);
    }

    public function h5Sign($arrParam,$arrUnSign=[])
    {
        ksort($arrParam);
        reset($arrParam);
        $strBeforeSign = '';
        foreach ($arrParam as $key => $value) {
            if (in_array($key , array_merge($arrUnSign , ['sign_type' , 'strSign' , 'sign' ,'s']))) {
                continue;
            }
            if (is_null($value) || $value === '') {
                continue;
            }
            $strBeforeSign .= $key . '=' . $value . '&';
        }
        //如果存在转义字符，那么去掉转义
        if ( get_magic_quotes_gpc() ) {
            $strBeforeSign = stripslashes($strBeforeSign);
        }
        return md5($strBeforeSign);
    }

}