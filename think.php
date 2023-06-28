<?php 
/*
* Copyright (c) 2021-2031, All rights reserved.
* MIT LICENSE
*/ 
/**
* 添加到队列
* "topthink/think-queue": "^3" 
* push_task('app\printer\job\Printer',['tt'=>11],'');
*/
function push_task($job, $data = '', $queue = ''){
    think\facade\Queue::push($job, $data , $queue );
}


