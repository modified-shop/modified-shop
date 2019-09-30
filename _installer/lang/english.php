<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
  
  
  // buttons
  define('BUTTON_BACK', 'Back');
  define('BUTTON_SUBMIT', 'Confirm');
  define('BUTTON_INSTALL', 'New installation');
  define('BUTTON_UPDATE', 'Update');
  define('BUTTON_SHOP', 'Go to Shop');

  define('BUTTON_CONFIGURE', 'execute <i class="fa fa-caret-right"></i>');
  define('BUTTON_SYSTEM_UPDATES', 'execute <i class="fa fa-caret-right"></i>');
  define('BUTTON_DB_UPDATE', 'execute <i class="fa fa-caret-right"></i>');
  define('BUTTON_SQL_UPDATE', 'execute <i class="fa fa-caret-right"></i>');
  define('BUTTON_SQL_MANUELL', 'execute <i class="fa fa-caret-right"></i>');
  define('BUTTON_DB_BACKUP', 'execute <i class="fa fa-caret-right"></i>');
  define('BUTTON_DB_RESTORE', 'execute <i class="fa fa-caret-right"></i>');
  define('BUTTON_PAYMENT_INSTALL', 'install <i class="fa fa-caret-right"></i>');
  
  // text
  define('TEXT_SQL_SUCCESS', '%s');
  define('TEXT_INFO_DONATIONS_IMG_ALT', 'Please support this project with your donation.');
  define('BUTTON_DONATE','<a href="https://www.modified-shop.org/spenden" target="_blank"><img src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" alt="' . TEXT_INFO_DONATIONS_IMG_ALT . '" border="0" /></a>');
  define('TEXT_START', '<b>Welcome to the modified eCommerce Shopsoftware installation process</b><br /><br />The modified eCommerce Shopsoftware is an open source e-commerce solution under on going development by the modified eCommerce Shopsoftware Team and its community.<br /> Its feature packed out-of-the-box installation allows store owners to setup, run, and maintain their online stores with minimum effort and with no costs involved.<br />The modified eCommerce Shopsoftware combines open source solutions to provide a free and open development platform, which includes the powerful PHP web scripting language, the stable Apache web server, and the fast MySQL database server.<br /><br />With no restrictions or special requirements, the modified eCommerce Shopsoftware can be installed on on any environment that supports PHP '.PHP_VERSION_MIN.' and MySQL 5.0.0, which includes Linux, Solaris, BSD, and Microsoft Windows environments.<br /><br />The modified eCommerce Shopsoftware is an open source project, yet a lot of work and spare time go into this project. Therefore we would be grateful if you show your appreciation by <b>donating</b> to the project.<br /><br />' . BUTTON_DONATE);
  define('TEXT_UPDATER_HEADING', 'Please Choose');
  define('TEXT_UPDATER', 'Welcome to the Updater of the modified eCommerce Shopsoftware');
  define('TEXT_UPDATE_CONFIG', 'Update configuration file (configure.php)');
  define('TEXT_UPDATE_SYSTEM', 'System updates');
  define('TEXT_UPDATE_SYSTEM_SUCCESS', 'System updates executed successful.');
  
  define('TEXT_CONFIGURE', 'Recreate configuration file (configure.php)');
  define('TEXT_CONFIGURE_DESC', 'Here you can update the configure.php file to make sure it is up to date.');
  define('TEXT_CONFIGURE_SUCCESS', 'configure.php written!');
  
  define('TEXT_SQL_UPDATE', 'Database Update');
  define('TEXT_SQL_UPDATE_HEADING', 'Choose SQL Update');
  define('TEXT_SQL_UPDATE_DESC', 'Please select only the update files, which are necessary for your current Shopversion.');
  define('TEXT_EXECUTED_SUCCESS', '<b>Executed successful:</b>');
  define('TEXT_EXECUTED_ERROR', '<b>Executed with errors:</b>');

  define('TEXT_SQL_MANUELL', 'Manual SQL input');
  define('TEXT_SQL_MANUELL_HEADING', 'Enter SQL command:');
  define('TEXT_SQL_MANUELL_DESC', 'SQL commands have to be enclosed with a semicolon ( ; )!');

  define('TEXT_DB_RESTORE', 'Database restore');
  define('TEXT_DB_RESTORE_DESC', 'You can restore your database from an existing backup here.');
  define('TEXT_INFO_DO_RESTORE', 'The database is being restored!');
  define('TEXT_INFO_DO_RESTORE_OK', 'The database was restored successfully!');
  
  define('TEXT_DB_BACKUP', 'Database backup');
  define('TEXT_DB_BACKUP_DESC', 'You can backup your database here.');
  define('TEXT_DB_COMPRESS', 'Compress backup');
  define('TEXT_DB_REMOVE_COLLATE', 'Without encoding \'COLLATE\' and \'DEFAULT CHARSET\'');
  define('TEXT_DB_REMOVE_ENGINE', 'Without storage engines \'ENGINE\'');
  define('TEXT_DB_COMPLETE_INSERTS', 'Complete \'INSERT\'s');
  define('TEXT_DB_UFT8_CONVERT', 'Convert database to UTF-8');
  define('TEXT_DB_COMPRESS_GZIP', 'Use GZIP');
  define('TEXT_DB_COMPRESS_RAW', 'No Compression (Pure SQL)');
  define('TEXT_DB_SIZE', 'Size');
  define('TEXT_DB_DATE', 'Date');
  
  define('TEXT_INFO_DO_UPDATE_OK', 'The database was successfully updated!');
  define('TEXT_INFO_DO_UPDATE', 'The database is being updated!');

  define('TEXT_INFO_DO_BACKUP_OK', 'The database was successfully backed up!');
  define('TEXT_INFO_DO_BACKUP', 'The database is being backed up!');
  define('TEXT_INFO_WAIT', 'Please wait!');
  define('TEXT_INFO_FINISH', 'FINISH!');
  define('TEXT_INFO_UPDATE', 'Tables updated: ');
  define('TEXT_INFO_RESTORE', 'Tables restored: ');
  define('TEXT_INFO_BACKUP', 'Tables backed up: ');
  define('TEXT_INFO_LAST', 'Last edited: ');
  define('TEXT_INFO_CALLS', 'Page views: ');
  define('TEXT_INFO_TIME', 'Script runtime: ');
  define('TEXT_INFO_FROM', ' of ');
  define('TEXT_INFO_MAX_RELOADS', 'Maximum page reloads were reached: ');
  
  define('TEXT_DB_UPDATE', 'Database structure update');
  define('TEXT_DB_UPDATE_DESC', 'Here you can bring the database of your shop installation up to date.');
  define('TEXT_DB_UPDATE_FINISHED', 'DB structure update successfully completed!');
  define('TEXT_FROM', ' of ');
  //define('TEXT_DB_UPDATE_BEFORE', 'Text before'); // Not used yet
  //define('TEXT_DB_UPDATE_AFTER', 'Text after'); // Not used yet

  define('TEXT_DB_HEADING', 'Information about the database:');
  define('TEXT_DB_SERVER', 'Server:');
  define('TEXT_DB_USERNAME', 'Username:');
  define('TEXT_DB_PASSWORD', 'Password:');
  define('TEXT_DB_DATABASE', 'Database:');
  define('TEXT_DB_MYSQL_TYPE', 'Type:');
  define('TEXT_DB_CHARSET', 'Charset:');
  define('TEXT_DB_PCONNECT', 'Persistent:');
  define('TEXT_DB_EXISTS', 'Database already exists');
  define('TEXT_DB_EXISTS_DESC', 'If you click "Confirm", all tables in this database will be overwritten! If you do not want it, click "Back" and specify a different database. Otherwise, click "Confirm".');
  define('TEXT_DB_INSTALL', 'Database installation (mandatory on initial setup!). Existing database tables are exhausted!');

  define('TEXT_SERVER_HEADING', 'Information about the shop:');
  define('TEXT_SERVER_HTTP_SERVER', 'HTTP:');
  define('TEXT_SERVER_HTTPS_SERVER', 'HTTPS:');
  define('TEXT_SERVER_USE_SSL', 'SSL:');
  define('TEXT_SERVER_SESSION', 'Session:');

  define('TEXT_ADMIN_DIRECTORY_HEADING','Admin Directory:');
  define('TEXT_ADMIN_DIRECTORY_DESCRIPTION', 'Due to seurity reasons change the name of the admin directory.');
  define('TEXT_ADMIN_DIRECTORY', 'This is a generated suggestion:');

  define('TEXT_ACCOUNT','The installer will create the admin account and will perform some db actions.<br /> The given information for <b>Country</b> and <b>Post Code</b> are used for shipping and tax callculations.');
  define('TEXT_ACCOUNT_HEADING', 'Account details:');
  define('TEXT_ACCOUNT_FIRSTNAME', 'First name:');
  define('TEXT_ACCOUNT_LASTNAME', 'Last name:');
  define('TEXT_ACCOUNT_COMPANY', 'Company:');
  define('TEXT_ACCOUNT_STREET', 'Street/No.:');
  define('TEXT_ACCOUNT_CODE', 'Postcode:');
  define('TEXT_ACCOUNT_CITY', 'City:');
  define('TEXT_ACCOUNT_COUNTRY', 'Country:');
  define('TEXT_ACCOUNT_EMAIL', 'E-Mail:');
  define('TEXT_ACCOUNT_CONFIRM_EMAIL', ' Confirm E-Mail:');
  define('TEXT_ACCOUNT_PASSWORD', 'Password:');
  define('TEXT_ACCOUNT_CONFIRMATION', 'Confirm Password:');
  
  define('TEXT_FINISHED', 'Here you can already install the popular PayPal payment methods.');
  define('TEXT_MODULES_INSTALLED', 'Intalled:');
  define('TEXT_MODULES_UNINSTALLED', 'Not installed:');
  define('TEXT_INFO_DO_INSTALL', 'The database is being installed.');
  
  define('TEXT_ERROR_JAVASCRIPT', 'Javascript is deactivated in your browser. You have to enable javascript to run the installer.');
  define('TEXT_ERROR_PERMISSION_FILES', 'The following files require write permissions (CHMOD 777):');
  define('TEXT_ERROR_PERMISSION_FOLDER', 'The following folders require write permissions (CHMOD 777):');
  define('TEXT_ERROR_PERMISSION_RFOLDER', 'The following folders including all files and subfolders require recursive write permissions (CHMOD 777):');
  define('TEXT_ERROR_REQUIREMENTS', 'Requirements');
  define('TEXT_ERROR_REQUIREMENTS_NAME', 'Name');
  define('TEXT_ERROR_REQUIREMENTS_VERSION', 'Version');
  define('TEXT_ERROR_REQUIREMENTS_MIN', 'Min');
  define('TEXT_ERROR_REQUIREMENTS_MAX', 'Max');
  define('TEXT_ERROR_FTP', 'Change permissions via FTP:');
  define('TEXT_ERROR_FTP_HOST', 'FTP Host:');
  define('TEXT_ERROR_FTP_PORT', 'FTP Port:');
  define('TEXT_ERROR_FTP_PATH', 'FTP Path:');
  define('TEXT_ERROR_FTP_USER', 'FTP Username:');
  define('TEXT_ERROR_FTP_PASS', 'FTP Password:');
  define('TEXT_ERROR_UNLINK_FILES', 'The following files have to be deleted:');
  define('TEXT_ERROR_UNLINK_FOLDER', 'The following folders have to be deleted:');
  
  // errors
  define('ERROR_DATABASE_CONNECTION', 'Please check DB data');
  define('ERROR_DATABASE_NOT_EMPTY', 'ATTENTION: Your database already contains tables!');
  define('ERROR_MODULES_PAYMENT', 'Unfortunately we could not find this type of payment...');
  define('ERROR_SQL_UPDATE_NO_FILE', 'Unfortunately we could not find any SQL update file...');
  define('ERROR_FTP_LOGIN_NOT_POSSIBLE', 'FTP access data incorrect, host not available');
  define('ERROR_FTP_CHMOD_WAS_NOT_SUCCESSFUL', 'Changing the directory permissions was unsuccessful');

  define('ENTRY_FIRST_NAME_ERROR', 'Your first name must consist of at least  ' . ENTRY_FIRST_NAME_MIN_LENGTH . ' characters.');
  define('ENTRY_LAST_NAME_ERROR', 'Your last name must consist of at least ' . ENTRY_LAST_NAME_MIN_LENGTH . ' characters.');
  define('ENTRY_EMAIL_ADDRESS_ERROR', 'Your e-mail address must consist of at least  ' . ENTRY_EMAIL_ADDRESS_MIN_LENGTH . ' characters.');
  define('ENTRY_EMAIL_ADDRESS_CHECK_ERROR', 'Your e-mail address entered is incorrect or already registered.');
  define('ENTRY_EMAIL_ERROR_NOT_MATCHING', 'Your entered e-mail addresses do not match.'); // Hetfield - 2009-08-15 - confirm e-mail at registration
  define('ENTRY_STREET_ADDRESS_ERROR', 'Street/No. must consist of at least ' . ENTRY_STREET_ADDRESS_MIN_LENGTH . ' characters.');
  define('ENTRY_POST_CODE_ERROR', 'Your postcode must consist of at least ' . ENTRY_POSTCODE_MIN_LENGTH . ' characters.');
  define('ENTRY_CITY_ERROR', 'City must consist of at least ' . ENTRY_CITY_MIN_LENGTH . ' characters.');
  define('ENTRY_PASSWORD_ERROR', 'Your password must consist of at least ' . ENTRY_PASSWORD_MIN_LENGTH . ' characters.');
  define('ENTRY_PASSWORD_ERROR_MIN_LOWER', 'Password must contain at least %s lowercase characters');
  define('ENTRY_PASSWORD_ERROR_MIN_UPPER', 'Password must contain at least %s uppercase characters');
  define('ENTRY_PASSWORD_ERROR_MIN_NUM', 'Password must contain at least %s numbers');
  define('ENTRY_PASSWORD_ERROR_MIN_CHAR', 'Password must contain at least %s non-aplhanumeric characters');
  define('ENTRY_PASSWORD_ERROR_NOT_MATCHING', 'Your passwords do not match.');
  define('ENTRY_PASSWORD_CURRENT_ERROR','Your current password must not be empty.');
  
?>