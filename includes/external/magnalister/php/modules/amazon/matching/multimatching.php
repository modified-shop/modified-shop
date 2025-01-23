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

$itemsPerPage = getDBConfigValue('amazon.multimatching.itemsperpage', $_MagnaSession['mpID']);

initArrayIfNecessary($_MagnaSession, 'amazon|multimatching|items');
if (empty($_MagnaSession['amazon']['multimatching']['items']) || 
	(isset($_POST['timestamp']) && ($_MagnaSession['amazon']['multimatching']['timestamp'] != $_POST['timestamp']))
) {
	$_MagnaSession['amazon']['multimatching']['timestamp'] = $_POST['timestamp'];

	$allItems = MagnaDB::gi()->fetchArray('
	    SELECT pID FROM '.TABLE_MAGNA_SELECTION.'
	     WHERE mpID=\''.$_MagnaSession['mpID'].'\' AND
	           selectionname=\''.$matchingSetting['selectionName'].'\' AND
	           session_id=\''.session_id().'\'
	', true);

	$alreadyMatched = MagnaDB::gi()->fetchArray('
		SELECT products_id 
		  FROM `'.TABLE_MAGNA_AMAZON_PROPERTIES.'`
		 WHERE mpID=\''.$_MagnaSession['mpID'].'\'
		       AND asin<>\'\'
	', true);
	if ((isset($_POST['match']) && ($_POST['match'] == 'notmatched')) 
		|| (!isset($_POST['match']) && !getDBConfigValue(array('amazon.multimatching', 'rematch'), $_MagnaSession['mpID'], false))
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

	$_MagnaSession['amazon']['multimatching']['items'] = array_chunk($allItems, $itemsPerPage);
}

if (!empty($_MagnaSession['amazon']['multimatching']['items'])) {
	if (isset($_POST['matching_nextpage']) && 
	    ctype_digit($_POST['matching_nextpage']) && 
	    (count($_MagnaSession['amazon']['multimatching']['items']) > $_POST['matching_nextpage'])
	) {
		$currentPage = $_POST['matching_nextpage'];
	} else {
		$currentPage = 0;
	}

	$currentItems = $_MagnaSession['amazon']['multimatching']['items'][$currentPage];
	$_MagnaSession['amazon']['multimatching']['nextpage'] = (
			count($_MagnaSession['amazon']['multimatching']['items']) > ($currentPage + 1)
		) ? $currentPage + 1
		  : 'null';

	// echo print_m($currentItems, 'Zu verarbeitende Items');

	$products = array();
	
	$price = new SimplePrice();
	$price->setCurrency(getCurrencyFromMarketplace($_MagnaSession['mpID']));

	foreach ($currentItems as $current_product_id) {
		@set_time_limit(60);
		$productsData = MLProduct::gi()->getProductByIdOld($current_product_id);
		$asin = MagnaDB::gi()->fetchOne('
			SELECT `asin` FROM '.TABLE_MAGNA_AMAZON_PROPERTIES.'
			 WHERE mpID=\''.$_MagnaSession['mpID'].'\' AND
			       '.((getDBConfigValue('general.keytype', '0') == 'artNr')
						? 'products_model=\''.MagnaDB::gi()->escape($productsData['products_model']).'\''
						: 'products_id = '.$current_product_id
					).'
			 LIMIT 1
		');

		$result = performItemSearch(
			trim($asin),
			trim($productsData['products_ean']),
			trim($productsData['products_name'])
		);

		$price->setPrice($productsData['products_price'])->calculateCurr();
		$price->addTaxByTaxID($productsData['products_tax_class_id']);
		
		$productsData['products_description'] = stripEvilBlockTags($productsData['products_description']);
		$productsData['products_description'] = magnalisterIsUTF8($productsData['products_description']) ?
				$productsData['products_description'] : utf8_encode($productsData['products_description']);
		$productsData['products_model'] = magnalisterIsUTF8($productsData['products_model']) ?
				$productsData['products_model'] : utf8_encode($productsData['products_model']);

		if ($productsData['manufacturers_id'] > 0) {
			$manufacturerName = MagnaDB::gi()->fetchOne('
				SELECT manufacturers_name 
				  FROM '.TABLE_MANUFACTURERS.'
				 WHERE manufacturers_id=\''.$productsData['manufacturers_id'].'\'
			');
		} else {
			$manufacturerName = '';
		}
		
		$productsData['products_ean'] = isset($productsData['products_ean']) ? $productsData['products_ean'] : '';
		// If exists products_manufacturers_model it will be provided (modified 2.0) and products_manufacturers_sku (modified 1.0)
		if (    !array_key_exists('products_manufacturers_model', $productsData)
		     || (strlen($productsData['products_manufacturers_model']) == 0)) {
		    if (array_key_exists('products_manufacturers_sku', $productsData)) {
		        $productsData['products_manufacturers_model'] = $productsData['products_manufacturers_sku'];
		        unset($productsData['products_manufacturers_sku']);
		    } else {
		        $productsData['products_manufacturers_model'] = '';
		    }
		}
		$products[] = array (
			'product' => array (
	            'products_id' => $current_product_id,
	            'products_name' => $productsData['products_name'],
	            'products_details' => array (
	            	'desc' => $productsData['products_description'],
	            	'images' => $productsData['products_allimages'],
	            	'manufacturer' => $manufacturerName,
	            	'manufacturer_model' => $productsData['products_manufacturers_model'],
	            	'model' => $productsData['products_model'],
	            	'ean' => $productsData['products_ean'],
	            	'price' => $price->format(),
	            ),
	            'products_asin' => ($asin !== false) ? $asin : '',
	        ),
			'result'  => $result
		);
	}
	$error = '';
} else if (getDBConfigValue(array('amazon.multimatching', 'rematch'), $_MagnaSession['mpID'], false)) {
	$error = '<p>'.ML_AMAZON_TEXT_REMATCH.'</p>';
} else {
	$error = '<p>'.ML_ERROR_UNKNOWN.'</p>';
}

++$currentPage;
$totalPages = count($_MagnaSession['amazon']['multimatching']['items']);

echo '
<h2>Multi Matching'.(empty($error) ? ('<span class="small right successBox" style="margin-top: -4px; font-size: 12px !important;">
		'.ML_LABEL_STEP.' '.$currentPage.' von '.$totalPages.'
	</span>') : ''
).'</h2>';

if (!empty($products)) {
	echo '
<form name="matching" id="matching" action="'.toURL($_url, array('action' => 'multimatching')).'" method="POST" enctype="multipart/form-data">';
	renderMatchingTable($products, getCurrencyFromMarketplace($_MagnaSession['mpID']), true);
	echo '
	<input type="hidden" name="matching_nextpage" value="'.(($currentPage == $totalPages) ? 'null' : $currentPage).'" />
	<input type="hidden" name="action" value="multimatching" />

	<table class="actions">
		<thead><tr><th>Aktionen</th></tr></thead>
		<tbody><tr><td>
			<table><tbody><tr>
				<td class="first_child"><a href="'.toURL($_url).'" title="'.ML_BUTTON_LABEL_BACK.'" class="ml-button">'.ML_BUTTON_LABEL_BACK.'</a></td>
				<td class="last_child"><input type="submit" class="ml-button" value="'.
					(($currentPage == $totalPages) ? ML_BUTTON_LABEL_SAVE_DATA : ML_BUTTON_LABEL_SAVE_AND_NEXT).'" /></td>
			</tr></tbody></table>
		</td></tr></tbody>
	</table>
</form>';

} else {
	echo ML_AMAZON_TEXT_REMATCH;

}
