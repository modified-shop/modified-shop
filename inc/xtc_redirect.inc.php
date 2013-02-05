<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_redirect.inc.php 1261 2005-09-29 19:01:49Z hhgag $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_redirect.inc.php,v 1.5 2003/08/13); www.nextcommerce.org
   
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  // include needed functions
  require_once(DIR_FS_INC . 'xtc_exit.inc.php');
  
  function xtc_redirect($url, $ssl='') {
  	global $request_type;

    if ( (ENABLE_SSL == true) && ($request_type == 'SSL') && ($ssl != 'NONSSL') ) {
		  if (substr($url, 0, strlen(HTTP_SERVER)) == HTTP_SERVER) {
		    $url = HTTPS_SERVER . substr($url, strlen(HTTP_SERVER));
		  }
    }
    
    $_SESSION['REFERER'] = basename(parse_url($_SERVER['SCRIPT_NAME'], PHP_URL_PATH));

    header('Location: ' . preg_replace("/[\r\n]+(.*)$/i", "", html_entity_decode($url)));
    xtc_exit();
  }
?>