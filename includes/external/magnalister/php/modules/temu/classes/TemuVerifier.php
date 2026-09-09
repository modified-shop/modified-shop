<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_MODULES.'temu/TemuHelper.php');

/**
 * TemuVerifier — runs a VerifyAddItems call against the magnalister API
 * and records Verified='OK'/'ERROR' on the prepare row.
 *
 * Product data is fetched via MLProduct::gi()->setLanguage()->getProductById(),
 * which returns CAPITALISED keys (Price, Quantity, Title, …).
 * These are mapped into the lowercase $aBase expected by TemuHelper::buildSubmitData().
 *
 * NOTE: VerifyAddItems live acceptance must be confirmed during the E2E test
 *       (task-3-report.md). The request shape mirrors the regular AddItems call
 *       used in TemuCheckinSubmit / CheckinSubmit.php.
 */
class TemuVerifier {

	/**
	 * Verify a single product against the Temu marketplace.
	 *
	 * @param int $mpID  Marketplace configuration ID
	 * @param int $pID   Shop products_id
	 * @return array array('ok' => bool, 'errors' => array of strings)
	 */
	public static function verifyPID($mpID, $pID) {
		$aPrepare = MagnaDB::gi()->fetchRow("
			SELECT * FROM ".TABLE_MAGNA_TEMU_PREPARE."
			 WHERE mpID = '".(int)$mpID."' AND products_id = '".(int)$pID."' AND PrepareType = 'Apply'
		");
		if (empty($aPrepare)) {
			return array('ok' => false, 'errors' => array('Prepare row missing'));
		}

		/*
		 * Fetch the shop product using the Temu-configured language, identical
		 * to how TemuProductSaver::preparePropertiesRow() does it.
		 * setLanguage() MUST be called before getProductById() or an exception
		 * is thrown.  getProductById() returns CAPITALISED keys (Price, Quantity, …).
		 */
		$iLang = getDBConfigValue(
			'temu.lang',
			$mpID,
			isset($_SESSION['magna']['selected_language']) ? $_SESSION['magna']['selected_language'] : 2
		);
		// Temu requires the NET price — apply the Temu price config (IncludeTax=false) so the
		// verified price matches the AddItems upload price (which uses the same config).
		// setQuantityConfig too so each variant's Quantity is Temu-adjusted (stock/stocksub/
		// lump + MaxQuantity) the same way the AddItems upload adjusts it — otherwise Verify
		// would validate raw variant stock while upload sends the clamped value.
		// Load variants with the SAME options TemuCheckinSubmit::setUpMLProduct() uses, so Verify
		// validates the identical multi-variant payload the AddItems upload sends. Without
		// useGambioProperties a properties-based product returns no Variations and Verify would
		// check only a single master item.
		$aProduct = MLProduct::gi()
			->setLanguage($iLang)
			->setPriceConfig(TemuHelper::loadPriceSettings($mpID))
			->setQuantityConfig(TemuHelper::loadQuantitySettings($mpID))
			->useMultiDimensionalVariations(true)
			->getProductById((int)$pID, array(
				'includeVariations'          => true,
				'sameVariationsToAttributes' => false,
				'purgeVariations'            => true,
				'useGambioProperties'        => (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties'),
			));
		if (!is_array($aProduct)) {
			$aProduct = array();
		}

		/*
		 * Build the $aBase array with lowercase keys as expected by
		 * TemuHelper::buildSubmitData().  Map capitalised MLProduct keys:
		 *   Price    → price
		 *   Quantity → quantity   (MLProduct::buildSelectFields maps products_quantity → 'Quantity')
		 * currency is left null so the builder falls back to getCurrencyFromMarketplace().
		 */
		$aBase = array(
			'price'    => isset($aProduct['Price'])    ? $aProduct['Price']    : $aPrepare['Price'],
			// Apply the same quantity config the real upload uses. The AddItems quantity is
			// config-adjusted in MagnaCompatibleSummaryView (stock/stocksub/lump) + TemuSummaryView
			// (maxquantity cap); getQuantityForTemu() replicates that. Sending raw products_quantity
			// here made Verify validate a different quantity than what gets uploaded (e.g. a
			// stocksub that clamps upload to 0 while verify passed on the raw stock).
			'quantity' => TemuHelper::getQuantityForTemu(
				isset($aProduct['Quantity']) ? $aProduct['Quantity'] : 0, $mpID),
			'currency' => getCurrencyFromMarketplace($mpID),
		);

		// One payload item per variant (or a single item for a simple product), matching
		// exactly what TemuCheckinSubmit uploads, so Verify validates the real payload.
		$aItems = TemuHelper::buildSubmitItems(
			$mpID, $pID, $aPrepare, $aBase,
			isset($aProduct['Variations']) ? $aProduct['Variations'] : null
		);

		$aErrorRecords = array();
		try {
			/*
			 * Request shape mirrors the regular AddItems request built in
			 * CheckinSubmit::sendRequest() / TemuCheckinSubmit, confirmed via
			 * temu.php (IsAuthed) and MagnaCompatibleCheckinSubmit::generateRequestHeader().
			 * SUBSYSTEM and MARKETPLACEID are explicit so this works outside the
			 * normal checkin session context.
			 * DATA is an array-of-product-arrays, identical to AddItems.
			 */
			$result = MagnaConnector::gi()->submitRequest(array(
				'ACTION'        => 'VerifyAddItems',
				'SUBSYSTEM'     => 'Temu',
				'MARKETPLACEID' => (int)$mpID,
				'DATA'          => $aItems,
			));
			// Some responses may carry errors without throwing.
			$aErrorRecords = TemuHelper::collectApiErrors($result, 'VerifyAddItems');
		} catch (MagnaException $e) {
			// MagnaConnector throws on API errors — the full response (top-level ERRORS[] AND
			// the per-SKU DATA.Errors detail) lives on the exception, not in $result. Parse it
			// so the actionable field-level messages ("Base Price …", "Handling Time …") are
			// captured, not just the generic "[val_verification_failed]" wrapper (getMessage()).
			$aResp = $e->getResponse();
			$aErrorRecords = TemuHelper::collectApiErrors(is_array($aResp) ? $aResp : array(), 'VerifyAddItems');
			if (empty($aErrorRecords)) {
				$aErrorRecords[] = array(
					'sku'     => isset($aPrepare['SKU']) ? $aPrepare['SKU'] : '',
					'message' => $e->getMessage(),
					'origin'  => 'VerifyAddItems',
				);
			}
		} catch (Exception $e) {
			$aErrorRecords[] = array(
				'sku'     => isset($aPrepare['SKU']) ? $aPrepare['SKU'] : '',
				'message' => $e->getMessage(),
				'origin'  => 'VerifyAddItems',
			);
		}

		$aErrors = array();
		foreach ($aErrorRecords as $aRec) {
			$aErrors[] = $aRec['message'];
		}
		$bOk = empty($aErrorRecords);

		// Surface VerifyAddItems failures (API ERRORS, per-SKU DATA.Errors, and
		// exceptions/timeouts) in the Error Log tab, not only in the per-product
		// PrepareError column. Mirrors TemuHelper::processCheckinErrors() (AddItems path).
		if (!$bOk) {
			$sFallbackSku = isset($aPrepare['SKU']) ? $aPrepare['SKU'] : '';
			foreach ($aErrorRecords as $aRec) {
				$sSku = ($aRec['sku'] !== '') ? $aRec['sku'] : $sFallbackSku;
				MagnaDB::gi()->insert(TABLE_MAGNA_COMPAT_ERRORLOG, array(
					'mpID'           => (int)$mpID,
					'origin'         => isset($aRec['origin']) ? $aRec['origin'] : 'VerifyAddItems',
					'errormessage'   => $aRec['message'],
					'dateadded'      => gmdate('Y-m-d H:i:s'),
					'additionaldata' => serialize(array('SKU' => $sSku)),
				));
			}
		}

		MagnaDB::gi()->update(TABLE_MAGNA_TEMU_PREPARE, array(
			'Verified'     => $bOk ? 'OK' : 'ERROR',
			'PrepareError' => $bOk ? '' : implode("\n", $aErrors),
		), array(
			'mpID'        => (int)$mpID,
			'products_id' => (int)$pID,
			'PrepareType' => 'Apply',
		));

		return array('ok' => $bOk, 'errors' => $aErrors);
	}
}
