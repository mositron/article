<?php

// set start time
define('START',microtime(true));

//Global Constant
define('ROOT',realpath('../').'/');
define('_PHP',ROOT.'php/');
define('_FILES',ROOT.'files/');
define('__APP',_PHP.'App/');
define('__CONF',_PHP.'Conf/');
define('__CORE',_PHP.'Core/');
define('__TPL',_PHP.'Tpl/');

/**
 * แสดงข้อผิดพลาดทั้งหมด
 * เฉพาะตอนพัฒาน
*/
error_reporting(E_ALL & ~E_NOTICE);

/**
 * แสดงข้อผิดพลาดที่สำคัญ ให้ละเอียดยิ่งขึ้น เพื่อง่ายต่อการไล่แก้ไข
 * E_ALL & ~E_NOTICE
*/
set_error_handler(function($no,$str,$file,$line){
  #debug_print_backtrace();
  while(@ob_end_clean());
  header('Content-type: application/json');
  echo json_encode(['status'=>'FAIL',
  'message'=>$no.' / '.str_replace(_PHP,'',$str).' / '.$line.' / '.str_replace(_PHP,'',$file)
  ]);
  exit;
},E_ALL & ~E_NOTICE);

//Autoload function
spl_autoload_register(function($c)
{
  require_once(_PHP.str_replace('\\','/',explode('\\',$c,2)[1]).'.php');
});

#
Article\Core\Load::Init([])
  ->run(['upload'=>[]]);

?>
