<?php
namespace Article\App\Www;
use Article\Core\Load;

class Home extends Service
{
  public function _home()
  {
    Load::cache();
    $news=[];
    $news[0]=$this->findAll('news',8,['rc'=>1]);
    foreach(Load::$core->data['cate'] as $k=>$v)
    {
      if(!$v['hide'])
      {
        $news[$k]=$this->find(['pl'=>1,'c'=>$k,'sv'=>DOMAIN],[],['limit'=>12]);
      }
    }
    return Load::$core
      ->assign('news',$news)
      ->fetch('www/home');
  }
}
?>
