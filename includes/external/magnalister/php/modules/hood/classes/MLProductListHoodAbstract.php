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

abstract class MLProductListHoodAbstract extends MLProductList {
	protected $aPrepareData = array();
	
	protected function getPrepareData($aRow, $sFieldName = null) {
		if (!isset($this->aPrepareData[$aRow['products_id']])) {
			$this->aPrepareData[$aRow['products_id']] = MagnaDB::gi()->fetchRow("
				SELECT * 
				FROM ".TABLE_MAGNA_HOOD_PROPERTIES." 
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
	
	protected function getHoodPrice ($aRow) {
		$sListingType = $this->getPrepareData($aRow , 'ListingType');
		if (!empty($sListingType)) {
			$aPriceConfig = HoodHelper::loadPriceSettings($this->aMagnaSession['mpID']);
			$sListingType = $sListingType == 'classic' ? 'Auction' : 'Fixed';
			$aPrices = array();
			if ($sListingType == 'Auction'){
				$fStartPrice = (float) $this->getPrepareData($aRow, 'StartPrice');
				if ($fStartPrice > 0) {
					$price = $this->getPrice()->setPrice($fStartPrice)->format();
					$aPrices[] = ML_HOOD_LABEL_STARTPRICE.': '.$price;
				}
				if ($aPriceConfig['Auction']['BuyItNow']['UseBuyItNow']) {
					$price = $this->getPrice()
						->setFinalPriceFromDB($aRow['products_id'], $this->aMagnaSession['mpID'], $aPriceConfig['Auction']['BuyItNow'])
						->format()
					;
					$aPrices[] = ML_HOOD_BUYITNOW.': '.$price;
				}
			} else {
				
			$price = $this->getPrice()
				->setFinalPriceFromDB($aRow['products_id'], $this->aMagnaSession['mpID'], $aPriceConfig['Fixed'])
				->format();
				$aPrices[] = ML_HOOD_BUYITNOW.': '.$price;
			}
		}
		if (empty($aPrices)) {
			$aPrices[] = '&mdash;';
		}
		return $aPrices;
	}
}