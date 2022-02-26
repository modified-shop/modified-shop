<?php
/*
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
 * (c) 2010 - 2021 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

if (!defined('MAGNA_ECHO_UPDATE') && isset($_GET['MLDEBUG']) && ($_GET['MLDEBUG'] == 'true')) {
	define('MAGNA_ECHO_UPDATE', true);
	magnaPreparePlainTextMode();
}

function magnaProcessCallbackRequest() {
	$do = array();
	if (isset($_GET['do']) && !empty($_GET['do'])) {
		$do = explode(',', $_GET['do']);
	}
	if (empty($do)) return;

        /* check if maintenance mode */
        if (getDBConfigValue(array('general.maintenance', 'val'), 0, 'false') == 'true') {
           echo "The magnalister plugin in this shop is in maintenance mode. All synchronization processes are deactivated.\n";
           return;
        }

	/* Import orders */
	if (in_array('ImportOrders', $do)) {
		require_once(DIR_MAGNALISTER_CALLBACK.'orders_import.php');
		magnaImportAllOrders();
	}

	/* Update orders */
	if (in_array('UpdateOrders', $do)) {
		require_once(DIR_MAGNALISTER_CALLBACK.'orders_update.php');
		magnaUpdateAllOrders();
	}
	$fname = '';
	/* Sync inventory */
	if (in_array('SyncInventory', $do)) {
		$fname = 'autosync_inventory';
		require_once(DIR_MAGNALISTER_CALLBACK.'autosyncInventory.php');
		if (function_exists('ml_debug_out') && file_exists(DIR_MAGNALISTER_CALLBACK.$fname.'.log')) {
			rename(DIR_MAGNALISTER_CALLBACK.$fname.'.log', DIR_MAGNALISTER_CALLBACK.$fname.'_'.date('Ymd_His').'.log');
		}
		magnaAutosyncInventories();
	}
	/* Sync orders_status */
	if (in_array('SyncOrderStatus', $do)) {
		$fname = 'autosync_orderstatus';
		require_once(DIR_MAGNALISTER_CALLBACK.'autosyncOrderStatus.php');
		if (function_exists('ml_debug_out') && file_exists(DIR_MAGNALISTER_CALLBACK.$fname.'.log')) {
			rename(DIR_MAGNALISTER_CALLBACK.$fname.'.log', DIR_MAGNALISTER_CALLBACK.$fname.'_'.date('Ymd_His').'.log');
		}
		magnaAutosyncOrderStatus();
	}

	/* Sync ebay product properties */
	if (in_array('SyncEbayListingDetails', $do)) {
		$fname = 'magnaAutoEbaySyncListingDetails';
		require_once(DIR_MAGNALISTER_CALLBACK.'autosyncEbayListingDetails.php');
		if (function_exists('ml_debug_out') && file_exists(DIR_MAGNALISTER_CALLBACK.$fname.'.log')) {
			rename(DIR_MAGNALISTER_CALLBACK.$fname.'.log', DIR_MAGNALISTER_CALLBACK.$fname.'_'.date('Ymd_His').'.log');
		}
		magnaAutoEbaySyncListingDetails();
	}

    /* Build or refresh Variations table */
    if (in_array('updateVariationsTable', $do)) {
        require_once(DIR_MAGNALISTER_CALLBACK.'updateVariationsTable.php');
    }

	/* Upload Invoices to Amazon */
    if (in_array('UploadInvoices', $do)) {
        require_once(DIR_MAGNALISTER_CALLBACK.'uploadInvoices.php');
        magnaUploadInvoices();
    }

    /* Import categories from Otto */
    if (in_array('ImportCategories', $do)) {
        require_once(DIR_MAGNALISTER_CALLBACK.'importCategories.php');
        magnaImportCategories();
    }
}
