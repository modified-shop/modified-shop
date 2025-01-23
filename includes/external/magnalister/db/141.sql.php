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

# Amazon-Modul: Default values for properties table
$queries[] = 'ALTER TABLE '.TABLE_MAGNA_AMAZON_PROPERTIES.'
    CHANGE COLUMN asin_type asin_type INT(2) DEFAULT 0,
    CHANGE COLUMN amazon_price amazon_price DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
    CHANGE COLUMN image_url image_url TEXT NOT NULL DEFAULT \'\',
    CHANGE COLUMN item_note item_note TEXT NOT NULL DEFAULT \'\',
    CHANGE COLUMN will_ship_internationally will_ship_internationally INT(2) NOT NULL DEFAULT 0,
    CHANGE COLUMN category_id category_id VARCHAR(15) NOT NULL DEFAULT \'0\',
    CHANGE COLUMN category_name category_name VARCHAR(200) NOT NULL DEFAULT \'0\',
    CHANGE COLUMN lowestprice lowestprice DECIMAL(15,2) NOT NULL DEFAULT 0.00';
