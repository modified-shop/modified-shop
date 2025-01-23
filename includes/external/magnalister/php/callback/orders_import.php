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
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_CALLBACK.'callbackFunctions.php');

function magnaInitOrderImport() {
    global $magnaInitOrderImportCalled, $_magnaLanguage;
    
    /* Prevent this funciton to be called twice. */
    if ($magnaInitOrderImportCalled === true) {
        return;
    }
    $magnaInitOrderImportCalled = true;
    define('MAGNA_ORDERS_DATERANGE_BEGIN', time() - 60 * 60 * 24 * 30);

    /* Wegen include aus magnaCallback.php */
    if (!defined('DIR_MAGNA_LANGUAGES')) {
        if (defined('DIR_FS_LANGUAGES')) {
            define('DIR_MAGNA_LANGUAGES', DIR_FS_LANGUAGES);
        } else {
            if (strpos(DIR_WS_LANGUAGES, DIR_FS_DOCUMENT_ROOT) === false) {
                define('DIR_MAGNA_LANGUAGES', DIR_FS_DOCUMENT_ROOT.DIR_WS_LANGUAGES);
            } else {
                define('DIR_MAGNA_LANGUAGES', DIR_WS_LANGUAGES);
            }
        }
    }
    if (!defined('DIR_MAGNA_MODULES')) {
        if (defined('DIR_FS_CATALOG_MODULES')) {
            define('DIR_MAGNA_MODULES', DIR_FS_CATALOG_MODULES);
        } else {
            if (strpos(DIR_WS_MODULES, DIR_FS_DOCUMENT_ROOT) === false) {
                define('DIR_MAGNA_MODULES', DIR_FS_DOCUMENT_ROOT.DIR_WS_MODULES);
            } else {
                define('DIR_MAGNA_MODULES', DIR_WS_MODULES);
            }
        }
    }

    /* Die Shop-Scripte werfen einige Notices aus. Ausgabe von Notices unterdruecken falls Sie aktiv sind. */
    $errorlevel = error_reporting(0);
    error_reporting($errorlevel & ~E_NOTICE);

    /* Labels fuer OrdersTotal */
    
    if (MagnaDB::gi()->tableExists('language_section_phrases')) {
        // Fix for Gambio 2.1.1.0. The language files have all been deleted there. The translations are defined in the database.
        $_gm_lang_defines = MagnaDB::gi()->fetchArray("
                SELECT lsp.phrase_name AS `define`, lsp.phrase_value AS `value`
                  FROM `language_sections` ls
            INNER JOIN language_section_phrases lsp ON ls.language_section_id = lsp.language_section_id
                 WHERE section_name LIKE '%".$_magnaLanguage . "/modules/order_total/%'
                       AND lsp.phrase_name <> ''
              GROUP BY lsp.phrase_name
        ");
        if (!empty($_gm_lang_defines)) {
            foreach ($_gm_lang_defines as $_gm_set) {
                if (!defined($_gm_set['define'])) {
                    define($_gm_set['define'], $_gm_set['value']);
                }
            }
        }
    } elseif (MagnaDB::gi()->tableExists('language_phrases_cache')) {
        // Fix for Gambio 2.3.1.0.
        $_gm_lang_defines = MagnaDB::gi()->fetchArray("
                SELECT lsc.phrase_name AS `define`, lsc.phrase_text AS `value`
                  FROM `language_phrases_cache` lsc
                 WHERE lsc.section_name LIKE 'ot\_%'
                       AND lsc.language_id = " . (int)$_SESSION['languages_id'] . "
              GROUP BY lsc.phrase_name
        ");
        if (!empty($_gm_lang_defines)) {
            foreach ($_gm_lang_defines as $_gm_set) {
                if (!defined($_gm_set['define'])) {
                    define($_gm_set['define'], $_gm_set['value']);
                }
            }
        }
    }
    
    if (!defined('MODULE_ORDER_TOTAL_SHIPPING_TITLE')) {
        mlLoadModuleLanguageDefines(DIR_MAGNA_LANGUAGES.$_magnaLanguage.'/modules/order_total/ot_shipping.php');
    }
    if (!defined('MODULE_ORDER_TOTAL_SUBTOTAL_TITLE')) {
        mlLoadModuleLanguageDefines(DIR_MAGNA_LANGUAGES.$_magnaLanguage.'/modules/order_total/ot_subtotal.php');
    }
    if (!defined('MODULE_ORDER_TOTAL_TOTAL_TITLE')) {
        mlLoadModuleLanguageDefines(DIR_MAGNA_LANGUAGES.$_magnaLanguage.'/modules/order_total/ot_total.php');
    }
    if (!defined('MODULE_ORDER_TOTAL_TOTAL_NETTO_TITLE')) {
        # nur bei Gambio
        mlLoadModuleLanguageDefines(DIR_MAGNA_LANGUAGES.$_magnaLanguage.'/modules/order_total/ot_total_netto.php');
    }
    if (!defined('MODULE_ORDER_TOTAL_TAX_TITLE')) {
        mlLoadModuleLanguageDefines(DIR_MAGNA_LANGUAGES.$_magnaLanguage.'/modules/order_total/ot_tax.php');
    }
    // Gambio specific "Kleinunternehmer Regelung"
    if(!defined('MAGNA_GAMBIO_PLUGIN_GM_TAX_FREE_STATUS')) {
        if (defined('MODULE_ORDER_TOTAL_GM_TAX_FREE_STATUS')
            && (strtolower(MODULE_ORDER_TOTAL_GM_TAX_FREE_STATUS) == 'true') 
        ) {
            if (!defined('MODULE_ORDER_TOTAL_GM_TAX_FREE_TEXT')) {
                mlLoadModuleLanguageDefines(DIR_MAGNA_LANGUAGES.$_magnaLanguage.'/modules/order_total/ot_gm_tax_free.php');
            }
            if (defined('MODULE_ORDER_TOTAL_GM_TAX_FREE_TEXT')) {
                define('MAGNA_GAMBIO_PLUGIN_GM_TAX_FREE_STATUS', true);
            } else {
                define('MAGNA_GAMBIO_PLUGIN_GM_TAX_FREE_STATUS', false);
            }
        } else {
            define('MAGNA_GAMBIO_PLUGIN_GM_TAX_FREE_STATUS', false);
        }
    }
    
    if(!defined('MAGNA_LABEL_ORDERS_COD_CHARGE')) {
        if (defined('MODULE_ORDER_TOTAL_COD_FEE_TITLE')) {
            define('MAGNA_LABEL_ORDERS_COD_CHARGE', MODULE_ORDER_TOTAL_COD_FEE_TITLE.':');
        } else {
            mlLoadModuleLanguageDefines(DIR_MAGNA_LANGUAGES.$_magnaLanguage.'/modules/order_total/ot_cod_fee.php');
            if (defined('MODULE_ORDER_TOTAL_COD_FEE_TITLE')) {
                define('MAGNA_LABEL_ORDERS_COD_CHARGE', MODULE_ORDER_TOTAL_COD_FEE_TITLE.':');
            } else {
                define('MAGNA_LABEL_ORDERS_COD_CHARGE', ML_LABEL_ORDER_TOTAL_COD_FEE.':');
            }
        }
    }
    if (!defined('MAGNA_LABEL_ORDERS_VOUCHER')) {
        if (defined('MODULE_ORDER_TOTAL_COUPON_TITLE')) {
            define('MAGNA_LABEL_ORDERS_VOUCHER', MODULE_ORDER_TOTAL_COUPON_TITLE.':');
        } else {
            mlLoadModuleLanguageDefines(DIR_MAGNA_LANGUAGES.$_magnaLanguage.'/modules/order_total/ot_coupon.php');
            if (defined('MODULE_ORDER_TOTAL_COUPON_TITLE')) {
                define('MAGNA_LABEL_ORDERS_VOUCHER', MODULE_ORDER_TOTAL_COUPON_TITLE.':');
            } else {
                define('MAGNA_LABEL_ORDERS_VOUCHER', ML_LABEL_ORDER_TOTAL_COUPON.':');
            }
        }
    }
    if (!defined('MAGNA_LABEL_ORDERS_DISCOUNT')) {
        if (defined('MODULE_ORDER_TOTAL_DISCOUNT_TITLE')) {
            define('MAGNA_LABEL_ORDERS_DISCOUNT', MODULE_ORDER_TOTAL_DISCOUNT_TITLE.':');
        } else {
            mlLoadModuleLanguageDefines(DIR_MAGNA_LANGUAGES.$_magnaLanguage.'/modules/order_total/ot_discount.php');
            if (defined('MODULE_ORDER_TOTAL_DISCOUNT_TITLE')) {
                define('MAGNA_LABEL_ORDERS_DISCOUNT', MODULE_ORDER_TOTAL_DISCOUNT_TITLE.':');
            } else {
                define('MAGNA_LABEL_ORDERS_DISCOUNT', ML_LABEL_ORDER_TOTAL_DISCOUNT.':');
            }
        }
    }

    defined('MAGNA_LABEL_ORDERS_SUBTOTAL') or define('MAGNA_LABEL_ORDERS_SUBTOTAL', MODULE_ORDER_TOTAL_SUBTOTAL_TITLE.':');
    defined('MAGNA_LABEL_ORDERS_TAX') or define('MAGNA_LABEL_ORDERS_TAX', MODULE_ORDER_TOTAL_TAX_TITLE.':');
    defined('MAGNA_LABEL_ORDERS_SHIPPING') or define('MAGNA_LABEL_ORDERS_SHIPPING', MODULE_ORDER_TOTAL_SHIPPING_TITLE.':');
    defined('MAGNA_LABEL_ORDERS_TOTAL') or define('MAGNA_LABEL_ORDERS_TOTAL', MODULE_ORDER_TOTAL_TOTAL_TITLE.':');
    defined('MAGNA_LABEL_ORDERS_COUNTRY_CHARGE') or define('MAGNA_LABEL_ORDERS_COUNTRY_CHARGE', ML_LABEL_ORDER_TOTAL_COUNTRY_CHARGE.':');
    error_reporting($errorlevel);
}

function magnaImportAllOrders() {
    global $_MagnaShopSession, $magnaConfig;

    magnaInitOrderImport();

    $verbose = isset($_GET['MLDEBUG']) && ($_GET['MLDEBUG'] == 'true');

    /* Bitte nicht vor Lachen in Traenen ausbrechen, aber die naechsten Zeilen sollen so etwas wie ein "mutex" darstellen :-) */
    $doImports = false;
    usleep(rand(0, $verbose ? 2000 : 2000000)); // sleep for 0 to max 2 seconds
    $lockName = DIR_MAGNALISTER_FS.'OrderImportLock';
    
    $aMutex = array(
        'time' => time(),
    );
    $sRandom = '';
    if (function_exists('random_bytes') && function_exists('bin2hex')) {
        try {
            $sRandom = bin2hex(random_bytes(32));
        } catch (Exception $oEx) {
        }
    } 
    if (empty($sRandom) && function_exists('openssl_random_pseudo_bytes') && function_exists('bin2hex')) {
        $sRandom = bin2hex(openssl_random_pseudo_bytes(32));
    }
    if (empty($sRandom)) {
        $sRandom = uniqid('', true);
    }
    $aMutex['unique'] = $sRandom;
    if (!file_exists($lockName)) {
        file_put_contents($lockName, json_encode($aMutex));
        chmod($lockName, 0666);
        $doImports = true;
    } else {
        $aGetted = json_decode(@file_get_contents($lockName), true);
        $time = (int)$aGetted['time'];
        if (($time + 1200) < time()) {
            file_put_contents($lockName, json_encode($aMutex));
            chmod($lockName, 0666);
            $doImports = true;
        }
    }
    usleep(rand(20000, 80000));
    $aGetted = json_decode(@file_get_contents($lockName), true);
    if ($aMutex['unique'] !== $aGetted['unique']) {
        $doImports = false;
    }
    
    /* {Hook} "PreOrderImport": Runs before the order import starts. */
    if (($hp = magnaContribVerify('PreOrderImport', 1)) !== false) {
        require($hp);
    }    

    if ($verbose) {
        echo var_dump_pre($doImports, '$doImports');
    }

    if(!$doImports && MAGNA_CALLBACK_MODE == 'STANDALONE') {
        echo "OrderImportLock set, other order import is already running. Not starting.\n";
    }

    if ($doImports) {
        $modules = magnaGetInvolvedMarketplaces();

        #ini_set('memory_limit', '512M');
        MagnaConnector::gi()->setTimeOutInSeconds(600);

        $break = false;
        foreach ($modules as $marketplace) {
            if ($break) break;
            $mpIDs = magnaGetInvolvedMPIDs($marketplace);
            if (empty($mpIDs)) continue;
            if ($verbose) echo print_m($mpIDs, $marketplace);
            foreach ($mpIDs as $mpID) {
                if ($break) break;
                $funcName = false;
                $className = false;
                
                $cronPath = DIR_MAGNALISTER_MODULES.strtolower($marketplace).'/crons/';
                $funcFile = $cronPath.'get_'.$marketplace.'_orders.php';
                $classFile = $cronPath.ucfirst($marketplace).'ImportOrders.php';
                
                /*
                if (file_exists($funcFile)) {
                    require_once($funcFile);
                    $funcName = 'magnaImport'.ucfirst($marketplace).'Orders';
                    
                    if (!function_exists($funcName)) {
                        continue;
                    }
                } else
                //*/
                if (file_exists($classFile)) {
                    require_once($classFile);
                    $className = ucfirst($marketplace).'ImportOrders';
                    if (!class_exists($className)) {
                        if ($verbose) echo 'Class '.$className.' does not exist in '.$classFile."\n";
                        continue;
                    }
                } else if (file_exists($funcFile)) {
                    require_once($funcFile);
                    $funcName = 'magnaImport'.ucfirst($marketplace).'Orders';
                    
                    if (!function_exists($funcName)) {
                        if ($verbose) echo 'File '.$funcName.' does not exist in '.$funcFile."\n";
                        continue;
                    }
                } else {
                    if ($verbose) echo 'Neither '.$classFile.' nor '.$funcFile." do exist.\n";
                    continue;
                }

                $aGetted = json_decode(@file_get_contents($lockName), true);
                if ($aMutex['unique'] !== $aGetted['unique']) {
                    # Sollte ein anderer Prozess gestartet sein, hoere hier auf
                    # und vermerke dass nach doppelten Bestellungen geschaut werden soll
                    setDBConfigValue('deletedoubleorders', 0, 'true', true);
                    if ($verbose || (MAGNA_CALLBACK_MODE == 'STANDALONE')) echo "Parallel ImportOrders detected.\n";
                    $break = true;
                    break;
                }
                if (!array_key_exists('db', $magnaConfig) || 
                    !array_key_exists($mpID, $magnaConfig['db'])
                ) {
                    loadDBConfig($mpID);
                }
                if (getDBConfigValue($marketplace.'.import', $mpID, 'false') != 'true') {
                    if ($verbose || (MAGNA_CALLBACK_MODE == 'STANDALONE')) echo $marketplace.' ('.$mpID.'): Import disabled.'."\n";
                    continue;
                }

                if ($className !== false) {
                    if ($verbose) echo print_m('Import :: new '.$className.'('.$mpID.', \''.$marketplace.'\')'."\n");
                    $ic = new $className($mpID, $marketplace);
                    $ic->process();
                } else {
                    if ($verbose) echo print_m('Import :: '.$funcName.'['.$mpID.']'."\n");
                    $funcName($mpID);
                }
            }
            #echo print_m($mpIDs, $marketplace);
            MagnaConnector::gi()->resetTimeOut();
        }
        @unlink($lockName);
    }
    magnaFixOrders();

    if (    (defined('GM_SET_OUT_OF_STOCK_PRODUCTS') && (GM_SET_OUT_OF_STOCK_PRODUCTS == 'true')) 
         || (defined('STOCK_CHECKOUT_UPDATE_PRODUCTS_STATUS') && (STOCK_CHECKOUT_UPDATE_PRODUCTS_STATUS == 'true'))
         || (defined('STOCK_ALLOW_CHECKOUT_DEACTIVATE') && (STOCK_ALLOW_CHECKOUT_DEACTIVATE == 'true'))
         || (    !defined('GM_SET_OUT_OF_STOCK_PRODUCTS')
              && !defined('STOCK_CHECKOUT_UPDATE_PRODUCTS_STATUS')
              && !defined('STOCK_ALLOW_CHECKOUT_DEACTIVATE')
              && defined('STOCK_ALLOW_CHECKOUT')
              && (STOCK_ALLOW_CHECKOUT == false)
            )
    ) {
        /* Set sold out products to inactive - for MPs which don't use MagnaCompatibleImportOrders */
        MagnaDB::gi()->query('UPDATE '.TABLE_PRODUCTS.' SET products_status=0 WHERE products_quantity <= 0');
    }
    
    /* {Hook} "PostOrderImport": Runs after the order import ends. */
    if (($hp = magnaContribVerify('PostOrderImport', 1)) !== false) {
        require($hp);
    }
    if ($verbose) {
        die();
    }
}
