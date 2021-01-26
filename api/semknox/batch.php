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
    require_once(DIR_FS_EXTERNAL.'semknox/Semknox.php');

    require_once(DIR_WS_CLASSES . 'language.php');
    $lng = new language();
    
    $semknox_array = array();
    foreach ($lng->catalog_languages as $language) {
      if (defined('MODULE_SEMKNOX_SYSTEM_API_'.$language['id'])
          && constant('MODULE_SEMKNOX_SYSTEM_API_'.$language['id']) != ''
          )
      {
        $semknox_array[$language['id']] = new Semknox($language['id']);
      }
    }

    $products_query = xtc_db_query("SELECT products_id 
                                      FROM ".TABLE_PRODUCTS);
    if (xtc_db_num_rows($products_query) > 0) {
      foreach ($semknox_array as $semknox) {
        $response = $semknox->initBatch();
        while ($products = xtc_db_fetch_array($products_query)) {
          $response = $semknox->uploadBatch(array($products['products_id']));
        }
        $response = $semknox->startBatch();
      }
    }
  }
?>