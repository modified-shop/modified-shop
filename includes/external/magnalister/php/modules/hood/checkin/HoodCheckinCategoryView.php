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
 * $Id$
 *
 * (c) 2010 - 2013 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimpleCheckinCategoryView.php');

class HoodCheckinCategoryView extends SimpleCheckinCategoryView {
	protected $priceConfig = array();
	
	public function __construct($cPath = 0, $settings = array(), $sorting = false, $search = '') {
		global $_MagnaSession;
		$settings = array_merge(array(
			'selectionName'   => 'checkin',
			'selectionValues' => array (
				'quantity' => null
			)
		), $settings);
		
		parent::__construct($cPath, $settings, $sorting, $search);
		
		if (!isset($_GET['kind']) || ($_GET['kind'] != 'ajax')) {
			$this->simplePrice->setCurrency(getCurrencyFromMarketplace($this->_magnasession['mpID']));
		}
		
		$this->priceConfig = HoodHelper::loadPriceSettings($this->_magnasession['mpID']);
	}
	
	protected function init() {
		parent::init();
		
		$this->productIdFilterRegister('PreparedHoodFilter', array());
		$this->productIdFilterRegister('DeletedInHoodFilter', array());
	}
	
	public function getAdditionalHeadlines() {
		return '
			<td class="lowestprice">'.ML_HOOD_LABEL_HOOD_PRICE.'</td>
			<td class="lowestprice">'.ML_HOOD_LISTING_TYPE.'</td>
			<td class="lowestprice">'.ML_HOOD_DURATION.'</td>';
	}

	public function getAdditionalCategoryInfo($cID, $data = false) {
		return '
			<td>&mdash;</td>
			<td>&mdash;</td>
			<td>&mdash;</td>';
	}

	public function getAdditionalProductInfo($pID, $data = false) {
		$matchRow = MagnaDB::gi()->fetchRow('
			SELECT StartPrice, ListingType, ListingDuration
			  FROM '.TABLE_MAGNA_HOOD_PROPERTIES.' 
			 WHERE '.((getDBConfigValue('general.keytype', '0') == 'artNr')
						? 'products_model="'.MagnaDB::gi()->escape($data['products_model']).'"'
						: 'products_id="'.$pID.'"'
					).'
			       AND mpID="'.$this->_magnasession['mpID'].'"
		');
		
		$listingDefine = 'ML_HOOD_LISTINGTYPE_'.strtoupper($matchRow['ListingType']);
		$textListingType = (defined($listingDefine) ? constant($listingDefine) : $matchRow['ListingType']);
		$durationDefine = 'ML_HOOD_LABEL_LISTINGDURATION_'.strtoupper($matchRow['ListingDuration']);
		$textListingDuration = (defined($durationDefine) ? constant($durationDefine) : $matchRow['ListingDuration']);
		
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
				->setFinalPriceFromDB($pID,$this->mpID, $this->priceConfig['Fixed'])
				->format();
			$textHoodPrice[] = ML_HOOD_BUYITNOW.': '.$price;
		}
		
		if (empty($textHoodPrice)) {
			$textHoodPrice = '&mdash;';
		} else {
			$textHoodPrice = implode('<br>', $textHoodPrice);
		}
		
		return '
			<td title="'.ML_HOOD_PRICE_CALCULATED_TOOLTIP.'">'.$textHoodPrice.'</td>
			<td>'.$textListingType.'</td>
			<td>'.$textListingDuration.'</td>';
	}
	
	protected function getEmptyInfoText() {
		if (empty($this->search)) {
			return ML_HOOD_TEXT_NO_MATCHED_PRODUCTS;
		} else {
			return parent::getEmptyInfoText();
		}
	}

	
}
