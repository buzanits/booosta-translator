<?php
namespace booosta\translator;

use \booosta\Framework as b;
b::init_module('translator');

class Translator extends \booosta\base\Module
{
  use moduletrait_translator;

  public $lang;
  protected $map_path, $map;
  protected $merge = false;

  public function __construct($lang = 'en', $map_path = '', $map = null)
  {
    parent::__construct();

    $this->lang = $lang;
    $this->map_path = $map_path;
    $this->map = $map;
  }

  public function t($key)
  {
    if(!is_array($this->map)) $this->read_map();
    if(!is_array($this->map) && is_object($this->topobj) && is_a($this->topobj, "\\booosta\\webapp\\webapp")):
      #$this->topobj->raise_error("no map for '$key'", null, false);   // false = no translation
      \booosta\Framework::debug("no translator map for '$key'");
      return $key;
    endif;

    if(is_array($this->map)):
      if(isset($this->map[$key])) return $this->map[$key];
      if(isset($this->map[ucfirst($key)])) return lcfirst($this->map[ucfirst($key)]);
      if(isset($this->map[lcfirst($key)])) return ucfirst($this->map[lcfirst($key)]);
    endif;

    #\booosta\Framework::debug("key: $key"); \booosta\Framework::debug($this->map);
    return $key;
  }

  protected function read_map()
  {
    if(is_readable($this->map)) $mapfile = $this->map;

    if($this->merge):
      if(is_readable("lang.$this->lang")):
        #\booosta\debug("lang.$this->lang");
        include("lang.$this->lang");
        $this->merge_usertype();
      endif;

      if(is_readable("tpl/lang-{$this->lang}/map.php")):
        #b::debug("tpl/lang-{$this->lang}/map.php");
        include("tpl/lang-{$this->lang}/map.php");
        $this->merge_usertype();
      endif;

      if($mapfile):
        include($mapfile);
        $this->merge_usertype();
      endif;

      if(is_readable("$this->map_path/lang.$this->lang")):
        #\booosta\debug("$this->map_path/lang.$this->lang");
        include("$this->map_path/lang.$this->lang");
        $this->merge_usertype();
      endif;

      if($this->map_path && is_readable("$this->map_path/$this->map")):
        #\booosta\debug("$this->map_path/$this->map");
        include("$this->map_path/$this->map");
        $this->merge_usertype();
      endif;
    else:
      #\booosta\Framework::debug('no merge');
      #\booosta\Framework::debug(getcwd());
      #\booosta\Framework::debug("$this->map_path/lang.$this->lang");
      if($this->map_path && !is_dir("$this->map_path/$this->map") && is_readable("$this->map_path/$this->map")) 
        include("$this->map_path/$this->map");
      elseif(is_readable("$this->map_path/lang.$this->lang")) include("$this->map_path/lang.$this->lang");
      elseif(is_readable($this->map) && !is_dir($this->map)) include($this->map);
      elseif(is_readable("lang.$this->lang")) include("lang.$this->lang");
      elseif(is_readable("tpl/lang-{$this->lang}/map.php")) include("tpl/lang-{$this->lang}/map.php");
      else $this->map = [];

      $this->merge_usertype();
    endif;
  }

  protected function merge_usertype()
  {
    if(is_object($this->topobj) && is_a($this->topobj, "\\booosta\\webapp\\Webapp")) $obj = $this->topobj;

    if(is_array($this->usertype_allowed_map))
      foreach($this->usertype_allowed_map as $type=>$tr)
        if(is_object($obj) && $obj->usertype_allowed($type, false)) $this->map = array_merge($this->map, $tr);

    if(isset($this->usertype_map_link[$_SESSION['act_usertype'] ?? null])) $this->map = array_merge($this->map, $this->usertype_map[$this->usertype_map_link[$_SESSION['act_usertype']]]);
    if(isset($this->usertype_map[$_SESSION['act_usertype'] ?? null])) $this->map = array_merge($this->map, $this->usertype_map[$_SESSION['act_usertype']]);
  }

  public function __invoke($param) { return $this->t($param); }
  public function set_merge($flag) { $this->merge = $flag; }
}
