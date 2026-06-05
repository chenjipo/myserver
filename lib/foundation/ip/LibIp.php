<?php
namespace YXLib\foundation\ip;

use YXLib\support\Util;

/**
 * IP 地理位置查询类
 *
 */
class LibIp
{
    /**
     * 文件指针
     */
    var $fp;

    /**
     * 第一条IP记录的偏移地址
     */
    var $firstip;

    /**
     * 最后一条IP记录的偏移地址
     */
    var $lastip;

    /**
     * IP记录的总条数（不包含版本信息记录）
     */
    var $totalip;

    var $provinces = array("黑龙江省", "辽宁省", "吉林省", "河北省", "河南省", "湖北省", "湖南省", "山东省", "山西省", "陕西省","安徽省", "浙江省", "江苏省", "福建省", "广东省", "海南省", "四川省", "云南省", "贵州省", "青海省", "甘肃省","江西省", "台湾省", "内蒙古", "宁夏", "新疆", "西藏", "广西", "北京市", "上海市", "天津市", "重庆市", "香港", "澳门");
    /**
     * 返回读取的长整型数
     */
    private function getlong(){
        //将读取的little-endian编码的4个字节转化为长整型数
        $result = unpack('Vlong', fread($this->fp, 4));
        return $result['long'];
    }

    /**
     * 返回读取的3个字节的长整型数
     */
    private function getlong3(){
        //将读取的little-endian编码的3个字节转化为长整型数
        $result = unpack('Vlong', fread($this->fp, 3).chr(0));
        return $result['long'];
    }

    /**
     * 返回压缩后可进行比较的IP地址
     */
    private function packip($ip){
        // 将IP地址转化为长整型数，如果在PHP5中，IP地址错误，则返回False，
        // 这时intval将Flase转化为整数-1，之后压缩成big-endian编码的字符串
        return pack('N', intval(ip2long($ip)));//intaval 获取变量的整数值
    }

    /**
     * 返回读取的字符串
     */
    private function getstring($data = ""){
        $char = fread($this->fp, 1);
        while (ord($char) > 0){        // 字符串按照C格式保存，以\0结束 ord()得到字符的ASCII码
            $data .= $char;                // 将读取的字符连接到给定字符串之后
            $char = fread($this->fp, 1);
        }
        return $data;
    }

    /**
     * 返回地区信息
     */
    private function getarea(){
        $byte = fread($this->fp, 1);    // 标志字节
        switch (ord($byte)) {
            case 0:                        // 没有区域信息
                $area = "";
                break;
            case 1:
            case 2:                        // 标志字节为1或2，表示区域信息被重定向
                fseek($this->fp, $this->getlong3());
                $area = $this->getstring();
                break;
            default:                    // 否则，表示区域信息没有被重定向
                $area = $this->getstring($byte);
                break;
        }
        return $area;
    }

    /**
     * 根据所给 IP 地址或域名返回所在地区信息
     */
    public function getlocation($ip){
        if (!$this->fp) return null;            // 如果数据文件没有被正确打开，则直接返回空
        $location['ip'] = $ip;
        $ip = $this->packip($location['ip']);    // 将输入的IP地址转化为可比较的IP地址

        // 对分搜索
        $l = 0;                            // 搜索的下边界
        $u = $this->totalip;            // 搜索的上边界
        $findip = $this->lastip;        // 如果没有找到就返回最后一条IP记录（QQWry.Dat的版本信息）
        while ($l <= $u){                // 当上边界小于下边界时，查找失败
            $i = floor(($l + $u) / 2);    // 计算近似中间记录
            fseek($this->fp, $this->firstip + $i * 7);
            $beginip = strrev(fread($this->fp, 4));        // 获取中间记录的开始IP地址
            // strrev函数在这里的作用是将little-endian的压缩IP地址转化为big-endian的格式
            // 以便用于比较，后面相同。
            if ($ip < $beginip){        // 用户的IP小于中间记录的开始IP地址时
                $u = $i - 1;            // 将搜索的上边界修改为中间记录减一
            }else{
                fseek($this->fp, $this->getlong3());
                $endip = strrev(fread($this->fp, 4));    // 获取中间记录的结束IP地址
                if ($ip > $endip){        // 用户的IP大于中间记录的结束IP地址时
                    $l = $i + 1;        // 将搜索的下边界修改为中间记录加一
                }else{                    // 用户的IP在中间记录的IP范围内时
                    $findip = $this->firstip + $i * 7;
                    break;                // 则表示找到结果，退出循环
                }
            }
        }

        //获取查找到的IP地理位置信息
        fseek($this->fp, $findip);
        $location['beginip'] = long2ip($this->getlong());    // 用户IP所在范围的开始地址
        $offset = $this->getlong3();
        fseek($this->fp, $offset);
        $location['endip'] = long2ip($this->getlong());        // 用户IP所在范围的结束地址
        $byte = fread($this->fp, 1);    // 标志字节
        switch (ord($byte)){
            case 1:                        // 标志字节为1，表示国家和区域信息都被同时重定向
                $countryOffset = $this->getlong3();            // 重定向地址
                fseek($this->fp, $countryOffset);
                $byte = fread($this->fp, 1);    // 标志字节
                switch (ord($byte))
                {
                    case 2:                // 标志字节为2，表示国家信息又被重定向
                        fseek($this->fp, $this->getlong3());
                        $location['country'] = $this->getstring();
                        fseek($this->fp, $countryOffset + 4);
                        $location['isp'] = $this->getarea();
                        break;
                    default:            // 否则，表示国家信息没有被重定向
                        $location['country'] = $this->getstring($byte);
                        $location['isp'] = $this->getarea();
                        break;
                }
                break;
            case 2:                        // 标志字节为2，表示国家信息被重定向
                fseek($this->fp, $this->getlong3());
                $location['country'] = $this->getstring();
                fseek($this->fp, $offset + 8);
                $location['isp'] = $this->getarea();
                break;
            default:                    // 否则，表示国家信息没有被重定向
                $location['country'] = $this->getstring($byte);
                $location['isp'] = $this->getarea();
                break;
        }
        $location['country'] = isset($location['country'])?iconv('gbk','utf-8',$location['country']):"";
        $location['isp'] = isset($location['isp'])?iconv('gbk','utf-8',$location['isp']):"";
        if ($location['country'] == " CZ88.NET"){
            $location['country'] = "未知";
        }
        if ($location['isp'] == " CZ88.NET"){
            $location['isp'] = "";
        }

        if ($location['country']){
            $location = array_merge($location, $this->split_str($location['country']));
        }
        if($location['city'] == false){
            $location['city'] = $location['province'];
        }

        if(!in_array($location['province'],$this->provinces)){
            //外国ip用外网校验
            $url = 'http://ip.taobao.com/service/getIpInfo.php?ip='.$location['ip'];
            $file_contents = Util::request($url);
            $area = json_decode($file_contents['result'],true);
            $location = array(
                'country' => $area['data']['country'],
                'isp' => $area['data']['isp'] == 'XX' ? '' : $area['data']['isp'],
                'province' => $area['data']['region'] == 'XX' ? $area['data']['country'] : $area['data']['region'],
                'city' => $area['data']['region'] == 'XX' ? $area['data']['country'] : $area['data']['region'],
            );
        }

        unset($location['beginip']);
        unset($location['endip']);
        unset($location['ip']);
        return $location;
    }

    public function __construct(){
        $filename = dirname(__FILE__) . "/qqwry.dat";
        if (($this->fp = @fopen($filename, 'rb')) !== false)
        {
            $this->firstip = $this->getlong();
            $this->lastip = $this->getlong();
            $this->totalip = ($this->lastip - $this->firstip) / 7;
        }
    }

    public function __destruct(){
        fclose($this->fp);
    }

    private function get_split_pos($addr='广东省广州市'){
        // 省
        if($pos = strpos( $addr, '省')) return $pos+3;
        // 直辖市
        $municipality_arr = array('北京市','天津市','上海市','重庆市');
        foreach($municipality_arr as $k=>$v){
            $pos = strpos($addr, $v);
            if( $pos!==false ){
                $pos = strlen($v);
                return $pos;
            }
        }
        // 自治区/港澳
        $auto_nomous_arr = array('广西自治区','广西','宁夏','新疆','西藏','内蒙古','香港','澳门');
        foreach($auto_nomous_arr as $k=>$v){
            $pos = strpos($addr, $v);
            if( $pos!==false ){
                $pos = strlen($v);
                return $pos;
            }
        }
    }

    private function split_str($addr){
        $pos = $this->get_split_pos($addr);
        if(preg_match('/自治区/', $addr)){
            $pos_ = strpos($addr, '自治区');
        }else{
            $pos_ = $pos;
        }
        if(empty($pos)){
            $pos_ = $pos = strlen($addr);
        }
        $add_arr['province'] = substr($addr, 0, $pos_);
        $add_arr['city'] = substr($addr, $pos);
        return $add_arr;
    }
}