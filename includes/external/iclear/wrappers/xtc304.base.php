<?php
/*
 $Id$

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

/**
 *
 * @package IclearWrapperXTC
 * @author dis
 *
 */
class IclearWrapperXTC extends IclearWrapperBase {

  /**
   *
   * @var IclearLanguage
   * @access private
   */
  var $lang;
  /**
   * module code - used internally by shop system
   * @var string
   * @access public
   */
  var $code;

  /**
   * Title of the module in shop system
   * @var sting
   * @access public
   */
  var $title;
  /**
   * Description of the module in shop system
   * @var string
   * @access public
   */
  var $description;
  /**
   * Flag 2 decide if module is enabled
   * @var boolean
   * @access public
   */
  var $enabled;
  /**
   * Order object provided by shop system in context of order process
   *
   * @var Order
   * @access private
   */
  var $order;
  /**
   * iclear statusID of given order
   * @var int
   * @access private
   */
  var $orderStatusID;
  /**
   * Internal product counter 4 iterating over order positions
   * @var int
   * @access private
   */
  var $currentProductID = 0;
  /**
   * Internal special items counter
   * @var int
   * @access private
   */
  var $specialItemID = 0;
  /**
   * Flag to detect if prices in order net or gros
   * @var boolean
   * @access private
   */
  var $showPriceTax;
  /**
   * Number of position in ot_payment module
   * @var int
   * @access private
   */
  var $otPaymentItemID = 0;
  /**
   * entries of this array are masked in shop systems order
   * if a basket is assigned to iclear's wait state
   * @var array
   * @access private
   */
  var $addressKeysMask = array(
      'name' => '',
      'company' => '',
      'street_address' => 'info',
      'suburb' => '',
      'city' => '',
      'postcode' => '',
      'state' => '',
  );
  /**
   * entries of this array are unmasked in shop systems order
   * if a waiting basket becomes OK
   * @var array
   * @access private
   */
  var $addressKeysUnmask = array(
      'company',
      'street_address',
      'suburb',
      'city',
      'postcode',
      'state',
  );

  /**
   * iclear executed in an iframe
   * @var boolean
   */
  var $bIframe;
  
  var $module_name = "";
  var $module_name_lang = "";

  /**
   * Class constructor
   * @param IclearCore $icCore
   */
  function IclearWrapperXTC(&$icCore) {
    $this->icVersion = '$Id$';
    global $icCore;
    parent::IclearWrapperBase($icCore);
    if(!defined('IC_WRAPPER_ID')) {
      die("Wrapper base class XTC not instantiated!\n");
    }

    $this->id = IC_WRAPPER_ID;

    $this->code = 'iclear';
    $lang =& $icCore->getLanguage();
    $this->title = preg_match('!/admin/!', $_SERVER['SCRIPT_FILENAME']) ? $lang->getParam('MODULE_TITLE_ADMIN' . $this->module_name_lang)
    : $lang->getParam('MODULE_TITLE' . $this->module_name_lang);
    $this->description =  $lang->getParam('DESCRIPTION' . $this->module_name_lang);
    $this->sort_order =  $lang->getParam('SORT_ORDER' . $this->module_name_lang);

    $this->lang =& $lang;

    $this->enabled = $this->enabled();
    $this->update_status();

    $this->showPriceTax = (isset($_SESSION['customers_status']['customers_status_show_price_tax'])  && $_SESSION['customers_status']['customers_status_show_price_tax']) ? true : false;

    $this->bIframe = $this->iframe();
  }

  /**
   * Queries DB
   *
   * @param $sql
   * @return mysql result
   */
  function dbQuery($sql) {
    return xtc_db_query($sql);
  }
  /**
   * Fetches one record from DB
   * @param string $sql
   * @return array
   */
  function dbFetchRecord($sql) {
    $rec = false;
    if($qry = $this->dbQuery($sql)) {
      $rec = xtc_db_fetch_array($qry);
    }
    return $rec;
  }

  function dbLastInsertID() {
    return xtc_db_insert_id();
  }

  function title() {
      return preg_match('!/admin/!', $_SERVER['SCRIPT_FILENAME']) ? $this->lang->getParam('MODULE_TITLE_ADMIN' . $this->module_name_lang) : $this->lang->getParam('MODULE_TITLE' . $this->module_name_lang);
  }

  function enabled() {
    return (defined('MODULE_PAYMENT_ICLEAR_' . $this->module_name . 'STATUS') && constant( 'MODULE_PAYMENT_ICLEAR_' . $this->module_name . 'STATUS' ) == 'True') ? true : false;
  }

  function sortOrder() {
    return defined('MODULE_PAYMENT_ICLEAR_' . $this->module_name . 'SORT_ORDER') ? constant ( 'MODULE_PAYMENT_ICLEAR_' . $this->module_name . 'SORT_ORDER' ) : ''; //DokuMan - 2011-08-29 - set SORT_ORDER to blank
  }

  function axCheckout() {
    return defined('CHECKOUT_AJAX_STAT') && CHECKOUT_AJAX_STAT == 'true' ? true : false;
  }

  function iframe() {
    return (defined('MODULE_PAYMENT_ICLEAR_' . $this->module_name . 'IFRAME') && constant ( 'MODULE_PAYMENT_ICLEAR_' . $this->module_name . 'IFRAME' ) == 'True') ? true : false;
  }
  
  function correctValue() {
    $this->enabled = $this->enabled();
    $this->bIframe = $this->iframe();
    $this->update_status();
    
  }

  function getOrder() {
    global $order;
    return $order;
  }

  function currency() {
    $rv = '';
    if($order =& $this->getOrder()) {
      $rv = $order->info['currency'];
    }
    return $rv;
  }

  function orderItemCount() {
    $rv = 0;

    if($order =& $this->getOrder()) {
      $rv = sizeof($order->products);
    }
    return $rv;
  }

  function itemPrice($price = 0, $multiplier = 1) {
    $rv = 0;
    if(isset($price) && $price) {
      $neg = false;
      $rv = xtc_round($price, 4);
      if($neg) {
        $rv *= -1;
      }
    }
    return $rv;
  }

  function nextOrderItem() {
    global $xtPrice;
    $item = false;
    if(!function_exists('xtc_round')) {
      require_once DIR_FS_INC . 'xtc_round.inc.php';
    }

    if(version_compare(PHP_VERSION, '5.0.0', '<')) {
      static $id;
      if(!isset($id)) {
        $id = 0;
      }
      $this->currentProductID = $id;
    } else {
      $id =& $this->currentProductID;
    }
    if($order =& $this->getOrder()) {
      if($id < sizeof($order->products)) {
        $product = $order->products[$id];
        $item[IC_SOAP_ITEM_NO] = $this->encodeUTF8($product['model']);
        $item[IC_SOAP_ITEM_TITLE] = $this->encodeUTF8($product['qty'] . ' x ' . $product['name']);
        if(isset($product['attributes']) && is_array($product['attributes'])) {
          foreach($product['attributes'] AS $rec) {
            $item[IC_SOAP_ITEM_TITLE] .= $this->encodeUTF8(' | ' . $rec['option'] . ': ' . $rec['value']);
          }
        }

        // bundling items to single article @ iclear
        $item[IC_SOAP_ITEM_QTY] = 1;

        // store original state of xtPrice tax display setting
        $showPriceTaxTemp = $xtPrice->cStatus['customers_status_show_price_tax'];
        // decide howto calculate gros price - XTC differs between B2B and normal customer tax calculation!
        // b2b case
        // disable tax setting 2 obtain net price
        $xtPrice->cStatus['customers_status_show_price_tax'] = 0;
        $item[IC_SOAP_ITEM_PRICE_NET] = $this->itemPrice( ($xtPrice->xtcGetPrice($product['id'], false, $product['qty'], $product['tax_class_id'], '' )
        +  $xtPrice->xtcFormat($_SESSION['cart']->attributes_price($product['id']),false)) * $product['qty']);
        // enable tax setting 2 obtain net price
        $xtPrice->cStatus['customers_status_show_price_tax'] = 1;
        $item[IC_SOAP_ITEM_PRICE_GROS] = $this->itemPrice( ($xtPrice->xtcGetPrice($product['id'], false, $product['qty'], $product['tax_class_id'], '')
        +  $xtPrice->xtcFormat($_SESSION['cart']->attributes_price($product['id']),false)) * $product['qty'] );

        // restore original tax setting
        $xtPrice->cStatus['customers_status_show_price_tax'] = $showPriceTaxTemp;

        $item[IC_SOAP_ITEM_VAT_RATE] = xtc_round($product['tax'], 1);
        if(!$item[IC_SOAP_ITEM_VAT_RATE]) {
          $item[IC_SOAP_ITEM_VAT_RATE] = '0.0';
        }
      }
      $id++;
    }
    return $item;
  }

  function specialItems() {
    $items = false;

    $order =& $this->getOrder();
    // get shipping item
    if( $order && isset($_SESSION['shipping']['title'])) {
      $item[IC_SOAP_ITEM_NO] = $this->specialItemID; // in osc exists no zero itemID
      $item[IC_SOAP_ITEM_TITLE] = $this->encodeUTF8($_SESSION['shipping']['title']);

      // get shipping tax
      $mod = substr($_SESSION['shipping']['id'], 0, strpos($_SESSION['shipping']['id'], '_'));
      $shippingTax = xtc_get_tax_rate($GLOBALS[$mod]->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);

      $item[IC_SOAP_ITEM_PRICE_NET] = $this->itemPrice($_SESSION['shipping']['cost']);
      $item[IC_SOAP_ITEM_PRICE_GROS] =  $this->itemPrice(xtc_add_tax($_SESSION['shipping']['cost'], $shippingTax));
      $item[IC_SOAP_ITEM_QTY] = 1;
      $item[IC_SOAP_ITEM_VAT_RATE] = $shippingTax ? $shippingTax : 0.0; //20101203 CA fixed always 0 vat rate
      $items[] = $item;
    }


    // check if low orderfee is given
    if( isset($GLOBALS['ot_loworderfee']) && sizeof($GLOBALS['ot_loworderfee']->output) ) {
      $item[IC_SOAP_ITEM_NO] = --$this->specialItemID;
      $item[IC_SOAP_ITEM_TITLE] = $this->encodeUTF8($GLOBALS['ot_loworderfee']->title);
      $sql = 'SELECT configuration_value AS loworderfee FROM ' . TABLE_CONFIGURATION . ' WHERE configuration_key="MODULE_ORDER_TOTAL_LOWORDERFEE_FEE"';
      $lofTax = xtc_get_tax_rate(MODULE_ORDER_TOTAL_LOWORDERFEE_TAX_CLASS, $order->delivery['country']['id'], $order->delivery['zone_id']);

      $item[IC_SOAP_ITEM_PRICE_NET] = $this->itemPrice(MODULE_ORDER_TOTAL_LOWORDERFEE_FEE);
      $item[IC_SOAP_ITEM_PRICE_GROS] = $this->itemPrice(xtc_add_tax(MODULE_ORDER_TOTAL_LOWORDERFEE_FEE, $lofTax));
      $item[IC_SOAP_ITEM_QTY] = 1;
      $item[IC_SOAP_ITEM_VAT_RATE] = $lofTax;

      $items[] = $item;
    }
    
    // 20110728 CA added support for new coupons (ot_grad_order_total_discount)
    $class = 'ot_grad_order_total_discount';
    if(isset($GLOBALS[$class]) && $GLOBALS[$class]->enabled) {
      $item = array();
      for ($x = 0, $y = sizeof($GLOBALS[$class]->output); $x < $y; $x++) {
        $item[IC_SOAP_ITEM_NO] =  --$this->specialItemID;
        $item[IC_SOAP_ITEM_TITLE] = $this->encodeUTF8($GLOBALS[$class]->title . ' ' .  ( $x + 1)) ;
        $item[IC_SOAP_ITEM_QTY] = 1;
        $item[IC_SOAP_ITEM_PRICE_GROS] = $item[IC_SOAP_ITEM_PRICE_NET] =  $this->itemPrice(-1 * $GLOBALS[$class]->output[$x]['value']);
        $item[IC_SOAP_ITEM_VAT_RATE] = '0.0';
        $items[] = $item;
      }
    }
    
    // 20110309 CA added support for new coupons (ot_coupon)
    $class = 'ot_coupon';
    if(isset($GLOBALS[$class]) && $GLOBALS[$class]->enabled) {
      $item = array();
      for ($x = 0, $y = sizeof($GLOBALS[$class]->output); $x < $y; $x++) {
        $item[IC_SOAP_ITEM_NO] =  --$this->specialItemID;
        $item[IC_SOAP_ITEM_TITLE] = $this->encodeUTF8($GLOBALS[$class]->title . ' ' .  ( $x + 1)) ;
        $item[IC_SOAP_ITEM_QTY] = 1;
        $item[IC_SOAP_ITEM_PRICE_GROS] = $item[IC_SOAP_ITEM_PRICE_NET] =  $this->itemPrice(-1 * $GLOBALS[$class]->output[$x]['value']);
        $item[IC_SOAP_ITEM_VAT_RATE] = '0.0';
        $items[] = $item;
      }
    }

    // check 4 coupons
    $class = 'ot_gv';
    if(isset($GLOBALS[$class]) && $GLOBALS[$class]->enabled) {
      $item = array();
      for ($x = 0, $y = sizeof($GLOBALS[$class]->output); $x < $y; $x++) {
        $item[IC_SOAP_ITEM_NO] =  --$this->specialItemID;
        $item[IC_SOAP_ITEM_TITLE] = $this->encodeUTF8($GLOBALS[$class]->title . ' ' .  ( $x + 1)) ;
        $item[IC_SOAP_ITEM_QTY] = 1;
        $item[IC_SOAP_ITEM_PRICE_GROS] = $item[IC_SOAP_ITEM_PRICE_NET] =  $this->itemPrice(-1 * $GLOBALS[$class]->output[$x]['value']);
        $item[IC_SOAP_ITEM_VAT_RATE] = '0.0';
        $items[] = $item;
      }
    }

    // check if order discount is given
    $class = 'ot_discount';
    if($order && isset($GLOBALS[$class]) && $GLOBALS[$class]->enabled && sizeof($GLOBALS[$class]->output)) {
      $otDiscount =& $GLOBALS[$class];
      $item = array();
      $item[IC_SOAP_ITEM_NO] =  --$this->specialItemID;
      $item[IC_SOAP_ITEM_TITLE] = $this->encodeUTF8($otDiscount->title);
      $item[IC_SOAP_ITEM_PRICE_GROS] = $item[IC_SOAP_ITEM_PRICE_NET] =   $this->itemPrice($order->info['subtotal'] / 100 * $_SESSION['customers_status']['customers_status_ot_discount']*-1);
      $item[IC_SOAP_ITEM_QTY] = 1;
      $item[IC_SOAP_ITEM_VAT_RATE] = '0.0';
      $items[] = $item;
    }

    // check if payment discount is given
    $class = 'ot_payment';
    if(isset($GLOBALS[$class]) && $GLOBALS[$class]->enabled && sizeof($GLOBALS[$class]->output)) {
      $otPayment =& $GLOBALS[$class];
      $item = array();
      $item[IC_SOAP_ITEM_NO] =  --$this->specialItemID;
      $item[IC_SOAP_ITEM_TITLE] = $this->encodeUTF8($otPayment->title);
      $item[IC_SOAP_ITEM_QTY] = 1;
      $item[IC_SOAP_ITEM_PRICE_GROS] = $item[IC_SOAP_ITEM_PRICE_NET] = $this->itemPrice($otPayment->output[0]['value']);
      $item[IC_SOAP_ITEM_VAT_RATE] = '0.0';
      $items[] = $item;

      // store this itemID to find item in finalize basket
      $this->otPaymentItemID = $this->specialItemID;
    }

    return $items;
  }

  function &deliveryAddress() {
    $rv = false;
    if($order =& $this->getOrder()) {
      $rec = $order->delivery;
      $params = array(
      IC_SOAP_ADDRESS_SALUTATION => $rec['entry_gender'] == '' ? -1 : ($rec['entry_gender'] == 'm' ? 1 : 0),  //20110202 CA - fixed salutaion
      IC_SOAP_ADDRESS_FIRSTNAME => $this->encodeUTF8($rec['firstname']),
      IC_SOAP_ADDRESS_LASTNAME => $this->encodeUTF8($rec['lastname']),
      IC_SOAP_ADDRESS_COMPANY => $this->encodeUTF8($rec['company']),
      IC_SOAP_ADDRESS_STREET => $this->encodeUTF8($this->parseStreet($rec['street_address'])),
      IC_SOAP_ADDRESS_STREET_NO => $this->parseStreetNo($rec['street_address']),
      IC_SOAP_ADDRESS_ZIPCODE => $rec['postcode'],
      IC_SOAP_ADDRESS_CITY => $this->encodeUTF8($rec['city']),
      IC_SOAP_ADDRESS_COUNTRY => $rec['country']['iso_code_2'],
      );
      $address =& $this->icCore->getObject('IclearAddress', $instance = true);
      if($address->address($params)) {
        $rv =& $address;
      }

    }
    return $rv;
  }

  function customerInfo() {
    if(!$this->customerInfo) {
      $sql = 'SELECT ' .
                 '* ' .
               'FROM ' . 
      TABLE_CUSTOMERS . ' c ' .
               'LEFT JOIN ' . TABLE_ADDRESS_BOOK . ' ab ON (c.customers_default_address_id = ab.address_book_id) ' .
               'LEFT JOIN ' . TABLE_COUNTRIES . ' co ON (ab.entry_country_id = co.countries_id) ' .
               'WHERE ' .
                 'c.customers_id = "' . xtc_db_prepare_input($_SESSION['customer_id']) . '"';
      if($rec = $this->dbFetchRecord($sql)) {
        $info = array(
        IC_SOAP_REG_CUSTOMER_ACQUISE_ID => defined('MODULE_PAYMENT_ICLEAR_' . $this->module_name . 'ACQUISE_ID') ? constant ( 'MODULE_PAYMENT_ICLEAR_' . $this->module_name . 'ACQUISE_ID' ) : 0,
        IC_SOAP_REG_CUSTOMER_BANK_ACCOUNT_ID => '',
        IC_SOAP_REG_CUSTOMER_BANK_BIC => '',
        IC_SOAP_REG_CUSTOMER_BANK_BLZ => '',
        IC_SOAP_REG_CUSTOMER_BANK_CUSTOMER => '',
        IC_SOAP_REG_CUSTOMER_BANK_IBAN => '',
        IC_SOAP_REG_CUSTOMER_BANK_NAME => '',
        IC_SOAP_REG_CUSTOMER_COMPANYNAME => $this->encodeUTF8($rec['entry_company']),
        IC_SOAP_REG_CUSTOMER_EMAIL => $this->encodeUTF8($rec['customers_email_address']),
        IC_SOAP_REG_CUSTOMER_FAX => $this->encodeUTF8($rec['customers_fax']),
        IC_SOAP_REG_CUSTOMER_FIRSTNAME => $this->encodeUTF8($rec['entry_firstname']),
        IC_SOAP_REG_CUSTOMER_DOB => $rec['customers_dob'],
        IC_SOAP_REG_CUSTOMER_INVOICE_CITY => $this->encodeUTF8($rec['entry_city']),
        IC_SOAP_REG_CUSTOMER_INVOICE_COUNTRY => $rec['countries_iso_code_2'],
        IC_SOAP_REG_CUSTOMER_INVOICE_RECEIVER => $this->encodeUTF8($rec['entry_firstname'] . ' ' . $rec['entry_lastname']),
        IC_SOAP_REG_CUSTOMER_INVOICE_STREET => $this->encodeUTF8($this->parseStreet($rec['entry_street_address'])),
        IC_SOAP_REG_CUSTOMER_INVOICE_STREETNO => $this->parseStreetNo($rec['entry_street_address']),
        IC_SOAP_REG_CUSTOMER_INVOICE_ZIPCODE => $rec['entry_postcode'],
        IC_SOAP_REG_CUSTOMER_LASTNAME => $this->encodeUTF8($rec['entry_lastname']),
        IC_SOAP_REG_CUSTOMER_PHONE => $rec['customers_telephone'],
        IC_SOAP_REG_CUSTOMER_SALUTATION_ID => $rec['customers_gender'] == '' ? -1 : ($rec['customers_gender'] == 'm' ? 1 : 0),  //20110202 CA - fixed salutaion
        IC_SOAP_REG_CUSTOMER_EXT_CUSTOMER_ID => $rec['customers_id'],
        IC_SOAP_REG_CUSTOMER_NEWSLETTER_FLAG => '',
        );
        
        //20110202 CA - fixed DOB
        $info[IC_SOAP_REG_CUSTOMER_DOB] = str_ireplace(' ', 'T', $rec['customers_dob']);
          
        $this->customerInfo = $info;
      }

    }
    return $this->customerInfo;
  }

  function sessionID($sessionID = '') {
    return $sessionID ? xtc_session_id($sessionID) : xtc_session_id();
  }

  function customerID() {
    return isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : 0 ;
  }

  function shopID() {
    return defined('MODULE_PAYMENT_ICLEAR_' . $this->module_name . 'ID') ? constant ( 'MODULE_PAYMENT_ICLEAR_' . $this->module_name . 'ID' ) : 0;
  }

  function languageISO() {
     $rv = 'DE'; 
     
    if($_SESSION['language']) { //20110228 DB / CA - fixed language bug - language is in session
      switch($_SESSION['language']) { //20110228 DB / CA - fixed language bug - language is in session
        case 'english':
          $rv = 'EN';
          break;

        case 'espanol':
          $rv = 'ES';
          break;

        case 'german':
        default:
          $rv = 'DE';
      }
    }
    return $rv;
  }

  function httpHost($https = false) {
    $rv = '';
    if($https && ENABLE_SSL) {
      $rv = HTTPS_SERVER;
    } else {
      $rv = HTTP_SERVER;
    }
    return $rv;
  }

  function redirect($url = '') {
    if($url) {
      xtc_redirect($url);
      exit;
    }
  }

  function finalizeBasket() {
    if( $this->otPaymentItemID && ($basket =& $this->icCore->getBasket()) ) {
      $order =& $this->getOrder();
      $basketGros = $basket->basketTotalGros();
      $basketNet = $basket->basketTotalNet();

      foreach($basket->basketItems() AS $item) {
        if($item[IC_SOAP_ITEM_NO] == $this->otPaymentItemID) {
          if($this->showPriceTax) {
            $orderGros = $this->itemPrice($order->info['total']);
            $orderNet = $this->itemPrice($order->info['total'] - $order->info['tax']);
          } else {
            $orderGros = $this->itemPrice($order->info['total'] + $order->info['tax']);
            $orderNet = $this->itemPrice($order->info['total']);
          }

          $deltaGros = $basketGros - $orderGros;
          $deltaNet = $basketNet - $orderNet;

          $item[IC_SOAP_ITEM_PRICE_GROS] -= $deltaGros;
          $item[IC_SOAP_ITEM_PRICE_NET] -= $deltaNet;

          $basket->setBasketItem($this->otPaymentItemID, $item);
        }
      }
    }
  }

  function storeOrder() {
    die("Store order not implemented in enclosing instance!\n");
  }

  // this function is called in calls from iclear server as initiator - shop environemnt only loaded throug config!

  function maskOrder() {
    $rc = false;
    $basket =& $this->icCore->getBasket();
    if($orderID = $basket->orderID()) {
      $lang = $this->getLanguage();
      $fields = false;
      foreach(array('delivery', 'billing') AS $prefix) {
        foreach($this->addressKeysMask AS $key => $val) {
          //xtc_db_prepare_input($order->$prefix[$key])
          $fields[] = $prefix.'_'.$key . ' = ' . ($val == 'info' ? '"'.$lang->getParam('ORDER_WAITING').'"' : '"**********"');
        }
      }
      if($fields) {
        $sql = 'UPDATE ' . TABLE_ORDERS . ' SET ' . implode(',', $fields) . ' WHERE orders_id = ' . $orderID . ' LIMIT 1';
        $rc = xtc_db_query($sql);
      }
    }
    return $rc;
  }

  function unmaskOrder() {
    $rc = false;
    $basket = $this->icCore->getBasket();
    if($orderID = $basket->orderID()) {
      $order = $basket->getOrder();
      $fields = false;
      foreach(array('delivery', 'billing') AS $prefix) {
        $src = $order->$prefix;
        foreach($this->addressKeysUnmask AS $key) {
          $fields[] = $prefix.'_'.$key . ' = "' . xtc_db_prepare_input($src[$key]) .'"';
        }
      }
      if($fields) {
        $fields[] = 'delivery_name = "' . $order->delivery['firstname'] . ' ' . $order->delivery['lastname'] . '"';
        $fields[] = 'billing_name = "' . $order->billing['firstname'] . ' ' . $order->billing['lastname'] . '"';
        $fields[] = 'orders_status = "' . constant( 'MODULE_PAYMENT_ICLEAR_' . $this->module_name . 'ORDER_STATUS_ID') . '"';
        $sql = 'UPDATE ' . TABLE_ORDERS . ' SET ' . implode(',', $fields) . ' WHERE orders_id = ' . $orderID . ' LIMIT 1';
        if($rc = xtc_db_query($sql)) {
          $sql = 'INSERT INTO ' . TABLE_ORDERS_STATUS_HISTORY . ' (' .
                     'orders_id, ' .
                     'orders_status_id, ' .
                     'date_added, ' .
                     'customer_notified ' .
                   ') VALUES (' .
                     '"' . $orderID . '", ' .
                     '"' . constant ( 'MODULE_PAYMENT_ICLEAR_' . $this->module_name . 'ORDER_STATUS_ID' ) . '", ' .
                     'NOW(), ' .
                     '"0"' .
                   ')';
          xtc_db_query($sql);
        }
      }
    }
    return $rc;
  }

  function cancelOrder($orderID = 0) {
    // TODO: what's about to check the order undo result (always true correct)?
    $rc = true;
    $orderID = (int) $orderID;

    if($orderID) {
      $sql = "SELECT products_id, products_quantity from " . TABLE_ORDERS_PRODUCTS . " WHERE orders_id = '" . (int)$orderID . "'";
      $pqry = xtc_db_query($sql);

      while ($product = xtc_db_fetch_array($pqry)) {
        xtc_db_query("UPDATE " . TABLE_PRODUCTS . " SET " .
                         "products_quantity = products_quantity + " . $product['products_quantity'] . ", " .
                         "products_ordered = products_ordered - " . $product['products_quantity'] .
                       " WHERE products_id = '" . (int)$product['products_id'] . "'");
      }
      xtc_db_query('DELETE FROM ' . TABLE_ORDERS_PRODUCTS . ' WHERE orders_id = "' . $orderID . '"');
      xtc_db_query('DELETE FROM ' . TABLE_ORDERS . ' WHERE orders_id = "' . $orderID . '" LIMIT 1');
    }
    return $rc;
  }

  function shopURL() {
    $rv = '';
    $basket =& $this->icCore->getBasket();
    $icSessionParam = IC_SESSION_NAME . '=' . $basket->sessionID();

    if($basket->orderAccepted()) {
      if($this->status == IC_ORDER_CANCEL) {
        if ( $this->bIframe )
        $rv = xtc_href_link('checkout_iclear.php', $icSessionParam . '&ic_cancel=1', 'SSL'); // 20110203 CA - fixed link, session is default generated
        else
        $rv = xtc_href_link(FILENAME_DEFAULT, $icSessionParam, 'SSL'); // 20110203 CA - fixed link, session is default generated
      } else {
        if ( $this->bIframe ) {
          $_SESSION['ic_processed'] = true;
          $rv = xtc_href_link('checkout_iclear.php',  $icSessionParam . '&ic_processed=1', 'SSL'); // 20110203 CA - fixed link, session is default generated
        } else {
          $rv = xtc_href_link(FILENAME_CHECKOUT_SUCCESS, $icSessionParam, 'SSL'); // 20110203 CA - fixed link, session is default generated
        }
      }
    } else {
      $lang =& $this->icCore->getLanguage();
      if ( $this->bIframe )
      $rv =  xtc_href_link('checkout_iclear.php', $icSessionParam . '&error_message=' . urlencode($lang->getParam('TRANSACTION_ERROR')) . '&ic_cancel=1'); // 20110203 CA - fixed link, session is default generated
      else
      $rv = xtc_href_link(FILENAME_CHECKOUT_PAYMENT, $icSessionParam . '&error_message=' . urlencode($lang->getParam('TRANSACTION_ERROR')), 'SSL'); // 20110203 CA - fixed link, session is default generated
    }
    return str_replace('&amp;', '&', $rv); //20110203 CA - removed gambio bugs
  }

  /**
   * objects methods expected 2 exist by shop system
   *
   */

  function update_status() {
    global $order;

    // BOF - Hendrik - 2010-08-11 - exlusion config for shipping modules
    if( 'MODULE_PAYMENT_ICLEAR_' . $this->module_name . 'NEG_SHIPPING' != '' ) {
      $neg_shpmod_arr = explode(',','MODULE_PAYMENT_ICLEAR_' . $this->module_name . 'NEG_SHIPPING');
      foreach( $neg_shpmod_arr as $neg_shpmod ) {
        $nd=$neg_shpmod.'_'.$neg_shpmod;
        //BOF - DokuMan - 2011-12-22 - fix undefined index in modules -> payment methods
        //if( $_SESSION['shipping']['id']==$nd || $_SESSION['shipping']['id']==$neg_shpmod ) {
        if( isset($_SESSION['shipping']['id']) && ($_SESSION['shipping']['id']==$nd || $_SESSION['shipping']['id']==$neg_shpmod )) {
        //EOF - DokuMan - 2011-12-22 - fix undefined index in modules -> payment methods
          $this->enabled = false;
          break;
        }
      }
    }
    // EOF - Hendrik - 2010-08-11 - exlusion config for shipping modules

    if ( ($this->enabled == true) && ((int) constant ( 'MODULE_PAYMENT_ICLEAR_' . $this->module_name . 'ZONE' ) > 0) ) {
      $check_flag = false;
      $check_query = xtc_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES .
                                    " where " .
                                    "geo_zone_id = '" . constant ( 'MODULE_PAYMENT_ICLEAR_' . $this->module_name . 'ZONE' ) . "'" .
                                    " and zone_country_id = '" . $order->billing['country']['id'] . "'" .
                                    " order by zone_id");
      while ($check = xtc_db_fetch_array($check_query)) {
        if ($check['zone_id'] < 1) {
          $check_flag = true;
          break;
        } elseif ($check['zone_id'] == $order->billing['zone_id']) {
          $check_flag = true;
          break;
        }
      }

      if ($check_flag == false) {
        $this->enabled = false;
      }
    }
  }

  function javascript_validation() {
    return false;
  }

  function selection() {
    return array (
        'id' => $this->code,
        'module' => $this->title,
        'fields' => '',
        'description' => $this->lang->getParam('INFO_EXTENDED' . $this->module_name_lang)
    );
  }

  function pre_confirmation_check($vars = '') {
    return false;
  }

  function confirmation() {
    return false;
  }

  function process_button($vars = '') {
    $rv = '';

    unset($_SESSION['ic_processed']);
    //unset($_SESSION['icSessionID']);
    //unset($_SESSION['icBasketID']);

    $soap =& $this->icCore->getObject('IclearSOAP');
    $rv = xtc_draw_hidden_field('icRedirect', 1);
    if ($this->icCore->wrapperID() == "xtc304sp2.gambiogx2") //20110330 CA - added session icRedirect for gambiogx2
      $_SESSION['icRedirect'] = 1;
      
    if($this->icCore->perform($soap, IC_SOAP_SENDORDER_METHOD)) {
      // ajax checkout patch
      if(!$this->axCheckout()) {
        $rv .= xtc_draw_hidden_field('conditions', $_POST['conditions']);
      }
    } else {
      $lang =& $this->icCore->getLanguage();
      $rv .= xtc_draw_hidden_field('icErrorURL',
      urlencode($lang->getParam('TRANSACTION_ERROR'))
      );
      if ( !$this->bIframe )
      $rv .= xtc_draw_hidden_field('icErrorMessage', 'error_message=' . urlencode($soap->lastError()));
    }
    return $rv;
  }

  function before_process() {
    $basket =& $this->icCore->getBasket();
    // if there's no wsdlResult, no confirmation was done!

    if(preg_match('/checkout_iclear/', $_SERVER['SCRIPT_FILENAME'])) {
      return;
    }

    if((isset($_POST['icRedirect']) || isset($_SESSION['icRedirect'])) && ($url = $basket->iclearURL()) ) { //20110330 CA - added session icRedirect for gambiogx2
      if ( !$this->bIframe )
      xtc_redirect($url);
      else
      xtc_redirect(xtc_href_link('checkout_iclear.php'));
      exit();
    }
    $lang =& $this->icCore->getLanguage();
    xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '&error_message=' . urlencode($lang->getParam('TRANSACTION_ERROR'))));
    exit();
  }

  function after_process() {
    return false;
  }

  function output_error() {
    return false;
  }

  function check() {
    if (!isset($this->installOK)) {
      $check_query = xtc_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_ICLEAR_" . $this->module_name . "STATUS'");
      $this->installOK = xtc_db_num_rows($check_query);
    }
    return $this->installOK;
  }

  function install() {
    if($this->installTable()) {
      // remove old keys
      xtc_db_query("DELETE from " . TABLE_CONFIGURATION . " WHERE configuration_key in ('" . implode("', '", $this->keys(true)) . "')");

      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (" .
                       "configuration_key, " .
                       "configuration_value, ".
                       "configuration_group_id, " .
                       "sort_order, " .
                       "set_function," .
                       "date_added" .
                     ") VALUES (" .
                       "'MODULE_PAYMENT_ICLEAR_" . $this->module_name . "STATUS', " .
                       "'True', " .
                       "'6', " .
                       "'0', " .
                       "'xtc_cfg_select_option(array(\'True\', \'False\'), ', " .
                       "NOW()" .
                      ")");

      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (" .
                       "configuration_key, " .
                       "configuration_value, " .
                       "configuration_group_id, " .
                       "sort_order, " .
                       "date_added" .
                     ") VALUES (" .
                       "'MODULE_PAYMENT_ICLEAR_" . $this->module_name . "ID', " .
                       "'ShopID', " .
                       "'6', " .
                       "'1', " .
                       "NOW()" .
                     ")");

      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (" .
                       "configuration_key, " .
                       "configuration_value, ".
                       "configuration_group_id, " .
                       "sort_order, " .
                       "date_added" .
                     ") VALUES (" .
                       "'MODULE_PAYMENT_ICLEAR_" . $this->module_name . "ALLOWED', " .
                       "'', " .
                       "'6', " .
                       "'2', " .
                       "NOW()" .
                      ")");

      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (" .
                       "configuration_key, " .
                       "configuration_value, " .
                       "configuration_group_id, " .
                       "sort_order, " .
                       "date_added" .
                     ") VALUES (" .
                       "'MODULE_PAYMENT_ICLEAR_" . $this->module_name . "SORT_ORDER', " .
                       "'0', " .
                       "'6', " .
                       "'3', " .
                       "NOW()" .
                     ")");

      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (" .
                       "configuration_key, " .
                       "configuration_value, " .
                       "configuration_group_id, " .
                       "sort_order, " .
                       "use_function, " .
                       "set_function, " .
                       "date_added" .
                     ") VALUES (" .
                       "'MODULE_PAYMENT_ICLEAR_" . $this->module_name . "ZONE', " .
                       "'0', " .
                       "'6', " .
                       "'2', " .
                       "'xtc_get_zone_class_title', " .
                       "'xtc_cfg_pull_down_zone_classes(', " .
                       "NOW()" .
                     ")");

      xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." ( " .
                       "configuration_key, " .
                       "configuration_value, " .
                       "configuration_group_id, " .
                       "sort_order, " .
                       "set_function, " .
                       "use_function, " .
                       "date_added" .
                     ") VALUES (" .
                       "'MODULE_PAYMENT_ICLEAR_" . $this->module_name . "ORDER_STATUS_ID'," .
                       "'1', " .
                       "'6', " .
                       "'0', " .
                       "'xtc_cfg_pull_down_order_statuses(', " .
                       "'xtc_get_order_status_name', " .
                       "now()" .
                     ")");
      // BOF - Hendrik - 2010-08-11 - exlusion config for shipping modules
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_ICLEAR_" . $this->module_name . "NEG_SHIPPING', '', '6', '99', now())");
      // EOF - Hendrik - 2010-08-11 - exlusion config for shipping modules
                     
      // create iclear waiting order status
      $status = $this->dbFetchRecord('SELECT orders_status_id FROM ' . TABLE_ORDERS_STATUS . ' ORDER BY orders_status_id DESC LIMIT 1');

      if(is_array($status) && $status['orders_status_id']) {
        $status['orders_status_id']++;
      } else {
        $status['orders_status_id'] = 1;
      }

      xtc_db_query('INSERT INTO ' . TABLE_ORDERS_STATUS . ' ' .
                       '(orders_status_id, language_id, orders_status_name) ' .
                     'VALUES ' .
                       '(' . $status['orders_status_id'] . ', 1, "iclear pending" )');

      xtc_db_query('INSERT INTO ' . TABLE_ORDERS_STATUS . ' ' .
                       '(orders_status_id, language_id, orders_status_name) ' .
                      'VALUES ' .
                      '(' . $status['orders_status_id'] . ', 2, "iclear wartend" )');

      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (" .
                       "configuration_key, " .
                       "configuration_value, " .
                       "configuration_group_id, " .
                       "sort_order, " .
                       "date_added" .
                     ") VALUES (" .
                       "'MODULE_PAYMENT_ICLEAR_" . $this->module_name . "STATUS_WAIT_ID', " .
                       "'" . $status['orders_status_id'] . "', " .
                       "'6', " .
                       "'0', " .
                       "NOW()" .
                     ")");

      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (" .
                       "configuration_key, " .
                       "configuration_value, ".
                       "configuration_group_id, " .
                       "sort_order, " .
                       "set_function," .
                       "date_added" .
                     ") VALUES (" .
                       "'MODULE_PAYMENT_ICLEAR_" . $this->module_name . "IFRAME', " .
                       "'True', " .
                       "'6', " .
                       "'0', " .
                       "'xtc_cfg_select_option(array(\'True\', \'False\'), ', " .
                       "NOW()" .
                      ")");
    }
  }



  function remove() {
    xtc_db_query("DELETE from " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode("', '", $this->keys(true)) . "')");
    // check 4 lagging orders, restock and delete 'em
    $status_qry = xtc_db_query('SELECT orders_status_id FROM ' . TABLE_ORDERS_STATUS . ' WHERE orders_status_name LIKE "iclear_' . $this->module_name . '%" LIMIT 1');
    $status = xtc_db_fetch_array($status_qry);
    if(is_array($status) && $status['orders_status_id']) {
      // check if there are any lagging orders in the orders table
      // if, remove them and update stock and orders history
      $orders_qry = xtc_db_query('SELECT orders_id FROM ' . TABLE_ORDERS . ' WHERE orders_status = ' . $status['orders_status_id']);
      if($orders_qry) {
        while($order = xtc_db_fetch_array($orders_qry)) {
          $this->cancelOrder($order['orders_id']);
        }
      }
    }
    xtc_db_query('DELETE FROM ' . TABLE_ORDERS_STATUS . ' WHERE orders_status_name LIKE "iclear_' . $this->module_name . '%"');
  }

  function keys($all = false) {
    $rv = array(
        'MODULE_PAYMENT_ICLEAR_' . $this->module_name . 'STATUS',
        'MODULE_PAYMENT_ICLEAR_' . $this->module_name . 'ALLOWED',
        'MODULE_PAYMENT_ICLEAR_' . $this->module_name . 'ID',
        'MODULE_PAYMENT_ICLEAR_' . $this->module_name . 'ZONE',
        'MODULE_PAYMENT_ICLEAR_' . $this->module_name . 'SORT_ORDER',
        'MODULE_PAYMENT_ICLEAR_' . $this->module_name . 'ORDER_STATUS_ID',
        'MODULE_PAYMENT_ICLEAR_' . $this->module_name . 'IFRAME',
        'MODULE_PAYMENT_ICLEAR_' . $this->module_name . 'NEG_SHIPPING',  // Hendrik - 2010-08-11 - exlusion config for shipping modules
    );
    if($all) {
      $rv[] = 'MODULE_PAYMENT_ICLEAR_' . $this->module_name . 'STATUS_WAIT_ID';
    }

    return $rv;
  }
  
  function moduleName ( $name ) {
    if ( strlen ( $name  ) > 1 ) {
      $this->module_name = strtoupper( $name ) . '_';
      $this->module_name_lang = '_' . strtoupper( $name );
    }
    else {
      $this->module_name = "";
      $this->module_name_lang = "";
    }
  }

  function pageparams ( ) {
    $Param = array( );
    if ( $this->bIframe ) {
      $Param['mode'] = 'inline';
    }
    else {
      $Param['mode'] = 'std';
    }
    return $Param;
  }
  
  function paymentType ( ) {
    $rv = false;
    if ( strlen( $this->module_name ) > 0 ) {
      switch ( $this->module_name ) {
        case "INVOICE_": 
          $rv = 'bi';
          break;
        case "CREDITCARD_":
          $rv = 'cc';
          break;
        case "ONLINEBANKING_":
          $rv = 'ob';
          break;
        case "PREPAYMENT_":
          $rv = 'ia';
          break;
        default:
          break;  
      }
    }
    return $rv;
  }
  
  function conditionsAccepted ( ) {
    return 1;
  }

}
?>