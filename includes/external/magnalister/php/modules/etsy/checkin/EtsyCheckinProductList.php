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
 * $Id$
 *
 * (c) 2010 - 2019 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

require_once(DIR_MAGNALISTER_MODULES . 'etsy/classes/MLProductListEtsyAbstract.php');

class EtsyCheckinProductList extends MLProductListEtsyAbstract{
	
	protected function getSelectionName() {
		return 'checkin';
	}
	public function __construct() {
		// Shop Stock | Stock for Etsy | Shop Price | Price for Etsy | (MP Category)
		$aShopPriceColumn = array_pop($this->aListConfig); // insert later (other column order)
		$this->aListConfig[] = array(
			'head' => array(
				'attributes' => 'class="lowestprice"',
				'content' => 'ML_LABEL_SHOP_QUANTITY',
			),
			'field' => array('shopquantity'),
		);
		$this->aListConfig[] = array(
			'head' => array(
				'attributes' => 'class="lowestprice"',
				'content' => 'ML_ETSY_STOCK_FOR_ETSY',
			),
			'field' => array('quantityforetsy'), // oder: quantityformp  und zentral machen?
		);
		$this->aListConfig[] = $aShopPriceColumn;
		$this->aListConfig[] = array(
			'head' => array(
				'attributes' => 'class="lowestprice"',
				'content' => 'ML_ETSY_PRICE_FOR_ETSY',
			),
			'field' => array('priceforetsy'),
		);
		/*$this->aListConfig[] = array(
			'head' => array(
				'attributes' => 'class="lowestprice"',
				'content' => 'ML_MAGNACOMPAT_LABEL_CATEGORY',
			),
			'field' => array('primarycategory'), // hier muss Kategoriepfad rein
		);*/
		parent::__construct();
		$this
			->addDependency('MLProductListDependencyCheckinToSummaryAction')
			->addDependency('MLProductListDependencyTemplateSelectionAction')
			->addDependency('MLProductListDependencyLastPreparedFilter', array(
				'propertiestablename' => TABLE_MAGNA_ETSY_PREPARE,
				'propertiestablealias' => 'mcc',
				'preparedtimestampfield' => 'PreparedTS',
			))
		;
		removeDoublePrepareEntries(TABLE_MAGNA_ETSY_PREPARE, $this->aMagnaSession['mpID']);
	}
	
	/**
	 * adding propertiestable for filter
	 */
	protected function buildQuery(){
		parent::buildQuery()->oQuery->join(
			array (
				TABLE_MAGNA_ETSY_PREPARE,
				'mcc',
				(
					(getDBConfigValue('general.keytype', '0') == 'artNr')
						? 'p.products_model=mcc.products_model'
						: 'p.products_id=mcc.products_id'
				)."
					AND mcc.mpID = '".$this->aMagnaSession['mpID']."'
					AND mcc.Primarycategory<>''
				"
			),
			ML_Database_Model_Query_Select::JOIN_TYPE_INNER
		);
		return $this;
	}

	protected function getQuantity($aRow) {
		// nice-to-have: Variation quantity (when we have a function for it)
		return (int)$aRow['products_quantity'];
	}

	/*protected function getQuantityForEtsy($aRow) {
		// nice-to-have: Variation quantity (when we have a function for it)
		$sCalcMethod    = getDBConfigValue('etsy.quantity.type',  $this->aMagnaSession['mpID']);
		$iQuantityValue = getDBConfigValue('etsy.quantity.value', $this->aMagnaSession['mpID']);
		$iMaxQuantity   = getDBConfigValue('etsy.maxquantity', $this->aMagnaSession['mpID'], 0);
		switch ($sCalcMethod) {
			case ('stocksub'): {
				$iQuantity = (int)($aRow['products_quantity'] - $iQuantityValue);
				break;
			}
			case ('lump'): {
				// here, maxquantity is not relevant
				return (int)$iQuantityValue;
				break;
			}
			case ('stock'):
			default: {
				$iQuantity = (int)$aRow['products_quantity'];
				break;
			}
		}
		if (empty($iMaxQuantity)) return $iQuantity;
		else return min($iQuantity, $iMaxQuantity);
	}*/

	protected function getPrimaryCategory($aRow) {
		// TODO Pfad zurueckgeben
		return $this->getPrepareData($aRow, 'Primarycategory');
	}
}
