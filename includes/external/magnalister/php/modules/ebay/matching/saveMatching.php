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
 * $Id: saveMatching.php 5727 2015-06-09 13:06:53Z tim.neumann $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

#echo print_m($_GET, __LINE__.' $_GET');
#echo print_m($_POST, __LINE__.' $_POST');

/*

multi:

23 $_GET :: Array
(
    [mp] => 396
    [mode] => prepare
    [view] => match
    [action] => multimatching
)

24 $_POST :: Array
(
    [match] => Array
        (
            [ML9_2__2] => 99986961
            [ML9_2__3] => newproduct
            [16] => 168458658
        )

    [model] => Array
        (
            [ML9_2__2] => GT-S5230LKAXEBp9_521
            [ML9_2__3] => GT-S5230LKAXEBp9_520
            [16] => p16
        )

    [matching_nextpage] => null
    [action] => multimatching
)
-----------------------------
single / newproduct:

23 $_GET :: Array
(
    [mp] => 396
    [mode] => prepare
    [view] => match
)

24 $_POST :: Array
(
    [match] => Array
        (
            [16] => newproduct
        )

    [model] => Array
        (
            [16] => p16
        )

    [ebayProperties] => Array
        (
            [products_id] => 16
        )

    [action] => singlematching
)

----------------------------
single / newproduct / variations:
23 $_GET :: Array
(
    [mp] => 396
    [mode] => prepare
    [view] => match
)

24 $_POST :: Array
(
    [match] => Array
        (
            [ML9_2__2] => newproduct
            [ML9_2__3] => 169666037
        )

    [model] => Array
        (
            [ML9_2__2] => GT-S5230LKAXEBp9_521
            [ML9_2__3] => GT-S5230LKAXEBp9_520
        )

    [ebayProperties] => Array
        (
            [products_id] => 9
        )

    [action] => singlematching
)
------------------------------
No matching:
24 $_POST :: Array
(
    [match] => Array
        (
            [25] => false
        )
    ...
)

*/

// set to false if we don't match anything
$blDoMatch = true;

/**
 * Single Matching
 */
if (array_key_exists('ebayProperties', $_POST)) {
    $data = $_POST['ebayProperties'];
    $data['mpID'] = $_MagnaSession['mpID'];
    $pID = $data['products_id'];

    $ePID = (isset($data['products_id']) && isset($_POST['match'][$data['products_id']]))
        ? ($_POST['match'][$data['products_id']])
        : '';

    if ($ePID == '') { // Variations
	$aVariationIds = array_keys($_POST['match']);
	$pID = ltrim(substr($aVariationIds[0], 0, strpos($aVariationIds[0], '_')), 'ML');
	$products_model = MagnaDB::gi()->fetchOne('SELECT products_model FROM '.TABLE_PRODUCTS.' WHERE products_id = '.$pID);
	$aVariationEpids = array();
	#$aNewProducts = array();
	foreach ($_POST['model'] as $vID => $vSKU) {
		#if ($_POST['match'][$vID] != 'newproduct') {
			$aVariationEpids[] = array (
				'mpID' => $_MagnaSession['mpID'],
				'products_id' => $pID,
				'products_sku' => $products_model,
				'marketplace_id' => str_replace('__', '.', $vID),
				'marketplace_sku' => $vSKU,
				'ePID' => $_POST['match'][$vID]
			);
		// omit double data
		#$myTable = ($_POST['match'][$vID] == 'newproduct')? 'magnalister_ebay_new_products' : 'magnalister_ebay_variations_epids';
		$myTable = 'magnalister_ebay_variations_epids';
		if (getDBConfigValue('general.keytype', '0') == 'artNr') {
			if (MagnaDB::gi()->recordExists($myTable, array('mpID' => $_MagnaSession['mpID'], 'products_sku' => $products_model, 'marketplace_sku' => $vSKU))) {
				MagnaDB::gi()->delete($myTable, array('mpID' => $_MagnaSession['mpID'], 'products_sku' => $products_model, 'marketplace_sku' => $vSKU));
			}
		} else {
			if (MagnaDB::gi()->recordExists($myTable, array('mpID' => $_MagnaSession['mpID'], 'products_id' => $pID, 'marketplace_id' => $vID))) {
				MagnaDB::gi()->delete($myTable, array('mpID' => $_MagnaSession['mpID'], 'products_id' => $pID, 'marketplace_id' => $vID));
			}
		}
	}
	if (!empty($aVariationEpids)) {
		MagnaDB::gi()->batchinsert('magnalister_ebay_variations_epids', $aVariationEpids, true);
	}
	/*if (!empty($aNewProducts)) {
		MagnaDB::gi()->batchinsert('magnalister_ebay_new_products', $aNewProducts, true);
	}*/
	$data = array (
		'mpID' => $_MagnaSession['mpID'],
		'products_id' => $pID,
		'products_model' => $products_model,
		'ePID' => 'variations'
	);
        #echo print_m($data, __LINE__.'Data');
    } else if ($ePID != 'false') {
        $data['ePID'] = $ePID;
    } else {
	// don't match and don't prepare
        $data['ePID'] = '';
	MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array (
		'mpID' => $_MagnaSession['mpID'],
		'selectionname' => 'matching',
		'pID' => $pID
	));
	$blDoMatch = false;
    }

    $_MagnaSession['ebayLastPreparedTS'] = array_key_exists('ebayLastPreparedTS', $_MagnaSession) ? $_MagnaSession['ebayLastPreparedTS'] : date('Y-m-d H:i:s');
    $data['PreparedTS'] = $_MagnaSession['ebayLastPreparedTS'];
    if(!isset($data['products_model'])) {
        if(isset($_POST['model'][$data['products_id']])) {
	    $data['products_model'] = $_POST['model'][$data['products_id']];
        } else {
            $data['products_model'] = MagnaDB::gi()->fetchOne('SELECT products_model FROM '.TABLE_PRODUCTS.' WHERE products_id = '.$data['products_id']);
        }
    }
    $where = (getDBConfigValue('general.keytype', '0') == 'artNr')
        ? array(
            'products_model' => $data['products_model']
        )
        : array(
            'products_id' => $data['products_id']
        );
    $where['mpID'] = $_MagnaSession['mpID'];

    if ($blDoMatch) {
        if (MagnaDB::gi()->recordExists(TABLE_MAGNA_EBAY_PROPERTIES, $where)) {
            $blIsEpidError = (boolean)MagnaDB::gi()->fetchOne('
               SELECT COUNT(*)
                 FROM '.TABLE_MAGNA_EBAY_PROPERTIES.'
                WHERE     mpID = '.$where['mpID'].'
                      AND '.((getDBConfigValue('general.keytype', '0') == 'artNr')
                        ? 'products_model = \''.$data['products_model'].'\''
                        : 'products_id = '.$data['products_id']).' 
                      AND Verified = \'ERROR\'
                      AND ErrorCode = \'21920000\'
                LIMIT 1
            ');
            // if the error was bc of lacking ePID, and ePID is now there, set Verified = OK
            // (actually not necessary, we do the same in Preparation)
            if ($blIsEpidError && !empty($data['ePID'])) {
                $data['Verified'] = 'OK';
            }
            MagnaDB::gi()->update(TABLE_MAGNA_EBAY_PROPERTIES, $data, $where);
        } else {
            #echo print_m($data, __LINE__.'Data');
            #echo "Datensatz existiert nicht, wird neu angelegt.\n";
            MagnaDB::gi()->insert(TABLE_MAGNA_EBAY_PROPERTIES, $data);
        }
    }
}

/**
 * Multi Matching
 */
if (array_key_exists('action', $_GET) && ($_GET['action'] == 'multimatching') && array_key_exists('match', $_POST)) {
    $items = $_POST['match'];
    foreach ($items as $productID => $ePID) {
        if ($ePID != 'false') {
            if (is_numeric($productID)) {
                $pID             = $productID;
                $products_sku    = $_POST['model'][$pID]; 
                $marketplace_id  = '';
                $marketplace_sku = '';
            } else { //variations
                $pID             = ltrim(substr($productID, 0, strpos($productID, '_')), 'ML');
                $products_sku    = MagnaDB::gi()->fetchOne('SELECT products_model
                     FROM '.TABLE_PRODUCTS.'
                    WHERE products_id = '.$pID);
                $marketplace_id  = str_replace('__', '.', $productID);
                $marketplace_sku = $_POST['model'][$productID];
            }
            if ($ePID != 'false') {
# matching
                if ($marketplace_id != '') {
                    MagnaDB::gi()->insert('magnalister_ebay_variations_epids', array(
                        'mpID'            => $_MagnaSession['mpID'],
                        'products_id'     => $pID,
                        'products_sku'    => $products_sku,
                        'marketplace_id'  => $marketplace_id,
                        'marketplace_sku' => $marketplace_sku,
                        'ePID'            => $_POST['match'][str_replace('.', '__',$marketplace_id)]
                    ));
                    $ePID = 'variations';
                }
                $data = array(
                    'mpID' => $_MagnaSession['mpID'],
                    'ePID' => $ePID,
                    'products_id' => $pID,
                    'products_model' => $products_sku,
                    'ConditionID' => getDBConfigValue('ebay.condition', $_MagnaSession['mpID'], 1000)
                );
            } else {
                // don't match and don't prepare
                $data['ePID'] = '';
	        MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array (
		        'mpID' => $_MagnaSession['mpID'],
		        'selectionname' => 'matching',
		        'pID' => $pID
	        ));
                continue; 
	        //$blDoMatch = false;
            }

            $_MagnaSession['ebayLastPreparedTS'] = array_key_exists('ebayLastPreparedTS', $_MagnaSession) ? $_MagnaSession['ebayLastPreparedTS'] : date('Y-m-d H:i:s');
            $data['PreparedTS'] = $_MagnaSession['ebayLastPreparedTS'];
            $where = (getDBConfigValue('general.keytype', '0') == 'artNr')
                ? array(
                    'products_model' => $data['products_model']
                )
                : array(
                    'products_id' => $data['products_id']
                );
            $where['mpID'] = $_MagnaSession['mpID'];
    
            if (MagnaDB::gi()->recordExists(TABLE_MAGNA_EBAY_PROPERTIES, $where)) {
                MagnaDB::gi()->update(TABLE_MAGNA_EBAY_PROPERTIES, $data, $where);
            } else {
                #echo print_m($data, __LINE__.'Data');
                #echo "Datensatz existiert nicht, wird neu angelegt.\n";
                MagnaDB::gi()->insert(TABLE_MAGNA_EBAY_PROPERTIES, $data);
            }
        }
    }
}
