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

define('HEADING_TITLE', 'Sonderangebote');

define('TABLE_HEADING_PRODUCTS', 'Artikel');
define('TABLE_HEADING_PRODUCTS_PRICE', 'Artikelpreis');
define('TABLE_HEADING_STATUS', 'Status');
define('TABLE_HEADING_ACTION', 'Aktion');

define('TEXT_SPECIALS_PRODUCT', 'Artikel:');
define('TEXT_SPECIALS_SPECIAL_PRICE', 'Angebotspreis:');
define('TEXT_SPECIALS_SPECIAL_QUANTITY', 'Anzahl:');
// BOF - Tomcraft - 2009-11-06 - Use "iso 8601" for the date format
//define('TEXT_SPECIALS_EXPIRES_DATE', 'G&uuml;ltig bis:<br /><small>(dd.mm.yyyy)</small>'
define('TEXT_SPECIALS_EXPIRES_DATE', 'G&uuml;ltig bis: <small>(JJJJ-MM-TT)</small>');
// EOF - Tomcraft - 2009-11-06 - Use "iso 8601" for the date format
// BOF - Tomcraft - 2009-11-06 - extended description
//define('TEXT_SPECIALS_PRICE_TIP', '<strong>Bemerkung:</strong><ul><li>Sie k&ouml;nnen im Feld Angebotspreis auch prozentuale Werte angeben, z.B.: <strong>20%</strong></li><li>Wenn Sie einen neuen Preis eingeben, m&uuml;ssen die Nachkommastellen mit einem \'.\' getrennt werden, z.B.: <strong>49.99</strong></li><li>Lassen Sie das Feld <strong>\'G&uuml;ltig bis\'</strong> leer, wenn der Angebotspreis zeitlich unbegrenzt gelten soll.</li></ul>');
define('TEXT_SPECIALS_PRICE_TIP', '<strong>Bemerkung:</strong><br>Sie k&ouml;nnen im Feld Angebotspreis auch prozentuale Werte angeben, z.B.: <strong>20%</strong><br>Wenn Sie einen neuen Preis eingeben, m&uuml;ssen die Nachkommastellen mit einem \'.\' getrennt werden, z.B.: <strong>49.99</strong><br>Lassen Sie das Feld <strong>\'G&uuml;ltig bis\'</strong> leer, wenn der Angebotspreis zeitlich unbegrenzt gelten soll.<br>Im Feld <strong>Anzahl</strong> k&ouml;nnen Sie die St&uuml;ckzahl eingeben, f&uuml;r die das Angebot gelten soll. Lassen Sie das Feld leer, wenn Sie die Anzahl nicht begrenzen wollen.');
// EOF - Tomcraft - 2009-11-06 - extended description

define('TEXT_INFO_DATE_ADDED', 'hinzugef&uuml;gt am:');
define('TEXT_INFO_LAST_MODIFIED', 'letzte &Auml;nderung:');
define('TEXT_INFO_NEW_PRICE', 'neuer Preis:');
define('TEXT_INFO_ORIGINAL_PRICE', 'alter Preis:');
define('TEXT_INFO_PERCENTAGE', 'Prozent:');
define('TEXT_INFO_EXPIRES_DATE', 'G&uuml;ltig bis:');
define('TEXT_INFO_STATUS_CHANGE', 'Status ge&auml;ndert:');

define('TEXT_INFO_HEADING_DELETE_SPECIALS', 'Sonderangebot l&ouml;schen');
define('TEXT_INFO_DELETE_INTRO', 'Sind Sie sicher, dass Sie das Sonderangebot l&ouml;schen m&ouml;chten?');

define('TEXT_IMAGE_NONEXISTENT','Kein Bild verf&uuml;gbar!');
?>