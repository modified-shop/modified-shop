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
 * $Id: CheckinCategoryView.php 3809 2014-04-24 13:58:24Z derpapst $
 *
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimpleCheckinCategoryView.php');

class Ayn24CheckinCategoryView extends SimpleCheckinCategoryView {
	protected $preptable = TABLE_MAGNA_AYN24_CATEGORYMATCHING;
	
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
			  FROM '.TABLE_MAGNA_AYN24_PROPERTIES.'
			 WHERE MarketplaceCategory <> ""
			       AND mpID="'.$_MagnaSession['mpID'].'"
		', true);
		#echo print_m($preparedItems, '$preparedItems');

		if (!empty($preparedItems)) {
			if (getDBConfigValue('general.keytype', '0') == 'artNr') {
				$filter = array(
					'join' => '',
					'where' => 'p.products_model IN ("'.implode('", "', MagnaDB::gi()->escape($preparedItems)).'")'
				);
			} else {
				$filter = array(
					'join' => '',
					'where' => 'p2c.products_id IN ("'.implode('", "', $preparedItems).'")'
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
		
		if (!isset($_GET['kind']) || ($_GET['kind'] != 'ajax')) {
			$this->simplePrice->setCurrency(getCurrencyFromMarketplace($this->_magnasession['mpID']));
		}
	}
	
	public function getAdditionalHeadlines() {
		return '
			<td>'.ML_AYN24_LABEL_CATEGORY.'</td>';
	}

	public function getAdditionalCategoryInfo($cID, $data = false) {
		return '
			<td>&mdash;</td>';
	}

	public function getAdditionalProductInfo($pID, $data = false) {
		$a = MagnaDB::gi()->fetchRow('
			SELECT *
			  FROM '.TABLE_MAGNA_AYN24_PROPERTIES.'
			 WHERE '.((getDBConfigValue('general.keytype', '0') == 'artNr')
						? 'products_model="'.MagnaDB::gi()->escape($data['products_model']).'"'
						: 'products_id="'.$pID.'"'
					).'
			        AND mpID="'.$this->_magnasession['mpID'].'"
		');
		return '
			<td>
				<table class="nostyle"><tbody>
					<tr><td class="label">MP:&nbsp;</td><td>'.(empty($a['MarketplaceCategory']) ? '&mdash;' : $a['MarketplaceCategory']).'</td><tr>
					<tr><td class="label">Store:&nbsp;</td><td>'.(empty($a['StoreCategory']) ? '&mdash;' : $a['StoreCategory']).'</td><tr>
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
