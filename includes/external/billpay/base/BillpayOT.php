<?php

/** @noinspection PhpIncludeInspection */
require_once(DIR_FS_CATALOG . 'includes/external/billpay/base/BillpayDB.php');
/** @noinspection PhpIncludeInspection */
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
    /** @var string $_paymentIdentifier - billpayBase::PAYMENT_METHOD_* + _FEE */
    protected $_paymentIdentifier;

    /** @var string $_paymentIdentifier - billpayBase::PAYMENT_METHOD_* */
    protected $paymentMethod;

    /** @var bool|null $_check - Cache for checking if module is enabled */
    var $_check = null;

    /** @var array $config */
    protected $config = array(
        'STATUS'    =>  array(
            'set_function'  =>  'xtc_cfg_select_option(array("true", "false"), ',
            'default'       =>  'true',
            'use_function'  =>  '',
        ),
        'TYPE'    =>  array(
            'set_function'  =>  'xtc_cfg_select_option(array("fest", "prozentual"), ',
            'default'       =>  'fest',
        ),
        'SORT_ORDER'    =>  array(
            'default'       =>  '90',
        ),
        'PERCENT'    =>  array(
            'default'       =>  '',
        ),
        'VALUE'    =>  array(
            'default'       =>  '',
        ),
        'TAX_CLASS'    =>  array(
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

    /** @var string $status_field defines key used to check if OT is enabled. */
    protected $status_field;

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
            case billpayBase::PAYMENT_METHOD_INVOICE:
                /** @noinspection PhpIncludeInspection */
                require_once(DIR_FS_CATALOG.'includes/modules/order_total/ot_billpay_fee.php');
                return new ot_billpay_fee($paymentMethod);
                break;
            case billpayBase::PAYMENT_METHOD_DEBIT:
                /** @noinspection PhpIncludeInspection */
                require_once(DIR_FS_CATALOG.'includes/modules/order_total/ot_billpaydebit_fee.php');
                return new ot_billpaydebit_fee($paymentMethod);
            case billpayBase::PAYMENT_METHOD_TRANSACTION_CREDIT:
                /** @noinspection PhpIncludeInspection */
                require_once(DIR_FS_CATALOG.'includes/modules/order_total/ot_billpaytc_surcharge.php');
                return new ot_billpaytc_surcharge($paymentMethod);
            case billpayBase::PAYMENT_METHOD_PAY_LATER:
                /** @noinspection PhpIncludeInspection */
                require_once(DIR_FS_CATALOG.'includes/modules/order_total/ot_z_paylater_fee.php');
                return new ot_z_paylater_fee($paymentMethod);
        }
        return null;
    }

    public function __construct()
    {
        $this->_configPrefix = "MODULE_ORDER_TOTAL_".$this->_paymentIdentifier."_";
        $this->status_field = $this->_configPrefix . 'STATUS';

        $this->code = 'ot_'.strtolower($this->_paymentIdentifier);
        $this->title = defined($this->_configPrefix.'TITLE') ? constant($this->_configPrefix.'TITLE') : '';
        $this->description = defined($this->_configPrefix.'DESCRIPTION') ? constant($this->_configPrefix.'DESCRIPTION') : '';
        $this->type = defined($this->_configPrefix.'TYPE') ? constant($this->_configPrefix.'TYPE') : '';
        $this->sort_order = defined($this->_configPrefix.'SORT_ORDER') ? constant($this->_configPrefix.'SORT_ORDER') : '';

        $this->output = array();

        $this->enabled = $this->check();
    }

    /**
     * Checks if customer is using this payment method.
     * @return bool
     */
    protected function isPaymentMethod()
    {
        $paymentMethod = $_SESSION['payment'];
        if (empty($paymentMethod))
        {
            $paymentMethod = $_POST['payment'];
        }
        return (strtoupper($paymentMethod) === $this->paymentMethod);
    }

    /**
     * Calculates fee and tax and stores info in $this->output
     * @return bool
     */
    public function process()
    {
        /** @var $xtPrice object */
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
            if (!isset($order->info['tax_groups'][TAX_ADD_TAX . "$tax_description"])) {
                $order->info['tax_groups'][TAX_ADD_TAX . "$tax_description"] = 0;
            }
            $tax_value = $this->calculateTax();
            $tax_description = xtc_get_tax_description(constant($this->_configPrefix.'TAX_CLASS'), $order->delivery['country']['id'], $order->delivery['zone_id']);
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

    public function display() {
        $value = $this->calculateFee();
        if ($this->isTaxPayer()) {
            $value += $this->calculateTax();
        }

        return $value;
    }

    public function display_formated()
    {
        /** @var $xtPrice object */
        global $xtPrice, $order;

        if ($this->type === constant('BillpayOT_TYPE_PERCENT'))
        {
            return ' '
                .$this->getFeeByCountry($order->billing['country']['iso_code_2'])
                .'% '.constant($this->_configPrefix.'FROM_TOTAL');
        }
        $value = $this->display();
        return $xtPrice->xtcFormat($value, true);
    }

    protected function calculateTax($total = NULL) {
        global $order, $xtPrice;

        $billpay_tax = xtc_get_tax_rate(constant($this->_configPrefix.'TAX_CLASS'), $order->delivery['country']['id'], $order->delivery['zone_id']);
        $value = $xtPrice->calcTax($this->calculateFee($total), $billpay_tax);
        $value = round($value, 2);
        return $value;
    }

    /**
     * Calculates fee for OT module
     * @param null $total   - (default: null) if null, gets totals from global $order
     * @return float|int
     */
    private function calculateFee($total = NULL)
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
    private function getFeeByCountry($country)
    {
        $field = ($this->type === constant('BillpayOT_TYPE_PERCENT') ? "PERCENT" : "VALUE");
        $arr = explode(";", constant($this->_configPrefix.$field));
        foreach($arr as $val)
        {
            $element = explode(":", $val);
            if($element[0] == $country)
            {
                $value = $element[1];
                $value = str_replace(array(',', ' '), array('.', ''), $value);
                return $value;
            }
        }
        return 0;
    }

    /**
     * Returns if current customer should pay tax
     * @return bool
     */
    private function isTaxPayer()
    {
        return ($_SESSION['customers_status']['customers_status_show_price_tax'] == 1
            || $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1);
    }

    /**
     * Function checks if OT module is enabled
     * @return bool|null
     */
    public function check()
    {
        if (!isset($this->_check)) {
          if (defined($this->status_field)) {
            $this->_check = true;
          } else {
            $table = TABLE_CONFIGURATION;
            $config_key = $this->status_field;
            $query = "SELECT configuration_value from $table where configuration_key = '$config_key'";
            $this->_check = BillpayDB::DBCount($query);
          }
        }
        return $this->_check;
    }

    /**
     * List of configuration keys that can be changed in backend by admin.
     * @return array
     */
    public function keys()
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
    public function install()
    {
        $configs = $this->config;
        foreach ($configs as $config_key => $val)
        {
            $table = TABLE_CONFIGURATION;
            $config_key = $this->_configPrefix.$config_key;
            $default = (isset($val['default']) ? $val['default'] : '');
            $use = (isset($val['use_function']) ? $val['use_function'] : '');
            $set = (isset($val['set_function']) ? $val['set_function'] : '');
            xtc_db_query("REPLACE INTO $table (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('$config_key', '$default', '6', '0', '$use', '$set', NOW())");
        }
        if (in_array($this->code, array('ot_billpaytc_surcharge', 'ot_z_paylater_fee', 'ot_z_paylater_total'))) {
            $this->ensureEnabled();
        }
    }

    /**
     * Event called when admin uninstalls module
     */
    public function remove()
    {
        $table = TABLE_CONFIGURATION;
        $in = implode("', '", $this->keys());
        xtc_db_query("DELETE FROM $table WHERE configuration_key IN ('$in')");
    }

    /**
     * Ensures that OT module is on enabled list.
     * If we install the OT module with parent module (like PayLater), it does not get on the list automatically.
     */
    private function ensureEnabled()
    {
        $thisFile = $this->code . '.php';
        $table = TABLE_CONFIGURATION;
        $cv = BillpayDB::DBFetchValue("SELECT configuration_value FROM $table WHERE configuration_key = 'MODULE_ORDER_TOTAL_INSTALLED'");
        if (strpos($cv, $thisFile) === false)
        {
            $newCv = $cv . ';'.$thisFile;
            xtc_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '".$newCv."', last_modified = now() where configuration_key = 'MODULE_ORDER_TOTAL_INSTALLED'");
        }
    }

    protected function _getDataValue($key)
    {
        return $_SESSION[strtolower($this->paymentMethod.'_'.$key)];
    }
}