<?php
/* --------------------------------------------------------------
   $Id: products_attributes_action.php 3674 2012-09-26 12:27:49Z web28 $

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

   products_attribtues_action (c) www.rpa-com.de
   --------------------------------------------------------------*/

  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  $PHP_SELF = (!isset($PHP_SELF)) ? $_SERVER['SCRIPT_NAME'] : $PHP_SELF; //compatibility for modified eCommerce Shopsoftware 1.06 files

  switch($action) {

    // NEW OPTIONS
    case 'add_product_options':
      if (!empty($_POST['products_options_id'])) {
        $option_name = $_POST['option_name'];
        for ($i = 0, $n = sizeof($languages); $i < $n; $i ++) {
          if (!empty($option_name[$languages[$i]['id']])) {
            $sql_data_array = array('products_options_id' => (int)$_POST['products_options_id'],
                                    'products_options_name' => xtc_db_prepare_input(trim($option_name[$languages[$i]['id']])),
                                    'language_id' => $languages[$i]['id'],
                                    'products_options_sortorder' => (int)$_POST['products_options_sortorder']
                                   );
            $update = xtc_db_perform(TABLE_PRODUCTS_OPTIONS, $sql_data_array);
          }
        }
        $updatestatus = ($update) ? 'true': 'empty';
      }

      xtc_redirect(xtc_href_link(basename($PHP_SELF),$page_info.'&add_product_option_status='.$updatestatus.'&option_order_by='.$_GET['option_order_by']));
      break;

    //UPDATE OPTIONS
    case 'update_option_name':
      if (!empty($_POST['option_id'])) {
        $option_name = $_POST['option_name'];
        for ($i = 0, $n = sizeof($languages); $i < $n; $i ++) {
          if (!empty($option_name[$languages[$i]['id']])) {
            //BOF - web28 - 2010-07-11 - BUGFIX no entry stored for previous deactivated languages
            $products_options_query = xtc_db_query("SELECT * FROM ".TABLE_PRODUCTS_OPTIONS." WHERE language_id = '".$languages[$i]['id']."' AND products_options_id = '".(int)$_POST['option_id']."'");
            if (xtc_db_num_rows($products_options_query) == 0) xtc_db_perform(TABLE_PRODUCTS_OPTIONS, array ('products_options_id' => (int)$_POST['option_id'], 'language_id' => $languages[$i]['id']));
            //EOF - web28 - 2010-07-11 - BUGFIX no entry stored for previous deactivated languages
            $sql_data_array = array('products_options_name' => xtc_db_prepare_input(trim($option_name[$languages[$i]['id']])),
                                    'products_options_sortorder' => (int)$_POST['products_options_sortorder']
                                   );
            $update = xtc_db_perform(TABLE_PRODUCTS_OPTIONS, $sql_data_array, 'update', "products_options_id = '".(int)$_POST['option_id']."' AND language_id = '".$languages[$i]['id']."'");
          }
          $updatestatus = ($update) ? 'true': 'empty';
        }
      }
      xtc_redirect(xtc_href_link(basename($PHP_SELF),$page_info.'&update_option_name_status='.$updatestatus.'&option_order_by='.$_GET['option_order_by']));
      break;

    //DELETE OPTIONS
    case 'delete_option':
      $del_options = xtc_db_query("SELECT products_options_values_id FROM " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . "
                                                                    WHERE products_options_id = '" . (int)$_GET['option_id'] . "'");

      while($del_options_values = xtc_db_fetch_array($del_options)){
        // BOF - Dokuman - 2009-09-02: Beim Löschen eines Artikelmerkmals werden die zugehörigen Optionswerte nicht mitgelöscht
        xtc_db_query("DELETE FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . " WHERE products_options_values_id = '" . (int)$del_options_values['products_options_values_id'] . "'");
        // EOF - Dokuman - 2009-09-02: Beim Löschen eines Artikelmerkmals werden die zugehörigen Optionswerte nicht mitgelöscht
      }
      xtc_db_query("DELETE FROM " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " WHERE products_options_id = '" . (int)$_GET['option_id'] . "'");
      xtc_db_query("DELETE FROM " . TABLE_PRODUCTS_OPTIONS . " WHERE products_options_id = '" . (int)$_GET['option_id'] . "'");

      xtc_redirect(xtc_href_link(basename($PHP_SELF), $page_info));
      break;

    // NEW OPTIONSVALUES
    case 'add_product_option_values':
      if ( !empty($_POST['option_id']) && !empty($_POST['value_id'])) {
        $value_name = $_POST['value_name'];
        for ($i = 0, $n = sizeof($languages); $i < $n; $i ++) {
          if (!empty($value_name[$languages[$i]['id']])) {
            $sql_data_array = array('products_options_values_id' => (int)$_POST['value_id'],
                                    'language_id' => $languages[$i]['id'],
                                    'products_options_values_name' => xtc_db_prepare_input(trim($value_name[$languages[$i]['id']]))
                                   );
            $update = xtc_db_perform(TABLE_PRODUCTS_OPTIONS_VALUES, $sql_data_array);
          }
        }
        if($update) {
          $sql_data_array = array('products_options_id' => (int)$_POST['option_id'],
                                  'products_options_values_id' => (int)$_POST['value_id']
                                 );
          $update2 = xtc_db_perform(TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS, $sql_data_array);
          $updatestatus = ($update2) ? 'true' : 'false';
        } else $updatestatus = 'empty';
      }
      xtc_redirect(xtc_href_link(basename($PHP_SELF),$page_info.'&add_product_option_values_status='.$updatestatus.'&option_order_by='.$_GET['option_order_by']));
      break;

    //UPDATE OPTIONSVALUES
    case 'update_value':
      if ( !empty($_POST['option_id']) && !empty($_POST['value_id'])) {
        $value_name = $_POST['value_name'];
        for ($i = 0, $n = sizeof($languages); $i < $n; $i ++) {
          if (!empty($value_name[$languages[$i]['id']])) {
            //BOF - web28 - 2010-07-11 - BUGFIX no entry stored for previous deactivated languages
            $products_options_values_query = xtc_db_query("SELECT * FROM ".TABLE_PRODUCTS_OPTIONS_VALUES." WHERE language_id = '".$languages[$i]['id']."' AND products_options_values_id = '".(int)$_POST['value_id']."'");
            if (xtc_db_num_rows($products_options_values_query) == 0) xtc_db_perform(TABLE_PRODUCTS_OPTIONS_VALUES, array ('products_options_values_id' => (int)$_POST['value_id'], 'language_id' => $languages[$i]['id']));
            //EOF - web28 - 2010-07-11 - BUGFIX no entry stored for previous deactivated languages
            $sql_data_array = array('products_options_values_name' => xtc_db_prepare_input(trim($value_name[$languages[$i]['id']])));
            $update = xtc_db_perform(TABLE_PRODUCTS_OPTIONS_VALUES, $sql_data_array, 'update', "products_options_values_id = '".(int)$_POST['value_id']."' AND language_id = '".$languages[$i]['id']."'");
          }
        }
        if($update) {
          $update2 = xtc_db_query("UPDATE " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . "
                                      SET products_options_id = '" . (int)$_POST['option_id'] . "'
                                    WHERE products_options_values_id = '" . (int)$_POST['value_id'] . "'");
          $updatestatus = ($update2) ? 'true' : 'false';
        } else $updatestatus = 'empty';
      }
      xtc_redirect(xtc_href_link(basename($PHP_SELF),$page_info.'&update_value_name_status='.$updatestatus.'&option_order_by='.$_GET['option_order_by']));
      break;

    //DELETE OPTIONSVALUES
    case 'delete_value':
      xtc_db_query("DELETE FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . " WHERE products_options_values_id = '" . (int)$_GET['value_id'] . "'");
      xtc_db_query("DELETE FROM " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " WHERE products_options_values_id = '" . (int)$_GET['value_id'] . "'");
      xtc_redirect(xtc_href_link(basename($PHP_SELF), $page_info));
      break;
  }