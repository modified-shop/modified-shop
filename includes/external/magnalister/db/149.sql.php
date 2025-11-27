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
 * (c) 2010 - 2025 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$functions = array();

function mlDbUpdate_AmazonPrepareTable_149() {
    if (MagnaDB::gi()->tableExists(TABLE_MAGNA_AMAZON_APPLY) &&
        !MagnaDB::gi()->recordExists(TABLE_MAGNA_CONFIG, array('mpID' => 0, 'mkey' => 'AmazonListingMigration'))
    ) {
        MagnaDB::gi()->update(TABLE_MAGNA_AMAZON_APPLY,
            array('is_incomplete' => 'true')
        );
        MagnaDB::gi()->insert(TABLE_MAGNA_CONFIG, array('mpid' => 0, 'mkey' => 'AmazonListingMigration', 'value' => 1));
    }
}

function mlDbUpdate_AmazonPrepareTable_BrowseNodes_149() {
    if (MagnaDB::gi()->columnExistsInTable('topBrowseNode1', TABLE_MAGNA_AMAZON_APPLY)
        && MagnaDB::gi()->columnExistsInTable('topBrowseNode2', TABLE_MAGNA_AMAZON_APPLY)
    ) {
        MagnaDB::gi()->query(
            'ALTER TABLE ' . TABLE_MAGNA_AMAZON_APPLY .
            ' MODIFY COLUMN `topBrowseNode1` TEXT NULL DEFAULT NULL, ' .
            'MODIFY COLUMN `topBrowseNode2` TEXT NULL DEFAULT NULL'
        );
    }
}

$functions[] = 'mlDbUpdate_AmazonPrepareTable_149';
$functions[] = 'mlDbUpdate_AmazonPrepareTable_BrowseNodes_149';

