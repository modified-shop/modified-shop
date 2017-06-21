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

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleSyncInventory.php');

class CrowdfoxSyncInventory extends MagnaCompatibleSyncInventory {
	public function __construct($mpID, $marketplace) {
		parent::__construct($mpID, $marketplace);
	}

	protected function calcNewQuantity() {
		if ($this->config['QuantityType'] == 'infinity') {
			return -1;
		}
		return parent::calcNewQuantity();
	}
	
	public function process() {
		parent::process();
	}

    protected function syncInventory() {
        $this->initQuantitySub();

        $request = $this->getBaseRequest();
        $request['ACTION'] = 'GetInventory';
        $request['MODE'] = 'SyncInventory';
        if (isset($_GET['SEARCH']) && !empty($_GET['SEARCH'])) {
            $request['SEARCH'] = $_GET['SEARCH'];
        }
        $this->extendGetInventoryRequest($request);

        do {
            $request['LIMIT'] = $this->limit;
            $request['OFFSET'] = $this->offset;

            $this->log("\n\nFetch Inventory: ");
            MagnaConnector::gi()->setTimeOutInSeconds($this->timeouts['GetInventory']);
            try {
                $result = MagnaConnector::gi()->submitRequest($request);
            } catch (MagnaException $e) {
                $this->logException($e, $this->_debugLevel >= self::DBGLV_HIGH);
                return false;
            }
            $this->log(
                'Received '.count($result['DATA']).' items '.
                '('.($this->offset + count($result['DATA'])).' of '.$result['NUMBEROFLISTINGS'].') '.
                'in '.microtime2human($result['Client']['Time'])."\n"
            );
            if (!empty($result['DATA'])) {
                $this->stockBatch = array();

                foreach ($result['DATA'] as $item){
                    $this->cItem = $item;
                    @set_time_limit(180);
                    $this->updateItem();
                    //return;
                }

                $this->submitStockBatch();
            }
            // Marker for continue requests from the API
            // If Synchro not completed, API takes the last marker arrived,
            // and uses the data for a continue request
            // Always send this, no matter if MLDEBUG is on.
            $this->dataOut(array (
                'Marketplace' => $this->marketplace,
                'MPID'  => $this->mpID,
                'Done'  => (int)($this->offset + count($result['DATA'])),
                'Step' => $this->steps,
                'Total' => $result['NUMBEROFLISTINGS'],
            ));
            $this->offset += $this->limit;

            if (($this->steps !== false) && ($this->offset < $result['NUMBEROFLISTINGS'])) {
                if ($this->steps <= 1) {
                    // Abort sync. Will be continued though another callback request.
                    return true;
                } else {
                    --$this->steps;
                }
            }
            #echo 'Step: '.$this->steps."\n";

        } while ($this->offset <= $result['NUMBEROFLISTINGS']);
        // Marker for completed operation, so that no continue request is made
        $this->dataOut(array (
            'Complete' => 'true',
        ));

        return true;
    }
}
