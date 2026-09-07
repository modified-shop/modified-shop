<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/TopTen.php');
require_once(DIR_MAGNALISTER_MODULES.'temu/prepare/TemuCategoryMatching.php');

class TemuTopTenCategories extends TopTen {

	public function getTopTenCategories($sType, $sGetCatPathFunc = 'getMPCategoryPath') {
		$sType = 'top'.str_replace('ies', 'y', $sType);
		$limit = (int)getDBConfigValue($this->marketplace.'.topten', $this->iMarketPlaceId);
		$aTopTenCat = MagnaDB::gi()->fetchArray('
			  SELECT DISTINCT '.$sType.'
			    FROM '.TABLE_MAGNA_TEMU_PREPARE.'
			   WHERE '.$sType.' != 0
			         AND '.$sType.' != ""
			         AND mpID = "'.$this->iMarketPlaceId.'"
			GROUP BY '.$sType.'
			ORDER BY COUNT( `'.$sType.'` ) DESC
			'.(($limit != 0) ? 'LIMIT '.$limit : '').'
		', true);

		if (empty($aTopTenCat)) {
			return array();
		}

		$oDCM = new TemuCategoryMatching();

		$aTopTenCatIds = array();
		foreach ($aTopTenCat as $iCatId) {
			$aTopTenCatIds[$iCatId] = $oDCM->$sGetCatPathFunc($iCatId);
			if (strpos($aTopTenCatIds[$iCatId], '"invalid"') !== false) {
				unset($aTopTenCatIds[$iCatId]);
				MagnaDB::gi()->query('
					UPDATE '.TABLE_MAGNA_TEMU_PREPARE.'
					   SET '.$sType.' = 0
					 WHERE '.$sType.' = "'.$iCatId.'"
				');
			}
		}
		asort($aTopTenCatIds);
		return $aTopTenCatIds;
	}

	protected function getTableName() {
		return TABLE_MAGNA_TEMU_CATEGORIES;
	}

	public function configCopy() {
	}

	protected function getResettableCategoryDescription() {
		return array();
	}

	protected function getResettableCategoryDefinition() {
		return array();
	}

	public static function renderConfigForm($args, &$value = '') {
		return self::runRenderConfigForm(new self(), __METHOD__, $args, $value);
	}
}
