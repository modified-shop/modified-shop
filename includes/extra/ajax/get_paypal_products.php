<?php
/**
 * $Id: get_paypal_data.php 12577 2020-02-20 17:28:18Z GTB $
 *
 * modified eCommerce Shopsoftware
 * http://www.modified-shop.org
 *
 * Copyright (c) 2009 - 2013 [www.modified-shop.org]
 *
 * Released under the GNU General Public License
 */

function get_paypal_products() {  
  if (!isset($_GET['sec'])
      || $_GET['sec'] != MODULE_PAYMENT_PAYPAL_SECRET
      )
  {
    return;
  }

  require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalInfo.php');
  $paypal = new PayPalInfo('subscription');
  
  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  
  ob_start();
  switch ($action) {
    case 'create_plan':
    case 'patch_plan':
      $data = array_merge(array('products_id' => (int)$_GET['pID']), $_POST);
      $data['paypal_plan_fixed_price'] = str_replace(',', '.', preg_replace('/[^0-9,.]/', '', $data['paypal_plan_fixed_price']));
      $data['paypal_plan_setup_fee'] = str_replace(',', '.', preg_replace('/[^0-9,.]/', '', $data['paypal_plan_setup_fee']));
      
      if ($_GET['action'] == 'patch_plan') {
        $success = $paypal->patch_plan($data);      
      } else {
        $success = $paypal->create_plans($data);
      }
      include(DIR_FS_EXTERNAL.'paypal/modules/products_paypal_data.php');      
      break;
      
    case 'create_product':
    case 'patch_product':
      $products_query = xtc_db_query("SELECT *
                                        FROM ".TABLE_PRODUCTS." p
                                        JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                             ON p.products_id = pd.products_id
                                                AND pd.language_id = '".$_SESSION['languages_id']."'
                                       WHERE p.products_id = '".(int)$_GET['pID']."'");
      $products = xtc_db_fetch_array($products_query);
      $products = array_merge($products, $_POST);
      
      if ($_GET['action'] == 'patch_product') {
        $success = $paypal->patch_product($products);
      } else {
        $success = $paypal->create_product($products);
      }
      
      include(DIR_FS_EXTERNAL.'paypal/modules/products_paypal_data.php');      
      break;
    
    default:
      include(DIR_FS_EXTERNAL.'paypal/modules/products_paypal_data.php');      
      break;
  }
  $output = ob_get_contents();
  ob_end_clean();  
  
  $output = encode_htmlentities($output);
  $output = base64_encode($output);

  return $output;
}
?>