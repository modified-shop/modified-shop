<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2012 by www.rpa-com.de

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

$cfg_install = false;
$cfg_update = false;
$cfg_group_install = false;
$cfg_group_update = false;
$values = array();
$values_update = array();
$values_group = array();
$values_group_update = array();

//##############################//

//configuration_group_id 1 --- "Mein Shop"
  $values[] = "(NULL, 'CHECKOUT_USE_PRODUCTS_SHORT_DESCRIPTION', 'false', '1', '40', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'CHECKOUT_SHOW_PRODUCTS_IMAGES', 'true', '1', '41', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'USE_SHORT_DATE_FORMAT', 'true', '1', '50', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  //$values[] = "(NULL, 'CHECKOUT_SHOW_PRODUCTS_IMAGES_STYLE', 'max-width:90px;', '1', '42', NULL, NOW(), NULL, NULL);";
  //$values[] = "(NULL, 'IBN_BILLNR', '1', '1', '99', NULL, NOW(), NULL, NULL);"; //modified 1.07
  //$values[] = "(NULL, 'IBN_BILLNR_FORMAT', '{n}-{d}-{m}-{y}', '1', '99', NULL, NOW(), NULL, NULL);"; //modified 1.07
  $values[] = "(NULL, 'USE_BROWSER_LANGUAGE', 'false', '1', '11', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";

  $values_update[] = array (
                           'values' => "configuration_group_id = '8'",
                           'configuration_key' => 'EXPECTED_PRODUCTS_SORT'
                           );
  $values_update[] = array (
                           'values' => "configuration_group_id = '8'",
                           'configuration_key' => 'EXPECTED_PRODUCTS_FIELD'
                           );
  $values_update[] = array (
                           'values' => "sort_order = '4'",
                           'configuration_key' => 'STORE_COUNTRY'
                           );
  $values_update[] = array (
                           'values' => " sort_order = '5'",
                           'configuration_key' => 'STORE_ZONE'
                           );
  $values_update[] = array (
                           'values' => "sort_order = '3'",
                           'configuration_key' => 'STORE_NAME_ADDRESS'
                           );
  $values_update[] = array (
                           'values' => "sort_order = '6'",
                           'configuration_key' => 'STORE_OWNER_EMAIL_ADDRESS'
                           );
  $values_update[] = array (
                           'values' => "sort_order = '7'",
                           'configuration_key' => 'EMAIL_FROM'
                           );
  $values_update[] = array (
                           'values' => "configuration_group_id = '1000', sort_order = '11'",
                           'configuration_key' => 'PRICE_PRECISION'
                           );

//configuration_group_id 2 --- "Minimum Werte"
  $values[] = "(NULL, 'POLICY_MIN_LOWER_CHARS', '1', '2', '12', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'POLICY_MIN_UPPER_CHARS', '1', '2', '12', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'POLICY_MIN_NUMERIC_CHARS', '1', '2', '12', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'POLICY_MIN_SPECIAL_CHARS', '1', '2', '12', NULL, NOW(), NULL, NULL);";

//configuration_group_id 3 --- "Maximalwerte"
  $values[] = "(NULL, 'MAX_DISPLAY_PRODUCTS_CATEGORY', '10', '3', '23', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'MAX_DISPLAY_ADVANCED_SEARCH_RESULTS', '10', '3', '24', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'MAX_DISPLAY_PRODUCTS_HISTORY', '6', '3', '25', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'MAX_DISPLAY_BESTSELLERS_DAYS', '100', '3', '15', NULL, NOW(), NULL, NULL);";

//configuration_group_id 4 --- "Bild Optionen"
  $values[] = "(NULL, 'PRODUCT_IMAGE_NO_ENLARGE_UNDER_DEFAULT', 'false', '4', '6', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'), ');";
  $values[] = "(NULL, 'PRODUCT_IMAGE_SHOW_NO_IMAGE', 'false', '4', '6', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'), ');";

//configuration_group_id 5 --- "Kundendetails"
  $values[] = "(NULL, 'ACCOUNT_TELEPHONE_OPTIONAL', 'false', '5', '70', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'), ');";
  $values[] = "(NULL, 'GUEST_ACCOUNT_EDIT', 'false', '5', '120', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'), ');";

//configuration_group_id 6 --- "Modul Optionen"
  $values[] = "(NULL, 'COMPRESS_STYLESHEET_TIME', '', '6', '100', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'NEWSFEED_LAST_READ', '', '6', '100', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'NEWSFEED_LAST_UPDATE', '', '6', '100', NULL, NOW(), NULL, NULL);";

//configuration_group_id 7 --- "Versandoptionen"
  //$values[] = "(NULL, 'SHIPPING_DEFAULT_TAX_CLASS_METHOD', '1', 7, 7, NULL, NOW(), 'xtc_get_default_tax_class_method_name', 'xtc_cfg_pull_down_default_tax_class_methods(');"; //modified 1.07
  $values[] = "(NULL, 'SHOW_SHIPPING_EXCL', 'true', '7', '6', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'), ');";

  $values_update[] = array (
                           'values' => "set_function = 'xtc_cfg_select_content(\'SHIPPING_INFOS\','",
                           'configuration_key' => 'SHIPPING_INFOS'
                           );

//configuration_group_id 8 --- "Artikel Listen Optionen"
  $values[] = "(NULL, 'SHOW_BUTTON_BUY_NOW', 'false', '8', '20', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'USE_PAGINATION_LIST', 'false', '8', '21', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'DISPLAY_FILTER_INDEX', '3,12,27,all', '8', '100', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'DISPLAY_FILTER_SPECIALS', '3,12,27,all', '8', '101', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'DISPLAY_FILTER_PRODUCTS_NEW', '3,12,27,all', '8', '102', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'DISPLAY_FILTER_ADVANCED_SEARCH_RESULT', '4,12,32,all', '8', '103', NULL, NOW(), NULL, NULL);";

//configuration_group_id 9 --- "Lagerverwaltungs Optionen"
  $values[] = "(NULL, 'STOCK_CHECKOUT_UPDATE_PRODUCTS_STATUS', 'false', '9', '20', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'STOCK_CHECK_SPECIALS', 'false', '9', '21', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";

//configuration_group_id 10 --- "Logging Optionen"
  $values[] = "(NULL, 'STORE_DB_SLOW_QUERY', 'false', '10', '6', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'STORE_DB_SLOW_QUERY_TIME', '1.0', '10', '7', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'DISPLAY_ERROR_REPORTING', 'none', '10', '8', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'none\', \'admin\', \'all\'),');";

  $values_update[] = array (
                           'values' => "configuration_group_id = '10', set_function = 'xtc_cfg_select_option(array(\'none\', \'admin\', \'all\'),'",
                           'configuration_key' => 'DISPLAY_PAGE_PARSE_TIME'
                           );

//configuration_group_id 11 --- "Cache Optionen"
  $values[] = "(NULL, 'DB_CACHE_TYPE', 'files', '11', '7', NULL, NOW(), NULL, 'xtc_cfg_pull_down_cache_type(\'DB_CACHE_TYPE\',');";

//configuration_group_id 12 --- "Email Optionen"
  $values[] = "(NULL, 'SMTP_SECURE', 'none', 12, 8, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'none\', \'ssl\', \'tls\'),');";
  $values[] = "(NULL, 'EMAIL_SQL_ERRORS', 'false', '12', '14', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'EMAIL_BILLING_ATTACHMENTS', '', '12', '39', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'SHOW_IMAGES_IN_EMAIL', 'false', '12', '15', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'SHOW_IMAGES_IN_EMAIL_DIR', 'thumbnail', '12', '16', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'thumbnail\', \'info\'),');";
  $values[] = "(NULL, 'SHOW_IMAGES_IN_EMAIL_STYLE', 'max-width:90px;max-height:120px;', '12', '17', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'SEND_EMAILS_DOUBLE_OPT_IN', 'true', '12', '14', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'SEND_MAIL_ACCOUNT_CREATED', 'false', '12', '14', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'EMAIL_WORD_WRAP', '50', '12', '18', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'EMAIL_SIGNATURE_ID', '', '12', '19', NULL, NOW(), NULL, 'xtc_cfg_select_content(\'EMAIL_SIGNATURE_ID\',');";

  $values_update[] = array (
                           'values' => "configuration_group_id = '12', set_function = 'xtc_cfg_input_email_language;CONTACT_US_EMAIL_ADDRESS'",
                           'configuration_key' => 'CONTACT_US_EMAIL_ADDRESS'
                           );
  $values_update[] = array (
                           'values' => "configuration_group_id = '12', set_function = 'xtc_cfg_input_email_language;CONTACT_US_NAME'",
                           'configuration_key' => 'CONTACT_US_NAME'
                           );
  $values_update[] = array (
                           'values' => "configuration_group_id = '12', set_function = 'xtc_cfg_input_email_language;CONTACT_US_REPLY_ADDRESS'",
                           'configuration_key' => 'CONTACT_US_REPLY_ADDRESS'
                           );
  $values_update[] = array (
                           'values' => "configuration_group_id = '12', set_function = 'xtc_cfg_input_email_language;CONTACT_US_REPLY_ADDRESS_NAME'",
                           'configuration_key' => 'CONTACT_US_REPLY_ADDRESS_NAME'
                           );
  $values_update[] = array (
                           'values' => "configuration_group_id = '12', set_function = 'xtc_cfg_input_email_language;CONTACT_US_EMAIL_SUBJECT'",
                           'configuration_key' => 'CONTACT_US_EMAIL_SUBJECT'
                           );
  $values_update[] = array (
                           'values' => "configuration_group_id = '12', set_function = 'xtc_cfg_input_email_language;CONTACT_US_FORWARDING_STRING'",
                           'configuration_key' => 'CONTACT_US_FORWARDING_STRING'
                           );
  $values_update[] = array (
                           'values' => "configuration_group_id = '12', set_function = 'xtc_cfg_input_email_language;EMAIL_SUPPORT_ADDRESS'",
                           'configuration_key' => 'EMAIL_SUPPORT_ADDRESS'
                           );
  $values_update[] = array (
                           'values' => "configuration_group_id = '12', set_function = 'xtc_cfg_input_email_language;EMAIL_SUPPORT_NAME'",
                           'configuration_key' => 'EMAIL_SUPPORT_NAME'
                           );
  $values_update[] = array (
                           'values' => "configuration_group_id = '12', set_function = 'xtc_cfg_input_email_language;EMAIL_SUPPORT_REPLY_ADDRESS'",
                           'configuration_key' => 'EMAIL_SUPPORT_REPLY_ADDRESS'
                           );
  $values_update[] = array (
                           'values' => "configuration_group_id = '12', set_function = 'xtc_cfg_input_email_language;EMAIL_SUPPORT_REPLY_ADDRESS_NAME'",
                           'configuration_key' => 'EMAIL_SUPPORT_REPLY_ADDRESS_NAME'
                           );
  $values_update[] = array (
                           'values' => "configuration_group_id = '12', set_function = 'xtc_cfg_input_email_language;EMAIL_SUPPORT_SUBJECT'",
                           'configuration_key' => 'EMAIL_SUPPORT_SUBJECT'
                           );
  $values_update[] = array (
                           'values' => "configuration_group_id = '12', set_function = 'xtc_cfg_input_email_language;EMAIL_SUPPORT_FORWARDING_STRING'",
                           'configuration_key' => 'EMAIL_SUPPORT_FORWARDING_STRING'
                           );
  $values_update[] = array (
                           'values' => "configuration_group_id = '12', set_function = 'xtc_cfg_input_email_language;EMAIL_BILLING_ADDRESS'",
                           'configuration_key' => 'EMAIL_BILLING_ADDRESS'
                           );
  $values_update[] = array (
                           'values' => "configuration_group_id = '12', set_function = 'xtc_cfg_input_email_language;EMAIL_BILLING_NAME'",
                           'configuration_key' => 'EMAIL_BILLING_NAME'
                           );
  $values_update[] = array (
                           'values' => "configuration_group_id = '12', set_function = 'xtc_cfg_input_email_language;EMAIL_BILLING_REPLY_ADDRESS'",
                           'configuration_key' => 'EMAIL_BILLING_REPLY_ADDRESS'
                           );
  $values_update[] = array (
                           'values' => "configuration_group_id = '12', set_function = 'xtc_cfg_input_email_language;EMAIL_BILLING_REPLY_ADDRESS_NAME'",
                           'configuration_key' => 'EMAIL_BILLING_REPLY_ADDRESS_NAME'
                           );
  $values_update[] = array (
                           'values' => "configuration_group_id = '12', set_function = 'xtc_cfg_input_email_language;EMAIL_BILLING_SUBJECT'",
                           'configuration_key' => 'EMAIL_BILLING_SUBJECT'
                           );
  $values_update[] = array (
                           'values' => "configuration_group_id = '12', set_function = 'xtc_cfg_input_email_language;EMAIL_BILLING_FORWARDING_STRING'",
                           'configuration_key' => 'EMAIL_BILLING_FORWARDING_STRING'
                           );
  $values_update[] = array (
                           'values' => "configuration_group_id = '12', set_function = 'xtc_cfg_input_email_language;EMAIL_BILLING_SUBJECT_ORDER'",
                           'configuration_key' => 'EMAIL_BILLING_SUBJECT_ORDER'
                           );
  $values_update[] = array (
                           'values' => "configuration_group_id = '12', set_function = 'xtc_cfg_input_email_language;EMAIL_BILLING_ATTACHMENTS'",
                           'configuration_key' => 'EMAIL_BILLING_ATTACHMENTS'
                           );

//configuration_group_id 13 --- "Download Optionen"
  $values_update[] = array (
                           'values' => "configuration_group_id = '13', set_function = 'xtc_cfg_multi_checkbox(\'xtc_get_orders_status\', \'chr(44)\','",
                           'configuration_key' => 'DOWNLOAD_MIN_ORDERS_STATUS'
                           );
  $values_update[] = array (
                           'values' => "configuration_group_id = '13', set_function = 'xtc_cfg_checkbox_unallowed_module(\'payment\', \'DOWNLOAD_UNALLOWED_PAYMENT\','",
                           'configuration_key' => 'DOWNLOAD_UNALLOWED_PAYMENT'
                           );
  $values[] = "(NULL, 'DOWNLOAD_MULTIPLE_ATTRIBUTES_ALLOWED', 'false', '13', '6', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'DOWNLOAD_SHOW_LANG_DROPDOWN', 'true', '13', '7', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";

//configuration_group_id 14 --- "GZIP Kompression"
  $values[] = "(NULL, 'COMPRESS_HTML_OUTPUT', 'true', 14, 3, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'COMPRESS_STYLESHEET', 'true', 14, 4, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";

//configuration_group_id 15 --- "Sessions"
  $values[] = "(NULL, 'SESSION_LIFE_CUSTOMERS', '1440', '15', '20', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'SESSION_LIFE_ADMIN', '7200', '15', '21', NULL, NOW(), NULL, NULL);";

//configuration_group_id 16 --- "Metatags Suchmaschinen"
  $values[] = "(NULL, 'DISPLAY_BREADCRUMB_OPTION', 'name', '16', '15', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'name\', \'model\'),');";
  $values[] = "(NULL, 'META_MAX_KEYWORD_LENGTH', '18', '16', '1', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'META_DESCRIPTION_LENGTH', '156', '16', '2', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'META_PRODUCTS_KEYWORDS_LENGTH', '255', '16', '2', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'META_KEYWORDS_LENGTH', '180', '16', '2', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'META_TITLE_LENGTH', '55', '16', '2', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'META_STOP_WORDS', '".utf8_decode('versandkosten,zzgl,mwst,lieferzeit,aber,alle,alles,als,auch,auf,aus,bei,beim,beinahe,bin,bis,ist,dabei,dadurch,daher,dank,darum,danach,das,daß,dass,dein,deine,dem,den,der,des,dessen,dadurch,deshalb,die,dies,diese,dieser,diesen,diesem,dieses,doch,dort,durch,eher,ein,eine,einem,einen,einer,eines,einige,einigen,einiges,eigene,eigenes,eigener,endlich,euer,eure,etwas,fast,findet,für,gab,gibt,geben,hatte,hatten,hattest,hattet,heute,hier,hinter,ich,ihr,ihre,ihn,ihm,im,immer,in,ist,ja,jede,jedem,jeden,jeder,jedes,jener,jenes,jetzt,kann,kannst,kein,können,könnt,machen,man,mein,meine,mehr,mit,muß,mußt,musst,müssen,müßt,nach,nachdem,neben,nein,nicht,nichts,noch,nun,nur,oder,statt,anstatt,seid,sein,seine,seiner,sich,sicher,sie,sind,soll,sollen,sollst,sollt,sonst,soweit,sowie,und,uns,unser,unsere,unserem,unseren,unter,vom,von,vor,wann,warum,was,war,weiter,weitere,wenn,wer,werde,widmen,widmet,viel,viele,vieles,weil,werden,werdet,weshalb,wie,wieder,wieso,wir,wird,wirst,wohl,woher,wohin,wurde,zum,zur,über')."', '16', '16', NULL, NOW(), NULL, 'xtc_cfg_textarea(');";
  $values[] = "(NULL, 'META_GO_WORDS', '".utf8_decode('tracht,dirndl,kleid,mode,modern,bluse,trachten,hose,leder,schmuck,t-shirt,t-shirts,schuh,schuhe')."', '16', '17', NULL, NOW(), NULL, 'xtc_cfg_textarea(');";
  $values[] = "(NULL, 'META_CAT_SHOP_TITLE', 'false', '16', '18', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'META_PROD_SHOP_TITLE', 'false', '16', '19', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'META_CONTENT_SHOP_TITLE', 'false', '16', '20', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'META_SPECIALS_SHOP_TITLE', 'false', '16', '21', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'META_NEWS_SHOP_TITLE', 'false', '16', '22', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'META_SEARCH_SHOP_TITLE', 'false', '16', '23', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'META_OTHER_SHOP_TITLE', 'false', '16', '24', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'META_GOOGLE_VERIFICATION_KEY', '', '16', '25', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'META_BING_VERIFICATION_KEY', '', '16', '26', NULL, NOW(), NULL, NULL);";

//configuration_group_id 17 --- "Zusatzmodule"
  $values_group[] = "(17,'Additional Modules','Additional Modules',17,1);";
  $values[] = "(NULL, 'GOOGLE_RSS_FEED_REFID', '', 17, 15, NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'SAVE_IP_LOG', 'false', 17, 8, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\', \'xxx\'),');";
  $values[] = "(NULL, 'SHIPPING_STATUS_INFOS', '', 17, 14, NULL, NOW(), NULL, 'xtc_cfg_select_content(\'SHIPPING_STATUS_INFOS\',');";
  $values[] = "(NULL, 'MODULE_SMALL_BUSINESS', 'false', 17, 9, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'WYSIWYG_TEMPLATE', 'moonocolor', 17, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'moono\', \'moonocolor\'),');";

  $values_update[] = array (
                           'values' => "set_function = 'xtc_cfg_select_content(\'REVOCATION_ID\','",
                           'configuration_key' => 'REVOCATION_ID'
                           );

//configuration_group_id 18 --- "UST-ID"

//configuration_group_id 19 --- "Google Conversionr"
  $values[] = "(NULL, 'GOOGLE_CONVERSION_LABEL', 'Purchase', '19', '4', NULL, NOW(), NULL, NULL);";

//configuration_group_id 20 --- "Import/export"
  $values[] = "(NULL, 'CSV_CATEGORY_DEFAULT', '0', '20', '4', NULL, NOW(), NULL, 'xtc_cfg_get_category_tree(');";
  $values[] = "(NULL, 'CSV_CAT_DEPTH', '4', '20', '5', NULL, NOW(), NULL, NULL);";
//configuration_group_id 21 --- "Afterbuy"
  //$values[] = "(NULL, 'AFTERBUY_DEALERS', '3', '21', '7', NULL , NOW(), NULL , NULL);";
  //$values[] = "(NULL, 'AFTERBUY_IGNORE_GROUPE', '', '21', '8', NULL , NOW(), NULL , NULL);";

//configuration_group_id 22 --- "Such-Optionen"
  $values[] = "(NULL, 'SEARCH_IN_MANU', 'true', 22, 4, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');"; 
  //$values[] = "(NULL, 'SEARCH_HIGHLIGHT', 'true', 22, 4, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";   //modified 2.10
  //$values[] = "(NULL, 'SEARCH_HIGHLIGHT_STYLE', 'color:#000;background-color:#eee;border:dotted #000 1px;', 22, 5, NULL, NOW(), NULL, NULL);"; //modified 2.10

//configuration_group_id 23 --- "Econda Tracking"
  $values_group[] = "(23,'Econda Tracking','Econda Tracking System',23,1);";

//configuration_group_id 24 --- "google analytics, piwik & facebook tracking"
  $values_group[] = "(24,'PIWIK &amp; Google Analytics Tracking','Settings for PIWIK &amp; Google Analytics Tracking',24,1);";

  $values[] = "(NULL, 'TRACKING_COUNT_ADMIN_ACTIVE', 'false', 24, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'TRACKING_GOOGLEANALYTICS_ACTIVE', 'false', 24, 2, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'TRACKING_GOOGLEANALYTICS_ID','UA-XXXXXXX-X', 24, 3, NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'TRACKING_GOOGLEANALYTICS_UNIVERSAL', 'false', 24, 3, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'TRACKING_GOOGLEANALYTICS_DOMAIN','example.de', 24, 3, NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'TRACKING_GOOGLE_LINKID', 'false', 24, 3, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'TRACKING_GOOGLE_DISPLAY', 'false', 24, 3, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'TRACKING_GOOGLE_ECOMMERCE', 'false', 24, 3, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";

  $values[] = "(NULL, 'TRACKING_PIWIK_ACTIVE', 'false', 24, 4, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'TRACKING_PIWIK_LOCAL_PATH','www.domain.de/piwik', 24, 5, NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'TRACKING_PIWIK_ID','1', 24, 6, NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'TRACKING_PIWIK_GOAL','1', 24, 7, NULL, NOW(), NULL, NULL);";

  $values[] = "(NULL, 'TRACKING_FACEBOOK_ACTIVE', 'false', 24, 8, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'TRACKING_FACEBOOK_ID','', 24, 9, NULL, NOW(), NULL, NULL);";

//configuration_group_id 25 --- "captcha"
  $values_group[] = "(25,'Captcha','Captcha Configuration',25,1);";
  $values[] = "(NULL, 'MODULE_CAPTCHA_ACTIVE', 'newsletter,contact,password', 25, 1, NULL, NOW(), NULL, 'xtc_cfg_multi_checkbox(array(\'newsletter\' => \'Newsletter\', \'contact\' => \'Contact\', \'password\' => \'Password\', \'reviews\' => \'Reviews\'), \',\',');";
  $values[] = "(NULL, 'MODULE_CAPTCHA_LOGGED_IN', 'False', 25, 2, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'True\', \'False\'),');";
  $values[] = "(NULL, 'MODULE_CAPTCHA_USE_COLOR', 'False', 25, 10, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'True\', \'False\'),');";
  $values[] = "(NULL, 'MODULE_CAPTCHA_USE_SHADOW', 'False', 25, 11, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'True\', \'False\'),');";
  $values[] = "(NULL, 'MODULE_CAPTCHA_CODE_LENGTH', '6', '25', '12', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'MODULE_CAPTCHA_NUM_LINES', '70', '25', '13', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'MODULE_CAPTCHA_MIN_FONT', '24', '25', '14', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'MODULE_CAPTCHA_MAX_FONT', '28', '25', '15', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'MODULE_CAPTCHA_BACKGROUND_RGB', '192,192,192', '25', '16', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'MODULE_CAPTCHA_LINES_RGB', '220,148,002', '25', '17', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'MODULE_CAPTCHA_CHARS_RGB', '112,112,112', '25', '18', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'MODULE_CAPTCHA_WIDTH', '240', '25', '19', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'MODULE_CAPTCHA_HEIGHT', '50', '25', '20', NULL, NOW(), NULL, NULL);";

//configuration_group_id 31 --- "Moneybookers"
  $values_group[] = "(31,'Moneybookers','Moneybookers System',31,1);";

//configuration_group_id 40 --- "Popup window configuration"
  $values_group[] = "(40,'Popup Window Configuration','Popup Window Parameters',40,1);";

  $values[] = "(NULL, 'POPUP_SHIPPING_LINK_PARAMETERS', '&KeepThis=true&TB_iframe=true&height=400&width=600', '40', '10', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'POPUP_SHIPPING_LINK_CLASS', 'thickbox', '40', '11', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'POPUP_CONTENT_LINK_PARAMETERS', '&KeepThis=true&TB_iframe=true&height=400&width=600', '40', '20', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'POPUP_CONTENT_LINK_CLASS', 'thickbox', '40', '21', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'POPUP_PRODUCT_LINK_PARAMETERS', '&KeepThis=true&TB_iframe=true&height=450&width=750', '40', '30', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'POPUP_PRODUCT_LINK_CLASS', 'thickbox', '40', '31', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'POPUP_COUPON_HELP_LINK_PARAMETERS', '&KeepThis=true&TB_iframe=true&height=400&width=600', '40', '40', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'POPUP_COUPON_HELP_LINK_CLASS', 'thickbox', '40', '41', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'POPUP_PRODUCT_PRINT_SIZE', 'width=640, height=600', '40', '60', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'POPUP_PRINT_ORDER_SIZE', 'width=640, height=600', '40', '70', NULL, NOW(), NULL, NULL);";

//configuration_group_id 1000 --- "Adminbereich"
  $values_group[] = "(1000,'Adminarea Options','Adminarea Configuration', 1000,1);";

  $values[] = "(NULL, 'USE_ADMIN_FIXED_TOP', 'false', '1000', '23', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'USE_ADMIN_FIXED_SEARCH', 'false', '1000', '24', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'USE_ADMIN_THUMBS_IN_LIST', 'true', '1000', '32', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'USE_ADMIN_THUMBS_IN_LIST_STYLE', 'max-width:40px;max-height:40px;', '1000', '33', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'MAX_DISPLAY_ORDER_RESULTS', '30', '1000', '50', NULL , NOW(), NULL , NULL);";
  $values[] = "(NULL, 'MAX_DISPLAY_LIST_PRODUCTS', '50', '1000', '51', NULL , NOW(), NULL , NULL);";
  $values[] = "(NULL, 'MAX_DISPLAY_LIST_CUSTOMERS', '100', '1000', '52', NULL , NOW(), NULL , NULL);";
  $values[] = "(NULL, 'MAX_ROW_LISTS_ATTR_OPTIONS', '10', '1000', '53', NULL , NOW(), NULL , NULL);";
  $values[] = "(NULL, 'MAX_ROW_LISTS_ATTR_VALUES', '50', '1000', '54', NULL , NOW(), NULL , NULL);";
  $values[] = "(NULL, 'WHOS_ONLINE_TIME_LAST_CLICK', '900', '1000', '60', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'WHOS_ONLINE_IP_WHOIS_SERVICE', 'http://www.utrace.de/?query=', '1000', '62', NULL, NOW(), NULL, NULL);"; 
  $values[] = "(NULL, 'CONFIRM_SAVE_ENTRY', 'true', '1000', '70', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'MAX_DISPLAY_STATS_RESULTS', '30', '1000', '55', NULL , NOW(), NULL , NULL);";
  $values[] = "(NULL, 'MAX_DISPLAY_COUPON_RESULTS', '30', '1000', '56', NULL , NOW(), NULL , NULL);";
  $values[] = "(NULL, 'MIN_GROUP_PRICE_STAFFEL', '2', '1000', '34', NULL , NOW(), NULL , NULL);";
  $values[] = "(NULL, 'ORDER_STATUSES_FOR_SALES_STATISTICS', '3', 1000, 100, NULL, NOW(), NULL, 'xtc_cfg_multi_checkbox(\'order_statuses\', \',\',');";
  $values[] = "(NULL, 'USE_ATTRIBUTES_IFRAME', 'true', '1000', '110', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'NEW_ATTRIBUTES_STYLING', 'true', '1000', '112', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'NEW_SELECT_CHECKBOX', 'true', '1000', '113', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'CSRF_TOKEN_SYSTEM', 'true', '1000', '114', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'ADMIN_HEADER_X_FRAME_OPTIONS', 'true', '1000', '115', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values_update[] = array (
                           'values' => "configuration_group_id = '1000', sort_order = '30'",
                           'configuration_key' => 'MAX_DISPLAY_ORDER_RESULTS'
                           );
  $values_update[] = array (
                           'values' => "configuration_group_id = '1000', sort_order = '26'",
                           'configuration_key' => 'USE_ADMIN_LANG_TABS'
                           );

  //configuration_group_id 111125 --- "Paypal"
  if (defined('MODULE_PAYMENT_PAYPAL_STATUS') || defined('MODULE_PAYMENT_PAYPALEXPRESS_STATUS')) {
    $values[] = "(NULL, 'PAYPAL_BRANDNAME', '', '111125', '26', NULL, NOW(), NULL, NULL);";
  }
//##############################//

$cfg_installer_fileemtime = filemtime(DIR_WS_INCLUDES.'configuration_installer.php');

if (!defined('CFG_INTSTALLER_FILEEMTIME') || CFG_INTSTALLER_FILEEMTIME != $cfg_installer_fileemtime) {
    if (!defined('CFG_INTSTALLER_FILEEMTIME')) {
        $cfg_data_array = array(
            'configuration_key' => 'CFG_INTSTALLER_FILEEMTIME',
            'configuration_value' => $cfg_installer_fileemtime,
            'configuration_group_id' => '1000',
            'sort_order' => '-1',
            'last_modified' => 'now()',
            'date_added' => 'now()'
            );
        xtc_db_perform(TABLE_CONFIGURATION,$cfg_data_array);   
    } else {
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " 
                         SET configuration_value = '" . xtc_db_input($cfg_installer_fileemtime) . "', 
                             last_modified = NOW()
                       WHERE configuration_key = 'CFG_INTSTALLER_FILEEMTIME'
                     ");
    }

    //install configuration group
    $cfg_group_install = insert_into_config_group_table($values_group);

    //update configuration group
    $cfg_group_update = update_config_group_table($values_group_update);

    //install configuration
    $cfg_install = insert_into_config_table($values);

    //update configuration
    $cfg_update = update_config_table($values_update);

    //redirect
    if ($cfg_install || $cfg_group_install || $cfg_update || $cfg_group_update) {
      xtc_redirect(xtc_href_link(FILENAME_CONFIGURATION, 'gID=' . (int)$_GET['gID']));
    }
}

//---------- FUNCTIONS ----------//

  /**
   * insert_into_config_table()
   *
   * @param string $values
   * @return boolean
   */
function insert_into_config_table($values)
{
  global $messageStack;
  //print_r($values);
  $install = false;
  foreach($values as $value) {
    $cfg_arr = explode(',', $value);
    $cfg_key = str_replace("'", '',$cfg_arr[1]); // Hochkommata entfernen
    $result_cfg = xtc_db_query("SELECT * FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = '" . trim($cfg_key) . "' LIMIT 1");
    if (xtc_db_num_rows($result_cfg) == 0) {
      $insert_into = "INSERT INTO ".TABLE_CONFIGURATION." (configuration_id ,configuration_key ,configuration_value ,configuration_group_id ,sort_order ,last_modified ,date_added ,use_function ,set_function) VALUES ";
      if( xtc_db_query($insert_into.$value)){
        $messageStack->add_session('OK: INSERT INTO '.TABLE_CONFIGURATION.' '.$value, 'success');
        $install = true;
      } else {
        $messageStack->add_session('ERROR: INSERT INTO '.TABLE_CONFIGURATION.' '.$value, 'error');
      }
    }
  }
  return $install;
}

  /**
   * update_config_table()
   *
   * @param array $values
   * @return boolean
   */
function update_config_table($values)
{
  global $messageStack;

  $install = false;
  foreach($values as $value) {
    //don't update configuration_value
    if (strpos($value['values'], 'configuration_value') === false) {
 
      $cfg_values = rtrim($value['values'],',');
      $cfg_key = trim($value['configuration_key']);
      
      //only update if values are different       
      $check = str_replace("),'","|#|", $cfg_values);
      $check = str_replace(array(", \'",",\'",",'"),"|##|", $check);     
      $check = str_replace(array('use_function', 'set_function'), 'IFNULL(set_function', $check);
      $check = " AND (" . str_replace(array("=", ","),array("!=", " OR "),$check); 
      if (strpos($check, 'IFNULL') !== false) {
        $check .= '|###| TRUE)';
      }
      $check .= ')';
      $check = str_replace(array("|##|","|#|"), array(", \'","),'"), $check);
      $check = str_replace("\', \')", "\',')", $check); 
      $check = str_replace("\'|###|", "',", $check); 
      $check = str_replace("|###|", ",", $check); 
      
      $result_cfg = xtc_db_query("SELECT * FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = '" . $cfg_key ."' ". trim($check)." LIMIT 1");
      if (xtc_db_num_rows($result_cfg) != 0) {
        $update = "UPDATE ".TABLE_CONFIGURATION." SET ".$cfg_values." , last_modified = NOW() WHERE configuration_key = '" . $cfg_key . "'";

        if( xtc_db_query($update)){
          $messageStack->add_session('OK: '.$update, 'success');
          $install = true;
        } else {
          $messageStack->add_session('ERROR: '.$update, 'error');
        }
      }
    }
  }
  return $install;
}

  /**
   * insert_into_config_group_table()
   *
   * @param string $values_group
   * @return boolean
   */
function insert_into_config_group_table($values_group)
{
  global $messageStack;
  $install = false;
  foreach($values_group as $value) {
    $cfg_arr = explode(',', $value);
    $cfg_id = str_replace(array("(","'"), '',$cfg_arr[0]);
    $query = "SELECT * FROM ".TABLE_CONFIGURATION_GROUP." WHERE configuration_group_id = '".$cfg_id ."' LIMIT 1";
    $result_cfg_query = xtc_db_query($query);
    if (xtc_db_num_rows($result_cfg_query) == 0) {
      $insert_into = "INSERT INTO ".TABLE_CONFIGURATION_GROUP ." VALUES ";
      if (xtc_db_query($insert_into.$value)) {
        $messageStack->add_session('OK: INSERT INTO '.TABLE_CONFIGURATION_GROUP.' '.$value, 'success');
        $install = true;
      } else {
        $messageStack->add_session('ERROR: INSERT INTO '.TABLE_CONFIGURATION_GROUP.' '.$value, 'error');
      }
    }
  }
  return $install;
}

  /**
   * update_config_group_table()
   *
   * @param array $values_group
   * @return boolean
   */
function update_config_group_table($values_group)
{
  global $messageStack;
  $install = false;
  foreach($values_group as $value) {
    $cfg_values = rtrim($value['values'],',');
    $cfg_id = $value['configuration_group_id'];
    //only update if values are different
    $check = " AND (" . str_replace(array("=",","),array("!="," OR "),$cfg_values). ")";
    $query = "SELECT * FROM ".TABLE_CONFIGURATION_GROUP." WHERE configuration_group_id = '".$cfg_id . "'". $check." LIMIT 1";
    $result_cfg_query = xtc_db_query($query);
    if (xtc_db_num_rows($result_cfg_query) != 0) {      
      $update = "UPDATE ".TABLE_CONFIGURATION_GROUP." SET ".$cfg_values." WHERE configuration_group_id = '" . $cfg_id . "'";
      if (xtc_db_query($update)) {
        $messageStack->add_session('OK: '.$update, 'success');
        $install = true;
      } else {
        $messageStack->add_session('ERROR: '.$update, 'error');
      }
    }
  }
  return $install;
}