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
 * $Id: singlematching.php 4961 2014-12-09 14:10:12Z tim.neumann $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.'); 
require_once (DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');

$current_product_id = MagnaDB::gi()->fetchOne('
	SELECT pID FROM '.TABLE_MAGNA_SELECTION.'
	 WHERE mpID=\''.$_MagnaSession['mpID'].'\' AND
	       selectionname=\''.$matchingSetting['selectionName'].'\' AND
	       session_id=\''.session_id().'\'
	 LIMIT 1
');

$productsData = MLProduct::gi()->getProductByIdOld($current_product_id);
$amazonProperties = MagnaDB::gi()->fetchRow('
	SELECT * FROM '.TABLE_MAGNA_AMAZON_PROPERTIES.' 
	 WHERE mpID=\''.$_MagnaSession['mpID'].'\' AND
	       '.((getDBConfigValue('general.keytype', '0') == 'artNr')
				? 'products_model=\''.MagnaDB::gi()->escape($productsData['products_model']).'\''
				: 'products_id = '.$current_product_id
			).'
	 LIMIT 1
');

$sprice = new SimplePrice(
	$productsData['products_price'], 
	getCurrencyFromMarketplace($_MagnaSession['mpID'])
);
$tax = SimplePrice::getTaxByClassID($productsData['products_tax_class_id']);
$sprice->addTax($tax)->calculateCurr()->roundPrice();

if (!empty($amazonProperties) && !empty($amazonProperties['asin'])) {
	$productDetails = $amazonProperties;

} else {
	$productDetails['products_id'] = $productsData['products_price'];
	$productDetails['products_model'] = $productsData['products_model'];
	$productDetails['asin'] = '';
	$productDetails['asin_type'] = '';
	$productDetails['item_condition'] = getDBConfigValue('amazon.itemCondition', $_MagnaSession['mpID']);
	$productDetails['will_ship_internationally'] = getDBConfigValue('amazon.internationalShipping', $_MagnaSession['mpID']);
	$productDetails['item_note'] = '';
	
	if (defined('DEVELOPMENT_TEST')) {
		$productDetails['item_note'] = DEVELOPMENT_TEST;
	}

}

$searchResults = performItemSearch(
	trim($productDetails['asin']),
	trim($productsData[MAGNA_FIELD_PRODUCTS_EAN]),
	trim($productsData['products_name'])
);


$charLimit = 2000;

$productsData['products_description'] = stripEvilBlockTags($productsData['products_description']);
$productsData['products_description'] = magnalisterIsUTF8($productsData['products_description'])
	? $productsData['products_description']
	: utf8_encode($productsData['products_description']);
$productsData['products_model'] = magnalisterIsUTF8($productsData['products_model'])
	? $productsData['products_model']
	: utf8_encode($productsData['products_model']);

if ($productsData['manufacturers_id'] > 0) {
	$manufacturerName = MagnaDB::gi()->fetchOne(
		'SELECT manufacturers_name FROM '.TABLE_MANUFACTURERS.' WHERE manufacturers_id=\''.$productsData['manufacturers_id'].'\''
	);
} else {
	$manufacturerName = '';
}

$productsData[MAGNA_FIELD_PRODUCTS_EAN] = isset($productsData[MAGNA_FIELD_PRODUCTS_EAN]) ? $productsData[MAGNA_FIELD_PRODUCTS_EAN] : '';
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

$products = array(array(
	'product' => array(
		'products_id' => $current_product_id,
		'products_name' => $productsData['products_name'],
		'products_details' => array (
			'desc' => $productsData['products_description'],
			'images' => $productsData['products_allimages'],
			'manufacturer' => $manufacturerName,
            'manufacturer_model' => $productsData['products_manufacturers_model'],
			'model' => $productsData['products_model'],
			'ean' => $productsData[MAGNA_FIELD_PRODUCTS_EAN],
			'price' => $sprice->format(),
		),
		'products_description' => json_encode($productsData['products_description']),
		'products_asin' => $productDetails['asin']
	),
	'result' => $searchResults,
));
$matchingConfig = array (
	'itemConditions' => amazonGetPossibleOptions('ConditionTypes'),
	'internationalShipping' => amazonGetPossibleOptions('ShippingLocations'),
);

echo '
<h2>Single Matching</h2>
<form name="singleMatching" id="singleMatching" action="'.toURL($_url).'" method="POST" enctype="multipart/form-data">';
	renderMatchingTable($products, getCurrencyFromMarketplace($_MagnaSession['mpID']));
	echo '
<table class="amazon_properties">
	<thead><tr><th colspan="2">'.ML_GENERIC_PRODUCTDETAILS.'</th></tr></thead>	
	<tbody>
		<tr>
			<td class="label top">
				'.ML_GENERIC_CONDITION_NOTE.'<br/>
				<span class="normal">'.sprintf(ML_AMAZON_X_CHARS_LEFT, '<span id="charsLeft">0</span>').'</span>
			</td>
			<td class="options">
				<textarea class="fullwidth" rows="10" cols="100" wrap="soft" name="amazonProperties[item_note]" id="item_note">'.
					$productDetails['item_note'].
				'</textarea>
			</td>
		</tr>
		<tr class="odd">
			<td class="label">'.ML_GENERIC_CONDITION.'</td>
			<td class="options">
				<select name="amazonProperties[item_condition]" id="item_condition">';
					foreach ($matchingConfig['itemConditions'] as $type => $name) {
						if ($productDetails['item_condition'] == $type) {
							$sel = ' selected="selected"';
						} else{ 
							$sel = '';
						}
						echo'
						<option'.$sel.' value="'.$type.'">'.$name.'</option>';
					}
					echo '
				</select>
			</td>
		</tr>
		<tr class="last">
			<td class="label">'.ML_GENERIC_SHIPPING.'</td>
			<td class="options">
				<select name="amazonProperties[will_ship_internationally]" id="amazon_shipping">';
					foreach ($matchingConfig['internationalShipping'] as $type => $name) {
						if ($productDetails['will_ship_internationally'] == $type) {
							$sel = ' selected="selected"';
						} else{ 
							$sel = '';
						}
						echo'
						<option'.$sel.' value="'.$type.'">'.$name.'</option>';
					}
					echo '
				</select>
				&nbsp;&nbsp;&nbsp;
				'.ML_AMAZON_LABEL_LEADTIME_TO_SHIP.': 
				<select name="amazonProperties[leadtimeToShip]" id="amazon_leadtimeToShip">';
					$leadtimeToShipOpts = array_merge(array (
						'0' => '&mdash;',
					), range(1, 30));

					$usrValue = isset($productDetails['leadtimeToShip'])
						? $productDetails['leadtimeToShip']
						: getDBConfigValue('amazon.leadtimetoship', $_MagnaSession['mpID'], '0');
					
					foreach ($leadtimeToShipOpts as $vk => $vv) {
						echo '	<option value="'.$vk.'"'.(($vk == $usrValue) ? 'selected="selected"' : '').'>'.$vv.'</option>'."\n";
					}
					echo '
				</select>
			</td>
		</tr>
	</tbody>
</table>

<input type="hidden" name="amazonProperties[products_id]" value="'.$productsData['products_id'].'">
<input type="hidden" name="action" value="singlematching">

<table class="actions">
	<thead><tr><th>Aktionen</th></tr></thead>
	<tbody><tr><td>
		<table><tbody><tr>
			<td class="first_child"><a href="'.toURL(array('mp' => $_MagnaSession['mpID'], 'mode' => 'prepare', 'view' => 'match')).'" title="'.ML_BUTTON_LABEL_BACK.'" class="ml-button">
				'.ML_BUTTON_LABEL_BACK.
			'</a></td>
			<td class="last_child"><input type="submit" class="ml-button" value="'.ML_BUTTON_LABEL_SAVE_DATA.'" /></td>
		</tr></tbody></table>
	</td></tr></tbody>
</table>

</form>';
?>
<script type="text/javascript">/*<![CDATA[*/
var zeichenLimit = <?php echo $charLimit; ?>;
function checkCharLimit(tArea) {
	if (tArea.val().length > zeichenLimit) {
		tArea.val(tArea.val().substr(0, zeichenLimit));
	}
	$('#charsLeft').html(zeichenLimit - tArea.val().length);
}
$(document).ready(function() {
	$('#item_note').keydown(function(event) { 
		myConsole.log('event.which: '+event.which);
		if (($(this).val().length >= zeichenLimit) && 
			(event.which != 46) && // del
			(event.which != 8) && // backspace
			((event.which < 37) || (event.which > 40)) // arrow-keys*/
		) {
			myConsole.log('prevent');
			event.preventDefault();
		}
		return true;
	}).keyup(function(event) {
		checkCharLimit($(this)); 
		return true;
	});
	
	checkCharLimit($('#item_note'));
});
/*]]>*/</script>
