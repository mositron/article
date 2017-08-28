<?php

// set start time
define('START',microtime(true));

// use gzip compression
if(!ob_start("ob_gzhandler"))
{
  ob_start();
}

// check user-agent
if(empty(trim($_SERVER['HTTP_USER_AGENT'])))
{
  exit;
}

//Global Constant
define('ROOT',realpath('../').'/');
define('_PHP',ROOT.'php/');
define('_FILES',ROOT.'files/');
define('__APP',_PHP.'App/');
define('__CONF',_PHP.'Conf/');
define('__CORE',_PHP.'Core/');
define('__TPL',_PHP.'Tpl/');

//Reponse to html
header('Content-type: text/html; charset=utf-8');

/**
 * แสดงข้อผิดพลาดทั้งหมด
 * เฉพาะตอนพัฒนา
 * E_ALL
*/
error_reporting(E_ALL & ~E_NOTICE);

/**
 * แสดงข้อผิดพลาดที่สำคัญ ให้ละเอียดยิ่งขึ้น เพื่อง่ายต่อการไล่แก้ไข
 * E_ALL & ~E_NOTICE
*/
set_error_handler(function($no,$str,$file,$line){
  debug_print_backtrace();
  echo $no.' / '.str_replace(_PHP,'',$str).' / '.$line.' / '.str_replace(_PHP,'',$file);
  return true;
},E_ALL & ~E_NOTICE);

//Autoload function
spl_autoload_register(function($c)
{
  require_once(_PHP.str_replace('\\','/',explode('\\',$c,2)[1]).'.php');
});

//define('DOMAIN', strtolower($_SERVER['HTTP_HOST']));

//Start App.
Article\Core\Load::Init([
      'type'=>'website',
      'image'=>'/files/img/logo.png',
      'image_type'=>'image/png',
      'content'=>'',
  ])
  ->route([])
  ->run();

?>
