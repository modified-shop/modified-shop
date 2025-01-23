<?php
/*
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
 * (c) 2010 - 2024 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

# Hood-Modul: Main image for prepare table
function mlDbUpdate_AddShopVariationToHoodPropertiesAndAddHoodVariantMatchingTableTable() {
    if (!MagnaDB::gi()->columnExistsInTable('ShopVariation', TABLE_MAGNA_HOOD_PROPERTIES)) {
        MagnaDB::gi()->query('ALTER TABLE ' . TABLE_MAGNA_HOOD_PROPERTIES . ' ADD COLUMN ShopVariation mediumtext NOT NULL AFTER `StoreCategory`');
    }
}

$functions[] = 'mlDbUpdate_AddShopVariationToHoodPropertiesAndAddHoodVariantMatchingTableTable';
$queries[] = '
	CREATE TABLE IF NOT EXISTS '.TABLE_MAGNA_HOOD_VARIANTMATCHING.' (
		MpId int(11) NOT NULL,
		MpIdentifier varchar(50) NOT NULL,
		CustomIdentifier varchar(64) NOT NULL DEFAULT \'\',
		ShopVariation mediumtext NOT NULL,
		IsValid bit(1) NOT NULL DEFAULT b\'1\',
		ModificationDate datetime NOT NULL,
		PRIMARY KEY (MpId, MpIdentifier, CustomIdentifier)
	)
';

