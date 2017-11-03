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

function AmazonShippingAddressConfig($args) {
	global $_MagnaSession;
	$sHtml = '<table>';
	$form = array();
	
	$cG = new MLConfigurator($form, $_MagnaSession['mpID'], 'conf_amazon');
	foreach ($args['subfields'] as $item){
		$idkey = str_replace('.', '_', $item['key']);
		$configValue = getDBConfigValue($item['key'], $_MagnaSession['mpID'],'');
		$value = '';
		if(isset($configValue[$args['currentIndex']])) {
			$value = $configValue[$args['currentIndex']];
		}
		$item['key'] .= '][';
		if(isset($item['params'])){
			$item['params']['value'] = $value;
		}
		$sHtml .='<tr><td>'. $cG->renderLabel($item['label'], $idkey).':</td><td>'.$cG->renderInput($item,$value).'</td></tr>';
	}
	$sHtml .= '</table>';
	return $sHtml;
}

function AmazonShippingLabelAddressCountryConfig($args) {
	$sHtml = '<select name="conf['.$args['key'].']">';
	foreach (amazonMfsGetConfigurationValues(current($args)) as $iso => $name){
		$sHtml .='<option '.($args['value'] == $iso? 'selected=selected' : '' ).' value="'.$iso.'">'.fixHTMLUTF8Entities($name).'</option>';
	}
	$sHtml .= '</select>';
	return $sHtml;
}

function AmazonShippingDimensionConfig($args) {
	global $_MagnaSession;
	$sHtml = '<table>';
	$form = array();
	
	$cG = new MLConfigurator($form, $_MagnaSession['mpID'], 'conf_amazon');
	//put description on separate that
	$item = array_shift($args['subfields']);
	$idkey = str_replace('.', '_', $item['key']);
	$configValue = getDBConfigValue($item['key'], $_MagnaSession['mpID'],'');
	$value = '';
	if(isset($configValue[$args['currentIndex']])) {
		$value = $configValue[$args['currentIndex']];
	} 
	$item['key'] .= '][';
	$sHtml .='<tr><td>'. $cG->renderLabel($item['label'], $idkey).':</td><td  colspan="5" >'.$cG->renderInput($item,$value).'</td></tr>';
	$sHtml .='<tr>';
	foreach ($args['subfields'] as $item){
		$idkey = str_replace('.', '_', $item['key']);
		$configValue = getDBConfigValue($item['key'], $_MagnaSession['mpID'],'');
		$value = null;
		if(isset($configValue[$args['currentIndex']])) {
			$value = $configValue[$args['currentIndex']];
		}
		$item['key'] .= '][';
		$sHtml .='<td>'. $cG->renderLabel($item['label'], $idkey).':</td><td>'.$cG->renderInput($item,$value).'</td>';
	}
	$sHtml .='</tr>';
	$sHtml .= '</table>';
	return $sHtml;
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

/**
 * Adds customer groups to form field.
 *
 * @param array $form
 * @param string $field
 */
function addCustomerGroups(&$form, $field) {
    mlGetCustomersStatus($form[$field], false);
    if (!empty($form[$field])) {
        $form[$field]['values']['0'] = ML_LABEL_SHOP_PRICE;
        ksort($form[$field]['values']);
    } else {
        unset($form[$field]);
    }
}

function getTaxes(&$form) {
	$data = MagnaDB::gi()->fetchArray(eecho('
			SELECT tax_class_id AS id, tax_class_title AS name
			FROM `' . TABLE_TAX_CLASS . '`
		', false));

	$form['values'] = array();

	foreach ($data as $elem) {
		$form['values'][$elem['id']] = fixHTMLUTF8Entities($elem['name']);
	}
}

function renderB2BTaxMatching($args)
{
	global $_MagnaSession;

	return renderTaxMatching($_MagnaSession['mpID'], $args['key']);
}

function renderB2BTaxMatchingCategory($args)
{
	global $_MagnaSession;

	$mpId = $_MagnaSession['mpID'];
	$currentIndex = $args['currentIndex'];
	$categoryKey = 'amazon.b2b.tax_code_category';
	$matchingKey = 'amazon.b2b.tax_code_specific';

	$categories = MagnaConnector::gi()->submitRequest(array('ACTION' => 'GetMainCategories'));
	$configCategories = getDBConfigValue($categoryKey, $mpId, array());
	$configMatching = getDBConfigValue($matchingKey, $mpId, array());

	// sender category select box
	if (isset($args['value'])) {
		$value = $args['value'];
	} else {
		$value = isset($configCategories[$currentIndex]) ? $configCategories[$currentIndex] : '';
	}

	$result = renderTaxMatchingCategorySelect($categoryKey, $categories['DATA'], $value);
	$result .= '<div class="category-tax-match">';
	if (!empty($value) && $value !== 'null') {
		// render tax matching for current category
		$result .= '<br>'.renderTaxMatching($mpId, $matchingKey.']['.$value, $value,
				!empty($configMatching[$value]) ? $configMatching[$value] : array()
			);
	}

	return $result.'<div>';
}

function renderTaxMatching($mpId, $key, $category = '', $configValues = null)
{
	$taxes = MagnaConnector::gi()->submitRequest(array(
		'ACTION' => 'GetB2BProductTaxCode',
		'CATEGORY' => $category,
	));

	$taxes = $taxes['DATA'];
	$shopTaxes = array('values' => array());
	getTaxes($shopTaxes);

	if ($configValues === null) {
		$configValues = getDBConfigValue($key, $mpId, array());
	}

	if (!is_array($configValues)) {
		$configValues = array();
	}

	$html = '<table class="nostyle tax-matching" width="100%">
		<thead><tr>
			<th width="50%">'.ML_LABEL_SHOP_TAXES.'</th>
			<th width="50%">'.ML_AMAZON_TAX_CLASSES.'</th>
		</tr></thead>
		<tbody>';

	foreach ($shopTaxes['values'] as $keyTax => $tax) {
		$html .= '<tr>
			<td class="nowrap" width="50%">'.$tax.'</td>
			<td width="50%">
				<select name="conf['.$key.']['.$keyTax.']">';
		foreach ($taxes as $sKey => $sVal) {
			$html .= '<option value="'.$sKey.'" '
				.(isset($configValues[$keyTax]) && ($configValues[$keyTax] == $sKey) ? 'selected="selected"' : '')
				.'>'.$sVal.'</option>';
		}

		$html .= '</select></td></tr>';
	}

	$html .= '</tbody></table>';

	return $html;
}

function renderTaxMatchingCategorySelect($key, $options, $selectedKey = '')
{
	$id = str_replace(array('[', ']', '.'), '_', $key);
	$result = '<select name="conf['.$key.'][]" id="conf_'.$id.'" class="fullWidth amazon-tax-matching-category">'
		.'<option value="null">'.ML_AMAZON_LABEL_APPLY_PLEASE_SELECT.'</option>';
	if ($options) {
		foreach ($options as $optionKey => $optionValue) {
			if ($optionKey === $selectedKey) {
				$result .= '<option value="'.$optionKey.'" selected="selected">'.fixHTMLUTF8Entities($optionValue).'</option>';
			} else {
				$result .= '<option value="'.$optionKey.'">'.fixHTMLUTF8Entities($optionValue).'</option>';
			}
		}
	}

	$result .= '</select>';

	return $result;
}

$aMarketplaces = amazonGetMarketplaces();

function renderAmazonSite($args) {
	global $_MagnaSession;
	$aMarketplaces = amazonGetMarketplaces();
	$values = $aMarketplaces['Sites'];

	$amazonSite = getDBConfigValue($args['key'], $_MagnaSession['mpID'], array());
	$html = '<select id="config_amazon_site" name="conf[' . $args['key'] . ']">';
	foreach ($values as $key => $val) {
		$html .= '<option value="' . $key . '" '.(
			($key === $amazonSite)
				? 'selected="selected"'
				: ''
			).'>'.$val.'</option>';
	}
	$html .= '</select>';
	return $html;
}
	
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

function validateB2bTierPrices(&$fields) {
	if ($fields['amazon.b2b.active'] === 'true' && $fields['amazon.b2b.discount_type'] !== '') {
		$errors = array();
		$previousQuantity = -1;
		$previousPrice = -1;
		for ($i = 1; $i < 6; $i++) {
			$q = "amazon.b2b.discount_tier$i.quantity";
			$p = "amazon.b2b.discount_tier$i.discount";
			$quantity = priceToFloat($fields[$q]);
			$price = priceToFloat($fields[$p]);

			if (($quantity > 0 && $price <= 0) || ($quantity <= 0 && $price > 0) || $quantity < 0 || $price < 0) {
				$errors[] = $i;
			} else if ($quantity > 0 && $price > 0) {
				if ($i !== 1) {
					if ($previousQuantity >= $quantity || $previousPrice >= $price) {
						$errors[] = $i;
					}
				}

				$previousPrice = $price;
				$previousQuantity = $quantity;
			}
		}

		if (!empty($errors)) {
			$result = '<p class="errorBox"><span class="error bold larger">' . ML_ERROR_LABEL . ':</span>';
			foreach ($errors as $tier) {
				$result .= '<br>' . sprintf(ML_AMAZON_CONF_QUANTITY_TIER_ERROR, $tier);
			}

			$result .= '</p>';
			return $result;
		}
	}

	return '';
}

$aMarketplaces = amazonGetMarketplaces();
$form['amazonaccount']['fields']['site']['values'] = $aMarketplaces['Sites'];
	
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

	$boxes .= validateB2bTierPrices($_POST['conf']);
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

	addCustomerGroups($form['price']['fields'], 'whichprice');
	addCustomerGroups($form['b2b']['fields'], 'whichprice');

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
	$deliveryexpirience = amazonMfsGetConfigurationValues('ServiceOptions');
	$form['shippinglabel']['fields']['deliveryexpirience']['values'] = array_key_exists('DeliveryExperience', $deliveryexpirience) ? $deliveryexpirience['DeliveryExperience'] : array();
	$form['shippinglabel']['fields']['sizeunit']['values'] = amazonMfsGetConfigurationValues('SizeUnits');
	$form['shippinglabel']['fields']['weightunit']['values'] = amazonMfsGetConfigurationValues('WeightUnits');
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

?>
<script>
    if (window['jQuery']) {
        (function ($) {
		function enableB2b(enable, cls) {
			$(cls).parent().find('input, select').prop('disabled', !enable);
		}

		function showMessage() {
			$('<div class="ml-modal dialog2" title="<?php echo ML_LABEL_INFORMATION ?>"></div>')
				.html('<?php echo addslashes(ML_AMAZON_B2B_ACTIVATE_NOTIFICATION); ?>').jDialog({
					width: '500px'
				});
		}

		$('[id="conf_amazon.b2b.active_true"]').click(function() {
			enableB2b(true, '.js-b2b');
			showMessage();
			$('#config_amazon_b2b_discount_type').change();
		});
		$('[id="conf_amazon.b2b.active_false"]').click(function() {
			enableB2b(false, '.js-b2b');
		});
		<?php
		$enabled = getDBConfigValue('amazon.b2b.active', $_MagnaSession['mpID'], false);
		if (!$enabled || $enabled === 'false') { ?>
		enableB2b(false, '.js-b2b');
		<?php } ?>
		$('#amazon_b2b_tax_code_container').on('change', '.amazon-tax-matching-category', function () {
			var me = $(this);
			$.blockUI(blockUILoading);
			$.ajax({
				type: 'POST',
				url: '<?php echo toURL($_url, array('kind' => 'ajax'), true); ?>',
				data: {
					'key': 'amazon.b2b.tax_code_container',
					'action': 'duplicate',
					'kind': 'ajax',
					'skipRadio': true,
					'subtype': 'extern',
					'procFunc': 'renderB2BTaxMatchingCategory',
					'params': {'value': me.val()}
				},
				success: function (data) {
					$.unblockUI();
					me.parent().find('.category-tax-match').html($(data).find('.category-tax-match').html());
				},
				error: function () {
					$.unblockUI();
				},
				dataType: 'html'
			});
		});

		$('#config_amazon_b2b_discount_type').change(function() {
			enableB2b($(this).val() !== '', '.js-b2b-tier');
		}).change();

		$('.errorBox').nextAll('.successBox:first').css('display', 'none');
        })(jQuery);
    }
</script>
