<?php

namespace App\common;

class Table {

    static $admin_user = 'admin_user';
    static $roles = 'admin_roles';
    static $role_has_permissions = 'admin_role_has_permissions';
    static $permissions = 'admin_permissions';//后台权限表
    static $admin_mk_authority = 'admin_mk_authority';//投放权限表
    static $admin_data_authority = 'admin_data_authority';//数据权限表

    static $data_overview_day = 'data_overview_day';
    static $data_retain = 'data_retain';
    
    static $pf_game = 'pf_game';
    static $pf_game_main = 'pf_game_main';
    static $pf_game_divide = 'pf_game_divide';
    static $pf_game_channel_mpkg = 'pf_game_channel_mpkg';
    static $pf_game_channel_pkg = 'pf_game_channel_pkg';
    static $pf_game_channel_pkg_task = 'pf_game_channel_pkg_task';
    static $pf_game_channel_pkg_task_locl = 'pf_game_channel_pkg_task_locl';
    static $pf_game_sdk_version = 'pf_game_sdk_version';
    static $pf_channel = 'pf_channel';
    static $pf_partner = 'pf_partner';
    static $pf_game_partner_pkg = 'pf_game_partner_pkg';
    static $pf_payway_divide = 'pf_payway_divide';
    static $pf_sdk_white = 'pf_sdk_white';
    static $pf_sdk_black = 'pf_sdk_black';
    static $pf_announcement = 'pf_announcement';

    static $mk_pitcher = 'mk_pitcher';
    static $mk_pitcher_team = 'mk_pitcher_team';
    static $mk_company = 'mk_company';
    static $mk_agent_rebate = 'mk_agent_rebate';
    static $mk_agent = 'mk_agent';
    static $mk_ad_link = 'mk_ad_link';
    static $mk_ad_account = 'mk_ad_account';
    static $mk_channel_ad_click = 'mk_channel_ad_click';
    static $mk_channel_ad_callback = 'mk_channel_ad_callback';

    static $pf_app = 'pf_app';
    static $pf_product = 'pf_product';
    static $pf_payway = 'pf_payway';

    static $log_active = 'log_active_';
    static $log_login = 'log_login_';
    static $log_click_ = 'log_click_';

    static $t_user = 'z_yonghu';
    static $t_order = 'z_dingdan';
    static $t_order_key = 'z_order_key';
    static $z_agreement_sign = 'z_agreement_sign';

    static $x_mac = 'x_mac';
    static $x_user = 'x_user';
    static $x_mac_user_map = 'x_mac_user_map';
    static $t_vote = 'x_mac_vote';

    static $tableFields = [
        'log_active'=>['uid','duid','appid','cid','refer','version','resolution','mod','model','os','sysver','brand','pkgid','mac','client_id','ntype','nname','install','ip','continent','country','province','city','isp','ymd','h','atime'],
        'x_mac_active'=>['appid','mac','ymd','atime'],
        'log_login'=>['uid','duid','appid','cid','refer','version','resolution','mod','model','os','sysver','brand','pkgid','mac','client_id','ntype','nname','install','ip','continent','country','province','city','isp','ymd','h','atime','rdate','rhour','utype'],
        'log_event'=>['uuid','duid','appid','cid','refer','version','event','os','pkgid','client_id','ip','continent','country','province','city','ymd','h','atime'],
        'log_crash'=>['uid','duid','appid','cid','refer','version','pkgid','mac','ntype','nname','ip','continent','country','province','city', 'crash','ymd','h','atime']
    ];

    //过滤表字段
    static function fieldsFilter($in, $type)
    {
        $out = [];
        foreach ( self::$tableFields[$type] as $field) {
            if (isset($in[$field])) {
                if (in_array($field,['appid','pid'])) {
                    $out[$field] = (int)$in[$field];
                } else {
                    $out[$field] = $in[$field];
                }
            }
        }
        return $out;
    }
}