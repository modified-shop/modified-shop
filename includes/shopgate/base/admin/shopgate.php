<?php
require_once 'includes/application_top.php';

defined( '_VALID_XTC' ) or die('Direct Access not allowed.');

require(DIR_FS_CATALOG.'/includes/shopgate/shopgate_library/shopgate.php');
require(DIR_FS_CATALOG.'/includes/shopgate/base/shopgate_config.php');
$encodings = array('UTF-8', 'ISO-8859-1', 'ISO-8859-15');
$error = array();

if (isset($_GET['action']) && ($_GET["action"] === "save")) {
	try {
		$shopgate_config = new ShopgateConfigModified($_POST["_shopgate_config"]);
		$shopgate_config->saveFile(array_keys($_POST['_shopgate_config']));
		xtc_redirect(FILENAME_SHOPGATE."?sg_option=".$_GET["sg_option"]);
	} catch (ShopgateLibraryException $e) {
		$message = 'Speichern der Konfiguration fehlgeschlagen. ';
		switch ($e->getCode()) {
			case ShopgateLibraryException::CONFIG_READ_WRITE_ERROR:
				$message .= 'Bitte überprüfen Sie die Schreibrechte für die Konfigurationsdatei von Shopgate.';
			break;
			case ShopgateLibraryException::CONFIG_INVALID_VALUE:
				$message .= 'Bitte überprüfen Sie ihre Eingaben. ('.$e->getAdditionalInformation().')';
				foreach (explode(',', $e->getAdditionalInformation()) as $errorField) {
					$error[$errorField] = true;
				}
			break;
		}
		$shopgate_config = $_POST['_shopgate_config']; // Formular-Eingaben beibehalten
	}
} else {
	$shopgate_config = new ShopgateConfigModified();
	$shopgate_config = $shopgate_config->toArray();
}

if($_GET["sg_option"] === "config") {

} else if($_GET["sg_option"] === "config_ext") {

	$sgOrderStatus = array();
	$qry = xtc_db_query("
		SELECT orders_status_id, orders_status_name
		FROM orders_status os
		JOIN languages l ON l.languages_id = os.language_id
		WHERE " . (!empty($_SESSION['language']) ? ("UPPER(l.directory) = '".strtoupper($_SESSION['language'])."'") : ("UPPER(l.code) = '".strtoupper($shopgate_config['language'])."'")) ."
		ORDER BY os.orders_status_id"
	);

	while ($row = xtc_db_fetch_array($qry)) {
		$sgOrderStatus[] = $row;
	}

	$sgCustomerGroups = array();
	$qry = xtc_db_query("
		SELECT
			s.customers_status_id,
			s.customers_status_name
		FROM `".TABLE_CUSTOMERS_STATUS."` s
		JOIN `".TABLE_LANGUAGES."` lng ON s.language_id = lng.languages_id
		WHERE UPPER(lng.code) = '".strtoupper($_SESSION['language_code'])."' AND customers_status_id != '0'
	");
	
	while ($row = xtc_db_fetch_array($qry)) {
		$sgCustomerGroups[] = $row;
	}

	$sgTaxZones = array();
	$qry = xtc_db_query("
		SELECT
			geo_zone_id,
			geo_zone_name,
			geo_zone_description
		FROM `".TABLE_GEO_ZONES."`
		ORDER BY geo_zone_id
	");
	while ($row = xtc_db_fetch_array($qry)) {
		$sgTaxZones[] = $row;
	}

	$sgCurrencies = array();
	$qry = xtc_db_query("
		SELECT
			*
		FROM `".TABLE_CURRENCIES."`
		ORDER BY title
	");
	while ($row = xtc_db_fetch_array($qry)) {
		$sgCurrencies[$row["code"]] = $row["title"];
	}

	$sgCountries = array();
	$qry = xtc_db_query("
		SELECT
			UPPER(countries_iso_code_2) AS countries_iso_code_2,
			countries_name
		FROM `".TABLE_COUNTRIES."`
		WHERE status = 1
		ORDER BY countries_name
	");
	while ($row = xtc_db_fetch_array($qry)) {
		$sgCountries[] = $row;
	}

	$sgLanguages = array();
	$qry = xtc_db_query("
		SELECT
			UPPER(code) AS code,
			name
		FROM `".TABLE_LANGUAGES."`
		ORDER BY code
	");
	while ($row = xtc_db_fetch_array($qry)) {
		$sgLanguages[] = $row;
	}
}










$shopgateWikiLink = 'http://wiki.shopgate.com/Modified/de';


?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_SESSION['language_charset']; ?>">
	<title><?php echo TITLE; ?></title>
	<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
	<script type="text/javascript" src="includes/general.js"></script>
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
		
		td.shopgate_input.error div input {
			border-color: red;
		}
	</style>
</head>
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
							<div class="pageHeading" style="background-image: url(images/gm_icons/module.png)">
								<?php echo SHOPGATE_CONFIG_TITLE; ?>
							</div>
						</td>
					</tr>
					<tr style="height: 100%;">
						<td class="main" style="height: 100%; vertical-align: top;">
							<?php if(!empty($message)):?>
							<div style="background: #FFD6D9; width; 100%; padding: 10px;">
								<strong style="color: red;"><?php echo SHOPGATE_CONFIG_ERROR; ?></strong>
								<?php echo htmlentities( $message , ENT_COMPAT, "UTF-8") ?>
							</div>
							<?php endif; ?>
<?php if ($_GET["sg_option"] === "info"): ?>
							<iframe src="http://www.shopgate.com/de/sell" class="shopgate_iframe"></iframe>
<?php elseif($_GET["sg_option"] === "help"): ?>
							<iframe src="<?php echo $shopgateWikiLink; ?>" class="shopgate_iframe"></iframe>
<?php elseif($_GET["sg_option"] === "register"): ?>
							<iframe src="https://www.shopgate.com/welcome/shop_register" class="shopgate_iframe"></iframe>
<?php elseif($_GET["sg_option"] === "config"): ?>
							<?php echo xtc_draw_form('shopgate', FILENAME_SHOPGATE, 'sg_option=config&action=save'); ?>
							<table>
								<tr>
									<td class="shopgate_setting" align="right">
										<table width="100%" cellspacing="0" cellpadding="4" border="0" class="shopgate_setting">
											<tr valign="top" class="<?php echo ($alt == 'shopgate_uneven') ? $alt = 'shopgate_even' : $alt = 'shopgate_uneven' ?>">
												<td width="300" class="dataTableContent"><b><?php echo SHOPGATE_CONFIG_CUSTOMER_NUMBER; ?></b></td>
												<td class="dataTableContent shopgate_input<?php echo empty($error['customer_number']) ? '' : ' error' ?>">
													<div><input type="text" name="_shopgate_config[customer_number]" value="<?php echo $shopgate_config["customer_number"]?>" /></div>
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
												<td width="300" class="dataTableContent"><b><?php echo SHOPGATE_CONFIG_SHOP_NUMBER; ?></b></td>
												<td class="dataTableContent shopgate_input<?php echo empty($error['shop_number']) ? '' : ' error' ?>">
													<div><input type="text" name="_shopgate_config[shop_number]" value="<?php echo $shopgate_config["shop_number"]?>" /></div>
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
												<td width="300" class="dataTableContent"><b><?php echo SHOPGATE_CONFIG_APIKEY; ?></b></td>
												<td class="dataTableContent shopgate_input<?php echo empty($error['apikey']) ? '' : ' error' ?>">
													<div><input type="text" name="_shopgate_config[apikey]" value="<?php echo $shopgate_config["apikey"]?>" /></div>
													<?php echo SHOPGATE_CONFIG_APIKEY_DESCRIPTION; ?>
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
												<td width="300" class="dataTableContent"><b><?php echo SHOPGATE_CONFIG_ALIAS; ?></b></td>
												<td class="dataTableContent shopgate_input<?php echo empty($error['alias']) ? '' : ' error' ?>">
													<div><input type="text" name="_shopgate_config[alias]" value="<?php echo $shopgate_config["alias"]?>" /></div>
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
												<td width="300" class="dataTableContent"><b><?php echo SHOPGATE_CONFIG_CNAME; ?></b></td>
												<td class="dataTableContent shopgate_input<?php echo empty($error['cname']) ? '' : ' error' ?>">
													<div><input type="text" name="_shopgate_config[cname]" value="<?php echo $shopgate_config["cname"]?>" /></div>
													<?php echo SHOPGATE_CONFIG_CNAME_DESCRIPTION; ?>
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
												<td width="300" class="dataTableContent"><b><?php echo SHOPGATE_CONFIG_SERVER_TYPE; ?></b></td>
												<td class="dataTableContent shopgate_input<?php echo empty($error['api_url']) ? '' : ' error' ?>">
													<div>
														<select name="_shopgate_config[server]">
															<option value="live" <?php echo $shopgate_config["server"]=='live'?'selected=""':''?>>
																<?php echo SHOPGATE_CONFIG_SERVER_TYPE; ?>
															</option>
															<option value="pg" <?php echo $shopgate_config["server"]=='pg'?'selected=""':''?>>
																<?php echo SHOPGATE_CONFIG_SERVER_TYPE_PG; ?>
															</option>
															<option value="custom" <?php echo $shopgate_config["server"]=='custom'?'selected=""':''?>>
																<?php echo SHOPGATE_CONFIG_SERVER_TYPE_CUSTOM; ?>
															</option>
														</select>
														<br />
														<input type="text" name="_shopgate_config[api_url]" value="<?php echo $shopgate_config["api_url"]?>" /> <?php echo SHOPGATE_CONFIG_SERVER_TYPE_CUSTOM_URL; ?>
													</div>
													<?php echo SHOPGATE_CONFIG_SERVER_TYPE_CUSTOM_URL; ?>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td class="shopgate_setting" align="right">
										<table width="100%" cellspacing="0" cellpadding="4" border="0" class="shopgate_setting">
											<tr valign="top" class="<?php echo ($alt == 'shopgate_uneven') ? $alt = 'shopgate_even' : $alt = 'shopgate_uneven' ?>">
												<td width="300" class="dataTableContent"><b><?php echo SHOPGATE_CONFIG_SHOP_ACTIVATED; ?></b></td>
												<td class="dataTableContent shopgate_input">
													<div>
														<input type="radio" <?php echo  $shopgate_config["shop_is_active"]?'checked=""':''?> value="1" name="_shopgate_config[shop_is_active]">
														<?php echo SHOPGATE_CONFIG_SHOP_ACTIVATED_ON; ?><br>
														<input type="radio" <?php echo !$shopgate_config["shop_is_active"]?'checked=""':''?> value="0" name="_shopgate_config[shop_is_active]">
														<?php echo SHOPGATE_CONFIG_SHOP_ACTIVATED_OFF; ?>
													</div>
													<?php echo SHOPGATE_CONFIG_SHOP_ACTIVATED_DESCRIPTION; ?>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td class="shopgate_setting" align="right">
										<table width="100%" cellspacing="0" cellpadding="4" border="0" class="shopgate_setting">
											<tr valign="top" class="<?php echo ($alt == 'shopgate_uneven') ? $alt = 'shopgate_even' : $alt = 'shopgate_uneven' ?>">
												<td width="300" class="dataTableContent"><b><?php echo SHOPGATE_CONFIG_MOBILE_WEBSITE; ?></b></td>
												<td class="dataTableContent shopgate_input">
													<div>
														<input type="radio" <?php echo $shopgate_config["enable_mobile_website"]?'checked=""':''?> value="1" name="_shopgate_config[enable_mobile_website]">
														<?php echo SHOPGATE_CONFIG_MOBILE_WEBSITE_ON; ?><br>
														<input type="radio" <?php echo !$shopgate_config["enable_mobile_website"]?'checked=""':''?> value="0" name="_shopgate_config[enable_mobile_website]">
														<?php echo SHOPGATE_CONFIG_MOBILE_WEBSITE_OFF; ?>
													</div>
													<?php echo SHOPGATE_CONFIG_MOBILE_WEBSITE_DESCRIPTION; ?>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
							<input type="submit" value="<?php echo SHOPGATE_CONFIG_SAVE; ?>" onclick="this.blur();" class="button">
							</form>
<?php elseif ($_GET["sg_option"] === "config_ext"): ?>
							<?php echo xtc_draw_form('shopgate', FILENAME_SHOPGATE, 'sg_option=config_ext&action=save'); ?>
							<table>
								<tr>
									<td class="shopgate_setting" align="right">
										<table width="100%" cellspacing="0" cellpadding="4" border="0" class="shopgate_setting">
											<tr valign="top" class="<?php echo ($alt == 'shopgate_uneven') ? $alt = 'shopgate_even' : $alt = 'shopgate_uneven' ?>">
												<td width="300" class="dataTableContent"><b><?php echo SHOPGATE_CONFIG_EXTENDED_TAX_ZONE; ?></b></td>
												<td class="dataTableContent shopgate_input">
													<div>
														<select name="_shopgate_config[tax_zone_id]">
															<?php foreach($sgTaxZones as $sgTaxZone): ?>
															<option value="<?php echo $sgTaxZone["geo_zone_id"]?>" <?php echo $shopgate_config["tax_zone_id"]==$sgTaxZone["geo_zone_id"]?'selected=""':''?>>
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
												<td width="300" class="dataTableContent"><b><?php echo SHOPGATE_CONFIG_EXTENDED_CUSTOMER_PRICE_GROUP; ?></b></td>
												<td class="dataTableContent shopgate_input">
													<div>
														<select name="_shopgate_config[customer_price_group]">
															<option value="0"><?php echo SHOPGATE_CONFIG_EXTENDED_CUSTOMER_PRICE_GROUP_OFF; ?></option>
															<?php foreach($sgCustomerGroups as $sgCustomerGroup): ?>
															<option value="<?php echo $sgCustomerGroup["customers_status_id"]?>"
																<?php echo $shopgate_config["customer_price_group"]==$sgCustomerGroup["customers_status_id"]?'selected=""':''?>>
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
								<tr>
									<td class="shopgate_setting" align="right">
										<table width="100%" cellspacing="0" cellpadding="4" border="0" class="shopgate_setting">
											<tr valign="top" class="<?php echo ($alt == 'shopgate_uneven') ? $alt = 'shopgate_even' : $alt = 'shopgate_uneven' ?>">
												<td width="300" class="dataTableContent"><b><?php echo SHOPGATE_CONFIG_EXTENDED_CUSTOMER_GROUP; ?></b></td>
												<td class="dataTableContent shopgate_input">
													<div>
														<select name="_shopgate_config[customers_status_id]">
															<?php foreach($sgCustomerGroups as $sgCustomerGroup): ?>
															<option value="<?php echo $sgCustomerGroup["customers_status_id"]?>"
																<?php echo $shopgate_config["customers_status_id"]==$sgCustomerGroup["customers_status_id"]?'selected=""':''?>>
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
												<td width="300" class="dataTableContent"><b><?php echo SHOPGATE_CONFIG_EXTENDED_CURRENCY; ?></b></td>
												<td class="dataTableContent shopgate_input">
													<div>
														<select name="_shopgate_config[currency]">
															<?php foreach($sgCurrencies as $sgCurrencyCode => $sgCurrency): ?>
															<option value="<?php echo $sgCurrencyCode?>"
																<?php echo $shopgate_config["currency"]==$sgCurrencyCode?'selected=""':''?>>
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
												<td width="300" class="dataTableContent"><b><?php echo SHOPGATE_CONFIG_EXTENDED_LANGUAGE; ?></b></td>
												<td class="dataTableContent shopgate_input">
													<div>
														<select name="_shopgate_config[language]">
															<?php foreach ($sgLanguages as $sgLanguage): ?>
															<option value="<?php echo $sgLanguage["code"]?>" <?php echo strtoupper($shopgate_config["language"])==strtoupper($sgLanguage["code"])?'selected="selected"':''?>>
								        						<?php echo $sgLanguage['name']?>
															</option>
															<?php endforeach; ?>
														</select>
													</div>
													<?php echo SHOPGATE_CONFIG_EXTENDED_LANGUAGE_DESCRIPTION; ?>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td class="shopgate_setting" align="right">
										<table width="100%" cellspacing="0" cellpadding="4" border="0" class="shopgate_setting">
											<tr valign="top" class="<?php echo ($alt == 'shopgate_uneven') ? $alt = 'shopgate_even' : $alt = 'shopgate_uneven' ?>">
												<td width="300" class="dataTableContent"><b><?php echo SHOPGATE_CONFIG_EXTENDED_COUNTRY; ?></b></td>
												<td class="dataTableContent shopgate_input">
													<div>
														<select name="_shopgate_config[country]">
															<?php foreach ($sgCountries as $sgCountry): ?>
															<option value="<?php echo $sgCountry["countries_iso_code_2"]?>" <?php echo $shopgate_config["country"]==$sgCountry["countries_iso_code_2"]?'selected="selected"':''?>>
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
												<td width="300" class="dataTableContent"><b><?php echo SHOPGATE_CONFIG_EXTENDED_ENCODING; ?></b></td>
												<td class="dataTableContent shopgate_input">
													<div>
														<select name="_shopgate_config[encoding]">
															<?php foreach ($encodings as $encoding): ?>
															<option <?php if ($shopgate_config['encoding'] == $encoding) echo 'selected="selected"'; ?>>
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
								<tr><td colspan="2">&nbsp;</td></tr>
								<tr><td colspan="2">&nbsp;</td></tr>
								<tr><th colspan="2" style="text-align: left;"><?php echo SHOPGATE_CONFIG_EXTENDED_STATUS_SETTINGS; ?></th></tr>
								<tr>
									<td class="shopgate_setting" align="right">
										<table width="100%" cellspacing="0" cellpadding="4" border="0" class="shopgate_setting">
											<tr valign="top" class="<?php echo ($alt == 'shopgate_uneven') ? $alt = 'shopgate_even' : $alt = 'shopgate_uneven' ?>">
												<td width="300" class="dataTableContent"><b><?php echo SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_APPROVED; ?></b></td>
												<td class="dataTableContent shopgate_input">
													<div>
														<select name="_shopgate_config[order_status_open]">
															<?php foreach($sgOrderStatus as $_sgOrderStatus): ?>
															<option value="<?php echo $_sgOrderStatus["orders_status_id"]?>"
																<?php echo $shopgate_config["order_status_open"]==$_sgOrderStatus["orders_status_id"]?'selected=""':''?>>
																<?php echo $_sgOrderStatus["orders_status_name"]?> (<?php echo $_sgOrderStatus["orders_status_id"]?>)
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
												<td width="300" class="dataTableContent"><b><?php echo SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_BLOCKED; ?></b></td>
												<td class="dataTableContent shopgate_input">
													<div>
														<select name="_shopgate_config[order_status_shipping_blocked]">
															<?php foreach($sgOrderStatus as $_sgOrderStatus): ?>
															<option value="<?php echo $_sgOrderStatus["orders_status_id"]?>"
																<?php echo $shopgate_config["order_status_shipping_blocked"]==$_sgOrderStatus["orders_status_id"]?'selected=""':''?>>
																<?php echo $_sgOrderStatus["orders_status_name"]?> (<?php echo $_sgOrderStatus["orders_status_id"]?>)
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
												<td width="300" class="dataTableContent"><b><?php echo SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SENT; ?></b></td>
												<td class="dataTableContent shopgate_input">
													<div>
														<select name="_shopgate_config[order_status_shipped]">
															<?php foreach($sgOrderStatus as $_sgOrderStatus): ?>
															<option value="<?php echo $_sgOrderStatus["orders_status_id"]?>"
																<?php echo $shopgate_config["order_status_shipped"]==$_sgOrderStatus["orders_status_id"]?'selected=""':''?>>
																<?php echo $_sgOrderStatus["orders_status_name"]?> (<?php echo $_sgOrderStatus["orders_status_id"]?>)
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
												<td width="300" class="dataTableContent"><b><?php echo SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED; ?></b></td>
												<td class="dataTableContent shopgate_input">
													<div>
														<select name="_shopgate_config[order_status_cancled]">
															<option value="0"><?php echo SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_NOT_SET; ?></option>
															<?php foreach($sgOrderStatus as $_sgOrderStatus): ?>
															<option value="<?php echo $_sgOrderStatus["orders_status_id"]?>"
																<?php echo $shopgate_config["order_status_cancled"]==$_sgOrderStatus["orders_status_id"]?'selected=""':''?>>
																<?php echo $_sgOrderStatus["orders_status_name"]?> (<?php echo $_sgOrderStatus["orders_status_id"]?>)
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
							</table>
							<input type="submit" value="<?php echo SHOPGATE_CONFIG_SAVE; ?>" onclick="this.blur();" class="button">
							</form>
<?php elseif ($_GET["sg_option"] === "merchant"): ?>
							<iframe src="https://www.shopgate.com/users/login/0/2" style="width: 1000px; min-height: 600px; height: 100%; border: 0;"></iframe>
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