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
 * $Id: CheckinCategoryView.php 749 2011-02-01 01:27:02Z derpapst $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimpleCheckinCategoryView.php');

class eBayCheckinCategoryView extends SimpleCheckinCategoryView {
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
	}
	
	protected function init() {
		parent::init();
		
		$this->productIdFilterRegister('PreparedEbayFilter', array());
		$this->productIdFilterRegister('DeletedInEbayFilter', array());
	}
	
	public function getAdditionalHeadlines() {
		return '
			<td class="lowestprice">'.ML_EBAY_LABEL_EBAY_PRICE.'</td>
			<td class="lowestprice">'.ML_EBAY_LISTING_TYPE.'</td>
			<td class="lowestprice">'.ML_EBAY_DURATION.'</td>';
	}

	public function getAdditionalCategoryInfo($cID, $data = false) {
		return '
			<td>&mdash;</td>
			<td>&mdash;</td>
			<td>&mdash;</td>';
	}

	public function getAdditionalProductInfo($pID, $data = false) {
		$priceFrozen = false;
		if (getDBConfigValue('general.keytype', '0') == 'artNr') {
			$matchRow = MagnaDB::gi()->fetchRow('
				SELECT Price, BuyItNowPrice, ListingType, ListingDuration
				  FROM '.TABLE_MAGNA_EBAY_PROPERTIES.' 
				 WHERE products_model=\''.MagnaDB::gi()->escape($data['products_model']).'\' AND
				       mpID=\''.$this->_magnasession['mpID'].'\'
			');
		} else {
			$matchRow = MagnaDB::gi()->fetchRow('
				SELECT Price, BuyItNowPrice, ListingType, ListingDuration
				  FROM '.TABLE_MAGNA_EBAY_PROPERTIES.'
				 WHERE products_id=\''.$pID.'\' AND
				       mpID=\''.$this->_magnasession['mpID'].'\'
			');
		}
		$listingDefine = 'ML_EBAY_LISTINGTYPE_'.strtoupper($matchRow['ListingType']);
		$textListingType = (defined($listingDefine) ? constant($listingDefine) : $matchRow['ListingType']);
		$durationDefine = 'ML_EBAY_LABEL_LISTINGDURATION_'.strtoupper($matchRow['ListingDuration']);
		$textListingDuration = (defined($durationDefine) ? constant($durationDefine) : $matchRow['ListingDuration']);
		if (0.0 == $matchRow['Price']) { # Preis nicht eingefroren => berechnen
			$matchRow['Price'] = makePrice($pID,  $matchRow['ListingType']);
		} else {
			$priceFrozen = true;
		}
		$textEBayPrice = $this->simplePrice->setPrice($matchRow['Price'])->format();
		if (0 != $matchRow['BuyItNowPrice']) {
			$textEBayPrice .= '<br>'.ML_EBAY_BUYITNOW.': '.$this->simplePrice->setPrice($matchRow['BuyItNowPrice'])->format();
		}
		if ($priceFrozen) {
			$startPriceFormat = '<b>';
			$endPriceFormat = '</b>';
			$priceTooltip = ' title="'.ML_EBAY_PRICE_FROZEN_TOOLTIP.'" ';
		} else {
			$startPriceFormat = $endPriceFormat = '';
			$priceTooltip = ' title="'.ML_EBAY_PRICE_CALCULATED_TOOLTIP.'" ';
		}
		return '
			<td '.$priceTooltip.'>'.$startPriceFormat.$textEBayPrice.$endPriceFormat.'</td>
			<td>'.$textListingType.'</td>
			<td>'.$textListingDuration.'</td>';
	}
	
	protected function getEmptyInfoText() {
		if (empty($this->search)) {
			return ML_EBAY_TEXT_NO_MATCHED_PRODUCTS;
		} else {
			return parent::getEmptyInfoText();
		}
	}
	
}
