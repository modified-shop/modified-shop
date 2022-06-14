# -----------------------------------------------------------------------------------------
#  $Id$
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#GTB - 2022-06-14 - changed database_version
INSERT INTO `database_version` (`version`) VALUES ('MOD_2.0.7.1');

#GTB - 2022-06-14 - fix empty payment
UPDATE `orders` SET `payment_class` = 'no_payment' WHERE `payment_class` = '';
UPDATE `orders` SET `payment_method` = 'no_payment' WHERE `payment_method` = '';

# Keep an empty line at the end of this file for the db_updater to work properly