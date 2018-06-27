# -----------------------------------------------------------------------------------------
#  $Id: update_2.0.4.0_to_2.0.4.1.sql 11316 2018-06-27 09:16:53Z GTB $
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#Tomcraft - 2018-06-11 - changed database_version
INSERT INTO `database_version` (`version`) VALUES ('MOD_2.0.4.1');

#GTB - 2018-06-12 - fix #1462
REPLACE INTO `customers_status` SELECT cs.`customers_status_id`, 2, cs.`customers_status_name`, cs.`customers_status_public`, cs.`customers_status_min_order`, cs.`customers_status_max_order`, cs.`customers_status_image`, cs.`customers_status_discount`, cs.`customers_status_ot_discount_flag`, cs.`customers_status_ot_discount`, cs.`customers_status_graduated_prices`, cs.`customers_status_show_price`, cs.`customers_status_show_price_tax`, cs.`customers_status_show_tax_total`, cs.`customers_status_add_tax_ot`, cs.`customers_status_payment_unallowed`, cs.`customers_status_shipping_unallowed`, cs.`customers_status_discount_attributes`, cs.`customers_fsk18`, cs.`customers_fsk18_display`, cs.`customers_status_write_reviews`, cs.`customers_status_read_reviews`, cs.`customers_status_reviews_status`, cs.`customers_status_specials` FROM `customers_status` cs WHERE cs.`customers_status_id` = '0' AND cs.`language_id` = '1';

# Keep an empty line at the end of this file for the db_updater to work properly