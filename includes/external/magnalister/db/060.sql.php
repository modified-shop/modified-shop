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
/*
2024-01-08 module deleted
function md_db_update_60_1() {
	if (!MagnaDB::gi()->columnExistsInTable('IsValid', TABLE_MAGNA_AYN24_VARIANTMATCHING)) {
		MagnaDB::gi()->query("ALTER TABLE `".TABLE_MAGNA_AYN24_VARIANTMATCHING."` ADD COLUMN `IsValid` BIT NOT NULL DEFAULT 1 AFTER `ShopVariation` ");
	}
}
$functions[] = 'md_db_update_60_1';

2024-01-08 module deleted
function md_db_update_60_2() {
	if (!MagnaDB::gi()->columnExistsInTable('IsValid', TABLE_MAGNA_MEINPAKET_VARIANTMATCHING)) {
		MagnaDB::gi()->query("ALTER TABLE `".TABLE_MAGNA_MEINPAKET_VARIANTMATCHING."` ADD COLUMN `IsValid` BIT NOT NULL DEFAULT 1 AFTER `ShopVariation` ");
	}
}
$functions[] = 'md_db_update_60_2';
*/
