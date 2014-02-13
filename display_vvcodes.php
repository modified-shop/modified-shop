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
  
  /*
  require_once (DIR_FS_INC.'xtc_render_vvcode.inc.php');
  require_once (DIR_FS_INC.'xtc_random_charcode.inc.php');

  $visual_verify_code = xtc_random_charcode(6);
  $_SESSION['vvcode'] = strtoupper($visual_verify_code);
  $vvimg = vvcode_render_code($visual_verify_code);
  */
  
  // include captcha class
  require_once (DIR_FS_EXTERNAL.'captcha/php-captcha.inc.php');

  // load fonts
  $aFonts = array();
  if ($dir= opendir(DIR_WS_INCLUDES.'fonts/')){
    while  (($file = readdir($dir)) !==false) {
      if (is_file(DIR_WS_INCLUDES.'fonts/'.$file) and (strstr(strtoupper($file),'.TTF'))){
        $aFonts[] = DIR_FS_CATALOG.'/includes/fonts/'.$file;
      }
    }
    closedir($dir);
  }

  // create new image
  $oPhpCaptcha = new PhpCaptcha($aFonts, 200, 50);
  $oPhpCaptcha->UseColour(true);
  $oPhpCaptcha->Create();
  
?>