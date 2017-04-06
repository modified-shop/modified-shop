# -----------------------------------------------------------------------------------------
#  $Id$
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#Tomcraft - 2017-03-08 - changed database_version
INSERT INTO `database_version` (`version`) VALUES ('MOD_2.0.2.2');

#Web28 - 2017-03-08 - add keys
ALTER TABLE `products`
 ADD KEY `idx_manufacturers_id` (`manufacturers_id`);
 
ALTER TABLE `products_tags_values`
 ADD KEY `idx_filter` (`filter`);

ALTER TABLE `products_tags_options`
 ADD KEY `idx_filter` (`filter`);

#Tomcraft - 2017-03-13 - Fix Online Dispute Resolution links on r9479 (Fix #795)
UPDATE content_manager SET content_text = REPLACE(content_text, '<p>The EU Commission provides on its website the following link to the ODR platform: http://ec.europa.eu/consumers/odr.</p>', '<p>The EU Commission provides on its website the following link to the ODR platform: <a href="http://ec.europa.eu/consumers/odr/" target="_blank">http://ec.europa.eu/consumers/odr/</a></p>');
UPDATE content_manager SET content_text = REPLACE(content_text, '<p>Die EU-Kommission stellt im Internet unter folgendem Link eine Plattform zur Online-Streitbeilegung bereit: http://ec.europa.eu/consumers/odr</p>', '<p>Die EU-Kommission stellt im Internet unter folgendem Link eine Plattform zur Online-Streitbeilegung bereit: <a href="http://ec.europa.eu/consumers/odr/" target="_blank">http://ec.europa.eu/consumers/odr/</a></p>');
UPDATE content_manager SET content_text = REPLACE(content_text, '<a href="http//ec.europa.eu/consumers/odr" target="_blank">http//ec.europa.eu/consumers/odr</a>', '<a href="http://ec.europa.eu/consumers/odr/" target="_blank">http://ec.europa.eu/consumers/odr/</a>');

UPDATE orders_status SET orders_status_name = 'Shipped' WHERE orders_status_name = 'Delivered';
UPDATE orders_status SET orders_status_name = 'Canceled' WHERE orders_status_name = 'Reversed';

# Keep an empty line at the end of this file for the db_updater to work properly