<?php
/**
 * 队列任务管理器
 * User: myf
 * Date: 2018/4/3
 * Time: 05:20
 */

namespace Myf\Libs;


use Myf\Task\BaseTask;

class WorkerManager
{

    //子进程，用于消费队列，处理任务
    protected $workers = [];
    //子进程数
    protected $atomic = null;
    //处理任务
    private $task;
    //主进程pid
    private $masterPid;

    public function __construct(BaseTask $task) {
        $this->task = $task;
        $this->masterPid = posix_getpid();
    }


    /**
     * 创建队列消费子进程
     * @param int $num 子进程数，范围1-1000
     * @param string $name 进程名称
     * @param bool $daemon 是否运行于后台
     */
    public function createWork($num,$name,$daemon=false){
        //最多可开启1000个子进程
        if($num<0){
            $num = 1;
        }
        if($num>1000){
            $num = 1000;
        }

        //后台守护进程
        if($daemon){
            \swoole_process::daemon();
        }

        //创建num个子进程
        $this->atomic = new \swoole_atomic(1);
        for($i=0;$i<$num;$i++){
            //创建子进程，并执行task的execute方法
            $childProcess = new \swoole_process(function (\swoole_process $worker){
                call_user_func(array($this->task,'execute'));
            },false,false);
            //设置进程名称
            //$childProcess->name($name);
            //启动子进程
            $pid = $childProcess->start();
            $this->workers[$pid]=$childProcess;
        }

        echo "[{$name}]任务成功启动{$num}个子进程,主进程pid:".$this->masterPid.PHP_EOL;

        //处理子进程异常关闭后，主进程接受到信号后，自动启动子进程
        \swoole_process::signal(SIGCHLD,function (){
            while (true){
                $exitChildProcess = \swoole_process::wait(false);
                if($exitChildProcess){
                    foreach ($this->workers as $k=>$childProcess){
                        if($childProcess->pid == $exitChildProcess['pid']){
                            if($this->atomic->get()==1){
                                $childProcess->start();
                            }else{
                                unset($this->workers[$k]);
                                if(count($this->workers)==0){
                                    swoole_event_exit();
                                }
                            }
                        }
                    }
                }else{
                    break;
                }
            }
        });

    }
}