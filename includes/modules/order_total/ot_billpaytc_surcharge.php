<?php
require_once(DIR_FS_CATALOG . 'includes/external/billpay/base/BillpayOT.php');
require_once(DIR_FS_CATALOG . 'includes/external/billpay/base/billpayBase.php');

class ot_billpaytc_surcharge extends BillpayOT
{
    public function __construct()
    {
        $this->_paymentIdentifier = 'BILLPAYTC_SURCHARGE';
        $this->paymentMethod = billpayBase::PAYMENT_METHOD_TRANSACTION_CREDIT;
        unset($this->config['TYPE']);
        unset($this->config['PERCENT']);
        unset($this->config['VALUE']);
        $this->config['SORT_ORDER']['default'] = 150;
        parent::__construct();

        $this->code = 'ot_billpaytc_surcharge';
    }

    public function check()
    {
        return false; // this file is no longer in use, check ot_z_bpytc_*.php
    }

    public function process() {
        if (!$this->isPaymentMethod()) return false;

        $surcharge = $this->_getDataValue('feeAmount') * 0.01;
        $total = $this->_getDataValue('totalAmount') * 0.01;

        if ($surcharge > 0) $this->addOT(MODULE_ORDER_TOTAL_BILLPAYTC_SURCHARGE, $surcharge, 10);
        if ($total > 0) $this->addOT(MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_TOTAL, $total, 30);

        return true;
    }

    private function addOT($title, $value, $sort_order)
    {
        global $xtPrice;
        $this->output[] = array(
            'title'      => '<strong>' . $title . ':</strong>',
            'text'       => '<strong>' . $xtPrice->xtcFormat($value, true) . '</strong>',
            'value'      => 0,
            'sort_order' => $this->sort_order + $sort_order,
        );
    }

}
