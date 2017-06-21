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
 * $Id: 074.sql.php $
 *
 * (c) 2016 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

function md_db_update_83() {
    if (MagnaDB::gi()->columnExistsInTable('Title', TABLE_MAGNA_PRICEMINISTER_PREPARE)){
        MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_PRICEMINISTER_PREPARE.'` CHANGE COLUMN `Title` `Title` VARCHAR(200) DEFAULT NULL');
    }

    if (MagnaDB::gi()->columnExistsInTable('Description', TABLE_MAGNA_PRICEMINISTER_PREPARE)){
        MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_PRICEMINISTER_PREPARE.'` CHANGE COLUMN `Description` `Description` VARCHAR(4096) DEFAULT NULL');
    }
}

$functions[] = 'md_db_update_83';