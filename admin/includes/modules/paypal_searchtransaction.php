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
 echo xtc_draw_form('transaction_search', FILENAME_PAYPAL, xtc_get_all_get_params(array('action')) . 'action=perform');
?>
<div class="highlightbox">
	<p class="pageHeading"><?php echo TEXT_PAYPAL_SEARCH_TRANSACTION; ?></p>
	<table "width="100%"  border="0">
		<tr>
			<td class="main" width="200" valign="top">Suchen nach:</td>
			<td class="main"><input type="text" name="search_type" value=""></td>
		</tr>
		<tr>
			<td class="main" width="200" valign="top">In:</td>
			<td class="main">
				<select name="search_first_type"><option value="email_alias">E-Mail</option>
				<option value="trans_id">Transaktionscode</option>
				<option value="last_name_only">Nachname</option>
				<option value="last_name">Nachname, Vorname</option>
				<option value="invoice_id">Rechnungsnummer</option>
				</select>
			</td>
		</tr>
		<tr>
			<td class="main" width="200" valign="top"><input type="radio" name="span" value="broad">Zeitraum</td>
			<td class="main">
				<select name="for" onChange="javascript:CheckMe('0',this.form);"><option value="1">Letzter Tag</option>
				<option value="2">Letzte Woche</option>
				<option value="3">Letzter Monat</option>
				<option value="4">Letztes Jahr</option></select>
			</td>
		</tr>
		<tr>
			<td class="main" width="200" valign="top"><input type="radio" checked name="span" value="narrow">Von:</td>
			<td class="main">
				<table align="left" border="0" cellpadding="0" cellspacing="0">
					<tr>
						<td><input type="text" size="2" maxlength="2" onFocus="javascript:CheckMe('1',this.form);" name="from_t" value="<?php echo $date['last_month']['tt']; ?>"></td>
						<td class="main"> / </td>
						<td><input type="text" size="2" maxlength="2" onFocus="javascript:CheckMe('1',this.form);" name="from_m" value="<?php echo $date['last_month']['mm']; ?>"></td>
						<td class="main"> / </td>
						<td><input type="text" size="4" maxlength="4" onFocus="javascript:CheckMe('1',this.form);" name="from_y" value="<?php echo $date['last_month']['yyyy']; ?>"></td>
					</tr>
					<tr>
						<td class="smallText" colspan="2">tt</td>
						<td class="smallText" colspan="2">mm</td>
						<td class="smallText" colspan="2">jjjj</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td class="main" width="200" valign="top">Bis:</td>
			<td class="main">
				<table align="left" border="0" cellpadding="0" cellspacing="0">
					<tr>
						<td><input type="text" size="2" maxlength="2" onFocus="javascript:CheckMe('1',this.form);" name="to_t" value="<?php echo $date['actual']['tt']; ?>"></td>
						<td class="main"> / </td>
						<td><input type="text" size="2" maxlength="2" onFocus="javascript:CheckMe('1',this.form);" name="to_m" value="<?php echo $date['actual']['mm']; ?>"></td>
						<td class="main"> / </td>
						<td><input type="text" size="4" maxlength="4" onFocus="javascript:CheckMe('1',this.form);" name="to_y" value="<?php echo $date['actual']['yyyy']; ?>"></td>
					</tr>
					<tr>
						<td class="smallText" colspan="2">tt</td>
						<td class="smallText" colspan="2">mm</td>
						<td class="smallText" colspan="2">jjjj</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td class="main" width="200" valign="top">Suchen nach:</td>
			<td class="main"></td>
		</tr>
	</table>
	<input type="submit" class="button" value="<?php echo BUTTON_SEARCH; ?>">
	<?php echo '<a class="button" href="'.xtc_href_link(FILENAME_PAYPAL).'">Zur&uuml;ck</a>'; ?>
	</form>
</div>
<br />
<table border="0" width="100%" cellspacing="0" cellpadding="0">
	<tr>
		<td valign="top">
			<div class="highlightbox">
				<p class="pageHeading"><?php echo TEXT_PAYPAL_FOUND_TRANSACTION; ?></p>
				<?php
				if(isset($paypal->SearchError['code'])) {
					$messageStack->add($paypal->SearchError['longmessage'],'warning');
					echo $messageStack->output();
				}?>
				<table border="0" width="100%" cellspacing="0" cellpadding="2">
					<tr class="dataTableHeadingRow">
						<td class="dataTableHeadingContent" width="10">&nbsp;</td>
						<td class="dataTableHeadingContent"><?php echo TEXT_PAYPAL_PAYMENT_DATE; ?></td>
						<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_NAME; ?></td>
						<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PAYPAL_ID; ?></td>
						<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TXN_TYPE; ?></td>
						<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PAYMENT_STATUS; ?></td>
						<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PAYMENT_AMOUNT; ?></td>
						<td class="dataTableHeadingContent"><?php echo TEXT_PAYPAL_FEE; ?></td>
						<td class="dataTableHeadingContent"><?php echo TEXT_PAYPAL_NETTO; ?></td>
						<td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
					</tr>
					<?php
					if(!is_array($response)) {
						echo '<tr><td class="dataTableContent" colspan="9"> - Keine Transaktionen gefunden - </td></tr>';
					} else {
						foreach($response as $arr) { ?>
							<tr>
								<td class="dataTableContent" nowrap><?php echo $paypal->getStatusSymbol($arr['TYPE'],$arr['STATUS']); ?></td>
								<td class="dataTableContent" nowrap><?php echo $arr['TIMESTAMP']; ?></td>
								<td class="dataTableContent" nowrap><?php echo $arr['NAME']; ?></td>
								<td class="dataTableContent" nowrap><?php echo $arr['TXNID']; ?></td>
								<td class="dataTableContent" nowrap><?php echo $arr['TYPE']; ?></td>
								<td class="dataTableContent" nowrap><?php echo $arr['STATUS']; ?></td>
								<td class="dataTableContent" nowrap><?php echo $arr['AMT']; ?></td>
								<td class="dataTableContent" nowrap><?php echo $arr['FEEAMT']; ?></td>
								<td class="dataTableContent" nowrap><?php echo $arr['NETAMT']; ?></td>
								<td class="dataTableContent" nowrap><?php echo '<a href="' . xtc_href_link(FILENAME_PAYPAL, 'view=detail&txn_id='.$arr['TXNID']) . '">' . xtc_image(DIR_WS_ICONS . 'page_find.gif', IMAGE_ICON_INFO) . '</a>'; ?></td>
							</tr>
					<?php } }?>
				</table>
			</div>
		</td>
	</tr>
</table>