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

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/catmatch/MagnaCompatibleCategoryMatching.php');

class GoogleshoppingCategoryMatching extends MagnaCompatibleCategoryMatching {
    protected function getCategoryValidityPeriod() {
        /* 1 day for googleshopping */
        return 60 * 60 * 24;
    }

    protected function getMatchingBoxHTML() {
        $html = '
			<style>
table.actions table.matchingTable, table.categoryAttributes {
	width: 100%;
}
body.magna table.actions tbody table.matchingTable tbody tr td {
	text-align: left;
	width: auto;
}
body.magna table.actions tbody table.categoryAttributes tbody tr td {
	text-align: left;
	width: auto;
}
body.magna table.actions tbody table.matchingTable tbody tr td.buttons {
	width: 6em;
}
body.magna table.actions tbody table.matchingTable tbody tr td.actionbuttons {
	text-align: right;
}
div.catVisual {
	display: inline-block;
	width: 100%;
	height: 1.5em;
	line-height: 1.5em;
	background: #fff;
	color: #000;
	border: 1px solid #999;
}

			</style>
			<table class="matchingTable"><tbody>
				<tr><td colspan="2">'.ML_MAGNACOMPAT_CATEGORYMATCHING_ASSIGN_MP_CAT.'</td></tr>
				<tr>
					<td><div class="catVisual" id="mpCategoryVisual">'.$primaryCategoryName.'</div></td>
					<td class="buttons">
						<input type="hidden" id="mpCategory" name="mpCategory" value="'.$primaryCategory.'"/>
						<input type="hidden" id="mpCategoryName" name="mpCategoryName" value="'.$primaryCategoryName.'"/>
						<input class="fullWidth ml-button smallmargin" type="button" value="'.ML_LABEL_CHOOSE.'" id="selectMPCategory"/>
					</td>
				</tr>
				<tr><td colspan="2">&nbsp;</td></tr>'.(!getDBConfigValue(array($this->marketplace.'.catmatch.mpshopcats', 'val'), $this->mpID, false) ? ('
				<tr><td colspan="2">'.ML_MAGNACOMPAT_CATEGORYMATCHING_ASSIGN_SHOP_CAT.'</td></tr>') : '').'
			</tbody></table>
			<table class="categoryAttributes"></table>
		';
        ob_start();
        /*
        TABLE_MAGNA_COMPAT_CATEGORIES
        TABLE_MAGNA_COMPAT_CATEGORYMATCHING
        */ ?>
        <script type="text/javascript">/*<![CDATA[*/
            $(document).ready(function () {
                $('#selectMPCategory').click(function () {
                    mpCategorySelector.startCategorySelector(function (cID) {
                        $('#mpCategory').val(cID).trigger('change');
                        mpCategorySelector.getCategoryPath($('#mpCategoryVisual'));
                        $('#mpCategoryName').val($('#mpCategoryVisual').html());
                    }, 'mp');
                });
            });
            /*]]>*/</script>
        <?php
        $html .= ob_get_contents();
        ob_end_clean();

        return $html;
    }

    public function insertMpCategories($categories) {
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
}
