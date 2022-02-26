<?php
/*
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
 * (c) 2010 - 2021 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/TopTen.php');
require_once(DIR_MAGNALISTER_MODULES.'otto/prepare/OttoCategoryMatching.php');

class OttoTopTenCategories extends TopTen {

    public static function renderConfigForm($args, &$value = '') {
        return self::runRenderConfigForm(new self(), __METHOD__, $args, $value);
    }

    public function getTopTenCategories($sType, $sGetCatPathFunc = 'getMPCategoryPath') {
        //categories to category
        #$sType = 'top'.str_replace('ies', 'y', $sType);
        $sType = 'PrimaryCategory';
        $limit = (int)getDBConfigValue($this->marketplace.'.topten', $this->iMarketPlaceId);
        $aTopTenCat = MagnaDB::gi()->fetchArray(eecho('
			  SELECT DISTINCT '.$sType.'
			    FROM '.TABLE_MAGNA_OTTO_PREPARE.'
			   WHERE '.$sType.' != 0
			         AND '.$sType.' != ""
			         AND mpID = "'.$this->iMarketPlaceId.'"
			GROUP BY '.$sType.'
			ORDER BY COUNT( `'.$sType.'` ) DESC
			'.(($limit != 0) ? 'LIMIT '.$limit : '').'
		', false), true);

        if (empty($aTopTenCat)) {
            $aTopTenCat = MagnaDB::gi()->fetchArray('
				SELECT DISTINCT MpIdentifier
				  FROM '.TABLE_MAGNA_OTTO_VARIANTMATCHING.'
				 WHERE mpID = '.$this->iMarketPlaceId.'
                 AND MpIdentifier != "category_independent_attributes"
                 AND MpIdentifier <> ""
			', true);
        }
        if (empty($aTopTenCat)) {
            return array();
        }

        $oDCM = new OttoCategoryMatching();

        $sGetCatPathFunc = 'getOttoCategoryName';
        $aTopTenCatIds = array();
        foreach ($aTopTenCat as $iCatId) {
            $aTopTenCatIds[$iCatId] = $oDCM->$sGetCatPathFunc($iCatId);
            if (strpos($aTopTenCatIds[$iCatId], '"invalid"') !== false) {
                unset($aTopTenCatIds[$iCatId]);
                // no mpid
                MagnaDB::gi()->query('
					UPDATE '.TABLE_MAGNA_OTTO_PREPARE.'
					   SET '.$sType.' = 0
					 WHERE '.$sType.' = "'.$iCatId.'"
				');
            }
        }
        asort($aTopTenCatIds);
        return $aTopTenCatIds;

    }

    public function configCopy() {
    }

    protected function getTableName() {
        return false;
    }

    protected function getResettableCategoryDescription() {
        return array();
    }

    protected function getResettableCategoryDefinition() {
        return array();
    }
}
