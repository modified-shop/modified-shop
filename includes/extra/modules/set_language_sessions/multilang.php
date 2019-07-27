<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  if (SEARCH_ENGINE_FRIENDLY_URLS == 'true'
      && defined('SEO_URL_MOD_CLASS')
      && defined('MODULE_MULTILANG_STATUS')
      && MODULE_MULTILANG_STATUS == 'true'
      && !defined('RUN_MODE_ADMIN')
      )
  {
    $seo_url_sites = array(
      FILENAME_PRODUCT_INFO,
      FILENAME_CONTENT,
      FILENAME_DEFAULT,
      FILENAME_SPECIALS,
      FILENAME_PRODUCTS_NEW,
    );
  
    if (in_array(basename($PHP_SELF), $seo_url_sites)) {
      if (!isset($_GET['language'])) {
        $_GET['language'] = DEFAULT_LANGUAGE;
      }
      $site_key = array_search(basename($PHP_SELF), $seo_url_sites);
      if ($site_key >= 2
          && !isset($_GET['cPath'])
          && !isset($_GET['manufacturers_id'])
          )
      {
        $redirect_link = xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(), $request_type);
        $redirect_link = str_replace(array(HTTP_SERVER,HTTPS_SERVER), '', preg_replace("/([^\?]*)(\?.*)/", "$1", $redirect_link));
        $current_link = preg_replace("/([^\?]*)(\?.*)/", "$1", $_SERVER['REQUEST_URI']);
      
        if ($current_link != $redirect_link) {
          header('HTTP/1.1 301 Moved Permanently' );
          header('Location: '.preg_replace("/[\r\n]+(.*)$/i", "", $redirect_link));
          exit();      
        }
      }
    }  
  }
?>