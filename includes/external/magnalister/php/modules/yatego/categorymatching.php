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
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

class YategoCategoryMatching {
	private $request = 'view';
	private $mpID = null;
	private $url;
	
	public function __construct($request = 'view') {
		global $_url, $_MagnaSession;
		
		$this->request = $request;
		$this->mpID = $_MagnaSession['mpID'];
		$this->url = $_url;
	}
	
	private function getYategoCategories($object_id = '') {
		if ($object_id == '') {
			$level = 0;
		} else {
			$level = substr_count($object_id, '-') + 1;
		}
		$field = '';
		switch ($level) {
			case 2: {
				$field = 'title_l';
				break;
			}
			case 1: {
				$field = 'title_m';
				break;
			}
			default: {
				$field = 'title_h';
				break;
			}
		}
		
		$yategoCategories = MagnaDB::gi()->fetchArray('
			SELECT DISTINCT '.$field.' AS title, object_id 
			  FROM '.TABLE_MAGNA_YATEGO_CATEGORIES.'
			 WHERE object_id LIKE \''.$object_id.'%\'
		  GROUP BY '.$field.'
		');
		
		if (empty($yategoCategories)) {
			return false;
		}
		if ($level < 2) {
			foreach ($yategoCategories as &$item) {
				if ($level == 1) {
					$item['object_id'] = substr($item['object_id'], 0, strrpos($item['object_id'], '-'));
				} else {
					$item['object_id'] = substr($item['object_id'], 0, strpos($item['object_id'], '-'));
				}
			}
		}

		return $yategoCategories;
	}
	
	private function getShopCategories($cID) {
		$subCats = MagnaDB::gi()->fetchArray('
			SELECT c.categories_id, c.parent_id, cd.categories_name
			  FROM '.TABLE_CATEGORIES.' c, '.TABLE_CATEGORIES_DESCRIPTION.' cd
			 WHERE c.categories_id = cd.categories_id AND
			       c.parent_id = '.$cID.' AND
			       cd.language_id = \''.$_SESSION['languages_id'].'\'
		  ORDER BY TRIM(cd.categories_name) ASC
		');
		if (empty($subCats)) {
			return false;
		}
		
		foreach($subCats as &$item) {
			$item['children'] = MagnaDB::gi()->fetchOne('
				SELECT count(c.parent_id) FROM '.TABLE_CATEGORIES.' c WHERE c.parent_id = '.$item['categories_id']
			);
			$item['yCats'] = MagnaDB::gi()->fetchOne('
				SELECT count(yatego_category_id) 
				  FROM '.TABLE_MAGNA_YATEGO_CATEGORYMATCHING.'
				 WHERE category_id=\''.$item['categories_id'].'\'
				       AND mpID=\''.$this->mpID.'\'
			  GROUP BY category_id'
			);
		}

		return $subCats;
	}

	private function renderShopCategories($cID) {
		$catTree = $this->getShopCategories($cID);
		foreach ($catTree as $item) {
			$class = array('toggle');
			if ($item['children']) {
				$class[] = 'plus';
			} else {
				$class[] = 'leaf';
			}
			if ($item['yCats']) {
				$class[] = 'tick';
			}
			$item['categories_name'] = htmlspecialchars($item['categories_name']);
			$html .= '
				<div class="catelem" id="s_'.$item['categories_id'].'"'.(!empty($subcats) ? ' style="display:none"' : '').'>
					<span class="'.implode(' ', $class).'" id="s_toggle_'.$item['categories_id'].'">&nbsp;</span>
					<div class="catname" id="s_select_'.$item['categories_id'].'">
						<span class="catname">'.fixHTMLUTF8Entities($item['categories_name']).'</span>
					</div>
				</div>';
		}
		if ($cID == 0) {
			$html = '
				<div class="catelem" id="s_0">
					<span class="toggle minus" id="s_toggle_0">&nbsp;</span>
					<div class="catname" id="s_select_0">
						<span class="catname">'.fixHTMLUTF8Entities(ML_LABEL_CATEGORY_TOP).'</span>'.$html.'
					</div>
				</div>';
		}
		return $html;
	}
	
	private function renderYategoCategories($object_id = '') {
		$isLeaf = substr_count($object_id, '-')+1 == 2;
		$yategoSubCats = $this->getYategoCategories($object_id);
		if ($yategoSubCats === false) {
			return ML_YAGETO_ERROR_CATEGORIES_NOT_IMPORTED_YET;
		}
		$yategoTopLevelList = '';
		foreach ($yategoSubCats as $item) {
			if ($isLeaf) {
				$class = 'leaf';
			} else {
				$class = 'plus';
			}
			$yategoTopLevelList .= '
				<div class="catelem" id="y_'.$item['object_id'].'">
					<span class="toggle '.$class.'" id="y_toggle_'.$item['object_id'].'">&nbsp;</span>
					<div class="catname" id="y_select_'.$item['object_id'].'">
						<span class="catname">'.fixHTMLUTF8Entities($item['title']).'</span>
					</div>
				</div>';
		}
		return $yategoTopLevelList;
	}

	private function getYategoCategoryPath($object_id) {
		$yCP = MagnaDB::gi()->fetchRow('
			SELECT * FROM '.TABLE_MAGNA_YATEGO_CATEGORIES.'
			 WHERE object_id=\''.$object_id.'\'
			 LIMIT 1
		');
		if ($yCP === false) {
			return '<span class="invalid">'.ML_LABEL_INVALID.'</span>';
		}
		$appendedText = '&nbsp;<span class="cp_next">&gt;</span>&nbsp;';
		
		return fixHTMLUTF8Entities($yCP['title_h']).$appendedText.
		       fixHTMLUTF8Entities($yCP['title_m']).$appendedText.
		       fixHTMLUTF8Entities($yCP['title_l']);
	}

	private function renderYategoCategoryItem($id) {
		return '
			<div id="yc_'.$id.'" class="yategoCategory">
				<div id="y_remove_'.$id.'" class="y_rm_handle">&nbsp;</div><div class="ycpath">'.$this->getYategoCategoryPath($id).'</div>
			</div>';
	}

	public function renderView() {
		$html = '
			<table id="catMatch"><tbody>
				<tr>
					<td class="headline">'.ML_YATEGO_LABEL_SHOP_CATEGORIES.'</td>
					<td class="spacer">&nbsp;</td>
					<td class="headline">'.ML_YATEGO_LABEL_YATEGO_CATEGORIES.'</td>
					<td class="spacer">&nbsp;</td>
					<td class="headline">'.ML_YATEGO_LABEL_SELECTED_SHOP_CAT.'</td>
				</tr>
				<tr>
					<td rowspan="3" id="shopCats" class="catView"><div class="catView">'.$this->renderShopCategories(0).'</div></td>
					<td rowspan="3" class="spacer">&nbsp;</td>
					<td rowspan="3" id="yategoCats" class="catView"><div class="catView">'.$this->renderYategoCategories('').'</div></td>
					<td rowspan="3" class="spacer">&nbsp;</td>
					<td id="selectedShopCategory" class="catView">
						<div class="catView"></div>
					</td>
				</tr>
				<tr>
					<td class="headline">'.ML_YATEGO_LABEL_SELECTED_YATEGO_CATS.'</td>
				</tr>
				<tr>
					<td id="selectedYategoCategories" class="catView">
						<div class="catView"></div>
					</td>
				</tr>
				<tr class="spacer">
					<td colspan="5">&nbsp;</td>
				</tr>
			</tbody></table>
			<div id="messageDialog" class="dialog2"></div>
			<table class="actions">
				<thead><tr><th>'.ML_LABEL_ACTIONS.'</th></tr></thead>
				<tbody>
					<tr class="firstChild"><td>
						<table><tbody><tr>
							<td class="firstChild">'.
								'<input type="button" class="ml-button" value="'.ML_YATEGO_LABEL_PURGE_CATEGORIES.'" id="yPurgeCategories" />
								 <div id="confirmPurgeDiag" class="dialog2" title="'.ML_YATEGO_HINT_HEADLINE_PURGE_CATEGORIES.'">'.ML_YATEGO_TEXT_PURGE_CATEGORIES.'</div>
								 <script type="text/javascript">/*<![CDATA[*/
								 	$(document).ready(function() {
								 		$(\'#yPurgeCategories\').click(function() {
											$(\'#confirmPurgeDiag\').jDialog({
												buttons: {
													'.ML_BUTTON_LABEL_ABORT.': function() {
														$(this).dialog(\'close\');
													},
													'.ML_BUTTON_LABEL_OK.': function() {
														window.location.href = \''.toURL($this->url, array('yPurgeCategories' => 'true'), true).'\';
														$(this).dialog(\'close\');
													}
												}
											});
										});
									});
								/*]]>*/</script>'.
							'</td>
							<td class="lastChild">'.'<input type="button" class="ml-button" value="'.ML_BUTTON_LABEL_SAVE_DATA.'" id="saveMatching"/>'.'</td>
						</tr></tbody></table>
					</td></tr>
				</tbody>
			</table>
		';
		ob_start();
?>
<script type="text/javascript">/*<![CDATA[*/
var selectedShopCategory = '';
var selectedYategoCategories = new Array();
var madeChanges = false;

function collapseAllNodes(elem) {
	$('div.catelem span.toggle:not(.leaf)', $(elem)).each(function() {
		$(this).removeClass('minus').addClass('plus');
		$(this).parent().children('div.catname').children('div.catelem').css({display: 'none'});
	});
	$('div.catname span.catname.selected', $(elem)).removeClass('selected').css({'font-weight':'normal'});
}

function resetEverything() {
	madeChanges = false;
	collapseAllNodes($('#yategoCats'));
	collapseAllNodes($('#shopCats'));
	/* Expand Top-Node */
	$('#s_toggle_0').removeClass('plus').addClass('minus').parent().children('div.catname').children('div.catelem').css({display: 'block'});
	$('#selectedYategoCategories div.catView').empty();
	$('#selectedShopCategory div.catView').empty();
	selectedYategoCategories = new Array();
	selectedShopCategory = '';
}

function appendYategoCategory(yID, html) {
	madeChanges = true;
	$('#selectedYategoCategories div.catView').append(html).children().last().data({'origID': yID}).click(function() {
		madeChanges = true;
		origID = $(this).data('origID');
		$('#'+origID+' span.catname.selected').removeClass('selected').css({'font-weight':'normal'});
		$(this).remove();
		//myConsole.log(origID, selectedYategoCategories);
		array_remove(selectedYategoCategories, origID);
		myConsole.log(origID, selectedYategoCategories);
		//myConsole.log($(this));
	});

	selectedYategoCategories.push(yID);
	myConsole.log('selectedYategoCategories', selectedYategoCategories);

	$('#'+yID+' span.catname').addClass('selected').css({'font-weight':'bold'});
}

function selectShopCategory(elem, doit) {
	if ((selectedYategoCategories.length > 0) && (madeChanges) && !doit) {
		$('#messageDialog').html(
			'<?php echo preg_replace('/\s\s+/', ' ', ML_YATEGO_MESSAGE_NOT_YET_SAVED); ?>'
		).jDialog({
			title: '<?php echo ML_LABEL_NOTE; ?>',
			buttons: {
				'<?php echo ML_BUTTON_LABEL_YES; ?>': function() {
					$(this).dialog('close');
					selectShopCategory(elem, true);
				},
				'<?php echo ML_BUTTON_LABEL_NO; ?>': function() {
					$(this).dialog('close');
					return;
				}
			}
		});
		return;
	}

	if (selectedYategoCategories.length > 0) {
		collapseAllNodes($('#yategoCats'));
	}
	
	$('#selectedYategoCategories div.catView').empty();
	selectedYategoCategories = new Array();
	
	tmpNewID = $(elem).parent().attr('id');
	if (selectedShopCategory != tmpNewID) {
		$('#shopCats div.catname span.catname.selected').removeClass('selected').css({'font-weight':'normal'});
		$(elem).addClass('selected').css({'font-weight':'bold'});

		jQuery.blockUI(blockUILoading);
		jQuery.ajax({
			type: 'POST',
			url: '<?php echo toURL($this->url, array('kind' => 'ajax'), true);?>',
			data: {
				'action': 'getCategoryPath',
				'id': tmpNewID
			},
			success: function(data) {
				myConsole.log(data.shopCatHtml);
				$('#selectedShopCategory div.catView').html(data.shopCatHtml);
				if (data.yCategories.length > 0) {
					for (var c = 0; c < data.yCategories.length; ++c) {
						yCat = data.yCategories[c];
						myConsole.log('yCat', yCat);
						appendYategoCategory(yCat.origID, yCat.html);
					}
				}
				madeChanges = false;
				jQuery.unblockUI();
			},
			error: function() {
				jQuery.unblockUI();
			},
			dataType: 'json'
		});
		selectedShopCategory = tmpNewID;
	}
}

function addShopCategoriesEventListener(elem) {
	/* Shop Kategorien */
	$('div.catelem span.toggle:not(.leaf)', $(elem)).each(function() {
		$(this).click(function () {
			myConsole.log($(this).attr('id'));
			if ($(this).hasClass('plus')) {
				tmpElem = $(this);
				tmpElem.removeClass('plus').addClass('minus');
				
				if (tmpElem.parent().children('div.catname').children('div.catelem').length == 0) {
					jQuery.blockUI(blockUILoading);
					jQuery.ajax({
						type: 'POST',
						url: '<?php echo toURL($this->url, array('kind' => 'ajax'), true);?>',
						data: {
							'action': 'getShopCategories',
							'cID': tmpElem.attr('id')
						},
						success: function(data) {
							appendTo = tmpElem.parent().children('div.catname');
							appendTo.append(data);
							addShopCategoriesEventListener(appendTo);
							appendTo.children('div.catelem').css({display: 'block'});
							jQuery.unblockUI();
						},
						error: function() {
							jQuery.unblockUI();
						},
						dataType: 'html'
					});
				} else {
					tmpElem.parent().children('div.catname').children('div.catelem').css({display: 'block'});
				}
			} else {
				$(this).removeClass('minus').addClass('plus');
				$(this).parent().children('div.catname').children('div.catelem').css({display: 'none'});
			}
		});
		// myConsole.log($(this).attr('id'));
	});
	$('div.catelem span.toggle.leaf', $(elem)).each(function() {
		$(this).click(function () {
			selectShopCategory($(this).parent().children('div.catname').children('span.catname'));
		});
	});
	$('div.catname span.catname', $(elem)).each(function() {
		$(this).click(function () {
			selectShopCategory($(this));
		});
	});
	
}

function array_remove( inputArr ) {
	var argv = arguments, argc = argv.length;
	for (i = 1; i < argc; ++i) {
		if ((ix = inputArr.indexOf(argv[i])) != -1) {
			inputArr.splice(ix, 1);
		}
	}
}

function addYategoCategory(elem) {
	if ($('#selectedShopCategory div.catView:empty').length) {
		$('#messageDialog').html(
			'<?php echo preg_replace('/\s\s+/', ' ', ML_YATEGO_MESSAGE_SELECT_SHOP_CAT_FIRST); ?>'
		).jDialog({
			title: '<?php echo ML_LABEL_NOTE; ?>'
		});
		return;
	}
	
	tmpNewID = $(elem).parent().attr('id');

	if (in_array(tmpNewID, selectedYategoCategories)) {
		return;
	}

	shortYID = tmpNewID.substr(0, tmpNewID.lastIndexOf('-'));
	for (i = 0; i < selectedYategoCategories.length; ++i) {
		if (shortYID == selectedYategoCategories[i].substr(0, selectedYategoCategories[i].lastIndexOf('-'))) {
			$('#messageDialog').html(
				'<?php echo preg_replace('/\s\s+/', ' ', ML_YATEGO_MESSAGE_ONLY_ONE_SUBCAT); ?>'
			).jDialog({
				title: '<?php echo ML_LABEL_NOTE; ?>'
			});
			return;
		}
	}

	jQuery.blockUI(blockUILoading);
	jQuery.ajax({
		type: 'POST',
		url: '<?php echo toURL($this->url, array('kind' => 'ajax'), true);?>',
		data: {
			'action': 'getYategoCategoryPath',
			'id': tmpNewID
		},
		success: function(data) {
			appendYategoCategory(tmpNewID, data);
			jQuery.unblockUI();
		},
		error: function() {
			jQuery.unblockUI();
		},
		dataType: 'html'
	});
}

function addYategoCategoriesEventListener(elem) {
	$('div.catelem span.toggle:not(.leaf)', $(elem)).each(function() {
		$(this).click(function () {
			myConsole.log($(this).attr('id'));
			if ($(this).hasClass('plus')) {
				tmpElem = $(this);
				tmpElem.removeClass('plus').addClass('minus');
				
				if (tmpElem.parent().children('div.catname').children('div.catelem').length == 0) {
					jQuery.blockUI(blockUILoading);
					jQuery.ajax({
						type: 'POST',
						url: '<?php echo toURL($this->url, array('kind' => 'ajax'), true);?>',
						data: {
							'action': 'getYategoCategories',
							'objID': tmpElem.attr('id')
						},
						success: function(data) {
							appendTo = tmpElem.parent().children('div.catname');
							appendTo.append(data);
							addYategoCategoriesEventListener(appendTo);
							appendTo.children('div.catelem').css({display: 'block'});
							jQuery.unblockUI();
						},
						error: function() {
							jQuery.unblockUI();
						},
						dataType: 'html'
					});
				} else {
					tmpElem.parent().children('div.catname').children('div.catelem').css({display: 'block'});
				}
			} else {
				$(this).removeClass('minus').addClass('plus');
				$(this).parent().children('div.catname').children('div.catelem').css({display: 'none'});
			}
		});
	});	
	$('div.catelem span.toggle.leaf', $(elem)).each(function() {
		$(this).click(function () {
			addYategoCategory($(this).parent().children('div.catname').children('span.catname'));
		});
		$(this).parent().children('div.catname').children('span.catname').each(function() {
			$(this).click(function () {
				addYategoCategory($(this));
			});
			if (in_array($(this).parent().attr('id'), selectedYategoCategories)) {
				$(this).addClass('selected').css({'font-weight':'bold'});	
			}
		});
	});
}

$(document).ready(function() {
	addShopCategoriesEventListener($('#shopCats'));
	addYategoCategoriesEventListener($('#yategoCats'));
	
	$('#saveMatching').click(function() {
		if (selectedShopCategory == '') {
			$('#messageDialog').html(
				'<?php echo preg_replace('/\s\s+/', ' ', ML_YATEGO_MESSAGE_SAVE_SELECT_SHOP_CAT_FIRST); ?>'
			).jDialog({
				title: '<?php echo ML_LABEL_NOTE; ?>'
			});
			return;
		}
/*
		if (selectedYategoCategories.length == 0) {
			$('#messageDialog').html(
				'Es kann nichts gespeichert werden, da Sie der ausgew&auml;hlten Shop-Kategorie noch keine Yatego-Kategorien hinzugef&uuml;gt haben.'
			).jDialog({
				title: '<?php echo ML_LABEL_NOTE; ?>'
			});
			return;
		}
*/
		jQuery.blockUI(blockUILoading);
		jQuery.ajax({
			type: 'POST',
			url: '<?php echo toURL($this->url, array('kind' => 'ajax'), true);?>',
			data: {
				'action': 'saveCategoryMatching',
				'selectedShopCategory': selectedShopCategory,
				'selectedYategoCategories': selectedYategoCategories
			},
			success: function(data) {
				jQuery.unblockUI();
				myConsole.log('data', data);
				myConsole.log('data.error', data.error);
				if (data.error == "") {
					$('#messageDialog').html(
						'<?php echo preg_replace('/\s\s+/', ' ', ML_YATEGO_MESSAGE_MATCHING_SAVED); ?>'
					).jDialog({
						title: '<?php echo ML_LABEL_SAVED_SUCCESSFULLY; ?>'
					});
					if (selectedYategoCategories.length > 0) {
						$('#'+selectedShopCategory).parent().children('span.toggle').addClass('tick');
					} else {
						$('#'+selectedShopCategory).parent().children('span.toggle').removeClass('tick');
					}
					resetEverything();
				} else {
					$('#messageDialog').html(
						data.error
					).jDialog({
						title: '<?php echo ML_ERROR_LABEL; ?>'
					});
				}
			},
			error: function() {
				jQuery.unblockUI();
				$('#messageDialog').html(
					'<?php echo preg_replace('/\s\s+/', ' ', ML_YATEGO_ERROR_WHILE_SAVING); ?>'
				).jDialog({
					title: '<?php echo ML_LABEL_NOTE; ?>'
				});
			},
			dataType: 'json'
		});
	});
});
/*]]>*/</script>
<?php
		$html .= ob_get_contents();	
		ob_end_clean();

		return $html;
	}
	
	public function renderAjax() {
		$id = '';
		if (isset($_POST['id'])) {
			$id = substr($_POST['id'], strrpos($_POST['id'], '_')+1);
		}
		switch($_POST['action']) {
			case 'getCategoryPath': {
				$_timer = microtime(true);
				$cID = (int)$id;
				$yIDs = MagnaDB::gi()->fetchArray('
					SELECT yatego_category_id 
					  FROM '.TABLE_MAGNA_YATEGO_CATEGORYMATCHING.'
					 WHERE category_id=\''.$cID.'\' AND mpID=\''.$this->mpID.'\'', true
				);
				$yategoCategories = array();
				if (!empty($yIDs)) {
					foreach ($yIDs as $yID) {
						$yategoCategories[] = array(
							'origID' => 'y_select_'.$yID,
							'html' => $this->renderYategoCategoryItem($yID)
						);
					}
				}
				$shopCatHtml = renderCategoryPath($cID);
				return json_encode(array(
					'shopCatHtml' => $shopCatHtml,
					'yCategories' => $yategoCategories,
					'timer' => microtime2human(microtime(true) -  $_timer)
				));
				break;
			}
			case 'getYategoCategories': {
				return $this->renderYategoCategories(str_replace('y_toggle_', '', $_POST['objID']));
				break;
			}
			case 'getShopCategories': {
				return $this->renderShopCategories(str_replace('s_toggle_', '', $_POST['cID']));
				break;
			}
			case 'getYategoCategoryPath': {
				return $this->renderYategoCategoryItem($id);
			}
			case 'saveCategoryMatching': {
				if (!isset($_POST['selectedShopCategory']) || empty($_POST['selectedShopCategory']) || 
					(isset($_POST['selectedYategoCategories']) && !is_array($_POST['selectedYategoCategories']))
				) {
					return json_encode(array(
						'debug' => var_dump_pre($_POST['selectedYategoCategories'], true),
						'error' => preg_replace('/\s\s+/', ' ', ML_YATEGO_ERROR_SAVING_INVALID_YATEGO_CATS)
					));
				}
 
				$cID = str_replace('s_select_', '', $_POST['selectedShopCategory']);
				if (!ctype_digit($cID)) {
					return json_encode(array(
						'debug' => var_dump_pre($cID, true),
						'error' => preg_replace('/\s\s+/', ' ', ML_YATEGO_ERROR_SAVING_INVALID_SHOP_CAT)
					));
				}
				$cID = (int)$cID;
				
				if (isset($_POST['selectedYategoCategories']) && !empty($_POST['selectedYategoCategories'])) {
					$yategoIDs = array();
					foreach ($_POST['selectedYategoCategories'] as $tmpYID) {
						$tmpYID = str_replace('y_select_', '', $tmpYID);
						if (preg_match('/^[0-9]{2}-[0-9]{2}-[0-9]{2}$/', $tmpYID)) {
							$yategoIDs[] = $tmpYID;
						}
					}
					if (empty($yategoIDs)) {
						return json_encode(array(
							'error' => preg_replace('/\s\s+/', ' ', ML_YATEGO_ERROR_SAVING_INVALID_YATEGO_CATS_ALL)
						));
					}
					MagnaDB::gi()->delete(TABLE_MAGNA_YATEGO_CATEGORYMATCHING, array (
						'category_id' => $cID,
						'mpID' => $this->mpID,
					));
					foreach ($yategoIDs as $yID) {
						MagnaDB::gi()->insert(TABLE_MAGNA_YATEGO_CATEGORYMATCHING, array (
							'category_id' => $cID,
							'yatego_category_id' => $yID,
							'mpID' => $this->mpID,
						));
					}
				} else {
					MagnaDB::gi()->delete(TABLE_MAGNA_YATEGO_CATEGORYMATCHING, array (
						'category_id' => $cID,
						'mpID' => $this->mpID,
					));
				}

				return json_encode(array(
					'error' => ''
				));

				break;
			}
			default: {
				return json_encode(array(
					'error' => ML_YATEGO_ERROR_REQUEST_INVALID
				));
			}
		}
	}
	
	public function render() {
		if ($this->request == 'ajax') {
			return $this->renderAjax();
		} else {
			return $this->renderView();
		}
		
	}
}

$_url['mode'] = $_magnaQuery['mode'];

$ycm = new YategoCategoryMatching((isset($_GET['kind']) && ($_GET['kind'] == 'ajax')) ? 'ajax' : 'view');
echo $ycm->render();
