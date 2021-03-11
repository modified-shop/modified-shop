<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  include (dirname(__FILE__).'/../../includes/application_top_callback.php');
  
  if (defined('MODULE_SEMKNOX_SYSTEM_STATUS')
      && MODULE_SEMKNOX_SYSTEM_STATUS == 'true'
      )
  {
    // set the language
    include_once (DIR_WS_MODULES.'set_language_sessions.php');

    // language translations
    require_once (DIR_WS_LANGUAGES.$_SESSION['language'].'/'.$_SESSION['language'].'.php');

    // write customers status in session
    require_once (DIR_WS_INCLUDES.'write_customers_status.php');

    // content, product, category - sql group_check/fsk_lock
    require_once (DIR_WS_INCLUDES.'define_conditions.php');

    // add_select
    require_once (DIR_WS_INCLUDES.'define_add_select.php');

    require_once(DIR_FS_EXTERNAL.'semknox/Semknox.php');

    require_once(DIR_WS_CLASSES . 'language.php');
    $lng = new language();
    
    $semknox_array = array();
    foreach ($lng->catalog_languages as $language) {
      if (defined('MODULE_SEMKNOX_SYSTEM_API_'.$language['id'])
          && constant('MODULE_SEMKNOX_SYSTEM_API_'.$language['id']) != ''
          )
      {
        $semknox_array[$language['id']] = new Semknox($language['id'], 30);
      }
    }    
    
    if (count($semknox_array) > 0) {
      $products_array = array();
      $products_query = xtc_db_query("SELECT p.products_id 
                                        FROM ".TABLE_PRODUCTS." p
                                       WHERE p.products_status = 1");
      if (xtc_db_num_rows($products_query) > 0) {
        while ($products = xtc_db_fetch_array($products_query)) {
          $products_array[] = $products['products_id'];
        }
      }
    
      if (count($products_array) > 0) {
        foreach ($semknox_array as $semknox) {
        $response = $semknox->initBatch();
          $response = $semknox->uploadBatch($products_array);
          $response = $semknox->startBatch();
        }
      }
    }
  }
?>