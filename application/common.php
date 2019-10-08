<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
if(!function_exists('getPost')){
    function getPost($param = ''){
        $request = \think\Request::instance();
        $res = $request->post($param);
        return $res;
    }
}

if(!function_exists('getGet')){
    function getGet($param = ''){
        $request = \think\Request::instance();
        $res = $request->get($param);
        return $res;
    }
}

if(!function_exists('handleType')){
    function handleType($array){
        $type = 0;
        foreach($array as $p){
            $type =$type | $p;
        }
        return $type;
    }
}

if(!function_exists('unHandleType')){
    function unHandleType($number){
        $type[] = [];
        ($number&1)==1?$type[0]=1:$type[0]='';
        ($number&2)==2?$type[1]=2:$type[1]='';
        ($number&4)==4?$type[2]=4:$type[2]='';
        ($number&8)==8?$type[3]=8:$type[3]='';
        return $type;
    }
}