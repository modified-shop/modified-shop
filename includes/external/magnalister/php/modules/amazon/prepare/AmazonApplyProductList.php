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
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

require_once(DIR_MAGNALISTER_MODULES.'amazon/classes/MLProductListAmazonAbstract.php');

class AmazonApplyProductList extends MLProductListAmazonAbstract {
	
	public function __construct() {
		$this->aListConfig[] = array(
			'head' => array(
				'attributes' => 'class="matched"',
				'content' => 'ML_LABEL_DATA_PREPARED',
			),
			'field' => array('preparestatusindicator'),
		);
		parent::__construct();
		$this
			->addDependency('MLProductListDependencyAmazonApplyFormAction', array('selectionname' => $this->getSelectionName()))
			->addDependency('MLProductListDependencyAmazonApplyPrepareStatusFilter')
		;
	}

	/**
	 * removing items which are in propertiestable
	 */
	protected function buildQuery(){
		$sKeyType = (getDBConfigValue('general.keytype', '0') == 'artNr') ? 'products_model' : 'products_id';
		$aAmazonProperties = MagnaDB::gi()->fetchArray("
			SELECT DISTINCT ".$sKeyType."
			           FROM ".TABLE_MAGNA_AMAZON_PROPERTIES."
			          WHERE     mpID = '".$this->aMagnaSession['mpID']."'
			                AND `asin` IS NOT NULL
			                AND `asin` <>''
		", true);

		parent::buildQuery()->oQuery->where(
			"p.".$sKeyType." NOT IN ('".implode('\' , \'', $aAmazonProperties)."')
		");
		return $this;
	}
	protected function getSelectionName() {
		return 'apply';
	}
	
}