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
    // the posted status link of a manufacturer has no fields when the form token is switched off
    if (isset($_POST)
        && (count($_POST) > 0
            || (isset($_SERVER['REQUEST_METHOD'])
                && $_SERVER['REQUEST_METHOD'] == 'POST'
                && basename($PHP_SELF) == 'manufacturers.php'
                && isset($_GET['action'])
                && $_GET['action'] === 'setflag'
                )
            )
        )
    {
      $sql_data_array = array();
  
      switch (basename($PHP_SELF)) {
        case 'categories.php':
          if (isset($_POST['products_id'])) {
            $sql_data_array = array(
              'customers_id' => (int)$_SESSION['customer_id'],
              'products_id' => (int)$_POST['products_id'],
            );
          }
          if (isset($_POST['multi_products']) 
              && is_array($_POST['multi_products']) 
              && count($_POST['multi_products']) > 0
              && isset($_POST['multi_delete_confirm'])
              )
          {
            foreach ($_POST['multi_products'] as $products_id) {
              $sql_data_array[] = array(
                'customers_id' => (int)$_SESSION['customer_id'],
                'products_id' => (int)$products_id,
              );
            }
          }
    
          if (isset($_POST['categories_id'])) {
            $sql_data_array = array(
              'customers_id' => (int)$_SESSION['customer_id'],
              'categories_id' => (int)$_POST['categories_id'],
            );
          }
          if (isset($_POST['multi_categories']) 
              && is_array($_POST['multi_categories']) 
              && count($_POST['multi_categories']) > 0
              && isset($_POST['multi_delete_confirm'])
              )
          {
            foreach ($_POST['multi_categories'] as $categories_id) {
              $sql_data_array[] = array(
                'customers_id' => (int)$_SESSION['customer_id'],
                'categories_id' => (int)$categories_id,
              );
            }
          }
          break;
      
        case 'content_manager.php':
          // products, content and e-mail content count their entries on their own, the type keeps them apart from the pages
          // and from older entries, which have none
          $admin_log_content_types = array(
            'delete_product' => 'product',
            'update_products' => 'product',
            'insert_products' => 'product',
            'delete_content' => 'content',
            'update_content_manager' => 'content',
            'insert_content_manager' => 'content',
            'delete_email' => 'email',
            'update_email' => 'email',
            'insert_email' => 'email',
          );
          $admin_log_content_type = 'pages';
          foreach (array('special', 'id') as $key) {
            if (isset($_GET[$key]) && is_string($_GET[$key]) && isset($admin_log_content_types[$_GET[$key]])) {
              $admin_log_content_type = $admin_log_content_types[$_GET[$key]];
            }
          }
          $sql_data_array = array(
            'customers_id' => (int)$_SESSION['customer_id'],
            'content_group' => (int)$_GET['coID'],
            'type' => $admin_log_content_type,
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
      
          if (isset($_POST['oID'])) {
            $sql_data_array = array(
              'customers_id' => (int)$_SESSION['customer_id'],
              'orders_id' => (int)$_POST['oID'],
              'type' => $type,
            );
          }
          break;

        case 'configuration.php':
          $sql_data_array = array(
            'customers_id' => (int)$_SESSION['customer_id'],
            'configuration_id' => (int)$_GET['gID'],
          );
          break;
  
      }
  
      if (count($sql_data_array) > 0) {
        // the form token is no part of the change
        $log_data = $_POST;
        if (isset($_SESSION['CSRFName'])) {
          unset($log_data[$_SESSION['CSRFName']]);
        }
        // a posted link carries its change in the address
        if (count($log_data) < 1) {
          foreach (array('action', 'subaction', 'special', 'flag', 'coIndex') as $key) {
            if (isset($_GET[$key]) && is_string($_GET[$key])) {
              $log_data[$key] = $_GET[$key];
            }
          }
        }
        $text = base64_encode(serialize($log_data));
        
        if (isset($sql_data_array[0])) {
          foreach ($sql_data_array as $sql_data) {
            $sql_data['text'] = $text;
            $sql_data['date_modified'] = 'now()';
          
            xtc_db_perform('admin_log', $sql_data);
          }
        } else {
          $sql_data_array['text'] = $text;
          $sql_data_array['date_modified'] = 'now()';
        
          xtc_db_perform('admin_log', $sql_data_array);
        }
      }
    }
  }
?>