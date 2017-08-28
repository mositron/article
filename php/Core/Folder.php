<?php
namespace Article\Core;

class Folder
{
  public $folder;
  public function __construct()
  {
    //debug_print_backtrace();
    $this->folder=_FILES;
  }

  public function save(string $file,string $data): bool
  {
    $this->_mkdir(dirname($this->folder.$file));
    if($fp=@fopen($this->folder.$file, 'wb'))
    {
      @fwrite($fp, $data);
      @fclose($fp);
      return true;
    }
    return false;
  }

  public function mkdir(string $dir,int $mode=0777): bool
  {
    if(!is_dir($this->folder.$dir))
    {
      @mkdir($this->folder.$dir, $mode, true);
      @chmod($this->folder.$dir, $mode);
    }
    return true;
  }

  public function delete($file): bool
  {
    if(file_exists($this->folder.$file))
    {
      @unlink($this->folder.$file);
    }
    return true;
  }

  private function _mkdir($dir, $mode = 0777): void
  {
    if(!is_dir($dir))
    {
      //$this->_mkdir(dirname($dir));
      @mkdir($dir, $mode, true);
      @chmod($dir, $mode);
    }
  }

  public function clean($type): bool
  {
    $type=trim($type,'/');
    if (!is_dir($this->folder.$type)||!($dh=@opendir($this->folder.$type))) return false;
    $result=true;
    while($file=readdir($dh))
    {
      if(!in_array($file,['.','..']))
      {
        $file2=$type.'/'.$file;
        if(is_dir($this->folder.$file2))
        {
          $this->clean($file2);
        }
        else
        {
          @unlink($this->folder.$file2);
        }
      }
    }
    @rmdir($this->folder.$type);
    return true;
  }
}
?>
