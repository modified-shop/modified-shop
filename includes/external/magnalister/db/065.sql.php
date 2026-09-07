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
$queries = array();
$functions = array();

function md_db_update_65_1() {
	/* The shrink of an existing `Subtitle` column is disabled on purpose.
	 *
	 * Migration 067 later changed `Subtitle` to TEXT on purpose (commit c948fcf1,
	 * "Fixed bug with item title and subtitle length restriction"), so shrinking it
	 * back to VARCHAR(200) undid a deliberate later change. Because a forced
	 * database update (?dbupdate=true) replays every migration, this ran again on
	 * every forced update and failed with MySQL error 1265. The marketplace limit is
	 * enforced when the data is written, see HitmeisterHelper::truncateSubtitle().
	 *
	 * MagnaDB::gi()->query("ALTER TABLE `".TABLE_MAGNA_HITMEISTER_PREPARE."` CHANGE COLUMN `Subtitle` `Subtitle` VARCHAR(200) DEFAULT NULL ");
	 *
	 * The rename of the misspelled `Subitle` column below is kept: it is the only
	 * thing this migration still has to do, and it widens the column instead of
	 * shrinking it.
	 */
	if (!MagnaDB::gi()->columnExistsInTable('Subtitle', TABLE_MAGNA_HITMEISTER_PREPARE)
		&& MagnaDB::gi()->columnExistsInTable('Subitle', TABLE_MAGNA_HITMEISTER_PREPARE)
	) {
		MagnaDB::gi()->query("ALTER TABLE `".TABLE_MAGNA_HITMEISTER_PREPARE."` CHANGE COLUMN `Subitle` `Subtitle` VARCHAR(200) DEFAULT NULL ");
	}
}
$functions[] = 'md_db_update_65_1';

function md_db_update_65_2() {
	/* Disabled on purpose.
	 *
	 * Migration 067 later widened `Title` to VARCHAR(255), so shrinking it back to
	 * VARCHAR(120) undid a deliberate later change and failed with MySQL error 1265
	 * on every forced database update. The marketplace limit is enforced when the
	 * data is written, see HitmeisterHelper::truncateTitle().
	 *
	 * MagnaDB::gi()->query("ALTER TABLE `".TABLE_MAGNA_HITMEISTER_PREPARE."` CHANGE COLUMN `Title` `Title` VARCHAR(120) DEFAULT NULL ");
	 */
}
$functions[] = 'md_db_update_65_2';
