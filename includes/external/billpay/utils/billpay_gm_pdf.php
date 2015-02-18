<?php
// TODO: rework to plugin class

// if there is TCPDF ERROR: Some data has already been output, uncomment following line
//ob_end_clean();

global $order;
$billpayPayments = array('billpay', 'billpaydebit', 'billpaytransactioncredit', 'billpaypaylater');

if (empty($order)) {
    $order = new order((int)$_GET['oID']);
}

if (in_array($order->info['payment_method'], $billpayPayments)) {
    require_once(DIR_FS_LANGUAGES . $_SESSION['language'] . '/modules/payment/billpay.php');
    /** @noinspection PhpIncludeInspection */
    require_once(DIR_FS_CATALOG . 'includes/external/billpay/base/BillpayDB.php');

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


    if ($order->info['payment_method'] == 'billpay') {
        $bank_data_query = xtc_db_query(' SELECT account_holder, account_number, bank_code, bank_name, invoice_reference, invoice_due_date '.
            ' FROM billpay_bankdata WHERE orders_id = '.(int)$_GET['oID']);
        if (xtc_db_num_rows($bank_data_query)) {
            $bank_data = xtc_db_fetch_array($bank_data_query);
            $iConstantMargin = $this->pdf_order_info_cell_width[0];
            $dat = $bank_data['invoice_due_date'];
            if (!empty($dat)) {
                $year = substr($dat, 0, -4);
                $mon = substr($dat, 4, -2);
                $day = substr($dat, 6, 2);
                $sBankDataInfo = sprintf(
                    bpyEntityDecode(MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO, $charset),
                    $bank_data['invoice_reference'],
                    $day,
                    $mon,
                    $year
                );
                $invoice_date_string = $day.".".$mon.".".$year;
            } else {
                $sBankDataInfo = sprintf(
                    bpyEntityDecode(MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO_NO_DUEDATE, $charset),
                    $bank_data['invoice_reference']
                );
                $invoice_date_string = strip_tags(constant('MODULE_PAYMENT_BILLPAY_ACTIVATE_ORDER_WARNING'));
            }

            $sBankData1 = bpyEntityDecode(MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_HOLDER, $charset) . ':';
            $sBankData2 = bpyEntityDecode(MODULE_PAYMENT_BILLPAY_TEXT_IBAN, $charset) . ':';
            $sBankData3 = bpyEntityDecode(MODULE_PAYMENT_BILLPAY_TEXT_BIC, $charset) . ':';
            $sBankData4 = bpyEntityDecode(MODULE_PAYMENT_BILLPAY_TEXT_BANK_NAME, $charset) . ':';
            $sBankData5 = bpyEntityDecode(MODULE_PAYMENT_BILLPAY_TEXT_PURPOSE, $charset) . ':';
            $sBankData6 = bpyEntityDecode(MODULE_PAYMENT_BILLPAY_DUEDATE_TITLE, $charset) . ':';

            $sHolder    = bpyEntityDecode(MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_HOLDER, $charset);
            $sIbanBic   = bpyEntityDecode(MODULE_PAYMENT_BILLPAY_TEXT_IBAN, $charset).' / '.bpyEntityDecode(MODULE_PAYMENT_BILLPAY_TEXT_BIC, $charset);
            $sReason    = bpyEntityDecode(MODULE_PAYMENT_BILLPAY_TEXT_PURPOSE, $charset);
            $sDueDate   = bpyEntityDecode(MODULE_PAYMENT_BILLPAY_DUEDATE_TITLE, $charset);

            $font_normal = $this->pdf_fonts['CUSTOMER'];
            $font_bold   = $this->pdf_fonts['HEADING_ORDER'];
            $iLeftCol = 40;

            parent::SetX(parent::getLeftMargin());
            parent::getFont($font_normal);
            parent::MultiCell(parent::getInnerWidth(), parent::getCellHeight(), $sBankDataInfo, '', 'L');

            // Gambio < 2.1 fix
            $y = parent::GetY() + 2;
            parent::SetY($y);

            $table_rows = array(
                array($sBankData1, $bank_data['account_holder']),
                array($sBankData2, $bank_data['account_number']),
                array($sBankData3, $bank_data['bank_code']),
                array($sBankData4, $bank_data['bank_name']),
                array($sBankData5, $bank_data['invoice_reference']),
                array($sBankData6, $invoice_date_string),
            );
            foreach ($table_rows as $row) {
                parent::SetX(parent::getLeftMargin());
                parent::getFont($font_bold);
                parent::Cell($iLeftCol, 0, $row[0]);
                parent::getFont($font_normal);
                parent::Cell(0, 0, $row[1], '');
                $y = $this->is_newPageOi($this->order_info, parent::GetY(), 5, (parent::getCellHeight()) + 3, $this->pdf_order_info_cell_width);
                parent::SetY($y);
            }
        }

    } else if ($order->info['payment_method'] == 'billpaydebit') {
        require_once(DIR_FS_LANGUAGES . $_SESSION['language'] . '/modules/payment/billpaydebit.php');
        $iConstantMargin = 40;
        $sBankDataInfo = bpyEntityDecode(MODULE_PAYMENT_BILLPAYDEBIT_TEXT_INVOICE_INFO1 . ' ' . MODULE_PAYMENT_BILLPAYDEBIT_TEXT_INVOICE_INFO2, $charset);
        $y = parent::GetY();
        $y += 12;

        parent::SetY($y);
        $get_y = $this->getActualY($y);
        parent::SetX(parent::getLeftMargin());
        parent::getFont($this->pdf_fonts['CUSTOMER']);
        parent::MultiCell(parent::getInnerWidth(), parent::getCellHeight(), $sBankDataInfo, '', 'L', 0);
        parent::Ln();

        $get_y = $this->getActualY($y);
        parent::SetY($get_y + 5);
    } else if ($order->info['payment_method'] == 'billpaytransactioncredit') {
        require_once(DIR_FS_DOCUMENT_ROOT . DIR_WS_INCLUDES . 'modules/payment/billpaytransactioncredit.php');
        require_once(DIR_FS_LANGUAGES . $_SESSION['language'] . '/modules/payment/billpaytransactioncredit.php');
        $billpay = new billpaytransactioncredit();
        $orderId = (int)$_GET['oID'];
        $currency   = $order->info['currency'];
        $country2 = BillpayDB::DBFetchValue("SELECT billing_country_iso_code_2 FROM orders WHERE orders_id = '".(int)$orderId."'");
        $canPayWithAutoSEPA = $billpay->canPayWithAutoSEPA($country2);

        // Validate if order is activated. Otherwise show warning on invoice
        $isActivated = false;
        $activated_query = xtc_db_query('SELECT invoice_due_date FROM billpay_bankdata WHERE orders_id = '.(int)$orderId);
        if (xtc_db_num_rows($activated_query)) {
            $data = xtc_db_fetch_array($activated_query);
            if (!empty($data['invoice_due_date'])) {
                $isActivated = true;
            }
        }

        $iConstantMargin = 40;
        $sBankDataInfo = $infoText;

        $rate_details_query = xtc_db_query("SELECT rate_surcharge, rate_total_amount, rate_count, " .
            "rate_dues, rate_interest_rate, rate_anual_rate, rate_base_amount, rate_fee " .
            "FROM billpay_bankdata WHERE api_reference_id = '".(int)$_GET['oID'] . "'");
        $data = xtc_db_fetch_array($rate_details_query);
        $dueList = $data['rate_dues'];
        $trimmedDueList = trim($dueList);
        $dueDateArray = $billpay->unserializeDueDates($trimmedDueList);

        $font_normal = $this->pdf_fonts['CUSTOMER'];
        $font_bold   = $this->pdf_fonts['HEADING_ORDER'];

        if (!$isActivated) {
            parent::MultiCell(parent::getInnerWidth(), parent::getCellHeight(), decodeString(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_ACTIVATE_ORDER, $charset), '', 'L');
        } else {

            if ($canPayWithAutoSEPA) {
                parent::MultiCell(parent::getInnerWidth(), parent::getCellHeight(), decodeString(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_INVOICE_INFO1, $charset), '', 'L');
            } else {
                $bank_data_query = xtc_db_query(
                    ' SELECT account_holder, account_number, bank_code, bank_name, invoice_reference, invoice_due_date ' .
                    ' FROM billpay_bankdata WHERE orders_id = ' . (int)$_GET['oID']
                );
                $bank_data = xtc_db_fetch_array($bank_data_query);
                parent::Cell(parent::getInnerWidth(), parent::getCellHeight(), decodeString(MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_MANUAL_TRANSFER, $charset), '', 1);
                parent::Ln();
                $iCellWidthFirst  = 80;
                $iCellWidthSecond = 40;
                parent::getFont($font_bold);
                parent::Cell($iCellWidthFirst, parent::getCellHeight(), decodeString(MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_PAYEE, $charset), '', 0);
                parent::Cell($iCellWidthSecond, parent::getCellHeight(), decodeString(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_IBAN, $charset).':', '', 0);
                parent::getFont($font_normal);
                parent::Cell($iCellWidthSecond, parent::getCellHeight(), $bank_data['account_number'], '', 1);

                parent::Cell($iCellWidthFirst, parent::getCellHeight(), 'BillPay GmbH', '', 0);
                parent::getFont($font_bold);
                parent::Cell($iCellWidthSecond, parent::getCellHeight(), decodeString(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_BIC, $charset).':', '', 0);
                parent::getFont($font_normal);
                parent::Cell(parent::getInnerWidth(), parent::getCellHeight(), $bank_data['bank_code'], '', 1);

                parent::Cell($iCellWidthFirst, parent::getCellHeight(), 'Zweigniederlassung Schweiz (Regensdorf)', '', 0);
                parent::getFont($font_bold);
                parent::Cell($iCellWidthSecond, parent::getCellHeight(), decodeString(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_BANK_NAME, $charset).':', '', 0);
                parent::getFont($font_normal);
                parent::Cell(parent::getInnerWidth(), parent::getCellHeight(), $bank_data['bank_name'], '', 1);

                parent::Cell($iCellWidthFirst, parent::getCellHeight(), 'DE-10115 Berlin', '', 0);
                parent::getFont($font_bold);
                parent::Cell($iCellWidthSecond, parent::getCellHeight(), decodeString(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_PURPOSE, $charset).':', '', 0);
                parent::getFont($font_normal);
                parent::Cell(parent::getInnerWidth(), parent::getCellHeight(), $bank_data['invoice_reference'], '', 1);

                parent::Ln();

                parent::getFont($font_bold);
                parent::Cell(parent::getInnerWidth(), parent::getCellHeight(), decodeString(MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_MANUAL_RATE_PLAN, $charset), '', 1);
            }

            $dues = array();
            foreach ((array)$dueDateArray as $due) {
                if (!isset($dues[$due['value']])) {
                    $dues[$due['value']] = array();
                }
                $dues[$due['value']][] = substr($due['date'], 6, 2) . '.' . substr($due['date'], 4, 2) . '.' . substr(
                        $due['date'],
                        0,
                        4
                    );
            }

            $iCellWidthRateAmount = 40;
            parent::getFont($font_bold);
            parent::Cell($iCellWidthRateAmount, parent::getCellHeight(), decodeString(MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_AMOUNT, $charset), 'R', 0);
            parent::Cell(parent::getInnerWidth(), parent::getCellHeight(), decodeString(MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_DATES, $charset), '', 1);

            parent::getFont($font_normal);
            foreach ($dues as $rate_value => $rate_dates) {
                $sValue = xtc_format_price_order($rate_value / 100, 1, $order->info['currency']);
                parent::Cell($iCellWidthRateAmount, parent::getCellHeight(), $sValue, 'T', 0);
                $isCursorMoved = true;
                $aaLines = array_chunk($rate_dates, 6);
                foreach ($aaLines as $aLine) {
                    $sLine = join('; ', $aLine);
                    $sBorderStyle = 'LT';
                    if (!$isCursorMoved) {
                        parent::Cell($iCellWidthRateAmount, parent::getCellHeight(), '', '', 0);
                        $sBorderStyle = 'L';
                    }
                    parent::Cell(parent::getInnerWidth(), parent::getCellHeight(), $sLine, $sBorderStyle, 1);
                    $isCursorMoved = false;
                }
            }
            parent::Cell(parent::getInnerWidth(), parent::getCellHeight(), '', 'T', 1);
        }
    } else if ($order->info['payment_method'] == 'billpaypaylater') {
        require_once(DIR_FS_LANGUAGES . $_SESSION['language'] . '/modules/payment/billpaypaylater.php');
        $sThankYouText = decodeString(MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_INVOICE_INFO1, $charset);
        $y = parent::GetY();
        $y += 12;
        parent::SetY($y);
        $get_y = $this->getActualY($y);
        parent::SetX(parent::getLeftMargin());
        parent::getFont($this->pdf_fonts['CUSTOMER']);
        parent::MultiCell(parent::getInnerWidth(), parent::getCellHeight(), $sThankYouText, '', 'L', 0);
        parent::Ln();
    }
}
