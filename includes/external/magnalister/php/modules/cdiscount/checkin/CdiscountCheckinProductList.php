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
 * $Id:$
 *
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

require_once(DIR_MAGNALISTER_MODULES . 'magnacompatible/classes/MLProductListMagnaCompatibleAbstract.php');

class CdiscountCheckinProductList extends MLProductListMagnaCompatibleAbstract{
	
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
		$this->aListConfig[] = array(
			'head' => array(
				'attributes' => 'class="lowestprice"',
				'content' => 'ML_LABEL_DATA_PREPARED',
			),
			'field' => array('cdiscountpreparetype'),
		);
		parent::__construct();
		$this
			->addDependency('MLProductListDependencyCheckinToSummaryAction')
			->addDependency('MLProductListDependencyTemplateSelectionAction')
			->addDependency('MLProductListDependencyLastPreparedFilter', array(
				'propertiestablename' => TABLE_MAGNA_CDISCOUNT_PREPARE,
				'propertiestablealias' => 'hp', 
				'preparedtimestampfield' => 'PreparedTS',
			))
		;
	}
	
	/**
	 * adding propertiestable for filter
	 */
	protected function buildQuery(){
		parent::buildQuery()->oQuery
			->join(
				array(
					TABLE_MAGNA_CDISCOUNT_PREPARE,
						'hp',
						(
							(getDBConfigValue('general.keytype', '0') == 'artNr')
								? 'p.products_model = hp.products_model'
								: 'p.products_id = hp.products_id'
						).
						" AND hp.mpID = '".$this->aMagnaSession['mpID']."'
						AND Verified = 'OK' "
				),
				ML_Database_Model_Query_Select::JOIN_TYPE_INNER
			)
//			->where("p.products_ean IS NOT NULL AND p.products_ean <> ''")
		;
		return $this;
	}
	
	protected function getPrepareData($aRow, $sFieldName = null) {
		if (!isset($this->aPrepareData[$aRow['products_id']])) {
			$this->aPrepareData[$aRow['products_id']] = MagnaDB::gi()->fetchRow("
				SELECT * 
				FROM ".TABLE_MAGNA_CDISCOUNT_PREPARE."
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
							<td class="label">' . ML_LABEL_CATEGORY . ':&nbsp;</td>
							<td>'.(empty($aData['MarketplaceCategories']) ? '&mdash;' : $aData['MarketplaceCategories']).(empty($aData['MarketplaceCategoriesName']) ? '' : ' '.$aData['MarketplaceCategoriesName']).'</td>
						<tr>
					</tbody></table>
				</td><tr>
			</tbody></table>';
		}
		return '&mdash;';
	}

	protected function getPrepareType($aRow){
		return $this->getPrepareData($aRow, 'PrepareType') == 'Apply' ? ML_AMAZON_LABEL_PREPARE_IS_APPLIED : ML_AMAZON_LABEL_PREPARE_IS_MATCHED;
	}

	protected function isPreparedDifferently($aRow) {
		$sPrimaryCategory = $this->getPrepareData($aRow, 'PrimaryCategory');
		if (!empty($sPrimaryCategory)) {
			$sCategoryDetails = $this->getPrepareData($aRow, 'CategoryAttributes');
			$categoryMatching = CdiscountHelper::gi()->getCategoryMatching($sPrimaryCategory);
			$sCategoryDetails = json_decode($sCategoryDetails, true);
			return CdiscountHelper::gi()->detectChanges($categoryMatching, $sCategoryDetails);
		}

		return false;
	}

	protected function isDeletedAttributeFromShop($aRow, &$message) {
	    $aPrimaryCategories = $this->getPrepareData($aRow, 'PrimaryCategory');
		if (!empty($aPrimaryCategories)) {
			$matchedAttributes = $this->getPrepareData($aRow, 'CategoryAttributes');
			$matchedAttributes = json_decode($matchedAttributes, true);
			$shopAttributes = CdiscountHelper::gi()->flatShopVariations();

            if (!is_array($matchedAttributes)) {
                $matchedAttributes = array();
            }

            foreach ($matchedAttributes as $matchedAttribute) {
				if (CdiscountHelper::gi()->detectIfAttributeIsDeletedOnShop($shopAttributes, $matchedAttribute, $message)) {
					return true;
				}
			}
		}

		return false;
	}
}
