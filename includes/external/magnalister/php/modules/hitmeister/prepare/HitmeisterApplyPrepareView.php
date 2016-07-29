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

require_once(DIR_MAGNALISTER_MODULES.'hitmeister/catmatch/HitmeisterCategoryMatching.php');
require_once(DIR_MAGNALISTER_MODULES.'hitmeister/HitmeisterHelper.php');

class HitmeisterApplyPrepareView extends MagnaCompatibleBase {
	
	protected $catMatch = null;
	protected $prepareSettings = array();
	protected $price = null;
	
	protected function initCatMatching() {
		$this->price = new SimplePrice(null, getCurrencyFromMarketplace($this->mpID));
		$params = array();
		foreach (array('mpID', 'marketplace', 'marketplaceName', 'prepareSettings') as $attr) {
			if (isset($this->$attr)) {
				$params[$attr] = &$this->$attr;
			}
		}
		
		$this->catMatch = new HitmeisterCategoryMatching($params);
	}

	protected function showProductDetails($data) {
		if (1 != count($data)) {
			return '';
		}
		
		$data = $data[0];
		$oddEven = false;
		$pictureUrls = array();
		$aProduct = MLProduct::gi()->setLanguage(getDBConfigValue($this->marketplace . '.lang', $this->mpID))->getProductById($data['products_id']);
		if (isset($data['PictureUrl']) && empty($data['PictureUrl']) === false) {
			$pictureUrls = json_decode($data['PictureUrl'], true);
		}
		
		if (empty($pictureUrls) || !is_array($pictureUrls)) {
			$pictureUrls = array();
			$i = 0;
			foreach ($aProduct['Images'] as $img) {
				$pictureUrls[$img] = 'true';
			}
		}

		foreach ($aProduct['Images'] as $img) {
			$img = fixHTMLUTF8Entities($img, ENT_COMPAT);
			$data['Images'][$img] = (isset($pictureUrls[$img]) && ($pictureUrls[$img] === 'true')) ? 'true' : 'false';
		}
		
		ob_start();
		?>

		<tbody>
			<tr class="headline">
				<td colspan="3"><h4><?= ML_HITMEISTER_PRODUCT_DETAILS ?></h4></td>
			</tr>
			<tr class="<?= ($oddEven = !$oddEven) ? 'odd' : 'even' ?>">
				<th><?= ML_HITMEISTER_ITEM_NAME_TITLE ?></th>
				<td class="input">
					<input type="text" class="fullwidth" name="Title" id="Title" value="<?= fixHTMLUTF8Entities($data['Title'], ENT_COMPAT, 'UTF-8') ?>"/>
				</td>
				<td class="info"></td>
			</tr>
			<tr class="<?= ($oddEven = !$oddEven) ? 'odd' : 'even' ?>">
				<th><?= ML_HITMEISTER_SUBTITLE ?></th>
				<td class="input">
					<input type="text" class="fullwidth" name="Subtitle" id="Subtitle" value="<?= fixHTMLUTF8Entities($data['Subtitle'], ENT_COMPAT, 'UTF-8') ?>"/>
				</td>
				<td class="info"></td>
			</tr>
			<tr class="<?= ($oddEven = !$oddEven) ? 'odd' : 'even' ?>">
				<th><?= ML_HITMEISTER_DESCRIPTION ?></th>
				<td class="input">
					<?= magna_wysiwyg(array(
						'id' => 'Description',
						'name' => 'Description',
						'class' => 'fullwidth',
						'cols' => '80',
						'rows' => '20',
						'wrap' => 'virtual'
					), fixHTMLUTF8Entities($data['Description'], ENT_COMPAT)) ?>
				</td>
				<td class="info"></td>
			</tr>
			<tr class="<?= ($oddEven = !$oddEven) ? 'odd' : 'even' ?>">
				<th><?= ML_LABEL_PRODUCTS_IMAGES ?></th>
				<td class="input">
					<input type="hidden" id="image_hidden" name="Images[]" value="false"/>
				<?php foreach ($data['Images'] as $img => $checked) : ?>
					<table class="imageBox"><tbody>
						<tr><td class="image"><label for="image_<?= $img ?>"><?= generateProductCategoryThumb($img, 60, 60) ?></label></td></tr>
						<tr><td class="cb"><input type="checkbox" id="image_<?= $img ?>" name="Images[<?= urlencode($img) ?>]" value="true" <?= $checked == 'true' ? 'checked="checked"' : '' ?> /></td></tr>
					</tbody></table>
				<?php endforeach; ?>
				</td>
				<td class="info"></td>
			</tr>
			<tr class="spacer">
				<td colspan="3">&nbsp;</td>
			</tr>
		</tbody>
		
		<?php
		$html = ob_get_contents();
		ob_end_clean();
		
		return $html;
	}
	
	public function process() {
		$this->initCatMatching();
		$data = $this->getSelection();
		$preSelected = $this->getPreSelectedData($data);
		
		$html = '
			<p>'.ML_AMAZON_TEXT_APPLY_REQUIERD_FIELDS.'</p>
			<form method="post" action="'.toURL($this->resources['url']).'">
				'.$this->catMatch->renderMatching();

		$shippingTimes = HitmeisterHelper::GetShippingTimes();
		$defaultShippingTime = $preSelected['ShippingTime'];
		if (getDBConfigValue(array('hitmeister.shippingtimematching.prefer', 'val'), $this->mpID, false)) {
			$shippingTimes['m']  = ML_HITMEISTER_USE_SHIPPINGTIME_MATCHING;
			$defaultShippingTime = 'm';
		}
		
		$conditions				= HitmeisterHelper::GetConditionTypes();
		$defaultCondition		= $preSelected['ConditionType'];
		$defaultMpCategory		= '0';
		$defaultMpCategoryName	= '';
		$defaultComment			= $preSelected['Comment'];
		$deliveryCountries		= HitmeisterHelper::GetDeliveryCountries();
		$defaultDeliveryCountry = $preSelected['Location'];
		
		$prepareView = (1 == count($data)) ? 'single' : 'multiple';
		if ('single' == $prepareView) {
			$defaultMpCategory     = $preSelected['MarketplaceCategories'];
			$defaultMpCategoryName = $this->catMatch->getMPCategory($defaultMpCategory);
			if (is_array($defaultMpCategoryName)) {
				$defaultMpCategoryName = fixHTMLUTF8Entities($defaultMpCategoryName['CategoryName']);
			}
			
			$this->price->setFinalPriceFromDB($data[0]['products_id'], $this->mpID);
			$defaultPrice = $this->price
					->roundPrice()
					->getPrice();
			
			ob_start();
			?>
			<script type="text/javascript">
			/*<![CDATA[*/
				(function ($) {
					$(document).ready(function () {
						$('#mpCategory').val('<?php echo $defaultMpCategory; ?>').trigger('change');
						$('#mpCategoryName').val('<?php echo $defaultMpCategoryName; ?>');
						$('#mpCategoryVisual').html('<?php echo $this->catMatch->getMPCategoryPath($defaultMpCategory); ?>');
					});	
				}(jQuery))
			/*]]>*/
			</script>
			<?php
			$html .= ob_get_contents();
			ob_end_clean();
			
			if ('m' == $defaultShippingTime) {
				$products_shippingtime = $preSelected['ShippingTime'];
				$shippingtimeMatching = getDBConfigValue($this->marketplace . '.shippingtimematching.values', $this->mpID, array());
				if (array_key_exists($products_shippingtime, $shippingtimeMatching)) {
					$defaultShippingTime = $shippingtimeMatching["$products_shippingtime"];
					unset($shippingTimes['m']);
				}
			}
		}
		
		ob_start();
		?>
		<script type="text/javascript">
		/*<![CDATA[*/
			(function ($) {
				$('#mpCategory').change(function() {
					var catID = $('#mpCategory').val();
					var itemID = '<?php echo $data[0]['products_id']?>';
					jQuery.ajax({
						type: 'POST',
						url: '<?php echo toURL(array('mp' => $this->mpID, 'mode' => 'prepare', 'view' => 'apply', 'where' => 'catAttributes', 'kind' => 'ajax'), true);?>',
						data: ({categoryID: catID, itemID: itemID}),
						dataType: 'html',
						success: function(data) {
							$('.categoryAttributes').html(data);
							jQuery.unblockUI();
						},
						error: function() {
							jQuery.unblockUI();
						}
					});
				});

				$('.categoryAttributes').on('click', '.ml-button.plus', (function(e) {
					if (e.target) {
						var attributeName = newElementId(e.target.id, false, false);
						var className = $('#' + e.target.id).closest('tr').attr('class');
						var thTitle = $('#' + e.target.id + '_th').html();
						$('#' + e.target.id + '.ml-button.plus')[0].style.display = 'none';
						
						if (attributeName === 'additional_categories') {
							addAdditionalCategoriesRow(className, thTitle, e.target.id);
						} else {
							var mandatory = $('input[type="hidden"]#' + e.target.id).val();
							addRow(className, thTitle, e.target.id, mandatory);
						}
					}
				}));

				$('.categoryAttributes').on('click', '.ml-button.minus', (function(e) {
					if (e.target) {
						var elementId = newElementId(e.target.id, false, false);
						if (elementId === 'additional_categories') {
							var className = $('#' + e.target.id).closest('tr').attr('class');
							$('#' + e.target.id).parents('tr .' + className).remove();
						} else {
							$('#' + e.target.id).closest('tr').remove();
						}
						$('input[id*=' + elementId + '][type="button"].ml-button.plus').last()[0].style.display = 'inline-block';
					}
				}));

				function addRow(className, thTitle, elementId, mandatory) {
					if (className === 'even') {
						className = 'odd';
					} else {
						className = 'even';
					}

					var nextElementId = newElementId(elementId, true, false);
					var idForClass = newElementId(elementId, false, false);

					var row =	'<tr class="' + className + '">\n\
									<th id="' + nextElementId + '_th">' + thTitle + ((mandatory) ? '<span class="bull">&bull;</span>' : '')+'</th>\n\
									<td class="input">\n\
										<input type="text" class="fullwidth" name="catAttributes[' + idForClass + '][values][]" id="' + nextElementId + '">\n\
									</td>\n\
									<td style="width: 100px">\n\
										<input id="' + nextElementId + '" type="button" value="+" class="ml-button plus"/>\n\
										<input id="' + nextElementId + '" type="button" value="-" class="ml-button minus"/>\n\
									</td>\n\\n\
									<input id="' + nextElementId + '" type="hidden" name="catAttributes[' + idForClass + '][required]" value="' + mandatory + '"/>\n\
								</tr>';
					$('#' + elementId).closest('tr').after(row);
				}
				
				function addAdditionalCategoriesRow(className, thTitle, elementId) {
					var classSelector;
					if (className === 'even') {
						className = 'odd';
						classSelector = 'even';
					} else {
						className = 'even';
						classSelector = 'odd';
					}

					var nextElementId = newElementId(elementId, true, false);
					var idForClass = newElementId(elementId, false, false);
					var key = newElementId(elementId, false, true);

					var row =	'<table><tr class="' + className + '">\n\
									<th id="' + nextElementId + '_th">' + thTitle + '</th>\n\
									<td class="input">\n\
										<table class="matchingTable" style="width:100%;"><tbody>\n\
											<tr class="' + className + '">\n\
												<td><div class="catVisual" id="mpCategoryVisualAdditional_' + key + '"></div></td>\n\
												<td class="buttons">\n\
													<input type="hidden" id="mpCategoryAdditional_' + key + '" name="mpCategoryAdditional_' + key + '" value=""/>\n\
													<input type="hidden" name="catAttributes[' + idForClass + '][values][]" id="' + nextElementId + '" value=""/>\n\
													<input class="fullWidth ml-button smallmargin" type="button" value="<?php echo ML_LABEL_CHOOSE ?>" id="selectMPCategoryAdditional_' + key + '"/>\n\
												</td>\n\
											</tr>\n\
										</tbody></table>\n\
									</td>\n\
									<td style="width: 100px">\n\
										<input id="' + nextElementId + '" type="button" value="+" class="ml-button plus"/>\n\
										<input id="' + nextElementId + '" type="button" value="-" class="ml-button minus"/>\n\
									</td>\n\
									<input id="' + nextElementId + '" type="hidden" name="catAttributes[' + idForClass + '][required]" value="false"/>\n\
								</tr></table>\n\
								', 
						parser = new DOMParser(),
						doc = parser.parseFromString(row, "text/html"), button = null;
					
					button = doc.getElementById('selectMPCategoryAdditional_' + key);
					if (button) {
						button.onclick = (function (key, nextElementId){
							return function() {
								mpCategorySelector.startCategorySelector(function(cID) {
									$("#mpCategoryAdditional_" + key).val(cID).trigger("change");
									mpCategorySelector.getCategoryPath($("#mpCategoryVisualAdditional_" + key));
									$("#" + nextElementId).val(encodeURIComponent($("#mpCategoryVisualAdditional_" + key).html()));
								}, "mp");
							};
						})(key, nextElementId);
					}
					    
					
					$('#' + elementId).parents('tr .' + classSelector).eq(1).after(doc.querySelector('.' + className));
					
				}

				function newElementId(elementId, next, key) {
					var n = elementId.lastIndexOf('_');
					var result = parseInt(elementId.substring(n + 1));
					result = result + 1;
					if (next === true)  {
						return elementId.substring(0, n) + '_' + result;
					} else if (key === true) {
						return result;
					}
					
					return elementId.substring(0, n);
				}

			}(jQuery))
		/*]]>*/
		</script>
		<?php
		$html .= ob_get_contents();
		ob_end_clean();
			
		# multiple items: no pre-filling except default values

		$html .= '
			<table class="attributesTable">'
				. $this->showProductDetails($data) .'
				<tbody>
				<tr class="headline">
					<td colspan="3"><h4>' . ML_HITMEISTER_UNIT_ATTRIBUTES . '</h4></td>
				</tr>
				<tr class="odd">
					<th>'.ML_HITMEISTER_CONDITION.'</th>
					<td class="input">
					<select name="condition_id" id="condition_id">';
		foreach ($conditions as $condID => $condName) {
			if ($condID == $defaultCondition) {
				$html .= '
					<option selected value="'.$condID.'">'.$condName.'</option>';
			} else {
				$html .= '
					<option value="'.$condID.'">'.$condName.'</option>';
			}
		}
		$html .= '
					</select>
					</td>
					<td class="info">&nbsp;</td>
				</tr>';
		
		if ('single' === $prepareView) {
			$html .= '
					<tr class="even">
						<th>' . ML_HITMEISTER_PRICE . '</th>
						<td class="input">
							<input type="text" name="Price" id="Price" value="' . $defaultPrice . '" disabled="true"/>
							<lable>' . ML_HITMEISTER_CURRENCY . '</lable>
						</td>
						<td class="info"></td>
					</tr>';
		}
		
		$html .= '
				<tr class="odd">
					<th>'.ML_HITMEISTER_SHIPPINGTIME.'</th>
					<td class="input">
					<select name="shippingtime" id="shippingtime">';
		foreach ($shippingTimes as $shipTimeID => $shipTimeName) {
			if ($shipTimeID == $defaultShippingTime) {
				$html .= '
					<option selected value="'.$shipTimeID.'">'.fixHTMLUTF8Entities($shipTimeName, ENT_COMPAT, 'UTF-8').'</option>';
			} else {
				$html .= '
					<option value="'.$shipTimeID.'">'.fixHTMLUTF8Entities($shipTimeName, ENT_COMPAT, 'UTF-8').'</option>';
			}
		}
		
		$html .= '
					</select>
					</td>
					<td class="info">&nbsp;</td>
				</tr>
				<tr class="even">
					<th>'.ML_HITMEISTER_DELIVERY_COUNTRY.'</th>
					<td class="input">
					<select name="deliverycountry" id="deliverycountry">';
		foreach ($deliveryCountries as $deliveryCountryID => $deliveryCountryName) {
			if ($deliveryCountryID == $defaultDeliveryCountry) {
				$html .= '
					<option selected value="'.$deliveryCountryID.'">'.fixHTMLUTF8Entities($deliveryCountryName, ENT_COMPAT, 'UTF-8').'</option>';
			} else {
				$html .= '
					<option value="'.$deliveryCountryID.'">'.fixHTMLUTF8Entities($deliveryCountryName, ENT_COMPAT, 'UTF-8').'</option>';
			}
		}
		
		$html .= '
					</select>
					</td>
					<td class="info">&nbsp;</td>
				</tr>
				<tr class="odd">
					<th>'.ML_HITMEISTER_COMMENT.'</th>
					<td class="input">
						<textarea name="comment">'.$defaultComment.'</textarea>
					</td>
					<td class="info">&nbsp;</td>
				</tr>
				<tr class="spacer">
					<td colspan="3">&nbsp;</td>
				</tr>
			</tbody></table>
			<table class="actions">
				<thead><tr><th>'.ML_LABEL_ACTIONS.'</th></tr></thead>
				<tbody>
					<tr><td>
						<table><tbody>
							<tr><td>
								<input type="submit" class="ml-button mlbtn-action" name="saveMatching" value="'.ML_BUTTON_LABEL_SAVE_DATA.'"/>
							</td></tr>
						</tbody></table>
					</td></tr>
				</tbody>
			</table>';
			
		$html .= '
			</form>';
		
		return $html;
	}
	
	public function renderAjax() {
		if (isset($_GET['where']) && ($_GET['where'] == 'catMatchView')) {
			$this->initCatMatching();
			return $this->catMatch->renderAjax();
		} else if (isset($_GET['where']) && ($_GET['where'] == 'catAttributes')) {
			try {
				if (empty($_POST['categoryID'])) {
					return null;
				}
				$catAttributes = MagnaConnector::gi()->submitRequest(array('ACTION' => 'GetCategoryDetails', 'DATA' => array('CategoryID' => $_POST['categoryID'])));
			} catch (MagnaException $me) {
				$catAttributes = array (
					'DATA' => null
				);
			}
			
			if (isset($catAttributes['DATA']['attributes'])) {
				return $this->renderCatAttributes($catAttributes['DATA'], $_POST['itemID']);
			}
		}
	}
	
	protected function getPreSelectedData($data) {
		// Check which values all prepared products have in common to preselect the values.
		$preSelected = array(
			'ConditionType' => null,
			'ShippingTime' => null,
			'Location' => null,
			'Comment' => null,
			'PictureUrl' => null,
			'MarketplaceCategories' => null,
		);

		$defaults = array(
			'ConditionType' => getDBConfigValue($this->marketplace.'.itemcondition', $this->mpID),
			'ShippingTime' => getDBConfigValue($this->marketplace.'.shippingtime', $this->mpID),
			'Location' => getDBConfigValue($this->marketplace.'.itemcountry', $this->mpID),
			'Comment' => null,
			'PictureUrl' => null,
			'MarketplaceCategories' => null,
		);

		foreach ($data as $row) {
			foreach ($preSelected as $field => $collection) {
				$preSelected[$field][] = isset($row[$field]) ? $row[$field] : null;
			}
		}

		foreach ($preSelected as $field => $collection) {
			$collection = array_unique($collection);
			if (count($collection) == 1) {
				$preSelected[$field] = array_shift($collection);
				if (($preSelected[$field] === null) && isset($defaults[$field])) {
					$preSelected[$field] = $defaults[$field];
				}
			} else {
				$preSelected[$field] = isset($defaults[$field])
					? $defaults[$field]
					: null;
			}
		}

		return $preSelected;
	}
	
	protected function getSelection() {
		$sLanguageId = getDBConfigValue($this->marketplace . '.lang', $this->mpID);
		$keytypeIsArtNr = (getDBConfigValue('general.keytype', '0') == 'artNr');

		$dbOldSelectionQuery = '
			SELECT *
			FROM ' . TABLE_MAGNA_HITMEISTER_PREPARE . ' dp
		';
		
		if ($keytypeIsArtNr) {
			$dbOldSelectionQuery .= '
				INNER JOIN ' . TABLE_PRODUCTS . ' p ON dp.products_model = p.products_model
				INNER JOIN ' . TABLE_MAGNA_SELECTION . ' ms ON p.products_id = ms.pID AND dp.mpID = ms.mpID
			';
		} else {
			$dbOldSelectionQuery .= '
				INNER JOIN ' . TABLE_MAGNA_SELECTION . ' ms ON dp.products_id = ms.pID AND dp.mpID = ms.mpID
			';
		}
		
		$dbOldSelectionQuery .='
		    WHERE selectionname = "apply"
				AND ms.mpID = "' . $this->mpID . '"
				AND session_id="' . session_id() . '"
				AND dp.products_id IS NOT NULL
				AND TRIM(dp.products_id) <> ""
				AND dp.PrepareType = "Apply"
		';
		$dbOldSelection = MagnaDB::gi()->fetchArray($dbOldSelectionQuery);
		$oldProducts = array();
		if (is_array($dbOldSelection)) {
			foreach ($dbOldSelection as $row) {
				$oldProducts[] = MagnaDB::gi()->escape($keytypeIsArtNr ? $row['products_model'] : $row['products_id']);
			}
		}

		# Daten fuer properties Tabelle
		# die Namen schon fuer diese Tabelle
		# products_short_description nicht bei OsC, nur bei xtC, Gambio und Klonen
		$dbNewSelectionQuery = '
			SELECT	ms.mpID mpID,
					p.products_id,
					p.products_model,
					p.products_image as PictureUrl,
					pd.products_name as Title,
					pd.products_short_description as Subtitle,
					pd.products_description as Description
			FROM ' . TABLE_PRODUCTS . ' p
			INNER JOIN ' . TABLE_MAGNA_SELECTION . ' ms ON ms.pID = p.products_id
			LEFT JOIN ' . TABLE_PRODUCTS_DESCRIPTION . ' pd ON pd.products_id = p.products_id AND pd.language_id = "' . $sLanguageId . '"
			WHERE '.($keytypeIsArtNr ? 'p.products_model' : 'p.products_id').' NOT IN ("' . implode('", "', $oldProducts) . '")
				AND ms.mpID = "' . $this->mpID . '"
				AND selectionname="apply"
				AND session_id="' . session_id() . '"
		';
		$dbNewSelection = MagnaDB::gi()->fetchArray($dbNewSelectionQuery);
		
		$dbSelection = array_merge(
			is_array($dbOldSelection) ? $dbOldSelection : array(),
			is_array($dbNewSelection) ? $dbNewSelection : array()
		);
		
		// For debug purpose
		if (false) {
			echo '<span id="shMlDebug">X</span>';
			echo '<div id="mlDebug">';
			
			echo print_m("dbOldSelectionQuery == \n$dbOldSelectionQuery\n");
			echo print_m($dbOldSelection, '$dbOldSelection');

			echo print_m("dbNewSelectionQuery == \n$dbNewSelectionQuery\n");
			echo print_m($dbNewSelection, '$dbNewSelection');
			
			echo print_m($dbSelection, '$dbSelectionMerged');
			echo '</div>';
			ob_start();
			?>
			<script type="text/javascript">/*<![CDATA[*/
				$('#mlDebug').fadeOut(0);
				$('#shMlDebug').on('click', function() {
					$('#mlDebug:visible').fadeOut();
					$('#mlDebug:hidden').fadeIn();
				});
			/*]]>*/</script>
			<?php
			$content = ob_get_contents();
			ob_end_clean();
			echo $content;
		}

		return $dbSelection;
	}
	
	private function renderCatAttributes($category, $itemID) {	
		$html = '<tr><td colspan="2">' . ML_HITMEISTER_CATEGORY_ATTRIBUTES . '</td></tr>';
		if (empty($category['attributes'])) {
			$html .= '<th>' . ML_HITMEISTER_CATEGORY_NO_ATTRIBUTES . '</th>';
		} else {
			$values = $this->getValuesFromPrepare($category['id_category'], $itemID);
			$oddEven = false;
			foreach ($category['attributes'] as $attribute) {
				$data = isset($values[$attribute['name']]) ? $values[$attribute['name']] : '';
				$class = ($oddEven = !$oddEven) ? 'odd' : 'even';
				if ($attribute['name'] === 'additional_categories') {
					$html = $this->renderAdditionalCategories($html, $data, $attribute, $class);
				} else {
					if ($attribute['is_multiple_allowed']) {
						if (empty($data)) {
							$data = array( 0 => '');
						}

						$lastValue = end($data);
						$lastKey = key($data);

						foreach ($data as $key => $value) {
							$disabled = $lastKey === $key ? '' : 'disabled';
							$minusButton = $key === 0 ? '' : '<input id="' . $attribute['name'] . '_' . $key . '" type="button" value="-" class="ml-button minus"/>';
							$html .= '<tr class="' . $class . '">
										<th id="' . $attribute['name'] . '_' . $key . '_th">' . fixHTMLUTF8Entities($attribute['title'], ENT_COMPAT, 'UTF-8') .(($attribute['mandatory']) ? '<span class="bull">&bull;</span>' : ''). '</th>
										<td class="input">
											<input type="text" class="fullwidth" name="catAttributes[' . $attribute['name'] . '][values][]" id="' . $attribute['name'] . '_' . $key . '" value="' . $value . '">
										</td>
										<td style="width: 100px">
											<input id="' . $attribute['name'] . '_' . $key . '" type="button" value="+" class="ml-button plus" ' . $disabled . '/>
											' . $minusButton . '
										</td>
										<input id="' . $attribute['name'] . '_' . $key . '" type="hidden" name="catAttributes[' . $attribute['name'] . '][required]" value="' . $attribute['mandatory'] . '"/>
									 </tr>
									';
						}
					} else {
						if ($attribute['name'] === 'weight' || $attribute['name'] === 'content_volume') {
							if (1 < count($this->getSelection())) {
								$html .= '<tr class="' . $class . '">
											<td class="input">
												<input type="hidden" name="catAttributes[' . $attribute['name'] . '][values]" id="' . $attribute['name'] . '" value="">
											</td>
											<input id="' . $attribute['name'] . '" type="hidden" name="catAttributes[' . $attribute['name'] . '][required]" value="' . $attribute['mandatory'] . '"/>
										 </tr>
										';
								continue;
							} elseif (empty($data)) {
								if ($attribute['name'] === 'weight') {
									$data = HitmeisterHelper::GetWeightFromShop($itemID);
								} else {
									$data = HitmeisterHelper::GetContentVolumeFromShop($itemID);
								}
							}
						}

						$html .= '<tr class="' . $class . '">
									<th id="' . $attribute['name'] . '_th">' . fixHTMLUTF8Entities($attribute['title'], ENT_COMPAT, 'UTF-8') .(($attribute['mandatory']) ? '<span class="bull">&bull;</span>' : ''). '</th>
									<td class="input">
										<input type="text" class="fullwidth" name="catAttributes[' . $attribute['name'] . '][values]" id="' . $attribute['name'] . '" value="' . $data . '">
									</td>
									<input id="' . $attribute['name'] . '" type="hidden" name="catAttributes[' . $attribute['name'] . '][required]" value="' . $attribute['mandatory'] . '"/>
								 </tr>
								';
					}
				}
			}
		}
		
		return $html;
	}

	private function getValuesFromPrepare($catID, $itemID) {
		$result = false;
		if (isset($itemID)) {
			$result = MagnaDB::gi()->fetchOne('
				SELECT CategoryAttributes 
				FROM ' . TABLE_MAGNA_HITMEISTER_PREPARE . '
				WHERE products_id = "' . $itemID . '" 
					AND MarketplaceCategories = "' . $catID . '"
			');
		}
		
		if ($result !== false) {
			return json_decode($result, true);
		} else {
			$result = MagnaDB::gi()->fetchOne('
				SELECT CategoryAttributes 
				FROM ' . TABLE_MAGNA_HITMEISTER_PREPARE . '
				WHERE MarketplaceCategories = "' . $catID . '"
			');
			
			if ($result !== false) {
				return json_decode($result, true);
			}
		}
		
		return null;
	}
	
	private function renderAdditionalCategories($html, $data, $attribute, $class) {
		if (empty($data)) {
			$data = array( 0 => '');
		}
		
		$this->initCatMatching();
		end($data);
		$lastKey = key($data);
		
		foreach ($data as $key => $value) {
			$disabled = $lastKey === $key ? '' : 'style="display: none"';
			$minusButton = $key === 0 ? '' : '<input id="' . $attribute['name'] . '_' . $key . '" type="button" value="-" class="ml-button minus"/>';
			$html .= '<tr class="' . $class . '">
						<th id="' . $attribute['name'] . '_' . $key . '_th">' . fixHTMLUTF8Entities($attribute['title'], ENT_COMPAT, 'UTF-8') . '</th>
						<td class="input">'
						. $this->getMatchingBoxHTMLAdditionalCategories($attribute, $key, $value, $class) .
						'</td>
						<td style="width: 100px">
							<input id="' . $attribute['name'] . '_' . $key . '" type="button" value="+" class="ml-button plus" ' . $disabled . '/>
							' . $minusButton . '
						</td>
						<input id="' . $attribute['name'] . '_' . $key . '" type="hidden" name="catAttributes[' . $attribute['name'] . '][required]" value="' . $attribute['mandatory'] . '"/>
					 </tr>
					';
		}
		
		return $html;
	}
	
	protected function getMatchingBoxHTMLAdditionalCategories($attribute, $key, $value, $class) {
		$html = '
			<table class="matchingTable" style="width:100%;"><tbody>
				<tr class="' . $class . '">
					<td><div class="catVisual" id="mpCategoryVisualAdditional_'.$key.'">'.utf8_decode(urldecode($value)).'</div></td>
					<td class="buttons">
						<input type="hidden" id="mpCategoryAdditional_'.$key.'" name="mpCategoryAdditional_'.$key.'" value=""/>
						<input type="hidden" name="catAttributes['.$attribute['name'].'][values][]" id="'.$attribute['name'].'_'.$key.'" value="'.fixHTMLUTF8Entities($value, ENT_COMPAT, 'UTF-8').'"/>
						<input class="fullWidth ml-button smallmargin" type="button" value="'.ML_LABEL_CHOOSE.'" id="selectMPCategoryAdditional_'.$key.'"/>
					</td>
				</tr>
			</tbody></table>
		';
		
		ob_start();
		/*
		TABLE_MAGNA_COMPAT_CATEGORIES
		TABLE_MAGNA_COMPAT_CATEGORYMATCHING
		*/
		?>
		<script type="text/javascript">/*<![CDATA[*/
			$(document).ready(function() {
				$('#selectMPCategoryAdditional_<?php echo $key ?>').click(function() {
					mpCategorySelector.startCategorySelector(function(cID) {
						$('#mpCategoryAdditional_<?php echo $key ?>').val(cID).trigger('change');
						mpCategorySelector.getCategoryPath($('#mpCategoryVisualAdditional_<?php echo $key ?>'));
						$('#<?php echo $attribute['name'] . '_' . $key ?>').val(encodeURIComponent($('#mpCategoryVisualAdditional_<?php echo $key ?>').html()));
					}, 'mp');
				});
			});
		/*]]>*/</script>
		<?php
		
		$html .= ob_get_contents();	
		ob_end_clean();

		return $html;
	}
	
}
