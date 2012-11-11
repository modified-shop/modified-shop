<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2001 - 2003 osCommerce

  Released under the GNU General Public License

************************************************************************

                    All rights reserved.

  iclear design pattern
  Copyright (C) 2007 - 2009 iclear GmbH

                    All rights reserved.

  This program is free software licensed under the GNU General Public License (GPL).

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
  USA

*************************************************************************/
  if(!class_exists('IclearWrapperXTC')) {
    require dirname(__FILE__) . '/xtc304.base.php';
  }

  define('IC_WRAPPER_ID', 'xtc304sp2' );

  class IclearWrapper extends IclearWrapperXTC {
    
// class constructor
    function IclearWrapper(&$icCore) {
      $this->icVersion = '$Id$';
      global $icCore;
      parent::IclearWrapperXTC($icCore);
    }
    
    function storeOrder() {
      global $cart, $currencies, $customer_id, $language, $languages_id, $order, $main;
      $rc = false;
      
      if($basket =& $this->icCore->getBasket()) {
        global $SESS_LIFE;
        
        // $SESS_LIFE used in session modules / functions 2 determine session lifetime (time() + $SESS_LIFE)
        $SESS_LIFE = 1440;
        $icLang = $this->icCore->getLanguage();
        xtc_session_name(IC_SESSION_NAME);
        xtc_session_id($basket->sessionID());
        xtc_session_start();

        // recreate session data from basket 2 prevent logout after redirect iclear -> shopURL!
        $_SESSION = $basket->session();
        
        // set globals - needed 4 OSC mods
        $customer_id = $basket->customerID();
        $order = $basket->getOrder();

        switch($basket->language()) {
          case 'EN':
            $language = 'english';
            break;
            
          case 'DE':
          default:
            $language = 'german';
            break;
        }
        
        global $xtPrice;
        $xtPrice = new xtcPrice($_SESSION['currency'], $_SESSION['customers_status']['customers_status_id']);
        // load needed consts 4 email
        require_once $this->icCore->getPath() . 'lang/' . $language . '/' . $language . '.php';
        
        // adaption - brandtd: prepare environment 4 basket / session deletion
        $cart =& $_SESSION['cart'];
// XTC checkout_process.php START
// FIXME: xajax checkout check?
//        if ($_POST['checkout_xajax'] == 1) {
//          $_SESSION['comments'] = strip_tags($_POST['comments']);
//        }
        
        // load selected payment module
        require (DIR_WS_CLASSES.'payment.php');
        if (isset ($_SESSION['credit_covers']))
          $_SESSION['payment'] = ''; //ICW added for CREDIT CLASS
        //$payment_modules = new payment($_SESSION['payment']);
        
        // load the selected shipping module
        require (DIR_WS_CLASSES.'shipping.php');
        $shipping_modules = new shipping($_SESSION['shipping']);
        
        require (DIR_WS_CLASSES.'order.php');
        $order = new order();
        
        require (DIR_WS_CLASSES.'order_total.php');
        $order_total_modules = new order_total();
        
        $order_totals = $order_total_modules->process();
        
        // set correct order state if it's an waiting order
        if($basket->status() == IC_ORDER_WAIT) {
          $orderStatus = MODULE_PAYMENT_ICLEAR_STATUS_WAIT_ID;
        } else {
          $orderStatus = MODULE_PAYMENT_ICLEAR_ORDER_STATUS_ID;
        }
        
        // check if tmp order id exists
        if (isset ($_SESSION['tmp_oID']) && is_int($_SESSION['tmp_oID'])) {
          $tmp = false;
          $insert_id = $_SESSION['tmp_oID'];
        } else {
        
          if ($_SESSION['customers_status']['customers_status_ot_discount_flag'] == 1) {
            $discount = $_SESSION['customers_status']['customers_status_ot_discount'];
          } else {
            $discount = '0.00';
          }
        
          if ($_SERVER["HTTP_X_FORWARDED_FOR"]) {
            $customers_ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
          } else {
            $customers_ip = $_SERVER["REMOTE_ADDR"];
          }
          if ($_SESSION['credit_covers'] != '1') {
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
                                     'payment_method' => $order->info['payment_method'],
                                     'payment_class' => $order->info['payment_class'],
                                     'shipping_method' => $order->info['shipping_method'],
                                     'shipping_class' => $order->info['shipping_class'],
                                     'cc_type' => $order->info['cc_type'],
                                     'cc_owner' => $order->info['cc_owner'],
                                     'cc_number' => $order->info['cc_number'],
                                     'cc_expires' => $order->info['cc_expires'],
                                     'cc_start' => $order->info['cc_start'],
                                     'cc_cvv' => $order->info['cc_cvv'],
                                     'cc_issue' => $order->info['cc_issue'],
                                     'date_purchased' => 'now()',
                                     'orders_status' => $orderStatus,
                                     'currency' => $order->info['currency'],
                                     'currency_value' => $order->info['currency_value'],
                                     'customers_ip' => $customers_ip,
                                     'language' => $_SESSION['language'],
                                     'comments' => $order->info['comments']);
          } else {
            // free gift , no paymentaddress
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
                                     'payment_method' => $order->info['payment_method'],
                                     'payment_class' => $order->info['payment_class'],
                                     'shipping_method' => $order->info['shipping_method'],
                                     'shipping_class' => $order->info['shipping_class'],
                                     'cc_type' => $order->info['cc_type'],
                                     'cc_owner' => $order->info['cc_owner'],
                                     'cc_number' => $order->info['cc_number'],
                                     'cc_expires' => $order->info['cc_expires'],
                                     'date_purchased' => 'now()',
                                     'orders_status' => $orderStatus,
                                     'currency' => $order->info['currency'],
                                     'currency_value' => $order->info['currency_value'],
                                     'customers_ip' => $customers_ip,
                                     'comments' => $order->info['comments']);
          }
        
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
                                   'orders_status_id' => $orderStatus,
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
                $stock_query_raw = "SELECT products_quantity, pad.products_attributes_filename
                                                  FROM ".TABLE_PRODUCTS." p
                                                  LEFT JOIN ".TABLE_PRODUCTS_ATTRIBUTES." pa
                                                   ON p.products_id=pa.products_id
                                                  LEFT JOIN ".TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD." pad
                                                   ON pa.products_attributes_id=pad.products_attributes_id
                                                  WHERE p.products_id = '".xtc_get_prid($order->products[$i]['id'])."'";
                // Will work with only one option for downloadable products
                // otherwise, we have to build the query dynamically with a loop
                $products_attributes = $order->products[$i]['attributes'];
                if (is_array($products_attributes)) {
                  $stock_query_raw .= " AND pa.options_id = '".$products_attributes[0]['option_id']."' AND pa.options_values_id = '".$products_attributes[0]['value_id']."'";
                }
                $stock_query = xtc_db_query($stock_query_raw);
              } else {
                $stock_query = xtc_db_query("select products_quantity from ".TABLE_PRODUCTS." where products_id = '".xtc_get_prid($order->products[$i]['id'])."'");
              }
              if (xtc_db_num_rows($stock_query) > 0) {
                $stock_values = xtc_db_fetch_array($stock_query);
                // do not decrement quantities if products_attributes_filename exists
                if ((DOWNLOAD_ENABLED != 'true') || (!$stock_values['products_attributes_filename'])) {
                  $stock_left = $stock_values['products_quantity'] - $order->products[$i]['qty'];
                } else {
                  $stock_left = $stock_values['products_quantity'];
                }
          
                xtc_db_query("update ".TABLE_PRODUCTS." set products_quantity = '".$stock_left."' where products_id = '".xtc_get_prid($order->products[$i]['id'])."'");
                if (($stock_left < 1) && (STOCK_ALLOW_CHECKOUT == 'false')) {
                  xtc_db_query("update ".TABLE_PRODUCTS." set products_status = '0' where products_id = '".xtc_get_prid($order->products[$i]['id'])."'");
                }
              }
            }
          
            // Update products_ordered (for bestsellers list)
            xtc_db_query("update ".TABLE_PRODUCTS." set products_ordered = products_ordered + ".sprintf('%d', $order->products[$i]['qty'])." where products_id = '".xtc_get_prid($order->products[$i]['id'])."'");
          
            $sql_data_array = array ('orders_id' => $insert_id, 'products_id' => xtc_get_prid($order->products[$i]['id']), 'products_model' => $order->products[$i]['model'], 'products_name' => $order->products[$i]['name'],'products_shipping_time'=>$order->products[$i]['shipping_time'], 'products_price' => $order->products[$i]['price'], 'final_price' => $order->products[$i]['final_price'], 'products_tax' => $order->products[$i]['tax'], 'products_discount_made' => $order->products[$i]['discount_allowed'], 'products_quantity' => $order->products[$i]['qty'], 'allow_tax' => $_SESSION['customers_status']['customers_status_show_price_tax']);
          
            xtc_db_perform(TABLE_ORDERS_PRODUCTS, $sql_data_array);
            $order_products_id = xtc_db_insert_id();
          
            // Aenderung Specials Quantity Anfang
            $specials_result = xtc_db_query("SELECT products_id, specials_quantity from ".TABLE_SPECIALS." WHERE products_id = '".xtc_get_prid($order->products[$i]['id'])."' ");
            if (xtc_db_num_rows($specials_result)) {
              $spq = xtc_db_fetch_array($specials_result);
          
              $new_sp_quantity = ($spq['specials_quantity'] - $order->products[$i]['qty']);
          
              if ($new_sp_quantity >= 1) {
                xtc_db_query("update ".TABLE_SPECIALS." set specials_quantity = '".$new_sp_quantity."' where products_id = '".xtc_get_prid($order->products[$i]['id'])."' ");
              } else {
                xtc_db_query("update ".TABLE_SPECIALS." set status = '0', specials_quantity = '".$new_sp_quantity."' where products_id = '".xtc_get_prid($order->products[$i]['id'])."' ");
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
                if (DOWNLOAD_ENABLED == 'true') {
                  $attributes_query = "select popt.products_options_name,
                                                         poval.products_options_values_name,
                                                         pa.options_values_price,
                                                         pa.price_prefix,
                                                         pad.products_attributes_maxdays,
                                                         pad.products_attributes_maxcount,
                                                         pad.products_attributes_filename
                                                         from ".TABLE_PRODUCTS_OPTIONS." popt, ".TABLE_PRODUCTS_OPTIONS_VALUES." poval, ".TABLE_PRODUCTS_ATTRIBUTES." pa
                                                         left join ".TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD." pad
                                                          on pa.products_attributes_id=pad.products_attributes_id
                                                         where pa.products_id = '".$order->products[$i]['id']."'
                                                          and pa.options_id = '".$order->products[$i]['attributes'][$j]['option_id']."'
                                                          and pa.options_id = popt.products_options_id
                                                          and pa.options_values_id = '".$order->products[$i]['attributes'][$j]['value_id']."'
                                                          and pa.options_values_id = poval.products_options_values_id
                                                          and popt.language_id = '".$_SESSION['languages_id']."'
                                                          and poval.language_id = '".$_SESSION['languages_id']."'";
                  $attributes = xtc_db_query($attributes_query);
                } else {
                  $attributes = xtc_db_query("select popt.products_options_name,
                                                                       poval.products_options_values_name,
                                                                       pa.options_values_price,
                                                                       pa.price_prefix
                                                                       from ".TABLE_PRODUCTS_OPTIONS." popt, ".TABLE_PRODUCTS_OPTIONS_VALUES." poval, ".TABLE_PRODUCTS_ATTRIBUTES." pa
                                                                       where pa.products_id = '".$order->products[$i]['id']."'
                                                                       and pa.options_id = '".$order->products[$i]['attributes'][$j]['option_id']."'
                                                                       and pa.options_id = popt.products_options_id
                                                                       and pa.options_values_id = '".$order->products[$i]['attributes'][$j]['value_id']."'
                                                                       and pa.options_values_id = poval.products_options_values_id
                                                                       and popt.language_id = '".$_SESSION['languages_id']."'
                                                                       and poval.language_id = '".$_SESSION['languages_id']."'");
                }
                // update attribute stock
                xtc_db_query("UPDATE ".TABLE_PRODUCTS_ATTRIBUTES." set
                                                     attributes_stock=attributes_stock - '".$order->products[$i]['qty']."'
                                                     where
                                                     products_id='".$order->products[$i]['id']."'
                                                     and options_values_id='".$order->products[$i]['attributes'][$j]['value_id']."'
                                                     and options_id='".$order->products[$i]['attributes'][$j]['option_id']."'
                                                     ");
          
                $attributes_values = xtc_db_fetch_array($attributes);
          
                $sql_data_array = array ('orders_id' => $insert_id, 'orders_products_id' => $order_products_id, 'products_options' => $attributes_values['products_options_name'], 'products_options_values' => $attributes_values['products_options_values_name'], 'options_values_price' => $attributes_values['options_values_price'], 'price_prefix' => $attributes_values['price_prefix']);
                xtc_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $sql_data_array);
          
                if ((DOWNLOAD_ENABLED == 'true') && isset ($attributes_values['products_attributes_filename']) && xtc_not_null($attributes_values['products_attributes_filename'])) {
                  $sql_data_array = array ('orders_id' => $insert_id, 'orders_products_id' => $order_products_id, 'orders_products_filename' => $attributes_values['products_attributes_filename'], 'download_maxdays' => $attributes_values['products_attributes_maxdays'], 'download_count' => $attributes_values['products_attributes_maxcount']);
                  xtc_db_perform(TABLE_ORDERS_PRODUCTS_DOWNLOAD, $sql_data_array);
                }
              }
            }
            //------insert customer choosen option eof ----
            $total_weight += ($order->products[$i]['qty'] * $order->products[$i]['weight']);
            $total_tax += xtc_calculate_tax($total_products_price, $products_tax) * $order->products[$i]['qty'];
            $total_cost += $total_products_price;
          
          }
          
          if (isset ($_SESSION['tracking']['refID'])) {
          
            xtc_db_query("update ".TABLE_ORDERS." set
                                             refferers_id = '".$_SESSION['tracking']['refID']."'
                                             where orders_id = '".$insert_id."'");
          
            // check if late or direct sale                         
            $customers_logon_query = "SELECT customers_info_number_of_logons
                                              FROM ".TABLE_CUSTOMERS_INFO." 
                                              WHERE customers_info_id  = '".$_SESSION['customer_id']."'";
            $customers_logon_query = xtc_db_query($customers_logon_query);
            $customers_logon = xtc_db_fetch_array($customers_logon_query);
          
            if ($customers_logon['customers_info_number_of_logons'] == 0) {
              // direct sale
              xtc_db_query("update ".TABLE_ORDERS." set
                                               conversion_type = '1'
                                               where orders_id = '".$insert_id."'");
            } else {
              // late sale
          
              xtc_db_query("update ".TABLE_ORDERS." set
                                               conversion_type = '2'
                                               where orders_id = '".$insert_id."'");
            }
          
          } else {
          
            $customers_query = xtc_db_query("SELECT refferers_id as ref FROM ".TABLE_CUSTOMERS." WHERE customers_id='".$_SESSION['customer_id']."'");
            $customers_data = xtc_db_fetch_array($customers_query);
            if (xtc_db_num_rows($customers_query)) {
          
              xtc_db_query("update ".TABLE_ORDERS." set
                                               refferers_id = '".$customers_data['ref']."'
                                               where orders_id = '".$insert_id."'");
              // check if late or direct sale                         
              $customers_logon_query = "SELECT customers_info_number_of_logons
                                                FROM ".TABLE_CUSTOMERS_INFO." 
                                                WHERE customers_info_id  = '".$_SESSION['customer_id']."'";
              $customers_logon_query = xtc_db_query($customers_logon_query);
              $customers_logon = xtc_db_fetch_array($customers_logon_query);
          
              if ($customers_logon['customers_info_number_of_logons'] == 0) {
                // direct sale
                xtc_db_query("update ".TABLE_ORDERS." set
                                                 conversion_type = '1'
                                                 where orders_id = '".$insert_id."'");
              } else {
                // late sale
          
                xtc_db_query("update ".TABLE_ORDERS." set
                                                 conversion_type = '2'
                                                 where orders_id = '".$insert_id."'");
              }
            }
          }
        }
        
        // NEW EMAIL configuration !
        $order_totals = $order_total_modules->apply_credit();
        
        $_SESSION['cart']->reset(true);
      
        // unregister session variables used during checkout
        unset ($_SESSION['sendto']);
        unset ($_SESSION['billto']);
        unset ($_SESSION['shipping']);
        unset ($_SESSION['payment']);
        unset ($_SESSION['comments']);
        unset ($_SESSION['last_order']);
        unset ($_SESSION['tmp_oID']);
        unset ($_SESSION['cc']);
        $last_order = $insert_id;
        //GV Code Start
        if (isset ($_SESSION['credit_covers']))
          unset ($_SESSION['credit_covers']);
        $order_total_modules->clear_posts(); //ICW ADDED FOR CREDIT CLASS SYSTEM
        // GV Code End
        
        if($rc = $basket->orderID($insert_id)) {
          if($basket->status() == IC_ORDER_WAIT) {
            $this->maskOrder();
          }
        }
        global $smarty;
        $smarty = new Smarty();
        include ('send_order.php');
      }
      return $rc;
    }
  }
?>