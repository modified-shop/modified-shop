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
// äöüß

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

defined('TABLE_MAGNA_MEINPAKET_VARIANTMATCHING') OR define('TABLE_MAGNA_MEINPAKET_VARIANTMATCHING', 'magnalister_meinpaket_variantmatching');

require_once(DIR_MAGNALISTER_MODULES . 'magnacompatible/prepare/VariationMatching.php');
require_once(DIR_MAGNALISTER_MODULES . 'meinpaket/MeinpaketHelper.php');
require_once(DIR_MAGNALISTER_MODULES . 'meinpaket/prepare/MeinpaketCategoryMatching.php');

class MeinpaketVariationMatching  extends VariationMatching
{
    protected function getAttributesMatchingHelper()
    {
        return MeinpaketHelper::gi();
	}
	
    /**
     * Fetches the options for the top 20 category selectors
     * @param string $sType
     *     Type of category (PrimaryCategory, SecondaryCategory, StoreCategory, StoreCategory2, StoreCategory3)
     * @param string $sCategory
     *     the selected category (empty for newly prepared items)
     * @returns string
     *     option tags for the select element
     */
    protected function renderCategoryOptions($sType, $sCategory)
    {

        $categories = MeinpaketApiConfigValues::gi()->getAvailableVariantConfigurations();
	
        $htmlCategories = '<option value="">' . ML_GENERAL_VARMATCH_PLEASE_SELECT . '</option>';
        if (!empty($categories)) {
            foreach ($categories as $catKey => $catName) {
                if ($catKey === $sCategory) {
                    $htmlCategories .= '<option value="' . fixHTMLUTF8Entities($catKey) . '" selected="selected">' . $catName['Name'] . '</option>';
                } else {
                    $htmlCategories .= '<option value="' . fixHTMLUTF8Entities($catKey) . '">' . $catName['Name'] . '</option>';
                }
			}
		}
		
        return $htmlCategories;
	}
}
