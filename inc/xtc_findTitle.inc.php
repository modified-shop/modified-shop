<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_findTitle.inc.php 1313 2005-10-18 15:49:15Z mz $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(new_attributes); www.oscommerce.com
   (c) 2003     nextcommerce (new_attributes.php,v 1.13 2003/08/21); www.nextcommerce.org
   (c) 2003 XT-Commerce
   
   Released under the GNU General Public License
   --------------------------------------------------------------
   Third Party contributions:
   New Attribute Manager v4b             
   Autor: Mike G | mp3man@internetwork.net | http://downloads.ephing.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function xtc_findTitle($current_pid, $languageFilter) {
    $name_query =  xtc_db_query("SELECT products_name 
                                   FROM ".TABLE_PRODUCTS_DESCRIPTION."  
                                  WHERE language_id = '" . (int)$_SESSION['languages_id'] . "' 
                                    AND products_id = '" . (int)$current_pid . "'");
    if (xtc_db_num_rows($name_query) > 0) {
      $line = xtc_db_fetch_array($name_query);
      return $line['products_name'];
    } else {
      return "Something isn't right....";
    }
  }
?>