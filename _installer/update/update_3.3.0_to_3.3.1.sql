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

#GTB - 2026-07-27 - preserve country IDs and ensure missing non-EU tax-zone assignments
INSERT INTO `countries`
  (`countries_name`, `countries_iso_code_2`, `countries_iso_code_3`,
   `address_format_id`, `status`, `required_zones`, `sort_order`)
VALUES
  ('Republic of South Sudan', 'SS', 'SSD', 1, 1, 0, 100),
  ('Democratic Republic of the Congo', 'CD', 'COD', 1, 1, 0, 100),
  ('Bonaire', 'BQ', 'BES', 1, 1, 0, 100)
ON DUPLICATE KEY UPDATE
  `countries_name` = VALUES(`countries_name`),
  `countries_iso_code_3` = VALUES(`countries_iso_code_3`),
  `address_format_id` = VALUES(`address_format_id`),
  `status` = VALUES(`status`),
  `required_zones` = VALUES(`required_zones`),
  `sort_order` = VALUES(`sort_order`);

INSERT INTO `zones_to_geo_zones`
  (`zone_country_id`, `zone_id`, `geo_zone_id`,
   `last_modified`, `date_added`)
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

#GTB - 2026-07-20 - set index to speed up admin dashboard turnover query
ALTER TABLE `orders_total` ADD INDEX `idx_class` (`class`, `orders_id`);

# Keep an empty line at the end of this file for the db_updater to work properly
