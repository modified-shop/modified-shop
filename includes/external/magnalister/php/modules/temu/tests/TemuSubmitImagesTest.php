<?php
/**
 * Standalone regression tests for the Temu upload images.
 *
 * Run: php magnalister/php/modules/temu/tests/TemuSubmitImagesTest.php
 *
 * Covers:
 *  - MasterDetailImage: the selected images (Prepare) in selected order, duplicates removed,
 *    at most 49, sent on the simple item and inherited by every variant item.
 *  - SKU Images like OTTO: a variant with own images (Gambio properties combi images) sends only
 *    those; a variant without own images keeps the selected images.
 *
 * The real TemuHelper is loaded via the stub tree (tests/stubs/); shop, API and config access are
 * replaced by the minimal fakes below.
 */

error_reporting(E_ALL);

define('_VALID_XTC', true);
define('DIR_MAGNALISTER_MODULES', dirname(__FILE__) . '/stubs/');
define('DIR_MAGNALISTER_INCLUDES', dirname(__FILE__) . '/stubs/');
define('HTTP_CATALOG_SERVER', 'https://shop.example');
define('DIR_WS_CATALOG', '/shop/');

function getDBConfigValue($sKey, $mpID, $mDefault = null) {
	if ($sKey === 'temu.imagepath') {
		return 'https://shop.example/shop/images/product_images/original_images/';
	}
	return $mDefault;
}

function getCurrencyFromMarketplace($mpID) {
	return 'EUR';
}

class MLProduct {
	public static function gi() { return new self(); }
	public function setLanguage($iLang) { return $this; }
	public function getProductById($pID, $aOptions = array()) { return array('ProductId' => $pID, 'Title' => 'Shirt'); }
}

class MagnaConnector {
	public static function gi() { return new self(); }
	public function submitRequestCached($aRequest, $iLifetime) { throw new Exception('no API in tests'); }
}

require_once(dirname(dirname(__FILE__)) . '/TemuHelper.php'); // real TemuHelper

$GLOBALS['__pass'] = 0; $GLOBALS['__fail'] = 0;
function check($label, $cond) {
	if ($cond) { $GLOBALS['__pass']++; echo "  PASS  $label\n"; }
	else       { $GLOBALS['__fail']++; echo "  FAIL  $label\n"; }
}

$sImgBase = 'https://shop.example/shop/images/product_images/original_images/';
$aPrepare = array(
	'SKU'             => 'SHIRT',
	'Title'           => 'Shirt',
	'Description'     => 'Desc',
	'PrimaryCategory' => '',
	'EAN'             => '',
	'Price'           => 10,
	'Images'          => json_encode(array('front.jpg', 'back.jpg', 'front.jpg')),
);
$aBase = array('price' => 10, 'quantity' => 5, 'currency' => 'EUR');

echo "== Test 1: buildMasterDetailImage ==\n";
$aMany = array();
for ($i = 1; $i <= 60; $i++) { $aMany[] = 'https://x/' . $i . '.jpg'; }
$aDetail = TemuHelper::buildMasterDetailImage(array_merge(array('https://x/2.jpg'), $aMany));
check('duplicates removed, selected order kept', $aDetail[0] === 'https://x/2.jpg' && $aDetail[1] === 'https://x/1.jpg' && count(array_unique($aDetail)) === count($aDetail));
check('capped at 49', count($aDetail) === 49);
check('empty input gives empty list', TemuHelper::buildMasterDetailImage(array()) === array());

echo "== Test 2: getVariationImageUrls ==\n";
$aPictures = array(
	array('VariationId' => '11', 'Image' => 'images/product_images/original_images/red_1.jpg',
		'Images' => array('images/product_images/original_images/red_1.jpg', ' ', 'https://cdn.example/red_2.jpg')),
	array('VariationId' => '12', 'Image' => ''), // no image list (older Gambio / no combi images)
	array('VariationId' => '13', 'Images' => array()),
);
$aVarImages = TemuHelper::getVariationImageUrls($aPictures);
check('relative path resolved against the shop URL', isset($aVarImages['11'][0]) && $aVarImages['11'][0] === 'https://shop.example/shop/images/product_images/original_images/red_1.jpg');
check('absolute URL kept, empty entry skipped', count($aVarImages['11']) === 2 && $aVarImages['11'][1] === 'https://cdn.example/red_2.jpg');
check('variations without an image list are left out', !isset($aVarImages['12']) && !isset($aVarImages['13']));
check('null input gives empty map', TemuHelper::getVariationImageUrls(null) === array());

echo "== Test 3: simple product ==\n";
$aItems = TemuHelper::buildSubmitItems(1, 100, $aPrepare, $aBase, null, null);
check('one item', count($aItems) === 1);
check('Images = selected images', $aItems[0]['Images'] === array($sImgBase . 'front.jpg', $sImgBase . 'back.jpg', $sImgBase . 'front.jpg'));
check('MasterDetailImage = selected images without duplicates', $aItems[0]['MasterDetailImage'] === array($sImgBase . 'front.jpg', $sImgBase . 'back.jpg'));
$aNoImages = $aPrepare;
$aNoImages['Images'] = '';
$aItems = TemuHelper::buildSubmitItems(1, 100, $aNoImages, $aBase, null, null);
check('no selection: no Images and no MasterDetailImage', !isset($aItems[0]['Images']) && !isset($aItems[0]['MasterDetailImage']));

echo "== Test 4: variants, like OTTO ==\n";
TemuLongtextStore::$aStored = array(
	TemuLongtextStore::FIELD_SHOP_VARIATION => json_encode(array(
		'variation_dim_100' => array('Code' => 'freetext', 'Values' => '12', 'AttributeName' => 'Quantity'),
	)),
);
$aVariants = array(
	array('VariationId' => '11', 'MarketplaceSku' => 'SHIRT-RED', 'Price' => 10, 'Quantity' => 1, 'Variation' => array(array('Name' => 'Color', 'Value' => 'Red'))),
	array('VariationId' => '12', 'MarketplaceSku' => 'SHIRT-BLUE', 'Price' => 10, 'Quantity' => 1, 'Variation' => array(array('Name' => 'Color', 'Value' => 'Blue'))),
);
$aItems = TemuHelper::buildSubmitItems(1, 100, $aPrepare, $aBase, $aVariants, $aPictures);
$aBySku = array();
foreach ($aItems as $aItem) { $aBySku[$aItem['SKU']] = $aItem; }
check('one item per variant', isset($aBySku['SHIRT-RED']) && isset($aBySku['SHIRT-BLUE']));
check('variant with combi images sends only its own images', $aBySku['SHIRT-RED']['Images'] === $aVarImages['11']);
check('variant without combi images keeps the selected images', $aBySku['SHIRT-BLUE']['Images'] === array($sImgBase . 'front.jpg', $sImgBase . 'back.jpg', $sImgBase . 'front.jpg'));
check('every variant sends the selection as MasterDetailImage', $aBySku['SHIRT-RED']['MasterDetailImage'] === array($sImgBase . 'front.jpg', $sImgBase . 'back.jpg') && $aBySku['SHIRT-BLUE']['MasterDetailImage'] === $aBySku['SHIRT-RED']['MasterDetailImage']);

$aItems = TemuHelper::buildSubmitItems(1, 100, $aPrepare, $aBase, $aVariants);
$bAllSelected = count($aItems) === 2;
foreach ($aItems as $aItem) {
	$bAllSelected = $bAllSelected && $aItem['Images'] === array($sImgBase . 'front.jpg', $sImgBase . 'back.jpg', $sImgBase . 'front.jpg');
}
check('without VariationPictures every variant keeps the selected images', $bAllSelected);
TemuLongtextStore::$aStored = array();

echo "\n" . ($GLOBALS['__fail'] === 0 ? "ALL PASS" : "FAILURES") . ": {$GLOBALS['__pass']} passed, {$GLOBALS['__fail']} failed\n";
exit($GLOBALS['__fail'] === 0 ? 0 : 1);
