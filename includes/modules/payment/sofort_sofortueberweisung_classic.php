<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
 	 based on:
	  (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
	  (c) 2002-2003 osCommerce - www.oscommerce.com
	  (c) 2001-2003 TheMedia, Dipl.-Ing Thomas Plänkers - http://www.themedia.at & http://www.oscommerce.at
	  (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com
    (c) 2010 Payment Network AG - http://www.payment-network.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// include needed classes
require_once(DIR_FS_EXTERNAL.'sofort/classes/sofortLibSofortueberweisungClassic.inc.php');
require_once(DIR_FS_EXTERNAL.'sofort/classes/SofortLibPayment.php');
require_once(DIR_FS_EXTERNAL.'sofort/core/fileLogger.php');

class sofort_sofortueberweisung_classic extends SofortLibPayment {

	var $code, $title, $description, $enabled;

  function __construct() {
    $this->SofortPayment();

    // logger
    $this->logger = new FileLogger();
    $this->logger->setLogfilePath(DIR_FS_LOG.'sofort_'.date('Y-m-d').'.log');
    $this->logger->setErrorLogfilePath(DIR_FS_LOG.'sofort_error_'.date('Y-m-d').'.log');
    $this->logger->setWarningsLogfilePath(DIR_FS_LOG.'sofort_warning_'.date('Y-m-d').'.log');
	}


  function _payment_action() {
    global $order, $xtPrice, $insert_id;

    if (!isset($insert_id) || $insert_id == '') {
      $insert_id = $_SESSION['tmp_oID'];
    }

    $this->_payment_data();

    // prepare call
    $this->Sofortueberweisung = new SofortLibSofortueberweisungClassic(constant('MODULE_PAYMENT_'.strtoupper($this->code).'_USER_ID'),
                                                                       constant('MODULE_PAYMENT_'.strtoupper($this->code).'_PROJECT_ID'),
                                                                       constant('MODULE_PAYMENT_'.strtoupper($this->code).'_PROJECT_PASS'),
                                                                       constant('MODULE_PAYMENT_'.strtoupper($this->code).'_HASH_ALGORITHM'));

    // set Logging
    $this->Sofortueberweisung->setLogger($this->logger);
    if ($this->logging === true) {
      $this->Sofortueberweisung->setLogEnabled();
    }

    $this->Sofortueberweisung->setAmount($this->data['amount']);
    $this->Sofortueberweisung->setCurrencyCode($this->data['currency']);
    $this->Sofortueberweisung->setSenderCountryId(strtoupper($order->billing['country']['iso_code_2']));
    $this->Sofortueberweisung->setLanguageCode(strtoupper($_SESSION['language_code']));
    $this->Sofortueberweisung->setReason($this->data['reason_1'], $this->data['reason_2']);
    $this->Sofortueberweisung->setOrderID($insert_id);
    $this->Sofortueberweisung->setCustomerID($_SESSION['customer_id']);
    $this->Sofortueberweisung->setSuccessUrl($this->data['success_url'], true);
    $this->Sofortueberweisung->setAbortUrl($this->data['abort_url']);
    $this->Sofortueberweisung->setNotificationUrl($this->data['callback_url']);
    $this->Sofortueberweisung->setCallbackIdentifier(32);
    $this->Sofortueberweisung->setEncoding($_SESSION['language_charset']);
    $this->Sofortueberweisung->setVersion($this->version);

		$paymentUrl = $this->Sofortueberweisung->getPaymentUrl();

    // write some variables to session
    $_SESSION['sofort'][$this->code]['total'] = $this->data['amount'];
    $_SESSION['sofort'][$this->code]['cartID'] = $_SESSION['cart']->cartID;
    $_SESSION['sofort'][$this->code]['oID'] = $insert_id;
    $_SESSION['sofort'][$this->code]['tID'] = $this->Sofortueberweisung->getUserVariable(5);

    if ($this->tmpOrders === true) {
      // update status
      $order_status_id = constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TMP_STATUS_ID');
      if ($order_status_id < 0) {
        $order_status_id = $this->_get_orders_status($insert_id);
      }
      $comments = 'TransactionID: ' . $_SESSION['sofort'][$this->code]['tID'];
      $this->update_sofort_status($insert_id, $order_status_id, $comments, true);
    }

    // redirected to $paymentUrl
    xtc_redirect($paymentUrl);
  }


	function install() {
	  $this->install_default();
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_".strtoupper($this->code)."_USER_ID', '',  '6', '4', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_".strtoupper($this->code)."_PROJECT_ID', '',  '6', '4', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_".strtoupper($this->code)."_PROJECT_PASS', '',  '6', '4', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_".strtoupper($this->code)."_NOTIFY_PASS', '',  '6', '4', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_".strtoupper($this->code)."_KS_STATUS', 'False', '6', '3', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_".strtoupper($this->code)."_HASH_ALGORITHM', 'sha1',  '6', '6', 'xtc_cfg_select_option(array(\'md5\',\'sha1\',\'sha256\',\'sha512\'), ', now())");
	}


	function remove() {
		xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode("', '", $this->keys()) . "')");
	  xtc_db_query("DROP TABLE `".$this->code."`");
	}


	function keys() {
	  $keys = $this->keys_default();
	  $keys[1] = 'MODULE_PAYMENT_'.strtoupper($this->code).'_KS_STATUS';
	  $keys[2] = 'MODULE_PAYMENT_'.strtoupper($this->code).'_USER_ID';
	  $keys[3] = 'MODULE_PAYMENT_'.strtoupper($this->code).'_PROJECT_ID';
	  $keys[4] = 'MODULE_PAYMENT_'.strtoupper($this->code).'_PROJECT_PASS';
	  $keys[5] = 'MODULE_PAYMENT_'.strtoupper($this->code).'_NOTIFY_PASS';
	  $keys[6] = 'MODULE_PAYMENT_'.strtoupper($this->code).'_HASH_ALGORITHM';

    ksort($keys);
    $keys = array_values($keys);

		return $keys;
	}

}
?>