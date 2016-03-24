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

function check_category_permission($category_id) {
  $dbQuery = xtDBquery("SELECT c.categories_id
                          FROM " . TABLE_CATEGORIES . " c
                         WHERE c.categories_id = '" . (int)$category_id . "'         
                               " . CATEGORIES_CONDITIONS_C);
  if (xtc_db_num_rows($dbQuery, true) > 0) {
    return true;
  }
  return false;
}

function category_redirect($cPath) {
  global $PHP_SELF, $request_type, $current_category_id;

  // Wenn wir auf ner Produkt-Info-Seite sind
  if (basename($PHP_SELF) == FILENAME_DEFAULT 
      && strpos($_SERVER['QUERY_STRING'], 'error') === false 
      && strpos($_SERVER['QUERY_STRING'], 'success') === false
      && strpos($_SERVER['QUERY_STRING'], 'action') === false
     ) 
  {
    // check conditions
    if (check_category_permission($current_category_id) === true) {
        
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
        header('Location: '.preg_replace("/[\r\n]+(.*)$/i", "", $redirect_link));
      }
    } else {
      // 404er-Weiterleitung
      header('HTTP/1.1 404 Not Found' );
      header('Location: '.preg_replace("/[\r\n]+(.*)$/i", "", xtc_href_link(FILENAME_DEFAULT)));
    }   
  }
  return $cPath;
}
?>