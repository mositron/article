<?php
use Article\Core\Load;

if($_FILES['file'] && $_FILES['file']['tmp_name'])
{
  $photo=Load::Photo();
  if($n=$photo->thumb('original',$_FILES['file']['tmp_name'],UPLOAD_FOLDER,1024,1024,'width','jpg'))
  {
    //1024x1024
    $f = UPLOAD_PATH.'/'.$n;
    $t = $photo->thumb('thumbnail',$f,UPLOAD_FOLDER,420,280,'bothtop','jpg');
    $s = $photo->thumb('small',$f,UPLOAD_FOLDER,150,100,'bothtop','jpg');
    $ft = UPLOAD_PATH.'/'.$t;
    $fs = UPLOAD_PATH.'/'.$s;
    exec('/usr/bin/convert -strip -interlace Plane -sampling-factor 4:2:0 -define jpeg:dct-method=float -quality 85% '.$f.' '.$f);
    exec('/usr/bin/convert -strip -interlace Plane -sampling-factor 4:2:0 -define jpeg:dct-method=float -quality 85% '.$ft.' '.$ft);
    exec('/usr/bin/convert -strip -interlace Plane -sampling-factor 4:2:0 -define jpeg:dct-method=float -quality 85% '.$fs.' '.$fs);
  }
}

if($_POST['data']['_id'])
{
  $id=intval($_POST['data']['_id']);
  unset($_POST['data']['_id']);
  if($_POST['data']['dd'])
  {
    Load::DB()->update('article',['_id'=>$id],['$set'=>['dd'=>Load::Time()->now()]]);
  }
  else
  {
    if($_POST['data']['di'])
    {
      $_POST['data']['di']=Load::Time()->from($_POST['data']['di']);
    }
    if($_POST['data']['de'])
    {
      $_POST['data']['de']=Load::Time()->from($_POST['data']['de']);
    }
    if($_POST['data']['ds'])
    {
      $_POST['data']['ds']=Load::Time()->from($_POST['data']['ds']);
    }
    Load::DB()->update('article',['_id'=>$id],['$set'=>$_POST['data']],['upsert'=>true]);
  }
  Load::$core
    ->delete('home')
    ->delete('cate/'.intval($_POST['data']['c']))
    ->delete('view/'.intval($_POST['data']['no']));
  $status=['status'=>'OK'];
}
?>
