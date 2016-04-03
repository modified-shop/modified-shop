# -----------------------------------------------------------------------------------------
#  $Id: modified.sql 2796 2012-04-27 16:56:01Z tonne1 $
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------
#  Third Party Contributions:
#  Customers status v3.x (c) 2002-2003 Elari elari@free.fr
#  Download area : www.unlockgsm.com/dload-osc/
#  CVS : http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/elari/?sortby=date#dirlist
#  BMC 2003 for the CC CVV Module
#  --------------------------------------------------------------
#  based on:
#  (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
#  (c) 2002-2003 osCommerce (oscommerce.sql,v 1.83); www.oscommerce.com
#  (c) 2003 nextcommerce (nextcommerce.sql,v 1.76 2003/08/25); www.nextcommerce.org
#  (c) 2006 xtCommerce (xtcommerce.sql,v 1.62 2004/06/06); www.xt-commerce.com
#
#  Released under the GNU General Public License
#
#  --------------------------------------------------------------
#  NOTE: * Please make any modifications to this file by hand!
#   * DO NOT use a mysqldump created file for new changes!
#   * Please take note of the table structure, and use this
#   structure as a standard for future modifications!
#   * Comments should be like these, full line comments.
#   (don`t use inline comments)
#  --------------------------------------------------------------


DROP TABLE IF EXISTS address_book;
CREATE TABLE address_book (
  address_book_id INT NOT NULL AUTO_INCREMENT,
  customers_id INT NOT NULL,
  entry_gender CHAR(1) NOT NULL,
  entry_company VARCHAR(64),
  entry_firstname VARCHAR(64) NOT NULL,
  entry_lastname VARCHAR(64) NOT NULL,
  entry_street_address VARCHAR(64) NOT NULL,
  entry_suburb VARCHAR(32),
  entry_postcode VARCHAR(10) NOT NULL,
  entry_city VARCHAR(64) NOT NULL,
  entry_state VARCHAR(32),
  entry_country_id INT DEFAULT 0 NOT NULL,
  entry_zone_id INT DEFAULT 0 NOT NULL,
  address_date_added DATETIME DEFAULT '0000-00-00 00:00:00',
  address_last_modified DATETIME DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (address_book_id),
  KEY idx_customers_id (customers_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS address_format;
CREATE TABLE address_format (
  address_format_id INT NOT NULL AUTO_INCREMENT,
  address_format VARCHAR(128) NOT NULL,
  address_summary VARCHAR(48) NOT NULL,
  PRIMARY KEY (address_format_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS admin_access;
CREATE TABLE admin_access (
  customers_id VARCHAR(32) NOT NULL DEFAULT 0,
  configuration INT(1) NOT NULL DEFAULT 0,
  modules INT(1) NOT NULL DEFAULT 0,
  countries INT(1) NOT NULL DEFAULT 0,
  currencies INT(1) NOT NULL DEFAULT 0,
  zones INT(1) NOT NULL DEFAULT 0,
  geo_zones INT(1) NOT NULL DEFAULT 0,
  tax_classes INT(1) NOT NULL DEFAULT 0,
  tax_rates INT(1) NOT NULL DEFAULT 0,
  accounting INT(1) NOT NULL DEFAULT 0,
  backup INT(1) NOT NULL DEFAULT 0,
  cache INT(1) NOT NULL DEFAULT 0,
  server_info INT(1) NOT NULL DEFAULT 0,
  whos_online INT(1) NOT NULL DEFAULT 0,
  languages INT(1) NOT NULL DEFAULT 0,
  define_language INT(1) NOT NULL DEFAULT 0,
  orders_status INT(1) NOT NULL DEFAULT 0,
  shipping_status INT(1) NOT NULL DEFAULT 0,
  module_export INT(1) NOT NULL DEFAULT 0,
  customers INT(1) NOT NULL DEFAULT 0,
  create_account INT(1) NOT NULL DEFAULT 0,
  customers_status INT(1) NOT NULL DEFAULT 0,
  customers_group INT(1) NOT NULL DEFAULT 0,
  orders INT(1) NOT NULL DEFAULT 0,
  campaigns INT(1) NOT NULL DEFAULT 0,
  print_packingslip INT(1) NOT NULL DEFAULT 0,
  print_order INT(1) NOT NULL DEFAULT 0,
  popup_memo INT(1) NOT NULL DEFAULT 0,
  coupon_admin INT(1) NOT NULL DEFAULT 0,
  listproducts INT(1) NOT NULL DEFAULT 0,
  listcategories INT(1) NOT NULL DEFAULT 0,
  products_tags INT(1) NOT NULL DEFAULT 0,
  gv_queue INT(1) NOT NULL DEFAULT 0,
  gv_mail INT(1) NOT NULL DEFAULT 0,
  gv_sent INT(1) NOT NULL DEFAULT 0,
  gv_customers INT(1) NOT NULL DEFAULT 0,
  validproducts INT(1) NOT NULL DEFAULT 0,
  validcategories INT(1) NOT NULL DEFAULT 0,
  mail INT(1) NOT NULL DEFAULT 0,
  categories INT(1) NOT NULL DEFAULT 0,
  new_attributes INT(1) NOT NULL DEFAULT 0,
  products_attributes INT(1) NOT NULL DEFAULT 0,
  manufacturers INT(1) NOT NULL DEFAULT 0,
  reviews INT(1) NOT NULL DEFAULT 0,
  specials INT(1) NOT NULL DEFAULT 0,
  products_expected INT(1) NOT NULL DEFAULT 0,
  stats_products_expected INT(1) NOT NULL DEFAULT 0,
  stats_products_viewed INT(1) NOT NULL DEFAULT 0,
  stats_products_purchased INT(1) NOT NULL DEFAULT 0,
  stats_customers INT(1) NOT NULL DEFAULT 0,
  stats_sales_report INT(1) NOT NULL DEFAULT 0,
  stats_stock_warning INT(1) NOT NULL DEFAULT 0,
  stats_campaigns INT(1) NOT NULL DEFAULT 0,
  banner_manager INT(1) NOT NULL DEFAULT 0,
  banner_statistics INT(1) NOT NULL DEFAULT 0,
  module_newsletter INT(1) NOT NULL DEFAULT 0,
  start INT(1) NOT NULL DEFAULT 0,
  content_manager INT(1) NOT NULL DEFAULT 0,
  content_preview INT(1) NOT NULL DEFAULT 0,
  credits INT(1) NOT NULL DEFAULT 0,
  orders_edit INT(1) NOT NULL DEFAULT 0,
  popup_image INT(1) NOT NULL DEFAULT 0,
  csv_backend INT(1) NOT NULL DEFAULT 0,
  products_vpe INT(1) NOT NULL DEFAULT 0,
  cross_sell_groups INT(1) NOT NULL DEFAULT 0,
  filemanager INT(1) NOT NULL DEFAULT 0,
  econda INT(1) NOT NULL DEFAULT 0,
  cleverreach INT(1) NOT NULL DEFAULT 0,
  sofortueberweisung_install INT(1) NOT NULL DEFAULT 0,
  shop_offline INT(1) NOT NULL DEFAULT 0,
  blz_update INT(1) NOT NULL DEFAULT 0,
  removeoldpics INT(1) NOT NULL DEFAULT 0,
  janolaw INT(1) NOT NULL DEFAULT 0,
  haendlerbund INT(1) NOT NULL DEFAULT 0,
  safeterms INT(1) NOT NULL DEFAULT 0,
  check_update INT(1) NOT NULL DEFAULT 0,
  easymarketing INT(1) NOT NULL DEFAULT 0,
  it_recht_kanzlei INT(1) NOT NULL DEFAULT 0,
  payone_config INT(1) NOT NULL DEFAULT 0,
  payone_logs INT(1) NOT NULL DEFAULT 0,
  protectedshops INT(1) NOT NULL DEFAULT 0,
  parcel_carriers INT(1) NOT NULL DEFAULT 0,
  supermailer INT(1) NOT NULL DEFAULT 0,
  shopgate INT(1) NOT NULL DEFAULT 0,
  newsfeed INT(1) NOT NULL DEFAULT 0,
  logs INT(1) NOT NULL DEFAULT 0,
  shipcloud INT(1) NOT NULL DEFAULT 0,
  trustedshops INT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (customers_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS banktransfer;
CREATE TABLE banktransfer (
  orders_id INT(11) NOT NULL DEFAULT 0,
  banktransfer_owner VARCHAR(64) DEFAULT NULL,
  banktransfer_number VARCHAR(24) DEFAULT NULL,
  banktransfer_bankname VARCHAR(255) DEFAULT NULL,
  banktransfer_blz VARCHAR(8) DEFAULT NULL,
  banktransfer_iban VARCHAR(34) DEFAULT NULL,
  banktransfer_bic VARCHAR(11) DEFAULT NULL,
  banktransfer_status INT(11) DEFAULT NULL,
  banktransfer_prz CHAR(2) DEFAULT NULL,
  banktransfer_fax CHAR(2) DEFAULT NULL,
  banktransfer_owner_email VARCHAR(96) DEFAULT NULL,
  KEY idx_orders_id (orders_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS banktransfer_blz;
CREATE TABLE IF NOT EXISTS banktransfer_blz (
  blz int(10) NOT NULL DEFAULT 0,
  bankname varchar(255) NOT NULL DEFAULT '',
  prz char(2) NOT NULL DEFAULT '',
  PRIMARY KEY (blz)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS banners;
CREATE TABLE banners (
  banners_id INT NOT NULL AUTO_INCREMENT,
  banners_title VARCHAR(64) NOT NULL,
  banners_url VARCHAR(255) NOT NULL,
  banners_image VARCHAR(64) NOT NULL,
  banners_group VARCHAR(10) NOT NULL,
  banners_html_text TEXT,
  languages_id INT(11) NOT NULL DEFAULT 0,
  expires_impressions INT(7) DEFAULT NULL,
  expires_date DATETIME DEFAULT NULL,
  date_scheduled DATETIME DEFAULT NULL,
  date_added DATETIME NOT NULL,
  date_status_change DATETIME DEFAULT NULL,
  status INT(1) DEFAULT 1 NOT NULL,
  PRIMARY KEY (banners_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS banners_history;
CREATE TABLE banners_history (
  banners_history_id INT NOT NULL AUTO_INCREMENT,
  banners_id INT NOT NULL,
  banners_shown INT(5) NOT NULL DEFAULT 0,
  banners_clicked INT(5) NOT NULL DEFAULT 0,
  banners_history_date DATETIME NOT NULL,
  PRIMARY KEY (banners_history_id),
  KEY idx_banners_id (banners_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS campaigns;
CREATE TABLE campaigns (
  campaigns_id INT(11) NOT NULL AUTO_INCREMENT,
  campaigns_name VARCHAR(32) NOT NULL DEFAULT '',
  campaigns_refID VARCHAR(64) NOT NULL,
  campaigns_leads INT(11) NOT NULL DEFAULT 0,
  date_added DATETIME DEFAULT NULL,
  last_modified DATETIME DEFAULT NULL,
  PRIMARY KEY (campaigns_id),
  KEY idx_campaigns_name (campaigns_name),
  UNIQUE idx_campaigns_refID (campaigns_refID)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS campaigns_ip;
CREATE TABLE campaigns_ip (
  user_ip VARCHAR(39) NOT NULL,
  time DATETIME NOT NULL,
  campaign VARCHAR(32) NOT NULL,
  KEY idx_campaign (campaign)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS carriers;
CREATE TABLE carriers (
  carrier_id INT(11) NOT NULL AUTO_INCREMENT,
  carrier_name VARCHAR(80) NOT NULL,
  carrier_tracking_link VARCHAR(512) NOT NULL,
  carrier_sort_order INT(11) NOT NULL,
  carrier_date_added DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  carrier_last_modified DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (carrier_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS categories;
CREATE TABLE categories (
  categories_id INT NOT NULL AUTO_INCREMENT,
  categories_image VARCHAR(64),
  parent_id INT DEFAULT 0 NOT NULL,
  categories_status TINYINT (1) UNSIGNED DEFAULT 1 NOT NULL,
  categories_template VARCHAR(64),
  group_permission_0 TINYINT(1) NOT NULL,
  group_permission_1 TINYINT(1) NOT NULL,
  group_permission_2 TINYINT(1) NOT NULL,
  group_permission_3 TINYINT(1) NOT NULL,
  group_permission_4 TINYINT(1) NOT NULL,
  listing_template VARCHAR(64) NOT NULL DEFAULT '',
  sort_order INT(3) DEFAULT 0 NOT NULL,
  products_sorting VARCHAR(64),
  products_sorting2 VARCHAR(64),
  date_added DATETIME,
  last_modified DATETIME,
  PRIMARY KEY (categories_id),
  KEY idx_categories_parent_id (parent_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS categories_description;
CREATE TABLE categories_description (
  categories_id INT(11) NOT NULL,
  language_id TINYINT DEFAULT 1 NOT NULL,
  categories_name VARCHAR(255) NOT NULL,
  categories_heading_title VARCHAR(255) NOT NULL,
  categories_description text NOT NULL,
  categories_meta_title VARCHAR(100) NOT NULL,
  categories_meta_description VARCHAR(255) NOT NULL,
  categories_meta_keywords VARCHAR(255) NOT NULL,
  PRIMARY KEY (categories_id, language_id),
  KEY idx_categories_name (categories_name)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS cm_file_flags;
CREATE TABLE cm_file_flags (
  file_flag INT(11) NOT NULL,
  file_flag_name VARCHAR(32) NOT NULL,
  PRIMARY KEY (file_flag)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS configuration;
CREATE TABLE configuration (
  configuration_id INT NOT NULL AUTO_INCREMENT,
  configuration_key VARCHAR(64) NOT NULL,
  configuration_value text NOT NULL,
  configuration_group_id INT NOT NULL,
  sort_order INT(5) NULL,
  last_modified DATETIME NULL,
  date_added DATETIME NOT NULL,
  use_function VARCHAR(255) NULL,
  set_function VARCHAR(255) NULL,
  PRIMARY KEY (configuration_id),
  KEY idx_configuration_group_id (configuration_group_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS configuration_group;
CREATE TABLE configuration_group (
  configuration_group_id INT NOT NULL AUTO_INCREMENT,
  configuration_group_title VARCHAR(64) NOT NULL,
  configuration_group_description VARCHAR(255) NOT NULL,
  sort_order INT(5) NULL,
  visible INT(1) DEFAULT 1 NULL,
  PRIMARY KEY (configuration_group_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS content_manager;
CREATE TABLE content_manager (
  content_id INT(11) NOT NULL AUTO_INCREMENT,
  categories_id INT(11) NOT NULL DEFAULT 0,
  parent_id INT(11) NOT NULL DEFAULT 0,
  group_ids TEXT,
  languages_id INT(11) NOT NULL DEFAULT 0,
  content_title TEXT NOT NULL,
  content_heading TEXT NOT NULL,
  content_text TEXT NOT NULL,
  sort_order INT(4) NOT NULL DEFAULT 0,
  file_flag INT(1) NOT NULL DEFAULT 0,
  content_file VARCHAR(64) NOT NULL DEFAULT '',
  content_status INT(1) NOT NULL DEFAULT 0,
  content_group INT(11) NOT NULL,
  content_delete INT(1) NOT NULL DEFAULT 1,
  content_meta_title TEXT,
  content_meta_description TEXT,
  content_meta_keywords TEXT,
  content_meta_robots VARCHAR(32) NOT NULL,
  content_active INT(1) NOT NULL DEFAULT '1',
  content_group_index int(4) NOT NULL DEFAULT '0',
  date_added DATETIME NOT NULL,
  last_modified DATETIME NULL,
  PRIMARY KEY (content_id),
  KEY idx_content_group (content_group)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS countries;
CREATE TABLE countries (
  countries_id INT NOT NULL AUTO_INCREMENT,
  countries_name VARCHAR(64) NOT NULL,
  countries_iso_code_2 CHAR(2) NOT NULL,
  countries_iso_code_3 CHAR(3) NOT NULL,
  address_format_id INT NOT NULL,
  status INT(1) DEFAULT 1 NULL,
  required_zones INT(1) DEFAULT '0',
  PRIMARY KEY (countries_id),
  KEY idx_countries_name (countries_name),
  UNIQUE idx_countries_iso_code_2 (countries_iso_code_2),
  UNIQUE idx_countries_iso_code_3 (countries_iso_code_3)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS coupon_email_track;
CREATE TABLE coupon_email_track (
  unique_id INT(11) NOT NULL AUTO_INCREMENT,
  coupon_id INT(11) NOT NULL DEFAULT 0,
  customer_id_sent INT(11) NOT NULL DEFAULT 0,
  sent_firstname VARCHAR(32) DEFAULT NULL,
  sent_lastname VARCHAR(32) DEFAULT NULL,
  emailed_to VARCHAR(32) DEFAULT NULL,
  date_sent DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (unique_id),
  UNIQUE idx_coupon_id (coupon_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS coupon_gv_customer;
CREATE TABLE coupon_gv_customer (
  customer_id INT(5) NOT NULL DEFAULT 0,
  amount DECIMAL(8,4) NOT NULL DEFAULT 0.0000,
  PRIMARY KEY (customer_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS coupon_gv_queue;
CREATE TABLE coupon_gv_queue (
  unique_id INT(5) NOT NULL AUTO_INCREMENT,
  customer_id INT(5) NOT NULL DEFAULT 0,
  order_id INT(5) NOT NULL DEFAULT 0,
  amount DECIMAL(8,4) NOT NULL DEFAULT '0.0000',
  date_created DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  ipaddr VARCHAR(39) NOT NULL DEFAULT '',
  release_flag CHAR(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (unique_id),
  KEY idx_customer_id (customer_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS coupon_redeem_track;
CREATE TABLE coupon_redeem_track (
  unique_id INT(11) NOT NULL AUTO_INCREMENT,
  coupon_id INT(11) NOT NULL DEFAULT 0,
  customer_id INT(11) NOT NULL DEFAULT 0,
  redeem_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  redeem_ip VARCHAR(39) NOT NULL DEFAULT '',
  order_id INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (unique_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS coupons;
CREATE TABLE coupons (
  coupon_id INT(11) NOT NULL AUTO_INCREMENT,
  coupon_type CHAR(1) NOT NULL DEFAULT 'F',
  coupon_code VARCHAR(32) NOT NULL DEFAULT '',
  coupon_amount DECIMAL(8,4) NOT NULL DEFAULT 0.0000,
  coupon_minimum_order DECIMAL(8,4) NOT NULL DEFAULT 0.0000,
  coupon_start_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  coupon_expire_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  uses_per_coupon INT(5) NOT NULL DEFAULT 1,
  uses_per_user INT(5) NOT NULL DEFAULT 0,
  restrict_to_products TEXT DEFAULT NULL,
  restrict_to_categories TEXT DEFAULT NULL,
  restrict_to_customers TEXT,
  coupon_active CHAR(1) NOT NULL DEFAULT 'Y',
  date_created DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  date_modified DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (coupon_id),
  UNIQUE idx_coupon_code (coupon_code)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS coupons_description;
CREATE TABLE coupons_description (
  coupon_id INT(11) NOT NULL DEFAULT 0,
  language_id TINYINT NOT NULL DEFAULT 1,
  coupon_name VARCHAR(32) NOT NULL DEFAULT '',
  coupon_description text,
  PRIMARY KEY (coupon_id, language_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS currencies;
CREATE TABLE currencies (
  currencies_id INT NOT NULL AUTO_INCREMENT,
  title VARCHAR(32) NOT NULL,
  code CHAR(3) NOT NULL,
  symbol_left VARCHAR(12),
  symbol_right VARCHAR(12),
  decimal_point CHAR(1),
  thousands_point CHAR(1),
  decimal_places CHAR(1),
  value FLOAT(13,8),
  last_updated DATETIME NULL,
  status INT(1) DEFAULT 1 NOT NULL,
  PRIMARY KEY (currencies_id),
  UNIQUE KEY idx_code (code)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS customers;
CREATE TABLE customers (
  customers_id INT NOT NULL AUTO_INCREMENT,
  customers_cid VARCHAR(32),
  customers_vat_id VARCHAR(20),
  customers_vat_id_status INT(2) DEFAULT 0 NOT NULL,
  customers_warning VARCHAR(32),
  customers_status INT(5) DEFAULT 1 NOT NULL,
  customers_gender CHAR(1) NOT NULL,
  customers_firstname VARCHAR(64) NOT NULL,
  customers_lastname VARCHAR(64) NOT NULL,
  customers_dob DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
  customers_email_address VARCHAR(96) NOT NULL,
  customers_default_address_id INT NOT NULL,
  customers_telephone VARCHAR(32) NOT NULL,
  customers_fax VARCHAR(32),
  customers_password VARCHAR(60) NOT NULL,
  customers_newsletter CHAR(1),
  customers_newsletter_mode CHAR(1) DEFAULT '0' NOT NULL,
  member_flag CHAR(1) DEFAULT '0' NOT NULL,
  delete_user CHAR(1) DEFAULT '1' NOT NULL,
  account_type INT(1) NOT NULL DEFAULT 0,
  password_request_key VARCHAR(32) NOT NULL,
  password_request_time DATETIME DEFAULT '0000-00-00 00:00:00',
  payment_unallowed VARCHAR(255) NOT NULL,
  shipping_unallowed VARCHAR(255) NOT NULL,
  refferers_id VARCHAR(32) DEFAULT '0' NOT NULL,
  customers_date_added DATETIME DEFAULT '0000-00-00 00:00:00',
  customers_last_modified DATETIME DEFAULT '0000-00-00 00:00:00',
  customers_login_tries INT(11) NOT NULL DEFAULT '0',
  customers_login_time DATETIME DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (customers_id),
  KEY idx_customers_email_address (customers_email_address)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS customers_basket;
CREATE TABLE customers_basket (
  customers_basket_id INT NOT NULL AUTO_INCREMENT,
  customers_id INT NOT NULL,
  products_id TINYTEXT NOT NULL,
  customers_basket_quantity INT(2) NOT NULL,
  final_price DECIMAL(15,4) NOT NULL,
  customers_basket_date_added DATETIME,
  PRIMARY KEY (customers_basket_id),
  KEY idx_customers_id (customers_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS customers_basket_attributes;
CREATE TABLE customers_basket_attributes (
  customers_basket_attributes_id INT NOT NULL AUTO_INCREMENT,
  customers_id INT NOT NULL,
  products_id TINYTEXT NOT NULL,
  products_options_id INT NOT NULL,
  products_options_value_id INT NOT NULL,
  PRIMARY KEY (customers_basket_attributes_id),
  KEY idx_customers_id (customers_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS customers_info;
CREATE TABLE customers_info (
  customers_info_id INT NOT NULL,
  customers_info_date_of_last_logon DATETIME,
  customers_info_number_of_logons INT(5),
  customers_info_date_account_created DATETIME,
  customers_info_date_account_last_modified DATETIME,
  global_product_notifications INT(1) DEFAULT 0,
  PRIMARY KEY (customers_info_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS customers_ip;
CREATE TABLE customers_ip (
  customers_ip_id INT(11) NOT NULL AUTO_INCREMENT,
  customers_id INT(11) NOT NULL DEFAULT 0,
  customers_ip VARCHAR(39) NOT NULL DEFAULT '',
  customers_ip_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  customers_host VARCHAR(255) NOT NULL DEFAULT '',
  customers_advertiser VARCHAR(30) DEFAULT NULL,
  customers_referer_url VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (customers_ip_id),
  KEY idx_customers_id (customers_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS customers_memo;
CREATE TABLE customers_memo (
  memo_id INT(11) NOT NULL AUTO_INCREMENT,
  customers_id INT(11) NOT NULL DEFAULT 0,
  memo_date DATE NOT NULL DEFAULT '0000-00-00',
  memo_title TEXT NOT NULL,
  memo_text TEXT NOT NULL,
  poster_id INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (memo_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS customers_status;
CREATE TABLE customers_status (
  customers_status_id INT(11) NOT NULL DEFAULT 0,
  language_id TINYINT NOT NULL DEFAULT 1,
  customers_status_name VARCHAR(32) NOT NULL DEFAULT '',
  customers_status_public INT(1) NOT NULL DEFAULT 1,
  customers_status_min_order INT(7) DEFAULT NULL,
  customers_status_max_order INT(7) DEFAULT NULL,
  customers_status_image VARCHAR(64) DEFAULT NULL,
  customers_status_discount DECIMAL(4,2) DEFAULT 0.00,
  customers_status_ot_discount_flag CHAR(1) NOT NULL DEFAULT '0',
  customers_status_ot_discount DECIMAL(4,2) DEFAULT 0.00,
  customers_status_graduated_prices VARCHAR(1) NOT NULL DEFAULT '0',
  customers_status_show_price INT(1) NOT NULL DEFAULT 1,
  customers_status_show_price_tax INT(1) NOT NULL DEFAULT 1,
  customers_status_show_tax_total int(7) DEFAULT '150',
  customers_status_add_tax_ot INT(1) NOT NULL DEFAULT 0,
  customers_status_payment_unallowed VARCHAR(255) NOT NULL,
  customers_status_shipping_unallowed VARCHAR(255) NOT NULL,
  customers_status_discount_attributes INT(1) NOT NULL DEFAULT 0,
  customers_fsk18 INT(1) NOT NULL DEFAULT 1,
  customers_fsk18_display INT(1) NOT NULL DEFAULT 1,
  customers_status_write_reviews INT(1) NOT NULL DEFAULT 1,
  customers_status_read_reviews INT(1) NOT NULL DEFAULT 1,
  customers_status_specials INT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (customers_status_id, language_id),
  UNIQUE idx_customers_status_name (customers_status_name, language_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS customers_status_history;
CREATE TABLE customers_status_history (
  customers_status_history_id INT(11) NOT NULL AUTO_INCREMENT,
  customers_id INT(11) NOT NULL DEFAULT 0,
  new_value INT(5) NOT NULL DEFAULT 0,
  old_value INT(5) DEFAULT NULL,
  date_added DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  customer_notified INT(1) DEFAULT 0,
  PRIMARY KEY (customers_status_history_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS database_version;
CREATE TABLE database_version (
  version VARCHAR(32) NOT NULL
) ENGINE=MyISAM;

DROP TABLE IF EXISTS geo_zones;
CREATE TABLE geo_zones (
  geo_zone_id INT NOT NULL AUTO_INCREMENT,
  geo_zone_name VARCHAR(32) NOT NULL,
  geo_zone_description VARCHAR(255) NOT NULL,
  geo_zone_info INT(1) DEFAULT 0,
  last_modified DATETIME NULL,
  date_added DATETIME NOT NULL,
  PRIMARY KEY (geo_zone_id),
  UNIQUE idx_geo_zone_name (geo_zone_name)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS languages;
CREATE TABLE languages (
  languages_id INT NOT NULL AUTO_INCREMENT,
  name VARCHAR(32) NOT NULL,
  code CHAR(5) NOT NULL,
  image VARCHAR(64),
  directory VARCHAR(32),
  sort_order INT(3),
  language_charset text NOT NULL,
  status INT(1) NOT NULL DEFAULT 1,
  status_admin INT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (languages_id),
  UNIQUE idx_code (code)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS manufacturers;
CREATE TABLE manufacturers (
  manufacturers_id INT NOT NULL AUTO_INCREMENT,
  manufacturers_name VARCHAR(64) NOT NULL,
  manufacturers_image VARCHAR(64),
  date_added DATETIME NULL,
  last_modified DATETIME NULL,
  PRIMARY KEY (manufacturers_id),
  KEY idx_manufacturers_name (manufacturers_name)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS manufacturers_info;
CREATE TABLE manufacturers_info (
  manufacturers_id INT NOT NULL,
  languages_id INT NOT NULL,
  manufacturers_description text,
  manufacturers_meta_title VARCHAR(100) NOT NULL,
  manufacturers_meta_description VARCHAR(255) NOT NULL,
  manufacturers_meta_keywords VARCHAR(255) NOT NULL,
  manufacturers_url VARCHAR(255) NOT NULL,
  url_clicked INT(5) NOT NULL DEFAULT 0,
  date_last_click DATETIME NULL,
  PRIMARY KEY (manufacturers_id, languages_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS module_backup;
CREATE TABLE module_backup (
  configuration_id int(11) NOT NULL AUTO_INCREMENT,
  configuration_key varchar(64) NOT NULL,
  configuration_value text NOT NULL,
  last_modified datetime DEFAULT NULL,
  PRIMARY KEY (configuration_id),
  UNIQUE idx_configuration_key (configuration_key)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS newsfeed;
CREATE TABLE newsfeed (
  news_id INT( 11 ) NOT NULL AUTO_INCREMENT,
  news_title VARCHAR( 128 ) NULL,
  news_text TEXT NULL,
  news_link VARCHAR( 128 ) NULL,
  news_date INT( 11 ) NULL,
  PRIMARY KEY (news_id),
  UNIQUE idx_news_link (news_link)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS module_newsletter;
CREATE TABLE module_newsletter (
  newsletter_id INT(11) NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  bc TEXT NOT NULL,
  cc TEXT NOT NULL,
  date DATETIME DEFAULT NULL,
  status INT(1) NOT NULL DEFAULT 0,
  body TEXT NOT NULL,
  PRIMARY KEY (newsletter_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS newsletter_recipients;
CREATE TABLE newsletter_recipients (
  mail_id INT(11) NOT NULL AUTO_INCREMENT,
  customers_email_address VARCHAR(96) NOT NULL DEFAULT '',
  customers_id INT(11) NOT NULL DEFAULT 0,
  customers_status INT(5) NOT NULL DEFAULT 0,
  customers_firstname VARCHAR(64) NOT NULL DEFAULT '',
  customers_lastname VARCHAR(64) NOT NULL DEFAULT '',
  mail_status INT(1) NOT NULL DEFAULT 0,
  mail_key VARCHAR(32) NOT NULL DEFAULT '',
  date_added DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  ip_date_added varchar(32) DEFAULT NULL,
  date_confirmed datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  ip_date_confirmed varchar(32) DEFAULT NULL,
  PRIMARY KEY (mail_id),
  KEY idx_mail_key (mail_key),
  UNIQUE idx_customers_email_address (customers_email_address)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS newsletters;
CREATE TABLE newsletters (
  newsletters_id INT NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  content text NOT NULL,
  module VARCHAR(255) NOT NULL,
  date_added DATETIME NOT NULL,
  date_sent DATETIME,
  status INT(1),
  locked INT(1) DEFAULT 0,
  PRIMARY KEY (newsletters_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS newsletters_history;
CREATE TABLE newsletters_history (
  news_hist_id INT(11) NOT NULL DEFAULT 0,
  news_hist_cs INT(11) NOT NULL DEFAULT 0,
  news_hist_cs_date_sent date DEFAULT NULL,
  PRIMARY KEY (news_hist_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS orders;
CREATE TABLE orders (
  orders_id INT NOT NULL AUTO_INCREMENT,
  customers_id INT NOT NULL,
  customers_cid VARCHAR(32),
  customers_vat_id VARCHAR(20),
  customers_status INT(11),
  customers_status_name VARCHAR(32) NOT NULL,
  customers_status_image VARCHAR(64),
  customers_status_discount DECIMAL(4,2),
  customers_name VARCHAR(64) NOT NULL,
  customers_firstname VARCHAR(64) NOT NULL,
  customers_lastname VARCHAR(64) NOT NULL,
  customers_gender CHAR(1) NOT NULL,
  customers_company VARCHAR(64),
  customers_street_address VARCHAR(64) NOT NULL,
  customers_suburb VARCHAR(32),
  customers_city VARCHAR(64) NOT NULL,
  customers_postcode VARCHAR(10) NOT NULL,
  customers_state VARCHAR(32),
  customers_country VARCHAR(64) NOT NULL,
  customers_telephone VARCHAR(32) NOT NULL,
  customers_email_address VARCHAR(96) NOT NULL,
  customers_address_format_id INT(5) NOT NULL,
  customers_country_iso_code_2 varchar(2) NOT NULL,
  delivery_name VARCHAR(64) NOT NULL,
  delivery_firstname VARCHAR(64) NOT NULL,
  delivery_lastname VARCHAR(64) NOT NULL,
  delivery_gender CHAR(1) NOT NULL,
  delivery_company VARCHAR(64),
  delivery_street_address VARCHAR(64) NOT NULL,
  delivery_suburb VARCHAR(32),
  delivery_city VARCHAR(64) NOT NULL,
  delivery_postcode VARCHAR(10) NOT NULL,
  delivery_state VARCHAR(32),
  delivery_country VARCHAR(64) NOT NULL,
  delivery_country_iso_code_2 CHAR(2) NOT NULL,
  delivery_address_format_id INT(5) NOT NULL,
  billing_name VARCHAR(64) NOT NULL,
  billing_firstname VARCHAR(64) NOT NULL,
  billing_lastname VARCHAR(64) NOT NULL,
  billing_gender CHAR(1) NOT NULL,
  billing_company VARCHAR(64),
  billing_street_address VARCHAR(64) NOT NULL,
  billing_suburb VARCHAR(32),
  billing_city VARCHAR(64) NOT NULL,
  billing_postcode VARCHAR(10) NOT NULL,
  billing_state VARCHAR(32),
  billing_country VARCHAR(64) NOT NULL,
  billing_country_iso_code_2 CHAR(2) NOT NULL,
  billing_address_format_id INT(5) NOT NULL,
  payment_method VARCHAR(128) NOT NULL,
  comments text,
  last_modified DATETIME,
  date_purchased DATETIME,
  orders_status INT(5) NOT NULL,
  orders_date_finished DATETIME,
  currency CHAR(3),
  currency_value DECIMAL(14,6),
  account_type INT(1) DEFAULT 0 NOT NULL,
  payment_class VARCHAR(64) NOT NULL,
  shipping_method VARCHAR(128) NOT NULL,
  shipping_class VARCHAR(32) NOT NULL,
  customers_ip VARCHAR(39) NOT NULL,
  language VARCHAR(32) NOT NULL,
  languages_id int(11) NOT NULL,
  afterbuy_success INT(1) DEFAULT 0 NOT NULL,
  afterbuy_id INT(32) DEFAULT 0 NOT NULL,
  refferers_id VARCHAR(32) NOT NULL,
  conversion_type INT(1) DEFAULT 0 NOT NULL,
  orders_ident_key VARCHAR(128),
  PRIMARY KEY (orders_id),
  KEY idx_customers_id (customers_id),
  KEY idx_orders_status (orders_status)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS orders_products;
CREATE TABLE orders_products (
  orders_products_id INT NOT NULL AUTO_INCREMENT,
  orders_id INT NOT NULL,
  products_id INT NOT NULL,
  products_model VARCHAR(64),
  products_ean VARCHAR(128),
  products_name VARCHAR(255) NOT NULL,
  products_price DECIMAL(15,4) NOT NULL,
  products_price_origin DECIMAL(15,4) NOT NULL,
  products_discount_made DECIMAL(4,2) DEFAULT NULL,
  products_shipping_time VARCHAR(255) DEFAULT NULL,
  final_price DECIMAL(15,4) NOT NULL,
  products_tax DECIMAL(7,4) NOT NULL,
  products_quantity INT(2) NOT NULL,
  allow_tax INT(1) NOT NULL,
  products_order_description text,
  products_weight DECIMAL(6,3) NOT NULL,
  PRIMARY KEY (orders_products_id),
  KEY idx_orders_id (orders_id),
  KEY idx_products_id (products_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS orders_products_attributes;
CREATE TABLE orders_products_attributes (
  orders_products_attributes_id INT NOT NULL AUTO_INCREMENT,
  orders_id INT NOT NULL,
  orders_products_id INT NOT NULL,
  products_options VARCHAR(255) NOT NULL,
  products_options_values VARCHAR(255) NOT NULL,
  attributes_model VARCHAR(64),
  attributes_ean VARCHAR(128),
  options_values_price DECIMAL(15,4) NOT NULL,
  price_prefix CHAR(1) NOT NULL,
  orders_products_options_id INT(11) NOT NULL,
  orders_products_options_values_id INT(11) NOT NULL,  
  options_values_weight DECIMAL(15,4) NOT NULL,
  weight_prefix CHAR(1) NOT NULL,
  PRIMARY KEY (orders_products_attributes_id),
  KEY idx_orders_id (orders_id),
  KEY idx_orders_products_id (orders_products_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS orders_products_download;
CREATE TABLE orders_products_download (
  orders_products_download_id INT NOT NULL AUTO_INCREMENT,
  orders_id INT NOT NULL DEFAULT 0,
  orders_products_id INT NOT NULL DEFAULT 0,
  orders_products_filename VARCHAR(255) NOT NULL DEFAULT '',
  download_maxdays INT(2) NOT NULL DEFAULT 0,
  download_count INT(2) NOT NULL DEFAULT 0,
  download_key VARCHAR(32) NOT NULL DEFAULT '',
  PRIMARY KEY (orders_products_download_id),
  KEY idx_orders_id (orders_id),
  KEY idx_orders_products_id (orders_products_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS orders_recalculate;
CREATE TABLE orders_recalculate (
  orders_recalculate_id INT(11) NOT NULL AUTO_INCREMENT,
  orders_id INT(11) NOT NULL DEFAULT 0,
  n_price DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
  b_price DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
  tax DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
  tax_rate DECIMAL(7,4) NOT NULL DEFAULT '0.0000',
  class VARCHAR(32) NOT NULL DEFAULT '',
  PRIMARY KEY (orders_recalculate_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS orders_status;
CREATE TABLE orders_status (
  orders_status_id INT DEFAULT 0 NOT NULL,
  language_id TINYINT DEFAULT 1 NOT NULL,
  orders_status_name VARCHAR(64) NOT NULL,
  sort_order INT(11) DEFAULT 0 NOT NULL,
  PRIMARY KEY (orders_status_id, language_id),
  KEY idx_orders_status_name (orders_status_name)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS orders_status_history;
CREATE TABLE orders_status_history (
  orders_status_history_id INT NOT NULL AUTO_INCREMENT,
  orders_id INT NOT NULL,
  orders_status_id INT(5) NOT NULL,
  date_added DATETIME NOT NULL,
  customer_notified INT(1) DEFAULT 0,
  comments text,
  comments_sent INT(1) DEFAULT 0,
  PRIMARY KEY (orders_status_history_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS orders_total;
CREATE TABLE orders_total (
  orders_total_id INT unsigned NOT NULL AUTO_INCREMENT,
  orders_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  text VARCHAR(255) NOT NULL,
  value DECIMAL(15,4) NOT NULL,
  class VARCHAR(32) NOT NULL,
  sort_order INT NOT NULL,
  PRIMARY KEY (orders_total_id),
  KEY idx_orders_id (orders_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS orders_tracking;
CREATE TABLE orders_tracking (
  tracking_id INT(11) NOT NULL AUTO_INCREMENT,
  orders_id INT(11) NOT NULL,
  carrier_id INT(11) NOT NULL,
  parcel_id VARCHAR(80) NOT NULL,
  PRIMARY KEY (tracking_id),
  KEY idx_orders_id (orders_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS payment_moneybookers;
CREATE TABLE payment_moneybookers (
  mb_TRID VARCHAR(255) NOT NULL DEFAULT '',
  mb_ERRNO SMALLINT(3) unsigned NOT NULL DEFAULT 0,
  mb_ERRTXT VARCHAR(255) NOT NULL DEFAULT '',
  mb_DATE DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  mb_MBTID BIGINT(18) unsigned NOT NULL DEFAULT 0,
  mb_STATUS TINYINT(1) NOT NULL DEFAULT 0,
  mb_ORDERID INT(11) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (mb_TRID)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS products;
CREATE TABLE products (
  products_id INT NOT NULL AUTO_INCREMENT,
  products_ean VARCHAR(128),
  products_quantity INT(4) NOT NULL,
  products_shippingtime INT(4) NOT NULL,
  products_model VARCHAR(64),
  group_permission_0 TINYINT(1) NOT NULL,
  group_permission_1 TINYINT(1) NOT NULL,
  group_permission_2 TINYINT(1) NOT NULL,
  group_permission_3 TINYINT(1) NOT NULL,
  group_permission_4 TINYINT(1) NOT NULL,
  products_sort INT(4) NOT NULL DEFAULT 0,
  products_image VARCHAR(254) NOT NULL,
  products_price DECIMAL(15,4) NOT NULL,
  products_discount_allowed DECIMAL(4,2) DEFAULT 0.00 NOT NULL,
  products_date_added DATETIME NOT NULL,
  products_last_modified DATETIME,
  products_date_available DATETIME,
  products_weight DECIMAL(6,3) NOT NULL,
  products_status TINYINT(1) NOT NULL,
  products_tax_class_id INT NOT NULL,
  product_template VARCHAR(64),
  options_template VARCHAR(64),
  manufacturers_id INT NULL,
  products_manufacturers_model varchar(64),
  products_ordered INT NOT NULL DEFAULT 0,
  products_fsk18 INT(1) NOT NULL DEFAULT 0,
  products_vpe INT(11) NOT NULL,
  products_vpe_status INT(1) NOT NULL DEFAULT 0,
  products_vpe_value DECIMAL(15,4) NOT NULL,
  products_startpage INT(1) NOT NULL DEFAULT 0,
  products_startpage_sort INT(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (products_id),
  KEY idx_products_date_added (products_date_added),
  KEY idx_products_model (products_model),
  KEY idx_products_status (products_status)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS products_attributes;
CREATE TABLE products_attributes (
  products_attributes_id INT NOT NULL AUTO_INCREMENT,
  products_id INT NOT NULL,
  options_id INT NOT NULL,
  options_values_id INT NOT NULL,
  options_values_price DECIMAL(15,4) NOT NULL,
  price_prefix CHAR(1) NOT NULL,
  attributes_model VARCHAR(64) NULL,
  attributes_stock INT(4) NULL,
  options_values_weight DECIMAL(15,4) NOT NULL,
  weight_prefix CHAR(1) NOT NULL,
  sortorder INT(11) NULL,
  attributes_ean VARCHAR(64) NULL DEFAULT NULL,
  attributes_vpe_id int(11) NOT NULL,
  attributes_vpe_value decimal(15,4) NOT NULL,
  PRIMARY KEY (products_attributes_id),
  KEY idx_products_id (products_id),
  KEY idx_options (options_id, options_values_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS products_attributes_download;
CREATE TABLE products_attributes_download (
  products_attributes_id INT NOT NULL,
  products_attributes_filename VARCHAR(255) NOT NULL DEFAULT '',
  products_attributes_maxdays INT(2) DEFAULT 0,
  products_attributes_maxcount INT(2) DEFAULT 0,
  PRIMARY KEY (products_attributes_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS products_content;
CREATE TABLE products_content (
  content_id INT(11) NOT NULL AUTO_INCREMENT,
  products_id INT(11) NOT NULL DEFAULT 0,
  group_ids TEXT,
  content_name VARCHAR(32) NOT NULL DEFAULT '',
  content_file VARCHAR(64) NOT NULL,
  content_link TEXT NOT NULL,
  languages_id INT(11) NOT NULL DEFAULT 0,
  content_read INT(11) NOT NULL DEFAULT 0,
  file_comment TEXT NOT NULL,
  PRIMARY KEY (content_id),
  KEY idx_products_id (products_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS products_description;
CREATE TABLE products_description (
  products_id INT(11) NOT NULL,
  language_id TINYINT NOT NULL DEFAULT 1,
  products_name VARCHAR(255) NOT NULL DEFAULT '',
  products_description text,
  products_short_description text,
  products_keywords VARCHAR(255) DEFAULT NULL,
  products_meta_title text NOT NULL,
  products_meta_description text NOT NULL,
  products_meta_keywords text NOT NULL,
  products_url VARCHAR(255) DEFAULT NULL,
  products_viewed INT(5) DEFAULT 0,
  products_order_description text,
  PRIMARY KEY (products_id, language_id),
  KEY idx_products_name (products_name)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS products_graduated_prices;
CREATE TABLE products_graduated_prices (
  products_id INT(11) NOT NULL DEFAULT 0,
  quantity INT(11) NOT NULL DEFAULT 0,
  unitprice DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
  KEY idx_products_id (products_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS products_images;
CREATE TABLE products_images (
  image_id INT NOT NULL AUTO_INCREMENT,
  products_id INT NOT NULL,
  image_nr SMALLINT NOT NULL,
  image_name VARCHAR(254) NOT NULL,
  PRIMARY KEY (image_id),
  KEY idx_products_id (products_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS products_notifications;
CREATE TABLE products_notifications (
  products_id INT NOT NULL,
  customers_id INT NOT NULL,
  date_added DATETIME NOT NULL,
  PRIMARY KEY (products_id, customers_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS products_options;
CREATE TABLE products_options (
  products_options_id INT NOT NULL DEFAULT 0,
  language_id TINYINT NOT NULL DEFAULT 1,
  products_options_name VARCHAR(255) NOT NULL DEFAULT '',
  products_options_sortorder INT(11) NOT NULL,
  PRIMARY KEY (products_options_id, language_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS products_options_values;
CREATE TABLE products_options_values (
  products_options_values_id INT NOT NULL DEFAULT 0,
  language_id TINYINT NOT NULL DEFAULT 1,
  products_options_values_name VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (products_options_values_id, language_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS products_options_values_to_products_options;
CREATE TABLE products_options_values_to_products_options (
  products_options_values_to_products_options_id INT NOT NULL AUTO_INCREMENT,
  products_options_id INT NOT NULL,
  products_options_values_id INT NOT NULL,
  PRIMARY KEY (products_options_values_to_products_options_id),
  KEY idx_products_options_id (products_options_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS products_tags;
CREATE TABLE products_tags (
  products_id int(11) NOT NULL,
  options_id int(11) NOT NULL,
  values_id int(11) NOT NULL,
  products_options_id int(11) NOT NULL DEFAULT '0',
  products_options_values_id int(11) NOT NULL DEFAULT '0',
  KEY idx_products_options_values (products_id,options_id,values_id),
  KEY idx_products_options_id (products_options_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS products_tags_options;
CREATE TABLE products_tags_options (
  options_id int(11) NOT NULL,
  options_name varchar(128) NOT NULL,
  options_description text NOT NULL,
  options_content_group int(11) DEFAULT NULL,
  sort_order int(11) NOT NULL DEFAULT '0',
  languages_id int(11) NOT NULL,
  status int(1) NOT NULL DEFAULT '1',
  filter int(1) NOT NULL DEFAULT '1',
  last_modified datetime DEFAULT NULL,
  date_added datetime NOT NULL,
  products_options_id int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (options_id,languages_id),
  KEY idx_products_options_id (products_options_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS products_tags_values;
CREATE TABLE products_tags_values (
  values_id int(11) NOT NULL,
  options_id int(11) NOT NULL,
  values_name varchar(128) NOT NULL,
  values_description text NOT NULL,
  values_image varchar(128) NOT NULL,
  values_content_group int(11) DEFAULT NULL,
  sort_order int(11) NOT NULL DEFAULT '0',
  languages_id int(11) NOT NULL,
  status int(1) NOT NULL DEFAULT '1',
  filter int(1) NOT NULL DEFAULT '1',
  last_modified datetime DEFAULT NULL,
  date_added datetime NOT NULL,
  products_options_values_id int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (values_id,languages_id),
  KEY idx_options_id (options_id),
  KEY idx_products_options_values_id (products_options_values_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS products_to_categories;
CREATE TABLE products_to_categories (
  products_id INT NOT NULL,
  categories_id INT NOT NULL,
  PRIMARY KEY (products_id,categories_id),
  KEY idx_categories_id (categories_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS products_vpe;
CREATE TABLE products_vpe (
  products_vpe_id INT(11) NOT NULL DEFAULT 0,
  language_id TINYINT NOT NULL DEFAULT 1,
  products_vpe_name VARCHAR(32) NOT NULL DEFAULT '',
  PRIMARY KEY (products_vpe_id, language_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS products_xsell;
CREATE TABLE products_xsell (
  ID int(10) NOT NULL AUTO_INCREMENT,
  products_id INT(10) UNSIGNED NOT NULL DEFAULT 1,
  products_xsell_grp_name_id INT(10) UNSIGNED NOT NULL DEFAULT 1,
  xsell_id INT(10) UNSIGNED NOT NULL DEFAULT 1,
  sort_order INT(10) UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (ID)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS products_xsell_grp_name;
CREATE TABLE products_xsell_grp_name (
  products_xsell_grp_name_id INT(10) NOT NULL,
  xsell_sort_order INT(10) NOT NULL DEFAULT 0,
  language_id TINYINT NOT NULL DEFAULT 1,
  groupname VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (products_xsell_grp_name_id, language_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS reviews;
CREATE TABLE reviews (
  reviews_id INT NOT NULL AUTO_INCREMENT,
  products_id INT NOT NULL,
  customers_id int,
  customers_name VARCHAR(64) NOT NULL,
  reviews_rating INT(1),
  date_added DATETIME,
  last_modified DATETIME,
  reviews_read INT(5) NOT NULL DEFAULT 0,
  PRIMARY KEY (reviews_id),
  KEY idx_products_id (products_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS reviews_description;
CREATE TABLE reviews_description (
  reviews_id INT NOT NULL,
  languages_id INT NOT NULL,
  reviews_text text NOT NULL,
  PRIMARY KEY (reviews_id, languages_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS sessions;
CREATE TABLE sessions (
  sesskey VARCHAR(32) NOT NULL,
  expiry INT(11) unsigned NOT NULL,
  value text NOT NULL,
  flag VARCHAR( 5 ) NULL DEFAULT NULL,
  PRIMARY KEY (sesskey),
  KEY idx_expiry (expiry)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS shipping_status;
CREATE TABLE shipping_status (
  shipping_status_id INT DEFAULT 0 NOT NULL,
  language_id TINYINT DEFAULT 1 NOT NULL,
  shipping_status_name VARCHAR(32) NOT NULL,
  shipping_status_image VARCHAR(32) NOT NULL,
  sort_order INT(11) DEFAULT 0 NOT NULL,
  PRIMARY KEY (shipping_status_id, language_id),
  KEY idx_shipping_status_name (shipping_status_name)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS shop_configuration;
CREATE TABLE shop_configuration (
  configuration_id INT(11) NOT NULL AUTO_INCREMENT,
  configuration_key VARCHAR(255) NOT NULL DEFAULT '',
  configuration_value TEXT NOT NULL,
  PRIMARY KEY (configuration_id),
  KEY idx_configuration_key (configuration_key)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS specials;
CREATE TABLE specials (
  specials_id INT NOT NULL AUTO_INCREMENT,
  products_id INT NOT NULL,
  specials_quantity INT(4) NOT NULL,
  specials_new_products_price DECIMAL(15,4) NOT NULL,
  specials_date_added DATETIME,
  specials_last_modified DATETIME,
  start_date DATETIME,
  expires_date DATETIME,
  date_status_change DATETIME,
  status INT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (specials_id),
  KEY idx_products_id (products_id),
  KEY idx_status (status)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS tax_class;
CREATE TABLE tax_class (
  tax_class_id INT NOT NULL AUTO_INCREMENT,
  tax_class_title VARCHAR(32) NOT NULL,
  tax_class_description VARCHAR(255) NOT NULL,
  last_modified DATETIME NULL,
  date_added DATETIME NOT NULL,
  PRIMARY KEY (tax_class_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS tax_rates;
CREATE TABLE tax_rates (
  tax_rates_id INT NOT NULL AUTO_INCREMENT,
  tax_zone_id INT NOT NULL,
  tax_class_id INT NOT NULL,
  tax_priority INT(5) DEFAULT 1,
  tax_rate DECIMAL(7,4) NOT NULL,
  tax_description VARCHAR(255) NOT NULL,
  last_modified DATETIME NULL,
  date_added DATETIME NOT NULL,
  PRIMARY KEY (tax_rates_id),
  KEY idx_tax_zone_id (tax_zone_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS whos_online;
CREATE TABLE whos_online (
  customer_id INT(11) DEFAULT NULL,
  full_name VARCHAR(64) NOT NULL,
  session_id VARCHAR(32) NOT NULL,
  ip_address VARCHAR(39) NOT NULL,
  time_entry VARCHAR(14) NOT NULL,
  time_last_click VARCHAR(14) NOT NULL,
  last_page_url VARCHAR(255) NOT NULL,
  http_referer VARCHAR(255) NOT NULL,
  PRIMARY KEY (session_id),
  KEY idx_time_last_click (time_last_click)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS zones;
CREATE TABLE zones (
  zone_id INT NOT NULL AUTO_INCREMENT,
  zone_country_id INT NOT NULL,
  zone_code VARCHAR(32) NOT NULL,
  zone_name VARCHAR(64) NOT NULL,
  PRIMARY KEY (zone_id),
  UNIQUE idx_country_code (zone_country_id, zone_code)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS zones_to_geo_zones;
CREATE TABLE zones_to_geo_zones (
 association_id INT NOT NULL AUTO_INCREMENT,
 zone_country_id INT NOT NULL,
 zone_id INT NULL,
 geo_zone_id INT NULL,
 last_modified DATETIME NULL,
 date_added DATETIME NOT NULL,
 PRIMARY KEY (association_id),
 KEY idx_geo_zone_id (geo_zone_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS personal_offers_by_customers_status_0;
DROP TABLE IF EXISTS personal_offers_by_customers_status_1;
DROP TABLE IF EXISTS personal_offers_by_customers_status_2;
DROP TABLE IF EXISTS personal_offers_by_customers_status_3;
DROP TABLE IF EXISTS personal_offers_by_customers_status_4;

# 1 - Default, 2 - USA, 3 - Spain, 4 - Singapore, 5 - Germany , 6 - Taiwan , 7 - China
INSERT INTO address_format VALUES (1, '$firstname $lastname$cr$streets$cr$city, $postcode$cr$statecomma$country','$city / $country');
INSERT INTO address_format VALUES (2, '$firstname $lastname$cr$streets$cr$city, $state    $postcode$cr$country','$city, $state / $country');
INSERT INTO address_format VALUES (3, '$firstname $lastname$cr$streets$cr$city$cr$postcode - $statecomma$country','$state / $country');
INSERT INTO address_format VALUES (4, '$firstname $lastname$cr$streets$cr$city ($postcode)$cr$country', '$postcode / $country');
INSERT INTO address_format VALUES (5, '$firstname $lastname$cr$streets$cr$postcode $city$cr$country','$city / $country');
INSERT INTO address_format VALUES (6, '$firstname $lastname$cr$streets$cr$city $state $postcode$cr$country','$country / $city');
INSERT INTO address_format VALUES (7, '$firstname $lastname$cr$streets, $city$cr$postcode $state$cr$country','$country / $city');
INSERT INTO address_format VALUES (8, '$firstname $lastname$cr$streets$cr$city$cr$state$cr$postcode$cr$country','$postcode / $country');

# add entry for admin_access
INSERT INTO admin_access VALUES (1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);
INSERT INTO admin_access VALUES ('groups', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 2, 2, 2, 2, 3, 3, 3, 3, 3, 3, 3, 3, 3, 4, 4, 4, 4, 2, 4, 2, 2, 2, 2, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 1, 1, 1, 1, 1, 1, 1, 1, 5, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);

# banner
INSERT INTO `banners` VALUES (1, 'modified eCommerce Shopsoftware', 'http://www.modified-shop.org', 'banner_modified-ecommerce-shopsoftware_en.jpg', 'banner', '', '1', NULL, NULL, NULL, NOW(), NULL, 1);
INSERT INTO `banners` VALUES (2, 'modified eCommerce Shopsoftware', 'http://www.modified-shop.org', 'banner_modified-ecommerce-shopsoftware_de.jpg', 'banner', '', '2', NULL, NULL, NULL, NOW(), NULL, 1);

# carriers
INSERT INTO carriers VALUES (1, 'DHL', 'http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=$2&idc=$1', '10', NOW(), '');
INSERT INTO carriers VALUES (2, 'DPD', 'https://extranet.dpd.de/cgi-bin/delistrack?pknr=$1+&typ=1&lang=$2', '20', NOW(), '');
INSERT INTO carriers VALUES (3, 'GLS', 'https://gls-group.eu/DE/de/paketverfolgung?match=$1', '30', NOW(), '');
INSERT INTO carriers VALUES (4, 'UPS', 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums=$1', '40', NOW(), '');
INSERT INTO carriers VALUES (5, 'HERMES', 'http://tracking.hlg.de/Tracking.jsp?TrackID=$1', '50', NOW(), '');
INSERT INTO carriers VALUES (6, 'FEDEX', 'http://www.fedex.com/Tracking?action=track&tracknumbers=$1', '60', NOW(), '');
INSERT INTO carriers VALUES (7, 'TNT', 'http://www.tnt.de/servlet/Tracking?cons=$1', '70', NOW(), '');
INSERT INTO carriers VALUES (8, 'TRANS-O-FLEX', 'http://track.tof.de/trace/tracking.cgi?barcode=$1', '80', NOW(), '');
INSERT INTO carriers VALUES (9, 'KUEHNE-NAGEL', 'https://knlogin.kuehne-nagel.com/apps/fls.do?subevent=search&knReference=$1', '90', NOW(), '');
INSERT INTO carriers VALUES (10, 'ILOXX', 'http://www.iloxx.de/net/einzelversand/tracking.aspx?ix=$1', '100', NOW(), '');
INSERT INTO carriers VALUES (11, 'LogoiX', 'http://www.logoix.com/cgi-bin/tnt.pl?q=$1', '110', NOW(), '');

# file flag
INSERT INTO cm_file_flags (file_flag, file_flag_name) VALUES ('0', 'information');
INSERT INTO cm_file_flags (file_flag, file_flag_name) VALUES ('1', 'content');

# configuration_group_id 1, My Shop
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'STORE_NAME', 'modified eCommerce Shopsoftware', 1, 1, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'STORE_OWNER', 'modified eCommerce Shopsoftware', 1, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'STORE_NAME_ADDRESS', 'Store Name\nAddress\nCountry\nPhone', 1, 3, NULL, NOW(), NULL, 'xtc_cfg_textarea(');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'STORE_COUNTRY', '81', 1, 4, NULL, NOW(), 'xtc_get_country_name', 'xtc_cfg_pull_down_country_list(');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'STORE_ZONE', '', 1, 5, NULL, NOW(), 'xtc_cfg_get_zone_name', 'xtc_cfg_pull_down_zone_list(');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'STORE_OWNER_EMAIL_ADDRESS', 'owner@your-shop.com', 1, 6, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'EMAIL_FROM', 'modified eCommerce Shopsoftware owner@your-shop.com', 1, 7, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'USE_DEFAULT_LANGUAGE_CURRENCY', 'false', 1, 10, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'USE_BROWSER_LANGUAGE', 'false', 1, 11, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'DISPLAY_CART', 'false', 1, 13, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SHOW_COUNTS', 'false', 1, 17, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'DEFAULT_CUSTOMERS_STATUS_ID_ADMIN', '0', 1, 20, NULL, NOW(), 'xtc_get_customers_status_name', 'xtc_cfg_pull_down_customers_status_list(');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'DEFAULT_CUSTOMERS_STATUS_ID_GUEST', '1', 1, 21, NULL, NOW(), 'xtc_get_customers_status_name', 'xtc_cfg_pull_down_customers_status_list(');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'DEFAULT_CUSTOMERS_STATUS_ID', '2', 1, 23, NULL, NOW(), 'xtc_get_customers_status_name', 'xtc_cfg_pull_down_customers_status_list(');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ALLOW_ADD_TO_CART', 'false', 1, 24, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'CURRENT_TEMPLATE', 'tpl_modified', 1, 26, NULL, NOW(), NULL, 'xtc_cfg_pull_down_template_sets(');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'USE_SHORT_DATE_FORMAT', 'true', '1', '50', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');

# Constants for checkout options
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'CHECKOUT_USE_PRODUCTS_SHORT_DESCRIPTION', 'true', 1, 40, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'CHECKOUT_USE_PRODUCTS_DESCRIPTION_FALLBACK_LENGTH', '300', 1, 41, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'CHECKOUT_SHOW_PRODUCTS_IMAGES', 'true', 1, 42, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');

# independent billingnumber and date
#INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'IBN_BILLNR', '1', '1', '99', NULL, NOW(), NULL, NULL);
#INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'IBN_BILLNR_FORMAT', '{n}-{d}-{m}-{y}', '1', '99', NULL, NOW(), NULL, NULL);

# configuration_group_id 2, Minimum Values
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ENTRY_FIRST_NAME_MIN_LENGTH', '2', 2, 1, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ENTRY_LAST_NAME_MIN_LENGTH', '2', 2, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ENTRY_DOB_MIN_LENGTH', '10', 2, 3, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ENTRY_EMAIL_ADDRESS_MIN_LENGTH', '6', 2, 4, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ENTRY_STREET_ADDRESS_MIN_LENGTH', '4', 2, 5, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ENTRY_COMPANY_MIN_LENGTH', '2', 2, 6, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ENTRY_POSTCODE_MIN_LENGTH', '4', 2, 7, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ENTRY_CITY_MIN_LENGTH', '3', 2, 8, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ENTRY_STATE_MIN_LENGTH', '0', 0, 9, NULL, NOW(), NULL, NULL); 
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ENTRY_TELEPHONE_MIN_LENGTH', '3', 2, 10, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ENTRY_PASSWORD_MIN_LENGTH', '5', 2, 11, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'POLICY_MIN_LOWER_CHARS', '1', 2, 12, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'POLICY_MIN_UPPER_CHARS', '1', 2, 12, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'POLICY_MIN_NUMERIC_CHARS', '1', 2, 12, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'POLICY_MIN_SPECIAL_CHARS', '1', 2, 12, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'REVIEW_TEXT_MIN_LENGTH', '50', 2, 14, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MIN_DISPLAY_BESTSELLERS', '1', 2, 15, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MIN_DISPLAY_ALSO_PURCHASED', '1', 2, 16, NULL, NOW(), NULL, NULL);

# configuration_group_id 3, Maximum Values
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MAX_ADDRESS_BOOK_ENTRIES', '5', 3, 1, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MAX_DISPLAY_SEARCH_RESULTS', '20', 3, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MAX_DISPLAY_PAGE_LINKS', '5', 3, 3, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MAX_DISPLAY_SPECIAL_PRODUCTS', '9', 3, 4, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MAX_DISPLAY_NEW_PRODUCTS', '9', 3, 5, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MAX_DISPLAY_UPCOMING_PRODUCTS', '10', 3, 6, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MAX_DISPLAY_MANUFACTURERS_IN_A_LIST', '0', 3, 7, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MAX_MANUFACTURERS_LIST', '1', 3, 7, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MAX_DISPLAY_MANUFACTURER_NAME_LEN', '26', 3, 8, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MAX_DISPLAY_NEW_REVIEWS', '6', 3, 9, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MAX_RANDOM_SELECT_REVIEWS', '10', 3, 10, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MAX_RANDOM_SELECT_NEW', '10', 3, 11, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MAX_RANDOM_SELECT_SPECIALS', '10', 3, 12, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MAX_DISPLAY_CATEGORIES_PER_ROW', '5', 3, 13, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MAX_DISPLAY_PRODUCTS_NEW', '10', 3, 14, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MAX_DISPLAY_BESTSELLERS', '10', 3, 15, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MAX_DISPLAY_BESTSELLERS_DAYS', '100', 3, 15, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MAX_DISPLAY_ALSO_PURCHASED', '6', 3, 16, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MAX_DISPLAY_PRODUCTS_IN_ORDER_HISTORY_BOX', '6', 3, 17, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MAX_DISPLAY_ORDER_HISTORY', '10', 3, 18, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'PRODUCT_REVIEWS_VIEW', '5', 3, 19, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MAX_PRODUCTS_QTY', '1000', 3, 21, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MAX_DISPLAY_NEW_PRODUCTS_DAYS', '30', 3, 22, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MAX_DISPLAY_PRODUCTS_CATEGORY', '10', 3, 23, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MAX_DISPLAY_ADVANCED_SEARCH_RESULTS', '10', 3, 24, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MAX_DISPLAY_PRODUCTS_HISTORY', '6', 3, 25, NULL, NOW(), NULL, NULL);

# configuration_group_id 4, Images Options
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'CONFIG_CALCULATE_IMAGE_SIZE', 'true', 4, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'IMAGE_QUALITY', '100', 4, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'PRODUCT_IMAGE_THUMBNAIL_WIDTH', '160', 4, 7, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'PRODUCT_IMAGE_THUMBNAIL_HEIGHT', '160', 4, 8, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'PRODUCT_IMAGE_INFO_WIDTH', '230', 4, 9, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'PRODUCT_IMAGE_INFO_HEIGHT', '230', 4, 10, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'PRODUCT_IMAGE_POPUP_WIDTH', '800', 4, 11, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'PRODUCT_IMAGE_POPUP_HEIGHT', '800', 4, 12, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'PRODUCT_IMAGE_THUMBNAIL_MERGE', '', 4, 17, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'PRODUCT_IMAGE_INFO_MERGE', '', 4, 25, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'PRODUCT_IMAGE_POPUP_MERGE', '', 4, 33, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MO_PICS', '3', '4', '3', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'IMAGE_MANIPULATOR', 'image_manipulator_GD2_advanced.php', '4', '3', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'image_manipulator_GD2.php\', \'image_manipulator_GD2_advanced.php\', \'image_manipulator_GD1.php\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'PRODUCT_IMAGE_NO_ENLARGE_UNDER_DEFAULT', 'false', 4, 6, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(''true'', ''false''), ');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'PRODUCT_IMAGE_SHOW_NO_IMAGE', 'false', 4, 6, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(''true'', ''false''), ');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'CATEGORIES_IMAGE_SHOW_NO_IMAGE', 'true', 4, 6, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(''true'', ''false''), ');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MANUFACTURER_IMAGE_SHOW_NO_IMAGE', 'false', 4, 6, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(''true'', ''false''), ');

# configuration_group_id 5, Customer Details
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ACCOUNT_GENDER', 'true', 5, 10, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ACCOUNT_DOB', 'false', 5, 20, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ACCOUNT_COMPANY', 'true', 5, 30, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ACCOUNT_SUBURB', 'true', 5, 50, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ACCOUNT_STATE', 'false', 5, 60, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ACCOUNT_TELEPHONE_OPTIONAL', 'false', 5, 70, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ACCOUNT_OPTIONS', 'account', 5, 100, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'account\', \'guest\', \'both\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'DELETE_GUEST_ACCOUNT', 'true', 5, 110, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'GUEST_ACCOUNT_EDIT', 'false', 5, 120, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'DISPLAY_PRIVACY_CHECK', 'true', 5, 130, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');

# configuration_group_id 6, Module Options
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_PAYMENT_INSTALLED', '', 6, 0, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_ORDER_TOTAL_INSTALLED', 'ot_subtotal.php;ot_shipping.php;ot_tax.php;ot_total.php', 6, 0, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_SHIPPING_INSTALLED', '', 6, 0, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'DEFAULT_CURRENCY', 'EUR', 6, 0, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'DEFAULT_LANGUAGE', 'de', 6, 0, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'DEFAULT_ORDERS_STATUS_ID', '1', 6, 0, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'DEFAULT_PRODUCTS_VPE_ID', '', 6, 0, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'DEFAULT_SHIPPING_STATUS_ID', '1', 6, 0, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_ORDER_TOTAL_SHIPPING_STATUS', 'true', 6, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER', '30', 6, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING', 'false', 6, 3, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER', '50', 6, 4, NULL, NOW(), 'currencies->format', NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER_INTERNATIONAL', '50', 6, 4, NULL, NOW(), 'currencies->format', NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_ORDER_TOTAL_SHIPPING_DESTINATION', 'national', 6, 5, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'national\', \'international\', \'both\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_ORDER_TOTAL_SHIPPING_TAX_CLASS', '0', 6, 7, NULL, NOW(), 'xtc_get_tax_class_title', 'xtc_cfg_pull_down_tax_classes(');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_ORDER_TOTAL_SUBTOTAL_STATUS', 'true', 6, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_ORDER_TOTAL_SUBTOTAL_SORT_ORDER', '10', 6, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_ORDER_TOTAL_TAX_STATUS', 'true', 6, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_ORDER_TOTAL_TAX_SORT_ORDER', '50', 6, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_ORDER_TOTAL_TOTAL_STATUS', 'true', 6, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_ORDER_TOTAL_TOTAL_SORT_ORDER', '99', 6, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_ORDER_TOTAL_DISCOUNT_STATUS', 'true', 6, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_ORDER_TOTAL_DISCOUNT_SORT_ORDER', '20', 6, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_ORDER_TOTAL_SUBTOTAL_NO_TAX_STATUS', 'true', 6, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_ORDER_TOTAL_SUBTOTAL_NO_TAX_SORT_ORDER','40', 6, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'COMPRESS_STYLESHEET_TIME', '', 6, 100, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'NEWSFEED_LAST_READ', '', 6, 100, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'NEWSFEED_LAST_UPDATE', '', 6, 100, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SESSION_CHECK_SSL_SESSION_ID', 'False', 6, 100, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'True\', \'False\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SESSION_CHECK_USER_AGENT', 'False', 6, 100, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'True\', \'False\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SESSION_CHECK_IP_ADDRESS', 'False', 6, 100, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'True\', \'False\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SESSION_RECREATE', 'False', 6, 100, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'True\', \'False\'),');

# configuration_group_id 7, Shipping/Packaging
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SHIPPING_MAX_WEIGHT', '50', 7, 3, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SHIPPING_BOX_WEIGHT', '3', 7, 4, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SHIPPING_BOX_PADDING', '10', 7, 5, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SHOW_SHIPPING', 'true', 7, 6, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SHOW_SHIPPING_EXCL', 'true', 7, 6, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SHIPPING_INFOS', '1', 7, 7, NULL, NOW(), NULL, 'xtc_cfg_select_content(\'SHIPPING_INFOS\',');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'CHECK_CHEAPEST_SHIPPING_MODUL', 'false', 7, 8, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SHOW_SELFPICKUP_FREE', 'false', 7, 9, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
#INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SHIPPING_DEFAULT_TAX_CLASS_METHOD', '1', 7, 7, NULL, NOW(), 'xtc_get_default_tax_class_method_name', 'xtc_cfg_pull_down_default_tax_class_methods(');

# configuration_group_id 8, Product Listing
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'PRODUCT_LIST_FILTER', 'true', 8, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SHOW_BUTTON_BUY_NOW', 'false', 8, 2, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'USE_PAGINATION_LIST', 'true', 8, 8, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'EXPECTED_PRODUCTS_SORT', 'desc', 8, 4, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'asc\', \'desc\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'EXPECTED_PRODUCTS_FIELD', 'date_expected', 8, 5, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'products_name\', \'date_expected\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'DISPLAY_FILTER_INDEX', '3,12,27,all', 8, 100, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'DISPLAY_FILTER_SPECIALS', '3,12,27,all', 8, 101, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'DISPLAY_FILTER_PRODUCTS_NEW', '3,12,27,all', 8, 102, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'DISPLAY_FILTER_ADVANCED_SEARCH_RESULT', '4,12,32,all', 8, 103, NULL, NOW(), NULL, NULL);

# configuration_group_id 9, Stock
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'STOCK_CHECK', 'true', 9, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ATTRIBUTE_STOCK_CHECK', 'true', 9, 2, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'STOCK_LIMITED', 'true', 9, 3, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'STOCK_ALLOW_CHECKOUT', 'true', 9, 4, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'STOCK_MARK_PRODUCT_OUT_OF_STOCK', '<span style="color:red">***</span>', 9, 5, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'STOCK_REORDER_LEVEL', '5', 9, 6, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'STOCK_CHECKOUT_UPDATE_PRODUCTS_STATUS', 'false', 9, 20, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'STOCK_CHECK_SPECIALS', 'false', 9, 21, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ATTRIBUTES_VALID_CHECK', 'true', 9, 22, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');

# configuration_group_id 10, Logging
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'STORE_PAGE_PARSE_TIME', 'false', 10, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'STORE_PAGE_PARSE_TIME_THRESHOLD', '1.0', 10, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'STORE_PARSE_DATE_TIME_FORMAT', '%d/%m/%Y %H:%M:%S', 10, 3, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'DISPLAY_PAGE_PARSE_TIME', 'none', 10, 4, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'none\', \'admin\', \'all\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'STORE_DB_TRANSACTIONS', 'false', 10, 5, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'STORE_DB_SLOW_QUERY', 'false', 10, 6, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'STORE_DB_SLOW_QUERY_TIME', '1.0', 10, 7, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'DISPLAY_ERROR_REPORTING', 'none', 10, 8, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'none\', \'admin\', \'all\'),');

# configuration_group_id 11, Cache
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'USE_CACHE', 'false', 11, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'DIR_FS_CACHE', 'cache', 11, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'CACHE_LIFETIME', '3600', 11, 3, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'CACHE_CHECK', 'true', 11, 4, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'DB_CACHE', 'false', 11, 5, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'DB_CACHE_EXPIRE', '3600', 11, 6, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'DB_CACHE_TYPE', 'files', 11, 7, NULL, NOW(), NULL, 'xtc_cfg_pull_down_cache_type(\'DB_CACHE_TYPE\',');

# configuration_group_id 12, E-Mail Options
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'EMAIL_TRANSPORT', 'mail', 12, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'sendmail\', \'smtp\', \'mail\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SENDMAIL_PATH', '/usr/sbin/sendmail', 12, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SMTP_MAIN_SERVER', 'localhost', 12, 3, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SMTP_BACKUP_SERVER', 'localhost', 12, 4, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SMTP_PORT', '25', 12, 5, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SMTP_USERNAME', 'Please Enter', 12, 6, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SMTP_PASSWORD', 'Please Enter', 12, 7, NULL, NOW(), NULL, 'xtc_cfg_password_field;SMTP_PASSWORD');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SMTP_AUTH', 'false', 12, 8, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SMTP_SECURE', '', 12, 8, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'none\', \'ssl\', \'tls\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'EMAIL_LINEFEED', 'LF', 12, 9, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'LF\', \'CRLF\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'EMAIL_USE_HTML', 'true', 12, 10, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ENTRY_EMAIL_ADDRESS_CHECK', 'false', 12, 11, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SEND_EMAILS', 'true', 12, 12, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'EMAIL_SQL_ERRORS', 'false', 12, 14, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SEND_EMAILS_DOUBLE_OPT_IN', 'true', 12, 14, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SEND_MAIL_ACCOUNT_CREATED', 'false', 12, 14, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'STATUS_EMAIL_SENT_COPY_TO_ADMIN', 'false', 12, 14, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'EMAIL_WORD_WRAP', '50', 12, 18, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'EMAIL_SIGNATURE_ID', '', 12, 19, NULL, NOW(), NULL, 'xtc_cfg_select_content(\'EMAIL_SIGNATURE_ID\',');

# Constants for images
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SHOW_IMAGES_IN_EMAIL', 'false', '12', '15', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SHOW_IMAGES_IN_EMAIL_DIR', 'thumbnail', '12', '16', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'thumbnail\', \'info\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SHOW_IMAGES_IN_EMAIL_STYLE', 'max-width:90px;max-height:120px;', '12', '17', NULL, NOW(), NULL, NULL);

# Constants for contact_us
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'CONTACT_US_EMAIL_ADDRESS', 'contact@your-shop.com', 12, 20, NULL, NOW(), NULL, 'xtc_cfg_input_email_language;CONTACT_US_EMAIL_ADDRESS');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'CONTACT_US_NAME', 'DE::Kontaktformular||EN::Contactform', 12, 21, NULL, NOW(), NULL, 'xtc_cfg_input_email_language;CONTACT_US_NAME');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'CONTACT_US_REPLY_ADDRESS', '', 12, 22, NULL, NOW(), NULL, 'xtc_cfg_input_email_language;CONTACT_US_REPLY_ADDRESS');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'CONTACT_US_REPLY_ADDRESS_NAME', '', 12, 23, NULL, NOW(), NULL, 'xtc_cfg_input_email_language;CONTACT_US_REPLY_ADDRESS_NAME');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'CONTACT_US_EMAIL_SUBJECT', 'DE::Ihre Anfrage||EN::Your inquiry', 12, 24, NULL, NOW(), NULL, 'xtc_cfg_input_email_language;CONTACT_US_EMAIL_SUBJECT');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'CONTACT_US_FORWARDING_STRING', '', 12, 25, NULL, NOW(), NULL, 'xtc_cfg_input_email_language;CONTACT_US_FORWARDING_STRING');

# Constants for support system
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'EMAIL_SUPPORT_ADDRESS', 'support@your-shop.com', 12, 26, NULL, NOW(), NULL, 'xtc_cfg_input_email_language;EMAIL_SUPPORT_ADDRESS');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'EMAIL_SUPPORT_NAME', 'DE::Support System||EN::Support System', 12, 27, NULL, NOW(), NULL, 'xtc_cfg_input_email_language;EMAIL_SUPPORT_NAME');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'EMAIL_SUPPORT_REPLY_ADDRESS', '', 12, 28, NULL, NOW(), NULL, 'xtc_cfg_input_email_language;EMAIL_SUPPORT_REPLY_ADDRESS');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'EMAIL_SUPPORT_REPLY_ADDRESS_NAME', '', 12, 29, NULL, NOW(), NULL, 'xtc_cfg_input_email_language;EMAIL_SUPPORT_REPLY_ADDRESS_NAME');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'EMAIL_SUPPORT_SUBJECT', 'DE::Ihr Kundenkonto||EN::Your customer account', 12, 30, NULL, NOW(), NULL, 'xtc_cfg_input_email_language;EMAIL_SUPPORT_SUBJECT');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'EMAIL_SUPPORT_FORWARDING_STRING', '', 12, 31, NULL, NOW(), NULL, 'xtc_cfg_input_email_language;EMAIL_SUPPORT_FORWARDING_STRING');

# Constants for billing system
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'EMAIL_BILLING_ADDRESS', 'billing@your-shop.com', 12, 32, NULL, NOW(), NULL, 'xtc_cfg_input_email_language;EMAIL_BILLING_ADDRESS');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'EMAIL_BILLING_NAME', 'DE::Verrechnungssystem||EN::Billingsystem', 12, 33, NULL, NOW(), NULL, 'xtc_cfg_input_email_language;EMAIL_BILLING_NAME');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'EMAIL_BILLING_REPLY_ADDRESS', '', 12, 34, NULL, NOW(), NULL, 'xtc_cfg_input_email_language;EMAIL_BILLING_REPLY_ADDRESS');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'EMAIL_BILLING_REPLY_ADDRESS_NAME', '', 12, 35, NULL, NOW(), NULL, 'xtc_cfg_input_email_language;EMAIL_BILLING_REPLY_ADDRESS_NAME');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'EMAIL_BILLING_SUBJECT', 'DE::Ihre Bestellung||EN::Your order', 12, 36, NULL, NOW(), NULL, 'xtc_cfg_input_email_language;EMAIL_BILLING_SUBJECT');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'EMAIL_BILLING_FORWARDING_STRING', '', 12, 37, NULL, NOW(), NULL, 'xtc_cfg_input_email_language;EMAIL_BILLING_FORWARDING_STRING');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'EMAIL_BILLING_SUBJECT_ORDER', 'DE::Ihre Bestellung {$nr} vom {$date}||EN::Your order {$nr} from {$date}', 12, 38, NULL, NOW(), NULL, 'xtc_cfg_input_email_language;EMAIL_BILLING_SUBJECT_ORDER');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'EMAIL_BILLING_ATTACHMENTS', '', 12, 39, NULL, NOW(), NULL, 'xtc_cfg_input_email_language;EMAIL_BILLING_ATTACHMENTS');

# configuration_group_id 13, Download
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'DOWNLOAD_ENABLED', 'false', 13, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'DOWNLOAD_BY_REDIRECT', 'false', 13, 2, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'DOWNLOAD_UNALLOWED_PAYMENT', 'banktransfer,cod,invoice,moneyorder', 13, 5, NULL, NOW(), NULL, 'xtc_cfg_checkbox_unallowed_module(\'payment\', \'DOWNLOAD_UNALLOWED_PAYMENT\',');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'DOWNLOAD_MIN_ORDERS_STATUS', '1', 13, 5, NULL, NOW(), NULL, 'xtc_cfg_multi_checkbox(\'xtc_get_orders_status\', \'chr(44)\',');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'DOWNLOAD_MULTIPLE_ATTRIBUTES_ALLOWED', 'false', 13, 6, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'DOWNLOAD_SHOW_LANG_DROPDOWN', 'true', 13, 7, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');

# configuration_group_id 14, GZIP Kompression
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'GZIP_COMPRESSION', 'false', 14, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'GZIP_LEVEL', '5', 14, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'COMPRESS_HTML_OUTPUT', 'true', 14, 3, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'COMPRESS_STYLESHEET', 'true', 14, 4, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');

# configuration_group_id 15, Sessions
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SESSION_WRITE_DIRECTORY', '/tmp', 15, 1, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SESSION_FORCE_COOKIE_USE', 'False', 15, 2, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'True\', \'False\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SESSION_LIFE_CUSTOMERS', '1440', '15', '20', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SESSION_LIFE_ADMIN', '7200', '15', '21', NULL, NOW(), NULL, NULL);

# configuration_group_id 16, Meta-Tags/Search engines
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'META_MAX_KEYWORD_LENGTH', '18', 16, 1, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'META_MIN_KEYWORD_LENGTH', '5', 16, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'META_KEYWORDS_NUMBER', '15', 16, 3, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'META_AUTHOR', '', 16, 4, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'META_PUBLISHER', '', 16, 5, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'META_COMPANY', '', 16, 6, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'META_TOPIC', 'shopping', 16, 7, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'META_REPLY_TO', 'xx@xx.com', 16, 8, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'META_REVISIT_AFTER', '5', 16, 9, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'META_ROBOTS', 'index,follow', 16, 10, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'META_DESCRIPTION', '', 16, 11, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'META_KEYWORDS', '', 16, 12, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SEARCH_ENGINE_FRIENDLY_URLS', 'false', 16, 13, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'CHECK_CLIENT_AGENT', 'true',16, 14, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'DISPLAY_BREADCRUMB_OPTION', 'name', 16, 15, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'name\', \'model\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'META_DESCRIPTION_LENGTH', '156', 16, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'META_PRODUCTS_KEYWORDS_LENGTH', '255', 16, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'META_KEYWORDS_LENGTH', '180', 16, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'META_TITLE_LENGTH', '55', 16, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'META_STOP_WORDS', '#german:\r\nab,aber,abgerufen,abgerufene,abgerufener,abgerufenes,acht,alle,allein,allem,allen,aller,allerdings,allerlei,alles,allgemein,allmählich,allzu,als,alsbald,also,am,an,ander,andere,anderem,anderen,anderer,andererseits,anderes,anderm,andern,andernfalls,anders,anerkannt,anerkannte,anerkannter,anerkanntes,anfangen,anfing,angefangen,angesetze,angesetzt,angesetzten,angesetzter,ansetzen,anstatt,arbeiten,auch,auf,aufgehört,aufgrund,aufhören,aufhörte,aufzusuchen,aus,ausdrücken,ausdrückt,ausdrückte,ausgenommen,ausser,ausserdem,author,autor,außen,außer,außerdem,außerhalb,bald,bearbeite,bearbeiten,bearbeitete,bearbeiteten,bedarf,bedurfte,bedürfen,befragen,befragte,befragten,befragter,begann,beginnen,begonnen,behalten,behielt,bei,beide,beiden,beiderlei,beides,beim,beinahe,beitragen,beitrugen,bekannt,bekannte,bekannter,bekennen,benutzt,bereits,berichten,berichtet,berichtete,berichteten,besonders,besser,bestehen,besteht,beträchtlich,bevor,bezüglich,bietet,bin,bis,bisher,bislang,bist,bleiben,blieb,bloss,bloß,brachte,brachten,brauchen,braucht,bringen,bräuchte,bsp.,bzw,böden,ca.,da,dabei,dadurch,dafür,dagegen,daher,dahin,damals,damit,danach,daneben,dank,danke,danken,dann,dannen,daran,darauf,daraus,darf,darfst,darin,darum,darunter,darüber,darüberhinaus,das,dass,dasselbe,davon,davor,dazu,daß,dein,deine,deinem,deinen,deiner,deines,dem,demnach,demselben,den,denen,denn,dennoch,denselben,der,derart,derartig,derem,deren,derer,derjenige,derjenigen,derselbe,derselben,derzeit,des,deshalb,desselben,dessen,desto,deswegen,dich,die,diejenige,dies,diese,dieselbe,dieselben,diesem,diesen,dieser,dieses,diesseits,dinge,dir,direkt,direkte,direkten,direkter,doch,doppelt,dort,dorther,dorthin,drauf,drei,dreißig,drin,dritte,drunter,drüber,du,dunklen,durch,durchaus,durfte,durften,dürfen,dürfte,eben,ebenfalls,ebenso,ehe,eher,eigenen,eigenes,eigentlich,ein,einbaün,eine,einem,einen,einer,einerseits,eines,einfach,einführen,einführte,einführten,eingesetzt,einig,einige,einigem,einigen,einiger,einigermaßen,einiges,einmal,eins,einseitig,einseitige,einseitigen,einseitiger,einst,einstmals,einzig,ende,entsprechend,entweder,er,ergänze,ergänzen,ergänzte,ergänzten,erhalten,erhielt,erhielten,erhält,erneut,erst,erste,ersten,erster,eröffne,eröffnen,eröffnet,eröffnete,eröffnetes,es,etc,etliche,etwa,etwas,euch,euer,eure,eurem,euren,eurer,eures,fall,falls,fand,fast,ferner,finden,findest,findet,folgende,folgenden,folgender,folgendes,folglich,fordern,fordert,forderte,forderten,fortsetzen,fortsetzt,fortsetzte,fortsetzten,fragte,frau,frei,freie,freier,freies,fuer,fünf,für,gab,ganz,ganze,ganzem,ganzen,ganzer,ganzes,gar,gbr,geb,geben,geblieben,gebracht,gedurft,geehrt,geehrte,geehrten,geehrter,gefallen,gefiel,gefälligst,gefällt,gegeben,gegen,gehabt,gehen,geht,gekommen,gekonnt,gemacht,gemocht,gemäss,genommen,genug,gern,gesagt,gesehen,gestern,gestrige,getan,geteilt,geteilte,getragen,gewesen,gewissermaßen,gewollt,geworden,ggf,gib,gibt,gleich,gleichwohl,gleichzeitig,glücklicherweise,gmbh,gratulieren,gratuliert,gratulierte,gute,guten,gängig,gängige,gängigen,gängiger,gängiges,gänzlich,hab,habe,haben,haette,halb,hallo,hast,hat,hatte,hatten,hattest,hattet,heraus,herein,heute,heutige,hier,hiermit,hiesige,hin,hinein,hinten,hinter,hinterher,hoch,hundert,hätt,hätte,hätten,höchstens,ich,igitt,ihm,ihn,ihnen,ihr,ihre,ihrem,ihren,ihrer,ihres,im,immer,immerhin,important,in,indem,indessen,info,infolge,innen,innerhalb,ins,insofern,inzwischen,irgend,irgendeine,irgendwas,irgendwen,irgendwer,irgendwie,irgendwo,ist,ja,je,jede,jedem,jeden,jedenfalls,jeder,jederlei,jedes,jedoch,jemand,jene,jenem,jenen,jener,jenes,jenseits,jetzt,jährig,jährige,jährigen,jähriges,kam,kann,kannst,kaum,kein,keine,keinem,keinen,keiner,keinerlei,keines,keineswegs,klar,klare,klaren,klares,klein,kleinen,kleiner,kleines,koennen,koennt,koennte,koennten,komme,kommen,kommt,konkret,konkrete,konkreten,konkreter,konkretes,konnte,konnten,könn,können,könnt,könnte,könnten,künftig,lag,lagen,langsam,lassen,laut,lediglich,leer,legen,legte,legten,leicht,leider,lesen,letze,letzten,letztendlich,letztens,letztes,letztlich,lichten,liegt,liest,links,längst,längstens,mache,machen,machst,macht,machte,machten,mag,magst,mal,man,manche,manchem,manchen,mancher,mancherorts,manches,manchmal,mann,margin,mehr,mehrere,mein,meine,meinem,meinen,meiner,meines,meist,meiste,meisten,meta,mich,mindestens,mir,mit,mithin,mochte,morgen,morgige,muessen,muesst,muesste,muss,musst,musste,mussten,muß,mußt,möchte,möchten,möchtest,mögen,möglich,mögliche,möglichen,möglicher,möglicherweise,müssen,müsste,müssten,müßt,müßte,nach,nachdem,nacher,nachhinein,nacht,nahm,natürlich,neben,nebenan,nehmen,nein,neu,neue,neuem,neuen,neuer,neues,neun,nicht,nichts,nie,niemals,niemand,nimm,nimmer,nimmt,nirgends,nirgendwo,noch,nun,nur,nutzen,nutzt,nutzung,nächste,nämlich,nötigenfalls,nützt,ob,oben,oberhalb,obgleich,obschon,obwohl,oder,oft,ohne,per,pfui,plötzlich,pro,reagiere,reagieren,reagiert,reagierte,rechts,regelmäßig,rief,rund,sage,sagen,sagt,sagte,sagten,sagtest,sang,sangen,schlechter,schließlich,schnell,schon,schreibe,schreiben,schreibens,schreiber,schwierig,schätzen,schätzt,schätzte,schätzten,sechs,sect,sehe,sehen,sehr,sehrwohl,seht,sei,seid,sein,seine,seinem,seinen,seiner,seines,seit,seitdem,seite,seiten,seither,selber,selbst,senke,senken,senkt,senkte,senkten,setzen,setzt,setzte,setzten,sich,sicher,sicherlich,sie,sieben,siebte,siehe,sieht,sind,singen,singt,so,sobald,sodaß,soeben,sofern,sofort,sog,sogar,solange,solch,solche,solchem,solchen,solcher,solches,soll,sollen,sollst,sollt,sollte,sollten,solltest,somit,sondern,sonst,sonstwo,sooft,soviel,soweit,sowie,sowohl,spielen,später,startet,startete,starteten,statt,stattdessen,steht,steige,steigen,steigt,stets,stieg,stiegen,such,suchen,sämtliche,tages,tat,tatsächlich,tatsächlichen,tatsächlicher,tatsächliches,tausend,teile,teilen,teilte,teilten,titel,total,trage,tragen,trotzdem,trug,trägt,tun,tust,tut,txt,tät,ueber,um,umso,unbedingt,und,ungefähr,unmöglich,unmögliche,unmöglichen,unmöglicher,unnötig,uns,unse,unsem,unsen,unser,unsere,unserem,unseren,unserer,unseres,unserm,unses,unten,unter,unterbrach,unterbrechen,unterhalb,unwichtig,usw,vergangen,vergangene,vergangener,vergangenes,vermag,vermutlich,vermögen,verrate,verraten,verriet,verrieten,version,versorge,versorgen,versorgt,versorgte,versorgten,versorgtes,veröffentlichen,veröffentlicher,veröffentlicht,veröffentlichte,veröffentlichten,veröffentlichtes,viel,viele,vielen,vieler,vieles,vielleicht,vielmals,vier,vollständig,vom,von,vor,voran,vorbei,vorgestern,vorher,vorne,vorüber,völlig,wachen,waere,wann,war,waren,warst,warum,was,weder,weg,wegen,weil,weiter,weitere,weiterem,weiteren,weiterer,weiteres,weiterhin,weiß,welche,welchem,welchen,welcher,welches,wem,wen,wenig,wenige,weniger,wenigstens,wenn,wenngleich,wer,werde,werden,werdet,weshalb,wessen,wichtig,wie,wieder,wieso,wieviel,wiewohl,will,willst,wir,wird,wirklich,wirst,wo,wodurch,wogegen,woher,wohin,wohingegen,wohl,wohlweislich,wolle,wollen,wollt,wollte,wollten,wolltest,wolltet,womit,woraufhin,woraus,worin,wurde,wurden,während,währenddessen,wär,wäre,wären,würde,würden,z.B.,zahlreich,zehn,zeitweise,ziehen,zieht,zog,zogen,zu,zudem,zuerst,zufolge,zugleich,zuletzt,zum,zumal,zur,zurück,zusammen,zuviel,zwanzig,zwar,zwei,zwischen,zwölf,ähnlich,übel,über,überall,überallhin,überdies,übermorgen,übrig,übrigens\r\n\r\n#english:\r\na\'s,able,about,above,abroad,according,accordingly,across,actually,adj,after,afterwards,again,against,ago,ahead,ain\'t,all,allow,allows,almost,alone,along,alongside,already,also,although,always,am,amid,amidst,among,amongst,an,and,another,any,anybody,anyhow,anyone,anything,anyway,anyways,anywhere,apart,appear,appreciate,appropriate,are,aren\'t,around,as,aside,ask,asking,associated,at,available,away,awfully,back,backward,backwards,be,became,because,become,becomes,becoming,been,before,beforehand,begin,behind,being,believe,below,beside,besides,best,better,between,beyond,both,brief,but,by,c\'mon,c\'s,came,can,can\'t,cannot,cant,caption,cause,causes,certain,certainly,changes,clearly,co,co.,com,come,comes,concerning,consequently,consider,considering,contain,containing,contains,corresponding,could,couldn\'t,course,currently,dare,daren\'t,definitely,described,despite,did,didn\'t,different,directly,do,does,doesn\'t,doing,don\'t,done,down,downwards,during,each,edu,eg,eight,eighty,either,else,elsewhere,end,ending,enough,entirely,especially,et,etc,even,ever,evermore,every,everybody,everyone,everything,everywhere,ex,exactly,example,except,fairly,far,farther,few,fewer,fifth,first,five,followed,following,follows,for,forever,former,formerly,forth,forward,found,four,from,further,furthermore,get,gets,getting,given,gives,go,goes,going,gone,got,gotten,greetings,had,hadn\'t,half,happens,hardly,has,hasn\'t,have,haven\'t,having,he,he\'d,he\'ll,he\'s,hello,help,hence,her,here,here\'s,hereafter,hereby,herein,hereupon,hers,herself,hi,him,himself,his,hither,hopefully,how,howbeit,however,hundred,i\'d,i\'ll,i\'m,i\'ve,ie,if,ignored,immediate,in,inasmuch,inc,inc.,indeed,indicate,indicated,indicates,inner,inside,insofar,instead,into,inward,is,isn\'t,it,it\'d,it\'ll,it\'s,its,itself,just,k,keep,keeps,kept,know,known,knows,last,lately,later,latter,latterly,least,less,lest,let,let\'s,like,liked,likely,likewise,little,look,looking,looks,low,lower,ltd,made,mainly,make,makes,many,may,maybe,mayn\'t,me,mean,meantime,meanwhile,merely,might,mightn\'t,mine,minus,miss,more,moreover,most,mostly,mr,mrs,much,must,mustn\'t,my,myself,name,namely,nd,near,nearly,necessary,need,needn\'t,needs,neither,never,neverf,neverless,nevertheless,new,next,nine,ninety,no,no-one,nobody,non,none,nonetheless,noone,nor,normally,not,nothing,notwithstanding,novel,now,nowhere,obviously,of,off,often,oh,ok,okay,old,on,once,one,one\'s,ones,only,onto,opposite,or,other,others,otherwise,ought,oughtn\'t,our,ours,ourselves,out,outside,over,overall,own,particular,particularly,past,per,perhaps,placed,please,plus,possible,presumably,probably,provided,provides,que,quite,qv,rather,rd,re,really,reasonably,recent,recently,regarding,regardless,regards,relatively,respectively,right,round,said,same,saw,say,saying,says,second,secondly,see,seeing,seem,seemed,seeming,seems,seen,self,selves,sensible,sent,serious,seriously,seven,several,shall,shan\'t,she,she\'d,she\'ll,she\'s,should,shouldn\'t,since,six,so,some,somebody,someday,somehow,someone,something,sometime,sometimes,somewhat,somewhere,soon,sorry,specified,specify,specifying,still,sub,such,sup,sure,t\'s,take,taken,taking,tell,tends,th,than,thank,thanks,thanx,that,that\'ll,that\'s,that\'ve,thats,the,their,theirs,them,themselves,then,thence,there,there\'d,there\'ll,there\'re,there\'s,there\'ve,thereafter,thereby,therefore,therein,theres,thereupon,these,they,they\'d,they\'ll,they\'re,they\'ve,thing,things,think,third,thirty,this,thorough,thoroughly,those,though,three,through,throughout,thru,thus,till,to,together,too,took,toward,towards,tried,tries,truly,try,trying,twice,two,un,under,underneath,undoing,unfortunately,unless,unlike,unlikely,until,unto,up,upon,upwards,us,use,used,useful,uses,using,usually,v,value,various,versus,very,via,viz,vs,want,wants,was,wasn\'t,way,we,we\'d,we\'ll,we\'re,we\'ve,welcome,well,went,were,weren\'t,what,what\'ll,what\'s,what\'ve,whatever,when,whence,whenever,where,where\'s,whereafter,whereas,whereby,wherein,whereupon,wherever,whether,which,whichever,while,whilst,whither,who,who\'d,who\'ll,who\'s,whoever,whole,whom,whomever,whose,why,will,willing,wish,with,within,without,won\'t,wonder,would,wouldn\'t,yes,yet,you,you\'d,you\'ll,you\'re,you\'ve,your,yours,yourself,yourselves,zero', 16, 16, NULL, NOW(), NULL, 'xtc_cfg_textarea(');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'META_GO_WORDS', '', 16, 17, NULL, NOW(), NULL, 'xtc_cfg_textarea(');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'META_CAT_SHOP_TITLE', 'false', 16, 18, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'META_PROD_SHOP_TITLE', 'false', 16, 19, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'META_CONTENT_SHOP_TITLE', 'false', 16, 20, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'META_SPECIALS_SHOP_TITLE', 'false', 16, 21, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'META_NEWS_SHOP_TITLE', 'false', 16, 22, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'META_SEARCH_SHOP_TITLE', 'false', 16, 23, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'META_OTHER_SHOP_TITLE', 'false', 16, 24, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'META_GOOGLE_VERIFICATION_KEY', '', 16, 25, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'META_BING_VERIFICATION_KEY', '', 16, 26, NULL, NOW(), NULL, NULL);

# configuration_group_id 17, Specialmodules
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'USE_WYSIWYG', 'true', 17, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'WYSIWYG_SKIN', 'moonocolor', 17, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'moono\', \'moonocolor\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ACTIVATE_GIFT_SYSTEM', 'false', 17, 2, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SECURITY_CODE_LENGTH', '10', 17, 3, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'NEW_SIGNUP_GIFT_VOUCHER_AMOUNT', '0', 17, 4, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'NEW_SIGNUP_DISCOUNT_COUPON', '', 17, 5, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ACTIVATE_SHIPPING_STATUS', 'true', 17, 6, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'DISPLAY_CONDITIONS_ON_CHECKOUT', 'false', 17, 7, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SHOW_IP_LOG', 'false', 17, 8, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SAVE_IP_LOG', 'false', 17, 8, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\', \'xxx\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'GROUP_CHECK', 'false', 17, 9, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_SMALL_BUSINESS', 'false', 17, 9, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ACTIVATE_NAVIGATOR', 'false', 17, 10, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'QUICKLINK_ACTIVATED', 'true', 17, 11, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ACTIVATE_REVERSE_CROSS_SELLING', 'true', 17, 12, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'DISPLAY_REVOCATION_ON_CHECKOUT', 'true', 17, 13, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'DISPLAY_REVOCATION_VIRTUAL_ON_CHECKOUT', 'false', 17, 13, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'REVOCATION_ID', '9', 17, 14, NULL, NOW(), NULL, 'xtc_cfg_select_content(\'REVOCATION_ID\',');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SHIPPING_STATUS_INFOS', '10', 17, 14, NULL, NOW(), NULL, 'xtc_cfg_select_content(\'SHIPPING_STATUS_INFOS\',');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'GOOGLE_RSS_FEED_REFID', '', 17, 15, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'CHECK_FIRST_PAYMENT_MODUL', 'false', 17, 16, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'INVOICE_INFOS', '12', 17, 17, NULL, NOW(), NULL, 'xtc_cfg_select_content(\'INVOICE_INFOS\',');

#configuration_group_id 18, VAT reg no
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ACCOUNT_COMPANY_VAT_CHECK', 'true', 18, 4, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'STORE_OWNER_VAT_ID', '', 18, 3, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'DEFAULT_CUSTOMERS_VAT_STATUS_ID', '4', 18, 23, NULL, NOW(), 'xtc_get_customers_status_name', 'xtc_cfg_pull_down_customers_status_list(');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ACCOUNT_COMPANY_VAT_LIVE_CHECK', 'true', 18, 4, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ACCOUNT_COMPANY_VAT_GROUP', 'true', 18, 4, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ACCOUNT_VAT_BLOCK_ERROR', 'true', 18, 4, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'DEFAULT_CUSTOMERS_VAT_STATUS_ID_LOCAL', '3', '18', '24', NULL, NOW(), 'xtc_get_customers_status_name', 'xtc_cfg_pull_down_customers_status_list(');

#configuration_group_id 19, Google Conversion
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'GOOGLE_CONVERSION_ID', '', '19', '2', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'GOOGLE_LANG', 'de', '19', '3', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'GOOGLE_CONVERSION', 'false', '19', '0', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'GOOGLE_CONVERSION_LABEL', 'Purchase', '19', '4', NULL, NOW(), NULL, NULL);

#configuration_group_id 20, Import/export
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'CSV_TEXTSIGN', '"', '20', '1', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'CSV_SEPERATOR', ';', '20', '2', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'COMPRESS_EXPORT', 'false', '20', '3', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'CSV_CATEGORY_DEFAULT', '0', '20', '4', NULL, NOW(), NULL, 'xtc_cfg_get_category_tree(');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'CSV_CAT_DEPTH', '4', '20', '5', NULL, NOW(), NULL, NULL);

#configuration_group_id 21, Afterbuy
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'AFTERBUY_PARTNERID', '', '21', '2', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'AFTERBUY_PARTNERPASS', '', '21', '3', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'AFTERBUY_USERID', '', '21', '4', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'AFTERBUY_ORDERSTATUS', '1', '21', '5', NULL, NOW(), 'xtc_get_order_status_name' , 'xtc_cfg_pull_down_order_statuses(');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'AFTERBUY_ACTIVATED', 'false', '21', '6', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
#INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'AFTERBUY_DEALERS', '3', '21', '7', NULL , NOW(), NULL , NULL);
#INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'AFTERBUY_IGNORE_GROUPE', '', '21', '8', NULL , NOW(), NULL , NULL);

#configuration_group_id 22, Search Options
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SEARCH_IN_DESC', 'true', '22', '2', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SEARCH_IN_ATTR', 'true', '22', '3', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ADVANCED_SEARCH_DEFAULT_OPERATOR', 'and', '22', '4', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'and\', \'or\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SEARCH_IN_MANU', 'true', '22', '4', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SEARCH_IN_FILTER', 'true', '22', '5', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SEARCH_AC_STATUS', 'true', '22', '10', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SEARCH_AC_MIN_LENGTH', '3', '22', '11', NULL , NOW(), NULL , NULL);

#configuration_group_id 23, econda
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'TRACKING_ECONDA_ACTIVE', 'false', 23, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'TRACKING_ECONDA_ID','', 23, 2, NULL, NOW(), NULL, NULL);

#configuration_group_id 24, google analytics, piwik & facebook tracking
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'TRACKING_COUNT_ADMIN_ACTIVE', 'false', 24, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'TRACKING_GOOGLEANALYTICS_ACTIVE', 'false', 24, 2, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'TRACKING_GOOGLEANALYTICS_ID','UA-XXXXXXX-X', 24, 3, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'TRACKING_GOOGLEANALYTICS_UNIVERSAL', 'false', 24, 3, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'TRACKING_GOOGLEANALYTICS_DOMAIN','example.de', 24, 3, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'TRACKING_GOOGLE_LINKID', 'false', 24, 3, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'TRACKING_GOOGLE_DISPLAY', 'false', 24, 3, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'TRACKING_GOOGLE_ECOMMERCE', 'false', 24, 3, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'TRACKING_PIWIK_ACTIVE', 'false', 24, 4, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'TRACKING_PIWIK_LOCAL_PATH','www.domain.de/piwik', 24, 5, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'TRACKING_PIWIK_ID','1', 24, 6, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'TRACKING_PIWIK_GOAL','1', 24, 7, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'TRACKING_FACEBOOK_ACTIVE', 'false', 24, 8, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'TRACKING_FACEBOOK_ID','', 24, 9, NULL, NOW(), NULL, NULL);

#configuration_group_id 25, captcha
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_CAPTCHA_ACTIVE', 'newsletter,contact,password', 25, 1, NULL, NOW(), NULL, 'xtc_cfg_multi_checkbox(array(\'newsletter\' => \'Newsletter\', \'contact\' => \'Contact\', \'password\' => \'Password\', \'reviews\' => \'Reviews\'), \',\',');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_CAPTCHA_LOGGED_IN', 'False', 25, 2, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'True\', \'False\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_CAPTCHA_USE_COLOR', 'True', 25, 10, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'True\', \'False\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_CAPTCHA_USE_SHADOW', 'False', 25, 11, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'True\', \'False\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_CAPTCHA_CODE_LENGTH', '6', '25', '12', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_CAPTCHA_NUM_LINES', '70', '25', '13', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_CAPTCHA_MIN_FONT', '24', '25', '14', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_CAPTCHA_MAX_FONT', '28', '25', '15', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_CAPTCHA_BACKGROUND_RGB', '192,192,192', '25', '16', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_CAPTCHA_LINES_RGB', '220,148,002', '25', '17', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_CAPTCHA_CHARS_RGB', '112,112,112', '25', '18', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_CAPTCHA_WIDTH', '240', '25', '19', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MODULE_CAPTCHA_HEIGHT', '50', '25', '20', NULL, NOW(), NULL, NULL);

#configuration_group_id 31, Moneybookers
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, '_PAYMENT_MONEYBOOKERS_EMAILID', '', 31, 1, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, '_PAYMENT_MONEYBOOKERS_PWD','', 31, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, '_PAYMENT_MONEYBOOKERS_MERCHANTID','', 31, 3, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, '_PAYMENT_MONEYBOOKERS_TMP_STATUS_ID','0', 31, 4, NULL, NOW(), 'xtc_get_order_status_name' , 'xtc_cfg_pull_down_order_statuses(');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, '_PAYMENT_MONEYBOOKERS_PROCESSED_STATUS_ID','0', 31, 5, NULL, NOW(),'xtc_get_order_status_name' , 'xtc_cfg_pull_down_order_statuses(');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, '_PAYMENT_MONEYBOOKERS_PENDING_STATUS_ID','0', 31, 6, NULL, NOW(), 'xtc_get_order_status_name' , 'xtc_cfg_pull_down_order_statuses(');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, '_PAYMENT_MONEYBOOKERS_CANCELED_STATUS_ID','0', 31, 7, NULL, NOW(), 'xtc_get_order_status_name' , 'xtc_cfg_pull_down_order_statuses(');

#configuration_group_id 40, Popup Window Configuration
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'POPUP_SHIPPING_LINK_PARAMETERS', '&KeepThis=true&TB_iframe=true&height=400&width=600', '40', '10', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'POPUP_SHIPPING_LINK_CLASS', 'thickbox', '40', '11', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'POPUP_CONTENT_LINK_PARAMETERS', '&KeepThis=true&TB_iframe=true&height=400&width=600', '40', '20', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'POPUP_CONTENT_LINK_CLASS', 'thickbox', '40', '21', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'POPUP_PRODUCT_LINK_PARAMETERS', '&KeepThis=true&TB_iframe=true&height=450&width=750', '40', '30', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'POPUP_PRODUCT_LINK_CLASS', 'thickbox', '40', '31', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'POPUP_COUPON_HELP_LINK_PARAMETERS', '&KeepThis=true&TB_iframe=true&height=400&width=600', '40', '40', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'POPUP_COUPON_HELP_LINK_CLASS', 'thickbox', '40', '41', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'POPUP_PRODUCT_PRINT_SIZE', 'width=640, height=600', '40', '60', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'POPUP_PRINT_ORDER_SIZE', 'width=640, height=600', '40', '70', NULL, NOW(), NULL, NULL);

# configuration_group_id 1000, Adminarea Options
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'PRICE_IS_BRUTTO', 'false', 1000, 10, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'PRICE_PRECISION', '4', 1000, 11, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'USE_ADMIN_TOP_MENU', 'true', 1000, 20, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'USE_ADMIN_LANG_TABS', 'true', 1000, 26, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id ,configuration_key ,configuration_value ,configuration_group_id ,sort_order ,last_modified ,date_added ,use_function ,set_function) VALUES (NULL, 'MAX_DISPLAY_ORDER_RESULTS', '30', '1000', '30', NULL , NOW(), NULL , NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'USE_ADMIN_THUMBS_IN_LIST', 'true', 1000, 32, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id ,configuration_key ,configuration_value ,configuration_group_id ,sort_order ,last_modified ,date_added ,use_function ,set_function) VALUES (NULL, 'USE_ADMIN_THUMBS_IN_LIST_STYLE', 'max-width:40px;max-height:40px;', '1000', '33', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MAX_DISPLAY_LIST_PRODUCTS', '50', '1000', '51', NULL , NOW(), NULL , NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MAX_DISPLAY_LIST_CUSTOMERS', '100', '1000', '52', NULL , NOW(), NULL , NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MAX_ROW_LISTS_ATTR_OPTIONS', '10', '1000', '53', NULL , NOW(), NULL , NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MAX_ROW_LISTS_ATTR_VALUES', '50', '1000', '54', NULL , NOW(), NULL , NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'WHOS_ONLINE_TIME_LAST_CLICK', '900', '1000', '60', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'WHOS_ONLINE_IP_WHOIS_SERVICE', 'http://www.utrace.de/?query=', '1000', '62', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'CONFIRM_SAVE_ENTRY', 'true', '1000', '70', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'USE_ADMIN_FIXED_TOP', 'true', '1000', '23', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'USE_ADMIN_FIXED_SEARCH', 'false', '1000', '24', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MAX_DISPLAY_STATS_RESULTS', '30', '1000', '55', NULL , NOW(), NULL , NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MAX_DISPLAY_COUPON_RESULTS', '30', '1000', '56', NULL , NOW(), NULL , NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ORDER_STATUSES_DISPLAY_DEFAULT', '', '1000', '90', NULL, NOW(), NULL, 'xtc_cfg_multi_checkbox(\'order_statuses\', \',\',');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ORDER_STATUSES_FOR_SALES_STATISTICS', '3', '1000', '100', NULL, NOW(), NULL, 'xtc_cfg_multi_checkbox(\'order_statuses\', \',\',');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'MIN_GROUP_PRICE_STAFFEL', '2', '1000', '34', NULL , NOW(), NULL , NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'USE_ATTRIBUTES_IFRAME', 'true', 1000, '110', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'NEW_ATTRIBUTES_STYLING', 'true', 1000, '112', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'NEW_SELECT_CHECKBOX', 'true', 1000, '113', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'CSRF_TOKEN_SYSTEM', 'true', 1000, '114', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ADMIN_HEADER_X_FRAME_OPTIONS', 'true', 1000, '115', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'ATTRIBUTE_MODEL_DELIMITER', '<br />', 1000, '116', NULL, NOW(), NULL, NULL);

INSERT INTO configuration_group VALUES (1,'My Store','General information about my store',1,1);
INSERT INTO configuration_group VALUES (2,'Minimum Values','The minimum values for functions / data',2,1);
INSERT INTO configuration_group VALUES (3,'Maximum Values','The maximum values for functions / data',3,1);
INSERT INTO configuration_group VALUES (4,'Images','Image parameters',4,1);
INSERT INTO configuration_group VALUES (5,'Customer Details','Customer account configuration',5,1);
INSERT INTO configuration_group VALUES (6,'Module Options','Hidden from configuration',6,0);
INSERT INTO configuration_group VALUES (7,'Shipping/Packaging','Shipping options available at my store',7,1);
INSERT INTO configuration_group VALUES (8,'Product Listing','Product Listing configuration options',8,1);
INSERT INTO configuration_group VALUES (9,'Stock','Stock configuration options',9,1);
INSERT INTO configuration_group VALUES (10,'Logging','Logging configuration options',10,1);
INSERT INTO configuration_group VALUES (11,'Cache','Caching configuration options',11,1);
INSERT INTO configuration_group VALUES (12,'E-Mail Options','General setting for E-Mail transport and HTML E-Mails',12,1);
INSERT INTO configuration_group VALUES (13,'Download','Downloadable products options',13,1);
INSERT INTO configuration_group VALUES (14,'GZip Compression','GZip compression options',14,1);
INSERT INTO configuration_group VALUES (15,'Sessions','Session options',15,1);
INSERT INTO configuration_group VALUES (16,'Meta-Tags/Search engines','Meta-tags/Search engines',16,1);
INSERT INTO configuration_group VALUES (17,'Additional Modules','Additional Modules',17,1);
INSERT INTO configuration_group VALUES (18,'Vat ID','Vat ID',18,1);
INSERT INTO configuration_group VALUES (19,'Google Conversion','Google Conversion-Tracking',19,1);
INSERT INTO configuration_group VALUES (20,'Import/Export','Import/Export',20,1);
INSERT INTO configuration_group VALUES (21,'Afterbuy','Afterbuy.de',21,1);
INSERT INTO configuration_group VALUES (22,'Search Options','Additional Options for search function',22,1);
INSERT INTO configuration_group VALUES (23,'Econda Tracking','Econda Tracking System',23,1);
INSERT INTO configuration_group VALUES (24,'PIWIK & Google Analytics Tracking','Settings for PIWIK & Google Analytics Tracking',24,1); 
INSERT INTO configuration_group VALUES (25,'Captcha','Captcha Configuration',25,1);
INSERT INTO configuration_group VALUES (31,'Moneybookers','Moneybookers System',31,1);
INSERT INTO configuration_group VALUES (40,'Popup Window Configuration','Popup Window Parameters',40,1);
INSERT INTO configuration_group VALUES (1000,'Adminarea Options','Adminarea Configuration', 1000,1);

# content manager - english
INSERT INTO content_manager VALUES (1, 0, 0, '', 1, 'Payment & Shipping', 'Payment & Shipping', 'Put here your Payment &amp; Shipping information.', 0, 1, '', 1, 1, 0, '', '', '', '', '1', 0, NOW(), NULL);
INSERT INTO content_manager VALUES (2, 0, 0, '', 1, 'Privacy Notice', 'Privacy Notice', 'Put here your Privacy Notice information.', 0, 1, '', 1, 2, 0, '', '', '', '', '1', 0, NOW(), NULL);
INSERT INTO content_manager VALUES (3, 0, 0, '', 1, 'Conditions of Use', 'Conditions of Use', '<strong>Conditions of Use</strong><br /><br />Put here your Conditions of Use information.<br /><br /><ol><li>Scope of Application</li><li>Contract partner</li><li>Conclusion of the Contract</li><li>Right to cancel</li><li>Price and Delivery Costs</li><li>Shipment and delivery conditions</li><li>Payment methods</li><li>Reservation of title</li><li>Warranty</li><li>Information about online dispute resolution</li></ol>...<br />...<br />...<h2>Information about online dispute resolution</h2><p>The EU Commission provides on its website the following link to the ODR platform: http://ec.europa.eu/consumers/odr.</p><p>This platform shall be a point of entry for out-of-court resolutions of disputes arising from online sales and service contracts concluded between consumers and traders.</p><h2>Further informations</h2>...<br />...<br />...', 0, 1, '', 1, 3, 0, '', '', '', '', '1', 0, NOW(), NULL);
INSERT INTO content_manager VALUES (4, 0, 0, '', 1, 'Imprint', 'Imprint', 'Put here your Company information.<br /><br />DemoShop GmbH<br />Managing director: Max Muster und Fritz Beispiel<br /><br />Max Muster Stra&szlig;e 21-23<br />D-0815 Musterhausen<br />E-Mail: max.muster@muster.de<br /><br />HRB 123456<br />Amtsgericht Musterhausen<br />VAT ID No.: DE 000 111 222<br /><br />Platform of the EU Commission regarding online dispute resolution: <a href="http//ec.europa.eu/consumers/odr" target="_blank">http//ec.europa.eu/consumers/odr</a>', 0, 1, '', 1, 4, 0, '', '', '', '', '1', 0, NOW(), NULL);
INSERT INTO content_manager VALUES (5, 0, 0, '', 1, 'Index', 'Welcome', '{$greeting}<br /><br />This is the default installation of <strong><span style="color:#B0347E;">mod</span><span style="color:#6D6D6D;">ified eCommerce Shopsoftware</span></strong>. All products shown are for demonstrational purposes. If you order products, they will be not be delivered nor billed.<br /><br />Should you be interested in the program, which forms the basis for this store, so please visit the website of <a href="http://www.modified-shop.org" target="_blank"><u><strong><span style="color:#B0347E;">mod</span><span style="color:#6D6D6D;">ified eCommerce Shopsoftware</span></strong></u></a>.<br /><br />The text shown here may be edited in the admin area under <b>Content Manager</b> - entry Index.', 0, 1, '', 0, 5, 0, '', '', '', '', '1', 0, NOW(), NULL);
INSERT INTO content_manager VALUES (6, 0, 0, '', 1, 'Coupons', 'Coupons FAQ', '<table cellSpacing="0" cellPadding="0">\r\n<tbody>\r\n<tr>\r\n<td class="main"><strong>Buy Gift Vouchers/Coupons </strong></td></tr>\r\n<tr>\r\n<td class="main">If the shop provided gift vouchers or coupons, You can buy them alike all other products. As soon as You have bought and payed the coupon, the shop system will activate Your coupon. You will then see the coupon amount in Your shopping cart. Then You can send the coupon via e-mail by clicking the link "Send Coupon". </td></tr></tbody></table>\r\n<table cellSpacing="0" cellPadding="0">\r\n<tbody>\r\n<tr>\r\n<td class="main"><strong>How to dispatch Coupons </strong></td></tr>\r\n<tr>\r\n<td class="main">To dispatch a coupon, please click the link "Send Coupon" in Your shopping cart. To send the coupon to the correct person, we need the following details: Surname and realname of the recipient and a valid e-mail adress of the recipient, and the desired coupon amount (You can also use only parts of Your balance). Please provide also a short message for the recipient. Please check those information again before You click the "Send Coupon" button. You can change all information at any time before clicking the "Send Coupon" button. </td></tr></tbody></table>\r\n<table cellSpacing="0" cellPadding="0">\r\n<tbody>\r\n<tr>\r\n<td class="main"><strong>How to use Coupons to buy products. </strong></td></tr>\r\n<tr>\r\n<td class="main">As soon as You have a balance, You can use it to pay for Your orders. During the checkout process, You can redeem Your coupon. In case Your balance is less than the value of goods You ordered, You would have to choose Your preferred method of payment for the difference amount. In case Your balance is more than the value of goods You ordered, the remaining amount of Your balance will be saved for Your next order. </td></tr></tbody></table>\r\n<table cellSpacing="0" cellPadding="0">\r\n<tbody>\r\n<tr>\r\n<td class="main"><strong>How to redeem Coupons. </strong></td></tr>\r\n<tr>\r\n<td class="main">In case You have received a coupun via e-mail, You can: <br />1. Click on the link provided in the e-mail. If You do not have an account in this shop already, please create a personal account. <br />2. After having added a product to Your shopping cart, You can enter Your coupon code.</td></tr></tbody></table>\r\n<table cellSpacing="0" cellPadding="0">\r\n<tbody>\r\n<tr>\r\n<td class="main"><strong>Problems?</strong></td></tr>\r\n<tr>\r\n<td class="main">If You have trouble or problems in using Your coupons, please check back with us via our e-mail: you@yourdomain.com. Please describe the encountered problem as detailed as possible! We need the following information to process Your request quickly: Your user id, the coupon code, error messages the shop system returned to You, and the name of the web browser You are using (e.g. "Internet Explorer 6" or "Firefox 1.5"). </td></tr></tbody></table>', 0, 1, '', 0, 6, 1, '', '', '', '', '1', 0, NOW(), NULL);
INSERT INTO content_manager VALUES (7, 0, 0, '', 1, 'Contact', 'Contact', 'Please enter your contact information.', 0, 1, '', 1, 7, 0, '', '', '', '', '1', 0, NOW(), NULL);
INSERT INTO content_manager VALUES (8, 0, 0, '', 1, 'Sitemap', '', '', 0, 0, 'sitemap.php', 1, 8, 0, '', '', '', '', '1', 0, NOW(), NULL);
INSERT INTO content_manager VALUES (9, 0, 0, '', 1, 'Right of revocation & revocation form', 'Right of revocation & revocation form', '<p><strong>Right of revocation<br /></strong><br />Add your right of revocation here.</p><p><strong>Revocation form</strong><br /><br />(Complete and return this form only if you wish to withdraw from the contract.)<br /><br />To<br />Max Mustermann / Muster GmbH<br />Musterstra&szlig;e 11<br />66666 Musterstadt<br />Fax: 000-777777<br />E-Mail:info@muster.de<br /><br />[enter the name, address and if appropriate, fax number and e-mail-address of the entrepreneur by the entrepreneur]:<br /><br />I/We* hereby give notice that I/We (*) withdraw from my/our (*) contract of sale of the following goods (*) / provision of the following service (*)<br />_______________________________________________<br />_______________________________________________<br /><br />Ordered on ___________________ (*)/received on _______________________(*)<br /><br />Name of the consumer(s) ______________________________________<br />Address of the consumer(s)<br />_________________________________<br />_________________________________<br />_________________________________<br /><br />_________&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; _____________________________________________________<br />Date&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; signature of the consumer(s) (only with message on paper)<br /><br />_____________________________________________________________________________________<br />(*) delete as applicable</p>', 0, 1, '', 1, 9, 0, '', '', '', '', '1', 0, NOW(), NULL);
INSERT INTO content_manager VALUES (10, 0, 0, '', 1, 'Delivery time', 'Delivery time', 'The deadline for delivery begins when paying in advance on the day after the payment order to the remitting bank or for other payments on the day to run after the conclusion and ends with the expiry of the last day of the period. Falls on a Saturday, Sunday or a public holiday delivery nationally recognized, the last day of the period, as occurs, the next business day at the place of such a day.', 0, 1, '', 1, 10, 0, '', '', '', '', '1', 0, NOW(), NULL);
INSERT INTO content_manager VALUES (11, 0, 0, '', 1, 'E-Mail Signature', '', '<b>Company</b><br />Address<br />Location<br />Homepage<br />E-mail:<br />Phone:<br />Fax:<br />CEO:<br />VAT Reg No:', 0, 1, '', 0, 11, 0, '', '', '', '', '0', 0, NOW(), NULL);
INSERT INTO content_manager VALUES (12, 0, 0, '', 1, 'Invoice data', 'Company - Address - Code City', 'Company<br/>Address<br/>Code City<br/><br/>Phone: 0123456789<br/>E-Mail: info@shop.de<br/>www: www.shopurl.de<br/><br/>IBAN: DE123456789011<br/>BIC: BYLEMDNE1DE<br/><br/>You can change this in the content manager.', 0, 1, '', 0, 12, 0, '', '', '', '', '0', 0, NOW(), NULL);

# content manager - german
INSERT INTO content_manager VALUES (13, 0, 0, '', 2, 'Zahlung & Versand', 'Zahlung & Versand', 'F&uuml;gen Sie hier Ihre Informationen &uuml;ber Zahlung &amp; Versand ein.', 0, 1, '', 1, 1, 0, '', '', '', '', '1', 0, NOW(), NULL);
INSERT INTO content_manager VALUES (14, 0, 0, '', 2, 'Privatsphäre und Datenschutz', 'Privatsphäre und Datenschutz', 'F&uuml;gen Sie hier Ihre Informationen &uuml;ber Privatsph&auml;re und Datenschutz ein.', 0, 1, '', 1, 2, 0, '', '', '', '', '1', 0, NOW(), NULL);
INSERT INTO content_manager VALUES (15, 0, 0, '', 2, 'Unsere AGB', 'Allgemeine Geschäftsbedingungen', '<strong>Allgemeine Gesch&auml;ftsbedingungen</strong><br /><br />F&uuml;gen Sie hier Ihre allgemeinen Gesch&auml;ftsbedingungen ein.<br /><br /><ol><li>Geltungsbereich</li><li>Vertragspartner</li><li>Angebot und Vertragsschluss</li><li>Widerrufsrecht, Widerrufsbelehrung, Widerrufsfolgen</li><li>Preise und Versandkosten</li><li>Lieferung</li><li>Zahlung</li><li>Eigentumsvorbehalt</li><li>Gew&auml;hrleistung</li><li>Informationen zur Online-Streitbeilegung</li></ol>...<br />...<br />...<h2>Informationen zur Online-Streitbeilegung</h2><p>Die EU-Kommission stellt im Internet unter folgendem Link eine Plattform zur Online-Streitbeilegung bereit: http://ec.europa.eu/consumers/odr</p><p>Diese Plattform dient als Anlaufstelle zur au&szlig;ergerichtlichen Beilegung von Streitigkeiten aus Online-Kauf- oder Dienstleistungsvertr&auml;gen, an denen ein Verbraucher beteiligt ist.</p><h2>Weitere Informationen</h2>...<br />...<br />...', 0, 1, '', 1, 3, 0, '', '', '', '', '1', 0, NOW(), NULL);
INSERT INTO content_manager VALUES (16, 0, 0, '', 2, 'Impressum', 'Impressum', 'F&uuml;gen Sie hier Ihr Impressum ein.<br /><br />DemoShop GmbH<br />Gesch&auml;ftsf&uuml;hrer: Max Muster und Fritz Beispiel<br /><br />Max Muster Stra&szlig;e 21-23<br />D-0815 Musterhausen<br />E-Mail: max.muster@muster.de<br /><br />HRB 123456<br />Amtsgericht Musterhausen<br />UStid-Nr.: DE 000 111 222<br /><br />Plattform der EU-Kommission zur Online-Streitbeilegung: <a href="http//ec.europa.eu/consumers/odr" target="_blank">http//ec.europa.eu/consumers/odr</a>', 0, 1, '', 1, 4, 0, '', '', '', '', '1', 0, NOW(), NULL);
INSERT INTO content_manager VALUES (17, 0, 0, '', 2, 'Index', 'Willkommen', '{$greeting}<br /><br />Dies ist die Standardinstallation der <strong><span style="color:#B0347E;">mod</span><span style="color:#6D6D6D;">ified eCommerce Shopsoftware</span></strong>. Alle dargestellten Produkte dienen zur Demonstration der Funktionsweise. Wenn Sie Produkte bestellen, so werden diese weder ausgeliefert, noch in Rechnung gestellt.<br /><br />Sollten Sie daran interessiert sein das Programm, welches die Grundlage f&uuml;r diesen Shop bildet, einzusetzen, so besuchen Sie bitte die Webseite der <a href="http://www.modified-shop.org" target="_blank"><u><strong><span style="color:#B0347E;">mod</span><span style="color:#6D6D6D;">ified eCommerce Shopsoftware</span></strong></u></a>.<br /><br />Der hier dargestellte Text kann im Adminbereich unter <b>Content Manager</b> - Eintrag Index bearbeitet werden.', 0, 1, '', 0, 5, 0, '', '', '', '', '1', 0, NOW(), NULL);
INSERT INTO content_manager VALUES (18, 0, 0, '', 2, 'Gutscheine', 'Gutscheine - Fragen und Antworten', '<table cellSpacing="0" cellPadding="0">\r\n<tbody>\r\n<tr>\r\n<td class="main"><strong>Gutscheine kaufen </strong></td></tr>\r\n<tr>\r\n<td class="main">Gutscheine k&ouml;nnen, falls sie im Shop angeboten werden, wie normale Artikel gekauft werden. Sobald Sie einen Gutschein gekauft haben und dieser nach erfolgreicher Zahlung freigeschaltet wurde, erscheint der Betrag unter Ihrem Warenkorb. Nun können Sie über den Link " Gutschein versenden " den gewünschten Betrag per E-Mail versenden.</td></tr></tbody></table>\r\n<table cellSpacing="0" cellPadding="0">\r\n<tbody>\r\n<tr>\r\n<td class="main"><strong>Wie man Gutscheine versendet</strong></td></tr>\r\n<tr>\r\n<td class="main">Um einen Gutschein zu versenden, klicken Sie bitte auf den Link "Gutschein versenden" in Ihrem Einkaufskorb. Um einen Gutschein zu versenden, benötigen wir folgende Angaben von Ihnen: Vor- und Nachname des Empfängers. Eine gültige E-Mail Adresse des Empfängers. Den gewünschten Betrag (Sie können auch Teilbeträge Ihres Guthabens versenden). Eine kurze Nachricht an den Empfänger. Bitte überprüfen Sie Ihre Angaben noch einmal vor dem Versenden. Sie haben vor dem Versenden jederzeit die Möglichkeit Ihre Angaben zu korrigieren.</td></tr></tbody></table>\r\n<table cellSpacing="0" cellPadding="0">\r\n<tbody>\r\n<tr>\r\n<td class="main"><strong>Mit Gutscheinen einkaufen.</strong></td></tr>\r\n<tr>\r\n<td class="main">Sobald Sie über ein Guthaben verfügen, können Sie dieses zum Bezahlen Ihrer Bestellung verwenden. Während des Bestellvorganges haben Sie die Möglichkeit Ihr Guthaben einzulösen. Falls das Guthaben unter dem Warenwert liegt müssen Sie Ihre bevorzugte Zahlungsweise für den Differenzbetrag wählen. Übersteigt Ihr Guthaben den Warenwert, steht Ihnen das Restguthaben selbstverständlich für Ihre nächste Bestellung zur Verfügung.</td></tr></tbody></table>\r\n<table cellSpacing="0" cellPadding="0">\r\n<tbody>\r\n<tr>\r\n<td class="main"><strong>Gutscheine verbuchen. </strong></td></tr>\r\n<tr>\r\n<td class="main">Wenn Sie einen Gutschein per E-Mail erhalten haben, können Sie den Betrag wie folgt verbuchen: <br />1. Klicken Sie auf den in der E-Mail angegebenen Link. Falls Sie noch nicht über ein persönliches Kundenkonto verfügen, haben Sie die Möglichkeit ein Konto zu eröffnen. <br />2. Nachdem Sie ein Produkt in den Warenkorb gelegt haben, können Sie dort Ihren Gutscheincode eingeben.</td></tr></tbody></table>\r\n<table cellSpacing="0" cellPadding="0">\r\n<tbody>\r\n<tr>\r\n<td class="main"><strong>Falls es zu Problemen kommen sollte:</strong></td></tr>\r\n<tr>\r\n<td class="main">Falls es wider Erwarten zu Problemen mit einem Gutschein kommen sollte, kontaktieren Sie uns bitte per E-Mail: you@yourdomain.com. Bitte beschreiben Sie möglichst genau das Problem, wichtige Angaben sind unter anderem: Ihre Kundennummer, der Gutscheincode, Fehlermeldungen des Systems sowie der von Ihnen benutzte Browser (z.B. "Internet Explorer 6" oder "Firefox 1.5"). </td></tr></tbody></table>', 0, 1, '', 0, 6, 1, '', '', '', '', '1', 0, NOW(), NULL);
INSERT INTO content_manager VALUES (19, 0, 0, '', 2, 'Kontakt', 'Kontakt', 'Ihre Kontaktinformationen', 0, 1, '', 1, 7, 0, '', '', '', '', '1', 0, NOW(), NULL);
INSERT INTO content_manager VALUES (20, 0, 0, '', 2, 'Sitemap', '', '', 0, 0, 'sitemap.php', 1, 8, 0, '', '', '', '', '1', 0, NOW(), NULL);
INSERT INTO content_manager VALUES (21, 0, 0, '', 2, 'Widerrufsrecht & Widerrufsformular', 'Widerrufsrecht & Widerrufsformular', '<p><strong>Widerrufsrecht<br /></strong><br />F&uuml;gen Sie hier das Widerrufsrecht ein.</p><p><strong>Widerrufsformular</strong><br /><br />(Wenn Sie den Vertrag widerrufen wollen, dann f&uuml;llen Sie bitte dieses Formular aus und senden Sie es zur&uuml;ck.)<br /><br />An<br />Max Mustermann / Muster GmbH<br />Musterstra&szlig;e 11<br />66666 Musterstadt<br />Fax: 000-777777<br />E-Mail:info@muster.de<br /><br />[hier ist der Name, die Anschrift und gegebenenfalls die Telefaxnummer und E-Mail-Adresse des Unternehmers durch den Unternehmer einzuf&uuml;gen]:<br /><br />Hiermit widerrufe(n) ich/wir (*) den von mir/uns (*) abgeschlossenen Vertrag &uuml;ber den Kauf der folgenden Waren (*) / die Erbringung der folgenden Dienstleistung (*)<br />_______________________________________________<br />_______________________________________________<br /><br />Bestellt am ___________________ (*)/erhalten am _______________________(*)<br /><br />Name des/der Verbraucher(s) ______________________________________<br />Anschrift des/der Verbraucher(s)<br />_________________________________<br />_________________________________<br />_________________________________<br /><br />_________&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; _____________________________________________________<br />Datum&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Unterschrift des/der Verbraucher(s) (nur bei Mitteilung auf Papier)<br /><br />_____________________________________________________________________________________<br />(*) Unzutreffendes streichen</p>', 0, 1, '', 1, 9, 0, '', '', '', '', '1', 0, NOW(), NULL);
INSERT INTO content_manager VALUES (22, 0, 0, '', 2, 'Lieferzeit', 'Lieferzeit', 'Die Frist f&uuml;r die Lieferung beginnt bei Zahlung per Vorkasse am Tag nach Erteilung des Zahlungsauftrags an das &uuml;berweisende Kreditinstitut bzw. bei anderen Zahlungsarten am Tag nach Vertragsschluss zu laufen und endet mit dem Ablauf des letzten Tages der Frist. F&auml;llt der letzte Tag der Frist auf einen Samstag, Sonntag oder einen am Lieferort staatlich anerkannten allgemeinen Feiertag, so tritt an die Stelle eines solchen Tages der n&auml;chste Werktag.', 0, 1, '', 1, 10, 0, '', '', '', '', '1', 0, NOW(), NULL);
INSERT INTO content_manager VALUES (23, 0, 0, '', 2, 'E-Mail Signatur', '', 'Firma<br />Adresse<br />Ort<br />Homepage<br />E-Mail:<br />Fon:<br />Fax:<br />USt-IdNr.:<br />Handelsregister<br />Gesch&auml;ftsf&uuml;hrer:', 0, 1, '', 0, 11, 0, '', '', '', '', '0', 0, NOW(), NULL);
INSERT INTO content_manager VALUES (24, 0, 0, '', 2, 'Rechnungsdaten', 'Firma - Adresse - PLZ Stadt', 'Firma<br/>Adresse<br/>PLZ Stadt<br/><br/>Tel: 0123456789<br/>E-Mail: info@shop.de<br/>www: www.shopurl.de<br/><br/>IBAN: DE123456789011<br/>BIC: BYLEMDNE1DE<br/><br/>Diese Daten k&ouml;nnen im Content Manager ge&auml;ndert werden.', 0, 1, '', 0, 12, 0, '', '', '', '', '0', 0, NOW(), NULL);

# countries
INSERT INTO countries VALUES (1,'Afghanistan','AF','AFG',1,1,0);
INSERT INTO countries VALUES (2,'Albania','AL','ALB',1,1,0);
INSERT INTO countries VALUES (3,'Algeria','DZ','DZA',1,1,0);
INSERT INTO countries VALUES (4,'American Samoa','AS','ASM',1,1,0);
INSERT INTO countries VALUES (5,'Andorra','AD','AND',1,1,0);
INSERT INTO countries VALUES (6,'Angola','AO','AGO',1,1,0);
INSERT INTO countries VALUES (7,'Anguilla','AI','AIA',1,1,0);
INSERT INTO countries VALUES (8,'Antarctica','AQ','ATA',1,1,0);
INSERT INTO countries VALUES (9,'Antigua and Barbuda','AG','ATG',1,1,0);
INSERT INTO countries VALUES (10,'Argentina','AR','ARG',1,1,0);
INSERT INTO countries VALUES (11,'Armenia','AM','ARM',1,1,0);
INSERT INTO countries VALUES (12,'Aruba','AW','ABW',1,1,0);
INSERT INTO countries VALUES (13,'Australia','AU','AUD',1,1,0);
INSERT INTO countries VALUES (14,'Austria','AT','AUT',5,1,0);
INSERT INTO countries VALUES (15,'Azerbaijan','AZ','AZE',1,1,0);
INSERT INTO countries VALUES (16,'Bahamas','BS','BHS',1,1,0);
INSERT INTO countries VALUES (17,'Bahrain','BH','BHR',1,1,0);
INSERT INTO countries VALUES (18,'Bangladesh','BD','BGD',1,1,0);
INSERT INTO countries VALUES (19,'Barbados','BB','BRB',1,1,0);
INSERT INTO countries VALUES (20,'Belarus','BY','BLR',1,1,0);
INSERT INTO countries VALUES (21,'Belgium','BE','BEL',1,1,0);
INSERT INTO countries VALUES (22,'Belize','BZ','BLZ',1,1,0);
INSERT INTO countries VALUES (23,'Benin','BJ','BEN',1,1,0);
INSERT INTO countries VALUES (24,'Bermuda','BM','BMU',1,1,0);
INSERT INTO countries VALUES (25,'Bhutan','BT','BTN',1,1,0);
INSERT INTO countries VALUES (26,'Bolivia','BO','BOL',1,1,0);
INSERT INTO countries VALUES (27,'Bosnia and Herzegowina','BA','BIH',1,1,0);
INSERT INTO countries VALUES (28,'Botswana','BW','BWA',1,1,0);
INSERT INTO countries VALUES (29,'Bouvet Island','BV','BVT',1,1,0);
INSERT INTO countries VALUES (30,'Brazil','BR','BRA',1,1,0);
INSERT INTO countries VALUES (31,'British Indian Ocean Territory','IO','IOT',1,1,0);
INSERT INTO countries VALUES (32,'Brunei Darussalam','BN','BRN',1,1,0);
INSERT INTO countries VALUES (33,'Bulgaria','BG','BGR',1,1,0);
INSERT INTO countries VALUES (34,'Burkina Faso','BF','BFA',1,1,0);
INSERT INTO countries VALUES (35,'Burundi','BI','BDI',1,1,0);
INSERT INTO countries VALUES (36,'Cambodia','KH','KHM',1,1,0);
INSERT INTO countries VALUES (37,'Cameroon','CM','CMR',1,1,0);
INSERT INTO countries VALUES (38,'Canada','CA','CAN',1,1,0);
INSERT INTO countries VALUES (39,'Cape Verde','CV','CPV',1,1,0);
INSERT INTO countries VALUES (40,'Cayman Islands','KY','CYM',1,1,0);
INSERT INTO countries VALUES (41,'Central African Republic','CF','CAF',1,1,0);
INSERT INTO countries VALUES (42,'Chad','TD','TCD',1,1,0);
INSERT INTO countries VALUES (43,'Chile','CL','CHL',1,1,0);
INSERT INTO countries VALUES (44,'China','CN','CHN',7,1,0);
INSERT INTO countries VALUES (45,'Christmas Island','CX','CXR',1,1,0);
INSERT INTO countries VALUES (46,'Cocos (Keeling) Islands','CC','CCK',1,1,0);
INSERT INTO countries VALUES (47,'Colombia','CO','COL',1,1,0);
INSERT INTO countries VALUES (48,'Comoros','KM','COM',1,1,0);
INSERT INTO countries VALUES (49,'Congo','CG','COG',1,1,0);
INSERT INTO countries VALUES (50,'Cook Islands','CK','COK',1,1,0);
INSERT INTO countries VALUES (51,'Costa Rica','CR','CRI',1,1,0);
INSERT INTO countries VALUES (52,'Cote D\'Ivoire','CI','CIV',1,1,0);
INSERT INTO countries VALUES (53,'Croatia','HR','HRV',1,1,0);
INSERT INTO countries VALUES (54,'Cuba','CU','CUB',1,1,0);
INSERT INTO countries VALUES (55,'Cyprus','CY','CYP',1,1,0);
INSERT INTO countries VALUES (56,'Czech Republic','CZ','CZE',1,1,0);
INSERT INTO countries VALUES (57,'Denmark','DK','DNK',1,1,0);
INSERT INTO countries VALUES (58,'Djibouti','DJ','DJI',1,1,0);
INSERT INTO countries VALUES (59,'Dominica','DM','DMA',1,1,0);
INSERT INTO countries VALUES (60,'Dominican Republic','DO','DOM',1,1,0);
INSERT INTO countries VALUES (61,'East Timor','TP','TMP',1,1,0);
INSERT INTO countries VALUES (62,'Ecuador','EC','ECU',1,1,0);
INSERT INTO countries VALUES (63,'Egypt','EG','EGY',1,1,0);
INSERT INTO countries VALUES (64,'El Salvador','SV','SLV',1,1,0);
INSERT INTO countries VALUES (65,'Equatorial Guinea','GQ','GNQ',1,1,0);
INSERT INTO countries VALUES (66,'Eritrea','ER','ERI',1,1,0);
INSERT INTO countries VALUES (67,'Estonia','EE','EST',1,1,0);
INSERT INTO countries VALUES (68,'Ethiopia','ET','ETH',1,1,0);
INSERT INTO countries VALUES (69,'Falkland Islands (Malvinas)','FK','FLK',1,1,0);
INSERT INTO countries VALUES (70,'Faroe Islands','FO','FRO',1,1,0);
INSERT INTO countries VALUES (71,'Fiji','FJ','FJI',1,1,0);
INSERT INTO countries VALUES (72,'Finland','FI','FIN',1,1,0);
INSERT INTO countries VALUES (73,'France','FR','FRA',1,1,0);
#INSERT INTO countries VALUES (74,'France, Metropolitan','FX','FXX',1,1,0);
INSERT INTO countries VALUES (75,'French Guiana','GF','GUF',1,1,0);
INSERT INTO countries VALUES (76,'French Polynesia','PF','PYF',1,1,0);
INSERT INTO countries VALUES (77,'French Southern Territories','TF','ATF',1,1,0);
INSERT INTO countries VALUES (78,'Gabon','GA','GAB',1,1,0);
INSERT INTO countries VALUES (79,'Gambia','GM','GMB',1,1,0);
INSERT INTO countries VALUES (80,'Georgia','GE','GEO',1,1,0);
INSERT INTO countries VALUES (81,'Germany','DE','DEU',5,1,0);
INSERT INTO countries VALUES (82,'Ghana','GH','GHA',1,1,0);
INSERT INTO countries VALUES (83,'Gibraltar','GI','GIB',1,1,0);
INSERT INTO countries VALUES (84,'Greece','GR','GRC',1,1,0);
INSERT INTO countries VALUES (85,'Greenland','GL','GRL',1,1,0);
INSERT INTO countries VALUES (86,'Grenada','GD','GRD',1,1,0);
INSERT INTO countries VALUES (87,'Guadeloupe','GP','GLP',1,1,0);
INSERT INTO countries VALUES (88,'Guam','GU','GUM',1,1,0);
INSERT INTO countries VALUES (89,'Guatemala','GT','GTM',1,1,0);
INSERT INTO countries VALUES (90,'Guinea','GN','GIN',1,1,0);
INSERT INTO countries VALUES (91,'Guinea-bissau','GW','GNB',1,1,0);
INSERT INTO countries VALUES (92,'Guyana','GY','GUY',1,1,0);
INSERT INTO countries VALUES (93,'Haiti','HT','HTI',1,1,0);
INSERT INTO countries VALUES (94,'Heard and Mc Donald Islands','HM','HMD',1,1,0);
INSERT INTO countries VALUES (95,'Honduras','HN','HND',1,1,0);
INSERT INTO countries VALUES (96,'Hong Kong','HK','HKG',1,1,0);
INSERT INTO countries VALUES (97,'Hungary','HU','HUN',1,1,0);
INSERT INTO countries VALUES (98,'Iceland','IS','ISL',1,1,0);
INSERT INTO countries VALUES (99,'India','IN','IND',1,1,0);
INSERT INTO countries VALUES (100,'Indonesia','ID','IDN',1,1,0);
INSERT INTO countries VALUES (101,'Iran (Islamic Republic of)','IR','IRN',1,1,0);
INSERT INTO countries VALUES (102,'Iraq','IQ','IRQ',1,1,0);
INSERT INTO countries VALUES (103,'Ireland','IE','IRL',6,1,0);
INSERT INTO countries VALUES (104,'Israel','IL','ISR',1,1,0);
INSERT INTO countries VALUES (105,'Italy','IT','ITA',1,1,0);
INSERT INTO countries VALUES (106,'Jamaica','JM','JAM',1,1,0);
INSERT INTO countries VALUES (107,'Japan','JP','JPN',1,1,0);
INSERT INTO countries VALUES (108,'Jordan','JO','JOR',1,1,0);
INSERT INTO countries VALUES (109,'Kazakhstan','KZ','KAZ',1,1,0);
INSERT INTO countries VALUES (110,'Kenya','KE','KEN',1,1,0);
INSERT INTO countries VALUES (111,'Kiribati','KI','KIR',1,1,0);
INSERT INTO countries VALUES (112,'Korea, Democratic People\'s Republic of','KP','PRK',1,1,0);
INSERT INTO countries VALUES (113,'Korea, Republic of','KR','KOR',1,1,0);
INSERT INTO countries VALUES (114,'Kuwait','KW','KWT',1,1,0);
INSERT INTO countries VALUES (115,'Kyrgyzstan','KG','KGZ',1,1,0);
INSERT INTO countries VALUES (116,'Lao People\'s Democratic Republic','LA','LAO',1,1,0);
INSERT INTO countries VALUES (117,'Latvia','LV','LVA',1,1,0);
INSERT INTO countries VALUES (118,'Lebanon','LB','LBN',1,1,0);
INSERT INTO countries VALUES (119,'Lesotho','LS','LSO',1,1,0);
INSERT INTO countries VALUES (120,'Liberia','LR','LBR',1,1,0);
INSERT INTO countries VALUES (121,'Libyan Arab Jamahiriya','LY','LBY',1,1,0);
INSERT INTO countries VALUES (122,'Liechtenstein','LI','LIE',1,1,0);
INSERT INTO countries VALUES (123,'Lithuania','LT','LTU',1,1,0);
INSERT INTO countries VALUES (124,'Luxembourg','LU','LUX',5,1,0);
INSERT INTO countries VALUES (125,'Macau','MO','MAC',1,1,0);
INSERT INTO countries VALUES (126,'Macedonia, The Former Yugoslav Republic of','MK','MKD',1,1,0);
INSERT INTO countries VALUES (127,'Madagascar','MG','MDG',1,1,0);
INSERT INTO countries VALUES (128,'Malawi','MW','MWI',1,1,0);
INSERT INTO countries VALUES (129,'Malaysia','MY','MYS',1,1,0);
INSERT INTO countries VALUES (130,'Maldives','MV','MDV',1,1,0);
INSERT INTO countries VALUES (131,'Mali','ML','MLI',1,1,0);
INSERT INTO countries VALUES (132,'Malta','MT','MLT',1,1,0);
INSERT INTO countries VALUES (133,'Marshall Islands','MH','MHL',1,1,0);
INSERT INTO countries VALUES (134,'Martinique','MQ','MTQ',1,1,0);
INSERT INTO countries VALUES (135,'Mauritania','MR','MRT',1,1,0);
INSERT INTO countries VALUES (136,'Mauritius','MU','MUS',1,1,0);
INSERT INTO countries VALUES (137,'Mayotte','YT','MYT',1,1,0);
INSERT INTO countries VALUES (138,'Mexico','MX','MEX',1,1,0);
INSERT INTO countries VALUES (139,'Micronesia, Federated States of','FM','FSM',1,1,0);
INSERT INTO countries VALUES (140,'Moldova, Republic of','MD','MDA',1,1,0);
INSERT INTO countries VALUES (141,'Monaco','MC','MCO',1,1,0);
INSERT INTO countries VALUES (142,'Mongolia','MN','MNG',1,1,0);
INSERT INTO countries VALUES (143,'Montserrat','MS','MSR',1,1,0);
INSERT INTO countries VALUES (144,'Morocco','MA','MAR',1,1,0);
INSERT INTO countries VALUES (145,'Mozambique','MZ','MOZ',1,1,0);
INSERT INTO countries VALUES (146,'Myanmar','MM','MMR',1,1,0);
INSERT INTO countries VALUES (147,'Namibia','NA','NAM',1,1,0);
INSERT INTO countries VALUES (148,'Nauru','NR','NRU',1,1,0);
INSERT INTO countries VALUES (149,'Nepal','NP','NPL',1,1,0);
INSERT INTO countries VALUES (150,'Netherlands','NL','NLD',1,1,0);
INSERT INTO countries VALUES (151,'Netherlands Antilles','AN','ANT',1,1,0);
INSERT INTO countries VALUES (152,'New Caledonia','NC','NCL',1,1,0);
INSERT INTO countries VALUES (153,'New Zealand','NZ','NZL',1,1,0);
INSERT INTO countries VALUES (154,'Nicaragua','NI','NIC',1,1,0);
INSERT INTO countries VALUES (155,'Niger','NE','NER',1,1,0);
INSERT INTO countries VALUES (156,'Nigeria','NG','NGA',1,1,0);
INSERT INTO countries VALUES (157,'Niue','NU','NIU',1,1,0);
INSERT INTO countries VALUES (158,'Norfolk Island','NF','NFK',1,1,0);
INSERT INTO countries VALUES (159,'Northern Mariana Islands','MP','MNP',1,1,0);
INSERT INTO countries VALUES (160,'Norway','NO','NOR',1,1,0);
INSERT INTO countries VALUES (161,'Oman','OM','OMN',1,1,0);
INSERT INTO countries VALUES (162,'Pakistan','PK','PAK',1,1,0);
INSERT INTO countries VALUES (163,'Palau','PW','PLW',1,1,0);
INSERT INTO countries VALUES (164,'Panama','PA','PAN',1,1,0);
INSERT INTO countries VALUES (165,'Papua New Guinea','PG','PNG',1,1,0);
INSERT INTO countries VALUES (166,'Paraguay','PY','PRY',1,1,0);
INSERT INTO countries VALUES (167,'Peru','PE','PER',1,1,0);
INSERT INTO countries VALUES (168,'Philippines','PH','PHL',1,1,0);
INSERT INTO countries VALUES (169,'Pitcairn','PN','PCN',1,1,0);
INSERT INTO countries VALUES (170,'Poland','PL','POL',1,1,0);
INSERT INTO countries VALUES (171,'Portugal','PT','PRT',1,1,0);
INSERT INTO countries VALUES (172,'Puerto Rico','PR','PRI',1,1,0);
INSERT INTO countries VALUES (173,'Qatar','QA','QAT',1,1,0);
INSERT INTO countries VALUES (174,'Reunion','RE','REU',1,1,0);
INSERT INTO countries VALUES (175,'Romania','RO','ROM',1,1,0);
INSERT INTO countries VALUES (176,'Russian Federation','RU','RUS',1,1,0);
INSERT INTO countries VALUES (177,'Rwanda','RW','RWA',1,1,0);
INSERT INTO countries VALUES (178,'Saint Kitts and Nevis','KN','KNA',1,1,0);
INSERT INTO countries VALUES (179,'Saint Lucia','LC','LCA',1,1,0);
INSERT INTO countries VALUES (180,'Saint Vincent and the Grenadines','VC','VCT',1,1,0);
INSERT INTO countries VALUES (181,'Samoa','WS','WSM',1,1,0);
INSERT INTO countries VALUES (182,'San Marino','SM','SMR',1,1,0);
INSERT INTO countries VALUES (183,'Sao Tome and Principe','ST','STP',1,1,0);
INSERT INTO countries VALUES (184,'Saudi Arabia','SA','SAU',1,1,0);
INSERT INTO countries VALUES (185,'Senegal','SN','SEN',1,1,0);
INSERT INTO countries VALUES (186,'Seychelles','SC','SYC',1,1,0);
INSERT INTO countries VALUES (187,'Sierra Leone','SL','SLE',1,1,0);
INSERT INTO countries VALUES (188,'Singapore','SG','SGP', '4','1',0);
INSERT INTO countries VALUES (189,'Slovakia (Slovak Republic)','SK','SVK',1,1,0);
INSERT INTO countries VALUES (190,'Slovenia','SI','SVN',1,1,0);
INSERT INTO countries VALUES (191,'Solomon Islands','SB','SLB',1,1,0);
INSERT INTO countries VALUES (192,'Somalia','SO','SOM',1,1,0);
INSERT INTO countries VALUES (193,'South Africa','ZA','ZAF',1,1,0);
INSERT INTO countries VALUES (194,'South Georgia and the South Sandwich Islands','GS','SGS',1,1,0);
INSERT INTO countries VALUES (195,'Spain','ES','ESP','3','1',0);
INSERT INTO countries VALUES (196,'Sri Lanka','LK','LKA',1,1,0);
INSERT INTO countries VALUES (197,'St. Helena','SH','SHN',1,1,0);
INSERT INTO countries VALUES (198,'St. Pierre and Miquelon','PM','SPM',1,1,0);
INSERT INTO countries VALUES (199,'Sudan','SD','SDN',1,1,0);
INSERT INTO countries VALUES (200,'Suriname','SR','SUR',1,1,0);
INSERT INTO countries VALUES (201,'Svalbard and Jan Mayen Islands','SJ','SJM',1,1,0);
INSERT INTO countries VALUES (202,'Swaziland','SZ','SWZ',1,1,0);
INSERT INTO countries VALUES (203,'Sweden','SE','SWE',1,1,0);
INSERT INTO countries VALUES (204,'Switzerland','CH','CHE',5,1,0);
INSERT INTO countries VALUES (205,'Syrian Arab Republic','SY','SYR',1,1,0);
INSERT INTO countries VALUES (206,'Taiwan','TW','TWN',6,1,0);
INSERT INTO countries VALUES (207,'Tajikistan','TJ','TJK',1,1,0);
INSERT INTO countries VALUES (208,'Tanzania, United Republic of','TZ','TZA',1,1,0);
INSERT INTO countries VALUES (209,'Thailand','TH','THA',1,1,0);
INSERT INTO countries VALUES (210,'Togo','TG','TGO',1,1,0);
INSERT INTO countries VALUES (211,'Tokelau','TK','TKL',1,1,0);
INSERT INTO countries VALUES (212,'Tonga','TO','TON',1,1,0);
INSERT INTO countries VALUES (213,'Trinidad and Tobago','TT','TTO',1,1,0);
INSERT INTO countries VALUES (214,'Tunisia','TN','TUN',1,1,0);
INSERT INTO countries VALUES (215,'Turkey','TR','TUR',1,1,0);
INSERT INTO countries VALUES (216,'Turkmenistan','TM','TKM',1,1,0);
INSERT INTO countries VALUES (217,'Turks and Caicos Islands','TC','TCA',1,1,0);
INSERT INTO countries VALUES (218,'Tuvalu','TV','TUV',1,1,0);
INSERT INTO countries VALUES (219,'Uganda','UG','UGA',1,1,0);
INSERT INTO countries VALUES (220,'Ukraine','UA','UKR',1,1,0);
INSERT INTO countries VALUES (221,'United Arab Emirates','AE','ARE',1,1,0);
INSERT INTO countries VALUES (222,'United Kingdom','GB','GBR',8,1,0);
INSERT INTO countries VALUES (223,'United States','US','USA', '2','1',0);
INSERT INTO countries VALUES (224,'United States Minor Outlying Islands','UM','UMI',1,1,0);
INSERT INTO countries VALUES (225,'Uruguay','UY','URY',1,1,0);
INSERT INTO countries VALUES (226,'Uzbekistan','UZ','UZB',1,1,0);
INSERT INTO countries VALUES (227,'Vanuatu','VU','VUT',1,1,0);
INSERT INTO countries VALUES (228,'Vatican City State (Holy See)','VA','VAT',1,1,0);
INSERT INTO countries VALUES (229,'Venezuela','VE','VEN',1,1,0);
INSERT INTO countries VALUES (230,'Viet Nam','VN','VNM',1,1,0);
INSERT INTO countries VALUES (231,'Virgin Islands (British)','VG','VGB',1,1,0);
INSERT INTO countries VALUES (232,'Virgin Islands (U.S.)','VI','VIR',1,1,0);
INSERT INTO countries VALUES (233,'Wallis and Futuna Islands','WF','WLF',1,1,0);
INSERT INTO countries VALUES (234,'Western Sahara','EH','ESH',1,1,0);
INSERT INTO countries VALUES (235,'Yemen','YE','YEM',1,1,0);
INSERT INTO countries VALUES (237,'Zaire','ZR','ZAR',1,1,0);
INSERT INTO countries VALUES (238,'Zambia','ZM','ZMB',1,1,0);
INSERT INTO countries VALUES (239,'Zimbabwe','ZW','ZWE',1,1,0);
INSERT INTO countries VALUES (240,'Serbia','RS','SRB',1,1,0);
INSERT INTO countries VALUES (241,'Montenegro','ME','MNE',1,1,0);

# currencies
INSERT INTO currencies VALUES (1,'Euro','EUR','','EUR',',','.','2','1.0000',NOW(),'1');
INSERT INTO currencies VALUES (2,'United States Dollar','USD', '$', '', '.', ',', '2','1.2978',NOW(),'0');
INSERT INTO currencies VALUES (3,'Schweizer Franken','CHF', 'CHF', '', '.', '', '2','1.2044',NOW(),'0');
INSERT INTO currencies VALUES (4,'Great Britain Pound','GBP', '', '&pound;', '.', ',', '2','0.8094',NOW(),'0');

# database Version
INSERT INTO database_version(version) VALUES ('MOD_2.0.0.0');

# languages
INSERT INTO languages VALUES (1,'English','en','icon.gif','english',2,'iso-8859-15',1,1);
INSERT INTO languages VALUES (2,'Deutsch','de','icon.gif','german',1,'iso-8859-15',1,1);

# orders status
INSERT INTO orders_status VALUES (1,1,'Pending', 1);
INSERT INTO orders_status VALUES (1,2,'Offen', 1);
INSERT INTO orders_status VALUES (2,1,'Processing', 2);
INSERT INTO orders_status VALUES (2,2,'In Bearbeitung', 2);
INSERT INTO orders_status VALUES (3,1,'Delivered', 3);
INSERT INTO orders_status VALUES (3,2,'Versendet', 3);
INSERT INTO orders_status VALUES (4,1,'Reversed', 4);
INSERT INTO orders_status VALUES (4,2,'Storniert', 4);

# shipping status
INSERT INTO shipping_status VALUES (1, 1, '3-4 Days', '', 1);
INSERT INTO shipping_status VALUES (1, 2, '3-4 Tage', '', 1);
INSERT INTO shipping_status VALUES (2, 1, '1 Week', '', 2);
INSERT INTO shipping_status VALUES (2, 2, '1 Woche', '', 2);
INSERT INTO shipping_status VALUES (3, 1, '2 Weeks', '', 3);
INSERT INTO shipping_status VALUES (3, 2, '2 Wochen', '', 3);

# shop offline
INSERT INTO shop_configuration (configuration_id, configuration_key, configuration_value) VALUES(NULL, 'SHOP_OFFLINE', '');
INSERT INTO shop_configuration (configuration_id, configuration_key, configuration_value) VALUES(NULL, 'SHOP_OFFLINE_MSG', '<p style="text-align: center;"><span style="font-size: large;"><font face="Arial">Unser Shop ist aufgrund von Wartungsarbeiten im Moment nicht erreichbar.<br /></font><font face="Arial">Bitte besuchen Sie uns zu einem sp&auml;teren Zeitpunkt noch einmal.<br /><br /><br /><br /></font></span><font><font><a href="login_admin.php"><font color="#808080">Login</font></a></font></font><span style="font-size: large;"><font face="Arial"><br /></font></span></p>');

# USA
INSERT INTO zones VALUES (1,223,'AL','Alabama');
INSERT INTO zones VALUES (2,223,'AK','Alaska');
INSERT INTO zones VALUES (3,223,'AS','American Samoa');
INSERT INTO zones VALUES (4,223,'AZ','Arizona');
INSERT INTO zones VALUES (5,223,'AR','Arkansas');
INSERT INTO zones VALUES (6,223,'AF','Armed Forces Africa');
INSERT INTO zones VALUES (7,223,'AA','Armed Forces Americas');
INSERT INTO zones VALUES (8,223,'AC','Armed Forces Canada');
INSERT INTO zones VALUES (9,223,'AE','Armed Forces Europe');
INSERT INTO zones VALUES (10,223,'AM','Armed Forces Middle East');
INSERT INTO zones VALUES (11,223,'AP','Armed Forces Pacific');
INSERT INTO zones VALUES (12,223,'CA','California');
INSERT INTO zones VALUES (13,223,'CO','Colorado');
INSERT INTO zones VALUES (14,223,'CT','Connecticut');
INSERT INTO zones VALUES (15,223,'DE','Delaware');
INSERT INTO zones VALUES (16,223,'DC','District of Columbia');
INSERT INTO zones VALUES (17,223,'FM','Federated States Of Micronesia');
INSERT INTO zones VALUES (18,223,'FL','Florida');
INSERT INTO zones VALUES (19,223,'GA','Georgia');
INSERT INTO zones VALUES (20,223,'GU','Guam');
INSERT INTO zones VALUES (21,223,'HI','Hawaii');
INSERT INTO zones VALUES (22,223,'ID','Idaho');
INSERT INTO zones VALUES (23,223,'IL','Illinois');
INSERT INTO zones VALUES (24,223,'IN','Indiana');
INSERT INTO zones VALUES (25,223,'IA','Iowa');
INSERT INTO zones VALUES (26,223,'KS','Kansas');
INSERT INTO zones VALUES (27,223,'KY','Kentucky');
INSERT INTO zones VALUES (28,223,'LA','Louisiana');
INSERT INTO zones VALUES (29,223,'ME','Maine');
INSERT INTO zones VALUES (30,223,'MH','Marshall Islands');
INSERT INTO zones VALUES (31,223,'MD','Maryland');
INSERT INTO zones VALUES (32,223,'MA','Massachusetts');
INSERT INTO zones VALUES (33,223,'MI','Michigan');
INSERT INTO zones VALUES (34,223,'MN','Minnesota');
INSERT INTO zones VALUES (35,223,'MS','Mississippi');
INSERT INTO zones VALUES (36,223,'MO','Missouri');
INSERT INTO zones VALUES (37,223,'MT','Montana');
INSERT INTO zones VALUES (38,223,'NE','Nebraska');
INSERT INTO zones VALUES (39,223,'NV','Nevada');
INSERT INTO zones VALUES (40,223,'NH','New Hampshire');
INSERT INTO zones VALUES (41,223,'NJ','New Jersey');
INSERT INTO zones VALUES (42,223,'NM','New Mexico');
INSERT INTO zones VALUES (43,223,'NY','New York');
INSERT INTO zones VALUES (44,223,'NC','North Carolina');
INSERT INTO zones VALUES (45,223,'ND','North Dakota');
INSERT INTO zones VALUES (46,223,'MP','Northern Mariana Islands');
INSERT INTO zones VALUES (47,223,'OH','Ohio');
INSERT INTO zones VALUES (48,223,'OK','Oklahoma');
INSERT INTO zones VALUES (49,223,'OR','Oregon');
INSERT INTO zones VALUES (50,223,'PW','Palau');
INSERT INTO zones VALUES (51,223,'PA','Pennsylvania');
INSERT INTO zones VALUES (52,223,'PR','Puerto Rico');
INSERT INTO zones VALUES (53,223,'RI','Rhode Island');
INSERT INTO zones VALUES (54,223,'SC','South Carolina');
INSERT INTO zones VALUES (55,223,'SD','South Dakota');
INSERT INTO zones VALUES (56,223,'TN','Tennessee');
INSERT INTO zones VALUES (57,223,'TX','Texas');
INSERT INTO zones VALUES (58,223,'UT','Utah');
INSERT INTO zones VALUES (59,223,'VT','Vermont');
INSERT INTO zones VALUES (60,223,'VI','Virgin Islands');
INSERT INTO zones VALUES (61,223,'VA','Virginia');
INSERT INTO zones VALUES (62,223,'WA','Washington');
INSERT INTO zones VALUES (63,223,'WV','West Virginia');
INSERT INTO zones VALUES (64,223,'WI','Wisconsin');
INSERT INTO zones VALUES (65,223,'WY','Wyoming');

# Canada
INSERT INTO zones VALUES (66,38,'AB','Alberta');
INSERT INTO zones VALUES (67,38,'BC','British Columbia');
INSERT INTO zones VALUES (68,38,'MB','Manitoba');
INSERT INTO zones VALUES (69,38,'NF','Newfoundland');
INSERT INTO zones VALUES (70,38,'NB','New Brunswick');
INSERT INTO zones VALUES (71,38,'NS','Nova Scotia');
INSERT INTO zones VALUES (72,38,'NT','Northwest Territories');
INSERT INTO zones VALUES (73,38,'NU','Nunavut');
INSERT INTO zones VALUES (74,38,'ON','Ontario');
INSERT INTO zones VALUES (75,38,'PE','Prince Edward Island');
INSERT INTO zones VALUES (76,38,'QC','Quebec');
INSERT INTO zones VALUES (77,38,'SK','Saskatchewan');
INSERT INTO zones VALUES (78,38,'YT','Yukon Territory');

# Germany
INSERT INTO zones VALUES (79,81,'NI','Niedersachsen');
INSERT INTO zones VALUES (80,81,'BW','Baden-Württemberg');
INSERT INTO zones VALUES (81,81,'BY','Bayern');
INSERT INTO zones VALUES (82,81,'BE','Berlin');
INSERT INTO zones VALUES (83,81,'BR','Brandenburg');
INSERT INTO zones VALUES (84,81,'HB','Bremen');
INSERT INTO zones VALUES (85,81,'HH','Hamburg');
INSERT INTO zones VALUES (86,81,'HE','Hessen');
INSERT INTO zones VALUES (87,81,'MV','Mecklenburg-Vorpommern');
INSERT INTO zones VALUES (88,81,'NW','Nordrhein-Westfalen');
INSERT INTO zones VALUES (89,81,'RP','Rheinland-Pfalz');
INSERT INTO zones VALUES (90,81,'SL','Saarland');
INSERT INTO zones VALUES (91,81,'SN','Sachsen');
INSERT INTO zones VALUES (92,81,'ST','Sachsen-Anhalt');
INSERT INTO zones VALUES (93,81,'SH','Schleswig-Holstein');
INSERT INTO zones VALUES (94,81,'TH','Thüringen');

# Austria
INSERT INTO zones VALUES (95,14,'WI','Wien');
INSERT INTO zones VALUES (96,14,'NO','Niederösterreich');
INSERT INTO zones VALUES (97,14,'OO','Oberösterreich');
INSERT INTO zones VALUES (98,14,'SB','Salzburg');
INSERT INTO zones VALUES (99,14,'KN','Kärnten');
INSERT INTO zones VALUES (100,14,'ST','Steiermark');
INSERT INTO zones VALUES (101,14,'TI','Tirol');
INSERT INTO zones VALUES (102,14,'BL','Burgenland');
INSERT INTO zones VALUES (103,14,'VB','Voralberg');

# Swizterland
INSERT INTO zones VALUES (104,204,'AG','Aargau');
INSERT INTO zones VALUES (105,204,'AI','Appenzell Innerrhoden');
INSERT INTO zones VALUES (106,204,'AR','Appenzell Ausserrhoden');
INSERT INTO zones VALUES (107,204,'BE','Bern');
INSERT INTO zones VALUES (108,204,'BL','Basel-Landschaft');
INSERT INTO zones VALUES (109,204,'BS','Basel-Stadt');
INSERT INTO zones VALUES (110,204,'FR','Freiburg');
INSERT INTO zones VALUES (111,204,'GE','Genf');
INSERT INTO zones VALUES (112,204,'GL','Glarus');
INSERT INTO zones VALUES (113,204,'GR','Graubünden');
INSERT INTO zones VALUES (114,204,'JU','Jura');
INSERT INTO zones VALUES (115,204,'LU','Luzern');
INSERT INTO zones VALUES (116,204,'NE','Neuenburg');
INSERT INTO zones VALUES (117,204,'NW','Nidwalden');
INSERT INTO zones VALUES (118,204,'OW','Obwalden');
INSERT INTO zones VALUES (119,204,'SG','St. Gallen');
INSERT INTO zones VALUES (120,204,'SH','Schaffhausen');
INSERT INTO zones VALUES (121,204,'SO','Solothurn');
INSERT INTO zones VALUES (122,204,'SZ','Schwyz');
INSERT INTO zones VALUES (123,204,'TG','Thurgau');
INSERT INTO zones VALUES (124,204,'TI','Tessin');
INSERT INTO zones VALUES (125,204,'UR','Uri');
INSERT INTO zones VALUES (126,204,'VD','Waadt');
INSERT INTO zones VALUES (127,204,'VS','Wallis');
INSERT INTO zones VALUES (128,204,'ZG','Zug');
INSERT INTO zones VALUES (129,204,'ZH','Zürich');

# Spain
INSERT INTO zones VALUES (130,195,'ES-C','A Coruña');
INSERT INTO zones VALUES (131,195,'ES-VI','Álava');
INSERT INTO zones VALUES (132,195,'ES-AB','Albacete');
INSERT INTO zones VALUES (133,195,'ES-A','Alicante');
INSERT INTO zones VALUES (134,195,'ES-AL','Almería');
INSERT INTO zones VALUES (135,195,'ES-O','Asturias');
INSERT INTO zones VALUES (136,195,'ES-AV','Ávila');
INSERT INTO zones VALUES (137,195,'ES-BA','Badajoz');
INSERT INTO zones VALUES (138,195,'ES-PM','Balears');
INSERT INTO zones VALUES (139,195,'ES-B','Barcelona');
INSERT INTO zones VALUES (140,195,'ES-BU','Burgos');
INSERT INTO zones VALUES (141,195,'ES-CC','Cáceres');
INSERT INTO zones VALUES (142,195,'ES-CA','Cádiz');
INSERT INTO zones VALUES (143,195,'ES-S','Cantabria');
INSERT INTO zones VALUES (144,195,'ES-CS','Castellón');
INSERT INTO zones VALUES (145,195,'ES-CE','Ceuta');
INSERT INTO zones VALUES (146,195,'ES-CR','Ciudad Real');
INSERT INTO zones VALUES (147,195,'ES-CO','Córdoba');
INSERT INTO zones VALUES (148,195,'ES-CU','Cuenca');
INSERT INTO zones VALUES (149,195,'ES-GI','Girona');
INSERT INTO zones VALUES (150,195,'ES-GR','Granada');
INSERT INTO zones VALUES (151,195,'ES-GU','Guadalajara');
INSERT INTO zones VALUES (152,195,'ES-SS','Guipúzcoa');
INSERT INTO zones VALUES (153,195,'ES-H','Huelva');
INSERT INTO zones VALUES (154,195,'ES-HU','Huesca');
INSERT INTO zones VALUES (155,195,'ES-J','Jaén');
INSERT INTO zones VALUES (156,195,'ES-LO','La Rioja');
INSERT INTO zones VALUES (157,195,'ES-GC','Las Palmas');
INSERT INTO zones VALUES (158,195,'ES-LE','León');
INSERT INTO zones VALUES (159,195,'ES-L','Lleida');
INSERT INTO zones VALUES (160,195,'ES-LU','Lugo');
INSERT INTO zones VALUES (161,195,'ES-M','Madrid');
INSERT INTO zones VALUES (162,195,'ES-MA','Malaga');
INSERT INTO zones VALUES (163,195,'ES-ML','Melilla');
INSERT INTO zones VALUES (164,195,'ES-MU','Murcia');
INSERT INTO zones VALUES (165,195,'ES-NA','Navarra');
INSERT INTO zones VALUES (166,195,'ES-OR','Ourense');
INSERT INTO zones VALUES (167,195,'ES-P','Palencia');
INSERT INTO zones VALUES (168,195,'ES-PO','Pontevedra');
INSERT INTO zones VALUES (169,195,'ES-SA','Salamanca');
INSERT INTO zones VALUES (170,195,'ES-TF','Santa Cruz de Tenerife');
INSERT INTO zones VALUES (171,195,'ES-SG','Segovia');
INSERT INTO zones VALUES (172,195,'ES-SE','Sevilla');
INSERT INTO zones VALUES (173,195,'ES-SO','Soria');
INSERT INTO zones VALUES (174,195,'ES-T','Tarragona');
INSERT INTO zones VALUES (175,195,'ES-TE','Teruel');
INSERT INTO zones VALUES (176,195,'ES-TO','Toledo');
INSERT INTO zones VALUES (177,195,'ES-V','Valencia');
INSERT INTO zones VALUES (178,195,'ES-VA','Valladolid');
INSERT INTO zones VALUES (179,195,'ES-BI','Vizcaya');
INSERT INTO zones VALUES (180,195,'ES-ZA','Zamora');
INSERT INTO zones VALUES (181,195,'ES-Z','Zaragoza');

#Australia
INSERT INTO zones VALUES (182,13,'NSW','New South Wales');
INSERT INTO zones VALUES (183,13,'VIC','Victoria');
INSERT INTO zones VALUES (184,13,'QLD','Queensland');
INSERT INTO zones VALUES (185,13,'NT','Northern Territory');
INSERT INTO zones VALUES (186,13,'WA','Western Australia');
INSERT INTO zones VALUES (187,13,'SA','South Australia');
INSERT INTO zones VALUES (188,13,'TAS','Tasmania');
INSERT INTO zones VALUES (189,13,'ACT','Australian Capital Territory');

#New Zealand
INSERT INTO zones VALUES (190,153,'Northland','Northland');
INSERT INTO zones VALUES (191,153,'Auckland','Auckland');
INSERT INTO zones VALUES (192,153,'Waikato','Waikato');
INSERT INTO zones VALUES (193,153,'Bay of Plenty','Bay of Plenty');
INSERT INTO zones VALUES (194,153,'Gisborne','Gisborne');
INSERT INTO zones VALUES (195,153,'Hawkes Bay','Hawkes Bay');
INSERT INTO zones VALUES (196,153,'Taranaki','Taranaki');
INSERT INTO zones VALUES (197,153,'Manawatu-Wanganui','Manawatu-Wanganui');
INSERT INTO zones VALUES (198,153,'Wellington','Wellington');
INSERT INTO zones VALUES (199,153,'West Coast','West Coast');
INSERT INTO zones VALUES (200,153,'Canterbury','Canterbury');
INSERT INTO zones VALUES (201,153,'Otago','Otago');
INSERT INTO zones VALUES (202,153,'Southland','Southland');
INSERT INTO zones VALUES (203,153,'Tasman','Tasman');
INSERT INTO zones VALUES (204,153,'Nelson','Nelson');
INSERT INTO zones VALUES (205,153,'Marlborough','Marlborough');

#Brazil
INSERT INTO zones VALUES (NULL,30,'SP','São Paulo');
INSERT INTO zones VALUES (NULL,30,'RJ','Rio de Janeiro');
INSERT INTO zones VALUES (NULL,30,'PE','Pernanbuco');
INSERT INTO zones VALUES (NULL,30,'BA','Bahia');
INSERT INTO zones VALUES (NULL,30,'AM','Amazonas');
INSERT INTO zones VALUES (NULL,30,'MG','Minas Gerais');
INSERT INTO zones VALUES (NULL,30,'ES','Espirito Santo');
INSERT INTO zones VALUES (NULL,30,'RS','Rio Grande do Sul');
INSERT INTO zones VALUES (NULL,30,'PR','Paraná');
INSERT INTO zones VALUES (NULL,30,'SC','Santa Catarina');
INSERT INTO zones VALUES (NULL,30,'RG','Rio Grande do Norte');
INSERT INTO zones VALUES (NULL,30,'MS','Mato Grosso do Sul');
INSERT INTO zones VALUES (NULL,30,'MT','Mato Grosso');
INSERT INTO zones VALUES (NULL,30,'GO','Goias');
INSERT INTO zones VALUES (NULL,30,'TO','Tocantins');
INSERT INTO zones VALUES (NULL,30,'DF','Distrito Federal');
INSERT INTO zones VALUES (NULL,30,'RO','Rondonia');
INSERT INTO zones VALUES (NULL,30,'AC','Acre');
INSERT INTO zones VALUES (NULL,30,'AP','Amapa');
INSERT INTO zones VALUES (NULL,30,'RR','Roraima');
INSERT INTO zones VALUES (NULL,30,'AL','Alagoas');
INSERT INTO zones VALUES (NULL,30,'CE','Ceará');
INSERT INTO zones VALUES (NULL,30,'MA','Maranhão');
INSERT INTO zones VALUES (NULL,30,'PA','Pará');
INSERT INTO zones VALUES (NULL,30,'PB','Paraíba');
INSERT INTO zones VALUES (NULL,30,'PI','Piauí');
INSERT INTO zones VALUES (NULL,30,'SE','Sergipe');

#Chile
INSERT INTO zones VALUES (NULL,43,'I','I Región de Tarapacá');
INSERT INTO zones VALUES (NULL,43,'II','II Región de Antofagasta');
INSERT INTO zones VALUES (NULL,43,'III','III Región de Atacama');
INSERT INTO zones VALUES (NULL,43,'IV','IV Región de Coquimbo');
INSERT INTO zones VALUES (NULL,43,'V','V Región de Valaparaíso');
INSERT INTO zones VALUES (NULL,43,'RM','Región Metropolitana');
INSERT INTO zones VALUES (NULL,43,'VI','VI Región de L. B. O´higgins');
INSERT INTO zones VALUES (NULL,43,'VII','VII Región del Maule');
INSERT INTO zones VALUES (NULL,43,'VIII','VIII Región del Bío Bío');
INSERT INTO zones VALUES (NULL,43,'IX','IX Región de la Araucanía');
INSERT INTO zones VALUES (NULL,43,'X','X Región de los Lagos');
INSERT INTO zones VALUES (NULL,43,'XI','XI Región de Aysén');
INSERT INTO zones VALUES (NULL,43,'XII','XII Región de Magallanes');

#Columbia
INSERT INTO zones VALUES (NULL,47,'AMA','Amazonas');
INSERT INTO zones VALUES (NULL,47,'ANT','Antioquia');
INSERT INTO zones VALUES (NULL,47,'ARA','Arauca');
INSERT INTO zones VALUES (NULL,47,'ATL','Atlantico');
INSERT INTO zones VALUES (NULL,47,'BOL','Bolivar');
INSERT INTO zones VALUES (NULL,47,'BOY','Boyaca');
INSERT INTO zones VALUES (NULL,47,'CAL','Caldas');
INSERT INTO zones VALUES (NULL,47,'CAQ','Caqueta');
INSERT INTO zones VALUES (NULL,47,'CAS','Casanare');
INSERT INTO zones VALUES (NULL,47,'CAU','Cauca');
INSERT INTO zones VALUES (NULL,47,'CES','Cesar');
INSERT INTO zones VALUES (NULL,47,'CHO','Choco');
INSERT INTO zones VALUES (NULL,47,'COR','Cordoba');
INSERT INTO zones VALUES (NULL,47,'CUN','Cundinamarca');
INSERT INTO zones VALUES (NULL,47,'HUI','Huila');
INSERT INTO zones VALUES (NULL,47,'GUA','Guainia');
INSERT INTO zones VALUES (NULL,47,'LAG','Guajira');
INSERT INTO zones VALUES (NULL,47,'GUV','Guaviare');
INSERT INTO zones VALUES (NULL,47,'MAG','Magdalena');
INSERT INTO zones VALUES (NULL,47,'MET','Meta');
INSERT INTO zones VALUES (NULL,47,'NAR','Narino');
INSERT INTO zones VALUES (NULL,47,'NDS','Norte de Santander');
INSERT INTO zones VALUES (NULL,47,'PUT','Putumayo');
INSERT INTO zones VALUES (NULL,47,'QUI','Quindio');
INSERT INTO zones VALUES (NULL,47,'RIS','Risaralda');
INSERT INTO zones VALUES (NULL,47,'SAI','San Andres Islas');
INSERT INTO zones VALUES (NULL,47,'SAN','Santander');
INSERT INTO zones VALUES (NULL,47,'SUC','Sucre');
INSERT INTO zones VALUES (NULL,47,'TOL','Tolima');
INSERT INTO zones VALUES (NULL,47,'VAL','Valle');
INSERT INTO zones VALUES (NULL,47,'VAU','Vaupes');
INSERT INTO zones VALUES (NULL,47,'VIC','Vichada');

#France
INSERT INTO zones VALUES (NULL,73,'Et','Etranger');
INSERT INTO zones VALUES (NULL,73,'01','Ain');
INSERT INTO zones VALUES (NULL,73,'02','Aisne');
INSERT INTO zones VALUES (NULL,73,'03','Allier');
INSERT INTO zones VALUES (NULL,73,'04','Alpes de Haute Provence');
INSERT INTO zones VALUES (NULL,73,'05','Hautes-Alpes');
INSERT INTO zones VALUES (NULL,73,'06','Alpes Maritimes');
INSERT INTO zones VALUES (NULL,73,'07','Ardèche');
INSERT INTO zones VALUES (NULL,73,'08','Ardennes');
INSERT INTO zones VALUES (NULL,73,'09','Ariège');
INSERT INTO zones VALUES (NULL,73,'10','Aube');
INSERT INTO zones VALUES (NULL,73,'11','Aude');
INSERT INTO zones VALUES (NULL,73,'12','Aveyron');
INSERT INTO zones VALUES (NULL,73,'13','Bouches-du-Rhône');
INSERT INTO zones VALUES (NULL,73,'14','Calvados');
INSERT INTO zones VALUES (NULL,73,'15','Cantal');
INSERT INTO zones VALUES (NULL,73,'16','Charente');
INSERT INTO zones VALUES (NULL,73,'17','Charente Maritime');
INSERT INTO zones VALUES (NULL,73,'18','Cher');
INSERT INTO zones VALUES (NULL,73,'19','Corrèze');
INSERT INTO zones VALUES (NULL,73,'2A','Corse du Sud');
INSERT INTO zones VALUES (NULL,73,'2B','Haute Corse');
INSERT INTO zones VALUES (NULL,73,'21','Côte-d\'Or');
INSERT INTO zones VALUES (NULL,73,'22','Côtes-d\'Armor');
INSERT INTO zones VALUES (NULL,73,'23','Creuse');
INSERT INTO zones VALUES (NULL,73,'24','Dordogne');
INSERT INTO zones VALUES (NULL,73,'25','Doubs');
INSERT INTO zones VALUES (NULL,73,'26','Drôme');
INSERT INTO zones VALUES (NULL,73,'27','Eure');
INSERT INTO zones VALUES (NULL,73,'28','Eure et Loir');
INSERT INTO zones VALUES (NULL,73,'29','Finistère');
INSERT INTO zones VALUES (NULL,73,'30','Gard');
INSERT INTO zones VALUES (NULL,73,'31','Haute Garonne');
INSERT INTO zones VALUES (NULL,73,'32','Gers');
INSERT INTO zones VALUES (NULL,73,'33','Gironde');
INSERT INTO zones VALUES (NULL,73,'34','Hérault');
INSERT INTO zones VALUES (NULL,73,'35','Ille et Vilaine');
INSERT INTO zones VALUES (NULL,73,'36','Indre');
INSERT INTO zones VALUES (NULL,73,'37','Indre et Loire');
INSERT INTO zones VALUES (NULL,73,'38','Isère');
INSERT INTO zones VALUES (NULL,73,'39','Jura');
INSERT INTO zones VALUES (NULL,73,'40','Landes');
INSERT INTO zones VALUES (NULL,73,'41','Loir et Cher');
INSERT INTO zones VALUES (NULL,73,'42','Loire');
INSERT INTO zones VALUES (NULL,73,'43','Haute Loire');
INSERT INTO zones VALUES (NULL,73,'44','Loire Atlantique');
INSERT INTO zones VALUES (NULL,73,'45','Loiret');
INSERT INTO zones VALUES (NULL,73,'46','Lot');
INSERT INTO zones VALUES (NULL,73,'47','Lot et Garonne');
INSERT INTO zones VALUES (NULL,73,'48','Lozère');
INSERT INTO zones VALUES (NULL,73,'49','Maine et Loire');
INSERT INTO zones VALUES (NULL,73,'50','Manche');
INSERT INTO zones VALUES (NULL,73,'51','Marne');
INSERT INTO zones VALUES (NULL,73,'52','Haute Marne');
INSERT INTO zones VALUES (NULL,73,'53','Mayenne');
INSERT INTO zones VALUES (NULL,73,'54','Meurthe et Moselle');
INSERT INTO zones VALUES (NULL,73,'55','Meuse');
INSERT INTO zones VALUES (NULL,73,'56','Morbihan');
INSERT INTO zones VALUES (NULL,73,'57','Moselle');
INSERT INTO zones VALUES (NULL,73,'58','Nièvre');
INSERT INTO zones VALUES (NULL,73,'59','Nord');
INSERT INTO zones VALUES (NULL,73,'60','Oise');
INSERT INTO zones VALUES (NULL,73,'61','Orne');
INSERT INTO zones VALUES (NULL,73,'62','Pas de Calais');
INSERT INTO zones VALUES (NULL,73,'63','Puy-de-Dôme');
INSERT INTO zones VALUES (NULL,73,'64','Pyrénées-Atlantiques');
INSERT INTO zones VALUES (NULL,73,'65','Hautes-Pyrénées');
INSERT INTO zones VALUES (NULL,73,'66','Pyrénées-Orientales');
INSERT INTO zones VALUES (NULL,73,'67','Bas Rhin');
INSERT INTO zones VALUES (NULL,73,'68','Haut Rhin');
INSERT INTO zones VALUES (NULL,73,'69','Rhône');
INSERT INTO zones VALUES (NULL,73,'70','Haute-Saône');
INSERT INTO zones VALUES (NULL,73,'71','Saône-et-Loire');
INSERT INTO zones VALUES (NULL,73,'72','Sarthe');
INSERT INTO zones VALUES (NULL,73,'73','Savoie');
INSERT INTO zones VALUES (NULL,73,'74','Haute Savoie');
INSERT INTO zones VALUES (NULL,73,'75','Paris');
INSERT INTO zones VALUES (NULL,73,'76','Seine Maritime');
INSERT INTO zones VALUES (NULL,73,'77','Seine et Marne');
INSERT INTO zones VALUES (NULL,73,'78','Yvelines');
INSERT INTO zones VALUES (NULL,73,'79','Deux-Sèvres');
INSERT INTO zones VALUES (NULL,73,'80','Somme');
INSERT INTO zones VALUES (NULL,73,'81','Tarn');
INSERT INTO zones VALUES (NULL,73,'82','Tarn et Garonne');
INSERT INTO zones VALUES (NULL,73,'83','Var');
INSERT INTO zones VALUES (NULL,73,'84','Vaucluse');
INSERT INTO zones VALUES (NULL,73,'85','Vendée');
INSERT INTO zones VALUES (NULL,73,'86','Vienne');
INSERT INTO zones VALUES (NULL,73,'87','Haute Vienne');
INSERT INTO zones VALUES (NULL,73,'88','Vosges');
INSERT INTO zones VALUES (NULL,73,'89','Yonne');
INSERT INTO zones VALUES (NULL,73,'90','Territoire de Belfort');
INSERT INTO zones VALUES (NULL,73,'91','Essonne');
INSERT INTO zones VALUES (NULL,73,'92','Hauts de Seine');
INSERT INTO zones VALUES (NULL,73,'93','Seine St-Denis');
INSERT INTO zones VALUES (NULL,73,'94','Val de Marne');
INSERT INTO zones VALUES (NULL,73,'95','Val d\'Oise');
INSERT INTO zones VALUES (NULL,73,'971 (DOM)','Guadeloupe');
INSERT INTO zones VALUES (NULL,73,'972 (DOM)','Martinique');
INSERT INTO zones VALUES (NULL,73,'973 (DOM)','Guyane');
INSERT INTO zones VALUES (NULL,73,'974 (DOM)','Saint Denis');
INSERT INTO zones VALUES (NULL,73,'975 (DOM)','St-Pierre de Miquelon');
INSERT INTO zones VALUES (NULL,73,'976 (TOM)','Mayotte');
INSERT INTO zones VALUES (NULL,73,'984 (TOM)','Terres australes et Antartiques françaises');
INSERT INTO zones VALUES (NULL,73,'985 (TOM)','Nouvelle Calédonie');
INSERT INTO zones VALUES (NULL,73,'986 (TOM)','Wallis et Futuna');
INSERT INTO zones VALUES (NULL,73,'987 (TOM)','Polynésie française');

#India
INSERT INTO zones VALUES (NULL,99,'DL','Delhi');
INSERT INTO zones VALUES (NULL,99,'MH','Maharashtra');
INSERT INTO zones VALUES (NULL,99,'TN','Tamil Nadu');
INSERT INTO zones VALUES (NULL,99,'KL','Kerala');
INSERT INTO zones VALUES (NULL,99,'AP','Andhra Pradesh');
INSERT INTO zones VALUES (NULL,99,'KA','Karnataka');
INSERT INTO zones VALUES (NULL,99,'GA','Goa');
INSERT INTO zones VALUES (NULL,99,'MP','Madhya Pradesh');
INSERT INTO zones VALUES (NULL,99,'PY','Pondicherry');
INSERT INTO zones VALUES (NULL,99,'GJ','Gujarat');
INSERT INTO zones VALUES (NULL,99,'OR','Orrisa');
INSERT INTO zones VALUES (NULL,99,'CA','Chhatisgarh');
INSERT INTO zones VALUES (NULL,99,'JH','Jharkhand');
INSERT INTO zones VALUES (NULL,99,'BR','Bihar');
INSERT INTO zones VALUES (NULL,99,'WB','West Bengal');
INSERT INTO zones VALUES (NULL,99,'UP','Uttar Pradesh');
INSERT INTO zones VALUES (NULL,99,'RJ','Rajasthan');
INSERT INTO zones VALUES (NULL,99,'PB','Punjab');
INSERT INTO zones VALUES (NULL,99,'HR','Haryana');
INSERT INTO zones VALUES (NULL,99,'CH','Chandigarh');
INSERT INTO zones VALUES (NULL,99,'JK','Jammu & Kashmir');
INSERT INTO zones VALUES (NULL,99,'HP','Himachal Pradesh');
INSERT INTO zones VALUES (NULL,99,'UA','Uttaranchal');
INSERT INTO zones VALUES (NULL,99,'LK','Lakshadweep');
INSERT INTO zones VALUES (NULL,99,'AN','Andaman & Nicobar');
INSERT INTO zones VALUES (NULL,99,'MG','Meghalaya');
INSERT INTO zones VALUES (NULL,99,'AS','Assam');
INSERT INTO zones VALUES (NULL,99,'DR','Dadra & Nagar Haveli');
INSERT INTO zones VALUES (NULL,99,'DN','Daman & Diu');
INSERT INTO zones VALUES (NULL,99,'SK','Sikkim');
INSERT INTO zones VALUES (NULL,99,'TR','Tripura');
INSERT INTO zones VALUES (NULL,99,'MZ','Mizoram');
INSERT INTO zones VALUES (NULL,99,'MN','Manipur');
INSERT INTO zones VALUES (NULL,99,'NL','Nagaland');
INSERT INTO zones VALUES (NULL,99,'AR','Arunachal Pradesh');

#Italy
INSERT INTO zones VALUES (NULL,105,'AG','Agrigento');
INSERT INTO zones VALUES (NULL,105,'AL','Alessandria');
INSERT INTO zones VALUES (NULL,105,'AN','Ancona');
INSERT INTO zones VALUES (NULL,105,'AO','Aosta');
INSERT INTO zones VALUES (NULL,105,'AR','Arezzo');
INSERT INTO zones VALUES (NULL,105,'AP','Ascoli Piceno');
INSERT INTO zones VALUES (NULL,105,'AT','Asti');
INSERT INTO zones VALUES (NULL,105,'AV','Avellino');
INSERT INTO zones VALUES (NULL,105,'BA','Bari');
INSERT INTO zones VALUES (NULL,105,'BT','Barletta-Andria-Trani');
INSERT INTO zones VALUES (NULL,105,'BL','Belluno');
INSERT INTO zones VALUES (NULL,105,'BN','Benevento');
INSERT INTO zones VALUES (NULL,105,'BG','Bergamo');
INSERT INTO zones VALUES (NULL,105,'BI','Biella');
INSERT INTO zones VALUES (NULL,105,'BO','Bologna');
INSERT INTO zones VALUES (NULL,105,'BZ','Bolzano');
INSERT INTO zones VALUES (NULL,105,'BS','Brescia');
INSERT INTO zones VALUES (NULL,105,'BR','Brindisi');
INSERT INTO zones VALUES (NULL,105,'CA','Cagliari');
INSERT INTO zones VALUES (NULL,105,'CL','Caltanissetta');
INSERT INTO zones VALUES (NULL,105,'CB','Campobasso');
INSERT INTO zones VALUES (NULL,105,'CI','Carbonia-Iglesias');
INSERT INTO zones VALUES (NULL,105,'CE','Caserta');
INSERT INTO zones VALUES (NULL,105,'CT','Catania');
INSERT INTO zones VALUES (NULL,105,'CZ','Catanzaro');
INSERT INTO zones VALUES (NULL,105,'CH','Chieti');
INSERT INTO zones VALUES (NULL,105,'CO','Como');
INSERT INTO zones VALUES (NULL,105,'CS','Cosenza');
INSERT INTO zones VALUES (NULL,105,'CR','Cremona');
INSERT INTO zones VALUES (NULL,105,'KR','Crotone');
INSERT INTO zones VALUES (NULL,105,'CN','Cuneo');
INSERT INTO zones VALUES (NULL,105,'EN','Enna');
INSERT INTO zones VALUES (NULL,105,'FM','Fermo');
INSERT INTO zones VALUES (NULL,105,'FE','Ferrara');
INSERT INTO zones VALUES (NULL,105,'FI','Firenze');
INSERT INTO zones VALUES (NULL,105,'FG','Foggia');
INSERT INTO zones VALUES (NULL,105,'FC','Forlì-Cesena');
INSERT INTO zones VALUES (NULL,105,'FR','Frosinone');
INSERT INTO zones VALUES (NULL,105,'GE','Genova');
INSERT INTO zones VALUES (NULL,105,'GO','Gorizia');
INSERT INTO zones VALUES (NULL,105,'GR','Grosseto');
INSERT INTO zones VALUES (NULL,105,'IM','Imperia');
INSERT INTO zones VALUES (NULL,105,'IS','Isernia');
INSERT INTO zones VALUES (NULL,105,'SP','La Spezia');
INSERT INTO zones VALUES (NULL,105,'AQ','Aquila');
INSERT INTO zones VALUES (NULL,105,'LT','Latina');
INSERT INTO zones VALUES (NULL,105,'LE','Lecce');
INSERT INTO zones VALUES (NULL,105,'LC','Lecco');
INSERT INTO zones VALUES (NULL,105,'LI','Livorno');
INSERT INTO zones VALUES (NULL,105,'LO','Lodi');
INSERT INTO zones VALUES (NULL,105,'LU','Lucca');
INSERT INTO zones VALUES (NULL,105,'MC','Macerata');
INSERT INTO zones VALUES (NULL,105,'MN','Mantova');
INSERT INTO zones VALUES (NULL,105,'MS','Massa-Carrara');
INSERT INTO zones VALUES (NULL,105,'MT','Matera');
INSERT INTO zones VALUES (NULL,105,'ME','Messina');
INSERT INTO zones VALUES (NULL,105,'MI','Milano');
INSERT INTO zones VALUES (NULL,105,'MO','Modena');
INSERT INTO zones VALUES (NULL,105,'MB','Monza e della Brianza');
INSERT INTO zones VALUES (NULL,105,'NA','Napoli');
INSERT INTO zones VALUES (NULL,105,'NO','Novara');
INSERT INTO zones VALUES (NULL,105,'NU','Nuoro');
INSERT INTO zones VALUES (NULL,105,'OT','Olbia-Tempio');
INSERT INTO zones VALUES (NULL,105,'OR','Oristano');
INSERT INTO zones VALUES (NULL,105,'PD','Padova');
INSERT INTO zones VALUES (NULL,105,'PA','Palermo');
INSERT INTO zones VALUES (NULL,105,'PR','Parma');
INSERT INTO zones VALUES (NULL,105,'PV','Pavia');
INSERT INTO zones VALUES (NULL,105,'PG','Perugia');
INSERT INTO zones VALUES (NULL,105,'PU','Pesaro e Urbino');
INSERT INTO zones VALUES (NULL,105,'PE','Pescara');
INSERT INTO zones VALUES (NULL,105,'PC','Piacenza');
INSERT INTO zones VALUES (NULL,105,'PI','Pisa');
INSERT INTO zones VALUES (NULL,105,'PT','Pistoia');
INSERT INTO zones VALUES (NULL,105,'PN','Pordenone');
INSERT INTO zones VALUES (NULL,105,'PZ','Potenza');
INSERT INTO zones VALUES (NULL,105,'PO','Prato');
INSERT INTO zones VALUES (NULL,105,'RG','Ragusa');
INSERT INTO zones VALUES (NULL,105,'RA','Ravenna');
INSERT INTO zones VALUES (NULL,105,'RC','Reggio di Calabria');
INSERT INTO zones VALUES (NULL,105,'RE','Reggio Emilia');
INSERT INTO zones VALUES (NULL,105,'RI','Rieti');
INSERT INTO zones VALUES (NULL,105,'RN','Rimini');
INSERT INTO zones VALUES (NULL,105,'RM','Roma');
INSERT INTO zones VALUES (NULL,105,'RO','Rovigo');
INSERT INTO zones VALUES (NULL,105,'SA','Salerno');
INSERT INTO zones VALUES (NULL,105,'VS','Medio Campidano');
INSERT INTO zones VALUES (NULL,105,'SS','Sassari');
INSERT INTO zones VALUES (NULL,105,'SV','Savona');
INSERT INTO zones VALUES (NULL,105,'SI','Siena');
INSERT INTO zones VALUES (NULL,105,'SR','Siracusa');
INSERT INTO zones VALUES (NULL,105,'SO','Sondrio');
INSERT INTO zones VALUES (NULL,105,'TA','Taranto');
INSERT INTO zones VALUES (NULL,105,'TE','Teramo');
INSERT INTO zones VALUES (NULL,105,'TR','Terni');
INSERT INTO zones VALUES (NULL,105,'TO','Torino');
INSERT INTO zones VALUES (NULL,105,'OG','Ogliastra');
INSERT INTO zones VALUES (NULL,105,'TP','Trapani');
INSERT INTO zones VALUES (NULL,105,'TN','Trento');
INSERT INTO zones VALUES (NULL,105,'TV','Treviso');
INSERT INTO zones VALUES (NULL,105,'TS','Trieste');
INSERT INTO zones VALUES (NULL,105,'UD','Udine');
INSERT INTO zones VALUES (NULL,105,'VA','Varese');
INSERT INTO zones VALUES (NULL,105,'VE','Venezia');
INSERT INTO zones VALUES (NULL,105,'VB','Verbania');
INSERT INTO zones VALUES (NULL,105,'VC','Vercelli');
INSERT INTO zones VALUES (NULL,105,'VR','Verona');
INSERT INTO zones VALUES (NULL,105,'VV','Vibo Valentia');
INSERT INTO zones VALUES (NULL,105,'VI','Vicenza');
INSERT INTO zones VALUES (NULL,105,'VT','Viterbo');

#Japan
INSERT INTO zones VALUES (NULL,107,'Niigata', 'Niigata');
INSERT INTO zones VALUES (NULL,107,'Toyama', 'Toyama');
INSERT INTO zones VALUES (NULL,107,'Ishikawa', 'Ishikawa');
INSERT INTO zones VALUES (NULL,107,'Fukui', 'Fukui');
INSERT INTO zones VALUES (NULL,107,'Yamanashi', 'Yamanashi');
INSERT INTO zones VALUES (NULL,107,'Nagano', 'Nagano');
INSERT INTO zones VALUES (NULL,107,'Gifu', 'Gifu');
INSERT INTO zones VALUES (NULL,107,'Shizuoka', 'Shizuoka');
INSERT INTO zones VALUES (NULL,107,'Aichi', 'Aichi');
INSERT INTO zones VALUES (NULL,107,'Mie', 'Mie');
INSERT INTO zones VALUES (NULL,107,'Shiga', 'Shiga');
INSERT INTO zones VALUES (NULL,107,'Kyoto', 'Kyoto');
INSERT INTO zones VALUES (NULL,107,'Osaka', 'Osaka');
INSERT INTO zones VALUES (NULL,107,'Hyogo', 'Hyogo');
INSERT INTO zones VALUES (NULL,107,'Nara', 'Nara');
INSERT INTO zones VALUES (NULL,107,'Wakayama', 'Wakayama');
INSERT INTO zones VALUES (NULL,107,'Tottori', 'Tottori');
INSERT INTO zones VALUES (NULL,107,'Shimane', 'Shimane');
INSERT INTO zones VALUES (NULL,107,'Okayama', 'Okayama');
INSERT INTO zones VALUES (NULL,107,'Hiroshima', 'Hiroshima');
INSERT INTO zones VALUES (NULL,107,'Yamaguchi', 'Yamaguchi');
INSERT INTO zones VALUES (NULL,107,'Tokushima', 'Tokushima');
INSERT INTO zones VALUES (NULL,107,'Kagawa', 'Kagawa');
INSERT INTO zones VALUES (NULL,107,'Ehime', 'Ehime');
INSERT INTO zones VALUES (NULL,107,'Kochi', 'Kochi');
INSERT INTO zones VALUES (NULL,107,'Fukuoka', 'Fukuoka');
INSERT INTO zones VALUES (NULL,107,'Saga', 'Saga');
INSERT INTO zones VALUES (NULL,107,'Nagasaki', 'Nagasaki');
INSERT INTO zones VALUES (NULL,107,'Kumamoto', 'Kumamoto');
INSERT INTO zones VALUES (NULL,107,'Oita', 'Oita');
INSERT INTO zones VALUES (NULL,107,'Miyazaki', 'Miyazaki');
INSERT INTO zones VALUES (NULL,107,'Kagoshima', 'Kagoshima');

#Malaysia
INSERT INTO zones VALUES (NULL,129,'JOH','Johor');
INSERT INTO zones VALUES (NULL,129,'KDH','Kedah');
INSERT INTO zones VALUES (NULL,129,'KEL','Kelantan');
INSERT INTO zones VALUES (NULL,129,'KL','Kuala Lumpur');
INSERT INTO zones VALUES (NULL,129,'MEL','Melaka');
INSERT INTO zones VALUES (NULL,129,'NS','Negeri Sembilan');
INSERT INTO zones VALUES (NULL,129,'PAH','Pahang');
INSERT INTO zones VALUES (NULL,129,'PRK','Perak');
INSERT INTO zones VALUES (NULL,129,'PER','Perlis');
INSERT INTO zones VALUES (NULL,129,'PP','Pulau Pinang');
INSERT INTO zones VALUES (NULL,129,'SAB','Sabah');
INSERT INTO zones VALUES (NULL,129,'SWK','Sarawak');
INSERT INTO zones VALUES (NULL,129,'SEL','Selangor');
INSERT INTO zones VALUES (NULL,129,'TER','Terengganu');
INSERT INTO zones VALUES (NULL,129,'LAB','W.P.Labuan');

#Mexico
INSERT INTO zones VALUES (NULL,138,'AGS','Aguascalientes');
INSERT INTO zones VALUES (NULL,138,'BC','Baja California');
INSERT INTO zones VALUES (NULL,138,'BCS','Baja California Sur');
INSERT INTO zones VALUES (NULL,138,'CAM','Campeche');
INSERT INTO zones VALUES (NULL,138,'COA','Coahuila');
INSERT INTO zones VALUES (NULL,138,'COL','Colima');
INSERT INTO zones VALUES (NULL,138,'CHI','Chiapas');
INSERT INTO zones VALUES (NULL,138,'CHIH','Chihuahua');
INSERT INTO zones VALUES (NULL,138,'DF','Distrito Federal');
INSERT INTO zones VALUES (NULL,138,'DGO','Durango');
INSERT INTO zones VALUES (NULL,138,'MEX','Estado de Mexico');
INSERT INTO zones VALUES (NULL,138,'GTO','Guanajuato');
INSERT INTO zones VALUES (NULL,138,'GRO','Guerrero');
INSERT INTO zones VALUES (NULL,138,'HGO','Hidalgo');
INSERT INTO zones VALUES (NULL,138,'JAL','Jalisco');
INSERT INTO zones VALUES (NULL,138,'MCH','Michoacan');
INSERT INTO zones VALUES (NULL,138,'MOR','Morelos');
INSERT INTO zones VALUES (NULL,138,'NAY','Nayarit');
INSERT INTO zones VALUES (NULL,138,'NL','Nuevo Leon');
INSERT INTO zones VALUES (NULL,138,'OAX','Oaxaca');
INSERT INTO zones VALUES (NULL,138,'PUE','Puebla');
INSERT INTO zones VALUES (NULL,138,'QRO','Queretaro');
INSERT INTO zones VALUES (NULL,138,'QR','Quintana Roo');
INSERT INTO zones VALUES (NULL,138,'SLP','San Luis Potosi');
INSERT INTO zones VALUES (NULL,138,'SIN','Sinaloa');
INSERT INTO zones VALUES (NULL,138,'SON','Sonora');
INSERT INTO zones VALUES (NULL,138,'TAB','Tabasco');
INSERT INTO zones VALUES (NULL,138,'TMPS','Tamaulipas');
INSERT INTO zones VALUES (NULL,138,'TLAX','Tlaxcala');
INSERT INTO zones VALUES (NULL,138,'VER','Veracruz');
INSERT INTO zones VALUES (NULL,138,'YUC','Yucatan');
INSERT INTO zones VALUES (NULL,138,'ZAC','Zacatecas');

#Norway
INSERT INTO zones VALUES (NULL,160,'OSL','Oslo');
INSERT INTO zones VALUES (NULL,160,'AKE','Akershus');
INSERT INTO zones VALUES (NULL,160,'AUA','Aust-Agder');
INSERT INTO zones VALUES (NULL,160,'BUS','Buskerud');
INSERT INTO zones VALUES (NULL,160,'FIN','Finnmark');
INSERT INTO zones VALUES (NULL,160,'HED','Hedmark');
INSERT INTO zones VALUES (NULL,160,'HOR','Hordaland');
INSERT INTO zones VALUES (NULL,160,'MOR','Møre og Romsdal');
INSERT INTO zones VALUES (NULL,160,'NOR','Nordland');
INSERT INTO zones VALUES (NULL,160,'NTR','Nord-Trøndelag');
INSERT INTO zones VALUES (NULL,160,'OPP','Oppland');
INSERT INTO zones VALUES (NULL,160,'ROG','Rogaland');
INSERT INTO zones VALUES (NULL,160,'SOF','Sogn og Fjordane');
INSERT INTO zones VALUES (NULL,160,'STR','Sør-Trøndelag');
INSERT INTO zones VALUES (NULL,160,'TEL','Telemark');
INSERT INTO zones VALUES (NULL,160,'TRO','Troms');
INSERT INTO zones VALUES (NULL,160,'VEA','Vest-Agder');
INSERT INTO zones VALUES (NULL,160,'OST','Østfold');
INSERT INTO zones VALUES (NULL,160,'SVA','Svalbard');

#Pakistan
INSERT INTO zones VALUES (NULL,162,'KHI','Karachi');
INSERT INTO zones VALUES (NULL,162,'LH','Lahore');
INSERT INTO zones VALUES (NULL,162,'ISB','Islamabad');
INSERT INTO zones VALUES (NULL,162,'QUE','Quetta');
INSERT INTO zones VALUES (NULL,162,'PSH','Peshawar');
INSERT INTO zones VALUES (NULL,162,'GUJ','Gujrat');
INSERT INTO zones VALUES (NULL,162,'SAH','Sahiwal');
INSERT INTO zones VALUES (NULL,162,'FSB','Faisalabad');
INSERT INTO zones VALUES (NULL,162,'RIP','Rawal Pindi');

#Romania
INSERT INTO zones VALUES (NULL,175,'AB','Alba');
INSERT INTO zones VALUES (NULL,175,'AR','Arad');
INSERT INTO zones VALUES (NULL,175,'AG','Arges');
INSERT INTO zones VALUES (NULL,175,'BC','Bacau');
INSERT INTO zones VALUES (NULL,175,'BH','Bihor');
INSERT INTO zones VALUES (NULL,175,'BN','Bistrita-Nasaud');
INSERT INTO zones VALUES (NULL,175,'BT','Botosani');
INSERT INTO zones VALUES (NULL,175,'BV','Brasov');
INSERT INTO zones VALUES (NULL,175,'BR','Braila');
INSERT INTO zones VALUES (NULL,175,'B','Bucuresti');
INSERT INTO zones VALUES (NULL,175,'BZ','Buzau');
INSERT INTO zones VALUES (NULL,175,'CS','Caras-Severin');
INSERT INTO zones VALUES (NULL,175,'CL','Calarasi');
INSERT INTO zones VALUES (NULL,175,'CJ','Cluj');
INSERT INTO zones VALUES (NULL,175,'CT','Constanta');
INSERT INTO zones VALUES (NULL,175,'CV','Covasna');
INSERT INTO zones VALUES (NULL,175,'DB','Dimbovita');
INSERT INTO zones VALUES (NULL,175,'DJ','Dolj');
INSERT INTO zones VALUES (NULL,175,'GL','Galati');
INSERT INTO zones VALUES (NULL,175,'GR','Giurgiu');
INSERT INTO zones VALUES (NULL,175,'GJ','Gorj');
INSERT INTO zones VALUES (NULL,175,'HR','Harghita');
INSERT INTO zones VALUES (NULL,175,'HD','Hunedoara');
INSERT INTO zones VALUES (NULL,175,'IL','Ialomita');
INSERT INTO zones VALUES (NULL,175,'IS','Iasi');
INSERT INTO zones VALUES (NULL,175,'IF','Ilfov');
INSERT INTO zones VALUES (NULL,175,'MM','Maramures');
INSERT INTO zones VALUES (NULL,175,'MH','Mehedint');
INSERT INTO zones VALUES (NULL,175,'MS','Mures');
INSERT INTO zones VALUES (NULL,175,'NT','Neamt');
INSERT INTO zones VALUES (NULL,175,'OT','Olt');
INSERT INTO zones VALUES (NULL,175,'PH','Prahova');
INSERT INTO zones VALUES (NULL,175,'SM','Satu-Mare');
INSERT INTO zones VALUES (NULL,175,'SJ','Salaj');
INSERT INTO zones VALUES (NULL,175,'SB','Sibiu');
INSERT INTO zones VALUES (NULL,175,'SV','Suceava');
INSERT INTO zones VALUES (NULL,175,'TR','Teleorman');
INSERT INTO zones VALUES (NULL,175,'TM','Timis');
INSERT INTO zones VALUES (NULL,175,'TL','Tulcea');
INSERT INTO zones VALUES (NULL,175,'VS','Vaslui');
INSERT INTO zones VALUES (NULL,175,'VL','Valcea');
INSERT INTO zones VALUES (NULL,175,'VN','Vrancea');

#South Africa
INSERT INTO zones VALUES (NULL,193,'WP','Western Cape');
INSERT INTO zones VALUES (NULL,193,'GP','Gauteng');
INSERT INTO zones VALUES (NULL,193,'KZN','Kwazulu-Natal');
INSERT INTO zones VALUES (NULL,193,'NC','Northern-Cape');
INSERT INTO zones VALUES (NULL,193,'EC','Eastern-Cape');
INSERT INTO zones VALUES (NULL,193,'MP','Mpumalanga');
INSERT INTO zones VALUES (NULL,193,'NW','North-West');
INSERT INTO zones VALUES (NULL,193,'FS','Free State');
INSERT INTO zones VALUES (NULL,193,'NP','Northern Province');

# Sweden
INSERT INTO zones VALUES (NULL,203,'K','Blekinge');
INSERT INTO zones VALUES (NULL,203,'W','Dalarna');
INSERT INTO zones VALUES (NULL,203,'I','Gotland');
INSERT INTO zones VALUES (NULL,203,'X','Gävleborg');
INSERT INTO zones VALUES (NULL,203,'N','Halland');
INSERT INTO zones VALUES (NULL,203,'Z','Jämtland');
INSERT INTO zones VALUES (NULL,203,'F','Jönköping');
INSERT INTO zones VALUES (NULL,203,'H','Kalmar');
INSERT INTO zones VALUES (NULL,203,'G','Kronoberg');
INSERT INTO zones VALUES (NULL,203,'BD','Norrbotten');
INSERT INTO zones VALUES (NULL,203,'T','Örebro');
INSERT INTO zones VALUES (NULL,203,'E','Östergötland');
INSERT INTO zones VALUES (NULL,203,'M','Skåne');
INSERT INTO zones VALUES (NULL,203,'AB','Stockholm');
INSERT INTO zones VALUES (NULL,203,'D','Södermanland');
INSERT INTO zones VALUES (NULL,203,'C','Uppsala');
INSERT INTO zones VALUES (NULL,203,'S','Värmland');
INSERT INTO zones VALUES (NULL,203,'AC','Västerbotten');
INSERT INTO zones VALUES (NULL,203,'Y','Västernorrland');
INSERT INTO zones VALUES (NULL,203,'U','Västmanland');
INSERT INTO zones VALUES (NULL,203,'O','Västra Götaland');

#Turkey
INSERT INTO zones VALUES (NULL,215,'AA', 'Adana');
INSERT INTO zones VALUES (NULL,215,'AD', 'Adiyaman');
INSERT INTO zones VALUES (NULL,215,'AF', 'Afyonkarahisar');
INSERT INTO zones VALUES (NULL,215,'AG', 'Agri');
INSERT INTO zones VALUES (NULL,215,'AK', 'Aksaray');
INSERT INTO zones VALUES (NULL,215,'AM', 'Amasya');
INSERT INTO zones VALUES (NULL,215,'AN', 'Ankara');
INSERT INTO zones VALUES (NULL,215,'AL', 'Antalya');
INSERT INTO zones VALUES (NULL,215,'AR', 'Ardahan');
INSERT INTO zones VALUES (NULL,215,'AV', 'Artvin');
INSERT INTO zones VALUES (NULL,215,'AY', 'Aydin');
INSERT INTO zones VALUES (NULL,215,'BK', 'Balikesir');
INSERT INTO zones VALUES (NULL,215,'BR', 'Bartin');
INSERT INTO zones VALUES (NULL,215,'BM', 'Batman');
INSERT INTO zones VALUES (NULL,215,'BB', 'Bayburt');
INSERT INTO zones VALUES (NULL,215,'BC', 'Bilecik');
INSERT INTO zones VALUES (NULL,215,'BG', 'Bingöl');
INSERT INTO zones VALUES (NULL,215,'BT', 'Bitlis');
INSERT INTO zones VALUES (NULL,215,'BL', 'Bolu' );
INSERT INTO zones VALUES (NULL,215,'BD', 'Burdur');
INSERT INTO zones VALUES (NULL,215,'BU', 'Bursa');
INSERT INTO zones VALUES (NULL,215,'CK', 'Çanakkale');
INSERT INTO zones VALUES (NULL,215,'CI', 'Çankiri');
INSERT INTO zones VALUES (NULL,215,'CM', 'Çorum');
INSERT INTO zones VALUES (NULL,215,'DN', 'Denizli');
INSERT INTO zones VALUES (NULL,215,'DY', 'Diyarbakir');
INSERT INTO zones VALUES (NULL,215,'DU', 'Düzce');
INSERT INTO zones VALUES (NULL,215,'ED', 'Edirne');
INSERT INTO zones VALUES (NULL,215,'EG', 'Elazig');
INSERT INTO zones VALUES (NULL,215,'EN', 'Erzincan');
INSERT INTO zones VALUES (NULL,215,'EM', 'Erzurum');
INSERT INTO zones VALUES (NULL,215,'ES', 'Eskisehir');
INSERT INTO zones VALUES (NULL,215,'GA', 'Gaziantep');
INSERT INTO zones VALUES (NULL,215,'GI', 'Giresun');
INSERT INTO zones VALUES (NULL,215,'GU', 'Gümüshane');
INSERT INTO zones VALUES (NULL,215,'HK', 'Hakkari');
INSERT INTO zones VALUES (NULL,215,'HT', 'Hatay');
INSERT INTO zones VALUES (NULL,215,'IG', 'Igdir');
INSERT INTO zones VALUES (NULL,215,'IP', 'Isparta');
INSERT INTO zones VALUES (NULL,215,'IB', 'Istanbul');
INSERT INTO zones VALUES (NULL,215,'IZ', 'Izmir');
INSERT INTO zones VALUES (NULL,215,'KM', 'Kahramanmaras');
INSERT INTO zones VALUES (NULL,215,'KB', 'Karabük');
INSERT INTO zones VALUES (NULL,215,'KR', 'Karaman');
INSERT INTO zones VALUES (NULL,215,'KA', 'Kars');
INSERT INTO zones VALUES (NULL,215,'KS', 'Kastamonu');
INSERT INTO zones VALUES (NULL,215,'KY', 'Kayseri');
INSERT INTO zones VALUES (NULL,215,'KI', 'Kilis');
INSERT INTO zones VALUES (NULL,215,'KK', 'Kirikkale');
INSERT INTO zones VALUES (NULL,215,'KL', 'Kirklareli');
INSERT INTO zones VALUES (NULL,215,'KH', 'Kirsehir');
INSERT INTO zones VALUES (NULL,215,'KC', 'Kocaeli');
INSERT INTO zones VALUES (NULL,215,'KO', 'Konya');
INSERT INTO zones VALUES (NULL,215,'KU', 'Kütahya');
INSERT INTO zones VALUES (NULL,215,'ML', 'Malatya');
INSERT INTO zones VALUES (NULL,215,'MN', 'Manisa');
INSERT INTO zones VALUES (NULL,215,'MR', 'Mardin');
INSERT INTO zones VALUES (NULL,215,'IC', 'Mersin');
INSERT INTO zones VALUES (NULL,215,'MG', 'Mugla');
INSERT INTO zones VALUES (NULL,215,'MS', 'Mus');
INSERT INTO zones VALUES (NULL,215,'NV', 'Nevsehir');
INSERT INTO zones VALUES (NULL,215,'NG', 'Nigde');
INSERT INTO zones VALUES (NULL,215,'OR', 'Ordu');
INSERT INTO zones VALUES (NULL,215,'OS', 'Osmaniye');
INSERT INTO zones VALUES (NULL,215,'RI', 'Rize');
INSERT INTO zones VALUES (NULL,215,'SK', 'Sakarya');
INSERT INTO zones VALUES (NULL,215,'SS', 'Samsun');
INSERT INTO zones VALUES (NULL,215,'SU', 'Sanliurfa');
INSERT INTO zones VALUES (NULL,215,'SI', 'Siirt');
INSERT INTO zones VALUES (NULL,215,'SP', 'Sinop');
INSERT INTO zones VALUES (NULL,215,'SR', 'Sirnak');
INSERT INTO zones VALUES (NULL,215,'SV', 'Sivas');
INSERT INTO zones VALUES (NULL,215,'TG', 'Tekirdag');
INSERT INTO zones VALUES (NULL,215,'TT', 'Tokat');
INSERT INTO zones VALUES (NULL,215,'TB', 'Trabzon');
INSERT INTO zones VALUES (NULL,215,'TC', 'Tunceli');
INSERT INTO zones VALUES (NULL,215,'US', 'Usak');
INSERT INTO zones VALUES (NULL,215,'VA', 'Van');
INSERT INTO zones VALUES (NULL,215,'YL', 'Yalova');
INSERT INTO zones VALUES (NULL,215,'YZ', 'Yozgat');
INSERT INTO zones VALUES (NULL,215,'ZO', 'Zonguldak');

#Venezuela
INSERT INTO zones VALUES (NULL,229,'AM','Amazonas');
INSERT INTO zones VALUES (NULL,229,'AN','Anzoátegui');
INSERT INTO zones VALUES (NULL,229,'AR','Aragua');
INSERT INTO zones VALUES (NULL,229,'AP','Apure');
INSERT INTO zones VALUES (NULL,229,'BA','Barinas');
INSERT INTO zones VALUES (NULL,229,'BO','Bolívar');
INSERT INTO zones VALUES (NULL,229,'CA','Carabobo');
INSERT INTO zones VALUES (NULL,229,'CO','Cojedes');
INSERT INTO zones VALUES (NULL,229,'DA','Delta Amacuro');
INSERT INTO zones VALUES (NULL,229,'DC','Distrito Capital');
INSERT INTO zones VALUES (NULL,229,'FA','Falcón');
INSERT INTO zones VALUES (NULL,229,'GA','Guárico');
INSERT INTO zones VALUES (NULL,229,'GU','Guayana');
INSERT INTO zones VALUES (NULL,229,'LA','Lara');
INSERT INTO zones VALUES (NULL,229,'ME','Mérida');
INSERT INTO zones VALUES (NULL,229,'MI','Miranda');
INSERT INTO zones VALUES (NULL,229,'MO','Monagas');
INSERT INTO zones VALUES (NULL,229,'NE','Nueva Esparta');
INSERT INTO zones VALUES (NULL,229,'PO','Portuguesa');
INSERT INTO zones VALUES (NULL,229,'SU','Sucre');
INSERT INTO zones VALUES (NULL,229,'TA','Táchira');
INSERT INTO zones VALUES (NULL,229,'TU','Trujillo');
INSERT INTO zones VALUES (NULL,229,'VA','Vargas');
INSERT INTO zones VALUES (NULL,229,'YA','Yaracuy');
INSERT INTO zones VALUES (NULL,229,'ZU','Zulia');

#UK
INSERT INTO zones VALUES (NULL,222,'BAS','Bath and North East Somerset');
INSERT INTO zones VALUES (NULL,222,'BDF','Bedfordshire');
INSERT INTO zones VALUES (NULL,222,'WBK','Berkshire');
INSERT INTO zones VALUES (NULL,222,'BBD','Blackburn with Darwen');
INSERT INTO zones VALUES (NULL,222,'BPL','Blackpool');
INSERT INTO zones VALUES (NULL,222,'BMH','Bournemouth');
INSERT INTO zones VALUES (NULL,222,'BNH','Brighton and Hove');
INSERT INTO zones VALUES (NULL,222,'BST','Bristol');
INSERT INTO zones VALUES (NULL,222,'BKM','Buckinghamshire');
INSERT INTO zones VALUES (NULL,222,'CAM','Cambridgeshire');
INSERT INTO zones VALUES (NULL,222,'CHS','Cheshire');
INSERT INTO zones VALUES (NULL,222,'CON','Cornwall');
INSERT INTO zones VALUES (NULL,222,'DUR','County Durham');
INSERT INTO zones VALUES (NULL,222,'CMA','Cumbria');
INSERT INTO zones VALUES (NULL,222,'DAL','Darlington');
INSERT INTO zones VALUES (NULL,222,'DER','Derby');
INSERT INTO zones VALUES (NULL,222,'DBY','Derbyshire');
INSERT INTO zones VALUES (NULL,222,'DEV','Devon');
INSERT INTO zones VALUES (NULL,222,'DOR','Dorset');
INSERT INTO zones VALUES (NULL,222,'ERY','East Riding of Yorkshire');
INSERT INTO zones VALUES (NULL,222,'ESX','East Sussex');
INSERT INTO zones VALUES (NULL,222,'ESS','Essex');
INSERT INTO zones VALUES (NULL,222,'GLS','Gloucestershire');
INSERT INTO zones VALUES (NULL,222,'LND','Greater London');
INSERT INTO zones VALUES (NULL,222,'MAN','Greater Manchester');
INSERT INTO zones VALUES (NULL,222,'HAL','Halton');
INSERT INTO zones VALUES (NULL,222,'HAM','Hampshire');
INSERT INTO zones VALUES (NULL,222,'HPL','Hartlepool');
INSERT INTO zones VALUES (NULL,222,'HEF','Herefordshire');
INSERT INTO zones VALUES (NULL,222,'HRT','Hertfordshire');
INSERT INTO zones VALUES (NULL,222,'KHL','Hull');
INSERT INTO zones VALUES (NULL,222,'IOW','Isle of Wight');
INSERT INTO zones VALUES (NULL,222,'KEN','Kent');
INSERT INTO zones VALUES (NULL,222,'LAN','Lancashire');
INSERT INTO zones VALUES (NULL,222,'LCE','Leicester');
INSERT INTO zones VALUES (NULL,222,'LEC','Leicestershire');
INSERT INTO zones VALUES (NULL,222,'LIN','Lincolnshire');
INSERT INTO zones VALUES (NULL,222,'LUT','Luton');
INSERT INTO zones VALUES (NULL,222,'MDW','Medway');
INSERT INTO zones VALUES (NULL,222,'MER','Merseyside');
INSERT INTO zones VALUES (NULL,222,'MDB','Middlesbrough');
INSERT INTO zones VALUES (NULL,222,'MIK','Milton Keynes');
INSERT INTO zones VALUES (NULL,222,'NFK','Norfolk');
INSERT INTO zones VALUES (NULL,222,'NTH','Northamptonshire');
INSERT INTO zones VALUES (NULL,222,'NEL','North East Lincolnshire');
INSERT INTO zones VALUES (NULL,222,'NLN','North Lincolnshire');
INSERT INTO zones VALUES (NULL,222,'NSM','North Somerset');
INSERT INTO zones VALUES (NULL,222,'NBL','Northumberland');
INSERT INTO zones VALUES (NULL,222,'NYK','North Yorkshire');
INSERT INTO zones VALUES (NULL,222,'NGM','Nottingham');
INSERT INTO zones VALUES (NULL,222,'NTT','Nottinghamshire');
INSERT INTO zones VALUES (NULL,222,'OXF','Oxfordshire');
INSERT INTO zones VALUES (NULL,222,'PTE','Peterborough');
INSERT INTO zones VALUES (NULL,222,'PLY','Plymouth');
INSERT INTO zones VALUES (NULL,222,'POL','Poole');
INSERT INTO zones VALUES (NULL,222,'POR','Portsmouth');
INSERT INTO zones VALUES (NULL,222,'RCC','Redcar and Cleveland');
INSERT INTO zones VALUES (NULL,222,'RUT','Rutland');
INSERT INTO zones VALUES (NULL,222,'SHR','Shropshire');
INSERT INTO zones VALUES (NULL,222,'SOM','Somerset');
INSERT INTO zones VALUES (NULL,222,'STH','Southampton');
INSERT INTO zones VALUES (NULL,222,'SOS','Southend-on-Sea');
INSERT INTO zones VALUES (NULL,222,'SGC','South Gloucestershire');
INSERT INTO zones VALUES (NULL,222,'SYK','South Yorkshire');
INSERT INTO zones VALUES (NULL,222,'STS','Staffordshire');
INSERT INTO zones VALUES (NULL,222,'STT','Stockton-on-Tees');
INSERT INTO zones VALUES (NULL,222,'STE','Stoke-on-Trent');
INSERT INTO zones VALUES (NULL,222,'SFK','Suffolk');
INSERT INTO zones VALUES (NULL,222,'SRY','Surrey');
INSERT INTO zones VALUES (NULL,222,'SWD','Swindon');
INSERT INTO zones VALUES (NULL,222,'TFW','Telford and Wrekin');
INSERT INTO zones VALUES (NULL,222,'THR','Thurrock');
INSERT INTO zones VALUES (NULL,222,'TOB','Torbay');
INSERT INTO zones VALUES (NULL,222,'TYW','Tyne and Wear');
INSERT INTO zones VALUES (NULL,222,'WRT','Warrington');
INSERT INTO zones VALUES (NULL,222,'WAR','Warwickshire');
INSERT INTO zones VALUES (NULL,222,'WMI','West Midlands');
INSERT INTO zones VALUES (NULL,222,'WSX','West Sussex');
INSERT INTO zones VALUES (NULL,222,'WYK','West Yorkshire');
INSERT INTO zones VALUES (NULL,222,'WIL','Wiltshire');
INSERT INTO zones VALUES (NULL,222,'WOR','Worcestershire');
INSERT INTO zones VALUES (NULL,222,'YOR','York');


# Keep an empty line at the end of this file for the installer to work properly
