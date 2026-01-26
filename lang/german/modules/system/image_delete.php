<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  define('MODULE_IMAGE_DELETE_TEXT_TITLE', 'Alte Bilder l&ouml;schen');
  define('MODULE_IMAGE_DELETE_TEXT_DESCRIPTION', 'Es werden alle nicht verwendeten Bilder in den Verzeichnissen<br /><br />
  /images/product_images/popup_images/<br />
  /images/product_images/info_images/<br />
  /images/product_images/midi_images/<br />
  /images/product_images/thumbnail_images/<br />
  /images/product_images/mini_images/ <br /> 
  /images/categories/ <br /> 
  /images/manufacturers/ <br /> 
  /images/banner/ <br /> 
  <br /> gel&ouml;scht.<br /> <br />
  Hierzu verarbeitet das Script nur eine begrenzte Anzahl von Bildern und ruft sich danach selbst wieder auf.<br /> <br />');
  
  define('MODULE_IMAGE_DELETE_STATUS_DESC','Modulstatus');
  define('MODULE_IMAGE_DELETE_STATUS_TITLE','Status');
  
  define('MODULE_IMAGE_DELETE_EXPORT_TEXT','Dr&uuml;cken Sie Start um die Stapelverarbeitung zu starten. Dieser Vorgang kann einige Zeit dauern - auf keinen Fall unterbrechen!');
  define('MODULE_IMAGE_DELETE_EXPORT_TEXT_TYPE','<hr noshade><strong>Stapelverarbeitung:</strong>');
  
  define('MODULE_IMAGE_DELETE_IMAGE_STEP_INFO','%s Bilder &uuml;berpr&uuml;ft: ');
  define('MODULE_IMAGE_DELETE_IMAGE_STEP_INFO_READY',' - Fertig!');
  define('MODULE_IMAGE_DELETE_TEXT_MAX_IMAGES','<b>Bilder pro Seitenreload:</b>');
  define('MODULE_IMAGE_DELETE_TEXT_PROCESS_TYPE', '<b>Bilder L&ouml;schen:</b>');
  define('MODULE_IMAGE_DELETE_TEXT_READY', '<div class="ready_info">%s</div>');
  define('MODULE_IMAGE_DELETE_TEXT_READY_BACK', MODULE_IMAGE_DELETE_TEXT_READY);
  
  define('MODULE_IMAGE_DELETE_TEXT_PRODUCTS','Artikel');
  define('MODULE_IMAGE_DELETE_TEXT_PRODUCTS_MINI_IMAGES','Mini Bilder');
  define('MODULE_IMAGE_DELETE_TEXT_PRODUCTS_THUMBNAIL_IMAGES','Thumbnail Bilder');
  define('MODULE_IMAGE_DELETE_TEXT_PRODUCTS_MIDI_IMAGES','Midi Bilder');
  define('MODULE_IMAGE_DELETE_TEXT_PRODUCTS_INFO_IMAGES','Info Bilder');
  define('MODULE_IMAGE_DELETE_TEXT_PRODUCTS_POPUP_IMAGES','Popup Bilder');
  define('MODULE_IMAGE_DELETE_TEXT_PRODUCTS_ORIGINAL_IMAGES','Original Bilder');
  
  define('MODULE_IMAGE_DELETE_TEXT_CATEGORIES','Kategorien');
  define('MODULE_IMAGE_DELETE_TEXT_CATEGORIES_IMAGES','Kategorie Bilder');
  define('MODULE_IMAGE_DELETE_TEXT_CATEGORIES_ORIGINAL_IMAGES','Kategorie Original Bilder');
  
  define('MODULE_IMAGE_DELETE_TEXT_MANUFACTURERS','Hersteller');
  define('MODULE_IMAGE_DELETE_TEXT_MANUFACTURERS_IMAGES','Hersteller Bilder');
  define('MODULE_IMAGE_DELETE_TEXT_MANUFACTURERS_ORIGINAL_IMAGES','Hersteller Original Bilder');
  
  define('MODULE_IMAGE_DELETE_TEXT_BANNER','Banner');
  define('MODULE_IMAGE_DELETE_TEXT_BANNER_IMAGES','Banner Bilder');
  define('MODULE_IMAGE_DELETE_TEXT_BANNER_ORIGINAL_IMAGES','Banner Original Bilder');
  
  define('MODULE_IMAGE_DELETE_TEXT_LOGGING', '<b>Log:</b>');
  define('MODULE_IMAGE_DELETE_TEXT_LOGFILE','Die Logdatei wird im Ordner /log im Hauptverzeichnis gespeichert und beinhaltet die gel&ouml;schten Bilder.');
