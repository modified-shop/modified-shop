# -----------------------------------------------------------------------------------------
#  $Id$
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#GTB - 2025-12-19 - database_version unique key
ALTER TABLE `database_version` ADD UNIQUE `idx_version` (`version`); 
REPLACE INTO `database_version` (`version`, `date_added`) VALUES ('MOD_3.2.0', NOW());

#Tomcraft - 2025-12-18 - changed database_version
INSERT INTO `database_version` (`version`, `date_added`) VALUES ('MOD_3.2.1', NOW());

#GTB - 2025-12-19 - fix errors due to problems with update API
DELETE FROM configuration_group WHERE configuration_group_id = '31';

REPLACE INTO `scheduled_tasks` (`time_regularity`, `time_unit`, `status`, `edit`, `tasks`) VALUES (1, 'd', 0, 1, 'customers_ip_maintenance');

ALTER TABLE `admin_access` DROP `removeoldpics`;

UPDATE `countries` SET `countries_name` = 'Kingdom of Eswatini' WHERE countries_iso_code_2 = 'SZ';
UPDATE `countries` SET `countries_name` = 'Republic of Cote d\'Ivoire' WHERE countries_iso_code_2 = 'CI';
UPDATE `countries` SET `countries_name` = 'Republic of the Sudan' WHERE countries_iso_code_2 = 'SD';
UPDATE `countries` SET `countries_name` = 'Republic of the Congo' WHERE countries_iso_code_2 = 'CG';

REPLACE INTO `countries` (`countries_id`, `countries_name`, `countries_iso_code_2`, `countries_iso_code_3`, `address_format_id`, `status`, `required_zones`, `sort_order`) VALUES ((SELECT `countries_id` FROM `countries` WHERE `countries_iso_code_2` = 'SS'), 'Republic of South Sudan','SS','SSD',1,1,0,100);
REPLACE INTO `countries` (`countries_id`, `countries_name`, `countries_iso_code_2`, `countries_iso_code_3`, `address_format_id`, `status`, `required_zones`, `sort_order`) VALUES ((SELECT `countries_id` FROM `countries` WHERE `countries_iso_code_2` = 'CD'), 'Democratic Republic of the Congo','CD','COD',1,1,0, 100);
REPLACE INTO `countries` (`countries_id`, `countries_name`, `countries_iso_code_2`, `countries_iso_code_3`, `address_format_id`, `status`, `required_zones`, `sort_order`) VALUES ((SELECT `countries_id` FROM `countries` WHERE `countries_iso_code_2` = 'BQ'), 'Bonaire','BQ','BES',1,1,0, 100);

REPLACE INTO `zones_to_geo_zones` (`association_id`, `zone_country_id`, `zone_id`, `geo_zone_id`, `last_modified`, `date_added`) VALUES ((SELECT `countries_id` FROM `countries` WHERE `countries_iso_code_2` = 'SS'), (SELECT `countries_id` FROM `countries` WHERE `countries_iso_code_2` = 'SS'), 0, 6, NULL, NOW());
REPLACE INTO `zones_to_geo_zones` (`association_id`, `zone_country_id`, `zone_id`, `geo_zone_id`, `last_modified`, `date_added`) VALUES ((SELECT `countries_id` FROM `countries` WHERE `countries_iso_code_2` = 'CD'), (SELECT `countries_id` FROM `countries` WHERE `countries_iso_code_2` = 'CD'), 0, 6, NULL, NOW());
REPLACE INTO `zones_to_geo_zones` (`association_id`, `zone_country_id`, `zone_id`, `geo_zone_id`, `last_modified`, `date_added`) VALUES ((SELECT `countries_id` FROM `countries` WHERE `countries_iso_code_2` = 'BQ'), (SELECT `countries_id` FROM `countries` WHERE `countries_iso_code_2` = 'BQ'), 0, 6, NULL, NOW());

DELETE ztgz
  FROM zones_to_geo_zones AS ztgz
  JOIN countries AS co 
       ON co.countries_id = ztgz.zone_country_id
          AND co.countries_iso_code_2 = 'ZR';
DELETE FROM `countries` WHERE countries_iso_code_2 = 'ZR';

# Keep an empty line at the end of this file for the db_updater to work properly