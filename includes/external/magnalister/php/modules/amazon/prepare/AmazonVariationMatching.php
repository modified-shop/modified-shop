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
 * (c) 2010 - 2016 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
// äöüß

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES . 'magnacompatible/prepare/VariationMatching.php');
require_once(DIR_MAGNALISTER_MODULES . 'amazon/AmazonHelper.php');

class AmazonVariationMatching extends VariationMatching
{
    /**
     * @return AmazonHelper
     */
    protected function getAttributesMatchingHelper()
    {
        return AmazonHelper::gi();
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
        $categories = array('DATA' => array());
        try {
            $categories = MagnaConnector::gi()->submitRequest(array(
                'ACTION' => 'GetMainCategories',
            ));
        } catch (MagnaException $e) {
            //echo print_m($e->getErrorArray(), 'Error: '.$e->getMessage(), true);
        }

        $htmlCategories = '<option value="">' . ML_AMAZON_LABEL_APPLY_PLEASE_SELECT . '</option>';
        if (!empty($categories['DATA'])) {
            foreach ($categories['DATA'] as $catKey => $catName) {
                $catName = fixHTMLUTF8Entities($catName);
                if ($catKey === $sCategory) {
                    $htmlCategories .= '<option value="' . $catKey . '" selected="selected">' . $catName . '</option>';
                } else {
                    $htmlCategories .= '<option value="' . $catKey . '">' . $catName . '</option>';
                }
            }
        }

        return $htmlCategories;
    }
}
