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
 * $Id: CheckinCategoryView.php 1152 2011-07-25 16:34:12Z derpapst $
 *
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimpleCheckinCategoryView.php');

class MagnaCompatibleCheckinCategoryView extends SimpleCheckinCategoryView {

	public function __construct($cPath = 0, $settings = array(), $sorting = false, $search = '') {
		global $_MagnaSession;
		$settings = array_merge(array(
			'selectionName'   => 'checkin',
			'selectionValues' => array (
				'quantity' => null
			)
		), $settings);
		
		$preparedItems = MagnaDB::gi()->fetchArray('
			SELECT DISTINCT '.(
				(getDBConfigValue('general.keytype', '0') == 'artNr') 
					? 'products_model' 
					: 'products_id'
				).'
			  FROM '.TABLE_MAGNA_COMPAT_CATEGORYMATCHING.' 
			 WHERE mp_category_id<>\'\' 
			   AND mpID=\''.$_MagnaSession['mpID'].'\'
		', true);
		#echo print_m($preparedItems, '$preparedItems');

		if (!empty($preparedItems)) {
			if (getDBConfigValue('general.keytype', '0') == 'artNr') {
				$filter = array(
					'join' => '',
					'where' => 'p.products_model IN (\''.implode('\', \'', MagnaDB::gi()->escape($preparedItems)).'\')'
				);
			} else {
				$filter = array(
					'join' => '',
					'where' => 'p2c.products_id IN (\''.implode('\', \'', $preparedItems).'\')'
				);
			}
		} else {
			$filter = array(
				'join' => '',
				'where' => '0=1'
			);
		}
		#echo print_m(array($filter),'array($filter)');

		$this->setCat2ProdCacheQueryFilter(array($filter));

		parent::__construct($cPath, $settings, $sorting, $search);
		
		if (!$this->isAjax) {
			$this->simplePrice->setCurrency(getCurrencyFromMarketplace($this->_magnasession['mpID']));
		}
	}

	public function getAdditionalHeadlines() {
		return '
			<td>'.ML_MAGNACOMPAT_LABEL_CATEGORY.'</td>';
	}

	public function getAdditionalCategoryInfo($cID, $data = false) {
		return '
			<td>&mdash;</td>';
	}

	public function getAdditionalProductInfo($pID, $data = false) {
		$a = MagnaDB::gi()->fetchRow('
			SELECT products_id, mp_category_id, store_category_id
			  FROM '.TABLE_MAGNA_COMPAT_CATEGORYMATCHING.' 
			 WHERE '.((getDBConfigValue('general.keytype', '0') == 'artNr')
						? 'products_model=\''.MagnaDB::gi()->escape($data['products_model']).'\''
						: 'products_id=\''.$pID.'\''
					).'
				   AND mpID=\''.$this->_magnasession['mpID'].'\'
		');
		$matchMPShopCats = !getDBConfigValue(array($this->marketplace.'.catmatch.mpshopcats', 'val'), $this->mpID, false);
		return '
			<td>
				<table class="nostyle"><tbody>
					<tr><td class="label">MP:&nbsp;</td><td>'.(empty($a['mp_category_id']) ? '&mdash;' : $a['mp_category_id']).'</td><tr>
					'.($matchMPShopCats
						? ('<tr><td class="label">Store:&nbsp;</td><td>'.(empty($a['store_category_id']) ? '&mdash;' : $a['store_category_id']).'</td><tr>')
						: ''
					).'
				</tbody></table>
			</td>';
	}
	
	protected function getEmptyInfoText() {
		if (empty($this->search)) {
			return ML_GENERIC_TEXT_NO_PREPARED_PRODUCTS;
		} else {
			return parent::getEmptyInfoText();
		}
	}
	
}
