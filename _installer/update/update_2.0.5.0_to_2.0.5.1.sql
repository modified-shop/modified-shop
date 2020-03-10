# -----------------------------------------------------------------------------------------
#  $Id$
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#Tomcraft - 2018-06-11 - changed database_version
INSERT INTO `database_version` (`version`) VALUES ('MOD_2.0.5.1');

#GTB - 2019-11-18 - Force Cookie Usage, see: https://trac.modified-shop.org/changeset/12419
UPDATE `configuration` SET `configuration_value` = 'True' WHERE `configuration_key` = 'SESSION_FORCE_COOKIE_USE';

#Tomcraft - 2020-03-10 - removed blz update again due to r12625, so the database structure update will not add the field again
ALTER TABLE `admin_access` DROP `blz_update`;

#Tomcraft - 2020-03-10 - removed start again due to r12626, so the database structure update will not add the field again
ALTER TABLE `admin_access` DROP `start`;

# Keep an empty line at the end of this file for the db_updater to work properly