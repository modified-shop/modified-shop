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
require_once (DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');
require_once (DIR_MAGNALISTER_INCLUDES.'lib/classes/VariationsCalculator.php');

class DeletedView {
	protected $marketplaceID;

	protected $settings = array();
	protected $sort = array();

	protected $numberofitems = 0;
	protected $offset = 0;
	
	protected $renderableData = array();
		
	protected $simplePrice = null;
	protected $url = array();
	protected $magnasession = array();
	protected $magnaShopSession = array();

	protected $search = '';

	public function __construct($settings = array()) {
		global $_MagnaShopSession, $_MagnaSession, $_url, $_modules;
		
		$this->marketplaceID = $_MagnaSession['mpID'];
		
		$this->settings = array_merge(array(
			'maxTitleChars'	=> 80,
			'itemLimit'		=> 50,
		), $settings);

		$this->simplePrice = new SimplePrice();
		$this->simplePrice->setCurrency(getCurrencyFromMarketplace($this->marketplaceID));
		$this->url = $_url;
		$this->url['view'] = 'deleted';
		$this->magnasession = &$_MagnaSession;
		$this->magnaShopSession = &$_MagnaShopSession;

		if (array_key_exists('tfSearch', $_POST) && !empty($_POST['tfSearch'])) {
			$this->search = $_POST['tfSearch'];
		} else if (array_key_exists('search', $_GET) && !empty($_GET['search'])) {
			$this->search = $_GET['search'];
		}
	}

	private function getInventory() {
		try {
			$request = array(
				'ACTION' => 'GetInventory',
				'LIMIT' => $this->settings['itemLimit'],
				'OFFSET' => $this->offset,
				'ORDERBY' => $this->sort['order'],
				'SORTORDER' => $this->sort['type'],
				'FILTER' => 'DELETED'
			);
			if (!empty($this->search)) {
				#$request['SEARCH'] = (!magnalisterIsUTF8($this->search)) ? utf8_encode($this->search) : $this->search;
				$request['SEARCH'] = $this->search;
			}
			MagnaConnector::gi()->setTimeOutInSeconds(1800);
			$result = MagnaConnector::gi()->submitRequest($request);
			MagnaConnector::gi()->resetTimeOut();
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

		switch ($sorting) {
	        case 'sku':
	            $this->sort['order'] = 'SKU';
	            $this->sort['type']  = 'ASC';
	            break;
	        case 'sku-desc':
	            $this->sort['order'] = 'SKU';
	            $this->sort['type']  = 'DESC';
	            break;
	        case 'itemtitle':
	            $this->sort['order'] = 'ItemTitle';
	            $this->sort['type']  = 'ASC';
	            break;
	        case 'itemtitle-desc':
	            $this->sort['order'] = 'ItemTitle';
	            $this->sort['type']  = 'DESC';
	            break;
	        case 'price':
	            $this->sort['order'] = 'Price';
	            $this->sort['type']  = 'ASC';
	            break;
	        case 'price-desc':
	            $this->sort['order'] = 'Price';
	            $this->sort['type']  = 'DESC';
	            break;
			case 'dateadded':
				$this->sort['order'] = 'DateAdded';
				$this->sort['type']  = 'ASC';
				break;
			case 'dateadded-desc':
				$this->sort['order'] = 'DateAdded';
				$this->sort['type']  = 'DESC';
				break;
			case 'datedeleted':
				$this->sort['order'] = 'End';
				$this->sort['type']  = 'ASC';
				break;
			case 'datedeleted-desc':
				$this->sort['order'] = 'End';
				$this->sort['type']  = 'DESC';
				break;
	        default:
	            $this->sort['order'] = 'DateAdded';
	            $this->sort['type']  = 'DESC';
	            break;
	    }
	}
	
	protected function postDelete() { /* Nix :-) */ }
	
	private function initDeletedView() {
		//$_POST['timestamp'] = time();
		if (isset($_POST['ItemIDs']) && is_array($_POST['ItemIDs']) && isset($_POST['action']) && 
			($_SESSION['POST_TS'] != $_POST['timestamp']) // Re-Post Prevention
		) {
			$_SESSION['POST_TS'] = $_POST['timestamp'];
			switch ($_POST['action']) {
				case 'delete': {
					$itemIDs = $_POST['ItemIDs'];
					$request = array (
						'ACTION' => 'DeleteItems',
						'DATA' => array(),
					);
					$insertData = array();
					foreach ($itemIDs as $itemID) {
						$request['DATA'][] = array (
							'ItemID' => $itemID,
						);
						$pDetails = unserialize(str_replace('\\"', '"', $_POST['details'][$itemID]));
						$pID = magnaSKU2pID($sku);
						$model = '';
						if ($pID > 0) {
							$model = (string)MagnaDB::gi()->fetchOne('SELECT products_model FROM '.TABLE_PRODUCTS.' WHERE products_id=\''.$pID.'\'');
						}
						if (empty($model)) {
							$model = $sku;
						}
						$insertData[$itemID] = array (
							'products_id' => $pID,
							'products_model' => $model,
							'mpID' => $this->magnasession['mpID'],
							'ItemID' => $itemID,
							'price' => $pDetails['Price'],
							'timestamp' => date('Y-m-d H:i:s')
						);
					}
					/*
					echo print_m($insertData, '$insertData');
					echo print_m($request, '$request');
					*/
					MagnaConnector::gi()->setTimeOutInSeconds(7200); # 2 hrs, about 1800 Items
					try {
						$result = MagnaConnector::gi()->submitRequest($request);
					} catch (MagnaException $e) {
						$result = array (
							'STATUS' => 'ERROR'
						);
					}
					MagnaConnector::gi()->resetTimeOut();
					/*
					if ($result['STATUS'] == 'SUCCESS') {
						$result['DeletedItemIDs'] = array_keys($insertData);
					}
					echo print_m($result, '$result');
					*/
					if (($result['STATUS'] == 'SUCCESS') 
						&& array_key_exists('DeletedItemIDs', $result) 
						&& is_array($result['DeletedItemIDs'])
						&& !empty($result['DeletedItemIDs'])
					) {
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
	
	public function prepareInventoryData() {
		global $magnaConfig;

		$result = $this->getInventory();
		if (($result !== false) && !empty($result['DATA'])) {
			$this->renderableData = $result['DATA'];
			foreach ($this->renderableData as &$item) {
				$item['ItemTitleShort'] = (strlen($item['ItemTitle']) > $this->settings['maxTitleChars'] + 2)
						? (fixHTMLUTF8Entities(substr($item['ItemTitle'], 0, $this->settings['maxTitleChars'])).'&hellip;')
						: fixHTMLUTF8Entities($item['ItemTitle']);
				$item['VariationAttributesText'] = fixHTMLUTF8Entities($item['VariationAttributesText']);
				$item['DateAdded'] = strtotime($item['DateAdded']);
				$item['DateEnd'] = ('1'==$item['GTC']?max(strtotime($item['End']), strtotime($item['LastSync'])):strtotime($item['End']));
				$item['LastSync'] = strtotime($item['LastSync']);
			}
			unset($result);
		}

	}

    private function getShopDataForItems() {
        global $magnaConfig;
        $language = $magnaConfig['db'][$this->magnasession['mpID']]['ebay.lang'];
        $SKUarr = array();
        $SKUlist = '';
        foreach ($this->renderableData as $item) {
            $SKUarr[] = $item['SKU'];
        }
        $SKUarr = array_unique($SKUarr);
        foreach ($SKUarr as $currentSKU) {
			$SKUlist .= ", '".MagnaDB::gi()->escape($currentSKU)."'";
        }
        $SKUlist = ltrim($SKUlist, ', ');
        if ('artNr' == getDBConfigValue('general.keytype', '0')) {
            $ShopDataForSimpleItems = MagnaDB::gi()->fetchArray('SELECT DISTINCT p.products_model SKU, p.products_id products_id, CAST(p.products_quantity AS SIGNED) ShopQuantity, p.products_price ShopPrice, pd.products_name ShopTitle 
                                        FROM '.TABLE_PRODUCTS.' p, '.TABLE_PRODUCTS_DESCRIPTION.' pd
                                        WHERE p.products_id=pd.products_id
                                        AND pd.language_id='.$language.'
                                        AND p.products_model IN ('.$SKUlist.')');
        } else {
            $ShopDataForSimpleItems = MagnaDB::gi()->fetchArray('SELECT DISTINCT CONCAT(\'ML\',p.products_id) SKU, p.products_id products_id, CAST(p.products_quantity AS SIGNED)  ShopQuantity, p.products_price ShopPrice, pd.products_name ShopTitle
                                        FROM '.TABLE_PRODUCTS.' p, '.TABLE_PRODUCTS_DESCRIPTION.' pd
                                        WHERE p.products_id=pd.products_id
                                        AND pd.language_id='.$language.'
                                        AND CONCAT(\'ML\',p.products_id) IN ('.$SKUlist.')');
        }
		if (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties') {
			if ('artNr' == getDBConfigValue('general.keytype', '0')) {
				$selectSku = " CONCAT(p.products_model, '-', ppc.combi_model) ";
           	$ShopDataForVariationItems = MagnaDB::gi()->fetchArray(eecho("
			SELECT DISTINCT $selectSku AS SKU,
				   ppc.products_id products_id, '' AS variation_attributes,
				   CAST(ppc.combi_quantity AS SIGNED) AS ShopQuantity,
				   ppc.combi_price + p.products_price AS ShopPrice,
				   pd.products_name AS ShopTitle
				FROM products_properties_combis ppc, ".TABLE_PRODUCTS." p, ".TABLE_PRODUCTS_DESCRIPTION." pd
			   WHERE ppc.products_id=p.products_id
					AND ppc.products_id=pd.products_id
					AND pd.language_id='$language'
					AND $selectSku IN ($SKUlist)", false));
			} else {
				$ShopDataForVariationItems = array();
				foreach ($SKUarr as $sku) {
					$combisId = magnaSKU2aID($sku, false, true);
					$ShopDataForVariationItems[] = MagnaDB::gi()->fetchRow("
						SELECT '$sku' AS SKU,
				   		ppc.products_id AS products_id, '' AS variation_attributes,
				   		CAST(ppc.combi_quantity AS SIGNED) AS ShopQuantity,
				   		ppc.combi_price + p.products_price AS ShopPrice,
				   		pd.products_name AS ShopTitle
						FROM products_properties_combis ppc, ".TABLE_PRODUCTS." p, ".TABLE_PRODUCTS_DESCRIPTION." pd
				   	  	WHERE ppc.products_id=p.products_id
							AND ppc.products_id=pd.products_id
							AND pd.language_id='$language'
							AND ppc.products_properties_combis_id = '$combisId'");
				}
			}
		} else {
        	$ShopDataForVariationItems = MagnaDB::gi()->fetchArray('SELECT DISTINCT v.'.mlGetVariationSkuField().' AS SKU, v.products_id products_id,
                                        	variation_attributes,
                                        	CAST(v.variation_quantity AS SIGNED) ShopQuantity, v.variation_price + p.products_price ShopPrice, pd.products_name ShopTitle
                                        	FROM '.TABLE_MAGNA_VARIATIONS.' v, '.TABLE_PRODUCTS.' p, '.TABLE_PRODUCTS_DESCRIPTION.' pd
                                        	WHERE v.products_id=p.products_id
                                        	AND v.products_id=pd.products_id
                                        	AND pd.language_id='.$language.'
                                        	AND v.'.mlGetVariationSkuField().' IN ('.$SKUlist.')');
		}
        $ShopDataForItemsBySKU = array();
        foreach ($ShopDataForSimpleItems as $ShopDataForSimpleItem) {
            $ShopDataForItemsBySKU[$ShopDataForSimpleItem['SKU']] = $ShopDataForSimpleItem;
            unset ($ShopDataForItemsBySKU[$ShopDataForSimpleItem['SKU']]['SKU']);
            $ShopDataForItemsBySKU[$ShopDataForSimpleItem['SKU']]['ShopVarText'] = '';
        }
        foreach ($ShopDataForVariationItems as $ShopDataForVariationItem) {
            $ShopDataForItemsBySKU[$ShopDataForVariationItem['SKU']] = $ShopDataForVariationItem;
            unset ($ShopDataForItemsBySKU[$ShopDataForVariationItem['SKU']]['SKU']);
            $ShopDataForVariationItem['ShopVarText'] = VariationsCalculator::generateVariationsAttributesText($ShopDataForVariationItem['variation_attributes'], $language, ', ', ':');
        }
        foreach ($this->renderableData as &$item) {
            if (isset ($ShopDataForItemsBySKU[$item['SKU']])) {
                $item['ShopQuantity'] = $ShopDataForItemsBySKU[$item['SKU']]['ShopQuantity'];
                $item['ShopPrice']    = $ShopDataForItemsBySKU[$item['SKU']]['ShopPrice'];
                $item['ShopTitle']    = $ShopDataForItemsBySKU[$item['SKU']]['ShopTitle'];
                $item['ShopVarText']  = isset($ShopDataForItemsBySKU[$item['SKU']]['ShopVarText']) ? $ShopDataForItemsBySKU[$item['SKU']]['ShopVarText'] : '&nbsp;';
            } else {
                $item['ShopQuantity'] = $item['ShopPrice'] = $item['ShopTitle'] = '&mdash;';
                $item['ShopVarText']  = '&nbsp;';
            }
        }
    }
	
	private function emptyStr2mdash($str) {
		return (empty($str) || (is_numeric($str) && ($str == 0))) ? '&mdash;' : $str;
	}
	
	protected function additionalHeaders() { }

	protected function additionalValues($item) { }

	private function renderDataGrid($id = '') {
		global $magnaConfig;

		$html = '
			<table'.(($id != '') ? ' id="'.$id.'"' : '').' class="datagrid">
				<thead class="small"><tr>
					<td>'.ML_LABEL_SKU.' '.$this->sortByType('sku').'</td>
					<td>'.ML_LABEL_SHOP_TITLE.'</td>
					<td>'.ML_LABEL_EBAY_TITLE.' '.$this->sortByType('itemtitle').'</td>
					<td>'.ML_LABEL_EBAY_ITEM_ID.'</td>
					<td>'.ML_PRICE_SHOP_PRICE_EBAY.' '.$this->sortByType('price').'</td>
					<td>'.ML_LAST_SYNC.'</td>
					<td>'.ML_LABEL_EBAY_STARTTIME.' '.$this->sortByType('dateadded').'</td>
					<td>'.ML_LABEL_EBAY_ENDTIME.' '.$this->sortByType('datedeleted').'</td>
					<td>'.ML_LABEL_EBAY_DELETION_REASON.'</td>
				</tr></thead>
				<tbody>
		';
		$oddEven = false;
        $this->getShopDataForItems();
		foreach ($this->renderableData as $item) {
			$details = htmlspecialchars(str_replace('"', '\\"', serialize(array(
			 	'SKU' => $item['SKU'],
			 	'Price' => $item['Price'],
			 	'Currency' => $item['Currency'],
			))));


            $renderedShopPrice = (0 != $item['ShopPrice'])?$this->simplePrice->setPriceAndCurrency($item['ShopPrice'], $item['Currency'])->format():'&mdash;';
            switch ($item['deletedBy']) {
                case('Sync'):    $deletedBy = ML_SYNCHRONIZATION; break;
                case('Button'):  $deletedBy = ML_DELETION_BUTTON; break;
                case('expired'): $deletedBy = ML_EXPIRED;         break;
                case('notML'):   $deletedBy = ML_NOT_BY_ML;       break;
                default:         $deletedBy = '&mdash;';          break;
            }
			$html .= '
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
					<td>'.$item['SKU'].'</td>
					<td title="'.fixHTMLUTF8Entities($item['ShopTitle'], ENT_COMPAT).'">'.$item['ShopTitle'].'<br /><span class="small">'.$item['ShopVarText'].'</span></td>
					<td title="'.fixHTMLUTF8Entities($item['ItemTitle'], ENT_COMPAT).'">'.$item['ItemTitleShort'].'<br /><span class="small">'.$item['VariationAttributesText'].'</span></td>
					<td><a href="'.$item['SiteUrl'].'?ViewItem&item='.$item['ItemID'].'" target="_blank">'.$item['ItemID'].'</a></td>
					<td>'.$renderedShopPrice.' / '.$this->simplePrice->setPriceAndCurrency($item['Price'], $item['Currency'])->format().'</td>
					<td>'.($item['LastSync'] > 0 ? date("d.m.Y", $item['LastSync']).' &nbsp;&nbsp;<span class="small">'.date("H:i", $item['LastSync']) : '&mdash;').'</span></td>
					<td>'.date("d.m.Y", $item['DateAdded']).' &nbsp;&nbsp;<span class="small">'.date("H:i", $item['DateAdded']).'</span></td>
					<td>'.date("d.m.Y", $item['DateEnd']).' &nbsp;&nbsp;<span class="small">'.date("H:i", $item['DateEnd']).'</span></td>
					<td>'.$deletedBy.'</td>';
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
		#echo print_m($this->renderableData, 'renderInventoryTable: $this->renderableData');


		$pages = ceil($this->numberofitems / $this->settings['itemLimit']);
		$bla = '';
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

		for ($i = 1; $i <= $pages; ++$i) {
			$hi = ($currentPage == $i) ? '<b>'.$i.'</b>' : $i;
			$bla .= ' <a href="'.toUrl($tmpURL, array('page' => $i)).'" title="'.ML_LABEL_PAGE.' '.$i.'">'.$hi.'</a>';
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
						'.$bla.'
					</td>
				</tr></tbody></table>';

		if (!empty($this->renderableData)) {
			$html .= $this->renderDataGrid('ebayinventory');
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
		state = $(this).attr('checked');
		$('#ebayinventory input[type="checkbox"]:not([disabled])').each(function() {
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
	
	protected function getRightActionButton() { return ''; }
	
	public function renderActionBox() {
		global $_modules;
		/*$left = (!empty($this->renderableData) ? 
			'<input type="button" class="ml-button" value="'.ML_BUTTON_LABEL_DELETE.'" id="listingDelete" name="listing[delete]"/>' : 
			''
		);*/
		$left  = '';
		$right = $this->getRightActionButton();

		ob_start();?>
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function() {
	$('#listingDelete').click(function() {
		if (($('#ebayinventory input[type="checkbox"]:checked').length > 0) &&
			confirm(unescape(<?php echo "'".html2url(sprintf(ML_GENERIC_DELETE_LISTINGS, $_modules[$this->magnasession['currentPlatform']]['title']))."'"; ?>))
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

		/*if (($left == '') && ($right == '')) {
			return '';
		}*/
		return '
			<input type="hidden" id="action" name="action" value="">
			<input type="hidden" name="timestamp" value="'.time().'">
			<table class="actions">
				<thead><tr><th>'.ML_LABEL_ACTIONS.'</th></tr></thead>
				<tbody><tr><td>
					<table><tbody><tr>
						<td class="firstChild">'.$left.'</td>
						<td><label for="tfSearch">'.ML_LABEL_SEARCH.':</label>
							<input id="tfSearch" name="tfSearch" type="text" value="'.fixHTMLUTF8Entities($this->search, ENT_COMPAT).'"/>
							<input type="submit" class="ml-button" value="'.ML_BUTTON_LABEL_GO.'" name="search_go" /></td>
						<td class="lastChild">'.$right.'</td>
					</tr></tbody></table>
				</td></tr></tbody>
			</table>
			'.$js;
	}

	public function renderView() {
		$html = '<form action="'.toUrl($this->url).'" id="ebayDeletedView" method="post">';
		$this->initDeletedView();
		$html .= $this->renderInventoryTable();
		return $html.$this->renderActionBox().'
			</form>
			<script type="text/javascript">/*<![CDATA[*/
				$(document).ready(function() {
					$(\'#ebayDeletedView\').submit(function () {
						jQuery.blockUI(blockUILoading);
					});
				});
			/*]]>*/</script>';
	}
	
}
