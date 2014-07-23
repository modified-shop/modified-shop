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
 * $Id: eBayCheckinSubmit.php 601 2010-12-09 18:55:30Z MaW $
 *
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/CheckinSubmit.php');

class eBayCheckinSubmit extends CheckinSubmit {
	private $verify = false;
	private $lastException = null;
	
	protected $ignoreErrors = true;

	public function __construct($settings = array()) {
		global $_MagnaSession;
		$settings = array_merge(array(
			'itemsPerBatch'   => 1,
			'language' => getDBConfigValue($settings['marketplace'].'.lang', $_MagnaSession['mpID']),
			'currency' => getCurrencyFromMarketplace($_MagnaSession['mpID']),
		), $settings);
		
		parent::__construct($settings);
		$this->summaryAddText = "<br />\n".ML_EBAY_SUBMIT_ADD_TEXT_ZERO_STOCK_ITEMS_REMOVED;
	}

	protected function generateRequestHeader() {
	# das Request braucht nur action, subsystem und data
		return array(
			'ACTION' => ($this->verify ? 'VerifyAddItems' : 'AddItems'),
			'SUBSYSTEM' => 'eBay'
		);
	}

	# Anders als im allg. Fall: Reihenfolge wie in der Auflistung
	protected function initSelection($offset, $limit) {
		$this->selection = array();
		if ($this->verify) {
			# fuer Verify nur Artikel mit gueltiger Menge und Preis nehmen, ausser man findet keine
			$verifySelectionResult = MagnaDB::gi()->query('
			    SELECT ms.pID pID, ms.data data
			      FROM '.TABLE_MAGNA_SELECTION.' ms, '.TABLE_PRODUCTS.' p, '.TABLE_PRODUCTS_DESCRIPTION.' pd
			     WHERE mpID=\''.$this->_magnasession['mpID'].'\' AND
			           selectionname=\''.$this->settings['selectionName'].'\' AND
			           session_id=\''.session_id().'\' AND
			           pd.language_id = \''.$this->settings['language'].'\' AND
			           p.products_quantity > 0 AND p.products_price > 0.0 AND
			           p.products_id = ms.pID AND
			           pd.products_id = ms.pID
			  ORDER BY pd.products_name ASC
			     LIMIT '.$offset.','.$limit.'
			');
			while ($row = MagnaDB::gi()->fetchNext($verifySelectionResult)) {
				$this->selection[$row['pID']] = unserialize($row['data']);
			}
			if (!empty($this->selection)) {
				return;
			}
		}
		parent::initSelection($offset, $limit);
	}

	protected function appendAdditionalData($pID, $product, &$data) {
		$propertiesRow = MagnaDB::gi()->fetchRow('SELECT * FROM '.TABLE_MAGNA_EBAY_PROPERTIES
				.' WHERE '
				.((getDBConfigValue('general.keytype', '0') == 'artNr')
				     ? 'products_model=\''.MagnaDB::gi()->escape($product['products_model']).'\''
				     : 'products_id=\''.$pID.'\''
				).' AND mpID = '.$this->_magnasession['mpID']);
		require_once(DIR_MAGNALISTER_MODULES.'ebay/ebayFunctions.php');
		
		if (!empty($propertiesRow['Subtitle'])) $data['submit']['ItemSubTitle'] = $propertiesRow['Subtitle'];
		$shortdesc = '';
		#if (!empty($propertiesRow['Subtitle'])) {
		#	$shortdesc = $propertiesRow['Subtitle'];
		#} else
		if (array_key_exists('products_short_description', $product)) {
			$shortdesc = $product['products_short_description'];
		}
		$data['submit']['Price'] = (!empty($data['price'])) ? $data['price']:$propertiesRow['Price'];
		if (0 == $data['submit']['Price']) { # preis nicht eingefroren bzw. gegeben, berechnen
			$data['submit']['Price'] = makePrice($pID, $propertiesRow['ListingType']);
		}
		# VPE
		if (isset($product['products_vpe_name'])
		    && (0 <> $product['products_vpe_value'])
		) {
			$formatted_vpe = $this->simpleprice->setPrice($data['submit']['Price'] * (1.0 / $product['products_vpe_value']))->format().' / '.fixHTMLUTF8Entities($product['products_vpe_name']);
		} else {
			$formatted_vpe = '';
		}
		# Titel: Entferne komische nicht-druckbare Zeichen wie &curren; & ggf VPE einsetzen
		$data['submit']['Title'] = $this->restoreCutBaseprice(eBaySubstituteTemplate($this->_magnasession['mpID'], $pID, html_entity_decode(fixHTMLUTF8Entities($propertiesRow['Title']),ENT_COMPAT,'UTF-8'), array(
						'#VPE#' => $formatted_vpe,
						'#BASEPRICE#' => $formatted_vpe
		)), $formatted_vpe);
		if ('1' == $propertiesRow['PrivateListing']) {
			$data['submit']['PrivateListing'] = 'true';
		}
		if (('1' == $propertiesRow['BestOfferEnabled']) && ('Chinese' != $propertiesRow['ListingType'])){
			$data['submit']['BestOfferEnabled'] = 'true';
		}
		if (!empty($propertiesRow['StartTime'])) {
			$data['submit']['StartTime'] = $propertiesRow['StartTime'];
		}
		if (!empty($propertiesRow['HitCounter'])) {
			$data['submit']['HitCounter'] = $propertiesRow['HitCounter'];
		}
		# RestrictedToBusiness, wenn in der Config aktiviert (default false)
		if (getDBConfigValue(array($this->_magnasession['currentPlatform'].'.restrictToBusiness', 'val'), $this->_magnasession['mpID'], false)) {
			$data['submit']['RestrictedToBusiness'] = 'true';
		}
		# Wenn nicht in der Maske gefuellt
		if (empty($data['submit']['Description'])) {
			if (!empty($propertiesRow['Description'])) {
				if($this->verify)
					$data['submit']['Description'] = stringToUTF8($propertiesRow['Description']);
				else
				# Beim Uebermitteln Preis einsetzen
					$data['submit']['Description'] = eBaySubstituteTemplate($this->_magnasession['mpID'], $pID, $propertiesRow['Description'], array(
						'#PRICE#' => $this->simpleprice->setPrice($data['submit']['Price'])->formatWOCurrency(),
						'#VPE#' => $formatted_vpe,
						'#BASEPRICE#' => $formatted_vpe
					));
			} else {
				$eBayTemplate = getDBConfigValue('ebay.template.content', $this->_magnasession['mpID']);
				$imagePath    = getDBConfigValue('ebay.imagepath', $this->_magnasession['mpID']);
				$substitution = array(
					'#TITLE#' => fixHTMLUTF8Entities($product['products_name']),
					'#ARTNR#' => $product['products_model'],
					'#PID#' => $pID,
					'#SKU#' => magnaPID2SKU($pID),
					'#SHORTDESCRIPTION#' => stringToUTF8($shortdesc),
					'#DESCRIPTION#' => stripLocalWindowsLinks(stringToUTF8($product['products_description'])),
					'#PICTURE1#' => $propertiesRow['PictureURL'],
					'#PRICE#' => $this->simpleprice->setPrice($data['submit']['Price'])->formatWOCurrency(),
					'#VPE#' => $formatted_vpe,
					'#BASEPRICE#' => $formatted_vpe,
					'#WEIGHT#' => ((float)$product['products_weight']>0)?$product['products_weight']:'',
				);
				$data['submit']['Description'] = stringToUTF8(substitutePictures(eBaySubstituteTemplate(
					$this->_magnasession['mpID'], $pID, $eBayTemplate, $substitution
				), $pID, $imagePath));
			}
		} else {
			$data['submit']['Description'] = stringToUTF8($data['submit']['Description']);
		}
		$data['submit']['PictureURL'] = str_replace(array(' ','&'),array('%20','%26'), trim($propertiesRow['PictureURL']));
		if (!empty($propertiesRow['GalleryURL'])) {
			$data['submit']['GalleryURL'] = str_replace(array(' ','&'),array('%20','%26'), trim($propertiesRow['GalleryURL']));
		}
		if ($propertiesRow['ConditionID']) {
			$data['submit']['ConditionID'] = $propertiesRow['ConditionID'];
		}
		if (!empty($propertiesRow['BuyItNowPrice']) && 'Chinese' == $propertiesRow['ListingType']) {
			$data['submit']['BuyItNowPrice'] = $propertiesRow['BuyItNowPrice'];
		}
		$data['submit']['SKU'] = magnaPID2SKU($pID);
		# EAN, wenn aktiviert (default false)
		if (getDBConfigValue(array($this->_magnasession['currentPlatform'].'.useean', 'val'), $this->_magnasession['mpID'], false)) {
			$data['submit']['EAN'] = MagnaDB::gi()->fetchOne('SELECT products_ean FROM '.TABLE_PRODUCTS.' WHERE products_id = '.$pID);
			if (empty($data['submit']['EAN'])) unset($data['submit']['EAN']);
		}
		# IncludePrefilledItemInformation, wenn aktiviert (default false)
		if (getDBConfigValue(array($this->_magnasession['currentPlatform'].'.usePrefilledInfo', 'val'), $this->_magnasession['mpID'], false)) {
			$data['submit']['IncludePrefilledItemInformation'] = 'true';
		}
		# TecDoc KType, wenn aktiviert
		$tecDocKType = getDBConfigValue('ebay.tecdoc.column', $this->_magnasession['mpID'], false);
		if (is_array($tecDocKType) && !empty($tecDocKType['column']) && !empty($tecDocKType['table'])) {
			$pIDAlias = getDBConfigValue('ebay.tecdoc.alias', $this->_magnasession['mpID'], false);
			if (!$pIDAlias) {
				$pIDAlias = 'products_id';
			}
			$data['submit']['tecDocKType'] = MagnaDB::gi()->fetchOne('
				SELECT `'.$tecDocKType['column'].'`
				  FROM `'.$tecDocKType['table'].'`
				WHERE `'.$pIDAlias.'`=\''.MagnaDB::gi()->escape($pID).'\'
				LIMIT 1
			');
			if (!$data['submit']['tecDocKType']) unset($data['submit']['tecDocKType']);
		}
		$data['submit']['PrimaryCategory'] = $propertiesRow['PrimaryCategory'];
		if(!empty($propertiesRow['SecondaryCategory'])) $data['submit']['SecondaryCategory'] = $propertiesRow['SecondaryCategory'];
		if(!empty($propertiesRow['StoreCategory'])) $data['submit']['StoreCategory'] = $propertiesRow['StoreCategory'];
		if(!empty($propertiesRow['StoreCategory2'])) $data['submit']['StoreCategory2'] = $propertiesRow['StoreCategory2'];
		if(!empty($propertiesRow['Attributes'])) $data['submit']['Attributes'] = json_decode($propertiesRow['Attributes'], true);
		if(!empty($propertiesRow['ItemSpecifics'])) $data['submit']['ItemSpecifics'] = json_decode($propertiesRow['ItemSpecifics'], true);
		# Varianten: Nur bei Festpreis-Einstellungen,
		# default is true
		if (  ('Chinese' <> $propertiesRow['ListingType'])
		    && getDBConfigValue(array($this->_magnasession['currentPlatform'].'.usevariations', 'val'), $this->_magnasession['mpID'], true)
		    && VariationsEnabled($data['submit']['PrimaryCategory'])
		) {
			$data['submit']['Variations'] = getVariations($pID, $data['submit']['Price']);
			if (!$data['submit']['Variations']) unset($data['submit']['Variations']);
		}
		$data['submit']['ListingType']     = $propertiesRow['ListingType'];
		$data['submit']['ListingDuration'] = $propertiesRow['ListingDuration'];
		$data['submit']['Country']         = getDBConfigValue('ebay.country', $this->_magnasession['mpID']);
		$data['submit']['Site']            = getDBConfigValue('ebay.site', $this->_magnasession['mpID']);

		# Der Preis wurde mit der in der Config festgelegten Currency berechnet. Nicht die Currency aus der Vorbereitung nehmen, sondern aus der Config.
		//$data['submit']['currencyID']      = $propertiesRow['currencyID'];
		$data['submit']['currencyID']      = $this->settings['currency'];

		$data['submit']['Location']        = getDBConfigValue('ebay.location', $this->_magnasession['mpID']);
		$data['submit']['PostalCode']      = getDBConfigValue('ebay.postalcode', $this->_magnasession['mpID']);
		$data['submit']['Tax']      = getDBConfigValue('ebay.mwst', $this->_magnasession['mpID'], 0);
		$data['submit']['PaymentMethods']  = json_decode($propertiesRow['PaymentMethods'], true);
		$PayPalEmailAddress = getDBConfigValue('ebay.paypal.address', $this->_magnasession['mpID']);
		if(!empty($PayPalEmailAddress)) $data['submit']['PayPalEmailAddress']  = $PayPalEmailAddress;
		$data['submit']['Quantity']        = (!empty($data['quantity'])) ? $data['quantity']: makeQuantity($pID, $propertiesRow['ListingType']);
		$data['submit']['ReturnPolicy']    = array();
		# Return Policy, Details:
		$data['submit']['ReturnPolicy']['ReturnsAcceptedOption'] = getDBConfigValue('ebay.returnpolicy.returnsaccepted', $this->_magnasession['mpID'], 'ReturnsAccepted');
		$data['submit']['ReturnPolicy']['Description'] = getDBConfigValue('ebay.returnpolicy.description', $this->_magnasession['mpID'], null);
		if (empty($data['submit']['ReturnPolicy']['Description'])) unset($data['submit']['ReturnPolicy']['Description']);
		$data['submit']['ReturnPolicy']['ReturnsWithinOption'] = getDBConfigValue('ebay.returnpolicy.returnswithin', $this->_magnasession['mpID'], null);
		if (empty($data['submit']['ReturnPolicy']['ReturnsWithinOption'])) unset($data['submit']['ReturnPolicy']['ReturnsWithinOption']);
		$data['submit']['ReturnPolicy']['ShippingCostPaidByOption'] = getDBConfigValue('ebay.returnpolicy.shippingcostpaidby', $this->_magnasession['mpID'], null);
		if (empty($data['submit']['ReturnPolicy']['ShippingCostPaidByOption'])) unset($data['submit']['ReturnPolicy']['ShippingCostPaidByOption']);
		$data['submit']['ReturnPolicy']['WarrantyDurationOption'] = getDBConfigValue('ebay.returnpolicy.warrantyduration', $this->_magnasession['mpID'], 'none');
		if ('none' == $data['submit']['ReturnPolicy']['WarrantyDurationOption']) unset($data['submit']['ReturnPolicy']['WarrantyDurationOption']);
		$data['submit']['DispatchTimeMax'] = (string)getDBConfigValue('ebay.DispatchTimeMax', $this->_magnasession['mpID']);
		$data['submit']['ShippingDetails'] = json_decode($propertiesRow['ShippingDetails'], true);
		# Payment instructions
		$data['submit']['ShippingDetails']['PaymentInstructions'] = getDBConfigValue('ebay.paymentinstructions', $this->_magnasession['mpID'], null);
		if (empty($data['submit']['ShippingDetails']['PaymentInstructions'])) unset($data['submit']['ShippingDetails']['PaymentInstructions']);
		# =GEWICHT beruecksichtigen
		foreach ( $data['submit']['ShippingDetails']['ShippingServiceOptions'] as &$options) {
			if ('=GEWICHT' == (string)$options['ShippingServiceCost']) {
				$options['ShippingServiceCost'] = $product['products_weight'];
				if(isset($options['FreeShipping'])) unset($options['FreeShipping']);
			}
		}
		if(is_array($data['submit']['ShippingDetails']['InternationalShippingServiceOption'])) {
			foreach ( $data['submit']['ShippingDetails']['InternationalShippingServiceOption'] as &$options) {
				if ('=GEWICHT' == (string)$options['ShippingServiceCost']) {
					$options['ShippingServiceCost'] = $product['products_weight'];
					if(isset($options['FreeShipping'])) unset($options['FreeShipping']);
				}
			}
		}
		# Versandprofil Inland
		if (   array_key_exists('LocalProfile', $data['submit']['ShippingDetails'])
			&& (0 != $data['submit']['ShippingDetails']['LocalProfile'])) {
			$data['submit']['ShippingDetails']['ShippingDiscountProfileID'] = $data['submit']['ShippingDetails']['LocalProfile'];
		} else if (!array_key_exists('LocalProfile', $data['submit']['ShippingDetails'])
					&& (0 != getDBConfigValue('ebay.default.shippingprofile.local',$this->_magnasession['mpID'], 0))) {
			$data['submit']['ShippingDetails']['ShippingDiscountProfileID'] = getDBConfigValue('ebay.default.shippingprofile.local',$this->_magnasession['mpID'], 0);
		}
		if (isset($data['submit']['ShippingDetails']['LocalProfile']))
			unset($data['submit']['ShippingDetails']['LocalProfile']);
		# Versandpauschale Inland
		if (   array_key_exists('LocalPromotionalDiscount', $data['submit']['ShippingDetails'])
			&& ('true' == $data['submit']['ShippingDetails']['LocalPromotionalDiscount'])) {
			$data['submit']['ShippingDetails']['PromotionalShippingDiscount'] = 'true';
		} else if (!array_key_exists('LocalPromotionalDiscount', $data['submit']['ShippingDetails'])) {
			$data['submit']['ShippingDetails']['PromotionalShippingDiscount'] = getDBConfigValue(array('ebay.shippingdiscount.local', 'val'), $this->_magnasession['mpID'], 'false');
		}
		if (isset($data['submit']['ShippingDetails']['LocalPromotionalDiscount']))
			unset($data['submit']['ShippingDetails']['LocalPromotionalDiscount']);
		# Versandprofil Ausland
		if (   array_key_exists('InternationalProfile', $data['submit']['ShippingDetails'])
			&& (0 != $data['submit']['ShippingDetails']['InternationalProfile'])) {
			$data['submit']['ShippingDetails']['InternationalShippingDiscountProfileID'] = $data['submit']['ShippingDetails']['InternationalProfile'];
		} else if (!array_key_exists('InternationalProfile', $data['submit']['ShippingDetails'])
					&& (0 != getDBConfigValue('ebay.default.shippingprofile.international',$this->_magnasession['mpID'], 0))) {
			$data['submit']['ShippingDetails']['InternationalShippingDiscountProfileID'] = getDBConfigValue('ebay.default.shippingprofile.international',$this->_magnasession['mpID'], 0);
		}
		if (isset($data['submit']['ShippingDetails']['InternationalProfile']))
			unset($data['submit']['ShippingDetails']['InternationalProfile']);
		# Versandpauschale Ausland
		if (   array_key_exists('InternationalPromotionalDiscount', $data['submit']['ShippingDetails'])
			&& ('true' == $data['submit']['ShippingDetails']['InternationalPromotionalDiscount'])) {
			$data['submit']['ShippingDetails']['InternationalPromotionalShippingDiscount'] = 'true';
		} else if (!array_key_exists('InternationalPromotionalDiscount', $data['submit']['ShippingDetails'])) {
			$data['submit']['ShippingDetails']['InternationalPromotionalShippingDiscount'] = getDBConfigValue(array('ebay.shippingdiscount.international', 'val'), $this->_magnasession['mpID'], 'false');
		}
		if (isset($data['submit']['ShippingDetails']['InternationalPromotionalDiscount']))
			unset($data['submit']['ShippingDetails']['InternationalPromotionalDiscount']);
		# ShippingServiceAdditionalCost aus den Profilen nehmen
		$shippingProfiles = getDBConfigValue('ebay.shippingprofiles', $this->_magnasession['mpID'], null);
		if (!empty($shippingProfiles))  {
			$localProfileID = isset($data['submit']['ShippingDetails']['ShippingDiscountProfileID'])
				?$data['submit']['ShippingDetails']['ShippingDiscountProfileID']:0;
			$internationalProfileID = isset($data['submit']['ShippingDetails']['InternationalShippingDiscountProfileID'])
				?$data['submit']['ShippingDetails']['InternationalShippingDiscountProfileID']:0;
			if(!empty($localProfileID)) {
				$localAddCost = $shippingProfiles['Profiles']["$localProfileID"]['EachAdditionalAmount'];
			}
			if(!empty($internationalProfileID)) {	
				$internationalAddCost = $shippingProfiles['Profiles']["$internationalProfileID"]['EachAdditionalAmount'];
			}
			foreach ( $data['submit']['ShippingDetails']['ShippingServiceOptions'] as &$options) {
				if(0 == $options['ShippingServiceCost']) {
					$options['ShippingServiceAdditionalCost'] = 0.0;
					continue;
				}
				if(isset($localAddCost))
					$options['ShippingServiceAdditionalCost'] = (float)$localAddCost;
				else
					if (isset($options['ShippingServiceAdditionalCost'])) unset($options['ShippingServiceAdditionalCost']);
			}
			if(is_array($data['submit']['ShippingDetails']['InternationalShippingServiceOption'])) {
				foreach ( $data['submit']['ShippingDetails']['InternationalShippingServiceOption'] as &$options) {
					if(0 == $options['ShippingServiceCost']) {
						$options['ShippingServiceAdditionalCost'] = 0.0;
						continue;
					}
					if(isset($internationalAddCost))
						$options['ShippingServiceAdditionalCost'] = (float)$internationalAddCost;
					else
						if (isset($options['ShippingServiceAdditionalCost'])) unset($options['ShippingServiceAdditionalCost']);
				}
			}
		}
		# RateTableDetails: possibly switchable-off by config in the future
		$data['submit']['ShippingDetails']['UseRateTables'] = 'true';
	}

	# Hilfsfunktion: Fuer den Fall dass am Ende des Titels ein #BASEPRICE# steht,
	# das durch die 80-Zeichen-Beschraenkung abgeschnitten wurde
	private function restoreCutBaseprice($str, $bp) {
		$maxLength = 80;
		if (strlen($str) < $maxLength) return $str;
		$lastHashPos = strrpos($str, '#');
		$lastPlaceholder = substr($str, $lastHashPos);
		if (0 === strpos('#BASEPRICE#', $lastPlaceholder)) {
			# wiederherstellen
			$str = substr($str, 0, $lastHashPos).'#BASEPRICE#';
		} else {
			return $str;
		} 
		# ersetzen
		$str = str_replace('#BASEPRICE#', $bp, $str);

		if (strlen($str) > $maxLength) {
			# falls jetzt zu lang, kuerzen, aber Ersetzung erhalten
			$str = substr($str, 0, $maxLength - strlen($bp) -1) . ' '. $bp;
		}
		return $str;
	}

	protected function preSubmit(&$request) {
		MagnaConnector::gi()->setTimeOutInSeconds(600);
	}

	protected function postSubmit() {
		MagnaConnector::gi()->resetTimeOut();
	}

	protected function processSubmitResult($result) {
		$responsedata = $result['RESPONSEDATA'];
		foreach($responsedata as $i => $itemResult) {
			if(!is_numeric($i)) continue; # lass Header-Daten weg 
			$listing_data[$i] = array(
				'mpID'           => $itemResult['MARKETPLACEID'],
				'SKU'            => $itemResult['DATA']['SKU'],
				'products_id'    => magnaSKU2pID($itemResult['DATA']['SKU']),
				'products_model' => MagnaDB::gi()->fetchOne('
					SELECT products_model 
					  FROM '.TABLE_PRODUCTS.'
					 WHERE products_id = '.magnaSKU2pID($itemResult['DATA']['SKU'])
				),
				'Title'          => $itemResult['DATA']['ItemTitle'],
				'Price'          => $itemResult['DATA']['Price'],
				'currencyID'     => $itemResult['DATA']['Currency'],
				'CategoryID'     => $itemResult['DATA']['CategoryID'],
				'ListingType'    => $itemResult['DATA']['ListingType'],
				'Quantity'       => $itemResult['DATA']['Quantity']
			);
		
			if(!empty($itemResult['DATA']['ItemID'])) {
				$listing_data[$i]['ItemID']    = $itemResult['DATA']['ItemID'];
				$listing_data[$i]['StartTime'] = eBayTimeToTs($itemResult['DATA']['StartTime']);
				$listing_data[$i]['EndTime']   = eBayTimeToTs($itemResult['DATA']['EndTime']);
				#$listing_data[$i]['Fees']     = serialize($itemResult['DATA']['Fees']);
			}
			if(!empty($itemResult['ERRORS'])) {
				$listing_data[$i]['Errors'] = serialize($itemResult['ERRORS']);
			}

			//<changesMBa>
			MagnaDB::gi()->query('UPDATE '.TABLE_MAGNA_EBAY_PROPERTIES.' SET Transferred=\''.('ERROR' == $itemResult['STATUS']?0:1).'\' WHERE  products_id =\''.$listing_data[$i]['products_id'].'\'');
			//</changesMBa>
			
			if ('ERROR' == $itemResult['STATUS']) {
				$listing_data[$i]['Timestamp'] = eBayTimeToTs($itemResult['DATA']['Timestamp']);
				MagnaDB::gi()->insert(TABLE_MAGNA_EBAY_ERRORLOG, $listing_data[$i]);
				$pID = $listing_data[$i]['products_id'];
				$this->badItems[] = $pID;
				unset($this->selection[$pID]);
			}
		}
	}

	public function makeSelectionFromErrorLog() {}

	protected function filterSelection() {
		# Anzahlen <=0 wegfiltern
		foreach ($this->selection as $pID => &$data) { 
			if (
				((int)$data['submit']['Quantity'] <= 0)
				|| empty($data['submit']['Description'])
			) { 
				unset($this->selection[$pID]); 
				$this->disabledItems[] = $pID; 
			} 
		}
	}

	protected function generateRedirectURL($state) {
		return toURL(array(
			'mp' => $this->realUrl['mp'],
			'mode'   => 'listings',
			'view'   => ($state == 'fail') ? 'failed' : 'inventory'
		), true);
	}
	
	protected function processException($e) {
		$this->lastException = $e;
	}

	public function getLastException() {
		return $this->lastException;
	}

	protected function generateCustomErrorHTML() {
		$exs = MagnaError::gi()->getExceptionCollection();
		$html = '';
		foreach ($exs as $ex) {
			if (!is_object($ex) || ($ex->getSubsystem() == 'PHP') || (($ex->getSubsystem() == 'Core'))) {
				continue;
			}
			$errors = $ex->getErrorArray();
			if (  !is_array($errors['RESPONSEDATA'])
			    ||!is_array($errors['RESPONSEDATA'][0])
			    ||!is_array($errors['RESPONSEDATA'][0]['ERRORS'])
			    ||!is_array($errors['RESPONSEDATA'][0]['ERRORS'][0])) continue;
			if (!isset($errors['RESPONSEDATA'][0]['ERRORS'][0]['ERRORCODE'])) continue;

			/* ... als unkrittisch markieren. */
			$ex->setCriticalStatus(false);

			foreach ($errors['RESPONSEDATA'] as $ebayItemErrors) {
				#$html .= print_m($ebayItemErrors);
				foreach ($ebayItemErrors['ERRORS'] as $ebayError) {
					#$html .= print_m($ebayError);
					if (($ebayError['ERRORCLASS'] != 'RequestError') || ($ebayError['ERRORLEVEL'] != 'Error')) continue;
					$html .= '
					<div class="ebay errorBox">
						<div class="itemident">
							<span class="label">'.ML_LABEL_SKU.'</span>: '.$ebayItemErrors['DATA']['SKU'].', 
							<span class="label">'.ML_LABEL_TITLE.'</span>: '.$ebayItemErrors['DATA']['ItemTitle'].'
						</div>
						<span class="error">'.sprintf(ML_EBAY_LABEL_EBAYERROR, $ebayError['ERRORCODE']).':</span> '.
						$ebayError['ERRORMESSAGE'].'
					</div>';
				}
			}
		}
		return $html;
	}

	public function verifyOneItem() {
		$this->verify = true;
		MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
			'mpID' => $this->_magnasession['mpID'],
			'selectionname' => $this->settings['selectionName'].'Verify',
			'session_id' => session_id()
		));
		$item = MagnaDB::gi()->fetchRow('
			SELECT * FROM '.TABLE_MAGNA_SELECTION.' 
			 WHERE mpID=\''.$this->_magnasession['mpID'].'\' AND
			       selectionname=\''.$this->settings['selectionName'].'\' AND
			       session_id=\''.session_id().'\'
			 LIMIT 1
		');
		if (empty($item)) {
			return false;
		}
		
		$oldSelectionName = $this->settings['selectionName'];
		$this->settings['selectionName'] = $this->settings['selectionName'].'Verify';
		$item['selectionname'] = $this->settings['selectionName'];
		MagnaDB::gi()->insert(TABLE_MAGNA_SELECTION, $item);
		
		//echo print_m($this->settings, '$this->settings');

		$this->initSelection(0, 1);
		//echo print_m($this->selection, '$this->selection[1]');
		foreach ($this->selection as $pID => &$data) {
			$data['quantity'] = 1; // hack to get verification of chinese items working
		}
		$this->populateSelectionWithData();
		//echo print_m($this->selection, '$this->selection[2]');
		$result = $this->sendRequest();

		MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
			'mpID' => $this->_magnasession['mpID'],
			'selectionname' => $this->settings['selectionName'],
			'session_id' => session_id()
		));
		
		// restore selection name
		$this->settings['selectionName'] = $oldSelectionName;
		
		# Liste der pIDs um die ebay_properties upzudaten
		$selectedPidsArray = MagnaDB::gi()->fetchArray('
			SELECT distinct pID FROM '.TABLE_MAGNA_SELECTION.'
		 	WHERE mpID=\''.$this->_magnasession['mpID'].'\' AND
			selectionname=\''.$this->settings['selectionName'].'\' AND
			session_id=\''.session_id().'\'
		');
		$selectedPidsList = '';
		foreach ($selectedPidsArray as $pIDsRow) {
			if (is_numeric($pIDsRow['pID'])) $selectedPidsList .= $pIDsRow['pID'].', ';
		}
		$selectedPidsList = trim($selectedPidsList, ', ');
		if (  ('SUCCESS' == $result['STATUS'])
			&&('SUCCESS' == $result[0]['STATUS'])
		   ) {
			MagnaDB::gi()->query('UPDATE '.TABLE_MAGNA_EBAY_PROPERTIES.' SET Verified=\'OK\' WHERE mpID = '.$this->_magnasession['mpID'].' AND products_id IN ('.$selectedPidsList.')');
		} else if ('ERROR' == $result['STATUS']) {
			MagnaDB::gi()->query('UPDATE '.TABLE_MAGNA_EBAY_PROPERTIES.' SET Verified=\'ERROR\' WHERE mpID = '.$this->_magnasession['mpID'].' AND products_id IN ('.$selectedPidsList.')');
		}
		return $result;
	}
	
}
