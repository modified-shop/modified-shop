<?php
/* --------------------------------------------------------------
   $Id: languages.php 5011 2013-07-04 15:22:37Z web28 $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(languages.php,v 1.10 2002/01/19); www.oscommerce.com 
   (c) 2003	 nextcommerce (languages.php,v 1.5 2003/08/14); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

define('HEADING_TITLE', 'Languages');

define('TABLE_HEADING_LANGUAGE_NAME', 'Language');
define('TABLE_HEADING_LANGUAGE_CODE', 'Code');
define('TABLE_HEADING_ACTION', 'Action');

define('TEXT_INFO_EDIT_INTRO', 'Please make any necessary changes');
define('TEXT_INFO_LANGUAGE_NAME', 'Name:');
define('TEXT_INFO_LANGUAGE_CODE', 'Code:');
define('TEXT_INFO_LANGUAGE_IMAGE', 'Image:');
define('TEXT_INFO_LANGUAGE_DIRECTORY', 'Directory:');
define('TEXT_INFO_LANGUAGE_SORT_ORDER', 'Sort Order:');
define('TEXT_INFO_INSERT_INTRO', 'Please enter the new language with its related data');
define('TEXT_INFO_DELETE_INTRO', 'Are you sure you want to delete this language?');
define('TEXT_INFO_HEADING_NEW_LANGUAGE', 'New Language');
define('TEXT_INFO_HEADING_EDIT_LANGUAGE', 'Edit Language');
define('TEXT_INFO_HEADING_DELETE_LANGUAGE', 'Delete Language');
define('TEXT_INFO_LANGUAGE_CHARSET','Charset');
define('TEXT_INFO_LANGUAGE_CHARSET_INFO','meta-content:');

define('ERROR_REMOVE_DEFAULT_LANGUAGE', 'Error: The default language can not be removed. Please set another language as default, and try again.');

define('TEXT_INFO_LANGUAGE_STATUS', 'Status:');
define('TABLE_HEADING_LANGUAGE_STATUS', 'Status');
define('TEXT_INFO_LANGUAGE_STATUS_ADMIN', 'Status Admin:');
define('TABLE_HEADING_LANGUAGE_STATUS_ADMIN', 'Status Admin');

define ('TEXT_LANGUAGE_TRANSFER_INFO', 'Transfer following language tables');
define ('TEXT_LANGUAGE_TRANSFER_BTN', 'Transfer');
define ('TEXT_LANGUAGE_TRANSFER_FROM', 'From');
define ('TEXT_LANGUAGE_TRANSFER_TO', ' to ');
define ('TEXT_LANGUAGE_TRANSFER_OK', 'Transfer successful!');
define ('TEXT_LANGUAGE_TRANSFER_ERR', 'Please select a different language!');
define ('TEXT_LANGUAGE_TRANSFER_INFO2', '<strong>ATTENTION:</strong> When transferring, the existing texts will be overwritten, so please be sure to backup the database before.');
?>