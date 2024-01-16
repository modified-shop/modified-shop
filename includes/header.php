<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(header.php,v 1.40 2003/03/14); www.oscommerce.com
   (c) 2003 nextcommerce (header.php,v 1.13 2003/08/17); www.nextcommerce.org
   (c) 2006 XT-Commerce (header.php 1140 2005-08-10)

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:

   Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
   http://www.oscommerce.com/community/contributions,282
   Copyright (c) Strider | Strider@oscworks.com
   Copyright (c)  Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// include needed functions
require_once(DIR_FS_INC . 'xtc_output_warning.inc.php');
require_once(DIR_FS_INC . 'xtc_parse_input_field_data.inc.php');

if ($shop_is_offline) {
  $current_link = preg_replace("/([^\?]*)(\?.*)/", "$1", $_SERVER['REQUEST_URI']);  
  $redirect_link = xtc_href_link(FILENAME_DEFAULT);
  $category_link = str_replace(array(HTTP_SERVER, HTTPS_SERVER), '', preg_replace("/([^\?]*)(\?.*)/", "$1", $redirect_link));
  if ($category_link != $current_link) {
    header('Location: '.preg_replace("/[\r\n]+(.*)$/i", "", html_entity_decode($redirect_link)));
    exit();
  }  
  header("HTTP/1.1 503 Service Temporarily Unavailable");
  header("Status: 503 Service Temporarily Unavailable");
}
//SET 410 STATUS CODE
elseif (isset($site_error) 
        && ($site_error === TEXT_CATEGORIE_NOT_FOUND 
            || $site_error === TEXT_PRODUCT_NOT_FOUND 
            || $site_error === TEXT_CONTENT_NOT_FOUND 
            || $site_error === TEXT_MANUFACTURER_NOT_FOUND
            || $site_error === TEXT_SITE_NOT_FOUND
            || $site_error === TEXT_SEARCH_NOT_FOUND
            )
        ) 
{
  if (!isset($_REQUEST['error'])) {
    header("HTTP/1.0 410 Gone"); 
    header("Status: 410 Gone"); // FAST CGI
  }
}

// gzip compression
if (GZIP_COMPRESSION == 'true' 
    && isset($ext_zlib_loaded)
    && $ext_zlib_loaded == true 
    && isset($ini_zlib_output_compression)
    && $ini_zlib_output_compression < 1
    && $encoding = xtc_check_gzip()
    )
{
  header('Content-Encoding: ' . $encoding);
}

foreach(auto_include(DIR_FS_CATALOG.'includes/extra/header/header_begin/','php') as $file) require_once ($file);

defined('TEMPLATE_RESPONSIVE') or define('TEMPLATE_RESPONSIVE', 'false');
defined('TEMPLATE_HTML_ENGINE') or define('TEMPLATE_HTML_ENGINE', 'xhtml');
?>
<!DOCTYPE html<?php echo ((TEMPLATE_HTML_ENGINE == 'xhtml') ? ' PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"' : ''); ?>>
<html<?php echo ((TEMPLATE_HTML_ENGINE == 'xhtml') ? ' '.HTML_PARAMS : ' lang="'.$_SESSION['language_code'].'"'); ?>>
<head>
<?php include(DIR_WS_MODULES.FILENAME_METATAGS); ?>
<?php include(DIR_WS_MODULES.'favicons.php'); ?>
<?php
/*
  The following copyright announcement is in compliance
  to section 2c of the GNU General Public License, and
  thus can not be removed, or can only be modified
  appropriately.

  Please leave this comment intact together with the
  following copyright announcement.
*/
?>
<!--
=========================================================
modified eCommerce Shopsoftware (c) 2009-2013 [www.modified-shop.org]
=========================================================

modified eCommerce Shopsoftware offers you highly scalable E-Commerce-Solutions and Services.
The Shopsoftware is redistributable under the GNU General Public License (Version 2) [http://www.gnu.org/licenses/gpl-2.0.html].
based on: E-Commerce Engine Copyright (c) 2006 xt:Commerce, created by Mario Zanier & Guido Winger and licensed under GNU/GPL.
Information and contribution at http://www.xt-commerce.com

=========================================================
Please visit our website: www.modified-shop.org
=========================================================
-->
<meta name="generator" content="(c) by <?php echo PROJECT_VERSION; ?> 12D https://www.modified-shop.org" />
<?php
if (DIR_WS_BASE == '') {
  echo '<base href="'.(($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER).DIR_WS_CATALOG.'" />'.PHP_EOL;
}
if (is_file('templates/'.CURRENT_TEMPLATE.'/css/general.css.php')) {
  require('templates/'.CURRENT_TEMPLATE.'/css/general.css.php');
} else { //Maintain backwards compatibility for older templates 
  echo '<link rel="stylesheet" type="text/css" href="templates/'.CURRENT_TEMPLATE.'/stylesheet.css" />'.PHP_EOL;
}

// require theme based javascript
require('templates/'.CURRENT_TEMPLATE.'/javascript/general.js.php');

// require additional javascript
switch(basename($PHP_SELF)) {
  case FILENAME_CHECKOUT_PAYMENT:
    require('includes/form_check.js.php');
    if (isset($payment_modules)
        && is_object($payment_modules)
        && method_exists($payment_modules, 'javascript_validation')
        )
    {
      echo $payment_modules->javascript_validation();
    }
    break;

  case FILENAME_CHECKOUT_SHIPPING:
    if (isset($shipping_modules) 
        && is_object($shipping_modules)
        && method_exists($shipping_modules, 'javascript_validation')
        )
    {
      echo $shipping_modules->javascript_validation();
    }
    break;
}

foreach(auto_include(DIR_FS_CATALOG.'includes/extra/header/header_head/','php') as $file) require_once ($file);
?>
</head>
<body>
<?php
if ($shop_is_offline) {
  $smarty->assign('language', $_SESSION['language']);
  $smarty->assign('shop_offline_msg', xtc_get_shop_conf('SHOP_OFFLINE_MSG'));	
  $smarty->display(CURRENT_TEMPLATE.'/offline.html');	
  include ('includes/application_bottom.php');
  exit();
}

xtc_output_warning();

$smarty->assign('navtrail', $breadcrumb->trail(' &raquo; '));
if (isset($_SESSION['customer_id'])) {
	$smarty->assign('logoff',xtc_href_link(FILENAME_LOGOFF, '', 'SSL'));
} else {
	$smarty->assign('login',xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
	$smarty->assign('create_account',xtc_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'));
}
$smarty->assign('index',xtc_href_link(FILENAME_DEFAULT));
if ((isset($_SESSION['customer_id']) 
     && $_SESSION['customers_status']['customers_status_id'] != DEFAULT_CUSTOMERS_STATUS_ID_GUEST
     ) || GUEST_ACCOUNT_EDIT == 'true'
    ) 
{
  $smarty->assign('account',xtc_href_link(FILENAME_ACCOUNT, '', 'SSL'));
}
$smarty->assign('cart',xtc_href_link(FILENAME_SHOPPING_CART, '', 'NONSSL'));
$smarty->assign('checkout',xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
$smarty->assign('store_name', encode_htmlspecialchars(TITLE));

foreach(auto_include(DIR_FS_CATALOG.'includes/extra/header/header_body/','php') as $file) require_once ($file);
