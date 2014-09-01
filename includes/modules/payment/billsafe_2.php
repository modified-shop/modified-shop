<?php
/* -----------------------------------------------------------------------------------------
   $Id: billsafe_2.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   Copyright (c) 2013 PayPal SE and Bernd Blazynski

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

/*
* id = billsafe_2.php
* location = /includes/modules/payment
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License, version 2, as
* published by the Free Software Foundation.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* @package BillSAFE_2
* @copyright (C) 2013 Bernd Blazynski
* @license GPLv2
*/

class billsafe_2 {
  var $code;
  var $title;
  var $description;
  var $enabled;
  var $info;
  var $response;

  function billsafe_2() {
    global $order;
    require (DIR_FS_CATALOG.'includes/external/billsafe/classes/billsafe_2/ini.php'); //DokuMan - 2012-06-19 - move billsafe to external directory
    $this->code = 'billsafe_2';
    $this->title = MODULE_PAYMENT_BILLSAFE_2_TEXT_TITLE;
    if (preg_match('/checkout_payment/',$_SERVER['PHP_SELF'])) {
      $url_image = $this->checkBillSAFELogoURL(MODULE_PAYMENT_BILLSAFE_2_BILLSAFE_LOGO_URL);
      $this->info ='<img src="'.$url_image.'" title="BillSAFE" alt="BillSAFE" style="margin-right:10px; float:left;" />'.MODULE_PAYMENT_BILLSAFE_2_CHECKOUT_TEXT_INFO;
    }
    $this->description = MODULE_PAYMENT_BILLSAFE_2_TEXT_DESCRIPTION;
    $this->sort_order = MODULE_PAYMENT_BILLSAFE_2_SORT_ORDER;
    $this->enabled = ((MODULE_PAYMENT_BILLSAFE_2_STATUS == 'True') ? true : false);
    $this->application_version = $ini['applicationVersion'];
    $this->applicationSignature = $ini['applicationSignature'];
    $this->api_version = '2.0';
    $this->signature = 'billsafe|billsafe|1.0|2.0';
    $currency = $_SESSION ['currency'];
    if ((int)MODULE_PAYMENT_BILLSAFE_2_ORDER_STATUS_ID > 0) $this->order_status = MODULE_PAYMENT_BILLSAFE_2_ORDER_STATUS_ID;
    if(isset($_SESSION['billsafe_status']) && $_SESSION['billsafe_status'] == 'declined') $this->enabled = false; //Dokuman - 2012-06-19 - added isset check
    $this->check();
    if (is_object($order)) $this->update_status();
  }

  function update_status() {
    global $order;
    if (($this->enabled == true) && ((int)MODULE_PAYMENT_BILLSAFE_2_ZONE > 0) ) {
      $check_flag = false;
      $check_query = xtc_db_query('SELECT zone_id FROM '.TABLE_ZONES_TO_GEO_ZONES.' WHERE geo_zone_id = "'.xtc_db_input(MODULE_PAYMENT_BILLSAFE_2_ZONE).'" AND zone_country_id = "'.xtc_db_input($order->billing['country']['id']).'" ORDER BY zone_id');
      while ($check = xtc_db_fetch_array($check_query)) {
        if ($check['zone_id'] < 1) {
          $check_flag = true;
          break;
        } elseif ($check['zone_id'] == $order->billing['zone_id']) {
          $check_flag = true;
          break;
        }
      }
      if ($check_flag == false) $this->enabled = false;
    }
  }

  function javascript_validation() {
    return false;
  }

  function selection() {
    global $order, $xtPrice;
    $currency = $_SESSION['currency'];
    $customer_id = $_SESSION['customer_id'];
    if (strtoupper($currency) != 'EUR') {
      $display = null;
      return $display;
      break;
    }
    $order_total = $order->info['total'];
    $min_order = MODULE_PAYMENT_BILLSAFE_2_MIN_ORDER;
    $max_order = MODULE_PAYMENT_BILLSAFE_2_MAX_ORDER;
    if (($order_total < $min_order) || ($order_total > $max_order)) {
      $display = null;
      return $display;
      break;
    }
    require_once (DIR_FS_CATALOG.'includes/external/billsafe/classes/billsafe_2/billsafe_2.php');//DokuMan - 2012-06-19 - move billsafe to external directory
    $bs = new Billsafe_Sdk(DIR_FS_CATALOG.'includes/external/billsafe/classes/billsafe_2/ini.php');//DokuMan - 2012-06-19 - move billsafe to external directory
    if (MODULE_PAYMENT_BILLSAFE_2_LOG == 'True') {
      if (MODULE_PAYMENT_BILLSAFE_2_LOG_TYPE == 'Echo') {
        require_once DIR_FS_CATALOG.'includes/external/billsafe/classes/billsafe_2/LoggerEcho.php';//DokuMan - 2012-06-19 - move billsafe to external directory
        $bs->setLogger(new Billsafe_LoggerEcho());
      } elseif (MODULE_PAYMENT_BILLSAFE_2_LOG_TYPE == 'Mail') {
        require_once DIR_FS_CATALOG.'includes/external/billsafe/classes/billsafe_2/LoggerMail.php';//DokuMan - 2012-06-19 - move billsafe to external directory
        $bs->setLogger(new Billsafe_LoggerMail(MODULE_PAYMENT_BILLSAFE_2_LOG_ADDR));
      } elseif (MODULE_PAYMENT_BILLSAFE_2_LOG_TYPE == 'File') {
        require_once DIR_FS_CATALOG.'includes/external/billsafe/classes/billsafe_2/LoggerFile.php';//DokuMan - 2012-06-19 - move billsafe to external directory
        $bs->setLogger(new Billsafe_LoggerFile(DIR_FS_CATALOG.'export/BillSAFE_'.date('YmdHis').'.log'));
      }
    }
    if ($_SESSION['language_charset'] == 'iso-8859-1' || $_SESSION['language_charset'] == 'iso-8859-15') {
      $bs->setUtf8Mode(false);
    } else {
      $bs->setUtf8Mode(true);
    }
    if (MODULE_PAYMENT_BILLSAFE_2_SERVER == 'Live') {
      $bs->setMode('LIVE');
    } else {
      $bs->setMode('SANDBOX');
    }
    $bs->setCredentials(array('merchantId' => MODULE_PAYMENT_BILLSAFE_2_MERCHANT_ID, 'merchantLicenseSandbox' => MODULE_PAYMENT_BILLSAFE_2_MERCHANT_LICENSE, 'merchantLicenseLive' => MODULE_PAYMENT_BILLSAFE_2_MERCHANT_LICENSE, 'applicationSignature' => $this->applicationSignature, 'applicationVersion' => $this->application_version));
    $shipping_cost = $order->info['shipping_cost'];
    if ($shipping_cost > 0) {
      $shipping_id = explode('_', $order->info['shipping_class']);
      $shipping_id = $shipping_id[0];
      $shipping_tax_rate = $this->get_shipping_tax_rate($shipping_id);
      $shipping_cost = round($order->info['shipping_cost'] * $xtPrice->currencies[$currency]['value'], $xtPrice->get_decimal_places($currency));
      $shipping_cost = xtc_add_tax($shipping_cost, $shipping_tax_rate);
    }
    $customer_query = xtc_db_query('SELECT customers_gender, DATE_FORMAT(customers_dob, "%Y-%m-%d") AS customers_dob, customers_email_address, customers_telephone from '.TABLE_CUSTOMERS.' WHERE customers_id = "'.xtc_db_input($customer_id).'"');
    if (xtc_db_num_rows($customer_query)) $customer = xtc_db_fetch_array($customer_query);
    if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
      $total = $order->info['subtotal'] + $order->info['tax'];
    } else {
      $total = $order->info['subtotal'];
    }
    if (MODULE_PAYMENT_BILLSAFE_2_SCHG != '') {
      $schg_tax_rate = xtc_get_tax_rate(MODULE_PAYMENT_BILLSAFE_2_SCHGTAX);
      if (stristr(MODULE_PAYMENT_BILLSAFE_2_SCHG, '%')) {
        $schg_amount = $total * MODULE_PAYMENT_BILLSAFE_2_SCHG / 100;
        $schg = '<br />'.MODULE_PAYMENT_BILLSAFE_2_SCHG_TEXT_INFO.MODULE_PAYMENT_BILLSAFE_2_SCHG;
      } else {
        $schg_amount = MODULE_PAYMENT_BILLSAFE_2_SCHG;
        $schg = '<br />'.MODULE_PAYMENT_BILLSAFE_2_SCHG_TEXT_INFO.$xtPrice->xtcFormat($schg_amount, true);
      }
    } else {
      $schg = '';
      $schg_amount = 0;
    }
    $total = $total + $schg_amount;
    $total = round($xtPrice->xtcCalculateCurrEx($total, $_SESSION['currency']), $xtPrice->get_decimal_places($_SESSION['currency']));
    $total = number_format(round(($total + $shipping_cost), $xtPrice->get_decimal_places($currency)), 2, '.', '');
    $company_b = md5($order->billing['company']);
    $company_d = md5($order->delivery['company']);
    $firstname_b = md5($order->billing['firstname']);
    $firstname_d = md5($order->delivery['firstname']);
    $lastname_b = md5($order->billing['lastname']);
    $lastname_d = md5($order->delivery['lastname']);
    $email = md5($customer['customers_email_address']);
    $phone = md5($customer['customers_telephone']);
    if ($customer['customers_gender'] != '') {
      $params = array('order_amount' => round($total, $xtPrice->get_decimal_places($currency)), 'order_currencyCode' => $currency, 'customer_id' => $customer_id, 'customer_gender' => $customer['customers_gender'], 'customer_company' => $company_b, 'deliveryAddress_company' => $company_d, 'customer_firstname' => $firstname_b, 'deliveryAddress_firstname' => $firstname_d, 'customer_lastname' => $lastname_b, 'deliveryAddress_lastname' => $lastname_d, 'customer_street' => $order->billing['street_address'], 'deliveryAddress_street' => $order->delivery['street_address'], 'customer_postcode' => $order->billing['postcode'], 'deliveryAddress_postcode' => $order->delivery['postcode'], 'customer_city' => $order->billing['city'], 'deliveryAddress_city' => $order->delivery['city'], 'customer_country' => $order->billing['country']['iso_code_2'], 'deliveryAddress_country' => $order->delivery['country']['iso_code_2'], 'customer_dateOfBirth' => $customer['customers_dob'],'customer_email' => $email, 'customer_phone' => $phone, );
    } else {
      $params = array('order_amount' => round($total, $xtPrice->get_decimal_places($currency)), 'order_currencyCode' => $currency, 'customer_id' => $customer_id, 'customer_company' => $company_b, 'deliveryAddress_company' => $company_d, 'customer_firstname' => $firstname_b, 'deliveryAddress_firstname' => $firstname_d, 'customer_lastname' => $lastname_b, 'deliveryAddress_lastname' => $lastname_d, 'customer_street' => $order->billing['street_address'], 'deliveryAddress_street' => $order->delivery['street_address'], 'customer_postcode' => $order->billing['postcode'], 'deliveryAddress_postcode' => $order->delivery['postcode'], 'customer_city' => $order->billing['city'], 'deliveryAddress_city' => $order->delivery['city'], 'customer_country' => $order->billing['country']['iso_code_2'], 'deliveryAddress_country' => $order->delivery['country']['iso_code_2'], 'customer_dateOfBirth' => $customer['customers_dob'],'customer_email' => $email, 'customer_phone' => $phone, );
    }
    $response = $bs->callMethod('prevalidateOrder', $params);
    if ($response->ack == 'OK') {
      if ($response->invoice->isAvailable == 'TRUE') {
        $display = array('id' => $this->code, 'module' => $this->title, 'description' => $this->info.$schg.'<div style="clear:both;">
</div>');
      } else {
        $display = array('id' => $this->code, 'module' => $this->title, 'description' => $this->info.$schg.'<br /><b><font color="#ff0000">'.$response->invoice->message.'</font></b><div style="clear:both;">
</div>');
//        $display = null;
      }
    } else {
      $display = null;
    }
    return $display;
  }

  function pre_confirmation_check() {
    return false;
  }

  function confirmation() {
    $_SESSION['discount_value'] = $GLOBALS['ot_discount']->output[0]['value'];
    $_SESSION['discount_name'] = $GLOBALS['ot_discount']->output[0]['title'];
    $_SESSION['voucher_value'] = $GLOBALS['ot_gv']->output[0]['value'];
    $_SESSION['voucher_name'] = $GLOBALS['ot_gv']->output[0]['title'];
    $_SESSION['coupon_value'] = $GLOBALS['ot_coupon']->output[0]['value'];
    $_SESSION['coupon_name'] = $GLOBALS['ot_coupon']->output[0]['title'];
    $_SESSION['schg_value'] = $GLOBALS['ot_billsafe']->output[0]['value'];
    $_SESSION['schg_name'] = $GLOBALS['ot_billsafe']->output[0]['title'];
    $_SESSION['lofee_value'] = $GLOBALS['ot_loworderfee']->output[0]['value'];
    $_SESSION['lofee_name'] = $GLOBALS['ot_loworderfee']->output[0]['title'];
    return false;
  }

  function process_button() {
    global $order;
    $payment_type = $this->title;
    $process_button_string = xtc_draw_hidden_field('paymentType', $payment_type);
    if (MODULE_PAYMENT_BILLSAFE_2_LAYER == 'True') {
      if (MODULE_PAYMENT_BILLSAFE_2_SERVER == 'Live') {
        $lisb = 'false';
      } else {
        $lisb = 'true';
      }
          //var formElement = document.getElementsByTagName(\'form\')[0];
          //var formElement = document.getElementById(\'checkout_confirmation\');
      $process_button_string .= '<script type="text/javascript" src="https://content.billsafe.de/lpg/js/client.js"></script>
        <script type="text/javascript"><!--
          var formElement = document.getElementById(\'checkout_confirmation\');
          var lpg = new BillSAFE.LPG.client({form: formElement, conditions: {invoice: [{element: \'paymentType\', value: \''.$payment_type.'\'}]}, sandbox: '.$lisb.'});
        //--></script>';
    }
    return $process_button_string;
  }

  function before_process() {
    global $order, $xtPrice;
    $currency = $_SESSION['currency'];
    $customer_id = $_SESSION['customer_id'];
    if(sizeof($order->delivery) != sizeof($order->billing)) {
      $display = null;
    } else {
      if (is_array($order->billing)) {
        foreach ($order->billing as $key => $val) {
          if ($order->billing[$key] != $order->delivery[$key]) xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code.'&error_message='.stripslashes(urlencode(html_entity_decode(MODULE_PAYMENT_BILLSAFE_2_ERROR_MESSAGE_ADDRESS))), 'SSL'));
        }
      }
    }
//    if ($order->billing['company'] != '') xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code.'&error_message='.stripslashes(html_entity_decode(MODULE_PAYMENT_BILLSAFE_2_ERROR_MESSAGE_COMPANY)), 'SSL'));
    if (empty($_GET['token'])) {
      require_once (DIR_FS_CATALOG.'includes/external/billsafe/classes/billsafe_2/billsafe_2.php');//DokuMan - 2012-06-19 - move billsafe to external directory
      $bs = new Billsafe_Sdk(DIR_FS_CATALOG.'includes/external/billsafe/classes/billsafe_2/ini.php');//DokuMan - 2012-06-19 - move billsafe to external directory
      if (MODULE_PAYMENT_BILLSAFE_2_LOG == 'True') {
        if (MODULE_PAYMENT_BILLSAFE_2_LOG_TYPE == 'Echo') {
          require_once DIR_FS_CATALOG.'includes/external/billsafe/classes/billsafe_2/LoggerEcho.php';//DokuMan - 2012-06-19 - move billsafe to external directory
          $bs->setLogger(new Billsafe_LoggerEcho());
        } elseif (MODULE_PAYMENT_BILLSAFE_2_LOG_TYPE == 'Mail') {
          require_once DIR_FS_CATALOG.'includes/external/billsafe/classes/billsafe_2/LoggerMail.php';//DokuMan - 2012-06-19 - move billsafe to external directory
          $bs->setLogger(new Billsafe_LoggerMail(MODULE_PAYMENT_BILLSAFE_2_LOG_ADDR));
        } elseif (MODULE_PAYMENT_BILLSAFE_2_LOG_TYPE == 'File') {
          require_once DIR_FS_CATALOG.'includes/external/billsafe/classes/billsafe_2/LoggerFile.php';//DokuMan - 2012-06-19 - move billsafe to external directory
          $bs->setLogger(new Billsafe_LoggerFile(DIR_FS_CATALOG.'export/BillSAFE_'.date('YmdHis').'.log'));
        }
      }
      if ($_SESSION['language_charset'] == 'iso-8859-1' || $_SESSION['language_charset'] == 'iso-8859-15') {
        $bs->setUtf8Mode(false);
      } else {
        $bs->setUtf8Mode(true);
      }
      if (MODULE_PAYMENT_BILLSAFE_2_SERVER == 'Live') {
        $bs->setMode('LIVE');
      } else {
        $bs->setMode('SANDBOX');
      }
      $bs->setCredentials(array('merchantId' => MODULE_PAYMENT_BILLSAFE_2_MERCHANT_ID, 'merchantLicenseSandbox' => MODULE_PAYMENT_BILLSAFE_2_MERCHANT_LICENSE, 'merchantLicenseLive' => MODULE_PAYMENT_BILLSAFE_2_MERCHANT_LICENSE, 'applicationSignature' => $this->applicationSignature, 'applicationVersion' => $this->application_version));
      $schg_tax = 0;
      $article = array();
      for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
        if (is_array($order->products[$i]['attributes'])) {
          for ($ia = 0, $na = sizeof($order->products[$i]['attributes']); $ia < $na; $ia++) {
            for ($iao = 0, $nao = sizeof($order->products[$i]['attributes'][$ia]['option']); $iao < $nao; $iao++) {
              $att .= '-'.$order->products[$i]['attributes'][$ia]['value'];
            }
          }
        }
        $article[$i]['number'] = $order->products[$i]['model'].$att;
        $article[$i]['name'] = $order->products[$i]['name'];
        $article[$i]['type'] = 'goods';
        $article[$i]['quantity'] = intval($order->products[$i]['qty']);
        if ($_SESSION['customers_status']['customers_status_show_price_tax'] == '0') {
          $article[$i]['netPrice'] = number_format($order->products[$i]['price'], 2, '.','');
        } else {
          $article[$i]['grossPrice'] = number_format($order->products[$i]['price'], 2, '.','');
        }
        if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 0) {
          $article[$i]['tax'] = number_format(0, 2, '.', '');
        } else {
          $article[$i]['tax'] = number_format($order->products[$i]['tax'], 2, '.', '');
        }
        unset($att);
      }
      $shipping_cost = $order->info['shipping_cost'];
      if ($shipping_cost > 0) {
        $shipping_id = explode('_', $order->info['shipping_class']);
        $shipping_id = $shipping_id[0];
        $shipping_tax_rate = $this->get_shipping_tax_rate($shipping_id);
        $article[$i]['number'] = 'Versand';
        $article[$i]['name'] = $order->info['shipping_method'];
        $article[$i]['type'] = 'shipment';
        $article[$i]['quantity'] = 1;
        $shipping_cost = round($order->info['shipping_cost'] * $xtPrice->currencies[$currency]['value'], $xtPrice->get_decimal_places($currency));
        if ($_SESSION['customers_status']['customers_status_show_price_tax'] == '0') {
          $article[$i]['netPrice'] = number_format($shipping_cost, 2, '.', '');
        } else {
          $shipping_cost = xtc_add_tax($shipping_cost, $shipping_tax_rate);
          $article[$i]['grossPrice'] = number_format($shipping_cost, 2, '.', '');
        }
        if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 0) {
          $article[$i]['tax'] = number_format(0, 2, '.', '');
        } else {
          $article[$i]['tax'] = number_format($shipping_tax_rate, 2, '.', '');
        }
      } else {
        $i = $i - 1;
      }
      if ($_SESSION['discount_value'] != 0) {
        $discount_tax_rate = xtc_get_tax_rate('1');
        $i = $i + 1;
        $article[$i]['number'] = 'discount';
        $article[$i]['name'] = $_SESSION['discount_name'];
        $article[$i]['type'] = 'voucher';
        $article[$i]['quantity'] = 1;
        if ($_SESSION['customers_status']['customers_status_show_price_tax'] == '0') {
          $article[$i]['netPrice'] = number_format($_SESSION['discount_value'], 2, '.', '');
        } else {
          $article[$i]['grossPrice'] = number_format($_SESSION['discount_value'], 2, '.', '');
        }
        if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 0) {
          $article[$i]['tax'] = number_format(0, 2, '.', '');
        } else {
          $article[$i]['tax'] = number_format($discount_tax_rate, 2, '.', '');
        }
      }
      if ($_SESSION['voucher_value'] != 0) {
        $i = $i + 1;
        $article[$i]['number'] = 'voucher';
        $article[$i]['name'] = $_SESSION['voucher_name'];
        $article[$i]['type'] = 'voucher';
        $article[$i]['quantity'] = 1;
        if ($_SESSION['customers_status']['customers_status_show_price_tax'] == '0') {
          $article[$i]['netPrice'] = number_format($_SESSION['voucher_value'], 2, '.', '');
        } else {
          $article[$i]['grossPrice'] = number_format($_SESSION['voucher_value'], 2, '.', '');
        }
        $article[$i]['tax'] = number_format(0, 2, '.', '');
      }
      if ($_SESSION['coupon_value'] != 0) {
        if (MODULE_ORDER_TOTAL_COUPON_TAX_CLASS) {
          $coupon_tax_rate = xtc_get_tax_rate(MODULE_ORDER_TOTAL_COUPON_TAX_CLASS);
        } else {
          $coupon_tax_rate = xtc_get_tax_rate('1');
        }
        $i = $i + 1;
        $article[$i]['number'] = 'coupon';
        $article[$i]['name'] = $_SESSION['coupon_name'];
        $article[$i]['type'] = 'voucher';
        $article[$i]['quantity'] = 1;
        if ($_SESSION['customers_status']['customers_status_show_price_tax'] == '0') {
          $article[$i]['netPrice'] = number_format($_SESSION['coupon_value'], 2, '.', '');
        } else {
          $article[$i]['grossPrice'] = number_format($_SESSION['coupon_value'], 2, '.', '');
        }
        if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
          $article[$i]['tax'] = number_format($coupon_tax_rate, 2, '.', '');
          $coupon_tax = $_SESSION['coupon_value'] * $coupon_tax_rate / 100;
        } elseif ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 0) {
          $article[$i]['tax'] = number_format(0, 2, '.', '');
          $coupon_tax = 0;
        } else {
          $article[$i]['tax'] = number_format($coupon_tax_rate, 2, '.', '');
          $coupon_tax = $_SESSION['coupon_value'] - ($_SESSION['coupon_value'] / (1 + $coupon_tax_rate / 100));
        }
      }
      if ($_SESSION['schg_value'] != 0) {
        $schg_tax_rate = xtc_get_tax_rate(MODULE_PAYMENT_BILLSAFE_2_SCHGTAX);
        $i = $i + 1;
        $article[$i]['number'] = 'surcharge';
        $article[$i]['name'] = $_SESSION['schg_name'];
        $article[$i]['type'] = 'handling';
        $article[$i]['quantity'] = 1;
        if ($_SESSION['customers_status']['customers_status_show_price_tax'] == '0') {
          $article[$i]['netPrice'] = number_format(($_SESSION['schg_value']), 2, '.', '');
        } else {
          $article[$i]['grossPrice'] = number_format(($_SESSION['schg_value']), 2, '.', '');
        }
        if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
          $article[$i]['tax'] = number_format($schg_tax_rate, 2, '.', '');
          $schg_tax = $_SESSION['schg_value'] * $schg_tax_rate / 100;
        } elseif ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 0) {
          $article[$i]['tax'] = number_format(0, 2, '.', '');
          $schg_tax = 0;
        } else {
          $article[$i]['tax'] = number_format($schg_tax_rate, 2, '.', '');
          $schg_tax = $_SESSION['schg_value'] - ($_SESSION['schg_value'] / (1 + $schg_tax_rate / 100));
        }
      }
      if ($_SESSION['lofee_value'] != 0) {
        $lofee_tax_rate = xtc_get_tax_rate(MODULE_ORDER_TOTAL_LOWORDERFEE_TAX_CLASS);
        $i = $i + 1;
        $article[$i]['number'] = 'surcharge';
        $article[$i]['name'] = $_SESSION['lofee_name'];
        $article[$i]['type'] = 'goods';
        $article[$i]['quantity'] = 1;
        if ($_SESSION['customers_status']['customers_status_show_price_tax'] == '0') {
          $article[$i]['netPrice'] = number_format(($_SESSION['lofee_value']), 2, '.', '');
        } else {
          $article[$i]['grossPrice'] = number_format(($_SESSION['lofee_value']), 2, '.', '');
        }
        if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 0) {
          $article[$i]['tax'] = number_format(0, 2, '.', '');
          $lofee_tax = 0;
        } else {
          $article[$i]['tax'] = number_format($lofee_tax_rate, 2, '.', '');
          $lofee_tax = $_SESSION['lofee_value'] - ($_SESSION['lofee_value'] / (1 + $lofee_tax_rate / 100));
        }
      }

      $customer_query = xtc_db_query('SELECT customers_gender, DATE_FORMAT(customers_dob, "%Y-%m-%d") AS customers_dob, customers_email_address, customers_telephone from '.TABLE_CUSTOMERS.' WHERE customers_id = "'.xtc_db_input($customer_id).'"');
      if (xtc_db_num_rows($customer_query)) $customer = xtc_db_fetch_array($customer_query);
      $shipping_tax = round(($order->info['shipping_cost'] / 100) * $shipping_tax_rate, 2);
      if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
        $total = $order->info['subtotal'] + $order->info['tax'] + $shipping_tax + $_SESSION['discount_value'] + $_SESSION['voucher_value'] + $_SESSION['coupon_value'] + $_SESSION['schg_value'] + $schg_tax + $coupon_tax + $_SESSION['lofee_value'];
      } else {
        $total = $order->info['subtotal'] + $_SESSION['discount_value'] + $_SESSION['voucher_value'] + $_SESSION['coupon_value'] + $_SESSION['schg_value'] + $_SESSION['lofee_value'];
      }
      $total = round($xtPrice->xtcCalculateCurrEx($total, $_SESSION['currency']), $xtPrice->get_decimal_places($_SESSION['currency']));
      $info_tax = $order->info['tax']; 
      $total = number_format(round(($total + $shipping_cost), $xtPrice->get_decimal_places($currency)), 2, '.', '');
      if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 0) {
        $info_tax = number_format(0, 2, '.', '');
      } else {
        $info_tax = number_format(round(($info_tax + $shipping_tax + $schg_tax + $coupon_tax + $lofee_tax), $xtPrice->get_decimal_places($currency)), 2, '.', '');
      }
      $url_image = $this->checkLogoURL(MODULE_PAYMENT_BILLSAFE_2_SHOP_LOGO_URL);
      if ($customer['customers_gender'] != '') {
        $params = array('order_amount' => round($total, $xtPrice->get_decimal_places($currency)), 'order_taxAmount' => number_format($info_tax, 2, '.', ''), 'order_currencyCode' => $currency, 'customer' => array('id' => $customer_id, 'gender' => $customer['customers_gender'], 'company' => $order->delivery['company'], 'firstname' => $order->delivery['firstname'], 'lastname' => $order->delivery['lastname'], 'street' => $order->delivery['street_address'], 'postcode' => $order->delivery['postcode'], 'city' => $order->delivery['city'], 'country' => $order->delivery['country']['iso_code_2'], 'email' => $customer['customers_email_address'], 'phone' => $customer['customers_telephone']), 'product' => 'invoice', 'url_return' => xtc_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL'), 'url_cancel' => xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'), 'url_image' => $url_image, 'articleList' => $article);
      } else {
        $params = array('order_amount' => round($total, $xtPrice->get_decimal_places($currency)), 'order_taxAmount' => number_format($info_tax, 2, '.', ''), 'order_currencyCode' => $currency, 'customer' => array('id' => $customer_id, 'company' => $order->delivery['company'], 'firstname' => $order->delivery['firstname'], 'lastname' => $order->delivery['lastname'], 'street' => $order->delivery['street_address'], 'postcode' => $order->delivery['postcode'], 'city' => $order->delivery['city'], 'country' => $order->delivery['country']['iso_code_2'], 'email' => $customer['customers_email_address'], 'phone' => $customer['customers_telephone']), 'product' => 'invoice', 'url_return' => xtc_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL'), 'url_cancel' => xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'), 'url_image' => $url_image, 'articleList' => $article);
      }
      if (!empty($customer['customers_dob']) && $customer['customers_dob'] != '0000-00-00') $params['customer']['dateOfBirth'] = $customer['customers_dob'];
      $response = $bs->callMethod('prepareOrder', $params);
      if ($response->ack == 'OK') {
        if (MODULE_PAYMENT_BILLSAFE_2_LAYER == 'True') {
          $bs->callPaymentLayer($response->token);
        } else {
          $bs->redirectToPaymentGateway($response->token);
        }
      } else {
        $message = $this->get_error_message($response);
        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code.'&error_message='.stripslashes(urlencode(html_entity_decode($message))), 'SSL'));
      }
    } else {
      $token = $_GET['token'];
      $check_query = xtc_db_query('SELECT token FROM billsafe_orders_2 WHERE token = "'.$token.'"');
      $check_token = xtc_db_num_rows($check_query);
      if ($check_token == 1) xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code.'&error_message='.stripslashes(urlencode(html_entity_decode(MODULE_PAYMENT_BILLSAFE_2_ERROR_MESSAGE_COMMON))), 'SSL'));
      require_once (DIR_FS_CATALOG.'includes/external/billsafe/classes/billsafe_2/billsafe_2.php');//DokuMan - 2012-06-19 - move billsafe to external directory
      $bs = new Billsafe_Sdk(DIR_FS_CATALOG.'includes/external/billsafe/classes/billsafe_2/ini.php');//DokuMan - 2012-06-19 - move billsafe to external directory
      if($_SESSION['language_charset'] == 'iso-8859-1' || $_SESSION['language_charset'] == 'iso-8859-15') {
        $bs->setUtf8Mode(false);
      } else {
        $bs->setUtf8Mode(true);
      }
      if (MODULE_PAYMENT_BILLSAFE_2_SERVER == 'Live') {
        $bs->setMode("LIVE");
      } else {
        $bs->setMode("SANDBOX");
      }
      $bs->setCredentials(array('merchantId' => MODULE_PAYMENT_BILLSAFE_2_MERCHANT_ID, 'merchantLicenseSandbox' => MODULE_PAYMENT_BILLSAFE_2_MERCHANT_LICENSE, 'merchantLicenseLive' => MODULE_PAYMENT_BILLSAFE_2_MERCHANT_LICENSE, 'applicationSignature' => $this->applicationSignature, 'applicationVersion' => $this->application_version));
      $params = array('token' => $token);
      $this->response = $bs->callMethod('getTransactionResult', $params);
      if ($this->response->ack == 'OK' && $this->response->status == 'ACCEPTED') {
      } else {
        $_SESSION['billsafe_status'] = 'declined';
        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code.'&error_message='.stripslashes(urlencode(html_entity_decode(MODULE_PAYMENT_BILLSAFE_2_ERROR_MESSAGE_COMMON))), 'SSL'));
      }
    }
    return true;
  }

  function after_process() {
    global $order, $insert_id;
    unset ($_SESSION['discount_value']);
    unset ($_SESSION['discount_name']);
    unset ($_SESSION['voucher_value']);
    unset ($_SESSION['voucher_name']);
    unset ($_SESSION['coupon_value']);
    unset ($_SESSION['coupon_name']);
    unset ($_SESSION['schg_value']);
    unset ($_SESSION['schg_name']);
    unset ($_SESSION['lofee_value']);
    unset ($_SESSION['lofee_name']);
    $token = $_GET['token'];
    if ($this->response->ack == 'OK' && $this->response->status == 'ACCEPTED') {
      if ($this->order_status) xtc_db_query('UPDATE '.TABLE_ORDERS.' SET orders_status = "'.xtc_db_input($this->order_status).'" WHERE orders_id = "'.xtc_db_input($insert_id).'"');
      $customer = $this->response->customer;
      xtc_db_query('INSERT INTO billsafe_orders_2 (id, orderid, transactionid, token, billsafeStatus, type, paymentStatus,date) VALUES (NULL,"'.xtc_db_input($insert_id).'", "'.xtc_db_input($this->response->transactionId).'", "'.$token.'", "'.xtc_db_input($this->response->status).'", "invoice", NULL, "'.date('Y-m-d H:i:s').'")');
      $resultQuery = xtc_db_query('SELECT id FROM billsafe_orders_2 WHERE transactionId = "'.xtc_db_input($this->response->transactionId).'"');
      $result = xtc_db_fetch_array($resultQuery);
      xtc_db_query('INSERT INTO billsafe_orders_user_2 (id, bsorders_id, gender, company, firstname, lastname, street, housenumber, postcode, city, country, dateofbirth, email, phone) VALUES (NULL, "'.xtc_db_input($result['id']).'", "'.xtc_db_input($customer->gender).'", "'.xtc_db_input($customer->firstname).'", "'.xtc_db_input($customer->company).'", "'.xtc_db_input($customer->lastname).'", "'.xtc_db_input($customer->street).'", "'.xtc_db_input($customer->housenumber).'", "'.xtc_db_input($customer->postcode).'", "'.xtc_db_input($customer->city).'", "'.xtc_db_input($customer->country).'", "0000-00-00", "'.xtc_db_input($customer->email).'", "'.xtc_db_input($customer->phone).'")');
      $bs = new Billsafe_Sdk(DIR_FS_CATALOG.'includes/external/billsafe/classes/billsafe_2/ini.php');//DokuMan - 2012-06-19 - move billsafe to external directory
      if($_SESSION['language_charset'] == 'iso-8859-1' || $_SESSION['language_charset'] == 'iso-8859-15') {
        $bs->setUtf8Mode(false);
      } else {
        $bs->setUtf8Mode(true);
      }
      if (MODULE_PAYMENT_BILLSAFE_2_SERVER == 'Live') {
        $bs->setMode("LIVE");
      } else {
        $bs->setMode("SANDBOX");
      }
      $bs->setCredentials(array('merchantId' => MODULE_PAYMENT_BILLSAFE_2_MERCHANT_ID, 'merchantLicenseSandbox' => MODULE_PAYMENT_BILLSAFE_2_MERCHANT_LICENSE, 'merchantLicenseLive' => MODULE_PAYMENT_BILLSAFE_2_MERCHANT_LICENSE, 'applicationSignature' => $this->applicationSignature, 'applicationVersion' => $this->application_version));
      $params = array('transactionId' => $this->response->transactionId);
      $response = $bs->callMethod('getArticleList', $params);
      $article = $response->articleList;
      for ($i = 0, $n = sizeof($article); $i < $n; $i++) {
        for ($ii = 0, $nn = intval($article[$i]->quantity); $ii < $nn; $ii++) {
          $article[$i]->name = str_replace("\"", "\\\"", $article[$i]->name);
          $article[$i]->name = str_replace("\'", "\\\'", $article[$i]->name);
          xtc_db_query ('INSERT INTO billsafe_orders_details_2 (ordernumber, product_id, articletype, articlenumber, articlename, articleprice, articletax, bsorders_id) values("'.xtc_db_input($insert_id).'", "'.xtc_db_input($order->products[$i]['id']).'", "'.xtc_db_input($article[$i]->type).'", "'.xtc_db_input($article[$i]->number).'", "'.xtc_db_input($article[$i]->name).'", "'.xtc_db_input($article[$i]->grossPrice).'", "'.xtc_db_input($article[$i]->tax).'", "'.xtc_db_input($result['id']).'")');
        }
      }
      $comments = MODULE_PAYMENT_BILLSAFE_2_STATUS_TEXT.': '.$this->response->status.'; '.MODULE_PAYMENT_BILLSAFE_2_TRANSACTIONID.': '.$this->response->transactionId;
      xtc_db_query ('INSERT INTO orders_status_history (orders_status_history_id, orders_id, orders_status_id, date_added, customer_notified, comments) VALUES (NULL, "'.xtc_db_input($insert_id).'", "1", "'.date('Y-m-d H:i:s').'", "0", "'.xtc_db_input($comments).'")');
      $params = array('transactionId' => xtc_db_input($this->response->transactionId), 'orderNumber' => xtc_db_input($insert_id));
      $response = $bs->callMethod('setOrderNumber', $params);
      if ($response->ack == 'OK') {
      } else {
      }
    } else {
      $_SESSION['billsafe_status'] = 'declined';
      $message = $this->get_error_message($response);
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code.'&error_message='.stripslashes(urlencode(html_entity_decode($message))), 'SSL'));
    }
    return false;
  }

  function get_error() {
    return false;
  }

  function check() {
    if (!isset($this->_check)) {
      $check_query = xtc_db_query('SELECT configuration_value FROM '.TABLE_CONFIGURATION.' WHERE configuration_key = "MODULE_PAYMENT_BILLSAFE_2_STATUS"');
      $this->_check = xtc_db_num_rows($check_query);
    }
    return $this->_check;
  }

  function install() {
    $check_query = xtc_db_query('SHOW TABLES LIKE "billsafe_orders_2"');
    if (xtc_db_num_rows($check_query) == 0) {
      xtc_db_query('CREATE TABLE billsafe_orders_2 (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, orderid VARCHAR(255) NOT NULL, transactionid VARCHAR(255) NOT NULL, billsafeStatus VARCHAR(255) NOT NULL, type VARCHAR(64) NOT NULL, token VARCHAR(255) NOT NULL, date TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, paymentStatus VARCHAR(255) NULL DEFAULT NULL) ENGINE = MYISAM;');
    } elseif (xtc_db_num_rows($check_query) != 0) {
      $check_query = xtc_db_query('SHOW COLUMNS FROM billsafe_orders_2 like "type"');
      if (xtc_db_num_rows($check_query) == 0) {
        xtc_db_query('ALTER TABLE billsafe_orders_2 ADD type VARCHAR(64) NOT NULL AFTER billsafeStatus');
      }
    }
    $check_query = xtc_db_query('SHOW TABLES LIKE "billsafe_orders_details_2"');
    if (xtc_db_num_rows($check_query) == 0) {
      xtc_db_query('CREATE TABLE billsafe_orders_details_2 (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, ordernumber VARCHAR(255) NOT NULL, product_id VARCHAR(255) NOT NULL, articletype VARCHAR(64) NOT NULL, articlenumber VARCHAR(255) NOT NULL, articlename VARCHAR(255) NOT NULL, articleprice VARCHAR(64) NOT NULL, articletax VARCHAR(64) NOT NULL, bsorders_id INT NOT NULL, shipped INT NOT NULL DEFAULT "0", storno INT NOT NULL DEFAULT "0", retoure INT NOT NULL DEFAULT "0", pause INT NOT NULL DEFAULT "0") ENGINE = MYISAM;');
    } elseif (xtc_db_num_rows($check_query) != 0) {
      $check_query = xtc_db_query('SHOW COLUMNS FROM billsafe_orders_details_2 like "pause"');
      if (xtc_db_num_rows($check_query) == 0) xtc_db_query('ALTER TABLE billsafe_orders_details_2 ADD pause INT NOT NULL DEFAULT "0"');
      $check_query = xtc_db_query('SHOW COLUMNS FROM billsafe_orders_details_2 like "product_id"');
      if (xtc_db_num_rows($check_query) == 0) xtc_db_query('ALTER TABLE billsafe_orders_details_2 ADD product_id VARCHAR(64) NOT NULL AFTER ordernumber');
      $check_query = xtc_db_query('SHOW COLUMNS FROM billsafe_orders_details_2 like "articletype"');
      if (xtc_db_num_rows($check_query) == 0) xtc_db_query('ALTER TABLE billsafe_orders_details_2 ADD articletype VARCHAR(64) NOT NULL AFTER product_id');
      $check_query = xtc_db_query('SHOW COLUMNS FROM billsafe_orders_details_2 like "articlename"');
      if (xtc_db_num_rows($check_query) == 0) xtc_db_query('ALTER TABLE billsafe_orders_details_2 ADD articlename VARCHAR(255) NOT NULL AFTER articlenumber');
      $check_query = xtc_db_query('SHOW COLUMNS FROM billsafe_orders_details_2 like "articleprice"');
      if (xtc_db_num_rows($check_query) == 0) xtc_db_query('ALTER TABLE billsafe_orders_details_2 ADD articleprice VARCHAR(64) NOT NULL AFTER articlename');
      $check_query = xtc_db_query('SHOW COLUMNS FROM billsafe_orders_details_2 like "articletax"');
      if (xtc_db_num_rows($check_query) == 0) xtc_db_query('ALTER TABLE billsafe_orders_details_2 ADD articletax VARCHAR(64) NOT NULL AFTER articleprice');
    }
    $check_query = xtc_db_query('SHOW TABLES LIKE "billsafe_transactions_2"');
    if (xtc_db_num_rows($check_query) == 0) {
      xtc_db_query('CREATE TABLE billsafe_transactions_2 (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, ordernumber VARCHAR(255) NOT NULL, articlenumber VARCHAR(255) NOT NULL, transactionmethod VARCHAR(255) NOT NULL, bsorder_id INT NOT NULL, bsordersdetails_id INT(11) NOT NULL, date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE = MYISAM;');
    }
    $check_query = xtc_db_query('SHOW TABLES LIKE "billsafe_orders_user_2"');
    if (xtc_db_num_rows($check_query) == 0) {
      xtc_db_query('CREATE TABLE billsafe_orders_user_2 (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, bsorders_id INT NOT NULL, gender VARCHAR(255) NOT NULL, company VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, street VARCHAR(255) NOT NULL, housenumber VARCHAR(255) NOT NULL, postcode VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, country VARCHAR(255) NOT NULL, dateofbirth VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL) ENGINE = MYISAM;');
    } elseif (xtc_db_num_rows($check_query) != 0) {
      $check_query = xtc_db_query('SHOW COLUMNS FROM billsafe_orders_user_2 like "company"');
      if (xtc_db_num_rows($check_query) == 0) {
        xtc_db_query('ALTER TABLE billsafe_orders_user_2 ADD company VARCHAR(255) NOT NULL AFTER gender');
      }
    }
    $check_query = xtc_db_query('SHOW COLUMNS FROM admin_access like "billsafe_orders_2"');
    if (xtc_db_num_rows($check_query) == 0) {
      xtc_db_query('ALTER TABLE admin_access ADD billsafe_orders_2 INT(1) NOT NULL DEFAULT "0"');
      xtc_db_query('UPDATE admin_access SET billsafe_orders_2 = "1" WHERE customers_id = "1" OR customers_id = "groups"');
    }
    $check_query = xtc_db_query('SHOW COLUMNS FROM admin_access like "billsafe_print_order_2"');
    if (xtc_db_num_rows($check_query) == 0) {
      xtc_db_query('ALTER TABLE admin_access ADD billsafe_print_order_2 INT(1) NOT NULL DEFAULT "0"');
      xtc_db_query('UPDATE admin_access SET billsafe_print_order_2 = "1" WHERE customers_id = "1" OR customers_id = "groups"');
    }
    $logo_url = HTTPS_CATALOG_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/top_logo.jpg';
    $billsafe_logo = 'https://images.billsafe.de/image/image/id/2120806d6053';
    $check_query = xtc_db_query('SHOW COLUMNS FROM '.TABLE_CONFIGURATION.' like "MODULE_PAYMENT_BILLSAFE_2_STATUS"');
    if (xtc_db_num_rows($check_query) == 0) xtc_db_query('INSERT INTO '.TABLE_CONFIGURATION.' (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ("MODULE_PAYMENT_BILLSAFE_2_STATUS", "False", "6", "1", "xtc_cfg_select_option(array(\'True\', \'False\'), ", now())');
    $check_query = xtc_db_query('SHOW COLUMNS FROM '.TABLE_CONFIGURATION.' like "MODULE_PAYMENT_BILLSAFE_2_LAYER"');
    if (xtc_db_num_rows($check_query) == 0) xtc_db_query('INSERT INTO '.TABLE_CONFIGURATION.' (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ("MODULE_PAYMENT_BILLSAFE_2_LAYER", "True", "6", "1", "xtc_cfg_select_option(array(\'True\', \'False\'), ", now())');
    $check_query = xtc_db_query('SHOW COLUMNS FROM '.TABLE_CONFIGURATION.' like "MODULE_PAYMENT_BILLSAFE_2_LOG"');
    if (xtc_db_num_rows($check_query) == 0) xtc_db_query('INSERT INTO '.TABLE_CONFIGURATION.' (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ("MODULE_PAYMENT_BILLSAFE_2_LOG", "False", "6", "1", "xtc_cfg_select_option(array(\'True\', \'False\'), ", now())');
    $check_query = xtc_db_query('SHOW COLUMNS FROM '.TABLE_CONFIGURATION.' like "MODULE_PAYMENT_BILLSAFE_2_LOG_TYPE"');
    if (xtc_db_num_rows($check_query) == 0) xtc_db_query('INSERT INTO '.TABLE_CONFIGURATION.' (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ("MODULE_PAYMENT_BILLSAFE_2_LOG_TYPE", "False", "6", "1", "xtc_cfg_select_option(array(\'Echo\', \'Mail\', \'File\'), ", now())');
    $check_query = xtc_db_query('SHOW COLUMNS FROM '.TABLE_CONFIGURATION.' like "MODULE_PAYMENT_BILLSAFE_2_LOG_ADDR"');
    if (xtc_db_num_rows($check_query) == 0) xtc_db_query('INSERT INTO '.TABLE_CONFIGURATION.' (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ("MODULE_PAYMENT_BILLSAFE_2_LOG_ADDR", "", "6", "0", now())');
    $check_query = xtc_db_query('SHOW COLUMNS FROM '.TABLE_CONFIGURATION.' like "MODULE_PAYMENT_BILLSAFE_2_MERCHANT_ID"');
    if (xtc_db_num_rows($check_query) == 0) xtc_db_query('INSERT INTO '.TABLE_CONFIGURATION.' (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ("MODULE_PAYMENT_BILLSAFE_2_MERCHANT_ID", "", "6", "0", now())');
    $check_query = xtc_db_query('SHOW COLUMNS FROM '.TABLE_CONFIGURATION.' like "MODULE_PAYMENT_BILLSAFE_2_MERCHANT_LICENSE"');
    if (xtc_db_num_rows($check_query) == 0) xtc_db_query('INSERT INTO '.TABLE_CONFIGURATION.' (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ("MODULE_PAYMENT_BILLSAFE_2_MERCHANT_LICENSE", "", "6", "0", now())');
    $check_query = xtc_db_query('SHOW COLUMNS FROM '.TABLE_CONFIGURATION.' like "MODULE_PAYMENT_BILLSAFE_2_MIN_ORDER"');
    if (xtc_db_num_rows($check_query) == 0) xtc_db_query('INSERT INTO '.TABLE_CONFIGURATION.' (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ("MODULE_PAYMENT_BILLSAFE_2_MIN_ORDER", "0", "6", "0", now())');
    $check_query = xtc_db_query('SHOW COLUMNS FROM '.TABLE_CONFIGURATION.' like "MODULE_PAYMENT_BILLSAFE_2_MAX_ORDER"');
    if (xtc_db_num_rows($check_query) == 0) xtc_db_query('INSERT INTO '.TABLE_CONFIGURATION.' (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ("MODULE_PAYMENT_BILLSAFE_2_MAX_ORDER", "500", "6", "0", now())');
    $check_query = xtc_db_query('SHOW COLUMNS FROM '.TABLE_CONFIGURATION.' like "MODULE_PAYMENT_BILLSAFE_2_SCHG"');
    if (xtc_db_num_rows($check_query) == 0) xtc_db_query('INSERT INTO '.TABLE_CONFIGURATION.' (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ("MODULE_PAYMENT_BILLSAFE_2_SCHG", "", "6", "0", now())');
    $check_query = xtc_db_query('SHOW COLUMNS FROM '.TABLE_CONFIGURATION.' like "MODULE_PAYMENT_BILLSAFE_2_SCHGTAX"');
    if (xtc_db_num_rows($check_query) == 0) xtc_db_query('INSERT INTO '.TABLE_CONFIGURATION.' (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ("MODULE_PAYMENT_BILLSAFE_2_SCHGTAX", "1", "6", "0", "xtc_get_tax_class_title", "xtc_cfg_pull_down_tax_classes(", now())');
    $check_query = xtc_db_query('SHOW COLUMNS FROM '.TABLE_CONFIGURATION.' like "MODULE_PAYMENT_BILLSAFE_2_SHOP_LOGO_URL"');
    if (xtc_db_num_rows($check_query) == 0) xtc_db_query('INSERT INTO '.TABLE_CONFIGURATION.' (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ("MODULE_PAYMENT_BILLSAFE_2_SHOP_LOGO_URL", "'.$logo_url.'", "6", "0", now())');
    $check_query = xtc_db_query('SHOW COLUMNS FROM '.TABLE_CONFIGURATION.' like "MODULE_PAYMENT_BILLSAFE_2_BILLSAFE_LOGO_URL"');
    if (xtc_db_num_rows($check_query) == 0) xtc_db_query('INSERT INTO '.TABLE_CONFIGURATION.' (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ("MODULE_PAYMENT_BILLSAFE_2_BILLSAFE_LOGO_URL", "'.$billsafe_logo.'", "6", "0", now())');
    $check_query = xtc_db_query('SHOW COLUMNS FROM '.TABLE_CONFIGURATION.' like "MODULE_PAYMENT_BILLSAFE_2_SERVER"');
    if (xtc_db_num_rows($check_query) == 0) xtc_db_query('INSERT INTO '.TABLE_CONFIGURATION.' (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ("MODULE_PAYMENT_BILLSAFE_2_SERVER", "Sandbox", "6", "1", "xtc_cfg_select_option(array(\'Live\', \'Sandbox\'), ", now())');
    $check_query = xtc_db_query('SHOW COLUMNS FROM '.TABLE_CONFIGURATION.' like "MODULE_PAYMENT_BILLSAFE_2_ZONE"');
    if (xtc_db_num_rows($check_query) == 0) xtc_db_query('INSERT INTO '.TABLE_CONFIGURATION.' (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ("MODULE_PAYMENT_BILLSAFE_2_ZONE", "0", "6", "2", "xtc_get_zone_class_title", "xtc_cfg_pull_down_zone_classes(", now())');
    $check_query = xtc_db_query('SHOW COLUMNS FROM '.TABLE_CONFIGURATION.' like "MODULE_PAYMENT_BILLSAFE_2_ORDER_STATUS_ID"');
    if (xtc_db_num_rows($check_query) == 0) xtc_db_query('INSERT INTO '.TABLE_CONFIGURATION.' (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ("MODULE_PAYMENT_BILLSAFE_2_ORDER_STATUS_ID", "0", "6", "0", "xtc_cfg_pull_down_order_statuses(", "xtc_get_order_status_name", now())');
    $check_query = xtc_db_query('SHOW COLUMNS FROM '.TABLE_CONFIGURATION.' like "MODULE_PAYMENT_BILLSAFE_2_SORT_ORDER"');
    if (xtc_db_num_rows($check_query) == 0) xtc_db_query('INSERT INTO '.TABLE_CONFIGURATION.' (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ("MODULE_PAYMENT_BILLSAFE_2_SORT_ORDER", "0", "6", "0", now())');
    $check_query = xtc_db_query('SHOW COLUMNS FROM '.TABLE_CONFIGURATION.' like "MODULE_PAYMENT_BILLSAFE_2_ALLOWED"');
    if (xtc_db_num_rows($check_query) == 0) xtc_db_query('INSERT INTO '.TABLE_CONFIGURATION.' (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ("MODULE_PAYMENT_BILLSAFE_2_ALLOWED", "DE", "6", "0", now())');
  }

  function remove() {
    xtc_db_query('DELETE FROM '.TABLE_CONFIGURATION.' WHERE configuration_key in ("'.implode('", "', $this->keys()).'")');
  }

  function keys() {
    return array('MODULE_PAYMENT_BILLSAFE_2_STATUS', 'MODULE_PAYMENT_BILLSAFE_2_LAYER', 'MODULE_PAYMENT_BILLSAFE_2_LOG', 'MODULE_PAYMENT_BILLSAFE_2_LOG_TYPE', 'MODULE_PAYMENT_BILLSAFE_2_LOG_ADDR', 'MODULE_PAYMENT_BILLSAFE_2_MERCHANT_ID', 'MODULE_PAYMENT_BILLSAFE_2_MERCHANT_LICENSE', 'MODULE_PAYMENT_BILLSAFE_2_MIN_ORDER', 'MODULE_PAYMENT_BILLSAFE_2_MAX_ORDER', 'MODULE_PAYMENT_BILLSAFE_2_SCHG', 'MODULE_PAYMENT_BILLSAFE_2_SCHGTAX', 'MODULE_PAYMENT_BILLSAFE_2_SHOP_LOGO_URL', 'MODULE_PAYMENT_BILLSAFE_2_BILLSAFE_LOGO_URL', 'MODULE_PAYMENT_BILLSAFE_2_SERVER', 'MODULE_PAYMENT_BILLSAFE_2_ZONE', 'MODULE_PAYMENT_BILLSAFE_2_ORDER_STATUS_ID', 'MODULE_PAYMENT_BILLSAFE_2_SORT_ORDER', 'MODULE_PAYMENT_BILLSAFE_2_ALLOWED');
  }

  public function checkLogoURL($logoURL) {
    if ($logoURL != '' && strpos($logoURL, 'https://') === 0) {
      return $logoURL;
    } else {
      return '';
    }
  }

  public function checkBillSAFELogoURL($logoURL) {
    if ($logoURL != '' ) {
      if (ENABLE_SSL == true) {
        if (strpos($logoURL, 'https://') === 0) {
          return $logoURL;
        } else {
          return 'https://images.billsafe.de/image/image/id/2120806d6053';
        }
      } else {
        return $logoURL;
      }
    } else {
      return 'https://images.billsafe.de/image/image/id/04105000caae';
    }
  }

  function get_shipping_tax_rate($shipping_id) {
    $check_query = xtc_db_query('SELECT configuration_value FROM '.TABLE_CONFIGURATION.' WHERE configuration_key = "MODULE_SHIPPING_'.$shipping_id.'_TAX_CLASS"');
    $configuration = xtc_db_fetch_array($check_query);
    $tax_class_id = $configuration['configuration_value'];
    $shipping_tax_rate = xtc_get_tax_rate($tax_class_id);
    return $shipping_tax_rate;
  }

  function get_error_message($response) {
    foreach ($response->errorList as $error) {
      switch($error->code) {
        case '101':
          $message .= MODULE_PAYMENT_BILLSAFE_2_ERROR_MESSAGE_101;
          break;
        case '102':
          $message .= MODULE_PAYMENT_BILLSAFE_2_ERROR_MESSAGE_102;
          break;
        case '215':
          $message .= MODULE_PAYMENT_BILLSAFE_2_ERROR_MESSAGE_215;
          break;
        case '216':
          $message .= MODULE_PAYMENT_BILLSAFE_2_ERROR_MESSAGE_216;
          break;
        default:
          $message .= MODULE_PAYMENT_BILLSAFE_2_ERROR_MESSAGE_COMMON;
          break;
      }
    }
    return $message;
  }
}

?>
