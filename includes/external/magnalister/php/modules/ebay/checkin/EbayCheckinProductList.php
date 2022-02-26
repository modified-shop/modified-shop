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
require_once(DIR_MAGNALISTER_MODULES.'ebay/classes/MLProductListEbayAbstract.php');

class EbayCheckinProductList extends MLProductListEbayAbstract{
	
	protected function getSelectionName() {
		return 'checkin';
	}
	public function __construct() {
		$this->aListConfig[] = array(
			'head' => array(
				'attributes'	=> 'class="lowestprice"',
				'content'		=> 'ML_EBAY_LABEL_EBAY_PRICE',
			),
			'field' => array('ebayprice'),
		);
		/*$this->aListConfig[] = array(
			'head' => array(
				'attributes'	=> 'class="lowestprice"',
				'content'		=> 'ML_EBAY_LABEL_PREPARE_KIND',
			),
			'field' => array('ebaypreparetype'),
		);*/
		$this->aListConfig[] = array(
			'head' => array(
				'attributes'	=> 'class="lowestprice"',
				'content'		=> 'ML_EBAY_LISTING_TYPE',
			),
			'field' => array('ebaylistingtype'),
		);
		$this->aListConfig[] = array(
			'head' => array(
				'attributes'	=> 'class="lowestprice"',
				'content'		=> 'ML_EBAY_DURATION',
			),
			'field' => array('ebayduration'),
		);
		parent::__construct();
		$this
			->addDependency('MLProductListDependencyCheckinToSummaryAction')
			->addDependency('MLProductListDependencyEbayMarketplaceSync', array('propertiestablename' => $this->isAjax() ? TABLE_MAGNA_EBAY_PROPERTIES :'ep.')) //table will be joined in $this->buildQuery()
			->addDependency('MLProductListDependencyTemplateSelectionAction')
			->addDependency('MLProductListDependencyLastPreparedFilter', array(
				'propertiestablename' => TABLE_MAGNA_EBAY_PROPERTIES, 
				'propertiestablealias' => 'ep', 
				'preparedtimestampfield' => 'PreparedTS',
			))
//			->addDependency('MLProductListDependencyManufacturersFilter')// its now in MLProductList as global filter
		;
	}
	
	/**
	 * adding propertiestable for filter
	 */
	protected function buildQuery(){
		parent::buildQuery()->oQuery->join(
			array(
				TABLE_MAGNA_EBAY_PROPERTIES,
					'ep',
					(
						(getDBConfigValue('general.keytype', '0') == 'artNr')
							? 'p.products_model = ep.products_model'
							: 'p.products_id = ep.products_id'
					).
					"
						AND ep.mpID = '".$this->aMagnaSession['mpID']."'
						AND ep.Verified ='OK'
					"
				),
				ML_Database_Model_Query_Select::JOIN_TYPE_INNER
			);
		return $this;
	}
}
