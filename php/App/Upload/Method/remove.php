<?php
use Article\Core\Load;

if($_POST['data']['_id'])
{
  Load::DB()->remove('article',['_id'=>intval($_POST['data']['_id'])]);
  Load::$core->delete('home');
  $status=['status'=>'OK'];
}
?>
