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
 * (c) 2010 - 2020 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

function etsy_update_item_creation_time_2023() {
    MagnaDB::gi()->query("UPDATE ".TABLE_MAGNA_ETSY_PREPARE."
        SET Whenmade = '2020_2023' WHERE Whenmade = '2020_2021'
    ");
    MagnaDB::gi()->query("UPDATE ".TABLE_MAGNA_ETSY_PREPARE."
        SET Whenmade = '2004_2009' WHERE Whenmade = '2000_2009'
    ");
    MagnaDB::gi()->query("UPDATE ".TABLE_MAGNA_ETSY_PREPARE."
        SET Whenmade = '1990s' WHERE Whenmade = 'before_2000'
    ");
    return;
}

$functions[] = 'etsy_update_item_creation_time_2023';
