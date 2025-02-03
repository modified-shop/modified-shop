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
 * $Id: 001.sql.php 650 2011-01-08 22:30:52Z MaW $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

# Tabellen fuer das eBay-Modul

$queries = array();
$functions = array();

/* Tabellenstruktur fuer Tabelle `magnalister_ebay_categories` */
$queries[] = '
	CREATE TABLE IF NOT EXISTS `'.TABLE_MAGNA_EBAY_CATEGORIES.'` (
		`CategoryID` bigint(11) NOT NULL DEFAULT 0,
		`SiteID` int(4) NOT NULL DEFAULT 77,
		`CategoryName` varchar(128) NOT NULL DEFAULT \'\',
		`CategoryLevel` int(3) NOT NULL DEFAULT 1,
		`ParentID` bigint(11) NOT NULL DEFAULT 0,
		`LeafCategory` enum(\'0\',\'1\') NOT NULL DEFAULT \'1\',
		`StoreCategory` enum(\'0\',\'1\') NOT NULL DEFAULT \'0\',
		`InsertTimestamp` int(11) NOT NULL DEFAULT 0,
		PRIMARY KEY (`CategoryID`, `SiteID`, `StoreCategory`)
	) ENGINE='.DB_SERVER_ENGINE.';
';

/* Tabellenstruktur fuer Tabelle `magnalister_ebay_properties` */
$queries[] = '
	CREATE TABLE IF NOT EXISTS `'.TABLE_MAGNA_EBAY_PROPERTIES.'` (
		`products_id` int(11) NOT NULL,
		`products_model` VARCHAR(64) NOT NULL,
		`mpID` int(11) unsigned NOT NULL DEFAULT 0,
		`Title` varchar(80) NOT NULL,
		`Subtitle` varchar(55) DEFAULT NULL,
		`Description` longtext DEFAULT NULL,
		`PictureURL` varchar(255) DEFAULT NULL,
		`GalleryURL` varchar(255) DEFAULT NULL,
		`ConditionID` int(4) NOT NULL DEFAULT 0,
		`Price` decimal(15,4) NOT NULL,
		`BuyItNowPrice` decimal(15,4) DEFAULT NULL,
		`currencyID` enum("AUD", "CAD", "CHF", "CNY", "EUR", "GBP", "HKD", "INR", "MYR", "PHP", "PLN", "SEK", "SGD", "TWD", "USD") NOT NULL DEFAULT "EUR",
		`Site` enum("Australia", "Austria", "Belgium_Dutch", "Belgium_French", "Canada", "CanadaFrench", "China", "CustomCode", "eBayMotors", "France", "Germany", "HongKong", "India", "Ireland", "Italy", "Malaysia", "Netherlands", "Philippines", "Poland", "Singapore", "Spain", "Sweden", "Switzerland", "Taiwan", "UK", "US") NOT NULL DEFAULT "Germany",
		`PrimaryCategory` int(10) NOT NULL,
		`PrimaryCategoryName` varchar(128) NOT NULL,
		`SecondaryCategory` int(10) DEFAULT NULL,
		`SecondaryCategoryName` varchar(128) DEFAULT NULL,
		`StoreCategory` bigint(11) DEFAULT NULL,
		`StoreCategory2` bigint(11) DEFAULT NULL,
		`Attributes` text,
		`ListingType` enum(\'Chinese\',\'FixedPriceItem\',\'StoresFixedPrice\') NOT NULL DEFAULT \'FixedPriceItem\',
		`ListingDuration` varchar(10) NOT NULL,
		`PaymentMethods` longtext,
		`ShippingDetails` longtext,
		`Verified` enum("OK", "ERROR", "OPEN") NOT NULL DEFAULT "OPEN",
		UNIQUE KEY `UniqueEntry` (`mpID`, `products_id`, `products_model`)
	) ENGINE='.DB_SERVER_ENGINE.';
';

/* Tabellenstruktur fuer Tabelle `magnalister_ebay_errorlog` */
$queries[] = '
	CREATE TABLE IF NOT EXISTS `'.TABLE_MAGNA_EBAY_ERRORLOG.'` (
		`Timestamp` int(11) NOT NULL,
		`SKU` varchar(64) NOT NULL,
		`products_id` int(11) NOT NULL,
		`products_model` VARCHAR(64) NOT NULL,
		`mpID` int(11) unsigned NOT NULL DEFAULT 0,
		`Title` varchar(80) DEFAULT NULL,
		`Subtitle` varchar(55) DEFAULT NULL,
		`PictureURL` varchar(255) DEFAULT NULL,
		`ConditionID` int(4) NOT NULL DEFAULT 0,
		`Price` decimal(15,4) NOT NULL,
		`BuyItNowPrice` decimal(15,4) DEFAULT NULL,
		`currencyID` enum("AUD", "CAD", "CHF", "CNY", "EUR", "GBP", "HKD", "INR", "MYR", "PHP", "PLN", "SEK", "SGD", "TWD", "USD") NOT NULL DEFAULT "EUR",
		`Site` enum("Australia", "Austria", "Belgium_Dutch", "Belgium_French", "Canada", "CanadaFrench", "China", "CustomCode", "eBayMotors", "France", "Germany", "HongKong", "India", "Ireland", "Italy", "Malaysia", "Netherlands", "Philippines", "Poland", "Singapore", "Spain", "Sweden", "Switzerland", "Taiwan", "UK", "US") NOT NULL DEFAULT "Germany",
		`CategoryID` int(10) NOT NULL,
		`CategoryName` varchar(128) NOT NULL,
		`Category2ID` int(10) DEFAULT NULL,
		`Category2Name` varchar(128) DEFAULT NULL,
		`Attributes` text,
		`ShippingServices` text,
		`PaymentMethods` text,
		`Quantity` int(9) NOT NULL DEFAULT 1,
		`ListingType` enum("FixedPrice", "Chinese") not null default "Chinese",
		`Errors` text,
		PRIMARY KEY `UC_Timestamp_SKU` (`Timestamp`, `SKU`)
	);
';

function loadMarketplaceIDs() {
	if (!file_exists(DIR_MAGNALISTER_INCLUDES.'lib/MagnaConnector.php')
		|| !file_exists(DIR_MAGNALISTER_INCLUDES.'lib/MagnaException.php')
		|| !file_exists(DIR_MAGNALISTER_INCLUDES.'lib/MagnaError.php')
	) {
		return array();
	}
	require_once(DIR_MAGNALISTER_INCLUDES.'lib/MagnaConnector.php');
	require_once(DIR_MAGNALISTER_INCLUDES.'lib/MagnaException.php');
	require_once(DIR_MAGNALISTER_INCLUDES.'lib/MagnaError.php');

	$pp = MagnaDB::gi()->fetchOne('SELECT value FROM `'.TABLE_MAGNA_CONFIG.'` WHERE mkey=\'general.passphrase\'');
	if (($pp === false) || empty($pp)) {
		return array();
	}
	MagnaConnector::gi()->setPassPhrase($pp);
	try {
		$result = MagnaConnector::gi()->submitRequest(array(
			'SUBSYSTEM' => 'Core',
			'ACTION' => 'GetShopInfo'
		));
		$result = $result['DATA'];
	} catch (MagnaException $e) {
		return array();
	}
	return (array_key_exists('Marketplaces', $result) && is_array($result['Marketplaces'])) 
		? $result['Marketplaces'] 
		: array();
}

function removePlatformKeys() {
	$bleh = array ( # Nicht die Orders-Tabelle!
		TABLE_MAGNA_SELECTION_TEMPLATES,
		TABLE_MAGNA_CS_DELETEDLOG,
		TABLE_MAGNA_CS_ERRORLOG,
		TABLE_MAGNA_SELECTION,
	);
	foreach ($bleh as $tbl) {
		$cols = array();
		$colsQuery = MagnaDB::gi()->query('SHOW COLUMNS FROM `'.$tbl.'`');
		while ($row = MagnaDB::gi()->fetchNext($colsQuery))	{
			$cols[] = $row['Field'];
		}
		if (in_array('platform', $cols)) {
			MagnaDB::gi()->query('ALTER TABLE `'.$tbl.'` DROP `platform`');
		}
	}
}

function prepareMultiMarketplaces() {
	$bleh = array (
		TABLE_MAGNA_SELECTION_TEMPLATES => array (
			'ALTER TABLE `'.TABLE_MAGNA_SELECTION_TEMPLATES.'` DROP INDEX `title`',
			'ALTER TABLE `'.TABLE_MAGNA_SELECTION_TEMPLATES.'` ADD INDEX  `title` ( `title` , `mpID` )'
		),
		TABLE_MAGNA_CS_DELETEDLOG => array(),
		TABLE_MAGNA_CS_ERRORLOG => array(),
		TABLE_MAGNA_ORDERS => array(),
		TABLE_MAGNA_SELECTION => array (
			'TRUNCATE TABLE `'.TABLE_MAGNA_SELECTION.'`',
			'ALTER TABLE `'.TABLE_MAGNA_SELECTION.'` DROP INDEX `selection`',
			'ALTER TABLE `'.TABLE_MAGNA_SELECTION.'` ADD UNIQUE `selection` (`pID`,`mpID`,`selectionname`,`session_id`)',
		),
	);
	
	$cols = array();
	$colsQuery = MagnaDB::gi()->query('SHOW COLUMNS FROM `'.TABLE_MAGNA_CONFIG.'`');
	while ($row = MagnaDB::gi()->fetchNext($colsQuery))	{
		$cols[] = $row['Field'];
	}
	if (!in_array('mpID', $cols)) {
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_CONFIG.'` ADD `mpID` INT( 8 ) UNSIGNED NOT NULL DEFAULT \'0\' FIRST');
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_CONFIG.'` DROP PRIMARY KEY ');
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_CONFIG.'` ADD UNIQUE `UniqueKey` ( `mpID` , `mkey` )');
	}
	
	foreach ($bleh as $tbl => $keyFix) {
		$cols = array();
		$colsQuery = MagnaDB::gi()->query('SHOW COLUMNS FROM `'.$tbl.'`');
		while ($row = MagnaDB::gi()->fetchNext($colsQuery))	{
			$cols[] = $row['Field'];
		}
		if (in_array('mpID', $cols)) {
			continue;
		}
		MagnaDB::gi()->query('ALTER TABLE `'.$tbl.'` ADD `mpID` INT( 8 ) UNSIGNED NOT NULL AFTER `platform`');
		if (!empty($keyFix)) {
			foreach ($keyFix as $fix) {
				MagnaDB::gi()->query($fix);
			}
		}
	}
	
	$marketplaces = loadMarketplaceIDs();
	if (empty($marketplaces)) {
		return;
	}

	foreach ($marketplaces as $mp) {
		$updated = (int)MagnaDB::gi()->fetchOne("
			   SELECT mpID
			     FROM `".TABLE_MAGNA_CONFIG."`
			    WHERE mkey LIKE '".$mp['Marketplace'].".%'
			          AND `mpID` = '".$mp['ID']."'
			 ORDER BY mpID DESC
			    LIMIT 1
		") > 0;
		if (!$updated) {
			MagnaDB::gi()->query('
				UPDATE `'.TABLE_MAGNA_CONFIG.'` SET `mpID`=\''.$mp['ID'].'\'
				 WHERE mkey LIKE \''.$mp['Marketplace'].'.%\' AND `mpID`=0
			');
		}
		if (MagnaDB::gi()->fetchRow('SELECT * FROM `'.$tbl.'` LIMIT 1') === false) {
			continue;
		}
		foreach ($bleh as $tbl => $keyFix) {
			if (MagnaDB::gi()->recordExists($tbl, array('mpID' => $mp['ID']))) {
				continue;
			}
			if (!MagnaDB::gi()->columnExistsInTable('platform', $tbl)) {
				continue;
			}
			MagnaDB::gi()->query('
				UPDATE `'.$tbl.'` SET `mpID`=\''.$mp['ID'].'\'
				 WHERE platform=\''.$mp['Marketplace'].'\'
			');
		}
	}
	removePlatformKeys();	

}
$functions[] = 'prepareMultiMarketplaces';

if (!function_exists('getProductsIDColname')) {
	function getProductsIDColname($a) {
		if (empty($a)) {
			return '';
		}
		$prevCol = '';
		foreach ($a as $col) {
			if (($col == 'products_id') || ($col == 'pID')) {
				return $col;
			}
		}
		return '';
	}
}

function addProductsModelToMagnaTables2() {
	$tables = array (
		TABLE_MAGNA_CS_DELETEDLOG,
		TABLE_MAGNA_CS_ERRORLOG,
	);
	foreach ($tables as $table) {
		if (!MagnaDB::gi()->tableExists($table)) {
			continue;
		}
		$cols = array();
		$colsQuery = MagnaDB::gi()->query('SHOW COLUMNS FROM `'.$table.'`');
		while ($row = MagnaDB::gi()->fetchNext($colsQuery))	{
			$cols[] = $row['Field'];
		}
		MagnaDB::gi()->freeResult($colsQuery);
		if (in_array('products_model', $cols)) {
			continue;
		}
		$pIDCol = getProductsIDColname($cols);
		MagnaDB::gi()->query('ALTER TABLE `'.$table.'` ADD `products_model` VARCHAR(64) NOT NULL AFTER `'.$pIDCol.'`');
		MagnaDB::gi()->query('
			UPDATE `'.$table.'` ml, '.TABLE_PRODUCTS.' p
	           SET ml.products_model = p.products_model
	         WHERE ml.`'.$pIDCol.'`=p.products_id
		');
	}
}
$functions[] = 'addProductsModelToMagnaTables2';

function convertAmazonApplyTmpStuffNSuch() {
	if (MagnaDB::gi()->tableExists('magnalister_amazon_apply_tmp')) {
		MagnaDB::gi()->query('DROP TABLE IF EXISTS `'.TABLE_MAGNA_AMAZON_APPLY.'`');
	}
	MagnaDB::gi()->query('
		CREATE TABLE IF NOT EXISTS `'.TABLE_MAGNA_AMAZON_APPLY.'` (
			`products_id` int(10) unsigned NOT NULL,
			`products_model` varchar(64) NOT NULL,
			`is_incomplete` ENUM(\'true\', \'false\') NOT NULL DEFAULT \'false\',
			`data` text NOT NULL,
			PRIMARY KEY  (`products_id`, `products_model`)
		);
	');
	if (!MagnaDB::gi()->tableExists('magnalister_amazon_apply_tmp')) {
		return;
	}
	$rows = MagnaDB::gi()->fetchArray('
		SELECT * FROM magnalister_amazon_apply_tmp
	');
	if (empty($rows)) {
		return;
	}
	require_once(DIR_MAGNALISTER_INCLUDES.'lib/functionLib.php');
	foreach ($rows as &$row) {
		if (substr($row['data'], 0, 3) == 'YTo') {
			$row['data'] = base64_decode($row['data']);
		}
		$row['data'] = myUnserialize($row['data']);
		if (!is_array($row['data'])) {
			continue;
		}
		$newDatatada = array();
		$newDatatada['MainCategory'] = $row['data']['g']['maincat'];
		$newDatatada['ProductType'] = $row['data']['g']['subcat'];
		$newDatatada['BrowseNodes'] = array();
		if (isset($row['data']['g']['browsenode']) 
			&& is_array($row['data']['g']['browsenode']) 
			&& !empty($row['data']['g']['browsenode'])
		) {
			foreach ($row['data']['g']['browsenode'] as $blubb) {
				if ($blubb == 'null') continue;
				$newDatatada['BrowseNodes'][] = $blubb;
			}
		}
		$newDatatada['ItemTitle'] = $row['data']['n']['products_name'];
		$newDatatada['Manufacturer'] = $row['data']['n']['products_manufacturer'];
		$newDatatada['ManufacturerPartNumber'] = $row['data']['n']['products_modelnumber'];
		$newDatatada['Brand'] = $row['data']['n']['products_model'];
		$newDatatada['EAN'] = $row['data']['n']['products_ean'];
		$newDatatada['Images'] = $row['data']['n']['products_images'];
		$newDatatada['BulletPoints'] = $row['data']['n']['bullet'];
		$newDatatada['Description'] = $row['data']['n']['products_description'];
		$newDatatada['Keywords'] = $row['data']['n']['keyword'];
		$newDatatada['Attributes'] = $row['data']['a'];
		$row['data'] = base64_encode(serialize($newDatatada));
		$row['products_id'] = $row['pID'];
		unset($row['pID']);
	}
	MagnaDB::gi()->batchinsert(TABLE_MAGNA_AMAZON_APPLY, $rows, true);
	MagnaDB::gi()->query('DROP TABLE IF EXISTS magnalister_amazon_apply_tmp');
}
$functions[] = 'convertAmazonApplyTmpStuffNSuch';

function prepareMultiMarketplacesPart2() {
	$platformToTable = array (
		'amazon' => array (
			TABLE_MAGNA_AMAZON_APPLY => '
				ALTER TABLE `'.TABLE_MAGNA_AMAZON_APPLY.'` 
			        ADD `mpID` INT( 8 ) UNSIGNED NOT NULL FIRST,
					DROP PRIMARY KEY,
					ADD PRIMARY KEY ( `mpID` , `products_id` , `products_model` )
			',
			TABLE_MAGNA_AMAZON_PROPERTIES => '
				ALTER TABLE `'.TABLE_MAGNA_AMAZON_PROPERTIES.'` 
			        ADD `mpID` INT( 8 ) UNSIGNED NOT NULL FIRST,
					DROP INDEX UC_products_id,
					ADD UNIQUE `UC_products_id` ( `mpID` , `products_id` , `products_model` )
			',
		),
		'yatego' => array (
			TABLE_MAGNA_YATEGO_CATEGORYMATCHING => '
				ALTER TABLE `'.TABLE_MAGNA_YATEGO_CATEGORYMATCHING.'` 
			        ADD `mpID` INT( 8 ) UNSIGNED NOT NULL FIRST,
					ADD INDEX ( `mpID` )
			',
		)
	);

	foreach ($platformToTable as $mp => $tables) {
		foreach ($tables as $table => $query) {
			$cols = MagnaDB::gi()->getTableCols($table);
			if (in_array('mpID', $cols)) {
				continue; /* Tabelle bereits modifiziert */
			}
			MagnaDB::gi()->query($query);
		}
	}
	if (!isset($modificationsMade) || !$modificationsMade) {
		return;
	}
	$marketplaces = loadMarketplaceIDs();

	if (empty($marketplaces)) {
		return;
	}
	$tmpMP = array();
	foreach ($marketplaces as $mp) {
		/* Bei ml v1.1.0 gab es noch keine Multi-Marketplaces */
		if (array_key_exists($mp['Marketplace'], $tmpMP)) continue;
		$tmpMP[$mp['Marketplace']] = $mp['ID'];
	}
	$marketplaces = $tmpMP;

	foreach ($platformToTable as $mp => $tables) {
		foreach ($tables as $table => $pos) {
			if (array_key_exists($mp, $marketplaces) 
				&& !(MagnaDB::gi()->recordExists($table, array('mpID' => $marketplaces[$mp])))
			) {
				MagnaDB::gi()->update($table, array('mpID' => $marketplaces[$mp]), array());
			}
		}
	}
}
$functions[] = 'prepareMultiMarketplacesPart2';

function amazonTransformConditionTypes() {
	$conditionTypes = array (
		 '0' => '',
		 '1' => 'UsedLikeNew',
		 '2' => 'UsedVeryGood',
		 '3' => 'UsedGood',
		 '4' => 'UsedAcceptable',
		 '5' => 'CollectibleLikeNew',
		 '6' => 'CollectibleVeryGood',
		 '7' => 'CollectibleGood',
		 '8' => 'CollectibleAcceptable',
		 '9' => 'Club',
		'10' => 'Refurbished',
		'11' => 'New',
	);
	MagnaDB::gi()->query('
		ALTER TABLE `'.TABLE_MAGNA_AMAZON_PROPERTIES.'` 
		     CHANGE `item_condition` `item_condition` VARCHAR( 30 ) NOT NULL 
	');

	foreach ($conditionTypes as $id => $key) {
		MagnaDB::gi()->update(TABLE_MAGNA_AMAZON_PROPERTIES, array (
			'item_condition' => $key
		), array (
			'item_condition' => (string)$id
		));
	}
	$blubb = MagnaDB::gi()->fetchArray('
		SELECT value FROM `'.TABLE_MAGNA_CONFIG.'`
		 WHERE mkey=\'amazon.itemCondition\'
	', true);
	if (!empty($blubb)) {
		foreach ($blubb as $id) {
			if (!array_key_exists($id, $conditionTypes)) continue;
			MagnaDB::gi()->update(TABLE_MAGNA_CONFIG, array (
				'value' => $conditionTypes[$id]
			), array (
				'mkey' => 'amazon.itemCondition',
				'value' => (string)$id
			));
		}
	}
}
$functions[] = 'amazonTransformConditionTypes';

function magnaOrdersAddCols() {
	$cols = MagnaDB::gi()->getTableCols(TABLE_MAGNA_ORDERS);
	if (!in_array('orders_status', $cols)) {
		MagnaDB::gi()->query('
			ALTER TABLE `'.TABLE_MAGNA_ORDERS.'` 
			  ADD `orders_status` INT NOT NULL AFTER `orders_id`
		');		
	}
	if (!in_array('internaldata', $cols)) {
		MagnaDB::gi()->query('
			ALTER TABLE `'.TABLE_MAGNA_ORDERS.'` 
			  ADD `internaldata` longtext NOT NULL AFTER `data`
		');
	}
}
$functions[] = 'magnaOrdersAddCols';

function magnaAddNewAmazonErrorLog() {
	$cols = MagnaDB::gi()->getTableCols(TABLE_MAGNA_AMAZON_ERRORLOG);
	if (in_array('additionaldata', $cols)) {
		return;
	}
	MagnaDB::gi()->query('
		DROP TABLE `'.TABLE_MAGNA_AMAZON_ERRORLOG.'`
	');
	MagnaDB::gi()->query('
		CREATE TABLE IF NOT EXISTS `'.TABLE_MAGNA_AMAZON_ERRORLOG.'` (
			`id` int(10) unsigned NOT NULL auto_increment,
			`mpID` int(8) unsigned NOT NULL,
			`batchid` varchar(50) NOT NULL,
			`dateadded` datetime NOT NULL,
			`errormessage` text NOT NULL,
			`additionaldata` longtext NOT NULL,
			PRIMARY KEY (`id`),
			KEY `mpID` (`mpID`)
		) ENGINE='.DB_SERVER_ENGINE.';
	');
}
$functions[] = 'magnaAddNewAmazonErrorLog';

function magnaOrdersUpdateAmazon() {
	# only the last 10 000, to avoid execution time problems (normally, no one cares for very old orders)
	$iLimit  = 10000;
	$aoid = MagnaDB::gi()->query('
		SELECT SQL_CALC_FOUND_ROWS orders_id, data, internaldata
		  FROM `'.TABLE_MAGNA_ORDERS.'`
		 WHERE platform=\'amazon\'
		ORDER BY orders_id DESC
		LIMIT '.$iLimit.'
	');
	while (($row = MagnaDB::gi()->fetchNext($aoid)) !== false) {
		$row['data'] = @unserialize($row['data']);
		if (!is_array($row['data'])) {
			$row['data'] = array();
		}
		if (array_key_exists('AmazonOrderId', $row['data'])) {
			$row['data']['AmazonOrderID'] = $row['data']['AmazonOrderId'];
			unset($row['data']['AmazonOrderId']);
		}
		$row['data'] = serialize($row['data']);

		$row['internaldata'] = @unserialize($row['internaldata']);
		if (!is_array($row['internaldata'])) {
			$row['internaldata'] = array();
		}
		if (!array_key_exists('FulfillmentChannel', $row['internaldata'])) {
			$row['internaldata']['FulfillmentChannel'] = 'MFN';
		}
		$row['internaldata'] = serialize($row['internaldata']);

		MagnaDB::gi()->update(TABLE_MAGNA_ORDERS, $row, array (
			'orders_id' => $row['orders_id']
		));
	}
}
$functions[] = 'magnaOrdersUpdateAmazon';

function magnaAmazonDeleteOldSessionStuff() {
	global $_MagnaShopSession;
	unset($_MagnaShopSession['amazon']['Checkin']);
}
$functions[] = 'magnaAmazonDeleteOldSessionStuff';
