<?php
namespace Article\App\Www;
use Article\Core\Load;
use Article\App\Container;

class Service extends Container
{
  public static $arg_view=['_id'=>1,'no'=>1,'t'=>1,'fd'=>1,'da'=>1,'ds'=>1,'di'=>1,'do'=>1,'is'=>1,'mb'=>1,'dt'=>1,'tb'=>1,'c'=>1,'na'=>1,'d'=>1,'dm'=>1,'sv'=>1,'u'=>1,'un'=>1,'exl'=>1,'url'=>1,'tags'=>1,'sh'=>1];
  public function __construct(array $arg=[])
  {

  }

  public function find(array $cond=[],array $arg=[],array $sort=[]): ?array
  {
    if($n=Load::DB()->find('article',
      array_merge(['dd'=>['$exists'=>false]],$cond),
      array_merge(['_id'=>1,'no'=>1,'t'=>1,'fd'=>1,'da'=>1,'ds'=>1,'di'=>1,'de'=>1,'u'=>1,'un'=>1,'ue'=>1,'do'=>1,'c'=>1,'exl'=>1,'url'=>1,'dm'=>1,'sv'=>1,'pl'=>1,'is'=>1,'na'=>1],$arg),
      array_merge(['sort'=>['ds'=>-1],'skip'=>0,'limit'=>100],$sort)))
    {
      for($i=0;$i<count($n);$i++)
      {
        $n[$i]=$this->fetch($n[$i]);
      }
      return $n;
    }
    return null;
  }

  public function findAll(string $type,int $limit=13,array $arg=[]): array
  {
    $db=Load::DB();
    if(!empty($arg['rc']))
    {
      $arg['ds']=['$gte'=>Load::Time()->now(-3600*48)];
    #  $arg['ds.milliseconds']=['$gte'=>((time()-(3600*48))*1000)];
    }
    $arg['pl']=1;
    #$arg['dd']=['$exists'=>false];
    /*
    if($adver=$db->find('ads',['dd'=>['$exists'=>false],'pl'=>1,'ty'=>'advertorial','boxza.'.$type=>['$exists'=>true],'dt1'=>['$lte'=>Load::Time()->now()],'dt2'=>['$gte'=>Load::Time()->now()]],[],['sort'=>['so'=>1,'_id'=>1]]))
    {
      $advs=[];
      $nin=[];
      for($j=0;$j<count($adver);$j++)
      {
        $tmp=$adver[$j];
        if($adv=$this->find(['_id'=>$tmp['content']],[],['limit'=>1]))
        {
          $d=strtr(base64_encode(json_encode(['i'=>$tmp['_id'],'l'=>$adv[0]['link'],'t'=>time()])), '+/', '-_');
          $adv[0]['cls']='n-ads';
          $adv[0]['pr']='https://code.jarm.com/click/?__b='.urlencode($d);
          $advs[]=$adv[0];
          $nin[]=$tmp['content'];
        }
      }
      if(count($nin))
      {
        $arg['_id']=['$nin'=>$nin];
      }
      $ct=$this->find($arg,[],['limit'=>$limit]);
      if(count($advs))
      {
        $ct=array_slice(array_merge((array)$advs,(array)$ct),0,$limit);
      }
    }
    else
    {
    */
    #print_r($arg);

    #$ct=Load::DB()->find('article',$arg,['ds'=>1]);
        $ct=$this->find($arg,[],['limit'=>$limit]);
    //}

    #print_r($ct);
    #exit;
    for($i=0;$i<count($ct);$i++)
    {
      if($ct[$i]['cls'])
      {
        $ct[$i]['cls'].=' hot';
      }
      else
      {
        $ct[$i]['cls']='hot';
      }
    }
    if(count($ct)<$limit)
    {
      unset($arg['rc'],$arg['ds']);
      $arg['_id']=['$nin'=>[]];
      for($i=0;$i<count($ct);$i++)
      {
        $ct[$i]['cls'].=' n-gt-'.$limit;
        $arg['_id']['$nin'][]=$ct[$i]['_id'];
      }
      $ct2=$this->find($arg,[],['limit'=>$limit-count($ct)]);
      for($i=0;$i<count($ct2);$i++)
      {
        $ct2[$i]['cls']='n-findA-sec n-gt-'.count($ct2);
      }
      return array_merge((array)$ct,(array)$ct2);
    }
    return $ct;
  }

  public function _news()
  {
    if(!$id=intval(Load::$path[1]))
    {
      return ['move'=>'/'];
    }
    return ['move'=>'/view/'.$id];
  }

  public function _view()
  {
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
    $_ = ['dd'=>['$exists'=>false],'pl'=>1,'c'=>$cid];
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

  public function fetch(array $n): ?array
  {
    $img='https://'.$n['sv'].'/files/'.$n['fd'].'/';
    $fig=($n['sv']==DOMAIN?'':'https://'.$n['sv']).'/files/'.$n['fd'].'/';
    $cate=[];
    if($n['sv']==DOMAIN)
    {
      foreach ((array)$n['c'] as $c)
      {
        $cate[]=Load::$core->data['cate'][$c]['t'];
      }
    }
    return array_merge($n,[
                'title'=>$n['t'],
                'link'=>$n['pr']?:$this->link($n),
                'url'=>$n['pr']?:(($n['sv']==DOMAIN?'':'https://'.$n['sv']).'/view/'.$n['no']),
                'sec'=>Load::Time()->sec($n['ds']),
                'ago'=>Load::Time()->from($n['ds'],'ago'),
                'cate'=>implode(', ',$cate),
                'pl'=>($n['pl']?:0),
                'do'=>($n['do']?:0),
                'is'=>($n['is']?:0),
                'img'=>'/files/'.$n['fd'].'/small.jpg',
                'img_s'=>$img.'small.jpg',
                'img_t'=>$img.'thumbnail.jpg',
                'img_m'=>$img.'original.jpg',
                'fig_s'=>$fig.'small.jpg',
                'fig_t'=>$fig.'thumbnail.jpg',
                'fig_m'=>$fig.'original.jpg'
    ]);
  }

  public function link(array $n): string
  {
    return 'https://'.$n['sv'].'/view/'.$n['no'];
  }
}
?>
