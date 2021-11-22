# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

## 2.9.40 - 2017-11-9
### Changed
- migrated Shopgate integration for modified eCommerce to GitHub

## 2.9.39
- export EAN and SKU for child products
- insert used coupon code in order total title
- fixed a type inconsistency in child product export

## 2.9.38
- restored compatibility with PHP 5.2.17
- uses Shopgate Library 2.9.65

## 2.9.37
- fixed issue with decoding the inter order infos
- fixed an issue with changing the order status in the admin area
- uses library 2.9.62
- fixed item export for modified versions below 1.06

## 2.9.36
- improved stock validation for products with attributes

## 2.9.35
- improved integration with version modified eCommerce 2.00

## 2.9.34
- fixed detection of Modified eCommerce version
- fixed customer group for guest orders
- uses library 2.9.58
- fixed item validation in checkCart and checkStock for products with options

## 2.9.33
- fixed prices of inputs in product export

## 2.9.32
- fixed stock export of child products

## 2.9.31
- fixed calculation of coupon values

## 2.9.30
- uses Shopgate Library 2.9.45
- added manufacturer model number to product export
- adjusted manufacturer uid's in product export to contain database index values
- fixed a bug in detection of the correct language for product export
- fixed negative sort_order positions in product export

## 2.9.29
- fixed detection of free shipping
- improved mapping of payment fees in the order import

## 2.9.28
- added export of article number (sku) to the xml item export

## 2.9.27
- uses Shopgate Library 2.9.36
- fixed reduction type fixed in tier prices
- checkCart will only return shipping methods if a delivery address is given

## 2.9.26
- fixed a bug in the real-time synchronization of shipping methods
- fixed tier prices
- fixed bug with amount based coupons

## 2.9.25
- fixed issue on initializing the module
- fixed tax export

## 2.9.24
- added configuration to define display names for payment methods on order import
- implemented voucher support
- restored compatibility with PHP 5.2

## 2.9.23
- implemented tier price support

## 2.9.22
- fixed categories in xml product export

## 2.9.21
- helper loading logic changed
- saving customer custom fields to DB
- implement mobile redirect for search and brand
- implemented product xml export

## 2.9.20
- saving custom fields to DB or printing in order page history

## 2.9.19
- uses Shopgate Library 2.9.24
- implemented category XML export
- implemented review XML export

## 2.9.18
- removed german changelog
- implemented cronjob function for transferring order cancellations back to Shopgate

## 2.9.17
- fixed a bug in the real-time synchronization of shipping methods

## 2.9.16
- product options won't be exported simultaneously as text field
- the shipping method is now displayed in the order detail view

## 2.9.15
- User_Groups are now returned by checkCart, getCustomer and getSettings

## 2.9.14
- version of the shopping system was not recognized correctly under certain circumstances
- implemented function check_cart

## 2.9.13
- implemented function check_stock
- fixed encoding issue on creating new user accounts

## 2.9.12
- products with of quantity of zero will be exported now
- fixed a bug in exporting product images
- added new plugin configuration setting to allow exporting certain product options as input fields

## 2.9.11
- fixed a bug in getting the valid shipping methods
- bug in generating in product images fixed

## 2.9.10
- fixed a bug in importing orders for very old versions of the shopping cart
- fixed a bug while initializing of the plugin

## 2.9.9
- Small changes provided by Modified eCommerce
- Compatibility changes for the new v## 2.00 of Modified eCommerce

## 2.9.8
- fixed bug in product image export

## 2.9.7
- bug in splitted product export fixed

## 2.9.6
- adapted link to the Shopgate wiki
- fixed a bug in exporting product images
- empty variation values are now exported as double dashes (--)

## 2.9.5
- uses Shopgate Library 2.9.10

## 2.9.4
- bug in setting the right shipping status fixed
- fixed a bug that prevented registration with an email address that has previously been added as guest user by Shopgate

## 2.9.3
- fixed a bug in accessing request parameters for mobile redirect
- uses Shopgate Library 2.9.6

## 2.9.2
- fixed a bug in getting the valid shipping methods(tax calculation)

## 2.9.1
- updated Shopgate menu links
- the default redirect cannot be enabled in the plugin's configuration page anymore
- fixed a bug in getting the valid shipping methods
- fixed a bug in the mobile redirect for the welcome page

## 2.9.0
- dynamic initialising of shipping module constants improved
- Shopgate wiki link will be red out of the config now
- bug in export shipping methods fixed
- uses Shopgate Library 2.9.4

## 2.8.1
- bug while reading textfield information from database fixed
- bug in parsing the tax classes fixed

## 2.8.0
- uses Shopgate Library 2.8.3

## 2.7.2
- bug in tax rules datastructure fixed

## 2.7.1
- added missing instantiation of an Smarty Objektes

## 2.7.0
- uses Shopgate Library 2.7.0
- bug finding out shop version fixed

## 2.6.9
- send order confirmation mail in modified lower than 1.05

## 2.6.8
- Bug in Shopgate Connect fixed

## 2.6.7
- Now textfields were considered while order import
- While senden the order confirmation mail the language is set correctly
- Shopgate Connect sets the second user-id now

## 2.6.6
- improved stock check logic
- Textfields in orders are supported now

## 2.6.5
- supports paypal_ipn payment module at orders import

## 2.6.4
- shopgate config variable set

## 2.6.3
- shipping price will now be exported as net

## 2.6.2
- Shipping methods can be selected

## 2.6.1
- request plugin configuration extended

## 2.6.0
- virtual category "new products" can now be exported
- virtual category "special products" can now be exported

## 2.5.5
- uses Shopgate Library 2.6.6
- VAT bug fixed in Shopgate order import

## 2.5.4
- uses Shopgate Library 2.5.6
- plugin ping function extended

## 2.5.3
- uses Shopgate Library 2.5.5
- request Shopgate plugin properties

## 2.5.2
- order confirmation mail can now be sent through the shop system

## 2.5.1
- bug fixed in plugin installation

## 2.5.0
- uses Shopgate Library 2.5.0

## 2.4.9
- plugin installation optimized

## 2.4.8
- installation problem fixedf (column_left)

## 2.4.7
- Bug fixed which disabled the Shopgate-Menue

## 2.4.6
- uses Shopgate Library 2.4.13
- Bug fixed which disabled the Shopgate-Menue

## 2.4.5
- uses Shopgate Library 2.4.12

## 2.4.4
- Shipping class and method are now set correctly in shopgate orders

## 2.4.3
- added head comment (license) into plugin files

## 2.4.2
- register customer implemented

## 2.4.1
- Fixed a Css problem in the Admin Backend Menü
- nutzt Shopgate Library 2.4.6

## 2.4.0
- uses Shopgate Library 2.4.0

## 2.3.3
- Output buffer will be deleted to prevent error which caused through linebreaks/spaces

## 2.3.2
- fixed special prices deactiviation issue on order import with special prices

## 2.3.1
- uses Shopgate Library 2.3.8
- fixed issue on editing English orders

## 2.3.0
- fixed issue in the import of orders with customer group discount

## 2.1.26
- the country and state-zone is now set correctly for guest customers while importing an order without Shopgate-Connect

## 2.1.25
- fixed issue in install script
- fixed issue with item csv export. Items with more than 10 options are ignored, since they would break the export file
- Only home page, product detail pages and category pages are always redirected to the mobile web site from now on. There's a new setting for specifying whether or not other pages should also be redirected.
- a problem has been solved, that happened to cause warnings while redirect on servers that has set its error level to strict
- uses Shopgate Library 2.3.0

## 2.1.24
- applied all plugin modifications, done by the modified eCommerce team
- it's now possible to reduce the category depth to a maximum value upon request, where products deeper will be assigned to the deepest exported parent
- a problem has been solved, that happened to cause warnings while csv exports and order imports on servers that has set its error level to strict
- uses Shopgate Library 2.1.29

## 2.1.23
- added support for SEO URLs to export of products and categories
- adapted CSS classes for backend

## 2.1.22
- it is now possible to choose a combination of the products description and the short description on the Shopgate settings page
- the additional address field on Shopgate orders is now appended to the address while importing orders
- the customers data while importing orders is now taken from the shop customer data, instead of the customer data, given by the addOrder request

## 2.1.21
- the add order request doesn't stop anymore with an error message, when optional database fields are missing

## 2.1.20
- the stock of the products attributes is now exported correctly

## 2.1.19
- the selected shipping method should now consulted correctly to be able to calculate taxes for the shipping costs

## 2.1.18
- fixed an issue where the shopgate payment module could not be installed properly

## 2.1.17
- the stock level options are now accessed on importing orders and adapted to the ordered products, attributes and specials. Products and specials are now deactivated according to the shoppingsystem
- support for the older products structure to avoid false order imports after updating to a newer shopgate plugin is already included
- preorder products are now exported as such
- method updateOrder() doesn't throw an exception anymore if payment is done after shipping and shipping was not blocked by Shopgate.
- the Shopgate configuration file can now be saved without any problems, even if false formatted GET parameters are appended on navigation to the Shopgate configuration page
- the orders status "Shipping blocked (Shopgate)" can now be recognized while installing the Shopgate payment module even if its name has been changed, as long as it is marked with the keyword "Shopgate" (without quoutes)
- fixed an issue with DreamRobot and shipping costs
- moved functionality for handling of global and language dependend configurations to Shopgate Library
- fixed a bug in saving of log files
- uses Shopgate Library 2.1.26

## 2.1.16
- revised configuration interface
- it's now possible to select multiple languages for the Mobile Redirect
- added global configuration settings
- uses Shopgate Library 2.1.23

## 2.1.15
- fixed the directory structure
- uses Shopgate Library 2.1.22

## 2.1.14
- fixed issue with mobile redirect on not configured languages

## 2.1.13
- updated directory structure to simplify the plugin installation
- uses Shopgate Library 2.1.21

## 2.1.12
- fixed an issue that caused categories to be in the exactly reversed sort order
- a setting has been added for shops that use the category sort order in an inverted way to display the categories
- the sort order of products can now also be inverted by setting in the extended shopgate settings
- fixed an issue with products discount allowed values where product prices was exportet wrong in some cases
- uses Shopgate Library 2.1.21
- configuration fields "mobile Website" / "shop is active" removed
- js header output in <head> HTML tag
- <link rel="alternate" ...> HTML tag output in <head>

## 2.1.11
- fixed an issue in order synchronization

## 2.1.10
- the default charset is no longer set while creating the orders_shopgate_order table, because there seems to be a problem with some providers, while using this functionality
- mysql selects using join are now called explicit for cases where the keyword "JOIN" can not be used alone
- discounts for customer groups are now exported in the way the shoppingsystem implements it. The discount limit is also included for the export
- support for multiple languages: for every language a different Shopgate shop can be configured now
- the shipping method for the orders import can now be selected on the shopgate-confuration page. The tax rate set for the shipping methods tax class will be used for calculating the shipping cost taxes on the orders detail page
- payment costs aren't added as article anymore
- duplicate product option value names are now automatically renamed while giving an index for each duplicate name
- fixed an issue with false price calculation for product attributes while exporting a currency using an exchange rate
- uses Shopgate Library 2.1.18

## 2.1.9
- orders that are marked as shipped at shopgate will now be updated correctly while executing the cronjob

## 2.1.8
- fixed issue in sorting products and categories with negative sort order indices

## 2.1.7
- fixed an incompatiblity issue with older MySQL versions
- uses Shopgate Library 2.1.12

## 2.1.6
- fixed error in export of item numbers for variations
- additional debug logging added
- uses Shopgate Library 2.1.8
- rename in Modified

## 2.1.5
- The comments for orders via Shopgate have been revised due to misconceptions in the past.
: Orders that are not blocked for shipping by Shopgate are ''not'' approved for shipping either. When an order is placed with a merchant's payment method the transaction must be reviewed before shipping.
- item number export for one dimension variants

## 2.1.4
- fixed issues in products export
- fixed error in payment module installation
- uses Shopgate Library 2.1.6

## 2.1.3
- fixed error in payment module installation
- improved error display in configuration interface
- fixed issue at push to afterbuy
- uses Shopgate Library 2.1.5

## 2.1.2
- fixed an error concerning Shopgate Library
- uses Shopgate Library 2.1.3

## 2.1.1
- fixed (multibyte) charset issues when converting html entities in category names
- uses Shopgate Library 2.1.1

## 2.1.0
- the installation routine of the payment module now adds a new send status called "Shipping blocked (Shopgate)"
- common bugfixes
- uses Shopgate Library 2.1.0

## 2.0.33
- Manual update of database tables is not neccessary anymore. This is now done automatically during installation of the Shopgate payment module.
- uses Shopgate Library 2.0.34
- fixed export of personal offer prices
- use short description for products if description is empty
- fixed PHP warning on reinstallation of the Shopgate payment module
- removed unused configuration settings from Shopgate payment module

## 2.0.32
- orders import now uses "encoding" setting for comments

## 2.0.31
- fixed (multibyte) charset issues when converting html entities in attributes

## 2.0.30
- uses Shopgate Library 2.0.31
- changed export of product variations
- fixed "use of undefined constant" on mobile redirect

## 2.0.29
- added changelog.txt
- uses Shopgate Library 2.0.27
- supports the "Redirect to tablets (yes/no)" setting
- supports remote cron jobs via Shopgate Plugin API
- remote cron job for synchronization of order status at Shopgate

[Unreleased]: https://github.com/shopgate/cart-integration-modified/compare/2.9.40...HEAD
[2.9.40]: https://github.com/shopgate/cart-integration-modified/compare/2.9.40...2.9.40
