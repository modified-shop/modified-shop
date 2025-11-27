<?php
/*
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
 * (c) 2010 - 2023 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_MODULES.'amazon/AmazonHelper.php');

function renderFlat($data, $prefix = '') {
	$finalArray = array();
	foreach ($data as $key => $value) {
		$newKey = empty($prefix) ? $key : $prefix . '[' . $key . ']';
		if (is_array($value)) {
			$finalArray = array_merge($finalArray, renderFlat($value, $newKey));
		} else {
			$finalArray[$newKey] = $value;
		}
	}
	return $finalArray;
}

function getBrowseNodes($category, $selectedNodes = array(), $newStyle = 'ALL') {
	try {
		$browseNodes = MagnaConnector::gi()->submitRequest(array(
			'ACTION' => 'GetBrowseNodes',
			'CATEGORY' => $category,
			'NewResponse' => $newStyle,
            'Version' => 2
		));

        $browseNodes = isset($browseNodes['DATA']) ? $browseNodes['DATA'] : array();
	} catch (MagnaException $e) {
	}

    // to ensure that its an array
    if (!is_array($selectedNodes)) {
        $selectedNodes = array();
    }

	$html = renderAmazonTopTen('topBrowseNode', array($category));
	$html .= '<optgroup label="' . fixHTMLUTF8Entities(ML_AMAZON_LABEL_APPLY_BROWSENODES) . '">';
	foreach ($browseNodes as $nodeID => $nodeName) {
		$selected = in_array($nodeID, $selectedNodes) ? 'selected="selected"' : '';
		$html .= '
			<option value="' . $nodeID . '" ' . $selected . '>'.str_replace(
				array('\\/',  '/',        '#\\#'),
				array('#\\#', ' &rarr; ', '/'   ),
				fixHTMLUTF8Entities($nodeName)
			).'</option>';
	}
     $html .= '</optgroup>';
	return $html;
}

function checkCondition(&$attributes, $selected = false) {
	global $conditionStatus;
	$html = '';
	if (!empty($attributes['Attributes']) && array_key_exists('ConditionType', $attributes['Attributes'])) {
		global $_MagnaSession;
		$selected = ($selected && !empty($selected)) ? $selected : getDBConfigValue('amazon.itemCondition', $_MagnaSession['mpID'], false);
		$mapConditionAttributes = $attributes['Attributes']['ConditionType']['values'];
		unset($attributes['Attributes']['ConditionType']);
		$html = '';
		foreach ($mapConditionAttributes as $conditions_key => $conditions_val) {
			$html .= '<option value="' . $conditions_key . '" ' . (($selected == $conditions_key) ? 'selected' : '') . '>' . fixHTMLUTF8Entities($conditions_val) . '</option>';
		}
		$attributes['ConditionType'] = $html;
		$conditionStatus = true;
	} else {
		$attributes['ConditionType'] = false;
	}

	return $html;
}

function convertAttrArrayToHTML($data, $usrData = array()) {
	if (!is_array($data) || empty($data)) return '';
	$attr = array();

	foreach ($data as $key => &$def) {
		$usrValue = isset($usrData[$key]) ? fixHTMLUTF8Entities($usrData[$key]) : '';
		#echo var_dump_pre($usrValue, $key);
		$def['type'] = isset($def['type']) ? $def['type'] : 'text';
		$def['desc'] = isset($def['desc']) ? $def['desc'] : '';

		switch ($def['type']) {
			case 'select': {
				$html = '<select name="Attributes[' . $key . ']" class="fullWidth">' . "\n";
				foreach ($def['values'] as $vk => $vv) {
					$vv = fixHTMLUTF8Entities($vv);
					$vk = fixHTMLUTF8Entities($vk);
					$selected = ($vk == $usrValue);
					$html .= '    <option value="' . $vk . '"' . ($selected ? 'selected="selected"' : '') . '>' . $vv . '</option>' . "\n";
				}
				$html .= '</select><br/>' . "\n";
				break;
			}
			default: {
				$html = '<input type="text" value="' . $usrValue . '" name="Attributes[' . $key . ']">' . "\n";
				break;
			}
		}
		$def['html'] = $html;
	}

	$htmlAA = '<table class="attrTable"><tbody>';
	$rowC = 0;
	$maxRowC = count($data) - 1;
	foreach ($data as $a) {
		$class = array();
		if ($rowC == 0) $class[] = 'first';
		if ($rowC == $maxRowC) $class[] = 'last';
		$htmlAA .= '<tr class="' . implode(' ', $class) . '">
			<td class="key">' . fixHTMLUTF8Entities($a['title']) . ': </td>
			<td class="input">' . $a['html'] . '</td>
			<td class="info">' . (isset($a['desc']) ? str_replace("\n", "<br>\n", fixHTMLUTF8Entities($a['desc'])) : '') . '</td>
		</tr>';
		++$rowC;
	}
	$htmlAA .= '</tbody></table>';
	return $htmlAA;
}

function renderMultiApplication($data) {
	global $_url, $applyAction, $_MagnaSession;

	$categories = array('DATA' => array());
	try {
		$categories = MagnaConnector::gi()->submitRequest(array(
			'ACTION' => 'GetAllProductTypes',
		));
	} catch (MagnaException $e) {
		//echo print_m($e->getErrorArray(), 'Error: '.$e->getMessage(), true);
	}
    $htmlCategories = renderAmazonTopTen('topMainCategory');
	$htmlCategories .= '<optgroup label="' . ML_LABEL_CATEGORY . '">';
	if (!empty($categories['DATA'])) {
		foreach ($categories['DATA'] as $catKey => $catName) {
			$htmlCategories .= '
				<option value="' . $catKey . '">' . fixHTMLUTF8Entities($catName) . '</option>';
		}
	}
	if (($data['MainCategory'] != '') && ($data['MainCategory'] != 'null')) {
		$htmlCategories = str_replace(
			'<option value="' . $data['MainCategory'] . '">',
			'<option value="' . $data['MainCategory'] . '" selected="selected">',
			$htmlCategories
		);
	}
    $htmlCategories .= '</optgroup>';
	if (($data['MainCategory'] != '') && ($data['MainCategory'] != 'null')|| !empty($data['Attributes'])) {
		$mNewResponse = 'ALL';
		if (isset($data['BrowseNodes'][0]) && !empty($data['BrowseNodes'][0])) {
			preg_match("/([0-9]*)__([0-9]*)__([0-9]*)/", $data['BrowseNodes'][0], $aOutput);
			if (!empty($aOutput)) {
                $mNewResponse = 'ALL';
            } else {
                preg_match("/([0-9]*)__([0-9]*)/", $data['BrowseNodes'][0], $aOutput);
                if (!empty($aOutput)) {
                    $mNewResponse = true;
                } else {
                    $mNewResponse = false;
                }
            }
		}
		$browseNodes = getBrowseNodes($data['MainCategory'], $data['BrowseNodes'], $mNewResponse);
	} else {
		$browseNodes = '';
	}

	if (!empty($browseNodes)) $browseNodesStyle = '';
	else $browseNodesStyle = 'display: none';

    // Load variation themes if MainCategory is set
    $variationThemes = array();
    $variationDesignStyle = 'display: none';
    $variationThemeOptions = '<option value="">Please select...</option>';
    $selectedVariationTheme = isset($data['VariationTheme']) ? $data['VariationTheme'] : '';

    if (($data['MainCategory'] != '') && ($data['MainCategory'] != 'null')) {
        try {
            $result = MagnaConnector::gi()->submitRequest(array(
                    'ACTION' => 'GetCategoryDetails',
                    'DATA'   => array(
                            'PRODUCTTYPE' => $data['MainCategory'],
                            'INCLUDE_CONDITIONAL_RULES' => true
                    ),
            ));
            if (isset($result['DATA']['variation_details']) && !empty($result['DATA']['variation_details'])) {
                $variationThemes = $result['DATA']['variation_details'];
                $variationDesignStyle = ''; // Show the row

                foreach ($variationThemes as $themeCode => $themeData) {
                    $selected = ($selectedVariationTheme === $themeCode) ? 'selected="selected"' : '';
                    $themeName = isset($themeData['name']) ? fixHTMLUTF8Entities($themeData['name']) : $themeCode;
                    $variationThemeOptions .= '<option value="' . $themeCode . '" ' . $selected . '>' . $themeName . '</option>';
                }
            }
        } catch (MagnaException $e) {
            // If API fails, just hide the dropdown
        }
    }

	$html = '
		<tbody id="variationMatcher" class="attributesTable">
			<tr class="headline">
				<td colspan="3"><h4>' . ML_LABEL_CATEGORY . '</h4></td>
			</tr>
			<tr class="odd">
				<th>' . ML_LABEL_MAINCATEGORY . ' <span>&bull;</span></th>
				<td class="input">
					<select name="MainCategory" id="maincat" class="fullWidth">
						' . $htmlCategories . '
					</select>
				</td>
				<td class="info">&nbsp;</td>
			</tr>
			<tr class="even" id="variation-design-row" style="' . $variationDesignStyle . '">
				<th>' . ML_AMAZON_LABEL_VARIATION_THEME . ' <span>&bull;</span></th>
				<td class="input">
					<select name="VariationTheme" id="variation-design" style="width: 100%">
						' . $variationThemeOptions . '
					</select>
				</td>
				<td class="info">&nbsp;</td>
			</tr>
			<tr class="odd" style="'.$browseNodesStyle.'">
				<th>' . ML_AMAZON_LABEL_APPLY_BROWSENODES . ' <span>&bull;</span></th>
				<td class="input" id="browsenodes">
					<select multiple name="BrowseNodes[]" id="browsenode" style="width: 100%">
						' . $browseNodes . '
					</select>
				</td>
				<td class="info">&nbsp;</td>
			</tr>
			<tr class="spacer">
				<td colspan="3">&nbsp;';

	ob_start();
	?>
	<script type="text/javascript">/*<![CDATA[*/
		function loadBrowseNodes(mainCat) {
            mlShowLoading();
			jQuery.ajax({
				type: 'POST',
				url: '<?php echo toURL($_url, array('kind' => 'ajax', 'applyAction' => $applyAction, 'ts' => time()), true);?>',
				dataType: 'html',
				data: {
					'type': 'browsenodes',
					'category': mainCat,
					'selected': $('#browsenodes select').val()
				},
				success: function (data) {
					$('#browsenodes select').html(data);
                    toggleBrowseNodesRow(true);
                    mlHideLoading();
				},
				error: function (xhr, status, error) {
					if ($('#browsenodes select option').length <= 1) {
						$('#browsenodes select').html('<option value="null"><?php echo ML_AMAZON_LABEL_APPLY_SELECT_MAIN_SUB_CAT_FIRST; ?></option>');
					}
					myConsole.log(arguments);
                    mlHideLoading();
				}
			});
		}

        // Function to conditionally show row based on some condition
        function toggleBrowseNodesRow(show) {
            $('#browsenodes').closest('tr').toggle(show);
        }



        $(document).ready(function () {
            // Single select options
            var singleSelectOptions = {
                width: 'resolve',
                placeholder: '<?php echo addslashes(html_entity_decode((ML_AMAZON_LABEL_APPLY_PLEASE_SELECT))); ?>',
            };

            // Multiselect options
            var multiSelectOptions = {
                width: 'resolve',
                multiple: true,
                allowClear: true,
                minimumResultsForSearch: Infinity,
                placeholder: '<?php echo addslashes(html_entity_decode((ML_AMAZON_LABEL_APPLY_PLEASE_SELECT))); ?>'
            };

            $('#maincat').select2(singleSelectOptions);
            $('#browsenode').select2(multiSelectOptions);

			$('#maincat').change(function () {
				if ($(this).val() == 'null') {
                    toggleBrowseNodesRow(false);
					if ($('#browsenodes select option').length <= 1) {
						$('#browsenodes select').html('<option value="null"><?php echo ML_AMAZON_LABEL_APPLY_SELECT_MAIN_SUB_CAT_FIRST; ?></option>');
					}
					$('#additionalAttributes').css({'display': 'none'});
					$('#additionalAttributes td.input').html('');
                    // Hide variation-design dropdown
                    $('#variation-design-row').hide();
				} else {
                    loadBrowseNodes($(this).val());
                    // Load variation themes
                    loadVariationDesign($(this).val());
                }
			});

            // Function to load variation themes (variation-design) for selected category
            function loadVariationDesign(categoryId) {
                $.ajax({
                    type: 'POST',
                    url: '<?php echo toURL($_url, array('kind'        => 'ajax',
                                                        'applyAction' => $applyAction,
                                                        'ts'          => time()
                    ), true);?>',
                    dataType: 'json',
                    data: {
                        'type': 'variationthemes',
                        'category': categoryId
                    },
                    success: function (data) {
                        if (data && data.success && data.variation_details) {
                            var variationDetails = data.variation_details;
                            var $variationDesign = $('#variation-design');

                            // Clear existing options
                            $variationDesign.html('<option value="">Please select...</option>');

                            // Add variation theme options
                            var hasThemes = false;
                            for (var themeCode in variationDetails) {
                                if (variationDetails.hasOwnProperty(themeCode)) {
                                    var theme = variationDetails[themeCode];
                                    $variationDesign.append(
                                        '<option value="' + themeCode + '">' + theme.name + '</option>'
                                    );
                                    hasThemes = true;
                                }
                            }

                            // Show/hide row based on whether themes exist
                            if (hasThemes) {
                                $('#variation-design-row').show();
                                // Initialize select2 if not already initialized
                                if (!$variationDesign.data('select2')) {
                                    $variationDesign.select2({
                                        width: '100%'
                                    });
                                }
                            } else {
                                $('#variation-design-row').hide();
                            }
                        } else {
                            // No variation themes available
                            $('#variation-design-row').hide();
                        }
                    },
                    error: function () {
                        // Hide on error
                        $('#variation-design-row').hide();
                    }
                });
            }
		});
		/*]]>*/</script><?php
	$html .= ob_get_contents();
	ob_end_clean();
	$html .= '
				</td>
			</tr>
		</tbody>';

	$html .= '
		<tbody id="tbodyDynamicMatchingHeadline" style="display:none;">
			<tr class="headline">
				<td colspan="1"><h4>' . str_replace('%marketplace%', ucfirst($_MagnaSession['currentPlatform']), ML_GENERAL_VARMATCH_MP_ATTRIBUTE) . '</h4></td>
				<td colspan="2"><h4>' . ML_GENERAL_VARMATCH_MY_WEBSHOP_ATTRIB .' </h4></td>
			</tr>
		</tbody>
		<tbody id="tbodyDynamicMatchingInput" style="display:none;">
			<tr>
				<th></th>
				<td class="input">' . ML_GENERAL_VARMATCH_SELECT_CATEGORY . '</td>
				<td class="info"></td>
			</tr>
			<tr class="spacer">
				<td colspan="3">&nbsp;</td>
			</tr>
		</tbody>
		<tbody id="tbodyDynamicMatchingOptionalHeadline" style="display:none;">
           <tr class="headline">
               <td colspan="1"><h4>' . str_replace('%marketplace%', ucfirst($_MagnaSession['currentPlatform']), ML_GENERAL_VARMATCH_MP_OPTIONAL_ATTRIBUTE) . '</h4></td>
              <td colspan="2"><h4>' . ML_GENERAL_VARMATCH_MY_WEBSHOP_ATTRIB .' </h4></td>
           </tr>
      </tbody>
      <tbody id="tbodyDynamicMatchingOptionalInput" style="display:none;">
      	<tr>
      		<th></th>
            <td class="input">' . ML_GENERAL_VARMATCH_SELECT_CATEGORY . '</td>
            <td class="info"></td>
        </tr>
        <tr class="spacer">
        	<td colspan="3">&nbsp;</td>
		</tr>
	  </tbody>
	  <tbody id="tbodyDynamicMatchingCustomHeadline" style="display:none;">
           <tr class="headline">
               <td colspan="1"><h4>' . str_replace('%marketplace%', ucfirst($_MagnaSession['currentPlatform']), ML_GENERAL_VARMATCH_MP_CUSTOM_ATTRIBUTE) . '</h4></td>
              <td colspan="2"><h4>' . ML_GENERAL_VARMATCH_MY_WEBSHOP_ATTRIB .' </h4></td>
           </tr>
      </tbody>
      <tbody id="tbodyDynamicMatchingCustomInput" style="display:none;">
      	<tr>
      		<th></th>
            <td class="input">' . ML_GENERAL_VARMATCH_SELECT_CATEGORY . '</td>
            <td class="info"></td>
        </tr>
        <tr class="spacer">
        	<td colspan="3">&nbsp;</td>
		</tr>
	  </tbody>
		';

	return $html;
}

function renderSingleApplication($data) {
	global $_MagnaSession;
	$productImagesHTML = '';
	if (!empty($data['Images'])) {
		foreach ($data['Images'] as $img => $checked) {
			$productImagesHTML .= '
				<table class="imageBox"><tbody>
					<tr><td class="image"><label for="image_' . $img . '">' . generateProductCategoryThumb($img, 60, 60) . '</label></td></tr>
					<tr><td class="cb"><input type="checkbox" id="image_' . $img . '" name="Images[' . $img . ']" value="true" ' . (($checked == 'true') ? 'checked="checked"' : '') . '/></td></tr>
				</tbody></table>';
		}
	}
	if (empty($productImagesHTML)) {
		$productImagesHTML = '&nbsp;';
	}

	if (getDBConfigValue('amazon.site', $_MagnaSession['mpID']) !== 'US') {
		$eanHtml = '
		<tr class="odd">
				<th>' . ML_GENERIC_EAN . ' <span>&bull;</span></th>
				<td class="input"><input class="fullwidth" type="text" name="EAN" value="' . fixHTMLUTF8Entities($data['EAN']) . '"/></td>
				<td class="info">' . ML_AMAZON_TEXT_APPLY_REQUIERD_EAN . '</td>
			</tr>
		';
	} else {
		$eanHtml = '';
	}

	$charset = (isset($_SESSION['magna']['language_charset']) && (stripos($_SESSION['magna']['language_charset'], 'utf') !== false)) ? 'UTF-8' : 'ISO-8859-1';
	$html = '
		<tbody>
			<tr class="headline">
				<td colspan="3"><h4>' . ML_LABEL_DETAILS . '</h4></td>
			</tr>
			<tr class="odd">
				<th>' . ML_LABEL_PRODUCT_NAME . ' <span>&bull;</span></th>
				<td class="input"><input class="fullwidth" type="text" name="ItemTitle" value="' . fixHTMLUTF8Entities($data['ItemTitle'], ENT_QUOTES) . '"/></td>
				<td class="info">&nbsp;</td>
			</tr>
			<tr class="even">
				<th>' . ML_GENERIC_MANUFACTURER_NAME . ' <span>&bull;</span></th>
				<td class="input"><input class="fullwidth" type="text" name="Manufacturer" value="' . fixHTMLUTF8Entities($data['Manufacturer']) . '"/></td>
				<td class="info">' . ML_AMAZON_TEXT_APPLY_MANUFACTURER_NAME . '</td>
			</tr>
			<tr class="odd">
				<th>' . ML_LABEL_BRAND . ' <span>&bull;</span></th>
				<td class="input"><input class="fullwidth" type="text" name="Brand" value="' . fixHTMLUTF8Entities($data['Brand']) . '"/></td>
				<td class="info">' . ML_AMAZON_TEXT_APPLY_BRAND . '</td>
			</tr>
			<tr class="even">
				<th>' . ML_GENERIC_MANUFACTURER_PARTNO . '</th>
				<td class="input"><input class="fullwidth" type="text" name="ManufacturerPartNumber" value="' . fixHTMLUTF8Entities($data['ManufacturerPartNumber']) . '"/></td>
				<td class="info">' . ML_AMAZON_TEXT_APPLY_MANUFACTURER_PARTNO . '</td>
			</tr>
			' . $eanHtml . '
			<tr class="spacer">
				<td colspan="3">&nbsp;</td>
			</tr>
			<tr class="headline">
				<td colspan="3"><h4>' . ML_AMAZON_LABEL_APPLY_ADDITIONAL_DETAILS . '</h4></td>
			</tr>
			<tr class="odd">
				<th>' . ML_LABEL_PRODUCTS_IMAGES . '</th>
				<td class="input">' . $productImagesHTML . '</td>
				<td class="info">' . ML_AMAZON_TEXT_APPLY_PRODUCTS_IMAGES . '</td>
			</tr>
			<tr class="even">
				<th>' . ML_AMAZON_LABEL_APPLY_BULLETPOINTS . '</th>
				<td class="input">
				    <input type="text" class="fullwidth" name="BulletPoints[0]" value="' . fixHTMLUTF8Entities($data['BulletPoints'][0]) . '"/><br/>
				    <input type="text" class="fullwidth" name="BulletPoints[1]" value="' . fixHTMLUTF8Entities($data['BulletPoints'][1]) . '"/><br/>
				    <input type="text" class="fullwidth" name="BulletPoints[2]" value="' . fixHTMLUTF8Entities($data['BulletPoints'][2]) . '"/><br/>
				    <input type="text" class="fullwidth" name="BulletPoints[3]" value="' . fixHTMLUTF8Entities($data['BulletPoints'][3]) . '"/><br/>
				    <input type="text"class="fullwidth"  name="BulletPoints[4]" value="' . fixHTMLUTF8Entities($data['BulletPoints'][4]) . '"/><br/></td>
				<td class="info">' . ML_AMAZON_TEXT_APPLY_BULLETPOINTS . '</td>
			</tr>
			<tr class="odd">
				<th>' . ML_GENERIC_PRODUCTDESCRIPTION . '</th>
				<td class="input"><textarea class="fullwidth" name="Description" rows="10">' .
		fixHTMLUTF8Entities(amazonSanitizeDesc($data['Description'])) .
		'</textarea></td>
		<td class="info">' . ML_AMAZON_TEXT_APPLY_PRODUCTDESCRIPTION . '</td>
			</tr>
			<tr class="even">
				<th><table class="nostyle actions">
						<tr>
							<td>' . ML_AMAZON_LABEL_APPLY_KEYWORDS . '</td>
							<td style="width: 20px;">
								<div class="desc" title="<?php echo ML_LABEL_INFO ?>">
									<span style="display: none">' . ML_AMAZON_INFO_APPLY_KEYWORDS . '</span>
								</div>
							</td>
						</tr>
					</table></th>
				<td class="input">
				    <div>
                        <input class="ml-always-from-web-shop" id="ml-keywords-always-from-web-shop" name="Keywords__FromWebShop" type="checkbox" '.($data['Keywords'] === null ? 'checked=checked' : '').'">
                        <label style="color:black;" for="ml-keywords-always-from-web-shop">
                            '.ML_AMAZON_FROMWEBSHOP_APPLY_KEYWORDS.'
                        </label>
                    </div>
				    <input type="text" '.($data['Keywords'] === null ? 'disabled="true"' : '').' class="fullwidth" name="Keywords" value="'.fixHTMLUTF8Entities($data['Keywords']).'"/></td>
				<td class="info">'.ML_AMAZON_TEXT_APPLY_KEYWORDS.'</td>
			</tr>
			<tr class="spacer">
				<td colspan="3">&nbsp;</td>
			</tr>
		</tbody>';
	return $html;
}

function renderB2B($data) {
	global $_MagnaSession;
	if (getDBConfigValue('amazon.b2b.active', $_MagnaSession['mpID'], 'false') === 'false') {
		$data['B2BActive'] = 'false';
		$data['B2BDisableActivation'] = true;
	}

	$enabled = isset($data['B2BActive']) ? $data['B2BActive'] : 'false';
	$disableActivation = isset($data['B2BDisableActivation']) ? $data['B2BDisableActivation'] : false;
	ob_start();
	?>
		<tbody>
			<tr class="headline">
				<td colspan="3"><h4><?php echo ML_AMAZON_B2B_TITLE ?></h4></td>
			</tr>
			<tr class="odd">
				<th>
					<table class="nostyle actions">
						<tr>
							<td><?php echo ML_AMAZON_B2B_ACTIVATE ?></td>
							<td style="width: 20px;">
								<div class="desc" title="<?php echo ML_LABEL_INFO ?>">
									<span style="display: none"><?php echo ML_AMAZON_B2B_ACTIVATE_INFO ?></span>
								</div>
							</td>
						</tr>
					</table>
				</th>
				<td class="input">
					<input type="radio" value="true" id="B2BActiveTrue" name="B2BActive"
						<?php echo $enabled === 'true' ? 'checked' : '';?>>
					<label for="B2BActiveTrue"><?php echo ML_BUTTON_LABEL_YES ?></label>
					<input type="radio" value="false" id="B2BActiveFalse" name="B2BActive"
						<?php echo $enabled === 'false' ? 'checked' : '';?>>
					<label for="B2BActiveFalse"><?php echo ML_BUTTON_LABEL_NO ?></label>
				</td>
				<td class="info"> </td>
			</tr>
			<tr class="even">
				<th>
					<table class="nostyle actions">
						<tr>
							<td><?php echo ML_AMAZON_B2B_SELL_TO ?></td>
							<td style="width: 20px;">
								<div class="desc" title="<?php echo ML_LABEL_INFO ?>">
									<span style="display: none"><?php echo ML_AMAZON_B2B_SELL_TO_INFO ?></span>
								</div>
							</td>
						</tr>
					</table>
				</th>
				<td class="input">
					<select name="B2BSellTo" class="fullWidth js-b2b" title="<?php echo ML_AMAZON_B2B_SELL_TO ?>">
						<option value="b2b_b2c" <?php echo isset($data['B2BSellTo']) && $data['B2BSellTo'] === 'b2b_b2c' ? 'selected' : '';?>><?php echo ML_AMAZON_B2B_SELL_TO_ALL ?></option>
						<option value="b2b_only" <?php echo isset($data['B2BSellTo']) && $data['B2BSellTo'] === 'b2b_only' ? 'selected' : '';?>><?php echo ML_AMAZON_B2B_SELL_TO_B2B ?></option>
					</select>
				</td>
				<td class="info"> </td>
			</tr>
			<tr class="odd">
				<th>
					<table class="nostyle actions">
						<tr>
							<td><?php echo ML_AMAZON_B2B_QUANTITY_DISCOUNT_TYPE ?></td>
							<td style="width: 20px;">
								<div class="desc" title="<?php echo ML_LABEL_INFO ?>">
									<span style="display: none"><?php echo ML_AMAZON_B2B_QUANTITY_DISCOUNT_TYPE_INFO ?></span>
								</div>
							</td>
						</tr>
					</table>
				</th>
				<td class="input">
					<select name="QuantityPriceType" id="QuantityPriceType" class="fullWidth js-b2b"
							title="<?php echo ML_AMAZON_B2B_QUANTITY_DISCOUNT_TYPE ?>">
						<?php $quantityPriceType = isset($data['QuantityPriceType']) ? $data['QuantityPriceType'] : '' ?>
						<option value="" <?php echo $quantityPriceType === '' ? 'selected' : '';?>><?php
							echo ML_AMAZON_B2B_QUANTITY_DISCOUNT_DO_NOT_USE ?></option>
						<option value="percent" <?php echo $quantityPriceType === 'percent' ? 'selected' : '';?>><?php
							echo ML_AMAZON_B2B_QUANTITY_DISCOUNT_PERCENT ?></option>
						<option value="fixed" <?php echo $quantityPriceType === 'fixed' ? 'selected' : '';?>><?php
							echo ML_AMAZON_B2B_QUANTITY_DISCOUNT_FIXED ?></option>
					</select>
				</td>
				<td class="info">&nbsp;</td>
			</tr>
			<tr class="even">
				<th><?php echo ML_AMAZON_B2B_QUANTITY_DISCOUNT_TIER ?> 1</th>
				<td class="input">
					<label for="QuantityLowerBound1"><?php echo ML_LABEL_QUANTITY ?></label>:&nbsp;
					<input type="text" class="autoWidth rightSpacer js-b2b js-b2b-tier" id="QuantityLowerBound1"
						   name="QuantityLowerBound1"
						   value="<?php echo isset($data['QuantityLowerBound1']) ? $data['QuantityLowerBound1'] : ''; ?>">
					<label for="QuantityPrice1"><?php echo ML_LABEL_ORDER_TOTAL_DISCOUNT ?></label>:&nbsp;
					<input type="text" class="autoWidth rightSpacer js-b2b js-b2b-tier" id="QuantityPrice1"
						   name="QuantityPrice1"
						   value="<?php echo isset($data['QuantityLowerBound1']) ? $data['QuantityLowerBound1'] : ''; ?>">
				</td>
				<td class="info">&nbsp;</td>
			</tr>
			<tr class="odd">
				<th><?php echo ML_AMAZON_B2B_QUANTITY_DISCOUNT_TIER ?> 2</th>
				<td class="input">
					<label for="QuantityLowerBound2"><?php echo ML_LABEL_QUANTITY ?></label>:&nbsp;
					<input type="text" class="autoWidth rightSpacer js-b2b js-b2b-tier" id="QuantityLowerBound2"
						   name="QuantityLowerBound2"
						   value="<?php echo isset($data['QuantityLowerBound2']) ? $data['QuantityLowerBound2'] : ''; ?>">
					<label for="QuantityPrice2"><?php echo ML_LABEL_ORDER_TOTAL_DISCOUNT ?></label>:&nbsp;
					<input type="text" class="autoWidth rightSpacer js-b2b js-b2b-tier" id="QuantityPrice2"
						   name="QuantityPrice2"
						   value="<?php echo isset($data['QuantityPrice2']) ? $data['QuantityPrice2'] : ''; ?>">
				</td>
				<td class="info">&nbsp;</td>
			</tr>
			<tr class="even">
				<th><?php echo ML_AMAZON_B2B_QUANTITY_DISCOUNT_TIER ?> 3</th>
				<td class="input">
					<label for="QuantityLowerBound3"><?php echo ML_LABEL_QUANTITY ?></label>:&nbsp;
					<input type="text" class="autoWidth rightSpacer js-b2b js-b2b-tier" id="QuantityLowerBound3"
						   name="QuantityLowerBound3"
						   value="<?php echo isset($data['QuantityLowerBound3']) ? $data['QuantityLowerBound3'] : ''; ?>">
					<label for="QuantityPrice3"><?php echo ML_LABEL_ORDER_TOTAL_DISCOUNT ?></label>:&nbsp;
					<input type="text" class="autoWidth rightSpacer js-b2b js-b2b-tier" id="QuantityPrice3"
						   name="QuantityPrice3"
						   value="<?php echo isset($data['QuantityPrice3']) ? $data['QuantityPrice3'] : ''; ?>">
				</td>
				<td class="info">&nbsp;</td>
			</tr>
			<tr class="odd">
				<th><?php echo ML_AMAZON_B2B_QUANTITY_DISCOUNT_TIER ?> 4</th>
				<td class="input">
					<label for="QuantityLowerBound4"><?php echo ML_LABEL_QUANTITY ?></label>:&nbsp;
					<input type="text" class="autoWidth rightSpacer js-b2b js-b2b-tier" id="QuantityLowerBound4"
						   name="QuantityLowerBound4"
						   value="<?php echo isset($data['QuantityLowerBound4']) ? $data['QuantityLowerBound4'] : ''; ?>">
					<label for="QuantityPrice4"><?php echo ML_LABEL_ORDER_TOTAL_DISCOUNT ?></label>:&nbsp;
					<input type="text" class="autoWidth rightSpacer js-b2b js-b2b-tier" id="QuantityPrice4"
						   name="QuantityPrice4"
						   value="<?php echo isset($data['QuantityPrice4']) ? $data['QuantityPrice4'] : ''; ?>">
				</td>
				<td class="info">&nbsp;</td>
			</tr>
			<tr class="even">
				<th><?php echo ML_AMAZON_B2B_QUANTITY_DISCOUNT_TIER ?> 5</th>
				<td class="input">
					<label for="QuantityLowerBound5"><?php echo ML_LABEL_QUANTITY ?></label>:&nbsp;
					<input type="text" class="autoWidth rightSpacer js-b2b js-b2b-tier" id="QuantityLowerBound5"
						   name="QuantityLowerBound5"
						   value="<?php echo isset($data['QuantityLowerBound5']) ? $data['QuantityLowerBound5'] : ''; ?>">
					<label for="QuantityPrice5"><?php echo ML_LABEL_ORDER_TOTAL_DISCOUNT ?></label>:&nbsp;
					<input type="text" class="autoWidth rightSpacer js-b2b js-b2b-tier" id="QuantityPrice5"
						   name="QuantityPrice5"
						   value="<?php echo isset($data['QuantityPrice5']) ? $data['QuantityPrice5'] : ''; ?>">
				</td>
				<td class="info">&nbsp;</td>
			</tr>
			<tr class="spacer">
				<td colspan="3">&nbsp;</td>
			</tr>
		</tbody>
	<script>
		if (window['jQuery']) {
			(function ($) {
				function enableB2B(enable, cls) {
					$(cls).parent().find('input, select').prop('disabled', !enable);
				}

				function showMessage(message) {
					$('<div class="ml-modal dialog2" title="<?php echo ML_LABEL_INFORMATION ?>"></div>').html(message)
						.jDialog({
							width: (message.length > 1000) ? '700px' : '500px'
						});
				}

				$('#B2BActiveTrue').click(function() {
					<?php if (!$disableActivation) { ?>
					enableB2B(true, '.js-b2b');
					showMessage('<?php echo addslashes(ML_AMAZON_B2B_ACTIVATE_NOTIFICATION) ?>');
					$('#QuantityPriceType').change();
					<?php } else { ?>
					showMessage('<?php echo addslashes(ML_AMAZON_B2B_ACTIVATE_DISABLED_NOTIFICATION) ?>');
					$('#B2BActiveFalse').click();
					<?php } ?>
				});
				$('#B2BActiveFalse').click(function() {
					enableB2B(false, '.js-b2b');
				});

				$('#QuantityPriceType').change(function() {
					enableB2B($(this).val() !== '', '.js-b2b-tier');
				}).change();

				<?php if ($enabled === 'false') { ?>
				enableB2B(false, '.js-b2b');
				<?php } ?>

				$('table.actions div.desc').click(function () {
					var d = $(this).find('span').html();
					$('<div class="dialog2" title="<?php echo ML_LABEL_INFORMATION ?>"></div>').html(d)
						.jDialog({'width': (d.length > 1000) ? '700px' : '500px'});
				});
			})(jQuery);
		}
	</script>
	<?php
	$html = ob_get_clean();

	return $html;
}

function renderGenericApplication($data) {
	global $_MagnaSession;
    $opts = array(
        '-' => ML_AMAZON_SHIPPING_TIME_DEFAULT_VALUE,
        '0' => ML_AMAZON_SHIPPING_TIME_SAMEDAY_VALUE
    );
    for ($i = 1; $i < 31; $i++) {
        $opts[$i.''] = $i;
    }

	$html = '
		<tbody>
			<tr class="headline">
				<td colspan="3"><h4>' . ML_LABEL_GENERIC_SETTINGS . '</h4></td>
			</tr>
			<tr class="odd">
				<th>' . ML_AMAZON_LABEL_LEADTIME_TO_SHIP . '</th>
				<td class="input">
					<select class="fullWidth" name="LeadtimeToShip">';
	$usrValue = $data['LeadtimeToShip'];
	foreach ($opts as $vk => $vv) {
		$html .= '
						<option value="' . $vk . '"' . (($vk.'' === $usrValue.'') ? 'selected="selected"' : '') . '>' . $vv . '</option>' . "\n";
	}
	$html .= '"
					</select>
				</td>
				<td class="info">&nbsp;</td>
			</tr>';
	//.print_m($data);
	
	if(isset($data['ShippingTemplate'])){
		$aTemplates = getDBConfigValue(array($_MagnaSession['currentPlatform'] . '.shipping.template', 'values'), $_MagnaSession['mpID']);
		$html .= '
			<tr class="even">
				<th>' . ML_AMAZON_SHIPPING_TEMPLATE . '</th>
				<td class="input">
					<select class="fullWidth" name="ShippingTemplate">';
		
				foreach ($aTemplates as $key => $name) {
					$html .= '
						<option value="' . $key . '"' . (($data['ShippingTemplate'] == $key) ? 'selected="selected"' : '') . '>' . $name . '</option>' . "\n";
				}
				$html .= '"
					</select>
				</td>
				<td class="info">'.ML_AMAZON_SHIPPING_TEMPLATE_PREPARE_INFO.'</td>
			</tr>';
	}
	$html .= 
			'<tr class="spacer">
				<td colspan="3">&nbsp;</td>
			</tr>
		</tbody>';
	return $html;
}

$conditionStatus = false;
if (isset($_GET['kind']) && ($_GET['kind'] == 'ajax')) {
	if (isset($_POST['type']) && ($_POST['type'] == 'LoadCustomIdentifiers') && isset($_POST['SelectValue'])) {
		die(json_encode(AmazonHelper::gi()->getCustomIdentifiers($_POST['SelectValue'])));
	}
	if (isset($_POST['type']) && ($_POST['type'] == 'browsenodes') && isset($_POST['category'])) {
        $mNewResponse = 'ALL';
		if (isset($_POST['selected']) && !empty($_POST['selected']) && $_POST['selected'] != 'null') {
            preg_match("/([0-9]*)__([0-9]*)__([0-9]*)/", $_POST['selected'], $aOutput);
            if (!empty($aOutput)) {
                $mNewResponse = 'ALL';
            } else {
                preg_match("/([0-9]*)__([0-9]*)/", $_POST['selected'], $aOutput);
                if (!empty($aOutput)) {
                    $mNewResponse = true;
                } else {
                    $mNewResponse = false;
                }
            }
		}
		die(getBrowseNodes($_POST['category'], $_POST['selected'], $mNewResponse));
	}
    if (isset($_POST['type']) && ($_POST['type'] == 'variationthemes') && isset($_POST['category'])) {
        global $_MagnaSession;
        try {
            $result = MagnaConnector::gi()->submitRequest(array(
                    'ACTION' => 'GetCategoryDetails',
                    'DATA'   => array(
                            'PRODUCTTYPE' => $_POST['category'],
                            'INCLUDE_CONDITIONAL_RULES' => true
                    ),
                    //                'MARKETPLACEID' => $_MagnaSession['mpID'],
                    //				'PrimaryCategory' => $_POST['category']
            ));

            if (isset($result['DATA']['variation_details'])) {
                die(json_encode(array(
                        'success'           => true,
                        'variation_details' => $result['DATA']['variation_details']
                )));
            } else {
                // Return the full result for debugging
                die(json_encode(array(
                        'success'           => false,
                        'variation_details' => array(),
                        'debug_response'    => $result
                )));
            }
        } catch (MagnaException $e) {
            die(json_encode(array('success' => false, 'error' => $e->getMessage())));
        }
    }
	if (isset($_POST['type']) && ($_POST['type'] == 'resetToDefaults') && isset($_POST['pID']) && ctype_digit($_POST['pID'])) {
		$pID = $_POST['pID'];

		$delWhere = array();
		if (getDBConfigValue('general.keytype', '0') == 'artNr') {
			$pModel = MagnaDB::gi()->fetchOne('
				SELECT products_model FROM ' . TABLE_PRODUCTS . ' p WHERE p.products_id = \'' . $pID . '\'
			');
			if (!empty($pModel)) {
				$delWhere['products_model'] = $pModel;
			}
		} else {
			$delWhere['pID'] = $pID;
		}
		if (!empty($delWhere)) {
			$delWhere['mpID'] = $_MagnaSession['mpID'];
			MagnaDB::gi()->delete(TABLE_MAGNA_AMAZON_APPLY, $delWhere);
		}

		$dataReset = populateGenericData($pID);
		$dataReset = renderFlat($dataReset);
		arrayEntitiesToUTF8($dataReset);
		$dataReset['Description'] = html_entity_decode($dataReset['Description'], ENT_COMPAT, 'UTF-8');
		die(json_encode($dataReset));
	}

    // React attribute matching save handler (supports both V2 and V3 formats)
    // V2 format: $_POST['type'] = 'saveAttributeMatching'
    // V3 format: $_POST['ml']['action'] = 'saveAttributeMatching'
    if ((isset($_POST['type']) && $_POST['type'] == 'saveAttributeMatching') || (isset($_POST['ml']['action']) && $_POST['ml']['action'] == 'saveAttributeMatching')) {
        require_once(DIR_MAGNALISTER_MODULES . 'amazon/application/applicationviews_react.php');
        handleSaveAttributeMatching();
        exit;
    }

    // React attribute matching batch save handler
    // V3 format: $_POST['ml']['action'] = 'saveAttributeMatchingBatch'
    if (isset($_POST['ml']['action']) && $_POST['ml']['action'] == 'saveAttributeMatchingBatch') {
        require_once(DIR_MAGNALISTER_MODULES . 'amazon/application/applicationviews_react.php');
        handleSaveAttributeMatchingBatch();
        exit;
    }

    // React component data reload handler (for category changes)
    if (isset($_POST['type']) && ($_POST['type'] == 'getReactComponentData')) {
        require_once(DIR_MAGNALISTER_MODULES . 'amazon/application/applicationviews_react.php');
        handleGetReactComponentData();
        exit;
    }

	die();
}

echo '<h2>' . (($applyAction == 'multiapplication') ? ML_AMAZON_LABEL_APPLY_MULTI : ML_AMAZON_LABEL_APPLY_SINGLE) . '</h2>';
if ($applyAction != 'multiapplication') {
	$pID = MagnaDB::gi()->fetchOne('
		SELECT pID FROM ' . TABLE_MAGNA_SELECTION . '
		 WHERE mpID=\'' . $_MagnaSession['mpID'] . '\' AND
		       selectionname=\'' . $applySetting['selectionName'] . '\' AND
		       session_id=\'' . session_id() . '\'
		 LIMIT 1
	');
	$data = populateGenericData($pID, true);
} else {
	$multiEdit = MagnaDB::gi()->fetchOne(eecho('
		SELECT pID
		  FROM ' . TABLE_MAGNA_SELECTION . ' s, ' . TABLE_MAGNA_AMAZON_APPLY . ' a
		 WHERE s. mpID=\'' . $_MagnaSession['mpID'] . '\'
		       AND s.selectionname=\'' . $applySetting['selectionName'] . '\'
		       AND s.session_id=\'' . session_id() . '\'
		       AND s.mpID = a.mpID
		       AND s.pID = a.products_id
		 LIMIT 1
	', false)) === false ? false : true;
	$data = populateGenericData(0, $multiEdit);
}

// Load React integration
require_once(DIR_MAGNALISTER_MODULES . 'amazon/application/applicationviews_react.php');
try {

    // Use React component
    echo '
<form name="apply" method="post" action="' . toURL($_url) . '">
	<input type="hidden" name="saveApplyData" value="true"/>
	<p>' . ML_AMAZON_TEXT_APPLY_REQUIERD_FIELDS . '</p>
	<table class="attributesTable">
		' . renderMultiApplication($data) . '
		' . renderReactVariationMatching($pID, $data) . '
		' . (($applyAction != 'multiapplication') ? renderSingleApplication($data) : '') . '
		' . renderB2B($data) . '
		' . renderGenericApplication($data) . '
	</table>
	<table class="actions">
		<thead><tr><th>' . ML_LABEL_ACTIONS . '</th></tr></thead>
		<tbody>
			<tr class="firstChild"><td>
				<table><tbody><tr>
					<td class="firstChild">' . (($applyAction == 'singleapplication') ? '<input id="resetToDefaults" class="ml-button" type="button" value="' . ML_BUTTON_LABEL_REVERT . '"/>' : '') . '</td>
					<td class="lastChild">' . '<input class="ml-button mlbtn-action" type="button" value="' . ML_BUTTON_LABEL_SAVE_DATA . '"/>' . '</td>
				</tr></tbody></table>
			</td></tr>
		</tbody>
	</table>
</form>
';

} catch (\Throwable $ex) {
    echo $ex->getMessage() . "\n" . $ex->getFile() . ':' . $ex->getLine();
}
if ($applyAction != 'multiapplication') {
?>
	<script type="text/javascript">/*<![CDATA[*/
		$(document).ready(function () {
			$('#resetToDefaults').click(function () {
				$.blockUI(blockUILoading);
				$.ajax({
					type: 'POST',
					url: '<?php echo toURL($_url, array('kind' => 'ajax', 'applyAction' => $applyAction, 'ts' => time()), true);?>',
					dataType: 'json',
					data: {
						'type': 'resetToDefaults',
						'pID': <?php echo $pID; ?>
					},
					success: function (data) {
						$('#maincat').val('null');
						$('#subcat').html('<option value="null"><?php echo ML_AMAZON_LABEL_APPLY_SELECT_MAIN_CAT_FIRST; ?></option>').css({'display': 'block'});
						$('#browsenodes select').html('<option value="null"><?php echo ML_AMAZON_LABEL_APPLY_SELECT_MAIN_SUB_CAT_FIRST; ?></option>');
						myConsole.log(data);
						if (is_object(data)) {
							for (var k in data) {
								var v = data[k];
								var e = $('[name="' + k + '"]');
								if (e.attr('type') == 'checkbox') {
									if (v == "false") {
										e.removeAttr('checked');
									} else {
										e.attr('checked', 'checked');
									}
								} else {
									e.val(v);
								}
							}
						}
						$.unblockUI();
					},
					error: function (xhr, status, error) {
						myConsole.log(arguments);
						$.unblockUI();
					}
				});
			});
			$('.ml-always-from-web-shop').change(function (){
                var self = $(this);
                if(self.is(':checked')){
                    console.log(self.parent().parent().children('input'));
                    self.parent().parent().children('input').prop( "disabled", true );
                    self.parent().parent().children('input').css( "color", '#818181' );
                    self.parent().parent().children('input').css( "background-color", '#d3d3d3' );
                } else{
                    self.parent().parent().children('input').prop( "disabled", false );
                    self.parent().parent().children('input').css( "color", 'black' );
                    self.parent().parent().children('input').css( "background-color", 'white' );
                }
            });
            $('.ml-always-from-web-shop').change();
		});
	/*]]>*/</script>
<?php
}
