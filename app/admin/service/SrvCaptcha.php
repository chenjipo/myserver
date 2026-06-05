<?php

namespace App\admin\service;

use YXLib\foundation\Cache;
use YXLib\support\Util;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/2/17
 * Time: 10:58
 */
class SrvCaptcha{

    private static $para = array(
        'background' => '#FFFFFF',
        'width' => '100',
        'height' => '80',
        'type' => 'jpg',
        'color' => '#000000',
        'fontSize' => 12,
        'fontAngle' => 0,
    );

    public static function changeToChn($code){
        $map = array(
            1=>'一',
            2=>'二',
            3=>'三',
            4=>'四',
            5=>'五',
            6=>'六',
            7=>'七',
            8=>'八',
            9=>'九',
            10=>'十',
        );
        if($code<10){
            return $map[$code];
        }else{
            $code .= '';
            return $map[$code[0]].'十'.($code[1]==0?'':$map[$code[1]]);
        }
    }

    public static function save($code){
        $mc = Cache::getInstance();
        $mc->set('Captcha'.Util::getIp(),$code,60);
    }

    private static $ignore_font = array(
        '减'=>array('bantianshui','kongyin','xinchaofont'),
        '减去'=>array('bantianshui','kongyin','xinchaofont'),
    );

    private static $images;
    private static $colorPool = array();

    public static function meansCode($str, $config = array())
    {

        if (is_array($config) && !empty($config)) {
            self::$para = array_merge(self::$para, $config);
        }

        self::init();

        //$str = "您帐号中第{$str}个拼音";
        self::text($str, 'ch');

        self::output();
    }

    /**
     * 写字
     * @param $str
     * @param string $lang
     */
    private static function text($str, $lang = "")
    {
        if (empty($str)) {
            return;
        }

        // 选择字体

        self::$para['font_path'] = APP_ROOT . '/public/static/fonts/';

        $_t = Util::strSplit($str);

        // 中文增加字符的宽度
        $_strLength = self::$para['fontSize'];
        /*if (!empty($lang)) {
            $_strLength = $_strLength * 1.2;
        }*/
        // 左间距
        $paddingLeft = intval(self::$para['width'] - count($_t) * $_strLength) / 2;
        if ($paddingLeft < 5) {
            $paddingLeft = 5;
        }
        //$text_color = imagecolorallocate(self::$images, 0, 0, 255);
        //imagestring(self::$images, 5, 0, 0, $str, $text_color);
        foreach ($_t as $n => $char) {
            $_s = self::$para['fontSize'];
            $_h = (self::$para['height'] - $_s) / 2 + $_s;
            $_ag = 0;
            $font = self::fontRand(self::$para['font'],$char);
            imagettftext(self::$images, $_s, $_ag, $paddingLeft, $_h, self::color(self::randGet(self::$para['color'])), self::$para['font_path'].$font.'.ttf', $char);
            $paddingLeft += $_strLength;;
            if (ord($char) < 127) {
                $paddingLeft -= $_strLength * 0.2;
            }
        }
    }

    private static function fontRand($fonts,$str){
        $font = self::randGet($fonts);
        if(!self::$ignore_font[$str]) return $font;
        if(in_array($font,self::$ignore_font[$str])) return self::fontRand($fonts,$str);
        return $font;
    }

    private static function randGet($some)
    {
        if (is_array($some)) {
            return $some[array_rand($some)];
        } else {
            return $some;
        }
    }

    private static $typeNotice = array(
        'png' => array('image/png', 'imagepng'), 'jpg' => array('image/jpeg', 'imagejpeg'), 'gif' => array('image/gif', 'imagegif'), 'bmp' => array('image/x-ms-bmp', 'imagewbmp')
    );

    /**
     * 初始化图像
     */
    private static function init()
    {
        if (!in_array(self::$para['type'], self::$typeNotice)) {
            self::$para['type'] = 'png';
        }
        header('Content-type: ' . self::$typeNotice[self::$para['type']][0]);

        self::$images = imagecreate(self::$para['width'], self::$para['height']);
        imageantialias(self::$images, true);
        if (self::$para['background'] != 'none') {
            imagefilledrectangle(self::$images, 100, 0, self::$para['width'], self::$para['height'], self::color(self::randGet(self::$para['background'])));
        }
    }

    /**
     * 输出
     */
    private static function output()
    {
        if (!empty(self::$para['border'])) {
            imagerectangle(self::$images, 0, 0, self::$para['width'] - 1, self::$para['height'] - 1, self::color(self::$para['border']));
        }

        //
        $_fun = self::$typeNotice[self::$para['type']][1];
        $_fun(self::$images);
        imagedestroy(self::$images);
        die();
    }

    /**
     * 获取颜色代码
     * @param string $string
     * @return int
     */
    private static function color($string)
    {
        if (empty(self::$colorPool[$string])) {
            if (substr($string, 0, 1) == '#') {
                $string = substr($string, 1);
            }
            $string = hexdec($string);
            $r = ($string >> 16) & 0xFF;
            $g = ($string >> 8) & 0xFF;
            $b = $string & 0xFF;
            self::$colorPool[$string] = imagecolorallocate(self::$images, $r, $g, $b);
        }
        return self::$colorPool[$string];
    }

}