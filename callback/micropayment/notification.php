<?php
/**
 *
 * @package    micropayment
 * @copyright  Copyright (c) 2022 Micropayment GmbH (http://www.micropayment.de)
 * @author     micropayment GmbH (TE) <support@micropayment.de>
 */
chdir('../../');
require_once('includes/application_top.php');
$method_class_file = DIR_FS_EXTERNAL.'micropayment/class.micropayment_method.php';
require_once($method_class_file);

define('MODULE_PAYMENT_MCP_NOTIFICATION_MESSAGE_INVALID_REQUEST','INVALID_REQUEST');
define('MODULE_PAYMENT_MCP_NOTIFICATION_MESSAGE_INVALID_PARAMETER','INVALID_REQUEST %s');
define('MODULE_PAYMENT_MCP_NOTIFICATION_STATUS_OK_URL','STATUS=%s' . PHP_EOL . 'FORWARD=%s' . PHP_EOL .' URL=%s' . PHP_EOL . 'TARGET=_top');
define('MODULE_PAYMENT_MCP_NOTIFICATION_STATUS_OK','STATUS=%s');
define('MODULE_PAYMENT_MCP_NOTIFICATION_STATUS_ERROR','STATUS=%s' . PHP_EOL . 'MESSAGE=%s');
define('MODULE_PAYMENT_MCP_NOTIFICATION_STATUS_ERROR_WORKFLOW','STATUS=ERROR' . PHP_EOL . 'MESSAGE=EVENT-WORKFLOW-ERROR: %s -> %s');

define('MODULE_PAYMENT_MCP_NOTIFICATION_MESSAGE_BILLING','Payment complete. %s %s Auth %s');
define('MODULE_PAYMENT_MCP_NOTIFICATION_MESSAGE_INIT','Prepayment, outstanding. Deadline till %s');
define('MODULE_PAYMENT_MCP_NOTIFICATION_MESSAGE_STORNO','Order canceled. Auth %s');
define('MODULE_PAYMENT_MCP_NOTIFICATION_MESSAGE_REFUND','Order refunded. Refund amount: %s %s. Auth %s');
define('MODULE_PAYMENT_MCP_NOTIFICATION_MESSAGE_EXPIRE','Payment deadline has expired.');
define('MODULE_PAYMENT_MCP_NOTIFICATION_MESSAGE_BACKPAY','Redeposit of %s %s. Auth %s');
define('MODULE_PAYMENT_MCP_NOTIFICATION_MESSAGE_PAYIN','Receipt of payment from %s %s. Auth %s');
define('MODULE_PAYMENT_MCP_NOTIFICATION_MESSAGE_CHANGE','Payment has been reducted by  %s %s, paid: %s %s, still open is %s %s. Auth %s');
define('MODULE_PAYMENT_MCP_NOTIFICATION_MESSAGE_EVENT_CONFLICT','EVENT-WORKFLOW-FAILURE: The logical cycle has been interrupted . %s cannot follow %s. Auth Code: %s');
define('MODULE_PAYMENT_MCP_NOTIFICATION_MESSAGE_INFO','ERR: %s - %s');


if (defined('MODULE_PAYMENT_MCP_SERVICE_STATUS') 
    && isset($_REQUEST['function']) 
    && $_REQUEST['function'] == 'test'
    )
{
    require_once(DIR_FS_INC.'get_database_version.inc.php');
    $db_version  = get_database_version();
    $version     = $db_version['full'];
    $accKey      = MODULE_PAYMENT_MCP_SERVICE_ACCESS_KEY;
    $accId       = MODULE_PAYMENT_MCP_SERVICE_ACCOUNT_ID;
    $secretField = MODULE_PAYMENT_MCP_SERVICE_SECRET_FIELD;
    $secretValue = MODULE_PAYMENT_MCP_SERVICE_SECRET_FIELD_VALUE;
    
    $lastRefresh = '-';
    $interval = '0';
    $refreshQuery = xtc_db_query('SELECT * 
                                    FROM '.TABLE_CONFIGURATION.' 
                                   WHERE configuration_key = "MODULE_PAYMENT_MCP_SERVICE_REFRESH_INTERVAL"');
    if (xtc_db_num_rows($refreshQuery) > 0) {
      $refresh = xtc_db_fetch_array($refreshQuery);
      
      $lastRefresh = $refresh['last_modified'];
      $interval = $refresh['configuration_value'];
    }
    
    echo '<pre>';
    echo 'MICROPAYMENT GATEWAY TEST FUNCTION' . PHP_EOL;
    echo 'VERSION-SHOP: ' . $version . ' ; MOD: 2.2.9' . PHP_EOL;
    echo 'ACCOUNT-ID: ' . substr($accId,0,1).str_repeat('x',strlen($accId)-2).substr($accId,strlen($accId)-1) . PHP_EOL;
    echo 'ACCESSKEY: ' . substr($accKey,0,1).str_repeat('x',strlen($accKey)-2).substr($accKey,strlen($accKey)-1) . PHP_EOL;
    echo 'SECRET_FIELD: ' . substr($secretField,0,1).str_repeat('x',strlen($secretField)-2).substr($secretField,strlen($secretField)-1) . PHP_EOL;
    echo 'SECRET_VALUE: ' . substr($secretValue,0,1).str_repeat('x',strlen($secretValue)-2).substr($secretValue,strlen($secretValue)-1) . PHP_EOL;
    echo 'LAST_REFRESH: ' . $lastRefresh . ' ; INTERVAL: ' . $interval . 's' . PHP_EOL;
    echo '</pre>';
    exit();
}

class micropayment_callback
{
    const FORWARD          = 1;
    const FUNCTION_STORNO  = 'storno';
    const FUNCTION_BILLING = 'billing';
    const FUNCTION_BACKPAY = 'backpay';
    const FUNCTION_REFUND  = 'refund';
    const FUNCTION_INIT    = 'init';
    const FUNCTION_PAYIN   = 'payin';
    const FUNCTION_EXPIRE  = 'expire';
    const FUNCTION_CHANGE  = 'change';
    const FUNCTION_ERROR   = 'error';

    const STATUS_OK    = 'ok';
    const STATUS_ERROR = 'error';

    const REGEX_SIMPLE_TEXT = '/^([a-zA-Z0-9 .?\[\]_\-\.\:\,]+)$/';
    const REGEX_INTEGER     = '/^[\d]{1,}$/';

    var $allowedFunctions = array(
        self::FUNCTION_STORNO,
        self::FUNCTION_BACKPAY,
        self::FUNCTION_BILLING,
        self::FUNCTION_REFUND,
        self::FUNCTION_INIT,
        self::FUNCTION_PAYIN,
        self::FUNCTION_EXPIRE,
        self::FUNCTION_CHANGE,
        self::FUNCTION_ERROR
    );

    var $returnUrl     = null;
    var $returnMessage = null;
    var $returnStatus  = null;

    static function processRequest()
    {
        if(defined('MODULE_PAYMENT_MCP_SERVICE_STATUS')) {
            $obj = new self();
            $obj->process();
        }
    }

    function process()
    {
        if(!$this->verifyRequest()) {
            $this->exitWithError(MODULE_PAYMENT_MCP_NOTIFICATION_MESSAGE_INVALID_REQUEST);
        }
        $this->processFunction();
        $this->sendStatus();

        // Sending new order Emails
/*        if($this->getParam('function',self::REGEX_SIMPLE_TEXT) == self::FUNCTION_BILLING) {
            $order = $this->fetchOrder();
            $order = $order['order'];

        }*/
    }

    function sendStatus()
    {
        if($this->returnStatus == self::STATUS_OK) {
            if($this->returnUrl) {
                $result = sprintf(
                    MODULE_PAYMENT_MCP_NOTIFICATION_STATUS_OK_URL,
                    strtoupper($this->returnStatus),
                    self::FORWARD,
                    $this->returnUrl
                );
            } else {
                $result = sprintf(
                    MODULE_PAYMENT_MCP_NOTIFICATION_STATUS_OK,
                    strtoupper($this->returnStatus)
                );
            }
        } else {
            $result = sprintf(
                MODULE_PAYMENT_MCP_NOTIFICATION_STATUS_ERROR,
                strtoupper($this->returnStatus),
                urlencode($this->returnMessage)
            );
        }
        echo $result;
        exit();
    }

    /**
     * @param $key
     *
     * @param $allowed
     *
     * @return null|string
     */
    function getParam($key,$allowed)
    {
        if(isset($_REQUEST[$key])) {
            if(is_array($allowed) && in_array($_REQUEST[$key],$allowed)) {
                return $_REQUEST[$key];
            } elseif(!is_array($allowed) && preg_match($allowed,$_REQUEST[$key])) {
                return $_REQUEST[$key];
            } else {
                $this->exitWithError(sprintf(
                    MODULE_PAYMENT_MCP_NOTIFICATION_MESSAGE_INVALID_PARAMETER,
                    $key)
                );
            }
        } else {
            $this->exitWithError(sprintf(
                MODULE_PAYMENT_MCP_NOTIFICATION_MESSAGE_INVALID_PARAMETER,
                $key)
            );
        }
        $this->exitWithError(MODULE_PAYMENT_MCP_NOTIFICATION_MESSAGE_INVALID_REQUEST);
    }

    function exitWithError($error)
    {
        $this->returnStatus  = self::STATUS_ERROR;
        $this->returnMessage = $error;
        $this->sendStatus();
    }

    function exitSuccess()
    {
        $url = xtc_href_link(
            FILENAME_CHECKOUT_PROCESS,
            '',
            'SSL',
            true
        );

        echo sprintf(
            MODULE_PAYMENT_MCP_NOTIFICATION_STATUS_OK_URL,
            self::STATUS_OK,
            self::FORWARD,
            $url
        );
        exit();
    }

    function fetchOrder()
    {
        $order_query  = xtc_db_query(
            sprintf(
                'SELECT * FROM %s WHERE `orders_id` = "%s"',
                TABLE_ORDERS,
                xtc_db_prepare_input(
                    $this->getParam('orderid',
                        self::REGEX_INTEGER
                    )
                )
            )
        );

        $order = xtc_db_fetch_array($order_query);

        $total_query = xtc_db_query(
            sprintf(
                'SELECT `value` FROM %s WHERE `orders_id` = "%s"',
                TABLE_ORDERS_TOTAL,
                xtc_db_prepare_input(
                    $this->getParam('orderid',
                        self::REGEX_INTEGER
                    )
                )
            )
        );
        $total = xtc_db_fetch_array($total_query);
        $total = $total['value'];
        return array(
            'order' => $order,
            'total' => $total
        );
    }

    function verifyRequest()
    {
        $ok          = true;
        $data        = $_REQUEST;
        $secretField = MODULE_PAYMENT_MCP_SERVICE_SECRET_FIELD;
        $secretValue = MODULE_PAYMENT_MCP_SERVICE_SECRET_FIELD_VALUE;

        if(!isset($data[$secretField])) {
            $this->exitWithError(MODULE_PAYMENT_MCP_NOTIFICATION_MESSAGE_INVALID_REQUEST);
        }
        if($data[$secretField] !== $secretValue) {
            $this->exitWithError(MODULE_PAYMENT_MCP_NOTIFICATION_MESSAGE_INVALID_REQUEST);
        }

        foreach($data as $key=>$value) {
            switch($key) {
                case 'auth':
                    if(!preg_match(self::REGEX_SIMPLE_TEXT,$value)) {
                        $this->exitWithError(MODULE_PAYMENT_MCP_NOTIFICATION_MESSAGE_INVALID_REQUEST);
                    }
                break;
                case 'orderid':
                    if(!preg_match(self::REGEX_INTEGER,$value)) {
                        $this->exitWithError(MODULE_PAYMENT_MCP_NOTIFICATION_MESSAGE_INVALID_REQUEST);
                    }
                break;
                case 'amount':
                    if(!preg_match(self::REGEX_INTEGER,$value)) {
                        $this->exitWithError(MODULE_PAYMENT_MCP_NOTIFICATION_MESSAGE_INVALID_REQUEST);
                    }
                break;
                case 'function':
                    if(!in_array($value,$this->allowedFunctions)) {
                        $this->exitWithError(MODULE_PAYMENT_MCP_NOTIFICATION_MESSAGE_INVALID_REQUEST);
                    }
                break;
            }
        }
        $order_query  = xtc_db_query(
            sprintf(
                'SELECT * FROM %s WHERE `orders_id` = "%s"',
                TABLE_ORDERS,
                xtc_db_prepare_input(
                    $this->getParam(
                        'orderid',
                        self::REGEX_INTEGER
                    )
                )
            )
        );

        if($ok && (xtc_db_num_rows($order_query) != 1)) {
            $this->exitWithError(MODULE_PAYMENT_MCP_NOTIFICATION_MESSAGE_INVALID_REQUEST);
        }
        return $ok;
    }

    function processFunction()
    {


        $aOrder    = $this->fetchOrder();
        $order     = $aOrder['order'];
        $lastEvent = $this->getLastEventFromMicropaymentLog();
        $function  = $this->getParam('function',$this->allowedFunctions);

        $this->addToMicropaymentLog();

        if(!$this->checkEventState($lastEvent,$function)) {
            $this->setOrderInConflict($lastEvent);
        }

        switch($function) {
            case self::FUNCTION_BILLING:
                try {
                    $this->sendNewOrderEmail();
                } catch(Exception $e) {
                    
                }
                $customer_notification = 0;
                $order_status          = MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PROCESSING_ID;
                $comment = sprintf(
                    MODULE_PAYMENT_MCP_NOTIFICATION_MESSAGE_BILLING,
                    $this->getParam('amount',self::REGEX_INTEGER)/100,
                    $this->getParam('currency',self::REGEX_SIMPLE_TEXT),
                    $this->getParam('auth',self::REGEX_SIMPLE_TEXT)
                );
                $sql_data_array = array(
                    'orders_id'         => $this->getParam('orderid',self::REGEX_INTEGER),
                    'orders_status_id'  => $order_status,
                    'date_added'        => 'now()' ,
                    'customer_notified' => $customer_notification ,
                    'comments'          => $comment
                );
                xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
                $this->setOrderStatus($this->getParam('orderid',self::REGEX_INTEGER),$order_status);

                $this->returnStatus = self::STATUS_OK;
                $this->returnUrl    = xtc_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL',true);
            break;
            case self::FUNCTION_INIT:
                $order_status = MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PENDING_PAYMENT_ID;
                $comment      = sprintf(
                    MODULE_PAYMENT_MCP_NOTIFICATION_MESSAGE_INIT,
                    $this->getParam('expire',self::REGEX_SIMPLE_TEXT),
                    $this->getParam('auth',self::REGEX_SIMPLE_TEXT)
                );
                $sql_data_array = array(
                    'orders_id'         => $this->getParam('orderid',self::REGEX_INTEGER),
                    'orders_status_id'  => $order_status,
                    'date_added'        => 'now()' ,
                    'customer_notified' => 0,
                    'comments'          => $comment
                );
                xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
                $this->setOrderStatus($this->getParam('orderid',self::REGEX_INTEGER),$order_status);

                $this->returnStatus = self::STATUS_OK;
                $this->returnUrl    = xtc_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL',true);
            break;
            case self::FUNCTION_STORNO:
                $order_status          = MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_CANCELLED_ID;
                $comment = sprintf(
                    MODULE_PAYMENT_MCP_NOTIFICATION_MESSAGE_STORNO,
                    $this->getParam('auth',self::REGEX_SIMPLE_TEXT)
                );
                $sql_data_array = array(
                    'orders_id'         => $this->getParam('orderid',self::REGEX_INTEGER),
                    'orders_status_id'  => $order_status,
                    'date_added'        => 'now()' ,
                    'customer_notified' => 0,
                    'comments'          => $comment
                );
                xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
                $this->setOrderStatus($this->getParam('orderid',self::REGEX_INTEGER),$order_status);
                $this->returnStatus = self::STATUS_OK;
            break;
            case self::FUNCTION_REFUND:
                $order_status          = MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_REFUNDED_ID;
                $comment = sprintf(
                    MODULE_PAYMENT_MCP_NOTIFICATION_MESSAGE_REFUND,
                    ($this->getParam('amount',self::REGEX_INTEGER)/100),
                    $this->getParam('currency',self::REGEX_SIMPLE_TEXT),
                    $this->getParam('auth',self::REGEX_SIMPLE_TEXT)
                );
                $sql_data_array = array(
                    'orders_id'         => $this->getParam('orderid',self::REGEX_INTEGER),
                    'date_added'        => 'now()' ,
                    'orders_status_id'  => $order_status,
                    'customer_notified' => 0,
                    'comments'          => $comment
                );
                xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
                $this->setOrderStatus($this->getParam('orderid',self::REGEX_INTEGER),$order_status);
                $this->returnStatus = self::STATUS_OK;
            break;
            case self::FUNCTION_EXPIRE:
                $order_status = MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_CANCELLED_ID;
                $order_id     = $this->getParam('orderid',self::REGEX_INTEGER);
                $comment      = sprintf(
                    MODULE_PAYMENT_MCP_NOTIFICATION_MESSAGE_EXPIRE,
                    $this->getParam('auth',self::REGEX_SIMPLE_TEXT)
                );

                $sql_data_array = array(
                    'orders_id'         => $order_id,
                    'orders_status_id'  => $order_status,
                    'date_added'        => 'now()' ,
                    'customer_notified' => 0,
                    'comments'          => $comment
                );
                xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

                $model = new micropayment_method();
                $model->mcp_remove_order($order_id,true);
                xtc_db_query(sprintf('DELETE FROM micropayment_orders WHERE order_id = "%s"',$order_id));
                xtc_db_query(sprintf('DELETE FROM micropayment_log WHERE order_id = "%s"',$order_id));
                $this->returnStatus = self::STATUS_OK;
            break;
            case self::FUNCTION_BACKPAY:
                $order_status          = MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PAYMENT_REVIEW_ID;
                $comment = sprintf(
                    MODULE_PAYMENT_MCP_NOTIFICATION_MESSAGE_BACKPAY,
                    ($this->getParam('amount',self::REGEX_INTEGER)/100),
                    $this->getParam('currency',self::REGEX_SIMPLE_TEXT),
                    $this->getParam('auth',self::REGEX_SIMPLE_TEXT)
                );
                $sql_data_array = array(
                    'orders_id'         => $this->getParam('orderid',self::REGEX_INTEGER),
                    'orders_status_id'  => $order_status,
                    'date_added'        => 'now()' ,
                    'customer_notified' => 0,
                    'comments'          => $comment
                );
                xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
                $this->setOrderStatus($this->getParam('orderid',self::REGEX_INTEGER),$order_status);
                $this->returnStatus = self::STATUS_OK;
            break;
            case self::FUNCTION_PAYIN:
                $order_status = MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PARTPAY_ID;
                $comment = sprintf(
                    MODULE_PAYMENT_MCP_NOTIFICATION_MESSAGE_PAYIN,
                    ($this->getParam('amount',self::REGEX_INTEGER)/100),
                    $this->getParam('currency',self::REGEX_SIMPLE_TEXT),
                    $this->getParam('auth',self::REGEX_SIMPLE_TEXT)
                );

                $sql_data_array = array(
                    'orders_id'         => $this->getParam('orderid',self::REGEX_INTEGER),
                    'orders_status_id'  => $order_status,
                    'date_added'        => 'now()',
                    'customer_notified' => 0,
                    'comments'          => $comment
                );
                xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
                $this->setOrderStatus($this->getParam('orderid',self::REGEX_INTEGER),$order_status);
                $this->returnStatus = self::STATUS_OK;
            break;
            case self::FUNCTION_CHANGE:
                $order_status = MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PAYMENT_REVIEW_ID;
                $comment = sprintf(
                    MODULE_PAYMENT_MCP_NOTIFICATION_MESSAGE_CHANGE,
                    ($this->getParam('amount',self::REGEX_INTEGER)/100),$this->getParam('currency',self::REGEX_SIMPLE_TEXT),
                    ($this->getParam('paidamount',self::REGEX_INTEGER)/100),$this->getParam('currency',self::REGEX_SIMPLE_TEXT),
                    ($this->getParam('openamount',self::REGEX_INTEGER)/100),$this->getParam('currency',self::REGEX_SIMPLE_TEXT),
                    $this->getParam('auth',self::REGEX_SIMPLE_TEXT)
                );

                $sql_data_array = array(
                    'orders_id'         => $this->getParam('orderid',self::REGEX_INTEGER),
                    'orders_status_id'  => $order_status,
                    'date_added'        => 'now()' ,
                    'customer_notified' => 0,
                    'comments'          => $comment
                );
                xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
                $this->setOrderStatus($this->getParam('orderid',self::REGEX_INTEGER),$order_status);
                $this->returnStatus = self::STATUS_OK;
            break;
            case self::FUNCTION_ERROR:
                $order_status = MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PAYMENT_REVIEW_ID;
                $comment = sprintf(
                    MODULE_PAYMENT_MCP_NOTIFICATION_MESSAGE_INFO,
                    $this->getParam('errorcode',self::REGEX_SIMPLE_TEXT),
                    $this->getParam('errormessage',self::REGEX_SIMPLE_TEXT)
                );

                $sql_data_array = array(
                    'orders_id'         => $this->getParam('orderid',self::REGEX_INTEGER),
                    'orders_status_id'  => $order_status,
                    'date_added'        => 'now()' ,
                    'customer_notified' => 0,
                    'comments'          => $comment
                );
                xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
                $this->setOrderStatus($this->getParam('orderid',self::REGEX_INTEGER),$order_status);
                $this->returnStatus = self::STATUS_OK;
                break;
            default:
                $this->returnStatus = self::STATUS_ERROR;
            break;
        }
        return true;
    }

    function addToMicropaymentLog()
    {
        xtc_db_query(
            sprintf(
                'INSERT INTO micropayment_log (`order_id`,`auth`,`amount`,`function`) VALUES ("%s","%s","%s","%s")',
                xtc_db_prepare_input($this->getParam('orderid',self::REGEX_INTEGER)),
                xtc_db_prepare_input($this->getParam('auth',self::REGEX_SIMPLE_TEXT)),
                xtc_db_prepare_input($this->getParam('amount',self::REGEX_INTEGER)),
                xtc_db_prepare_input($this->getParam('function',$this->allowedFunctions))
            )
        );
    }

    function getLastEventFromMicropaymentLog()
    {
        $orderid = $this->getParam('orderid',self::REGEX_INTEGER);
        $event = xtc_db_query(sprintf('SELECT `function` FROM `micropayment_log` WHERE `order_id` = "%s" ORDER BY `created` DESC LIMIT 1',xtc_db_prepare_input($orderid)));
        $event = xtc_db_fetch_array($event);
        if(count($event)>0) {
            return $event['function'];
        } else {
            return null;
        }
    }

    function checkEventState($actualEvent,$newEvent)
    {
        return true;
        switch($actualEvent) {
            case 'new':
                if(!in_array($newEvent,array('init','billing','error'))) {
                    return false;
                }
            break;
            case self::FUNCTION_INIT:
                if(!in_array($newEvent,array('payin','expire','change','error'))) {
                    return false;
                }
            break;
            case self::FUNCTION_PAYIN:
                if(!in_array($newEvent,array('payin','billing','expire','change','error'))) {
                    return false;
                }
            break;
            case self::FUNCTION_BILLING:
                if(!in_array($newEvent,array('refund','storno','error'))) {
                    return false;
                }
            break;
            case self::FUNCTION_REFUND:
                if(!in_array($newEvent,array('storno','error'))) {
                    return false;
                }
            break;
            case self::FUNCTION_STORNO:
                if(!in_array($newEvent,array('backpay','error'))) {
                    return false;
                }
            break;
            case self::FUNCTION_BACKPAY:
                if(!in_array($newEvent,array('backpay','error'))) {
                    return false;
                }
            break;
            case self::FUNCTION_EXPIRE:
                if(!in_array($newEvent,array('refund','error'))) {
                    return false;
                }
            break;
            case self::FUNCTION_CHANGE:
                if(!in_array($newEvent,array('payin','expire','change','error'))) {
                    return false;
                }
            break;
            case self::FUNCTION_ERROR:
                return true;
            break;

        }
        return true;
    }

    function setOrderInConflict($old)
    {
        $order_id              = $this->getParam('orderid',self::REGEX_INTEGER);
        $order_status          = MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_CONFLICT_ID;
        $comment               = sprintf(
            MODULE_PAYMENT_MCP_NOTIFICATION_MESSAGE_EVENT_CONFLICT,
            $this->getParam('function',self::REGEX_SIMPLE_TEXT),
            $old,
            $this->getParam('auth',self::REGEX_SIMPLE_TEXT)
        );


        $sql_data_array = array(
            'orders_id'         => $order_id,
            'orders_status_id'  => $order_status,
            'date_added'        => 'now()' ,
            'customer_notified' => 0,
            'comments'          => $comment
        );
        xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        $this->setOrderStatus($order_id,$order_status);
        echo sprintf(
            MODULE_PAYMENT_MCP_NOTIFICATION_STATUS_ERROR_WORKFLOW,
            $old,
            $this->getParam('function',self::REGEX_SIMPLE_TEXT)
        );
        exit();
    }

    function setOrderStatus($orderId,$status)
    {
        xtc_db_query(
            sprintf(
                'UPDATE ' . TABLE_ORDERS . '
                SET `orders_status` = "%s",
                `last_modified` = NOW() WHERE `orders_id` = "%s"',
                xtc_db_prepare_input($status),
                xtc_db_prepare_input($orderId)
            )
        );
    }

    function sendNewOrderEmail()
    {

        //must be set for send_order.php (also $insert_id)
        global $smarty, $order, $insert_id, $send_by_admin, $redirect_to_admin, $messageStack;
        
        $send_by_admin = true;
        $redirect_to_admin = false;
        
        defined('COMMENT_SEND_ORDER_BY_ADMIN') OR define('COMMENT_SEND_ORDER_BY_ADMIN', 'new order email send by notification from micropayment');
        defined('SUCCESS_ORDER_SEND') OR define('SUCCESS_ORDER_SEND', 'Order confirmation sent successfully');

        $insert_id = $this->getParam('orderid',self::REGEX_INTEGER);

        if (!is_object($order)) { //$order doesnt exist if called by notification!
            require_once(DIR_FS_CATALOG.'includes/classes/order.php');
            $order = new order($this->getParam('orderid',self::REGEX_INTEGER));
        }

        if (!is_object($smarty)) { //$smarty doesnt exist if called by notification!
            $smarty = new Smarty();
        }

        include (DIR_FS_CATALOG.'send_order.php');
    }
}

micropayment_callback::processRequest();
