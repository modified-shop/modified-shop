# -----------------------------------------------------------------------------------------
#  $Id$
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#Tomcraft - 2024-03-19 - changed database_version
INSERT INTO `database_version` (`version`) VALUES ('MOD_3.1.0');

#GTB - 2024-03-19 - remove paypal sofort
DELETE FROM `configuration` WHERE `configuration_key` = 'MODULE_PAYMENT_PAYPALSOFORT_STATUS';
DELETE FROM `configuration` WHERE `configuration_key` = 'MODULE_PAYMENT_PAYPALSOFORT_ALLOWED';
DELETE FROM `configuration` WHERE `configuration_key` = 'MODULE_PAYMENT_PAYPALSOFORT_ZONE';
DELETE FROM `configuration` WHERE `configuration_key` = 'MODULE_PAYMENT_PAYPALSOFORT_SORT_ORDER';

# Keep an empty line at the end of this file for the db_updater to work properly