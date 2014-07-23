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

require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/productIdFilter/AbstractProductIdFilter.php');

class ManufacturerFilter extends AbstractProductIdFilter {
	/**
	 * already setted product-ids
	 * @var array
	 */
	protected $pIds = null;
	protected $aConfig = array();
	
	protected $mpId = 0;
	protected $marketplace = '';
	
	protected $disallowedManufacturers = array();
	
	public function __construct() {
		
	}
	
	public function init($aConfig) {
		$this->aConfig = $aConfig;
		$this->mpId = $this->aConfig['MpId'];
		$this->marketplace = $this->aConfig['Marketplace'];
		
		$this->disallowedManufacturers = getDBConfigValue($this->marketplace.'.filter.manufacturer', $this->mpId, array());
		return $this;
	}
	
	public function isActive() {
		return !empty($this->disallowedManufacturers);
	}
	
	public function setCurrentIds($pIds) {
		$this->pIds = $pIds;
		return $this;
	}
	
	public function getHtml() {
		return '';
	}
	
	public function getUrlParams() {
		return array();
	}

	public function getProductIds() {
		// won't be executed if $this->isActive() returns false.
		$this->pIds = MagnaDB::gi()->fetchArray('
			SELECT DISTINCT p.products_id
			  FROM '.TABLE_PRODUCTS.' p
			 WHERE p.manufacturers_id NOT IN('.implode(', ', $this->disallowedManufacturers).')
		', true);

		// Makes simple filters perform worse
		//        '.(($this->pIds === null) ? '' : ' AND p.products_id IN("'.implode('", "', $this->pIds).'")').'

		$this->pIds = empty($this->pIds) ? array() : $this->pIds;
		return $this->pIds;
	}

}
