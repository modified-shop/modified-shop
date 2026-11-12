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
 * $Id$
 *
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

class MLShop {
	protected static $instance = null;
	
	private function __construct() {
		
	}
	
	public static function gi() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function getBaseUrl() {
		if (MAGNA_DEBUG && defined('ML_DEBUG_HOST')) {
			return ML_DEBUG_HOST;
		}
		
		/* Do not use HTTP_SERVER: inside the admin area that is the admin domain
		   from admin/includes/configure.php. With a separate admin URL (e.g.
		   admin.shop.de instead of www.shop.de) it does not match the licensed
		   shop URL, so the service layer rejects every request coming from the
		   admin area (IsAccessAllowed = no) while the cron via magnaCallback.php
		   keeps working. mlGetCatalogServerUrl() returns the shop domain and
		   degrades gracefully if HTTP_CATALOG_SERVER is empty or missing. */
		if (function_exists('mlGetCatalogServerUrl')) {
			$sServer = mlGetCatalogServerUrl();
		} else if (defined('HTTP_CATALOG_SERVER') && (HTTP_CATALOG_SERVER != '')) {
			$sServer = HTTP_CATALOG_SERVER;
		} else {
			$sServer = HTTP_SERVER;
		}
		
		/* A hand-edited configure.php may leave a trailing slash on the server
		   URL. DIR_WS_CATALOG always starts with one, and the service layer
		   compares the result against the licensed shop URL verbatim, so a
		   double slash would break the match. */
		$sServer = rtrim($sServer, '/');
		
		/* In some shops DIR_WS_CATALOG already is an absolute URL. */
		if (($sServer != '') && (strpos(DIR_WS_CATALOG, $sServer) === 0)) {
			return DIR_WS_CATALOG;
		}
		
		return $sServer.DIR_WS_CATALOG;
	}
}
