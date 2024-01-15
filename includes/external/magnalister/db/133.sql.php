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
 * (c) 2010 - 2023 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

function md_db_update_133() {
    if (!MagnaDB::gi()->columnExistsInTable('ShippingGroup', TABLE_MAGNA_HITMEISTER_PREPARE)) {
        MagnaDB::gi()->query("ALTER TABLE `".TABLE_MAGNA_HITMEISTER_PREPARE."` ADD COLUMN `ShippingGroup` INT(11) NOT NULL DEFAULT 0 AFTER `HandlingTime`");
    }
    if (MagnaDB::gi()->columnExistsInTable('ShippingTimeMin', TABLE_MAGNA_HITMEISTER_PREPARE)) {
        MagnaDB::gi()->query("ALTER TABLE `".TABLE_MAGNA_HITMEISTER_PREPARE."` DROP COLUMN `ShippingTimeMin`");
    }
    if (MagnaDB::gi()->columnExistsInTable('ShippingTimeMax', TABLE_MAGNA_HITMEISTER_PREPARE)) {
        MagnaDB::gi()->query("ALTER TABLE `".TABLE_MAGNA_HITMEISTER_PREPARE."` DROP COLUMN `ShippingTimeMax`");
    }
}

$functions[] = 'md_db_update_133';

