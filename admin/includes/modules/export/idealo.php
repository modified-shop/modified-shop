<?php
/*
 * export module for php version 5.x
 */

/* -----------------------------------------------------------------------------------------
   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(cod.php,v 1.28 2003/02/14); www.oscommerce.com
   (c) 2003 nextcommerce (invoice.php,v 1.6 2003/08/24); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com
   (c) 2009 idealo, provided as is, no warranty

   Extended by
   - Jens-Uwe Rumstich (Idealo Internet GmbH, http://www.idealo.de)
   - Andreas Geisler (Idealo Internet GmbH, http://www.idealo.de)
   - Christoph Zurek (Idealo Internet GmbH, http://www.idealo.de)

   Please note that this extension is provided as is and without any warranty. It is recommended to always backup your installation prior to use. Use at your own risk.

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

//Version of module
$version_number_idealo = '2.6.0';
$version_date = '02.08.2012';
$idealo_module_modified = 'no';

define( 'TEXT_IDEALO_CSV_MODIFIED', $idealo_module_modified );

define( 'VERSION_TEXT_01', 'Idealo - csv Exportmodul V ' );
define( 'VERSION_TEXT_02', $version_number_idealo );
define( 'VERSION_TEXT_03', ' fuer xt-Systeme vom ' );
define( 'VERSION_TEXT_04', $version_date );
define( 'TEXT_NEW_IDEALO_MODULE_01', 'Die Version ' );
define( 'TEXT_NEW_IDEALO_MODULE_02', ' des Moduls ist auf Idealo verf&uuml;gbar.' );
define( 'TEXT_IDEALO_CSV_TEAM', '<br>Da das installierte Modul f&uuml;r Ihr Shopsystem modifiziert wurde, wenden Sie sich f&uuml;r ein Update bitte an <a href="mailto:csv@idealo.de">csv@idealo.de.</a>');

//find out if new version exists
$version_location_idealo = 'http://ftp.idealo.de/software/modules/version.xml';
$new_idealo_version_text = '';

 if( @file_get_contents ( $version_location_idealo ) !== false ) {
  $xml_idealo = simplexml_load_file ( $version_location_idealo );
  $version_idealo = ( string ) $xml_idealo->csv_export->xt_systeme;

  $idealo_module_download = ( string )$xml_idealo->download->url;

  $old_version_idealo = explode ( '.', $version_number_idealo );
  $new_version_idealo = explode ( '.', $version_idealo );

  $idealo_version_text_modified = TEXT_NEW_IDEALO_MODULE_01 . $version_idealo . TEXT_NEW_IDEALO_MODULE_02 . ' ' . TEXT_IDEALO_CSV_TEAM;
  $idealo_version_text_no_modified = TEXT_NEW_IDEALO_MODULE_01 . $version_idealo .TEXT_NEW_IDEALO_MODULE_02 . ' <a href="' . $idealo_module_download . '" target="_newtab"><b>zur Download-Seite</b></a>'; //DokuMan - 2012-08-21 - removed "blink"-tag

    if ( count ( $old_version_idealo ) == count ( $new_version_idealo ) ){
        if (
               ( $old_version_idealo [0] < $new_version_idealo [0] )
               or
               (
                 $old_version_idealo [0] == $new_version_idealo [0]
                 and
                 $old_version_idealo[1] < $new_version_idealo [1]
               )
               or
               (
                 $old_version_idealo[0] == $new_version_idealo[0]
                 and
                 $old_version_idealo[1] == $new_version_idealo[1]
                 and
                 $old_version_idealo[2] < $new_version_idealo[2]
               )
           ){
          if ( TEXT_IDEALO_CSV_MODIFIED == 'no' ){
            $new_idealo_version_text = $idealo_version_text_no_modified;
          }else{
            $new_idealo_version_text = $idealo_version_text_modified;
          }
        }
      }
   }

define( 'NEW_IDEALO_VERSION_TEXT', $new_idealo_version_text );

// module display config
define('MODULE_IDEALO_TEXT_DESCRIPTION', 'Export - Idealo');
define('MODULE_IDEALO_TEXT_TITLE', '<img src = "http://www.idealo.de/pics/common/logoidealo_blue_s.gif"> - CSV');
define('MODULE_IDEALO_FILE_TITLE' , '<hr noshade>Dateiname');
define('MODULE_IDEALO_FILE_DESC' , 'Geben Sie einen Dateinamen ein, falls die Exportadatei am Server gespeichert werden soll.<br>(Verzeichnis export/)');
define('FIELDSEPARATOR', '<b>Spaltentrenner</b>');
define('FIELDSEPARATOR_HINT_IDEALO', 'Beispiel:<br>,&nbsp;&nbsp;&nbsp;(Komma)<br>|&nbsp;&nbsp;(Pipe)<br>...');
define('QUOTING','<b>Quoting</b>');
define('QUOTING_HINT','Beispiel:<br>"&nbsp;&nbsp;&nbsp;(Anf&uuml;hrungszeichen)<br>\'&nbsp;&nbsp;&nbsp;(Hochkomma)<br>#&nbsp;&nbsp;(Raute)<br>... <br>Wird das Feld leer gelassen, wird nicht gequotet.');
define('CODEXTRAFEE', '<b>Nachnahme</b>');
define('CODEXTRAFEE_HINT', 'Tragen Sie die Geb&uuml;hren f&uuml;r Nachnahme ein, inkl. zus&auml;tzlich vom Zusteller f&uuml;r Nachnahme verlangter Zustellgeb&uuml;hr.');
define('CODEEXTRAFEE_BSP', 'Beispiel: "4.95"');

define('PAYPALEXTRAFEE', '<b>PayPal</b>');
define('PAYPALEXTRAFEE_HINT', 'Die Geb&uuml;hren die zus&auml;tzlich zu den normalen Versandkosten anfallen.');
define('PAYPALEXTRAFEE_INPUT_FIX', 'EUR fixe Geb&uuml;hren (Bsp.: 5.00 oder 3 ...)');
define('PAYPALEXTRAFEE_INPUT_NOFIX', '% vom Warenwert (Bsp.:3.5 oder 1 ...)');
define('PAYPALEXTRAFEE_RADIO_SCINCLUSIVE', '<b>inkl.</b> VK');
define('PAYPALEXTRAFEE_RADIO_SCNOTINCLUSIVE', '<b>exkl.</b> VK');
define('PAYPAL_MAXPRICELIMIT', '<b>Oberste Preisgrenze f&uuml;r PayPal</b>');
define('PAYPAL_MAXPRICEVALUE', 'Der max. Warenwert bis zu dem das Bezahlen mit PayPal m&ouml;glich ist.');
define('PAYPAL_MAXPRICEEXAMPLE', ' EUR (Beispiel: "500" oder "99.99" ...)');

define('SOFORTUEBERWEISUNGEXTRAFEE', '<b>Sofort&uuml;berweisung</b>');
define('SOFORTUEBERWEISUNGEXTRAFEE_HINT', 'Die Geb&uuml;hren die zus&auml;tzlich zu den normalen Versandkosten anfallen.');
define('SOFORTUEBERWEISUNGEXTRAFEE_INPUT_FIX', 'EUR fixe Geb&uuml;hren (Bsp.: 5.00 oder 3 ...)');
define('SOFORTUEBERWEISUNGEXTRAFEE_INPUT_NOFIX', '% vom Warenwert (Bsp.:3.5 oder 1 ...)');
define('SOFORTUEBERWEISUNGEXTRAFEE_RADIO_SCINCLUSIVE', '<b>inkl.</b> VK');
define('SOFORTUEBERWEISUNGEXTRAFEE_RADIO_SCNOTINCLUSIVE', '<b>exkl.</b> VK');
define('SOFORTUEBERWEISUNG_MAXPRICELIMIT', '<b>Oberste Preisgrenze f&uuml;r Sofort&uuml;berweisung</b>');
define('SOFORTUEBERWEISUNG_MAXPRICEVALUE', 'Der max. Warenwert bis zu dem das Bezahlen mit Sofort&uuml;berweisung m&ouml;glich ist.');
define('SOFORTUEBERWEISUNG_MAXPRICEEXAMPLE', ' EUR (Beispiel: "500" oder "99.99" ...)');

define('CCEXTRAFEE', '<b>Kreditkarte</b>');
define('CCEXTRAFEE_HINT', 'Die Geb&uuml;hren die zus&auml;tzlich zu den normalen Versandkosten anfallen.');
define('CCEXTRAFEE_INPUT_FIX', 'EUR fixe Geb&uuml;hren (Bsp.: 5.00 oder 3 ...)');
define('CCEXTRAFEE_INPUT_NOFIX', '% vom Warenwert <b>inkl.</b> VK (Bsp.:3.5 oder 1 ...)');
define('CCEXTRAFEE_RADIO_SCINCLUSIVE', '<b>inkl.</b> VK');
define('CCEXTRAFEE_RADIO_SCNOTINCLUSIVE', '<b>exkl.</b> VK');
define('CC_MAXPRICELIMIT', '<b>Oberste Preisgrenze f&uuml;r Kreditkarte</b>');
define('CC_MAXPRICEVALUE', 'Der max. Warenwert bis zu dem das Bezahlen mit Kreditkarte m&ouml;glich ist.');
define('CC_MAXPRICEEXAMPLE', ' EUR (Beispiel: "500" oder "99.99" ...)');

define('SHIPPINGCOMMENT', '<b>Versandkommentar</b>');
define('SHIPPINGCOMMENT_HINT', 'Max. 100 Zeichen');
define('FREESHIPPINGCOMMENT', '<b>Kommentar zur Versankosten-Grenze</b>');
define('FREESHIPPINGCOMMENT_HINT', 'Wird bei allen Angeboten angezeigt, die unter der Versandkostenfreiheits-Grenze liegen.<br>Max. 100 Zeichen');
define('LANGUAGE', '<b>Export f&uuml;r</b>');
define('LANGUAGE_HINT', 'Beispiel:<br>DE (Deutschland)<br>AT (&Ouml;sterreich)<br>...<br>Es sollten(!) die Sprachen genutzt werden, die auch bei den Versandkosten etc. korrekt hinterlegt sind.<br>Wird das Feld leer gelassen, wird \'DE\' benutzt.');
define('MODULE_IDEALO_STATUS_DESC','Modulstatus');
define('MODULE_IDEALO_STATUS_TITLE','Status');
define('MODULE_IDEALO_CURRENCY_TITLE','W&auml;hrung');
define('MODULE_IDEALO_CURRENCY_DESC','Welche W&auml;hrung soll exportiert werden?');
define('EXPORT_YES','Nur Herunterladen');
define('EXPORT_NO','Am Server Speichern');
define('CURRENCY','EUR');
define('SHIPPING','<hr noshade><b>Versandart / Versandkosten</b>');
define('SHIPPING_DESC','W&auml;hlen Sie bitte aus, welche Versandkosten exportiert werden sollen');
define('SHIPPING_ALLOWED', 'Es werden die folgenden Versandarten vom Modul unterst&uuml;tzt:' .
    '<li>FREEAMOUNT: Kostenfreier Versand (wird automatisch &uuml;berpr&uuml;ft)' .
    '<li>FLAT: Pauschale Versandkosten' .
    '<li>ITEM: Versandkosten pro St&uuml;ck'.
    '<li>DP: Deutsche Post' .
    '<li>TABLE: Die Versandkosten werden gewichtsabh&auml;ngig berechnet');
define('SHIPPING_NOT_ALLOWED', 'Bitte die gew&uuml;nschte Versandart ausw&auml;hlen. Bei der Versandart "feste Versandkosten" werden die im Modul eingestellten Versandkosten exportiert.');

define('CSV_TYPE', '<hr noshade><b>CSV-Art:</b>');
define('CSV_TEXT', 'Die CSV-Datei kann statisch erzeugt und unter einem Link abgelegt werden oder dynamisch bei jeder Anfrage aktuallisiert erzeugt werden.');
define('EXPORT','Bitte den Sicherungsprozess AUF KEINEN FALL unterbrechen. Dieser kann einige Minuten in Anspruch nehmen.');
define('EXPORT_TYPE','<hr noshade><b>Speicherart:</b>');
define('CAMPAIGNS','<hr noshade><b>Kampagnen:</b>');
define('CAMPAIGNS_DESC','Mit Kampagne zur Nachverfolgung verbinden.');
define('DATE_FORMAT_EXPORT', '%d.%m.%Y');  // this is used for strftime()
define('DISPLAY_PRICE_WITH_TAX','true');
define('COMMENTLENGTH', 100);
define('DYNAMIC_TYPE', '<hr noshade><b>Livedatei:</b>');
define('DYNAMIC', 'Waehlen Sie bitte aus, wie die Datei erzeugt werden soll');
define('DYNAMIC_YES', 'Datei beim Update live erzeugen');
define('DYNAMIC_NO', 'Datei per Hand erzeugen');
define('LINK_TO_DYNAMIC_MODULE', '   Link zum dyn. Modul');
define('PATH', '/export/idealo/idealo_dynamic.php'); // subpath to the livemodule
define('MODULE_NOT_FOUND', '<hr noshade><b>Modul "idealo_dynamic.php" nicht vorhanden!</b>');
define('COSTUMER_STATUS', '1'); // consumer stat 1 = Gast.
define('PACK_TEXT', '<hr noshade><b>Datei komprimieren?</b>');
define('DELIVERYTEXT', '<hr noshade><b>feste Versandkosten</b>');
define('DILEVERYCOSTS', 'F&uuml;gen Sie bitte Ihre Versandkosten ein');
define('DILEVERYCOSTS_TYPE', 'w&auml;hlen Sie die Berechnungsart aus');
define('DELIVERYBSP', ' (Bsp.: 5.00 oder 3 ...)');
define('DELIVERYFREETEXT', '<b>Versandkostenfrei</b>');
define('DELIVERYFREEBSP', ' (Bsp. 50.00 oder 60 ...)');
define('DELIVERYFREE', 'Kostenfreie Lieferung ab einen Warenwert von:');
define('RUN_TEXT', '*Idealo uebernimmt keine Haftung für den einwandfreien Betrieb, die Funktionalität des Moduls, der Sicherheit der übertragenen Daten und Haftung fuer etwaige Schaeden. Idealo kann den Service der Module jederzeit einstellen. Mit der Nutzung der Module stimmt der Kooperationspartner dem vorgenannten Haftungsausschluss von Idealo zu.');

// header
define('ARTICLE_ID','artikelId');
define('BRAND','hersteller');
define('PRODUCT_NAME','bezeichnung');
define('CATEGORIE','kategorie');
define('DESCRIPTION_SHORT','beschreibung_kurz');
define('DESCRIPTION_SHORT_LONG','beschreibung_lang');
define('IMAGE','bild');
define('DEEPLINK','deeplink');
define('PRICE','preis');
define('EAN','ean');
define('DELIVERY','lieferzeit');
define('ARTICLE_SHOP_NUMBER', 'Artikelnummer im Shop');

// check if shipping type is already in db
$module_shipping_type_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_MODULE_SHIPPING_TYPE' LIMIT 1");
$module_shipping_type_db = xtc_db_fetch_array($module_shipping_type_query); // false if 'MODULE_IDEALO_MODULE_SHIPPING_TYPE' doesn't exist

// check if campaign is already in db
$campaign_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_CAMPAIGN' LIMIT 1");
$campaign_db = xtc_db_fetch_array($campaign_query); // false if 'MODULE_IDEALO_CAMPAIGN' doesn't exist

// check if catfilter is already in db
$cat_filter_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_CAT_FILTER' LIMIT 1");
$cat_filter_db = xtc_db_fetch_array($cat_filter_query); // false if 'MODULE_IDEALO_CAT_FILTER' doesn't exist

// check if catfilter is already in db
$cat_filter_value_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_CAT_FILTER_VALUE' LIMIT 1");
$cat_filter_value_db = xtc_db_fetch_array($cat_filter_value_query); // false if 'MODULE_IDEALO_CAT_FILTER' doesn't exist

// check if Shipping is already in db
$shipping_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_SHIPPING' LIMIT 1");
$shipping_db = xtc_db_fetch_array($shipping_query); // false if 'MODULE_IDEALO_SHIPPING' doesn't exist


// check if cod active is already in db
$cod_active_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_COD_ACTIVE' LIMIT 1");
$cod_active_db = xtc_db_fetch_array($cod_active_query); // false if 'MODULE_IDEALO_COD_ACTIVE' doesn't exist


// check if brandfilter is already in db
$brand_filter_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_BRAND_FILTER' LIMIT 1");
$brand_filter_db = xtc_db_fetch_array($brand_filter_query); // false if 'MODULE_IDEALO_BRAND_FILTER' doesn't exist

// check if brandfilter is already in db
$brand_filter_value_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_BRAND_FILTER_VALUE' LIMIT 1");
$brand_filter_value_db = xtc_db_fetch_array($brand_filter_value_query); // false if 'MODULE_IDEALO_BRAND_FILTER' doesn't exist


// check if articlefilter is already in db
$article_filter_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_ARTICLE_FILTER' LIMIT 1");
$article_filter_db = xtc_db_fetch_array($article_filter_query); // false if 'MODULE_IDEALO_ARTICLE_FILTER' doesn't exist

// check if articlefilter is already in db
$article_filter_value_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_ARTICLE_FILTER_VALUE' LIMIT 1");
$article_filter_value_db = xtc_db_fetch_array($article_filter_value_query); // false if 'MODULE_IDEALO_ARTICLE_FILTER' doesn't exist

// check if sofortueberweisung active is already in db
$sofortueberweisung_active_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_SOFORTUEBERWEISUNG_ACTIVE' LIMIT 1");
$sofortueberweisung_active_db = xtc_db_fetch_array($sofortueberweisung_active_query); // false if 'MODULE_IDEALO_SOFORTUEBERWEISUNG_ACTIVE' doesn't exist

// check if sofortueberweisung fix is already in db
$sofortueberweisungextrafee_input_fix_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_SOFORTUEBERWEISUNGEXTRAFEE_FIX' LIMIT 1");
$sofortueberweisungextrafee_input_fix_db = xtc_db_fetch_array($sofortueberweisungextrafee_input_fix_query); // false if 'MODULE_IDEALO_SOFORTUEBERWEISUNGEXTRAFEE_FIX' doesn't exist

// check if sofortueberweisung nofix is already in db
$sofortueberweisungextrafee_input_nofix_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_SOFORTUEBERWEISUNGEXTRAFEE_NOFIX' LIMIT 1");
$sofortueberweisungextrafee_input_nofix_db = xtc_db_fetch_array($sofortueberweisungextrafee_input_nofix_query); // false if 'MODULE_IDEALO_SOFORTUEBERWEISUNGEXTRAFEE_NOFIX' doesn't exist

// check if sofortueberweisungextrafee_nofix_scinclusive is already in db
$sofortueberweisungextrafee_input_nofix_scinclusive_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_SOFORTUEBERWEISUNGEXTRAFEE_NOFIX_SCINCLUSIVE' LIMIT 1");
$sofortueberweisungextrafee_input_nofix_scinclusive_db = xtc_db_fetch_array($sofortueberweisungextrafee_input_nofix_scinclusive_query); // false if 'MODULE_IDEALO_SOFORTUEBERWEISUNGEXTRAFEE_NOFIX_SCINCLUSIVE' doesn't exist

// check if cc active is already in db
$cc_active_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_CC_ACTIVE' LIMIT 1");
$cc_active_db = xtc_db_fetch_array($cc_active_query); // false if 'MODULE_IDEALO_CC_ACTIVE' doesn't exist

// check if paypal active is already in db
$paypal_active_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_PAYPAL_ACTIVE' LIMIT 1");
$paypal_active_db = xtc_db_fetch_array($paypal_active_query); // false if 'MODULE_IDEALO_PAYPAL_ACTIVE' doesn't exist


//chek if $deliveryfree is already in db;
$deliveryfree_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_DELIVERYFREE' LIMIT 1");
$deliveryfree_db = xtc_db_fetch_array($deliveryfree_query); // false if 'MODULE_IDEALO_DELIVERYCOSTS' doesn't exist

// check if deliverycosts is already in db
$deliverycosts_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_DELIVERYCOSTS' LIMIT 1");
$deliverysosts_db = xtc_db_fetch_array($deliverycosts_query); // false if 'MODULE_IDEALO_DELIVERYCOSTS' doesn't exist

// check if separator is already in db
$separator_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_SEPARATOR' LIMIT 1");
$separator_db = xtc_db_fetch_array($separator_query); // false if 'MODULE_IDEALO_SEPARATOR' doesn't exist

// check if a quoting character is already in db
$quoting_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_QUOTING' LIMIT 1");
$quoting_db = xtc_db_fetch_array($quoting_query); // false if 'MODULE_IDEALO_QUOTING doesn't exist

// check if a quoting character is already in db
$language_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_LANGUAGE' LIMIT 1");
$language_db = xtc_db_fetch_array($language_query); // false if 'MODULE_IDEALO_LANGUAGE doesn't exist

// check if codextrafee is already in db
$codextrafee_input_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_CODEXTRAFEE' LIMIT 1");
$codextrafee_db = xtc_db_fetch_array($codextrafee_input_query); // false if 'MODULE_IDEALO_CODEXTRAFEE' doesn't exist

// check if paypalextrafee_fix is already in db
$paypalextrafee_input_fix_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_PAYPALEXTRAFEE_FIX' LIMIT 1");
$paypalextrafee_input_fix_db = xtc_db_fetch_array($paypalextrafee_input_fix_query); // false if 'MODULE_IDEALO_PAYPALEXTRAFEE_FIX' doesn't exist

// check if paypalextrafee_nofix is already in db
$paypalextrafee_input_nofix_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_PAYPALEXTRAFEE_NOFIX' LIMIT 1");
$paypalextrafee_input_nofix_db = xtc_db_fetch_array($paypalextrafee_input_nofix_query); // false if 'MODULE_IDEALO_PAYPALEXTRAFEE_NOFIX' doesn't exist

// check if paypalextrafee_nofix_scinclusive is already in db
$paypalextrafee_input_nofix_scinclusive_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_PAYPALEXTRAFEE_NOFIX_SCINCLUSIVE' LIMIT 1");
$paypalextrafee_input_nofix_scinclusive_db = xtc_db_fetch_array($paypalextrafee_input_nofix_scinclusive_query); // false if 'MODULE_IDEALO_PAYPALEXTRAFEE_NOFIX_SCINCLUSIVE' doesn't exist

// check if paypalmaxpricelimit is already in db
$paypalmaxpricelimit_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_PAYPALMAXPRICELIMIT' LIMIT 1");
$paypalmaxpricelimit_db = xtc_db_fetch_array($paypalmaxpricelimit_query); // false if 'MODULE_IDEALO_PAYPALMAXPRICELIMIT' doesn't exist

// check if ccextrafee_fix is already in db
$ccextrafee_input_fix_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_CCEXTRAFEE_FIX' LIMIT 1");
$ccextrafee_input_fix_db = xtc_db_fetch_array($ccextrafee_input_fix_query); // false if 'MODULE_IDEALO_CCEXTRAFEE_FIX' doesn't exist

// check if ccextrafee_nofix is already in db
$ccextrafee_input_nofix_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_CCEXTRAFEE_NOFIX' LIMIT 1");
$ccextrafee_input_nofix_db = xtc_db_fetch_array($ccextrafee_input_nofix_query); // false if 'MODULE_IDEALO_CCEXTRAFEE_NOFIX' doesn't exist

// check if ccextrafee_nofix_scinclusive is already in db
$ccextrafee_input_nofix_scinclusive_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_CCEXTRAFEE_NOFIX_SCINCLUSIVE' LIMIT 1");
$ccextrafee_input_nofix_scinclusive_db = xtc_db_fetch_array($ccextrafee_input_nofix_scinclusive_query); // false if 'MODULE_IDEALO_CCEXTRAFEE_NOFIX_SCINCLUSIVE' doesn't exist

// check if ccmaxpricelimit is already in db
$ccmaxpricelimit_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_CCMAXPRICELIMIT' LIMIT 1");
$ccmaxpricelimit_db = xtc_db_fetch_array($ccmaxpricelimit_query); // false if 'MODULE_IDEALO_CCMAXPRICELIMIT' doesn't exist

// check if shippinglimit_input is already in db
$shipping_input_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_SHIPPINGCOMMENT' LIMIT 1");
$shipping_comment_db = xtc_db_fetch_array($shipping_input_query); // false if 'MODULE_IDEALO_SHIPPINGCOMMENT' doesn't exist

// check if freeshippinglimit_input is already in db
$freeshipping_input_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_FREESHIPPINGCOMMENT' LIMIT 1");
$freeshipping_comment_db = xtc_db_fetch_array($freeshipping_input_query); // false if 'MODULE_IDEALO_FREESHIPPINGCOMMENT' doesn't exist

// check if livedata module is already in db
$h_string = 'select configuration_value from `'. TABLE_CONFIGURATION . '` where `configuration_key` = \'MODULE_IDEALO_LIVEDATA_MODULE\' LIMIT 1';
$livedata_query = xtc_db_query($h_string);
$livedata_db = xtc_db_fetch_array($livedata_query);

// check if livedata setting is already in db
$h_string = 'select configuration_value from `'. TABLE_CONFIGURATION . '` where `configuration_key` = \'MODULE_IDEALO_LIVEDATA_SETTING\' LIMIT 1';
$livedata_setting_query = xtc_db_query($h_string);
$livedata_setting_db = xtc_db_fetch_array($livedata_setting_query);

// check if zip setting is already in db
$h_string = 'select `configuration_value` from `'. TABLE_CONFIGURATION . '` where `configuration_key` = \'IDEALO_ZIP_SETTING\' LIMIT 1';
$zip_setting_query = xtc_db_query($h_string);
$zip_setting_db = xtc_db_fetch_array($zip_setting_query );


/*
 * module_shipping type
 */

// is module_shipping_type set?
if( isset($_POST['module_shipping_type'])) {
  // does a dataset exist?
  if( $module_shipping_type_db !== false ) {
    // update value if $_POST['shipping_type'] != $shipping_type_db
    if( $_POST['module_shipping_type'] != $shipping_type_db['module_configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $_POST['module_shipping_type'] . "'
                where configuration_key = 'MODULE_IDEALO_MODULE_SHIPPING_TYPE'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_MODULE_SHIPPING_TYPE', '" . $_POST['module_shipping_type'] . "', 6, 1, '', now()) ");
  }

  $module_shipping_type = stripcslashes($_POST['module_shipping_type']);
} else {
  $module_shipping_type = "";
}

/*
 * campaign
 */

// is campaign set?
if( isset($_POST['campaign'])) {
  // does a dataset exist?
  if( $campaign_db !== false ) {
    // update value if $_POST['campaign'] != $campaign_db
    if( $_POST['campaign'] != $campaign_db['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $_POST['campaign'] . "'
                where configuration_key = 'MODULE_IDEALO_CAMPAIGN'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_CAMPAIGN', '" . $_POST['campaign'] . "', 6, 1, '', now()) ");
  }

  $campaign = stripcslashes($_POST['campaign']);
} else {
  $campaign = "";
}



/*
 * cat filter value
 */

// is cat_filter_value set?
if( isset($_POST['cat_filter_value'])) {
  // does a dataset exist?
  if( $cat_filter_value_db !== false ) {
    // update value if $_POST['cat_filter_value'] != $quoting_db
    if( $_POST['cat_filter_value'] != $cat_filter_value_db['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $_POST['cat_filter_value'] . "'
                where configuration_key = 'MODULE_IDEALO_CAT_FILTER_VALUE'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_CAT_FILTER_VALUE', '" . $_POST['cat_filter_value'] . "', 6, 1, '', now()) ");
  }

  $cat_filter_value = stripcslashes($_POST['cat_filter_value']);
} else {
  $cat_filter_value = "";
}


/*
 * cat filter
 */

// is cat_filter set?
if( isset($_POST['cat_filter'])) {
  // does a dataset exist?
  if( $cat_filter_db !== false ) {
    // update value if $_POST['cat_filter'] != $quoting_db
    if( $_POST['cat_filter'] != $cat_filter_db['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $_POST['cat_filter'] . "'
                where configuration_key = 'MODULE_IDEALO_CAT_FILTER'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_CAT_FILTER', '" . $_POST['cat_filter'] . "', 6, 1, '', now()) ");
  }

  $cat_filter = stripcslashes($_POST['cat_filter']);
} else {
  $cat_filter = "";
}


/*
 * SHIPPING
*/
// is a specific shipping set?
if( isset($_POST['shipping'])) {
  // db does not care for extra slashes
  $dbValue = $_POST['shipping'];
  // does a dataset exist?
  if( $shipping_db !== false ) {

    // update value if $_POST['shipping'] != $shipping_db
    if( $_POST['shipping'] != $shipping_db['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $dbValue . "'
                where configuration_key = 'MODULE_IDEALO_SHIPPING'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_SHIPPING', '" . $dbValue . "', 6, 1, '', now()) ");
  }

  $shipping = $_POST['shipping'];

} else {
  // if nothing is entered by '' gets as default
  $shipping = '';
}



/*
 * cod_ACTIVE
 */

// is cod_active set?
if( isset($_POST['cod_active'])) {
  // does a dataset exist?
  if( $cod_active_db !== false ) {
    // update value if $_POST['cod_active'] != $quoting_db
    if( $_POST['cod_active'] != $cod_active_db['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $_POST['cod_active'] . "'
                where configuration_key = 'MODULE_IDEALO_COD_ACTIVE'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_COD_ACTIVE', '" . $_POST['cod_active'] . "', 6, 1, '', now()) ");
  }

  $cod_active = stripcslashes($_POST['cod_active']);
} else {
  $cod_active = "";
}


/*
 * brand filter value
 */

// is brand_filter_value set?
if( isset($_POST['brand_filter_value'])) {
  // does a dataset exist?
  if( $brand_filter_value_db !== false ) {
    // update value if $_POST['brand_filter_value'] != $quoting_db
    if( $_POST['brand_filter_value'] != $brand_filter_value_db['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $_POST['brand_filter_value'] . "'
                where configuration_key = 'MODULE_IDEALO_BRAND_FILTER_VALUE'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_BRAND_FILTER_VALUE', '" . $_POST['brand_filter_value'] . "', 6, 1, '', now()) ");
  }

  $brand_filter_value = stripcslashes($_POST['brand_filter_value']);
} else {
  $brand_filter_value = "";
}


/*
 * brand filter
 */

// is brand_filter set?
if( isset($_POST['brand_filter'])) {
  // does a dataset exist?
  if( $brand_filter_db !== false ) {
    // update value if $_POST['brand_filter'] != $quoting_db
    if( $_POST['brand_filter'] != $brand_filter_db['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $_POST['brand_filter'] . "'
                where configuration_key = 'MODULE_IDEALO_BRAND_FILTER'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_BRAND_FILTER', '" . $_POST['brand_filter'] . "', 6, 1, '', now()) ");
  }

  $brand_filter = stripcslashes($_POST['brand_filter']);
} else {
  $brand_filter = "";
}


/*
 * article filter value
 */

// is article_filter_value set?
if( isset($_POST['article_filter_value'])) {
  // does a dataset exist?
  if( $article_filter_value_db !== false ) {
    // update value if $_POST['article_filter_value'] != $quoting_db
    if( $_POST['article_filter_value'] != $article_filter_value_db['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $_POST['article_filter_value'] . "'
                where configuration_key = 'MODULE_IDEALO_ARTICLE_FILTER_VALUE'");
    }
  } else {

    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_ARTICLE_FILTER_VALUE', '" . $_POST['article_filter_value'] . "', 6, 1, '', now()) ");
  }

  $article_filter_value = stripcslashes($_POST['article_filter_value']);
} else {
  $article_filter_value = "";
}


/*
 * article filter
 */

// is article_filter set?
if( isset($_POST['article_filter'])) {
  // does a dataset exist?
  if( $article_filter_db !== false ) {
    // update value if $_POST['article_filter'] != $quoting_db
    if( $_POST['article_filter'] != $article_filter_db['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $_POST['article_filter'] . "'
                where configuration_key = 'MODULE_IDEALO_ARTICLE_FILTER'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_ARTICLE_FILTER', '" . $_POST['article_filter'] . "', 6, 1, '', now()) ");
  }

  $article_filter = stripcslashes($_POST['article_filter']);
} else {
  $article_filter = "";
}



/*
 * sofortueberweisung_ACTIVE
 */

// is sofortueberweisung_active set?
if( isset($_POST['sofortueberweisung_active'])) {
  // does a dataset exist?
  if( $sofortueberweisung_active_db !== false ) {
    // update value if $_POST['sofortueberweisung_active'] != $quoting_db
    if( $_POST['sofortueberweisung_active'] != $sofortueberweisung_active_db['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $_POST['sofortueberweisung_active'] . "'
                where configuration_key = 'MODULE_IDEALO_SOFORTUEBERWEISUNG_ACTIVE'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_SOFORTUEBERWEISUNG_ACTIVE', '" . $_POST['sofortueberweisung_active'] . "', 6, 1, '', now()) ");
  }

  $sofortueberweisung_active = stripcslashes($_POST['sofortueberweisung_active']);
} else {
  $sofortueberweisung_active = "";
}


/*
 * CC_ACTIVE_FIX
 */

// is cc_active set?
if( isset($_POST['cc_active'])) {
  // does a dataset exist?
  if( $cc_active_db !== false ) {
    // update value if $_POST['cc_active'] != $quoting_db
    if( $_POST['cc_active'] != $cc_active_db['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $_POST['cc_active'] . "'
                where configuration_key = 'MODULE_IDEALO_CC_ACTIVE'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_CC_ACTIVE', '" . $_POST['cc_active'] . "', 6, 1, '', now()) ");
  }

  $cc_active = stripcslashes($_POST['cc_active']);
} else {
  $cc_active = "";
}


/*
 * PAYPAL_ACTIVE_FIX
 */

// is paypal_active set?
if( isset($_POST['paypal_active'])) {
  // does a dataset exist?
  if( $paypal_active_db !== false ) {
    // update value if $_POST['paypal_active'] != $quoting_db
    if( $_POST['paypal_active'] != $paypal_active_db['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $_POST['paypal_active'] . "'
                where configuration_key = 'MODULE_IDEALO_PAYPAL_ACTIVE'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_PAYPAL_ACTIVE', '" . $_POST['paypal_active'] . "', 6, 1, '', now()) ");
  }

  $paypal_active = stripcslashes($_POST['paypal_active']);
} else {
  $paypal_active = "";
}


/*
 * SOFORTUEBERWEISUNGEXTRAFEE_FIX
 */

// is a fix fee for sofortueberweisung set?
if( isset($_POST['sofortueberweisung_extrafee_fix'])) {
  // does a dataset exist?
  if( $sofortueberweisungextrafee_input_fix_db !== false ) {
    // update value if $_POST['sofortueberweisung_extrafee_fix'] != $quoting_db
    if( $_POST['sofortueberweisung_extrafee_fix'] != $sofortueberweisungextrafee_input_fix_db['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $_POST['sofortueberweisung_extrafee_fix'] . "'
                where configuration_key = 'MODULE_IDEALO_SOFORTUEBERWEISUNGEXTRAFEE_FIX'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_SOFORTUEBERWEISUNGEXTRAFEE_FIX', '" . $_POST['sofortueberweisung_extrafee_fix'] . "', 6, 1, '', now()) ");
  }

  $sofortueberweisung_extrafee_fix = stripcslashes($_POST['sofortueberweisung_extrafee_fix']);
} else {
  $sofortueberweisung_extrafee_fix = "";
}

/*
 * SOFORTUEBERWEISUNGEXTRAFEE_NOFIX
 */
// is a fee for sofortueberweisung set that depends on then price+shipping cost?
if( isset($_POST['sofortueberweisung_extrafee_nofix'])) {
  // does a dataset exist?
  if( $sofortueberweisungextrafee_input_nofix_db !== false ) {
    // update value if $_POST['sofortueberweisung_extrafee_nofix'] != $quoting_db
    if( $_POST['sofortueberweisung_extrafee_nofix'] != $sofortueberweisungextrafee_input_nofix_db['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $_POST['sofortueberweisung_extrafee_nofix'] . "'
                where configuration_key = 'MODULE_IDEALO_SOFORTUEBERWEISUNGEXTRAFEE_NOFIX'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_SOFORTUEBERWEISUNGEXTRAFEE_NOFIX', '" . $_POST['sofortueberweisung_extrafee_nofix'] . "', 6, 1, '', now()) ");
  }

  $sofortueberweisung_extrafee_nofix = stripcslashes($_POST['sofortueberweisung_extrafee_nofix']);
} else {
  $sofortueberweisung_extrafee_nofix = "";
}
/*
 * SOFORTUEBERWEISUNGEXTRAFEE_NOFIX_SCINCLUSIVE
 */
// include or exclude shipping cost for variable extra fee
if( isset($_POST['sofortueberweisung_extrafee_nofix_inkl_sc'])) {
  // does a dataset exist?

  if( $sofortueberweisungextrafee_input_nofix_scinclusive_db !== false ) {
    // update value if $_POST['sofortueberweisung_extrafee_nofix'] != $quoting_db
    if( $_POST['sofortueberweisung_extrafee_nofix_inkl_sc'] != $sofortueberweisungextrafee_input_nofix_scinclusive_db['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $_POST['sofortueberweisung_extrafee_nofix_inkl_sc'] . "'
                where configuration_key = 'MODULE_IDEALO_SOFORTUEBERWEISUNGEXTRAFEE_NOFIX_SCINCLUSIVE'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_SOFORTUEBERWEISUNGEXTRAFEE_NOFIX_SCINCLUSIVE', '" . $_POST['sofortueberweisung_extrafee_nofix_inkl_sc'] . "', 6, 1, '', now()) ");
  }

  $sofortueberweisung_extrafee_nofix_scinclusive = stripcslashes($_POST['sofortueberweisung_extrafee_nofix_inkl_sc']);
} else {
  $sofortueberweisung_extrafee_nofix_scinclusive = "";
}

/*
 * SOFORTUEBERWEISUNG_MAXPRICELIMIT
 */
// maximum price at which payment with sofortueberweisung is possible
if( isset($_POST['sofortueberweisung_maxpricelimit'])) {
  // does a dataset exist?
  if( $sofortueberweisungmaxpricelimit_db !== false ) {
    // update value if $_POST['sofortueberweisung_maxpricelimit'] != $quoting_db
    if( $_POST['sofortueberweisung_maxpricelimit'] != $sofortueberweisungmaxpricelimit_db['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $_POST['sofortueberweisung_maxpricelimit'] . "'
                where configuration_key = 'MODULE_IDEALO_SOFORTUEBERWEISUNGMAXPRICELIMIT'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_SOFORTUEBERWEISUNGMAXPRICELIMIT', '" . $_POST['sofortueberweisung_maxpricelimit'] . "', 6, 1, '', now()) ");
  }

  $sofortueberweisung_maxpricelimit = stripcslashes($_POST['sofortueberweisung_maxpricelimit']);
} else {
  $sofortueberweisung_maxpricelimit = "";
}


/*
 * SEPARATOR
 */
// is a specific separator set?
if( isset($_POST['separator_input'])) {
  // db does not care for extra slashes
  $dbValue = $_POST['separator_input'];

  // check if slashes need to be stripped
  if( $_POST['separator_input'] != stripslashes($_POST['separator_input']) ) {
    $_POST['separator_input'] = stripslashes($_POST['separator_input']);
  }

  // hack
  if( $_POST['separator_input'] == '\t' ) {
    $_POST['separator_input'] = "\t";
  }

  // does a dataset exist?
  if( $separator_db !== false ) {

    // update value if $_POST['separator_input'] != $separator_db
    if( $_POST['separator_input'] != $separator_db['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $dbValue . "'
                where configuration_key = 'MODULE_IDEALO_SEPARATOR'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_SEPARATOR', '" . $dbValue . "', 6, 1, '', now()) ");
  }

  $separator = $_POST['separator_input'];

} else {
  // if nothing is entered by the admin: $separator gets | as default
  $separator = "|";
}

/*
 * $deliveryfree_db
 */

// is a specific quoting character set?
if( isset($_POST['dilevery_free'])) {
  // does a dataset exist?
  if( $deliveryfree_db !== false ) {

    // update value if $_POST['quoting_input'] != $quoting_db
    if( $_POST['dilevery_free'] != $deliveryfree_db['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $_POST['dilevery_free'] . "'
                where configuration_key = 'MODULE_IDEALO_DELIVERYFREE'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_DELIVERYFREE', '" . $_POST['configuration_value'] . "', 6, 1, '', now()) ");
  }

  $deliveryfree = stripcslashes($_POST['configuration_value']);
} else {
  // if nothing is entered by the admin: $quoting is disabled
  $deliveryfree = "";
}

/*
 * Deliverycosts
 */

// is a specific quoting character set?
if( isset($_POST['dilevery_costs'])) {
  // does a dataset exist?
  if( $deliverysosts_db !== false ) {

    // update value if $_POST['quoting_input'] != $quoting_db
    if( $_POST['dilevery_costs'] != $deliverysosts_db['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $_POST['dilevery_costs'] . "'
                where configuration_key = 'MODULE_IDEALO_DELIVERYCOSTS'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_DELIVERYCOSTS', '" . $_POST['configuration_value'] . "', 6, 1, '', now()) ");
  }

  $deliverysosts = stripcslashes($_POST['configuration_value']);
} else {
  // if nothing is entered by the admin: $quoting is disabled
  $deliverysosts = "";
}


/*
 * QUOTING
 */

// is a specific quoting character set?
if( isset($_POST['quoting_input'])) {
  // does a dataset exist?
  if( $quoting_db !== false ) {

    // update value if $_POST['quoting_input'] != $quoting_db
    if( $_POST['quoting_input'] != $quoting_db['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $_POST['quoting_input'] . "'
                where configuration_key = 'MODULE_IDEALO_QUOTING'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_QUOTING', '" . $_POST['quoting_input'] . "', 6, 1, '', now()) ");
  }

  $quoting = stripcslashes($_POST['quoting_input']);
} else {
  // if nothing is entered by the admin: $quoting is disabled
  $quoting = "";
}

/*
 * CODEXTRAFEE
 */

// is an extra fee for "cash on delivery" set?
if( isset($_POST['cod_extrafee_fix'])) {
  // does a dataset exist?
  if( $codextrafee_db !== false ) {
    // update value if $_POST['codextrafee_input'] != $quoting_db
    if( $_POST['cod_extrafee_fix'] != $codextrafee_db['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $_POST['cod_extrafee_fix'] . "'
                where configuration_key = 'MODULE_IDEALO_CODEXTRAFEE'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_CODEXTRAFEE', '" . $_POST['cod_extrafee_fix'] . "', 6, 1, '', now()) ");
  }

  $codextrafee = stripcslashes($_POST['cod_extrafee_fix']);
} else {
  // if nothing is entered by the admin: $quoting is disabled
  $codextrafee = "";
}

/*
 * PAYPALEXTRAFEE_FIX
 */

// is a fix fee for paypal set?
if( isset($_POST['paypal_extrafee_fix'])) {
  // does a dataset exist?
  if( $paypalextrafee_input_fix_db !== false ) {
    // update value if $_POST['paypal_extrafee_fix'] != $quoting_db
    if( $_POST['paypal_extrafee_fix'] != $paypalextrafee_input_fix_db['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $_POST['paypal_extrafee_fix'] . "'
                where configuration_key = 'MODULE_IDEALO_PAYPALEXTRAFEE_FIX'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_PAYPALEXTRAFEE_FIX', '" . $_POST['paypal_extrafee_fix'] . "', 6, 1, '', now()) ");
  }

  $paypal_extrafee_fix = stripcslashes($_POST['paypal_extrafee_fix']);
} else {
  $paypal_extrafee_fix = "";
}

/*
 * PAYPALEXTRAFEE_NOFIX
 */
// is a fee for paypal set that depends on then price+shipping cost?
if( isset($_POST['paypal_extrafee_nofix'])) {
  // does a dataset exist?
  if( $paypalextrafee_input_nofix_db !== false ) {
    // update value if $_POST['paypal_extrafee_nofix'] != $quoting_db
    if( $_POST['paypal_extrafee_nofix'] != $paypalextrafee_input_nofix_db['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $_POST['paypal_extrafee_nofix'] . "'
                where configuration_key = 'MODULE_IDEALO_PAYPALEXTRAFEE_NOFIX'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_PAYPALEXTRAFEE_NOFIX', '" . $_POST['paypal_extrafee_nofix'] . "', 6, 1, '', now()) ");
  }

  $paypal_extrafee_nofix = stripcslashes($_POST['paypal_extrafee_nofix']);
} else {
  $paypal_extrafee_nofix = "";
}

/*
 * PAYPALEXTRAFEE_NOFIX_SCINCLUSIVE
 */
// include or exclude shipping cost for variable extra fee
if( isset($_POST['paypal_extrafee_nofix_inkl_sc'])) {
  // does a dataset exist?
  if( $paypalextrafee_input_nofix_scinclusive_db !== false ) {
    // update value if $_POST['paypal_extrafee_nofix'] != $quoting_db
    if( $_POST['paypal_extrafee_nofix_inkl_sc'] != $paypalextrafee_input_nofix_scinclusive_db['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $_POST['paypal_extrafee_nofix_inkl_sc'] . "'
                where configuration_key = 'MODULE_IDEALO_PAYPALEXTRAFEE_NOFIX_SCINCLUSIVE'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_PAYPALEXTRAFEE_NOFIX_SCINCLUSIVE', '" . $_POST['paypal_extrafee_nofix_inkl_sc'] . "', 6, 1, '', now()) ");
  }

  $paypal_extrafee_nofix_scinclusive = stripcslashes($_POST['paypal_extrafee_nofix_inkl_sc']);
} else {
  $paypal_extrafee_nofix_scinclusive = "";
}

/*
 * PAYPAL_MAXPRICELIMIT
 */
// maximum price at which payment with paypal is possible
if( isset($_POST['paypal_maxpricelimit'])) {
  // does a dataset exist?
  if( $paypalmaxpricelimit_db !== false ) {
    // update value if $_POST['paypal_maxpricelimit'] != $quoting_db
    if( $_POST['paypal_maxpricelimit'] != $paypalmaxpricelimit_db['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $_POST['paypal_maxpricelimit'] . "'
                where configuration_key = 'MODULE_IDEALO_PAYPALMAXPRICELIMIT'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_PAYPALMAXPRICELIMIT', '" . $_POST['paypal_maxpricelimit'] . "', 6, 1, '', now()) ");
  }

  $paypal_maxpricelimit = stripcslashes($_POST['paypal_maxpricelimit']);
} else {
  $paypal_maxpricelimit = "";
}


/*
 * CCEXTRAFEE_FIX
 */

// is a fix fee for cc set?
if( isset($_POST['cc_extrafee_fix'])) {
  // does a dataset exist?
  if( $ccextrafee_input_fix_db !== false ) {
    // update value if $_POST['cc_extrafee_fix'] != $quoting_db
    if( $_POST['cc_extrafee_fix'] != $ccextrafee_input_fix_db['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $_POST['cc_extrafee_fix'] . "'
                where configuration_key = 'MODULE_IDEALO_CCEXTRAFEE_FIX'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_CCEXTRAFEE_FIX', '" . $_POST['cc_extrafee_fix'] . "', 6, 1, '', now()) ");
  }

  $cc_extrafee_fix = stripcslashes($_POST['cc_extrafee_fix']);
} else {
  $cc_extrafee_fix = "";
}

/*
 * CCEXTRAFEE_NOFIX
 */
// is a fee for cc set that depends on the price+shipping cost?
if( isset($_POST['cc_extrafee_nofix'])) {
  // does a dataset exist?
  if( $ccextrafee_input_nofix_db !== false ) {
    // update value if $_POST['cc_extrafee_nofix'] != $quoting_db
    if( $_POST['cc_extrafee_nofix'] != $ccextrafee_input_nofix_db['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $_POST['cc_extrafee_nofix'] . "'
                where configuration_key = 'MODULE_IDEALO_CCEXTRAFEE_NOFIX'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_CCEXTRAFEE_NOFIX', '" . $_POST['cc_extrafee_nofix'] . "', 6, 1, '', now()) ");
  }

  $cc_extrafee_nofix = stripcslashes($_POST['cc_extrafee_nofix']);
} else {
  $cc_extrafee_nofix = "";
}

/*
 * CCEXTRAFEE_NOFIX_SCINCLUSIVE
 */
// include or exclude shipping cost for variable extra fee
if( isset($_POST['cc_extrafee_nofix_inkl_sc'])) {
  // does a dataset exist?
  if( $ccextrafee_input_nofix_scinclusive_db !== false ) {
    // update value if $_POST['cc_extrafee_nofix'] != $quoting_db
    if( $_POST['cc_extrafee_nofix_inkl_sc'] != $ccextrafee_input_nofix_scinclusive_db['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $_POST['cc_extrafee_nofix_inkl_sc'] . "'
                where configuration_key = 'MODULE_IDEALO_CCEXTRAFEE_NOFIX_SCINCLUSIVE'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_CCEXTRAFEE_NOFIX_SCINCLUSIVE', '" . $_POST['cc_extrafee_nofix_inkl_sc'] . "', 6, 1, '', now()) ");
  }

  $cc_extrafee_nofix_scinclusive = stripcslashes($_POST['cc_extrafee_nofix_inkl_sc']);
} else {
  $cc_extrafee_nofix_scinclusive = "";
}

/*
 * CCEXTRAFEE_MAXPRICELIMIT
 */
// maximum price at which payment with cc is possible
if( isset($_POST['cc_maxpricelimit'])) {
  // does a dataset exist?
  if( $ccmaxpricelimit_db !== false ) {
    // update value if $_POST['cc_maxpricelimit'] != $quoting_db
    if( $_POST['cc_maxpricelimit'] != $ccmaxpricelimit_db['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $_POST['cc_maxpricelimit'] . "'
                where configuration_key = 'MODULE_IDEALO_CCMAXPRICELIMIT'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_CCMAXPRICELIMIT', '" . $_POST['cc_maxpricelimit'] . "', 6, 1, '', now()) ");
  }

  $cc_maxpricelimit = stripcslashes($_POST['cc_maxpricelimit']);
} else {
  $cc_maxpricelimit = "";
}


/*
 * $country
 */

// is a specific language set?
if( isset($_POST['language_input'])) {
  // does a dataset exist?
  if( $language_db !== false ) {

    // update value if $_POST['language_input'] != $quoting_db
    if( $_POST['language_input'] != $language_db['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $_POST['language_input'] . "'
                where configuration_key = 'MODULE_IDEALO_LANGUAGE'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_LANGUAGE', '" . $_POST['language_input'] . "', 6, 1, '', now()) ");
  }

  $country_sc = stripslashes($_POST['language_input']);
//  $this->country_array = explode (',', $country_sc);
} else {
  if(empty($language_db['configuration_value'])){
    $country_sc = 'DE';
  }else{
    $country_sc = $language_db['configuration_value'];
  }
}

/*
 * SHIPPINGLIMIT COMMENT
 */

// is shipping comment set?
// do not exceed COMMENTLENGTH
if( isset( $_POST['shippingcomment_input']) && ( strlen($_POST['shippingcomment_input']) <= COMMENTLENGTH ) ) {

  // does a dataset exist?
  if( $shipping_comment_db !== false ) {

    // update value if $_POST['freeshippinglimit_input'] != $freeshipping_comment_db
    if( $_POST['shippingcomment_input'] != $shipping_comment_db['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $_POST['shippingcomment_input'] . "'
                where configuration_key = 'MODULE_IDEALO_SHIPPINGCOMMENT'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_SHIPPINGCOMMENT', '" . $_POST['shippingcomment_input'] . "', 6, 1, '', now()) ");
  }

  $shipping_comment_input = stripslashes($_POST['shippingcomment_input']);

} else {
  $shipping_comment_input = "";
}

/*
 * FREESHIPPINGLIMIT COMMENT
 */

// is free shipping comment set?
// do not exceed COMMENTLENGTH
if( isset( $_POST['freeshippingcomment_input']) && ( strlen($_POST['freeshippingcomment_input']) <= COMMENTLENGTH ) ) {

  // does a dataset exist?
  if( $freeshipping_comment_db !== false ) {

    // update value if $_POST['freeshippingcomment_input'] != $freeshipping_comment_db
    if( $_POST['freeshippingcomment_input'] != $freeshipping_comment_db['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $_POST['freeshippingcomment_input'] . "'
                where configuration_key = 'MODULE_IDEALO_FREESHIPPINGCOMMENT'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_FREESHIPPINGCOMMENT', '" . $_POST['freeshippingcomment_input'] . "', 6, 1, '', now()) ");
  }

  $freeshipping_comment_input = stripslashes($_POST['freeshippingcomment_input']);
} else {
  $freeshipping_comment_input = "";
}

/*
 * LIVEDATA MODULE and LIVEDATA SETTING
 */

// is livedata module set?
$path = __FILE__; // path of this class
$path = substr($path, 0, -41); //cut
if(file_exists($path.PATH)) {

  // does a dataset exist?
  if( $livedata_db !== false ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = 'yes'
                where configuration_key = 'MODULE_IDEALO_LIVEDATA_MODULE'");
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_LIVEDATA_MODULE', 'yes', 6, 1, '', now()) ");
  }
  // check and update the livedata setting
  if( isset($_POST['export'])) {
    $livedata = '';
    if ($_POST['export'] == 'live'){
      $livedata = 'yes';
    }else{
      $livedata = 'no';
    }
    // does a dataset exist?
    if( $livedata_setting_db !== false ) {
        xtc_db_query("update " . TABLE_CONFIGURATION . "
                  set configuration_value = '{$livedata}'
                  where configuration_key = 'MODULE_IDEALO_LIVEDATA_SETTING'");
    } else {
      xtc_db_query("insert into " . TABLE_CONFIGURATION . "
              (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
              values ('MODULE_IDEALO_LIVEDATA_SETTING', '{$livedata}', 6, 1, '', now()) ");
    }
  }

  $livedata_module = 'yes';
} else {
    // does a dataset exist?
  if( $livedata_db !== false ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = 'no'
                where configuration_key = 'MODULE_IDEALO_LIVEDATA_MODULE'");
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_IDEALO_LIVEDATA_MODULE', 'no', 6, 1, '', now()) ");
  }

  $livedata_module = 'no';
}

/*
 * zipfile setting
 */
// is zipfile settingset?
// do not exceed IDEALO_ZIP_SETTING

if( isset( $_POST['pack']) ) {
  // does a dataset exist?

  if( $zip_setting_db !== false ) {

    // update value if $_POST['pack'] != $zip_setting_db
    if( $_POST['pack'] != $zip_setting_db['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $_POST['pack'] . "'
                where configuration_key = 'IDEALO_ZIP_SETTING'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('IDEALO_ZIP_SETTING', '" . $_POST['pack'] . "', 6, 1, '', now()) ");
  }

  $zip_setting_input = stripslashes($_POST['pack']);

}else {
  $zip_setting_input = "";
}

// check is filename already in db
$h_string = 'select `configuration_value` from `'. TABLE_CONFIGURATION . '` where `configuration_key` = \'IDEALO_FILENAME\' LIMIT 1';
$file_name_query = xtc_db_query($h_string);
$file_name_db = xtc_db_fetch_array($file_name_query );


// file config
define('SEPARATOR', $separator);           // character that separates the data

define('QUOTECHAR',  $quoting);              // character to quote the data
define('CODEXTRAFEE_VALUE',  $codextrafee);        // extra fee for "cash on delivery"
define('COUNTRY_SC', $country_sc);           // country the shipping costs are for
define('PAYPALEXTRAFEE_FIX', $paypal_extrafee_fix); // value of fix fee
define('PAYPALEXTRAFEE_NOFIX', $paypal_extrafee_nofix); // value of fee that is not fix but dependent on price+shipping cost
define('PAYPALEXTRAFEE_NOFIX_SCINCLUSIVE', $paypal_extrafee_nofix_scinclusive); // include or exclude sc in PAYPALEXTRAFEE_NOFIX?
define('PAYPALEXTRAFEE_MAXPRICELIMIT', $paypal_maxpricelimit); // value of fee that is not fix but dependent on price+shipping cost


define('CCEXTRAFEE_FIX', $cc_extrafee_fix); // value of fix fee
define('CCEXTRAFEE_NOFIX', $cc_extrafee_nofix); // value of fee that is not fix but dependent on price+shipping cost
define('CCEXTRAFEE_NOFIX_SCINCLUSIVE', $cc_extrafee_nofix_scinclusive); // include or exclude sc in CCEXTRAFEE_NOFIX?
define('CCEXTRAFEE_MAXPRICELIMIT', $cc_maxpricelimit); // value of fee that is not fix but dependent on price+shipping cost

define('DISPLAYINACTIVEMODULES', true); // display modules that are not active but in the payment array
                    // advantage: structure of the file hardly changes

define('SHIPPINGCOMMENT_INPUT', $shipping_comment_input);
define('FREESHIPPINGCOMMENT_INPUT', $freeshipping_comment_input);
define('SHOWFREESHIPPINGLIMITCOMMENT', true); // set 'true' to show comment for free shipping limit
define('SPLITCHAR', ',');  // character to split an array
define('LIVEDATA_MODULE',$livedata_module );
define('ZIP_SETTING', $zip_setting_input);
define('DELIVERY_COSTS', $deliverysosts);
define('DELIVERY_FREE', $deliveryfree);
define('MODULE_SHIPPING_TYPE', $module_shipping_type);

//filter
define('ARTICLE_FILTER_VALUE', $article_filter_value);
define('ARTICLE_EXPORT', $article_filter);
define('ARTICLE_FILTER', '<hr noshade><b>Filter nach Artikelnummer</b>');
define('ARTICLE_FILTER_SELECTION', 'W&auml;hlen Sie aus, ob die Artikel gefiltert, oder "nur diese" exportiert werden sollen.');
define('ARTICLE_FILTER_TEXT', 'Geben Sie hier die Artikelnummern ein. Trennen Sie die Artikelnummern mit einem Semikolon ";".');
define('BRAND_FILTER_VALUE', $brand_filter_value);
define('BRAND_EXPORT', $brand_filter);
define('BRAND_FILTER', '<b>Filter nach Hersteller</b>');
define('BRAND_FILTER_SELECTION', 'W&auml;hlen Sie aus, ob die Hersteller gefiltert, oder "nur diese" exportiert werden sollen.');
define('BRAND_FILTER_TEXT', 'Geben Sie hier die Hersteller ein. Trennen Sie die Hersteller mit einem Semikolon ";".');
define('CAT_FILTER_VALUE', $cat_filter_value);
define('CAT_EXPORT', $cat_filter);
define('CAT_FILTER', '<b>Filter nach Kategorien</b>');
define('CAT_FILTER_SELECTION', 'W&auml;hlen Sie aus, ob die Kategorien gefiltert, oder &quot;nur diese&quot; exportiert werden sollen.');
define('CAT_FILTER_TEXT', 'Geben Sie hier die Kategorien ein. Trennen Sie die Kategorien mit einem Semikolon &quot;;&quot;. Es gen&uuml;gt, einen Teilpfad der Kategorie anzugeben. wird der Teilpfad in der Kategorie eines Artikels gefunden, wird dieser gefiltert. Z.B. Filter &quot;TV&quot;: alle Kategorien mit &quot;TV&quot; als Teilpfad (z.B. TV->LCD und TV->Plasma) werden gefiltert. Filter &quot;LCD&quot;: alle Artikel mit dem Teilpfad &quot;LCD&quot; werden gefiltert. &quot;TV->Plasma&quot; wird exportiert.');

define('CAMPAIGN', '94511215');

if ( file_exists ( DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.xtcprice.php' ) ){
  require_once ( DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.xtcprice.php' );
}
if ( file_exists ( DIR_FS_CATALOG . DIR_WS_CLASSES . 'xtcPrice.php' ) ){
  require_once ( DIR_FS_CATALOG . DIR_WS_CLASSES . 'xtcPrice.php' );
}

require_once ( DIR_FS_INC . 'xtc_get_product_path.inc.php');
require_once ( DIR_FS_INC . 'xtc_get_category_path.inc.php');
require_once ( DIR_FS_INC . 'xtc_get_parent_categories.inc.php');
require_once (DIR_FS_INC.'xtc_get_product_path.inc.php');
require_once (DIR_FS_INC.'xtc_get_category_path.inc.php');
require_once (DIR_FS_INC.'xtc_get_parent_categories.inc.php');

//include 'backup/zip.php';
  class idealo{


    // these attributes have to be public, as module_export.php uses them directly ...
    public $code;
    public $title;
    public $description;
    public $enabled;

    //language id
    public $language;


  // all payment (and its status) that should be displayed in the csv
  // if a payment is 'false', the column in the csv stays empty
  // the key needs to be the same as it is used in the db for the entry in `configuration_key` in the table `configuration`
  private $payment = array('CC'           => array('active'   => false,
                               'title'   => 'Kreditkarte',
                               'db'    => 'CC',
                               'fix'    => '',
                               'no_fix'  => '',
                               'max'    => '',
                               'incl'    => 'no'),
               'PAYPAL'         => array('active'   => false,
                               'title'   => 'PayPal',
                               'db'    => 'PAYPAL',
                               'fix'    => '',
                               'no_fix'  => '',
                               'max'    => '',
                               'incl'    => 'no'),
               'SOFORTUEBERWEISUNG'  => array('active'   => false,
                                'title'   => 'Sofortueberweisung',
                                'db'    => 'SOFORTUEBERWEISUNG',
                               'fix'    => '',
                               'no_fix'  => '',
                               'max'    => '',
                               'incl'    => 'no'),
              );

  private $cod = array('active'   => false,
             'title'   => 'Nachnahme',
             'db'    => 'COD',
             'fix'    => '');

  private $paymentTaxModulClass;       // e.g. MODULE_SHIPPING_FLAT_TAX_CLASS, MODULE_SHIPPING_TABLE_TAX_CLASS ...
  private $paymentTaxModulZone;      // e.g. MODULE_SHIPPING_FLAT_TAX_ZONE, MODULE_SHIPPING_TABLE_TAX_ZONE ...

  private $loworderfee = false;       // no surcharge (loworderfee ...)
  private $loworderOption = array();    // contains max price ('orderfeeUnder') the surcharged is charged and surcharge ('loworderfee')

  //shippingcosts
  private $shippingcosts;
  private $maxprice_value;


  //allowed shippingtypes by module
  public $shipping_type = array();

  private $allowed_types = array('FLAT', 'ITEM', 'DP', 'TABLE');

  private $freeShippingAllowed = '';

    public function __construct() {

      $this->code = 'idealo';
      if ( TEXT_IDEALO_CSV_MODIFIED == 'no' ){
              $this->title = MODULE_IDEALO_TEXT_TITLE . ' v. '. VERSION_TEXT_02 . ' - ' . NEW_IDEALO_VERSION_TEXT;
      }else{
              $this->title = MODULE_IDEALO_TEXT_TITLE . ' v. '. VERSION_TEXT_02 . '.mod - ' . NEW_IDEALO_VERSION_TEXT;
      }

//      $this->img = 'images/export/idealo.gif';
      $this->description = MODULE_IDEALO_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_IDEALO_SORT_ORDER;
      $this->enabled = ((MODULE_IDEALO_STATUS == 'True') ? true : false);
      $this->CAT=array();
      $this->PARENT=array();
      $this->productsPrice = 0;
      $this->description = '<center><a href="http://www.idealo.de" target="_blank"><img src = "http://www.idealo.de/pics/common/logoidealo_blue_l.gif"></a></center>';
      $this->checkFreeShipping();
      $this->getShippingTypes();

      // check which payment method (cod, cash etc. ...) is active
      $this->checkActivePayment();

      $this->getShippingcostsFromDB();
      // check if surcharge is active
//      $this->checkLoworderfee();
    }


  /**
   * check if freeamount ist active
   */
   private function checkFreeShipping(){
     $value = xtc_db_query("SELECT `configuration_key`
                  FROM `configuration`
                  WHERE `configuration_key` LIKE 'MODULE_SHIPPING_FREEAMOUNT_STATUS';");

     $shipping = xtc_db_fetch_array($value);

     if($shipping['configuration_key'] == 'MODULE_SHIPPING_FREEAMOUNT_STATUS'){

       $value = xtc_db_query("SELECT `configuration_value`
                  FROM `configuration`
                  WHERE `configuration_key` LIKE 'MODULE_SHIPPING_FREEAMOUNT_STATUS';");

       $shipping = xtc_db_fetch_array($value);

       if($shipping['configuration_value'] == 'True'){
         $free = xtc_db_query("SELECT `configuration_value`
                    FROM `configuration`
                    WHERE `configuration_key` LIKE 'MODULE_SHIPPING_FREEAMOUNT_AMOUNT';");

         $free = xtc_db_fetch_array($free);

         $this->freeShipping = true;
        $this->freeShippingValue = (float)$free['configuration_value'];

        $free = xtc_db_query("SELECT `configuration_value`
                    FROM `configuration`
                    WHERE `configuration_key` LIKE 'MODULE_ORDER_TOTAL_FREEAMOUNT_FREE';");

        $free = xtc_db_fetch_array($free);
        $this->freeShippingValueCOD =  $free['configuration_value'];

        $free = xtc_db_query("SELECT `configuration_value`
                    FROM `configuration`
                    WHERE `configuration_key` LIKE 'MODULE_ORDER_TOTAL_FREEAMOUNT_ALLOWED';");

        $free = xtc_db_fetch_array($free);
        $this->freeShippingAllowed =  $free['configuration_value'];


       }
     }else{
         $this->freeShipping = false;
     }
   }


  /**
   * get all allowed types
   */
   public function getShippingTypes(){
     $value = xtc_db_query("SELECT `configuration_key`
                  FROM `configuration`
                  WHERE `configuration_key` LIKE 'MODULE_SHIPPING_%_STATUS'
                  AND `configuration_value` LIKE 'True';");
    //each shipping modul status true
    $modul = 'MODULE_ORDER_TOTAL_FREEAMOUNT_FREE';

     while ($shipping = xtc_db_fetch_array($value)) {

       $type = explode('_',$shipping['configuration_key']);

      if($type[2] == 'FREEAMOUNT'){

        $free = xtc_db_query("SELECT `configuration_value`
                    FROM `configuration`
                    WHERE `configuration_key` LIKE 'MODULE_SHIPPING_FREEAMOUNT_AMOUNT';");

        $this->freeShipping = true;
        $free = xtc_db_fetch_array($free);
        $this->freeShippingValue = $free['configuration_value'];

        $free = xtc_db_query("SELECT `configuration_value`
                    FROM `configuration`
                    WHERE `configuration_key` LIKE 'MODULE_ORDER_TOTAL_FREEAMOUNT_FREE';");

        $free = xtc_db_fetch_array($free);
        $this->freeShippingValueCOD =  $free['configuration_value'];

      }elseif($this->isInAllowedShipping($type[2]) === true){

         $resultarray = array();

         $resultarray ['name'] = $type[2];

         $resultarray ['modul'] = $type[0]. '_' . $type[1]. '_' . $type[2]. '_';

         $shipping_attributes = xtc_db_query("SELECT `configuration_key`
                      FROM `configuration`
                      WHERE `configuration_key` LIKE 'MODULE_SHIPPING_" . $type[2] . "_%';");



         //every attributes for aktive shippingmodul
         while ($detail = xtc_db_fetch_array($shipping_attributes)){
           $det = xtc_db_query("SELECT `configuration_value`
                      FROM `configuration`
                      WHERE `configuration_key` LIKE '" . $detail['configuration_key'] . "';");

          $det = xtc_db_fetch_array($det);

          $resultarray [$detail['configuration_key']] = $det['configuration_value'];

          $key = 'MODULE_ORDER_TOTAL_COD_FEE_' . $type[2];

          $det = xtc_db_query("SELECT `configuration_value`
                      FROM `configuration`
                      WHERE `configuration_key` LIKE '" . $key . "';");

          $det = xtc_db_fetch_array($det);



          $resultarray [$key] = $det['configuration_value'];


         }

           $this->shipping_type[$type[2]] = $resultarray;
       }
     }
   }

  /**
   * look if shippingtype is allowed in module
   *
   * @param string
   * @return boolean
   */
   private function isInAllowedShipping($type){
     foreach($this->allowed_types as $all){
       if($all == $type){
         return true;
       }
     }
     return false;
   }


  /**
   * get shipping costs from db
   */
    public function getShippingcostsFromDB(){
      $result = xtc_db_query("  SELECT `configuration_value`
                    FROM `configuration`
                    WHERE `configuration_key` LIKE 'MODULE_IDEALO_DELIVERYCOSTS';");
    $result = xtc_db_fetch_array($result);
    $this->shippingcosts = $result['configuration_value'];

    $result = xtc_db_query("  SELECT `configuration_value`
                    FROM `configuration`
                    WHERE `configuration_key` LIKE 'MODULE_IDEALO_DELIVERYFREE';");
    $result = xtc_db_fetch_array($result);
    $this->maxprice_value = $result['configuration_value'];


    }

  /**
   * Checks which payment method is active
   */
  private function checkActivePayment() {
  foreach($this->payment as $pay){
       //is payment active?
       $result = xtc_db_query("  SELECT `configuration_value`
                    FROM `configuration`
                    WHERE `configuration_key` LIKE 'MODULE_IDEALO_" . $pay['db'] . "_ACTIVE';");
      $result = xtc_db_fetch_array($result);
      if($result['configuration_value'] == 'yes'){
      //set payment active
      $this->payment[$pay['db']]['active'] = true;

      //get extrafee fix
      $result = xtc_db_query("  SELECT `configuration_value`
                    FROM `configuration`
                    WHERE `configuration_key` LIKE 'MODULE_IDEALO_" . $pay['db'] . "EXTRAFEE_FIX';");
      $result = xtc_db_fetch_array($result);
      $this->payment[$pay['db']]['fix'] = $result['configuration_value'];

      //get extra fee no fix
      $result = xtc_db_query("  SELECT `configuration_value`
                    FROM `configuration`
                    WHERE `configuration_key` LIKE 'MODULE_IDEALO_" . $pay['db'] . "EXTRAFEE_NOFIX';");
      $result = xtc_db_fetch_array($result);
      $this->payment[$pay['db']]['no_fix'] = $result['configuration_value'];

      //get max for payment
      $result = xtc_db_query("  SELECT `configuration_value`
                    FROM `configuration`
                    WHERE `configuration_key` LIKE 'MODULE_IDEALO_" . $pay['db'] . "MAXPRICELIMIT';");
      $result = xtc_db_fetch_array($result);
      $this->payment[$pay['db']]['max'] = $result['configuration_value'];

      //get shipping inclusive for costs for payment
      $result = xtc_db_query("  SELECT `configuration_value`
                    FROM `configuration`
                    WHERE `configuration_key` LIKE 'MODULE_IDEALO_" . $pay['db'] . "EXTRAFEE_NOFIX_SCINCLUSIVE';");
      $result = xtc_db_fetch_array($result);
      $this->payment[$pay['db']]['incl'] = $result['configuration_value'];



      }else{
        $this->payment[$pay['db']]['active'] = false;
      }
     }

     //COD
     $result = xtc_db_query("  SELECT `configuration_value`
                  FROM `configuration`
                  WHERE `configuration_key` LIKE 'MODULE_IDEALO_COD_ACTIVE';");
    $result = xtc_db_fetch_array($result);
    if($result['configuration_value'] == 'yes'){
      //set payment active
      $this->cod['active'] = true;
      $result = xtc_db_query("  SELECT `configuration_value`
                  FROM `configuration`
                  WHERE `configuration_key` LIKE 'MODULE_IDEALO_CODEXTRAFEE';");
      $result = xtc_db_fetch_array($result);
      $this->cod['fix'] = $result['configuration_value'];
    }
  }

  /**
   * look for value in filter
   */
  private function isIn($value, $array){
    $array = explode(';', $array);
    foreach($array as $a){
      if($a == $value){
        return true;
      }
    }
    return false;
  }

  /**
   * filter article for export
   *
   * @param int
   * @param string
   * @return boolean
   */
  private function filter($id, $brand){

    if(BRAND_FILTER_VALUE != ''){
      $isIn = $this->isIn($brand, BRAND_FILTER_VALUE);
      if(BRAND_EXPORT == 'export'){
        if($isIn === false){
          return false;
        }
      }

      if(BRAND_EXPORT == 'filter'){
        if($isIn === true){
          return false;
        }
      }
    }

    if(ARTICLE_FILTER_VALUE != ''){
      $isIn = $this->isIn($id, ARTICLE_FILTER_VALUE);
      if(ARTICLE_EXPORT == 'export'){
        if($isIn === false){
          return false;
        }
      }

      if(ARTICLE_EXPORT == 'filter'){
        if($isIn === true){
          return false;
        }
      }
    }

    return true;

  }


  /**
   * check if COD for shippingtype is active
   *
   * @param string
   * @return boolean
   */
   private function codActive($shipping){
     if($shipping != 'HARD'){
       $value = xtc_db_query("SELECT `configuration_value`
                    FROM `configuration`
                    WHERE `configuration_key` LIKE '  MODULE_ORDER_TOTAL_COD_FEE_STATUS';");

       $value = xtc_db_fetch_array($value);

       if($value['configuration_value'] == 'true'){
         return true;
       }else{
         return false;
       }
     }else{
       if($this->cod['active'] === true){
         return true;
       }else{
         return false;
       }
     }

   }


  /**
   * filter for categories
   *
   * @param string
   * @return boolean
   */
   public function filterCat($cat){
     if(CAT_FILTER_VALUE != ''){
       $cat_filter = explode(';', CAT_FILTER_VALUE);
       foreach($cat_filter as $ca){
         if(strpos($cat, $ca) !== false){
           if(CAT_EXPORT == 'export'){
             return true;
           }
           if(CAT_EXPORT == 'filter'){
             return false;
           }
         }
       }
    }

    if(CAT_FILTER_VALUE != '' && CAT_EXPORT == 'export'){
      return false;
    }
    return true;
   }

  /**
   * get categories text from db
   *
   * @param int
   * @return string
   */
  public function getCategory( $id = '0' ){

    $cat_array = array();

     $cat_text = '';

     if ( isset ( $id ) && $id != 0 ) {
       $ids = explode( '_', $id );
       foreach( $ids as $ca ){
         $category_query =xtc_db_query(  "SELECT `categories_name`
                         FROM `categories_description`
                         WHERE `categories_id` = " . $ca . " AND `language_id` = " . $_SESSION['languages_id'] . ";" );

         $category = xtc_db_fetch_array( $category_query );

         $cat_text .= $category['categories_name'] . ' -> ';
       }
     }else{
       return 'keine Kategorie';
     }


     return substr( $cat_text, 0 , -4 );

  }

  /**
   * get images names from db and create links to info  folder
   *
   * @param int article
   * @return string of images
   */
   public function getImages( $id, $main_image ){
     $images = HTTP_CATALOG_SERVER . DIR_WS_CATALOG_POPUP_IMAGES . $main_image . ';';

     $images_query = xtc_db_query ( "SELECT `image_name` FROM `products_images` WHERE `products_id` = " . $id . ";" );

     while ($image = xtc_db_fetch_array( $images_query )) {
       $images .= HTTP_CATALOG_SERVER . DIR_WS_CATALOG_POPUP_IMAGES . $image['image_name'] . ';';
     }

    return substr( $images, 0, -1 );
   }

  /**
   * Methode creates the content of the csv
   *
   * @param string $file
   */
    public function process($file) {
      $schema = '';

      @xtc_set_time_limit(0);
      $xtPrice = new xtcPrice(CURRENCY,'1');

      $free_shipping_value = '';

      if ( $_POST [ 'dilevery_free' ] != '' ){
        $free_shipping_value = ( float ) $_POST [ 'dilevery_free' ];
      }

      $file = $_POST['configuration']['MODULE_IDEALO_FILE'];

      $schema .= QUOTECHAR . ARTICLE_ID . QUOTECHAR . SEPARATOR .
                 QUOTECHAR . BRAND . QUOTECHAR . SEPARATOR .
                 QUOTECHAR . PRODUCT_NAME . QUOTECHAR . SEPARATOR .
                 QUOTECHAR . CATEGORIE . QUOTECHAR . SEPARATOR .
                 QUOTECHAR . DESCRIPTION_SHORT . QUOTECHAR . SEPARATOR .
                 QUOTECHAR . DESCRIPTION_SHORT_LONG . QUOTECHAR . SEPARATOR .
                 QUOTECHAR . IMAGE . QUOTECHAR . SEPARATOR .
                 QUOTECHAR . DEEPLINK . QUOTECHAR . SEPARATOR .
                 QUOTECHAR . PRICE . QUOTECHAR . SEPARATOR .
                 QUOTECHAR . EAN . QUOTECHAR . SEPARATOR .
                 QUOTECHAR . DELIVERY . QUOTECHAR . SEPARATOR;

      $schema .= QUOTECHAR . 'Vorkasse' . QUOTECHAR . SEPARATOR;

      if($this->codActive($_POST['shipping']) === true){
        $schema .= QUOTECHAR . 'Nachnahme' . QUOTECHAR . SEPARATOR;
      }

      if($_POST['cod_active'] == 'yes'){
        $schema .= QUOTECHAR . 'Nachnahme aus Modul' . QUOTECHAR . SEPARATOR;
      }

      // run through the payment method titles to display them in the header
      foreach($this->payment as $payment) {
        if($payment['active'] === true){
          $schema .= QUOTECHAR . $payment['title'] . QUOTECHAR . SEPARATOR;
        }
      }

      // shipping comment
      $schema .= QUOTECHAR . 'Versandkommentar' . QUOTECHAR . SEPARATOR;

      // loworder fee
      if( $this->loworderfee === true ) {
        $schema .= QUOTECHAR . 'Mindermengenzuschlag' . QUOTECHAR . SEPARATOR;
      }

      // product weight
      $schema .= QUOTECHAR . 'Gewicht' . QUOTECHAR . SEPARATOR;

      //VPE Value
      $schema .= QUOTECHAR . 'VPE Value' . QUOTECHAR . SEPARATOR .
                 QUOTECHAR . ARTICLE_SHOP_NUMBER . QUOTECHAR . SEPARATOR;

      $schema .= "\n";

      if ( TEXT_IDEALO_CSV_MODIFIED == 'no' ){
        $schema .= VERSION_TEXT_01 . VERSION_TEXT_02 . ' vom ' . VERSION_TEXT_04;
      }else{
        $schema .= VERSION_TEXT_01 . VERSION_TEXT_02 . '.mod vom ' . VERSION_TEXT_04;
      }

      $schema .= "\n";

      $export_query =xtc_db_query("SELECT
                           p.products_id,
                           pd.products_name,
                           pd.products_description,
                           pd.products_short_description,
                           p.products_model,
                           p.products_ean,
                           p.products_image,
                           p.products_price,
                           p.products_status,
                           p.products_shippingtime,
                           p.products_tax_class_id,
                           p.products_weight,
                           m.manufacturers_name,
                           p.products_vpe_value,
                           p.products_vpe_status,
                           p.products_vpe
                       FROM
                           " . TABLE_PRODUCTS . " p LEFT JOIN
                           " . TABLE_MANUFACTURERS . " m
                         ON p.manufacturers_id = m.manufacturers_id LEFT JOIN
                           " . TABLE_PRODUCTS_DESCRIPTION . " pd
                         ON p.products_id = pd.products_id AND
                          pd.language_id = '".$_SESSION['languages_id']."' LEFT JOIN
                           " . TABLE_SPECIALS . " s
                         ON p.products_id = s.products_id
                       WHERE
                         p.products_status = 1
                       ORDER BY
                          p.products_date_added DESC,
                          pd.products_name");

      while ($products = xtc_db_fetch_array($export_query)) {
        $cat =  $this->cleanText ( $this->getCategory ( xtc_get_product_path ( $products [ 'products_id' ] ) ), 100 );

        if($this->filter($products['products_id'], $products['manufacturers_name']) === true && $this->filterCat($cat) === true){
          $cat = str_replace(QUOTECHAR, QUOTECHAR.QUOTECHAR, $cat);
          $products_price = $xtPrice->xtcGetPrice($products['products_id'],
                                    $format=false,
                                    1,
                                    $products['products_tax_class_id'],
                                    '');
          $this->productsPrice = $products_price;

          // replace characters and cut to the appropriate length
          $products_description = $this->cleanText ( $products['products_description'], 1000 );
          $products_description = str_replace ( QUOTECHAR, QUOTECHAR.QUOTECHAR, $products_description );
          $products_description = str_replace ( SEPARATOR, "", $products_description );

          $products_short_description = $this->cleanText ( $products['products_short_description'], 250 );
          $products_short_description = str_replace ( QUOTECHAR, QUOTECHAR.QUOTECHAR, $products_short_description );
          $products_short_description = str_replace ( SEPARATOR, "", $products_short_description );

          if ($products['products_image'] != ''){
              $image = $this->getImages( $products['products_id'], $products['products_image']);
          }else{
              $image = '';
          }

          //get compaign
          $campaign = '';
          if( $_POST['campaign'] != '0' ){
            $campaign = $_POST['campaign'];
          }

          $price = number_format($products_price,2,'.','');

          // create content
          $schema .= QUOTECHAR . $products['products_id'] . QUOTECHAR . SEPARATOR .
                     QUOTECHAR . $products['manufacturers_name']. QUOTECHAR . SEPARATOR;

          $products['products_name'] = str_replace(QUOTECHAR, QUOTECHAR.QUOTECHAR, $products['products_name']);

          $schema .= QUOTECHAR . $this->cleanText ( $products['products_name'], 200) . QUOTECHAR . SEPARATOR .
                     QUOTECHAR . $cat . QUOTECHAR. SEPARATOR .
                     QUOTECHAR . $products_short_description . QUOTECHAR . SEPARATOR .
                     QUOTECHAR . $products_description . QUOTECHAR . SEPARATOR .
                     QUOTECHAR . $image . QUOTECHAR . SEPARATOR .
                     QUOTECHAR . HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'product_info.php?'.$campaign.xtc_product_link($products['products_id'], $products['products_name']) . QUOTECHAR . SEPARATOR .
                     QUOTECHAR . $price . QUOTECHAR . SEPARATOR .
                     QUOTECHAR . $products['products_ean'] . QUOTECHAR . SEPARATOR .
                     QUOTECHAR . xtc_get_shipping_status_name($products['products_shippingtime']) . QUOTECHAR . SEPARATOR;

          // free shipping costs AND free sc comment available?
          $showScFreeComment = false;

          $shipping = $this->getShipping($price, $products['products_weight'], $_POST['shipping'], 'DE');

          if ( $free_shipping_value != '' ){
            if( ( float ) $price >= $free_shipping_value ){
              $shipping = 0;
            }
          }

          $schema .= QUOTECHAR . round($shipping, 2) . QUOTECHAR . SEPARATOR;

          if($this->codActive($_POST['shipping']) === true){
            $cod = $this->getCOD($shipping, 'DE', $_POST['shipping']);
            $schema .= QUOTECHAR . round($cod, 2) . QUOTECHAR . SEPARATOR;
          }

          if(($_POST['cod_active']) == 'yes'){
            $cod = $_POST['cod_extrafee_fix'];
            $schema .= QUOTECHAR . round($cod, 2) . QUOTECHAR . SEPARATOR;
          }

          // run through the payment methods to display the fee
          foreach($this->payment as $payment) {
            if($payment['active'] === true){
              $schema .= QUOTECHAR . $this->getPaymentCosts( $payment [ 'db' ], $price, $shipping ) . QUOTECHAR . SEPARATOR;
            }
          }

          $schema .= QUOTECHAR . SHIPPINGCOMMENT_INPUT . QUOTECHAR . SEPARATOR;

          if (empty($products['products_weight'])){
            $schema .= QUOTECHAR . 'keine Angabe' . QUOTECHAR . SEPARATOR;
          }else{
            $schema .= QUOTECHAR . $products['products_weight'] . QUOTECHAR . SEPARATOR;
          }

          if ($products['products_vpe_status'] == '1' && (float)$products['products_vpe_value'] > 0){
            $vpe = $this->getVPE($products['products_vpe']);
            $base_price = $price / $products['products_vpe_value'];
            $schema .= QUOTECHAR . round($base_price, 2) . ' ' . CURRENCY . ' / ' . $vpe . QUOTECHAR . SEPARATOR;
          }else{
            $schema .= QUOTECHAR . '' . QUOTECHAR . SEPARATOR;
          }

          $schema .= QUOTECHAR . $products['products_model'] . QUOTECHAR . SEPARATOR;

          $schema .= "\n";
        }
      }

      // create File
      $fp = fopen(DIR_FS_DOCUMENT_ROOT.'export/' . $file, "w+");
      fputs($fp, $schema);
      fclose($fp);

      if( isset($_POST['export']) && $_POST['export'] == 'yes' ) {
        // send File to Browser
        $extension = substr($file, -3);
        $fp = fopen(DIR_FS_DOCUMENT_ROOT.'export/' . $file,"rb");
        $buffer = fread($fp, filesize(DIR_FS_DOCUMENT_ROOT.'export/' . $file));
        fclose($fp);
        header('Content-type: application/x-octet-stream');
        header('Content-disposition: attachment; filename=' . $file);
        echo $buffer;
        exit;
      }

    }

   /**
     * function format string and cut lenght
     *
     * @param string text to clean
     * @param integer lenght to cut
     *
     * @return string in utf-8
     */
    public function cleanText( $text, $cut ){

      // newlines will be deleta from text
      $text = str_replace ( array ( "\r\n", "\r", "\n", "|", "&nbsp;" ), "", $text );

      // characters that should be replaced

      // replace by space
      $spaceToReplace = array ( "<br>", "<br />", "\n", "\r", "\t", "\v", chr(13) );

      // replace by comma
      $commaToReplace = array ( "'" );

      // replace characters and cut to the appropriate length
      $text = strip_tags ( $text );
      $text = str_replace ( $spaceToReplace, " ", $text );
      $text = str_replace ( $commaToReplace, ", ", $text ) ;

      //remove all tags begin '<' and end '>'
      $Regex = '/<.*>/';
      $Ersetzen = ' ';
      $text = preg_replace ( $Regex, $Ersetzen, $text );

      // decode text into utf-8
      $text = html_entity_decode ( $text, ENT_QUOTES, "UTF-8" );

      $text = $this->prepareText ( $text );

      // all allowed characters in text
      $regex = '/[^\d\w\s_\!\$\%&;:+\^\~#\-|\/]/';

      // delet all not allowed characters in text
      $text = preg_replace ( $regex, '', $text );

      // if mb_substr exists cut text by this function
      if ( function_exists ( mb_substr ) ){
        $text = mb_substr ( $text, 0, $cut );
      }else{
        $text = substr( $text, 0, $cut );
      }

      // decode all html entities in text
      $text = htmlentities ( $text, ENT_QUOTES, "UTF-8" );

      return $text;
    }

  /**
   * prepair text means clean it. Replace all special characters
   *
   * @param string
   * @return string in utf-8
   */
   public function prepareText( $string ){

    $spaceToReplace = array ( "$", ".", "|" ); // replace by space

    $string = str_replace ( $spaceToReplace, " ", $string );
    $string = str_replace ( "ä", "ae", $string );
    $string = str_replace ( "ü", "ue", $string );
    $string = str_replace ( "ö", "oe", $string );
    $string = str_replace ( "Ä", "Ae", $string );
    $string = str_replace ( "Ü", "ue", $string );
    $string = str_replace ( "Ö", "Oe", $string );
    $string = str_replace ( "ß", "ss", $string );

    return $string;

   }

  /**
   * get cod if allowed
   *
   * @param float
   * @param string
   * @param string
   * @return string
   */
  private function getCOD($shipping, $country, $name){

    if($name == 'HARD'){
      return $this->cod['fix'];
    }

    //get allowed countries for cod
    $coutries = xtc_db_query("SELECT `configuration_value`
                  FROM `configuration`
                  WHERE `configuration_key` LIKE 'MODULE_PAYMENT_COD_ALLOWED';");

    $coutries = xtc_db_fetch_array($coutries);

    $allowed = false;

    $allowed_coutries = explode(',', $coutries['configuration_value']);

    foreach($allowed_coutries as $ac){
      if($ac == $country){
        $allowed = true;
        break;
        break;
      }
    }

    if($allowed === true){
      //get tax for cod
      //first get tax id
      $tax_id = xtc_db_query("SELECT `configuration_value`
                  FROM `configuration`
                  WHERE `configuration_key` LIKE 'MODULE_ORDER_TOTAL_COD_FEE_TAX_CLASS';");

      $tax_id = xtc_db_fetch_array($tax_id);


      $tax = xtc_db_query("SELECT `tax_rate`
                  FROM `tax_rates`
                  WHERE `tax_class_id` = " . $tax_id['configuration_value'] . " AND `tax_zone_id` = 5;");

      $tax = xtc_db_fetch_array($tax);


      $costs = xtc_db_query("SELECT `configuration_value`
                  FROM `configuration`
                  WHERE `configuration_key` LIKE 'MODULE_ORDER_TOTAL_COD_FEE_{$name}';");

      $costs = xtc_db_fetch_array($costs);

      //explode by , to get all costs for countries
      $costs = explode(',', $costs['configuration_value']);

      //check for costs for coutry
      foreach($costs as $cost){
        $cost = explode(':', $cost);
        if($cost[0] == $country){
          $cod_fee = $shipping + ($cost[1] * (1 + $tax['tax_rate'] / 100));
          return round($cod_fee, 2);
        }
      }

      //check if allowed for all coutries if no target until jet
      foreach($costs as $cost){
        $cost = explode(':', $cost);
        if($cost[0] == '00'){
          $cod_fee = $shipping ($cost[1] * (1 + $tax['tax_rate'] / 100));
          return  round($cod_fee, 2);
        }
      }
    }
  }

  /**
   * get shipping costs for shippingart
   *
   * @param string
   * @return string
   */
   private function getShipping($price = null, $offerWeight = null, $name, $country = 'DE'){
     $shipping = 0;
     $tax = 0;

     if($name == 'HARD'){
       return $this->getDelivery($price, $this->maxprice_value, $offerWeight);
     }

     $coutries = xtc_db_query("SELECT `configuration_value`
                  FROM `configuration`
                  WHERE `configuration_key` LIKE 'MODULE_SHIPPING_{$name}_ALLOWED';");

    $coutries = xtc_db_fetch_array($coutries);

     if($this->freeShipping === true){
       $price_float = (float)$price;
       $freeShippingValue_float = (float)$this->freeShippingValue;
       $allowed_country = $this->freeAllowedCoutry($country);
       if($allowed_country === true && $price_float >= $freeShippingValue_float){
         return 0;
       }
     }

    $tax = xtc_db_query("SELECT `configuration_value`
                  FROM `configuration`
                  WHERE `configuration_key` LIKE 'MODULE_SHIPPING_{$name}_TAX_CLASS';");

    $tax = xtc_db_fetch_array($tax);

    if($coutries['configuration_value'] == ''){
      return $this->getShippingCosts('DE', $tax['configuration_value'], $offerWeight, $name);
    }

    return $this->getShippingCosts($country, $tax['configuration_value'], $offerWeight, $name);
   }


  /**
   * get shippingcosts for shippingtype
   *
   * @param string
   * @param float
   * @param float
   * @param string
   *
   * @return string
   */
  private function getShippingCosts($allowed, $tax, $offerWeight, $name){

    $shipping = array();

    if($name == 'FLAT' || $name == 'ITEM'){

      $costs = xtc_db_query("SELECT `configuration_value`
                  FROM `configuration`
                  WHERE `configuration_key` LIKE 'MODULE_SHIPPING_{$name}_COST';");
      $result = xtc_db_fetch_array($costs);

      $costs_value = $this->shippingTax($result['configuration_value'],$tax);
      return $costs_value;
    }

    $countries = '';
    $att = '';

    if($name == 'TABLE'){
      $countries = xtc_db_query("SELECT `configuration_value`, `configuration_key`
              FROM `configuration`
              WHERE `configuration_key` LIKE 'MODULE_SHIPPING_{$name}_ALLOWED';");
      $att = "MODULE_SHIPPING_{$name}_COST";

    }else{
      $countries = xtc_db_query("SELECT `configuration_value`, `configuration_key`
              FROM `configuration`
              WHERE `configuration_key` LIKE 'MODULE_SHIPPING_{$name}_COUNTRIES%';");

      //is a coutry in coutry-set
      $is_in_set = false;
      $set = '';

      while ($coutry = xtc_db_fetch_array($countries)){

        $coutries = explode(',', $coutry['configuration_value']);
        $set = explode('_',$coutry['configuration_key']);

        foreach($coutries as $co){
          if($co == $allowed){
            $is_in_set = true;
            $set = explode('_',$coutry['configuration_key']);
            break;
          }
        }
        if($is_in_set === true){
          break;
        }
      }

      $att = "MODULE_SHIPPING_{$name}_COST_" . $set[4];
    }

    $costs =   xtc_db_query("SELECT `configuration_value`, `configuration_key`
              FROM `configuration`
              WHERE `configuration_key` LIKE '" . $att . "';");

    $costs = xtc_db_fetch_array($costs);

    $shipping = explode(',',$costs['configuration_value']);
    if(count($shipping) == 1){
      $shipping = explode(':',$shipping[0]);
      $count = count($shipping);
      $count--;
      $costs_value = $this->shippingTax($shipping[$count],$tax);
      return $costs_value;
    }else{
      $offerWeight = (float)$offerWeight;
      $costs_value = $this->shippingTax($this->shippingViaWeight($shipping,$offerWeight),$tax);
      return $costs_value;
    }

  }

  /**
   * get shippingcosts for product by weight
   *
   * @param array
   * @param float
   *
   * @return float
   */
  public function shippingViaWeight($shipping,$offerWeight){
    $shipping = array_reverse($shipping);
    $offerWeight = (float)$offerWeight;


    foreach ($shipping as $ship){
      $attributes = explode(':', $ship);

      $weight = (float)$attributes[0];

      if($weight <= $offerWeight){
        return $attributes[1];
      }
    }

    $count = count($shipping);

    $count--;

    $return = explode(':',$shipping[$count]);

    return $return[1];

  }


  /**
   * return shippingcost incl. tax
   *
   * @param float
   * @param float
   * @return float
   */
   public function shippingTax($costs, $tax = 0){

     if($tax == 0){
       return $costs;
     }

     $tax_rate = xtc_db_query("SELECT `tax_rate`
                FROM `tax_rates`
                WHERE `tax_class_id` = " . $tax . " AND `tax_zone_id` = 5");

     $tax_rate = xtc_db_fetch_array($tax_rate);

     $tax_rate = 1 + ($tax_rate['tax_rate'] / 100);

     return round($costs * $tax_rate,2);
   }


  /**
   * look if in this coutry free shipping allowed
   *
   * @param string
   *
   * @return boolean
   */
   public function freeAllowedCoutry($coutry){
     if($this->freeShippingAllowed == ''){return true;}
     if($this->freeShippingAllowed != ''){

       $allowed = explode(',',$this->freeShippingAllowed);

       foreach($allowed as $all){
         if($all == $coutry){
           return true;
         }
       }
     }
     return false;
   }


  /**
   * get paypal for flat delivery
   *
   * @param string
   * @param float
   * @param float
   *
   * @return float
   */
  public function getPaymentCosts($payment, $price, $shipping){
    $result = $shipping;
    $price = (float)$price;

    $payment = $this->payment[$payment];

    if($payment['max'] != ''){
      $payment['max'] = (float)$payment['max'];
      if($price >=  $payment['max']){
        return 'keine Zahlung per '. $payment['title'] . ' möglich';
      }
    }


    if($payment['no_fix'] != ''){
      $payment['no_fix'] = (float)$payment['no_fix'];

      if($payment['incl'] == 'yes'){
        $result = $result +(($price + $shipping) * $payment['no_fix'] / 100);
      }else{
        $result = $result +($price * $payment['no_fix'] / 100);
      }
    }

    if($payment['fix'] != ''){
      $payment['fix'] = (float)$payment['fix'];
      $result = $result + $payment['fix'];
    }

    return round ( $result, 2 );
  }

  /**
   * get the flat shipping costs
   *
   * @param float
   * @param float
   * @param float
   *
   * @return float
   */
  public function getDelivery($price, $max, $weight){

    // check if free
    if ( $max != '' ){

      if ( ( float ) $price >= ( float ) $max ){

        return '0';

      }
    }

    // check for shipping flat
     if ( MODULE_SHIPPING_TYPE == 'hard' ){

       return $this->shippingcosts;

     }

     $costs = explode ( ';', $this->shippingcosts );

     //value for calculate costs
     $value = '';

     if ( MODULE_SHIPPING_TYPE == 'weight' ){

       $value = $weight;

     }else{

       $value = $price;

     }
     // search for costs by value
     for ( $i = 0; $i < count ( $costs ); $i++ ){

       $co = explode ( ':', $costs [ $i ] );

       if ( ( count ( $costs ) - 1 ) == $i ){

         return $co [1];

       }

       if ( ( float ) $value <= ( float ) $co [0] ){

         return $co [1];

       }

     }

  }


  /**
   * Methode take vpe from db
   *
   * @param intcod_extrafee_fix
   * @return string
   */
   public function getVPE($product_vpe){
     $vpe = xtc_db_query("SELECT `products_vpe_name` FROM `products_vpe` WHERE `products_vpe_id` = " . $product_vpe . " AND `language_id` = 2;");
     $vpe = xtc_db_fetch_array($vpe);
    return $vpe['products_vpe_name'];

   }


  /**
   * Method prepares the text that is displayed at the detailed options on module_export.php
   */
    public function display() {

      $customers_statuses_array = xtc_get_customers_statuses();

      $campaign_array[] = array ('id' => '0', 'text' => 'no');
    $campaign_array[] = array ('id' => 'refID=' . CAMPAIGN . '&', 'text' => '94511215 (idealo)');

    // get campaign from db
    $campaign_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_CAMPAIGN' LIMIT 1");
    $campaign_db = xtc_db_fetch_array($campaign_query);
    $campaign = $campaign_db['configuration_value'];

    // get deliverfree from db
    $h_string = 'select configuration_value from `'. TABLE_CONFIGURATION . '` where `configuration_key` = \'MODULE_IDEALO_DELIVERYFREE\' LIMIT 1';
    $deliveryfree_query = xtc_db_query($h_string);
    $deliveryfree_db = xtc_db_fetch_array($deliveryfree_query);
    $deliveryfree = $deliveryfree_db['configuration_value'];

    // get deliverycosts from db
    $h_string = 'select configuration_value from `'. TABLE_CONFIGURATION . '` where `configuration_key` = \'MODULE_IDEALO_DELIVERYCOSTS\' LIMIT 1';
    $deliverycosts_query = xtc_db_query($h_string);
    $deliverycosts_db = xtc_db_fetch_array($deliverycosts_query);
    $deliverycosts = $deliverycosts_db['configuration_value'];

    // get livedata from db
    $h_string = 'select configuration_value from `'. TABLE_CONFIGURATION . '` where `configuration_key` = \'MODULE_IDEALO_LIVEDATA_SETTING\' LIMIT 1';
    $livedata_query = xtc_db_query($h_string);
    $livedata_db = xtc_db_fetch_array($livedata_query);
    $livedata = $livedata_db['configuration_value'];

    // get separator from db
    $separator_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_SEPARATOR' LIMIT 1");
    $separator_db = xtc_db_fetch_array($separator_query);

    $separator = $separator_db['configuration_value'];

    // get quoting character from db
    $quoting_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_QUOTING' LIMIT 1");
    $quoting_db = xtc_db_fetch_array($quoting_query);

    $quoting = $quoting_db['configuration_value'];

    // get codextrafee from db
    $codextrafee_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_CODEXTRAFEE' LIMIT 1");
    $codextrafee_db = xtc_db_fetch_array($codextrafee_query);

    $codextrafee = $codextrafee_db['configuration_value'];

    // get quoting character from db
    $language_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_LANGUAGE' LIMIT 1");
    $language_db = xtc_db_fetch_array($language_query);

    $language = $language_db['configuration_value'];

    // get free shipping comment from db
    if( $this->freeShipping === true && SHOWFREESHIPPINGLIMITCOMMENT === true ) {
      $freeshipping_input_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_FREESHIPPINGCOMMENT' LIMIT 1");
      $freeshipping_comment_db = xtc_db_fetch_array($freeshipping_input_query);

      $freeValue_Input_Text = ( $this->freeShippingValue != '' ) ? $freeshipping_comment_db['configuration_value'] : '';
      $freeshippingHTML = FREESHIPPINGCOMMENT . '<br>' . FREESHIPPINGCOMMENT_HINT . '<br>' . xtc_draw_input_field('freeshippingcomment_input', "{$freeValue_Input_Text}") . '<br><br>';
    } else {
      $freeshippingHTML = '';
    }

    // get shipping comment from db
    $shipping_input_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_SHIPPINGCOMMENT' LIMIT 1");
    $shipping_comment_db = xtc_db_fetch_array($shipping_input_query);
    $shipping_comment_text = ( $shipping_comment_db !== false ) ? $shipping_comment_db['configuration_value'] : '';


    $paypal_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_PAYPAL_ACTIVE' LIMIT 1");
    $paypal_db = xtc_db_fetch_array($paypal_query);

    $paypal_active = $paypal_db['configuration_value'];

    $paypal_array[] = array ('id' => 'yes', 'text' => 'ja',);
    $paypal_array[] = array ('id' => 'no', 'text' => 'nein',);

    // get fee values
    $paypalextrafee_fix_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_PAYPALEXTRAFEE_FIX' LIMIT 1");
    $paypalextrafee_fix_db = xtc_db_fetch_array($paypalextrafee_fix_query); // false if 'MODULE_IDEALO_PAYPALEXTRAFEE_FIX' doesn't exist
    $fix_value = ( empty($paypalextrafee_fix_db) )? '' : $paypalextrafee_fix_db['configuration_value'];

    $paypalextrafee_nofix_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_PAYPALEXTRAFEE_NOFIX' LIMIT 1");
    $paypalextrafee_nofix_db = xtc_db_fetch_array($paypalextrafee_nofix_query); // false if 'MODULE_IDEALO_PAYPALEXTRAFEE_FIX' doesn't exist
    $nofix_value = ( empty($paypalextrafee_nofix_db) )? '' : $paypalextrafee_nofix_db['configuration_value'];

    $paypalextrafee_input_nofix_scinclusive_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_PAYPALEXTRAFEE_NOFIX_SCINCLUSIVE' LIMIT 1");
    $paypalextrafee_input_nofix_scinclusive_db = xtc_db_fetch_array($paypalextrafee_input_nofix_scinclusive_query); // false if 'MODULE_IDEALO_PAYPALEXTRAFEE_NOFIX_SCINCLUSIVE' doesn't exist
    if( empty($paypalextrafee_input_nofix_scinclusive_db) || $paypalextrafee_input_nofix_scinclusive_db['configuration_value'] == "yes" ) {
      $nofix_scinclusive_yes = true;
      $nofix_scinclusive_no = false;
    } else {
      $nofix_scinclusive_yes = false;
      $nofix_scinclusive_no = true;
    }

    $paypalmaxpricelimit_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_PAYPALMAXPRICELIMIT' LIMIT 1");
    $paypalmaxpricelimit_db = xtc_db_fetch_array($paypalmaxpricelimit_query); // false if 'MODULE_IDEALO_PAYPALEXTRAFEE_FIX' doesn't exist
    $maxprice_value = ( empty($paypalmaxpricelimit_db) )? '' : $paypalmaxpricelimit_db['configuration_value'];




    $paypalextrafee = PAYPALEXTRAFEE.'<br>'.PAYPALEXTRAFEE_HINT.'<br>'.
              xtc_draw_pull_down_menu('paypal_active',$paypal_array, $paypal_active).'<br>'.
              xtc_draw_small_input_field('paypal_extrafee_fix', $fix_value).PAYPALEXTRAFEE_INPUT_FIX.'<br>'.
              xtc_draw_small_input_field('paypal_extrafee_nofix', $nofix_value).PAYPALEXTRAFEE_INPUT_NOFIX.'<br>' .
                          xtc_draw_radio_field('paypal_extrafee_nofix_inkl_sc', 'yes', $nofix_scinclusive_yes).PAYPALEXTRAFEE_RADIO_SCINCLUSIVE.'&nbsp;'.
                          xtc_draw_radio_field('paypal_extrafee_nofix_inkl_sc', 'no', $nofix_scinclusive_no).PAYPALEXTRAFEE_RADIO_SCNOTINCLUSIVE.'<br><br>'.
              PAYPAL_MAXPRICELIMIT.'<br>'.PAYPAL_MAXPRICEVALUE.'<br>'.
              xtc_draw_small_input_field('paypal_maxpricelimit', $maxprice_value) . PAYPAL_MAXPRICEEXAMPLE . '<br><br>';


    $cc_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_CC_ACTIVE' LIMIT 1");
    $cc_db = xtc_db_fetch_array($cc_query);

    $cc_active = $cc_db['configuration_value'];

    $cc_array[] = array ('id' => 'yes', 'text' => 'ja',);
    $cc_array[] = array ('id' => 'no', 'text' => 'nein',);


    // get fee values
    $ccextrafee_fix_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_CCEXTRAFEE_FIX' LIMIT 1");
    $ccextrafee_fix_db = xtc_db_fetch_array($ccextrafee_fix_query); // false if 'MODULE_IDEALO_CCEXTRAFEE_FIX' doesn't exist
    $fix_value = ( empty($ccextrafee_fix_db) )? '' : $ccextrafee_fix_db['configuration_value'];

    $ccextrafee_nofix_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_CCEXTRAFEE_NOFIX' LIMIT 1");
    $ccextrafee_nofix_db = xtc_db_fetch_array($ccextrafee_nofix_query); // false if 'MODULE_IDEALO_CCEXTRAFEE_NOFIX' doesn't exist
    $nofix_value = ( empty($ccextrafee_nofix_db) )? '' : $ccextrafee_nofix_db['configuration_value'];

    $ccextrafee_input_nofix_scinclusive_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_CCEXTRAFEE_NOFIX_SCINCLUSIVE' LIMIT 1");
    $ccextrafee_input_nofix_scinclusive_db = xtc_db_fetch_array($ccextrafee_input_nofix_scinclusive_query); // false if 'MODULE_IDEALO_PAYPALEXTRAFEE_NOFIX_SCINCLUSIVE' doesn't exist
    if( empty($ccextrafee_input_nofix_scinclusive_db) || $ccextrafee_input_nofix_scinclusive_db['configuration_value'] == "yes" ) {
      $nofix_scinclusive_yes = true;
      $nofix_scinclusive_no = false;
    } else {
      $nofix_scinclusive_yes = false;
      $nofix_scinclusive_no = true;
    }

    $ccmaxpricelimit_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_CCMAXPRICELIMIT' LIMIT 1");
    $ccmaxpricelimit_db = xtc_db_fetch_array($ccmaxpricelimit_query); // false if 'MODULE_IDEALO_CCMAXPRICELIMIT' doesn't exist
    $maxprice_value = ( empty($ccmaxpricelimit_db) )? '' : $ccmaxpricelimit_db['configuration_value'];

    $ccextrafee = CCEXTRAFEE.'<br>'.CCEXTRAFEE_HINT.'<br>'.
            xtc_draw_pull_down_menu('cc_active',$cc_array, $cc_active).'<br>'.
            xtc_draw_small_input_field('cc_extrafee_fix', $fix_value).CCEXTRAFEE_INPUT_FIX.'<br>'.
            xtc_draw_small_input_field('cc_extrafee_nofix', $nofix_value).CCEXTRAFEE_INPUT_NOFIX.'<br>' .
                      xtc_draw_radio_field('cc_extrafee_nofix_inkl_sc', 'yes', $nofix_scinclusive_yes).CCEXTRAFEE_RADIO_SCINCLUSIVE.'&nbsp;'.
                      xtc_draw_radio_field('cc_extrafee_nofix_inkl_sc', 'no', $nofix_scinclusive_no).CCEXTRAFEE_RADIO_SCNOTINCLUSIVE.'<br><br>'.
            CC_MAXPRICELIMIT.'<br>'.CC_MAXPRICEVALUE.'<br>'.
            xtc_draw_small_input_field('cc_maxpricelimit', $maxprice_value) . CC_MAXPRICEEXAMPLE . '<br><br>';




    $sofortueberweisung_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_SOFORTUEBERWEISUNG_ACTIVE' LIMIT 1");
    $sofortueberweisung_db = xtc_db_fetch_array($sofortueberweisung_query);

    $sofortueberweisung_active = $sofortueberweisung_db['configuration_value'];

    $sofortueberweisung_array[] = array ('id' => 'yes', 'text' => 'ja',);
    $sofortueberweisung_array[] = array ('id' => 'no', 'text' => 'nein',);

    // get fee values
    $sofortueberweisungextrafee_fix_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_SOFORTUEBERWEISUNGEXTRAFEE_FIX' LIMIT 1");
    $sofortueberweisungextrafee_fix_db = xtc_db_fetch_array($sofortueberweisungextrafee_fix_query); // false if 'MODULE_IDEALO_SOFORTUEBERWEISUNGEXTRAFEE_FIX' doesn't exist
    $fix_value = ( empty($sofortueberweisungextrafee_fix_db) )? '' : $sofortueberweisungextrafee_fix_db['configuration_value'];

    $sofortueberweisungextrafee_nofix_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_SOFORTUEBERWEISUNGEXTRAFEE_NOFIX' LIMIT 1");
    $sofortueberweisungextrafee_nofix_db = xtc_db_fetch_array($sofortueberweisungextrafee_nofix_query); // false if 'MOD__SOFORTUEBERWEISUNGEXTRAFEE_NOFIX' doesn't exist
    $nofix_value = ( empty($sofortueberweisungextrafee_nofix_db) )? '' : $sofortueberweisungextrafee_nofix_db['configuration_value'];

    $sofortueberweisungextrafee_input_nofix_scinclusive_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_SOFORTUEBERWEISUNGEXTRAFEE_NOFIX_SCINCLUSIVE' LIMIT 1");
    $sofortueberweisungextrafee_input_nofix_scinclusive_db = xtc_db_fetch_array($sofortueberweisungextrafee_input_nofix_scinclusive_query); // false if 'MODULE_IDEALO_PAYPALEXTRAFEE_NOFIX_SCINCLUSIVE' doesn't exist
    if( empty($sofortueberweisungextrafee_input_nofix_scinclusive_db) || $sofortueberweisungextrafee_input_nofix_scinclusive_db['configuration_value'] == "yes" ) {
      $nofix_scinclusive_yes = true;
      $nofix_scinclusive_no = false;
    } else {
      $nofix_scinclusive_yes = false;
      $nofix_scinclusive_no = true;
    }

    $sofortueberweisungmaxpricelimit_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_SOFORTUEBERWEISUNGMAXPRICELIMIT' LIMIT 1");
    $sofortueberweisungmaxpricelimit_db = xtc_db_fetch_array($sofortueberweisungmaxpricelimit_query); // false if 'MODULE_IDEALO_SOFORTUEBERWEISUNGMAXPRICELIMIT' doesn't exist
    $maxprice_value = ( empty($sofortueberweisungmaxpricelimit_db) )? '' : $sofortueberweisungmaxpricelimit_db['configuration_value'];

    $sofortueberweisungextrafee = SOFORTUEBERWEISUNGEXTRAFEE.'<br>'.SOFORTUEBERWEISUNGEXTRAFEE_HINT.'<br>'.
            xtc_draw_pull_down_menu('sofortueberweisung_active',$sofortueberweisung_array, $sofortueberweisung_active).'<br>'.
            xtc_draw_small_input_field('sofortueberweisung_extrafee_fix', $fix_value).SOFORTUEBERWEISUNGEXTRAFEE_INPUT_FIX.'<br>'.
            xtc_draw_small_input_field('sofortueberweisung_extrafee_nofix', $nofix_value).SOFORTUEBERWEISUNGEXTRAFEE_INPUT_NOFIX.'<br>' .
                      xtc_draw_radio_field('sofortueberweisung_extrafee_nofix_inkl_sc', 'yes', $nofix_scinclusive_yes).SOFORTUEBERWEISUNGEXTRAFEE_RADIO_SCINCLUSIVE.'&nbsp;'.
                      xtc_draw_radio_field('sofortueberweisung_extrafee_nofix_inkl_sc', 'no', $nofix_scinclusive_no).SOFORTUEBERWEISUNGEXTRAFEE_RADIO_SCNOTINCLUSIVE.'<br><br>'.
            SOFORTUEBERWEISUNG_MAXPRICELIMIT.'<br>'.SOFORTUEBERWEISUNG_MAXPRICEVALUE.'<br>'.
            xtc_draw_small_input_field('sofortueberweisung_maxpricelimit', $maxprice_value) . SOFORTUEBERWEISUNG_MAXPRICEEXAMPLE . '<br><br>';


    $article_filter_array[] = array ('id' => 'filter', 'text' => 'filtern',);
    $article_filter_array[] = array ('id' => 'export', 'text' => 'exportieren',);

    $article_filter_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_ARTICLE_FILTER' LIMIT 1");
    $article_filter_db = xtc_db_fetch_array($article_filter_query); // false if 'MODULE_IDEALO_ARTICLE_FILTER' doesn't exist
    $article_value = $article_filter_db['configuration_value'];

    $article_filter_value_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_ARTICLE_FILTER_VALUE' LIMIT 1");
    $article_filter_value_db = xtc_db_fetch_array($article_filter_value_query); // false if 'MODULE_IDEALO_ARTICLE_FILTER_VALUE' doesn't exist
    $article_filter_value = $article_filter_value_db['configuration_value'];

    $article_filter = ARTICLE_FILTER . '<br>' .
              ARTICLE_FILTER_SELECTION . '<br>'.
              xtc_draw_pull_down_menu('article_filter',$article_filter_array , $article_value).'<br><br>'.
              ARTICLE_FILTER_TEXT . '<br>' .
              xtc_draw_input_field('article_filter_value', $article_filter_value) . '<br><br>';

    $brand_filter_array[] = array ('id' => 'filter', 'text' => 'filtern',);
    $brand_filter_array[] = array ('id' => 'export', 'text' => 'exportieren',);

    $brand_filter_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_BRAND_FILTER' LIMIT 1");
    $brand_filter_db = xtc_db_fetch_array($brand_filter_query); // false if 'MODULE_IDEALO_BRAND_FILTER' doesn't exist
    $brand_value = $brand_filter_db['configuration_value'];

    $brand_filter_value_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_BRAND_FILTER_VALUE' LIMIT 1");
    $brand_filter_value_db = xtc_db_fetch_array($brand_filter_value_query); // false if 'MODULE_IDEALO_BRAND_FILTER_VALUE' doesn't exist
    $brand_filter_value = $brand_filter_value_db['configuration_value'];

    $brand_filter = BRAND_FILTER . '<br>' .
              BRAND_FILTER_SELECTION . '<br>'.
              xtc_draw_pull_down_menu('brand_filter',$brand_filter_array , $brand_value).'<br><br>'.
              BRAND_FILTER_TEXT . '<br>' .
              xtc_draw_input_field('brand_filter_value', $brand_filter_value) . '<br><br>';


    //cat filter
    $cat_filter_array[] = array ('id' => 'filter', 'text' => 'filtern',);
    $cat_filter_array[] = array ('id' => 'export', 'text' => 'exportieren',);

    $cat_filter_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_CAT_FILTER' LIMIT 1");
    $cat_filter_db = xtc_db_fetch_array($cat_filter_query); // false if 'MODULE_IDEALO_CAT_FILTER' doesn't exist
    $cat_value = $cat_filter_db['configuration_value'];

    $cat_filter_value_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_CAT_FILTER_VALUE' LIMIT 1");
    $cat_filter_value_db = xtc_db_fetch_array($cat_filter_value_query); // false if 'MODULE_IDEALO_cat_FILTER_VALUE' doesn't exist
    $cat_filter_value = $cat_filter_value_db['configuration_value'];

    $cat_filter = CAT_FILTER . '<br>' .
              CAT_FILTER_SELECTION . '<br>'.
              xtc_draw_pull_down_menu('cat_filter',$cat_filter_array , $cat_value).'<br><br>'.
              CAT_FILTER_TEXT . '<br>' .
              xtc_draw_input_field('cat_filter_value', $cat_filter_value) . '<br><br>';


    $cod_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_COD_ACTIVE' LIMIT 1");
    $cod_db = xtc_db_fetch_array($cod_query);

    $cod_active = $cod_db['configuration_value'];

    $cod_array[] = array ('id' => 'yes', 'text' => 'ja',);
    $cod_array[] = array ('id' => 'no', 'text' => 'nein',);


    $codextrafee = CODEXTRAFEE.'<br>'.CODEXTRAFEE_HINT.'<br>'.
            xtc_draw_pull_down_menu('cod_active',$cod_array, $cod_active).'<br>'.
            xtc_draw_small_input_field('cod_extrafee_fix', $codextrafee) .
            CODEEXTRAFEE_BSP . '<br><br>';



    /*
    CODEXTRAFEE . '<br>' .
                xtc_draw_pull_down_menu('brand_filter',$cod_array , $cod_value).'<br><br>'.
                  CODEXTRAFEE_HINT . '<br>' .
                  xtc_draw_small_input_field('codextrafee_input', $codextrafee) .
                  CODEEXTRAFEE_BSP. '<br><br>' .

    */




    $shipparray = array(array('id' => '', 'text' => TEXT_NONE));
    $shipparray[] = array ('id' => 'HARD', 'text' => 'feste Versandkosten');
    foreach($this->shipping_type as $ship){
      $shipparray[] = array ('id' => $ship['name'], 'text' => $ship['name']);
    }

    // get shipping from db
    $shipping_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_SHIPPING' LIMIT 1");
    $shipping_db = xtc_db_fetch_array($shipping_query);

    $shipping = $shipping_db['configuration_value'];


    $module_shipping_type_array [] = array( 'id' => 'hard', 'text' => 'pauschal' );
    $module_shipping_type_array [] = array( 'id' => 'weight', 'text' => 'nach Gewicht' );
    $module_shipping_type_array [] = array( 'id' => 'price', 'text' => 'nach Preise' );

    $module_shipping_type_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_MODULE_SHIPPING_TYPE' LIMIT 1");
    $module_shipping_type_db = xtc_db_fetch_array($module_shipping_type_query); // false if 'MODULE_IDEALO_MODULE_SHIPPING_TYPE' doesn't exist
    $module_shipping_type_value = $module_shipping_type_db['configuration_value'];

      return array('text' =>
                  '<br>' . FIELDSEPARATOR . '<br>' .
                  FIELDSEPARATOR_HINT_IDEALO . '<br>' .
                  xtc_draw_small_input_field('separator_input', $separator) . '<br><br>' .
                  QUOTING . '<br>' .
                  QUOTING_HINT . '<br><br>' .
                  xtc_draw_small_input_field('quoting_input', $quoting) . '<br><br>' .

                  SHIPPING.'<br>'.
                              SHIPPING_DESC.'<br><br>'.
                              SHIPPING_ALLOWED.'<br><br>'.
                              SHIPPING_NOT_ALLOWED . '<br>' .
                              xtc_draw_pull_down_menu('shipping',$shipparray, $shipping).'<br><br>'.

                  DELIVERYTEXT . '<br>' .
                  DILEVERYCOSTS .'<br>' .
                  xtc_draw_input_field('dilevery_costs', $deliverycosts) . '<br>'.
                DELIVERYBSP . '<br><br>' .
                DILEVERYCOSTS_TYPE . '<br>' .
                xtc_draw_pull_down_menu('module_shipping_type', $module_shipping_type_array, $module_shipping_type_value).'<br><br>' .
                DELIVERYFREETEXT . '<br>' .
                DELIVERYFREE . '<br>' .
                xtc_draw_small_input_field('dilevery_free', $deliveryfree) .
                DELIVERYFREEBSP . '<br><br>' .
                $codextrafee .
                $paypalextrafee .
                $ccextrafee .
                $sofortueberweisungextrafee .
                SHIPPINGCOMMENT . '<br>' .
                SHIPPINGCOMMENT_HINT . '<br>' .
                xtc_draw_input_field('shippingcomment_input', $shipping_comment_text) . '<br><br>'.
//                $freeshippingHTML .
                $article_filter .
                $brand_filter .
                $cat_filter .
                              CAMPAIGNS.'<br>'.
                              CAMPAIGNS_DESC.'<br>'.
                              xtc_draw_pull_down_menu( 'campaign', $campaign_array, $campaign ).'<br>'.
                              EXPORT_TYPE.'<br>'.
                              EXPORT.'<br>'.
                              $this->liveExist($livedata).
                              '<br>' . xtc_button(BUTTON_EXPORT) .
                              xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=idealo'))
                              );


    }


    /**
   * Methode create a button to show the Link to the dynamic module
   */
  public function link_button($value, $type='button', $parameter) {
       return '<input type="'.$type.'" class="button" onClick="javascript:alert(\''.$parameter.'\')" value="' . $value . '">';
  }



    /**
     /**
     * Methode check if idealo_dynamic-Module is installed
     * If idealo_dynamic-Module is installed methode show radiobuttons to make a chois (csv make self or liveupdate by idealo) and the button to the URL to the dynamic module.
     *
     */
    public function liveExist($livedata){
        return xtc_draw_radio_field('export', 'no',false).EXPORT_NO.'<br>'.xtc_draw_radio_field('export', 'yes',true).EXPORT_YES.'<br>';
    }

    public function check() {
      if (!isset($this->_check)) {
        $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }
      return $this->_check;
    }

  /**
   * Method installs a module in module_export.php
   */
    public function install() {
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_IDEALO_FILE', 'idealo.csv',  '6', '1', '', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_IDEALO_STATUS', 'True',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    }

  /**
   * Method removes a module
   */
    public function remove() {
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key LIKE '%IDEALO%'");
    }

    public function keys() {
      return array('MODULE_IDEALO_STATUS','MODULE_IDEALO_FILE');
    }
  }
?>