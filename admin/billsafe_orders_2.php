<?php
/* -----------------------------------------------------------------------------------------
   $Id: billsafe_orders_2.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   Copyright (c) 2013 PayPal SE and Bernd Blazynski

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

/*
* id = billsafe_orders_2.php
* location = /admin
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

  $order_id = $_GET['oID'];
  $post_url = 'billsafe_orders_2.php';
  $post_param = 'oID='.$order_id;
  
  require ('includes/application_top.php');
  $language= $_SESSION['language'];

  require (DIR_FS_CATALOG.'lang/'.$language.'/modules/payment/billsafe_2.php');
  require (DIR_WS_CLASSES.'order.php');

  $order = new order($order_id);

  require (DIR_WS_CLASSES.'currencies.php');
  $currencies = new currencies();

  require_once (DIR_FS_CATALOG.'includes/external/billsafe/classes/billsafe_2/billsafe_2.php'); // DokuMan - 2012-06-19 - move billsafe to external directory
  require (DIR_FS_CATALOG.'includes/external/billsafe/classes/billsafe_2/ini.php'); // DokuMan - 2012-06-19 - move billsafe to external directory

  $bs = new Billsafe_Sdk(DIR_FS_CATALOG.'includes/external/billsafe/classes/billsafe_2/ini.php'); // DokuMan - 2012-06-19 - move billsafe to external directory

  if($_SESSION['language_charset'] == 'iso-8859-1' || $_SESSION['language_charset'] == 'iso-8859-15') {
    $bs->setUtf8Mode(false);
  } else {
    $bs->setUtf8Mode(true);
  } 
  if (MODULE_PAYMENT_BILLSAFE_2_SERVER == 'Live') {
    $bs->setMode('LIVE');
  } else {
    $bs->setMode('SANDBOX');
  }

  if (MODULE_PAYMENT_BILLSAFE_2_LOG == 'True') {
    if (MODULE_PAYMENT_BILLSAFE_2_LOG_TYPE == 'Echo') {
      require_once DIR_FS_CATALOG.'includes/external/billsafe/classes/billsafe_2/LoggerEcho.php'; // DokuMan - 2012-06-19 - move billsafe to external directory
      $bs->setLogger(new Billsafe_LoggerEcho());
    } elseif (MODULE_PAYMENT_BILLSAFE_2_LOG_TYPE == 'Mail') {
      require_once DIR_FS_CATALOG.'includes/external/billsafe/classes/billsafe_2/LoggerMail.php'; // DokuMan - 2012-06-19 - move billsafe to external directory
      $bs->setLogger(new Billsafe_LoggerMail(MODULE_PAYMENT_BILLSAFE_2_LOG_ADDR));
    } elseif (MODULE_PAYMENT_BILLSAFE_2_LOG_TYPE == 'File') {
      require_once DIR_FS_CATALOG.'includes/external/billsafe/classes/billsafe_2/LoggerFile.php'; // DokuMan - 2012-06-19 - move billsafe to external directory
      $bs->setLogger(new Billsafe_LoggerFile(DIR_FS_CATALOG.'export/BillSAFE_'.date('YmdHis').'.log'));
    }
  }

  $bs->setCredentials(array('merchantId' => MODULE_PAYMENT_BILLSAFE_2_MERCHANT_ID, 'merchantLicenseSandbox' => MODULE_PAYMENT_BILLSAFE_2_MERCHANT_LICENSE, 'merchantLicenseLive' => MODULE_PAYMENT_BILLSAFE_2_MERCHANT_LICENSE, 'applicationSignature' => $ini['applicationSignature'], 'applicationVersion' => $ini['applicationVersion']));

  $orders_query = xtc_db_query('SELECT id, transactionid, billsafeStatus FROM billsafe_orders_2 WHERE orderid = "'.xtc_db_input($order_id).'"');
  if(xtc_db_num_rows($orders_query) == 0) {
    $messageBox = 'ERROR';
    $message = MODULE_PAYMENT_BILLSAFE_2_NO_ORDERID;
  }
  $billsafe_orders = xtc_db_fetch_array($orders_query);
  $currency = $order->info['currency'];
  $tax_class_array = array(array('id' => '0', 'text' => TEXT_NONE));
  $tax_class_query = xtc_db_query('SELECT tax_class_id, tax_class_title FROM '.xtc_db_input(TABLE_TAX_CLASS).' ORDER BY tax_class_title');
  while ($tax_class = xtc_db_fetch_array($tax_class_query)) {
    $tax_class_array[] = array('id' => $tax_class['tax_class_id'], 'text' => $tax_class['tax_class_title']);
  }
  $sql_order_id = 'SELECT id, articlenumber FROM billsafe_orders_details_2 WHERE ordernumber = "'.$order_id.'"';
  $articleList = array();
  $orderArticles = array();
  $orderIDArr = array();

  if (isset($_POST['reportShipmentFull'])) {
    $paramsShipment = array('transactionId' => $billsafe_orders['transactionid']);
    $responseShipment = $bs->callMethod('reportShipment', $paramsShipment);
    if ($responseShipment->ack == 'OK' && $full_shipment_count == 0) {
      insDB_trans($order, $order_id, 'reportShipmentFull', $billsafe_orders['id'], $sql_order_id);
      xtc_db_query('UPDATE billsafe_orders_details_2 SET shipped = 1 WHERE ordernumber = "'.xtc_db_input($order_id).'"');
      $messageBox = 'SUCCESS';
      $message = MODULE_PAYMENT_BILLSAFE_2_MESSAGE_FSHIPMENT;
    } else {
      if (is_array($responseShipment->errorList)) respError($responseShipment);
    }
  } elseif (isset($_POST['reportShipmentPart'])) {
    $orderIDArr = $_POST['shipped'];
    updDB_art_part('shipped', $orderIDArr);
    $queryLeftArticles = xtc_db_query('SELECT * FROM billsafe_orders_details_2 WHERE ordernumber = "'.xtc_db_input($order_id).'" AND articletype = "goods" AND shipped = 0 AND storno = 0');
    $countArticles = xtc_db_num_rows($queryLeftArticles);
    $counter = 0;
    if ($countArticles != 0) {
      foreach ($orderArticles as $key => $value) {
        $queryOrders = xtc_db_query('SELECT * FROM billsafe_orders_details_2 WHERE articlenumber = "'.xtc_db_input($key).'" AND ordernumber = "'.xtc_db_input($order_id).'"');
        $order = xtc_db_fetch_array($queryOrders);
        $articleList[$counter]['number'] = $order['articlenumber'];
        $articleList[$counter]['name'] = $order['articlename'];
        $articleList[$counter]['tax'] = number_format(floatval($order['articletax']), 2 , '.', '');
        $articleList[$counter]['grossPrice'] = number_format($order['articleprice'], 2, '.', '');
        $articleList[$counter]['type'] = $order['articletype'];
        $articleList[$counter]['quantity'] = (int)$value;
        $counter++;
      }
    } else {
      $queryLeftArticles = xtc_db_query('SELECT * FROM billsafe_orders_details_2 WHERE ordernumber = "'.xtc_db_input($order_id).'" AND shipped = 0 AND storno = 0 GROUP BY id');
      while ($leftArticles = xtc_db_fetch_array($queryLeftArticles)) {
        xtc_db_query('UPDATE billsafe_orders_details_2 SET shipped = 1 WHERE id = "'.xtc_db_input($leftArticles['id']).'"');
        xtc_db_query('INSERT INTO billsafe_transactions_2 (ordernumber, articlenumber, transactionmethod, bsorder_id, bsordersdetails_id, date) VALUES ("'.xtc_db_input($order_id).'", "'.xtc_db_input($leftArticles['articlenumber']).'", "reportShipmentPart", "'.xtc_db_input($billsafe_orders['id']).'", "'.xtc_db_input($leftArticles['id']).'", CURRENT_TIMESTAMP);');
      }
    }
    if (count($articleList) > 0) {
      $paramsPartialShipment = array('transactionId' => $billsafe_orders['transactionid'], 'articleList' => $articleList);
    } else {
      $paramsPartialShipment = array('transactionId' => $billsafe_orders['transactionid']);
    }
    $responsePartialShipment = $bs->callMethod('reportShipment', $paramsPartialShipment);
    getBillSAFEresp($responsePartialShipment, MODULE_PAYMENT_BILLSAFE_2_MESSAGE_PSHIPMENT, 'reportShipmentPart', 'shipped', $orderIDArr, $order_id, $billsafe_orders['id']);
  } elseif (isset($_POST['updateArticleListRetourePart'])) {
    $orderIDArr = $_POST['retoure'];
    updDB_art_part('retoure', $orderIDArr);
    $total = 0;
    $totalTax = 0;
    getTotalsOrderProducts($order_id);
    if ($total < 0) $orderIDArr = getTotal0($order_id, 'retoure');
    updDB_ot('retoure', $order_id);
    get_bsod($order_id);
    $total = 0;
    $totalTax = 0;
    getTotalsOrderProducts($order_id);
    $params['transactionId'] = $billsafe_orders['transactionid'];
    $params['articleList'] = $articleList;
    $params['order_amount'] = number_format($total, 2, '.', '');
    $params['order_taxAmount'] = number_format($totalTax, 2, '.', '');
    $params['order_currencyCode'] = $currency;
    $response = $bs->callMethod('updateArticleList', $params);
    getBillSAFEresp($response, MODULE_PAYMENT_BILLSAFE_2_MESSAGE_PRETOURE, 'updateArticleListRetourePart', 'retoure', $orderIDArr, $order_id, $billsafe_orders['id']);
  } elseif (isset($_POST['updateArticleListStornoPart'])) {
    $orderIDArr = $_POST['shipped'];
    updDB_art_part('storno', $orderIDArr);
    $total = 0;
    $totalTax = 0;
    getTotalsOrderProducts($order_id);
    if ($total < 0) $orderIDArr = getTotal0($order_id, 'storno');
    updDB_ot('storno', $order_id);
    get_bsod($order_id);
    $total = 0;
    $totalTax = 0;
    getTotalsOrderProducts($order_id);
    $params['transactionId'] = $billsafe_orders['transactionid'];
    $params['articleList'] = $articleList;
    $params['order_amount'] = number_format($total, 2, '.', '');
    $params['order_taxAmount'] = number_format($totalTax, 2, '.', '');
    $params['order_currencyCode'] = $currency;
    $response = $bs->callMethod('updateArticleList', $params);
    getBillSAFEresp($response, MODULE_PAYMENT_BILLSAFE_2_MESSAGE_PSTORNO, 'updateArticleListStornoPart', 'storno', $orderIDArr, $order_id, $billsafe_orders['id']);
  } elseif (isset($_POST['updateArticleListVoucher'])) {
    $params['transactionId'] = $billsafe_orders['transactionid'];
    $counter = 0;
    $voucherAmount = $_POST['voucherAmount'];
    $voucherAmountKomma = $_POST['voucherAmountKomma'];
    $voucher_tax_rate = xtc_get_tax_rate($_POST['voucherTax']);
    if ($voucherAmount == '') $voucherAmount = 0;
    if ($voucherAmountKomma == '') $voucherAmountKomma = 0;
    $amount = '-'.$voucherAmount.'.'.$voucherAmountKomma;
    $total = 0;
    $totalTax = 0;
    getTotalsOrderProducts($order_id);
    if (($total + $amount) >= 0) {
      $queryVoucher = xtc_db_query('SELECT * FROM billsafe_orders_details_2 WHERE articlenumber LIKE "backend-voucher-%"');
      $countVoucher = xtc_db_num_rows($queryVoucher);
      xtc_db_query('INSERT INTO billsafe_orders_details_2 (ordernumber, articletype, articlenumber, articlename, articleprice, articletax, bsorders_id) VALUES ("'.xtc_db_input($order_id).'", "voucher", "backend-voucher-'.($countVoucher + 1).'", "'.MODULE_PAYMENT_BILLSAFE_2_UPDATEARTICLELISTVOUCHER.'-'.($countVoucher + 1).'", "'.xtc_db_input($amount).'", "'.xtc_db_input($voucher_tax_rate).'", "'.xtc_db_input($billsafe_orders['id']).'");');
      get_bsod($order_id);
      $total = 0;
      $totalTax = 0;
      getTotalsOrderProducts($order_id);
      $params['articleList'] = $articleList;
      $params['order_amount'] = number_format($total, 2, '.', '');
      $params['order_taxAmount'] = number_format($totalTax, 2, '.', '');
      $params['order_currencyCode'] = $currency;
      $response = $bs->callMethod('updateArticleList', $params);
      if ($response->ack == "OK") {
        xtc_db_query('INSERT INTO billsafe_transactions_2 (id, ordernumber, articlenumber, transactionmethod, bsorder_id, bsordersdetails_id, date) VALUES (NULL, "'.xtc_db_input($order_id).'", "backend-voucher-'.($countVoucher + 1).'", "updateArticleListVoucher", "'.xtc_db_input($billsafe_orders['id']).'", "'.xtc_db_input($addVoucher['id']).'", CURRENT_TIMESTAMP);');
        $messageBox = 'SUCCESS';
        $message = MODULE_PAYMENT_BILLSAFE_2_MESSAGE_VOUCHER;
      } else {
        xtc_db_query('DELETE FROM billsafe_orders_details_2 WHERE articlenumber = "backend-voucher-'.($countVoucher + 1).'"');
        if (is_array($response->errorList)) respError($response);
      }
    }
  } elseif (isset($_POST['reportDirectPayment'])) {
    $params['transactionId'] = $billsafe_orders['transactionid'];
    $counter = 0;
    $dpaymentAmount = $_POST['dpaymentAmount'];
    $dpaymentAmountKomma = $_POST['dpaymentAmountKomma'];
    if ($dpaymentAmount == '') $dpaymentAmount = 0;
    if ($dpaymentAmountKomma == '') $dpaymentAmountKomma = 0;
    $dpayment = $dpaymentAmount.'.'.$dpaymentAmountKomma;
    if ($_POST['dpaymentDay'] > 0 && $_POST['dpaymentDay'] <= 31) {
      if ($_POST['dpaymentDay'] > 0 && $_POST['dpaymentDay'] < 10 && substr($_POST['dpaymentDay'], 0, 1) != 0) {
        $dpaymentDay = '0'.$_POST['dpaymentDay'];
      } else {
        $dpaymentDay = $_POST['dpaymentDay'];
      }
    } else {
      $dpaymentDay = date('d');
    }
    if ($_POST['dpaymentMonth'] > 0 && $_POST['dpaymentMonth'] <= 12) {
      if ($_POST['dpaymentMonth'] > 0 && $_POST['dpaymentMonth'] < 10 && substr($_POST['dpaymentMonth'], 0, 1) != 0) {
        $dpaymentMonth = '0'.$_POST['dpaymentMonth'];
      } else {
        $dpaymentMonth = $_POST['dpaymentMonth'];
      }
    } else {
      $dpaymentMonth = date('m');
    }
    if ($_POST['dpaymentYear'] != '') {
      $dpaymentYear = $_POST['dpaymentYear'];
    } else {
      $dpaymentYear = date('Y');
    }
    $params['amount'] = number_format($dpayment, 2, '.', '');
    $params['currencyCode'] = $currency;
    $params['date'] = $dpaymentYear.'-'.$dpaymentMonth.'-'.$dpaymentDay;
    $response = $bs->callMethod('reportDirectPayment', $params);
    if ($response->ack == "OK") {
      $messageBox = 'SUCCESS';
      $message = MODULE_PAYMENT_BILLSAFE_2_MESSAGE_DPAYMENT;
    } else {
      if (is_array($response->errorList)) respError($response);
    }
  } elseif (isset($_POST['updateArticleListRetoureFull'])) {
    $params = getParamsFullStRe($billsafe_orders, $order);
    $response = $bs->callMethod('updateArticleList', $params);
    if ($response->ack == 'OK') {
      insDB_trans($order, $order_id, 'updateArticleListRetoureFull', $billsafe_orders['id'], $sql_order_id);
      xtc_db_query('UPDATE billsafe_orders_details_2 SET retoure = 1 WHERE ordernumber = "'.xtc_db_input($order_id).'"');
      $messageBox = 'SUCCESS';
      $message = MODULE_PAYMENT_BILLSAFE_2_MESSAGE_FRETOURE;
    } else {
      if (is_array($response->errorList)) respError($response);
    }
  } elseif (isset($_POST['updateArticleListStornoFull'])) {
    $params = getParamsFullStRe($billsafe_orders, $order);
    $response = $bs->callMethod('updateArticleList', $params);
    if ($response->ack == 'OK') {
      insDB_trans($order, $order_id, 'updateArticleListStornoFull', $billsafe_orders['id'], $sql_order_id);
      xtc_db_query('UPDATE billsafe_orders_details_2 SET storno = 1 WHERE ordernumber = "'.xtc_db_input($order_id).'"');
      $messageBox = 'SUCCESS';
      $message = MODULE_PAYMENT_BILLSAFE_2_MESSAGE_FSTORNO;
    } else {
      if (is_array($response->errorList)) respError($response);
    }
  } elseif (isset($_POST['pauseTransaction'])) {
    $params['transactionId'] = $billsafe_orders['transactionid'];
    $pauseDays = $_POST['pauseDays'];
    if ($pauseDays == '') $pauseDays = 0;
    if ($pauseDays > 10) $pauseDays = 10;
    $params['pause'] = $pauseDays;
    $response = $bs->callMethod('pauseTransaction', $params);
    if ($response->ack == 'OK') {
      insDB_trans($order, $order_id, 'pauseTransaction', $billsafe_orders['id'], $sql_order_id);
      xtc_db_query('UPDATE billsafe_orders_details_2 SET pause = "'.xtc_db_input($pauseDays).'" WHERE ordernumber = "'.xtc_db_input($order_id).'" AND storno = "0" AND retoure = "0"');
      $messageBox = 'SUCCESS';
      $message = MODULE_PAYMENT_BILLSAFE_2_MESSAGE_PAUSETRANSACTION;
    } else {
      if (is_array($response->errorList)) respError($response);
    }
  }

  $order = new order($order_id);

  function insDB_trans($order, $order_id, $action, $bs_orders_id, $sql_order_id) {
    $queryOrders = xtc_db_query($sql_order_id);
    while ($order=xtc_db_fetch_array($queryOrders)) {
      xtc_db_query('INSERT INTO billsafe_transactions_2 (id, ordernumber, articlenumber, transactionmethod, bsorder_id, bsordersdetails_id, date) VALUES (NULL, "'.xtc_db_input($order_id).'", "'.xtc_db_input($order['articlenumber']).'", "'.xtc_db_input($action).'", "'.xtc_db_input($bs_orders_id).'", "'.xtc_db_input($order['id']).'", CURRENT_TIMESTAMP);');
    }
  }

  function updDB_art_part($type, $orderIDArr) {
    global $orderArticles, $orderIDArr;
    for ($i = 0; $i < count($orderIDArr); $i++) {
      $sOrderArtId = $orderIDArr[$i];
      $querybsod = xtc_db_query('SELECT * FROM billsafe_orders_details_2 WHERE id = "'.xtc_db_input($sOrderArtId).'"');
      $bsod = xtc_db_fetch_array($querybsod);
      if (array_key_exists($bsod['articlenumber'], $orderArticles)) {
        $orderArticles[$bsod['articlenumber']] = $orderArticles[$bsod['articlenumber']] + 1;
      } else {
        $orderArticles[$bsod['articlenumber']] = 1;
      }
      xtc_db_query('UPDATE billsafe_orders_details_2 SET '.xtc_db_input($type).' = 1 WHERE id = "'.xtc_db_input($sOrderArtId).'"');
    }
  }


  function getParamsFullStRe($billsafe_orders, $order) {
    $params = array();
    $params['transactionid'] = $billsafe_orders['transactionid'];
    $params['order_amount'] = number_format((int)0, 2, '.', '');
    $params['order_taxAmount'] = number_format((int)0, 2, '.', '');
    $params['order_currencyCode'] = $order->info['currency'];
    return ($params);
  }

  function getTotalsOrderProducts($order_id) {
    global $total, $totalTax;
    $orderQuery = xtc_db_query('SELECT * FROM billsafe_orders_details_2 bsod WHERE ordernumber = "'.xtc_db_input($order_id).'"');
    while ($orderData = xtc_db_fetch_array($orderQuery)) {
      if ($orderData['storno'] != 1 && $orderData['retoure'] != 1) {
        $total = $total + $orderData['articleprice'];
        $zwischenTax = ($orderData['articleprice'] / (100 + $orderData['articletax'])) * $orderData['articletax'];
        $totalTax = $totalTax + $zwischenTax;
      }
    }
  }

  function updDB_ot($action, $order_id) {
    global $orderIDArr;
    $queryLeftArticles = xtc_db_query('SELECT * FROM billsafe_orders_details_2 WHERE ordernumber = "'.xtc_db_input($order_id).'" AND articletype = "goods" AND (shipped = 0 AND storno = 0) OR (shipped = 1 AND retoure = 0)');
    $countArticles = xtc_db_num_rows($queryLeftArticles);
    if ($countArticles == 0) {
      $queryLeftArticles = xtc_db_query('SELECT * FROM billsafe_orders_details_2 WHERE ordernumber = "'.xtc_db_input($order_id).'" AND (shipped = 0 AND storno = 0) OR (shipped = 1 AND retoure = 0) GROUP BY id');
      while ($leftArticles = xtc_db_fetch_array($queryLeftArticles)) {
        xtc_db_query('UPDATE billsafe_orders_details_2 SET '.xtc_db_input($action).' = 1 WHERE id = "'.xtc_db_input($leftArticles['id']).'"');
      }
    }
  }

  function get_bsod($order_id) {
    global $articleList;
    $counter = 0;
    $querybsod = xtc_db_query('SELECT COUNT(articlenumber) AS quantity, articlenumber FROM billsafe_orders_details_2 WHERE ordernumber = "'.xtc_db_input($order_id).'" GROUP BY articlenumber');
    while ($bsod = xtc_db_fetch_array($querybsod)) {
      $queryOrders = xtc_db_query('SELECT * FROM billsafe_orders_details_2 WHERE articlenumber = "'.xtc_db_input($bsod['articlenumber']).'" AND ordernumber = "'.xtc_db_input($order_id).'"');
      $order = xtc_db_fetch_array($queryOrders);
      $articleList[$counter]['number'] = $order['articlenumber'];
      $articleList[$counter]['name'] = $order['articlename'];
      $ssrarr = getSSRarr($order_id, $bsod['articlenumber']);
      $articleList[$counter]['type'] = $order['articletype'];
      $articleList[$counter]['quantity'] = (int)$bsod['quantity'] - $ssrarr['storno'] - $ssrarr['retoure'];
      $articleList[$counter]['quantityShipped'] = (int)$ssrarr['shipped'] - $ssrarr['retoure'];
      $articleList[$counter]['tax'] = number_format(floatval($order['articletax']), 2 , '.', '');
      $articleList[$counter]['grossPrice'] = number_format($order['articleprice'], 2, '.', '');
      $counter++;
    }
  }

  function getSSRarr($order_id, $artno) {
    $shippedArray = xtc_db_fetch_array(xtc_db_query('SELECT COUNT(articlenumber) AS shipped FROM billsafe_orders_details_2 WHERE ordernumber = "'.xtc_db_input($order_id).'" AND shipped = 1 AND articlenumber = "'.$artno.'"'));
    $ssrarr['shipped'] = $shippedArray['shipped'];
    $stornoArray = xtc_db_fetch_array(xtc_db_query('SELECT COUNT(articlenumber) AS storno FROM billsafe_orders_details_2 WHERE ordernumber = "'.xtc_db_input($order_id).'" AND storno = 1 AND articlenumber = "'.$artno.'"'));
    $ssrarr['storno'] = $stornoArray['storno'];
    $retoureArray = xtc_db_fetch_array(xtc_db_query('SELECT COUNT(articlenumber) AS retoure FROM billsafe_orders_details_2 WHERE ordernumber = "'.xtc_db_input($order_id).'" AND retoure = 1 AND articlenumber = "'.$artno.'"'));
    $ssrarr['retoure'] = $retoureArray['retoure'];
    return $ssrarr;
  }

  function getTotal0($order_id, $action) {
    $orderIDArr = array();
    $orderVoucher = xtc_db_query('SELECT * FROM billsafe_vouchers_2 bv, billsafe_orders_details_2 bsod WHERE bsod.ordernumber = "'.xtc_db_input($order_id).'" AND bsod.id = bv.id AND bsod.articlenumber LIKE "backend-voucher-%" AND bsod.storno = 0 AND retoure = 0');
    while ($orderData = xtc_db_fetch_array($orderVoucher)) {
      xtc_db_query('UPDATE billsafe_orders_details_2 SET '.xtc_db_input($action).' = 1 WHERE id = "'.xtc_db_input($orderData['id']).'"');
      $orderIDArr[] = $orderData['id'];
    }
    return $orderIDArr;
  }

  function getBillSAFEresp($response, $defmess, $method, $action, $orderIDArr, $order_id, $billsafe_orders_id) {
    global $messageBox, $message;
    if ($response->ack == 'OK') {
      foreach ($orderIDArr as $orderID) {
        $values = array();
        $resultArticle = xtc_db_query('SELECT * FROM billsafe_orders_details_2 WHERE id = "'.xtc_db_input($orderID).'"');
        $values = xtc_db_fetch_array($resultArticle);
        xtc_db_query('INSERT INTO billsafe_transactions_2 (id, ordernumber, articlenumber, transactionmethod, bsorder_id, bsordersdetails_id, date) VALUES (NULL, "'.xtc_db_input($order_id).'", "'.xtc_db_input($values['articlenumber']).'", "'.xtc_db_input($method).'", "'.xtc_db_input($billsafe_orders_id).'", "'.xtc_db_input($values['id']).'", CURRENT_TIMESTAMP);');
      }
      $messageBox = 'SUCCESS';
      $message = $defmess;
    } else {
      if (is_array($response->errorList)) respError($response);
      for ($i = 0; $i < count($orderIDArr); $i++) {
        $sOrderArtId = $orderIDArr[$i];
        xtc_db_query('UPDATE billsafe_orders_details_2 SET '.xtc_db_input($action).' = 0 WHERE id = "'.xtc_db_input($sOrderArtId).'"');
      }
    }
  }

  function respError($errorMess) {
    global $messageBox, $message;
    foreach ($errorMess->errorList as $error) {
      $messageBox = 'ERROR';
      $message = $error->message;
    }
  }

  function showMessageSuccess($message) {
    $message_box = '<table border="0" width="100%" cellspacing="0" cellpadding="2"><tr><td class="messageStackSuccess"><img border="0" title="" alt="" src="images/icons/success.gif">'.$message.'</td></tr></table>';
    return $message_box;
  }

  function showMessageError($message) {
    $message = '<table border="0" width="100%" cellspacing="0" cellpadding="2"><tr><td class="messageStackError"><img border="0" title="" alt="" src="images/icons/error.gif">'.$message.'</td></tr></table>';
    return $message;
  }
  
  require (DIR_WS_INCLUDES.'head.php');
?>

<script language="javascript" src="includes/general.js"></script>
<script type="text/javascript" >
  function doShipment(jalert) {
    var count = document.getElementsByName('shipped[]').length;
    var flag = 0;
    for (i = 0; i < count; i++) {
      if (document.getElementById('shipped'+i).disabled == true) {
        continue;
      }
      if (document.getElementById('shipped'+i).checked == true) {
        flag = 1;
      }
    }
    if (flag == 0) {
      alert(jalert);
    } else {
      document.pshipment.submit();
    }
  }
</script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<table border="0" width="100%" cellspacing="2" cellpadding="2"><tr>
<td class="columnLeft2" width="<?php echo BOX_WIDTH; ?>" valign="top">
<table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
<?php if (USE_ADMIN_TOP_MENU != 'true') require(DIR_WS_INCLUDES . 'column_left.php'); ?>
</table></td>
<td width="100%" valign="top">
<table border="0" width="100%" cellspacing="0" cellpadding="2"><tr><td>
<table border="0" width="100%" cellspacing="0" cellpadding="2"><tr>
<td width="100%"><?php 
  if ($messageBox == 'SUCCESS') {
    $showMessage = showMessageSuccess($message);
    echo $showMessage;
  } elseif ($messageBox == 'ERROR') {
    $showMessage = showMessageError($message);
    echo $showMessage;
  } ?>
<table border="0" width="100%" cellspacing="0" cellpadding="2" height="40"><tr>
<td class="pageHeading"><?php echo MODULE_PAYMENT_BILLSAFE_2_DETAILS; ?></td>
<td class="pageHeading" align="right"><?php echo '<a class="button" href="'.xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action'))).'">'.BUTTON_BACK.'</a>'; ?></td></tr>
<tr><td colspan="2"><img width="100%" height="1" border="0" alt="" src="images/pixel_black.gif"></td></tr></table>
</td></tr><tr><td>
<table width="100%" border="0" cellspacing="0" cellpadding="2"><tr>
<td colspan="3"><?php //echo xtc_draw_separator(); ?></td></tr>
<tr><td valign="top" width="30%">
<table width="100%" border="0" cellspacing="0" cellpadding="2"><tr>
<td class="main" valign="top"><strong><?php echo MODULE_PAYMENT_BILLSAFE_2_BADDRESS; ?></strong></td>
</tr><tr>
<td class="main"><?php
  $customer = xtc_db_fetch_array(xtc_db_query('SELECT * FROM billsafe_orders_user_2 WHERE bsorders_id  ="'.xtc_db_input($billsafe_orders['id']).'"'));
  $country_query = xtc_db_query('SELECT countries_name FROM countries WHERE countries_iso_code_2 = "'.xtc_db_input($customer['country']).'"');
  $country = xtc_db_fetch_array($country_query);
  echo $customer['company'].'<br />'.$customer['firstname'].' '.$customer['lastname'].'<br />'.$customer['street'].' '.$customer['housenumber'].'<br />'.$customer['postcode'].' '.$customer['city'].'<br />'.$country['countries_name'].'<br /><br />'.MODULE_PAYMENT_BILLSAFE_2_EMAIL.': '.$customer['email'].'<br />';
  ?>
</td></tr></table></td>
<td valign="top" width="30%"><table width="100%" border="0" cellspacing="0" cellpadding="2"><tr>
<td class="main" valign="top"><strong><?php echo MODULE_PAYMENT_BILLSAFE_2_SADDRESS; ?></strong></td>
</tr><tr>
<td class="main"><?php
  echo xtc_address_format($order->billing['format_id'], $order->delivery, 1, '', '<br />');
  ?>
</td></tr></table></td>
<td valign="top" width="40%"><?php
  $params = array('transactionId' => $billsafe_orders['transactionid']);
  $response = $bs->callMethod('getPaymentInstruction', $params);
  if ($response->ack == 'OK') {
    $ins = $response->instruction; ?>
    <table width="100%" border="0" cellspacing="0" cellpadding="2"><tr>
    <td class="main" valign="top"><strong><?php echo MODULE_PAYMENT_BILLSAFE_2_BANK_DETAILS; ?></strong></td>
    <tr><td class="main" width="40%" valign="top"><table><tr>
      <td class="main"><?php echo MODULE_PAYMENT_BILLSAFE_2_BANK_CODE; ?></td>
      <td class="main"><?php echo ':'; ?></td>
      <td class="main"><?php echo $ins->bankCode; ?></td>
    </tr><tr>
      <td class="main"><?php echo MODULE_PAYMENT_BILLSAFE_2_BANK_NAME; ?></td>
      <td class="main"><?php echo ':'; ?></td>
      <td class="main"><?php echo $ins->bankName; ?></td>
    </tr><tr>
      <td class="main"><?php echo MODULE_PAYMENT_BILLSAFE_2_ACCOUNT_NUMBER; ?></td>
      <td class="main"><?php echo ':'; ?></td>
      <td class="main"><?php echo $ins->accountNumber; ?></td>
    </tr><tr>
      <td class="main"><?php echo MODULE_PAYMENT_BILLSAFE_2_RECIPIENT; ?></td>
      <td class="main"><?php echo ':'; ?></td>
      <td class="main"><?php echo $ins->recipient; ?></td>
    </tr><tr>
      <td class="main"><?php echo MODULE_PAYMENT_BILLSAFE_2_BIC; ?></td>
      <td class="main"><?php echo ':'; ?></td>
      <td class="main"><?php echo $ins->bic; ?></td>
    </tr><tr>
      <td class="main"><?php echo MODULE_PAYMENT_BILLSAFE_2_IBAN; ?></td>
      <td class="main"><?php echo ':'; ?></td>
      <td class="main"><?php echo $ins->iban; ?></td>
    </tr><tr>
      <td class="main"><?php echo MODULE_PAYMENT_BILLSAFE_2_REFERENCE; ?></td>
      <td class="main"><?php echo ':'; ?></td>
      <td class="main"><?php echo $ins->reference; ?></td>
    </tr><tr>
      <td class="main" valign="top"><?php echo MODULE_PAYMENT_BILLSAFE_2_NOTE; ?></td>
      <td class="main" valign="top"><?php echo ':'; ?></td>
      <td class="main"><?php echo $ins->legalNote; ?><br /><b><?php echo $ins->note; ?></b></td>
    </tr><tr >
      <td class="main"><?php echo MODULE_PAYMENT_BILLSAFE_2_AMOUNT; ?></td>
      <td class="main"><?php echo ':'; ?></td>
      <td class="main"><?php echo number_format($ins->amount,2).' '.$ins->currencyCode; ?></td>
    </tr></table></td></tr></td></tr></table>
  <?php } ?>
</td></tr></table>
<table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php echo xtc_draw_form('article', $post_url, $post_param, 'post'); ?>
<tr class="dataTableHeadingRow">
<td class="dataTableHeadingContent"><?php echo MODULE_PAYMENT_BILLSAFE_2_PRODUCTS; ?></td>
<td class="dataTableHeadingContent"><?php echo MODULE_PAYMENT_BILLSAFE_2_MODEL; ?></td>
<td class="dataTableHeadingContent" align="right"><?php echo MODULE_PAYMENT_BILLSAFE_2_TAX; ?></td>
<td class="dataTableHeadingContent" align="right"><?php echo MODULE_PAYMENT_BILLSAFE_2_PRICE_INC; ?></td>
<td class="dataTableHeadingContent" align="center"><?php echo MODULE_PAYMENT_BILLSAFE_2_CHECK; ?></td>
</tr><?php
  $countOrder = xtc_db_fetch_array(xtc_db_query('SELECT COUNT(articlenumber) AS quantity FROM billsafe_orders_details_2 WHERE ordernumber = "'.xtc_db_input($order_id).'"'));
  $countUsed = xtc_db_fetch_array(xtc_db_query('SELECT COUNT(articlenumber) AS quantity FROM billsafe_orders_details_2 WHERE ordernumber = "'.xtc_db_input($order_id).'" AND (shipped = 1 OR storno = 1)'));
  $countShipped = xtc_db_fetch_array(xtc_db_query('SELECT COUNT(articlenumber) AS quantity FROM billsafe_orders_details_2 WHERE ordernumber = "'.xtc_db_input($order_id).'" AND shipped = 1'));
  $countRetoure = xtc_db_fetch_array(xtc_db_query('SELECT COUNT(articlenumber) AS quantity FROM billsafe_orders_details_2 WHERE ordernumber = "'.xtc_db_input($order_id).'" AND (shipped = 1 AND retoure = 1)'));
  $countNotRetoure = xtc_db_fetch_array(xtc_db_query('SELECT COUNT(articlenumber) AS quantity FROM billsafe_orders_details_2 WHERE ordernumber = "'.xtc_db_input($order_id).'" AND (shipped = 1 AND retoure = 0)'));
  $orderQuery = xtc_db_query('SELECT * FROM billsafe_orders_details_2 WHERE ordernumber = "'.xtc_db_input($order_id).'"');
  while ($orderData = xtc_db_fetch_array($orderQuery)) {
    echo '<tr class="dataTableRow"><td class="dataTableContent" valign="top">'.$orderData['articlename'].'</td><td class="dataTableContent" valign="top">'.$orderData['articlenumber'].'</td><td class="dataTableContent" align="right" valign="top">'.xtc_display_tax_value($orderData['articletax']).'%</td><td class="dataTableContent" align="right" valign="top"><strong>'.format_price($orderData['articleprice'], 1, $order->info['currency'], 0, 0).'</strong></td><td class="dataTableContent" valign="top" align="center">';
    if ($orderData['shipped'] == 1 || $orderData['storno'] == 1) {
      echo '<input type="checkbox" name="shipped[]" value='.$orderData['id'].' checked="checked" disabled="disabled" />&nbsp;</td>';
    } else {
      echo '<input type="checkbox" name="shipped[]" value='.$orderData['id'].' />&nbsp;</td>';
    }
    echo '</tr>';
  }
  ?><tr><td colspan='9' align='right'><?php
  if ($countOrder['quantity'] == $countUsed['quantity']) { ?>
    <input type="submit" name="reportShipmentFull" value="<?php echo MODULE_PAYMENT_BILLSAFE_2_FREPORT_SHIPMENT; ?>" disabled="disabled" />
    <input type="submit" name="reportShipmentPart" value="<?php echo MODULE_PAYMENT_BILLSAFE_2_PREPORT_SHIPMENT; ?>" disabled="disabled" />
    <input type="submit" name="updateArticleListStornoFull" value="<?php echo MODULE_PAYMENT_BILLSAFE_2_UPDATEARTICLELISTSTORNOFULL; ?>" disabled="disabled" />
    <input type="submit" name="updateArticleListStornoPart" value="<?php echo MODULE_PAYMENT_BILLSAFE_2_UPDATEARTICLELISTSTORNOPART; ?>" disabled="disabled" />
    <input type="hidden" name="id" value="<?php echo $id; ?>" />
  <?php } elseif ($countUsed['quantity'] == 0) { ?>
    <input type="submit" name="reportShipmentFull" value="<?php echo MODULE_PAYMENT_BILLSAFE_2_FREPORT_SHIPMENT; ?>" />
    <input type="submit" name="reportShipmentPart" value="<?php echo MODULE_PAYMENT_BILLSAFE_2_PREPORT_SHIPMENT; ?>">
    <input type="submit" name="updateArticleListStornoFull" value="<?php echo MODULE_PAYMENT_BILLSAFE_2_UPDATEARTICLELISTSTORNOFULL; ?>" />
    <input type="submit" name="updateArticleListStornoPart" value="<?php echo MODULE_PAYMENT_BILLSAFE_2_UPDATEARTICLELISTSTORNOPART; ?>" />
    <input type="hidden" name="id" value="<?php echo $id; ?>" />
  <?php } else { ?>
    <input type="submit" name="reportShipmentFull" value="<?php echo MODULE_PAYMENT_BILLSAFE_2_FREPORT_SHIPMENT; ?>" disabled="disabled" />
    <input type="submit" name="reportShipmentPart" value="<?php echo MODULE_PAYMENT_BILLSAFE_2_PREPORT_SHIPMENT; ?>" />
    <input type="submit" name="updateArticleListStornoFull" value="<?php echo MODULE_PAYMENT_BILLSAFE_2_UPDATEARTICLELISTSTORNOFULL; ?>" disabled="disabled" />
    <input type="submit" name="updateArticleListStornoPart" value="<?php echo MODULE_PAYMENT_BILLSAFE_2_UPDATEARTICLELISTSTORNOPART; ?>" />
    <input type="hidden" name="id" value="<?php echo $id; ?>" />
  <?php } ?>
</td></tr></form></table>
<br /><?php
  if (!empty($_SESSION['languages_id']) && $_SESSION['languages_id'] > 0) {
    $language_id = (int)$_SESSION['languages_id'];
  } else {
    $language_id = 1;
  }
  $prod_history_query = xtc_db_query('SELECT bd.articlenumber, bd.articletype, bsod.bsordersdetails_id, bd.articlename, bsod.transactionmethod, bsod.date, bd.retoure, bd.pause FROM billsafe_transactions_2 bsod, billsafe_orders_details_2 bd WHERE bsod.ordernumber = "'.xtc_db_input($order_id).'" AND bd.ordernumber = "'.xtc_db_input($order_id).'" AND bsod.bsordersdetails_id = bd.id');
  if (xtc_db_num_rows($prod_history_query) > 0) { 
    echo xtc_draw_form('update_transaction', $post_url, $post_param, 'post');
    echo '<table border="0" width="100%" cellspacing="0" cellpadding="2"><tr class="dataTableHeadingRow"><td class="dataTableHeadingContent" align="center"><strong>'.MODULE_PAYMENT_BILLSAFE_2_MODEL.'</strong></td><td class="dataTableHeadingContent" align="center"><strong>'.MODULE_PAYMENT_BILLSAFE_2_PRODUCTS.'</strong></td><td class="dataTableHeadingContent" align="center"><strong>'.MODULE_PAYMENT_BILLSAFE_2_PREPORT_METHOD.'</strong></td><td class="dataTableHeadingContent" align="center"><strong>'.MODULE_PAYMENT_BILLSAFE_2_PREPORT_DATE.'</strong></td><td class="dataTableHeadingContent" align="center">&nbsp;</td></tr>';
    while ($prod_history = xtc_db_fetch_array($prod_history_query)) {
      if ($prod_history['transactionmethod'] == 'reportShipmentFull') {
        $method = MODULE_PAYMENT_BILLSAFE_2_FREPORT_SHIPMENT;
      } elseif ($prod_history['transactionmethod'] == 'reportShipmentPart') {
        $method = MODULE_PAYMENT_BILLSAFE_2_PREPORT_SHIPMENT;
      } elseif ($prod_history['transactionmethod'] == 'updateArticleListRetoureFull') {
        $method = MODULE_PAYMENT_BILLSAFE_2_UPDATEARTICLELISTRETOUREFULL;
      } elseif ($prod_history['transactionmethod'] == 'updateArticleListRetourePart') {
        $method = MODULE_PAYMENT_BILLSAFE_2_UPDATEARTICLELISTRETOUREPART;
      } elseif ($prod_history['transactionmethod'] == 'updateArticleListStornoFull') {
        $method = MODULE_PAYMENT_BILLSAFE_2_UPDATEARTICLELISTSTORNOFULL;
      } elseif ($prod_history['transactionmethod'] == 'updateArticleListStornoPart') {
        $method = MODULE_PAYMENT_BILLSAFE_2_UPDATEARTICLELISTSTORNOPART;
      } elseif ($prod_history['transactionmethod'] == 'updateArticleListVoucher') {
        $method = MODULE_PAYMENT_BILLSAFE_2_UPDATEARTICLELISTVOUCHER;
      } elseif ($prod_history['transactionmethod'] == 'pauseTransaction') {
        $method = MODULE_PAYMENT_BILLSAFE_2_PAUSETRANSACTION.': '.$prod_history['pause'].'&nbsp;'.MODULE_PAYMENT_BILLSAFE_2_PAUSEDAYS;
      }
      if (($prod_history['transactionmethod'] == 'pauseTransaction') && ($prod_history['pause'] > 0)) {
        echo '<tr class="dataTableRow" valign="top"><td class="dataTableContent" align="center" valign="top">'.$prod_history['articlenumber'].'</td><td class="dataTableContent" align="center" valign="top">'.$prod_history['articlename'].'</td><td class="dataTableContent" align="center" valign="top">'.$method.'</td><td class="dataTableContent" align="center" valign="top">'.$prod_history['date'].'</td><td class="dataTableContent" valign="top" align="right">';
        if ((($prod_history['transactionmethod'] != 'reportShipmentFull') && ($prod_history['transactionmethod'] != 'reportShipmentPart')) || ($prod_history['articletype'] == 'voucher')) {
          echo '&nbsp;</td>';
        } elseif ($prod_history['retoure'] == 1) {
          echo '<input type="checkbox" name="retoure[]" value='.$prod_history['bsordersdetails_id'].' checked="checked" disabled="disabled" />&nbsp;</td>';
        } else {
          echo '<input type="checkbox" name="retoure[]" value='.$prod_history['bsordersdetails_id'].' />&nbsp;</td>';
        }
        echo '</tr>';
      } elseif ($prod_history['transactionmethod'] != 'pauseTransaction') {
        echo '<tr class="dataTableRow" valign="top"><td class="dataTableContent" align="center" valign="top">'.$prod_history['articlenumber'].'</td><td class="dataTableContent" align="center" valign="top">'.$prod_history['articlename'].'</td><td class="dataTableContent" align="center" valign="top">'.$method.'</td><td class="dataTableContent" align="center" valign="top">'.$prod_history['date'].'</td><td class="dataTableContent" valign="top" align="right">';
        if ((($prod_history['transactionmethod'] != 'reportShipmentFull') && ($prod_history['transactionmethod'] != 'reportShipmentPart')) || ($prod_history['articletype'] == 'voucher')) {
          echo '&nbsp;</td>';
        } elseif ($prod_history['retoure'] == 1) {
          echo '<input type="checkbox" name="retoure[]" value='.$prod_history['bsordersdetails_id'].' checked="checked" disabled="disabled" />&nbsp;</td>';
        } else {
          echo '<input type="checkbox" name="retoure[]" value='.$prod_history['bsordersdetails_id'].' />&nbsp;</td>';
        }
        echo '</tr>';
      }
    } ?>
    <td colspan="9" align="right"><?php
    if ($countOrder['quantity'] == $countNotRetoure['quantity']) { ?>
      <input type="submit" name="updateArticleListRetoureFull" value="<?php echo MODULE_PAYMENT_BILLSAFE_2_UPDATEARTICLELISTRETOUREFULL; ?>" />
      <input type="submit" name="updateArticleListRetourePart" value="<?php echo MODULE_PAYMENT_BILLSAFE_2_UPDATEARTICLELISTRETOUREPART; ?>" />
    <?php } elseif($countNotRetoure['quantity'] != 0) { ?>
      <input type="submit" name="updateArticleListRetoureFull" value="<?php echo MODULE_PAYMENT_BILLSAFE_2_UPDATEARTICLELISTRETOUREFULL; ?>" disabled="disabled" />
      <input type="submit" name="updateArticleListRetourePart" value="<?php echo MODULE_PAYMENT_BILLSAFE_2_UPDATEARTICLELISTRETOUREPART; ?>" />
    <?php } else { ?>
      <input type="submit" name="updateArticleListRetoureFull" value="<?php echo MODULE_PAYMENT_BILLSAFE_2_UPDATEARTICLELISTRETOUREFULL; ?>" disabled="disabled" />
      <input type="submit" name="updateArticleListRetourePart" value="<?php echo MODULE_PAYMENT_BILLSAFE_2_UPDATEARTICLELISTRETOUREPART; ?>" disabled="disabled" />
    <?php } ?>
    </td></form>
  <?php echo '</table>';
  } ?>
<table border="0" cellspacing="0" cellpadding="2"><tr valign="top">
  <?php if ($countOrder['quantity'] == $countUsed['quantity']) { ?>
    <td><table border="0" cellspacing="0" cellpadding="2"><tr class="dataTableHeadingRow">
    <td class="dataTableHeadingContent"><?php echo MODULE_PAYMENT_BILLSAFE_2_PAUSETRANSACTION; ?></td>
    </tr><tr class="dataTableRow">
    <td class="dataTableContent" align="right" valign="top">
    <?php echo xtc_draw_form('pause', $post_url, $post_param, 'post'); ?>
    <input id="pauseDays" type="text" style="float:none;" maxlength="2" name="pauseDays" size="2" value="" />&nbsp;<?php echo MODULE_PAYMENT_BILLSAFE_2_PAUSEDAYS; ?>&nbsp;
    <input type="submit" name="pauseTransaction" value="<?php echo MODULE_PAYMENT_BILLSAFE_2_PAUSETRANSACTION; ?>" />
    </form></td></tr></table></td>
  <?php } ?>
<td><table border="0" cellspacing="0" cellpadding="2"><tr class="dataTableHeadingRow">
<td class="dataTableHeadingContent"><?php echo MODULE_PAYMENT_BILLSAFE_2_UPDATEARTICLELISTVOUCHER; ?></td>
</tr><tr class="dataTableRow">
<td class="dataTableContent" align="right" valign="top">
<?php echo xtc_draw_form('voucher', $post_url, $post_param, 'post'); ?>
<input id="voucherAmount" type="text" style="float:none;" maxlength="4" name="voucherAmount" size="4" value="" />,<input style="float:none;" id="voucherAmountKomma" type="text" maxlength="2" name="voucherAmountKomma" size="2" value="" />&nbsp;<?php echo $currency; ?>,&nbsp;<?php echo MODULE_PAYMENT_BILLSAFE_2_TAX.': '; echo xtc_draw_pull_down_menu('voucherTax', $tax_class_array, $tax_class_id); ?><br /><br />
<input type="submit" name="updateArticleListVoucher" value="<?php echo MODULE_PAYMENT_BILLSAFE_2_UPDATEARTICLELISTVOUCHER; ?>" />
</form></td></tr></table></td>
<td style="text-align:center;"><a class="button" href="Javascript:void()" onclick="window.open('<?php echo xtc_href_link('billsafe_print_order_2.php', 'oID='.$order_id); ?>', 'popup', 'toolbar=0, width=640, height=600')"><?php echo BUTTON_INVOICE; ?></a><br /><br />
<a class="button" href="https://client.billsafe.de" target="_blank"><?php echo MODULE_PAYMENT_BILLSAFE_2_MP; ?></td>
<td><table border="0" cellspacing="0" cellpadding="2"><tr class="dataTableHeadingRow">
<td class="dataTableHeadingContent"><?php echo MODULE_PAYMENT_BILLSAFE_2_DPAYMENT; ?></td>
</tr><tr class="dataTableRow">
<td class="dataTableContent" align="right" valign="top">
<?php echo xtc_draw_form('dpayment', $post_url, $post_param, 'post'); ?>
<input id="dpaymentAmount" type="text" style="float:none;" maxlength="10" name="dpaymentAmount" size="4" value="" />,<input style="float:none;" id="dpaymentAmountKomma" type="text" maxlength="2" name="dpaymentAmountKomma" size="2" value="" />&nbsp;<?php echo $currency; ?>&nbsp;&nbsp;&nbsp;<?php echo MODULE_PAYMENT_BILLSAFE_2_DAY; ?>:&nbsp;<input style="float:none;" id="dpaymentDay" type="text" maxlength="2" name="dpaymentDay" size="2" value="" />&nbsp;<?php echo MODULE_PAYMENT_BILLSAFE_2_MONTH; ?>:&nbsp;<input style="float:none;" id="dpaymentMonth" type="text" maxlength="2" name="dpaymentMonth" size="2" value="" />&nbsp;<?php echo MODULE_PAYMENT_BILLSAFE_2_YEAR; ?>:&nbsp;<input style="float:none;" id="dpaymentYear" type="text" maxlength="4" name="dpaymentYear" size="4" value="" /><br /><br />
<input type="submit" name="reportDirectPayment" value="<?php echo MODULE_PAYMENT_BILLSAFE_2_REPORT_DPAYMENT; ?>" />
</form></td></tr></table>
</td></tr></table>
<?php require(DIR_WS_INCLUDES.'footer.php'); ?>
<br />
</body>
</html>
<?php require(DIR_WS_INCLUDES.'application_bottom.php'); ?>