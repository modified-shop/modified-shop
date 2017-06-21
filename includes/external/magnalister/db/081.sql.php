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

function ml_db_81_UpdatePriceministerPrepareTable()
{
    if (MagnaDB::gi()->columnExistsInTable('Subtitle', TABLE_MAGNA_PRICEMINISTER_PREPARE)){
        MagnaDB::gi()->query("ALTER TABLE `" . TABLE_MAGNA_PRICEMINISTER_PREPARE . "` DROP COLUMN `Subtitle`");
    }

    if (MagnaDB::gi()->columnExistsInTable('ShippingTimeMin', TABLE_MAGNA_PRICEMINISTER_PREPARE)){
        MagnaDB::gi()->query("ALTER TABLE `" . TABLE_MAGNA_PRICEMINISTER_PREPARE . "` DROP COLUMN `ShippingTimeMin`");
    }

    if (MagnaDB::gi()->columnExistsInTable('ShippingTimeMax', TABLE_MAGNA_PRICEMINISTER_PREPARE)){
        MagnaDB::gi()->query("ALTER TABLE `" . TABLE_MAGNA_PRICEMINISTER_PREPARE . "` DROP COLUMN `ShippingTimeMax`");
    }

    if (MagnaDB::gi()->columnExistsInTable('ShippingFeeStandard', TABLE_MAGNA_PRICEMINISTER_PREPARE)){
        MagnaDB::gi()->query("ALTER TABLE `" . TABLE_MAGNA_PRICEMINISTER_PREPARE . "` DROP COLUMN `ShippingFeeStandard`");
    }

    if (MagnaDB::gi()->columnExistsInTable('ShippingFeeExtraStandard', TABLE_MAGNA_PRICEMINISTER_PREPARE)){
        MagnaDB::gi()->query("ALTER TABLE `" . TABLE_MAGNA_PRICEMINISTER_PREPARE . "` DROP COLUMN `ShippingFeeExtraStandard`");
    }

    if (MagnaDB::gi()->columnExistsInTable('ShippingFeeRegistered', TABLE_MAGNA_PRICEMINISTER_PREPARE)){
        MagnaDB::gi()->query("ALTER TABLE `" . TABLE_MAGNA_PRICEMINISTER_PREPARE . "` DROP COLUMN `ShippingFeeRegistered`");
    }

    if (MagnaDB::gi()->columnExistsInTable('ShippingFeeExtraRegistered', TABLE_MAGNA_PRICEMINISTER_PREPARE)){
        MagnaDB::gi()->query("ALTER TABLE `" . TABLE_MAGNA_PRICEMINISTER_PREPARE . "` DROP COLUMN `ShippingFeeExtraRegistered`");
    }

    if (MagnaDB::gi()->columnExistsInTable('Comment', TABLE_MAGNA_PRICEMINISTER_PREPARE)){
        MagnaDB::gi()->query("ALTER TABLE `" . TABLE_MAGNA_PRICEMINISTER_PREPARE . "` DROP COLUMN `Comment`");
    }

    if (MagnaDB::gi()->columnExistsInTable('ShippingFeeTracked', TABLE_MAGNA_PRICEMINISTER_PREPARE)){
        MagnaDB::gi()->query("ALTER TABLE `" . TABLE_MAGNA_PRICEMINISTER_PREPARE . "` DROP COLUMN `ShippingFeeTracked`");
    }

    if (MagnaDB::gi()->columnExistsInTable('ShippingFeeExtraTracked', TABLE_MAGNA_PRICEMINISTER_PREPARE)){
        MagnaDB::gi()->query("ALTER TABLE `" . TABLE_MAGNA_PRICEMINISTER_PREPARE . "` DROP COLUMN `ShippingFeeExtraTracked`");
    }
}

$functions[] = 'ml_db_81_UpdatePriceministerPrepareTable';
