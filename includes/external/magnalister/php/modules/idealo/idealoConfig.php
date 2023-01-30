<?php
/*
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
 * (c) 2010 - 2023 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/Configurator.php');
include_once(DIR_MAGNALISTER_INCLUDES.'lib/configFunctions.php');

$_url['mode'] = 'conf';

$form = loadConfigForm($_lang,
	array(
		'idealo/comparisonshopping_generic.form' => array()
	), array(
		'_#_platform_#_' => $_MagnaSession['currentPlatform'],
		'_#_platformName_#_' => $_modules[$_Marketplace]['title']
	)
);

mlGetCountries($form['shipping']['fields']['country']);
mlGetLanguages($form['lang']['fields']['lang']);
mlGetShippingMethods($form['shipping']['fields']['method']);
mlGetCustomersStatus($form['price']['fields']['whichprice'], false);
if (!empty($form['price']['fields']['whichprice'])) {
	$form['price']['fields']['whichprice']['values']['0'] = ML_LABEL_SHOP_PRICE;
	ksort($form['price']['fields']['whichprice']['values']);
	unset($form['price']['fields']['specialprices']);
} else {
	unset($form['price']['fields']['whichprice']);
}

$form['shop']['fields']['imagepath']['default'] =
	defined('DIR_WS_CATALOG_POPUP_IMAGES')	? HTTP_CATALOG_SERVER.DIR_WS_CATALOG_POPUP_IMAGES
		: HTTP_CATALOG_SERVER.DIR_WS_CATALOG_IMAGES;

mlGetCustomersStatus($form['orders']['fields']['customersgroup']);
mlGetOrderStatus($form['orders']['fields']['openstatus']);
mlGetOrderStatus($form['orderSyncState']['fields']['shippedstatus']);
mlGetOrderStatus($form['orderSyncState']['fields']['cancelstatus']);
mlGetOrderStatus($form['orderSyncState']['fields']['refundstatus']);
$form['orderSyncState']['fields']['refundstatus']['values'] = array('--' => ML_AMAZON_LABEL_APPLY_PLEASE_SELECT) + $form['orderSyncState']['fields']['refundstatus']['values'];


try {
	$result = MagnaConnector::gi()->submitRequest(array(
		'SUBSYSTEM' => 'ComparisonShopping',
		'ACTION' => 'GetCSInfo',
	));
	if ($result['DATA']['HasUpload'] == 'no') {
        $form['account']['fields']['csvpath'] = array(
            'label' => ML_COMPARISON_SHOPPING_LABEL_PATH_TO_CSV_TABLE,
            'type' => 'text',
            'default' => empty($result['DATA']['CSVPath']) 
                ? ML_COMPARISON_SHOPPING_TEXT_NO_CSV_TABLE_YET
                : $result['DATA']['CSVPath']
            ,
            'key' => 'idealo.csvpath',
            'parameters' => array(
                'readonly' => 'readonly'
            ),
        );
        unset($_POST['conf']['idealo.csvpath']);
        unset($magnaConfig['db'][$_MagnaSession['mpID']]['idealo.csvpath']);
	}
} catch (MagnaException $e) {
}

$blShowPopUpForOldToken = false;
$mOldCheckoutStatus = getDBConfigValue('idealo.checkout.status', $_MagnaSession['mpID']);
$sCheckoutStatus = getDBConfigValue('idealo.directbuy.active', $_MagnaSession['mpID']);
$mDirectBuyClientId = getDBConfigValue('idealo.directbuy.clientid', $_MagnaSession['mpID']);
$mDirectBuyClientPassword = getDBConfigValue('idealo.directbuy.password', $_MagnaSession['mpID']);

if(!isset($sCheckoutStatus) && empty($_POST['conf']['idealo.directbuy.active']) &&
    is_array($mOldCheckoutStatus) && isset($mOldCheckoutStatus['val'])){
    $blShowPopUpForOldToken =
        $mOldCheckoutStatus['val'] &&
        $mDirectBuyClientId === null && empty($_POST['conf']['idealo.directbuy.clientid']) &&
        $mDirectBuyClientPassword === null && empty($_POST['conf']['idealo.directbuy.password']);
    if($blShowPopUpForOldToken){
        $form['directbuyactivation']['fields']['directbuyactive']['default'] = 'true';
    }
}
$cG = new MLConfigurator($form, $_MagnaSession['mpID'], 'conf_idealo');
$cG->setRenderTabIdent(true);


ob_start();
?>
    <script type="text/javascript">
        /*<![CDATA[*/
        (function ($) {
            $(document).ready(function () {
                <?php if($blShowPopUpForOldToken){ ?>
                var dialogOldTokenWarning = $('<div style="display:none" title="<?php echo ML_IDEALO_ACTIVATE_CHECKOUT_Old_TOKEN_POPUP_TITLE; ?>"><?php echo ML_IDEALO_ACTIVATE_CHECKOUT_Old_TOKEN_POPUP_CONTENT; ?></div>');
                dialogOldTokenWarning.jDialog();
                <?php } ?>
                var formElement = $('#conf_idealo');
                var dialog = $('<div style="display:none" title="<?php echo ML_IDEALO_ACTIVATE_CHECKOUT_POPUP_TITLE; ?>"><?php echo ML_IDEALO_ACTIVATE_CHECKOUT_POPUP_CONTENT; ?></div>');
                var directBuyActive = formElement.find('#conf_idealo\\.directbuy\\.active_true');
                var directBuyInactive = formElement.find('#conf_idealo\\.directbuy\\.active_false');
                var activateFulFillmentInput = formElement.find('[name="conf[idealo.shipping.methods]"]');
                var activateDirectSubElements = formElement.find('[data-direct="true"]');
                var activateFulFillmentSubElements = formElement.find('[data-fulfillment="Spedition"]');
                $('<div  class="ml-disable-panel" style="position:absolute; left:0; right:0; top:0; bottom:0; display: none; background: white; opacity:.6;"></div>')
                    .appendTo(activateDirectSubElements.closest('td.input'))
                    .on('click', function () {
                        dialog.jDialog();
                    })
                ;
                activateDirectSubElements.closest('td.input').wrapInner('<div style="display:inline-block; position:relative;"></div>');
                activateFulFillmentSubElements.closest('td.input').wrapInner('<div style="display:inline-block; position:relative;"></div>');
                var disableElement = function (disable) {
                    $('.ml-disable-panel').css('display', disable ? "inherit" : "none");

                }
                activateFulFillmentInput.change(function () {
                    var disable = ($(this).val() !== 'Spedition');
                    activateFulFillmentSubElements.each(function (index, item) {
                        item.value = disable ? '' : item.value === '' ? '' : item.value;
                    });
                });
                if(directBuyActive.is(':checked')) {
                    disableElement(false);
                } else {
                    disableElement(true);
                }
                activateFulFillmentInput.trigger("change");

                directBuyInactive.click(function () {
                    disableElement(true);
                    activateFulFillmentInput.trigger("change");
                });

                directBuyActive.click(function () {
                    disableElement(false);
                    activateFulFillmentInput.trigger("change");
                });

              //  Based on the shipping methods disable or enable the the twoManHandlingFee and disposalFee
                var fieldStr = '#config_idealo_shipping_methods'
                var shippingMethod = $(fieldStr).val();
                setDirectBuyCostsStatus(shippingMethod, fieldStr)

                $(fieldStr).on('change', function () {
                    shippingMethod = this.value;
                    setDirectBuyCostsStatus(shippingMethod, fieldStr)
                });

            });
            function setDirectBuyCostsStatus(shippingMethod, fieldStr) {
                var twoManHandlingFee = $(fieldStr + '_twomanhandlingfee');
                var disposalFee = $(fieldStr + '_disposalfee');
                if (shippingMethod != 'Spedition') {
                    twoManHandlingFee.attr("disabled", true).nextAll('.ml-disable-panel').css('display', "inherit");
                    disposalFee.attr("disabled", true).nextAll('.ml-disable-panel').css('display', "inherit");
                } else {
                    twoManHandlingFee.attr("disabled", false).nextAll('.ml-disable-panel').css('display', "none");
                    disposalFee.attr("disabled", false).nextAll('.ml-disable-panel').css('display', "none");
                }
            }

        })(jQuery);
        /*]]>*/
    </script>
<?php
$cG->setTopHTML(ob_get_clean());
if(!isset($errorMessage)) {//$errorMessage could be filled in idealo.php
    $errorMessage = '';
}

//Check if checkout token is not filled and checkout option is selected
if(isset($_POST['conf'])){
	$checkoutOption = $_POST['conf']['idealo.directbuy.active'];
	$sIdealoClientId = $_POST['conf']['idealo.directbuy.clientid'];
    $sIdealoPassword = $_POST['conf']['idealo.directbuy.password'];
    if (getDBConfigValue('idealo.directbuy.password', $_MagnaSession['mpID']) === '__saved__' && empty($sIdealoPassword)) {
        $sIdealoPassword = '__saved__';
    }
	if ($checkoutOption === 'true') {
	    if (empty($checkoutToken) && empty($sIdealoClientId)) {
            $errorMessage = '<p class="noticeBox">' . ML_IDEALO_CHECKOUT_ERROR . '</p>';
            // reset checkout setting
            $checkoutOption = $_POST['conf']['idealo.directbuy.active'] = 'false';
	    }
	}
} else {
	$checkoutOption = false;
}
$cG->processPOST();

try {
    $aResponse = MagnaConnector::gi()->submitRequest(array('ACTION' => 'IsAuthed', 'disableCache' => uniqid()));
    $blDirectBuy = array_key_exists('STATUS', $aResponse) && $aResponse['STATUS'] === 'SUCCESS';
} catch (MagnaException $e) {
//	$form['prepare']['fields']['shippingmethods']['values'] = array('noselection' => ML_IDEALO_METHODS_NOT_AVAILABLE);
    $blDirectBuy = false;
}

if (!$blDirectBuy) {
    $form['account']['fields']['checkoutcredentials']['parameters'] =  array('style' => 'border: 1px solid red;');
}

try {
	$result = MagnaConnector::gi()->submitRequest(array(
		'SUBSYSTEM' => 'ComparisonShopping',
		'ACTION' => 'GetCancellationReasons',
	));

	if (isset($result['DATA'])) {
		$form['orderSyncState']['fields']['cancelreaason']['values'] = $result['DATA'];
	} else {
		$form['orderSyncState']['fields']['cancelreaason']['values'] = array('noselection' => ML_IDEALO_METHODS_NOT_AVAILABLE);
	}
} catch (MagnaException $e) {
	$form['orderSyncState']['fields']['cancelreaason']['values'] = array('noselection' => ML_IDEALO_METHODS_NOT_AVAILABLE);
}

if ($checkoutOption === 'true') {
    try {
        $result = MagnaConnector::gi()->submitRequest(array(
            'ACTION'              => 'SetCredentials',
            'Access.ClientID'     => $sIdealoClientId,
            'Access.ClientSecret' => $sIdealoPassword
        ));
        $errorMessage .= '
					<p class="successBox">'.ML_GENERIC_STATUS_LOGIN_SAVED.'</p>
				';
    } catch (MagnaException $e) {
        $errorMessage .= '
					<p class="errorBox">'.ML_GENERIC_STATUS_LOGIN_SAVEERROR.'</p>
				';
    }
    try {
        $result = MagnaConnector::gi()->submitRequest(array(
            'SUBSYSTEM' => 'ComparisonShopping',
            'ACTION'    => 'IsAuthed',
        ));

        if ($result['STATUS'] !== 'SUCCESS') {
            $errorMessage .= '<p class="errorBox">'.ML_GENERIC_STATUS_LOGIN_SAVEERROR.'</p>';
        }
    } catch (MagnaException $e) {
        $authError = $e->getErrorArray();
        $mpTimeOut = false;
        $errors = array();
        if (is_array($authError) && !empty($authError)
            && isset($authError['ERRORS']) && !empty($authError['ERRORS'])
        ) {
            foreach ($authError['ERRORS'] as $err) {
                if(isset($err['ERRORMESSAGE'])) {
                    $errors[] = fixHTMLUTF8Entities($err['ERRORMESSAGE']);
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
        $errorMessage .= '<p class="errorBox">
        <span class="error bold larger">'.ML_ERROR_LABEL.':</span>
        '.sprintf(ML_MAGNACOMPAT_ERROR_ACCESS_DENIED, 'idealo').(
            (!empty($errors))
                ? '<br /><br />'.implode('<br />', $errors)
                : ''
            ).'</p>';
    }
}

if ($errorMessage) {
    echo $errorMessage;
}

if (isset($_GET['kind']) && ($_GET['kind'] == 'ajax')) {
	echo $cG->processAjaxRequest();
} else {
	include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_top.php');
	echo $cG->renderConfigForm();
	echo $cG->exchangeRateAlert();
	include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_bottom.php');
}
