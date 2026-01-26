<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // fallbacks
  defined('DIR_FS_EXTERNAL') OR define('DIR_FS_EXTERNAL', DIR_FS_CATALOG.'includes/external/');
  defined('DIR_WS_EXTERNAL') OR define('DIR_WS_EXTERNAL', 'includes/external/');
  defined('DIR_FS_LOG') OR define('DIR_FS_LOG', DIR_FS_CATALOG.'log/');
  defined('DIR_WS_BASE') OR define('DIR_WS_BASE', '');
  
  // needed classes
  require_once(DIR_FS_EXTERNAL.'Teambank/autoload.php');
  require_once(DIR_FS_EXTERNAL.'Teambank/classes/TeambankStorage.php');
  
  //include needed functions
  require_once(DIR_FS_EXTERNAL.'GuzzleHttp/functions_include.php');
  require_once(DIR_FS_EXTERNAL.'GuzzleHttp/Promise/functions_include.php');
  require_once(DIR_FS_EXTERNAL.'GuzzleHttp/Psr7/functions_include.php');
  
  // language
  if (isset($_SESSION) && is_file(DIR_FS_EXTERNAL.'Teambank/lang/'.$_SESSION['language'].'.php')) {
    require_once(DIR_FS_EXTERNAL.'Teambank/lang/'.$_SESSION['language'].'.php');
  } else {
    require_once(DIR_FS_EXTERNAL.'Teambank/lang/english.php');
  }

  class TeambankPayment {

    var $code;
    var $version = '1.32';
    var $webshopId;
    var $token;
    var $secret;
    var $loglevel;
    
    var $ecCheckout;
    var $ecMerchant;
    var $WebshopDetails;
    var $total_amount;
    
    function __construct() {}
    
    function init($class) {
      $this->code = $class;
      
      $this->webshopId = ((defined('MODULE_PAYMENT_'.strtoupper($this->code).'_SHOP_ID')) ? constant('MODULE_PAYMENT_'.strtoupper($this->code).'_SHOP_ID') : '');
      $this->token = ((defined('MODULE_PAYMENT_'.strtoupper($this->code).'_SHOP_TOKEN')) ? constant('MODULE_PAYMENT_'.strtoupper($this->code).'_SHOP_TOKEN') : '');
      $this->secret = ((defined('MODULE_PAYMENT_'.strtoupper($this->code).'_SHOP_SECRET')) ? constant('MODULE_PAYMENT_'.strtoupper($this->code).'_SHOP_SECRET') : '');
      $this->loglevel = ((defined('MODULE_PAYMENT_'.strtoupper($this->code).'_LOG_LEVEL')) ? constant('MODULE_PAYMENT_'.strtoupper($this->code).'_LOG_LEVEL') : 'error');
      
      $storage = new TeambankStorage();
      
      // mechant API
      $config = new \Teambank\EasyCreditApiV3\Configuration();
      $config->setHost('https://partner.easycredit-ratenkauf.de')
             ->setUsername($this->webshopId)
             ->setPassword($this->token)
             ->setAccessToken($this->secret);
      
      $LoggingManager = new LoggingManager(DIR_FS_LOG.'mod_teambank_transaction_%s_'.((defined('RUN_MODE_ADMIN')) ? 'admin_' : '').'%s.log', $class, strtolower($this->loglevel));
      $transactionApiInstance = new \Teambank\EasyCreditApiV3\Service\TransactionApi(
        new \Teambank\EasyCreditApiV3\Client($LoggingManager),
        $config
      );

      $this->ecMerchant = new \Teambank\EasyCreditApiV3\Integration\Merchant(
        $transactionApiInstance,
        $LoggingManager
      );

      // checkout API
      $config = new \Teambank\EasyCreditApiV3\Configuration();
      $config->setHost('https://ratenkauf.easycredit.de')
             ->setUsername($this->webshopId)
             ->setPassword($this->token)
             ->setAccessToken($this->secret);
      
      $transactionApiInstance = new \Teambank\EasyCreditApiV3\Service\TransactionApi(
        new \Teambank\EasyCreditApiV3\Client($LoggingManager),
        $config
      );
      
      $LoggingManager = new LoggingManager(DIR_FS_LOG.'mod_teambank_webshop_%s_'.((defined('RUN_MODE_ADMIN')) ? 'admin_' : '').'%s.log', $class, strtolower($this->loglevel));
      $webshopApiInstance = new \Teambank\EasyCreditApiV3\Service\WebshopApi(
        new \Teambank\EasyCreditApiV3\Client($LoggingManager),
        $config
      );
      
      $LoggingManager = new LoggingManager(DIR_FS_LOG.'mod_teambank_installment_%s_'.((defined('RUN_MODE_ADMIN')) ? 'admin_' : '').'%s.log', $class, strtolower($this->loglevel));
      $installmentplanApiInstance = new \Teambank\EasyCreditApiV3\Service\InstallmentplanApi(
        new \Teambank\EasyCreditApiV3\Client($LoggingManager),
        $config
      );

      $LoggingManager = new LoggingManager(DIR_FS_LOG.'mod_teambank_%s_'.((defined('RUN_MODE_ADMIN')) ? 'admin_' : '').'%s.log', $class, strtolower($this->loglevel));
      $this->ecCheckout = new \Teambank\EasyCreditApiV3\Integration\Checkout(
        $webshopApiInstance,
        $transactionApiInstance,
        $installmentplanApiInstance,
        $storage,
        new \Teambank\EasyCreditApiV3\Integration\Util\AddressValidator(),
        new \Teambank\EasyCreditApiV3\Integration\Util\PrefixConverter(),
        $LoggingManager
      );
      
      if (!defined('RUN_MODE_ADMIN')) {
        $this->WebshopDetails = $this->ecCheckout->getWebshopDetails();
      }
    }

    function custom() {
      global $messageStack;

      try {
        $this->ecCheckout->verifyCredentials($this->webshopId, $this->token, $this->secret);
      
        $messageStack->add_session(sprintf('%s credentials OK', $this->code), 'success');
      } catch (Exception $e) {
        $messageStack->add_session(sprintf('%s credentials invalid', $this->code));
      }
    }

    function javascript_validation() {
      return false;
    }
    
    function pre_confirmation_check() {
      
      if (isset($_GET['easycredit'])
          && $_GET['easycredit'] == 'true'
          )
      {
        $this->ecCheckout->restore();
        $TransactionInformation = $this->ecCheckout->loadTransaction();
        
        if ($TransactionInformation->getStatus() == \Teambank\EasyCreditApiV3\Model\TransactionInformation::STATUS_PREAUTHORIZED) {
          $TransactionSummary = $TransactionInformation->getDecision();
          
          $_SESSION['easycredit']['decision'] = array(
            'interest' => $TransactionSummary->getInterest(),
            'totalValue' => $TransactionSummary->getTotalValue(),
            'decisionOutcome' => $TransactionSummary->getDecisionOutcome(),
            'amortizationPlanText' => $TransactionSummary->getAmortizationPlanText(),
          );
          return true;
               
        } else {
          $this->payment_error_redirect();
        }
      }
      
      // load the selected shipping module
      require_once (DIR_WS_CLASSES . 'shipping.php');
      $shipping_modules = new shipping($_SESSION['shipping']);
      
      $this->payment_redirect();
    }

    function confirmation() {
      if (isset($_SESSION['easycredit']['decision'])
          && $_SESSION['easycredit']['decision']['amortizationPlanText'] != ''
          )
      {
        return array(
          'title' => $this->title,
          'fields' => array(
            array(
              'title' => $_SESSION['easycredit']['decision']['amortizationPlanText'],
            ),
          )
        );      
      }
      
      return false;                     
    }
  
    function process_button() {
      return false;
    }
  
    function before_process() {
      if ($this->use_real_order_id !== true) {
        $this->ecCheckout->restore();
        $this->ecCheckout->loadTransaction();
        $result = $this->ecCheckout->authorize($_SESSION['easycredit']['oID']);
        
        if ($result !== true) {
          $this->payment_error_redirect();
        }
      }
      
      return false;
    }
    
    function before_send_order() {
      global $insert_id;
      
      if ($this->use_real_order_id === true) {
        $this->ecCheckout->restore();
        
        $TransactionInformation = $this->ecCheckout->loadTransaction();
        $Transaction = $TransactionInformation->getTransaction();
        $orderDetails = $Transaction->getOrderDetails();
        $orderDetails->setOrderId($insert_id);
        
        $this->ecCheckout->update($Transaction);

        $this->ecCheckout->loadTransaction();
        $result = $this->ecCheckout->authorize($insert_id);
        
        if ($result !== true) {
          require_once(DIR_FS_INC.'xtc_remove_order.inc.php');
          xtc_remove_order((int)$insert_id, ((STOCK_LIMITED == 'true') ? 'on' : false));

          $this->payment_error_redirect();
        }
      }

      $wait = 0;
      for ($i = 0; $i <= 10; $i ++) {
        $wait += $i * 0.5;
        sleep($wait);
  
        $TransactionInformation = $this->ecCheckout->loadTransaction();
        if ($TransactionInformation->getStatus() == \Teambank\EasyCreditApiV3\Model\TransactionInformation::STATUS_AUTHORIZED) {
          return true;
        } elseif (in_array($TransactionInformation->getStatus(), array(\Teambank\EasyCreditApiV3\Model\TransactionInformation::STATUS_DECLINED, \Teambank\EasyCreditApiV3\Model\TransactionInformation::STATUS_EXPIRED))) {
          require_once(DIR_FS_INC.'xtc_remove_order.inc.php');
          xtc_remove_order((int)$insert_id, ((STOCK_LIMITED == 'true') ? 'on' : false));
          
          $this->payment_error_redirect();
        }
      }
    }
    
    function after_process() {
      global $insert_id;
      
      if (isset($this->order_status) && $this->order_status) {
        $orders_query = xtc_db_query("SELECT *
                                        FROM ".TABLE_ORDERS."
                                       WHERE orders_id = '".$insert_id."'");
        $orders = xtc_db_fetch_array($orders_query);
      
        if ($this->order_status != $orders['orders_status']) {
          $sql_data_array = array(
            'orders_id' => (int)$insert_id,
            'orders_status_id' => $this->order_status,
            'date_added' => 'now()',
          );
          xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        }
        
        xtc_db_query("UPDATE ".TABLE_ORDERS." 
                         SET orders_status = '".$this->order_status_success."' 
                       WHERE orders_id = '".(int)$insert_id."'");
        
        $sql_data_array = array (
          'orders_id' => $insert_id,
          'orders_status_id' => $this->order_status_success,
          'date_added' => 'now()',
          'customer_notified' => 0,
          'comments' => constant('TEXT_'.strtoupper($this->code).'_TBAID').' '.$_SESSION['easycredit']['storage']['transaction_id'],
        );
        xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

        $sql_data_array = array (
          'orders_id' => $insert_id,
          'tbaId' => $_SESSION['easycredit']['storage']['token'],
          'technicalTbaId' => $_SESSION['easycredit']['storage']['transaction_id'],
        );
        xtc_db_perform('easycredit', $sql_data_array);
      }
      
      $this->ecCheckout->clear();
      $this->ecCheckout->save();
      unset($_SESSION['easycredit']);
    }

    function get_error() {
      if (isset($_GET['payment_error'])) {
        $error = array(
          'title' => constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_ERROR_HEADING'),
          'error' => constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_ERROR_MESSAGE'),
        );
        return $error;
      }
    }
  
    function check() {
      if (!isset ($this->_check)) {
        if (defined('MODULE_PAYMENT_'.strtoupper($this->code).'_STATUS') && !defined('RUN_MODE_ADMIN')) {
          $this->_check = true;
        } else {
          $check_query = xtc_db_query("SELECT configuration_value 
                                         FROM ".TABLE_CONFIGURATION." 
                                        WHERE configuration_key = 'MODULE_PAYMENT_".strtoupper($this->code)."_STATUS'");
          $this->_check = xtc_db_num_rows($check_query);
        }
      }
      return $this->_check;
    }

    function payment_redirect() {
      global $order, $messageStack;
  
      $this->total_amount = $this->calculate_total();
  
      $customer = new \Teambank\EasyCreditApiV3\Model\Customer([
        'gender' => (($order->customer['gender'] == 'm') ? \Teambank\EasyCreditApiV3\Model\Customer::GENDER_MR : (($order->customer['gender'] == 'f') ? \Teambank\EasyCreditApiV3\Model\Customer::GENDER_MRS : (($order->customer['gender'] == 'd') ? \Teambank\EasyCreditApiV3\Model\Customer::GENDER_DIVERS : \Teambank\EasyCreditApiV3\Model\Customer::GENDER_NO_GENDER))),
        'firstName' => $this->data_encoding($order->customer['firstname']),
        'lastName' => $this->data_encoding($order->customer['lastname']),
        'contact' => new \Teambank\EasyCreditApiV3\Model\Contact([
          'email' => $order->customer['email_address'],
          'phoneNumber' => $order->customer['telephone'],
        ])
      ]);
  
      $invoiceAddress = new \Teambank\EasyCreditApiV3\Model\Address([
        'address' => $this->data_encoding($order->billing['street_address']),
        'additionalAddressInformation' => $this->data_encoding($order->billing['suburb']),
        'zip' => $this->data_encoding($order->billing['postcode']),
        'city' => $this->data_encoding($order->billing['city']),
        'country' => $order->billing['country']['iso_code_2'],
      ]);
  
      $shippingAddress = new \Teambank\EasyCreditApiV3\Model\ShippingAddress([
        'firstName' => $this->data_encoding($order->delivery['firstname']),
        'lastName' => $this->data_encoding($order->delivery['lastname']),
        'address' => $this->data_encoding($order->delivery['street_address']),
        'additionalAddressInformation' => $this->data_encoding($order->delivery['suburb']),
        'zip' => $this->data_encoding($order->delivery['postcode']),
        'city' => $this->data_encoding($order->delivery['city']),
        'country' => $order->delivery['country']['iso_code_2'],
      ]);
  
      $redirectLinks = new \Teambank\EasyCreditApiV3\Model\RedirectLinks([
        'urlSuccess' => $this->link_encoding(xtc_href_link(FILENAME_CHECKOUT_CONFIRMATION, 'conditions=true&easycredit=true', 'SSL')),
        'urlCancellation' => $this->link_encoding(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL')),
        'urlDenial' => $this->link_encoding(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL')),
      ]);
  
      $shopsystem = new \Teambank\EasyCreditApiV3\Model\Shopsystem([
        'shopSystemManufacturer' => 'modified eCommerce',
        'shopSystemModuleVersion' => $this->version,
      ]);
  
      $check_query = xtc_db_query("SELECT c.customers_date_added,
                                          count(o.orders_id) as total
                                     FROM ".TABLE_CUSTOMERS." c
                                LEFT JOIN ".TABLE_ORDERS." o
                                          ON o.customers_id = c.customers_id
                                    WHERE c.customers_id = '".(int)$_SESSION['customer_id']."'");
      $check = xtc_db_fetch_array($check_query);

      $customerRelationship = new \Teambank\EasyCreditApiV3\Model\CustomerRelationship([
        'customerStatus' => \Teambank\EasyCreditApiV3\Model\CustomerRelationship::CUSTOMER_STATUS_NEW_CUSTOMER,
        'customerSince' => new DateTime((strtotime($check['customers_date_added']) > 0) ? $check['customers_date_added'] : ''),
        'orderDoneWithLogin' => (($_SESSION['account_type'] == 0) ? true : false),
        'numberOfOrders' => $check['total'],
        'negativePaymentInformation' => \Teambank\EasyCreditApiV3\Model\CustomerRelationship::NEGATIVE_PAYMENT_INFORMATION_NO_PAYMENT_DISRUPTION,
        'riskyItemsInShoppingCart' => false,
      ]);
  
      $shoppingCartInformation = array();
      for ($i = 0, $n = sizeof($order->products); $i < $n; $i ++) {
        $shoppingCartInformation[] = new \Teambank\EasyCreditApiV3\Model\ShoppingCartInformationItem([
          'productName' => $this->data_encoding($order->products[$i]['name']),
          'quantity' => (int)$order->products[$i]['quantity'],
          'price' => sprintf("%01.2f", $order->products[$i]['final_price']),
          'articleNumber' => [
              new \Teambank\EasyCreditApiV3\Model\ArticleNumberItem([
                  'numberType' => 'id',
                  'number' => $order->products[$i]['id']
              ])
          ] 
        ]);
      }
      
      $payment_type = \Teambank\EasyCreditApiV3\Model\Transaction::PAYMENT_TYPE_INSTALLMENT_PAYMENT;
      if ($this->code == 'easyinvoice') {
        $payment_type = \Teambank\EasyCreditApiV3\Model\Transaction::PAYMENT_TYPE_BILL_PAYMENT;
      }
      
      $_SESSION['easycredit']['oID'] = md5(uniqid(mt_rand(), true).microtime(true));
      
      $Transaction = new \Teambank\EasyCreditApiV3\Model\Transaction([
          'orderDetails' => new \Teambank\EasyCreditApiV3\Model\OrderDetails([
            'orderValue' => sprintf("%01.2f", $this->total_amount),
            'orderId' => $_SESSION['easycredit']['oID'],
            'numberOfProductsInShoppingCart' => count($order->products),
            'invoiceAddress' => $invoiceAddress,
            'shippingAddress' => $shippingAddress,
            'shoppingCartInformation' => $shoppingCartInformation,
          ]),
          'shopsystem' => $shopsystem,
          'customer' => $customer,
          'customerRelationship' => $customerRelationship,
          'redirectLinks' => $redirectLinks,
          'paymentSwitchPossible' => false,
          'paymentType' => $payment_type,
      ]);
  
      try {
        $this->ecCheckout->start($Transaction);      
        $this->ecCheckout->save();
              
        xtc_redirect($this->ecCheckout->getRedirectUrl());
      } catch (Exception $e) {
        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
      }
    }
  
    function payment_error_redirect() {
      $this->ecCheckout->clear();
      $this->ecCheckout->save();

      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
    }
    
    function link_encoding($string) {
      $string = str_replace('&amp;', '&', $string);
      
      return $string;
    }
  
    function data_encoding($string) {
      $string = decode_htmlentities($string);
      $cur_encoding = detect_encoding($string);
      if ($cur_encoding == "UTF-8" && mb_check_encoding($string, "UTF-8")) {
        return $string;
      } else {
        return mb_convert_encoding($string, "UTF-8", $_SESSION['language_charset']);
      }
    }
  
    function calculate_total() {
      global $order, $xtPrice, $PHP_SELF;
      
      $order_backup = $order;
      $self_backup = $PHP_SELF;
      if (isset($_SESSION['payment'])) {
        $payment_backup = $_SESSION['payment'];
      }
      
      $PHP_SELF = FILENAME_CHECKOUT_CONFIRMATION;
      if (isset($_SESSION['shipping'])) {
        if (!class_exists('shipping')) {
          require_once (DIR_WS_CLASSES . 'shipping.php');
        }
        $shipping_modules = new shipping($_SESSION['shipping']);
      }
      
      if (!class_exists('order')) {
        require_once (DIR_WS_CLASSES . 'order.php');
      }
      $_SESSION['payment'] = $this->code;
      $order = new order();
      
      if (!class_exists('order_total')) {
        require_once (DIR_WS_CLASSES . 'order_total.php');
      }
      $order_total_modules = new order_total();
      $order_total = $order_total_modules->process();
      
      $total = $order->info['total'];
  
      $order = $order_backup;
      $PHP_SELF = $self_backup;    
      unset($_SESSION['payment']);
      if (isset($payment_backup)) {
        $_SESSION['payment'] = $payment_backup;
      }
  
      return $xtPrice->xtcFormat($total, false);
    }
    
    function get_order_info($orders_id) {
      $check_query = xtc_db_query("SELECT e.*
                                     FROM `easycredit` e
                                     JOIN ".TABLE_ORDERS." o
                                          ON o.orders_id = e.orders_id
                                    WHERE e.orders_id = '".(int)$orders_id."'");
      if (xtc_db_num_rows($check_query) > 0) {
        $check = xtc_db_fetch_array($check_query);
        
        try {
          $Transaction = $this->ecMerchant->getTransaction($check['technicalTbaId']);
          return $Transaction;
        } catch (Exception $e) {}
      }
      
      return false;
    }
    
  }