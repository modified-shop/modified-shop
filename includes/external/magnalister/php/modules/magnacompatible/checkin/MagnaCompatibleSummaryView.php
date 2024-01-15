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
 * $Id: MagnaCompatibleSummaryView.php 1018 2011-04-29 11:20:46Z derpapst $
 *
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimpleSummaryView.php');

class MagnaCompatibleSummaryView extends SimpleSummaryView {
	protected $inventoryData = array();
	
	public function __construct($settings = array()) {
		global $_MagnaSession;
		$settings = array_merge(array(
			'selectionName' => 'checkin',
			'currency'      => getCurrencyFromMarketplace($_MagnaSession['mpID']),
		), $settings);

		parent::__construct($settings);

		$this->inventoryPriceSync = getDBConfigValue($this->marketplace.'.inventorysync.price', $this->mpID, false);
	}
	
	protected function additionalInitialisation() {
		$pIDs = array();
		foreach ($this->selection as $pID => $item) {
			$pIDs[] = $pID;
		}
		//echo print_m($this->selection, '$this->selection');

		$request = array (
			'ACTION' => 'GetInventoryBySKUs',
			'DATA' => array(),
		);
		foreach ($pIDs as $pID) {
			$request['DATA'][]['SKU'] = magnaPID2SKU($pID);
		}
		
		MagnaConnector::gi()->setTimeOutInSeconds(3);
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
		if ($this->isAjax) {
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
		#if (!$this->isAjax) echo print_m($_POST, '$_POST');
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
					$this->selection[$pID]['price'] = $this->ajaxReply['value'] = $price;
				}
			}
		}
	}

	protected function extendProductAttributes($pID, &$data) {
		$mp = $this->_magnasession['currentPlatform'];
		if (!isset($data['quantity']) || ($data['quantity'] === null)) {
			$quantityType = getDBConfigValue($mp.'.quantity.type', $this->_magnasession['mpID']);
			switch ($quantityType) {
				case 'stock': {
					$data['quantity'] = (int)MagnaDB::gi()->fetchOne('SELECT products_quantity FROM '.TABLE_PRODUCTS.' WHERE products_id=\''.$pID.'\'');
					break;
				}
				case 'stocksub': {
					$data['quantity'] = (int)MagnaDB::gi()->fetchOne(
											'SELECT products_quantity FROM '.TABLE_PRODUCTS.' WHERE products_id=\''.$pID.'\''
										) - getDBConfigValue($mp.'.quantity.value', $this->_magnasession['mpID'], 0);
					break;
				}
				default: {
					$data['quantity'] = getDBConfigValue($mp.'.quantity.value', $this->_magnasession['mpID'], 1);
				}
			}
			if ($data['quantity'] < 0) {
				$data['quantity'] = 0;
			}
		}
		if (!isset($data['price']) || ($data['price'] === null)) {
			$this->simplePrice->setPriceFromDB(
				$pID, $this->_magnasession['mpID']
			)->addTaxByPID($pID)->calculateCurr();
			if (getDBConfigValue($mp.'.price.addkind', $this->_magnasession['mpID']) == 'percent') {
				$this->simplePrice->addTax((float)getDBConfigValue($mp.'.price.factor', $this->_magnasession['mpID']));
			} else if (getDBConfigValue($mp.'.price.addkind', $this->_magnasession['mpID']) == 'addition') {
				$this->simplePrice->addLump((float)getDBConfigValue($mp.'.price.factor', $this->_magnasession['mpID']));
			}
			
			$data['price'] = $this->simplePrice->roundPrice()->makeSignalPrice(
								getDBConfigValue($this->_magnasession['currentPlatform'].'.price.signal', $this->_magnasession['mpID'], '')
							 )->getPrice();
		}
	}

	protected function getAdditionalHeadlines() {
		if ($this->inventoryPriceSync == 'auto') {
			return '
				<td title="'.ML_LABEL_BRUTTO.'">'.ML_MAGNACOMPAT_LABEL_MP_PRICE_SHORT.'&nbsp;<span class="small">'.$this->settings['currency'].'</span></td>
				<td>'.ML_LABEL_QUANTITY_AVAILABLE.'</td>
				<td>'.ML_LABEL_QUANTITY.'</td>
			';
		}

		return '<td title="'.ML_LABEL_BRUTTO.'">'.$this->provideResetFunction(
					ML_MAGNACOMPAT_LABEL_MP_PRICE_SHORT.' <span class="small">'.
						$this->settings['currency'].
				    '</span>',
				    'price',
				    'formatPriceWoCur(#VAL#, '.json_encode(array('2', '.', '')).')'
				).'</td>
				<td>'.ML_LABEL_QUANTITY_AVAILABLE.'</td>
				<td>'.$this->provideResetFunction(ML_LABEL_NEW_QUANTITY, 'quantity').'</td>';
	}

	protected function getAdditionalItemCells($key, $dbRow) {
		$this->extendProductAttributes($dbRow['products_id'], $this->selection[$dbRow['products_id']]);

		return '
				<td><table class="nostyle"><tbody>
						<tr><td>'.ML_LABEL_NEW.':&nbsp;</td><td>
						'. $this->simplePrice->setPrice($this->selection[$dbRow['products_id']]['price'])->getPrice()
						.'
							<input type="hidden" id="backup_price_'.$dbRow['products_id'].'"
						           value="'.$this->simplePrice->getPrice().'"/>
						</td></tr>
				    	<tr><td>'.ML_LABEL_OLD.':&nbsp;</td><td>&nbsp;'.(
							array_key_exists($dbRow['products_id'], $this->inventoryData) ?
								/* Waehrung von Preis nicht umrechnen, da bereits in Zielwaehrung. */
								$this->simplePrice->setPrice($this->inventoryData[$dbRow['products_id']]['Price'])->formatWOCurrency() :
								'&mdash;'
						).'</td></tr>
				    </tbody></table>
				</td>
				<td>'.(int)$dbRow['products_quantity'].'</td>
				
				<td><table class="nostyle"><tbody>
						<tr><td>'.ML_LABEL_NEW.':&nbsp;</td><td>
							<input type="hidden" id="old_quantity_'.$dbRow['products_id'].'"
						           value="'.$this->selection[$dbRow['products_id']]['quantity'].'"/>
						    '.$this->selection[$dbRow['products_id']]['quantity'].'
						</td></tr>
				    	<tr><td>'.ML_LABEL_OLD.':&nbsp;</td><td>&nbsp;'.(
							array_key_exists($dbRow['products_id'], $this->inventoryData) ?
								$this->inventoryData[$dbRow['products_id']]['Quantity'] :
								'&mdash;'
						).'</td></tr>
				    </tbody></table>
				</td>';
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
var formatOptions = <?php echo json_encode($formatOptions); ?>;

$(document).ready(function() {
	$('#summaryForm input[name^="quantity"]').each(function(i, e) {
		//myConsole.log($(e).attr('id'));
		$(e).blur(function() {
			val = jQuery.trim($(this).val());
			tfID = $(this).attr('id');
			if ((val == '') || !/^[0-9]*$/.test(val) || (val <= 0) || (val > 1000)) {
				alert(unescape(<?php echo "'".html2url(ML_ERROR_INVALID_NUMBER)."'"; ?>));
				val = $('#old_'+tfID).val();
				$(this).val(val);
			}
			jQuery.ajax({
				type: 'POST',
				url: '<?php echo toURL($this->url, array('kind' => 'ajax'), true); ?>',
				dataType: 'json',
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
			price = convertPriceToFloat(val, formatOptions);
			myConsole.log(price);
			if (price < 0) {
				alert(unescape(<?php echo "'".html2url(ML_ERROR_INVALID_NUMBER)."'"; ?>));
				$(e).val(formatPriceWoCur($('#backup_'+tfID).val(), formatOptions));
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
						$(e).val(formatPriceWoCur(data.value, formatOptions));
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
}
