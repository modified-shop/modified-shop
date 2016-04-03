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
 * $Id: AmazonCategoryView.php 4688 2014-10-08 13:06:57Z miguel.heredia $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/QuickCategoryView.php');

class AmazonCategoryView extends QuickCategoryView {
	public function __construct($cPath = 0, $settings = array(), $sorting = false, $search = '', $productIDs = array()) {
		global $_MagnaSession;
		$filter = array();
		
		if (($matchedItems = MagnaDB::gi()->fetchArray('
				SELECT DISTINCT '.(
					(getDBConfigValue('general.keytype', '0') == 'artNr') ? 'products_model' : 'products_id'
				).'
				  FROM '.TABLE_MAGNA_AMAZON_APPLY.' 
				 WHERE mpID="'.$_MagnaSession['mpID'].'"
			', true)) !== false
		) {
			if (getDBConfigValue('general.keytype', '0') == 'artNr') {
				$filter[] = array(
					'join' => '',
					'where' => 'p.products_model NOT IN ("'.implode('", "', MagnaDB::gi()->escape($matchedItems)).'")'
				);
			} else {
				$filter[] = array(
					'join' => '',
					'where' => 'p2c.products_id NOT IN ("'.implode('", "', $matchedItems).'")'
				);
			}
		}
		$this->setCat2ProdCacheQueryFilter($filter);
		if ($search != '') {
			$this->blUseParent  = true;
		}
		
		parent::__construct($cPath, $settings, $sorting, $search, $productIDs);
		//$this->action = array('action' => 'matching');

		if (!isset($_GET['kind']) || ($_GET['kind'] != 'ajax')) {
			$this->simplePrice->setCurrency(getCurrencyFromMarketplace($this->_magnasession['mpID']));
		}
	}
	
	protected function init() {
		parent::init();
		
		$this->productIdFilterRegister('ManufacturerFilter', array());
	}
	
	protected function getProductsCountOfCategoryInfo($iId){
		if (!isset($this->aCatInfo[$iId])) {
			$aOut = array (
				'iTotal' => 0,
				'iMatched' => 0,
				'iFailed' => 0
			);
			$aCatIds = $this->getAllSubCategoriesOfCategory($iId);
			$aCatIds[] = $iId;
			$sIdent = (getDBConfigValue('general.keytype', '0') == 'artNr') ? 'products_model' : 'products_id';
			$sSql = '
				     SELECT DISTINCT p.'.$sIdent.' 
				       FROM '.TABLE_PRODUCTS_TO_CATEGORIES.' p2c
				  LEFT JOIN '.TABLE_PRODUCTS.' p on p2c.products_id=p.products_id
				      WHERE p2c.categories_id IN(' . implode(', ', $aCatIds) . ')
				           '.($this->showOnlyActiveProducts ? 'AND p.products_status<>0' : '').'
				'.((getDBConfigValue('general.keytype', '0') == 'artNr')
						? 'AND p.products_model != "" AND p.products_model is not null'
						: ''
				).'
			';
			$aProducts = MagnaDB::gi()->fetchArray($sSql);
			$aProductIds = array();
			foreach ($aProducts as $aRow) {
				$aProductIds[$aRow[$sIdent]] = MagnaDB::gi()->escape($aRow[$sIdent]);
			}
			if (count($aProductIds) > 0) {
				$sSql = "
					SELECT ".$sIdent."
					  FROM ".TABLE_MAGNA_AMAZON_APPLY."
					 WHERE ".$sIdent." IN ('".implode("', '",$aProductIds)."')
					        AND mpID = '".$this->_magnasession['mpID']."'
				";
				$aAll = MagnaDB::gi()->fetchArray($sSql);
				foreach ($aAll as $aRow) {
					unset($aProductIds[$aRow[$sIdent]]);
				}
			}
			$aOut['iTotal'] = count($aProductIds);
			if (count($aProductIds)) {
				$sSql= "
					  SELECT DISTINCT COUNT(products_id) AS count, (asin = '' OR asin IS NULL) AS is_incomplete
					    FROM ".TABLE_MAGNA_AMAZON_PROPERTIES."
					   WHERE ".$sIdent." IN ('".implode("', '",$aProductIds)."')
					         AND mpid = '".$this->_magnasession['mpID']."'
					GROUP BY is_incomplete;
				";
				foreach (MagnaDB::gi()->fetchArray($sSql) as $aInfo) {
					if ($aInfo['is_incomplete'] == 'true') {
						$aOut['iFailed'] += $aInfo['count'];
					} else {
						$aOut['iMatched'] += $aInfo['count'];
					}
				}
			}
			$this->aCatInfo[$iId] = $aOut;
		}
		return $this->aCatInfo[$iId];
	}
	
	public function getAdditionalHeadlines() {
		return '
			<td class="lowestprice">'.ML_GENERIC_LOWEST_PRICE.'</td>
			<td class="matched">'.ML_AMAZON_LABEL_MATCHED.'</td>';
	}

	public function getAdditionalCategoryInfo($cID, $data = false) {
		return '<td>&mdash;</td>'.parent::renderAdditionalCategoryInfo($cID);
	}

	public function getAdditionalProductInfo($pID, $data = false) {
		$a = MagnaDB::gi()->fetchRow('
			SELECT products_id, `asin`, `lowestprice` 
			  FROM '.TABLE_MAGNA_AMAZON_PROPERTIES.'
			 WHERE '.((getDBConfigValue('general.keytype', '0') == 'artNr')
						? 'products_model="'.MagnaDB::gi()->escape($data['products_model']).'"'
						: 'products_id="'.$pID.'"'
					).'
					AND mpID="'.$this->_magnasession['mpID'].'"
		');
		if (empty($a)) {
			return '
				<td>&mdash;</td>
				<td>'.html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/grey_dot.png', ML_AMAZON_PRODUCT_MATHCED_NO, 9, 9).'</td>';
		}
		if (empty($a['asin'])) {
			return '
				<td>&mdash;</td>
				<td>'.html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/red_dot.png', ML_AMAZON_PRODUCT_MATCHED_FAULTY, 9, 9).'</td>';
		}
		return '
			<td>'.((!empty($a['lowestprice']) && ($a['lowestprice'] > 0)) ?  $this->simplePrice->setPrice($a['lowestprice'])->format().'<br />&nbsp;' : '&mdash;').'</td>
			<td>'.html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/green_dot.png', ML_AMAZON_PRODUCT_MATCHED_OK, 9, 9).'</td>';
	}
	
	public function getFunctionButtons() {
		global $_url;
		ob_start();
?>
<script type="text/javascript">/*<![CDATA[*/
var selectedItems = 0;
var progressInterval = null;
var percent = 0.0;

var _demo_sub = 0;
function updateProgressDemo() {
	_demo_sub -= 300;
	if (_demo_sub <= 0) {
		_demo_sub = 0;
		window.clearInterval(progressInterval);
		jQuery.unblockUI();
	}
	percent = 100 - ((_demo_sub / selectedItems) * 100);
	myConsole.log('Progress: '+_demo_sub+'/'+selectedItems+' ('+percent+'%)');	
	$('div.progressBarContainer div.progressPercent').html(Math.round(percent)+'%');
	$('div.progressBarContainer div.progressBar').css({'width' : percent+'%'});
}

function demoProgress() {
	jQuery.blockUI(blockUIProgress);
	selectedItems = _demo_sub = 4635;
	progressInterval = window.setInterval("updateProgressDemo()", 500);
}

function updateProgress() {
	jQuery.ajax({
		type: 'get',
		async: false,
		url: '<?php echo toURL($this->url, array('kind' => 'ajax', 'automatching' => 'getProgress'), true); ?>',
		success: function(data) {
			if (!is_object(data)) {
				//selectedItems = 0;
				return;
			}
			percent = 100 - ((data.x / selectedItems) * 100);
			myConsole.log('Progress: '+data.x+'/'+selectedItems+' ('+percent+'%)');
			$('div.progressBarContainer div.progressPercent').html(Math.round(percent)+'%');
			$('div.progressBarContainer div.progressBar').css({'width' : percent+'%'});
		},
		dataType: 'json'
	});
}
function runAutoMatching(matchSetting) {
	jQuery.blockUI(blockUIProgress);
	progressInterval = window.setInterval("updateProgress()", 500);
	jQuery.ajax({
		type: 'post',
		url: '<?php echo toURL($this->url, array('kind' => 'ajax', 'automatching' => 'start'), true); ?>',
		data: {
			'match': matchSetting
		},
		success: function(data) {
			window.clearInterval(progressInterval);
			jQuery.unblockUI();
			myConsole.log(data);
			$('#finalInfo').html(data).jDialog({
				buttons: {
					'<?php echo ML_BUTTON_LABEL_OK; ?>': function() {
						window.location.href = '<?php echo toURL($this->url, true); ?>';
					}
				}
			});
		},
		dataType: 'html'
	});
}

function handleAutomatching(matchSetting) {
	jQuery.ajax({
		type: 'get',
		async: false,
		url: '<?php echo toURL($this->url, array('kind' => 'ajax', 'automatching' => 'getProgress'), true); ?>',
		success: function(data) {
			if (!is_object(data)) {
				selectedItems = 0;
				return;
			}
			selectedItems = data.x;
		},
		dataType: 'json'
	});	
	myConsole.log(selectedItems);
	jQuery.unblockUI();

	if (selectedItems <= 0) {
		$('#noItemsInfo').jDialog();
	} else {
		$('#confirmDiag').jDialog({
			buttons: {
				'<?php echo ML_BUTTON_LABEL_ABORT; ?>': function() {
					$(this).dialog('close');
				},
				'<?php echo ML_BUTTON_LABEL_OK; ?>': function() {
					$(this).dialog('close');
					runAutoMatching(matchSetting);
				}
			}
		});
	}
}

$(document).ready(function() {
	$('#desc_man_match').click(function() {
		$('#manMatchInfo').jDialog();
	});
	$('#desc_auto_match').click(function() {
		$('#autoMatchInfo').jDialog();
	});
	$('#automatching').click(function() {
		//jQuery.blockUI(jQuery.extend(blockUILoading, {onBlock: handleAutomatching()}));
		var blockUILoading2 = jQuery.extend({}, blockUILoading);
		jQuery.blockUI(jQuery.extend(blockUILoading2, {onBlock: function() {
			handleAutomatching($('#match_settings input[type="radio"]:checked').val());
		}}));
		
	});
});
/*]]>*/</script>
<?php
		$js = ob_get_contents();
		ob_end_clean();

		$mmatch = getDBConfigValue(array('amazon.multimatching', 'rematch'), $this->_magnasession['mpID']);

		return '
			<input type="hidden" value="'.$this->settings['selectionName'].'" name="selectionName"/>
			<input type="hidden" value="_" id="actionType"/>
			<table class="right"><tbody>
				<tr>
					<td id="match_settings" rowspan="2" class="textleft inputCell">
						<input id="match_all_rb" type="radio" name="match" value="all" '.($mmatch ? 'checked="checked"' : '').'/>
						<label for="match_all_rb">'.ML_LABEL_ALL.'</label><br />
						<input id="match_notmatched_rb" type="radio" name="match" value="notmatched" '.(!$mmatch ? 'checked="checked"' : '').'/>
						<label for="match_notmatched_rb">'.ML_AMAZON_LABEL_ONLY_NOT_MATCHED.'</label>
					</td>
					<td class="texcenter inputCell">
						<input type="submit" class="fullWidth ml-button smallmargin mlbtn-action" value="'.ML_AMAZON_LABEL_MANUAL_MATCHING.'" id="matching" name="matching"/>
					</td>
					<td>
						<div class="desc" id="desc_man_match" title="'.ML_LABEL_INFOS.'"><span>'.ML_AMAZON_LABEL_MANUAL_MATCHING.'</span></div>
					</td>
				</tr>
				<tr>
					<td class="texcenter inputCell">
						<input type="button" class="fullWidth ml-button smallmargin mlbtn-action" value="'.ML_AMAZON_LABEL_AUTOMATIC_MATCHING.'" id="automatching" name="automatching"/>
					</td>
					<td>
						<div class="desc" id="desc_auto_match" title="'.ML_LABEL_INFOS.'"><span>'.ML_AMAZON_LABEL_AUTOMATIC_MATCHING.'</span></div>
					</td>
				</tr>
			</tbody></table>
			<div id="finalInfo" class="dialog2" title="'.ML_LABEL_INFORMATION.'"></div>
			<div id="noItemsInfo" class="dialog2" title="'.ML_LABEL_NOTE.'">'.ML_AMAZON_TEXT_MATCHING_NO_ITEMS_SELECTED.'</div>
			<div id="manMatchInfo" class="dialog2" title="'.ML_LABEL_INFORMATION.' '.ML_AMAZON_LABEL_MANUAL_MATCHING.'">'.ML_AMAZON_TEXT_MANUALLY_MATCHING_DESC.'</div>
			<div id="autoMatchInfo" class="dialog2" title="'.ML_LABEL_INFORMATION.' '.ML_AMAZON_LABEL_AUTOMATIC_MATCHING.'">'.ML_AMAZON_TEXT_AUTOMATIC_MATCHING_DESC.'</div>
			<div id="confirmDiag" class="dialog2" title="'.ML_LABEL_NOTE.'">'.ML_AMAZON_TEXT_AUTOMATIC_MATCHING_CONFIRM.'</div>
		'.$js;

	}

	public function getLeftButtons() {
		return '<input type="submit" class="ml-button" value="'.ML_AMAZON_BUTTON_MATCHING_DELETE.'" id="unmatching" name="unmatching"/>';
	}
	
}
