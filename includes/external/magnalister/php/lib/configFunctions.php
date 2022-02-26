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
 * $Id$
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

function mlGetLanguages(&$form) {
	$sSql = 'SELECT * FROM '.TABLE_LANGUAGES;
	if (SHOPSYSTEM === 'xtcmodified' && MagnaDB::gi()->columnExistsInTable('status_admin', TABLE_LANGUAGES)) {
		$sSql .= " WHERE status_admin='1'"; // admin can view and edit
	}
	
	$langs = MagnaDB::gi()->fetchArray($sSql);
	$form['values'] = array();
	foreach ($langs as $lang) {
		$form['values'][$lang['languages_id']] = $lang['name'].' ('.$lang['code'].')';
		if (strtolower($lang['code']) == 'de') { /* Deutsch als standard */
			$form['default'] = $lang['languages_id'];
		}
	}
}

function mlGetCountries(&$form) {
	$countries = MagnaDB::gi()->fetchArray('SELECT * FROM '.TABLE_COUNTRIES);
	$form['values'] = array();
	foreach ($countries as $country) {
		$form['values'][$country['countries_id']] = $country['countries_name'].' ('.$country['countries_iso_code_2'].')';
		if (strtolower($country['countries_iso_code_2']) == 'de') { /* Deutschland als standard */
			$form['default'] = $country['countries_id'];
		}
	}
}

function mlGetCountriesWithIso2Keys(&$form) {
	$countries = MagnaDB::gi()->fetchArray('SELECT UPPER(countries_iso_code_2) as iso2, countries_name FROM '.TABLE_COUNTRIES);
	$form['values'] = array();
	foreach ($countries as $country) {
		$form['values'][$country['iso2']] = $country['countries_name'];
		if ($country['iso2'] == 'DE') { /* Deutschland als standard */
			$form['default'] = $country['iso2'];
		}
	}
}

function mlGetShippingMethods(&$form) {
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
	if (MagnaDB::gi()->columnExistsInTable('products_weight', TABLE_PRODUCTS)) {
		$form['values']['__ml_weight'] = ML_LABEL_SHIPPINGCOSTS_EQ_ARTICLEWEIGHT;
	}
	if (!empty($shippingMethods)) {
		foreach ($shippingMethods as $method) {
			if ($method['code'] == 'gambioultra') continue;
			$form['values'][$method['code']] = fixHTMLUTF8Entities($method['title']);
		}
	}
	unset($shippingClass);
}

function mlGetOrderStatus(&$form) {
	if (!isset($_SESSION['languages_id'])) {
		if (defined('DEFAULT_LANGUAGE')) {
			$_SESSION['languages_id'] = MagnaDB::gi()->fetchOne("
				SELECT `languages_id`
				  FROM ".TABLE_LANGUAGES." l
				 WHERE l.`code` = '".DEFAULT_LANGUAGE."'
			");
		} else {
            if (defined('ML_GAMBIO_41_NEW_CONFIG_TABLE')) {
                $_SESSION['languages_id'] = MagnaDB::gi()->fetchOne("
                    SELECT `languages_id`
                      FROM ".TABLE_LANGUAGES." l, ".TABLE_CONFIGURATION." c
                     WHERE     l.`code` = c.`value`
                           AND c.`key` = 'configuration/DEFAULT_LANGUAGE'
                ");
            } else {
                $_SESSION['languages_id'] = MagnaDB::gi()->fetchOne("
                    SELECT `languages_id`
                      FROM ".TABLE_LANGUAGES." l, ".TABLE_CONFIGURATION." c 
                    WHERE l.`code` = c.`configuration_value` 
                    AND c.`configuration_key` = 'DEFAULT_LANGUAGE'
                ");
            }
		}
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

function mlGetCustomersStatus(&$form, $inclAdmin = true) {
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

function mlGetPaymentModules(&$form) {
	global $_magnaLanguage;
	$payments = explode(';', MODULE_PAYMENT_INSTALLED);
	
	if (MAGNA_SHOW_WARNINGS) error_reporting(error_reporting(E_ALL) ^ E_NOTICE);
	foreach ($payments as $p) {
		if (empty($p)) continue;
		$payment = substr(basename($p), 0, strrpos($p, '.'));
		$c = 'MODULE_PAYMENT_'.strtoupper($payment).'_TEXT_TITLE';
		$m = DIR_FS_LANGUAGES.$_magnaLanguage.'/modules/payment/'.$p;
		mlLoadModuleLanguageDefines($m);
		if (!defined($c) && file_exists($m) && is_file($m)) {
			try {
				require_once($m);
			} catch (Exception $e) {}
		}
		if (!defined($c)) continue;
		$c = trim(strip_tags(constant($c)));
		$form['values'][$payment] = $c;
	}
	if (MAGNA_SHOW_WARNINGS) error_reporting(error_reporting(E_ALL) | E_WARNING | E_NOTICE);
}

function mlGetShippingModules(&$form) {
	global $_magnaLanguage;
	$shippings = explode(';', MODULE_SHIPPING_INSTALLED);
	
	if (MAGNA_SHOW_WARNINGS) error_reporting(error_reporting(E_ALL) ^ E_NOTICE);
	foreach ($shippings as $s) {
		if (empty($s)) continue;
		$shipping = substr(basename($s), 0, strrpos($s, '.'));
		$c = 'MODULE_SHIPPING_'.strtoupper($shipping).'_TEXT_TITLE';
		$m = DIR_FS_LANGUAGES.$_magnaLanguage.'/modules/shipping/'.$s;
		mlLoadModuleLanguageDefines($m);
		if (!defined($c) && file_exists($m) && is_file($m)) {
			try {
				require_once($m);
			} catch (Exception $e) {}
		}
		if (!defined($c)) continue;
		$c = trim(strip_tags(constant($c)));
		$form['values'][$shipping] = $c;
	}
	if (MAGNA_SHOW_WARNINGS) error_reporting(error_reporting(E_ALL) | E_WARNING | E_NOTICE);
}

function mlGetProductOptions(&$form) {
	if (!isset($_SESSION['languages_id'])) {
        if (defined('DEFAULT_LANGUAGE')) {
            $_SESSION['languages_id'] = MagnaDB::gi()->fetchOne("
				SELECT `languages_id`
				  FROM ".TABLE_LANGUAGES." l
				 WHERE l.`code` = '".DEFAULT_LANGUAGE."'
			");
        } else {
            if (defined('ML_GAMBIO_41_NEW_CONFIG_TABLE')) {
                $_SESSION['languages_id'] = MagnaDB::gi()->fetchOne("
                    SELECT `languages_id`
                      FROM ".TABLE_LANGUAGES." l, ".TABLE_CONFIGURATION." c
                     WHERE     l.`code` = c.`value`
                           AND c.`key` = 'configuration/DEFAULT_LANGUAGE'
                ");
            } else {
                $_SESSION['languages_id'] = MagnaDB::gi()->fetchOne("
                    SELECT `languages_id`
                      FROM ".TABLE_LANGUAGES." l, ".TABLE_CONFIGURATION." c 
                    WHERE l.`code` = c.`configuration_value` 
                    AND c.`configuration_key` = 'DEFAULT_LANGUAGE'
                ");
            }
        }
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

function mlGetManufacturers(&$form){
	$manufacturers = MagnaDB::gi()->fetchArray('
	    SELECT manufacturers_id, manufacturers_name 
	      FROM '.TABLE_MANUFACTURERS.'
	     WHERE manufacturers_id<>0
	  ORDER BY manufacturers_name ASC
	');
	
	$form['values'] = array();
	
	if (!empty($manufacturers)) {
		foreach ($manufacturers as $manufacturer) {
			$form['values'][$manufacturer['manufacturers_id']] = fixHTMLUTF8Entities($manufacturer['manufacturers_name']);
		}
	}
}

function mlGetShippingStatus(&$form) {
	$data = MagnaDB::gi()->fetchArray('
	    SELECT shipping_status_id as id, shipping_status_name as name
	      FROM '.TABLE_SHIPPING_STATUS.'
	     WHERE language_id = '.$_SESSION['languages_id'].'
	  ORDER BY shipping_status_id ASC
	');
	
	$form['values'] = array();

	if (!empty($data)) {
		foreach ($data as $elem) {
			$form['values'][$elem['id']] = fixHTMLUTF8Entities($elem['name']);
		}
	}
}

/**
 * Preset the tracking and carrier matching if Gambio has "orders_parcel_tracking_codes" table
 * @param $sMarketplaceId
 * @param $sConfigKeyCarrier
 * @param $sConfigKeyTrackingCode
 */
function mlPresetTrackingCodeMatching($sMarketplaceId, $sConfigKeyCarrier, $sConfigKeyTrackingCode) {
	if (   MagnaDB::gi()->tableExists('orders_parcel_tracking_codes')
		&& !MagnaDB::gi()->recordExists(TABLE_MAGNA_CONFIG, array('mpID' => $sMarketplaceId, 'mkey' => $sConfigKeyCarrier.'.table'))
		&& !MagnaDB::gi()->recordExists(TABLE_MAGNA_CONFIG, array('mpID' => $sMarketplaceId, 'mkey' => $sConfigKeyTrackingCode.'.table'))
		&& !MagnaDB::gi()->recordExists(TABLE_MAGNA_CONFIG, array('mpID' => $sMarketplaceId, 'mkey' => $sConfigKeyCarrier.'.alias'))
		&& !MagnaDB::gi()->recordExists(TABLE_MAGNA_CONFIG, array('mpID' => $sMarketplaceId, 'mkey' => $sConfigKeyTrackingCode.'.alias'))
	) {
		MagnaDB::gi()->insert(TABLE_MAGNA_CONFIG, array(
			'mpID' => $sMarketplaceId,
			'mkey' => $sConfigKeyCarrier.'.table',
			'value' => json_encode(array(
				'table' => 'orders_parcel_tracking_codes',
				'column' => 'parcel_service_name',
			)),
		));
		MagnaDB::gi()->insert(TABLE_MAGNA_CONFIG, array(
			'mpID' => $sMarketplaceId,
			'mkey' => $sConfigKeyCarrier.'.alias',
			'value' => 'order_id',
		));

		MagnaDB::gi()->insert(TABLE_MAGNA_CONFIG, array(
			'mpID' => $sMarketplaceId,
			'mkey' => $sConfigKeyTrackingCode.'.table',
			'value' => json_encode(array(
				'table' => 'orders_parcel_tracking_codes',
				'column' => 'tracking_code',
			)),
		));
		MagnaDB::gi()->insert(TABLE_MAGNA_CONFIG, array(
			'mpID' => $sMarketplaceId,
			'mkey' => $sConfigKeyTrackingCode.'.alias',
			'value' => 'order_id',
		));
	}
}

/**
 * Preset the tracking and return tracking key if Gambio has "orders_parcel_tracking_codes" table
 * @param $sMarketplaceId
 * @param $sConfigKeyTrackingCode
 */
function mlPresetTrackingAndReturnTrackingCodeMatching($sMarketplaceId, $sConfigKeyTrackingCode) {
    // Column "is_return_delivery" exists since gambio 4.5+ check if table and column exists
    if (strpos($sConfigKeyTrackingCode, 'return')
        || !MagnaDB::gi()->tableExists('orders_parcel_tracking_codes')
        || !MagnaDB::gi()->columnExistsInTable('is_return_delivery','orders_parcel_tracking_codes')
    ) {
        return false;
    }

    if (   MagnaDB::gi()->tableExists('orders_parcel_tracking_codes')
        && !MagnaDB::gi()->recordExists(TABLE_MAGNA_CONFIG, array('mpID' => $sMarketplaceId, 'mkey' => $sConfigKeyTrackingCode.'.table'))
        && !MagnaDB::gi()->recordExists(TABLE_MAGNA_CONFIG, array('mpID' => $sMarketplaceId, 'mkey' => $sConfigKeyTrackingCode.'.alias'))
    ) {
        MagnaDB::gi()->insert(TABLE_MAGNA_CONFIG, array(
            'mpID' => $sMarketplaceId,
            'mkey' => $sConfigKeyTrackingCode.'.table',
            'value' => json_encode(array(
                'table' => 'orders_parcel_tracking_codes',
                'column' => 'tracking_code',
            )),
        ));
        MagnaDB::gi()->insert(TABLE_MAGNA_CONFIG, array(
            'mpID' => $sMarketplaceId,
            'mkey' => $sConfigKeyTrackingCode.'.alias',
            'value' => 'order_id',
        ));
    }
}
