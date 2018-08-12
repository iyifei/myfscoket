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
    //存储缓存fd与uid关系的
    'memDB'=>2,
];