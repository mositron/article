<?php
use Article\Core\Load;

$f=false;
$log=[];
$path=UPLOAD_PATH;
if(is_dir($path))
{
  Load::Folder()->clean(UPLOAD_FOLDER);
  $status=['status'=>'OK','data'=>[]];
}
else
{
  $error='folder not found';
}

?>
