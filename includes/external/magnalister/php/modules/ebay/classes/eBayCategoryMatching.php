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

class eBayCategoryMatching {
	const EBAY_CAT_VALIDITY_PERIOD = 86400; # Nach welcher Zeit werden Kategorien ungueltig (Sekunden)
	const EBAY_STORE_CAT_VALIDITY_PERIOD = 600; # Nach welcher Zeit werden Store-Kategorien ungueltig (Sekunden)

	private $request = 'view';
	private $isStoreCategory = false;

	private $url;
	private $SiteID;

	public function __construct($request = 'view') {
		global $_url;

		$this->request = $request;
		$this->url = $_url;

        $this->SiteID = geteBaySiteID();
	}

	# Die Funktion wird verwendet beim Aufruf der Kategorie-Zuordnung, nicht vorher.
	# Beim Aufruf werden die Hauptkategorien gezogen,
	# und beim Anklicken der einzelnen Kategorie die Kind-Kategorien, falls noch nicht vorhanden.
	private static function importeBayCategories($ParentID = 0) {
		try {
			$categories = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'GetChildCategories',
				'DATA' => array('ParentID' => $ParentID)
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
		foreach($categories['DATA'] as &$curRow) {
			$curRow['InsertTimestamp'] = $now;
			$curRow['StoreCategory'] = '0';
		}
		$delete_query = 'DELETE FROM '.TABLE_MAGNA_EBAY_CATEGORIES
			.' WHERE StoreCategory=\'0\'
			AND SiteID = '.$categories['DATA'][0]['SiteID'].'
			AND ParentID = ';
		# ganz oben ist CategoryID == ParentID
		if (0 == (int)$ParentID)	{ // (int), cause for PHP >= 8.1, '' != 0
			$delete_query .= 'CategoryID';
		} else {
			$delete_query .= $ParentID.' AND ParentID <> CategoryID';
		}
		MagnaDB::gi()->query($delete_query);
		MagnaDB::gi()->batchinsert(TABLE_MAGNA_EBAY_CATEGORIES, $categories['DATA'], true);
		return true;
	}

	# Das gleiche fuer Store-Categories.
	# Nur: Es wird immer der ganze Kategorie-Baum abgerufen (die Datenmenge ist uebersichtlich)
	public static function importeBayStoreCategories() {
		try {
			$categories = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'GetStoreCategories',
			));
		} catch (MagnaException $e) {
			$categories = array(
				'DATA' => false
			);
		}
		if (!is_array($categories['DATA']) || empty($categories['DATA'])) {
			return false;
		}
		// echo print_m($categories);
		$now = time();
		foreach($categories['DATA'] as &$curRow) {
			$curRow['SiteID'] = $curRow['mpID'];
			unset($curRow['mpID']);
			$curRow['InsertTimestamp'] = $now;
			$curRow['StoreCategory'] = '1';
		}
		$categories = array_values($categories['DATA']);
		MagnaDB::gi()->query('DELETE FROM '.TABLE_MAGNA_EBAY_CATEGORIES.' WHERE StoreCategory=\'1\'');
		#echo print_m($categories, '$categories');
		MagnaDB::gi()->batchinsert(TABLE_MAGNA_EBAY_CATEGORIES, $categories, true);
		return true;
	}

	private function geteBayCategories($ParentID = 0, $purge = false) {
		global $_MagnaSession;
		if ($purge) {
			MagnaDB::gi()->delete(TABLE_MAGNA_EBAY_CATEGORIES, array (
				'StoreCategory' => '0',
				'SiteID' => $this->SiteID
			));
		}
		if (0 == (int)$ParentID) { // (int), cause for PHP >= 8.1, '' != 0
			$whereCondition = 'CategoryID = ParentID';
		} else {
			$whereCondition = "CategoryID != ParentID AND ParentID = $ParentID";
		}
		if (getDBConfigValue(array($_MagnaSession['currentPlatform'].'.restrictToBusiness', 'val'), $_MagnaSession['mpID'], false)) {
			$whereCondition .= "
		           AND B2BVATEnabled = '1'";
		}

		$ebayCategories = MagnaDB::gi()->fetchArray('
		    SELECT SQL_CALC_FOUND_ROWS DISTINCT CategoryID, SiteID, CategoryName,
		           CategoryLevel, ParentID, LeafCategory
		      FROM '.TABLE_MAGNA_EBAY_CATEGORIES.'
		     WHERE '.$whereCondition.'
		           AND StoreCategory = \'0\'
                   AND SiteID = '.$this->SiteID.'
		           AND InsertTimestamp > UNIX_TIMESTAMP() - '.self::EBAY_CAT_VALIDITY_PERIOD.'
		  ORDER BY CategoryName ASC
		');
        $countFoundCategories = (int)MagnaDB::gi()->foundRows();

		# nichts gefunden? vom Server abrufen
        # Mit < 5 fuer den Fall dass Kategoriepfade zu einzelnen Kategorien geholt wurden
		if ($countFoundCategories < 5) {
			if (self::importeBayCategories($ParentID)) {
				# Wenn Daten bekommen, noch mal select
				$ebayCategories = MagnaDB::gi()->fetchArray('
				    SELECT DISTINCT CategoryID, SiteID, CategoryName,
				           CategoryLevel, ParentID, LeafCategory
			  	      FROM '.TABLE_MAGNA_EBAY_CATEGORIES.'
			 	     WHERE '.$whereCondition.'
				           AND StoreCategory = \'0\'
                           AND SiteID = '.$this->SiteID.'
			 	  ORDER BY CategoryName ASC
				');
			}
		}

		if (empty($ebayCategories)) {
			return false;
		}
		return $ebayCategories;
	}

	private function geteBayStoreCategories($ParentID = 0, $purge = false) {
		if ($purge) {
			MagnaDB::gi()->delete(TABLE_MAGNA_EBAY_CATEGORIES, array (
				'StoreCategory' => '1',
			));
		}
		if (0 == (int)$ParentID) { // (int), cause for PHP >= 8.1, '' != 0
			$whereCondition = 'CategoryID = ParentID';
		} else {
			$whereCondition = 'CategoryID != ParentID AND ParentID = '.$ParentID;
		}
		# echo print_m(func_get_args(), __METHOD__);

		$ebayCategories = MagnaDB::gi()->fetchArray(eecho('
			SELECT DISTINCT CategoryID, SiteID, CategoryName,
				CategoryLevel, ParentID, LeafCategory
			  FROM '.TABLE_MAGNA_EBAY_CATEGORIES.'
			 WHERE '.$whereCondition.'
			 AND StoreCategory = \'1\'
			 AND InsertTimestamp > UNIX_TIMESTAMP() - '.self::EBAY_STORE_CAT_VALIDITY_PERIOD.'
			 ORDER BY CategoryName ASC
		', false));

		# nichts gefunden? vom Server abrufen
		if (empty($ebayCategories)) {
			if (self::importeBayStoreCategories($ParentID)) {
				# Wenn Daten bekommen, noch mal select
				$ebayCategories = MagnaDB::gi()->fetchArray('
					SELECT DISTINCT CategoryID, SiteID, CategoryName,
						CategoryLevel, ParentID, LeafCategory
			  		FROM '.TABLE_MAGNA_EBAY_CATEGORIES.'
			 		WHERE '.$whereCondition.'
					AND StoreCategory = \'1\'
			 		ORDER BY CategoryName ASC
				');
			}
		}
		if (empty($ebayCategories)) {
			return false;
		}
		return $ebayCategories;
	}

	private function rendereBayCategories($ParentID = 0, $purge = false) {
		#echo print_m(func_get_args(), __METHOD__);
		#echo var_dump_pre($this->isStoreCategory, '$this->isStoreCategory');
		if ($this->isStoreCategory) {
			$ebaySubCats = $this->geteBayStoreCategories($ParentID, $purge);
		} else {
			$ebaySubCats = $this->geteBayCategories($ParentID, $purge);
		}
		if ($ebaySubCats === false) {
			return '';
		}
		$ebayTopLevelList = '';
		foreach ($ebaySubCats as $item) {
			if (1 == $item['LeafCategory']) {
				$class = 'leaf';
			} else {
				$class = 'plus';
			}
			$ebayTopLevelList .= '
				<div class="catelem" id="y_'.$item['CategoryID'].'">
					<span class="toggle '.$class.'" id="y_toggle_'.$item['CategoryID'].'">&nbsp;</span>
					<div class="catname" id="y_select_'.$item['CategoryID'].'">
						<span class="catname">'.fixHTMLUTF8Entities($item['CategoryName']).'</span>
					</div>
				</div>';
		}
		return $ebayTopLevelList;
	}

	# dummy
	private function renderShopCategories() {
		return '';
	}
	# Artikel-Auswahl anzeigen. Spaeter schauen ob wir das auch strukturiert mit Kategorien machen,
	# aber erschtmal flat.
	private function renderSelection() {
		$selection = $this->getSelection();
		if ($selection === false) {
			return '';
		}
		$itemList = '';
		foreach ($selection as $item) {
			$itemList .= '
				<div class="catelem" id="y_'.$item['SKU'].'">
					<span class="toggle leaf" id="y_toggle_'.$item['SKU'].'">&nbsp;</span>
					<div class="catname" id="y_select_'.$item['SKU'].'">
						<span class="catname">'.fixHTMLUTF8Entities($item['products_name'].' ('.$item['SKU'].')').'</span>
					</div>
				</div>';
		}
		return $itemList;

	}

	private function rendereBayCategoryItem($id) {
		return '
			<div id="yc_'.$id.'" class="ebayCategory">
				<div id="y_remove_'.$id.'" class="y_rm_handle">&nbsp;</div><div class="ycpath">'.geteBayCategoryPath($id, $this->isStoreCategory).'</div>
			</div>';
	}

	public function renderView() {
		global $_MagnaSession;
		if (getDBConfigValue(array($_MagnaSession['currentPlatform'].'.restrictToBusiness', 'val'), $_MagnaSession['mpID'], false)) {
			$sNotice = '<tr><td><div class="noticeBox">'.ML_EBAY_ONLY_B2B_CATS.'</div></td></tr>';
		} else {
			$sNotice = '';
		}
		$html = '
			<div id="ebayCategorySelector" class="dialog2" title="'.ML_EBAY_LABEL_SELECT_CATEGORY.'">
				<table id="catMatch"><tbody>
					'.$sNotice.'
					<tr>
						<td id="ebayCats" class="catView"><div class="catView">'.$this->rendereBayCategories('').'</div></td>
					</tr>
					<!--<tr>
						<td id="selectedeBayCategory" class="catView"><div class="catView"></div></td>
					</tr>-->
				</tbody></table>
				<div id="messageDialog" class="dialog2"></div>
			</div>
		';
		ob_start();
?>
<script type="text/javascript">/*<![CDATA[*/
var selectedEBayCategory = '';
var madeChanges = false;
var isStoreCategory = false;
var finalEbayCategoryPath = '';

function collapseAllNodes(elem) {
	$('div.catelem span.toggle:not(.leaf)', $(elem)).each(function() {
		$(this).removeClass('minus').addClass('plus');
		$(this).parent().children('div.catname').children('div.catelem').css({display: 'none'});
	});
	$('div.catname span.catname.selected', $(elem)).removeClass('selected').css({'font-weight':'normal'});
}

function resetEverything() {
	madeChanges = false;
	collapseAllNodes($('#ebayCats'));
	/* Expand Top-Node */
	$('#s_toggle_0').removeClass('plus').addClass('minus').parent().children('div.catname').children('div.catelem').css({display: 'block'});
	$('#selectedeBayCategory div.catView').empty();
	selectedEBayCategory = '';
}

function selectEBayCategory(yID, html) {
	madeChanges = true;
	$('#selectedeBayCategory div.catView').html(html);

	selectedEBayCategory = yID;
	myConsole.log('selectedeBayCategory', selectedEBayCategory);

	//$('#ebayCats div.catname span.catname.selected').removeClass('selected').css({'font-weight':'normal'});
	$('#ebayCats div.catView').find('span.catname.selected').removeClass('selected').css({'font-weight':'normal'});
	$('#ebayCats div.catView').find('span.toggle.tick').removeClass('tick');

	$('#'+yID+' span.catname').addClass('selected').css({'font-weight':'bold'});
	$('#'+yID+' span.catname').parents().prevAll('span.catname').addClass('selected').css({'font-weight':'bold'});
	$('#'+yID+' span.catname').parents().prev('span.toggle').addClass('tick');
}

function clickEBayCategory(elem) {
    // hier Kategorien zuordnen, zu allen ausgewaehlten Items
	tmpNewID = $(elem).parent().attr('id');

	jQuery.ajax({
		type: 'POST',
		url: '<?php echo toURL($this->url, array('where' => 'prepareView', 'kind' => 'ajax'), true);?>',
		data: {
			'action': 'rendereBayCategoryItem',
			'id': tmpNewID,
			'isStoreCategory': isStoreCategory
		},
		success: function(data) {
			selectEBayCategory(tmpNewID, data);
		},
		error: function() {
		},
		dataType: 'html'
	});
}

function addeBayCategoriesEventListener(elem) {
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
							'action': 'geteBayCategories',
							'objID': tmpElem.attr('id'),
							'isStoreCategory': isStoreCategory
						},
						success: function(data) {
							appendTo = tmpElem.parent().children('div.catname');
							appendTo.append(data);
							addeBayCategoriesEventListener(appendTo);
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
			clickEBayCategory($(this).parent().children('div.catname').children('span.catname'));
		});
		$(this).parent().children('div.catname').children('span.catname').each(function() {
			$(this).click(function () {
				clickEBayCategory($(this));
			});
			if ($(this).parent().attr('id') == selectedEBayCategory) {
				$(this).addClass('selected').css({'font-weight':'bold'});
			}
		});
	});
}

function returnCategoryID() {
	if (selectedEBayCategory == '') {
		$('#messageDialog').html(
			'Bitte w&auml;hlen Sie eine eBay-Kategorie aus.'
		).jDialog({
			title: '<?php echo ML_LABEL_NOTE; ?>'
		});
		return false;
	}
	cID = selectedEBayCategory;
	cID = str_replace('y_select_', '', cID);
	resetEverything();
	return cID;
}

function generateEbayCategoryPath(cID, viewElem) {
    viewElem.find('option').attr('selected','');
    if(viewElem.find('[value='+cID+']').length>0){
        viewElem.find('[value='+cID+']').attr('selected','selected');
    }else{
	jQuery.ajax({
		type: 'POST',
		url: '<?php echo toURL($this->url, array('where' => 'prepareView', 'kind' => 'ajax'), true);?>',
		data: {
			'action': 'geteBayCategoryPath',
			'id': cID,
			'isStoreCategory': isStoreCategory
		},
		success: function(data) {
			finalEbayCategoryPath = data;
//			viewElem.html(data);
			viewElem.find('select').append('<option selected="selected" value="'+cID+'">'+data+'</option>').change();
		},
		error: function() {
		},
		dataType: 'html'
	});
    }
}

function VariationsEnabled(cID, viewElem) {
	jQuery.ajax({
		type: 'POST',
		url: '<?php echo toURL($this->url, array('where' => 'prepareView', 'kind' => 'ajax'), true);?>',
		data: {
			'action': 'VariationsEnabled',
			'id': cID
		},
		success: function(data) {
			var msg;
			if(data == 'true') msg='<?php echo ML_EBAY_NOTE_VARIATIONS_ENABLED ?>';
			else msg='<?php echo ML_EBAY_NOTE_VARIATIONS_DISABLED ?>';
			viewElem.html(msg);
		},
		error: function() {
		},
		dataType: 'html'
	});
}

function VariationsEnabled2(cID1, cID2, viewElem) {
	jQuery.ajax({
		type: 'POST',
		url: '<?php echo toURL($this->url, array('where' => 'prepareView', 'kind' => 'ajax'), true);?>',
		data: {
			'action': 'VariationsEnabled2',
			'id1': cID1,
			'id2': cID2
		},
		success: function(data) {
			var msg;
			if(data == 'true') msg='<?php echo ML_EBAY_NOTE_VARIATIONS_ENABLED ?>';
			else msg='<?php echo ML_EBAY_NOTE_VARIATIONS_DISABLED ?>';
			viewElem.html(msg);
		},
		error: function() {
		},
		dataType: 'html'
	});
}

function ProductRequired(cID, viewElem) {
	jQuery.ajax({
		type: 'POST',
		url: '<?php echo toURL($this->url, array('where' => 'prepareView', 'kind' => 'ajax'), true);?>',
		data: {
			'action': 'ProductRequired',
			'id': cID
		},
		success: function(data) {
			var msg;
			if(data == 'true') msg='<?php echo '<table style="vertical-align:middle"><tr><td><div class="noticeBox">'.ML_EBAY_NOTE_PRODUCT_REQUIRED_SHORT.'</div></td><td><div class="gfxbutton info" id="infobuttonProductRequired" title="Infos"><span>'.ML_EBAY_NOTE_PRODUCT_REQUIRED.'</span></div></td></tr></table><div id="infobuttonProductRequiredDialog" class="dialog2" title="'.ML_LABEL_NOTE.'"></div>'; ?>';
			else msg='';
			viewElem.html(msg);
		},
		error: function() {
		},
		dataType: 'html'
	});
	window.setTimeout('productRequiredInfoPopup()', 500);
}

function productRequiredInfoPopup() {
	$('div#infobuttonProductRequired').off();
	$('div#infobuttonProductRequired').click(function() {
		$('#infobuttonProductRequiredDialog').html($('span', this).html()).jDialog();
	});
	window.setTimeout('productRequiredInfoPopup()', 500);
}

function GetConditionValues(cID, viewElem, defaultConditionID) {
	jQuery.ajax({
		type: 'POST',
		url: '<?php echo toURL($this->url, array('where' => 'prepareView', 'kind' => 'ajax'), true);?>',
		data: {
			'action': 'GetConditionValues',
			'id': cID,
			'defaultConditionID': defaultConditionID
		},
		success: function(data) {
			viewElem.html(data);
		},
		error: function() {
		},
		dataType: 'html'
	});
}

function GetConditionDescriptors(iCategoryID, iConditionID, viewElem, defaultDescriptors) {
	jQuery.ajax({
		type: 'POST',
		url: '<?php echo toURL($this->url, array('where' => 'prepareView', 'kind' => 'ajax'), true);?>',
		data: {
			'action': 'GetConditionDescriptors',
			'iCategoryID': iCategoryID,
			'iConditionID': iConditionID,
			'defaultDescriptors': defaultDescriptors
		},
		success: function(data) {
			viewElem.html(data);
		},
		error: function() {
		},
		dataType: 'html'
	});
}

function initEBayCategories(purge) {
	purge = purge || false;
	myConsole.log('isStoreCategory', isStoreCategory);
	jQuery.ajax({
		type: 'POST',
		url: '<?php echo toURL($this->url, array('where' => 'prepareView', 'kind' => 'ajax'), true);?>',
		data: {
			'action': 'geteBayCategories',
			'objID': '',
			'isStoreCategory': isStoreCategory,
			'purge': purge ? 'true' : 'false'
		},
		success: function(data) {
			$('#ebayCats > div.catView').html(data);
			addeBayCategoriesEventListener($('#ebayCats'));
		},
		error: function() {
		},
		dataType: 'html'
	});
}

function startCategorySelector(callback, kind) {
	newStoreState = (kind == 'store');
	if (newStoreState != isStoreCategory) {
		isStoreCategory = newStoreState;
		$('#ebayCats > div.catView').html('');
		initEBayCategories();
	}

	$('#ebayCategorySelector').jDialog({
		width: '75%',
		minWidth: '300px',
		buttons: {
			'<?php echo ML_BUTTON_LABEL_ABORT; ?>': function() {
				$(this).dialog('close');
			},
			'<?php echo ML_BUTTON_LABEL_OK; ?>': function() {
				cID = returnCategoryID();
				if (cID != false) {
					callback(cID);
					$(this).dialog('close');
				}
			}
		},
		open: function(event, ui) {
			var tbar = $('#ebayCategorySelector').parent().find('.ui-dialog-titlebar');
			if (tbar.find('.ui-icon-arrowrefresh-1-n').length == 0) {
				var rlBtn = $('<a class="ui-dialog-titlebar-close ui-corner-all ui-state-focus" '+
					'role="button" href="#" style="right: 2em; padding: 0px;">'+
			    		'<span class="ui-icon ui-icon-arrowrefresh-1-n">reload</span>'+
			    	'</a>')
			    tbar.append(rlBtn);
			    rlBtn.click(function (event) {
			    	event.preventDefault();
			    	initEBayCategories(true);
			    });
			}
		}
	});
}

	var mpCategorySelector = (function() {
		return {
			addCategoriesEventListener: addeBayCategoriesEventListener,
			getCategoryPath: function(e) {
				e.html(finalEbayCategoryPath);
			},
			startCategorySelector: startCategorySelector
		}
	})();

$(document).ready(function() {
		mpCategorySelector.addCategoriesEventListener($('#ebayCats'));
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
			if (($pos = strrpos($_POST['id'], '_')) !== false) {
				$id = substr($_POST['id'], $pos+1);
			} else {
				$id = $_POST['id'];
			}
		}
		$id1 = $id2 = ''; // for VariationsEnabled2
		if (isset($_POST['id1'])) {
			$id1 = $_POST['id1'];
		}
		if (isset($_POST['id2'])) {
			$id2 = $_POST['id2'];
		}

		$iCategoryID = $iConditionID = '';
		if (isset($_POST['iCategoryID'])) {
			$iCategoryID = $_POST['iCategoryID'];
		}
		if (isset($_POST['iConditionID'])) {
			$iConditionID = $_POST['iConditionID'];
		}
		
		$this->isStoreCategory = (array_key_exists('isStoreCategory', $_POST))
			? (($_POST['isStoreCategory'] == 'false')
				? false
				: true
			  )
			: false;

		switch ($_POST['action']) {
			/*case 'getCategoryPath': {
				$_timer = microtime(true);
				$cID = (int)$id;
				$yIDs = MagnaDB::gi()->fetchArray('
					SELECT ebay_category_id
					  FROM '.TABLE_MAGNA_EBAY_CATEGORYMATCHING.'
					 WHERE category_id=\''.$cID.'\'', true
				);
				$ebayCategories = array();
				if (!empty($yIDs)) {
					foreach ($yIDs as $yID) {
						$ebayCategories[] = array(
							'origID' => 'y_select_'.$yID,
							'html' => $this->rendereBayCategoryItem($yID)
						);
					}
				}
				$shopCatHtml = renderCategoryPath($cID);
				return json_encode(array(
					'shopCatHtml' => $shopCatHtml,
					'yCategories' => $ebayCategories,
					'timer' => microtime2human(microtime(true) -  $_timer)
				));
				break;
			}*/
			case 'geteBayCategories': {
				return $this->rendereBayCategories(
					empty($_POST['objID'])
						? 0
						: str_replace('y_toggle_', '', $_POST['objID']),
					isset($_POST['purge']) ? $_POST['purge'] : false
				);
				break;
			}
			#case 'getShopCategories': {
			#	return $this->renderShopCategories(str_replace('s_toggle_', '', $_POST['cID']));
			#	break;
			#}
			# dummy
			case 'rendereBayCategoryItem': {
				return $this->rendereBayCategoryItem($id);
			}
			case 'geteBayCategoryPath': {
				return geteBayCategoryPath($id, $this->isStoreCategory);
			}
			case 'VariationsEnabled': {
				return VariationsEnabled($id)?'true':'false';
			}
			case 'VariationsEnabled2': {
				if (empty($id2)) {
					return VariationsEnabled($id1)?'true':'false';
				} else {
					return (    VariationsEnabled($id1)
					         && VariationsEnabled($id2)) ?'true':'false';
				}
			}
			case 'ProductRequired': {
				return ProductRequired($id)?'true':'false';
			}
			case 'GetConditionValues': {
				$conditionValues = GetConditionValues($id);
				if (false == $conditionValues) return '<input type="hidden" name="ConditionID" id="ConditionID" value="0">'."\n"
				. ML_EBAY_NO_CONDITIONS_APPLICABLE_FOR_CAT;
				$isSelected = false;
				$maxID = max(array_keys($conditionValues));
				if (!array_key_exists('defaultConditionID', $_POST)) $_POST['defaultConditionID'] = 1000;
				$html = '<select name="ConditionID" id="ConditionID">'."\n";
					foreach ($conditionValues as $cid => $name) {
						if ((($cid >= $_POST['defaultConditionID']) || ($cid == $maxID))
						     && (!$isSelected)) {
							# if the default is not in the list, take the next higher value
							$html .=  "<option selected value=$cid>$name</option>\n";
							$isSelected = true;
						} else {
							$html .=  "<option value=$cid>$name</option>\n";
						}
					}
				$html .= "</select>\n";
				return $html;
			}
			case 'GetConditionDescriptors': {
				$conditionDescriptors = GetConditionDescriptors($iCategoryID, $iConditionID);
				if (false == $conditionDescriptors) return '';
				if(array_key_exists('defaultDescriptors', $_POST)
				    && !empty($_POST['defaultDescriptors'])) {
					$defaultDescriptors = $_POST['defaultDescriptors'];
				} else {
					$defaultDescriptors = array();
				}
				$html = "<table>\n";
				foreach ($conditionDescriptors['conditionDescriptors'] as $iDescriptorId => $aDescriptor) {
					$html .= '<tr><td>'.$aDescriptor['conditionDescriptorName'].'</td><td>';
					if ($aDescriptor['conditionDescriptorConstraint']['mode'] == 'SELECTION_ONLY') {
						$html .= '<select name="ConditionDescriptors['.$iDescriptorId.']" id="ConditionDescriptors_'.$iDescriptorId.'">'."\n";
						foreach ($aDescriptor['conditionDescriptorValues'] as $sDescriptorID => $sDescriptorName) {
							$html .= "<option ". ((array_key_exists($iDescriptorId, $defaultDescriptors) && $defaultDescriptors[$iDescriptorId] == $sDescriptorID ) ? 'selected ' : '')."value=$sDescriptorID>$sDescriptorName</option>\n";
						}
						$html .= "</select>\n";
					} else {
						$html .= '<input type="text" name="ConditionDescriptors['.$iDescriptorId.']" id="ConditionDescriptors_'.$iDescriptorId.'" '.(array_key_exists($iDescriptorId, $defaultDescriptors) ? 'value="'.$defaultDescriptors[$iDescriptorId].'"':'').'>';
					}
					$html .= "</tr></td>\n";
				}
				$html .= "</table>\n";
				return $html;
			}
			case 'saveCategoryMatching': {
				if (!isset($_POST['selectedShopCategory']) || empty($_POST['selectedShopCategory']) ||
					(isset($_POST['selectedeBayCategories']) && !is_array($_POST['selectedeBayCategories']))
				) {
					return json_encode(array(
						'debug' => var_dump_pre($_POST['selectedeBayCategories'], true),
						'error' => preg_replace('/\s\s+/', ' ', ML_EBAY_ERROR_SAVING_INVALID_EBAY_CATS)
					));
				}

				$cID = str_replace('s_select_', '', $_POST['selectedShopCategory']);
				if (!ctype_digit($cID)) {
					return json_encode(array(
						'debug' => var_dump_pre($cID, true),
						'error' => preg_replace('/\s\s+/', ' ', ML_EBAY_ERROR_SAVING_INVALID_SHOP_CAT)
					));
				}
				$cID = (int)$cID;

				if (isset($_POST['selectedeBayCategories']) && !empty($_POST['selectedeBayCategories'])) {
					$ebayIDs = array();
					foreach ($_POST['selectedeBayCategories'] as $tmpYID) {
						$tmpYID = str_replace('y_select_', '', $tmpYID);
						if (preg_match('/^[0-9]{2}-[0-9]{2}-[0-9]{2}$/', $tmpYID)) {
							$ebayIDs[] = $tmpYID;
						}
					}
					if (empty($ebayIDs)) {
						return json_encode(array(
							'error' => preg_replace('/\s\s+/', ' ', ML_EBAY_ERROR_SAVING_INVALID_EBAY_CATS_ALL)
						));
					}
					#MagnaDB::gi()->delete(TABLE_MAGNA_EBAY_CATEGORYMATCHING, array (
					#	'category_id' => $cID
					#));
					#foreach ($ebayIDs as $yID) {
/*
	Hier muss stehen:
	fuer alle ausgewaehlten produkte:
	insert(TABLE_MAGNA_EBAY_PROPERTIES, ...)
	Wobei: Kategorie-Auswahl haben wir, ABER wir brauchen noch die Auswahl von dem ganzen anderen Zeug.
*/
					#	MagnaDB::gi()->insert(TABLE_MAGNA_EBAY_CATEGORYMATCHING, array (
					#		'category_id' => $cID,
					#		'ebay_category_id' => $yID
					#	));
					#}
				} else {
					#MagnaDB::gi()->delete(TABLE_MAGNA_EBAY_CATEGORYMATCHING, array (
					#	'category_id' => $cID
					#));
				}

				return json_encode(array(
					'error' => ''
				));

				break;
			}
            case 'DBMatchingColumns':
                $columns = MagnaDB::gi()->getTableCols($_POST['Table']);
                $editedColumns = array();
                foreach ($columns as $column) {
                    $editedColumns[$column] = $column;
                }

                return json_encode($editedColumns, JSON_FORCE_OBJECT);
                break;
			default: {
				return json_encode(array(
					'error' => ML_EBAY_ERROR_REQUEST_INVALID
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

	public function getMPCategoryPath($id) {
		return geteBayCategoryPath($id, false);
	}

	public function renderMatching() {
		return $this->renderView();
	}
}
