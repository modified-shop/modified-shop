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
 * $Id: identifyShop.php 6705 2016-05-13 10:05:57Z tim.neumann $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

function identShopSystem() {
	$content = file_get_contents('includes/application_top.php', 0, null, -1, 1500);
	if (    MagnaDB::gi()->tableExists('database_version')
	     && (MagnaDB::gi()->fetchOne("SELECT version FROM database_version WHERE `version` LIKE '%commerce:SEO v%'") !== false)) {
		define('SHOPSYSTEM', 'commerceseo');
	} else if (defined('_GM_VALID_CALL') || (stripos($content, 'gambio') !== false)) {
		define('SHOPSYSTEM', 'gambio');

        // try to detect the gambio cloud
        if (function_exists('gm_get_conf') && gm_get_conf('is_cloud', 'ASSOC', true) === 'true') {
            define('SHOPSYSTEM_GAMBIO_CLOUD', true);
        }
	} else if (defined('PROJECT_VERSION')
	            && (
	                   stripos(PROJECT_VERSION, 'modified') !== false
	                || stripos(PROJECT_VERSION, 'shophelfer') !== false
	                || stripos(PROJECT_VERSION, 'fishnet shop') !== false
	                || stripos($content, 'modified eCommerce Shopsoftware') !== false
	               )
	          ) {
		define('SHOPSYSTEM', 'xtcmodified');
	} else if (defined('PROJECT_VERSION') && (stripos(PROJECT_VERSION, 'xt:commerce') !== false)) {
		define('SHOPSYSTEM', 'xtcommerce');
	} else if (defined('PROJECT_VERSION') && (stripos(PROJECT_VERSION, 'deLuxe') !== false)) {
		define('SHOPSYSTEM', 'xonsoft');
	} else if (stripos($content, 'xt-commerce') !== false) {
		define('SHOPSYSTEM', 'xtcommerce');
	} else if (stripos($content, 'xt:Commerce') !== false) {
		define('SHOPSYSTEM', 'xtcommerce');
	} else if (stripos($content, 'oscommerce') !== false) {
		define('SHOPSYSTEM', 'oscommerce');
	} else {
		/* Shop unbekannt, aber mindestens ein osC fork */
		define('SHOPSYSTEM', 'oscommerce');
	}
}

identShopSystem();

/**
 * Try to get Gambio ShopSystem Version from Database
 * @return bool|string
 */
function mlGetGambioShopSystemVersion() {
	if (MagnaDB::gi()->tableExists('version_history')) {
		$sVersion = MagnaDB::gi()->fetchOne("
			SELECT version
			  FROM version_history
			 WHERE     type IN ('service_pack', 'master_update')
			".((MagnaDB::gi()->columnExistsInTable('installed', 'version_history'))
				? 'AND installed = 1'
				: 'AND (is_full_version = 0 OR (is_full_version = 1 AND history_id = 1))'
			)."
		  ORDER BY installation_date DESC
			 LIMIT 1
		");
	} else {
		return false;
	}

	return $sVersion;
}
