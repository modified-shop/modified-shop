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

  require_once(DIR_WS_CLASSES.'modified_captcha.php');
  
  $captcha_class = CAPTCHA_MOD_CLASS;
  $mod_captcha = $captcha_class::getInstance();
  $mod_captcha->output();
?>