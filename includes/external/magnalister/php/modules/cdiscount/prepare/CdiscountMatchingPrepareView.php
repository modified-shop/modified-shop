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

require_once(DIR_MAGNALISTER_MODULES.'cdiscount/catmatch/CdiscountCategoryMatching.php');
require_once(DIR_MAGNALISTER_MODULES.'cdiscount/CdiscountHelper.php');

class CdiscountMatchingPrepareView extends MagnaCompatibleBase {
	
	protected $catMatch = null;
	protected $prepareSettings = array();

	public function getSelection($skipSearch = false) {
		global $_MagnaSession;

		if (isset($_POST['match']) && $_POST['match'] === 'notmatched') {
			$alreadyMatched = MagnaDB::gi()->fetchArray('
				SELECT products_id
				  FROM `' . TABLE_MAGNA_CDISCOUNT_PREPARE . '`
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

		$sLanguageCode = getDBConfigValue($this->marketplace . '.lang', $this->mpID);

		$query = '
			SELECT	ms.mpID mpID,
					p.products_id,
					p.products_model,
					p.products_price,
					pd.products_name,
					pr.ConditionType,
					pr.Comment,
					pr.Location
			  FROM ' . TABLE_PRODUCTS . ' p
		INNER JOIN ' . TABLE_MAGNA_SELECTION . ' ms ON ms.pID = p.products_id
		 LEFT JOIN ' . TABLE_PRODUCTS_DESCRIPTION . ' pd ON pd.products_id = p.products_id AND pd.language_id = "' . $sLanguageCode . '"
		 LEFT JOIN ' . TABLE_MAGNA_CDISCOUNT_PREPARE . ' pr ON pr.products_id = p.products_id AND pr.mpID = "' . $this->mpID . '"
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
				if (in_array($sKey, array('ConditionType', 'Location')) && $sValue === null) {
					switch ($sKey) {
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
				'Condition'		=> $p['ConditionType'],
				'Comment'		=> $p['Comment'],
				'Country'		=> $p['Location'],
			);

			if ($skipSearch === false) {
				$searchResult = false;
				if (empty($mlProduct['products_ean']) === false) {
					$searchResult = CdiscountHelper::SearchOnCdiscount($mlProduct['products_ean'], 'EAN');
				}

				if ($searchResult === false) {
					$searchResult = CdiscountHelper::SearchOnCdiscount($p['products_name'], 'Title');
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

		$conditions			= CdiscountHelper::GetConditionTypes();


		$defaultCondition		= getDBConfigValue($this->marketplace . '.itemcondition', $this->mpID);
		$defaultComment			= '';
		
		if (count($currentChunk) === 1) {
			$singleProduct = reset($products);

			$defaultCondition		= isset($singleProduct['Condition']) ? $singleProduct['Condition'] : $defaultCondition;
			$defaultComment			= isset($singleProduct['Comment']) ? $singleProduct['Comment'] : $defaultComment;

			$price = new SimplePrice(null, getCurrencyFromMarketplace($this->mpID));
			$price->setFinalPriceFromDB($singleProduct['Id'], $this->mpID);
			$defaultPrice = $price
					->roundPrice()
					->getPrice();
		}

		ob_start();
		?>

		<h2>
			<?= count($products) === 1 ? ML_CDISCOUNT_SINGLE_MATCHING : ML_CDISCOUNT_MULTI_MATCHING ?>
			<?php if ($totalPages > 1) : ?>
			<span class="small right successBox" style="margin-top: -13px; font-size: 12px !important;">
				<?= ML_LABEL_STEP . ' ' . $currentPage . ' von ' . $totalPages ?>
			</span>
			<?php endif ?>
		</h2>
		<form name="matching" id="matching" action="" method="POST" enctype="multipart/form-data">
			<input type="hidden" name="matching_nextpage" value="<?= $currentPage == $totalPages ? 'null' : $currentPage + 1 ?>" />
			<table class="attributesTable">
				<tbody>
					<tr class="headline">
						<td colspan="3"><h4><?= ML_CDISCOUNT_UNIT_ATTRIBUTES ?></h4></td>
					</tr>
					<tr class="odd">
						<th><?= ML_CDISCOUNT_CONDITION ?></th>
						<td class="input">
						<select name="unit[condition_id]" id="condition_id">
						<?php foreach ($conditions as $condID => $condName) : ?>
							<option <?= $condID == $defaultCondition ? 'selected' : '' ?> value="<?= $condID ?>"><?= $condName ?></option>
						<?php endforeach ?>
						</select>
						</td>
						<td class="info">&nbsp;</td>
					</tr>
					<?php if (count($products) === 1) { ?>
					<tr class="even">
						<th><?= ML_CDISCOUNT_PRICE ?></th>
						<td class="input">
							<input type="text" name="Price" id="Price" value="<?= $defaultPrice ?>" disabled="true"/>
							<lable><?= ML_CDISCOUNT_CURRENCY ?></lable>
						</td>
						<td class="info"></td>
					</tr>
					<?php } ?>
					<tr class="odd">
						<th><?= ML_CDISCOUNT_COMMENT ?></th>
						<td class="input">
							<textarea name="unit[comment]"><?= $defaultComment ?></textarea>
						</td>
						<td class="info">&nbsp;</td>
					</tr>
					<tr class="spacer">
						<td colspan="3">&nbsp;</td>
					</tr>
				</tbody>
			</table>
			<div id="productDetailContainer" class="dialog2" title="<?= ML_LABEL_DETAILS ?>"></div>
			<table class="matching">
				<?php foreach ($currentChunk as $product) : ?>
				<tbody class="product">
					<tr>
						<th colspan="5">
							<div class="title">
								<span class="darker"><?= ML_LABEL_SHOP_TITLE ?>:</span>
								<?= $product['Title'] ?>&nbsp;&nbsp;
								<span>
									[<span style="color: #ddd;"><?= ML_LABEL_ARTICLE_NUMBER ?></span>: <?= $product['Model'] ?>,
									<span style="color: #ddd;"><?= ML_LABEL_SHOP_PRICE_BRUTTO ?></span>: <?= $product['Price'] ?>]
								</span>
							</div>
							<input type="hidden" name="match[<?= $product['Id'] ?>]" value="false">
							<input type="hidden" name="model[<?= $product['Id'] ?>]" value="<?= $product['Model'] ?>">
							<div id="productDetails_<?= $product['Id'] ?>" class="productDescBtn" title="<?= ML_LABEL_DETAILS ?>"><?= ML_LABEL_DETAILS ?></div>
						</th>
					</tr>
				</tbody>
				<tbody class="headline"><tr>
					<th class="input"><?= ML_LABEL_CHOOSE ?></th>
					<th class="title"><?= ML_CDISCOUNT_LABEL_TITLE ?></th>
					<th class="productGroup"><?= ML_CDISCOUNT_CATEGORY ?></th>
					<th class="asin"><?= ML_CDISCOUNT_LABEL_ITEM_ID ?></th>
				</tr></tbody>
				<tbody class="options" id="matchingResults_<?= $product['Id'] ?>">
					<?= $this->getSearchResultsHtml($product) ?>
				</tbody>
				<tbody class="func"><tr><td colspan="5">
						<div><?= ML_CDISCOUNT_SEARCH_BY_TITLE ?>: <input type="text" id="newSearch_<?= $product['Id'] ?>"> <input type="button" value="OK" id="newSearchGo_<?= $product['Id'] ?>"></div>
						<div><?= ML_CDISCOUNT_SEARCH_BY_EAN ?>: <input type="text" id="newEAN_<?= $product['Id'] ?>"> <input type="button" value="OK" id="newEANGo_<?= $product['Id'] ?>"></div>
				</td></tr></tbody>
				<tbody class="clear">
					<tr>
						<td colspan="5">&nbsp;</td>
					</tr>
				</tbody>
				<script type="text/javascript">/*<![CDATA[*/
					var productDetailJson_<?= $product['Id'] ?> = <?php echo $this->renderDetailView($product); ?>
					
					$('#productDetails_<?= $product['Id'] ?>').click(function() {
						myConsole.log(productDetailJson_<?= $product['Id'] ?>);
						$('#productDetailContainer').html(productDetailJson_<?= $product['Id'] ?>.content).jDialog({
							width: "75%",
							title: productDetailJson_<?= $product['Id'] ?>.title
						});
					});
					$('#newSearchGo_<?= $product['Id'] ?>').click(function() {
						newSearch = $('#newSearch_<?= $product['Id'] ?>').val();
						if (jQuery.trim(newSearch) != '') {
							jQuery.blockUI({ message: blockUIMessage, css: blockUICSS });
							myConsole.log(newSearch);
							jQuery.ajax({
								type: 'POST',
								url: 'magnalister.php?mp=<?= $_MagnaSession['mpID'] ?>&kind=ajax',
								data: ({request: 'ItemSearchByTitle', 'productID': <?= $product['Id'] ?>, 'search': newSearch}),
								dataType: "html",
								success: function(data) {
									$('#matchingResults_<?= $product['Id'] ?>').html(data);
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
					$('#newSearch_<?= $product['Id'] ?>').keypress(function(event) {
						if (event.keyCode == '13') {
							event.preventDefault();
							$('#newSearchGo_<?= $product['Id'] ?>').click();
						}
					});
					$('#newEANGo_<?= $product['Id'] ?>').click(function() {
						newEAN = $('#newEAN_<?= $product['Id'] ?>').val();
						if (jQuery.trim(newEAN) != '') {
							myConsole.log(newEAN);
							jQuery.blockUI({ message: blockUIMessage, css: blockUICSS });
							jQuery.ajax({
								type: 'POST',
								url: 'magnalister.php?mp=<?= $_MagnaSession['mpID'] ?>&kind=ajax',
								data: ({request: 'ItemSearchByEAN', 'productID': <?= $product['Id'] ?>, 'ean': newEAN}),
								dataType: "html",
								success: function(data) {
									$('#matchingResults_<?= $product['Id'] ?>').html(data);
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
					$('#newEAN_<?= $product['Id'] ?>').keypress(function(event) {
						if (event.keyCode == '13') {
							event.preventDefault();
							$('#newEANGo_<?= $product['Id'] ?>').click();
						}
					});
				/*]]>*/
				</script>
				<?php endforeach ?>
			</table>
			<table class="actions">
				<thead>
					<tr>
						<th><?= ML_LABEL_ACTIONS ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<table>
								<tbody>
									<tr>
										<td class="first_child">
											<a href="<?= toURL($this->resources['url']) ?>" title="<?= ML_BUTTON_LABEL_BACK ?>" class="ml-button"><?= ML_BUTTON_LABEL_BACK ?></a>
										</td>
										<td class="last_child">
											<input type="submit" class="ml-button" name="saveMatching" value="<?= $currentPage == $totalPages ? ML_BUTTON_LABEL_SAVE_DATA : ML_BUTTON_LABEL_SAVE_AND_NEXT ?>" />
										</td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
				</tbody>
			</table>
		</form>

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
				<input type="radio" name="match[<?= $product['Id'] ?>]" id="match_<?= $product['Id'] . '_' . $result['id_item'] ?>" value="<?= $result['id_item'] ?>" <?= $checkedProductId === $result['id_item'] ? 'checked' : '' ?>>
				<input type="hidden" name="ean[<?= $result['id_item'] ?>]" value="<?= reset($result['eans']) ?>">
			</td>
			<td class="title">
				<label for="match_<?= $product['Id'] . '_' . $result['id_item'] ?>"><?= $result['title'] ?></label>
				<input type="hidden" name="title[<?= $result['id_item'] ?>]" value="<?= $result['title'] ?>">
			</td>
			<td class="productGroup">
				<?= $result['category_name'] ?>
			</td>
			<td class="asin">
				<a href="<?= $result['url'] ?>" title="<?= ML_CDISCOUNT_LABEL_PRODUCT_AT_CDISCOUNT ?>" target="_blank" onclick="
					(function(url) {
						f = window.open(url, '<?= ML_CDISCOUNT_LABEL_PRODUCT_AT_CDISCOUNT ?>', 'width=1017, height=600, resizable=yes, scrollbars=yes');
						f.focus();
					})(this.href);
					return false;">
					<?= $result['id_item'] ?>
				</a>
			</td>
		</tr>
		<?php endforeach ?>
		<tr class="last noItem">
			<td class="input"><input type="radio" name="match[<?= $product['Id'] ?>]" id="match_<?= $product['Id'] ?>_false" value="false" <?= $checkedProductId === null ? 'checked' : '' ?>></td>
			<td class="title italic"><label for="match_<?= $product['Id'] ?>_false"><?= ML_CDISCOUNT_LABEL_NOT_MATCHED ?></label></td>
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
					<th class="smallwidth"><?= ML_GENERIC_MANUFACTURER_NAME ?>:</th>
					<td><?= $product['Manufacturer'] ?></td>
				</tr>
			<?php endif ?>
			<?php if (empty($product['Model']) === false) : ?>
				<tr>
					<th class="smallwidth"><?= ML_GENERIC_MODEL_NUMBER ?>:</th>
					<td><?= $product['Model'] ?></td>
				</tr>
			<?php endif ?>
			<?php if (empty($product['EAN']) === false || (SHOPSYSTEM != 'oscommerce')) : ?>
				<tr>
					<th class="smallwidth"><?= ML_GENERIC_EAN ?>:</th>
					<td><?= empty($product['EAN']) === true ? '&nbsp;' : $product['EAN'] ?></td>
				</tr>
			<?php endif ?>
			<?php if (empty($product['Description']) === false) : ?>
				<tr>
					<th colspan="2"><?= ML_GENERIC_MY_PRODUCTDESCRIPTION ?></th>
				</tr>
				<tr class="desc">
					<td colspan="2"><div class="mlDesc"><?= $product['Description'] ?></div></td>
				</tr>
			<?php endif ?>
			<?php if (empty($product['Images']) === false) : ?>
				<tr>
					<th colspan="2"><?= ML_LABEL_PRODUCTS_IMAGES ?></th>
				</tr>
				<tr class="images">
					<td colspan="2">
						<div class="main">
						<?php foreach ($product['Images'] as $image) : ?>
							<table>
								<tbody>
									<tr>
										<td style="width: <?= $w ?>px; height: <?= $h ?>px;">
											<?= generateProductCategoryThumb($image, $w, $h) ?>
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
