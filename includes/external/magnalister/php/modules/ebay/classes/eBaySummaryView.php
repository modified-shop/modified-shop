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
 * $Id: eBaySummaryView.php 733 2011-01-21 07:42:58Z derpapst $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimpleSummaryView.php');

class eBaySummaryView extends SimpleSummaryView {
	private $inventoryData = array();
	
	private $fixedPriceStockSync = '';
	private $chineseStockSync = '';

	public function __construct($settings = array()) {
		global $_MagnaSession;
		$settings = array_merge(array(
			'selectionName' => 'checkin',
			'currency'      => getCurrencyFromMarketplace($_MagnaSession['mpID']),
		), $settings);

		parent::__construct($settings);
		
		$this->fixedPriceStockSync = getDBConfigValue($this->marketplace.'.fixed.quantity.type', $this->mpID);
		$this->chineseStockSync = 'lump';
		$this->inventoryPriceSync = getDBConfigValue($this->marketplace.'.inventorysync.price', $this->mpID, false);
	}

	protected function additionalInitialisation() {
		$pIDs = array();
		foreach ($this->selection as $pID => $item) {
			$pIDs[] = $pID;
		}
		$request = array (
			'ACTION' => 'GetInventoryBySKUs',
			'DATA' => array(),
		);
		foreach ($pIDs as $pID) {
			$request['DATA'][]['SKU'] = magnaPID2SKU($pID);
		}

		MagnaConnector::gi()->setTimeOutInSeconds(1800);
		try {
			$result = MagnaConnector::gi()->submitRequest($request);
			
			if (!empty($result['DATA'])) {
				foreach ($result['DATA'] as $item) {
					$this->inventoryData[magnaSKU2pID($item['SKU'])] = $item;
				}
			}
			unset($request);
			unset($result);
			
		} catch (MagnaException $e) {
			if ($e->getCode() == MagnaException::TIMEOUT) {
				$e->setCriticalStatus(false);
			}
		}
		MagnaConnector::gi()->resetTimeOut();
	}

	protected function processAdditionalPost() {
		parent::processAdditionalPost();

		if (isset($_GET['kind']) && ($_GET['kind'] == 'ajax')) {
			if (!isset($_POST['productID'])) {
				return;
			}
			$pID = $this->ajaxReply['pID'] = substr($_POST['productID'], strpos($_POST['productID'], '_') + 1);
			if (!array_key_exists($pID, $this->selection)) {
				$this->loadItemToSelection($pID);
			}
			$this->extendProductAttributes($pID, $this->selection[$pID]);

			if (isset($_POST['changeQuantity'])) {
				$_POST['quantity'][$pID] = $_POST['changeQuantity'];
			}
			if (isset($_POST['changePrice'])) {
				$_POST['price'][$pID] = $_POST['changePrice'];
			}
		}
		
		if (array_key_exists('quantity', $_POST)) {
			foreach ($_POST['quantity'] as $pID => &$item) {
				if (ctype_digit($_POST['quantity'][$pID])) {
					$this->selection[$pID]['quantity'] = $this->ajaxReply['value'] = $_POST['quantity'][$pID];
				}
			}
		}

		if (array_key_exists('price', $_POST)) {
			$format = $this->simplePrice->getFormatOptions();
			foreach ($_POST['price'] as $pID => $price) {
				$price = $_POST['price'][$pID];
				if (($price == (string)(float)$price)) {
					$price = (float)$price;
				} else {
					$price = priceToFloat($_POST['price'][$pID], $format);
				}
				if ($price > 0) {
					# if changed by hand to not-configured price, freeze price
					if ($price != makePrice($pID, (string)MagnaDB::gi()->fetchOne(
					'SELECT ListingType FROM '.TABLE_MAGNA_EBAY_PROPERTIES
					.' WHERE products_id = '.$pID.' AND mpID = '
					.$this->_magnasession['mpID']))) {
						MagnaDB::gi()->update(TABLE_MAGNA_EBAY_PROPERTIES,
						array (	'Price' => $price ),
						array (	'products_id' => $pID,
							'mpID' => $this->_magnasession['mpID'] )
						);
					} else {
					# if frozen before, but resettet to configured price, unfreeze
						MagnaDB::gi()->update(TABLE_MAGNA_EBAY_PROPERTIES,
						array (	'Price' => 0.0 ),
						array (	'products_id' => $pID,
							'mpID' => $this->_magnasession['mpID'] )
						);
					}
					$this->selection[$pID]['price'] = $this->ajaxReply['value'] = $price;
				}
			}
		}

	}

	protected function getAdditionalHeadlines() {
		$ret = parent::getAdditionalHeadlines();

		if ($this->inventoryPriceSync == 'auto') {
			return '
				<td title="'.ML_LABEL_BRUTTO.'">'.ML_EBAY_LABEL_EBAY_PRICE.'&nbsp;<span class="small">'.$this->settings['currency'].'</span></td>
				'.$ret.'
				<td>'.ML_LABEL_QUANTITY_AVAILABLE.'</td>
				<td>'.$this->provideResetFunction(ML_LABEL_QUANTITY, 'quantity').'</td>
			';
		}

		return '
			<td title="'.ML_LABEL_BRUTTO.'">'.$this->provideResetFunction(
				ML_EBAY_LABEL_EBAY_PRICE.' <span class="small">'.
					$this->settings['currency'].
			    '</span>',
			    'price',
			    'formatPriceWoCur(#VAL#, '.json_encode(array('2', '.', '')).')'
			).'</td>
			'.$ret.'
			<td>'.ML_LABEL_QUANTITY_AVAILABLE.'</td>
			<td>'.$this->provideResetFunction(ML_LABEL_QUANTITY, 'quantity').'</td>
		';
	}

	protected function extendProductAttributes($pID, &$data) {
		parent::extendProductAttributes($pID, $data);
		$mp = $this->_magnasession['currentPlatform'];
		$mpID = $this->_magnasession['mpID'];
		$maxQuantity = (int)getDBConfigValue('ebay.maxquantity', $mpID, 0);
		if (0 == $maxQuantity) $maxQuantity = PHP_INT_MAX;

		if (getDBConfigValue('general.keytype', '0') == 'artNr') {
			$products_model = MagnaDB::gi()->fetchOne('SELECT products_model FROM '.TABLE_PRODUCTS.' WHERE products_id = '.$pID);
			$whereproduct = "WHERE products_model = '".MagnaDB::gi()->escape($products_model)."'";
		} else {
			$whereproduct = "WHERE products_id = $pID";
		}
		$listingproperties = MagnaDB::gi()->fetchRow('
			SELECT Price, ListingType, IF(\'Chinese\'=ListingType, \'chinese\',\'fixed\') as listingsupertype, 
			       ListingDuration as duration 
			  FROM '.TABLE_MAGNA_EBAY_PROPERTIES.' 
			   '.$whereproduct.'
			       AND mpID='.$this->_magnasession['mpID'].' 
		  ORDER BY products_id DESC
		     LIMIT 1
		');

		if (!isset($data['price']) || ($data['price'] === null)) {
			$data['price'] = makePrice($pID, $listingproperties['ListingType']);
		}
		
		if (isset($listingproperties) && is_array($listingproperties)) {
			$data['listingsupertype'] = $listingproperties['listingsupertype'];
			$data['duration'] = $listingproperties['duration'];
		}
		
		if ($data['listingsupertype'] == 'chinese') {
			// frozen price only at chinese auction
			if (0.0 != $listingproperties['Price']) {
				$data['price'] = $listingproperties['Price'];
			}
			$availableQuantity = (int)MagnaDB::gi()->fetchOne('SELECT products_quantity FROM '.TABLE_PRODUCTS.' WHERE products_id=\''.$pID.'\'');
			if ($availableQuantity > 0) {
				$data['quantity'] = 1;
			} else {
				$data['quantity'] = 0;
			}
		} else {
			if (!isset($data['quantity']) || ($data['quantity'] === null) || getDBConfigValue(array($mp.'.usevariations', 'val'), $mpID, true)) {
				$quantityType = getDBConfigValue($mp.'.'.$data['listingsupertype'].'.quantity.type', $mpID);
				switch ($quantityType) {
					case 'stock': {
						# Nehme Anzahl Varianten, soweit Varianten lt konfig aktiviert, und soweit solche existieren
						if (getDBConfigValue(array($mp.'.usevariations', 'val'), $mpID, true)
							&& variationsExist($pID))
							$data['quantity'] = min((int)getProductVariationsQuantity($pID), $maxQuantity);
						else 
							$data['quantity'] = min((int)MagnaDB::gi()->fetchOne('SELECT products_quantity FROM '.TABLE_PRODUCTS.' WHERE products_id=\''.$pID.'\''), $maxQuantity);
						if ($data['quantity'] < 0) {
							$data['quantity'] = 0;
						}
						break;
					}
					case 'stocksub': {
						if (getDBConfigValue(array($mp.'.usevariations', 'val'), $mpID, true)
							&& variationsExist($pID))
							$data['quantity'] = min((int)getProductVariationsQuantity($pID, getDBConfigValue($mp.'.'.$data['listingsupertype'].'.quantity.value', $mpID, 0)), $maxQuantity);
						else 
							$data['quantity'] = min((int)MagnaDB::gi()->fetchOne('SELECT products_quantity FROM '.TABLE_PRODUCTS.' WHERE products_id=\''.$pID.'\'') - getDBConfigValue($mp.'.'.$data['listingsupertype'].'.quantity.value', $mpID, 0), $maxQuantity);
						if ($data['quantity'] < 0) {
							$data['quantity'] = 0;
						}
						break;
					}
					case 'infinity': {
						$data['quantity'] = -1;
						break;
					}
					default: {
						$data['quantity'] = (int)getDBConfigValue($mp.'.'.$data['listingsupertype'].'.quantity.value', $mpID, 1);
					}
				}
			}
		}
	}
	
	protected function getAdditionalItemCells($key, $dbRow) {
		$this->extendProductAttributes($dbRow['products_id'], $this->selection[$dbRow['products_id']]);
		if (getDBConfigValue(array($this->_magnasession['currentPlatform'].'.usevariations', 'val'), $this->_magnasession['mpID'], true)) {
			$tmp_quantity = (int)getProductVariationsQuantity($dbRow['products_id']);
			if ($tmp_quantity > 0) $dbRow['products_quantity'] = $tmp_quantity; 
		}
		$type = $this->selection[$dbRow['products_id']]['listingsupertype'];
		if ($type == 'chinese') {
			$stocktype = $this->chineseStockSync;
		} else {
			$stocktype = $this->fixedPriceStockSync;
		}
		$stock = (($stocktype == 'stock') || ($stocktype == 'stocksub'));

		return '
			<td><table class="nostyle"><tbody>
					<tr><td>'.ML_LABEL_NEW.':&nbsp;</td><td>'
			.$this->simplePrice->setPrice($this->selection[$dbRow['products_id']]['price'])->getPrice().'
						<input type="hidden" id="backup_price_'.$dbRow['products_id'].'"
					           value="'.$this->simplePrice->getPrice().'"/>
					</td></tr>
			    	<tr><td>'.ML_LABEL_OLD.':&nbsp;</td><td>'.(
						array_key_exists($dbRow['products_id'], $this->inventoryData) ?
							/* Waehrung von Preis nicht umrechnen, da bereits in Zielwaehrung. */
							$this->simplePrice->setPrice($this->inventoryData[$dbRow['products_id']]['Price'])->formatWOCurrency() :
							'&mdash;'
					).'</td></tr>
			    </tbody></table>
			</td>
			'.parent::getAdditionalItemCells($key, $dbRow).'
			<td>'.(int)$dbRow['products_quantity'].'</td>
			<td><input type="hidden" id="old_quantity_'.$dbRow['products_id'].'"
				       value="'.$this->selection[$dbRow['products_id']]['quantity'].'"/>
				'.$this->selection[$dbRow['products_id']]['quantity'].'</td>'
				;
	}

	# beim Preis anders als im allg. Fall: Reset gibt Preis aus Konfig, auch wenn sonst eingefroren
	protected function resetSelectionAttributes($reset, $limit) {
		if ('price' != $reset) {
			parent::resetSelectionAttributes($reset, $limit);
			return;
		}
		$limit[0] = (int)$limit[0];
		$limit[1] = (int)$limit[1];
		
		if ($limit[1] <= 0) {
			$this->ajaxReply['proceed'] = false;
			$this->ajaxReply['error'] = 'Invalid LIMIT parameter';
			return;
		}
	
		$itemsResult = MagnaDB::gi()->query('
			SELECT pID, data
			  FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID=\''.$this->_magnasession['mpID'].'\' AND
			       selectionname=\''.$this->settings['selectionName'].'\' AND
			       session_id=\''.session_id().'\'
			 LIMIT '.$limit[0].','.$limit[1].'
		');
		$fetchedElements = 0;
		while ($row = MagnaDB::gi()->fetchNext($itemsResult)) {
			$sListingType = MagnaDB::gi()->fetchOne("
				SELECT ListingType
				  FROM ".TABLE_MAGNA_EBAY_PROPERTIES."
				 WHERE     products_id = '".$row['pID']."'
				       AND mpID = '".$this->_magnasession['mpID']."'
			  ORDER BY products_id DESC LIMIT 1
			");

			if ($sListingType == 'chinese') {
				continue;
			}

			++$fetchedElements;

			$row['data'] = unserialize($row['data']);

			if (!array_key_exists($reset, $row['data'])) {
				$this->ajaxReply['skiped'][] = $row['pID'];
				continue;
			}
			unset($row['data'][$reset]);

			$row['data']['price'] = makePrice($row['pID'], $sListingType);
			# $this->extendProductAttributes($row['pID'], $row['data']);
			
			$this->ajaxReply['changedData'][$row['pID']][$reset] = $row['data'][$reset];

			MagnaDB::gi()->insert(TABLE_MAGNA_SELECTION, array (
				'pID' => $row['pID'],
				'data' => serialize($row['data']),
				'mpID' => $this->_magnasession['mpID'],
				'selectionname' => $this->settings['selectionName'],
				'session_id' => session_id(),
				'expires' => gmdate('Y-m-d H:i:s')
			), true);
		}
		$this->ajaxReply['proceed'] = $fetchedElements >= $limit[1];
		$this->ajaxReply['error'] = false;
		$this->ajaxReply['limit'] = array($limit[0] + $limit[1], $limit[1]);
	}
	
	public function renderSelection() {
		$topHTML = '';
		/* Currency-Check */
		if ($this->settings['currency'] != DEFAULT_CURRENCY) {
			$topHTML .= '<p class="noticeBox"><b class="notice">'.ML_LABEL_ATTENTION.':</b> '.sprintf(
				ML_GENERIC_ERROR_WRONG_CURRENCY,
				$this->settings['currency'],
				DEFAULT_CURRENCY
			).'</p>';
		}

		ob_start();
		$formatOptions = $this->simplePrice->getFormatOptions();
		$formatOptions = array('2', '.', '');
?>
<script type="text/javascript">/*<![CDATA[*/
var formatPriceOptions = <?php echo json_encode($formatOptions); ?>;

$(document).ready(function() {
	$('#summaryForm input[name^="quantity"]').each(function(i, e) {
		//myConsole.log($(e).attr('id'));
		$(e).blur(function() {
			val = jQuery.trim($(this).val());
			tfID = $(this).attr('id');
			if ((val == '') || !/^(-1|[0-9]*)$/.test(val) || (val < -1)) {
				alert(unescape(<?php echo "'".html2url(ML_ERROR_INVALID_NUMBER)."'"; ?>));
				val = $('#old_'+tfID).val();
				$(this).val(val);
			}
			$.ajax({
				type: 'POST',
				url: '<?php echo toURL($this->url, array('kind' => 'ajax'), true); ?>',
				data: {
					'changeQuantity':val,
					'productID':tfID
				},
				dataType: 'json'
			});
		}).keypress(function(event) {
			if (event.keyCode == '13') {
				/* Bei ENTER nicht Form absenden, aber ajax request bei onBlur ausfuehren */
				event.preventDefault();
				$(e).blur();
			}
		});
	});
	
	$('#summaryForm input[name^="price"]').each(function(i, e) {
		//myConsole.log($(e).attr('id'));
		$(e).blur(function() {
			val = jQuery.trim($(e).val());
			tfID = $(e).attr('id');
			price = convertPriceToFloat(val, formatPriceOptions);
			myConsole.log(price);
			if (price < 0) {
				alert(unescape(<?php echo "'".html2url(ML_ERROR_INVALID_NUMBER)."'"; ?>));
				$(e).val(formatPriceWoCur($('#backup_'+tfID).val(), formatPriceOptions));
			} else {
				jQuery.ajax({
					type: 'POST',
					url: '<?php echo toURL($this->url, array('kind' => 'ajax'), true); ?>',
					dataType: 'json',
					data: {
						'changePrice':price,
						'productID':tfID,
					},
					success: function(data) {
						//myConsole.log(data);
						$('#backup_'+tfID).val(data.value);
						$(e).val(formatPriceWoCur(data.value, formatPriceOptions));
					}
				});
			}
			//myConsole.log($(this).attr('id')+': '+val);
		}).keypress(function(event) {
			if (event.keyCode == '13') {
				/* Bei ENTER nicht Form absenden, aber ajax request bei onBlur ausfuehren */
				event.preventDefault();
				$(e).blur();
			}
		});
	});
});
/*]]>*/</script>
<?php
		$html = ob_get_contents();
		ob_end_clean();
		return $topHTML.parent::renderSelection().$html;
	}
	
	protected function getTopInfoBox() { 
		return 'eBay-Listings';
	}
}
