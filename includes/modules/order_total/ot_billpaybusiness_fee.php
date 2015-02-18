<?php
require_once('ot_billpay_fee.php');

class ot_billpaybusiness_fee extends ot_billpay_fee{
    var $_paymentIdentifier = 'BILLPAYBUSINESS';

    function addFee()
    {
        if ($_SESSION['payment'] == 'billpay' || $_POST['payment'] == 'billpay')
        {
            if ($this->_checkFeeGroup(1)===2)
            {
                return $_SESSION['billpay_b2b'];
            }
            return $this->_checkFeeGroup(1);

        }
        return false;
    }
}

