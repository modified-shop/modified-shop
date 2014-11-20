<?php
/* -----------------------------------------------------------------------------------------
   $Id: billpay_display_pdf_data.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   Copyright (c) 2012 Billpay GmbH

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

	$bank_data_query = xtc_db_query("SELECT account_holder,
	                                        account_number,
	                                        bank_code,
	                                        bank_name,
	                                        invoice_reference,
	                                        invoice_due_date
											               FROM billpay_bankdata
											              WHERE orders_id = '".$_GET['oID']."'");
	if (xtc_db_num_rows($bank_data_query) > 0) {
		if ($order->info['payment_method'] == 'billpay') {
			$bank_data = xtc_db_fetch_array($bank_data_query);

			$dat = $bank_data['invoice_due_date'];
			$year = substr($dat,0,-4);
			$mon = substr($dat,4,-2);
			$day = substr($dat,6,2);

			$bank_data_string = sprintf(decode_htmlentities(MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO), $bank_data['invoice_reference'], $day, $mon, $year);

			$pdf->SetFont($pdf->fontfamily, 'B', '9');
			$pdf->SetLineWidth(0.4);
			$pdf->ln(4);
			$pdf->MultiCell(0, 1, '', 'LRT');
			//$pdf->MultiCell(0, 4, decode_htmlentities(MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO1) . $day.".".$mon.".".$year.decode_htmlentities(MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO2), 'LR');
			$pdf->MultiCell(0, 4, decode_htmlentities($bank_data_string), 'LR');
			$pdf->MultiCell(0, 2, '', 'LR');
			$pdf->SetFont($pdf->fontfamily, '', '9');
			$pdf->MultiCell(0, 4, decode_htmlentities(MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_HOLDER) . ': ' . $bank_data['account_holder'], 'LR');
			$pdf->ln(0);
			$pdf->MultiCell(0, 4, decode_htmlentities(MODULE_PAYMENT_BILLPAY_TEXT_BANK_NAME) . ': ' . $bank_data['bank_name']  , 'LR');
			$pdf->ln(0);
      if (is_numeric($bank_data['bank_code']) === false) {
        $pdf->MultiCell(0, 4, decode_htmlentities(MODULE_PAYMENT_BILLPAY_TEXT_BIC) . ': ' . $bank_data['bank_code'], 'LR');
        $pdf->ln(0);
        $pdf->MultiCell(0, 4, decode_htmlentities(MODULE_PAYMENT_BILLPAY_TEXT_IBAN) . ': ' . $bank_data['account_number'], 'LR');
        $pdf->ln(0);
      } else {
        $pdf->MultiCell(0, 4, decode_htmlentities(MODULE_PAYMENT_BILLPAY_TEXT_BANK_CODE) . ': ' . $bank_data['bank_code'], 'LR');
        $pdf->ln(0);
        $pdf->MultiCell(0, 4, decode_htmlentities(MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_NUMBER) . ': ' . $bank_data['account_number'], 'LR');
        $pdf->ln(0);
      }
			$pdf->MultiCell(0, 4, decode_htmlentities(MODULE_PAYMENT_BILLPAY_TEXT_BANK_CODE) . ': ' . $bank_data['bank_code'], 'LR');
			$pdf->ln(0);
			$pdf->MultiCell(0, 4, decode_htmlentities(MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_NUMBER) . ': ' . $bank_data['account_number'], 'LR');
			$pdf->ln(0);
			$pdf->MultiCell(0, 4, decode_htmlentities(MODULE_PAYMENT_BILLPAY_TEXT_PURPOSE) . ': ' . $bank_data['invoice_reference'], 'LR');
			$pdf->MultiCell(0, 1, '', 'LRB');
			$pdf->ln(3);
			$pdf->SetLineWidth(0.1);
		} elseif ($order->info['payment_method'] == 'billpaydebit') {
			$pdf->SetFont($pdf->fontfamily, 'B', '9');
			$pdf->SetLineWidth(0.4);
			$pdf->ln(4);
			$pdf->MultiCell(0, 1, '', 'LRT');
			$pdf->MultiCell(0, 4, decode_htmlentities(MODULE_PAYMENT_BILLPAYDEBIT_TEXT_INVOICE_INFO1), 'LR');
			$pdf->MultiCell(0, 1, '', 'LRB');
			$pdf->ln(3);
			$pdf->SetLineWidth(0.1);
		} elseif ($order->info['payment_method'] == 'billpaytransactioncredit') {
			$pdf->SetFont($pdf->fontfamily, 'B', '9');
			$pdf->SetLineWidth(0.4);
			$pdf->ln(4);
			$pdf->MultiCell(0, 1, '', 'LRT');
			$pdf->MultiCell(0, 4, decode_htmlentities(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_INVOICEPDF_INFO), 'LR');
			$pdf->MultiCell(0, 1, '', 'LRB');
			$pdf->ln(3);
			$pdf->SetLineWidth(0.1);
		}
	}
?>