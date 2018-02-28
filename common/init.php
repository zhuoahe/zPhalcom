<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-02-27
 * Time: 上午 9:13
 */

spl_autoload_register(function ($class){
    $classPath  = dirname(__DIR__) . '/' . $class . '.php';
    if(file_exists($classPath)){
        require $classPath;
    }
});
