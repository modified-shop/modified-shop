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

INSERT INTO `countries` (`countries_name`, `countries_iso_code_2`, `countries_iso_code_3`, `address_format_id`, `status`, `required_zones`, `sort_order`) VALUES ('Republic of South Sudan','SS','SSD',1,1,0,100);
INSERT INTO `countries` (`countries_name`, `countries_iso_code_2`, `countries_iso_code_3`, `address_format_id`, `status`, `required_zones`, `sort_order`) VALUES ('Democratic Republic of the Congo','CD','COD',1,1,0, 100);
INSERT INTO `countries` (`countries_name`, `countries_iso_code_2`, `countries_iso_code_3`, `address_format_id`, `status`, `required_zones`, `sort_order`) VALUES ('Bonaire','BQ','BES',1,1,0, 100);

INSERT INTO `zones_to_geo_zones` (`association_id`, `zone_country_id`, `zone_id`, `geo_zone_id`, `last_modified`, `date_added`) VALUES ((SELECT `countries_id` FROM `countries` WHERE `countries_iso_code_2` = 'SS'), (SELECT `countries_id` FROM `countries` WHERE `countries_iso_code_2` = 'SS'), 0, 6, NULL, NOW());
INSERT INTO `zones_to_geo_zones` (`association_id`, `zone_country_id`, `zone_id`, `geo_zone_id`, `last_modified`, `date_added`) VALUES ((SELECT `countries_id` FROM `countries` WHERE `countries_iso_code_2` = 'CD'), (SELECT `countries_id` FROM `countries` WHERE `countries_iso_code_2` = 'CD'), 0, 6, NULL, NOW());
INSERT INTO `zones_to_geo_zones` (`association_id`, `zone_country_id`, `zone_id`, `geo_zone_id`, `last_modified`, `date_added`) VALUES ((SELECT `countries_id` FROM `countries` WHERE `countries_iso_code_2` = 'BQ'), (SELECT `countries_id` FROM `countries` WHERE `countries_iso_code_2` = 'BQ'), 0, 6, NULL, NOW());

DELETE ztgz
  FROM zones_to_geo_zones AS ztgz
  JOIN countries AS co 
       ON co.countries_id = ztgz.zone_country_id
          AND co.countries_iso_code_2 = 'ZR';
DELETE FROM `countries` WHERE countries_iso_code_2 = 'ZR';

# Keep an empty line at the end of this file for the db_updater to work properly