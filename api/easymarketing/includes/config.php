<?php
/* -----------------------------------------------------------------------------------------
   $Id:$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
  die('Direct Access to this location is not allowed.');
}

require_once(DIR_WS_CLASSES.'language.php');
$language = new language(DEFAULT_LANGUAGE);

define('MODULE_EASYMARKETING_LANGUAGES_ID', $language->language['id']);
define('MODULE_EASYMARKETING_LANGUAGE_ISOCODE_2', strtoupper($language->language['code']));
define('MODULE_EASYMARKETING_LANGUAGE_NAME', $language->language['directory']);
define('MODULE_EASYMARKETING_LANGUAGE_CHARSET', $language->language['language_charset']);

define('MODULE_EASYMARKETING_IMAGE_SIZE', DIR_WS_POPUP_IMAGES); // allowed: DIR_WS_POPUP_IMAGES, DIR_WS_INFO_IMAGES, DIR_WS_THUMBNAIL_IMAGES
defined('MODULE_EASYMARKETING_CONDITION_DEFAULT') OR define('MODULE_EASYMARKETING_CONDITION_DEFAULT', 'new'); // allowed: 'new', 'refurbished', 'used'
defined('MODULE_EASYMARKETING_AVAILIBILLITY_STOCK_0') OR define('MODULE_EASYMARKETING_AVAILIBILLITY_STOCK_0', 'available for order'); // allowed: 'in stock', 'available for order', 'out of stock', 'preorder'
defined('MODULE_EASYMARKETING_AVAILIBILLITY_STOCK_1') OR define('MODULE_EASYMARKETING_AVAILIBILLITY_STOCK_1', 'in stock'); // allowed: 'in stock', 'available for order', 'out of stock', 'preorder'

define('MODULE_EASYMARKETING_ADDITIONAL_DB', ''); // comma separated list

defined('MODULE_EASYMARKETING_SHOP_TOKEN') OR define('MODULE_EASYMARKETING_SHOP_TOKEN', 'sjynphz5e00d0jt8p75rsan1npn7ljbj'); // this token must have a lenth of 32 chars and must be set in easymarketing
defined('MODULE_EASYMARKETING_ACCESS_TOKEN') OR define('MODULE_EASYMARKETING_ACCESS_TOKEN', 'a5b5e7155a2d729adbd656369e28668b'); // this token comes from easymarketing

define('DIR_FS_EASYMARKETING_ROOT', DIR_FS_CATALOG.'api/easymarketing/');
define('DIR_FS_EASYMARKETING_INCLUDES', DIR_FS_EASYMARKETING_ROOT.'includes/');

define('EASYMARKETING_API_URL', 'api.easymarketing.de');

define('MODULE_EASYMARKETING_DEBUG', false);
