<?php
/*
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
 * (c) 2010 - 2021 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

require_once(DIR_MAGNALISTER_MODULES . 'otto/classes/MLProductListOttoAbstract.php');

class OttoCheckinProductList extends MLProductListOttoAbstract{
	
	protected function getSelectionName() {
		return 'checkin';
	}
	public function __construct() {
		// Shop Stock | Stock for Otto | Shop Price | Price for Otto | (MP Category)
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
				'content' => 'ML_OTTO_STOCK_FOR_OTTO',
			),
			'field' => array('quantityforotto'), // oder: quantityformp  und zentral machen?
		);
		$this->aListConfig[] = $aShopPriceColumn;
		$this->aListConfig[] = array(
			'head' => array(
				'attributes' => 'class="lowestprice"',
				'content' => 'ML_OTTO_PRICE_FOR_OTTO',
			),
			'field' => array('priceforotto'),
		);
		parent::__construct();
		$this
			->addDependency('MLProductListDependencyCheckinToSummaryAction')
			->addDependency('MLProductListDependencyTemplateSelectionAction')
			->addDependency('MLProductListDependencyLastPreparedFilter', array(
				'propertiestablename' => TABLE_MAGNA_OTTO_PREPARE,
				'propertiestablealias' => 'mcc',
				'preparedtimestampfield' => 'PreparedTS',
			))
		;
	}
	
	/**
	 * adding propertiestable for filter
	 */
	protected function buildQuery(){
		parent::buildQuery()->oQuery->join(
			array (
				TABLE_MAGNA_OTTO_PREPARE,
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

	protected function getPrimaryCategory($aRow) {
		// TODO Pfad zurueckgeben
		return $this->getPrepareData($aRow, 'Primarycategory');
	}

}
