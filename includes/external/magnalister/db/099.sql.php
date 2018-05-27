<?php

$functions = array();

function md_db_update_AddVariationThemeToCdiscountPrepare_99() {
    if (!MagnaDB::gi()->columnExistsInTable('variation_theme', TABLE_MAGNA_CDISCOUNT_PREPARE)){
        MagnaDB::gi()->query('ALTER TABLE `' . TABLE_MAGNA_CDISCOUNT_PREPARE .
            '` ADD COLUMN `variation_theme` varchar(400) DEFAULT NULL AFTER `PreparedTS`');
    }
}

$functions[] = 'md_db_update_AddVariationThemeToCdiscountPrepare_99';