<?php
/**
 * redis配置
 * User: myf
 * Date: 2018/3/30
 * Time: 11:19
 */
return [
    'dsn' => array(
        'host'=>'localhost',
        'port'=>6379,
        'password'=>'123456',
        'db'=>1,
    ),
    //webSocket队列监听db
    'queue'=>1,
    //webSocket交换数据缓存db
    'cache'=>2,
];