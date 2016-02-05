<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(order.php,v 1.32 2003/02/26); www.oscommerce.com
   (c) 2003 nextcommerce (order.php,v 1.28 2003/08/18); www.nextcommerce.org
   (c) 2006 XT-Commerce (order.php 1533 2006-08-20)

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:

   Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
   http://www.oscommerce.com/community/contributions,282
   Copyright (c) Strider | Strider@oscworks.com
   Copyright (c) Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org

   credit card encryption functions for the catalog module
   BMC 2003 for the CC CVV Module

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  if (!defined('CHECKOUT_USE_PRODUCTS_SHORT_DESCRIPTION')) {
    define('CHECKOUT_USE_PRODUCTS_SHORT_DESCRIPTION', 'true'); // 'true' 'false'  --- default: true
  }

  if(!defined('RUN_MODE_ADMIN')) {
    // include needed functions
    require_once(DIR_FS_INC . 'xtc_date_long.inc.php');
    require_once(DIR_FS_INC . 'xtc_address_format.inc.php');
    require_once(DIR_FS_INC . 'xtc_get_country_name.inc.php');
    require_once(DIR_FS_INC . 'xtc_get_zone_code.inc.php');
    require_once(DIR_FS_INC . 'xtc_get_tax_description.inc.php');
  }

  class order {
    var $info, $totals, $products, $customer, $delivery, $content_type;

    function __construct($order_id = '') {
      
      //new module support
      require_once (DIR_FS_CATALOG.'includes/classes/orderModules.class.php');
      $this->orderModules = new orderModules();
      
      //global $xtPrice;
      $this->info = array();
      $this->totals = array();
      $this->products = array();
      $this->customer = array();
      $this->delivery = array();

      if (xtc_not_null($order_id)) {
        $this->query($order_id);
      } else {
        if (!defined('RUN_MODE_ADMIN')) {
          $this->cart();
        }
      }
    }

    function query($order_id) {
      $order_id = (int)$order_id;
      $order_query = xtc_db_query("SELECT *,
                                          orders_id as order_id
                                     FROM " . TABLE_ORDERS . "
                                    WHERE orders_id = '" . $order_id . "'");
      $order = xtc_db_fetch_array($order_query);

      $totals_query = xtc_db_query("SELECT *
                                      FROM " . TABLE_ORDERS_TOTAL . "
                                     WHERE orders_id = '" . $order_id . "'
                                  ORDER BY sort_order ASC, value DESC");
      while ($totals = xtc_db_fetch_array($totals_query)) {
        $this->totals[] = array('title' => $totals['title'],
                                'text' => $totals['text'],
                                'value'=> $totals['value'],
                                'class'=> $totals['class']
                               );
      }

      $order_total_query = xtc_db_query("SELECT SUM(IF(class = 'ot_tax', value, 0)) as ot_tax,
                                                SUM(IF(class = 'ot_discount', value, 0)) as ot_discount,
                                                SUM(IF(class IN ('ot_cod_fee',
                                                                 'ot_ps_fee',
                                                                 'ot_loworderfee'
                                                                 ), value, 0)) as ot_fee,
                                                SUM(IF(class IN ('ot_coupon',
                                                                 'ot_gv',
                                                                 'ot_bonus_fee'
                                                                 ), value, 0)) as ot_gv,
                                                SUM(IF(class = 'ot_payment', value, 0)) as ot_payment,
                                                SUM(IF(class = 'ot_shipping', value, 0)) as ot_shipping_value,
                                                SUM(IF(class = 'ot_total', value, 0)) as ot_total_value,
                                                (SELECT text
                                                   FROM " . TABLE_ORDERS_TOTAL . "
                                                  WHERE orders_id = '" . $order_id . "'
                                                    AND class = 'ot_total') as ot_total_text,
                                                (SELECT title
                                                   FROM " . TABLE_ORDERS_TOTAL . "
                                                  WHERE orders_id = '" . $order_id . "'
                                                    AND class = 'ot_shipping') as ot_shipping_title
                                            FROM " . TABLE_ORDERS_TOTAL . "
                                          WHERE orders_id = '" . $order_id . "'");
      $order_total = xtc_db_fetch_array($order_total_query);

      if($order_total['ot_payment'] < 0) {
        $order_total['ot_discount'] += $order_total['ot_payment'];
      } else {
        $order_total['ot_fee'] += $order_total['ot_payment'];
      }

      $order_status_query = xtc_db_query("SELECT orders_status_name 
                                            FROM " . TABLE_ORDERS_STATUS . " 
                                           WHERE orders_status_id = '" . $order['orders_status'] . "' 
                                             AND language_id = '" . (int)$_SESSION['languages_id'] . "'");
      $order_status_array = xtc_db_fetch_array($order_status_query);
      $order_status = (!defined('RUN_MODE_ADMIN')) ? $order_status_array['orders_status_name'] : $order['orders_status'];

      // build info array dynamically
      foreach ($order as $key => $val) {
        if (strpos($key, 'customers_') === false
            && strpos($key, 'delivery_') === false
            && strpos($key, 'billing_') === false
            )
        {
          $this->info[$key] = $val;
        }
      }

      // additional info
      $this->info['status'] = $order['customers_status'];
      $this->info['status_name'] = $order['customers_status_name'];
      $this->info['status_image'] = $order['customers_status_image'];
      $this->info['status_discount'] = $order['customers_status_discount'];
      $this->info['orders_status'] = $order_status;
      $this->info['orders_status_id'] = $order['orders_status'];
      $this->info['total'] = strip_tags($order_total['ot_total_text']);
      $this->info['shipping_method'] = ((substr($order_total['ot_shipping_title'], -1) == ':') ? substr(strip_tags($order_total['ot_shipping_title']), 0, -1) : strip_tags($order_total['ot_shipping_title']));

      ## PayPal
      $this->info['pp_total'] = $order_total['ot_total_value'];
      $this->info['pp_shipping'] = $order_total['ot_shipping_value'];
      $this->info['pp_tax'] = $order_total['ot_tax'];
      $this->info['pp_disc'] = $order_total['ot_discount'];
      $this->info['pp_gs'] = $order_total['ot_gv'];
      $this->info['pp_fee'] = $order_total['ot_fee'];

      // build customer array dynamically
      foreach ($order as $key => $val) {
        if (strpos($key, 'customers_') !== false) {
          $this->customer[str_replace('customers_', '', $key)] = $val;
        }
      }
      // additional customer
      $this->customer['customers_status'] = $order['customers_status'];
      $this->customer['csID'] = $order['customers_cid'];
      $this->customer['country_iso_2'] = $order['customers_country_iso_code_2'];
      $this->customer['format_id'] = $order['customers_address_format_id'];
      $this->customer['ID'] = $order['customers_id'];
      $this->customer['cIP'] = $order['customers_ip'];

      // build delivery array dynamically
      foreach ($order as $key => $val) {
        if (strpos($key, 'delivery_') !== false) {
          $this->delivery[str_replace('delivery_', '', $key)] = $val;
        }
      }
      // additional delivery
      $this->delivery['country_iso_2'] = $order['delivery_country_iso_code_2'];
      $this->delivery['format_id'] = $order['delivery_address_format_id'];

      if(!defined('RUN_MODE_ADMIN')) {
        if (empty($this->delivery['name']) && empty($this->delivery['street_address'])) {
          $this->delivery = false;
        }
      }

      // build billing array dynamically
      foreach ($order as $key => $val) {
        if (strpos($key, 'billing_') !== false) {
          $this->billing[str_replace('billing_', '', $key)] = $val;
        }
      }
      // additional billing
      $this->billing['country_iso_2'] = $order['billing_country_iso_code_2'];
      $this->billing['format_id'] = $order['billing_address_format_id'];


      $index = 0;
      $orders_products_query = xtc_db_query("SELECT *,
                                                    products_quantity as qty,
                                                    orders_products_id as opid,
                                                    products_discount_made as discount
                                               FROM " . TABLE_ORDERS_PRODUCTS . "
                                              WHERE orders_id = '" . $order_id . "'");
      while ($orders_products = xtc_db_fetch_array($orders_products_query)) {
        // build products array dynamically
        $this->products[$index] = array();
        foreach ($orders_products as $key => $val) {
          $this->products[$index][str_replace('products_', '', $key)] = $val;
        }

        $attributes_query = xtc_db_query("SELECT *,
                                                 products_options as `option`,
                                                 products_options_values as value,
                                                 price_prefix as prefix,
                                                 options_values_price as price
                                            FROM " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . "
                                           WHERE orders_id = '" . $order_id . "'
                                             AND orders_products_id = '" . $orders_products['orders_products_id'] . "'
                                        ORDER BY orders_products_attributes_id");
        if (xtc_db_num_rows($attributes_query)) {
          $subindex = 0;
          while ($attributes = xtc_db_fetch_array($attributes_query)) {
            // build attributes array dynamically
            $this->products[$index]['attributes'][$subindex] = $attributes;
            
            //new module support
            $this->products[$index]['attributes'][$subindex] = $this->orderModules->add_attributes($this->products[$index]['attributes'][$subindex],$attributes);
            $subindex++;
          }
        }

        if(!defined('RUN_MODE_ADMIN')) {
          $this->info['tax_groups']["{$this->products[$index]['tax']}"] = '1';
        }
        $index++;
      }
    }

    function getOrderData($oID) {
      global $xtPrice, $PHP_SELF;

      require_once(DIR_FS_INC . 'xtc_get_attributes_model.inc.php');
      require_once(DIR_FS_INC . 'xtc_get_short_description.inc.php');
      require_once(DIR_FS_INC . 'xtc_get_description.inc.php');
      require_once(DIR_FS_INC . 'xtc_get_products_image.inc.php');
      require_once(DIR_FS_INC . 'xtc_image_button.inc.php');
      
      $order_lang_query = xtc_db_query("SELECT languages_id
                                    FROM ".TABLE_LANGUAGES."
                                   WHERE directory = '".$this->info['language']."'");
      $order_lang_array = xtc_db_fetch_array($order_lang_query);
      $order_lang_id = $order_lang_array['languages_id'];

      $order_query = "SELECT op.*,
                             pd.products_description,
                             pd.products_short_description
                        FROM ".TABLE_ORDERS_PRODUCTS." op
                   LEFT JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                             ON op.products_id = pd.products_id
                                AND pd.language_id = '".(int)$order_lang_id."'
                       WHERE op.orders_id='".(int)$oID."'";

      $index = 0;
      $order_data = array ();
      $order_query = xtc_db_query($order_query);
      while ($order_data_values = xtc_db_fetch_array($order_query)) {
        $attributes_query = "SELECT *
                               FROM ".TABLE_ORDERS_PRODUCTS_ATTRIBUTES."
                              WHERE orders_products_id='".$order_data_values['orders_products_id']."'
                           ORDER BY orders_products_attributes_id";
        $attributes_data = '';
        $attributes_model = '';
        $attributes_array = array();
        $attributes_query = xtc_db_query($attributes_query);
        $subindex = 0;
        $attr_model_delimiter = defined('ATTRIBUTE_MODEL_DELIMITER') ? ATTRIBUTE_MODEL_DELIMITER : '<br />';
        while ($attributes_data_values = xtc_db_fetch_array($attributes_query)) {
          $attrib_model = $attributes_data_values['attributes_model'];
          if ($attrib_model == '') {
            $attrib_model = xtc_get_attributes_model($order_data_values['products_id'], $attributes_data_values['products_options_values'],$attributes_data_values['products_options'],$order_lang_id);
          }
          $attributes_array[$subindex] = array(
            'option' => $attributes_data_values['products_options'],
            'value' => $attributes_data_values['products_options_values'],
            'option_id' => $attributes_data_values['orders_products_options_id'],
            'value_id' => $attributes_data_values['orders_products_options_values_id'],
            'model' => $attrib_model,
          );
          $attributes_data .= '<br />'.$attributes_data_values['products_options'].': '.$attributes_data_values['products_options_values'];
          $attributes_model .= $attr_model_delimiter.$attrib_model;
          $subindex++;
        }
        
        // build order_data array dynamically
        foreach ($order_data_values as $key => $val) {
          $order_data[$index][strtoupper($key)] = $val;
        }

        // additional data
        $order_data[$index]['PRODUCTS_IMAGE'] = xtc_get_products_image($order_data_values['products_id']);
        $order_data[$index]['PRODUCTS_ATTRIBUTES'] = $attributes_data;
        $order_data[$index]['PRODUCTS_ATTRIBUTES_ARRAY'] = $attributes_array;
        $order_data[$index]['PRODUCTS_ATTRIBUTES_MODEL'] = $attributes_model;
        $order_data[$index]['PRODUCTS_PRICE'] = $xtPrice->xtcFormat($order_data_values['final_price'], true);
        $order_data[$index]['PRODUCTS_SINGLE_PRICE'] = $xtPrice->xtcFormat($order_data_values['final_price']/$order_data_values['products_quantity'], true);
        $order_data[$index]['PRODUCTS_TAX'] = (($order_data_values['products_tax'] > 0.00) ? number_format($order_data_values['products_tax'], TAX_DECIMAL_PLACES) : 0);
        $order_data[$index]['PRODUCTS_QTY'] = $order_data_values['products_quantity'];
        $order_data[$index]['BUTTON_CART'] = '<a href="'.xtc_href_link(basename($PHP_SELF), 'action=add_order_product&order_id='.(int)$oID.'&id='.$order_data_values['orders_products_id'], 'SSL').'">'.xtc_image_button('small_cart.gif', IMAGE_BUTTON_IN_CART).'</a>';

        if (defined('MODULE_CHECKOUT_EXPRESS_STATUS') && MODULE_CHECKOUT_EXPRESS_STATUS == 'true') {
          $order_data[$index]['BUTTON_CART_EXPRESS'] = '<a href="'.xtc_href_link(basename($PHP_SELF), 'action=add_order_product&express=on&order_id='.(int)$oID.'&id='.$order_data_values['orders_products_id'], 'SSL').'">'.xtc_image_button('small_express.gif', IMAGE_BUTTON_IN_CART).'</a>';
        }

        $index ++;
      }

      return $order_data;
    }

    function getTotalData($oID) {
      global $xtPrice,$db;

      $index = 0;
      $total = '';
      $shipping = '';

      // get order_total data
      $order_total_query = "SELECT *
                              FROM ".TABLE_ORDERS_TOTAL."
                             WHERE orders_id='".(int)$oID."'
                          ORDER BY sort_order ASC, value DESC";

      $order_total = array ();
      $order_total_query = xtc_db_query($order_total_query);
      while ($order_total_values = xtc_db_fetch_array($order_total_query)) {

        // build order_total array dynamically
        foreach ($order_total_values as $key => $val) {
          $order_total[$index][strtoupper($key)] = $val;
        }

        if ($order_total_values['class'] == 'ot_total') {
          $total = $order_total_values['value'];
        }

        if ($order_total_values['class'] == 'ot_shipping') {
          $shipping = $order_total_values['value'];
        }

        $index ++;
      }

      return array(
          'data' => $order_total,
          'total' => $total,
          'shipping' => $shipping
        );
    }

    function parse_customers_data($customers_data, $customers_array) {
      foreach ($customers_array as $key => $val) {
        if (is_array($val)) {
          $customer[$key] = $this->parse_customers_data($customers_data, $val);
        } else {
          if (isset($customers_data[$key])) {
            $customer[$key] = $customers_data[$key];
          }
        }
      }

      return $customer;
    }


    function cart() {
      global $currencies,$xtPrice,$main;

      $this->content_type = $_SESSION['cart']->get_content_type();

      // used for customer, billing, delivery array
      $customers_standard_arr = array(
            'firstname' => '',
            'lastname' => '',
            'gender' => '',
            'company' => '',
            'street_address' => '',
            'suburb' => '',
            'city' => '',
            'postcode' => '',
            'state' => '',
            'zone_id' => '',
            'country' => array(
                'id' => '',
                'title' => '',
                'iso_code_2' => '',
                'iso_code_3' => ''
              ),
            'country_id' => '',
            'format_id' => ''
          );

      // only used for customer array
      $customers_extended_arr = array(
            'csID' => '',
            'telephone' => '',
            'payment_unallowed' => '',
            'shipping_unallowed' => '',
            'email_address' => ''
          );

      $default_select =
        "ab.entry_company as company,
         ab.entry_street_address as street_address,
         ab.entry_suburb as suburb,
         ab.entry_gender as gender,
         ab.entry_postcode as postcode,
         ab.entry_city as city,
         ab.entry_zone_id as zone_id,
         ab.entry_country_id as country_id,
         ab.entry_state as state,
         co.countries_name as title,
         co.countries_id as id,
         co.countries_iso_code_2 as iso_code_2,
         co.countries_iso_code_3 as iso_code_3,
         co.address_format_id as format_id,
         z.zone_name
        ";

      $default_join =
        "LEFT JOIN " . TABLE_ZONES . " z ON (ab.entry_zone_id = z.zone_id)
         LEFT JOIN " . TABLE_COUNTRIES . " co ON (ab.entry_country_id = co.countries_id)
        ";

      if (isset($_SESSION['customer_id'])) {
        $customer_address_query = xtc_db_query("SELECT c.payment_unallowed,
                                                       c.shipping_unallowed,
                                                       c.customers_firstname as firstname,
                                                       c.customers_cid as csID,
                                                       c.customers_gender as gender,
                                                       c.customers_lastname as lastname,
                                                       c.customers_telephone as telephone,
                                                       c.customers_email_address as email_address,
                                                       " . $default_select . "
                                                  FROM " . TABLE_CUSTOMERS . " c
                                             LEFT JOIN " . TABLE_ADDRESS_BOOK . " ab
                                                       ON (ab.customers_id = '" . $_SESSION['customer_id'] . "'
                                                           AND c.customers_default_address_id = ab.address_book_id)
                                                       " . $default_join . "
                                                 WHERE c.customers_id = '" . $_SESSION['customer_id'] . "'
                                              ");
        $customer_address = xtc_db_fetch_array($customer_address_query);

        $shipping_address_query = xtc_db_query("SELECT ab.entry_firstname as firstname,
                                                       ab.entry_lastname as lastname,
                                                       " . $default_select . "
                                                  FROM " . TABLE_ADDRESS_BOOK . " ab
                                                       " . $default_join . "
                                                 WHERE ab.customers_id = '" . $_SESSION['customer_id'] . "'
                                                   AND ab.address_book_id = '" . ((isset($_SESSION['sendto'])) ? $_SESSION['sendto'] : $_SESSION['customer_default_address_id']) . "'
                                              ");
        $shipping_address = xtc_db_fetch_array($shipping_address_query);

        $billing_address_query = xtc_db_query("SELECT ab.entry_firstname as firstname,
                                                      ab.entry_lastname as lastname,
                                                      " . $default_select . "
                                                 FROM " . TABLE_ADDRESS_BOOK . " ab
                                                      " . $default_join . "
                                                WHERE ab.customers_id = '" . $_SESSION['customer_id'] . "'
                                                  AND ab.address_book_id = '" . ((isset($_SESSION['billto'])) ? $_SESSION['billto'] : ((isset($_SESSION['sendto'])) ? $_SESSION['sendto'] : $_SESSION['customer_default_address_id'])) . "'
                                             ");

        $billing_address = xtc_db_fetch_array($billing_address_query);

        $tax_address_query = xtc_db_query("SELECT ab.entry_country_id as country_id,
                                                  ab.entry_zone_id as zone_id
                                             FROM " . TABLE_ADDRESS_BOOK . " ab
                                        LEFT JOIN " . TABLE_ZONES . " z ON (ab.entry_zone_id = z.zone_id)
                                            WHERE ab.customers_id = '" . $_SESSION['customer_id'] . "'
                                              AND ab.address_book_id = '" . (($this->content_type == 'virtual') ? ((isset($_SESSION['billto'])) ? $_SESSION['billto'] : ((isset($_SESSION['sendto'])) ? $_SESSION['sendto'] : $_SESSION['customer_default_address_id'])) : ((isset($_SESSION['sendto'])) ? $_SESSION['sendto'] : $_SESSION['customer_default_address_id'])) . "'
                                         ");
        $tax_address = xtc_db_fetch_array($tax_address_query);
      }

      // web28 - set tax country id for using order total in shopping cart
      if (!isset($tax_address['country_id']) || isset($_SESSION['country'])) {
        $tax_address['country_id'] = isset($_SESSION['country']) ?  $_SESSION['country'] : STORE_COUNTRY;
        $tax_address['zone_id'] = -1;
      }

      $this->info = array('order_status' => DEFAULT_ORDERS_STATUS_ID,
                          'currency' => $_SESSION['currency'],
                          'currency_value' => $xtPrice->currencies[$_SESSION['currency']]['value'],
                          'payment_method' => isset($_SESSION['payment']) ? $_SESSION['payment'] : '',
                          'shipping_method' => isset($_SESSION['shipping']) ? $_SESSION['shipping']['title'] : '',
                          'shipping_cost' => isset($_SESSION['shipping']) ? $_SESSION['shipping']['cost'] : '',
                          'comments' => isset($_SESSION['comments']) ? $_SESSION['comments'] : '',
                          'shipping_class' => isset($_SESSION['shipping']) ? $_SESSION['shipping']['id'] : '',
                          'payment_class' => isset($_SESSION['payment']) ? $_SESSION['payment'] : '',
                          'subtotal' => 0,
                          'tax' => 0,
                          'tax_groups' => array(),
                          );

      if (isset($_SESSION['payment']) && is_object($_SESSION['payment'])) {
        $this->info['payment_method'] = $_SESSION['payment']->title;
        $this->info['payment_class'] = $_SESSION['payment']->title;
        if (isset($_SESSION['payment']->order_status) && is_numeric($_SESSION['payment']->order_status) && $_SESSION['payment']->order_status > 0) {
          $this->info['order_status'] = $_SESSION['payment']->order_status;
        }
      }

      // build customer, billing, delivery array
      if (isset($_SESSION['customer_id'])) {
        $this->customer = $this->parse_customers_data($customer_address, array_merge($customers_standard_arr, $customers_extended_arr));
        $this->delivery = $this->parse_customers_data($shipping_address, $customers_standard_arr);
        $this->billing = $this->parse_customers_data($billing_address, $customers_standard_arr);
      } else {
        $this->customer = array_merge($customers_standard_arr, $customers_extended_arr);
        $this->delivery = $customers_standard_arr;
        $this->billing = $customers_standard_arr;
      }

      $index = 0;
      $this->tax_discount = array ();

      $products = $_SESSION['cart']->get_products(); //set in includes/classes/shopping_cart.php function get_products

      for ($i=0, $n=sizeof($products); $i<$n; $i++) {

        //attribute mapping
        $products_attributes = array();
        if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
          $products_attributes = $products[$i]['attributes']; //contains only option_id and value_id
          unset($products[$i]['attributes']); //remove from array for direct array mapping
        }
        //direct products array mapping
        $this->products[$index] = $products[$i];

        //using short description  if order description is not defined or empty
        $short_description = '';
        if (CHECKOUT_USE_PRODUCTS_SHORT_DESCRIPTION == 'true') {
          $short_description = (($products[$i]['short_description'] != '') ? $products[$i]['short_description'] : $products[$i]['description']);
        }
        $this->products[$index]['order_description'] = !empty($products[$i]['order_description']) ? nl2br($products[$i]['order_description']) : $short_description;
        $this->products[$index]['image'] = !empty($products[$i]['image']) ? $main->getProductPopupLink($products[$i]['id'],$products[$i]['image'], 'image') : '&nbsp;';
        $this->products[$index]['link'] = $main->getProductPopupLink($products[$i]['id'],$products[$i]['name'], 'details');
        $this->products[$index]['tax'] = xtc_get_tax_rate($products[$i]['tax_class_id'], $tax_address['country_id'], $tax_address['zone_id']);
        $this->products[$index]['tax_info'] = $main->getTaxInfo($this->products[$index]['tax']);
        $this->products[$index]['tax_description'] = xtc_get_tax_description($products[$i]['tax_class_id'], $tax_address['country_id'], $tax_address['zone_id']);
        $this->products[$index]['price_formated'] = $xtPrice->xtcFormat($products[$i]['price'],true); //$products[$i]['price'] is single plain price including attributes_price
        $this->products[$index]['final_price_formated'] = $xtPrice->xtcFormat($products[$i]['final_price'],true); //$products[$i]['final_price'] is quantity * plain price including attributes_price

        if (count($products_attributes) > 0) {
          $attributes_model = array();
          $check_attributes_model = false;
          if ($this->products[$index]['model'] == '') {
            $check_attributes_model = true;
  	      }
          $subindex = 0;
          reset($products_attributes);
          while (list($option, $value) = each($products_attributes)) {
            $attributes = $main->getAttributes($products[$i]['id'],$option,$value);
            if ($check_attributes_model === true && $attributes['attributes_model'] != '') {
              $attributes_model[] = $attributes['attributes_model'];
            }
            $this->products[$index]['attributes'][$subindex] = array(
                'option' => $attributes['products_options_name'],
                'value' => $attributes['products_options_values_name'],
                'option_id' => $option,
                'value_id' => $value,
                'weight' => $attributes['options_values_weight'],
                'prefix' => $attributes['price_prefix'],
                'price' => $attributes['options_values_price'],
                'price_formated' => $xtPrice->xtcFormat($attributes['options_values_price'], true)
              );

            // extend attributes array dynamically
            foreach ($attributes as $key => $val) {
              $this->products[$index]['attributes'][$subindex][str_replace('attributes_', '', $key)] = $val;
            }

            //new module support
            $this->products[$index]['attributes'][$subindex] = $this->orderModules->cart_attributes($this->products[$index]['attributes'][$subindex],$attributes,$products[$i]['id'],$value);
  
            $subindex++;
          }
          $this->products[$i]['attributes'] = array_merge($this->products[$i]['attributes']); //index correction needed in "for loops"
          if($check_attributes_model === true && count($attributes_model) > 0) {
          	$attr_model_delimiter = defined('ATTRIBUTE_MODEL_DELIMITER') ? ATTRIBUTE_MODEL_DELIMITER : '<br />';
          	$this->products[$index]['model'] = implode($attr_model_delimiter, $attributes_model);
          }
        }

        $shown_price = $this->products[$index]['final_price'];
        $this->info['subtotal'] += $shown_price;
        if ($_SESSION['customers_status']['customers_status_ot_discount_flag'] == '1'){
          $shown_price_tax = $shown_price-($shown_price/100 * $_SESSION['customers_status']['customers_status_ot_discount']);
        }

        $products_tax = $this->products[$index]['tax'];
        $products_tax_description = $this->products[$index]['tax_description'];
        if ($_SESSION['customers_status']['customers_status_show_price_tax'] == '1') {
          $tax_index = TAX_ADD_TAX.$products_tax_description;
          if (!isset($this->info['tax_groups'][$tax_index])) {
            $this->info['tax_groups'][$tax_index] = 0;
          }
          if ($_SESSION['customers_status']['customers_status_ot_discount_flag'] == '1') {
            $this->info['tax'] += $shown_price_tax - ($shown_price_tax / (($products_tax < 10) ? "1.0" . str_replace('.', '', $products_tax) : "1." . str_replace('.', '', $products_tax)));
            $this->info['tax_groups'][$tax_index] += (($shown_price_tax /(100+$products_tax)) * $products_tax);
          } else {
            $this->info['tax'] += $shown_price - ($shown_price / (($products_tax < 10) ? "1.0" . str_replace('.', '', $products_tax) : "1." . str_replace('.', '', $products_tax)));
            $this->info['tax_groups'][$tax_index] += (($shown_price /(100+$products_tax)) * $products_tax);
          }
        } else {
          $tax_index = TAX_NO_TAX.$products_tax_description;
          if (!isset($this->info['tax_groups'][$tax_index])) {
            $this->info['tax_groups'][$tax_index] = 0;
          }
          if ($_SESSION['customers_status']['customers_status_ot_discount_flag'] == '1') {
            $this->tax_discount[$products[$i]['tax_class_id']]+=($shown_price_tax/100) * $products_tax;
            $this->info['tax_groups'][$tax_index] += ($shown_price_tax/100) * ($products_tax);
          } else {
            $this->info['tax'] += ($shown_price/100) * ($products_tax);
            $this->info['tax_groups'][$tax_index] += ($shown_price/100) * ($products_tax);
          }
        }
        $index++;
      }

      foreach ($this->tax_discount as $value) {
        $this->info['tax'] += round($value, $xtPrice->get_decimal_places(''));
      }

      $this->info['total'] = $this->info['subtotal'] + $xtPrice->xtcFormat($this->info['shipping_cost'], false,0,true);
      if ($_SESSION['customers_status']['customers_status_ot_discount_flag'] == '1') {
        $this->info['total'] -= ($this->info['subtotal'] /100 * $_SESSION['customers_status']['customers_status_ot_discount']);
      }
    }
  }
?>