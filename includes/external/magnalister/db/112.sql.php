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
 * $Id: 001.sql.php 650 2011-01-08 22:30:52Z MaW $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

# Adding new fields to Idealo product prepare

$queries = array();
$functions = array();

function idealo_add_new_fields() {
	if (! MagnaDB::gi()->columnExistsInTable('DeliveryTimeSource', TABLE_MAGNA_IDEALO_PROPERTIES)) {
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_IDEALO_PROPERTIES.'` ADD COLUMN `DeliveryTimeSource` varchar(127)  default ""');
	}

	if (! MagnaDB::gi()->columnExistsInTable('DeliveryTime', TABLE_MAGNA_IDEALO_PROPERTIES)) {
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_IDEALO_PROPERTIES.'` ADD COLUMN `DeliveryTime` varchar(31)  default ""');
	}

	if (! MagnaDB::gi()->columnExistsInTable('FulFillmentType', TABLE_MAGNA_IDEALO_PROPERTIES)) {
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_IDEALO_PROPERTIES.'` ADD COLUMN `FulFillmentType` varchar(31)  default ""');
	}

	if (! MagnaDB::gi()->columnExistsInTable('TwoManHandlingFee', TABLE_MAGNA_IDEALO_PROPERTIES)) {
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_IDEALO_PROPERTIES.'` ADD COLUMN `TwoManHandlingFee` varchar(16)  default "0.00"');
	}

	if (! MagnaDB::gi()->columnExistsInTable('DisposalFee', TABLE_MAGNA_IDEALO_PROPERTIES)) {
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_IDEALO_PROPERTIES.'` ADD COLUMN `DisposalFee` varchar(16)  default "0.00"');
	}
}

$functions[] = 'idealo_add_new_fields';
