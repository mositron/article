<?php
namespace Article\App\Www;
use Article\Core\Load;

class Stats extends Service
{
  public function get_stats()
  {
    if(!$id=intval(Load::$path[1]))
    {
      exit;
    }
    Load::cache(3600,3);
    Load::$core->data['stats']='news:'.$id.':is:'.intval(Load::$path[2]);
    Load::$core->data['echo']='/* stats */';
  }
}
?>
