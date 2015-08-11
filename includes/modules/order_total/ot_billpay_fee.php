<?php
require_once(DIR_FS_CATALOG . 'includes/external/billpay/base/BillpayOT.php');
require_once(DIR_FS_CATALOG . 'includes/external/billpay/base/billpayBase.php');

class ot_billpay_fee extends BillpayOT
{
    public function __construct() {
        $this->_paymentIdentifier = billpayBase::PAYMENT_METHOD_INVOICE.'_FEE';
        $this->paymentMethod = billpayBase::PAYMENT_METHOD_INVOICE;
        $this->config['SORT_ORDER']['default'] = 91; // this needs to be unique in xtc3
        parent::__construct();
    }

    protected function isPaymentMethod() {
        $isInvoice = parent::isPaymentMethod();
        if (!$isInvoice) return false;
        $is_b2b = $this->_getDataValue("b2b");
        return $is_b2b === false;
    }
}
