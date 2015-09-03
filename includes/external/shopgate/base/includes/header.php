<?php
/**
 * Shopgate GmbH
 *
 * URHEBERRECHTSHINWEIS
 *
 * Dieses Plugin ist urheberrechtlich geschützt. Es darf ausschließlich von Kunden der Shopgate GmbH
 * zum Zwecke der eigenen Kommunikation zwischen dem IT-System des Kunden mit dem IT-System der
 * Shopgate GmbH über www.shopgate.com verwendet werden. Eine darüber hinausgehende Vervielfältigung, Verbreitung,
 * öffentliche Zugänglichmachung, Bearbeitung oder Weitergabe an Dritte ist nur mit unserer vorherigen
 * schriftlichen Zustimmung zulässig. Die Regelungen der §§ 69 d Abs. 2, 3 und 69 e UrhG bleiben hiervon unberührt.
 *
 * COPYRIGHT NOTICE
 *
 * This plugin is the subject of copyright protection. It is only for the use of Shopgate GmbH customers,
 * for the purpose of facilitating communication between the IT system of the customer and the IT system
 * of Shopgate GmbH via www.shopgate.com. Any reproduction, dissemination, public propagation, processing or
 * transfer to third parties is only permitted where we previously consented thereto in writing. The provisions
 * of paragraph 69 d, sub-paragraphs 2, 3 and paragraph 69, sub-paragraph e of the German Copyright Act shall remain unaffected.
 *
 * @author Shopgate GmbH <interfaces@shopgate.com>
 */
$shopgateMobileHeader = '';// compatibility to older versions
$shopgateJsHeader     = '';
if (MODULE_PAYMENT_SHOPGATE_STATUS == 'True') {
	
	include_once DIR_FS_CATALOG
		. 'includes/external/shopgate/shopgate_library/shopgate.php';
	include_once DIR_FS_CATALOG
		. 'includes/external/shopgate/base/shopgate_config.php';
	
	
	try {
		$shopgateCurrentLanguage = isset($_SESSION['language_code'])
			? strtolower($_SESSION['language_code']) : 'de';
		$shopgateHeaderConfig    = new ShopgateConfigModified();
		$shopgateHeaderConfig->loadByLanguage($shopgateCurrentLanguage);
		
		if ($shopgateHeaderConfig->checkUseGlobalFor(
			$shopgateCurrentLanguage
		)
		) {
			$shopgateRedirectThisLanguage = in_array(
				$shopgateCurrentLanguage,
				$shopgateHeaderConfig->getRedirectLanguages()
			);
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
			$shopgateRedirector = $shopgateBuilder->buildRedirect();
			
			##################
			# redirect logic #
			##################
			
			if (($product instanceof product) && $product->isProduct
				&& !empty($product->pID)
			) {
				$shopgateJsHeader = $shopgateRedirector->buildScriptItem(
					$product->pID
				);
			} elseif (!empty($current_category_id)) {
				$shopgateJsHeader = $shopgateRedirector->buildScriptCategory(
					$current_category_id
				);
			} elseif (sgIsHomepage()) {
				$shopgateJsHeader = $shopgateRedirector->buildScriptShop();
			} else {
				$shopgateJsHeader = $shopgateRedirector->buildScriptDefault();
			}
		}
	} catch (ShopgateLibraryException $e) {
	}
}

function sgIsHomepage()
{
	$scriptName = explode('/', $_SERVER['SCRIPT_NAME']);
	$scriptName = end($scriptName);
	
	if ($scriptName != 'index.php') {
		return false;
	}
	
	return true;
}
