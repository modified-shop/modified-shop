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
 * $Id: amazonConfig.php 6288 2015-12-04 15:08:12Z tim.neumann $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/Configurator.php');
include_once(DIR_MAGNALISTER_INCLUDES.'lib/configFunctions.php');

function renderAuthError($authError) {
	if (!is_array($authError)) {
		return '';
	}
	$errors = array();
	if (array_key_exists('ERRORS', $authError) && !empty($authError['ERRORS'])) {
		foreach ($authError['ERRORS'] as $err) {
			$errors[] = $err['ERRORMESSAGE'];
		}
	}
    return '<p class="errorBox">
     	<span class="error bold larger">'.ML_ERROR_LABEL.':</span>
     	'.ML_ERROR_AMAZON_WRONG_SELLER_CENTRAL_LOGIN.(
     		(!empty($errors))
     			? '<br /><br />'.implode('<br />', $errors)
     			: ''
     	).'</p>';
}

function amazonTopTenConfig($aArgs = array(), &$sValue = ''){
	global $_MagnaSession;
	require_once DIR_MAGNALISTER_FS.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'amazon'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'amazonTopTen.php';
	$oTopTen = new amazonTopTen();
	$oTopTen->setMarketPlaceId($_MagnaSession['mpID']);
	if (isset($_GET['what'])) {
		if (!isset($_GET['tab'])) {
			echo $oTopTen->renderConfig();
		} elseif ($_GET['tab'] == 'init') {
			echo $oTopTen->renderConfigCopy( (isset($_GET['execute'])) && ($_GET['execute']=='true') );
		} elseif($_GET['tab'] == 'delete') {
			echo $oTopTen->renderConfigDelete(
				isset($_POST['delete']) 
				? $_POST['delete'] 
				: array()
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

function magnaUpdateCarrierCodes($args) {
	global $_MagnaSession;

	setDBConfigValue('amazon.orderstatus.carrier.additional', $_MagnaSession['mpID'], $args['value']);

	$carrierCodes = loadCarrierCodes();
	$setting = getDBConfigValue(
		'amazon.orderstatus.carrier.default',
		$_MagnaSession['mpID']
	);

	$ret = '';
	foreach ($carrierCodes as $val) {
		$ret .= '<option '.(($val == $setting) ? 'selected="selected"' : '').' value="'.$val.'">'.$val.'</option>'."\n";
	}
	return $ret;
}

$_url['mode'] = 'conf';
if (isset($_GET['what']) && ($_GET['what'] == 'topTenConfig')){
	amazonTopTenConfig();
	exit();
}

$form = loadConfigForm($_lang,
	array(
		'amazon.form' => array(),
	), array(
		'_#_platform_#_' => $_MagnaSession['currentPlatform'],
		'_#_platformName_#_' => $_modules[$_Marketplace]['title']
	)
);

function amazonLeadtimeToShipMatching($args, &$value = '') {
	global $_MagnaSession;
	if (!defined('TABLE_SHIPPING_STATUS') || !MagnaDB::gi()->tableExists(TABLE_SHIPPING_STATUS)) {
		return ML_ERROR_NO_SHIPPINGTIME_MATCHING;
	}
	$hippingtimes = MagnaDB::gi()->fetchArray('
	    SELECT shipping_status_id as id, shipping_status_name as name
	      FROM '.TABLE_SHIPPING_STATUS.'
	     WHERE language_id = '.$_SESSION['languages_id'].' 
	  ORDER BY shipping_status_id ASC
	');
	$leadtimeMatch = getDBConfigValue($args['key'], $_MagnaSession['mpID'], array());
	$opts = array_merge(array (
		'0' => '&mdash;',
	), range(1, 30));
		$html = '<table class="nostyle" width="100%" style="float: left; margin-right: 2em;">
		<thead><tr>
			<th width="25%">'.ML_LABEL_SHIPPING_TIME_SHOP.'</th>
			<th width="75%">'.ML_AMAZON_LABEL_LEADTIME_TO_SHIP.'</th>
		</tr></thead>
		<tbody>';
	foreach ($hippingtimes as $st) {
		$html .= '
			<tr>
				<td width="25%" class="nowrap">'.$st['name'].'</td>
				<td width="75%"><select name="conf['.$args['key'].']['.$st['id'].']">';
		foreach ($opts as $key => $val) {
			$html .= '<option value="'.$key.'" '.(
				(array_key_exists($st['id'], $leadtimeMatch) && ($leadtimeMatch[$st['id']] == $key))
					? 'selected="selected"'
					: ''
			).'>'.$val.'</option>';
		}
		$html .= '
				</select></td>
			</tr>';
	}
	$html .= '</tbody></table><p>&nbsp;</p>';

#	$html .= print_m($taxes, '$taxes');
#	$html .= print_m(func_get_args(), 'func_get_args');
	return $html;
}


$aMarketplaces = amazonGetMarketplaces();
$form['amazonaccount']['fields']['site']['values'] = $aMarketplaces['Sites'];
	
$boxes = '';
$auth = getDBConfigValue('amazon.authed', $_MagnaSession['mpID'], false);
if ((!is_array($auth) || !$auth['state']) &&
	allRequiredConfigKeysAvailable($authConfigKeys, $_MagnaSession['mpID']) && 
	!(
		array_key_exists('conf', $_POST) && 
		allRequiredConfigKeysAvailable($authConfigKeys, $_MagnaSession['mpID'], $_POST['conf'])
	)
) {
    $boxes .= renderAuthError($authError);
}

if (array_key_exists('conf', $_POST)) {
	$nUser = trim($_POST['conf']['amazon.username']);
	$nPass = trim($_POST['conf']['amazon.password']);
	$nMerchant = trim($_POST['conf']['amazon.merchantid']);
	$nMarketplace = trim($_POST['conf']['amazon.marketplaceid']);
	$nSite = $_POST['conf']['amazon.site'];
	$sToken = trim($_POST['conf']['amazon.mwstoken']);

	if (!empty($nUser) && (getDBConfigValue('amazon.password', $_MagnaSession['mpID']) == '__saved__') && empty($nPass)) {
		$nPass = '__saved__';
	}
	if (!empty($nUser) && (getDBConfigValue('amazon.mwstoken', $_MagnaSession['mpID']) == '__saved__') && empty($sToken)) {
		$sToken = '__saved__';
	}

	if (!empty($nUser) && !empty($nPass) && !empty($sToken)) {
		if ((strpos($nPass, '&#9679;') === false) && (strpos($nPass, '&#8226;') === false)) {
			/*               Windows                                  Mac                */
			setDBConfigValue('amazon.authed', $_MagnaSession['mpID'], array (
				'state' => false,
				'expire' => time()
			), true);
			try {
				$result = MagnaConnector::gi()->submitRequest(array(
					'ACTION' => 'SetCredentials',
					'USERNAME' => $nUser,
					'PASSWORD' => $nPass,
					'MERCHANTID' => $nMerchant,
					'MARKETPLACE' => $nMarketplace,
					'MWSToken' => $sToken,
					'SITE' => $nSite
				));
				$boxes .= '
					<p class="successBox">'.ML_GENERIC_STATUS_LOGIN_SAVED.'</p>
				';
			} catch (MagnaException $e) {
				$boxes .= '
					<p class="errorBox">'.ML_GENERIC_STATUS_LOGIN_SAVEERROR.'</p>
				';
			}
			
			try {
				MagnaConnector::gi()->submitRequest(array(
					'ACTION' => 'IsAuthed',
				));
				$auth = array (
					'state' => true,
				);
			} catch (MagnaException $e) {
				$e->setCriticalStatus(false);
				$boxes .= renderAuthError($e->getErrorArray());
				$auth = array (
					'state' => false
				);
			}

		} else {
			$boxes .= '
				<p class="errorBox">'.ML_ERROR_INVALID_PASSWORD.'</p>
			';
		}
	}
	
	if (!empty($nSite)) {
		setDBConfigValue('amazon.currency', $_MagnaSession['mpID'], $aMarketplaces['Currencies'][$nSite], true);
	}
	unset($currencyError);
	require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');
	$sp = new SimplePrice();
	if (!$sp->currencyExists($aMarketplaces['Currencies'][$nSite])) {
		$boxes .= '<p class="errorBox">'.sprintf(
			ML_GENERIC_ERROR_CURRENCY_NOT_IN_SHOP,
			$aMarketplaces['Currencies'][$nSite]
		).'</p>';
	}
}
if (isset($currencyError) && (getCurrencyFromMarketplace($_MagnaSession['mpID']) !== false)) {
	$boxes .= $currencyError;
}

if (!$auth['state']) {
	$form = array (
		'amazonaccount' => $form['amazonaccount']
	);
} else {
	$auth['expire'] = time() + 60 * 15;
	setDBConfigValue('amazon.authed', $_MagnaSession['mpID'], $auth, true);
	$form['matchingvalues']['fields']['itemcondition']['values'] = amazonGetPossibleOptions('ConditionTypes');
	$form['matchingvalues']['fields']['shipping']['values'] = amazonGetPossibleOptions('ShippingLocations');
	$form['orderSyncState']['fields']['carrier']['values'] = loadCarrierCodes();

	mlGetManufacturers($form['prepare']['fields']['manufacturerfilter']);
	mlGetLanguages($form['prepare']['fields']['lang']);

	mlGetOrderStatus($form['import']['fields']['openstatus']);
	mlGetOrderStatus($form['import']['fields']['orderStatusFba']);
	mlGetOrderStatus($form['orderSyncState']['fields']['cancelstatus']);
	mlGetOrderStatus($form['orderSyncState']['fields']['shippedstatus']);

	mlGetCustomersStatus($form['import']['fields']['customersgroup']);
	mlGetCustomersStatus($form['price']['fields']['whichprice'], false);
	if (!empty($form['price']['fields']['whichprice'])) {
		$form['price']['fields']['whichprice']['values']['0'] = ML_LABEL_SHOP_PRICE;
		ksort($form['price']['fields']['whichprice']['values']);
		unset($form['price']['fields']['specialprices']);
	} else {
		unset($form['price']['fields']['whichprice']);
	}
	$form['apply']['fields']['imagepath']['default'] = SHOP_URL_POPUP_IMAGES;

	mlGetShippingModules($form['import']['fields']['defaultshipping']);
	mlGetPaymentModules($form['import']['fields']['defaultpayment']);
	mlGetShippingModules($form['import']['fields']['defaultshippingfba']);
	mlGetPaymentModules($form['import']['fields']['defaultpaymentfba']);

	mlPresetTrackingCodeMatching($_MagnaSession['mpID'], 'amazon.orderstatus.carrier.carrierDBMatching', 'amazon.orderstatus.carrier.trackingcode');
	
	if ((getDBConfigValue('amazon.checkin.SkuAsMfrPartNo', $_MagnaSession['mpID']) == null) // setting doesn't exist yet
		// has the config been saved before that feature was implemented?
		&& (getDBConfigValue('amazon.preimport.start', $_MagnaSession['mpID'], date('Y-m-d')) < '2014-02-19')
	) {
		// then change the default to false.
		$form['stockCI']['fields']['manufacturerpartnumber']['default']['val'] = false;
	}
}

$cG = new MLConfigurator($form, $_MagnaSession['mpID'], 'conf_amazon');
$cG->setRenderTabIdent(true);
$allCorrect = $cG->processPOST();

if (isset($_GET['kind']) && ($_GET['kind'] == 'ajax')) {
	echo $cG->processAjaxRequest();
} else {
	include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_top.php');

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

	echo $cG->renderConfigForm();
	echo $cG->exchangeRateAlert();
	include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_bottom.php');
}
