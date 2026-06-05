<?php

namespace YXLib\foundation\sms;

/**
 * 阿里云短信类
 */
include ROOT .'/lib/foundation/sms/dayusms/aliyun-php-sdk-core/Config.php';
include_once ROOT .'/lib/foundation/sms/dayusms/Dysmsapi/Request/V20170525/SendSmsRequest.php';
include_once ROOT .'/lib/foundation/sms/dayusms/Dysmsapi/Request/V20170525/QuerySendDetailsRequest.php';

class LibAliyun{

    public static function send($type,$phone,$code){

        $config = config('sms.huawei');
        $accessKeyId = $config['accessKeyId'];
        $accessKeySecret = $config['accessKeySecret'];
        //短信API产品名
        $product = "Dysmsapi";
        //短信API产品域名
        $domain = "dysmsapi.aliyuncs.com";
        //暂时不支持多Region
        $region = "cn-hangzhou";

        //初始化访问的acsCleint
        $profile = \DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);
        \DefaultProfile::addEndpoint("cn-hangzhou", "cn-hangzhou", $product, $domain);
        $acsClient= new \DefaultAcsClient($profile);

        $request = new \Dysmsapi\Request\V20170525\SendSmsRequest;
        //必填-短信接收号码
        $request->setPhoneNumbers($phone);
        //必填-短信签名
        $request->setSignName($config['SignName']);
        //必填-短信模板Code
        $request->setTemplateCode($config['Template'][$type]);
        //选填-假如模板中存在变量需要替换则为必填(JSON格式)
        $request->setTemplateParam("{\"code\":\"{$code}\"}");
        //选填-发送短信流水号
        $request->setOutId("");

        //发起访问请求
        $acsResponse = (array) $acsClient->getAcsResponse($request);
        //该死的阿里大鱼sdk，竟然改我的时区，垃圾sdk
        date_default_timezone_set('PRC');
        if($acsResponse['Code'] != 'OK'){
            return array('state' => false,'msg' => $acsResponse['Message']);
        }
        return array(
            'state' => true,
            'msg' => $acsResponse['BizId'],
        );
    }

}