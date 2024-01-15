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

function md_db_update_137() {
    if (    MagnaDB::gi()->columnExistsInTable('leadtimeToShip', TABLE_MAGNA_AMAZON_APPLY)
        && MagnaDB::gi()->columnType('leadtimeToShip', TABLE_MAGNA_AMAZON_APPLY) == 'int(11)'

    ) {
        MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_AMAZON_APPLY.'` CHANGE `leadtimeToShip` `leadtimeToShip` VARCHAR(11) NOT NULL DEFAULT \'-\';');
    }

    if (    MagnaDB::gi()->columnExistsInTable('leadtimeToShip', TABLE_MAGNA_AMAZON_PROPERTIES)
        && MagnaDB::gi()->columnType('leadtimeToShip', TABLE_MAGNA_AMAZON_PROPERTIES) == 'int(11)'
    ) {
        MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_AMAZON_PROPERTIES.'` CHANGE `leadtimeToShip` `leadtimeToShip` VARCHAR(11) NOT NULL DEFAULT \'-\';');
    }

    if (!MagnaDB::gi()->recordExists(TABLE_MAGNA_CONFIG, array('mpID' => 0, 'mkey' => 'AmazonShippingTimeMigrationV2'))) {
        MagnaDB::gi()->update(TABLE_MAGNA_AMAZON_APPLY,
            array('leadtimeToShip' => '-'),
            array('leadtimeToShip' => '0')
        );
        MagnaDB::gi()->update(TABLE_MAGNA_AMAZON_PROPERTIES,
            array('leadtimeToShip' => '-'),
            array('leadtimeToShip' => '0')
        );
        //update config values -> amazon.leadtimetoship and amazon.leadtimetoshipmatching.values
        // empty as "-" and "0" as "-"
        MagnaDB::gi()->update(TABLE_MAGNA_CONFIG,
            array('value' => '-'),
            array(
                'mkey' => 'amazon.leadtimetoship',
                'value' => '',
            )
        );
        $configMatching = MagnaDB::gi()->fetchArray("
            SELECT mpID, value
              FROM ".TABLE_MAGNA_CONFIG."
             WHERE mkey = 'amazon.leadtimetoshipmatching.values'
        ");
        foreach ($configMatching as $matching) {
            $aConfigMatching = json_decode($matching['value'], true);
            foreach ($aConfigMatching as &$match) {
                if ($match == '0') {
                    $match = '-';
                }
            }
            MagnaDB::gi()->update(TABLE_MAGNA_CONFIG,
                array(
                    'value' => json_encode($aConfigMatching)
                ),
                array(
                    'mpID' => $matching['mpID'],
                    'mkey' => 'amazon.leadtimetoshipmatching.values'
                )
            );
        }

        MagnaDB::gi()->insert('magnalister_config', array('mpID' => 0, 'mkey' => 'AmazonShippingTimeMigrationV2', 'value' => 1));
    }

}

$functions[] = 'md_db_update_137';
