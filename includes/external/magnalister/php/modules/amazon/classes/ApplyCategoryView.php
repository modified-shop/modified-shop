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
 * $Id: ApplyCategoryView.php 4688 2014-10-08 13:06:57Z miguel.heredia $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/QuickCategoryView.php');

class ApplyCategoryView extends QuickCategoryView {

	public function __construct($cPath = 0, $settings = array(), $sorting = false, $search = '') {
		global $_MagnaSession;

		$settings = array_merge(array(
			'selectionName'   => 'checkin',
			'selectionValues' => array (
				'quantity' => null
			)
		), $settings);

		$filter = array();
		
		if (($matchedItems = MagnaDB::gi()->fetchArray('
				SELECT DISTINCT '.(
					(getDBConfigValue('general.keytype', '0') == 'artNr') ? 'products_model' : 'products_id'
				).'
				  FROM '.TABLE_MAGNA_AMAZON_PROPERTIES.' 
				 WHERE `asin`<>"" 
				       AND `asin` IS NOT NULL 
				       AND mpID="'.$_MagnaSession['mpID'].'"
			', true)) !== false
		) {
			if (getDBConfigValue('general.keytype', '0') == 'artNr') {
				$filter[] = array(
					'join' => '',
					'where' => 'p.products_model NOT IN ("'.implode('", "', MagnaDB::gi()->escape($matchedItems)).'")'
				);
			} else {
				$filter[] = array(
					'join' => '',
					'where' => 'p2c.products_id NOT IN ("'.implode('", "', $matchedItems).'")'
				);
			}
		}

		if (defined('MAGNA_FIELD_ATTRIBUTES_EAN') && (
			($attrWEAN = MagnaDB::gi()->fetchArray('
				SELECT DISTINCT products_id FROM '.TABLE_PRODUCTS_ATTRIBUTES.' WHERE '.MAGNA_FIELD_ATTRIBUTES_EAN.'<>""
			', true)) !== false
		)) {
			$filter[] = array(
				'join' => '',
				'where' => '(p.'.MAGNA_FIELD_PRODUCTS_EAN.'<>"" OR p2c.products_id IN ("'.implode('", "', $attrWEAN).'"))'
			);
		} else {
			$filter[] = array(
				'join' => '',
				'where' => 'p.'.MAGNA_FIELD_PRODUCTS_EAN.'<>""'
			);
		}
		$this->setCat2ProdCacheQueryFilter($filter);
		if ($search!='') {
			$this->blUseParent=true;
		}
		parent::__construct($cPath, $settings, $sorting, $search);
		
		if (!isset($_GET['kind']) || ($_GET['kind'] != 'ajax')) {
			$this->simplePrice->setCurrency(getCurrencyFromMarketplace($this->_magnasession['mpID']));
		}
	}

	protected function init() {
		parent::init();
		
		$this->productIdFilterRegister('ManufacturerFilter', array());
	}

	/**
	 * see parent, adding ean to filter
	 * @param int $iId
	 * @return array
	 */
	protected function getProductsCountOfCategoryInfo($iId) {
		if (!isset($this->aCatInfo[$iId])) {
			$aOut = array(
				'iTotal' => 0,
				'iMatched' => 0,
				'iFailed' => 0
			);
			$aCatIds   = $this->getAllSubCategoriesOfCategory($iId);
			$aCatIds[] = $iId;
			$sIdent    = (getDBConfigValue('general.keytype', '0') == 'artNr') ? 'products_model' : 'products_id';
			
			/** @var array $aCategoryProducts all products in category */
			$aCategoryProducts = MagnaDB::gi()->fetchArray('
				    SELECT DISTINCT p.products_id , p.' . $sIdent . ', p.' . MAGNA_FIELD_PRODUCTS_EAN . '
				      FROM ' . TABLE_PRODUCTS_TO_CATEGORIES . ' p2c
				INNER JOIN ' . TABLE_PRODUCTS . ' p on p2c.products_id=p.products_id
				     WHERE p2c.categories_id IN(' . implode(', ', $aCatIds) . ')
				           '.($this->showOnlyActiveProducts ? 'AND p.products_status<>0' : '').'
				           '.(($sIdent == 'products_model') ? 'AND p.products_model != "" AND p.products_model IS NOT NULL' : '').'
			');
			
			/** @var array $aProducts all valid products of this category */
			$aProducts = array();
			if (!empty($aCategoryProducts)) {
				/** @var array $aSimpleProducts products which *don't need* attributes_ean to be valid */
				$aSimpleProducts       = array();
				/** @var array $aAttributePreProducts products which *need* attributes_ean to be valid */
				$aAttributePreProducts = array();
				/** @var array $aAttributeProducts products which *have* valid attributes_ean */
				$aAttributeProducts    = array();
				foreach ($aCategoryProducts as $aProduct) {
					if (($aProduct[MAGNA_FIELD_PRODUCTS_EAN] !== '') && ($aProduct[MAGNA_FIELD_PRODUCTS_EAN] !== null)) { // have ean
						$aSimpleProducts[] = array(
							$sIdent => $aProduct[$sIdent]
						);
					} else { //need attribute ean => more complex query
						$aAttributePreProducts[] = $aProduct['products_id'];
					}
				}
				if (!empty($aAttributePreProducts) && defined('MAGNA_FIELD_ATTRIBUTES_EAN')) {
					$sSql = '
						   SELECT DISTINCT p.' . $sIdent . '
						     FROM ' . TABLE_PRODUCTS . ' p
						LEFT JOIN ' . TABLE_PRODUCTS_ATTRIBUTES . ' pa on p.products_id=pa.products_id 
						    WHERE p.products_id IN(' . implode(', ', $aAttributePreProducts) . ')
						          AND pa.' . MAGNA_FIELD_ATTRIBUTES_EAN . '!=""
					';
					$aAttributeProducts = MagnaDB::gi()->fetchArray($sSql);
				} else {
					$aAttributeProducts = array();
				}
				$aProducts = array_merge($aAttributeProducts, $aSimpleProducts);
			}
			$aProductIds = array();
			foreach ($aProducts as $aRow) {
				$aProductIds[$aRow[$sIdent]] = $aRow[$sIdent];
			}
			if (count($aProductIds) > 0) {
				$aAll = MagnaDB::gi()->fetchArray("
					SELECT DISTINCT " . $sIdent . " from " . TABLE_MAGNA_AMAZON_PROPERTIES . " 
					 WHERE " . $sIdent . " in('" . implode("', '", $aProductIds) . "')
					       AND mpID='" . $this->_magnasession['mpID'] . "'
				");
				foreach ($aAll as $aRow) {
					unset($aProductIds[$aRow[$sIdent]]);
				}
			}
			$aOut['iTotal'] = count($aProductIds);
			if (count($aProductIds)) {
				$sSql = "
					  SELECT DISTINCT COUNT(products_id) AS count, is_incomplete 
					    FROM " . TABLE_MAGNA_AMAZON_APPLY . " 
					   WHERE " . $sIdent . "  IN('" . implode("', '", $aProductIds) . "')
					         AND mpid='" . $this->_magnasession['mpID'] . "'
					GROUP BY is_incomplete;
				";
				foreach (MagnaDB::gi()->fetchArray($sSql) as $aInfo) {
					if ($aInfo['is_incomplete'] == 'true') {
						$aOut['iFailed'] += $aInfo['count'];
					} else {
						$aOut['iMatched'] += $aInfo['count'];
					}
				}
			}
			$this->aCatInfo[$iId] = $aOut;
		}
		return $this->aCatInfo[$iId];
	}
	
	public function getAdditionalHeadlines() {
		return '
			<td class="matched">'. ML_LABEL_DATA_PREPARED.'</td>';
	}
	
	public function getAdditionalCategoryInfo($cID, $data = false) {
		return parent::renderAdditionalCategoryInfo($cID);
	}

	public function getAdditionalProductInfo($pID, $product = false) {
		$a = MagnaDB::gi()->fetchRow('
			SELECT products_id, is_incomplete
			  FROM '.TABLE_MAGNA_AMAZON_APPLY.' 
			 WHERE '.((getDBConfigValue('general.keytype', '0') == 'artNr')
						? 'products_model="'.MagnaDB::gi()->escape($product['products_model']).'"'
						: 'products_id="'.$pID.'"'
					).'
			       AND mpID="'.$this->_magnasession['mpID'].'"
		');
		if ($a !== false) {
			if ($a['is_incomplete'] == 'true') {
				return '
					<td>'.html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/red_dot.png', ML_AMAZON_LABEL_APPLY_PREPARE_INCOMPLETE, 9, 9).'</td>';				
			} else {
				return '
					<td>'.html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/green_dot.png', ML_AMAZON_LABEL_APPLY_PREPARE_COMPLETE, 9, 9).'</td>';				
			}
		}
		return '
			<td>'.html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/grey_dot.png', ML_AMAZON_LABEL_APPLY_NOT_PREPARED, 9, 9).'</td>';
	}
	
	public function getFunctionButtons() {
		return '
			<input type="hidden" value="'.$this->settings['selectionName'].'" name="selectionName"/>
			<input type="hidden" value="_" id="actionType"/>
			<table class="right"><tbody>
				<tr>
					<td class="texcenter inputCell">
						<table class="right"><tbody>
							<tr><td><input type="submit" class="fullWidth ml-button smallmargin" value="'. ML_AMAZON_BUTTON_PREPARE.'" id="apply" name="apply"/></td></tr>
						</tbody></table>
					</td>
				</tr>
			</tbody></table>
			<div id="finalInfo" class="dialog2" title="'.ML_LABEL_INFORMATION.'"></div>
		';
	}

	public function getLeftButtons() {
		// ML_AMAZON_BUTTON_APPLY_DELETE
		return '
			<input type="submit" class="ml-button" value="'.ML_EBAY_BUTTON_UNPREPARE.'" id="removeapply" name="removeapply"/><br>
			<input type="submit" class="ml-button" value="'.ML_EBAY_BUTTON_RESET_DESCRIPTION.'" id="resetapply" name="resetapply"/>';
	}
	
	protected function getEmptyInfoText() {
		return ML_AMAZON_LABEL_APPLY_EMPTY;
	}

}
