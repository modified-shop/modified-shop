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

#GTB - 2026-08-17 - move orphaned guest account cleanup out of the session gc into its own scheduled task (#3087)
INSERT INTO `scheduled_tasks`
  (`time_next`, `time_offset`, `time_regularity`, `time_unit`, `status`, `edit`, `tasks`)
VALUES
  (0, 0, 1, 'd', 1, 0, 'guest_account_maintenance')
ON DUPLICATE KEY UPDATE
  `time_next` = VALUES(`time_next`),
  `time_offset` = VALUES(`time_offset`),
  `time_regularity` = VALUES(`time_regularity`),
  `time_unit` = VALUES(`time_unit`),
  `status` = VALUES(`status`),
  `edit` = VALUES(`edit`);

#GTB - 2026-08-17 - backfill orders.languages_id for orders placed before this column existed (added 2014-01-05, never backfilled), so order::getOrderData() can read it directly
UPDATE `orders` AS o
JOIN `languages` AS l ON l.`directory` = o.`language`
SET o.`languages_id` = l.`languages_id`
WHERE o.`languages_id` = 0;

#GTB - 2026-08-24 - add withdrawal form content page for every language, but only if no content page uses withdraw.php yet
INSERT INTO `content_manager`
  (`categories_id`, `parent_id`, `group_ids`, `languages_id`, `content_title`, `content_heading`, `content_text`, `sort_order`, `file_flag`, `content_file`, `content_status`, `content_group`, `content_delete`, `content_meta_title`, `content_meta_description`, `content_meta_keywords`, `content_meta_robots`, `content_active`, `content_group_index`, `date_added`, `last_modified`)
SELECT
  0,
  0,
  '',
  l.`languages_id`,
  IF(l.`directory` = 'german', 'Vertrag widerrufen', 'Withdraw contract'),
  IF(l.`directory` = 'german', 'Widerruf erklären', 'Declare withdrawal'),
  IF(l.`directory` = 'german', '<p>Unser Widerrufsformular bietet Ihnen eine einfache Möglichkeit, Ihren Vertrag innerhalb der gesetzlichen Widerrufsfrist zu widerrufen.</p><p>Nach Eingang Ihres Widerrufs erhalten Sie von uns eine Bestätigung über den Erhalt.</p>', '<p>Our withdrawal form gives you an easy way to withdraw from your contract within the statutory withdrawal period.</p><p>Once we have received your withdrawal, we will send you a confirmation of receipt.</p>'),
  0,
  1,
  'withdraw.php',
  0,
  g.`content_group`,
  0,
  '', '', '', '',
  '1',
  0,
  NOW(),
  NULL
FROM `languages` AS l
CROSS JOIN (SELECT COALESCE(MAX(`content_group`), 0) + 1 AS `content_group`,
                   COALESCE(SUM(`content_file` = 'withdraw.php'), 0) AS `already_installed`
              FROM `content_manager`) AS g
WHERE g.`already_installed` = 0;

#GTB - 2026-08-25 - removed worldpay junior
DELETE FROM `configuration` WHERE `configuration_key` IN (
  'MODULE_PAYMENT_WORLDPAY_JUNIOR_STATUS',
  'MODULE_PAYMENT_WORLDPAY_JUNIOR_ALLOWED',
  'MODULE_PAYMENT_WORLDPAY_JUNIOR_INSTALLATION_ID',
  'MODULE_PAYMENT_WORLDPAY_JUNIOR_CALLBACK_PASSWORD',
  'MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD',
  'MODULE_PAYMENT_WORLDPAY_JUNIOR_TRANSACTION_METHOD',
  'MODULE_PAYMENT_WORLDPAY_JUNIOR_TESTMODE',
  'MODULE_PAYMENT_WORLDPAY_JUNIOR_ZONE',
  'MODULE_PAYMENT_WORLDPAY_JUNIOR_PREPARE_ORDER_STATUS_ID',
  'MODULE_PAYMENT_WORLDPAY_JUNIOR_ORDER_STATUS_ID',
  'MODULE_PAYMENT_WORLDPAY_JUNIOR_SORT_ORDER'
);

#GTB - 2026-08-25 - remove leftovers of the older worldpay module, whose uninstall never deleted MD5KEY and USEMD5
DELETE FROM `configuration` WHERE `configuration_key` IN (
  'MODULE_PAYMENT_WORLDPAY_STATUS',
  'MODULE_PAYMENT_WORLDPAY_ID',
  'MODULE_PAYMENT_WORLDPAY_MODE',
  'MODULE_PAYMENT_WORLDPAY_ALLOWED',
  'MODULE_PAYMENT_WORLDPAY_USEMD5',
  'MODULE_PAYMENT_WORLDPAY_MD5KEY',
  'MODULE_PAYMENT_WORLDPAY_USEPREAUTH',
  'MODULE_PAYMENT_WORLDPAY_PREAUTH',
  'MODULE_PAYMENT_WORLDPAY_ZONE',
  'MODULE_PAYMENT_WORLDPAY_SORT_ORDER',
  'MODULE_PAYMENT_WORLDPAY_ORDER_STATUS_ID'
);

UPDATE `configuration`
   SET `configuration_value` = TRIM(BOTH ';' FROM REPLACE(REPLACE(CONCAT(';', `configuration_value`, ';'), ';worldpay_junior.php;', ';'), ';worldpay.php;', ';'))
 WHERE `configuration_key` = 'MODULE_PAYMENT_INSTALLED';

#GTB - 2026-08-27 - added orders_source to track where an order came from (shop, admin, api, third party)
ALTER TABLE `orders` ADD `orders_source` VARCHAR(32) NOT NULL DEFAULT '' AFTER `content_type`;
ALTER TABLE `orders` ADD INDEX `idx_orders_source` (`orders_source`);

# Keep an empty line at the end of this file for the db_updater to work properly
