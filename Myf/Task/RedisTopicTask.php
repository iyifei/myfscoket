<?php
/**
 * redis消费订阅主题
 * User: myf
 * Date: 2018/4/3
 * Time: 05:21
 */

namespace Myf\Task;


use Myf\Libs\Logger;
use Myf\Libs\RedisClient;

class RedisTopicTask implements BaseTask
{
    /**
     * @var \swoole_websocket_server $ws
     */
    private $ws;
    private $param;
    private $logger;
    private $serverFlag;

    function __construct($ws,$taskParam=[]) {
        $this->ws=$ws;
        $this->param = $taskParam;
        $this->logger = Logger::getInstance();
        $this->serverFlag = $ws->serverFlag;
    }

    function execute() {
        $redis = RedisClient::getInstance();
        $callback = function($redis, $channel, $msg) {
            $data = [
                'redis'=>$redis,
                'channel'=>$channel,
                'msg'=>$msg,
            ];
            $this->trigger($data);
        };
        $channels = $this->param['channels'];
        $redis->subscribe($channels,$callback);
    }

    function trigger($data) {
        $msg = $data['msg'];
        $json = json_decode($msg,true);
        $res = false;
        if($json['server']==$this->serverFlag){
            $fd = $json['fd'];
            $message = $json['message'];
            $res = $this->ws->push($fd,$message);
        }
        $this->logger->debug(sprintf("TOPIC=%s,msg=【%s】,result=%s",$data['channel'],$msg,intval($res)));

    }
}