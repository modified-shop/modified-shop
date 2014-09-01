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
#   * To see the 'diff'erence between MySQL databases, use
#   the mysqldiff perl script located in the extras
#   directory of the 'catalog' module.
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
  KEY idx_address_book_customers_id (customers_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS banktransfer_blz;
CREATE TABLE IF NOT EXISTS banktransfer_blz (
  blz int(10) NOT NULL DEFAULT 0,
  bankname varchar(255) NOT NULL DEFAULT '',
  prz char(2) NOT NULL DEFAULT '',
  PRIMARY KEY (blz)
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
  groupname VARCHAR(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM;

DROP TABLE IF EXISTS campaigns;
CREATE TABLE campaigns (
  campaigns_id INT(11) NOT NULL AUTO_INCREMENT,
  campaigns_name VARCHAR(32) NOT NULL DEFAULT '',
  campaigns_refID VARCHAR(64) DEFAULT NULL,
  campaigns_leads INT(11) NOT NULL DEFAULT 0,
  date_added DATETIME DEFAULT NULL,
  last_modified DATETIME DEFAULT NULL,
  PRIMARY KEY (campaigns_id),
  KEY idx_campaigns_name (campaigns_name)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS campaigns_ip;
CREATE TABLE campaigns_ip (
  user_ip VARCHAR(39) NOT NULL,
  time DATETIME NOT NULL,
  campaign VARCHAR(32) NOT NULL
) ENGINE=MyISAM;

DROP TABLE IF EXISTS address_format;
CREATE TABLE address_format (
  address_format_id INT NOT NULL AUTO_INCREMENT,
  address_format VARCHAR(128) NOT NULL,
  address_summary VARCHAR(48) NOT NULL,
  PRIMARY KEY (address_format_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS database_version;
CREATE TABLE database_version (
  version VARCHAR(32) NOT NULL
) ENGINE=MyISAM;

# Tomcraft - 2009-11-02 - set global customers-group-permissions (customers_group)
# web28 - 2010-07-07 - set shop_offline parameter
# Web28 - 2010-11-13 - add missing listproducts
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
  gv_queue INT(1) NOT NULL DEFAULT 0,
  gv_mail INT(1) NOT NULL DEFAULT 0,
  gv_sent INT(1) NOT NULL DEFAULT 0,
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
  stats_campaigns INT(1) NOT NULL DEFAULT 0,

  banner_manager INT(1) NOT NULL DEFAULT 0,
  banner_statistics INT(1) NOT NULL DEFAULT 0,

  module_newsletter INT(1) NOT NULL DEFAULT 0,
  start INT(1) NOT NULL DEFAULT 0,

  content_manager INT(1) NOT NULL DEFAULT 0,
  content_preview INT(1) NOT NULL DEFAULT 0,
  credits INT(1) NOT NULL DEFAULT 0,
  blacklist INT(1) NOT NULL DEFAULT 0,

  orders_edit INT(1) NOT NULL DEFAULT 0,
  popup_image INT(1) NOT NULL DEFAULT 0,
  csv_backend INT(1) NOT NULL DEFAULT 0,
  products_vpe INT(1) NOT NULL DEFAULT 0,
  cross_sell_groups INT(1) NOT NULL DEFAULT 0,

  fck_wrapper INT(1) NOT NULL DEFAULT 0,
  econda INT(1) NOT NULL DEFAULT 0,
  cleverreach INT(1) NOT NULL DEFAULT 0,
  sofortueberweisung_install INT(1) NOT NULL DEFAULT 0,
  shop_offline INT(1) NOT NULL DEFAULT 0,
  xajax INT(1) NOT NULL DEFAULT 0,
  blz_update INT(1) NOT NULL DEFAULT 0,
  removeoldpics INT(1) NOT NULL DEFAULT 0,
  janolaw INT(1) NOT NULL DEFAULT 0,
  haendlerbund INT(1) NOT NULL DEFAULT 0,

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
  KEY orders_id (orders_id)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS banners;
CREATE TABLE banners (
  banners_id INT NOT NULL AUTO_INCREMENT,
  banners_title VARCHAR(64) NOT NULL,
  banners_url VARCHAR(255) NOT NULL,
  banners_image VARCHAR(64) NOT NULL,
  banners_group VARCHAR(10) NOT NULL,
  banners_html_text TEXT,
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
  PRIMARY KEY (banners_history_id)
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
  categories_id INT DEFAULT 0 NOT NULL,
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

DROP TABLE IF EXISTS configuration;
CREATE TABLE configuration (
  configuration_id INT NOT NULL AUTO_INCREMENT,
  configuration_key VARCHAR(64) NOT NULL,
  configuration_value VARCHAR(255) NOT NULL,
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

DROP TABLE IF EXISTS counter;
CREATE TABLE counter (
  startdate CHAR(8),
  counter INT(12)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS counter_history;
CREATE TABLE counter_history (
  month CHAR(8),
  counter INT(12)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS countries;
CREATE TABLE countries (
  countries_id INT NOT NULL AUTO_INCREMENT,
  countries_name VARCHAR(64) NOT NULL,
  countries_iso_code_2 CHAR(2) NOT NULL,
  countries_iso_code_3 CHAR(3) NOT NULL,
  address_format_id INT NOT NULL,
  status INT(1) DEFAULT 1 NULL,
  PRIMARY KEY (countries_id),
  KEY IDX_COUNTRIES_NAME (countries_name)
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
  PRIMARY KEY (currencies_id)
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
  customers_password VARCHAR(50) NOT NULL,
  customers_newsletter CHAR(1),
  customers_newsletter_mode CHAR(1) DEFAULT '0' NOT NULL,
  member_flag CHAR(1) DEFAULT '0' NOT NULL,
  delete_user CHAR(1) DEFAULT '1' NOT NULL,
  account_type INT(1) NOT NULL DEFAULT 0,
  password_request_key VARCHAR(32) NOT NULL,
  payment_unallowed VARCHAR(255) NOT NULL,
  shipping_unallowed VARCHAR(255) NOT NULL,
  refferers_id VARCHAR(32) DEFAULT '0' NOT NULL,
  customers_date_added DATETIME DEFAULT '0000-00-00 00:00:00',
  customers_last_modified DATETIME DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (customers_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS customers_basket;
CREATE TABLE customers_basket (
  customers_basket_id INT NOT NULL AUTO_INCREMENT,
  customers_id INT NOT NULL,
  products_id TINYTEXT NOT NULL,
  customers_basket_quantity INT(2) NOT NULL,
  final_price DECIMAL(15,4) NOT NULL,
  customers_basket_date_added CHAR(8),
  PRIMARY KEY (customers_basket_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS customers_basket_attributes;
CREATE TABLE customers_basket_attributes (
  customers_basket_attributes_id INT NOT NULL AUTO_INCREMENT,
  customers_id INT NOT NULL,
  products_id TINYTEXT NOT NULL,
  products_options_id INT NOT NULL,
  products_options_value_id INT NOT NULL,
  PRIMARY KEY (customers_basket_attributes_id)
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
  KEY customers_id (customers_id)
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
  customers_status_add_tax_ot INT(1) NOT NULL DEFAULT 0,
  customers_status_payment_unallowed VARCHAR(255) NOT NULL,
  customers_status_shipping_unallowed VARCHAR(255) NOT NULL,
  customers_status_discount_attributes INT(1) NOT NULL DEFAULT 0,
  customers_fsk18 INT(1) NOT NULL DEFAULT 1,
  customers_fsk18_display INT(1) NOT NULL DEFAULT 1,
  customers_status_write_reviews INT(1) NOT NULL DEFAULT 1,
  customers_status_read_reviews INT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (customers_status_id,language_id),
  KEY idx_orders_status_name (customers_status_name)
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

# Tomcraft - 2009-11-08 - Added option to deactivate languages (status)
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
  PRIMARY KEY (languages_id),
  KEY idx_languages_name (name)
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
  manufacturers_meta_title VARCHAR(100) NOT NULL,
  manufacturers_meta_description VARCHAR(255) NOT NULL,
  manufacturers_meta_keywords VARCHAR(255) NOT NULL,
  manufacturers_url VARCHAR(255) NOT NULL,
  url_clicked INT(5) NOT NULL DEFAULT 0,
  date_last_click DATETIME NULL,
  PRIMARY KEY (manufacturers_id, languages_id)
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
  PRIMARY KEY (mail_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS newsletters_history;
CREATE TABLE newsletters_history (
  news_hist_id INT(11) NOT NULL DEFAULT 0,
  news_hist_cs INT(11) NOT NULL DEFAULT 0,
  news_hist_cs_date_sent date DEFAULT NULL,
  PRIMARY KEY (news_hist_id)
) ENGINE=MyISAM;

# vr - 2012-10-26 add index idx_customers_id
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
  customers_company VARCHAR(64),
  customers_street_address VARCHAR(64) NOT NULL,
  customers_suburb VARCHAR(32),
  customers_city VARCHAR(64) NOT NULL,
  customers_postcode VARCHAR(10) NOT NULL,
  customers_state VARCHAR(32),
  customers_country VARCHAR(32) NOT NULL,
  customers_telephone VARCHAR(32) NOT NULL,
  customers_email_address VARCHAR(96) NOT NULL,
  customers_address_format_id INT(5) NOT NULL,
  delivery_name VARCHAR(64) NOT NULL,
  delivery_firstname VARCHAR(64) NOT NULL,
  delivery_lastname VARCHAR(64) NOT NULL,
  delivery_company VARCHAR(64),
  delivery_street_address VARCHAR(64) NOT NULL,
  delivery_suburb VARCHAR(32),
  delivery_city VARCHAR(64) NOT NULL,
  delivery_postcode VARCHAR(10) NOT NULL,
  delivery_state VARCHAR(32),
  delivery_country VARCHAR(32) NOT NULL,
  delivery_country_iso_code_2 CHAR(2) NOT NULL,
  delivery_address_format_id INT(5) NOT NULL,
  billing_name VARCHAR(64) NOT NULL,
  billing_firstname VARCHAR(64) NOT NULL,
  billing_lastname VARCHAR(64) NOT NULL,
  billing_company VARCHAR(64),
  billing_street_address VARCHAR(64) NOT NULL,
  billing_suburb VARCHAR(32),
  billing_city VARCHAR(64) NOT NULL,
  billing_postcode VARCHAR(10) NOT NULL,
  billing_state VARCHAR(32),
  billing_country VARCHAR(32) NOT NULL,
  billing_country_iso_code_2 CHAR(2) NOT NULL,
  billing_address_format_id INT(5) NOT NULL,
  payment_method VARCHAR(32) NOT NULL,
  cc_type VARCHAR(20),
  cc_owner VARCHAR(64),
  cc_number VARCHAR(64),
  cc_expires VARCHAR(4),
  cc_start VARCHAR(4) DEFAULT NULL,
  cc_issue VARCHAR(3) DEFAULT NULL,
  cc_cvv VARCHAR(4) DEFAULT NULL,
  comments text,
  last_modified DATETIME,
  date_purchased DATETIME,
  orders_status INT(5) NOT NULL,
  orders_date_finished DATETIME,
  currency CHAR(3),
  currency_value DECIMAL(14,6),
  account_type INT(1) DEFAULT 0 NOT NULL,
  payment_class VARCHAR(32) NOT NULL,
  shipping_method VARCHAR(32) NOT NULL,
  shipping_class VARCHAR(32) NOT NULL,
  customers_ip VARCHAR(39) NOT NULL,
  language VARCHAR(32) NOT NULL,
  afterbuy_success INT(1) DEFAULT 0 NOT NULL,
  afterbuy_id INT(32) DEFAULT 0 NOT NULL,
  refferers_id VARCHAR(32) NOT NULL,
  conversion_type INT(1) DEFAULT 0 NOT NULL,
  orders_ident_key VARCHAR(128),
  ibn_billnr VARCHAR(32) default '',
  ibn_billdate DATE NOT NULL,
  PRIMARY KEY (orders_id),
  KEY idx_customers_id (customers_id)
) ENGINE=MyISAM;

# vr - 2010-04-21 add indices idx_orders_id, idx_products_id
DROP TABLE IF EXISTS orders_products;
CREATE TABLE orders_products (
  orders_products_id INT NOT NULL AUTO_INCREMENT,
  orders_id INT NOT NULL,
  products_id INT NOT NULL,
  products_model VARCHAR(64),
  products_name VARCHAR(255) NOT NULL,
  products_price DECIMAL(15,4) NOT NULL,
  products_discount_made DECIMAL(4,2) DEFAULT NULL,
  products_shipping_time VARCHAR(255) DEFAULT NULL,
  final_price DECIMAL(15,4) NOT NULL,
  products_tax DECIMAL(7,4) NOT NULL,
  products_quantity INT(2) NOT NULL,
  allow_tax INT(1) NOT NULL,
  products_order_description text,
  PRIMARY KEY (orders_products_id),
  KEY idx_orders_id (orders_id),
  KEY idx_products_id (products_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS orders_status;
CREATE TABLE orders_status (
  orders_status_id INT DEFAULT 0 NOT NULL,
  language_id TINYINT DEFAULT 1 NOT NULL,
  orders_status_name VARCHAR(32) NOT NULL,
  PRIMARY KEY (orders_status_id, language_id),
  KEY idx_orders_status_name (orders_status_name)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS shipping_status;
CREATE TABLE shipping_status (
  shipping_status_id INT DEFAULT 0 NOT NULL,
  language_id TINYINT DEFAULT 1 NOT NULL,
  shipping_status_name VARCHAR(32) NOT NULL,
  shipping_status_image VARCHAR(32) NOT NULL,
  PRIMARY KEY (shipping_status_id, language_id),
  KEY idx_shipping_status_name (shipping_status_name)
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

DROP TABLE IF EXISTS orders_products_attributes;
CREATE TABLE orders_products_attributes (
  orders_products_attributes_id INT NOT NULL AUTO_INCREMENT,
  orders_id INT NOT NULL,
  orders_products_id INT NOT NULL,
  products_options VARCHAR(32) NOT NULL,
  products_options_values VARCHAR(64) NOT NULL,
  options_values_price DECIMAL(15,4) NOT NULL,
  price_prefix CHAR(1) NOT NULL,
  orders_products_options_id INT(11) NOT NULL,
  orders_products_options_values_id INT(11) NOT NULL,
  PRIMARY KEY (orders_products_attributes_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS orders_products_download;
CREATE TABLE orders_products_download (
  orders_products_download_id INT NOT NULL AUTO_INCREMENT,
  orders_id INT NOT NULL DEFAULT 0,
  orders_products_id INT NOT NULL DEFAULT 0,
  orders_products_filename VARCHAR(255) NOT NULL DEFAULT '',
  download_maxdays INT(2) NOT NULL DEFAULT 0,
  download_count INT(2) NOT NULL DEFAULT 0,
  PRIMARY KEY (orders_products_download_id)
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
  KEY idx_orders_total_orders_id (orders_id)
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
  products_sort INT(4) NOT NULL DEFAULT 0,
  products_image VARCHAR(64),
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
  KEY idx_products_date_added (products_date_added)
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

DROP TABLE IF EXISTS products_description;
CREATE TABLE products_description (
  products_id INT NOT NULL AUTO_INCREMENT,
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
  PRIMARY KEY (products_id,language_id),
  KEY products_name (products_name)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS products_images;
CREATE TABLE products_images (
  image_id INT NOT NULL AUTO_INCREMENT,
  products_id INT NOT NULL,
  image_nr SMALLINT NOT NULL,
  image_name VARCHAR(254) NOT NULL,
  PRIMARY KEY (image_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS products_notifications;
CREATE TABLE products_notifications (
  products_id INT NOT NULL,
  customers_id INT NOT NULL,
  date_added DATETIME NOT NULL,
  PRIMARY KEY (products_id, customers_id)
) ENGINE=MyISAM;

# Tomcraft - 2009-11-07 - Added sortorder to products_options
DROP TABLE IF EXISTS products_options;
CREATE TABLE products_options (
  products_options_id INT NOT NULL DEFAULT 0,
  language_id TINYINT NOT NULL DEFAULT 1,
  products_options_name VARCHAR(32) NOT NULL DEFAULT '',
  products_options_sortorder INT(11) NOT NULL,
  PRIMARY KEY (products_options_id,language_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS products_options_values;
CREATE TABLE products_options_values (
  products_options_values_id INT NOT NULL DEFAULT 0,
  language_id TINYINT NOT NULL DEFAULT 1,
  products_options_values_name VARCHAR(64) NOT NULL DEFAULT '',
  PRIMARY KEY (products_options_values_id,language_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS products_options_values_to_products_options;
CREATE TABLE products_options_values_to_products_options (
  products_options_values_to_products_options_id INT NOT NULL AUTO_INCREMENT,
  products_options_id INT NOT NULL,
  products_options_values_id INT NOT NULL,
  PRIMARY KEY (products_options_values_to_products_options_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS products_graduated_prices;
CREATE TABLE products_graduated_prices (
  products_id INT(11) NOT NULL DEFAULT 0,
  quantity INT(11) NOT NULL DEFAULT 0,
  unitprice DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
  KEY products_id (products_id)
) ENGINE=MyISAM;

# DokuMan - 2010-10-13 add index idx_categories_id
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
  products_vpe_name VARCHAR(32) NOT NULL DEFAULT ''
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
  PRIMARY KEY (reviews_id)
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
  PRIMARY KEY (sesskey)
) ENGINE=MyISAM;

# BOF - web28 - 2010-07-07 - set shop offline
DROP TABLE IF EXISTS shop_configuration;
CREATE TABLE shop_configuration (
  configuration_id INT(11) NOT NULL AUTO_INCREMENT,
  configuration_key VARCHAR(255) NOT NULL DEFAULT '',
  configuration_value TEXT NOT NULL,
  PRIMARY KEY (configuration_id),
  KEY configuration_key (configuration_key)
) ENGINE=MyISAM;

INSERT INTO shop_configuration (configuration_id, configuration_key, configuration_value) VALUES(NULL, 'SHOP_OFFLINE', '');
INSERT INTO shop_configuration (configuration_id, configuration_key, configuration_value) VALUES(NULL, 'SHOP_OFFLINE_MSG', '<p style="text-align: center;"><span style="font-size: large;"><font face="Arial">Unser Shop ist aufgrund von Wartungsarbeiten im Moment nicht erreichbar.<br /></font><font face="Arial">Bitte besuchen Sie uns zu einem sp&auml;teren Zeitpunkt noch einmal.<br /><br /><br /><br /></font></span><font><font><a href="login_admin.php"><font color="#808080">Login</font></a></font></font><span style="font-size: large;"><font face="Arial"><br /></font></span></p>');
# EOF - web28 - 2010-07-07 - set shop offline

DROP TABLE IF EXISTS specials;
CREATE TABLE specials (
  specials_id INT NOT NULL AUTO_INCREMENT,
  products_id INT NOT NULL,
  specials_quantity INT(4) NOT NULL,
  specials_new_products_price DECIMAL(15,4) NOT NULL,
  specials_date_added DATETIME,
  specials_last_modified DATETIME,
  expires_date DATETIME,
  date_status_change DATETIME,
  status INT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (specials_id),
  KEY idx_specials_products_id (products_id)
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
  PRIMARY KEY (tax_rates_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS geo_zones;
CREATE TABLE geo_zones (
  geo_zone_id INT NOT NULL AUTO_INCREMENT,
  geo_zone_name VARCHAR(32) NOT NULL,
  geo_zone_description VARCHAR(255) NOT NULL,
  last_modified DATETIME NULL,
  date_added DATETIME NOT NULL,
  PRIMARY KEY (geo_zone_id)
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
  http_referer VARCHAR(255) NOT NULL
) ENGINE=MyISAM;

DROP TABLE IF EXISTS zones;
CREATE TABLE zones (
  zone_id INT NOT NULL AUTO_INCREMENT,
  zone_country_id INT NOT NULL,
  zone_code VARCHAR(32) NOT NULL,
  zone_name VARCHAR(32) NOT NULL,
  PRIMARY KEY (zone_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS zones_to_geo_zones;
CREATE TABLE zones_to_geo_zones (
 association_id INT NOT NULL AUTO_INCREMENT,
 zone_country_id INT NOT NULL,
 zone_id INT NULL,
 geo_zone_id INT NULL,
 last_modified DATETIME NULL,
 date_added DATETIME NOT NULL,
 PRIMARY KEY (association_id)
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
  PRIMARY KEY (content_id),
  FULLTEXT (content_meta_title,content_meta_description,content_meta_keywords)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS media_content;
CREATE TABLE media_content (
  file_id INT(11) NOT NULL AUTO_INCREMENT,
  old_filename TEXT NOT NULL,
  new_filename TEXT NOT NULL,
  file_comment TEXT NOT NULL,
  PRIMARY KEY (file_id)
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
  PRIMARY KEY (content_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS module_newsletter;
CREATE TABLE module_newsletter (
  newsletter_id INT(11) NOT NULL AUTO_INCREMENT,
  title TEXT NOT NULL,
  bc TEXT NOT NULL,
  cc TEXT NOT NULL,
  date DATETIME DEFAULT NULL,
  status INT(1) NOT NULL DEFAULT 0,
  body TEXT NOT NULL,
  PRIMARY KEY (newsletter_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS cm_file_flags;
CREATE TABLE cm_file_flags (
  file_flag INT(11) NOT NULL,
  file_flag_name VARCHAR(32) NOT NULL,
  PRIMARY KEY (file_flag)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS payment_moneybookers_currencies;
CREATE TABLE payment_moneybookers_currencies (
  mb_currID CHAR(3) NOT NULL DEFAULT '',
  mb_currName VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (mb_currID)
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

DROP TABLE IF EXISTS payment_moneybookers_countries;
CREATE TABLE payment_moneybookers_countries (
  osc_cID INT(11) NOT NULL DEFAULT 0,
  mb_cID CHAR(3) NOT NULL DEFAULT '',
  PRIMARY KEY (osc_cID)
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
  PRIMARY KEY (unique_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS coupon_gv_customer;
CREATE TABLE coupon_gv_customer (
  customer_id INT(5) NOT NULL DEFAULT 0,
  amount DECIMAL(8,4) NOT NULL DEFAULT 0.0000,
  PRIMARY KEY (customer_id),
  KEY customer_id (customer_id)
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
  KEY uid (unique_id,customer_id,order_id)
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
  restrict_to_products VARCHAR(255) DEFAULT NULL,
  restrict_to_categories VARCHAR(255) DEFAULT NULL,
  restrict_to_customers TEXT,
  coupon_active CHAR(1) NOT NULL DEFAULT 'Y',
  date_created DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  date_modified DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (coupon_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS coupons_description;
CREATE TABLE coupons_description (
  coupon_id INT(11) NOT NULL DEFAULT 0,
  language_id TINYINT NOT NULL DEFAULT 1,
  coupon_name VARCHAR(32) NOT NULL DEFAULT '',
  coupon_description text,
  KEY coupon_id (coupon_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS personal_offers_by_customers_status_0;
DROP TABLE IF EXISTS personal_offers_by_customers_status_1;
DROP TABLE IF EXISTS personal_offers_by_customers_status_2;
DROP TABLE IF EXISTS personal_offers_by_customers_status_3;

#database Version
INSERT INTO database_version(version) VALUES ('MOD_1.0.6.0');

INSERT INTO cm_file_flags (file_flag, file_flag_name) VALUES ('0', 'information');
INSERT INTO cm_file_flags (file_flag, file_flag_name) VALUES ('1', 'content');

INSERT INTO shipping_status VALUES (1, 1, '3-4 Days', '');
INSERT INTO shipping_status VALUES (1, 2, '3-4 Tage', '');
INSERT INTO shipping_status VALUES (2, 1, '1 Week', '');
INSERT INTO shipping_status VALUES (2, 2, '1 Woche', '');
INSERT INTO shipping_status VALUES (3, 1, '2 Weeks', '');
INSERT INTO shipping_status VALUES (3, 2, '2 Wochen', '');

# data
INSERT INTO content_manager VALUES (1, 0, 0, '', 1, 'Shipping &amp; Returns', 'Shipping &amp; Returns', 'Put here your Shipping &amp; Returns information.', 0, 1, '', 1, 1, 0, '', '', '');
INSERT INTO content_manager VALUES (2, 0, 0, '', 1, 'Privacy Notice', 'Privacy Notice', 'Put here your Privacy Notice information.', 0, 1, '', 1, 2, 0, '', '', '');
INSERT INTO content_manager VALUES (3, 0, 0, '', 1, 'Conditions of Use', 'Conditions of Use', 'Conditions of Use<br />Put here your Conditions of Use information.<br /><br /><ol><li>Geltungsbereich</li><li>Vertragspartner</li><li>Angebot und Vertragsschluss</li><li>Widerrufsrecht, Widerrufsbelehrung, Widerrufsfolgen</li><li>Preise und Versandkosten</li><li>Lieferung</li><li>Zahlung</li><li>Eigentumsvorbehalt</li><li>Gew&auml;hrleistung</li></ol>Weitere Informationen', 0, 1, '', 1, 3, 0, '', '', '');
INSERT INTO content_manager VALUES (4, 0, 0, '', 1, 'Imprint', 'Imprint', 'Put here your Company information.<br /><br />DemoShop GmbH<br />Gesch&auml;ftsf&uuml;hrer: Max Muster und Fritz Beispiel<br /><br />Max Muster Stra&szlig;e 21-23<br />D-0815 Musterhausen<br />E-Mail: max.muster@muster.de<br /><br />HRB 123456<br />Amtsgericht Musterhausen<br />UStid-Nr. DE 000 111 222', 0, 1, '', 1, 4, 0, '', '', '');
INSERT INTO content_manager VALUES (5, 0, 0, '', 1, 'Index', 'Welcome', '{$greeting}<br /><br />Dies ist die Standardinstallation der <strong><span style="color:#B0347E;">mod</span><span style="color:#6D6D6D;">ified eCommerce Shopsoftware</span></strong>. Alle dargestellten Produkte dienen zur Demonstration der Funktionsweise. Wenn Sie Produkte bestellen, so werden diese weder ausgeliefert, noch in Rechnung gestellt. <br /><br />Sollten Sie daran interessiert sein das Programm, welches die Grundlage f&uuml;r diesen Shop bildet, einzusetzen, so besuchen Sie bitte die Webseite der <a href="http://www.modified-shop.org" target="_blank"><u><strong><span style="color:#B0347E;">mod</span><span style="color:#6D6D6D;">ified eCommerce Shopsoftware</span></strong></u></a>.<br /><br />Der hier dargestellte Text kann im Adminbereich unter <b>Content Manager</b> - Eintrag Index bearbeitet werden.', 0, 1, '', 0, 5, 0, '', '', '');
INSERT INTO content_manager VALUES (6, 0, 0, '', 2, 'Liefer- und Versandkosten', 'Liefer- und Versandkosten', 'F&uuml;gen Sie hier Ihre Informationen &uuml;ber Liefer- und Versandkosten ein.', 0, 1, '', 1, 1, 0, '', '', '');
INSERT INTO content_manager VALUES (7, 0, 0, '', 2, 'Privatsphäre und Datenschutz', 'Privatsphäre und Datenschutz', 'F&uuml;gen Sie hier Ihre Informationen &uuml;ber Privatsph&auml;re und Datenschutz ein.', 0, 1, '', 1, 2, 0, '', '', '');
INSERT INTO content_manager VALUES (8, 0, 0, '', 2, 'Unsere AGB', 'Allgemeine Geschäftsbedingungen', '<strong>Allgemeine Gesch&auml;ftsbedingungen<br /></strong><br />F&uuml;gen Sie hier Ihre allgemeinen Gesch&auml;ftsbedingungen ein.<br /><br /><ol><li>Geltungsbereich</li><li>Vertragspartner</li><li>Angebot und Vertragsschluss</li><li>Widerrufsrecht, Widerrufsbelehrung, Widerrufsfolgen</li><li>Preise und Versandkosten</li><li>Lieferung</li><li>Zahlung</li><li>Eigentumsvorbehalt</li><li>Gew&auml;hrleistung</li></ol>Weitere Informationen', 0, 1, '', 1, 3, 0, '', '', '');
INSERT INTO content_manager VALUES (9, 0, 0, '', 2, 'Impressum', 'Impressum', 'F&uuml;gen Sie hier Ihr Impressum ein.<br /><br />DemoShop GmbH<br />Gesch&auml;ftsf&uuml;hrer: Max Muster und Fritz Beispiel<br /><br />Max Muster Stra&szlig;e 21-23<br />D-0815 Musterhausen<br />E-Mail: max.muster@muster.de<br /><br />HRB 123456<br />Amtsgericht Musterhausen<br />UStid-Nr. DE 000 111 222', 0, 1, '', 1, 4, 0, '', '', '');
INSERT INTO content_manager VALUES (10, 0, 0, '', 2, 'Index', 'Willkommen', '{$greeting}<br /><br />Dies ist die Standardinstallation der <strong><span style="color:#B0347E;">mod</span><span style="color:#6D6D6D;">ified eCommerce Shopsoftware</span></strong>. Alle dargestellten Produkte dienen zur Demonstration der Funktionsweise. Wenn Sie Produkte bestellen, so werden diese weder ausgeliefert, noch in Rechnung gestellt. <br /><br />Sollten Sie daran interessiert sein das Programm, welches die Grundlage f&uuml;r diesen Shop bildet, einzusetzen, so besuchen Sie bitte die Webseite der <a href="http://www.modified-shop.org" target="_blank"><u><strong><span style="color:#B0347E;">mod</span><span style="color:#6D6D6D;">ified eCommerce Shopsoftware</span></strong></u></a>.<br /><br />Der hier dargestellte Text kann im Adminbereich unter <b>Content Manager</b> - Eintrag Index bearbeitet werden.', 0, 1, '', 0, 5, 0, '', '', '');
INSERT INTO content_manager VALUES (11, 0, 0, '', 1, 'Coupons', 'Coupons FAQ', '<table cellSpacing="0" cellPadding="0">\r\n<tbody>\r\n<tr>\r\n<td class="main"><strong>Buy Gift Vouchers/Coupons </strong></td></tr>\r\n<tr>\r\n<td class="main">If the shop provided gift vouchers or coupons, You can buy them alike all other products. As soon as You have bought and payed the coupon, the shop system will activate Your coupon. You will then see the coupon amount in Your shopping cart. Then You can send the coupon via e-mail by clicking the link "Send Coupon". </td></tr></tbody></table>\r\n<table cellSpacing="0" cellPadding="0">\r\n<tbody>\r\n<tr>\r\n<td class="main"><strong>How to dispatch Coupons </strong></td></tr>\r\n<tr>\r\n<td class="main">To dispatch a coupon, please click the link "Send Coupon" in Your shopping cart. To send the coupon to the correct person, we need the following details: Surname and realname of the recipient and a valid e-mail adress of the recipient, and the desired coupon amount (You can also use only parts of Your balance). Please provide also a short message for the recipient. Please check those information again before You click the "Send Coupon" button. You can change all information at any time before clicking the "Send Coupon" button. </td></tr></tbody></table>\r\n<table cellSpacing="0" cellPadding="0">\r\n<tbody>\r\n<tr>\r\n<td class="main"><strong>How to use Coupons to buy products. </strong></td></tr>\r\n<tr>\r\n<td class="main">As soon as You have a balance, You can use it to pay for Your orders. During the checkout process, You can redeem Your coupon. In case Your balance is less than the value of goods You ordered, You would have to choose Your preferred method of payment for the difference amount. In case Your balance is more than the value of goods You ordered, the remaining amount of Your balance will be saved for Your next order. </td></tr></tbody></table>\r\n<table cellSpacing="0" cellPadding="0">\r\n<tbody>\r\n<tr>\r\n<td class="main"><strong>How to redeem Coupons. </strong></td></tr>\r\n<tr>\r\n<td class="main">In case You have received a coupun via e-mail, You can: <br />1. Click on the link provided in the e-mail. If You do not have an account in this shop already, please create a personal account. <br />2. After having added a product to Your shopping cart, You can enter Your coupon code.</td></tr></tbody></table>\r\n<table cellSpacing="0" cellPadding="0">\r\n<tbody>\r\n<tr>\r\n<td class="main"><strong>Problems?</strong></td></tr>\r\n<tr>\r\n<td class="main">If You have trouble or problems in using Your coupons, please check back with us via our e-mail: you@yourdomain.com. Please describe the encountered problem as detailed as possible! We need the following information to process Your request quickly: Your user id, the coupon code, error messages the shop system returned to You, and the name of the web browser You are using (e.g. "Internet Explorer 6" or "Firefox 1.5"). </td></tr></tbody></table>', 0, 1, '', 0, 6, 1, '', '', '');
INSERT INTO content_manager VALUES (12, 0, 0, '', 2, 'Gutscheine', 'Gutscheine - Fragen und Antworten', '<table cellSpacing="0" cellPadding="0">\r\n<tbody>\r\n<tr>\r\n<td class="main"><strong>Gutscheine kaufen </strong></td></tr>\r\n<tr>\r\n<td class="main">Gutscheine k&ouml;nnen, falls sie im Shop angeboten werden, wie normale Artikel gekauft werden. Sobald Sie einen Gutschein gekauft haben und dieser nach erfolgreicher Zahlung freigeschaltet wurde, erscheint der Betrag unter Ihrem Warenkorb. Nun können Sie über den Link " Gutschein versenden " den gewünschten Betrag per E-Mail versenden.</td></tr></tbody></table>\r\n<table cellSpacing="0" cellPadding="0">\r\n<tbody>\r\n<tr>\r\n<td class="main"><strong>Wie man Gutscheine versendet</strong></td></tr>\r\n<tr>\r\n<td class="main">Um einen Gutschein zu versenden, klicken Sie bitte auf den Link "Gutschein versenden" in Ihrem Einkaufskorb. Um einen Gutschein zu versenden, benötigen wir folgende Angaben von Ihnen: Vor- und Nachname des Empfängers. Eine gültige E-Mail Adresse des Empfängers. Den gewünschten Betrag (Sie können auch Teilbeträge Ihres Guthabens versenden). Eine kurze Nachricht an den Empfänger. Bitte überprüfen Sie Ihre Angaben noch einmal vor dem Versenden. Sie haben vor dem Versenden jederzeit die Möglichkeit Ihre Angaben zu korrigieren.</td></tr></tbody></table>\r\n<table cellSpacing="0" cellPadding="0">\r\n<tbody>\r\n<tr>\r\n<td class="main"><strong>Mit Gutscheinen einkaufen.</strong></td></tr>\r\n<tr>\r\n<td class="main">Sobald Sie über ein Guthaben verfügen, können Sie dieses zum Bezahlen Ihrer Bestellung verwenden. Während des Bestellvorganges haben Sie die Möglichkeit Ihr Guthaben einzulösen. Falls das Guthaben unter dem Warenwert liegt müssen Sie Ihre bevorzugte Zahlungsweise für den Differenzbetrag wählen. Übersteigt Ihr Guthaben den Warenwert, steht Ihnen das Restguthaben selbstverständlich für Ihre nächste Bestellung zur Verfügung.</td></tr></tbody></table>\r\n<table cellSpacing="0" cellPadding="0">\r\n<tbody>\r\n<tr>\r\n<td class="main"><strong>Gutscheine verbuchen. </strong></td></tr>\r\n<tr>\r\n<td class="main">Wenn Sie einen Gutschein per E-Mail erhalten haben, können Sie den Betrag wie folgt verbuchen: <br />1. Klicken Sie auf den in der E-Mail angegebenen Link. Falls Sie noch nicht über ein persönliches Kundenkonto verfügen, haben Sie die Möglichkeit ein Konto zu eröffnen. <br />2. Nachdem Sie ein Produkt in den Warenkorb gelegt haben, können Sie dort Ihren Gutscheincode eingeben.</td></tr></tbody></table>\r\n<table cellSpacing="0" cellPadding="0">\r\n<tbody>\r\n<tr>\r\n<td class="main"><strong>Falls es zu Problemen kommen sollte:</strong></td></tr>\r\n<tr>\r\n<td class="main">Falls es wider Erwarten zu Problemen mit einem Gutschein kommen sollte, kontaktieren Sie uns bitte per E-Mail: you@yourdomain.com. Bitte beschreiben Sie möglichst genau das Problem, wichtige Angaben sind unter anderem: Ihre Kundennummer, der Gutscheincode, Fehlermeldungen des Systems sowie der von Ihnen benutzte Browser (z.B. "Internet Explorer 6" oder "Firefox 1.5"). </td></tr></tbody></table>', 0, 1, '', 0, 6, 1, '', '', '');
INSERT INTO content_manager VALUES (13, 0, 0, '', 2, 'Kontakt', 'Kontakt', 'Ihre Kontaktinformationen', 0, 1, '', 1, 7, 0, '', '', '');
INSERT INTO content_manager VALUES (14, 0, 0, '', 1, 'Contact', 'Contact', 'Please enter your contact information.', 0, 1, '', 1, 7, 0, '', '', '');
INSERT INTO content_manager VALUES (15, 0, 0, '', 1, 'Sitemap', '', '', 0, 0, 'sitemap.php', 1, 8, 0, '', '', '');
INSERT INTO content_manager VALUES (16, 0, 0, '', 2, 'Sitemap', '', '', 0, 0, 'sitemap.php', 1, 8, 0, '', '', '');
# BOF - Tomcraft - 2010-06-09 - Added right of revocation
INSERT INTO content_manager VALUES (17, 0, 0, '', 1, 'Right of revocation', 'Right of revocation', '<p><strong>Right of revocation<br /></strong><br />Add your right of revocation here.</p>', 0, 1, '', 1, 9, 0, '', '', '');
INSERT INTO content_manager VALUES (18, 0, 0, '', 2, 'Widerrufsrecht', 'Widerrufsrecht', '<p><strong>Widerrufsrecht<br /></strong><br />F&uuml;gen Sie hier das Widerrufsrecht ein.</p>', 0, 1, '', 1, 9, 0, '', '', '');
# EOF - Tomcraft - 2010-06-09 - Added right of revocation
# BOF - Tutorial: Umsetzung der EU-Verbraucherrichtlinie vom 13.06.2014
INSERT INTO content_manager VALUES (19, 0, 0, '', 1, 'Revocation form', 'Revocation form', '<p><strong>Revocation form<br /></strong><br />Add your revocation form here.</p>', 0, 1, '', 1, 10, 0, '', '', '');
INSERT INTO content_manager VALUES (20, 0, 0, '', 2, 'Muster-Widerrufsformular', 'Muster-Widerrufsformular', '<p><strong>Muster-Widerrufsformular<br /></strong><br />F&uuml;gen Sie hier das Muster-Widerrufsformular ein.</p>', 0, 1, '', 1, 10, 0, '', '', '');
INSERT INTO content_manager VALUES (21, 0, 0, '', 1, 'Shipping time', 'Shipping time', '<p><strong>Shipping time<br /></strong><br />Add your shipping time informations here.</p>', 0, 1, '', 1, 11, 0, '', '', '');
INSERT INTO content_manager VALUES (22, 0, 0, '', 2, 'Lieferzeit', 'Lieferzeit', '<p><strong>Lieferzeit<br /></strong><br />F&uuml;gen Sie hier Ihre Angaben zur Lieferzeit ein.</p>', 0, 1, '', 1, 11, 0, '', '', '');
# EOF - Tutorial: Umsetzung der EU-Verbraucherrichtlinie vom 13.06.2014

# 1 - Default, 2 - USA, 3 - Spain, 4 - Singapore, 5 - Germany , 6 - Taiwan , 7 - China
INSERT INTO address_format VALUES (1, '$firstname $lastname$cr$streets$cr$city, $postcode$cr$statecomma$country','$city / $country');
INSERT INTO address_format VALUES (2, '$firstname $lastname$cr$streets$cr$city, $state    $postcode$cr$country','$city, $state / $country');
INSERT INTO address_format VALUES (3, '$firstname $lastname$cr$streets$cr$city$cr$postcode - $statecomma$country','$state / $country');
INSERT INTO address_format VALUES (4, '$firstname $lastname$cr$streets$cr$city ($postcode)$cr$country', '$postcode / $country');
INSERT INTO address_format VALUES (5, '$firstname $lastname$cr$streets$cr$postcode $city$cr$country','$city / $country');
# BOF - DokuMan - 2011-03-28 - Added address_format for Taiwan, Ireland, China and Great Britain
INSERT INTO address_format VALUES (6, '$firstname $lastname$cr$streets$cr$city $state $postcode$cr$country','$country / $city');
INSERT INTO address_format VALUES (7, '$firstname $lastname$cr$streets, $city$cr$postcode $state$cr$country','$country / $city');
INSERT INTO address_format VALUES (8, '$firstname $lastname$cr$streets$cr$city$cr$state$cr$postcode$cr$country','$postcode / $country');
# EOF - DokuMan - 2011-03-28 - Added address_format for Taiwan, Ireland, China and Great Britain

# Web28 - 2010-11-13 - add entry for listproducts
INSERT INTO admin_access VALUES ( 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);
INSERT INTO admin_access VALUES ( 'groups', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 2, 2, 2, 2, 3, 3, 3, 3, 3, 3, 3, 3, 4, 4, 4, 4, 2, 4, 2, 2, 2, 2, 5, 5, 5, 5, 5, 5, 5, 5, 5, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 1, 1, 1, 1, 1, 1, 1, 1, 1, 5, 1, 1);

# configuration_group_id 1, My Shop
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'STORE_NAME', 'modified eCommerce Shopsoftware', 1, 1, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'STORE_OWNER', 'modified eCommerce Shopsoftware', 1, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'STORE_OWNER_EMAIL_ADDRESS', 'owner@your-shop.com', 1, 3, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'EMAIL_FROM', 'modified eCommerce Shopsoftware owner@your-shop.com', 1, 4, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'STORE_COUNTRY', '81', 1, 6, NULL, NOW(), 'xtc_get_country_name', 'xtc_cfg_pull_down_country_list(');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'STORE_ZONE', '', 1, 7, NULL, NOW(), 'xtc_cfg_get_zone_name', 'xtc_cfg_pull_down_zone_list(');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'EXPECTED_PRODUCTS_SORT', 'desc', 1, 8, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'asc\', \'desc\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'EXPECTED_PRODUCTS_FIELD', 'date_expected', 1, 9, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'products_name\', \'date_expected\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'USE_DEFAULT_LANGUAGE_CURRENCY', 'false', 1, 10, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'DISPLAY_CART', 'true', 1, 13, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'STORE_NAME_ADDRESS', 'Store Name\nAddress\nCountry\nPhone', 1, 16, NULL, NOW(), NULL, 'xtc_cfg_textarea(');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'SHOW_COUNTS', 'false', 1, 17, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'DEFAULT_CUSTOMERS_STATUS_ID_ADMIN', '0', 1, 20, NULL, NOW(), 'xtc_get_customers_status_name', 'xtc_cfg_pull_down_customers_status_list(');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'DEFAULT_CUSTOMERS_STATUS_ID_GUEST', '1', 1, 21, NULL, NOW(), 'xtc_get_customers_status_name', 'xtc_cfg_pull_down_customers_status_list(');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'DEFAULT_CUSTOMERS_STATUS_ID', '2', 1, 23, NULL, NOW(), 'xtc_get_customers_status_name', 'xtc_cfg_pull_down_customers_status_list(');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'ALLOW_ADD_TO_CART', 'false', 1, 24, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'CURRENT_TEMPLATE', 'xtc5', 1, 26, NULL, NOW(), NULL, 'xtc_cfg_pull_down_template_sets(');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRICE_PRECISION', '4', 1, 28, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'CC_KEYCHAIN', 'changeme', 1, 29, NULL, NOW(), NULL, NULL);


#Web28 - 2012-08-28 - Constants for checkout options
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'CHECKOUT_USE_PRODUCTS_SHORT_DESCRIPTION', 'false', 1, 40, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'CHECKOUT_SHOW_PRODUCTS_IMAGES', 'true', 1, 41, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');


# BOF - hendrik - 2011-05-14 - independent billingnumber and date
#INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'IBN_BILLNR', '1', '1', '99', NULL, NOW(), NULL, NULL);
#INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'IBN_BILLNR_FORMAT', '{n}-{d}-{m}-{y}', '1', '99', NULL, NOW(), NULL, NULL);
# EOF - hendrik - 2011-05-14 - independent billingnumber and date


# configuration_group_id 2, Minimum Values
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'ENTRY_FIRST_NAME_MIN_LENGTH', '2', 2, 1, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'ENTRY_LAST_NAME_MIN_LENGTH', '2', 2, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'ENTRY_DOB_MIN_LENGTH', '10', 2, 3, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'ENTRY_EMAIL_ADDRESS_MIN_LENGTH', '6', 2, 4, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'ENTRY_STREET_ADDRESS_MIN_LENGTH', '5', 2, 5, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'ENTRY_COMPANY_MIN_LENGTH', '2', 2, 6, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'ENTRY_POSTCODE_MIN_LENGTH', '4', 2, 7, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'ENTRY_CITY_MIN_LENGTH', '3', 2, 8, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'ENTRY_STATE_MIN_LENGTH', '0', 2, 9, NULL, NOW(), NULL, NULL); # h-h-h change state_min_length 2 to 0
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'ENTRY_TELEPHONE_MIN_LENGTH', '3', 2, 10, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'ENTRY_PASSWORD_MIN_LENGTH', '5', 2, 11, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'CC_OWNER_MIN_LENGTH', '3', 2, 12, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'CC_NUMBER_MIN_LENGTH', '10', 2, 13, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'REVIEW_TEXT_MIN_LENGTH', '50', 2, 14, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MIN_DISPLAY_BESTSELLERS', '1', 2, 15, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MIN_DISPLAY_ALSO_PURCHASED', '1', 2, 16, NULL, NOW(), NULL, NULL);

# configuration_group_id 3, Maximum Values
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MAX_ADDRESS_BOOK_ENTRIES', '5', 3, 1, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MAX_DISPLAY_SEARCH_RESULTS', '20', 3, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MAX_DISPLAY_PAGE_LINKS', '5', 3, 3, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MAX_DISPLAY_SPECIAL_PRODUCTS', '9', 3, 4, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MAX_DISPLAY_NEW_PRODUCTS', '9', 3, 5, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MAX_DISPLAY_UPCOMING_PRODUCTS', '10', 3, 6, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MAX_DISPLAY_MANUFACTURERS_IN_A_LIST', '0', 3, 7, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MAX_MANUFACTURERS_LIST', '1', 3, 7, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MAX_DISPLAY_MANUFACTURER_NAME_LEN', '15', 3, 8, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MAX_DISPLAY_NEW_REVIEWS', '6', 3, 9, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MAX_RANDOM_SELECT_REVIEWS', '10', 3, 10, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MAX_RANDOM_SELECT_NEW', '10', 3, 11, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MAX_RANDOM_SELECT_SPECIALS', '10', 3, 12, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MAX_DISPLAY_CATEGORIES_PER_ROW', '3', 3, 13, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MAX_DISPLAY_PRODUCTS_NEW', '10', 3, 14, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MAX_DISPLAY_BESTSELLERS', '10', 3, 15, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MAX_DISPLAY_ALSO_PURCHASED', '6', 3, 16, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MAX_DISPLAY_PRODUCTS_IN_ORDER_HISTORY_BOX', '6', 3, 17, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MAX_DISPLAY_ORDER_HISTORY', '10', 3, 18, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRODUCT_REVIEWS_VIEW', '5', 3, 19, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MAX_PRODUCTS_QTY', '1000', 3, 21, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MAX_DISPLAY_NEW_PRODUCTS_DAYS', '30', 3, 22, NULL, NOW(), NULL, NULL);

# configuration_group_id 4, Images Options
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'CONFIG_CALCULATE_IMAGE_SIZE', 'true', 4, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'IMAGE_QUALITY', '100', 4, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRODUCT_IMAGE_THUMBNAIL_WIDTH', '120', 4, 7, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRODUCT_IMAGE_THUMBNAIL_HEIGHT', '80', 4, 8, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRODUCT_IMAGE_INFO_WIDTH', '200', 4, 9, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRODUCT_IMAGE_INFO_HEIGHT', '160', 4, 10, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRODUCT_IMAGE_POPUP_WIDTH', '800', 4, 11, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRODUCT_IMAGE_POPUP_HEIGHT', '640', 4, 12, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRODUCT_IMAGE_THUMBNAIL_BEVEL', '', 4, 13, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRODUCT_IMAGE_THUMBNAIL_GREYSCALE', '', 4, 14, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRODUCT_IMAGE_THUMBNAIL_ELLIPSE', '', 4, 15, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRODUCT_IMAGE_THUMBNAIL_ROUND_EDGES', '', 4, 16, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRODUCT_IMAGE_THUMBNAIL_MERGE', '', 4, 17, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRODUCT_IMAGE_THUMBNAIL_FRAME', '', 4, 18, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRODUCT_IMAGE_THUMBNAIL_DROP_SHADOW', '', 4, 19, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRODUCT_IMAGE_THUMBNAIL_MOTION_BLUR', '', 4, 20, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRODUCT_IMAGE_INFO_BEVEL', '', 4, 21, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRODUCT_IMAGE_INFO_GREYSCALE', '', 4, 22, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRODUCT_IMAGE_INFO_ELLIPSE', '', 4, 23, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRODUCT_IMAGE_INFO_ROUND_EDGES', '', 4, 24, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRODUCT_IMAGE_INFO_MERGE', '', 4, 25, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRODUCT_IMAGE_INFO_FRAME', '', 4, 26, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRODUCT_IMAGE_INFO_DROP_SHADOW', '', 4, 27, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRODUCT_IMAGE_INFO_MOTION_BLUR', '', 4, 28, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRODUCT_IMAGE_POPUP_BEVEL', '', 4, 29, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRODUCT_IMAGE_POPUP_GREYSCALE', '', 4, 30, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRODUCT_IMAGE_POPUP_ELLIPSE', '', 4, 31, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRODUCT_IMAGE_POPUP_ROUND_EDGES', '', 4, 32, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRODUCT_IMAGE_POPUP_MERGE', '', 4, 33, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRODUCT_IMAGE_POPUP_FRAME', '', 4, 34, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRODUCT_IMAGE_POPUP_DROP_SHADOW', '', 4, 35, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRODUCT_IMAGE_POPUP_MOTION_BLUR', '', 4, 36, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MO_PICS', '0', '4', '3', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'IMAGE_MANIPULATOR', 'image_manipulator_GD2.php', '4', '3', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'image_manipulator_GD2.php\', \'image_manipulator_GD2_advanced.php\', \'image_manipulator_GD1.php\'),');

# BOF - Web28 - 2011-03-27 - Option no enlarge product image under default
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRODUCT_IMAGE_NO_ENLARGE_UNDER_DEFAULT', 'false', 4, 6, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(''true'', ''false''), ');
# EOF - Web28 - 2011-03-27 - Option no enlarge product image under default

# configuration_group_id 5, Customer Details
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'ACCOUNT_GENDER', 'true', 5, 10, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'ACCOUNT_DOB', 'true', 5, 20, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'ACCOUNT_COMPANY', 'true', 5, 30, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'ACCOUNT_SUBURB', 'true', 5, 50, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'ACCOUNT_STATE', 'false', 5, 60, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'ACCOUNT_OPTIONS', 'account', 5, 100, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'account\', \'guest\', \'both\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'DELETE_GUEST_ACCOUNT', 'true', 5, 110, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');

# configuration_group_id 6, Module Options
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MODULE_PAYMENT_INSTALLED', '', 6, 0, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MODULE_ORDER_TOTAL_INSTALLED', 'ot_subtotal.php;ot_shipping.php;ot_tax.php;ot_total.php', 6, 0, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MODULE_SHIPPING_INSTALLED', '', 6, 0, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'DEFAULT_CURRENCY', 'EUR', 6, 0, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'DEFAULT_LANGUAGE', 'de', 6, 0, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'DEFAULT_ORDERS_STATUS_ID', '1', 6, 0, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'DEFAULT_PRODUCTS_VPE_ID', '', 6, 0, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'DEFAULT_SHIPPING_STATUS_ID', '1', 6, 0, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MODULE_ORDER_TOTAL_SHIPPING_STATUS', 'true', 6, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER', '30', 6, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING', 'false', 6, 3, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER', '50', 6, 4, NULL, NOW(), 'currencies->format', NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER_INTERNATIONAL', '50', 6, 4, NULL, NOW(), 'currencies->format', NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MODULE_ORDER_TOTAL_SHIPPING_TAX_CLASS', '0', 6, 7, 'xtc_get_tax_class_title', NOW(), 'xtc_cfg_pull_down_tax_classes(', NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MODULE_ORDER_TOTAL_SHIPPING_DESTINATION', 'national', 6, 5, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'national\', \'international\', \'both\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MODULE_ORDER_TOTAL_SUBTOTAL_STATUS', 'true', 6, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MODULE_ORDER_TOTAL_SUBTOTAL_SORT_ORDER', '10', 6, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MODULE_ORDER_TOTAL_TAX_STATUS', 'true', 6, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MODULE_ORDER_TOTAL_TAX_SORT_ORDER', '50', 6, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MODULE_ORDER_TOTAL_TOTAL_STATUS', 'true', 6, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MODULE_ORDER_TOTAL_TOTAL_SORT_ORDER', '99', 6, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MODULE_ORDER_TOTAL_DISCOUNT_STATUS', 'true', 6, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MODULE_ORDER_TOTAL_DISCOUNT_SORT_ORDER', '20', 6, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MODULE_ORDER_TOTAL_SUBTOTAL_NO_TAX_STATUS', 'true', 6, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'MODULE_ORDER_TOTAL_SUBTOTAL_NO_TAX_SORT_ORDER','40', 6, 2, NULL, NOW(), NULL, NULL);

# configuration_group_id 7, Shipping/Packaging
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'SHIPPING_ORIGIN_COUNTRY', '81', 7, 1, NULL, NOW(), 'xtc_get_country_name', 'xtc_cfg_pull_down_country_list(');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'SHIPPING_ORIGIN_ZIP', '', 7, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'SHIPPING_MAX_WEIGHT', '50', 7, 3, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'SHIPPING_BOX_WEIGHT', '3', 7, 4, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'SHIPPING_BOX_PADDING', '10', 7, 5, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'SHOW_SHIPPING', 'true', 7, 6, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'SHIPPING_INFOS', '1', 7, 5, NULL, NOW(), NULL, NULL);
#INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'SHIPPING_DEFAULT_TAX_CLASS_METHOD', '1', 7, 7, NULL, NOW(), 'xtc_get_default_tax_class_method_name', 'xtc_cfg_pull_down_default_tax_class_methods(');

# configuration_group_id 8, Product Listing
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRODUCT_LIST_FILTER', 'true', 8, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'SHOW_BUTTON_BUY_NOW', 'false', 8, 20, '', NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');

# configuration_group_id 9, Stock
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'STOCK_CHECK', 'true', 9, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'ATTRIBUTE_STOCK_CHECK', 'true', 9, 2, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'STOCK_LIMITED', 'true', 9, 3, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'STOCK_ALLOW_CHECKOUT', 'true', 9, 4, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'STOCK_MARK_PRODUCT_OUT_OF_STOCK', '<span style="color:red">***</span>', 9, 5, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'STOCK_REORDER_LEVEL', '5', 9, 6, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'STOCK_CHECKOUT_UPDATE_PRODUCTS_STATUS', 'true', 9, 20, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');

# configuration_group_id 10, Logging
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'STORE_PAGE_PARSE_TIME', 'false', 10, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'STORE_PAGE_PARSE_TIME_LOG', 'page_parse_time.log', 10, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'STORE_PARSE_DATE_TIME_FORMAT', '%d/%m/%Y %H:%M:%S', 10, 3, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'DISPLAY_PAGE_PARSE_TIME', 'true', 10, 4, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'STORE_DB_TRANSACTIONS', 'false', 10, 5, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');

# configuration_group_id 11, Cache
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'USE_CACHE', 'false', 11, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'DIR_FS_CACHE', 'cache', 11, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'CACHE_LIFETIME', '3600', 11, 3, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'CACHE_CHECK', 'true', 11, 4, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'DB_CACHE', 'false', 11, 5, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'DB_CACHE_EXPIRE', '3600', 11, 6, NULL, NOW(), NULL, NULL);

# configuration_group_id 12, E-Mail Options
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'EMAIL_TRANSPORT', 'mail', 12, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'sendmail\', \'smtp\', \'mail\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'SENDMAIL_PATH', '/usr/sbin/sendmail', 12, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'SMTP_MAIN_SERVER', 'localhost', 12, 3, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'SMTP_BACKUP_SERVER', 'localhost', 12, 4, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'SMTP_PORT', '25', 12, 5, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'SMTP_USERNAME', 'Please Enter', 12, 6, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'SMTP_PASSWORD', 'Please Enter', 12, 7, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'SMTP_AUTH', 'false', 12, 8, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'EMAIL_LINEFEED', 'LF', 12, 9, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'LF\', \'CRLF\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'EMAIL_USE_HTML', 'true', 12, 10, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'ENTRY_EMAIL_ADDRESS_CHECK', 'false', 12, 11, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'SEND_EMAILS', 'true', 12, 12, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'USE_CONTACT_EMAIL_ADDRESS', 'false', 12, 13, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'EMAIL_SQL_ERRORS', 'false', '12', '14', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');

# Constants for contact_us
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'CONTACT_US_EMAIL_ADDRESS', 'contact@your-shop.com', 12, 20, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'CONTACT_US_NAME', 'Mail send by Contact_us Form', 12, 21, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'CONTACT_US_REPLY_ADDRESS', '', 12, 22, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'CONTACT_US_REPLY_ADDRESS_NAME', '', 12, 23, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'CONTACT_US_EMAIL_SUBJECT', '', 12, 24, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'CONTACT_US_FORWARDING_STRING', '', 12, 25, NULL, NOW(), NULL, NULL);

# Constants for support system
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'EMAIL_SUPPORT_ADDRESS', 'support@your-shop.com', 12, 26, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'EMAIL_SUPPORT_NAME', 'Mail send by support systems', 12, 27, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'EMAIL_SUPPORT_REPLY_ADDRESS', '', 12, 28, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'EMAIL_SUPPORT_REPLY_ADDRESS_NAME', '', 12, 29, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'EMAIL_SUPPORT_SUBJECT', '', 12, 30, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'EMAIL_SUPPORT_FORWARDING_STRING', '', 12, 31, NULL, NOW(), NULL, NULL);

# Constants for billing system
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'EMAIL_BILLING_ADDRESS', 'billing@your-shop.com', 12, 32, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'EMAIL_BILLING_NAME', 'Mail send by billing systems', 12, 33, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'EMAIL_BILLING_REPLY_ADDRESS', '', 12, 34, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'EMAIL_BILLING_REPLY_ADDRESS_NAME', '', 12, 35, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'EMAIL_BILLING_SUBJECT', 'Ihre Bestellung bei uns', 12, 36, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'EMAIL_BILLING_FORWARDING_STRING', '', 12, 37, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'EMAIL_BILLING_SUBJECT_ORDER', 'Ihre Bestellung {$nr} vom {$date}', 12, 38, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'EMAIL_BILLING_ATTACHMENTS', '', 12, 39, NULL, NOW(), NULL, NULL);

#Web28 - 2012-08-28 Constants for images
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SHOW_IMAGES_IN_EMAIL', 'false', '12', '50', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SHOW_IMAGES_IN_EMAIL_DIR', 'thumbnail', '12', '51', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'thumbnail\', \'info\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SHOW_IMAGES_IN_EMAIL_STYLE', 'max-width:90px;max-height:120px;', '12', '52', NULL, NOW(), NULL, NULL);

# configuration_group_id 13, Download
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'DOWNLOAD_ENABLED', 'false', 13, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'DOWNLOAD_BY_REDIRECT', 'false', 13, 2, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'DOWNLOAD_UNALLOWED_PAYMENT', 'banktransfer,cod,invoice,moneyorder', 13, 5, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'DOWNLOAD_MIN_ORDERS_STATUS', '1', 13, 5, NULL, NOW(), NULL, NULL);

# configuration_group_id 14, GZIP Kompression
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'GZIP_COMPRESSION', 'false', 14, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'GZIP_LEVEL', '5', 14, 2, NULL, NOW(), NULL, NULL);

# configuration_group_id 15, Sessions
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'SESSION_WRITE_DIRECTORY', '/tmp', 15, 1, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'SESSION_FORCE_COOKIE_USE', 'False', 15, 2, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'True\', \'False\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'SESSION_CHECK_SSL_SESSION_ID', 'False', 15, 3, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'True\', \'False\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'SESSION_CHECK_USER_AGENT', 'False', 15, 4, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'True\', \'False\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'SESSION_CHECK_IP_ADDRESS', 'False', 15, 5, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'True\', \'False\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'SESSION_RECREATE', 'False', 15, 7, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'True\', \'False\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SESSION_LIFE_CUSTOMERS', '1440', '15', '20', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'SESSION_LIFE_ADMIN', '7200', '15', '21', NULL, NOW(), NULL, NULL);

# configuration_group_id 16, Meta-Tags/Search engines
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'META_MIN_KEYWORD_LENGTH', '6', 16, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'META_KEYWORDS_NUMBER', '5', 16, 3, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'META_AUTHOR', '', 16, 4, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'META_PUBLISHER', '', 16, 5, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'META_COMPANY', '', 16, 6, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'META_TOPIC', 'shopping', 16, 7, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'META_REPLY_TO', 'xx@xx.com', 16, 8, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'META_REVISIT_AFTER', '5', 16, 9, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'META_ROBOTS', 'index,follow', 16, 10, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'META_DESCRIPTION', '', 16, 11, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'META_KEYWORDS', '', 16, 12, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'SEARCH_ENGINE_FRIENDLY_URLS', 'false', 16, 13, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'CHECK_CLIENT_AGENT', 'true',16, 14, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');

# configuration_group_id 17, Secialmodules
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'USE_WYSIWYG', 'true', 17, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'ACTIVATE_GIFT_SYSTEM', 'false', 17, 2, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'SECURITY_CODE_LENGTH', '10', 17, 3, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'NEW_SIGNUP_GIFT_VOUCHER_AMOUNT', '0', 17, 4, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'NEW_SIGNUP_DISCOUNT_COUPON', '', 17, 5, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'ACTIVATE_SHIPPING_STATUS', 'true', 17, 6, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'DISPLAY_CONDITIONS_ON_CHECKOUT', 'true', 17, 7, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'SHOW_IP_LOG', 'false', 17, 8, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'GROUP_CHECK', 'false', 17, 9, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'ACTIVATE_NAVIGATOR', 'false', 17, 10, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'QUICKLINK_ACTIVATED', 'true', 17, 11, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'ACTIVATE_REVERSE_CROSS_SELLING', 'true', 17, 12, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'DISPLAY_REVOCATION_ON_CHECKOUT', 'true', 17, 13, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
# BOF - Tomcraft - 2010-06-09 - predefined revocation_id
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'REVOCATION_ID', '9', 17, 14, NULL, NOW(), NULL, NULL);
# EOF - Tomcraft - 2010-06-09 - predefined revocation_id

# BOF - DokuMan - 2010-08-13 - Google RSS Feed REFID configuration
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'GOOGLE_RSS_FEED_REFID', '', 17, 15, NULL, NOW(), NULL, NULL);
# EOF - DokuMan - 2010-08-13 - Google RSS Feed REFID configuration

# BOF - Tutorial: Umsetzung der EU-Verbraucherrichtlinie vom 13.06.2014
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'SHIPPING_STATUS_INFOS', '11', 17, 14, NULL, NOW(), NULL, NULL);
# EOF - Tutorial: Umsetzung der EU-Verbraucherrichtlinie vom 13.06.2014

#configuration_group_id 18, VAT reg no
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'ACCOUNT_COMPANY_VAT_CHECK', 'true', 18, 4, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'STORE_OWNER_VAT_ID', '', 18, 3, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'DEFAULT_CUSTOMERS_VAT_STATUS_ID', '1', 18, 23, NULL, NOW(), 'xtc_get_customers_status_name', 'xtc_cfg_pull_down_customers_status_list(');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'ACCOUNT_COMPANY_VAT_LIVE_CHECK', 'true', 18, 4, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'ACCOUNT_COMPANY_VAT_GROUP', 'true', 18, 4, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'ACCOUNT_VAT_BLOCK_ERROR', 'true', 18, 4, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'DEFAULT_CUSTOMERS_VAT_STATUS_ID_LOCAL', '3', '18', '24', '', NOW(), 'xtc_get_customers_status_name', 'xtc_cfg_pull_down_customers_status_list(');

#configuration_group_id 19, Google Conversion
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'GOOGLE_CONVERSION_ID', '', '19', '2', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'GOOGLE_LANG', 'de', '19', '3', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'GOOGLE_CONVERSION', 'false', '19', '0', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');

#configuration_group_id 20, Import/export
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'CSV_TEXTSIGN', '"', '20', '1', NULL, NOW(), NULL, NULL);
# BOF - DokuMan - 2010-02-11 - set DEFAULT separator sign to semicolon ';' instead of tabulator '\t'
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'CSV_SEPERATOR', ';', '20', '2', NULL, NOW(), NULL, NULL);
# EOF - DokuMan - 2010-02-11 - set DEFAULT separator sign to semicolon ';' instead of tabulator '\t'
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'COMPRESS_EXPORT', 'false', '20', '3', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');

#configuration_group_id 21, Afterbuy
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'AFTERBUY_PARTNERID', '', '21', '2', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'AFTERBUY_PARTNERPASS', '', '21', '3', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'AFTERBUY_USERID', '', '21', '4', NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'AFTERBUY_ORDERSTATUS', '1', '21', '5', NULL, NOW(), 'xtc_get_order_status_name' , 'xtc_cfg_pull_down_order_statuses(');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'AFTERBUY_ACTIVATED', 'false', '21', '6', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
#INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'AFTERBUY_DEALERS', '3', '21', '7', NULL , NOW(), NULL , NULL);
#INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'AFTERBUY_IGNORE_GROUPE', '', '21', '8', NULL , NOW(), NULL , NULL);

#configuration_group_id 22, Search Options
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'SEARCH_IN_DESC', 'true', '22', '2', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'SEARCH_IN_ATTR', 'true', '22', '3', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'ADVANCED_SEARCH_DEFAULT_OPERATOR', 'and', '22', '4', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'and\', \'or\'),');

#configuration_group_id 23, econda
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'TRACKING_ECONDA_ACTIVE', 'false', 23, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'TRACKING_ECONDA_ID','', 23, 2, NULL, NOW(), NULL, NULL);

#Dokuman - 2012-08-27 - added entries for new google analytics & piwik tracking
#configuration_group_id 24, google analytics & piwik tracking
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'TRACKING_COUNT_ADMIN_ACTIVE', 'false', 24, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'TRACKING_GOOGLEANALYTICS_ACTIVE', 'false', 24, 2, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'TRACKING_GOOGLEANALYTICS_ID','UA-XXXXXXX-X', 24, 3, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'TRACKING_PIWIK_ACTIVE', 'false', 24, 4, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'TRACKING_PIWIK_LOCAL_PATH','www.domain.de/piwik', 24, 5, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'TRACKING_PIWIK_ID','1', 24, 6, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'TRACKING_PIWIK_GOAL','1', 24, 7, NULL, NOW(), NULL, NULL);

#Dokuman - 2009-10-02 - added entries for new moneybookers payment module version 2.4
#configuration_group_id 31, Moneybookers
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', '_PAYMENT_MONEYBOOKERS_EMAILID', '', 31, 1, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', '_PAYMENT_MONEYBOOKERS_PWD','', 31, 2, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', '_PAYMENT_MONEYBOOKERS_MERCHANTID','', 31, 3, NULL, NOW(), NULL, NULL);
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', '_PAYMENT_MONEYBOOKERS_TMP_STATUS_ID','0', 31, 4, NULL, NOW(), 'xtc_get_order_status_name' , 'xtc_cfg_pull_down_order_statuses(');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', '_PAYMENT_MONEYBOOKERS_PROCESSED_STATUS_ID','0', 31, 5, NULL, NOW(),'xtc_get_order_status_name' , 'xtc_cfg_pull_down_order_statuses(');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', '_PAYMENT_MONEYBOOKERS_PENDING_STATUS_ID','0', 31, 6, NULL, NOW(), 'xtc_get_order_status_name' , 'xtc_cfg_pull_down_order_statuses(');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', '_PAYMENT_MONEYBOOKERS_CANCELED_STATUS_ID','0', 31, 7, NULL, NOW(), 'xtc_get_order_status_name' , 'xtc_cfg_pull_down_order_statuses(');

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
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'PRICE_IS_BRUTTO', 'false', 1000, 10, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'USE_ADMIN_TOP_MENU', 'true', 1000, 20, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'USE_ADMIN_LANG_TABS', 'true', 1000, 21, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
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
#franky_n - 2010-12-24 - added configuration_group entries for econda and moneybookers
INSERT INTO configuration_group VALUES (23,'Econda Tracking','Econda Tracking System',23,1);
INSERT INTO configuration_group VALUES (24,'PIWIK &amp; Google Analytics Tracking','Settings for PIWIK &amp; Google Analytics Tracking',24,1); #Dokuman - 2012-08-27 - added entries for new google analytics & piwik tracking
INSERT INTO configuration_group VALUES (31,'Moneybookers','Moneybookers System',31,1);
INSERT INTO configuration_group VALUES (40,'Popup Window Configuration','Popup Window Parameters',40,1);
INSERT INTO configuration_group VALUES (1000,'Adminarea Options','Adminarea Configuration', 1000,1);

#Countries
INSERT INTO countries VALUES (1,'Afghanistan','AF','AFG',1,1);
INSERT INTO countries VALUES (2,'Albania','AL','ALB',1,1);
INSERT INTO countries VALUES (3,'Algeria','DZ','DZA',1,1);
INSERT INTO countries VALUES (4,'American Samoa','AS','ASM',1,1);
INSERT INTO countries VALUES (5,'Andorra','AD','AND',1,1);
INSERT INTO countries VALUES (6,'Angola','AO','AGO',1,1);
INSERT INTO countries VALUES (7,'Anguilla','AI','AIA',1,1);
INSERT INTO countries VALUES (8,'Antarctica','AQ','ATA',1,1);
INSERT INTO countries VALUES (9,'Antigua and Barbuda','AG','ATG',1,1);
INSERT INTO countries VALUES (10,'Argentina','AR','ARG',1,1);
INSERT INTO countries VALUES (11,'Armenia','AM','ARM',1,1);
INSERT INTO countries VALUES (12,'Aruba','AW','ABW',1,1);
INSERT INTO countries VALUES (13,'Australia','AU','AUD',1,1);
INSERT INTO countries VALUES (14,'Austria','AT','AUT',5,1);
INSERT INTO countries VALUES (15,'Azerbaijan','AZ','AZE',1,1);
INSERT INTO countries VALUES (16,'Bahamas','BS','BHS',1,1);
INSERT INTO countries VALUES (17,'Bahrain','BH','BHR',1,1);
INSERT INTO countries VALUES (18,'Bangladesh','BD','BGD',1,1);
INSERT INTO countries VALUES (19,'Barbados','BB','BRB',1,1);
INSERT INTO countries VALUES (20,'Belarus','BY','BLR',1,1);
INSERT INTO countries VALUES (21,'Belgium','BE','BEL',1,1);
INSERT INTO countries VALUES (22,'Belize','BZ','BLZ',1,1);
INSERT INTO countries VALUES (23,'Benin','BJ','BEN',1,1);
INSERT INTO countries VALUES (24,'Bermuda','BM','BMU',1,1);
INSERT INTO countries VALUES (25,'Bhutan','BT','BTN',1,1);
INSERT INTO countries VALUES (26,'Bolivia','BO','BOL',1,1);
INSERT INTO countries VALUES (27,'Bosnia and Herzegowina','BA','BIH',1,1);
INSERT INTO countries VALUES (28,'Botswana','BW','BWA',1,1);
INSERT INTO countries VALUES (29,'Bouvet Island','BV','BVT',1,1);
INSERT INTO countries VALUES (30,'Brazil','BR','BRA',1,1);
INSERT INTO countries VALUES (31,'British Indian Ocean Territory','IO','IOT',1,1);
INSERT INTO countries VALUES (32,'Brunei Darussalam','BN','BRN',1,1);
INSERT INTO countries VALUES (33,'Bulgaria','BG','BGR',1,1);
INSERT INTO countries VALUES (34,'Burkina Faso','BF','BFA',1,1);
INSERT INTO countries VALUES (35,'Burundi','BI','BDI',1,1);
INSERT INTO countries VALUES (36,'Cambodia','KH','KHM',1,1);
INSERT INTO countries VALUES (37,'Cameroon','CM','CMR',1,1);
INSERT INTO countries VALUES (38,'Canada','CA','CAN',1,1);
INSERT INTO countries VALUES (39,'Cape Verde','CV','CPV',1,1);
INSERT INTO countries VALUES (40,'Cayman Islands','KY','CYM',1,1);
INSERT INTO countries VALUES (41,'Central African Republic','CF','CAF',1,1);
INSERT INTO countries VALUES (42,'Chad','TD','TCD',1,1);
INSERT INTO countries VALUES (43,'Chile','CL','CHL',1,1);
#DokuMan - 2011-03-28 - Added address_format for Taiwan, Ireland, China and Great Britain
INSERT INTO countries VALUES (44,'China','CN','CHN',7,1);
#DokuMan - 2011-03-28 - Added address_format for Taiwan, Ireland, China and Great Britain
INSERT INTO countries VALUES (45,'Christmas Island','CX','CXR',1,1);
INSERT INTO countries VALUES (46,'Cocos (Keeling) Islands','CC','CCK',1,1);
INSERT INTO countries VALUES (47,'Colombia','CO','COL',1,1);
INSERT INTO countries VALUES (48,'Comoros','KM','COM',1,1);
INSERT INTO countries VALUES (49,'Congo','CG','COG',1,1);
INSERT INTO countries VALUES (50,'Cook Islands','CK','COK',1,1);
INSERT INTO countries VALUES (51,'Costa Rica','CR','CRI',1,1);
INSERT INTO countries VALUES (52,'Cote D\'Ivoire','CI','CIV',1,1);
INSERT INTO countries VALUES (53,'Croatia','HR','HRV',1,1);
INSERT INTO countries VALUES (54,'Cuba','CU','CUB',1,1);
INSERT INTO countries VALUES (55,'Cyprus','CY','CYP',1,1);
INSERT INTO countries VALUES (56,'Czech Republic','CZ','CZE',1,1);
INSERT INTO countries VALUES (57,'Denmark','DK','DNK',1,1);
INSERT INTO countries VALUES (58,'Djibouti','DJ','DJI',1,1);
INSERT INTO countries VALUES (59,'Dominica','DM','DMA',1,1);
INSERT INTO countries VALUES (60,'Dominican Republic','DO','DOM',1,1);
INSERT INTO countries VALUES (61,'East Timor','TP','TMP',1,1);
INSERT INTO countries VALUES (62,'Ecuador','EC','ECU',1,1);
INSERT INTO countries VALUES (63,'Egypt','EG','EGY',1,1);
INSERT INTO countries VALUES (64,'El Salvador','SV','SLV',1,1);
INSERT INTO countries VALUES (65,'Equatorial Guinea','GQ','GNQ',1,1);
INSERT INTO countries VALUES (66,'Eritrea','ER','ERI',1,1);
INSERT INTO countries VALUES (67,'Estonia','EE','EST',1,1);
INSERT INTO countries VALUES (68,'Ethiopia','ET','ETH',1,1);
INSERT INTO countries VALUES (69,'Falkland Islands (Malvinas)','FK','FLK',1,1);
INSERT INTO countries VALUES (70,'Faroe Islands','FO','FRO',1,1);
INSERT INTO countries VALUES (71,'Fiji','FJ','FJI',1,1);
INSERT INTO countries VALUES (72,'Finland','FI','FIN',1,1);
INSERT INTO countries VALUES (73,'France','FR','FRA',1,1);
INSERT INTO countries VALUES (74,'France, Metropolitan','FX','FXX',1,1);
INSERT INTO countries VALUES (75,'French Guiana','GF','GUF',1,1);
INSERT INTO countries VALUES (76,'French Polynesia','PF','PYF',1,1);
INSERT INTO countries VALUES (77,'French Southern Territories','TF','ATF',1,1);
INSERT INTO countries VALUES (78,'Gabon','GA','GAB',1,1);
INSERT INTO countries VALUES (79,'Gambia','GM','GMB',1,1);
INSERT INTO countries VALUES (80,'Georgia','GE','GEO',1,1);
INSERT INTO countries VALUES (81,'Germany','DE','DEU',5,1);
INSERT INTO countries VALUES (82,'Ghana','GH','GHA',1,1);
INSERT INTO countries VALUES (83,'Gibraltar','GI','GIB',1,1);
INSERT INTO countries VALUES (84,'Greece','GR','GRC',1,1);
INSERT INTO countries VALUES (85,'Greenland','GL','GRL',1,1);
INSERT INTO countries VALUES (86,'Grenada','GD','GRD',1,1);
INSERT INTO countries VALUES (87,'Guadeloupe','GP','GLP',1,1);
INSERT INTO countries VALUES (88,'Guam','GU','GUM',1,1);
INSERT INTO countries VALUES (89,'Guatemala','GT','GTM',1,1);
INSERT INTO countries VALUES (90,'Guinea','GN','GIN',1,1);
INSERT INTO countries VALUES (91,'Guinea-bissau','GW','GNB',1,1);
INSERT INTO countries VALUES (92,'Guyana','GY','GUY',1,1);
INSERT INTO countries VALUES (93,'Haiti','HT','HTI',1,1);
INSERT INTO countries VALUES (94,'Heard and Mc Donald Islands','HM','HMD',1,1);
INSERT INTO countries VALUES (95,'Honduras','HN','HND',1,1);
INSERT INTO countries VALUES (96,'Hong Kong','HK','HKG',1,1);
INSERT INTO countries VALUES (97,'Hungary','HU','HUN',1,1);
INSERT INTO countries VALUES (98,'Iceland','IS','ISL',1,1);
INSERT INTO countries VALUES (99,'India','IN','IND',1,1);
INSERT INTO countries VALUES (100,'Indonesia','ID','IDN',1,1);
INSERT INTO countries VALUES (101,'Iran (Islamic Republic of)','IR','IRN',1,1);
INSERT INTO countries VALUES (102,'Iraq','IQ','IRQ',1,1);
#DokuMan - 2011-03-28 - Added address_format for Taiwan, Ireland, China and Great Britain
INSERT INTO countries VALUES (103,'Ireland','IE','IRL',6,1);
#DokuMan - 2011-03-28 - Added address_format for Taiwan, Ireland, China and Great Britain
INSERT INTO countries VALUES (104,'Israel','IL','ISR',1,1);
INSERT INTO countries VALUES (105,'Italy','IT','ITA',1,1);
INSERT INTO countries VALUES (106,'Jamaica','JM','JAM',1,1);
INSERT INTO countries VALUES (107,'Japan','JP','JPN',1,1);
INSERT INTO countries VALUES (108,'Jordan','JO','JOR',1,1);
INSERT INTO countries VALUES (109,'Kazakhstan','KZ','KAZ',1,1);
INSERT INTO countries VALUES (110,'Kenya','KE','KEN',1,1);
INSERT INTO countries VALUES (111,'Kiribati','KI','KIR',1,1);
INSERT INTO countries VALUES (112,'Korea, Democratic People\'s Republic of','KP','PRK',1,1);
INSERT INTO countries VALUES (113,'Korea, Republic of','KR','KOR',1,1);
INSERT INTO countries VALUES (114,'Kuwait','KW','KWT',1,1);
INSERT INTO countries VALUES (115,'Kyrgyzstan','KG','KGZ',1,1);
INSERT INTO countries VALUES (116,'Lao People\'s Democratic Republic','LA','LAO',1,1);
INSERT INTO countries VALUES (117,'Latvia','LV','LVA',1,1);
INSERT INTO countries VALUES (118,'Lebanon','LB','LBN',1,1);
INSERT INTO countries VALUES (119,'Lesotho','LS','LSO',1,1);
INSERT INTO countries VALUES (120,'Liberia','LR','LBR',1,1);
INSERT INTO countries VALUES (121,'Libyan Arab Jamahiriya','LY','LBY',1,1);
INSERT INTO countries VALUES (122,'Liechtenstein','LI','LIE',1,1);
INSERT INTO countries VALUES (123,'Lithuania','LT','LTU',1,1);
INSERT INTO countries VALUES (124,'Luxembourg','LU','LUX',1,1);
INSERT INTO countries VALUES (125,'Macau','MO','MAC',1,1);
INSERT INTO countries VALUES (126,'Macedonia, The Former Yugoslav Republic of','MK','MKD',1,1);
INSERT INTO countries VALUES (127,'Madagascar','MG','MDG',1,1);
INSERT INTO countries VALUES (128,'Malawi','MW','MWI',1,1);
INSERT INTO countries VALUES (129,'Malaysia','MY','MYS',1,1);
INSERT INTO countries VALUES (130,'Maldives','MV','MDV',1,1);
INSERT INTO countries VALUES (131,'Mali','ML','MLI',1,1);
INSERT INTO countries VALUES (132,'Malta','MT','MLT',1,1);
INSERT INTO countries VALUES (133,'Marshall Islands','MH','MHL',1,1);
INSERT INTO countries VALUES (134,'Martinique','MQ','MTQ',1,1);
INSERT INTO countries VALUES (135,'Mauritania','MR','MRT',1,1);
INSERT INTO countries VALUES (136,'Mauritius','MU','MUS',1,1);
INSERT INTO countries VALUES (137,'Mayotte','YT','MYT',1,1);
INSERT INTO countries VALUES (138,'Mexico','MX','MEX',1,1);
INSERT INTO countries VALUES (139,'Micronesia, Federated States of','FM','FSM',1,1);
INSERT INTO countries VALUES (140,'Moldova, Republic of','MD','MDA',1,1);
INSERT INTO countries VALUES (141,'Monaco','MC','MCO',1,1);
INSERT INTO countries VALUES (142,'Mongolia','MN','MNG',1,1);
INSERT INTO countries VALUES (143,'Montserrat','MS','MSR',1,1);
INSERT INTO countries VALUES (144,'Morocco','MA','MAR',1,1);
INSERT INTO countries VALUES (145,'Mozambique','MZ','MOZ',1,1);
INSERT INTO countries VALUES (146,'Myanmar','MM','MMR',1,1);
INSERT INTO countries VALUES (147,'Namibia','NA','NAM',1,1);
INSERT INTO countries VALUES (148,'Nauru','NR','NRU',1,1);
INSERT INTO countries VALUES (149,'Nepal','NP','NPL',1,1);
INSERT INTO countries VALUES (150,'Netherlands','NL','NLD',1,1);
INSERT INTO countries VALUES (151,'Netherlands Antilles','AN','ANT',1,1);
INSERT INTO countries VALUES (152,'New Caledonia','NC','NCL',1,1);
INSERT INTO countries VALUES (153,'New Zealand','NZ','NZL',1,1);
INSERT INTO countries VALUES (154,'Nicaragua','NI','NIC',1,1);
INSERT INTO countries VALUES (155,'Niger','NE','NER',1,1);
INSERT INTO countries VALUES (156,'Nigeria','NG','NGA',1,1);
INSERT INTO countries VALUES (157,'Niue','NU','NIU',1,1);
INSERT INTO countries VALUES (158,'Norfolk Island','NF','NFK',1,1);
INSERT INTO countries VALUES (159,'Northern Mariana Islands','MP','MNP',1,1);
INSERT INTO countries VALUES (160,'Norway','NO','NOR',1,1);
INSERT INTO countries VALUES (161,'Oman','OM','OMN',1,1);
INSERT INTO countries VALUES (162,'Pakistan','PK','PAK',1,1);
INSERT INTO countries VALUES (163,'Palau','PW','PLW',1,1);
INSERT INTO countries VALUES (164,'Panama','PA','PAN',1,1);
INSERT INTO countries VALUES (165,'Papua New Guinea','PG','PNG',1,1);
INSERT INTO countries VALUES (166,'Paraguay','PY','PRY',1,1);
INSERT INTO countries VALUES (167,'Peru','PE','PER',1,1);
INSERT INTO countries VALUES (168,'Philippines','PH','PHL',1,1);
INSERT INTO countries VALUES (169,'Pitcairn','PN','PCN',1,1);
INSERT INTO countries VALUES (170,'Poland','PL','POL',1,1);
INSERT INTO countries VALUES (171,'Portugal','PT','PRT',1,1);
INSERT INTO countries VALUES (172,'Puerto Rico','PR','PRI',1,1);
INSERT INTO countries VALUES (173,'Qatar','QA','QAT',1,1);
INSERT INTO countries VALUES (174,'Reunion','RE','REU',1,1);
INSERT INTO countries VALUES (175,'Romania','RO','ROM',1,1);
INSERT INTO countries VALUES (176,'Russian Federation','RU','RUS',1,1);
INSERT INTO countries VALUES (177,'Rwanda','RW','RWA',1,1);
INSERT INTO countries VALUES (178,'Saint Kitts and Nevis','KN','KNA',1,1);
INSERT INTO countries VALUES (179,'Saint Lucia','LC','LCA',1,1);
INSERT INTO countries VALUES (180,'Saint Vincent and the Grenadines','VC','VCT',1,1);
INSERT INTO countries VALUES (181,'Samoa','WS','WSM',1,1);
INSERT INTO countries VALUES (182,'San Marino','SM','SMR',1,1);
INSERT INTO countries VALUES (183,'Sao Tome and Principe','ST','STP',1,1);
INSERT INTO countries VALUES (184,'Saudi Arabia','SA','SAU',1,1);
INSERT INTO countries VALUES (185,'Senegal','SN','SEN',1,1);
INSERT INTO countries VALUES (186,'Seychelles','SC','SYC',1,1);
INSERT INTO countries VALUES (187,'Sierra Leone','SL','SLE',1,1);
INSERT INTO countries VALUES (188,'Singapore','SG','SGP', '4','1');
INSERT INTO countries VALUES (189,'Slovakia (Slovak Republic)','SK','SVK',1,1);
INSERT INTO countries VALUES (190,'Slovenia','SI','SVN',1,1);
INSERT INTO countries VALUES (191,'Solomon Islands','SB','SLB',1,1);
INSERT INTO countries VALUES (192,'Somalia','SO','SOM',1,1);
INSERT INTO countries VALUES (193,'South Africa','ZA','ZAF',1,1);
INSERT INTO countries VALUES (194,'South Georgia and the South Sandwich Islands','GS','SGS',1,1);
INSERT INTO countries VALUES (195,'Spain','ES','ESP','3','1');
INSERT INTO countries VALUES (196,'Sri Lanka','LK','LKA',1,1);
INSERT INTO countries VALUES (197,'St. Helena','SH','SHN',1,1);
INSERT INTO countries VALUES (198,'St. Pierre and Miquelon','PM','SPM',1,1);
INSERT INTO countries VALUES (199,'Sudan','SD','SDN',1,1);
INSERT INTO countries VALUES (200,'Suriname','SR','SUR',1,1);
INSERT INTO countries VALUES (201,'Svalbard and Jan Mayen Islands','SJ','SJM',1,1);
INSERT INTO countries VALUES (202,'Swaziland','SZ','SWZ',1,1);
INSERT INTO countries VALUES (203,'Sweden','SE','SWE',1,1);
INSERT INTO countries VALUES (204,'Switzerland','CH','CHE',5,1);
INSERT INTO countries VALUES (205,'Syrian Arab Republic','SY','SYR',1,1);
#DokuMan - 2011-03-28 - Added address_format for Taiwan, Ireland, China and Great Britain
INSERT INTO countries VALUES (206,'Taiwan','TW','TWN',6,1);
#DokuMan - 2011-03-28 - Added address_format for Taiwan, Ireland, China and Great Britain
INSERT INTO countries VALUES (207,'Tajikistan','TJ','TJK',1,1);
INSERT INTO countries VALUES (208,'Tanzania, United Republic of','TZ','TZA',1,1);
INSERT INTO countries VALUES (209,'Thailand','TH','THA',1,1);
INSERT INTO countries VALUES (210,'Togo','TG','TGO',1,1);
INSERT INTO countries VALUES (211,'Tokelau','TK','TKL',1,1);
INSERT INTO countries VALUES (212,'Tonga','TO','TON',1,1);
INSERT INTO countries VALUES (213,'Trinidad and Tobago','TT','TTO',1,1);
INSERT INTO countries VALUES (214,'Tunisia','TN','TUN',1,1);
INSERT INTO countries VALUES (215,'Turkey','TR','TUR',1,1);
INSERT INTO countries VALUES (216,'Turkmenistan','TM','TKM',1,1);
INSERT INTO countries VALUES (217,'Turks and Caicos Islands','TC','TCA',1,1);
INSERT INTO countries VALUES (218,'Tuvalu','TV','TUV',1,1);
INSERT INTO countries VALUES (219,'Uganda','UG','UGA',1,1);
INSERT INTO countries VALUES (220,'Ukraine','UA','UKR',1,1);
INSERT INTO countries VALUES (221,'United Arab Emirates','AE','ARE',1,1);
#DokuMan - 2011-03-28 - Added address_format for Taiwan, Ireland, China and Great Britain
INSERT INTO countries VALUES (222,'United Kingdom','GB','GBR',8,1);
#DokuMan - 2011-03-28 - Added address_format for Taiwan, Ireland, China and Great Britain
INSERT INTO countries VALUES (223,'United States','US','USA', '2','1');
INSERT INTO countries VALUES (224,'United States Minor Outlying Islands','UM','UMI',1,1);
INSERT INTO countries VALUES (225,'Uruguay','UY','URY',1,1);
INSERT INTO countries VALUES (226,'Uzbekistan','UZ','UZB',1,1);
INSERT INTO countries VALUES (227,'Vanuatu','VU','VUT',1,1);
INSERT INTO countries VALUES (228,'Vatican City State (Holy See)','VA','VAT',1,1);
INSERT INTO countries VALUES (229,'Venezuela','VE','VEN',1,1);
INSERT INTO countries VALUES (230,'Viet Nam','VN','VNM',1,1);
INSERT INTO countries VALUES (231,'Virgin Islands (British)','VG','VGB',1,1);
INSERT INTO countries VALUES (232,'Virgin Islands (U.S.)','VI','VIR',1,1);
INSERT INTO countries VALUES (233,'Wallis and Futuna Islands','WF','WLF',1,1);
INSERT INTO countries VALUES (234,'Western Sahara','EH','ESH',1,1);
INSERT INTO countries VALUES (235,'Yemen','YE','YEM',1,1);
# BOF - Tomcraft - 2010-07-02 - Deleted Yugoslavia
#INSERT INTO countries VALUES (236,'Yugoslavia','YU','YUG',1,1);
# EOF - Tomcraft - 2010-07-02 - Deleted Yugoslavia
INSERT INTO countries VALUES (237,'Zaire','ZR','ZAR',1,1);
INSERT INTO countries VALUES (238,'Zambia','ZM','ZMB',1,1);
INSERT INTO countries VALUES (239,'Zimbabwe','ZW','ZWE',1,1);
# BOF - Tomcraft - 2010-07-02 - Added Serbia & Montenegro
INSERT INTO countries VALUES (240,'Serbia','RS','SRB',1,1);
INSERT INTO countries VALUES (241,'Montenegro','ME','MNE',1,1);
# EOF - Tomcraft - 2010-07-02 - Added Serbia & Montenegro

INSERT INTO currencies VALUES (1,'Euro','EUR','','EUR',',','.','2','1.0000', NOW());

# BOF - Tomcraft - 2009-11-08 - Added option to deactivate languages (status 1)
INSERT INTO languages VALUES (1,'English','en','icon.gif','english',2,'iso-8859-15',1);
INSERT INTO languages VALUES (2,'Deutsch','de','icon.gif','german',1,'iso-8859-15',1);
# EOF - Tomcraft - 2009-11-08 - Added option to deactivate languages (status 1)

INSERT INTO orders_status VALUES (1,1,'Pending');
INSERT INTO orders_status VALUES (1,2,'Offen');
INSERT INTO orders_status VALUES (2,1,'Processing');
INSERT INTO orders_status VALUES (2,2,'In Bearbeitung');
INSERT INTO orders_status VALUES (3,1,'Delivered');
INSERT INTO orders_status VALUES (3,2,'Versendet');

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
# Dokuman - 2009-08-21 - Bundesländer->ISO-3166-2
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
INSERT INTO zones VALUES (113,204,'JU','Graubünden');
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
INSERT INTO zones VALUES (130,195,'A Coruña','A Coruña');
INSERT INTO zones VALUES (131,195,'Alava','Alava');
INSERT INTO zones VALUES (132,195,'Albacete','Albacete');
INSERT INTO zones VALUES (133,195,'Alicante','Alicante');
INSERT INTO zones VALUES (134,195,'Almeria','Almeria');
INSERT INTO zones VALUES (135,195,'Asturias','Asturias');
INSERT INTO zones VALUES (136,195,'Avila','Avila');
INSERT INTO zones VALUES (137,195,'Badajoz','Badajoz');
INSERT INTO zones VALUES (138,195,'Baleares','Baleares');
INSERT INTO zones VALUES (139,195,'Barcelona','Barcelona');
INSERT INTO zones VALUES (140,195,'Burgos','Burgos');
INSERT INTO zones VALUES (141,195,'Caceres','Caceres');
INSERT INTO zones VALUES (142,195,'Cadiz','Cadiz');
INSERT INTO zones VALUES (143,195,'Cantabria','Cantabria');
INSERT INTO zones VALUES (144,195,'Castellon','Castellon');
INSERT INTO zones VALUES (145,195,'Ceuta','Ceuta');
INSERT INTO zones VALUES (146,195,'Ciudad Real','Ciudad Real');
INSERT INTO zones VALUES (147,195,'Cordoba','Cordoba');
INSERT INTO zones VALUES (148,195,'Cuenca','Cuenca');
INSERT INTO zones VALUES (149,195,'Girona','Girona');
INSERT INTO zones VALUES (150,195,'Granada','Granada');
INSERT INTO zones VALUES (151,195,'Guadalajara','Guadalajara');
INSERT INTO zones VALUES (152,195,'Guipuzcoa','Guipuzcoa');
INSERT INTO zones VALUES (153,195,'Huelva','Huelva');
INSERT INTO zones VALUES (154,195,'Huesca','Huesca');
INSERT INTO zones VALUES (155,195,'Jaen','Jaen');
INSERT INTO zones VALUES (156,195,'La Rioja','La Rioja');
INSERT INTO zones VALUES (157,195,'Las Palmas','Las Palmas');
INSERT INTO zones VALUES (158,195,'Leon','Leon');
INSERT INTO zones VALUES (159,195,'Lleida','Lleida');
INSERT INTO zones VALUES (160,195,'Lugo','Lugo');
INSERT INTO zones VALUES (161,195,'Madrid','Madrid');
INSERT INTO zones VALUES (162,195,'Malaga','Malaga');
INSERT INTO zones VALUES (163,195,'Melilla','Melilla');
INSERT INTO zones VALUES (164,195,'Murcia','Murcia');
INSERT INTO zones VALUES (165,195,'Navarra','Navarra');
INSERT INTO zones VALUES (166,195,'Ourense','Ourense');
INSERT INTO zones VALUES (167,195,'Palencia','Palencia');
INSERT INTO zones VALUES (168,195,'Pontevedra','Pontevedra');
INSERT INTO zones VALUES (169,195,'Salamanca','Salamanca');
INSERT INTO zones VALUES (170,195,'Santa Cruz de Tenerife','Santa Cruz de Tenerife');
INSERT INTO zones VALUES (171,195,'Segovia','Segovia');
INSERT INTO zones VALUES (172,195,'Sevilla','Sevilla');
INSERT INTO zones VALUES (173,195,'Soria','Soria');
INSERT INTO zones VALUES (174,195,'Tarragona','Tarragona');
INSERT INTO zones VALUES (175,195,'Teruel','Teruel');
INSERT INTO zones VALUES (176,195,'Toledo','Toledo');
INSERT INTO zones VALUES (177,195,'Valencia','Valencia');
INSERT INTO zones VALUES (178,195,'Valladolid','Valladolid');
INSERT INTO zones VALUES (179,195,'Vizcaya','Vizcaya');
INSERT INTO zones VALUES (180,195,'Zamora','Zamora');
INSERT INTO zones VALUES (181,195,'Zaragoza','Zaragoza');

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
INSERT INTO zones VALUES ('',30,'SP','São Paulo');
INSERT INTO zones VALUES ('',30,'RJ','Rio de Janeiro');
INSERT INTO zones VALUES ('',30,'PE','Pernanbuco');
INSERT INTO zones VALUES ('',30,'BA','Bahia');
INSERT INTO zones VALUES ('',30,'AM','Amazonas');
INSERT INTO zones VALUES ('',30,'MG','Minas Gerais');
INSERT INTO zones VALUES ('',30,'ES','Espirito Santo');
INSERT INTO zones VALUES ('',30,'RS','Rio Grande do Sul');
INSERT INTO zones VALUES ('',30,'PR','Paraná');
INSERT INTO zones VALUES ('',30,'SC','Santa Catarina');
INSERT INTO zones VALUES ('',30,'RG','Rio Grande do Norte');
INSERT INTO zones VALUES ('',30,'MS','Mato Grosso do Sul');
INSERT INTO zones VALUES ('',30,'MT','Mato Grosso');
INSERT INTO zones VALUES ('',30,'GO','Goias');
INSERT INTO zones VALUES ('',30,'TO','Tocantins');
INSERT INTO zones VALUES ('',30,'DF','Distrito Federal');
INSERT INTO zones VALUES ('',30,'RO','Rondonia');
INSERT INTO zones VALUES ('',30,'AC','Acre');
INSERT INTO zones VALUES ('',30,'AP','Amapa');
INSERT INTO zones VALUES ('',30,'RO','Roraima');
INSERT INTO zones VALUES ('',30,'AL','Alagoas');
INSERT INTO zones VALUES ('',30,'CE','Ceará');
INSERT INTO zones VALUES ('',30,'MA','Maranhão');
INSERT INTO zones VALUES ('',30,'PA','Pará');
INSERT INTO zones VALUES ('',30,'PB','Paraíba');
INSERT INTO zones VALUES ('',30,'PI','Piauí');
INSERT INTO zones VALUES ('',30,'SE','Sergipe');

#Chile
INSERT INTO zones VALUES ('',43,'I','I Región de Tarapacá');
INSERT INTO zones VALUES ('',43,'II','II Región de Antofagasta');
INSERT INTO zones VALUES ('',43,'III','III Región de Atacama');
INSERT INTO zones VALUES ('',43,'IV','IV Región de Coquimbo');
INSERT INTO zones VALUES ('',43,'V','V Región de Valaparaíso');
INSERT INTO zones VALUES ('',43,'RM','Región Metropolitana');
INSERT INTO zones VALUES ('',43,'VI','VI Región de L. B. O´higgins');
INSERT INTO zones VALUES ('',43,'VII','VII Región del Maule');
INSERT INTO zones VALUES ('',43,'VIII','VIII Región del Bío Bío');
INSERT INTO zones VALUES ('',43,'IX','IX Región de la Araucanía');
INSERT INTO zones VALUES ('',43,'X','X Región de los Lagos');
INSERT INTO zones VALUES ('',43,'XI','XI Región de Aysén');
INSERT INTO zones VALUES ('',43,'XII','XII Región de Magallanes');

#Columbia
INSERT INTO zones VALUES ('',47,'AMA','Amazonas');
INSERT INTO zones VALUES ('',47,'ANT','Antioquia');
INSERT INTO zones VALUES ('',47,'ARA','Arauca');
INSERT INTO zones VALUES ('',47,'ATL','Atlantico');
INSERT INTO zones VALUES ('',47,'BOL','Bolivar');
INSERT INTO zones VALUES ('',47,'BOY','Boyaca');
INSERT INTO zones VALUES ('',47,'CAL','Caldas');
INSERT INTO zones VALUES ('',47,'CAQ','Caqueta');
INSERT INTO zones VALUES ('',47,'CAS','Casanare');
INSERT INTO zones VALUES ('',47,'CAU','Cauca');
INSERT INTO zones VALUES ('',47,'CES','Cesar');
INSERT INTO zones VALUES ('',47,'CHO','Choco');
INSERT INTO zones VALUES ('',47,'COR','Cordoba');
INSERT INTO zones VALUES ('',47,'CUN','Cundinamarca');
INSERT INTO zones VALUES ('',47,'HUI','Huila');
INSERT INTO zones VALUES ('',47,'GUA','Guainia');
INSERT INTO zones VALUES ('',47,'GUA','Guajira');
INSERT INTO zones VALUES ('',47,'GUV','Guaviare');
INSERT INTO zones VALUES ('',47,'MAG','Magdalena');
INSERT INTO zones VALUES ('',47,'MET','Meta');
INSERT INTO zones VALUES ('',47,'NAR','Narino');
INSERT INTO zones VALUES ('',47,'NDS','Norte de Santander');
INSERT INTO zones VALUES ('',47,'PUT','Putumayo');
INSERT INTO zones VALUES ('',47,'QUI','Quindio');
INSERT INTO zones VALUES ('',47,'RIS','Risaralda');
INSERT INTO zones VALUES ('',47,'SAI','San Andres Islas');
INSERT INTO zones VALUES ('',47,'SAN','Santander');
INSERT INTO zones VALUES ('',47,'SUC','Sucre');
INSERT INTO zones VALUES ('',47,'TOL','Tolima');
INSERT INTO zones VALUES ('',47,'VAL','Valle');
INSERT INTO zones VALUES ('',47,'VAU','Vaupes');
INSERT INTO zones VALUES ('',47,'VIC','Vichada');

#France
# BOF - web28 - 2010-07-07 - FIX special character
INSERT INTO zones VALUES ('',73,'Et','Etranger');
INSERT INTO zones VALUES ('',73,'01','Ain');
INSERT INTO zones VALUES ('',73,'02','Aisne');
INSERT INTO zones VALUES ('',73,'03','Allier');
INSERT INTO zones VALUES ('',73,'04','Alpes de Haute Provence');
INSERT INTO zones VALUES ('',73,'05','Hautes-Alpes');
INSERT INTO zones VALUES ('',73,'06','Alpes Maritimes');
INSERT INTO zones VALUES ('',73,'07','Ardèche');
INSERT INTO zones VALUES ('',73,'08','Ardennes');
INSERT INTO zones VALUES ('',73,'09','Ariège');
INSERT INTO zones VALUES ('',73,'10','Aube');
INSERT INTO zones VALUES ('',73,'11','Aude');
INSERT INTO zones VALUES ('',73,'12','Aveyron');
INSERT INTO zones VALUES ('',73,'13','Bouches-du-Rhône');
INSERT INTO zones VALUES ('',73,'14','Calvados');
INSERT INTO zones VALUES ('',73,'15','Cantal');
INSERT INTO zones VALUES ('',73,'16','Charente');
INSERT INTO zones VALUES ('',73,'17','Charente Maritime');
INSERT INTO zones VALUES ('',73,'18','Cher');
INSERT INTO zones VALUES ('',73,'19','Corrèze');
INSERT INTO zones VALUES ('',73,'2A','Corse du Sud');
INSERT INTO zones VALUES ('',73,'2B','Haute Corse');
INSERT INTO zones VALUES ('',73,'21','Côte-d\'Or');
INSERT INTO zones VALUES ('',73,'22','Côtes-d\'Armor');
INSERT INTO zones VALUES ('',73,'23','Creuse');
INSERT INTO zones VALUES ('',73,'24','Dordogne');
INSERT INTO zones VALUES ('',73,'25','Doubs');
INSERT INTO zones VALUES ('',73,'26','Drôme');
INSERT INTO zones VALUES ('',73,'27','Eure');
INSERT INTO zones VALUES ('',73,'28','Eure et Loir');
INSERT INTO zones VALUES ('',73,'29','Finistère');
INSERT INTO zones VALUES ('',73,'30','Gard');
INSERT INTO zones VALUES ('',73,'31','Haute Garonne');
INSERT INTO zones VALUES ('',73,'32','Gers');
INSERT INTO zones VALUES ('',73,'33','Gironde');
INSERT INTO zones VALUES ('',73,'34','Hérault');
INSERT INTO zones VALUES ('',73,'35','Ille et Vilaine');
INSERT INTO zones VALUES ('',73,'36','Indre');
INSERT INTO zones VALUES ('',73,'37','Indre et Loire');
INSERT INTO zones VALUES ('',73,'38','Isère');
INSERT INTO zones VALUES ('',73,'39','Jura');
INSERT INTO zones VALUES ('',73,'40','Landes');
INSERT INTO zones VALUES ('',73,'41','Loir et Cher');
INSERT INTO zones VALUES ('',73,'42','Loire');
INSERT INTO zones VALUES ('',73,'43','Haute Loire');
INSERT INTO zones VALUES ('',73,'44','Loire Atlantique');
INSERT INTO zones VALUES ('',73,'45','Loiret');
INSERT INTO zones VALUES ('',73,'46','Lot');
INSERT INTO zones VALUES ('',73,'47','Lot et Garonne');
INSERT INTO zones VALUES ('',73,'48','Lozère');
INSERT INTO zones VALUES ('',73,'49','Maine et Loire');
INSERT INTO zones VALUES ('',73,'50','Manche');
INSERT INTO zones VALUES ('',73,'51','Marne');
INSERT INTO zones VALUES ('',73,'52','Haute Marne');
INSERT INTO zones VALUES ('',73,'53','Mayenne');
INSERT INTO zones VALUES ('',73,'54','Meurthe et Moselle');
INSERT INTO zones VALUES ('',73,'55','Meuse');
INSERT INTO zones VALUES ('',73,'56','Morbihan');
INSERT INTO zones VALUES ('',73,'57','Moselle');
INSERT INTO zones VALUES ('',73,'58','Nièvre');
INSERT INTO zones VALUES ('',73,'59','Nord');
INSERT INTO zones VALUES ('',73,'60','Oise');
INSERT INTO zones VALUES ('',73,'61','Orne');
INSERT INTO zones VALUES ('',73,'62','Pas de Calais');
INSERT INTO zones VALUES ('',73,'63','Puy-de-Dôme');
INSERT INTO zones VALUES ('',73,'64','Pyrénées-Atlantiques');
INSERT INTO zones VALUES ('',73,'65','Hautes-Pyrénées');
INSERT INTO zones VALUES ('',73,'66','Pyrénées-Orientales');
INSERT INTO zones VALUES ('',73,'67','Bas Rhin');
INSERT INTO zones VALUES ('',73,'68','Haut Rhin');
INSERT INTO zones VALUES ('',73,'69','Rhône');
INSERT INTO zones VALUES ('',73,'70','Haute-Saône');
INSERT INTO zones VALUES ('',73,'71','Saône-et-Loire');
INSERT INTO zones VALUES ('',73,'72','Sarthe');
INSERT INTO zones VALUES ('',73,'73','Savoie');
INSERT INTO zones VALUES ('',73,'74','Haute Savoie');
INSERT INTO zones VALUES ('',73,'75','Paris');
INSERT INTO zones VALUES ('',73,'76','Seine Maritime');
INSERT INTO zones VALUES ('',73,'77','Seine et Marne');
INSERT INTO zones VALUES ('',73,'78','Yvelines');
INSERT INTO zones VALUES ('',73,'79','Deux-Sèvres');
INSERT INTO zones VALUES ('',73,'80','Somme');
INSERT INTO zones VALUES ('',73,'81','Tarn');
INSERT INTO zones VALUES ('',73,'82','Tarn et Garonne');
INSERT INTO zones VALUES ('',73,'83','Var');
INSERT INTO zones VALUES ('',73,'84','Vaucluse');
INSERT INTO zones VALUES ('',73,'85','Vendée');
INSERT INTO zones VALUES ('',73,'86','Vienne');
INSERT INTO zones VALUES ('',73,'87','Haute Vienne');
INSERT INTO zones VALUES ('',73,'88','Vosges');
INSERT INTO zones VALUES ('',73,'89','Yonne');
INSERT INTO zones VALUES ('',73,'90','Territoire de Belfort');
INSERT INTO zones VALUES ('',73,'91','Essonne');
INSERT INTO zones VALUES ('',73,'92','Hauts de Seine');
INSERT INTO zones VALUES ('',73,'93','Seine St-Denis');
INSERT INTO zones VALUES ('',73,'94','Val de Marne');
INSERT INTO zones VALUES ('',73,'95','Val d\'Oise');
INSERT INTO zones VALUES ('',73,'971 (DOM)','Guadeloupe');
INSERT INTO zones VALUES ('',73,'972 (DOM)','Martinique');
INSERT INTO zones VALUES ('',73,'973 (DOM)','Guyane');
INSERT INTO zones VALUES ('',73,'974 (DOM)','Saint Denis');
INSERT INTO zones VALUES ('',73,'975 (DOM)','St-Pierre de Miquelon');
INSERT INTO zones VALUES ('',73,'976 (TOM)','Mayotte');
INSERT INTO zones VALUES ('',73,'984 (TOM)','Terres australes et Antartiques françaises');
INSERT INTO zones VALUES ('',73,'985 (TOM)','Nouvelle Calédonie');
INSERT INTO zones VALUES ('',73,'986 (TOM)','Wallis et Futuna');
INSERT INTO zones VALUES ('',73,'987 (TOM)','Polynésie française');
# EOF - web28 - 2010-07-07 - FIX special character

#India
INSERT INTO zones VALUES ('',99,'DL','Delhi');
INSERT INTO zones VALUES ('',99,'MH','Maharashtra');
INSERT INTO zones VALUES ('',99,'TN','Tamil Nadu');
INSERT INTO zones VALUES ('',99,'KL','Kerala');
INSERT INTO zones VALUES ('',99,'AP','Andhra Pradesh');
INSERT INTO zones VALUES ('',99,'KA','Karnataka');
INSERT INTO zones VALUES ('',99,'GA','Goa');
INSERT INTO zones VALUES ('',99,'MP','Madhya Pradesh');
INSERT INTO zones VALUES ('',99,'PY','Pondicherry');
INSERT INTO zones VALUES ('',99,'GJ','Gujarat');
INSERT INTO zones VALUES ('',99,'OR','Orrisa');
INSERT INTO zones VALUES ('',99,'CA','Chhatisgarh');
INSERT INTO zones VALUES ('',99,'JH','Jharkhand');
INSERT INTO zones VALUES ('',99,'BR','Bihar');
INSERT INTO zones VALUES ('',99,'WB','West Bengal');
INSERT INTO zones VALUES ('',99,'UP','Uttar Pradesh');
INSERT INTO zones VALUES ('',99,'RJ','Rajasthan');
INSERT INTO zones VALUES ('',99,'PB','Punjab');
INSERT INTO zones VALUES ('',99,'HR','Haryana');
INSERT INTO zones VALUES ('',99,'CH','Chandigarh');
INSERT INTO zones VALUES ('',99,'JK','Jammu & Kashmir');
INSERT INTO zones VALUES ('',99,'HP','Himachal Pradesh');
INSERT INTO zones VALUES ('',99,'UA','Uttaranchal');
INSERT INTO zones VALUES ('',99,'LK','Lakshadweep');
INSERT INTO zones VALUES ('',99,'AN','Andaman & Nicobar');
INSERT INTO zones VALUES ('',99,'MG','Meghalaya');
INSERT INTO zones VALUES ('',99,'AS','Assam');
INSERT INTO zones VALUES ('',99,'DR','Dadra & Nagar Haveli');
INSERT INTO zones VALUES ('',99,'DN','Daman & Diu');
INSERT INTO zones VALUES ('',99,'SK','Sikkim');
INSERT INTO zones VALUES ('',99,'TR','Tripura');
INSERT INTO zones VALUES ('',99,'MZ','Mizoram');
INSERT INTO zones VALUES ('',99,'MN','Manipur');
INSERT INTO zones VALUES ('',99,'NL','Nagaland');
INSERT INTO zones VALUES ('',99,'AR','Arunachal Pradesh');

#Italy
INSERT INTO zones VALUES ('',105,'AG','Agrigento');
INSERT INTO zones VALUES ('',105,'AL','Alessandria');
INSERT INTO zones VALUES ('',105,'AN','Ancona');
INSERT INTO zones VALUES ('',105,'AO','Aosta');
INSERT INTO zones VALUES ('',105,'AR','Arezzo');
INSERT INTO zones VALUES ('',105,'AP','Ascoli Piceno');
INSERT INTO zones VALUES ('',105,'AT','Asti');
INSERT INTO zones VALUES ('',105,'AV','Avellino');
INSERT INTO zones VALUES ('',105,'BA','Bari');
INSERT INTO zones VALUES ('',105,'BT','Barletta-Andria-Trani');
INSERT INTO zones VALUES ('',105,'BL','Belluno');
INSERT INTO zones VALUES ('',105,'BN','Benevento');
INSERT INTO zones VALUES ('',105,'BG','Bergamo');
INSERT INTO zones VALUES ('',105,'BI','Biella');
INSERT INTO zones VALUES ('',105,'BO','Bologna');
INSERT INTO zones VALUES ('',105,'BZ','Bolzano');
INSERT INTO zones VALUES ('',105,'BS','Brescia');
INSERT INTO zones VALUES ('',105,'BR','Brindisi');
INSERT INTO zones VALUES ('',105,'CA','Cagliari');
INSERT INTO zones VALUES ('',105,'CL','Caltanissetta');
INSERT INTO zones VALUES ('',105,'CB','Campobasso');
INSERT INTO zones VALUES ('',105,'CI','Carbonia-Iglesias');
INSERT INTO zones VALUES ('',105,'CE','Caserta');
INSERT INTO zones VALUES ('',105,'CT','Catania');
INSERT INTO zones VALUES ('',105,'CZ','Catanzaro');
INSERT INTO zones VALUES ('',105,'CH','Chieti');
INSERT INTO zones VALUES ('',105,'CO','Como');
INSERT INTO zones VALUES ('',105,'CS','Cosenza');
INSERT INTO zones VALUES ('',105,'CR','Cremona');
INSERT INTO zones VALUES ('',105,'KR','Crotone');
INSERT INTO zones VALUES ('',105,'CN','Cuneo');
INSERT INTO zones VALUES ('',105,'EN','Enna');
INSERT INTO zones VALUES ('',105,'FM','Fermo');
INSERT INTO zones VALUES ('',105,'FE','Ferrara');
INSERT INTO zones VALUES ('',105,'FI','Firenze');
INSERT INTO zones VALUES ('',105,'FG','Foggia');
INSERT INTO zones VALUES ('',105,'FC','Forlì-Cesena');
INSERT INTO zones VALUES ('',105,'FR','Frosinone');
INSERT INTO zones VALUES ('',105,'GE','Genova');
INSERT INTO zones VALUES ('',105,'GO','Gorizia');
INSERT INTO zones VALUES ('',105,'GR','Grosseto');
INSERT INTO zones VALUES ('',105,'IM','Imperia');
INSERT INTO zones VALUES ('',105,'IS','Isernia');
INSERT INTO zones VALUES ('',105,'SP','La Spezia');
INSERT INTO zones VALUES ('',105,'AQ','Aquila');
INSERT INTO zones VALUES ('',105,'LT','Latina');
INSERT INTO zones VALUES ('',105,'LE','Lecce');
INSERT INTO zones VALUES ('',105,'LC','Lecco');
INSERT INTO zones VALUES ('',105,'LI','Livorno');
INSERT INTO zones VALUES ('',105,'LO','Lodi');
INSERT INTO zones VALUES ('',105,'LU','Lucca');
INSERT INTO zones VALUES ('',105,'MC','Macerata');
INSERT INTO zones VALUES ('',105,'MN','Mantova');
INSERT INTO zones VALUES ('',105,'MS','Massa-Carrara');
INSERT INTO zones VALUES ('',105,'MT','Matera');
INSERT INTO zones VALUES ('',105,'ME','Messina');
INSERT INTO zones VALUES ('',105,'MI','Milano');
INSERT INTO zones VALUES ('',105,'MO','Modena');
INSERT INTO zones VALUES ('',105,'MB','Monza e della Brianza');
INSERT INTO zones VALUES ('',105,'NA','Napoli');
INSERT INTO zones VALUES ('',105,'NO','Novara');
INSERT INTO zones VALUES ('',105,'NU','Nuoro');
INSERT INTO zones VALUES ('',105,'OT','Olbia-Tempio');
INSERT INTO zones VALUES ('',105,'OR','Oristano');
INSERT INTO zones VALUES ('',105,'PD','Padova');
INSERT INTO zones VALUES ('',105,'PA','Palermo');
INSERT INTO zones VALUES ('',105,'PR','Parma');
INSERT INTO zones VALUES ('',105,'PV','Pavia');
INSERT INTO zones VALUES ('',105,'PG','Perugia');
INSERT INTO zones VALUES ('',105,'PU','Pesaro e Urbino');
INSERT INTO zones VALUES ('',105,'PE','Pescara');
INSERT INTO zones VALUES ('',105,'PC','Piacenza');
INSERT INTO zones VALUES ('',105,'PI','Pisa');
INSERT INTO zones VALUES ('',105,'PT','Pistoia');
INSERT INTO zones VALUES ('',105,'PN','Pordenone');
INSERT INTO zones VALUES ('',105,'PZ','Potenza');
INSERT INTO zones VALUES ('',105,'PO','Prato');
INSERT INTO zones VALUES ('',105,'RG','Ragusa');
INSERT INTO zones VALUES ('',105,'RA','Ravenna');
INSERT INTO zones VALUES ('',105,'RC','Reggio di Calabria');
INSERT INTO zones VALUES ('',105,'RE','Reggio Emilia');
INSERT INTO zones VALUES ('',105,'RI','Rieti');
INSERT INTO zones VALUES ('',105,'RN','Rimini');
INSERT INTO zones VALUES ('',105,'RM','Roma');
INSERT INTO zones VALUES ('',105,'RO','Rovigo');
INSERT INTO zones VALUES ('',105,'SA','Salerno');
INSERT INTO zones VALUES ('',105,'VS','Medio Campidano');
INSERT INTO zones VALUES ('',105,'SS','Sassari');
INSERT INTO zones VALUES ('',105,'SV','Savona');
INSERT INTO zones VALUES ('',105,'SI','Siena');
INSERT INTO zones VALUES ('',105,'SR','Siracusa');
INSERT INTO zones VALUES ('',105,'SO','Sondrio');
INSERT INTO zones VALUES ('',105,'TA','Taranto');
INSERT INTO zones VALUES ('',105,'TE','Teramo');
INSERT INTO zones VALUES ('',105,'TR','Terni');
INSERT INTO zones VALUES ('',105,'TO','Torino');
INSERT INTO zones VALUES ('',105,'OG','Ogliastra');
INSERT INTO zones VALUES ('',105,'TP','Trapani');
INSERT INTO zones VALUES ('',105,'TN','Trento');
INSERT INTO zones VALUES ('',105,'TV','Treviso');
INSERT INTO zones VALUES ('',105,'TS','Trieste');
INSERT INTO zones VALUES ('',105,'UD','Udine');
INSERT INTO zones VALUES ('',105,'VA','Varese');
INSERT INTO zones VALUES ('',105,'VE','Venezia');
INSERT INTO zones VALUES ('',105,'VB','Verbania');
INSERT INTO zones VALUES ('',105,'VC','Vercelli');
INSERT INTO zones VALUES ('',105,'VR','Verona');
INSERT INTO zones VALUES ('',105,'VV','Vibo Valentia');
INSERT INTO zones VALUES ('',105,'VI','Vicenza');
INSERT INTO zones VALUES ('',105,'VT','Viterbo');

#Japan
INSERT INTO zones VALUES ('',107,'Niigata', 'Niigata');
INSERT INTO zones VALUES ('',107,'Toyama', 'Toyama');
INSERT INTO zones VALUES ('',107,'Ishikawa', 'Ishikawa');
INSERT INTO zones VALUES ('',107,'Fukui', 'Fukui');
INSERT INTO zones VALUES ('',107,'Yamanashi', 'Yamanashi');
INSERT INTO zones VALUES ('',107,'Nagano', 'Nagano');
INSERT INTO zones VALUES ('',107,'Gifu', 'Gifu');
INSERT INTO zones VALUES ('',107,'Shizuoka', 'Shizuoka');
INSERT INTO zones VALUES ('',107,'Aichi', 'Aichi');
INSERT INTO zones VALUES ('',107,'Mie', 'Mie');
INSERT INTO zones VALUES ('',107,'Shiga', 'Shiga');
INSERT INTO zones VALUES ('',107,'Kyoto', 'Kyoto');
INSERT INTO zones VALUES ('',107,'Osaka', 'Osaka');
INSERT INTO zones VALUES ('',107,'Hyogo', 'Hyogo');
INSERT INTO zones VALUES ('',107,'Nara', 'Nara');
INSERT INTO zones VALUES ('',107,'Wakayama', 'Wakayama');
INSERT INTO zones VALUES ('',107,'Tottori', 'Tottori');
INSERT INTO zones VALUES ('',107,'Shimane', 'Shimane');
INSERT INTO zones VALUES ('',107,'Okayama', 'Okayama');
INSERT INTO zones VALUES ('',107,'Hiroshima', 'Hiroshima');
INSERT INTO zones VALUES ('',107,'Yamaguchi', 'Yamaguchi');
INSERT INTO zones VALUES ('',107,'Tokushima', 'Tokushima');
INSERT INTO zones VALUES ('',107,'Kagawa', 'Kagawa');
INSERT INTO zones VALUES ('',107,'Ehime', 'Ehime');
INSERT INTO zones VALUES ('',107,'Kochi', 'Kochi');
INSERT INTO zones VALUES ('',107,'Fukuoka', 'Fukuoka');
INSERT INTO zones VALUES ('',107,'Saga', 'Saga');
INSERT INTO zones VALUES ('',107,'Nagasaki', 'Nagasaki');
INSERT INTO zones VALUES ('',107,'Kumamoto', 'Kumamoto');
INSERT INTO zones VALUES ('',107,'Oita', 'Oita');
INSERT INTO zones VALUES ('',107,'Miyazaki', 'Miyazaki');
INSERT INTO zones VALUES ('',107,'Kagoshima', 'Kagoshima');

#Malaysia
INSERT INTO zones VALUES ('',129,'JOH','Johor');
INSERT INTO zones VALUES ('',129,'KDH','Kedah');
INSERT INTO zones VALUES ('',129,'KEL','Kelantan');
INSERT INTO zones VALUES ('',129,'KL','Kuala Lumpur');
INSERT INTO zones VALUES ('',129,'MEL','Melaka');
INSERT INTO zones VALUES ('',129,'NS','Negeri Sembilan');
INSERT INTO zones VALUES ('',129,'PAH','Pahang');
INSERT INTO zones VALUES ('',129,'PRK','Perak');
INSERT INTO zones VALUES ('',129,'PER','Perlis');
INSERT INTO zones VALUES ('',129,'PP','Pulau Pinang');
INSERT INTO zones VALUES ('',129,'SAB','Sabah');
INSERT INTO zones VALUES ('',129,'SWK','Sarawak');
INSERT INTO zones VALUES ('',129,'SEL','Selangor');
INSERT INTO zones VALUES ('',129,'TER','Terengganu');
INSERT INTO zones VALUES ('',129,'LAB','W.P.Labuan');

#Mexico
INSERT INTO zones VALUES ('',138,'AGS','Aguascalientes');
INSERT INTO zones VALUES ('',138,'BC','Baja California');
INSERT INTO zones VALUES ('',138,'BCS','Baja California Sur');
INSERT INTO zones VALUES ('',138,'CAM','Campeche');
INSERT INTO zones VALUES ('',138,'COA','Coahuila');
INSERT INTO zones VALUES ('',138,'COL','Colima');
INSERT INTO zones VALUES ('',138,'CHI','Chiapas');
INSERT INTO zones VALUES ('',138,'CHIH','Chihuahua');
INSERT INTO zones VALUES ('',138,'DF','Distrito Federal');
INSERT INTO zones VALUES ('',138,'DGO','Durango');
INSERT INTO zones VALUES ('',138,'MEX','Estado de Mexico');
INSERT INTO zones VALUES ('',138,'GTO','Guanajuato');
INSERT INTO zones VALUES ('',138,'GRO','Guerrero');
INSERT INTO zones VALUES ('',138,'HGO','Hidalgo');
INSERT INTO zones VALUES ('',138,'JAL','Jalisco');
INSERT INTO zones VALUES ('',138,'MCH','Michoacan');
INSERT INTO zones VALUES ('',138,'MOR','Morelos');
INSERT INTO zones VALUES ('',138,'NAY','Nayarit');
INSERT INTO zones VALUES ('',138,'NL','Nuevo Leon');
INSERT INTO zones VALUES ('',138,'OAX','Oaxaca');
INSERT INTO zones VALUES ('',138,'PUE','Puebla');
INSERT INTO zones VALUES ('',138,'QRO','Queretaro');
INSERT INTO zones VALUES ('',138,'QR','Quintana Roo');
INSERT INTO zones VALUES ('',138,'SLP','San Luis Potosi');
INSERT INTO zones VALUES ('',138,'SIN','Sinaloa');
INSERT INTO zones VALUES ('',138,'SON','Sonora');
INSERT INTO zones VALUES ('',138,'TAB','Tabasco');
INSERT INTO zones VALUES ('',138,'TMPS','Tamaulipas');
INSERT INTO zones VALUES ('',138,'TLAX','Tlaxcala');
INSERT INTO zones VALUES ('',138,'VER','Veracruz');
INSERT INTO zones VALUES ('',138,'YUC','Yucatan');
INSERT INTO zones VALUES ('',138,'ZAC','Zacatecas');

#Norway
INSERT INTO zones VALUES ('',160,'OSL','Oslo');
INSERT INTO zones VALUES ('',160,'AKE','Akershus');
INSERT INTO zones VALUES ('',160,'AUA','Aust-Agder');
INSERT INTO zones VALUES ('',160,'BUS','Buskerud');
INSERT INTO zones VALUES ('',160,'FIN','Finnmark');
INSERT INTO zones VALUES ('',160,'HED','Hedmark');
INSERT INTO zones VALUES ('',160,'HOR','Hordaland');
INSERT INTO zones VALUES ('',160,'MOR','Møre og Romsdal');
INSERT INTO zones VALUES ('',160,'NOR','Nordland');
INSERT INTO zones VALUES ('',160,'NTR','Nord-Trøndelag');
INSERT INTO zones VALUES ('',160,'OPP','Oppland');
INSERT INTO zones VALUES ('',160,'ROG','Rogaland');
INSERT INTO zones VALUES ('',160,'SOF','Sogn og Fjordane');
INSERT INTO zones VALUES ('',160,'STR','Sør-Trøndelag');
INSERT INTO zones VALUES ('',160,'TEL','Telemark');
INSERT INTO zones VALUES ('',160,'TRO','Troms');
INSERT INTO zones VALUES ('',160,'VEA','Vest-Agder');
INSERT INTO zones VALUES ('',160,'OST','Østfold');
INSERT INTO zones VALUES ('',160,'SVA','Svalbard');

#Pakistan
INSERT INTO zones VALUES ('',162,'KHI','Karachi');
INSERT INTO zones VALUES ('',162,'LH','Lahore');
INSERT INTO zones VALUES ('',162,'ISB','Islamabad');
INSERT INTO zones VALUES ('',162,'QUE','Quetta');
INSERT INTO zones VALUES ('',162,'PSH','Peshawar');
INSERT INTO zones VALUES ('',162,'GUJ','Gujrat');
INSERT INTO zones VALUES ('',162,'SAH','Sahiwal');
INSERT INTO zones VALUES ('',162,'FSB','Faisalabad');
INSERT INTO zones VALUES ('',162,'RIP','Rawal Pindi');

#Romania
INSERT INTO zones VALUES ('',175,'AB','Alba');
INSERT INTO zones VALUES ('',175,'AR','Arad');
INSERT INTO zones VALUES ('',175,'AG','Arges');
INSERT INTO zones VALUES ('',175,'BC','Bacau');
INSERT INTO zones VALUES ('',175,'BH','Bihor');
INSERT INTO zones VALUES ('',175,'BN','Bistrita-Nasaud');
INSERT INTO zones VALUES ('',175,'BT','Botosani');
INSERT INTO zones VALUES ('',175,'BV','Brasov');
INSERT INTO zones VALUES ('',175,'BR','Braila');
INSERT INTO zones VALUES ('',175,'B','Bucuresti');
INSERT INTO zones VALUES ('',175,'BZ','Buzau');
INSERT INTO zones VALUES ('',175,'CS','Caras-Severin');
INSERT INTO zones VALUES ('',175,'CL','Calarasi');
INSERT INTO zones VALUES ('',175,'CJ','Cluj');
INSERT INTO zones VALUES ('',175,'CT','Constanta');
INSERT INTO zones VALUES ('',175,'CV','Covasna');
INSERT INTO zones VALUES ('',175,'DB','Dimbovita');
INSERT INTO zones VALUES ('',175,'DJ','Dolj');
INSERT INTO zones VALUES ('',175,'GL','Galati');
INSERT INTO zones VALUES ('',175,'GR','Giurgiu');
INSERT INTO zones VALUES ('',175,'GJ','Gorj');
INSERT INTO zones VALUES ('',175,'HR','Harghita');
INSERT INTO zones VALUES ('',175,'HD','Hunedoara');
INSERT INTO zones VALUES ('',175,'IL','Ialomita');
INSERT INTO zones VALUES ('',175,'IS','Iasi');
INSERT INTO zones VALUES ('',175,'IF','Ilfov');
INSERT INTO zones VALUES ('',175,'MM','Maramures');
INSERT INTO zones VALUES ('',175,'MH','Mehedint');
INSERT INTO zones VALUES ('',175,'MS','Mures');
INSERT INTO zones VALUES ('',175,'NT','Neamt');
INSERT INTO zones VALUES ('',175,'OT','Olt');
INSERT INTO zones VALUES ('',175,'PH','Prahova');
INSERT INTO zones VALUES ('',175,'SM','Satu-Mare');
INSERT INTO zones VALUES ('',175,'SJ','Salaj');
INSERT INTO zones VALUES ('',175,'SB','Sibiu');
INSERT INTO zones VALUES ('',175,'SV','Suceava');
INSERT INTO zones VALUES ('',175,'TR','Teleorman');
INSERT INTO zones VALUES ('',175,'TM','Timis');
INSERT INTO zones VALUES ('',175,'TL','Tulcea');
INSERT INTO zones VALUES ('',175,'VS','Vaslui');
INSERT INTO zones VALUES ('',175,'VL','Valcea');
INSERT INTO zones VALUES ('',175,'VN','Vrancea');

#South Africa
INSERT INTO zones VALUES ('',193,'WP','Western Cape');
INSERT INTO zones VALUES ('',193,'GP','Gauteng');
INSERT INTO zones VALUES ('',193,'KZN','Kwazulu-Natal');
INSERT INTO zones VALUES ('',193,'NC','Northern-Cape');
INSERT INTO zones VALUES ('',193,'EC','Eastern-Cape');
INSERT INTO zones VALUES ('',193,'MP','Mpumalanga');
INSERT INTO zones VALUES ('',193,'NW','North-West');
INSERT INTO zones VALUES ('',193,'FS','Free State');
INSERT INTO zones VALUES ('',193,'NP','Northern Province');

#Turkey
INSERT INTO zones VALUES ('',215,'AA', 'Adana');
INSERT INTO zones VALUES ('',215,'AD', 'Adiyaman');
INSERT INTO zones VALUES ('',215,'AF', 'Afyonkarahisar');
INSERT INTO zones VALUES ('',215,'AG', 'Agri');
INSERT INTO zones VALUES ('',215,'AK', 'Aksaray');
INSERT INTO zones VALUES ('',215,'AM', 'Amasya');
INSERT INTO zones VALUES ('',215,'AN', 'Ankara');
INSERT INTO zones VALUES ('',215,'AL', 'Antalya');
INSERT INTO zones VALUES ('',215,'AR', 'Ardahan');
INSERT INTO zones VALUES ('',215,'AV', 'Artvin');
INSERT INTO zones VALUES ('',215,'AY', 'Aydin');
INSERT INTO zones VALUES ('',215,'BK', 'Balikesir');
INSERT INTO zones VALUES ('',215,'BR', 'Bartin');
INSERT INTO zones VALUES ('',215,'BM', 'Batman');
INSERT INTO zones VALUES ('',215,'BB', 'Bayburt');
INSERT INTO zones VALUES ('',215,'BC', 'Bilecik');
INSERT INTO zones VALUES ('',215,'BG', 'Bingöl');
INSERT INTO zones VALUES ('',215,'BT', 'Bitlis');
INSERT INTO zones VALUES ('',215,'BL', 'Bolu' );
INSERT INTO zones VALUES ('',215,'BD', 'Burdur');
INSERT INTO zones VALUES ('',215,'BU', 'Bursa');
INSERT INTO zones VALUES ('',215,'CK', 'Çanakkale');
INSERT INTO zones VALUES ('',215,'CI', 'Çankiri');
INSERT INTO zones VALUES ('',215,'CM', 'Çorum');
INSERT INTO zones VALUES ('',215,'DN', 'Denizli');
INSERT INTO zones VALUES ('',215,'DY', 'Diyarbakir');
INSERT INTO zones VALUES ('',215,'DU', 'Düzce');
INSERT INTO zones VALUES ('',215,'ED', 'Edirne');
INSERT INTO zones VALUES ('',215,'EG', 'Elazig');
INSERT INTO zones VALUES ('',215,'EN', 'Erzincan');
INSERT INTO zones VALUES ('',215,'EM', 'Erzurum');
INSERT INTO zones VALUES ('',215,'ES', 'Eskisehir');
INSERT INTO zones VALUES ('',215,'GA', 'Gaziantep');
INSERT INTO zones VALUES ('',215,'GI', 'Giresun');
INSERT INTO zones VALUES ('',215,'GU', 'Gümüshane');
INSERT INTO zones VALUES ('',215,'HK', 'Hakkari');
INSERT INTO zones VALUES ('',215,'HT', 'Hatay');
INSERT INTO zones VALUES ('',215,'IG', 'Igdir');
INSERT INTO zones VALUES ('',215,'IP', 'Isparta');
INSERT INTO zones VALUES ('',215,'IB', 'Istanbul');
INSERT INTO zones VALUES ('',215,'IZ', 'Izmir');
INSERT INTO zones VALUES ('',215,'KM', 'Kahramanmaras');
INSERT INTO zones VALUES ('',215,'KB', 'Karabük');
INSERT INTO zones VALUES ('',215,'KR', 'Karaman');
INSERT INTO zones VALUES ('',215,'KA', 'Kars');
INSERT INTO zones VALUES ('',215,'KS', 'Kastamonu');
INSERT INTO zones VALUES ('',215,'KY', 'Kayseri');
INSERT INTO zones VALUES ('',215,'KI', 'Kilis');
INSERT INTO zones VALUES ('',215,'KK', 'Kirikkale');
INSERT INTO zones VALUES ('',215,'KL', 'Kirklareli');
INSERT INTO zones VALUES ('',215,'KH', 'Kirsehir');
INSERT INTO zones VALUES ('',215,'KC', 'Kocaeli');
INSERT INTO zones VALUES ('',215,'KO', 'Konya');
INSERT INTO zones VALUES ('',215,'KU', 'Kütahya');
INSERT INTO zones VALUES ('',215,'ML', 'Malatya');
INSERT INTO zones VALUES ('',215,'MN', 'Manisa');
INSERT INTO zones VALUES ('',215,'MR', 'Mardin');
INSERT INTO zones VALUES ('',215,'IC', 'Mersin');
INSERT INTO zones VALUES ('',215,'MG', 'Mugla');
INSERT INTO zones VALUES ('',215,'MS', 'Mus');
INSERT INTO zones VALUES ('',215,'NV', 'Nevsehir');
INSERT INTO zones VALUES ('',215,'NG', 'Nigde');
INSERT INTO zones VALUES ('',215,'OR', 'Ordu');
INSERT INTO zones VALUES ('',215,'OS', 'Osmaniye');
INSERT INTO zones VALUES ('',215,'RI', 'Rize');
INSERT INTO zones VALUES ('',215,'SK', 'Sakarya');
INSERT INTO zones VALUES ('',215,'SS', 'Samsun');
INSERT INTO zones VALUES ('',215,'SU', 'Sanliurfa');
INSERT INTO zones VALUES ('',215,'SI', 'Siirt');
INSERT INTO zones VALUES ('',215,'SP', 'Sinop');
INSERT INTO zones VALUES ('',215,'SR', 'Sirnak');
INSERT INTO zones VALUES ('',215,'SV', 'Sivas');
INSERT INTO zones VALUES ('',215,'TG', 'Tekirdag');
INSERT INTO zones VALUES ('',215,'TT', 'Tokat');
INSERT INTO zones VALUES ('',215,'TB', 'Trabzon');
INSERT INTO zones VALUES ('',215,'TC', 'Tunceli');
INSERT INTO zones VALUES ('',215,'US', 'Usak');
INSERT INTO zones VALUES ('',215,'VA', 'Van');
INSERT INTO zones VALUES ('',215,'YL', 'Yalova');
INSERT INTO zones VALUES ('',215,'YZ', 'Yozgat');
INSERT INTO zones VALUES ('',215,'ZO', 'Zonguldak');

#Venezuela
INSERT INTO zones VALUES ('',229,'AM','Amazonas');
INSERT INTO zones VALUES ('',229,'AN','Anzoátegui');
INSERT INTO zones VALUES ('',229,'AR','Aragua');
INSERT INTO zones VALUES ('',229,'AP','Apure');
INSERT INTO zones VALUES ('',229,'BA','Barinas');
INSERT INTO zones VALUES ('',229,'BO','Bolívar');
INSERT INTO zones VALUES ('',229,'CA','Carabobo');
INSERT INTO zones VALUES ('',229,'CO','Cojedes');
INSERT INTO zones VALUES ('',229,'DA','Delta Amacuro');
INSERT INTO zones VALUES ('',229,'DC','Distrito Capital');
INSERT INTO zones VALUES ('',229,'FA','Falcón');
INSERT INTO zones VALUES ('',229,'GA','Guárico');
INSERT INTO zones VALUES ('',229,'GU','Guayana');
INSERT INTO zones VALUES ('',229,'LA','Lara');
INSERT INTO zones VALUES ('',229,'ME','Mérida');
INSERT INTO zones VALUES ('',229,'MI','Miranda');
INSERT INTO zones VALUES ('',229,'MO','Monagas');
INSERT INTO zones VALUES ('',229,'NE','Nueva Esparta');
INSERT INTO zones VALUES ('',229,'PO','Portuguesa');
INSERT INTO zones VALUES ('',229,'SU','Sucre');
INSERT INTO zones VALUES ('',229,'TA','Táchira');
INSERT INTO zones VALUES ('',229,'TU','Trujillo');
INSERT INTO zones VALUES ('',229,'VA','Vargas');
INSERT INTO zones VALUES ('',229,'YA','Yaracuy');
INSERT INTO zones VALUES ('',229,'ZU','Zulia');

#UK
INSERT INTO zones VALUES ('',222,'BAS','Bath and North East Somerset');
INSERT INTO zones VALUES ('',222,'BDF','Bedfordshire');
INSERT INTO zones VALUES ('',222,'WBK','Berkshire');
INSERT INTO zones VALUES ('',222,'BBD','Blackburn with Darwen');
INSERT INTO zones VALUES ('',222,'BPL','Blackpool');
INSERT INTO zones VALUES ('',222,'BPL','Bournemouth');
INSERT INTO zones VALUES ('',222,'BNH','Brighton and Hove');
INSERT INTO zones VALUES ('',222,'BST','Bristol');
INSERT INTO zones VALUES ('',222,'BKM','Buckinghamshire');
INSERT INTO zones VALUES ('',222,'CAM','Cambridgeshire');
INSERT INTO zones VALUES ('',222,'CHS','Cheshire');
INSERT INTO zones VALUES ('',222,'CON','Cornwall');
INSERT INTO zones VALUES ('',222,'DUR','County Durham');
INSERT INTO zones VALUES ('',222,'CMA','Cumbria');
INSERT INTO zones VALUES ('',222,'DAL','Darlington');
INSERT INTO zones VALUES ('',222,'DER','Derby');
INSERT INTO zones VALUES ('',222,'DBY','Derbyshire');
INSERT INTO zones VALUES ('',222,'DEV','Devon');
INSERT INTO zones VALUES ('',222,'DOR','Dorset');
INSERT INTO zones VALUES ('',222,'ERY','East Riding of Yorkshire');
INSERT INTO zones VALUES ('',222,'ESX','East Sussex');
INSERT INTO zones VALUES ('',222,'ESS','Essex');
INSERT INTO zones VALUES ('',222,'GLS','Gloucestershire');
INSERT INTO zones VALUES ('',222,'LND','Greater London');
INSERT INTO zones VALUES ('',222,'MAN','Greater Manchester');
INSERT INTO zones VALUES ('',222,'HAL','Halton');
INSERT INTO zones VALUES ('',222,'HAM','Hampshire');
INSERT INTO zones VALUES ('',222,'HPL','Hartlepool');
INSERT INTO zones VALUES ('',222,'HEF','Herefordshire');
INSERT INTO zones VALUES ('',222,'HRT','Hertfordshire');
INSERT INTO zones VALUES ('',222,'KHL','Hull');
INSERT INTO zones VALUES ('',222,'IOW','Isle of Wight');
INSERT INTO zones VALUES ('',222,'KEN','Kent');
INSERT INTO zones VALUES ('',222,'LAN','Lancashire');
INSERT INTO zones VALUES ('',222,'LCE','Leicester');
INSERT INTO zones VALUES ('',222,'LEC','Leicestershire');
INSERT INTO zones VALUES ('',222,'LIN','Lincolnshire');
INSERT INTO zones VALUES ('',222,'LUT','Luton');
INSERT INTO zones VALUES ('',222,'MDW','Medway');
INSERT INTO zones VALUES ('',222,'MER','Merseyside');
INSERT INTO zones VALUES ('',222,'MDB','Middlesbrough');
INSERT INTO zones VALUES ('',222,'MDB','Milton Keynes');
INSERT INTO zones VALUES ('',222,'NFK','Norfolk');
INSERT INTO zones VALUES ('',222,'NTH','Northamptonshire');
INSERT INTO zones VALUES ('',222,'NEL','North East Lincolnshire');
INSERT INTO zones VALUES ('',222,'NLN','North Lincolnshire');
INSERT INTO zones VALUES ('',222,'NSM','North Somerset');
INSERT INTO zones VALUES ('',222,'NBL','Northumberland');
INSERT INTO zones VALUES ('',222,'NYK','North Yorkshire');
INSERT INTO zones VALUES ('',222,'NGM','Nottingham');
INSERT INTO zones VALUES ('',222,'NTT','Nottinghamshire');
INSERT INTO zones VALUES ('',222,'OXF','Oxfordshire');
INSERT INTO zones VALUES ('',222,'PTE','Peterborough');
INSERT INTO zones VALUES ('',222,'PLY','Plymouth');
INSERT INTO zones VALUES ('',222,'POL','Poole');
INSERT INTO zones VALUES ('',222,'POR','Portsmouth');
INSERT INTO zones VALUES ('',222,'RCC','Redcar and Cleveland');
INSERT INTO zones VALUES ('',222,'RUT','Rutland');
INSERT INTO zones VALUES ('',222,'SHR','Shropshire');
INSERT INTO zones VALUES ('',222,'SOM','Somerset');
INSERT INTO zones VALUES ('',222,'STH','Southampton');
INSERT INTO zones VALUES ('',222,'SOS','Southend-on-Sea');
INSERT INTO zones VALUES ('',222,'SGC','South Gloucestershire');
INSERT INTO zones VALUES ('',222,'SYK','South Yorkshire');
INSERT INTO zones VALUES ('',222,'STS','Staffordshire');
INSERT INTO zones VALUES ('',222,'STT','Stockton-on-Tees');
INSERT INTO zones VALUES ('',222,'STE','Stoke-on-Trent');
INSERT INTO zones VALUES ('',222,'SFK','Suffolk');
INSERT INTO zones VALUES ('',222,'SRY','Surrey');
INSERT INTO zones VALUES ('',222,'SWD','Swindon');
INSERT INTO zones VALUES ('',222,'TFW','Telford and Wrekin');
INSERT INTO zones VALUES ('',222,'THR','Thurrock');
INSERT INTO zones VALUES ('',222,'TOB','Torbay');
INSERT INTO zones VALUES ('',222,'TYW','Tyne and Wear');
INSERT INTO zones VALUES ('',222,'WRT','Warrington');
INSERT INTO zones VALUES ('',222,'WAR','Warwickshire');
INSERT INTO zones VALUES ('',222,'WMI','West Midlands');
INSERT INTO zones VALUES ('',222,'WSX','West Sussex');
INSERT INTO zones VALUES ('',222,'WYK','West Yorkshire');
INSERT INTO zones VALUES ('',222,'WIL','Wiltshire');
INSERT INTO zones VALUES ('',222,'WOR','Worcestershire');
INSERT INTO zones VALUES ('',222,'YOR','York');

# Data for table payment_moneybookers_countries
INSERT INTO payment_moneybookers_countries VALUES (2, 'ALB');
INSERT INTO payment_moneybookers_countries VALUES (3, 'ALG');
INSERT INTO payment_moneybookers_countries VALUES (4, 'AME');
INSERT INTO payment_moneybookers_countries VALUES (5, 'AND');
INSERT INTO payment_moneybookers_countries VALUES (6, 'AGL');
INSERT INTO payment_moneybookers_countries VALUES (7, 'ANG');
INSERT INTO payment_moneybookers_countries VALUES (9, 'ANT');
INSERT INTO payment_moneybookers_countries VALUES (10, 'ARG');
INSERT INTO payment_moneybookers_countries VALUES (11, 'ARM');
INSERT INTO payment_moneybookers_countries VALUES (12, 'ARU');
INSERT INTO payment_moneybookers_countries VALUES (13, 'AUS');
INSERT INTO payment_moneybookers_countries VALUES (14, 'AUT');
INSERT INTO payment_moneybookers_countries VALUES (15, 'AZE');
INSERT INTO payment_moneybookers_countries VALUES (16, 'BMS');
INSERT INTO payment_moneybookers_countries VALUES (17, 'BAH');
INSERT INTO payment_moneybookers_countries VALUES (18, 'BAN');
INSERT INTO payment_moneybookers_countries VALUES (19, 'BAR');
INSERT INTO payment_moneybookers_countries VALUES (20, 'BLR');
INSERT INTO payment_moneybookers_countries VALUES (21, 'BGM');
INSERT INTO payment_moneybookers_countries VALUES (22, 'BEL');
INSERT INTO payment_moneybookers_countries VALUES (23, 'BEN');
INSERT INTO payment_moneybookers_countries VALUES (24, 'BER');
INSERT INTO payment_moneybookers_countries VALUES (26, 'BOL');
INSERT INTO payment_moneybookers_countries VALUES (27, 'BOS');
INSERT INTO payment_moneybookers_countries VALUES (28, 'BOT');
INSERT INTO payment_moneybookers_countries VALUES (30, 'BRA');
INSERT INTO payment_moneybookers_countries VALUES (32, 'BRU');
INSERT INTO payment_moneybookers_countries VALUES (33, 'BUL');
INSERT INTO payment_moneybookers_countries VALUES (34, 'BKF');
INSERT INTO payment_moneybookers_countries VALUES (35, 'BUR');
INSERT INTO payment_moneybookers_countries VALUES (36, 'CAM');
INSERT INTO payment_moneybookers_countries VALUES (37, 'CMR');
INSERT INTO payment_moneybookers_countries VALUES (38, 'CAN');
INSERT INTO payment_moneybookers_countries VALUES (39, 'CAP');
INSERT INTO payment_moneybookers_countries VALUES (40, 'CAY');
INSERT INTO payment_moneybookers_countries VALUES (41, 'CEN');
INSERT INTO payment_moneybookers_countries VALUES (42, 'CHA');
INSERT INTO payment_moneybookers_countries VALUES (43, 'CHL');
INSERT INTO payment_moneybookers_countries VALUES (44, 'CHN');
INSERT INTO payment_moneybookers_countries VALUES (47, 'COL');
INSERT INTO payment_moneybookers_countries VALUES (49, 'CON');
INSERT INTO payment_moneybookers_countries VALUES (51, 'COS');
INSERT INTO payment_moneybookers_countries VALUES (52, 'COT');
INSERT INTO payment_moneybookers_countries VALUES (53, 'CRO');
INSERT INTO payment_moneybookers_countries VALUES (54, 'CUB');
INSERT INTO payment_moneybookers_countries VALUES (55, 'CYP');
INSERT INTO payment_moneybookers_countries VALUES (56, 'CZE');
INSERT INTO payment_moneybookers_countries VALUES (57, 'DEN');
INSERT INTO payment_moneybookers_countries VALUES (58, 'DJI');
INSERT INTO payment_moneybookers_countries VALUES (59, 'DOM');
INSERT INTO payment_moneybookers_countries VALUES (60, 'DRP');
INSERT INTO payment_moneybookers_countries VALUES (62, 'ECU');
INSERT INTO payment_moneybookers_countries VALUES (64, 'EL_');
INSERT INTO payment_moneybookers_countries VALUES (65, 'EQU');
INSERT INTO payment_moneybookers_countries VALUES (66, 'ERI');
INSERT INTO payment_moneybookers_countries VALUES (67, 'EST');
INSERT INTO payment_moneybookers_countries VALUES (68, 'ETH');
INSERT INTO payment_moneybookers_countries VALUES (70, 'FAR');
INSERT INTO payment_moneybookers_countries VALUES (71, 'FIJ');
INSERT INTO payment_moneybookers_countries VALUES (72, 'FIN');
INSERT INTO payment_moneybookers_countries VALUES (73, 'FRA');
INSERT INTO payment_moneybookers_countries VALUES (75, 'FRE');
INSERT INTO payment_moneybookers_countries VALUES (78, 'GAB');
INSERT INTO payment_moneybookers_countries VALUES (79, 'GAM');
INSERT INTO payment_moneybookers_countries VALUES (80, 'GEO');
INSERT INTO payment_moneybookers_countries VALUES (81, 'GER');
INSERT INTO payment_moneybookers_countries VALUES (82, 'GHA');
INSERT INTO payment_moneybookers_countries VALUES (83, 'GIB');
INSERT INTO payment_moneybookers_countries VALUES (84, 'GRC');
INSERT INTO payment_moneybookers_countries VALUES (85, 'GRL');
INSERT INTO payment_moneybookers_countries VALUES (87, 'GDL');
INSERT INTO payment_moneybookers_countries VALUES (88, 'GUM');
INSERT INTO payment_moneybookers_countries VALUES (89, 'GUA');
INSERT INTO payment_moneybookers_countries VALUES (90, 'GUI');
INSERT INTO payment_moneybookers_countries VALUES (91, 'GBS');
INSERT INTO payment_moneybookers_countries VALUES (92, 'GUY');
INSERT INTO payment_moneybookers_countries VALUES (93, 'HAI');
INSERT INTO payment_moneybookers_countries VALUES (95, 'HON');
INSERT INTO payment_moneybookers_countries VALUES (96, 'HKG');
INSERT INTO payment_moneybookers_countries VALUES (97, 'HUN');
INSERT INTO payment_moneybookers_countries VALUES (98, 'ICE');
INSERT INTO payment_moneybookers_countries VALUES (99, 'IND');
INSERT INTO payment_moneybookers_countries VALUES (101, 'IRN');
INSERT INTO payment_moneybookers_countries VALUES (102, 'IRA');
INSERT INTO payment_moneybookers_countries VALUES (103, 'IRE');
INSERT INTO payment_moneybookers_countries VALUES (104, 'ISR');
INSERT INTO payment_moneybookers_countries VALUES (105, 'ITA');
INSERT INTO payment_moneybookers_countries VALUES (106, 'JAM');
INSERT INTO payment_moneybookers_countries VALUES (107, 'JAP');
INSERT INTO payment_moneybookers_countries VALUES (108, 'JOR');
INSERT INTO payment_moneybookers_countries VALUES (109, 'KAZ');
INSERT INTO payment_moneybookers_countries VALUES (110, 'KEN');
INSERT INTO payment_moneybookers_countries VALUES (112, 'SKO');
INSERT INTO payment_moneybookers_countries VALUES (113, 'KOR');
INSERT INTO payment_moneybookers_countries VALUES (114, 'KUW');
INSERT INTO payment_moneybookers_countries VALUES (115, 'KYR');
INSERT INTO payment_moneybookers_countries VALUES (116, 'LAO');
INSERT INTO payment_moneybookers_countries VALUES (117, 'LAT');
INSERT INTO payment_moneybookers_countries VALUES (141, 'MCO');
INSERT INTO payment_moneybookers_countries VALUES (119, 'LES');
INSERT INTO payment_moneybookers_countries VALUES (120, 'LIB');
INSERT INTO payment_moneybookers_countries VALUES (121, 'LBY');
INSERT INTO payment_moneybookers_countries VALUES (122, 'LIE');
INSERT INTO payment_moneybookers_countries VALUES (123, 'LIT');
INSERT INTO payment_moneybookers_countries VALUES (124, 'LUX');
INSERT INTO payment_moneybookers_countries VALUES (125, 'MAC');
INSERT INTO payment_moneybookers_countries VALUES (126, 'F.Y');
INSERT INTO payment_moneybookers_countries VALUES (127, 'MAD');
INSERT INTO payment_moneybookers_countries VALUES (128, 'MLW');
INSERT INTO payment_moneybookers_countries VALUES (129, 'MLS');
INSERT INTO payment_moneybookers_countries VALUES (130, 'MAL');
INSERT INTO payment_moneybookers_countries VALUES (131, 'MLI');
INSERT INTO payment_moneybookers_countries VALUES (132, 'MLT');
INSERT INTO payment_moneybookers_countries VALUES (134, 'MAR');
INSERT INTO payment_moneybookers_countries VALUES (135, 'MRT');
INSERT INTO payment_moneybookers_countries VALUES (136, 'MAU');
INSERT INTO payment_moneybookers_countries VALUES (138, 'MEX');
INSERT INTO payment_moneybookers_countries VALUES (140, 'MOL');
INSERT INTO payment_moneybookers_countries VALUES (142, 'MON');
INSERT INTO payment_moneybookers_countries VALUES (143, 'MTT');
INSERT INTO payment_moneybookers_countries VALUES (144, 'MOR');
INSERT INTO payment_moneybookers_countries VALUES (145, 'MOZ');
INSERT INTO payment_moneybookers_countries VALUES (76, 'PYF');
INSERT INTO payment_moneybookers_countries VALUES (147, 'NAM');
INSERT INTO payment_moneybookers_countries VALUES (149, 'NEP');
INSERT INTO payment_moneybookers_countries VALUES (150, 'NED');
INSERT INTO payment_moneybookers_countries VALUES (151, 'NET');
INSERT INTO payment_moneybookers_countries VALUES (152, 'CDN');
INSERT INTO payment_moneybookers_countries VALUES (153, 'NEW');
INSERT INTO payment_moneybookers_countries VALUES (154, 'NIC');
INSERT INTO payment_moneybookers_countries VALUES (155, 'NIG');
INSERT INTO payment_moneybookers_countries VALUES (69, 'FLK');
INSERT INTO payment_moneybookers_countries VALUES (160, 'NWY');
INSERT INTO payment_moneybookers_countries VALUES (161, 'OMA');
INSERT INTO payment_moneybookers_countries VALUES (162, 'PAK');
INSERT INTO payment_moneybookers_countries VALUES (164, 'PAN');
INSERT INTO payment_moneybookers_countries VALUES (165, 'PAP');
INSERT INTO payment_moneybookers_countries VALUES (166, 'PAR');
INSERT INTO payment_moneybookers_countries VALUES (167, 'PER');
INSERT INTO payment_moneybookers_countries VALUES (168, 'PHI');
INSERT INTO payment_moneybookers_countries VALUES (170, 'POL');
INSERT INTO payment_moneybookers_countries VALUES (171, 'POR');
INSERT INTO payment_moneybookers_countries VALUES (172, 'PUE');
INSERT INTO payment_moneybookers_countries VALUES (173, 'QAT');
INSERT INTO payment_moneybookers_countries VALUES (175, 'ROM');
INSERT INTO payment_moneybookers_countries VALUES (176, 'RUS');
INSERT INTO payment_moneybookers_countries VALUES (177, 'RWA');
INSERT INTO payment_moneybookers_countries VALUES (178, 'SKN');
INSERT INTO payment_moneybookers_countries VALUES (179, 'SLU');
INSERT INTO payment_moneybookers_countries VALUES (180, 'ST.');
INSERT INTO payment_moneybookers_countries VALUES (181, 'WES');
INSERT INTO payment_moneybookers_countries VALUES (182, 'SAN');
INSERT INTO payment_moneybookers_countries VALUES (183, 'SAO');
INSERT INTO payment_moneybookers_countries VALUES (184, 'SAU');
INSERT INTO payment_moneybookers_countries VALUES (185, 'SEN');
INSERT INTO payment_moneybookers_countries VALUES (186, 'SEY');
INSERT INTO payment_moneybookers_countries VALUES (187, 'SIE');
INSERT INTO payment_moneybookers_countries VALUES (188, 'SIN');
INSERT INTO payment_moneybookers_countries VALUES (189, 'SLO');
INSERT INTO payment_moneybookers_countries VALUES (190, 'SLV');
INSERT INTO payment_moneybookers_countries VALUES (191, 'SOL');
INSERT INTO payment_moneybookers_countries VALUES (192, 'SOM');
INSERT INTO payment_moneybookers_countries VALUES (193, 'SOU');
INSERT INTO payment_moneybookers_countries VALUES (195, 'SPA');
INSERT INTO payment_moneybookers_countries VALUES (196, 'SRI');
INSERT INTO payment_moneybookers_countries VALUES (199, 'SUD');
INSERT INTO payment_moneybookers_countries VALUES (200, 'SUR');
INSERT INTO payment_moneybookers_countries VALUES (202, 'SWA');
INSERT INTO payment_moneybookers_countries VALUES (203, 'SWE');
INSERT INTO payment_moneybookers_countries VALUES (204, 'SWI');
INSERT INTO payment_moneybookers_countries VALUES (205, 'SYR');
INSERT INTO payment_moneybookers_countries VALUES (206, 'TWN');
INSERT INTO payment_moneybookers_countries VALUES (207, 'TAJ');
INSERT INTO payment_moneybookers_countries VALUES (208, 'TAN');
INSERT INTO payment_moneybookers_countries VALUES (209, 'THA');
INSERT INTO payment_moneybookers_countries VALUES (210, 'TOG');
INSERT INTO payment_moneybookers_countries VALUES (212, 'TON');
INSERT INTO payment_moneybookers_countries VALUES (213, 'TRI');
INSERT INTO payment_moneybookers_countries VALUES (214, 'TUN');
INSERT INTO payment_moneybookers_countries VALUES (215, 'TUR');
INSERT INTO payment_moneybookers_countries VALUES (216, 'TKM');
INSERT INTO payment_moneybookers_countries VALUES (217, 'TCI');
INSERT INTO payment_moneybookers_countries VALUES (219, 'UGA');
INSERT INTO payment_moneybookers_countries VALUES (231, 'BRI');
INSERT INTO payment_moneybookers_countries VALUES (221, 'UAE');
INSERT INTO payment_moneybookers_countries VALUES (222, 'GBR');
INSERT INTO payment_moneybookers_countries VALUES (223, 'UNI');
INSERT INTO payment_moneybookers_countries VALUES (225, 'URU');
INSERT INTO payment_moneybookers_countries VALUES (226, 'UZB');
INSERT INTO payment_moneybookers_countries VALUES (227, 'VAN');
INSERT INTO payment_moneybookers_countries VALUES (229, 'VEN');
INSERT INTO payment_moneybookers_countries VALUES (230, 'VIE');
INSERT INTO payment_moneybookers_countries VALUES (232, 'US_');
INSERT INTO payment_moneybookers_countries VALUES (235, 'YEM');
INSERT INTO payment_moneybookers_countries VALUES (236, 'YUG');
INSERT INTO payment_moneybookers_countries VALUES (238, 'ZAM');
INSERT INTO payment_moneybookers_countries VALUES (239, 'ZIM');

# Data for table payment_moneybookers_currencies
INSERT INTO payment_moneybookers_currencies VALUES ('AUD', 'Australian Dollar');
INSERT INTO payment_moneybookers_currencies VALUES ('BGN', 'Bulgarian Lev');
INSERT INTO payment_moneybookers_currencies VALUES ('CAD', 'Canadian Dollar');
INSERT INTO payment_moneybookers_currencies VALUES ('CHF', 'Swiss Franc');
INSERT INTO payment_moneybookers_currencies VALUES ('CZK', 'Czech Koruna');
INSERT INTO payment_moneybookers_currencies VALUES ('DKK', 'Danish Krone');
INSERT INTO payment_moneybookers_currencies VALUES ('EEK', 'Estonian Koruna');
INSERT INTO payment_moneybookers_currencies VALUES ('EUR', 'Euro');
INSERT INTO payment_moneybookers_currencies VALUES ('GBP', 'Pound Sterling');
INSERT INTO payment_moneybookers_currencies VALUES ('HKD', 'Hong Kong Dollar');
INSERT INTO payment_moneybookers_currencies VALUES ('HUF', 'Forint');
INSERT INTO payment_moneybookers_currencies VALUES ('ILS', 'Shekel');
INSERT INTO payment_moneybookers_currencies VALUES ('ISK', 'Iceland Krona');
INSERT INTO payment_moneybookers_currencies VALUES ('JPY', 'Yen');
INSERT INTO payment_moneybookers_currencies VALUES ('KRW', 'South-Korean Won');
INSERT INTO payment_moneybookers_currencies VALUES ('LVL', 'Latvian Lat');
INSERT INTO payment_moneybookers_currencies VALUES ('MYR', 'Malaysian Ringgit');
INSERT INTO payment_moneybookers_currencies VALUES ('NOK', 'Norwegian Krone');
INSERT INTO payment_moneybookers_currencies VALUES ('NZD', 'New Zealand Dollar');
INSERT INTO payment_moneybookers_currencies VALUES ('PLN', 'Zloty');
INSERT INTO payment_moneybookers_currencies VALUES ('SEK', 'Swedish Krona');
INSERT INTO payment_moneybookers_currencies VALUES ('SGD', 'Singapore Dollar');
INSERT INTO payment_moneybookers_currencies VALUES ('SKK', 'Slovak Koruna');
INSERT INTO payment_moneybookers_currencies VALUES ('THB', 'Baht');
INSERT INTO payment_moneybookers_currencies VALUES ('TWD', 'New Taiwan Dollar');
INSERT INTO payment_moneybookers_currencies VALUES ('USD', 'US Dollar');
INSERT INTO payment_moneybookers_currencies VALUES ('ZAR', 'South-African Rand');

# Keep an empty line at the end of this file for the installer to work properly
