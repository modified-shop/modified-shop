<?php
/*
  $Id: backup.php,v 1.16 2002/03/16 21:30:02 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

define('HEADING_TITLE', 'Database Backup Manager');

define('TEXT_INFO_DO_BACKUP', 'The database is being backed up!');
define('TEXT_INFO_DO_BACKUP_OK', 'The database has been backed up successfully!');
define('TEXT_INFO_DO_GZIP', 'The backup file is packed!');
define('TEXT_INFO_WAIT', 'Please wait!');

define('TEXT_INFO_DO_RESTORE', 'The database is being restored!');
define('TEXT_INFO_DO_RESTORE_OK', 'The database has been restored successfully!');
define('TEXT_INFO_DO_GUNZIP', 'The database is being unpacked!');

define('ERROR_BACKUP_DIRECTORY_DOES_NOT_EXIST', 'Error: The directory for the backup does not exist. Please correct the error in your configure.php.');
define('ERROR_BACKUP_DIRECTORY_NOT_WRITEABLE', 'Error: Unable to write to the backup directory.');
define('ERROR_DOWNLOAD_LINK_NOT_ACCEPTABLE', 'Error: The download link is not acceptable.');
define('ERROR_DECOMPRESSOR_NOT_AVAILABLE', 'Error: No suitable unpacker available.');
define('ERROR_UNKNOWN_FILE_TYPE', 'Error: Unknown file type.');
define('ERROR_RESTORE_FAILES', 'Error: Restore failed.');
define('ERROR_DATABASE_SAVED', 'Error: The database could not be backed up.');
define('ERROR_TEXT_PATH', 'Error: The path to mysqldump not found or given!');

define('SUCCESS_LAST_RESTORE_CLEARED', 'Successful: The last restoration date has been cleared.');
define('SUCCESS_DATABASE_SAVED', 'Successful: The database was backed up.');
define('SUCCESS_DATABASE_RESTORED', 'Successful: The database has been restored.');
define('SUCCESS_BACKUP_DELETED', 'Successful: The backup has been removed.');

define('TEXT_BACKUP_UNCOMPRESSED', 'The backup file has been unpacked: ');

define('TEXT_SIMULATION', '<br>(Simulation with log file)');

?>