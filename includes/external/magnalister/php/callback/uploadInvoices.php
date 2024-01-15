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
 * $Id: autosyncInventory.php 5357 2015-03-13 14:16:33Z Soprex $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('ML_LOG_INVENTORY_CHANGE') or define('ML_LOG_INVENTORY_CHANGE', true);

require_once(DIR_MAGNALISTER_CALLBACK.'callbackFunctions.php');

function magnaUploadInvoices() {
	global $magnaConfig;


	$verbose = isset($_GET['MLDEBUG']) && ($_GET['MLDEBUG'] == 'true');

	if ($verbose) {
		echo '#######################################'."\n##\n".
			'## Begin of protocol: Uploading invoices, Shop > Marketplace'
		;
	 	echo "\n##\n".'#######################################'."\n";
		$_timer = microtime(true);
	} else{
	    echo '<pre>';
    }
	
	MagnaDB::gi()->logQueryTimes(false);
	MagnaConnector::gi()->setTimeOutInSeconds(600);

	$modules = magnaGetInvolvedMarketplaces();
	foreach ($modules as $marketplace) {
		$mpIDs = magnaGetInvolvedMPIDs($marketplace);
        foreach ($mpIDs as $mpID) {
            @set_time_limit(60 * 10); // 10 minutes per module
            $cronPath = DIR_MAGNALISTER_MODULES.strtolower($marketplace).'/crons/';
            $className = ucfirst($marketplace).'UploadInvoices';
            $classFile = $cronPath.$className.'.php';
            if (file_exists($classFile)) {
                require_once($classFile);
                if (!class_exists($className)) {
                    if ($verbose) {
                        echo 'Class '.$className.' not found.'."\n";
                    }
                    continue;
                }
            } else {
                if ($verbose) {
                    echo 'No upload invoice functions are available for '.$marketplace.' ('.$mpID.').'."\n";
                }
                continue;
            }

            if (!array_key_exists('db', $magnaConfig) ||
                !array_key_exists($mpID, $magnaConfig['db'])
            ) {
                loadDBConfig($mpID);
            }
            #echo print_m("MP: $marketplace  MPID: $mpID");

            if ($className !== false) {
                if (function_exists('ml_debug_out'))
                    ml_debug_out("\n\n\n#####\n## Uploading invoice of $marketplace ($mpID) with class $className\n##\n");

                $ic = new $className($mpID, $marketplace);
                $ic->process();
            }
        }
		#echo print_m($mpIDs, $marketplace);
	}
	
	MagnaConnector::gi()->resetTimeOut();
	MagnaDB::gi()->logQueryTimes(true);
	
	if ($verbose) {
		echo "\n\nComplete (".microtime2human(microtime(true) - $_timer).").\n";
		die();
	} else{
        echo '</pre>';
    }
}
