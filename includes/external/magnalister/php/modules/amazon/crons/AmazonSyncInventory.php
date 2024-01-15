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
 * (c) 2010 - 2019 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleSyncInventory.php');
require_once(DIR_MAGNALISTER_MODULES.'amazon/amazonFunctions.php');

class AmazonSyncInventory extends MagnaCompatibleSyncInventory {

    private $aBusinessPriceConfig = array();

    protected function initSync() {
        parent::initSync();

        $this->aBusinessPriceConfig = $this->simplePrice->loadPriceSettings($this->mpID, 'b2b.');
        if (getDBConfigValue(array('amazon.b2b.price.use.netto', 'val'), $this->mpID, false) == true) {
            $this->aBusinessPriceConfig['IncludeTax'] = false;
        }
    }

	protected function initMLProduct() {
		parent::initMLProduct();
		MLProduct::gi()->setOptions(array(
			'sameVariationsToAttributes' => false,
			'useGambioProperties' => (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties'),
		));
	}

    /**
     * Overwrite the generic updatePrice function so it returns always false
     *      But the parent function is used in updateCustomFields()
     *
     * @return bool|float
     */
    protected function updatePrice() {
        return false;
    }

    protected function updateCustomFields(&$data) {
        $businessPrice = $this->updateBusinessPrice();

        if ($businessPrice) {
            $data['BusinessPrice'] = $businessPrice;

            // the price needs to be submitted if its not only B2B otherwise it will be after only B2B on Amazon
            if (!array_key_exists('Price', $data) && ((float)$this->cItem['Price'] !== 0.0 && $this->cItem['Price'] !== null)) {
                $data['Price'] = parent::updatePrice();

                // if updatePrice() === false - so price is not changed use price from inventory response
                if ($data['Price'] === false) {
                    $data['Price'] = (float)$this->cItem['Price'];
                }
            }
        } elseif ($this->cItem['Price'] !== null && (float)$this->cItem['Price'] !== 0.0) {
            // if b2b price is not changed we need to check also for default price
            $price = parent::updatePrice();

            // if updatePrice() !== false - so price is changed
            if ($price !== false) {
                $data['Price'] = $price;

                // so b2b price was not changed so use data from inventory response
                if (array_key_exists('BusinessPrice', $this->cItem)) {
                    $data['BusinessPrice'] = $this->cItem['BusinessPrice'];
                }
            }
        }

        // If we don't get Price from API the item is B2B only!
        if ($this->cItem['Price'] === null) {
            unset($data['Price']);
        }

		if (empty($data)) {
			return;
		}

        $data['LeadtimeToShip'] = amazonGetLeadtimeToShip($this->mpID, $this->cItem['pID']);
	}

    /**
     * We need to return false if no business price is provided by API but we need to always return business price when provided
     *
     * @return bool|float
     */
    protected function updateBusinessPrice() {
        if (!$this->syncPrice || !isset($this->cItem['BusinessPrice'])) {
            return false;
        }

        $data = false;

        $price = $this->simplePrice
            ->setPriceFromDB($this->cItem['pID'], $this->mpID, $this->aBusinessPriceConfig)
            ->addAttributeSurcharge($this->cItem['aID'])
            ->finalizePrice($this->cItem['pID'], $this->mpID, $this->aBusinessPriceConfig)
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
