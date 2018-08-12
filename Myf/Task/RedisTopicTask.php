<?php
/**
 * redis消费订阅主题
 * User: myf
 * Date: 2018/4/3
 * Time: 05:21
 */

namespace Myf\Task;


use Myf\Libs\Logger;
use Myf\Libs\MemData;
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
        /**
         * Function:
         * 1.请描述方法的功能
         * 2.其他信息
         *
         * @param \Redis $redis redis
         * @param string $channel 订阅渠道
         * @param $msg
         *
         * @return void
         */
        $callback = function($redis, $channel, $msg){
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
        if($json['server']==$this->serverFlag){
            $db = config('redis.memDB');
            $this->logger->debug(sprintf("Receive db=%s",$db));
            $redis = RedisClient::getInstance($db);
            $fd = $redis->hGet($this->serverFlag,$json['uid']);
            $message = $json['message'];
            try{
                $this->logger->debug(sprintf("Receive Fd=%s",$fd));
                $res = $this->ws->push($fd,$message);
            }catch (\Exception $e){
                $res = false;
            }
            $this->logger->debug(sprintf("TOPIC=%s,fd=【%s】,msg=【%s】,result=%s",$data['channel'],$fd,$msg,intval($res)));
        }

    }
}