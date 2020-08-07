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

require_once(DIR_MAGNALISTER_MODULES.'googleshopping/catmatch/GoogleshoppingCategoryMatching.php');
require_once(DIR_MAGNALISTER_MODULES.'googleshopping/GoogleshoppingHelper.php');

class GoogleshoppingPrepareView extends MagnaCompatibleBase {
    protected $catMatch = null;
    protected $prepareSettings = array();

    protected function showItemName() {
        $itemName = MagnaDB::gi()->fetchArray(eecho('
			SELECT pd.products_name
			 FROM  '.TABLE_MAGNA_SELECTION.' ms, '.TABLE_PRODUCTS_DESCRIPTION.' pd
			 WHERE ms.mpID=\''.$this->mpID.'\'
			  AND ms.selectionname = \'prepare\'
			  AND ms.pID = pd.products_id
		', false));

        if (1 != MagnaDB::gi()->numRows()) {
            return '';
        }
        return '
			<tr class="odd">
				<th>'.ML_COMPARISON_SHOPPING_FIELD_ITEM_TITLE.'</th>
				<td>
					'.$itemName[0]['products_name'].'
				</td>
				<td class="info">&nbsp;</td>
			</tr>
		';
    }

    protected function process() {
        $this->initCategoryMatching();

        $html = '
			<form method="post" action="'.toURL($this->resources['url']).'">
				'.$this->catMatch->renderMatching();

        $defaultMpCategory = '0';
        $defaultMpCategoryName = array();

        $preselectPreparedValues = MagnaDB::gi()->fetchArray(eecho('
			SELECT mp_category_id, mp_category_name, condition_id, shippingtime, age_rating, comment
			 FROM '.TABLE_MAGNA_GOOGLESHOPPING_PREPARE.' gp, '.TABLE_MAGNA_SELECTION.' ms
			 WHERE ms.mpID=\''.$this->mpID.'\'
			  AND ms.selectionname = \'prepare\'
			  AND ms.pID = gp.products_id
			  AND ms.mpID = gp.mpID
		', false));

        $numberOfPreparedValues = MagnaDB::gi()->numRows();

        if (1 == $numberOfPreparedValues) {
            $defaultMpCategory = $preselectPreparedValues[0]['mp_category_id'];
            $defaultMpCategoryName = $this->catMatch->getMPCategory($defaultMpCategory);
            if (is_array($defaultMpCategoryName)) {
                $defaultMpCategoryName = fixHTMLUTF8Entities($defaultMpCategoryName['CategoryName']);
            }
            ob_start(); ?>
            <script type="text/javascript">/*<![CDATA[*/
                (function ($) {
                    $(document).ready(function () {
                        $('#mpCategory').val('<?php echo $defaultMpCategory; ?>');
                        $('#mpCategoryName').val('<?php echo $defaultMpCategoryName; ?>');
                        $('#mpCategoryVisual').html('<?php echo $this->catMatch->getMPCategoryPath($defaultMpCategory); ?>');
                    });
                }(jQuery))
                /*]]>*/</script>
            <?php
            $html .= ob_get_contents();
            ob_end_clean();
        }

        $html .= '
			<table class="attributesTable"><tbody>'.$this->showItemName();

        $html .= '
				<tr class="spacer">
					<td colspan="3">&nbsp;</td>
				</tr>
			</tbody></table>
			<table class="actions">
				<thead><tr><th>'.ML_LABEL_ACTIONS.'</th></tr></thead>
				<tbody>
					<tr><td>
						<table><tbody>
							<tr><td>
								<input type="submit" class="ml-button mlbtn-action" name="saveMatching" value="'.ML_BUTTON_LABEL_SAVE_DATA.'"/>
							</td></tr>
						</tbody></table>
					</td></tr>
				</tbody>
			</table>';

        $html .= '
			</form>';

        return $html;
    }

    private function initCategoryMatching() {
        $params = array();
        foreach (array('mpID', 'marketplace', 'marketplaceName', 'prepareSettings') as $attr) {
            if (isset($this->$attr)) {
                $params[$attr] = &$this->$attr;
            }
        }

        $this->catMatch = new GoogleshoppingCategoryMatching($params);
    }

    public function renderAjax() {
        $this->initCategoryMatching();

        return $this->catMatch->renderAjax();
    }
}
