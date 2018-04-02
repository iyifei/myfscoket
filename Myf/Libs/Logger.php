<?php
/**
 * 日志
 * User: myf
 * Date: 2018/4/2
 * Time: 10:49
 */

namespace Myf\Libs;


class Logger
{

    static $logger = null;

    public static function getInstance(){
        if(!isset(self::$logger)){
            self::$logger = new \Katzgrau\KLogger\Logger(APP_PATH.'/_logs');
        }
        return self::$logger;
    }

}