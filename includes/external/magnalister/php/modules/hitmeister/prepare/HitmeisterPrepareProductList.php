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
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/MLProductList.php');

class HitmeisterPrepareProductList extends MLProductList {
	protected $aPrepareData = array();
	
	public function __construct() {
		$this->aListConfig[] = array(
			'head' => array(
				'attributes'    => 'class="lowestprice"',
				'content'       => 'ML_HITMEISTER_LABEL_HITMEISTER_PRICE_SHORT',
			),
			'field' => array(
				'hitmeisterprice'
			)
		);
		$this->aListConfig[] = array(
			'head' => array(
				'attributes' => 'class="lowestprice"',
				'content' => 'ML_MAGNACOMPAT_LABEL_CATEGORY'
			),
			'field' => array(
				'magnacompatmpcategory'
			)
		);
		$this->aListConfig[] = array(
			'head' => array(
				'attributes' => 'class="matched"',
				'content' => 'ML_MAGNACOMPAT_LABEL_PREPARED'
			),
			'field' => array(
				'preparestatusindicator'
			)
		);
		
		parent::__construct();
		
		$this
			->addDependency('MLProductListDependencyHitmeisterPrepareFormAction', array('selectionname' => $this->getSelectionName()))
			->addDependency('MLProductListDependencyHitmeisterPrepareStatusFilter');
	}
	
	protected function getSelectionName() {
		return 'prepare';
	}
	
	protected function getPreparedStatusIndicator($aRow) {
		$aData = $this->getPrepareData($aRow);
		if ($aData !== false) {
			if ($aData['mp_category_id'] != '') {
				return html_image(DIR_MAGNALISTER_WS_IMAGES.'status/green_dot.png', ML_MAGNACOMPAT_LABEL_CATMATCH_PREPARE_COMPLETE, 9, 9);
			} else {
				return html_image(DIR_MAGNALISTER_WS_IMAGES.'status/red_dot.png', ML_MAGNACOMPAT_LABEL_CATMATCH_PREPARE_INCOMPLETE, 9, 9);
			}
		}
		return html_image(DIR_MAGNALISTER_WS_IMAGES.'status/grey_dot.png', ML_MAGNACOMPAT_LABEL_CATMATCH_NOT_PREPARED, 9, 9);
	}
	

	protected function getPrepareData($aRow, $sFieldName = null) {
		if (!isset($this->aPrepareData[$aRow['products_id']])) {
			$this->aPrepareData[$aRow['products_id']] = MagnaDB::gi()->fetchRow("
				SELECT * 
				  FROM ".TABLE_MAGNA_HITMEISTER_PREPARE." 
				 WHERE ".((getDBConfigValue('general.keytype', '0') == 'artNr') 
					? 'products_model=\''.MagnaDB::gi()->escape($aRow['products_model']).'\'' 
					: 'products_id=\''.$aRow['products_id'].'\''
				)."
				        AND mpID = '".$this->aMagnaSession['mpID']."'
			");
		}
		if ($sFieldName === null) {
			return $this->aPrepareData[$aRow['products_id']];
		} else {
			return isset($this->aPrepareData[$aRow['products_id']][$sFieldName]) ? $this->aPrepareData[$aRow['products_id']][$sFieldName] : null;
		}
	}

	/**
	 * adding propertiestable for filter
	 */
	protected function buildQuery() {
		$oQueryBuilder = parent::buildQuery()->oQuery;
		$oQueryBuilder->where("p.products_ean IS NOT NULL AND products_ean <> '' AND products_ean <> '0'");
		return $this;
	}
	
}
