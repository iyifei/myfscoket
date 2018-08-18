<?php
/**
 * redis消费订阅主题
 * User: myf
 * Date: 2018/4/3
 * Time: 05:21
 */

namespace Myf\Task;


use Myf\GEnum\QueueAction;
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
        $redis = RedisClient::getWebSocketQueueDB();
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
            $hRedis = RedisClient::getWebSocketShareDB();
            $fd = $hRedis->hGet($this->serverFlag,$json['uid']);
            if($fd){
                try{
                    //关闭
                    if($json['action']==QueueAction::Close){
                        $res = $this->ws->close($fd);
                    }else{
                        $message = $json['message'];
                        $res = $this->ws->push($fd,$message);
                    }
                }catch (\Exception $e){
                    $res = false;
                }
                $this->logger->debug(sprintf("TOPIC=%s,fd=【%s】,msg=【%s】,result=%s",$data['channel'],$fd,$msg,intval($res)));
            }
        }

    }
}