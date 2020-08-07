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
 * (c) 2010 - 2019 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/MLProductList.php');

abstract class MLProductListGoogleshoppingAbstract extends MLProductList {
    protected $aPrepareData = array();

    protected function getPreparedStatusIndicator($aRow) {
        $sVerified = $this->getPrepareData($aRow, 'Verified');
        if (empty($sVerified)) {
            return html_image(DIR_MAGNALISTER_WS_IMAGES.'status/grey_dot.png', ML_HOOD_PRODUCT_MATCHED_NO, 9, 9);
        } elseif ('OK' == $sVerified) {
            return html_image(DIR_MAGNALISTER_WS_IMAGES.'status/green_dot.png', ML_HOOD_PRODUCT_PREPARED_OK, 9, 9);
        } elseif ('EMPTY' == $sVerified) {
            return html_image(DIR_MAGNALISTER_WS_IMAGES.'status/white_dot.png', ML_EBAY_PRODUCT_PREPARED_FAULTY_BUT_MP, 9, 9);
        } else {
            return html_image(DIR_MAGNALISTER_WS_IMAGES.'status/red_dot.png', ML_HOOD_PRODUCT_PREPARED_FAULTY, 9, 9);
        }
    }

    protected function getPrepareData($aRow, $sFieldName = null) {
        if (!isset($this->aPrepareData[$aRow['products_id']])) {
            $this->aPrepareData[$aRow['products_id']] = MagnaDB::gi()->fetchRow("
				SELECT *
				  FROM ".TABLE_MAGNA_GOOGLESHOPPING_PREPARE."
				 WHERE ".(
                (getDBConfigValue('general.keytype', '0') != 'artNr')
                    ? 'products_model=\''.MagnaDB::gi()->escape($aRow['products_model']).'\''
                    : 'products_id=\''.$aRow['products_id'].'\''
                )."
					   AND mpID = '".$this->aMagnaSession['mpID']."'
			");
        }
        if ($sFieldName === null) {
            return $this->aPrepareData[$aRow['products_id']];
        } else {
            return isset($this->aPrepareData[$aRow['products_id']][$sFieldName]) ? $this->aPrepareData[$aRow['products_id']][$sFieldName] : null;
        }
    }

    protected function getGoogleShoppingPrice($aRow) {
        return $this->getPrice()
            ->setFinalPriceFromDB($aRow['products_id'], $this->aMagnaSession['mpID'])
            ->format();
    }

    protected function getSelectionName() {
        return 'apply';
    }

    protected function isPreparedDifferently($aRow) {
        $sPrimaryCategory = $this->getPrepareData($aRow, 'MarketplaceCategories');
        if (!empty($sPrimaryCategory)) {
            $sPrimaryCategory = json_decode($sPrimaryCategory, true);
            $sPrimaryCategory = is_array($sPrimaryCategory) ? $sPrimaryCategory['primary'] : $sPrimaryCategory;
            $sCategoryDetails = $this->getPrepareData($aRow, 'CategoryAttributes');
            $categoryMatching = GoogleshoppingHelper::gi()->getCategoryMatching($sPrimaryCategory);
            $categoryDetails = json_decode($sCategoryDetails, true);
            return GoogleshoppingHelper::gi()->detectChanges($categoryMatching, $categoryDetails);
        }

        return false;
    }

    protected function isDeletedAttributeFromShop($aRow, &$message) {
        $aMarketplaceCategories = $this->getPrepareData($aRow, 'MarketplaceCategories');
        if (!empty($aMarketplaceCategories)) {
            $matchedAttributes = $this->getPrepareData($aRow, 'CategoryAttributes');
            $matchedAttributes = json_decode($matchedAttributes, true);
            $shopAttributes = GoogleshoppingHelper::gi()->flatShopVariations();

            if (!is_array($matchedAttributes)) {
                $matchedAttributes = array();
            }

            foreach ($matchedAttributes as $matchedAttribute) {
                if (GoogleshoppingHelper::gi()->detectIfAttributeIsDeletedOnShop($shopAttributes, $matchedAttribute, $message)) {
                    return true;
                }
            }
        }

        return false;
    }
}
