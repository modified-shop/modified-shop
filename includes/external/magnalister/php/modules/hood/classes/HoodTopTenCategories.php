<?php
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/TopTen.php');
require_once(DIR_MAGNALISTER_MODULES.'hood/prepare/HoodCategoryMatching.php');
require_once(DIR_MAGNALISTER_MODULES.'hood/HoodHelper.php');

class HoodTopTenCategories extends TopTen {
	/**
	 *
	 * @param string $sType  topPrimaryCategory || topSecondaryCategory || topStoreCategory || topStoreCategory2
	 * @return array (key=>value)
	 * @throws Exception 
	 */
	public function getTopTenCategories($sType, $aConfig = array()){
		$isStore = stripos($sType, 'store') !== false;
		if ($isStore) {
			$store = HoodApiConfigValues::gi()->getHasStore();
			if (!HoodHelper::hasStore()) {
				throw new Exception('noStore');
			}
			$sField = $sType;
		} else {
			$sField = 'top'.$sType;
		}
		
		$limit = (int)getDBConfigValue($this->marketplace.'.topten', $this->iMarketPlaceId);
		$aTopTenCat = MagnaDB::gi()->fetchArray(eecho('
			  SELECT DISTINCT '.$sField.'
			    FROM '.TABLE_MAGNA_HOOD_PROPERTIES.' 
			   WHERE '.$sField.' != 0
			         AND '.$sField.' != ""
			         AND mpID = "'.$this->iMarketPlaceId.'"
			GROUP BY '.$sField.' 
			ORDER BY COUNT( `'.$sField.'` ) DESC
			'.(($limit != 0) ? 'LIMIT '.$limit : '').'
		', false), true);
		
		#echo print_m($aTopTenCat, '$aTopTenCat');
		if (empty($aTopTenCat)) {
			return array();
		}
		
		$hcm = new HoodCategoryMatching();
		
		$aTopTenCatIds = array();
		foreach ($aTopTenCat as $iCatId) {
			$aTopTenCatIds[$iCatId] = $hcm->getHoodCategoryPath($iCatId, $isStore);
			if (strpos($aTopTenCatIds[$iCatId], '"invalid"') !== false) {
				unset($aTopTenCatIds[$iCatId]);
				//*
				// no mpid
				MagnaDB::gi()->query('
					UPDATE '.TABLE_MAGNA_HOOD_PROPERTIES.'
					   SET '.$sField.' = 0,
					       '.$sType.' = 0
					 WHERE '.$sType.'="'.$iCatId.'"
				');
				//*/
			}
		}
		asort($aTopTenCatIds);
		return $aTopTenCatIds;
	}
	
	protected function getTableName() {
		return TABLE_MAGNA_HOOD_PROPERTIES;
	}
	
	public function configCopy() {
		MagnaDb::gi()->query('
			UPDATE '.TABLE_MAGNA_HOOD_PROPERTIES.'
			   SET topPrimaryCategory = PrimaryCategory,
			       topSecondaryCategory = SecondaryCategory
			 WHERE mpID = '.$this->iMarketPlaceId.'
		');
	}
	
	protected function getResettableCategoryDescription() {
		return array (
			'PrimaryCategory' => ML_HOOD_PRIMARY_CATEGORY,
			'SecondaryCategory' => ML_HOOD_SECONDARY_CATEGORY,
		);
	}
	
	protected function getResettableCategoryDefinition() {
		return array (
			'PrimaryCategory' => 'topPrimaryCategory',
			'SecondaryCategory' => 'topSecondaryCategory',
		);
	}
	
	public static function renderConfigForm($args, &$value = '') {
		return self::runRenderConfigForm(new self(), __METHOD__, $args, $value);
	}
}
