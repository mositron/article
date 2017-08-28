<?php
namespace Article\App\Www;
use Article\Core\Load;

class Pull extends Service
{
  public function _pull()
  {
    if(Load::$path[1]!=Load::$conf['upload']['key'])
    {
      exit;
    }
    $now = intval(date('YmdHi'));

    $folder=Load::Folder();
    $echo=[];
    //echo 'NOW - '.$now.'<br>';
    $day=$this->getlist($inf=_FILES.'bin/'.DOMAIN.'/news-view/','/^(([0-9]+)\-([0-9]+)\-([0-9]+))$/iU');
    //echo '<pre>'.print_r($news,1).'</pre>';
    for($i=0;$i<count($day);$i++)
    {
      $cday=intval(str_replace('-','',$day[$i]));
      //echo 'day: '.$day[$i].'<br>';
      $hour = $this->getlist($inf.$day[$i].'/','/^([0-9]+)$/iU');
      for($j=0;$j<count($hour);$j++)
      {
        //echo 'hour: '.$hour[$j].'<br>';

        $minute = $this->getlist($inf.$day[$i].'/'.$hour[$j].'/','/^([0-9]+)$/iU');
        for($l=0;$l<count($minute);$l++)
        {
          $d = intval(str_replace('-','',$day[$i]).''.$hour[$j].''.$minute[$l]);
          if($now>$d) //
          {
            $news = $this->getlist($inf.$day[$i].'/'.$hour[$j].'/'.$minute[$l].'/','/^([0-9]+)$/iU');
            for($k=0;$k<count($news);$k++)
            {
              //echo 'news: '.$news[$k].'<br>';
              $imp = $this->getlist($inf.$day[$i].'/'.$hour[$j].'/'.$minute[$l].'/'.$news[$k].'/','/^(([0-9]+)\.txt)$/iU');
              //echo '<pre>'.print_r($imp,1).'</pre>';
              $log = ['do'=>0,'is'=>0,'mb'=>0,'tb'=>0,'dt'=>0];
              for($m=0;$m<count($imp);$m++)
              {
                $log2=(array)unserialize(file_get_contents($inf.$day[$i].'/'.$hour[$j].'/'.$minute[$l].'/'.$news[$k].'/'.$imp[$m]));
                foreach($log2 as $kl=>$vl)
                {
                  if($kl=='u')
                  {
                    $log['u']=intval($vl);
                  }
                  else
                  {
                    if(!isset($log[$kl]))
                    {
                      $log[$kl]=0;
                    }
                    $log[$kl]+=$vl;
                  }
                }
              }
              $echo[]=$cday.'-'.$hour[$j].'-'.intval($log['u']).'-'.intval($news[$k]).'-'.intval($log['do']).'-'.intval($log['is']).'-'.intval($log['mb']).'-'.intval($log['tb']).'-'.intval($log['dt']);
              $folder->clean('bin/'.DOMAIN.'/news-view/'.$day[$i].'/'.$hour[$j].'/'.$minute[$l].'/'.$news[$k]);
              usleep(1000);
            }
            usleep(1000);
            $folder->clean('bin/'.DOMAIN.'/news-view/'.$day[$i].'/'.$hour[$j].'/'.$minute[$l]);
          }
        }
        if(!count($minute))
        {
          $folder->clean('bin/'.DOMAIN.'/news-view/'.$day[$i].'/'.$hour[$j]);
        }
      }
      if(!count($hour))
      {
        $folder->clean('bin/'.DOMAIN.'/news-view/'.$day[$i]);
      }
    }
    echo "\r\n";
    for($i=0;$i<count($echo);$i++)
    {
      echo $echo[$i]."\r\n";
    }
    echo 'OK';
    exit;
  }

  function getlist($path,$pattern)
  {
    $f=[];
    if(is_dir($path))
    {
      if($dh=opendir($path))
      {
        while(($dir=readdir($dh))!==false)
        {
          if(preg_match($pattern,$dir,$file))
          {
            array_push($f,$file[1]);
          }
        }
        closedir($dh);
      }
    }
    sort($f);
    return $f;
  }
}
?>
