<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2008 Gambio OHG - login_admin.php 2008-08-10 gambio - http://www.gambio.de

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
   
defined( '_MODIFIED_SHOP_LOGIN' ) or die( 'Direct Access to this location is not allowed.' );

include ('includes/application_top.php');

define('LOGIN_NUM', 2);
defined('MODULE_CAPTCHA_CODE_LENGTH') or define('MODULE_CAPTCHA_CODE_LENGTH', 6);

// create smarty elements
$smarty = new Smarty;

if (!isset($_SESSION['customers_login_tries'])) {
  $_SESSION['customers_login_tries'] = 0;
}

if (isset($_GET['info_message']) && xtc_not_null($_GET['info_message'])) {
  $messageStack->add('login', get_message('info_message'));
}

if ($messageStack->size('login') > 0) {
	$smarty->assign('info_message', $messageStack->output('login'));
}

$smarty->assign('FORM_ACTION', xtc_draw_form('login', xtc_href_link(FILENAME_LOGIN, xtc_get_all_get_params().'action=process', 'SSL')));
$smarty->assign('INPUT_MAIL', xtc_draw_input_field('email_address'));
$smarty->assign('INPUT_PASSWORD', xtc_draw_password_field('password'));
$smarty->assign('LINK_LOST_PASSWORD', xtc_href_link(FILENAME_PASSWORD_DOUBLE_OPT, '', 'SSL'));
$smarty->assign('FORM_END', '</form>');

// captcha
if ($_SESSION['customers_login_tries'] >= LOGIN_NUM) {
  $smarty->assign('VVIMG', '<img src="'.xtc_href_link(FILENAME_DISPLAY_VVCODES, '', 'SSL').'" alt="Captcha" />');
  $smarty->assign('INPUT_CODE', xtc_draw_input_field('vvcode', '', 'size="'.MODULE_CAPTCHA_CODE_LENGTH.'" maxlength="'.MODULE_CAPTCHA_CODE_LENGTH.'"', 'text', false));
}

$smarty->assign('charset', $_SESSION['language_charset']);
$smarty->assign('language', $_SESSION['language']);
$smarty->caching = 0;

$smarty->display(CURRENT_TEMPLATE.'/login_shop.html');
