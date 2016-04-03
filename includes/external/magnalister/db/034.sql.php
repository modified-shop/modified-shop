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
 * $Id$
 *
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

function hoodUpdatePropertiesTable1() {
	$tableStructureTmp = MagnaDB::gi()->fetchArray('SHOW COLUMNS FROM `'.TABLE_MAGNA_HOOD_PROPERTIES.'`');
	if (empty($tableStructureTmp)) {
		return;
	}
	$tableStructure = array();
	foreach ($tableStructureTmp as $row) {
		$tableStructure[$row['Field']] = array();
		
		$m = array();
		if (!preg_match('/^([a-z]+)(\(([^\)]+)\))?(\s+(.*))?$/', $row['Type'], $m)) {
			continue;
		}
		
		$tableStructure[$row['Field']] = array (
			'FBaseType' => isset($m[1]) ? $m[1] : '',
			'FLength' => isset($m[3]) ? $m[3] : '',
			'FAdd' => isset($m[5]) ? $m[5] : '',
			'IsNull' => strtoupper($row['Null']) == 'YES',
			'Default' => $row['Default'],
		);
	}
	
	#echo print_m($tableStructure, '$tableStructure');
	
	// Add some columns
	if (!isset($tableStructure['Features'])) {
		MagnaDB::gi()->query('
			ALTER TABLE `'.TABLE_MAGNA_HOOD_PROPERTIES.'` ADD `Features` TEXT NOT NULL AFTER `ShippingServiceOptions`
		');
	}
	if (!isset($tableStructure['FSK'])) {
		MagnaDB::gi()->query('
			ALTER TABLE `'.TABLE_MAGNA_HOOD_PROPERTIES.'` ADD `FSK` TINYINT NOT NULL DEFAULT "-1" AFTER `Features`
		');
	}
	if (!isset($tableStructure['USK'])) {
		MagnaDB::gi()->query('
			ALTER TABLE `'.TABLE_MAGNA_HOOD_PROPERTIES.'` ADD `USK` TINYINT NOT NULL DEFAULT "-1" AFTER `FSK`
		');
	}
	if (!isset($tableStructure['StoreCategory3'])) {
		MagnaDB::gi()->query('
			ALTER TABLE `'.TABLE_MAGNA_HOOD_PROPERTIES.'` ADD `StoreCategory3` BIGINT NOT NULL DEFAULT "0" AFTER `StoreCategory2`
		');
	}
	if (!isset($tableStructure['ShortDescription'])) {
		MagnaDB::gi()->query('
			ALTER TABLE `'.TABLE_MAGNA_HOOD_PROPERTIES.'` ADD `ShortDescription` TEXT NOT NULL AFTER `Subtitle`
		');
	}
	if (!isset($tableStructure['GalleryPictures'])) {
		MagnaDB::gi()->query('
			ALTER TABLE `'.TABLE_MAGNA_HOOD_PROPERTIES.'` ADD `GalleryPictures` TEXT NOT NULL AFTER `PictureURL`
		');
	}
	
	// Fix the lengths
	if (isset($tableStructure['ManufacturerPartNumber']['IsNull'])
		&& ($tableStructure['ManufacturerPartNumber']['IsNull'] || ((int)$tableStructure['ManufacturerPartNumber']['FLength'] < 200))
	) {
		MagnaDB::gi()->query('
			ALTER TABLE `'.TABLE_MAGNA_HOOD_PROPERTIES.'` CHANGE `ManufacturerPartNumber` `ManufacturerPartNumber` VARCHAR( 200 ) NOT NULL DEFAULT ""
		');
	}
	if (isset($tableStructure['Subtitle']['FLength']) && ((int)$tableStructure['Subtitle']['FLength'] < 100)) {
		MagnaDB::gi()->query('
			ALTER TABLE `'.TABLE_MAGNA_HOOD_PROPERTIES.'` CHANGE `Subtitle` `Subtitle` VARCHAR( 100 ) NOT NULL DEFAULT ""
		');
	}
	if (isset($tableStructure['Title']['FLength']) && ((int)$tableStructure['Title']['FLength'] < 85)) {
		MagnaDB::gi()->query('
			ALTER TABLE `'.TABLE_MAGNA_HOOD_PROPERTIES.'` CHANGE `Title` `Title` VARCHAR( 85 ) NOT NULL DEFAULT ""
		');
	}
	
	if (isset($tableStructure['PictureURL'])) {
		$pictures = MagnaDB::gi()->fetchArray('
			SELECT mpID, products_id, products_model, PictureURL
			  FROM `'.TABLE_MAGNA_HOOD_PROPERTIES.'`
			 WHERE PictureURL != ""
		');
		if (!empty($pictures)) {
			foreach ($pictures as $p) {
				if (!(strpos($p['PictureURL'], '/') > 0)) {
					continue;
				}
				$galleryPictures = array (
					'BaseUrl' => dirname($p['PictureURL']).'/',
					'Images' => array (
						basename($p['PictureURL']) => true,
					)
				);
				unset($p['PictureURL']);
				#echo print_m($galleryPictures, '$galleryPictures');
				
				MagnaDB::gi()->update(TABLE_MAGNA_HOOD_PROPERTIES, array (
					'GalleryPictures' => json_encode($galleryPictures)
				), $p);
			}
		}
	}
	
	if (isset($tableStructure['ItemSpecifics'])) {
		MagnaDB::gi()->query('
			ALTER TABLE `'.TABLE_MAGNA_HOOD_PROPERTIES.'` DROP `ItemSpecifics`
		');
	}
	if (isset($tableStructure['PictureURL'])) {
		MagnaDB::gi()->query('
			ALTER TABLE `'.TABLE_MAGNA_HOOD_PROPERTIES.'` DROP `PictureURL`
		');
	}
}
$functions[] = 'hoodUpdatePropertiesTable1';
