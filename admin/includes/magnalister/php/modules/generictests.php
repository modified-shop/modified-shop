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
 * $Id: generictests.php 2556 2013-05-24 09:16:36Z tim.neumann $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

header('Content-Type: text/plain; charset=utf-8');
#echo "\xEF\xBB\xBF";
//*
if (!function_exists('ml_debug_out')) {
	function ml_debug_out($m) {
		echo $m;
		flush();
	}
}
//*/
#require_once(DIR_MAGNALISTER_INCLUDES.'testing/ordertest.php');
#require_once(DIR_MAGNALISTER_INCLUDES.'testing/inventoryEdit.php');
#require_once(DIR_MAGNALISTER_INCLUDES.'testing/syncAmazonOrder.php');
#require_once(DIR_MAGNALISTER_INCLUDES.'testing/checkinTest.php');
#require_once(DIR_MAGNALISTER_INCLUDES.'testing/genEANForSelection.php');
#require_once(DIR_MAGNALISTER_INCLUDES.'testing/orderimport.php');
#require_once(DIR_MAGNALISTER_INCLUDES.'testing/callback.php');

function verifyUniqueSKUs() {
	if (getDBConfigValue('general.keytype', '0', 'pID') != 'artNr') {
		return true;
	}

	# Verify products
	$countProductsIDs = MagnaDB::gi()->fetchOne('
		SELECT COUNT(DISTINCT products_id) FROM '.TABLE_PRODUCTS
	);
	$countProductsModels = MagnaDB::gi()->fetchOne('
		SELECT COUNT(DISTINCT products_model) FROM '.TABLE_PRODUCTS.' 
		 WHERE products_model <> \'\' AND products_model IS NOT NULL
	');
	#echo '$countProductsIDs['.$countProductsIDs.'] != $countProductsModels['.$countProductsModels.']'."\n";
	if ($countProductsIDs != $countProductsModels) {
		return false;
	}
	
	# Verify attributes
	$countAttributesIDs = MagnaDB::gi()->fetchOne('
		SELECT COUNT(DISTINCT products_attributes_id) FROM '.TABLE_PRODUCTS_ATTRIBUTES
	);
	$countAttributesModels = MagnaDB::gi()->fetchOne('
		SELECT COUNT(DISTINCT attributes_model) FROM '.TABLE_PRODUCTS_ATTRIBUTES.' 
		 WHERE attributes_model <> \'\' AND attributes_model IS NOT NULL
	');
	#echo '$countAttributesIDs['.$countAttributesIDs.'] != $countAttributesModels['.$countAttributesModels.']'."\n";
	if ($countAttributesIDs != $countAttributesModels) {
		return false;
	}
	
	# Verify products in compinations with attributes
	$zort = (int)MagnaDB::gi()->fetchOne('
		SELECT COUNT(DISTINCT pa.products_id) 
		  FROM '.TABLE_PRODUCTS.' p, '.TABLE_PRODUCTS_ATTRIBUTES.' pa
		 WHERE p.products_model=pa.attributes_model
		       AND attributes_model <> \'\'
		       AND attributes_model IS NOT NULL
	');
	
	return ($zort == 0);
}

#var_dump(verifyUniqueSKUs());
/*
$aID = 24;
$sku = magnaAID2SKU($aID);
echo var_dump_pre($sku, $aID);
$aID = magnaSKU2aID($sku);
echo var_dump_pre($aID, $sku);
*/
/*
$mpID = 395;
$pID = 4641;

require_once(DIR_MAGNALISTER_MODULES.'ebay/ebayFunctions.php');

loadDBConfig($mpID);
//echo print_m(getVariations($pID));

require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/VariationsCalculator.php');
$skutype = (getDBConfigValue('general.keytype', '0') == 'artNr') ? 'model' : 'id';
$vc = new VariationsCalculator(array(
	'skubasetype' => $skutype,
	'skuvartype'  => $skutype,
));
$d = $vc->getVariationsByPIDFromDB($pID, true);
arrayEntitiesToUTF8($d);
echo print_m($d, 'd');
*/
/*
class StaticTest {
	public static function test($param1, $param2) {
		echo 'works! '.$param1.' '.$param2;
	}
}

$meh = 'static';
//ucfirst($meh).'Test'::test();
$path = 'C:\server\var/www/bla.php';
#call_user_func(ucfirst($meh).'Test::test', "a", "b");
if (($path[0] != '/') && !preg_match('/^[a-zA-Z]:\\\/', $path)) {
	echo 'nope';
} else {
	echo 'yup';
}
*/

var_dump(magnaGetTimezoneOffset('Europe/Berlin'));

echo '
----------------------------------------------------
 Entire page served in '.microtime2human(microtime(true) -  $_executionTime).'.
----------------------------------------------------
 Updater Time: '.microtime2human($_updaterTime).'.
 API-Request Time: '.microtime2human(MagnaConnector::gi()->getRequestTime()).'.
 Processing Time: '.microtime2human(microtime(true) -  $_executionTime - $_updaterTime - MagnaConnector::gi()->getRequestTime() - MagnaDB::gi()->getRealQueryTime()).'.
----------------------------------------------------
 '.((memory_usage() !== false) ? 'Max. Memory used: '.memory_usage().'.' : '').'
----------------------------------------------------
 DB-Stats:
 	Queries used: '.MagnaDB::gi()->getQueryCount().'
 	Real query time: '.microtime2human(MagnaDB::gi()->getRealQueryTime()).'
----------------------------------------------------
';
if (class_exists('MagnaConnector') && true) {
	$tpR = MagnaConnector::gi()->getTimePerRequest();
	if (!empty($tpR)) {
		foreach ($tpR as $item) {
			echo print_m($item['request'], microtime2human($item['time']).' ['.$item['status'].']', true)."\n";
		}
		echo '----------------------------------------------------'."\n";
	}
	
}
if (class_exists('MagnaDB') && false) {
	$tpR = MagnaDB::gi()->getTimePerQuery();
	if (!empty($tpR)) {
		foreach ($tpR as $item) {
			echo print_m(ltrim(rtrim($item['query'], "\n"), "\n"), microtime2human($item['time']), true)."\n";
		}
		echo '----------------------------------------------------'."\n";
	}
}
include_once(DIR_WS_INCLUDES . 'application_bottom.php');
exit();
