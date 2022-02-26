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
 * (c) 2010 - 2022 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

# Adding new fields to Idealo product prepare
$queries = array();
$functions = array();

function cdiscount_add_new_attribute_fields_ml124() {
    if (! MagnaDB::gi()->columnExistsInTable('ShippingProfileName', TABLE_MAGNA_CDISCOUNT_PREPARE)) {
        MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_CDISCOUNT_PREPARE.'` ADD COLUMN `ShippingProfileName` text DEFAULT NULL');
    }
    if (! MagnaDB::gi()->columnExistsInTable('ShippingFee', TABLE_MAGNA_CDISCOUNT_PREPARE)) {
        MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_CDISCOUNT_PREPARE.'` ADD COLUMN `ShippingFee` text DEFAULT NULL');
    }
    if (! MagnaDB::gi()->columnExistsInTable('ShippingFeeAdditional', TABLE_MAGNA_CDISCOUNT_PREPARE)) {
        MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_CDISCOUNT_PREPARE.'` ADD COLUMN `ShippingFeeAdditional` text DEFAULT NULL');
    }
}

$functions[] = 'cdiscount_add_new_attribute_fields_ml124';
