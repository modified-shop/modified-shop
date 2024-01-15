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
 * $Id: InventoryView.php 1224 2011-09-06 00:28:04Z derpapst $
 *
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once (DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');

class MagnaCompatibleInventoryView {
	protected $mpID = 0;
	protected $marketplace = '';

	protected $settings = array();
	protected $sort = array();

	protected $numberofitems = 0;
	protected $offset = 0;
	
	protected $renderableData = array();
		
	protected $simplePrice = null;
	protected $mpCurrency = '';
	protected $url = array();
	protected $magnasession = array();
	protected $magnaShopSession = array();

	protected $search = '';
	protected $additionalParameters = array();
	protected $saveDeletedLocally = true;

	public function __construct($settings = array()) {
		global $_MagnaShopSession, $_MagnaSession, $_url, $_modules;

		$this->marketplace = $_MagnaSession['currentPlatform'];
		$this->mpID = $_MagnaSession['mpID'];

		$this->settings = array_merge(array(
			'maxTitleChars'	=> 40,
			'itemLimit'		=> 50,
			'language'      => getDBConfigValue($this->marketplace.'.lang', $this->mpID, $_SESSION['languages_id']),
		), $settings);

		$this->simplePrice = new SimplePrice();
		$this->mpCurrency = getCurrencyFromMarketplace($this->mpID);
		$this->simplePrice->setCurrency($this->mpCurrency);
		$this->url = $_url;
		$this->url['view'] = 'inventory';
		$this->magnasession = &$_MagnaSession;
		$this->magnaShopSession = &$_MagnaShopSession;

		if (array_key_exists('tfSearch', $_POST) && !empty($_POST['tfSearch'])) {
			$this->search = $_POST['tfSearch'];
		} else if (array_key_exists('search', $_GET) && !empty($_GET['search'])) {
			$this->search = $_GET['search'];
		}
		
		if (isset($_POST['refreshStock'])) {
			$classFile = DIR_MAGNALISTER_MODULES.strtolower($this->marketplace).'/crons/'.ucfirst($this->marketplace).'SyncInventory.php';
			if (file_exists($classFile)) {
				require_once($classFile);
				$className = ucfirst($this->marketplace).'SyncInventory';
				if (class_exists($className)) {
					@set_time_limit(60 * 10);
					$ic = new $className($this->mpID, $this->marketplace);
					$ic->process();
				}
			}
		}
	}

	protected function getInventory() {
		try {
			$request = array(
				'ACTION' => 'GetInventory',
				'LIMIT' => $this->settings['itemLimit'],
				'OFFSET' => $this->offset,
				'ORDERBY' => $this->sort['order'],
				'SORTORDER' => $this->sort['type'],
			);
            $request = array_merge($request, $this->additionalParameters);
			if (!empty($this->search)) {
				#$request['SEARCH'] = (!magnalisterIsUTF8($this->search)) ? utf8_encode($this->search) : $this->search;
				$request['SEARCH'] = $this->search;
			}
			$result = MagnaConnector::gi()->submitRequest($request);
			$this->numberofitems = (int)$result['NUMBEROFLISTINGS'];
			return $result;

		} catch (MagnaException $e) {
			return false;
		}
	}

	protected function sortByType($type) {
		$tmpURL = $this->url;
		if (!empty($this->search)) {
			$tmpURL['search'] = urlencode($this->search);
		}
		return '
			<span class="nowrap">
				<a href="'.toURL($tmpURL, array('sorting' => $type.'')).'" title="'.ML_LABEL_SORT_ASCENDING.'" class="sorting">
					<img alt="'.ML_LABEL_SORT_ASCENDING.'" src="'.DIR_MAGNALISTER_WS_IMAGES.'sort_up.png" />
				</a>
				<a href="'.toURL($tmpURL, array('sorting' => $type.'-desc')).'" title="'.ML_LABEL_SORT_DESCENDING.'" class="sorting">
					<img alt="'.ML_LABEL_SORT_DESCENDING.'" src="'.DIR_MAGNALISTER_WS_IMAGES.'sort_down.png" />
				</a>
			</span>';
	}

	protected function getSortOpt() {
		if (isset($_GET['sorting'])) {
			$sorting = $_GET['sorting'];
		} else {
			$sorting = 'blabla'; // fallback for default
		}
		
		$fields = $this->getFields();
		$sortFlags = array ();
		foreach ($fields as $key => $fieldCfg) {
			if (empty($fieldCfg['Sorter'])) {
				continue;
			}
			$sortFlags[$fieldCfg['Sorter']] = $key;
		}
		
		#echo print_m($sortFlags, '$sortFlags');
		
		$order = 'ASC';
		if (strpos($sorting, '-desc') !== false) {
			$order = 'DESC';
			$sorting = str_replace('-desc', '', $sorting);
		}
		if (array_key_exists($sorting, $sortFlags)) {
			$this->sort['order'] = $sortFlags[$sorting];
			$this->sort['type']  = $order;
		} else {
			$this->sort['order'] = 'DateAdded';
			$this->sort['type']  = 'DESC';
		}
	}
	
	protected function postDelete() { /* Nix :-) */ }
	
	protected function initInventoryView() {
		if (isset($_POST['SKUs']) && is_array($_POST['SKUs']) && isset($_POST['action'])
			 //&& ($_SESSION['POST_TS'] != $_POST['timestamp']) // Re-Post Prevention
		) {
			$_SESSION['POST_TS'] = $_POST['timestamp'];
			switch ($_POST['action']) {
				case 'delete': {
					$skus = $_POST['SKUs'];
					$itemdata = array();
					$insertData = array();
					foreach ($skus as $sku) {
						$itemdata[] = array (
							'SKU' => $sku,
						);
						$pDetails = unserialize(str_replace('\\"', '"', $_POST['details'][$sku]));
						$pID = magnaSKU2pID($sku);
						$model = '';
						if ($pID > 0) {
							$model = (string)MagnaDB::gi()->fetchOne('
								SELECT products_model FROM '.TABLE_PRODUCTS.'
								 WHERE products_id=\''.$pID.'\'
							');
						}
						if (empty($model)) {
							$model = $sku;
						}
						$insertData[] = array (
							'products_id' => magnaSKU2pID($sku),
							'products_model' => $model,
							'mpID' => $this->mpID,
							'old_price' => $pDetails['Price'],
							'timestamp' => date('Y-m-d H:i:s')
						);
					}

					try {
						$result = MagnaConnector::gi()->submitRequest(array(
							'ACTION' => 'DeleteItems',
							'DATA' => $itemdata
						));
						call_user_func(ucfirst($this->marketplace).'Helper::processCheckinErrors', $result, $this->mpID);
					} catch (MagnaException $e) {
						$result = array (
							'STATUS' => 'ERROR'
						);
					}

					if ($this->saveDeletedLocally && $result['STATUS'] == 'SUCCESS') {
						MagnaDB::gi()->batchinsert(
							TABLE_MAGNA_COMPAT_DELETEDLOG,
							$insertData
						);
						$this->postDelete();
					}
					break;
				}
			}
		}

		$this->getSortOpt();

		if (isset($_GET['page']) && ctype_digit($_GET['page'])) {
			$this->offset = ($_GET['page'] - 1) * $this->settings['itemLimit'];
		} else {
			$this->offset = 0;
		}
	}

	protected function prepareInventoryItemData(&$item) {
		
	}

	public function prepareInventoryData() {
		global $magnaConfig;

		$result = $this->getInventory();
		if (($result !== false) && !empty($result['DATA'])) {
			$this->renderableData = $result['DATA'];
			foreach ($this->renderableData as &$item) {
				if (isset($item['ItemTitle'])) {
					$item['Title'] = $item['ItemTitle'];
					unset($item['ItemTitle']);
				}
				$this->prepareInventoryItemData($item);
				$pID = magnaSKU2pID($item['SKU']);
				if (is_array($this->settings['language'])) {
					$iLanguageId = current($this->settings['language']);
				} else {
					$iLanguageId = $this->settings['language'];
				}

				$item['MarketplaceTitle'] = $item['Title'];
				if(function_exists('mb_strlen')) {
                    $iLengthOfMarketplaceTitle = mb_strlen($item['MarketplaceTitle'], 'UTF-8');
                } else {
                    $iLengthOfMarketplaceTitle = strlen($item['MarketplaceTitle']);
                }
				$item['MarketplaceTitleShort'] = ($iLengthOfMarketplaceTitle > $this->settings['maxTitleChars'] + 2)
					? (fixHTMLUTF8Entities(mb_substr($item['MarketplaceTitle'], 0, $this->settings['maxTitleChars'], 'UTF-8')) . '&hellip;')
					: fixHTMLUTF8Entities($item['MarketplaceTitle']);

				$sTitle = (string)MagnaDB::gi()->fetchOne("
					SELECT products_name 
					  FROM ".TABLE_PRODUCTS_DESCRIPTION."
					 WHERE     products_id = '".$pID."'
					       AND language_id = '".$iLanguageId."'
				");
				$item['Title'] = '&mdash;';
				if (!empty($sTitle)) {
					$item['Title'] = $sTitle;
				}

                if(function_exists('mb_strlen')) {
                    $iLengthOfTitle = mb_strlen($item['Title'], 'UTF-8');
                } else {
                    $iLengthOfTitle = strlen($item['Title']);
                }
				$item['TitleShort'] = ($iLengthOfTitle > $this->settings['maxTitleChars'] + 2)
						? (fixHTMLUTF8Entities(mb_substr($item['Title'], 0, $this->settings['maxTitleChars'], 'UTF-8')).'&hellip;')
						: fixHTMLUTF8Entities($item['Title']);
			}
			unset($result);
		}

	}
	
	private function emptyStr2mdash($str) {
		return (empty($str) || (is_numeric($str) && ($str == 0))) ? '&mdash;' : $str;
	}
	
	protected function getFields() {
		return array(
			'SKU' => array (
				'Label' => ML_LABEL_SKU,
				'Sorter' => 'sku',
				'Getter' => null,
				'Field' => 'SKU'
			),
			'ItemID' => array (
				'Label' => ML_MAGNACOMPAT_LABEL_MP_ITEMID,
				'Sorter' => 'itemid',
				'Getter' => null,
				'Field' => 'ItemID',
			),
			'Title' => array (
				'Label' => ML_LABEL_SHOP_TITLE,
				'Sorter' => null,
				'Getter' => 'getTitle',
				'Field' => null,
 			),
 			'Price' => array (
 				'Label' => ML_GENERIC_PRICE,
 				'Sorter' => 'price',
 				'Getter' => 'getItemPrice',
 				'Field' => null
 			),
 			'Quantity' => array (
				'Label' => ML_LABEL_QUANTITY,
				'Sorter' => 'quantity',
				'Getter' => null,
				'Field' => 'Quantity',
			),
 			'DateAdded' => array (
 				'Label' => ML_GENERIC_CHECKINDATE,
 				'Sorter' => 'dateadded',
 				'Getter' => 'getItemDateAdded',
 				'Field' => null
 			),
		);
	}
	
	protected function getTitle($item) {
		return '<td title="'.fixHTMLUTF8Entities($item['Title'], ENT_COMPAT).'">'.$item['TitleShort'].'</td>';
	}

	protected function getMarketplaceTitle($item)
	{
		return '<td title="'.fixHTMLUTF8Entities($item['MarketplaceTitle'], ENT_COMPAT).'">'.$item['MarketplaceTitleShort'].'</td>';
	}
	
	protected function getItemPrice($item) {
		$item['Currency'] = isset($item['Currency']) ? $item['Currency'] : $this->mpCurrency;
		return '<td>'.$this->simplePrice->setPriceAndCurrency($item['Price'], $item['Currency'])->format().'</td>';
	}
	
	protected function getItemDateAdded($item) {
		$item['DateAdded'] = ((isset($item['DateAdded'])) ? strtotime($item['DateAdded']) : '');
		return '<td>'.date("d.m.Y", $item['DateAdded']).' &nbsp;&nbsp;<span class="small">'.date("H:i", $item['DateAdded']).'</span>'.'</td>';	
	}

	protected function renderDataGrid($id = '') {
		global $magnaConfig;

		#echo print_m($this->renderableData, '$this->renderableData');

		$html = '
			<table'.(($id != '') ? ' id="'.$id.'"' : '').' class="datagrid">
				<thead class="small"><tr>
					<td class="nowrap" style="width: 5px;">
						<input type="checkbox" id="selectAll"/><label for="selectAll">'.ML_LABEL_CHOICE.'</label>
					</td>';
		$fieldsDesc = $this->getFields();
		foreach ($fieldsDesc as $fdesc) {
			$html .= '
					<td>'.$fdesc['Label'].((isset($fdesc['Sorter']) && ($fdesc['Sorter'] != null)) ? ' '.$this->sortByType($fdesc['Sorter']) : '').'</td>';
		}
		$html .= '
				</tr></thead>
				<tbody>';
		
		$oddEven = false;
		foreach ($this->renderableData as $item) {
			$details = htmlspecialchars(str_replace('"', '\\"', serialize(array(
			 	'SKU' => $item['SKU'],
			 	'Price' => $item['Price'],
			 	'Currency' => isset($item['Currency']) ? $item['Currency'] : $this->mpCurrency,
			))));

			$addStyle = ($item['Title'] === '&mdash;' && $item['SKU'] !== '&mdash;') ? 'style="color:#900;"' : '';

			$html .= '
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'" ' . $addStyle . '>
					<td><input type="checkbox" name="SKUs[]" value="'.$item['SKU'].'">
						<input type="hidden" name="details['.$item['SKU'].']" value="'.$details.'"></td>';
			foreach ($fieldsDesc as $fdesc) {
				if (isset($fdesc['Getter']) && !empty($fdesc['Getter'])) {
					$html .= '
					'.call_user_func(array($this, $fdesc['Getter']), $item);
				} else {
					$html .= '
					<td>'.$item[$fdesc['Field']].'</td>';
				}
			}
			$html .= '	
				</tr>';
		}
		$html .= '
				</tbody>
			</table>';

		return $html;
	}

	public function renderInventoryTable() {
		$html = '';

		if (empty($this->renderableData)) {
			$this->prepareInventoryData();
		}

		$pages = ceil($this->numberofitems / $this->settings['itemLimit']);
		$tmpURL = $this->url;
		if (isset($_GET['sorting'])) {
			$tmpURL['sorting'] = $_GET['sorting'];
		}
		if (!empty($this->search)) {
			$tmpURL['search'] = urlencode($this->search);
		}
		$currentPage = 1;
		if (isset($_GET['page']) && ctype_digit($_GET['page']) && (1 <= (int)$_GET['page']) && ((int)$_GET['page'] <= $pages)) {
			$currentPage = (int)$_GET['page'];
		}

		$offset = $currentPage * $this->settings['itemLimit'] - $this->settings['itemLimit'] + 1;
		$limit = $offset + count($this->renderableData) - 1;
		$html .= '<table class="listingInfo"><tbody><tr>
					<td class="ml-pagination">
						'.(($this->numberofitems > 0)
							?	('<span class="bold">'.ML_LABEL_PRODUCTS.':&nbsp; '.
								 $offset.' bis '.$limit.' von '.($this->numberofitems).'&nbsp;&nbsp;&nbsp;&nbsp;</span>'
								)
							:	''
						).'
						<span class="bold">'.ML_LABEL_CURRENT_PAGE.':&nbsp; '.$currentPage.'</span>
					</td>
					<td class="textright">
						'.renderPagination($currentPage, $pages, $tmpURL).'
					</td>
				</tr></tbody></table>';

		if (!empty($this->renderableData)) {
			$html .= $this->renderDataGrid('csinventory');
		} else {
			$html .= '<table class="magnaframe"><tbody><tr><td>'.
						(empty($this->search) ? ML_GENERIC_NO_INVENTORY : ML_LABEL_NO_SEARCH_RESULTS).
					 '</td></tr></tbody></table>';
		}

		ob_start();
?>
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function() {
	$('#selectAll').click(function() {
		state = $(this).attr('checked') !== undefined;
		$('#csinventory input[type="checkbox"]:not([disabled])').each(function() {
			$(this).attr('checked', state);
		});
	});
});
/*]]>*/</script>
<?php
		$html .= ob_get_contents();	
		ob_end_clean();
		
		return $html;
	}
	
	public function renderActionBox() {
		global $_modules;

		$js = '';
		$left = (!empty($this->renderableData) ? 
			'<input type="button" class="ml-button" value="'.ML_BUTTON_LABEL_DELETE.'" id="listingDelete" name="listing[delete]"/>' : 
			''
		);
		$right = '<table class="right"><tbody>
			'.(in_array(getDBConfigValue($this->magnasession['currentPlatform'] . '.stocksync.tomarketplace', $this->mpID), array('abs', 'auto'))
				? '<tr><td><input type="submit" class="ml-button fullWidth smallmargin" name="refreshStock" value="'.ML_BUTTON_REFRESH_STOCK.'"/></td></tr>'
				: ''
			).'
		</tbody></table>';

		ob_start();?>
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function() {
	$('#listingDelete').click(function() {
		if (($('#csinventory input[type="checkbox"]:checked').length > 0) &&
			confirm(unescape(<?php echo "'".html2url(sprintf(ML_GENERIC_DELETE_LISTINGS, $_modules[$this->marketplace]['title']))."'"; ?>))
		) {
			$('#action').val('delete');
			$(this).parents('form').submit();
		}
	});
});
/*]]>*/</script>
<?php // Durch aufrufen der Seite wird automatisch ein Aktualisierungsauftrag gestartet
		$js = ob_get_contents();	
		ob_end_clean();

		if (($left == '') && ($right == '')) {
			return '';
		}
		return '
			<input type="hidden" id="action" name="action" value="">
			<input type="hidden" name="timestamp" value="'.time().'">
			<table class="actions">
				<thead><tr><th>'.ML_LABEL_ACTIONS.'</th></tr></thead>
				<tbody><tr><td>
					<table style="table-layout:fixed"><tbody><tr>
						<td class="firstChild">'.$left.'</td>
						<td style="text-align:center"><label for="tfSearch">'.ML_LABEL_SEARCH.':</label>
							<input id="tfSearch" name="tfSearch" type="text" value="'.fixHTMLUTF8Entities($this->search, ENT_COMPAT).'"/>
							<input type="submit" class="ml-button" value="'.ML_BUTTON_LABEL_GO.'" name="search_go" /></td>
						<td class="lastChild">'.$right.'</td>
					</tr></tbody></table>
				</td></tr></tbody>
			</table>
			'.$js;
	}

	public function renderView() {
		$html = '<form action="'.toUrl($this->url).'" id="csInventoryView" method="post">';
		$this->initInventoryView();
		$html .= $this->renderInventoryTable();
		return $html.$this->renderActionBox().'
			</form>
			<script type="text/javascript">/*<![CDATA[*/
				$(document).ready(function() {
					$(\'#csInventoryView\').submit(function () {
						jQuery.blockUI(blockUILoading);
					});
				});
			/*]]>*/</script>';
	}
	
}
