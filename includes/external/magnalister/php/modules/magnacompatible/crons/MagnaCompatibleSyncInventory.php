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

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleCronBase.php');

abstract class MagnaCompatibleSyncInventory extends MagnaCompatibleCronBase {
	protected $offset = 0;
	protected $limit = 100;
	protected $steps = false;

	/** @var SimplePrice $simplePrice */
	protected $simplePrice = null;
	
	protected $syncStock = false;
	protected $syncPrice = false;
	
	protected $cItem = array();
	
	protected $stockBatch = array();
	
	protected $helperClass = '';

	protected $blMultiDimVariations = false;

	protected $aPidVariationsCalculated = array();
	
	protected $timeouts = array (
		'GetInventory' => 60,
		'UpdateItems' => 5,
		'UploadItems' => 30,
	);
	
	protected $hasDbColumn = array();

	public function __construct($mpID, $marketplace, $limit = 100) {
		parent::__construct($mpID, $marketplace);
		$this->limit = $limit;
		
		$this->initSync();
		$this->initMLProduct();
		
		$this->helperClass = ucfirst($this->marketplace).'Helper';
		$helperPath = DIR_MAGNALISTER_MODULES.strtolower($this->marketplace).'/'.$this->helperClass.'.php';
		if (file_exists($helperPath)) {
			include_once($helperPath);
		}
		//$this->limit = 10;
		
		$this->hasDbColumn['pa.attributes_stock'] = MagnaDB::gi()->columnExistsInTable('attributes_stock', TABLE_PRODUCTS_ATTRIBUTES);
	}
	
	protected function initSync() {
		require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');
		$this->simplePrice = new SimplePrice();

		/* {Hook} "SyncInventory_initSync": Runs beginning of an the synchronization for a marketplace. Here you can add or modify any kind of settings for the synchronization.<br>
			   Variables that can be used: 
			   <ul><li>$this->mpID: The ID of the marketplace.</li>
			       <li>$this->marketplace: The name of the marketplace.</li>
			   </ul>
		 */
		if (($hp = magnaContribVerify('SyncInventory_initSync', 1)) !== false) {
			require($hp);
		}
	}
	
	protected function initMLProduct() {
		MLProduct::gi()->resetOptions();
	}
	
	protected function getConfigKeys() {
		return array (
			'KeyType' => array (
				'key' => 'general.keytype',
				'default' => 'artNr',
			),
			'VarType' => array (
				'key' => 'general.options',
				'default' => 'old',
			),
			'StockSync' => array (
				'key' => 'stocksync.tomarketplace',
			),
			'PriceSync' => array (
				'key' => 'inventorysync.price',
			),
			'QuantityType' => array (
				'key' => 'quantity.type',
				'default' => '',
			),
			'QuantityValue' => array (
				'key' => 'quantity.value',
				'default' => 0,
			),
			'QuantityMax' => array (
				'key' => 'quantity.maxquantity',
				'default' => 0,
			),
			'StatusMode' => array (
				'key' => 'general.inventar.productstatus',
				'default' => 'false',
			)
		);
	}

	protected function processUpdateItemsErrors($result) {
		if (!array_key_exists('ERRORS', $result)
			|| !is_array($result['ERRORS'])
			|| empty($result['ERRORS'])
		) {
			if ($this->_debugLevel >= self::DBGLV_HIGH) $this->log("\n\nNo errors.");
			return;
		}
		if ($this->_debugLevel >= self::DBGLV_HIGH) $this->logAPIErrors($result['ERRORS']);
		
		$callback = $this->helperClass.'::processCheckinErrors';
		if (is_callable($callback)) {
			call_user_func($callback, $result, $this->mpID);
			return;
		}
		
		foreach ($result['ERRORS'] as $err) {
			$ad = array ();
			if (isset($err['DETAILS']['SKU'])) {
				$ad['SKU'] = $err['DETAILS']['SKU'];
			}
			$err = array (
				'mpID' => $this->mpID,
				'errormessage' => $err['ERRORMESSAGE'],
				'dateadded' => gmdate('Y-m-d H:i:s'),
				'additionaldata' => serialize($ad),
			);
			MagnaDB::gi()->insert(TABLE_MAGNA_COMPAT_ERRORLOG, $err);
		}
	}

	protected function postProcessRequest(&$request) { }

	protected function updateItems($data) {
		if (!is_array($data) || empty($data)) {
			if ($this->_debug) $this->log("\n\nNothing to update in this batch.");
			return false;
		}
		$request = $this->getBaseRequest();
		$request['ACTION'] = 'UpdateItems';
		$request['DATA'] = $data;
		$this->postProcessRequest($request);
		
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
	
	protected function uploadItems() {
		$request = $this->getBaseRequest();
		$request['ACTION'] = 'UploadItems';

		if ($this->_debugLevel >= self::DBGLV_HIGH) $this->logAPIRequest($request);
		if ($this->_debug && $this->_debugDryRun) {
			return true;
		}
		MagnaConnector::gi()->setTimeOutInSeconds($this->timeouts['UploadItems']);
		try {
			$r = MagnaConnector::gi()->submitRequest($request);
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
	
	protected function calcNewQuantity() {
		if (($this->config['StatusMode'] === 'true')
			&& ((int)MagnaDB::gi()->fetchOne('
			    SELECT products_status FROM '.TABLE_PRODUCTS.'
			    WHERE products_id = \''.$this->cItem['pID'].'\'
			') == 0)
		) {
			return 0;
		}
		if ($this->config['QuantityType'] == 'lump') { # :-(
			return (int)$this->config['QuantityValue'];
		}
		
		$curQty = false;
		switch ($this->config['VarType']) {
			case ('gambioProperties'): {
				if ($this->cItem['aID'] > 0) {
					$curQty = MagnaDB::gi()->fetchOne('
						SELECT combi_quantity FROM products_properties_combis
				 		WHERE products_properties_combis_id = \''.$this->cItem['aID'].'\'
					');
				}
				break;
			}
			case('old'):
			default: {
				if (($this->cItem['aID'] > 0) && $this->hasDbColumn['pa.attributes_stock']) {
					if ($this->blMultiDimVariations) {
						if (!in_array($this->cItem['pID'], $this->aPidVariationsCalculated)) {
							setProductVariations($this->cItem['pID'], getDBConfigValue($this->marketplace.'.lang', $this->mpID));
							$this->aPidVariationsCalculated[] = $this->cItem['pID'];
						}
						$curQty = MagnaDB::gi()->fetchOne('
							SELECT variation_quantity FROM '.TABLE_MAGNA_VARIATIONS.'
							WHERE products_id = \''.$this->cItem['pID'].'\'
							'.(($this->config['KeyType'] == 'artNr')
							? 'AND marketplace_sku = \''.$this->cItem['SKU'].'\''
							: 'AND marketplace_id = \''.$this->cItem['SKU'].'\'')
						);
					} else {
						$curQty = MagnaDB::gi()->fetchOne('
							SELECT attributes_stock FROM '.TABLE_PRODUCTS_ATTRIBUTES.' 
				 			WHERE products_attributes_id = \''.$this->cItem['aID'].'\'
						');
					}
				}
				break;
			}
		}
		if ($curQty === false) {
			$curQty = MagnaDB::gi()->fetchOne('
				SELECT products_quantity FROM '.TABLE_PRODUCTS.'
				 WHERE products_id = \''.$this->cItem['pID'].'\'
			');
		}
		if ($curQty === false) {
			return false;
		}
	
		$curQty -= $this->config['QuantitySub'];
		if (($this->config['QuantityMax'] > 0)) {
			$curQty = min($curQty, $this->config['QuantityMax']);
		}
		if ($curQty < 0) {
			$curQty = 0;
		}
		return $curQty;
	}
	
	protected function isAutoSyncEnabled() {
		$this->syncStock = $this->config['StockSync'] == 'auto'  || ($this->config['StockSync'] == 'auto_fast');
		$this->syncPrice = $this->config['PriceSync'] == 'auto';
		
		//$this->syncStock = $this->syncPrice = true;

		if (!($this->syncStock || $this->syncPrice)) {
			$this->log('== '.$this->marketplace.' ('.$this->mpID.'): no autosync =='."\n");
			return false;
		}
		$this->log(
			'== '.$this->marketplace.' ('.$this->mpID.'): '.
			'Sync stock: '.($this->syncStock ? 'true' : 'false').'; '.
			'Sync price: '.($this->syncPrice ? 'true' : 'false')." ==\n"
		);
		return true;
	}

	protected function initQuantitySub() {
		$this->config['QuantitySub'] = 0;
		if ($this->syncStock) {
			if ($this->config['QuantityType'] == 'stocksub') {
				$this->config['QuantitySub'] = $this->config['QuantityValue'];
			}
		}
	}

	protected function identifySKU() {
		$this->cItem['pID'] = (int)magnaSKU2pID($this->cItem['SKU']);
		$this->cItem['aID'] = (int)magnaSKU2aID($this->cItem['SKU']);
		if (    !$this->cItem['pID']
		     && $this->config['VarType'] == 'old'
                     && $this->blMultiDimVariations) {
			$this->cItem['pID'] = (int)MagnaDB::gi()->fetchOne('
				SELECT products_id
				  FROM '.TABLE_MAGNA_VARIATIONS.'
				'.(($this->config['KeyType'] == 'artNr')
				? 'WHERE marketplace_sku = \''.$this->cItem['SKU'].'\''
				: 'WHERE marketplace_id = \''.$this->cItem['SKU'].'\'').'
				LIMIT 1');
		}
	}
	
	protected function fixIdentification() {
		if (!($this->cItem['pID'] > 0) && ($this->cItem['aID'] > 0)) {
			$this->cItem['pID'] = MagnaDB::gi()->fetchOne(eecho('
				SELECT products_id
				  FROM '.TABLE_PRODUCTS_ATTRIBUTES.' 
				 WHERE products_attributes_id="'.$this->cItem['aID'].'"
				 LIMIT 1
			', false));
		}
	}

	protected function updateQuantity() {
		if (!$this->syncStock) return false;
		
		$data = false;
		$curQty = $this->calcNewQuantity();

		if (!isset($this->cItem['Quantity'])) {
			$this->cItem['Quantity'] = 0;
		}

		if (isset($this->cItem['Quantity']) && ($this->cItem['Quantity'] != $curQty)) {
			$data = array (
				'Mode' => 'SET',
				'Value' => (int)$curQty
			);
			$this->log("\n\t".
				'Quantity changed (old: '.$this->cItem['Quantity'].'; new: '.$curQty.')'
			);

		} else {
			$this->log("\n\t".
				'Quantity not changed ('.$curQty.')'
			);
		}
		return $data;
	}

	protected function updatePrice() {
		if (!$this->syncPrice) return false;
		
		$data = false;
		
		if (    $this->blMultiDimVariations
		     && $this->config['VarType'] == 'old') {
			if ($this->cItem['aID'] > 0) {
				if (!in_array($this->cItem['pID'], $this->aPidVariationsCalculated)) {
					setProductVariations($this->cItem['pID'], getDBConfigValue($this->marketplace.'.lang', $this->mpID));
					$this->aPidVariationsCalculated[] = $this->cItem['pID'];
				}
				$varPriceAdd = MagnaDB::gi()->fetchOne('
					SELECT variation_price FROM '.TABLE_MAGNA_VARIATIONS.'
					WHERE products_id = \''.$this->cItem['pID'].'\'
					'.(($this->config['KeyType'] == 'artNr')
					? 'AND marketplace_sku = \''.$this->cItem['SKU'].'\''
					: 'AND marketplace_id = \''.$this->cItem['SKU'].'\'')
				);
			} else {
				$varPriceAdd = 0;
			}
			$price = $this->simplePrice
				->setPriceFromDB($this->cItem['pID'], $this->mpID)
				->addLump($varPriceAdd)
				->finalizePrice($this->cItem['pID'], $this->mpID)
				->getPrice();
		} else {
			$price = $this->simplePrice
				->setPriceFromDB($this->cItem['pID'], $this->mpID)
				->addAttributeSurcharge($this->cItem['aID'])
				->finalizePrice($this->cItem['pID'], $this->mpID)
				->getPrice();
		}

		if (($price > 0) && ((float)$this->cItem['Price'] != $price)) {
			$this->log("\n\t".
				'Price changed (old: '.$this->cItem['Price'].'; new: '.$price.')'
			);
			$data = $price;
		} else {
			$this->log("\n\t".
				'Price not changed ('.$price.')'
			);
		}
		return $data;
	}

	protected function updateCustomFields(&$data) {
		/* Child classes may add aditional fields that have to be provided or can be synced. */
	}
	
	final protected function addToBatch($data) {
		$mpID = $this->mpID;
		$marketplace = $this->marketplace;
		/* {Hook} "SyncInventory_UpdateItem": Runs at the end of an item synchronization. Here you can add additional data to the item that
			   should be synchronized or change the calculated data.<br>
			   Variables that can be used: 
			   <ul><li>$this->mpID: The ID of the marketplace.</li>
			       <li>$this->marketplace: The name of the marketplace.</li>
			       <li>$data (array): The content of the changes of one product (used to generate the <code>UpdateItem</code> request).<br>
			           Supported are <span class="tt">Price</span> and <span class="tt">Quantity</span>
			       </li>
			       <li>$this->cItem (array): The current product from the marketplaces inventory including some identification information.
			           <ul><li>SKU: Article number of marketplace</li>
			               <li>pID: products_id of product</li>
			               <li>aID: attributes_id of product</li></ul>
			       </li>
			   </ul>
			   <p>Notice: It is only possible to modify products that have been identified by the magnalister plugin!</p>
			   Example:
			   <pre>// For amazon set the quantity of the product with the SKU blabla123 to be always 5
if (($this->marketplace == 'amazon') && ($this->cItem['SKU'] == 'blabla123')) {
	$data['Quantity'] = 5;
}</pre>
		*/
		if (($hp = magnaContribVerify('SyncInventory_UpdateItem', 1)) !== false) {
			require($hp);
		}

		if (!empty($data)) {
			if (!isset($data['SKU'])) {
				$data['SKU'] = $this->cItem['SKU'];
			}
			$this->stockBatch[] = $data;
		}
	}

    /**
     * Returns the marketplace Product title
     *
     * @return mixed|string
     */
    protected function getProductTitle() {
        return isset($this->cItem['Title'])
            ? $this->cItem['Title']
            : (isset($this->cItem['ItemTitle'])
                ? $this->cItem['ItemTitle']
                : 'unknown'
            );
    }
	
	protected function updateItem() {
		$this->identifySKU();
		$this->fixIdentification();
		
		$title = $this->getProductTitle();
		
		/* {Hook} "SyncInventory_PreUpdateItem": Runs at the beginning of an item synchronization. Here you can try to fix the identification of
			   the item to make sure it gets processed in case the SKU can not be found in the first try.<br>
			   Variables that can be used: 
			   <ul><li>$this->mpID: The ID of the marketplace.</li>
			       <li>$this->marketplace: The name of the marketplace.</li>
			       <li>$this->cItem (array): The current product from the marketplaces inventory including some identification information.
			           <ul><li>SKU: Article number of marketplace</li>
			               <li>pID: products_id of product</li>
			               <li>aID: attributes_id of product</li></ul>
			       </li>
			   </ul>
			   <p>Notice: In case the identification of the item was successfull the value of $this->cItem['pID'] is > 0.</p>
		*/
		if (($hp = magnaContribVerify('SyncInventory_PreUpdateItem', 1)) !== false) {
			require($hp);
		}
		
		if ((int)$this->cItem['pID'] <= 0) {
			$this->log("\n".
				'SKU: '.$this->cItem['SKU'].' ('.$title.') not found (Marketplace: '.$this->marketplaceTitle.')'
			);
			return;
		} else {
			$this->log("\n".
				'SKU: '.$this->cItem['SKU'].' ('.$title.') found ('.
				'pID: '.$this->cItem['pID'].'; aID: '.$this->cItem['aID'].'; Marketplace: '.$this->marketplaceTitle.')'
			);
		}
		
		$data = array();
		
		$qU = $this->updateQuantity();
		if ($qU !== false) {
			$data['NewQuantity'] = $qU;
		}
		
		$pU = $this->updatePrice();
		if ($pU !== false) {
			$data['Price'] = $pU;
		}
		
		$this->updateCustomFields($data);
		
		$this->addToBatch($data);
	}

	protected function extendGetInventoryRequest(&$request) { }

	protected function submitStockBatch() {
		$this->updateItems($this->stockBatch);
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
			
				foreach ($result['DATA'] as $item) {
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
		
		$this->uploadItems();
		
		// Marker for completed operation, so that no continue request is made
		$this->dataOut(array (
			'Complete' => 'true',
		));
		
		return true;
	}

	public function process() {
		if (!$this->isAutoSyncEnabled()) {
			return;
		}
		if (isset($_GET['mpid']) && ($_GET['mpid'] == $this->mpID) && isset($_GET['offset']) && ctype_digit($_GET['offset'])) {
			$this->offset = (int)$_GET['offset'];
			$this->log('--> Continue from offset: '.$this->offset."\n");
		}
		// Only sync X steps. Execution will then be aborted and later continued though another request.
		if (isset($_GET['steps']) && ((int)$_GET['steps'] >= 1)) {
			$this->steps = (int)$_GET['steps'];
		}
		// Define the size of the response of the GetInventory call
		if (isset($_GET['maxitems']) && ((int)$_GET['maxitems'] >= 1)) {
			$this->limit = (int)$_GET['maxitems'];
		}
		
		$this->syncInventory();
		MagnaConnector::gi()->resetTimeOut();
		
	}
}
