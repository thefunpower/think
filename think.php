<?php 
/**
* 返回URL路径，不含有域名部分
*/
function get_url_remove_http($url){
    if(strpos($url,'://')===false){
        return $url;
    }
    $url = substr($url,strpos($url,'://')+3);
    $url = substr($url,strpos($url,'/'));
    return $url;
}
/**
* 取后缀
*/
function get_ext_by_url($url){
    $mime = lib\Mime::load();  
    $type = get_mime($url);
    if($type){
        foreach($mime as $k=>$v){
            if(is_array($v)){
                if(in_array($type,$v)){
                    $find = $k;
                    break;
                }
            }else{
                if($v == $type){
                    $find = $k;
                    break;
                }
            }
        }
    }    
    return $find;
}
/**
* 取mime
*/
function get_mime($url)
{ 
    if(strpos($url,'://')!==false){
        $type = get_headers($url,true)['Content-Type'];  
    }else{
        $type = mime_content_type($url); 
    }
    return $type;
}
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
* 下载文件
*/
function download_file($url,$contain_http = false){
    $host = cdn_url();
    if(strpos($url,"://") !== false){
        $url = download_remote_file($url);
        if($contain_http){
            return $url;
        }
        $url = str_replace($host,'',$url);
    }else if(strpos($url,WWW_PATH)!==false){
        $url = str_replace(WWW_PATH,'',$url);  
    } 
    if($contain_http){
        return $host.$url;
    }else{
        return $url;    
    }    
}
/**
* 下载远程文件
* global $remote_to_local_path;
* $remote_to_local_path = '/uploads/saved/'.date("Y-m-d");
*/
function download_remote_file($url,$path='',$name = ''){
    global $remote_to_local_path;
    $remote_to_local_path = $remote_to_local_path?:'/uploads/tmp/'.date("Y-m-d");
    $name = $name?:$remote_to_local_path.'/'.md5($url).'.'.get_ext_by_url($url);
    $path = $path?:WWW_PATH;
    $file = $path.$name;  
    if(!file_exists($file)){ 
        $context = get_remote_file($url);
        $dir = get_dir($file); 
        if(!is_dir($dir)){
            mkdir($dir,0777,true);
        }
        file_put_contents($file,$context);    
    } 
    return cdn_url().$name; 
}
