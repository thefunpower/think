<?php

/**
 * 消息模板
 */
if(!function_exists('get_template')) {
    function get_template($name, $replace_arr = [])
    {
        $file = PATH . '/template/' . $name . '.php';
        if(!file_exists($file)) {
            throw new Exception("模板文件不存在");
        }
        ob_start();
        include $file;
        $content = ob_get_contents();
        ob_end_clean();
        foreach($replace_arr as $k => $v) {
            $content = str_replace("{" . $k . "}", $v, $content);
        }
        return $content;
    }
}

/**
 *  获取接口请求数据，如果存在将不发起接口请求
 */
function get_api_data($title, $name)
{
    return db_get_one("api_data", "*", ['title' => $title,'name' => $name]);
}

/**
 * 记录接口请求数据
 */
function api_data_insert($data = [])
{
    $data = db_allow("api_data", $data);
    $data['created_at'] = now();
    db_insert("api_data", $data);
}


/**
 * 设置、获取cookie 
 */
if(!function_exists('cookie') && function_exists('xcookie')){
    function cookie($name, $value = '', $expire = 0)
    {
        return xcookie($name, $value, $expire);
    }
}
/**
 * 删除COOKIE 
 */ 
if(!function_exists('cookie_delete') && function_exists('xcookie_delete')){
    function cookie_delete($name)
    {
        return xcookie_delete($name);
    }
}