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
		
		return (strpos(DIR_WS_CATALOG, HTTP_SERVER) === 0)
			? DIR_WS_CATALOG
			: HTTP_SERVER.DIR_WS_CATALOG;
	}
}
