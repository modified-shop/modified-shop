# -----------------------------------------------------------------------------------------
#  $Id$
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#Tomcraft - 2020-06-26 - changed database_version
INSERT INTO `database_version` (`version`) VALUES ('MOD_2.0.5.2');

#Tomcraft - 2020-06-26 - delete obsolete configuration
DELETE FROM `configuration` WHERE `configuration_key` = 'GOOGLE_CERTIFIED_SHOPS_MERCHANT_ACTIVE';
DELETE FROM `configuration` WHERE `configuration_key` = 'GOOGLE_SHOPPING_ID';
DELETE FROM `configuration` WHERE `configuration_key` = 'GOOGLE_TRUSTED_ID';

# Keep an empty line at the end of this file for the db_updater to work properly