<?php
require_once(DIR_FS_CATALOG . 'includes/external/billpay/base/BillpayOT.php');
require_once(DIR_FS_CATALOG . 'includes/external/billpay/base/billpayBase.php');

class ot_z_bpytc_fee extends BillpayOT
{
    public function __construct()
    {
        $this->_paymentIdentifier = 'Z_BPYTC_FEE';
        $this->paymentMethod = billpayBase::PAYMENT_METHOD_TRANSACTION_CREDIT;
        $this->config = array(
            'STATUS'    => $this->config['STATUS'],
            'SORT_ORDER'=> $this->config['SORT_ORDER'],
            'TAX_CLASS' => $this->config['TAX_CLASS'],
        );
        $this->config['SORT_ORDER']['default'] = 153; // must be unique for xtc3
        parent::__construct();

        /** @var BillPayTransactionCredit $billpay */
        $billpay = billpayBase::PaymentInstance(billpayBase::PAYMENT_METHOD_TRANSACTION_CREDIT);
        $billpay->requireLang();

        $this->code = 'ot_z_bpytc_fee';
        $this->title = MODULE_ORDER_TOTAL_BILLPAYTC_SURCHARGE;
        $this->description = "";
        $this->enabled = true;
        $this->output = array();
    }

    /**
     * Executed on checkout_payment and checkout_confirmation
     * @return bool
     */
    function process()
    {
        global $xtPrice;
        if (!$this->isPaymentMethod()) return false;
        $value = $this->_getDataValue('feeAmount') * 0.01;
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
}
