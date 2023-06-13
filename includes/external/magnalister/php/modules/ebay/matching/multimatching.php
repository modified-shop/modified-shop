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
 * $Id: multimatching.php 4961 2014-12-09 14:10:12Z tim.neumann $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once (DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');

$itemsPerPage = getDBConfigValue('ebay.multimatching.itemsperpage', $_MagnaSession['mpID'], '10');

initArrayIfNecessary($_MagnaSession, 'ebay|multimatching|items');
if (empty($_MagnaSession['ebay']['multimatching']['items']) || 
	(isset($_POST['timestamp']) && ($_MagnaSession['ebay']['multimatching']['timestamp'] != $_POST['timestamp']))
) {
	$_MagnaSession['ebay']['multimatching']['timestamp'] = $_POST['timestamp'];

	$allItems = MagnaDB::gi()->fetchArray('
	    SELECT pID FROM '.TABLE_MAGNA_SELECTION.'
	     WHERE mpID=\''.$_MagnaSession['mpID'].'\' AND
	           selectionname=\''.$matchingSetting['selectionName'].'\' AND
	           session_id=\''.session_id().'\'
	', true);

	$alreadyMatched = MagnaDB::gi()->fetchArray('
		SELECT products_id 
		  FROM `'.TABLE_MAGNA_EBAY_PROPERTIES.'`
		 WHERE mpID=\''.$_MagnaSession['mpID'].'\'
		       AND ePID<>\'\'
	', true);
	if ((isset($_POST['match']) && ($_POST['match'] == 'notmatched')) 
		|| (!isset($_POST['match']) && !getDBConfigValue(array('ebay.multimatching', 'rematch'), $_MagnaSession['mpID'], false))
	) {
		$allItems = array_diff($allItems, $alreadyMatched);
		MagnaDB::gi()->query('
			DELETE FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID=\''.$_MagnaSession['mpID'].'\' AND
			       selectionname=\''.$matchingSetting['selectionName'].'\' AND
			       session_id=\''.session_id().'\' AND
			       pID IN (\''.implode('\', \'', $alreadyMatched).'\')
		');
	}

	$_MagnaSession['ebay']['multimatching']['items'] = array_chunk($allItems, $itemsPerPage);
}

if (!empty($_MagnaSession['ebay']['multimatching']['items'])) {
	if (isset($_POST['matching_nextpage']) && 
	    ctype_digit($_POST['matching_nextpage']) && 
	    (count($_MagnaSession['ebay']['multimatching']['items']) > $_POST['matching_nextpage'])
	) {
		$currentPage = $_POST['matching_nextpage'];
	} else {
		$currentPage = 0;
	}

	$currentItems = $_MagnaSession['ebay']['multimatching']['items'][$currentPage];
	$_MagnaSession['ebay']['multimatching']['nextpage'] = (
			count($_MagnaSession['ebay']['multimatching']['items']) > ($currentPage + 1)
		) ? $currentPage + 1
		  : 'null';

	#echo print_m($currentItems, __LINE__.' Zu verarbeitende Items');

	$products = array();
	
	$price = new SimplePrice();
	$price->setCurrency(getCurrencyFromMarketplace($_MagnaSession['mpID']));

	MLProduct::gi()->setLanguage(getDBConfigValue('ebay.lang', $_MagnaSession['mpID']));
	foreach ($currentItems as $current_product_id) {
		#$productsData = MLProduct::gi()->getProductByIdOld($current_product_id);
		$productsData = MLProduct::gi()->getProductById($current_product_id);
		$ePID = MagnaDB::gi()->fetchOne('
			SELECT `ePID` FROM '.TABLE_MAGNA_EBAY_PROPERTIES.'
			 WHERE mpID=\''.$_MagnaSession['mpID'].'\' AND
			       '.((getDBConfigValue('general.keytype', '0') == 'artNr')
						? 'products_model=\''.MagnaDB::gi()->escape($productsData['ProductsModel']).'\''
						: 'products_id = '.$current_product_id
					).'
			 LIMIT 1
		');

		$result = ebayPerformItemSearch(
			trim($ePID),
			// don't use the main EAN if you have Variations
			($ePID != 'variations') ? trim($productsData['EAN']) : '',
			'',
			trim($productsData['Title']),
			''
		);

		$price->setPrice($productsData['Price'])->calculateCurr();
		$price->addTaxByTaxID($productsData['TaxClass']);
		
		$productsData['Description'] = stripEvilBlockTags($productsData['Description']);
		$productsData['Description'] = magnalisterIsUTF8($productsData['Description']) ?
				$productsData['Description'] : utf8_encode($productsData['Description']);
		$productsData['ProductsModel'] = magnalisterIsUTF8($productsData['ProductsModel']) ?
				$productsData['ProductsModel'] : utf8_encode($productsData['ProductsModel']);
		$manufacturerName = $productsData['Manufacturer'];

	    if (isset($productsData['Variations']) && !empty($productsData['Variations'])) {
	        if(empty($products)) $products = array();
	        $blKeytypeIsArtnr = (getDBConfigValue('general.keytype', '0') == 'artNr');
	        $ePIDsForVariationsByKey = getEpidsForVariationsByKey($current_product_id, $productsData['ProductsModel']);
	        if ($ePIDsForVariationsByKey != false) {
	    	foreach ($productsData['Variations'] as &$v) {
	    	    if ($blKeytypeIsArtnr) $v['ePID'] = $ePIDsForVariationsByKey[$v['MarketplaceSku']];
	    	    else $v['ePID'] = $ePIDsForVariationsByKey[$v['MarketplaceId']];
	    	}
	        }
	        foreach ($productsData['Variations'] as $var) {
	    	$attrs = ' (';
	    	foreach ($var['Variation'] as $attr) {
	    	    $attrs .= $attr['Name'].': '.$attr['Value'].', ';
	    	}
	    	$attrs = rtrim($attrs, ', ').')';
	    	$products[] = array (
	    	    'product' => array(
	    		'products_id' => $var['MarketplaceId'],
	    		'products_name' => $productsData['Title'],
	    		'products_attributes' => $attrs,
	    		'products_details' => array (
	    			'desc' => $productsData['Description'],
	    			'images' => '', //$productsData['products_allimages'],
	    			'manufacturer' => $manufacturerName,
	    			'model' => $var['MarketplaceSku'],
	    			'ean' => $var['EAN'],
	    			'price' => $price->format(),
	    		),
	    		'products_description' => json_encode($productsData['Description']),
			'products_ePID' => ($var['ePID'] !== false) ? $var['ePID'] : '',
	    	    ),
	    	    'result' => $result,
	    	);
	    	$attrs = '';
	    	}
	    } else {
		
		$products[] = array (
			'product' => array (
	            'products_id' => $current_product_id,
	            'products_name' => $productsData['Title'],
	            'products_details' => array (
	            	'desc' => $productsData['Description'],
	            	'images' => '', //$productsData['products_allimages'],
	            	'manufacturer' => $manufacturerName,
	            	'model' => $productsData['ProductsModel'],
	            	'ean' => $productsData['EAN'],
	            	'price' => $price->format(),
	            ),
	            'products_description' => json_encode($productsData['Description']),
	            'products_ePID' => ($ePID !== false) ? $ePID : '',
	        ),
			'result'  => $result
		);
	    }
	}
	$error = '';
} else if (getDBConfigValue(array('ebay.multimatching', 'rematch'), $_MagnaSession['mpID'], false)) {
	$error = '<p>'.ML_EBAY_TEXT_REMATCH.'</p>';
} else {
	$error = '<p>'.ML_ERROR_UNKNOWN.'</p>';
}

++$currentPage;
$totalPages = count($_MagnaSession['ebay']['multimatching']['items']);

echo '
<h2>Multi Matching'.(empty($error) ? ('<span class="small right successBox" style="margin-top: -4px; font-size: 12px !important;">
		'.ML_LABEL_STEP.' '.$currentPage.' von '.$totalPages.'
	</span>') : ''
).'</h2>';

if (!empty($products)) {
	echo '
<form name="matching" id="matching" action="'.toURL($_url, array('action' => 'multimatching')).'" method="POST" enctype="multipart/form-data" >';
	renderMatchingTable($products, getCurrencyFromMarketplace($_MagnaSession['mpID']), true);
	echo '
	<input type="hidden" name="matching_nextpage" value="'.(($currentPage == $totalPages) ? 'null' : $currentPage).'" />
	<input type="hidden" name="action" value="multimatching" />

	<table class="actions">
		<thead><tr><th>'.ML_LABEL_ACTIONS.'</th></tr></thead>
		<tbody><tr><td>
			<table><tbody><tr>
				<td class="first_child"><a href="'.toURL($_url).'" title="'.ML_BUTTON_LABEL_BACK.'" class="ml-button">'.ML_BUTTON_LABEL_BACK.'</a></td>
				<td class="last_child"><input type="submit" class="ml-button" value="'.
					(($currentPage == $totalPages) ? ML_EBAY_BUTTON_MATCH_PREPARE_PRODUCTS : ML_BUTTON_LABEL_SAVE_AND_NEXT).'" /></td>
			</tr></tbody></table>
		</td></tr></tbody>
	</table>
</form>';

} else {
	echo ML_EBAY_TEXT_REMATCH;

}
