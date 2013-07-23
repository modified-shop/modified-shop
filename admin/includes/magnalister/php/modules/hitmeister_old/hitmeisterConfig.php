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
 * $Id: hitmeisterConfig.php 2332 2013-04-04 16:12:19Z derpapst $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/Configurator.php');
include_once(DIR_MAGNALISTER_INCLUDES.'lib/configFunctions.php');

$_url['mode'] = 'conf';

$form = loadConfigForm($_lang,
	array(
		'hitmeister.form' => array(),
		'modules/inventorysync.form' => array(),
		'email_template_generic.form' => array()
	), array(
		'_#_platform_#_' => $_MagnaSession['currentPlatform'],
		'_#_platformName_#_' => $_modules[$_Marketplace]['title']
	)
);

#getCountries($form['shipping']['fields']['country']);

# Kurzbeschreibung fuer comment nicht bei oscommerce und xonsoft, gibt's nicht
if (('oscommerce' == SHOPSYSTEM) || ('xonsoft' == SHOPSYSTEM)) {
	$form['itemdetails']['fields']['commentfrom']['default'] = 'title';
	unset($form['itemdetails']['fields']['commentfrom']['values']['short_description']);
} 

getLanguages($form['itemdetails']['fields']['lang']);
#getShippingMethods($form['shipping']['fields']['method']);
getCustomersStatus($form['price']['fields']['whichprice'], false);
if (!empty($form['price']['fields']['whichprice'])) {
	$form['price']['fields']['whichprice']['values']['0'] = ML_LABEL_SHOP_PRICE;
	ksort($form['price']['fields']['whichprice']['values']);
} else {
	unset($form['price']['fields']['whichprice']);
}
GetConditionTypes($form['itemdetails']['fields']['condition']);
GetShippingTimes($form['itemdetails']['fields']['deliverytime']);
getCountriesWithIso2Keys($form['itemdetails']['fields']['location']);
#die (print_m($form['itemdetails'],'itemdetails'));
//getOrderStatus($form['orderstatus']['fields']['openstatus']);
//getOrderStatus($form['orderstatus']['fields']['cancelstatus']);
//getOrderStatus($form['orderstatus']['fields']['shippedstatus']);
getOrderStatus($form['import']['fields']['openstatus']);

getCustomersStatus($form['import']['fields']['customersgroup']);

getShippingModules($form['import']['fields']['defaultshipping']);
getPaymentModules($form['import']['fields']['defaultpayment']);

#$form['shipping']['fields']['method']['label'] = ML_GENERIC_SHIPPING_COST_ADDITIONAL;
$cG = new Configurator($form, $_MagnaSession['mpID'], 'conf_yatego');
$cG->setRenderTabIdent(true);

$boxes = '';
if (array_key_exists('conf', $_POST)) {
    $nIdent = trim($_POST['conf'][$_Marketplace.'.ident']);
    $nAccessKey = trim($_POST['conf'][$_Marketplace.'.accesskey']);

	if (!empty($nIdent) && (getDBConfigValue($_Marketplace.'.authkey', $_MagnaSession['mpID']) == '__saved__') 
	    && empty($nAccessKey)
	) {
		$nAccessKey = '__saved__';
	}

	if ((strpos($nAccessKey, '&#9679;') === false) && (strpos($nAccessKey, '&#8226;') === false)) {
		/*               Windows                                  Mac                */
	    if (!empty($nIdent) && !empty($nAccessKey)) {
	        try {
	            $result = MagnaConnector::gi()->submitRequest(array(
	                'ACTION' => 'SetCredentials',
                    'IDENT' => $nIdent,
                    'ACCESSKEY' => $nAccessKey,
	            ));
	            $boxes .= '
	                <p class="successBox">'.ML_GENERIC_STATUS_LOGIN_SAVED.'</p>
	            ';
	        } catch (MagnaException $e) {
	            $boxes .= '
	                <p class="errorBox">'.ML_GENERIC_STATUS_LOGIN_SAVEERROR.'</p>
	            ';
	        }
	    }
	} else {
        $boxes .= '
            <p class="errorBox">'.ML_ERROR_INVALID_PASSWORD.'</p>
        ';
	}
}

$allCorrect = $cG->processPOST($keysToSubmit);
if (!empty($keysToSubmit)) {
	$request = array(
		'ACTION' => 'SetConfigValues',
		'DATA' => array(),
	);
	foreach ($keysToSubmit as $key) {
		$request['DATA'][$key] = getDBConfigValue($key, $_MagnaSession['mpID']);
	}
	try {
		MagnaConnector::gi()->submitRequest($request);
	} catch (MagnaException $me) { }
}

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
		'access', 'checkin', 'itemdetails', 'stats', 'price', 'inventorysync', 'import', 'mail'
	));
	echo $cG->renderConfigForm();
	include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_bottom.php');
}
