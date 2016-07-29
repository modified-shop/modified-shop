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
 * $Id: RicardoSummaryView.php 1314 2011-10-20 16:44:16Z derpapst $
 *
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/checkin/MagnaCompatibleSummaryView.php');

class RicardoSummaryView extends MagnaCompatibleSummaryView {
	
	protected function getAdditionalItemCells($key, $dbRow) {
		$productTax = SimplePrice::getTaxByPID($dbRow['products_id']);
		$taxFromConfig = getDBConfigValue($this->_magnasession['currentPlatform'] . '.checkin.mwst', $this->_magnasession['mpID']);

		$this->simplePrice->setFinalPriceFromDB($dbRow['products_id'], $this->_magnasession['mpID']);
		if (isset($taxFromConfig) && $taxFromConfig !== '') {
			$this->simplePrice
				->removeTax($productTax)
				->addTax($taxFromConfig);
		}

		$ricardoPrice = $this->simplePrice
				->roundPrice()
				->getPrice();

		$this->extendProductAttributes($dbRow['products_id'], $this->selection[$dbRow['products_id']]);
		
		return '
				<td><table class="nostyle"><tbody>
						<tr><td>'.ML_LABEL_NEW.':&nbsp;</td><td>'.$ricardoPrice
						/* we don't use a price input field here anymore (unified policy for all MPs)
						   (and the function was buggy here)
						'.(($this->inventoryPriceSync == 'auto')
							? $ricardoPrice
							: '<input type="text" id="price_'.$dbRow['products_id'].'"
						           name="price['.$dbRow['products_id'].']"
						           value="'.$ricardoPrice.'"/>
							'
						).'*/
							.'<input type="hidden" id="backup_price_'.$dbRow['products_id'].'"
						           value="'.$ricardoPrice.'"/>
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
}
