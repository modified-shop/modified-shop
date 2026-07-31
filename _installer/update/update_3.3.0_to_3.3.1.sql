# -----------------------------------------------------------------------------------------
#  $Id$
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#GTB - 2026-07-20 - changed database_version
INSERT INTO `database_version` (`version`, `date_added`) VALUES ('MOD_3.3.1', NOW());

#GTB - 2026-07-27 - preserve country IDs and settings and ensure missing non-EU tax-zone assignments
INSERT INTO `countries`
  (`countries_name`, `countries_iso_code_2`, `countries_iso_code_3`, `address_format_id`, `status`, `required_zones`, `sort_order`)
VALUES
  ('Republic of South Sudan', 'SS', 'SSD', 1, 1, 0, 100),
  ('Democratic Republic of the Congo', 'CD', 'COD', 1, 1, 0, 100),
  ('Bonaire', 'BQ', 'BES', 1, 1, 0, 100)
ON DUPLICATE KEY UPDATE
  `countries_name` = VALUES(`countries_name`),
  `countries_iso_code_3` = VALUES(`countries_iso_code_3`);

INSERT INTO `zones_to_geo_zones`
  (`zone_country_id`, `zone_id`, `geo_zone_id`, `last_modified`, `date_added`)
SELECT
  c.`countries_id`,
  0,
  g.`geo_zone_id`,
  NULL,
  NOW()
FROM `countries` AS c
JOIN `geo_zones` AS g
  ON g.`geo_zone_id` = 6
LEFT JOIN `zones_to_geo_zones` AS z
  ON z.`zone_country_id` = c.`countries_id`
 AND z.`zone_id` = 0
 AND z.`geo_zone_id` = g.`geo_zone_id`
WHERE c.`countries_iso_code_2` IN ('SS', 'CD', 'BQ')
  AND z.`association_id` IS NULL
ORDER BY c.`countries_id`;

#Tomcraft - 2026-07-27 - restore scheduled task defaults without replacing its primary key
INSERT INTO `scheduled_tasks`
  (`time_next`, `time_offset`, `time_regularity`, `time_unit`, `status`, `edit`, `tasks`)
VALUES
  (0, 0, 1, 'd', 0, 1, 'customers_ip_maintenance')
ON DUPLICATE KEY UPDATE
  `time_next` = VALUES(`time_next`),
  `time_offset` = VALUES(`time_offset`),
  `time_regularity` = VALUES(`time_regularity`),
  `time_unit` = VALUES(`time_unit`),
  `status` = VALUES(`status`),
  `edit` = VALUES(`edit`);

#Tomcraft - 2026-07-27 - ensure countries and missing non-EU tax-zone assignments from previous updates
INSERT INTO `countries`
  (`countries_name`, `countries_iso_code_2`, `countries_iso_code_3`, `address_format_id`, `status`, `required_zones`, `sort_order`)
VALUES
  ('Serbia', 'RS', 'SRB', 1, 1, 0, 100),
  ('Montenegro', 'ME', 'MNE', 1, 1, 0, 100),
  ('Kosovo', 'CS', 'SCG', 1, 1, 0, 100)
ON DUPLICATE KEY UPDATE
  `countries_name` = VALUES(`countries_name`),
  `countries_iso_code_3` = VALUES(`countries_iso_code_3`);

#Tomcraft - 2026-07-27 - ensure historical tax-zone assignments without fixed association IDs
INSERT INTO `zones_to_geo_zones`
  (`zone_country_id`, `zone_id`, `geo_zone_id`, `last_modified`, `date_added`)
SELECT
  c.`countries_id`,
  0,
  g.`geo_zone_id`,
  NULL,
  NOW()
FROM `countries` AS c
JOIN `geo_zones` AS g
  ON g.`geo_zone_id` = 6
LEFT JOIN `zones_to_geo_zones` AS z
  ON z.`zone_country_id` = c.`countries_id`
 AND z.`zone_id` = 0
 AND z.`geo_zone_id` = g.`geo_zone_id`
WHERE c.`countries_iso_code_2` IN ('RS', 'ME', 'CS')
  AND z.`association_id` IS NULL
ORDER BY c.`countries_id`;

#GTB - 2026-07-20 - set index to speed up admin dashboard turnover query
ALTER TABLE `orders_total` ADD INDEX `idx_class` (`class`, `orders_id`);

#GTB - 2026-07-23 - update iso codes for some countries
UPDATE `countries` SET `countries_iso_code_3` = 'AUS' WHERE countries_iso_code_2 = 'AU' AND countries_iso_code_3 = 'AUD';
UPDATE `countries` SET `countries_iso_code_3` = 'ROU' WHERE countries_iso_code_2 = 'RO' AND countries_iso_code_3 = 'ROM';
UPDATE `countries` SET `countries_name` = 'Timor-Leste', `countries_iso_code_2` = 'TL', `countries_iso_code_3` = 'TLS' WHERE countries_iso_code_2 = 'TP' AND countries_iso_code_3 = 'TMP';

#GTB - 2026-07-23 - migrate TP to TL in saved shipping module country lists (East Timor iso code change above)
UPDATE `configuration` SET `configuration_value` = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', `configuration_value`, ','), ',TP,', ',TL,')) WHERE `configuration_key` = 'MODULE_SHIPPING_DHL_COUNTRIES_10';
UPDATE `configuration` SET `configuration_value` = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', `configuration_value`, ','), ',TP,', ',TL,')) WHERE `configuration_key` = 'MODULE_SHIPPING_CHP_COUNTRIES_7';
UPDATE `configuration` SET `configuration_value` = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', `configuration_value`, ','), ',TP,', ',TL,')) WHERE `configuration_key` = 'MODULE_SHIPPING_AP_COUNTRIES_5';

#GTB - 2026-07-24 - update DPD tracking link
UPDATE `carriers` SET `carrier_tracking_link` = 'https://my.dpd.de/redirect.aspx?action=2&parcelno=$1&locale=$2' WHERE `carrier_tracking_link` = 'https://extranet.dpd.de/cgi-bin/delistrack?pknr=$1+&typ=1&lang=$2';

#GTB - 2026-07-31 - add checkout processing ownership
CREATE TABLE IF NOT EXISTS `checkout_processing` (
  `checkout_key` CHAR(64) NOT NULL,
  `customers_id` INT(11) NOT NULL,
  `owner_token` CHAR(64) NOT NULL,
  `status_token` CHAR(64) NOT NULL,
  `request_fingerprint` CHAR(64) NOT NULL,
  `orders_id` INT(11),
  `processing_status` VARCHAR(16) NOT NULL DEFAULT 'processing',
  `date_added` DATETIME NOT NULL,
  `last_modified` DATETIME NOT NULL,
  PRIMARY KEY (`checkout_key`),
  UNIQUE KEY `idx_orders_id` (`orders_id`),
  KEY `idx_customers_id` (`customers_id`),
  KEY `idx_processing_status` (`processing_status`)
);

# Keep an empty line at the end of this file for the db_updater to work properly
