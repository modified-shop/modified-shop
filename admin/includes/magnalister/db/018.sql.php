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
 * (c) 2012 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

function extend_ebay_properties_table_18() {
	if (!MagnaDB::gi()->columnExistsInTable('Transferred', TABLE_MAGNA_EBAY_PROPERTIES)) {
		MagnaDB::gi()->query('
			ALTER TABLE `'.TABLE_MAGNA_EBAY_PROPERTIES.'` 
			 ADD COLUMN `Transferred` tinyint(1) unsigned NOT NULL DEFAULT \'0\' 
			      AFTER Verified
		');
	}
}
$functions[] = 'extend_ebay_properties_table_18';

function updateAmazonApplyTable_18() {
	if (MagnaDB::gi()->columnExistsInTable('category', TABLE_MAGNA_AMAZON_APPLY)) {
		return;
	}
	MagnaDB::gi()->query('
		ALTER TABLE `'.TABLE_MAGNA_AMAZON_APPLY.'` 
		 ADD COLUMN `category` TEXT NOT NULL
		      AFTER is_incomplete
	');
	/* Convert the data */
	$meh = MagnaDB::gi()->fetchArray('
		SELECT mpID, products_id, products_model, data
		  FROM '.TABLE_MAGNA_AMAZON_APPLY.'
		 WHERE category=\'\'
	');
	foreach ($meh as $r) {
		$r['data'] = @unserialize(@base64_decode($r['data']));
		if (empty($r['data']) || !is_array($r['data'])) {
			unset($r['data']);
			MagnaDB::gi()->delete(TABLE_MAGNA_AMAZON_APPLY, $r);
			continue;
		}
		$s = array (
			'MainCategory' => $r['data']['MainCategory'],
            'ProductType' => $r['data']['ProductType'],
            'BrowseNodes' => $r['data']['BrowseNodes']
		);
		unset($r['data']['MainCategory']);
		unset($r['data']['ProductType']);
		unset($r['data']['BrowseNodes']);
		
		$u = array (
			'data' => base64_encode(serialize($r['data'])),
			'category' => base64_encode(serialize($s))
		);
		unset($r['data']);
		
		MagnaDB::gi()->update(TABLE_MAGNA_AMAZON_APPLY, $u, $r);
	}
}
$functions[] = 'updateAmazonApplyTable_18';