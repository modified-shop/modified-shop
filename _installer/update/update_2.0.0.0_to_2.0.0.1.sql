# -----------------------------------------------------------------------------------------
#  $Id: update_2.0.0.0_to_2.0.0.1.sql 3813 2012-10-29 11:54:40Z Tomcraft1980 $
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#GTB - 2016-04-07 - remove old admin access
ALTER TABLE admin_access DROP cache;
ALTER TABLE admin_access DROP define_language;
ALTER TABLE admin_access DROP module_paypal_install;
ALTER TABLE admin_access DROP popup_image;
ALTER TABLE admin_access DROP sofortueberweisung_install;
DELETE FROM admin_access WHERE customers_id = 'groups';
INSERT INTO `admin_access` (`customers_id`, `configuration`, `modules`, `countries`, `currencies`, `zones`, `geo_zones`, `tax_classes`, `tax_rates`, `accounting`, `backup`, `server_info`, `whos_online`, `languages`, `orders_status`, `shipping_status`, `module_export`, `customers`, `create_account`, `customers_status`, `customers_group`, `orders`, `campaigns`, `print_packingslip`, `print_order`, `popup_memo`, `coupon_admin`, `listproducts`, `listcategories`, `products_tags`, `gv_queue`, `gv_mail`, `gv_sent`, `gv_customers`, `validproducts`, `validcategories`, `mail`, `categories`, `new_attributes`, `products_attributes`, `manufacturers`, `reviews`, `specials`, `products_expected`, `stats_products_expected`, `stats_products_viewed`, `stats_products_purchased`, `stats_customers`, `stats_sales_report`, `stats_stock_warning`, `stats_campaigns`, `banner_manager`, `banner_statistics`, `module_newsletter`, `start`, `content_manager`, `content_preview`, `credits`, `orders_edit`, `csv_backend`, `products_vpe`, `cross_sell_groups`, `filemanager`, `econda`, `cleverreach`, `shop_offline`, `blz_update`, `removeoldpics`, `janolaw`, `haendlerbund`, `safeterms`, `check_update`, `easymarketing`, `it_recht_kanzlei`, `payone_config`, `payone_logs`, `protectedshops`, `parcel_carriers`, `supermailer`, `shopgate`, `newsfeed`, `logs`, `shipcloud`, `trustedshops`) VALUES ('groups', 8, 8, 7, 7, 7, 7, 7, 7, 2, 5, 5, 5, 7, 8, 8, 8, 2, 2, 2, 2, 2, 8, 2, 2, 2, 6, 6, 6, 3, 6, 6, 6, 6, 6, 6, 2, 3, 3, 3, 3, 3, 3, 3, 4, 4, 4, 4, 4, 4, 4, 5, 5, 5, 1, 5, 5, 1, 2, 5, 8, 8, 3, 9, 9, 8, 5, 5, 9, 9, 9, 1, 9, 9, 9, 9, 9, 5, 9, 9, 1, 5, 9, 9);

# Keep an empty line at the end of this file for the db_updater to work properly
