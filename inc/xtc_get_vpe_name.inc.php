<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_get_vpe_name.inc.php

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

   function xtc_get_vpe_name($vpeID) {
   	  $vpe_query="SELECT products_vpe_name FROM " . TABLE_PRODUCTS_VPE . " WHERE language_id='".(int)$_SESSION['languages_id']."' and products_vpe_id='".$vpeID."'";
   	  $vpe_query = xtDBquery($vpe_query);
   	  $vpe = xtc_db_fetch_array($vpe_query,true);
   	  return $vpe['products_vpe_name'];
   }
?>