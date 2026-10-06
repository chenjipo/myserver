<?php

namespace App\common;

use phpDocumentor\Reflection\DocBlock\Tags\Var_;

Class TvTool
{
    private $cpUrlAction = array();
    private $userInfo = array();
    private $reqHost;
    private $sessionId;
    public $errcode = 0;
    private $fpath;

    public function __construct()
    {
        $conf = $this->confOfshumaCp();
        $format = '/www/runtime/cookie.txt';
        $this->fpath = sprintf($format, $cpName);
        $this->reqHost = $conf['host'];
        $this->cpUrlAction = $conf['action'];
        $this->userInfo = $conf['userInfo'];
        $this->getUserToken();
    }

    /**
     * 数码精灵cp的配置信息
     *
     * author lyd
     * @return array
     */
    private function confOfshumaCp()
    {
        return array(
            'host' => 'http://kytv.xyz', //后台地址
            'action' => array(
                'loginindex' => 'HckqYJZU/dashboard',
                'loginin' => 'HckqYJZU/login',
                'index' => 'HckqYJZU/dashboard',
                'notloginindex' => 'HckqYJZU/login?referrer=dashboard',
                'addUser' => 'HckqYJZU/post.php?action=line',
                'userList' => 'HckqYJZU/table',
                'xfUser' => 'HckqYJZU/post.php?action=line',
                'userinfo' => 'HckqYJZU/api?action=dashboard',
                // 'handleemail' => 'mail/system_pass'
            ),
            'userInfo' => array( //后台账号密码
                'uname'    => 'dsds94882',
                'password' => 'qwzu159753'
            )
        );
    }

    /**
     * 按页拉取账号列表（单页）
     * @return array|false ['list'=>[], 'recordsTotal'=>int]
     */
    public function fetchUserListPage($page = 1, $pageSize = 1000)
    {
        $cookieId = $this->getCooike();
        $headers = array(
             'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
             'Accept-Encoding: gzip, deflate, br, zstd',
             'Accept-Language: zh-CN,zh;q=0.9',
             'Proxy-Connection:keep-alive',
             'Host: kytv.xyz',
             "Cookie: theme=0; PHPSESSID={$cookieId};",
             'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
             'Referer:http://kytv.xyz/HckqYJZU/lines?order=0&dir=desc&entries=500',
             'X-Requested-With: XMLHttpRequest'
        );
        $rows = max(1, (int)$pageSize);
        $page = max(1, (int)$page);
        $start = ($page - 1) * $rows;
        $demoStr = "columns%5B0%5D%5Bdata%5D=0&columns%5B0%5D%5Bname%5D=&columns%5B0%5D%5Bsearchable%5D=true&columns%5B0%5D%5Borderable%5D=true&columns%5B0%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B0%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B1%5D%5Bdata%5D=1&columns%5B1%5D%5Bname%5D=&columns%5B1%5D%5Bsearchable%5D=true&columns%5B1%5D%5Borderable%5D=true&columns%5B1%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B1%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B2%5D%5Bdata%5D=2&columns%5B2%5D%5Bname%5D=&columns%5B2%5D%5Bsearchable%5D=true&columns%5B2%5D%5Borderable%5D=true&columns%5B2%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B2%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B3%5D%5Bdata%5D=3&columns%5B3%5D%5Bname%5D=&columns%5B3%5D%5Bsearchable%5D=true&columns%5B3%5D%5Borderable%5D=true&columns%5B3%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B3%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B4%5D%5Bdata%5D=4&columns%5B4%5D%5Bname%5D=&columns%5B4%5D%5Bsearchable%5D=true&columns%5B4%5D%5Borderable%5D=true&columns%5B4%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B4%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B5%5D%5Bdata%5D=5&columns%5B5%5D%5Bname%5D=&columns%5B5%5D%5Bsearchable%5D=true&columns%5B5%5D%5Borderable%5D=false&columns%5B5%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B5%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B6%5D%5Bdata%5D=6&columns%5B6%5D%5Bname%5D=&columns%5B6%5D%5Bsearchable%5D=true&columns%5B6%5D%5Borderable%5D=true&columns%5B6%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B6%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B7%5D%5Bdata%5D=7&columns%5B7%5D%5Bname%5D=&columns%5B7%5D%5Bsearchable%5D=true&columns%5B7%5D%5Borderable%5D=false&columns%5B7%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B7%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B8%5D%5Bdata%5D=8&columns%5B8%5D%5Bname%5D=&columns%5B8%5D%5Bsearchable%5D=true&columns%5B8%5D%5Borderable%5D=true&columns%5B8%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B8%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B9%5D%5Bdata%5D=9&columns%5B9%5D%5Bname%5D=&columns%5B9%5D%5Bsearchable%5D=true&columns%5B9%5D%5Borderable%5D=true&columns%5B9%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B9%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B10%5D%5Bdata%5D=10&columns%5B10%5D%5Bname%5D=&columns%5B10%5D%5Bsearchable%5D=true&columns%5B10%5D%5Borderable%5D=true&columns%5B10%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B10%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B11%5D%5Bdata%5D=11&columns%5B11%5D%5Bname%5D=&columns%5B11%5D%5Bsearchable%5D=true&columns%5B11%5D%5Borderable%5D=false&columns%5B11%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B11%5D%5Bsearch%5D%5Bregex%5D=false&order%5B0%5D%5Bcolumn%5D=0&order%5B0%5D%5Bdir%5D=desc&search%5Bvalue%5D=&search%5Bregex%5D=false&id=lines&filter=1&reseller=";
        parse_str($demoStr, $demoArr);

        $microtime = microtime(true);
        $param = $demoArr;
        $milliseconds = round($microtime * 1000);
        $param['draw'] = $page;
        $param['start'] = $start;
        $param['length'] = $rows;
        $param['_'] = $milliseconds;

        $res = $this->sendRequestByGet($this->getUrl('userList'), $param, $headers);
        if ($res['response_code'] !== 200) {
            return false;
        }
        $responseData = json_decode($res['output'], true);
        if (!is_array($responseData)) {
            return false;
        }
        $insArr = [];
        if (!empty($responseData['data'])) {
            foreach ($responseData['data'] as $uinfo) {
                $insData = [];
                preg_match("/>(.*?)<\/a>/", $uinfo[0], $xxx);
                $insData['uid'] = isset($xxx[1]) ? $xxx[1] : '';
                preg_match("/>(.*?)<\/a>/", $uinfo[1], $xxx);
                $insData['uname'] = isset($xxx[1]) ? $xxx[1] : '';
                $insData['upwd'] = $uinfo[2];
                preg_match("/>(.*?)<\/a>/", $uinfo[3], $xxx);
                $insData['parentname'] = isset($xxx[1]) ? $xxx[1] : '';
                $insData['ystatus'] = 0;
                preg_match("/title=\"(.*?)\"/", $uinfo[4], $xxx);
                if (!empty($xxx[1]) && $xxx[1] == 'Active') {
                    $insData['ystatus'] = 1;
                }
                $insData['yexpired'] = '';
                if (preg_match("/(.*?)<br\\/?><small class=['\"]text-secondary['\"]>(.*?)<\\/small>/i", $uinfo[9], $xxx)) {
                    $insData['yexpired'] = trim($xxx[1] . ' ' . $xxx[2]);
                }
                $insData['lastonline'] = '';
                if (preg_match("/(.*?)<br\\/?><small class=['\"]text-secondary['\"]>(.*?)<\\/small>/i", $uinfo[10], $xxx)) {
                    $insData['lastonline'] = trim($xxx[1] . ' ' . $xxx[2]);
                }
                $insData['is_online'] = 0;
                if (preg_match('/\btext-success\b/', $uinfo[5])) {
                    $insData['is_online'] = 1;
                }
                $insArr[] = $insData;
            }
        }
        return [
            'list' => $insArr,
            'recordsTotal' => isset($responseData['recordsTotal']) ? (int)$responseData['recordsTotal'] : count($insArr),
        ];
    }

    /**
     * 按 recordsTotal 自动翻页拉全量账号
     */
    public function getAllUserList($pageSize = 1000)
    {
        $pageSize = max(1, (int)$pageSize);
        $maxPages = 100;
        $all = [];
        $page = 1;
        $recordsTotal = null;
        while ($page <= $maxPages) {
            $pageResult = $this->fetchUserListPage($page, $pageSize);
            if ($pageResult === false) {
                break;
            }
            if ($recordsTotal === null) {
                $recordsTotal = (int)$pageResult['recordsTotal'];
            }
            if (!empty($pageResult['list'])) {
                foreach ($pageResult['list'] as $row) {
                    $all[] = $row;
                }
            } else {
                break;
            }
            if ($page * $pageSize >= $recordsTotal) {
                break;
            }
            $page++;
        }
        return [
            'list' => $all,
            'recordsTotal' => $recordsTotal === null ? count($all) : $recordsTotal,
            'pages' => $page,
        ];
    }

    public function getUserList($page = 1)
    {
        $pageResult = $this->fetchUserListPage($page, 1000);
        if ($pageResult === false) {
            return [];
        }
        return $pageResult['list'];
    }

    public function xfUser($newData)
    {
        $cookieId = $this->getCooike();
        $url = $this->getUrl('xfUser');
        $headers = array(
         'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
         'Accept-Encoding: gzip, deflate, br, zstd',
         'Accept-Language: zh-CN,zh;q=0.9',
         'Proxy-Connection:keep-alive',
         "Cookie: theme=0; PHPSESSID={$cookieId};",
         'Host: kytv.xyz',
         'Sec-Fetch-Dest: document',
         'Sec-Fetch-Mode: navigate',
         'Sec-Fetch-Site: none',
         'Sec-Fetch-User: ?1',
         'Upgrade-Insecure-Requests: 1',
         'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
         'sec-ch-ua: "Google Chrome";v="125", "Chromium";v="125", "Not.A/Brand";v="24"',
         'sec-ch-ua-mobile: ?0',
         'sec-ch-ua-platform: "Windows"'
        );

        $str = '{"$newData":"","orig_package":"1 Month + 1 connection","package":"2","package_cost":"","package_duration":"","contact":"","reseller_notes":"","max_connections":1}';
        $data = json_decode($str, true);
        $data = array_merge($newData, $data);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 这将跳过SSL证书验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 这将检查证书中的公用名是否与服务器的主机名匹配
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $curlErr = curl_error($ch);
        curl_close($ch);
        if (!empty($response)) {
            $jsonArr = @json_decode($response, true);
            if (!empty($jsonArr) && $jsonArr['result'] === true) {
                return true;
            }
        }
        $uid = isset($newData['edit']) ? $newData['edit'] : '';
        $uname = isset($newData['username']) ? $newData['username'] : '';
        $logLine = date('Y-m-d H:i:s') . "\tuid={$uid}\tuname={$uname}\tcurl_err={$curlErr}\tresponse=" . substr((string)$response, 0, 1000) . "\n";
        @file_put_contents(ROOT . '/runtime/logs/tv_xfuser_fail.log', $logLine, FILE_APPEND);
        return false;
    }

    public function getUserInfo()
    {
        $cookieId = $this->getCooike();
        // $url = $this->getUrl('userinfo');
        //模拟请求
        $headers = array(
             'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
             'Accept-Encoding: gzip, deflate, br, zstd',
             'Accept-Language: zh-CN,zh;q=0.9',
             'Proxy-Connection:keep-alive',
             'Host: kytv.xyz',
             "Cookie: theme=0; PHPSESSID={$cookieId};",
             'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
             'Referer:http://kytv.xyz/HckqYJZU/lines?order=0&dir=desc&entries=500',
             'X-Requested-With: XMLHttpRequest'
        );

        $res = $this->sendRequestByGet($this->getUrl('userinfo'), [], $headers);
        if ($res['response_code'] === 200) {
            $responseData = json_decode($res['output'], true);
            return $responseData;

        }
        return false;
    }



    public function addUser($newData)
    {
        $cookieId = $this->getCooike();
        $url = $this->getUrl('addUser');
        $headers = array(
         'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
         'Accept-Encoding: gzip, deflate, br, zstd',
         'Accept-Language: zh-CN,zh;q=0.9',
         'Proxy-Connection:keep-alive',
         "Cookie: theme=0; PHPSESSID={$cookieId};",
         'Host: kytv.xyz',
         'Sec-Fetch-Dest: document',
         'Sec-Fetch-Mode: navigate',
         'Sec-Fetch-Site: none',
         'Sec-Fetch-User: ?1',
         'Upgrade-Insecure-Requests: 1',
         'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
         'sec-ch-ua: "Google Chrome";v="125", "Chromium";v="125", "Not.A/Brand";v="24"',
         'sec-ch-ua-mobile: ?0',
         'sec-ch-ua-platform: "Windows"'
        );
        $exp_date = date('Y-m-d H', strtotime('+1 month'));
        $str = '{"package":"2","package_cost":"1","package_duration":"1 months","max_connections":"1","exp_date":"' . $exp_date . '","contact":"","reseller_notes":"","bouquets_selected":["13","14","15","16","70","17","97","18","11","12","19","20","111","145","21","79","132","22","121","10","23","91","133","80","81","25","82","26","71","144","27","128","29","122","30","75","101","31","32","104","130","33","163","50","123","9","72","165","34","119","140","113","8","99","35","84","85","102","7","146","88","110","125","60","103","68","69","127","126","108","64","112","65","66","67","37","143","73","137","6","141","124","109","55","39","40","78","135","41","42","158","131","43","63","120","5","142","114","118","139","93","4","107","47","3","1","117","94"]}';
        $data = json_decode($str, true);
        $data = array_merge($newData, $data);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 这将跳过SSL证书验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 这将检查证书中的公用名是否与服务器的主机名匹配
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);
        if (!empty($response)) {
            $jsonArr = @json_decode($response, true);
            if (!empty($jsonArr) && $jsonArr['result'] === true) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取请求的url
     *
     * author lyd
     * @param  $ <type> $actionName
     * @return type
     */
    private function getUrl($actionName)
    {
        $action = trim($this->cpUrlAction[$actionName], '/');
        return $this->reqHost . '/' . $action;
    }

    /**
     * 获取后台登陆的用户seesionid
     *
     * author lyd
     * @return type
     */
    private function getUserToken()
    {
        $i = 0; 
        while (true) {
            //cookie文件不存在， 要去请求获取
            if (!file_exists($this->fpath)) {
                 //登录并获取cookie
                $result = $this->login();

                // var_dump($result);
                // exit;
                // //相应失败的话重新进行
                if ($result['response_code'] != 200 || rtrim($result['info']['url'], '/') != $this->getUrl('loginindex')) {
                    $i++;
                    continue;
                }
            }

            //检测是否已经过期
            $res = sq_curl_headers_post($this->getUrl('index'), array(), 60, null, 80, $this->setHeaders(), false, $this->fpath);
            if ($res['response_code'] == 200 && rtrim($res['info']['url'], '/') != $this->getUrl('notloginindex')) {
                break; //能够使用
            }
            //不能使用
            //删除原本的cookie文件
            file_exists($this->fpath) && unlink($this->fpath);
            if ($i >= 2) {
                break; //网络可能请求失败，尝试3次，如果还失败则跳出
            }
            $i++;
        }
    }

    /**
     * 初始化header
     *
     * @param  $ <type> $cookie
     * @return <type>
     */
    private function setHeaders($cookie = '')
    {
        // 设置头部
        $headers = array(
           'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'Accept-Encoding: gzip, deflate, br, zstd',
            'Accept-Language: zh-CN,zh;q=0.9',
            'Proxy-Connection:keep-alive',
            'Host: kytv.xyz',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
            'Referer:http://kytv.xyz/HckqYJZU/dashboard',
            'X-Requested-With: XMLHttpRequest'
        );
        return $headers;
    }

    /**
     * 获取cookie
     *
     * @return <type>
     */
    private function getCooike()
    {
        if (file_exists($this->fpath)) {
            $content = file_get_contents($this->fpath);
            preg_match('/PHPSESSID(.*?)/U', $content, $match);

            $sessionId = trim($match[1]);
            if (!empty($sessionId)) {
                return $sessionId;
            }
        }
        return false;
    }

    public function login()
    {
        $paramString = "referrer=logout&username={$this->userInfo['uname']}&password={$this->userInfo['password']}&login=";
        $headers = array(
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'Accept-Encoding: gzip, deflate',
            'Accept-Language: zh-CN',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/127.0.0.0 Safari/537.36',
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: 61',
            'Referer: http://kytv.xyz/HckqYJZU/login',
            'Origin: http://kytv.xyz',
            'Upgrade-Insecure-Requests: 1',
            'Host: kytv.xyz',
            'Proxy-Connection: keep-alive',
        );
        return sq_curl_headers_post($this->getUrl('loginin'), $paramString, 30, null, 80, $headers, $this->fpath);
    }

    /**
     * 发送请求(登录成功后) GET
     *
     * author lyd
     * @param string $url
     * @param string $data
     * @return type
     */
    private function sendRequestByGet($url, $data = array(), $headers = array())
    {
        if ($data) {
            $data = http_build_query($data);
            $url .= '?' . $data;
        }
        $getHeader = $this->setHeaders();
        if (!empty($headers)) {
            $getHeader = $headers;
        }
        $result = sq_curl_headers_get($url, 60, null, 80, $getHeader, $this->fpath);
        return $result;
    }

    /**
     * 发送请求(登录成功后)
     *
     * author lyd
     * @param string $url
     * @param string $data
     * @return type
     */
    private function sendRequest($url, $data = array())
    {
        $result = sq_curl_headers_post($url, $data, 60, null, 80, $this->setHeaders(), false, $this->fpath);
        return $result;
    }
}

/**
 * curl 方式的get请求(可以设置headers)
 *
 * @param  $ <type> $url
 * @param  $ <type> $timeout
 * @param  $ <type> $ip
 * @param  $ <type> $port
 * @param  $ <type> $header
 * @param  $ <type> $referer
 * @return <type>
 */
function sq_curl_headers_get($url, $timeout = 30, $ip = null, $port = 80, $headers = array(), $referer = '')
{
    $arrCurlResult = array();
    $ch = curl_init($url);
    // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    // curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    // curl_setopt($ch, CURLOPT_VERBOSE, true);
    // if (!empty($referer)) {
    //     curl_setopt($ch, CURLOPT_REFERER, $referer);
    // }

    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_COOKIEFILE, "/tmp/cookie.txt");
    $output = curl_exec($ch);
    $get_info = curl_getinfo($ch);
    $arrCurlResult['output'] = $output; //返回结果
    $arrCurlResult['response_code'] = $get_info['http_code']; //返回http状态
    $arrCurlResult['info'] = $get_info; //返回所有相关信息
    $arrCurlResult['error_no'] = curl_errno($ch); //返回错误状态
    $arrCurlResult['error_content'] = curl_error($ch); //返回错误内容
    curl_close($ch);
    unset($ch);
    return $arrCurlResult;
}


function sq_curl_headers_post($url, $data, $timeout = 30, $ip = null, $port = 80, $headers = array(), $writeCookie = false, $readCookie = false)
{
    $arrCurlResult = array();
    $ch = curl_init();
    if ($ip !== null) { // 带代理方式
        $proxy = "http://$ip:$port";
        curl_setopt($ch, CURLOPT_PROXY, $proxy);
    }
    if (is_array($data)) {
        $data = http_build_query($data, null, '&');
    }
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    // curl_setopt($ch, CURLOPT_REFERER, "YX Lib.");
    // curl_setopt($ch, CURLOPT_VERBOSE, true);


    if ($writeCookie) {
        $fpath = $writeCookie; 
        if (!file_exists(dirname($fpath))) {
            mkdir(dirname($fpath), 0777, true);
        }
    } 
    if ($readCookie) {
        //指定发送给服务器的cookie文件
        curl_setopt($ch, CURLOPT_COOKIEFILE, $readCookie);
    }
    // curl_setopt($ch, CURLOPT_COOKIEFILE, "/tmp/cookie.txt");
   
    curl_setopt($ch, CURLOPT_COOKIEJAR, $fpath); //保存cookie
    if ('https://' === strtolower(substr($url, 0, 8))) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }

    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $output = curl_exec($ch);
    $get_info = curl_getinfo($ch);
    $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $arrCurlResult['output'] = $output; //返回结果
    $arrCurlResult['response_code'] = $responseCode; //返回http状态
    $arrCurlResult['info'] = $get_info; //返回所有相关信息
    $arrCurlResult['error_no'] = curl_errno($ch); //返回错误状态
    $arrCurlResult['error_content'] = curl_error($ch); //返回错误内容
    curl_close($ch);
    unset($ch);
    return $arrCurlResult;
}

?>