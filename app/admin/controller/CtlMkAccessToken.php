<?php
namespace App\admin\controller;

use App\admin\model\ModMk;
use App\common\Game;
use App\common\Table;
use YXLib\foundation\ModelFactory;
use YXLib\support\Util;
use TencentAds\TencentAds;
use TencentAds\Exception\TencentAdsResponseException;
use TencentAds\Exception\TencentAdsSDKException;

class CtlMkAccessToken{

    public function tt()
    {
        include_once ROOT .'/app/common/library/mkting/toutiao-marketing-sdk/index.php';
        
        //授权相关
        $state = get('state');
        $auth_code = get('auth_code');
        if(!$auth_code){
            return "<div>授权失败：auth_code need!</div>";
        }
        if(!$state){
            exit("<div>授权失败：state need!</div>");
        }

        $cid = Game::defaultCid(Game::$default_toutiao_slug);
        if(!$cid){
            return "<div>系统错误#1!</div>";
        }
        $developerInfo = sy_data_read(Table::$pf_channel,$cid,['config_dev']);
        $appid = $developerInfo['config_dev']['apppid'];
        $secret = $developerInfo['config_dev']['secret'];
        if(!$appid || !$secret){
            return "<div>系统错误#2!</div>";
        }
        $userAccount = $state;
        $mod = new ModMk();
        $info = $mod->getTtMajordomoInfoByAccount($userAccount);
        if(!$info){
            return "<div>授权失败：找不到此管家账号!</div>";
        }
        
        //通过auth_code获取access_token
        $auth = new \ToutiaoSdk\ToutiaoAuth($appid, $secret);
        $ret = $auth->getAccessToken($auth_code);

        $ret = json_decode($ret,true);
        if(0 !== $ret['code']){
            return "<div>授权获取access_token失败：".json_encode($ret)."</div>";
        }
        $save = [
            'access_token'=>$ret['data']['access_token'],
            'refresh_token'=>$ret['data']['refresh_token'],
            'expires_in'=>time()+ $ret['data']['expires_in'],
            'refresh_token_expires_in'=>time()+$ret['data']['refresh_token_expires_in'],
            'invalid'=>0,
        ];

        //插入或更新数据库
        $mod->updateTtMajordomoAction($save,$info['id']);
        $ret = $mod->affectedRows();
        if($ret<0){
            return '<div>授权获取access_token失败：更新记录失败!</div>';
        }
        $mod->multiUpdateAdAccountByMajor(['auth_time'=>time()],$userAccount);
        return '<div style="color:green;font-size:20px;">授权成功，请关闭页面！</div>';
    }

    public function ks()
    {
        //授权相关
        $state = get('state');
        $auth_code = get('auth_code');
        if(!$auth_code){
            return "<div>授权失败：auth_code need!</div>";
        }
        if(!$state){
            return "<div>授权失败：state need!</div>";
        }

        $cid = Game::defaultCid(Game::$default_kuaishou_slug);
        if(!$cid){
            return "<div>系统错误#1!</div>";
        }
        $developerInfo = sy_data_read(Table::$pf_channel,$cid,['config_dev']);
        $appid = $developerInfo['config_dev']['apppid'];
        $secret = $developerInfo['config_dev']['secret'];

        $mod = new modMk();
        $info = $mod->getAdAccountByName($state);
        if(!$info){
            return "<div>授权失败：后台账号不存在!</div>";
        }
        
        $params = json_encode([
            'app_id'=> $appid,
            'secret'=> $secret,
            'auth_code'=>trim($auth_code)
        ]);
        
        $api = "https://ad.e.kuaishou.com/rest/openapi/oauth2/authorize/access_token";
        $result = Util::requestJSON($api,$params,5);
        $ret = json_decode($result['result'],true);
        if($ret['code'] === 0){
            $udpate = [
                'advertiser_id'=>$ret['data']['advertiser_id'],
                'auth_time'=>time(),
            ];
            $mod->updateAdAccountAction($udpate,$info['accid']);
            $data = [
                'access_token'=>$ret['data']['access_token'],
                'refresh_token'=>$ret['data']['refresh_token'],
                'expires_in'=>time()+$ret['data']['access_token_expires_in'],
                'refresh_token_expires_in'=>time()+$ret['data']['refresh_token_expires_in'],
                'invalid'=>0,
            ];
            $insert = $data;
            $insert['accid']=$info['accid'];
            $res = ModelFactory::getInstance()->insertOrUpdate($insert,$data,Table::$mk_ad_account_token);
            if($res){
                return "<div>授权成功，请关闭页面!</div>";
            }
        }else{
            return var_export($result,true);
        }

        return "<div>授权失败，请联系技术!</div>";
    }

    public function gdt()
    {
        //授权相关
        $state = get('state');
        $auth_code = get('authorization_code');
        if(!$auth_code){
            return "<div>授权失败：authorization_code need!</div>";
        }
        if(!$state){
            return "<div>授权失败：state need!</div>";
        }
        $cid = Game::defaultCid(Game::$default_gdt_slug);
        if(!$cid){
            return "<div>系统错误#1!</div>";
        }
        $developerInfo = sy_data_read(Table::$pf_channel,$cid,['config_dev']);
        $appid = $developerInfo['config_dev']['apppid'];
        $secret = $developerInfo['config_dev']['secret'];
        $redirect_uri = $developerInfo['config_dev']['redirect_uri'];

        $mod = new ModMk();
        $info = $mod->getAdAccountByName($state);
        if(!$info){
            return "<div>授权失败：后台账号不存在!</div>";
        }
        require_once  ROOT . '/app/common/library/mkting/gdt/vendor/autoload.php';
        
        try {
            /* @var TencentAds $tads */
            $tads = TencentAds::init([]);
            $token = $tads->oauth()
                          ->token([
                              'client_id'          => $appid,
                              'client_secret'      => $secret,
                              'grant_type'         => 'authorization_code',
                              'authorization_code' => $auth_code,
                              'redirect_uri'       => $redirect_uri,
                          ]);

            // 从返回里获得AccessToken并设置到$tads中
            $tokenValue = $token->getAccessToken();

            $data = [
                'access_token'=>$tokenValue,
                'invalid'=>0,
            ];
            $insert = $data;
            $insert['accid']=$info['accid'];
            ModelFactory::getInstance()->insertOrUpdate($insert,$data,Table::$mk_ad_account_token);

            $udpate = [
                'auth_time'=>time()
            ];
            $mod->updateAdAccountAction($udpate,$info['accid']);
            return '<div style="color:green;font-size:20px;">授权成功，请关闭页面！</div>';


            // $tads->setAccessToken($token->getAccessToken());
            // echo 'Access token expires in: ' . $token->getAccessTokenExpiresIn() . PHP_EOL;
            // echo 'Refresh token: ' . $token->getRefreshToken() . PHP_EOL;
            // echo 'Refresh token expires in: ' . $token->getRefreshTokenExpiresIn() . PHP_EOL;
        } catch (TencentAdsResponseException $e) {
            // When Api returns an error
            echo 'Tencent ads returned an error: ' . $e->getMessage() . PHP_EOL;
            throw $e;
        } catch (TencentAdsSDKException $e) {
            // When validation fails or other local issues
            echo 'Tencent ads SDK returned an error: ' . $e->getMessage() . PHP_EOL;
            throw $e;
        } catch (\Exception $e) {
            echo 'Other exception: ' . $e->getMessage() . PHP_EOL;
            throw $e;
        }
    }
}   