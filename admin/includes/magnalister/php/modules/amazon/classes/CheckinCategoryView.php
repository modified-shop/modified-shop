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
 * $Id: CheckinCategoryView.php 2437 2013-05-06 13:32:58Z tim.neumann $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimpleCheckinCategoryView.php');

class AmazonCheckinCategoryView extends SimpleCheckinCategoryView {

	public function __construct($cPath = 0, $settings = array(), $sorting = false, $search = '') {
		global $_MagnaSession;
		$settings = array_merge(array(
			'selectionName'   => 'checkin',
			'selectionValues' => array (
				'quantity' => null
			)
		), $settings);
		
		$preparedItems = array_unique(array_merge(
			(array)MagnaDB::gi()->fetchArray('
				SELECT DISTINCT '.(
					(getDBConfigValue('general.keytype', '0') == 'artNr') ? 'products_model' : 'products_id'
				).' FROM '.TABLE_MAGNA_AMAZON_PROPERTIES.' WHERE asin<>\'\' AND mpID=\''.$_MagnaSession['mpID'].'\'
			', true),
			(array)MagnaDB::gi()->fetchArray('
				SELECT DISTINCT '.(
					(getDBConfigValue('general.keytype', '0') == 'artNr') ? 'products_model' : 'products_id'
				).' 
				  FROM '.TABLE_MAGNA_AMAZON_APPLY.' 
				 WHERE data<>\'\' 
				       AND is_incomplete=\'false\'
				       AND mpID=\''.$_MagnaSession['mpID'].'\'
			', true)
		));

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

		$this->setCat2ProdCacheQueryFilter(array($filter));

		parent::__construct($cPath, $settings, $sorting, $search);

		if (!isset($_GET['kind']) || ($_GET['kind'] != 'ajax')) {
			$this->simplePrice->setCurrency(getCurrencyFromMarketplace($this->_magnasession['mpID']));
		}
	}

	public function getAdditionalHeadlines() {
		return '
			<td class="lowestprice">'.ML_GENERIC_LOWEST_PRICE.'</td>
			<td class="lowestprice">'.ML_AMAZON_LABEL_PREPARE_KIND.'</td>';
	}

	public function getAdditionalCategoryInfo($cID, $data = false) {
		return '
			<td>&mdash;</td>
			<td>&mdash;</td>';
	}

	public function getAdditionalProductInfo($pID, $data = false) {
		$html = '';
		if (getDBConfigValue('general.keytype', '0') == 'artNr') {
			$matchRow = MagnaDB::gi()->fetchRow('
				SELECT asin, lowestprice 
				  FROM '.TABLE_MAGNA_AMAZON_PROPERTIES.' 
				 WHERE products_model=\''.MagnaDB::gi()->escape($data['products_model']).'\' AND
				       mpID=\''.$this->_magnasession['mpID'].'\' AND
				       asin<>\'\'
			');
		} else {
			$matchRow = MagnaDB::gi()->fetchRow('
				SELECT asin, lowestprice 
				  FROM '.TABLE_MAGNA_AMAZON_PROPERTIES.'
				 WHERE products_id=\''.$pID.'\' AND
				       mpID=\''.$this->_magnasession['mpID'].'\' AND
				       asin<>\'\'
			');
		}
		if (is_array($matchRow)) {
			$data['lowestprice'] = $matchRow['lowestprice'];
		}
		return '
			<td>'.((isset($data['lowestprice']) && !empty($data['lowestprice']) && ($data['lowestprice'] > 0)) 
				? $this->simplePrice->setPrice($data['lowestprice'])->format()
				: '&mdash;'
			).'<br />&nbsp;</td>
			<td>'.(is_array($matchRow) ? ML_AMAZON_LABEL_PREPARE_IS_MATCHED : ML_AMAZON_LABEL_PREPARE_IS_APPLIED).'</td>';
	}
	
	protected function getEmptyInfoText() {
		if (empty($this->search)) {
			return ML_AMAZON_TEXT_NO_MATCHED_PRODUCTS;
		} else {
			return parent::getEmptyInfoText();
		}
	}
	
}
