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

/**
 * TODO: Siehe appendAdditionalData()
 */
class HitmeisterCheckinSubmit extends MagnaCompatibleCheckinSubmit {

	protected $useShippingtimeMatching = false;
	protected $defaultShippingtime = '';
	protected $shippingtimeMatching = array();

	public function __construct($settings = array()) {
		parent::__construct($settings);
		$this->summaryAddText = "<br /><br />\n".ML_HITMEISTER_UPLOAD_EXPLANATION;
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
		
		$this->defaultShippingtime  = getDBConfigValue($this->marketplace.'.shippingtime', $this->mpID, 0); 
		$this->shippingtimeMatching = getDBConfigValue($this->marketplace.'.shippingtimematching.values', $this->mpID, array()); 
		$this->useShippingtimeMatching = getDBConfigValue(array($this->marketplace.'.shippingtimematching.prefer', 'val'), $this->mpID, false); 
		
		if (!is_array($this->shippingtimeMatching) || empty($this->shippingtimeMatching)) {
			$this->useShippingtimeMatching = false;
		}
	}
	
	protected function appendAdditionalData($pID, $product, &$data) {
		parent::appendAdditionalData($pID, $product, $data);
		
		$data['submit']['Title'] = $data['submit']['ItemTitle'];
		unset($data['submit']['ItemTitle']);
		
		if (defined('MAGNA_FIELD_PRODUCTS_EAN') && array_key_exists(MAGNA_FIELD_PRODUCTS_EAN, $product)) {
			$data['submit']['EAN'] = $product[MAGNA_FIELD_PRODUCTS_EAN];
		}
		
		$prepare = MagnaDB::gi()->fetchRow('
			SELECT * FROM '.TABLE_MAGNA_HITMEISTER_PREPARE.'
			 WHERE '.((getDBConfigValue('general.keytype', '0') == 'artNr')
					     ? 'products_model=\''.MagnaDB::gi()->escape($product['products_model']).'\''
					     : 'products_id=\''.$pID.'\''
					).' 
				   AND mpID = '.$this->_magnasession['mpID'].'
		');
		if (is_array($prepare)) {
			$data['submit']['MarketplaceCategory'] = $prepare['mp_category_id'];
			$data['submit']['IsPorn']    = $prepare['is_porn'];
			$data['submit']['AgeRating'] = $prepare['age_rating'];
			$data['submit']['ShippingTime']  = $prepare['shippingtime'];
			$data['submit']['ConditionType'] = $prepare['condition_id'];
			$data['submit']['Comment']   = $prepare['comment'];
		} else {
			/* TODO: Shippingtime aus selection oder aus matching oder der generelle wert. */
			$data['submit']['ShippingTime']  = isset($data['shippingtime']) && !empty($data['shippingtime'])
				? $data['shippingtime']
				: (($this->useShippingtimeMatching)
					? $this->shippingtimeMatching[$product['products_shippingtime']]
					: $this->defaultShippingtime
				);
			$data['submit']['ConditionType'] = getDBConfigValue($this->settings['marketplace'].'.itemcondition', $this->_magnasession['mpID']);
		}
		$data['submit']['Location'] = getDBConfigValue($this->settings['marketplace'].'.itemcountry', $this->_magnasession['mpID']);
		
		 //echo print_m($data);
	}
	
	protected function filterItem($pID, $data) {
		return array();
	}
	
	protected function filterSelection() {
		$b = parent::filterSelection();

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
					$mfC[$key] = $field;
				}
				$sku = magnaPID2SKU($pID);
				//echo print_m($mfC, $sku);
				//*
				MagnaDB::gi()->insert(
					TABLE_MAGNA_COMPAT_ERRORLOG,
					array (
						'mpID' => $this->mpID,
						'errormessage' => json_encode(array (
							'MissingFields' => $mfC
						)),
						'dateadded' => gmdate('Y-m-d H:i:s'),
						'additionaldata' => serialize(array(
							'SKU' => $sku
						))
					)
				);
				//*/
				$shitHappend = true;
				$this->badItems[] = $pID;
				unset($this->selection[$pID]);
			}
		}
		$this->badItems = array_unique($this->badItems);
		return $b || $shitHappend;
	}

	protected function postSubmit() {
		#echo 'postSubmit';
		/*if (isset($this->initSession['selectionFromErrorLog']) && !empty($this->initSession['selectionFromErrorLog'])) {
			foreach ($this->initSession['selectionFromErrorLog'] as $errID => $pID) {
				MagnaDB::gi()->delete(
					TABLE_MAGNA_CS_ERRORLOG,
					array(
						'id' => (int)$errID
					)
				);
			}
		}*/
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

}
