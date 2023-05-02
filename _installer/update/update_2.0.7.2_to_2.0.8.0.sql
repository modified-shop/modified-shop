# -----------------------------------------------------------------------------------------
#  $Id$
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#GTB - 2022-07-10 - changed database_version
INSERT INTO `database_version` (`version`) VALUES ('MOD_2.0.8.0');

#GTB - 2022-07-10 - fix wrong function
UPDATE configuration SET set_function = 'xtc_cfg_select_content(\'REVIEWS_PURCHASED_INFOS\',' WHERE configuration_key = 'REVIEWS_PURCHASED_INFOS';

#GTB - 2022-07-10 - fix #2266 - fix NULL for zone_id
ALTER TABLE `zones_to_geo_zones` MODIFY `zone_id` INT(11) NOT NULL;

#GTB - 2022-07-10 - expand password field
ALTER TABLE `customers` MODIFY `customers_password` VARCHAR(255) NOT NULL;

#GTB - 2022-07-12 - extend manufacturers
ALTER TABLE `manufacturers` ADD `manufacturers_status` INT(1) NOT NULL AFTER `manufacturers_image`; 
ALTER TABLE `manufacturers` ADD `sort_order` INT(3) DEFAULT 0 NOT NULL AFTER `manufacturers_status`; 
ALTER TABLE `manufacturers` ADD `products_sorting` VARCHAR(64) NULL AFTER `sort_order`; 
ALTER TABLE `manufacturers` ADD `products_sorting2` VARCHAR(64) NOT NULL AFTER `products_sorting`; 
ALTER TABLE `manufacturers` ADD `listing_template` VARCHAR(64) NOT NULL DEFAULT '' AFTER `products_sorting2`; 
ALTER TABLE `manufacturers` ADD `categories_template` VARCHAR(64) AFTER `listing_template`; 
ALTER TABLE `manufacturers` ADD KEY `idx_manufacturers_status` (`manufacturers_status`);
ALTER TABLE `manufacturers` ADD KEY `idx_sort_order` (`sort_order`);

#GTB - 2022-07-13 - add image description
CREATE TABLE IF NOT EXISTS `products_images_description` (
  `image_id` INT(11) NOT NULL,
  `products_id` INT(11) NOT NULL,
  `image_title` VARCHAR(255) NOT NULL,
  `image_alt` VARCHAR(255) NOT NULL,
  `language_id` INT(11) NOT NULL,
  PRIMARY KEY (`image_id`, `language_id`),
  KEY idx_products_id (`products_id`)
);

#GTB - 2022-07-18 - add index for products_description
ALTER TABLE `products_description` ADD KEY `idx_products_heading_title` (`products_heading_title`);
ALTER TABLE `products_description` ADD KEY `idx_products_keywords` (`products_keywords`);

#GTB - 2022-07-18 - add index for categories_description
ALTER TABLE `categories_description` ADD KEY `idx_categories_heading_title` (`categories_heading_title`);

#GTB - 2022-07-18 - add index for manufacturers
ALTER TABLE `manufacturers` ADD KEY `idx_manufacturers_image` (`manufacturers_image`);
ALTER TABLE `manufacturers_info` ADD KEY `idx_manufacturers_title` (`manufacturers_title`);

#GTB - 2022-07-22 - add content_type for orders
ALTER TABLE `orders` ADD `content_type` VARCHAR(32) NOT NULL; 

#GTB - 2022-07-12 - extend manufacturers
ALTER TABLE `customers_login` ADD `date_added` DATETIME DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `customers_login` ADD `last_modified` DATETIME DEFAULT '0000-00-00 00:00:00';

#GTB - 2023-02-07 - scheduled tasks
CREATE TABLE IF NOT EXISTS `scheduled_tasks` (
  `tasks_id` INT(11) NOT NULL AUTO_INCREMENT,
  `time_next` INT(11) NOT NULL DEFAULT 0,
  `time_offset` INT(11) NOT NULL DEFAULT 0,
  `time_regularity` INT(5) NOT NULL,
  `time_unit` VARCHAR(1) NOT NULL DEFAULT 'h',
  `status` INT(1) NOT NULL,
  `edit` INT(1) NOT NULL DEFAULT 1,
  `tasks` VARCHAR(128) NOT NULL,
  PRIMARY KEY (`tasks_id`),
  UNIQUE KEY `idx_task` (`tasks`),
  KEY `idx_status` (`status`),
  KEY `idx_time_next` (`time_next`)
);

#GTB - 2023-02-07 - scheduled tasks log
CREATE TABLE IF NOT EXISTS `scheduled_tasks_log` (
  `logs_id` BIGINT(11) NOT NULL AUTO_INCREMENT,
  `tasks_id` INT(11) NOT NULL,
  `time_run` INT(11) NOT NULL,
  `time_taken` FLOAT NOT NULL,
  PRIMARY KEY (`logs_id`),
  KEY `idx_tasks_id` (`tasks_id`)
);

#GTB - 2023-02-07 - scheduled tasks
ALTER TABLE `admin_access` ADD `scheduled_tasks` INT(1) NOT NULL DEFAULT '0' AFTER `dhl`;
UPDATE `admin_access` SET `scheduled_tasks` = 1 WHERE `customers_id` = 1 LIMIT 1;
UPDATE `admin_access` SET `scheduled_tasks` = 5 WHERE `customers_id` = 'groups' LIMIT 1;

#GTB - 2023-02-08 - insert scheduled tasks
INSERT INTO `scheduled_tasks` (`time_regularity`, `time_unit`, `status`, `edit`, `tasks`) VALUES (1, 'm', 1, 1, 'status_specials');
INSERT INTO `scheduled_tasks` (`time_regularity`, `time_unit`, `status`, `edit`, `tasks`) VALUES (1, 'm', 1, 1, 'status_banners');
INSERT INTO `scheduled_tasks` (`time_regularity`, `time_unit`, `status`, `edit`, `tasks`) VALUES (1, 'w', 0, 1, 'db_maintenance');
INSERT INTO `scheduled_tasks` (`time_regularity`, `time_unit`, `status`, `edit`, `tasks`) VALUES (1, 'd', 0, 1, 'db_backup');
INSERT INTO `scheduled_tasks` (`time_regularity`, `time_unit`, `status`, `edit`, `tasks`) VALUES (1, 'd', 0, 1, 'logs_maintenance');

#GTB - 2023-02-14 - add sort order for contents
ALTER TABLE `content_manager_content` ADD `sort_order` INT(3) NOT NULL DEFAULT '0' AFTER `file_comment`;
ALTER TABLE `content_manager_content` ADD INDEX `idx_sort_order` (`sort_order`); 
ALTER TABLE `email_content` ADD `sort_order` INT(3) NOT NULL DEFAULT '0' AFTER `file_comment`;
ALTER TABLE `email_content` ADD INDEX `idx_sort_order` (`sort_order`); 
ALTER TABLE `products_content` ADD `sort_order` INT(3) NOT NULL DEFAULT '0' AFTER `file_comment`;
ALTER TABLE `products_content` ADD INDEX `idx_sort_order` (`sort_order`); 

#Tomcraft - 2023-03-11 - avalex
ALTER TABLE `admin_access` ADD `avalex` INT(1) NOT NULL DEFAULT '0' AFTER `scheduled_tasks`;
UPDATE `admin_access` SET `avalex` = 1 WHERE `customers_id` = 1 LIMIT 1;
UPDATE `admin_access` SET `avalex` = 9 WHERE `customers_id` = 'groups' LIMIT 1;

#GTB - 2023-04-17 - delete obsolete configuration
DELETE FROM `configuration` WHERE `configuration_key` = 'TRACKING_GOOGLEANALYTICS_GTAG';
DELETE FROM `configuration_group` WHERE `configuration_group_id` = 24;

#GTB - 2023-04-19 - set image manpulator
UPDATE `configuration` SET `configuration_value` = 'image_manipulator.php' WHERE `configuration_key` = 'IMAGE_MANIPULATOR';

#GTB - 2023-05-02 - add whos online filter
ALTER TABLE `whos_online` ADD `status` INT(11) NOT NULL;
ALTER TABLE `whos_online` ADD INDEX `idx_status` (`status`); 

# Keep an empty line at the end of this file for the db_updater to work properly