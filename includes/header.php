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
   Copyright (c  Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

define('DIR_MODIFIED_INSTALLER', '_installer');

//SET SHOP OFFLINE 503 STATUS CODE
require_once(DIR_FS_INC . 'xtc_get_shop_conf.inc.php'); 
if(xtc_get_shop_conf('SHOP_OFFLINE') == 'checked' && $_SESSION['customers_status']['customers_status_id'] != 0) {	   
	header("HTTP/1.1 503 Service Temporarily Unavailable");
  header("Status: 503 Service Temporarily Unavailable");
}
//SET 410 STATUS CODE
elseif (isset($error) && ($error == CATEGORIE_NOT_FOUND || $error == TEXT_PRODUCT_NOT_FOUND)) {
  header("HTTP/1.0 410 Gone"); 
  header("Status: 410 Gone"); // FAST CGI 
}

/******** SHOPGATE **********/
if(strpos(MODULE_PAYMENT_INSTALLED, 'shopgate.php') !== false && strpos($_SESSION['customers_status']['customers_status_payment_unallowed'], 'shopgate') === false){
  include_once (DIR_FS_CATALOG.'includes/external/shopgate/base/includes/header.php');
}
/******** SHOPGATE **********/

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_SESSION['language_charset']; ?>" /> 
<meta http-equiv="Content-Style-Type" content="text/css" />
<?php
/******** SHOPGATE **********/
if(strpos(MODULE_PAYMENT_INSTALLED, 'shopgate.php') !== false && strpos($_SESSION['customers_status']['customers_status_payment_unallowed'], 'shopgate') === false){
  echo $shopgateJsHeader;
}
/******** SHOPGATE **********/
?>
<?php include(DIR_WS_MODULES.FILENAME_METATAGS); ?>
<link rel="shortcut icon" href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER).DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/favicon.ico';?>" type="image/x-icon" />
<?php
/*
  The following copyright announcement is in compliance  to section 2c of the GNU General Public License, 
  and thus can not be removed, or can only be modified  appropriately.

  Please leave this comment intact together with the following copyright announcement.
*/
?>
<!--
=========================================================
modified eCommerce Shopsoftware (c) 2009-2012 [www.modified-shop.org]
=========================================================

modified eCommerce Shopsoftware offers you highly scalable E-Commerce-Solutions and Services.
The Shopsoftware is redistributable under the GNU General Public License (Version 2) [http://www.gnu.org/licenses/gpl-2.0.html].
based on: E-Commerce Engine Copyright (c) 2006 xt:Commerce, created by Mario Zanier & Guido Winger and licensed under GNU/GPL.
Information and contribution at http://www.xt-commerce.com

=========================================================
Please visit our website: www.modified-shop.org
=========================================================
-->

<meta name="generator" content="(c) by <?php echo PROJECT_VERSION; ?> ------| http://www.modified-shop.org" />
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>" />
<?php
if (file_exists('templates/'.CURRENT_TEMPLATE.'/css/general.css.php')) {
  require('templates/'.CURRENT_TEMPLATE.'/css/general.css.php');
} else { //Maintain backwards compatibility for older templates 
  echo '<link rel="stylesheet" type="text/css" href="templates/'.CURRENT_TEMPLATE.'/stylesheet.css" />';
}

?>
<script type="text/javascript"><!--
var selected;
var submitter = null;
function submitFunction() {
    submitter = 1;
}
function popupWindow(url) {
  window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=100,height=100,screenX=150,screenY=150,top=150,left=150')
}  
function selectRowEffect(object, buttonSelect) {
  if (!selected) {
    if (document.getElementById) {
      selected = document.getElementById('defaultSelected');
    } else {
      selected = document.all['defaultSelected'];
    }
  }
  if (selected) selected.className = 'moduleRow';
  object.className = 'moduleRowSelected';
  selected = object;
  if (document.getElementById('payment'[0])) {
    document.getElementById('payment'[buttonSelect]).checked=true;
  }
}
function rowOverEffect(object) {
  if (object.className == 'moduleRow') object.className = 'moduleRowOver';
}
function rowOutEffect(object) {
  if (object.className == 'moduleRowOver') object.className = 'moduleRow';
}
function popupImageWindow(url) {
  window.open(url,'popupImageWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=yes,copyhistory=no,width=100,height=100,screenX=150,screenY=150,top=150,left=150')
}
//--></script>
<?php
// require theme based javascript
require('templates/'.CURRENT_TEMPLATE.'/javascript/general.js.php');

if (strstr($PHP_SELF, FILENAME_CHECKOUT_PAYMENT)) {
 echo $payment_modules->javascript_validation();
}

if (strstr($PHP_SELF, FILENAME_CREATE_ACCOUNT)) {
  require('includes/form_check.js.php');
}

if (strstr($PHP_SELF, FILENAME_CREATE_GUEST_ACCOUNT )) {
  require('includes/form_check.js.php');
}
if (strstr($PHP_SELF, FILENAME_ACCOUNT_PASSWORD )) {
  require('includes/form_check.js.php');
}

if (strstr($PHP_SELF, FILENAME_ACCOUNT_EDIT )) {
  require('includes/form_check.js.php');
}

if (strstr($PHP_SELF, FILENAME_ADDRESS_BOOK_PROCESS )) {
  if (isset($_GET['delete']) == false) {
    include('includes/form_check.js.php');
  }
}

if (strstr($PHP_SELF, FILENAME_CHECKOUT_SHIPPING_ADDRESS ) || strstr($PHP_SELF,FILENAME_CHECKOUT_PAYMENT_ADDRESS)) {
  require('includes/form_check.js.php');
  ?>
<script type="text/javascript"><!--
function check_form_optional(form_name) {
  var form = form_name;
  var firstname = form.elements['firstname'].value;
  var lastname = form.elements['lastname'].value;
  var street_address = form.elements['street_address'].value;
  if (firstname == '' && lastname == '' && street_address == '') {
    return true;
  } else {
    return check_form(form_name);
  }
}
//--></script>
  <?php
}

if (strstr($PHP_SELF, FILENAME_ADVANCED_SEARCH )) {
?>
<script type="text/javascript" src="includes/general.js"></script>
<script type="text/javascript"><!--
function check_form() {
  var error_message = unescape("<?php echo xtc_js_lang(JS_ERROR); ?>");
  var error_found = false;
  var error_field;
  var keywords = document.getElementById("advanced_search").keywords.value;
  var pfrom = document.getElementById("advanced_search").pfrom.value;
  var pto = document.getElementById("advanced_search").pto.value;
  var pfrom_float;
  var pto_float;
  if ( (keywords == '' || keywords.length < 1) && (pfrom == '' || pfrom.length < 1) && (pto == '' || pto.length < 1) ) {
    error_message = error_message + unescape("<?php echo xtc_js_lang(JS_AT_LEAST_ONE_INPUT); ?>");
    error_field = document.getElementById("advanced_search").keywords;
    error_found = true;
  }
  if (pfrom.length > 0) {
    pfrom_float = parseFloat(pfrom);
    if (isNaN(pfrom_float)) {
      error_message = error_message + unescape("<?php echo xtc_js_lang(JS_PRICE_FROM_MUST_BE_NUM); ?>");
      error_field = document.getElementById("advanced_search").pfrom;
      error_found = true;
    }
  } else {
    pfrom_float = 0;
  }
  if (pto.length > 0) {
    pto_float = parseFloat(pto);
    if (isNaN(pto_float)) {
      error_message = error_message + unescape("<?php echo xtc_js_lang(JS_PRICE_TO_MUST_BE_NUM); ?>");
      error_field = document.getElementById("advanced_search").pto;
      error_found = true;
    }
  } else {
    pto_float = 0;
  }
  if ( (pfrom.length > 0) && (pto.length > 0) ) {
    if ( (!isNaN(pfrom_float)) && (!isNaN(pto_float)) && (pto_float < pfrom_float) ) {
      error_message = error_message + unescape("<?php echo xtc_js_lang(JS_PRICE_TO_LESS_THAN_PRICE_FROM); ?>");
      error_field = document.getElementById("advanced_search").pto;
      error_found = true;
    }
  }
  if (error_found == true) {
    alert(error_message);
    error_field.focus();
    return false;
  }
}
function popupWindow(url) {
  window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=450,height=280,screenX=150,screenY=150,top=150,left=150')
}
//--></script>
<?php
}

if (strstr($PHP_SELF, FILENAME_PRODUCT_REVIEWS_WRITE )) {
?>
<script type="text/javascript"><!--
function checkForm() {
  var error = 0;
  var error_message = unescape("<?php echo xtc_js_lang(JS_ERROR); ?>");
  var review = document.getElementById("product_reviews_write").review.value;
  if (review.length < <?php echo REVIEW_TEXT_MIN_LENGTH; ?>) {
    error_message = error_message + unescape("<?php echo xtc_js_lang(JS_REVIEW_TEXT); ?>");
    error = 1;
  }
  if (!((document.getElementById("product_reviews_write").rating[0].checked) || (document.getElementById("product_reviews_write").rating[1].checked) || (document.getElementById("product_reviews_write").rating[2].checked) || (document.getElementById("product_reviews_write").rating[3].checked) || (document.getElementById("product_reviews_write").rating[4].checked))) {
    error_message = error_message + unescape("<?php echo xtc_js_lang(JS_REVIEW_RATING); ?>");
    error = 1;
  }
  if (error == 1) {
    alert(error_message);
    return false;
  } else {
    return true;
  }
}
//--></script>
<?php
}
if (strstr($PHP_SELF, FILENAME_POPUP_IMAGE )) {
?>
<script type="text/javascript"><!--
var i=0;
function resize() {
  if (navigator.appName == 'Netscape') i=40;
  if (document.images[0]) window.resizeTo(document.images[0].width +30, document.images[0].height+60-i);
  self.focus();
}
//--></script>
<?php 
}
?>
</head>
<body<?php if(strstr($PHP_SELF, FILENAME_POPUP_IMAGE )) echo ' onload="resize();"'; ?>>
<?php

// econda tracking
if (TRACKING_ECONDA_ACTIVE=='true') { ?>
<script type="text/javascript"><!--
var emos_kdnr='<?php echo TRACKING_ECONDA_ID; ?>';
//--></script>
<a name="emos_sid" rel="<?php echo session_id(); ?>" rev=""></a>
<a name="emos_name" title="siteid" rel="<?php echo $_SESSION['languages_id']; ?>" rev=""></a>
<?php
}

if (strstr($PHP_SELF, FILENAME_CHECKOUT_SUCCESS) && GOOGLE_CONVERSION == 'true') {
  require('includes/google_conversiontracking.js.php');
}

// include needed functions
require_once('inc/xtc_output_warning.inc.php');
require_once('inc/xtc_image.inc.php');
require_once('inc/xtc_parse_input_field_data.inc.php');
require_once('inc/xtc_draw_separator.inc.php');

// check if the 'install' directory exists, and warn of its existence
if (WARN_INSTALL_EXISTENCE == 'true') {
  if (file_exists(dirname($_SERVER['SCRIPT_FILENAME']) . '/' . DIR_MODIFIED_INSTALLER)) {
    xtc_output_warning(sprintf(WARNING_INSTALL_DIRECTORY_EXISTS, dirname($_SERVER['SCRIPT_FILENAME']) . '/_installer'));
  }
}

// check if the configure.php file is writeable
if (WARN_CONFIG_WRITEABLE == 'true') {
  if ( (file_exists(dirname($_SERVER['SCRIPT_FILENAME']) . '/includes/configure.php')) && (is_writeable(dirname($_SERVER['SCRIPT_FILENAME']) . '/includes/configure.php')) ) {
    xtc_output_warning(sprintf(WARNING_CONFIG_FILE_WRITEABLE, dirname($_SERVER['SCRIPT_FILENAME']) . '/includes/configure.php'));
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
if ( (function_exists('ini_get')) && (WARN_SESSION_AUTO_START == 'true') ) {
  if (ini_get('session.auto_start') == '1') {
    xtc_output_warning(WARNING_SESSION_AUTO_START);
  }
}

if ( (WARN_DOWNLOAD_DIRECTORY_NOT_READABLE == 'true') && (DOWNLOAD_ENABLED == 'true') ) {
  if (!is_dir(DIR_FS_DOWNLOAD)) {
    xtc_output_warning(WARNING_DOWNLOAD_DIRECTORY_NON_EXISTENT);
  }
}

$smarty->assign('navtrail',$breadcrumb->trail(' &raquo; '));
if (isset($_SESSION['customer_id'])) {
	$smarty->assign('logoff',xtc_href_link(FILENAME_LOGOFF, '', 'SSL'));
} else {
	$smarty->assign('login',xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
}
$smarty->assign('index',xtc_href_link(FILENAME_DEFAULT));
if ( $_SESSION['account_type']=='0') {
$smarty->assign('account',xtc_href_link(FILENAME_ACCOUNT, '', 'SSL'));
}
$smarty->assign('cart',xtc_href_link(FILENAME_SHOPPING_CART, '', 'NONSSL'));
$smarty->assign('checkout',xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
$smarty->assign('store_name', encode_htmlspecialchars(TITLE));

if (isset($_GET['error_message']) && xtc_not_null($_GET['error_message'])) {
	$smarty->assign('error','<p class="errormessage">'. encode_htmlspecialchars(urldecode($_GET['error_message'])).'</p>');
}
if (isset($_GET['info_message']) && xtc_not_null($_GET['info_message'])) {
	$smarty->assign('error','<p class="errormessage">'.encode_htmlspecialchars($_GET['info_message']).'</p>');
}

include(DIR_WS_INCLUDES.FILENAME_BANNER);

//SHOP OFFLINE INFO
if(xtc_get_shop_conf('SHOP_OFFLINE') == 'checked' && $_SESSION['customers_status']['customers_status_id'] != 0) {	
	$smarty->assign('language', $_SESSION['language']);
	$smarty->assign('shop_offline_msg', xtc_get_shop_conf('SHOP_OFFLINE_MSG'));	
  $smarty->display(CURRENT_TEMPLATE.'/offline.html');	
	EXIT;
}

//BOF - Dokuman - 2012-06-19 - BILLSAFE payment module (BillSAFE-Layer Start)
if (defined('MODULE_PAYMENT_BILLSAFE_2_LAYER')) {
  if (preg_match('/checkout_payment/',$_SERVER['PHP_SELF']) && MODULE_PAYMENT_BILLSAFE_2_LAYER == 'True') {
    if (isset($_GET['payment_error'])) {
      $bs_error = stripslashes(html_entity_decode('payment_error='.$_GET['payment_error'].'&error_message='.$_GET['error_message']));
    } else {
      $bs_error = '';
    }
    echo '<script type="text/javascript"><!--
      if (top.lpg) top.lpg.close("'.str_replace('&amp;', '&', xtc_href_link(FILENAME_CHECKOUT_PAYMENT, $bs_error, 'SSL')).'");
    --></script>';
  }
  if (preg_match('/checkout_success/',$_SERVER['PHP_SELF']) && MODULE_PAYMENT_BILLSAFE_2_LAYER == 'True') {
    echo '<script type="text/javascript"><!--
      if (top.lpg) top.lpg.close("'.xtc_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL').'");
    --></script>';
  }
}
//EOF - Dokuman - 2012-06-19 - BILLSAFE payment module - BillSAFE-Layer End
?>
