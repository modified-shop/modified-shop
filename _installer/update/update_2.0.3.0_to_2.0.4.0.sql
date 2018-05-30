# -----------------------------------------------------------------------------------------
#  $Id$
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#Tomcraft - 2018-02-06 - changed database_version
INSERT INTO `database_version` (`version`) VALUES ('MOD_2.0.4.0');

#Tomcraft - 2018-02-06 - change Online Dispute Resolution (odr) links from http to https
UPDATE content_manager SET content_text = REPLACE(content_text, 'http://ec.europa.eu/consumers/odr', 'https://ec.europa.eu/consumers/odr');

#GTB - 2018-03-16 - new products attributes handling
ALTER TABLE products_options_values ADD products_options_values_sortorder INT(11) NOT NULL AFTER products_options_values_name;

#Tomcraft - 2018-03-26 - change project links to https
UPDATE content_manager SET content_text = REPLACE(content_text, '<a href="http://www.modified-shop.org" target="_blank">', '<a href="https://www.modified-shop.org" target="_blank">');

#Tomcraft - 2018-03-26 - added rel="nofollow noopener" to external links
UPDATE content_manager SET content_text = REPLACE(content_text, '<a href="https://ec.europa.eu/consumers/odr/" target="_blank">', '<a href="https://ec.europa.eu/consumers/odr/" rel="nofollow noopener" target="_blank">');
UPDATE content_manager SET content_text = REPLACE(content_text, '<a href="https://ec.europa.eu/consumers/odr/" target="_blank">', '<a href="https://ec.europa.eu/consumers/odr/" rel="nofollow noopener" target="_blank">');
UPDATE content_manager SET content_text = REPLACE(content_text, '<a href="https://www.modified-shop.org" target="_blank">', '<a href="https://www.modified-shop.org" rel="nofollow noopener" target="_blank">');

#GTB - 2018-05-25 - performance tweaks for admin
ALTER TABLE `orders`
ADD KEY `idx_payment_class` (`payment_class`);

ALTER TABLE `orders_status_history`
ADD KEY `idx_orders_id` (`orders_id`);

#GTB - 2018-05-30 - fix #1412
ALTER TABLE categories_description MODIFY categories_meta_title text NOT NULL;
ALTER TABLE categories_description MODIFY categories_meta_description text NOT NULL;
ALTER TABLE categories_description MODIFY categories_meta_keywords text NOT NULL;
ALTER TABLE content_manager MODIFY content_meta_title text NOT NULL;
ALTER TABLE content_manager MODIFY content_meta_description text NOT NULL;
ALTER TABLE content_manager MODIFY content_meta_keywords text NOT NULL;
ALTER TABLE manufacturers_info MODIFY manufacturers_meta_title text NOT NULL;
ALTER TABLE manufacturers_info MODIFY manufacturers_meta_description text NOT NULL;
ALTER TABLE manufacturers_info MODIFY manufacturers_meta_keywords text NOT NULL;

# Keep an empty line at the end of this file for the db_updater to work properly