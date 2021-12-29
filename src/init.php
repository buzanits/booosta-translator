<?php
namespace booosta\translator;

\booosta\Framework::add_module_trait('base', 'translator\base');
\booosta\Framework::add_module_trait('webapp', 'translator\webapp');

trait Base
{
  public $lang;

  public function t($key)
  {
    if(!is_object($this->t)):
      $this->lang = $this->get_lang();

      $this->t = $this->makeInstance('Translator', $this->lang, $this->translator_dir);
      if(isset($this->translator_merge)) $this->t->set_merge($this->translator_merge);
    endif;

    $t = $this->t;
    return $t($key);
  }

  public function get_lang()
  {
    $lang = null;
    if(isset($_SESSION['LANG'])) $lang = $_SESSION['LANG'];
    if($lang === null) $lang = $this->config('language');
    if($lang === null) $lang = 'en';

    return $lang;
  }

  public function get_language() { return $this->get_lang(); }
}


trait Webapp
{
  protected function autorun_translator()
  {
    if(isset($_SESSION['LANG'])) $this->lang = $_SESSION['LANG'];
    if($this->lang === null) $this->lang = $this->config('language');
    if($this->lang === null) $this->lang = 'en';
    $this->t = $this->makeInstance('Translator', $this->lang, $this->translator_dir);
    if(isset($this->translator_merge)) $this->t->set_merge($this->translator_merge);
  }
}
