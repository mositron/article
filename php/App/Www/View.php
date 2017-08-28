<?php
namespace Article\App\Www;
use Article\Core\Load;

class View extends Service
{
  public function _view()
  {
    if(!is_numeric(Load::$path[1]))
    {
      return ['move'=>'/'];
    }
    if(!$id=intval(Load::$path[1]))
    {
      return ['move'=>'/'];
    }

    Load::cache();
    $db=Load::DB();
    if(!$news=$db->findone('article',['no'=>$id,'dd'=>['$exists'=>false],'pl'=>['$gte'=>1]],self::$arg_view))
    {
      return ['move'=>'/'];
    }
    if($news['exl'])
    {
      return ['move'=>$news['url'],'stats'=>'news:'.$news['_id'].':do:'.$news['u']];
    }
    $news=$this->fetch($news);
    $ctitle=(array)$news['tags'];

    if(!is_array($news['c']))
    {
      $news['c']=[$news['c']];
    }
    $ncate=[];
    foreach($news['c'] as $v)
    {
      $ncate[$v]=Load::$core->data['cate'][$v]['t'];
    }

    Load::$core->data['stats']='news:'.$news['_id'].':do:'.$news['u'];
    Load::$core->data['title']=$news['t'];
    Load::$core->data['description']=$news['t'].' - '.implode(' ',array_values($ncate)).' '.implode(' ',$ctitle).' ข่าวล่าสุด ข่าววันนี้ ข่าวด่วน ข่าวเด่น';
    Load::$core->data['keywords']=implode(', ',$ctitle).', '.implode(', ',array_values($ncate));
    Load::$core->data['image']=$news['img_m'];
    Load::$core->data['image_type']='image/jpeg';
    Load::$core->data['type']='article';

    $arg=['_id'=>['$ne'=>$news['_id']],'c'=>array_keys($ncate)[0],'pl'=>1];
    $relate=$this->find($arg,[],['limit'=>6]);

    return Load::$core
      ->assign('news',$news)
      ->assign('ncate',$ncate)
      ->assign('relate',$relate)
      ->fetch('www/view');
  }
}
?>
