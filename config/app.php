<?php

return [
    /**
     * 缓存驱动 redis|memcache|file
     */
    'cache'=>'file',
    /**
     * 控制器目录名称
     */
    'ctl_name'=> 'controller',
    /**
     * 默认语言
     */
    'app_lang' => 'zh_CN',
    /**
     * 时区
     */
    'timezone' => 'Asia/Shanghai',
    /**
     * 当前运行环境，开发或线上
     */
    'env' => env('app_env', 'production'),
    /**
     * 调试模式
     */
    'debug' => env('app_debug', false),
    /**
     * 中间件配置
     * 控制器数组为空代表全局中间件
     * 项目=>[中间件]
     */
    'middleware'=>[
        //用户认证
        'admin'=>[
            \App\admin\middleware\UserAuth::class,
            \App\admin\middleware\MaxPageLimit::class,
        ],
    ],
    /**
     * 监测域名
     */
    'monitor_domain'=> env('monitor_domain'),
    /**
     * cdn域名
     */
    'cdn_domain'=> env('cdn_domain'),
    /**
     * sdk域名
     */
    'sdk_domain'=> env('sdk_domain'),
    /**
     * 安卓母包上传目录
     */
    'model_dir'=> env('model_dir'),
    /**
     * 安卓母包oss存放目录
     */
    'model_oss_dir'=> env('model_oss_dir'),
    /**
     * 数据库缓存文件
     */
    'sy_data_path' => ROOT . '/runtime/data/%s/%s.arr.php',

    /**
     * 标记sdk日志已处理的锁文件存放目录
     */
    'sdk_lock_path' => ROOT . '/runtime/lock',

    /**
     * sdk日志文件存放目录
     */
    'sdk_log_path' => ROOT . '/runtime/sdk'
];