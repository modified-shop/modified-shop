<?php

    require_once(DIR_FS_CATALOG. 'includes/external/billpay/base/billpayBase.php');
    $bank_data_query = xtc_db_query(' SELECT account_holder, account_number, bank_code, bank_name, invoice_reference, invoice_due_date '.
                                              ' FROM billpay_bankdata WHERE orders_id = '.(int)$_GET["oID"]);
    if (!xtc_db_num_rows($bank_data_query)) {
        return '';
    }

    /** @var $order order */
    if (empty($order)) {
        $order = $GLOBALS['order'];
    }
    $paymentMethod = $order->info['payment_method'];
/** @var billpayBase $billpay */
    $billpay = billpayBase::PaymentInstance($paymentMethod);
    if ($billpay) {
        $bankDataQuery = xtc_db_fetch_array($bank_data_query);
        /** @var $pdf */
        if (get_class($this) === "pdfbill") {
            // Mastershop24
            $pdf = $this;
            $orderId = $pdf->orders_id;
        }
        if (empty($orderId)) {
            $orderId = (int)$_GET["oID"];
        }
        $billpay->onDisplayPdf($pdf, $orderId, $bankDataQuery);
    }


