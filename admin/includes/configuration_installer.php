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
  $values[] = "(NULL, 'CATEGORIES_IMAGE_SHOW_NO_IMAGE', 'true', '4', '6', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'), ');";
  $values[] = "(NULL, 'MANUFACTURER_IMAGE_SHOW_NO_IMAGE', 'false', '4', '6', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'), ');";

//configuration_group_id 5 --- "Kundendetails"
  $values[] = "(NULL, 'ACCOUNT_TELEPHONE_OPTIONAL', 'false', '5', '70', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'), ');";
  $values[] = "(NULL, 'GUEST_ACCOUNT_EDIT', 'false', '5', '120', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'), ');";
  $values[] = "(NULL, 'DISPLAY_PRIVACY_CHECK', 'true', '5', '130', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'), ');";

//configuration_group_id 6 --- "Modul Optionen"
  $values[] = "(NULL, 'COMPRESS_STYLESHEET_TIME', '', '6', '100', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'NEWSFEED_LAST_READ', '', '6', '100', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'NEWSFEED_LAST_UPDATE', '', '6', '100', NULL, NOW(), NULL, NULL);";

//configuration_group_id 7 --- "Versandoptionen"
  //$values[] = "(NULL, 'SHIPPING_DEFAULT_TAX_CLASS_METHOD', '1', 7, 7, NULL, NOW(), 'xtc_get_default_tax_class_method_name', 'xtc_cfg_pull_down_default_tax_class_methods(');"; //modified 1.07
  $values[] = "(NULL, 'SHOW_SHIPPING_EXCL', 'true', '7', '6', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'), ');";
  $values[] = "(NULL, 'CHECK_CHEAPEST_SHIPPING_MODUL', 'false', '7', '8', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'), ');";
  $values[] = "(NULL, 'SHOW_SELFPICKUP_FREE', 'false', '7', '9', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'), ');";

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
  $values[] = "(NULL, 'META_STOP_WORDS', '#german:\r\nab\r\nbei\r\nda\r\ndeshalb\r\nein\r\nfür\r\nhaben\r\nhier\r\nich\r\nja\r\nkann\r\nmachen\r\nmuesste\r\nnach\r\noder\r\nseid\r\nsonst\r\nund\r\nvom\r\nwann\r\nwenn\r\nwie\r\nzu\r\nbin\r\neines\r\nhat\r\nmanche\r\nsolches\r\nan\r\nanderm\r\nbis\r\ndas\r\ndeinem\r\ndemselben\r\ndir\r\ndoch\r\neinig\r\ner\r\neurer\r\nhatte\r\nihnen\r\nihre\r\nins\r\njenen\r\nkeinen\r\nmanchem\r\nmeinen\r\nnichts\r\nseine\r\nsoll\r\nunserm\r\nwelche\r\nwerden\r\nwollte\r\nwährend\r\nalle\r\nallem\r\nallen\r\naller\r\nalles\r\nals\r\nalso\r\nam\r\nander\r\nandere\r\nanderem\r\nanderen\r\nanderer\r\nanderes\r\nandern\r\nanders\r\nauch\r\nauf\r\naus\r\nbist\r\nbsp.\r\ndaher\r\ndamit\r\ndann\r\ndasselbe\r\ndazu\r\ndaß\r\ndein\r\ndeine\r\ndeinen\r\ndeiner\r\ndeines\r\ndem\r\nden\r\ndenn\r\ndenselben\r\nder\r\nderer\r\nderselbe\r\nderselben\r\ndes\r\ndesselben\r\ndessen\r\ndich\r\ndie\r\ndies\r\ndiese\r\ndieselbe\r\ndieselben\r\ndiesem\r\ndiesen\r\ndieser\r\ndieses\r\ndort\r\ndu\r\ndurch\r\neine\r\neinem\r\neinen\r\neiner\r\neinige\r\neinigem\r\neinigen\r\neiniger\r\neiniges\r\neinmal\r\nes\r\netwas\r\neuch\r\neuer\r\neure\r\neurem\r\neuren\r\neures\r\nganz\r\nganze\r\nganzen\r\nganzer\r\nganzes\r\ngegen\r\ngemacht\r\ngesagt\r\ngesehen\r\ngewesen\r\ngewollt\r\nhab\r\nhabe\r\nhatten\r\nhin\r\nhinter\r\nihm\r\nihn\r\nihr\r\nihrem\r\nihren\r\nihrer\r\nihres\r\nim\r\nin\r\nindem\r\nist\r\njede\r\njedem\r\njeden\r\njeder\r\njedes\r\njene\r\njenem\r\njener\r\njenes\r\njetzt\r\nkein\r\nkeine\r\nkeinem\r\nkeiner\r\nkeines\r\nkonnte\r\nkönnen\r\nkönnte\r\nmache\r\nmachst\r\nmacht\r\nmachte\r\nmachten\r\nman\r\nmanchen\r\nmancher\r\nmanches\r\nmein\r\nmeine\r\nmeinem\r\nmeiner\r\nmeines\r\nmich\r\nmir\r\nmit\r\nmuss\r\nmusste\r\nmüßt\r\nnicht\r\nnoch\r\nnun\r\nnur\r\nob\r\nohne\r\nsage\r\nsagen\r\nsagt\r\nsagte\r\nsagten\r\nsagtest\r\nsehe\r\nsehen\r\nsehr\r\nseht\r\nsein\r\nseinem\r\nseinen\r\nseiner\r\nseines\r\nselbst\r\nsich\r\nsicher\r\nsie\r\nsind\r\nso\r\nsolche\r\nsolchem\r\nsolchen\r\nsolcher\r\nsollte\r\nsondern\r\num\r\nuns\r\nunse\r\nunsen\r\nunser\r\nunses\r\nunter\r\nviel\r\nvon\r\nvor\r\nwar\r\nwaren\r\nwarst\r\nwas\r\nweg\r\nweil\r\nweiter\r\nwelchem\r\nwelchen\r\nwelcher\r\nwelches\r\nwerde\r\nwieder\r\nwill\r\nwir\r\nwird\r\nwirst\r\nwo\r\nwolle\r\nwollen\r\nwollt\r\nwollten\r\nwolltest\r\nwolltet\r\nwürde\r\nwürden\r\nz.B.\r\nzum\r\nzur\r\nzwar\r\nzwischen\r\nüber\r\naber\r\nabgerufen\r\nabgerufene\r\nabgerufener\r\nabgerufenes\r\nacht\r\nallein\r\nallerdings\r\nallerlei\r\nallgemein\r\nallmählich\r\nallzu\r\nalsbald\r\nandererseits\r\nandernfalls\r\nanerkannt\r\nanerkannte\r\nanerkannter\r\nanerkanntes\r\nanfangen\r\nanfing\r\nangefangen\r\nangesetze\r\nangesetzt\r\nangesetzten\r\nangesetzter\r\nansetzen\r\nanstatt\r\narbeiten\r\naufgehört\r\naufgrund\r\naufhören\r\naufhörte\r\naufzusuchen\r\nausdrücken\r\nausdrückt\r\nausdrückte\r\nausgenommen\r\nausser\r\nausserdem\r\nauthor\r\nautor\r\naußen\r\naußer\r\naußerdem\r\naußerhalb\r\nbald\r\nbearbeite\r\nbearbeiten\r\nbearbeitete\r\nbearbeiteten\r\nbedarf\r\nbedurfte\r\nbedürfen\r\nbefragen\r\nbefragte\r\nbefragten\r\nbefragter\r\nbegann\r\nbeginnen\r\nbegonnen\r\nbehalten\r\nbehielt\r\nbeide\r\nbeiden\r\nbeiderlei\r\nbeides\r\nbeim\r\nbeinahe\r\nbeitragen\r\nbeitrugen\r\nbekannt\r\nbekannte\r\nbekannter\r\nbekennen\r\nbenutzt\r\nbereits\r\nberichten\r\nberichtet\r\nberichtete\r\nberichteten\r\nbesonders\r\nbesser\r\nbestehen\r\nbesteht\r\nbeträchtlich\r\nbevor\r\nbezüglich\r\nbietet\r\nbisher\r\nbislang\r\nbis\r\nbleiben\r\nblieb\r\nbloss\r\nbloß\r\nbrachte\r\nbrachten\r\nbrauchen\r\nbraucht\r\nbringen\r\nbräuchte\r\nbzw\r\nböden\r\nca.\r\ndabei\r\ndadurch\r\ndafür\r\ndagegen\r\ndahin\r\ndamals\r\ndanach\r\ndaneben\r\ndank\r\ndanke\r\ndanken\r\ndannen\r\ndaran\r\ndarauf\r\ndaraus\r\ndarf\r\ndarfst\r\ndarin\r\ndarum\r\ndarunter\r\ndarüber\r\ndarüberhinaus\r\ndass\r\ndavon\r\ndavor\r\ndemnach\r\ndenen\r\ndennoch\r\nderart\r\nderartig\r\nderem\r\nderen\r\nderjenige\r\nderjenigen\r\nderzeit\r\ndesto\r\ndeswegen\r\ndiejenige\r\ndiesseits\r\ndinge\r\ndirekt\r\ndirekte\r\ndirekten\r\ndirekter\r\ndoppelt\r\ndorther\r\ndorthin\r\ndrauf\r\ndrei\r\ndreißig\r\ndrin\r\ndritte\r\ndrunter\r\ndrüber\r\ndunklen\r\ndurchaus\r\ndurfte\r\ndurften\r\ndürfen\r\ndürfte\r\neben\r\nebenfalls\r\nebenso\r\nehe\r\neher\r\neigenen\r\neigenes\r\neigentlich\r\neinbaün\r\neinerseits\r\neinfach\r\neinführen\r\neinführte\r\neinführten\r\neingesetzt\r\neinigermaßen\r\neins\r\neinseitig\r\neinseitige\r\neinseitigen\r\neinseitiger\r\neinst\r\neinstmals\r\neinzig\r\nende\r\nentsprechend\r\nentweder\r\nergänze\r\nergänzen\r\nergänzte\r\nergänzten\r\nerhalten\r\nerhielt\r\nerhielten\r\nerhält\r\nerneut\r\nerst\r\nerste\r\nersten\r\nerster\r\neröffne\r\neröffnen\r\neröffnet\r\neröffnete\r\neröffnetes\r\netc\r\netliche\r\netwa\r\nfall\r\nfalls\r\nfand\r\nfast\r\nferner\r\nfinden\r\nfindest\r\nfindet\r\nfolgende\r\nfolgenden\r\nfolgender\r\nfolgendes\r\nfolglich\r\nfordern\r\nfordert\r\nforderte\r\nforderten\r\nfortsetzen\r\nfortsetzt\r\nfortsetzte\r\nfortsetzten\r\nfragte\r\nfrau\r\nfrei\r\nfreie\r\nfreier\r\nfreies\r\nfuer\r\nfünf\r\ngab\r\nganzem\r\ngar\r\ngbr\r\ngeb\r\ngeben\r\ngeblieben\r\ngebracht\r\ngedurft\r\ngeehrt\r\ngeehrte\r\ngeehrten\r\ngeehrter\r\ngefallen\r\ngefiel\r\ngefälligst\r\ngefällt\r\ngegeben\r\ngehabt\r\ngehen\r\ngeht\r\ngekommen\r\ngekonnt\r\ngemocht\r\ngemäss\r\ngenommen\r\ngenug\r\ngern\r\ngestern\r\ngestrige\r\ngetan\r\ngeteilt\r\ngeteilte\r\ngetragen\r\ngewissermaßen\r\ngeworden\r\nggf\r\ngib\r\ngibt\r\ngleich\r\ngleichwohl\r\ngleichzeitig\r\nglücklicherweise\r\ngmbh\r\ngratulieren\r\ngratuliert\r\ngratulierte\r\ngute\r\nguten\r\ngängig\r\ngängige\r\ngängigen\r\ngängiger\r\ngängiges\r\ngänzlich\r\nhaette\r\nhalb\r\nhallo\r\nhast\r\nhattest\r\nhattet\r\nheraus\r\nherein\r\nheute\r\nheutige\r\nhiermit\r\nhiesige\r\nhinein\r\nhinten\r\nhinterher\r\nhoch\r\nhundert\r\nhätt\r\nhätte\r\nhätten\r\nhöchstens\r\nigitt\r\nimmer\r\nimmerhin\r\nimportant\r\nindessen\r\ninfo\r\ninfolge\r\ninnen\r\ninnerhalb\r\ninsofern\r\ninzwischen\r\nirgend\r\nirgendeine\r\nirgendwas\r\nirgendwen\r\nirgendwer\r\nirgendwie\r\nirgendwo\r\nje\r\njedenfalls\r\njederlei\r\njedoch\r\njemand\r\njenseits\r\njährig\r\njährige\r\njährigen\r\njähriges\r\nkam\r\nkannst\r\nkaum\r\nkeines\r\nkeinerlei\r\nkeineswegs\r\nklar\r\nklare\r\nklaren\r\nklares\r\nklein\r\nkleinen\r\nkleiner\r\nkleines\r\nkoennen\r\nkoennt\r\nkoennte\r\nkoennten\r\nkomme\r\nkommen\r\nkommt\r\nkonkret\r\nkonkrete\r\nkonkreten\r\nkonkreter\r\nkonkretes\r\nkonnten\r\nkönn\r\nkönnt\r\nkönnten\r\nkünftig\r\nlag\r\nlagen\r\nlangsam\r\nlassen\r\nlaut\r\nlediglich\r\nleer\r\nlegen\r\nlegte\r\nlegten\r\nleicht\r\nleider\r\nlesen\r\nletze\r\nletzten\r\nletztendlich\r\nletztens\r\nletztes\r\nletztlich\r\nlichten\r\nliegt\r\nliest\r\nlinks\r\nlängst\r\nlängstens\r\nmag\r\nmagst\r\nmal\r\nmancherorts\r\nmanchmal\r\nmann\r\nmargin\r\nmehr\r\nmehrere\r\nmeist\r\nmeiste\r\nmeisten\r\nmeta\r\nmindestens\r\nmithin\r\nmochte\r\nmorgen\r\nmorgige\r\nmuessen\r\nmuesst\r\nmusst\r\nmussten\r\nmuß\r\nmußt\r\nmöchte\r\nmöchten\r\nmöchtest\r\nmögen\r\nmöglich\r\nmögliche\r\nmöglichen\r\nmöglicher\r\nmöglicherweise\r\nmüssen\r\nmüsste\r\nmüssten\r\nmüßte\r\nnachdem\r\nnacher\r\nnachhinein\r\nnahm\r\nnatürlich\r\nnacht\r\nneben\r\nnebenan\r\nnehmen\r\nnein\r\nneu\r\nneue\r\nneuem\r\nneuen\r\nneuer\r\nneues\r\nneun\r\nnie\r\nniemals\r\nniemand\r\nnimm\r\nnimmer\r\nnimmt\r\nnirgends\r\nnirgendwo\r\nnutzen\r\nnutzt\r\nnutzung\r\nnächste\r\nnämlich\r\nnötigenfalls\r\nnützt\r\noben\r\noberhalb\r\nobgleich\r\nobschon\r\nobwohl\r\noft\r\nper\r\npfui\r\nplötzlich\r\npro\r\nreagiere\r\nreagieren\r\nreagiert\r\nreagierte\r\nrechts\r\nregelmäßig\r\nrief\r\nrund\r\nsang\r\nsangen\r\nschlechter\r\nschließlich\r\nschnell\r\nschon\r\nschreibe\r\nschreiben\r\nschreibens\r\nschreiber\r\nschwierig\r\nschätzen\r\nschätzt\r\nschätzte\r\nschätzten\r\nsechs\r\nsect\r\nsehrwohl\r\nsei\r\nseit\r\nseitdem\r\nseite\r\nseiten\r\nseither\r\nselber\r\nsenke\r\nsenken\r\nsenkt\r\nsenkte\r\nsenkten\r\nsetzen\r\nsetzt\r\nsetzte\r\nsetzten\r\nsicherlich\r\nsieben\r\nsiebte\r\nsiehe\r\nsieht\r\nsingen\r\nsingt\r\nsobald\r\nsodaß\r\nsoeben\r\nsofern\r\nsofort\r\nsog\r\nsogar\r\nsolange\r\nsolc hen\r\nsolch\r\nsollen\r\nsollst\r\nsollt\r\nsollten\r\nsolltest\r\nsomit\r\nsonstwo\r\nsooft\r\nsoviel\r\nsoweit\r\nsowie\r\nsowohl\r\nspielen\r\nspäter\r\nstartet\r\nstartete\r\nstarteten\r\nstatt\r\nstattdessen\r\nsteht\r\nsteige\r\nsteigen\r\nsteigt\r\nstets\r\nstieg\r\nstiegen\r\nsuch\r\nsuchen\r\nsämtliche\r\ntages\r\ntat\r\ntatsächlich\r\ntatsächlichen\r\ntatsächlicher\r\ntatsächliches\r\ntausend\r\nteile\r\nteilen\r\nteilte\r\nteilten\r\ntitel\r\ntotal\r\ntrage\r\ntragen\r\ntrotzdem\r\ntrug\r\nträgt\r\ntun\r\ntust\r\ntut\r\ntxt\r\ntät\r\nueber\r\numso\r\nunbedingt\r\nungefähr\r\nunmöglich\r\nunmögliche\r\nunmöglichen\r\nunmöglicher\r\nunnötig\r\nunsem\r\nunser\r\nunsere\r\nunserem\r\nunseren\r\nunserer\r\nunseres\r\nunten\r\nunterbrach\r\nunterbrechen\r\nunterhalb\r\nunwichtig\r\nusw\r\nvergangen\r\nvergangene\r\nvergangener\r\nvergangenes\r\nvermag\r\nvermutlich\r\nvermögen\r\nverrate\r\nverraten\r\nverriet\r\nverrieten\r\nversion\r\nversorge\r\nversorgen\r\nversorgt\r\nversorgte\r\nversorgten\r\nversorgtes\r\nveröffentlichen\r\nveröffentlicher\r\nveröffentlicht\r\nveröffentlichte\r\nveröffentlichten\r\nveröffentlichtes\r\nviele\r\nvielen\r\nvieler\r\nvieles\r\nvielleicht\r\nvielmals\r\nvier\r\nvollständig\r\nvoran\r\nvorbei\r\nvorgestern\r\nvorher\r\nvorne\r\nvorüber\r\nvöllig\r\nwährend\r\nwachen\r\nwaere\r\nwarum\r\nweder\r\nwegen\r\nweitere\r\nweiterem\r\nweiteren\r\nweiterer\r\nweiteres\r\nweiterhin\r\nweiß\r\nwem\r\nwen\r\nwenig\r\nwenige\r\nweniger\r\nwenigstens\r\nwenngleich\r\nwer\r\nwerdet\r\nweshalb\r\nwessen\r\nwichtig\r\nwieso\r\nwieviel\r\nwiewohl\r\nwillst\r\nwirklich\r\nwodurch\r\nwogegen\r\nwoher\r\nwohin\r\nwohingegen\r\nwohl\r\nwohlweislich\r\nwomit\r\nworaufhin\r\nworaus\r\nworin\r\nwurde\r\nwurden\r\nwährenddessen\r\nwär\r\nwäre\r\nwären\r\nzahlreich\r\nzehn\r\nzeitweise\r\nziehen\r\nzieht\r\nzog\r\nzogen\r\nzudem\r\nzuerst\r\nzufolge\r\nzugleich\r\nzuletzt\r\nzumal\r\nzurück\r\nzusammen\r\nzuviel\r\nzwanzig\r\nzwei\r\nzwölf\r\nähnlich\r\nübel\r\nüberall\r\nüberallhin\r\nüberdies\r\nübermorgen\r\nübrig\r\nübrigens\r\n\r\n#english:\r\nable\r\nabout\r\nabove\r\nabroad\r\naccording\r\naccordingly\r\nacross\r\nactually\r\nadj\r\nafter\r\nafterwards\r\nagain\r\nagainst\r\nago\r\nahead\r\nain\\''t\r\nall\r\nallow\r\nallows\r\nalmost\r\nalone\r\nalong\r\nalongside\r\nalready\r\nalso\r\nalthough\r\nalways\r\nam\r\namid\r\namidst\r\namong\r\namongst\r\nan\r\nand\r\nanother\r\nany\r\nanybody\r\nanyhow\r\nanyone\r\nanything\r\nanyway\r\nanyways\r\nanywhere\r\napart\r\nappear\r\nappreciate\r\nappropriate\r\nare\r\naren\\''t\r\naround\r\nas\r\na\\''s\r\naside\r\nask\r\nasking\r\nassociated\r\nat\r\navailable\r\naway\r\nawfully\r\nback\r\nbackward\r\nbackwards\r\nbe\r\nbecame\r\nbecause\r\nbecome\r\nbecomes\r\nbecoming\r\nbeen\r\nbefore\r\nbeforehand\r\nbegin\r\nbehind\r\nbeing\r\nbelieve\r\nbelow\r\nbeside\r\nbesides\r\nbest\r\nbetter\r\nbetween\r\nbeyond\r\nboth\r\nbrief\r\nbut\r\nby\r\ncame\r\ncan\r\ncannot\r\ncant\r\ncan\\''t\r\ncaption\r\ncause\r\ncauses\r\ncertain\r\ncertainly\r\nchanges\r\nclearly\r\nc\\''mon\r\nco\r\nco.\r\ncom\r\ncome\r\ncomes\r\nconcerning\r\nconsequently\r\nconsider\r\nconsidering\r\ncontain\r\ncontaining\r\ncontains\r\ncorresponding\r\ncould\r\ncouldn\\''t\r\ncourse\r\nc\\''s\r\ncurrently\r\ndare\r\ndaren\\''t\r\ndefinitely\r\ndescribed\r\ndespite\r\ndid\r\ndidn\\''t\r\ndifferent\r\ndirectly\r\ndo\r\ndoes\r\ndoesn\\''t\r\ndoing\r\ndone\r\ndon\\''t\r\ndown\r\ndownwards\r\nduring\r\neach\r\nedu\r\neg\r\neight\r\neighty\r\neither\r\nelse\r\nelsewhere\r\nend\r\nending\r\nenough\r\nentirely\r\nespecially\r\net\r\netc\r\neven\r\never\r\nevermore\r\nevery\r\neverybody\r\neveryone\r\neverything\r\neverywhere\r\nex\r\nexactly\r\nexample\r\nexcept\r\nfairly\r\nfar\r\nfarther\r\nfew\r\nfewer\r\nfifth\r\nfirst\r\nfive\r\nfollowed\r\nfollowing\r\nfollows\r\nfor\r\nforever\r\nformer\r\nformerly\r\nforth\r\nforward\r\nfound\r\nfour\r\nfrom\r\nfurther\r\nfurthermore\r\nget\r\ngets\r\ngetting\r\ngiven\r\ngives\r\ngo\r\ngoes\r\ngoing\r\ngone\r\ngot\r\ngotten\r\ngreetings\r\nhad\r\nhadn\\''t\r\nhalf\r\nhappens\r\nhardly\r\nhas\r\nhasn\\''t\r\nhave\r\nhaven\\''t\r\nhaving\r\nhe\r\nhe\\''d\r\nhe\\''ll\r\nhello\r\nhelp\r\nhence\r\nher\r\nhere\r\nhereafter\r\nhereby\r\nherein\r\nhere\\''s\r\nhereupon\r\nhers\r\nherself\r\nhe\\''s\r\nhi\r\nhim\r\nhimself\r\nhis\r\nhither\r\nhopefully\r\nhow\r\nhowbeit\r\nhowever\r\nhundred\r\ni\\''d\r\nie\r\nif\r\nignored\r\ni\\''ll\r\ni\\''m\r\nimmediate\r\nin\r\ninasmuch\r\ninc\r\ninc.\r\nindeed\r\nindicate\r\nindicated\r\nindicates\r\ninner\r\ninside\r\ninsofar\r\ninstead\r\ninto\r\ninward\r\nis\r\nisn\\''t\r\nit\r\nit\\''d\r\nit\\''ll\r\nits\r\nit\\''s\r\nitself\r\ni\\''ve\r\njust\r\nk\r\nkeep\r\nkeeps\r\nkept\r\nknow\r\nknown\r\nknows\r\nlast\r\nlately\r\nlater\r\nlatter\r\nlatterly\r\nleast\r\nless\r\nlest\r\nlet\r\nlet\\''s\r\nlike\r\nliked\r\nlikely\r\nlikewise\r\nlittle\r\nlook\r\nlooking\r\nlooks\r\nlow\r\nlower\r\nltd\r\nmade\r\nmainly\r\nmake\r\nmakes\r\nmany\r\nmay\r\nmaybe\r\nmayn\\''t\r\nme\r\nmean\r\nmeantime\r\nmeanwhile\r\nmerely\r\nmight\r\nmightn\\''t\r\nmine\r\nminus\r\nmiss\r\nmore\r\nmoreover\r\nmost\r\nmostly\r\nmr\r\nmrs\r\nmuch\r\nmust\r\nmustn\\''t\r\nmy\r\nmyself\r\nname\r\nnamely\r\nnd\r\nnear\r\nnearly\r\nnecessary\r\nneed\r\nneedn\\''t\r\nneeds\r\nneither\r\nnever\r\nneverf\r\nneverless\r\nnevertheless\r\nnew\r\nnext\r\nnine\r\nninety\r\nno\r\nnobody\r\nnon\r\nnone\r\nnonetheless\r\nnoone\r\nno-one\r\nnor\r\nnormally\r\nnot\r\nnothing\r\nnotwithstanding\r\nnovel\r\nnow\r\nnowhere\r\nobviously\r\nof\r\noff\r\noften\r\noh\r\nok\r\nokay\r\nold\r\non\r\nonce\r\none\r\nones\r\none\\''s\r\nonly\r\nonto\r\nopposite\r\nor\r\nother\r\nothers\r\notherwise\r\nought\r\noughtn\\''t\r\nour\r\nours\r\nourselves\r\nout\r\noutside\r\nover\r\noverall\r\nown\r\nparticular\r\nparticularly\r\npast\r\nper\r\nperhaps\r\nplaced\r\nplease\r\nplus\r\npossible\r\npresumably\r\nprobably\r\nprovided\r\nprovides\r\nque\r\nquite\r\nqv\r\nrather\r\nrd\r\nre\r\nreally\r\nreasonably\r\nrecent\r\nrecently\r\nregarding\r\nregardless\r\nregards\r\nrelatively\r\nrespectively\r\nright\r\nround\r\nsaid\r\nsame\r\nsaw\r\nsay\r\nsaying\r\nsays\r\nsecond\r\nsecondly\r\nsee\r\nseeing\r\nseem\r\nseemed\r\nseeming\r\nseems\r\nseen\r\nself\r\nselves\r\nsensible\r\nsent\r\nserious\r\nseriously\r\nseven\r\nseveral\r\nshall\r\nshan\\''t\r\nshe\r\nshe\\''d\r\nshe\\''ll\r\nshe\\''s\r\nshould\r\nshouldn\\''t\r\nsince\r\nsix\r\nso\r\nsome\r\nsomebody\r\nsomeday\r\nsomehow\r\nsomeone\r\nsomething\r\nsometime\r\nsometimes\r\nsomewhat\r\nsomewhere\r\nsoon\r\nsorry\r\nspecified\r\nspecify\r\nspecifying\r\nstill\r\nsub\r\nsuch\r\nsup\r\nsure\r\ntake\r\ntaken\r\ntaking\r\ntell\r\ntends\r\nth\r\nthan\r\nthank\r\nthanks\r\nthanx\r\nthat\r\nthat\\''ll\r\nthats\r\nthat\\''s\r\nthat\\''ve\r\nthe\r\ntheir\r\ntheirs\r\nthem\r\nthemselves\r\nthen\r\nthence\r\nthere\r\nthereafter\r\nthereby\r\nthere\\''d\r\ntherefore\r\ntherein\r\nthere\\''ll\r\nthere\\''re\r\ntheres\r\nthere\\''s\r\nthereupon\r\nthere\\''ve\r\nthese\r\nthey\r\nthey\\''d\r\nthey\\''ll\r\nthey\\''re\r\nthey\\''ve\r\nthing\r\nthings\r\nthink\r\nthird\r\nthirty\r\nthis\r\nthorough\r\nthoroughly\r\nthose\r\nthough\r\nthree\r\nthrough\r\nthroughout\r\nthru\r\nthus\r\ntill\r\nto\r\ntogether\r\ntoo\r\ntook\r\ntoward\r\ntowards\r\ntried\r\ntries\r\ntruly\r\ntry\r\ntrying\r\nt\\''s\r\ntwice\r\ntwo\r\nun\r\nunder\r\nunderneath\r\nundoing\r\nunfortunately\r\nunless\r\nunlike\r\nunlikely\r\nuntil\r\nunto\r\nup\r\nupon\r\nupwards\r\nus\r\nuse\r\nused\r\nuseful\r\nuses\r\nusing\r\nusually\r\nv\r\nvalue\r\nvarious\r\nversus\r\nvery\r\nvia\r\nviz\r\nvs\r\nwant\r\nwants\r\nwas\r\nwasn\\''t\r\nway\r\nwe\r\nwe\\''d\r\nwelcome\r\nwell\r\nwe\\''ll\r\nwent\r\nwere\r\nwe\\''re\r\nweren\\''t\r\nwe\\''ve\r\nwhat\r\nwhatever\r\nwhat\\''ll\r\nwhat\\''s\r\nwhat\\''ve\r\nwhen\r\nwhence\r\nwhenever\r\nwhere\r\nwhereafter\r\nwhereas\r\nwhereby\r\nwherein\r\nwhere\\''s\r\nwhereupon\r\nwherever\r\nwhether\r\nwhich\r\nwhichever\r\nwhile\r\nwhilst\r\nwhither\r\nwho\r\nwho\\''d\r\nwhoever\r\nwhole\r\nwho\\''ll\r\nwhom\r\nwhomever\r\nwho\\''s\r\nwhose\r\nwhy\r\nwill\r\nwilling\r\nwish\r\nwith\r\nwithin\r\nwithout\r\nwonder\r\nwon\\''t\r\nwould\r\nwouldn\\''t\r\nyes\r\nyet\r\nyou\r\nyou\\''d\r\nyou\\''ll\r\nyour\r\nyou\\''re\r\nyours\r\nyourself\r\nyourselves\r\nyou\\''ve\r\nzero', '16', '16', NULL, NOW(), NULL, 'xtc_cfg_textarea(');";
  $values[] = "(NULL, 'META_GO_WORDS', '', '16', '17', NULL, NOW(), NULL, 'xtc_cfg_textarea(');";
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
  $values[] = "(NULL, 'WYSIWYG_SKIN', 'moonocolor', 17, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'moono\', \'moonocolor\'),');";
  $values[] = "(NULL, 'CHECK_FIRST_PAYMENT_MODUL', 'false', '17', '16', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'), ');";

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