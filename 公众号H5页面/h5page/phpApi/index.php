<?php
/**
 * 入口文件.
 * User: zyy
 * Date: 2017/11/27
 * Time: 15:50
 */
require_once 'vendor/autoload.php';
$pathInfo = substr($_SERVER['REQUEST_URI'],strpos($_SERVER['REQUEST_URI'],'.')+5);
$class = 'App\\'.ucfirst(substr($pathInfo,0,strpos($pathInfo,'/')));
$method =substr($pathInfo,strpos($pathInfo,'/')+1);
if($pos =strpos($method,'?')){
    $method = substr($method,0,$pos);
}
$obj = new $class;
$obj->$method();

