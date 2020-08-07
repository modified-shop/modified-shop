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

function renderMathingResultTr($productID, $productName, $productASIN, $productDATA) {
	$nomatch = '
		<tr class="last noItem">
			<td class="input"><input type="radio" name="match['.$productID.']" id="match_'.$productID.'_'.'false" value="false" '.'/></td>
			<td class="title italic"><label for="match_'.$productID.'_'.'false">nicht matchen</label></td>
			<td class="productGroup">&nbsp;</td>
			<td class="lowestprice">&nbsp;</td>
			<td class="asin">&nbsp;</td>
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

			echo '
			<tr class="'.$class.'">
				<td class="input">
					<input type="radio" name="match['.$productID.']" id="match_'.$productID.'_'.$item['ASIN'].'" value="'.$item['ASIN'].'" '.
						(((empty($productASIN) && $isFirst) || ($productASIN == $item['ASIN'])) ? 
							'checked="checked"' : ''
						).'/>
				</td>
				<td class="title"><label for="match_'.$productID.'_'.$item['ASIN'].'">'.fixHTMLUTF8Entities($item['Title']).'</label></td>
				<td class="productGroup">
					'.((empty($item['CategoryName'])) ? 
						'&mdash;' :
						(fixHTMLUTF8Entities($item['CategoryName']).'
						<input type="hidden" name="catID['.$item['ASIN'].']" value="'.$item['CategoryID'].'" />
						<input type="hidden" name="catName['.$item['ASIN'].']" value="'.fixHTMLUTF8Entities($item['CategoryName']).'" />')
					).'
				</td>
				<td class="lowestprice">
					'.(!empty($item['LowestPriceFormated']) ? $item['LowestPriceFormated'] : '&mdash;').'
					<input type="hidden" name="lowprice['.$item['ASIN'].']" value="'.$item['LowestPrice'].'" />
				</td>
				<td class="asin">
					<a href="'.$item['URL'].'" title="'.ML_AMAZON_LABEL_PRODUCT_AT_AMAZON.'"
					   target="_blank" onclick="(function(url){
					   		f = window.open(url, \''.ML_AMAZON_LABEL_PRODUCT_AT_AMAZON.'\', \'width=800,height=600,resizable=yes,scrollbars=yes\');
					   		f.focus();
					   	})(this.href); return false">'.$item['ASIN'].'</a>
				</td>
			</tr>';
			$isFirst = false;
		}
		echo $nomatch;
	} else {
		echo '
		<tr class="searchFailed">
			<td class="input">&mdash;</td>
			<td class="title" colspan="3">Such-Anfrage fehlgeschlagen ('.fixHTMLUTF8Entities($productName).')</td>
			<td class="asin">&mdash;</td>
		</tr>';
	}

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
    if (!empty($product['products_details']['manufacturer_model'])) {
        $html .= '
			<tr>
				<th class="smallwidth">'.ML_GENERIC_MANUFACTURER_PARTNO.':</th>
				<td>'.$product['products_details']['manufacturer_model'].'</td>
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
	global $_MagnaSession;
	echo '
<div id="productDetailContainer" class="dialog2" title="'.ML_LABEL_DETAILS.'"></div>
<table class="matching">';
	foreach ($products as $product) {
		$addHeadline = (!empty($product['product']['products_details']['model'])
			? '<span style="color: #ddd;">'.ML_LABEL_ARTICLE_NUMBER.'</span>: '.$product['product']['products_details']['model'].', '
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
		<th class="title">'.ML_AMAZON_LABEL_TITLE.'</th>
		<th class="productGroup">'.ML_AMAZON_CATEGORY.'</th>
		<th class="lowestprice">'.ML_GENERIC_LOWEST_PRICE.'</th>
		<th class="asin">ASIN</th>
	</tr></tbody>
	<tbody class="options" id="matchingResults_'.$product['product']['products_id'].'">';
		renderMathingResultTr(
			$product['product']['products_id'], 
			$product['product']['products_name'],
			$product['product']['products_asin'], 
			$product['result']
		);
		echo '
	</tbody>
	<tbody class="func"><tr><td colspan="5">
		<div>'.ML_AMAZON_NEW_SEARCH_QUERY.': <input type="text" id="newSearch_'.$product['product']['products_id'].'"/> '.
			'<input type="button" value="OK" id="newSearchGo_'.$product['product']['products_id'].'"/></div>
		<div>'.ML_AMAZON_ENTER_ASIN_DIRECTLY.': <input type="text" id="newASIN_'.$product['product']['products_id'].'"/> '.
			'<input type="button" value="OK" id="newASINGo_'.$product['product']['products_id'].'"/></div>
	</td></tr></tbody>
	<tbody class="clear"><tr>
		<td colspan="5">&nbsp;</td>
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
					data: ({request: 'ItemSearch', 'productID': <?php echo $product['product']['products_id']; ?>, 'search':newSearch}),
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
		$('#newASINGo_<?php echo $product['product']['products_id']; ?>').click(function() {
			newASIN = $('#newASIN_<?php echo $product['product']['products_id']; ?>').val();
			if (jQuery.trim(newASIN) != '') {
				myConsole.log(newASIN);
				jQuery.blockUI({ message: blockUIMessage, css: blockUICSS });
				jQuery.ajax({
					type: 'POST',
					url: '<?php echo toURL(array('mp' => $_MagnaSession['mpID'], 'mode' => 'ajax'), true); ?>',
					data: ({request: 'ItemLookup', 'productID': <?php echo $product['product']['products_id']; ?>, 'asin':newASIN}),
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
		$('#newASIN_<?php echo $product['product']['products_id']; ?>').keypress(function(event) {
			if (event.keyCode == '13') {
				event.preventDefault();
				$('#newASINGo_<?php echo $product['product']['products_id']; ?>').click();
			}
		});
	/*]]>*/</script>
<?php
	}
	echo '
</table>';
}
