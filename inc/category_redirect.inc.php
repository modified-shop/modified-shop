<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   gunnart_productRedirect.inc.php
   (c) 2012 web28/GTB
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


function category_redirect($cPath) {
  global $PHP_SELF, $request_type;

  // only if no action
  if (basename($PHP_SELF) == FILENAME_DEFAULT 
      && strpos($_SERVER['QUERY_STRING'], 'error') === false 
      && strpos($_SERVER['QUERY_STRING'], 'success') === false
      && strpos($_SERVER['QUERY_STRING'], 'action') === false
     ) 
  {
    if (SEARCH_ENGINE_FRIENDLY_URLS != 'true' || defined('SUMA_URL_MODUL')) {
      return $cPath;
    }
    
    // check Session-ID and $_GET-Parameter
    $current_link = preg_replace("/([^\?]*)(\?.*)/", "$1", $_SERVER['REQUEST_URI']);
    
    $redirect_link = xtc_href_link(FILENAME_DEFAULT, xtc_get_all_get_params(array('cPath')).'cPath='.$cPath, $request_type);
    $category_link = str_replace(array(HTTP_SERVER, HTTPS_SERVER), '', preg_replace("/([^\?]*)(\?.*)/", "$1", $redirect_link));
    
    // redirect
    if ($category_link != $current_link) {
      header('HTTP/1.1 301 Moved Permanently' );
      header('Location: '.preg_replace("/[\r\n]+(.*)$/i", "", html_entity_decode($redirect_link)));
    }
  }
  
  return $cPath;
}
?>