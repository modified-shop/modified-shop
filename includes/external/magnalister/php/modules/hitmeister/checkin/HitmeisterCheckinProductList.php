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

require_once(DIR_MAGNALISTER_MODULES . 'magnacompatible/classes/MLProductListMagnaCompatibleAbstract.php');

class HitmeisterCheckinProductList extends MLProductListMagnaCompatibleAbstract{
	
	protected function getSelectionName() {
		return 'checkin';
	}
	public function __construct() {
		$this->aListConfig[] = array(
			'head' => array(
				'attributes' => 'class="lowestprice"',
				'content' => 'ML_MAGNACOMPAT_LABEL_CATEGORY',
			),
			'field' => array('magnacompatmpcategory'),
		);
		parent::__construct();
		$this
			->addDependency('MLProductListDependencyCheckinToSummaryAction')
			->addDependency('MLProductListDependencyTemplateSelectionAction')
		;
	}
	
	/**
	 * adding propertiestable for filter
	 */
	protected function buildQuery(){
		$allPreparedItems = (array)MagnaDB::gi()->fetchArray('
			SELECT DISTINCT '.((getDBConfigValue('general.keytype', '0') == 'artNr')
					? 'products_model'
					: 'products_id'
				).'
			FROM '.TABLE_MAGNA_HITMEISTER_PREPARE.'
			WHERE mpID=\''.$this->aMagnaSession['mpID'].'\'
		', true);
		$itemsWithEAN = (array)MagnaDB::gi()->fetchArray('
			SELECT DISTINCT '.((getDBConfigValue('general.keytype', '0') == 'artNr')
					? 'products_model'
					: 'products_id'
				).'
			FROM '.TABLE_PRODUCTS.'
			WHERE products_ean IS NOT NULL AND products_ean <> \'\'
		', true);
		$preparedItems = array_intersect($allPreparedItems, $itemsWithEAN);
		$oQueryBuilder = parent::buildQuery()->oQuery;	
		if (getDBConfigValue('general.keytype', '0') == 'artNr') {				
			$oQueryBuilder->where( 'p.products_model IN (\''.implode('\', \'', MagnaDB::gi()->escape($preparedItems)).'\')');				
		} else {
			$oQueryBuilder->where('p.products_id IN (\''.implode('\', \'', $preparedItems).'\')');
		}
		return $this;
	}
	
	protected function getPrepareData($aRow, $sFieldName = null) {
		if (!isset($this->aPrepareData[$aRow['products_id']])) {
			$this->aPrepareData[$aRow['products_id']] = MagnaDB::gi()->fetchRow("
				SELECT * 
				FROM ".TABLE_MAGNA_HITMEISTER_PREPARE." 
				WHERE 
					".(
						(getDBConfigValue('general.keytype', '0') == 'artNr')
							? 'products_model=\''.MagnaDB::gi()->escape($aRow['products_model']).'\''
							: 'products_id=\''.$aRow['products_id'].'\''
					)."
					AND mpID = '".$this->aMagnaSession['mpID']."'
			");
		}
		if($sFieldName === null){
			return $this->aPrepareData[$aRow['products_id']];
		}else{
			return isset($this->aPrepareData[$aRow['products_id']][$sFieldName]) ? $this->aPrepareData[$aRow['products_id']][$sFieldName] : null;
		}
	}
	
	protected function getMarketPlaceCategory($aRow) {
		$aData = $this->getPrepareData($aRow);
		if ($aData !== false) {
			return '<table class="nostyle"><tbody>
				<tr><td>
					<table class="nostyle"><tbody>
						<tr>
							<td class="label">'.'Kategorie'.':&nbsp;</td>
							<td>'.(empty($aData['mp_category_id']) ? '&mdash;' : $aData['mp_category_id']).(empty($aData['mp_category_name']) ? '' : ' '.$aData['mp_category_name']).'</td>
						<tr>
					</tbody></table>
				</td><tr>
			</tbody></table>';
		}
		return '&mdash;';
	}
}
