<?php
use Article\Core\Load;

if($_FILES['file'] && $_FILES['file']['tmp_name']&&$_POST['data']['name']&&$_POST['data']['type'])
{
  $photo=Load::Photo();
  if($n=$photo->thumb($_POST['data']['name'],$_FILES['file']['tmp_name'],UPLOAD_FOLDER,$_POST['data']['width'],$_POST['data']['height'],$_POST['data']['fix'],$_POST['data']['type']))
  {
    //1024x1024
    $f = UPLOAD_PATH.'/'.$n;
    exec('/usr/bin/convert -strip -interlace Plane -sampling-factor 4:2:0 -define jpeg:dct-method=float -quality 85% '.$f.' '.$f);
    $status=['status'=>'OK','data'=>['n'=>$n,'w'=>$size[0],'h'=>$size[1],'s'=>filesize($f)]];
  }
}
?>
