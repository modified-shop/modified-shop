<?php
  /* --------------------------------------------------------------
   $Id: backup.php 1780 2011-02-08 02:09:45Z cybercosmonaut $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

define('HEADING_TITLE', 'Logfiles'); 

define('TABLE_HEADING_TITLE', 'Title');
define('TABLE_HEADING_FILE_DATE', 'Date');
define('TABLE_HEADING_FILE_SIZE', 'Size');

define('TEXT_LOG_DIRECTORY', 'Log Directory:');
define('TEXT_DELETE_INTRO', 'Are you sure you want to delete this Logfile?');

define('SUCCESS_LOG_DELETED', 'Success: The Logfile has been removed.');

define('ERROR_BACKUP_DIRECTORY_DOES_NOT_EXIST', 'Error: Log directory does not exist. Please set this in configure.php.');
define('ERROR_BACKUP_DIRECTORY_NOT_WRITEABLE', 'Error: Log directory is not writeable.');
define('ERROR_DOWNLOAD_LINK_NOT_ACCEPTABLE', 'Error: Download link not acceptable.');
?>
