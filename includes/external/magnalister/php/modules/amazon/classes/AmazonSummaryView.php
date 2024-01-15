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
 * $Id: AmazonSummaryView.php 6173 2015-10-29 11:59:53Z markus.bauer $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimpleSummaryView.php');
require_once (DIR_MAGNALISTER_MODULES.'amazon/amazonFunctions.php');

class AmazonSummaryView extends SimpleSummaryView {
	private $inventoryData = array();
	public function __construct($settings = array()) {
		global $_MagnaSession;
		$settings = array_merge(array(
			'selectionName' => 'checkin',
			'currency'      => getCurrencyFromMarketplace($_MagnaSession['mpID']),
		), $settings);
		
		parent::__construct($settings);
	}
	
	protected function updateLowestprice($pIds) {
		/* Update Toppreis */
		if (getDBConfigValue('general.keytype', '0') == 'artNr') {
			$query = '
				SELECT DISTINCT `asin`
				  FROM '.TABLE_PRODUCTS.' p, '.TABLE_MAGNA_AMAZON_PROPERTIES.' pa
				 WHERE p.products_id IN ("'.implode('", "', $pIds).'")
				       AND p.products_model=pa.products_model
				       AND p.products_model<>""
				       AND pa.mpID="'.$this->mpID.'"
				       AND asin<>""
			';
		} else {
			$query = '
				SELECT DISTINCT asin FROM '.TABLE_MAGNA_AMAZON_PROPERTIES.' 
				 WHERE products_id IN ('.implode(', ', $pIds).')
				       AND mpID="'.$this->mpID.'"
				       AND asin<>""
			';
		}

		$asins = MagnaDB::gi()->fetchArray($query, true);
		
		if (empty($asins)) {
			return;
		}
		
		//echo print_m($asins, '$asins');
		$request = array (
			'ACTION' => 'LowestPriceByASINs',
		);
		$asinsChunk = array_chunk($asins, 100);
		foreach ($asinsChunk as $asins) {
			$request['DATA'] = array();
			foreach ($asins as $asin) {
				$request['DATA'][]['ASIN'] = $asin;
			}
			try {
				$result = MagnaConnector::gi()->submitRequest($request);
				if (!empty($result['DATA'])) {
					foreach ($result['DATA'] as $item) {
						// Currency Conversion no yet supportet, fail silently
						if ($item['LowestPrice']['CurrencyCode'] != getCurrencyFromMarketplace($this->mpID)) continue;
						MagnaDB::gi()->update(TABLE_MAGNA_AMAZON_PROPERTIES,
							array (
								'lowestprice' => $item['LowestPrice']['Price']
							), array (
								'mpID' => $this->mpID,
								'asin' => $item['ASIN']
							)
						);
					}
				}
			} catch (MagnaException $e) {
				if ($e->getCode() == MagnaException::TIMEOUT) {
					$e->setCriticalStatus(false);
				}
			}
		}
	}
	
	protected function additionalInitialisation() {
		$pIDs = array();
		foreach ($this->selection as $pID => $item) {
			$pIDs[] = $pID;
		}
		//echo print_m($this->selection, '$this->selection');
		$this->updateLowestprice($pIDs);
		
		$request = array (
			'ACTION' => 'GetInventoryBySKUs',
			'DATA' => array(),
		);
		foreach ($pIDs as $pID) {
			$request['DATA'][]['SKU'] = magnaPID2SKU($pID);
		}

		try {
			$result = MagnaConnector::gi()->submitRequest($request);
			if (!empty($result['DATA'])) {
				foreach ($result['DATA'] as $item) {
					$this->inventoryData[magnaAmazonSKU2pID($item['SKU'])] = $item;
				}
			}
			unset($request);
			unset($result);
			
		} catch (MagnaException $e) {
			if ($e->getCode() == MagnaException::TIMEOUT) {
				$e->setCriticalStatus(false);
			}
		}

	}
	
	protected function setupQuery($addFields = '', $addFrom = '', $addWhere = '') {
		$w = (getDBConfigValue('general.keytype', '0') == 'artNr')
			? 'products_model=p.products_model'
			: 'products_id=p.products_id';
			
		$addFields .= (empty($addFields) ? '' : ',').' ap.lowestprice,
		              IF(ap.leadtimeToShip IS NULL, aa.leadtimeToShip, ap.leadtimeToShip) AS leadtimeToShip,
		              IF(ap.leadtimeToShip IS NULL, "apply", "match") AS prepareType
		              ';
		$addFrom   = 'LEFT JOIN '.TABLE_MAGNA_AMAZON_PROPERTIES.' ap ON (
		                    ap.mpID="'.$this->mpID.'" 
		                    AND ap.'.$w.'
		                    AND ap.asin<>""
		              )
		              LEFT JOIN '.TABLE_MAGNA_AMAZON_APPLY.' aa ON (
		                    aa.mpID="'.$this->mpID.'"
		                    AND aa.is_incomplete="false"
		                    AND aa.'.$w.'
		              )'.$addFrom;
		parent::setupQuery($addFields, $addFrom, $addWhere);
	}

	protected function processAdditionalPost() {
		if (isset($_GET['kind']) && ($_GET['kind'] == 'ajax')) {
			if (isset($_POST['reset']) && ($_POST['reset'] == 'leadtimeToShip') && isset($_POST['limit']) && is_array($_POST['limit'])) {
				$this->resetLeadtimeToShip();
				unset($_POST['reset']);
				unset($_POST['limit']);
			}
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
			/*
			if (isset($_POST['changeLeadtimeToShip'])) {
				$_POST['leadtimeToShip'][$pID] = $_POST['changeLeadtimeToShip'];
			}
			*/
		}
		#if (!isset($_GET['kind']) || ($_GET['kind'] != 'ajax')) echo print_m($_POST, '$_POST');
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

		if (getDBConfigValue('general.keytype', '0') == 'artNr') {
			$leadtimeTemplates = '
				UPDATE _#_TABLE_#_ AS a 
			INNER JOIN '.TABLE_PRODUCTS.' AS p ON (a.products_model = p.products_model)
			       SET a.leadtimeToShip = \'_#_VALUE_#_\'
			     WHERE p.products_id = \'_#_ID_#_\'
			           AND a.mpID="'.$this->mpID.'"
			';
		} else {
			$leadtimeTemplates = '
				UPDATE _#_TABLE_#_
				   SET leadtimeToShip = \'_#_VALUE_#_\'
				 WHERE products_id = \'_#_ID_#_\'
				       AND mpID="'.$this->mpID.'"
			';
		}
		
		if (array_key_exists('leadtimeToShip', $_POST)) {
			foreach ($_POST['leadtimeToShip'] as $pID => $value) {
				MagnaDB::gi()->query(eecho(str_replace(array (
						'_#_TABLE_#_',
						'_#_VALUE_#_',
						'_#_ID_#_'
					), array (
						($_POST['prepareType'][$pID] == 'apply')
							? TABLE_MAGNA_AMAZON_APPLY
							: TABLE_MAGNA_AMAZON_PROPERTIES,
						$value,
						$pID
					),
					$leadtimeTemplates
				), false));
			}
		}
	}

	protected function resetLeadtimeToShip() {
		// Get all settings and verify them.
		$defaultLeadtime = getDBConfigValue($this->marketplace.'.leadtimetoship', $this->mpID, '-');
		$leadtimeMatching = getDBConfigValue($this->marketplace.'.leadtimetoshipmatching.values', $this->mpID, array()); 
		$useMatching = getDBConfigValue(array($this->marketplace.'.leadtimetoshipmatching.prefer', 'val'), $this->mpID, false); 
		
		if (!is_array($leadtimeMatching) || empty($leadtimeMatching)) {
			$useMatching = false;
		}
		
		$tables = array (
			TABLE_MAGNA_AMAZON_APPLY,
			TABLE_MAGNA_AMAZON_PROPERTIES
		);
		
		if ($useMatching) {
			/* Check matching and fix it if necessary. */
			$matchedShippingIDs = array_keys($leadtimeMatching);
			$availableShippingIDs = MagnaDB::gi()->fetchArray('
				SELECT DISTINCT shipping_status_id
				  FROM '.TABLE_SHIPPING_STATUS.'
			', true);
			$removedIDs = array_diff($matchedShippingIDs, $availableShippingIDs);
			if (!empty($removedIDs)) {
				foreach ($removedIDs as $id) {
					unset($leadtimeMatching[$id]);
				}
			}
			$addedIDs = array_diff($availableShippingIDs, $matchedShippingIDs);
			if (!empty($addedIDs)) {
				foreach ($addedIDs as $id) {
					$leadtimeMatching[$id] = $defaultLeadtime;
				}
			}
			if (!empty($removedIDs) || !empty($addedIDs)) {
				/* Save the updated matching */
				setDBConfigValue($this->marketplace.'.leadtimetoshipmatching.values', $this->mpID, $leadtimeMatching, true);
			}

			if (getDBConfigValue('general.keytype', '0') == 'artNr') {
				$leadtimeTemplate = '
				    UPDATE _#_TABLE_#_ AS a 
				INNER JOIN '.TABLE_PRODUCTS.' AS p ON (a.products_model = p.products_model)
				       SET a.leadtimeToShip = \'_#_VALUE_#_\'
				     WHERE p.products_shippingtime = \'_#_TIME_#_\'
				           AND a.mpID="'.$this->mpID.'"
				';
			} else {
				$leadtimeTemplate = '
				    UPDATE _#_TABLE_#_ AS a 
				INNER JOIN '.TABLE_PRODUCTS.' AS p ON (a.products_id = p.products_id)
				       SET a.leadtimeToShip = \'_#_VALUE_#_\'
				     WHERE p.products_shippingtime = \'_#_TIME_#_\'
				           AND a.mpID="'.$this->mpID.'"
				';
			}

			foreach ($tables as $tbl) { 
				foreach ($availableShippingIDs as $id) {
					$leadtime = $leadtimeMatching[$id];
					MagnaDB::gi()->query(eecho(str_replace(array (
							'_#_TABLE_#_',
							'_#_VALUE_#_',
							'_#_TIME_#_'
						), array (
							$tbl,
							$leadtime,
							$id
						),
						$leadtimeTemplate
					), false));
				}
			}

		} else {
			foreach ($tables as $tbl) {
				MagnaDB::gi()->update($tbl, array (
					'leadtimeToShip' => $defaultLeadtime
				), array(
					'mpID' => $this->mpID
				));
			}
		}
		
		$this->ajaxReply['changedData'] = array();
		
		$this->loadSelection();
		
		$w = (getDBConfigValue('general.keytype', '0') == 'artNr')
			? 'products_model=p.products_model'
			: 'products_id=p.products_id';
		$changedData = MagnaDB::gi()->fetchArray(eecho('
		    SELECT p.products_id AS pID,
		           IF(ap.leadtimeToShip IS NULL, aa.leadtimeToShip, ap.leadtimeToShip) AS leadtimeToShip
		      FROM '.TABLE_PRODUCTS.' p
		 LEFT JOIN '.TABLE_MAGNA_AMAZON_PROPERTIES.' ap ON (
		           ap.mpID=\''.$this->mpID.'\' 
		           AND ap.'.$w.'
			)
		 LEFT JOIN '.TABLE_MAGNA_AMAZON_APPLY.' aa ON (
		           aa.mpID=\''.$this->mpID.'\' 
		           AND aa.'.$w.'
		    )
		     WHERE p.products_id IN (\''.implode('\', \'', array_keys($this->selection)).'\')
		', false));
		if (!empty($changedData)) {
			foreach ($changedData as $row) {
				$this->ajaxReply['changedData'][$row['pID']]['leadtimeToShip'] = $row['leadtimeToShip'];
			}
		}
		
		$this->ajaxReply['proceed'] = false;
		$this->ajaxReply['error'] = false;
		$this->ajaxReply['limit'] = array(0, 0);
	}

	protected function extendProductAttributes($pID, &$data) {
		$mp = $this->_magnasession['currentPlatform'];
		if (!isset($data['quantity']) || ($data['quantity'] === null)) {
			$quantityType = getDBConfigValue($mp.'.quantity.type', $this->mpID);
			switch ($quantityType) {
				case 'stock': {
					$data['quantity'] = (int)MagnaDB::gi()->fetchOne('SELECT products_quantity FROM '.TABLE_PRODUCTS.' WHERE products_id=\''.$pID.'\'');
					break;
				}
				case 'stocksub': {
					$data['quantity'] = (int)MagnaDB::gi()->fetchOne(
											'SELECT products_quantity FROM '.TABLE_PRODUCTS.' WHERE products_id=\''.$pID.'\''
										) - getDBConfigValue($mp.'.quantity.value', $this->mpID, 0);
					break;
				}
				default: {
					$data['quantity'] = getDBConfigValue($mp.'.quantity.value', $this->mpID, 1);
				}
			}
			if (
				(getDBConfigValue($mp.'.quantity.maxquantity', $this->mpID) > 0) 
				&& (getDBConfigValue($mp.'.quantity.type', $this->mpID) != 'lump')
			) {
				$data['quantity'] = min($data['quantity'], getDBConfigValue($mp.'.quantity.maxquantity', $this->mpID));
			}
			if ($data['quantity'] < 0) {
				$data['quantity'] = 0;
			}
		}
		if (!isset($data['price']) || ($data['price'] === null)) {
			$this->simplePrice->setPriceFromDB(
				$pID, $this->mpID
			)->addTaxByPID($pID)->calculateCurr();
			if (getDBConfigValue($mp.'.price.addkind', $this->mpID) == 'percent') {
				$this->simplePrice->addTax((float)getDBConfigValue($mp.'.price.factor', $this->mpID));
			} else if (getDBConfigValue($mp.'.price.addkind', $this->mpID) == 'addition') {
				$this->simplePrice->addLump((float)getDBConfigValue($mp.'.price.factor', $this->mpID));
			}
			
			$data['price'] = $this->simplePrice->roundPrice()->makeSignalPrice(
								getDBConfigValue($this->_magnasession['currentPlatform'].'.price.signal', $this->mpID, '')
							 )->getPrice();
		}
	}

	protected function getAdditionalHeadlines() {
		return '<td>'.ML_GENERIC_LOWEST_PRICE.'</td>
				<td title="'.ML_LABEL_BRUTTO.'">'.$this->provideResetFunction(
					ML_AMAZON_LABEL_AMAZON_PRICE_SHORT.' <span class="small">'.
						$this->settings['currency'].
					'</span>',
					'price',
					'formatPriceWoCur(#VAL#, '.json_encode(array('2', '.', '')).')'
				).'</td>
				<td>'.ML_LABEL_QUANTITY_AVAILABLE.'</td>
				<td>'.$this->provideResetFunction(ML_LABEL_NEW_QUANTITY, 'quantity').'</td>
				<td>'.$this->provideResetFunction(ML_GENERIC_SHIPPING_TIME, 'leadtimeToShip').'</td>';
	}

	protected function getAdditionalItemCells($key, $dbRow) {
		$this->extendProductAttributes($dbRow['products_id'], $this->selection[$dbRow['products_id']]);
		if ((float)$dbRow['lowestprice'] > 0) {
			$lowPrice = str_replace(' ', '&nbsp;', $this->simplePrice->setPrice($dbRow['lowestprice'])->format());
		} else {
			$lowPrice = '&mdash;';
		}
		$html = '
				<td>'.$lowPrice.'<br />&nbsp;</td>
				<td><table class="nostyle"><tbody>
						<tr><td>'.ML_LABEL_NEW.':&nbsp;</td><td>'.
			$this->simplePrice->setPrice($this->selection[$dbRow['products_id']]['price'])->getPrice().'
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
							       value="'.$this->selection[$dbRow['products_id']]['quantity'].'"/>'
			.$this->selection[$dbRow['products_id']]['quantity'].'
						</td></tr>
						<tr><td>'.ML_LABEL_OLD.':&nbsp;</td><td>&nbsp;'.(
							array_key_exists($dbRow['products_id'], $this->inventoryData) ?
								$this->inventoryData[$dbRow['products_id']]['Quantity'] :
								'&mdash;'
						).'</td></tr>
					</tbody></table>
				</td>';
				
					if (getDBConfigValue(array('amazon.leadtimetoshipmatching.prefer', 'val'), $this->mpID, false)) {
						$products_shippingtime = MagnaDB::gi()->fetchOne('
						    SELECT products_shippingtime
						        FROM '. TABLE_PRODUCTS .' p
						        WHERE p.products_id = \''. $dbRow['products_id'] .'\'
						');
						$leadtime = getDBConfigValue(
							array('amazon.leadtimetoshipmatching.values', $products_shippingtime),
							$this->mpID,
							getDBConfigValue('amazon.leadtimetoship', $this->mpID, '-')
						);
					} else {
						$leadtime = $dbRow['leadtimeToShip'];
					}
					$html .= '
				<td>
                                        '.(int)$leadtime.'
					<input type="hidden" id="leadtimeToShip_'.$dbRow['products_id'].'"
					       name="leadtimeToShip_['.$dbRow['products_id'].']" value="'.$leadtime.'"/>
					<input type="hidden" id="prepareType_'.$dbRow['products_id'].'"
					       name="prepareType['.$dbRow['products_id'].']" value="'.$dbRow['prepareType'].'"/>
				</td>';
		return $html;
	}
	
	public function renderSelection() {
		$topHTML = '';
		/* Currency-Check */
		if ($this->settings['currency'] != DEFAULT_CURRENCY) {
			$topHTML .= '<p class="noticeBox"><b class="notice">'.ML_LABEL_ATTENTION.':</b> '.sprintf(
				ML_AMAZON_ERROR_WRONG_CURRENCY,
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
			if ((val == '') || !/^[0-9]*$/.test(val) || (val < 0) || (val > 1000)) {
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
	/*
	@todo: submit prepare type
	$('#summaryForm select[name^="leadtimeToShip"]').each(function(i, e) {
		//myConsole.log($(e).attr('id'));
		$(e).change(function() {
			jQuery.ajax({
				type: 'POST',
				url: '<?php echo toURL($this->url, array('kind' => 'ajax'), true); ?>',
				dataType: 'json',
				data: {
					'changeLeadtimeToShip': $(this).val(),
					'productID': $(this).attr('id')
				},
				dataType: 'json'
			});
		});
	});
	*/
});
/*]]>*/</script>
<?php
		$html = ob_get_contents();
		ob_end_clean();
		return $topHTML.parent::renderSelection().$html;
	}
}
