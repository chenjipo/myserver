<?php
namespace YXLib\foundation\sms;
/**
 * 华为云短信类
 */
class LibHuawei{

    public static function send($phone,$code){

        $config = config('sms.huawei');
        $url = 'https://smsapi.cn-north-4.myhuaweicloud.com:443/sms/batchSendSms/v1';
        $APP_KEY = $config['accessKeyId'];
        $APP_SECRET = $config['accessKeySecret'];

        //短信接收人号码,必填,全局号码格式(包含国家码),示例:+86151****6789,多个号码之间用英文逗号分隔
        $receiver = '+86'.$phone;
        //选填,短信状态报告接收地址,推荐使用域名,为空或者不填表示不接收状态报告
        $statusCallback = '';

        /**
         * 选填,使用无变量模板时请赋空值 $TEMPLATE_PARAS = '';
         * 单变量模板示例:模板内容为"您的验证码是${1}"时,$TEMPLATE_PARAS可填写为'["369751"]'
         * 双变量模板示例:模板内容为"您有${1}件快递请到${2}领取"时,$TEMPLATE_PARAS可填写为'["3","人民公园正门"]'
         * 模板中的每个变量都必须赋值，且取值不能为空
         * 查看更多模板和变量规范:产品介绍>模板和变量规范
         * @var string $TEMPLATE_PARAS
         */
        $TEMPLATE_PARAS = '["'.$code.'"]';

        //请求Headers
        $headers = [
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: WSSE realm="SDP",profile="UsernameToken",type="Appkey"',
            'X-WSSE: ' . self::buildWsseHeader($APP_KEY, $APP_SECRET)
        ];
        //请求Body
        $data = http_build_query([
            'from' => $config['sender'],
            'to' => $receiver,
            'templateId' => $config['TemplateId'],
            'templateParas' => $TEMPLATE_PARAS,
            'statusCallback' => $statusCallback,
            'signature' => $config['signature'],
        ]);

        $context_options = [
            'http' => ['method' => 'POST', 'header'=> $headers, 'content' => $data, 'ignore_errors' => true],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false] //为防止因HTTPS证书认证失败造成API调用失败，需要先忽略证书信任问题
        ];
        //打印请求信息
        //Debug::log($context_options);

        $response = file_get_contents($url, false, stream_context_create($context_options));
        //打印响应信息
        //Debug::log($response);

        $result = json_decode($response, true);
        if($result['code'] != '000000'){
            return array('state' => false,'msg' => $response['description']);
        }
        return array(
            'state' => true,
            'msg' => $result['result'],
        );
    }

    /**
     * 构造X-WSSE参数值
     * @param string $appKey
     * @param string $appSecret
     * @return string
     */
    public static function buildWsseHeader(string $appKey, string $appSecret){
        date_default_timezone_set('Asia/Shanghai');
        $now = date('Y-m-d\TH:i:s\Z'); //Created
        $nonce = uniqid(); //Nonce
        $base64 = base64_encode(hash('sha256', ($nonce . $now . $appSecret))); //PasswordDigest
        return sprintf("UsernameToken Username=\"%s\",PasswordDigest=\"%s\",Nonce=\"%s\",Created=\"%s\"", $appKey, $base64, $nonce, $now);
    }

}