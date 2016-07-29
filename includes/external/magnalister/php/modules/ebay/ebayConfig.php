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
 * $Id: ebayConfig.php 733 2011-01-21 07:42:58Z derpapst $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/Configurator.php');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/ShopAddOns.php');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');
include_once(DIR_MAGNALISTER_INCLUDES.'lib/configFunctions.php');
require_once(DIR_MAGNALISTER_MODULES.'ebay/classes/eBayShippingDetailsProcessor.php');

function renderAuthError($authError) {
	global $_MagnaSession;
	global $magnaConfig;
	$errors = array();
	if (array_key_exists('ERRORS', $authError) && !empty($authError['ERRORS'])) {
		foreach ($authError['ERRORS'] as $err) {
			$errors[] = $err['ERRORMESSAGE'];
		}
	}
	# schreib in die Shop-DB dass Token nicht gueltig
	removeDBConfigValue('ebay.token', $_MagnaSession['mpID']);
	unset($magnaConfig['db'][$_MagnaSession['mpID']]['ebay.token']);
	removeDBConfigValue('ebay.authed', $_MagnaSession['mpID']);
    return '<p class="errorBox">
     	<span class="error bold larger">'.ML_ERROR_LABEL.':</span>
     	'.ML_ERROR_EBAY_WRONG_TOKEN.(
     		(!empty($errors))
     			? '<br /><br />'.implode('<br />', $errors)
     			: ''
     	).'</p>';
}

function magnaUpdateCurrencyValues($args) {
	global $magnaConfig;
	
	$ret = '';
	if (array_key_exists($args['value'], $magnaConfig['ebay']['currencies']) && 
		!empty($magnaConfig['ebay']['currencies'][$args['value']])
	) {
		foreach ($magnaConfig['ebay']['currencies'][$args['value']] as $key => $val) {
			$ret .= '<option value="'.$val.'">'.$val.'</option>';
		}
	} else {
		$ret = 'FAILURE';
	}
	return $ret;
}

/*function magnaConstraintPrefilledInfoSetting($args) {
    global $magnaConfig;

}*/

function eBayGenToken($args, &$value = '') {
	global $_MagnaSession, $_url;
	$expires = getDBConfigValue('ebay.token.expires', $_MagnaSession['mpID'], '');
	$firstToken = '';
	if (!empty($expires)) {
		if(is_numeric($expires))
			$expires = sprintf(ML_EBAY_TEXT_TOKEN_EXPIRES_AT, date('d.m.Y H:i:s', $expires));
		else
			$expires = sprintf(ML_EBAY_TEXT_TOKEN_EXPIRES_AT, date('d.m.Y H:i:s', unix_timestamp($expires)));
	} else {
		$firstToken = ' mlbtn-action';
	}
	return '<input class="ml-button'.$firstToken.' mlbtn-action" type="button" value="'.ML_EBAY_BUTTON_TOKEN_NEW.'" id="requestToken"/>
	'.$expires.'
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function() {
	$(\'#requestToken\').click(function() {
		jQuery.blockUI(blockUILoading);
		jQuery.ajax({
			\'method\': \'get\',
			\'url\': \''.toURL($_url, array('what' => 'GetTokenCreationLink', 'kind' => 'ajax'), true).'\',
			\'success\': function (data) {
				jQuery.unblockUI();
				myConsole.log(\'ajax.success\', data);
				if (data == \'error\') {
					$(\'<div></div>\')
						.attr(\'title\', '.json_encode(ML_EBAY_ERROR_CREATE_TOKEN_LINK_HEADLINE).')
						.html('.json_encode(ML_EBAY_ERROR_CREATE_TOKEN_LINK_TEXT).')
						.jDialog();
				} else {
		          		var hwin = window.open(data, "popup", "resizable=yes,scrollbars=yes");
		          		if (hwin.focus) {
		 				hwin.focus();
		            		}
				}
			}
		});
	});
});
/*]]>*/</script>';
}

function eBayTopTenConfig($aArgs = array(), &$sValue = '') {
	global $_MagnaSession;
	require_once DIR_MAGNALISTER_FS.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'ebay'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'ebayTopTen.php';
	$oTopTen = new ebayTopTen();
	$oTopTen->setMarketPlaceId($_MagnaSession['mpID']);
	if (isset($_GET['what'])) {
		if(!isset($_GET['tab'])) {
			echo $oTopTen->renderConfig();
		} elseif ($_GET['tab'] == 'init') {
			echo $oTopTen->renderConfigCopy(isset($_GET['execute']) && ($_GET['execute'] == 'true'));
		} elseif ($_GET['tab'] == 'delete') {
			echo $oTopTen->renderConfigDelete(
				isset($_POST['delete'])
				?$_POST['delete']
				:array()
			);
		}
	} else {
		return $oTopTen->renderMain(
			$aArgs['key'],
			isset($_POST['conf'][$aArgs['key']])
			? (int)$_POST['conf'][$aArgs['key']]
			: (int)getDBConfigValue($aArgs['key'], $_MagnaSession['mpID'], 10)
		);
	}
}

function eBayShippingConfig($args, &$value = '') {
	global $_MagnaSession;
	$shipProc = new eBayShippingDetailsProcessor($args, 'conf', array(
		'mp' => $_MagnaSession['mpID'],
		'mode' => 'conf'
	), $value);
	return $shipProc->process();
}

function tokenAvailable() {
	global $_MagnaSession;
	$mpID = $_MagnaSession['mpID'];
	try {
		$result = MagnaConnector::gi()->submitRequest(array(
			'ACTION' => 'CheckIfTokenAvailable'
		));
		if ('true' == $result['DATA']['TokenAvailable']) {
			setDBConfigValue('ebay.token', $_MagnaSession['mpID'], '__saved__', true);
			setDBConfigValue('ebay.token.expires', $_MagnaSession['mpID'], $result['DATA']['TokenExpirationTime'], true);
			return true;
		}
	} catch (MagnaException $e) {}
	return false;
}

$_url['mode'] = 'conf';

if (isset($_GET['what'])) {
	if($_GET['what'] == 'GetTokenCreationLink') {
		$iframeURL = 'error';
		try {
			//*
			$result = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'GetTokenCreationLink'
			));
			$iframeURL = $result['DATA']['tokenCreationLink'];
			//*/
		} catch (MagnaException $e) { }
		echo $iframeURL;
		#require(DIR_WS_INCLUDES . 'application_bottom.php');
		exit();
	} elseif ($_GET['what'] == 'topTenConfig'){
		ebayTopTenConfig();
		exit();
	}
}

if (SHOPSYSTEM == 'gambio') {
	$sGambioVarPicsExpla = ML_EBAY_GAMBIO_VARIATIONPICS_EXPLANATION;
} else {
	$sGambioVarPicsExpla = '';
}

$form = loadConfigForm($_lang,
	array(
		'ebay.form' => array(),
		'email_template_generic.form' => array()
	), array(
		'_#_platform_#_' => $_MagnaSession['currentPlatform'],
		'_#_platformName_#_' => $_modules[$_Marketplace]['title'],
		'_#_ebayGambioVariationpicsExplanation_#_' => $sGambioVarPicsExpla
	)
);

$cG = new MLConfigurator($form, $_MagnaSession['mpID'], 'conf_ebay');

$boxes = '';
$auth = getDBConfigValue('ebay.authed', $_MagnaSession['mpID'], false);
if (   (!is_array($auth) || !$auth['state'])
	&& allRequiredConfigKeysAvailable($authConfigKeys, $_MagnaSession['mpID'])
	&& !(   array_key_exists('conf', $_POST)
		 && allRequiredConfigKeysAvailable($authConfigKeys, $_MagnaSession['mpID'], $_POST['conf'])
	)
	&& isset($authError)
) {
    $boxes .= renderAuthError($authError);
}

if (array_key_exists('conf', $_POST)) {
	$nUser = trim($_POST['conf']['ebay.username']);
	$nPass = trim($_POST['conf']['ebay.password']);
	$nSite = $_POST['conf']['ebay.site'];
	setDBConfigValue('ebay.site', $_MagnaSession['mpID'], $nSite, true);

    if (!empty($nUser) && (getDBConfigValue('ebay.password', $_MagnaSession['mpID']) == '__saved__') && empty($nPass)) {
        $nPass = '__saved__'; 
    }

    if ((strpos($nPass, '&#9679;') === false) && (strpos($nPass, '&#8226;') === false)) {

        if (!empty($nUser) && !empty($nPass)) {
            try {
                $result = MagnaConnector::gi()->submitRequest(array(
                    'ACTION' => 'SetCredentials',
                    'USERNAME' => $nUser,
                    'PASSWORD' => $nPass,
                ));
            } catch (MagnaException $e) {
                $boxes .= '
                    <p class="errorBox">'.ML_GENERIC_STATUS_LOGIN_SAVEERROR.'</p>
                ';
            }
        } else {
            $boxes .= '
                <p class="errorBox">'.ML_ERROR_INVALID_PASSWORD.'</p>';
	    }
	}

	unset($currencyError);
	$sp = new SimplePrice();
	if ($auth['state'] && isset($_POST['conf']['ebay.currency']) && !$sp->currencyExists($_POST['conf']['ebay.currency'])) {
		//removeDBConfigValue('ebay.validconfig', $_MagnaSession['mpID']);
		$boxes .= '<p class="errorBox">'.sprintf(
			ML_GENERIC_ERROR_CURRENCY_NOT_IN_SHOP,
			$_POST['conf']['ebay.currency']
		).'</p>';
	}/* else {
		setDBConfigValue('ebay.validconfig', $_MagnaSession['mpID'], true, true);
	}
*/

	// Tracking-Code-Matching only one of both settings for carrier is set display notice
	if ((      isset($_POST['conf']['ebay.orderstatus.carrier.default'])
			&& isset($_POST['conf']['ebay.orderstatus.carrier.dbmatching.table']['table'])
			&& isset($_POST['conf']['ebay.orderstatus.trackingcode.dbmatching.table']['table'])
		)
		&& ((      empty($_POST['conf']['ebay.orderstatus.carrier.default'])
				&& empty($_POST['conf']['ebay.orderstatus.carrier.dbmatching.table']['table'])
			)
			&& !empty($_POST['conf']['ebay.orderstatus.trackingcode.dbmatching.table']['table'])
		)
	) {
		$boxes .= '<p class="errorBox">'.ML_GENERIC_ERROR_TRACKING_CODE_MATCHING.'</p>';
	}
} else {
	$nSite = getDBConfigValue('ebay.site', $_MagnaSession['mpID']);
}

if (isset($currencyError) && (getCurrencyFromMarketplace($_MagnaSession['mpID']) !== false)) {
	$boxes .= $currencyError;
}

$form['ebayaccount']['fields']['site']['values'] = $magnaConfig['ebay']['sites'];
if ($nSite !== null) {
	$curVal = array();
	foreach ($magnaConfig['ebay']['currencies'][$nSite] as $cur) {
		$curVal[$cur] = $cur;
	}
	$form['ebayaccount']['fields']['currency']['values'] = $curVal;
	$form['ebayaccount']['fields']['site']['ajaxlinkto']['initload'] = false;
}
#echo var_dump_pre($auth, '$auth');

//$auth['state'] = false;
if ($auth['state']) {
	$payment = geteBayPaymentOptions();
	if (!is_array($payment)) {
		$auth['state'] = false;
		setDBConfigValue('ebay.authed', $_MagnaSession['mpID'], $auth, true);
		
	} else {
		#$form['listingdefaults']['fields']['paymentmethod']['values'] = $payment;
		$form['payment']['fields']['paymentmethod']['values'] = $payment;
	
		$shippingprofiles = geteBayShippingDiscountProfiles();
		$form['shipping']['fields']['shippingprofilelocal']['values'] = $shippingprofiles;
		$form['shipping']['fields']['shippingprofileinternational']['values'] = $shippingprofiles;
	}
}

if (!$auth['state']) {
	$form = array('ebayaccount' => $form['ebayaccount']);
	if (tokenAvailable()) {
		$expires = getDBConfigValue('ebay.token.expires', $_MagnaSession['mpID'], '');
		if (is_datetime($expires) && ($expires < date('Y-m-d H:i:s'))) {
			$form = array ('ebayaccount' => $form['ebayaccount']);
			unset($form['ebayaccount']['fields']['currency']);
			$boxes .= '<p class="noticeBox">'.ML_EBAY_TEXT_TOKEN_INVALID.'</p>';
		}
	} else {
		$boxes .= '<p class="successBoxBlue">'.ML_EBAY_TEXT_TOKEN_NOT_AVAILABLE_YET.'</p>';
	}
	$form = array('ebayaccount' => $form['ebayaccount']);
	unset($form['ebayaccount']['fields']['currency']);
	
} else {
	$auth['expire'] = time() + 60 * 15;
	setDBConfigValue('ebay.authed', $_MagnaSession['mpID'], $auth, true);
	// renderAuthError might have removed 'ebay.token'. But the token is there and valid at this point.
	// Call tokenAvailable() again to set this config value.
	if (getDBConfigValue('ebay.token', $_MagnaSession['mpID'], '') !== '__saved__') {
		tokenAvailable();
	}
	#if (!is_array($form['listingdefaults']['fields']['paymentmethod']['values'])) {
	#	$form['listingdefaults']['fields']['paymentmethod']['values'] = geteBayPaymentOptions();
	#}
	if (!is_array($form['payment']['fields']['paymentmethod']['values'])) {
		$form['payment']['fields']['paymentmethod']['values'] = geteBayPaymentOptions();
	}
	
	mlGetLanguages($form['listingdefaults']['fields']['language']);
	// show status filter only if products_status is available in shop
	if (!MagnaDB::gi()->columnExistsInTable('products_status', TABLE_PRODUCTS)) {
		unset($form['listingdefaults']['fields']['Statusfilter']);
	}
	$form['location']['fields']['country']['values'] = $magnaConfig['ebay']['countries'];
	mlGetCustomersStatus($form['fixedsettings']['fields']['whichprice'], true);
	if (!empty($form['fixedsettings']['fields']['whichprice'])) {
		$form['fixedsettings']['fields']['whichprice']['values']['0'] = ML_LABEL_SHOP_PRICE;
		ksort($form['fixedsettings']['fields']['whichprice']['values']);
		unset($form['fixedsettings']['fields']['specialprices']);
	} else {
		unset($form['fixedsettings']['fields']['whichprice']);
	}	
	
	mlGetCustomersStatus($form['chinesesettings']['fields']['whichprice'], true);
	if (!empty($form['chinesesettings']['fields']['whichprice'])) {
		$form['chinesesettings']['fields']['whichprice']['values']['0'] = ML_LABEL_SHOP_PRICE;
		ksort($form['chinesesettings']['fields']['whichprice']['values']);
		unset($form['chinesesettings']['fields']['specialprices']);
	} else {
		unset($form['chinesesettings']['fields']['whichprice']);
	}	

	mlGetManufacturers($form['listingdefaults']['fields']['manufacturerfilter']);

	# Voreinstellung Dauer Festpreis-Listings
	try {
		$eBayStoreData = MagnaConnector::gi()->submitRequest(array('ACTION' => 'HasStore'));
		if('True' == $eBayStoreData['DATA']['Answer']) {
			$fixedListingType = 'StoresFixedPrice';
		} else {
			$fixedListingType = 'FixedPriceItem';
		}
		$fixedDurationData = MagnaConnector::gi()->submitRequest(array(
			'ACTION' => 'GetListingDurations',
			'DATA' => array (
				'ListingType' => $fixedListingType
			)
		));
	} catch (MagnaException $e) {
		$fixedDurationData = array('DATA' => array('ListingDurations' => array('Days_30')));
	}
	$fixedDurations = array();
	foreach ($fixedDurationData['DATA']['ListingDurations'] as $duration) {
		$lastFixedDuration = $duration;
		$define = 'ML_EBAY_LABEL_LISTINGDURATION_'.strtoupper($duration);
		$fixedDurations["$duration"] = defined($define) ? constant($define) : $duration;
	}
	$form['fixedsettings']['fields']['fixedduration']['values'] = $fixedDurations;
	if (null == getDBConfigValue('ebay.fixed.duration', $_MagnaSession['mpID'], null)) {
		setDBConfigValue('ebay.fixed.duration', $_MagnaSession['mpID'], $lastFixedDuration);
	}
	$form['fixedsettings']['fields']['fixedduration']['default'] = getDBConfigValue('ebay.fixed.duration', $_MagnaSession['mpID'], $lastFixedDuration);

	# eBay Plus ist unten (nur wenn nicht ajax)

	# Voreinstellung Dauer Steigerungsauktionen
	try {
		$chineseDurationData = MagnaConnector::gi()->submitRequest(array(
			'ACTION' => 'GetListingDurations',
			'DATA' => array (
				'ListingType' => 'Chinese'
			)
		));
	} catch (MagnaException $e) {
		$chineseDurationData = array('DATA' => array('ListingDurations' => array('Days_3')));
	}
	$chineseDurations = array();
	foreach ($chineseDurationData['DATA']['ListingDurations'] as $duration) {
		if (!isset($firstChineseDuration)) $firstChineseDuration = $duration;
		$define = 'ML_EBAY_LABEL_LISTINGDURATION_'.strtoupper($duration);
		$chineseDurations["$duration"] = defined($define) ? constant($define) : $duration;
	}
	$form['chinesesettings']['fields']['chineseduration']['values'] = $chineseDurations;
	if (null == getDBConfigValue('ebay.chinese.duration', $_MagnaSession['mpID'], null)) {
		setDBConfigValue('ebay.chinese.duration', $_MagnaSession['mpID'], $firstChineseDuration);
	}
	$form['chinesesettings']['fields']['chineseduration']['default'] = getDBConfigValue('ebay.chinese.duration', $_MagnaSession['mpID'], $firstChineseDuration);

	# Bestellimporte
	mlGetCustomersStatus($form['import']['fields']['customersgroup']);
	mlGetOrderStatus($form['import']['fields']['openstatus']);
	# Build 1735: allow multiple 'closed states'
	if (!is_array($closedstatus = getDBConfigValue('ebay.orderstatus.closed', $_MagnaSession['mpID'], '3'))) {
		setDBConfigValue('ebay.orderstatus.closed', $_MagnaSession['mpID'], array($closedstatus));
	}
	mlGetOrderStatus($form['import']['fields']['closedstatus']);
	if (false === getDBConfigValue('ebay.orderstatus.paid', $_MagnaSession['mpID'], false)) {
		$paidStatus = (int)MagnaDB::gi()->fetchOne('SELECT orders_status_id FROM '.TABLE_ORDERS_STATUS.'
		    WHERE orders_status_name IN (\'Bezahlt\',\'Payment received\') ORDER BY language_id LIMIT 1');
		setDBConfigValue('ebay.orderstatus.paid', $_MagnaSession['mpID'], $paidStatus);
	}
	mlGetOrderStatus($form['ordersync']['fields']['paidstatus']);
	if (false === getDBConfigValue('ebay.updateable.orderstatus', $_MagnaSession['mpID'], false)) {
		setDBConfigValue('ebay.updateable.orderstatus', $_MagnaSession['mpID'], array($form['import']['fields']['openstatus']['default']));
	}

	# nur bezahlte importieren: Felder entspr. (wird über die Einstellung gesteuert, aber wg der Optik)
	if (getDBConfigValue($mp.'.order.importonlypaid', $_MagnaSession['mpID'], false) === 'true') {
		setDBConfigValue('ebay.orderstatus.closed', $_MagnaSession['mpID'], MagnaDB::gi()->fetchArray('
            SELECT DISTINCT orders_status_id FROM '.TABLE_ORDERS_STATUS, true), true);
		setDBConfigValue('ebay.updateable.orderstatus', $_MagnaSession['mpID'], array(), true);
	}
	mlGetOrderStatus($form['ordersync']['fields']['updateablestatus']);
	
	# Bestellstatus-Sync
	mlGetOrderStatus($form['orderSyncState']['fields']['shippedstatus']);
	mlGetOrderStatus($form['orderSyncState']['fields']['cancelstatus']);
	
	mlGetShippingModules($form['import']['fields']['defaultshipping']);
	mlGetPaymentModules($form['import']['fields']['defaultpayment']);

	mlPresetTrackingCodeMatching($_MagnaSession['mpID'], 'ebay.orderstatus.carrier.dbmatching', 'ebay.orderstatus.trackingcode.dbmatching');

	if (false === getDBConfigValue('ebay.imagepath', $_MagnaSession['mpID'], false)) {
		#$form['listingdefaults']['fields']['imagepath']['default'] =
		$form['images']['fields']['imagepath']['default'] =
			defined('DIR_WS_CATALOG_POPUP_IMAGES')
				? HTTP_CATALOG_SERVER.DIR_WS_CATALOG_POPUP_IMAGES
				: HTTP_CATALOG_SERVER.DIR_WS_CATALOG_IMAGES;
		#setDBConfigValue('ebay.imagepath', $_MagnaSession['mpID'], $form['listingdefaults']['fields']['imagepath']['default'], true);
		setDBConfigValue('ebay.imagepath', $_MagnaSession['mpID'], $form['images']['fields']['imagepath']['default'], true);
	}
	# Bilder
//	if (false === getDBConfigValue('ebay.gallery.imagepath', $_MagnaSession['mpID'], false)) {
//		# normalerweise dasselbe wie fuer die Hauptbilder
//		#$form['listingdefaults']['fields']['galleryimagepath']['default'] =
//		$form['images']['fields']['galleryimagepath']['default'] =
//			defined('DIR_WS_CATALOG_POPUP_IMAGES')
//				? HTTP_CATALOG_SERVER.DIR_WS_CATALOG_POPUP_IMAGES
//				: HTTP_CATALOG_SERVER.DIR_WS_CATALOG_IMAGES;
//		#setDBConfigValue('ebay.gallery.imagepath', $_MagnaSession['mpID'], $form['listingdefaults']['fields']['galleryimagepath']['default'], true);
//		setDBConfigValue('ebay.gallery.imagepath', $_MagnaSession['mpID'], $form['images']['fields']['galleryimagepath']['default'], true);
//	}
	# Ruecknahmebedingungen
	$form['returnpolicy']['fields']['returnsaccepted']['values']    = geteBaySingleReturnPolicyDetail('ReturnsAccepted');
	$form['returnpolicy']['fields']['returnswithin']['values']      = geteBaySingleReturnPolicyDetail('ReturnsWithin');
	$form['returnpolicy']['fields']['shippingcostpaidby']['values'] = geteBaySingleReturnPolicyDetail('ShippingCostPaidBy');
	# Shop-URL: Nicht erlaubt
	$form['mail']['fields']['subject']['default'] = str_replace('#SHOPURL#', '', $form['mail']['fields']['subject']['default']);
	$form['mail']['fields']['mail']['default'] = str_replace(' unter <strong>#SHOPURL#</strong>', '', $form['mail']['fields']['mail']['default']);
	$form['mail']['fields']['mail']['externalDesc'] = str_replace('<dt>#SHOPURL#</dt>', '', str_replace('<dd>URL zu Ihrem Shop</dd>', '',  str_replace('<dd>URL to your shop</dd>', '', $form['mail']['fields']['mail']['externalDesc'])));

	# Carriers
	$form['orderSyncState']['fields']['carrier']['values'] = array('' => ML_LABEL_NO_SELECTION);
	$carriers = EbayApiConfigValues::gi()->getCarriers();
	foreach ($carriers as $carKey => $carName) {
		$form['orderSyncState']['fields']['carrier']['values'][$carKey] = $carName;
	}

	// only show matching options if booked
	if (!ML_ShopAddOns::mlAddOnIsBooked('EbayProductIdentifierSync')) {
		unset($form['stocksync']['fields']['propertiesMatching']);
	}

	// PicturePack: show only if bookable
	if (!ML_ShopAddOns::getAddOnInfo('EbayPicturePack')) {
		unset($form['images']['fields']['picturepack']);
	}
	
	if (   MAGNA_GAMBIO_VARIATIONS
	    && (getDBConfigValue('general.gambio.useproperties', '0', 'true') == 'true')
	    && (ML_ShopAddOns::mlAddOnIsBooked('EbayPicturePack'))) {
		$properties = MagnaDb::gi()->fetchArray('
			SELECT properties_id ,  properties_name 
			FROM properties_description
			WHERE language_id = '.$_SESSION['languages_id'].'
		');
		$properties[] = array ('properties_id' => '-1', 'properties_name' => ML_EBAY_DO_NOT_USE_VARIATION_PICS);
		$form['images']['fields']['picturepackproperty']['values'] = array();
		if(!empty($properties)){
			foreach ($properties as $property) {
				$form['images']['fields']['picturepackproperty']['values'][$property['properties_id']] = $property['properties_name'];
			}
		}
	} else if (    MagnaDb::gi()->columnExistsInTable('attributes_image', TABLE_PRODUCTS_ATTRIBUTES)
	            && ML_ShopAddOns::mlAddOnIsBooked('EbayPicturePack')) {
		$attributes = MagnaDb::gi()->fetchArray('
			SELECT products_options_id, products_options_name
			FROM '.TABLE_PRODUCTS_OPTIONS.'
			WHERE language_id = '.$_SESSION['languages_id'].'
		');
		$attributes[] = array ('products_options_id' => '-1', 'products_options_name' => ML_EBAY_DO_NOT_USE_VARIATION_PICS);
		$form['images']['fields']['picturepackproperty']['values'] = array();
		if(!empty($attributes)){
			foreach ($attributes as $attr) {
				$form['images']['fields']['picturepackproperty']['values'][$attr['products_options_id']] = $attr['products_options_name'];
			}
		}
	} else {
		unset($form['images']['fields']['picturepackproperty']);
	}

}

if (isset($_GET['kind']) && ($_GET['kind'] == 'ajax')) {
	echo $cG->processAjaxRequest();
} else {

	# eBay Plus (nur wenn nicht ajax)
	try {
		$eBayPlusSettings = MagnaConnector::gi()->submitRequest(array(
			'ACTION' => 'GeteBayAccountSettings',
		));
	} catch (MagnaException $e) {
		$eBayPlusSettings = array('DATA' => array('eBayPlus' => 'false', 'eBayPlusListingDefault' => 'false'));
	}
	if (    ('false' == $eBayPlusSettings['DATA']['eBayPlus'])
	     || ( false  == $eBayPlusSettings['DATA']['eBayPlus'])) {
?><script>/*<!CDATA[*/
	$(document).ready(function() {
		$('input[id="conf_ebay.plus_val"]').prop('checked', false);
		$('input[id="conf_ebay.plus_val"]').prop('disabled', true);
	});
/*]]>*/</script><?php
	} else if ('true' == getDBConfigValue('ebay.plus', $_MagnaSession['mpID'], $eBayPlusSettings['DATA']['eBayPlusListingDefault'])) {
	# aktiviere Checkbox, wenn Voreinstellung auf eBay aktiv und noch keine Voreistellung im Plugin getroffen
?><script>/*<!CDATA[*/
	$(document).ready(function() {
		$('input[id="conf_ebay.plus_val"]').prop('checked', true);
	});
/*]]>*/</script><?php
	}
	

	$cG->setRenderTabIdent(true);
	$allCorrect = $cG->processPOST();

	echo $boxes;
	if (array_key_exists('sendTestmail', $_POST)) {
		if ($allCorrect) {
			if (sendTestMail($_MagnaSession['mpID'])) {
				echo '<p class="successBox">'.ML_GENERIC_TESTMAIL_SENT.'</p>';
			} else {
				echo '<p class="successBox">'.ML_GENERIC_TESTMAIL_SENT_FAIL.'</p>';
			}
		} else {
			echo '<p class="noticeBox">'.ML_GENERIC_NO_TESTMAIL_SENT.'</p>';
		}
	}
	#echo print_m($form);
	echo $cG->renderConfigForm();
	$curSite = getDBConfigValue('ebay.site', $_MagnaSession['mpID'], false);
	if (($curSite != false) || !$auth['state']) {
?><script>/*<!CDATA[*/
		$('#config_ebay_site').change(function() {
			var s = $(this);
			if (s.val() == '<?php echo $curSite; ?>') return true;
			$('<div></div>').html('<?php echo str_replace(array("\n", "\r"), ' ', ML_EBAY_TEXT_CHANGE_SITE); ?>').jDialog({
				title: '<?php echo ML_EBAY_LABEL_CHANGE_SITE ?>',
				buttons: {
					'<?php echo ML_BUTTON_LABEL_NO; ?>': function() {
						s.val('<?php echo $curSite; ?>');
						jQuery(this).dialog('close');
					},
					'<?php echo ML_BUTTON_LABEL_YES; ?>': function() {
						$('#conf_ebay').submit();
					}
				}
			});
		});
/*]]>*/</script><?php
	}
?><script>/*<!CDATA[*/
$('input[id="conf_ebay.usePrefilledInfo_val"]').change(function() {
    var pia = $(this);
    var eaa = $('input[id="conf_ebay.useean_val"]');
	myConsole.log('eaa.val == '+((eaa.attr('checked') == 'checked')?'true':'false')+"\n"+'pia.val == '+((pia.attr('checked') == 'checked')?'true':'false'));
    if (eaa.attr('checked') == 'checked') return true;
    if (pia.attr('checked') != 'checked') return true;
    $('<div></div>').html('<?php echo ML_EBAY_TEXT_SET_PROD_INFOS ?>').jDialog({
		title: '<?php echo ML_EBAY_LABEL_PROD_INFOS ?>',
		buttons: {
			'<?php echo ML_BUTTON_LABEL_NO; ?>': function() {
				pia.removeAttr('checked');
				jQuery(this).dialog('close');
			},
			'<?php echo ML_BUTTON_LABEL_YES; ?>': function() {
				eaa.attr('checked', 'checked');
				jQuery(this).dialog('close');
			}
		}
	});
});
/*]]>*/</script><?php
?><script>/*<!CDATA[*/
if ($('input[id="conf_ebay.order.importonlypaid_true"]').attr('checked') == 'checked') {
    	$('select[id="config_ebay_orderstatus_closed"]').prop('disabled', true);
    	$('select[id="config_ebay_orderstatus_paid"]').prop('disabled', true);
    	$('select[id="config_ebay_updateable_orderstatus"]').prop('disabled', true);
    	$('input[id="conf_ebay.update.orderstatus_val"]').prop('checked', false);
    	$('input[id="conf_ebay.update.orderstatus_val"]').prop('disabled', true);
}
$('input[id="conf_ebay.order.importonlypaid_true"]').change(function() {
    		var rdio = $(this);
    		if (rdio.attr('checked') != 'checked') return true;
			rdio.removeAttr('checked');
			$('input[id="conf_ebay.order.importonlypaid_false"]').attr('checked', 'checked');
			$('<div></div>').html('<?php echo ML_TEXT_WARNING_EBAY_IMPORT_ONLY_PAID_ORDERS ?>').jDialog({
				title: '<?php echo ML_TITLE_EBAY_IMPORT_ONLY_PAID_ORDERS ?>',
				buttons: {
					'<?php echo ML_BUTTON_LABEL_NO; ?>': function() {
						jQuery(this).dialog('close');
					},
					'<?php echo ML_BUTTON_LABEL_YES; ?>': function() {
						$('input[id="conf_ebay.order.importonlypaid_false"]').removeAttr('checked');
						rdio.attr('checked', 'checked');
    					$('select[id="config_ebay_orderstatus_closed"]').prop('disabled', true);
    					$('select[id="config_ebay_orderstatus_paid"]').prop('disabled', true);
    					$('select[id="config_ebay_updateable_orderstatus"]').prop('disabled', true);
    					$('input[id="conf_ebay.update.orderstatus_val"]').prop('checked', false);
    					$('input[id="conf_ebay.update.orderstatus_val"]').prop('disabled', true);
						jQuery(this).dialog('close');
					}
				}
			})
		});
$('input[id="conf_ebay.order.importonlypaid_false"]').change(function() {
    		var rdio = $(this);
    		if (rdio.attr('checked') == 'checked') {
    			$('select[id="config_ebay_orderstatus_closed"]').prop('disabled', false);
    			$('select[id="config_ebay_orderstatus_paid"]').prop('disabled', false);
    			$('select[id="config_ebay_updateable_orderstatus"]').prop('disabled', false);
    			$('input[id="conf_ebay.update.orderstatus_val"]').prop('disabled', false);
			}
					
		});
/*]]>*/</script><?php
?><script>/*<!CDATA[*/
	$(document).ready(function() {
      $('select[id="config_ebay_gallery_type"]').data('ml-oldvalue', $('select[id="config_ebay_gallery_type"]').val());
    });
    $('select[id="config_ebay_gallery_type"]').change(function() {
      var sel=$(this);
      if (sel.val() != 'Plus') {
        sel.data('ml-oldvalue', sel.val());
        return true;
      }
      sel.val(sel.data('ml-oldvalue'));
		$('<div></div>').html('<?php echo ML_TEXT_WARNING_EBAY_GALLERY_PLUS_COSTS ?>').jDialog({
			title: '<?php echo ML_TITLE_EBAY_WARNING_GALLERY_PLUS_COST ?>',
			buttons: {
				'<?php echo ML_BUTTON_LABEL_NO; ?>': function() {
					jQuery(this).dialog('close');
				},
				'<?php echo ML_BUTTON_LABEL_YES; ?>': function() {
					sel.data('ml-oldvalue', 'Plus');
					sel.val('Plus');
					jQuery(this).dialog('close');
				}
			}
		})
    });
/*]]>*/</script><?php
	echo $cG->exchangeRateAlert();
	ML_ShopAddOns::generateConfigPopup('EbayProductIdentifierSync', 'conf_ebay.listingdetails.sync', '#conf_ebay');
	ML_ShopAddOns::generateConfigPopup('EbayZeroStockAndRelisting', 'conf_ebay.autorelist', '#conf_ebay');
	ML_ShopAddOns::generateConfigPopup('EbayZeroStockAndRelisting', 'conf_ebay.zerostockontrol', '#conf_ebay');
	ML_ShopAddOns::generateConfigPopup('EbayPicturePack', 'conf_ebay.picturepack_val', '#conf_ebay','checkbox');
}
