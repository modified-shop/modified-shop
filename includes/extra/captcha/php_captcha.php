<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


class php_captcha extends modified_captcha {

  /**
   * instance
   *
   * @var Singleton
   */
  protected static $_instance = null;


  /**
   * get instance
   *
   * @return Singleton
   */
  public static function getInstance() {

    if (null === self::$_instance) {
      self::$_instance = new self;
    }

    return self::$_instance;
  }


  /**
   * output
   *
   * echo   vvcode
   */
  public function output() {
    // include captcha class
    require_once (DIR_FS_EXTERNAL.'captcha/php-captcha.inc.php');
    
    // load fonts
    $aFonts = array();
    if ($dir= opendir(DIR_WS_INCLUDES.'fonts/')){
      while  (($file = readdir($dir)) !==false) {
        if (is_file(DIR_WS_INCLUDES.'fonts/'.$file) and (strstr(strtoupper($file),'.TTF'))){
          $aFonts[] = DIR_FS_CATALOG.'includes/fonts/'.$file;
        }
      }
      closedir($dir);
    }

    // create new image 
    $oPhpCaptcha = new PhpCaptcha($aFonts, MODULE_SYSTEM_PHP_CAPTCHA_WIDTH, MODULE_SYSTEM_PHP_CAPTCHA_HEIGHT);
    $oPhpCaptcha->UseColour((MODULE_SYSTEM_PHP_CAPTCHA_USE_COLOR == 'true' ? true : false));
    $oPhpCaptcha->DisplayShadow((MODULE_SYSTEM_PHP_CAPTCHA_USE_SHADOW == 'true' ? true : false));
    $oPhpCaptcha->SetCharSet(array('A','B','C','D','E','F','G','H','K','M','N','P','Q','R','S','T','U','V','W','X','Y','Z','2','3','4','5','6','8','9'));
    $oPhpCaptcha->SetNumChars((int)MODULE_SYSTEM_PHP_CAPTCHA_CODE_LENGTH);
    $oPhpCaptcha->SetNumLines((int)MODULE_SYSTEM_PHP_CAPTCHA_NUM_LINES);
    $oPhpCaptcha->SetMinFontSize((int)MODULE_SYSTEM_PHP_CAPTCHA_MIN_FONT);
    $oPhpCaptcha->SetMaxFontSize((int)MODULE_SYSTEM_PHP_CAPTCHA_MAX_FONT);
    $oPhpCaptcha->SetBackgroundColors(MODULE_SYSTEM_PHP_CAPTCHA_BACKGROUND_RGB);
    $oPhpCaptcha->SetLinesColors(MODULE_SYSTEM_PHP_CAPTCHA_LINES_RGB);
    $oPhpCaptcha->SetCharsColors(MODULE_SYSTEM_PHP_CAPTCHA_CHARS_RGB);
    $oPhpCaptcha->CaseInsensitive(true);
    return $oPhpCaptcha->Create(); 
  }


  /**
   * get_input_code
   *
   * @return   input field
   */
  public function get_input_code() {
    return xtc_draw_input_field('vvcode', '', 'size="'.MODULE_SYSTEM_PHP_CAPTCHA_CODE_LENGTH.'" maxlength="'.MODULE_SYSTEM_PHP_CAPTCHA_CODE_LENGTH.'"', 'text', false);
  }
  
}
?>