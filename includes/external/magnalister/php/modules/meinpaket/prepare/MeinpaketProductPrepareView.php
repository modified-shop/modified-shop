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

require_once(DIR_MAGNALISTER_MODULES . 'meinpaket/classes/MeinpaketApiConfigValues.php');
require_once(DIR_MAGNALISTER_MODULES.'meinpaket/prepare/MeinpaketCategoryMatching.php');
require_once(DIR_MAGNALISTER_MODULES . 'meinpaket/MeinpaketHelper.php');

class MeinpaketProductPrepareView {
	protected $resources = array();
	
	protected $mpId = 0;
	protected $marketplace = '';
	
	protected $isAjax = false;
	
	public function __construct(&$params)
	{
		$this->resources = &$params['resources'];
		
		$this->mpId = $params['mpID'];
		$this->marketplace = $params['marketplace'];
		
		$this->isAjax = isset($_GET['kind']) && ($_GET['kind'] == 'ajax');
		
		$this->categoryMatcher = new MeinpaketCategoryMatching();
	}
	
	protected function renderCategoryMatching($data) {
		$oddEven = false;
		$mpOptionalAttributeTitle = str_replace('%marketplace%', ML_MODULE_MEINPAKET, ML_GENERAL_VARMATCH_MP_OPTIONAL_ATTRIBUTE);
		$mpCustomAttributeTitle = str_replace('%marketplace%', ML_MODULE_MEINPAKET, ML_GENERAL_VARMATCH_MP_CUSTOM_ATTRIBUTE);

		$attributeMatchingTableHtml = '
			<tbody id="variationMatcher" class="attributesTable">
				<tr class="headline">
					<td colspan="3"><h4>' . ML_MEINPAKET_VARIATIONCONFIG . '</h4></td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_MEINPAKET_VARIATIONCONFIG . '</th>
					<td class="input">
						<table class="inner middle fullwidth categorySelect"><tbody>
							<tr>
								<td>
									<div class="hoodCatVisual" id="PrimaryCategoryVisual">
										<select id="VariationConfiguration" name="VariationConfiguration" style="width:100%">
											'.$this->renderCategoryOptions('VariationConfiguration', $data['VariationConfiguration']).'
										</select>
									</div>
								</td>
							</tr>
						</tbody></table>
					</td>
					<td class="info"><span style="color:red;"></span></td>
				</tr>
				<tr class="spacer">
					<td colspan="3">&nbsp;</td>
				</tr>
			</tbody>
			<tbody id="tbodyDynamicMatchingHeadline" style="display:none;">
				<tr class="headline">
					<td colspan="1"><h4>' . str_replace('%marketplace%', ML_MODULE_MEINPAKET, ML_GENERAL_VARMATCH_MP_ATTRIBUTE) . '</h4></td>
					<td colspan="2"><h4>' . ML_GENERAL_VARMATCH_MY_WEBSHOP_ATTRIB . '</h4></td>
				</tr>
			</tbody>
			<tbody id="tbodyDynamicMatchingInput" style="display:none;">
				<tr>
					<th></th>
					<td class="input">' . ML_GENERAL_VARMATCH_SELECT_CATEGORY . '</td>
					<td class="info"></td>
				</tr>
			</tbody>
			<tbody id="tbodyDynamicMatchingOptionalHeadline" style="display:none;">
                <tr class="headline">
                    <td colspan="1"><h4>' . $mpOptionalAttributeTitle . '</h4></td>
                    <td colspan="2"><h4>' . ML_GENERAL_VARMATCH_MY_WEBSHOP_ATTRIB . '</h4></td>
                </tr>
            </tbody>
            <tbody id="tbodyDynamicMatchingOptionalInput" style="display:none;">
                <tr>
                    <th></th>
                    <td class="input">' . ML_GENERAL_VARMATCH_SELECT_CATEGORY . '</td>
                    <td class="info"></td>
                </tr>
            </tbody>
            <tbody id="tbodyDynamicMatchingCustomHeadline" style="display:none;">
                <tr class="headline">
                    <td colspan="1"><h4>' . $mpCustomAttributeTitle . '</h4></td>
                    <td colspan="2"><h4>' . ML_GENERAL_VARMATCH_MY_WEBSHOP_ATTRIB . '</h4></td>
                </tr>
            </tbody>
            <tbody id="tbodyDynamicMatchingCustomInput" style="display:none;">
                <tr>
                    <th></th>
                    <td class="input">' . ML_GENERAL_VARMATCH_SELECT_CATEGORY . '</td>
                    <td class="info"></td>
                </tr>
            </tbody>
			<tbody id="categoryInfo" style="display:none;">
				<tr class="spacer"><td colspan="3">' . ML_GENERAL_VARMATCH_CATEGORY_INFO . '</td></tr>
				<tr class="spacer"><td colspan="3">&nbsp;</td></tr>
			</tbody>';

		ob_start();
		?>
		<script type="text/javascript" src="<?php echo DIR_MAGNALISTER_WS ?>js/variation_matching.js?<?php echo CLIENT_BUILD_VERSION?>"></script>
		<script type="text/javascript" src="<?php echo DIR_MAGNALISTER_WS ?>js/marketplaces/meinpaket/variation_matching.js?<?php echo CLIENT_BUILD_VERSION?>"></script>
		<script type="text/javascript">
			/*<![CDATA[*/
			var ml_vm_config = {
				url: '<?php echo toURL($this->resources['url'], array('where' => 'prepareView', 'kind' => 'ajax'), true);?>',
				viewName: 'prepareView',
				formName: '#prepareForm',
				handleCategoryChange: false,
				i18n: <?php echo json_encode(MeinpaketHelper::gi()->getVarMatchTranslations());?>,
				shopVariations : <?php echo json_encode(MeinpaketHelper::gi()->getShopVariations()); ?>
			};
			/*]]>*/
		</script>
		<?php
		$attributeMatchingTableHtml .= ob_get_contents();
		ob_end_clean();


		$html = '
				<tr class="headline">
					<td colspan="3"><h4>' . ML_MEINPAKET_CATEGORY_MATCHING . '</h4></td>
				</tr>
				<tr class="even">
					<th>' . ML_MEINPAKET_CATEGORY_MATCHING . '</th>
					<td class="input">
						<style>
table.attributesTable table.inner
table.attributesTable table.inlinetable {
	border: none;
	border-spacing: 0px;
	border-collapse: collapse;
}
table.attributesTable td.fullwidth {
	width: 100%;
}
table.attributesTable table.fullwidth {
	width: 100%;
}
table.attributesTable table.inner tr td {
	border: none;
	padding: 1px 2px;
}
table.attributesTable table.inner.middle tr td {
	vertical-align: middle;
}
table.attributesTable table.categorySelect tr td.ml-buttons {
	width: 6em;
}
table.attributesTable table.categorySelect tr td.label {
	width: 1em;
	white-space: nowrap;
}
table.attributesTable table.inlinetable tr td {
	border: none;
	padding: 0;
}
div.catVisual {
	display: inline-block;
	width: 100%;
	height: 1.5em;
	line-height: 1.5em;
	background: #fff;
	color: #000;
	border: 1px solid #999;
	padding-left: 2px;
}

						</style>
						<table class="inner middle fullwidth categorySelect"><tbody>
							<tr>
								<td class="label">' . ML_MEINPAKET_LABEL_MEINPAKET_CATEGORY . ':</td>
								<td>
									<div class="catVisual" id="mpCategoryVisual">'.$this->categoryMatcher->getMPCategoryPath($data['MarketplaceCategory']).'</div>
									<input type="hidden" id="mpCategory" name="prepare[MarketplaceCategory]" value="'.$data['MarketplaceCategory'].'"/>
								</td>
								<td class="buttons">
									<input class="fullWidth ml-button smallmargin" type="button" value="' . ML_HOOD_CHOOSE . '" id="selectMPCategory"/>
								</td>
							</tr>
							'.(!getDBConfigValue(array($this->marketplace.'.catmatch.mpshopcats', 'val'), $this->mpId, false)
								? ('
							<tr>
								<td class="label">' . ML_MEINPAKET_LABEL_SHOP_CATEGORY . ':</td>
								<td>
									<div class="catVisual" id="storeCategoryVisual">'.$this->categoryMatcher->getShopCategoryPath($data['StoreCategory']).'</div>
									<input type="hidden" id="storeCategory" name="prepare[StoreCategory]" value="'.$data['StoreCategory'].'"/>
								</td>
								<td class="buttons">
									<input class="fullWidth ml-button smallmargin" type="button" value="'.ML_HOOD_CHOOSE.'" id="selectStoreCategory"/>
								</td>
							</tr>
								')
								: ''
							).'
						</tbody></table>'.
						$this->categoryMatcher->renderMatching();
		ob_start();
?>
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function() {
	$('#selectMPCategory').click(function() {
		mpCategorySelector.startCategorySelector(function(cID) {
			$('#mpCategory').val(cID);
			mpCategorySelector.getCategoryPath($('#mpCategoryVisual'));
		}, 'mp');
	});
	$('#selectStoreCategory').click(function() {
		mpCategorySelector.startCategorySelector(function(cID) {
			$('#storeCategory').val(cID);
			mpCategorySelector.getCategoryPath($('#storeCategoryVisual'));
		}, 'store');
	});
});
/*]]>*/</script>
<?php
		$html .= ob_get_contents();	
		ob_end_clean();
		$html .= '
					</td>
					<td class="info">' . '' . '</td>
				</tr>
				<tr class="spacer">
					<td colspan="3">&nbsp;</td>
				</tr>';

		return $html . $attributeMatchingTableHtml;
	}
	
	protected function renderShippingDetailsJs() {
		ob_start();
		?>
			<script type="text/javascript">/*<![CDATA[*/
				(function($) {
					$(document).ready(function () {
						$('#ShippingType select').change(function() {
							var e=$('#ShippingCostFixed').find('input[type="checkbox"]');
							if(!e.attr('data-name')){
								e.attr('data-name',e.attr('name'))
							}
							switch ($(this).val()) {
								case 'FORWARDING_AGENCY':
								case 'BULK_GOODS': {
									$('#ShippingCostFixed').show();
									e.attr('name',e.attr('data-name'));
									break;
								}
								default:
									$('#ShippingCostFixed').hide();
									e.removeAttr('name');
							}
						}).trigger('change');
						
					});
				})(jQuery);
			/*]]>*/</script>
		<?php
		$html=  ob_get_contents();
		ob_end_clean();
		return $html;
	}

	protected function renderShippingDetails($data) {
		$html = '
				<tr class="headline">'.$this->renderShippingDetailsJs().'
					<td colspan="3"><h4>' . ML_MEINPAKET_SHIPPING_DETAILS . '</h4></td>
				</tr>
				<tr class="even">
					<th>' . ML_MEINPAKET_SHIPPING_DETAILS_SHIPPINGCOST . '</th>
					<td class="input">
						<input type="text" name="prepare[ShippingDetails][ShippingCost]" value="'.$data['ShippingDetails']['ShippingCost'].'">
					</td>
					<td class="info">' . '' . '</td>
				</tr>
				<tr class="even" id="ShippingType">
					<th>' . ML_MEINPAKET_SHIPPING_DETAILS_SHIPPINGTYPE . '</th>
					<td class="input">
						<select name="prepare[ShippingDetails][ShippingType]">';
		$aShippingTypes = MeinpaketApiConfigValues::gi()->getShippingTypes();
		arrayEntitiesFixHTMLUTF8($aShippingTypes);
		$html .= '<option value="">'.ML_LABEL_DONT_USE.'</option>';
		foreach ($aShippingTypes as $key => $val) {
			if (!is_string($val)) {
				continue;
			}
			$html .= '
							<option value="'.$key.'" '.(($data['ShippingDetails']['ShippingType'] == $key) ? 'selected="selected"' : '').'>'.$val.'</option>';
		}
		$html .= '
						</select>
					</td>
					<td class="info">' . '' . '</td>
				</tr>
				<tr class="odd" id="ShippingCostFixed">
					<th>' . ML_MEINPAKET_SHIPPING_DETAILS_SHIPPINGCOSTFIXED . '</th>
					<td class="input">
						<input type="hidden" name="prepare[ShippingDetails][ShippingCostFixed]" value="0" />
						<input type="checkbox" name="prepare[ShippingDetails][ShippingCostFixed]" value="1"'.(
							$data['ShippingDetails']['ShippingCostFixed'] ?' checked="checked"' : ''
						).'>
					</td>
					<td class="info">' . '' . '</td>
				</tr>
				<tr class="spacer">
					<td colspan="3">&nbsp;</td>
				</tr>';
		return $html;
	}
	
	public function process($data) {
		/**
		 * Check ob einer oder mehrere Artikel
		 */
		$prepareView = (1 == count($data)) ? 'single' : 'multiple';
	
		$renderedView = '
			<form method="post" id="prepareForm" name="prepareForm" action="' . toURL($this->resources['url']) . '">
				<table class="attributesTable">
					<tbody>';

		#$renderedView .= print_m($data, __METHOD__);
		$renderedView .= $this->renderCategoryMatching($data);
		$renderedView .= $this->renderShippingDetails($data);
		$renderedView .= '
					</tbody>
				</table>
				<table class="actions">
					<thead><tr><th>' . ML_LABEL_ACTIONS . '</th></tr></thead>
					<tbody>
						<tr class="firstChild"><td>
							<table><tbody><tr>
								<td class="firstChild">'.(
									($prepareView == 'single' && false)
										? '<input class="ml-button" type="submit" name="unprepare" id="unprepare" value="' . ML_BUTTON_LABEL_REVERT . '"/>'
										: ''
									).'
								</td>
								<td class="lastChild">
									<input class="ml-button mlbtn-action" type="submit" name="saveMatching" value="' . ML_BUTTON_LABEL_SAVE_DATA . '"/>
								</td>
							</tr></tbody></table>
						</td></tr>
					</tbody>
				</table>
			</form>';
		echo $renderedView;
	}
	
	public function renderAjax() {
		if (isset($_GET['where']) && ($_GET['where'] == 'catMatchView')) {
			echo $this->categoryMatcher->renderAjax();
		} else if ($_POST['prepare'] === 'prepare' || (isset($_POST['Action']) && ($_POST['Action'] == 'LoadMPVariations'))) {
			if (isset($_POST['SelectValue'])) {
				$select = $_POST['SelectValue'];
			} else {
				$select = $_POST['VariationConfiguration'];
			}

			$productModel = MeinpaketHelper::gi()->getProductModel('apply');

			return json_encode(MeinpaketHelper::gi()->getMPVariations($select, $productModel, true));
		} else if (isset($_POST['Action']) && ($_POST['Action'] === 'DBMatchingColumns')) {
			$columns = MagnaDB::gi()->getTableCols($_POST['Table']);
			$editedColumns = array();
			foreach ($columns as $column) {
				$editedColumns[$column] = $column;
			}

			echo json_encode($editedColumns, JSON_FORCE_OBJECT);
		}
	}

	/**
	 * Fetches the options for the top 20 category selectors
	 * @param string $sType
	 *     Type of category (PrimaryCategory, SecondaryCategory, StoreCategory, StoreCategory2, StoreCategory3)
	 * @param string $sCategory
	 *     the selected category (empty for newly prepared items)
	 * @returns string
	 *     option tags for the select element
	 */
	protected function renderCategoryOptions($sType, $sCategory)
	{
		$categories = MeinpaketApiConfigValues::gi()->getAvailableVariantConfigurations();

		$htmlCategories = '<option value="">' . ML_GENERAL_VARMATCH_PLEASE_SELECT . '</option>';
		if (!empty($categories)) {
			foreach ($categories as $catKey => $catName) {
				if ($catKey === $sCategory) {
					$htmlCategories .= '<option value="' . fixHTMLUTF8Entities($catKey) . '" selected="selected">' . $catName['Name'] . '</option>';
				} else {
					$htmlCategories .= '<option value="' . fixHTMLUTF8Entities($catKey) . '">' . $catName['Name'] . '</option>';
				}
			}
		}

		return $htmlCategories;
	}
	
}
