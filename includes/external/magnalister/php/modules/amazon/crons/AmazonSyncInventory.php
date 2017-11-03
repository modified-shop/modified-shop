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
 * (c) 2010 - 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleSyncInventory.php');
require_once(DIR_MAGNALISTER_MODULES.'amazon/amazonFunctions.php');

class AmazonSyncInventory extends MagnaCompatibleSyncInventory {

	protected function initMLProduct() {
		parent::initMLProduct();
		MLProduct::gi()->setOptions(array(
			'sameVariationsToAttributes' => false,
			'useGambioProperties' => (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties'),
		));
	}

	protected function updateCustomFields(&$data) {
		if (empty($data)) {
			return;
		}
		$timeToShip = (int)amazonGetLeadtimeToShip($this->mpID, $this->cItem['pID']);
		if ($timeToShip > 0) {
			$data['LeadtimeToShip'] = $timeToShip;
		}

		$businessPrice = $this->updateBusinessPrice();
		if ($businessPrice) {
			$data['BusinessPrice'] = $businessPrice;
		}
	}

	protected function updateBusinessPrice() {
		if (!$this->syncPrice || !isset($this->cItem['BusinessPrice'])) {
			return false;
		}

		$data = false;

		$price = $this->simplePrice
			->setFinalPriceFromDB($this->cItem['pID'], $this->mpID, 'b2b.')
			->getPrice();

		if (($price > 0) && ((float)$this->cItem['BusinessPrice'] != $price)) {
			$this->log("\n\t".
				'Business Price changed (old: ' . $this->cItem['BusinessPrice'] . '; new: ' . $price . ')'
			);
			$data = $price;
		} else {
			$this->log("\n\t" .
				'Business Price not changed (' . $price . ')'
			);
		}

		return $data;
	}

}
