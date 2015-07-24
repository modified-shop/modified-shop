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
 * $Id: Ayn24CategoryMatching.php 4283 2014-07-24 22:00:04Z derpapst $
 *
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/MarketplaceCategoryMatching.php');

class Ayn24CategoryMatching extends MarketplaceCategoryMatching {
	protected function getTableName() {
		return TABLE_MAGNA_AYN24_CATEGORIES;
	}

	protected function getMatchingBoxHTML() {
		$html = '
			<style>
table.actions table.matchingTable {
	width: 100%;
}
body.magna table.actions tbody table.matchingTable tbody tr td {
	text-align: left;
	width: auto;
}
body.magna table.actions tbody table.matchingTable tbody tr td.ml-buttons {
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
				<tr><td colspan="2">'.ML_AYN24_CATEGORYMATCHING_ASSIGN_MP_CAT.'</td></tr>
				<tr>
					<td><div class="catVisual" id="mpCategoryVisual">'.$primaryCategoryName.'</div></td>
					<td class="buttons">
						<input type="hidden" id="mpCategory" name="mpCategory" value="'.$primaryCategory.'"/>
						<input class="fullWidth ml-button smallmargin" type="button" value="W&auml;hlen" id="selectMPCategory"/>
					</td>
				</tr>
				<tr><td colspan="2">&nbsp;</td></tr>'.(!getDBConfigValue(array($this->marketplace.'.catmatch.mpshopcats', 'val'), $this->mpID, false) ? ('
				<tr><td colspan="2">'.ML_AYN24_CATEGORYMATCHING_ASSIGN_SHOP_CAT.'</td></tr>
				<tr>
					<td><div class="catVisual" id="storeCategoryVisual">'.$primaryCategoryName.'</div></td>
					<td class="buttons">
						<input type="hidden" id="storeCategory" name="storeCategory" value="'.$primaryCategory.'"/>
						<input class="fullWidth ml-button smallmargin" type="button" value="W&auml;hlen" id="selectStoreCategory"/>
					</td>
				</tr>') : '').'
			</tbody></table>
		';
		ob_start();
?>
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function() {
	$('#selectMPCategory').click(function() {
		mpCategorySelector.startCategorySelector(function(cID) {
			$('#mpCategory').val(cID);
			mpCategorySelector.getCategoryPath($('#mpCategoryVisual'));
		}, 'mp');
	});
	$('#selectStoreCategory').click(function() {
		mpCategorySelector.startCategorySelector(function(cID) {
			$('#storeCategory').val(cID);
			mpCategorySelector.getCategoryPath($('#storeCategoryVisual'));
		}, 'store');
	});
});
/*]]>*/</script>
<?php
		$html .= ob_get_contents();	
		ob_end_clean();

		return $html;
	}
	
	protected function getActionBoxHTML() {
		return '
			<table><tbody>
				<tr><td>
					<input type="submit" class="ml-button" name="saveMatching" value="'.ML_BUTTON_LABEL_SAVE_DATA.'"/>
				</td></tr>
			</tbody></table>';
	}



}
