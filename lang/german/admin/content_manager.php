<?php
/* --------------------------------------------------------------
   $Id: content_manager.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on:
   (c) 2003	 nextcommerce (content_manager.php,v 1.8 2003/08/25); www.nextcommerce.org
   
   Released under the GNU General Public License 
   --------------------------------------------------------------*/
   
 define('HEADING_TITLE','Content Manager');
 define('HEADING_CONTENT','Seiten Content');
 define('HEADING_PRODUCTS_CONTENT','Artikel Content');
 define('TABLE_HEADING_CONTENT_ID','Link ID');
 define('TABLE_HEADING_CONTENT_TITLE','Titel');
 define('TABLE_HEADING_CONTENT_FILE','Datei');
 define('TABLE_HEADING_CONTENT_STATUS','In Box sichtbar');
 define('TABLE_HEADING_CONTENT_BOX','Box');
 define('TABLE_HEADING_PRODUCTS_ID','ID');
 define('TABLE_HEADING_PRODUCTS','Artikel');
 define('TABLE_HEADING_PRODUCTS_CONTENT_ID','ID');
 define('TABLE_HEADING_LANGUAGE','Sprache');
 define('TABLE_HEADING_CONTENT_NAME','Name/Dateiname');
 define('TABLE_HEADING_CONTENT_LINK','Link');
 define('TABLE_HEADING_CONTENT_HITS','Hits');
 define('TABLE_HEADING_CONTENT_GROUP','Gruppe');
 define('TABLE_HEADING_CONTENT_SORT','Reihenfolge');
 define('TEXT_YES','Ja');
 define('TEXT_NO','Nein');
 define('TABLE_HEADING_CONTENT_ACTION','Aktion');
 define('TEXT_DELETE','L&ouml;schen');
 define('TEXT_EDIT','Bearbeiten');
 define('TEXT_PREVIEW','Vorschau');
 define('CONFIRM_DELETE','Wollen Sie den Content wirklich l&ouml;schen ?');
 define('CONTENT_NOTE','Content markiert mit <font color="#ff0000">*</font> geh&ouml;rt zum System und kann nicht gel&ouml;scht werden!');

 
 // edit
 define('TEXT_LANGUAGE','Sprache:');
 define('TEXT_STATUS','Sichtbar:');
 define('TEXT_STATUS_DESCRIPTION','Wenn ausgew&auml;hlt, wird ein Link in der Info Box angezeigt');
 define('TEXT_TITLE','Titel:');
 define('TEXT_TITLE_FILE','Titel/Dateiname:');
 define('TEXT_SELECT','-Bitte w&auml;hlen-');
 define('TEXT_HEADING','&Uuml;berschrift:');
 define('TEXT_CONTENT','Text:');
 define('TEXT_UPLOAD_FILE','Datei Hochladen:');
 define('TEXT_UPLOAD_FILE_LOCAL','(von Ihrem lokalen System)');
 define('TEXT_CHOOSE_FILE','Datei W&auml;hlen:');
 define('TEXT_CHOOSE_FILE_DESC','Sie k&ouml;nnen ebenfalls eine bereits verwendete Datei aus der Liste ausw&auml;hlen.');
 define('TEXT_NO_FILE','Auswahl L&ouml;schen');
 define('TEXT_CHOOSE_FILE_SERVER','(Falls Sie ihre Dateien selbst via FTP auf ihren Server gespeichert haben <i>(media/content)</i>, k&ouml;nnen Sie hier die Datei ausw&auml;hlen.');
 define('TEXT_CURRENT_FILE','Aktuelle Datei:');
 define('TEXT_FILE_DESCRIPTION','<b>Info:</b><br />Sie haben ebenfalls die M&ouml;glichkeit eine <b>.html</b> oder <b>.htm</b> Datei als Content einzubinden.<br /> Falls Sie eine Datei ausw&auml;hlen oder hochladen, wird der Text im Textfeld ignoriert.<br /><br />');
 define('ERROR_FILE','Falsches Dateiformat (nur .html od .htm)');
 define('ERROR_TITLE','Bitte geben Sie einen Titel ein');
 define('ERROR_COMMENT','Bitte geben Sie eine Dateibeschreibung ein!');
 define('TEXT_FILE_FLAG','Box:');
 define('TEXT_PARENT','Hauptdokument:');
 define('TEXT_PARENT_DESCRIPTION','Diesem Dokument zuweisen');
 define('TEXT_PRODUCT','Artikel:');
 define('TEXT_LINK','Link:');
 define('TEXT_SORT_ORDER','Sortierung:');
 define('TEXT_GROUP','Sprachgruppe:');
 define('TEXT_GROUP_DESC','Mit dieser ID verkn&uuml;pfen sie gleiche Themen unterschiedlicher Sprachen miteinander.');
 
 define('TEXT_CONTENT_DESCRIPTION','Mit diesem Content Manager haben Sie die M&ouml;glichkeit, jeden beliebige Dateityp einem Artikel hinzuzuf&uuml;gen.<br />Zb. Artikelbeschreibungen, Handb&uuml;cher, technische Datenbl&auml;tter, H&ouml;rproben, usw...<br />Diese Elemente werden In der Artikel-Detailansicht angezeigt.<br /><br />');
 define('TEXT_FILENAME','Benutze Datei:');
 define('TEXT_FILE_DESC','Beschreibung:');
 define('USED_SPACE','Verwendeter Speicherplatz:');
 define('TABLE_HEADING_CONTENT_FILESIZE','Dateigr&ouml;&szlig;e');
   
 
 ?>