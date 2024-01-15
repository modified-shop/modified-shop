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
 * (c) 2010 - 2019 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/Configurator.php');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/ShopAddOns.php');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');
include_once(DIR_MAGNALISTER_INCLUDES.'lib/configFunctions.php');
require_once(DIR_MAGNALISTER_MODULES.'ebay/classes/eBayShippingDetailsProcessor.php');
require_once(DIR_MAGNALISTER_MODULES.'ebay/EbayHelper.php');

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

function eBayGenOauthToken($args, &$value = '') {
	return eBayGenToken($args, $value, false);
}

function eBayGenToken($args, &$value = '', $blTradeAPIToken = true) {
	global $_MagnaSession, $_url;
	$expires = getDBConfigValue('ebay.token.expires', $_MagnaSession['mpID'], '');
	if ($blTradeAPIToken) {
		$expires = getDBConfigValue('ebay.token.expires', $_MagnaSession['mpID'], '');
		$apiRequest = 'GetTokenCreationLink';
		$buttonId = 'requestToken';
	} else {
		$expires = getDBConfigValue('ebay.oauth.token.expires', $_MagnaSession['mpID'], '');
		$apiRequest = 'GetOauthTokenCreationLink';
		$buttonId = 'requestOauthToken';
	}
	$firstToken = '';
	if (!empty($expires)) {
		if(is_numeric($expires))
			$expires = sprintf(ML_EBAY_TEXT_TOKEN_EXPIRES_AT, date('d.m.Y H:i:s', $expires));
		else
			$expires = sprintf(ML_EBAY_TEXT_TOKEN_EXPIRES_AT, date('d.m.Y H:i:s', unix_timestamp($expires)));
	} else {
		$firstToken = ' mlbtn-action';
	}
	return '<input class="ml-button'.$firstToken.' mlbtn-action" type="button" value="'.ML_EBAY_BUTTON_TOKEN_NEW.'" id="'.$buttonId.'"/>
	'.$expires.'
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function() {
	$(\'#'.$buttonId.'\').click(function() {
		jQuery.blockUI(blockUILoading);
		jQuery.ajax({
			\'method\': \'get\',
			\'url\': \''.toURL($_url, array('what' => $apiRequest, 'kind' => 'ajax'), true).'\',
			\'success\': function (data) {
				// some shop systems attach error messages, warnings or even notices
				// to the output, which would be fatal here, so we strip it away
				if (data.indexOf(\'<style\') > 0) {
					data=data.substring(0, data.indexOf(\'<style\'));
				}
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

function eBayTopTenConfig($args = array(), &$value = '') {
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
			$args['key'],
			isset($_POST['conf'][$args['key']])
			? (int)$_POST['conf'][$args['key']]
			: (int)getDBConfigValue($args['key'], $_MagnaSession['mpID'], 10)
		);
	}
}

function eBayShippingConfig($args, &$value = '') {
	global $_MagnaSession;
	if (geteBayBusinessPolicies(true) == false) {
	// regular: render shipping details form
		$shipProc = new eBayShippingDetailsProcessor($args, 'conf', array(
			'mp' => $_MagnaSession['mpID'],
			'mode' => 'conf'
		), $value);
		return $shipProc->process();
	} else {
	// business policies: just show the shipping services
		if (empty($value)) {
			$aDetails = getDBConfigValue(current($args), $_MagnaSession['mpID'], false);
			$blInt = (current($args) == 'ebay.default.shipping.international');
		} else {
			$aDetails = json_decode($value, true);
			$blInt =  (boolean)(strpos($value, 'location'));
		}
		if ($blInt) {
			$sTableId = 'ebay_default_shipping_international';
		} else {
			$sTableId = 'ebay_default_shipping_local';
		}
		$html = '<table id="'.$sTableId.'" class="shippingDetails inlinetable nowrap autoWidth"><tbody>'
		."\n".renderReadonlyShippingDetails($aDetails, $blInt)
		."\n</tbody></table>\n";
		return $html;
	}
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
			if (array_key_exists('OauthTokenExpirationTime', $result['DATA'])) {
				// actually, it's the expiration time for the "refresh token" - but we handle these things within the API (the customer only needs to know when it's time to renew the auth process)
				setDBConfigValue('ebay.oauth.token.expires', $_MagnaSession['mpID'], $result['DATA']['OauthTokenExpirationTime'], true);
			}
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
	} else if($_GET['what'] == 'GetOauthTokenCreationLink') {
		$iframeURL = 'error';
		try {
			//*
			$result = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'GetOauthTokenCreationLink'
			));
			$iframeURL = $result['DATA']['tokenCreationLink'];
			//*/
		} catch (MagnaException $e) { }
		echo $iframeURL;
		exit();
	} elseif ($_GET['what'] == 'GetSellerProfileData'){
		eBayGetSellerProfileData($_GET['value']);
		exit();
	} elseif ($_GET['what'] == 'topTenConfig'){
		eBayTopTenConfig();
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

tokenAvailable(); //each time, so that we have the correct token expiration time
$blResult = false;
try {
    $aResponse = MagnaConnector::gi()->submitRequest(array(
        'ACTION' => 'CheckPaymentProgramAvailability',
    ));
    $blResult = isset($aResponse['IsAvailable']) ? $aResponse['IsAvailable'] : false;
} catch (MagnaException $oEx) {
}
if (!$blResult) {
    unset($form['orderRefund']);
}
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
	$nSite = $_POST['conf']['ebay.site'];
	setDBConfigValue('ebay.site', $_MagnaSession['mpID'], $nSite, true);
        if (!empty($nUser)) {
            try {
                $result = MagnaConnector::gi()->submitRequest(array(
                    'ACTION' => 'SetCredentials',
                    'USERNAME' => $nUser,
                ));
            } catch (MagnaException $e) {
                $boxes .= '
                    <p class="errorBox">'.ML_GENERIC_STATUS_LOGIN_SAVEERROR.'</p>
                ';
            }
        }

	// Business Policies / Shipping Seller Profile: set the shipping discount rules as the Profile defines
	if (array_key_exists('ebay.default.shippingsellerprofile', $_POST['conf'])) {
		$aDiscountProfiles = getDBConfigValue('ebay.shippingprofiles', $_MagnaSession['mpID'], false);
		if (false == $aDiscountProfiles) $aDiscountProfiles = geteBayShippingDiscountProfiles(true);
		$aSellerProfiles = getDBConfigValue('ebay.sellerprofiles', $_MagnaSession['mpID'], false);
		$aDefaultShippingSellerProfileData = $aSellerProfiles['Profiles'][$_POST['conf']['ebay.default.shippingsellerprofile']];
		if (array_key_exists('ShippingDiscount', $aDefaultShippingSellerProfileData)) {
			if (array_key_exists('local', $aDefaultShippingSellerProfileData['ShippingDiscount'])) {
				setDBConfigValue('ebay.default.shippingprofile.local', $_MagnaSession['mpID'], $aDefaultShippingSellerProfileData['ShippingDiscount']['local'], true);
			} else {
				setDBConfigValue('ebay.default.shippingprofile.local', $_MagnaSession['mpID'], 0, true);
			}
			if (array_key_exists('international', $aDefaultShippingSellerProfileData['ShippingDiscount'])) {
				setDBConfigValue('ebay.default.shippingprofile.international', $_MagnaSession['mpID'], $aDefaultShippingSellerProfileData['ShippingDiscount']['international'], true);
			} else {
				setDBConfigValue('ebay.default.shippingprofile.international', $_MagnaSession['mpID'], 0, true);
			}
			if (array_key_exists('LocalPromotionalDiscount', $aDefaultShippingSellerProfileData['ShippingDiscount'])
			     && ('true' === $aDefaultShippingSellerProfileData['ShippingDiscount']['LocalPromotionalDiscount'])) {
				setDBConfigValue('ebay.shippingdiscount.local', $_MagnaSession['mpID'], '{"val":true}', true);
			} else {
				setDBConfigValue('ebay.shippingdiscount.local', $_MagnaSession['mpID'], '{"val":false}', true);
			}
			if (array_key_exists('InternationalPromotionalDiscount', $aDefaultShippingSellerProfileData['ShippingDiscount'])
			     && ('true' === $aDefaultShippingSellerProfileData['ShippingDiscount']['InternationalPromotionalDiscount'])) {
				setDBConfigValue('ebay.shippingdiscount.international', $_MagnaSession['mpID'], '{"val":true}', true);
			} else {
				setDBConfigValue('ebay.shippingdiscount.international', $_MagnaSession['mpID'], '{"val":false}', true);
			}
		} else {
			setDBConfigValue('ebay.default.shippingprofile.local', $_MagnaSession['mpID'], 0, true);
			setDBConfigValue('ebay.default.shippingprofile.international', $_MagnaSession['mpID'], 0, true);
			setDBConfigValue('ebay.shippingdiscount.local', $_MagnaSession['mpID'], '{"val":false}', true);
			setDBConfigValue('ebay.shippingdiscount.international', $_MagnaSession['mpID'], '{"val":false}', true);
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
	if (array_key_exists('ebay.template.mobilecontent', $_POST['conf'])) {
	// Mobile description: only list elements and linebreaks allowed
		$_POST['conf']['ebay.template.mobilecontent'] =
			ltrim(strip_tags($_POST['conf']['ebay.template.mobilecontent'], '<ol></ol><ul></ul><li></li><br><br/><br />'), '/ ');
	// and filter out double content, if mobile content active
		if ($_POST['conf']['ebay.template.usemobile']) {
			EbayHelper::filterDoubleContentFromDescTemplate($_POST['conf']['ebay.template.content'],
			    $_POST['conf']['ebay.template.mobilecontent']);
		}
		
	}
} else {
	$nSite = getDBConfigValue('ebay.site', $_MagnaSession['mpID']);
}

if (isset($currencyError) && (getCurrencyFromMarketplace($_MagnaSession['mpID']) !== false)) {
	$boxes .= $currencyError;
}

$form['ebayaccount']['fields']['site']['values'] = isset($magnaConfig['ebay']['sites'])? $magnaConfig['ebay']['sites']: array();
$magnaConfig['ebay']['currencies'] = isset($magnaConfig['ebay']['currencies'])? $magnaConfig['ebay']['currencies']: array();
if ($nSite !== null) {
	$curVal = array();
	if(isset($magnaConfig['ebay']['currencies'][$nSite])) {
	foreach ($magnaConfig['ebay']['currencies'][$nSite] as $cur) {
		$curVal[$cur] = $cur;
		}
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
		if (count($payment) > 1) {
			$form['payment']['fields']['paymentmethod']['values'] = $payment;
		} else {
			$form['payment']['fields']['paymentmethod']['type'] = 'html';
			$form['payment']['fields']['paymentmethod']['value'] = current($payment);
		}
	
		$shippingprofiles = geteBayShippingDiscountProfiles();
		$form['shipping']['fields']['shippingprofilelocal']['values'] = $shippingprofiles;
		$form['shipping']['fields']['shippingprofileinternational']['values'] = $shippingprofiles;
	}
	/* Business Policies / Rahmenbedingungen für Ihre Angebote */
	$blBusinessPoliciesSet = geteBayBusinessPolicies(true);
	if (!$blBusinessPoliciesSet) {
		// default case (no business policies)
		unset($form['payment']['fields']['paymentsellerprofile']    );
		unset($form['shipping']['fields']['shippingsellerprofile']  );
		unset($form['returnpolicy']['fields']['returnsellerprofile']);
	} else {
		/* if Business Policies set, use SellerProfiles to fill all payment + shipping + return fields */
		$form['payment']['fields']['paymentsellerprofile']['values']     = geteBaySellerPaymentProfiles();
		$form['shipping']['fields']['shippingsellerprofile']['values']   = geteBaySellerShippingProfiles();
		$form['returnpolicy']['fields']['returnsellerprofile']['values'] = geteBaySellerReturnProfiles();
		$sellerProfileContents = getDBConfigValue('ebay.sellerprofile.contents', $_MagnaSession['mpID']);
		$defaultPaymentSellerProfile  =  getDBConfigValue('ebay.default.paymentsellerprofile', $_MagnaSession['mpID'], 0);
		$defaultShippingSellerProfile =  getDBConfigValue('ebay.default.shippingsellerprofile', $_MagnaSession['mpID'], 0);
		$defaultReturnSellerProfile   =  getDBConfigValue('ebay.default.returnsellerprofile', $_MagnaSession['mpID'], 0);
		foreach (array('paymentmethod', 'paypal.address', 'paymentinstructions') as $paymentSetting) {
			if (!isset($sellerProfileContents['Payment'][$defaultPaymentSellerProfile][$paymentSetting])) {
				$sellerProfileContents['Payment'][$defaultPaymentSellerProfile][$paymentSetting] = ''; // because 'value' in magnalister_config is not nullable
			}
		}
		unset($paymentSetting);
		setDBConfigValue('ebay.default.paymentmethod', $_MagnaSession['mpID'], $sellerProfileContents['Payment'][$defaultPaymentSellerProfile]['paymentmethod'], true);
		setDBConfigValue('ebay.paypal.address', $_MagnaSession['mpID'], $sellerProfileContents['Payment'][$defaultPaymentSellerProfile]['paypal.address'], true);
		setDBConfigValue('ebay.paymentinstructions', $_MagnaSession['mpID'], fixHTMLUTF8Entities($sellerProfileContents['Payment'][$defaultPaymentSellerProfile]['paymentinstructions']), true);
        // check if shipping seller profile exists in sellerProfileContents->Shipping
        if (!array_key_exists($defaultShippingSellerProfile, $sellerProfileContents['Shipping'])) {
            // set as empty array to prevent any php errors and warnings
            $sellerProfileContents['Shipping'][$defaultShippingSellerProfile] = array();
        }

        if (array_key_exists('DispatchTimeMax', $sellerProfileContents['Shipping'][$defaultShippingSellerProfile])) {
            setDBConfigValue('ebay.DispatchTimeMax', $_MagnaSession['mpID'], $sellerProfileContents['Shipping'][$defaultShippingSellerProfile]['DispatchTimeMax'], true);
        }
        if (array_key_exists('shipping.local', $sellerProfileContents['Shipping'][$defaultShippingSellerProfile])) {
            setDBConfigValue('ebay.default.shipping.local', $_MagnaSession['mpID'], $sellerProfileContents['Shipping'][$defaultShippingSellerProfile]['shipping.local'], true);
        }
        if (array_key_exists('shipping.international', $sellerProfileContents['Shipping'][$defaultShippingSellerProfile])) {
            setDBConfigValue('ebay.default.shipping.international', $_MagnaSession['mpID'], $sellerProfileContents['Shipping'][$defaultShippingSellerProfile]['shipping.international'], true);
        }
		if (array_key_exists('shippingprofile.local', $sellerProfileContents['Shipping'][$defaultShippingSellerProfile])) {
			setDBConfigValue('ebay.default.shippingprofile.local', $_MagnaSession['mpID'], $sellerProfileContents['Shipping'][$defaultShippingSellerProfile]['shippingprofile.local'], true);
			$blShippingProfileLocalSet = true;
		} else {
			$blShippingProfileLocalSet = false;
		}
		if (array_key_exists('shippingprofile.international', $sellerProfileContents['Shipping'][$defaultShippingSellerProfile])) {
			setDBConfigValue('ebay.default.shippingprofile.international', $_MagnaSession['mpID'], $sellerProfileContents['Shipping'][$defaultShippingSellerProfile]['shippingprofile.international'], true);
			$blShippingProfileInternationalSet = true;
		} else {
			$blShippingProfileInternationalSet = false;
		}
		if (array_key_exists('shippingdiscount.local', $sellerProfileContents['Shipping'][$defaultShippingSellerProfile])) {
			setDBConfigValue('ebay.shippingdiscount.local', $_MagnaSession['mpID'], $sellerProfileContents['Shipping'][$defaultShippingSellerProfile]['shippingdiscount.local'], true);
			$blShippingDiscountLocalSet = true;
		} else {
			setDBConfigValue('ebay.shippingdiscount.local', $_MagnaSession['mpID'], '{"val":false}', true);
			$blShippingDiscountLocalSet = false;
		}
		if (array_key_exists('shippingdiscount.international', $sellerProfileContents['Shipping'][$defaultShippingSellerProfile])) {
			setDBConfigValue('default.shippingdiscount.international', $_MagnaSession['mpID'], $sellerProfileContents['Shipping'][$defaultShippingSellerProfile]['shippingdiscount.international'] , true);
			$blShippingDiscountInternationalSet = true;
		} else {
			setDBConfigValue('ebay.shippingdiscount.international', $_MagnaSession['mpID'], '{"val":false}', true);
			$blShippingDiscountInternationalSet = false;
		}
		setDBConfigValue('ebay.returnpolicy.returnsaccepted', $_MagnaSession['mpID'], $sellerProfileContents['Return'][$defaultReturnSellerProfile]['returnsaccepted'], true);
		setDBConfigValue('ebay.returnpolicy.returnswithin', $_MagnaSession['mpID'], $sellerProfileContents['Return'][$defaultReturnSellerProfile]['returnswithin'], true);
		setDBConfigValue('ebay.returnpolicy.shippingcostpaidby', $_MagnaSession['mpID'], $sellerProfileContents['Return'][$defaultReturnSellerProfile]['shippingcostpaidby'], true);
		setDBConfigValue('ebay.returnpolicy.description', $_MagnaSession['mpID'], fixHTMLUTF8Entities($sellerProfileContents['Return'][$defaultReturnSellerProfile]['description']), true);
		/* disable fields which now depend on seller profiles */
?><script>/*<!CDATA[*/
	$(document).ready(function() {
		$('select[id="config_ebay_DispatchTimeMax"]').prop('disabled', true);
		$('select[id="config_ebay_default_paymentmethod"]').prop('disabled', true);
		$('input[id="config_ebay_paypal_address"]').prop('disabled', true);
		$('textarea[id="config_ebay_paymentinstructions"]').prop('disabled', true);
		$('select[id="config_ebay_default_shippingprofile_local"]').prop('disabled', true);
		$('input[id="conf_ebay.shippingdiscount.local_val"]').prop('disabled', true);
		$('select[id="config_ebay_default_shippingprofile_international"]').prop('disabled', true);
		$('input[id="conf_ebay.shippingdiscount.international_val"]').prop('disabled', true);
		/* if the API doesn't give the info (new AccountAPI), hide fields */
	<?php if (!($blShippingProfileLocalSet || $blShippingDiscountLocalSet)): ?>
		$('#config_ebay_default_shippingprofile_local').parent().parent().hide();
	<?php endif; ?>
	<?php if (!($blShippingProfileInternationalSet || $blShippingDiscountInternationalSet)): ?>
		$('#config_ebay_default_shippingprofile_international').parent().parent().hide();
	<?php endif; ?>
		$('select[id="config_ebay_returnpolicy_returnsaccepted"]').prop('disabled', true);
		$('select[id="config_ebay_returnpolicy_returnswithin"]').prop('disabled', true);
		$('select[id="config_ebay_returnpolicy_shippingcostpaidby"]').prop('disabled', true);
		$('textarea[id="config_ebay_returnpolicy_description"]').prop('disabled', true);

		$('select[id="config_ebay_DispatchTimeMax"]').css('background-color','#dfdfdf');
		$('select[id="config_ebay_default_paymentmethod"]').css('background-color','#dfdfdf');
		$('input[id="config_ebay_paypal_address"]').css('background-color','#dfdfdf');
		$('textarea[id="config_ebay_paymentinstructions"]').css('background-color','#dfdfdf');
		$('select[id="config_ebay_default_shippingprofile_local"]').css('background-color','#dfdfdf');
		$('input[id="conf_ebay.shippingdiscount.local_val"]').css('background-color','#dfdfdf');
		$('select[id="config_ebay_default_shippingprofile_international"]').css('background-color','#dfdfdf');
		$('input[id="conf_ebay.shippingdiscount.international_val"]').css('background-color','#dfdfdf');
		$('select[id="config_ebay_returnpolicy_returnsaccepted"]').css('background-color','#dfdfdf');
		$('select[id="config_ebay_returnpolicy_returnswithin"]').css('background-color','#dfdfdf');
		$('select[id="config_ebay_returnpolicy_shippingcostpaidby"]').css('background-color','#dfdfdf');
		$('textarea[id="config_ebay_returnpolicy_description"]').css('background-color','#dfdfdf');
	});
/*]]>*/</script><?php
		/* add annotations to info texts for disabled fields */
		foreach($form['payment']['fields'] as $sPaymentFieldName => &$aPaymentField) {
			if ('paymentsellerprofile' == $sPaymentFieldName) continue;
			$aPaymentField['desc'] =
				'<span style="color:dimgray">'.$aPaymentField['desc'].'</span><br /><br />'
				.ML_EBAY_NOTE_DISABLED_BC_OF_BUSINESSPOLICIES_PAYMENT; 
		}
		$form['listingdefaults']['fields']['dispatchtimemax']['desc'] = 
			'<span style="color:dimgray">'.$form['listingdefaults']['fields']['dispatchtimemax']['desc'].'</span><br /><br />'
			.ML_EBAY_NOTE_DISABLED_BC_OF_BUSINESSPOLICIES_SHIPPING;
		foreach($form['shipping']['fields'] as $sShippingFieldName => &$aShippingField) {
			if ('shippingsellerprofile' == $sShippingFieldName) continue;
			$aShippingField['desc'] =
				'<span style="color:dimgray">'.$aShippingField['desc'].'</span><br /><br />'
				.ML_EBAY_NOTE_DISABLED_BC_OF_BUSINESSPOLICIES_SHIPPING; 
		}
		foreach($form['returnpolicy']['fields'] as $sReturnFieldName => &$aReturnField) {
			if ('returnsellerprofile' == $sReturnFieldName) continue;
			$aReturnField['desc'] =
				'<span style="color:dimgray">'.$aReturnField['desc'].'</span><br /><br />'
				.ML_EBAY_NOTE_DISABLED_BC_OF_BUSINESSPOLICIES_RETURN; 
		}
	}
}

if (!$auth['state']) {
	$form = array('ebayaccount' => $form['ebayaccount']);
	if (tokenAvailable()) {
		$expires = getDBConfigValue('ebay.token.expires', $_MagnaSession['mpID'], '');
		if (ml_is_datetime($expires) && ($expires < date('Y-m-d H:i:s'))) {
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
	if (    getDBConfigValue('ebay.token', $_MagnaSession['mpID'], '') !== '__saved__'
	     || getDBConfigValue('ebay.token.expires', $_MagnaSession['mpID'], '') == ''  ) {
		tokenAvailable();
	}
	if (  (!$blBusinessPoliciesSet) 
	    &&(!is_array($form['payment']['fields']['paymentmethod']['values']))) {
		$form['payment']['fields']['paymentmethod']['values'] = geteBayPaymentOptions();
		if (count($form['payment']['fields']['paymentmethod']['values']) == 1) {
			$form['payment']['fields']['paymentmethod']['type'] = 'html';
			$form['payment']['fields']['paymentmethod']['value'] = current($form['payment']['fields']['paymentmethod']['values']);
		}
	}
	
	mlGetLanguages($form['listingdefaults']['fields']['language']);
	// show status filter only if products_status is available in shop
	if (!MagnaDB::gi()->columnExistsInTable('products_status', TABLE_PRODUCTS)) {
		unset($form['listingdefaults']['fields']['Statusfilter']);
	}
	$form['location']['fields']['country']['values'] = isset($magnaConfig['ebay']['countries']) ? $magnaConfig['ebay']['countries']:array();
	mlGetCustomersStatus($form['fixedsettings']['fields']['whichprice'], true);
	if (!empty($form['fixedsettings']['fields']['whichprice'])) {
		$form['fixedsettings']['fields']['whichprice']['values']['0'] = ML_LABEL_SHOP_PRICE;
		ksort($form['fixedsettings']['fields']['whichprice']['values']);
		unset($form['fixedsettings']['fields']['specialprices']);
	} else {
		unset($form['fixedsettings']['fields']['whichprice']);
	}	
	// Strike Through Prices: Only on certain Sites
	if (!in_array($nSite, array('UK', 'Germany'))) {
		unset($form['fixedsettings']['fields']['strikepricekind']);
		unset($form['fixedsettings']['fields']['whichstrikeprice']);
	} else {
		mlGetCustomersStatus($form['fixedsettings']['fields']['whichstrikeprice'], true);
		if (!empty($form['fixedsettings']['fields']['whichstrikeprice'])) {
			$form['fixedsettings']['fields']['whichstrikeprice']['values']['-1'] = ML_LABEL_DONT_USE;
			$form['fixedsettings']['fields']['whichstrikeprice']['values']['0'] = ML_LABEL_SHOP_PRICE;
			ksort($form['fixedsettings']['fields']['whichstrikeprice']['values']);
		} else {
			unset($form['fixedsettings']['fields']['whichstrikeprice']);
		}
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
	mlGetOrderStatus($form['orderRefund']['fields']['ebayrefundconfig']['params']['subfields']['status']);
    $form['orderRefund']['fields']['ebayrefundconfig']['params']['subfields']['status']['values'] = array('--' => ML_AMAZON_LABEL_APPLY_PLEASE_SELECT) + $form['orderRefund']['fields']['ebayrefundconfig']['params']['subfields']['status']['values'];
	mlGetShippingModules($form['import']['fields']['defaultshipping']);
	mlGetPaymentModules($form['import']['fields']['defaultpayment']);

	mlPresetTrackingCodeMatching($_MagnaSession['mpID'], 'ebay.orderstatus.carrier.dbmatching', 'ebay.orderstatus.trackingcode.dbmatching');

	if (false == getDBConfigValue('ebay.imagepath', $_MagnaSession['mpID'], false)) {
		$form['images']['fields']['imagepath']['default'] =
			defined('DIR_WS_CATALOG_POPUP_IMAGES')
				? HTTP_CATALOG_SERVER.DIR_WS_CATALOG_POPUP_IMAGES
				: HTTP_CATALOG_SERVER.DIR_WS_CATALOG_IMAGES;
		setDBConfigValue('ebay.imagepath', $_MagnaSession['mpID'], $form['images']['fields']['imagepath']['default'], true);
	}
	if (   'gambioProperties' == getDBConfigValue('general.options', 0, 'old')
	    && ML_ShopAddOns::mlAddOnIsBooked('EbayPicturePack')
        && version_compare(ML_GAMBIO_VERSION, '4.1', '<')
    ) {
        // don't use empty(getDBConfigValue(()) its not supported by PHP 5.4 -  PHP Fatal error:  Can't use function return value in write context
        $varImageField = getDBConfigValue('ebay.imagepath.variations', $_MagnaSession['mpID'], false);
		if (empty($varImageField)) {
			$form['images']['fields']['imagepathvariations']['default'] =
				HTTP_CATALOG_SERVER.DIR_WS_CATALOG.DIR_WS_IMAGES.'product_images/properties_combis_images/';
			setDBConfigValue('ebay.imagepath.variations', $_MagnaSession['mpID'], $form['images']['fields']['imagepathvariations']['default'], true);
		}
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
	# Extra f. eBay: Extended Order ID
	$form['mail']['fields']['mail']['externalDesc'] = str_replace('<dt>#ORDERSUMMARY#</dt>', "<dt>#EORDERID#</dt>\n\t\t\t\t\t\t<dd>eBay Extended Order ID</dd>\n\t\t\t\t\t<dt>#ORDERSUMMARY#</dt>", $form['mail']['fields']['mail']['externalDesc']);
	# Carriers
	$form['orderSyncState']['fields']['carrier']['values'] = array('' => ML_LABEL_NO_SELECTION);
	$carriers = EbayApiConfigValues::gi()->getCarriers();
	foreach ($carriers as $carKey => $carName) {
		$form['orderSyncState']['fields']['carrier']['values'][$carKey] = $carName;
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
	// if Business Policies (Seller Profiles) submitted, adjust resp. fields to display
	if (    array_key_exists('conf', $_POST)
	     && is_array($_POST['conf'])) {
		$sellerProfileContents = getDBConfigValue('ebay.sellerprofile.contents', $_MagnaSession['mpID']);
		if (array_key_exists('ebay.default.paymentsellerprofile', $_POST['conf'])) {
			$_POST['conf']['ebay.default.paymentmethod'] = $sellerProfileContents['Payment'][$_POST['conf']['ebay.default.paymentsellerprofile']]['paymentmethod'];
			$_POST['conf']['ebay.paypal.address'] = $sellerProfileContents['Payment'][$_POST['conf']['ebay.default.paymentsellerprofile']]['paypal.address'];
			$_POST['conf']['ebay.paymentinstructions'] = fixHTMLUTF8Entities($sellerProfileContents['Payment'][$_POST['conf']['ebay.default.paymentsellerprofile']]['paymentinstructions']);
		}
		if (array_key_exists('ebay.default.shippingsellerprofile', $_POST['conf'])) {
			if (array_key_exists('DispatchTimeMax', $sellerProfileContents['Shipping'][$_POST['conf']['ebay.default.shippingsellerprofile']])) $_POST['conf']['ebay.DispatchTimeMax'] = $sellerProfileContents['Shipping'][$_POST['conf']['ebay.default.shippingsellerprofile']]['DispatchTimeMax'];
			$_POST['conf']['ebay.default.shipping.local'] = $sellerProfileContents['Shipping'][$_POST['conf']['ebay.default.shippingsellerprofile']]['shipping.local'];
			$_POST['conf']['ebay.default.shipping.international'] = $sellerProfileContents['Shipping'][$_POST['conf']['ebay.default.shippingsellerprofile']]['shipping.international'];
			$_POST['conf']['ebay.default.shippingprofile.local'] = $sellerProfileContents['Shipping'][$_POST['conf']['ebay.default.shippingsellerprofile']]['shippingprofile.local'];
			$_POST['conf']['ebay.default.shippingprofile.international'] = $sellerProfileContents['Shipping'][$_POST['conf']['ebay.default.shippingsellerprofile']]['shippingprofile.international'];
			$_POST['conf']['ebay.shippingdiscount.local'] = $sellerProfileContents['Shipping'][$_POST['conf']['ebay.default.shippingsellerprofile']]['shippingdiscount.local'];
			$_POST['conf']['ebay.shippingdiscount.international'] = $sellerProfileContents['Shipping'][$_POST['conf']['ebay.default.shippingsellerprofile']]['shippingdiscount.international'];
		}
		if (array_key_exists('ebay.default.returnsellerprofile', $_POST['conf'])) {
			$_POST['conf']['ebay.returnpolicy.returnsaccepted'] = $sellerProfileContents['Return'][$_POST['conf']['ebay.default.returnsellerprofile']]['returnsaccepted'];
			$_POST['conf']['ebay.returnpolicy.returnswithin'] = $sellerProfileContents['Return'][$_POST['conf']['ebay.default.returnsellerprofile']]['returnswithin'];
			$_POST['conf']['ebay.returnpolicy.shippingcostpaidby'] = $sellerProfileContents['Return'][$_POST['conf']['ebay.default.returnsellerprofile']]['shippingcostpaidby'];
			$_POST['conf']['ebay.returnpolicy.description'] = fixHTMLUTF8Entities($sellerProfileContents['Return'][$_POST['conf']['ebay.default.returnsellerprofile']]['description']);
		}
	}

	// adjust strike prices
	if (    array_key_exists('conf', $_POST)
	     && is_array($_POST['conf'])
	     && array_key_exists('ebay.strike.price.kind', $_POST['conf'])) {
		switch($_POST['conf']['ebay.strike.price.kind']) {
			case ('SpecialPrice'): {
				$_POST['conf']['ebay.strike.price.addkind'] = $_POST['conf']['ebay.fixed.price.addkind'];
				$_POST['conf']['ebay.strike.price.factor'] = $_POST['conf']['ebay.fixed.price.factor'];
				$_POST['conf']['ebay.strike.price.signal'] = $_POST['conf']['ebay.fixed.price.signal'];
				$_POST['conf']['ebay.strike.price.group'] = $_POST['conf']['ebay.fixed.price.group'];
				$_POST['conf']['ebay.fixed.price.usespecialoffer'] = array ('val' => true);
				break;
			}
			case ('DontUse'): {
				$_POST['conf']['ebay.strike.price.group'] = '-1';
				// no break here, the following stuff regards also this case:
				// we take all the configuration from main price only in 'SpecialPrice' case, otherwise is must be unset
			}
			default: {
				setDBConfigValue('ebay.strike.price.addkind', $_MagnaSession['mpID'], 'percent', true);
				setDBConfigValue('ebay.strike.price.factor', $_MagnaSession['mpID'], '0', true);
				setDBConfigValue('ebay.strike.price.signal', $_MagnaSession['mpID'], '', true);
			}
		}
	}

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
	if (array_key_exists('conf', $_POST) && is_array($_POST['conf']) &&
	    array_key_exists('configtool', $_POST) && ($_POST['configtool'] == 'MagnaConfigurator')) {
		geteBayBusinessPolicies(true); // refresh BusinessPolicies when form submitted
	}
	$sRenderedForm = $cG->renderConfigForm();
	extendOrderimportPaymentmethodSelection($sRenderedForm);
	echo $sRenderedForm;
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
$(document).ready(function() {
	var standardRow = $('#conf_ebay').find('#standardTemplateButton').closest('tr');
	var mobileRow = $('#conf_ebay').find('#standardTemplateButton_mobile').closest('tr');
	var interval = window.setInterval(function() {
		if (
			typeof tinyMCE === "undefined"
			||
			(
				mobileRow.next("tr").find('.mce-tinymce.mce-container').length > 0
				&& standardRow.next("tr").find('.mce-tinymce.mce-container').length > 0
			)
		) {
			standardRow.find('input[name="mobileTemplateButton"]').on('click', function() {
				mobileRow.show().next("tr").show();
				standardRow.hide().next("tr").hide();
			});
			mobileRow.find('input[name="standardTemplateButton"]').on('click', function() {
				mobileRow.hide().next("tr").hide();
				standardRow.show().next("tr").show();
			}).trigger('click');
			window.clearInterval(interval);
		}
	}, 300);
});
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

// strike through prices
$(document).ready(function() {
    $('select[id="config_ebay_strike_price_group"]').data('ml-oldvalue', $('select[id="config_ebay_strike_price_group"]').val());
    $('input[id="conf_ebay.strike.price.isUVP_val"]').data('ml-oldvalue', $('input[id="conf_ebay.strike.price.isUVP_val"]').prop('checked'));
    if ($('select[id="config_ebay_strike_price_kind"]').val() != 'CustomerGroup') {
        $('select[id="config_ebay_strike_price_group"]').val('<?php echo ML_LABEL_DONT_USE;?>');
        $('input[id="conf_ebay.strike.price.isUVP_val"]').prop('checked', false);
        $('select[id="config_ebay_strike_price_group"]').prop('disabled', true);
        $('select[id="config_ebay_strike_price_group"]').css('background-color','#dfdfdf');
        $('input[id="conf_ebay.strike.price.isUVP_val"]').prop('disabled', true);
        $('input[id="conf_ebay.strike.price.isUVP_val"]').css('background-color','#dfdfdf');
    }
});

$('select[id="config_ebay_strike_price_group"]').change(function() {
    $('select[id="config_ebay_strike_price_group"]').data('ml-oldvalue', $('select[id="config_ebay_strike_price_group"]').val());
});

$('input[id="conf_ebay.strike.price.isUVP_val"]').change(function() {
    $('input[id="conf_ebay.strike.price.isUVP_val"]').data('ml-oldvalue', $('input[id="conf_ebay.strike.price.isUVP_val"]').prop('checked'));
});


$('select[id="config_ebay_strike_price_kind"]').change(function() {
    var sel=$(this);
    if(sel.val() == 'CustomerGroup') {
        $('select[id="config_ebay_strike_price_group"]').val($('select[id="config_ebay_strike_price_group"]').data('ml-oldvalue'));
        $('input[id="conf_ebay.strike.price.isUVP_val"]').prop('checked', $('input[id="conf_ebay.strike.price.isUVP_val"]').data('ml-oldvalue')); 
        $('select[id="config_ebay_strike_price_group"]').prop('disabled', false);
        $('select[id="config_ebay_strike_price_group"]').css('background-color','#fff');
        $('input[id="conf_ebay.strike.price.isUVP_val"]').prop('disabled', false);
        $('input[id="conf_ebay.strike.price.isUVP_val"]').css('background-color','#fff');
    } else {
        $('select[id="config_ebay_strike_price_group"]').val('<?php echo ML_LABEL_DONT_USE;?>');
        $('input[id="conf_ebay.strike.price.isUVP_val"]').prop('checked', false);
        $('select[id="config_ebay_strike_price_group"]').prop('disabled', true);
        $('select[id="config_ebay_strike_price_group"]').css('background-color','#dfdfdf');
        $('input[id="conf_ebay.strike.price.isUVP_val"]').prop('disabled', true);
        $('input[id="conf_ebay.strike.price.isUVP_val"]').css('background-color','#dfdfdf');
    }
    if (sel.val() == 'ManufacturersPrice') {
        // don't change ml-oldvalue here
        var ov=$('input[id="conf_ebay.strike.price.isUVP_val"]').data('ml-oldvalue');
        $('input[id="conf_ebay.strike.price.isUVP_val"]').prop('checked', true);
        $('input[id="conf_ebay.strike.price.isUVP_val"]').data('ml-oldvalue', ov);
    }
});

// import only paid
if ($('input[id="conf_ebay.order.importonlypaid_true"]').attr('checked') == 'checked') {
    	$('select[id="config_ebay_orderstatus_closed"]').prop('disabled', true);
    	$('select[id="config_ebay_orderstatus_paid"]').prop('disabled', true);
    	$('select[id="config_ebay_updateable_orderstatus"]').prop('disabled', true);
    	$('input[id="conf_ebay.update.orderstatus_val"]').prop('checked', false);
    	$('input[id="conf_ebay.update.orderstatus_val"]').prop('disabled', true);

        $('select[id="config_ebay_orderstatus_closed"]').css('color', '#d3d3d3');
    	$('select[id="config_ebay_orderstatus_paid"]').css('color', '#d3d3d3');
    	$('select[id="config_ebay_updateable_orderstatus"]').css('color', '#d3d3d3');
        $('input[id="conf_ebay.update.orderstatus_val"]').css('color', '#d3d3d3');
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
       					$('select[id="config_ebay_orderstatus_paid"]').val($('select[id="config_ebay_orderstatus_open"]').val());
    					$('select[id="config_ebay_orderstatus_closed"]').prop('disabled', true);
    					$('select[id="config_ebay_orderstatus_paid"]').prop('disabled', true);
    					$('select[id="config_ebay_updateable_orderstatus"]').prop('disabled', true);
    					$('input[id="conf_ebay.update.orderstatus_val"]').prop('checked', false);
    					$('input[id="conf_ebay.update.orderstatus_val"]').prop('disabled', true);
					$('select[id="config_ebay_orderstatus_closed"]').css('color', '#d3d3d3');
					$('select[id="config_ebay_orderstatus_paid"]').css('color', '#d3d3d3');
					$('select[id="config_ebay_updateable_orderstatus"]').css('color', '#d3d3d3');
					$('input[id="conf_ebay.update.orderstatus_val"]').css('color', '#d3d3d3');
						jQuery(this).dialog('close');
					}
				}
			})
		});
$('select[id="config_ebay_orderstatus_open"]').change(function() {
    if ($('input[id="conf_ebay.order.importonlypaid_true"]').attr('checked') == 'checked') {
       $('select[id="config_ebay_orderstatus_paid"]').val($('select[id="config_ebay_orderstatus_open"]').val());
    }
});
$('input[id="conf_ebay.order.importonlypaid_false"]').change(function() {
    		var rdio = $(this);
    		if (rdio.attr('checked') == 'checked') {
    			$('select[id="config_ebay_orderstatus_closed"]').prop('disabled', false);
    			$('select[id="config_ebay_orderstatus_paid"]').prop('disabled', false);
    			$('select[id="config_ebay_updateable_orderstatus"]').prop('disabled', false);
    			$('input[id="conf_ebay.update.orderstatus_val"]').prop('disabled', false);
    			$('select[id="config_ebay_orderstatus_closed"]').css('color', 'black');
    			$('select[id="config_ebay_orderstatus_paid"]').css('color', 'black');
    			$('select[id="config_ebay_updateable_orderstatus"]').css('color', 'black');
    			$('input[id="conf_ebay.update.orderstatus_val"]').css('color', 'black');
			}
					
});
/*]]>*/</script><?php
?><script>/*<!CDATA[*/
// gallery plus warn popup
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

// Streichpreise warn popup
	$(document).ready(function() {
      $('select[id="config_ebay_strike_price_kind"]').data('ml-oldvalue', $('select[id="config_ebay_strike_price_kind"]').val());
    });
    $('select[id="config_ebay_strike_price_kind"]').change(function() {
      var sel=$(this);
      var newValue=sel.val();
      if (newValue == 'DontUse' || sel.data('ml-oldvalue') != 'DontUse') {
        sel.data('ml-oldvalue', newValue);
        return true;
      }
      sel.val(sel.data('ml-oldvalue'));
		$('<div></div>').html('<?php echo ML_TEXT_EBAY_WARNING_STRIKE_PRICE_REQUIREMENTS ?>').jDialog({
			title: '<?php echo ML_TITLE_EBAY_WARNING_STRIKE_PRICE_REQUIREMENTS ?>',
			buttons: {
				'<?php echo ML_BUTTON_LABEL_ABORT; ?>': function() {
					jQuery(this).dialog('close');
				},
				'<?php echo ML_BUTTON_LABEL_OK; ?>': function() {
					sel.data('ml-oldvalue', newValue);
					sel.val(newValue);
					jQuery(this).dialog('close');
				}
			}
		})
    });

// seller profiles
	$('select[id="config_ebay_default_paymentsellerprofile"]').change(function() {
	 var sel=$(this);
	 jQuery.ajax({
		method: 'get',
		url: '<?php echo toURL($_url, array('what' => 'GetSellerProfileData', 'kind' => 'ajax'), true)?>',
		data: { 'value': sel.val() },
		dataType: 'json',
		success: function(data) {
			$('select[id="config_ebay_default_paymentmethod"]').val(data['paymentmethod']);
			$('input[id="config_ebay_paypal_address"]').val(data['paypal.address']);
			$('textarea[id="config_ebay_paymentinstructions"]').val(data['paymentinstructions']);
		}
	 });
	});
	$('select[id="config_ebay_default_returnsellerprofile"]').change(function() {
	 var sel=$(this);
	 jQuery.ajax({
		method: 'get',
		url: '<?php echo toURL($_url, array('what' => 'GetSellerProfileData', 'kind' => 'ajax'), true)?>',
		data: { 'value': sel.val() },
		dataType: 'json',
		success: function(data) {
			$('select[id="config_ebay_returnpolicy_returnsaccepted"]').val(data['returnsaccepted']);
			$('select[id="config_ebay_returnpolicy_returnswithin"]').val(data['returnswithin']);
			$('select[id="config_ebay_returnpolicy_shippingcostpaidby"]').val(data['shippingcostpaidby']);
			$('textarea[id="config_ebay_returnpolicy_description"]').val(data['description']);
		}
	 });
	});
	$('select[id="config_ebay_default_shippingsellerprofile"]').change(function() {
	 var sel=$(this);
	 jQuery.ajax({
		method: 'get',
		url: '<?php echo toURL($_url, array('what' => 'GetSellerProfileData', 'kind' => 'ajax'), true)?>',
		data: { 'value': sel.val() },
		dataType: 'json',
		success: function(data) {
			$('select[id="config_ebay_DispatchTimeMax"]').val(data['DispatchTimeMax']);
			$('#ebay_default_shipping_local').html(data['ebay_default_shipping_local']);
			$('#ebay_default_shipping_international').html(data['ebay_default_shipping_international']);
                        if ((typeof data['shippingprofile.local'] == "undefined") && (typeof data['shippingdiscount.local'] == "undefined")) {
				$('#config_ebay_default_shippingprofile_local').parent().parent().hide();
                        } else {
				$('#config_ebay_default_shippingprofile_local').parent().parent().show();
				$('select[id="config_ebay_default_shippingprofile_local"]').val(data['shippingprofile.local']);
				$('input[id="conf_ebay\.shippingdiscount\.local_val"]').prop('checked', ('{"val":true}' == data['shippingdiscount.local']));
                        }
			if ((typeof data['shippingprofile.international'] == "undefined") && (typeof data['shippingdiscount.international'] == "undefined")) {
				$('#config_ebay_default_shippingprofile_international').parent().parent().hide();
			} else {
				$('#config_ebay_default_shippingprofile_international').parent().parent().show();
				$('select[id="config_ebay_default_shippingprofile_international"]').val(data['shippingprofile.international']);
				$('input[id="conf_ebay\.shippingdiscount\.international_val"]').prop('checked', ('{"val":true}' == data['shippingdiscount.international']));
			}
		}
	 });
	});
/*]]>*/</script><?php
	echo $cG->exchangeRateAlert();
	echo $cG->radioAlert('conf_ebay.template.usemobile', ML_LABEL_IMPORTANT, ML_EBAY_POPUP_MOBILEDESC);
	ML_ShopAddOns::generateConfigPopup('EbayProductIdentifierSync', 'conf_ebay.listingdetails.sync', '#conf_ebay');
	ML_ShopAddOns::generateConfigPopup('EbayZeroStockAndRelisting', 'conf_ebay.autorelist', '#conf_ebay');
	ML_ShopAddOns::generateConfigPopup('EbayZeroStockAndRelisting', 'conf_ebay.zerostockontrol', '#conf_ebay');
	ML_ShopAddOns::generateConfigPopup('EbayPicturePack', 'conf_ebay.picturepack_val', '#conf_ebay','checkbox');
}


function mlEbayRefundConfig($args) {
    global $_MagnaSession;
    $sHtml = '<table class="inlinetable nowrap">';
    $form = array();
    $cG = new MLConfigurator($form, $_MagnaSession['mpID'], 'conf_ebay');

    foreach ($args['subfields'] as $item) {
        $idkey = str_replace('.', '_', $item['key']);
        $sHtml .= '<tr><td>'.$cG->renderLabel($item['label'], $idkey).':</td><td>'.$cG->renderInput($item).'</td></tr>';
    }
    $sHtml .= '</table>';
    return $sHtml;
}
