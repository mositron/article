<?php

namespace Article\Core;

class Load
{
  /**
  * @var array $h
  * object
  */
  private static $h=[];

  /**
  * @var object $core = $this
  */
  public static $core;

  public static $first;

  public static $key;

  /**
  * @var array $path url split by "/"
  */
  public static $path;

  /**
  * @var array $conf // config from file
  */
  public static $conf=[];

  /**
  * @var object $app current app  (Article\App\...)
  */
  public static $app;

  /**
  * @var string $sub urrent sub-domain
  */
  public static $sub='';

  /**
  * @var array $map current config for sub-domain
  */
  public static $map=[];

  /**
  * @var array $data assign to template
  */
  public $data=[];

  /**
  * @var int $time current time for cache file
  */
  public static $time;

  /**
  * @var int $expire expire time for cache file
  */
  public static $cache;

  /**
  * @var string $request is strtolower($_SERVER['REQUEST_METHOD'])
  */
  public static $request;

  /**
  * ใช้แทน __construct เพื่อเก็บค่า self::$core
  * @param array $data ค่าเริ่มต้นของแอพ
  * @return Object $this
  */
  public static function Init(array $data=[])
  {
    self::$time=time();
    define('HOST',strtolower($_SERVER['HTTP_HOST']));
    define('URL',urldecode(parse_url(strtolower($_SERVER['REQUEST_URI']),PHP_URL_PATH)));
    define('URH','https://'.HOST);
    define('URI',URH.URL);
    self::$path=array_values(array_filter(explode('/',trim(URL,'/'))));
    self::$request=strtolower($_SERVER['REQUEST_METHOD']?:'get');
    self::$first=ucwords(str_replace(['-','.'],['_','_dot_'],trim(self::$path[0]?:'home','_')),'_');
    self::$key=strtolower(self::$first.'/'.implode('/',array_slice(self::$path,1)));

    $file=_FILES.'bin/'.HOST.'/cache/'.trim(self::$key,'/').'.php';
    if(self::$request=='get'&&file_exists($file))
    {
      $_=include($file);
      if(!empty($_['expire']) && $_['expire']>self::$time)
      {
        self::process($_['data']);
      }
    }
    self::$conf=require(__CONF.'global.php');
    return self::$core = new self($data);
  }

  /**
  * Magic
  * @param array $data is variable for data
  * @return Object
  */
  public function __construct(array $data=[])
  {
    $this->data=$data;
  }

  /**
  * Magic
  * call class in Article\Core namespace
  * example: self::DB() - new Article\Core\DB()
  * @param string $c is class name
  * @param string|array $n is variable for assign to __construct
  * @return Object
  */
  public static function __callStatic($c,$n)
  {
    $_=!empty($n)?md5(serialize($n)):'default';
    if(empty(self::$h[$c.'.'.$_]))
    {
      try
      {
        self::$h[$c.'.'.$_]=(new \ReflectionClass('Article\\Core\\'.$c))->newInstanceArgs($n);
      }
      catch(Exception $e)
      {
        var_dump($e->getMessage());
        exit;
      }
    }
    return self::$h[$c.'.'.$_];
  }

  /**
  * set config for all sub-domains
  * @param array $map sub-domain config
  * @return Load $this object, or redirect to main page if this sub-domain set "enable = false";
  */
  public function route(array $map): Load
  {
    //$subc=strlen(self::$conf['domain'])*-1;
    define('DOMAIN',str_replace('www.','',HOST));
    if(isset(self::$conf['domain'][DOMAIN]))
    {
      if(substr(HOST,0,4)=='www.')
      {
        Load::move('https://'.DOMAIN.URL,true);
      }
      self::$sub='www';
      $this->data=array_merge($this->data,self::$conf['domain'][DOMAIN]);
      return $this;
    }
    // domain name does not match
    die('page: not found');
  }

  /**
  * execute application
  * @param array $app [directory or file] to execute (if set)
  * @return Load $this object
  */
  public function run(array $app=[]): Load
  {
    if(count($app)>0)
    {
      self::$sub=array_keys($app)[0];
      self::$map=array_values($app)[0];
    }
    $app=ucwords(self::$map['app']??self::$sub,'_');
    $func=function()use($app)
    {
      if(is_null($serv=file_exists(__APP.$app.'/'.self::$first.'.php')?'\\'.self::$first:'\\Service'))
      {
        // if don't have app [directory or file]
        // redirect to main page
        return ['move'=>'https://'.DOMAIN];
      }
      $arg=(self::$map['arg']?:[]);
      self::$app=(new \ReflectionClass('\\Article\\App\\'.$app.$serv))->newInstanceArgs([$arg]);
      if(method_exists(self::$app,$p=self::$request.'_'.self::$first)||
          method_exists(self::$app,$p='_'.self::$first))
      {
        // call method by link name
        //$data=(array)(self::$app->{$p}($arg));
        $data=(self::$app->{$p}($arg));
        if(is_string($data))
        {
          $this->data['content']=$data;
        }
        elseif(is_array($data))
        {
          $this->data=array_merge($this->data,$data);
        }
        //if(self::$cache['expire']>0 && $this->data['content'])
        if($this->data['content'])
        {
          $this->data['content']=self::Minify()->minify_html($this->data['content']);
          $this->data['content']=$this->assign('data',$this->data)->fetch('global',true);
        }

        // merge data and return
        //return array_merge($this->data,$data);
        return $this->data;
      }
      else
      {
        //redirect to main path if don't have method
        return (self::$first!='home')?['move'=>'/']:[];
      }
    };

    // key for cache file
    // auto-gen by url
    $this->process($this->data=(self::$request=='get'?$this->get(self::$key,$func):$func()));
  }

  public static function process($data)
  {
    if($data['stats'])
    {
      self::stats($data['stats']);
    }
    if(isset($data['move']))
    {
      self::move($data['move']);
    }
    if(self::$request=='get' || !empty($data['content']))
    {
      if(!empty($data['content']))
      {
        echo $data['content'];
        //echo $this->assign('data',$data)->fetch('global',true);
        self::exit();
      }
      if(!empty($data['echo']))
      {
        echo $data['echo'];
      }
    }
    exit;
  }

  /**
  * display included files and memory usage, and exit
  */
  public static function exit(): void
  {
    echo "<!--\ninclude/require: ".count($f=get_included_files())." files\n";
    for($i=0;$i<count($f);$i++)
    {
      echo ($i+1).' - '.str_replace(ROOT,'/',$f[$i])."\n";
    }
    echo 'memory (real): '.number_format(memory_get_usage(true)/1024,0).' KB'."\n".
    'memory (emalloc): '.number_format(memory_get_usage(false)/1024,0).' KB'."\n".
    'memory peak (real): '.number_format(memory_get_peak_usage(true)/1024,0).' KB'."\n".
    'memory peak (emalloc): '.number_format(memory_get_peak_usage(false)/1024,0).' KB'."\n".
    'time: '.number_format((microtime(true)-START)*1000000,0).' µs.'."\n".'-->';
  }

  /**
  * redirect to new page
  * @param string|array $u url of new page
  * @param bool $m use 301 header
  */
  public static function move($u='/',bool $m=false): void
  {
    while(@ob_end_clean());
    if($m)header('HTTP/1.1 301 Moved Permanently');
    header('Location: '.$u);
    exit;
  }

  /**
  * Template
  * assign variable to template
  * @param string|array $s is variable in template
  * @return Load $this object
  */
  public function assign($s): Load
  {
    if(is_string($s))
    {
      $this->$s=@func_get_arg(1);
    }
    elseif(is_array($s))
    {
      foreach($s as $k=>$v) $this->$k=$v;
    }
    return $this;
  }

  /**
  * Template
  * get html from template
  * @param string $f is path for get template file
  * @return string html
  */
  public function fetch(string $f)
  {
    ob_start();
    include(__TPL.$this->data['theme']['folder'].'/tmp/'.$f.'.tpl');
    return ob_get_clean();
  }

  /**
  * Cache
  * generate cache file. key by url
  * @param string $key key name
  * @param function $function to generate new data and save to cache file (if set)
  * @return array|string|null of data
  */
  public static function cache(int $expire=3600,int $maxlv=2,bool $redirect=true)
  {
    self::$cache['expire']=$expire;
    if($redirect&&count(self::$path)>$maxlv)
    {
      self::move('/'.implode('/',array_slice(self::$path,0,$maxlv)));
    }
  }

  /**
  * Cache
  * get data from cache file
  * @param string $key key name
  * @param function $function to generate new data and save to cache file (if set)
  * @return array|string|null of data
  */
  public function get(string $key,$func=null)
  {
    $file=_FILES.'bin/'.DOMAIN.'/cache/'.trim($key,'/').'.php';
    if(file_exists($file))
    {
      $_=include($file);
      if(!empty($_['expire']) && $_['expire']>self::$time)
      {
        return $_['data'];
      }
    }
    if(!is_null($func))
    {
      self::$cache=['key'=>$key,'expire'=>-1];
      if($data=$func())
      {
        if(self::$cache['expire']>0)
        {
          $this->set(self::$cache['key'],$data,self::$cache['expire']);
        }
        return $data;
      }
    }
    return null;
  }

  /**
  * Cache
  * set data to cache file
  * @param string $key key name
  * @param array|string $data data
  * @param int $expire second for expire time
  */
  public function set(string $key,$data,int $expire=3600): void
  {
    Load::Folder()->save('bin/'.DOMAIN.'/cache/'.trim($key,'/').'.php',"<?php\nreturn ".var_export(['create'=>self::$time,'expire'=>self::$time+$expire,'data'=>$data],true)."\n?>");
  }

  /**
  * Cache
  * delete cache file
  * @param string $key key name
  */
  public function delete(string $key)
  {
    self::Folder()->delete('bin/'.DOMAIN.'/cache/'.$key.'.php');
    return $this;
  }

  /**
  * Cache
  * delete cache folder
  * @param string $key folder name
  */
  public function clear(string $folder='')
  {
    self::Folder()->clean('bin/'.DOMAIN.'/cache/'.$folder);
    return $this;
  }

  /**
  * Stats
  * save browser info to file
  * @param string $key name
  */
  public static function stats(string $key='')
  {
    list($type,$id,$view,$user)=explode(':',$key);
    if(stripos($_SERVER['HTTP_USER_AGENT'], 'bot') === false )
    {
      $file='bin/'.(defined('DOMAIN')?DOMAIN:HOST).'/'.$type.'-view/'.date('Y-m-d/H/i').'/'.$id.'/'.substr('00'.rand(1,99),-2).'.txt';
      if(file_exists(_FILES.$file))
      {
        $log=unserialize(file_get_contents(_FILES.$file));
        if(!$log['u'])
        {
          $log['u']=intval($user);
        }
      }
      else
      {
        $log=['do'=>0,'is'=>0,'mb'=>0,'tb'=>0,'dt'=>0,'u'=>intval($user)];
      }
      if($view=='do')
      {
        $tablet_browser = 0;
        $mobile_browser = 0;
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($_SERVER['HTTP_USER_AGENT'])))
        {
          $tablet_browser++;
        }
        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', strtolower($_SERVER['HTTP_USER_AGENT'])))
        {
          $mobile_browser++;
        }
        if ((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') > 0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE']))))
        {
          $mobile_browser++;
        }
        $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
        $mobile_agents = array(
            'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
            'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
            'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
            'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
            'newt','noki','palm','pana','pant','phil','play','port','prox',
            'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
            'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
            'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
            'wapr','webc','winw','winw','xda ','xda-');
        if (in_array($mobile_ua,$mobile_agents))
        {
          $mobile_browser++;
        }
        if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'opera mini') > 0)
        {
          $mobile_browser++;
            //Check for tablets on opera mini alternative headers
          $stock_ua = strtolower(isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA'])?$_SERVER['HTTP_X_OPERAMINI_PHONE_UA']:(isset($_SERVER['HTTP_DEVICE_STOCK_UA'])?$_SERVER['HTTP_DEVICE_STOCK_UA']:''));
          if (preg_match('/(tablet|ipad|playbook)|(android(?!.*mobile))/i', $stock_ua))
          {
            $tablet_browser++;
          }
        }
        if ($tablet_browser > 0)
        {
          $log['tb']=($log['tb']??0)+1;
        }
        else if ($mobile_browser > 0)
        {
          $log['mb']=($log['mb']??0)+1;
        }
        else
        {
          $log['dt']=($log['dt']??0)+1;
        }
      }
      if($log[$view])
      {
        $log[$view]++;
      }
      else
      {
        $log[$view]=1;
      }
      if(!is_dir($dir=dirname(_FILES.$file)))
      {
        @mkdir($dir, 0777 ,true);
        @chmod($dir, 0777);
      }
      if($fp=@fopen(_FILES.$file, 'wb'))
      {
        @fwrite($fp, serialize($log));
        @fclose($fp);
      }
    }
  }
}

?>
