<?php
/**
 * Standalone regression tests for the shipping template in the Temu upload items.
 *
 * Run: php magnalister/php/modules/temu/tests/TemuSubmitCostTemplateTest.php
 *
 * The shipping template comes from the "Shipping Template" category-independent matching and
 * is sent in CategoryIndependentAttributes. It is also sent as root CostTemplateId (like v3):
 * the API merges the variant items of a product into one master item and reads the root value
 * first, so without it a variant product reaches Temu without a shipping template.
 */

error_reporting(E_ALL);

define('_VALID_XTC', true);
define('DIR_MAGNALISTER_MODULES', dirname(__FILE__) . '/stubs/');
define('DIR_MAGNALISTER_INCLUDES', dirname(__FILE__) . '/stubs/');
define('HTTP_CATALOG_SERVER', 'https://shop.example');
define('DIR_WS_CATALOG', '/shop/');

function getDBConfigValue($sKey, $mpID, $mDefault = null) {
	return $mDefault;
}

function getCurrencyFromMarketplace($mpID) {
	return 'EUR';
}

class MLProduct {
	public static function gi() { return new self(); }
	public function setLanguage($iLang) { return $this; }
	public function getProductById($pID, $aOptions = array()) { return array('ProductId' => $pID, 'Title' => 'T-Shirt'); }
}

/** GetCategoryIndependentAttributes: the title of the matching row => the API name. */
class MagnaConnector {
	public static function gi() { return new self(); }
	public function submitRequestCached($aRequest, $iLifetime) {
		return array('DATA' => array('attributes' => array(
			'Shipping Template' => array('name' => 'CostTemplateId'),
			'Brand'             => array('name' => 'TrademarkId'),
		)));
	}
}

require_once(dirname(dirname(__FILE__)) . '/TemuHelper.php'); // real TemuHelper

$GLOBALS['__pass'] = 0; $GLOBALS['__fail'] = 0;
function check($label, $cond) {
	if ($cond) { $GLOBALS['__pass']++; echo "  PASS  $label\n"; }
	else       { $GLOBALS['__fail']++; echo "  FAIL  $label\n"; }
}

$aPrepare = array(
	'SKU'             => 'ABC1231xuz',
	'Title'           => 'T-Shirt',
	'Description'     => 'Desc',
	'PrimaryCategory' => '',
	'Images'          => '',
	'EAN'             => '',
);
$aBase = array('price' => 19.99, 'quantity' => 100, 'currency' => 'EUR');
$aVariants = array(
	array('VariationId' => '1', 'MarketplaceSku' => 'ABC1231xuz-s-gold', 'Price' => 21, 'Quantity' => 100, 'Variation' => array(array('Name' => 'Size', 'Value' => 'S'))),
	array('VariationId' => '2', 'MarketplaceSku' => 'ABC1231xuz-s-red', 'Price' => 21, 'Quantity' => 37, 'Variation' => array(array('Name' => 'Size', 'Value' => 'M'))),
);
$sTemplate = 'LFT-11890644245094420183';

// The variation dimension of the prepare: without one the product is uploaded as a simple item
TemuLongtextStore::$aStored = array(
	TemuLongtextStore::FIELD_SHOP_VARIATION => json_encode(array(
		'variation_dim_100' => array('Code' => 'freetext', 'Values' => 'S', 'AttributeName' => 'Size'),
	)),
);

function storeIndependentMatching($aMatching) {
	TemuLongtextStore::$aRow = array('CategoryIndependentShopVariationId' => json_encode($aMatching));
}

echo "== Test 1: variant product with a shipping template ==\n";
storeIndependentMatching(array(
	'Shipping Template' => array('Code' => 'attribute_value', 'Values' => $sTemplate),
	'Brand'             => array('Code' => 'attribute_value', 'Values' => '123'),
));
$aItems = TemuHelper::buildSubmitItems(43705, 1, $aPrepare, $aBase, $aVariants);
$bAll = count($aItems) === 2;
foreach ($aItems as $aItem) {
	$bAll = $bAll && isset($aItem['CostTemplateId']) && $aItem['CostTemplateId'] === $sTemplate;
}
check('every variant item sends the template as root CostTemplateId', $bAll);
check('the template stays in CategoryIndependentAttributes', $aItems[0]['CategoryIndependentAttributes']['CostTemplateId'] === $sTemplate);

echo "== Test 2: simple product with a shipping template ==\n";
$aItems = TemuHelper::buildSubmitItems(43705, 1, $aPrepare, $aBase, null);
check('the simple item sends the template as root CostTemplateId', count($aItems) === 1 && isset($aItems[0]['CostTemplateId']) && $aItems[0]['CostTemplateId'] === $sTemplate);

echo "== Test 3: no shipping template matched ==\n";
storeIndependentMatching(array(
	'Shipping Template' => array('Code' => 'attribute_value', 'Values' => ''),
	'Brand'             => array('Code' => 'attribute_value', 'Values' => '123'),
));
$aItems = TemuHelper::buildSubmitItems(43705, 1, $aPrepare, $aBase, $aVariants);
check('no root CostTemplateId is sent (never an empty one)', !array_key_exists('CostTemplateId', $aItems[0]));

echo "== Test 4: no category-independent matching ==\n";
TemuLongtextStore::$aRow = array();
$aItems = TemuHelper::buildSubmitItems(43705, 1, $aPrepare, $aBase, $aVariants);
check('no root CostTemplateId without a matching', !array_key_exists('CostTemplateId', $aItems[0]));

TemuLongtextStore::$aStored = array();

echo "\n" . ($GLOBALS['__fail'] === 0 ? "ALL PASS" : "FAILURES") . ": {$GLOBALS['__pass']} passed, {$GLOBALS['__fail']} failed\n";
exit($GLOBALS['__fail'] === 0 ? 0 : 1);
