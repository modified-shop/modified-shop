# -----------------------------------------------------------------------------------------
#  $Id$
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#Tomcraft - 2018-06-11 - changed database_version
INSERT INTO `database_version` (`version`) VALUES ('MOD_2.0.4.1');

#GTB - 2018-06-11 - fix #1378
ALTER TABLE orders_products MODIFY products_weight DECIMAL(15,4) NOT NULL;
ALTER TABLE products MODIFY products_weight DECIMAL(15,4) NOT NULL;

#GTB - 2018-06-12 - fix #1462
REPLACE INTO `customers_status` SELECT cs.`customers_status_id`, 2, cs.`customers_status_name`, cs.`customers_status_public`, cs.`customers_status_min_order`, cs.`customers_status_max_order`, cs.`customers_status_image`, cs.`customers_status_discount`, cs.`customers_status_ot_discount_flag`, cs.`customers_status_ot_discount`, cs.`customers_status_graduated_prices`, cs.`customers_status_show_price`, cs.`customers_status_show_price_tax`, cs.`customers_status_show_tax_total`, cs.`customers_status_add_tax_ot`, cs.`customers_status_payment_unallowed`, cs.`customers_status_shipping_unallowed`, cs.`customers_status_discount_attributes`, cs.`customers_fsk18`, cs.`customers_fsk18_display`, cs.`customers_status_write_reviews`, cs.`customers_status_read_reviews`, cs.`customers_status_reviews_status`, cs.`customers_status_specials` FROM `customers_status` cs WHERE cs.`customers_status_id` = '0' AND cs.`language_id` = '1';


#GTB - 2018-06-19 - add newsletter update
ALTER TABLE admin_access ADD newsletter_recipients INT(1) NOT NULL DEFAULT '0' AFTER paypal_module;
UPDATE admin_access SET newsletter_recipients = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET newsletter_recipients = 5 WHERE customers_id = 'groups' LIMIT 1;

DROP TABLE IF EXISTS newsletter_recipients_history;
CREATE TABLE newsletter_recipients_history (
  customers_email_address VARCHAR(255) NOT NULL,
  customers_action VARCHAR(32) NOT NULL,
  ip_address varchar(50) DEFAULT NULL,
  date_added datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  KEY idx_customers_email_address (customers_email_address)
);

#GTB - 2018-06-27 - add sort order tags
ALTER TABLE `products_tags` ADD `sort_order` INT(11) NOT NULL DEFAULT '0' AFTER `values_id` ;

# Keep an empty line at the end of this file for the db_updater to work properly