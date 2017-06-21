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
 * $Id: $
 *
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_INCLUDES . 'lib/classes/MarketplaceCategoryMatching.php');

class CrowdfoxCategoryMatching extends MarketplaceCategoryMatching {
    protected function getTableName() {
        return TABLE_MAGNA_COMPAT_CATEGORIES;
    }

    protected function getMatchingBoxHTML() {
        return '';
    }

    protected function getActionBoxHTML() {
        return '';
    }

    protected function insertMpCategories($categories) {
        $now = gmdate('Y-m-d H:i:s');
        foreach ($categories as $curRow) {
            if (!isset($curRow['mpID'])) {
                $curRow['mpID'] = '0';
            }

            if ($this->hasPlatformCol) {
                $curRow['platform'] = $this->marketplace;
            }

            $curRow['InsertTimestamp'] = $now;
            $curRow['Selectable'] = '1';

            $not = array_diff(array_keys($curRow), $this->columns);
            if (!empty($not)) {
                foreach ($not as $notKey) {
                    unset($curRow[$notKey]);
                }
            }

            #echo print_m($curRow, $this->getTableName());
            MagnaDB::gi()->insert($this->getTableName(), $curRow, true);
        }
    }

    public function getPostCategoryId() {
        return isset($_POST['id']) ? $_POST['id'] : '';
    }

    public function getMPCategory($categoryID, $secondCall = false) {
        return false;
    }

    public function importMPCategories($parentID = 0) {
        return true;
    }

    protected function renderMatchingDialog() {
        return '
			<div id="mpCategorySelector" class="dialog2" title="' . ML_LABEL_MATCH_CATEGORIES . '">
				<table id="catMatch"><tbody>
					<tr><td id="mpCats" class="catView"><div class="catView"></div></td></tr>
					<tr><td style="height: 0.5em"></td></tr>
					<tr><td class="catVisual" id="tmpSelectedCat"></td></tr>
				</tbody></table>
				<div id="messageDialog" class="dialog2"></div>
			</div>';
    }

    private function renderCategories($parentID = 0, $purge = false) {

        return '';
    }

}