<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

/* Variations-Tabelle aufbauen */
define('MP_DEBUG', true);
define('MP_SHOW_WARNINGS', true);

if (!defined('E_RECOVERABLE_ERROR')) define('E_RECOVERABLE_ERROR', 0x1000);
if (!defined('E_DEPRECATED'))        define('E_DEPRECATED',        0x2000);
if (!defined('E_USER_DEPRECATED'))   define('E_USER_DEPRECATED',   0x4000);

require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/VariationsCalculator.php');

function buildVariationsTable() {

	$skutype = ('artNr' == getDBConfigValue('general.keytype', '0'))?'model':'id';
	$vc = new VariationsCalculator(array(
		'skubasetype' => $skutype, //  [model | id]
		'skuvartype'  => $skutype, //  [model | id]
	));

	$productIdList = MagnaDB::gi()->fetchArray(
		'SELECT products_id FROM '.TABLE_PRODUCTS.' ORDER BY products_id'
	);
	MagnaDB::gi()->query('update '.TABLE_MAGNA_VARIATIONS.' SET variation_quantity = 0');
	foreach ($productIdList as $product) {
		$permutations = $vc->getVariationsByPID($product['products_id']);
		if (!$permutations) continue; # Artikel ohne Varianten
		MagnaDB::gi()->batchinsert(TABLE_MAGNA_VARIATIONS, $permutations, true);
	}
}

buildVariationsTable();
