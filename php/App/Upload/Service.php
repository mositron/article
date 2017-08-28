<?php
namespace Article\App\Upload;
use Article\Core\Load;
use Article\App\Container;

class Service extends Container
{
  public function __construct(array $arg=[])
  {

  }

  public function get_home()
  {
    exit;
  }

  public function post_home()
  {
    $error=false;
    $status =['status'=>'FAIL','message'=>'รหัสไม่ถูกต้อง'];
    if($_POST['key']==md5($_POST['method'].Load::$conf['upload']['key'].$_POST['data']))
    {
      $status['message']='ข้อมูลไม่ถูกต้อง';
      switch($_POST['method'])
      {
        case 'post':
        case 'list':
        case 'delete':
        case 'upload':
          $_POST['data']=json_decode($_POST['data'],true);
          if($_POST['data']['sv']&&$_POST['data']['fd'])
          {
            define('DOMAIN',$_POST['data']['sv']);
            define('UPLOAD_FOLDER',$_POST['data']['sv'].'/'.$_POST['data']['fd']);
            define('UPLOAD_PATH',_FILES.UPLOAD_FOLDER);
            require_once(__DIR__.'/Method/'.$_POST['method'].'.php');
          }
          break;
      }
    }
    if($error)
    {
      $status['message']=$error;
    }
    header('Content-type: application/json');
    echo json_encode($status);
    exit;
  }
}

?>
