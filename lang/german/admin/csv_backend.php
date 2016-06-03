<?php
/* --------------------------------------------------------------
   $Id: csv_backend.php 5217 2013-07-22 14:47:23Z Tomcraft $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------*/


define('TITLE','CSV Backend');

define('IMPORT','Import');
define('EXPORT','Export');
define('UPLOAD','Datei auf Server laden');
define('SELECT','Zu importierende Datei auswaehlen und Import durchfuehren (/import Verzeichnis)');
define('SAVE','Auf Server Speichern (/export Verzeichnis)');
define('LOAD','Datei an Browser senden');
define('CSV_TEXTSIGN_TITLE','Texterkennungszeichen');
define('CSV_TEXTSIGN_DESC','Z.B. " &nbsp; | &nbsp;<span style="color:#c00;">Bei Semikolon als Trennzeichen sollte das Texterkennungszeichen auf " gesetzt werden!</span>');
define('CSV_SEPERATOR_TITLE','Trennzeichen');
define('CSV_SEPERATOR_DESC','Z.B. ; &nbsp; | &nbsp;<span style="color:#c00;">wird das Eingabefeld leer gelassen wird beim Export/Import per default \\t (= Tab) benutzt !</span>');
define('COMPRESS_EXPORT_TITLE','Kompression');
define('COMPRESS_EXPORT_DESC','Kompression der exportierten Daten');
define('CSV_SETUP','Einstellungen');
define('TEXT_IMPORT','');
define('TEXT_PRODUCTS','Produkte');
define('TEXT_EXPORT','Exportierte Datei wird im /export Verzeichnis gespeichert');
define('CSV_CATEGORY_DEFAULT_TITLE','Kategorie f&uuml;r den Import');
define('CSV_CATEGORY_DEFAULT_DESC','Alle Artikel, die in der CSV-Importdatei <b>keine</b> Kategorie zugeordnet haben und noch nicht im Shop vorhanden sind, werden in diese Kategorie importiert.<br/><b>Wichtig:</b> Wenn Sie Artikel ohne Kategorie in der CSV-Importdatei nicht importieren m&ouml;chten, dann w&auml;hlen Sie Kategorie "Top" aus, da in diese Kategorie keine Artikel importiert werden.');
//BOC added constants for category depth, noRiddle
define('CSV_CAT_DEPTH_TITLE','Kategorietiefe');
define('CSV_CAT_DEPTH_DESC','Wie tief soll der Kategoriebaum gehen? (z.B. bei Default-Einstellung 4: Hauptkategorie und drei Unterkategorien)<br />Diese Einstellung ist wichtig um die in der CSV angelegten Kategorien auch korrekt importiert zu bekommen. Das gleiche gilt f&uuml;r den Export.<br /><span style="color:#c00;">Mehr als 4 kann zu Performance-Einbu&szlig;en f&uuml;hren und ist evtl. nicht kundenfreundlich!');
//EOC added constants for category depth, noRiddle
?>