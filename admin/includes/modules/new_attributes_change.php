<?php
/* --------------------------------------------------------------
   $Id: new_attributes_change.php 3676 2012-09-26 12:35:43Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(new_attributes_change); www.oscommerce.com
   (c) 2003	 nextcommerce (new_attributes_change.php,v 1.8 2003/08/14); www.nextcommerce.org
   (c) 2006  xt-commerce(new_attributes_select.php 901 2005-04-29); www.xt-commerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------
   Third Party contributions:
   New Attribute Manager v4b          Autor: Mike G | mp3man@internetwork.net | http://downloads.ephing.com

   Released under the GNU General Public License
   --------------------------------------------------------------*/
  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
  require_once(DIR_FS_INC .'xtc_get_tax_rate.inc.php');
  require_once(DIR_FS_INC .'xtc_get_tax_class_id.inc.php');
  //require_once(DIR_FS_INC .'xtc_format_price.inc.php');
  // I found the easiest way to do this is just delete the current attributes & start over =)
  // download function start
  $delete_sql = xtc_db_query("SELECT products_attributes_id
                                FROM ".TABLE_PRODUCTS_ATTRIBUTES."
                               WHERE products_id = '" . $_POST['current_product_id'] . "'");

  while($delete_res = xtc_db_fetch_array($delete_sql)) {
      $delete_download_sql = xtc_db_query("SELECT products_attributes_filename
                                             FROM ".TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD."
                                            WHERE products_attributes_id = '" . $delete_res['products_attributes_id'] . "'"); //Web28 - 2010-12-16 - fix typo

      $delete_download_file = xtc_db_fetch_array($delete_download_sql);
      xtc_db_query("DELETE FROM ".TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD." WHERE products_attributes_id = '" . $delete_res['products_attributes_id'] . "'");
  }

  // download function end
  xtc_db_query("DELETE FROM ".TABLE_PRODUCTS_ATTRIBUTES." WHERE products_id = '" . $_POST['current_product_id'] . "'" );

  // Simple, yet effective.. loop through the selected Option Values.. find the proper price & prefix.. insert.. yadda yadda yadda.
  for ($i = 0; $i < sizeof($_POST['optionValues']); $i++) {
    $query = "SELECT * 
                FROM ".TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS." 
               WHERE products_options_values_id = '" . $_POST['optionValues'][$i] . "'";
    $result = xtc_db_query($query);
    $matches = xtc_db_num_rows($result);
    while ($line = xtc_db_fetch_array($result)) {
      $optionsID = $line['products_options_id'];
    }

    $cv_id = $_POST['optionValues'][$i];
    $value_price =  $_POST[$cv_id . '_price'];

    if (PRICE_IS_BRUTTO=='true'){
      $value_price= ($value_price/((xtc_get_tax_rate(xtc_get_tax_class_id($_POST['current_product_id'])))+100)*100);
    }

    $value_price=xtc_round($value_price,PRICE_PRECISION);
    
    //default values
    $sql_data_array = array ('products_id' => $_POST['current_product_id'],
                             'options_id' => $optionsID,
                             'options_values_id' => $_POST['optionValues'][$i],
                             'options_values_price' => $value_price,
                             'price_prefix' => $_POST[$cv_id . '_prefix'],
                             'attributes_model' => xtc_db_prepare_input($_POST[$cv_id . '_model']),
                             'attributes_stock' => $_POST[$cv_id . '_stock'],
                             'options_values_weight' => $_POST[$cv_id . '_weight'],
                             'weight_prefix' => $_POST[$cv_id . '_weight_prefix'],
                             'sortorder' => $_POST[$cv_id . '_sortorder']
                             );
    //additional values
    $add_data_array = array ('attributes_ean' => xtc_db_prepare_input($_POST[$cv_id . '_ean']));
    
    $sql_data_array = xtc_array_merge($sql_data_array, $add_data_array);
    xtc_db_perform(TABLE_PRODUCTS_ATTRIBUTES, $sql_data_array);
    $products_attributes_id = xtc_db_insert_id();

    if ($_POST[$cv_id . '_download_file'] != '') {
      $value_download_file = $_POST[$cv_id . '_download_file'];
      $value_download_expire = $_POST[$cv_id . '_download_expire'];
      $value_download_count = $_POST[$cv_id . '_download_count'];

      $sql_data_array = array ('products_attributes_id' => $products_attributes_id,
                               'products_attributes_filename' => xtc_db_prepare_input($value_download_file),
                               'products_attributes_maxdays' => $value_download_expire,
                               'products_attributes_maxcount' => $value_download_count
                              );
      xtc_db_perform(TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD, $sql_data_array);
    }
  }
  xtc_db_query('UPDATE ' . TABLE_PRODUCTS . ' SET products_last_modified=now() WHERE products_id=' . (int)$_POST['current_product_id']); //DokuMan - 2010-09-21 - set modified date on product
?>