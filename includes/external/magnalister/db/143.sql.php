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
 * (c) 2010 - 2024 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

/**
 * Update the cross border configuration to a valid state.
 *
 * @return void
 */
function metro_cross_borders_db_update_143() {
    require_once __DIR__.'/../php/modules/metro/classes/MetroCrossBordersConfiguration.php';
    $cross_borders_conf = MetroCrossBordersConfiguration::gi();

    if (1 < $cross_borders_conf->countMarketplaces()) {
        $groups = array();
        foreach ($cross_borders_conf->iterateMarketplaces() as $marketplace) {
            $group_key = $marketplace['metro.clientkey'].';'.$marketplace['metro.shippingorigin'];
            if (!array_key_exists($group_key, $groups)) {
                $groups[$group_key] = array();
            }
            $groups[$group_key][$marketplace['mpID']] = $marketplace;
        }

        $db = MagnaDB::gi();
        foreach ($groups as $group) {
            $first = null;
            foreach ($group as $marketplace_id => $setting) {
                if ('auto' == $setting) {
                    // first one goes through, all others will be updated to no and quantity settings set to the first
                    // one
                    if (null === $first) {
                        $first = $setting;
                    } else {
                        $db->update('magnalister_config', array('value' => 'no'),
                            array('mpID' => $marketplace_id, 'mkey' => 'metro.stocksync.tomarketplace'));
                        $db->update('magnalister_config', array('value' => $first['metro.maxquantity']),
                            array('mpID' => $marketplace_id, 'mkey' => 'metro.maxquantity'));
                        $db->update('magnalister_config', array('value' => $first['metro.quantity.type']),
                            array('mpID' => $marketplace_id, 'mkey' => 'metro.quantity.type'));
                        $db->update('magnalister_config', array('value' => $first['metro.quantity.value']),
                            array('mpID' => $marketplace_id, 'mkey' => 'metro.quantity.value'));
                    }
                }
            }
        }
    }
}

$functions[] = 'metro_cross_borders_db_update_143';
