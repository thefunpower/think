<?php 
/**
* 获取远程URL内容
*/
function get_remote_file($url,$is_json = false){
    $client = guzzle_http();
    $res    = $client->request('GET', $url);
    $res =  (string)$res->getBody(); 
    if($is_json){
        $res = json_decode($res,true);
    } 
    return $res;
}
/**
* 下载远程文件
*/
function download_remote_file($url,$path='',$name = ''){
    $name = $name?:'/uploads/tmp/'.md5($url).'.'.get_ext($url);
    $path = $path?:WWW_PATH;
    $file = $path.$name; 
    $context = get_remote_file($url);
    if(!file_exists($file)){  
        $dir = get_dir($file); 
        if(!is_dir($dir)){
            mkdir($dir,0777,true);
        }
        file_put_contents($file,$context);    
    } 
    return cdn_url().$name; 
}