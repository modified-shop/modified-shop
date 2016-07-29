<?php
/**
 * 888888ba                 dP  .88888.                    dP                
 * 88    `8b                88 d8'   `88                   88                
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b. 
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88 
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88 
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P' 
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * $Id: DeletedView.php 6571 2016-03-21 08:51:30Z markus.bauer $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once (DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');
require_once (DIR_MAGNALISTER_MODULES.'amazon/amazonFunctions.php');

class DeletedView {
	private $delFromDate;
	private $deToDate;
	private $url = array();
	
	private $simplePrice = null;

	private $settings = array();

	public function __construct($settings = array()) {
		global $_MagnaSession, $_url;
		
		$this->settings = array_merge(array(
			'maxTitleChars'	=> 40,
		), $settings);

		$this->delFromDate = mktime(0, 0, 0, date('n'), 1, date('Y'));
		$this->deToDate = mktime(23, 59, 59, date('n'), date('j'), date('Y'));
		
		$this->url = $_url;
		
		$this->simpleprice = new SimplePrice();
		$this->simpleprice->setCurrency(getCurrencyFromMarketplace($_MagnaSession['mpID']));
		
		if (isset($_POST['date']['from'])) {
			$this->delFromDate = strtotime($_POST['date']['from']);
		}
		if (isset($_POST['date']['to'])) {
			$this->deToDate = strtotime($_POST['date']['to']);
			$this->deToDate += 24 * 60 * 60 - 1;
		}
	}

	private function getDeteltedItems() {
		$result = array();
		try {
			$result = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'GetDeletedItemsForDateRange',
				'BEGIN' => date('Y-m-d H:i:s', $this->delFromDate),
				'END' => date('Y-m-d H:i:s', $this->deToDate),
			));
		} catch (MagnaException $e) {
			$this->latestChange = 0;
			return false;
		}
		if (!array_key_exists('DATA', $result) || empty($result['DATA'])) {
			return array();
		}
		foreach ($result['DATA'] as &$item) {
			$item['DateAdded'] = strtotime($item['DateAdded'].' +0000');
			$pID = magnaAmazonSKU2pID($item['SKU'], $item['ASIN']);
			$item['ShopItemName'] = MagnaDB::gi()->fetchOne('
				 SELECT pd.products_name FROM '.TABLE_PRODUCTS_DESCRIPTION.' pd
				  WHERE pd.language_id = \''.$_SESSION['languages_id'].'\' AND
				        pd.products_id=\''.$pID.'\'
				  LIMIT 1
			');
			if (!empty($item['ShopItemName'])) {
				$item['ShopItemNameShort'] = (
					(strlen($item['ShopItemName']) > $this->settings['maxTitleChars'] + 2) 
						? 
							(fixHTMLUTF8Entities(substr($item['ShopItemName'], 0, $this->settings['maxTitleChars']), ENT_COMPAT).'&hellip;')
						: 
							fixHTMLUTF8Entities($item['ShopItemName'], ENT_COMPAT)
				);
				$item['ShopItemName'] = fixHTMLUTF8Entities($item['ShopItemName'], ENT_COMPAT);
			} else {
				$item['ShopItemNameShort'] = $item['ShopItemNameShort'] = '&mdash;';
			}
		}
		return $result['DATA'];
	}

	public function renderView() {
		$data = $this->getDeteltedItems();
		#echo print_m($data);
		$fromDate = date('Y', $this->delFromDate).', '.(date('n', $this->delFromDate) - 1).', 1';
		$toDate   = date('Y', $this->deToDate).', '.(date('n', $this->deToDate) - 1).', '.date('j', $this->deToDate);
		
		$langCode = MagnaDB::gi()->fetchOne('
			SELECT code FROM '.TABLE_LANGUAGES.' WHERE languages_id=\''.$_SESSION['languages_id'].'\' LIMIT 1
		');
		
		$html = '
			<form method="POST" action="'.toURL($this->url, array('view' => 'deleted')).'"><table class="magnaframe">
				<thead><tr><th>Zeitraum</th></tr></thead>
				<tbody><tr><td class="fullWidth">
					<table><tbody>
						<tr>
							<td>Von:</td>
							<td>
								<input type="text" id="fromDate" readonly="readonly"/>
								<input type="hidden" id="fromActualDate" name="date[from]" value=""/>
							</td>
							<td>Bis:</td>
							<td>
								<input type="text" id="toDate" readonly="readonly"/>
								<input type="hidden" id="toActualDate" name="date[to]" value=""/>
							</td>
							<td><input class="ml-button" type="submit" value="Los"/></td>
						</tr>
					</tbody></table>
				</td></tr></tbody>
			</table></form>
			<script type="text/javascript">
				$(document).ready(function() {
					$.datepicker.setDefaults($.datepicker.regional[\'\']);
					$("#fromDate").datepicker(
						$.datepicker.regional[\''.$langCode.'\']
					).datepicker(
						"option", "altField", "#fromActualDate"
					).datepicker(
						"option", "altFormat", "yy-mm-dd"
					).datepicker(
						"option", "defaultDate", new Date('.$fromDate.')
					);
					var dateFormat = $("#fromDate").datepicker("option", "dateFormat");
					$("#fromDate").val($.datepicker.formatDate(dateFormat, new Date('.$fromDate.')));
					$("#fromActualDate").val($.datepicker.formatDate("yy-mm-dd", new Date('.$fromDate.')));

					$("#toDate").datepicker(
						$.datepicker.regional[\''.$langCode.'\']
					).datepicker(
						"option", "altField", "#toActualDate"
					).datepicker(
						"option", "altFormat", "yy-mm-dd"
					).datepicker(
						"option", "defaultDate", new Date('.$toDate.')
					);
					$("#toDate").val($.datepicker.formatDate(dateFormat, new Date('.$toDate.')));
					$("#toActualDate").val($.datepicker.formatDate("yy-mm-dd", new Date('.$toDate.')));
				});
			</script>';
		
		if (is_array($data) && !empty($data)) {
			$html .= '
				<table id="deleted" class="datagrid">
					<thead><tr>
						<td>'.ML_LABEL_SHOP_TITLE.'</td>
						<td>ASIN</td>
						<td>'.ML_AMAZON_LABEL_AMAZON_PRICE.'</td>
						<td>'.ML_LABEL_QUANTITY.'</td>
						<td>'.ML_GENERIC_DELETEDDATE.'</td>
						<td>'.ML_GENERIC_STATUS.'</td>
					</tr></thead>
					<tbody>
			';

			$oddEven = false;
			foreach ($data as $item) {
				/* Waehrung von Preis nicht umrechnen, da bereits in Zielwaehrung. */
				$html .= '
					<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
						<td title="'.$item['ShopItemName'].'">'.$item['ShopItemNameShort'].'</td>
						<td>'.getAmazonOfferLink($item['ASIN'], ML_AMAZON_LABEL_SAME_PRODUCTS).'</a></td>
						<td>'.$this->simpleprice->setPrice($item['Price'])->format().'</td>
						<td>'.$item['Quantity'].'</td>
						<td>'.date("d.m.Y", $item['DateAdded']).' &nbsp;&nbsp;<span class="small">'.date("H:i", $item['DateAdded']).'</span>'.'</td>
						<td title="'.ML_GENERIC_DELETED.'"><img src="'.DIR_MAGNALISTER_WS_IMAGES.'status/green_dot.png" alt="'.ML_GENERIC_DELETED.'"/></td>
					</tr>';
			}
			$html .= '
					</tbody>
				</table>';
		} else {
			$html .= '<table class="magnaframe"><tbody><tr><td>'.ML_GENERIC_NO_DELETED_ITEMS_IN_TIMEFRAME.'</td></tr></tbody></table>';
		}
		return $html;
	}

}
