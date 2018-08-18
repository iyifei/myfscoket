<?php
/**
 * redis实现的队列事件
 * User: myf
 * Date: 2018/4/2
 * Time: 09:43
 */

namespace Myf\Event;


use Myf\Libs\RedisClient;

class RedisQueue implements IQueue
{

    private function getRedis(){
        return RedisClient::getWebSocketQueueDB();
    }


    /**
     * 消息压入队列
     * @param $key
     * @param $msg
     * @return mixed
     */
    function push($key, $msg) {
        return $this->getRedis()->lPush($key,$msg);
    }

    /**
     * 弹出队列消息
     * @param $key
     * @return mixed
     */
    function pop($key) {
        return $this->getRedis()->lPop($key);
    }

    /**
     * 发布topic
     * @param $channel
     * @param $msg
     * @return mixed
     */
    function publish($channel, $msg) {
        return $this->getRedis()->publish($channel,$msg);
    }

    /**
     * 订阅topic
     * @param $channel
     * @param $callback
     * @return mixed
     */
    function subscribe($channel,$callback) {
        return $this->getRedis()->subscribe($channel,$callback);
    }
}