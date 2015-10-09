<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  require ('includes/application_top.php');

  if (!defined('MODULE_CAPTCHA_ACTIVE')) {
    require_once (DIR_FS_INC.'xtc_render_vvcode.inc.php');
    require_once (DIR_FS_INC.'xtc_random_charcode.inc.php');

    $visual_verify_code = xtc_random_charcode(6);
    $_SESSION['vvcode'] = strtoupper($visual_verify_code);
    $vvimg = vvcode_render_code($visual_verify_code);
  } else {
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
    $oPhpCaptcha = new PhpCaptcha($aFonts, MODULE_CAPTCHA_WIDTH, MODULE_CAPTCHA_HEIGHT);
    $oPhpCaptcha->UseColour((MODULE_CAPTCHA_USE_COLOR == 'True' ? true : false));
    $oPhpCaptcha->DisplayShadow((MODULE_CAPTCHA_USE_SHADOW == 'True' ? true : false));
    $oPhpCaptcha->SetCharSet(array('A','B','C','D','E','F','G','H','K','M','N','P','Q','R','S','T','U','V','W','X','Y','Z','2','3','4','5','6','8','9'));
    $oPhpCaptcha->SetNumChars((int)MODULE_CAPTCHA_CODE_LENGTH);
    $oPhpCaptcha->SetNumLines((int)MODULE_CAPTCHA_NUM_LINES);
    $oPhpCaptcha->SetMinFontSize((int)MODULE_CAPTCHA_MIN_FONT);
    $oPhpCaptcha->SetMaxFontSize((int)MODULE_CAPTCHA_MAX_FONT);
    $oPhpCaptcha->SetBackgroundColors(MODULE_CAPTCHA_BACKGROUND_RGB);
    $oPhpCaptcha->SetLinesColors(MODULE_CAPTCHA_LINES_RGB);
    $oPhpCaptcha->SetCharsColors(MODULE_CAPTCHA_CHARS_RGB);
    $oPhpCaptcha->Create(); 
  }
?>