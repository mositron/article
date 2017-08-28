<?php
namespace Article\App\Www;
use Article\Core\Load;

class Rebuild extends Service
{
  public function _rebuild()
  {
    if(Load::$path[1]==Load::$conf['rebuild']['key'])
    {
      (new \Article\Core\Minify())->make_tpl();
      echo 'tpl - OK<br>';
      Load::$core->clear();
      echo 'cache - OK';
    }
  }
}
?>
