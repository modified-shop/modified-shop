<?php
/**
 * -----------------------------------------------------------------------------
 * (c) 2010 - 2026 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 *
 * Pure, stateless resolver: builds the per-variant Temu `SpecDetails` for one
 * variant, given the parent's `variation_dim_<id>` matching and THIS variant's
 * shop value for each dimension. Generalises TemuHelper::buildSpecDetails() (which
 * picks one representative spec per dimension) to select the spec matching a
 * specific variant's value — so different variants yield different specIds
 * (Blau -> blue, Rot -> red). No shop/DB dependency; unit-testable in isolation.
 * PHP 5.2 compatible.
 */
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

class TemuVariantSpecResolver {

	/**
	 * @param array $aVariationDims     variation_dim_<id> => matching entry (Code/Values/UseShopValues)
	 * @param array $aVariantValueByDim dimId => this variant's shop value (string)
	 * @param array $aVariationDetails  category variation_details (each: id, type, specList[]) — used to
	 *                                  route each value to specId (preset) or specName (custom/free-text).
	 *                                  Empty ⇒ legacy specId placement (backward compatible).
	 * @param callable|null $mNormalizer  optional value normaliser applied to every resolved value
	 *                                  (TemuHelper::normalizeAttributeValue), so the per-variant
	 *                                  path emits the same types as TemuHelper::buildSpecDetails()
	 *                                  ("1.5" → 1.5, "true" → true). Injected rather than called
	 *                                  directly to keep this class shop/DB-free and unit-testable.
	 * @return array  list of array('parentSpecId' => int, 'specId'|'specName' => mixed)
	 */
	public static function resolve($aVariationDims, $aVariantValueByDim, $aVariationDetails = array(), $mNormalizer = null) {
		$aRaw = array();
		if (empty($aVariationDims) || !is_array($aVariationDims)) {
			return $aRaw;
		}
		if (!is_array($aVariantValueByDim)) {
			$aVariantValueByDim = array();
		}

		foreach ($aVariationDims as $sKey => $aAttr) {
			if (strpos($sKey, 'variation_dim_') !== 0 || !is_array($aAttr)) {
				continue;
			}
			$iDimId = (int)substr($sKey, strlen('variation_dim_'));
			$mSpec  = null;

			if (isset($aAttr['Code']) && $aAttr['Code'] === 'attribute_value'
				&& isset($aAttr['Values']) && !is_array($aAttr['Values']) && $aAttr['Values'] !== ''
			) {
				// Literal value — same for all variants (e.g. a fixed single-size). A preset value
				// id for a select spec, or custom text for a free-text spec (routing decides).
				$mSpec = $aAttr['Values'];
			} elseif (isset($aAttr['Values']) && is_array($aAttr['Values'])) {
				$sWant = isset($aVariantValueByDim[$iDimId]) ? (string)$aVariantValueByDim[$iDimId] : null;
				if ($sWant !== null && $sWant !== '') {
					foreach ($aAttr['Values'] as $aMap) {
						if (!is_array($aMap) || !isset($aMap['Shop']['Value'])) {
							continue;
						}
						if ((string)$aMap['Shop']['Value'] !== $sWant) {
							continue;
						}
						if (isset($aMap['Marketplace']['Key']) && $aMap['Marketplace']['Key'] !== '') {
							$mSpec = $aMap['Marketplace']['Key'];
						} elseif (!empty($aAttr['UseShopValues'])) {
							$mSpec = $aMap['Shop']['Value'];
						}
						break;
					}
				}
			}

			if ($mSpec === null || $mSpec === '') {
				continue;
			}
			if ($mNormalizer !== null && is_callable($mNormalizer)) {
				$mSpec = call_user_func($mNormalizer, $mSpec);
			}
			$aRaw[] = array('parentSpecId' => $iDimId, 'value' => $mSpec);
		}
		return self::routeSpecDetails($aRaw, $aVariationDetails);
	}

	/**
	 * Route raw (parentSpecId, value) pairs to Temu SpecDetails entries. Verbatim port of the v3
	 * ML_Temu_Helper_Model_Table_Temu_PrepareData::routeSpecDetails() so v2, v2veyton and v3 agree.
	 *
	 * Placement is decided by the spec's control TYPE from variation_details:
	 *   - 'select'                 → preset-only spec         → specId
	 *   - 'text' / 'selectAndText' → custom value is expected → specName
	 * A custom value sent as `specId` is what triggers Temu errorCode 150010076 ("SKU specification
	 * value ID is incorrect"); `specName` is always accepted for custom values. When a dimension is
	 * not present in $aVariationDetails (details unavailable), the value keeps the legacy `specId`
	 * placement, so the central API stays the single backstop and no regression is introduced.
	 *
	 * @param array $aRawSpecs         list of array('parentSpecId' => int, 'value' => mixed)
	 * @param array $aVariationDetails category variation_details (each: id, type, specList[])
	 * @return array SpecDetails entries (each array('parentSpecId'=>int, 'specId'|'specName'=>mixed))
	 */
	public static function routeSpecDetails($aRawSpecs, $aVariationDetails) {
		if (empty($aRawSpecs) || !is_array($aRawSpecs)) {
			return array();
		}
		if (!is_array($aVariationDetails)) {
			$aVariationDetails = array();
		}

		// Index parentSpecId => array('type' => controlType, 'presets' => array(specId(str) => true)).
		$aDimsById = array();
		foreach ($aVariationDetails as $aDimension) {
			if (!is_array($aDimension) || !isset($aDimension['id'])) {
				continue;
			}
			$iId = (int)$aDimension['id'];
			$aPresets = array();
			if (!empty($aDimension['specList']) && is_array($aDimension['specList'])) {
				foreach ($aDimension['specList'] as $aSpec) {
					if (is_array($aSpec) && isset($aSpec['specId'])) {
						$aPresets[(string)$aSpec['specId']] = true;
					}
				}
			}
			$aDimsById[$iId] = array(
				'type'    => isset($aDimension['type']) ? strtolower((string)$aDimension['type']) : '',
				'presets' => $aPresets,
			);
		}

		$aSpecDetails = array();
		foreach ($aRawSpecs as $aRaw) {
			if (!is_array($aRaw) || !isset($aRaw['parentSpecId'])) {
				continue;
			}
			$iParent = (int)$aRaw['parentSpecId'];
			$mValue  = isset($aRaw['value']) ? $aRaw['value'] : null;
			if ($mValue === null || $mValue === '') {
				continue;
			}

			if (!isset($aDimsById[$iParent])) {
				// Unknown dimension (no category data) → keep legacy specId placement.
				$aSpecDetails[] = array('parentSpecId' => $iParent, 'specId' => $mValue);
				continue;
			}

			$sType = $aDimsById[$iParent]['type'];
			if ($sType === 'select') {
				$aSpecDetails[] = array('parentSpecId' => $iParent, 'specId' => $mValue);
			} elseif ($sType === 'text' || $sType === 'selectandtext') {
				$aSpecDetails[] = array('parentSpecId' => $iParent, 'specName' => (string)$mValue);
			} elseif (isset($aDimsById[$iParent]['presets'][(string)$mValue])) {
				// Unknown/missing control type → fall back to preset-list membership.
				$aSpecDetails[] = array('parentSpecId' => $iParent, 'specId' => $mValue);
			} else {
				$aSpecDetails[] = array('parentSpecId' => $iParent, 'specName' => (string)$mValue);
			}
		}
		return $aSpecDetails;
	}

	/**
	 * Map ONE variant's shop option values onto the variation dimensions, i.e. build
	 * the `dimId => shopValue` map that resolve() consumes. For each dimension, the
	 * variant's value = the option whose id/name appears in that dimension's mapping
	 * Values (matched by Shop.Key == ValueId, or Shop.Value == Value). Literal
	 * (`attribute_value`) dimensions need no value (resolve() emits the literal).
	 *
	 * @param array $aVariantVariation MLProduct variant['Variation'] — list of
	 *                                 array('NameId','Name','ValueId','Value')
	 * @param array $aVariationDims     variation_dim_<id> => matching entry
	 * @return array  dimId => shop value (string)
	 */
	public static function variantValueByDim($aVariantVariation, $aVariationDims) {
		$aOut = array();
		if (empty($aVariantVariation) || !is_array($aVariantVariation)
			|| empty($aVariationDims) || !is_array($aVariationDims)) {
			return $aOut;
		}
		foreach ($aVariationDims as $sKey => $aAttr) {
			if (strpos($sKey, 'variation_dim_') !== 0 || !is_array($aAttr)) {
				continue;
			}
			if (!isset($aAttr['Values']) || !is_array($aAttr['Values'])) {
				continue; // literal / no mapping list — resolve() handles it
			}
			$iDimId = (int)substr($sKey, strlen('variation_dim_'));
			foreach ($aVariantVariation as $aOpt) {
				if (!is_array($aOpt)) {
					continue;
				}
				$sValId = isset($aOpt['ValueId']) ? (string)$aOpt['ValueId'] : '';
				$sVal   = isset($aOpt['Value']) ? (string)$aOpt['Value'] : '';
				foreach ($aAttr['Values'] as $aMap) {
					if (!is_array($aMap) || !isset($aMap['Shop'])) {
						continue;
					}
					$sShopKey = isset($aMap['Shop']['Key']) ? (string)$aMap['Shop']['Key'] : '';
					$sShopVal = isset($aMap['Shop']['Value']) ? (string)$aMap['Shop']['Value'] : '';
					if (($sShopKey !== '' && $sShopKey === $sValId)
						|| ($sShopVal !== '' && $sShopVal === $sVal)) {
						// Return the STORED Shop.Value (what resolve() re-matches against), not the
						// variant's own label — the product may be fetched in a different language
						// (e.g. variant "Rot" vs stored match "Red"), and the id-based match above
						// already confirmed this is the right dimension. Falling back to the variant
						// label only when the stored value is empty.
						$aOut[$iDimId] = ($sShopVal !== '') ? $sShopVal : $sVal;
						break 2;
					}
				}
			}
		}
		return $aOut;
	}
}
