<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_findTitle.inc.php 1313 2005-10-18 15:49:15Z mz $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(new_attributes); www.oscommerce.com
   (c) 2003     nextcommerce (new_attributes.php,v 1.13 2003/08/21); www.nextcommerce.org

   Released under the GNU General Public License
   --------------------------------------------------------------
   Third Party contributions:
   New Attribute Manager v4b                Autor: Mike G | mp3man@internetwork.net | http://downloads.ephing.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


  function xtc_findTitle($current_pid, $languageFilter) {
    $query = "SELECT * FROM ".TABLE_PRODUCTS_DESCRIPTION."  where language_id = '" . $_SESSION['languages_id'] . "' AND products_id = '" . $current_pid . "'";

    $result = xtc_db_query($query);

    $matches = xtc_db_num_rows($result);

    if ($matches) {
      while ($line = xtc_db_fetch_array($result)) {
        $productName = $line['products_name'];
      }
      return $productName;
    } else {
      return "Something isn't right....";
    }
  }
?>