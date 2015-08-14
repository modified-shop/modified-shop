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
  $values[] = "(NULL, 'META_STOP_WORDS', '#german:\r\nab,bei,da,deshalb,ein,für,haben,hier,ich,ja,kann,machen,muesste,nach,oder,seid,sonst,und,vom,wann,wenn,wie,zu,bin,eines,hat,manche,solches,an,anderm,bis,das,deinem,demselben,dir,doch,einig,er,eurer,hatte,ihnen,ihre,ins,jenen,keinen,manchem,meinen,nichts,seine,soll,unserm,welche,werden,wollte,während,alle,allem,allen,aller,alles,als,also,am,ander,andere,anderem,anderen,anderer,anderes,andern,anders,auch,auf,aus,bist,bsp.,daher,damit,dann,dasselbe,dazu,daß,dein,deine,deinen,deiner,deines,dem,den,denn,denselben,der,derer,derselbe,derselben,des,desselben,dessen,dich,die,dies,diese,dieselbe,dieselben,diesem,diesen,dieser,dieses,dort,du,durch,eine,einem,einen,einer,einige,einigem,einigen,einiger,einiges,einmal,es,etwas,euch,euer,eure,eurem,euren,eures,ganz,ganze,ganzen,ganzer,ganzes,gegen,gemacht,gesagt,gesehen,gewesen,gewollt,hab,habe,hatten,hin,hinter,ihm,ihn,ihr,ihrem,ihren,ihrer,ihres,im,in,indem,ist,jede,jedem,jeden,jeder,jedes,jene,jenem,jener,jenes,jetzt,kein,keine,keinem,keiner,keines,konnte,können,könnte,mache,machst,macht,machte,machten,man,manchen,mancher,manches,mein,meine,meinem,meiner,meines,mich,mir,mit,muss,musste,müßt,nicht,noch,nun,nur,ob,ohne,sage,sagen,sagt,sagte,sagten,sagtest,sehe,sehen,sehr,seht,sein,seinem,seinen,seiner,seines,selbst,sich,sicher,sie,sind,so,solche,solchem,solchen,solcher,sollte,sondern,um,uns,unse,unsen,unser,unses,unter,viel,von,vor,war,waren,warst,was,weg,weil,weiter,welchem,welchen,welcher,welches,werde,wieder,will,wir,wird,wirst,wo,wolle,wollen,wollt,wollten,wolltest,wolltet,würde,würden,z.B.,zum,zur,zwar,zwischen,über,aber,abgerufen,abgerufene,abgerufener,abgerufenes,acht,allein,allerdings,allerlei,allgemein,allmählich,allzu,alsbald,andererseits,andernfalls,anerkannt,anerkannte,anerkannter,anerkanntes,anfangen,anfing,angefangen,angesetze,angesetzt,angesetzten,angesetzter,ansetzen,anstatt,arbeiten,aufgehört,aufgrund,aufhören,aufhörte,aufzusuchen,ausdrücken,ausdrückt,ausdrückte,ausgenommen,ausser,ausserdem,author,autor,außen,außer,außerdem,außerhalb,bald,bearbeite,bearbeiten,bearbeitete,bearbeiteten,bedarf,bedurfte,bedürfen,befragen,befragte,befragten,befragter,begann,beginnen,begonnen,behalten,behielt,beide,beiden,beiderlei,beides,beim,beinahe,beitragen,beitrugen,bekannt,bekannte,bekannter,bekennen,benutzt,bereits,berichten,berichtet,berichtete,berichteten,besonders,besser,bestehen,besteht,beträchtlich,bevor,bezüglich,bietet,bisher,bislang,bis,bleiben,blieb,bloss,bloß,brachte,brachten,brauchen,braucht,bringen,bräuchte,bzw,böden,ca.,dabei,dadurch,dafür,dagegen,dahin,damals,danach,daneben,dank,danke,danken,dannen,daran,darauf,daraus,darf,darfst,darin,darum,darunter,darüber,darüberhinaus,dass,davon,davor,demnach,denen,dennoch,derart,derartig,derem,deren,derjenige,derjenigen,derzeit,desto,deswegen,diejenige,diesseits,dinge,direkt,direkte,direkten,direkter,doppelt,dorther,dorthin,drauf,drei,dreißig,drin,dritte,drunter,drüber,dunklen,durchaus,durfte,durften,dürfen,dürfte,eben,ebenfalls,ebenso,ehe,eher,eigenen,eigenes,eigentlich,einbaün,einerseits,einfach,einführen,einführte,einführten,eingesetzt,einigermaßen,eins,einseitig,einseitige,einseitigen,einseitiger,einst,einstmals,einzig,ende,entsprechend,entweder,ergänze,ergänzen,ergänzte,ergänzten,erhalten,erhielt,erhielten,erhält,erneut,erst,erste,ersten,erster,eröffne,eröffnen,eröffnet,eröffnete,eröffnetes,etc,etliche,etwa,fall,falls,fand,fast,ferner,finden,findest,findet,folgende,folgenden,folgender,folgendes,folglich,fordern,fordert,forderte,forderten,fortsetzen,fortsetzt,fortsetzte,fortsetzten,fragte,frau,frei,freie,freier,freies,fuer,fünf,gab,ganzem,gar,gbr,geb,geben,geblieben,gebracht,gedurft,geehrt,geehrte,geehrten,geehrter,gefallen,gefiel,gefälligst,gefällt,gegeben,gehabt,gehen,geht,gekommen,gekonnt,gemocht,gemäss,genommen,genug,gern,gestern,gestrige,getan,geteilt,geteilte,getragen,gewissermaßen,geworden,ggf,gib,gibt,gleich,gleichwohl,gleichzeitig,glücklicherweise,gmbh,gratulieren,gratuliert,gratulierte,gute,guten,gängig,gängige,gängigen,gängiger,gängiges,gänzlich,haette,halb,hallo,hast,hattest,hattet,heraus,herein,heute,heutige,hiermit,hiesige,hinein,hinten,hinterher,hoch,hundert,hätt,hätte,hätten,höchstens,igitt,immer,immerhin,important,indessen,info,infolge,innen,innerhalb,insofern,inzwischen,irgend,irgendeine,irgendwas,irgendwen,irgendwer,irgendwie,irgendwo,je,jedenfalls,jederlei,jedoch,jemand,jenseits,jährig,jährige,jährigen,jähriges,kam,kannst,kaum,keines,keinerlei,keineswegs,klar,klare,klaren,klares,klein,kleinen,kleiner,kleines,koennen,koennt,koennte,koennten,komme,kommen,kommt,konkret,konkrete,konkreten,konkreter,konkretes,konnten,könn,könnt,könnten,künftig,lag,lagen,langsam,lassen,laut,lediglich,leer,legen,legte,legten,leicht,leider,lesen,letze,letzten,letztendlich,letztens,letztes,letztlich,lichten,liegt,liest,links,längst,längstens,mag,magst,mal,mancherorts,manchmal,mann,margin,mehr,mehrere,meist,meiste,meisten,meta,mindestens,mithin,mochte,morgen,morgige,muessen,muesst,musst,mussten,muß,mußt,möchte,möchten,möchtest,mögen,möglich,mögliche,möglichen,möglicher,möglicherweise,müssen,müsste,müssten,müßte,nachdem,nacher,nachhinein,nahm,natürlich,nacht,neben,nebenan,nehmen,nein,neu,neue,neuem,neuen,neuer,neues,neun,nie,niemals,niemand,nimm,nimmer,nimmt,nirgends,nirgendwo,nutzen,nutzt,nutzung,nächste,nämlich,nötigenfalls,nützt,oben,oberhalb,obgleich,obschon,obwohl,oft,per,pfui,plötzlich,pro,reagiere,reagieren,reagiert,reagierte,rechts,regelmäßig,rief,rund,sang,sangen,schlechter,schließlich,schnell,schon,schreibe,schreiben,schreibens,schreiber,schwierig,schätzen,schätzt,schätzte,schätzten,sechs,sect,sehrwohl,sei,seit,seitdem,seite,seiten,seither,selber,senke,senken,senkt,senkte,senkten,setzen,setzt,setzte,setzten,sicherlich,sieben,siebte,siehe,sieht,singen,singt,sobald,sodaß,soeben,sofern,sofort,sog,sogar,solange,solchen,solch,sollen,sollst,sollt,sollten,solltest,somit,sonstwo,sooft,soviel,soweit,sowie,sowohl,spielen,später,startet,startete,starteten,statt,stattdessen,steht,steige,steigen,steigt,stets,stieg,stiegen,such,suchen,sämtliche,tages,tat,tatsächlich,tatsächlichen,tatsächlicher,tatsächliches,tausend,teile,teilen,teilte,teilten,titel,total,trage,tragen,trotzdem,trug,trägt,tun,tust,tut,txt,tät,ueber,umso,unbedingt,ungefähr,unmöglich,unmögliche,unmöglichen,unmöglicher,unnötig,unsem,unser,unsere,unserem,unseren,unserer,unseres,unten,unterbrach,unterbrechen,unterhalb,unwichtig,usw,vergangen,vergangene,vergangener,vergangenes,vermag,vermutlich,vermögen,verrate,verraten,verriet,verrieten,version,versorge,versorgen,versorgt,versorgte,versorgten,versorgtes,veröffentlichen,veröffentlicher,veröffentlicht,veröffentlichte,veröffentlichten,veröffentlichtes,viele,vielen,vieler,vieles,vielleicht,vielmals,vier,vollständig,voran,vorbei,vorgestern,vorher,vorne,vorüber,völlig,während,wachen,waere,warum,weder,wegen,weitere,weiterem,weiteren,weiterer,weiteres,weiterhin,weiß,wem,wen,wenig,wenige,weniger,wenigstens,wenngleich,wer,werdet,weshalb,wessen,wichtig,wieso,wieviel,wiewohl,willst,wirklich,wodurch,wogegen,woher,wohin,wohingegen,wohl,wohlweislich,womit,woraufhin,woraus,worin,wurde,wurden,währenddessen,wär,wäre,wären,zahlreich,zehn,zeitweise,ziehen,zieht,zog,zogen,zudem,zuerst,zufolge,zugleich,zuletzt,zumal,zurück,zusammen,zuviel,zwanzig,zwei,zwölf,ähnlich,übel,überall,überallhin,überdies,übermorgen,übrig,übrigens\r\n\r\n#english:\r\nable,about,above,abroad,according,accordingly,across,actually,adj,after,afterwards,again,against,ago,ahead,ain\'t,all,allow,allows,almost,alone,along,alongside,already,also,although,always,am,amid,amidst,among,amongst,an,and,another,any,anybody,anyhow,anyone,anything,anyway,anyways,anywhere,apart,appear,appreciate,appropriate,are,aren\'t,around,as,a\'s,aside,ask,asking,associated,at,available,away,awfully,back,backward,backwards,be,became,because,become,becomes,becoming,been,before,beforehand,begin,behind,being,believe,below,beside,besides,best,better,between,beyond,both,brief,but,by,came,can,cannot,cant,can\'t,caption,cause,causes,certain,certainly,changes,clearly,c\'mon,co,co.,com,come,comes,concerning,consequently,consider,considering,contain,containing,contains,corresponding,could,couldn\'t,course,c\'s,currently,dare,daren\'t,definitely,described,despite,did,didn\'t,different,directly,do,does,doesn\'t,doing,done,don\'t,down,downwards,during,each,edu,eg,eight,eighty,either,else,elsewhere,end,ending,enough,entirely,especially,et,etc,even,ever,evermore,every,everybody,everyone,everything,everywhere,ex,exactly,example,except,fairly,far,farther,few,fewer,fifth,first,five,followed,following,follows,for,forever,former,formerly,forth,forward,found,four,from,further,furthermore,get,gets,getting,given,gives,go,goes,going,gone,got,gotten,greetings,had,hadn\'t,half,happens,hardly,has,hasn\'t,have,haven\'t,having,he,he\'d,he\'ll,hello,help,hence,her,here,hereafter,hereby,herein,here\'s,hereupon,hers,herself,he\'s,hi,him,himself,his,hither,hopefully,how,howbeit,however,hundred,i\'d,ie,if,ignored,i\'ll,i\'m,immediate,in,inasmuch,inc,inc.,indeed,indicate,indicated,indicates,inner,inside,insofar,instead,into,inward,is,isn\'t,it,it\'d,it\'ll,its,it\'s,itself,i\'ve,just,k,keep,keeps,kept,know,known,knows,last,lately,later,latter,latterly,least,less,lest,let,let\'s,like,liked,likely,likewise,little,look,looking,looks,low,lower,ltd,made,mainly,make,makes,many,may,maybe,mayn\'t,me,mean,meantime,meanwhile,merely,might,mightn\'t,mine,minus,miss,more,moreover,most,mostly,mr,mrs,much,must,mustn\'t,my,myself,name,namely,nd,near,nearly,necessary,need,needn\'t,needs,neither,never,neverf,neverless,nevertheless,new,next,nine,ninety,no,nobody,non,none,nonetheless,noone,no-one,nor,normally,not,nothing,notwithstanding,novel,now,nowhere,obviously,of,off,often,oh,ok,okay,old,on,once,one,ones,one\'s,only,onto,opposite,or,other,others,otherwise,ought,oughtn\'t,our,ours,ourselves,out,outside,over,overall,own,particular,particularly,past,per,perhaps,placed,please,plus,possible,presumably,probably,provided,provides,que,quite,qv,rather,rd,re,really,reasonably,recent,recently,regarding,regardless,regards,relatively,respectively,right,round,said,same,saw,say,saying,says,second,secondly,see,seeing,seem,seemed,seeming,seems,seen,self,selves,sensible,sent,serious,seriously,seven,several,shall,shan\'t,she,she\'d,she\'ll,she\'s,should,shouldn\'t,since,six,so,some,somebody,someday,somehow,someone,something,sometime,sometimes,somewhat,somewhere,soon,sorry,specified,specify,specifying,still,sub,such,sup,sure,take,taken,taking,tell,tends,th,than,thank,thanks,thanx,that,that\'ll,thats,that\'s,that\'ve,the,their,theirs,them,themselves,then,thence,there,thereafter,thereby,there\'d,therefore,therein,there\'ll,there\'re,theres,there\'s,thereupon,there\'ve,these,they,they\'d,they\'ll,they\'re,they\'ve,thing,things,think,third,thirty,this,thorough,thoroughly,those,though,three,through,throughout,thru,thus,till,to,together,too,took,toward,towards,tried,tries,truly,try,trying,t\'s,twice,two,un,under,underneath,undoing,unfortunately,unless,unlike,unlikely,until,unto,up,upon,upwards,us,use,used,useful,uses,using,usually,v,value,various,versus,very,via,viz,vs,want,wants,was,wasn\'t,way,we,we\'d,welcome,well,we\'ll,went,were,we\'re,weren\'t,we\'ve,what,whatever,what\'ll,what\'s,what\'ve,when,whence,whenever,where,whereafter,whereas,whereby,wherein,where\'s,whereupon,wherever,whether,which,whichever,while,whilst,whither,who,who\'d,whoever,whole,who\'ll,whom,whomever,who\'s,whose,why,will,willing,wish,with,within,without,wonder,won\'t,would,wouldn\'t,yes,yet,you,you\'d,you\'ll,your,you\'re,yours,yourself,yourselves,you\'ve,zero', '16', '16', NULL, NOW(), NULL, 'xtc_cfg_textarea(');";
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
      $value = encode_utf8($value);
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