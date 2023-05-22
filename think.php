<?php 


function t_file_put($url,$path='',$name = ''){
	$name = $name?:'/uploads/tmp/'.md5($url).'.'.get_ext($url);
	$path = $path?:WWW_PATH;
    $file = $path.$name; 
    $client = guzzle_http();
    $res    = $client->request('GET', $url);
    $context =  (string)$res->getBody();  
    if(!file_exists($file)){  
        $dir = get_dir($file); 
        if(!is_dir($dir)){
            mkdir($dir,0777,true);
        }
        file_put_contents($file,$context);    
    } 
    return cdn_url().$name; 
}