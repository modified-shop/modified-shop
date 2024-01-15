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
 * $Id: categorymatching.php 674 2011-01-08 03:21:50Z derpapst $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'etsy/EtsyHelper.php');

class EtsyCategoryMatching {
	const ETSY_CAT_VALIDITY_PERIOD = 86400; # Nach welcher Zeit werden Kategorien ungueltig (Sekunden)
	
	private $request = 'view';
	private $isStoreCategory = false;
	private $mpID = 0;

	private $url;

	public static $aCategoriePathCache = array();

	public function __construct($request = 'view') {
		global $_url, $_MagnaSession;
		
		$this->request = $request;
		$this->url = $_url;
		$this->mpID = $_MagnaSession['mpID'];
		$this->marketplace = $_MagnaSession['currentPlatform'];
		$this->hasPlatformCol = true;
		$this->columns = MagnaDB::gi()->getTableColumns(TABLE_MAGNA_ETSY_CATEGORIES);
	}
	
	# Die Funktion wird verwendet beim Aufruf der Kategorie-Zuordnung, nicht vorher.
	# Beim Aufruf werden die Hauptkategorien gezogen,
	# und beim Anklicken der einzelnen Kategorie die Kind-Kategorien, falls noch nicht vorhanden.
	private static function importEtsyCategories($ParentID = 0) {
		global $_MagnaSession;
		try {
			$categories = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'GetChildCategories',
				'DATA' => array(
					'ParentID' => (int)$ParentID,
					'Language' => getDBConfigValue('etsy.shop.language', $_MagnaSession['mpID'], 'en')
				)
			));
		} catch (MagnaException $e) {
			$categories = array(
				'DATA' => false
			);
		}
		if (!is_array($categories['DATA']) || empty($categories['DATA'])) {
			return false;
		}
		$now = date('Y-m-d H:i:s');
		foreach($categories['DATA'] as &$curRow) {
			$curRow['InsertTimestamp'] = $now;
			$curRow['Language'] = getDBConfigValue('etsy.shop.language', $_MagnaSession['mpID'], 'en');
			$curRow = array_filter_keys($curRow, MagnaDB::gi()->getTableColumns(TABLE_MAGNA_ETSY_CATEGORIES));
		}
		$delete_query = 'DELETE FROM '.TABLE_MAGNA_ETSY_CATEGORIES
			.' WHERE Language = \''.getDBConfigValue('etsy.shop.language', $_MagnaSession['mpID'], 'en').'\'
			AND ParentID = ';
		if (0 == (int)$ParentID)	{
			$delete_query .= '0';
		} else {
			$delete_query .= $ParentID.' AND ParentID <> 0';
		}
		MagnaDB::gi()->query($delete_query);
		MagnaDB::gi()->batchinsert(TABLE_MAGNA_ETSY_CATEGORIES, $categories['DATA'], true);
		return true;
	}
	
	/** 
	 * Die Funktion wird verwendet beim Aufruf der Kategorie-Zuordnung, nicht vorher.
	 * Beim Aufruf werden die Hauptkategorien gezogen,
	 * und beim Anklicken der einzelnen Kategorie die Kind-Kategorien, falls noch nicht vorhanden.
	 */
	public function importEtsyCategoryPath($categoryID) {
		global $_MagnaSession;
		try {
			$categories = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'GetCategoryWithAncestors',
				'DATA' => array(
					'CategoryID' => (int)$categoryID,
					'Language' => getDBConfigValue('etsy.shop.language', $_MagnaSession['mpID'], 'en')
				)
			));
		} catch (MagnaException $e) {
			$categories = array(
				'DATA' => false
			);
		}
		if (!is_array($categories['DATA']) || empty($categories['DATA'])) {
			return false;
		}
		$now = time();
		foreach ($categories['DATA'] as &$curRow) {
			$curRow['InsertTimestamp'] = $now;
			$curRow['Language'] = getDBConfigValue('etsy.shop.language', $_MagnaSession['mpID'], 'en');
			unset($curRow['CategoryLevel']);
			unset($curRow['Timestamp']);
		}
		MagnaDB::gi()->batchinsert(TABLE_MAGNA_ETSY_CATEGORIES, $categories['DATA'], true);
		return true;
	}
	
	private function getEtsyCategories($ParentID = 0, $purge = false) {
		if ($purge) {
			MagnaDB::gi()->delete(TABLE_MAGNA_ETSY_CATEGORIES, array (
				'platform' => $this->marketplace,
				'mpID' => $this->mpID
			));
		}
		if (0 == (int)$ParentID) { // (int), cause for PHP >= 8.1, '' != 0
			$whereCondition = '0 = ParentID';
		} else {
			$whereCondition = "0 != ParentID AND ParentID = $ParentID";
		}
		$whereCondition .= " AND Language = '".getDBConfigValue('etsy.shop.language', $this->mpID, 'en')."'";
		
		$etsyCategories = MagnaDB::gi()->fetchArray('
		    SELECT SQL_CALC_FOUND_ROWS DISTINCT CategoryID, CategoryName,
		           ParentID, LeafCategory
		      FROM '.TABLE_MAGNA_ETSY_CATEGORIES.'
		     WHERE '.$whereCondition.'
		           AND InsertTimestamp > UNIX_TIMESTAMP() - '.self::ETSY_CAT_VALIDITY_PERIOD.'
		  ORDER BY CategoryName ASC
		');
		$countFoundCategories = (int)MagnaDB::gi()->foundRows();
		
		# nichts gefunden? vom Server abrufen
		# Mit < 5 fuer den Fall dass Kategoriepfade zu einzelnen Kategorien geholt wurden
		if ($countFoundCategories < 5) {
			if (self::importEtsyCategories($ParentID)) {
				# Wenn Daten bekommen, noch mal select
				$etsyCategories = MagnaDB::gi()->fetchArray('
				    SELECT DISTINCT CategoryID, CategoryName,
				           ParentID, LeafCategory
				      FROM '.TABLE_MAGNA_ETSY_CATEGORIES.'
				     WHERE '.$whereCondition.'
				  ORDER BY CategoryName ASC
				');
			}
		}

		if (empty($etsyCategories)) {
			return false;
		}
		return $etsyCategories;
	}
	
	public function getEtsyCategoryPath($categoryID, $storeCategory = false, $justImported = false) {
		$appendedText = '&nbsp;<span class="cp_next">&gt;</span>&nbsp;';
		
		$storeCategory = $storeCategory ? '1' : '0';
		$catPath = '';
		do {
			# Ermittle Namen, CategoryID und ParentID,
			# dann das gleiche fuer die ParentCategory usw.
			# bis bei Top angelangt (0 = ParentID)
			$yCP = MagnaDB::gi()->fetchRow('
			    SELECT CategoryID, CategoryName , ParentID
			      FROM ' . TABLE_MAGNA_ETSY_CATEGORIES . '
			     WHERE CategoryID="' . $categoryID . '"
			           AND Language="'.getDBConfigValue('etsy.shop.language', $this->mpID, 'en').'"
			  ORDER BY InsertTimestamp DESC LIMIT 1
			');
			if ($yCP === false)
				break;
			if (empty($catPath)) {
				$catPath = fixHTMLUTF8Entities($yCP['CategoryName']);
			} else {
				$catPath = fixHTMLUTF8Entities($yCP['CategoryName']) . $appendedText . $catPath;
			}
			$categoryID = $yCP['ParentID'];
		} while (0 != $yCP['ParentID']);
	
		if (($yCP === false) && ($justImported == true)) {
			return '<span class="invalid">' . ML_LABEL_INVALID . '</span>';
		}
		if (($yCP === false) && ($justImported == false)) {
			if ($storeCategory) {
				$this->importEtsyStoreCategories();
			} else {
				$this->importEtsyCategoryPath($categoryID);
			}
			return $this->getEtsyCategoryPath($categoryID, $storeCategory, true);
		}
		return $catPath;
	}
	
	private function renderEtsyCategories($ParentID = 0, $purge = false) {
		#echo print_m(func_get_args(), __METHOD__);
		#echo var_dump_pre($this->isStoreCategory, '$this->isStoreCategory');
		$etsySubCats = $this->getEtsyCategories($ParentID, $purge);
		#echo print_m($etsySubCats, '$etsySubCats');
		if ($etsySubCats === false) {
			return '';
		}
		$etsyTopLevelList = '';
		foreach ($etsySubCats as $item) {
			if (1 == $item['LeafCategory']) {
				$class = 'leaf';
			} else {
				$class = 'plus';
			}
			$etsyTopLevelList .= '
				<div class="catelem" id="y_'.$item['CategoryID'].'">
					<span class="toggle '.$class.'" id="y_toggle_'.$item['CategoryID'].'">&nbsp;</span>
					<div class="catname" id="y_select_'.$item['CategoryID'].'">
						<span class="catname">'.fixHTMLUTF8Entities($item['CategoryName']).'</span>
					</div>
				</div>';
		}
		return $etsyTopLevelList;
	}
	

	private function renderEtsyCategoryItem($id) {
		return '
			<div id="yc_'.$id.'" class="etsyCategory">
				<div id="y_remove_'.$id.'" class="y_rm_handle">&nbsp;</div><div class="ycpath">'.$this->getEtsyCategoryPath($id, $this->isStoreCategory).'</div>
			</div>';
	}

	public function renderView() {
		$html = '
			<div id="etsyCategorySelector" class="dialog2" title="'.ML_HOOD_LABEL_SELECT_CATEGORY.'">
				<table id="catMatch"><tbody>
					<tr>
						<td id="etsyCats" class="catView"><div class="catView">'.$this->renderEtsyCategories('').'</div></td>
					</tr>
					<tr><td class="catVisual" id="tmpSelectedCat"></td></tr>
				</tbody></table>
				<div id="messageDialog" class="dialog2"></div>
			</div>
		';
		ob_start();
?>
<script type="text/javascript">/*<![CDATA[*/
var selectedEtsyCategory = '';
var madeChanges = false;
var isStoreCategory = false;

function collapseAllNodes(elem) {
	$('div.catelem span.toggle:not(.leaf)', $(elem)).each(function() {
		$(this).removeClass('minus').addClass('plus');
		$(this).parent().children('div.catname').children('div.catelem').css({display: 'none'});
	});
	$('div.catname span.catname.selected', $(elem)).removeClass('selected').css({'font-weight':'normal'});
}

function resetEverything() {
	madeChanges = false;
	collapseAllNodes($('#etsyCats'));
	/* Expand Top-Node */
	$('#s_toggle_0').removeClass('plus').addClass('minus').parent().children('div.catname').children('div.catelem').css({display: 'block'});
	$('#etsyCategorySelector td.catVisual').empty();
	selectedEtsyCategory = '';
}

function selectEtsyCategory(yID, html) {
	madeChanges = true;
	$('#etsyCategorySelector td.catVisual').html(html);

	selectedEtsyCategory = yID;
	myConsole.log('selectedEtsyCategory', selectedEtsyCategory);

	//$('#etsyCats div.catname span.catname.selected').removeClass('selected').css({'font-weight':'normal'});
	//$('#'+yID+' span.catname').addClass('selected').css({'font-weight':'bold'});
	
	$('#etsyCats div.catView').find('span.catname.selected').removeClass('selected').css({'font-weight':'normal'});
	$('#etsyCats div.catView').find('span.toggle.tick').removeClass('tick');
	
	$('#'+yID+' span.catname').addClass('selected').css({'font-weight':'bold'});
	$('#'+yID+' span.catname').parents().prevAll('span.catname').addClass('selected').css({'font-weight':'bold'});
	$('#'+yID+' span.catname').parents().prev('span.toggle').addClass('tick');
	generateEtsyCategoryPath(yID, $('#PrimaryCategoryVisual'));
}

function clickEtsyCategory(elem) {
	// hier Kategorien zuordnen, zu allen ausgewaehlten Items
	tmpNewID = $(elem).parent().attr('id');

	jQuery.ajax({
		type: 'POST',
		url: '<?php echo toURL($this->url, array('where' => 'prepareView', 'kind' => 'ajax'), true);?>',
		data: {
			'action': 'getEtsyCategoryPath',
			'id': tmpNewID,
			'isStoreCategory': isStoreCategory
		},
		success: function(data) {
			selectEtsyCategory(tmpNewID, data);
		},
		error: function() {
		},
		dataType: 'html'
	});
}

function addEtsyCategoriesEventListener(elem) {
	$('div.catelem span.toggle:not(.leaf)', $(elem)).each(function() {
		$(this).click(function () {
			myConsole.log($(this).attr('id'));
			if ($(this).hasClass('plus')) {
				tmpElem = $(this);
				tmpElem.removeClass('plus').addClass('minus');
				
				if (tmpElem.parent().children('div.catname').children('div.catelem').length == 0) {
					jQuery.ajax({
						type: 'POST',
						url: '<?php echo toURL($this->url, array('where' => 'prepareView', 'kind' => 'ajax'), true);?>',
						data: {
							'action': 'getEtsyCategories',
							'objID': tmpElem.attr('id'),
							'isStoreCategory': isStoreCategory
						},
						success: function(data) {
							appendTo = tmpElem.parent().children('div.catname');
							appendTo.append(data);
							addEtsyCategoriesEventListener(appendTo);
							appendTo.children('div.catelem').css({display: 'block'});
						},
						error: function() {
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
			clickEtsyCategory($(this).parent().children('div.catname').children('span.catname'));
		});
		$(this).parent().children('div.catname').children('span.catname').each(function() {
			$(this).click(function () {
				clickEtsyCategory($(this));
			});
			if ($(this).parent().attr('id') == selectedEtsyCategory) {
				$(this).addClass('selected').css({'font-weight':'bold'});
			}
		});
	});
}

function returnCategoryID() {
	if (selectedEtsyCategory == '') {
		$('#messageDialog').html(
			'Bitte w&auml;hlen Sie eine Etsy-Kategorie aus.'
		).jDialog({
			title: '<?php echo ML_LABEL_NOTE; ?>'
		});
		return false;
	}
	cID = selectedEtsyCategory;
	cID = str_replace('y_select_', '', cID);
	resetEverything();
	return cID;
}

function generateEtsyCategoryPath(cID, viewElem) {
	cID = str_replace('y_select_', '', cID);
	viewElem.find('option').attr('selected','');
	if(viewElem.find('[value='+cID+']').length>0){
		viewElem.find('[value='+cID+']').attr('selected','selected');
	}else{
	jQuery.ajax({
		type: 'POST',
		url: '<?php echo toURL($this->url, array('where' => 'prepareView', 'kind' => 'ajax'), true);?>',
		data: {
			'action': 'getEtsyCategoryPath',
			'id': cID,
			'isStoreCategory': isStoreCategory
		},
		success: function(data) {
//			viewElem.html(data);
			viewElem.find('select').append('<option selected="selected" value="'+cID+'">'+data+'</option>');
		},
		error: function() {
		},
		dataType: 'html'
	});
	}
}

function initEtsyCategories(purge) {
	purge = purge || false;
	myConsole.log('isStoreCategory', isStoreCategory);
	jQuery.ajax({
		type: 'POST',
		url: '<?php echo toURL($this->url, array('where' => 'prepareView', 'kind' => 'ajax'), true);?>',
		data: {
			'action': 'getEtsyCategories',
			'objID': '',
			'isStoreCategory': isStoreCategory,
			'purge': purge ? 'true' : 'false'
		},
		success: function(data) {
			$('#etsyCats > div.catView').html(data);
			addEtsyCategoriesEventListener($('#etsyCats'));
		},
		error: function() {
		},
		dataType: 'html'
	});
}

function startCategorySelector(callback, kind) {
	newStoreState = (kind == 'store');
	if ((newStoreState != isStoreCategory) || ($('#etsyCats > div.catView').children().length == 0)) {
		isStoreCategory = newStoreState;
		$('#etsyCats > div.catView').html('');
		initEtsyCategories();
	}
	
	$('#etsyCategorySelector').jDialog({
		width: '75%',
		minWidth: '300px',
		buttons: {
			'<?php echo ML_BUTTON_LABEL_ABORT; ?>': function() {
				$(this).dialog('close');
			},
			'<?php echo ML_BUTTON_LABEL_OK; ?>': function() {
				cID = returnCategoryID();
				if (cID != false) {
					callback(cID, $('#tmpSelectedCat').html());
					$('#PrimaryCategory').trigger('change');  // for apply form
					$(this).dialog('close');
				}
			}
		},
		open: function(event, ui) {
			var tbar = $('#etsyCategorySelector').parent().find('.ui-dialog-titlebar');
			if (tbar.find('.ui-icon-arrowrefresh-1-n').length == 0) {
				var rlBtn = $('<a class="ui-dialog-titlebar-close ui-corner-all ui-state-focus" '+
					'role="button" href="#" style="right: 2em; padding: 0px;">'+
						'<span class="ui-icon ui-icon-arrowrefresh-1-n">reload</span>'+
					'</a>')
				tbar.append(rlBtn);
				rlBtn.click(function (event) {
					event.preventDefault();
					initEtsyCategories(true);
				});
			}
		}
	});
}
// new 20190131
	var mpCategorySelector = (function() {
		return {
			addCategoriesEventListener: addEtsyCategoriesEventListener,
			getCategoryPath: function(e) {
				e.html(finalEtsyCategoryPath);
			},
			startCategorySelector: startCategorySelector
		}
	})();

// end new 20190131
$(document).ready(function() {
	//addEtsyCategoriesEventListener($('#etsyCats'));
	mpCategorySelector.addCategoriesEventListener($('#etsyCats')); // new 20190131
});
/*]]>*/</script>
<?php
		$html .= ob_get_contents();	
		ob_end_clean();

		return $html;
	}

	public function renderMatching() {
		return $this->renderView();
	}
	
	public function renderAjax() {
		$id = '';
		if (isset($_POST['id'])) {
			if (($pos = strrpos($_POST['id'], '_')) !== false) {
				$id = substr($_POST['id'], $pos+1);
			} else {
				$id = $_POST['id'];
			}
		}
		$this->isStoreCategory = false;

		switch ($_POST['action']) {
			case 'getEtsyCategories': {
				return $this->renderEtsyCategories(
					empty($_POST['objID'])
						? 0
						: str_replace('y_toggle_', '', $_POST['objID']),
					isset($_POST['purge']) ? $_POST['purge'] : false
				);
				break;
			}
			case 'renderEtsyCategoryItem': {
				return $this->renderEtsyCategoryItem($id);
			}
			case 'getEtsyCategoryPath': {
				return $this->getEtsyCategoryPath($id, $this->isStoreCategory);
			}
			case 'GetMpCategoryAttributes': {
				if (isset($_POST['cId'])) {
					return json_encode(EtsyHelper::gi()->getAttributesFromMP($_POST['cId']));
				} else {
					return '';
				}
			}
			case 'saveCategoryMatching': {
				if (!isset($_POST['selectedShopCategory']) || empty($_POST['selectedShopCategory']) || 
					(isset($_POST['selectedEtsyCategories']) && !is_array($_POST['selectedEtsyCategories']))
				) {
					return json_encode(array(
						'debug' => var_dump_pre($_POST['selectedEtsyCategories'], true),
						'error' => preg_replace('/\s\s+/', ' ', ML_HOOD_ERROR_SAVING_INVALID_HOOD_CATS)
					));
				}
 
				$cID = str_replace('s_select_', '', $_POST['selectedShopCategory']);
				if (!ctype_digit($cID)) {
					return json_encode(array(
						'debug' => var_dump_pre($cID, true),
						'error' => preg_replace('/\s\s+/', ' ', ML_HOOD_ERROR_SAVING_INVALID_SHOP_CAT)
					));
				}
				$cID = (int)$cID;
				
				if (isset($_POST['selectedEtsyCategories']) && !empty($_POST['selectedEtsyCategories'])) {
					$etsyIDs = array();
					foreach ($_POST['selectedEtsyCategories'] as $tmpYID) {
						$tmpYID = str_replace('y_select_', '', $tmpYID);
						if (preg_match('/^[0-9]{2}-[0-9]{2}-[0-9]{2}$/', $tmpYID)) {
							$etsyIDs[] = $tmpYID;
						}
					}
					if (empty($etsyIDs)) {
						return json_encode(array(
							'error' => preg_replace('/\s\s+/', ' ', ML_HOOD_ERROR_SAVING_INVALID_HOOD_CATS_ALL)
						));
					}
				}

				return json_encode(array(
					'error' => ''
				));

				break;
			}
			default: {
				return json_encode(array(
					//'error' => ML_HOOD_ERROR_REQUEST_INVALID
					'error' => $_POST['action']
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
