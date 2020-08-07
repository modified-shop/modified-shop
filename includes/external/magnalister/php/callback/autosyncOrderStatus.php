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
 * $Id: autosyncInventory.php 1216 2011-08-30 12:42:51Z derpapst $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the GNU General Public License v2 or later
 * -----------------------------------------------------------------------------
 */

require_once(DIR_MAGNALISTER_CALLBACK.'callbackFunctions.php');

function magnaAutosyncOrderStatus() {
	global $_MagnaShopSession, $magnaConfig;
	
	$modules = magnaGetInvolvedMarketplaces();
	magnaResetEbayOrderStatus20191206();
	$skippedMPs = array();
	$skippedMPIDs = array();

	$verbose = isset($_GET['MLDEBUG']) && ($_GET['MLDEBUG'] == 'true');

	if ($verbose) {
		echo '#######################################'."\n##\n".
			 '## Begin of protocoll: SyncOrderStatus Shop > Marketplace';
		if (isset($_GET['continue'])) {
			echo (defined('ML_LABEL_SYNC_CONTINUE_MODE')
				? ' ('.ML_LABEL_SYNC_CONTINUE_MODE.')'
				: ' (in continue mode)'
			);
		}
	 	echo "\n##\n".'#######################################'."\n";
		$_timer = microtime(true);
	}

	MagnaConnector::gi()->setTimeOutInSeconds(600);
	foreach ($modules as $marketplace) {
		$mpIDs = magnaGetInvolvedMPIDs($marketplace);
		if (empty($mpIDs)) {
			if (function_exists('ml_debug_out')) ml_debug_out('Skip[2] ('.$marketplace.' not booked)'."\n");
			$skippedMPs[] = $marketplace;
			continue;
		}
		foreach ($mpIDs as $mpID) {
			$funcName = false;
			$className = false;
			
			$funcFile = DIR_MAGNALISTER_MODULES.$marketplace.'/'.$marketplace.'Functions.php';
			$classFile = DIR_MAGNALISTER_MODULES.strtolower($marketplace).'/crons/'.ucfirst($marketplace).'SyncOrderStatus.php';

			if (file_exists($classFile)) {
				require_once($classFile);
				$className = ucfirst($marketplace).'SyncOrderStatus';
				if (!class_exists($className)) {
					if ($verbose) echo $className.' not found.'."\n";
					$skippedMPIDs[] = $mpID;
					continue;
				}
			} else if (file_exists($funcFile)) {
				require_once($funcFile);
				$funcName = 'autoupdate'.ucfirst($marketplace).'OrdersStatus';
				
				if (!function_exists($funcName)) {
					if ($verbose) echo $funcName.' not found.'."\n";
					$skippedMPIDs[] = $mpID;
					continue;
				}
			} else {
				if ($verbose) echo 'No sync functions available for '.$marketplace.'('.$mpID.').'."\n";
				$skippedMPIDs[] = $mpID;
				continue;
			}

			if (!array_key_exists('db', $magnaConfig) || 
			    !array_key_exists($mpID, $magnaConfig['db'])
			) {
				loadDBConfig($mpID);
			}
			
			if ($className !== false) {
				if (function_exists('ml_debug_out')) ml_debug_out("\n\n\n#####\n## Sync $marketplace ($mpID) with class $className\n##\n");
				$ic = new $className($mpID, $marketplace);
				$ic->process();
			} else {
				if (function_exists('ml_debug_out')) ml_debug_out("\n\n\n#####\n## Sync $marketplace ($mpID) with function $funcName\n##\n");
				$funcName($mpID);
			}
			@set_time_limit(60 * 10); // 10 minutes per module

		}
	}
	$queries = array();
	
	/* Update orders_status for marketplaces that have been skipped. */
	if (!empty($skippedMPs)) {
		$queries[] = "
			UPDATE `".TABLE_MAGNA_ORDERS."` mo, `".TABLE_ORDERS."` o 
			   SET mo.orders_status=o.orders_status
		     WHERE mo.orders_id=o.orders_id
		           AND mo.platform IN ('".implode("', '", $skippedMPs)."')
		";
	}
	if (!empty($skippedMPIDs)) {
		$queries[] = "
			UPDATE `".TABLE_MAGNA_ORDERS."` mo, `".TABLE_ORDERS."` o 
			   SET mo.orders_status=o.orders_status
		     WHERE mo.orders_id=o.orders_id
		           AND mo.mpID IN ('".implode("', '", $skippedMPIDs)."')
		";
	}
	if (!empty($queries)) {
		if (defined('MAGNA_ECHO_UPDATE') && MAGNA_ECHO_UPDATE) {
			ml_debug_out(implode("\n", $queries));
		} else {
			foreach ($queries as $q) {
				MagnaDB::gi()->query($q);
			}
		}
	}
	MagnaConnector::gi()->resetTimeOut();
	
	if ($verbose) {
		echo "\n\nComplete.";
		die();
	}
}
