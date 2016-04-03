<?php
// if there is TCPDF ERROR: Some data has already been output, uncomment following line
//ob_end_clean();

global $order;
$billpayPayments = array('billpay', 'billpaydebit', 'billpaytransactioncredit', 'billpaypaylater');
$orderId = (int)$_GET['oID'];
if (empty($order)) {
    $order = new order($orderId);
}

if (in_array($order->info['payment_method'], $billpayPayments)) {
    require_once(DIR_FS_LANGUAGES . $_SESSION['language'] . '/modules/payment/billpay.php');
    /** @noinspection PhpIncludeInspection */
    require_once(DIR_FS_CATALOG . 'includes/external/billpay/base/BillpayDB.php');
    /** @noinspection PhpIncludeInspection */
    require_once(DIR_FS_CATALOG . 'includes/external/billpay/base/billpayBase.php');

    $this->order_info = array(); // empty to disable order info generation

    $displayMeinPortalLink = false;

    if ($displayMeinPortalLink) {
        parent::getFont($this->pdf_fonts['HEADING_ORDER_INFO']);

        parent::SetY($y);
        parent::SetX(parent::getLeftMargin());
        parent::MultiCell(parent::getInnerWidth(), parent::getCellHeight(), "Mein BillPay Portal: https://billpay.de/meinbillpay/", '0', 'L', 0);

        parent::SetY(parent::GetY());
        parent::SetX(parent::getLeftMargin());
        parent::MultiCell(parent::getInnerWidth(), 3, '', 'T', '', 0);
    }

    $className = get_class();
    switch ($className) {
        // Gambio 2.1
        case 'gmOrderPDF_ORIGIN':
            $charset = 'UTF-8';
            break;
        // Gambio 2.0 or less
        case 'gmOrderPDF':
        default:
            $charset = 'iso-8859-15';
    }

    function bpyEntityDecode($string, $charset)
    {
        return html_entity_decode($string, ENT_COMPAT | ENT_HTML401, $charset);
    }

    function utf8ToPdfString($string, $charset)
    {
        return utf8_decode(bpyEntityDecode(strip_tags($string), $charset));
    }

    function decodeString($string, $charset)
    {
        return (bpyEntityDecode(strip_tags($string), $charset));
    }

    /** @var BillPayBase $billpay */
    $billpay = billpayBase::PaymentInstance($order->info['payment_method']);
    $billpay->requireLang();

    $currency = BillpayOrder::getCurrencyById($orderId);
    $bank_data = Billpay_Base_Bankdata::LoadByOrdersId($orderId);
    $data = $billpay->getInvoiceTextData($bank_data, $currency);
    $isActivated = $billpay->isActivated($orderId);

    $font_normal = $this->pdf_fonts['CUSTOMER'];
    $font_bold   = $this->pdf_fonts['HEADING_ORDER'];
    $font_warning= $font_bold;
    $font_warning[3] = '#FF0000';
    $iLeftCol = 40;

    // Gambio < 2.1 fix
    $y = parent::GetY() + 2;
    parent::SetY($y);

    foreach ($data as $row) {
        if (is_array($row)) {
            // Gambio < 2.1 fix
            $y = parent::GetY() + 4;
            parent::SetY($y);

            $table_rows = $row;
            foreach ($table_rows as $header => $value) {
                if (empty($value)) continue;
                parent::SetX(parent::getLeftMargin());
                parent::getFont($font_bold);
                parent::Cell($iLeftCol, 0, bpyEntityDecode($header, $charset));
                parent::getFont($font_normal);
                parent::Cell(0, 0, $value, '');
                $y = $this->is_newPageOi($this->order_info, parent::GetY(), 5, (parent::getCellHeight()) + 3, $this->pdf_order_info_cell_width);
                parent::SetY($y);
            }
        } else {
            parent::SetX(parent::getLeftMargin());
            parent::getFont($font_normal);
            parent::MultiCell(parent::getInnerWidth(), parent::getCellHeight(), bpyEntityDecode($row, $charset), '', 'L');
        }
    }
    if (!$isActivated) {
        parent::SetX(parent::getLeftMargin());
        parent::getFont($font_warning);
        parent::Cell(0, 0, bpyEntityDecode(MODULE_PAYMENT_BILLPAY_ACTIVATE_ORDER_WARNING, $charset), '');
        $y = $this->is_newPageOi($this->order_info, parent::GetY(), 5, (parent::getCellHeight()) + 3, $this->pdf_order_info_cell_width);
        parent::SetY($y);

    }
}
