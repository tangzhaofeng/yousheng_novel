<?php
define('APP_PATH', dirname(__FILE__)); //这个参数必须配置，是项目文件目录
header("Content-Type:text/html; charset=utf-8"); //编码
$_GET['c']=empty($_GET['c'])?"source":$_GET['c'];
$_GET['a']=empty($_GET['a'])?"index":$_GET['a'];
require_once(APP_PATH . '/initphp/initphp.php'); //导入框架
require_once(APP_PATH . '/conf/comm.conf.php'); //公用配置
InitPHP::import('library/helper/BaseUserController.php');
InitPHP::init(); //框架初始化