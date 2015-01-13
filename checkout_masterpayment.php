<?php
/****************************************************** 
 * Masterpayment Modul for modified eCommerce Shopsoftware 
 * Version 3.5.1
 * Copyright (c) 2010-2012 by K-30 | Florian Ressel 
 *
 * support@k-30.de | www.k-30.de
 * ----------------------------------------------------
 *
 * $Id: checkout_masterpayment.php 19.06.2013 09:12 $
 *	
 *	The Modul based on:
 *  XT-Commerce - community made shopping
 *  http://www.xt-commerce.com
 *
 *  Copyright (c) 2003 XT-Commerce
 *
 *	Released under the GNU General Public License
 *
 ******************************************************/

include ('includes/application_top.php');
// create smarty elements
$smarty = new Smarty;
// include boxes
require (DIR_FS_CATALOG . 'templates/' . CURRENT_TEMPLATE . '/source/boxes.php');

$action = $_GET['action'];

if($action == '' or !isset($action) or $action != 'response')
{	
	// if the customer is not logged on, redirect them to the login page
	if (!isset ($_SESSION['customer_id'])) {
		if (ACCOUNT_OPTIONS == 'guest') {
			xtc_redirect(xtc_href_link(FILENAME_CREATE_GUEST_ACCOUNT, '', 'SSL'));
		} else {
			xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
		}
	}
	
	// if there is nothing in the customers cart, redirect them to the shopping cart page
	if ($_SESSION['cart']->count_contents() < 1)
		xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART));
	
	// if no shipping method has been selected, redirect the customer to the shipping method selection page
	if (!isset ($_SESSION['shipping']))
		xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
	
	// avoid hack attempts during the checkout procedure by checking the internal cartID
	if (isset ($_SESSION['cart']->cartID) && isset ($_SESSION['cartID'])) {
		if ($_SESSION['cart']->cartID != $_SESSION['cartID'])
			xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
	}
	
	
	if ((!isset($_SESSION['cart_Masterpayment_ID']) && empty($_SESSION['cart_Masterpayment_ID'])) or (substr($_SESSION['payment'], 0, strpos($_SESSION['payment'], '_')) != 'masterpayment')) {	
		xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'NONSSL'));		
	}
}

$breadcrumb->add(NAVBAR_TITLE_1_CHECKOUT_PAYMENT, xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2_CHECKOUT_PAYMENT, xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));

$smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');

if($action == 'response')
{
	require_once('includes/external/masterpayment/MasterpaymentResponse.class.php');  
	$MasterpaymentResponse = new MasterpaymentResponse($_GET);
	
	require (DIR_WS_INCLUDES . 'header.php');
	
	if(@file_exists('lang/' . $_SESSION['language'] . '/masterpayment_callback.php'))
	{
		include('lang/' . $_SESSION['language'] . '/masterpayment_callback.php');
	}
	
	if(@file_exists('lang/' . $_SESSION['language'] . '/modules/payment/masterpayment_' . $_GET['payment_method'] . '.php'))
	{
		include('lang/' . $_SESSION['language'] . '/modules/payment/masterpayment_' . $_GET['payment_method'] . '.php');
		$smarty->assign('masterpayment_payment_title', constant('MODULE_PAYMENT_MASTERPAYMENT_'.strtoupper($_GET['payment_method']).'_CHECKOUT_TITLE'));
	}

	$smarty->assign('masterpayment_message', $_masterpaymentCallbackMessages[strtoupper($_GET['response'])]);		
	
	$main_content = $smarty->fetch(CURRENT_TEMPLATE . '/module/masterpayment_response.html');
} elseif($action == 'request') {	
	require_once('includes/external/masterpayment/MasterpaymentRequest.class.php');  
	$masterpayment = new MasterpaymentRequest();
	
	if(@file_exists('lang/' . $_SESSION['language'] . '/modules/payment/masterpayment_config.php'))
	{
		include('lang/' . $_SESSION['language'] . '/modules/payment/masterpayment_config.php');
	}

	if($masterpayment->init())
	{
		if(!class_exists(order))
		{
			require (DIR_WS_CLASSES . 'order.php');
		}
	
		$order = new order($masterpayment->order_ID);
		
		$smarty->assign('masterpayment_url', $masterpayment->masterpaymentGatewayURL);
	  $smarty->assign('masterpaymentForm', xtc_draw_form('masterpaymentForm', $masterpayment->masterpaymentGatewayURL, 'post', 'name="masterpaymentForm"'));
		$smarty->assign('request_parameters', $masterpayment->generateRequest());
	} else {
		$smarty->assign('masterpayment_error', 1);
	}

	$smarty->assign('masterpayment_button_text', MODULE_PAYMENT_MASTERPAYMENT_FRAME_BUTTON_TEXT);
	$smarty->assign('masterpayment_error_message', MODULE_PAYMENT_MASTERPAYMENT_ERROR_MESSAGE);
	$smarty->assign('masterpayment_error_button_link', $masterpayment->getShopURL() . 'checkout_payment.php?' . session_name() . '=' . session_id());
	$smarty->assign('masterpayment_error_button_text', MODULE_PAYMENT_MASTERPAYMENT_FRAME_ERROR_BUTTON_TEXT);
	$smarty->display(CURRENT_TEMPLATE . '/module/masterpayment_request.html');	
	exit;
} else {
	require (DIR_WS_INCLUDES . 'header.php');
	
	require_once('includes/external/masterpayment/MasterpaymentActions.class.php');  
	$MasterpaymentActions = new MasterpaymentActions();
	
	$smarty->assign('language', $_SESSION['language']);
	$smarty->assign('masterpayment_request_url', $MasterpaymentActions->getRequestURL());
	$smarty->assign('masterpaymentForm', xtc_draw_form('masterpaymentForm', $MasterpaymentActions->getRequestURL(), 'post', 'name="masterpaymentForm"'));
	
	@include('lang/' . $_SESSION['language'] . '/modules/payment/masterpayment_config.php');
	
	if(@file_exists('lang/' . $_SESSION['language'] . '/modules/payment/' . $_SESSION['payment'] . '.php'))
	{
		include('lang/' . $_SESSION['language'] . '/modules/payment/' . $_SESSION['payment'] . '.php');
		$smarty->assign('masterpayment_button_text', MODULE_PAYMENT_MASTERPAYMENT_FRAME_BUTTON_TEXT); 
		$smarty->assign('masterpayment_payment_title', constant('MODULE_PAYMENT_MASTERPAYMENT_' . strtoupper(str_replace('masterpayment_', '', $_SESSION['payment'])) . '_CHECKOUT_TITLE'));
	}
	
	// BOF GM_MOD
	if(function_exists('gm_get_conf'))
	{
		$smarty->assign('LIGHTBOX', gm_get_conf('GM_LIGHTBOX_CHECKOUT'));
		$smarty->assign('LIGHTBOX_CLOSE', xtc_href_link(FILENAME_DEFAULT, '', 'NONSSL'));
	}
	// EOF GM_MOD
		
	$main_content = $smarty->fetch(CURRENT_TEMPLATE . '/module/checkout_masterpayment.html');
}

$smarty->assign('main_content', $main_content);				          
$smarty->assign('language', $_SESSION['language']);
$smarty->caching = 0;
if (!defined('RM'))
	$smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE . '/index.html');
include ('includes/application_bottom.php');
?>