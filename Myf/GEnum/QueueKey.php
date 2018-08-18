<?php
namespace Myf\GEnum;

/**
 * 队列的全局键名
 * User: myf
 * Date: 2018/4/2
 * Time: 09:46
 */
class QueueKey
{

    /**
     * 终端给服务器的消费消息
     */
    const Terminal_to_Server_Once = 'Terminal_to_Server_Once';

    /**
     * 服务器给的终端消息
     */
    const Server_to_Terminal_Topic = 'Server_to_Terminal_Topic';

    //服务器与uid的关系
    const Server_Uid_Relation = "Server_Uid_Relation";

}