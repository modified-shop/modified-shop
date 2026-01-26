<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  define('MODULE_IMAGE_DELETE_TEXT_TITLE', 'Delete old images');
  define('MODULE_IMAGE_DELETE_TEXT_DESCRIPTION', 'All not used Images in these directories<br /><br />
  /images/product_images/popup_images/<br />
  /images/product_images/info_images/<br />
  /images/product_images/midi_images/<br />
  /images/product_images/thumbnail_images/<br />
  /images/product_images/mini_images/ <br /> 
  /images/categories/ <br /> 
  /images/manufacturers/ <br /> 
  /images/banner/ <br /> 
  <br /> getting deleted.<br /> <br />
  For this purpose, the script uses only a limited number of images and calls itself afterwards again.<br /> <br />');
  
  define('MODULE_IMAGE_DELETE_STATUS_DESC','Module status');
  define('MODULE_IMAGE_DELETE_STATUS_TITLE','Status');
  
  define('MODULE_IMAGE_DELETE_EXPORT_TEXT','Press Start to start the processing. This process may take some time - do not interrupt in any case!');
  define('MODULE_IMAGE_DELETE_EXPORT_TEXT_TYPE','<hr noshade><strong>Batch Processing:</strong>');
  
  define('MODULE_IMAGE_DELETE_IMAGE_STEP_INFO','%s Images checked: ');
  define('MODULE_IMAGE_DELETE_IMAGE_STEP_INFO_READY',' - Finished!');
  define('MODULE_IMAGE_DELETE_TEXT_MAX_IMAGES','<b>Max. Images for each reload:</b>');
  define('MODULE_IMAGE_DELETE_TEXT_PROCESS_TYPE', '<b>Delete Images:</b>');
  define('MODULE_IMAGE_DELETE_TEXT_READY', '<div class="ready_info">%s</div>');
  define('MODULE_IMAGE_DELETE_TEXT_READY_BACK', MODULE_IMAGE_DELETE_TEXT_READY);
  
  define('MODULE_IMAGE_DELETE_TEXT_PRODUCTS','Products');
  define('MODULE_IMAGE_DELETE_TEXT_PRODUCTS_MINI_IMAGES','Mini Images');
  define('MODULE_IMAGE_DELETE_TEXT_PRODUCTS_THUMBNAIL_IMAGES','Thumbnail Images');
  define('MODULE_IMAGE_DELETE_TEXT_PRODUCTS_MIDI_IMAGES','Midi Images');
  define('MODULE_IMAGE_DELETE_TEXT_PRODUCTS_INFO_IMAGES','Info Images');
  define('MODULE_IMAGE_DELETE_TEXT_PRODUCTS_POPUP_IMAGES','Popup Images');
  define('MODULE_IMAGE_DELETE_TEXT_PRODUCTS_ORIGINAL_IMAGES','Original Images');
  
  define('MODULE_IMAGE_DELETE_TEXT_CATEGORIES','Categories');
  define('MODULE_IMAGE_DELETE_TEXT_CATEGORIES_IMAGES','Categories Images');
  define('MODULE_IMAGE_DELETE_TEXT_CATEGORIES_ORIGINAL_IMAGES','Categories Original Images');
  
  define('MODULE_IMAGE_DELETE_TEXT_MANUFACTURERS','Manufacturers');
  define('MODULE_IMAGE_DELETE_TEXT_MANUFACTURERS_IMAGES','Manufacturers Images');
  define('MODULE_IMAGE_DELETE_TEXT_MANUFACTURERS_ORIGINAL_IMAGES','Manufacturers Original Images');
  
  define('MODULE_IMAGE_DELETE_TEXT_BANNER','Banner');
  define('MODULE_IMAGE_DELETE_TEXT_BANNER_IMAGES','Banner Images');
  define('MODULE_IMAGE_DELETE_TEXT_BANNER_ORIGINAL_IMAGES','Banner Original Images');
  
  define('MODULE_IMAGE_DELETE_TEXT_LOGGING', '<b>Log:</b>');
  define('MODULE_IMAGE_DELETE_TEXT_LOGFILE','The log file is saved in the folder /log in the root directory and includes the deleted images.');
