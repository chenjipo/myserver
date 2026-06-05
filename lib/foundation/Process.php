<?php
namespace YXLib\foundation;
use YXLib\foundation\Debug;
/**
 * 多进程模型
 * 依赖 swoole 一般配合队列
 * User: wtf
 * Date: 2019/4/22
 * Time: 2:54 PM
 */
class Process
{
    /**
     * @var array
     */
    private $worker = [];

    /**
     * @var int
     */
    private $work_num = 0;

    /**
     * @var null
     */
    private $start_time = null;

    /**
     * @var null
     */
    private $end_time = null;

    /**
     * LibProcess constructor.
     * @param $work_num
     */
    public function __construct($work_num = 1)
    {
        $this->work_num = $work_num;
    }

    /**
     * 任务启动
     * @param $callable - 回调函数
     * @param array $params - 回调函数的参数
     * @return bool
     */
    public function start($callable,$params = array())
    {
        Debug::log('多进程任务开始');

        $this->start_time = microtime(true);

        for ($i=0;$i<$this->work_num;$i++)
        {
            $this->startWorker($callable,$params);
        }

        $this->wait();

        $this->end_time = microtime(true);

        $exec_time = round($this->end_time - $this->start_time, 3);

        Debug::log('多进程任务结束 耗时'.$exec_time);

        return true;
    }

    public function startWorker($callable,$params=array())
    {
        $process = new \swoole_process(function (\swoole_process $worker) use ($callable,$params) {

            call_user_func_array($callable,$params);

            $worker->exit(0);
        });

        $child_pid = $process->start();

        Debug::log('创建子进程:'.$child_pid);

        $this->worker[$child_pid] = $process;
    }

    /**
     * 回收子进程
     */
    public function wait()
    {
        while(1) {

            if(count($this->worker)){

                $ret = \swoole_process::wait();

                if ($ret) {

                    Debug::log("回收子进程：".$ret['pid']);

                    unset($this->worker[$ret['pid']]);

                }

            }else{

                break;

            }
        }
    }
}


