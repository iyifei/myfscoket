<?php
/**
 * WebSocket服务
 * User: myf
 * Date: 2018/4/2
 * Time: 09:13
 */


use Illuminate\Container\Container;
use Myf\Event\IQueue;
use Myf\Event\RedisQueue;
use Myf\GEnum\QueueKey;
use Myf\Libs\Logger;
use Myf\Libs\RedisClient;
use Myf\Libs\WorkerManager;
use Myf\Task\BaseTask;

date_default_timezone_set("Asia/Shanghai");
define("APP_PATH",__DIR__);
require_once APP_PATH.'/bootstrap/core.php';
//日志
$logger = Logger::getInstance();
//初始化容器
$container = Container::getInstance();
$container->bind(IQueue::class,RedisQueue::class);
//创建处理队列
/**
 * @var IQueue $queue
 */
$queue = $container->make(IQueue::class);

//webSocket服务配置
$wsConfig = config("swoole.WebSocket");
//创建WebSocket服务器对象
$ws = new swoole_websocket_server($wsConfig['host'], $wsConfig['port']);
$wsSet = $wsConfig['set'];
if (!empty($wsSet)) {
    $ws->set($wsSet);
}
$serverIp = getServerIp();
//唯一标识
$serverFlag = sprintf("%s-%s",$serverIp,$wsConfig['port']);
$ws->serverFlag = $serverFlag;

//redis访问
$memDB = config('redis.memDB');
$redis = RedisClient::getInstance($memDB);

//监听WebSocket连接事件
$ws->on("open", function ($ws, $request) use ($logger,$queue,$serverFlag,$redis){
    $fd = $request->fd;
    $get = isset($request->get)?$request->get:[];
    $uid =  md5(uniqid(microtime(true),true));
    $logger->debug(sprintf("WebSocket->open: fd=【%s】,uid=【%s】,serverFlag=【%s】, request=【%s】", $request->fd,$uid,$serverFlag, json_encode($get)));
    $refuse = true;
    if (!empty($get) && isset($get['token']) && isset($get['id'])) {
        //生成新的映射关系
        $redis->hSet($serverFlag,$uid,$fd);
        $redis->hSet($serverFlag,$fd,$uid);

        $refuse = false;
        $data = [
            'uid'=>$uid,
            'action'=>'connect',
            'server'=>$serverFlag,
            'data'=>$get,
        ];
        $queue->push(QueueKey::Terminal_to_Server_Once,json_encode($data));
    }

    if ($refuse) {
        $ws->close($fd);
    }
});


//监听WebSocket消息事件
$ws->on("message", function ($ws, $frame) use ($logger,$queue,$serverFlag,$redis){
    $fd = $frame->fd;
    $uid = $redis->hGet($serverFlag,$fd);
    $logger->debug(sprintf("WebSocket->message: fd=【%s】,uid=【%s】,data=【%s】", $fd,$uid, $frame->data));
    $data = [
        'uid'=>$uid,
        'action'=>'message',
        'server'=>$serverFlag,
        'data'=>$frame->data,
    ];
    $queue->push(QueueKey::Terminal_to_Server_Once,json_encode($data));

});

//监听WebSocket连接关闭事件
$ws->on("close", function ($ws, $fd, $reactorId) use ($logger,$queue,$serverFlag,$redis) {
    $uid = $redis->hGet($serverFlag,$fd);
    $logger->debug(sprintf("WebSocket->close: fd=【%s】,uid=【%s】,reactorId=【%s】", $fd,$uid, $reactorId));
    $data = [
        'fd'=>$fd,
        'action'=>'close',
        'server'=>$serverFlag,
    ];
    $queue->push(QueueKey::Terminal_to_Server_Once,json_encode($data));
});

//swoole启动成功事件
$ws->on("start", function ($ws) use ($serverFlag,$logger,$container,$redis) {
    $redis->del($serverFlag);

    $masterPid = $ws->master_pid;
    $logger->debug(sprintf("master_pid=%s,serverFlag=%s,started webSocket",$masterPid,$serverFlag));

    //监听topic队列
    //循环创建对应的任务
    $tasks = config("tasks");
    foreach ($tasks as $task){
        //绑定任务实现类
        $container->bind(BaseTask::class,$task['taskClass']);
        $container->when($task['taskClass'])->needs('$ws')->give($ws);
        if(isset($task['taskParam'])){
            $container->when($task['taskClass'])->needs('$taskParam')->give($task['taskParam']);
        }
        /**
         * @var WorkerManager $manager
         */
        $manager = $container->make(WorkerManager::class);
        //进程数
        $num = $task['workNum'];
        //任务名称
        $name = $task['name'];
        //启动进程
        $manager->createWork($num,$name);
    }

});

//启动
$ws->start();


