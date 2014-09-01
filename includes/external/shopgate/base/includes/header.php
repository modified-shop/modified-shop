<?php
$shopgateMobileHeader = '';// compatibility to older versions
$shopgateJsHeader = '';
if(defined('MODULE_PAYMENT_INSTALLED') && strpos(MODULE_PAYMENT_INSTALLED, 'shopgate.php') !== false){
	include_once DIR_FS_CATALOG.'includes/external/shopgate/shopgate_library/shopgate.php';
	include_once DIR_FS_CATALOG.'includes/external/shopgate/base/shopgate_config.php';

	try {
		$shopgateCurrentLanguage = isset($_SESSION['language_code']) ? strtolower($_SESSION['language_code']) : 'de';
		$shopgateHeaderConfig = new ShopgateConfigModified();
		$shopgateHeaderConfig->loadByLanguage($shopgateCurrentLanguage);
		
		if ($shopgateHeaderConfig->checkUseGlobalFor($shopgateCurrentLanguage)) {
			$shopgateRedirectThisLanguage = in_array($shopgateCurrentLanguage, $shopgateHeaderConfig->getRedirectLanguages());
		} else {
			$shopgateRedirectThisLanguage = true;
		}
		
		if ($shopgateRedirectThisLanguage) {
			// SEO modules fix (for Commerce:SEO and others): if session variable was set, SEO did a redirect and most likely cut off our GET parameter
			// => reconstruct here, then unset the session variable
			if (!empty($_SESSION['shopgate_redirect'])) {
				$_GET['shopgate_redirect'] = 1;
				unset($_SESSION['shopgate_redirect']);
			}

			// instantiate and set up redirect class
			$shopgateBuilder = new ShopgateBuilder($shopgateHeaderConfig);
			$shopgateRedirector = &$shopgateBuilder->buildRedirect();
	
			##################
			# redirect logic #
			##################
	
			if (($product instanceof product) && $product->isProduct && !empty($product->pID)) {
				$shopgateJsHeader = $shopgateRedirector->buildScriptItem($product->pID);
			} elseif (!empty($current_category_id)) {
				$shopgateJsHeader = $shopgateRedirector->buildScriptCategory($current_category_id);
			} else {
				$shopgateJsHeader = $shopgateRedirector->buildScriptShop();
			}
		}
	} catch (ShopgateLibraryException $e) {	}
}
