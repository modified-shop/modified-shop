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
 * (c) 2010 - 2013 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
// äöüß

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

defined('TABLE_MAGNA_AYN24_VARIANTMATCHING') OR define('TABLE_MAGNA_AYN24_VARIANTMATCHING', 'magnalister_ayn24_variantmatching');

class Ayn24VariationMatching {
	protected $resources = array();
	
	protected $mpId = 0;
	protected $marketplace = '';
	
	protected $isAjax = false;
	
	protected $availableVariationConfigs = array();
	protected $availableCustomConfigs = array();
	
	protected $languageId = 0;
	protected $mpActionSelectRequest = 't:null';
	
	public function __construct(&$resources) {
		$this->resources = &$resources;
		
		$this->mpId = $this->resources['session']['mpID'];
		$this->marketplace = $this->resources['session']['currentPlatform'];
		
		$this->isAjax = isset($_GET['kind']) && ($_GET['kind'] == 'ajax');
		
		$this->languageId = getDBConfigValue($this->marketplace.'.keytype', $this->mpId, 2);
	}
	
	protected function loadAvailabeVariationGroups() {
		$this->availableVariationConfigs = Ayn24ApiConfigValues::gi()->getAvailableVariantConfigurations();
		
		$availableCustomConfigs = MagnaDB::gi()->fetchArray('
			SELECT CustomIdentifier, MpIdentifier
			  FROM '.TABLE_MAGNA_AYN24_VARIANTMATCHING.'
			 WHERE MpId = '.$this->mpId.'
			       AND CustomIdentifier<>""
		');
		if (!empty($availableCustomConfigs)) {
			foreach ($availableCustomConfigs as $cfg) {
				$this->availableCustomConfigs[$cfg['CustomIdentifier'].':'.($cfg['MpIdentifier']==''?'null':$cfg['MpIdentifier'])] = $this->umlautkeyDecode($cfg['CustomIdentifier']);
			}
			asort($this->availableCustomConfigs);
		}
		#echo print_m($this->availableCustomConfigs);
	}
	
	protected function umlautkeyProtect($str) {
		return base64_encode(stringToUTF8($str));
	}
	
	protected function umlautkeyDecode($str) {
		return base64_decode($str);
	}
	
	protected function renderJs() {
		ob_start();
		?>
			<script type="text/javascript" src="<?php echo DIR_MAGNALISTER_WS; ?>js/marketplaces/ayn24/variationmatching.js"></script>
			<script>
				$(document).ready(function(){
					jQuery('#matchingForm').ayn24variationmatching({
						urlPostfix : '&kind=ajax',
						i18n: <?php echo json_encode(array (
							'defineName' => ML_AYN24_VARMATCH_DEFINE_NAME,
							'ajaxError' => ML_AYN24_VARMATCH_AJAX_ERROR,
							'selectVariantGroup' => ML_AYN24_VARMATCH_SELECT_VARIANT_GROUP,
							'allAttributsMustDefined' => ML_AYN24_VARMATCH_ALL_ATTRIBS_MUST_BE_DEFINED,
							'pleaseSelect' => ML_AYN24_VARMATCH_PLEASE_SELECT,
							'shopValue' => ML_AYN24_VARMATCH_PLEASE_SELECT,
							'mpValue' => ML_AYN24_VARMATCH_MP_VALUE,
							'dontTransmit' => ML_AYN24_VARMATCH_DONT_TRANSMIT,
							'webShopAttribute' => ML_AYN24_VARMATCH_WEBSHOP_ATTRIB,
							'deleteCustomGroupButtonTitle' => ML_AYN24_VARMATCH_DELETE_CUSTOM_BTN_TITLE,
							'deleteCustomGroupButtonContent' => ML_AYN24_VARMATCH_DELETE_CUSTOM_BTN_CONTENT,
							'deleteCustomGroupButtonOk' => ML_AYN24_VARMATCH_DELETE_CUSTOM_BTN_OK,
							'deleteCustomGroupButtonCancel' => ML_AYN24_VARMATCH_DELETE_CUSTOM_BTN_CANCEL
						));?>,
						elements: {
							newGroupIdentifier: '#newGroupIdentifier',
							customVariationHeaderContainer: '#tbodyVariationConfigurationSelector',
							newCustomGroupContainer: '#newCustomGroup',
							mainSelectElement: '#mpActionSelect',
							matchingHeadline: '#tbodyDynamicMatchingHeadline',
							matchingInput: '#tbodyDynamicMatchingInput'
						},
						shopVariations : <?php echo json_encode($this->loadShopVariations()); ?>
					});
				});
			</script>
		<?php
		return ob_get_clean();
	}
	
	protected function renderCss() {
		ob_start();
		?>
		<style>
body.magna table#variationMatcher tr#mpVariationSelector td.input {
	white-space: nowrap;
}
body.magna table#variationMatcher tr#mpVariationSelector td.input div#newCustomGroup {
	display: inline;	
}
body.magna table#variationMatcher tbody tr th {
	min-width: 150px;
}
body.magna table#variationMatcher table.attrTable.matchingTable {
	margin-top: 10px;
	margin-bottom: 1.5em;
	margin-left: -6px;
	width: -moz-calc(100% + 12px);
	width: -webkit-calc(100% + 12px);
	width: calc(100% + 12px);
	border-bottom: 2px solid #888;
}
body.magna table#variationMatcher table.attrTable.matchingTable tr.headline td {
	font-weight: bold;
}
body.magna table#variationMatcher table.attrTable.matchingTable tr td {
	width: 50%;
}
body.magna table#variationMatcher table.attrTable.matchingTable tr td.input {
	border-right: none;	
}
		</style>
		<?php
		return ob_get_clean();
	}
	
	protected function renderMatchingTable() {
		ob_start();
		?>
			<form method="post" id="matchingForm" action="<?php echo toURL($this->resources['url'], array (), true); ?>">
				<table id="variationMatcher" class="attributesTable">
					<tbody>
						<tr class="headline">
							<td colspan="3"><h4>Variantengruppe von Ayn24 ausw&auml;hlen</h4></td>
						</tr>
						<tr id="mpVariationSelector">
							<th>Variantengruppe</th>
							<td class="input">
								<?php $sSelected=$this->mpActionSelectRequest; ?>
								<select name="mpActionSelect" id="mpActionSelect">
									<option value="t:null"<?php echo $sSelected=='t:null'?' selected="selected"':''?>>Bitte w&auml;hlen...</option>
									<optgroup label="Ayn24 Gruppen">
		<?php
		foreach ($this->availableVariationConfigs as $key => $grp) {
			if ($grp['IsFinal'] === false) {
				continue;
			}
			echo '
										<option value="mp:'.$this->umlautkeyProtect($key).'"'.($sSelected=='mp:'.$this->umlautkeyProtect($key)?' selected="selected"':'').'>'.$grp['Name'].'</option>';
		}
		?>
									</optgroup>
		<?php
		if (!empty($this->availableCustomConfigs)) {
			echo '
									<optgroup label="Eigene Gruppen">';
			foreach ($this->availableCustomConfigs as $key => $value) {
				echo '
										<option value="ct:'.$key.'"'.($sSelected=='ct:'.$key?' selected="selected"':'').'>'.fixHTMLUTF8Entities($value).'</option>';
			}
			echo '
									</optgroup>';
		}
		?>
									<option value="t:new">Eigene Gruppe anlegen</option>
								</select>
								<div id="newCustomGroup" style="display:none">
									<input id="isMatchingValid" type="hidden" name="ml[match][IsValid]" value="true">
									<input id="newGroupIdentifier" type="hidden" name="ml[match][MpIdentifier]">
									&nbsp;&nbsp;
									<input id="newCustomGroupIdentifier" type="text" name="ml[match][CustomIdentifier]" placeholder="Bezeichner">
									&nbsp;
									<input id="newCustomGroupSaveBtn" type="button" value="anlegen">
									<input id="customGroupDeleteBtn" type="submit" value="l&ouml;schen" name="ml[match][delete]" style="display:none">
								</div>
							</td>
							<td class="info"></td>
						</tr>
						<tr class="spacer">
							<td colspan="3">&nbsp;</td>
						</tr>
					</tbody>
					<tbody id="tbodyVariationConfigurationSelector" style="display:none;">
						<tr class="headline">
							<td colspan="3"><h4>Attributsnamen von Ayn24 ausw&auml;hlen</h4></td>
						</tr>
						<tr>
							<th>Attributsnamen</th>
							<td class="input">
								<select id="availableVariationConfigs">
									<option value="null" selected>Bitte w&auml;hlen...</option>
		<?php
		foreach ($this->availableVariationConfigs as $key => $grp) {
			if ($grp['IsFinal'] !== false) {
				continue;
			}
			echo '
									<option value="'.$this->umlautkeyProtect($key).'">'.$grp['Name'].'</option>';
		}
		?>
								</select>
							</td>
							<td class="info"></td>
						</tr>
						<tr class="spacer">
							<td colspan="3">&nbsp;</td>
						</tr>
					</tbody>
					<tbody id="tbodyDynamicMatchingHeadline" style="display:none;">
						<tr class="headline">
							<td colspan="1"><h4>Ayn24 Attribut</h4></td>
							<td colspan="2"><h4>Mein Web-Shop Attribut</h4></td>
						</tr>
					</tbody>
					<tbody id="tbodyDynamicMatchingInput" style="display:none;">
						<tr>
							<th></th>
							<td class="input">Bitte w&auml;hlen sie eine Variantengruppe.</td>
							<td class="info"></td>
						</tr>
					</tbody>
				</table>
				<br><br>
				<table class="actions">
					<thead><tr><th>Aktionen</th></tr></thead>
					<tbody>
						<tr class="firstChild"><td>
							<table><tbody><tr>
								<td class="firstChild"></td>
								<td></td>
								<td class="lastChild">
									<input id="saveMatching" class="ml-button" type="submit">
								</td>
							</tr></tbody></table>
						</td></tr>
					</tbody>
				</table>
			</form>
		<?php
		return ob_get_clean();
	}
	
	protected function loadShopVariations() {
		$groups = MagnaDB::gi()->fetchArray('
		    SELECT products_options_id AS Code, products_options_name AS Name
		      FROM '.TABLE_PRODUCTS_OPTIONS.'
		     WHERE language_id = "'.$this->languageId.'"
		  ORDER BY products_options_name ASC
		');
		if (empty($groups)) {
			return;
		}
		foreach ($groups as $k => &$g) {
			$values = MagnaDB::gi()->fetchArray('
			    SELECT pov.products_options_values_id Id, pov.products_options_values_name AS Value
			      FROM '.TABLE_PRODUCTS_OPTIONS_VALUES.' pov
			INNER JOIN '.TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS.' ov2po ON
			                    ov2po.products_options_values_id = pov.products_options_values_id
			                AND ov2po.products_options_id = "'.$g['Code'].'"
			     WHERE pov.language_id = "'.$this->languageId.'"
			  ORDER BY pov.products_options_values_name ASC
			');
			if (empty($values)) {
				unset($groups[$k]);
				continue;
			}
			$g['Values'] = array();
			foreach ($values as $v) {
				$g['Values'][$v['Id']] = $v['Value'];
			}
		}
		arrayEntitiesToUTF8($groups);
		$aOut = array();
		foreach ($groups as $aGroup) {
			$aOut[$aGroup['Code']] = $aGroup;
		}
		return $aOut;
	}
	
	protected function loadShopVariationData($which) {
		$values = MagnaDB::gi()->fetchOne('
			SELECT MpIdentifier
			  FROM '.TABLE_MAGNA_AYN24_VARIANTMATCHING.'
			 WHERE MpId = '.$this->mpId.'
			       AND CustomIdentifier="'.$which['CustomIdentifier'].'"
		');
		return $values;
	}
	
	protected function loadMPVariations($which, $select) {
		$data = Ayn24ApiConfigValues::gi()->getVariantConfigurationDefinition($which);
		if (!is_array($data) || !isset($data['Attributes'])) {
			return array();
		}
		
		$aSelect=explode(':',$select);
		$availableCustomConfigs = json_decode(MagnaDB::gi()->fetchOne(eecho('
			SELECT ShopVariation
			  FROM '.TABLE_MAGNA_AYN24_VARIANTMATCHING.'
			 WHERE MpId = '.$this->mpId.'
			       AND CustomIdentifier = "'.($aSelect[0] == 'ct' ? $aSelect[1] : '').'"
			       AND MpIdentifier = "'.$this->umlautkeyProtect($which).'"
		',false), true), true);
		
		arrayEntitiesToUTF8($data);
		$keys = array_keys($data['Attributes']);
		foreach ($keys as $key) {
			$newkey = $this->umlautkeyProtect($key);
			$data['Attributes'][$key]['AttributeCode'] = $newkey;
			$data['Attributes'][$key]['CurrentValues'] = isset($availableCustomConfigs[$newkey]) ? $availableCustomConfigs[$newkey] : array();
		}
		return $data['Attributes'];
	}
	
	protected function processAjax() {
		if (!isset($_POST['Action'])) {
			return;
		}
		switch ($_POST['Action']) {
			case 'LoadShopVariations': {
				$data = $this->loadShopVariations();
				echo json_encode($data);
				return;
			}
			case 'LoadMPVariations': {
				$variation = $this->umlautkeyDecode($_POST['MPVariation']);
				$select = $_POST['SelectValue'];
				$data = $this->loadMPVariations($variation, $select);
				echo json_encode($data);
				return;
			}
			case 'LoadShopVariationData': {
				$data = $this->loadShopVariationData($_POST['Data']);
				echo json_encode($data);
				return;
			}
		}
	}
	
	protected function saveMatching() {
		if (isset($_POST['ml']['match'])) {
			$matching = $_POST['ml']['match'];
			if (isset($matching['CustomIdentifier']) && $matching['CustomIdentifier']!='') {
				MagnaDB::gi()->delete(TABLE_MAGNA_AYN24_VARIANTMATCHING, array (
					'MpId' => $this->mpId,
					'CustomIdentifier' => $this->umlautkeyProtect($matching['CustomIdentifier'])
				));
				if (isset($matching['delete'])) {
					MagnaDB::gi()->delete(TABLE_MAGNA_AYN24_PROPERTIES, array (
						'mpID' => $this->mpId,
						'VariationConfiguration' => json_encode(array(
							'MpIdentifier' => $matching['MpIdentifier'],
							'CustomIdentifier' => $this->umlautkeyProtect($matching['CustomIdentifier'])
						))
					));
					return;
				}
				$this->mpActionSelectRequest = 'ct:'.$this->umlautkeyProtect($matching['CustomIdentifier']).':'.$matching['MpIdentifier'];
			} else {
				$this->mpActionSelectRequest = 'mp:'.$matching['MpIdentifier'];
			}

			arrayEntitiesToUTF8($matching['ShopVariation']);
			MagnaDB::gi()->insert(TABLE_MAGNA_AYN24_VARIANTMATCHING, array (
				'MpId' => $this->mpId,
				'MpIdentifier' => $matching['MpIdentifier'],
				'CustomIdentifier' => $this->umlautkeyProtect($matching['CustomIdentifier']),
				'ShopVariation' => json_encode($matching['ShopVariation']),
				'IsValid' => $matching['IsValid'] === 'false' ? false : true,
			), true);
			//echo print_m($matching, '$matching');
			
			if ($matching['IsValid'] === 'false') {
				echo '<p class="errorBox">'.ML_AYN24_VARMATCH_ALL_ATTRIBS_MUST_BE_DEFINED.'</p>';
			} else {
				echo '<p class="successBox">'.ML_LABEL_SAVED_SUCCESSFULLY.'</p>';
			}
		}
		
	}
	
	public function process() {
		if ($this->isAjax) {
			$this->processAjax();
			return;
		}
		$this->saveMatching();
		
		$this->loadAvailabeVariationGroups();
		
		echo $this->renderJs();
		echo $this->renderCss();
		echo $this->renderMatchingTable();
		
		
	}
	
}
