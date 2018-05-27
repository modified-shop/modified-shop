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

abstract class MLProductListCdiscountAbstract extends MLProductList {
	protected $aPrepareData = array();
	
	protected function getPreparedStatusIndicator($aRow){
		$sVerified = $this->getPrepareData($aRow, 'Verified');
		if (empty($sVerified)) {
			return html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/grey_dot.png', ML_HOOD_PRODUCT_MATCHED_NO, 9, 9);
		} elseif ('OK' == $sVerified) {
			return html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/green_dot.png', ML_HOOD_PRODUCT_PREPARED_OK, 9, 9);
		} elseif ('EMPTY' == $sVerified) {
			return html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/white_dot.png', ML_EBAY_PRODUCT_PREPARED_FAULTY_BUT_MP, 9, 9);
		} else {
			return html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/red_dot.png', ML_HOOD_PRODUCT_PREPARED_FAULTY, 9, 9);
		}
	}
	
	protected function getPrepareData($aRow, $sFieldName = null) {
		if (!isset($this->aPrepareData[$aRow['products_id']])) {
			$aApplyData = MagnaDB::gi()->fetchRow("
				SELECT * 
				FROM ".TABLE_MAGNA_CDISCOUNT_PREPARE."
				WHERE ".(
						(getDBConfigValue('general.keytype', '0') == 'artNr')
							? 'products_model=\''.MagnaDB::gi()->escape($aRow['products_model']).'\''
							: 'products_id=\''.$aRow['products_id'].'\''
				)."
					AND mpID = '".$this->aMagnaSession['mpID']."'
					AND PrepareType='Apply'
			");
			$aMatchData =  MagnaDB::gi()->fetchRow("
				SELECT * 
				FROM ".TABLE_MAGNA_CDISCOUNT_PREPARE."
				WHERE ".(
						(getDBConfigValue('general.keytype', '0') == 'artNr')
							? 'products_model=\''.MagnaDB::gi()->escape($aRow['products_model']).'\''
							: 'products_id=\''.$aRow['products_id'].'\''
				)."
					AND mpID = '".$this->aMagnaSession['mpID']."'
					AND PrepareType='Match'
			");
			
			if (empty($aApplyData) && empty($aMatchData)) {
				$this->aPrepareData[$aRow['products_id']] = array();
			} elseif (empty($aMatchData)) {
				$aApplyData['preparetype'] = 'applied';
				$this->aPrepareData[$aRow['products_id']] = $aApplyData;
			} elseif (empty($aApplyData)) {
				$aMatchData['preparetype'] = 'matched';
				$this->aPrepareData[$aRow['products_id']] = $aMatchData;
			} else { //both - not good
				$aApplyData['preparetype'] = array('matched', 'applied');
				$aData = $aApplyData;
				foreach ($aMatchData as $sKey => $mValue) {
					$aData[$sKey] = $mValue;
				}
				$this->aPrepareData[$aRow['products_id']] = $aData;
			}
		}
		
		if($sFieldName === null){
			return $this->aPrepareData[$aRow['products_id']];
		}else{
			return isset($this->aPrepareData[$aRow['products_id']][$sFieldName]) ? $this->aPrepareData[$aRow['products_id']][$sFieldName] : null;
		}
	}
	
	protected function getMarketPlaceCategory($aRow) {
		$aData = $this->getPrepareData($aRow);
		if ($aData !== false) {
			$matchMPShopCats = !getDBConfigValue(array(
				$this->aMagnaSession['currentPlatform'].'.catmatch.mpshopcats', 'val'
			), $this->aMagnaSession['mpID'], false);
			
			return '
			<table class="nostyle"><tbody>
				<tr>
					<td>MP:</td>
					<td>'.(empty($aData['MarketplaceCategories']) ? '&mdash;' : $aData['MarketplaceCategories']).(empty($aData['MarketplaceCategoriesName']) ? '' : ' '.$aData['MarketplaceCategoriesName']).'</td>
				</tr>
				'.($matchMPShopCats
					? ('<tr><td>Store:</td><td>'.(
						empty($aData['store_category_id']) 
							? '&mdash;' 
							: $aData['store_category_id']
						).'</td></tr>') 
					: ''
				).'
			</tbody></table>';
		}
		return '&mdash;';
	}
	
	protected function getSelectionName() {
		return 'prepare';
	}


	protected function isPreparedDifferently($aRow) {
		$sPrimaryCategory = $this->getPrepareData($aRow, 'PrimaryCategory');
		if (!empty($sPrimaryCategory)) {
			$sCategoryDetails = $this->getPrepareData($aRow, 'CategoryAttributes');
			$categoryMatching = CdiscountHelper::gi()->getCategoryMatching($sPrimaryCategory);
			$sCategoryDetails = json_decode($sCategoryDetails, true);
			return CdiscountHelper::gi()->detectChanges($categoryMatching, $sCategoryDetails);
		}

		return false;
	}

	protected function isDeletedAttributeFromShop($aRow, &$message) {
	    $aPrimaryCategory = $this->getPrepareData($aRow, 'PrimaryCategory');
		if (!empty($aPrimaryCategory)) {
			$matchedAttributes = $this->getPrepareData($aRow, 'CategoryAttributes');
			$matchedAttributes = json_decode($matchedAttributes, true);
			$shopAttributes = CdiscountHelper::gi()->flatShopVariations();

            if (!is_array($matchedAttributes)) {
                $matchedAttributes = array();
            }
			
			foreach ($matchedAttributes as $matchedAttribute) {
				if (CdiscountHelper::gi()->detectIfAttributeIsDeletedOnShop($shopAttributes, $matchedAttribute, $message)) {
					return true;
				}
			}
		}
		
		return false;
	}

}
