<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_get_vpe_name.inc.php 

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
  
  
  function xtc_get_vpe_name($products_vpe_id, $languages_id = '') {
    static $vpe_name_array;
    
    if (!isset($vpe_name_array)) {
      $vpe_name_array = array();
    }

    if ($languages_id == '') {
      $languages_id = (int)$_SESSION['languages_id'];
    }

    if (!isset($vpe_name_array[$languages_id][$products_vpe_id])) {
      $vpe_name_array[$languages_id][$products_vpe_id] = '';
    
      $vpe_name_query = xtDBquery("SELECT products_vpe_name 
                                     FROM " . TABLE_PRODUCTS_VPE . " 
                                    WHERE language_id = '".(int)$languages_id."' 
                                      AND products_vpe_id = '".(int)$products_vpe_id."'");
      if (xtc_db_num_rows($vpe_name_query)) {
        $vpe_name = xtc_db_fetch_array($vpe_name_query, true);
        $vpe_name_array[$languages_id][$products_vpe_id] = $vpe_name['products_vpe_name'];
      }
    }
    
    return $vpe_name_array[$languages_id][$products_vpe_id];
  }  
