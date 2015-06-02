<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_redirect.inc.php 5462 2013-09-03 13:10:26Z Tomcraft $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com
   (c) 2003 nextcommerce (xtc_redirect.inc.php,v 1.5 2003/08/13); www.nextcommerce.org
   (c) 2003 XT-Commerce - www.xt-commerce.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function xtc_redirect($url, $ssl='') {
  	global $request_type, $PHP_SELF;

    if ( (ENABLE_SSL == true) && ($request_type == 'SSL') && ($ssl != 'NONSSL') ) {
		  if (substr($url, 0, strlen(HTTP_SERVER)) == HTTP_SERVER) {
		    $url = HTTPS_SERVER . substr($url, strlen(HTTP_SERVER));
		  }
    }

    $_SESSION['REFERER'] = '';
    if (strpos($PHP_SELF, 'admin') === false &&
        strpos($PHP_SELF, FILENAME_CHECKOUT_SUCCESS) === false &&
        strpos($PHP_SELF, FILENAME_LOGIN) === false &&
        strpos($PHP_SELF, FILENAME_PASSWORD_DOUBLE_OPT) === false)
    {
      $_SESSION['REFERER'] = basename($PHP_SELF);
    }

    // save SESSION before redirect
    session_write_close();

    if (function_exists('xtc_db_close')) {
      xtc_db_close();
    }
    
    header('Location: ' . preg_replace("/[\r\n]+(.*)$/i", "", html_entity_decode($url)));
    exit();
  }
?>