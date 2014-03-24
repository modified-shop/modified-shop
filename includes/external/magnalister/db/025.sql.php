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

$functions = array('addTopTen');
$queries = array();
function addTopTen(){
	$aAddFields = array(
		TABLE_MAGNA_AMAZON_APPLY => array(
			'topMainCategory' => 'VARCHAR( 64 ) not null',
			'topProductType'  => 'VARCHAR( 64 ) not null',
			'topBrowseNode1'  => 'VARCHAR( 64 ) not null',
			'topBrowseNode2'  => 'VARCHAR( 64 ) not null'
		),
		TABLE_MAGNA_EBAY_PROPERTIES	=> array(
			'topPrimaryCategory'   => 'VARCHAR( 64 ) not null',
			'topSecondaryCategory' => 'VARCHAR( 64 ) not null',
			'topStoreCategory1'    => 'VARCHAR( 64 ) not null',
			'topStoreCategory2'    => 'VARCHAR( 64 ) not null'
		)
	);
	foreach ($aAddFields as $sTable => $aField) {
		foreach ($aField as $sField => $sAlter) {
			if (!MagnaDB::gi()->columnExistsInTable($sField, $sTable)) {
				$sSql = "ALTER TABLE `".$sTable."` ADD `".$sField."` ".$sAlter;
				MagnaDB::gi()->query($sSql);
			}
		}
	}
}
