<?php

namespace App\sdk\console;

use App\common\App;
use App\common\Table;
use YXLib\foundation\ModelFactory;
use YXLib\foundation\queue\RedisQueue;
use App\common\Queue;

class PopActiveData
{
    public $queueName = 'log_active';

    public function handle()
    {
        $job = new RedisQueue('queue');
        $store = new RedisQueue('store');
        $model = ModelFactory::getInstance('main');
        $sdk = ModelFactory::getInstance('sdk');
        
        try {
            while (1) {
                $json = $job->pop($this->queueName);
                if ($json) {
                    $gpc = json_decode($json, true);//[]
                    $wm_gpc = array();
                    if ($gpc['install'] == 1) {//第一次启动
                        
                    }
                    
                    if (empty($wm_gpc['refer'])) {
                        $arr_refer = App::parseSdkRefer($gpc['refer'], $gpc['appid']);
                        //修改cid
                        $gpc['cid'] = $arr_refer['cid'];
                    }

                    if (isset($gpc['set_black_mac']) && $gpc['set_black_mac'] == 1) {
                        $xxsxx = $sdk->commonGetOne('x_mac', 'mac', $gpc['mac']);
                        if (empty($xxsxx) || $xxsxx['status'] != 0) {
                            ##设置黑名单
                            $res = $sdk->update(array('status' => 0), ['mac' => $gpc['mac']], 'x_mac');
                            ###钉钉通知
                            if ($res) {
                                $black_num = $sdk->getCount(['status' => 0], 'x_mac');
                                $monitorArr = ['title' => '黑名单设备变动通知', 'message' => "新增黑名单设备MAC:{$gpc['mac']}，黑名单设备共{$black_num}台."];
                                //Queue::push('monitor', $monitorArr); 
                            }
                        }
                    }

                    if (isset($gpc['is_conuse_user']) && $gpc['is_conuse_user'] == 1) {
                        ##补充账号
                        Queue::push('add_xuser', $gpc);
                    }

                    $row = Table::fieldsFilter($gpc, 'log_active');
                    // $model->insert($row, false, Table::$log_active . date('Ymd'));
                    $model->insert($row, false, 'log_active');

                    $row3 = Table::fieldsFilter($gpc, 'x_mac_active');
                    $sdk->insert($row3, false, 'x_mac_active');

                    $row2 = Table::fieldsFilter($gpc, 'log_login');
                    // $model->ignoreInsert($row2, false, Table::$log_login . date('Ymd'));
                    $model->ignoreInsert($row2, false, 'log_login');

                    

                } else {
                    sleep(3);
                }
            }
        } catch (\Exception $ex) {
            $date = date("Y-m-d H:i:s");
            file_put_contents(ROOT . '/runtime/logs/queue_pop_[' . $this->queueName . '].log', $date . '###' . json_encode($gpc) . "\n", FILE_APPEND);
            file_put_contents(ROOT . '/runtime/logs/queue_pop_[' . $this->queueName . ']_error.log', $date . '###' . $ex->getMessage() . "\n", FILE_APPEND);
            exit();
        }    
    }
}
