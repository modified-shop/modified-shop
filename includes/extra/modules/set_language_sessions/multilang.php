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
      && !defined('RUN_MODE_CALLBACK')
      && !defined('RUN_MODE_EXPORT')
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
      } else {
        $_GET['language'] = xtc_input_validation($_GET['language'], 'lang');
      }
      $site_key = array_search(basename($PHP_SELF), $seo_url_sites);
      if ($site_key >= 2
          && !isset($_GET['cPath'])
          && !isset($_GET['manufacturers_id'])
          )
      {
        require_once (DIR_WS_CLASSES.'language.php');
        $lng = new language($_GET['language']);
        $_GET['language'] = $lng->language['code'];
        
        $redirect_link = xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(), $request_type);
        $redirect_link = str_replace(array(HTTP_SERVER,HTTPS_SERVER,FILENAME_DEFAULT), '', preg_replace("/([^\?]*)(\?.*)/", "$1", $redirect_link));
        $current_link = preg_replace("/([^\?]*)(\?.*)/", "$1", $_SERVER['REQUEST_URI']);
      
        if ($current_link != $redirect_link) {
          header('HTTP/1.1 301 Moved Permanently' );
          header('Location: '.preg_replace("/[\r\n]+(.*)$/i", "", $redirect_link));
          exit();      
        }
      }
    }  
  }
