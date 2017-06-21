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
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/Configurator.php');
include_once(DIR_MAGNALISTER_INCLUDES.'lib/configFunctions.php');

function renderAuthError($authError) {
	$mpTimeOut = false;
	$errors = array();
	if (isset($authError['ERRORS']) && !empty($authError['ERRORS'])) {
		foreach ($authError['ERRORS'] as $err) {
			if (isset($err['ERRORMESSAGE'])) {
				$errors[] = $err['ERRORMESSAGE'];
			}
			if (isset($err['ERRORCODE']) && ($err['ERRORCODE'] == 'MARKETPLACE_TIMEOUT')) {
				$mpTimeOut = true;
			}
		}
	}
	if ($mpTimeOut) {
		return '<p class="errorBox">
			<span class="error bold larger">'.ML_ERROR_LABEL.':</span>
			'.ML_ERROR_MARKETPLACE_TIMEOUT.'
		</p>';
	}
	return '<p class="errorBox">
		<span class="error bold larger">'.ML_ERROR_LABEL.':</span>
		'.ML_MEINPAKET_ERROR_ACCESS_DENIED.(
			(!empty($errors))
				? '<br /><br />'.implode('<br />', $errors)
				: ''
		).'</p>';
}

function meinpaketTaxMatching($args, &$value = '') {
	global $_MagnaSession;
	$taxes = MagnaDB::gi()->fetchArray('
		SELECT tc.tax_class_id AS id, MAX(tr.tax_rate) AS rate, tc.tax_class_title AS title
		  FROM '.TABLE_TAX_RATES.' tr, '.TABLE_TAX_CLASS.' tc
		 WHERE tr.tax_class_id=tc.tax_class_id
	  GROUP BY tc.tax_class_id
	');
	$taxMatch = getDBConfigValue($args['key'], $_MagnaSession['mpID'], array());
	$opts = array (
		'Standard' => ML_LABEL_TAX_STANDARD,
		'Reduced'  => ML_LABEL_TAX_REDUCED,
		'Free'     => ML_LABEL_TAX_FREE,
	);
	$html = '<table class="nostyle"><tbody>';
	foreach ($taxes as $tax) {
		$html .= '
			<tr>
				<td>'.$tax['title'].' ('.$tax['rate'].'%)</td>
				<td><select name="conf['.$args['key'].']['.$tax['id'].']">';
		foreach ($opts as $key => $val) {
			$html .= '<option value="'.$key.'" '.(
				(array_key_exists($tax['id'], $taxMatch) && ($taxMatch[$tax['id']] == $key))
					? 'selected="selected"'
					: ''
			).'>'.$val.'</option>';
		}
		$html .= '
				</select></td>
			</tr>';
	}
	$html .= '</tbody></table>';

#	$html .= print_m($taxes, '$taxes');
#	$html .= print_m(func_get_args(), 'func_get_args');
	return $html;
}

function meinpaketDescFieldSelector($args, &$value = '') {
	global $_MagnaSession;
	$pID = MagnaDB::gi()->fetchOne(eecho('
		SELECT p.products_id
		  FROM '.TABLE_PRODUCTS.' p, '.TABLE_PRODUCTS_DESCRIPTION.' pd
		 WHERE p.products_id = pd.products_id
		       AND pd.language_id = "'.$_SESSION['languages_id'].'"
		       AND pd.products_description <> ""
		       AND pd.products_description IS NOT NULL
		 LIMIT 1
	', false));
	$savedVal = getDBConfigValue($args['key'], $_MagnaSession['mpID'], '');
	MLProduct::gi()->setLanguage($_SESSION['languages_id']);
	$p = MLProduct::gi()->getProductById($pID);
	#echo var_dump_pre($p, '$p');
	$html = '
		<select name="conf['.$args['key'].']">
			<option value="">'.ML_LABEL_USE_STANDARD.'</option>';
	foreach ($p as $key => $val) {
		if (!is_string($val)) continue;
		$html .= '
			<option value="'.$key.'" '.(($savedVal == $key) ? 'selected="selected"' : '').'>'.$key.'</option>';
	}
	$html .= '
		</select>';
	return $html;
}

function meinpaketShippingTypeFieldSelector($args, &$value = '') {
	$p = MeinpaketApiConfigValues::gi()->getShippingTypes();
	arrayEntitiesFixHTMLUTF8($p);
	global $_MagnaSession;
	$savedVal = getDBConfigValue($args['key'], $_MagnaSession['mpID'], '');
	$html = '
		<select name="conf['.$args['key'].']">
			<option value="">'.ML_LABEL_DONT_USE.'</option>';
	foreach ($p as $key => $val) {
		if (!is_string($val)) continue;
		$html .= '
			<option value="'.$key.'" '.(($savedVal == $key) ? 'selected="selected"' : '').'>'.$val.'</option>';
	}
	$html .= '
		</select>';
	return $html;
}

$_url['mode'] = 'conf';

$form = loadConfigForm($_lang,
	array(
		'meinpaket.form' => array(),
		'email_template_generic.form' => array()
	), array(
		'_#_platform_#_' => $_MagnaSession['currentPlatform'],
		'_#_platformName_#_' => $_modules[$_Marketplace]['title']
	)
);

mlGetLanguages($form['prepare']['fields']['lang']);
mlGetCustomersStatus($form['price']['fields']['whichprice'], false);
if (!empty($form['price']['fields']['whichprice'])) {
	$form['price']['fields']['whichprice']['values']['0'] = ML_LABEL_SHOP_PRICE;
	ksort($form['price']['fields']['whichprice']['values']);
} else {
	unset($form['price']['fields']['whichprice']);
}
mlGetManufacturers($form['prepare']['fields']['manufacturerfilter']);

mlGetOrderStatus($form['import']['fields']['openstatus']);
mlGetOrderStatus($form['orderSyncState']['fields']['shippedstatus']);
mlGetOrderStatus($form['orderSyncState']['fields']['cancelstatuscustomerrequest']);
mlGetOrderStatus($form['orderSyncState']['fields']['cancelstatusoutofstock']);
mlGetOrderStatus($form['orderSyncState']['fields']['cancelstatusdamagedgoods']);
mlGetOrderStatus($form['orderSyncState']['fields']['cancelstatusdealerrequest']);

mlGetCustomersStatus($form['import']['fields']['customersgroup']);

mlGetShippingModules($form['import']['fields']['defaultshipping']);
mlGetPaymentModules($form['import']['fields']['defaultpayment']);

mlPresetTrackingCodeMatching($_MagnaSession['mpID'], 'meinpaket.orderstatus.carrier', 'meinpaket.orderstatus.trackingcode');

$form['checkin']['fields']['imagepath']['default'] = SHOP_URL_POPUP_IMAGES;

$cG = new MLConfigurator($form, $_MagnaSession['mpID'], 'conf_meinpaket');
$cG->setRenderTabIdent(true);

$boxes = '';
$auth = getDBConfigValue($_Marketplace.'.authed', $_MagnaSession['mpID'], false);
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
		/*			   Windows								  Mac				*/
		setDBConfigValue($_Marketplace.'.authed', $_MagnaSession['mpID'], array (
			'state' => false,
			'expire' => time()
		), true);
		if (!empty($nUser) && !empty($nPass)) {
			try {
				$result = MagnaConnector::gi()->submitRequest(array(
					'ACTION' => 'SetCredentials',
					'USERNAME' => $nUser,
					'PASSWORD' => $nPass,
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
				setDBConfigValue($_Marketplace.'.authed', $_MagnaSession['mpID'], $auth, true);
			} catch (MagnaException $e) {
				$e->setCriticalStatus(false);
				$boxes .= renderAuthError($e->getErrorArray());
			}
		}
	} else {
		$boxes .= '
			<p class="errorBox">'.ML_ERROR_INVALID_PASSWORD.'</p>
		';
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
	echo $cG->renderConfigForm();
	echo $cG->exchangeRateAlert();
	include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_bottom.php');
}
