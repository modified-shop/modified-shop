<?php
/* -----------------------------------------------------------------------------------------
   $Id: header.php 3808 2012-10-28 20:39:04Z web28 $

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

//SET SHOP OFFLINE 503 STATUS CODE
require_once(DIR_FS_INC . 'xtc_get_shop_conf.inc.php'); 

$shop_is_offline = get_shop_offline_status();
if ($shop_is_offline) {
  header("HTTP/1.1 503 Service Temporarily Unavailable");
  header("Status: 503 Service Temporarily Unavailable");
}
//SET 410 STATUS CODE
elseif (isset($site_error) && ($site_error === CATEGORIE_NOT_FOUND || $site_error === TEXT_PRODUCT_NOT_FOUND || $site_error === TEXT_CONTENT_NOT_FOUND || $site_error === TEXT_SITE_NOT_FOUND)) {
  header("HTTP/1.0 410 Gone"); 
  header("Status: 410 Gone"); // FAST CGI
}

/******** SHOPGATE **********/
if(defined('MODULE_PAYMENT_SHOPGATE_STATUS') && MODULE_PAYMENT_SHOPGATE_STATUS=='True' && strpos($_SESSION['customers_status']['customers_status_payment_unallowed'], 'shopgate') === false){
  include_once (DIR_FS_CATALOG.'includes/external/shopgate/base/includes/header.php');
}
/******** SHOPGATE **********/

defined('TEMPLATE_HTML_ENGINE') or define('TEMPLATE_HTML_ENGINE', 'xhtml');
?>
<!DOCTYPE html<?php echo ((TEMPLATE_HTML_ENGINE == 'xhtml') ? ' PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"' : ''); ?>>
<html<?php echo ((TEMPLATE_HTML_ENGINE == 'xhtml') ? ' '.HTML_PARAMS : ' lang="'.$_SESSION['language_code'].'"'); ?>>
<head>
<?php include(DIR_WS_MODULES.FILENAME_METATAGS); ?>
<?php include(DIR_WS_MODULES.'favicon.php'); ?>
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
<meta name="generator" content="(c) by <?php echo PROJECT_VERSION; ?> ------ http://www.modified-shop.org" />
<?php
if (DIR_WS_BASE == '') {
  echo '<base href="'.(($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER).DIR_WS_CATALOG.'" />';
}
if (is_file('templates/'.CURRENT_TEMPLATE.'/css/general.css.php')) {
  require('templates/'.CURRENT_TEMPLATE.'/css/general.css.php');
} else { //Maintain backwards compatibility for older templates 
  echo '<link rel="stylesheet" type="text/css" href="templates/'.CURRENT_TEMPLATE.'/stylesheet.css" />';
}

// require theme based javascript
require('templates/'.CURRENT_TEMPLATE.'/javascript/general.js.php');

// require additional javascript
switch(basename($PHP_SELF)) {

  case FILENAME_CHECKOUT_PAYMENT:
      require('includes/form_check.js.php');
      echo $payment_modules->javascript_validation();
    break;

  case FILENAME_CHECKOUT_SHIPPING:
      require('includes/form_check.js.php');
      echo $shipping_modules->javascript_validation();
    break;

  case FILENAME_CREATE_ACCOUNT:
  case FILENAME_CREATE_GUEST_ACCOUNT:
  case FILENAME_ACCOUNT_PASSWORD:
  case FILENAME_ACCOUNT_EDIT:
  case FILENAME_CHECKOUT_PAYMENT:
  case FILENAME_CHECKOUT_SHIPPING_ADDRESS:
  case FILENAME_CHECKOUT_PAYMENT_ADDRESS:
  case FILENAME_ADVANCED_SEARCH:
  case FILENAME_PRODUCT_REVIEWS_WRITE: 
      require('includes/form_check.js.php');
    break;

  case FILENAME_ADDRESS_BOOK_PROCESS:
      if (isset($_GET['delete']) === false) {
        include('includes/form_check.js.php');
      }
    break;

}
?>
</head>
<body>
<?php

// include needed functions
require_once('inc/xtc_output_warning.inc.php');
require_once('inc/xtc_parse_input_field_data.inc.php');

// check if the 'install' directory exists, and warn of its existence
if (WARN_INSTALL_EXISTENCE == 'true') {
  if (is_dir(DIR_FS_CATALOG . '/' . DIR_MODIFIED_INSTALLER)) {
    xtc_output_warning(sprintf(WARNING_INSTALL_DIRECTORY_EXISTS, DIR_FS_CATALOG . DIR_MODIFIED_INSTALLER));
  }
}

// check if the configure.php file is writeable
if (WARN_CONFIG_WRITEABLE == 'true') {
  if ((is_file(DIR_WS_INCLUDES . 'configure.php')) && (is_writeable(DIR_WS_INCLUDES . 'configure.php'))) {
    xtc_output_warning(sprintf(WARNING_CONFIG_FILE_WRITEABLE, DIR_WS_INCLUDES . 'configure.php'));
  }
  if ((is_file(DIR_WS_INCLUDES . 'local/configure.php')) && (is_writeable(DIR_WS_INCLUDES . 'local/configure.php'))) {
    xtc_output_warning(sprintf(WARNING_CONFIG_FILE_WRITEABLE, DIR_WS_INCLUDES . 'local/configure.php'));
  }
}

// check if the session folder is writeable
if (WARN_SESSION_DIRECTORY_NOT_WRITEABLE == 'true') {
  if (STORE_SESSIONS == '') {
    if (!is_dir(xtc_session_save_path())) {
      xtc_output_warning(WARNING_SESSION_DIRECTORY_NON_EXISTENT);
    } elseif (!is_writeable(xtc_session_save_path())) {
      xtc_output_warning(WARNING_SESSION_DIRECTORY_NOT_WRITEABLE);
    }
  }
}

// check session.auto_start is disabled
if ( (WARN_SESSION_AUTO_START == 'true') && (function_exists('ini_get')) ) {
  if (ini_get('session.auto_start') == '1') {
    xtc_output_warning(WARNING_SESSION_AUTO_START);
  }
}

if ( (WARN_DOWNLOAD_DIRECTORY_NOT_READABLE == 'true') && (DOWNLOAD_ENABLED == 'true') ) {
  if (!is_dir(DIR_FS_DOWNLOAD)) {
    xtc_output_warning(WARNING_DOWNLOAD_DIRECTORY_NON_EXISTENT);
  }
}

$smarty->assign('navtrail', $breadcrumb->trail(' &raquo; '));
if (isset($_SESSION['customer_id'])) {
	$smarty->assign('logoff',xtc_href_link(FILENAME_LOGOFF, '', 'SSL'));
} else {
	$smarty->assign('login',xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
	$smarty->assign('create_account',xtc_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'));
}
$smarty->assign('index',xtc_href_link(FILENAME_DEFAULT));
if ($_SESSION['account_type'] == '0' || GUEST_ACCOUNT_EDIT == 'true') {
  $smarty->assign('account',xtc_href_link(FILENAME_ACCOUNT, '', 'SSL'));
}
$smarty->assign('cart',xtc_href_link(FILENAME_SHOPPING_CART, '', 'NONSSL'));
$smarty->assign('checkout',xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
$smarty->assign('store_name', encode_htmlspecialchars(TITLE));

if (isset($_GET['error_message']) && xtc_not_null($_GET['error_message'])) {
  $smarty->assign('error', get_message('error_message'));
}
if (isset($_GET['info_message']) && xtc_not_null($_GET['info_message'])) {
  $smarty->assign('error', get_message('info_message'));
}

## header_body_extra

// SHOP OFFLINE INFO
if ($shop_is_offline) {
  $smarty->assign('language', $_SESSION['language']);
  $smarty->assign('shop_offline_msg', xtc_get_shop_conf('SHOP_OFFLINE_MSG'));	
  $smarty->display(CURRENT_TEMPLATE.'/offline.html');	
  exit();
}

// ECONDA TRACKING
if (TRACKING_ECONDA_ACTIVE=='true') {
  echo '<script type="text/javascript"><!--'.PHP_EOL.
       '  var emos_kdnr="'. TRACKING_ECONDA_ID. '";'.PHP_EOL.
       '//--></script>'.PHP_EOL.
       '<a name="emos_sid" rel="'. xtc_session_id().'"></a>'.PHP_EOL.
       '<a name="emos_name" title="siteid" rel="'. $_SESSION['languages_id'].'" rev=""></a>'.PHP_EOL;
}

// GOOGLE CONV. TRACKING
if (basename($PHP_SELF) == FILENAME_CHECKOUT_SUCCESS && GOOGLE_CONVERSION == 'true') {
  require('includes/google_conversiontracking.js.php');
}

// BANNER SYSTEM
include(DIR_WS_INCLUDES.FILENAME_BANNER);

// BILLSAFE PAYMENT MODULE
if (defined('MODULE_PAYMENT_BILLSAFE_2_LAYER') && MODULE_PAYMENT_BILLSAFE_2_LAYER == 'True') {
  $bs_error = '';
  if (basename($PHP_SELF) == 'checkout_payment.php') {
    if (isset($_GET['payment_error'])) {
      $bs_error = stripslashes(html_entity_decode('payment_error='.$_GET['payment_error'].'&error_message='.$_GET['error_message']));
    }
    echo '<script type="text/javascript"><!--' .
         ' if (top.lpg) top.lpg.close("'.str_replace('&amp;', '&', xtc_href_link(FILENAME_CHECKOUT_PAYMENT, $bs_error, 'SSL')).'");' .
         '--></script>' . PHP_EOL;
  }
  if (basename($PHP_SELF) == 'checkout_success.php') {
    echo '<script type="text/javascript"><!--' .
         '  if (top.lpg) top.lpg.close("'.xtc_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL').'");' .
         '--></script>' . PHP_EOL;
  }
}
## header_body_extra
?>