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
 * $Id: InventoryView.php 6572 2016-03-21 09:00:26Z markus.bauer $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once (DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');
require_once (DIR_MAGNALISTER_MODULES.'amazon/amazonFunctions.php');
require_once (DIR_MAGNALISTER_MODULES.'amazon/crons/AmazonSyncInventory.php');

class InventoryView {
	private $settings = array();
	private $sort = array();

	private $latestChange = 0;
	private $latestReport = 0;
	private $numberofitems = 0;
	private $offset = 0;

	private $add = array();
	private $updatedelete = array();
	private $getPendingItemsCalled = false;
	private $renderableData = array();

	private $simpleprice = null;
	private $url = array();
	private $magnaSession = array();

	private $inventoryPurged = false;

	private $search = '';
	private $businessFeature = '';
	private $inventoryType;
	private $itemSkus = array();
	private $itemMFNFBASkus = array();

	private $batchesProcessed = 0;

	public function __construct($settings = array()) {
		global $_MagnaShopSession, $_MagnaSession, $_url;
		$this->magnaSession = &$_MagnaSession;

		if (isset($_GET['itemsPerPage'])) {
			$this->magnaSession[$this->magnaSession['mpID']]['InventoryView']['ItemLimit'] = (int)$_GET['itemsPerPage'];
		}
		if (!isset($this->magnaSession[$this->magnaSession['mpID']]['InventoryView']['ItemLimit'])
			|| ($this->magnaSession[$this->magnaSession['mpID']]['InventoryView']['ItemLimit'] <= 0)
		) {
			$this->magnaSession[$this->magnaSession['mpID']]['InventoryView']['ItemLimit'] = 50;
		}
		
		$this->settings = array_merge(array(
			'maxTitleChars'	=> 35,
			'itemLimit'		=> $this->magnaSession[$this->magnaSession['mpID']]['InventoryView']['ItemLimit'],
		), $settings);
		
		$this->simpleprice = new SimplePrice();
		$this->simpleprice->setCurrency(getCurrencyFromMarketplace($this->magnaSession['mpID']));
		$this->url = $_url;
		$this->url['view'] = 'inventory';

		if (array_key_exists('tfSearch', $_POST) && !empty($_POST['tfSearch'])) {
			$this->search = $_POST['tfSearch'];
		} else if (array_key_exists('search', $_GET) && !empty($_GET['search'])) {
			$this->search = $_GET['search'];
		}

		if (array_key_exists('businessFeature', $_POST) && !empty($_POST['businessFeature'])) {
			$this->businessFeature = $_POST['businessFeature'];
		}

		if (array_key_exists('inventoryType', $_POST) && !empty($_POST['inventoryType'])) {
			$this->inventoryType = $_POST['inventoryType'];
		}

		initArrayIfNecessary($_MagnaShopSession, array($this->magnaSession['mpID'], 'InventoryView', 'Add'));
		$this->add = &$_MagnaShopSession[$this->magnaSession['mpID']]['InventoryView']['Add'];
		#$this->add = array();
		initArrayIfNecessary($_MagnaShopSession, array($this->magnaSession['mpID'], 'InventoryView', 'UpdateDelete'));
		$this->updatedelete = &$_MagnaShopSession[$this->magnaSession['mpID']]['InventoryView']['UpdateDelete'];
		#$this->updatedelete = array();
		if (!array_key_exists('LatestReport', $_MagnaShopSession[$this->magnaSession['mpID']]['InventoryView'])) {
			$_MagnaShopSession[$this->magnaSession['mpID']]['InventoryView']['LatestReport'] = 0;
		}
		$this->latestReport = &$_MagnaShopSession[$this->magnaSession['mpID']]['InventoryView']['LatestReport'];
	}

	private function getInventory() {
		try {
			$request = array(
				'ACTION' => 'GetInventory',
				'LIMIT' => $this->settings['itemLimit'],
				'OFFSET' => $this->offset,
				'ORDERBY' => $this->sort['order'],
				'SORTORDER' => $this->sort['type']
			);
			if (!empty($this->search)) {
				$request['SEARCH'] = $this->search;
			}

			if (!empty($this->businessFeature)) {
				$request['FILTERBUSINESS'] = $this->businessFeature;
			}

			if (!empty($this->inventoryType)) {
				$request['FILTERINVENTORYTYPE'] = $this->inventoryType;
			}

			#echo print_m($request);
			$result = MagnaConnector::gi()->submitRequest($request);
			if ($result['LATESTCHANGE']) {
				$this->latestChange = strtotime($result['LATESTCHANGE']);
			}
			if ($result['LATESTREPORT']) {
				$latestReport = strtotime($result['LATESTREPORT']);
				if ($this->latestReport != $latestReport) {
					$this->getPendingItems();
				}
				$this->latestReport = $latestReport;
			}
			$this->numberofitems = (int)$result['NUMBEROFLISTINGS'];
			return $result;

		} catch (MagnaException $e) {
			$this->latestChange = 0;
			return array();
		}
	}

	private function getPendingItems() {
		//*
		if ($this->getPendingItemsCalled) {
			return;
		}
		//*/
		$this->getPendingItemsCalled = true;

		/* Gibt es neue Listings? */
		$this->add = array();
		$this->updatedelete = array();
		$request = array(
			'ACTION' => 'GetPendingItems',
		);

		if (!empty($this->businessFeature)) {
			$request['FILTERBUSINESS'] = $this->businessFeature;
		}

		if (!empty($this->inventoryType)) {
			$request['FILTERINVENTORYTYPE'] = $this->inventoryType;
		}

		try {
			$result = MagnaConnector::gi()->submitRequest($request);
		} catch (MagnaException $e) {
			$result = array('DATA' => false);
		}
		#echo print_m($result);
		if (is_array($result['DATA']) && !empty($result['DATA'])) {
			foreach ($result['DATA'] as $item) {
				/* Get some more informations */
				if (($item['Mode'] == 'ADD') || ($item['Mode'] == 'PURGE')) {
					$pID = magnaSKU2pID($item['SKU']);
					$item['ShopItemName'] = MagnaDB::gi()->fetchOne('
						SELECT products_name 
						  FROM '.TABLE_PRODUCTS_DESCRIPTION.'
						 WHERE products_id=\''.$pID.'\'
						       AND language_id = \''.$_SESSION['languages_id'].'\'
					');
					unset($item['BatchID']);
					$this->add[$item['SKU']] = $item;
				} else {
					unset($item['BatchID']);
					$this->updatedelete[$item['SKU']] = $item;
				}
			}
		}
		#echo print_m('Reloaded Pending Items');
	}
	
	protected function getSortOpt() {
		if (isset($_GET['sorting'])) {
			$sorting = $_GET['sorting'];
		} else {
			$sorting = 'blabla'; // fallback for default
		}
		$sortFlags = array (
			'sku' => 'SKU',
			'itemtitle' => 'ItemTitle',
			'asin' => 'ASIN',
			'price' => 'Price',
			'businessprice' => 'BusinessPrice',
			'quantity' => 'Quantity',
			'dateadded' => 'DateAdded'
		);
		
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
	
	private function initInventoryView() {
		/* Listings beenden */
		if (isset($_POST['skus']) && is_array($_POST['skus']) && isset($_POST['action'])
			 && ($_SESSION['posttime'] != $_POST['timestamp']) // Re-Post Prevention
		) {
			$_SESSION['posttime'] = $_POST['timestamp'];
			switch ($_POST['action']) {
				case 'delete': {
					$skus = $_POST['skus'];
					$data = array();
					foreach ($skus as $sku) {
						$data[] = array (
							'SKU' => $sku,
						);
					}
					//*
					try {
						$result = MagnaConnector::gi()->submitRequest(array(
							'ACTION' => 'DeleteItems',
							'DATA' => $data,
							'UPLOAD' => true,
						));
						#echo print_m($result);
					} catch (MagnaException $e) { }
					//*/
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

	private function sortByType($type) {
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

	private function prepareInventoryData() {
		$result = $this->getInventory();
		$this->getPendingItems();
		/*
		echo print_m(array(
			'add' => $this->add,
			'updel' => $this->updatedelete
		));
		//*/
		$this->renderableData = array();
		if (!empty($this->add)) {
			foreach ($this->add as $item) {
				if ($item['Mode'] == 'PURGE') {
					$result['DATA'] = array();
				}
				$item = array_merge(array(
					'pID' => magnaSKU2pID($item['SKU']),
					'ItemTitle' => '',
					'Type' => 'add',
				), $item);
				$item['DateAdded'] = strtotime($item['DateAdded']);
				$this->renderableData[] = $item;
			}
		}
		if (array_key_exists('DATA', $result) && !empty($result['DATA'])) {
			foreach ($result['DATA'] as $item) {
				if (array_key_exists($item['SKU'], $this->add)) {
					continue;
				}

				if (in_array($item['SKU'], $this->itemSkus)) {
					$this->itemMFNFBASkus[] = $item['SKU'];
				}

				unset($item['ConditionType']);
				unset($item['ConditionNote']);
				unset($item['Description']);
				$item['Type'] = 'regular';
				
				$aID = magnaSKU2aID($item['SKU']);
				$item['pID'] = magnaSKU2pID($item['SKU']);
				$item['aID'] = $aID;
				
				$variationTheme = false;
				if ($aID !== false) {
					if (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties') {
						$variationTheme = MagnaDB::gi()->fetchArray(eecho('
							SELECT ppi.products_id AS pID, ppi.properties_name AS VariationTitle,
					   			ppi.values_name AS VariationValue
				  			FROM products_properties_index ppi
				 			WHERE ppi.products_id = \''.$item['pID'].'\'
							AND ppi.products_properties_combis_id = \''.$aID.'\'
				   			AND ppi.language_id = \''.getDBConfigValue(
			                			$this->magnaSession['currentPlatform'].'.lang',
			                			$this->magnaSession['mpID'],
			                			$_SESSION['languages_id']
										).'\''
							, false));
						$item['pID'] = $variationTheme[0]['pID'];
					} else {
						$variationTheme = MagnaDB::gi()->fetchRow('
					    	SELECT pa.products_id AS pID, po.products_options_name AS VariationTitle,
					           	pov.products_options_values_name AS VariationValue
					      	FROM '.TABLE_PRODUCTS_ATTRIBUTES.' pa,
					           	'.TABLE_PRODUCTS_OPTIONS.' po, 
					           	'.TABLE_PRODUCTS_OPTIONS_VALUES.' pov, 
					           	'.TABLE_LANGUAGES.' l
					     	WHERE pa.products_attributes_id = \''.$aID.'\'
					           	AND po.language_id = l.languages_id
					           	AND po.products_options_id = pa.options_id
					           	AND pov.language_id = l.languages_id
					           	AND pov.products_options_values_id = pa.options_values_id
					           	AND l.directory = \''.$_SESSION['language'].'\'
					     	LIMIT 1
						');
					$item['pID'] = $variationTheme['pID'];
					}
				}
				
				if ($item['pID'] > 0) {
					$item['ShopItemName'] = (string)MagnaDB::gi()->fetchOne('
						SELECT products_name 
						  FROM '.TABLE_PRODUCTS_DESCRIPTION.'
						 WHERE products_id=\''.$item['pID'].'\'
						       AND language_id = \''.$_SESSION['languages_id'].'\'
					');
					// Shop Price (attribute price is added in the render function)
					$item['ShopPrice'] = (float)MagnaDB::gi()->fetchOne('SELECT products_price FROM '.TABLE_PRODUCTS.' WHERE products_id='.$item['pID']);
					if (is_array($variationTheme)) {
						if (array_key_exists('VariationTitle', $variationTheme)) {
						$variationTheme = array($variationTheme);
						}
						foreach ($variationTheme as $theme) {
							if(is_array($theme) && !empty($theme['VariationTitle']) && !empty($theme['VariationValue'])) {
								$item['ShopItemName'] .= ' '.$theme['VariationTitle'].': '.$theme['VariationValue'];
							}
						}
					}
					$item['Type'] = 'inventory';
				} else {
					$item['ShopItemName'] = '';
				}
				if (array_key_exists($item['SKU'], $this->updatedelete)) {
					$tItem = $this->updatedelete[$item['SKU']];
					if (!empty($tItem['Price'])) {
						$item['Price'] = $tItem['Price'];
					}
					if (!empty($tItem['Quantity'])) {
						$item['Quantity'] = $tItem['Quantity'];
					}
					$item['Type'] = strtolower($tItem['Mode']);
				}
				$item['DateAdded'] = ($item['DateAdded'] == '0000-00-00 00:00:00')
					? 0
					: strtotime($item['DateAdded']);

				$this->itemSkus[] = $item['SKU'];
				$this->renderableData[] = $item;
			}
		}
	}

	private function renderDataGrid($id = '') {
		$html = '
			<table'.(($id != '') ? ' id="'.$id.'"' : '').' class="datagrid">
				<thead><tr>
					<td class="nowrap"><input type="checkbox" id="selectAll"/><label for="selectAll">'.ML_LABEL_CHOICE.'</label></td>
					<td>'.'SKU'.' '.$this->sortByType('sku').'</td>
					<td>'.ML_LABEL_SHOP_TITLE.'</td>
					<td>'.ML_AMAZON_LABEL_TITLE.' '.$this->sortByType('itemtitle').'</td>
					<td>ASIN '.$this->sortByType('asin').'</td>
					<td>'.ML_LABEL_SHOP_PRICE_BRUTTO.'</td>
					<td>'.ML_AMAZON_LABEL_AMAZON_PRICE.' '.$this->sortByType('price').'</td>
					<td>'.ML_AMAZON_BUSINESS_LABEL_PRICE.' '.$this->sortByType('businessprice').'</td>
					<td>'.ML_LABEL_QUANTITY.' '.$this->sortByType('quantity').'</td>
					<td>'.ML_GENERIC_CHECKINDATE.' '.$this->sortByType('dateadded').'</td>
					<td>'.ML_AMAZON_BUSINESS_LABEL_FEATURE.'</td>
					<td>'.ML_AMAZON_LABEL_INVENTORY_TYPE.'</td>
					<td>'.ML_GENERIC_STATUS.'</td>
				</tr></thead>
				<tbody>
		';
		$oddEven = false;
		#echo print_m($this->renderableData);
		$sFuncNameSubStr = 'substr';
		if (function_exists('mb_substr')) {
			mb_internal_encoding("UTF-8");
			$sFuncNameSubStr = 'mb_substr';
		}
		foreach ($this->renderableData as $item) {
			if (!empty($item['ShopItemName'])) {
				$item['ShopItemNameShort'] = (
					(strlen($item['ShopItemName']) > $this->settings['maxTitleChars'] + 2) 
						? 
							(fixHTMLUTF8Entities($sFuncNameSubStr($item['ShopItemName'], 0, $this->settings['maxTitleChars']), ENT_COMPAT).'&hellip;')
						: 
							fixHTMLUTF8Entities($item['ShopItemName'], ENT_COMPAT)
				);
				$item['ShopItemName'] = fixHTMLUTF8Entities($item['ShopItemName'], ENT_COMPAT);
			} else {
				$item['ShopItemNameShort'] = $item['ShopItemNameShort'] = '&mdash;';
			}

			if (!empty($item['ItemTitle'])) {
				$item['ItemTitleShort'] = (
					(strlen($item['ItemTitle']) > $this->settings['maxTitleChars'] + 2) 
						? 
							(fixHTMLUTF8Entities($sFuncNameSubStr($item['ItemTitle'], 0, $this->settings['maxTitleChars']), ENT_COMPAT).'&hellip;')
						: 
							fixHTMLUTF8Entities($item['ItemTitle'], ENT_COMPAT)
				);
				$item['ItemTitle'] = fixHTMLUTF8Entities($item['ItemTitle'], ENT_COMPAT);
			} else {
				$item['ItemTitleShort'] = '<span class="italic grey">'.ML_LABEL_IN_QUEUE.'</span>';
				$item['ItemTitle'] = ML_LABEL_IN_QUEUE;
			}
			
			$item['SKU_Rendered'] = fixHTMLUTF8Entities($item['SKU'], ENT_COMPAT);
			if ($item['Type'] == 'inventory') {
				$item['SKU_Rendered'] = '<a href="categories.php?pID='.$item['pID'].'&action=new_product" target="_blank" title="'.ML_LABEL_EDIT.'">'.$item['SKU'].'</a>';
			}
			$class = (($oddEven = !$oddEven) ? 'odd' : 'even').' '.$item['Type'];
			if ($item['ItemTitle'] == 'incomplete') {
				$class .= ' incomplete';
			}

			if (isset($item['ShopPrice'])) {
				$shopPrice = $this->simpleprice->setPrice($item['ShopPrice'])->addAttributeSurcharge((int)magnaSKU2aID($item['SKU']))->addTaxByPID($item['pID'])->format();
			} else {
				$shopPrice = '&mdash;';
			}

			if (isset($item['BusinessFeature'])) {
				$businessFeature = constant('ML_' . $item['BusinessFeature']);
			} else {
				$businessFeature = '&mdash;';
			}

			if (isset($item['BusinessPrice'])) {
				$businessPrice = $this->simpleprice->setPrice($item['BusinessPrice'])->format();
			} else {
				$businessPrice = '&mdash;';
			}

			if (isset($item['InventoryType'])) {
				$inventoryType = constant('ML_AMAZON_INVENTORY_TYPE_' . $item['InventoryType']);
			} else {
				$inventoryType = '&mdash;';
			}

			$addStyle = '';
			if ($item['ShopItemNameShort'] === '&mdash;' && $item['SKU'] !== '&mdash;') {
				$addStyle = 'style="color:#900;"';
			} else if (in_array($item['SKU'], $this->itemMFNFBASkus)) {
				$addStyle = 'style="color:#0000ff;"';
			}

			$html .= '
				<tr class="'.$class.'" '.$addStyle.'>
					<td><input type="checkbox" name="skus[]" value="'.$item['SKU'].'" '.((in_array($item['Type'], array(
						'add', 'delete', 'sysdelete'
					))) ? 'disabled="disabled"' : '').'/></td>
					<td>'.$item['SKU_Rendered'].'</td>
					<td title="'.$item['ShopItemName'].'">'.str_replace(' ', '&nbsp;', $item['ShopItemNameShort']).'</td>
					'.(($item['ItemTitle'] == 'incomplete')
						? ('<td>'.ML_AMAZON_LABEL_INCOMPLETE.'</td>')
						: ('<td title="'.$item['ItemTitle'].'">'.str_replace(' ', '&nbsp;', $item['ItemTitleShort']).'</td>')
					).'
					<td>'.getAmazonOfferLink($item['ASIN'], ML_AMAZON_LABEL_PRODUCT_IN_AMAZON).'</td>
					<td>'.$shopPrice.'</td>
					<td>'.$this->simpleprice->setPrice($item['Price'])->format().'</td>
					<td>'.$businessPrice.'</td>
					<td>'.(($item['Quantity'] > 0) ? $item['Quantity'] : ML_LABEL_SOLD_OUT).'</td>
					<td>'.(($item['DateAdded'] == 0)
						? '&mdash;'
						: (date("d.m.Y", $item['DateAdded']).' &nbsp;&nbsp;<span class="small">'.date("H:i", $item['DateAdded']).'</span>')
					).'</td>
					<td>'.$businessFeature.'</td>
					<td>'.$inventoryType.'</td>';

			switch ($item['Type']) {
				case 'add': {
					$html .= '
						<td title="'.ML_AMAZON_LABEL_ADD_WAIT.'"><img src="'.DIR_MAGNALISTER_WS_IMAGES.'status/grey_dot.png" alt="'.ML_AMAZON_LABEL_ADD_WAIT.'"/></td>';
					break;
				}
				case 'update': {
					$html .= '
						<td title="'.ML_AMAZON_LABEL_EDIT_WAIT.'"><img src="'.DIR_MAGNALISTER_WS_IMAGES.'status/blue_dot.png" alt="'.ML_AMAZON_LABEL_EDIT_WAIT.'"/></td>';
					break;					
				}
				case 'delete':
				case 'sysdelete': {
					$html .= '
						<td title="'.ML_AMAZON_LABEL_DELETE_WAIT.'"><img src="'.DIR_MAGNALISTER_WS_IMAGES.'status/red_dot.png" alt="'.ML_AMAZON_LABEL_DELETE_WAIT.'"/></td>';					
					break;
				}
				default: {
					$html .= '
						<td title="'.ML_AMAZON_LABEL_IN_INVENTORY.'"><img src="'.DIR_MAGNALISTER_WS_IMAGES.'status/green_dot.png" alt="'.ML_AMAZON_LABEL_IN_INVENTORY.'"/></td>';
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

	private function renderInventoryTable() {
		$html = '';

		if (empty($this->renderableData)) {
			$this->prepareInventoryData();
		}

		$html .= '
			<table class="magnaframe">
				<thead><tr><th>'.ML_LABEL_NOTE.'</th></tr></thead>
				<tbody><tr><td class="fullWidth">
					<table><tbody>
						<!--<tr><td>'.ML_AMAZON_LABEL_LAST_INVENTORY_CHANGE.':</td>
							<td>'.(($this->latestChange > 0) ? date("d.m.Y &\b\u\l\l; H:i:s", $this->latestChange) : ML_LABEL_UNKNOWN).'</td></tr>-->
						<tr><td>'.ML_AMAZON_LABEL_LAST_REPORT.'
								<div id="amazonInfo" class="desc"><span>
									'.ML_AMAZON_TEXT_CHECKIN_DELAY.'
								</span></div>:
							</td>
							<td>'.(($this->latestReport > 0) ? date("d.m.Y &\b\u\l\l; H:i:s", $this->latestReport) : ML_LABEL_UNKNOWN).'</td></tr>
						</tbody></table>
				</td></tr></tbody>
			</table>
			<div id="infodiag" class="dialog2" title="'.ML_LABEL_NOTE.'"></div>
		    <script type="text/javascript">/*<![CDATA[*/
				$(document).ready(function() {
					$(\'#amazonInfo\').click(function () {
						$(\'#infodiag\').html($(\'#amazonInfo span\').html()).jDialog();
					});
				});
			/*]]>*/</script>';

		if (isset($_POST['reload'])) {
			$html .= '
			<div id="reloaddiag" class="dialog2" title="'.ML_LABEL_NOTE.'">'.ML_AMAZON_TEXT_REFRESH_REQUEST_SEND.'</div>
		    <script type="text/javascript">/*<![CDATA[*/
				$(document).ready(function() {
					$(\'#reloaddiag\').jDialog();
				});
			/*]]>*/</script>
			';
		}
		if (isset($_POST['refreshStock'])) {
			@set_time_limit(60 * 10);
			$asi = new AmazonSyncInventory($this->magnaSession['mpID'], 'amazon');
			$asi->disableMarker(true);
			$asi->process();
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

		$itemsPerPageSelect = array(50, 100, 250, 500, 1000, 2500);
		$chooser = '
        		<select id="itemsPerPage" name="itemsPerPage" class="">'."\n";
		foreach ($itemsPerPageSelect as $chc) {
			$chcSelected = ($this->settings['itemLimit'] == $chc) ? 'selected' : '';
			$chooser .= '<option value="'.$chc.'" '.$chcSelected.'>'.$chc.'</option>';
		}
		$chooser .= '
        		</select>';

		$offset = $currentPage * $this->settings['itemLimit'] - $this->settings['itemLimit'] + 1;
		$limit = $offset + count($this->renderableData) - 1;

		ob_start();
		?>
		<table class="fullWidth nospacing nopadding valigntop topControls"><tbody><tr>
				<td class="actionLeft">
				</td>
				<td>
					<table class="nospacing nopadding right"><tbody><tr>
							<td class="filterRight">
								<div class="filterWrapper">
									<select id="filter_business_feature_select" class="n">
										<option value="AMAZON_BUSINESS_ALL"
											<?php if (isset($this->businessFeature) && $this->businessFeature === 'AMAZON_BUSINESS_ALL') {
												echo 'selected="selected"';
											}?>
										><?php echo ML_AMAZON_BUSINESS_ALL ?></option>
										<option value="AMAZON_BUSINESS_STANDARD"
											<?php if (isset($this->businessFeature) && $this->businessFeature === 'AMAZON_BUSINESS_STANDARD') {
												echo 'selected="selected"';
											}?>
										><?php echo ML_AMAZON_BUSINESS_STANDARD ?></option>
										<option value="AMAZON_BUSINESS_B2B"
											<?php if (isset($this->businessFeature) && $this->businessFeature === 'AMAZON_BUSINESS_B2B') {
												echo 'selected="selected"';
											}?>
										><?php echo ML_AMAZON_BUSINESS_B2B ?></option>
										<option value="AMAZON_BUSINESS_B2B_B2C"
											<?php if (isset($this->businessFeature) && $this->businessFeature === 'AMAZON_BUSINESS_B2B_B2C') {
												echo 'selected="selected"';
											}?>
										><?php echo ML_AMAZON_BUSINESS_B2B_B2C ?></option>
									</select>
									<input type="hidden" id="filter_business_feature_input" name="businessFeature" value="">
									<select id="filter_inventory_type_select" class="n">
										<option value="ALL"
											<?php if (isset($this->inventoryType) && $this->inventoryType === 'ALL') {
												echo 'selected="selected"';
											}?>
										><?php echo ML_AMAZON_INVENTORY_TYPE_ALL ?></option>
										<option value="MFN"
											<?php if (isset($this->inventoryType) && $this->inventoryType === 'MFN') {
												echo 'selected="selected"';
											}?>
										><?php echo ML_AMAZON_INVENTORY_TYPE_MFN ?></option>
										<option value="FBA"
											<?php if (isset($this->inventoryType) && $this->inventoryType === 'FBA') {
												echo 'selected="selected"';
											}?>
										><?php echo ML_AMAZON_INVENTORY_TYPE_FBA ?></option>
									</select>
									<input type="hidden" id="filter_inventory_type_input" name="inventoryType" value="">
								</div>
							</td>
						</tr></tbody></table>
				</td>
			</tr></tbody></table>
		<script type="text/javascript">/*<![CDATA[*/
			$('#filter_business_feature_select').change(function () {
				$('#filter_business_feature_input').val(this.value);
				this.closest('form').submit();
			});

			$('#filter_inventory_type_select').change(function () {
				$('#filter_inventory_type_input').val(this.value);
				this.closest('form').submit();
			});
			/*]]>*/</script>
		<br>
		<?php
		$html .= ob_get_contents();
		ob_end_clean();

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
						'.renderPagination($currentPage, $pages, $tmpURL).'&nbsp;'.$chooser.'
					</td>
				</tr></tbody></table>';
		
		if (!empty($this->renderableData)) {
			$html .= $this->renderDataGrid('inventory');
		} else {
			$html .= '<table class="magnaframe"><tbody><tr><td>'.
						(empty($this->search) ? ML_AMAZON_LABEL_NO_INVENTORY : ML_LABEL_NO_SEARCH_RESULTS).
					 '</td></tr></tbody></table>';
		}

		ob_start();
?>
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function() {
	$('#selectAll').click(function() {
		state = $(this).attr('checked');
		$('#inventory input[type="checkbox"]:not([disabled])').each(function() {
			$(this).attr('checked', state);
		});
	});
	$('table.datagrid tbody tr').click(function() {
		cb = $('input[type="checkbox"]:not(:disabled)', $(this));
		if (cb.length != 1) return;
		if (cb.is(':checked')) {
			cb.removeAttr('checked');
		} else {
			cb.attr('checked', 'checked');
		}
	});
	$('table.datagrid tbody tr td input[type="checkbox"]').click(function () {
		this.checked = !this.checked;
	});
	$('#itemsPerPage').change(function() {
		window.location.href = '<?php echo toURL($tmpURL, true);?>&itemsPerPage='+$(this).val();
	});
});
/*]]>*/</script>
<?php
		$html .= ob_get_contents();	
		ob_end_clean();
		
		return $html;
	}

	private function renderActionBox() {
		global $_modules;

		$left = '<input type="button" class="ml-button" value="'.ML_BUTTON_LABEL_DELETE.'" id="listingDelete" name="listing[delete]"/>';
		$right = '<table class="right"><tbody>
			<tr><td><input type="submit" class="ml-button fullWidth smallmargin" name="reload" value="'.ML_BUTTON_RELOAD_INVENTORY.'"/></td></tr>
		</tbody></table>';
			
		ob_start();?>
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function() {
	$('#listingDelete').click(function() {
		if (($('#inventory input[type="checkbox"]:checked').length > 0) &&
			confirm(unescape(<?php echo "'".html2url(sprintf(ML_GENERIC_DELETE_LISTINGS, $_modules[$this->magnaSession['currentPlatform']]['title']))."'"; ?>))
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
		$html = '<form action="'.toUrl($this->url).'" id="amazonInventoryView" method="post">';
		$this->initInventoryView();
		$html .= $this->renderInventoryTable();
		return $html.$this->renderActionBox().'
			</form>
			<script type="text/javascript">/*<![CDATA[*/
				$(document).ready(function() {
					$(\'#amazonInventoryView\').submit(function () {
						jQuery.blockUI(blockUILoading);
					});
				});
			/*]]>*/</script>';
	}
}
