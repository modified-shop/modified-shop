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
require_once DIR_MAGNALISTER_INCLUDES.'lib/classes/ProductList/Dependency/MLProductListDependencyMarketplaceSync.php';

class MLProductListDependencyEbayMarketplaceSync extends MLProductListDependencyMarketplaceSync {
	
	
	protected $filterValues = array (
		'' => ML_OPTION_FILTER_ARTICLES_ALL,
		'notactive' => ML_OPTION_FILTER_ARTICLES_NOTACTIVE,
		'active' => ML_OPTION_FILTER_ARTICLES_ACTIVE,
		'nottransferred' => ML_OPTION_FILTER_ARTICLES_NOTTRANSFERRED_1YEAR,
		'sync' => ML_OPTION_FILTER_ARTICLES_DELETEDBY_SYNC,
//		'button' => ML_OPTION_FILTER_ARTICLES_DELETEDBY_BUTTON,
		'expired' => ML_OPTION_FILTER_ARTICLES_DELETEDBY_EXPIRED,
	);
}
