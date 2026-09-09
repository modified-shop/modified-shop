<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/AttributesMatchingHelper.php');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');
require_once(DIR_MAGNALISTER_MODULES.'temu/classes/TemuVariantSpecResolver.php');
require_once(DIR_MAGNALISTER_MODULES.'temu/classes/TemuParentChildVisibility.php');

class TemuHelper extends AttributesMatchingHelper {

	protected static $instance = null;

	public static function gi() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	protected static $aWeightConversions = array(
		'kg' => 1, 'g' => 0.001, 'mg' => 0.000001,
		'lb' => 0.45359237, 'oz' => 0.0283495231, 't' => 1000,
	);

	/**
	 * Price config for MLProduct::setPriceConfig() / SimplePrice::setFinalPriceFromDB().
	 * A FLAT valid config (single-price) so MLProduct returns a scalar Price — a nested
	 * { 'Fixed' => … } config would make MLProduct return a keyed Price array, which
	 * buildSubmitData/preparePropertiesRow can't cast to a scalar.
	 * Delegates to SimplePrice::loadPriceSettings so the config keys match what the
	 * configure form actually stores: the markup lives in `temu.price.factor` (there is
	 * no `temu.price.addvalue`), and `temu.price.usespecialoffer` is stored as
	 * {"val":...} — a plain-key read returns that array, which is truthy even when the
	 * merchant disabled the option.
	 * `IncludeTax => false` because **Temu requires the NET price (tax excluded)** for
	 * add/update (matches Hitmeister/Check24/Metro/… net-price marketplaces).
	 */
	public static function loadPriceSettings($mpId) {
		$aConfig = SimplePrice::loadPriceSettings($mpId);
		$aConfig['IncludeTax'] = false; // Temu requires NET price (tax excluded)
		return $aConfig;
	}

	public static function loadQuantitySettings($mpId) {
		return array(
			'QuantityType' => getDBConfigValue('temu.quantity.type', $mpId, 'stock'),
			'QuantityValue' => getDBConfigValue('temu.quantity.value', $mpId, 0),
		);
	}

	/**
	 * Marketplace-adjusted quantity for the checkin "Stock for Temu" column.
	 * Mirrors OttoHelper::getQuantityForOtto using temu.quantity.* config.
	 * @return int
	 */
	public static function getQuantityForTemu($iProductsQuantity, $mpID) {
		$sCalcMethod = getDBConfigValue('temu.quantity.type', $mpID, 'stock');
		$iQuantityValue = getDBConfigValue('temu.quantity.value', $mpID, 0);
		$iMaxQuantity = getDBConfigValue('temu.maxquantity', $mpID, 0);
		switch ($sCalcMethod) {
			case 'stocksub':
				$iQuantity = (int)($iProductsQuantity - $iQuantityValue);
				break;
			case 'lump':
				return (int)$iQuantityValue; // maxquantity not relevant
			case 'stock':
			default:
				$iQuantity = (int)$iProductsQuantity;
				break;
		}
		return empty($iMaxQuantity) ? $iQuantity : min($iQuantity, $iMaxQuantity);
	}

	public static function GetShippingTypesConfig() {
		return array(
			'PARCEL' => ML_TEMU_LABEL_SHIPPING_PARCEL,
			'FORWARDER' => ML_TEMU_LABEL_SHIPPING_FORWARDER,
		);
	}

	public static function getRegionalUnits($mpID = null) {
		if ($mpID === null) {
			global $_MagnaSession;
			$mpID = $_MagnaSession['mpID'];
		}
		return TemuMarketplace::getRegionalUnits($mpID);
	}

	public static function convertWeight($weight, $fromUnit, $toUnit) {
		if ($fromUnit === $toUnit) return $weight;
		$fromFactor = isset(self::$aWeightConversions[$fromUnit]) ? self::$aWeightConversions[$fromUnit] : 1;
		$toFactor = isset(self::$aWeightConversions[$toUnit]) ? self::$aWeightConversions[$toUnit] : 1;
		return ($weight * $fromFactor) / $toFactor;
	}

	public static function hexEncodeAttributeName($sName) {
		return bin2hex($sName);
	}

	public static function hexDecodeAttributeName($sHex) {
		return hex2bin($sHex);
	}

	/**
	 * Collect human-readable error messages from a marketplace API result.
	 *
	 * Prefers the per-SKU DATA.Errors map ({sku: [msg, …]}) — the actionable field-level
	 * errors, each prefixed with "[SKU] " when it doesn't already reference the SKU. Only when
	 * no per-SKU detail is present does it fall back to the top-level ERRORS[] (which for
	 * VerifyAddItems is just the generic "[val_verification_failed]" wrapper). Mirrors v3
	 * ML_Temu_Model_Service_AddItems::handleException().
	 *
	 * Each record also carries an `origin` (the API operation that produced it, e.g.
	 * "VerifyAddItems"/"AddItems") so the Error Log tab can show where the error came from —
	 * taken from the error/result APIACTION, falling back to $sDefaultOrigin.
	 *
	 * @param array  $result
	 * @param string $sDefaultOrigin Origin to use when the result carries no APIACTION.
	 * @return array list of ['sku' => string, 'message' => string, 'origin' => string]
	 */
	public static function collectApiErrors($result, $sDefaultOrigin = '') {
		if (!is_array($result)) {
			return array();
		}
		// Which API operation produced these errors (shown as "Origin" in the Error Log tab).
		$sOrigin = $sDefaultOrigin;
        if (!empty($result['ORIGIN'])) {
            $sOrigin = $result['ORIGIN'];
        } else if (!empty($result['ERRORS'][0]['APIACTION'])) {
			$sOrigin = $result['ERRORS'][0]['APIACTION'];
		} else if (!empty($result['REQUEST']['ACTION'])) {
			$sOrigin = $result['REQUEST']['ACTION'];
		}
		// Per-SKU detail: DATA.Errors = { sku: [msg, …] } — the actionable field-level errors.
		$aDetail = array();
		if (!empty($result['DATA']['Errors']) && is_array($result['DATA']['Errors'])) {
			foreach ($result['DATA']['Errors'] as $sSku => $aSkuErrors) {
				if (!is_array($aSkuErrors)) {
					$aSkuErrors = array($aSkuErrors);
				}
				foreach ($aSkuErrors as $sErr) {
					if (!is_string($sErr) || $sErr === '') {
						continue;
					}
					$sMsg = (strpos($sErr, (string)$sSku) === false) ? '['.$sSku.'] '.$sErr : $sErr;
					$aDetail[] = array('sku' => (string)$sSku, 'message' => $sMsg, 'origin' => $sOrigin);
				}
			}
		}
		// When per-SKU detail exists it fully supersedes the generic top-level wrapper.
		if (!empty($aDetail)) {
			return $aDetail;
		}
		// Fallback: top-level ERRORS[] (may carry a SKU in DETAILS/ERRORDATA).
		$aOut = array();
		if (!empty($result['ERRORS']) && is_array($result['ERRORS'])) {
			foreach ($result['ERRORS'] as $err) {
				if (!is_array($err)) {
					continue;
				}
				$sMsg = isset($err['ERRORMESSAGE']) ? $err['ERRORMESSAGE']
					: (isset($err['ErrorMessage']) ? $err['ErrorMessage'] : '');
				if ($sMsg === '') {
					continue;
				}
				$sSku = '';
				if (isset($err['ERRORDATA']['SKU'])) {
					$sSku = $err['ERRORDATA']['SKU'];
				} elseif (isset($err['DETAILS']['SKU'])) {
					$sSku = $err['DETAILS']['SKU'];
				}
				$sErrOrigin = !empty($err['APIACTION']) ? $err['APIACTION'] : $sOrigin;
				$aOut[] = array('sku' => (string)$sSku, 'message' => $sMsg, 'origin' => $sErrOrigin);
			}
		}
		return $aOut;
	}

	public static function processCheckinErrors($result, $mpID) {
		foreach (self::collectApiErrors($result, 'AddItems') as $aErr) {
			MagnaDB::gi()->insert(TABLE_MAGNA_COMPAT_ERRORLOG, array(
				'mpID'           => $mpID,
				'origin'         => isset($aErr['origin']) ? $aErr['origin'] : 'AddItems',
				'errormessage'   => $aErr['message'],
				'dateadded'      => gmdate('Y-m-d H:i:s'),
				'additionaldata' => serialize($aErr['sku'] !== '' ? array('SKU' => $aErr['sku']) : array()),
			));
		}
		return array();
	}

	public static function getWarehouses($mpID = null) {
		if ($mpID === null) { global $_MagnaSession; $mpID = $_MagnaSession['mpID']; }
		try {
			$result = MagnaConnector::gi()->submitRequestCached(array(
				'ACTION' => 'GetWarehouseList', 'SUBSYSTEM' => 'Temu', 'MARKETPLACEID' => $mpID,
			), 30 * 60);
			$aWarehouses = array();
			// API returns the list nested under DATA.Warehouses; fall back to a flat DATA list.
			$aList = array();
			if (isset($result['DATA']['Warehouses']) && is_array($result['DATA']['Warehouses'])) {
				$aList = $result['DATA']['Warehouses'];
			} elseif (isset($result['DATA']) && is_array($result['DATA'])) {
				$aList = $result['DATA'];
			}
			foreach ($aList as $wh) {
				if (!is_array($wh) || empty($wh['WarehouseId'])) continue;
				$label = isset($wh['WarehouseName']) && $wh['WarehouseName'] !== ''
					? $wh['WarehouseName']
					: $wh['WarehouseId'];
				$aWarehouses[$wh['WarehouseId']] = $label;
			}
			return $aWarehouses;
		} catch (MagnaException $e) { return array(); }
	}

	public static function getCarriers($mpID = null) {
		if ($mpID === null) { global $_MagnaSession; $mpID = $_MagnaSession['mpID']; }
		try {
			$result = MagnaConnector::gi()->submitRequestCached(array(
				'ACTION' => 'GetCarrierList', 'SUBSYSTEM' => 'Temu', 'MARKETPLACEID' => $mpID,
			), 30 * 60);
			$aCarriers = array();
			// API returns the list nested under DATA.Carriers; fall back to a flat DATA list.
			$aList = array();
			if (isset($result['DATA']['Carriers']) && is_array($result['DATA']['Carriers'])) {
				$aList = $result['DATA']['Carriers'];
			} elseif (isset($result['DATA']) && is_array($result['DATA'])) {
				$aList = $result['DATA'];
			}
			foreach ($aList as $carrier) {
				if (!is_array($carrier) || empty($carrier['CarrierId'])) continue;
				$aCarriers[$carrier['CarrierId']] = isset($carrier['CarrierName']) && $carrier['CarrierName'] !== ''
					? $carrier['CarrierName']
					: $carrier['CarrierId'];
			}
			return $aCarriers;
		} catch (MagnaException $e) { return array(); }
	}

	public static function getCancellationReasons($mpID = null) {
		if ($mpID === null) { global $_MagnaSession; $mpID = $_MagnaSession['mpID']; }
		try {
			$result = MagnaConnector::gi()->submitRequestCached(array(
				'ACTION' => 'GetCancellationReasons', 'SUBSYSTEM' => 'Temu', 'MARKETPLACEID' => $mpID,
			), 30 * 60);
			$aReasons = array();
			if (isset($result['DATA']) && is_array($result['DATA'])) {
				foreach ($result['DATA'] as $reason) {
					if (!is_array($reason) || empty($reason['ReasonId'])) {
						continue;
					}
					$aReasons[$reason['ReasonId']] = isset($reason['ReasonText']) ? $reason['ReasonText'] : $reason['ReasonId'];
				}
			}
			return $aReasons;
		} catch (MagnaException $e) { return array(); }
	}

	/**
	 * Build the Temu product submit payload (simple products) shared by
	 * VerifyAddItems and AddItems. $aBase provides lowercase price/quantity/currency.
	 */
	public static function buildSubmitData($mpID, $pID, $aPrepare, $aBase) {
		$aLongtext = MagnaDB::gi()->fetchRow("
			SELECT * FROM ".TABLE_MAGNA_TEMU_PREPARE_LONGTEXT."
			 WHERE mpID = '".(int)$mpID."' AND products_id = '".(int)$pID."'
		");
		$fPrice = isset($aBase['price']) ? (float)$aBase['price'] : (float)$aPrepare['Price'];
		$aSubmit = array(
			'SKU'            => $aPrepare['SKU'],
			'ProductName'    => $aPrepare['Title'],
			'Description'    => $aPrepare['Description'],
			'CatId'          => $aPrepare['PrimaryCategory'],
			'EAN'            => $aPrepare['EAN'],
			// Temu API expects a DECIMAL price (e.g. "16.80"), NOT cents — the API's
			// BaseHelper::toTemuPrice() no longer multiplies by 100 (see API BUG-007). Sending
			// cents here made the Temu price 100× too high. The API number_formats to 2 decimals.
			'StandardPrice'  => round($fPrice, 2),
			// Temu requires BasePrice alongside StandardPrice. Mirror v3, where
			// PrepareData::basePriceField() == standardPriceField() (both the suggested
			// marketplace price).
			'BasePrice'      => round($fPrice, 2),
			'Currency'       => isset($aBase['currency']) ? $aBase['currency'] : getCurrencyFromMarketplace($mpID),
			'Quantity'       => isset($aBase['quantity']) ? (int)$aBase['quantity'] : 0,
		);
		// NOTE: MainImage is intentionally NOT sent — the image set is provided via Images[]
		// (Temu derives the main image from the first entry).
		// NOTE: CostTemplateId is intentionally NOT sent at the root — it is submitted inside
		// CategoryIndependentAttributes (from the "Shipping Template" CI matching, which carries
		// the real LFT-… value). A root CostTemplateId here duplicated it as an empty string.
		if (!empty($aPrepare['Images'])) {
			$aImages = json_decode($aPrepare['Images'], true);
			if (is_array($aImages)) {
				$aSubmit['Images'] = self::resolveImageUrls($aImages, $mpID);
			}
		}
		if (!empty($aPrepare['BulletPoints'])) {
			$aSubmit['BulletPoints'] = json_decode($aPrepare['BulletPoints'], true);
		}
		if (!empty($aPrepare['ShipmentLimitDay'])) {
			$aSubmit['ShipmentLimitDay'] = (int)$aPrepare['ShipmentLimitDay'];
		}
		if (is_array($aLongtext)
			&& (!empty($aLongtext['ShopVariationId']) || !empty($aLongtext['CategoryIndependentShopVariationId']))
		) {
			$aProductData = self::getProductDataForMatching($mpID, $pID);
			$oAttrMatch = new AttributesMatchingHelper($mpID);

			if (!empty($aLongtext['CategoryIndependentShopVariationId'])) {
				$aCatIndep = json_decode($aLongtext['CategoryIndependentShopVariationId'], true);
				if (is_array($aCatIndep)) {
					$aCI      = $oAttrMatch->convertMatchingToNameValue($aCatIndep, $aProductData);
					$aNameMap = self::getCategoryIndependentNameMap($mpID);
					$aCIOut   = array();
					foreach ($aCI as $sTitle => $mVal) {
						$sName = isset($aNameMap[$sTitle]) ? $aNameMap[$sTitle] : $sTitle;
						$aCIOut[$sName] = self::normalizeAttributeValue($mVal);
					}
					$aSubmit['CategoryIndependentAttributes'] = $aCIOut;
				}
			}

			if (!empty($aLongtext['ShopVariationId'])) {
				$aCatAttrs = json_decode($aLongtext['ShopVariationId'], true);
				if (is_array($aCatAttrs)) {
					// Only submit attributes that BELONG to the product's current category.
					// The stored ShopVariation blob can accumulate keys from other categories
					// (the matching is merged, not category-scoped), so filter against the
					// category's own attribute set. Returns null on API/cache miss → fail open
					// (submit as-is) rather than drop everything.
					$aValidKeys = self::getCategoryAttributeKeys($mpID, $aPrepare['PrimaryCategory']);
					// Split category attributes from variation dimensions (Color/Size →
					// SpecDetails); category attrs are also filtered to the current category.
					$aNonVariation = array();
					$aVariationDims = array();
					foreach ($aCatAttrs as $sKey => $mVal) {
						if (strpos($sKey, 'variation_dim_') === 0) {
							$aVariationDims[$sKey] = $mVal;
							continue;
						}
						if ($aValidKeys !== null && !isset($aValidKeys[$sKey])) {
							continue; // key belongs to a different category — drop
						}
						$aNonVariation[$sKey] = $mVal;
					}
					$aConverted = $oAttrMatch->convertMatchingToNameValue($aNonVariation, $aProductData);

					// Parent-child visibility (P03/P04): drop child attribute values whose parent's
					// resolved value does not trigger them — including children of a parent that
					// resolved to no value at all (orphans). Fail-open when the category has no
					// hierarchy or the details are unavailable. Mirrors v3 filterResolvedChildAttributes().
					$aCatDetails = self::getCategoryAttributes($mpID, $aPrepare['PrimaryCategory']);
					$aConverted  = TemuParentChildVisibility::filterResolvedOrphans($aConverted, $aCatDetails);

					$aCatOut = array();
					foreach ($aConverted as $sKey => $mVal) {
						$mVal = self::normalizeAttributeValue($mVal);
						// Select attributes carry a marketplace value-id (vid) and must be sent
						// wrapped as {vid: …} — this covers both a fixed marketplace value
						// (Code 'attribute_value') and a shop attribute matched to a select value
						// (convertMatchingToNameValue already resolved it to the vid). Free-text
						// attributes stay plain. Mirrors v3 CategoryAttributesField() (P06).
						$sCode = isset($aNonVariation[$sKey]['Code']) ? $aNonVariation[$sKey]['Code'] : '';
						$sType = (isset($aCatDetails[$sKey]['type']) && is_string($aCatDetails[$sKey]['type']))
							? $aCatDetails[$sKey]['type'] : '';
						if ($sCode === 'attribute_value' || $sType === 'select') {
							$aCatOut[$sKey] = array('vid' => $mVal);
						} else {
							$aCatOut[$sKey] = $mVal;
						}
					}
					$aSubmit['CategoryAttributes'] = $aCatOut;

					// Variation dimensions → SpecDetails (routed to specId/specName by category type).
					$aVarDetails  = self::getVariationDetails($mpID, $aPrepare['PrimaryCategory']);
					$aSpecDetails = self::buildSpecDetails($aVariationDims, $aVarDetails);
					if (!empty($aSpecDetails)) {
						$aSubmit['SpecDetails'] = $aSpecDetails;
					}
				}
			}
		}
		return $aSubmit;
	}

	/**
	 * FEAT-014: build the checkin payload as ONE item per variant (or a single
	 * item for a simple product). The base item (shared fields: CatId, Currency,
	 * Description, CategoryAttributes, CategoryIndependentAttributes, Images) comes
	 * from buildSubmitData(); each variant overrides SKU/ProductName/price/qty/EAN
	 * and its own per-variant SpecDetails. A simple product returns one item with
	 * MasterSKU == SKU and the base (representative) SpecDetails — backward compatible.
	 *
	 * @param int        $mpID
	 * @param int        $pID
	 * @param array      $aPrepare  parent prepare row (TABLE_MAGNA_TEMU_PREPARE)
	 * @param array      $aBase     price/quantity/currency overrides from the summary
	 * @param array|null $aVariants MLProduct variant list (each: MarketplaceSku, Price,
	 *                              Quantity, EAN, Variation[]); null/empty = simple product
	 * @return array  list of payload items
	 */
	public static function buildSubmitItems($mpID, $pID, $aPrepare, $aBase, $aVariants) {
		$aBaseItem = self::buildSubmitData($mpID, $pID, $aPrepare, $aBase);
		$sMasterSku  = isset($aBaseItem['SKU']) ? $aBaseItem['SKU'] : '';
		$sMasterName = isset($aBaseItem['ProductName']) ? $aBaseItem['ProductName'] : '';

		$aVariationDims = self::extractVariationDims($mpID, $pID);

		// Single item when there are no shop variants, OR the product was not prepared with
		// variation dimensions (no variation_dim_* matching) — e.g. skip_variations. Uploading
		// variant SKUs without SpecDetails would be rejected/mis-listed by Temu, so fall back to
		// the simple item (MasterSKU == SKU, base representative SpecDetails). Backward compatible.
		if (empty($aVariants) || !is_array($aVariants) || empty($aVariationDims)) {
			$aBaseItem['MasterSKU']         = $sMasterSku;
			$aBaseItem['MasterProductName'] = $sMasterName;
			return array($aBaseItem);
		}

		// Category variation_details for specId/specName routing — fetched once (cached) and reused
		// for every variant. Empty on failure ⇒ routeSpecDetails() keeps legacy specId placement.
		$aVariationDetails = self::getVariationDetails(
			$mpID, isset($aPrepare['PrimaryCategory']) ? $aPrepare['PrimaryCategory'] : ''
		);

		$aItems = array();
		foreach ($aVariants as $aVariation) {
			if (!is_array($aVariation)) {
				continue;
			}
			$aItem = $aBaseItem; // inherit shared/parent-level fields

			$aItem['SKU']               = !empty($aVariation['MarketplaceSku']) ? $aVariation['MarketplaceSku'] : $sMasterSku;
			$aItem['MasterSKU']         = $sMasterSku;
			$aItem['MasterProductName'] = $sMasterName;
			$aItem['ProductName']       = self::buildVariantProductName($sMasterName, $aVariation);

			if (isset($aVariation['Price']) && is_numeric($aVariation['Price'])) {
				$aItem['StandardPrice'] = round((float)$aVariation['Price'], 2);
				$aItem['BasePrice']     = round((float)$aVariation['Price'], 2);
			}
			if (isset($aVariation['Quantity'])) {
				$aItem['Quantity'] = (int)$aVariation['Quantity'];
			}
			if (!empty($aVariation['EAN'])) {
				$aItem['EAN'] = $aVariation['EAN'];
			}

			// Per-variant SpecDetails (replaces the representative one from the base item).
			$aVarValByDim = TemuVariantSpecResolver::variantValueByDim(
				isset($aVariation['Variation']) ? $aVariation['Variation'] : array(),
				$aVariationDims
			);
			// Same value normalisation buildSpecDetails() applies, so a variant sends the
			// identical type a simple product would ("1.5" → 1.5, "true" → true).
			$aSpec = TemuVariantSpecResolver::resolve(
				$aVariationDims, $aVarValByDim, $aVariationDetails,
				array('TemuHelper', 'normalizeAttributeValue')
			);
			if (!empty($aSpec)) {
				$aItem['SpecDetails'] = $aSpec;
			} else {
				unset($aItem['SpecDetails']);
			}

			$aItems[] = $aItem;
		}

		// Defensive: no usable variants resolved → fall back to the single base item.
		if (empty($aItems)) {
			$aBaseItem['MasterSKU']         = $sMasterSku;
			$aBaseItem['MasterProductName'] = $sMasterName;
			return array($aBaseItem);
		}
		return $aItems;
	}

	/**
	 * The `variation_dim_<id> => matching` subset of the product's stored ShopVariation
	 * matching (parent-level). Same split buildSubmitData() does inline; extracted so the
	 * per-variant path can resolve SpecDetails.
	 *
	 * @param int $mpID
	 * @param int $pID
	 * @return array
	 */
	public static function extractVariationDims($mpID, $pID) {
		$aDims = array();
		$aLongtext = MagnaDB::gi()->fetchRow("
			SELECT ShopVariationId FROM ".TABLE_MAGNA_TEMU_PREPARE_LONGTEXT."
			 WHERE mpID = '".(int)$mpID."' AND products_id = '".(int)$pID."'
		");
		if (is_array($aLongtext) && !empty($aLongtext['ShopVariationId'])) {
			$aCatAttrs = json_decode($aLongtext['ShopVariationId'], true);
			if (is_array($aCatAttrs)) {
				foreach ($aCatAttrs as $sKey => $mVal) {
					if (strpos($sKey, 'variation_dim_') === 0) {
						$aDims[$sKey] = $mVal;
					}
				}
			}
		}
		return $aDims;
	}

	/**
	 * Variant product name = master name + " : " + each variation value (e.g.
	 * "T-Shirt : Blau : L"), mirroring the shop variant title. Falls back to the
	 * master name when the variant carries no readable Variation values.
	 *
	 * @param string $sMasterName
	 * @param array  $aVariation MLProduct variant (expects Variation[] of {Value})
	 * @return string
	 */
	protected static function buildVariantProductName($sMasterName, $aVariation) {
		$aParts = array();
		if (!empty($aVariation['Variation']) && is_array($aVariation['Variation'])) {
			foreach ($aVariation['Variation'] as $aOpt) {
				if (is_array($aOpt) && isset($aOpt['Value']) && $aOpt['Value'] !== '') {
					$aParts[] = $aOpt['Value'];
				}
			}
		}
		return empty($aParts) ? $sMasterName : $sMasterName.' : '.implode(' : ', $aParts);
	}

	/**
	 * Build the Temu SpecDetails array from stored variation dimensions (variation_dim_<id>).
	 * Each resolved value becomes ['parentSpecId' => <dimId>, 'specId' => <value>].
	 *
	 * v2 value-matching blob shape:
	 *   - Code 'attribute_value' with a scalar Values → Values IS the specId (literal).
	 *   - Values = { idx: { Shop:{Key,Value}, Marketplace:{Key,Value} } } → the matched
	 *     Marketplace.Key (the Temu specId / spec name), falling back to the shop value when
	 *     UseShopValues is set and no marketplace match exists (free-text specs).
	 *
	 * ONE representative spec per variation dimension (the base/simple-product item; the
	 * per-variant SkuList combinations are built by TemuHelper::buildSubmitItems() +
	 * TemuVariantSpecResolver). Each raw value is routed to specId (preset) or specName
	 * (custom / free-text) via TemuVariantSpecResolver::routeSpecDetails() using the
	 * category's variation_details — a custom value sent as specId triggers Temu
	 * errorCode 150010076.
	 *
	 * @param array $aVariationDims   variation_dim_<id> => matching entry
	 * @param array $aVariationDetails category variation_details (id/type/specList) for routing;
	 *                                 empty ⇒ legacy specId placement (backward compatible).
	 * @return array
	 */
	public static function buildSpecDetails($aVariationDims, $aVariationDetails = array()) {
		$aRaw = array();
		if (empty($aVariationDims) || !is_array($aVariationDims)) {
			return $aRaw;
		}
		foreach ($aVariationDims as $sKey => $aAttr) {
			if (strpos($sKey, 'variation_dim_') !== 0 || !is_array($aAttr)) {
				continue;
			}
			$iDimId = (int)substr($sKey, strlen('variation_dim_'));

			// Resolve a single representative spec value for this dimension.
			$mSpec = null;
			if (isset($aAttr['Code']) && $aAttr['Code'] === 'attribute_value'
				&& isset($aAttr['Values']) && !is_array($aAttr['Values']) && $aAttr['Values'] !== ''
			) {
				$mSpec = $aAttr['Values']; // literal value (preset id or custom text)
			} elseif (isset($aAttr['Values']) && is_array($aAttr['Values'])) {
				foreach ($aAttr['Values'] as $aMap) {
					if (!is_array($aMap)) {
						continue;
					}
					if (isset($aMap['Marketplace']['Key']) && $aMap['Marketplace']['Key'] !== '') {
						$mSpec = $aMap['Marketplace']['Key'];
						break;
					}
					if (!empty($aAttr['UseShopValues']) && isset($aMap['Shop']['Value']) && $aMap['Shop']['Value'] !== '') {
						$mSpec = $aMap['Shop']['Value'];
						break;
					}
				}
			}

			if ($mSpec === null || $mSpec === '') {
				continue;
			}
			$aRaw[] = array(
				'parentSpecId' => $iDimId,
				'value'        => self::normalizeAttributeValue($mSpec),
			);
		}
		return TemuVariantSpecResolver::routeSpecDetails($aRaw, $aVariationDetails);
	}

	/**
	 * Return the set of valid category-specific attribute keys (prop_*, variation_dim_*)
	 * for a Temu category, as a lookup map [key => true]. Sourced from the cached
	 * GetCategoryDetails response so it does not hit the live API on every upload.
	 *
	 * @param int        $mpID
	 * @param string|int $categoryId
	 * @return array|null  Lookup map, or null when unavailable (caller must fail open).
	 */
	public static function getCategoryAttributeKeys($mpID, $categoryId) {
		if (empty($categoryId)) {
			return null;
		}
		try {
			$result = MagnaConnector::gi()->submitRequestCached(array(
				'ACTION'        => 'GetCategoryDetails',
				'SUBSYSTEM'     => 'Temu',
				'MARKETPLACEID' => (int)$mpID,
				'DATA'          => array('CategoryID' => $categoryId),
			), 86400);
		} catch (Exception $e) {
			return null; // fail open on API/timeout error
		}
		if (empty($result['DATA']['attributes']) || !is_array($result['DATA']['attributes'])) {
			return null;
		}
		$aKeys = array();
		foreach (array_keys($result['DATA']['attributes']) as $sKey) {
			$aKeys[$sKey] = true;
		}
		// variation dimensions (Color/Size → variation_dim_<id>) are legitimate category keys
		// too; they live under variation_details, not attributes.
		if (!empty($result['DATA']['variation_details']) && is_array($result['DATA']['variation_details'])) {
			foreach ($result['DATA']['variation_details'] as $aDim) {
				if (isset($aDim['id'])) {
					$aKeys['variation_dim_'.$aDim['id']] = true;
				}
			}
		}
		return $aKeys;
	}

	/**
	 * Return a Temu category's full attribute definitions (DATA.attributes, keyed by plain
	 * name), each carrying at least type/values/refPid/parentRefPid/childAttributes. Sourced
	 * from the cached GetCategoryDetails response (same cache as getCategoryAttributeKeys, so no
	 * extra API round-trip). Used to drop stale/orphan child attribute values from the upload
	 * payload (TemuParentChildVisibility::filterResolvedOrphans) and to decide whether a
	 * shop-attribute-matched value is a SELECT vid that must be wrapped as {vid: …} (P06).
	 * Returns an empty array on API/cache miss so callers fail open (submit as-is / no filtering).
	 *
	 * @param int        $mpID
	 * @param string|int $categoryId
	 * @return array  DATA.attributes map (possibly empty).
	 */
	public static function getCategoryAttributes($mpID, $categoryId) {
		if (empty($categoryId)) {
			return array();
		}
		try {
			$result = MagnaConnector::gi()->submitRequestCached(array(
				'ACTION'        => 'GetCategoryDetails',
				'SUBSYSTEM'     => 'Temu',
				'MARKETPLACEID' => (int)$mpID,
				'DATA'          => array('CategoryID' => $categoryId),
			), 86400);
		} catch (Exception $e) {
			return array(); // fail open on API/timeout error
		}
		if (empty($result['DATA']['attributes']) || !is_array($result['DATA']['attributes'])) {
			return array();
		}
		return $result['DATA']['attributes'];
	}

	/**
	 * Return a Temu category's `variation_details` (each: id, type, specList[]) from the cached
	 * GetCategoryDetails response, used by TemuVariantSpecResolver::routeSpecDetails() to place
	 * each spec value in specId (preset) vs specName (custom / free-text). Fully guarded: any
	 * failure (empty category, API/timeout error, missing key) returns an empty array, which makes
	 * routing fall back to the legacy specId placement — the central API stays the single backstop.
	 *
	 * @param int        $mpID
	 * @param string|int $categoryId
	 * @return array  variation_details (possibly empty)
	 */
	public static function getVariationDetails($mpID, $categoryId) {
		if (empty($categoryId)) {
			return array();
		}
		try {
			$result = MagnaConnector::gi()->submitRequestCached(array(
				'ACTION'        => 'GetCategoryDetails',
				'SUBSYSTEM'     => 'Temu',
				'MARKETPLACEID' => (int)$mpID,
				'DATA'          => array('CategoryID' => $categoryId),
			), 86400);
		} catch (Exception $e) {
			return array(); // fail open — routing keeps legacy specId placement
		}
		if (empty($result['DATA']['variation_details']) || !is_array($result['DATA']['variation_details'])) {
			return array();
		}
		return $result['DATA']['variation_details'];
	}

	/**
	 * Convert stored product-image filenames into absolute URLs for the marketplace API.
	 * Filenames are resolved against the configured `temu.imagepath` base (mirrors the
	 * Amazon checkin behaviour). Values that are already absolute (http/https) are kept
	 * as-is. Empty entries are dropped.
	 *
	 * @param array $aImages Image filenames (or absolute URLs)
	 * @param int   $mpID    Marketplace configuration ID
	 * @return array Absolute image URLs
	 */
	protected static function resolveImageUrls($aImages, $mpID) {
		$sBase = getDBConfigValue('temu.imagepath', (int)$mpID, '');
		if (empty($sBase) && defined('SHOP_URL_POPUP_IMAGES')) {
			$sBase = SHOP_URL_POPUP_IMAGES;
		}
		$sBase = ($sBase === '') ? '' : rtrim($sBase, '/').'/';

		$aOut = array();
		foreach ((array)$aImages as $sImg) {
			$sImg = trim((string)$sImg);
			if ($sImg === '') {
				continue;
			}
			$aOut[] = preg_match('#^https?://#i', $sImg) ? $sImg : $sBase.$sImg;
		}
		return $aOut;
	}

	/**
	 * Normalize an attribute value for the Temu submit payload — verbatim port of v3
	 * PrepareData::checkAndConvertAttributeByType(): 'true'/'false' → bool, decimal
	 * strings (dot or comma) → float. Integer strings and everything else are left as-is.
	 *
	 * @param mixed $mVal
	 * @return mixed
	 */
	public static function normalizeAttributeValue($mVal) {
		if (is_string($mVal) && (strtolower($mVal) === 'true' || strtolower($mVal) === 'false')) {
			return filter_var($mVal, FILTER_VALIDATE_BOOLEAN);
		}
		if (is_string($mVal) && preg_match('/^-?\d+\.\d*$/', $mVal)) {
			return (float)$mVal;
		}
		if (is_string($mVal) && preg_match('/^-?\d+\,\d*$/', $mVal)) {
			return (float)str_replace(',', '.', $mVal);
		}
		return $mVal;
	}

	/**
	 * Build a [display-title => API attribute name] map for the category-independent
	 * attributes, from the cached GetCategoryIndependentAttributes response. Used to
	 * rekey the CI submit payload to the API `name` (OriginRegion, TrademarkId, …),
	 * matching v3.
	 *
	 * @param int $mpID
	 * @return array
	 */
	public static function getCategoryIndependentNameMap($mpID) {
		$aMap = array();
		try {
			$aResult = MagnaConnector::gi()->submitRequestCached(array(
				'ACTION'        => 'GetCategoryIndependentAttributes',
				'SUBSYSTEM'     => 'Temu',
				'MARKETPLACEID' => (int)$mpID,
			), 86400);
			if (!empty($aResult['DATA']['attributes']) && is_array($aResult['DATA']['attributes'])) {
				foreach ($aResult['DATA']['attributes'] as $sTitle => $aAttr) {
					if (isset($aAttr['name']) && $aAttr['name'] !== '') {
						$aMap[$sTitle] = $aAttr['name'];
					}
				}
			}
		} catch (Exception $e) {
			// leave map empty on failure; callers fall back to the original key
		}
		return $aMap;
	}

	/**
	 * Build the product-data array used by AttributesMatchingHelper::convertMatchingToNameValue()
	 * to resolve UseShopValues / FreeText attributes (weight, dimensions, title, ...).
	 * Configures MLProduct the same way AmazonCheckinSubmit::setUpMLProduct() does so that
	 * Weight and BasePrice are populated as array('Value'=>..,'Unit'=>..).
	 *
	 * For a SIMPLE product getProductById() returns:
	 *   Weight   => array('Value'=>float,'Unit'=>'kg') when products_weight > 0; array() otherwise.
	 *   BasePrice=> array('Value'=>float,'Unit'=>string) when VPE enabled+configured; array() otherwise.
	 *   Title, EAN, ProductId, ProductsModel, ManufacturerId are always present at the top level.
	 *
	 * @param int $mpID
	 * @param int $pID
	 * @return array
	 */
	public static function getProductDataForMatching($mpID, $pID) {
		$iLang = getDBConfigValue('temu.lang', $mpID,
			isset($_SESSION['magna']['selected_language']) ? $_SESSION['magna']['selected_language'] : 2);

		$aOptionsTmp = array(
			'sameVariationsToAttributes' => true,
			'purgeVariations'            => true,
		);
		if (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties') {
			$aOptionsTmp['useGambioProperties'] = true;
		}

		$aProduct = MLProduct::gi()->setLanguage($iLang)->getProductById((int)$pID, $aOptionsTmp);
		return is_array($aProduct) ? $aProduct : array();
	}

	/**
	 * Fetch marketplace attributes for a given Temu category.
	 * Called by base class getMPVariations() when loading the attribute matching table.
	 */
	protected function getAttributesFromMP($category, $additionalData = null, $customIdentifier = '') {
		$data = array();
		try {
			$result = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'GetCategoryDetails',
				'SUBSYSTEM' => 'Temu',
				'MARKETPLACEID' => $this->mpId,
				'DATA' => array('CategoryID' => $category),
			));
			if (!empty($result['DATA'])) {
				$data = $result['DATA'];

				// Add variation dimensions as additional attributes (v3 pattern)
				if (!empty($data['variation_details'])) {
					foreach ($data['variation_details'] as $aDimension) {
						$sKey = 'variation_dim_' . $aDimension['id'];
						$aValues = array();
						if (!empty($aDimension['specList'])) {
							foreach ($aDimension['specList'] as $aSpec) {
								$aValues[(string)$aSpec['specId']] = $aSpec['specName'];
							}
						}
						if (!isset($data['attributes'])) {
							$data['attributes'] = array();
						}
						$data['attributes'][$sKey] = array(
							'title' => $aDimension['name'],
							'mandatory' => !empty($aDimension['required']),
							'type' => !empty($aDimension['type']) ? $aDimension['type'] : 'text',
							'multi' => !empty($aDimension['multi']),
							'values' => $aValues,
						);
					}
				}

				// Add skip_variations theme option
				if (!isset($data['variation_details'])) {
					$data['variation_details'] = array();
				}
				$data['variation_details']['skip_variations'] = array(
					'name' => defined('ML_GENERAL_VARIATION_THEME_SKIP_VARIATIONS')
						? ML_GENERAL_VARIATION_THEME_SKIP_VARIATIONS
						: 'Keine Varianten',
					'attributes' => array(),
				);
			}
		} catch (MagnaException $e) {
			$e->setCriticalStatus(false);
		}

		if (!is_array($data) || !isset($data['attributes'])) {
			return array();
		}
		return $data;
	}
}
