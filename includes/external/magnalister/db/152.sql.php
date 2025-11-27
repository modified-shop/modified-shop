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
function etsy_add_processing_profile() {
    $oDB = MagnaDB::gi();
    if ($oDB->tableExists(TABLE_MAGNA_ETSY_PREPARE) && !$oDB->columnExistsInTable('ProcessingProfile', TABLE_MAGNA_ETSY_PREPARE)) {
        $oDB->query("ALTER TABLE `".TABLE_MAGNA_ETSY_PREPARE."` ADD `ProcessingProfile` varchar(127) NOT NULL DEFAULT '' COMMENT 'New column for Etsy V3 API to define processing time in each variation' AFTER `ShippingProfile`;");
    }
}

function etsy_set_products_open_if_no_processing_profile() {
    $oDB = MagnaDB::gi();
    if (!$oDB->tableExists(TABLE_MAGNA_ETSY_PREPARE)) {
        return;
    }
    if ($oDB->tableExists(TABLE_MAGNA_AMAZON_APPLY) &&
        !$oDB->recordExists(TABLE_MAGNA_CONFIG, array('mpID' => 0, 'mkey' => 'EtsyProcessingProfileSetProductsToOpen'))
    ) {

        // Process updates in chunks of 200
        $batchSize = 200;
        while (true) {
            $rows = $oDB->fetchArray(
                "SELECT mpID, products_id             
             FROM `" . TABLE_MAGNA_ETSY_PREPARE . "`             
             WHERE `Verified` = 'OK' AND (`ProcessingProfile` = '' OR `ProcessingProfile` IS NULL)             
             LIMIT " . $batchSize,
                true
            );

            if (empty($rows)) {
                break; // nothing left to update
            }

            $conditions = array();
            foreach ($rows as $row) {
                $mpID = (int)$row['mpID'];
                $pID = (int)$row['products_id'];
                $conditions[] = "(mpID = {$mpID} AND products_id = {$pID})";
            }
            $where = implode(' OR ', $conditions);

            // Update only this batch
            $oDB->query("UPDATE `" . TABLE_MAGNA_ETSY_PREPARE . "` SET `Verified` = 'OPEN' WHERE " . $where);

        }
        $oDB->insert(TABLE_MAGNA_CONFIG, array('mpid' => 0, 'mkey' => 'EtsyProcessingProfileSetProductsToOpen', 'value' => 1));
    }
}

$functions[] = 'etsy_add_processing_profile';
$functions[] = 'etsy_set_products_open_if_no_processing_profile';
