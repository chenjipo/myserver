<?php

namespace App\sdk\console;

use App\common\TvTool;
use App\common\TvUserPool;
use App\common\Queue;
use YXLib\foundation\ModelFactory;

class PreTvUserTool
{
    private const COOLDOWN_SECONDS = 3600;

    public function handle($params)
    {
        $lockFile = ROOT . '/runtime/locks/pre_tv_user_tool.lock';
        $cooldownFile = ROOT . '/runtime/locks/pre_tv_user_last_run.txt';
        $lockDir = dirname($lockFile);
        if (!is_dir($lockDir)) {
            mkdir($lockDir, 0777, true);
        }
        $fp = fopen($lockFile, 'c+');
        if ($fp === false || !flock($fp, LOCK_EX | LOCK_NB)) {
            echo "PreTvUserTool: another instance is running\n";
            if ($fp !== false) {
                fclose($fp);
            }
            return;
        }
        try {
            $inventory = TvUserPool::getInventory();
            $dbPoolNum = $inventory['dbPool'];
            $queueLength = $inventory['queue'];
            $availableNum = $inventory['available'];
            $limitNum = TvUserPool::LIMIT_NUM;
            echo "PreTvUserTool: dbPool={$dbPoolNum}, queue={$queueLength}, available={$availableNum}, limit={$limitNum}\n";
            if ($availableNum >= $limitNum) {
                echo "PreTvUserTool: inventory sufficient, skip\n";
                return;
            }
            if (file_exists($cooldownFile)) {
                $lastRun = (int)file_get_contents($cooldownFile);
                $remain = self::COOLDOWN_SECONDS - (time() - $lastRun);
                if ($remain > 0) {
                    echo "PreTvUserTool: cooldown active, remain {$remain}s, skip\n";
                    return;
                }
            }
            $needNum = $limitNum - $availableNum;
            $obj = new TvTool();
            $success = 0;
            for ($i = 1; $i <= $needNum; $i++) {
                $newData = array(
                    'username' => rand_user('x1'),
                    'password' => sprintf('%08d', rand(00000000, 99999999)),
                );
                $res = $obj->addUser($newData);
                if ($res === true) {
                    $success++;
                }
            }
            if ($success > 0) {
                file_put_contents($cooldownFile, (string)time());
                $getTvUserTool = new GetTvUserTool();
                $getTvUserTool->handle([]);
                $message = "可用库存(队列{$queueLength}+空闲池{$dbPoolNum})共{$availableNum}个,不足{$limitNum}个,已补充{$success}个账号";
                $monitorArr = ['title' => 'player空闲账号库存通知', 'message' => $message];
                Queue::push('monitor', $monitorArr);
            }
        } finally {
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }
}
