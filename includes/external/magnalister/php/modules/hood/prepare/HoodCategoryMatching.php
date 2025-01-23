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

class HoodCategoryMatching {
	const HOOD_CAT_VALIDITY_PERIOD = 86400; # Nach welcher Zeit werden Kategorien ungueltig (Sekunden)
	const HOOD_STORE_CAT_VALIDITY_PERIOD = 600; # Nach welcher Zeit werden Store-Kategorien ungueltig (Sekunden)
	
	private $request = 'view';
	private $isStoreCategory = false;

	private $url;

	public function __construct($request = 'view') {
		global $_url;
		
		$this->request = $request;
		$this->url = $_url;
	}
	
	# Die Funktion wird verwendet beim Aufruf der Kategorie-Zuordnung, nicht vorher.
	# Beim Aufruf werden die Hauptkategorien gezogen,
	# und beim Anklicken der einzelnen Kategorie die Kind-Kategorien, falls noch nicht vorhanden.
	private static function importHoodCategories($ParentID = 0) {
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
		$delete_query = 'DELETE FROM '.TABLE_MAGNA_HOOD_CATEGORIES
			.' WHERE StoreCategory="0"
			AND ParentID = ';
		# ganz oben ist CategoryID == ParentID
		if (0 == $ParentID)	{
			$delete_query .= 'CategoryID';
		} else {
			$delete_query .= $ParentID.' AND ParentID <> 0';
		}
		MagnaDB::gi()->query($delete_query);
		MagnaDB::gi()->batchinsert(TABLE_MAGNA_HOOD_CATEGORIES, $categories['DATA'], true);
		return true;
	}
	
	# Das gleiche fuer Store-Categories.
	# Nur: Es wird immer der ganze Kategorie-Baum abgerufen (die Datenmenge ist uebersichtlich)
	public static function importHoodStoreCategories() {
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
			unset($curRow['Marketplace']);
			unset($curRow['Timestamp']);
			$curRow['InsertTimestamp'] = $now;
			$curRow['StoreCategory'] = '1';
		}
		$categories = array_values($categories['DATA']);
		foreach ($categories as &$category) {
			$category['LeafCategory'] = (string)$category['LeafCategory'];
		}
		MagnaDB::gi()->query('DELETE FROM '.TABLE_MAGNA_HOOD_CATEGORIES.' WHERE StoreCategory="1"');
		#echo print_m($categories, '$categories');
		MagnaDB::gi()->batchinsert(TABLE_MAGNA_HOOD_CATEGORIES, $categories, true);
		return true;
	}
	
	/** 
	 * Die Funktion wird verwendet beim Aufruf der Kategorie-Zuordnung, nicht vorher.
	 * Beim Aufruf werden die Hauptkategorien gezogen,
	 * und beim Anklicken der einzelnen Kategorie die Kind-Kategorien, falls noch nicht vorhanden.
	 */
	public function importHoodCategoryPath($categoryID) {
		try {
			$categories = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'GetCategoryWithAncestors',
				'DATA' => array(
					'CategoryID' => $categoryID
				),
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
			$curRow['StoreCategory'] = '0';
		}
		MagnaDB::gi()->batchinsert(TABLE_MAGNA_HOOD_CATEGORIES, $categories['DATA'], true);
		return true;
	}
	
	private function getHoodCategories($ParentID = 0, $purge = false) {
		if ($purge) {
			MagnaDB::gi()->delete(TABLE_MAGNA_HOOD_CATEGORIES, array (
				'StoreCategory' => '0'
			));
		}
		if (0 == (int)$ParentID) {
			$whereCondition = '0 = ParentID';
		} else {
			$whereCondition = "0 != ParentID AND ParentID = $ParentID";
		}
		
		$hoodCategories = MagnaDB::gi()->fetchArray('
		    SELECT SQL_CALC_FOUND_ROWS DISTINCT CategoryID, CategoryName,
		           ParentID, LeafCategory
		      FROM '.TABLE_MAGNA_HOOD_CATEGORIES.'
		     WHERE '.$whereCondition.'
		           AND StoreCategory = "0"
		           AND InsertTimestamp > UNIX_TIMESTAMP() - '.self::HOOD_CAT_VALIDITY_PERIOD.'
		  ORDER BY CategoryName ASC
		');
		$countFoundCategories = (int)MagnaDB::gi()->foundRows();
		
		# nichts gefunden? vom Server abrufen
		# Mit < 5 fuer den Fall dass Kategoriepfade zu einzelnen Kategorien geholt wurden
		if ($countFoundCategories < 5) {
			if (self::importHoodCategories($ParentID)) {
				# Wenn Daten bekommen, noch mal select
				$hoodCategories = MagnaDB::gi()->fetchArray('
				    SELECT DISTINCT CategoryID, CategoryName,
				           ParentID, LeafCategory
				      FROM '.TABLE_MAGNA_HOOD_CATEGORIES.'
				     WHERE '.$whereCondition.'
				           AND StoreCategory = "0"
				  ORDER BY CategoryName ASC
				');
			}
		}

		if (empty($hoodCategories)) {
			return false;
		}
		return $hoodCategories;
	}

	private function getHoodStoreCategories($ParentID = 0, $purge = false) {
		if ($purge) {
			MagnaDB::gi()->delete(TABLE_MAGNA_HOOD_CATEGORIES, array (
				'StoreCategory' => '1',
			));
		}
		if (0 == (int)$ParentID) {
			$whereCondition = ' 0 = ParentID ';
		} else {
			$whereCondition = ' 0 != ParentID AND ParentID = '.$ParentID;
		}
		# echo print_m(func_get_args(), __METHOD__);
		
		$hoodCategories = MagnaDB::gi()->fetchArray(eecho('
		    SELECT DISTINCT CategoryID, CategoryName,
		           ParentID, LeafCategory
		      FROM '.TABLE_MAGNA_HOOD_CATEGORIES.'
		     WHERE '.$whereCondition.'
		           AND StoreCategory = "1"
		           AND InsertTimestamp > UNIX_TIMESTAMP() - '.self::HOOD_STORE_CAT_VALIDITY_PERIOD.'
		  ORDER BY CategoryName ASC
		', false));
		
		# nichts gefunden? vom Server abrufen
		if (empty($hoodCategories)) {
			if (self::importHoodStoreCategories($ParentID)) {
				# Wenn Daten bekommen, noch mal select
				$hoodCategories = MagnaDB::gi()->fetchArray('
				    SELECT DISTINCT CategoryID, CategoryName,
				           ParentID, LeafCategory
				      FROM '.TABLE_MAGNA_HOOD_CATEGORIES.'
				     WHERE '.$whereCondition.'
				           AND StoreCategory = "1"
				  ORDER BY CategoryName ASC
				');
			}
		}
		if (empty($hoodCategories)) {
			return false;
		}
		return $hoodCategories;
	}
	
	public function getHoodCategoryPath($categoryID, $storeCategory = false, $justImported = false) {
		$appendedText = '&nbsp;<span class="cp_next">&gt;</span>&nbsp;';
		
		$storeCategory = $storeCategory ? '1' : '0';
		$catPath = '';
		do {
			# Ermittle Namen, CategoryID und ParentID,
			# dann das gleiche fuer die ParentCategory usw.
			# bis bei Top angelangt (0 = ParentID)
			$yCP = MagnaDB::gi()->fetchRow('
			    SELECT CategoryID, CategoryName , ParentID
			      FROM ' . TABLE_MAGNA_HOOD_CATEGORIES . '
			     WHERE CategoryID="' . $categoryID . '"
			           AND StoreCategory="' . $storeCategory . '"
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
				$this->importHoodStoreCategories();
			} else {
				$this->importHoodCategoryPath($categoryID);
			}
			return $this->getHoodCategoryPath($categoryID, $storeCategory, true);
		}
		return $catPath;
	}
	
	private function renderHoodCategories($ParentID = 0, $purge = false) {
		#echo print_m(func_get_args(), __METHOD__);
		#echo var_dump_pre($this->isStoreCategory, '$this->isStoreCategory');
		if ($this->isStoreCategory) {
			$hoodSubCats = $this->getHoodStoreCategories($ParentID, $purge);
		} else {
			$hoodSubCats = $this->getHoodCategories($ParentID, $purge);
		}
		if ($hoodSubCats === false) {
			return '';
		}
		$hoodTopLevelList = '';
		foreach ($hoodSubCats as $item) {
			if (1 == $item['LeafCategory']) {
				$class = 'leaf';
			} else {
				$class = 'plus';
			}
			$hoodTopLevelList .= '
				<div class="catelem" id="y_'.$item['CategoryID'].'">
					<span class="toggle '.$class.'" id="y_toggle_'.$item['CategoryID'].'">&nbsp;</span>
					<div class="catname" id="y_select_'.$item['CategoryID'].'">
						<span class="catname">'.fixHTMLUTF8Entities($item['CategoryName']).'</span>
					</div>
				</div>';
		}
		return $hoodTopLevelList;
	}
	

	private function renderHoodCategoryItem($id) {
		return '
			<div id="yc_'.$id.'" class="hoodCategory">
				<div id="y_remove_'.$id.'" class="y_rm_handle">&nbsp;</div><div class="ycpath">'.$this->getHoodCategoryPath($id, $this->isStoreCategory).'</div>
			</div>';
	}

	public function renderView() {
		$html = '
			<div id="hoodCategorySelector" class="dialog2" title="'.ML_HOOD_LABEL_SELECT_CATEGORY.'">
				<table id="catMatch"><tbody>
					<tr>
						<td id="hoodCats" class="catView"><div class="catView">'.$this->renderHoodCategories('').'</div></td>
					</tr>
					<!--<tr>
						<td id="selectedHoodCategory" class="catView"><div class="catView"></div></td>
					</tr>-->
				</tbody></table>
				<div id="messageDialog" class="dialog2"></div>
			</div>
		';
		ob_start();
?>
<script type="text/javascript">/*<![CDATA[*/
var selectedHoodCategory = '';
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
	collapseAllNodes($('#hoodCats'));
	/* Expand Top-Node */
	$('#s_toggle_0').removeClass('plus').addClass('minus').parent().children('div.catname').children('div.catelem').css({display: 'block'});
	$('#selectedHoodCategory div.catView').empty();
	selectedHoodCategory = '';
}

function selectHoodCategory(yID, html) {
	madeChanges = true;
	$('#selectedHoodCategory div.catView').html(html);

	selectedHoodCategory = yID;
	myConsole.log('selectedHoodCategory', selectedHoodCategory);

	//$('#hoodCats div.catname span.catname.selected').removeClass('selected').css({'font-weight':'normal'});
	//$('#'+yID+' span.catname').addClass('selected').css({'font-weight':'bold'});
	
	$('#hoodCats div.catView').find('span.catname.selected').removeClass('selected').css({'font-weight':'normal'});
	$('#hoodCats div.catView').find('span.toggle.tick').removeClass('tick');
	
	$('#'+yID+' span.catname').addClass('selected').css({'font-weight':'bold'});
	$('#'+yID+' span.catname').parents().prevAll('span.catname').addClass('selected').css({'font-weight':'bold'});
	$('#'+yID+' span.catname').parents().prev('span.toggle').addClass('tick');
}

function clickHoodCategory(elem) {
	// hier Kategorien zuordnen, zu allen ausgewaehlten Items
	tmpNewID = $(elem).parent().attr('id');

	jQuery.ajax({
		type: 'POST',
		url: '<?php echo toURL($this->url, array('where' => 'prepareView', 'kind' => 'ajax'), true);?>',
		data: {
			'action': 'renderHoodCategoryItem',
			'id': tmpNewID,
			'isStoreCategory': isStoreCategory
		},
		success: function(data) {
			selectHoodCategory(tmpNewID, data);
		},
		error: function() {
		},
		dataType: 'html'
	});
}

function addHoodCategoriesEventListener(elem) {
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
							'action': 'getHoodCategories',
							'objID': tmpElem.attr('id'),
							'isStoreCategory': isStoreCategory
						},
						success: function(data) {
							appendTo = tmpElem.parent().children('div.catname');
							appendTo.append(data);
							addHoodCategoriesEventListener(appendTo);
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
			clickHoodCategory($(this).parent().children('div.catname').children('span.catname'));
		});
		$(this).parent().children('div.catname').children('span.catname').each(function() {
			$(this).click(function () {
				clickHoodCategory($(this));
			});
			if ($(this).parent().attr('id') == selectedHoodCategory) {
				$(this).addClass('selected').css({'font-weight':'bold'});	
			}
		});
	});
}

function returnCategoryID() {
	if (selectedHoodCategory == '') {
		$('#messageDialog').html(
			'Bitte w&auml;hlen Sie eine Hood-Kategorie aus.'
		).jDialog({
			title: '<?php echo ML_LABEL_NOTE; ?>'
		});
		return false;
	}
	cID = selectedHoodCategory;
	cID = str_replace('y_select_', '', cID);
	resetEverything();
	return cID;
}

function generateHoodCategoryPath(cID, viewElem) {
	viewElem.find('option').attr('selected','');
	if(viewElem.find('[value='+cID+']').length>0){
		viewElem.find('[value='+cID+']').attr('selected','selected');
	}else{
	jQuery.ajax({
		type: 'POST',
		url: '<?php echo toURL($this->url, array('where' => 'prepareView', 'kind' => 'ajax'), true);?>',
		data: {
			'action': 'getHoodCategoryPath',
			'id': cID,
			'isStoreCategory': isStoreCategory
		},
		success: function(data) {
//			viewElem.html(data);
			viewElem.find('select').append('<option selected="selected" value="' + cID + '">' + data + '</option>');
            $('#PrimaryCategory').trigger('change');
		},
		error: function() {
		},
		dataType: 'html'
	});
	}
}

function initHoodCategories(purge) {
	purge = purge || false;
	myConsole.log('isStoreCategory', isStoreCategory);
	jQuery.ajax({
		type: 'POST',
		url: '<?php echo toURL($this->url, array('where' => 'prepareView', 'kind' => 'ajax'), true);?>',
		data: {
			'action': 'getHoodCategories',
			'objID': '',
			'isStoreCategory': isStoreCategory,
			'purge': purge ? 'true' : 'false'
		},
		success: function(data) {
			$('#hoodCats > div.catView').html(data);
			addHoodCategoriesEventListener($('#hoodCats'));
		},
		error: function() {
		},
		dataType: 'html'
	});
}

function startCategorySelector(callback, kind) {
	newStoreState = (kind == 'store');
	if ((newStoreState != isStoreCategory) || ($('#hoodCats > div.catView').children().length == 0)) {
		isStoreCategory = newStoreState;
		$('#hoodCats > div.catView').html('');
		initHoodCategories();
	}
	
	$('#hoodCategorySelector').jDialog({
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
			var tbar = $('#hoodCategorySelector').parent().find('.ui-dialog-titlebar');
			if (tbar.find('.ui-icon-arrowrefresh-1-n').length == 0) {
				var rlBtn = $('<a class="ui-dialog-titlebar-close ui-corner-all ui-state-focus" '+
					'role="button" href="#" style="right: 2em; padding: 0px;">'+
						'<span class="ui-icon ui-icon-arrowrefresh-1-n">reload</span>'+
					'</a>')
				tbar.append(rlBtn);
				rlBtn.click(function (event) {
					event.preventDefault();
					initHoodCategories(true);
				});
			}
		}
	});
}
    var mpCategorySelector = (function () {
        return {
            addCategoriesEventListener: addHoodCategoriesEventListener,
            getCategoryPath: function (e) {
                e.html(finalHoodCategoryPath);
            },
            startCategorySelector: startCategorySelector
        }
    })();

    $(document).ready(function () {
        mpCategorySelector.addCategoriesEventListener($('#hoodCats')); // new 20190131
    });
</script>
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
		$this->isStoreCategory = (array_key_exists('isStoreCategory', $_POST))
			? (($_POST['isStoreCategory'] == 'false')
				? false
				: true
			  ) 
			: false;

		switch ($_POST['action']) {
			case 'getHoodCategories': {
				return $this->renderHoodCategories(
					empty($_POST['objID'])
						? 0
						: str_replace('y_toggle_', '', $_POST['objID']),
					isset($_POST['purge']) ? $_POST['purge'] : false
				);
				break;
			}
			case 'renderHoodCategoryItem': {
				return $this->renderHoodCategoryItem($id);
			}
			case 'getHoodCategoryPath': {
				return $this->getHoodCategoryPath($id, $this->isStoreCategory);
			}
			case 'saveCategoryMatching': {
				if (!isset($_POST['selectedShopCategory']) || empty($_POST['selectedShopCategory']) || 
					(isset($_POST['selectedHoodCategories']) && !is_array($_POST['selectedHoodCategories']))
				) {
					return json_encode(array(
						'debug' => var_dump_pre($_POST['selectedHoodCategories'], true),
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
				
				if (isset($_POST['selectedHoodCategories']) && !empty($_POST['selectedHoodCategories'])) {
					$hoodIDs = array();
					foreach ($_POST['selectedHoodCategories'] as $tmpYID) {
						$tmpYID = str_replace('y_select_', '', $tmpYID);
						if (preg_match('/^[0-9]{2}-[0-9]{2}-[0-9]{2}$/', $tmpYID)) {
							$hoodIDs[] = $tmpYID;
						}
					}
					if (empty($hoodIDs)) {
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
            case 'GetMpCategoryAttributes': {
                if (isset($_POST['cId'])) {
                    return json_encode(HoodHelper::gi()->getAttributesFromMP($_POST['cId']));
                } else {
                    return '';
                }
            }
			default: {
				return json_encode(array(
					'error' => ML_HOOD_ERROR_REQUEST_INVALID
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
