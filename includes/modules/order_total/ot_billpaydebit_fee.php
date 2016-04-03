<?php
require_once(DIR_FS_CATALOG . 'includes/external/billpay/base/BillpayOT.php');
require_once(DIR_FS_CATALOG . 'includes/external/billpay/base/billpayBase.php');

class ot_billpaydebit_fee extends BillpayOT
{
    public function __construct() {
        $this->_paymentIdentifier = billpayBase::PAYMENT_METHOD_DEBIT.'_FEE';
        $this->paymentMethod = billpayBase::PAYMENT_METHOD_DEBIT;
        $this->config['SORT_ORDER']['default'] = 93; // this needs to be unique in xtc3
        parent::__construct();
    }

    function addFee() {
        $payment_method = strtolower($this->_paymentIdentifier);
        return $_SESSION['payment'] == $payment_method
               || $_POST['payment'] == $payment_method;
    }
}
