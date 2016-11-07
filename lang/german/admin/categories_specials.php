<?php
/* --------------------------------------------------------------
   $Id: specials.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(specials.php,v 1.10 2002/01/31); www.oscommerce.com 
   (c) 2003	 nextcommerce (specials.php,v 1.4 2003/08/14); www.nextcommerce.org

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

define('SPECIALS_TITLE', 'Sonderangebot ');

define('TEXT_SPECIALS_PRODUCT', 'Artikel:');
define('TEXT_SPECIALS_SPECIAL_PRICE', 'Angebotspreis:');
define('TEXT_SPECIALS_SPECIAL_QUANTITY', 'Anzahl:');
define('TEXT_SPECIALS_START_DATE', 'G&uuml;ltig ab: <small>(JJJJ-MM-TT)</small>');
define('TEXT_SPECIALS_EXPIRES_DATE', 'G&uuml;ltig bis: <small>(JJJJ-MM-TT)</small>');

define('TEXT_INFO_DATE_ADDED', 'hinzugef&uuml;gt am:');
define('TEXT_INFO_LAST_MODIFIED', 'letzte &Auml;nderung:');
define('TEXT_INFO_NEW_PRICE', 'neuer Preis:');
define('TEXT_INFO_ORIGINAL_PRICE', 'alter Preis:');
define('TEXT_INFO_PERCENTAGE', 'Prozent:');
define('TEXT_INFO_START_DATE', 'G&uuml;ltig ab:');
define('TEXT_INFO_EXPIRES_DATE', 'G&uuml;ltig bis:');

define('TEXT_INFO_HEADING_DELETE_SPECIALS', 'Sonderangebot l&ouml;schen');
define('TEXT_INFO_DELETE_INTRO', 'Sind Sie sicher, dass Sie das Sonderangebot l&ouml;schen m&ouml;chten?');

define('TEXT_SPECIALS_NO_PID', 'Der Artikel muss zuerst gespeichert werden, ansonsten kann das Sonderangebot nicht korrekt angelegt werden!');

define('TEXT_CATSPECIALS_START_DATE_TT', 'Geben Sie das Datum an, ab wann der Angebotspreis gelten soll.<br>');
define('TEXT_CATSPECIALS_EXPIRES_DATE_TT', 'Lassen Sie das Feld <strong>G&uuml;ltig bis</strong> leer, wenn der Angebotspreis zeitlich unbegrenzt gelten soll.<br>');
define('TEXT_CATSPECIALS_SPECIAL_QUANTITY_TT', 'Im Feld <strong>Anzahl</strong> k&ouml;nnen Sie die St&uuml;ckzahl eingeben, f&uuml;r die das Angebot gelten soll.<br>Unter "Konfiguration" -> "Lagerverwaltungs Optionen" -> "&Uuml;berpr&uuml;fen der Sonderangebote" k&ouml;nnen Sie entscheiden, ob der Bestand von Sonderangeboten &uuml;berpr&uuml;ft werden soll.');
define('TEXT_CATSPECIALS_SPECIAL_PRICE_TT', 'Sie k&ouml;nnen im Feld Angebotspreis auch prozentuale Werte angeben, z.B.: <strong>20%</strong><br>Wenn Sie einen neuen Preis eingeben, m&uuml;ssen die Nachkommastellen mit einem \'.\' getrennt werden, z.B.: <strong>49.99</strong>');
?>