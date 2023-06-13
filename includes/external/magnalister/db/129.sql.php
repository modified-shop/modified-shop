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

function md_db_update_129() {
    if (!MagnaDB::gi()->columnExistsInTable('HandlingTime', TABLE_MAGNA_HITMEISTER_PREPARE)) {
        MagnaDB::gi()->query("ALTER TABLE `".TABLE_MAGNA_HITMEISTER_PREPARE."` ADD COLUMN `HandlingTime` INT(2) DEFAULT 1 AFTER `ShippingTime`");
        $aCodeToHandlingTime = array (
                    'a' => array('HandlingTime' => 1),
                    'b' => array('HandlingTime' => 1),
                    'c' => array('HandlingTime' => 4),
                    'd' => array('HandlingTime' => 7),
                    'e' => array('HandlingTime' => 11),
                    'f' => array('HandlingTime' => 15),
                    'g' => array('HandlingTime' => 29),
                    'h' => array('HandlingTime' => 21),
                    'i' => array('HandlingTime' => 50),
                    'm' => array('HandlingTime' => 1),
                );
        foreach ($aCodeToHandlingTime as $sCode => $aHandlingTime) {
        MagnaDB::gi()->update(TABLE_MAGNA_HITMEISTER_PREPARE,
                    $aHandlingTime,
                    array (
                        'ShippingTime' => $sCode
                ));
        }
        // with API v2, only the entry "m" (matching) has a meaning
        MagnaDB::gi()->query("ALTER TABLE `".TABLE_MAGNA_HITMEISTER_PREPARE."` CHANGE COLUMN `ShippingTime` `ShippingTime` CHAR(1) DEFAULT NULL");
        // set the config value for default handlingtime, we may have multiple accounts
        $aDefaultShippingTimes = MagnaDB::gi()->fetchArray("SELECT * FROM ".TABLE_MAGNA_CONFIG." WHERE mkey = 'hitmeister.shippingtime'");
        foreach ($aDefaultShippingTimes as $aDefaultShippingTime) {
            MagnaDB::gi()->query("INSERT INTO ".TABLE_MAGNA_CONFIG." (mpID, mkey, value) VALUES (".$aDefaultShippingTime['mpID'].", 'hitmeister.handlingtime', ".$aCodeToHandlingTime[$aDefaultShippingTime['value']]['HandlingTime'].")");
        }
        // set the confing value for handlingtime matching
        $aShippingTimeMatchings = MagnaDB::gi()->fetchArray("SELECT * FROM ".TABLE_MAGNA_CONFIG." WHERE mkey = 'hitmeister.shippingtimematching.values'");
        foreach ($aShippingTimeMatchings as $aShippingTimeMatching) {
            $aShippingTimeMatchingValues = json_decode($aShippingTimeMatching['value'], true);
            $aHandlingTimeMatchingValues = array();
            foreach ($aShippingTimeMatchingValues as $sShippingTimeMatchingKey => $sShippingTimeMatchingValue) {
                $aHandlingTimeMatchingValues[$sShippingTimeMatchingKey] = $aCodeToHandlingTime[$sShippingTimeMatchingValue]['HandlingTime'];
            }
            $jHandlingTimeMatchingValues = json_encode($aHandlingTimeMatchingValues);
            MagnaDB::gi()->query("INSERT INTO ".TABLE_MAGNA_CONFIG." (mpID, mkey, value) VALUES (".$aShippingTimeMatching['mpID'].", 'hitmeister.handlingtimematching.values', '".$jHandlingTimeMatchingValues."')");

        }
    }
}

$functions[] = 'md_db_update_129';

