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
 * Fix customers with missing customers_default_address_id
 *
 * Bug: A previous change replaced mysqli::ping() with mysqli::query('DO 1')
 * which reset insert_id to 0, causing customers to be created without a valid
 * default address ID. This caused Gambio errors like:
 * "Customer::setDefaultAddress() must be an instance of CustomerAddressInterface, null given"
 */
function sql_155_fix_customers_missing_default_address_id() {
    $oDB = MagnaDB::gi();

    // Only fix customers that were imported via magnalister and have an address
    $sql = "
        UPDATE ".TABLE_CUSTOMERS." c
        INNER JOIN ".TABLE_ADDRESS_BOOK." ab ON ab.customers_id = c.customers_id
        SET c.customers_default_address_id = (
            SELECT MIN(ab2.address_book_id)
            FROM ".TABLE_ADDRESS_BOOK." ab2
            WHERE ab2.customers_id = c.customers_id
        )
        WHERE (c.customers_default_address_id = 0 OR c.customers_default_address_id IS NULL)
    ";

    $oDB->query($sql);
}

$functions[] = 'sql_155_fix_customers_missing_default_address_id';
