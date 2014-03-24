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
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/checkin/MagnaCompatibleCheckinSubmit.php');

class LaaryCheckinSubmit extends MagnaCompatibleCheckinSubmit {
	private $regions = array();

	public function __construct($settings = array()) {
		parent::__construct($settings);
		$this->region = getDBConfigValue($this->marketplace.'.checkin.region', $this->mpID, false);
		if (is_array($this->region) && !empty($this->region)) {
			$totalB = false;
			foreach ($this->region as $b) {
				$totalB = $totalB || $b;
			}
			if (!$totalB) {
				$this->region = false;
			}
		} else {
			$this->region = false;
		}
		if ($this->region === false) {
			MagnaDB::gi()->insert(
				TABLE_MAGNA_COMPAT_ERRORLOG,
				array (
					'mpID' => $this->mpID,
					'dateadded' => gmdate('Y-m-d H:i:s'),
					'errormessage' => 'constant(ML_LAARY_ERROR_NO_REGION_SELECTED)',
				)
			); 	
		}
	}
	
	protected function appendAdditionalData($pID, $product, &$data) {
		parent::appendAdditionalData($pID, $product, $data);
		$data['submit']['MarketplaceRegion'] = $this->region;
	}
	
	protected function filterSelection() {
		$b = parent::filterSelection();
		if (($this->region !== false) || empty($this->selection)) {
			return $b;
		}
		foreach ($this->selection as $pID => &$data) {
			$this->badItems[] = $pID;
			unset($this->selection[$pID]);
		}
		return true;
	}
}