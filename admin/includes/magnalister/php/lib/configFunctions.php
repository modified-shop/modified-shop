<?php
/**
 * 888888ba                 dP  .88888.                    dP                
 * 88    `8b                88 d8'   `88                   88                
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b. 
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88 
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88 
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P' 
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * $Id: configFunctions.php 2332 2013-04-04 16:12:19Z derpapst $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

function getLanguages(&$form) {
	$langs = MagnaDB::gi()->fetchArray('SELECT * FROM '.TABLE_LANGUAGES);
	$form['values'] = array();
	foreach ($langs as $lang) {
		$form['values'][$lang['languages_id']] = $lang['name'].' ('.$lang['code'].')';
		if (strtolower($lang['code']) == 'de') { /* Deutsch als standard */
			$form['default'] = $lang['languages_id'];
		}
	}
}

function getCountries(&$form) {
	$countries = MagnaDB::gi()->fetchArray('SELECT * FROM '.TABLE_COUNTRIES);
	$form['values'] = array();
	foreach ($countries as $country) {
		$form['values'][$country['countries_id']] = $country['countries_name'].' ('.$country['countries_iso_code_2'].')';
		if (strtolower($country['countries_iso_code_2']) == 'de') { /* Deutschland als standard */
			$form['default'] = $country['countries_id'];
		}
	}
}

function getCountriesWithIso2Keys(&$form) {
	$countries = MagnaDB::gi()->fetchArray('SELECT UPPER(countries_iso_code_2) as iso2, countries_name FROM '.TABLE_COUNTRIES);
	$form['values'] = array();
	foreach ($countries as $country) {
		$form['values'][$country['iso2']] = $country['countries_name'];
		if ($country['iso2'] == 'DE') { /* Deutschland als standard */
			$form['default'] = $country['iso2'];
		}
	}
}

function getShippingMethods(&$form) {
	if (!class_exists('Shipping')) {
		require_once (DIR_MAGNALISTER_INCLUDES.'lib/classes/Shipping.php');
	}
	$shippingClass = new Shipping();
	$shippingMethods = $shippingClass->getShippingMethods();
	$form['values'] = array(
		'__ml_lump' => ML_COMPARISON_SHOPPING_LABEL_LUMP
	);
	if (SHOPSYSTEM == 'gambio') {
		$form['values']['__ml_gambio'] = ML_COMPARISON_SHOPPING_LABEL_ARTICLE_SHIPPING_COSTS;
	}
	if (!empty($shippingMethods)) {
		foreach ($shippingMethods as $method) {
			if ($method['code'] == 'gambioultra') continue;
			$form['values'][$method['code']] = fixHTMLUTF8Entities($method['title']);
		}
	}
	unset($shippingClass);
}

function getOrderStatus(&$form) {
	if (!isset($_SESSION['languages_id'])) {
		$_SESSION['languages_id'] = MagnaDB::gi()->fetchOne(
		'SELECT languages_id '.
		'FROM '.TABLE_LANGUAGES.' l, '.TABLE_CONFIGURATION.' c '.
		'WHERE l.code=c.configuration_value '.
		'AND c.configuration_key=\'DEFAULT_LANGUAGE\'');
	}
	$orders_status_array = MagnaDB::gi()->fetchArray(
		'SELECT orders_status_id, orders_status_name '.
		'FROM '.TABLE_ORDERS_STATUS.' '.
		'WHERE language_id = \''.$_SESSION['languages_id'].'\''
	);
	$form['values'] = array();
	foreach ($orders_status_array as $item) {
		$form['values'][$item['orders_status_id']] = fixHTMLUTF8Entities($item['orders_status_name']);
	}
}

function getCustomersStatus(&$form, $inclAdmin = true) {
	if (MagnaDB::gi()->tableExists(TABLE_CUSTOMERS_STATUS)) {
		$customers_status_array = MagnaDB::gi()->fetchArray(
			'SELECT customers_status_id, customers_status_name '.
			'FROM '.TABLE_CUSTOMERS_STATUS.' '.
			'WHERE language_id = \''.$_SESSION['languages_id'].'\''
		);
		$form['values'] = array();
		foreach ($customers_status_array as $item) {
			if (!$inclAdmin && ($item['customers_status_id'] == '0')) continue;
			if (empty($item['customers_status_name'])) continue;
			$form['values'][$item['customers_status_id']] = fixHTMLUTF8Entities($item['customers_status_name']);
		}
	} else {
		// osCommerce kennt keine Kaeufergruppen
		$form = array();
	}
}

function getPaymentModules(&$form) {
	$payments = explode(';', MODULE_PAYMENT_INSTALLED);
	$lang = (isset($_SESSION['language']) && !empty($_SESSION['language'])) ? $_SESSION['language'] : 'english';
	
	if (MAGNA_SHOW_WARNINGS) error_reporting(error_reporting(E_ALL) ^ E_NOTICE);
	foreach ($payments as $p) {
		if (empty($p)) continue;
		$m = DIR_FS_LANGUAGES.$lang.'/modules/payment/'.$p;
		if (file_exists($m) && is_file($m)) {
			require_once($m);
		}
		$payment = substr($p, 0, strrpos($p, '.'));
		$c = 'MODULE_PAYMENT_'.strtoupper($payment).'_TEXT_TITLE';
		if (!defined($c)) continue;
		$c = trim(strip_tags(constant($c)));
		$form['values'][$payment] = $c;
	}
	if (MAGNA_SHOW_WARNINGS) error_reporting(error_reporting(E_ALL) | E_WARNING | E_NOTICE);
}

function getShippingModules(&$form) {
	$shippings = explode(';', MODULE_SHIPPING_INSTALLED);
	$lang = (isset($_SESSION['language']) && !empty($_SESSION['language'])) ? $_SESSION['language'] : 'english';
	
	if (MAGNA_SHOW_WARNINGS) error_reporting(error_reporting(E_ALL) ^ E_NOTICE);
	foreach ($shippings as $s) {
		if (empty($s)) continue;
		$m = DIR_FS_LANGUAGES.$lang.'/modules/shipping/'.$s;
		if (file_exists($m) && is_file($m)) {
			require_once($m);
		}
		$shipping = substr($s, 0, strrpos($s, '.'));
		$c = 'MODULE_SHIPPING_'.strtoupper($shipping).'_TEXT_TITLE';
		if (!defined($c)) continue;
		$c = trim(strip_tags(constant($c)));
		$form['values'][$shipping] = $c;
	}
	if (MAGNA_SHOW_WARNINGS) error_reporting(error_reporting(E_ALL) | E_WARNING | E_NOTICE);
}

function getProductOptions(&$form) {
	if (!isset($_SESSION['languages_id'])) {
		$_SESSION['languages_id'] = MagnaDB::gi()->fetchOne(
		'SELECT languages_id '.
		'FROM '.TABLE_LANGUAGES.' l, '.TABLE_CONFIGURATION.' c '.
		'WHERE l.code=c.configuration_value '.
		'AND c.configuration_key=\'DEFAULT_LANGUAGE\'');
	}
	$products_options_array = MagnaDB::gi()->fetchArray(
		'SELECT products_options_id, products_options_name '.
		'FROM '.TABLE_PRODUCTS_OPTIONS.' '.
		'WHERE language_id = \''.$_SESSION['languages_id'].'\''
	);
	$form['values'] = array();
	foreach ($products_options_array as $item) {
		$form['values'][$item['products_options_id']] = fixHTMLUTF8Entities($item['products_options_name']);
	}
}
