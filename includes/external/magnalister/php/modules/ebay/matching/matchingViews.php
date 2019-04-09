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
 * $Id: matchingViews.php 2332 2013-04-04 16:12:19Z derpapst $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

function renderMatchingResultTr($productID, $productName, $productEPID, $productDATA) {
	// Variations: replace dots in id, they break js functions
	if (strpos($productID, '.') !== false) {
		$productID = str_replace('.', '__', $productID);
	}
	$createNew = '
		<tr class="last noItem">
			<td class="input"><input type="radio" name="match['.$productID.']" id="match_'.$productID.'_'.'newproduct" value="newproduct" ';
	if ('newproduct' == $productEPID) $createNew .= 'checked="checked"';
	$createNew .= '/></td>
			<td colspan="4" class="title"><label for="match_'.$productID.'_'.'newproduct">'.ML_EBAY_LABEL_CREATE_PRODUCT.'</label></td>
		</tr>';
	$nomatch = '
		<tr class="last noItem">
			<td class="input"><input type="radio" name="match['.$productID.']" id="match_'.$productID.'_'.'false" value="false" '.'/></td>
			<td colspan="4" class="title italic">
                          <label for="match_'.$productID.'_'.'false">'.ucfirst(ML_GENERIC_DONT_MATCH).'
                          </label>&nbsp;
                          <div class="gfxbutton info" id="infobuttonNoMatch_'.$productID.'" name="infobuttonNoMatch" title="Infos">
                          </div>
                          <span id="infobuttonNoMatchContent_'.$productID.'" name="infobuttonNoMatchContent" style="display:none">'.ML_EBAY_NOTE_NOT_MATCHED_VARIATIONS.'
                          </span>
                          <div id="infobuttonNoMatchDialog_'.$productID.'" name="infobuttonNoMatchDialog" class="dialog2" title="'.ML_LABEL_INFORMATION.'">
                          </div>
                        </td>
                </tr>';

	if (!empty($productDATA)) {
		$isOdd = true;
		$isLast = false;
		$rows = count($productDATA);
		$isFirst = true;

		foreach ($productDATA as $item) {
			$class = array();
			if ($isOdd = !$isOdd) $class[] = 'odd';
			if (!(--$rows)) $class[] = 'last';
			$class = implode(' ', $class);

			if (    isset($item['GTIN']) 
			     && is_array($item['GTIN']) ) {
				if (count($item['GTIN']) == 1) $item['GTIN'] = reset($item['GTIN']);
				else $item['GTIN'] = implode(', ', $item['GTIN']);
			} else {
				$item['GTIN'] = ' ';
			}

			if (    isset($item['MPN']) 
			     && is_array($item['MPN']) ) {
				if (count($item['MPN']) == 1) $item['MPN'] = reset($item['MPN']);
				else $item['MPN'] = implode(', ', $item['MPN']);
			} else {
				$item['MPN'] = ' ';
			}

			echo '
			<tr class="'.$class.'">
				<td class="input">
					<input type="radio" name="match['.$productID.']" id="match_'.$productID.'_'.$item['EPID'].'" value="'.$item['EPID'].'" '.
						(((empty($productEPID) && $isFirst) || ($productEPID == $item['EPID'])) ? 
							'checked="checked"' : ''
						).'/>
				</td>
				<td class="title"><label for="match_'.$productID.'_'.$item['EPID'].'">'.fixHTMLUTF8Entities($item['Title']).'</label></td>
				<td class="title"><label for="match_'.$productID.'_'.$item['EPID'].'">'.fixHTMLUTF8Entities($item['MPN']).'</label></td>
				<td class="title"><label for="match_'.$productID.'_'.$item['EPID'].'">'.fixHTMLUTF8Entities($item['GTIN']).'</label></td>';
			echo '
				<td class="asin">
					<a href="'.$item['URL'].'" title="'.ML_EBAY_LABEL_PRODUCT_AT_EBAY.'<br />'.ML_EBAY_LABEL_LINK_OPENS_NEW_WINDOW.'"
					   target="_blank" onclick="(function(url){
					   		f = window.open(url, \''.ML_EBAY_LABEL_PRODUCT_AT_EBAY.'\', \'width=800,height=600,resizable=yes,scrollbars=yes\');
					   		f.focus();
					   	})(this.href); return false">'.$item['EPID'].'</a>
				</td>
			</tr>';
			$isFirst = false;
		}
	} else {
		echo '
		<tr class="searchFailed">
			<td class="input">&mdash;</td>
			<td class="title" colspan="3">'.ML_EBAY_SEARCH_NO_RESULT.' ('.fixHTMLUTF8Entities($productName).')</td>
			<td class="asin">&mdash;</td>
		</tr>';
	}
	echo $createNew;
	echo $nomatch;

}

function renderDetailView($product) {
	$w = 60;
	$h = 60;
	
	arrayEntitiesToUTF8($product);
	
	$html = '
		<table class="matchingDetailInfo"><tbody>';
	if (!empty($product['products_details']['manufacturer'])) {
		$html .= '
			<tr>
				<th class="smallwidth">'.ML_GENERIC_MANUFACTURER_NAME.':</th>
				<td>'.$product['products_details']['manufacturer'].'</td>
			</tr>';
	}
	if (!empty($product['products_details']['model'])) {
		$html .= '
			<tr>
				<th class="smallwidth">'.ML_GENERIC_MODEL_NUMBER.':</th>
				<td>'.$product['products_details']['model'].'</td>
			</tr>';
	}
	if (!empty($product['products_details']['ean']) || (SHOPSYSTEM != 'oscommerce')) {
		$html .= '
			<tr>
				<th class="smallwidth">'.ML_GENERIC_EAN.':</th>
				<td>'.(empty($product['products_details']['ean']) ? '&nbsp;' : $product['products_details']['ean']).'</td>
			</tr>';
	}
	if (!empty($product['products_details']['desc'])) {
		$html .= '
			<tr>
				<th colspan="2">'.ML_GENERIC_MY_PRODUCTDESCRIPTION.'</th>
			</tr>
			<tr class="desc">
				<td colspan="2"><div class="mlDesc">'.$product['products_details']['desc'].'</div></td>
			</tr>';
	}
	if (!empty($product['products_details']['images'])) {
		$html .= '
			<tr>
				<th colspan="2">'.ML_LABEL_PRODUCTS_IMAGES.'</th>
			</tr>
			<tr class="images">
				<td colspan="2"><div class="main">';
		foreach ($product['products_details']['images'] as $image) {
			$html .= '<table><tbody><tr><td style="width: '.$w.'px; height: '.$h.'px;">'.generateProductCategoryThumb($image, $w, $h).'</td></tr></tbody></table>';
		}
		$html .= '
				</div></td>
			</tr>';
	}
	$html .= '
		</tbody></table>
	';

	return json_encode(array(
		'title' => ML_LABEL_DETAILS_FOR.': '.$product['products_name'],
		'content' => $html,
	));
}

function renderMatchingTable($products, $currency, $multimatching = false) {
#echo print_m($products, __FUNCTION__.' '.__LINE__.' $products'); //DEBUG
	global $_MagnaSession;
	echo '
<div id="productDetailContainer" class="dialog2" title="'.ML_LABEL_DETAILS.'"></div>
<table class="matching">';
	foreach ($products as $product) {
		// Variations: replace dots in id, they break js functions
		if (strpos($product['product']['products_id'], '.') !== false) {
			$product['product']['products_id'] = str_replace('.', '__', $product['product']['products_id']);
		}
		$addHeadline = (!empty($product['product']['products_attributes'])
			? $product['product']['products_attributes'].' '
			: '');

		$addHeadline .= (!empty($product['product']['products_details']['model'])
			? '<span style="color: #ddd;">'.ML_LABEL_ARTICLE_NUMBER.'</span>: '.$product['product']['products_details']['model'].', '
			: '');
			
		$addHeadline .= (!empty($product['product']['products_details']['ean'])
			? '<span style="color: #ddd;">'.ML_EBAY_LABEL_GTIN.'</span>: '.$product['product']['products_details']['ean'].', '
			: '');
			
		$addHeadline .= (!empty($product['product']['products_details']['price'])
			? '<span style="color: #ddd;">'.ML_LABEL_SHOP_PRICE_BRUTTO.'</span>: '.$product['product']['products_details']['price'].', '
			: '');
		$addHeadline = rtrim($addHeadline, ', ');

		echo '
	<tbody class="product"><tr><th colspan="5">
		<div class="title"><span class="darker">'.ML_LABEL_SHOP_TITLE.':</span> '.$product['product']['products_name'].
		(!empty($addHeadline)
			? ('&nbsp;&nbsp;&nbsp;<span>['.$addHeadline.']</span>')
			: ''
		).'</div>
		<input type="hidden" name="match['.$product['product']['products_id'].']" value="false" />
		<input type="hidden" name="model['.$product['product']['products_id'].']" value="'.$product['product']['products_details']['model'].'" />
		'.(!empty($product['product']['products_details']) ?
			'<div id="productDetails_'.$product['product']['products_id'].'" class="productDescBtn" title="'.ML_LABEL_DETAILS.'">'.ML_LABEL_DETAILS.'</div>' : ''
		).'
	</th></tr></tbody>
	<tbody class="headline"><tr>
		<th class="input">'.ML_LABEL_CHOOSE.'</th>
		<th class="title">'.ML_EBAY_LABEL_TITLE.'</th>
		<th class="title">'.ML_EBAY_LABEL_MPN.'</th>
		<th class="title">'.ML_EBAY_LABEL_GTIN.'</th>
		<th class="asin">ePID</th>
	</tr></tbody>
	<tbody class="options" id="matchingResults_'.$product['product']['products_id'].'">';
		renderMatchingResultTr(
			$product['product']['products_id'], 
			$product['product']['products_name'],
			$product['product']['products_ePID'], 
			$product['result']
		);
		echo '
	</tbody>
	<tbody class="func"><tr><td colspan="5">
		<div>'.ML_EBAY_NEW_SEARCH_QUERY.': <input type="text" id="newSearch_'.$product['product']['products_id'].'"/> '.
			'<input type="button" value="OK" id="newSearchGo_'.$product['product']['products_id'].'"/></div>
		<div>'.ML_EBAY_ENTER_EPID_DIRECTLY.': <input type="text" id="newEPID_'.$product['product']['products_id'].'"/> '.
			'<input type="button" value="OK" id="newEPIDGo_'.$product['product']['products_id'].'"/></div>
	</td></tr></tbody>
	<tbody class="clear"><tr>
		<td colspan="4">&nbsp;</td>
	</tr></tbody>';
?>
	<script type="text/javascript">/*<![CDATA[*/
		var productDetailJson_<?php echo $product['product']['products_id']; ?> = <?php echo renderDetailView($product['product']); ?>;
		$('#productDetails_<?php echo $product['product']['products_id']; ?>').click(function() {
			myConsole.log(productDetailJson_<?php echo $product['product']['products_id']; ?>);
			$('#productDetailContainer').html(productDetailJson_<?php echo $product['product']['products_id']; ?>.content).jDialog({
				width: "75%",
				'title': productDetailJson_<?php echo $product['product']['products_id']; ?>.title
			});
		});
		$('#newSearchGo_<?php echo $product['product']['products_id']; ?>').click(function() {
			newSearch = $('#newSearch_<?php echo $product['product']['products_id']; ?>').val();
			if (jQuery.trim(newSearch) != '') {
				jQuery.blockUI({ message: blockUIMessage, css: blockUICSS });
				myConsole.log(newSearch);
				jQuery.ajax({
					type: 'POST',
					url: '<?php echo toURL(array('mp' => $_MagnaSession['mpID'], 'mode' => 'ajax'), true); ?>',
					data: ({request: 'ItemSearch', 'productID': '<?php echo $product['product']['products_id']; ?>', 'search':newSearch}),
					dataType: "html",
					success: function(data) {
						$('#matchingResults_<?php echo $product['product']['products_id']; ?>').html(data);
						if (function_exists("initRadioButtons")) {
							initRadioButtons();
						}
						jQuery.unblockUI();
					},
					error: function() {
						jQuery.unblockUI();
					}
				});
			}
		});
		$('#newSearch_<?php echo $product['product']['products_id']; ?>').keypress(function(event) {
			if (event.keyCode == '13') {
				event.preventDefault();
				$('#newSearchGo_<?php echo $product['product']['products_id']; ?>').click();
			}
		});
		$('#newEPIDGo_<?php echo $product['product']['products_id']; ?>').click(function() {
			newEPID = $('#newEPID_<?php echo $product['product']['products_id']; ?>').val();
			if (jQuery.trim(newEPID) != '') {
				jQuery.blockUI({ message: blockUIMessage, css: blockUICSS });
				myConsole.log(newEPID);
				jQuery.ajax({
					type: 'POST',
					url: '<?php echo toURL(array('mp' => $_MagnaSession['mpID'], 'mode' => 'ajax'), true); ?>',
					data: ({request: 'ItemLookup', 'productID': <?php echo $product['product']['products_id']; ?>, 'epid':newEPID}),
					dataType: "html",
					success: function(data) {
						$('#matchingResults_<?php echo $product['product']['products_id']; ?>').html(data);
						if (function_exists("initRadioButtons")) {
							initRadioButtons();
						}
						jQuery.unblockUI();
					},
					error: function() {
						jQuery.unblockUI();
					}
				});
			}
		});
		$('#newEPID_<?php echo $product['product']['products_id']; ?>').keypress(function(event) {
			if (event.keyCode == '13') {
				event.preventDefault();
				$('#newEPIDGo_<?php echo $product['product']['products_id']; ?>').click();
			}
		});
		$('div#infobuttonNoMatch_<?php echo $product['product']['products_id']; ?>').click(function() {
			$('#infobuttonNoMatchDialog_<?php echo $product['product']['products_id']; ?>').html($('span#infobuttonNoMatchContent_<?php echo $product['product']['products_id']; ?>').html()).jDialog();
		});
		//$('div#infobuttonNoMatch').click(function() {
		//	$('#infobuttonNoMatchDialog').html($('span#infobuttonNoMatchContent').html()).jDialog();
		//});
	/*]]>*/</script>
<?php
	}
	echo '
</table>';
}
