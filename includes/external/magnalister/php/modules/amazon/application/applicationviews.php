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
 * $Id: applicationviews.php 6382 2016-01-19 12:31:41Z tim.neumann $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

function renderFlat($data, $prefix = '') {
	$finalArray = array();
	foreach ($data as $key => $value) {
		$newKey = (empty($prefix)) ? $key : $prefix . '[' . $key . ']';
		if (is_array($value)) {
			$finalArray = array_merge($finalArray, renderFlat($value, $newKey));
		} else {
			$finalArray[$newKey] = $value;
		}
	}
	return $finalArray;
}


function renderAmazonTopTen($sField, $aConfig = array()) {
	global $_MagnaSession;
	require_once (DIR_MAGNALISTER_MODULES.DIRECTORY_SEPARATOR.'amazon'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'amazonTopTen.php');
	$oTopTen = new amazonTopTen();
	$oTopTen->setMarketPlaceId($_MagnaSession['mpID']);
	$aTopTen = $oTopTen->getTopTenCategories($sField, $aConfig);
	if (empty($aTopTen)) {
		return '';
	}
	$sOut = '<optgroup label="' . ML_TOPTEN_TEXT . '">';
	foreach ($aTopTen as $sKey => $sValue) {
		$sOut .= '<option value="' . $sKey . '">' . fixHTMLUTF8Entities($sValue) . '</option>';
	}
	$sOut .= '</optgroup>';
	return $sOut;
}

function getProductTypesAndAttributes($category) {
	try {
		$result = MagnaConnector::gi()->submitRequest(array(
			'ACTION' => 'GetProductTypesAndAttributes',
			'CATEGORY' => $category
		));
		$result = $result['DATA'];
	} catch (MagnaException $e) {
		$result = array(
			'ProductTypes' => array('null' => ML_AMAZON_ERROR_APPLY_CANNOT_FETCH_SUBCATS),
			'Attributes' => false
		);
	}

	if ($result['ProductTypes'] !== false) {
		$html = '';
		foreach ($result['ProductTypes'] as $key => $value) {
			$html .= '
				<option value="' . $key . '">' . $value . '</option>';
		}
		$result['ProductTypes'] = $html;
	}
	return $result;
}

function getBrowseNodes($category, $subcategory) {
	try {
		$browseNodes = MagnaConnector::gi()->submitRequest(array(
			'ACTION' => 'GetBrowseNodes',
			'CATEGORY' => $category,
			'SUBCATEGORY' => $subcategory
		));
		$browseNodes = $browseNodes['DATA'];
	} catch (MagnaException $e) {
	}
	if (!isset($browseNodes) || empty($browseNodes)) {
		$browseNodes = array('null' => ML_AMAZON_LABEL_APPLY_SELECT_MAIN_SUB_CAT_FIRST);
	}
	$html = '
			<option value="null">' . ML_AMAZON_LABEL_APPLY_BROWSENODE_NOT_SELECTED . '</option>';
	$html .= renderAmazonTopTen('topBrowseNode', array($category));
	foreach ($browseNodes as $nodeID => $nodeName) {
		$html .= '
			<option value="' . $nodeID . '">' . str_replace(
				array('\\/', '/', '#\\#'),
				array('#\\#', ' &rarr; ', '/'),
				fixHTMLUTF8Entities($nodeName)
			) . '</option>';
	}
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

	/*
		try {
			$conditions = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'GetConditionTypes',
				'SUBSYSTEM' => 'Amazon',
				'MARKETPLACEID' => $_MagnaSession['mpID'],
			));
			$conditions = $conditions['DATA'];
		} catch (MagnaException $e) {
		}
		$html = '';
		foreach($conditions as $conditions_key=>$conditions_val){
			$html .= '<option '.((getDBConfigValue('amazon.itemCondition', $_MagnaSession['mpID'], false)==$conditions_key)?'selected':'').'>'.$conditions_val.'</option>';
		}
	*/
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
	global $_url, $applyAction, $conditionHtml;

	$categories = array('DATA' => array());
	try {
		$categories = MagnaConnector::gi()->submitRequest(array(
			'ACTION' => 'GetMainCategories',
		));
	} catch (MagnaException $e) {
		//echo print_m($e->getErrorArray(), 'Error: '.$e->getMessage(), true);
	}
	$htmlCategories = '<option value="null">' . ML_AMAZON_LABEL_APPLY_PLEASE_SELECT . '</option>';
	$tmpCats = array('null' => ML_AMAZON_LABEL_APPLY_PLEASE_SELECT);
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
		$cna = getProductTypesAndAttributes($data['MainCategory']);
		$conditionHtml = checkCondition($cna, $data['ConditionType']);
		$htmlSubCategories = $cna['ProductTypes'];
		$htmlAdditionalAttributes = $cna['Attributes'];

	} else {
		$htmlSubCategories = '<option value="null">' . ML_AMAZON_LABEL_APPLY_SELECT_MAIN_CAT_FIRST . '</option>';
		$htmlAdditionalAttributes = false;
	}

	if (($data['MainCategory'] != '') && ($data['MainCategory'] != 'null')
		&& (array_key_exists('ProductType', $data) || !empty($data['Attributes']))
	) {
		if (array_key_exists('ProductType', $data) && ($data['ProductType'] != '')
			&& ($data['ProductType'] != 'null') && ($data['ProductType'] != false)
		) {
			$htmlSubCategories = str_replace(
				'<option value="' . $data['ProductType'] . '">',
				'<option value="' . $data['ProductType'] . '" selected="selected">',
				$htmlSubCategories
			);
		} else {
			$data['ProductType'] = false;
		}
		$browseNodes = getBrowseNodes($data['MainCategory'], $data['ProductType']);
		$browseNodes = array(
			0 => $browseNodes,
			1 => $browseNodes,
		);
		for ($i = 0; $i < 2; ++$i) {
			if (isset($data['BrowseNodes'][$i]) && $data['BrowseNodes'][$i] != '') {
				$browseNodes[$i] = str_replace(
					'<option value="' . $data['BrowseNodes'][$i] . '">',
					'<option value="' . $data['BrowseNodes'][$i] . '" selected="selected">',
					$browseNodes[$i]
				);
			}
		}
	} else {
		$browseNodes = '<option value="null">' . ML_AMAZON_LABEL_APPLY_SELECT_MAIN_SUB_CAT_FIRST . '</option>';
		$browseNodes = array(
			0 => $browseNodes,
			1 => $browseNodes,
		);
	}

	$htmlAdditionalAttributes = convertAttrArrayToHTML($htmlAdditionalAttributes, $data['Attributes']);

	$html = '
		<tbody>
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
			<tr id="subCategory" class="even" ' . (empty($htmlSubCategories) ? 'style="display:none;"' : '') . '>
				<th>' . ML_LABEL_SUBCATEGORY . ' <span>&bull;</span></th>
				<td class="input">
					<select name="ProductType" id="subcat" class="fullWidth">
						' . $htmlSubCategories . '
					</select>
				</td>
				<td class="info">&nbsp;</td>
			</tr>
			<tr id="additionalAttributes" class="even" ' . (empty($htmlAdditionalAttributes) ? 'style="display:none;"' : '') . '>
				<th>' . ML_AMAZON_LABEL_APPLY_ATTRIBUTES . '</th>
				<td class="input" colspan="2">
					' . $htmlAdditionalAttributes . '
				</td>
			</tr>
			<tr class="odd">
				<th>' . ML_AMAZON_LABEL_APPLY_BROWSENODES . ' <span>&bull;</span></th>
				<td class="input" id="browsenodes">
					<select name="BrowseNodes[]" id="browsenode" class="fullWidth">
						' . $browseNodes[0] . '
					</select>
				</td>
				<td class="info">&nbsp;</td>
			</tr>
			<tr class="spacer">
				<td colspan="3">&nbsp;';

	ob_start();
	?>
	<script type="text/javascript">/*<![CDATA[*/
		function loadBrowseNodes(subCat) {
			jQuery.blockUI(blockUILoading);
			jQuery.ajax({
				type: 'POST',
				url: '<?php echo toURL($_url, array('kind' => 'ajax', 'applyAction' => $applyAction, 'ts' => time()), true);?>',
				dataType: 'html',
				data: {
					'type': 'browsenodes',
					'category': $('#maincat').val(),
					'subcategory': subCat
				},
				success: function (data) {
					$('#browsenodes select').html(data);
					jQuery.unblockUI();
				},
				error: function (xhr, status, error) {
					$('#browsenodes select').html('<option value="null"><?php echo ML_AMAZON_LABEL_APPLY_SELECT_MAIN_SUB_CAT_FIRST; ?></option>');
					$('#subcat').val('null');
					myConsole.log(arguments);
					jQuery.unblockUI();
				}
			});
		}

		function loadProductTypesAndAttributes(mainCategory) {
			jQuery.blockUI(blockUILoading);
			jQuery.ajax({
				type: 'POST',
				url: '<?php echo toURL($_url, array('kind' => 'ajax', 'applyAction' => $applyAction, 'ts' => time()), true);?>',
				dataType: 'json',
				data: {
					'type': 'subcategories',
					'category': mainCategory
				},
				success: function (data) {
					if (data.ProductTypes == false) {
						$('#subCategory').css({'display': 'none'});
						$('#subcat').html('');
					} else {
						$('#subCategory').css({'display': 'table-row'});
						$('#subcat').html(data.ProductTypes)
					}
					subcatVal = $('#subcat').val();
					if ((subcatVal == null) || (subcatVal == '') || (subcatVal == 'null')) {
						loadBrowseNodes(false);
					} else {
						loadBrowseNodes(subcatVal);
					}
					if (data.Attributes == false) {
						$('#additionalAttributes').css({'display': 'none'});
						$('#additionalAttributes td.input').html('');
					} else {
						$('#additionalAttributes').css({'display': 'table-row'});
						$('#additionalAttributes > td.input').html(data.Attributes);
					}
					if (data.ConditionType == false) {
						$('.ArtikelConditions').css({'display': 'none'});
					} else {
						$('.ArtikelConditions').css({'display': 'table-row'});
						$('#condition_type').html(data.ConditionType);
					}
					//jQuery.unblockUI();
				},
				error: function (xhr, status, error) {
					$('#subcat').html('<option value="null"><?php echo ML_AMAZON_LABEL_APPLY_SELECT_MAIN_CAT_FIRST; ?></option>').css({'display': 'block'});
					$('#browsenodes select').html('<option value="null"><?php echo ML_AMAZON_LABEL_APPLY_SELECT_MAIN_SUB_CAT_FIRST; ?></option>');
					$('#maincat').val('null');
					myConsole.log(arguments);
					jQuery.unblockUI();
				}
			});
		}

		$(document).ready(function () {
			$('#maincat').change(function () {
				if ($(this).val() != 'null') {
					loadProductTypesAndAttributes($(this).val());
				} else {
					$('#subcat').html('<option value="null"><?php echo ML_AMAZON_LABEL_APPLY_SELECT_MAIN_CAT_FIRST; ?></option>').css({'display': 'block'});
					$('#browsenodes select').html('<option value="null"><?php echo ML_AMAZON_LABEL_APPLY_SELECT_MAIN_SUB_CAT_FIRST; ?></option>');
					$('#additionalAttributes').css({'display': 'none'});
					$('#additionalAttributes td.input').html('');
				}
			});
			$('#subcat').change(function () {
				if ($(this).val() != 'null') {
					loadBrowseNodes($(this).val());
				}
			});
		});
		/*]]>*/</script><?php
	$html .= ob_get_contents();
	ob_end_clean();
	$html .= '
				</td>
			</tr>
		</tbody>';

	return $html;
}

function renderSingleApplication($data) {
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

	$charset = (isset($_SESSION['language_charset']) && (stripos($_SESSION['language_charset'], 'utf') !== false)) ? 'UTF-8' : 'ISO-8859-1';
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
			<tr class="odd">
				<th>' . ML_GENERIC_EAN . ' <span>&bull;</span></th>
				<td class="input"><input class="fullwidth" type="text" name="EAN" value="' . fixHTMLUTF8Entities($data['EAN']) . '"/></td>
				<td class="info">' . ML_AMAZON_TEXT_APPLY_REQUIERD_EAN . '</td>
			</tr>
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
		html_entity_decode(fixHTMLUTF8Entities(amazonSanitizeDesc($data['Description'])), ENT_NOQUOTES, $charset) .
		'</textarea></td>
		<td class="info">' . ML_AMAZON_TEXT_APPLY_PRODUCTDESCRIPTION . '</td>
			</tr>
			<tr class="even">
				<th>' . ML_AMAZON_LABEL_APPLY_KEYWORDS . '</th>
				<td class="input">
				    <input type="text" class="fullwidth" name="Keywords[0]" value="' . fixHTMLUTF8Entities($data['Keywords'][0]) . '"/><br/>
				    <input type="text" class="fullwidth" name="Keywords[1]" value="' . fixHTMLUTF8Entities($data['Keywords'][1]) . '"/><br/>
				    <input type="text" class="fullwidth" name="Keywords[2]" value="' . fixHTMLUTF8Entities($data['Keywords'][2]) . '"/><br/>
				    <input type="text" class="fullwidth" name="Keywords[3]" value="' . fixHTMLUTF8Entities($data['Keywords'][3]) . '"/><br/>
				    <input type="text" class="fullwidth" name="Keywords[4]" value="' . fixHTMLUTF8Entities($data['Keywords'][4]) . '"/><br/></td>
				<td class="info">' . ML_AMAZON_TEXT_APPLY_KEYWORDS . '</td>
			</tr>
			<tr class="spacer">
				<td colspan="3">&nbsp;</td>
			</tr>
		</tbody>';
	return $html;
}

function renderGenericApplication($data) {
	global $conditionStatus, $conditionHtml;
	$opts = array_merge(array(
		'0' => '&mdash;',
		'X' => ML_LABEL_DO_NOT_CHANGE,
	), range(1, 30));

	$html = '
		<tbody>
			<tr class="headline">
				<td colspan="3"><h4>' . ML_LABEL_GENERIC_SETTINGS . '</h4></td>
			</tr>
			<tr class="odd">
				<th>' . ML_GENERIC_SHIPPING_TIME . '</th>
				<td class="input">
					<select class="fullWidth" name="LeadtimeToShip">';
	$usrValue = $data['LeadtimeToShip'];
	foreach ($opts as $vk => $vv) {
		$html .= '
						<option value="' . $vk . '"' . (($vk == $usrValue) ? 'selected="selected"' : '') . '>' . $vv . '</option>' . "\n";
	}
	$html .= '"
					</select>
				</td>
				<td class="info">&nbsp;</td>
			</tr>';
//    if(!empty($tmpData) && array_key_exists('ConditionType', $tmpData)){
	$html .= '
			<tr class="odd ArtikelConditions" style="display: ' . (($conditionStatus) ? 'table-row' : 'none') . ';">
				<th>' . ML_GENERIC_CONDITION . '</th>
				<td class="input"><select class="fullWidth" id="condition_type" name="ConditionType">' . (($conditionStatus) ? $conditionHtml : '') . '</select></td>
				<td class="info"></td>
			</tr>
			<tr class="odd ArtikelConditions" style="display:' . (($conditionStatus) ? 'table-row' : 'none') . ';">
				<th>' . ML_GENERIC_CONDITION_NOTE . '</th>
				<td class="input"><textarea class="fullWidth" rows="10" id="condition_note" name="ConditionNote">' . (($conditionStatus) ? $data['ConditionNote'] : '') . '</textarea></td>
				<td class="info"></td>
			</tr>';
//    }
	$html .= '
			<tr class="spacer">
				<td colspan="3">&nbsp;</td>
			</tr>
		</tbody>';
	//.print_m($data);
	return $html;
}

$conditionStatus = false;
$conditionHtml = '';
if (isset($_GET['kind']) && ($_GET['kind'] == 'ajax')) {
	if (isset($_POST['type']) && ($_POST['type'] == 'subcategories') && isset($_POST['category'])) {
		$caa = getProductTypesAndAttributes($_POST['category']);
		checkCondition($caa);
		$caa['Attributes'] = convertAttrArrayToHTML($caa['Attributes']);
		die(json_encode($caa));
	}
	if (isset($_POST['type']) && ($_POST['type'] == 'browsenodes') && isset($_POST['category']) && isset($_POST['subcategory'])) {
		die(getBrowseNodes($_POST['category'], $_POST['subcategory']));
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

echo '
<form name="apply" method="post" action="' . toURL($_url) . '">
	<input type="hidden" name="saveApplyData" value="true"/>
	<p>' . ML_AMAZON_TEXT_APPLY_REQUIERD_FIELDS . '</p>
	<table class="attributesTable">
		' . renderMultiApplication($data) . '
		' . (($applyAction != 'multiapplication') ? (
	renderSingleApplication($data)
	) : '') . '
		' . renderGenericApplication($data) . '
	</table>
	<table class="actions">
		<thead><tr><th>' . ML_LABEL_ACTIONS . '</th></tr></thead>
		<tbody>
			<tr class="firstChild"><td>
				<table><tbody><tr>
					<td class="firstChild">' . (($applyAction == 'singleapplication')
		? '<input id="resetToDefaults" class="ml-button" type="button" value="' . ML_BUTTON_LABEL_REVERT . '"/>'
		: ''
	) . '</td>
					<td class="lastChild">' . '<input class="ml-button mlbtn-action" type="submit" value="' . ML_BUTTON_LABEL_SAVE_DATA . '"/>' . '</td>
				</tr></tbody></table>
			</td></tr>
		</tbody>
	</table>
</form>';
if ($applyAction != 'multiapplication') {
	?>
	<script type="text/javascript">/*<![CDATA[*/
		$(document).ready(function () {
			$('#resetToDefaults').click(function () {
				jQuery.blockUI(blockUILoading);
				jQuery.ajax({
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
						jQuery.unblockUI();
					},
					error: function (xhr, status, error) {
						myConsole.log(arguments);
						jQuery.unblockUI();
					}
				});
			});
		});
		/*]]>*/</script>
<?php
}
