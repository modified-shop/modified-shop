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
 * (c) 2010 - 2021 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

define('TABLE_MAGNA_CONFIG', 'magnalister_config');
define('TABLE_MAGNA_SESSION', 'magnalister_session');
define('TABLE_MAGNA_SELECTION', 'magnalister_selection');
define('TABLE_MAGNA_GLOBAL_SELECTION', 'magnalister_global_selection');
define('TABLE_MAGNA_SELECTION_TEMPLATES', 'magnalister_selection_templates');
define('TABLE_MAGNA_SELECTION_TEMPLATE_ENTRIES', 'magnalister_selection_template_entries');
define('TABLE_MAGNA_ORDERS', 'magnalister_orders');
define('TABLE_MAGNA_VARIATIONS', 'magnalister_variations');
define('TABLE_MAGNA_AMAZON_PROPERTIES', 'magnalister_amazon_properties');
define('TABLE_MAGNA_AMAZON_ERRORLOG', 'magnalister_amazon_errorlog');
define('TABLE_MAGNA_AMAZON_APPLY', 'magnalister_amazon_apply');
define('TABLE_MAGNA_CS_ERRORLOG', 'magnalister_cs_errorlog');
define('TABLE_MAGNA_CS_DELETEDLOG', 'magnalister_cs_deletedlog');
define('TABLE_MAGNA_YATEGO_CATEGORIES', 'magnalister_yatego_categories');
define('TABLE_MAGNA_YATEGO_CUSTOM_CATEGORIES', 'magnalister_yatego_custom_categories');
define('TABLE_MAGNA_YATEGO_CATEGORYMATCHING', 'magnalister_yatego_categorymatching');
define('TABLE_MAGNA_EBAY_CATEGORIES', 'magnalister_ebay_categories');
define('TABLE_MAGNA_EBAY_PROPERTIES', 'magnalister_ebay_properties');
define('TABLE_MAGNA_EBAY_LISTINGS', 'magnalister_ebay_listings');
define('TABLE_MAGNA_EBAY_ERRORLOG', 'magnalister_ebay_errorlog');
define('TABLE_MAGNA_EBAY_DELETEDLOG', 'magnalister_ebay_deletedlog');
define('TABLE_MAGNA_TECDOC', 'magnalister_tecdoc');
define('TABLE_MAGNA_API_REQUESTS', 'magnalister_api_requests');
define('TABLE_MAGNA_COMPAT_CATEGORYMATCHING', 'magnalister_magnacompat_categorymatching');
define('TABLE_MAGNA_COMPAT_CATEGORIES', 'magnalister_magnacompat_categories');
define('TABLE_MAGNA_COMPAT_ERRORLOG', 'magnalister_magnacompat_errorlog');
define('TABLE_MAGNA_COMPAT_DELETEDLOG', 'magnalister_magnacompat_deletedlog');
define('TABLE_MAGNA_HITMEISTER_PREPARE', 'magnalister_hitmeister_prepare');
define('TABLE_MAGNA_HITMEISTER_VARIANTMATCHING', 'magnalister_hitmeister_variantmatching');
define('TABLE_MAGNA_CDISCOUNT_PREPARE', 'magnalister_cdiscount_prepare');
define('TABLE_MAGNA_CDISCOUNT_VARIANTMATCHING', 'magnalister_cdiscount_variantmatching');
define('TABLE_MAGNA_TRADORIA_PREPARE', 'magnalister_tradoria_prepare');
define('TABLE_MAGNA_TRADORIA_VARIANTMATCHING', 'magnalister_tradoria_variantmatching');
define('TABLE_MAGNA_HOOD_CATEGORIES', 'magnalister_hood_categories');
define('TABLE_MAGNA_HOOD_VARIANTMATCHING', 'magnalister_hood_variantmatching');
define('TABLE_MAGNA_HOOD_PROPERTIES', 'magnalister_hood_properties');
define('TABLE_MAGNA_HOOD_ERRORLOG', 'magnalister_hood_errorlog');
define('TABLE_MAGNA_HOOD_DELETEDLOG', 'magnalister_hood_deletedlog');
define('TABLE_MAGNA_DAWANDA_PROPERTIES', 'magnalister_dawanda_properties');
define('TABLE_MAGNA_BEPADO_PROPERTIES', 'magnalister_bepado_properties');
define('TABLE_MAGNA_RICARDO_PROPERTIES', 'magnalister_ricardo_properties');
define('TABLE_MAGNA_CHECK24_PROPERTIES', 'magnalister_check24_properties');
define('TABLE_MAGNA_FYNDIQ_PROPERTIES', 'magnalister_fyndiq_properties');
define('TABLE_MAGNA_IDEALO_PROPERTIES', 'magnalister_idealo_properties');
define('TABLE_MAGNA_PROPERTIES_DESCRIPTION', 'properties_description');
define('TABLE_MAGNA_PROPERTIES_DESCRIPTION_VALUES', 'properties_values_description');
define('TABLE_MAGNA_PROPERTIES_VALUES', 'properties_values');
define('TABLE_MAGNA_AMAZON_VARIANTMATCHING', 'magnalister_amazon_variantmatching');
define('TABLE_MAGNA_PRICEMINISTER_PREPARE', 'magnalister_priceminister_prepare');
define('TABLE_MAGNA_PRICEMINISTER_VARIANTMATCHING', 'magnalister_priceminister_variantmatching');
define('TABLE_MAGNA_CROWDFOX_PREPARE', 'magnalister_crowdfox_prepare');
define('TABLE_MAGNA_CROWDFOX_VARIANTMATCHING', 'magnalister_crowdfox_variantmatching');
define('TABLE_MAGNA_DAWANDA_VARIANTMATCHING', 'magnalister_dawanda_variantmatching');
define('TABLE_MAGNA_EBAY_VARIANTMATCHING', 'magnalister_ebay_variantmatching');
define('TABLE_MAGNA_ETSY_CATEGORIES', 'magnalister_etsy_categories');
define('TABLE_MAGNA_ETSY_PREPARE', 'magnalister_etsy_prepare');
define('TABLE_MAGNA_ETSY_VARIANTMATCHING', 'magnalister_etsy_variantmatching');
define('TABLE_MAGNA_GOOGLESHOPPING_PROPERTIES', 'magnalister_googleshopping_properties');
define('TABLE_MAGNA_GOOGLESHOPPING_CATEGORIES', 'magnalister_googleshopping_categories');
define('TABLE_MAGNA_GOOGLESHOPPING_PREPARE', 'magnalister_googleshopping_prepare');
define('TABLE_MAGNA_GOOGLESHOPPING_VARIANTMATCHING', 'magnalister_googleshopping_variantmatching');
define('TABLE_MAGNA_METRO_PROPERTIES', 'magnalister_metro_properties');
define('TABLE_MAGNA_METRO_CATEGORIES', 'magnalister_metro_categories');
define('TABLE_MAGNA_METRO_PREPARE', 'magnalister_metro_prepare');
define('TABLE_MAGNA_METRO_VARIANTMATCHING', 'magnalister_metro_variantmatching');
define('TABLE_MAGNA_OTTO_PROPERTIES', 'magnalister_otto_properties');
define('TABLE_MAGNA_OTTO_CATEGORIES_MARKETPLACE', 'magnalister_otto_categories_marketplace');
define('TABLE_MAGNA_OTTO_PREPARE', 'magnalister_otto_prepare');
define('TABLE_MAGNA_OTTO_VARIANTMATCHING', 'magnalister_otto_variantmatching');
