<?php

/*
	Idealo, Export-Modul

	(c) Idealo 2012,
	
	Please note that this extension is provided as is and without any warranty. It is recommended to always backup your installation prior to use. Use at your own risk.
	
	Extended by
	
	Christoph Zurek (Idealo Internet GmbH, http://www.idealo.de)
*/


$version_number_idealo = '1.3.0';
$version_date = '27.03.2013';
$idealo_module_modified = 'no';

define( 'TEXT_IDEALO_REALTIME_MODIFIED', $idealo_module_modified );

define( 'VERSION_TEXT_01', 'Idealo - Realtime Exportmodul V ' );
define( 'VERSION_TEXT_02', $version_number_idealo );
define( 'VERSION_TEXT_03', ' fuer xt-Systeme vom ' );
define( 'VERSION_TEXT_04', $version_date );
define( 'TEXT_NEW_IDEALO_MODULE_01', 'Die Version ' );
define( 'TEXT_NEW_IDEALO_MODULE_02', ' des Moduls ist auf Idealo verf&uuml;gbar.' );
define( 'TEXT_IDEALO_CSV_TEAM', '<br>Da das installierte Modul f&uuml;r Ihr Shopsystem modifiziert wurde, wenden Sie sich f&uuml;r ein Update bitte an <a href="mailto:csv@idealo.de">csv@idealo.de.</a>');

define( 'MODULE_VERSION_TEXT', VERSION_TEXT_01 . $version_number_idealo . VERSION_TEXT_03 . $version_date );
$version_location_idealo = 'http://ftp.idealo.de/software/modules/version.xml';
$new_idealo_version_text = '';

 if( @file_get_contents ( $version_location_idealo ) !== false ) {
	$xml_idealo = simplexml_load_file ( $version_location_idealo );
	$version_idealo = ( string ) $xml_idealo->realtime->xt_systeme;

	$idealo_module_download = ( string )$xml_idealo->download->url;

	$old_version_idealo = explode ( '.', $version_number_idealo );
	$new_version_idealo = explode ( '.', $version_idealo );

   	   $idealo_version_text_modified = TEXT_NEW_IDEALO_MODULE_01 . $version_idealo . TEXT_NEW_IDEALO_MODULE_02 . ' ' . TEXT_IDEALO_CSV_TEAM;
	   $idealo_version_text_no_modified = TEXT_NEW_IDEALO_MODULE_01 . $version_idealo . TEXT_NEW_IDEALO_MODULE_02 . ' <a href="' . $idealo_module_download . '" target="_newtab"><b>zur Download-Seite</b></a>'; //DokuMan - 2012-08-21 - removed "blink"-tag
	   
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
	   								$old_version_idealo [0] == $new_version_idealo [0]
	   								and
	   								$old_version_idealo [1] == $new_version_idealo [1]
	   								and
	   								$old_version_idealo [2] < $new_version_idealo [2]
	   									
	   						)
	   				){
						if ( TEXT_IDEALO_REALTIME_MODIFIED == 'no' ){
							
							$new_idealo_version_text = $idealo_version_text_no_modified;
							
						}else{
							
							$new_idealo_version_text = $idealo_version_text_modified;
							
						}
						
					}
					
				}
	 }
   
define( 'NEW_IDEALO_REALTIME_VERSION_TEXT', $new_idealo_version_text );
define( 'MODULE_IDEALO_REALTIME_TEXT_DESCRIPTION', 'Realtime - Idealo' );
define( 'MODULE_IDEALO_REALTIME_TEXT_TITLE',  '<img src = "http://www.idealo.de/pics/common/logoidealo_blue_s.gif"> - Realtime');


define('MODULE_IDEALO_REALTIME_FILE_TITLE' , '<hr noshade>User');
define('MODULE_IDEALO_REALTIME_FILE_DESC' , 'Ihr Benutzername fuer den Webservice. Falls Sie noch nicht f&uuml;r die Nutzung des Webservice freigeschaltet sind, wenden Sie sich bitte an csv@idealo.de' );

define('IDEALO_REALTIME_TESTMODE_ACTIVE', '<b>Testmodus</b><br>Wenn aktiv, werden keine Logindaten im Modul ben&ouml;tigt. ');

define('URL', '<b>URL fuer Webservice</b>');
define('URL_HINT', 'Url fuer Request');
define('SHOP_ID','<b>ShopID</b>');
define('SHOP_ID_HINT','ID Ihres Shops bei Idealo (wird Ihnen bei Anmeldung mitgeteilt)');
define('PASSWORT', '<b>Passwort</b>');
define('PASSWORT_HINT', 'Ihr Passwort. Falls Sie noch nicht f&uuml;r die Nutzung des Webservice freigeschaltet sind, wenden Sie sich bitte an csv@idealo.de');
define('PAGESIZE', '<b>Pagesize</b>');
define('PAGESIZE_HINT', 'Anzahl der Angebote, welche pro Aufruf gesendet werden sollen. Es werden maximal 100 Angebote pro Aufruf zurueckgeliefert, groessere Werte fuer den pageSize-Parameter sind nicht zulaessig. Empfolen sind 100');
define('CERTIFICATE_TEXT', '<b>Zertifikatspr&uuml;ng</b>');
define('CERTIFICATE_TEXT_DESCRIPTION', 'Bei Deaktivierung der Zertifikatspr&uuml;fung, werden Daten an Idealo geschickt, ohne dass das Zertifikat &uuml;berpr&uuml;ft wird.');

define('PAYMENT', '<hr noshade><b>Zahlungsarten</b>');

define('CODEXTRAFEE_HINT', 'Die Geb&uuml;hren die zus&auml;tzlich vom Zustelldienst f&uuml;r Nachnahme verlangt werden.<br />Beispiel: "2" wenn die Zustellergeb&uuml;hren 2 Euro betragen');
define('PAYMENTEXTRAFEE', 'Zusatzgeb&uuml;hren bei');
define('PAYMENTEXTRAFEE_HINT', 'Die Geb&uuml;hren die zus&auml;tzlich zu den normalen Versandkosten anfallen.');
define('PAYMENTEXTRAFEE_INPUT_FIX', 'EUR fixe Geb&uuml;hren Bsp.: 5.00');
define('PAYMENTEXTRAFEE_INPUT_NOFIX', '% vom Warenwert Bsp.: 3.5');
define('PAYMENTEXTRAFEE_RADIO_SCINCLUSIVE', '<b>inkl.</b> VK');
define('PAYMENTEXTRAFEE_RADIO_SCNOTINCLUSIVE', '<b>exkl.</b> VK');


define('SHIPPINGCOMMENT', '<hr noshade><b>Versandkommentar</b>');
define('SHIPPINGCOMMENT_HINT', 'Max. 100 Zeichen');

define('CURRENCY','EUR');
define('SHIPPING','<hr noshade><b>Versandländer / Versandkosten</b>');

define('EXPORT','Bitte den Sicherungsprozess AUF KEINEN FALL unterbrechen. Dieser kann einige Minuten in Anspruch nehmen.');
define('CAMPAIGNS','<hr noshade><b>Kampagnen:</b>');
define('CAMPAIGNS_DESC','Mit Kampagne zur Nachverfolgung verbinden.');
define('TEXT_WARANTY_IDEALO_REALTIME', '* Idealo &uuml;bernimmt keine Haftung f&uuml;r den einwandfreien Betrieb, die Funktionalit&auml;t des Moduls, der Sicherheit der &uuml;bertragenen Daten und Haftung fuer etwaige Sch&auml;den. Idealo kann den Service der Module jederzeit einstellen. Mit der Nutzung der Module stimmt der Kooperationspartner dem vorgenannten Haftungsausschluss von Idealo zu.');
define('DATE_FORMAT_EXPORT', '%d.%m.%Y'); 
define('DISPLAY_PRICE_WITH_TAX','true');
define('COMMENTLENGTH', 100);
define('COSTUMER_STATUS', '1');
define('EXPORT_TEXT', '<hr noshade><b>Artikel bei Idealo aktualisieren?</b>');
define('REAL_TEXT', 'Url f&uuml;r Echtzeitaktualisierung (bitte an Idealo schicken):');
define('TESTFILE', 'Testdatei erstellen?');

define('SHIPPING_TEXT_01', '<b>Versand nach ');
define('SHIPPING_TEXT_02', '</b>Geben Sie die Versandkosten ein.');
define('SHIPPING_TEXT_03', 'z.B. 5.95');
define('SHIPPING_TEXT_04', 'Geben Sie an, ab welchem Warenwert der Versand kosten los erfolgt.');
define('SHIPPING_TEXT_05', 'z.B. 100');
define('SHIPPING_TEXT_06', 'W&auml;hlen Sie aus, wie die Versandkosten berechnet werden.');
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
define('CAMPAIGN', '94511215'); 
define('IDEALO_REALTIME_LINK', 'export/idealo_realtime/idealo_realtime.php');

define('IDEALO_REALTIME', 'export/idealo_realtime/');
define('IDEALO_TEXT_MISSING_CONFIG', '<font color="#FF0000"><b>* bitte einen Wert eintragen!</b></font>');
define('IDEALO_TEXT_MISSING_SHIPPING', '<font color="#FF0000"><b>* Versandkosten f&uuml;r min. ein Land aktivieren!</b></font>');
define('IDEALO_TEXT_MISSING_PAYMENT', '<font color="#FF0000"><b>* f&uuml;r jedes aktiviert Versandland min. eine Zahlungsart aktivieren!</b></font>');
define('IDEALO_TEXT_MISSING_COSTS_IDEALO_DE', '<font color="#FF0000"><b>* geben Sie die Versandkosten ein!<br></b></font>');
define ( 'IDEALO_REALTIME_CRON_TABLE', 'idealo_realtime_cron' );
define ( 'IDEALO_REQUEST_ERROR_TABLE', 'idealo_realtime_failed_request' );
$_realtime_cat_filter_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_CAT_FILTER' LIMIT 1");
$_realtime_cat_filter_db = xtc_db_fetch_array($_realtime_cat_filter_query);
$_realtime_cat_filter = $_realtime_cat_filter_db [ 'configuration_value' ];
$_realtime_cat_filter_value_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_CAT_FILTER_VALUE' LIMIT 1");
$_realtime_cat_filter_value_db = xtc_db_fetch_array($_realtime_cat_filter_value_query);
$_realtime_cat_filter_value = $_realtime_cat_filter_value_db [ 'configuration_value' ];
$_realtime_brand_filter_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_BRAND_FILTER' LIMIT 1");
$_realtime_brand_filter_db = xtc_db_fetch_array($_realtime_brand_filter_query);
$_realtime_brand_filter = $_realtime_brand_filter_db [ 'configuration_value' ];
$_realtime_brand_filter_value_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_BRAND_FILTER_VALUE' LIMIT 1");
$_realtime_brand_filter_value_db = xtc_db_fetch_array($_realtime_brand_filter_value_query);
$_realtime_brand_filter_value = $_realtime_brand_filter_value_db [ 'configuration_value' ];
$_realtime_article_filter_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_ARTICLE_FILTER' LIMIT 1");
$_realtime_article_filter_db = xtc_db_fetch_array($_realtime_article_filter_query);
$_realtime_article_filter = $_realtime_article_filter_db [ 'configuration_value' ];
$_realtime_article_filter_value_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_ARTICLE_FILTER_VALUE' LIMIT 1");
$_realtime_article_filter_value_db = xtc_db_fetch_array($_realtime_article_filter_value_query);
$_realtime_article_filter_value = $_realtime_article_filter_value_db [ 'configuration_value' ];


define('IDEALO_REALTIME_ARTICLE_FILTER_VALUE', $_realtime_article_filter_value);
define('IDEALO_REALTIME_ARTICLE_EXPORT', $_realtime_article_filter);
define('IDEALO_REALTIME_ARTICLE_FILTER', '<hr noshade><b>Filter nach Artikelnummer</b>');
define('IDEALO_REALTIME_ARTICLE_FILTER_SELECTION', 'W&auml;hlen Sie aus, ob die Artikel gefiltert, oder "nur diese" exportiert werden sollen.');
define('IDEALO_REALTIME_ARTICLE_FILTER_TEXT', 'Geben Sie hier die Artikelnummern ein. Trennen Sie die Artikelnummern mit einem Semikolon ";".');
define('IDEALO_REALTIME_BRAND_FILTER_VALUE', $_realtime_brand_filter_value);
define('IDEALO_REALTIME_BRAND_EXPORT', $_realtime_brand_filter);
define('IDEALO_REALTIME_BRAND_FILTER', '<b>Filter nach Hersteller</b>');
define('IDEALO_REALTIME_BRAND_FILTER_SELECTION', 'W&auml;hlen Sie aus, ob die Hersteller gefiltert, oder "nur diese" exportiert werden sollen.');
define('IDEALO_REALTIME_BRAND_FILTER_TEXT', 'Geben Sie hier die Hersteller ein. Trennen Sie die Hersteller mit einem Semikolon ";".');
define('IDEALO_REALTIME_CAT_FILTER_VALUE', $_realtime_cat_filter_value);
define('IDEALO_REALTIME_CAT_EXPORT', $_realtime_cat_filter);
define('IDEALO_REALTIME_CAT_FILTER', '<b>Filter nach Kategorien</b>');
define('IDEALO_REALTIME_CAT_FILTER_SELECTION', 'W&auml;hlen Sie aus, ob die Kategorien gefiltert, oder &quot;nur diese&quot; exportiert werden sollen.');
define('IDEALO_REALTIME_CAT_FILTER_TEXT', 'Geben Sie hier die Kategorien ein. Trennen Sie die Kategorien mit einem Semikolon &quot;;&quot;. Es gen&uuml;gt, einen Teilpfad der Kategorie anzugeben. wird der Teilpfad in der Kategorie eines Artikels gefunden, wird dieser gefiltert. Z.B. Filter &quot;TV&quot;: alle Kategorien mit &quot;TV&quot; als Teilpfad (z.B. TV->LCD und TV->Plasma) werden gefiltert. Filter &quot;LCD&quot;: alle Artikel mit dem Teilpfad &quot;LCD&quot; werden gefiltert. &quot;TV->Plasma&quot; wird exportiert.');

define('IDEALO_REALTIME_MIN_ORDER_TITLE', '<hr noshade><b>Mindestbestellwert</b>');
define('IDEALO_REALTIME_MIN_ORDER', 'Mindestbestellwert: ');
define('IDEALO_REALTIME_MIN_EXTRA_COSTS', '<b>Mindermengenzuschlag</b>');
define('IDEALO_REALTIME_MIN_ORDER_EXTRA_PRICE', ' EUR Mindermengenzuschlag unter ');
define('IDEALO_REALTIME_SUM', ' EUR Warenwert, bereits in den Versandkosten enthalten.');

define('IDEALO_REALTIME_MIN_ORDER_VALUE', '<b>Mindestbestellwert</b>');
define('IDEALO_REALTIME_MIN_ORDER_TEXT', 'Tragen Sie Ihren Mindestbestellwert ein. Verwenden Sie als Dezimaltrenner das Punktzeichen, z.B. 5.00. Die betreffenden Angebote erhalten automatisch einen entsprechenden Versandkommentar.');
define('IDEALO_REALTIME_MIN_ORDER_BORDER_TEXT', 'Tragen Sie die H&ouml;he des Zuschlages ein. Verwenden Sie als Dezimaltrenner das Punktzeichen, z.B. 2.99. ');
define('IDEALO_REALTIME_MIN_ORDER_BORDER_VALUE', '<b>Mindermengengrenze</b>');
define('IDEALO_REALTIME_MIN_ORDER_PRICE_TEXT', 'Tragen Sie den Betrag ein, ab welchem der Mindermengenzuschlag nicht mehr anfällt. Verwenden Sie als Dezimaltrenner das Punktzeichen, z.B. 49.95');

define('IDEALO_REALTIME_EXPORT_TEXT', '<hr noshade><b>Aktualisieren</b>');
 
 
 
?>
