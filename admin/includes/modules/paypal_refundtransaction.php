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
echo xtc_draw_form('refund_transaction', FILENAME_PAYPAL, xtc_get_all_get_params(array('action')) . 'action=perform');
echo xtc_draw_hidden_field('txn_id', $ipn_data['txn_id']);
echo xtc_draw_hidden_field('amount', $ipn_data['mc_gross']);
echo xtc_draw_hidden_field('ipn_id', (int)$_GET['paypal_ipn_id']);
?>
<div class="highlightbox">
	<p class="pageHeading"><?php echo TEXT_PAYPAL_REFUND_TRANSACTION; ?></p>
	<p><?php echo TEXT_PAYPAL_NOTE_REFUND_INFO; ?></p>
	<table class="main" width="100%" border="0">
		<tr>
			<td colspan="2"><hr noshade></td>
		</tr>
		<tr>
			<td width="10%" nowrap="nowrap"><?php echo TEXT_PAYPAL_TXN_ID; ?></td>
			<td width="90%"><?php echo $ipn_data['txn_id']; ?></td>
		</tr>
		<?php if($ipn_data['address_name']!='') { ?>
			<tr>
				<td width="10%" valign="top"><?php echo TEXT_PAYPAL_ADRESS; ?></td>
				<td width="90%"><?php echo $ipn_data['address_name']; ?></td>
			</tr>
		<?php } else { ?>
			<tr>
				<td width="10%" valign="top"><?php echo TEXT_PAYPAL_ADRESS; ?></td>
				<td width="90%"><?php echo $ipn_data['first_name'].' '.$ipn_data['last_name']; ?></td>
			</tr>
		<?php } ?>
		<tr>
			<td width="10%" nowrap="nowrap"><?php echo TEXT_PAYPAL_PAYER_EMAIL; ?></td>
			<td width="90%" valign="middle"><?php echo $ipn_data['payer_email'];?></td>
		</tr>
		<tr>
			<td width="10%" nowrap="nowrap"><?php echo TEXT_PAYPAL_TRANSACTION_TOTAL; ?></td>
			<td width="90%"><?php echo $ipn_data['mc_gross'].' '.$ipn_data['mc_currency']; ?></td>
		</tr>
		<tr>
			<td width="10%" nowrap="nowrap"><?php echo TEXT_PAYPAL_TRANSACTION_LEFT; ?></td>
			<td width="90%"><?php echo $ipn_data['mc_gross'].' '.$ipn_data['mc_currency']; ?></td>
		</tr>
		<tr>
			<td width="10%" nowrap="nowrap"><?php echo TEXT_PAYPAL_AMOUNT; ?></td>
			<td width="90%"><?php echo xtc_draw_input_field('refund_amount',$ipn_data['mc_gross'],'size="10"'); ?></td>
		</tr>
		<tr>
			<td width="10%" valign="top"><?php echo TEXT_PAYPAL_REFUND_NOTE; ?></td>
			<td width="90%">
				<?php echo xtc_draw_textarea_field('refund_info', '', '50', '5', ''); ?>
			</td>
		</tr>
	</table>
	<input type="submit" class="button" value="<?php echo REFUND; ?>">
	<?php echo '<a class="button" href="'.xtc_href_link(FILENAME_PAYPAL).'">Zur&uuml;ck</a>'; ?>
</div>
</form>