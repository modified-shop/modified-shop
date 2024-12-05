<?php
/**
 *
 * @package    micropayment
 * @copyright  Copyright (c) 2022 Micropayment GmbH (http://www.micropayment.de)
 * @author     micropayment GmbH (TE) <support@micropayment.de>
 */
require_once(dirname(__FILE__).'/class.micropayment_helper.php');
include_once(dirname(__FILE__).'/../../../lang/'.$_SESSION['language'].'/modules/payment/mcp_service.php');

class micropayment_method extends micropayment_helper
{
    var $code;
    var $title;
    var $title_extern;
    var $info;
    var $tmpOrders;
    var $tmpStatus;
    var $form_action_url;
    var $_check;

    var $enabled = true;
    var $version = '2.2.9';
    var $rslcode = 'r120';
    var $get_url_called = false;

    static $versionInfoShow = false;
    static $registerInfoShow = false;


    function __construct()
    {
        global $order;
        
        $this->form_action_url = 'not_used';
        $this->tmpOrders = true;
        $this->tmpStatus = ((defined('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PENDING_PAYMENT_ID')) ? MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PENDING_PAYMENT_ID : '');
        if (isset($this->code) && $this->code != '') {
          $this->check_enabled();
          $this->check();
        }

        if (!defined('RUN_MODE_ADMIN') && is_object($order)) {
          $this->update_status();
        }
    }

    function check()
    {

        if(isset($_SESSION['customers_status']) &&
            isset($_SESSION['customers_status']['customers_status_id']) &&
            $_SESSION['customers_status']['customers_status_id'] == 0) {
            if($this->check_is_service_installed() && MODULE_PAYMENT_MCP_SERVICE_ACCOUNT_ID == '' && !self::$registerInfoShow) {
                echo sprintf(MODULE_PAYMENT_MCP_SERVICE_NO_ACCOUNT, MODULE_PAYMENT_MCP_SERVICE_CSS, $this->rslcode);
                self::$registerInfoShow = true;
            }
            if(defined(self::CONFIG_NAME_CURRENT_VERSION)) {
                if(version_compare($this->version,constant(self::CONFIG_NAME_CURRENT_VERSION),'<') && !self::$versionInfoShow) {
                    echo sprintf(MODULE_PAYMENT_MCP_SERVICE_NEW_VERSION,MODULE_PAYMENT_MCP_SERVICE_CSS,constant(self::CONFIG_NAME_CURRENT_VERSION));
                    self::$versionInfoShow = true;
                }
            }
        }

        if ($this->check_is_service_installed()) {
            $check_query = xtc_db_query("SELECT `configuration_value` FROM " . TABLE_CONFIGURATION . " WHERE `configuration_key` = 'MODULE_PAYMENT_" . strtoupper($this->code) . "_STATUS'");
            $this->_check = xtc_db_num_rows($check_query);
            return $this->_check;
        }
        return false;
    }

    function check_enabled()
    {
        $check_query = xtc_db_query("SELECT `configuration_value` FROM " . TABLE_CONFIGURATION . " WHERE `configuration_key` = 'MODULE_PAYMENT_" . strtoupper($this->code) . "_STATUS' AND configuration_value = 'True'");
        $check = xtc_db_num_rows($check_query);
        $this->enabled = ($check != 0) ? true : false;
        if(!$this->getConfig('MODULE_PAYMENT_MCP_SERVICE_ACCOUNT_ID')) {
            $this->enabled = false;
        }
        return $this->enabled;
    }

    function selection()
    {
        $selection = array(
            'id' => $this->code,
            'module' => (!empty($this->title_extern)) ? $this->title_extern : $this->title,
            'description' => $this->info
        );

        if (isset($_GET['orderid']) && is_numeric($_GET['orderid'])) {
            $check_query = xtc_db_query("SELECT orders_status FROM " . TABLE_ORDERS . " WHERE orders_id = '" . (int)$_GET['orderid'] . "' LIMIT 1");
            if ($result = xtc_db_fetch_array($check_query)) {
                if ($result['orders_status'] == MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PENDING_PAYMENT_ID) {
                    if(isset($_GET['orderid']) && in_array($this->getLastEventFromMicropaymentLog((int) $_GET['orderid']),array('new','error'))) {
                        require_once(DIR_FS_INC.'xtc_remove_order.inc.php');
                        xtc_remove_order((int) $_GET['orderid'], ((STOCK_LIMITED == 'true') ? 'on' : false));
                    }
                    unset($_SESSION['tmp_oID']);
                }
            }
        }

        return $selection;
    }

    function confirmation()
    {
        $selection = array(
            'id'          => $this->code,
            'module'      => $this->title_extern,
            'description' => $this->info
        );
        return $selection;

    }

    function javascript_validation()
    {
        return true;
    }

    function process_button()
    {

    }

    function after_process()
    {
        return false;
    }

    public function payment_action()
    {
        global $insert_id;
        $order = new order($insert_id);
        $url = $this->generateBillingUrl($order);
        //echo '<pre>'.print_r($url,true).'</pre>'; exit();
        $this->addToMicropaymentOrders($insert_id,$this->code);
        $this->addToMicropaymentLog($insert_id,'new');

        xtc_redirect($url);
    }

    function before_process()
    {
        return false;
    }

    function _after_process()
    {
        return false;
    }

    function before_send_order()
    {
        return true;
    }
    
    function update_status()
    {
        global $order;

        if (!$this->check()) {
            $this->enabled = false;
        }
        $minimumAmount = (double)$this->getConfig('MODULE_PAYMENT_'.$this->code.'_MINIMUM_AMOUNT');
        $maximumAmount = (double)$this->getConfig('MODULE_PAYMENT_'.$this->code.'_MAXIMUM_AMOUNT');
        $order_total = $order->info['total'];

        if (($minimumAmount > 0 && $order_total < $minimumAmount) || ($maximumAmount > 0 && $order_total > $maximumAmount)) {
            $this->enabled = false;
        }
    }

    function pre_confirmation_check()
    {
        if (empty($_SESSION['cart']->cartID)) {
            $_SESSION['cart']->cartID = $_SESSION['cart']->generate_cart_id();
        }
        return false;
    }

    function install()
    {
        if (!$this->check_is_service_installed()) {
          require_once(dirname(__FILE__).'/../../../lang/'.$_SESSION['language'].'/modules/payment/mcp_service.php');
          
          $lastStatusArray = xtc_db_query('SELECT MAX(`orders_status_id`) last_id FROM '.TABLE_ORDERS_STATUS);
          $t               = xtc_db_fetch_array($lastStatusArray);
          $nextId          = $t['last_id'] + 1;

          $pendingPaymentId = $nextId + 1;
          $partPayId        = $nextId + 2;
          $processingId     = $nextId + 3;
          $cancelledId      = $nextId + 4;
          $paymentReviewId  = $nextId + 5;
          $conflictId       = $nextId + 6;


          $this->_createOrderStatus($pendingPaymentId, 2, MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PENDING_PAYMENT_GERMAN_TITLE);
          $pendingPaymentId = $this->_createOrderStatus($pendingPaymentId, 1, MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PENDING_PAYMENT_ENGLISH_TITLE);

          $this->_createOrderStatus($partPayId, 2, MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PARTPAY_GERMAN_TITLE);
          $partPayId = $this->_createOrderStatus($partPayId, 1, MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PARTPAY_ENGLISH_TITLE);

          $this->_createOrderStatus($processingId, 2, MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PROCESSING_GERMAN_TITLE);
          $processingId = $this->_createOrderStatus($processingId, 1, MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PROCESSING_ENGLISH_TITLE);

          $this->_createOrderStatus($cancelledId, 2, MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_CANCELLED_GERMAN_TITLE);
          $cancelledId = $this->_createOrderStatus($cancelledId, 1, MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_CANCELLED_ENGLISH_TITLE);

          $this->_createOrderStatus($paymentReviewId, 2, MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PAYMENT_REVIEW_GERMAN_TITLE);
          $paymentReviewId = $this->_createOrderStatus($paymentReviewId, 1, MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PAYMENT_REVIEW_ENGLISH_TITLE);

          $this->_createOrderStatus($conflictId, 2, MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_CONFLICT_GERMAN_TITLE);
          $conflictId = $this->_createOrderStatus($conflictId, 1, MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_CONFLICT_ENGLISH_TITLE);
        }

        $this->createConfigParameter('MODULE_PAYMENT_MCP_SERVICE_SORT_ORDER', '0', '6','0');
//        $this->createConfigParameter(self::CONFIG_NAME_BILLING_URL_CREDITCARD, '0', '6', '0');
//        $this->createConfigParameter(self::CONFIG_NAME_BILLING_URL_DEBIT, '0', '6', '0');
//        $this->createConfigParameter(self::CONFIG_NAME_BILLING_URL_SOFORT, '0', '6', '0');
//        $this->createConfigParameter(self::CONFIG_NAME_BILLING_URL_PREPAY, '0', '6', '0');
//        $this->createConfigParameter(self::CONFIG_NAME_REFRESH_INTERVAL, '0', '6', '0');
//        $this->createConfigParameter(self::CONFIG_NAME_CURRENT_VERSION, '2.1.0', '6', '0');
        $this->createConfigParameter('MODULE_PAYMENT_MCP_SERVICE_ACCOUNT_ID', '', '6', '0','');
        $this->createConfigParameter('MODULE_PAYMENT_MCP_SERVICE_ACCESS_KEY', '', '6', '0','');
        $this->createConfigParameter('MODULE_PAYMENT_MCP_SERVICE_PROJECT_CODE', '', '6', '0','');
        $this->createConfigParameter('MODULE_PAYMENT_MCP_SERVICE_PAYTEXT', '#ORDER#', '6', '0','');
        $this->createConfigParameter('MODULE_PAYMENT_MCP_SERVICE_THEME', 'x1', '6', '0','');
        $this->createConfigParameter('MODULE_PAYMENT_MCP_SERVICE_TESTMODE', 'True', '6', '0', 'xtc_cfg_select_option(array(\'True\',\'False\'),');
        $this->createConfigParameter('MODULE_PAYMENT_MCP_SERVICE_GFX', '', '6', '0','');
        $this->createConfigParameter('MODULE_PAYMENT_MCP_SERVICE_BGCOLOR', '', '6', '0','');
        $this->createConfigParameter('MODULE_PAYMENT_MCP_SERVICE_BGGFX', '', '6', '0','');
        $this->createConfigParameter('MODULE_PAYMENT_MCP_SERVICE_SECRET_FIELD', md5(time()), '6', '0','');
        $this->createConfigParameter('MODULE_PAYMENT_MCP_SERVICE_SECRET_FIELD_VALUE',  md5(time()+rand(10000,99999999)) , '6', '0','');
        $this->createConfigParameter('MODULE_PAYMENT_MCP_SERVICE_STATUS', 'False', '6', '1', 'xtc_cfg_select_option(array(\'True\',\'False\'),');
        $this->createConfigParameter('MODULE_PAYMENT_MCP_SERVICE_EXPIRE_DAYS', '30', '6', '0','');
        $this->createConfigParameter('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PENDING_PAYMENT_ID', $pendingPaymentId ,  '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name');
        $this->createConfigParameter('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PROCESSING_ID', $processingId ,  '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name');
        $this->createConfigParameter('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_CANCELLED_ID', $cancelledId ,  '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name');
        $this->createConfigParameter('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PAYMENT_REVIEW_ID', $paymentReviewId ,  '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name');
        $this->createConfigParameter('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_CONFLICT_ID', $conflictId,  '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name');
        $this->createConfigParameter('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PARTPAY_ID', $partPayId,  '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name');



        xtc_db_query("
            CREATE TABLE IF NOT EXISTS
              `micropayment_log` (
                  `id`       INT(11)     NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                  `order_id` INT(11)     NOT NULL ,
                  `auth`     VARCHAR(64) NOT NULL ,
                  `amount`   INT(11)     NOT NULL ,
                  `function` VARCHAR(10) NOT NULL ,
                  `created`  TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ,
                  INDEX (`order_id`)
              )"
        );
        xtc_db_query("
            CREATE TABLE IF NOT EXISTS
              `micropayment_orders` (
                  `order_id`        INT(11)     NOT NULL PRIMARY KEY,
                  `payment_method`  VARCHAR(20) NOT NULL,
                  `createdon`         TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP

              )"
        );

    }


    function check_is_service_installed()
    {
        $check_query = xtc_db_query("SELECT `configuration_value` FROM " . TABLE_CONFIGURATION . " WHERE `configuration_key` = 'MODULE_PAYMENT_MCP_SERVICE_STATUS'");
        return (xtc_db_num_rows($check_query) > 0) ? true : false;
    }

    function keys()
    {
        return array(
            'MODULE_PAYMENT_MCP_SERVICE_STATUS',
            'MODULE_PAYMENT_MCP_SERVICE_ACCOUNT_ID',
            'MODULE_PAYMENT_MCP_SERVICE_ACCESS_KEY',
            'MODULE_PAYMENT_MCP_SERVICE_PROJECT_CODE',
            'MODULE_PAYMENT_MCP_SERVICE_TESTMODE',
            'MODULE_PAYMENT_MCP_SERVICE_PAYTEXT',
            'MODULE_PAYMENT_MCP_SERVICE_THEME',
            'MODULE_PAYMENT_MCP_SERVICE_GFX',
            'MODULE_PAYMENT_MCP_SERVICE_BGGFX',
            'MODULE_PAYMENT_MCP_SERVICE_BGCOLOR',
            'MODULE_PAYMENT_MCP_SERVICE_EXPIRE_DAYS',
            'MODULE_PAYMENT_MCP_SERVICE_SECRET_FIELD',
            'MODULE_PAYMENT_MCP_SERVICE_SECRET_FIELD_VALUE',
            'MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PENDING_PAYMENT_ID',
            'MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PROCESSING_ID',
            'MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_CANCELLED_ID',
            'MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PAYMENT_REVIEW_ID',
            'MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_CONFLICT_ID',
            'MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PARTPAY_ID'
        );
    }


    function remove()
    {
        if ($this->isLastModul()) {
            xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE `configuration_key` LIKE 'MODULE_PAYMENT_MCP_SERVICE_%'");
        }
    }
}
