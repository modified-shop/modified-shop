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
 * $Id: MagnaCompatibleCategoryMatching.php 1018 2011-04-29 11:20:46Z derpapst $
 *
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/MarketplaceCategoryMatching.php');

class MagnaCompatibleCategoryMatching extends MarketplaceCategoryMatching {
	protected $marketplaceName = '';
	protected $marketplace = '';
	protected $mpID = 0;

	protected $prepareSettings = array();

	public function __construct(&$params) {
		foreach ($params as $attr => &$v) {
			if (isset($this->$attr)) {
				$this->$attr = &$v;
			}
		}
		parent::__construct();
	}
	
	protected function getTableName() {
		return TABLE_MAGNA_COMPAT_CATEGORIES;
	}

	protected function getMatchingBoxHTML() {
		$data = MagnaDB::gi()->fetchArray('
		    SELECT DISTINCT
		           cm.mp_category_id AS MarketplaceCategory,
		           cm.store_category_id AS StoreCategory
		      FROM '.TABLE_MAGNA_SELECTION.' ms
		INNER JOIN '.TABLE_PRODUCTS.' p ON ms.pID = p.products_id
		INNER JOIN '.TABLE_MAGNA_COMPAT_CATEGORYMATCHING.' cm
		           ON '.((getDBConfigValue('general.keytype', '0') == 'artNr')
		               ? 'cm.products_model = p.products_model'
		               : 'cm.products_id = p.products_id'
		           ).'
		     WHERE ms.mpID=\''.$this->mpID.'\'
		           AND cm.mpID=\''.$this->mpID.'\'
		           AND ms.selectionname=\''.$this->prepareSettings['selectionName'].'\'
		           AND ms.session_id=\''.session_id().'\'
		');
		#echo print_m($data, '$data');
		
		$preSelected = array (
			'MarketplaceCategory' => array(),
			'StoreCategory' => array(),
		);
		
		foreach ($data as $row) {
			foreach ($preSelected as $field => $collection) {
				$preSelected[$field][] = isset($row[$field]) ? $row[$field] : array();
			}
		}
		foreach ($preSelected as $field => $collection) {
			$collection = array_unique($collection);
			if (count($collection) == 1) {
				$preSelected[$field] = array_shift($collection);
			} else {
				$preSelected[$field] = null;
			}
		}
		
		if (!empty($preSelected['MarketplaceCategory'])) {
			$preSelected['MarketplaceCategoryName'] = $this->getMPCategoryPath($preSelected['MarketplaceCategory']);
		} else {
			$preSelected['MarketplaceCategoryName'] = '';
		}
		if (!empty($preSelected['StoreCategory'])) {
			$preSelected['StoreCategoryName'] = $this->getShopCategoryPath($preSelected['StoreCategory']);
		} else {
			$preSelected['StoreCategoryName'] = '';
		}
		
		#echo print_m($preSelected, 'preSelected');
		
		$html = '
			<style>
table.actions table.matchingTable {
	width: 100%;
}
body.magna table.actions tbody table.matchingTable tbody tr td {
	text-align: left;
	width: 90%;
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
				<tr><td colspan="2">'.ML_MAGNACOMPAT_CATEGORYMATCHING_ASSIGN_MP_CAT.'</td></tr>
				<tr>
					<td><div class="catVisual" id="mpCategoryVisual">'.$preSelected['MarketplaceCategoryName'].'</div></td>
					<td class="buttons">
						<input type="hidden" id="mpCategory" name="mpCategory" value="'.$preSelected['MarketplaceCategory'].'"/>
						<input type="hidden" id="mpCategoryName" name="mpCategoryName" value="'.strip_tags($preSelected['MarketplaceCategoryName']).'"/>
						<input class="fullWidth ml-button smallmargin" type="button" value="'.ML_LABEL_CHOOSE.'" id="selectMPCategory"/>
					</td>
				</tr>
				<tr><td colspan="2">&nbsp;</td></tr>'.(!getDBConfigValue(array($this->marketplace.'.catmatch.mpshopcats', 'val'), $this->mpID, false) ? ('
				<tr><td colspan="2">'.ML_MAGNACOMPAT_CATEGORYMATCHING_ASSIGN_SHOP_CAT.'</td></tr>
				<tr>
					<td><div class="catVisual" id="storeCategoryVisual">'.$preSelected['StoreCategoryName'].'</div></td>
					<td class="buttons">
						<input type="hidden" id="storeCategory" name="storeCategory" value="'.$preSelected['StoreCategory'].'"/>
						<input type="hidden" id="storeCategoryName" name="storeCategoryName" value="'.strip_tags($preSelected['StoreCategoryName']).'"/>
						<input class="fullWidth ml-button smallmargin" type="button" value="'.ML_LABEL_CHOOSE.'" id="selectStoreCategory"/>
					</td>
				</tr>') : '').'
			</tbody></table>
		';
		ob_start();
/*
TABLE_MAGNA_COMPAT_CATEGORIES
TABLE_MAGNA_COMPAT_CATEGORYMATCHING
*/
?>
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function() {
	$('#selectMPCategory').click(function() {
		mpCategorySelector.startCategorySelector(function(cID) {
			$('#mpCategory').val(cID);
			mpCategorySelector.getCategoryPath($('#mpCategoryVisual'));
			$('#mpCategoryName').val($('#mpCategoryVisual').html());
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
