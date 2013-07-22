<?php
/* --------------------------------------------------------------
   $Id: cvs_backend.php

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce

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
   define('CSV_TEXTSIGN_DESC','zb. "');
   define('CSV_SEPERATOR_TITLE','Trennzeichen');
   define('CSV_SEPERATOR_DESC','zb. ;');
   define('COMPRESS_EXPORT_TITLE','Kompression');
   define('COMPRESS_EXPORT_DESC','Kompression der exportierten Daten');
   define('CSV_SETUP','Einstellungen');
   define('TEXT_IMPORT','');
   define('TEXT_PRODUCTS','Produkte');
   define('TEXT_EXPORT','Exportierte Datei wird im /export Verzeichnis gespeichert');
   define('CSV_CATEGORY_DEFAULT_TITLE','Kategorie f&uuml;r den Import');
   define('CSV_CATEGORY_DEFAULT_DESC','Alle Artikel, die in der CSV-Importdatei <b>keine</b> Kategorie zugeordnet haben und noch nicht im Shop vorhanden sind, werden in diese Kategorie importiert.<br/><b>Wichtig:</b> Wenn Sie Artikel ohne Kategorie in der CSV-Importdatei nicht importieren m&ouml;chten, dann w&auml;hlen Sie Kategorie "Top" aus, da in diese Kategorie keine Artikel importiert werden.');

?>