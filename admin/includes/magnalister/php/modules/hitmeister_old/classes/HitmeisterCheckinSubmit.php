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
 * $Id: HitmeisterCheckinSubmit.php 2437 2013-05-06 13:32:58Z tim.neumann $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/CheckinSubmit.php');

class HitmeisterCheckinSubmit extends CheckinSubmit {
	protected $firstRun = false;

	public function __construct($settings = array()) {
		global $_MagnaSession, $_modules;

		$settings = array_merge(array(
			'language' => getDBConfigValue($settings['marketplace'].'.lang', $_MagnaSession['mpID']),
			'currency' => getCurrencyFromMarketplace($_MagnaSession['mpID']),
		), $settings);

		parent::__construct($settings);
	}
	
	public function init($mode, $items = -1) {
		parent::init($mode, $items);
		$this->initSession['RequiredFileds'] = array();
		try {
			$requiredFileds = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'GetRequiredKeys',
			));
			if (!empty($requiredFileds['DATA'])) {
				foreach ($requiredFileds['DATA'] as $key) {
					$this->initSession['RequiredFileds'][$key] = true;
				}
			}
		} catch (MagnaException $e) { }
	}

	public function makeSelectionFromErrorLog() {
		$madeSelection = false;
		
		$sanitizedIDs = array();
		foreach ($_POST['errIDs'] as $errID) {
			if (ctype_digit($errID)) {
				$sanitizedIDs[] = $errID;
			}
		}
		$res = MagnaDB::gi()->fetchArray('
			SELECT id, products_id, products_model, product_details
			  FROM '.TABLE_MAGNA_CS_ERRORLOG.' 
			 WHERE id IN ('.implode(', ', $sanitizedIDs).')
		');	
	
		if (!empty($res)) {
			$pIDs = array();
			$errorIDs = array();
			$data = array();
			
			foreach ($res as $item) {
				if (getDBConfigValue('general.keytype', '0') == 'artNr') {
					$pID = MagnaDB::gi()->fetchOne('
						SELECT products_id FROM '.TABLE_PRODUCTS.' WHERE products_model=\''.MagnaDB::gi()->escape($item['products_model']).'\'
					');
					if ($pID !== false) {
						$pIDs[] = $pID;
					}
				} else {
					if (MagnaDB::gi()->recordExists(TABLE_PRODUCTS, array('products_id' => $item['products_id']))) {
						$pIDs[] = $pID = $item['products_id'];
					} else {
						$pID = false;
					}
				}
				if ($pID !== false) {
					$errorIDs[$item['id']] = $pID;
					$data[$pID] = $item['product_details'];
				}
			}

			$pIDs = array_unique($pIDs);
			$batch = array();
			foreach ($pIDs as $pID) {
				$selection[$pID] = $data[$pID];
				$batch[] = array(
					'pID' => $pID,
					'data' => $data[$pID],
					'mpID' => $this->_magnasession['mpID'],
					'selectionname' => $this->settings['selectionName'],
					'session_id' => session_id(),
					'expires' => gmdate('Y-m-d H:i:s')
				);
			}
			MagnaDB::gi()->batchinsert(TABLE_MAGNA_SELECTION, $batch, true);
			unset($data);
			unset($pIDs);
			unset($batch);
			
			$this->initSession['selectionFromErrorLog'] = $errorIDs;
			$madeSelection = true;
		}

		return $madeSelection;
	}

	protected function generateRequestHeader() {
		return array(
			'ACTION' => 'AddItems',
			'MODE' => $this->submitSession['mode']
		);
	}

	protected function appendAdditionalData($pID, $product, &$data) {
		if (!empty($product['products_image']) && file_exists(SHOP_FS_POPUP_IMAGES.$product['products_image'])) {
			$imageUrl = (!empty($product['products_image']) ? SHOP_URL_POPUP_IMAGES : '').$product['products_image'];
		} else {
			$imageUrl = '';
		}

		$comment = '';

		switch (getDBConfigValue($this->settings['marketplace'].'.commentfrom', $this->_magnasession['mpID'])) {
			case 'description': {
				$comment = $product['products_description'];
				break;
			}
			case 'short_description': {
				if (array_key_exists('products_short_description', $product)) {
					$comment = $product['products_short_description'];
				}
				break;
			}
			case 'title': {
				$comment = $product['products_name'];
				break;
			}
		}

		$data['submit']['SKU']           = magnaPID2SKU($product['products_id']);
		if (defined('MAGNA_FIELD_PRODUCTS_EAN') && array_key_exists(MAGNA_FIELD_PRODUCTS_EAN, $product)) {
			$data['submit']['EAN']       = $product[MAGNA_FIELD_PRODUCTS_EAN];
		}
		$data['submit']['Title']         = $product['products_name'];
		# $data['submit']['Description']   = $product['products_description'];
		
		$data['submit']['Price']         = $data['price'];
		$data['submit']['Quantity']      = $data['quantity'];
		
		$data['submit']['ShippingTime']  = getDBConfigValue($this->settings['marketplace'].'.shippingtime', $this->_magnasession['mpID']);
		$data['submit']['ConditionType'] = getDBConfigValue($this->settings['marketplace'].'.itemcondition', $this->_magnasession['mpID']);
		$data['submit']['Comment']       = $comment;
		$data['submit']['Location']      = getDBConfigValue($this->settings['marketplace'].'.itemcountry', $this->_magnasession['mpID']);
		/*
		$data['submit']['Image']         = $imageUrl;
		
		$manufacturerName = '';
		if ($product['manufacturers_id'] > 0) {
			$manufacturerName = (string)MagnaDB::gi()->fetchOne(
				'SELECT manufacturers_name FROM '.TABLE_MANUFACTURERS.' WHERE manufacturers_id=\''.$product['manufacturers_id'].'\''
			);
		}
		if (empty($manufacturerName)) {
			$manufacturerName = getDBConfigValue(
				$this->marketplace.'.checkin.manufacturerfallback',
				$this->mpID,
				''
			);
		}
		if (!empty($manufacturerName)) {
			$data['submit']['Manufacturer'] = $manufacturerName;
		}
		*/
	}
	
	protected function generateErrorSaveArray(&$data) {
		return array (
			'quantity' => $data['submit']['Quantity'],
			'price' => $data['submit']['Price'],
		);
	}
	
	protected function filterItem($pID, $data) {
		return array();
	}

	protected function filterSelection() {
		$shitHappend = false;
		$missingFields = array();
		foreach ($this->selection as $pID => &$data) {
			if ($data['submit']['Price'] <= 0) {
				// Loesche das Feld, um eine Fehlermeldung zu erhalten
				unset($data['submit']['Price']);
			}
			
			$mfC = array();
			
			$this->requirementsMet($data['submit'], $this->initSession['RequiredFileds'], $mfC);
			$mfC = array_merge($mfC, $this->filterItem($pID, $data['submit']));

			if (!empty($mfC)) {
				foreach ($mfC as $key => $field) {
					$mfC[$key] = ltrim(strtoupper(preg_replace('/([A-Z][a-z])/', '_${1}', $field)), '_');
				}
				MagnaDB::gi()->insert(
					TABLE_MAGNA_CS_ERRORLOG,
					array (
						'mpID' => $this->_magnasession['mpID'],
						'products_id' => $pID,
						'products_model' => MagnaDB::gi()->fetchOne('SELECT products_model FROM '.TABLE_PRODUCTS.' WHERE products_id=\''.$pID.'\''),
						'product_details' => serialize($this->generateErrorSaveArray($data)),
						'errormessage' => json_encode($mfC),
						'timestamp' => gmdate('Y-m-d H:i:s')
					)
				);
				$shitHappend = true;
				$this->badItems[] = $pID;
				unset($this->selection[$pID]);
			}
		}
		return $shitHappend;
	}
	
	protected function processSubmitResult($result) {}
	
	protected function postSubmit() {
		#echo 'postSubmit';
		if (isset($this->initSession['selectionFromErrorLog']) && !empty($this->initSession['selectionFromErrorLog'])) {
			foreach ($this->initSession['selectionFromErrorLog'] as $errID => $pID) {
				MagnaDB::gi()->delete(
					TABLE_MAGNA_CS_ERRORLOG,
					array(
						'id' => (int)$errID
					)
				);
			}
		}
		#echo var_dump_pre($this->initSession['upload']);
		try {
			$result = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'UploadItems',
			));
			#echo print_m($result, true);
		} catch (MagnaException $e) {
			#echo print_m($e, 'Exception', true);
			$this->submitSession['api']['exception'] = $e->getErrorArray();
		}
	}

	protected function generateRedirectURL($state) {
		return toURL(array(
			'mp' => $this->realUrl['mp'],
			'mode'   => 'listings',
		    'view'   => ($state == 'fail') ? 'failed' : 'inventory'
		), true);
	}

	protected function getFinalDialogs() {
		return array();
	}
}
