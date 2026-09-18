<?php
/**
 * Standalone regression tests for Temu variation-dimension → SpecDetails resolution.
 *
 * Run: php magnalister/php/modules/temu/tests/TemuVariationSpecTest.php
 *
 * Covers the two defects fixed alongside the freetext SpecDetails fix:
 *  - Shop-backed variation dims (Code = products_id / title / database_value, or UseShopValues
 *    with no scalar Values) must be resolved via the shop-value resolver instead of being dropped
 *    from SpecDetails — on the simple/base path AND the per-variant path.
 *  - A database_value matching stores Values as the associative {Table, Column, Alias} config; it
 *    must NOT be mistaken for a Shop/Marketplace mapping list.
 *
 * The real production classes are loaded standalone via a tiny stub tree (tests/stubs/) so no
 * shop framework / DB is required; the shop-value lookup is injected as a mock.
 */

error_reporting(E_ALL);

define('_VALID_XTC', true);
define('DIR_MAGNALISTER_MODULES', dirname(__FILE__) . '/stubs/');
define('DIR_MAGNALISTER_INCLUDES', dirname(__FILE__) . '/stubs/');

require_once(dirname(dirname(__FILE__)) . '/TemuHelper.php'); // real TemuHelper (+ real resolver via stub)

/**
 * Mock shop-value resolver — mirrors the real convertMatchingToNameValue() cases the tests need
 * (products_id, title, database_value) and records which dimensions it was asked to resolve.
 */
class MockAttrMatch {
	public $receivedKeys = array();
	public function convertMatchingToNameValue($aMatching, $aProductData, $bLimit = false) {
		$this->receivedKeys = array_keys($aMatching);
		$aOut = array();
		foreach ($aMatching as $sKey => $aAttr) {
			$sCode = isset($aAttr['Code']) ? $aAttr['Code'] : '';
			switch ($sCode) {
				case 'products_id':   $aOut[$sKey] = $aProductData['ProductId']; break;
				case 'title':         $aOut[$sKey] = $aProductData['Title'];     break;
				case 'database_value':$aOut[$sKey] = 'DBVAL_' . $aAttr['Values']['Column']; break;
				default:              $aOut[$sKey] = isset($aAttr['Values']) && !is_array($aAttr['Values']) ? $aAttr['Values'] : '';
			}
		}
		return $aOut;
	}
}

$GLOBALS['__pass'] = 0; $GLOBALS['__fail'] = 0;
function check($label, $cond) {
	if ($cond) { $GLOBALS['__pass']++; echo "  PASS  $label\n"; }
	else       { $GLOBALS['__fail']++; echo "  FAIL  $label\n"; }
}

$aProductData = array('ProductId' => 2, 'Title' => 'Gambio Manual', 'ProductsModel' => 'test');

// A representative set of variation dimensions covering every matching shape.
$aDims = array(
	'variation_dim_100' => array('Code' => 'freetext',       'Values' => '12', 'AttributeName' => 'Quantity'),
	'variation_dim_200' => array('Code' => 'products_id', 'UseShopValues' => true, 'AttributeName' => 'Color'),
	'variation_dim_300' => array('Code' => 'title',                              'AttributeName' => 'Style'),
	'variation_dim_400' => array('Code' => 'database_value',
	                             'Values' => array('Table' => 'products', 'Column' => 'products_ean', 'Alias' => 'ean'),
	                             'AttributeName' => 'Material'),
	'variation_dim_500' => array('Code' => '2', 'UseShopValues' => true, 'AttributeName' => 'Size',
	                             'Values' => array(
	                                 1 => array('Shop' => array('Key' => '1', 'Value' => 'S'),
	                                            'Marketplace' => array('Key' => 'S', 'Value' => 'S')),
	                             )),
);

echo "== Test 1: resolveVariationDimsShopValues classification & resolution ==\n";
$oMock = new MockAttrMatch();
$aResolved = TemuHelper::resolveVariationDimsShopValues($aDims, $aProductData, $oMock);

// P1#1: shop-attribute-coded dims are resolved (not dropped).
check('products_id resolved to shop ProductId (2)', isset($aResolved['variation_dim_200']['Values']) && (string)$aResolved['variation_dim_200']['Values'] === '2');
check('title resolved to shop Title',               isset($aResolved['variation_dim_300']['Values']) && $aResolved['variation_dim_300']['Values'] === 'Gambio Manual');
// P1#2: database_value config is routed to the resolver (not treated as a mapping list).
check('database_value routed to resolver',          in_array('variation_dim_400', $oMock->receivedKeys, true));
check('database_value resolved value injected',     isset($aResolved['variation_dim_400']['Values']) && $aResolved['variation_dim_400']['Values'] === 'DBVAL_products_ean');
check('resolved dims re-coded as freetext',         $aResolved['variation_dim_200']['Code'] === 'freetext' && $aResolved['variation_dim_400']['Code'] === 'freetext');
// Untouched shapes: scalar freetext + genuine Shop/Marketplace mapping list must NOT be resolved.
check('freetext scalar NOT sent to resolver',       !in_array('variation_dim_100', $oMock->receivedKeys, true));
check('genuine mapping list NOT sent to resolver',  !in_array('variation_dim_500', $oMock->receivedKeys, true));
check('mapping list left untouched',                is_array($aResolved['variation_dim_500']['Values']) && isset($aResolved['variation_dim_500']['Values'][1]['Shop']));

echo "== Test 2: buildSpecDetails emits every dimension (nothing dropped) ==\n";
$aSpec = TemuHelper::buildSpecDetails($aResolved); // empty $aVariationDetails → legacy placement
$aParents = array();
foreach ($aSpec as $e) { $aParents[(int)$e['parentSpecId']] = $e; }
check('Quantity (100) present',      isset($aParents[100]));
check('Color/products_id (200) present', isset($aParents[200]));
check('Style/title (300) present',   isset($aParents[300]));
check('Material/database (400) present', isset($aParents[400]));
check('Size/mapping (500) present',  isset($aParents[500]));

echo "== Test 3: per-variant resolver keeps SpecDetails on every child SKU ==\n";
// Two variants; the shop-backed dims are product-level (same across combos), the mapping axis differs.
foreach (array('S', 'M') as $sVariantSize) {
	$aVarValByDim = array(500 => $sVariantSize); // this variant's value on the Size axis
	$aChildSpec = TemuVariantSpecResolver::resolve($aResolved, $aVarValByDim, array());
	$aChildParents = array();
	foreach ($aChildSpec as $e) { $aChildParents[(int)$e['parentSpecId']] = true; }
	check("variant $sVariantSize: products_id (200) present", isset($aChildParents[200]));
	check("variant $sVariantSize: database (400) present",    isset($aChildParents[400]));
}

echo "\n" . ($GLOBALS['__fail'] === 0 ? "ALL PASS" : "FAILURES") . ": {$GLOBALS['__pass']} passed, {$GLOBALS['__fail']} failed\n";
exit($GLOBALS['__fail'] === 0 ? 0 : 1);
