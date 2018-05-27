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
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'tradoria/prepare/TradoriaCategoryMatching.php');
require_once(DIR_MAGNALISTER_MODULES.'tradoria/TradoriaHelper.php');

class TradoriaMatchingPrepareView extends MagnaCompatibleBase {
	
	protected $catMatch = null;
	protected $prepareSettings = array();

	public function getSelection($skipSearch = false) {
		global $_MagnaSession;

		if (isset($_POST['match']) && $_POST['match'] === 'notmatched') {
			$alreadyMatched = MagnaDB::gi()->fetchArray('
				SELECT products_id
				  FROM `' . TABLE_MAGNA_TRADORIA_PREPARE . '`
				 WHERE mpID = "' . $this->mpID . '"
					   AND Verified = "OK"
			', true);

			MagnaDB::gi()->query('
				DELETE FROM ' . TABLE_MAGNA_SELECTION . '
				 WHERE mpID = "' . $this->mpID . '"
				   AND selectionname = "match"
				   AND session_id = "' . session_id() . '"
				   AND pID IN ("' . implode('", "', $alreadyMatched) . '")
			');
		}

		$sLanguageId = getDBConfigValue($this->marketplace . '.lang', $this->mpID);

		$query = '
			SELECT	ms.mpID mpID,
					p.products_id,
					p.products_model,
					p.products_price,
					pd.products_name,
					pr.ShippingTime,
					pr.ConditionType,
					pr.Comment,
					pr.Location
			  FROM ' . TABLE_PRODUCTS . ' p
		INNER JOIN ' . TABLE_MAGNA_SELECTION . ' ms ON ms.pID = p.products_id
		 LEFT JOIN ' . TABLE_PRODUCTS_DESCRIPTION . ' pd ON pd.products_id = p.products_id AND pd.language_id = "' . $sLanguageId . '"
		 LEFT JOIN ' . TABLE_MAGNA_TRADORIA_PREPARE . ' pr ON pr.products_id = p.products_id AND pr.mpID = "' . $this->mpID . '"
			 WHERE ms.mpID = "' . $this->mpID . '"
			   AND selectionname = "match"
			   AND session_id = "' . session_id() . '"
		';

		$selection = MagnaDB::gi()->fetchArray(eecho($query, false));

		$products = array();

		$price = new SimplePrice();
		$price->setCurrency(getCurrencyFromMarketplace($_MagnaSession['mpID']));

		foreach ($selection as $p) {
			$mlProduct = MLProduct::gi()->getProductByIdOld($p['products_id']);

			$price->setPrice($mlProduct['products_price'])->calculateCurr();
			$price->addTaxByTaxID($mlProduct['products_tax_class_id']);

			if ($mlProduct['manufacturers_id'] > 0) {
				$manufacturerName = MagnaDB::gi()->fetchOne("
					SELECT manufacturers_name
					  FROM ".TABLE_MANUFACTURERS."
					 WHERE manufacturers_id = '".$mlProduct['manufacturers_id']."'
				");
			} else {
				$manufacturerName = '';
			}

			foreach ($p as $sKey => &$sValue) {
				if (in_array($sKey, array('ShippingTime', 'ConditionType', 'Location')) && $sValue === null) {
					switch ($sKey) {
						case 'ShippingTime':
							$sValue = getDBConfigValue($this->marketplace.'.shippingtime', $this->mpID, 0);
							break;
						case 'ConditionType':
							$sValue = getDBConfigValue($this->marketplace.'.itemcondition', $this->mpID, 0);
							break;
						case 'Location':
							$sValue = getDBConfigValue($this->marketplace.'.itemcountry', $this->mpID, 0);
							break;
						default:
							breaK;
					}
				}
			}

			$product = array(
				'Id'			=> $p['products_id'],
				'Model'			=> $p['products_model'],
				'Title'			=> $p['products_name'],
				'Description'	=> $mlProduct['products_description'],
				'Images'		=> $mlProduct['products_allimages'],
				'Price'			=> $price->format(),
				'Manufacturer'	=> $manufacturerName,
				'EAN'			=> $mlProduct['products_ean'],
				'ShippingTime'	=> $p['ShippingTime'],
				'Condition'		=> $p['ConditionType'],
				'Comment'		=> $p['Comment'],
				'Country'		=> $p['Location'],
			);

			if ($skipSearch === false) {
				$searchResult = false;
				if (empty($mlProduct['products_ean']) === false) {
					$searchResult = TradoriaHelper::SearchOnTradoria($mlProduct['products_ean'], 'EAN');
					if ($searchResult){
						$product['SearchCriteria'] = 'EAN';
					}
				}

				if ($searchResult === false) {
					$searchResult = TradoriaHelper::SearchOnTradoria($p['products_name'], 'Title');
					if ($searchResult){
						$product['SearchCriteria'] = 'KW';
					}
				}
				
				if ($searchResult !== false) {
					$product['Results'] = $searchResult;
				}
			}

			$products[] = $product;
		}

		return $products;
	}
	
	public function process() {
		global $_MagnaSession;

		// Determine current page
		if (isset($_POST['matching_nextpage']) && $_POST['matching_nextpage'] !== null) {
			$currentPage = $_POST['matching_nextpage'];
		} else {
			$currentPage = 1;
		}
		
		$products = $this->getSelection();

		$itemsPerPage = getDBConfigValue($this->marketplace . '.multimatching.itemsperpage', $this->mpID);
		
		$productChunks = array_chunk($products, $itemsPerPage);

		$totalPages = count($productChunks);

		$currentChunk = $productChunks[$currentPage - 1];
		$defaultComment	= '';
		
		if (count($currentChunk) === 1) {
			$singleProduct = reset($products);
			$defaultComment			= isset($singleProduct['Comment']) ? $singleProduct['Comment'] : $defaultComment;
			
			$price = new SimplePrice(null, getCurrencyFromMarketplace($this->mpID));
			$price->setFinalPriceFromDB($singleProduct['Id'], $this->mpID);
		}

		ob_start();
		?>

		<h2>
			<?php echo count($products) === 1 ? ML_TRADORIA_SINGLE_MATCHING : ML_TRADORIA_MULTI_MATCHING ?>
			<?php if ($totalPages > 1) : ?>
			<span class="small right successBox" style="margin-top: -13px; font-size: 12px !important;">
				<?php echo ML_LABEL_STEP . ' ' . $currentPage . ' von ' . $totalPages ?>
			</span>
			<?php endif ?>
		</h2>
		<form name="matching" id="matching" action="" method="POST" enctype="multipart/form-data">
			<input type="hidden" name="matching_nextpage" value="<?php echo $currentPage == $totalPages ? 'null' : $currentPage + 1 ?>" />
			<table class="attributesTable">
				<tbody>
					<tr class="headline">
						<td colspan="3"><h4><?php echo ML_TRADORIA_UNIT_ATTRIBUTES ?></h4></td>
					</tr>
					<tr class="odd">
						<th><?php echo ML_TRADORIA_COMMENT ?></th>
						<td class="input">
							<textarea name="unit[comment]"><?php echo $defaultComment ?></textarea>
						</td>
						<td class="info">&nbsp;</td>
					</tr>
					<tr class="spacer">
						<td colspan="3">&nbsp;</td>
					</tr>
				</tbody>
			</table>
			<div id="productDetailContainer" class="dialog2" title="<?php echo ML_LABEL_DETAILS ?>"></div>
			<table class="matching">
				<?php foreach ($currentChunk as $product) : ?>
				<tbody class="product">
					<tr>
						<th colspan="5">
							<div class="title">
								<span class="darker"><?php echo ML_LABEL_SHOP_TITLE ?>:</span>
								<?php echo $product['Title'] ?>&nbsp;&nbsp;
								<span>
									[<span style="color: #ddd;"><?php echo ML_LABEL_ARTICLE_NUMBER ?></span>: <?php echo $product['Model'] ?>,
									<span style="color: #ddd;"><?php echo ML_LABEL_SHOP_PRICE_BRUTTO ?></span>: <?php echo $product['Price'] ?>]
								</span>
							</div>
							<input type="hidden" name="match[<?php echo $product['Id'] ?>]" value="false">
							<input type="hidden" name="model[<?php echo $product['Id'] ?>]" value="<?php echo $product['Model'] ?>">
							<div id="productDetails_<?php echo $product['Id'] ?>" class="productDescBtn" title="<?php echo ML_LABEL_DETAILS ?>"><?php echo ML_LABEL_DETAILS ?></div>
						</th>
					</tr>
				</tbody>
				<tbody class="headline"><tr>
					<th class="input"><?php echo ML_LABEL_CHOOSE ?></th>
					<th class="title"><?php echo ML_TRADORIA_LABEL_TITLE ?></th>
					<th class="productGroup"><?php echo ML_TRADORIA_CATEGORY ?></th>
					<th class="asin"><?php echo ML_TRADORIA_LABEL_ITEM_ID ?></th>
					<input type="hidden" name="matching[<?php echo $product['Id'] ?>][title]"
						   id="match_title_<?php echo $product['Id'] ?>"/>
					<input type="hidden" name="matching[<?php echo $product['Id'] ?>][ean]"
						   id="match_ean_<?php echo $product['Id'] ?>"/>
				</tr></tbody>
				<tbody class="options" id="matchingResults_<?php echo $product['Id'] ?>">
					<?php echo $this->getSearchResultsHtml($product) ?>
				</tbody>
				<tbody class="func"><tr><td colspan="5">
						<div><?php echo ML_TRADORIA_SEARCH_BY_TITLE ?>:
							<input type="text" id="newSearch_<?php echo $product['Id'] ?>"
								   value="<?php echo isset($product['SearchCriteria']) && $product['SearchCriteria'] === 'KW' ? fixHTMLUTF8Entities($product['Title'], ENT_COMPAT) : ''; ?>">
							<input type="button" value="OK" id="newSearchGo_<?php echo $product['Id'] ?>">
						</div>
						<div><?php echo ML_TRADORIA_SEARCH_BY_EAN ?>:
							<input type="text" id="newEAN_<?php echo $product['Id'] ?>"
								   value="<?php echo isset($product['SearchCriteria']) && $product['SearchCriteria'] === 'EAN' ? $product['EAN'] : ''; ?>">
							<input type="button" value="OK" id="newEANGo_<?php echo $product['Id'] ?>">
						</div>
				</td></tr></tbody>
				<tbody class="clear">
					<tr>
						<td colspan="5">&nbsp;</td>
					</tr>
				</tbody>
				<script type="text/javascript">/*<![CDATA[*/
					var productDetailJson_<?php echo $product['Id'] ?> = <?php echo $this->renderDetailView($product); ?>
					
					$('#productDetails_<?php echo $product['Id'] ?>').click(function() {
						myConsole.log(productDetailJson_<?php echo $product['Id'] ?>);
						$('#productDetailContainer').html(productDetailJson_<?php echo $product['Id'] ?>.content).jDialog({
							width: "75%",
							title: productDetailJson_<?php echo $product['Id'] ?>.title
						});
					});
					$('#newSearchGo_<?php echo $product['Id'] ?>').click(function() {
						newSearch = $('#newSearch_<?php echo $product['Id'] ?>').val();
						if (jQuery.trim(newSearch) != '') {
							jQuery.blockUI({ message: blockUIMessage, css: blockUICSS });
							myConsole.log(newSearch);
							jQuery.ajax({
								type: 'POST',
								url: 'magnalister.php?mp=<?php echo $_MagnaSession['mpID'] ?>&kind=ajax',
								data: ({request: 'ItemSearchByTitle', 'productID': <?php echo $product['Id'] ?>, 'search': newSearch}),
								dataType: "html",
								success: function(data) {
									$('#matchingResults_<?php echo $product['Id'] ?>').html(data);
									if (function_exists("initRadioButtons")) {
										initRadioButtons('#matchingResults_<?php echo $product['Id'] ?>');
									}
									jQuery.unblockUI();
								},
								error: function() {
									jQuery.unblockUI();
								}
							});
						}
					});
					$('#newSearch_<?php echo $product['Id'] ?>').keypress(function(event) {
						if (event.keyCode == '13') {
							event.preventDefault();
							$('#newSearchGo_<?php echo $product['Id'] ?>').click();
						}
					});
					$('#newEANGo_<?php echo $product['Id'] ?>').click(function() {
						newEAN = $('#newEAN_<?php echo $product['Id'] ?>').val();
						if (jQuery.trim(newEAN) != '') {
							myConsole.log(newEAN);
							jQuery.blockUI({ message: blockUIMessage, css: blockUICSS });
							jQuery.ajax({
								type: 'POST',
								url: 'magnalister.php?mp=<?php echo $_MagnaSession['mpID'] ?>&kind=ajax',
								data: ({request: 'ItemSearchByEAN', 'productID': <?php echo $product['Id'] ?>, 'ean': newEAN}),
								dataType: "html",
								success: function(data) {
									$('#matchingResults_<?php echo $product['Id'] ?>').html(data);
									if (function_exists("initRadioButtons")) {
										initRadioButtons('#matchingResults_<?php echo $product['Id'] ?>');
									}
									jQuery.unblockUI();
								},
								error: function() {
									jQuery.unblockUI();
								}
							});
						}
					});
					$('#newEAN_<?php echo $product['Id'] ?>').keypress(function(event) {
						if (event.keyCode == '13') {
							event.preventDefault();
							$('#newEANGo_<?php echo $product['Id'] ?>').click();
						}
					});
				/*]]>*/
				</script>
				<?php endforeach ?>
			</table>
			<table class="actions">
				<thead>
					<tr>
						<th><?php echo ML_LABEL_ACTIONS ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<table>
								<tbody>
									<tr>
										<td class="first_child">
											<a href="<?php echo toURL($this->resources['url']) ?>" title="<?php echo ML_BUTTON_LABEL_BACK ?>" class="ml-button"><?php echo ML_BUTTON_LABEL_BACK ?></a>
										</td>
										<td class="last_child">
											<input type="submit" class="ml-button" name="saveMatching" value="<?php echo $currentPage == $totalPages ? ML_BUTTON_LABEL_SAVE_DATA : ML_BUTTON_LABEL_SAVE_AND_NEXT ?>" />
										</td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
		<script type="text/javascript">/*<![CDATA[*/
			$('body').on('change', 'input:radio', function() {
				var me = $(this);
				var productId = me.attr('data-id');
				$('#match_title_' + productId).val($('#title_' + me.attr('id')).attr('data-id'));
				$('#match_ean_' + productId).val(me.attr('data-ean'));
			});

			function initRadioButtons(context) {
				$(context + " input[type='radio']:checked").trigger('change');
			}

			$('input:radio:checked').trigger('change');

			/*]]>*/
		</script>

		<?php
		$renderedView = ob_get_contents();
		ob_end_clean();

		return $renderedView;
	}

	public function getSearchResultsHtml($product) {
		if (!isset($product['Results']) || is_array($product['Results']) === false || count($product['Results']) === 0) {
            $product['Results'] = array();
        }
		
		$checkedProductId = count($product['Results']) > 0 ? $product['Results'][0]['id_item'] : null;

		foreach ($product['Results'] as $result) {
			if ($result['ean_match'] === true) {
				$checkedProductId = $result['id_item'];
				break;
			}
		}

		ob_start();
		?>
		<?php foreach ($product['Results'] as $result) : ?>
		<tr class="odd last">
			<td class="input">
				<input type="radio" name="match[<?php echo $product['Id'] ?>]" id="match_<?php echo $product['Id'] . '_' . $result['id_item'] ?>"
					   data-id="<?php echo $product['Id'] ?>" data-ean="<?php echo reset($result['eans']) ?>"
					   value="<?php echo $result['id_item'] ?>" <?php echo $checkedProductId === $result['id_item'] ? 'checked' : '' ?>>
			</td>
			<td class="title">
				<label for="match_<?php echo $product['Id'] . '_' . $result['id_item'] ?>" data-id="<?php echo $result['title'] ?>"
					   id="title_match_<?php echo $product['Id'] . '_' . $result['id_item'] ?>"><?php echo $result['title'] ?></label>
			</td>
			<td class="productGroup">
				<?php echo $result['category_name'] ?>
			</td>
			<td class="asin">
				<a href="<?php echo $result['url'] ?>" title="<?php echo ML_TRADORIA_LABEL_PRODUCT_AT_TRADORIA ?>" target="_blank" onclick="
					(function(url) {
						f = window.open(url, '<?php echo ML_TRADORIA_LABEL_PRODUCT_AT_TRADORIA ?>', 'width=1017, height=600, resizable=yes, scrollbars=yes');
						f.focus();
					})(this.href);
					return false;">
					<?php echo $result['id_item'] ?>
				</a>
			</td>
		</tr>
		<?php endforeach ?>
		<tr class="last noItem">
			<td class="input"><input type="radio" name="match[<?php echo $product['Id'] ?>]" id="match_<?php echo $product['Id'] ?>_false" value="false" <?php echo $checkedProductId === null ? 'checked' : '' ?>></td>
			<td class="title italic"><label for="match_<?php echo $product['Id'] ?>_false"><?php echo ML_TRADORIA_LABEL_NOT_MATCHED ?></label></td>
			<td class="productGroup">&nbsp;</td>
			<td class="asin">&nbsp;</td>
		</tr>
		<?php

		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	private function renderDetailView($product) {
		$w = 60;
		$h = 60;

		ob_start();
		?>

		<table class="matchingDetailInfo">
			<tbody>
			<?php if (empty($product['Manufacturer']) === false) : ?>
				<tr>
					<th class="smallwidth"><?php echo ML_GENERIC_MANUFACTURER_NAME ?>:</th>
					<td><?php echo $product['Manufacturer'] ?></td>
				</tr>
			<?php endif ?>
			<?php if (empty($product['Model']) === false) : ?>
				<tr>
					<th class="smallwidth"><?php echo ML_GENERIC_MODEL_NUMBER ?>:</th>
					<td><?php echo $product['Model'] ?></td>
				</tr>
			<?php endif ?>
			<?php if (empty($product['EAN']) === false || (SHOPSYSTEM != 'oscommerce')) : ?>
				<tr>
					<th class="smallwidth"><?php echo ML_GENERIC_EAN ?>:</th>
					<td><?php echo empty($product['EAN']) === true ? '&nbsp;' : $product['EAN'] ?></td>
				</tr>
			<?php endif ?>
			<?php if (empty($product['Description']) === false) : ?>
				<tr>
					<th colspan="2"><?php echo ML_GENERIC_MY_PRODUCTDESCRIPTION ?></th>
				</tr>
				<tr class="desc">
					<td colspan="2"><div class="mlDesc"><?php echo $product['Description'] ?></div></td>
				</tr>
			<?php endif ?>
			<?php if (empty($product['Images']) === false) : ?>
				<tr>
					<th colspan="2"><?php echo ML_LABEL_PRODUCTS_IMAGES ?></th>
				</tr>
				<tr class="images">
					<td colspan="2">
						<div class="main">
						<?php foreach ($product['Images'] as $image) : ?>
							<table>
								<tbody>
									<tr>
										<td style="width: <?php echo $w ?>px; height: <?php echo $h ?>px;">
											<?php echo generateProductCategoryThumb($image, $w, $h) ?>
										</td>
									</tr>
								</tbody>
							</table>
						<?php endforeach ?>
						</div>
					</td>
				</tr>
			<?php endif ?>
			</tbody>
		</table>

		<?php
		$html = ob_get_contents();
		ob_end_clean();

		return json_encode(array(
			'title' => ML_LABEL_DETAILS_FOR.': '.$product['Title'],
			'content' => utf8_encode($html),
		));
	}
}
