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
 * (c) 2010 - 2023 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');


require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleSyncOrderStatus.php');

class HitmeisterSyncOrderStatus extends MagnaCompatibleSyncOrderStatus {

    /**
     * Overwrite confirmationsResponseField
     */
    public function __construct($mpID, $marketplace) {
        parent::__construct($mpID, $marketplace);
        $this->confirmationResponseField = 'CONFIRMATIONS';
    }

    /**
     * CarrierDefault is named differently for Hitmeister
     */
    protected function getConfigKeys() {
        $aReturn = parent::getConfigKeys();

        $aReturn['CarrierDefault'] = array (
            'key' => 'orderstatus.carrier',
            'default' => false,
        );
        return $aReturn;
    }

    /**
     * Overwrite to set __dirty to false,
     * so only when marketplace responded the confirmations the sync was successful
     *
     * @param $date
     * @return void
     */
    protected function prepareSingleOrder($date) {
        parent::prepareSingleOrder($date);

        // this marketplace returns orders if successful
        $this->oOrder['__dirty'] = false;
    }

    /**
     * Decides if the order will be processed.
     *
     * Stop processing fulfilled_by_kaufland orders.
     *
     * @return bool
     */
    protected function isProcessable() {
        $data = is_string($this->oOrder['data'])
            ? @unserialize($this->oOrder['data'])
            : $this->oOrder['data'];

        $fulfillment_check = true;
        if (is_array($data) &&
            array_key_exists('FulfillmentType', $data)
        ) {
            $fulfillment_check = 'fulfilled_by_kaufland' != $data['FulfillmentType'];
        }

        return parent::isProcessable() && $fulfillment_check;
    }

    /**
	 * Builds an element for the CancelShipment request
	 * @return array
	 */
	protected function cancelOrder($date) {
		$cncl = array (
			'MOrderID' => $this->oOrder['special'],
			'Reason' => getDBConfigValue($this->marketplace . '.orderstatus.cancelreason', $this->mpID)
		);

		$this->oOrder['data']['ML_LABEL_ORDER_CANCELLED'] = $date;
		// flag order as dirty, meaning that it has to be saved.
		$this->oOrder['__dirty'] = true;
		return $cncl;
	}

	
	/**
	 * Adds an error to the hitmeister error log.
	 * 
	 * @param array $error
	 *   The entry for the error log.
	 * @return void
	 */
	protected function addToErrorLog($error) {
		$add = $error['DETAILS'];
		unset($add['ErrorCode']);
		unset($add['ErrorMessage']);
		$add['Action'] = $error['APIACTION'];
		
		MagnaDB::gi()->insert(
			TABLE_MAGNA_COMPAT_ERRORLOG,
			array (
				'mpID' => $this->mpID,
				'errormessage' => $error['ERRORMESSAGE'],
				'dateadded' => date('Y-m-d H:i:s'),
				'additionaldata' => serialize($add)
			)
		);
	}
	
}
