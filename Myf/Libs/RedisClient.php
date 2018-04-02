<?php
/**
 * redis操作类
 * User: myf
 * Date: 2018/3/30
 * Time: 11:18
 */

namespace Myf\Libs;


class RedisClient
{

    private static $_instances = [];

    /**
     * 获取redis的单利
     * @param int $db
     * @return mixed|\Redis
     * @throws \Exception
     */
    public static function getInstance($db = null) {
        $config = config("redis.dsn");
        if(isset($db)){
            $db = intval($db);
            if ($db < 0) {
                throw new \Exception("invalid db idx: $db");
            }
        }else{
            $db = $config['db'];
        }
        if (array_key_exists($db, self::$_instances)) {
            $instance = self::$_instances[$db];
            // important: reset db idx
            $instance->select($db);
            return $instance;
        }
        $host = $config['host'];
        $port = $config['port'];
        $password = $config['password'];
        $instance = new \Redis();
        $instance->connect($host, $port, 2);
        $instance->auth($password);
        $instance->select($db);
        self::$_instances[$db] = $instance;
        return $instance;
    }


}