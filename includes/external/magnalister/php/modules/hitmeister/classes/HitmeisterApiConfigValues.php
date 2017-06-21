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
 * (c) 2011 - 2013 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/classes/MagnaCompatibleApiConfigValues.php');

class HitmeisterApiConfigValues extends MagnaCompatibleApiConfigValues {
	protected static $instance = null;
	
	public static function gi() {
		// get_called_class() would be needed to kill that method and use the parent one.
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function getShippingTypes() {
		$data = $this->fetchDataFromApi('GetShippingTypes');
		return $data;
	}
	
	public function getAvailableVariantConfigurations() {
		$data = $this->fetchDataFromApi('GetAvailableVariantConfigurations');
		return $data;
	}
	
	public function getVariantConfigurationDefinition($which) {
		$data = $this->fetchDataFromApi('GetCategoryDetails', array (
			'DATA' => array (
				'CategoryID' => $which,
			)
		));
		return $data;
	}
}
