# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

## [2.9.69] - 2017-09-13
### Added
- ShopgateConfigInterface::buildConfigFilePath()
- ShopgateConfig::buildConfigFilePath()

### Deprecated
- ShopgateConfig::$config_folder_path, use ShopgateConfigInterface::buildConfigFilePath() instead

## [2.9.68] - 2017-08-29
### Changed
- fixed a bug in class ShopgateObject method jsonDecode

## [2.9.67] - 2017-08-22
### Changed
- renamed Shopgate Library to Shopgate Cart Integration SDK
- migrated Shopgate Cart Integration SDK to GitHub

## [2.9.66]
- added LICENSE.md, containing a copy of the Apache License, v2.0
- changed license headers to reflect the new license
- added README.md with general description and how to get started
- expanded Shopgate configuration by facebook_pixel_id

## [2.9.65]
- adjusted internal stack trace generation in order to restore compatibility with PHP 5.2.17

## [2.9.64]
- added missing getter for review XML filename in the configuration

## [2.9.63]
- added payment method constants for Merchant Payment

## [2.9.62]
- restored compatibility for PHP 5.2.17

## [2.9.61]
- fixed a bug in stack trace generation that may appear on certain server configurations

## [2.9.60]
- fixed debug logging

## [2.9.59]
- fixed a bug in the stack trace generation
- fixed a bug in registering an exception handler
- Shopgate configuration extended with parameter exclude_item_ids

## [2.9.58]
- fixed 'undefined index' notice in ShopgateBuilder
- fixed turning on error_reporting for all errors by default

## [2.9.57]
- fixed missing plugin_version in JSON response

## [2.9.56]
- fixed class names for logging classes to avoid duplicate declarations
- removed self-logging functionality from ShopgateLibraryException
- added proper stack trace generation
- added proper handlers for errors and exceptions
- added a new shutdown function to be able to log fatal errors that cause a script to stop unexpectedly
- moved initialization of error reporting settings and handlers from ShopgatePluginApi to ShopgateBuilder
- ShopgatePluginApi is now working with the "logging strategy" introduced in 2.9.55 if it was set
- ShopgatePluginApi is now using the stack trace generation mentioned above if it was set
- ShopgatePluginApi won't log stack traces anymore when using ShopgateLogger to avoid logging sensitive information
- deprecated ShopgateLogger
- added ShopgateClient to ShopgateCartBase to distinguish between the mobile clients
- added error code to remove items silently in the cart validation
- replaced "document.write" with asynchronous loading of mobile redirect / mobile header
- added Content-Length header to Response objects for CSV, XML and JSON responses

## [2.9.55]
- refactored ShopgateLogger to be able to pass a strategy for logging

## [2.9.54]
- adjusting CartCustomer object properties to contain an empty group array

## [2.9.53]
- added missing entries to composer.json
- fix for category asArray method when image is missing

## [2.9.52]
- extended method clear_cache so plugin related cache files can be deleted as well

## [2.9.51]
- http/js redirect split via new forwarder class

## [2.9.50]
- added constant for new error code 222 (missing fields in register_customer)
- updated deprecated status of product visibility statuses

## [2.9.49]
- fixed encoding issue related to objects in Plugin API responses that are in JSON

## [2.9.48]
- added process ID of incoming requests to access log
- added new constant for checkCart error "CART_ITEM_PRODUCT_NOT_ALLOWED"

## [2.9.47]
- "manufacturer_item_number" in the XML export of products is now CDATA, allowing for a correct handling of Umlauts and special characters

## [2.9.46]
- made isMobile() check public in the redirector class

## [2.9.45]
- "tax_class" in the XML export of products is now CDATA, allowing for a correct handling of Umlauts and special characters

## [2.9.44]
- won't crash anymore when an array of objects expected from the Shopgate Merchant API is set, but empty
- fixed encoding issue with non-object responses

## [2.9.43]
- changed error reporting settings for development environments
- fixes redirect when a shop is not active & full view mode

## [2.9.42]
- fixed an issue with home page infinite redirect loop
- fixed a bug in processing the user agent blacklist used for mobile redirect
- moved ShopgateOrder::$tracking_get_parameters and its getter & setter to ShopgateCartBase

## [2.9.41]
- meta tags and mobile header script won't be displayed anymore for inactive shops when using Shopgate_Helper_Redirect_MobileRedirect

## [2.9.40]
- added better escaping for the mobile redirect of search queries
- added customer ip address getter/setter in order object
- fixed issue with curly brace regex for jsHeader template
- started unit test structure
- removed PHP 4 style constructors from vendor classes as of http://php.net/manual/de/migration70.deprecated.php

## [2.9.39]
- adjusted type-hints to API documentations

## [2.9.38]
- minor bug fixes

## [2.9.37]
- added outputting open graph and other tags with the mobile redirect if activated in the configuration
- added a replacement class tree for ShopgateMobileRedirect
- ShopgateMobileRedirect is now deprecated

## [2.9.36]
- added a fallback in case that json_encode returns false (failure)
- added new constant ShopgateLibraryException::CART_ITEM_INVALID_PRODUCT_COMBINATION
- added a fallback in case that json_encode or json_decode return false (failure)
- added escaping to the mobile redirect of search queries

## [2.9.35]
- improved logging of Exception messages
- deprecated methods now disable themselves when called but not implemented

## [2.9.34]
- the method ShopgatePlugin::redeemCoupons() is now deprecated and not abstract anymore

## [2.9.33]
- changed the initial value of the ShopgateDeliveryNote::$shipping_service_id entry to null
- added payment method constant for Paypal Plus

## [2.9.32]
- amount_complete getter is now returning the correct field

## [2.9.31]
- added ShopgateShippingInfo::$amount_net and $amount_gross properties, getters and setters
- ShopgateShippingInfo::$amount, getter and setter are now deprecated

## [2.9.30]
- amount_complete is now being set correctly for get_orders

## [2.9.29]
- added a constructor to the category export model, so it can be called without any harm

## [2.9.28]
- XML export: fixed a bug that lead to unwanted occurrences of 'xmlns=""' in the XML

## [2.9.27]
- XML export: omit empty sub nodes if they are defined as optional by the XSD

## [2.9.26]
- added constants for order item types
- added external order items parameter: amount_items_gross; amount_items_net; amount_complete_net; amount_complete_gross

## [2.9.25]
- added and changed error messages for method checkCart and checkStock
- added ShopgateOrderItem::$type property, getter and setter
- fixed a bug in the comparison of objects leading to a "nesting level too deep" error

## [2.9.24]
- added constants for some payment methods

## [2.9.23]
- added sort order for options and option values

## [2.9.22]
- CSV exports: the methods ShopgatePlugin::createCategoriesCsv(), ::createItemsCsv() and ::createReviewsCsv() are now deprecated and not abstract anymore
- removed German changelog
- ShopgateMerchantApi::cancelOrder(): changed default value of parameter "$cancelCompleteOrder" from false to true

## [2.9.21]
- added constants for more payment types in class ShopgateCartBase

## [2.9.20]
- xml export does not include empty xml elements when they should actually be removed instead

## [2.9.19]
- product XML export: some XML nodes were exported with a "forceEmpty" attribute by mistake

## [2.9.18]
- Fixed a bug that broke compatibility with PHP < 5.3

## [2.9.17]
- fixed validation issue in XML export with boolean values

## [2.9.16]
- Shopgate configuration extended with parameter force_source_encoding

## [2.9.15]
- review XML export: fixed outdated type hint to Shopgate_Model_Review

## [2.9.14]
- exports: fixed a bug in setting memory limit and maximum execution time on export functions

## [2.9.13]
- ShopgateLibraryException: new constructor argument for previous Exception; improved stack trace logging
- product XML export: removed the subnode <paths> of <category>
- product XML export: The nodes <tax_percent> + <tax_class> will only be exported if a value was explicitly set
- XML export: all invalid characters are filtered out now (according to the rules from www.w3.org)
- added a new constant for product invisibility

## [2.9.12]
- ShopgateCustomer::getRegistrationDate() will now return the point in time a customer registered during ShopgatePlugin::registerCustomer()
- new methods ShopgateExternalOrder::setStatusName() and ::setStatusColor() to set an individual order status during ShopgatePlugin::getOrders()

## [2.9.11]
- product XML export: empty nodes in child products are now possible
- removed unsupported characters from CDATA
- bugfix: non-existent function ShopgateContainerVisitor::visitCoupon() was called

## [2.9.10]
- corrected wrong constant names

## [2.9.9]
- it is now possible to get instances of Shopgate helper classes via function call to ensure that the helper functionality is available on every level in the plugin

## [2.9.8]
- added setter and getter for external_customer_group_id
- on default redirect (i.e. we don't know the corresponding mobile page) no <link> tag is put out anymore
- corrected PHPDoc for some classes and methods

## [2.9.7]
- added support for tracking parameters in the import of orders

## [2.9.6]
- reworked access of request parameters

## [2.9.5]
- fixed a bug in processing the request parameters of API requests
- fixed XML export encoding issue

## [2.9.4]
- revised PHPDoc
- added constants for error codes of the Shopgate Merchant API
- removed direct access to $_REQUEST and $_GET

## [2.9.3]
- extended price model with base price

## [2.9.1]
- added CDATA for review author in xml

## [2.9.0]
- export of reviews as xml added

## [2.8.10]
- added cdata for deep links in categories and products (xml)
- extended cname validation
- removed legacy code

## [2.8.9]
- maximum memory value for executing scripts can be set now
- maximum execution time can be set now
- by default the ShopgateErrorHandler doesn't print the stacktrace

## [2.8.8]
- XML - Images new flag for isCover

## [2.8.7]
- integration for amazon payments

## [2.8.6]
- optimized function to remove empty XML nodes
- optimized function to prepare XML children

## [2.8.5]
- removed remaining git conflict markup
- replaced json_encode calls through the internal method jsonEncode

## [2.8.4]
- regex adjustment for cname validation
- new child node for grouped products / different display types added to xml

## [2.8.3]
- stripping invalid UTF-8 sequences now on XML export
- added setting 'supported_methods_cron' for plugins to tell which methods are supported by their cron action

## [2.8.2]
- fixed a bug in converting encodings into UTF-8

## [2.8.1]
- added property ShopgateExternalOrderExtraCost::$label with getter and setter
- on check_cart, the 'currency' of the cart in the response is pre-initialized with that of the cart in the request now

## [2.8.0]
- added support for synchronizing a favourite list via the Shopgate Plugin API
- added support for retrievieng orders from the shopping cart solution via the Shopgate Plugin API
- Shopgate orders now contain a field named "shipping_tax_percent" with the tax rate of shipping cost
- parameter $trackingNumber of ShopgateMerchantApi::addOrderDeliveryNote() is now optional (default: empty string)
- added 'related_shop_items' to the default products CSV row

## [2.7.2]
- obfuscation of the oauth_access_token on ping method output
- bugfix for inconsistency while renaming of methods

## [2.7.1]
- fixed a bug in the receive_authorization PluginAPI action
- shops that already have a configured Shopgate-Plugin, will now automatically get an oauth access token assigned

## [2.7.0]
- added new attribute (AggregateChildren) for xml / tierprices

## [2.6.11]
- included OAuth as an additional authentication service to connect with the Shopgate MerchantAPI.
- supports streaming exports to php://out

## [2.6.10]
- added a missing constant PLUGIN_API_NO_ITEMS.
- added a new constant CART_ITEM_REQUESTED_QUANTITY_UNDER_MINIMUM_QUANTITY
- added a new constant CART_ITEM_REQUESTED_QUANTITY_OVER_MAXIMUM_QUANTITY

## [2.6.9]
- bugfix for the encoding of get params while redirecting mobile
- bugfix for the filtering of get params while redirecting mobile
- Bugfix encoding unicode
- new error code constant PLUGIN_MISSING_ACCOUNT_PERMISSIONS

## [2.6.8]
- passthrough of get params for mobile redirect related to tracking
- extended method ping for xml paths

## [2.6.7]
- improved exception handling in loader methods
- implemented functionality to suppress the mobile redirect via javascript

## [2.6.6]
- Performance increased for the mobile redirect removed

## [2.6.5]
- bugfix in mobile redirect

## [2.6.4]
- Performance increased for the mobile redirect

## [2.6.3]
- Refactored constructor

## [2.6.2]
- New default data type "datetime" added

## [2.6.1]
- added feature to get categories in chunks

## [2.6.0]
- Added - Functionality XML export for products and categories.

## [2.5.7]
- added field display_name to class ShopgateShippingInfo
- added fields supported_fields_get_settings and supported_fields_check_cart in ShopgateConfig

## [2.5.6]
- modifed default value for "enable_default_redirect" in class ShopgateConfig
- embedding of Vary Header for pages which are potentially redirected to mobile devices
- implemented ShopgateCartCustomer and ShopgateCartCustomerGroup for checkCart

## [2.5.5]
- added a parameter to all encoding functions allowing to use iconv() instead of mb_convert_encoding()

## [2.5.4]
- extended the method ping with an additional information container ('shop_info')

## [2.5.3]
- added field "parent_item_number" to class ShopgateOrderItem

## [2.5.2]
- added ShopgateOrderItem::$order_item_id incl. getters and setters
- renamed function getErrorType() which is in global context to shopgateGetErrorType()

## [2.5.1]
- fixed a bug in the setter methods of some classes derived from ShopgateContainer

## [2.5.0]
- implemented media export for products
- implemented method check_stock to be able to check stock in realtime
- add error 303 for input field validation to check if input text is too long

## [2.4.16]
- added ShopgateCart::$internal_cart_info including getter and setter methods
- the field "internal_cart_info" is now processed when returned by ShopgatePlugin::checkCart()
- added ShopgateShippingInfo::$internal_cart_info including getter and setter methods
- removed the trailing "s" in ShopgateShippingMethod::$internal_cart_infos and its getter and setter methods

## [2.4.15]
- implemented switch for the use of tax classes

## [2.4.14]
- bugfix for custom_fields
- added a helper method "arrayCross" to create a cross product over multiple arrays, organized inside of a containing array

## [2.4.13]
- the currency in check_cart response now is transferred in whole cart scope

## [2.4.12]
- insert Visitor methods for Shipping, Payment and CartItem classes (array visitor)

## [2.4.11]
- fixed an error in the logging routine

## [2.4.10]
- insert Visitor methods for Shipping, Payment and CartItem classes (utf8 visitor)

## [2.4.9]
- method check_cart now also returns cart item stock information and shipping and payment methods which are available for cart address

## [2.4.8]
- added new error codes for get_customer method

## [2.4.7]
- error code for function register_customer implemented

## [2.4.6]
- added constants for more payment types in class ShopgateCartBase

## [2.4.5]
- improved logging for analyzing of fatal errors

## [2.4.4]
- implemented function to check if the values of two ShopgateContainer objects are equal

## [2.4.3]
- restored compatibility for PHP versions below 5.3

## [2.4.2]
- added custom input fields for user details to orders and addresses (ShopgateOrder::$custom_fields, ShopgateAddress::$custom_fields)
- fixed an error and decreased memory and CPU time consumption by a significant amount

## [2.4.1]
- added new error code (90 - error sending mail)

## [2.4.0]
- actions register_customer, get_debug_info and set_settings was added
- the <link> tag is now displayed in the <head> tag on activated shops only

## [2.3.10]
- it's possible to export any number of options, inputs and attributes while exporting item csv files

## [2.3.9]
- the ShopgateObject class now features a method for dumping objects that can't be dumped using var_dump, because of internal recursion (u_print_r).
- the ShopgatePlugin class now contains a helper method to retrieve the used ram that is also converted into the desired size-unit.
- when executing loaders using the "executeLoaders" method the logging of used ram can be activated so the ram before and after each called method is logged.
- Improved function splitStreetData() in class ShopgateAddress, because street and number were not split correctly in some cases.

## [2.3.8]
- handle extended shipping information that is provided in check_cart and redeem_coupon now
- constant values to use for converting weights

## [2.3.7]
- ShopgateConfig's "encoding" setting is now used for exporting products as well

## [2.3.6]
- added shopgate_license.txt

## [2.3.5]
- added index.php to each directory

## [2.3.4]
- bugfix inside of the ShopgateOrder class - delivery_notes are now initialized with an empty array
- on execution of loader methods, all thrown exceptions are processed to get more details

## [2.3.3]
- added license header

## [2.3.2]
- improved logging when a file couldn't be opened
- added new payment method ids
- Logger now allows to dynamically add field names that should be disuised in or removed from the log

## [2.3.1]
- if shop number is missing the js redirect script is not rendered
- ping returns size and ownership of files from now on

## [2.3.0]
- New function get_settings() for exporting the shop's tax rates, tax rules and tax classes (and other settings in the future)
- Standard value of send_customer_email for method add_order_delivery_note changed to false
- API calls can now contain a new field called "handle_errors". The given value will be passed on to error_reporting()

## [2.2.2]
- the authentication headers are regenerated on every call via the ShopgateMerchantApi class
- the setting "enable_get_redirect_keywords" is now all boolean; the prior use of it as cache lifetime does not apply any longer
- added new redirect type "default" for pages whose type cannot be determined
- added new setting "enable_default_redirect" to (de-)activate the "default" redirect type
- added new value "sl" for the setting "server" to enbale switching to Shopgate's Spotlight environment (API, Mobile Header, Mobile Redirect)
- paramter "cart" gets removed now on calls to the Shopgate Plugin API to prevent log files from growing too big

## [2.2.1]
- added switch for default redirect from content pages to mobile homepage in class ShopgateConfig
- added paramater send_customer_mail to Merchant API method addOrderDeliveryNote()
- support for new Shopgate shipping system

## [2.2.0]
- two new methods have been added and must be implemented: ShopgatePlugin::redeemCoupons() and ::checkCart(). These make coupons offered by the shoppingsystem available to the mobile shop
- add the parameter "keep_debug_log" to incoming requests to prevent the debug log file from being discarded

## [2.1.26]
- conversion of encodings is now performed via iconv if the mb_string extension for PHP is not installed
- implemented logic to handle processing of file-based global and language dependend configurations

## [2.1.25]
- fixed an issue producing an output during loading the configuration file
- implementation of the new Shopgate Merchant API fields "item_number_public" and "attributes" returned by "get_orders"
- fixed issue that caused renaming CSV files on Windows Server systems to fail

## [2.1.24]
- fixed issue with loading configuration via ob_start() / ob_end_clean()

## [2.1.23]
- on requests to the Shopgate Plugin API the shop number is checked now
- mobile redirection via alias is done to a http:// URL now
- the CNAME for mobile redirection must be http:// now
- fixed an error when loading configuration more than once

## [2.1.22]
- fixed issue on request for redirect keywords

## [2.1.21]
- fixed issue with loading the Shopgate configuration file

## [2.1.20]
- fixed issue with "enable_clear_log_file" setting

## [2.1.19]
- java script header added for linking online shop to the mobile shop site
- updated HTTP redirect code from 302 to 301
- new method "clear_cache" added

## [2.1.18]
- added new method ShopgateMerchantApi::batchAddItem()
- added new method ShopgateMerchantApi::batchUpdateItem()
- updated and corrected PHPDoc for API classes
- trailing slashes on the CNAME setting are cut off
- methods getCreateItemsCsvLoaders(), getCreateCategoriesCsvLoaders() and getCreateReviewsCsvLoaders() of class ShopgatePlugin are not "final" anymore
- fixed issue with function ini_get_all()

## [2.1.17]
- Mobile Header now gets attached via JavaScript for more flexibility in complicated layouts
- new settings "mobile_header_parent" and "mobile_header_prepend" in ShopgateConfig
- new method ShopgateRedirect::setParentElement()
- fixed the issue where redirection to "http://" was performed if no CNAME was set
- log, export and cache file now get the prefix "shopgate_"

## [2.1.16]
- Added function ShopgateAddress::getStreetNumber and ShopgateAddress::getStreetName
- http:// will add to cname if not given
- improved removal of <script>, <style> and <link> tags from the products description
- "ping" now checks the directory permissions of the configured directories for export, logs and cache

## [2.1.15]
- extended class ShopgateConfig for use with multiple configuration files depending on language or shop number

## [2.1.14]
- ShopgateMerchantApi::getMobileRedirectKeywords() is now deprecated
- added ShopgateMerchantApi::getMobileRedirectUserAgents()

## [2.1.13]
- the updateOrder() callback is now provided with the items of the order as well
- filtering of <script> tags in the export has been improved
- the getters of a ShopgateOrderItem object with no inputs or options set will now return empty arrays instead of null
- settings "country" and "language" have been added to the ShopgateConfig class
- added generic method to create redirection links for item numbers, category numbers and the welcome page
- fixed incompatibility issues between different PHP versions

## [2.1.12]
- fix possible timestamp-error
- set default timout for ShopagteMerchantApi to 30 seconds
- set timeout on getMobileRedirectKeywords to 1 second
- fixed compatibility issues with PHP < 5.3

## [2.1.11]
- script tags will be stripped off including content on products export

## [2.1.10]
- fixed timestamp error

## [2.1.9]
- extended authentication
- disable log entries if cache is not writeable

## [2.1.8]
- reduced notice Logs on ping action

## [2.1.7]
- return by reference needs &-sign on function name

## [2.1.6]
- new csv column "active_status"
- plugin API returns the plugin version if exists

## [2.1.5]
- fixed error in use of old configuration
- added changelog.txt

[Unreleased]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.69...HEAD
[2.9.69]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.68...2.9.69
[2.9.68]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.67...2.9.68
[2.9.67]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.66...2.9.67
[2.9.66]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.65...2.9.66
[2.9.65]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.64...2.9.65
[2.9.64]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.63...2.9.64
[2.9.63]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.62...2.9.63
[2.9.62]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.61...2.9.62
[2.9.61]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.60...2.9.61
[2.9.60]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.59...2.9.60
[2.9.59]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.58...2.9.59
[2.9.58]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.57...2.9.58
[2.9.57]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.56...2.9.57
[2.9.56]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.55...2.9.56
[2.9.55]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.54...2.9.55
[2.9.54]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.53...2.9.54
[2.9.53]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.52...2.9.53
[2.9.52]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.51...2.9.52
[2.9.51]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.50...2.9.51
[2.9.50]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.49...2.9.50
[2.9.49]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.48...2.9.49
[2.9.48]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.47...2.9.48
[2.9.47]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.46...2.9.47
[2.9.46]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.45...2.9.46
[2.9.45]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.44...2.9.45
[2.9.44]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.43...2.9.44
[2.9.43]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.42...2.9.43
[2.9.42]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.41...2.9.42
[2.9.41]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.40...2.9.41
[2.9.40]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.39...2.9.40
[2.9.39]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.38...2.9.39
[2.9.38]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.37...2.9.38
[2.9.37]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.36...2.9.37
[2.9.36]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.35...2.9.36
[2.9.35]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.34...2.9.35
[2.9.34]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.33...2.9.34
[2.9.33]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.32...2.9.33
[2.9.32]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.31...2.9.32
[2.9.31]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.30...2.9.31
[2.9.30]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.29...2.9.30
[2.9.29]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.28...2.9.29
[2.9.28]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.27...2.9.28
[2.9.27]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.26...2.9.27
[2.9.26]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.25...2.9.26
[2.9.25]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.24...2.9.25
[2.9.24]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.23...2.9.24
[2.9.23]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.22...2.9.23
[2.9.22]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.21...2.9.22
[2.9.21]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.20...2.9.21
[2.9.20]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.19...2.9.20
[2.9.19]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.18...2.9.19
[2.9.18]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.17...2.9.18
[2.9.17]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.16...2.9.17
[2.9.16]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.15...2.9.16
[2.9.15]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.14...2.9.15
[2.9.14]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.13...2.9.14
[2.9.13]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.12...2.9.13
[2.9.12]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.11...2.9.12
[2.9.11]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.10...2.9.11
[2.9.10]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.9...2.9.10
[2.9.9]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.8...2.9.9
[2.9.8]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.7...2.9.8
[2.9.7]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.6...2.9.7
[2.9.6]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.5...2.9.6
[2.9.5]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.4...2.9.5
[2.9.4]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.3...2.9.4
[2.9.3]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.2...2.9.3
[2.9.2]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.1...2.9.2
[2.9.1]: https://github.com/shopgate/cart-integration-sdk/compare/2.9.0...2.9.1
[2.9.0]: https://github.com/shopgate/cart-integration-sdk/compare/2.8.10...2.9.0
[2.8.10]: https://github.com/shopgate/cart-integration-sdk/compare/2.8.9...2.8.10
[2.8.9]: https://github.com/shopgate/cart-integration-sdk/compare/2.8.8...2.8.9
[2.8.8]: https://github.com/shopgate/cart-integration-sdk/compare/2.8.7...2.8.8
[2.8.7]: https://github.com/shopgate/cart-integration-sdk/compare/2.8.6...2.8.7
[2.8.6]: https://github.com/shopgate/cart-integration-sdk/compare/2.8.5...2.8.6
[2.8.5]: https://github.com/shopgate/cart-integration-sdk/compare/2.8.4...2.8.5
[2.8.4]: https://github.com/shopgate/cart-integration-sdk/compare/2.8.3...2.8.4
[2.8.3]: https://github.com/shopgate/cart-integration-sdk/compare/2.8.2...2.8.3
[2.8.2]: https://github.com/shopgate/cart-integration-sdk/compare/2.8.1...2.8.2
[2.8.1]: https://github.com/shopgate/cart-integration-sdk/compare/2.8.0...2.8.1
[2.8.0]: https://github.com/shopgate/cart-integration-sdk/compare/2.7.2...2.8.0
[2.7.2]: https://github.com/shopgate/cart-integration-sdk/compare/2.7.1...2.7.2
[2.7.1]: https://github.com/shopgate/cart-integration-sdk/compare/2.7.0...2.7.1
[2.7.0]: https://github.com/shopgate/cart-integration-sdk/compare/2.6.11...2.7.0
[2.6.11]: https://github.com/shopgate/cart-integration-sdk/compare/2.6.10...2.6.11
[2.6.10]: https://github.com/shopgate/cart-integration-sdk/compare/2.6.9...2.6.10
[2.6.9]: https://github.com/shopgate/cart-integration-sdk/compare/2.6.8...2.6.9
[2.6.8]: https://github.com/shopgate/cart-integration-sdk/compare/2.6.7...2.6.8
[2.6.7]: https://github.com/shopgate/cart-integration-sdk/compare/2.6.6...2.6.7
[2.6.6]: https://github.com/shopgate/cart-integration-sdk/compare/2.6.5...2.6.6
[2.6.5]: https://github.com/shopgate/cart-integration-sdk/compare/2.6.4...2.6.5
[2.6.4]: https://github.com/shopgate/cart-integration-sdk/compare/2.6.3...2.6.4
[2.6.3]: https://github.com/shopgate/cart-integration-sdk/compare/2.6.2...2.6.3
[2.6.2]: https://github.com/shopgate/cart-integration-sdk/compare/2.6.1...2.6.2
[2.6.1]: https://github.com/shopgate/cart-integration-sdk/compare/2.6.0...2.6.1
[2.6.0]: https://github.com/shopgate/cart-integration-sdk/compare/2.5.7...2.6.0
[2.5.7]: https://github.com/shopgate/cart-integration-sdk/compare/2.5.6...2.5.7
[2.5.6]: https://github.com/shopgate/cart-integration-sdk/compare/2.5.5...2.5.6
[2.5.5]: https://github.com/shopgate/cart-integration-sdk/compare/2.5.4...2.5.5
[2.5.4]: https://github.com/shopgate/cart-integration-sdk/compare/2.5.3...2.5.4
[2.5.3]: https://github.com/shopgate/cart-integration-sdk/compare/2.5.2...2.5.3
[2.5.2]: https://github.com/shopgate/cart-integration-sdk/compare/2.5.1...2.5.2
[2.5.1]: https://github.com/shopgate/cart-integration-sdk/compare/2.5.0...2.5.1
[2.5.0]: https://github.com/shopgate/cart-integration-sdk/compare/2.4.16...2.5.0
[2.4.16]: https://github.com/shopgate/cart-integration-sdk/compare/2.4.15...2.4.16
[2.4.15]: https://github.com/shopgate/cart-integration-sdk/compare/2.4.14...2.4.15
[2.4.14]: https://github.com/shopgate/cart-integration-sdk/compare/2.4.13...2.4.14
[2.4.13]: https://github.com/shopgate/cart-integration-sdk/compare/2.4.12...2.4.13
[2.4.12]: https://github.com/shopgate/cart-integration-sdk/compare/2.4.11...2.4.12
[2.4.11]: https://github.com/shopgate/cart-integration-sdk/compare/2.4.10...2.4.11
[2.4.10]: https://github.com/shopgate/cart-integration-sdk/compare/2.4.9...2.4.10
[2.4.9]: https://github.com/shopgate/cart-integration-sdk/compare/2.4.8...2.4.9
[2.4.8]: https://github.com/shopgate/cart-integration-sdk/compare/2.4.7...2.4.8
[2.4.7]: https://github.com/shopgate/cart-integration-sdk/compare/2.4.6...2.4.7
[2.4.6]: https://github.com/shopgate/cart-integration-sdk/compare/2.4.5...2.4.6
[2.4.5]: https://github.com/shopgate/cart-integration-sdk/compare/2.4.4...2.4.5
[2.4.4]: https://github.com/shopgate/cart-integration-sdk/compare/2.4.3...2.4.4
[2.4.3]: https://github.com/shopgate/cart-integration-sdk/compare/2.4.2...2.4.3
[2.4.2]: https://github.com/shopgate/cart-integration-sdk/compare/2.4.1...2.4.2
[2.4.1]: https://github.com/shopgate/cart-integration-sdk/compare/2.4.0...2.4.1
[2.4.0]: https://github.com/shopgate/cart-integration-sdk/compare/2.3.10...2.4.0
[2.3.10]: https://github.com/shopgate/cart-integration-sdk/compare/2.3.9...2.3.10
[2.3.9]: https://github.com/shopgate/cart-integration-sdk/compare/2.3.8...2.3.9
[2.3.8]: https://github.com/shopgate/cart-integration-sdk/compare/2.3.7...2.3.8
[2.3.7]: https://github.com/shopgate/cart-integration-sdk/compare/2.3.6...2.3.7
[2.3.6]: https://github.com/shopgate/cart-integration-sdk/compare/2.3.5...2.3.6
[2.3.5]: https://github.com/shopgate/cart-integration-sdk/compare/2.3.4...2.3.5
[2.3.4]: https://github.com/shopgate/cart-integration-sdk/compare/2.3.3...2.3.4
[2.3.3]: https://github.com/shopgate/cart-integration-sdk/compare/2.3.2...2.3.3
[2.3.2]: https://github.com/shopgate/cart-integration-sdk/compare/2.3.1...2.3.2
[2.3.1]: https://github.com/shopgate/cart-integration-sdk/compare/2.3.0...2.3.1
[2.3.0]: https://github.com/shopgate/cart-integration-sdk/compare/2.2.2...2.3.0
[2.2.2]: https://github.com/shopgate/cart-integration-sdk/compare/2.2.1...2.2.2
[2.2.1]: https://github.com/shopgate/cart-integration-sdk/compare/2.2.0...2.2.1
[2.2.0]: https://github.com/shopgate/cart-integration-sdk/compare/2.1.26...2.2.0
[2.1.26]: https://github.com/shopgate/cart-integration-sdk/compare/2.1.25...2.1.26
[2.1.25]: https://github.com/shopgate/cart-integration-sdk/compare/2.1.24...2.1.25
[2.1.24]: https://github.com/shopgate/cart-integration-sdk/compare/2.1.23...2.1.24
[2.1.23]: https://github.com/shopgate/cart-integration-sdk/compare/2.1.22...2.1.23
[2.1.22]: https://github.com/shopgate/cart-integration-sdk/compare/2.1.21...2.1.22
[2.1.21]: https://github.com/shopgate/cart-integration-sdk/compare/2.1.20...2.1.21
[2.1.20]: https://github.com/shopgate/cart-integration-sdk/compare/2.1.19...2.1.20
[2.1.19]: https://github.com/shopgate/cart-integration-sdk/compare/2.1.18...2.1.19
[2.1.18]: https://github.com/shopgate/cart-integration-sdk/compare/2.1.17...2.1.18
[2.1.17]: https://github.com/shopgate/cart-integration-sdk/compare/2.1.16...2.1.17
[2.1.16]: https://github.com/shopgate/cart-integration-sdk/compare/2.1.15...2.1.16
[2.1.15]: https://github.com/shopgate/cart-integration-sdk/compare/2.1.14...2.1.15
[2.1.14]: https://github.com/shopgate/cart-integration-sdk/compare/2.1.13...2.1.14
[2.1.13]: https://github.com/shopgate/cart-integration-sdk/compare/2.1.12...2.1.13
[2.1.12]: https://github.com/shopgate/cart-integration-sdk/compare/2.1.11...2.1.12
[2.1.11]: https://github.com/shopgate/cart-integration-sdk/compare/2.1.10...2.1.11
[2.1.10]: https://github.com/shopgate/cart-integration-sdk/compare/2.1.9...2.1.10
[2.1.9]: https://github.com/shopgate/cart-integration-sdk/compare/2.1.8...2.1.9
[2.1.8]: https://github.com/shopgate/cart-integration-sdk/compare/2.1.7...2.1.8
[2.1.7]: https://github.com/shopgate/cart-integration-sdk/compare/2.1.6...2.1.7
[2.1.6]: https://github.com/shopgate/cart-integration-sdk/compare/2.1.5...2.1.6
[2.1.5]: https://github.com/shopgate/cart-integration-sdk/compare/2.1.4...2.1.5
