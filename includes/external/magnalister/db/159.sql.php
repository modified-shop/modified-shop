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
 * (c) 2010 - 2026 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

function etsy_add_return_policy() {
    $oDB = MagnaDB::gi();
    if ($oDB->tableExists(TABLE_MAGNA_ETSY_PREPARE) && !$oDB->columnExistsInTable('ReturnPolicy', TABLE_MAGNA_ETSY_PREPARE)) {
        $oDB->query("ALTER TABLE `".TABLE_MAGNA_ETSY_PREPARE."` ADD `ReturnPolicy` varchar(127) NOT NULL DEFAULT '' COMMENT 'New column for Etsy return policy id' AFTER `ProcessingProfile`;");
    }
}

$functions[] = 'etsy_add_return_policy';