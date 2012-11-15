<?php
include_once DIR_FS_CATALOG.'includes/shopgate/shopgate_library/shopgate.php';
include_once DIR_FS_CATALOG.'includes/shopgate/base/shopgate_config.php';
$shopgateConfig = new ShopgateConfigModified();

$shopgateMobileHeader = '';
$shopgateLanguages = xtc_db_fetch_array(xtc_db_query("SELECT * FROM `".TABLE_LANGUAGES."` WHERE UPPER(code) = UPPER('".$shopgateConfig->getLanguage()."')"));
$shopgateLanguage = isset($shopgateLanguages['directory']) ? strtolower($shopgateLanguages["directory"]) : 'german';
$shopgateCurrentLanguage = isset($_SESSION['language']) ? strtolower($_SESSION['language']) : 'german';

if ($shopgateConfig->getShopIsActive() && $shopgateConfig->getEnableMobileWebsite()) {
	// instantiate and set up redirect class
	$shopgateBuilder = new ShopgateBuilder($shopgateConfig);
	$shopgateRedirector = &$shopgateBuilder->buildRedirect();
	$shopgateRedirector->setButtonDescription('Mobile Webseite aktivieren');
	
	##################
	# redirect logic #
	##################
	
	// check request for mobile devices
 	if ($shopgateRedirector->isRedirectAllowed() && $shopgateRedirector->isMobileRequest() && ($shopgateCurrentLanguage == $shopgateLanguage)) {
		$shopgateRedirectionUrl = null;
		
		// set redirection url
		if ($product->isProduct) {
			// product redirect
			$shopgateRedirectionUrl = $shopgateRedirector->getItemUrl($product->pID);
		
		} elseif (!empty($current_category_id)) {
			// category redirect
			$shopgateRedirectionUrl = $shopgateRedirector->getCategoryUrl($current_category_id);
			
		} else {
			// default redirect
			$shopgateRedirectionUrl = $shopgateRedirector->getShopUrl();
		}
		
		// perform the redirect
		$shopgateRedirector->redirect($shopgateRedirectionUrl);
 	} elseif ($shopgateRedirector->isMobileRequest() && !$shopgateRedirector->isRedirectAllowed() && ($shopgateCurrentLanguage == $shopgateLanguage)) {
 		$shopgateMobileHeader = $shopgateRedirector->getMobileHeader();
 	}
}