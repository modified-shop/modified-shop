# -----------------------------------------------------------------------------------------
#  $Id$
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#Tomcraft - 2016-10-17 - changed database_version
INSERT INTO `database_version` (`version`) VALUES ('MOD_2.0.2.0');

#GTB - 2016-11-28 - changed language_id
ALTER TABLE banners MODIFY languages_id INT(11) NOT NULL;
ALTER TABLE content_manager MODIFY languages_id INT(11) NOT NULL;
ALTER TABLE products_content MODIFY languages_id INT(11) NOT NULL;
ALTER TABLE products_tags_options MODIFY languages_id INT(11) NOT NULL;
ALTER TABLE categories_description MODIFY language_id INT(11) NOT NULL;
ALTER TABLE coupons_description MODIFY language_id INT(11) NOT NULL;
ALTER TABLE customers_status MODIFY language_id INT(11) NOT NULL;
ALTER TABLE manufacturers_info MODIFY languages_id INT(11) NOT NULL;
ALTER TABLE orders_status MODIFY language_id INT(11) NOT NULL;
ALTER TABLE products_description MODIFY language_id INT(11) NOT NULL;
ALTER TABLE products_options MODIFY language_id INT(11) NOT NULL;
ALTER TABLE products_options_values MODIFY language_id INT(11) NOT NULL;
ALTER TABLE products_vpe MODIFY language_id INT(11) NOT NULL;
ALTER TABLE products_xsell_grp_name MODIFY language_id INT(11) NOT NULL;
ALTER TABLE shipping_status MODIFY language_id INT(11) NOT NULL;

#GTB - 2016-11-28 - changed status
ALTER TABLE categories MODIFY categories_status INT(1) NOT NULL;
ALTER TABLE products MODIFY products_status INT(1) NOT NULL;

#GTB - 2016-12-14 - delete duplicate content_group
DELETE FROM content_manager
USING content_manager, content_manager as Dup
WHERE NOT content_manager.content_id = Dup.content_id
AND content_manager.content_id > Dup.content_id
AND content_manager.content_group = Dup.content_group
AND content_manager.languages_id = Dup.languages_id;

#GTB - 2016-12-16 - increase field length
ALTER TABLE address_book MODIFY entry_state VARCHAR(64);
ALTER TABLE orders MODIFY customers_state VARCHAR(64);
ALTER TABLE orders MODIFY delivery_state VARCHAR(64);
ALTER TABLE orders MODIFY billing_state VARCHAR(64);

#GTB - 2017-01-12 - increase field length
ALTER TABLE content_manager MODIFY content_file VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE products_content MODIFY content_name VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE products_content MODIFY content_file VARCHAR(255) NOT NULL;

#GTB - 2017-01-12 - increase session value to longtext
ALTER TABLE sessions MODIFY value longtext NOT NULL;

#GTB - 2017-01-19 - rename moneybookers
UPDATE configuration_group SET configuration_group_title = 'Skrill', configuration_group_description = 'Skrill System' WHERE configuration_group_id = '31';

# Keep an empty line at the end of this file for the db_updater to work properly