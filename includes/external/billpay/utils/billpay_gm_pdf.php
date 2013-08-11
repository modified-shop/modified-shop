<?php
/* -----------------------------------------------------------------------------------------
   $Id: billpay_gm_pdf.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   Copyright (c) 2012 Billpay GmbH

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

	require_once(DIR_FS_LANGUAGES . $_SESSION['language'] . '/modules/payment/billpay.php');
	
	global $order;
	if ($order->info['payment_method'] == 'billpay') {
 		$bank_data_query = xtc_db_query(' SELECT account_holder, account_number, bank_code, bank_name, invoice_reference, invoice_due_date '.
										  ' FROM billpay_bankdata WHERE orders_id = '.$_GET['oID']);
		if (xtc_db_num_rows($bank_data_query)) { 
			$bank_data = xtc_db_fetch_array($bank_data_query);
			
			$dat = $bank_data['invoice_due_date'];
			$year = substr($dat,0,-4);
			$mon = substr($dat,4,-2);
			$day = substr($dat,6,2);

			$iConstantMargin = 40;
			
			//$sBankDataInfo = html_entity_decode(MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO1 . "$day.$mon.$year" . MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO2);
			$sBankDataInfo = sprintf(html_entity_decode(MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO), $bank_data['invoice_reference'], $day, $mon, $year);
			$sBankData1 = html_entity_decode(MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_HOLDER) . ':';
			$sBankData2 = html_entity_decode(MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_NUMBER) . ':';
			$sBankData3 = html_entity_decode(MODULE_PAYMENT_BILLPAY_TEXT_BANK_CODE) .':';
			$sBankData4 = html_entity_decode(MODULE_PAYMENT_BILLPAY_TEXT_BANK_NAME) .':';
			$sBankData5 = html_entity_decode(MODULE_PAYMENT_BILLPAY_TEXT_PURPOSE) .':';
			$sBankData6 = html_entity_decode(MODULE_PAYMENT_BILLPAY_DUEDATE_TITLE) .':';
			$y = parent::GetY();

			parent::SetY($y);
			$get_y = $this->getActualY($y);
			parent::SetX(parent::getLeftMargin());
			parent::getFont($this->pdf_fonts['CUSTOMER']);
			parent::MultiCell(parent::getInnerWidth(), parent::getCellHeight(), $sBankDataInfo, '', 'L', 0);
			parent::Ln();

			$get_y = $this->getActualY($get_y);
			$y = $get_y;
			parent::SetY($y);
			parent::SetX(parent::getLeftMargin());
			parent::getFont($this->pdf_fonts['HEADING_ORDER']);
			parent::MultiCell(parent::GetStringWidth($sBankData1) + 10, parent::getCellHeight(), $sBankData1, '', 'L', 0);
			$get_y = $this->getActualY($get_y);
			parent::SetY($y);
			parent::SetX(parent::getLeftMargin() + $iConstantMargin);
			parent::getFont($this->pdf_fonts['CUSTOMER']);
			parent::MultiCell(parent::GetStringWidth($bank_data['account_holder']) + 10, parent::getCellHeight(), $bank_data['account_holder'], '', 'L', 0);

			$get_y = $this->getActualY($get_y);
			$y = $get_y;
			parent::SetY($y);
			parent::SetX(parent::getLeftMargin());
			parent::getFont($this->pdf_fonts['HEADING_ORDER']);
			parent::MultiCell(parent::GetStringWidth($sBankData2) + 10, parent::getCellHeight(), $sBankData2, '', 'L', 0);
			$get_y = $this->getActualY($get_y);
			parent::SetY($y);
			parent::SetX(parent::getLeftMargin() + $iConstantMargin);
			parent::getFont($this->pdf_fonts['CUSTOMER']);
			parent::MultiCell(parent::GetStringWidth($bank_data['account_number']) + 10, parent::getCellHeight(), $bank_data['account_number'], '', 'L', 0);

			$get_y = $this->getActualY($get_y);
			$y = $get_y;
			parent::SetY($y);
			parent::SetX(parent::getLeftMargin());
			parent::getFont($this->pdf_fonts['HEADING_ORDER']);
			parent::MultiCell(parent::GetStringWidth($sBankData3) + 10, parent::getCellHeight(), $sBankData3, '', 'L', 0);
			$get_y = $this->getActualY($get_y);
			parent::SetY($y);
			parent::SetX(parent::getLeftMargin() + $iConstantMargin);
			parent::getFont($this->pdf_fonts['CUSTOMER']);
			parent::MultiCell(parent::GetStringWidth($bank_data['bank_code']) + 10, parent::getCellHeight(), $bank_data['bank_code'], '', 'L', 0);

			$get_y = $this->getActualY($get_y);
			$y = $get_y;
			parent::SetY($y);
			parent::SetX(parent::getLeftMargin());
			parent::getFont($this->pdf_fonts['HEADING_ORDER']);
			parent::MultiCell(parent::GetStringWidth($sBankData4) + 10, parent::getCellHeight(), $sBankData4, '', 'L', 0);
			$get_y = $this->getActualY($get_y);
			parent::SetY($y);
			parent::SetX(parent::getLeftMargin() + $iConstantMargin);
			parent::getFont($this->pdf_fonts['CUSTOMER']);
			parent::MultiCell(parent::GetStringWidth($bank_data['bank_name']) + 10, parent::getCellHeight(), $bank_data['bank_name'], '', 'L', 0);

			$get_y = $this->getActualY($get_y);
			$y = $get_y;
			parent::SetY($y);
			parent::SetX(parent::getLeftMargin());
			parent::getFont($this->pdf_fonts['HEADING_ORDER']);
			parent::MultiCell(parent::GetStringWidth($sBankData5) + 10, parent::getCellHeight(), $sBankData5, '', 'L', 0);
			$get_y = $this->getActualY($get_y);
			parent::SetY($y);
			parent::SetX(parent::getLeftMargin() + $iConstantMargin);
			parent::getFont($this->pdf_fonts['CUSTOMER']);
			parent::MultiCell(parent::GetStringWidth($bank_data['invoice_reference']) + 10, parent::getCellHeight(), $bank_data['invoice_reference'], '', 'L', 0);

			$get_y = $this->getActualY($get_y);
			$y = $get_y;
			parent::SetY($y);
			parent::SetX(parent::getLeftMargin());
			parent::getFont($this->pdf_fonts['HEADING_ORDER']);
			parent::MultiCell(parent::GetStringWidth($sBankData6) + 10, parent::getCellHeight(), $sBankData6, '', 'L', 0);
			$get_y = $this->getActualY($get_y);
			parent::SetY($y);
			parent::SetX(parent::getLeftMargin() + $iConstantMargin);
			parent::getFont($this->pdf_fonts['CUSTOMER']);
			parent::MultiCell(parent::GetStringWidth("$day.$mon.$year") + 10, parent::getCellHeight(), "$day.$mon.$year", '', 'L', 0);

			
			$get_y = $this->getActualY($y);
			parent::SetY($get_y + 5);			
			
		}
	} else if ($order->info['payment_method'] == 'billpaydebit') {
		require_once(DIR_FS_LANGUAGES . $_SESSION['language'] . '/modules/payment/billpaydebit.php');
		$iConstantMargin = 40;
		$sBankDataInfo = html_entity_decode(MODULE_PAYMENT_BILLPAYDEBIT_TEXT_INVOICE_INFO1 . ' ' . MODULE_PAYMENT_BILLPAYDEBIT_TEXT_INVOICE_INFO2);
		$y = parent::GetY();

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
		$billpay = new billpaytransactioncredit();
		$currency 	= $order->info['currency'];
		//$rateDetails = $billpay->buildTCPaymentInfo($_GET['oID'], $order, true);
		//$rateDetails = buildTCPaymentInfoPDF($_GET['oID'], $order, true);
		
		// Validate if order is activated. Otherwise show warning on invoice
		$activated_query = xtc_db_query('SELECT invoice_due_date FROM billpay_bankdata WHERE orders_id = '.$_GET['oID']);
		if (xtc_db_num_rows($activated_query)) {
			$data = xtc_db_fetch_array($activated_query);
		 	if (!trim($data['invoice_due_date'])) {
		 		$rateDetails .= '<br/><br/>'.MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_ACTIVATE_ORDER_WARNING;
		 	}
		}

		$iConstantMargin = 40;
		$sBankDataInfo = $infoText;
		$y = parent::GetY();

		parent::SetY($y);
		$get_y = $this->getActualY($y);
		parent::SetX(parent::getLeftMargin());
		parent::getFont($this->pdf_fonts['CUSTOMER']);
		//parent::MultiCell(parent::getInnerWidth(), parent::getCellHeight(), strip_tags($sBankDataInfo), '', 'L', 0);
		parent::Ln();
		parent::SetFont($header_font_type, $header_font_style, 8);
		parent::MultiCell(parent::getInnerWidth(), parent::getCellHeight(), html_entity_decode(strip_tags(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_INVOICE_INFO1)), '', 'L', 0);
		parent::SetFont($header_font_type, $header_font_style, 7);
		$get_y = $this->getActualY($y);
		parent::SetY($get_y + 2);
			
		$rate_details_query = xtc_db_query("SELECT rate_surcharge, rate_total_amount, rate_count, " .
			"rate_dues, rate_interest_rate, rate_anual_rate, rate_base_amount, rate_fee, " .
			"rate_fee_tax FROM billpay_bankdata WHERE api_reference_id = '".$_GET['oID'] . "'");
		
		$data = xtc_db_fetch_array($rate_details_query);
		$dueList = $data['rate_dues'];
		
		$trimmedDueList = trim($dueList);
		$otherCosts = $data['rate_total_amount'] - $data['rate_surcharge'] - $data['rate_fee'] - $data['rate_base_amount'];
		
		$dueDateArray = $billpay->unserializeDueDates($trimmedDueList);
		

		$rateCount = 1;
		$b_y = parent::GetY();
		$b_x = 0;
		foreach ($dueDateArray as $entry) {
			
			/*parent::MultiCell(parent::getInnerWidth(), parent::getCellHeight(), html_entity_decode(strip_tags($rateCount.' '.MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_RATE)), '', 'L', 0);
			$get_x = 10;
			parent::SetX($get_x + 20);
			parent::MultiCell(parent::getInnerWidth(), parent::getCellHeight(), html_entity_decode(strip_tags(xtc_format_price_order($entry['value'] / 100, 1, $order->info['currency']))), '', 'L', 0);
			$infoText .= $rateCount . '. ' . MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_RATE . ': ';
			$infoText .= xtc_format_price_order($entry['value'] / 100, 1, $order->info['currency']);*/

			//$trimmedDueDate = trim($entry['date']);
			//$trimmedDueDate = substr($trimmedDueDate, 0, 4).'-'.substr($trimmedDueDate, 4, 2).'-'.substr($trimmedDueDate, 6);			
			
			if (!empty($entry['date'])) {
				$trimmedDueDate = trim($entry['date']);
				$trimmedDueDate = substr($trimmedDueDate, 0, 4).'-'.substr($trimmedDueDate, 4, 2).'-'.substr($trimmedDueDate, 6);				
			}
			if(count($dueDateArray) >= 12) {
				if($rateCount > (count($dueDateArray) / 2)) {
					parent::Cell(80, 4, '', '', '');	
				}
				parent::Cell(10, 4, $rateCount.' '.MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_RATE, '', '');
				parent::Cell(15, 4, xtc_format_price_order($entry['value'] / 100, 1, $order->info['currency']), '', '');
				if (!empty($entry['date'])) {
					parent::Cell(60, 4, '('.html_entity_decode(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_RATEDUE_TEXT .' '. xtc_date_short($trimmedDueDate)).')', '', '');
				}
				if($rateCount == (count($dueDateArray) / 2)) {
					parent::SetY($b_y);
				}			
				else {
					parent::ln(4);					
				}
			}
			else {
				parent::Cell(10, 4, $rateCount.' '.MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_RATE, '', '');
				parent::Cell(20, 4, xtc_format_price_order($entry['value'] / 100, 1, $order->info['currency']), '', '');
				if (!empty($entry['date'])) {
					parent::Cell(20, 4, '('.html_entity_decode(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_RATEDUE_TEXT .' '. xtc_date_short($trimmedDueDate)).')', '', '');
				}
				parent::ln(4);
			}
			++$rateCount;			
		}
		parent::ln(4);
		parent::SetFont($header_font_type, 'B', 9);
		parent::Cell(20, 4, ' '.html_entity_decode(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TOTAL_PRICE_CALC_TEXT), '', '');
		parent::SetFont($header_font_type, $header_font_style, 7);
		parent::ln(4);
		parent::Cell(20, 4, ' '.html_entity_decode(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_CART_AMOUNT_TEXT).'='.xtc_format_price_order($data['rate_base_amount'], 1, $currency), '', '');
		parent::ln(4);
		parent::Cell(20, 4, ' '.html_entity_decode(MODULE_PAYMENT_BILLPAYTC_SURCHARGE_TEXT), '', '');
		parent::ln(4);
		parent::Cell(20, 4, ' '.xtc_format_price_order($data['rate_base_amount'], 1, $currency).' x '.round($data['rate_interest_rate'], 2) .' x '.$data['rate_count'].' / 100)='.xtc_format_price_order($data['rate_surcharge'], 1, $currency), '', '');
		parent::ln(4);
		parent::Cell(20, 4, ' '.html_entity_decode(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TRANSACTION_FEE_TEXT).$taxString.'+'.xtc_format_price_order($data['rate_fee'], 1, $currency), '', '');
		parent::ln(4);
		parent::Cell(20, 4, ' '.html_entity_decode(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_OTHER_COSTS_TEXT).'+'.xtc_format_price_order($otherCosts, 1, $currency), '', '');
		parent::ln(4);
		parent::Cell(20, 4, ' '.html_entity_decode(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TOTAL_AMOUNT_TEXT).'='.xtc_format_price_order($data['rate_total_amount'], 1, $currency), '', '');
		parent::ln(4);
		parent::Cell(20, 4, ' '.html_entity_decode(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_ANUAL_RATE_TEXT).'='.round($data['rate_anual_rate'], 2).'%', '', '');
		parent::ln(4);
		parent::ln(4);
 		//parent::Cell(20, 4, ' '.MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_EXAMPLE_TEXT, '', '2');
 		parent::MultiCell(parent::getInnerWidth(), parent::getCellHeight(), html_entity_decode(strip_tags(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_EXAMPLE_TEXT)), '', 'L', 0);
		parent::ln(4);
		parent::ln(4);
	}
?>