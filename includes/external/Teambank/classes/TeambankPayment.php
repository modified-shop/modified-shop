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
  defined('TEAMBANK_PENDING_TIMEOUT') OR define('TEAMBANK_PENDING_TIMEOUT', 24);
  // seconds a task run may hold a transaction before another run may take it over
  defined('TEAMBANK_CLAIM_TIMEOUT') OR define('TEAMBANK_CLAIM_TIMEOUT', 1800);
  
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
    var $version = '1.34';
    var $webshopId;
    var $token;
    var $secret;
    var $loglevel;
    
    var $ecCheckout;
    var $ecMerchant;
    var $WebshopDetails;
    var $total_amount;
    var $authorized;
    protected static $task_support_ready = false;
    
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
      
      if (!defined('RUN_MODE_ADMIN') && !defined('RUN_MODE_TASKS')) {
        $this->WebshopDetails = $this->ecCheckout->getWebshopDetails();
      } else {
        $this->prepare_task_support();
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

      $this->authorized = false;
      // microseconds, the back-off grows by half a second per attempt
      $wait = 0;
      for ($i = 0; $i <= 10; $i ++) {
        $wait += $i * 500000;
        usleep($wait);
  
        $TransactionInformation = $this->ecCheckout->loadTransaction();
        if ($TransactionInformation->getStatus() == \Teambank\EasyCreditApiV3\Model\TransactionInformation::STATUS_AUTHORIZED) {
          $this->authorized = true;
          break;
        } elseif (in_array($TransactionInformation->getStatus(), array(\Teambank\EasyCreditApiV3\Model\TransactionInformation::STATUS_DECLINED, \Teambank\EasyCreditApiV3\Model\TransactionInformation::STATUS_EXPIRED))) {
          require_once(DIR_FS_INC.'xtc_remove_order.inc.php');
          xtc_remove_order((int)$insert_id, ((STOCK_LIMITED == 'true') ? 'on' : false));
          
          $this->payment_error_redirect();
        }
      }

      // the transaction can still be authorized after the polling window, so keep
      // the order pending and hold the mail back until the status is confirmed
      if ($this->authorized !== true) {
        return true;
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
        
        $status = (($this->authorized === true) ? $this->order_status_success : $this->order_status);
        $comments = constant('TEXT_'.strtoupper($this->code).'_TBAID').' '.$_SESSION['easycredit']['storage']['transaction_id'];
        if ($this->authorized !== true) {
          $comments .= "\n".constant('TEXT_'.strtoupper($this->code).'_AUTHORIZATION_PENDING');
        }
        
        xtc_db_query("UPDATE ".TABLE_ORDERS." 
                         SET orders_status = '".$status."' 
                       WHERE orders_id = '".(int)$insert_id."'");
        
        $sql_data_array = array (
          'orders_id' => $insert_id,
          'orders_status_id' => $status,
          'date_added' => 'now()',
          'customer_notified' => 0,
          'comments' => $comments,
        );
        xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

        $sql_data_array = array (
          'orders_id' => $insert_id,
          'tbaId' => $_SESSION['easycredit']['storage']['token'],
          'technicalTbaId' => $_SESSION['easycredit']['storage']['transaction_id'],
        );
        if ($this->has_pending_support() === true) {
          // an authorized order had its confirmation sent by the checkout
          $sql_data_array['authorized'] = (($this->authorized === true) ? 1 : 0);
          $sql_data_array['mail_sent'] = (($this->authorized === true) ? 1 : 0);
        }
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
    
    function install_task_support() {
      $migrate = false;
      if ($this->has_column('authorized') !== true) {
        xtc_db_query("ALTER TABLE `easycredit` ADD `authorized` TINYINT(1) NOT NULL DEFAULT 0");
        $migrate = true;
      }
      if ($this->has_column('mail_sent') !== true) {
        xtc_db_query("ALTER TABLE `easycredit` ADD `mail_sent` TINYINT(1) NOT NULL DEFAULT 0");
        $migrate = true;
      }
      if ($this->has_column('claimed') !== true) {
        xtc_db_query("ALTER TABLE `easycredit` ADD `claimed` DATETIME DEFAULT NULL");
      }

      if ($migrate === true) {
        // An order still parked on the temporary status of its own module is the only
        // kind worth a status lookup. The status has to be compared per module: a
        // status number that means "open" for one of them may mean something else
        // for the other.
        //
        // A module whose temporary and success status are the same, which is what
        // both of them ship with, says nothing through the status at all. There is no
        // other record of how the checkout ended either: it leaves a mail flag only
        // when SEND_EMAILS is on. Its orders all count as history, so an open one
        // waits for the merchant instead of the task mailing a customer, or a
        // merchant copy, a second time.
        $pending_status = array();
        foreach (array('easycredit', 'easyinvoice') as $module) {
          $constant = 'MODULE_PAYMENT_'.strtoupper($module).'_ORDER_STATUS_ID';
          $success_constant = 'MODULE_PAYMENT_'.strtoupper($module).'_ORDER_STATUS_SUCCESS_ID';
          if (!defined($constant) || (int)constant($constant) < 1) {
            continue;
          }
          if (defined($success_constant) && (int)constant($success_constant) === (int)constant($constant)) {
            continue;
          }
          $pending_status[] = "(o.payment_method = '".$module."'
                                AND o.orders_status = '".(int)constant($constant)."')";
        }

        // Everything else is history and keeps its mail flag set: whatever it
        // received, it received before the task existed. An order that is still open
        // never had its confirmation at all, since holding that back is exactly what
        // the pending path does, so both flags stay clear and the task finishes it.
        xtc_db_query("UPDATE `easycredit` e,
                             ".TABLE_ORDERS." o
                         SET e.authorized = 1,
                             e.mail_sent = 1
                       WHERE o.orders_id = e.orders_id
                         ".((count($pending_status) > 0) ? "AND NOT (".implode("
                              OR ", $pending_status).")" : ""));
      }

      // without the key the cancel status silently stays on the current one, and the
      // administration form has nothing to configure it with
      foreach (array('easycredit', 'easyinvoice') as $module) {
        if (!defined('MODULE_PAYMENT_'.strtoupper($module).'_STATUS')) {
          continue;
        }

        $check_query = xtc_db_query("SELECT configuration_key
                                       FROM ".TABLE_CONFIGURATION."
                                      WHERE configuration_key = 'MODULE_PAYMENT_".strtoupper($module)."_ORDER_STATUS_CANCEL_ID'");
        if (xtc_db_num_rows($check_query) < 1) {
          xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_PAYMENT_".strtoupper($module)."_ORDER_STATUS_CANCEL_ID', '".DEFAULT_ORDERS_STATUS_ID."', '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
        }
      }

      xtc_db_query("INSERT INTO ".TABLE_SCHEDULED_TASKS."
                      (time_next, time_offset, time_regularity, time_unit, status, edit, tasks)
                    VALUES
                      (0, 0, 5, 'm', 1, 0, 'easycredit_txstatus')
                    ON DUPLICATE KEY UPDATE
                      time_regularity = VALUES(time_regularity),
                      time_unit = VALUES(time_unit),
                      status = VALUES(status),
                      edit = VALUES(edit)");
    }

    function remove_task_support() {
      // both modules share the task, so it only goes when the last one is removed
      $check_query = xtc_db_query("SELECT configuration_key
                                     FROM ".TABLE_CONFIGURATION."
                                    WHERE configuration_key IN ('MODULE_PAYMENT_EASYCREDIT_STATUS', 'MODULE_PAYMENT_EASYINVOICE_STATUS')");
      if (xtc_db_num_rows($check_query) < 1) {
        xtc_db_query("DELETE FROM ".TABLE_SCHEDULED_TASKS."
                            WHERE tasks = 'easycredit_txstatus'");
      }
    }

    function prepare_task_support() {
      // an installation updated without reinstalling the module has neither, so the
      // administration and the task itself bring them up to date
      if (self::$task_support_ready === true) {
        return true;
      }

      $this->install_task_support();

      if ($this->has_pending_support() !== true) {
        return false;
      }

      self::$task_support_ready = true;
      return true;
    }

    function has_column($column) {
      $check_query = xtc_db_query("SHOW COLUMNS FROM `easycredit` LIKE '".$column."'");
      return (xtc_db_num_rows($check_query) > 0);
    }

    function has_pending_support() {
      return ($this->has_column('authorized') === true
              && $this->has_column('mail_sent') === true
              && $this->has_column('claimed') === true
              );
    }

    function process_pending_transactions() {
      if ($this->prepare_task_support() !== true) {
        return false;
      }

      // the column names are the wrong way round: tbaId holds the technical id the
      // checkout endpoint expects, technicalTbaId holds the merchant transaction id
      $pending_query = xtc_db_query("SELECT e.orders_id,
                                            e.tbaId,
                                            e.mail_sent,
                                            o.payment_method,
                                            o.orders_status,
                                            o.date_purchased
                                       FROM `easycredit` e
                                       JOIN ".TABLE_ORDERS." o
                                            ON o.orders_id = e.orders_id
                                      WHERE e.authorized = 0
                                   ORDER BY o.payment_method,
                                            e.orders_id");

      require_once(DIR_FS_INC.'send_order_mail.inc.php');

      $modules = array('easycredit', 'easyinvoice');
      $deadline = time() - (TEAMBANK_PENDING_TIMEOUT * 3600);
      $initialized = '';

      while ($pending = xtc_db_fetch_array($pending_query)) {
        if (!in_array($pending['payment_method'], $modules)) {
          continue;
        }

        if ($initialized != $pending['payment_method']) {
          if (!defined('MODULE_PAYMENT_'.strtoupper($pending['payment_method']).'_STATUS')) {
            continue;
          }
          $this->load_language($pending['payment_method']);
          $this->init($pending['payment_method']);
          $initialized = $pending['payment_method'];
        }

        // the merchant endpoint reports the billing status, only the checkout
        // endpoint tells whether the authorization itself went through
        $status = false;
        $failed = false;
        $unanswered = false;
        try {
          $TransactionInformation = $this->ecCheckout->loadTransaction($pending['tbaId']);
          $status = $TransactionInformation->getStatus();
        } catch (\Teambank\EasyCreditApiV3\Integration\InitializationException $e) {
          // OPEN, DECLINED or EXPIRED, the transaction will not complete any more
          $failed = true;
        } catch (Exception $e) {
          // No answer at all, which is not the same as a negative one: the service may
          // be down, or the transaction may be gone for good and answer 404 forever.
          $unanswered = true;
        }

        $expired = (strtotime($pending['date_purchased']) < $deadline);

        if ($unanswered === true) {
          // keep asking while there is time, then hand it over instead of cancelling
          // an order the provider may well have authorized
          if ($expired === true && $this->claim_pending_transaction($pending['orders_id']) === true) {
            $this->flag_pending_transaction($pending, 'AUTHORIZATION_UNKNOWN');
          }
          continue;
        }

        if ($status == \Teambank\EasyCreditApiV3\Model\TransactionInformation::STATUS_AUTHORIZED) {
          if ($this->claim_pending_transaction($pending['orders_id']) === true) {
            $this->confirm_pending_transaction($pending);
          }
        } elseif ($failed === true || $expired === true) {
          if ($this->claim_pending_transaction($pending['orders_id']) === true) {
            $this->cancel_pending_transaction($pending);
          }
        }
      }

      return true;
    }

    function claim_pending_transaction($orders_id) {
      // Two cron runs can overlap. A single conditional update is atomic on every
      // engine the shop supports, so exactly one of them takes the row and the other
      // comes away empty. The reservation carries a timestamp rather than a state, so
      // a run that dies halfway through, on a broken mail template or a timeout, does
      // not lock the order out for good: the next run past the window takes it on.
      xtc_db_query("UPDATE `easycredit`
                       SET claimed = now()
                     WHERE orders_id = '".(int)$orders_id."'
                       AND authorized = 0
                       AND (claimed IS NULL
                            OR claimed < '".date('Y-m-d H:i:s', (time() - TEAMBANK_CLAIM_TIMEOUT))."'
                            )");

      return (xtc_db_affected_rows() > 0);
    }

    function order_status_unchanged($pending) {
      // The administration can cancel or otherwise move an order while it waits here,
      // and xtc_reverse_order() does not touch this table. Writing the success status
      // over that would reopen the order without undoing anything else it did.
      $orders_query = xtc_db_query("SELECT orders_status
                                      FROM ".TABLE_ORDERS."
                                     WHERE orders_id = '".(int)$pending['orders_id']."'");
      if (xtc_db_num_rows($orders_query) < 1) {
        return false;
      }
      $orders = xtc_db_fetch_array($orders_query);

      return ((int)$orders['orders_status'] === (int)$pending['orders_status']);
    }

    function flag_pending_transaction($pending, $key) {
      // leave the order alone and say so in its history, the merchant decides
      $orders_query = xtc_db_query("SELECT orders_status
                                      FROM ".TABLE_ORDERS."
                                     WHERE orders_id = '".(int)$pending['orders_id']."'");
      $orders = ((xtc_db_num_rows($orders_query) > 0) ? xtc_db_fetch_array($orders_query) : array('orders_status' => $pending['orders_status']));

      $sql_data_array = array(
        'orders_id' => (int)$pending['orders_id'],
        'orders_status_id' => (int)$orders['orders_status'],
        'date_added' => 'now()',
        'customer_notified' => 0,
        'comments' => $this->get_task_text($pending, $key),
      );
      xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

      xtc_db_query("UPDATE `easycredit`
                       SET authorized = 2
                     WHERE orders_id = '".(int)$pending['orders_id']."'");
    }

    function confirm_pending_transaction($pending) {
      if ($this->order_status_unchanged($pending) !== true) {
        $this->flag_pending_transaction($pending, 'AUTHORIZATION_CONFLICT');
        return;
      }

      $orders_status = $this->get_task_status($pending, 'ORDER_STATUS_SUCCESS_ID');

      // send_order.php never ran for a pending order, so this is also where the
      // afterbuy export and the merchant copy happen, neither of which cares about
      // SEND_EMAILS. Only a confirmation that already went out stops it: the status
      // history cannot say so, because an ordinary status mail from the
      // administration sets customer_notified just as well.
      $sent = (($pending['mail_sent'] != 1) ? send_order_mail($pending['orders_id']) : false);
      $notified = ($sent === true && SEND_EMAILS == 'true');

      xtc_db_query("UPDATE ".TABLE_ORDERS."
                       SET orders_status = '".$orders_status."'
                     WHERE orders_id = '".(int)$pending['orders_id']."'");

      $sql_data_array = array(
        'orders_id' => (int)$pending['orders_id'],
        'orders_status_id' => $orders_status,
        'date_added' => 'now()',
        'customer_notified' => (($notified === true) ? 1 : 0),
        'comments' => $this->get_task_text($pending, 'AUTHORIZATION_CONFIRMED'),
      );
      xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

      xtc_db_query("UPDATE `easycredit`
                       SET authorized = 1,
                           mail_sent = '".(($sent === true) ? 1 : (int)$pending['mail_sent'])."'
                     WHERE orders_id = '".(int)$pending['orders_id']."'");
    }

    function cancel_pending_transaction($pending) {
      if ($this->order_status_unchanged($pending) !== true) {
        $this->flag_pending_transaction($pending, 'AUTHORIZATION_CONFLICT');
        return;
      }

      // easyCredit offers no endpoint to cancel an open transaction, it expires on
      // its own, so the order is only marked and never removed
      $orders_status = $this->get_task_status($pending, 'ORDER_STATUS_CANCEL_ID');

      xtc_db_query("UPDATE ".TABLE_ORDERS."
                       SET orders_status = '".$orders_status."'
                     WHERE orders_id = '".(int)$pending['orders_id']."'");

      $sql_data_array = array(
        'orders_id' => (int)$pending['orders_id'],
        'orders_status_id' => $orders_status,
        'date_added' => 'now()',
        'customer_notified' => 0,
        'comments' => $this->get_task_text($pending, 'AUTHORIZATION_FAILED'),
      );
      xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

      xtc_db_query("UPDATE `easycredit`
                       SET authorized = -1
                     WHERE orders_id = '".(int)$pending['orders_id']."'");
    }

    function get_task_status($pending, $key) {
      // installations updated without reinstalling the module lack the newer keys
      $constant = 'MODULE_PAYMENT_'.strtoupper($pending['payment_method']).'_'.$key;
      return ((defined($constant) && (int)constant($constant) > 0) ? (int)constant($constant) : (int)$pending['orders_status']);
    }

    function get_task_text($pending, $key) {
      $constant = 'TEXT_'.strtoupper($pending['payment_method']).'_'.$key;
      return ((defined($constant)) ? constant($constant) : '');
    }

    function load_language($class) {
      $language = ((isset($_SESSION['language'])) ? basename((string)$_SESSION['language']) : 'german');
      $language_file = DIR_FS_CATALOG.'lang/'.$language.'/modules/payment/'.$class.'.php';
      if (!is_file($language_file)) {
        $language_file = DIR_FS_CATALOG.'lang/german/modules/payment/'.$class.'.php';
      }
      if (is_file($language_file)) {
        include_once($language_file);
      }
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