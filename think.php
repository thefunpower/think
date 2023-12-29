<?php 
/**
* 对数组添加cdn url
*/
function set_url_use_cdn(&$row,$field = []){
    foreach ($field as $k){
        $v = $row[$k];
        if($v){
            if(is_array($v)){
                $new_v = [];
                foreach($v as $vv){
                    if(strpos($vv,'://') === false ){
                        $new_v[] = cdn_url().$vv;        
                    } else{
                        $new_v[] = $vv;
                    } 
                }
                 $row[$k] = $new_v;   
            }else {
                if(strpos($v,'://') === false){
                    $row[$k] = cdn_url().$v;        
                }
            }
            
        }
    }
}
/**
 * 消息模板
 */
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
* 添加到队列
* "topthink/think-queue": "^3" 
* push_task('app\printer\job\Printer',['tt'=>11],'');
*/
function push_task($job, $data = '', $queue = ''){
    think\facade\Queue::push($job, $data , $queue );
}
/**
* 向上取递归
* 如当前分类是3，将返回 123所有的值
* $arr = loop_tree("catalog",$v['catalog_id'],true);
* foreach($arr as $vv){
*   $title[] = $vv['title'];
* } 
* id pid 
* 1  0
* 2  1
* 3  2
*/
function loop_tree($table,$id,$is_frist = false){
  static $_data;
  if($is_frist){
    $_data = [];
  }
  $end = db_get($table,['id'=>$id],1); 
  $_data[] = $end;
  if($end['pid'] > 0){
    loop_tree($table,$end['pid']);
  }
  return array_reverse($_data);
}
/**
* 向下递归
*/
function get_loop_tree_ids($table,$id,$where=[],$get_field = 'id'){
  $list = db_get($table,$where);
  $tree = array_to_tree($list, $pk = 'id', $pid = 'pid', $child = 'children', $id);
  $tree[] = db_get($table,['id'=>$id],1);
  $all = _loop_tree_deep_inner($tree,$get_field,$is_frist=true);
  return $all;
}
/**
* 递归删除
*/
function loop_del($table,$id,$where = []){ 
    $catalog = db_get($table,$where);  
    $all = array_to_tree($catalog,$pk = 'id', $pid = 'pid', $child = 'children', $id);
    $where['id'] = $id;
    $cur = db_get($table,$where,1);  
    db_del($table,['id'=>$id]); 
    _loop_del_tree($table,$all);
} 
/**
* 用于tree表格排序
* 因为tree给的lists字段是用于显示，在排序时得到的index是不正确的，
* 此时需要lists_sort字段得到正确的index
* 在交换排序值时才能正确保存并显示
*/
function el_table_tree($list,$j,$is_top=false){ 
  static $_data;  
  if($is_top){
    $_data = [];
  }
  foreach ($list as $v){
    $j++;
    $children = $v['children'];
    unset($v['children']);
    $_data[] = $v; 
    if($children){
        el_table_tree($children,$j++);
    } 
  }
  return $_data;
}
/**
 * 用于cascader字段
 * public function cascader(){ 
 *     $catalog = db_get($this->table,"*",[
 *      'ORDER'=>catalog_default_order_by(),
 *      'status'=>1
 *     ]); 
 *     $select = el_cascader(array_to_tree($catalog));   
 *     json_success(['data'=>$select]);
 * } 
 * $catalog = db_get("catalog",[]);  
 * $select = el_cascader(array_to_tree($catalog));  
 */
function el_cascader($select,$label='title',$value='id'){ 
  foreach($select as $v){
    $children = $v['children'];
    unset($v['children']); 
    $v['value'] = (string)$v[$value];
    $v['label'] = $v[$label];
    if($children){
      $v['children'] = el_cascader($children,$label,$value); 
    }
    $_array_tree_key_change_data[] = $v;
  }
  return $_array_tree_key_change_data;
} 

/**
* 以下代码不建议直接调用
*/
/**
* 内部实现
*/
function _loop_del_tree($table,$all){
    foreach($all as $v){
      db_del($table,['id'=>$v['id']]);
      if($v['children']){
        _loop_del_tree($table,$v['children']);
      } 
    }
}
/**
* 内部实现
*/
function _loop_tree_deep_inner($all,$get_field,$is_frist = false){
  static $_data;
  if($is_frist){
    $_data = [];
  }
  foreach($all as $v){ 
      $_data[] = $v[$get_field];
      if($v['children']){
        _loop_tree_deep_inner($v['children'],$get_field);
      }
  }
  return $_data;
}