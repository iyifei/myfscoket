<?php

namespace Myf\Task;


/**
 * 基础任务
 * User: myf
 * Date: 2018/2/24
 * Time: 09:38
 */
interface BaseTask
{

    function execute();

    function trigger($data);

}