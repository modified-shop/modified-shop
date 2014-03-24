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
 * $Id: 030.sql.php 3098 2013-08-07 19:10:52Z markus.walkowiak $
 *
 * (c) 2010 - 2013 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array('changeVariationsTable');

function changeVariationsTable() {
	if (!MagnaDB::gi()->columnExistsInTable('products_sku', TABLE_MAGNA_VARIATIONS)) {
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_VARIATIONS.'` ADD COLUMN `products_sku` VARCHAR(150) DEFAULT NULL AFTER `products_id`');
	}
	if (!MagnaDB::gi()->columnExistsInTable('marketplace_id', TABLE_MAGNA_VARIATIONS)) {
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_VARIATIONS.'` ADD COLUMN `marketplace_id`  VARCHAR(32) NOT NULL DEFAULT \'ML\' AFTER `products_sku`');
	}
	if (!MagnaDB::gi()->columnExistsInTable('marketplace_sku', TABLE_MAGNA_VARIATIONS)) {
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_VARIATIONS.'` ADD COLUMN `marketplace_sku` VARCHAR(150) DEFAULT NULL AFTER `marketplace_id`');
	}
	if (!MagnaDB::gi()->columnExistsInTable('date_added', TABLE_MAGNA_VARIATIONS)) {
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_VARIATIONS.'` ADD COLUMN `date_added` DATETIME NOT NULL DEFAULT \'2000-01-01 00:00:00\'');
	}
	# erst aktivieren nachdem alles umgeschrieben ist
	#if (MagnaDB::gi()->columnExistsInTable('variation_attributes_text', TABLE_MAGNA_VARIATIONS)) {
	#	MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_VARIATIONS.'` DROP COLUMN `variation_attributes_text`');
	#}
}
