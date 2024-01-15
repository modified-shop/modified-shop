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
 *
 * (c) 2010 - 2020 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

function lengthen_variantmatching_shopvariation_122() {
	foreach(array (
		TABLE_MAGNA_AMAZON_VARIANTMATCHING,
		TABLE_MAGNA_CDISCOUNT_VARIANTMATCHING,
		TABLE_MAGNA_CROWDFOX_PREPARE,
		TABLE_MAGNA_CROWDFOX_VARIANTMATCHING,
		TABLE_MAGNA_DAWANDA_VARIANTMATCHING,
		TABLE_MAGNA_EBAY_VARIANTMATCHING,
		TABLE_MAGNA_ETSY_PREPARE,
		TABLE_MAGNA_ETSY_VARIANTMATCHING,
		TABLE_MAGNA_GOOGLESHOPPING_PREPARE,
		TABLE_MAGNA_GOOGLESHOPPING_VARIANTMATCHING,
		TABLE_MAGNA_HITMEISTER_VARIANTMATCHING,
		TABLE_MAGNA_METRO_VARIANTMATCHING,
		TABLE_MAGNA_OTTO_VARIANTMATCHING,
		TABLE_MAGNA_PRICEMINISTER_VARIANTMATCHING,
		TABLE_MAGNA_TRADORIA_VARIANTMATCHING
		) as $variantMatchingTable) {
		// set ShopVariation to mediumtext
		MagnaDB::gi()->query("ALTER TABLE `".$variantMatchingTable."` CHANGE COLUMN `ShopVariation` `ShopVariation` mediumtext NOT NULL");
	}
	return;
}

$functions[] = 'lengthen_variantmatching_shopvariation_122';
