<?php
  /* --------------------------------------------------------------
   $Id: class.paypal.php 1718 2011-01-29 00:49:50Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   @copyright Copyright 2003-2007 xt:Commerce (Winger/Zanier), www.xt-commerce.com
   @copyright based on Copyright 2002-2003 osCommerce; www.oscommerce.com
   @copyright Porttions Copyright 2003-2007 Zen Cart Development Team
   @copyright Porttions Copyright 2004 DevosC.com
   @license http://www.xt-commerce.com.com/license/2_0.txt GNU Public License V2.0
   ab 15.08.2008 Teile vom Hamburger-Internetdienst geändert
   Hamburger-Internetdienst Support Forums at www.forum.hamburger-internetdienst.de
   Stand 04.03.2012
   */
  class paypal_admin extends paypal_checkout {
    /*************************************************************/
    function GetTransactionDetails($txn_id) {
      // Stand: 29.04.2009
      $nvpstr = '&TRANSACTIONID=' . urlencode($txn_id);
      $resArray = $this->hash_call("gettransactionDetails", $nvpstr);
      $ack = strtoupper($resArray["ACK"]);
      if($ack != "SUCCESS")
        $this->build_error_message($resArray);
      return $resArray;
    }
    /*************************************************************/
    function RefundTransaction($txn_id, $curr, $amount, $refund, $note = '') {
      // Stand: 29.04.2009
      // full refund ?
      if($note != '')
        $note = '&NOTE=' . urlencode($note);
      if($amount != $refund) {
        $refund = str_replace(',', '.', $refund);
        $nvpstr = '&TRANSACTIONID=' . urlencode($txn_id) . '&REFUNDTYPE=Partial&CURRENCYCODE=' . $curr . '&AMT=' . $refund . $note;
      } else {
        $nvpstr = '&TRANSACTIONID=' . urlencode($txn_id) . '&REFUNDTYPE=Full' . $note;
      }
      $resArray = $this->hash_call("RefundTransaction", $nvpstr);
      $ack = strtoupper($resArray["ACK"]);
      if($ack != "SUCCESS")
        $this->build_error_message($resArray);
      return $resArray;
    }
    /*************************************************************/
    function DoCapture($txn_id, $curr, $amount, $capture_amount, $note = '') {
      // Stand: 29.04.2009
      if($note != '')
        $note = '&NOTE=' . urlencode($note);
      if($amount != $capture_amount) {
        $capture_amount = str_replace(',', '.', $capture_amount);
        $nvpstr = '&AUTHORIZATIONID=' . urlencode($txn_id) . '&COMPLETETYPE=NotComplete&CURRENCYCODE=' . $curr . '&AMT=' . $capture_amount . $note;
      } else {
        $nvpstr = '&AUTHORIZATIONID=' . urlencode($txn_id) . '&COMPLETETYPE=Complete' . $note;
      }
      $resArray = $this->hash_call("DoCapture", $nvpstr);
      $ack = strtoupper($resArray["ACK"]);
      if($ack != "SUCCESS")
        $this->build_error_message($resArray);
      return $resArray;
    }
    /*************************************************************/
    function TransactionSearch($data) {
      // Stand: 29.04.2009
      global $date;
      // date range
      if($data['span'] == 'narrow') {
        // show range
        $startdate = (int) $data['from_y'] . '-' . (int) $data['from_m'] . '-' . (int) $data['from_t'] . 'T00:00:00Z';
        $enddate = (int) $data['to_y'] . '-' . (int) $data['to_m'] . '-' . (int) $data['to_t'] . 'T24:00:00Z';
      } else {
        /*
        * 1 = last day
        * 2 = last week
        * 3 = last month
        * 4 = last year
        */
        switch($data['for']) {
          case '1' :
            $cal_date = mktime(0, 0, 0, date("m"), date("d") - 1, date("Y"));
            $_date = array();
            $_date['tt'] = date('d', $cal_date);
            $_date['mm'] = date('m', $cal_date);
            $_date['yyyy'] = date('Y', $cal_date);
            $startdate = (int) $_date['yyyy'] . '-' . (int) $_date['mm'] . '-' . (int) $_date['tt'] . 'T00:00:00Z';
            $enddate = $date['actual']['yyyy'] . '-' . $date['actual']['mm'] . '-' . $date['actual']['tt'] . 'T24:00:00Z';
            break;
          case '2' :
            $cal_date = mktime(0, 0, 0, date("m"), date("d") - 7, date("Y"));
            $_date = array();
            $_date['tt'] = date('d', $cal_date);
            $_date['mm'] = date('m', $cal_date);
            $_date['yyyy'] = date('Y', $cal_date);
            $startdate = (int) $_date['yyyy'] . '-' . (int) $_date['mm'] . '-' . (int) $_date['tt'] . 'T00:00:00Z';
            $enddate = $date['actual']['yyyy'] . '-' . $date['actual']['mm'] . '-' . $date['actual']['tt'] . 'T24:00:00Z';
            break;
          case '3' :
            $cal_date = mktime(0, 0, 0, date("m") - 1, date("d"), date("Y"));
            $_date = array();
            $_date['tt'] = date('d', $cal_date);
            $_date['mm'] = date('m', $cal_date);
            $_date['yyyy'] = date('Y', $cal_date);
            $startdate = (int) $_date['yyyy'] . '-' . (int) $_date['mm'] . '-' . (int) $_date['tt'] . 'T00:00:00Z';
            $enddate = $date['actual']['yyyy'] . '-' . $date['actual']['mm'] . '-' . $date['actual']['tt'] . 'T24:00:00Z';
            break;
          case '4' :
            $cal_date = mktime(0, 0, 0, date("m"), date("d"), date("Y") - 1);
            $_date = array();
            $_date['tt'] = date('d', $cal_date);
            $_date['mm'] = date('m', $cal_date);
            $_date['yyyy'] = date('Y', $cal_date);
            $startdate = (int) $_date['yyyy'] . '-' . (int) $_date['mm'] . '-' . (int) $_date['tt'] . 'T00:00:00Z';
            $enddate = $date['actual']['yyyy'] . '-' . $date['actual']['mm'] . '-' . $date['actual']['tt'] . 'T24:00:00Z';
            break;
        }
      }
      // search in details
      $detail_search = '';
      if($data['search_type'] != '') {
        switch($data['search_first_type']) {
          case 'email_alias' :
            $detail_search = '&EMAIL=' . urlencode($data['search_type']);
            break;
          case 'trans_id' :
            $detail_search = '&TRANSACTIONID=' . urlencode($data['search_type']);
            break;
          case 'last_name_only' :
            $detail_search = '&LASTNAME=' . urlencode($data['search_type']);
            break;
          case 'last_name' :
            $search = explode(',', $data['search_type']);
            $detail_search = '&LASTNAME=' . urlencode(trim($search['0'])) . '&FIRSTNAME=' . urlencode(trim($search['1']));
            break;
          case 'invoice_id' :
            $detail_search = '&INVNUM=' . urlencode($data['search_type']);
            break;
        }
      }
      $nvpstr = '&STARTDATE=' . $startdate . '&ENDDATE=' . $enddate . '&CURRENCYCODE=EUR' . $detail_search;
      $resArray = $this->hash_call("TransactionSearch", $nvpstr);
      if($resArray['ACK'] == 'Success') {
        $result = $this->createResultArray($resArray);
      } elseif($resArray['ACK'] == 'SuccessWithWarning') {
        $this->SearchError['code'] = $resArray['L_ERRORCODE0'];
        $this->SearchError['shortmessage'] = $resArray['L_SHORTMESSAGE0'];
        $this->SearchError['longmessage'] = $resArray['L_LONGMESSAGE0'];
        $result = $this->createResultArray($resArray);
      } else {
        $this->SearchError['code'] = $resArray['L_ERRORCODE0'];
        $this->SearchError['shortmessage'] = $resArray['L_SHORTMESSAGE0'];
        $this->SearchError['longmessage'] = $resArray['L_LONGMESSAGE0'];
        $result = -1;
      }
      return $result;
    }
    /*************************************************************/
    function createResultArray($response) {
      // Stand: 29.04.2009
      $result = array();
      $n = 0;
      $flag = true;
      while($flag) {
        if(!isset($response['L_TIMESTAMP' . $n])) {
          $flag = false;
          return -1;
        }
        $result[$n]['TIMESTAMP'] = $response['L_TIMESTAMP' . $n];
        $result[$n]['TYPE'] = $response['L_TYPE' . $n];
        $result[$n]['NAME'] = $response['L_NAME' . $n];
        $result[$n]['TXNID'] = $response['L_TRANSACTIONID' . $n];
        $result[$n]['STATUS'] = $response['L_STATUS' . $n];
        $result[$n]['AMT'] = $response['L_AMT' . $n];
        $result[$n]['FEEAMT'] = $response['L_FEEAMT' . $n];
        $result[$n]['NETAMT'] = $response['L_NETAMT' . $n];
        if(!isset($response['L_TIMESTAMP' . ($n +1)]))
          $flag = false;
        $n++;
      }
      return $result;
    }
    /*************************************************************/
    function getStatusSymbol($status, $type = '', $reason = '') {
      // Stand: 29.04.2009
      switch($status) {
        case 'Reversed' :
        case 'Refunded' :
          $symbol = xtc_image(DIR_WS_ICONS . 'action_refresh_blue.gif');
          break;
        case 'Completed' :
        case 'verified' :
        case 'confirmed' :
          $symbol = xtc_image(DIR_WS_ICONS . 'icon_accept.gif');
          break;
        case 'Pending' :
          $symbol = xtc_image(DIR_WS_ICONS . 'icon_clock.gif');
          if($reason == 'authorization')
            $symbol = xtc_image(DIR_WS_ICONS . 'icon_capture.gif');
          if($reason == 'partial-capture')
            $symbol = xtc_image(DIR_WS_ICONS . 'icon_partcapture.png');
          if($reason == 'completed-capture')
            $symbol = xtc_image(DIR_WS_ICONS . 'icon_capture.gif');
            break;
        case 'Denied' :
        case 'unverified' :
        case 'unconfirmed' :
          $symbol = xtc_image(DIR_WS_ICONS . 'exclamation.png');
          break;
        case 'Unconfirmed' :
          $symbol = xtc_image(DIR_WS_ICONS . 'exclamation.png');
          break;
        case 'Payment' :
        case 'Refund';
          switch($type) {
            case 'Completed' :
              $symbol = xtc_image(DIR_WS_ICONS . 'icon_accept.gif');
              break;
            case 'Pending' :
              $symbol = xtc_image(DIR_WS_ICONS . 'icon_clock.gif');
              break;
            case 'Refunded' :
            case 'Partially Refunded';
              $symbol = xtc_image(DIR_WS_ICONS . 'action_refresh_blue.gif');
              break;
            case 'Cancelled' :
              $symbol = xtc_image(DIR_WS_ICONS . 'icon_cancel.png');
              break;
          }
          break;
        case 'Transfer' :
          switch($type) {
            case 'Completed' :
              $symbol = xtc_image(DIR_WS_ICONS . 'icon_arrow_right.gif');
              break;
          }
        case '' :
          if($type == 'new_case')
            $symbol = xtc_image(DIR_WS_ICONS . 'exclamation.png');
          break;
      }
      return $symbol;
    }
    /*************************************************************/
    function mapResponse($data) {
      // Stand: 29.04.2009
      $data_array = array(
                          'xtc_order_id' => $data['INVNUM'],
                          'txn_type' => $data['TRANSACTIONTYPE'],
                          'reason_code' => $data['REASONCODE'],
                          'payment_type' => $data['PAYMENTTYPE'],
                          'payment_status' => $data['PAYMENTSTATUS'],
                          'pending_reason' => $data['PENDINGREASON'],
                          'invoice' => $data['INVNUM'],
                          'mc_currency' => $data['CURRENCYCODE'],
                          'first_name' => $this->UTF8decode($data['FIRSTNAME']),
                          'last_name' => $this->UTF8decode($data['LASTNAME']),
                          'payer_business_name' => $this->UTF8decode($data['BUSINESS']),
                          'address_name' => $this->UTF8decode($data['SHIPTONAME']),
                          'address_street' => $this->UTF8decode($data['SHIPTOSTREET']),
                          'address_city' => $this->UTF8decode($data['SHIPTOCITY']),
                          'address_state' => $this->UTF8decode($data['SHIPTOSTATE']),
                          'address_zip' => $data['SHIPTOZIP'],
                          'address_country' => $this->UTF8decode($data['SHIPTOCOUNTRYNAME']),
                          'address_status' => $data['ADDRESSSTATUS'],
                          'payer_email' => $data['EMAIL'],
                          'payer_id' => $data['PAYERID'],
                          'payer_status' => $data['PAYERSTATUS'],
                          'payment_date' => $data['TIMESTAMP'],
                          'business' => '',
                          'receiver_email' => $data['RECEIVEREMAIL'],
                          'receiver_id' => $data['RECEIVERID'],
                          'txn_id' => $data['TRANSACTIONID'],
                          'parent_txn_id' => '',
                          'num_cart_items' => '',
                          'mc_gross' => $data['AMT'],
                          'mc_fee' => $data['FEEAMT'],
                          'mc_authorization' => $data['AMT'],
                          'payment_gross' => '',
                          'payment_fee' => '',
                          'settle_amount' => $data['SETTLEAMT'],
                          'settle_currency' => '',
                          'exchange_rate' => $data['EXCHANGERATE'],
                          'notify_version' => $data['VERSION'],
                          'verify_sign' => '',
                          'last_modified' => '',
                          'date_added' => 'now()',
                          'memo' => $data['DESC']
                         );
      return $data_array;
    }
    /*************************************************************/
    function getPaymentType($type) {
      // Stand: 29.04.2009
      if($type == '' OR strtoupper($type)=='NONE')
        return;
      if(defined('TYPE_'.strtoupper($type))) {
        return constant('TYPE_'.strtoupper($type));
      } else {
        return(ucfirst($type));
      }
    }
  /*************************************************************/
    function getStatusName($status, $type = '') {
    // Stand: 29.04.2009
      if($type == 'new_case')
        return STATUS_CASE;
      if(defined('STATUS_'.strtoupper($status))) {
        return constant('STATUS_'.strtoupper($status));
      } else {
        return(ucfirst($status));
      }
    }
    /*************************************************************/
    function admin_notification($orders_id) {
      // Stand: 04.03.2012
      global $_GET;
      include(DIR_FS_CATALOG . 'lang/' . $_SESSION['language'] . '/admin/paypal.php');
      $db_installed = false;
      //BOF - Dokuman - 2009-11-23 - replace mysql_list_tables by xtc_db_query -> PHP5.3 deprecated
      //$tables = mysql_list_tables(DB_DATABASE);
      // BOF - Tomcraft - 2010-01-20 - Fix errors where database names include a minus
      //$tables = xtc_db_query('SHOW TABLES FROM ' . DB_DATABASE);
      $tables = xtc_db_query('SHOW TABLES FROM `' . DB_DATABASE . '`');
      // EOF - Tomcraft - 2010-01-20 - Fix errors where database names include a minus
      //EOF - Dokuman - 2009-11-23 - replace mysql_list_tables by xtc_db_query -> PHP5.3 deprecated
      while($row = mysql_fetch_row($tables)) {
        if($row[0] == TABLE_PAYPAL) {
          $db_installed=true;
				break;
        }
      }
      if($db_installed==false)
        return;
      $query = "SELECT * FROM " . TABLE_PAYPAL . " WHERE xtc_order_id = '" . $orders_id . "'ORDER BY paypal_ipn_id DESC LIMIT 1";
      $query = xtc_db_query($query);
      if(xtc_db_num_rows($query)){
        $data = xtc_db_fetch_array($query);
        if(substr($data['txn_id'],0,6)!="PayPal") {
          $response = $this->GetTransactionDetails($data['txn_id']);
        } else {
          $response = array('ACK' => 'PFailure','ERROR' => $data['txn_id']);
        }
        // show transaction status
        $output = '<tr>
                     <td class="main" valign="top"><b>' . TEXT_PAYPAL_PAYMENT . ':</b><br /></td>
                     <td class="main" style="border: 1px solid; border-color: #003366; background: #fff;">';
        // show INFO
        if($response['ACK']=='Failure') {
          $output .= '<table width="300">
                        <tr>
                          <td class="main" colspan="2">' . $this->getErrorDescription($response['L_ERRORCODE0']) . '</td>
                        </tr>';
        } elseif($response['ACK']=='PFailure') {
          $output .= '<table width="300">
                        <tr>
                          <td class="main" colspan="2">' . $response['ERROR'] . '</td>
                        </tr>';
        } else {
          // authorization ?
          if($response['PAYMENTSTATUS'] == 'None' && $response['PENDINGREASON'] == 'other') {
            $response['PAYMENTSTATUS'] = 'Pending';
            $response['PENDINGREASON'] = 'authorization';
            $response['AMT'] = $response['AMT'] . ' ( ' . $data['mc_captured'] . ' Captured) ';
          }
          $output .= '<table width="300">
                        <tr>
                          <td width="10">' . $this->getStatusSymbol($response['PAYMENTSTATUS'], $response['TRANSACTIONTYPE'], $response['PENDINGREASON']) . '</dt>
                          <td class="main">' . $this->getStatusName($response['PAYMENTSTATUS'], $response['TRANSACTIONTYPE']) . ' Total: ' . $response['AMT'] . ' ' . $response['CURRENCYCODE'] . '</td>
                        </tr>
                        <tr>
                          <td width="10">' . $this->getStatusSymbol($response['PAYERSTATUS']) . '</dt>
                          <td class="main">' . $response['PAYERSTATUS'] . '(' . $response['EMAIL'] . ')' . '</td>
                        </tr>
                        <tr>
                          <td width="10" valign="top">' . $this->getStatusSymbol($response['ADDRESSSTATUS']) . '</dt>
                          <td class="main">(' . $response['ADDRESSSTATUS'] . ')<br>' . $this->mn_iconv("UTF-8", $_SESSION['language_charset'], $response['SHIPTONAME']) . '<br>' . $this->mn_iconv("UTF-8", $_SESSION['language_charset'], $response['SHIPTOSTREET']) . '<br>' . $response['SHIPTOZIP'] . ' ' . $this->mn_iconv("UTF-8", $_SESSION['language_charset'], $response['SHIPTOCITY']) . '<br>' . $this->mn_iconv("UTF-8", $_SESSION['language_charset'], $response['SHIPTOCOUNTRYNAME']) . '</td>
                        </tr>
                        <tr>
                          <td width="10" valign="top">' . xtc_image(DIR_WS_IMAGES . 'icon_info.gif') . '</dt>
                          <td class="main"><a href="' . xtc_href_link(FILENAME_PAYPAL, 'view=detail&paypal_ipn_id=' . $data['paypal_ipn_id']) . '" target="_blank">' . TEXT_PAYPAL_DETAIL . '</td>
                        </tr>';
        }
        $output .= '    </table>
                      </td>
                    </tr>';
        echo $output;
      }
    }
    /*************************************************************/
    function getErrorDescription($err) {
      // Stand: 29.04.2009
      //return constant(strtoupper($err));
      $err = $_SESSION['reshash']['FORMATED_ERRORS'];
      unset($_SESSION['reshash']['FORMATED_ERRORS']);
      return strtoupper($err);
    }
    /*************************************************************/
    function UTF8decode($string){
      // Stand: 29.04.2009
      // Session vorhanden
      if($this->detectUTF8($string))
        $string=$this->mn_iconv('UTF-8', $_SESSION['language_charset'], $string);
      return($string);
    }
    /*************************************************************/
    function detectUTF8($string){
      // Stand: 29.04.2009
      return preg_match('%(?:
                        [\xC2-\xDF][\x80-\xBF]
                        |\xE0[\xA0-\xBF][\x80-\xBF]
                        |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}
                        |\xED[\x80-\x9F][\x80-\xBF]
                        |\xF0[\x90-\xBF][\x80-\xBF]{2}
                        |[\xF1-\xF3][\x80-\xBF]{3}
                        |\xF4[\x80-\x8F][\x80-\xBF]{2}
                        )+%xs', $string);
    }
    /*************************************************************/
    function mn_iconv($t1,$t2,$string){
      // Stand: 29.04.2009
      if(function_exists('iconv')) {
        return iconv($t1, $t2, $string);
      }
      /// Kein iconv im PHP
      if($t2 == "UTF-8") {
        // nur als Ersatz für das iconv und nur in eine richtung 1251 to UTF8
        //ISO 8859-1 to UTF-8
        if(function_exists('utf8_encode')) {
          return utf8_encode($string);
        } else {
          $string=preg_replace("/([\x80-\xFF])/e","chr(0xC0|ord('\\1')>>6).chr(0x80|ord('\\1')&0x3F)",$string);
          return($string);
        }
      } elseif($t1 == "UTF-8") {
        //UTF-8 to ISO 8859-1
        if(function_exists('utf8_decode')) {
          return utf8_decode($string);
        } else {
          $string=preg_replace("/([\xC2\xC3])([\x80-\xBF])/e","chr(ord('\\1')<<6&0xC0|ord('\\2')&0x3F)",$string);
          return($string);
        }
      } else {
        // keine Konvertierung möglich
        return($string);
      }
    }
  }
?>
