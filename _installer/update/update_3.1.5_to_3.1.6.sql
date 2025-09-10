# -----------------------------------------------------------------------------------------
#  $Id$
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#Tomcraft - 2025-08-25 - changed database_version
INSERT INTO `database_version` (`version`, `date_added`) VALUES ('MOD_3.1.6', NOW());

#GTB - 2025-09-10 - unify amount format
ALTER TABLE coupon_gv_customer MODIFY amount DECIMAL(15,4) NOT NULL;
ALTER TABLE coupon_gv_queue MODIFY amount DECIMAL(15,4) NOT NULL;
ALTER TABLE coupons MODIFY coupon_amount DECIMAL(15,4) NOT NULL;
ALTER TABLE coupons MODIFY coupon_minimum_order DECIMAL(15,4) NOT NULL;
ALTER TABLE orders_recalculate MODIFY n_price DECIMAL(15,4) NOT NULL;
ALTER TABLE orders_recalculate MODIFY b_price DECIMAL(15,4) NOT NULL;
ALTER TABLE orders_recalculate MODIFY tax DECIMAL(15,4) NOT NULL;
ALTER TABLE orders_recalculate MODIFY tax_rate DECIMAL(7,4) NOT NULL;
ALTER TABLE products_graduated_prices MODIFY unitprice DECIMAL(15,4) NOT NULL;

# Keep an empty line at the end of this file for the db_updater to work properly