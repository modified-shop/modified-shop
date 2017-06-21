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
 * (c) 2010 - 2016 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

function ml_db_80_UpdatePriceministerPrepareTable()
{
    if (MagnaDB::gi()->columnExistsInTable('PrimaryCategory', TABLE_MAGNA_PRICEMINISTER_PREPARE)) {
        MagnaDB::gi()->query("ALTER TABLE `" . TABLE_MAGNA_PRICEMINISTER_PREPARE . "` DROP COLUMN `PrimaryCategory`");
    }
    if (!MagnaDB::gi()->columnExistsInTable('MarketplaceCategories', TABLE_MAGNA_PRICEMINISTER_PREPARE)) {
        MagnaDB::gi()->query("ALTER TABLE `" . TABLE_MAGNA_PRICEMINISTER_PREPARE . "` ADD COLUMN `MarketplaceCategories` VARCHAR(50) NULL DEFAULT NULL AFTER `MarketplaceCategoriesName`");
    }
}

$functions[] = 'ml_db_80_UpdatePriceministerPrepareTable';
