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

require_once(DIR_MAGNALISTER_MODULES.'ebay/crons/EbaySyncInventory.php');
require_once(DIR_MAGNALISTER_MODULES.'ebay/ebayFunctions.php');


class EbaySyncListingDetails extends EbaySyncInventory {

	protected function getConfigKeys() {
		$aConfig = parent::getConfigKeys();
		$aConfig['SyncListingDetails'] = array (
			'key' => 'listingdetails.sync',
			'default' => 'false',
		);

		return $aConfig;
	}

	protected function isAutoSyncEnabled() {
		if ($this->config['SyncListingDetails'] == 'true') {
			$this->log('== '.$this->marketplace.' ('.$this->mpID.'): sync enabled =='."\n");
			return true;
		}
		$this->log('== '.$this->marketplace.' ('.$this->mpID.'): no autosync =='."\n");
		return false;
	}

	protected function updateItem() {
		if (in_array($this->cItem['ItemID'], $this->itemsProcessed)) {
			$this->log("\nItemID ".$this->cItem['ItemID'].' already processed.');
			return;
		}
		$this->cItem['SKU'] = trim($this->cItem['SKU']);
		if (empty($this->cItem['SKU'])) {
			$this->log("\nItemID ".$this->cItem['ItemID'].' has an emtpy SKU.');
			return;
		}

		@set_time_limit(180);
		$this->identifySKU();

		$articleIdent = 'SKU: '.$this->cItem['SKU'].(empty($this->cItem['MasterSKU']) ? '' : ' [MasterSKU: '.$this->cItem['MasterSKU'].']').' ('.$this->cItem['ItemTitle'].'); eBay-ItemID: '.$this->cItem['ItemID'].'; ListingType: '.$this->cItem['ListingType'].' ';
		if ((int)$this->cItem['pID'] <= 0) {
			$this->log("\n".$articleIdent.' not found');
			return;
		} else {
			$this->log("\n".$articleIdent.' found (pID: '.$this->cItem['pID'].')');
		}

		$aRequestData = EbayHelper::getProductListingDetailsFromProduct($this->cItem['pID'], $this->config['Lang']);
		// variations, if any
		MLProduct::gi()->setLanguage($this->config['Lang']);
		$product =  MLProduct::gi()->getProductById($this->cItem['pID']);
		if (isset($this->cItem['Variations']) && isset($product['Variations'])) {
			$aVariationData = array();
			foreach ($product['Variations'] as $n => $variantData) {
				if (    !array_key_exists('EAN', $variantData)
				     || empty($variantData['EAN'])) {
					continue;
				}
				$aVariationData[$n]['SKU'] = ($this->config['SKUType'] == 'artNr') ? $variantData['MarketplaceSku'] : $variantData['MarketplaceId'];
				$aVariationData[$n]['EAN'] = $variantData['EAN'];
			}
			if (!empty($aVariationData)) {
				$aRequestData['Variations'] = $aVariationData;
			}
		}
		$aRequestData['SKU'] = $this->cItem['SKU'];

		$this->updateProductListingDetails($aRequestData);
	}

	protected function updateProductListingDetails($data) {
		if (!is_array($data) || empty($data)) {
			if ($this->_debug) $this->log("\n\nNothing to update in this batch.");
			return false;
		}
		$request = $this->getBaseRequest();
		$request['ACTION'] = 'UpdateProductListingDetails';
		$request['DATA'] = $data;

		if ($this->_debug) {
			if (!self::isAssociativeArray($request['DATA'])) {
				$this->log("\nUpdating ".count($request['DATA']).' item(s) in this batch.');
			} else {
				$this->log("\nUpdating items.");
			}
		}
		if ($this->_debugLevel >= self::DBGLV_HIGH) $this->logAPIRequest($request);
		if ($this->_debug && $this->_debugDryRun) {
			return true;
		}
		MagnaConnector::gi()->setTimeOutInSeconds($this->timeouts['UpdateItems']);
		try {
			$r = MagnaConnector::gi()->submitRequest($request);
			if ($this->_debug && ($this->_debugLevel >= self::DBGLV_HIGH)) $this->logAPIResponse($r);
			$this->processUpdateItemsErrors($r);

		} catch (MagnaException $e) {
			if ($this->_debugLevel >= self::DBGLV_HIGH) $this->logException($e);
			if ($e->getCode() == MagnaException::TIMEOUT) {
				//$e->saveRequest();
				$e->setCriticalStatus(false);
			}
			return false;
		}
		return true;
	}

}
