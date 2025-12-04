# -----------------------------------------------------------------------------------------
#  $Id$
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#GTB - 2025-10-15 - changed database_version
INSERT INTO `database_version` (`version`, `date_added`) VALUES ('MOD_3.2.0', NOW());

#GTB - 2025-10-15 - removed moneybookers / skrill
DELETE FROM configuration_group WHERE configuration_group_id = '31';

#GTB - 2025-10-16 - insert scheduled tasks for customers ip maintenance
INSERT INTO `scheduled_tasks` (`time_regularity`, `time_unit`, `status`, `edit`, `tasks`) VALUES (1, 'd', 0, 1, 'customers_ip_maintenance');

#GTB - 2025-12-02 - removed removeoldpics
ALTER TABLE `admin_access` DROP `removeoldpics`;

#GTB - 2025-12-02 - update countries
UPDATE `countries` SET `countries_name` = 'Kingdom of Eswatini' WHERE countries_iso_code_2 = 'SZ';
UPDATE `countries` SET `countries_name` = 'Republic of Cote d\'Ivoire' WHERE countries_iso_code_2 = 'CI';
UPDATE `countries` SET `countries_name` = 'Republic of the Sudan' WHERE countries_iso_code_2 = 'SD';
UPDATE `countries` SET `countries_name` = 'Republic of the Congo' WHERE countries_iso_code_2 = 'CG';

INSERT INTO `countries` VALUES (243,'Republic of South Sudan','SS','SSD',1,1,0,100);
INSERT INTO `countries` VALUES (244,'Democratic Republic of the Congo','CD','COD',1,1,0, 100);
INSERT INTO `countries` VALUES (245,'Bonaire','BQ','BES',1,1,0, 100);

DELETE FROM `countries` WHERE countries_iso_code_2 = 'ZR';

# Keep an empty line at the end of this file for the db_updater to work properly