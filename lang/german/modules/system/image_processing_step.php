<?php
/* -----------------------------------------------------------------------------------------
   $Id: image_processing_step.php 2992 2012-06-07 16:59:49Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(cod.php,v 1.28 2003/02/14); www.oscommerce.com
   (c) 2003	 nextcommerce (invoice.php,v 1.6 2003/08/24); www.nextcommerce.org
   (c) 2006 XT-Commerce (image_processing_step.php 950 2005-05-14; www.xt-commerce.com
   --------------------------------------------------------------
   Contribution
   image_processing_step (step-by-step Variante B) by INSEH 2008-03-26

   new javascript reload / only missing image/ max images  by web28 2011-03-17

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

define('MODULE_STEP_IMAGE_PROCESS_TEXT_DESCRIPTION', 'Es werden alle Bilder in den Verzeichnissen<br /><br />
/images/product_images/popup_images/<br />
/images/product_images/info_images/<br />
/images/product_images/thumbnail_images/ <br /> <br /> neu erstellt.<br /> <br />
Hierzu verarbeitet das Script nur eine begrenzte Anzahl von %s Bildern und ruft sich danach selbst wieder auf.<br /> <br />');
define('MODULE_STEP_IMAGE_PROCESS_TEXT_TITLE', 'Imageprocessing - Produktbilder');
define('MODULE_STEP_IMAGE_PROCESS_STATUS_DESC','Modulstatus');
define('MODULE_STEP_IMAGE_PROCESS_STATUS_TITLE','Status');
define('IMAGE_EXPORT','Dr&uuml;cken Sie Start um die Stapelverarbeitung zu starten. Dieser Vorgang kann einige Zeit dauern - auf keinen Fall unterbrechen!');
define('IMAGE_EXPORT_TYPE','<hr noshade><strong>Stapelverarbeitung:</strong>');

define('IMAGE_STEP_INFO','Bilder erstellt: ');
define('IMAGE_STEP_INFO_READY',' - Fertig!');
define('TEXT_MAX_IMAGES','max. Bilder pro Seitenreload');
define('TEXT_ONLY_MISSING_IMAGES','Nur fehlende Bilder erstellen');
define('MODULE_STEP_READY_STYLE_TEXT', '<div class="ready_info">%s</div>');
define('MODULE_STEP_READY_STYLE_BACK', MODULE_STEP_READY_STYLE_TEXT);
define('TEXT_LOWER_FILE_EXT','Dateiendung in Kleinbuchstaben umwandeln Bsp.: <b> JPG -> jpg</b>');
define('IMAGE_COUNT_INFO','Anzahl Bilder in %s: %s Stk. ');

define('TEXT_LOGFILE','Logging aktivieren, n&uuml;tzlich zur Fehlersuche. Die Logdatei wird im Ordner /log im Hauptverzeichnis gespeichert.');

?>