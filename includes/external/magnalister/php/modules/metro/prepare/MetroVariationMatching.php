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

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

defined('TABLE_MAGNA_METRO_VARIANTMATCHING') OR define('TABLE_MAGNA_METRO_VARIANTMATCHING', 'magnalister_metro_variantmatching');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/prepare/VariationMatching.php');
require_once(DIR_MAGNALISTER_MODULES.'metro/MetroHelper.php');
require_once(DIR_MAGNALISTER_MODULES.'metro/prepare/MetroCategoryMatching.php');
require_once(DIR_MAGNALISTER_MODULES.'metro/classes/MetroTopTenCategories.php');

class MetroVariationMatching extends VariationMatching {
    public function renderAjax() {
        if (isset($_GET['where']) && ($_GET['where'] == 'prepareView')
            && isset($_GET['view']) && ($_GET['view'] == 'varmatch')) {
            $this->oCategoryMatching = $this->getCategoryMatchingHandler();
            echo $this->oCategoryMatching->renderAjax();
        } else {
            parent::renderAjax();
        }
    }

    protected function getCategoryMatchingHandler() {
        return new MetroCategoryMatching();
    }

    protected function getAttributesMatchingHelper() {
        return MetroHelper::gi();
    }

    protected function getTopTenCategoriesHandler() {
        return new MetroTopTenCategories();
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
    [action] => getMetroCategories
    [objID] => y_toggle_825
    [isStoreCategory] => false
)


*/
