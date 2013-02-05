<?php
/* -----------------------------------------------------------------------------------------
   $Id: checkout_process.php 3731 2012-09-30 17:35:19Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(checkout_process.php,v 1.128 2003/05/28); www.oscommerce.com
   (c) 2003 nextcommerce (checkout_process.php,v 1.30 2003/08/24); www.nextcommerce.org
   (c) 2006 XT-Commerce (checkout_process.php 1277 2005-10-01)

   Released under the GNU General Public License
    ----------------------------------------------------------------------------------------
   Third Party contribution:

   Customers Status v3.x  (c) 2002-2003 Copyright Elari elari@free.fr | www.unlockgsm.com/dload-osc/ | CVS : http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/elari/?sortby=date#dirlist

   Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
   http://www.oscommerce.com/community/contributions,282
   Copyright (c) Strider | Strider@oscworks.com
   Copyright (c) Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');

// include needed functions
require_once (DIR_FS_INC.'xtc_calculate_tax.inc.php');
require_once (DIR_FS_INC.'xtc_address_label.inc.php');
require_once (DIR_FS_INC.'changedatain.inc.php');

// initialize smarty
$smarty = new Smarty;

// BOF - Tomcraft - 2009-10-03 - Paypal Express Modul
if (@is_array($_SESSION['nvpReqArray']) && $_SESSION['payment'] == 'paypalexpress') {
  if ($_POST['comments_added'] != '') {
    $_SESSION['comments'] = xtc_db_prepare_input($_POST['comments']);
  }
}
$error_mess='';
if (DISPLAY_CONDITIONS_ON_CHECKOUT == 'true') {
  if (@is_array($_SESSION['nvpReqArray']) && $_POST['conditions'] != 'conditions' && $_SESSION['payment'] == 'paypalexpress') {
    $error_mess='1';
  }
}
if (@is_array($_SESSION['nvpReqArray']) && $_POST['check_address'] != 'address' && $_SESSION['payment'] == 'paypalexpress') {
  $error_mess.='2';
}
if($error_mess!='') {
  xtc_redirect(xtc_href_link(FILENAME_PAYPAL_CHECKOUT, 'error_message='.$error_mess, 'SSL', true, false));
}
// EOF - Tomcraft - 2009-10-03 - Paypal Express Modul

// if the customer is not logged on, redirect them to the login page
if (!isset ($_SESSION['customer_id'])) {
  xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
}

if ($_SESSION['customers_status']['customers_status_show_price'] != '1') {
  //BOF - web28.de - FIX redirect to NONSSL
  //xtc_redirect(xtc_href_link(FILENAME_DEFAULT, '', ''));
  xtc_redirect(xtc_href_link(FILENAME_DEFAULT,'','NONSSL'));
  //EOF - web28.de - FIX redirect to NONSSL
}

// BOF - Tomcraft - 2009-10-03 - Paypal Express Modul
/*
if (!isset ($_SESSION['sendto'])) {
  xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
}

if ((xtc_not_null(MODULE_PAYMENT_INSTALLED)) && (!isset ($_SESSION['payment']))) {
  xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
}

// avoid hack attempts during the checkout procedure by checking the internal cartID
if (isset ($_SESSION['cart']->cartID) && isset ($_SESSION['cartID'])) {
  if ($_SESSION['cart']->cartID != $_SESSION['cartID']) {
    xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  }
}
*/
if (!isset ($_SESSION['sendto'])) {
  if($_SESSION['payment']=='paypalexpress') {
    xtc_redirect(xtc_href_link(FILENAME_PAYPAL_CHECKOUT, '', 'SSL'));
  } else {
    xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
  }
}

if ((xtc_not_null(MODULE_PAYMENT_INSTALLED)) && (!isset ($_SESSION['payment']))) {
  xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
}

// avoid hack attempts during the checkout procedure by checking the internal cartID
if (isset ($_SESSION['cart']->cartID) && isset ($_SESSION['cartID'])) {
  if ($_SESSION['cart']->cartID != $_SESSION['cartID']) {
    if($_SESSION['payment']=='paypalexpress') {
      xtc_redirect(xtc_href_link(FILENAME_PAYPAL_CHECKOUT, '', 'SSL'));
    } else {
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
    }
  }
}
// EOF - Tomcraft - 2009-10-03 - Paypal Express Modul

// load selected payment module
require_once  (DIR_WS_CLASSES.'payment.php');
if (isset ($_SESSION['credit_covers'])) {
  $_SESSION['payment'] = ''; //ICW added for CREDIT CLASS 
}
$payment_modules = new payment($_SESSION['payment']);

// if no shipping method has been selected, redirect the customer to the shipping method selection page
if (!isset ($_SESSION['shipping'])) {
  xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
}

// load the selected shipping module
require (DIR_WS_CLASSES.'shipping.php');
$shipping_modules = new shipping($_SESSION['shipping']);

//--- SHOPSTAT -------------------------//
//require (DIR_WS_CLASSES.'order.php');
require_once(DIR_WS_CLASSES.'order.php');
//--- SHOPSTAT -------------------------//
$order = new order();

// load the before_process function from the payment modules
$payment_modules->before_process();

require (DIR_WS_CLASSES.'order_total.php');
$order_total_modules = new order_total();
$order_totals = $order_total_modules->process();

// check if tmp order id exists
if (isset ($_SESSION['tmp_oID']) && is_numeric($_SESSION['tmp_oID'])) { // Dokuman - 2009-10-11 - Paypal fix for infinite loop see, http://www.modified-shop.org/forum/topic.php?id=2235
  $tmp = false;
  $insert_id = $_SESSION['tmp_oID'];
} else {
  // check if tmp order need to be created
  if (isset ($$_SESSION['payment']->form_action_url) && $$_SESSION['payment']->tmpOrders) {
    $tmp = true;
    $tmp_status = $$_SESSION['payment']->tmpStatus;
  } else {
    $tmp = false;
    $tmp_status = $order->info['order_status'];
  }

  // BMC CC Mod Start
  if (defined('CC_ENC') && strtolower(CC_ENC) == 'true') {
    $plain_data = $order->info['cc_number'];
    $order->info['cc_number'] = changedatain($plain_data, CC_KEYCHAIN);
  }
  // BMC CC Mod End

  if ($_SESSION['customers_status']['customers_status_ot_discount_flag'] == 1) {
    $discount = $_SESSION['customers_status']['customers_status_ot_discount'];
  } else {
    $discount = '0.00';
  }

  if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $customers_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
  } else {
    $customers_ip = $_SERVER['REMOTE_ADDR'];
  }

  $sql_data_array = array ('customers_id' => $_SESSION['customer_id'],
                           'customers_name' => $order->customer['firstname'].' '.$order->customer['lastname'],
                           'customers_firstname' => $order->customer['firstname'],
                           'customers_lastname' => $order->customer['lastname'],
                           'customers_cid' => $order->customer['csID'],
                           'customers_vat_id' => $_SESSION['customer_vat_id'],
                           'customers_company' => $order->customer['company'],
                           'customers_status' => $_SESSION['customers_status']['customers_status_id'],
                           'customers_status_name' => $_SESSION['customers_status']['customers_status_name'],
                           'customers_status_image' => $_SESSION['customers_status']['customers_status_image'],
                           'customers_status_discount' => $discount,
                           'customers_street_address' => $order->customer['street_address'],
                           'customers_suburb' => $order->customer['suburb'],
                           'customers_city' => $order->customer['city'],
                           'customers_postcode' => $order->customer['postcode'],
                           'customers_state' => $order->customer['state'],
                           'customers_country' => $order->customer['country']['title'],
                           'customers_telephone' => $order->customer['telephone'],
                           'customers_email_address' => $order->customer['email_address'],
                           'customers_address_format_id' => $order->customer['format_id'],
                           'delivery_name' => $order->delivery['firstname'].' '.$order->delivery['lastname'],
                           'delivery_firstname' => $order->delivery['firstname'],
                           'delivery_lastname' => $order->delivery['lastname'],
                           'delivery_company' => $order->delivery['company'],
                           'delivery_street_address' => $order->delivery['street_address'],
                           'delivery_suburb' => $order->delivery['suburb'],
                           'delivery_city' => $order->delivery['city'],
                           'delivery_postcode' => $order->delivery['postcode'],
                           'delivery_state' => $order->delivery['state'],
                           'delivery_country' => $order->delivery['country']['title'],
                           'delivery_country_iso_code_2' => $order->delivery['country']['iso_code_2'],
                           'delivery_address_format_id' => $order->delivery['format_id'],
                           'billing_name' => $order->billing['firstname'].' '.$order->billing['lastname'],
                           'billing_firstname' => $order->billing['firstname'],
                           'billing_lastname' => $order->billing['lastname'],
                           'billing_company' => $order->billing['company'],
                           'billing_street_address' => $order->billing['street_address'],
                           'billing_suburb' => $order->billing['suburb'],
                           'billing_city' => $order->billing['city'],
                           'billing_postcode' => $order->billing['postcode'],
                           'billing_state' => $order->billing['state'],
                           'billing_country' => $order->billing['country']['title'],
                           'billing_country_iso_code_2' => $order->billing['country']['iso_code_2'],
                           'billing_address_format_id' => $order->billing['format_id'],
                           'cc_start' => $order->info['cc_start'],
                           'cc_cvv' => $order->info['cc_cvv'],
                           'cc_issue' => $order->info['cc_issue'],                           
                           'payment_method' => $order->info['payment_method'],
                           'payment_class' => $order->info['payment_class'],
                           'shipping_method' => $order->info['shipping_method'],
                           'shipping_class' => $order->info['shipping_class'],
                           'cc_type' => $order->info['cc_type'],
                           'cc_owner' => $order->info['cc_owner'],
                           'cc_number' => $order->info['cc_number'],
                           'cc_expires' => $order->info['cc_expires'],
                           'date_purchased' => 'now()',
                           'orders_status' => $tmp_status,
                           'currency' => $order->info['currency'],
                           'currency_value' => $order->info['currency_value'],
                           'account_type' => $_SESSION['account_type'], //web28 - 2012-04-12 add missing account-type
                           'customers_ip' => $customers_ip,
                           'language' => $_SESSION['language'],
                           'comments' => $order->info['comments']
                           );  

  xtc_db_perform(TABLE_ORDERS, $sql_data_array);
  $insert_id = xtc_db_insert_id();
  $_SESSION['tmp_oID'] = $insert_id;

  for ($i = 0, $n = sizeof($order_totals); $i < $n; $i ++) {
    $sql_data_array = array ('orders_id' => $insert_id,
                             'title' => $order_totals[$i]['title'],
                             'text' => $order_totals[$i]['text'],
                             'value' => $order_totals[$i]['value'],
                             'class' => $order_totals[$i]['code'],
                             'sort_order' => $order_totals[$i]['sort_order']);
    xtc_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);
  }

  $customer_notification = (SEND_EMAILS == 'true') ? '1' : '0';

  $sql_data_array = array ('orders_id' => $insert_id,
                           'orders_status_id' => $order->info['order_status'],
                           'date_added' => 'now()',
                           'customer_notified' => $customer_notification,
                           'comments' => $order->info['comments']);
  xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

  // initialized for the email confirmation
  $products_ordered = '';
  $products_ordered_html = '';
  $subtotal = 0;
  $total_tax = 0;

  for ($i = 0, $n = sizeof($order->products); $i < $n; $i ++) {
    // Stock Update - Joao Correia
    if (STOCK_LIMITED == 'true') {
      if (DOWNLOAD_ENABLED == 'true') {
        $stock_query_raw = "-- /checkout_process.php
                            SELECT products_quantity, 
                                   pad.products_attributes_filename
                              FROM ".TABLE_PRODUCTS." p
                         LEFT JOIN ".TABLE_PRODUCTS_ATTRIBUTES." pa ON p.products_id=pa.products_id
                         LEFT JOIN ".TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD." pad ON pa.products_attributes_id=pad.products_attributes_id
                             WHERE p.products_id = '".xtc_get_prid($order->products[$i]['id'])."'";
        // Will work with only one option for downloadable products
        // otherwise, we have to build the query dynamically with a loop
        $products_attributes = $order->products[$i]['attributes'];
        if (is_array($products_attributes)) {
          $stock_query_raw .= " AND pa.options_id = '".$products_attributes[0]['option_id']."' AND pa.options_values_id = '".$products_attributes[0]['value_id']."'";
        }
        $stock_query = xtc_db_query($stock_query_raw);
      } else {
        $stock_query = xtc_db_query(" -- /checkout_process.php
                                    SELECT products_quantity
                                        FROM ".TABLE_PRODUCTS."
                                       WHERE products_id = '".xtc_get_prid($order->products[$i]['id'])."'");
      }
      if (xtc_db_num_rows($stock_query) > 0) {
        $stock_values = xtc_db_fetch_array($stock_query);
        // do not decrement quantities if products_attributes_filename exists
        if ((DOWNLOAD_ENABLED != 'true') || (!$stock_values['products_attributes_filename'])) {
          $stock_left = $stock_values['products_quantity'] - $order->products[$i]['qty'];
        } else {
          $stock_left = $stock_values['products_quantity'];
        }

        xtc_db_query("UPDATE ".TABLE_PRODUCTS."
                         SET products_quantity = '".$stock_left."'
                       WHERE products_id = '".xtc_get_prid($order->products[$i]['id'])."'");
        if (($stock_left < 1) && (STOCK_CHECKOUT_UPDATE_PRODUCTS_STATUS == 'true')) {
          xtc_db_query("UPDATE ".TABLE_PRODUCTS."
                           SET products_status = '0'
                         WHERE products_id = '".xtc_get_prid($order->products[$i]['id'])."'");
        }
      }
    }

    // Update products_ordered (for bestsellers list)
    xtc_db_query("UPDATE ".TABLE_PRODUCTS."
                     SET products_ordered = products_ordered + ".sprintf('%d', $order->products[$i]['qty'])."
                   WHERE products_id = '".xtc_get_prid($order->products[$i]['id'])."'");

    $sql_data_array = array ( 'orders_id' => $insert_id,
                              'products_id' => xtc_get_prid($order->products[$i]['id']),
                              'products_model' => $order->products[$i]['model'],
                              'products_name' => $order->products[$i]['name'],
                              'products_shipping_time'=>$order->products[$i]['shipping_time'],
                              'products_price' => $order->products[$i]['price'],
                              'final_price' => $order->products[$i]['final_price'],
                              'products_tax' => $order->products[$i]['tax'],
                              'products_discount_made' => $order->products[$i]['discount_allowed'],
                              'products_quantity' => $order->products[$i]['qty'],
                              'allow_tax' => $_SESSION['customers_status']['customers_status_show_price_tax']
                              );

    $add_data_array = array('products_order_description' => $order->products[$i]['order_description']);
    $sql_data_array = array_merge($sql_data_array, $add_data_array);
    xtc_db_perform(TABLE_ORDERS_PRODUCTS, $sql_data_array);
    $order_products_id = xtc_db_insert_id();

    // Aenderung Specials Quantity Anfang
    $specials_result = xtc_db_query("SELECT products_id,
                                            specials_quantity
                                       FROM ".TABLE_SPECIALS."
                                      WHERE products_id = '".xtc_get_prid($order->products[$i]['id'])."' ");
    if (xtc_db_num_rows($specials_result)) {
      $spq = xtc_db_fetch_array($specials_result);
      if ($spq['specials_quantity'] != 0) {
        $new_sp_quantity = ($spq['specials_quantity'] - $order->products[$i]['qty']);
        if ($new_sp_quantity >= 1) {
          xtc_db_query("UPDATE ".TABLE_SPECIALS." 
                           SET specials_quantity = '".$new_sp_quantity."' 
                         WHERE products_id = '".xtc_get_prid($order->products[$i]['id'])."' ");
        } else {
          xtc_db_query("UPDATE ".TABLE_SPECIALS." 
                           SET status = '0', 
                               specials_quantity = '".$new_sp_quantity."'
                         WHERE products_id = '".xtc_get_prid($order->products[$i]['id'])."' ");
        }
      }
    }
    // Aenderung Ende

    $order_total_modules->update_credit_account($i); // GV Code ICW ADDED FOR CREDIT CLASS SYSTEM
    //------insert customer choosen option to order--------
    $attributes_exist = '0';
    $products_ordered_attributes = '';
    if (isset ($order->products[$i]['attributes'])) {
      $attributes_exist = '1';
      for ($j = 0, $n2 = sizeof($order->products[$i]['attributes']); $j < $n2; $j ++) {
        $left_join_downloads = $add_products_attributes = '';
        if (DOWNLOAD_ENABLED == 'true') {
          $left_join_downloads = "LEFT JOIN ".TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD." pad ON pa.products_attributes_id=pad.products_attributes_id";
          $add_products_attributes = 'pad.products_attributes_maxdays,
                                      pad.products_attributes_maxcount,
                                      pad.products_attributes_filename,';
        }

        // update attribute stock
        if (STOCK_LIMITED == 'true') {
          xtc_db_query("UPDATE ".TABLE_PRODUCTS_ATTRIBUTES."
                           SET attributes_stock=attributes_stock - '".$order->products[$i]['qty']."'
                         WHERE products_id='".$order->products[$i]['id']."'
                           AND options_values_id='".$order->products[$i]['attributes'][$j]['value_id']."'
                           AND options_id='".$order->products[$i]['attributes'][$j]['option_id']."'
                         ");
        }

        $attributes_values = $main->getAttributes( $order->products[$i]['id'],
                                                   $order->products[$i]['attributes'][$j]['option_id'],
                                                   $order->products[$i]['attributes'][$j]['value_id'],
                                                   $add_products_attributes,
                                                   $left_join_downloads
                                                  );

        $sql_data_array = array ('orders_id' => $insert_id,
                                 'orders_products_id' => $order_products_id,
                                 'products_options' => $attributes_values['products_options_name'],
                                 'products_options_values' => $attributes_values['products_options_values_name'],
                                 'options_values_price' => $attributes_values['options_values_price'],
                                 'price_prefix' => $attributes_values['price_prefix'],
                                 'orders_products_options_id' => $order->products[$i]['attributes'][$j]['option_id'],
                                 'orders_products_options_values_id' => $order->products[$i]['attributes'][$j]['value_id']
                                );
        xtc_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $sql_data_array);

        if ((DOWNLOAD_ENABLED == 'true') && isset ($attributes_values['products_attributes_filename']) && xtc_not_null($attributes_values['products_attributes_filename'])) {
          $sql_data_array = array ('orders_id' => $insert_id,
                                   'orders_products_id' => $order_products_id,
                                   'orders_products_filename' => $attributes_values['products_attributes_filename'],
                                   'download_maxdays' => $attributes_values['products_attributes_maxdays'],
                                   'download_count' => $attributes_values['products_attributes_maxcount']
                                  );
          xtc_db_perform(TABLE_ORDERS_PRODUCTS_DOWNLOAD, $sql_data_array);
        }
      }
    }
    //------insert customer choosen option eof ----
    $total_weight += ($order->products[$i]['qty'] * $order->products[$i]['weight']);
    $total_tax += xtc_calculate_tax($total_products_price, $products_tax) * $order->products[$i]['qty'];
    $total_cost += $total_products_price;

  }

  // check refID
  if (isset ($_SESSION['tracking']['refID'])) {
    $refferers_id = $_SESSION['tracking']['refID'];
  } else {
    $customers_query = xtc_db_query("SELECT refferers_id as ref FROM ".TABLE_CUSTOMERS." WHERE customers_id='".(int)$_SESSION['customer_id']."'");
    $customers_data = xtc_db_fetch_array($customers_query);
    if (xtc_db_num_rows($customers_query)) {
      $refferers_id = $customers_data['ref'];
      $campaign_check_query = xtc_db_query("SELECT campaigns_refID as refID FROM " . TABLE_CAMPAIGNS . " WHERE campaigns_id = '" . $customers_data['ref'] . "'");
      if (xtc_db_num_rows($campaign_check_query) > 0) {
        $campaign = xtc_db_fetch_array($campaign_check_query);
        $refferers_id = $campaign['refID'];
      }
    }
  }
  //write refID into TABLE_ORDERS
  xtc_db_query("UPDATE ".TABLE_ORDERS."
                     SET refferers_id = '".$_SESSION['tracking']['refID']."'
                   WHERE orders_id = '".$insert_id."'");
  
  // check if late or direct sale
  $customers_logon_query = "-- /checkout_process.php
                            SELECT customers_info_number_of_logons
                              FROM ".TABLE_CUSTOMERS_INFO."
                             WHERE customers_info_id  = '".(int)$_SESSION['customer_id']."'";
  $customers_logon_query = xtc_db_query($customers_logon_query);
  $customers_logon = xtc_db_fetch_array($customers_logon_query);
  $conversion_type = ($customers_logon['customers_info_number_of_logons'] == 0) ? '1' : '2'; //1: direct sale, 2: late sale
  //write conversion_type into TABLE_ORDERS
  xtc_db_query("UPDATE ".TABLE_ORDERS."
                   SET conversion_type = '".$conversion_type."'
                 WHERE orders_id = '".$insert_id."'
               ");

  // redirect to payment service
  if ($tmp) {
    $payment_modules->payment_action();
  }
}

if (!$tmp) {
  // NEW EMAIL configuration !
  $order_totals = $order_total_modules->apply_credit();
  include ('send_order.php');

  /* easyBill */
  include(DIR_WS_MODULES.'module.easybill.php');

  // load the after_process function from the payment modules
  $payment_modules->after_process();

  // BOF - Tomcraft - 2009-10-03 - PayPal Express Modul
  // PayPal ERROR Check, Order gespeichert, Mail gesendet, Cart noch belegt
  if( isset($_SESSION['reshash']['ACK']) && strtoupper($_SESSION['reshash']['ACK'])!="SUCCESS" && strtoupper($_SESSION['reshash']['ACK'])!="SUCCESSWITHWARNING") {
    if($_SESSION['payment'] == 'paypalexpress') {
      xtc_redirect($o_paypal->EXPRESS_CANCEL_URL);
    } else {
      if(isset($_SESSION['reshash']['REDIRECTREQUIRED']) && strtoupper($_SESSION['reshash']['REDIRECTREQUIRED'])=="TRUE") {
        xtc_redirect($o_paypal->EXPRESS_CANCEL_URL);
      } else {
        xtc_redirect($o_paypal->CANCEL_URL);
      }
    }
  }
  // EOF - Tomcraft - 2009-10-03 - PayPal Express Modul
  $_SESSION['cart']->reset(true);

  // unregister session variables used during checkout
  unset ($_SESSION['sendto']);
  unset ($_SESSION['billto']);
  unset ($_SESSION['shipping']);
  // BOF - Tomcraft - 2009-10-03 - PayPal Express Modul
  //unset ($_SESSION['payment']);
  // EOF - Tomcraft - 2009-10-03 - PayPal Express Modul
  unset ($_SESSION['comments']);
  //unset ($_SESSION['last_order']);
  unset ($_SESSION['tmp_oID']);
  unset ($_SESSION['cc']);
  $last_order = $insert_id;
  //GV Code Start
  if (isset ($_SESSION['credit_covers'])) {
    unset ($_SESSION['credit_covers']);
  }
  $order_total_modules->clear_posts(); //ICW ADDED FOR CREDIT CLASS SYSTEM
  // GV Code End

// BOF - Tomcraft - 2009-11-28 - Included xs:booster
  if(@isset($_SESSION['xtb0'])) {
    define('XTB_CHECKOUT_PROCESS', __LINE__);
    require_once (DIR_FS_CATALOG.'callback/xtbooster/xtbcallback.php');
  }
// EOF - Tomcraft - 2009-11-28 - Included xs:booster

  // BOF - Tomcraft - 2009-10-03 - PayPal Express Modul (PayPal GiroPay aufrufen zum bestätigen)
  if(isset($_SESSION['reshash']['REDIRECTREQUIRED'])  && strtoupper($_SESSION['reshash']['REDIRECTREQUIRED'])=="TRUE") {
    $payment_modules->giropay_process();
  } else {
    unset($_SESSION['payment']);
    unset($_SESSION['nvpReqArray']);
    unset($_SESSION['reshash']);
  }
  // EOF - Tomcraft - 2009-10-03 - PayPal Express Modul (PayPal GiroPay aufrufen zum bestätigen)

  xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL'));
}
?>
