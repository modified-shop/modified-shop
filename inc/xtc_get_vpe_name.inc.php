<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_get_vpe_name.inc.php 

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
  
  
  function xtc_get_vpe_name($vpe_id) {
    $vpe_query = xtDBquery("SELECT products_vpe_name 
                              FROM " . TABLE_PRODUCTS_VPE . " 
                             WHERE language_id = '".(int)$_SESSION['languages_id']."' 
                               AND products_vpe_id = '".(int)$vpe_id."'");
    if (xtc_db_num_rows($vpe_query, true) > 0) {
      $vpe = xtc_db_fetch_array($vpe_query, true);
      return $vpe['products_vpe_name'];
    }
  }  
?>