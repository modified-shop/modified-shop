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

defined('TABLE_MAGNA_ETSY_VARIANTMATCHING') OR define('TABLE_MAGNA_ETSY_VARIANTMATCHING', 'magnalister_etsy_variantmatching');

require_once(DIR_MAGNALISTER_MODULES . 'magnacompatible/prepare/VariationMatching.php');
require_once(DIR_MAGNALISTER_MODULES . 'etsy/EtsyHelper.php');
require_once(DIR_MAGNALISTER_MODULES . 'etsy/prepare/EtsyCategoryMatching.php');
require_once(DIR_MAGNALISTER_MODULES . 'etsy/classes/EtsyTopTenCategories.php');

class EtsyVariationMatching extends VariationMatching
{
    protected function getAttributesMatchingHelper()
    {
        return EtsyHelper::gi();
    }

    protected function getTopTenCategoriesHandler()
    {
        return new EtsyTopTenCategories();
    }

    protected function getCategoryMatchingHandler()
    {
        return new EtsyCategoryMatching();
    }

    public function renderAjax() {
        if (    isset($_GET['where']) && ($_GET['where'] == 'prepareView')
             && isset($_GET['view'])  && ($_GET['view'] == 'varmatch')) {
            $this->oCategoryMatching = $this->getCategoryMatchingHandler();
            echo $this->oCategoryMatching->renderAjax();
        } else {
            parent::renderAjax();
        }
    }
}


/*

variation_matching.js wird inkludiert in VariationMatching::renderJs(),
und das wird aufgerufen in VariationMatching::process()

$_GET :: Array
(
    [mp] => 42718
    [mode] => prepare
    [view] => varmatch
    [where] => prepareView
    [kind] => ajax
)

$_POST :: Array
(
    [action] => getEtsyCategories
    [objID] => y_toggle_825
    [isStoreCategory] => false
)


*/
