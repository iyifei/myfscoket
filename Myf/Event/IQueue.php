<?php
namespace Myf\Event;

/**
 * 基础
 * User: myf
 * Date: 2018/4/2
 * Time: 09:29
 */
interface IQueue
{

    /**
     * 消息压入队列
     * @param $key
     * @param $msg
     * @return mixed
     */
    function push($key,$msg);

    /**
     * 弹出队列消息
     * @param $key
     * @return mixed
     */
    function pop($key);


    /**
     * 发布topic
     * @param $channel
     * @param $msg
     * @return mixed
     */
    function publish($channel,$msg);

    /**
     * 订阅topic
     * @param $channel
     * @param $callback
     * @return mixed
     */
    function subscribe($channel,$callback);


}