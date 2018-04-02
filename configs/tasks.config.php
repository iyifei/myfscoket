<?php
/**
 * 任务
 * User: myf
 * Date: 2018/4/3
 * Time: 05:19
 */

return [
    //自动下发计划任务
    [
        'name'=>'MonitorServerMessage',
        //消费队列数量,订阅topic只能开启一个进程
        'workNum'=>1,
        //任务
        'taskParam'=>[
            //队列关键key
            'channels'=>['Server_to_Terminal_Topic'],
        ],
        //执行任务类
        'taskClass'=>\Myf\Task\RedisTopicTask::class,
    ],
];