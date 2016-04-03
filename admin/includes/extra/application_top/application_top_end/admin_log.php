<?php
  /* --------------------------------------------------------------
   $Id$   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   ----------------------------------------------------------------
   Released under the GNU General Public License 
   --------------------------------------------------------------*/

  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );
  
  if (defined('MODULE_ADMIN_LOG_STATUS') && MODULE_ADMIN_LOG_STATUS == 'true') {
    if (isset($_POST) && count($_POST) > 0) {
      $sql_data_array = array();
  
      switch (basename($PHP_SELF)) {
        case 'categories.php':
          if (isset($_POST['products_id'])) {
            $sql_data_array = array(
              'customers_id' => (int)$_SESSION['customer_id'],
              'products_id' => (int)$_POST['products_id'],
            );
          }
    
          if (isset($_POST['categories_id'])) {
            $sql_data_array = array(
              'customers_id' => (int)$_SESSION['customer_id'],
              'categories_id' => (int)$_POST['categories_id'],
            );
          }
          break;
      
        case 'content_manager.php':
          $sql_data_array = array(
            'customers_id' => (int)$_SESSION['customer_id'],
            'content_group' => (int)$_GET['coID'],
          );
          break;

        case 'manufacturers.php':
          if (isset($_GET['mID']) && $_GET['mID'] != '0') {
            $sql_data_array = array(
              'customers_id' => (int)$_SESSION['customer_id'],
              'manufacturers_id' => (int)$_GET['mID'],
            );
          }
          break;

        case 'modules.php':
        case 'module_export.php':
          $sql_data_array = array(
            'customers_id' => (int)$_SESSION['customer_id'],
            'module' => $_GET['module'],
            'type' => xtc_db_prepare_input($_GET['set']),
          );
          break;

        case 'orders.php':
          $sql_data_array = array(
            'customers_id' => (int)$_SESSION['customer_id'],
            'orders_id' => (int)$_GET['oID'],
          );
          break;

        case 'orders_edit.php':
          $type = 'edit';
          if (isset($_GET['action'])) {
            switch ($_GET['action']) {
              case 'product_ins':
              case 'product_edit':
              case 'product_delete':
                $type = 'products';
                break;
              case 'product_option_ins':
              case 'product_option_edit':
              case 'product_option_delete':
                $type = 'options';
                break;
              case 'address_edit':
                $type = 'address';
                break;
              case 'save_order':
                $type = 'edit';
                break;
              default:
                $type = 'other';
                break;
            }
          }
      
          $sql_data_array = array(
            'customers_id' => (int)$_SESSION['customer_id'],
            'orders_id' => (int)$_POST['oID'],
            'type' => $type,
          );
          break;

        case 'configuration.php':
          $sql_data_array = array(
            'customers_id' => (int)$_SESSION['customer_id'],
            'configuration_id' => (int)$_GET['gID'],
          );
          break;
  
      }
  
      if (count($sql_data_array) > 0) {
        $sql_data_array['text'] = base64_encode(serialize($_POST));
        $sql_data_array['date_modified'] = 'now()';
      
        xtc_db_perform('admin_log', $sql_data_array);
      }
    }
  }
?>