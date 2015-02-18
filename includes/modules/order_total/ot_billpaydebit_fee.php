<?php
require_once('ot_billpay_fee.php');

class ot_billpaydebit_fee extends ot_billpay_fee{
    var $_paymentIdentifier = 'BILLPAYDEBIT';

    function addFee() {
        return ($_SESSION['payment'] == 'billpaydebit' || $_POST['payment'] == 'billpaydebit') &&
            $_SESSION['billpay_customer_group'] != 'b';
    }
}

?>