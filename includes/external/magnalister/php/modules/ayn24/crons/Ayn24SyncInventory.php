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
require_once(DIR_MAGNALISTER_MODULES.'ayn24/ayn24Functions.php');

class Ayn24SyncInventory extends MagnaCompatibleSyncInventory {

	private $processedProductIds = array(); /* to decide if a variation matrix has to be calculated anew */

	public function __construct($mpID, $marketplace, $limit = 100) {
		parent::__construct($mpID, $marketplace, $limit);
	}
	
	protected function initMLProduct() {
		parent::initMLProduct();
		MLProduct::gi()->setLanguage(getDBConfigValue($this->marketplace.'.lang', $this->mpID, 1))
			->setPriceConfig(Ayn24Helper::loadPriceSettings($this->mpID))
			->setQuantityConfig(Ayn24Helper::loadQuantitySettings($this->mpID))
		;
	}
	
	protected function processUpdateItemsErrors($result) {
		magnaAyn24ProcessCheckinResult($result, $this->mpID);
	}
	
	/* No upload */
	protected function uploadItems() {
		return true;
	}
	
	/**
	 * Checks the quantity only for master or stand alone items
	 *
	 * @return bool
	 *    true if there was a change, false otherwise.
	 */
	protected function checkQuantity(&$batchItem) {
		if (!$this->syncStock) {
			return false;
		}
		
		$curQty = (int)$this->productData['Quantity'];
		
		if (!$this->productData['Status'] && ($this->config['StatusMode'] === 'true')) {
			// Product status is false and the config wants us to ignore the quantity and submit 0.
			$curQty = 0;
		}
		
		if (!isset($this->cItem['Quantity'])) {
			$this->cItem['Quantity'] = 0;
		}
		
		if ((int)$this->cItem['Quantity'] != (int)$curQty) {
			$this->log("\n\t".
				'Quantity changed (old: '.$this->cItem['Quantity'].'; new: '.$curQty.')'
			);
			$batchItem['Quantity'] = $curQty;
			return true;
		} else {
			$this->log("\n\t".
				'Quantity not changed ('.$curQty.')'
			);
		}
		
		return false;
	}
	
	/**
	 * Checks the price only for master or stand alone items
	 *
	 * @return bool
	 *    true if there was a change, false otherwise.
	 */
	protected function checkPrice(&$batchItem) {
		if (!$this->syncPrice) {
			return false;
		}

		$curPrice = ((array_key_exists('PriceReduced', $this->productData) && (float)$this->productData['PriceReduced'] > 0) ? (float)$this->productData['PriceReduced'] : (float)$this->productData['Price']);
		
		if (!isset($this->cItem['Price'])) {
			$this->cItem['Price'] = 0;
		}
		
		if ((float)$this->cItem['Price'] != $curPrice) {
			$this->log("\n\t".
				'Price changed (old: '.$this->cItem['Price'].'; new: '.$curPrice.')'
			);
			$batchItem['Price'] = $curPrice;
			return true;
		} else {
			$this->log("\n\t".
				'Price not changed ('.$curPrice.')'
			);
		}
		
		return false;
	}

	/**
	 * Checks the price and quantity only for master or stand alone items
	 *
	 * @return bool
	 *    true if there was a change, false otherwise.
	 */
	protected function checkQuantityPriceUpdate(&$batchItem) {
		/*
		echo "\n";
		echo print_m($this->cItem, '$this->cItem');
		echo print_m($this->productData, '$this->productData');
		//*/
		
		$cQ = $this->checkQuantity($batchItem);
		$cP = $this->checkPrice($batchItem);

		//Returns true if at least one option is true
		return (($cQ !== false) || ($cP !== false));
	}
	
	protected function fetchProductData() {
		// check if pID already processed & variations already updated
		$calculateVariations = false;
		if (!in_array($this->cItem['pID'], $this->processedProductIds)) {
			$calculateVariations = true;
			$this->processedProductIds[] = $this->cItem['pID'];
		}
		
		$this->productData = MLProduct::gi()->getProductOfferById(
			$this->cItem['pID'], array('purgeVariations' => $calculateVariations)
		);
		#echo print_m($this->productData);
		
		if (empty($this->productData)) {
			return false;
		}
		
		// save us some queries and don't use magnaPID2SKU().
		$masterSku = ($this->config['KeyType'] == 'artNr')
			? $this->productData['ProductsModel']
			: 'ML'.$this->productData['ProductId'];
		
		// Master item
		if ($masterSku == $this->cItem['SKU']) {
			return true;
		}
		
		if (isset($this->productData['Variations'])) {
			$varSku = ($this->config['KeyType'] == 'artNr')
				? 'MarketplaceSku'
				: 'MarketplaceId';
			
			$vItemFound = false;
			foreach ($this->productData['Variations'] as $vItem) {
				if ($vItem[$varSku] != $this->cItem['SKU']) {
					continue;
				}
				$vItemFound = true;
				break;
			}
			
			if (!$vItemFound) {
				// This variation item does not exist anymore.
				return false;
			}
			$this->productData = array_replace($this->productData, $vItem);
		}
		
		return true;
	}
	
	/**
	 * updateItem amazon like, each variation single
	 */
	protected function updateItem() {
		$this->identifySKU();
		
		$articleIdent = 'SKU: '.$this->cItem['SKU'].' ('.
			'Title: '.(!empty($this->cItem['ItemTitle']) ? $this->cItem['ItemTitle'] : '-').'; '.
			'Ayn24ID: '.$this->cItem['Ayn24ID'].
			')';
		
		if ((int)$this->cItem['pID'] <= 0) {
			$this->log("\n".$articleIdent.' not found');
			return;
		}
		
		$this->log("\n".$articleIdent.' found (pId: '.$this->cItem['pID'].')');
		
		if (!$this->fetchProductData()) {
			// unknown product
			return;
		}
		
		$batchItem = array();
		if (!$this->checkQuantityPriceUpdate($batchItem)) {
			// nothing to update
			return;
		}
		#echo print_m($batchItem, '$batchItem');
		$this->addToBatch($batchItem);
		
		return;
	}
	
}
