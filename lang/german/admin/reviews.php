<?php
/* --------------------------------------------------------------
   $Id: reviews.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(reviews.php,v 1.6 2002/02/06); www.oscommerce.com 
   (c) 2003	 nextcommerce (reviews.php,v 1.4 2003/08/14); www.nextcommerce.org

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

define('HEADING_TITLE', 'Artikelbewertungen');

define('TABLE_HEADING_PRODUCTS', 'Artikel');
define('TABLE_HEADING_RATING', 'Bewertung');
define('TABLE_HEADING_DATE_ADDED', 'hinzugef&uuml;gt am');
define('TABLE_HEADING_ACTION', 'Aktion');

define('ENTRY_PRODUCT', 'Artikel:');
define('ENTRY_FROM', 'von:');
define('ENTRY_DATE', 'Datum:');
define('ENTRY_REVIEW', 'Bewertung:');
define('ENTRY_REVIEW_TEXT', '<small><font color="#ff0000"><b>HINWEIS:</b></font></small>&nbsp;HTML wird nicht konvertiert!&nbsp;');
define('ENTRY_RATING', 'Bewertung:');

define('TEXT_INFO_DELETE_REVIEW_INTRO', 'Sind Sie sicher, dass Sie diese Bewertung l&ouml;schen m&ouml;chten?');

define('TEXT_INFO_DATE_ADDED', 'hinzugef&uuml;gt am:');
define('TEXT_INFO_LAST_MODIFIED', 'letzte &Auml;nderung:');
//BOF - DokuMan - 2010-02-15 - Change wrong constant-name
//define('TEXT_INFO_IMAGE_NONEXISTENT', 'BILD EXISTIERT NICHT');
define('TEXT_IMAGE_NONEXISTENT', 'BILD EXISTIERT NICHT');
//EOF - DokuMan - 2010-02-15 - Change wrong constant-name
define('TEXT_INFO_REVIEW_AUTHOR', 'geschrieben von:');
define('TEXT_INFO_REVIEW_RATING', 'Bewertung:');
define('TEXT_INFO_REVIEW_READ', 'gelesen :');
define('TEXT_INFO_REVIEW_SIZE', 'Gr&ouml;sse:');
define('TEXT_INFO_PRODUCTS_AVERAGE_RATING', 'durchschnittl. Wertung:');

define('TEXT_OF_5_STARS', '%s von 5 Sternen!');
define('TEXT_GOOD', '<small><font color="#ff0000"><b>GUT</b></font></small>');
define('TEXT_BAD', '<small><font color="#ff0000"><b>SCHLECHT</b></font></small>');
define('TEXT_INFO_HEADING_DELETE_REVIEW', 'Bewertung l&ouml;schen');
?>