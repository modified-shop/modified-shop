<?php
/**
 *
 * Copyright Shopgate Inc.
 *
 * Licensed under the GNU General Public License, Version 2 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    Shopgate Inc, 804 Congress Ave, Austin, Texas 78701 <interfaces@shopgate.com>
 * @copyright Shopgate Inc
 * @license   https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU General Public License, Version 2
 *
 */

define('MODULE_PAYMENT_SHOPGATE_TEXT_TITLE', 'Shopgate');
define(
    'MODULE_PAYMENT_SHOPGATE_TEXT_DESCRIPTION', 'Shopgate - Mobile Shopping.'
);
define(
    'MODULE_PAYMENT_SHOPGATE_TEXT_INFO', 'Orders are already paid at Shopgate.'
);

define('MODULE_PAYMENT_SHOPGATE_ORDER_LINE_TEXT_SHIPPING', 'Shipping');
define('MODULE_PAYMENT_SHOPGATE_ORDER_LINE_TEXT_SUBTOTAL', 'Subtotal');
define('MODULE_PAYMENT_SHOPGATE_ORDER_LINE_TEXT_PAYMENTFEE', 'Payment Fees');
define('MODULE_PAYMENT_SHOPGATE_ORDER_LINE_TEXT_TOTAL', 'Total');

define('MODULE_PAYMENT_SHOPGATE_TEXT_EMAIL_FOOTER', "");
define(
    'MODULE_PAYMENT_SHOPGATE_STATUS_TITLE', 'Shopgate payment module activated:'
);

define('MODULE_PAYMENT_SHOPGATE_STATUS_DESC', '');
define('MODULE_PAYMENT_SHOPGATE_ALLOWED_TITLE', '');
define('MODULE_PAYMENT_SHOPGATE_ALLOWED_DESC', '');
define('MODULE_PAYMENT_SHOPGATE_PAYTO_TITLE', '');
define('MODULE_PAYMENT_SHOPGATE_PAYTO_DESC', '');
define('MODULE_PAYMENT_SHOPGATE_SORT_ORDER_TITLE', 'Sort order of display');
define(
    'MODULE_PAYMENT_SHOPGATE_SORT_ORDER_DESC',
    'Sort order of display. Lowest is displayed first.'
);
define('MODULE_PAYMENT_SHOPGATE_ZONE_TITLE', '');
define('MODULE_PAYMENT_SHOPGATE_ZONE_DESC', '');
define('MODULE_PAYMENT_SHOPGATE_ORDER_STATUS_ID_TITLE', 'Status');
define(
    'MODULE_PAYMENT_SHOPGATE_ORDER_STATUS_ID_DESC',
    'Set status of orders imported by this module to:'
);
define(
    'MODULE_PAYMENT_SHOPGATE_ERROR_READING_LANGUAGES',
    'Error configuring language settings.'
);
define(
    'MODULE_PAYMENT_SHOPGATE_ERROR_LOADING_CONFIG',
    'Error loading configuration.'
);
define(
    'MODULE_PAYMENT_SHOPGATE_ERROR_SAVING_CONFIG',
    'Error saving configuration. ' .
    'Please check the permissions (777) for the folder ' .
    '&quot;/shopgate_library/config&quot; of the Shopgate plugin.'
);

define("MODULE_PAYMENT_SHOPGATE_LABEL_NEW_PRODUCTS", "New products");
define("MODULE_PAYMENT_SHOPGATE_LABEL_SPECIAL_PRODUCTS", "Special products");
define(
    'SHOPGATE_ORDER_CUSTOM_FIELD', 'Custom field(s) of this Shopgate order:'
);

define(
    "SHOPGATE_COUPON_ERROR_NEED_ACCOUNT",
    "You need do be logged in to use this coupon"
);
define(
    "SHOPGATE_COUPON_ERROR_RESTRICTED_PRODUCTS",
    "This coupon is restricted to special products"
);
define(
    "SHOPGATE_COUPON_ERROR_RESTRICTED_CATEGORIES",
    "This coupon is restricted to special categories"
);
define(
    "SHOPGATE_COUPON_ERROR_MINIMUM_ORDER_AMOUNT_NOT_REACHED",
    "This coupon has a minimum order amount which has not been reached"
);
