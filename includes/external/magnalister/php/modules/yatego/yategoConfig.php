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
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/Configurator.php');
include_once(DIR_MAGNALISTER_INCLUDES.'lib/configFunctions.php');

function renderAuthError($authError) {
	$errors = array();
	if (array_key_exists('ERRORS', $authError) && !empty($authError['ERRORS'])) {
		foreach ($authError['ERRORS'] as $err) {
			$errors[] = $err['ERRORMESSAGE'];
		}
	}
	return '<p class="errorBox">
		<span class="error bold larger">'.ML_ERROR_LABEL.':</span>
		'.ML_YAGETO_ERROR_ACCESS_DENIED.(
			(!empty($errors))
				? '<br /><br />'.implode('<br />', $errors)
				: ''
		).'</p>';
}

$_url['mode'] = 'conf';

$form = loadConfigForm($_lang,
	array(
		'yatego.form' => array(),
		'comparisonshopping_generic.form' => array('unset' => array(
			'inventoryupdate',
			'checkin',
			'price'
		)),
		'email_template_generic.form' => array()
	), array(
		'_#_platform_#_' => $_MagnaSession['currentPlatform'],
		'_#_platformName_#_' => $_modules[$_Marketplace]['title']
	)
);

$form['ftp']['headline'] = sprintf($form['ftp']['headline'], ML_MODULE_YATEGO);

mlGetManufacturers($form['checkin']['fields']['manufacturerfilter']);

mlGetCountries($form['shipping']['fields']['country']);
mlGetLanguages($form['lang']['fields']['lang']);
mlGetShippingMethods($form['shipping']['fields']['method']);
mlGetCustomersStatus($form['price']['fields']['whichprice'], false);
if (!empty($form['price']['fields']['whichprice'])) {
	$form['price']['fields']['whichprice']['values']['0'] = ML_LABEL_SHOP_PRICE;
	ksort($form['price']['fields']['whichprice']['values']);
} else {
	unset($form['price']['fields']['whichprice']);
}
//mlGetOrderStatus($form['orderstatus']['fields']['openstatus']);
//mlGetOrderStatus($form['orderstatus']['fields']['cancelstatus']);
//mlGetOrderStatus($form['orderstatus']['fields']['shippedstatus']);
mlGetOrderStatus($form['import']['fields']['openstatus']);

mlGetCustomersStatus($form['import']['fields']['customersgroup']);
mlGetShippingModules($form['import']['fields']['defaultshipping']);
mlGetPaymentModules($form['import']['fields']['defaultpayment']);

$form['shipping']['fields']['method']['label'] = ML_GENERIC_SHIPPING_COST_ADDITIONAL;

$cG = new MLConfigurator($form, $_MagnaSession['mpID'], 'conf_yatego');
$cG->setRenderTabIdent(true);

$boxes = '';
$auth = getDBConfigValue('yatego.authed', $_MagnaSession['mpID'], false);
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
	$nUser = trim($_POST['conf'][$_Marketplace.'.username']);
	$nPass = trim($_POST['conf'][$_Marketplace.'.password']);

	if (!empty($nUser) && (getDBConfigValue($_Marketplace.'.password', $_MagnaSession['mpID']) == '__saved__') 
	    && empty($nPass)
	) {
		$nPass = '__saved__';
	}

	if ((strpos($nPass, '&#9679;') === false) && (strpos($nPass, '&#8226;') === false)) {
		/*               Windows                                  Mac                */
		setDBConfigValue('yatego.authed', $_MagnaSession['mpID'], array (
			'state' => false,
			'expire' => time()
		), true);
		if (!empty($nUser) && !empty($nPass)) {
			try {
				$result = MagnaConnector::gi()->submitRequest(array(
					'ACTION' => 'SetCredentials',
					'USER' => $nUser,
					'PASS' => $nPass,
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
					'expire' => time() + 60 * 30
				);
				setDBConfigValue('yatego.authed', $_MagnaSession['mpID'], $auth, true);
			} catch (MagnaException $e) {
				$e->setCriticalStatus(false);
				$boxes .= renderAuthError($e->getErrorArray());
			}
		}
	} else {
		$boxes .= '
			<p class="errorBox">'.ML_ERROR_INVALID_PASSWORD.'</p>';
	}
}

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
	$cG->sortKeys(array(
		'ftp', 'checkin', 'lang', 'stats', 'price', 'shipping', 'inventoryupdate', 'checkinstandards', 'import', 'mail'
	));
	echo $cG->renderConfigForm();
	echo $cG->exchangeRateAlert();
	include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_bottom.php');
}
