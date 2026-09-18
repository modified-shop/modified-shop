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
 * (c) 2010 - 2026 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

/**
 * Content-addressed store for the Temu attribute-matching JSON, mirroring
 * magnalister_amazon_prepare_longtext.
 *
 * magnalister_temu_prepare_longtext holds one row per product with the JSON inlined, so
 * preparing a whole category writes one full copy of the same category template per
 * product. Addressing the value by the hash of its content means those products share a
 * single row and the reference lives on the per-product prepare row.
 */
function temu_create_longtext_table() {
    $oDB = MagnaDB::gi();

    if ($oDB->tableExists('magnalister_temu_longtext')) {
        return;
    }

    $oDB->query("
        CREATE TABLE IF NOT EXISTS `magnalister_temu_longtext` (
          `TextId` varchar(64) NOT NULL COMMENT 'SHA256 of the value',
          `ReferenceFieldName` varchar(64) NOT NULL COMMENT 'ShopVariation | CategoryIndependentShopVariation',
          `Value` mediumtext COMMENT 'JSON encoded attribute matching',
          `CreatedAt` datetime DEFAULT NULL,
          UNIQUE KEY `UC_TextIdReferenceFieldName` (`TextId`, `ReferenceFieldName`),
          KEY `idx_textid` (`TextId`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Deduplicated Temu attribute matching JSON';
    ");
}

/**
 * Adds the two references from the per-product prepare row into the content store, the
 * way magnalister_amazon_apply.DataId points into the Amazon longtext table.
 *
 * Both stay NULL until that product is next saved; readers fall back to the legacy
 * inline columns while they are, so nothing has to be migrated up front.
 */
function temu_add_textid_columns() {
    $oDB = MagnaDB::gi();

    if (!$oDB->tableExists(TABLE_MAGNA_TEMU_PREPARE)) {
        return;
    }

    if (!$oDB->columnExistsInTable('ShopVariationTextId', TABLE_MAGNA_TEMU_PREPARE)) {
        $oDB->query("
            ALTER TABLE `" . TABLE_MAGNA_TEMU_PREPARE . "`
            ADD COLUMN `ShopVariationTextId` varchar(64) DEFAULT NULL
            COMMENT 'TextId reference into magnalister_temu_longtext'
        ");
    }

    if (!$oDB->columnExistsInTable('CategoryIndependentTextId', TABLE_MAGNA_TEMU_PREPARE)) {
        $oDB->query("
            ALTER TABLE `" . TABLE_MAGNA_TEMU_PREPARE . "`
            ADD COLUMN `CategoryIndependentTextId` varchar(64) DEFAULT NULL
            COMMENT 'TextId reference into magnalister_temu_longtext'
        ");
    }
}

/**
 * NO DATA MIGRATION.
 *
 * Existing rows in magnalister_temu_prepare_longtext are left where they are and keep
 * being read as long as the product has no TextId reference yet. A product moves to the
 * content store the next time it is saved, which is the same fallback mechanism the
 * Amazon tables use (see 154.sql.php).
 *
 * A one-shot migration was rejected because of four properties of the existing data:
 *   - magnalister_temu_prepare is keyed by (mpID, products_id, PrepareType) while the
 *     longtext row carries no PrepareType, so the target row would be ambiguous;
 *   - longtext rows exist for products that have no prepare row at all, leaving the
 *     reference nowhere to live;
 *   - idx_mp_product is not unique, so duplicate longtext rows are already possible and
 *     a winner would have to be guessed;
 *   - both tables are MyISAM, so the migration could not run in a transaction and a
 *     failure would leave the data half-converted.
 *
 * The legacy table is dropped in a later release, once the references have filled in.
 */

$functions[] = 'temu_create_longtext_table';
$functions[] = 'temu_add_textid_columns';
