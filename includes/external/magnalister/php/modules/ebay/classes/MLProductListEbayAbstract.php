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
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/MLProductList.php');

abstract class MLProductListEbayAbstract extends MLProductList {

	protected $aPrepareData = array();

	protected function getPrepareData($aRow, $sFieldName = null) {
		if (!isset($this->aPrepareData[$aRow['products_id']])) {
			$this->aPrepareData[$aRow['products_id']] = MagnaDB::gi()->fetchRow("
				SELECT *
				FROM ".TABLE_MAGNA_EBAY_PROPERTIES."
				WHERE
					".(
						(getDBConfigValue('general.keytype', '0') == 'artNr')
							? 'products_model=\''.MagnaDB::gi()->escape($aRow['products_model']).'\''
							: 'products_id=\''.$aRow['products_id'].'\''
					)."
					AND mpID = '".$this->aMagnaSession['mpID']."'
			");
		}
		if($sFieldName === null){
			return $this->aPrepareData[$aRow['products_id']];
		}else{
			return isset($this->aPrepareData[$aRow['products_id']][$sFieldName]) ? $this->aPrepareData[$aRow['products_id']][$sFieldName] : null;
		}
	}

	protected function getEbayPrice($aRow) {
		$fPrice = $this->getPrepareData($aRow, 'Price');
		if ($fPrice === null) {
			return array(
				'tooltip' => '',
				'frozen' => false,
				'value' => '&mdash;'
			);
		} else {
			$sListingsType = $this->getPrepareData($aRow, 'ListingType');
			if (0.0 == $fPrice || $sListingsType != 'Chinese') { # Preis nicht eingefroren => berechnen
				$fPrice = makePrice($aRow['products_id'], $sListingsType);
				$priceFrozen = false;
			} else {
				$priceFrozen = true;
			}
			$textEBayPrice = $this->getPrice()->setPrice($fPrice)->format();
			if (0 != $this->getPrepareData($aRow,'BuyItNowPrice')) {
				$textEBayPrice .= '<br>'.ML_EBAY_BUYITNOW.': '.$this->getPrice()->setPrice($this->getPrepareData($aRow,'BuyItNowPrice'))->format();
			}
			if ($priceFrozen) {
				$priceTooltip = ML_EBAY_PRICE_FROZEN_TOOLTIP;
			} else {
				$priceTooltip = ML_EBAY_PRICE_CALCULATED_TOOLTIP;
			}
			return array(
				'tooltip' => $priceTooltip,
				'frozen' => $priceFrozen,
				'value' => $textEBayPrice
			);
		}
	}

	protected function isPreparedDifferently($aRow) {
		$sPrimaryCategory = $this->getPrepareData($aRow, 'PrimaryCategory');
		if (!empty($sPrimaryCategory)) {
			$_POST['SecondaryCategory'] = $this->getPrepareData($aRow, 'SecondaryCategory');
			$sItemSpecifics = EbayHelper::gi()->getPreparedData($sPrimaryCategory, 
			((getDBConfigValue('general.keytype', '0') == 'artNr')
			  ? $aRow['products_model']
			  : $aRow['products_id']));
			$categoryMatching = EbayHelper::gi()->getCategoryMatching($sPrimaryCategory);
			return EbayHelper::gi()->detectChanges($categoryMatching, $sItemSpecifics);
		}

		return false;
	}

	protected function isDeletedAttributeFromShop($aRow, &$message) {
		$sPrimaryCategory = $this->getPrepareData($aRow, 'PrimaryCategory');
		if (!empty($sPrimaryCategory)) {
			$_POST['SecondaryCategory'] = $this->getPrepareData($aRow, 'SecondaryCategory');
			$matchedAttributes = EbayHelper::gi()->getPreparedData($sPrimaryCategory,
			((getDBConfigValue('general.keytype', '0') == 'artNr')
			  ? $aRow['products_model']
			  : $aRow['products_id'])) ?: array();
			$matchedAttributes = is_array($matchedAttributes) ? $matchedAttributes : array();
			$shopAttributes = EbayHelper::gi()->flatShopVariations();

			foreach ($matchedAttributes as $matchedAttribute) {
				if (EbayHelper::gi()->detectIfAttributeIsDeletedOnShop($shopAttributes, $matchedAttribute, $message)) {
					return true;
				}
			}
		}

		return false;
	}
}
