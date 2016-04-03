<?php
/* --------------------------------------------------------------
   $Id: new_attributes_functions.php 899 2005-04-29 02:40:57Z hhgag $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(new_attributes_functions); www.oscommerce.com
   (c) 2003	 nextcommerce (new_attributes_functions.php,v 1.8 2003/08/14); www.nextcommerce.org

   Released under the GNU General Public License
   --------------------------------------------------------------
   Third Party contributions:
   New Attribute Manager v4b				Autor: Mike G | mp3man@internetwork.net | http://downloads.ephing.com

   Released under the GNU General Public License
   --------------------------------------------------------------*/
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
  // A simple little function to determine if the current value is already selected for the current product.
  function checkAttribute($current_value_id, $current_pid, $current_product_option_id) {
    global $attr_array, $attr_dl_array; //web28 - 2012-07-15 - change global variable list to array

    $query = "SELECT *
                FROM ".TABLE_PRODUCTS_ATTRIBUTES."
               WHERE options_values_id = '" . (int)$current_value_id . "'
                 AND products_id = '" . (int)$current_pid . "'
                 AND options_id = '" . (int)$current_product_option_id . "'";
    $result = xtc_db_query($query);
    $isFound = xtc_db_num_rows($result);

    $attr_array = array();
    $attr_dl_array = array();

    if ($isFound) {
      while($line = xtc_db_fetch_array($result)) {
        // download function start
        $attr_array= $line;
        $dl_sql = xtc_db_query("SELECT products_attributes_maxdays,
                                       products_attributes_filename,
                                       products_attributes_maxcount
                                 FROM ".TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD."
                                 WHERE products_attributes_id = '" . $line['products_attributes_id'] . "'");
        $attr_dl_array = xtc_db_fetch_array($dl_sql);
        // download function end
      }
      //echo <<pre>.print_r($attr_array,true).'</pre><br>';
      //echo <<pre>.print_r($attr_dl_array,true).'</pre><br>';
      return true;
    } else {      
      return false;
    }
  }

  function rowClass($i) {
    $class1 = 'attributes-odd';
    $class2 = 'attributes-even';
    if ($i%2) {
      return $class1;
    } else {
     return $class2;
    }
  }