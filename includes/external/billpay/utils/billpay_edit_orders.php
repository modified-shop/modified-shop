<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   Copyright (c) 2010 Billpay GmbH

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

$paymentMethod = $order->info['payment_method'];
if (in_array($paymentMethod, array('billpay', 'billpaydebit', 'billpaytransactioncredit'))) {
  $action   = $_GET['action'];
  $orderId   = $_GET['oID'];

  require_once(DIR_FS_EXTERNAL . 'billpay/api/ipl_xml_api.php');// DokuMan -2011-09-08 - BILLPAY payment module (in external directory)
  require_once(DIR_FS_EXTERNAL . 'billpay/api/php4/ipl_partialcancel_request.php');// DokuMan -2011-09-08 - BILLPAY payment module (in external directory)
  require_once(DIR_FS_CATALOG . 'includes/modules/payment/billpay.php');
  if (file_exists(DIR_FS_LANGUAGES . $language . '/modules/payment/billpay.php')) {
    require_once DIR_FS_LANGUAGES . $language . '/modules/payment/billpay.php';
  } else {
    require_once DIR_FS_LANGUAGES . 'german/modules/payment/billpay.php';
  }

  $billpay = new billpay(strtoupper($paymentMethod));

  $noArticleTaxCustomer = isCustomerWithoutTax($orderId);

  $articles = array();
  $totals = array(
    'rebatedecrease' => 0,
    'rebatedecreasegross' => 0,
    'shippingdecrease' => 0,
    'shippingdecreasegross' => 0
  );

  if ($action == 'address_edit') {
    $billpay->addHistoryEntry($orderId, MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_ADDRESS, 0);
    xtc_redirect(xtc_href_link(FILENAME_ORDERS, 'action=edit&oID='.$orderId));
    return;
  }
  else if($action == 'product_ins') {
    $billpay->addHistoryEntry($orderId, MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_PRODUCT, 0);
    xtc_redirect(xtc_href_link(FILENAME_ORDERS, 'action=edit&oID='.$orderId));
    return;
  }
  else if($action == 'payment_edit') {
    $billpay->addHistoryEntry($orderId, MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_PAYMENT, 0);
    xtc_redirect(xtc_href_link(FILENAME_ORDERS, 'action=edit&oID='.$orderId));
    return;
  }
  else if ($action == 'curr_edit') {
    $billpay->addHistoryEntry($orderId, MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_CURRENCY, 0);
    xtc_redirect(xtc_href_link(FILENAME_ORDERS, 'action=edit&oID='.$orderId));
    return;
  }
  else if ($action == "product_edit") {    // Insert article data
    $orderProductId     = $_POST['opID'];
    $newProductsId       = $_POST['products_id'];
    $newQuantity       = $_POST['products_quantity'];
    $newProductsTax       = $_POST['products_tax'];
    $newProductsPrice    = $_POST['products_price'];

    $success = false;
    foreach ($order->products as $product) {
      if ($product['opid'] == $orderProductId) {
        $quantityToReduce = $product['qty'] - $newQuantity;

        if ($newQuantity < 0) {
          $billpay->addHistoryEntry($orderId, MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_NEGATIVE_QUANTITY, 0);
        }
        else if ($newProductsTax != $product['tax']) {
          $billpay->addHistoryEntry($orderId, MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_TAX, 0);
        }
        else if ($newProductsPrice != $product['price']) {
          $billpay->addHistoryEntry($orderId, MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_PRICE, 0);
        }
        else if ($newProductsId != $product['id']) {
          $billpay->addHistoryEntry($orderId, MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_ID, 0);
        }
        else if ($quantityToReduce == 0) {
          $billpay->addHistoryEntry($orderId, MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_ZERO_REDUCTION, 0);
        }
        else if ($quantityToReduce < 0) {
          $billpay->addHistoryEntry($orderId, MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_NEGATIVE_REDUCTION, 0);
        }
        else {
          $success = true;
        }

        $articles[$product['opid']] += $quantityToReduce;
      }
    }

    if ($success == false) {
      xtc_redirect(xtc_href_link(FILENAME_ORDERS, 'action=edit&oID='.$orderId));
      return;
    }
  }
  else if ($action == "product_delete") {    // Loeschen eines Artikels aus der Bestellung
    $query = xtc_db_query("select products_quantity from ".TABLE_ORDERS_PRODUCTS." where orders_id = '".$_POST['oID']."' and orders_products_id = '".$_POST['opID']."'");
    if (xtc_db_num_rows($query)) {
      $data = xtc_db_fetch_array($query);

      $articles[$_POST['opID']] = $data['products_quantity'];
    }
  }
  else if ($action == "ot_edit") {    // OT Module
    $moduleClass = $_POST['class'];

    $query = xtc_db_query("select value from ".TABLE_ORDERS_TOTAL." where orders_id = '".$_POST['oID']."' and class = '".$moduleClass."'");

    if (xtc_db_num_rows($query)) {
      $data = xtc_db_fetch_array($query);

      $oldTotalValue = $data['value'];
      $newTotalValue = $_POST['value'];

      $billpayDelta = $oldTotalValue-$newTotalValue;

      if (in_array($moduleClass, $billpay->billpayShippingModules)) {  // shipping
        if ($newTotalValue < 0) {
          $billpay->addHistoryEntry($orderId, MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_NEGATIVE_SHIPPING, 0);
          xtc_redirect(xtc_href_link(FILENAME_ORDERS, 'action=edit&oID='.$orderId));
          return;
        }
        else if ($billpayDelta < 0) {
          $billpay->addHistoryEntry($orderId, MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_INCREASED_SHIPPING, 0);
          xtc_redirect(xtc_href_link(FILENAME_ORDERS, 'action=edit&oID='.$orderId));
          return;
        }

        $taxData = calculateModuleTax($moduleClass, $billpayDelta);
        $totals['shippingdecrease']     += $taxData['net'];
        $totals['shippingdecreasegross']   += $taxData['gross'];
      }
      else if (!in_array($moduleClass, $billpay->billpayExcludeModules)) {  // rebate
        $taxData = calculateModuleTax($moduleClass, $billpayDelta);
        $totals['rebatedecrease']       -= $taxData['net'];
        $totals['rebatedecreasegross']     -= $taxData['gross'];
      }
      else {
        $billpay->addHistoryEntry($orderId, MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_FORBIDDEN, 0);
        xtc_redirect(xtc_href_link(FILENAME_ORDERS, 'action=edit&oID='.$orderId));
        return;
      }
    }
    else { // totals that have been removed once must be sent in rebate field

      if (in_array($moduleClass, $billpay->billpayShippingModules)) {
        $billpay->addHistoryEntry($orderId, MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_ADDED_SHIPPING, 0);
        xtc_redirect(xtc_href_link(FILENAME_ORDERS, 'action=edit&oID='.$orderId));
        return;
      }
      else if (!in_array($moduleClass, $billpay->billpayExcludeModules)) {
        $moduleClass = $_POST['class'];
        $taxData = calculateModuleTax($moduleClass, $_POST['value']);

        $totals['rebatedecrease']       += $taxData['net'];
        $totals['rebatedecreasegross']     += $taxData['gross'];
      }
      else {
        $billpay->addHistoryEntry($orderId, MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_FORBIDDEN, 0);
        xtc_redirect(xtc_href_link(FILENAME_ORDERS, 'action=edit&oID='.$orderId));
        return;
      }
    }
  }
  else if ($action == "ot_delete") {    // Loeschen eines OT Moduls aus der Bestellung
    $query = xtc_db_query("SELECT value, class FROM ".TABLE_ORDERS_TOTAL." WHERE orders_total_id = '".xtc_db_input($_POST['otID'])."'");
    if (xtc_db_num_rows($query)) {
      $data = xtc_db_fetch_array($query);

      $moduleClass = $data['class'];
      if (in_array($moduleClass, $billpay->billpayShippingModules)) {  // shipping
        $taxData = calculateModuleTax($moduleClass, $data['value']);
        $totals['shippingdecrease']     += $taxData['net'];
        $totals['shippingdecreasegross']   += $taxData['gross'];
      }
      else if (!in_array($moduleClass, $billpay->billpayExcludeModules)) {  // rebate
        $taxData = calculateModuleTax($moduleClass, $data['value']);

        $totals['rebatedecrease']       -= $taxData['net'];
        $totals['rebatedecreasegross']     -= $taxData['gross'];
      }
    }
  }
  else if ($action == "shipping_edit") {    // Versandkosten
    $query = xtc_db_query("select value from ".TABLE_ORDERS_TOTAL." where orders_id = '".$_POST['oID']."' and class = 'ot_shipping'");

    if (xtc_db_num_rows($query)) {
      $data = xtc_db_fetch_array($query);

      $oldShippingValue = $data['value'];
      $newShippingValue = $_POST['value'];

      $billpayDelta = $oldShippingValue-$newShippingValue;

      if ($newShippingValue < 0) {
        $billpay->addHistoryEntry($orderId, MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_NEGATIVE_SHIPPING, 0);
        xtc_redirect(xtc_href_link(FILENAME_ORDERS, 'action=edit&oID='.$orderId));
        return;
      }
      else if ($billpayDelta < 0) {
        $billpay->addHistoryEntry($orderId, MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_INCREASED_SHIPPING, 0);
        xtc_redirect(xtc_href_link(FILENAME_ORDERS, 'action=edit&oID='.$orderId));
        return;
      }

      $taxData = calculateModuleTax('ot_shipping', $billpayDelta);
      $totals['shippingdecrease']     += $taxData['net'];
      $totals['shippingdecreasegross']   += $taxData['gross'];
    }
    else {
      $billpay->addHistoryEntry($orderId, MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_FORBIDDEN, 0);
      xtc_redirect(xtc_href_link(FILENAME_ORDERS, 'action=edit&oID='.$orderId));
      return;
    }
  }
  else if ($action == "product_option_edit") {
    $query = xtc_db_query("SELECT options_values_price, price_prefix FROM ".TABLE_ORDERS_PRODUCTS_ATTRIBUTES." WHERE orders_id='".$_POST['oID']."' AND orders_products_attributes_id='".$_POST['opAID']."'");
    if (xtc_db_num_rows($query)) {
      $data = xtc_db_fetch_array($query);

      if ($data['options_values_price'] !=  $_POST['options_values_price']) {
        $billpay->addHistoryEntry($orderId, MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_ADJUST_CHARGEABLE, 0);
        xtc_redirect(xtc_href_link(FILENAME_ORDERS, 'action=edit&oID='.$orderId));
        return;
      }

      if ($data['price_prefix'] !=  $_POST['prefix']) {
        $billpay->addHistoryEntry($orderId, MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_ADJUST_CHARGEABLE, 0);
        xtc_redirect(xtc_href_link(FILENAME_ORDERS, 'action=edit&oID='.$orderId));
        return;
      }
    }
  }
  else if ($action == "product_option_ins") {
    if ($_POST['options_values_price'] != 0) {
      $billpay->addHistoryEntry($orderId, MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_ADD_CHARGEABLE, 0);
      xtc_redirect(xtc_href_link(FILENAME_ORDERS, 'action=edit&oID='.$orderId));
      return;
    }
  }
  else if ($action == "product_option_delete") {
    $query = xtc_db_query("SELECT options_values_price FROM ".TABLE_ORDERS_PRODUCTS_ATTRIBUTES." WHERE orders_id='".$_POST['oID']."' AND orders_products_attributes_id='".$_POST['opAID']."'");
    if (xtc_db_num_rows($query)) {
      $data = xtc_db_fetch_array($query);

      if ($data['options_values_price'] != 0) {
        $billpay->addHistoryEntry($orderId, MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_REMOVE_CHARGEABLE, 0);
        xtc_redirect(xtc_href_link(FILENAME_ORDERS, 'action=edit&oID='.$orderId));
        return;
      }
    }
  }
  else if ($action == "save_order") {
    $success = true;

    if ($noArticleTaxCustomer == false) {
      // fetch articles from buffer
      $query = xtc_db_query("SELECT reference, sum(quantity) as qty FROM billpay_edit_orders_buffer WHERE orders_id='".$orderId."' AND entity_type=0 GROUP BY reference");

      while ($row = xtc_db_fetch_array($query)) {
        $articles[$row['reference']] = $row['qty'];
      }

      // fetch rebate from buffer
      $query = xtc_db_query("SELECT sum(value_units_1) as gross, sum(value_units_2) as net FROM billpay_edit_orders_buffer WHERE orders_id='".$orderId."' AND entity_type=1");
      if (xtc_db_num_rows($query)) {

        $data = xtc_db_fetch_array($query);
        $totals['rebatedecreasegross']  = ($data['gross'] ? $data['gross'] : 0);
        $totals['rebatedecrease']    = ($data['net'] ? $data['net'] : 0);
      }

      // fetch shipping from buffer
      $query = xtc_db_query("SELECT sum(value_units_1) as gross, sum(value_units_2) as net FROM billpay_edit_orders_buffer WHERE orders_id='".$orderId."' AND entity_type=2");
      if (xtc_db_num_rows($query)) {
        $data = xtc_db_fetch_array($query);
        $totals['shippingdecreasegross']  = ($data['gross'] ? $data['gross'] : 0);
        $totals['shippingdecrease']      = ($data['net'] ? $data['net'] : 0);
      }


      if (count($articles) > 0 || $totals['rebatedecreasegross'] != 0 || $totals['shippingdecreasegross'] != 0) {
        $currency = $order->info['currency'];
        if (!$currency) {
          $currency = 'EUR';
        }
        $success = $billpay->sendPartialCancel($orderId, $articles, $totals, $currency);
      }

      //if ($success == false) {
      //  xtc_redirect(xtc_href_link(FILENAME_ORDERS, 'action=edit&oID='.$orderId));
      //  return;
      //}
      //else {
        // clear buffer
        xtc_db_query("DELETE FROM billpay_edit_orders_buffer WHERE orders_id='".$orderId."'");
      //}
    }
    else {
      $billpay->addHistoryEntry($orderId, MODULE_PAYMENT_BILLPAY_PARTIAL_CANCEL_NOT_PROCESSED, 0);
    }
  }

  if ($action != "save_order" && $noArticleTaxCustomer == false) {
    /**
     * Entity types
     * 0: article
     * 1: rebate
     * 2: shipping
     */

    // store data in buffer
    foreach ($articles as $id => $quantity) {
      xtc_db_query("INSERT INTO billpay_edit_orders_buffer (orders_id, entity_type, reference, quantity) VALUE ('".$orderId."', 0, '".$id."', '".$quantity."')");
    }

    if ($totals['rebatedecreasegross'] != 0) {
      xtc_db_query("INSERT INTO billpay_edit_orders_buffer (orders_id, entity_type, quantity, value_units_1, value_units_2) VALUE ('".$orderId."', 1, 1, '".$billpay->_currencyToSmallerUnit($totals['rebatedecreasegross'])."', '".$billpay->_currencyToSmallerUnit($totals['rebatedecrease'])."')");
    }
    if ($totals['shippingdecreasegross'] > 0) {
      xtc_db_query("INSERT INTO billpay_edit_orders_buffer (orders_id, entity_type, quantity, value_units_1, value_units_2) VALUE ('".$orderId."', 2, 1, '".$billpay->_currencyToSmallerUnit($totals['shippingdecreasegross'])."', '".$billpay->_currencyToSmallerUnit($totals['shippingdecrease'])."')");
    }
  }
}



function calculateModuleTax($moduleClass, $amount) {
  global $order, $xtPrice;

  $moduleName = str_replace('ot_', '', $moduleClass);

  if ($moduleName != 'discount') {
    if ($moduleName != 'shipping') {
      $moduleTaxClass = constant('MODULE_ORDER_TOTAL_'.strtoupper($moduleName).'_TAX_CLASS');
    }
    else {
      $moduleTmpName = explode('_', $order->info['shipping_class']);
      $moduleTmpName = $moduleTmpName[0];
      if ($moduleTmpName != 'selfpickup' && $moduleTmpName != 'free') {
        $moduleTaxClass = constant('MODULE_SHIPPING_'.strtoupper($moduleTmpName).'_TAX_CLASS');
      } else {
        $moduleTaxClass = '0';
      }
    }
  }
  else {
    $moduleTaxClass = '0';
  }

  $cinfo = xtc_oe_customer_infos($order->customer['ID']);
  $moduleTaxRate = xtc_get_tax_rate($moduleTaxClass, $cinfo['country_id'], $cinfo['zone_id']);

  $status_query = xtc_db_query("select customers_status_show_price_tax, customers_status_add_tax_ot from ".TABLE_CUSTOMERS_STATUS." where customers_status_id = '".$order->info['status']."'");
  $status = xtc_db_fetch_array($status_query);

  if ($status['customers_status_show_price_tax'] == 1) {
    $module_b_price = $amount;

    if ($moduleTaxClass == '0') {
      $module_n_price = $amount;
    }
    else {
      $module_n_price = $xtPrice->xtcRemoveTax($module_b_price, $moduleTaxRate);
    }
    $module_tax = $xtPrice->calcTax($module_n_price, $moduleTaxRate);
  }
  else {
    $module_n_price = $amount;
    $module_b_price = $xtPrice->xtcAddTax($module_n_price, $moduleTaxRate);
    $module_tax = $xtPrice->calcTax($module_n_price, $moduleTaxRate);
  }

  return array(
    'tax' => xtc_db_prepare_input($module_tax),
    'tax_rate' => xtc_db_prepare_input($moduleTaxRate),
    'net' => xtc_db_prepare_input($module_n_price),
    'gross' => xtc_db_prepare_input($module_b_price)
  );
}

function isCustomerWithoutTax($orderId) {
  $query = xtc_db_query("select customers_status_show_price_tax, customers_status_add_tax_ot from ".TABLE_CUSTOMERS_STATUS." join ".TABLE_ORDERS." on customers_status = customers_status_id where orders_id = ".$orderId." LIMIT 1");
  $data = xtc_db_fetch_array($query);
  if ($data['customers_status_show_price_tax'] == 0 && $data['customers_status_add_tax_ot'] == 1) {
    return true;
  }
  else {
    return false;
  }
}

?>