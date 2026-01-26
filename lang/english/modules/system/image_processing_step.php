<?php
/* -----------------------------------------------------------------------------------------
   $Id$

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

define('MODULE_STEP_IMAGE_PROCESS_TEXT_DESCRIPTION', 'All Images in these directories<br /><br />
/images/product_images/popup_images/<br />
/images/product_images/info_images/<br />
/images/product_images/midi_images/<br />
/images/product_images/thumbnail_images/<br />
/images/product_images/mini_images/ <br /> 
/images/categories/ <br /> 
/images/manufacturers/ <br /> 
/images/banner/ <br /> 
<br /> are getting processed.<br /> <br />
For this purpose, the script uses only a limited number of images and calls himself afterwards again.<br /> <br />');
define('MODULE_STEP_IMAGE_PROCESS_TEXT_TITLE', 'Imageprocessing - product images');
define('MODULE_STEP_IMAGE_PROCESS_STATUS_DESC','Module status');
define('MODULE_STEP_IMAGE_PROCESS_STATUS_TITLE','Status');
define('MODULE_STEP_IMAGE_PROCESS_TEXT_IMAGE_EXPORT','Press Start to start the processing. This process may take some time - do not interrupt in any case!');
define('MODULE_STEP_IMAGE_PROCESS_TEXT_IMAGE_EXPORT_TYPE','<hr noshade><strong>Batch Processing:</strong>');

define('MODULE_STEP_IMAGE_PROCESS_TEXT_STEP_INFO','Images created: ');
define('MODULE_STEP_IMAGE_PROCESS_TEXT_STEP_INFO_READY',' - Finished!');
define('MODULE_STEP_IMAGE_PROCESS_TEXT_MAX_IMAGES','max. images for each reload');
define('MODULE_STEP_IMAGE_PROCESS_TEXT_PROCESS_TYPE', '<b>Imageprozessing:</b>');
define('MODULE_STEP_IMAGE_PROCESS_TEXT_SETTINGS', '<b>Settings:</b>');
define('MODULE_STEP_IMAGE_PROCESS_TEXT_LOGGING', '<b>Log:</b>');
define('MODULE_STEP_IMAGE_PROCESS_TEXT_ONLY_MISSING_IMAGES','Create only missing images');
define('MODULE_STEP_READY_STYLE_TEXT', '<div class="ready_info">%s</div>');
define('MODULE_STEP_READY_STYLE_BACK', MODULE_STEP_READY_STYLE_TEXT);
define('MODULE_STEP_IMAGE_PROCESS_TEXT_LOWER_FILE_EXT','Convert file extension to lowercase. Example: <b> JPG -> jpg</b>');
define('MODULE_STEP_IMAGE_PROCESS_TEXT_COUNT_INFO','Number of images in %s: %s pieces. ');

define('MODULE_STEP_IMAGE_PROCESS_TEXT_PRODUCTS','Products');
define('MODULE_STEP_IMAGE_PROCESS_TEXT_PRODUCTS_MINI_IMAGES','Mini Images');
define('MODULE_STEP_IMAGE_PROCESS_TEXT_PRODUCTS_THUMBNAIL_IMAGES','Thumbnail Images');
define('MODULE_STEP_IMAGE_PROCESS_TEXT_PRODUCTS_MIDI_IMAGES','Midi Images');
define('MODULE_STEP_IMAGE_PROCESS_TEXT_PRODUCTS_INFO_IMAGES','Info Images');
define('MODULE_STEP_IMAGE_PROCESS_TEXT_PRODUCTS_POPUP_IMAGES','Popup Images');

define('MODULE_STEP_IMAGE_PROCESS_TEXT_CATEGORIES','Categories');
define('MODULE_STEP_IMAGE_PROCESS_TEXT_CATEGORIES_IMAGES','Category Images');
define('MODULE_STEP_IMAGE_PROCESS_TEXT_CATEGORIES_LIST_IMAGES','Category Images Listing');
define('MODULE_STEP_IMAGE_PROCESS_TEXT_CATEGORIES_MOBILE_IMAGES','Category Images Mobile');

define('MODULE_STEP_IMAGE_PROCESS_TEXT_BANNERS','Banners');
define('MODULE_STEP_IMAGE_PROCESS_TEXT_BANNERS_IMAGES','Banner Images');
define('MODULE_STEP_IMAGE_PROCESS_TEXT_BANNERS_MOBILE_IMAGES','Banner Images Mobile');

define('MODULE_STEP_IMAGE_PROCESS_TEXT_MANUFACTURERS','Manufacturers');
define('MODULE_STEP_IMAGE_PROCESS_TEXT_MANUFACTURERS_IMAGES','Manufacturer Images');

define('MODULE_STEP_IMAGE_PROCESS_TEXT_LOGFILE','Enable logging, useful for debugging. The log file is saved in the folder /log in the root directory.');
