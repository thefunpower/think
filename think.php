<?php 
/*
* Copyright (c) 2021-2031, All rights reserved.
* MIT LICENSE
*/ 
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