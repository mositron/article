<?php
namespace Article\App\Www;
use Article\Core\Load;

class News extends Service
{
  public function _news()
  {
    if(!$id=intval(Load::$path[1]))
    {
      return ['move'=>'/'];
    }
    return ['move'=>'/view/'.$id];
  }
}
?>
