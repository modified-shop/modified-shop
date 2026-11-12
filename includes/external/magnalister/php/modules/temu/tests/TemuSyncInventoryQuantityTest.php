<?php
/**
 * Standalone regression tests for the Temu stock/price sync payload.
 *
 * Run: php magnalister/php/modules/temu/tests/TemuSyncInventoryQuantityTest.php
 *
 * The Temu API reads the stock of an UpdateItems item from Quantity, like OTTO, Metro and Etsy.
 * The MagnaCompatible base sends it as NewQuantity {Mode, Value}, so the Temu sync must convert
 * it (and rename Price to BasePrice, the net price Temu expects).
 */

error_reporting(E_ALL);

define('_VALID_XTC', true);
define('DIR_MAGNALISTER_MODULES', dirname(__FILE__) . '/stubs/');
define('DIR_MAGNALISTER_INCLUDES', dirname(__FILE__) . '/stubs/');

require_once(dirname(dirname(__FILE__)) . '/crons/TemuSyncInventory.php'); // real TemuSyncInventory

$GLOBALS['__pass'] = 0; $GLOBALS['__fail'] = 0;
function check($label, $cond) {
	if ($cond) { $GLOBALS['__pass']++; echo "  PASS  $label\n"; }
	else       { $GLOBALS['__fail']++; echo "  FAIL  $label\n"; }
}

class TestableTemuSyncInventory extends TemuSyncInventory {
	public function customFields($data) {
		$this->updateCustomFields($data);
		return $data;
	}
}

$oSync = new TestableTemuSyncInventory(87449, 'temu');

echo "== Test 1: stock change ==\n";
$aData = $oSync->customFields(array('NewQuantity' => array('Mode' => 'SET', 'Value' => 19), 'SKU' => '1626/161_8'));
check('the stock is sent as Quantity', isset($aData['Quantity']) && $aData['Quantity'] === 19);
check('NewQuantity is not sent', !array_key_exists('NewQuantity', $aData));

echo "== Test 2: stock 0 ==\n";
$aData = $oSync->customFields(array('NewQuantity' => array('Mode' => 'SET', 'Value' => 0), 'SKU' => 'A'));
check('stock 0 is sent as Quantity 0', array_key_exists('Quantity', $aData) && $aData['Quantity'] === 0);

echo "== Test 3: stock and price change ==\n";
$aData = $oSync->customFields(array('NewQuantity' => array('Mode' => 'SET', 'Value' => 18), 'Price' => 67.22, 'SKU' => 'B'));
check('the stock is sent as Quantity', isset($aData['Quantity']) && $aData['Quantity'] === 18);
check('the price is sent as BasePrice', isset($aData['BasePrice']) && $aData['BasePrice'] === 67.22 && !array_key_exists('Price', $aData));

echo "== Test 4: price change only ==\n";
$aData = $oSync->customFields(array('Price' => 33.61, 'SKU' => 'C'));
check('no Quantity without a stock change', !array_key_exists('Quantity', $aData));
check('the price is sent as BasePrice', isset($aData['BasePrice']) && $aData['BasePrice'] === 33.61);

echo "\n" . ($GLOBALS['__fail'] === 0 ? "ALL PASS" : "FAILURES") . ": {$GLOBALS['__pass']} passed, {$GLOBALS['__fail']} failed\n";
exit($GLOBALS['__fail'] === 0 ? 0 : 1);
