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
 *
 * (c) 2010 - 2025 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

/**
 * Create magnalister_amazon_prepare_longtext table for storing large JSON data
 * This improves performance by normalizing and deduplicating attribute matching data
 */
function amazon_create_prepare_longtext_table() {
    $oDB = MagnaDB::gi();

    // Check if table already exists
    if ($oDB->tableExists('magnalister_amazon_prepare_longtext')) {
        return; // Already created
    }

    // Create the longtext table
    $sql = "
        CREATE TABLE IF NOT EXISTS `magnalister_amazon_prepare_longtext` (
          `TextId` varchar(64) NOT NULL COMMENT 'SHA256 hash of JSON content',
          `ReferenceFieldName` varchar(64) NOT NULL COMMENT 'Field name (e.g. \"ShopVariation\")',
          `Value` longtext COMMENT 'JSON encoded attribute data',
          `CreatedAt` datetime DEFAULT CURRENT_TIMESTAMP,
          UNIQUE KEY `UC_TextIdReferenceFieldName` (`TextId`, `ReferenceFieldName`),
          KEY `idx_textid` (`TextId`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores large JSON data with deduplication';
    ";

    $oDB->query($sql);
}

/**
 * Add DataId column to magnalister_amazon_apply table
 * This column references the TextId in magnalister_amazon_prepare_longtext
 * Note: v2 uses 'data' column (mediumtext) for attribute matching, not 'ShopVariation'
 */
function amazon_add_dataid_column() {
    $oDB = MagnaDB::gi();

    if (!$oDB->tableExists(TABLE_MAGNA_AMAZON_APPLY)) {
        return; // Table doesn't exist
    }

    // Check if column already exists
    if ($oDB->columnExistsInTable('DataId', TABLE_MAGNA_AMAZON_APPLY)) {
        return; // Already added
    }

    // Add DataId column after data
    $sql = "
        ALTER TABLE `" . TABLE_MAGNA_AMAZON_APPLY . "`
        ADD COLUMN `DataId` varchar(64) DEFAULT NULL
        COMMENT 'TextId reference to magnalister_amazon_prepare_longtext'
        AFTER `data`
    ";

    $oDB->query($sql);

    // Add index for performance
    $sql = "
        ALTER TABLE `" . TABLE_MAGNA_AMAZON_APPLY . "`
        ADD INDEX `idx_dataid` (`DataId`)
    ";

    $oDB->query($sql);
}

/**
 * NO MIGRATION NEEDED!
 *
 * We use V3 approach: fallback mechanism without migration.
 *
 * When loading ShopVariation:
 * 1. Check if DataId is set
 * 2. If DataId is empty → load ShopVariation from 'data' column (old format)
 * 3. If DataId is set → load from magnalister_amazon_prepare_longtext table (new format)
 *
 * When saving ShopVariation:
 * 1. Save to magnalister_amazon_prepare_longtext table
 * 2. Set DataId reference
 * 3. Clear ShopVariation from 'data' column (keep other data)
 *
 * This approach:
 * - Avoids heavy migration on 5000+ products
 * - Prevents timeout issues
 * - Allows gradual migration as products are edited
 */

// Register functions to be executed
$functions[] = 'amazon_create_prepare_longtext_table';
$functions[] = 'amazon_add_dataid_column';
// NO migration function - using fallback mechanism instead