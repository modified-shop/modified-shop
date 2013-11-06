<?php
/* -----------------------------------------------------------------------------------------
   $Id:$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

chdir('../../');
require_once('includes/application_top.php');

// include easymarketing configuration
require_once(DIR_FS_CATALOG.'api/easymarketing/includes/config.php');

// include easymarketing authentification
require_once(DIR_FS_EASYMARKETING_INCLUDES.'auth.php');

// include easymarketing functions
require_once(DIR_FS_EASYMARKETING_INCLUDES.'functions.php');

// process request
if (isset($parent_id)) {

  // sql query for categories
  $categories_query_raw = "SELECT c.categories_id,
                                  cd.categories_name
                             FROM ".TABLE_CATEGORIES." c
                             JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd
                                  ON (c.categories_id = cd.categories_id
                                     AND cd.language_id = '".MODULE_EASYMARKETING_LANGUAGES_ID."')
                            WHERE c.categories_status = '1'
                              AND c.categories_id = '".$parent_id."'";
  
  // make sql query
  $categories_query = xtc_db_query($categories_query_raw);
  
  // check for result
  if (xtc_db_num_rows($categories_query) > 0) {

    while ($categories = xtc_db_fetch_array($categories_query)) {
      
      // build categories array
      $categories_array = array('id' => $categories['categories_id'],
                                'name' => mod_convert($categories['categories_name']),
                                'url' => xtc_href_link(FILENAME_DEFAULT, xtc_category_link($categories['categories_id'], $categories['categories_name']), 'NONSSL', false),
                                'children' => mod_get_sub_categories($categories['categories_id'])
                                );

    }
    
  } elseif ($parent_id == '0') {

    // build categories array
    $categories_array = array('id' => $parent_id,
                              'name' => mod_convert(STORE_NAME),
                              'url' => xtc_href_link(FILENAME_DEFAULT, '', 'NONSSL', false),
                              'children' => mod_get_sub_categories($parent_id)
                              );
  
  }
  
  if (isset($categories_array)) {
  
    // output categories  
    mod_stream_response($categories_array);
  }  
}
