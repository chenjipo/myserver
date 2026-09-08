<?php

namespace App\sdk\console;

use App\common\DeviceServer;
use App\common\TvTool;
use App\common\TvUserPool;
use App\sdk\model\Xuser;
use YXLib\foundation\ModelFactory;
use App\common\SdkQueue;
use App\common\Queue;

class CheckTvUserTool
{
    /** 队列入队要求：距过期至少剩余秒数 */
    private const QUEUE_MIN_REMAIN_SECONDS = 86400;

    /** 定时续费：距过期不足该秒数则续费 */
    private const RENEW_THRESHOLD_SECONDS = 259200;

    public function handle($params)
    { 
        $is_get_data = false;
        if (isset($params['out']) && 1 == $params['out']) {
            $is_get_data = true;
        }

        $min = date('i');
        if ($min % 30 == 0) {
            $is_get_data = true;
        }
        // $is_get_data = true;

        $time = time();
        $date = date("Y-m-d H:i:s");
        $model = ModelFactory::getInstance('sdk');
        $xuser = new Xuser();

        $allNum = $kcNum = $zxNum = $macNum = $macActiveNum = $macBlackNum = 0;
        $balance = $allUser = $onlineUser = 0;
        // ###检查账号队列库存
        $queueLength = SdkQueue::glength('pre_xuser');

        ###检查可用账号库存
        $sql = "select count(1) as allNum,count(if(is_push=0,1,null)) as kcNum,count(if(is_online=1,1,null)) as zxNum from x_user where status=1 and ystatus=1 and is_clear=0";
        $hasInfo = $model->getOne($sql);
        if (!empty($hasInfo)) {
            $allNum = (int)$hasInfo['allNum'];
            $kcNum  = (int)$hasInfo['kcNum'];
            $zxNum  = (int)$hasInfo['zxNum'];
        }

        if (true === $is_get_data) {
            $sql = "select count(1) as num from x_mac_user_map";
            $hasInfo = $model->getOne($sql);
            if (!empty($hasInfo)) {
                $macNum = (int)$hasInfo['num'];
            }

            ###获取用户余额数据
            $sql = "select balance,all_user,online_user from x_all_data where id=1";
            $hasInfo = $model->getOne($sql);
            if (!empty($hasInfo)) {
                $balance    = (float)$hasInfo['balance'];
                $allUser    = (int)$hasInfo['all_user'];
                $onlineUser = (int)$hasInfo['online_user'];
            }

            ###获取设备信息
            $sql = "select count(1) as num,count(if(b.status=0,1,null)) as blackNum from x_mac_active a join x_mac b on a.mac=b.mac where a.appid=100007";
            $hasInfo = $model->getOne($sql);
            if (!empty($hasInfo)) {
                $macActiveNum = (int)$hasInfo['num'];
                $macBlackNum = (int)$hasInfo['blackNum'];
            }
            echo "当前MAC激活总数:{$macActiveNum},设备黑名单:{$macBlackNum},账号池账号数:{$allNum},在线账号数:{$zxNum}({$onlineUser}),设备绑定数: {$macNum},可用库存数: {$kcNum},队列账号数: {$queueLength}\n";
        }
        
        // ##获取当前账号数据
        // if ($min % 30 == 0) {
            $message = "\n账户余额: \${$balance}\n总用户数: {$allUser}\n设备总激活: {$macActiveNum}\n设备黑名单: {$macBlackNum}\n账号池数: {$allNum}\n在线账号: {$zxNum}\n设备绑定数: {$macNum}\n可用库存数: {$kcNum}\n队列账号数: {$queueLength}";
            echo $message;
        //     $monitorArr = ['title' => 'player账号信息', 'message' => $message];
        //     Queue::push('monitor', $monitorArr); 
        // }

        if (true === $is_get_data) {
            $message = "\n账户余额: \${$balance}\n总用户数: {$allUser}\n设备总激活: {$macActiveNum}\n设备黑名单: {$macBlackNum}\n有效账号数: {$allNum}\n在线账号: {$onlineUser}\n设备绑定数: {$macNum}\n队列账号数: {$queueLength}";
            $monitorArr = ['title' => 'player账号信息', 'message' => $message];
            Queue::push('monitor', $monitorArr); 
        }

        // if ($kcNum <= 30) {
        //     echo "可用账号库存小于30\n";
        //     ###执行
        //     exec('/usr/local/php/bin/php /www/cmd sdk:ClearTvUserTool');
        // }

        // $expored = time() + 86400 * 5;
        // $sql = "select a.id,a.uid,a.uname,a.upwd,a.yexpired from x_user a left join x_mac_user_map b on a.id=b.userid where a.is_push=0 and a.is_online=0 and a.status=1 and a.ystatus=1 and a.is_clear=1 and a.yexpired >= {$expored} and b.macid is null";
        // $list = $model->query($sql);
        // // var_dump($list);
        // foreach ($list as $row) {
        //     // $xuser->updateUinfo($row['uid'], array('is_clear' => 0));
        // }
        // exit;
        $limitNum = TvUserPool::LIMIT_NUM;

        if ($queueLength < $limitNum) {
            $lockFile = ROOT . '/runtime/locks/check_tv_user_tool.lock';
            $lockDir = dirname($lockFile);
            if (!is_dir($lockDir)) {
                mkdir($lockDir, 0777, true);
            }
            $fp = fopen($lockFile, 'c+');
            if ($fp === false || !flock($fp, LOCK_EX | LOCK_NB)) {
                echo "CheckTvUserTool: queue refill skipped, another instance is running\n";
                if ($fp !== false) {
                    fclose($fp);
                }
            } else {
            try {
                $queueLength = SdkQueue::glength('pre_xuser');
                if ($queueLength >= $limitNum) {
                    echo "CheckTvUserTool: queue already refilled, skip\n";
                } else {
                echo "队列补充账号ing\n";
                $needNum = $limitNum - $queueLength;
                $renewed = $this->renewIdlePoolExpiringUsers($model, $time);
                if ($renewed > 0) {
                    echo "CheckTvUserTool: renewed {$renewed} idle pool account(s) before queue refill\n";
                }
                $list = $this->fetchQueueCandidates($model, $time, $needNum);

                if (!empty($list)) {
                    $success = 0;
                    $failed = 0;
                    foreach ($list as $row) {
                        $pushed = SdkQueue::push('pre_xuser', [
                            'id'         => $row['id'],
                            'uid'        => $row['uid'],
                            'uname'      => $row['uname'],
                            'upwd'       => $row['upwd'],
                            'expired'    => $row['yexpired'],
                        ]);
                        $json = json_encode([
                            'id'         => $row['id'],
                            'uid'        => $row['uid'],
                            'uname'      => $row['uname'],
                            'upwd'       => $row['upwd'],
                            'expired'    => $row['yexpired'],
                        ]);
                        echo $json . "\n";
                        if (!empty($pushed)) {
                            $success++;
                            $xuser->updateUinfo($row['uid'], array('is_push' => 1));
                        } else {
                            $failed++;
                            echo "CheckTvUserTool: push failed, uid={$row['uid']}, is_push unchanged\n";
                        }
                    }
                    echo "CheckTvUserTool: queue refill success={$success}, failed={$failed}\n";
                } else {
                    $message = "\n当前队列账号数量:{$queueLength},已不足{$limitNum}个,需补充{$needNum}个\n没有可用的账号库存,请联系技术管理员!";
                    $monitorArr = ['title' => 'player业务告警通知', 'message' => $message];
                    Queue::push('monitor', $monitorArr);
                }
                }
            } finally {
                flock($fp, LOCK_UN);
                fclose($fp);
            }
            }
        }
        

        // $time = $time - 86400 * 3;
        $hourI = date('H:i');
        // $hourI = "03:10";
        ###每天03:35检查续费
        if (in_array($hourI, ["03:10", "12:10", "18:10"])) {
            echo "{$hourI}检查续费账号ing\n";
            $boundRenewed = $this->renewBoundExpiringUsers($model, $time);
            $idleRenewed = $this->renewIdlePoolExpiringUsers($model, $time, self::RENEW_THRESHOLD_SECONDS);
            $success = $boundRenewed + $idleRenewed;
            if ($success > 0) {
                $message = "定时续费: 已绑定账号{$boundRenewed}个, 空闲池账号{$idleRenewed}个, 共成功续费{$success}个";
                echo $message;
                $monitorArr = ['title' => 'player业务通知', 'message' => $message];
                Queue::push('monitor', $monitorArr);
            }
        }
    }

    private function fetchQueueCandidates($model, $time, $needNum)
    {
        $minRemain = self::QUEUE_MIN_REMAIN_SECONDS;
        $sql = "select a.id,a.uid,a.uname,a.upwd,a.yexpired from x_user a left join x_mac_user_map b on a.id=b.userid where a.is_push=0 and a.is_online=0 and a.status=1 and a.ystatus=1 and a.yexpired-{$minRemain}>={$time} and a.is_clear=0 and b.macid is null order by a.lastonline asc limit {$needNum}";
        return $model->query($sql);
    }

    private function renewIdlePoolExpiringUsers($model, $time, $thresholdSeconds = self::QUEUE_MIN_REMAIN_SECONDS)
    {
        $sql = "select a.uid,a.uname,a.upwd,a.yexpired from x_user a left join x_mac_user_map b on a.id=b.userid where a.is_push=0 and a.is_online=0 and a.status=1 and a.ystatus=1 and a.is_clear=0 and b.macid is null and a.yexpired-{$thresholdSeconds}<{$time}";
        return $this->executeRenewUsers($model, $sql);
    }

    private function renewBoundExpiringUsers($model, $time)
    {
        $thresholdSeconds = self::RENEW_THRESHOLD_SECONDS;
        $sql = "select a.uid,a.uname,a.upwd,a.yexpired from x_user a join x_mac_user_map b on a.id=b.userid where a.is_clear=0 and a.yexpired-{$thresholdSeconds}<{$time}";
        return $this->executeRenewUsers($model, $sql);
    }

    private function executeRenewUsers($model, $sql)
    {
        $list = $model->query($sql);
        if (empty($list)) {
            return 0;
        }
        $obj = new TvTool();
        $success = 0;
        foreach ($list as $row) {
            $newData = array(
                'edit'     => $row['uid'],
                'username' => $row['uname'],
                'password' => $row['upwd'],
            );
            $res = $obj->xfUser($newData);
            if (true === $res) {
                $success++;
            }
        }
        if ($success > 0) {
            $getTvUserTool = new GetTvUserTool();
            $getTvUserTool->handle([]);
        }
        return $success;
    }
}