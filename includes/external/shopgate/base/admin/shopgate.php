<?php
require_once 'includes/application_top.php';

defined( '_VALID_XTC' ) or die('Direct Access not allowed.');

require(DIR_FS_CATALOG.'/includes/external/shopgate/shopgate_library/shopgate.php');
require(DIR_FS_CATALOG.'/includes/external/shopgate/base/shopgate_config.php');
$encodings = array('UTF-8', 'ISO-8859-1', 'ISO-8859-15');
$error = array();

// determine configuration language: $_GET > $_SESSION > global (null)
$sg_language = (!empty($_GET['sg_language'])
	? $_GET['sg_language']
	: null
);

// determine redirect_languages for global configuration
if (($sg_language === null) && !isset($_POST['_shopgate_config']['redirect_languages'])) {
	$_POST['_shopgate_config']['redirect_languages'] = array();
}

// load configuration
if (isset($_GET['action']) && ($_GET["action"] === "save")) {
	try {
		$shopgateConfig = new ShopgateConfigModified();
		
		// check if some settings are selected, keep default if not
		$sgEmptySettings = array(
		'language', 'currency', 'country', 'tax_zone_id', 'customer_price_group', 'customer_status_id',
		'order_status_open', 'order_status_shipping_blocked', 'order_status_shipped', 'order_status_cancled'
		);
		foreach ($sgEmptySettings as $sgEmptySetting) {
			if ($_POST['_shopgate_config'][$sgEmptySetting] == '-') {
				$_POST['_shopgate_config'][$sgEmptySetting] = $shopgateConfig->{'get'.$shopgateConfig->camelize($sgEmptySetting, true)}();
			}
		}
		
		$shopgateConfig->loadArray($_POST['_shopgate_config']);
		if (($sg_language !== null) && !empty($_POST['sg_global_switch'])) {
			$shopgateConfig->useGlobalFor($sg_language);
		} else {
			$shopgateConfig->saveFileForLanguage(array_keys($_POST['_shopgate_config']), $sg_language);
		}
		
		xtc_redirect(FILENAME_SHOPGATE.'?sg_option='.$_GET['sg_option'].(($sg_language === null) ? '' : '&sg_language='.$sg_language));
	} catch (ShopgateLibraryException $e) {
		$shopgate_message = SHOPGATE_CONFIG_ERROR_SAVING;
		switch ($e->getCode()) {
			case ShopgateLibraryException::CONFIG_READ_WRITE_ERROR:
				$shopgate_message .= SHOPGATE_CONFIG_ERROR_READ_WRITE;
			break;
			case ShopgateLibraryException::CONFIG_INVALID_VALUE:
				$shopgate_message .= SHOPGATE_CONFIG_ERROR_INVALID_VALUE.$e->getAdditionalInformation();
				foreach (explode(',', $e->getAdditionalInformation()) as $errorField) {
					$error[$errorField] = true;
				}
			break;
		}
		$shopgateConfig = $_POST['_shopgate_config']; // keep submitted form data
	}
} else {
	try {
		$shopgate_message = '';
		$shopgateConfig = new ShopgateConfigModified();
		
		if ($sg_language !== null) {
			$sgUseGlobalConfig = $shopgateConfig->checkUseGlobalFor($sg_language);
		}
		
		$shopgateConfig->loadByLanguage($sg_language);
		
		if ($shopgateConfig->checkDuplicates()) {
			$shopgate_message .= SHOPGATE_CONFIG_ERROR_DUPLICATE_SHOP_NUMBERS;
		}
		
		if ($shopgateConfig->checkMultipleConfigs()) {
			$shopgate_info = SHOPGATE_CONFIG_INFO_MULTIPLE_CONFIGURATIONS;
		}
		
		$shopgateConfig = $shopgateConfig->toArray();
	} catch (ShopgateLibraryException $e) {
		$shopgate_message .= SHOPGATE_CONFIG_ERROR_LOADING.SHOPGATE_CONFIG_ERROR_READ_WRITE;
		$shopgateConfig = $shopgateConfig->toArray();
	}
}

// load all languages
$qry = xtc_db_query("SELECT LOWER(code) AS code, name, directory FROM `".TABLE_LANGUAGES."` ORDER BY code");

$sgLanguages = array();
while ($row = xtc_db_fetch_array($qry)) {
	$sgLanguages[$row['code']] = $row;
}

// gather information about the system configuration for the plugin configuration
if($_GET["sg_option"] === "config") {
	// get order states
	$qry = xtc_db_query("
		SELECT
			orders_status_id,
			".(($sg_language === null)
					? "CONCAT(orders_status_name, ' (', code, ')') AS orders_status_name"
					: 'orders_status_name'
			).",
			code
		FROM orders_status os
		INNER JOIN languages l ON l.languages_id = os.language_id
		".(($sg_language === null) ? '' : "WHERE LOWER(l.code) = '{$sg_language}'")."
		ORDER BY os.orders_status_id"
	);
	
	$sgOrderStates = array();
	while ($row = xtc_db_fetch_array($qry)) {
		$sgOrderStates[$row['orders_status_id']] = $row;
	}

	// get customer groups
	$qry = xtc_db_query("
		SELECT
			s.customers_status_id,
			".(($sg_language === null)
					? "CONCAT(customers_status_name, ' (', code, ')') AS customers_status_name"
					: 'customers_status_name'
			)."
		FROM `".TABLE_CUSTOMERS_STATUS."` s
		INNER JOIN `".TABLE_LANGUAGES."` l ON s.language_id = l.languages_id
		WHERE
			".(($sg_language === null) ? '' : "LOWER(l.code) = '{$sg_language}' AND")."
			 customers_status_id != '0'
	");
	
	$sgCustomerGroups = array();
	while ($row = xtc_db_fetch_array($qry)) {
		$sgCustomerGroups[$row['customers_status_id']] = $row;
	}

	// get tax zones
	$qry = xtc_db_query("
		SELECT
			geo_zone_id,
			geo_zone_name,
			geo_zone_description
		FROM `".TABLE_GEO_ZONES."`
		ORDER BY geo_zone_id
	");
	
	$sgTaxZones = array();
	while ($row = xtc_db_fetch_array($qry)) {
		$sgTaxZones[$row['geo_zone_id']] = $row;
	}

	// get currencies
	$qry = xtc_db_query("
		SELECT
			*
		FROM `".TABLE_CURRENCIES."`
		ORDER BY title
	");
	
	$sgCurrencies = array();
	while ($row = xtc_db_fetch_array($qry)) {
		$sgCurrencies[$row["code"]] = $row["title"];
	}

	// get countries
	$qry = xtc_db_query("
		SELECT
			UPPER(countries_iso_code_2) AS countries_iso_code_2,
			countries_name
		FROM `".TABLE_COUNTRIES."`
		WHERE status = 1
		ORDER BY countries_name
	");
	
	$sgCountries = array();
	while ($row = xtc_db_fetch_array($qry)) {
		$sgCountries[$row['countries_iso_code_2']] = $row;
	}

	// get directory name by language of the backend interface
	if(!empty($_SESSION['language'])) {
		$languageDirectory = strtolower(trim($_SESSION['language']));
	}
	// fallback to language in config
	if(empty($languageDirectory)) {
		$languageDirectory = $sgLanguages[$shopgateConfig['language']]['directory'];
	}
	// create a list of all installed shipping modules
	$sgInstalledShippingModules = array('' => SHOPGATE_CONFIG_EXTENDED_SHIPPING_NO_SELECTION);
	$installedShippingModules = explode(';', MODULE_SHIPPING_INSTALLED);
	foreach($installedShippingModules as $shippingModule) {
		if(isset($shippingModule) && is_file(DIR_FS_LANGUAGES . $languageDirectory . '/modules/shipping/' . $shippingModule)) {
			require(DIR_FS_LANGUAGES . $languageDirectory . '/modules/shipping/' . $shippingModule);
			
			$shippingModule = substr($shippingModule, 0, strrpos($shippingModule, '.'));
			$sgInstalledShippingModules[$shippingModule] = constant(MODULE_SHIPPING_.strtoupper($shippingModule)._TEXT_TITLE);
		}
	}
}











$shopgateWikiLink = 'http://wiki.shopgate.com/Modified/de';





?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_SESSION['language_charset']; ?>">
	<meta name="robots" content="noindex,nofollow">
	<title><?php echo TITLE; ?></title>
	<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
	<script type="text/javascript" src="includes/general.js"></script>
	<script type="text/javascript">
		<!--
		function sgDisplayLanguageSelection(sg_button) {
			document.getElementById('shopgate_language_selection').setAttribute('style', 'display: block;');
			sg_button.setAttribute('style', 'display: none;');
		}
		
		function sgLoadLanguage(sg_option) {
			var sg_language = document.getElementById("sg_language").options[document.getElementById("sg_language").selectedIndex].value;
			window.location = '<?php echo FILENAME_SHOPGATE ?>?sg_option='+sg_option+((sg_language.length > 0) ? '&sg_language='+sg_language : '');
		}

		function sgToggleSettings(sg_checkbox) {
			document.getElementById("sg_settings").setAttribute('style', (sg_checkbox.checked ? 'display: none;' : 'display: table;'));
		}
		// -->
	</script>
	<style type="text/css">
		.shopgate_iframe {
			width: 1000px;
			min-height: 600px;
			height: 100%;
			border: 0;
		}
		
		table.shopgate_setting {
			
		}
		
		td.shopgate_setting {
			width: 1050px;
		}
		
		tr.shopgate_even {
			
		}
		
		tr.shopgate_uneven {
			
		}
		
		td.shopgate_input div {
			background: #f9f0f1;
			border: 1px solid #cccccc;
			margin-bottom: 10px;
			padding: 2px;
		}
		
		td.shopgate_input.error div input, td.shopgate_input.error div select {
			border-color: red;
		}
		
		div.shopgate_language_selection {
			font-size: 11pt;
			background: #f9f0f1;
			padding: 12px;
			margin-top: 8px;
			margin-bottom: 8px;
			border: 1px dashed #aaaaaa;
			width: 1023px;
		}
		
		div.shopgate_red_message {
			background: #ffd6d9;
			width; 100%;
			padding: 10px;
		}
		
		div.shopgate_blue_message {
			background: #d6e9ff;
			width; 100%;
			padding: 10px;
		}
		
		div.shopgate_language_selection div {
			font-size: 8pt;
			margin-bottom: 8px;
		}
		
		div.sg_submit {
			margin-top: 16px;
		}
		
		div.sg_submit input {
			padding: 2px;
		}
	</style>
</head>
<?php $tableClass = 'dataTableContent'; ?>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF" onload="SetFocus();">

	<!-- header //-->
	<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
	<!-- header_eof //-->

	<!-- body //-->
	<table border="0" width="100%" cellspacing="2" cellpadding="2">
		<tr>
			<td class="columnLeft2" width="<?php echo BOX_WIDTH; ?>" valign="top">
				<table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1"
					cellpadding="1" class="columnLeft">
					<!-- left_navigation //-->
					<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
					<!-- left_navigation_eof //-->
				</table>
			</td>
			<!-- body_text //-->
			<td class="boxCenter" width="100%" valign="top" style="height: 100%;">
				<table border="0" width="100%" cellspacing="0" cellpadding="2" style="height:100%;">
					<tr>
						<td>
							<div class="pageHeading">
								<?php echo SHOPGATE_CONFIG_TITLE; ?>
							</div>
						</td>
					</tr>
					<tr style="height: 100%;">
						<td class="main" style="height: 100%; vertical-align: top;">
							<?php if(!empty($shopgate_message)):?>
							<div class="shopgate_red_message">
								<strong style="color: red;"><?php echo SHOPGATE_CONFIG_ERROR; ?></strong>
								<?php echo htmlentities($shopgate_message , ENT_COMPAT, "UTF-8") ?>
							</div>
							<?php endif; ?>
<?php if ($_GET["sg_option"] === "info"): ?>
							<iframe src="<?php echo SHOPGATE_LINK_HOME; ?>" class="shopgate_iframe"></iframe>
<?php elseif($_GET["sg_option"] === "help"): ?>
							<iframe src="<?php echo $shopgateWikiLink; ?>" class="shopgate_iframe"></iframe>
<?php elseif($_GET["sg_option"] === "register"): ?>
							<iframe src="<?php echo SHOPGATE_LINK_REGISTER; ?>" class="shopgate_iframe"></iframe>
<?php elseif($_GET["sg_option"] === "config"): ?>
							<?php echo xtc_draw_form('shopgate', FILENAME_SHOPGATE, 'sg_option=config&action=save'.(($sg_language === null) ? '' : '&sg_language='.$sg_language)); ?>
							<?php if (count($sgLanguages) > 1): ?>
							<?php if ($sg_language === null): ?>
							<?php if (!empty($shopgate_info)): ?>
							<div class="shopgate_blue_message"><strong style="color: blue;">Info:</strong> <?php echo $shopgate_info; ?></div>
							<br />
							<?php endif; ?>
							<button onclick="sgDisplayLanguageSelection(this); return false;" id="sg_multiple_shops_button"><?php echo SHOPGATE_CONFIG_MULTIPLE_SHOPS_BUTTON ?></button>
							<?php endif ?>
							<div class="shopgate_language_selection" id="shopgate_language_selection" style="display: <?php echo ($sg_language !== null) ? 'block' : 'none' ?>">
								<div><?php echo SHOPGATE_CONFIG_LANGUAGE_SELECTION; ?></div>
								<select onchange="sgLoadLanguage('<?php echo $_GET["sg_option"] ?>')" id="sg_language">
									<option value=""><?php echo SHOPGATE_CONFIG_GLOBAL_CONFIGURATION; ?></option>
									<?php foreach ($sgLanguages as $sgLanguage): ?>
									<option value="<?php echo $sgLanguage['code']; ?>"<?php if ($sgLanguage['code'] == $sg_language) echo ' selected="selected"'; ?>>
										- <?php echo $sgLanguage['name']; ?>
									</option>
									<?php endforeach; ?>
								</select>
							</div>
							<?php if ($sg_language !== null): ?>
							<input type="hidden" name="sg_global_switch" value="0" />
							<input type="checkbox" name="sg_global_switch" value="1" onclick="sgToggleSettings(this)" id="sg_global_switch" <?php if (!empty($sgUseGlobalConfig)) echo 'checked="checked"' ?> />
							<label for="sg_global_switch"><?php echo SHOPGATE_CONFIG_USE_GLOBAL_CONFIG; ?></label>
							<?php endif; ?>
							<?php endif; ?>
							<table id="sg_settings" <?php if (!empty($sgUseGlobalConfig)) echo 'style="display: none;' ?>>
								<tr><td colspan="2">&nbsp;</td></tr>
								<tr><th colspan="2" style="text-align: left;"><?php echo SHOPGATE_CONFIG_CONNECTION_SETTINGS; ?></th></tr>
								<tr>
									<td class="shopgate_setting" align="right">
										<table width="100%" cellspacing="0" cellpadding="4" border="0" class="shopgate_setting">
											<tr valign="top" class="<?php echo ($alt == 'shopgate_uneven') ? $alt = 'shopgate_even' : $alt = 'shopgate_uneven' ?>">
												<td width="300" class="<?php echo $tableClass; ?>"><b><?php echo SHOPGATE_CONFIG_CUSTOMER_NUMBER; ?></b></td>
												<td class="<?php echo $tableClass; ?> shopgate_input<?php echo empty($error['customer_number']) ? '' : ' error' ?>">
													<div><input type="text" name="_shopgate_config[customer_number]" value="<?php echo $shopgateConfig["customer_number"]?>" /></div>
													<?php echo SHOPGATE_CONFIG_CUSTOMER_NUMBER_DESCRIPTION; ?>
													[<a	href="http://www.shopgate.com/merchant/" target="_blank">LINK</a>]
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td class="shopgate_setting" align="right">
										<table width="100%" cellspacing="0" cellpadding="4" border="0" class="shopgate_setting">
											<tr valign="top" class="<?php echo ($alt == 'shopgate_uneven') ? $alt = 'shopgate_even' : $alt = 'shopgate_uneven' ?>">
												<td width="300" class="<?php echo $tableClass; ?>"><b><?php echo SHOPGATE_CONFIG_SHOP_NUMBER; ?></b></td>
												<td class="<?php echo $tableClass; ?> shopgate_input<?php echo empty($error['shop_number']) ? '' : ' error' ?>">
													<div><input type="text" name="_shopgate_config[shop_number]" value="<?php echo $shopgateConfig["shop_number"]?>" /></div>
													<?php echo SHOPGATE_CONFIG_SHOP_NUMBER_DESCRIPTION; ?>
													[<a	href="http://www.shopgate.com/merchant/" target="_blank">LINK</a>]
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td class="shopgate_setting" align="right">
										<table width="100%" cellspacing="0" cellpadding="4" border="0" class="shopgate_setting">
											<tr valign="top" class="<?php echo ($alt == 'shopgate_uneven') ? $alt = 'shopgate_even' : $alt = 'shopgate_uneven' ?>">
												<td width="300" class="<?php echo $tableClass; ?>"><b><?php echo SHOPGATE_CONFIG_APIKEY; ?></b></td>
												<td class="<?php echo $tableClass; ?> shopgate_input<?php echo empty($error['apikey']) ? '' : ' error' ?>">
													<div><input type="text" name="_shopgate_config[apikey]" value="<?php echo $shopgateConfig["apikey"]?>" /></div>
													<?php echo SHOPGATE_CONFIG_APIKEY_DESCRIPTION; ?>
													[<a	href="http://www.shopgate.com/merchant/" target="_blank">LINK</a>]
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr><td colspan="2">&nbsp;</td></tr>
								<tr><td colspan="2">&nbsp;</td></tr>
								<tr><th colspan="2" style="text-align: left;"><?php echo SHOPGATE_CONFIG_MOBILE_REDIRECT_SETTINGS; ?></th></tr>
								<tr>
									<td class="shopgate_setting" align="right">
										<table width="100%" cellspacing="0" cellpadding="4" border="0" class="shopgate_setting">
											<tr valign="top" class="<?php echo ($alt == 'shopgate_uneven') ? $alt = 'shopgate_even' : $alt = 'shopgate_uneven' ?>">
												<td width="300" class="<?php echo $tableClass; ?>"><b><?php echo SHOPGATE_CONFIG_ALIAS; ?></b></td>
												<td class="<?php echo $tableClass; ?> shopgate_input<?php echo empty($error['alias']) ? '' : ' error' ?>">
													<div><input type="text" name="_shopgate_config[alias]" value="<?php echo $shopgateConfig["alias"]?>" /></div>
													<?php echo SHOPGATE_CONFIG_ALIAS_DESCRIPTION; ?>
													[<a	href="http://www.shopgate.com/merchant/" target="_blank">LINK</a>]
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td class="shopgate_setting" align="right">
										<table width="100%" cellspacing="0" cellpadding="4" border="0" class="shopgate_setting">
											<tr valign="top" class="<?php echo ($alt == 'shopgate_uneven') ? $alt = 'shopgate_even' : $alt = 'shopgate_uneven' ?>">
												<td width="300" class="<?php echo $tableClass; ?>"><b><?php echo SHOPGATE_CONFIG_CNAME; ?></b></td>
												<td class="<?php echo $tableClass; ?> shopgate_input<?php echo empty($error['cname']) ? '' : ' error' ?>">
													<div><input type="text" name="_shopgate_config[cname]" value="<?php echo $shopgateConfig["cname"]?>" /></div>
													<?php echo SHOPGATE_CONFIG_CNAME_DESCRIPTION; ?>
													[<a	href="http://www.shopgate.com/merchant/" target="_blank">LINK</a>]
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<?php if ($sg_language === null): ?>
								<tr>
									<td class="shopgate_setting" align="right">
										<table width="100%" cellspacing="0" cellpadding="4" border="0" class="shopgate_setting">
											<tr valign="top" class="<?php echo ($alt == 'shopgate_uneven') ? $alt = 'shopgate_even' : $alt = 'shopgate_uneven' ?>">
												<td width="300" class="<?php echo $tableClass; ?>"><b><?php echo SHOPGATE_CONFIG_REDIRECT_LANGUAGES; ?></b></td>
												<td class="<?php echo $tableClass; ?> shopgate_input<?php echo empty($error['redirect_languages']) ? '' : ' error' ?>">
													<div>
														<select multiple="multiple" name="_shopgate_config[redirect_languages][]">
															<?php foreach ($sgLanguages as $sgLanguageCode => $sgLanguage): ?>
															<?php $sgSelected = (in_array($sgLanguageCode, $shopgateConfig['redirect_languages'])) ? 'selected="selected"' : ''; ?>
															<option value="<?php echo $sgLanguageCode; ?>" <?php echo $sgSelected; ?>><?php echo $sgLanguage['name'] ?></option>
															<?php endforeach; ?>
														</select>
													</div>
													<?php echo SHOPGATE_CONFIG_REDIRECT_LANGUAGES_DESCRIPTION; ?>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<?php endif; ?>
								<tr><td colspan="2">&nbsp;</td></tr>
								<tr><td colspan="2">&nbsp;</td></tr>
								<tr><th colspan="2" style="text-align: left;"><?php echo SHOPGATE_CONFIG_EXPORT_SETTINGS; ?></th></tr>
								<?php if ($sg_language === null): ?>
								<tr>
									<td class="shopgate_setting" align="right">
										<table width="100%" cellspacing="0" cellpadding="4" border="0" class="shopgate_setting">
											<tr valign="top" class="<?php echo ($alt == 'shopgate_uneven') ? $alt = 'shopgate_even' : $alt = 'shopgate_uneven' ?>">
												<td width="300" class="<?php echo $tableClass; ?>"><b><?php echo SHOPGATE_CONFIG_LANGUAGE; ?></b></td>
												<td class="<?php echo $tableClass; ?> shopgate_input<?php echo empty($error['language']) ? '' : ' error' ?>">
													<div>
														<select name="_shopgate_config[language]">
															<?php if (!in_array($shopgateConfig['language'], array_keys($sgLanguages))): ?>
															<option value="-"></option>
															<?php endif; ?>
															<?php foreach ($sgLanguages as $sgLanguageCode => $sgLanguage): ?>
															<?php $sgSelected = ($sgLanguageCode == $shopgateConfig['language']) ? 'selected="selected"' : ''; ?>
															<option value="<?php echo $sgLanguageCode; ?>" <?php echo $sgSelected; ?>><?php echo $sgLanguage['name']; ?></option>
															<?php endforeach; ?>
														</select>
													</div>
													<?php echo SHOPGATE_CONFIG_LANGUAGE_DESCRIPTION; ?>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<?php endif; ?>
								<tr>
									<td class="shopgate_setting" align="right">
										<table width="100%" cellspacing="0" cellpadding="4" border="0" class="shopgate_setting">
											<tr valign="top" class="<?php echo ($alt == 'shopgate_uneven') ? $alt = 'shopgate_even' : $alt = 'shopgate_uneven' ?>">
												<td width="300" class="<?php echo $tableClass; ?>"><b><?php echo SHOPGATE_CONFIG_EXTENDED_CURRENCY; ?></b></td>
												<td class="<?php echo $tableClass; ?> shopgate_input">
													<div>
														<select name="_shopgate_config[currency]">
															<?php if (!in_array($shopgateConfig['currency'], array_keys($sgCurrencies))): ?>
															<option value="-"></option>
															<?php endif; ?>
															<?php foreach ($sgCurrencies as $sgCurrencyCode => $sgCurrency): ?>
															<option value="<?php echo $sgCurrencyCode?>"
																<?php echo $shopgateConfig["currency"]==$sgCurrencyCode?'selected=""':''?>>
																<?php echo $sgCurrency ?>
															</option>
															<?php endforeach; ?>
														</select>
													</div>
													<?php echo SHOPGATE_CONFIG_EXTENDED_CURRENCY_DESCRIPTION; ?>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td class="shopgate_setting" align="right">
										<table width="100%" cellspacing="0" cellpadding="4" border="0" class="shopgate_setting">
											<tr valign="top" class="<?php echo ($alt == 'shopgate_uneven') ? $alt = 'shopgate_even' : $alt = 'shopgate_uneven' ?>">
												<td width="300" class="<?php echo $tableClass; ?>"><b><?php echo SHOPGATE_CONFIG_EXTENDED_COUNTRY; ?></b></td>
												<td class="<?php echo $tableClass; ?> shopgate_input">
													<div>
														<select name="_shopgate_config[country]">
															<?php if (!in_array($shopgateConfig['country'], array_keys($sgCountries))): ?>
															<option value="-"></option>
															<?php endif; ?>
															<?php foreach ($sgCountries as $sgCountry): ?>
															<option value="<?php echo $sgCountry["countries_iso_code_2"]?>" <?php echo $shopgateConfig["country"]==$sgCountry["countries_iso_code_2"]?'selected="selected"':''?>>
																<?php echo $sgCountry["countries_name"]?>
															</option>
															<?php endforeach; ?>
														</select>
													</div>
													<?php echo SHOPGATE_CONFIG_EXTENDED_COUNTRY_DESCRIPTION; ?>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td class="shopgate_setting" align="right">
										<table width="100%" cellspacing="0" cellpadding="4" border="0" class="shopgate_setting">
											<tr valign="top" class="<?php echo ($alt == 'shopgate_uneven') ? $alt = 'shopgate_even' : $alt = 'shopgate_uneven' ?>">
												<td width="300" class="<?php echo $tableClass; ?>"><b><?php echo SHOPGATE_CONFIG_EXTENDED_TAX_ZONE; ?></b></td>
												<td class="<?php echo $tableClass; ?> shopgate_input">
													<div>
														<select name="_shopgate_config[tax_zone_id]">
															<?php if (!in_array($shopgateConfig['tax_zone_id'], array_keys($sgTaxZones))): ?>
															<option value="-"></option>
															<?php endif; ?>
															<?php foreach ($sgTaxZones as $sgTaxZone): ?>
															<option value="<?php echo $sgTaxZone["geo_zone_id"]?>" <?php echo $shopgateConfig["tax_zone_id"]==$sgTaxZone["geo_zone_id"]?'selected=""':''?>>
																<?php echo $sgTaxZone["geo_zone_name"]?>
																(<?php echo $sgTaxZone["geo_zone_id"] ?>)
															</option>
															<?php endforeach; ?>
														</select>
													</div>
													<?php echo SHOPGATE_CONFIG_EXTENDED_TAX_ZONE_DESCRIPTION; ?>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td class="shopgate_setting" align="right">
										<table width="100%" cellspacing="0" cellpadding="4" border="0" class="shopgate_setting">
											<tr valign="top" class="<?php echo ($alt == 'shopgate_uneven') ? $alt = 'shopgate_even' : $alt = 'shopgate_uneven' ?>">
												<td width="300" class="<?php echo $tableClass; ?>"><b><?php echo SHOPGATE_CONFIG_EXTENDED_REVERSE_CATEGORIES_SORT_ORDER; ?></b></td>
												<td class="<?php echo $tableClass; ?> shopgate_input">
													<div>
														<input type="radio" <?php echo  $shopgateConfig["reverse_categories_sort_order"]?'checked=""':''?> value="1" name="_shopgate_config[reverse_categories_sort_order]">
														<?php echo SHOPGATE_CONFIG_EXTENDED_REVERSE_CATEGORIES_SORT_ORDER_ON; ?><br>
														<input type="radio" <?php echo !$shopgateConfig["reverse_categories_sort_order"]?'checked=""':''?> value="0" name="_shopgate_config[reverse_categories_sort_order]">
														<?php echo SHOPGATE_CONFIG_EXTENDED_REVERSE_CATEGORIES_SORT_ORDER_OFF; ?>
													</div>
													<?php echo SHOPGATE_CONFIG_EXTENDED_REVERSE_CATEGORIES_SORT_ORDER_DESCRIPTION; ?>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td class="shopgate_setting" align="right">
										<table width="100%" cellspacing="0" cellpadding="4" border="0" class="shopgate_setting">
											<tr valign="top" class="<?php echo ($alt == 'shopgate_uneven') ? $alt = 'shopgate_even' : $alt = 'shopgate_uneven' ?>">
												<td width="300" class="<?php echo $tableClass; ?>"><b><?php echo SHOPGATE_CONFIG_EXTENDED_REVERSE_ITEMS_SORT_ORDER; ?></b></td>
												<td class="<?php echo $tableClass; ?> shopgate_input">
													<div>
														<input type="radio" <?php echo  $shopgateConfig["reverse_items_sort_order"]?'checked=""':''?> value="1" name="_shopgate_config[reverse_items_sort_order]">
														<?php echo SHOPGATE_CONFIG_EXTENDED_REVERSE_ITEMS_SORT_ORDER_ON; ?><br>
														<input type="radio" <?php echo !$shopgateConfig["reverse_items_sort_order"]?'checked=""':''?> value="0" name="_shopgate_config[reverse_items_sort_order]">
														<?php echo SHOPGATE_CONFIG_EXTENDED_REVERSE_ITEMS_SORT_ORDER_OFF; ?>
													</div>
													<?php echo SHOPGATE_CONFIG_EXTENDED_REVERSE_ITEMS_SORT_ORDER_DESCRIPTION; ?>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td class="shopgate_setting" align="right">
										<table width="100%" cellspacing="0" cellpadding="4" border="0" class="shopgate_setting">
											<tr valign="top" class="<?php echo ($alt == 'shopgate_uneven') ? $alt = 'shopgate_even' : $alt = 'shopgate_uneven' ?>">
												<td width="300" class="<?php echo $tableClass; ?>"><b><?php echo SHOPGATE_CONFIG_EXTENDED_CUSTOMER_PRICE_GROUP; ?></b></td>
												<td class="<?php echo $tableClass; ?> shopgate_input">
													<div>
														<select name="_shopgate_config[customer_price_group]">
															<?php if (!in_array($shopgateConfig['customer_price_group'], array_keys($sgCustomerGroups)) && $shopgateConfig['customer_price_group'] != '0'): ?>
															<option value="-"></option>
															<?php endif; ?>
															<option value="0"><?php echo SHOPGATE_CONFIG_EXTENDED_CUSTOMER_PRICE_GROUP_OFF; ?></option>
															<?php foreach($sgCustomerGroups as $sgCustomerGroup): ?>
															<option value="<?php echo $sgCustomerGroup["customers_status_id"]?>"
																<?php echo $shopgateConfig["customer_price_group"]==$sgCustomerGroup["customers_status_id"]?'selected=""':''?>>
																<?php echo $sgCustomerGroup["customers_status_name"]?>
															</option>
															<?php endforeach; ?>
														</select>
													</div>
													<?php echo SHOPGATE_CONFIG_EXTENDED_CUSTOMER_PRICE_GROUP_DESCRIPTION; ?>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr><td colspan="2">&nbsp;</td></tr>
								<tr><td colspan="2">&nbsp;</td></tr>
								<tr><th colspan="2" style="text-align: left;"><?php echo SHOPGATE_CONFIG_ORDER_IMPORT_SETTINGS; ?></th></tr>
								<tr>
									<td class="shopgate_setting" align="right">
										<table width="100%" cellspacing="0" cellpadding="4" border="0" class="shopgate_setting">
											<tr valign="top" class="<?php echo ($alt == 'shopgate_uneven') ? $alt = 'shopgate_even' : $alt = 'shopgate_uneven' ?>">
												<td width="300" class="<?php echo $tableClass; ?>"><b><?php echo SHOPGATE_CONFIG_EXTENDED_CUSTOMER_GROUP; ?></b></td>
												<td class="<?php echo $tableClass; ?> shopgate_input">
													<div>
														<select name="_shopgate_config[customers_status_id]">
															<?php if (!in_array($shopgateConfig['customers_status_id'], array_keys($sgCustomerGroups))): ?>
															<option value="-"></option>
															<?php endif; ?>
															<?php foreach($sgCustomerGroups as $sgCustomerGroup): ?>
															<option value="<?php echo $sgCustomerGroup["customers_status_id"]?>"
																<?php echo $shopgateConfig["customers_status_id"]==$sgCustomerGroup["customers_status_id"]?'selected=""':''?>>
																<?php echo $sgCustomerGroup["customers_status_name"]?>
															</option>
															<?php endforeach; ?>
														</select>
													</div>
													<?php echo SHOPGATE_CONFIG_EXTENDED_CUSTOMER_GROUP_DESCRIPTION; ?>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td class="shopgate_setting" align="right">
										<table width="100%" cellspacing="0" cellpadding="4" border="0" class="shopgate_setting">
											<tr valign="top" class="<?php echo ($alt == 'shopgate_uneven') ? $alt = 'shopgate_even' : $alt = 'shopgate_uneven' ?>">
												<td width="300" class="<?php echo $tableClass; ?>"><b><?php echo SHOPGATE_CONFIG_EXTENDED_SHIPPING; ?></b></td>
												<td class="<?php echo $tableClass; ?> shopgate_input">
													<div>
														<select name="_shopgate_config[shipping]">
															<?php foreach($sgInstalledShippingModules as $sgShippingModuleId => $sgShippingModuleName): ?>
															<option value="<?php echo $sgShippingModuleId?>"
																<?php echo $shopgateConfig["shipping"]==$sgShippingModuleId?'selected=""':''?>>
																<?php echo $sgShippingModuleName ?>
															</option>
															<?php endforeach; ?>
														</select>
													</div>
													<?php echo SHOPGATE_CONFIG_EXTENDED_SHIPPING_DESCRIPTION; ?>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td class="shopgate_setting" align="right">
										<table width="100%" cellspacing="0" cellpadding="4" border="0" class="shopgate_setting">
											<tr valign="top" class="<?php echo ($alt == 'shopgate_uneven') ? $alt = 'shopgate_even' : $alt = 'shopgate_uneven' ?>">
												<td width="300" class="<?php echo $tableClass; ?>"><b><?php echo SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_APPROVED; ?></b></td>
												<td class="<?php echo $tableClass; ?> shopgate_input">
													<div>
														<select name="_shopgate_config[order_status_open]">
															<?php if (!in_array($shopgateConfig['order_status_open'], array_keys($sgOrderStates))): ?>
															<option value="-"></option>
															<?php endif; ?>
															<?php foreach($sgOrderStates as $sgOrderState): ?>
															<?php $selected = (
																	($shopgateConfig['order_status_open'] == $sgOrderState['orders_status_id']) &&
																	($shopgateConfig['language'] == $sgOrderState['code']))
																	? 'selected="selected"'
																	: ($shopgateConfig['order_status_open'] == $sgOrderState['orders_status_id'])
																		? 'selected="selected"'
																		: '';
															?>
															<option value="<?php echo $sgOrderState["orders_status_id"]?>" <?php echo $selected; ?>>
																<?php echo $sgOrderState["orders_status_name"]?> (<?php echo $sgOrderState["orders_status_id"]?>)
															</option>
															<?php endforeach; ?>
														</select>
													</div>
													<?php echo SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_APPROVED_DESCRIPTION; ?>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td class="shopgate_setting" align="right">
										<table width="100%" cellspacing="0" cellpadding="4" border="0" class="shopgate_setting">
											<tr valign="top" class="<?php echo ($alt == 'shopgate_uneven') ? $alt = 'shopgate_even' : $alt = 'shopgate_uneven' ?>">
												<td width="300" class="<?php echo $tableClass; ?>"><b><?php echo SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_BLOCKED; ?></b></td>
												<td class="<?php echo $tableClass; ?> shopgate_input">
													<div>
														<select name="_shopgate_config[order_status_shipping_blocked]">
															<?php if (!in_array($shopgateConfig['order_status_shipping_blocked'], array_keys($sgOrderStates))): ?>
															<option value="-"></option>
															<?php endif; ?>
															<?php foreach($sgOrderStates as $sgOrderState): ?>
															<?php $selected = (
																	($shopgateConfig['order_status_shipping_blocked'] == $sgOrderState['orders_status_id']) &&
																	($shopgateConfig['language'] == $sgOrderState['code']))
																	? 'selected="selected"'
																	: ($shopgateConfig['order_status_shipping_blocked'] == $sgOrderState['orders_status_id'])
																		? 'selected="selected"'
																		: '';
															?>
															<option value="<?php echo $sgOrderState["orders_status_id"]?>" <?php echo $selected; ?>>
																<?php echo $sgOrderState["orders_status_name"]?> (<?php echo $sgOrderState["orders_status_id"]?>)
															</option>
															<?php endforeach; ?>
														</select>
													</div>
													<?php echo SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_BLOCKED_DESCRIPTION; ?>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td class="shopgate_setting" align="right">
										<table width="100%" cellspacing="0" cellpadding="4" border="0" class="shopgate_setting">
											<tr valign="top" class="<?php echo ($alt == 'shopgate_uneven') ? $alt = 'shopgate_even' : $alt = 'shopgate_uneven' ?>">
												<td width="300" class="<?php echo $tableClass; ?>"><b><?php echo SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SENT; ?></b></td>
												<td class="<?php echo $tableClass; ?> shopgate_input">
													<div>
														<select name="_shopgate_config[order_status_shipped]">
															<?php if (!in_array($shopgateConfig['order_status_shipped'], array_keys($sgOrderStates))): ?>
															<option value="-"></option>
															<?php endif; ?>
															<?php foreach($sgOrderStates as $sgOrderState): ?>
															<?php $selected = (
																	($shopgateConfig['order_status_shipped'] == $sgOrderState['orders_status_id']) &&
																	($shopgateConfig['language'] == $sgOrderState['code']))
																	? 'selected="selected"'
																	: ($shopgateConfig['order_status_shipped'] == $sgOrderState['orders_status_id'])
																		? 'selected="selected"'
																		: '';
															?>
															<option value="<?php echo $sgOrderState["orders_status_id"]?>" <?php echo $selected; ?>>
																<?php echo $sgOrderState["orders_status_name"]?> (<?php echo $sgOrderState["orders_status_id"]?>)
															</option>
															<?php endforeach; ?>
														</select>
													</div>
													<?php echo SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SENT_DESCRIPTION; ?>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td class="shopgate_setting" align="right">
										<table width="100%" cellspacing="0" cellpadding="4" border="0" class="shopgate_setting">
											<tr valign="top" class="<?php echo ($alt == 'shopgate_uneven') ? $alt = 'shopgate_even' : $alt = 'shopgate_uneven' ?>">
												<td width="300" class="<?php echo $tableClass; ?>"><b><?php echo SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED; ?></b></td>
												<td class="<?php echo $tableClass; ?> shopgate_input">
													<div>
														<select name="_shopgate_config[order_status_cancled]">
															<?php if (!in_array($shopgateConfig['order_status_cancled'], array_keys($sgOrderStates)) && $shopgateConfig['customer_price_group'] != '-1'): ?>
															<option value="-"></option>
															<?php endif; ?>
															<option value="-1"><?php echo SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_NOT_SET; ?></option>
															<?php foreach($sgOrderStates as $sgOrderState): ?>
															<?php $selected = (
																	($shopgateConfig['order_status_cancled'] == $sgOrderState['orders_status_id']) &&
																	($shopgateConfig['language'] == $sgOrderState['code']))
																	? 'selected="selected"'
																	: ($shopgateConfig['order_status_cancled'] == $sgOrderState['orders_status_id'])
																		? 'selected="selected"'
																		: '';
															?>
															<option value="<?php echo $sgOrderState["orders_status_id"]?>" <?php echo $selected; ?>>
																<?php echo $sgOrderState["orders_status_name"]?> (<?php echo $sgOrderState["orders_status_id"]?>)
															</option>
															<?php endforeach; ?>
														</select>
													</div>
													<?php echo SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_DESCRIPTION; ?>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr><td colspan="2">&nbsp;</td></tr>
								<tr><td colspan="2">&nbsp;</td></tr>
								<tr><th colspan="2" style="text-align: left;"><?php echo SHOPGATE_CONFIG_SYSTEM_SETTINGS; ?></th></tr>
								<tr>
									<td class="shopgate_setting" align="right">
										<table width="100%" cellspacing="0" cellpadding="4" border="0" class="shopgate_setting">
											<tr valign="top" class="<?php echo ($alt == 'shopgate_uneven') ? $alt = 'shopgate_even' : $alt = 'shopgate_uneven' ?>">
												<td width="300" class="<?php echo $tableClass; ?>"><b><?php echo SHOPGATE_CONFIG_EXTENDED_ENCODING; ?></b></td>
												<td class="<?php echo $tableClass; ?> shopgate_input">
													<div>
														<select name="_shopgate_config[encoding]">
															<?php foreach ($encodings as $encoding): ?>
															<option <?php if ($shopgateConfig['encoding'] == $encoding) echo 'selected="selected"'; ?>>
																<?php echo $encoding; ?>
															</option>
															<?php endforeach; ?>
														</select>
													</div>
													<?php echo SHOPGATE_CONFIG_EXTENDED_ENCODING_DESCRIPTION; ?>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td class="shopgate_setting" align="right">
										<table width="100%" cellspacing="0" cellpadding="4" border="0" class="shopgate_setting">
											<tr valign="top" class="<?php echo ($alt == 'shopgate_uneven') ? $alt = 'shopgate_even' : $alt = 'shopgate_uneven' ?>">
												<td width="300" class="<?php echo $tableClass; ?>"><b><?php echo SHOPGATE_CONFIG_SERVER_TYPE; ?></b></td>
												<td class="<?php echo $tableClass; ?> shopgate_input<?php echo empty($error['api_url']) ? '' : ' error' ?>">
													<div>
														<select name="_shopgate_config[server]">
															<option value="live" <?php echo $shopgateConfig["server"]=='live'?'selected=""':''?>>
																<?php echo SHOPGATE_CONFIG_SERVER_TYPE; ?>
															</option>
															<option value="pg" <?php echo $shopgateConfig["server"]=='pg'?'selected=""':''?>>
																<?php echo SHOPGATE_CONFIG_SERVER_TYPE_PG; ?>
															</option>
															<option value="custom" <?php echo $shopgateConfig["server"]=='custom'?'selected=""':''?>>
																<?php echo SHOPGATE_CONFIG_SERVER_TYPE_CUSTOM; ?>
															</option>
														</select>
														<br />
														<input type="text" name="_shopgate_config[api_url]" value="<?php echo $shopgateConfig["api_url"]?>" /> <?php echo SHOPGATE_CONFIG_SERVER_TYPE_CUSTOM_URL; ?>
													</div>
													<?php echo SHOPGATE_CONFIG_SERVER_TYPE_CUSTOM_URL; ?>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
							<div class="sg_submit"><input type="submit" value="<?php echo SHOPGATE_CONFIG_SAVE; ?>" onclick="this.blur();" class="button"></div>
							</form>
<?php elseif ($_GET["sg_option"] === "merchant"): ?>
							<iframe src="<?php echo SHOPGATE_LINK_LOGIN; ?>" style="width: 1000px; min-height: 600px; height: 100%; border: 0;"></iframe>
<?php endif; ?>
						</td>
					</tr>
				</table>
			</td>
			<!-- body_text_eof //-->
		</tr>
	</table>
	<!-- body_eof //-->

	<!-- footer //-->
	<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
	<!-- footer_eof //-->
	<br />
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>