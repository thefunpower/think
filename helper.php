<?php 
/*
* Copyright (c) 2021-2031, All rights reserved.
* MIT LICENSE
*/ 
include __DIR__.'/request.php'; 
include __DIR__.'/think.php'; 
/**
 * 尝试多次运行
 * $times 运行次数
 * $usleep_time 毫秒  
 */
function call_retry($func,$times=3,$usleep_time = 1000){
    $res = $func(); 
    if(is_array($res) && strtoupper($res['flag']) == 'OK'){
        return;
    } 
    $times--;
    if($times > 0){
        usleep($usleep_time*1000);
        call_retry($func,$times,$usleep_time);
    } 
}

/**
 * 数组转tree 
 * 
 * 输入$list 
 * [
 *   {id:1,pid:0,其他字段},
 *   {id:2,pid:1,其他字段},
 *   {id:3,pid:1,其他字段},
 * ]
 * 输出 
 * [
 *   [
 *      id:1,
 *      pid:0,
 *      其他字段,
 *      children:[
 *           {id:2,pid:1,其他字段},
 *           {id:3,pid:1,其他字段},
 *      ]
 *   ]
 * ]
 * 
 */
function array_to_tree($list, $pk = 'id', $pid = 'pid', $child = 'children', $root = 0, $my_id = '')
{
    $tree = array();
    if (is_array($list)) {
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] = &$list[$key];
        }
        foreach ($list as $key => $data) {
            $parentId = $data[$pid];
            if ($root == $parentId) {
                $tree[$data[$pk]] = &$list[$key];
            } else {
                if (isset($refer[$parentId])) {
                    $parent = &$refer[$parentId];
                    if ($my_id && $my_id == $list[$key]['id']) {
                    } else {
                        $parent[$child][] = &$list[$key];
                    }
                }
            }
        }
    }
    return $tree;
}

/**
* 数组或字符输出，方便查看
*/
function pr($str)
{
    print_r("<pre>");
    print_r($str);
    print_r("</pre>");
}
/**
 * 添加动作
 * @param string $name 动作名
 * @param couser $call function
 * @version 1.0.0
 * @author sun <sunkangchina@163.com>
 * @return mixed
 */
if(!function_exists("add_action")){
    function add_action($name, $call,$level = 20)
    {
        global $_app;
        if (strpos($name, '|') !== false) {
            $arr = explode('|', $name);
            foreach ($arr as $v) {
                add_action($v, $call,$level);
            }
            return;
        }
        $_app['actions'][$name][] = ['func'=>$call,'level'=>$level];  
    }
}
/**
 * 执行动作
 * @param  string $name 动作名
 * @param  array &$par  参数
 * @version 1.0.0
 * @author sun <sunkangchina@163.com>
 * @return  mixed
 */
if(!function_exists("do_action")){
    function do_action($name, &$par = null)
    {
        global $_app;
        if (!is_array($_app)) {
            return;
        }
        if(!isset($_app['actions'][$name])){return;}
        $calls  = $_app['actions'][$name]; 
        $calls  = lib\Arr::order_by($calls,'level',SORT_DESC);  
        if ($calls) {
            foreach ($calls as $v) {
                $func = $v['func'];
                $func($par);
            }
        }
    }
} 


/**
 * 判断是命令行下
 */
if(!function_exists('is_cli')){
	function is_cli()
	{
	    return PHP_SAPI == 'cli' ? true : false;
	}
}
/**
 * 是否是POST请求
 */
if(!function_exists('is_post')){
	function is_post()
	{
	    if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
	        return true;
	    }
	}
}
/**
 * 判断是否为json 
 */
if(!function_exists('is_json')){
   function is_json($data, $assoc = false)
   { 
        $data = json_decode($data, $assoc);
        if ($data && (is_object($data)) || (is_array($data) && !empty(current($data)))) {
            return $data;
        }
        return false;
   } 
}


/**
 * 数组转对象
 *
 * @param array $arr 数组
 * @return object
 */
if(!function_exists('array_to_object')){
	function array_to_object($arr)
	{
	    if (gettype($arr) != 'array') {
	        return;
	    }
	    foreach ($arr as $k => $v) {
	        if (gettype($v) == 'array' || getType($v) == 'object') {
	            $arr[$k] = (object) array_to_object($v);
	        }
	    }
	    return (object) $arr;
	}
}

/**
 * 对象转数组
 *
 * @param object $obj 对象
 * @return array
 */
if(!function_exists('object_to_array')){
	function object_to_array($obj)
	{
	    $obj = (array) $obj;
	    foreach ($obj as $k => $v) {
	        if (gettype($v) == 'resource') {
	            return;
	        }
	        if (gettype($v) == 'object' || gettype($v) == 'array') {
	            $obj[$k] = (array) object_to_array($v);
	        }
	    }
	    return $obj;
	}
}

/**
 * 取目录名
 *
 * @param string $name
 * @version 1.0.0
 * @author sun <sunkangchina@163.com>
 * @return void
 */
if(!function_exists('get_dir')){
	function get_dir($name)
	{
	    return substr($name, 0, strrpos($name, '/'));
	}
}
/**
 * 取后缀
 *
 * @param string $name
 * @version 1.0.0
 * @author sun <sunkangchina@163.com>
 * @return void
 */
if(!function_exists('get_ext')){
	function get_ext($name)
	{
	    if (strpos($name, '?') !== false) {
	        $name = substr($name, 0, strpos($name, '?'));
	    }
	    $name =  substr($name, strrpos($name, '.'));
	    return strtolower(substr($name, 1));
	}
}
/**
 * 取文件名
 *
 * @param string $name
 * @version 1.0.0
 * @author sun <sunkangchina@163.com>
 * @return void
 */
if(!function_exists('get_name')){
	function get_name($name)
	{
	    $name = substr($name, strrpos($name, '/'));
	    $name = substr($name, 0, strrpos($name, '.'));
	    $name = substr($name, 1);
	    return $name;
	}
}


/**
 * 创建目录 
 */
if(!function_exists('create_dir_if_not_exists')){
	function create_dir_if_not_exists($arr)
	{
	    if(is_string($arr)){
	        $v = $arr;
	        if (!is_dir($v)) {
	            mkdir($v, 0777, true);
	        }
	    }else if(is_array($arr)){
	        foreach ($arr as $v) {
	            if (!is_dir($v)) {
	                mkdir($v, 0777, true);
	            }
	        }
	    } 
	}
}


/**
 * 是否是本地环境
 * @version 1.0.0
 * @author sun <sunkangchina@163.com>
 * @return boolean
 */
if(!function_exists('is_local')){
	function is_local()
	{
	    return in_array(get_ip(), ['127.0.0.1', '::1']) ? true : false;
	}
}

/**
 * 取IP 
 */
if(!function_exists('get_ip')){
	function get_ip($type = 0, $adv = false)
	{
	    $type      = $type ? 1 : 0;
	    static $ip = null;
	    if (null !== $ip) {
	        return $ip[$type];
	    }

	    if ($adv) {
	        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
	            $pos = array_search('unknown', $arr);
	            if (false !== $pos) {
	                unset($arr[$pos]);
	            }

	            $ip = trim($arr[0]);
	        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
	            $ip = $_SERVER['HTTP_CLIENT_IP'];
	        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
	            $ip = $_SERVER['REMOTE_ADDR'];
	        }
	    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
	        $ip = $_SERVER['REMOTE_ADDR'];
	    }
	    // IP地址合法验证
	    $long = sprintf("%u", ip2long($ip));
	    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
	    return $ip[$type];
	}
}
/**
 * 当前时间
 */
if(!function_exists('now')){
	function now()
	{
	    return date('Y-m-d H:i:s', time());
	}
}


/**
 * 计算两点地理坐标之间的距离
 * @param  Decimal $longitude1 起点经度
 * @param  Decimal $latitude1  起点纬度
 * @param  Decimal $longitude2 终点经度
 * @param  Decimal $latitude2  终点纬度
 * @param  Int     $unit       单位 1:米 2:公里
 * @param  Int     $decimal    精度 保留小数位数
 * @return Decimal
 */
if(!function_exists('get_distance')){
	function get_distance($longitude1, $latitude1, $longitude2, $latitude2, $unit = 2, $decimal = 2)
	{

	    $EARTH_RADIUS = 6370.996; // 地球半径系数
	    $PI = 3.1415926;

	    $radLat1 = $latitude1 * $PI / 180.0;
	    $radLat2 = $latitude2 * $PI / 180.0;

	    $radLng1 = $longitude1 * $PI / 180.0;
	    $radLng2 = $longitude2 * $PI / 180.0;

	    $a = $radLat1 - $radLat2;
	    $b = $radLng1 - $radLng2;

	    $distance = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
	    $distance = $distance * $EARTH_RADIUS * 1000;

	    if ($unit == 2) {
	        $distance = $distance / 1000;
	    }

	    return round($distance, $decimal);
	}
}


/**
 * 路径列表，支持文件夹下的子所有文件夹
 *
 * @param string $path
 * @version 1.0.0
 * @author sun <sunkangchina@163.com>
 * @return void
 */
if(!function_exists('get_deep_dir')){
	function get_deep_dir($path)
	{
	    $arr = array();
	    $arr[] = $path;
	    if (is_file($path)) {
	    } else {
	        if (is_dir($path)) {
	            $data = scandir($path);
	            if (!empty($data)) {
	                foreach ($data as $value) {
	                    if ($value != '.' && $value != '..') {
	                        $sub_path = $path . "/" . $value;
	                        $temp = get_deep_dir($sub_path);
	                        $arr  = array_merge($temp, $arr);
	                    }
	                }
	            }
	        }
	    }
	    return $arr;
	}
}

/**
 * 显示2位小数
 * @version 1.0.0
 * @author sun <sunkangchina@163.com>
 */
if(!function_exists('price_format')){
	function price_format($yuan,$dot = 2)
	{
	    return bcmul($yuan, 1 ,$dot);
	} 
} 

/**
 * 返回错误信息，JSON格式 
 */
if(!function_exists('json_error')){
	function json_error($arr = [],$is_array = false)
	{
	    $arr['code'] = $arr['code'] ?: 250;
	    $arr['type'] = $arr['type'] ?: 'error';
	    if($is_array){
	        return $arr;
	    }
	    return json($arr);
	}
}


/**
 * 返回成功信息，JSON格式 
 */
if(!function_exists('json_success')){
	function json_success($arr = [],$is_array = false)
	{
	    $arr['code'] = $arr['code'] ?: 0;
	    $arr['type'] = $arr['type'] ?: 'success';
	    if($is_array){
	        return $arr;
	    }
	    return json($arr);
	} 
} 



/**
 * yaml转数组
 *
 * @param string $str
 * @version 1.0.0
 * @author sun <sunkangchina@163.com>
 * @return array
 */
if(!function_exists('yaml_load')){
	function yaml_load($str)
	{
	    return Symfony\Component\Yaml\Yaml::parse($str);
	}
}
/**
 * 数组转yaml
 *
 * @param array $array
 * @param integer $line
 * @version 1.0.0
 * @author sun <sunkangchina@163.com>
 * @return string
 */
if(!function_exists('yaml_dump')){
	function yaml_dump($array, $line = 3)
	{
	    return Symfony\Component\Yaml\Yaml::dump($array, $line);
	}
}
/**
 * yaml转数组，数组转yaml格式
 *
 * @param string $str
 * @version 1.0.0
 * @author sun <sunkangchina@163.com>
 * @return string|array
 */
if(!function_exists('yaml')){
	function yaml($str)
	{
	    if (is_string($str)) {
	        return yaml_load($str);
	    } else {
	        return yaml_dump($str);
	    }
	}
}

/**
 * AES加密 
 */
if(!function_exists('aes_encode')){
	function aes_encode($data, $key = '', $iv = '', $type = 'AES-128-CBC', $options = '')
	{    
		if(is_array($data)){$data = json_encode($data);}
	    $obj = new \lib\Aes($key, $iv, $type, $options);
	    return base64_encode($obj->encrypt($data));
	}
}
/**
 * AES解密   
 */
if(!function_exists('aes_decode')){
	function aes_decode($data, $key = '', $iv = '', $type = 'AES-128-CBC', $options = '')
	{
	    $data = base64_decode($data);
	    $obj = new \lib\Aes($key, $iv, $type, $options);
	    $data = $obj->decrypt($data);
	    if(is_json($data)){
	    	return json_decode($data,true);
	    }else{
	    	return $data;
	    }
	}
}


/**
 * 搜索替换\n , ，空格 >
 * @return array
 */
if(!function_exists('string_to_array')){
	function string_to_array($name,$array = '')
	{
	    if (!$name) {
	        return [];
	    }
	    $array = $array?:[
	        "\n",
	        "，",
	        "、",
	        "|",
	        ",",
	        " ",
	        chr(10),
	    ];
	    foreach ($array as $str) {
	        if (strpos($name, $str) !== false) {
	            $name = str_replace($str, ',', $name);
	        }
	    }
	    if (strpos($name, ",") !== false) {
	        $arr = explode(",", $name);
	    }
	    if ($arr) {
	        $arr = array_filter($arr);
	        foreach ($arr as $k => $v) {
	            if (!is_array($v)) {
	                $arr[$k] = trim($v);
	            } else {
	                $arr[$k] = $v;
	            }
	        }
	    } else {
	        $arr = [trim($name)];
	    }
	    return $arr;
	}
}


/**
 * 返回两个时间点间的日期数组
 *
 * @param string $start 时间格式 Y-m-d
 * @param string $end   时间格式 Y-m-d
 * @return void
 */
if(!function_exists('get_dates')){
	function get_dates($start, $end)
	{
	    $dt_start = strtotime($start);
	    $dt_end   = strtotime($end);
	    while ($dt_start <= $dt_end) {
	        $list[] = date('Y-m-d', $dt_start);
	        $dt_start = strtotime('+1 day', $dt_start);
	    }
	    return $list;
	}
}
/**
 * 当前时间是周几
 */
if(!function_exists('get_date_china')){
	function get_date_china($date)
	{
	    $weekarray = array("日", "一", "二", "三", "四", "五", "六");
	    return $weekarray[date("w", strtotime($date))];
	}
}


/**
 * 多少时间之前
 */
if(!function_exists('timeago')){
	function timeago($time)
	{
	    if (strpos($time, '-') !== false) {
	        $time = strtotime($time);
	    }
	    $rtime = date("m-d H:i", $time);
	    $top   = date("Y-m-d H:i", $time);
	    $htime = date("H:i", $time);
	    $time  = time() - $time;
	    if ($time < 60) {
	        $str = '刚刚';
	    } elseif ($time < 60 * 60) {
	        $min = floor($time / 60);
	        $str = $min . '分钟前';
	    } elseif ($time < 60 * 60 * 24) {
	        $h   = floor($time / (60 * 60));
	        $str = $h . '小时前 ' . $htime;
	    } elseif ($time < 60 * 60 * 24 * 3) {
	        $d = floor($time / (60 * 60 * 24));
	        if ($d == 1) {
	            $str = '昨天 ' . $rtime;
	        } else {
	            $str = '前天 ' . $rtime;
	        }
	    } else {
	        $str = $top;
	    }
	    return $str;
	}
}

/**
 * 请求是否是AJAX
 */
if(!function_exists('is_ajax')){
	function is_ajax()
	{
		if(strpos($_SERVER['HTTP_CONTENT_TYPE'] , 'application/json')!==false){
			return true;
		}
	    if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") {
	        return true;
	    } else {
	        return false;
	    }
	}
}

/**
 * 包含文件 
 */
if(!function_exists('import')){
	function import($file, $vars = [], $check_vars = false)
	{
	    static $obj;
	    $key = md5(str_replace('\\', '/', $file));
	    if ($vars && $check_vars) {
	        $md5 = md5(json_encode($vars));
	        $key = $key . $md5;
	    }
	    if ($vars) {
	        extract($vars);
	    }
	    if (!isset($obj[$key])) {
	        if (file_exists($file)) {
	            include $file;
	            $obj[$key] = true;
	            return true;
	        } else {
	            return false;
	        }
	    } else {
	        return true;
	    }
	}
}


/**
 * 取reffer
 */
if(!function_exists('get_reffer')){
	function get_reffer($refer = '')
	{
	    $refer = $refer ?: $_SERVER['HTTP_REFERER'];
	    $refer = str_replace("http://", '', $refer);
	    $refer = str_replace("https://", '', $refer); 
	    return $refer;
	}
}
/**
 * 取主域名，如 admin.baidu.com返回baidu.com
 */
if(!function_exists('get_root_domain')){
	function get_root_domain($host = '')
	{
	    $host = $host ?: host();
	    preg_match("#\.(.*)#i", $host, $match);
	    $host = $match[1];
	    return str_replace("/", '', $host);
	}
}
/**
 * 取子域名，如admin.baidu.com返回admin
 */
if(!function_exists('get_sub_domain')){
	function get_sub_domain($host = '')
	{
	    $host = $host ?: host();
	    preg_match("#(http://|https://)(.*?)\.#i", $host, $match);
	    $host = $match[2];
	    return str_replace("/", '', $host);
	}
}



/**
* 生成签名
签名生成的通用步骤如下：
第一步：将参与签名的参数按照键值(key)进行字典排序
第二步：将排序过后的参数，进行key和value字符串拼接
第三步：将拼接后的字符串首尾加上app_secret秘钥，合成签名字符串
第四步：对签名字符串进行MD5加密，生成32位的字符串
第五步：将签名生成的32位字符串转换为大写 
*/
if(!function_exists('sign_by_secret')){
	function sign_by_secret($params,$secret='',$array_encode = false){ 
	    $str = ''; 
	    //将参与签名的参数按照键值(key)进行字典排序
	    ksort($params); 
	    foreach ($params as $k => $v) { 
	        //将排序过后的参数，进行key和value字符串拼接
	        if(is_array($v) && $array_encode){
	            $v = json_encode($v,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
	        } 
	        $str .= "$k=$v";
	    } 
	    //将拼接后的字符串首尾加上app_secret秘钥，合成签名字符串
	    $str .= $secret; 
	    //对签名字符串进行MD5加密，生成32位的字符串
	    $str = md5($str);
	    //将签名生成的32位字符串转换为大写
	    return strtoupper($str);
	}
}

/**
* 处理ZIP
*/
/**
* 所本地文件解压到指定目录
*/
if(!function_exists('zip_extract')){
	function zip_extract($local_file,$extract_local_dir){ 
	    if(strpos($local_file,'/uploads/') !== false && strpos($local_file,'://') !== false){
	        $local_file = PATH.substr($local_file,strpos($local_file,'/uploads/')+1); 
	    }
	    if(!file_exists($local_file)){return false;}
	    $zippy = Alchemy\Zippy\Zippy::load();
	    $archive = $zippy->open($local_file);
	    if(!is_dir($extract_local_dir)){
	        create_dir_if_not_exists([$extract_local_dir]);
	    }
	    $archive->extract($extract_local_dir);
	}
}
/**
* 生成ZIP
* @param $local_zip_file 本地zip文件
* @param $files 包含的文件
*/
if(!function_exists('zip_create')){
	function zip_create($local_zip_file,$files = []){ 
	    $dir = get_dir($local_zip_file);
	    if(!is_dir($dir)){
	        create_dir_if_not_exists([$dir]);
	    } 
	    $zippy = Alchemy\Zippy\Zippy::load();
	    $archive = $zippy->create($local_zip_file, $files, true);
	    return str_replace(PATH,'',$local_zip_file);
	}
}

/**
* 判断是JSON请求
*/
if(!function_exists('is_json_request')){
	function is_json_request(){
	    if(is_ajax() || $_SERVER['HTTP_CONTENT_TYPE'] == 'application/json'
	        || $_SERVER['CONTENT_TYPE'] == 'application/json'
	    ){ 
	        return true;
	    }else{
	        return false;
	    }
	}
}

/** 
* 数组转el-select
*/
if(!function_exists('array_to_el_select')){
	function array_to_el_select($all,$v,$k){
	  $list = [];
	  foreach($all as $vv){
	    $list[] = ['label'=>$vv[$k],'value'=>$vv[$v]];
	  }
	  return $list;
	}
}

/**
* 生成图表
* https://echarts.apache.org/handbook/zh/how-to/chart-types/line/area-line
* 
echats(['id'=>'main1','width'=>600,'height'=>400],[
    'title'=>[
        'text'=>'ECharts 入门示例'
    ],
    'yAxis'=>"js:{}",
    'legend'=>[
        'data'=>['销量']
    ],
    'xAxis'=>[
        'data'=>['衬衫', '羊毛衫', '雪纺衫', '裤子', '高跟鞋', '袜子']
    ],
    'series'=>[
        [
            'name'=>'销量',
            'type'=>'bar',
            'data'=>[5, 20, 36, 10, 10, 20]
        ]
    ] 
]);
*/
if(!function_exists('echats')){
	function echats($ele,$options = []){ 
	    $ele_id = $ele['id'];
	    $width  = $ele['width'];
	    $height = $ele['height'];
	    $class  = $ele['class']; 
	    $echats = "var echart_".$ele_id." = echarts.init(document.getElementById('".$ele_id."'));\n
	    echart_".$ele_id.".setOption(".php_to_js($options).");"; 
	    $out['js'] = $echats;
	    $out['html'] = '<div id="'.$ele_id.'" class="'.$class.'" style="width: '.$width.'px;height:'.$height.'px;"></div>'."\n";
	    return $out;
	}
}

/**
* 设置允许字段
*/
function set_field_allow($collect,$field){
	global $_data_allow;
	$_data_allow[$collect] = $field;
}
/**
* 获取允许字段
*/
function get_field_allow($collect){
	global $_data_allow;
	$d = $_data_allow[$collect];
	$d[] = 'id';
	return $d;
}
/**
*返回允许字段值
*/
function get_data_allow($collect,$input)
{
	 global $_data_allow;
	 $new_input;
	 $field = $_data_allow[$collect];
	 foreach($input as $k=>$v){
	 	if($k == 'id' || in_array($k,$field)){
	 		$new_input[$k] = $v;
	 	}
	 }
	 return $new_input;
}