<?php
/**
 * Project: xt:Commerce - eCommerce Engine
 * @version $Id
 *
 * xt:Commerce - Shopsoftware
 * (c) 2003-2007 xt:Commerce (Winger/Zanier), http://www.xt-commerce.com
 *
 * xt:Commerce ist eine geschŸtzte Handelsmarke und wird vertreten durch die xt:Commerce GmbH (Austria)
 * xt:Commerce is a protected trademark and represented by the xt:Commerce GmbH (Austria)
 *
 * @copyright Copyright 2003-2007 xt:Commerce (Winger/Zanier), www.xt-commerce.com
 * @copyright based on Copyright 2002-2003 osCommerce; www.oscommerce.com
 * @copyright Porttions Copyright 2003-2007 Zen Cart Development Team
 * @copyright Porttions Copyright 2004 DevosC.com
 * @license http://www.xt-commerce.com.com/license/2_0.txt GNU Public License V2.0
 *
 * For questions, help, comments, discussion, etc., please join the
 * xt:Commerce Support Forums at www.xt-commerce.com
 *
 */
$show = true;
if($_GET['view']=='detail' && isset($_GET['paypal_ipn_id'])) {
	$ipn_id = (int)$_GET['paypal_ipn_id'];
	$query = "SELECT * FROM ".TABLE_PAYPAL." WHERE paypal_ipn_id = '".$ipn_id."'";
	$query = xtc_db_query($query);
	$ipn_data = xtc_db_fetch_array($query);
}
if($_GET['view']=='detail' && isset($_GET['txn_id'])) {
	$response = $paypal->GetTransactionDetails($_GET['txn_id']);
//echo '<pre>';
//print_r ($response);
//echo '</pre>';
	// error ?
	if($response['ACK']!='Success') {
		$error = $paypal->getErrorDescription($response['L_ERRORCODE0']);
		$messageStack->add($error,'error');
		$error = $messageStack->output();
		$show = false;
	} else {
		$ipn_data = $paypal->mapResponse($response);
	}
}
if(isset($error)) echo $error;
if($show) {
?>
	<div class="highlightbox">
		<p class="pageHeading"><?php echo TEXT_PAYPAL_TRANSACTION_DETAIL; ?></p>
		<table class="main" width="100%" border="0">
			<tr>
				<td width="10%"><?php echo TEXT_PAYPAL_TXN_ID; ?></td>
				<td width="90%"><?php echo $ipn_data['txn_type'].' (Code: '.$ipn_data['txn_id'].')'; ?></td>
			</tr>
			<?php if($ipn_data['payer_business_name']!='') { ?>
				<tr>
					<td width="10%"><?php echo TEXT_PAYPAL_COMPANY; ?></td>
					<td width="90%"><?php echo $ipn_data['payer_business_name']; ?></td>
				</tr>
			<?php } ?>
			<tr>
				<td width="10%"><?php echo TEXT_PAYPAL_PAYER_EMAIL; ?></td>
				<td width="90%" valign="middle"><?php echo $ipn_data['payer_email'];?></td>
			</tr>
			<tr>
				<td width="10%"><?php echo TEXT_PAYPAL_PAYER_EMAIL_STATUS; ?></td>
				<td width="90%"><?php echo $paypal->getStatusSymbol($ipn_data['payer_status']).$ipn_data['payer_status']; ?></td>
			</tr>
			<tr>
				<td width="10%"><?php echo TEXT_PAYPAL_RECEIVER_EMAIL; ?></td>
				<td width="90%"><?php echo $ipn_data['receiver_email']; ?></td>
			</tr>
			<tr>
				<td colspan="2"><hr noshade></td>
			</tr>
			<?php if($ipn_data['pending_reason']=='authorization') { ?>
				<tr>
					<td width="10%"><?php echo TEXT_PAYPAL_TRANSACTION_AUTH_TOTAL; ?></td>
					<td width="90%"><?php echo xtc_format_price($ipn_data['mc_authorization'],1,false,0).' '.$ipn_data['mc_currency']; ?></td>
				</tr>
				<tr>
					<td width="10%"><?php echo TEXT_PAYPAL_TRANSACTION_AUTH_CAPTURED; ?></td>
					<td width="90%"><?php echo xtc_format_price($ipn_data['mc_captured'],1,false,0).' '.$ipn_data['mc_currency']; ?></td>
				</tr>
				<tr>
					<td width="10%"><?php echo TEXT_PAYPAL_TRANSACTION_AUTH_OPEN; ?></td>
					<td width="90%"><?php echo xtc_format_price($ipn_data['mc_authorization']-$ipn_data['mc_captured'],1,false,0).' '.$ipn_data['mc_currency']; ?></td>
				</tr>
			<?php } else { ?>
				<?php if($ipn_data['num_cart_items']) { ?>
					<tr>
						<td width="10%"><?php echo TEXT_PAYPAL_CARTITEM; ?></td>
						<td width="90%"><?php echo $ipn_data['num_cart_items']; ?></td>
					</tr>
				<?php }  ?>
				<?php if($ipn_data['mc_shipping']!='0.00') { ?>
					<tr>
						<td width="10%"><?php echo TEXT_PAYPAL_VERSAND; ?></td>
						<td width="90%"><?php echo xtc_format_price($ipn_data['mc_shipping'],1,false,0).' '.$ipn_data['mc_currency']; ?></td>
					</tr>
				<?php }  ?>
				<tr>
					<td width="10%"><?php echo TEXT_PAYPAL_TOTAL; ?></td>
					<td width="90%"><?php echo xtc_format_price($ipn_data['mc_gross'],1,false,0).' '.$ipn_data['mc_currency']; ?></td>
				</tr>
				<tr>
					<td width="10%"><?php echo TEXT_PAYPAL_FEE; ?></td>
					<td width="90%"><?php echo xtc_format_price($ipn_data['mc_fee'],1,false,0).' '.$ipn_data['mc_currency']; ?></td>
				</tr>
				<tr>
					<td width="10%"><?php echo TEXT_PAYPAL_NETTO; ?></td>
					<td width="90%"><?php echo xtc_format_price(round($ipn_data['mc_gross']-$ipn_data['mc_fee'],2),1,false,0).' '.$ipn_data['mc_currency']; ?></td>
				</tr>
			<?php }  ?>
			<tr>
				<td colspan="2"><hr noshade></td>
			</tr>
			<tr>
				<td width="10%"><?php echo TEXT_PAYPAL_ORDER_ID; ?></td>
				<td width="90%"><?php echo $ipn_data['xtc_order_id']; ?></td>
			</tr>
			<tr>
				<td width="10%"><?php echo TEXT_PAYPAL_PAYMENT_STATUS; ?></td>
				<td width="90%"><?php echo $paypal->getStatusName($ipn_data['payment_status'],$ipn_data['txn_type']); ?></td>
			</tr>
			<tr>
				<td width="10%"><?php echo TEXT_PAYPAL_PAYMENT_DATE; ?></td>
				<td width="90%"><?php echo $ipn_data['payment_date']; ?></td>
			</tr>
			<tr>
				<td width="10%" valign="top"><?php echo TEXT_PAYPAL_KUNDE; ?></td>
				<td width="90%"><?php echo $ipn_data['first_name'].' '.$ipn_data['last_name']; ?></td>
			</tr>
			<tr>
				<td width="10%" valign="top"><?php echo TEXT_PAYPAL_ADRESS; ?></td>
				<td width="90%"><?php echo $ipn_data['address_name'].'<br>'.$ipn_data['address_street'].'<br>'.$ipn_data['address_zip'].' '.$ipn_data['address_city'].'<br>'.$ipn_data['address_country']; ?></td>
			</tr>
			<?php if($ipn_data['address_status']!='' and $ipn_data['address_status']!='None') { ?>
				<tr>
					<td width="10%"><?php echo TEXT_PAYPAL_ADRESS_STATUS; ?></td>
					<td width="90%"><?php echo $paypal->getStatusSymbol($ipn_data['address_status']).$ipn_data['address_status']; ?></td>
				</tr>
			<?php } ?>
			<tr>
				<td width="10%"><?php echo TEXT_PAYPAL_PAYMENT_TYPE; ?></td>
				<td width="90%"><?php echo $paypal->getPaymentType($ipn_data['payment_type']); ?></td>
			</tr>
		</table>
		<?php echo '<a class="button" href="'.xtc_href_link(FILENAME_PAYPAL).'">'.BUTTON_BACK.'</a>'; ?>
	</div>
	<?php if(isset($ipn_id)) { ?>
		<br />
		<div class="highlightbox">
			<p class="pageHeading"><?php echo TEXT_PAYPAL_OPTIONS; ?></p>
			<table class="main" width="100%" border="0">
				<tr>
					<td width="10%"><?php echo  xtc_image(DIR_WS_ICONS . 'icon_refund.gif'); ?></td>
					<td width="90%"><a href="<?php echo xtc_href_link(FILENAME_PAYPAL,'view=refund&paypal_ipn_id='.$ipn_id); ?>"><?php echo TEXT_PAYPAL_ACTION_REFUND; ?></a></td>
				</tr>
				<?php if(PAYPAL_COUNTRY_MODE=='uk') { ?>
					<tr>
						<td width="10%"><?php echo  xtc_image(DIR_WS_ICONS . 'icon_capture.gif'); ?></td>
						<td width="90%"><a href="<?php echo xtc_href_link(FILENAME_PAYPAL,'view=capture&paypal_ipn_id='.$ipn_id); ?>"><?php echo TEXT_PAYPAL_ACTION_CAPTURE; ?></a></td>
					</tr>
				<?php } ?>
			</table>
		</div>
	<?php }
//}
	if($ipn_data['parent_txn_id'] != '') {
		// get original transaction
		$_orig_query ="SELECT * FROM ".TABLE_PAYPAL." WHERE txn_id = '".$ipn_data['parent_txn_id']."'";
		$_orig_query = xtc_db_query($_orig_query);
		if(xtc_db_num_rows($_orig_query)>0) {
	?>
			<br />
			<div class="highlightbox">
				<h1><?php echo TEXT_PAYPAL_TRANSACTION_ORIGINAL; ?></h1>
				<table border="0" width="100%" cellspacing="0" cellpadding="2">
					<tr class="dataTableHeadingRow">
						<td class="dataTableHeadingContent" width="10">&nbsp;</td>
						<td class="dataTableHeadingContent"><?php echo TEXT_PAYPAL_PAYMENT_DATE; ?></td>
						<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PAYPAL_ID; ?></td>
						<td class="dataTableHeadingContent"><?php echo TEXT_PAYPAL_TYPE; ?></td>
						<td class="dataTableHeadingContent""><?php echo TEXT_PAYPAL_PAYMENT_STATUS; ?></td>
						<td class="dataTableHeadingContent"><?php echo TEXT_PAYPAL_DETAIL; ?></td>
						<td class="dataTableHeadingContent"><?php echo TEXT_PAYPAL_TOTAL; ?></td>
						<td class="dataTableHeadingContent"><?php echo TEXT_PAYPAL_FEE; ?></td>
						<td class="dataTableHeadingContent"><?php echo TEXT_PAYPAL_NETTO; ?></td>
					</tr>
					<?php
					while($conn_data = xtc_db_fetch_array($_orig_query)) {
					?>
						<tr class="dataTableRow">
							<td class="dataTableContent" nowrap><?php echo $paypal->getStatusSymbol($conn_data['payment_status'],$conn_data['txn_type']); ?></td>
							<td class="dataTableContent" nowrap><?php echo xtc_datetime_short($conn_data['payment_date']); ?></td>
							<td class="dataTableContent" nowrap><?php echo $conn_data['txn_id']; ?></td>
							<td class="dataTableContent" nowrap><?php echo $paypal->getStatusName($conn_data['payment_status'],$conn_data['txn_type']); ?></td>
							<td class="dataTableContent" nowrap><?php echo $conn_data['payment_type']; ?></td>
							<td class="dataTableContent" nowrap><?php echo '<a href="'.xtc_href_link(FILENAME_PAYPAL,'view=detail&paypal_ipn_id='.$conn_data['paypal_ipn_id']).'">'.TEXT_PAYPAL_DETAIL.'</a>'; ?></td>
							<td class="dataTableContent" nowrap><?php echo xtc_format_price($conn_data['mc_gross'],1,false,0).' '.$conn_data['mc_currency']; ?></td>
							<td class="dataTableContent" nowrap><?php echo xtc_format_price($conn_data['mc_fee'],1,false,0).' '.$conn_data['mc_currency']; ?></td>
							<td class="dataTableContent" nowrap><?php echo xtc_format_price(round($conn_data['mc_gross']-$conn_data['mc_fee'],2),1,false,0).' '.$conn_data['mc_currency']; ?></td>
						</tr>
					<?php } ?>
				</table>
			</div>
		<?php
		}
	}
	// show transaction History
	$hist_query = "SELECT * FROM ".TABLE_PAYPAL_STATUS_HISTORY." WHERE paypal_ipn_id='".$ipn_id."'";
	$hist_query = xtc_db_query($hist_query);
	if(xtc_db_num_rows($hist_query)>0) {
	?>
		<br />
		<div class="highlightbox">
			<h1><?php echo TEXT_PAYPAL_TRANSACTION_HISTORY; ?></h1>
			<table border="0" width="400" cellspacing="0" cellpadding="2">
				<tr class="dataTableHeadingRow">
					<td class="dataTableHeadingContent" width="10">&nbsp;</td>
					<td class="dataTableHeadingContent"><?php echo TEXT_PAYPAL_PAYMENT_DATE; ?></td>
					<td class="dataTableHeadingContent""><?php echo TEXT_PAYPAL_PAYMENT_STATUS; ?></td>
					<td class="dataTableHeadingContent"><?php echo TEXT_PAYPAL_PENDING_REASON; ?></td>
					<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PAYMENT_AMOUNT; ?></td>
				</tr>
				<?php
				while($hist_data = xtc_db_fetch_array($hist_query)) {
				?>
					<tr class="dataTableRow">
						<td class="dataTableContent" nowrap><?php echo $paypal->getStatusSymbol($hist_data['payment_status'],'',$hist_data['pending_reason']); ?></td>
						<td class="dataTableContent" nowrap><?php echo xtc_datetime_short($hist_data['date_added']); ?></td>
						<td class="dataTableContent" nowrap><?php echo $paypal->getStatusName($hist_data['payment_status']); ?></td>
						<td class="dataTableContent" nowrap><?php echo $hist_data['pending_reason']; ?></td>
						<td class="dataTableContent" nowrap><?php echo $hist_data['mc_amount']; ?></td>
					</tr>
				<?php } ?>
			</table>
		</div>
	<?php
	}
	// get connected transactions
	// get original transaction
	$conn_query ="SELECT * FROM ".TABLE_PAYPAL." WHERE parent_txn_id = '".$ipn_data['txn_id']."' or (txn_id='".$ipn_data['txn_id']."' and paypal_ipn_id != '".$ipn_data['paypal_ipn_id']."') ORDER BY payment_date";
	$conn_query = xtc_db_query($conn_query);
	if(xtc_db_num_rows($conn_query)>0) {?>
		<br />
		<div class="highlightbox">
			<h1><?php echo TEXT_PAYPAL_TRANSACTION_CONNECTED; ?></h1>
			<table border="0" width="100%" cellspacing="0" cellpadding="2">
				<tr class="dataTableHeadingRow">
					<td class="dataTableHeadingContent" width="10">&nbsp;</td>
					<td class="dataTableHeadingContent"><?php echo TEXT_PAYPAL_PAYMENT_DATE; ?></td>
					<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PAYPAL_ID; ?></td>
					<td class="dataTableHeadingContent"><?php echo TEXT_PAYPAL_TYPE; ?></td>
					<td class="dataTableHeadingContent""><?php echo TEXT_PAYPAL_PAYMENT_STATUS; ?></td>
					<td class="dataTableHeadingContent"><?php echo TEXT_PAYPAL_DETAIL; ?></td>
					<td class="dataTableHeadingContent"><?php echo TEXT_PAYPAL_TOTAL; ?></td>
					<td class="dataTableHeadingContent"><?php echo TEXT_PAYPAL_FEE; ?></td>
					<td class="dataTableHeadingContent"><?php echo TEXT_PAYPAL_NETTO; ?></td>
				</tr>
				<?php
				while($conn_data = xtc_db_fetch_array($conn_query)) {
				?>
					<tr class="dataTableRow">
						<td class="dataTableContent" nowrap><?php echo $paypal->getStatusSymbol($conn_data['payment_status'],$conn_data['txn_type']); ?></td>
						<td class="dataTableContent" nowrap><?php echo xtc_datetime_short($conn_data['payment_date']); ?></td>
						<td class="dataTableContent" nowrap><?php echo $conn_data['txn_id']; ?></td>
						<td class="dataTableContent" nowrap><?php echo $conn_data['payment_type']; ?></td>
						<td class="dataTableContent" nowrap><?php echo $paypal->getStatusName($conn_data['payment_status'],$conn_data['txn_type']); ?></td>
						<td class="dataTableContent" nowrap><?php echo '<a href="'.xtc_href_link(FILENAME_PAYPAL,'view=detail&paypal_ipn_id='.$conn_data['paypal_ipn_id']).'">'.TEXT_PAYPAL_DETAIL.'</a>'; ?></td>
						<td class="dataTableContent" nowrap><?php echo xtc_format_price($conn_data['mc_gross'],1,false,0).' '.$conn_data['mc_currency']; ?></td>
						<td class="dataTableContent" nowrap><?php echo xtc_format_price($conn_data['mc_fee'],1,false,0).' '.$conn_data['mc_currency']; ?></td>
						<td class="dataTableContent" nowrap><?php echo xtc_format_price(round($conn_data['mc_gross']-$conn_data['mc_fee'],2),1,false,0).' '.$conn_data['mc_currency']; ?></td>
					</tr>
				<?php } ?>
			</table>
		</div>
		<?php
	}
}
?>