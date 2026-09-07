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
 * (c) 2010 - 2026 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

# idealo: add item condition (conditionType/condition) and further standard-feed columns
# (freeReturnDays and the EEC energy-label fields) to product preparation

$queries = array();
$functions = array();

function idealo_add_item_condition_fields_160() {
	$oDB = MagnaDB::gi();
	if (!$oDB->tableExists(TABLE_MAGNA_IDEALO_PROPERTIES)) {
		return;
	}
	$aColumns = array(
		// idealo "conditionType": NEW, AS_NEW, REFURBISHED, USED
		'ItemConditionType'  => 'varchar(32) default ""',
		// idealo "condition": EXCELLENT, VERY_GOOD, GOOD, ACCEPTABLE
		'ItemCondition'      => 'varchar(32) default ""',
		// idealo "freeReturnDays": free returns within N days (pre-filled from configuration)
		'FreeReturnDays'     => 'varchar(16) default ""',
		// idealo "eec_spectrum": energy efficiency spectrum, e.g. "A-G" (pre-filled from configuration)
		'EecSpectrum'        => 'varchar(32) default ""',
		// idealo "eec_efficiencyClass": energy efficiency class, e.g. "A" (per-item)
		'EecEfficiencyClass' => 'varchar(16) default ""',
		// idealo "eec_labelUrl": link to the energy label (per-item)
		'EecLabelUrl'        => 'varchar(255) default ""',
		// idealo "eec_dataSheetUrl": link to the product data sheet (per-item)
		'EecDataSheetUrl'    => 'varchar(255) default ""',
		// idealo "eec_version": EEC label version, e.g. "0" (per-item)
		'EecVersion'         => 'varchar(16) default ""',
	);
	foreach ($aColumns as $sColumn => $sDefinition) {
		if (!$oDB->columnExistsInTable($sColumn, TABLE_MAGNA_IDEALO_PROPERTIES)) {
			$oDB->query('ALTER TABLE `'.TABLE_MAGNA_IDEALO_PROPERTIES.'` ADD COLUMN `'.$sColumn.'` '.$sDefinition);
		}
	}
}

$functions[] = 'idealo_add_item_condition_fields_160';
