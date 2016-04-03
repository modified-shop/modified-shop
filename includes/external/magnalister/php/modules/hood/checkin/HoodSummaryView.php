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
 * $Id: hoodSummaryView.php 733 2011-01-21 07:42:58Z derpapst $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimpleSummaryView.php');

class HoodSummaryView extends SimpleSummaryView {
	protected $priceConfig = array();
	protected $quantityConfig = array();
	
	protected $inventoryData = array();
	
	public function __construct($settings = array()) {
		global $_MagnaSession;
		$settings = array_merge(array(
			'selectionName' => 'checkin',
			'currency'      => getCurrencyFromMarketplace($_MagnaSession['mpID']),
		), $settings);
		
		parent::__construct($settings);
		
		$this->priceConfig = HoodHelper::loadPriceSettings($this->_magnasession['mpID']);
		$this->quantityConfig = HoodHelper::loadQuantitySettings($this->_magnasession['mpID']);
	}
	
	protected function additionalInitialisation() {
		return;
		
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
		echo print_m($this->inventoryData);
	}
	
	protected function processAdditionalPost() {
		parent::processAdditionalPost();
	}
	
	protected function getAdditionalHeadlines() {
		$ret = parent::getAdditionalHeadlines();
		return '
			<td title="'.ML_LABEL_BRUTTO.'">'.ML_HOOD_LABEL_HOOD_PRICE.'</td>
			'.$ret.'
			<td>'.ML_HOOD_LISTING_TYPE.'</td>
			<td>'.ML_LABEL_QUANTITY.'</td>
		';
	}
	
	protected function extendProductAttributes($pID, &$data) {
		
	}
	
	protected function getAdditionalItemCells($key, $dbRow) {
		// echo print_m(func_get_args(), __METHOD__);
		
		$matchRow = MagnaDB::gi()->fetchRow('
			SELECT StartPrice, ListingType
			  FROM '.TABLE_MAGNA_HOOD_PROPERTIES.' 
			 WHERE '.((getDBConfigValue('general.keytype', '0') == 'artNr')
						? 'products_model="'.MagnaDB::gi()->escape($dbRow['products_model']).'"'
						: 'products_id="'.$dbRow['products_id'].'"'
					).'
			       AND mpID="'.$this->_magnasession['mpID'].'"
		');
		$pID = $dbRow['products_id'];
		
		$listingDefine = 'ML_HOOD_LISTINGTYPE_'.strtoupper($matchRow['ListingType']);
		$textListingType = (defined($listingDefine) ? constant($listingDefine) : $matchRow['ListingType']);
		
		if ($matchRow['ListingType'] == 'shopProduct') {
			$textListingDuration = ML_LABEL_UNLIMITED;
		}
		
		$listingType = ($matchRow['ListingType'] == 'classic') ? 'Auction' : 'Fixed';
		
		$textHoodPrice = array();
		if ($listingType == 'Auction') {
			if ((float)$matchRow['StartPrice'] > 0) {
				$price = $this->simplePrice->setPrice($matchRow['StartPrice'])->format();
				$textHoodPrice[] = ML_HOOD_LABEL_STARTPRICE.': '.$price;
			}
			if ($this->priceConfig['Auction']['BuyItNow']['UseBuyItNow']) {
				$price = $this->simplePrice
					->setFinalPriceFromDB($pID, $this->mpID, $this->priceConfig['Auction']['BuyItNow'])
					->format();
				$textHoodPrice[] = ML_HOOD_BUYITNOW.': '.$price;
			}
		} else if ($listingType == 'Fixed') {
			$price = $this->simplePrice
				->setFinalPriceFromDB($pID, $this->mpID, $this->priceConfig['Fixed'])
				->format();
			$textHoodPrice[] = ML_HOOD_BUYITNOW.': '.$price;
		}
		
		if (empty($textHoodPrice)) {
			$textHoodPrice = '&mdash;';
		} else {
			$textHoodPrice = implode('<br>', $textHoodPrice);
		}
		
		$quantity = HoodHelper::calcQuantity($dbRow['products_quantity'], $this->quantityConfig[$listingType]);
		
		return '
			<td title="'.ML_HOOD_PRICE_CALCULATED_TOOLTIP.'">'.$textHoodPrice.'</td>
			'.parent::getAdditionalItemCells($key, $dbRow).'
			<td>'.$textListingType.'</td>
			<td>'.$quantity.'</td>';
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
		
		return $topHTML.parent::renderSelection();
	}
	
	protected function getTopInfoBox() { 
		return '';
	}
}
