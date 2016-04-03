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
require_once(DIR_MAGNALISTER_MODULES.'hood/classes/MLProductListHoodAbstract.php');

class HoodCheckinProductList extends MLProductListHoodAbstract {
	
	public function __construct() {
		$this->aListConfig[] = array(
			'head' => array(
				'attributes'	=> 'class="lowestprice"',
				'content'		=> 'ML_HOOD_LABEL_HOOD_PRICE',
			),
			'field' => array('hoodprice'),
		);
		$this->aListConfig[] = array(
			'head' => array(
				'attributes'	=> 'class="lowestprice"',
				'content'		=> 'ML_HOOD_LISTING_TYPE',
			),
			'field' => array('hoodlistingtype'),
		);
		$this->aListConfig[] = array(
			'head' => array(
				'attributes'	=> 'class="lowestprice"',
				'content'		=> 'ML_HOOD_DURATION',
			),
			'field' => array('hoodduration'),
		);
		parent::__construct();
		$this
			->addDependency('MLProductListDependencyCheckinToSummaryAction')
			->addDependency('MLProductListDependencyMarketplaceSync', array('propertiestablename' => $this->isAjax() ? TABLE_MAGNA_HOOD_PROPERTIES :'hp.')) //table will be joined in $this->buildQuery()
			->addDependency('MLProductListDependencyTemplateSelectionAction')
			->addDependency('MLProductListDependencyLastPreparedFilter', array(
				'propertiestablename' => TABLE_MAGNA_HOOD_PROPERTIES, 
				'propertiestablealias' => 'hp', 
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
				TABLE_MAGNA_HOOD_PROPERTIES,
					'hp',
					(
						(getDBConfigValue('general.keytype', '0') == 'artNr')
							? 'p.products_model=hp.products_model'
							: 'p.products_id=hp.products_id'
					).
					"
						AND hp.mpID = '".$this->aMagnaSession['mpID']."'
						AND hp.Verified ='OK'
					"
				),
				ML_Database_Model_Query_Select::JOIN_TYPE_INNER
			);
		return $this;
	}
	
	protected function getSelectionName() {
		return 'checkin';
	}
	
	protected function getHoodListingType ($aRow) {
		$sListingType = $this->getPrepareData($aRow, 'ListingType');
		$sListingDefine = 'ML_HOOD_LISTINGTYPE_'.strtoupper($sListingType);
		return (defined($sListingDefine) ? constant($sListingDefine) : $sListingType);
	}
	
	protected function getHoodDuration ($aRow) { 
		if($this->getPrepareData($aRow, 'ListingType')== 'shopProduct' ){
			return ML_LABEL_UNLIMITED;
		}else{
			$sDuration = $this->getPrepareData($aRow, 'ListingDuration');
			$sDurationDefine = 'ML_HOOD_LABEL_LISTINGDURATION_'.strtoupper($sDuration);
			return (defined($sDurationDefine) ? constant($sDurationDefine) : $sDuration);
		}
	}
	
}
