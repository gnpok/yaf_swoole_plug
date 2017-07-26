<?php
/**
 * yaf的公共方法
 */


/**
 * get获取参数 为了代码能兼容swwole模式和fpm模式
 * @param $name string  get参数名字
 * @param $default 参数若不存在赋予一个默认值
 * @param
 */
if(!function_exists('get')){
    function get($name = '',$default='',$func = ''){
        if(defined('IS_SWOOLE') && defined('HTTP_SERVER')){
            $httpserver = HTTP_SERVER;
            $gets = $httpserver::$get;
        }else{
            $gets = Yaf_Dispatcher::getInstance()->getRequest()->getQuery();
        }
        if(!empty($name)){
            $get_value = array_key_exists($name, $gets) ? $gets[$name] : $default;
            return empty($func) ? $get_value : call_user_func($func,$get_value);
        }
        return $gets;
    }
}

if(!function_exists('post')){
    function post($name = '',$default='',$func = ''){
        if(defined('IS_SWOOLE') && defined('HTTP_SERVER')){
            $httpserver = HTTP_SERVER;
            $posts = $httpserver::$post;
        }else{
            $posts = Yaf_Dispatcher::getInstance()->getRequest()->getPost();
        }
        if(!empty($name)){
            $post_value = array_key_exists($name, $posts) ? $posts[$name] : $default;
            return empty($func) ? $post_value : call_user_func($func,$post_value);
        }
        return $posts;
    }
}