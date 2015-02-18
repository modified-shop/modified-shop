<?php

require_once(DIR_FS_CATALOG . 'includes/external/billpay/base/BillpayDB.php');
require_once(DIR_FS_CATALOG . 'includes/external/billpay/base/billpayBase.php');

define('BillpayOT_TYPE_FLAT', 'fest');
define('BillpayOT_TYPE_PERCENT', 'prozentual');
/**
 * Class BillpayOT
 * Base class for Order Total modules
 * All the ot modules are used to allow merchant to charge a fee for using BillPay payment.
 */
class BillpayOT
{
    /** @var string $_paymentIdentifier - billpayBase::PAYMENT_METHOD_* */
    var $_paymentIdentifier;

    /** @var bool|null $_check - Cache for checking if module is enabled */
    var $_check = null;

    /** @var array $config */
    var $config = array(
        'FEE_STATUS'    =>  array(
            'set_function'  =>  'xtc_cfg_select_option(array("true", "false"), ',
            'default'       =>  'true',
            'use_function'  =>  '',
        ),
        'FEE_TYPE'    =>  array(
            'set_function'  =>  'xtc_cfg_select_option(array("fest", "prozentual"), ',
            'default'       =>  'fest',
        ),
        'FEE_SORT_ORDER'    =>  array(
            'default'       =>  '90',
        ),
        'FEE_PERCENT'    =>  array(
            'default'       =>  '',
        ),
        'FEE_VALUE'    =>  array(
            'default'       =>  '',
        ),
        'FEE_TAX_CLASS'    =>  array(
            'set_function'  =>  'xtc_cfg_pull_down_tax_classes(',
            'default'       =>  '0',
            'use_function'  =>  'xtc_get_tax_class_title',
        ),
    );

    var $code;
    var $title;
    var $description;
    var $type;
    var $enabled;
    var $sort_order;

    var $_configPrefix;
    var $output = array();

    /**
     * Returns instance of order total class for selected paymentMethod
     * @param string $paymentMethod
     * @return mixed
     * @static
     */
    static function OTInstance($paymentMethod)
    {
        $lowerPaymentMethod = strtoupper($paymentMethod);
        switch ($lowerPaymentMethod)
        {
            case constant('billpayBase_PAYMENT_METHOD_INVOICE'):
                require_once(DIR_FS_CATALOG.'includes/modules/order_total/ot_billpay_fee.php');
                return new ot_billpay_fee($paymentMethod);
                break;
            case constant('billpayBase_PAYMENT_METHOD_DEBIT'):
                require_once(DIR_FS_CATALOG.'includes/modules/order_total/ot_billpaydebit_fee.php');
                return new ot_billpaydebit_fee($paymentMethod);
            case constant('billpayBase_PAYMENT_METHOD_TRANSACTION_CREDIT'):
                require_once(DIR_FS_CATALOG.'includes/modules/order_total/billpaytc_surcharge.php');
                return new ot_billpaytc_surcharge($paymentMethod);
            case constant('billpayBase_PAYMENT_METHOD_PAY_LATER'):
                require_once(DIR_FS_CATALOG.'includes/modules/order_total/ot_billpaypaylater_fee.php');
                return new ot_billpaypaylater_fee($paymentMethod);
        }
        return null;
    }

    function BillpayOT()
    {
        $this->_configPrefix = "MODULE_ORDER_TOTAL_".$this->_paymentIdentifier."_";
        $this->code = 'ot_'.strtolower($this->_paymentIdentifier).'_fee';
        $this->title = defined($this->_configPrefix.'FEE_TITLE') ? constant($this->_configPrefix.'FEE_TITLE') : '';
        $this->description = defined($this->_configPrefix.'FEE_DESCRIPTION') ? constant($this->_configPrefix.'FEE_DESCRIPTION') : '';
        $this->type = defined($this->_configPrefix.'FEE_TYPE') ? constant($this->_configPrefix.'FEE_TYPE') : '';
        $this->enabled = constant($this->_configPrefix."FEE_STATUS") === "true";
        $this->sort_order = defined($this->_configPrefix.'FEE_SORT_ORDER') ? constant($this->_configPrefix.'FEE_SORT_ORDER') : '';
        $this->output = array();
    }

    /**
     * Checks if customer is using this payment method.
     * @return bool
     */
    function isPaymentMethod()
    {
        $paymentMethod = $_SESSION['payment'];
        if (empty($paymentMethod))
        {
            $paymentMethod = $_POST['payment'];
        }
        return (strtoupper($paymentMethod) === $this->_paymentIdentifier);
    }

    /**
     * Calculates fee and tax and stores info in $this->output
     * @return bool
     */
    function process()
    {
        global $order, $xtPrice;

        if (!$this->isPaymentMethod())
        {
            return false;
        }

        if (!$this->check())
        {
            return false;
        }

        $value = $this->calculateFee();
        if ($value <= 0)
        {
            return false;
        }

        $tax_value = 0;
        if ($this->isTaxPayer())
        {
            $tax_value = $this->calculateTax();
            $tax_description = xtc_get_tax_description(constant($this->_configPrefix.'FEE_TAX_CLASS'), $order->delivery['country']['id'], $order->delivery['zone_id']);
            $order->info['tax_groups'][TAX_ADD_TAX . "$tax_description"] += $this->calculateTax();
        }
        if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0
            && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1)
        {
            $order->info['subtotal'] += $value;
        }
        $value += $tax_value;
        $order->info['total'] += $value;
        $this->output[] = array(
            'title' => $this->title . ':',
            'text'  => $xtPrice->xtcFormat($value, true),
            'value' => $value
        );
        return true;
    }

    function display() {
        $value = $this->calculateFee();
        if ($this->isTaxPayer()) {
            $value += $this->calculateTax();
        }

        return $value;
    }

    function display_formated()
    {
        global $xtPrice, $order;

        if ($this->type === constant('BillpayOT_TYPE_PERCENT'))
        {
            return ' '
                .$this->getFeeByCountry($order->billing['country']['iso_code_2'])
                .'% '.constant($this->_configPrefix.'FEE_FROM_TOTAL');
        }
        $value = $this->display();
        return $xtPrice->xtcFormat($value, true);
    }

    function calculateTax($total = NULL) {
        global $order;

        require_once(DIR_FS_INC . 'xtc_calculate_tax.inc.php');

        $billpay_tax = xtc_get_tax_rate(constant($this->_configPrefix.'FEE_TAX_CLASS'), $order->delivery['country']['id'], $order->delivery['zone_id']);
        $value = xtc_calculate_tax($this->calculateFee($total), $billpay_tax);
        $value = round($value, 2);
        return $value;
    }

    /**
     * Calculates fee for OT module
     * @param null $total   - (default: null) if null, gets totals from global $order
     * @return float|int
     */
    function calculateFee($total = NULL)
    {
        global $order;

        if (!isset($total))
        {
            $total = $order->info['total'];
        }

        $value = $this->getFeeByCountry($order->billing['country']['iso_code_2']);
        if ($this->type === constant('BillpayOT_TYPE_PERCENT'))
        {
            $value = $total / 100 * $value;
            $value = round($value, 2);
        }
        return $value;
    }

    /**
     * Returns fee for selected country. If country is not on the list, returns 0
     * @param string $country - iso_code_2
     * @return int
     */
    function getFeeByCountry($country)
    {
        $field = ($this->type === constant('BillpayOT_TYPE_PERCENT') ? "FEE_PERCENT" : "FEE_VALUE");
        $arr = explode(";", constant($this->_configPrefix.$field));
        foreach($arr as $val)
        {
            $element = explode(":", $val);
            if($element[0] == $country)
            {
                $value = $element[1];
                return $value;
            }
        }
        return 0;
    }

    /**
     * Returns if current customer should pay tax
     * @return bool
     */
    function isTaxPayer()
    {
        return ($_SESSION['customers_status']['customers_status_show_price_tax'] == 1
            || $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1);
    }

    /**
     * Function checks if OT module is enabled
     * @return bool|null
     */
    function check()
    {
        if (!isset($this->_check)) {
            $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_".$this->_paymentIdentifier."_FEE_STATUS'");
            $this->_check = xtc_db_num_rows($check_query);
        }
        return $this->_check;
    }

    /**
     * List of configuration keys that can be changed in backend by admin.
     * @return array
     */
    function keys()
    {
        $ret = array();
        $keys = array_keys($this->config);
        foreach ($keys as $config_key)
        {
            array_push($ret, $this->_configPrefix.$config_key);
        }
        return $ret;
    }

    /**
     * Event called when admin installs module
     */
    function install()
    {
        $configs = $this->config;
        foreach ($configs as $config_key => $val)
        {
            xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values "
                ."('".$this->_configPrefix.$config_key."', '".(isset($val['default']) ? $val['default'] : '')."', '6', '0', '".
                (isset($val['use_function']) ? $val['use_function'] : '')."', '".
                (isset($val['set_function']) ? $val['set_function'] : '')."', now())");
        }
    }

    /**
     * Event called when admin uninstalls module
     */
    function remove()
    {
        xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

}