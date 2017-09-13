<?php
namespace Article\App\Www;
use Article\Core\Load;

class Cate extends Service
{
  public function _cate($arg)
  {
    $c=[];
    $nav=[];
    if(!$cid=intval(Load::$path[1]))
    {
      return ['move'=>'/'];
    }
    if(Load::$path[2])
    {
      list($k,$page)=explode('-',Load::$path[2],2);
      if($k!='page' || !is_numeric($page))
      {
        return ['move'=>'/'];
      }
    }

    $t=Load::$core->data['cate'][$cid]['t'];
    $nav[]=['link'=>'/cate/'.$cid,'title'=>Load::$core->data['cate'][$cid]['t']];

    Load::cache(3600,3);
    Load::$core->data['title']=$t.' | '.Load::$core->data['title'];
    Load::$core->data['description']=$t.' | '.Load::$core->data['description'];
    Load::$core->data['keywords']=$t.', '.Load::$core->data['keywords'];

    $db=Load::DB();
    $_ = ['dd'=>['$exists'=>false],'pl'=>1,'c'=>$cid,'sv'=>DOMAIN];
    if($count=$db->count('article',$_))
    {
      list($pg,$skip)=Load::Pager()->navigation(80,$count,['/cate/'.$cid,'/page-'],$page);
      $news=$this->find($_,[],['skip'=>$skip,'limit'=>80]);
    }
    return Load::$core
      ->assign('news',$news)
      ->assign('nav',$nav)
      ->assign('pager',$pg)
      ->fetch('www/list');
  }
}
?>
