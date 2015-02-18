<?php

class ot_z_paylater_fee {

    function ot_z_paylater_fee()
    {
        require_once(DIR_FS_CATALOG.'includes/external/billpay/base/billpayBase.php');
        /** @var BillpayPayLater $billpay */
        $billpay = billpayBase::PaymentInstance(constant('billpayBase_PAYMENT_METHOD_PAY_LATER'));
        $billpay->requireLang();
        $this->_paymentIdentifier = 'Z_PAYLATER_FEE';
        $this->code = "ot_z_paylater_fee";
        $this->title = constant('MODULE_PAYMENT_BILLPAY_OT_PAYLATER_FEE');
        $this->description = "";
        $this->enabled = true;
        $this->sort_order = 151;
        $this->output = array();
    }

    /**
     * Executed on checkout_payment and checkout_confirmation
     * @return bool
     */
    function process()
    {
        global $xtPrice;
        if ($_SESSION['payment'] !== "billpaypaylater") {
            return false;
        }
        $value = $_SESSION['billpaypaylater_feeamount'];
        if (empty($value)) {
            return false;
        }
        $this->output[] = array(
            'title' =>  $this->title.':',
            'text'  =>  $xtPrice->xtcFormat($value, true),
            'value' =>  $value,
        );
        return true;
    }

    function check() {
        if (!isset($this->_check)) {
            $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_".$this->_paymentIdentifier."_STATUS'");
            $this->_check = xtc_db_num_rows($check_query);
        }
        return $this->_check;
    }

    function keys() {
        return array(
            'MODULE_ORDER_TOTAL_'.$this->_paymentIdentifier.'_STATUS',
            'MODULE_ORDER_TOTAL_'.$this->_paymentIdentifier.'_SORT_ORDER',
            'MODULE_ORDER_TOTAL_'.$this->_paymentIdentifier.'_TAX_CLASS',
        );
    }

    function install() {
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_ORDER_TOTAL_".$this->_paymentIdentifier."_STATUS', 'true', '6', '0', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_".$this->_paymentIdentifier."_SORT_ORDER', '151', '6', '0', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_ORDER_TOTAL_".$this->_paymentIdentifier."_TAX_CLASS', '0', '6', '0', 'xtc_get_tax_class_title', 'xtc_cfg_pull_down_tax_classes(', now())");
        $this->ensureEnabled();
    }

    function remove() {
        xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    /**
     * Ensures that OT module is on enabled list.
     * If we install the OT module with parent module (like PayLater), it does not get on the list automatically.
     */
    function ensureEnabled()
    {
        $thisFile = $this->code . '.php';
        $cv = BillpayDB::DBFetchValue("SELECT configuration_value FROM ".TABLE_CONFIGURATION." WHERE configuration_key = 'MODULE_ORDER_TOTAL_INSTALLED'");
        if (strpos($cv, $thisFile) === false)
        {
            $newCv = $cv . ';'.$thisFile;
            xtc_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '".$newCv."', last_modified = now() where configuration_key = 'MODULE_ORDER_TOTAL_INSTALLED'");
        }
    }

}