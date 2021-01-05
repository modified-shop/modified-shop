<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


foreach(auto_include(DIR_FS_CATALOG.'includes/extra/captcha/','php') as $file) require_once ($file);

$_mod_captcha_class = CAPTCHA_MOD_CLASS;
if (!class_exists($_mod_captcha_class)) {
  $_mod_captcha_class = 'modified_captcha';
}


class modified_captcha {
    
  /**
   * instance
   *
   * @var Singleton
   */
  protected static $_instance = null;


  /**
   * get instance
   *
   * @return   Singleton
   */
  public static function getInstance() {

    if (null === self::$_instance) {
      self::$_instance = new self;
    }

    return self::$_instance;
  }


  /**
   * clone
   */
  protected function __clone() {}


  /**
   * constructor
   */
  protected function __construct() {}


  /**
   * get
   *
   * @return   vvcode
   */
  public function get() {
    return ((isset($_SESSION['vvcode'])) ? $_SESSION['vvcode'] : '');
  }
    
    
  /**
   * set
   */
  public function set() {
    require_once (DIR_FS_INC.'xtc_random_charcode.inc.php');
    $vvcode = xtc_random_charcode(6, true);
    $_SESSION['vvcode'] = strtoupper($vvcode);
  }
    
    
  /**
   * output
   *
   * echo   vvcode
   */
  public function output() {
    require_once (DIR_FS_INC.'xtc_render_vvcode.inc.php');
    
    $this->set();
    return vvcode_render_code($this->get());
  }
    
    
  /**
   * validate
   *
   * @return   boolean
   */
  public function validate($input) {
    $vvcode = $this->get();
    unset($_SESSION['vvcode']);          

    if (!empty($vvcode) && strtoupper($input) == $vvcode) {
        return true;
    }
    return false;
  }
    
    
  /**
   * get_image_code
   *
   * @return   image code
   */
  public function get_image_code() {
    return '<img src="data:image/jpeg;base64,' . base64_encode($this->output()).'">';
  }
    
    
  /**
   * get_input_code
   *
   * @return   input field
   */
  public function get_input_code() {
    return xtc_draw_input_field('vvcode', '', 'size="6" maxlength="6"', 'text', false);
  }

}
?>