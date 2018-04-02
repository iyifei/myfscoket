<?php
/**
 * 全局方法
 * User: myf
 * Date: 2018/3/29
 * Time: 14:47
 */


/**
 * 单层遍历
 * @param $dir
 * @return array
 */
function dir_files($dir){
    $files = [];
    if(is_dir($dir)){
        $handler = opendir($dir);
        while( ($filename = readdir($handler)) !== false )
        {
            $file = $dir . '/' . $filename;
            //略过linux目录的名字为'.'和‘..'的文件
            if($filename != "." && $filename != ".." && is_file($file))
            {
                $item = [ 'file' => $file, 'name' => $filename ];
                $files[] = $item;
            }
        }
        closedir($handler);
    }
    return $files;
}

/**
 * 读取配置文件内容
 * @param string $name
 * @return null
 */
function config($name = null) {
    global $_gblConfig;
    $nameArr = explode('.', $name);
    $fName = current($nameArr);
    $res = null;
    if (isset($_gblConfig[$fName])) {
        unset($nameArr[0]);
        $res = $_gblConfig[$fName];
        foreach ($nameArr as $ne) {
            if (isset($res[$ne])) {
                $res = $res[$ne];
            } else {
                $res = null;
                break;
            }
        }
    }
    return $res;
}

/**
 * 获取当前时间毫秒数
 * @return float
 */
function getMillisecond() {
    list($s1, $s2) = explode(' ', microtime());
    return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
}

/**
 * 获取当前时间
 * @return bool|string
 */
function getCurrentTime() {
    return date("Y-m-d H:i:s");
}

/**
 * 获取客户端IP
 * @return null
 */
function getClientIP() {
    static $ip = null;
    if ($ip !== null) {
        return $ip;
    }
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos = array_search('unknown', $arr);
        if (false !== $pos) unset($arr[$pos]);
        $ip = trim($arr[0]);
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $ip = (false !== ip2long($ip)) ? $ip : '0.0.0.0';
    return $ip;
}



/**
 * GET请求
 * @param String $name 变量
 * @param $default 默认值
 * @return value
 */
function get($name, $default = null) {
    if (isset($_GET[$name])) {
        $value = trim($_GET[$name]);
    } else {
        $value = $default;
    }
    return $value;
}


/**
 * 读取POST值
 * @param String $name 变量
 * @param $default 默认值
 * @return value
 */
function post($name, $default = null) {
    if (isset($_POST[$name])) {
        $value = trim($_POST[$name]);
    } else {
        $value = $default;
    }
    return $value;
}


/**
 * 读取请求数据
 * @param String $name
 * @param $default null
 * @return value
 */
function request($name, $default = null) {
    if (isset($_REQUEST[$name])) {
        $value = trim($_REQUEST[$name]);
    } else {
        $value = $default;
    }
    return $value;
}


/**
 * 获取Integer变量
 * @param String $name
 * @param $default null
 * @return NULL|number
 */
function getInteger($name, $default = null) {
    if (isset($_REQUEST[$name]) && is_numeric($_REQUEST[$name])) {
        $value = intval($_REQUEST[$name]);
    } else {
        $value = $default;
    }
    return $value;
}

/**
 * 获取Double变量
 * @param String $name
 * @param $default null
 * @return NULL|number
 */
function getDouble($name, $default = null) {
    if (isset($_REQUEST[$name]) && is_numeric($_REQUEST[$name])) {
        $value = doubleval($_REQUEST[$name]);
    } else {
        $value = $default;
    }
    return $value;
}


/**
 * 字符串加密
 * @param string $original
 * @param string $secret 秘钥
 * @return string
 */
function encodePassword($original, $secret = 'ZqK2etJM') {
    $encoder = md5($secret . md5(base64_encode($original . "_myf_yht")));
    return $encoder;
}

/**
 * 读取一个header的值
 * @param $name
 * @param null $headers
 * @return null
 */
function getHeader($name, $headers = null) {
    if (!isset($headers)) {
        $headers = getAllHeaders();
    }
    if (isset($headers[$name])) {
        return $headers[$name];
    } else {
        return null;
    }
}

/**
 * 读取所有的header信息
 * @return array
 */
function getAllHeaders() {
    $headers = [];
    foreach ($_SERVER as $name => $value) {
        if (substr($name, 0, 5) == 'HTTP_') {
            $headers[str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($name, 5))))] = $value;
        }
    }
    return $headers;
}



/**
 * 判断是否为https
 * @return bool
 */
function is_HTTPS() {
    if (!isset($_SERVER['HTTPS'])) return false;
    if ($_SERVER['HTTPS'] === 1) {  //Apache
        return true;
    } elseif ($_SERVER['HTTPS'] === 'on') { //IIS
        return true;
    } elseif ($_SERVER['SERVER_PORT'] == 443) { //其他
        return true;
    }
    return false;
}

/**
 * 判断是否在微信中
 * @return bool
 */
function isInWeixin(){
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    if (strpos($user_agent, 'MicroMessenger') === false) {
        // 非微信浏览器禁止浏览\
        return false;
    } else {
        return true;
    }
}

/**
 *      把秒数转换为时分秒的格式
 *      @param Int $times 时间，单位 秒
 *      @return String
 */
function secToTime($times){
    $result = '00:00:00.00';
    if ($times>0) {
        $hour = floor($times/3600);
        if($hour<10 && $hour>=0){
            $hour='0'.$hour;
        }
        $minute = floor(($times-3600 * $hour)/60);
        if($minute<10 && $minute>=0){
            $minute="0".$minute;
        }
        $second = floor((($times-3600 * $hour) - 60 * $minute) % 60);
        if($second<10){
            $second="0".$second;
        }
        //毫秒
        $mm = '00';
        $timeArr = explode('.',$times);
        if(count($timeArr)==2){
            $mm = $timeArr[1];
        }
        $result = $hour.':'.$minute.':'.$second.'.'.$mm;
    }
    return $result;
}

/**
 * 时间格式转为秒
 * @param String $time XX:XX:XX
 * @return int 秒
 */
function timeToSec($time){
    $sp = explode(":",$time);
    $sec = intval($sp[0])*3600+intval($sp[1])*60+intval($sp[2]);
    return $sec;
}

/**
 * 输出json格式，并退出
 * @param $response
 */
function exitJson($response){
   echoJson($response);
   exit;
}

/**
 * 输出json
 * @param $response
 */
function echoJson($response){
    header('Content-Type:application/json; charset=utf-8');
    if(is_array($response)){
        $response = json_encode($response);
    }
    echo $response;
}

/**
 * 字符串签名
 * @param $info
 * @param $token
 * @return string
 */
function signEncode($info,$token){
    return md5(sprintf("%s_%s",md5($info),$token));
}

/**
 * 获取服务器端ip
 * @return mixed
 */
function getServerIp(){
    $ips = swoole_get_local_ip();
    return reset($ips);
}