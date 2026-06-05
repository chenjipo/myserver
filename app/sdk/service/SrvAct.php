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

class SrvAct
{
	public function active($data)
	{
		//参数校验
		$nnf = [
			'appid', 'version', 'mac',
		];

		foreach ($nnf as $n) {
			if(empty($data[$n])) {
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

		$refer = 'stb';
		$ext = [
			'duid'       => $duid,
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
		$data['mac']   = strtoupper(urldecode($data['mac']));
		$data['ymd']   = date('Ymd');
		$data['h']     = date('YmdH');

		//强更新包
		$checkPkgUp = [
			'uurl'     => '',
			'utype'    => 0,//1可关闭更新，2强制更新
			'uct'      => '',
			'uversion' => '1.0.1'
		]; 

		if ($data['appid'] == 100003 && version_compare($data['version'], '1.0.12', '<')) { 
			$checkPkgUp = [
				'uurl'     => 'https://ustv.s3-accelerate.amazonaws.com/applist/slinglauncher/SlingLauncher_v1.0.12.apk',
				'utype'    => 2,//1可关闭更新，2强制更新
				'uct'      => 'Update',
				'uversion' => '1.0.12'
			];
		}

		//if ($data['mac'] == 'E076D0C7CF6E' && $data['appid'] == 100003 && version_compare($data['version'], '1.0.12', '<')) { 
		//     $checkPkgUp = [
		//         'uurl'     => 'https://ustv.s3.us-west-1.amazonaws.com/applist/slinglauncher/SlingLauncher_v1.0.12.apk',
		//         'utype'    => 2,//1可关闭更新，2强制更新
		//         'uct'      => 'Update',
		//         'uversion' => '1.0.12'
		//     ];
		// }

		if ($data['mac'] == 'E076D0C7CF6E' && $data['appid'] == 100007 && version_compare($data['version'], '1.0.16', '<')) { 
			$checkPkgUp = [
				'uurl'     => 'https://ustv.s3-accelerate.amazonaws.com/applist/MyTv/MyTv_1.0.16_v2.apk',
				'utype'    => 2,//1可关闭更新，2强制更新
				'uct'      => 'Update',
				'uversion' => '1.0.16'
			];
		}

		 if ($data['appid'] == 100007 && version_compare($data['version'], '1.0.16', '<')) { 
			$checkPkgUp = [
				// 'uurl'     => 'https://ustv.s3-accelerate.amazonaws.com/applist/MyTv/MyTv_1.0.3_v2.apk',
				'uurl'     => 'https://ustv.s3-accelerate.amazonaws.com/applist/MyTv/MyTv_1.0.16_v2.apk',
				'utype'    => 2,//1可关闭更新，2强制更新
				'uct'      => 'Update',
				'uversion' => '1.0.16'
			];
		}

		// if ($data['mac'] == '4A13E4F97975' && $data['appid'] == 100007 && version_compare($data['version'], '1.0.9', '<')) { 
		//     $checkPkgUp = [
		//         'uurl'     => 'https://ustv.s3-accelerate.amazonaws.com/applist/MyTv/MyTv_1.0.9_v2.apk',
		//         'utype'    => 2,//1可关闭更新，2强制更新
		//         'uct'      => 'Update',
		//         'uversion' => '1.0.9'
		//     ];
		// }

		// if ($data['appid'] == 100003 && version_compare($data['version'], '1.0.2', '<')) {
		//     $checkPkgUp = [
		//         'uurl'     => 'https://ustv.s3.us-west-1.amazonaws.com/applist/slinglauncher/SlingLauncher_v1.0.2.apk',
		//         'utype'    => 2,//1可关闭更新，2强制更新
		//         'uct'      => 'Update',
		//         'uversion' => '1.0.2'
		//     ];
		// }

		if ($data['appid'] == 100001 && version_compare($data['version'], '1.0.6','<')) {
			$checkPkgUp = [
				'uurl'     => 'https://ustv.s3-accelerate.amazonaws.com/applist/appstore/AppStore_1.0.6.apk',
				'utype'    => 2,//1可关闭更新，2强制更新
				'uct'      => 'Update',
				'uversion' => '1.0.6'
			];
		}

		//if ($data['mac'] == 'E076D0C7CF6E' && $data['appid'] == 100001 && version_compare($data['version'], '1.0.6','<')) {
		//	$checkPkgUp = [
		//		'uurl'     => 'https://ustv.s3-accelerate.amazonaws.com/applist/appstore/AppStore_1.0.6.apk',
		//		'utype'    => 2,//1可关闭更新，2强制更新
		//		'uct'      => 'Update',
		//		'uversion' => '1.0.6'
		//	];
		//}

		if ($data['mac'] == 'E076D0C7CF6E' && $data['appid'] == 100003 && version_compare($data['version'], '1.0.11','<')) {
			$checkPkgUp = [
				'uurl'     => 'https://ustv.s3-accelerate.amazonaws.com/applist/slinglauncher/SlingLauncher_v1.0.11.apk',
				'utype'    => 2,//1可关闭更新，2强制更新
				'uct'      => 'Update',
				'uversion' => '1.0.11'
			];
		}

		 if ($data['appid'] == 100008 && version_compare($data['version'], '1.0.2','<')) {
			$checkPkgUp = [
				'uurl'     => 'https://ustv.s3-accelerate.amazonaws.com/applist/MyTv/My18Tv_1.0.2_v2.apk',
				'utype'    => 2,//1可关闭更新，2强制更新
				'uct'      => 'Update',
				'uversion' => '1.0.2'
			];
		}

		// if ($data['appid'] == 100007 && version_compare($data['version'], '1.0.3','<')) {
		//     $checkPkgUp = [
		//         'uurl'     => 'https://ustv.s3-accelerate.amazonaws.com/applist/mytv/my-tv-v1.0.2.apk',
		//         'utype'    => 2,//1可关闭更新，2强制更新
		//         'uct'      => 'Update',
		//         'uversion' => '1.0.2'
		//     ];
		// }
		
		###获取邀请码
		$invite = $duid;
		$appConfig = sy_data_read(Table::$pf_app, $data['appid']);
		


		 ##获取redis
		$sdkCache = new RedisStore('sdk');
		$cacheKey = sprintf("xlplayer:sdk:mac#%s_%s_count_%s", 'all', 'share', $duid);
		$shareNum = $sdkCache->get($cacheKey);
		$shareNum = !empty($shareNum) ? $shareNum : 0;

		$shareSign = Sign::generatenew(array('appid' => $data['appid'], 'invite' => $invite, 'limit_num' => 2, 'sharemum' => $shareNum), $appConfig['appkey']);

		//返回初始化结果
		$result = [
			'duid'     => $duid,
			'server_time' => time(),
			'ad_image' => array(
				'https://ustv.s3.us-west-1.amazonaws.com/ad/1.jpeg',
				'https://ustv.s3.us-west-1.amazonaws.com/ad/2.jpg',
				'https://ustv.s3.us-west-1.amazonaws.com/ad/3.png',
				// 'https://ustv.s3.us-west-1.amazonaws.com/ad/4.jpg',
			), ###先写死
			'ad_downoload_url' => array(
				"1",
				"2",
				"3",
				// "4"
			),  ###先写死
			'is_cn'    => 0, //默认
			'is_black' => 0,
			'is_white' => 0,
			'switch'   => 0,//开启切H5
			'uurl'     => $checkPkgUp['uurl'],//apk更新包地址
			'utype'    => $checkPkgUp['utype'], //pkgver
			'uct'      => $checkPkgUp['uct'], //更新说明
			'uversion' => $checkPkgUp['uversion'], //更新版本号
			// 'shareUrl' => "http://sdk.linkstv.xyz/sdk/rgo?invite={$invite}&appid={$data['appid']}&sign={$shareSign}",
			'shareUrl' => "http://linkstv.xyz/ldy/index.html?invite={$invite}&appid={$data['appid']}&sign={$shareSign}&sharemum={$shareNum}&limit_num=2",
		];

		if ($data['appid'] == 100003) {
			// $result['ad_msg'] = 'SlingTv Box exclusive Black Friday special discount — $50 off one unit, one time only!';
			$result['ad_msg'] = "WhatsApp customer service number 1(646)-702-7901";
			$result['wa_num'] = "+1(646)702-7901";
			$result['em_addr'] = "support@slingtvbox.shop";
			$result['web_addr'] = "https://slingtvbox.shop";
			$result['wa_pic'] = "";	
		}


		// if ($data['mac'] == 'E076D0C6ABF4') {
			$result['shareUrl'] = "http://linkstv.xyz/ldy1/index.html?invite={$invite}&appid={$data['appid']}&sign={$shareSign}&sharemum={$shareNum}&limit_num=2";
		// }

		do {

			if ($location['country'] == '中国' && !in_array($location['province'], ['香港', '台湾', '澳门'])) {
				$result['is_cn'] = 1;
				$result['is_black'] = 1;
			}

			$model = ModelFactory::getInstance('sdk');
			$arr = $model->commonGetOne('x_mac', 'mac', $data['mac']);
			if (empty($arr)) {
				$result['is_black'] = 1;
				break;
			}

			if ($arr['status'] != 2) {
				if ($arr['status'] == 0) {
					###黑名单设备
					$result['is_black'] = 0;
					break;
				}
			} else {
				$result['is_white'] = 1;
				$result['is_black'] = 0;
			}

			$mac_id = $arr['id'];
			$result['switch'] = 1;
			###生成checktoken
			// $result['show_url'] = "http://linkstv.xyz/#/?appid=100006&version=1.0.1&mac={$data['mac']}&duid={$duid}&checktoken=123456";

			if (in_array($data['appid'], array(100007, 100008))) {
				###凌晨会更新菜单文件，2点前还是读取老文件就可以了
				if (date('H') < '02') {
					$ver = date("Ymd", time() - 86400);
				} else {
					$ver = date("Ymd");
				}
				$result['tv_json'] = 'https://file.zxcftech.site/json/1/tv_' . strtotime($ver) . '.json';
				if (in_array($data['mac'], ['E076D0C5EB21', '4A13E4F97975', 'E076D0C40CE7'])) {
					// $result['tv_json'] = 'https://linkstv.xyz/json/1/tv_' . strtotime($ver) . '_test.json';
				}
				###返回账号信息
				$map = $model->commonGetOne('x_mac_user_map', 'macid', $mac_id);
				if (empty($map)) {
					$sdkQueue = new RedisQueue('sdk');
					while (true) {
						$json = $sdkQueue->pop('pre_xuser');
						if ($json) {
							$userInfo = json_decode($json, true);//[]
							if (!empty($userInfo['id'])) {
								$userGet = $model->commonGetOne('x_mac_user_map', 'userid', $userInfo['id']);
								if (!empty($userGet)) {
									continue;
								}
								$result['uname']    = $userInfo['uname'];
								$result['password'] = $userInfo['upwd'];
								$model->ignoreInsert(array('macid' => $mac_id, 'userid' => $userInfo['id'], 'atime' => time()), false, 'x_mac_user_map');
								$data['is_conuse_user'] = 1;
								$data['uid'] = $userInfo['id'];
							}
						}
						break;
					}
					
				} else {
					$userInfo = $model->commonGetOne('x_user', 'id', $map['userid']);
					if (!empty($userInfo)) {
						$result['uname']    = $userInfo['uname'];
						$result['password'] = $userInfo['upwd'];
						$data['uid'] = $map['userid'];
					}
				}
			}

		} while (false);

		if ($result['is_black'] === 1) {
			$data['set_black_mac'] = 1;
		}
		Queue::push('log_active', $data); 
		Debug::log($data);
		return success($result, 'success');
	}

	public function applist($data)
	{
		###返回桌面应用
		// $result = [
		//     ['name' => 'hdotv', 'downurl' => 'https://ustv.s3-accelerate.amazonaws.com/applist/hdotv/hdotv-1.1.4.apk', 'icon' => 'https://ustv.s3-accelerate.amazonaws.com/applist/hdotv/tv.png', 'bid' => 'com.tv.hdobox'],
		//     ['name' => 'kknet', 'downurl' => 'https://ustv.s3-accelerate.amazonaws.com/applist/kknet/com.kknet.term.apk', 'icon' => 'https://ustv.s3-accelerate.amazonaws.com/applist/kknet/ic_launcher.png', 'bid' => 'com.kknet.term'],
		//     ['name' => 'lingsvideo', 'downurl' => 'https://ustv.s3-accelerate.amazonaws.com/applist/lingsvideo/LingsVideo_1.0.2_2024032716_debug.apk', 'icon' => 'https://ustv.s3-accelerate.amazonaws.com/applist/lingsvideo/Video.png', 'bid' => 'com.dt.lingsvideo'],
		//     ['name' => 'mytv', 'downurl' => 'https://ustv.s3-accelerate.amazonaws.com/applist/mytv/my-tv-v1.0.2.apk', 'icon' => 'https://ustv.s3-accelerate.amazonaws.com/applist/mytv/tv.png', 'bid' => 'com.lizongying.mytv'],
		//     ['name' => 'appstore', 'downurl' => 'https://ustv.s3-accelerate.amazonaws.com/applist/appstore/AppStore_1.0.3.apk', 'icon' => 'https://ustv.s3-accelerate.amazonaws.com/applist/appstore/ic_logo.png', 'bid' => 'com.dt.appstore'],
		// ];

		$result = [
			['name' => 'MyPlayer', 'downurl' => 'https://ustv.s3-accelerate.amazonaws.com/applist/MyPlayer/MyPlayer_110.apk', 'icon' => 'https://ustv.s3-accelerate.amazonaws.com/applist/MyPlayer/ic_launcher.png', 'bid' => 'com.drama.simpleplayer'],
			// ['name' => 'Player', 'downurl' => 'https://ustv.s3-accelerate.amazonaws.com/applist/Player/player_v2.apk', 'icon' => 'https://ustv.s3-accelerate.amazonaws.com/applist/Player/ic_launcher.png', 'bid' => 'com.mediaon.apt'],
			['name' => 'appstore', 'downurl' => 'https://ustv.s3-accelerate.amazonaws.com/applist/appstore/AppStore_1.0.5.apk', 'icon' => 'https://ustv.s3-accelerate.amazonaws.com/applist/appstore/ic_logo.png', 'bid' => 'com.dt.appstore'],
			['name' => 'MyTv', 'downurl' => 'https://ustv.s3-accelerate.amazonaws.com/applist/MyTv/MyTv_1.0.16_v2.apk', 'icon' => 'https://ustv.s3-accelerate.amazonaws.com/applist/MyTv/logo.png', 'bid' => 'com.dt.slingtv'],
			['name' => 'MyTv-Adult', 'downurl' => 'https://ustv.s3-accelerate.amazonaws.com/applist/MyTv/My18Tv_1.0.1_v2.apk', 'icon' => 'https://ustv.s3-accelerate.amazonaws.com/applist/MyTv/logo2.png', 'bid' => 'com.dt.my18tv'],
			// ['name' => 'MyVod', 'downurl' => 'https://ustv.s3-accelerate.amazonaws.com/applist/Movie/MyVod_203.apk', 'icon' => 'https://ustv.s3-accelerate.amazonaws.com/applist/Movie/ic_launcher.png', 'bid' => 'com.tv.hdobox'],
			['name' => 'Onstream', 'downurl' => 'https://ustv.s3-accelerate.amazonaws.com/applist/Onstream/onstream.apk', 'icon' => 'https://ustv.s3-accelerate.amazonaws.com/applist/Onstream/logo.png', 'bid' => 'com.maertsno.tv'],
			['name' => 'BeeTv', 'downurl' => 'https://ustv.s3-accelerate.amazonaws.com/applist/BeeTv/BeeTV.apk', 'icon' => 'https://ustv.s3-accelerate.amazonaws.com/applist/BeeTv/logo.png', 'bid' => 'com.bweather.forecast'],
			// ['name' => 'DisneyNow', 'downurl' => 'https://ustv.s3-accelerate.amazonaws.com/applist/Disney/disneynow-10-42-0-100.apk', 'icon' => 'https://ustv.s3-accelerate.amazonaws.com/applist/Disney/logo.png', 'bid' => 'com.disney.datg.videoplatforms.android.watchdc'],
			['name' => 'Facebook', 'downurl' => 'https://ustv.s3-accelerate.amazonaws.com/applist/facebook/facebook.apk', 'icon' => 'https://ustv.s3-accelerate.amazonaws.com/applist/facebook/logo.png', 'bid' => 'com.facebook.katana'],
			['name' => 'Kodi', 'downurl' => 'https://ustv.s3-accelerate.amazonaws.com/applist/kodi/kodi.apk', 'icon' => 'https://ustv.s3-accelerate.amazonaws.com/applist/kodi/logo.png', 'bid' => 'org.xbmc.kodi'],
			// ['name' => 'Netfix', 'downurl' => 'https://ustv.s3-accelerate.amazonaws.com/applist/Netfix/netflix-8-120-0-build-10-50712.apk', 'icon' => 'https://ustv.s3-accelerate.amazonaws.com/applist/Netfix/netflix.png', 'bid' => 'com.netflix.mediaclient'],
			['name' => 'Stremio', 'downurl' => 'https://ustv.s3-accelerate.amazonaws.com/applist/Stremio/Stremio.apk', 'icon' => 'https://ustv.s3-accelerate.amazonaws.com/applist/Stremio/logo.png', 'bid' => 'com.stremio.one'],
			['name' => 'Syncler', 'downurl' => 'https://ustv.s3-accelerate.amazonaws.com/applist/Syncler/Syncler.apk', 'icon' => 'https://ustv.s3-accelerate.amazonaws.com/applist/Syncler/logo.png', 'bid' => 'com.syncler'],
			// ['name' => 'VivaTv', 'downurl' => 'https://ustv.s3-accelerate.amazonaws.com/applist/ViviTv/VivaTV.apk', 'icon' => 'https://ustv.s3-accelerate.amazonaws.com/applist/ViviTv/logo.png', 'bid' => 'com.allsaversocial.gl'],
			['name' => 'youtube', 'downurl' => 'https://ustv.s3-accelerate.amazonaws.com/applist/youtube/youtube.apk', 'icon' => 'https://ustv.s3-accelerate.amazonaws.com/applist/youtube/logo.png', 'bid' => 'com.google.android.youtube.tv'],
			['name' => 'ScreenRecord', 'downurl' => 'https://ustv.s3-accelerate.amazonaws.com/applist/ScreenRecord/screen-record.apk', 'icon' => 'https://ustv.s3-accelerate.amazonaws.com/applist/ScreenRecord/ic_launcher.png', 'bid' => 'com.kimcy929.screenrecorder'],
			['name' => 'shadowsocksr', 'downurl' => 'https://ustv.s3-accelerate.amazonaws.com/applist/shadowsocksr/shadowsocksr.apk', 'icon' => 'https://ustv.s3-accelerate.amazonaws.com/applist/shadowsocksr/logo.png', 'bid' => 'com.bige0.shadowsocksr'],
			['name' => 'v2rayNG', 'downurl' => 'https://ustv.s3-accelerate.amazonaws.com/applist/v2rayNG/v2rayNG_1.8.19.apk', 'icon' => 'https://ustv.s3-accelerate.amazonaws.com/applist/v2rayNG/logo.png', 'bid' => 'com.v2ray.ang'],
			['name' => 'NordVPN', 'downurl' => 'https://ustv.s3-accelerate.amazonaws.com/applist/NordVPN/NordVPN.apk', 'icon' => 'https://ustv.s3-accelerate.amazonaws.com/applist/NordVPN/logo.png', 'bid' => 'com.nordvpn.android'],
			// ['name' => 'CinemaHD', 'downurl' => 'https://ustv.s3-accelerate.amazonaws.com/applist/CinemaHD/CinemaHD.apk', 'icon' => 'https://ustv.s3-accelerate.amazonaws.com/applist/CinemaHD/logo.png', 'bid' => 'com.yoku.marumovie'],
			['name' => 'FilmPlus', 'downurl' => 'https://ustv.s3-accelerate.amazonaws.com/applist/FilmPlus/FilmPlus.apk', 'icon' => 'https://ustv.s3-accelerate.amazonaws.com/applist/FilmPlus/logo.png', 'bid' => 'com.guideplus.co'],
			['name' => 'Surfshark', 'downurl' => 'https://ustv.s3-accelerate.amazonaws.com/applist/Surfshark/Surfshark.apk', 'icon' => 'https://ustv.s3-accelerate.amazonaws.com/applist/Surfshark/logo.png', 'bid' => 'com.surfshark.vpnclient.android']
		];
		return success($result, 'success');
	}

	public function share($data)
	{
		//参数校验
		$nnf = [
			'appid', 'duid', 'mac',
		];

		foreach ($nnf as $n) {
			if(empty($data[$n])) {
				Debug::log($n);
				return fail('必要参数不能为空');
			}
		}

		##获取redis
		$sdkCache = new RedisStore('sdk');
		$cacheKey = sprintf("xlplayer:sdk:mac#%s_%s_count_%s", 'all', 'share', $data['duid']);
		$shareNum = $sdkCache->get($cacheKey);
		$result['sharenum'] = !empty($shareNum) ? $shareNum : 0;
		Debug::log($data);
		return success($result, 'success');
	}

	public function rgo($data)
	{
		//参数校验
		$nnf = [
			'appid', 'invite',
		];

		foreach ($nnf as $n) {
			if(empty($data[$n])) {
				Debug::log($n);
				return fail('必要参数不能为空');
			}
		}

		$ip = Util::getIp();
		$model = new ModelVote();
		$model->relation($ip, $data['invite'], 'share', 'all');
		Debug::log($data);

		// $jumUrl = "https://slingtvbox.shop/products/slingtv-box-s5-max-hot-";
		$jumUrl = "https://slingtvbox.com/products/slingtv-box-s5-max-hot-";
		header("Location: {$jumUrl}");
		exit;
		echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><meta property="og:title" content="123"/><meta property="og:image" content="http://image.llmaitengff.com/data/upload/gwpaylist/20240718/af31e5d3e2e54539afc77dee8bf3aa15.jpg"/><meta name="twitter:image" content="https://image.llmaitengff.com/data/upload/gwpaylist/20240718/af31e5d3e2e54539afc77dee8bf3aa15.jpg" /></head><body><iframe src="https://slingtvbox.com/products/slingtv-box-s5-max-hot-" width="100%" height="100%" frameborder="0" style="border: none;" sandbox="allow-same-origin allow-forms"></iframe></body>';
		// echo '<iframe src="https://m.youdao.com/" sandbox="allow-same-origin allow-forms" seamless width="280" height="653" frameborder="0" name="youdaoFrame"></iframe>';
		exit;
	}

	public function runtv($data = [])
	{
		echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><meta property="og:title" content="123"/><meta property="og:image" content="http://image.llmaitengff.com/data/upload/gwpaylist/20240718/af31e5d3e2e54539afc77dee8bf3aa15.jpg"/><meta name="twitter:image" content="http://image.llmaitengff.com/data/upload/gwpaylist/20240718/af31e5d3e2e54539afc77dee8bf3aa15.jpg" /></head><body><iframe src="https://slingtvbox.com/" width="100%" height="100%" frameborder="0" style="border: none;" sandbox="allow-same-origin allow-forms"></iframe></body>';
		// echo '<iframe src="https://m.youdao.com/" sandbox="allow-same-origin allow-forms" seamless width="280" height="653" frameborder="0" name="youdaoFrame"></iframe>';
		exit;
	}


	// public function crash($data)
	// {
	//     //参数校验
	//     $nnf = [
	//         'appid', 'crash', 'mac', 'duid'
	//     ];

	//     foreach ($nnf as $n) {
	//         if(empty($data[$n])) {
	//             Debug::log($n);
	//             return fail('必要参数不能为空');
	//         }
	//     }

	//     //参数补齐
	//     $ip = Util::getIp();
	//     $ipSearch = new IpSearch;
	//     $location = $ipSearch->getIpAddr($ip);
	//     if (empty($location)) {
	//         Debug::log($n);
	//         return fail('必要参数不能为空');
	//     }

	//     $refer = 'stb';
	//     $ext = [
	//         'duid'       => $data['duid'],
	//         'ip'         => $ip,
	//         'refer'      => $refer,
	//         'continent'  => $location['continent'],
	//         'country'    => $location['country'],
	//         'city'       => $location['city'],
	//         'province'   => $location['province'],
	//         'ymd'        => date('Ymd'),
	//         'h'          => date('YmdH'),
	//         'atime'      => time(),
	//     ];
	//     $data = array_merge($data, $ext);
		
	//     $data['ntype'] = urldecode($data['ntype']);
	//     $data['nname'] = urldecode($data['nname']);
	//     $data['crash'] = urldecode($data['crash']);
	//     $data['mac']   = strtoupper(urldecode($data['mac']));
	//     $data['ymd']   = date('Ymd');
	//     $data['h']     = date('YmdH');

	//     Queue::push('log_crash', $data); 
	//     Debug::log($data);
	//     return success($result, 'success');
	// }
}
