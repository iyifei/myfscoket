<?php
/**
 * 队列动作
 */

namespace Myf\GEnum;


class QueueAction
{

    //终端连接上
    const Connect = 'connect';

    //来消息了
    const Message = 'message';

    //连接关闭
    const Close = 'close';

}