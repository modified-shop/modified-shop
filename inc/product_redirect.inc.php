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

function product_redirect_link($products_id = false, $current_link) {
  global $products_link_cat_id;  

  $return_arr = array('redirect' => true,
                      'RedirectionLink' => '');

  if ($products_id !== false) {
    $add_select = ((defined('PRODUCTS_CANONICAL_CAT_ID') && PRODUCTS_CANONICAL_CAT_ID) ? "p.products_canonical_cat_id," : '');
    $check_link_query = xtDBquery("SELECT ".$add_select."
                                          p.products_id, 
                                          pd.products_name,
                                          p2c.categories_id
                                     FROM " . TABLE_PRODUCTS . " p
                                     JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c 
                                          ON p.products_id = p2c.products_id 
                                     JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                          ON pd.products_id = p.products_id 
                                             AND pd.language_id = '".(int)$_SESSION['languages_id']."' 
                                             AND trim(pd.products_name) != ''
                                    WHERE p.products_id = '" . (int)$products_id . "'
                                          " . PRODUCTS_CONDITIONS_P . "
                                      AND p.products_status = '1'");
                    
    if (xtc_db_num_rows($check_link_query, true) > 0) {
      $link_array = array();
      while ($check_link = xtc_db_fetch_array($check_link_query, true)) {
        $products_link_cat_id = $check_link['categories_id'];
        $redirection_link = xtc_href_link(FILENAME_PRODUCT_INFO, 'products_id='.(int)$products_id);
        $link_array[] = $redirection_link;
    
        // Link without http/https, Session-ID and $_GET-Parameter
        $product_link = str_replace(array(HTTP_SERVER,HTTPS_SERVER), '', preg_replace("/([^\?]*)(\?.*)/", "$1", $redirection_link));
    
        if ($product_link == $current_link) {
          $return_arr['redirect'] = false;
          break;
        }
      }

      //links different, first Link as redirect
      $return_arr['RedirectionLink'] = (($return_arr['redirect'] === true) ? $link_array[0] : '');
      unset($link_array);
    }        
  }

  return $return_arr;
}


function product_redirect($actual_products_id) {
  global $products_link_cat_id, $PHP_SELF;

  // Wenn wir auf ner Produkt-Info-Seite sind
  if (basename($PHP_SELF) == FILENAME_PRODUCT_INFO 
      && strpos($_SERVER['QUERY_STRING'], 'error') === false 
      && strpos($_SERVER['QUERY_STRING'], 'success') === false
      && strpos($_SERVER['QUERY_STRING'], 'action') === false
     ) 
  {
    if (SEARCH_ENGINE_FRIENDLY_URLS != 'true' || defined('SUMA_URL_MODUL')) {
      $products_link_cat_id = 0;
      return xtc_get_product_path($actual_products_id);
    }
    
    // check Session-ID and $_GET-Parameter
    $current_link = preg_replace("/([^\?]*)(\?.*)/", "$1", $_SERVER['REQUEST_URI']);
    
    //check for products links without cat names
    $shortURL = ((strlen(DIR_WS_CATALOG) > 1) ? str_replace(DIR_WS_CATALOG, '', $current_link) : ltrim($current_link, '/'));
    if (isset($_SESSION['CatPath']) && trim($_SESSION['CatPath']) != '' && strpos($shortURL, '/') === false) {
      return $_SESSION['CatPath'];
    }
   
    // redirect
    $redirect_arr = product_redirect_link($actual_products_id, $current_link);
    if ($redirect_arr['redirect']) {
      if ($redirect_arr['RedirectionLink'] != '') {
        header('HTTP/1.1 301 Moved Permanently' );
        header('Location: '.preg_replace("/[\r\n]+(.*)$/i", "", $redirect_arr['RedirectionLink']));        
      } else {
        header('HTTP/1.1 404 Not Found' );
        header('Location: '.preg_replace("/[\r\n]+(.*)$/i", "", xtc_href_link(FILENAME_DEFAULT))); 
      }        
    }
    
    return xtc_get_category_path($products_link_cat_id);
  }
}
?>