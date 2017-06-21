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
 * $Id: PrepareCategoryView.php 699 2011-01-17 23:03:36Z derpapst $
 *
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimpleCategoryView.php');

class CrowdfoxPrepareCategoryView extends SimpleCategoryView {
	public function __construct($cPath = 0, $settings = array(), $sorting = false, $search = '', $productIDs = array()) {
		parent::__construct($cPath, $settings, $sorting, $search, $productIDs);

		if (!$this->isAjax) {
			$this->simplePrice->setCurrency(getCurrencyFromMarketplace($this->_magnasession['mpID']));
		}
	}

	protected function init() {
		parent::init();
		
		# take only products with EAN set
		$this->productIdFIlterRegister('EANMasterFilter', array());
		$this->productIdFilterRegister('ManufacturerFilter', array());
	}

	public function getAdditionalHeadlines() {
		return '
			<td class="lowestprice">'.ML_MAGNACOMPAT_LABEL_CATEGORY.'</td>
			<td class="matched">'.ML_MAGNACOMPAT_LABEL_PREPARED.'</td>';
	}

	public function getAdditionalCategoryInfo($cID, $data = false) {
		$html = '<td>&mdash;</td>';

		$pIDs = $this->list['categories'][$cID]['allproductsids'];
		if (!empty($pIDs)) {
			$totalItems = count($pIDs);
			$itemsFailed = 0;
			$itemsMatched = 0;
			if (getDBConfigValue('general.keytype', '0') == 'artNr') {
				$query = '
					SELECT COUNT(DISTINCT pa.products_id) as itemsCount,
					       SUM(IF(pa.mp_category_id=\'\', 1, 0)) as incompleteCount
					  FROM '.TABLE_PRODUCTS.' p, '.TABLE_MAGNA_CROWDFOX_PREPARE.' pa
					 WHERE p.products_id IN (\''.implode('\', \'', $pIDs).'\')
					       AND p.products_model=pa.products_model
					       AND p.products_model<>\'\'
					       AND mpID = '.$this->_magnasession['mpID'].'	
				';
			} else {
				$query = '
					SELECT COUNT(DISTINCT products_id) as itemsCount,
					       SUM(IF(mp_category_id=\'\', 1, 0)) as incompleteCount
					  FROM '.TABLE_MAGNA_CROWDFOX_PREPARE.'
					 WHERE products_id IN (\''.implode('\', \'', $pIDs).'\')
					       AND mpID = '.$this->_magnasession['mpID'].'	
				';
			}
			$itemsApplied = MagnaDB::gi()->fetchRow($query);
			#echo print_m($itemsApplied, '$itemsApplied ('.$totalItems.')');
		}
		if ($itemsApplied !== false) {
			if ($itemsApplied['itemsCount'] == 0) {
				/* Keine Artikel beantragt */
				return $html.'
					<td title="'.ML_MAGNACOMPAT_LABEL_CATMATCH_NOT_PREPARED.'">'.
						html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/grey_dot.png', ML_MAGNACOMPAT_LABEL_CATMATCH_NOT_PREPARED, 9, 9).
					'</td>';
			}
			if ($itemsApplied['incompleteCount'] == $totalItems) {
				/* Alle Artikel in Kategorie unvollstaendig beantragt */
				return $html.'
					<td title="'.ML_MAGNACOMPAT_LABEL_CATMATCH_PREPARE_INCOMPLETE.'">'.
						html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/red_dot.png', ML_MAGNACOMPAT_LABEL_CATMATCH_PREPARE_INCOMPLETE, 9, 9).
					'</td>';
			}
			if (($itemsApplied['itemsCount'] == $totalItems) && ($itemsApplied['incompleteCount'] == 0)) {
				/* Alle Artikel in Kategorie beantragt */
				return $html.'
					<td title="'.ML_MAGNACOMPAT_LABEL_CATMATCH_PREPARE_COMPLETE.'">'.
						html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/green_dot.png', ML_MAGNACOMPAT_LABEL_CATMATCH_PREPARE_COMPLETE, 9, 9).
					'</td>';
			}
			if ($itemsApplied['itemsCount'] > 0) {
				/* Einige nicht beantragt */
				return $html.'
					<td title="'.ML_MAGNACOMPAT_LABEL_CATMATCH_PREPARE_INCOMPLETE.'">'.
						html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/yellow_dot.png', ML_MAGNACOMPAT_LABEL_CATMATCH_PREPARE_INCOMPLETE, 9, 9).
					'</td>';
			}
		}
		return $html.'
			<td title="'.ML_ERROR_UNKNOWN.' $itemsApplied:'.print_m($itemsApplied, true).' $totalItems:'.$totalItems.'">'.
				html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/red_dot.png', ML_ERROR_UNKNOWN, 9, 9).
				html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/red_dot.png', ML_ERROR_UNKNOWN, 9, 9).
			'</td>';
	}

	private function renderCatBlock($data) {
		$matchMPShopCats = !getDBConfigValue(array($this->marketplace.'.catmatch.mpshopcats', 'val'), $this->mpID, false);
		return '
			<table class="nostyle"><tbody>
				<tr><td>MP:</td><td>'.(empty($data['MarketplaceCategories']) ? '&mdash;' : $data['MarketplaceCategories']).(empty($data['MarketplaceCategoriesName']) ? '' : ' '.$data['MarketplaceCategoriesName']).'</td><tr>
				'.($matchMPShopCats 
					? ('<tr><td>Store:</td><td>'.(empty($data['store_category_id']) ? '&mdash;' : $data['store_category_id']).'</td><tr>')
					: ''
				).'
			</tbody></table>';
	}

	public function getAdditionalProductInfo($pID, $data = false) {
		$a = MagnaDB::gi()->fetchRow(eecho('
			SELECT *
			  FROM '.TABLE_MAGNA_CROWDFOX_PREPARE.' 
			 WHERE '.((getDBConfigValue('general.keytype', '0') == 'artNr') && is_array($data) && isset($data['products_model'])
						? 'products_model=\''.MagnaDB::gi()->escape($data['products_model']).'\''
						: 'products_id=\''.$pID.'\''
					).'
				   AND mpID=\''.$this->_magnasession['mpID'].'\'
		', false));
		#echo var_dump_pre($a);
		if ($a !== false) {
			if ($a['mp_category_id'] != '') {
				return '
					<td>'.$this->renderCatBlock($a).'</td>
					<td>'.html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/green_dot.png', ML_MAGNACOMPAT_LABEL_CATMATCH_PREPARE_COMPLETE, 9, 9).'</td>';
			} else {
				return '
					<td>'.$this->renderCatBlock($a).'</td>
					<td>'.html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/red_dot.png', ML_MAGNACOMPAT_LABEL_CATMATCH_PREPARE_INCOMPLETE, 9, 9).'</td>';
			}
		}
		return '
			<td>&mdash;</td>
			<td>'.html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/grey_dot.png', ML_MAGNACOMPAT_LABEL_CATMATCH_NOT_PREPARED, 9, 9).'</td>';
	}

	public function getFunctionButtons() {
		global $_url;

		$mmatch = true;

		return '
			<input type="hidden" value="'.$this->settings['selectionName'].'" name="selectionName"/>
			<input type="hidden" value="_" id="actionType"/>
			<table class="right"><tbody>
				<tr>
					<td class="texcenter inputCell">
						<input type="submit" class="fullWidth ml-button smallmargin" value="'.ML_EBAY_LABEL_PREPARE.'" id="prepare" name="prepare"/>
					</td>
				</tr>
			</tbody></table>
		';

	}

	public function getLeftButtons() {
		return '<input type="submit" class="ml-button" value="'.ML_EBAY_BUTTON_UNPREPARE.'" id="unprepare" name="unprepare"/>';
	}
}
