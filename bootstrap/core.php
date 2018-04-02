<?php
/**
 * 核心类
 */
error_reporting(E_ALL ^ E_NOTICE);
//统一编码为utf8
mb_internal_encoding('UTF-8');
//引入全局函数
require_once __DIR__.'/functions.php';
//引入autoload
require_once APP_PATH.'/vendor/autoload.php';
//读取配置文件
$iniFiles = dir_files(APP_PATH. '/configs/');
$iniOpFiles = @dir_files(OP_CONF_DIR);
$iniFiles = array_merge($iniFiles,$iniOpFiles);
global $_gblConfig;
foreach ($iniFiles as $iniFile) {
    if(!isset($_gblConfig)){
        $_gblConfig=[];
    }
    $file = $iniFile['file'];
    $fileArr = explode("/",$file);
    $fileName = end($fileArr);
    $fileNames = explode(".",$fileName);
    $c = count($fileNames);
    $cs = [];
    if($fileNames[$c-2]=='config'){
        unset($fileNames[$c-1]);
        unset($fileNames[$c-2]);
        $data = include $file;
        switch ($c){
            case 3:
                $cs[$fileNames[0]]=$data;
                break;
            case 4:
                $cs[$fileNames[0]][$fileNames[1]]=$data;
                break;
            case 5:
                $cs[$fileNames[0]][$fileNames[1]][$fileNames[2]]=$data;
                break;
        }
        $_gblConfig = array_merge_recursive($_gblConfig,$cs);
    }
}