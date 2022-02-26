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
/*
		kann man noch einbauen
		if (getDBConfigValue('general.keytype', '0') == 'artNr') {
			$this->aPrepareData[$aRow['products_id']]['variationsEpids'] =  MagnaDB::gi()->fetchArray("
				SELECT marketplace_sku AS VariationSku, ePID
				FROM magnalister_ebay_variations_epids
				WHERE products_sku = '".MagnaDB::gi()->escape($aRow['products_model'])."'
				AND mpID = '".$this->aMagnaSession['mpID']."'
			");
		} else {
			$this->aPrepareData[$aRow['products_id']]['variationsEpids'] =  MagnaDB::gi()->fetchArray("
				SELECT marketplace_sku AS VariationSku, ePID
				FROM magnalister_ebay_variations_epids
				WHERE products_id = '".$aRow['products_id']."'
				AND mpID = '".$this->aMagnaSession['mpID']."'
			");
		}
*/
		if($sFieldName === null) {
			return $this->aPrepareData[$aRow['products_id']];
		} else {
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

			$jStrikePriceConf = $this->getPrepareData($aRow, 'StrikePriceConf');
			if (!empty($jStrikePriceConf)) {
				$aStrikePriceConf = json_decode($jStrikePriceConf, true);
			} else {
				$aStrikePriceConf = array();
			}
			if (    !empty($aStrikePriceConf)
			     && $aStrikePriceConf['ebay.strike.price.kind'] != 'DontUse') {
				$fStrikePrice = makePrice($aRow['products_id'], 'StrikePrice');
				if ($fStrikePrice > $fPrice) {
					$textEBayPrice .= '<br /><span style="text-decoration:line-through; color:red">'.$this->getPrice()->setPrice($fStrikePrice)->format().'</span';
				}
			}
			return array(
				'tooltip' => $priceTooltip,
				'frozen' => $priceFrozen,
				'value' => $textEBayPrice
			);
		}
	}

	protected function getPreparedStatusIndicator($aRow) {
	}

	/**
	 * @deprecated
	 * Functionality for eBay Product Based Shopping Experience,
	 * no longer in use
	 */
	protected function getPrepareType($aRow) {
		$sEPID = $this->getPrepareData($aRow, 'ePID');
		$sProductRequired = $this->getPrepareData($aRow, 'productRequired');
		$scurrencyID = $this->getPrepareData($aRow, 'currencyID');
		#$aVarEpids = $this->getPrepareData($aRow, 'variationsEpids');
		if (    strlen($sEPID) > 0
		     || $sProductRequired === 'true') {
			if ('newproduct' == $sEPID) {
				return ML_EBAY_LABEL_APPLIED_CATALOG;
			} else if ('variations' == $sEPID) {
				return ML_EBAY_LABEL_CATALOG_VARIATIONS;
			} else {
				return ML_EBAY_LABEL_PREPARED_CATALOG;
			       #.'<br />'.$sEPID;
			}
		} else if (!empty($scurrencyID)) {
			return ML_EBAY_LABEL_PREPARED_NO_CATALOG;
		} else {
			return '&mdash;';
		}
	}

}
