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
 * $Id: idealoConfig.php 750 2011-02-02 12:11:52Z $
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

//$cG = new MLConfigurator($form, $_MagnaSession['mpID'], 'conf_idealo');
//$cG->setRenderTabIdent(true);

try {
	$result = MagnaConnector::gi()->submitRequest(array(
		'SUBSYSTEM' => 'ComparisonShopping',
		'ACTION' => 'GetCSInfo',
	));
	if ($result['DATA']['HasUpload'] == 'no') {
//		$cG->setTopHTML('
//			<h3>'.ML_COMPARISON_SHOPPING_LABEL_PATH_TO_CSV_TABLE.'</h3>
//			<input type="text" class="fullwidth" value="'.(
//				!empty($result['DATA']['CSVPath']) 
//					? $result['DATA']['CSVPath'] 
//					: ML_COMPARISON_SHOPPING_TEXT_NO_CSV_TABLE_YET
//			).'" /><br/><br/>
//		');
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
$cG = new MLConfigurator($form, $_MagnaSession['mpID'], 'conf_idealo');
$cG->setRenderTabIdent(true);


ob_start();
?>
<script type="text/javascript">
    /*<![CDATA[*/
    (function($) {
        $(document).ready(function() {
            var formElement = $('#conf_idealo');
            var dialog = $('<div style="display:none" title="<?php echo ML_IDEALO_ACTIVATE_CHECKOUT_POPUP_TITLE; ?>"><?php echo ML_IDEALO_ACTIVATE_CHECKOUT_POPUP_CONTENT; ?></div>');
            var tokenElement = formElement.find('[name="conf[idealo.checkout.token]"]');
            var activateDirectInput = formElement.find('[name="conf[idealo.checkout.status][val]"][value="true"]');
            var activateDirectSubElements = formElement.find('[data-direct="true"]');
            $('<div class="ml-disable-panel" style="position:absolute; left:0; right:0; top:0; bottom:0; display: none; background: white; opacity:.6;"></div>')
                .appendTo(activateDirectSubElements.closest('td.input'))
                .on('click', function() {
                    dialog.jDialog();
                })
            ;
            activateDirectSubElements.closest('td.input').wrapInner('<div style="display:inline-block; position:relative;"></div>');
            var disableElement = function(element, disable) {
                element.next('.ml-disable-panel').css('display', disable ? "inherit" : "none");
            }
            activateDirectInput.change(function() {
                    disableElement(activateDirectSubElements, !activateDirectInput.prop("checked") || activateDirectInput.prop("disabled"));
            });
            tokenElement.change(function() {
                disableElement(activateDirectInput, tokenElement.val() === '');
                activateDirectInput.trigger("change");
            }).trigger('change');
        });
    })(jQuery);
    /*]]>*/
</script>
<?php
$cG->setTopHTML(ob_get_clean());

$errorMessage = '';

//Check if checkout token is not filled and checkout option is selected
if(isset($_POST['conf'])){
	$checkoutOption = $_POST['conf']['idealo.checkout.status']['val'];
	$checkoutToken = $_POST['conf']['idealo.checkout.token'];
	if ($checkoutOption === 'true') {
	    if (empty($checkoutToken)) {
		$errorMessage = '<p class="noticeBox">' . ML_IDEALO_CHECKOUT_ERROR . '</p>';
		// reset checkout setting
		$checkoutOption = $_POST['conf']['idealo.checkout.status']['val'] = 'false';
	    }
	}
} else {
	$checkoutOption = false;
}
$cG->processPOST();

try {
//	$result = MagnaConnector::gi()->submitRequest(array(
//		'SUBSYSTEM' => 'ComparisonShopping',
//		'ACTION' => 'GetShippingMethods',
//	));
//
//	if (isset($result['DATA'])) {
//		$form['prepare']['fields']['shippingmethods']['values'] = $result['DATA'];
//	} else {
//		$form['prepare']['fields']['shippingmethods']['values'] = array('noselection' => ML_IDEALO_METHODS_NOT_AVAILABLE);
//	}
    $aResponse = MagnaConnector::gi()->submitRequest(array('ACTION' => 'IsAuthed', 'disableCache' => uniqid()));
    $blDirectBuy = array_key_exists('STATUS', $aResponse) && $aResponse['STATUS'] === 'SUCCESS';
} catch (MagnaException $e) {
//	$form['prepare']['fields']['shippingmethods']['values'] = array('noselection' => ML_IDEALO_METHODS_NOT_AVAILABLE);
    $blDirectBuy = false;
}

//try {
//	$result = MagnaConnector::gi()->submitRequest(array(
//		'SUBSYSTEM' => 'ComparisonShopping',
//		'ACTION' => 'GetPaymentMethods',
//	));
//
//	if (isset($result['DATA'])) {
//		$form['prepare']['fields']['paymentmethods']['values'] = $result['DATA'];
//	} else {
//		$form['prepare']['fields']['paymentmethods']['values'] = array('noselection' => ML_IDEALO_METHODS_NOT_AVAILABLE);
//	}
//} catch (MagnaException $e) {
//	$form['prepare']['fields']['paymentmethods']['values'] = array('noselection' => ML_IDEALO_METHODS_NOT_AVAILABLE);
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
            'SUBSYSTEM' => 'ComparisonShopping',
            'ACTION' => 'IsAuthed',
        ));

        if ($result['STATUS'] !== 'SUCCESS') {
            $errorMessage = '<p class="errorBox">' . ML_GENERIC_STATUS_LOGIN_SAVEERROR . '</p>';
        }
    } catch (MagnaException $e) {
        $errorMessage = '<p class="errorBox">' . ML_GENERIC_STATUS_LOGIN_SAVEERROR . '</p>';
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
