<?php
/**
 * User: wuhangs
 * Date: 13-8-25
 * Time: 上午1:15
 * 入口文件
 */
//程序开始时间
$_SERVER['time'] = isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time();
$_SERVER['start_time'] = microtime(1);
$_SERVER['start_mem'] = memory_get_usage();

// 站点根目录，指向 public目录上一级
define("APP_PATH", dirname(__DIR__));

// 运行环境
define("ENV", ini_get('yaf.environ'));

/*实例化Bootstrap, 依次调用Bootstrap中所有_init开头的方法*/
$application = new Yaf_Application(APP_PATH . '/conf/application.ini');
$application->bootstrap()->run();
